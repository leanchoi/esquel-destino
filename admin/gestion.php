<?php
/**
 * Módulo de Gestión, Agenda y Acompañamiento de Proyectos · Esquel LAB 2026.
 *
 * Entidad viva de trabajo para consultores y conducción.
 * Integra:
 * - Agenda global y colisiones de carga
 * - Expediente 360° por emprendimiento (postulación original + evaluaciones jurado + plan estratégico)
 * - Playbook pedagógico por reunión con checklist, preguntas clave, asistencia y horas reales
 * - Matriz de carga horaria de consultores con detección de restricciones y conflictos
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/jurado.php';

$u = requiere_gestion_lab();
$pdo = db();

// Asegurar que las tablas y datos semilla existan
if (file_exists(__DIR__ . '/../includes/lab_seed.php')) {
    require_once __DIR__ . '/../includes/lab_seed.php';
    lab_asegurar_datos($pdo);
}

// 1. Metadatos de la cohorte
$meta = [
    'inicio'   => '2026-09-09',
    'fin'      => '2026-11-10',
    'fit'      => ['2026-09-24', '2026-09-30'],
    'feriados' => ['2026-10-12'],
    'cierre'   => '2026-11-10'
];

$celulasInfo = [
    1 => ['nombre' => 'Oficio y pieza', 'color' => '#132B43', 'desc' => 'Artesanías, carpintería, diseño y museo vintage'],
    2 => ['nombre' => 'Campo y gran superficie', 'color' => '#2F7D5D', 'desc' => 'Agroturismo, cabalgatas, chacras y laberinto'],
    3 => ['nombre' => 'Relato y territorio', 'color' => '#8A1E47', 'desc' => 'Patrimonio histórico, senderos y gastronomía ancestral'],
    4 => ['nombre' => 'Canal y volumen', 'color' => '#E8A33D', 'desc' => 'Parques aéreos, casas de té, cicloturismo y receptivo']
];

// 2. Consultores
$consultoresRaw = $pdo->query("SELECT * FROM lab_consultores WHERE activo = 1 ORDER BY id ASC")->fetchAll();
$consultores = [];
foreach ($consultoresRaw as $c) {
    $c['disponibilidad'] = json_decode($c['disponibilidad'], true) ?: [];
    $consultores[$c['id']] = $c;
}

// 3. Proyectos seleccionados y vinculación con aplicaciones
$proyectosRaw = $pdo->query("
    SELECT p.*, a.contact_name, a.email, a.phone, a.program, a.stage
    FROM lab_proyectos p
    LEFT JOIN applications a ON a.id = p.application_id
    ORDER BY p.puntaje DESC
")->fetchAll();

$appIds = array_column($proyectosRaw, 'application_id');
$detallesApp = [];
if ($appIds) {
    $ph = implode(',', array_fill(0, count($appIds), '?'));
    $dStmt = $pdo->prepare("SELECT application_id, field_key, field_value FROM application_details WHERE application_id IN ($ph)");
    $dStmt->execute($appIds);
    foreach ($dStmt->fetchAll() as $row) {
        $detallesApp[$row['application_id']][$row['field_key']] = $row['field_value'];
    }
}

// Votos de los jurados para los 18 proyectos
$votosApp = evaluaciones_de($pdo, $appIds);
$juradoLista = jurado($pdo);

$proyectos = [];
foreach ($proyectosRaw as $p) {
    $p['diagnostico'] = json_decode($p['diagnostico'], true) ?: [];
    $p['trabas'] = json_decode($p['trabas'], true) ?: [];
    $p['ejes'] = json_decode($p['ejes'], true) ?: [];
    $p['entregables'] = json_decode($p['entregables'], true) ?: [];
    $p['detalles_postulacion'] = $detallesApp[$p['application_id']] ?? [];
    
    $vs = $votosApp[$p['application_id']] ?? [];
    $p['votos_jurado'] = $vs;
    $p['consolidado_jurado'] = consolidado_para(consolidar($vs, $juradoLista), true);

    $proyectos[$p['id']] = $p;
}

// 4. Reuniones y Asistentes
$reunionesRaw = $pdo->query("
    SELECT r.*, GROUP_CONCAT(ra.consultor_id, ',') AS asistentes_str
    FROM lab_reuniones r
    LEFT JOIN lab_reunion_asistentes ra ON ra.reunion_id = r.id
    GROUP BY r.id
    ORDER BY r.fecha ASC, r.hora_inicio ASC
")->fetchAll();

$reuniones = [];
foreach ($reunionesRaw as $r) {
    $r['asistentes'] = !empty($r['asistentes_str']) ? explode(',', $r['asistentes_str']) : [];
    unset($r['asistentes_str']);
    $r['preguntas_clave'] = json_decode($r['preguntas_clave'], true) ?: [];
    $r['objetivos'] = json_decode($r['objetivos'], true) ?: [];
    $r['checklist'] = json_decode($r['checklist'], true) ?: [];
    $reuniones[] = $r;
}

// 5. Compromisos
$compromisos = $pdo->query("SELECT * FROM lab_compromisos ORDER BY fecha_limite ASC, id DESC")->fetchAll();

$pageTitle = 'Gestión LAB · Acompañamiento 2026';
$nav = 'gestion';
require __DIR__ . '/_header.php';
?>

<div class="gestion-root wrap">

  <!-- CABECERA PRINCIPAL Y METRICAS -->
  <header class="gestion-header">
    <div class="gestion-title-row">
      <div>
        <h1 class="gestion-h1">Esquel LAB <span>· Gestión y Aceleración 2026</span></h1>
        <p class="gestion-sub">1ª cohorte · 18 proyectos seleccionados · 9 sep → 10 nov 2026 · Subsecretaría de Turismo</p>
      </div>
      <div class="gestion-actions">
        <button type="button" class="btn btn-secondary btn-sm" id="btnExportJSON">Exportar JSON</button>
        <button type="button" class="btn btn-secondary btn-sm" id="btnExportTXT">Exportar Agenda TXT</button>
      </div>
    </div>

    <!-- Tira visual de colisiones y balance de carga -->
    <div class="strip-card">
      <div class="strip-header">
        <span class="strip-legend-title" id="stripLead">Cargando densidad de reuniones...</span>
        <div class="strip-legend">
          <span><i class="dot" style="background:var(--d2)"></i> 1 encuentro</span>
          <span><i class="dot" style="background:var(--d3)"></i> 2 encuentros</span>
          <span><i class="dot" style="background:var(--d4)"></i> 3+ colisión crítica</span>
          <span style="color:var(--d4);font-weight:600">▨ FIT (24–30 sep)</span>
        </div>
      </div>
      <div class="strip" id="stripContainer"></div>
      <div class="strip-axis">
        <div>septiembre 2026</div>
        <div>octubre 2026</div>
        <div>noviembre 2026</div>
      </div>
    </div>

    <!-- Tarjetas de indicadores en vivo -->
    <div class="stats-grid" id="statsGrid"></div>
  </header>

  <!-- NAVEGACION DE VISTAS -->
  <nav class="gestion-nav" role="tablist">
    <button role="tab" class="gnav-btn is-active" data-tab="agenda">📅 Agenda Global</button>
    <button role="tab" class="gnav-btn" data-tab="proyectos">📂 Emprendimientos (Ficha 360°)</button>
    <button role="tab" class="gnav-btn" data-tab="consultores">👥 Consultores & Carga</button>
    <button role="tab" class="gnav-btn" data-tab="alertas">⚠️ Alertas & Conflictos</button>
  </nav>

  <!-- ========================================================================= -->
  <!-- VISTA 1: AGENDA GLOBAL                                                    -->
  <!-- ========================================================================= -->
  <section id="tab-agenda" class="gtab-content">
    <div class="agenda-bar">
      <!-- Filtros combinados -->
      <div class="agenda-filters">
        <div class="agenda-filter">
          <label>Célula:</label>
          <select id="filtroCelula">
            <option value="">Todas las células</option>
            <option value="1">Célula 1 · Oficio y pieza</option>
            <option value="2">Célula 2 · Campo y gran superficie</option>
            <option value="3">Célula 3 · Relato y territorio</option>
            <option value="4">Célula 4 · Canal y volumen</option>
          </select>
        </div>
        <div class="agenda-filter">
          <label>Consultor:</label>
          <select id="filtroConsultor">
            <option value="">Todo el equipo</option>
            <?php foreach ($consultores as $c): ?>
              <option value="<?= e($c['id']) ?>"><?= e($c['nombre']) ?> (<?= e($c['rol']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="agenda-filter">
          <label>Tipo:</label>
          <select id="filtroTipo">
            <option value="">Todos los tipos</option>
            <option value="ind">Individual</option>
            <option value="gru">Célula / Grupal</option>
            <option value="ter">Terreno</option>
            <option value="cie">Acto de cierre</option>
          </select>
        </div>
      </div>

      <!-- Sub-tabs de la agenda: Calendario / Timeline / Día por Día -->
      <div class="agenda-submodes">
        <button type="button" class="submode-btn is-active" data-mode="cal">Calendario</button>
        <button type="button" class="submode-btn" data-mode="dxd">Día por día</button>
        <button type="button" class="submode-btn" data-mode="gantt">Timeline Células</button>
      </div>
    </div>

    <!-- Subvista Calendario -->
    <div id="sub-cal" class="submode-view">
      <div class="cal-legend">
        <span><i class="dot" style="background:var(--ind)"></i> Individual</span>
        <span><i class="dot" style="background:var(--gru)"></i> Célula / Grupal</span>
        <span><i class="dot" style="background:var(--ter)"></i> Terreno</span>
        <span><i class="dot" style="background:var(--cie)"></i> Cierre plenario</span>
        <span style="color:var(--d4)">▨ Período FIT (24–30 sep)</span>
        <span style="color:var(--d3)">⌷ Feriado</span>
      </div>
      <div class="meses-grid" id="mesesGrid"></div>
    </div>

    <!-- Subvista Día por Día -->
    <div id="sub-dxd" class="submode-view" style="display:none">
      <div class="dxd-container" id="dxdContainer"></div>
    </div>

    <!-- Subvista Gantt / Timeline -->
    <div id="sub-gantt" class="submode-view" style="display:none">
      <div class="gantt-scroll">
        <div class="gantt-inner" id="ganttInner"></div>
      </div>
    </div>
  </section>

  <!-- ========================================================================= -->
  <!-- VISTA 2: EXPEDIENTE 360° POR EMPRENDIMIENTO                               -->
  <!-- ========================================================================= -->
  <section id="tab-proyectos" class="gtab-content" style="display:none">
    <div class="expediente-layout">
      <!-- Selector lateral de emprendimientos -->
      <aside class="expediente-sidebar">
        <div class="sidebar-search">
          <input type="text" id="searchProyecto" placeholder="Buscar emprendimiento...">
        </div>
        <div class="proyectos-nav-list" id="proyectosNavList">
          <?php foreach ($proyectos as $p): ?>
            <button type="button" class="pnav-item" data-pid="<?= e($p['id']) ?>">
              <span class="pnav-cel-tag" style="background:<?= e($celulasInfo[$p['celula']]['color']) ?>"></span>
              <div class="pnav-info">
                <strong><?= e($p['nombre']) ?></strong>
                <span class="pnav-meta"><?= e($p['titular']) ?> · <?= e($p['linea']) ?> (<?= number_format($p['puntaje'], 2) ?>)</span>
              </div>
            </button>
          <?php endforeach; ?>
        </div>
      </aside>

      <!-- Ficha 360° del proyecto seleccionado -->
      <main class="expediente-body" id="expedienteBody">
        <div class="empty-state-notice">
          <p>Seleccioná un proyecto de la lista lateral para abrir su expediente 360° completo.</p>
        </div>
      </main>
    </div>
  </section>

  <!-- ========================================================================= -->
  <!-- VISTA 3: CONSULTORES & CARGA DE TRABAJO                                   -->
  <!-- ========================================================================= -->
  <section id="tab-consultores" class="gtab-content" style="display:none">
    <div class="consultores-grid" id="consultoresGrid"></div>
    <div class="consultor-detail-card" id="consultorDetailCard" style="display:none"></div>
  </section>

  <!-- ========================================================================= -->
  <!-- VISTA 4: ALERTAS & CONFLICTOS DE AGENDA                                   -->
  <!-- ========================================================================= -->
  <section id="tab-alertas" class="gtab-content" style="display:none">
    <div class="alertas-container" id="alertasContainer"></div>
  </section>

</div>

<!-- ========================================================================= -->
<!-- DRAWER MODAL: PLAYBOOK Y EDITOR DEL ENCUENTRO                             -->
<!-- ========================================================================= -->
<div class="drawer-scrim" id="drawerScrim"></div>
<aside class="reunion-drawer" id="reunionDrawer" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">
  <div class="rdrawer-header">
    <button type="button" class="rdrawer-close" id="drawerClose" aria-label="Cerrar">×</button>
    <div class="rdrawer-kick" id="drawerKick"></div>
    <h2 class="rdrawer-title" id="drawerTitle">Encuentro</h2>
    <div class="rdrawer-meta" id="drawerMeta"></div>
  </div>
  <div class="rdrawer-body" id="drawerBody"></div>
  <div class="rdrawer-footer" id="drawerFooter">
    <button type="button" class="btn btn-secondary btn-sm" id="btnDrawerCancel">Cerrar</button>
    <button type="button" class="btn btn-primary btn-sm" id="btnDrawerSave">Guardar cambios</button>
  </div>
</aside>

<!-- DATOS GLOBALES INYECTADOS -->
<script id="labData" type="application/json">
<?= json_encode([
    'meta'        => $meta,
    'celulas'     => $celulasInfo,
    'consultores' => $consultores,
    'proyectos'   => $proyectos,
    'reuniones'   => $reuniones,
    'compromisos' => $compromisos,
    'csrf'        => csrf_token(),
    'yo'          => [
        'id'       => (int) $u['id'],
        'username' => $u['username'],
        'role'     => $u['role']
    ]
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
</script>

<script src="../assets/js/gestion.js"></script>

<?php require __DIR__ . '/_footer.php'; ?>
