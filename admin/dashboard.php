<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$u = requiere_login();
$pdo = db();

$filtros = [
    'program' => $_GET['program'] ?? '',
    'stage'   => $_GET['stage'] ?? '',
    'q'       => trim((string) ($_GET['q'] ?? '')),
];

$sql = 'SELECT * FROM applications WHERE 1=1';
$params = [];
if (array_key_exists($filtros['program'], PROGRAMAS)) {
    $sql .= ' AND program = ?';
    $params[] = $filtros['program'];
}
if (array_key_exists($filtros['stage'], ESTADOS)) {
    $sql .= ' AND stage = ?';
    $params[] = $filtros['stage'];
}
if ($filtros['q'] !== '') {
    $sql .= ' AND (name LIKE ? OR contact_name LIKE ? OR email LIKE ?)';
    $like = '%' . $filtros['q'] . '%';
    array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY submitted_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$apps = $stmt->fetchAll();

// Detalles de todas las postulaciones listadas, en una sola consulta.
$detalles = [];
if ($apps) {
    $ids = array_column($apps, 'id');
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $d = $pdo->prepare("SELECT * FROM application_details WHERE application_id IN ($ph)");
    $d->execute($ids);
    foreach ($d->fetchAll() as $row) {
        $detalles[$row['application_id']][$row['field_key']] = $row['field_value'];
    }
}

// Métricas sobre el total, no sobre el filtro activo.
$tot = $pdo->query("SELECT
    COUNT(*) total,
    SUM(CASE WHEN program='Acelera' THEN 1 ELSE 0 END) acelera,
    SUM(CASE WHEN program='Raiz' THEN 1 ELSE 0 END) raiz,
    SUM(CASE WHEN stage='Aprobado' THEN 1 ELSE 0 END) aprobados,
    SUM(CASE WHEN stage='Pendiente' THEN 1 ELSE 0 END) pendientes
    FROM applications")->fetch();

$queryFiltros = http_build_query(array_filter($filtros));

$pageTitle = 'Postulaciones';
$nav = 'postulaciones';
require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Postulaciones</h1>
  <span class="admin-count"><?= count($apps) ?> de <?= (int) $tot['total'] ?></span>
</div>

<div class="admin-content">

  <div class="stats">
    <div class="stat"><span class="k">Total</span><span class="v"><?= (int) $tot['total'] ?></span></div>
    <div class="stat acelera"><span class="k">Acelera</span><span class="v"><?= (int) $tot['acelera'] ?></span></div>
    <div class="stat raiz"><span class="k">Raíz</span><span class="v"><?= (int) $tot['raiz'] ?></span></div>
    <div class="stat"><span class="k">Sin evaluar</span><span class="v"><?= (int) $tot['pendientes'] ?></span></div>
    <div class="stat ok"><span class="k">Aprobadas</span><span class="v"><?= (int) $tot['aprobados'] ?></span></div>
  </div>

  <form method="get" class="filters">
    <select name="program">
      <option value="">Todas las líneas</option>
      <?php foreach (PROGRAMAS as $k => $p): ?>
        <option value="<?= e($k) ?>" <?= $filtros['program'] === $k ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="stage">
      <option value="">Todos los estados</option>
      <?php foreach (ESTADOS as $k => $s): ?>
        <option value="<?= e($k) ?>" <?= $filtros['stage'] === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="search" name="q" value="<?= e($filtros['q']) ?>" placeholder="Buscar proyecto, persona o correo…">
    <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
    <?php if (array_filter($filtros)): ?><a href="dashboard.php" class="clear">Limpiar</a><?php endif; ?>

    <span class="grow"></span>
    <div class="view-toggle">
      <button type="button" class="is-active" id="viewTable">Tabla</button>
      <button type="button" id="viewCards">Tarjetas</button>
    </div>
    <a href="api.php?export=csv&amp;<?= e($queryFiltros) ?>" class="btn btn-secondary btn-sm">Descargar CSV</a>
  </form>

  <?php if (!$apps): ?>
    <div class="empty-state">No hay postulaciones que coincidan con estos filtros.</div>
  <?php else: ?>

    <div id="tableView" class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead>
            <tr>
              <th>Proyecto</th><th>Responsable</th><th>Línea</th>
              <th>Recibida</th><th>Estado</th><th>Puntaje</th><th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($apps as $a):
              $p = programa_info($a['program']);
              $s = estado_info($a['stage']);
              $pts = puntaje_ponderado($a);
            ?>
              <tr>
                <td><strong><?= e($a['name']) ?></strong></td>
                <td>
                  <div><?= e($a['contact_name']) ?></div>
                  <div class="sub"><?= e($a['email']) ?></div>
                </td>
                <td><span class="chip" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span></td>
                <td class="nowrap sub"><?= e(fecha_corta($a['submitted_at'], true)) ?></td>
                <td><span class="chip" style="--c:<?= e($s['color']) ?>"><?= e($s['label']) ?></span></td>
                <td class="score"><?= $pts === null ? '—' : e(number_format($pts, 2)) ?></td>
                <td class="right">
                  <button type="button" class="btn btn-secondary btn-sm" data-abrir="<?= (int) $a['id'] ?>">
                    <?= puede('editor') ? 'Evaluar' : 'Ver' ?>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="cardsView" class="kanban" hidden>
      <?php foreach (ESTADOS as $slug => $info):
        $col = array_values(array_filter($apps, fn($a) => $a['stage'] === $slug)); ?>
        <div class="kcol">
          <div class="kcol-head" style="--c:<?= e($info['color']) ?>">
            <span><?= e($info['label']) ?></span><span class="n"><?= count($col) ?></span>
          </div>
          <div class="kcol-body">
            <?php foreach ($col as $a):
              $p = programa_info($a['program']);
              $pts = puntaje_ponderado($a); ?>
              <button type="button" class="kcard" data-abrir="<?= (int) $a['id'] ?>">
                <span class="chip" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span>
                <span class="kcard-t"><?= e($a['name']) ?></span>
                <span class="kcard-s"><?= e($a['contact_name']) ?></span>
                <span class="kcard-f"><?= e(fecha_corta($a['submitted_at'])) ?><?= $pts !== null ? ' · ' . e(number_format($pts, 2)) : '' ?></span>
              </button>
            <?php endforeach; ?>
            <?php if (!$col): ?><div class="kempty">—</div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>
</div>

<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="drawer" id="drawer" hidden aria-label="Detalle de la postulación">
  <div class="drawer-head">
    <div>
      <h2 id="dTitulo">—</h2>
      <p id="dSub" class="sub"></p>
    </div>
    <button type="button" class="drawer-close" id="drawerClose" aria-label="Cerrar">&times;</button>
  </div>
  <div class="drawer-body" id="drawerBody"></div>
</aside>

<script id="datosApps" type="application/json"><?= json_encode(
    array_map(function ($a) use ($detalles) {
        $a['detalles'] = $detalles[$a['id']] ?? [];
        $a['puntaje'] = puntaje_ponderado($a);
        return $a;
    }, $apps),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?></script>
<script id="configCrm" type="application/json"><?= json_encode([
    'criterios'   => CRITERIOS,
    'estados'     => ESTADOS,
    'etiquetas'   => ETIQUETAS_DETALLE,
    'puedeEditar' => puede('editor'),
    'csrf'        => csrf_token(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?php require __DIR__ . '/_footer.php'; ?>
