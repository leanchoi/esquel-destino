<?php
/**
 * Gráficos del panel, dibujados a mano en SVG y HTML.
 *
 * No hay librería de charts y no la va a haber: el sitio no tiene paso de
 * compilación, así que meter una implicaría bajarse 100 KB de JavaScript desde
 * un CDN para dibujar veinticuatro números. Estas funciones escriben el SVG
 * directo.
 *
 * Todos los SVG llevan viewBox y escalan con el ancho del panel manteniendo la
 * proporción. Nada de preserveAspectRatio="none": eso estira el dibujo, y con
 * él los círculos —que salen ovalados— y las etiquetas.
 */

/** Colores de las dos líneas del programa, tal como se usan en los gráficos. */
const COLOR_ACELERA = '#AB2759';
const COLOR_RAIZ    = '#236F4C';

/**
 * Por qué Raíz va con trama y no sólo con color.
 *
 * Medido con el validador de paletas: #AB2759 y #236F4C se separan 26 puntos
 * para la vista normal pero apenas 2,4 bajo deuteranopia, que es el daltonismo
 * más común —una de cada doce personas con ojos de varón—. Para esa persona las
 * dos líneas del programa son el mismo color.
 *
 * Cambiarle el color a Raíz arreglaba la medición y rompía la identidad, que en
 * el resto del sitio ya es verde. Así que los colores se quedan y la diferencia
 * la carga además una trama diagonal, que se ve igual sin distinguir ningún
 * color. La referencia del gráfico muestra las dos con su trama.
 */
function trama_raiz(): string
{
    return 'repeating-linear-gradient(45deg, ' . COLOR_RAIZ . ' 0 5px, #1A5539 5px 10px)';
}

/**
 * Atributos del cuadro flotante de un elemento.
 *
 * El <title> de SVG que se usaba antes tarda casi un segundo en aparecer, no se
 * puede maquetar y en el celular directamente no existe. Estos atributos los
 * levanta assets/js/analitica.js.
 *
 * @param string   $titulo  el encabezado
 * @param string[] $lineas  el dato y su lectura, una por línea
 */
function tt(string $titulo, array $lineas = []): string
{
    $lineas = array_values(array_filter($lineas, fn($l) => trim((string) $l) !== ''));
    return ' data-tt="' . e($titulo) . '"'
         . ($lineas ? ' data-tt-lineas="' . e(implode('|', $lineas)) . '"' : '');
}

/**
 * Une los puntos con una curva en vez de con segmentos rectos.
 *
 * Usa interpolación cúbica monótona (Fritsch–Carlson), y no una spline
 * cualquiera, por una razón que importa: una spline común se pasa de largo en
 * los cambios bruscos e inventa picos y valles que en los datos no están. Si un
 * día hubo 0 postulaciones y al siguiente 3, una curva mal armada dibuja un
 * pozo por debajo de cero antes de subir. Ésta no puede: entre dos puntos la
 * curva se queda siempre entre esos dos valores.
 *
 * @param array $pts  [[x, y], …] en coordenadas del SVG
 */
function path_suave(array $pts): string
{
    $n = count($pts);
    if ($n < 2) {
        return $n === 1 ? 'M' . $pts[0][0] . ',' . $pts[0][1] : '';
    }
    if ($n === 2) {
        return 'M' . $pts[0][0] . ',' . $pts[0][1] . ' L' . $pts[1][0] . ',' . $pts[1][1];
    }

    // Pendiente de cada tramo.
    $d = [];
    for ($i = 0; $i < $n - 1; $i++) {
        $dx = $pts[$i + 1][0] - $pts[$i][0];
        $d[$i] = $dx == 0.0 ? 0.0 : ($pts[$i + 1][1] - $pts[$i][1]) / $dx;
    }

    // Tangente en cada punto: el promedio de los tramos que llegan y salen.
    $m = [$d[0]];
    for ($i = 1; $i < $n - 1; $i++) {
        $m[$i] = ($d[$i - 1] * $d[$i] <= 0) ? 0.0 : ($d[$i - 1] + $d[$i]) / 2;
    }
    $m[$n - 1] = $d[$n - 2];

    // Y acá se recortan las tangentes para que la curva no se pase de largo.
    for ($i = 0; $i < $n - 1; $i++) {
        if ($d[$i] == 0.0) {
            $m[$i] = 0.0;
            $m[$i + 1] = 0.0;
            continue;
        }
        $a = $m[$i] / $d[$i];
        $b = $m[$i + 1] / $d[$i];
        $s = $a * $a + $b * $b;
        if ($s > 9) {
            $t = 3 / sqrt($s);
            $m[$i] = $t * $a * $d[$i];
            $m[$i + 1] = $t * $b * $d[$i];
        }
    }

    $path = 'M' . round($pts[0][0], 1) . ',' . round($pts[0][1], 1);
    for ($i = 0; $i < $n - 1; $i++) {
        $dx = ($pts[$i + 1][0] - $pts[$i][0]) / 3;
        $path .= ' C' . round($pts[$i][0] + $dx, 1) . ',' . round($pts[$i][1] + $m[$i] * $dx, 1)
               . ' ' . round($pts[$i + 1][0] - $dx, 1) . ',' . round($pts[$i + 1][1] - $m[$i + 1] * $dx, 1)
               . ' ' . round($pts[$i + 1][0], 1) . ',' . round($pts[$i + 1][1], 1);
    }
    return $path;
}

