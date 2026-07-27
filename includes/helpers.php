<?php
/**
 * Utilidades compartidas.
 */

function e(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/** Arranca la sesión con cookie endurecida (una sola vez por request). */
function iniciar_sesion(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $params = [
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ];

    // Sin dominio explícito la cookie queda atada al host exacto, y entonces
    // esquel.site y www.esquel.site terminan con sesiones distintas: entrás
    // por una, abrís un link de la otra y aparecés deslogueado sin motivo.
    // Fijamos el dominio sin el www para que la sesión valga en las dos.
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = (string) preg_replace('/:\d+$/', '', $host);   // fuera el puerto
    if ($host !== '' && !filter_var($host, FILTER_VALIDATE_IP) && str_contains($host, '.')) {
        $params['domain'] = (string) preg_replace('/^www\./', '', $host);
    }

    session_set_cookie_params($params);

    // Nombre propio, y no el PHPSESSID de fábrica.
    //
    // Al empezar a fijar el dominio, la cookie nueva (.esquel.site) y la que
    // el navegador ya tenía guardada (esquel.site, sin dominio) pasaron a ser
    // dos entradas distintas con el mismo nombre. El navegador mandaba las
    // dos, PHP leía la vieja y te devolvía al login con la contraseña bien
    // puesta. Con un nombre propio no puede volver a chocar.
    session_name('esquellab_sesion');
    session_start();

    // Y de paso se borra la PHPSESSID heredada, que ya no sirve para nada.
    if (isset($_COOKIE['PHPSESSID']) && !headers_sent()) {
        $borrar = $params;
        $borrar['expires'] = time() - 3600;
        setcookie('PHPSESSID', '', $borrar);
        setcookie('PHPSESSID', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

/**
 * Ruta de un asset con el sello de su última modificación.
 *
 * El número de versión puesto a mano ya falló: se cambia el archivo, se
 * olvida subir el ?v= y el navegador —y el caché del hosting— siguen
 * sirviendo la copia vieja, así que el arreglo nunca llega al visitante.
 * Con filemtime la URL cambia sola cada vez que cambia el contenido.
 */
function asset(string $ruta): string
{
    $absoluta = __DIR__ . '/../' . $ruta;
    $sello = is_file($absoluta) ? filemtime($absoluta) : false;
    return $ruta . ($sello ? '?v=' . $sello : '');
}

/**
 * Una foto del sitio, en <picture>, al tamaño que realmente se muestra.
 *
 * Antes cada foto se servía en su master de 1800 px aunque la tarjeta midiera
 * 365: cuatro veces los píxeles necesarios, sobre una conexión de celular en
 * la cordillera. Ahora se sirven las copias de assets/images/fotos/web/, con
 * WebP primero y el JPEG detrás para el navegador que no lo soporte.
 *
 * width y height salen del archivo real: sin eso el navegador no sabe cuánto
 * espacio reservar y la página salta mientras carga.
 *
 * @param string $nombre  nombre del archivo sin extensión, p. ej. 'perfil-lana'
 * @param array  $attrs   atributos extra para el <img> (class, loading, etc.)
 */
function foto(string $nombre, string $alt, array $attrs = []): string
{
    $base = 'assets/images/fotos/web/' . $nombre;
    $jpg = __DIR__ . '/../' . $base . '.jpg';

    // Si todavía no se generó la versión web, se cae al master y no se rompe.
    if (!is_file($jpg)) {
        $base = 'assets/images/fotos/' . $nombre;
        $jpg = __DIR__ . '/../' . $base . '.jpg';
        if (!is_file($jpg)) {
            return '';
        }
    }

    $extras = '';
    foreach ($attrs as $k => $v) {
        $extras .= ' ' . $k . '="' . e((string) $v) . '"';
    }
    if (!isset($attrs['loading'])) {
        $extras .= ' loading="lazy"';        // el hero pasa loading="eager"
    }
    if (!isset($attrs['decoding'])) {
        $extras .= ' decoding="async"';
    }

    $tam = @getimagesize($jpg);
    $dim = $tam ? ' width="' . $tam[0] . '" height="' . $tam[1] . '"' : '';
    $webp = __DIR__ . '/../' . $base . '.webp';

    $html = '<picture>';
    if (is_file($webp)) {
        $html .= '<source type="image/webp" srcset="' . e(asset($base . '.webp')) . '">';
    }
    $html .= '<img src="' . e(asset($base . '.jpg')) . '" alt="' . e($alt) . '"' . $dim . $extras . '>';
    return $html . '</picture>';
}

// --- CSRF -----------------------------------------------------------------

function csrf_token(): string
{
    iniciar_sesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valido(?string $token): bool
{
    iniciar_sesion();
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// --- Fechas ---------------------------------------------------------------

// Estas dos aceptan null y devuelven una raya.
//
// Las fechas salen de la base, y una columna agregada por migración puede tener
// filas viejas sin rellenar. Que falte una fecha en una fila no puede tumbar la
// página entera: el panel de postulaciones se cayó justamente así.

function fecha_larga(?string $fecha): string
{
    $ts = $fecha === null || $fecha === '' ? false : strtotime($fecha);
    if ($ts === false) {
        return '—';
    }
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return (int) date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)];
}

function fecha_corta(?string $fecha, bool $conHora = false): string
{
    $ts = $fecha === null || $fecha === '' ? false : strtotime($fecha);
    return $ts === false ? '—' : date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $ts);
}

/** ¿La convocatoria acepta postulaciones en este momento? */
function convocatoria_abierta(): bool
{
    if (!CIERRE_DURO) {
        return true;
    }
    $ahora = time();
    return $ahora >= strtotime(FECHA_APERTURA) && $ahora <= strtotime(FECHA_CIERRE);
}

/** Días que faltan para el cierre; 0 si ya cerró. */
function dias_para_cierre(): int
{
    $diff = strtotime(FECHA_CIERRE) - time();
    return $diff <= 0 ? 0 : (int) ceil($diff / 86400);
}

// --- Programas y estados --------------------------------------------------

function programa_info(string $slug): array
{
    return PROGRAMAS[$slug] ?? ['nombre' => $slug, 'ambito' => '', 'color' => '#8a8178'];
}

function estado_info(string $estado): array
{
    return ESTADOS[$estado] ?? ['label' => $estado, 'color' => '#8a8178'];
}

/**
 * Puntaje ponderado de una postulación (0 a 5).
 * Devuelve null si todavía no fue evaluada en ningún criterio.
 */
function puntaje_ponderado(array $app): ?float
{
    $suma = 0.0;
    $pesos = 0.0;
    $evaluado = false;

    foreach (CRITERIOS as $campo => $def) {
        $valor = (int) ($app[$campo] ?? 0);
        if ($valor > 0) {
            $evaluado = true;
        }
        $suma  += $valor * $def['peso'];
        $pesos += $def['peso'];
    }

    if (!$evaluado || $pesos <= 0) {
        return null;
    }
    return round($suma / $pesos, 2);
}

/** Etiquetas legibles de las respuestas guardadas en application_details. */
const ETIQUETAS_DETALLE = [
    'situacion'             => 'Situación actual',
    'ubicacion'             => 'Dónde está',
    'antiguedad'            => 'Hace cuánto',
    'redes'                 => 'Redes / web',
    'descripcion'           => 'Qué hace hoy',
    'diferencial'           => 'Qué lo hace distinto',
    'visitable'             => 'Qué se puede visitar o mostrar',
    'conexiones'            => 'Con quién se conecta en Esquel',
    'producto_fisico'       => 'Producto físico asociado',
    'producto_fisico_cual'  => 'Detalle del producto físico',
    'recursos'              => 'Con qué cuenta hoy',
    'falta'                 => 'Qué le falta para vender',
    'motivacion'            => 'Por qué quiere participar',
    'equipo'                => 'Quiénes participarían',
    'material'              => 'Material de apoyo',
];

function etiqueta_detalle(string $key): string
{
    return ETIQUETAS_DETALLE[$key] ?? ucfirst(str_replace('_', ' ', $key));
}
