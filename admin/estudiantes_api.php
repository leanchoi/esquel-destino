<?php
/**
 * Endpoints del convenio ISET.
 *
 *   borrador / entregar  — el estudiante, y sólo sobre sus propias consignas
 *   valorar              — el consultor dice si el aporte sirvió
 *   asignar              — el admin cambia a qué emprendimiento va un estudiante
 *
 * Toda la telemetría de escritura que llega desde el navegador es un dato del
 * cliente y se trata como tal: se acota a rangos razonables y jamás se usa
 * para bloquear una entrega. Sirve para que el profesor mire, no para juzgar
 * automáticamente a nadie.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/estudiantes.php';

header('Content-Type: application/json; charset=utf-8');

$u = usuario_actual();
if (!$u) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'error' => 'Sesión no válida.']));
}
registrar_actividad();

$data = json_decode(file_get_contents('php://input'), true) ?: [];
if (!csrf_valido($data['csrf'] ?? null)) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'La sesión expiró. Recargá la página.']));
}

$pdo = db();
$accion = (string) ($data['accion'] ?? '');
$tareaId = (int) ($data['id'] ?? 0);

/** La consigna, sólo si es de quien la pide. */
function tarea_propia(PDO $pdo, int $id, array $u): ?array
{
    $st = $pdo->prepare(
        "SELECT t.* FROM lab_tareas_estudiante t
           JOIN lab_estudiantes e ON e.consultor_id = t.estudiante_id
          WHERE t.id = ? AND e.user_id = ?"
    );
    $st->execute([$id, (int) $u['id']]);
    return $st->fetch() ?: null;
}

// ---------------------------------------------------- guardar una entrega
if ($accion === 'borrador' || $accion === 'entregar') {
    $tarea = tarea_propia($pdo, $tareaId, $u);
    if (!$tarea) {
        http_response_code(404);
        exit(json_encode(['ok' => false, 'error' => 'Esa consigna no es tuya o ya no existe.']));
    }

    $esDurante = $tarea['momento'] === 'durante';
    $especificos = [];
    $texto = '';

    if ($esDurante) {
        foreach (array_keys(ESPECIFICOS_REUNION) as $clave) {
            $v = trim((string) ($data['esp'][$clave] ?? ''));
            $especificos[$clave] = mb_substr($v, 0, 4000);
            $texto .= $especificos[$clave] . "\n";
        }
        $texto = trim($texto);
    } else {
        $texto = trim((string) ($data['entrega'] ?? ''));
        $texto = mb_substr($texto, 0, 20000);
    }

    $chars = mb_strlen($texto);

    // Al entregar (no al guardar borrador) se exigen el mínimo y los
    // específicos. El mínimo no es un capricho: una consigna contestada en dos
    // líneas no le sirve a nadie, y el número está escrito en la pantalla
    // desde el principio para que no sea una sorpresa al final.
    if ($accion === 'entregar') {
        if ($chars < (int) $tarea['min_caracteres']) {
            http_response_code(422);
            exit(json_encode(['ok' => false, 'error' => sprintf(
                'Llevás %d caracteres y hacen falta %d. Guardalo como borrador y seguí cuando puedas.',
                $chars, (int) $tarea['min_caracteres']
            )]));
        }
        if ($esDurante) {
            $chk = especificos_cumplidos($especificos);
            if (!$chk['completo']) {
                $faltan = [];
                foreach ($chk['detalle'] as $d) {
                    if (!$d['ok']) {
                        $faltan[] = sprintf('%s (%d de %d)', $d['label'], $d['trae'], $d['pide']);
                    }
                }
                http_response_code(422);
                exit(json_encode(['ok' => false, 'error' => 'Faltan datos de la reunión: ' . implode(' · ', $faltan)]));
            }
        }
    }

    // Telemetría. Son datos del navegador: se acotan y se suman a lo que ya
    // había, porque una consigna se escribe en varias sentadas.
    $pegados = max(0, min(200000, (int) ($data['pegados'] ?? 0)));
    $segundos = max(0, min(86400, (int) ($data['segundos'] ?? 0)));

    $pdo->prepare(
        "UPDATE lab_tareas_estudiante
            SET entrega = ?, especificos = ?, caracteres = ?,
                caracteres_pegados = ?, segundos_edicion = segundos_edicion + ?,
                sesiones_edicion = sesiones_edicion + 1,
                estado = ?, entregado_at = CASE WHEN ? = 'entregar' THEN datetime('now') ELSE entregado_at END,
                updated_at = datetime('now')
          WHERE id = ?"
    )->execute([
        $texto,
        json_encode($especificos, JSON_UNESCAPED_UNICODE),
        $chars,
        $pegados,
        $segundos,
        $accion === 'entregar' ? 'entregado' : 'pendiente',
        $accion,
        $tareaId,
    ]);

    exit(json_encode([
        'ok' => true,
        'estado' => $accion === 'entregar' ? 'entregado' : 'pendiente',
        'caracteres' => $chars,
        'mensaje' => $accion === 'entregar' ? 'Entregado. Gracias.' : 'Borrador guardado.',
    ], JSON_UNESCAPED_UNICODE));
}

// ------------------------------------------- el consultor valora el aporte
if ($accion === 'valorar') {
    if (!puede_gestionar_lab($u)) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'error' => 'Sólo el equipo consultor valora los aportes.']));
    }
    $valor = (string) ($data['valoracion'] ?? '');
    if (!isset(VALORACIONES_APORTE[$valor])) {
        http_response_code(400);
        exit(json_encode(['ok' => false, 'error' => 'Valoración desconocida.']));
    }

    $pdo->prepare(
        "UPDATE lab_tareas_estudiante
            SET valoracion = ?, valoracion_nota = ?, valorado_por = ?, valorado_at = datetime('now'), estado = 'revisado'
          WHERE id = ?"
    )->execute([
        $valor,
        mb_substr(trim((string) ($data['nota'] ?? '')), 0, 2000),
        (string) $u['username'],
        $tareaId,
    ]);

    exit(json_encode(['ok' => true, 'mensaje' => 'Valoración guardada.']));
}

// --------------------------------------------- el admin reasigna un alumno
if ($accion === 'asignar') {
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'error' => 'Sólo un administrador reasigna estudiantes.']));
    }
    $est = (string) ($data['estudiante'] ?? '');
    $proy = (string) ($data['proyecto'] ?? '');

    $pdo->prepare('UPDATE lab_estudiantes SET proyecto_id = ? WHERE consultor_id = ?')->execute([$proy ?: null, $est]);
    $tocadas = $proy !== '' ? iset_enganchar_reuniones($pdo, $est) : 0;

    exit(json_encode(['ok' => true, 'reuniones' => $tocadas, 'mensaje' => "Reasignado. Se engancharon $tocadas reuniones."]));
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción desconocida.']);
