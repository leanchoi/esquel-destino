<?php
/**
 * API asíncrona (JSON) para el Módulo de Gestión y Aceleración de Proyectos.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/estudiantes.php';

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

        // Vincular al estudiante del proyecto si la fecha es a partir de su alta
        $stEst = $pdo->prepare("
            SELECT e.consultor_id
            FROM lab_reuniones r
            JOIN lab_estudiantes e ON e.proyecto_id = r.proyecto_id AND e.activo = 1
            WHERE r.id = ? AND (e.alta_desde IS NULL OR e.alta_desde = '' OR ? >= e.alta_desde)
        ");
        $stEst->execute([$id, $fecha]);
        $estId = $stEst->fetchColumn();
        if ($estId) {
            $insAsistEst = $pdo->prepare("INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol, asistio) VALUES (?, ?, 'estudiante', 1)");
            $insAsistEst->execute([$id, $estId]);
        }

        $pdo->commit();

        // Regenerar o recalcular vencimientos de consignas para el estudiante
        generar_consignas_reunion($pdo, $id);

        echo json_encode(['ok' => true, 'mensaje' => 'Reunión actualizada correctamente.']);
        exit;
    }

    if ($accion === 'crear_reunion') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        if (!$proyectoId) {
            throw new InvalidArgumentException('Falta ID del proyecto.');
        }

        $numero = (int) ($data['numero_reunion'] ?? 0);
        if ($numero <= 0) {
            $stMax = $pdo->prepare("SELECT COALESCE(MAX(numero_reunion), 0) + 1 FROM lab_reuniones WHERE proyecto_id = ?");
            $stMax->execute([$proyectoId]);
            $numero = (int) $stMax->fetchColumn();
        }

        $id = trim((string) ($data['id'] ?? ''));
        if (!$id) {
            $id = $proyectoId . '-r' . $numero;
        }

        $titulo = trim((string) ($data['titulo'] ?? ("Encuentro #" . $numero)));
        $fecha = trim((string) ($data['fecha'] ?? date('Y-m-d')));
        $horaInicio = trim((string) ($data['hora_inicio'] ?? '10:00'));
        $horaFin = trim((string) ($data['hora_fin'] ?? '12:00'));
        $lugar = trim((string) ($data['lugar'] ?? 'Oficina Esquel LAB'));
        $tipo = trim((string) ($data['tipo'] ?? 'ind'));
        $asistentes = is_array($data['asistentes'] ?? null) ? $data['asistentes'] : [];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO lab_reuniones (id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin, estado, guia_consultor, preguntas_clave, objetivos, checklist, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'programada', '', '[]', '[]', '[]', datetime('now'), datetime('now'))
        ");
        $stmt->execute([$id, $proyectoId, $numero, $titulo, $tipo, $lugar, $fecha, $horaInicio, $horaFin]);

        $insAsist = $pdo->prepare("INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol) VALUES (?, ?, 'asistente')");
        foreach ($asistentes as $consId) {
            $consId = trim((string) $consId);
            if ($consId) {
                $insAsist->execute([$id, $consId]);
            }
        }

        // Sumar al estudiante si fecha >= alta_desde
        $stEst = $pdo->prepare("
            SELECT e.consultor_id
            FROM lab_estudiantes e
            WHERE e.proyecto_id = ? AND e.activo = 1 AND (e.alta_desde IS NULL OR e.alta_desde = '' OR ? >= e.alta_desde)
        ");
        $stEst->execute([$proyectoId, $fecha]);
        $estId = $stEst->fetchColumn();
        if ($estId) {
            $insAsistEst = $pdo->prepare("INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol, asistio) VALUES (?, ?, 'estudiante', 1)");
            $insAsistEst->execute([$id, $estId]);
        }

        $pdo->commit();

        generar_consignas_reunion($pdo, $id);

        echo json_encode(['ok' => true, 'reunion_id' => $id, 'mensaje' => 'Reunión creada correctamente.']);
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

    if ($accion === 'guardar_minuta_cero') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        $minutaCero = $data['minuta_cero'] ?? '';
        if (is_array($minutaCero)) {
            $minutaCero = json_encode($minutaCero, JSON_UNESCAPED_UNICODE);
        } else {
            $minutaCero = (string) $minutaCero;
        }

        $stmtSel = $pdo->prepare("SELECT minuta_cero FROM lab_proyectos WHERE id = ?");
        $stmtSel->execute([$proyectoId]);
        $anterior = (string) $stmtSel->fetchColumn();

        if (trim($anterior) !== trim($minutaCero)) {
            $uNombre = !empty($u['nombre']) ? $u['nombre'] : ($u['username'] ?? 'usuario');
            $uId = !empty($u['id']) ? (int)$u['id'] : null;
            $insRev = $pdo->prepare("
                INSERT INTO lab_proyectos_revisiones (proyecto_id, campo, contenido_anterior, contenido_nuevo, usuario_id, usuario_nombre, motivo, created_at)
                VALUES (?, 'minuta_cero', ?, ?, ?, ?, 'Edición de Minuta Cero', datetime('now'))
            ");
            $insRev->execute([$proyectoId, $anterior, $minutaCero, $uId, $uNombre]);
        }

        $stmt = $pdo->prepare("
            UPDATE lab_proyectos
            SET minuta_cero = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$minutaCero, $proyectoId]);

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'guardar_componente_proyecto') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        $campo = trim((string) ($data['campo'] ?? ''));
        $motivo = trim((string) ($data['motivo'] ?? ''));

        $camposPermitidos = ['diagnostico', 'trabas', 'ejes', 'entregables', 'notas_generales', 'minuta_cero'];
        if (!in_array($campo, $camposPermitidos, true)) {
            throw new InvalidArgumentException('Campo no permitido para edición contextual.');
        }
        if (!$proyectoId) {
            throw new InvalidArgumentException('Falta ID del proyecto.');
        }

        $stmtSel = $pdo->prepare("SELECT $campo FROM lab_proyectos WHERE id = ?");
        $stmtSel->execute([$proyectoId]);
        $anterior = $stmtSel->fetchColumn();
        if ($anterior === false) {
            throw new InvalidArgumentException('Proyecto no encontrado.');
        }

        $nuevoRaw = $data['contenido'] ?? '';
        if (is_array($nuevoRaw)) {
            $nuevo = json_encode($nuevoRaw, JSON_UNESCAPED_UNICODE);
        } else {
            $nuevo = (string) $nuevoRaw;
        }

        $cambioReal = (trim((string)$anterior) !== trim((string)$nuevo));

        $pdo->beginTransaction();

        if ($cambioReal) {
            $insRev = $pdo->prepare("
                INSERT INTO lab_proyectos_revisiones (proyecto_id, campo, contenido_anterior, contenido_nuevo, usuario_id, usuario_nombre, motivo, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
            ");
            $uNombre = !empty($u['nombre']) ? $u['nombre'] : ($u['username'] ?? 'usuario');
            $uId = !empty($u['id']) ? (int)$u['id'] : null;
            $insRev->execute([$proyectoId, $campo, (string)$anterior, (string)$nuevo, $uId, $uNombre, $motivo]);

            $stmtUpd = $pdo->prepare("UPDATE lab_proyectos SET $campo = ?, updated_at = datetime('now') WHERE id = ?");
            $stmtUpd->execute([$nuevo, $proyectoId]);
        }

        $pdo->commit();

        $contenidoDecoded = in_array($campo, ['diagnostico', 'trabas', 'ejes', 'entregables'], true)
            ? (json_decode($nuevo, true) ?: [])
            : ($campo === 'minuta_cero' ? (json_decode($nuevo, true) ?: $nuevo) : $nuevo);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Componente guardado correctamente.',
            'campo' => $campo,
            'contenido' => $contenidoDecoded,
            'cambio_registrado' => $cambioReal
        ]);
        exit;
    }

    if ($accion === 'obtener_revisiones_componente') {
        $proyectoId = trim((string) ($data['proyecto_id'] ?? ''));
        $campo = trim((string) ($data['campo'] ?? ''));

        if (!$proyectoId || !$campo) {
            throw new InvalidArgumentException('Faltan parámetros requeridos.');
        }

        $stmt = $pdo->prepare("
            SELECT id, proyecto_id, campo, usuario_nombre, motivo, created_at, contenido_anterior, contenido_nuevo
            FROM lab_proyectos_revisiones
            WHERE proyecto_id = ? AND campo = ?
            ORDER BY id DESC
            LIMIT 50
        ");
        $stmt->execute([$proyectoId, $campo]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok' => true,
            'revisiones' => $rows
        ]);
        exit;
    }

    if ($accion === 'restaurar_revision_componente') {
        $revisionId = (int) ($data['revision_id'] ?? 0);
        if (!$revisionId) {
            throw new InvalidArgumentException('Falta ID de revisión.');
        }

        $stmtRev = $pdo->prepare("SELECT * FROM lab_proyectos_revisiones WHERE id = ?");
        $stmtRev->execute([$revisionId]);
        $rev = $stmtRev->fetch(PDO::FETCH_ASSOC);
        if (!$rev) {
            throw new InvalidArgumentException('Revisión no encontrada.');
        }

        $proyectoId = $rev['proyecto_id'];
        $campo = $rev['campo'];
        $camposPermitidos = ['diagnostico', 'trabas', 'ejes', 'entregables', 'notas_generales', 'minuta_cero'];
        if (!in_array($campo, $camposPermitidos, true)) {
            throw new InvalidArgumentException('Campo no válido.');
        }

        $stmtSel = $pdo->prepare("SELECT $campo FROM lab_proyectos WHERE id = ?");
        $stmtSel->execute([$proyectoId]);
        $actual = $stmtSel->fetchColumn();

        $aRestaurar = $rev['contenido_nuevo'];

        $pdo->beginTransaction();

        $uNombre = !empty($u['nombre']) ? $u['nombre'] : ($u['username'] ?? 'usuario');
        $uId = !empty($u['id']) ? (int)$u['id'] : null;
        $motivo = "Restauración a la versión #" . $rev['id'] . " (" . $rev['created_at'] . " por " . $rev['usuario_nombre'] . ")";

        $insRev = $pdo->prepare("
            INSERT INTO lab_proyectos_revisiones (proyecto_id, campo, contenido_anterior, contenido_nuevo, usuario_id, usuario_nombre, motivo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
        ");
        $insRev->execute([$proyectoId, $campo, (string)$actual, (string)$aRestaurar, $uId, $uNombre, $motivo]);

        $stmtUpd = $pdo->prepare("UPDATE lab_proyectos SET $campo = ?, updated_at = datetime('now') WHERE id = ?");
        $stmtUpd->execute([$aRestaurar, $proyectoId]);

        $pdo->commit();

        $contenidoDecoded = in_array($campo, ['diagnostico', 'trabas', 'ejes', 'entregables'], true)
            ? (json_decode($aRestaurar, true) ?: [])
            : ($campo === 'minuta_cero' ? (json_decode($aRestaurar, true) ?: $aRestaurar) : $aRestaurar);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Versión restaurada con éxito.',
            'campo' => $campo,
            'contenido' => $contenidoDecoded
        ]);
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
