<?php
// Creado para el Laboratorio de Destino Esquel
// admin/db_config.php

// Asegurar que exista la carpeta data
$db_dir = __DIR__ . '/../data';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// Ruta a la base de datos SQLite
$db_path = $db_dir . '/database.sqlite';

try {
    // Conectar a SQLite (crea el archivo si no existe)
    $db = new PDO("sqlite:" . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Habilitar claves foráneas
    $db->exec("PRAGMA foreign_keys = ON;");

    // Crear tabla de usuarios si no existe
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'viewer'
    );");

    // Crear tabla de postulaciones si no existe
    $db->exec("CREATE TABLE IF NOT EXISTS applications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        contact_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        program TEXT NOT NULL, -- 'Acelera' o 'Raiz'
        stage TEXT NOT NULL DEFAULT 'Pendiente', -- 'Pendiente', 'Preseleccionado', 'Aprobado', 'Rechazado'
        notes TEXT NOT NULL DEFAULT '',
        rating_diferenciacion INTEGER NOT NULL DEFAULT 0,
        rating_impacto INTEGER NOT NULL DEFAULT 0,
        rating_perfil INTEGER NOT NULL DEFAULT 0,
        rating_producto_fisico INTEGER NOT NULL DEFAULT 0,
        rating_viabilidad INTEGER NOT NULL DEFAULT 0,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Crear tabla de detalles dinámicos (respuestas a preguntas específicas)
    $db->exec("CREATE TABLE IF NOT EXISTS application_details (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        application_id INTEGER NOT NULL,
        field_key TEXT NOT NULL,
        field_value TEXT NOT NULL,
        FOREIGN KEY (application_id) REFERENCES applications (id) ON DELETE CASCADE
    );");

    // Sembrar usuario administrador por defecto si la tabla está vacía
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    if ($result['count'] == 0) {
        $default_username = 'admin';
        // admin123
        $default_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $default_role = 'admin';
        
        $insert_stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $insert_stmt->execute([
            ':username' => $default_username,
            ':password' => $default_password_hash,
            ':role' => $default_role
        ]);
    }

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