/**
 * Curva de una serie temporal, con relleno bajo la línea.
 *
 * Cada punto lleva además una banda transparente de alto completo que es la que
 * captura el mouse: acertarle a un círculo de tres píxeles y medio no es una
 * interacción, es puntería.
 *
 * @param array $puntos  cada uno ['v' => int, 'x' => string etiqueta o '', 'tt' => [título, línea…]]
 * @param int|null $corte  último punto con datos reales; de ahí no se dibuja
 */
function svg_curva(array $puntos, ?int $corte = null, string $color = COLOR_ACELERA): string
{
    $valores = array_map(fn($p) => (int) ($p['v'] ?? 0), $puntos);
    $n = count($valores);
    if ($n < 2) {
        return '';
    }
    $corte = $corte === null ? $n - 1 : max(0, min($corte, $n - 1));

    $W = 720; $H = 200; $izq = 38; $der = 12; $arriba = 14; $abajo = 30;
    $ancho = $W - $izq - $der;
    $alto  = $H - $arriba - $abajo;

    // El techo se redondea a un múltiplo par del paso para que la marca del
    // medio caiga en un entero. Con tope 75 el eje decía "37", que no es la
    // mitad de nada y encima está redondeado para abajo.
    $tope = max(1, max($valores));
    $paso = $tope <= 10 ? 2 : ($tope <= 50 ? 10 : ($tope <= 200 ? 20 : ($tope <= 1000 ? 100 : 500)));
    $tope = (int) (ceil($tope / $paso) * $paso);
    if (($tope / $paso) % 2 !== 0) {
        $tope += $paso;
    }

    $x = fn(int $i) => $izq + ($n > 1 ? $ancho * $i / ($n - 1) : 0);
    $y = fn(float $v) => $arriba + $alto - ($alto * $v / $tope);

    // $coords y no $puntos: así se llama el parámetro, y pisarlo dejaba sin
    // texto a todos los cuadros flotantes de la curva.
    $coords = [];
    for ($i = 0; $i <= $corte; $i++) {
        $coords[] = round($x($i), 1) . ',' . round($y($valores[$i]), 1);
    }
    $linea = 'M' . implode(' L', $coords);
    $area  = $linea . ' L' . round($x($corte), 1) . ',' . round($y(0), 1)
                    . ' L' . round($x(0), 1) . ',' . round($y(0), 1) . ' Z';

    $svg = '<div class="g-wrap"><svg class="g-svg" viewBox="0 0 ' . $W . ' ' . $H . '" role="img">';

    for ($g = 0; $g <= 2; $g++) {
        $v = $tope * $g / 2;
        $py = round($y($v), 1);
        $svg .= '<line class="g-grid" x1="' . $izq . '" y1="' . $py . '" x2="' . ($W - $der) . '" y2="' . $py . '"/>'
              . '<text class="g-eje" x="' . ($izq - 8) . '" y="' . ($py + 4) . '" text-anchor="end">' . (int) $v . '</text>';
    }

    if ($corte < $n - 1) {
        $cx = round($x($corte), 1);
        $svg .= '<line class="g-ahora" x1="' . $cx . '" y1="' . $arriba . '" x2="' . $cx . '" y2="' . ($arriba + $alto) . '"/>';
    }

    $svg .= '<path class="g-area" d="' . $area . '" style="fill:' . e($color) . '"/>'
          . '<path class="g-linea" d="' . $linea . '" style="stroke:' . e($color) . '"/>';

    // La línea de referencia vertical que sigue al mouse. Arranca escondida.
    $svg .= '<line class="g-cruz" x1="0" y1="' . $arriba . '" x2="0" y2="' . ($arriba + $alto) . '" hidden/>';

    for ($i = 0; $i <= $corte; $i++) {
        if ($valores[$i] <= 0) {
            continue;
        }
        $svg .= '<circle class="g-punto" data-i="' . $i . '" cx="' . round($x($i), 1) . '" cy="'
              . round($y($valores[$i]), 1) . '" r="3.4" style="fill:' . e($color) . '"/>';
    }

    // Las bandas de contacto van últimas para quedar por encima de todo.
    $ancho1 = $n > 1 ? $ancho / ($n - 1) : $ancho;
    for ($i = 0; $i <= $corte; $i++) {
        $bx = round($x($i) - $ancho1 / 2, 1);
        $svg .= '<rect class="g-hit" data-i="' . $i . '" x="' . max($izq - 2, $bx) . '" y="' . $arriba
              . '" width="' . round($ancho1, 1) . '" height="' . $alto . '"'
              . tt($puntos[$i]['tt'][0] ?? '', array_slice($puntos[$i]['tt'] ?? [], 1)) . '/>';
    }

    foreach ($puntos as $i => $p) {
        if (($p['x'] ?? '') === '' || $i >= $n) {
            continue;
        }
        $svg .= '<text class="g-x" x="' . round($x($i), 1) . '" y="' . ($H - 10) . '" text-anchor="middle">'
              . e($p['x']) . '</text>';
    }

    return $svg . '</svg></div>';
}

