<?php
/**
 * Endpoints del panel: exportación CSV y actualización de la evaluación.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

iniciar_sesion();
$u = usuario_actual();

if (!$u) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(['ok' => false, 'error' => 'Sesión no válida.']));
}
if (!empty($u['must_change'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(['ok' => false, 'error' => 'Cambiá la contraseña provisoria antes de operar el panel.']));
}

$pdo = db();

// ------------------------------------------------------------- exportación
if (($_GET['export'] ?? '') === 'csv') {
    $filtros = [
        'program' => $_GET['program'] ?? '',
        'stage'   => $_GET['stage'] ?? '',
        'q'       => trim((string) ($_GET['q'] ?? '')),
    ];

    $sql = 'SELECT * FROM applications WHERE 1=1';
    $params = [];
    if (array_key_exists($filtros['program'], PROGRAMAS)) { $sql .= ' AND program = ?'; $params[] = $filtros['program']; }
    if (array_key_exists($filtros['stage'], ESTADOS))     { $sql .= ' AND stage = ?';   $params[] = $filtros['stage']; }
    if ($filtros['q'] !== '') {
        $sql .= ' AND (name LIKE ? OR contact_name LIKE ? OR email LIKE ?)';
        $like = '%' . $filtros['q'] . '%';
        array_push($params, $like, $like, $like);
    }
    $sql .= ' ORDER BY submitted_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $apps = $stmt->fetchAll();

    $detalles = [];
    if ($apps) {
        $ids = array_column($apps, 'id');
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $d = $pdo->prepare("SELECT * FROM application_details WHERE application_id IN ($ph)");
        $d->execute($ids);
        foreach ($d->fetchAll() as $row) {
            $detalles[$row['application_id']][$row['field_key']] = $row['field_value'];
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="postulaciones-esquel-lab-' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel reconozca UTF-8

    // Cabecera: contacto + evaluación + TODAS las respuestas del formulario.
    $cab = ['ID', 'Recibida', 'Línea', 'Estado', 'Proyecto', 'Responsable', 'Email', 'Teléfono'];
    foreach (CRITERIOS as $def) {
        $cab[] = $def['label'] . ' (0-5)';
    }
    $cab[] = 'Puntaje ponderado';
    foreach (ETIQUETAS_DETALLE as $etq) {
        $cab[] = $etq;
    }
    $cab[] = 'Notas del equipo';
    fputcsv($out, $cab);

    foreach ($apps as $a) {
        $fila = [
            $a['id'],
            fecha_corta($a['submitted_at'], true),
            programa_info($a['program'])['nombre'],
            estado_info($a['stage'])['label'],
            $a['name'],
            $a['contact_name'],
            $a['email'],
            $a['phone'],
        ];
        foreach (CRITERIOS as $campo => $def) {
            $fila[] = (int) ($a[$campo] ?? 0);
        }
        $p = puntaje_ponderado($a);
        $fila[] = $p === null ? '' : number_format($p, 2, ',', '');
        foreach (array_keys(ETIQUETAS_DETALLE) as $key) {
            $fila[] = $detalles[$a['id']][$key] ?? '';
        }
        $fila[] = $a['notes'];
        fputcsv($out, $fila);
    }

    fclose($out);
    exit;
}

// ------------------------------------------------------------ actualización
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'error' => 'Método no permitido.']));
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Petición inválida.']));
}

if (!csrf_valido($data['csrf'] ?? null)) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'La sesión expiró. Recargá la página.']));
}

if (!puede('editor')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'Tu rol es de solo lectura.']));
}

$id = (int) ($data['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Postulación no válida.']));
}

$stmt = $pdo->prepare('SELECT stage FROM applications WHERE id = ?');
$stmt->execute([$id]);
$estadoPrevio = $stmt->fetchColumn();
if ($estadoPrevio === false) {
    http_response_code(404);
    exit(json_encode(['ok' => false, 'error' => 'No encontramos esa postulación.']));
}

$estadoNuevo = (string) ($data['stage'] ?? $estadoPrevio);
if (!array_key_exists($estadoNuevo, ESTADOS)) {
    $estadoNuevo = $estadoPrevio;
}

$sets = ['stage = :stage', 'notes = :notes'];
$bind = [
    ':stage' => $estadoNuevo,
    ':notes' => trim((string) ($data['notes'] ?? '')),
    ':id'    => $id,
];
foreach (array_keys(CRITERIOS) as $campo) {
    $val = (int) ($data[$campo] ?? 0);
    $val = max(0, min(5, $val));
    $sets[] = "$campo = :$campo";
    $bind[':' . $campo] = $val;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE applications SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($bind);

    if ($estadoNuevo !== $estadoPrevio) {
        $pdo->prepare(
            'INSERT INTO stage_history (application_id, user_id, username, stage_from, stage_to) VALUES (?, ?, ?, ?, ?)'
        )->execute([$id, $u['id'], $u['username'], $estadoPrevio, $estadoNuevo]);
    }
    $pdo->commit();
} catch (PDOException $ex) {
    $pdo->rollBack();
    error_log('[esquel-lab] error actualizando postulación: ' . $ex->getMessage());
    http_response_code(500);
    exit(json_encode(['ok' => false, 'error' => 'No pudimos guardar los cambios.']));
}

// Recalculamos el puntaje con los valores recién guardados.
$stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ?');
$stmt->execute([$id]);
$app = $stmt->fetch();

$hist = $pdo->prepare('SELECT username, stage_from, stage_to, created_at FROM stage_history WHERE application_id = ? ORDER BY created_at DESC');
$hist->execute([$id]);

echo json_encode([
    'ok'       => true,
    'puntaje'  => puntaje_ponderado($app),
    'stage'    => $app['stage'],
    'historial' => $hist->fetchAll(),
], JSON_UNESCAPED_UNICODE);
