<?php
/**
 * La línea de cohortes: dónde está el programa y qué viene.
 *
 * Es el bloque que reemplaza al "postulate ya" cuando la convocatoria cerró.
 * Tiene que contar tres cosas de un vistazo: la primera cohorte cerró, está
 * trabajando ahora, y va a haber una segunda.
 *
 * Va en SVG dibujado a mano y no como imagen: el texto tiene que poder leerse
 * con un lector de pantalla, escalar sin pixelarse y cambiar de estado solo
 * cuando pasen las fechas. Una imagen exportada envejece el día que cambia
 * cualquier dato.
 *
 * El movimiento es decorativo y se activa recién cuando el bloque entra en
 * pantalla. Con "reducir movimiento" activado se ve el estado final completo,
 * sin animación: la información no está en el movimiento, está en el dibujo.
 */

/**
 * Los hitos de la línea, con su estado calculado a partir de las fechas.
 *
 * @return array<int, array{clave:string, cuando:string, titulo:string, texto:string, estado:string}>
 *         estado: hecho | ahora | proximo
 */
function hitos_cohortes(): array
{
    $ahora = time();
    $inicio = strtotime(FECHA_INICIO);
    $fin    = strtotime(FECHA_FIN);

    return [
        [
            'clave'  => 'convocatoria',
            'cuando' => fecha_larga(FECHA_CIERRE),
            'titulo' => 'Se abrió y se cerró la convocatoria',
            'texto'  => 'Se presentaron proyectos de toda la ciudad y del campo.',
            'estado' => 'hecho',
        ],
        [
            'clave'  => 'trabajo',
            'cuando' => 'Ocho semanas',
            'titulo' => 'La primera cohorte, manos a la obra',
            'texto'  => 'Trabajando en su precio, sus reservas y su material de venta.',
            'estado' => $ahora >= $inicio && $ahora < $fin ? 'ahora' : ($ahora >= $fin ? 'hecho' : 'proximo'),
        ],
        [
            'clave'  => 'muestra',
            'cuando' => fecha_larga(FECHA_FIN),
            'titulo' => 'Presentan lo que armaron',
            'texto'  => 'Ante las agencias receptivas y la prensa local.',
            'estado' => $ahora >= $fin ? 'hecho' : 'proximo',
        ],
        [
            'clave'  => 'proxima',
            'cuando' => PROXIMA_COHORTE_FECHA ? fecha_larga(PROXIMA_COHORTE_FECHA) : PROXIMA_COHORTE_CUANDO,
            'titulo' => PROXIMA_COHORTE,
            'texto'  => 'El próximo grupo de emprendedores. Puede ser el tuyo.',
            'estado' => 'proximo',
        ],
    ];
}

/**
 * Dibuja la línea.
 *
 * El orden del marcado importa: primero la línea, después los hitos. Así, si
 * algo falla con el CSS, lo que queda es una lista de pasos en orden y se
 * entiende igual.
 */
function linea_cohortes(): string
{
    $hitos = hitos_cohortes();
    $total = count($hitos);

    // Hasta dónde llega la línea llena: el último hito que ya pasó o está
    // pasando. Se calcula acá y no en el CSS porque depende de las fechas.
    $ultimoVivo = 0;
    foreach ($hitos as $i => $h) {
        if ($h['estado'] !== 'proximo') {
            $ultimoVivo = $i;
        }
    }
    $avance = $total > 1 ? round($ultimoVivo / ($total - 1) * 100) : 0;

    $html = '<ol class="coh" data-coh style="--avance:' . $avance . '%">';
    $html .= '<li class="coh-riel" aria-hidden="true"><span class="coh-riel-lleno"></span></li>';

    foreach ($hitos as $i => $h) {
        $esProxima = $h['clave'] === 'proxima';
        $html .= '<li class="coh-hito is-' . e($h['estado']) . ($esProxima ? ' es-proxima' : '') . '"'
               . ' style="--i:' . $i . '">';

        $html .= '<span class="coh-punto" aria-hidden="true">';
        if ($h['estado'] === 'hecho') {
            $html .= '<svg viewBox="0 0 24 24" class="coh-tilde"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
        } elseif ($h['estado'] === 'ahora') {
            $html .= '<span class="coh-pulso"></span>';
        }
        $html .= '</span>';

        $html .= '<span class="coh-cuando">' . e($h['cuando']) . '</span>';
        $html .= '<h3 class="coh-titulo">' . e($h['titulo']) . '</h3>';
        $html .= '<p class="coh-texto">' . e($h['texto']) . '</p>';

        // El estado en palabras, para quien no ve el dibujo.
        $etiqueta = match ($h['estado']) {
            'hecho'  => 'Ya pasó',
            'ahora'  => 'Está pasando ahora',
            default  => 'Todavía no',
        };
        $html .= '<span class="visually-hidden">' . $etiqueta . '</span>';

        if ($esProxima) {
            $html .= '<button type="button" class="btn btn-primary btn-sm coh-cta" data-abrir-avisame="linea">'
                   . 'Avisame cuando abra</button>';
        }

        $html .= '</li>';
    }

    return $html . '</ol>';
}
