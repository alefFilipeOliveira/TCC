
<?php
session_start();
if (empty($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Triagem v7 — UPA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" href="favicon.ico">
<link href="style.css" rel="stylesheet">
</head>
<body class="triage-page">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="assets/hero.png" style="height:56px; border-radius:8px; margin-right:12px;">
      <div><strong>Sistema de Triagem UPA</strong><div class="small text-muted">Painel do Enfermeiro</div></div>
    </a>
    <div class="ms-auto"><a href="logout.php" class="btn btn-outline-secondary">Logout</a></div>
  </div>
</nav>

<header class="hero p-5 text-white" style="background-image: linear-gradient(90deg, rgba(7,90,150,0.92), rgba(40,130,200,0.82)), url('assets/bg.jpg'); background-size: cover; background-position: center;">
  <div class="container">
    <h1>Triagem clínica — relatório estruturado</h1>
    <p class="lead">Escolha condições a partir do campo pesquisável e gere um relatório clínico completo e padronizado. A IA (se configurada) analisará a lista selecionada.</p>
  </div>
</header>

<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card p-4 shadow-sm">
        <form action="process.php" method="post" id="triageForm">
          <div class="mb-3">
            <label class="form-label">Nome do paciente</label>
            <input name="nome" class="form-control" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-4"><label class="form-label">Idade</label><input name="idade" type="number" class="form-control" min="0" required></div>
            <div class="col-md-4"><label class="form-label">Peso (kg)</label><input name="peso" type="number" step="0.1" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Altura (m)</label><input name="altura" class="form-control" placeholder="1,75 ou 1.75" required></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Gênero</label>
            <select name="genero" class="form-select"><option>Masculino</option><option>Feminino</option><option>Outro</option></select>
          </div>
          <hr>
          <h5>Adicionar condições (pesquise e adicione)</h5>
          <div class="mb-2 d-flex gap-2">
            <input id="conditionInput" list="conditionsList" class="form-control" placeholder="Comece a digitar (ex: febre)" />
            <button type="button" id="addConditionBtn" class="btn btn-outline-primary">Adicionar</button>
          </div>
          <datalist id="conditionsList">
            <option value="Febre"></option>
            <option value="Tosse"></option>
            <option value="Dor de garganta"></option>
            <option value="Coriza"></option>
            <option value="Congestão nasal"></option>
            <option value="Dispneia (falta de ar)"></option>
            <option value="Dor no peito"></option>
            <option value="Taquicardia"></option>
            <option value="Bradicardia"></option>
            <option value="Palpitações"></option>
            <option value="Síncope (desmaio)"></option>
            <option value="Tontura"></option>
            <option value="Cefaleia (dor de cabeça)"></option>
            <option value="Rigidez de nuca"></option>
            <option value="Convulsões"></option>
            <option value="Fraqueza unilateral"></option>
            <option value="Dormência ou parestesia"></option>
            <option value="Alteração de linguagem"></option>
            <option value="Visão turva"></option>
            <option value="Perda de visão"></option>
            <option value="Zumbido"></option>
            <option value="Sangramento nasal"></option>
            <option value="Hemoptise (tosse com sangue)"></option>
            <option value="Hemorragia digestiva"></option>
            <option value="Hemorragia vaginal anormal"></option>
            <option value="Sangramento gengival"></option>
            <option value="Náusea"></option>
            <option value="Vômito persistente"></option>
            <option value="Dor abdominal intensa"></option>
            <option value="Distensão abdominal"></option>
            <option value="Icterícia"></option>
            <option value="Perda de apetite"></option>
            <option value="Perda de peso inexplicada"></option>
            <option value="Ganhos de peso rápidos"></option>
            <option value="Dor nas articulações"></option>
            <option value="Inchaço nas articulações"></option>
            <option value="Rigidez matinal prolongada"></option>
            <option value="Erupção cutânea"></option>
            <option value="Prurido (coceira)"></option>
            <option value="Lesões cutâneas que não cicatrizam"></option>
            <option value="Feridas que sangram facilmente"></option>
            <option value="Tosse crônica (>8 semanas)"></option>
            <option value="Expectoração purulenta"></option>
            <option value="Fadiga extrema"></option>
            <option value="Insônia"></option>
            <option value="Sonolência diurna excessiva"></option>
            <option value="Alteração do comportamento"></option>
            <option value="Ansiedade intensa"></option>
            <option value="Depressão"></option>
            <option value="Ideação suicida"></option>
            <option value="Confusão mental"></option>
            <option value="Alteração do nível de consciência"></option>
            <option value="Suores noturnos"></option>
            <option value="Calafrios"></option>
            <option value="Linfoadenopatia (inchaço de gânglios)"></option>
            <option value="Dor lombar"></option>
            <option value="Hematúria (sangue na urina)"></option>
            <option value="Urgência miccional"></option>
            <option value="Poliúria (urinar muito)"></option>
            <option value="Disúria (dor ao urinar)"></option>
            <option value="Incontinência urinária"></option>
            <option value="Secreção genital anormal"></option>
            <option value="Dor pélvica"></option>
            <option value="Dificuldade para engravidar"></option>
            <option value="Claudicação (dor ao caminhar)"></option>
            <option value="Edema de membros inferiores"></option>
            <option value="Varizes"></option>
            <option value="Trombose venosa profunda suspeita"></option>
            <option value="Dor muscular (mialgia)"></option>
            <option value="Fraqueza muscular progressiva"></option>
            <option value="Perda de força"></option>
            <option value="Alteração da marcha"></option>
            <option value="Lesão traumática recente"></option>
            <option value="Perda de mobilidade"></option>
            <option value="Queimaduras"></option>
            <option value="Feridas contaminadas"></option>
            <option value="Queixas odontológicas agudas"></option>
            <option value="Mau hálito severo"></option>
            <option value="Sintomas alérgicos agudos (anafilaxia)"></option>
            <option value="Dor ocular"></option>
            <option value="Olho vermelho"></option>
            <option value="Perda súbita de audição"></option>
            <option value="Dor auricular"></option>
            <option value="Corrimento auricular"></option>
            <option value="Secreção purulenta ocular"></option>
            <option value="Sensação de corpo estranho ocular"></option>
            <option value="Problemas de equilíbrio"></option>
            <option value="Dificuldade para engolir"></option>
            <option value="Refluxo severo"></option>
            <option value="Úlceras orais"></option>
            <option value="Rouquidão persistente"></option>
            <option value="Sintomas gripais generalizados"></option>
            <option value="Exposição a animais/feridas por animais"></option>
            <option value="Picadas de insetos infectadas"></option>
            <option value="Problemas de pele por contato"></option>
            <option value="Hematomas fáceis"></option>
            <option value="Uso de anticoagulantes"></option>
            <option value="Distúrbios menstruais"></option>
            <option value="Sintomas na gravidez"></option>
            <option value="Alterações endócrinas (sinais de hipotireoidismo)"></option>
            <option value="Sudorese excessiva"></option>
            <option value="Aumento de sede (polidipsia)"></option>
            <option value="Sintomas respiratórios noturnos"></option>
            <option value="Broncoespasmo"></option>
            <option value="Pneumotórax suspeito"></option>
            <option value="Sinais de sepse (hipotensão, taquicardia, febre)"></option>
            <option value="Confusão associada à infecção"></option>
            <option value="Lesões mamárias"></option>
            <option value="Secreção mamilar"></option>
            <option value="Nódulo mamário palpável"></option>
            <option value="Dor testicular"></option>
            <option value="Massa palpável abdominal"></option>
            <option value="Ascite (aumento de barriga)"></option>
            <option value="Icterícia progressiva"></option>
            <option value="Sinais neurológicos progressivos"></option>
            <option value="Perda auditiva progressiva"></option>
            <option value="Sinais de demência"></option>
            <option value="Queda de cabelo intensa"></option>
            <option value="Hirsutismo"></option>
            <option value="Sintomas oftalmológicos progressivos"></option>
            <option value="Dor em queimadura de sol extensiva"></option>
            <option value="Reações adversas a medicamentos"></option>
            <option value="Intolerâncias alimentares"></option>
            <option value="Sintomas gastrointestinais crônicos"></option>
            <option value="Esteatorreia (fezes oleosas)"></option>
            <option value="Sinais de desidratação"></option>
            <option value="Hipotermia"></option>
            <option value="Bradicinesia (lentidão)"></option>
            <option value="Sintomas reumatológicos (vasculite suspeita)"></option>
            <option value="Sintomas psiquiátricos graves"></option>
            <option value="Trauma craniano recente"></option>
            <option value="Lesão por esmagamento"></option>
            <option value="Síncope relacionada ao exercício"></option>
            <option value="Falta de resposta a tratamentos prévios"></option>
            <option value="História de câncer prévio"></option>
            <option value="Nódulos ou massas suspeitas"></option>
            <option value="Perda auditiva súbita"></option>
            <option value="Alterações do paladar"></option>
            <option value="Alterações olfativas (anosmia persistente)"></option>
            <option value="Sintomas associados a HIV/Imunossupressão"></option>
            <option value="Exposição a TB"></option>
            <option value="Tosse noturna persistente"></option>
            <option value="Problemas de cicatrização"></option>
            <option value="Febre de origem desconhecida"></option>
            <option value="Reações transfusionais"></option>
            <option value="Sintomas relacionados a transplante"></option>
            <option value="Sinais de choque hipovolêmico"></option>
            <option value="Leucemia"></option>
            <option value="Linfoma"></option>
            <option value="Carcinoma de pulmão"></option>
            <option value="Carcinoma colorretal"></option>
            <option value="Carcinoma de mama"></option>
            <option value="Carcinoma de próstata"></option>
            <option value="Câncer de pâncreas"></option>
            <option value="Câncer de estômago"></option>
            <option value="Melanoma"></option>
            <option value="Glioma"></option>
            <option value="Tumor hepático"></option>
            <option value="Metástase óssea"></option>
            <option value="Sarcoma"></option>
            <option value="Tumor de células germinativas"></option>
            <option value="AIDS"></option>
            <option value="Infecção por Influenza"></option>
            <option value="COVID-19 (sintomas compatíveis)"></option>
            <option value="Tuberculose"></option>
            <option value="Hepatite viral"></option>
            <option value="Malária"></option>
            <option value="Zika"></option>
            <option value="Dengue severa"></option>
            <option value="Septicemia"></option>
            <option value="Meningite"></option>
            <option value="Encefalite"></option>
            <option value="Endocardite"></option>
            <option value="Pneumonia bacteriana"></option>
            <option value="Pneumonia viral"></option>
            <option value="Abscesso"></option>

          </datalist>
          <div id="chipsContainer" class="mb-3" style="min-height:48px"></div>
          <div class="mb-3"><small class="text-muted">Dica: adicione várias condições para uma análise mais rica.</small></div>
          <input type="hidden" name="conditions_json" id="conditions_json" />
          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-success" type="submit" id="analyzeBtn"><span id="analyzeText">Gerar relatório</span><span id="analyzeSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span></button>
            <button type="button" class="btn btn-outline-secondary" id="clearChips">Limpar condições</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card p-4 shadow-sm" id="dashboardCard">
        <h5>Resumo & ações</h5>
        <div class="mb-3"><strong>Itens adicionados:</strong><div id="selectedList" class="mt-2 small text-muted">Nenhum</div></div>
        <div class="mb-3"><strong>Dicas rápidas</strong><ul><li>Adicione as condições mais relevantes.</li><li>Use o relatório para priorizar atendimento.</li></ul></div>
        <div class="mt-3"><a href="logout.php" class="btn btn-outline-secondary w-100">Sair</a></div>
      </div>
    </div>
  </div>
</div>

<script>
// JS to manage chips input and form submission
const conditions = [];
const chipsContainer = document.getElementById('chipsContainer');
const selectedList = document.getElementById('selectedList');
const conditionInput = document.getElementById('conditionInput');
const addConditionBtn = document.getElementById('addConditionBtn');
const clearChips = document.getElementById('clearChips');
const conditionsJson = document.getElementById('conditions_json');
const analyzeBtn = document.getElementById('analyzeBtn');
const analyzeText = document.getElementById('analyzeText');
const analyzeSpinner = document.getElementById('analyzeSpinner');

function renderChips() {
    chipsContainer.innerHTML = '';
    if (conditions.length === 0) {
        selectedList.innerText = 'Nenhum';
    } else {
        selectedList.innerText = conditions.join(', ');
    }
    conditions.forEach((c, idx) => {
        const chip = document.createElement('span');
        chip.className = 'badge bg-primary me-2 mb-2 chip-item';
        chip.style.cursor = 'pointer';
        chip.innerText = c + ' ✕';
        chip.title = 'Clique para remover';
        chip.addEventListener('click', () => { conditions.splice(idx,1); renderChips(); });
        chipsContainer.appendChild(chip);
    });
    conditionsJson.value = JSON.stringify(conditions);
}

addConditionBtn.addEventListener('click', () => {
    const v = conditionInput.value.trim();
    if (v && !conditions.includes(v)) {
        conditions.push(v);
        conditionInput.value = '';
        renderChips();
    }
});

conditionInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); addConditionBtn.click(); }
});

clearChips.addEventListener('click', () => { conditions.length = 0; renderChips(); });

// submit: show loading
document.getElementById('triageForm').addEventListener('submit', function(e){
    if (conditions.length === 0) {
        e.preventDefault();
        alert('Adicione pelo menos uma condição antes de enviar.');
        return;
    }
    analyzeText.innerText = 'Analisando...';
    analyzeSpinner.classList.remove('d-none');
});
</script>
</body>
</html>
