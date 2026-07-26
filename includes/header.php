<?php
/**
 * Header público.
 * La página que lo incluye define antes: $pageTitle, $pageDescription, $activeNav.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/analitica.php';

// Registro de visita. Si la analítica falla devuelve null y la página sigue.
$visita = registrar_visita((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));

$pageTitle       = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? 'Programa municipal gratuito que acompaña a emprendedores y productores de Esquel a convertir lo que ya hacen en una experiencia turística lista para recibir visitantes.';
$activeNav       = $activeNav ?? '';
$base            = $base ?? '';
?><!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#ab2759">
<link rel="icon" href="<?= $base ?>assets/images/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?><?= asset('assets/css/style.css') ?>">
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<header class="site-header">
  <div class="container header-inner">
    <a href="<?= $base ?>index.php" class="brand" aria-label="Esquel LAB — inicio">
      <img src="<?= $base ?>assets/images/logo-esquel-lab-horizontal.png" alt="Esquel LAB" class="brand-logo">
    </a>

    <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="siteNav" aria-label="Abrir menú">
      <span></span><span></span><span></span>
    </button>

    <nav class="site-nav" id="siteNav">
      <a href="<?= $base ?>index.php#para-vos" <?= $activeNav === 'para-vos' ? 'class="is-active"' : '' ?>>¿Es para vos?</a>
      <a href="<?= $base ?>index.php#que-te-llevas">Qué te llevás</a>
      <a href="<?= $base ?>index.php#preguntas">Preguntas</a>
      <a href="<?= $base ?>media-kit.php" <?= $activeNav === 'prensa' ? 'class="is-active"' : '' ?>>Pasá la voz</a>
      <a href="<?= $base ?>inscribirse.php" class="btn btn-primary btn-sm">Postularme</a>
    </nav>
  </div>
</header>

<main id="contenido">
