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

// --- Quién organiza y quién acompaña --------------------------------------
/**
 * Cómo se nombra al programa y a quienes lo respaldan.
 *
 * Esquel LAB se presenta como una iniciativa con apoyo institucional y no como
 * una dependencia del municipio. Es una diferencia de posicionamiento y no de
 * los hechos: las Subsecretarías siguen acompañando, financiando e integrando
 * el Cuadro Técnico, y eso está dicho en el sitio en todos lados. Lo que cambia
 * es de quién es el programa.
 *
 * Está acá arriba y no repartido por las páginas porque en algún momento la
 * titularidad puede cambiar de manos, y cuando pase tiene que ser una edición
 * en un archivo y no una cacería por veinte plantillas.
 *
 * Ojo con una cosa que estas constantes NO resuelven: en terminos.php hay
 * cláusulas —quién recibe los datos personales, de quién son los materiales—
 * que nombran a una parte responsable. Eso es una definición legal, no una de
 * redacción, y tiene que confirmarla el área legal del municipio antes de que
 * la titularidad se mueva. Un aviso de privacidad que nombra al responsable
 * equivocado no es un detalle de estilo.
 */
const ORGANIZA = 'Esquel LAB';
const APOYO_INSTITUCION = 'Municipalidad de Esquel';
const APOYO_AREAS = 'las Subsecretarías de Turismo y de Producción';
/** La leyenda completa, para pies de página y fichas. */
const LEYENDA_APOYO = 'Con el acompañamiento de la ' . APOYO_INSTITUCION . ', a través de ' . APOYO_AREAS . '.';
/** La versión corta, para cuando va dentro de una frase. */
const LEYENDA_APOYO_CORTA = 'con el apoyo de la ' . APOYO_INSTITUCION;

// --- Contacto -------------------------------------------------------------
const EMAIL_PRENSA = 'comunicacionesquel25@gmail.com';
const EMAIL_PROGRAMA = 'comunicacionesquel25@gmail.com';

// --- Video de fondo del cierre --------------------------------------------
// Va el ID solo, no la URL entera: de https://youtu.be/sX_Wcj130pE el ID es
// sX_Wcj130pE. Dejarlo vacío apaga el video y el bloque queda con la foto
// sola. La foto no es un plan B: es la base. Es lo que se ve mientras el
// video carga, en los celulares que no autoreproducen, y cuando el sistema
// pide menos movimiento. El video se le suma arriba cuando se puede.
const CIERRE_VIDEO_YT = 'sX_Wcj130pE';
/** Desde qué segundo arranca, para saltear placas o logos del comienzo. */
const CIERRE_VIDEO_DESDE = 0;
/** Sin extensión: al lado se sirven .webp y .jpg. */
const CIERRE_VIDEO_POSTER = 'assets/images/fotos/web/linea-acelera';

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

// --- Umbrales del tablero de indicadores ----------------------------------
/**
 * Dónde se pone en verde, en ámbar y en rojo cada indicador de la analítica.
 *
 * Son valores de referencia, no verdades: están acá para que se puedan discutir
 * y cambiar en un solo lugar, y para que el tablero pueda decir en pantalla
 * contra qué está comparando. Un semáforo que no dice su umbral es un semáforo
 * que hay que creerle.
 *
 * 'candidatos_por_cupo' es el que más conviene revisar. Dice cuántas
 * postulaciones hacen falta por cada cupo para que haya selección de verdad:
 * con 18 cupos y 18 postulaciones no se elige nada, se acepta a todos. Dos por
 * cupo es un piso razonable para un programa que recién arranca.
 */
const UMBRALES = [
    'conversion'          => ['ok' => 2.0, 'alerta' => 1.0],   // % de visitantes que se postulan
    'terminacion'         => ['ok' => 20.0, 'alerta' => 10.0], // % que termina el formulario tras abrirlo
    'candidatos_por_cupo' => 2.0,
    'ritmo'               => ['ok' => 100.0, 'alerta' => 70.0], // % del objetivo que proyecta el ritmo actual
    'trafico'             => ['ok' => 0.0, 'alerta' => -15.0],  // variación % contra el período anterior
];

// --- Qué pide el formulario -----------------------------------------------
/**
 * Campos obligatorios, agrupados por paso. Una sola lista para los dos lados:
 * inscribirse.php la usa para validar en el servidor y main.js la recibe en
 * JSON para frenar al visitante en el paso donde falta algo.
 *
 * Antes había dos listas parecidas —una en PHP y otra escrita a mano en el
 * JavaScript— y se desincronizaron: la pantalla dejaba pasar campos que el
 * servidor después rechazaba, y el visitante volvía al principio sin entender
 * por qué.
 *
 * Quedan fuera a propósito: 'redes', porque hay gente que no tiene ninguna y
 * exigirla la dejaría afuera por no estar en internet; 'material', que es un
 * enlace opcional; y 'producto_fisico_cual', que sólo se pide si antes dijiste
 * que sí y por eso se valida aparte.
 */
