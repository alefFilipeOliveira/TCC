const SYMPTOMS = [
  'febre', 'febre alta', 'calafrios', 'tosse', 'tosse seca', 'tosse com catarro',
  'falta de ar', 'chiado', 'dor no peito', 'dor abdominal', 'vomito persistente',
  'diarreia com sangue', 'rigidez de pescoco', 'dor de cabeca forte', 'manchas vermelhas',
  'confusao', 'desmaio', 'sangramento intenso', 'dor ao urinar', 'ardor ao urinar',
  'coriza', 'nariz entupido', 'coceira'
];
const tokens = () => ({ token: localStorage.getItem('token'), user: JSON.parse(localStorage.getItem('user') || 'null') });
const t = tokens();
if (!t.token) window.location.href = '/';

document.getElementById('user-info').textContent = `${t.user.name} (${t.user.role})`;
document.getElementById('logout').onclick = () => { localStorage.clear(); window.location.href = '/'; };

const chipsContainer = document.getElementById('symptom-chips');
SYMPTOMS.forEach(s => {
  const b = document.createElement('button');
  b.type = 'button'; b.textContent = s; b.className = 'chip';
  b.onclick = () => b.classList.toggle('active');
  chipsContainer.appendChild(b);
});

const form = document.getElementById('triage-form');
const resultado = document.getElementById('resultado');
const riskBadge = document.getElementById('risk-badge');
const recomendacao = document.getElementById('recomendacao');
const hipotese = document.getElementById('hipotese');
const relatorioJson = document.getElementById('relatorio-json');
const btnPdf = document.getElementById('btn-pdf');
const btnNova = document.getElementById('btn-nova');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(form);
  const payload = {
    nome: fd.get('nome'),
    idade: fd.get('idade'),
    genero: fd.get('genero'),
    alergias: fd.get('alergias'),
    gestante: fd.get('gestante') === 'on',
    sintomas: Array.from(document.querySelectorAll('.chip.active')).map(b => b.textContent),
    descricaoLivre: fd.get('descricaoLivre')
  };
  const resp = await fetch('/api/triage', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${t.token}` },
    body: JSON.stringify(payload)
  });
  const data = await resp.json();
  if (!resp.ok) { alert(data.error || 'Erro'); return; }
  showResult(data);
  // refresh patients list
  await fetchPatients();
});

function showResult(data) {
  resultado.hidden = false;
  const lvl = (data.resultado?.classificacaoRisco || '').toLowerCase();
  let cls = 'badge-azul';
  if (lvl.includes('vermelho')) cls = 'badge-vermelho';
  else if (lvl.includes('amarelo')) cls = 'badge-amarelo';
  else if (lvl.includes('verde')) cls = 'badge-verde';
  riskBadge.className = cls;
  riskBadge.textContent = `Risco: ${data.resultado.classificacaoRisco}`;
  recomendacao.textContent = data.resultado.recomendacao;
  hipotese.textContent = `Possível problema principal: ${data.resultado.hipotesePrincipal}`;
  relatorioJson.textContent = JSON.stringify(data, null, 2);
  btnPdf.href = `/api/triage/report/${data.triageId}.pdf`;
  btnNova.onclick = () => window.location.reload();
  window.scrollTo({ top: resultado.offsetTop - 12, behavior: 'smooth' });
}

// Patients & history
const patientsEl = document.getElementById('patients');
const historyEl = document.getElementById('history');
document.getElementById('btn-search').onclick = fetchPatients;
async function fetchPatients() {
  const q = document.getElementById('search').value;
  const resp = await fetch('/api/patients' + (q ? `?q=${encodeURIComponent(q)}` : ''), {
    headers: { 'Authorization': `Bearer ${t.token}` }
  });
  const data = await resp.json();
  patientsEl.innerHTML = '';
  (data.patients || []).forEach(p => {
    const li = document.createElement('li');
    li.innerHTML = `<strong>${p.name}</strong> <span class="muted">#${p.id} • ${p.genero || ''} • ${p.idade ?? ''}</span>`;
    li.onclick = () => fetchHistory(p.id);
    patientsEl.appendChild(li);
  });
}
async function fetchHistory(patientId) {
  const resp = await fetch(`/api/triage/history/${patientId}`, {
    headers: { 'Authorization': `Bearer ${t.token}` }
  });
  const data = await resp.json();
  historyEl.innerHTML = '';
  (data.triages || []).forEach(tg => {
    const li = document.createElement('li');
    const r = tg.relatorio?.resultado || {};
    li.innerHTML = `#${tg.id} • ${new Date(tg.createdAt).toLocaleString()} • Risco: ${r.classificacaoRisco} • <a href="/api/triage/report/${tg.id}.pdf" target="_blank">PDF</a>`;
    historyEl.appendChild(li);
  });
}
// initial load
fetchPatients();