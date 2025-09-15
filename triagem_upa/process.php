<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

$nome = $_POST['nome'];
$idade = intval($_POST['idade']);
$peso = floatval($_POST['peso']);
$altura = str_replace(",", ".", $_POST['altura']); // aceita vírgula ou ponto
$altura = floatval($altura);
$genero = $_POST['genero'];
$sintomas = strtolower($_POST['sintomas']);

// Calcular IMC
$imc = 0;
if ($altura > 0) {
    $imc = $peso / ($altura * $altura);
}

// Classificação básica por sintomas
$classificacao = "Verde";
$descricao = "Situação estável, sem sinais de urgência imediata.";
$cor = "success";

if (strpos($sintomas, "dor no peito") !== false || strpos($sintomas, "falta de ar") !== false) {
    $classificacao = "Vermelho";
    $descricao = "Risco imediato à vida. Atendimento emergencial necessário.";
    $cor = "danger";
} elseif (strpos($sintomas, "febre") !== false || strpos($sintomas, "tontura") !== false) {
    $classificacao = "Amarelo";
    $descricao = "Necessário atendimento médico em breve.";
    $cor = "warning";
}

// Resultado detalhado
$resultado = "<ul>";
$resultado .= "<li><strong>Nome:</strong> {$nome}</li>";
$resultado .= "<li><strong>Idade:</strong> {$idade} anos</li>";
$resultado .= "<li><strong>Gênero:</strong> {$genero}</li>";
$resultado .= "<li><strong>Peso:</strong> {$peso} kg</li>";
$resultado .= "<li><strong>Altura:</strong> {$altura} m</li>";
$resultado .= "<li><strong>IMC:</strong> " . number_format($imc, 2) . "</li>";
$resultado .= "<li><strong>Sintomas relatados:</strong> {$sintomas}</li>";
$resultado .= "</ul>";

$parecer = "<p>Com base nas informações fornecidas e nos sintomas descritos, a situação do paciente foi classificada como: <span class='badge bg-{$cor} fs-5'>{$classificacao}</span>.</p>";
$parecer .= "<p><strong>Análise clínica detalhada:</strong></p>";
$parecer .= "<ol>";
$parecer .= "<li>Avaliação geral do estado clínico considerando idade e IMC.</li>";
$parecer .= "<li>Observação dos sintomas principais e possíveis causas associadas.</li>";
$parecer .= "<li>Recomenda-se avaliação médica presencial para confirmação diagnóstica.</li>";
$parecer .= "<li>Manter monitoramento contínuo do paciente até atendimento médico.</li>";
$parecer .= "</ol>";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resultado da Triagem</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body class="bg-result">
<div class="container py-5">
    <div class="card shadow-lg p-4">
        <h3 class="text-primary mb-4">Resultado da Triagem</h3>
        <?php echo $resultado; ?>
        <hr>
        <?php echo $parecer; ?>
        <div class="mt-4">
            <a href="triage.php" class="btn btn-success">Nova Triagem</a>
            <a href="logout.php" class="btn btn-secondary">Sair</a>
        </div>
    </div>
</div>
</body>
</html>
