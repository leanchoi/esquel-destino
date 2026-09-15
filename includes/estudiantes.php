<?php
/**
 * Convenio ISET 815 — los estudiantes dentro del acompañamiento.
 *
 * ── El problema que este archivo resuelve ──────────────────────────────────
 *
 * Son 18 estudiantes, uno por emprendimiento, con unas 60 horas cada uno y
 * siete semanas por delante. Eso puede salir de dos maneras:
 *
 *   a) cada emprendedor termina mucho mejor acompañado, o
 *   b) el equipo termina con 18 personas más que administrar y una pila de
 *      textos escritos para aprobar una materia.
 *
 * Todo lo que sigue está diseñado para forzar (a). La idea central es una
 * sola: lo que el estudiante produce tiene que ser algo que el consultor
 * NECESITA antes de la próxima reunión. Si el senior depende de ese material,
 * lo lee; y si lo lee, la calidad se controla sola, por el trabajo y no por
 * un contador de caracteres.
 *
 * ── Por qué las consignas son así ─────────────────────────────────────────
 *
 * No se pide un resumen de la reunión. Un resumen lo escribe cualquiera —o
 * cualquier cosa— sin haber estado. Se piden tres materiales distintos:
 *
 *   ANTES    Un insumo de preparación que el senior lee antes de entrar.
 *            Precios de experiencias comparables, normativa que aplica,
 *            proveedores de la zona. Investigación real y acotada.
 *
 *   DURANTE  Lo que sólo tiene quien estuvo en la sala: frases textuales con
 *            su autor, números concretos que se dijeron, algo que contradice
 *            lo que ya teníamos anotado, algo que sorprendió.
 *
 *   DESPUÉS  Un entregable que mueve el proyecto un paso. Una tabla de
 *            precios de la competencia, un borrador de la ficha de venta, un
 *            inventario de fotos que faltan. No un relato de lo que pasó.
 *
 * Ese "DURANTE" es además el control real contra el texto generado: no hay
 * detector que funcione, pero sí hay consignas que no se pueden contestar sin
 * haber participado. Las frases y los números se contrastan contra la minuta
 * del consultor que estuvo ahí. Eso se verifica; un detector, no.
 */

require_once __DIR__ . '/db.php';

/** Momentos de una consigna, en el orden en que ocurren. */
const MOMENTOS_ESTUDIANTE = [
    'antes'      => ['label' => 'Antes de la reunión',  'ayuda' => 'Insumo de preparación para el consultor.'],
    'durante'    => ['label' => 'Durante la reunión',   'ayuda' => 'Lo que sólo se tiene habiendo estado.'],
    'despues'    => ['label' => 'Después de la reunión', 'ayuda' => 'Un entregable que mueve el proyecto.'],
    'intermedia' => ['label' => 'Entre reuniones',      'ayuda' => 'Investigación de fondo, sin reunión de por medio.'],
];

/** Cómo valora el consultor el aporte. Es el juicio que de verdad pesa. */
const VALORACIONES_APORTE = [
    'sirvio'  => ['label' => 'Sirvió',            'peso' => 1.0],
    'parcial' => ['label' => 'Sirvió a medias',   'peso' => 0.5],
    'rehacer' => ['label' => 'Hay que rehacerlo', 'peso' => 0.0],
];

/**
 * El "DURANTE": los específicos que se piden en la sala.
 *
 * Son cuatro campos y ninguno se puede inventar de manera creíble, porque el
 * consultor que estuvo en la reunión los ve al lado de su propia minuta. Ese
 * contraste es lo que hace honesto el sistema.
 */
/**
 * Largo mínimo de un ítem para que cuente como tal.
 *
 * No mide calidad —nada la mide automáticamente— pero corta el caso obvio: dos
 * letras y un Enter no son dos frases del emprendedor.
 */
const MINIMO_ITEM_ESPECIFICO = 15;

