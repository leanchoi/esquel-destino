<?php
/**
 * Layout del panel. Requiere que la página ya haya llamado a requiere_login()
 * o requiere_rol() (password.php es la excepción: la usa con sesión activa
 * pero sin exigir el cambio hecho).
 */
$u = usuario_actual();
$pageTitle = $pageTitle ?? 'Panel';
$nav = $nav ?? '';
?><!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · Panel Esquel LAB</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="../<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

<header class="admin-header">
  <div class="admin-header-inner">
    <a href="dashboard.php" class="admin-brand">
      <img src="../assets/images/logo-esquel-lab.png" alt="Esquel LAB">
    </a>
    <nav class="admin-nav">
      <a href="dashboard.php" class="<?= $nav === 'postulaciones' ? 'is-active' : '' ?>">Postulaciones</a>
      <a href="analitica.php" class="<?= $nav === 'analitica' ? 'is-active' : '' ?>">Analítica</a>
      <?php if (puede('admin')): ?>
        <a href="usuarios.php" class="<?= $nav === 'usuarios' ? 'is-active' : '' ?>">Usuarios</a>
      <?php endif; ?>
    </nav>
    <div class="admin-user">
      <span><strong><?= e($u['username'] ?? '') ?></strong> · <?= e($u['role'] ?? '') ?></span>
      <a href="password.php">Contraseña</a>
      <a href="logout.php" class="admin-logout">Salir</a>
    </div>
  </div>
</header>

<main class="admin-main">
