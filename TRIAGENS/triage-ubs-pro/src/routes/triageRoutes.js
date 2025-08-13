import { Router } from 'express';
import db from '../db.js';
import { authRequired } from '../middlewares/auth.js';
import { engineTriagem } from '../riskEngine.js';
import { askLLMFreeText } from '../services/aiProvider.js';
import PDFDocument from 'pdfkit';

const router = Router();

function ensurePatient(p) {
  // Try to find existing by name + (optional) idade
  const query = db.prepare('SELECT * FROM patients WHERE name = ? AND (idade = ? OR ? IS NULL) ORDER BY id DESC');
  const existing = query.get(p.nome, p.idade || null, p.idade || null);
  if (existing) return existing.id;
  const stmt = db.prepare('INSERT INTO patients (name, idade, genero, alergias, gestante, createdAt) VALUES (?, ?, ?, ?, ?, ?)');
  const id = stmt.run(p.nome, p.idade || null, p.genero || null, p.alergias || '', p.gestante ? 1 : 0, new Date().toISOString()).lastInsertRowid;
  return id;
}

router.post('/', authRequired, async (req, res) => {
  try {
    const payload = req.body || {};
    const patientId = ensurePatient({
      nome: payload.nome, idade: Number(payload.idade), genero: payload.genero, alergias: payload.alergias, gestante: !!payload.gestante
    });

    const base = engineTriagem(payload);

    // Optional LLM refinement
    let llmNote = null;
    if (process.env.OPENAI_API_KEY) {
      const prompt = `Dados do paciente: ${JSON.stringify({
        nome: payload.nome, idade: payload.idade, genero: payload.genero, alergias: payload.alergias, gestante: payload.gestante,
        sintomas: payload.sintomas, descricaoLivre: payload.descricaoLivre
      })}
Resuma de forma breve (3-5 linhas) hipóteses diferenciais prováveis e sinais de alarme. Diga claramente que é uma orientação educacional e para procurar avaliação profissional se sintomas persistirem ou piorarem.`;
      llmNote = await askLLMFreeText({ apiKey: process.env.OPENAI_API_KEY, prompt });
    }

    const relatorio = {
      cabecalho: {
        geradoEm: new Date().toISOString(),
        sistema: 'Triagem Inteligente UBS (Protótipo)',
        avisoLegal: 'Ferramenta educacional. Não substitui avaliação profissional.'
      },
      paciente: {
        nome: payload.nome, idade: Number(payload.idade) || null, genero: payload.genero, alergias: payload.alergias, gestante: !!payload.gestante
      },
      entrada: { sintomas: payload.sintomas || [], descricaoLivre: payload.descricaoLivre || '' },
      analiseIA: {
        escoreGravidade: base.escoreGravidade,
        marcadores: base.marcadores,
        possiveisCausas: base.possiveisCausas,
        notaLLM: llmNote
      },
      resultado: base.resultado
    };

    const insert = db.prepare('INSERT INTO triages (patientId, userId, sintomas, descricaoLivre, resultado, createdAt) VALUES (?, ?, ?, ?, ?, ?)');
    const triageId = insert.run(patientId, req.user?.id || null, JSON.stringify(payload.sintomas || []), payload.descricaoLivre || '', JSON.stringify(relatorio), new Date().toISOString()).lastInsertRowid;

    res.json({ triageId, patientId, ...relatorio });
  } catch (e) {
    console.error(e);
    res.status(400).json({ error: 'Erro ao processar triagem.' });
  }
});

router.get('/history/:patientId', authRequired, (req, res) => {
  const patientId = Number(req.params.patientId);
  const rows = db.prepare('SELECT id, createdAt, resultado FROM triages WHERE patientId = ? ORDER BY id DESC').all(patientId);
  const triages = rows.map(r => ({ id: r.id, createdAt: r.createdAt, relatorio: JSON.parse(r.resultado) }));
  res.json({ triages });
});

router.get('/report/:triageId.pdf', authRequired, (req, res) => {
  const tId = Number(req.params.triageId);
  const row = db.prepare('SELECT t.id, t.createdAt, t.resultado, p.name AS patientName FROM triages t JOIN patients p ON p.id = t.patientId WHERE t.id = ?').get(tId);
  if (!row) return res.status(404).json({ error: 'Relatório não encontrado' });

  const data = JSON.parse(row.resultado);
  res.setHeader('Content-Type', 'application/pdf');
  res.setHeader('Content-Disposition', `attachment; filename="triagem-${tId}.pdf"`);

  const doc = new PDFDocument({ margin: 40 });
  doc.pipe(res);
  doc.fontSize(18).text('Triagem Inteligente UBS (Protótipo)', { align: 'center' });
  doc.moveDown(0.3);
  doc.fontSize(10).text('Ferramenta educacional. Não substitui avaliação profissional.', { align: 'center' });
  doc.moveDown();

  doc.fontSize(12).text(`Relatório #${tId} • Gerado em: ${new Date().toLocaleString()}`);
  doc.moveDown();
  doc.fontSize(14).text('Paciente', { underline: true });
  doc.fontSize(12).text(`Nome: ${data.paciente?.nome || ''}`);
  doc.text(`Idade: ${data.paciente?.idade ?? ''}  •  Gênero: ${data.paciente?.genero || ''}`);
  doc.text(`Alergias: ${data.paciente?.alergias || '—'}  •  Gestante: ${data.paciente?.gestante ? 'Sim' : 'Não'}`);
  doc.moveDown();

  doc.fontSize(14).text('Entrada', { underline: true });
  doc.fontSize(12).text(`Sintomas: ${(data.entrada?.sintomas || []).join(', ') || '—'}`);
  doc.text('Descrição livre:');
  doc.text(data.entrada?.descricaoLivre || '—', { indent: 12 });
  doc.moveDown();

  doc.fontSize(14).text('Resultado', { underline: true });
  doc.fontSize(12).text(`Classificação de risco: ${data.resultado?.classificacaoRisco}`);
  doc.text(`Recomendação: ${data.resultado?.recomendacao}`);
  doc.text(`Hipótese principal: ${data.resultado?.hipotesePrincipal}`);
  doc.moveDown();

  doc.fontSize(14).text('Análise IA', { underline: true });
  doc.fontSize(12).text(`Escore de gravidade: ${data.analiseIA?.escoreGravidade}`);
  doc.text(`Marcadores: ${(data.analiseIA?.marcadores || []).join(', ') || '—'}`);
  doc.text(`Possíveis causas: ${(data.analiseIA?.possiveisCausas || []).join('; ') || '—'}`);
  if (data.analiseIA?.notaLLM) {
    doc.moveDown();
    doc.fontSize(12).text('Nota LLM (orientação educacional):', { underline: true });
    doc.font('Times-Italic').text(data.analiseIA.notaLLM, { indent: 12 });
    doc.font('Times-Roman');
  }

  doc.moveDown();
  doc.fontSize(10).text('Em caso de emergência, ligue 192 (SAMU).', { align: 'center' });

  doc.end();
});

export default router;