const ESPECIFICOS_REUNION = [
    'citas' => [
        'label'  => 'Frases textuales del emprendedor',
        'ayuda'  => 'Al menos dos, entre comillas y con quién la dijo. No las parafrasees: la forma en que lo dice es información.',
        'minimo' => 2,
    ],
    'numeros' => [
        'label'  => 'Números concretos que se dijeron',
        'ayuda'  => 'Precios, cantidades, distancias, tiempos, capacidad. Cada uno con qué mide y quién lo dijo.',
        'minimo' => 2,
    ],
    'contradice' => [
        'label'  => 'Algo que contradice lo que teníamos anotado',
        'ayuda'  => 'Mirá la ficha del proyecto antes de entrar. Si todo coincidió, escribí "nada" y explicá qué revisaste.',
        'minimo' => 1,
    ],
    'sorpresa' => [
        'label'  => 'Algo que te sorprendió y por qué',
        'ayuda'  => 'Lo que no esperabas. Suele ser la punta de algo que el equipo todavía no vio.',
        'minimo' => 1,
    ],
];

/**
 * Plantillas de consigna por número de reunión.
 *
 * El estudiante entra después de FIT, con el acompañamiento ya empezado, y
 * puede no haber estado en las reuniones anteriores ni conocer al emprendedor.
 * Por eso cada consigna se explica sola: dice qué hacer, con qué formato y
 * para qué le sirve al equipo. No hace falta haber estado antes.
 *
 * Las horas estimadas cierran el presupuesto: 60 horas por estudiante entre
 * reuniones, preparación y entregables.
 */
function plantillas_consignas(): array
{
    return [
        'antes' => [
            'titulo'  => 'Preparación: traé algo que el consultor no tenga',
            'consigna' => "Antes de esta reunión, investigá y traé material concreto sobre el punto que se va a trabajar.\n\n"
                . "Qué sirve: precios publicados de experiencias comparables (con el enlace), normativa o requisitos que apliquen, "
                . "proveedores o aliados de la zona con su contacto, ejemplos de cómo lo resolvió otro destino.\n\n"
                . "Qué no sirve: generalidades sobre turismo, definiciones de manual, cosas que ya están en la ficha del proyecto.\n\n"
                . "Formato: lista. Cada punto con su fuente. Si no encontraste algo, decilo y contá dónde buscaste.",
            'min_caracteres' => 900,
            'horas'   => 2.0,
            'vence_horas_antes' => 24,
        ],
        'durante' => [
            'titulo'  => 'En la sala: lo que sólo tenés vos por haber estado',
            'consigna' => "Completá los cuatro campos durante la reunión o apenas termine. No es un resumen: son datos.\n\n"
                . "El consultor que estuvo con vos va a leer esto al lado de su propia minuta, así que la precisión importa "
                . "más que la prolijidad. Una frase textual mal transcrita vale menos que una bien copiada.",
            // Sin mínimo de caracteres, y es deliberado. Esta consigna se
            // aprueba trayendo los cuatro específicos, no escribiendo largo:
            // cuatro citas y cuatro números bien tomados entran en 300
            // caracteres. Un mínimo de 700 acá empujaría a rellenar con
            // palabrerío, que es justo lo que esta consigna existe para evitar.
            // El control es especificos_cumplidos(), más abajo.
            'min_caracteres' => 0,
            'horas'   => 0.5,
            'vence_horas_despues' => 12,
        ],
        'despues' => [
            'titulo'  => 'Entregable: movele un paso al proyecto',
            'consigna' => "No escribas qué pasó en la reunión: eso ya está en la minuta. Producí una pieza que el emprendedor "
                . "o el consultor puedan usar tal cual.\n\n"
                . "Elegí con tu consultor cuál corresponde esta vez: tabla de precios de la competencia · borrador del texto "
                . "de la ficha de venta · inventario de las fotos y videos que faltan · mapa de accesos y logística de la visita · "
                . "lista de 10 aliados posibles con contacto · borrador de tres publicaciones · guion de la visita paso a paso.\n\n"
                . "Formato: el de la pieza, no el de un informe. Si es una tabla, que sea una tabla.",
            'min_caracteres' => 1200,
            'horas'   => 3.0,
            'vence_horas_despues' => 48,
        ],
    ];
}

