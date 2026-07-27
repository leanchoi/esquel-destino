<?php
/**
 * Endpoints del panel.
 *
 * Cada acción pide su propio permiso, y no todos son el mismo:
 *   voto     — editor o admin, y sólo sobre su propia evaluación
 *   estado   — admin, y nadie más: mover una postulación de columna es la
 *              decisión del proceso, no una opinión
 *   notas    — editor o admin (la nota del equipo sí es compartida)
 *   eliminar — admin, escribiendo el nombre del proyecto
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/jurado.php';

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

    $votos = evaluaciones_de($pdo, array_column($apps, 'id'));
    $jurado = jurado($pdo);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="postulaciones-esquel-lab-' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel reconozca UTF-8

    // Cabecera: contacto + consolidado del jurado + el voto de cada jurado con
    // nombre y apellido + TODAS las respuestas del formulario.
    $cab = ['ID', 'Recibida', 'Línea', 'Estado', 'Proyecto', 'Responsable', 'Email', 'Teléfono',
            'Puntaje del jurado', 'Votos', 'Abstenciones', 'Jurado completo', 'Falta votar', 'Dispersión'];
    foreach (CRITERIOS as $def) {
        $cab[] = 'Promedio · ' . $def['label'];
    }
    foreach ($jurado as $j) {
        $cab[] = 'Voto · ' . $j['username'];
        $cab[] = 'Comentario · ' . $j['username'];
    }
    foreach (ETIQUETAS_DETALLE as $etq) {
        $cab[] = $etq;
    }
    $cab[] = 'Notas del equipo';
    fputcsv($out, $cab);

    foreach ($apps as $a) {
        $vs = $votos[$a['id']] ?? [];
        $c  = consolidar($vs, $jurado);

        $fila = [
            $a['id'],
            fecha_corta($a['submitted_at'], true),
            programa_info($a['program'])['nombre'],
            estado_info($a['stage'])['label'],
            $a['name'],
            $a['contact_name'],
            $a['email'],
            $a['phone'],
            $c['puntaje'] === null ? '' : number_format($c['puntaje'], 2, ',', ''),
            $c['votos'],
            $c['abstenciones'],
            $c['completo'] ? 'sí' : 'no',
            implode(', ', $c['faltan']),
            $c['dispersion'] === null ? '' : number_format($c['dispersion'], 2, ',', ''),
        ];
        foreach (array_keys(CRITERIOS) as $campo) {
            $prom = $c['promedios'][$campo] ?? null;
            $fila[] = $prom === null ? '' : number_format($prom, 2, ',', '');
        }
        foreach ($jurado as $j) {
            $v = voto_de($vs, $j['id']);
            if (!$v) {
                $fila[] = '';
                $fila[] = '';
            } else {
                $fila[] = $v['abstencion'] ? 'se abstuvo' : number_format($v['puntaje'], 2, ',', '');
                $fila[] = $v['comentario'];
            }
        }
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

$accion = (string) ($data['accion'] ?? '');

/** Devuelve el cuadro del jurado ya recalculado, que es lo que la pantalla repinta. */
function respuesta_jurado(PDO $pdo, int $id, array $u): array
{
    $votos  = evaluaciones_de($pdo, [$id])[$id] ?? [];
    $jurado = jurado($pdo);

    $app = $pdo->prepare('SELECT stage, notes FROM applications WHERE id = ?');
    $app->execute([$id]);
    $fila = $app->fetch() ?: ['stage' => '', 'notes' => ''];

    $hist = $pdo->prepare('SELECT username, stage_from, stage_to, created_at FROM stage_history WHERE application_id = ? ORDER BY created_at DESC');
    $hist->execute([$id]);

    return [
        'ok'          => true,
        'id'          => $id,
        'stage'       => $fila['stage'],
        'notes'       => $fila['notes'],
        'votos'       => $votos,
        'miVoto'      => voto_de($votos, (int) $u['id']),
        'consolidado' => consolidar($votos, $jurado),
        'historial'   => $hist->fetchAll(),
    ];
}

// ------------------------------------------------------------------ borrado
// Solo admin. Es destructivo y no hay papelera: las respuestas de esa persona
// desaparecen. Los detalles y el historial se van por ON DELETE CASCADE.
if ($accion === 'eliminar') {
    if (!puede('admin')) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'error' => 'Solo un administrador puede eliminar postulaciones.']));
    }

    $stmt = $pdo->prepare('SELECT name FROM applications WHERE id = ?');
    $stmt->execute([$id]);
    $nombre = $stmt->fetchColumn();
    if ($nombre === false) {
        http_response_code(404);
        exit(json_encode(['ok' => false, 'error' => 'No encontramos esa postulación.']));
    }

    // Confirmación del lado del servidor: el nombre del proyecto tiene que
    // coincidir. Evita que un borrado se dispare por un click de más.
    if (trim((string) ($data['confirmar'] ?? '')) !== trim((string) $nombre)) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'error' => 'El nombre no coincide. No se eliminó nada.']));
    }

    $del = $pdo->prepare('DELETE FROM applications WHERE id = ?');
    $del->execute([$id]);

    error_log(sprintf(
        'Esquel LAB: %s (%s) eliminó la postulación #%d "%s".',
        $u['username'] ?? '?', $u['role'] ?? '?', $id, $nombre
    ));

    exit(json_encode(['ok' => true, 'eliminada' => $id]));
}

