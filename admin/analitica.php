<?php
/**
 * Analítica del sitio público. Sólo lee: visitas y las fechas de postulación.
 *
 * Todo lo que dice "día" u "hora" acá es hora de Esquel.
 *
 * SQLite guarda las fechas en UTC —CURRENT_TIMESTAMP no conoce zonas horarias—
 * y Esquel está tres horas atrás. Sin corregirlo, todo lo que pasa después de
 * las nueve de la noche se cuenta al día siguiente, que es justo la franja en
 * la que más se mira el sitio. Por eso cada consulta desplaza la fecha con el
 * huso configurado en config.php, calculado en PHP para que siga siendo cierto
 * si algún día cambia la regla horaria del país.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/analitica.php';

$u = requiere_login();
$pdo = db();

$offset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime('now'));
$LOCAL = sprintf('%d seconds', $offset);   // modificador de SQLite: '-10800 seconds'

$dias = (int) ($_GET['dias'] ?? 30);
if (!in_array($dias, [7, 30, 90], true)) {
    $dias = 30;
}
$desde = "-{$dias} days";

/** Consulta con el filtro de fecha y el huso local ya aplicados. */
function q(PDO $pdo, string $sql, string $desde, array $extra = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([$desde], $extra));
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
    "SELECT date(creada_at, ?) dia, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos
       FROM visitas WHERE creada_at >= datetime('now', ?)
      GROUP BY dia ORDER BY dia", $LOCAL, [$desde]);

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

// ---------------------------------------------------------------- hoy, hora a hora
$hoy = date('Y-m-d');
$filasHora = $pdo->prepare(
    "SELECT CAST(strftime('%H', creada_at, ?) AS INTEGER) hora,
            COUNT(*) vistas, COUNT(DISTINCT visitante) unicos
       FROM visitas WHERE date(creada_at, ?) = ?
      GROUP BY hora ORDER BY hora"
);
$filasHora->execute([$LOCAL, $LOCAL, $hoy]);

$horas = array_fill(0, 24, ['vistas' => 0, 'unicos' => 0]);
foreach ($filasHora->fetchAll() as $f) {
    $horas[(int) $f['hora']] = ['vistas' => (int) $f['vistas'], 'unicos' => (int) $f['unicos']];
}
$horaActual = (int) date('G');

$totHoy = $pdo->prepare(
    "SELECT COUNT(*) vistas, COUNT(DISTINCT visitante) unicos FROM visitas WHERE date(creada_at, ?) = ?"
);
$totHoy->execute([$LOCAL, $hoy]);
$hoyTot = $totHoy->fetch();

// Mismo corte de hora, pero ayer: sirve para saber si el día viene bien o mal
// sin esperar a que termine.
$ayerHasta = $pdo->prepare(
    "SELECT COUNT(DISTINCT visitante) FROM visitas
      WHERE date(creada_at, ?) = date(?, '-1 day')
        AND CAST(strftime('%H', creada_at, ?) AS INTEGER) <= ?"
);
$ayerHasta->execute([$LOCAL, $hoy, $LOCAL, $horaActual]);
$ayerAEstaHora = (int) $ayerHasta->fetchColumn();

$postHoy = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE date(submitted_at, ?) = ?");
$postHoy->execute([$LOCAL, $hoy]);
$postulacionesHoy = (int) $postHoy->fetchColumn();

// ------------------------------------------------------- postulaciones por día
$filasPost = $pdo->prepare(
    "SELECT date(submitted_at, ?) dia, program, COUNT(*) n
       FROM applications GROUP BY dia, program ORDER BY dia"
);
$filasPost->execute([$LOCAL]);

$postPorDia = [];
foreach ($filasPost->fetchAll() as $f) {
    $postPorDia[$f['dia']][$f['program']] = (int) $f['n'];
}

// La ventana es toda la convocatoria: un día sin postulaciones también es un
// dato, y sólo se ve si el día está dibujado aunque esté vacío.
$inicio = new DateTimeImmutable(date('Y-m-d', strtotime(FECHA_APERTURA)));
$finCal = new DateTimeImmutable(date('Y-m-d', strtotime(FECHA_CIERRE)));
if ($postPorDia) {
    $primera = new DateTimeImmutable(min(array_keys($postPorDia)));
    if ($primera < $inicio) {
        $inicio = $primera;               // llegó algo antes de abrir: se muestra igual
    }
}
$hoyDT = new DateTimeImmutable($hoy);
$hasta = $hoyDT < $finCal ? $finCal : ($hoyDT > $finCal ? $hoyDT : $finCal);

$calendario = [];
$acumulado = 0;
$maxPost = 0;
for ($d = $inicio; $d <= $hasta; $d = $d->modify('+1 day')) {
    $clave = $d->format('Y-m-d');
    $delDia = $postPorDia[$clave] ?? [];
    $n = array_sum($delDia);
    $acumulado += $n;
    $maxPost = max($maxPost, $n);
    $calendario[] = [
        'fecha'     => $clave,
        'dt'        => $d,
        'total'     => $n,
        'programas' => $delDia,
        'acumulado' => $acumulado,
        'futuro'    => $clave > $hoy,
        'hoy'       => $clave === $hoy,
    ];
}
$totalPostulaciones = $acumulado;

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