/**
 * Varias series sobre el mismo eje, con referencia.
 *
 * Un solo cuadro flotante por columna, con el valor de todas las series a la
 * vez: es lo que se quiere comparar. Las tres tintas están medidas con el
 * validador de paletas y se distinguen también con daltonismo (ΔE 12,4 en el
 * peor par bajo deuteranopia), y además cada línea lleva su nombre escrito en
 * la punta, así el color no es el único dato.
 *
 * Nunca dos ejes: son todas personas, se miden con la misma regla.
 *
 * @param array $series  [['nombre'=>, 'color'=>, 'valores'=>int[]], …]
 * @param array $ejeX    etiqueta por posición ('' = no se rotula)
 * @param array $tips    por posición: [título, línea…]
 */
function svg_series(array $series, array $ejeX, array $tips): string
{
    if (!$series || count($series[0]['valores']) < 2) {
        return '';
    }
    $n = count($series[0]['valores']);

    $W = 720; $H = 220; $izq = 38; $der = 74; $arriba = 14; $abajo = 30;
    $ancho = $W - $izq - $der;
    $alto  = $H - $arriba - $abajo;

    $tope = 1;
    foreach ($series as $s) {
        $tope = max($tope, max($s['valores']));
    }
    $paso = $tope <= 10 ? 2 : ($tope <= 50 ? 10 : ($tope <= 200 ? 20 : ($tope <= 1000 ? 100 : 500)));
    $tope = (int) (ceil($tope / $paso) * $paso);
    if (($tope / $paso) % 2 !== 0) {
        $tope += $paso;
    }

    $x = fn(int $i) => $izq + ($n > 1 ? $ancho * $i / ($n - 1) : 0);
    $y = fn(float $v) => $arriba + $alto - ($alto * $v / $tope);

    $svg = '<div class="g-wrap"><svg class="g-svg" viewBox="0 0 ' . $W . ' ' . $H . '" role="img">';

    for ($g = 0; $g <= 2; $g++) {
        $v = $tope * $g / 2;
        $py = round($y($v), 1);
        $svg .= '<line class="g-grid" x1="' . $izq . '" y1="' . $py . '" x2="' . ($W - $der) . '" y2="' . $py . '"/>'
              . '<text class="g-eje" x="' . ($izq - 8) . '" y="' . ($py + 4) . '" text-anchor="end">' . (int) $v . '</text>';
    }
    $svg .= '<line class="g-cruz" x1="0" y1="' . $arriba . '" x2="0" y2="' . ($arriba + $alto) . '" hidden />';

    foreach ($series as $k => $s) {
        $coords = [];
        for ($i = 0; $i < $n; $i++) {
            $coords[] = round($x($i), 1) . ',' . round($y($s['valores'][$i]), 1);
        }
        $svg .= '<path class="g-linea" d="M' . implode(' L', $coords) . '" style="stroke:' . e($s['color']) . '"/>';
        foreach ($s['valores'] as $i => $v) {
            if ($v > 0) {
                $svg .= '<circle class="g-punto s' . $k . '" data-i="' . $i . '" cx="' . round($x($i), 1)
                      . '" cy="' . round($y($v), 1) . '" r="2.6" style="fill:' . e($s['color']) . '"/>';
            }
        }
        // El nombre en la punta de la línea: identidad sin depender del color.
        $svg .= '<text class="g-fin" x="' . ($W - $der + 6) . '" y="'
              . round($y($s['valores'][$n - 1]) + 3.5, 1) . '" style="fill:' . e($s['color']) . '">'
              . e($s['nombre']) . '</text>';
    }

    $ancho1 = $n > 1 ? $ancho / ($n - 1) : $ancho;
    for ($i = 0; $i < $n; $i++) {
        $svg .= '<rect class="g-hit" data-i="' . $i . '" x="' . round(max($izq - 2, $x($i) - $ancho1 / 2), 1)
              . '" y="' . $arriba . '" width="' . round($ancho1, 1) . '" height="' . $alto . '"'
              . tt($tips[$i][0] ?? '', array_slice($tips[$i] ?? [], 1)) . '/>';
    }
    foreach ($ejeX as $i => $txt) {
        if ($txt === '' || $i >= $n) {
            continue;
        }
        $svg .= '<text class="g-x" x="' . round($x($i), 1) . '" y="' . ($H - 10) . '" text-anchor="middle">' . e($txt) . '</text>';
    }

    return $svg . '</svg></div>';
}