/**
 * Horas ya comprometidas por un estudiante.
 *
 * Suma las reuniones a las que va —con su duración real si ya pasó, y con la
 * planificada si todavía no— más las horas estimadas de sus consignas. Se
 * calcula, no se guarda: un total guardado se desincroniza el día que alguien
 * mueve una reunión, y entonces miente sin avisar.
 */
function horas_estudiante(PDO $pdo, string $estudianteId): array
{
    $reu = $pdo->prepare(
        "SELECT r.hora_inicio, r.hora_fin, r.hora_real_inicio, r.hora_real_fin, r.estado
           FROM lab_reunion_asistentes a
           JOIN lab_reuniones r ON r.id = a.reunion_id
          WHERE a.consultor_id = ?"
    );
    $reu->execute([$estudianteId]);

    $horasReuniones = 0.0;
    $reunionesCount = 0;
    foreach ($reu->fetchAll() as $r) {
        $ini = $r['hora_real_inicio'] !== '' ? $r['hora_real_inicio'] : $r['hora_inicio'];
        $fin = $r['hora_real_fin'] !== '' ? $r['hora_real_fin'] : $r['hora_fin'];
        $horasReuniones += duracion_horas($ini, $fin);
        $reunionesCount++;
    }

    $tar = $pdo->prepare(
        "SELECT COALESCE(SUM(horas_estimadas), 0) FROM lab_tareas_estudiante WHERE estudiante_id = ?"
    );
    $tar->execute([$estudianteId]);
    $horasTareas = (float) $tar->fetchColumn();

    $ficha = $pdo->prepare('SELECT horas_presupuesto FROM lab_estudiantes WHERE consultor_id = ?');
    $ficha->execute([$estudianteId]);
    $presupuesto = (float) ($ficha->fetchColumn() ?: 60);

    $usadas = round($horasReuniones + $horasTareas, 1);
    return [
        'reuniones'   => round($horasReuniones, 1),
        'tareas'      => round($horasTareas, 1),
        'usadas'      => $usadas,
        'presupuesto' => $presupuesto,
        'restantes'   => round($presupuesto - $usadas, 1),
        'porcentaje'  => $presupuesto > 0 ? min(100, round($usadas / $presupuesto * 100)) : 0,
        'cantidad_reuniones' => $reunionesCount,
    ];
}

/** Horas entre dos "HH:MM". Devuelve 0 si falta alguna o si no cierra. */
function duracion_horas(string $desde, string $hasta): float
{
    if (!preg_match('/^\d{1,2}:\d{2}$/', $desde) || !preg_match('/^\d{1,2}:\d{2}$/', $hasta)) {
        return 0.0;
    }
    [$h1, $m1] = array_map('intval', explode(':', $desde));
    [$h2, $m2] = array_map('intval', explode(':', $hasta));
    $min = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
    return $min > 0 ? round($min / 60, 2) : 0.0;
}

/**
 * Cuántos de los específicos pedidos trajo realmente una entrega.
 *
 * Cuenta ítems con contenido, no caracteres: dos frases textuales valen más
 * que un párrafo sobre la importancia de escuchar al emprendedor.
 */
function especificos_cumplidos(array $especificos): array
{
    $trae = 0;
    $pide = 0;
    $detalle = [];

    foreach (ESPECIFICOS_REUNION as $clave => $def) {
        $valor = $especificos[$clave] ?? '';
        $crudos = is_array($valor)
            ? array_map('trim', $valor)
            : array_map('trim', preg_split('/\r?\n/', (string) $valor));

        // Un ítem cuenta si dice algo. Sin este piso, "a" y "b" en dos líneas
        // pasaban como dos frases textuales del emprendedor, y el control se
        // volvía un trámite de apretar Enter.
        $items = array_filter($crudos, fn($x) => mb_strlen($x) >= MINIMO_ITEM_ESPECIFICO);

        $cuantos = count($items);
        $ok = $cuantos >= $def['minimo'];
        $detalle[$clave] = ['label' => $def['label'], 'pide' => $def['minimo'], 'trae' => $cuantos, 'ok' => $ok];
        $pide++;
        if ($ok) {
            $trae++;
        }
    }

    return ['cumplidos' => $trae, 'total' => $pide, 'detalle' => $detalle, 'completo' => $trae === $pide];
}

