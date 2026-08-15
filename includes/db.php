<?php
/**
 * Conexión SQLite + creación/migración del esquema.
 *
 * La base vive en /data, bloqueada al público por data/.htaccess.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    try {
        $pdo = new PDO('sqlite:' . $dir . '/database.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        migrar($pdo);
    } catch (PDOException $ex) {
        // Nunca exponer detalles internos al visitante.
        error_log('[esquel-lab] error de base: ' . $ex->getMessage());
        http_response_code(500);
        exit('El sitio no está disponible en este momento. Probá de nuevo en unos minutos.');
    }

    return $pdo;
}

function migrar(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'viewer',
        must_change_password INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        contact_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        program TEXT NOT NULL,
        stage TEXT NOT NULL DEFAULT 'Pendiente',
        notes TEXT NOT NULL DEFAULT '',
        rating_diferenciacion INTEGER NOT NULL DEFAULT 0,
        rating_impacto INTEGER NOT NULL DEFAULT 0,
        rating_perfil INTEGER NOT NULL DEFAULT 0,
        rating_producto_fisico INTEGER NOT NULL DEFAULT 0,
        rating_viabilidad INTEGER NOT NULL DEFAULT 0,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Evaluaciones: un voto por jurado y por postulación.
    //
    // Antes los rating_* vivían en la fila de la postulación, así que era una
    // sola evaluación compartida: el segundo evaluador pisaba lo que había
    // puesto el primero y nadie se enteraba. Ahora cada jurado tiene su propia
    // fila, con su comentario, y el puntaje que se muestra es el promedio de
    // los votos emitidos.
    //
    // Las columnas de criterios se generan desde CRITERIOS para que la config
    // siga siendo la única fuente de verdad. Son constantes del código, no
    // entrada del usuario, así que interpolarlas es seguro.
    $colsCriterios = '';
    foreach (array_keys(CRITERIOS) as $campo) {
        $colsCriterios .= "        $campo INTEGER NOT NULL DEFAULT 0,\n";
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS evaluaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        username TEXT NOT NULL,
$colsCriterios        comentario TEXT NOT NULL DEFAULT '',
        abstencion INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (application_id, user_id),
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_eval_app ON evaluaciones (application_id);");

    // Historial de cada evaluación.
    //
    // La tabla de arriba guarda cómo quedó el voto; ésta, cómo fue quedando.
    // Un jurado puede volver sobre su evaluación cuantas veces quiera —y está
    // bien que pueda: leer diez propuestas cambia la vara con la que se miró
    // la primera—, pero entonces el puntaje de hoy puede no ser el de ayer, y
    // sin registro no hay manera de saberlo ni de explicarlo después.
    //
    // Se guarda la foto entera y no el cambio. Ocupa más, pero volver a una
    // versión es copiar una fila en vez de rehacer una cadena de parches, y
    // una cadena de parches con un eslabón roto se lleva puesta toda la
    // historia que venga después.
    //
    // origen dice de dónde salió la fila: 'guardado' es el jurado guardando,
    // 'restaurado' es el jurado volviendo a una versión anterior, y 'retirado'
    // es el voto dado de baja, que también es parte de la historia.
    $pdo->exec("CREATE TABLE IF NOT EXISTS evaluacion_versiones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        username TEXT NOT NULL,
$colsCriterios        comentario TEXT NOT NULL DEFAULT '',
        abstencion INTEGER NOT NULL DEFAULT 0,
        origen TEXT NOT NULL DEFAULT 'guardado',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ver_app_user ON evaluacion_versiones (application_id, user_id, id);");

    $pdo->exec("CREATE TABLE IF NOT EXISTS application_details (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        field_key TEXT NOT NULL,
        field_value TEXT NOT NULL,
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");

    // Quienes dejaron los datos para que les avisemos de la próxima cohorte.
    //
    // El correo es único: alguien que deja los datos dos veces —porque no se
    // acuerda, o porque vuelve a entrar meses después— tiene que quedar una
    // sola vez en la lista y con lo último que escribió, no duplicado. Cuando
    // se repite se actualiza la fila y se guarda cuándo fue la última.
    $pdo->exec("CREATE TABLE IF NOT EXISTS interesados (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT NOT NULL,
        telefono TEXT NOT NULL DEFAULT '',
        linea TEXT NOT NULL DEFAULT '',
        instagram TEXT NOT NULL DEFAULT '',
        cuenta TEXT NOT NULL DEFAULT '',
        origen TEXT NOT NULL DEFAULT '',
        veces INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_interesados_email ON interesados (email);");

    // Bitácora de cambios de estado: trazabilidad del proceso de selección.
    $pdo->exec("CREATE TABLE IF NOT EXISTS stage_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        user_id INTEGER,
        username TEXT,
        stage_from TEXT,
        stage_to TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");

    // Analítica propia. Sin cookies de seguimiento y sin IPs en claro: al
    // visitante lo identifica un hash que cambia todos los días, así sirve
    // para contar únicos por jornada pero no para seguir a nadie en el tiempo.
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ruta TEXT NOT NULL,
        origen TEXT NOT NULL DEFAULT '',
        dispositivo TEXT NOT NULL DEFAULT 'escritorio',
        pais TEXT,
        visitante TEXT NOT NULL,
        segundos INTEGER,
        profundidad INTEGER,
        paso_form INTEGER,
        creada_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_visitas_fecha ON visitas (creada_at);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_visitas_ruta ON visitas (ruta);");

    // Sesiones del panel: cuándo entró cada quien y cuánto se quedó.
    //
    // login_attempts ya guarda el momento de cada ingreso, pero no cuánto duró.
    // Acá se abre una fila al entrar y se le corre ultima_actividad en cada
    // pantalla que se abre. La duración es la diferencia entre las dos, así que
    // no cuenta el rato que alguien pasa leyendo la última pantalla antes de
    // irse: es un piso, no un cronómetro, y así está dicho en el panel.
    $pdo->exec("CREATE TABLE IF NOT EXISTS sesiones_panel (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        username TEXT NOT NULL,
        inicio DATETIME,
        ultima_actividad DATETIME,
        pantallas INTEGER NOT NULL DEFAULT 1,
        cerrada INTEGER NOT NULL DEFAULT 0
    );");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_sesiones_user ON sesiones_panel (user_id, inicio);");

    // Rate-limiting de login.
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        ip TEXT NOT NULL,
        ok INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Configuración general del panel y procesos.
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    );");

    // Migraciones de columnas.
    //
    // El CREATE TABLE IF NOT EXISTS de arriba no toca una tabla que ya existe,
    // así que una base creada por una versión anterior se queda sin las
    // columnas nuevas para siempre. Eso ya rompió el panel de usuarios: era el
    // único lugar que leía users.created_at, la columna no estaba, y la página
    // moría con un 500 mientras el resto del panel andaba bien.
    //
    // Por eso la lista es declarativa: agregar una columna nueva es agregar
    // una línea acá, y las bases viejas se ponen al día solas.
    $columnas = [
        'users' => [
            'must_change_password' => "INTEGER NOT NULL DEFAULT 0",
            'created_at'           => "DATETIME",
        ],
        'applications' => [
            'notes'        => "TEXT",
            'submitted_at' => "DATETIME",
        ],
        'interesados' => [
            'instagram' => "TEXT NOT NULL DEFAULT ''",
        ],
        // Un criterio nuevo en CRITERIOS aparece solo en las bases que ya existen.
        'evaluaciones' => array_fill_keys(array_keys(CRITERIOS), "INTEGER NOT NULL DEFAULT 0") + [
            'comentario' => "TEXT NOT NULL DEFAULT ''",
            'abstencion' => "INTEGER NOT NULL DEFAULT 0",
        ],
    ];

    foreach ($columnas as $tabla => $defs) {
        $existentes = array_column($pdo->query("PRAGMA table_info($tabla)")->fetchAll(), 'name');
        if (!$existentes) {
            continue;                       // la tabla no existe todavía
        }
        foreach ($defs as $columna => $tipo) {
            if (in_array($columna, $existentes, true)) {
                continue;
            }
            // SQLite no acepta CURRENT_TIMESTAMP como default al agregar una
            // columna, así que se agrega vacía y se rellena a continuación.
            $pdo->exec("ALTER TABLE $tabla ADD COLUMN $columna $tipo");
            if (str_ends_with($columna, '_at')) {
                $pdo->exec("UPDATE $tabla SET $columna = datetime('now') WHERE $columna IS NULL");
            }
        }
    }

    migrar_evaluaciones_viejas($pdo);

    // Usuario semilla. Nace marcado para cambio obligatorio de contraseña:
    // admin/admin123 sirve para el primer ingreso, no para quedarse.
    $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, must_change_password, created_at) VALUES (?, ?, 'admin', 1, datetime('now'))");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}

/** id reservado para la evaluación que existía antes de que hubiera votos por jurado. */
const EVALUADOR_HEREDADO = -1;

