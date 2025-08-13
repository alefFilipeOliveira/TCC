/**
 * Servidor Express para Triagem Inteligente (Protótipo educacional)
 * ATENÇÃO: Não é um dispositivo médico nem substitui avaliação profissional.
 */
const express = require('express');
const cors = require('cors');
const path = require('path');
const { triage } = require('./riskEngine');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

app.post('/api/triage', (req, res) => {
  try {
    const input = req.body;
    const result = triage(input);
    res.json(result);
  } catch (err) {
    console.error(err);
    res.status(400).json({ error: 'Erro ao processar triagem.' });
  }
});

app.listen(PORT, () => {
  console.log(`Servidor rodando em http://localhost:${PORT}`);
});