/**
 * Curva de un día en SVG.
 *
 * Se dibuja a mano y no con una librería: son veinticuatro números y el sitio
 * no tiene paso de compilación. El SVG escala solo con el ancho del panel.
 */
function curva_horaria(array $horas, int $horaActual): string
{
    $W = 720; $H = 190; $izq = 34; $der = 10; $arriba = 14; $abajo = 26;
    $ancho = $W - $izq - $der;
    $alto  = $H - $arriba - $abajo;

    $vals = array_map(fn($h) => $h['vistas'], $horas);
    $tope = max(1, max($vals));
    // Techo redondeado para que la línea del eje sea un número legible.
    $paso = $tope <= 10 ? 2 : ($tope <= 50 ? 10 : ($tope <= 200 ? 25 : 100));
    $tope = (int) (ceil($tope / $paso) * $paso);

    $x = fn(int $i) => $izq + ($ancho * $i / 23);
    $y = fn(float $v) => $arriba + $alto - ($alto * $v / $tope);

    // La curva llega hasta la hora en curso y ahí se corta. Dibujar las horas
    // que todavía no pasaron como una línea en cero sería mostrar como dato lo
    // que en realidad es futuro: parecería que el tráfico se derrumbó.
    $puntos = [];
    for ($i = 0; $i <= $horaActual; $i++) {
        $puntos[] = round($x($i), 1) . ',' . round($y($vals[$i]), 1);
    }
    $linea = 'M' . implode(' L', $puntos);
    $area  = $linea . ' L' . round($x($horaActual), 1) . ',' . round($y(0), 1)
                    . ' L' . round($x(0), 1) . ',' . round($y(0), 1) . ' Z';

    $svg = '<svg class="curva" viewBox="0 0 ' . $W . ' ' . $H . '" preserveAspectRatio="none" role="img" '
         . 'aria-label="Visitas de hoy hora por hora">';

    // grilla y eje
    for ($g = 0; $g <= 2; $g++) {
        $v = $tope * $g / 2;
        $py = round($y($v), 1);
        $svg .= '<line class="g-linea" x1="' . $izq . '" y1="' . $py . '" x2="' . ($W - $der) . '" y2="' . $py . '"/>';
        $svg .= '<text class="g-eje" x="' . ($izq - 7) . '" y="' . ($py + 4) . '" text-anchor="end">' . (int) $v . '</text>';
    }

    // la hora en curso, todavía incompleta
    $svg .= '<line class="g-ahora" x1="' . round($x($horaActual), 1) . '" y1="' . $arriba
          . '" x2="' . round($x($horaActual), 1) . '" y2="' . ($arriba + $alto) . '"/>';

    $svg .= '<path class="g-area" d="' . $area . '"/><path class="g-linea-datos" d="' . $linea . '"/>';

    for ($i = 0; $i <= $horaActual; $i++) {
        if ($vals[$i] === 0) {
            continue;
        }
        $svg .= '<circle class="g-punto" cx="' . round($x($i), 1) . '" cy="' . round($y($vals[$i]), 1) . '" r="3">'
              . '<title>' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . ':00 · ' . $vals[$i] . ' vistas de '
              . $horas[$i]['unicos'] . ' personas</title></circle>';
    }
    for ($i = 0; $i <= 23; $i += 3) {
        $svg .= '<text class="g-hora" x="' . round($x($i), 1) . '" y="' . ($H - 8) . '" text-anchor="middle">'
              . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . '</text>';
    }

    return $svg . '</svg>';
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

  <!-- ---------------- el día de hoy ---------------- -->
  <div class="panel panel-hoy">
    <div class="hoy-head">
      <div>
        <h2 class="panel-title">Hoy, hora por hora</h2>
        <p class="hint" style="margin:0"><?= e(fecha_larga($hoy)) ?> · hora de Esquel</p>
      </div>
      <div class="hoy-cifras">
        <div class="hc">
          <span class="k">Personas</span>
          <span class="v"><?= (int) $hoyTot['unicos'] ?></span>
          <?php
          $delta = (int) $hoyTot['unicos'] - $ayerAEstaHora;
          if ($ayerAEstaHora > 0):
            $signo = $delta > 0 ? 'sube' : ($delta < 0 ? 'baja' : 'igual'); ?>
            <span class="d <?= $signo ?>">
              <?= $delta > 0 ? '+' : '' ?><?= $delta ?> vs. ayer a esta hora
            </span>
          <?php else: ?>
            <span class="d">sin comparación de ayer</span>
          <?php endif; ?>
        </div>
        <div class="hc"><span class="k">Vistas</span><span class="v"><?= (int) $hoyTot['vistas'] ?></span></div>
        <div class="hc"><span class="k">Postulaciones</span><span class="v"><?= $postulacionesHoy ?></span></div>
      </div>
    </div>
    <?php if ((int) $hoyTot['vistas'] === 0): ?>
      <p class="hint">Todavía no entró nadie hoy. La curva aparece con la primera visita.</p>
    <?php else: ?>
      <?= curva_horaria($horas, $horaActual) ?>
      <p class="hint">
        La línea vertical marca la hora en curso: esa franja todavía se está llenando.
        Pasá el dedo o el mouse por un punto para ver el detalle.
      </p>
    <?php endif; ?>
  </div>

  <div class="stats" style="margin-top:22px">
    <div class="stat"><span class="k">Visitas</span><span class="v"><?= (int) $tot['vistas'] ?></span></div>
    <div class="stat"><span class="k">Personas distintas</span><span class="v"><?= (int) $tot['unicos'] ?></span></div>
    <div class="stat"><span class="k">Tiempo promedio</span><span class="v"><?= $tot['seg'] ? round($tot['seg']) . 's' : '—' ?></span></div>
    <div class="stat"><span class="k">Scroll promedio</span><span class="v"><?= $tot['prof'] ? round($tot['prof']) . '%' : '—' ?></span></div>
    <div class="stat ok"><span class="k">Postulaciones</span><span class="v"><?= $totalEnviadas ?></span></div>
  </div>

  <!-- ---------------- cuándo se postulan ---------------- -->
  <div class="panel">
    <div class="hoy-head">
      <div>
        <h2 class="panel-title">Cuándo se postulan</h2>
        <p class="hint" style="margin:0">
          Del <?= e(fecha_larga($calendario[0]['fecha'])) ?> al
          <?= e(fecha_larga($calendario[count($calendario) - 1]['fecha'])) ?>,
          día por día. Los días vacíos también están dibujados: que no entre nada
          también es información.
        </p>
      </div>
      <div class="hoy-cifras">
        <div class="hc"><span class="k">Total</span><span class="v"><?= $totalPostulaciones ?></span></div>
        <div class="hc"><span class="k">Faltan</span><span class="v"><?= dias_para_cierre() ?><span class="u">días</span></span></div>
      </div>
    </div>

    <?php if ($totalPostulaciones === 0): ?>
      <p class="hint">Todavía no entró ninguna postulación. El gráfico se dibuja con la primera.</p>
    <?php else: ?>
      <div class="calendario" style="--dias:<?= count($calendario) ?>">
        <?php foreach ($calendario as $d):
          $alto = $maxPost > 0 ? round($d['total'] * 100 / $maxPost) : 0;
          $titulo = $d['total'] === 0
            ? fecha_larga($d['fecha']) . ' · sin postulaciones'
            : fecha_larga($d['fecha']) . ' · ' . $d['total'] . ($d['total'] === 1 ? ' postulación' : ' postulaciones')
              . ' (' . implode(', ', array_map(
                  fn($k, $v) => $v . ' ' . programa_info($k)['nombre'], array_keys($d['programas']), $d['programas'])) . ')'
              . ' · acumulado ' . $d['acumulado'];
        ?>
          <div class="cal-dia<?= $d['hoy'] ? ' es-hoy' : '' ?><?= $d['futuro'] ? ' es-futuro' : '' ?>" title="<?= e($titulo) ?>">
            <span class="cal-barra">
              <?php foreach (['Acelera', 'Raiz'] as $prog):
                if (empty($d['programas'][$prog])) continue;
                $h = round($d['programas'][$prog] * 100 / max($maxPost, 1)); ?>
                <span class="cal-seg cal-<?= e(strtolower($prog)) ?>" style="height:<?= $h ?>%"></span>
              <?php endforeach; ?>
              <?php if ($d['total'] > 0): ?><span class="cal-n"><?= $d['total'] ?></span><?php endif; ?>
            </span>
            <span class="cal-fecha"><?= e($d['dt']->format('d/m')) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="cal-leyenda">
        <span><i class="cal-acelera"></i> <?= e(PROGRAMAS['Acelera']['nombre']) ?></span>
        <span><i class="cal-raiz"></i> <?= e(PROGRAMAS['Raiz']['nombre']) ?></span>
        <span class="cal-hoy-ref">La franja marcada es hoy</span>
      </div>
    <?php endif; ?>
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
    <?php
    // Lista y no tabla: una tabla de cuatro columnas en un celular sólo muestra
    // la primera y manda los números al scroll horizontal, que es justo lo que
    // se viene a mirar.
    $maxPagina = 0;
    foreach ($porPagina as $p) {
        $maxPagina = max($maxPagina, (int) $p['vistas']);
    }
    ?>
    <div class="panel">
      <h2 class="panel-title">Qué se mira</h2>
      <ul class="paginas">
        <?php foreach ($porPagina as $p): ?>
          <li>
            <span class="pg-ruta"><?= e($p['ruta']) ?></span>
            <span class="pg-barra"><span style="width:<?= $maxPagina ? round($p['vistas'] * 100 / $maxPagina) : 0 ?>%"></span></span>
            <span class="pg-v"><?= (int) $p['vistas'] ?></span>
            <span class="pg-detalle">
              <?= (int) $p['unicos'] ?> personas ·
              <?= $p['seg'] ? round($p['seg']) . 's' : 'sin tiempo' ?> ·
              scroll <?= $p['prof'] ? round($p['prof']) . '%' : '—' ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="hint">
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
