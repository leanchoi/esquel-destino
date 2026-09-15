<?php
/**
 * Diagnóstico de la base. SOLO ADMIN.
 *
 * Antes de este candado el archivo abría la base y devolvía JSON sin pedir
 * sesión: entrando a /admin/check_db.php desde cualquier navegador salía el
 * esquema y la lista de usuarios con sus roles. Eso es media entrada: con los
 * nombres de usuario a la vista, sólo falta la contraseña.
 *
 * No hace falta que sea público para lo que sirve —mirar la base cuando algo
 * falla—, así que va detrás del rol admin como el resto del panel.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requiere_rol('admin');

// Creado para el Laboratorio de Destino Esquel
// admin/check_db.php
header('Content-Type: application/json');
try {
    $db = new PDO("sqlite:../data/database.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // List tables
    $tables_stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);

    $data = [];
    foreach ($tables as $table) {
        $schema_stmt = $db->query("PRAGMA table_info($table)");
        $schema = $schema_stmt->fetchAll();
        
        $rows_stmt = $db->query("SELECT * FROM $table");
        $rows = $rows_stmt->fetchAll();
        
        $data[$table] = [
            'schema' => $schema,
            'rows' => $rows
        ];
    }
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
