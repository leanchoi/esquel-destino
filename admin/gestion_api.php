<?php
/**
 * API asíncrona (JSON) para el Módulo de Gestión y Aceleración de Proyectos.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$u = usuario_actual();
if (!$u || !puede_gestionar_lab($u)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No tenés permisos para realizar esta acción.']);
    exit;
}

$pdo = db();
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$token = $data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!csrf_valido($token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Sesión o token CSRF inválido. Recargá la página.']);
    exit;
}

$accion = $data['accion'] ?? '';

try {
    if ($accion === 'guardar_reunion') {
        $id = trim((string) ($data['id'] ?? ''));
        if (!$id) {
            throw new InvalidArgumentException('Falta ID de la reunión.');
        }

        $fecha = trim((string) ($data['fecha'] ?? ''));
        $horaInicio = trim((string) ($data['hora_inicio'] ?? ''));
        $horaFin = trim((string) ($data['hora_fin'] ?? ''));
        $lugar = trim((string) ($data['lugar'] ?? ''));
        $tipo = trim((string) ($data['tipo'] ?? 'ind'));
        $estado = trim((string) ($data['estado'] ?? 'programada'));
        $asistenciaEstado = (int) ($data['asistencia_estado'] ?? 0);
        $horaRealInicio = trim((string) ($data['hora_real_inicio'] ?? ''));
        $horaRealFin = trim((string) ($data['hora_real_fin'] ?? ''));
        $minutaNotas = (string) ($data['minuta_notas'] ?? '');
        $asistentes = is_array($data['asistentes'] ?? null) ? $data['asistentes'] : [];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE lab_reuniones
            SET fecha = ?, hora_inicio = ?, hora_fin = ?, lugar = ?, tipo = ?, estado = ?,
                asistencia_estado = ?, hora_real_inicio = ?, hora_real_fin = ?,
                minuta_notas = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([
            $fecha, $horaInicio, $horaFin, $lugar, $tipo, $estado,
            $asistenciaEstado, $horaRealInicio, $horaRealFin,
            $minutaNotas, $id
        ]);

        // Actualizar asistentes
        $pdo->prepare("DELETE FROM lab_reunion_asistentes WHERE reunion_id = ?")->execute([$id]);
        $insAsist = $pdo->prepare("INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol) VALUES (?, ?, 'asistente')");
        foreach ($asistentes as $consId) {
            $consId = trim((string) $consId);
            if ($consId) {
                $insAsist->execute([$id, $consId]);
            }
        }

        $pdo->commit();

        echo json_encode(['ok' => true, 'mensaje' => 'Reunión actualizada correctamente.']);
        exit;
    }

    if ($accion === 'toggle_checklist') {
        $id = trim((string) ($data['reunion_id'] ?? ''));
        $checkId = (int) ($data['check_id'] ?? 0);
        $done = !empty($data['done']);

        $stmt = $pdo->prepare("SELECT checklist FROM lab_reuniones WHERE id = ?");
        $stmt->execute([$id]);
        $json = $stmt->fetchColumn();
        if ($json === false) {
            throw new InvalidArgumentException('Reunión no encontrada.');
        }

        $items = json_decode($json, true) ?: [];
        $modificado = false;
        foreach ($items as &$it) {
            if ((int) ($it['id'] ?? 0) === $checkId) {
                $it['done'] = $done;
                $it['done_at'] = $done ? date('Y-m-d H:i:s') : null;
                $it['done_by'] = $done ? ($u['username'] ?? 'usuario') : null;
                $modificado = true;
                break;
            }
        }
        unset($it);

        if ($modificado) {
            $upd = $pdo->prepare("UPDATE lab_reuniones SET checklist = ?, updated_at = datetime('now') WHERE id = ?");
            $upd->execute([json_encode($items, JSON_UNESCAPED_UNICODE), $id]);
        }

        echo json_encode(['ok' => true, 'checklist' => $items]);
        exit;
    }

    if ($accion === 'guardar_notas') {
        $id = trim((string) ($data['reunion_id'] ?? ''));
        $notas = (string) ($data['notas'] ?? '');
        $stmt = $pdo->prepare("UPDATE lab_reuniones SET minuta_notas = ?, updated_at = datetime('now') WHERE id = ?");
        $stmt->execute([$notas, $id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'agregar_compromiso') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        $reunionId = trim((string) ($data['reunion_id'] ?? '')) ?: null;
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $responsable = trim((string) ($data['responsable'] ?? 'Emprendedor'));
        $fechaLimite = trim((string) ($data['fecha_limite'] ?? ''));

        if (!$proyectoId || !$descripcion) {
            throw new InvalidArgumentException('Faltan datos para crear el compromiso.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO lab_compromisos (proyecto_id, reunion_id, descripcion, responsable, fecha_limite, estado)
            VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");
        $stmt->execute([$proyectoId, $reunionId, $descripcion, $responsable, $fechaLimite]);
        $nuevoId = (int) $pdo->lastInsertId();

        echo json_encode([
            'ok' => true,
            'compromiso' => [
                'id' => $nuevoId,
                'proyecto_id' => $proyectoId,
                'reunion_id' => $reunionId,
                'descripcion' => $descripcion,
                'responsable' => $responsable,
                'fecha_limite' => $fechaLimite,
                'estado' => 'pendiente'
            ]
        ]);
        exit;
    }

    if ($accion === 'toggle_compromiso') {
        $id = (int) ($data['id'] ?? 0);
        $estado = trim((string) ($data['estado'] ?? 'pendiente'));
        $completadoAt = $estado === 'cumplido' ? date('Y-m-d H:i:s') : null;

        $stmt = $pdo->prepare("UPDATE lab_compromisos SET estado = ?, completado_at = ? WHERE id = ?");
        $stmt->execute([$estado, $completadoAt, $id]);

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'guardar_plan_proyecto') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        $notas = (string) ($data['notas_generales'] ?? '');
        $estadoAcomp = trim((string) ($data['estado_acompanamiento'] ?? 'En curso'));

        $stmt = $pdo->prepare("
            UPDATE lab_proyectos
            SET notas_generales = ?, estado_acompanamiento = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$notas, $estadoAcomp, $proyectoId]);

        echo json_encode(['ok' => true]);
        exit;
    }

    throw new InvalidArgumentException('Acción no reconocida.');

} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $ex->getMessage()]);
    exit;
}
