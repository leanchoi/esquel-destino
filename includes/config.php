<?php
/**
 * Configuración central del sitio.
 * Todo lo que cambia de una cohorte a otra vive acá.
 */

date_default_timezone_set('America/Argentina/Buenos_Aires');

const SITE_NAME = 'Esquel LAB';
const SITE_TAGLINE = 'Laboratorio de Destino Esquel';

// --- Fechas de la cohorte -------------------------------------------------
const FECHA_APERTURA   = '2026-07-23 00:00:00';
const FECHA_CIERRE     = '2026-08-09 23:59:59';  // cierre duro: el formulario deja de aceptar envíos
const FECHA_INICIO     = '2026-08-10 00:00:00';
const FECHA_FIN        = '2026-10-02 00:00:00';

// Cierre duro confirmado: pasado FECHA_CIERRE el formulario se bloquea solo.
const CIERRE_DURO = true;

// --- Contacto -------------------------------------------------------------
const EMAIL_PRENSA = 'comunicacionesquel25@gmail.com';
const EMAIL_PROGRAMA = 'comunicacionesquel25@gmail.com';

// --- Programas ------------------------------------------------------------
const PROGRAMAS = [
    'Acelera' => [
        'nombre'    => 'Esquel Acelera',
        'ambito'    => 'Urbano',
        'resumen'   => 'Si tu proyecto está en la ciudad',
        'detalle'   => 'Gastronomía, talleres, comercios, circuitos, guías y oficios urbanos con potencial turístico.',
        'cupo'      => '8 a 10 proyectos',
        'color'     => '#ab2759',
    ],
    'Raiz' => [
        'nombre'    => 'Raíz',
        'ambito'    => 'Rural',
        'resumen'   => 'Si tu proyecto está en el campo',
        'detalle'   => 'Chacras, estancias, crianceros, viñedos, productores de lana, fruta fina y dulces regionales.',
        'cupo'      => '5 a 8 proyectos',
        'color'     => '#236f4c',
    ],
];

// --- Estados del CRM ------------------------------------------------------
const ESTADOS = [
    'Pendiente'       => ['label' => 'Pendiente',       'color' => '#8a8178'],
    'En revisión'     => ['label' => 'En revisión',     'color' => '#a6650e'],
    'Preseleccionado' => ['label' => 'Preseleccionado', 'color' => '#ab2759'],
    'Entrevista'      => ['label' => 'Entrevista',      'color' => '#1e63c7'],
    'Aprobado'        => ['label' => 'Aprobado',        'color' => '#236f4c'],
    'Lista de espera' => ['label' => 'Lista de espera', 'color' => '#946200'],
    'Rechazado'       => ['label' => 'No seleccionado', 'color' => '#9b2c2c'],
];

/**
 * Criterios de ponderación del Cuadro Técnico.
 * El peso define cuánto influye cada criterio en el puntaje final: "perfil y
 * motivación" pesa más que el resto por definición del programa.
 */
const CRITERIOS = [
    'rating_perfil' => [
        'label' => 'Perfil y motivación',
        'ayuda' => 'Ganas, compromiso y capacidad real de sostener el proceso.',
        'peso'  => 1.5,
    ],
    'rating_diferenciacion' => [
        'label' => 'Diferenciación',
        'ayuda' => 'Qué tiene de único frente al resto de la oferta del destino.',
        'peso'  => 1.0,
    ],
    'rating_impacto' => [
        'label' => 'Impacto en la matriz turística',
        'ayuda' => 'Cuánto suma a la oferta general y qué derrame genera.',
        'peso'  => 1.0,
    ],
    'rating_viabilidad' => [
        'label' => 'Viabilidad operativa',
        'ayuda' => 'Qué tan cerca está de poder venderse sin gran inversión.',
        'peso'  => 1.0,
    ],
    'rating_producto_fisico' => [
        'label' => 'Producto físico asociado',
        'ayuda' => 'Potencial de un objeto de identidad local ligado a la experiencia.',
        'peso'  => 0.5,
    ],
];
