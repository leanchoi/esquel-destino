<?php
/**
 * Analítica del sitio público. Sólo lee la tabla visitas.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/analitica.php';

$u = requiere_login();
$pdo = db();

$dias = (int) ($_GET['dias'] ?? 30);
if (!in_array($dias, [7, 30, 90], true)) {
    $dias = 30;
}
$desde = "-{$dias} days";

/** Consulta con el filtro de fecha ya aplicado. */
function q(PDO $pdo, string $sql, string $desde): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$desde]);
    return $stmt->fetchAll();
}

$totales = $pdo->prepare(
    "SELECT COUNT(*) vistas, COUNT(DISTINCT visitante) unicos,
            AVG(NULLIF(segundos,0)) seg, AVG(NULLIF(profundidad,0)) prof
       FROM visitas WHERE creada_at >= datetime('now', ?)"
);
$totales->execute([$desde]);
$tot = $totales->fetch();

$porDia = q($pdo,
    "SELECT date(creada_at) dia, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos
       FROM visitas WHERE creada_at >= datetime('now', ?)
      GROUP BY dia ORDER BY dia", $desde);

$porPagina = q($pdo,
    "SELECT ruta, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos,
            AVG(NULLIF(segundos,0)) seg, AVG(NULLIF(profundidad,0)) prof
       FROM visitas WHERE creada_at >= datetime('now', ?)
      GROUP BY ruta ORDER BY vistas DESC LIMIT 20", $desde);

$porOrigen = q($pdo,
    "SELECT CASE WHEN origen = '' THEN 'Directo o guardado' ELSE origen END fuente, COUNT(*) vistas
       FROM visitas WHERE creada_at >= datetime('now', ?)
      GROUP BY fuente ORDER BY vistas DESC LIMIT 12", $desde);

$porDispositivo = q($pdo,
    "SELECT dispositivo, COUNT(*) vistas FROM visitas
      WHERE creada_at >= datetime('now', ?) GROUP BY dispositivo ORDER BY vistas DESC", $desde);

$porPais = q($pdo,
    "SELECT pais, COUNT(*) vistas FROM visitas
      WHERE creada_at >= datetime('now', ?) AND pais IS NOT NULL
      GROUP BY pais ORDER BY vistas DESC LIMIT 10", $desde);

// Embudo del formulario: cuántas visitas llegaron a cada paso.
$embudo = [];
$stmtE = $pdo->prepare(
    "SELECT COUNT(DISTINCT visitante) n FROM visitas
      WHERE creada_at >= datetime('now', ?) AND ruta LIKE '%inscribirse.php' AND paso_form >= ?"
);
for ($i = 1; $i <= 6; $i++) {
    $stmtE->execute([$desde, $i]);
    $embudo[$i] = (int) $stmtE->fetchColumn();
}

$enviadas = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE submitted_at >= datetime('now', ?)");
$enviadas->execute([$desde]);
$totalEnviadas = (int) $enviadas->fetchColumn();

$maxDia = 0;
foreach ($porDia as $d) {
    $maxDia = max($maxDia, (int) $d['vistas']);
}

$pageTitle = 'Analítica';
$nav = 'analitica';
require __DIR__ . '/_header.php';

function pct(int $parte, int $total): float
{
    return $total > 0 ? round($parte * 100 / $total, 1) : 0;
}
?>

<div class="admin-topbar">
  <h1>Analítica del sitio</h1>
  <div class="rango">
    <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días'] as $d => $lbl): ?>
      <a href="?dias=<?= $d ?>" class="<?= $dias === $d ? 'is-active' : '' ?>"><?= e($lbl) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-content">

  <div class="stats">
    <div class="stat"><span class="k">Visitas</span><span class="v"><?= (int) $tot['vistas'] ?></span></div>
    <div class="stat"><span class="k">Personas distintas</span><span class="v"><?= (int) $tot['unicos'] ?></span></div>
    <div class="stat"><span class="k">Tiempo promedio</span><span class="v"><?= $tot['seg'] ? round($tot['seg']) . 's' : '—' ?></span></div>
    <div class="stat"><span class="k">Scroll promedio</span><span class="v"><?= $tot['prof'] ? round($tot['prof']) . '%' : '—' ?></span></div>
    <div class="stat ok"><span class="k">Postulaciones</span><span class="v"><?= $totalEnviadas ?></span></div>
  </div>

  <?php if (!$porDia): ?>
    <div class="empty-state">
      Todavía no hay visitas registradas en este período. Los datos empiezan a juntarse
      desde que esta versión del sitio está publicada.
    </div>
  <?php else: ?>

  <!-- ---------------- visitas por día ---------------- -->
  <div class="panel">
    <h2 class="panel-title">Visitas por día</h2>
    <div class="grafico">
      <?php foreach ($porDia as $d): $alto = $maxDia > 0 ? max(2, round($d['vistas'] * 100 / $maxDia)) : 2; ?>
        <div class="barra" title="<?= e($d['dia']) ?> · <?= (int) $d['vistas'] ?> visitas de <?= (int) $d['unicos'] ?> personas">
          <span class="col" style="height:<?= $alto ?>%"></span>
          <span class="fecha"><?= e(date('d/m', strtotime($d['dia']))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cols-2">
    <!-- ---------------- páginas ---------------- -->
    <div class="panel" style="padding:0;overflow:hidden">
      <h2 class="panel-title" style="padding:20px 22px 0">Qué se mira</h2>
      <div class="table-scroll">
        <table class="crm-table">
          <thead><tr><th>Página</th><th>Visitas</th><th>Tiempo</th><th>Scroll</th></tr></thead>
          <tbody>
            <?php foreach ($porPagina as $p): ?>
              <tr>
                <td><strong><?= e($p['ruta']) ?></strong></td>
                <td><?= (int) $p['vistas'] ?> <span class="sub">/ <?= (int) $p['unicos'] ?></span></td>
                <td class="sub"><?= $p['seg'] ? round($p['seg']) . 's' : '—' ?></td>
                <td class="sub"><?= $p['prof'] ? round($p['prof']) . '%' : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="hint" style="padding:0 22px 18px">
        “Scroll” es hasta qué parte de la página bajaron, en promedio. Un número bajo
        con mucho tiempo suele querer decir que algo arriba los frenó.
      </p>
    </div>

    <!-- ---------------- de dónde vienen ---------------- -->
    <div class="panel">
      <h2 class="panel-title">De dónde llegan</h2>
      <ul class="lista-barras">
        <?php foreach ($porOrigen as $o): ?>
          <li>
            <span class="lb-n"><?= e($o['fuente']) ?></span>
            <span class="lb-barra"><span style="width:<?= pct((int) $o['vistas'], (int) $tot['vistas']) ?>%"></span></span>
            <span class="lb-v"><?= (int) $o['vistas'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <h2 class="panel-title" style="margin-top:26px">Con qué entran</h2>
      <ul class="lista-barras">
        <?php foreach ($porDispositivo as $d): ?>
          <li>
            <span class="lb-n"><?= e(ucfirst($d['dispositivo'])) ?></span>
            <span class="lb-barra"><span style="width:<?= pct((int) $d['vistas'], (int) $tot['vistas']) ?>%"></span></span>
            <span class="lb-v"><?= (int) $d['vistas'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <h2 class="panel-title" style="margin-top:26px">Desde dónde</h2>
      <?php if ($porPais): ?>
        <ul class="lista-barras">
          <?php foreach ($porPais as $p): ?>
            <li>
              <span class="lb-n"><?= e($p['pais']) ?></span>
              <span class="lb-barra"><span style="width:<?= pct((int) $p['vistas'], (int) $tot['vistas']) ?>%"></span></span>
              <span class="lb-v"><?= (int) $p['vistas'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="hint">
          El hosting no está informando el país de origen. Para tenerlo habría que poner el sitio
          detrás de Cloudflare o mandarle la IP de cada visitante a un servicio externo — lo segundo
          no se hizo a propósito, por privacidad.
        </p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ---------------- embudo del formulario ---------------- -->
  <div class="panel">
    <h2 class="panel-title">Dónde se traba la postulación</h2>
    <p class="hint" style="margin-top:0">
      Cuánta gente distinta llegó a cada paso del formulario. La caída más grande entre
      dos pasos es el lugar donde conviene mirar.
    </p>
    <ul class="lista-barras embudo">
      <?php
      $nombres = [1 => 'Paso 1 · Dónde está', 2 => 'Paso 2 · Quién sos', 3 => 'Paso 3 · Qué hacés',
                  4 => 'Paso 4 · Conexiones', 5 => 'Paso 5 · Recursos', 6 => 'Paso 6 · Por qué vos'];
      $base = max(1, $embudo[1]);
      foreach ($nombres as $i => $nombre):
        $caida = $i > 1 && $embudo[$i - 1] > 0 ? 100 - pct($embudo[$i], $embudo[$i - 1]) : 0; ?>
        <li>
          <span class="lb-n"><?= e($nombre) ?></span>
          <span class="lb-barra"><span style="width:<?= pct($embudo[$i], $base) ?>%"></span></span>
          <span class="lb-v">
            <?= $embudo[$i] ?><?php if ($caida >= 15): ?> <em class="caida">−<?= round($caida) ?>%</em><?php endif; ?>
          </span>
        </li>
      <?php endforeach; ?>
      <li>
        <span class="lb-n"><strong>Postulaciones enviadas</strong></span>
        <span class="lb-barra"><span class="ok" style="width:<?= pct($totalEnviadas, $base) ?>%"></span></span>
        <span class="lb-v"><strong><?= $totalEnviadas ?></strong></span>
      </li>
    </ul>
  </div>

  <?php endif; ?>

  <div class="callout" style="margin-top:26px">
    <span class="lbl">Cómo se miden estos datos</span>
    <p>
      La medición es propia, sin Google Analytics ni servicios de terceros. No se guardan
      direcciones IP en claro ni se usan cookies de seguimiento: a cada visitante lo identifica un
      código que se recalcula todos los días, así que sirve para contar personas distintas en una
      jornada y no para seguir a nadie en el tiempo. Los registros se borran solos al año.
    </p>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
