<?php
/**
 * Qué se borró y qué se sumó entre dos versiones de un texto.
 *
 * Sirve para que el administrador pueda mirar la evaluación de un jurado y ver
 * el cambio, no dos paredes de texto para comparar a ojo. Un "subió el puntaje
 * de viabilidad de 2 a 4 y sacó el párrafo donde decía que no tenía costos"
 * se lee en dos segundos; encontrar eso solo, no.
 */

/** Tope de la comparación fina. Más grande que esto se informa en bloque. */
const DIFF_TOPE = 800;

/**
 * Parte el texto en palabras y espacios, conservando los dos.
 *
 * Los espacios entran como piezas propias para que rearmar la lista devuelva
 * el texto igualito al original, con sus saltos de línea y su sangría. Si se
 * descartaran, el comentario restaurado saldría con otro formato que el que
 * la persona escribió.
 */
function trozos_texto(string $texto): array
{
    $piezas = preg_split('/(\s+)/u', $texto, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    return $piezas === false ? [] : $piezas;
}

/**
 * Diferencias palabra por palabra entre dos textos.
 *
 * Primero se recortan el principio y el final que son iguales, que en un
 * comentario editado suelen ser casi todo. Lo que queda en el medio es lo
 * único que se compara en serio, y así una corrección de dos palabras sobre
 * un texto de mil no cuesta un millón de comparaciones.
 *
 * @return array<int, array{t: string, texto: string}>  t: igual | mas | menos
 */
function diff_palabras(string $antes, string $despues): array
{
    if ($antes === $despues) {
        return $antes === '' ? [] : [['t' => 'igual', 'texto' => $antes]];
    }

    $a = trozos_texto($antes);
    $b = trozos_texto($despues);
    $na = count($a);
    $nb = count($b);

    // Principio igual.
    $ini = 0;
    while ($ini < $na && $ini < $nb && $a[$ini] === $b[$ini]) {
        $ini++;
    }
    // Final igual, sin cruzarse con el principio ya recortado.
    $fin = 0;
    while ($fin < ($na - $ini) && $fin < ($nb - $ini) && $a[$na - 1 - $fin] === $b[$nb - 1 - $fin]) {
        $fin++;
    }

    $medioA = array_slice($a, $ini, $na - $ini - $fin);
    $medioB = array_slice($b, $ini, $nb - $ini - $fin);

    $salida = [];
    if ($ini > 0) {
        $salida[] = ['t' => 'igual', 'texto' => implode('', array_slice($a, 0, $ini))];
    }

    if (count($medioA) > DIFF_TOPE || count($medioB) > DIFF_TOPE) {
        // Demasiado para comparar palabra por palabra sin que cueste caro.
        // Se informa el bloque entero, que sigue siendo cierto: cambió todo eso.
        if ($medioA) $salida[] = ['t' => 'menos', 'texto' => implode('', $medioA)];
        if ($medioB) $salida[] = ['t' => 'mas',   'texto' => implode('', $medioB)];
    } else {
        foreach (lcs_diff($medioA, $medioB) as $tramo) {
            $salida[] = $tramo;
        }
    }

    if ($fin > 0) {
        $salida[] = ['t' => 'igual', 'texto' => implode('', array_slice($a, $na - $fin))];
    }

    return juntar_tramos($salida);
}

/**
 * Diferencia por subsecuencia común más larga.
 *
 * La tabla va en SplFixedArray y no en un array común: son enteros y nada más,
 * y un array de PHP gasta unas diez veces más memoria por casillero. Con el
 * tope de arriba, lo peor que puede pasar son unos 5 MB.
 */
function lcs_diff(array $a, array $b): array
{
    $n = count($a);
    $m = count($b);
    if ($n === 0 && $m === 0) return [];
    if ($n === 0) return [['t' => 'mas',   'texto' => implode('', $b)]];
    if ($m === 0) return [['t' => 'menos', 'texto' => implode('', $a)]];

    $ancho = $m + 1;
    $tabla = new SplFixedArray(($n + 1) * $ancho);
    for ($i = 0, $tope = ($n + 1) * $ancho; $i < $tope; $i++) {
        $tabla[$i] = 0;
    }

    for ($i = $n - 1; $i >= 0; $i--) {
        $fila = $i * $ancho;
        $sig  = ($i + 1) * $ancho;
        for ($j = $m - 1; $j >= 0; $j--) {
            $tabla[$fila + $j] = $a[$i] === $b[$j]
                ? $tabla[$sig + $j + 1] + 1
                : max($tabla[$sig + $j], $tabla[$fila + $j + 1]);
        }
    }

    $out = [];
    $i = 0;
    $j = 0;
    while ($i < $n && $j < $m) {
        if ($a[$i] === $b[$j]) {
            $out[] = ['t' => 'igual', 'texto' => $a[$i]];
            $i++;
            $j++;
        } elseif ($tabla[($i + 1) * $ancho + $j] >= $tabla[$i * $ancho + $j + 1]) {
            $out[] = ['t' => 'menos', 'texto' => $a[$i]];
            $i++;
        } else {
            $out[] = ['t' => 'mas', 'texto' => $b[$j]];
            $j++;
        }
    }
    while ($i < $n) { $out[] = ['t' => 'menos', 'texto' => $a[$i]]; $i++; }
    while ($j < $m) { $out[] = ['t' => 'mas',   'texto' => $b[$j]]; $j++; }

    return $out;
}

/** Pega los tramos seguidos del mismo tipo, para no escupir una pieza por palabra. */
function juntar_tramos(array $tramos): array
{
    $out = [];
    foreach ($tramos as $t) {
        if ($t['texto'] === '') {
            continue;
        }
        $ultimo = count($out) - 1;
        if ($ultimo >= 0 && $out[$ultimo]['t'] === $t['t']) {
            $out[$ultimo]['texto'] .= $t['texto'];
        } else {
            $out[] = $t;
        }
    }
    return $out;
}

/**
 * Cuántas palabras se sumaron y cuántas se borraron.
 * Es el titular del cambio: se lee antes de entrar a mirar el detalle.
 */
function resumen_diff(array $diff): array
{
    $mas = 0;
    $menos = 0;
    foreach ($diff as $t) {
        if ($t['t'] === 'igual') {
            continue;
        }
        // Se cuentan palabras, no espacios: "sumó 3 palabras" se entiende,
        // "sumó 7 piezas" no le dice nada a nadie.
        $palabras = preg_match_all('/\S+/u', $t['texto']);
        if ($t['t'] === 'mas') $mas += $palabras;
        else $menos += $palabras;
    }
    return ['mas' => $mas, 'menos' => $menos, 'hubo' => ($mas + $menos) > 0];
}

/**
 * Cómo se movió cada criterio entre dos versiones.
 *
 * @return array<string, array{label: string, antes: int, despues: int, delta: int}>
 *         sólo los criterios que cambiaron
 */
function diff_puntajes(array $antes, array $despues): array
{
    $out = [];
    foreach (CRITERIOS as $campo => $def) {
        $a = (int) ($antes[$campo] ?? 0);
        $d = (int) ($despues[$campo] ?? 0);
        if ($a !== $d) {
            $out[$campo] = ['label' => $def['label'], 'antes' => $a, 'despues' => $d, 'delta' => $d - $a];
        }
    }
    return $out;
}
