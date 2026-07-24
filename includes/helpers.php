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
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
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

function fecha_larga(string $fecha): string
{
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($fecha);
    return (int) date('j', $ts) . ' de ' . $meses[(int) date('n', $ts)];
}

function fecha_corta(string $fecha, bool $conHora = false): string
{
    $ts = strtotime($fecha);
    return date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $ts);
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
