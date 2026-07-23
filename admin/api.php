<?php
// Creado para el Laboratorio de Destino Esquel
// admin/api.php

session_start();
require_once 'db_config.php';

// 1. VERIFICAR AUTENTICACIÓN
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesión no autorizada']);
    exit();
}

$current_role = $_SESSION['role'];

// 2. PROCESAR EXPORTACIÓN A EXCEL (CSV UTF-8) POR GET
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    try {
        // Consultar todas las postulaciones
        $stmt = $db->query("SELECT * FROM applications ORDER BY submitted_at DESC");
        $apps = $stmt->fetchAll();

        // Configurar cabeceras de descarga de archivo CSV
        $filename = "Postulaciones_Esquel_LAB_" . date("Y-m-d_H-i") . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Abrir salida estándar
        $output = fopen('php://output', 'w');
        
        // Agregar BOM UTF-8 para que Excel detecte correctamente caracteres con tilde y eñes
        fwrite($output, "\xEF\xBB\xBF");

        // Cabeceras del CSV
        fputcsv($output, [
            'ID', 
            'Proyecto / Emprendimiento', 
            'Responsable', 
            'Email', 
            'Teléfono', 
            'Programa', 
            'Estado', 
            'Diferenciación (1-5)', 
            'Impacto (1-5)', 
            'Perfil (1-5)', 
            'Producto Físico (1-5)', 
            'Viabilidad (1-5)', 
            'Promedio', 
            'Fecha Registro', 
            'Notas de Evaluación'
        ], ';'); // Separador punto y coma para compatibilidad regional con Excel en Español

        // Inyectar datos
        foreach ($apps as $app) {
            $avg = ($app['rating_diferenciacion'] + $app['rating_impacto'] + $app['rating_perfil'] + $app['rating_producto_fisico'] + $app['rating_viabilidad']) / 5;
            
            fputcsv($output, [
                $app['id'],
                $app['name'],
                $app['contact_name'],
                $app['email'],
                $app['phone'],
                $app['program'],
                $app['stage'],
                $app['rating_diferenciacion'],
                $app['rating_impacto'],
                $app['rating_perfil'],
                $app['rating_producto_fisico'],
                $app['rating_viabilidad'],
                number_format($avg, 2),
                date('d/m/Y H:i', strtotime($app['submitted_at'])),
                $app['notes']
            ], ';');
        }

        fclose($output);
        exit();

    } catch (Exception $e) {
        die("Error al generar reporte: " . $e->getMessage());
    }
}

// 3. PROCESAR ACCIONES AJAX (POST JSON)
$input_raw = file_get_contents('php://input');
$data = json_decode($input_raw, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data) {
    
    // Validar permisos de rol
    if ($current_role === 'viewer') {
        echo json_encode(['success' => false, 'error' => 'Permisos insuficientes para realizar esta operación.']);
        exit();
    }

    $action = isset($data['action']) ? $data['action'] : '';

    if ($action === 'update_application') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $stage = isset($data['stage']) ? trim($data['stage']) : 'Pendiente';
        $notes = isset($data['notes']) ? trim($data['notes']) : '';
        
        $rating_dif = isset($data['rating_diferenciacion']) ? intval($data['rating_diferenciacion']) : 0;
        $rating_imp = isset($data['rating_impacto']) ? intval($data['rating_impacto']) : 0;
        $rating_per = isset($data['rating_perfil']) ? intval($data['rating_perfil']) : 0;
        $rating_pf = isset($data['rating_producto_fisico']) ? intval($data['rating_producto_fisico']) : 0;
        $rating_viab = isset($data['rating_viabilidad']) ? intval($data['rating_viabilidad']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de postulación no válido.']);
            exit();
        }

        try {
            $stmt = $db->prepare("UPDATE applications SET 
                stage = :stage,
                notes = :notes,
                rating_diferenciacion = :rat_dif,
                rating_impacto = :rat_imp,
                rating_perfil = :rat_per,
                rating_producto_fisico = :rat_pf,
                rating_viabilidad = :rat_viab
                WHERE id = :id");
                
            $result = $stmt->execute([
                ':stage' => $stage,
                ':notes' => $notes,
                ':rat_dif' => $rating_dif,
                ':rat_imp' => $rating_imp,
                ':rat_per' => $rating_per,
                ':rat_pf' => $rating_pf,
                ':rat_viab' => $rating_viab,
                ':id' => $id
            ]);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el registro.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Petición inválida
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Acción o método inválido']);
exit();
?>
