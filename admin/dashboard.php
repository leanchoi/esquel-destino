<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/jurado.php';

$u = requiere_login();
$pdo = db();

// Alternar pausa de evaluaciones (solo administrador)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && puede('admin')) {
    if (csrf_valido($_POST['csrf_token'] ?? null)) {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'toggle_pause') {
            $nuevo_estado = ($_POST['estado'] ?? '0') === '1' ? '1' : '0';
            $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES ('evaluations_paused', ?)
                                    ON CONFLICT(key) DO UPDATE SET value = excluded.value");
            $stmt->execute([$nuevo_estado]);
            redirect('dashboard.php');
        }
    }
}

$filtros = [
    'program' => $_GET['program'] ?? '',
    'stage'   => $_GET['stage'] ?? '',
    'q'       => trim((string) ($_GET['q'] ?? '')),
    'jurado'  => $_GET['jurado'] ?? '',      // '', 'mio', 'falta', 'completo', 'disenso'
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

// El historial de etapas de todas las listadas, también de una.
$historial = [];
if ($apps) {
    $h = $pdo->prepare(
        "SELECT application_id, username, stage_from, stage_to, created_at
           FROM stage_history WHERE application_id IN ($ph) ORDER BY created_at DESC"
    );
    $h->execute($ids);
    foreach ($h->fetchAll() as $row) {
        $historial[$row['application_id']][] = $row;
    }
}

// El jurado de hoy y todos sus votos, también en una sola consulta.
$jurado   = jurado($pdo);
$votos    = evaluaciones_de($pdo, array_column($apps, 'id'));
$miId     = (int) $u['id'];
$soyJuez  = es_jurado($u);       // rol exacto: el admin coordina, no vota
// Sólo el admin ve quién votó qué. Entre evaluadores el voto es secreto: saber
// lo que puso el otro antes de terminar el propio contamina la evaluación.
$puedeVerVotos = puede('admin');

foreach ($apps as $i => $a) {
    $vs = $votos[$a['id']] ?? [];
    $apps[$i]['votos']       = $vs;
    // El promedio se libera cuando votó todo el jurado. Hasta entonces un
    // evaluador ve el suyo, cuántos votaron y quiénes faltan, pero no el
    // número general: verlo antes de emitir el propio ya no es evaluar solo.
    $apps[$i]['consolidado'] = consolidado_para(consolidar($vs, $jurado), $puedeVerVotos);
    $apps[$i]['miVoto']      = voto_de($vs, $miId);
    // El DNI no dice nada del proyecto: no sirve para evaluarlo. Lo ve quien
    // hace la parte administrativa y nadie más. Se saca acá, en el servidor,
    // para que no le llegue a la pantalla de quien no tiene por qué verlo.
    $misDetalles = $detalles[$a['id']] ?? [];
    if (!$puedeVerVotos) {
        foreach (DETALLES_SOLO_ADMIN as $reservada) {
            unset($misDetalles[$reservada]);
        }
    }
    $apps[$i]['detalles']    = $misDetalles;
    $apps[$i]['claves']      = claves_detalle($misDetalles);
    $apps[$i]['historial']   = $historial[$a['id']] ?? [];

    // El voto es secreto entre pares: un evaluador ve el suyo, cuántos votaron
    // y el promedio, pero no quién puso qué. Se filtra acá, del lado del
    // servidor, y no en la pantalla: lo que no se manda no se puede espiar
    // mirando el código de la página.
    if (!$puedeVerVotos) {
        $apps[$i]['votos'] = $apps[$i]['miVoto'] ? [$apps[$i]['miVoto']] : [];
    }
}

// Filtro por estado del jurado. Va después de la consulta porque se calcula
// juntando votos, no leyendo una columna.
if ($filtros['jurado'] !== '') {
    $apps = array_values(array_filter($apps, function ($a) use ($filtros) {
        $c = $a['consolidado'];
        return match ($filtros['jurado']) {
            'mio'      => $a['miVoto'] === null,
            'falta'    => !$c['completo'],
            'completo' => $c['completo'],
            'disenso'  => $c['disenso'],
            default    => true,
        };
    }));
}

// Métricas sobre el total, no sobre el filtro activo.
$tot = $pdo->query("SELECT
    COUNT(*) total,
    SUM(CASE WHEN program='Acelera' THEN 1 ELSE 0 END) acelera,
    SUM(CASE WHEN program='Raiz' THEN 1 ELSE 0 END) raiz,
    SUM(CASE WHEN stage='Aprobado' THEN 1 ELSE 0 END) aprobados
    FROM applications")->fetch();

// Cuántas están esperando mi voto y cuántas ya tienen el jurado entero.
$sinMiVoto = 0;
$conJuradoCompleto = 0;
$stmtTodas = $pdo->query('SELECT id FROM applications');
$idsTodas = array_map('intval', $stmtTodas->fetchAll(PDO::FETCH_COLUMN));
$votosTodos = evaluaciones_de($pdo, $idsTodas);
foreach ($idsTodas as $idApp) {
    $vs = $votosTodos[$idApp] ?? [];
    if (voto_de($vs, $miId) === null) {
        $sinMiVoto++;
    }
    if (consolidar($vs, $jurado)['completo']) {
        $conJuradoCompleto++;
    }
}

$queryFiltros = http_build_query(array_filter($filtros));

// Ranking: las mismas postulaciones, ordenadas por lo que decidió el jurado.
// Sin puntaje van al final; entre ellas, la más vieja primero, que es la que
// lleva más tiempo esperando.
//
// Ojo con esto: ordena por el puntaje YA filtrado, y tiene que seguir siendo
// así. Para un evaluador, las que todavía no liberaron promedio llegan con
// puntaje null y caen todas juntas al final, ordenadas por fecha. Si se
// ordenara por el puntaje real y sólo se escondiera el número, el orden
// cantaría igual quién va ganando: esconder la cifra y dejar la fila en su
// posición no esconde nada.
$ranking = $apps;
usort($ranking, function ($x, $y) {
    $px = $x['consolidado']['puntaje'];
    $py = $y['consolidado']['puntaje'];
    if ($px === null && $py === null) {
        return strcmp((string) $x['submitted_at'], (string) $y['submitted_at']);
    }
    if ($px === null) return 1;
    if ($py === null) return -1;
    return $py <=> $px;
});

$pageTitle = 'Postulaciones';
$nav = 'postulaciones';
require __DIR__ . '/_header.php';

/**
 * Sello del jurado: cuántos votaron sobre cuántos, y si hay disenso.
 * Lleva data-sello para que el JS lo repinte tras un voto sin recargar la página.
 */
function sello_jurado(array $c, int $id): string
{
    $clase = $c['completo'] ? 'is-completo' : ($c['emitidos'] > 0 ? 'is-parcial' : 'is-vacio');
    $texto = $c['emitidos'] . '/' . max($c['jurados'], $c['emitidos']);
    $titulo = $c['faltan']
        ? 'Falta votar: ' . implode(', ', $c['faltan'])
        : ($c['jurados'] === 0 ? 'No hay jurados cargados' : 'Votó todo el jurado');
    $html = '<span class="jurado-sello ' . $clase . '" title="' . e($titulo) . '">'
          . '<span class="js-n">' . e($texto) . '</span></span>';
    if ($c['disenso']) {
        $html .= ' <span class="tag-disenso" title="Entre el voto más alto y el más bajo hay '
               . e(number_format($c['dispersion'], 2)) . ' puntos">disenso</span>';
    }
    return '<span class="sello-wrap" data-sello="' . $id . '">' . $html . '</span>';
}

/**
 * El puntaje del jurado, o por qué todavía no se muestra.
 *
 * Una raya cuando en realidad hay votos se lee como "no la miró nadie", que es
 * otra cosa. Los tres puntos dicen que el número existe pero no está liberado,
 * y el title lo explica al pasar por encima.
 */
function puntaje_jurado(array $c): string
{
    if (($c['liberado'] ?? true) === false && $c['emitidos'] > 0) {
        $falta = $c['faltan'] ? 'Falta votar: ' . implode(', ', $c['faltan']) . '.' : 'Falta que vote el resto del jurado.';
        return '<span class="score-reservado" title="' . e($falta . ' El promedio se libera cuando esté completo.') . '">···</span>';
    }
    return $c['puntaje'] === null ? '—' : e(number_format($c['puntaje'], 2));
}
?>

<div class="admin-topbar">
  <h1>Postulaciones</h1>
  <span class="admin-count"><?= count($apps) ?> de <?= (int) $tot['total'] ?></span>
</div>

<div class="admin-content">

  <?php if (evaluaciones_pausadas($pdo)): ?>
    <div class="callout" style="margin-bottom: 24px; border-left: 4px solid #d97706; background: #fffbeb; color: #92400e; padding: 14px 18px; border-radius: 6px; font-size: 15px;">
      <strong>Votación pausada temporalmente.</strong> 
      <?= puede('admin') 
        ? 'Las evaluaciones y cambios de votos están suspendidos para los jurados. Podés reanudarlos usando el botón en la barra de filtros.' 
        : 'La coordinación ha pausado la votación de forma temporal. Podés ver los proyectos y tu historial, pero no es posible emitir nuevos votos ni editar comentarios.' 
      ?>
    </div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat"><span class="k">Total</span><span class="v"><?= (int) $tot['total'] ?></span></div>
    <div class="stat acelera"><span class="k">Acelera</span><span class="v"><?= (int) $tot['acelera'] ?></span></div>
    <div class="stat raiz"><span class="k">Raíz</span><span class="v"><?= (int) $tot['raiz'] ?></span></div>
    <?php if ($soyJuez): ?>
      <a class="stat<?= $sinMiVoto > 0 ? ' alerta' : '' ?>" href="?jurado=mio">
        <span class="k">Falta tu voto</span><span class="v"><?= $sinMiVoto ?></span>
      </a>
    <?php else: ?>
      <div class="stat"><span class="k">Jurados</span><span class="v"><?= count($jurado) ?></span></div>
    <?php endif; ?>
    <a class="stat ok" href="?jurado=completo">
      <span class="k">Jurado completo</span><span class="v"><?= $conJuradoCompleto ?></span>
    </a>
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
    <select name="jurado">
      <option value="">Todo el jurado</option>
      <?php
      // "Con disenso" mide la distancia entre el voto más alto y el más bajo,
      // así que es información sobre los votos ajenos: va sólo para el admin.
      // Y si se ofreciera igual, para un evaluador no devolvería nunca nada,
      // que es la peor forma de esconder algo: parece que el panel falla.
      $opcionesJurado = [
        'mio'      => 'Me falta votar',
        'falta'    => 'Falta algún jurado',
        'completo' => 'Votó todo el jurado',
      ];
      if ($puedeVerVotos) {
          $opcionesJurado['disenso'] = 'Con disenso';
      }
      foreach ($opcionesJurado as $k => $lbl): ?>
        <option value="<?= e($k) ?>" <?= $filtros['jurado'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="search" name="q" value="<?= e($filtros['q']) ?>" placeholder="Buscar proyecto, persona o correo…">
    <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
    <?php if (array_filter($filtros)): ?><a href="dashboard.php" class="clear">Limpiar</a><?php endif; ?>

    <span class="grow"></span>
    <div class="view-toggle" role="tablist" aria-label="Cómo ver las postulaciones">
      <button type="button" class="is-active" id="viewTable" role="tab" aria-selected="true">Tabla</button>
      <button type="button" id="viewRank" role="tab" aria-selected="false">Ranking</button>
      <button type="button" id="viewCards" role="tab" aria-selected="false">Tablero</button>
    </div>
    <?php /* El CSV lleva el voto y el comentario de cada jurado con nombre y
             apellido, y el DNI de cada postulante: es del admin. */ ?>
    <?php if (puede('admin')): 
      $pausadas = evaluaciones_pausadas($pdo); ?>
      <a href="api.php?export=csv&amp;<?= e($queryFiltros) ?>" class="btn btn-secondary btn-sm">Descargar CSV</a>
      <form method="post" style="display:inline; margin-left:8px;">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="toggle_pause">
        <input type="hidden" name="estado" value="<?= $pausadas ? '0' : '1' ?>">
        <button type="submit" class="btn <?= $pausadas ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
          <?= $pausadas ? '🟢 Habilitar Votación' : '⏸️ Pausar Votación' ?>
        </button>
      </form>
    <?php endif; ?>
  </form>

  <?php if (!$apps): ?>
    <div class="empty-state">No hay postulaciones que coincidan con estos filtros.</div>
  <?php else: ?>

    <!-- ------------------------------------------------------------ tabla -->
    <div id="tableView" class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead>
            <tr>
              <th>Proyecto</th><th>Responsable</th><th>Línea</th>
              <th>Recibida</th><th>Estado</th><th>Jurado</th><th>Puntaje</th><th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($apps as $a):
              $p = programa_info($a['program']);
              $s = estado_info($a['stage']);
              $c = $a['consolidado'];
            ?>
              <tr<?= $soyJuez && $a['miVoto'] === null ? ' class="sin-mi-voto"' : '' ?>>
                <td><strong><?= e($a['name']) ?></strong></td>
                <td>
                  <div><?= e($a['contact_name']) ?></div>
                  <div class="sub"><?= e($a['email']) ?></div>
                </td>
                <td><span class="chip" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span></td>
                <td class="nowrap sub"><?= e(fecha_corta($a['submitted_at'], true)) ?></td>
                <td><span class="chip" style="--c:<?= e($s['color']) ?>"><?= e($s['label']) ?></span></td>
                <td class="nowrap"><?= sello_jurado($c, (int) $a['id']) ?></td>
                <td class="score" data-puntaje="<?= (int) $a['id'] ?>"><?= puntaje_jurado($c) ?></td>
                <td class="right">
                  <button type="button" class="btn btn-secondary btn-sm" data-abrir="<?= (int) $a['id'] ?>">
                    <?= $soyJuez ? ($a['miVoto'] === null ? 'Evaluar' : 'Ver / editar') : 'Ver' ?>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ---------------------------------------------------------- ranking -->
    <div id="rankView" class="panel" hidden>
      <h2 class="panel-title">Ranking del jurado</h2>
      <p class="hint" style="margin-top:-8px">
        Ordenado por el promedio de los votos emitidos. Ojo con las que todavía no tienen
        el jurado completo: un promedio de un solo voto no se compara con uno de cuatro.
      </p>
      <ol class="ranking">
        <?php foreach ($ranking as $i => $a):
          $c = $a['consolidado'];
          $p = programa_info($a['program']);
          $ancho = $c['puntaje'] === null ? 0 : round($c['puntaje'] * 20); ?>
          <li class="rank-row<?= $c['puntaje'] === null ? ' is-sin-votos' : '' ?>">
            <span class="rank-pos"><?= $c['puntaje'] === null ? '—' : $i + 1 ?></span>
            <button type="button" class="rank-main" data-abrir="<?= (int) $a['id'] ?>">
              <span class="rank-nombre"><?= e($a['name']) ?></span>
              <span class="rank-meta">
                <span class="chip" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span>
                <?= sello_jurado($c, (int) $a['id']) ?>
              </span>
            </button>
            <span class="rank-barra"><span style="width:<?= $ancho ?>%"></span></span>
            <span class="rank-num"><?= puntaje_jurado($c) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>

    <!-- ---------------------------------------------------------- tablero -->
    <div id="cardsView" class="kanban" hidden>
      <?php foreach (ESTADOS as $slug => $info):
        $col = array_values(array_filter($apps, fn($a) => $a['stage'] === $slug)); ?>
        <div class="kcol" data-estado="<?= e($slug) ?>">
          <div class="kcol-head" style="--c:<?= e($info['color']) ?>">
            <span><?= e($info['label']) ?></span><span class="n"><?= count($col) ?></span>
          </div>
          <div class="kcol-body" data-drop="<?= e($slug) ?>">
            <?php foreach ($col as $a):
              $p = programa_info($a['program']);
              $c = $a['consolidado']; ?>
              <article class="kcard" data-id="<?= (int) $a['id'] ?>"<?= puede('admin') ? ' draggable="true"' : '' ?>>
                <button type="button" class="kcard-abrir" data-abrir="<?= (int) $a['id'] ?>" title="<?= e($a['name']) ?>">
                  <?php // El nombre va primero y es lo más grande de la tarjeta.
                        // Antes arrancaba con la píldora de color de la línea, que
                        // por ser lo único con color se llevaba la mirada y dejaba
                        // al título de segundo: en un tablero uno busca el proyecto,
                        // no la línea. La línea ahora es un punto al lado del
                        // responsable, que alcanza para distinguirla. ?>
                  <span class="kcard-t"><?= e($a['name']) ?></span>
                  <span class="kcard-s">
                    <span class="kcard-linea" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span>
                    <span class="kcard-resp"><?= e($a['contact_name']) ?></span>
                  </span>
                  <span class="kcard-f">
                    <?= sello_jurado($c, (int) $a['id']) ?>
                    <span class="kcard-pts" data-puntaje="<?= (int) $a['id'] ?>"><?= ($c['liberado'] ?? true) === false && $c['emitidos'] > 0 ? '···' : ($c['puntaje'] === null ? 'sin puntaje' : e(number_format($c['puntaje'], 2))) ?></span>
                  </span>
                </button>
                <?php if (puede('admin')): ?>
                  <label class="kcard-mover">
                    <span class="sr-only">Mover «<?= e($a['name']) ?>» a otro estado</span>
                    <select data-mover="<?= (int) $a['id'] ?>">
                      <?php foreach (ESTADOS as $k => $s): ?>
                        <option value="<?= e($k) ?>" <?= $a['stage'] === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
            <?php if (!$col): ?><div class="kempty">—</div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (puede('admin')): ?>
      <p class="hint kanban-ayuda" id="kanbanAyuda" hidden>
        Arrastrá una tarjeta a otra columna para cambiarle el estado, o usá el selector de
        abajo de cada tarjeta —que además es el camino en el celular y con el teclado.
        Cada movimiento queda firmado con tu usuario en el historial.
      </p>
    <?php elseif ($soyJuez): ?>
      <p class="hint kanban-ayuda" id="kanbanAyuda" hidden>
        El estado lo mueve un administrador. Vos evaluás y comentás: tu voto entra en el
        promedio del jurado.
      </p>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php if (puede('admin')): ?>
  <datalist id="listaBarrios">
    <?php foreach (BARRIOS as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?>
  </datalist>
<?php endif; ?>

<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="drawer" id="drawer" hidden aria-label="Detalle de la postulación">
  <div class="drawer-head">
    <div>
      <h2 id="dTitulo">—</h2>
      <p id="dSub" class="sub"></p>
    </div>
    <button type="button" class="drawer-close" id="drawerClose" aria-label="Cerrar">&times;</button>
  </div>
  <div class="drawer-tabs" id="drawerTabs" role="tablist">
    <button type="button" role="tab" data-tab="jurado" class="is-active">Jurado</button>
    <button type="button" role="tab" data-tab="respuestas">Respuestas</button>
    <button type="button" role="tab" data-tab="proceso">Proceso</button>
    <?php /* Un evaluador ve acá su propio historial y puede volver a una
             versión anterior. El admin ve el de todo el jurado, con el detalle
             de qué se sacó y qué se agregó en cada guardado. */ ?>
    <button type="button" role="tab" data-tab="cambios">Cambios</button>
  </div>
  <div class="drawer-body" id="drawerBody"></div>
</aside>

<script id="datosApps" type="application/json"><?= json_encode(
    $apps,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?></script>
<script id="configCrm" type="application/json"><?= json_encode([
    'criterios'   => CRITERIOS,
    'estados'     => ESTADOS,
    'etiquetas'   => ETIQUETAS_DETALLE,
    'minComentario' => MIN_COMENTARIO,
    'pistas'      => PISTAS_COMENTARIO,
    'jurado'      => $jurado,
    'yo'          => ['id' => $miId, 'username' => $u['username'], 'role' => $u['role']],
    'puedeVotar'  => $soyJuez && !evaluaciones_pausadas($pdo),
    'puedeVerVotos' => $puedeVerVotos,
    'puedeEstado' => puede('admin'),
    'puedeBorrar' => puede('admin'),
    'csrf'        => csrf_token(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?php require __DIR__ . '/_footer.php'; ?>