/**
 * Una sola tasa, a su propia escala, con la tendencia encima.
 *
 * Existe porque en el gráfico de tres líneas la conversión queda pegada al cero:
 * al lado de cien visitas, dos postulaciones no se mueven. Acá el eje arranca en
 * lo que hay, así que las variaciones se ven.
 *
 * La línea fina es el día a día y la gruesa es el promedio de siete días. Con
 * números chicos —dos postulaciones sobre ochenta visitas— el dato diario salta
 * de 0 a 4% y de vuelta a 0 sin que haya pasado nada: el promedio móvil es el
 * que muestra hacia dónde va, y por eso es el que se dibuja fuerte.
 *
 * @param array $valores  la tasa cruda de cada día
 * @param array $media    la tendencia ya calculada, día por día
 * @param array $ejeX     etiqueta por posición ('' = no se rotula)
 * @param array $tips     por posición: [título, línea…]
 */
function svg_tasa(array $valores, array $media, array $ejeX, array $tips, string $color = '#B5651D'): string
{
    $n = count($valores);
    if ($n < 2) {
        return '';
    }

    $W = 720; $H = 200; $izq = 42; $der = 12; $arriba = 16; $abajo = 30;
    $ancho = $W - $izq - $der;
    $alto  = $H - $arriba - $abajo;

    $tope = max(0.5, max(max($valores), max($media)));
    $paso = $tope <= 2 ? 0.5 : ($tope <= 5 ? 1 : ($tope <= 20 ? 5 : 10));
    $tope = ceil($tope / $paso) * $paso;
    if (fmod($tope / $paso, 2) != 0) {
        $tope += $paso;
    }

    $x = fn(int $i) => $izq + $ancho * $i / ($n - 1);
    $y = fn(float $v) => $arriba + $alto - ($alto * $v / $tope);

    $svg = '<div class="g-wrap"><svg class="g-svg" viewBox="0 0 ' . $W . ' ' . $H . '" role="img">';
    for ($g = 0; $g <= 2; $g++) {
        $v = $tope * $g / 2;
        $py = round($y($v), 1);
        $svg .= '<line class="g-grid" x1="' . $izq . '" y1="' . $py . '" x2="' . ($W - $der) . '" y2="' . $py . '"/>'
              . '<text class="g-eje" x="' . ($izq - 8) . '" y="' . ($py + 4) . '" text-anchor="end">'
              . rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',') . '%</text>';
    }
    $svg .= '<line class="g-cruz" x1="0" y1="' . $arriba . '" x2="0" y2="' . ($arriba + $alto) . '" hidden />';

    $ptsDia = $ptsMedia = [];
    for ($i = 0; $i < $n; $i++) {
        $ptsDia[] = [$x($i), $y($valores[$i])];
        $ptsMedia[] = [$x($i), $y($media[$i])];
    }
    $svg .= '<path class="g-tasa-dia" d="' . path_suave($ptsDia) . '" style="stroke:' . e($color) . '"/>'
          . '<path class="g-tasa-media" d="' . path_suave($ptsMedia) . '" style="stroke:' . e($color) . '"/>';

    for ($i = 0; $i < $n; $i++) {
        if ($valores[$i] > 0) {
            $svg .= '<circle class="g-punto" data-i="' . $i . '" cx="' . round($x($i), 1) . '" cy="'
                  . round($y($valores[$i]), 1) . '" r="2.6" style="fill:' . e($color) . '"/>';
        }
    }
    $ancho1 = $ancho / ($n - 1);
    for ($i = 0; $i < $n; $i++) {
        $extra = ['Tendencia de esos días: ' . number_format($media[$i], 2, ',', '') . '%'];
        $svg .= '<rect class="g-hit" data-i="' . $i . '" x="' . round(max($izq - 2, $x($i) - $ancho1 / 2), 1)
              . '" y="' . $arriba . '" width="' . round($ancho1, 1) . '" height="' . $alto . '"'
              . tt($tips[$i][0] ?? '', array_merge(array_slice($tips[$i] ?? [], 1), $extra)) . '/>';
    }
    foreach ($ejeX as $i => $txt) {
        if ($txt === '' || $i >= $n) {
            continue;
        }
        $svg .= '<text class="g-x" x="' . round($x($i), 1) . '" y="' . ($H - 10) . '" text-anchor="middle">' . e($txt) . '</text>';
    }
    return $svg . '</svg></div>';
}

