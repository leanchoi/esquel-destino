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
 * Curva de una serie temporal, con relleno bajo la línea.
 *
 * @param array       $valores  los números, en orden
 * @param array       $etiquetas etiqueta del eje x por punto ('' = no se rotula)
 * @param array       $titulos  texto del tooltip nativo por punto
 * @param int|null    $corte    último punto con datos reales; de ahí no se dibuja
 * @param string      $color
 */
function svg_curva(array $valores, array $etiquetas, array $titulos = [], ?int $corte = null, string $color = COLOR_ACELERA): string
{
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

    $puntos = [];
    for ($i = 0; $i <= $corte; $i++) {
        $puntos[] = round($x($i), 1) . ',' . round($y($valores[$i]), 1);
    }
    $linea = 'M' . implode(' L', $puntos);
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

    for ($i = 0; $i <= $corte; $i++) {
        if ($valores[$i] <= 0) {
            continue;
        }
        $svg .= '<circle class="g-punto" cx="' . round($x($i), 1) . '" cy="' . round($y($valores[$i]), 1)
              . '" r="3.4" style="fill:' . e($color) . '"><title>' . e($titulos[$i] ?? '') . '</title></circle>';
    }
    foreach ($etiquetas as $i => $txt) {
        if ($txt === '' || $i >= $n) {
            continue;
        }
        $svg .= '<text class="g-x" x="' . round($x($i), 1) . '" y="' . ($H - 10) . '" text-anchor="middle">' . e($txt) . '</text>';
    }

    return $svg . '</svg></div>';
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
            $html .= '<span class="heat-c n' . $i . '" title="' . e($dias[$d] . ' ' . str_pad((string) $h, 2, '0', STR_PAD_LEFT)
                   . ':00 · ' . $v . ($v === 1 ? ' visita' : ' visitas')) . '"></span>';
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
                  . '" cy="' . round($y($ev['hora']), 1) . '" r="3.6"><title>' . e($ev['titulo']) . '</title></circle>';
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
