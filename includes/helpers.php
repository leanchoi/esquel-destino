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
    'dni'                   => 'DNI',
    'barrio'                => 'Barrio o paraje',
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

/**
 * Todas las claves que hay que mostrar de una postulación.
 *
 * Primero las del formulario de hoy, en su orden, y después cualquier otra que
 * la postulación traiga guardada.
 *
 * Esa segunda parte no es un detalle. application_details guarda clave-valor,
 * así que una respuesta no se borra nunca cuando la pregunta sale del
 * formulario: se queda en la base. Pero el panel mostraba sólo las claves de
 * ETIQUETAS_DETALLE, con lo cual, al sacar una pregunta, lo que había
 * contestado la gente dejaba de verse aunque siguiera ahí. Ahora se listan
 * todas, y las que ya no se preguntan quedan marcadas como tales en vez de
 * desaparecer.
 *
 * @param array $detalles  clave => valor de esa postulación
 * @return array           clave => ['label' => string, 'vigente' => bool]
 */
function claves_detalle(array $detalles): array
{
    $out = [];
    foreach (ETIQUETAS_DETALLE as $k => $label) {
        $out[$k] = ['label' => $label, 'vigente' => true];
    }
    foreach (array_keys($detalles) as $k) {
        if (!isset($out[$k])) {
            $out[$k] = ['label' => etiqueta_detalle($k), 'vigente' => false];
        }
    }
    return $out;
}

/**
 * Deja el DNI en dígitos pelados.
 *
 * La gente lo escribe como lo tiene en la cabeza: con puntos, con espacios, a
 * veces con "DNI" adelante. Guardar eso tal cual significa que después el
 * mismo documento aparece de tres formas distintas y no se puede buscar ni
 * cruzar. Se normaliza al entrar, una sola vez.
 */
function normalizar_dni(string $crudo): string
{
    return preg_replace('/\D+/', '', $crudo) ?? '';
}

/**
 * Si el número puede ser un DNI argentino, devuelve ''. Si no, el motivo.
 *
 * El rango va de 7 a 9 dígitos a propósito. Los que se emiten hoy tienen 8,
 * pero alguien de 70 años puede tener 7, y el DNI de un residente extranjero
 * —en Esquel hay chilenos con años en la ciudad— llega a 9. Apretar esto a 8
 * dígitos deja afuera gente que tiene todo el derecho a postularse. Lo que sí
 * atrapa es el error de tipeo, que es para lo que sirve validar.
 */
function error_dni(string $dni): string
{
    if ($dni === '') {
        return 'Poné tu DNI, sin puntos.';
    }
    $largo = strlen($dni);
    if ($largo < 7 || $largo > 9) {
        return 'Ese DNI no parece completo. Van entre 7 y 9 números, sin puntos ni espacios.';
    }
    if ((int) $dni === 0) {
        return 'Revisá el DNI.';
    }
    return '';
}

/**
 * Los campos de la lista de espera, una sola vez.
 *
 * Los mismos campos aparecen en tres lugares: la página avisame.php, el bloque
 * de la home y el pop-up. Escribirlos tres veces es garantizar que en algún
 * momento uno pida algo que los otros no, y que la validación del servidor
 * rechace lo que la pantalla dejó pasar. Se escriben acá y se usan en los tres.
 *
 * El prefijo existe porque el pop-up y el bloque de la home conviven en la
 * misma página: sin él habría dos elementos con el mismo id y el <label> del
 * segundo enfocaría el campo del primero.
 *
 * @param array  $v        valores actuales (para no perderlos si algo falla)
 * @param array  $errores  clave => mensaje
 * @param string $prefijo  para que los id no choquen entre instancias
 */
function campos_avisame(array $v, array $errores, string $prefijo): string
{
    $id = fn(string $c) => $prefijo . '-' . $c;
    $val = fn(string $c) => e($v[$c] ?? '');
    $err = fn(string $c) => isset($errores[$c])
        ? '<p class="err">' . e($errores[$c]) . '</p>'
        : '';

    $pistas = '';
    foreach (PISTAS_PROYECTO as $pista) {
        $pistas .= '<li>' . e($pista) . '</li>';
    }

    $lineas = '';
    foreach (PROGRAMAS as $slug => $p) {
        $marcado = ($v['linea'] ?? '') === $slug ? ' selected' : '';
        $lineas .= '<option value="' . e($slug) . '"' . $marcado . '>' . e($p['nombre']) . ' — ' . e($p['resumen']) . '</option>';
    }

    return '
      <div class="field">
        <label class="lbl" for="' . $id('nombre') . '">Tu nombre *</label>
        <input type="text" id="' . $id('nombre') . '" name="nombre" required autocomplete="name" value="' . $val('nombre') . '">
        ' . $err('nombre') . '
      </div>
      <div class="field-row">
        <div class="field">
          <label class="lbl" for="' . $id('email') . '">Correo electrónico *</label>
          <input type="email" id="' . $id('email') . '" name="email" required autocomplete="email"
                 inputmode="email" spellcheck="false" value="' . $val('email') . '">
          ' . $err('email') . '
        </div>
        <div class="field">
          <label class="lbl" for="' . $id('telefono') . '">WhatsApp <span class="lbl-opt">opcional</span></label>
          <input type="tel" id="' . $id('telefono') . '" name="telefono" autocomplete="tel"
                 inputmode="tel" placeholder="2945 123456" value="' . $val('telefono') . '">
        </div>
      </div>
      <div class="field">
        <label class="lbl" for="' . $id('linea') . '">¿Por dónde va lo tuyo? <span class="lbl-opt">opcional</span></label>
        <select id="' . $id('linea') . '" name="linea">
          <option value="">Todavía no sé</option>
          ' . $lineas . '
        </select>
      </div>
      <div class="field">
        <label class="lbl" for="' . $id('instagram') . '">Instagram <span class="lbl-opt">opcional</span></label>
        <div class="campo-arroba">
          <span aria-hidden="true">@</span>
          <input type="text" id="' . $id('instagram') . '" name="instagram" autocomplete="off"
                 spellcheck="false" placeholder="tuproyecto" value="' . $val('instagram') . '">
        </div>
        <p class="hint">Si tenés cuenta del proyecto, nos deja ver lo que hacés antes de escribirte.</p>
      </div>
      <div class="field">
        <label class="lbl" for="' . $id('cuenta') . '">Contanos de tu proyecto o de tu idea <span class="lbl-opt">opcional</span></label>
        <p class="hint">Lo que quieras. Si no sabés por dónde empezar, alguna de estas:</p>
        <ul class="pistas">' . $pistas . '</ul>
        <textarea id="' . $id('cuenta') . '" name="cuenta" rows="5"
                  placeholder="Ej.: tengo una chacra con frutas finas a 4 km del centro. Hacemos dulces caseros y me gustaría que la gente pueda venir a ver la cosecha y llevarse un frasco.">' . $val('cuenta') . '</textarea>
      </div>
      <p class="visually-hidden" aria-hidden="true">
        <label for="' . $id('sitio') . '">No completar</label>
        <input type="text" id="' . $id('sitio') . '" name="sitio_web" tabindex="-1" autocomplete="off">
      </p>';
}