$stmt = $pdo->prepare('SELECT stage FROM applications WHERE id = ?');
$stmt->execute([$id]);
$estadoPrevio = $stmt->fetchColumn();
if ($estadoPrevio === false) {
    http_response_code(404);
    exit(json_encode(['ok' => false, 'error' => 'No encontramos esa postulación.']));
}

// -------------------------------------------------------------------- voto
// Cada jurado escribe únicamente su propia fila. No hay forma de mandar un
// user_id: sale de la sesión, así que nadie puede votar en nombre de otro.
if ($accion === 'voto') {
    $abstencion = !empty($data['abstencion']);

    $valores = [];
    $total = 0;
    foreach (array_keys(CRITERIOS) as $campo) {
        $val = max(0, min(5, (int) ($data[$campo] ?? 0)));
        $valores[$campo] = $abstencion ? 0 : $val;
        $total += $valores[$campo];
    }

    // Un voto en cero para todo baja el promedio del proyecto igual que un voto
    // pensado, y es lo que sale de abrir la ficha, escribir un comentario y
    // guardar sin tocar los deslizadores. Si de verdad querés no puntuar, eso
    // se llama abstención y tiene su propia casilla.
    if (!$abstencion && $total === 0) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'error' => 'Puntuá al menos un criterio, o marcá que te abstenés.']));
    }

    $comentario = trim((string) ($data['comentario'] ?? ''));
    if ($abstencion && $comentario === '') {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'error' => 'Contá por qué te abstenés: el resto del jurado necesita saberlo.']));
    }
    if (mb_strlen($comentario) > 4000) {
        $comentario = mb_substr($comentario, 0, 4000);
    }

    $campos = array_keys(CRITERIOS);
    $lista  = implode(', ', $campos);
    $marcas = implode(', ', array_fill(0, count($campos), '?'));
    $updates = implode(', ', array_map(fn($c) => "$c = excluded.$c", $campos));

    try {
        $sql = "INSERT INTO evaluaciones (application_id, user_id, username, $lista, comentario, abstencion, created_at, updated_at)
                VALUES (?, ?, ?, $marcas, ?, ?, datetime('now'), datetime('now'))
                ON CONFLICT (application_id, user_id) DO UPDATE SET
                  $updates, comentario = excluded.comentario,
                  abstencion = excluded.abstencion, updated_at = datetime('now')";
        $bind = array_merge(
            [$id, (int) $u['id'], (string) $u['username']],
            array_values($valores),
            [$comentario, $abstencion ? 1 : 0]
        );
        $pdo->prepare($sql)->execute($bind);
    } catch (PDOException $ex) {
        error_log('[esquel-lab] error guardando el voto: ' . $ex->getMessage());
        http_response_code(500);
        exit(json_encode(['ok' => false, 'error' => 'No pudimos guardar tu evaluación.']));
    }

    exit(json_encode(respuesta_jurado($pdo, $id, $u), JSON_UNESCAPED_UNICODE));
}

// ------------------------------------------------------------- retirar voto
if ($accion === 'retirar-voto') {
    $del = $pdo->prepare('DELETE FROM evaluaciones WHERE application_id = ? AND user_id = ?');
    $del->execute([$id, (int) $u['id']]);
    exit(json_encode(respuesta_jurado($pdo, $id, $u), JSON_UNESCAPED_UNICODE));
}

// ------------------------------------------------------------------- notas
// La nota del equipo sí es de todos: es la bitácora compartida del caso.
if ($accion === 'notas') {
    $pdo->prepare('UPDATE applications SET notes = ? WHERE id = ?')
        ->execute([trim((string) ($data['notes'] ?? '')), $id]);
    exit(json_encode(respuesta_jurado($pdo, $id, $u), JSON_UNESCAPED_UNICODE));
}

// ------------------------------------------------------------------ estado
// Mover una postulación de columna cierra una etapa del proceso de selección.
// Es una decisión, no una opinión, y por eso queda en manos del admin: un
// editor evalúa y comenta, pero no adelanta a nadie de etapa.
if ($accion === 'estado') {
    if (!puede('admin')) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'error' => 'Solo un administrador puede cambiar el estado de una postulación.']));
    }

    $estadoNuevo = (string) ($data['stage'] ?? '');
    if (!array_key_exists($estadoNuevo, ESTADOS)) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'error' => 'Ese estado no existe.']));
    }

    if ($estadoNuevo !== $estadoPrevio) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE applications SET stage = ? WHERE id = ?')->execute([$estadoNuevo, $id]);
            $pdo->prepare(
                'INSERT INTO stage_history (application_id, user_id, username, stage_from, stage_to) VALUES (?, ?, ?, ?, ?)'
            )->execute([$id, $u['id'], $u['username'], $estadoPrevio, $estadoNuevo]);
            $pdo->commit();
        } catch (PDOException $ex) {
            $pdo->rollBack();
            error_log('[esquel-lab] error cambiando el estado: ' . $ex->getMessage());
            http_response_code(500);
            exit(json_encode(['ok' => false, 'error' => 'No pudimos cambiar el estado.']));
        }
    }

    exit(json_encode(respuesta_jurado($pdo, $id, $u), JSON_UNESCAPED_UNICODE));
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción desconocida.']);