/**
 * Un indicador con su luz.
 *
 * El color nunca va solo: cada tarjeta dice con letras cómo viene ("Bien",
 * "Atención", "Flojo") además de pintarse. Una de cada doce personas con ojos
 * de varón no distingue el verde del rojo, y un tablero que sólo cambia de
 * color no le dice nada.
 *
 * @param string $estado  'ok' | 'alerta' | 'mal' | 'neutro'
 */
function semaforo(string $titulo, string $valor, string $estado, string $lectura, string $referencia = ''): string
{
    $palabras = ['ok' => 'Bien', 'alerta' => 'Atención', 'mal' => 'Flojo', 'neutro' => 'Sin datos'];
    // El valor lleva marcado para la unidad; en el cuadro flotante va en texto
    // plano, que si no se lee el <span> escrito tal cual.
    $plano = trim(preg_replace('/\s+/', ' ', strip_tags($valor)));
    return '<article class="luz luz-' . e($estado) . '"'
        . tt($titulo, array_filter([$plano . ' · ' . ($palabras[$estado] ?? ''), $lectura, $referencia])) . '>'
        . '<header><span class="luz-t">' . e($titulo) . '</span>'
        . '<span class="luz-e">' . e($palabras[$estado] ?? '') . '</span></header>'
        . '<p class="luz-v">' . $valor . '</p>'
        . '<p class="luz-l">' . e($lectura) . '</p>'
        . ($referencia ? '<p class="luz-r">' . e($referencia) . '</p>' : '')
        . '</article>';
}

/**
 * Embudo de conversión: cada etapa con lo que sobrevive de la anterior.
 *
 * @param array $etapas  [['nombre'=>, 'n'=>int, 'nota'=>string, 'tt'=>[…]], …]
 */
