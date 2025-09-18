
<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: triagem.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u === 'EnfermeiroUpa2' && $p === 'Upa2') {
        $_SESSION['loggedin'] = true;
        header('Location: triagem.php');
        exit;
    } else {
        $error = 'Usuário ou senha incorretos.';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login — Triagem UPA v7</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" href="favicon.ico">
<link href="style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="card shadow-lg p-4" style="max-width:640px; width:100%; border-radius:14px;">
    <div class="d-flex align-items-center gap-3 mb-3">
      <img src="assets/hero.png" alt="logo" style="height:84px; border-radius:8px;">
      <div>
        <h2 class="mb-0">Sistema de Triagem UPA</h2>
        <div class="text-muted small">Dashboard de acesso — profissionais de saúde</div>
      </div>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Usuário</label><input class="form-control" name="username" required value="EnfermeiroUpa2"></div>
        <div class="col-md-6"><label class="form-label">Senha</label><input class="form-control" type="password" name="password" required value="Upa2"></div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary btn-lg" type="submit" id="loginBtn"><span id="loginBtnText">Entrar</span><span id="loginSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span></button>
        <a href="#" class="btn btn-outline-secondary ms-auto">Suporte</a>
      </div>
    </form>
    <div class="mt-3 text-muted small">Protótipo educacional — use apenas para demonstração.</div>
  </div>
</div>
<script>
document.getElementById('loginBtn').addEventListener('click', function(){ document.getElementById('loginBtnText').innerText='Carregando...'; document.getElementById('loginSpinner').classList.remove('d-none'); });
</script>
</body>
</html>
