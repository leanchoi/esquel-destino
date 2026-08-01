<?php
/**
 * Analítica del sitio público. Sólo para el administrador, y sólo de lectura.
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
require_once __DIR__ . '/../includes/graficos.php';

$u = requiere_rol('admin');
$pdo = db();

$offset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime('now'));
$LOCAL = sprintf('%+d seconds', $offset);   // modificador de SQLite: '-10800 seconds'
$hoy = date('Y-m-d');

// ------------------------------------------------------------------ el rango
//
// El rango se resuelve siempre a dos fechas concretas y de ahí en adelante todo
// consulta por ese par. Antes se filtraba con "los últimos N días" contra el
// reloj, y como el sitio tiene pocos días de vida, 7, 30 y 90 devolvían lo
// mismo: parecía que los botones no hacían nada. Ahora el período que se está
// mirando está escrito arriba, con sus fechas.
$PRESETS = [
    'hoy' => ['label' => 'Hoy',      'dias' => 0],
    '7'   => ['label' => '7 días',   'dias' => 6],
    '30'  => ['label' => '30 días',  'dias' => 29],
    '90'  => ['label' => '90 días',  'dias' => 89],
    'todo' => ['label' => 'Todo',    'dias' => null],
];

$primerDato = $pdo->prepare("SELECT date(MIN(creada_at), ?) FROM visitas");
$primerDato->execute([$LOCAL]);
$primerDia = $primerDato->fetchColumn() ?: $hoy;

$rango = (string) ($_GET['r'] ?? '30');
$desdeF = trim((string) ($_GET['desde'] ?? ''));
$hastaF = trim((string) ($_GET['hasta'] ?? ''));

/** ¿Es una fecha Y-m-d real? Nada de confiar en el input date del navegador. */
function fecha_valida(string $f): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return $d !== false && $d->format('Y-m-d') === $f;
}

if ($rango === 'custom' && fecha_valida($desdeF) && fecha_valida($hastaF)) {
    $desde = min($desdeF, $hastaF);
    $hasta = max($desdeF, $hastaF);
} else {
    if (!array_key_exists($rango, $PRESETS)) {
        $rango = '30';
    }
    $hasta = $hoy;
    $dias = $PRESETS[$rango]['dias'];
    $desde = $dias === null ? $primerDia : date('Y-m-d', strtotime("-{$dias} days"));
    $desdeF = $desde;
    $hastaF = $hasta;
}

$diasRango = max(1, (int) ((strtotime($hasta) - strtotime($desde)) / 86400) + 1);
$P = [$LOCAL, $desde, $hasta];      // los tres parámetros que repite cada consulta
$FILTRO = "date(creada_at, ?) BETWEEN ? AND ?";

/**
 * Consulta del período, con el huso y las fechas ya puestos.
 *
 * Cuenta los marcadores antes de ejecutar. Pasarle menos valores que ? no da
 * error en SQLite: los que sobran quedan en NULL, date(x, NULL) devuelve NULL,
 * el WHERE nunca se cumple y la consulta devuelve cero filas como si de verdad
 * no hubiera datos. Eso ya vació media página de analítica sin una sola línea
 * en el log; que reviente fuerte es preferible a que mienta bajito.
 */
