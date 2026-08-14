<?php
/**
 * Script para automatizar las evaluaciones de Leandro en producción.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/jurado.php';

// Security check: either must be logged in as admin/editor OR have the secret URL key
iniciar_sesion();
$u = usuario_actual();
$secret_key = 'leandro_eval_2026';
$has_key = isset($_GET['key']) && $_GET['key'] === $secret_key;

if (!$has_key && (!$u || !puede('editor'))) {
    http_response_code(403);
    die("Acceso no autorizado.");
}

$votes_file = __DIR__ . '/leandro_votes.json';
if (!file_exists($votes_file)) {
    die("Archivo leandro_votes.json no encontrado.");
}

$votes = json_decode(file_get_contents($votes_file), true);
if (!is_array($votes)) {
    die("JSON de votos no válido.");
}

$pdo = db();
$leandro_id = 4;
$leandro_username = 'Leandro';

echo "<h2>Procesando evaluaciones de Leandro...</h2>";
echo "<pre>";

$pdo->beginTransaction();
$count = 0;

$campos = array_keys(CRITERIOS);
$lista  = implode(', ', $campos);
$marcas = implode(', ', array_fill(0, count($campos), '?'));
$updates = implode(', ', array_map(fn($c) => "$c = excluded.$c", $campos));

$sql = "INSERT INTO evaluaciones (application_id, user_id, username, $lista, comentario, abstencion, created_at, updated_at)
        VALUES (?, ?, ?, $marcas, ?, ?, datetime('now'), datetime('now'))
        ON CONFLICT (application_id, user_id) DO UPDATE SET
          $updates, comentario = excluded.comentario,
          abstencion = excluded.abstencion, updated_at = datetime('now')";

foreach ($votes as $v) {
    $app_id = (int)$v['id'];
    $ratings = $v['ratings'];
    $comentario = $v['comentario'];
    $name = $v['name'];
    
    // Check if Leandro already has a vote
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluaciones WHERE application_id = ? AND user_id = ?");
    $stmt->execute([$app_id, $leandro_id]);
    $already_voted = (int)$stmt->fetchColumn() > 0;
    
    if ($already_voted) {
        echo "Proyecto ID $app_id - \"$name\": Ya tiene evaluación de Leandro (se actualizará).\n";
    } else {
        echo "Proyecto ID $app_id - \"$name\": Insertando evaluación de Leandro.\n";
    }
    
    $bind = array_merge(
        [$app_id, $leandro_id, $leandro_username],
        array_map(fn($c) => (int)($ratings[$c] ?? 0), $campos),
        [$comentario, 0]
    );
    
    $pdo->prepare($sql)->execute($bind);
    
    // Save version
    guardar_version($pdo, $app_id, $leandro_id, $leandro_username, $ratings, $comentario, false, 'guardado');
    $count++;
}

$pdo->commit();
echo "\nÉxito: Se procesaron $count evaluaciones correctamente.\n";
echo "</pre>";
