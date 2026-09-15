<?php
/**
 * Diagnóstico de la base. SOLO ADMIN.
 *
 * Antes de este candado el archivo abría la base y devolvía JSON sin pedir
 * sesión: entrando a /admin/inspect_data.php desde cualquier navegador salía el
 * esquema y la lista de usuarios con sus roles. Eso es media entrada: con los
 * nombres de usuario a la vista, sólo falta la contraseña.
 *
 * No hace falta que sea público para lo que sirve —mirar la base cuando algo
 * falla—, así que va detrás del rol admin como el resto del panel.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requiere_rol('admin');

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