function q(PDO $pdo, string $sql, array $p, array $extra = []): array
{
    $valores = array_merge($p, $extra);
    $marcadores = substr_count(preg_replace("/'[^']*'/", '', $sql), '?');
    if ($marcadores !== count($valores)) {
        throw new RuntimeException(
            "La consulta tiene $marcadores marcadores y recibió " . count($valores) . ' valores.'
        );
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    return $stmt->fetchAll();
}

$totales = $pdo->prepare(
    "SELECT COUNT(*) vistas, COUNT(DISTINCT visitante) unicos,
            AVG(NULLIF(segundos,0)) seg, AVG(NULLIF(profundidad,0)) prof
       FROM visitas WHERE $FILTRO"
);
$totales->execute($P);
$tot = $totales->fetch();

// Ojo con la cuenta de marcadores: el date() del SELECT también lleva uno, así
// que van cuatro y no los tres de $P. Con tres, PDO devolvía cero filas sin
// avisar y la página entera se iba por el "no hay visitas en este período"
// mientras las tarjetas de arriba mostraban mil quinientas.
$filasDia = q($pdo, "SELECT date(creada_at, ?) dia, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos
                       FROM visitas WHERE $FILTRO GROUP BY dia ORDER BY dia",
              [$LOCAL, $LOCAL, $desde, $hasta]);
$porDia = [];
foreach ($filasDia as $d) {
    $porDia[$d['dia']] = ['vistas' => (int) $d['vistas'], 'unicos' => (int) $d['unicos']];
}

$porPagina = q($pdo, "SELECT ruta, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos,
                             AVG(NULLIF(segundos,0)) seg, AVG(NULLIF(profundidad,0)) prof
                        FROM visitas WHERE $FILTRO GROUP BY ruta ORDER BY vistas DESC LIMIT 20", $P);

$porOrigen = q($pdo, "SELECT CASE WHEN origen = '' THEN 'Directo o guardado' ELSE origen END fuente, COUNT(*) vistas
                        FROM visitas WHERE $FILTRO GROUP BY fuente ORDER BY vistas DESC LIMIT 12", $P);

$porDispositivo = q($pdo, "SELECT dispositivo, COUNT(*) vistas FROM visitas
                            WHERE $FILTRO GROUP BY dispositivo ORDER BY vistas DESC", $P);

$porPais = q($pdo, "SELECT pais, COUNT(*) vistas FROM visitas
                     WHERE $FILTRO AND pais IS NOT NULL GROUP BY pais ORDER BY vistas DESC LIMIT 10", $P);

// Mapa de calor día de la semana × hora, sobre el período elegido.
$celdas = [];
$topeHeat = 0;
foreach (q($pdo, "SELECT CAST(strftime('%w', creada_at, ?) AS INTEGER) dsem,
                         CAST(strftime('%H', creada_at, ?) AS INTEGER) hora, COUNT(*) n
                    FROM visitas WHERE date(creada_at, ?) BETWEEN ? AND ?
                   GROUP BY dsem, hora", [$LOCAL, $LOCAL, $LOCAL, $desde, $hasta]) as $c) {
    $celdas[(int) $c['dsem']][(int) $c['hora']] = (int) $c['n'];
    $topeHeat = max($topeHeat, (int) $c['n']);
}

// -------------------------------------------------------------- por hora
// Este panel sigue al rango como todos los demás. Con "Hoy" es la curva del
// día en curso y se corta en la hora actual; con cualquier otro período es el
// total por hora de todos esos días juntos.
$filasHora = $pdo->prepare(
    "SELECT CAST(strftime('%H', creada_at, ?) AS INTEGER) hora, COUNT(*) vistas, COUNT(DISTINCT visitante) unicos
       FROM visitas WHERE date(creada_at, ?) BETWEEN ? AND ? GROUP BY hora ORDER BY hora"
);
$filasHora->execute([$LOCAL, $LOCAL, $desde, $hasta]);
$horas = array_fill(0, 24, ['vistas' => 0, 'unicos' => 0]);
foreach ($filasHora->fetchAll() as $f) {
    $horas[(int) $f['hora']] = ['vistas' => (int) $f['vistas'], 'unicos' => (int) $f['unicos']];
}
$esHoy = $rango === 'hoy' || ($desde === $hasta && $desde === $hoy);
$horaActual = (int) date('G');
$corteHora = $esHoy ? $horaActual : 23;

$totHoy = $pdo->prepare("SELECT COUNT(*) vistas, COUNT(DISTINCT visitante) unicos FROM visitas WHERE date(creada_at, ?) = ?");
$totHoy->execute([$LOCAL, $hoy]);
$hoyTot = $totHoy->fetch();

// Mismo corte de hora, pero ayer: sirve para saber si el día viene bien o mal
// sin esperar a que termine.
$ayerHasta = $pdo->prepare(
    "SELECT COUNT(DISTINCT visitante) FROM visitas
      WHERE date(creada_at, ?) = date(?, '-1 day') AND CAST(strftime('%H', creada_at, ?) AS INTEGER) <= ?"
);
$ayerHasta->execute([$LOCAL, $hoy, $LOCAL, $horaActual]);
$ayerAEstaHora = (int) $ayerHasta->fetchColumn();

$postHoy = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE date(submitted_at, ?) = ?");
$postHoy->execute([$LOCAL, $hoy]);
$postulacionesHoy = (int) $postHoy->fetchColumn();

// ------------------------------------------------------ embudo de conversión
//
// Cinco etapas, del que entra a la home al que manda la postulación.
//
// Una advertencia que va también en pantalla: las cuatro primeras cuentan
// personas y la última cuenta postulaciones, y no se pueden enlazar. Al
// visitante lo identifica un código que se recalcula todos los días —eso es lo
// que hace que la medición no siga a nadie—, así que quien mira el lunes y se
// postula el miércoles cuenta como dos personas distintas. El embudo compara
// volúmenes por etapa dentro del período, no sigue a una misma persona.
$emb = $pdo->prepare(
    "SELECT
        COUNT(DISTINCT CASE WHEN ruta LIKE '%index.php' OR ruta = '/' THEN visitante END) home,
        COUNT(DISTINCT CASE WHEN ruta LIKE '%inscribirse%' THEN visitante END) form,
        COUNT(DISTINCT CASE WHEN ruta LIKE '%inscribirse%' AND paso_form >= 2 THEN visitante END) arranca,
        COUNT(DISTINCT CASE WHEN ruta LIKE '%inscribirse%' AND paso_form >= 6 THEN visitante END) ultimo
       FROM visitas WHERE $FILTRO"
);
$emb->execute($P);
$E = $emb->fetch();

$enviadas = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE date(submitted_at, ?) BETWEEN ? AND ?");
$enviadas->execute($P);
$totalEnviadas = (int) $enviadas->fetchColumn();

// El mismo embudo día por día, para ver la evolución y no sólo el total.
$embDia = q($pdo,
    "SELECT date(creada_at, ?) dia,
            COUNT(DISTINCT CASE WHEN ruta LIKE '%index.php' OR ruta = '/' THEN visitante END) home,
            COUNT(DISTINCT CASE WHEN ruta LIKE '%inscribirse%' THEN visitante END) form
       FROM visitas WHERE $FILTRO GROUP BY dia",
    [$LOCAL, $LOCAL, $desde, $hasta]);
$porDiaEmbudo = [];
foreach ($embDia as $r) {
    $porDiaEmbudo[$r['dia']] = ['home' => (int) $r['home'], 'form' => (int) $r['form']];
}

$postDia = q($pdo, "SELECT date(submitted_at, ?) dia, COUNT(*) n FROM applications
                     WHERE date(submitted_at, ?) BETWEEN ? AND ? GROUP BY dia",
             [$LOCAL, $LOCAL, $desde, $hasta]);
$enviadasPorDia = [];
foreach ($postDia as $r) {
    $enviadasPorDia[$r['dia']] = (int) $r['n'];
}

$ultima = $pdo->query("SELECT MAX(creada_at) FROM visitas")->fetchColumn();

// ------------------------------------------------------- postulaciones por día
$filasPost = $pdo->prepare("SELECT date(submitted_at, ?) dia, program, COUNT(*) n FROM applications GROUP BY dia, program ORDER BY dia");
$filasPost->execute([$LOCAL]);
$postPorDia = [];
foreach ($filasPost->fetchAll() as $f) {
    $postPorDia[$f['dia']][$f['program']] = (int) $f['n'];
}

// La ventana es toda la convocatoria: un día sin postulaciones también es un
// dato, y sólo se ve si el día está dibujado aunque esté vacío.
// Sigue al rango, como todo lo demás. La única excepción es "Todo", que abre
// la ventana a la convocatoria entera —incluidos los días que faltan— porque
// ahí lo que se quiere ver es la campaña completa y cuánta pista queda.
if ($rango === 'todo') {
    $inicio = new DateTimeImmutable(date('Y-m-d', strtotime(FECHA_APERTURA)));
    $finCal = new DateTimeImmutable(date('Y-m-d', strtotime(FECHA_CIERRE)));
    if ($postPorDia) {
        $primera = new DateTimeImmutable(min(array_keys($postPorDia)));
        if ($primera < $inicio) {
            $inicio = $primera;
        }
    }
    $hoyDT = new DateTimeImmutable($hoy);
    $hasta_cal = $hoyDT > $finCal ? $hoyDT : $finCal;
} else {
    $inicio = new DateTimeImmutable($desde);
    $hasta_cal = new DateTimeImmutable($hasta);
}

// El acumulado arranca con lo que ya había entrado antes del período: si no,
// recortar la ventana haría parecer que la convocatoria empezó de cero.
$calendario = [];
$acumulado = 0;
foreach ($postPorDia as $f => $prog) {
    if ($f < $inicio->format('Y-m-d')) {
        $acumulado += array_sum($prog);
    }
}
$previos = $acumulado;
$maxPost = 0;
for ($d = $inicio; $d <= $hasta_cal; $d = $d->modify('+1 day')) {
    $clave = $d->format('Y-m-d');
    $delDia = $postPorDia[$clave] ?? [];
    $n = array_sum($delDia);
    $acumulado += $n;
    $maxPost = max($maxPost, $n);
    $calendario[] = [
        'fecha' => $clave, 'dt' => $d, 'total' => $n, 'programas' => $delDia,
        'acumulado' => $acumulado, 'futuro' => $clave > $hoy, 'hoy' => $clave === $hoy,
    ];
}
$totalPostulaciones = array_sum(array_map('array_sum', $postPorDia));
$enElPeriodo = $acumulado - $previos;

// ------------------------------------------------------- tablero de luces
//
// Los umbrales están en UMBRALES (config.php) y cada tarjeta dice contra qué
// compara: un semáforo que no muestra su vara es un semáforo al que hay que
// creerle.

/** Verde, ámbar o rojo según dos cortes. Más alto es mejor. */
function luz(float $valor, float $ok, float $alerta): string
{
    return $valor >= $ok ? 'ok' : ($valor >= $alerta ? 'alerta' : 'mal');
}

$U = UMBRALES;

// 1. Conversión de punta a punta.
$convPct = (int) $E['home'] > 0 ? $totalEnviadas * 100 / (int) $E['home'] : 0.0;
$luzConv = (int) $E['home'] === 0 ? 'neutro' : luz($convPct, $U['conversion']['ok'], $U['conversion']['alerta']);

// 2. De los que abren el formulario, cuántos lo terminan.
$termPct = (int) $E['form'] > 0 ? $totalEnviadas * 100 / (int) $E['form'] : 0.0;
$luzTerm = (int) $E['form'] === 0 ? 'neutro' : luz($termPct, $U['terminacion']['ok'], $U['terminacion']['alerta']);

// 3. Ritmo: ¿el promedio diario alcanza para llegar al objetivo antes del cierre?
$cupoMax = 0;
foreach (PROGRAMAS as $prog) {
    if (preg_match_all('/\d+/', $prog['cupo'], $m)) {
        $cupoMax += (int) max($m[0]);
    }
}
$objetivo = (int) ceil($cupoMax * $U['candidatos_por_cupo']);
$totalHoy = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();
$diasCorridos = max(1, (int) ceil((time() - strtotime(FECHA_APERTURA)) / 86400));
$porDiaPost = $totalHoy / $diasCorridos;
$restan = dias_para_cierre();
$proyeccion = (int) round($totalHoy + $porDiaPost * $restan);
$ritmoPct = $objetivo > 0 ? $proyeccion * 100 / $objetivo : 0.0;
$luzRitmo = $totalHoy === 0 ? 'neutro' : luz($ritmoPct, $U['ritmo']['ok'], $U['ritmo']['alerta']);

// 4. Cada línea contra su propio cupo: no alcanza con el total si una queda vacía.
$porLinea = [];
foreach ($pdo->query('SELECT program, COUNT(*) n FROM applications GROUP BY program') as $r) {
    $porLinea[$r['program']] = (int) $r['n'];
}
$luces_linea = [];
foreach (PROGRAMAS as $slug => $prog) {
    preg_match_all('/\d+/', $prog['cupo'], $m);
    $min = $m[0] ? (int) min($m[0]) : 0;
    $max = $m[0] ? (int) max($m[0]) : 0;
    $n = $porLinea[$slug] ?? 0;
    $necesita = (int) ceil($max * $U['candidatos_por_cupo']);
    $luces_linea[] = [
        'nombre' => $prog['nombre'],
        'n' => $n,
        'estado' => $n === 0 ? 'mal' : ($n >= $necesita ? 'ok' : ($n >= $min ? 'alerta' : 'mal')),
        'lectura' => $n === 0
            ? 'Todavía no se postuló nadie de esta línea'
            : ($n >= $necesita
                ? 'Alcanza para elegir entre varios'
                : ($n >= $min ? 'Da para cubrir el cupo, pero sin margen para elegir'
                              : 'No alcanza ni para el cupo mínimo de ' . $min)),
        'referencia' => 'Cupo: ' . $prog['cupo'] . ' · para elegir de verdad harían falta ' . $necesita,
    ];
}

// 5. Tráfico contra el período anterior del mismo largo.
$antesDesde = date('Y-m-d', strtotime($desde . ' -' . $diasRango . ' days'));
$antesHasta = date('Y-m-d', strtotime($desde . ' -1 day'));
$prev = $pdo->prepare("SELECT COUNT(DISTINCT visitante) FROM visitas WHERE date(creada_at, ?) BETWEEN ? AND ?");
$prev->execute([$LOCAL, $antesDesde, $antesHasta]);
$unicosAntes = (int) $prev->fetchColumn();
$varTrafico = $unicosAntes > 0 ? ((int) $tot['unicos'] - $unicosAntes) * 100 / $unicosAntes : 0.0;
$luzTrafico = $unicosAntes === 0 ? 'neutro' : luz($varTrafico, $U['trafico']['ok'], $U['trafico']['alerta']);

$pageTitle = 'Analítica';
$nav = 'analitica';
require __DIR__ . '/_header.php';

function pct(int $parte, int $total): float
{
    return $total > 0 ? round($parte * 100 / $total, 1) : 0;
}

/** La misma URL, cambiando sólo el rango. */
function url_rango(string $r): string
{
    return '?' . http_build_query(['r' => $r]);
}

/**
 * El distintivo que dice qué período cubre un panel.
 *
 * Es la respuesta a una queja concreta: "cambio de 7 a 30 días y los gráficos
 * no se ajustan". Dos de ellos efectivamente no se ajustan —el del día en curso
 * y el de la convocatoria entera— porque no tendría sentido, pero eso no estaba
 * escrito en ningún lado, así que parecían rotos. Ahora cada panel dice de qué
 * período está hablando.
 */
function sello_periodo(string $texto, bool $fijo = false): string
{
    return '<span class="panel-periodo' . ($fijo ? ' es-fijo' : '') . '">' . e($texto) . '</span>';
}

/** "1.234" con punto de miles, como se escribe acá. */
function num(int $n): string
{
    return number_format($n, 0, ',', '.');
}
?>

<div class="admin-topbar admin-topbar-analitica">
  <div>
    <h1>Analítica del sitio</h1>
    <p class="periodo-activo">
      <?php if ($rango === 'hoy'): ?>
        Hoy, <?= e(fecha_larga($hoy)) ?>
      <?php else: ?>
        Del <strong><?= e(fecha_larga($desde)) ?></strong> al <strong><?= e(fecha_larga($hasta)) ?></strong>
        · <?= $diasRango ?> <?= $diasRango === 1 ? 'día' : 'días' ?>
      <?php endif; ?>
      <?php if ($desde < $primerDia): ?>
        <span class="periodo-aviso">con datos desde el <?= e(fecha_larga($primerDia)) ?>, que es cuando empezó a medirse</span>
      <?php endif; ?>
    </p>
  </div>
  <div class="rango">
    <?php foreach ($PRESETS as $k => $p): $k = (string) $k; ?>
      <?php // (string) no es cosmético: PHP convierte las claves '7', '30' y '90'
            // en enteros, así que $rango === $k comparaba '30' con 30 y daba
            // falso. Ningún preset numérico se marcaba nunca como activo. ?>
      <a href="<?= e(url_rango($k)) ?>" class="<?= $rango === $k ? 'is-active' : '' ?>"><?= e($p['label']) ?></a>
    <?php endforeach; ?>
    <button type="button" class="rango-custom <?= $rango === 'custom' ? 'is-active' : '' ?>" id="btnCustom"
            aria-expanded="<?= $rango === 'custom' ? 'true' : 'false' ?>" aria-controls="formCustom">A medida</button>
  </div>
</div>

<form method="get" class="rango-form" id="formCustom" <?= $rango === 'custom' ? '' : 'hidden' ?>>
  <input type="hidden" name="r" value="custom">
  <label>Desde <input type="date" name="desde" value="<?= e($desdeF) ?>" min="<?= e($primerDia) ?>" max="<?= e($hoy) ?>" required></label>
  <label>Hasta <input type="date" name="hasta" value="<?= e($hastaF) ?>" min="<?= e($primerDia) ?>" max="<?= e($hoy) ?>" required></label>
  <button type="submit" class="btn btn-primary btn-sm">Ver ese período</button>
</form>

<div class="admin-content">

  <!-- ---------------- cómo venimos ---------------- -->
  <h2 class="panel-title bloque-t">Cómo venimos <?= sello_periodo('la convocatoria', true) ?></h2>
  <div class="luces">
    <?= semaforo('Ritmo de postulaciones',
        $totalHoy . ' <span class="u">de ' . $objetivo . '</span>',
        $luzRitmo,
        $totalHoy === 0 ? 'Todavía no entró ninguna.'
          : ($restan === 0 ? 'La convocatoria ya cerró.'
            : 'Al ritmo de hoy (' . number_format($porDiaPost, 1, ',', '') . ' por día) se llegaría a ' . $proyeccion
              . ' para el cierre.'),
        'Objetivo: ' . $objetivo . ' postulaciones, ' . UMBRALES['candidatos_por_cupo'] . ' por cada uno de los ' . $cupoMax . ' cupos') ?>

    <?= semaforo('Conversión',
        number_format($convPct, 2, ',', '') . '<span class="u">%</span>',
        $luzConv,
        (int) $E['home'] === 0 ? 'Sin visitas a la portada en el período.'
          : 'De ' . num((int) $E['home']) . ' personas que entraron, se postularon ' . $totalEnviadas . '.',
        'Verde desde ' . UMBRALES['conversion']['ok'] . '%, ámbar desde ' . UMBRALES['conversion']['alerta'] . '%') ?>

    <?= semaforo('Terminan el formulario',
        number_format($termPct, 1, ',', '') . '<span class="u">%</span>',
        $luzTerm,
        (int) $E['form'] === 0 ? 'Nadie abrió el formulario en el período.'
          : 'Lo abrieron ' . num((int) $E['form']) . ' y lo terminaron ' . $totalEnviadas . '.',
        'Verde desde ' . UMBRALES['terminacion']['ok'] . '%, ámbar desde ' . UMBRALES['terminacion']['alerta'] . '%') ?>

    <?= semaforo('Tráfico',
        ($varTrafico > 0 ? '+' : '') . number_format($varTrafico, 0, ',', '') . '<span class="u">%</span>',
        $luzTrafico,
        $unicosAntes === 0 ? 'No hay período anterior con el que comparar.'
          : num((int) $tot['unicos']) . ' personas contra ' . num($unicosAntes) . ' del período anterior.',
        'Compara con los ' . $diasRango . ' días previos al período elegido') ?>

    <?php foreach ($luces_linea as $l): ?>
      <?= semaforo($l['nombre'], (string) $l['n'] . ' <span class="u">postulaciones</span>',
          $l['estado'], $l['lectura'], $l['referencia']) ?>
    <?php endforeach; ?>
  </div>
  <p class="hint luces-pie">
    Los umbrales están en <code>UMBRALES</code>, dentro de <code>includes/config.php</code>: son
    valores de referencia para discutir, no verdades. Cada tarjeta dice contra qué compara.
  </p>

  <!-- ---------------- el día de hoy ---------------- -->
  <div class="panel">
    <div class="hoy-head">
      <div>
        <h2 class="panel-title">
          <?= $esHoy ? 'Hoy, hora por hora' : 'A qué hora entran' ?>
          <?= sello_periodo($esHoy ? 'hoy' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?>
        </h2>
        <p class="hint" style="margin:0">
          <?= $esHoy
              ? e(fecha_larga($hoy)) . ' · hora de Esquel'
              : 'Las ' . $diasRango . ' jornadas del período sumadas hora por hora · hora de Esquel' ?>
        </p>
      </div>
      <div class="hoy-cifras">
        <div class="hc">
          <span class="k">Personas</span>
          <span class="v"><?= $esHoy ? (int) $hoyTot['unicos'] : (int) $tot['unicos'] ?></span>
          <?php if ($esHoy): ?>
            <?php $delta = (int) $hoyTot['unicos'] - $ayerAEstaHora; ?>
            <?php if ($ayerAEstaHora > 0): ?>
              <span class="d <?= $delta > 0 ? 'sube' : ($delta < 0 ? 'baja' : '') ?>">
                <?= $delta > 0 ? '+' : '' ?><?= $delta ?> vs. ayer a esta hora
              </span>
            <?php else: ?>
              <span class="d">sin comparación de ayer</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="d"><?= e(number_format($tot['unicos'] / $diasRango, 1, ',', '.')) ?> por día</span>
          <?php endif; ?>
        </div>
        <div class="hc"><span class="k">Vistas</span>
          <span class="v"><?= $esHoy ? (int) $hoyTot['vistas'] : num((int) $tot['vistas']) ?></span></div>
        <div class="hc"><span class="k">Postulaciones</span>
          <span class="v"><?= $esHoy ? $postulacionesHoy : $totalEnviadas ?></span></div>
      </div>
    </div>
    <?php if (array_sum(array_column($horas, 'vistas')) === 0): ?>
      <p class="hint">
        <?= $esHoy ? 'Todavía no entró nadie hoy.' : 'No hay visitas en este período.' ?>
        La curva aparece con la primera visita.
        <?php if ($ultima): ?>
          La última que se registró fue el <?= e(fecha_corta(date('Y-m-d H:i:s', strtotime($ultima) + $offset), true)) ?>.
        <?php else: ?>
          <strong>No hay ninguna visita registrada todavía</strong>, ni hoy ni antes: si el sitio ya está publicado,
          eso quiere decir que la medición no está llegando a la base.
        <?php endif; ?>
      </p>
    <?php else:
      $vistasHora = array_column($horas, 'vistas');
      $hastaAhora = array_slice($vistasHora, 0, $corteHora + 1);
      $promHora = $hastaAhora ? array_sum($hastaAhora) / count($hastaAhora) : 0;
      $picoHora = max($vistasHora);
      $totalHoras = array_sum($vistasHora);
      $puntosHora = [];
      for ($i = 0; $i < 24; $i++) {
          $v = $horas[$i]['vistas'];
          $lineas = [$v === 0 ? 'Sin visitas' : $v . ($v === 1 ? ' visita' : ' visitas') . ' de '
                     . $horas[$i]['unicos'] . ($horas[$i]['unicos'] === 1 ? ' persona' : ' personas')];
          if ($i > $corteHora) {
              $lineas = ['Todavía no pasó'];
          } elseif ($v > 0) {
              if ($v === $picoHora) {
                  $lineas[] = $esHoy ? 'Es la hora más movida del día' : 'Es la hora más movida del período';
              } elseif ($promHora > 0) {
                  $dif = round(($v - $promHora) / $promHora * 100);
                  $lineas[] = $dif >= 10 ? '+' . $dif . '% sobre el promedio por hora'
                            : ($dif <= -10 ? $dif . '% bajo el promedio por hora' : 'En el promedio por hora');
              }
              if ($totalHoras > 0 && !$esHoy) {
                  $lineas[] = round($v * 100 / $totalHoras, 1) . '% de las visitas del período';
              }
              if ($esHoy && $i === $horaActual) {
                  $lineas[] = 'Esta franja todavía se está llenando';
              }
          }
          $puntosHora[] = [
              'v'  => $v,
              'x'  => $i % 3 === 0 ? str_pad((string) $i, 2, '0', STR_PAD_LEFT) : '',
              'tt' => array_merge([str_pad((string) $i, 2, '0', STR_PAD_LEFT) . ' a '
                       . str_pad((string) (($i + 1) % 24), 2, '0', STR_PAD_LEFT) . ' h'], $lineas),
          ];
      }
      echo svg_curva($puntosHora, $corteHora);
    ?>
      <?php if ($esHoy): ?>
        <p class="hint">
          La línea vertical es la hora en curso: esa franja todavía se está llenando, y de ahí
          para adelante no se dibuja nada porque todavía no pasó.
        </p>
      <?php else: ?>
        <p class="hint">
          Son las <?= $diasRango ?> jornadas del período apiladas: cada punto es el total de
          visitas que entraron a esa hora, sumando todos los días.
        </p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="stats" style="margin-top:22px">
    <div class="stat"><span class="k">Visitas</span><span class="v"><?= (int) $tot['vistas'] ?></span></div>
    <div class="stat"><span class="k">Personas distintas</span><span class="v"><?= (int) $tot['unicos'] ?></span></div>
    <div class="stat"><span class="k">Tiempo promedio</span><span class="v"><?= $tot['seg'] ? round($tot['seg']) . 's' : '—' ?></span></div>
    <div class="stat"><span class="k">Scroll promedio</span><span class="v"><?= $tot['prof'] ? round($tot['prof']) . '%' : '—' ?></span></div>
    <div class="stat ok"><span class="k">Postulaciones</span><span class="v"><?= $totalEnviadas ?></span></div>
  </div>

  <?php if (!$porDia): ?>
    <div class="empty-state">
      No hay visitas registradas entre el <?= e(fecha_larga($desde)) ?> y el <?= e(fecha_larga($hasta)) ?>.
      <?php if ($ultima): ?>
        La última visita registrada es del <?= e(fecha_corta(date('Y-m-d H:i:s', strtotime($ultima) + $offset))) ?>:
        probá con un período que la incluya.
      <?php endif; ?>
    </div>
  <?php else: ?>

  <!-- ---------------- visitas por día ---------------- -->
  <?php
  // Se dibujan todos los días del período, también los vacíos: un día en cero
  // es un dato, y salteárselo dibuja una curva que miente sobre el ritmo.
  $dias_es = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  $serieDia = [];
  for ($i = 0; $i < $diasRango; $i++) {
      $f = date('Y-m-d', strtotime($desde . " +{$i} days"));
      $serieDia[$i] = ['f' => $f] + ($porDia[$f] ?? ['vistas' => 0, 'unicos' => 0]);
  }
  $promDia = $diasRango > 0 ? array_sum(array_column($serieDia, 'vistas')) / $diasRango : 0;
  $picoDia = max(array_column($serieDia, 'vistas'));

  $puntosDia = [];
  $paso = max(1, (int) ceil($diasRango / 8));
  foreach ($serieDia as $i => $d) {
      // El último día siempre se rotula, pero sólo si no queda encima del
      // rótulo anterior: si no, "26/07" y "27/07" salen uno sobre el otro.
      $ultimo = $i === $diasRango - 1;
      $pegado = $ultimo && ($i % $paso) !== 0 && ($i - intdiv($i, $paso) * $paso) < max(2, (int) ($paso / 2));

      $lineas = [$d['vistas'] === 0 ? 'Sin visitas'
                 : $d['vistas'] . ($d['vistas'] === 1 ? ' visita' : ' visitas') . ' de '
                   . $d['unicos'] . ($d['unicos'] === 1 ? ' persona' : ' personas')];
      if ($d['vistas'] > 0) {
          if ($d['vistas'] === $picoDia) {
              $lineas[] = 'Es el día más movido del período';
          } elseif ($promDia > 0) {
              $dif = round(($d['vistas'] - $promDia) / $promDia * 100);
              $lineas[] = $dif >= 10 ? '+' . $dif . '% sobre el promedio (' . round($promDia) . ' por día)'
                        : ($dif <= -10 ? $dif . '% bajo el promedio (' . round($promDia) . ' por día)'
                                       : 'En el promedio del período');
          }
          $ayer = $serieDia[$i - 1]['vistas'] ?? null;
          if ($ayer) {
              $v = round(($d['vistas'] - $ayer) / $ayer * 100);
              if (abs($v) >= 10) {
                  $lineas[] = ($v > 0 ? '+' : '') . $v . '% respecto del día anterior';
              }
          }
      }
      if (!empty($postPorDia[$d['f']])) {
          $n = array_sum($postPorDia[$d['f']]);
          $lineas[] = 'Entraron ' . $n . ($n === 1 ? ' postulación' : ' postulaciones') . ' este día';
      }
      $puntosDia[] = [
          'v'  => $d['vistas'],
          'x'  => (($i % $paso === 0) || ($ultimo && !$pegado)) ? date('d/m', strtotime($d['f'])) : '',
          'tt' => array_merge([$dias_es[(int) date('w', strtotime($d['f']))] . ' ' . fecha_larga($d['f'])], $lineas),
      ];
  }
  ?>
  <div class="panel">
    <h2 class="panel-title">Visitas por día <?= sello_periodo($rango === 'hoy' ? 'hoy' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?></h2>
    <?php if ($diasRango < 2): ?>
      <p class="hint">Un solo día no dibuja una curva. Elegí un período más largo para ver la evolución.</p>
    <?php else: ?>
      <?= svg_curva($puntosDia) ?>
    <?php endif; ?>
  </div>

  <!-- ---------------- mapa de calor ---------------- -->
  <div class="panel">
    <h2 class="panel-title">A qué hora entra la gente <?= sello_periodo($rango === 'hoy' ? 'hoy' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?></h2>
    <p class="hint" style="margin-top:-8px">
      Cada cuadrito es un día de la semana y una hora, y cuanto más oscuro más visitas.
      Es el gráfico para decidir a qué hora conviene publicar en redes.
    </p>
    <?= heatmap_semana($celdas, $topeHeat) ?>
  </div>

  <!-- ---------------- cuándo se postulan ---------------- -->
  <div class="panel">
    <div class="hoy-head">
      <div>
        <h2 class="panel-title">Cuándo se postulan
          <?= sello_periodo($rango === 'todo' ? 'toda la convocatoria' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?>
        </h2>
        <p class="hint" style="margin:0">
          Del <?= e(fecha_larga($calendario[0]['fecha'])) ?> al
          <?= e(fecha_larga($calendario[count($calendario) - 1]['fecha'])) ?>, día por día.
          <?= $rango === 'todo'
              ? 'Con «Todo» se abre a la convocatoria completa, incluidos los días que faltan.'
              : 'Los días vacíos también están dibujados.' ?>
        </p>
      </div>
      <div class="hoy-cifras">
        <div class="hc"><span class="k"><?= $rango === 'todo' ? 'Total' : 'En el período' ?></span>
          <span class="v"><?= $rango === 'todo' ? $totalPostulaciones : $enElPeriodo ?></span>
          <?php if ($rango !== 'todo'): ?><span class="d"><?= $totalPostulaciones ?> en toda la convocatoria</span><?php endif; ?>
        </div>
        <div class="hc"><span class="k">Faltan</span><span class="v"><?= dias_para_cierre() ?><span class="u">días</span></span></div>
      </div>
    </div>

    <?php if ($totalPostulaciones === 0): ?>
      <p class="hint">Todavía no entró ninguna postulación. El gráfico se dibuja con la primera.</p>
    <?php elseif (!$calendario): ?>
      <p class="hint">El período elegido no tiene días para dibujar.</p>
    <?php else: ?>
      <div class="cal-scroll">
      <div class="calendario" style="--dias:<?= count($calendario) ?>">
        <?php
        $sinNada = 0;
        foreach ($calendario as $i => $d):
          $lineas = [];
          if ($d['futuro']) {
              $lineas[] = 'Todavía no pasó';
              $sinNada = 0;
          } elseif ($d['total'] === 0) {
              $sinNada++;
              $lineas[] = 'Sin postulaciones';
              if ($sinNada > 1) {
                  $lineas[] = $sinNada . ' días seguidos sin ninguna';
              }
          } else {
              $sinNada = 0;
              $lineas[] = $d['total'] . ($d['total'] === 1 ? ' postulación' : ' postulaciones') . ': '
                . implode(', ', array_map(fn($k, $v) => $v . ' ' . programa_info($k)['nombre'],
                                          array_keys($d['programas']), $d['programas']));
              if ($d['total'] === $maxPost && $maxPost > 1) {
                  $lineas[] = 'Es el día de más postulaciones';
              }
          }
          if (!$d['futuro']) {
              $lineas[] = 'Acumulado: ' . $d['acumulado'] . ' de ' . $totalPostulaciones;
          }
          $titulo = $d['hoy'] ? 'Hoy, ' . fecha_larga($d['fecha']) : fecha_larga($d['fecha']);
        ?>
          <div class="cal-dia<?= $d['hoy'] ? ' es-hoy' : '' ?><?= $d['futuro'] ? ' es-futuro' : '' ?>"<?= tt($titulo, $lineas) ?>>
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
      </div>
      <div class="cal-leyenda">
        <span><i class="cal-acelera"></i> <?= e(PROGRAMAS['Acelera']['nombre']) ?></span>
        <span><i class="cal-raiz"></i> <?= e(PROGRAMAS['Raiz']['nombre']) ?></span>
        <span class="cal-hoy-ref">La franja marcada es hoy</span>
      </div>
      <p class="hint">
        Raíz va con trama además de con color: medido, los dos colores del programa se
        distinguen apenas para quien tiene daltonismo rojo-verde, que es una de cada doce
        personas con ojos de varón. La trama se ve igual sin distinguir ningún color.
      </p>
    <?php endif; ?>
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
      <h2 class="panel-title">Qué se mira <?= sello_periodo($rango === 'hoy' ? 'hoy' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?></h2>
      <ul class="paginas">
        <?php foreach ($porPagina as $i => $p):
          $lineas = [(int) $p['vistas'] . ' vistas de ' . (int) $p['unicos'] . ' personas'];
          if ((int) $tot['vistas'] > 0) {
              $lineas[] = round($p['vistas'] * 100 / $tot['vistas'], 1) . '% de las visitas del período';
          }
          $lineas[] = $i === 0 ? 'Es la página más vista' : 'Puesto ' . ($i + 1) . ' entre las más vistas';
          if ($p['seg']) {
              $lineas[] = 'Se quedan ' . round($p['seg']) . ' segundos en promedio';
          }
          if ($p['prof']) {
              $lineas[] = $p['prof'] >= 70 ? 'La bajan casi entera (' . round($p['prof']) . '%)'
                        : ($p['prof'] <= 35 ? 'Sólo bajan el ' . round($p['prof']) . '%: se van arriba'
                                            : 'Bajan hasta el ' . round($p['prof']) . '% de la página');
          }
        ?>
          <li<?= tt($p['ruta'], $lineas) ?>>
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
      <h2 class="panel-title">De dónde llegan <?= sello_periodo($rango === 'hoy' ? 'hoy' : fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?></h2>
      <ul class="lista-barras">
        <?php foreach ($porOrigen as $i => $o):
          $pc = pct((int) $o['vistas'], (int) $tot['vistas']);
          $lineas = [(int) $o['vistas'] . ' visitas', $pc . '% de todo el período'];
          if ($i === 0) { $lineas[] = 'Es de donde llega más gente'; }
          if ($o['fuente'] === 'Directo o guardado') {
              $lineas[] = 'Escribieron la dirección o la tenían guardada';
          }
        ?>
          <li<?= tt($o['fuente'], $lineas) ?>>
            <span class="lb-n"><?= e($o['fuente']) ?></span>
            <span class="lb-barra"><span style="width:<?= pct((int) $o['vistas'], (int) $tot['vistas']) ?>%"></span></span>
            <span class="lb-v"><?= (int) $o['vistas'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <h2 class="panel-title" style="margin-top:26px">Con qué entran</h2>
      <ul class="lista-barras">
        <?php foreach ($porDispositivo as $d):
          $pc = pct((int) $d['vistas'], (int) $tot['vistas']);
          $lineas = [(int) $d['vistas'] . ' visitas', $pc . '% de todo el período'];
          if ($d['dispositivo'] === 'celular' && $pc >= 60) {
              $lineas[] = 'La mayoría entra desde el teléfono: conviene revisar ahí primero';
          }
        ?>
          <li<?= tt(ucfirst($d['dispositivo']), $lineas) ?>>
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

  <!-- ---------------- embudo de conversión ---------------- -->
  <?php
  $etapas = [
      ['clave' => 'home',    'nombre' => 'Entraron a la página',   'n' => (int) $E['home'],
       'nota' => 'personas distintas en la portada'],
      ['clave' => 'form',    'nombre' => 'Abrieron el formulario', 'n' => (int) $E['form'],
       'nota' => 'llegaron a inscribirse.php'],
      ['clave' => 'arranca', 'nombre' => 'Empezaron a completarlo','n' => (int) $E['arranca'],
       'nota' => 'pasaron del primer paso'],
      ['clave' => 'ultimo',  'nombre' => 'Llegaron al último paso','n' => (int) $E['ultimo'],
       'nota' => 'abrieron el paso 6'],
      ['clave' => 'enviada', 'nombre' => 'Se postularon',          'n' => $totalEnviadas,
       'nota' => 'postulaciones guardadas'],
  ];
  $arriba = max(1, $etapas[0]['n']);
  foreach ($etapas as $i => $et) {
      $l = [$et['n'] . ($et['n'] === 1 ? ' persona' : ' personas')];
      if ($i > 0) {
          $prev = $etapas[$i - 1]['n'];
          $l[] = $prev > 0 ? 'De los ' . $prev . ' del paso anterior, siguió el ' . round($et['n'] * 100 / $prev) . '%'
                           : 'No hubo nadie en el paso anterior';
          $l[] = 'Es el ' . round($et['n'] * 100 / $arriba, 1) . '% de los que entraron a la página';
      }
      if ($et['clave'] === 'enviada') {
          $l[] = 'Acá se cuentan postulaciones, no personas';
      }
      $etapas[$i]['tt'] = $l;
  }
  $conversion = $arriba > 0 ? round($totalEnviadas * 100 / $arriba, 2) : 0;
  ?>
  <div class="panel">
    <div class="hoy-head">
      <div>
        <h2 class="panel-title">Embudo de conversión <?= sello_periodo(fecha_corta($desde) . ' – ' . fecha_corta($hasta)) ?></h2>
        <p class="hint" style="margin:0">
          De quien entra a la página a quien manda la postulación. Sigue el período de arriba.
        </p>
      </div>
      <div class="hoy-cifras">
        <div class="hc">
          <span class="k">Conversión</span>
          <span class="v"><?= e(number_format($conversion, $conversion < 10 ? 2 : 1, ',', '.')) ?><span class="u">%</span></span>
          <span class="d">de la portada a la postulación</span>
        </div>
      </div>
    </div>

    <?= svg_embudo($etapas) ?>

    <?php
    // Evolución diaria de las tres etapas que importan. Todas cuentan personas,
    // así que van en un solo eje: dos escalas distintas en el mismo dibujo
    // inventan una relación que los datos no tienen.
    $sHome = $sForm = $sEnv = [];
    $serieEmbudo = [];
    $ejeX = $tips = [];
    $pasoX = max(1, (int) ceil($diasRango / 8));
    for ($i = 0; $i < $diasRango; $i++) {
        $f = date('Y-m-d', strtotime($desde . " +{$i} days"));
        $d = $porDiaEmbudo[$f] ?? ['home' => 0, 'form' => 0];
        $env = $enviadasPorDia[$f] ?? 0;
        $sHome[] = $d['home'];
        $sForm[] = $d['form'];
        $sEnv[]  = $env;
        $serieEmbudo[] = ['f' => $f, 'home' => $d['home'], 'form' => $d['form'], 'env' => $env];
        // El último día se rotula sólo si no queda encima del rótulo anterior.
        $esUltimo = $i === $diasRango - 1;
        $encima = $esUltimo && ($i % $pasoX) !== 0 && ($i % $pasoX) < max(2, (int) ($pasoX / 2));
        $ejeX[$i] = ($i % $pasoX === 0 || ($esUltimo && !$encima)) ? date('d/m', strtotime($f)) : '';

        $l = [$d['home'] . ' entraron a la página', $d['form'] . ' abrieron el formulario',
              $env . ($env === 1 ? ' postulación' : ' postulaciones')];
        if ($d['home'] > 0) {
            $l[] = 'Ese día abrió el formulario el ' . round($d['form'] * 100 / $d['home']) . '% de los que entraron';
        }
        $tips[$i] = array_merge([fecha_larga($f)], $l);
    }
    ?>
    <?php if ($diasRango >= 2): ?>
      <h3 class="panel-title" style="margin-top:26px">Día por día</h3>
      <?= svg_series([
          ['nombre' => 'Página',      'color' => '#AB2759', 'valores' => $sHome],
          ['nombre' => 'Formulario',  'color' => '#0B6B99', 'valores' => $sForm],
          ['nombre' => 'Postularon',  'color' => '#B5651D', 'valores' => $sEnv],
      ], $ejeX, $tips) ?>
      <div class="cal-leyenda">
        <span><i style="background:#AB2759"></i> Entraron a la página</span>
        <span><i style="background:#0B6B99"></i> Abrieron el formulario</span>
        <span><i style="background:#B5651D"></i> Se postularon</span>
      </div>

      <?php
      // La conversión sola, a su propia escala. En el gráfico de arriba queda
      // pegada al cero: al lado de cien visitas, dos postulaciones no se mueven.
      // La tendencia se calcula sumando primero y dividiendo después: las
      // postulaciones de los últimos siete días sobre las personas de esos
      // mismos siete días.
      //
      // Promediar los porcentajes diarios —que es lo que uno hace de primera—
      // está mal acá: le da el mismo peso a un día de tres visitas que a uno de
      // cien, y con estos números eso deforma la curva hasta dejarla pegada al
      // cero mientras el dato diario pega saltos del 5%.
      $VENTANA = 7;
      $tasas = $tendencia = $tipsTasa = [];
      foreach ($serieEmbudo as $i => $d) {
          $tasas[] = $d['home'] > 0 ? round($d['env'] * 100 / $d['home'], 2) : 0.0;

          $a = max(0, $i - intdiv($VENTANA, 2));
          $b = min(count($serieEmbudo) - 1, $i + intdiv($VENTANA, 2));
          $tramo = array_slice($serieEmbudo, $a, $b - $a + 1);
          $vis = array_sum(array_column($tramo, 'home'));
          $env = array_sum(array_column($tramo, 'env'));
          $tendencia[] = $vis > 0 ? round($env * 100 / $vis, 2) : 0.0;

          $tipsTasa[$i] = [
              fecha_larga($d['f']),
              $d['home'] === 0 ? 'Nadie entró ese día'
                : number_format(end($tasas), 2, ',', '') . '% ese día',
              $d['env'] . ($d['env'] === 1 ? ' postulación' : ' postulaciones') . ' sobre ' . $d['home']
                . ($d['home'] === 1 ? ' persona' : ' personas'),
          ];
      }
      ?>
      <h3 class="panel-title" style="margin-top:30px">Tasa de conversión, día por día</h3>
      <p class="hint" style="margin-top:-8px">
        La misma conversión de arriba pero sola y a su escala, para que se vean las variaciones.
        La línea fina es el dato de cada día; la gruesa es la tendencia de siete días, calculada
        sumando primero las postulaciones y las visitas de esa semana y dividiendo después.
      </p>
      <?= svg_tasa($tasas, $tendencia, $ejeX, $tipsTasa) ?>
      <div class="cal-leyenda">
        <span><i class="ref-fina"></i> Cada día</span>
        <span><i class="ref-gruesa"></i> Tendencia de 7 días</span>
      </div>
    <?php endif; ?>

    <p class="hint" style="margin-top:18px">
      <strong>Cómo leerlo.</strong> Las cuatro primeras etapas cuentan personas y la última cuenta
      postulaciones, y no se pueden enlazar entre sí: al visitante lo identifica un código que se
      recalcula todos los días —eso es justamente lo que hace que la medición no siga a nadie—, así
      que quien mira el lunes y se postula el miércoles figura como dos personas distintas. El
      embudo compara el volumen de cada etapa dentro del período; no persigue a una misma persona
      de punta a punta.
    </p>
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
