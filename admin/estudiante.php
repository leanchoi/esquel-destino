<?php
/**
 * Vista del estudiante ISET 815.
 *
 * Es la única pantalla que ve un estudiante, y tiene que responder cuatro
 * preguntas sin que tenga que preguntarle nada a nadie:
 *
 *   ¿con qué emprendimiento trabajo?   ¿qué tengo que hacer ahora?
 *   ¿cuándo es la próxima reunión?     ¿cuántas horas llevo?
 *
 * Qué NO se le muestra, y es a propósito: los datos de contacto del
 * emprendedor. El estudiante trabaja con el proyecto, no le escribe al titular
 * por su cuenta —eso lo coordina el consultor— y el teléfono y el correo de
 * una persona no tienen por qué circular más de lo necesario. Lo que sí ve es
 * todo el contexto del caso: diagnóstico, trabas, ejes y la minuta de arranque.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/estudiantes.php';

$u = requiere_estudiante();
$pdo = db();

// El admin puede espiar la vista de cualquiera con ?est=, para poder
// acompañar a un estudiante que no entiende qué tiene que hacer.
$ficha = null;
if (($u['role'] ?? '') === 'admin' && !empty($_GET['est'])) {
    $st = $pdo->prepare(
        "SELECT e.*, c.nombre, c.color, p.nombre AS proyecto_nombre, p.titular, p.celula, p.linea
           FROM lab_estudiantes e
           JOIN lab_consultores c ON c.id = e.consultor_id
           LEFT JOIN lab_proyectos p ON p.id = e.proyecto_id
          WHERE e.consultor_id = ?"
    );
    $st->execute([(string) $_GET['est']]);
    $ficha = $st->fetch() ?: null;
} else {
    $ficha = ficha_estudiante($u);
}

$pageTitle = 'Mi emprendimiento';
$nav = 'estudiante';

if (!$ficha) {
    require __DIR__ . '/_header.php';
    echo '<div class="admin-content"><div class="empty-state">Todavía no tenés un emprendimiento asignado. '
       . 'Escribile a la coordinación del programa para que te asignen uno.</div></div>';
    require __DIR__ . '/_footer.php';
    exit;
}

$estudianteId = $ficha['consultor_id'];
$horas = horas_estudiante($pdo, $estudianteId);

// El proyecto completo, sin los datos de contacto del titular.
$proy = $pdo->prepare('SELECT * FROM lab_proyectos WHERE id = ?');
$proy->execute([$ficha['proyecto_id']]);
$proyecto = $proy->fetch() ?: [];

// Sus reuniones, con quién más va a estar
$reu = $pdo->prepare(
    "SELECT r.*, (
        SELECT GROUP_CONCAT(c2.nombre, ' · ')
          FROM lab_reunion_asistentes a2
          JOIN lab_consultores c2 ON c2.id = a2.consultor_id
         WHERE a2.reunion_id = r.id AND c2.tipo <> 'estudiante'
     ) AS equipo
       FROM lab_reuniones r
       JOIN lab_reunion_asistentes a ON a.reunion_id = r.id AND a.consultor_id = ?
      ORDER BY r.fecha, r.hora_inicio"
);
$reu->execute([$estudianteId]);
$reuniones = $reu->fetchAll();

// Sus consignas
$tar = $pdo->prepare('SELECT * FROM lab_tareas_estudiante WHERE estudiante_id = ? ORDER BY vence_at');
$tar->execute([$estudianteId]);
$tareas = $tar->fetchAll();

$hoy = date('Y-m-d H:i');
$pendientes = array_values(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente'));
$entregadas = array_values(array_filter($tareas, fn($t) => $t['estado'] !== 'pendiente'));
$vencidas = array_values(array_filter($pendientes, fn($t) => $t['vence_at'] !== '' && $t['vence_at'] < $hoy));

$proximaReunion = null;
foreach ($reuniones as $r) {
    if ($r['fecha'] >= date('Y-m-d')) { $proximaReunion = $r; break; }
}

/** Lista JSON guardada en el proyecto, tolerante a basura. */
function lista_json(?string $raw): array
{
    $x = json_decode((string) $raw, true);
    return is_array($x) ? $x : [];
}

