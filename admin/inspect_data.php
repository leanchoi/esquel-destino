<?php
// admin/inspect_data.php
header('Content-Type: application/json');
try {
    $db = new PDO("sqlite:../data/database.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $users = $db->query("SELECT id, username, role FROM users")->fetchAll();
    $apps = $db->query("SELECT * FROM applications")->fetchAll();
    
    // Fetch details for each app
    foreach ($apps as &$app) {
        $app['details'] = $db->query("SELECT field_key, field_value FROM application_details WHERE application_id = " . intval($app['id']))->fetchAll();
    }
    
    $evaluaciones = $db->query("SELECT * FROM evaluaciones")->fetchAll();
    $versiones = $db->query("SELECT * FROM evaluacion_versiones")->fetchAll();

    echo json_encode([
        'users' => $users,
        'applications' => $apps,
        'evaluaciones' => $evaluaciones,
        'versiones' => $versiones
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
