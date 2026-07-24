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

    $pdo->exec("CREATE TABLE IF NOT EXISTS application_details (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        field_key TEXT NOT NULL,
        field_value TEXT NOT NULL,
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");

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

    // Rate-limiting de login.
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        ip TEXT NOT NULL,
        ok INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Migración: agregar must_change_password a bases ya creadas.
    $cols = $pdo->query("PRAGMA table_info(users)")->fetchAll();
    $tiene = false;
    foreach ($cols as $c) {
        if ($c['name'] === 'must_change_password') {
            $tiene = true;
        }
    }
    if (!$tiene) {
        $pdo->exec("ALTER TABLE users ADD COLUMN must_change_password INTEGER NOT NULL DEFAULT 0");
    }

    // Usuario semilla. Nace marcado para cambio obligatorio de contraseña:
    // admin/admin123 sirve para el primer ingreso, no para quedarse.
    $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, must_change_password) VALUES (?, ?, 'admin', 1)");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}