require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Mi emprendimiento</h1>
  <span class="admin-count"><?= e($ficha['nombre']) ?> · <?= e($ficha['instituto']) ?></span>
</div>

<div class="admin-content est-vista">

  <!-- El caso -->
  <section class="panel est-caso">
    <div class="est-caso-head">
      <div>
        <span class="est-eyebrow">Tu emprendimiento para toda la cohorte</span>
        <h2><?= e($ficha['proyecto_nombre'] ?? '—') ?></h2>
        <p class="est-sub"><?= e($proyecto['titular'] ?? '') ?> · Línea <?= e($proyecto['linea'] ?? '—') ?> · Célula <?= (int) ($proyecto['celula'] ?? 0) ?></p>
      </div>
      <div class="est-horas" title="Presupuesto del convenio: <?= (int) $horas['presupuesto'] ?> horas">
        <span class="est-horas-n"><?= number_format($horas['usadas'], 1, ',', '') ?> <small>de <?= (int) $horas['presupuesto'] ?> h</small></span>
        <div class="est-barra"><span style="width:<?= (int) $horas['porcentaje'] ?>%"></span></div>
        <span class="est-horas-d"><?= $horas['cantidad_reuniones'] ?> reuniones · <?= number_format($horas['tareas'], 1, ',', '') ?> h de trabajo propio</span>
      </div>
    </div>

    <?php if ($proximaReunion): ?>
      <div class="est-proxima">
        <span class="k">Próxima reunión</span>
        <strong><?= e(fecha_larga($proximaReunion['fecha'])) ?><?= $proximaReunion['hora_inicio'] ? ' · ' . e($proximaReunion['hora_inicio']) : '' ?></strong>
        <span><?= e($proximaReunion['titulo']) ?><?= $proximaReunion['equipo'] ? ' · con ' . e($proximaReunion['equipo']) : '' ?></span>
      </div>
    <?php endif; ?>
  </section>

  <!-- Lo que hay que hacer -->
  <section>
    <h2 class="est-h2">Lo que tenés que hacer
      <?php if ($vencidas): ?><span class="est-alerta"><?= count($vencidas) ?> vencida<?= count($vencidas) === 1 ? '' : 's' ?></span><?php endif; ?>
    </h2>

    <?php if (!$pendientes): ?>
      <div class="empty-state">No tenés nada pendiente. Cuando se acerque la próxima reunión te va a aparecer acá.</div>
    <?php endif; ?>

    <?php foreach ($pendientes as $t):
      $m = MOMENTOS_ESTUDIANTE[$t['momento']] ?? ['label' => $t['momento']];
      $vencida = $t['vence_at'] !== '' && $t['vence_at'] < $hoy;
      $reunion = null;
      foreach ($reuniones as $r) { if ($r['id'] === $t['reunion_id']) { $reunion = $r; break; } }
    ?>
      <article class="est-tarea<?= $vencida ? ' es-vencida' : '' ?>" data-tarea="<?= (int) $t['id'] ?>">
        <header class="est-tarea-head">
          <div>
            <span class="est-momento est-m-<?= e($t['momento']) ?>"><?= e($m['label']) ?></span>
            <h3><?= e($t['titulo']) ?></h3>
            <?php if ($reunion): ?>
              <p class="est-sub"><?= e($reunion['titulo']) ?> · <?= e(fecha_larga($reunion['fecha'])) ?></p>
            <?php endif; ?>
          </div>
          <span class="est-vence<?= $vencida ? ' es-vencida' : '' ?>">
            <?= $t['vence_at'] === '' ? 'Sin fecha' : ($vencida ? 'Venció el ' : 'Hasta el ') . e(fecha_corta($t['vence_at'], true)) ?>
          </span>
        </header>

        <p class="est-consigna"><?= e($t['consigna']) ?></p>

        <form class="est-form" data-id="<?= (int) $t['id'] ?>">
          <?php if ($t['momento'] === 'durante'): ?>
            <?php foreach (ESPECIFICOS_REUNION as $clave => $def): ?>
              <div class="field">
                <label class="lbl" for="e<?= (int) $t['id'] ?>-<?= e($clave) ?>">
                  <?= e($def['label']) ?>
                  <span class="est-min"><?= $def['minimo'] ?> como mínimo, uno por línea</span>
                </label>
                <p class="hint"><?= e($def['ayuda']) ?></p>
                <textarea id="e<?= (int) $t['id'] ?>-<?= e($clave) ?>" name="esp[<?= e($clave) ?>]" rows="3" data-telemetria></textarea>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="field">
              <label class="lbl" for="e<?= (int) $t['id'] ?>-entrega">Tu entrega</label>
              <textarea id="e<?= (int) $t['id'] ?>-entrega" name="entrega" rows="10" data-telemetria
                        placeholder="Escribí acá. Podés guardar borradores todas las veces que quieras."></textarea>
            </div>
          <?php endif; ?>

          <div class="est-acciones">
            <span class="est-contador" data-contador data-min="<?= (int) $t['min_caracteres'] ?>"></span>
            <button type="button" class="btn btn-secondary btn-sm" data-accion="borrador">Guardar borrador</button>
            <button type="submit" class="btn btn-primary btn-sm">Entregar</button>
            <span class="drawer-msg" data-msg></span>
          </div>
        </form>
      </article>
    <?php endforeach; ?>
  </section>

  <!-- Contexto del caso -->
  <section class="panel est-contexto">
    <h2 class="est-h2">El caso, para que puedas trabajar sin haber estado antes</h2>
    <p class="est-sub" style="margin-bottom:18px">Esto es lo que el equipo relevó antes de que entraras. Leelo antes de cada reunión: la consigna de sala te pide justamente lo que <em>contradice</em> esto.</p>

    <?php
      $bloques = [
        'diagnostico' => 'Diagnóstico',
        'trabas'      => 'Trabas detectadas',
        'ejes'        => 'Ejes de trabajo',
        'entregables' => 'Entregables comprometidos',
      ];
      foreach ($bloques as $campo => $label):
        $items = lista_json($proyecto[$campo] ?? '');
        if (!$items) continue;
    ?>
      <div class="est-bloque">
        <h3><?= e($label) ?></h3>
        <ul>
          <?php foreach ($items as $i): ?>
            <li><?= e(is_array($i) ? ($i['texto'] ?? json_encode($i, JSON_UNESCAPED_UNICODE)) : (string) $i) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($proyecto['minuta_cero'])): ?>
      <div class="est-bloque">
        <h3>Minuta de arranque</h3>
        <div class="est-minuta"><?= e($proyecto['minuta_cero']) ?></div>
      </div>
    <?php endif; ?>
  </section>

  <!-- Historial -->
  <?php if ($entregadas): ?>
  <section>
    <h2 class="est-h2">Lo que ya entregaste</h2>
    <div class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead><tr><th>Consigna</th><th>Entregada</th><th class="nowrap">Caracteres</th><th>Valoración del consultor</th></tr></thead>
          <tbody>
            <?php foreach (array_reverse($entregadas) as $t): ?>
              <tr>
                <td><strong><?= e($t['titulo']) ?></strong><div class="sub"><?= e(MOMENTOS_ESTUDIANTE[$t['momento']]['label'] ?? '') ?></div></td>
                <td class="nowrap sub"><?= e(fecha_corta($t['entregado_at'], true)) ?></td>
                <td class="num"><?= (int) $t['caracteres'] ?></td>
                <td>
                  <?php if ($t['valoracion'] === ''): ?>
                    <span class="sub">Todavía sin revisar</span>
                  <?php else: $v = VALORACIONES_APORTE[$t['valoracion']] ?? ['label' => $t['valoracion']]; ?>
                    <span class="est-val est-v-<?= e($t['valoracion']) ?>"><?= e($v['label']) ?></span>
                    <?php if ($t['valoracion_nota'] !== ''): ?><div class="sub"><?= e($t['valoracion_nota']) ?></div><?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div>

<script>
  window.ESQUEL_TAREAS_API = 'estudiantes_api.php';
  window.ESQUEL_CSRF = <?= json_encode(csrf_token()) ?>;
</script>
<script src="../<?= asset('assets/js/estudiante.js') ?>"></script>
<?php require __DIR__ . '/_footer.php'; ?>