/**
 * Rescata las evaluaciones que quedaron en la fila de la postulación.
 *
 * Los rating_* de applications eran una evaluación compartida y sin firma: no
 * hay forma de saber quién la cargó. Se pasan a evaluaciones bajo un evaluador
 * heredado con id -1, que no coincide con ningún usuario real. Así el trabajo
 * hecho no se pierde y, al mismo tiempo, no se le atribuye a nadie un voto que
 * quizás no emitió: todos los jurados de carne y hueso siguen figurando como
 * pendientes hasta que voten.
 *
 * Corre una sola vez: después de migrar, los rating_* de applications quedan en
 * cero y la condición deja de encontrar nada.
 */
function migrar_evaluaciones_viejas(PDO $pdo): void
{
    $campos = array_keys(CRITERIOS);

    $existentes = array_column($pdo->query("PRAGMA table_info(applications)")->fetchAll(), 'name');
    foreach ($campos as $campo) {
        if (!in_array($campo, $existentes, true)) {
            return;                          // base nueva: no hay nada que rescatar
        }
    }

    $cond = implode(' + ', $campos);
    $filas = $pdo->query("SELECT id, " . implode(', ', $campos) . " FROM applications WHERE ($cond) > 0")->fetchAll();
    if (!$filas) {
        return;
    }

    $lista = implode(', ', $campos);
    $marcas = implode(', ', array_fill(0, count($campos), '?'));
    $ins = $pdo->prepare(
        "INSERT OR IGNORE INTO evaluaciones (application_id, user_id, username, $lista, comentario, created_at, updated_at)
         VALUES (?, ?, ?, $marcas, ?, datetime('now'), datetime('now'))"
    );
    $limpiar = $pdo->prepare(
        'UPDATE applications SET ' . implode(', ', array_map(fn($c) => "$c = 0", $campos)) . ' WHERE id = ?'
    );

    $pdo->beginTransaction();
    foreach ($filas as $f) {
        $valores = [$f['id'], EVALUADOR_HEREDADO, 'Evaluación anterior'];
        foreach ($campos as $campo) {
            $valores[] = (int) $f[$campo];
        }
        $valores[] = 'Cargada antes de que cada jurado tuviera su propio voto. No quedó registro de quién la hizo.';
        $ins->execute($valores);
        $limpiar->execute([$f['id']]);
    }
    $pdo->commit();
}
