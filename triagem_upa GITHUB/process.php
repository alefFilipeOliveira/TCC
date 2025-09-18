
<?php
session_start();
if (empty($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}
$config = [];
if (file_exists('config.php')) {
    $cfg = include 'config.php';
    if (is_array($cfg)) $config = $cfg;
}

$nome = trim($_POST['nome'] ?? '');
$idade = intval($_POST['idade'] ?? 0);
$peso = floatval(str_replace(',', '.', $_POST['peso'] ?? '0'));
$altura = floatval(str_replace(',', '.', $_POST['altura'] ?? '0'));
$genero = $_POST['genero'] ?? '';
$conditions = [];
if (!empty($_POST['conditions_json'])) {
    $conditions = json_decode($_POST['conditions_json'], true) ?? [];
}

// compute BMI
$bmi = 0.0;
if ($altura > 0) $bmi = $peso / ($altura * $altura);

// Build an expanded clinical report
$date = date('Y-m-d H:i:s');
$report_lines = [];
$report_lines[] = "Relatório Clínico Estruturado - Sistema UPA";
$report_lines[] = "Data/Hora: $date";
$report_lines[] = "Paciente: $nome";
$report_lines[] = "Idade: $idade anos";
$report_lines[] = "Gênero: $genero";
$report_lines[] = "Peso: {$peso} kg | Altura: " . ($altura>0?number_format($altura,2):"0.00") . " m";
$report_lines[] = "IMC estimado: " . number_format($bmi,1);
$report_lines[] = "";
$report_lines[] = "Condições/Sintomas informados:";
if (!empty($conditions)) {
    foreach ($conditions as $c) $report_lines[] = " - " . $c;
} else {
    $report_lines[] = " - Nenhuma condição selecionada";
}
$report_lines[] = "";
$report_lines[] = "Sumário clínico objetivo:";
$report_lines[] = " - História resumida: formulário estruturado preenchido pelo profissional; condições selecionadas foram utilizadas para análise.";
$report_lines[] = " - Exame físico sugerido: avaliar sinais vitais, frequência cardíaca, saturação de O2, pressão arterial, temperatura, exame cardiopulmonar e inspeção de lesões.";
$report_lines[] = " - Dados que aumentam incerteza: detalhes sobre início dos sintomas, evolução temporal, medicações em uso, comorbidades, alergias e exames prévios.";

$report_text = implode("\n", $report_lines);

// Heuristic fallback triage scoring (structured)
$score = 0;
if ($idade <= 2 || $idade >= 65) $score += 2;
if ($bmi >= 35 || $bmi < 16) $score += 2;
$count_conditions = count($conditions);
if ($count_conditions >= 6) $score += 2;
elseif ($count_conditions >= 3) $score += 1;

$level = 'VERDE';
if ($score >= 5) $level = 'VERMELHO';
elseif ($score >= 3) $level = 'AMARELO';

// Classification textual comments (distinct for each color)
$comments = [
    'VERDE' => [
        'title' => 'Classificação: VERDE — Risco baixo',
        'text' => 'Paciente com sinais e condições que, no presente contexto, sugerem baixo risco de eventos adversos imediatos. Recomenda-se conduta ambulatorial, orientações de autocuidado, retorno se piora ou surgimento de sinais de alarme. Fornecer orientações escritas e agendamento de acompanhamento conforme necessidade.'
    ],
    'AMARELO' => [
        'title' => 'Classificação: AMARELO — Risco moderado',
        'text' => 'Paciente com sinais que merecem avaliação prioritária. Indica necessidade de observação clínica, monitorização de sinais vitais e exames iniciais (laboratoriais/imagem) conforme suspeita clínica. Considerar avaliação por médico em curto prazo e preparo para intervenções diagnósticas.'
    ],
    'VERMELHO' => [
        'title' => 'Classificação: VERMELHO — Risco alto / emergência',
        'text' => 'Paciente com sinais que podem indicar risco imediato à vida. Encaminhar imediatamente para atendimento de emergência, iniciar suporte (via aérea, ventilação, acesso venoso) conforme necessidade, monitorização contínua e contatar equipe de plantão.'
    ]
];

$comment = $comments[$level];

// Create a more complex diagnostic inference: if AI available, call it; otherwise build a multi-part structured diagnostic suggestion
$openai_out = null;
$openai_key = $config['OPENAI_API_KEY'] ?? '';
$model = $config['MODEL'] ?? 'gpt-4o-mini';

if (!empty($openai_key)) {
    $system = "Você é um clínico sênior que elabora relatórios médicos a partir de listas de condições/sintomas e dados básicos (idade, IMC). Baseado no relatório abaixo, gere:
1) Diagnóstico mais provável com nível de confiança (baixo/moderado/alto);
2) Até 4 diagnósticos diferenciais com justificativa; 
3) Sinais de alarme que justificam triagem VERMELHO;
4) Exames iniciais prioritários (laboratoriais e de imagem);
5) Plano de conduta imediato e orientações ao paciente (5-8 itens);
6) Justifique a classificação de risco (VERDE/AMARELO/VERMELHO).
Apresente a resposta em português, estrutura em tópicos e com linguagem clínica profissional. Não forneça doses de medicamentos controlados.";
    $user = "Relatório:\n\n" . $report_text;
    $payload = json_encode([
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user]
        ],
        'temperature' => 0.0,
        'max_tokens' => 1400
    ]);
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $openai_key]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false || $err) {
        $openai_out = null;
    } else {
        $j = json_decode($resp, true);
        if (isset($j['choices'][0]['message']['content'])) $openai_out = $j['choices'][0]['message']['content'];
    }
}

