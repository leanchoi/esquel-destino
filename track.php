<?php
/**
 * Recibe el "beacon" del navegador al cerrar la página: cuántos segundos
 * estuvo, hasta qué porcentaje bajó y, en el formulario, hasta qué paso llegó.
 *
 * Sólo actualiza la fila de la visita que el propio servidor creó, y hace
 * falta el token HMAC que se emitió con ella. Sin eso no se puede escribir
 * nada desde afuera.
 */
require_once __DIR__ . '/includes/analitica.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('{"ok":false}');
}

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    http_response_code(400);
    exit('{"ok":false}');
}

$id    = (int) ($datos['id'] ?? 0);
$token = (string) ($datos['t'] ?? '');

if ($id <= 0 || !hash_equals(analitica_token($id), $token)) {
    http_response_code(403);
    exit('{"ok":false}');
}

// Techos razonables: una pestaña abierta toda la noche no es tiempo de lectura.
$segundos    = max(0, min(1800, (int) ($datos['s'] ?? 0)));
$profundidad = max(0, min(100, (int) ($datos['p'] ?? 0)));
$paso        = isset($datos['f']) ? max(1, min(6, (int) $datos['f'])) : null;

try {
    // La ventana de una hora evita que se reescriban visitas viejas.
    db()->prepare(
        "UPDATE visitas
            SET segundos = ?, profundidad = ?, paso_form = COALESCE(?, paso_form)
          WHERE id = ? AND creada_at > datetime('now', '-1 hour')"
    )->execute([$segundos, $profundidad, $paso, $id]);
} catch (Throwable $e) {
    error_log('Esquel LAB track: ' . $e->getMessage());
}

echo '{"ok":true}';