/**
 * Señales sobre CÓMO se escribió una entrega.
 *
 * Importante, y va escrito acá para que no se pierda: esto NO dice si un texto
 * lo escribió una persona o un modelo. No existe forma confiable de saberlo, y
 * los detectores que dicen hacerlo se equivocan seguido y se equivocan peor con
 * quien escribe simple o no escribe en su lengua materna. Acusar a alguien con
 * una de esas salidas es injusto y además indefendible.
 *
 * Lo que sí son hechos: cuánto del texto entró pegado de una sola vez, en
 * cuántas sesiones se editó, cuánto tiempo estuvo abierto el campo, cuánto
 * tardó desde que terminó la reunión. Se muestran como hechos, sin veredicto.
 * El profesor los mira junto con la valoración del consultor y decide él.
 *
 * La defensa de verdad no está acá: está en pedir cosas que no se pueden
 * contestar sin haber estado.
 */
function señales_escritura(array $tarea): array
{
    $chars = max(1, (int) $tarea['caracteres']);
    $pegados = (int) $tarea['caracteres_pegados'];
    $segundos = (int) $tarea['segundos_edicion'];

    $pctPegado = round($pegados / $chars * 100);
    // Caracteres por minuto de edición. Sirve para conversar, no para acusar:
    // alguien que redactó aparte y pegó su propio texto da alto igual.
    $ritmo = $segundos > 0 ? round($chars / ($segundos / 60)) : null;

    return [
        'caracteres'     => (int) $tarea['caracteres'],
        'pct_pegado'     => $pctPegado,
        'sesiones'       => (int) $tarea['sesiones_edicion'],
        'minutos'        => round($segundos / 60),
        'ritmo_cpm'      => $ritmo,
        // Una sola frase honesta para la pantalla del profesor.
        'lectura' => $pctPegado >= 90
            ? 'El texto entró casi entero pegado de una vez. Puede ser que lo haya redactado aparte; conviene preguntarle.'
            : ($pctPegado >= 50
                ? 'Buena parte del texto entró pegado. Mirá si los específicos coinciden con la minuta.'
                : 'El texto se escribió acá, en varias pasadas.'),
    ];
}

/**
 * Genera las tres consignas de una reunión para el estudiante del proyecto.
 *
 * Idempotente: si ya existen, no las duplica. Se llama al crear la reunión y
 * cada vez que se le cambia la fecha, porque los vencimientos cuelgan de ella.
 */
