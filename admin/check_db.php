<?php
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
