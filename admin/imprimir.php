<?php
/**
 * Vista e Impresión Editorial de Documentos · Esquel LAB 2026.
 *
 * Permite imprimir o exportar a PDF:
 * 1. Plan de Trabajo Estratégico Completo (?tipo=plan&id=[proyecto_id])
 * 2. Minuta de Encuentro Individual (?tipo=reunion&id=[reunion_id])
 *
 * Incluye barra de herramientas para impresión directa y envío por WhatsApp.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$u = requiere_gestion_lab();
$pdo = db();

$tipo = trim((string)($_GET['tipo'] ?? 'plan'));
$id = trim((string)($_GET['id'] ?? ''));

if ($tipo !== 'plan' && $tipo !== 'reunion') {
    $tipo = 'plan';
}

if ($id === '') {
    die('Identificador de documento no proporcionado.');
}

$DRIVE_FOLDER_URL = 'https://drive.google.com/drive/folders/1RjISZm40UnLkYgFIFWzD67smXeghceOP?usp=sharing';

$celulasInfo = [
    1 => ['nombre' => 'Oficio y pieza', 'color' => '#132B43', 'desc' => 'Artesanías, carpintería, diseño y museo vintage'],
    2 => ['nombre' => 'Campo y gran superficie', 'color' => '#2F7D5D', 'desc' => 'Agroturismo, cabalgatas, chacras y laberinto'],
    3 => ['nombre' => 'Relato y territorio', 'color' => '#8A1E47', 'desc' => 'Patrimonio histórico, senderos y gastronomía ancestral'],
    4 => ['nombre' => 'Canal y volumen', 'color' => '#E8A33D', 'desc' => 'Parques aéreos, casas de té, cicloturismo y receptivo']
];

$tiposLabel = [
    'ind' => 'Individual',
    'gru' => 'Célula / Grupal',
    'ter' => 'Terreno',
    'cie' => 'Cierre Plenario'
];

// Cargar consultores
$consultoresRaw = $pdo->query("SELECT * FROM lab_consultores")->fetchAll(PDO::FETCH_ASSOC);
$consultores = [];
foreach ($consultoresRaw as $c) {
    $consultores[$c['id']] = $c;
}

function clean_phone_wa(?string $tel): string {
    if (!$tel) return '';
    $digits = preg_replace('/\D/', '', $tel);
    if (str_starts_with($digits, '549')) return $digits;
    if (str_starts_with($digits, '54')) return '549' . substr($digits, 2);
    if (str_starts_with($digits, '0')) $digits = substr($digits, 1);
    if (str_starts_with($digits, '15')) $digits = substr($digits, 2);
    return '549' . $digits;
}

function fecha_formateada(string $f): string {
    if (!$f) return 'A confirmar';
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($f);
    if (!$ts) return $f;
    $diaSem = $dias[(int)date('w', $ts)];
    $diaNum = (int)date('j', $ts);
    $mesNom = $meses[(int)date('n', $ts)];
    $anio = date('Y', $ts);
    return "$diaSem $diaNum de $mesNom de $anio";
}

function fecha_corta_ar(string $f): string {
    if (!$f) return '';
    $ts = strtotime($f);
    return $ts ? date('d-m-Y', $ts) : $f;
}

// -----------------------------------------------------------------------------
// CASO 1: PLAN DE TRABAJO ESTRATÉGICO COMPLETO
// -----------------------------------------------------------------------------
if ($tipo === 'plan') {
    $stmt = $pdo->prepare("
        SELECT p.*, a.contact_name, a.email, a.phone, a.program, a.stage
        FROM lab_proyectos p
        LEFT JOIN applications a ON a.id = p.application_id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        die('Proyecto no encontrado.');
    }

    $diagnostico = json_decode($p['diagnostico'] ?? '[]', true) ?: [];
    $trabas = json_decode($p['trabas'] ?? '[]', true) ?: [];
    $ejes = json_decode($p['ejes'] ?? '[]', true) ?: [];
    $entregables = json_decode($p['entregables'] ?? '[]', true) ?: [];

    // Cargar todas las reuniones
    $rStmt = $pdo->prepare("
        SELECT r.*, GROUP_CONCAT(ra.consultor_id, ',') AS asistentes_str
        FROM lab_reuniones r
        LEFT JOIN lab_reunion_asistentes ra ON ra.reunion_id = r.id
        WHERE r.proyecto_id = ?
        GROUP BY r.id
        ORDER BY r.numero_reunion ASC, r.fecha ASC
    ");
    $rStmt->execute([$id]);
    $reuniones = $rStmt->fetchAll(PDO::FETCH_ASSOC);

    // Cargar compromisos
    $cStmt = $pdo->prepare("SELECT * FROM lab_compromisos WHERE proyecto_id = ? ORDER BY fecha_limite ASC, id DESC");
    $cStmt->execute([$id]);
    $compromisos = $cStmt->fetchAll(PDO::FETCH_ASSOC);

    $srNom = $consultores[$p['consultor_sr_id']]['nombre'] ?? $p['consultor_sr_id'];
    $jrNom = $consultores[$p['consultor_jr_id']]['nombre'] ?? $p['consultor_jr_id'];
    $celInfo = $celulasInfo[$p['celula']] ?? ['nombre' => 'General', 'color' => '#132B43'];

    // Armar texto de WhatsApp para Plan Completo
    $ejesStr = !empty($ejes) ? implode(', ', array_map(fn($e) => $e['t'] ?? '', array_slice($ejes, 0, 3))) : 'Aceleración técnica y comercial';
    $msgPlanWa = "Hola " . ($p['titular'] ?: 'Emprendedor/a') . "! 👋 Te comparto el *Plan de Trabajo Estratégico* de *" . $p['nombre'] . "* en *Esquel LAB (1ª Cohorte 2026)*.\n\n"
               . "🏛️ *Subsecretaría de Turismo · Municipalidad de Esquel*\n"
               . "• Célula temática: Célula " . $p['celula'] . " (" . $celInfo['nombre'] . ")\n"
               . "• Equipo técnico: " . $srNom . " (Senior) y " . $jrNom . " (Junior)\n"
               . "• Duración: 10 semanas (16 sep → 20 nov 2026)\n"
               . "• Encuentros programados: " . count($reuniones) . " sesiones\n\n"
               . "🎯 *Ejes principales*: " . $ejesStr . "\n"
               . "📦 *Entregables pactados*: " . count($entregables) . " productos verificables\n\n"
               . "Cualquier consulta sobre las fechas y etapas coordinamos con el equipo. ¡Seguimos trabajando juntos!";
    
    $cleanPhone = clean_phone_wa($p['phone']);
    $waUrl = "https://wa.me/" . ($cleanPhone ? $cleanPhone : '') . "?text=" . urlencode($msgPlanWa);
}

// -----------------------------------------------------------------------------
// CASO 2: MINUTA Y PLAYBOOK DE REUNIÓN INDIVIDUAL
// -----------------------------------------------------------------------------
if ($tipo === 'reunion') {
    $stmt = $pdo->prepare("
        SELECT r.*, GROUP_CONCAT(ra.consultor_id, ',') AS asistentes_str,
               p.nombre AS proyecto_nombre, p.titular AS proyecto_titular, p.celula, p.linea, p.puntaje,
               p.consultor_sr_id, p.consultor_jr_id,
               a.phone, a.email, a.contact_name
        FROM lab_reuniones r
        JOIN lab_proyectos p ON p.id = r.proyecto_id
        LEFT JOIN applications a ON a.id = p.application_id
        LEFT JOIN lab_reunion_asistentes ra ON ra.reunion_id = r.id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    $stmt->execute([$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        die('Encuentro no encontrado.');
    }

    $asistentesIds = !empty($r['asistentes_str']) ? explode(',', $r['asistentes_str']) : [];
    $asistentesNombres = array_map(fn($aid) => $consultores[$aid]['nombre'] ?? $aid, $asistentesIds);

    $preguntas = json_decode($r['preguntas_clave'] ?? '[]', true) ?: [];
    $objetivos = json_decode($r['objetivos'] ?? '[]', true) ?: [];
    $checklist = json_decode($r['checklist'] ?? '[]', true) ?: [];

    // Cargar compromisos
    $cStmt = $pdo->prepare("SELECT * FROM lab_compromisos WHERE proyecto_id = ? ORDER BY fecha_limite ASC, id DESC");
    $cStmt->execute([$r['proyecto_id']]);
    $compromisos = $cStmt->fetchAll(PDO::FETCH_ASSOC);

    $celInfo = $celulasInfo[$r['celula']] ?? ['nombre' => 'General', 'color' => '#132B43'];

    // Estandarización de nombre para Google Drive
    // Formato: [PROYECTO_NOMBRE] - REUNION [NUM] - [DD-MM-AAAA]
    $projSlug = mb_strtoupper(preg_replace('/[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑ]/u', ' ', $r['proyecto_nombre']), 'UTF-8');
    $projSlug = trim(preg_replace('/\s+/', ' ', $projSlug));
    $nombreDriveOficial = $projSlug . " - REUNION " . $r['numero_reunion'] . " - " . fecha_corta_ar($r['fecha']);

    // Armar texto de WhatsApp para Minuta Individual
    $tareasDone = array_filter($checklist, fn($it) => !empty($it['done']));
    $tareasTxt = "";
    if (!empty($tareasDone)) {
        foreach (array_slice($tareasDone, 0, 3) as $td) {
            $tareasTxt .= "• " . ($td['texto'] ?? '') . "\n";
        }
    } elseif (!empty($checklist)) {
        foreach (array_slice($checklist, 0, 3) as $td) {
            $tareasTxt .= "• " . ($td['texto'] ?? '') . "\n";
        }
    }

    $msgReuWa = "Hola " . ($r['proyecto_titular'] ?: 'Emprendedor/a') . "! 👋 Te comparto el resumen de nuestro encuentro de acompañamiento en *Esquel LAB*:\n\n"
              . "📅 *Encuentro #" . $r['numero_reunion'] . ": " . $r['titulo'] . "*\n"
              . "• Fecha: " . fecha_corta_ar($r['fecha']) . " (" . ($r['hora_inicio'] ?: 'A conf.') . " a " . ($r['hora_fin'] ?: 'A conf.') . ")\n"
              . "• Lugar: " . $r['lugar'] . "\n"
              . "• Consultores participantes: " . implode(', ', $asistentesNombres) . "\n\n"
              . (!empty($tareasTxt) ? "✅ *Objetivos y avances*: \n" . $tareasTxt . "\n" : "")
              . (!empty($r['minuta_notas']) ? "📝 *Acuerdos principales*: " . mb_substr(strip_tags($r['minuta_notas']), 0, 160) . "...\n\n" : "")
              . "¡Seguimos en contacto para coordinar la próxima cita!";

    $cleanPhone = clean_phone_wa($r['phone']);
    $waUrl = "https://wa.me/" . ($cleanPhone ? $cleanPhone : '') . "?text=" . urlencode($msgReuWa);
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $tipo === 'plan' ? 'Plan de Trabajo · ' . e($p['nombre']) : 'Minuta #' . e($r['numero_reunion']) . ' · ' . e($r['proyecto_nombre']) ?> · Esquel LAB 2026</title>
<link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #1B2A32;
  --ink-2: #3E5363;
  --ink-3: #6B7C87;
  --line: #DCE3E8;
  --line-strong: #1B2A32;
  --surface: #FFFFFF;
  --paper-bg: #F4F7F9;
  --berry: #8A1E47;
  --font-body: 'Atkinson Hyperlegible', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-display: 'Fraunces', Georgia, serif;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: var(--font-body);
  color: var(--ink);
  background-color: var(--paper-bg);
  line-height: 1.45;
  font-size: 13.5px;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

/* BARRA SUPERIOR FLOTANTE (NO SE IMPRIME) */
.toolbar-noprint {
  position: sticky;
  top: 0;
  z-index: 100;
  background: #1B2A32;
  color: #FFFFFF;
  padding: 12px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 10px rgba(0,0,0,0.25);
  font-size: 14px;
}
.tb-left { display: flex; align-items: center; gap: 14px; }
.tb-right { display: flex; align-items: center; gap: 10px; }
.tb-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: opacity .15s;
}
.tb-btn:hover { opacity: 0.9; }
.tb-btn-primary { background: #E8A33D; color: #1B2A32; }
.tb-btn-wa { background: #25D366; color: #FFFFFF; }
.tb-btn-subtle { background: rgba(255,255,255,0.15); color: #FFFFFF; }

/* HOJA DE DOCUMENTO PRINCIPAL */
.doc-page {
  max-width: 960px;
  margin: 24px auto;
  background: var(--surface);
  padding: 44px 50px;
  border-radius: 4px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  border: 1px solid var(--line);
}

/* CABECERA INSTITUCIONAL */
.inst-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  border-bottom: 2px solid var(--line-strong);
  padding-bottom: 18px;
  margin-bottom: 24px;
  gap: 20px;
}
.inst-brand {
  display: flex;
  align-items: center;
  gap: 16px;
}
.inst-logo {
  height: 48px;
  width: auto;
}
.inst-titles h1 {
  font-family: var(--font-display);
  font-size: 22px;
  line-height: 1.15;
  color: var(--ink);
}
.inst-titles p {
  font-size: 12.5px;
  color: var(--ink-2);
  margin-top: 3px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.inst-doc-tag {
  text-align: right;
  font-size: 12px;
  color: var(--ink-3);
}
.inst-doc-tag strong {
  display: block;
  font-size: 13px;
  color: var(--ink);
}

/* BLOQUE PROYECTO (BIG & SMALL DATA) */
.data-card {
  background: #F8FAFC;
  border: 1px solid var(--line);
  border-radius: 6px;
  padding: 18px 20px;
  margin-bottom: 24px;
}
.dc-title-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  border-bottom: 1px solid var(--line);
  padding-bottom: 12px;
  margin-bottom: 12px;
}
.dc-proy-name {
  font-family: var(--font-display);
  font-size: 20px;
  color: var(--berry);
}
.dc-badges { display: flex; gap: 6px; align-items: center; }
.badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  background: #E2E8F0;
  color: #1E293B;
}
.badge-ok { background: #D1FAE5; color: #065F46; }
.badge-cel { color: #FFF; }

.data-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  font-size: 12.5px;
}
.dg-item .lbl { font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--ink-3); display: block; font-weight: 700; }
.dg-item .val { font-size: 13px; font-weight: 700; color: var(--ink); margin-top: 2px; }

/* SECCIONES Y TABLAS */
.sec-title {
  font-family: var(--font-display);
  font-size: 16px;
  color: var(--ink);
  border-bottom: 1.5px solid var(--ink);
  padding-bottom: 4px;
  margin: 22px 0 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sec-title span { font-size: 11.5px; font-family: var(--font-body); font-weight: normal; color: var(--ink-3); }

.grid-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.box-col {
  border: 1px solid var(--line);
  border-radius: 6px;
  padding: 14px 16px;
  background: #FFF;
}
.box-col h4 {
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.box-col.is-warn { border-left: 4px solid #C4442E; }
.box-col.is-ok { border-left: 4px solid #2F7D5D; }
.box-col.is-info { border-left: 4px solid #4A7FA8; }

ul.bullet-list { list-style: disc; padding-left: 18px; }
ul.bullet-list li { margin-bottom: 5px; font-size: 12.5px; }

/* TABLAS COMPACTAS */
.compact-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  margin-bottom: 20px;
}
.compact-table th, .compact-table td {
  padding: 8px 10px;
  border-bottom: 1px solid var(--line);
  text-align: left;
  vertical-align: top;
}
.compact-table th {
  background: #F1F5F9;
  font-size: 10.5px;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--ink-2);
  font-weight: 700;
  border-top: 1px solid var(--line);
}
.compact-table tr:nth-child(even) td { background: #FAFAFC; }
.compact-table .nowrap { white-space: nowrap; }

/* RECUADRO DE NOMENCLATURA DRIVE */
.drive-box {
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-left: 4px solid #2563EB;
  border-radius: 6px;
  padding: 14px 16px;
  margin-bottom: 20px;
}
.drive-box-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}
.drive-box-head strong { color: #1E40AF; font-size: 13px; }
.drive-file-code {
  background: #DBEAFE;
  color: #1E3A8A;
  padding: 6px 10px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 13px;
  font-weight: 700;
  display: inline-block;
  user-select: all;
  word-break: break-all;
}

/* CHECKLISTS Y VIÑETAS */
.chk-list { list-style: none; }
.chk-list li {
  margin-bottom: 6px;
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-size: 12.5px;
}
.chk-box {
  width: 13px;
  height: 13px;
  border: 1.5px solid var(--ink-2);
  display: inline-block;
  border-radius: 2px;
  text-align: center;
  line-height: 11px;
  font-size: 10px;
  font-weight: bold;
  flex: none;
}
.chk-box.checked { background: #065F46; border-color: #065F46; color: #FFF; }

/* FIRMAS */
.signature-block {
  margin-top: 36px;
  padding-top: 20px;
  border-top: 1px solid var(--line);
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 24px;
  page-break-inside: avoid;
}
.sig-line {
  text-align: center;
  border-top: 1px dashed var(--ink-3);
  padding-top: 8px;
  margin-top: 40px;
  font-size: 11.5px;
  color: var(--ink-2);
}
.sig-line strong { display: block; color: var(--ink); font-size: 12.5px; }

/* AJUSTES PARA IMPRESIÓN */
@media print {
  body { background: #FFF !important; }
  .toolbar-noprint { display: none !important; }
  .doc-page {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
  }
  .compact-table, .signature-block, .box-col, .data-card {
    page-break-inside: avoid;
  }
}
</style>
</head>
<body>

<!-- BARRA DE ACCIONES (NO SE IMPRIME) -->
<nav class="toolbar-noprint">
  <div class="tb-left">
    <a href="gestion.php" class="tb-btn tb-btn-subtle">← Volver a Gestión LAB</a>
    <span><strong>Esquel LAB · Vista de Impresión</strong> (<?= $tipo === 'plan' ? 'Plan de Trabajo' : 'Minuta de Reunión' ?>)</span>
  </div>
  <div class="tb-right">
    <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="tb-btn tb-btn-wa" title="Abrir mensaje de WhatsApp predeterminado">
      📲 Enviar por WhatsApp
    </a>
    <button type="button" onclick="window.print()" class="tb-btn tb-btn-primary">
      🖨️ Imprimir / Guardar PDF (Ctrl+P)
    </button>
  </div>
</nav>

<div class="doc-page">

  <!-- CABECERA INSTITUCIONAL -->
  <header class="inst-header">
    <div class="inst-brand">
      <img src="../assets/images/logo-esquel-lab.png" alt="Esquel LAB" class="inst-logo">
      <div class="inst-titles">
        <h1>Esquel LAB <span>· Programa de Aceleración 2026</span></h1>
        <p>Subsecretaría de Turismo · Municipalidad de Esquel</p>
      </div>
    </div>
    <div class="inst-doc-tag">
      <strong><?= $tipo === 'plan' ? 'DOCUMENTO OFICIAL DE PLANIFICACIÓN' : 'ACTA Y MINUTA DE ENCUENTRO' ?></strong>
      <span>Emitido el <?= date('d/m/Y H:i') ?> hs</span>
    </div>
  </header>

  <?php if ($tipo === 'plan'): ?>
    <!-- =================================================================== -->
    <!-- CUERPO: PLAN DE TRABAJO ESTRATÉGICO COMPLETO                         -->
    <!-- =================================================================== -->

    <div class="data-card">
      <div class="dc-title-row">
        <div>
          <span class="badge badge-cel" style="background:<?= e($celInfo['color']) ?>">Célula <?= e($p['celula']) ?>: <?= e($celInfo['nombre']) ?></span>
          <span class="badge"><?= e($p['linea']) ?></span>
          <span class="badge badge-ok">Puntaje Oficial: <?= number_format((float)$p['puntaje'], 2) ?></span>
          <h2 class="dc-proy-name" style="margin-top:6px"><?= e($p['nombre']) ?></h2>
        </div>
        <div style="text-align:right">
          <span class="badge badge-ok" style="font-size:12px"><?= e($p['estado_acompanamiento']) ?></span>
        </div>
      </div>
      <div class="data-grid-4">
        <div class="dg-item">
          <span class="lbl">Titular / Referente</span>
          <span class="val"><?= e($p['titular'] ?: '—') ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Teléfono / WhatsApp</span>
          <span class="val"><?= e($p['phone'] ?: 'No registrado') ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Consultor Senior (Conduce)</span>
          <span class="val"><?= e($srNom) ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Consultor Junior (Acompaña)</span>
          <span class="val"><?= e($jrNom) ?></span>
        </div>
      </div>
    </div>

    <!-- DIAGNOSTICO Y TRABAS -->
    <div class="grid-2col">
      <div class="box-col is-info">
        <h4>🔍 Diagnóstico Inicial de Situación</h4>
        <ul class="bullet-list">
          <?php foreach ($diagnostico as $d): ?>
            <li><?= e($d) ?></li>
          <?php endforeach; ?>
          <?php if (empty($diagnostico)): ?>
            <li>Sin diagnóstico formal cargado.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="box-col is-warn">
        <h4>⚠️ Trabas Operativas y Cuellos de Botella</h4>
        <ul class="bullet-list">
          <?php foreach ($trabas as $t): ?>
            <li><?= e($t) ?></li>
          <?php endforeach; ?>
          <?php if (empty($trabas)): ?>
            <li>Sin trabas críticas registradas.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- EJES Y ENTREGABLES -->
    <div class="grid-2col">
      <div class="box-col">
        <h4>🎯 Ejes Estratégicos de Intervención (10 Semanas)</h4>
        <?php foreach ($ejes as $eje): ?>
          <div style="margin-bottom:8px">
            <strong><?= e($eje['t'] ?? '') ?></strong>
            <ul class="bullet-list" style="margin-top:3px">
              <?php foreach ($eje['items'] ?? [] as $it): ?>
                <li><?= e($it) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
        <?php if (empty($ejes)): ?>
          <p class="sub">Sin ejes estratégicos definidos.</p>
        <?php endif; ?>
      </div>

      <div class="box-col is-ok">
        <h4>📦 Entregables Finales Comprometidos</h4>
        <ul class="bullet-list">
          <?php foreach ($entregables as $ent): ?>
            <li><?= e($ent) ?></li>
          <?php endforeach; ?>
          <?php if (empty($entregables)): ?>
            <li>Sin entregables finales listados.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- CRONOGRAMA MAESTRO DE REUNIONES -->
    <h3 class="sec-title">
      Agenda Maestra de Encuentros de Acompañamiento
      <span>10 semanas efectivas · 16 sep al 20 nov 2026</span>
    </h3>
    <table class="compact-table">
      <thead>
        <tr>
          <th style="width:35px">#</th>
          <th style="width:90px">Fecha</th>
          <th style="width:85px">Horario</th>
          <th>Título y Foco del Encuentro</th>
          <th style="width:110px">Sede / Lugar</th>
          <th style="width:75px">Tipo</th>
          <th>Equipo Asignado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reuniones as $reu): 
          $asistList = !empty($reu['asistentes_str']) ? explode(',', $reu['asistentes_str']) : [];
          $asistStr = implode(', ', array_map(fn($aid) => $consultores[$aid]['nombre'] ?? $aid, $asistList));
        ?>
          <tr>
            <td><strong>#<?= e($reu['numero_reunion']) ?></strong></td>
            <td class="nowrap"><?= e(fecha_corta_ar($reu['fecha'])) ?></td>
            <td class="nowrap"><?= e($reu['hora_inicio'] ?: 'A conf.') ?> a <?= e($reu['hora_fin'] ?: '') ?></td>
            <td>
              <strong><?= e($reu['titulo']) ?></strong>
              <?php if (!empty($reu['guia_consultor'])): ?>
                <div style="font-size:11px;color:var(--ink-2);margin-top:2px"><?= e($reu['guia_consultor']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($reu['lugar'] ?: 'A confirmar') ?></td>
            <td><span class="badge"><?= e($tiposLabel[$reu['tipo']] ?? $reu['tipo']) ?></span></td>
            <td style="font-size:11px"><?= e($asistStr ?: 'Sin asignar') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- COMPROMISOS ASUMIDOS -->
    <?php if (!empty($compromisos)): ?>
      <h3 class="sec-title">Compromisos de Campo Asignados</h3>
      <table class="compact-table">
        <thead>
          <tr>
            <th>Descripción del compromiso</th>
            <th style="width:130px">Responsable</th>
            <th style="width:100px">Fecha Límite</th>
            <th style="width:90px">Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($compromisos as $comp): ?>
            <tr>
              <td><?= e($comp['descripcion']) ?></td>
              <td><strong><?= e($comp['responsable']) ?></strong></td>
              <td><?= e(fecha_corta_ar($comp['fecha_limite'])) ?></td>
              <td><span class="badge <?= $comp['estado'] === 'cumplido' ? 'badge-ok' : '' ?>"><?= e($comp['estado']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- BITÁCORA Y NOTAS DEL PROYECTO -->
    <?php if (!empty($p['notas_generales'])): ?>
      <h3 class="sec-title">Bitácora y Notas Generales</h3>
      <div class="box-col" style="white-space: pre-wrap; font-size: 12px; margin-bottom: 20px;">
        <?= nl2br(e($p['notas_generales'])) ?>
      </div>
    <?php endif; ?>

    <!-- FIRMAS FORMALES -->
    <div class="signature-block">
      <div class="sig-line">
        <strong><?= e($p['titular'] ?: 'Titular del Emprendimiento') ?></strong>
        Emprendimiento Seleccionado
      </div>
      <div class="sig-line">
        <strong><?= e($srNom) ?></strong>
        Consultor Senior Referente
      </div>
      <div class="sig-line">
        <strong>Leandro Choi</strong>
        Coordinación Técnica · Esquel LAB
      </div>
    </div>

  <?php else: ?>
    <!-- =================================================================== -->
    <!-- CUERPO: MINUTA DE ENCUENTRO INDIVIDUAL                              -->
    <!-- =================================================================== -->

    <div class="data-card">
      <div class="dc-title-row">
        <div>
          <span class="badge" style="background:<?= e($celInfo['color']) ?>;color:#FFF">Célula <?= e($r['celula']) ?>: <?= e($celInfo['nombre']) ?></span>
          <span class="badge"><?= e($tiposLabel[$r['tipo']] ?? $r['tipo']) ?></span>
          <h2 class="dc-proy-name" style="margin-top:6px"><?= e($r['proyecto_nombre']) ?></h2>
          <div style="font-size:13px;color:var(--ink-2);margin-top:2px">
            👤 Titular: <strong><?= e($r['proyecto_titular']) ?></strong> · 📞 <?= e($r['phone'] ?: 'No registrado') ?>
          </div>
        </div>
        <div style="text-align:right">
          <span class="badge badge-ok" style="font-size:13px;padding:5px 10px">Encuentro #<?= e($r['numero_reunion']) ?></span>
        </div>
      </div>
      <div class="data-grid-4">
        <div class="dg-item">
          <span class="lbl">Fecha del encuentro</span>
          <span class="val"><?= e(fecha_formateada($r['fecha'])) ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Horario Programado</span>
          <span class="val"><?= e($r['hora_inicio'] ?: 'A conf.') ?> a <?= e($r['hora_fin'] ?: 'A conf.') ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Horario Real Ejecutado</span>
          <span class="val"><?= ($r['hora_real_inicio'] && $r['hora_real_fin']) ? e($r['hora_real_inicio']) . ' a ' . e($r['hora_real_fin']) : 'No registrado' ?></span>
        </div>
        <div class="dg-item">
          <span class="lbl">Lugar / Sede</span>
          <span class="val"><?= e($r['lugar'] ?: 'A confirmar') ?></span>
        </div>
      </div>
    </div>

    <!-- ENLACE Y NOMENCLATURA GOOGLE DRIVE -->
    <div class="drive-box">
      <div class="drive-box-head">
        <strong>📁 Resguardo y Desgrabación en Google Drive</strong>
        <a href="<?= e($DRIVE_FOLDER_URL) ?>" target="_blank" rel="noopener" style="font-size:11.5px;color:#1E40AF;font-weight:700">Abrir carpeta compartida ↗</a>
      </div>
      <p style="font-size:11.5px;color:var(--ink-2);margin-bottom:6px">
        Nomenclatura oficial para el archivo de audio/desgrabación en la carpeta del programa:
      </p>
      <div class="drive-file-code"><?= e($nombreDriveOficial) ?></div>
    </div>

    <!-- TÍTULO Y GUÍA DEL CONSULTOR -->
    <h3 class="sec-title">Tema y Foco Pedagógico del Encuentro</h3>
    <div class="box-col is-info" style="margin-bottom:16px">
      <h4 style="font-size:14px"><?= e($r['titulo']) ?></h4>
      <p style="font-size:12.5px;color:var(--ink-2);margin-top:4px"><?= e($r['guia_consultor'] ?: 'Revisar antecedentes y avances del proyecto antes de comenzar.') ?></p>
    </div>

    <!-- PREGUNTAS CLAVE -->
    <?php if (!empty($preguntas)): ?>
      <h3 class="sec-title">Preguntas Clave Disparadas en la Sesión</h3>
      <div class="box-col" style="margin-bottom:16px">
        <ul class="bullet-list">
          <?php foreach ($preguntas as $q): ?>
            <li><strong>«<?= e($q) ?>»</strong></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- CHECKLIST DE OBJETIVOS -->
    <?php if (!empty($checklist)): ?>
      <h3 class="sec-title">Checklist de Objetivos Operativos</h3>
      <div class="box-col" style="margin-bottom:16px">
        <ul class="chk-list">
          <?php foreach ($checklist as $it): ?>
            <li>
              <span class="chk-box <?= !empty($it['done']) ? 'checked' : '' ?>"><?= !empty($it['done']) ? '✓' : '' ?></span>
              <span><?= e($it['texto'] ?? '') ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- MINUTA DE CAMPO Y ACUERDOS -->
    <h3 class="sec-title">Minuta, Acuerdos y Bitácora de Campo</h3>
    <div class="box-col is-ok" style="white-space: pre-wrap; font-size: 13px; margin-bottom: 20px;">
      <?= !empty($r['minuta_notas']) ? nl2br(e($r['minuta_notas'])) : '<em>Sin notas cargadas al momento.</em>' ?>
    </div>

    <!-- FIRMAS DE CONFORMIDAD -->
    <div class="signature-block" style="grid-template-columns: 1fr 1fr;">
      <div class="sig-line">
        <strong><?= e($r['proyecto_titular']) ?></strong>
        Titular del Emprendimiento
      </div>
      <div class="sig-line">
        <strong><?= e(implode(' / ', $asistentesNombres) ?: 'Equipo Consultor') ?></strong>
        Consultor/es Interviniente/s
      </div>
    </div>

  <?php endif; ?>

</div>

</body>
</html>