function svg_embudo(array $etapas): string
{
    $tope = max(1, (int) ($etapas[0]['n'] ?? 1));
    $html = '<ol class="embudo">';
    foreach ($etapas as $i => $et) {
        $ancho = round($et['n'] * 100 / $tope, 1);
        $previa = $i > 0 ? (int) $etapas[$i - 1]['n'] : null;
        $pasan = $previa > 0 ? round($et['n'] * 100 / $previa) : null;
        $cae   = $pasan === null ? null : 100 - $pasan;

        $html .= '<li class="emb-paso"' . tt($et['nombre'], $et['tt'] ?? []) . '>'
            . '<div class="emb-cab"><span class="emb-n">' . $et['nombre'] . '</span>'
            . '<span class="emb-v">' . number_format($et['n'], 0, ',', '.') . '</span></div>'
            . '<div class="emb-barra"><span style="width:' . $ancho . '%"></span></div>'
            . '<div class="emb-pie">'
            . ($pasan === null
                ? '<span class="emb-base">el 100% del embudo arranca acá</span>'
                : '<span class="emb-pasan">pasa el ' . $pasan . '%</span>'
                  . ($cae > 0 ? '<span class="emb-cae' . ($cae >= 50 ? ' es-fuerte' : '') . '">se va el ' . $cae . '%</span>' : ''))
            . ($et['nota'] ? '<span class="emb-nota">' . e($et['nota']) . '</span>' : '')
            . '</div></li>';
    }
    return $html . '</ol>';
}

/**
 * Mapa de calor día de la semana × hora.
 *
 * Es el gráfico que contesta una pregunta que ningún otro contesta: a qué hora
 * y qué día conviene publicar. Una sola tinta de claro a oscuro, porque lo que
 * codifica es cantidad y no identidad.
 *
 * @param array $celdas  [diaSemana 0..6][hora 0..23] => visitas
 */
function heatmap_semana(array $celdas, int $tope): string
{
    $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    // Lunes primero: la semana de trabajo se lee mejor así.
    $orden = [1, 2, 3, 4, 5, 6, 0];

    $nombresLargos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $total = 0;
    foreach ($celdas as $fila) {
        $total += array_sum($fila);
    }

    // El ranking de franjas se calcula una vez: sirve para decir "es la tercera
    // hora más activa de la semana", que es lo que uno quiere saber.
    $planas = [];
    foreach ($celdas as $d => $fila) {
        foreach ($fila as $h => $v) {
            if ($v > 0) {
                $planas[$d . '-' . $h] = $v;
            }
        }
    }
    arsort($planas);
    $puesto = array_flip(array_keys($planas));

    $html = '<div class="heat"><div class="heat-grid">';
    $html .= '<span class="heat-esq"></span>';
    for ($h = 0; $h < 24; $h++) {
        $html .= '<span class="heat-hora">' . ($h % 3 === 0 ? str_pad((string) $h, 2, '0', STR_PAD_LEFT) : '') . '</span>';
    }
    foreach ($orden as $d) {
        $html .= '<span class="heat-dia">' . $dias[$d] . '</span>';
        for ($h = 0; $h < 24; $h++) {
            $v = (int) ($celdas[$d][$h] ?? 0);
            // Escala en raíz cuadrada: con unas pocas horas pico, la lineal deja
            // todo el resto en blanco y el gráfico no dice nada.
            $i = $tope > 0 && $v > 0 ? max(1, (int) ceil(sqrt($v / $tope) * 5)) : 0;

            $franja = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ' a '
                    . str_pad((string) (($h + 1) % 24), 2, '0', STR_PAD_LEFT) . ' h';
            $lineas = [$v === 0 ? 'Sin visitas' : $v . ($v === 1 ? ' visita' : ' visitas')];
            if ($v > 0 && $total > 0) {
                $lineas[] = round($v * 100 / $total, 1) . '% de todo el período';
                $p = ($puesto[$d . '-' . $h] ?? 99) + 1;
                if ($p === 1) {
                    $lineas[] = 'Es la franja más activa de la semana';
                } elseif ($p <= 5) {
                    $lineas[] = 'Está entre las ' . $p . ' franjas más activas';
                }
            }
            $html .= '<span class="heat-c n' . $i . '"'
                   . tt($nombresLargos[$d] . ', ' . $franja, $lineas) . '></span>';
        }
    }
    $html .= '</div><div class="heat-ref"><span>menos</span>';
    for ($i = 0; $i <= 5; $i++) {
        $html .= '<span class="heat-c n' . $i . '"></span>';
    }
    return $html . '<span>más</span></div></div>';
}

