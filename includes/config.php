<?php
/**
 * Configuración central del sitio.
 * Todo lo que cambia de una cohorte a otra vive acá.
 */

date_default_timezone_set('America/Argentina/Buenos_Aires');

const SITE_NAME = 'Esquel LAB';
const SITE_TAGLINE = 'Laboratorio de Destino Esquel';
// Dominio sin https://, para los textos que la gente copia y pega.
const SITE_DOMINIO = 'esquel.site';

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

// --- Roles del panel ------------------------------------------------------
/**
 * Cómo se llama cada rol en pantalla, y qué hace.
 *
 * Las claves (viewer / editor / admin) son las que están guardadas en la base
 * desde la primera versión y no se tocan: renombrarlas obligaría a una
 * migración que, si sale a medias, deja gente sin poder entrar. Lo que ve el
 * usuario es la etiqueta.
 *
 * "vota" es aparte del escalafón de permisos a propósito. El admin puede más
 * que un evaluador en todo lo demás, pero no emite voto: coordina el proceso y
 * mueve las postulaciones de etapa, y quien decide no debería además estar
 * puntuando. Por eso ser jurado se pregunta por rol exacto y no por jerarquía.
 */
const ROLES_INFO = [
    'viewer' => [
        'label'  => 'Observador',
        'ayuda'  => 'Ve las postulaciones y los votos del jurado, y baja el CSV. No vota.',
        'vota'   => false,
    ],
    'editor' => [
        'label'  => 'Evaluador',
        'ayuda'  => 'Todo lo anterior y además emite su voto con comentario. Es el jurado.',
        'vota'   => true,
    ],
    'admin' => [
        'label'  => 'Administrador',
        'ayuda'  => 'Coordina: mueve de etapa, gestiona usuarios y ve la analítica. No vota.',
        'vota'   => false,
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
