<?php
/**
 * Panel del profesor del ISET 815.
 *
 * Tiene que responder una sola pregunta por alumno: ¿participó de verdad?
 *
 * Y para eso junta tres cosas que hay que leer juntas, porque cada una sola
 * miente:
 *
 *   PRESENCIA     entró al panel, estuvo conectado, fue a las reuniones.
 *                 Se puede entrar todos los días y no aportar nada.
 *   CUMPLIMIENTO  entregó, a tiempo, con los específicos pedidos.
 *                 Se pueden escribir 4.000 caracteres que no le sirvan a nadie.
 *   UTILIDAD      el consultor que estuvo en la reunión dijo si sirvió.
 *                 Ésta es la que manda, y por eso va primero en la tabla.
 *
 * Sobre el "detector de IA" que se pidió: no está, y no por olvido. No existe
 * forma confiable de saber si un texto lo escribió una persona o un modelo;
 * los detectores que dicen hacerlo fallan seguido y fallan peor con quien
 * escribe simple o corto. Desaprobar a un chico con una salida así no se
 * sostiene si reclama.
 *
 * Lo que sí hay son hechos sobre cómo llegó el texto —cuánto entró pegado, en
 * cuántas sentadas, cuánto tardó tras la reunión— y el contraste de los
 * específicos contra la minuta del consultor. Eso se verifica y se defiende.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/estudiantes.php';

$u = requiere_profesor();
$pdo = db();

$detalle = trim((string) ($_GET['est'] ?? ''));

// --- exportación para la planilla del instituto ---------------------------
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="iset815-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Estudiante', 'Usuario', 'Legajo', 'Emprendimiento', 'Horas usadas', 'Horas presupuesto',
                   'Consignas', 'Entregadas', 'Vencidas', 'A tiempo', 'Caracteres', 'Específicos', 'Sirvió', 'A medias',
                   'A rehacer', 'Utilidad %', 'Ingresos al panel', 'Minutos en el panel']);
    foreach ($pdo->query('SELECT consultor_id FROM lab_estudiantes ORDER BY consultor_id') as $e) {
        $p = panel_estudiante($pdo, $e['consultor_id']);
        if (!$p) continue;
        $r = $p['resumen'];
        fputcsv($out, [
            $p['estudiante']['nombre'], $p['estudiante']['username'], $p['estudiante']['legajo'],
            $p['estudiante']['proyecto_nombre'], $p['horas']['usadas'], $p['horas']['presupuesto'],
            $r['asignadas'], $r['entregadas'], $r['vencidas'], $r['a_tiempo'], $r['caracteres'],
            $r['especificos']['ok'] . ' de ' . $r['especificos']['total'],
            $r['valoradas']['sirvio'], $r['valoradas']['parcial'], $r['valoradas']['rehacer'],
            $r['utilidad'] === null ? '' : $r['utilidad'],
            $r['ingresos'], $r['minutos_panel'],
        ]);
    }
    fclose($out);
    exit;
}

$ids = $pdo->query('SELECT consultor_id FROM lab_estudiantes WHERE activo = 1 ORDER BY consultor_id')->fetchAll(PDO::FETCH_COLUMN);
$paneles = [];
foreach ($ids as $id) {
    $p = panel_estudiante($pdo, $id);
    if ($p) $paneles[$id] = $p;
}

// Orden: primero los que necesitan atención. Un alumno con tareas vencidas o pendientes
// va primero. Un alumno que entregó todo y todo sirvió no necesita que el profesor lo mire.
uasort($paneles, function ($a, $b) {
    $va = $a['resumen']['vencidas'] ?? 0;
    $vb = $b['resumen']['vencidas'] ?? 0;
    if ($va !== $vb) return $vb <=> $va;

    $fa = $a['resumen']['asignadas'] - $a['resumen']['entregadas'];
    $fb = $b['resumen']['asignadas'] - $b['resumen']['entregadas'];
    if ($fa !== $fb) return $fb <=> $fa;
    return ($a['resumen']['utilidad'] ?? 101) <=> ($b['resumen']['utilidad'] ?? 101);
});

$totales = ['asignadas' => 0, 'entregadas' => 0, 'vencidas' => 0, 'a_tiempo' => 0, 'sirvio' => 0, 'valoradas' => 0];
foreach ($paneles as $p) {
    $totales['asignadas'] += $p['resumen']['asignadas'];
    $totales['entregadas'] += $p['resumen']['entregadas'];
    $totales['vencidas'] += $p['resumen']['vencidas'];
    $totales['a_tiempo'] += $p['resumen']['a_tiempo'];
    $totales['sirvio'] += $p['resumen']['valoradas']['sirvio'];
    $totales['valoradas'] += array_sum($p['resumen']['valoradas']);
}

$pageTitle = 'Estudiantes ISET';
$nav = 'profesor';
require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Estudiantes · ISET 815</h1>
  <span class="admin-count"><?= count($paneles) ?> en el convenio</span>
</div>

<div class="admin-content">

  <?php if ($detalle && isset($paneles[$detalle])): $p = $paneles[$detalle]; ?>
    <!-- ================= detalle de un estudiante ================= -->
    <p><a href="profesor.php" class="clear">← Volver a la lista</a></p>
    <div class="admin-topbar" style="margin-top:8px">
      <h2 style="font-size:24px"><?= e($p['estudiante']['nombre']) ?></h2>
      <span class="admin-count"><?= e($p['estudiante']['proyecto_nombre'] ?? 'sin emprendimiento') ?></span>
    </div>

    <div class="stats">
      <div class="stat"><span class="k">Entregadas</span><span class="v"><?= $p['resumen']['entregadas'] ?>/<?= $p['resumen']['asignadas'] ?></span></div>
      <?php if (!empty($p['resumen']['vencidas'])): ?>
        <div class="stat alerta"><span class="k">Vencidas</span><span class="v"><?= $p['resumen']['vencidas'] ?></span></div>
      <?php endif; ?>
      <div class="stat"><span class="k">A tiempo</span><span class="v"><?= $p['resumen']['a_tiempo'] ?></span></div>
      <div class="stat <?= ($p['resumen']['utilidad'] ?? 0) >= 70 ? 'raiz' : (($p['resumen']['utilidad'] === null) ? '' : 'alerta') ?>">
        <span class="k">Utilidad</span><span class="v"><?= $p['resumen']['utilidad'] === null ? '—' : $p['resumen']['utilidad'] . '%' ?></span>
      </div>
      <div class="stat"><span class="k">Horas</span><span class="v"><?= number_format($p['horas']['usadas'], 1, ',', '') ?></span></div>
      <div class="stat"><span class="k">Ingresos</span><span class="v"><?= $p['resumen']['ingresos'] ?></span></div>
      <div class="stat"><span class="k">En el panel</span><span class="v"><?= $p['resumen']['minutos_panel'] ?> min</span></div>
    </div>

    <?php foreach ($p['tareas'] as $t):
      $esVencida = $t['estado'] === 'pendiente' && !empty($t['vence_at']) && $t['vence_at'] < date('Y-m-d H:i');
      if ($t['estado'] === 'pendiente' && $t['entrega'] === '' && !$esVencida) continue;
      $señ = señales_escritura($t);
      $esp = $t['momento'] === 'durante' ? especificos_cumplidos(json_decode($t['especificos'] ?: '{}', true) ?: []) : null;
    ?>
      <article class="panel prof-entrega<?= $esVencida ? ' es-vencida' : '' ?>">
        <header class="prof-entrega-head">
          <div>
            <span class="est-momento est-m-<?= e($t['momento']) ?>"><?= e(MOMENTOS_ESTUDIANTE[$t['momento']]['label'] ?? '') ?></span>
            <h3><?= e($t['titulo']) ?></h3>
            <p class="sub">
              <?php if ($esVencida): ?>
                <span class="prof-tarde" style="font-weight:600">⚠️ Vencida <?= e(fecha_corta($t['vence_at'], true)) ?> sin entregar</span>
              <?php elseif ($t['entregado_at']): ?>
                Entregada <?= e(fecha_corta($t['entregado_at'], true)) ?>
                <?php if ($t['vence_at'] !== '' && $t['entregado_at'] > $t['vence_at']): ?>
                  · <span class="prof-tarde">fuera de plazo</span>
                <?php endif; ?>
              <?php else: ?>
                Borrador sin entregar
              <?php endif; ?>
            </p>
          </div>
          <?php if ($t['valoracion'] !== ''): $v = VALORACIONES_APORTE[$t['valoracion']]; ?>
            <span class="est-val est-v-<?= e($t['valoracion']) ?>"><?= e($v['label']) ?></span>
          <?php elseif ($esVencida): ?>
            <span class="prof-tarde" style="font-weight:600">Vencida</span>
          <?php else: ?>
            <span class="sub">Sin valorar</span>
          <?php endif; ?>
        </header>

        <?php if ($esp): ?>
          <div class="prof-esp">
            <span class="prof-esp-n <?= $esp['completo'] ? 'es-ok' : 'es-falta' ?>">
              <?= $esp['cumplidos'] ?> de <?= $esp['total'] ?> específicos
            </span>
            <?php foreach ($esp['detalle'] as $d): ?>
              <span class="prof-esp-i <?= $d['ok'] ? 'es-ok' : 'es-falta' ?>"><?= e($d['label']) ?>: <?= $d['trae'] ?>/<?= $d['pide'] ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="prof-texto"><?= e(mb_substr($t['entrega'], 0, 4000)) ?></div>

        <footer class="prof-señales">
          <span><strong><?= $señ['caracteres'] ?></strong> caracteres</span>
          <span><strong><?= $señ['pct_pegado'] ?>%</strong> entró pegado</span>
          <span><strong><?= $señ['sesiones'] ?></strong> <?= $señ['sesiones'] === 1 ? 'sentada' : 'sentadas' ?></span>
          <span><strong><?= $señ['minutos'] ?></strong> min escribiendo</span>
          <p class="prof-lectura"><?= e($señ['lectura']) ?></p>
        </footer>
      </article>
    <?php endforeach; ?>

  <?php else: ?>
    <!-- ================= lista de los 18 ================= -->
    <div class="stats">
      <div class="stat"><span class="k">Consignas</span><span class="v"><?= $totales['asignadas'] ?></span></div>
      <div class="stat"><span class="k">Entregadas</span><span class="v"><?= $totales['entregadas'] ?></span></div>
      <?php if (!empty($totales['vencidas'])): ?>
        <div class="stat alerta"><span class="k">Vencidas</span><span class="v"><?= $totales['vencidas'] ?></span></div>
      <?php endif; ?>
      <div class="stat"><span class="k">A tiempo</span><span class="v"><?= $totales['a_tiempo'] ?></span></div>
      <div class="stat raiz"><span class="k">Aportes que sirvieron</span><span class="v"><?= $totales['sirvio'] ?><?= $totales['valoradas'] ? '/' . $totales['valoradas'] : '' ?></span></div>
    </div>

    <div class="prof-aviso">
      <strong>Cómo leer esta tabla.</strong> Las tres columnas dicen cosas distintas y hay que mirarlas juntas.
      <em>Utilidad</em> es lo que dijo el consultor que estuvo en la reunión, y es la que más pesa: se puede entrar
      todos los días y escribir muchísimo sin aportarle nada a nadie. No hay ninguna columna que diga si un texto
      lo escribió una inteligencia artificial, porque eso no se puede saber con certeza; lo que hay es cuánto texto
      entró pegado y si los datos de la reunión coinciden con la minuta del consultor, que sí se verifica.
    </div>

    <form method="get" class="filters">
      <a href="profesor.php?export=csv" class="btn btn-secondary btn-sm">Descargar CSV</a>
    </form>

    <div class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead>
            <tr>
              <th>Estudiante</th><th>Emprendimiento</th>
              <th class="nowrap">Utilidad</th><th class="nowrap">Entregas</th>
              <th class="nowrap">Específicos</th><th class="nowrap">Horas</th>
              <th class="nowrap">Presencia</th><th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($paneles)): ?>
              <tr>
                <td colspan="8" style="text-align:center;padding:48px 24px;color:var(--ink-2)">
                  <div style="font-size:32px;margin-bottom:10px">🎓</div>
                  <strong style="font-size:16px;color:var(--ink);display:block;margin-bottom:6px">Aún no hay estudiantes con emprendimiento asignado</strong>
                  <p style="margin:0 auto;max-width:540px;line-height:1.5;font-size:14px;color:var(--ink-2)">
                    Los alumnos se encuentran completando el registro y evaluación de autopercepción. A medida que la coordinación les asigne su emprendimiento en marcha, acá podrás ver sus entregas, horas de trabajo y las devoluciones de los consultores.
                  </p>
                </td>
              </tr>
            <?php else: ?>
            <?php foreach ($paneles as $id => $p): $r = $p['resumen']; $faltan = $r['asignadas'] - $r['entregadas']; ?>
              <tr>
                <td data-col="Estudiante"><strong><?= e($p['estudiante']['nombre']) ?></strong><div class="sub"><?= e($p['estudiante']['username'] ?? '') ?></div></td>
                <td data-col="Emprendimiento"><?= e($p['estudiante']['proyecto_nombre'] ?? '—') ?></td>
                <td class="num" data-col="Utilidad">
                  <?php if ($r['utilidad'] === null): ?>
                    <span class="sub">sin valorar</span>
                  <?php else: ?>
                    <span class="prof-util <?= $r['utilidad'] >= 70 ? 'es-ok' : ($r['utilidad'] >= 40 ? 'es-medio' : 'es-bajo') ?>"><?= $r['utilidad'] ?>%</span>
                  <?php endif; ?>
                </td>
                <td class="num" data-col="Entregas">
                  <?= $r['entregadas'] ?>/<?= $r['asignadas'] ?>
                  <?php if (!empty($r['vencidas'])): ?>
                    <div class="sub prof-tarde" style="font-weight:600">⚠️ <?= $r['vencidas'] ?> <?= $r['vencidas'] === 1 ? 'vencida' : 'vencidas' ?></div>
                  <?php elseif ($faltan > 0): ?>
                    <div class="sub prof-tarde"><?= $faltan ?> sin entregar</div>
                  <?php endif; ?>
                </td>
                <td class="num" data-col="Específicos"><?= $r['especificos']['total'] ? $r['especificos']['ok'] . '/' . $r['especificos']['total'] : '—' ?></td>
                <td class="num" data-col="Horas"><?= number_format($p['horas']['usadas'], 1, ',', '') ?><div class="sub">de <?= (int) $p['horas']['presupuesto'] ?></div></td>
                <td class="num" data-col="Presencia"><?= $r['ingresos'] ?><div class="sub"><?= $r['minutos_panel'] ?> min</div></td>
                <td class="right"><a class="btn btn-secondary btn-sm" href="?est=<?= e($id) ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php require __DIR__ . '/_footer.php'; ?>