const REQUERIDOS_POR_PASO = [
    1 => [
        'program'   => 'Elegí una de las dos líneas.',
        'situacion' => 'Contanos en qué situación estás.',
    ],
    2 => [
        'name'         => 'Poné el nombre de tu proyecto.',
        'contact_name' => 'Necesitamos saber con quién hablamos.',
        'dni'          => 'Poné tu DNI, sin puntos.',
        'email'        => 'Revisá el correo: es por donde te vamos a contactar.',
        'phone'        => 'Dejanos un teléfono de contacto.',
        'barrio'       => 'Decinos de qué barrio o paraje sos.',
        'antiguedad'   => 'Contanos hace cuánto estás en esto.',
        'ubicacion'    => 'Contanos dónde está tu proyecto.',
    ],
    3 => [
        'descripcion' => 'Contanos qué hacés hoy. Sin esto no podemos evaluar la propuesta.',
        'diferencial' => 'Este campo es de los que más pesan en la evaluación.',
        'visitable'   => 'Contanos qué podría ver, hacer o probar un visitante.',
    ],
    4 => [
        'conexiones'      => 'Contanos con qué otros lugares o personas de Esquel se conecta.',
        'producto_fisico' => 'Decinos si hay un producto físico asociado.',
    ],
    5 => [
        'recursos' => 'Contanos con qué contás hoy.',
        'falta'    => 'Contanos qué te falta para poder vender.',
    ],
    6 => [
        'motivacion' => 'Contanos por qué querés participar. Es el criterio de mayor peso.',
        'equipo'     => 'Contanos quiénes participarían.',
        'compromiso' => 'Necesitamos que confirmes la disponibilidad de 12 horas semanales.',
    ],
];

// --- Respuestas que no ve todo el panel -----------------------------------
/**
 * Claves de application_details que sólo puede ver un administrador.
 *
 * El DNI no sirve para evaluar: no dice nada del proyecto. Se pide para poder
 * identificar a la persona en lo administrativo, y por eso lo ve quien hace esa
 * parte y nadie más. Un número de documento en manos de más gente de la
 * necesaria es un riesgo sin contrapartida.
 *
 * El filtro se aplica en el servidor, igual que con los votos: lo que no se
 * manda a la pantalla no se puede espiar mirando el código de la página.
 */
const DETALLES_SOLO_ADMIN = ['dni'];

// --- Barrios --------------------------------------------------------------
/**
 * Sugerencias para el campo de barrio. Van en un <datalist>, no en un <select>:
 * la lista ordena las respuestas de la mayoría sin dejar afuera al que vive en
 * un paraje que no está acá. Escribir libre sigue siendo válido.
 */
const BARRIOS = [
    'Centro', 'Ceferino', 'Estación', 'Badén', 'Bella Vista', 'Don Bosco',
    'Ideal', 'Km 4', 'Km 5', 'Las Américas', 'Malvinas Argentinas', 'Mutual',
    'Padre Juan', 'Roca', 'San Martín', 'Valle Chico', 'Villa Ayelén',
    'Zona rural', 'Trevelin', 'Otro',
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

// --- El comentario del jurado ---------------------------------------------
/**
 * Mínimo de caracteres del comentario de una evaluación.
 *
 * El puntaje ordena; el comentario es lo único que después explica por qué. Al
 * cerrar la selección hay que poder decirle a cada persona que se postuló qué
 * se vio en su propuesta, y un "muy bueno" no alcanza para eso. Trescientos
 * caracteres son unas cuatro o cinco líneas: incómodo de escribir de apuro,
 * razonable si de verdad se miró la postulación.
 */
const MIN_COMENTARIO = 300;

/**
 * Disparadores para el comentario. No es un formulario dentro del formulario:
 * son preguntas a la vista mientras se escribe, para que el jurado no se quede
 * en el juicio general y baje al caso concreto.
 */
const PISTAS_COMENTARIO = [
    'Qué tiene esta propuesta que no tenga otra de las que leíste.',
    'Qué le falta concretamente para poder recibir visitantes.',
    'Qué te hace dudar, si algo te hace dudar.',
    'Qué preguntarías en una entrevista.',
    'Con qué otro proyecto de Esquel lo ves conectado.',
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