/**
 * Cuándo entró cada persona: un gráfico chico por cada una, uno debajo del otro.
 *
 * Fecha a lo ancho, hora del día a lo alto, un punto por ingreso. La hora crece
 * hacia arriba, como en cualquier gráfico: medianoche abajo.
 *
 * Va separado por persona y no todo junto con un color cada uno por una razón
 * medida. Para distinguir cinco o seis colores hace falta que se separen lo
 * suficiente también para quien ve distinto los colores, y a igual luminosidad
 * eso no da: probadas varias paletas, siempre quedaba algún par a ΔE 3 bajo
 * deuteranopia, o sea idénticos. Con una franja por persona la identidad la
 * carga el nombre escrito al lado, el color pasa a ser decoración, y de paso
 * el patrón de cada uno se lee mucho mejor que en una nube de puntos mezclados.
 *
 * @param array $eventos  cada uno ['fecha'=>'Y-m-d', 'hora'=>float, 'quien'=>string, 'titulo'=>string]
 */
function svg_momentos(array $eventos, string $desde, string $hasta): string
{
    $t0 = strtotime($desde . ' 00:00:00');
    $t1 = strtotime($hasta . ' 23:59:59');
    if ($t1 <= $t0) {
        $t1 = $t0 + 86400;
    }

    $porQuien = [];
    foreach ($eventos as $ev) {
        $porQuien[$ev['quien']][] = $ev;
    }
    ksort($porQuien, SORT_NATURAL | SORT_FLAG_CASE);
    if (!$porQuien) {
        return '';
    }

    $W = 720; $izq = 30; $der = 10; $altoFranja = 84; $entre = 16; $abajo = 24;
    $ancho = $W - $izq - $der;
    $H = count($porQuien) * ($altoFranja + $entre) + $abajo;

    $x = fn(int $ts) => $izq + $ancho * max(0, min(1, ($ts - $t0) / ($t1 - $t0)));

    $svg = '<div class="g-wrap"><svg class="g-svg" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" '
         . 'aria-label="Momento de cada ingreso al panel, por persona">';

    $fila = 0;
    foreach ($porQuien as $quien => $suyos) {
        $top = $fila * ($altoFranja + $entre);
        $y = fn(float $h) => $top + $altoFranja - ($altoFranja * $h / 24);

        $svg .= '<rect class="g-franja" x="' . $izq . '" y="' . $top . '" width="' . $ancho . '" height="' . $altoFranja . '"/>';
        // Sólo medianoche y mediodía: con el 24 rotulado, ese número quedaba
        // pegado al 00 de la franja de abajo. La hora exacta la da el tooltip.
        foreach ([0, 12] as $h) {
            $py = round($y($h), 1);
            $svg .= '<line class="g-grid" x1="' . $izq . '" y1="' . $py . '" x2="' . ($W - $der) . '" y2="' . $py . '"/>'
                  . '<text class="g-eje" x="' . ($izq - 6) . '" y="' . ($py + 3.5) . '" text-anchor="end">'
                  . str_pad((string) $h, 2, '0', STR_PAD_LEFT) . '</text>';
        }
        $svg .= '<text class="g-serie" x="' . ($izq + 8) . '" y="' . ($top + 15) . '">'
              . e($quien) . ' <tspan class="g-serie-n">' . count($suyos)
              . (count($suyos) === 1 ? ' ingreso' : ' ingresos') . '</tspan></text>';

        foreach ($suyos as $ev) {
            $svg .= '<circle class="g-dot" cx="' . round($x(strtotime($ev['fecha'] . ' 12:00:00')), 1)
                  . '" cy="' . round($y($ev['hora']), 1) . '" r="5"'
                  . tt($ev['titulo'], $ev['lineas'] ?? []) . '/>';
        }
        $fila++;
    }

    // Rótulos de fecha una sola vez, abajo de todo.
    $dias = max(1, (int) round(($t1 - $t0) / 86400));
    $salto = max(1, (int) ceil($dias / 6));
    for ($d = 0; $d <= $dias; $d += $salto) {
        $ts = $t0 + $d * 86400;
        $svg .= '<text class="g-x" x="' . round($x($ts), 1) . '" y="' . ($H - 8) . '" text-anchor="middle">'
              . date('d/m', $ts) . '</text>';
    }

    return $svg . '</svg></div>';
}
