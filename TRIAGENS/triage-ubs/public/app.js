// Lista de sintomas populares (usuário pode clicar para ativar/desativar)
const SYMPTOMS = [
  'febre', 'febre alta', 'calafrios', 'tosse', 'tosse seca', 'tosse com catarro',
  'falta de ar', 'chiado', 'dor no peito', 'dor abdominal', 'vomito persistente',
  'diarreia com sangue', 'rigidez de pescoco', 'dor de cabeca forte', 'manchas vermelhas',
  'confusao', 'desmaio', 'sangramento intenso', 'dor ao urinar', 'ardor ao urinar',
  'coriza', 'nariz entupido', 'coceira'
];

const chipsContainer = document.getElementById('symptom-chips');
SYMPTOMS.forEach(s => {
  const b = document.createElement('button');
  b.type = 'button';
  b.textContent = s;
  b.className = 'chip';
  b.onclick = () => b.classList.toggle('active');
  chipsContainer.appendChild(b);
});

const form = document.getElementById('triage-form');
const resultado = document.getElementById('resultado');
const riskBadge = document.getElementById('risk-badge');
const recomendacao = document.getElementById('recomendacao');
const hipotese = document.getElementById('hipotese');
const relatorioJson = document.getElementById('relatorio-json');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(form);
  const payload = {
    nome: formData.get('nome'),
    idade: formData.get('idade'),
    genero: formData.get('genero'),
    alergias: formData.get('alergias'),
    gestante: formData.get('gestante') === 'on',
    sintomas: Array.from(document.querySelectorAll('.chip.active')).map(b => b.textContent),
    descricaoLivre: formData.get('descricaoLivre')
  };

  const resp = await fetch('/api/triage', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  const data = await resp.json();
  showResult(data);
});

function showResult(data) {
  resultado.hidden = false;
  const lvl = (data.resultado?.classificacaoRisco || '').toLowerCase();
  let cls = '';
  if (lvl.includes('vermelho')) cls = 'badge-vermelho';
  else if (lvl.includes('amarelo')) cls = 'badge-amarelo';
  else if (lvl.includes('verde')) cls = 'badge-verde';
  else cls = 'badge-azul';

  riskBadge.className = cls;
  riskBadge.textContent = `Risco: ${data.resultado.classificacaoRisco}`;
  recomendacao.textContent = data.resultado.recomendacao;
  hipotese.textContent = `Possível problema principal: ${data.resultado.hipotesePrincipal}`;
  relatorioJson.textContent = JSON.stringify(data, null, 2);
  window.scrollTo({ top: resultado.offsetTop - 12, behavior: 'smooth' });
}

document.getElementById('btn-imprimir').onclick = () => window.print();
document.getElementById('btn-nova').onclick = () => window.location.reload();