// If no AI response, create a composed diagnostic suggestion locally
if (empty($openai_out)) {
    // Build a local multi-part diagnostic suggestion based on conditions count and key syndromes
    $diagnostic = [];
    $diagnostic[] = "DIAGNÓSTICO SUSPEITO (baseado na lista):";
    if ($count_conditions == 0) {
        $diagnostic[] = "Sem condições selecionadas — requer avaliação clínica direta para definição.";
    } else {
        // infer simple syndrome clusters
        $joined = implode(' | ', $conditions);
        $diagnostic[] = "Síndromes prováveis (resumidas):";
        // respiratory cluster
        $resp_keywords = ['Tosse','Dispneia','Expectoração','Broncoespasmo','Pneumonia','Pneumotórax','COVID-19','Tuberculose'];
        $has_resp = false;
        foreach ($resp_keywords as $k) if (stripos($joined,$k)!==false) $has_resp = true;
        if ($has_resp) $diagnostic[] = " - Síndrome respiratória aguda possível (infecção, pneumonia, broncoespasmo).";
        // bleeding cluster
        $bleed_keywords = ['Sangramento','Hemoptise','Hemorragia','Hematúria','Sangramento nasal'];
        $has_bleed = false;
        foreach ($bleed_keywords as $k) if (stripos($joined,$k)!==false) $has_bleed = true;
        if ($has_bleed) $diagnostic[] = " - Quadro hemorrágico/lesão ativa — avaliar estabilidade hemodinâmica.";
        // neuro cluster
        $neuro_keywords = ['Convuls','Perda de visão','Confusão','Síncope','Alteração de linguagem','Fraqueza unilateral'];
        $has_neuro = false;
        foreach ($neuro_keywords as $k) if (stripos($joined,$k)!==false) $has_neuro = true;
        if ($has_neuro) $diagnostic[] = " - Sintomas neurológicos que exigem avaliação urgente (AVC, crise convulsiva, comprometimento neurológico).";
        // general
        if (!$has_resp && !$has_bleed && !$has_neuro) $diagnostic[] = " - Múltiplas condições não específicas — investigação complementar necessária.";
    }

    $openai_out = implode("\n", $diagnostic);
}

