# UBS Triagem Inteligente — Protótipo Completo (Educacional)

> **Atenção:** Este projeto é **exclusivamente educacional**. **Não** substitui avaliação profissional ou protocolos oficiais (ex.: Manchester). Em emergência, ligue **192 (SAMU)**.

## Recursos incluídos
- Frontend responsivo (HTML/CSS/JS).
- Backend Node.js + Express.
- **Autenticação JWT** e **papéis**: `admin`, `recepcao`, `enfermagem`, `medico` (usuários padrão com senha `123456`).
- **SQLite** com `better-sqlite3` (persistência local).
- **Histórico de triagens por paciente**.
- **Exportação de PDF** de relatórios (PDFKit).
- **Motor local de IA (regras + escore)**, com **integração opcional a LLM (OpenAI)** via `OPENAI_API_KEY`.
- Avisos de **segurança clínica** e **LGPD** (mínimo necessário neste protótipo).

## Como rodar
```bash
npm install
# (opcional) criar .env:
# JWT_SECRET=algumseguro
# OPENAI_API_KEY=sk-xxx
npm start
# abra http://localhost:3000
```

### Login de exemplo
- `admin@ubs.local` / `123456`
- `recepcao@ubs.local` / `123456`
- `enfermagem@ubs.local` / `123456`
- `medico@ubs.local` / `123456`

## Fluxo
1. Faça login → vá para **/app.html**.
2. Preencha cadastro + sintomas → **Analisar com IA**.
3. Veja **classificação de risco**, **recomendação**, **hipótese** e **nota LLM** (se configurado).
4. O resultado é salvo, aparece no **Histórico**, e você pode **baixar o PDF**.

## Estrutura
```
server.js
src/
  db.js
  middlewares/auth.js
  routes/
    authRoutes.js
    triageRoutes.js
    patientRoutes.js
  services/aiProvider.js
  riskEngine.js
public/
  index.html
  app.html
  styles.css
  login.js
  app.js
data.db (gerado)
```

## Observações de segurança e conformidade
- **Não** use em produção. Requer validação clínica, comitê de ética, revisão legal, LGPD, logs/auditoria, e rastreabilidade.
- Se integrar LLM, **não** envie dados identificáveis para terceiros sem consentimento explícito e base legal.
- Limite de escopo: **triagem** e **priorização inicial** — **não** é diagnóstico.

Boa prática: adote um **protocolo reconhecido** (p.ex., Manchester) e modele a UI com **perguntas direcionadas** por queixa principal, thresholds objetivos (sinais vitais), e regras de escalonamento.