<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

iniciar_sesion();

if (usuario_actual()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión expiró. Volvé a intentar.';
    } else {
        $r = intentar_login(trim((string) ($_POST['username'] ?? '')), (string) ($_POST['password'] ?? ''));
        if ($r['ok']) {
            redirect(!empty($r['must_change']) ? 'password.php' : 'dashboard.php');
        }
        $error = $r['error'];
    }
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Acceso del equipo · Esquel LAB</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="../<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="login-wrap">
  <form class="login-card" method="post" novalidate>
    <img src="../assets/images/logo-esquel-lab.png" alt="Esquel LAB" class="login-logo">
    <h1>Acceso del equipo</h1>
    <p class="login-sub">Panel de evaluación de postulaciones.</p>

    <?php if ($error): ?><div class="form-alert error" style="text-align:left"><?= e($error) ?></div><?php endif; ?>

    <?= csrf_field() ?>
    <div class="field">
      <label class="lbl" for="username">Usuario</label>
      <input type="text" id="username" name="username" autocomplete="username" required autofocus>
    </div>
    <div class="field">
      <label class="lbl" for="password">Contraseña</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
    <a href="../index.php" class="login-back">← Volver al sitio</a>
  </form>
</div>
</body>
</html>