// Render the result HTML with animated classification effects and detailed sections
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Resultado Triagem v7</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" href="favicon.ico">
<link href="style.css" rel="stylesheet">
<style>
/* classification effects */
.class-VERDE { box-shadow:0 8px 30px rgba(34,197,94,0.15); border-left:6px solid #10b981; animation: glowGreen 1.6s ease-in-out infinite; }
.class-AMARELO { box-shadow:0 8px 30px rgba(245,158,11,0.12); border-left:6px solid #f59e0b; animation: glowYellow 1.6s ease-in-out infinite; }
.class-VERMELHO { box-shadow:0 8px 30px rgba(239,68,68,0.14); border-left:6px solid #ef4444; animation: glowRed 1.6s ease-in-out infinite; }

@keyframes glowGreen { 0% { box-shadow:0 4px 12px rgba(16,185,129,0.06);} 50% { box-shadow:0 20px 50px rgba(16,185,129,0.12);} 100% { box-shadow:0 4px 12px rgba(16,185,129,0.06);} }
@keyframes glowYellow { 0% { box-shadow:0 4px 12px rgba(245,158,11,0.04);} 50% { box-shadow:0 20px 50px rgba(245,158,11,0.09);} 100% { box-shadow:0 4px 12px rgba(245,158,11,0.04);} }
@keyframes glowRed { 0% { box-shadow:0 4px 12px rgba(239,68,68,0.05);} 50% { box-shadow:0 20px 50px rgba(239,68,68,0.11);} 100% { box-shadow:0 4px 12px rgba(239,68,68,0.05);} }

/* button effects */
.btn-animated { position: relative; overflow: hidden; transition: transform .12s ease; }
.btn-animated:active { transform: scale(.98); }
.spinner-overlay { position: absolute; left:50%; top:50%; transform:translate(-50%,-50%); }

</style>
</head><body class="result-page">
<nav class="navbar navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#"><img src="assets/hero.png" style="height:44px; border-radius:8px; margin-right:10px;"><div><strong>Sistema de Triagem UPA</strong><div class="small text-muted">Resultado</div></div></a>
    <div><a href="triagem.php" class="btn btn-outline-secondary">Nova triagem</a> <a href="logout.php" class="btn btn-outline-secondary">Logout</a></div>
  </div>
</nav>
<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card p-4 shadow-sm class-<?php echo $level; ?>">
        <h4>Relatório clínico estruturado</h4>
        <pre class="bg-light p-3" style="white-space:pre-wrap;"><?php echo htmlspecialchars($report_text); ?></pre>
        <div class="mt-3">
          <h5><?php echo htmlspecialchars($comment['title']); ?></h5>
          <p><?php echo htmlspecialchars($comment['text']); ?></p>
        </div>
        <hr>
        <h5>Diagnóstico e justificativa</h5>
        <div class="p-3" style="background:#fbfdff;border-radius:8px;"><?php echo nl2br(htmlspecialchars($openai_out)); ?></div>
        <hr>
        <h5>Observações clínicas detalhadas</h5>
        <ul>
          <li><strong>Dados pendentes importantes:</strong> início exato dos sintomas, comorbidades, medicações em uso, alergias, registros de exames prévios.</li>
          <li><strong>Recomendações sobre documentação:</strong> registrar sinais vitais, imagens e anotações de evolução a cada 30-60 minutos em casos de risco.</li>
          <li><strong>Comunicação com a família:</strong> informar claramente o nível de urgência e orientar quanto a sinais de piora.</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card p-4 shadow-sm sticky-top">
        <h5>Sumário</h5>
        <ul class="list-unstyled">
          <li><strong>Paciente:</strong> <?php echo htmlspecialchars($nome); ?></li>
          <li><strong>Idade:</strong> <?php echo $idade; ?> anos</li>
          <li><strong>IMC:</strong> <?php echo number_format($bmi,1); ?></li>
          <li><strong>Triagem:</strong> <span class="badge bg-<?php echo $color; ?>"><?php echo $level; ?></span></li>
        </ul>
        <div class="mt-3">
          <button class="btn btn-primary btn-animated w-100" onclick="downloadReport()">Baixar relatório (.txt)</button>
        </div>
        <div class="mt-3 text-muted small"><strong>Atenção:</strong> confirme sempre com avaliação clínica presencial e exames complementares.</div>
      </div>
    </div>
  </div>
</div>

<script>
function downloadReport(){
    const text = `Relatório de Triagem\n\n` + `<?php echo addslashes($report_text); ?>` + `\n\nClassificação: <?php echo $level; ?>\n\nDiagnóstico:\n<?php echo addslashes($openai_out); ?>`;
    const blob = new Blob([text], {type: 'text/plain'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'relatorio_triagem_<?php echo date('Ymd_His'); ?>.txt';
    a.click();
}
</script>
</body>
</html>