function generar_consignas_reunion(PDO $pdo, string $reunionId): int
{
    $r = $pdo->prepare(
        "SELECT r.id, r.proyecto_id, r.fecha, r.hora_inicio, r.hora_fin, r.numero_reunion, r.titulo,
                e.consultor_id AS estudiante_id
           FROM lab_reuniones r
           JOIN lab_estudiantes e ON e.proyecto_id = r.proyecto_id AND e.activo = 1
          WHERE r.id = ?"
    );
    $r->execute([$reunionId]);
    $reunion = $r->fetch();
    if (!$reunion) {
        return 0;   // el proyecto todavía no tiene estudiante asignado
    }

    // Antes del alta del estudiante no se le generan consignas: entra después
    // de FIT y no tiene por qué responder por reuniones en las que no estuvo.
    $alta = $pdo->prepare('SELECT alta_desde FROM lab_estudiantes WHERE consultor_id = ?');
    $alta->execute([$reunion['estudiante_id']]);
    $desde = (string) $alta->fetchColumn();
    if ($desde !== '' && $reunion['fecha'] < $desde) {
        return 0;
    }

    $plantillas = plantillas_consignas();
    $creadas = 0;

    $existe = $pdo->prepare('SELECT id FROM lab_tareas_estudiante WHERE reunion_id = ? AND momento = ? AND estudiante_id = ?');
    $ins = $pdo->prepare(
        "INSERT INTO lab_tareas_estudiante
         (estudiante_id, proyecto_id, reunion_id, momento, plantilla_id, titulo, consigna, min_caracteres, horas_estimadas, vence_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $actualizaVence = $pdo->prepare('UPDATE lab_tareas_estudiante SET vence_at = ?, min_caracteres = ?, horas_estimadas = ?, updated_at = datetime(\'now\') WHERE id = ?');

    foreach ($plantillas as $momento => $p) {
        $vence = vencimiento_consigna($reunion, $p);

        $existe->execute([$reunionId, $momento, $reunion['estudiante_id']]);
        if ($id = $existe->fetchColumn()) {
            // Ya existe: se recalculan el vencimiento —la reunión pudo
            // moverse de fecha— y las condiciones de la consigna, para que un
            // ajuste de la plantilla llegue a las que ya están creadas. Lo
            // entregado no se toca nunca.
            $actualizaVence->execute([$vence, $p['min_caracteres'], $p['horas'], $id]);
            continue;
        }

        $ins->execute([
            $reunion['estudiante_id'], $reunion['proyecto_id'], $reunionId, $momento, $momento,
            $p['titulo'], $p['consigna'], $p['min_caracteres'], $p['horas'], $vence,
        ]);
        $creadas++;
    }

    return $creadas;
}

/** Cuándo vence una consigna, en función de la fecha y hora de la reunión. */
function vencimiento_consigna(array $reunion, array $plantilla): string
{
    $base = trim($reunion['fecha'] . ' ' . ($reunion['hora_inicio'] ?: '09:00'));
    $ts = strtotime($base);
    if ($ts === false) {
        return '';
    }
    if (isset($plantilla['vence_horas_antes'])) {
        return date('Y-m-d H:i', $ts - $plantilla['vence_horas_antes'] * 3600);
    }
    $fin = strtotime(trim($reunion['fecha'] . ' ' . ($reunion['hora_fin'] ?: '11:00')));
    $desde = $fin !== false ? $fin : $ts;
    return date('Y-m-d H:i', $desde + ($plantilla['vence_horas_despues'] ?? 48) * 3600);
}

/**
 * El tablero del profesor para un estudiante.
 *
 * Junta tres cosas que hay que mirar juntas y nunca por separado:
 *   1. presencia    — entró al panel, estuvo, fue a las reuniones
 *   2. cumplimiento — entregó a tiempo, con los específicos pedidos
 *   3. utilidad     — el consultor dijo si sirvió
 *
 * La tercera es la que manda. Se puede entrar todos los días, escribir 4.000
 * caracteres por consigna y no aportarle nada a nadie.
 */
function panel_estudiante(PDO $pdo, string $estudianteId): array
{
    $ficha = $pdo->prepare(
        "SELECT e.*, c.nombre, p.nombre AS proyecto_nombre, u.username
           FROM lab_estudiantes e
           JOIN lab_consultores c ON c.id = e.consultor_id
           LEFT JOIN lab_proyectos p ON p.id = e.proyecto_id
           LEFT JOIN users u ON u.id = e.user_id
          WHERE e.consultor_id = ?"
    );
    $ficha->execute([$estudianteId]);
    $est = $ficha->fetch();
    if (!$est) {
        return [];
    }

    $t = $pdo->prepare('SELECT * FROM lab_tareas_estudiante WHERE estudiante_id = ? ORDER BY vence_at');
    $t->execute([$estudianteId]);
    $tareas = $t->fetchAll();

    $entregadas = 0;
    $vencidas = 0;
    $aTiempo = 0;
    $caracteres = 0;
    $especificosOk = 0;
    $especificosTotal = 0;
    $valoradas = ['sirvio' => 0, 'parcial' => 0, 'rehacer' => 0];
    $ahora = date('Y-m-d H:i');

    foreach ($tareas as $x) {
        if ($x['estado'] === 'pendiente') {
            if (!empty($x['vence_at']) && $x['vence_at'] < $ahora) {
                $vencidas++;
            }
            continue;
        }
        $entregadas++;
        $caracteres += (int) $x['caracteres'];
        if ($x['vence_at'] === '' || ($x['entregado_at'] && $x['entregado_at'] <= $x['vence_at'])) {
            $aTiempo++;
        }
        if ($x['momento'] === 'durante') {
            $esp = especificos_cumplidos(json_decode($x['especificos'] ?: '{}', true) ?: []);
            $especificosOk += $esp['cumplidos'];
            $especificosTotal += $esp['total'];
        }
        if (isset($valoradas[$x['valoracion']])) {
            $valoradas[$x['valoracion']]++;
        }
    }

    // Presencia en el panel: sale de lo que ya registra el sitio.
    $act = $pdo->prepare(
        "SELECT COUNT(*) n, COALESCE(SUM(strftime('%s', ultima_actividad) - strftime('%s', inicio)), 0) seg
           FROM sesiones_panel WHERE username = ?"
    );
    $act->execute([$est['username'] ?? '']);
    $actividad = $act->fetch() ?: ['n' => 0, 'seg' => 0];

    $conValoracion = array_sum($valoradas);
    $utilidad = $conValoracion > 0
        ? round(($valoradas['sirvio'] + $valoradas['parcial'] * 0.5) / $conValoracion * 100)
        : null;

    return [
        'estudiante' => $est,
        'horas'      => horas_estudiante($pdo, $estudianteId),
        'tareas'     => $tareas,
        'resumen'    => [
            'asignadas'   => count($tareas),
            'entregadas'  => $entregadas,
            'vencidas'    => $vencidas,
            'a_tiempo'    => $aTiempo,
            'caracteres'  => $caracteres,
            'especificos' => ['ok' => $especificosOk, 'total' => $especificosTotal],
            'valoradas'   => $valoradas,
            'utilidad'    => $utilidad,
            'ingresos'    => (int) $actividad['n'],
            'minutos_panel' => round(((int) $actividad['seg']) / 60),
        ],
    ];
}

// --- Alta del convenio ----------------------------------------------------

/**
 * Deja listos los 18 estudiantes, uno por emprendimiento.
 *
 * Idempotente: se puede llamar en cada carga de página sin duplicar nada, que
 * es como funciona el resto de la semilla de este proyecto.
 *
 * Los nombres van con un marcador de plantilla a propósito. Todavía no tenemos
 * la lista real del instituto, y poner nombres inventados que después nadie
 * corrige es peor que dejar el lugar marcado: desde Usuarios se renombra cada
 * uno cuando llegue la nómina. Lo que sí queda armado es la estructura —el
 * usuario, la ficha, el emprendimiento asignado y el presupuesto de horas—,
 * que es lo que hace falta para que el sistema funcione.
 */
function iset_asegurar_estudiantes(PDO $pdo): array
{
    // Los estudiantes entran después de FIT. Antes de esa fecha no se les
    // generan consignas: no tienen por qué responder por reuniones que no
    // vivieron.
    $altaDesde = ISET_ALTA_DESDE;

    $proyectos = $pdo->query('SELECT id, nombre FROM lab_proyectos ORDER BY celula, nombre')->fetchAll();
    if (!$proyectos) {
        return ['creados' => 0, 'total' => 0, 'aviso' => 'Todavía no hay emprendimientos cargados.'];
    }

    $insUser = $pdo->prepare("INSERT INTO users (username, password, role, must_change_password, created_at) VALUES (?, ?, 'estudiante', 1, datetime('now'))");
    $buscaUser = $pdo->prepare('SELECT id FROM users WHERE LOWER(username) = LOWER(?)');
    $insCons = $pdo->prepare("INSERT INTO lab_consultores (id, nombre, rol, tipo, disponibilidad, restricciones, color, activo) VALUES (?, ?, 'Estudiante ISET', 'estudiante', ?, ?, ?, 1)");
    $existeCons = $pdo->prepare('SELECT 1 FROM lab_consultores WHERE id = ?');
    $insFicha = $pdo->prepare(
        "INSERT INTO lab_estudiantes (consultor_id, user_id, instituto, legajo, proyecto_id, horas_presupuesto, alta_desde, activo)
         VALUES (?, ?, 'ISET 815', '', ?, ?, ?, 1)"
    );
    $existeFicha = $pdo->prepare('SELECT 1 FROM lab_estudiantes WHERE consultor_id = ?');

    // Disponibilidad por defecto: tarde, que es cuando un estudiante de
    // terciario puede. Se ajusta uno por uno desde la ficha.
    $disponibilidad = json_encode(['14–20', '14–20', '14–20', '14–20', '14–20'], JSON_UNESCAPED_UNICODE);

    $creados = 0;
    $n = 0;
    foreach ($proyectos as $p) {
        $n++;
        $slug = 'est-' . $p['id'];
        $usuario = 'iset' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        $nombre = 'Estudiante ' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);

        $buscaUser->execute([$usuario]);
        $userId = $buscaUser->fetchColumn();
        if (!$userId) {
            // Contraseña provisoria dictable, la misma mecánica que el resto
            // del panel: se genera una y se cambia al entrar.
            $insUser->execute([$usuario, password_hash(clave_dictable(), PASSWORD_DEFAULT)]);
            $userId = (int) $pdo->lastInsertId();
        }

        $existeCons->execute([$slug]);
        if (!$existeCons->fetchColumn()) {
            $insCons->execute([$slug, $nombre, $disponibilidad, 'Convenio ISET 815. Disponible después de FIT.', ISET_COLOR]);
        }

        $existeFicha->execute([$slug]);
        if (!$existeFicha->fetchColumn()) {
            $insFicha->execute([$slug, (int) $userId, $p['id'], ISET_HORAS, $altaDesde]);
            $creados++;
        }

        // OJO: al estudiante NO se le da lab_user_access. Ese permiso abre
        // gestion.php, que es el módulo completo —los 18 emprendimientos, la
        // agenda de todo el equipo, la matriz de carga—. El estudiante entra
        // por estudiante.php, que tiene su propia puerta y le muestra sólo su
        // caso. Dárselo "para que vea su ficha" le abría los 17 restantes.
    }

    // El profesor: un solo usuario con la mirada global.
    $buscaUser->execute(['profesor']);
    if (!$buscaUser->fetchColumn()) {
        $pdo->prepare("INSERT INTO users (username, password, role, must_change_password, created_at) VALUES ('profesor', ?, 'profesor', 1, datetime('now'))")
            ->execute([password_hash(clave_dictable(), PASSWORD_DEFAULT)]);
    }

    return ['creados' => $creados, 'total' => count($proyectos), 'aviso' => ''];
}

/**
 * Asigna un estudiante a todas las reuniones futuras de su emprendimiento y
 * les genera las consignas.
 *
 * Se llama al dar de alta el convenio y cada vez que se crea una reunión.
 */
function iset_enganchar_reuniones(PDO $pdo, string $estudianteId): int
{
    $f = $pdo->prepare('SELECT proyecto_id, alta_desde FROM lab_estudiantes WHERE consultor_id = ?');
    $f->execute([$estudianteId]);
    $ficha = $f->fetch();
    if (!$ficha || !$ficha['proyecto_id']) {
        return 0;
    }

    $reu = $pdo->prepare('SELECT id FROM lab_reuniones WHERE proyecto_id = ? AND fecha >= ?');
    $reu->execute([$ficha['proyecto_id'], $ficha['alta_desde'] ?: '0000-00-00']);

    $ins = $pdo->prepare("INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol, asistio) VALUES (?, ?, 'estudiante', 1)");
    $tocadas = 0;
    foreach ($reu->fetchAll(PDO::FETCH_COLUMN) as $reunionId) {
        $ins->execute([$reunionId, $estudianteId]);
        generar_consignas_reunion($pdo, $reunionId);
        $tocadas++;
    }
    return $tocadas;
}
