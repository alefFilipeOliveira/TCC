<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Triagem - UPA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body class="bg-triage">
<div class="container py-5">
    <div class="card shadow-lg p-4">
        <h3 class="mb-4 text-primary">Triagem do Paciente</h3>
        <form action="process.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" name="nome" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Idade</label>
                    <input type="number" class="form-control" name="idade" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Peso (kg)</label>
                    <input type="number" step="0.1" class="form-control" name="peso" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Altura (m)</label>
                    <input type="text" class="form-control" name="altura" placeholder="Ex: 1,75" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gênero</label>
                    <select class="form-select" name="genero" required>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Sintomas</label>
                <textarea class="form-control" name="sintomas" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Analisar</button>
            <a href="logout.php" class="btn btn-secondary">Sair</a>
        </form>
    </div>
</div>
</body>
</html>
