<?php
// Creado para el Laboratorio de Destino Esquel
// admin/dashboard.php

session_start();
require_once 'db_config.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
$current_role = $_SESSION['role'];

$message = '';
$error = '';

// Procesar cambio de contraseña
if (isset($_POST['change_password'])) {
    $current_pwd = $_POST['current_password'];
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
        $error = 'Por favor complete todos los campos de contraseña.';
    } elseif ($new_pwd !== $confirm_pwd) {
        $error = 'Las nuevas contraseñas no coinciden.';
    } else {
        try {
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $current_user_id]);
            $user_pwd = $stmt->fetchColumn();

            if ($user_pwd && password_verify($current_pwd, $user_pwd)) {
                $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
                $update_stmt = $db->prepare("UPDATE users SET password = :pwd WHERE id = :id");
                $update_stmt->execute([':pwd' => $new_hash, ':id' => $current_user_id]);
                $message = 'Contraseña actualizada correctamente.';
            } else {
                $error = 'La contraseña actual es incorrecta.';
            }
        } catch (Exception $e) {
            $error = 'Error al cambiar contraseña: ' . $e->getMessage();
        }
    }
}

// Procesar creación de nuevo usuario (Solo Administrador)
if (isset($_POST['create_user']) && $current_role === 'admin') {
    $new_username = trim($_POST['new_username']);
    $new_password = $_POST['new_password_user'];
    $new_user_role = $_POST['new_role'];

    if (empty($new_username) || empty($new_password) || empty($new_user_role)) {
        $error = 'Por favor complete todos los campos para el nuevo usuario.';
    } else {
        try {
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $check_stmt->execute([':username' => $new_username]);
            if ($check_stmt->fetchColumn() > 0) {
                $error = 'El nombre de usuario ya está registrado.';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $insert_stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
                $insert_stmt->execute([
                    ':username' => $new_username,
                    ':password' => $new_hash,
                    ':role' => $new_user_role
                ]);
                $message = "Usuario '{$new_username}' creado con rol '{$new_user_role}'.";
            }
        } catch (Exception $e) {
            $error = 'Error al crear usuario: ' . $e->getMessage();
        }
    }
}

// Procesar eliminación de usuario (Solo Administrador)
if (isset($_GET['delete_user']) && $current_role === 'admin') {
    $del_id = intval($_GET['delete_user']);
    if ($del_id === $current_user_id) {
        $error = 'No puede eliminarse a sí mismo.';
    } else {
        try {
            $del_stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $del_stmt->execute([':id' => $del_id]);
            $message = 'Usuario eliminado correctamente.';
        } catch (Exception $e) {
            $error = 'Error al eliminar usuario: ' . $e->getMessage();
        }
    }
}

// Obtener todas las postulaciones con sus respuestas detalladas
try {
    // Buscar postulantes
    $stmt_apps = $db->query("SELECT * FROM applications ORDER BY submitted_at DESC");
    $applications_raw = $stmt_apps->fetchAll();
    
    // Buscar detalles dinámicos agrupados por aplicación
    $stmt_details = $db->query("SELECT * FROM application_details");
    $details_raw = $stmt_details->fetchAll();
    
    $app_details_map = [];
    foreach ($details_raw as $detail) {
        $app_details_map[$detail['application_id']][] = [
            'field_key' => $detail['field_key'],
            'field_value' => $detail['field_value']
        ];
    }
    
    // Consolidar
    $applications = [];
    $stats = [
        'total' => 0,
        'acelera' => 0,
        'raiz' => 0,
        'aprobados' => 0
    ];

    foreach ($applications_raw as $app) {
        $app_id = $app['id'];
        $app['details'] = isset($app_details_map[$app_id]) ? $app_details_map[$app_id] : [];
        $applications[] = $app;

        // Sumar estadísticas
        $stats['total']++;
        if ($app['program'] === 'Acelera') $stats['acelera']++;
        if ($app['program'] === 'Raiz') $stats['raiz']++;
        if ($app['stage'] === 'Aprobado') $stats['aprobados']++;
    }

} catch (PDOException $e) {
    die("Error al consultar postulaciones: " . $e->getMessage());
}

// Obtener lista de usuarios para la sección de gestión (Solo Administradores)
$system_users = [];
if ($current_role === 'admin') {
    try {
        $stmt_usr = $db->query("SELECT id, username, role FROM users ORDER BY username ASC");
        $system_users = $stmt_usr->fetchAll();
    } catch (Exception $e) {
        // Silencioso
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Interno · Laboratorio de Destino Esquel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: #0b0c0e;">
    <div class="bg-vignette"></div>

    <!-- Header Navigation -->
    <header class="header" style="position: static; margin-bottom: 32px;">
        <div class="container header-container">
            <a href="dashboard.php" class="logo-link">
                <img src="../assets/images/logo-lab-white.png" alt="Esquel LAB" class="logo-img">
            </a>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="font-size: 0.9rem; color: var(--color-text-secondary);">
                    Usuario: <strong style="color: var(--color-text-primary);"><?php echo htmlspecialchars($current_username); ?></strong> 
                    (<span style="text-transform: capitalize;"><?php echo $current_role; ?></span>)
                </span>
                <a href="api.php?export=excel" class="btn btn-secondary btn-sm">Descargar Excel (XLS)</a>
                <a href="logout.php" class="btn btn-primary btn-sm" style="background-color:#d43f3a; border-color:#d9534f; box-shadow:none;">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <main class="container" style="padding-bottom: 80px;">
        
        <!-- Alertas de Acciones -->
        <?php if (!empty($message)): ?>
            <div class="card" style="background-color: rgba(35, 111, 76, 0.1); border-color: #236f4c; padding: 16px; margin-bottom: 24px; text-align: center; border-radius: 8px;">
                <p style="color: #4ed392; margin-bottom: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="card" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; padding: 16px; margin-bottom: 24px; text-align: center; border-radius: 8px;">
                <p style="color: #f87171; margin-bottom: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <!-- CRM Header metrics -->
        <div class="stats-bar">
            <div class="card stat-card">
                <div class="stat-label">Total Postulantes</div>
                <div class="stat-val"><?php echo $stats['total']; ?></div>
            </div>
            <div class="card stat-card" style="border-left: 2px solid var(--color-wild-berry);">
                <div class="stat-label">Esquel Acelera (Urbano)</div>
                <div class="stat-val" style="color: var(--color-wild-berry);"><?php echo $stats['acelera']; ?></div>
            </div>
            <div class="card stat-card" style="border-left: 2px solid #236f4c;">
                <div class="stat-label">Raíz (Rural)</div>
                <div class="stat-val" style="color: #4ed392;"><?php echo $stats['raiz']; ?></div>
            </div>
            <div class="card stat-card" style="border-left: 2px solid #ffc107;">
                <div class="stat-label">Proyectos Aprobados</div>
                <div class="stat-val" style="color: #ffc107;"><?php echo $stats['aprobados']; ?></div>
            </div>
        </div>

        <!-- Vista toggles -->
        <div class="view-actions">
            <h2 style="margin-bottom: 0; font-size: 1.8rem;">Evaluación de Postulaciones</h2>
            <div class="view-toggles">
                <button id="btnListView" class="view-toggle-btn active">Tabla</button>
                <button id="btnCardView" class="view-toggle-btn">Tarjetas (Kanban)</button>
            </div>
        </div>

        <!-- 1. VISTA TABLA -->
        <div id="listViewContainer" class="card" style="padding: 12px; overflow: hidden;">
            <div class="table-responsive">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Responsable</th>
                            <th>Contacto</th>
                            <th>Línea</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Puntaje</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--color-text-secondary); font-style: italic; padding: 40px;">
                                    No hay postulaciones registradas en el sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): 
                                // Calcular promedio
                                $avg = ($app['rating_diferenciacion'] + $app['rating_impacto'] + $app['rating_perfil'] + $app['rating_producto_fisico'] + $app['rating_viabilidad']) / 5;
                                $app_json = rawurlencode(json_encode($app));
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($app['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['contact_name']); ?></td>
                                    <td>
                                        <div style="font-size: 0.8rem;"><?php echo htmlspecialchars($app['email']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--color-text-secondary);"><?php echo htmlspecialchars($app['phone']); ?></div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $app['program'] === 'Acelera' ? 'badge-acelera' : 'badge-raiz'; ?>" style="font-size: 0.7rem;">
                                            <?php echo $app['program']; ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--color-text-secondary);"><?php echo date('d/m/Y H:i', strtotime($app['submitted_at'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($app['stage'] === 'Preseleccionado' ? 'preseleccionado' : ($app['stage'] === 'Aprobado' ? 'aprobado' : ($app['stage'] === 'Rechazado' ? 'rechazado' : 'pendiente'))); ?>">
                                            <?php echo $app['stage']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-family: var(--font-mono); font-weight: bold; color: <?php echo $avg > 3 ? '#4ed392' : ($avg > 0 ? '#ffc107' : 'var(--color-text-secondary)'); ?>;">
                                            <?php echo $avg > 0 ? number_format($avg, 1) : '-'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button onclick="openAppDrawer(<?php echo $app['id']; ?>, '<?php echo $app_json; ?>')" class="btn btn-secondary btn-sm" style="border-color: rgba(255, 255, 255, 0.05); background-color: rgba(255,255,255,0.02);">
                                            <?php echo ($current_role === 'viewer') ? 'Ver Detalles' : 'Tomar Acción'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. VISTA TARJETAS (KANBAN) -->
        <div id="cardViewContainer" class="kanban-board" style="display: none;">
            <?php 
            $stages_def = [
                'Pendiente' => [],
                'Preseleccionado' => [],
                'Aprobado' => [],
                'Rechazado' => []
            ];
            
            // Agrupar
            foreach ($applications as $app) {
                $stages_def[$app['stage']][] = $app;
            }

            foreach ($stages_def as $stage => $stage_apps):
                $badge_class = strtolower($stage === 'Preseleccionado' ? 'preseleccionado' : ($stage === 'Aprobado' ? 'aprobado' : ($stage === 'Rechazado' ? 'rechazado' : 'pendiente')));
            ?>
                <div class="kanban-column">
                    <div class="kanban-column-header">
                        <span class="status-badge status-<?php echo $badge_class; ?>"><?php echo $stage; ?></span>
                        <span class="column-count"><?php echo count($stage_apps); ?></span>
                    </div>

                    <?php foreach ($stage_apps as $app): 
                        $avg = ($app['rating_diferenciacion'] + $app['rating_impacto'] + $app['rating_perfil'] + $app['rating_producto_fisico'] + $app['rating_viabilidad']) / 5;
                        $app_json = rawurlencode(json_encode($app));
                    ?>
                        <div class="kanban-card" onclick="openAppDrawer(<?php echo $app['id']; ?>, '<?php echo $app_json; ?>')">
                            <div class="kanban-card-title"><?php echo htmlspecialchars($app['name']); ?></div>
                            <div style="font-size: 0.8rem; color: #cbd5e1;"><?php echo htmlspecialchars($app['contact_name']); ?></div>
                            
                            <div class="kanban-card-meta">
                                <span class="status-badge <?php echo $app['program'] === 'Acelera' ? 'badge-acelera' : 'badge-raiz'; ?>" style="font-size: 0.65rem; padding: 2px 6px;">
                                    <?php echo $app['program']; ?>
                                </span>
                                <?php if ($avg > 0): ?>
                                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: bold; color: #ffc107;">★ <?php echo number_format($avg, 1); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($stage_apps)): ?>
                        <div style="text-align: center; color: var(--color-text-secondary); font-size: 0.8rem; padding: 32px 0; font-style: italic;">
                            Sin postulaciones
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 3. SECCIÓN DE CONFIGURACIÓN DE USUARIOS -->
        <section class="grid-2" style="margin-top: 64px; gap: 32px;">
            <!-- Cambio de Contraseña -->
            <div class="card">
                <h3 style="margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Cambiar mi Contraseña</h3>
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label class="form-label" for="current_password">Contraseña Actual</label>
                        <input class="form-input" type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">Nueva Contraseña</label>
                        <input class="form-input" type="password" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input class="form-input" type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Contraseña</button>
                </form>
            </div>

            <!-- Gestión de Usuarios (Solo Administrador) -->
            <?php if ($current_role === 'admin'): ?>
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Crear Nuevo Usuario</h3>
                        <form action="dashboard.php" method="POST">
                            <input type="hidden" name="create_user" value="1">
                            
                            <div class="grid-2" style="gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="new_username">Usuario</label>
                                    <input class="form-input" type="text" id="new_username" name="new_username" required placeholder="Ej: pedro">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="new_role">Rol</label>
                                    <select class="form-input" id="new_role" name="new_role" required style="background: #191b1e;">
                                        <option value="editor">Editor (Evalúa)</option>
                                        <option value="viewer">Lector (Solo mira)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="new_password_user">Contraseña Inicial</label>
                                <input class="form-input" type="password" id="new_password_user" name="new_password_user" required placeholder="Contraseña">
                            </div>

                            <button type="submit" class="btn btn-secondary" style="width: 100%; border-color: var(--color-wild-berry); color: var(--color-text-primary);">Crear Usuario</button>
                        </form>
                    </div>

                    <div style="margin-top: 32px;">
                        <h4 style="margin-bottom: 12px; font-size: 1.1rem;">Usuarios del Sistema</h4>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="crm-table" style="font-size: 0.8rem;">
                                <thead>
                                    <tr>
                                        <th style="padding: 8px 12px;">Usuario</th>
                                        <th style="padding: 8px 12px;">Rol</th>
                                        <th style="padding: 8px 12px; text-align: right;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($system_users as $sys_u): ?>
                                        <tr>
                                            <td style="padding: 8px 12px;"><strong><?php echo htmlspecialchars($sys_u['username']); ?></strong></td>
                                            <td style="padding: 8px 12px; text-transform: capitalize;"><?php echo $sys_u['role']; ?></td>
                                            <td style="padding: 8px 12px; text-align: right;">
                                                <?php if ($sys_u['id'] !== $current_user_id): ?>
                                                    <a href="dashboard.php?delete_user=<?php echo $sys_u['id']; ?>" onclick="return confirm('¿Está seguro de eliminar este usuario?');" style="color: #f87171; font-weight: 500;">Eliminar</a>
                                                <?php else: ?>
                                                    <span style="color: var(--color-text-secondary); font-style: italic;">Activo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Panel restrictivo para Editores/Lectores -->
                <div class="card" style="display: flex; align-items: center; justify-content: center; text-align: center; border-style: dashed;">
                    <div>
                        <div style="font-size: 2.5rem; margin-bottom: 16px;">🔒</div>
                        <h4>Gestión de Cuentas Restringida</h4>
                        <p style="font-size: 0.85rem; color: var(--color-text-secondary); max-width: 320px; margin: 8px auto 0 auto;">
                            Su cuenta con rol de **<?php echo $current_role; ?>** no tiene permisos para crear usuarios adicionales ni eliminar cuentas del sistema.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- DRAWER LATERAL DE DETALLES Y ACCIÓN -->
    <div id="drawerBackdrop" class="drawer-backdrop"></div>
    <div id="crmDrawer" class="drawer">
        <button id="drawerClose" class="drawer-close" title="Cerrar">&times;</button>
        
        <div class="drawer-section">
            <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.75rem;" id="drawerAppProgram">Línea de Postulación</span>
            <h2 style="font-size: 1.8rem; margin-top: 8px; margin-bottom: 4px;" id="drawerAppName">Nombre de Proyecto</h2>
            <div style="font-size: 0.85rem; color: var(--color-text-secondary);" id="drawerAppDate">Fecha de envío: 23/07/2026 12:00</div>
        </div>

        <!-- Sección de Datos del Responsable -->
        <div class="drawer-section">
            <h4 style="margin-bottom: 16px; font-size: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">Responsable de Contacto</h4>
            <div class="grid-2" style="gap: 16px 24px;">
                <div>
                    <div class="detail-label">Nombre</div>
                    <div class="detail-val" id="drawerAppContact">Juana Gómez</div>
                </div>
                <div>
                    <div class="detail-label">Teléfono</div>
                    <div class="detail-val"><a href="#" id="drawerAppPhone" style="color: var(--color-wild-berry);">2945 123456</a></div>
                </div>
            </div>
            <div style="margin-top: 12px;">
                <div class="detail-label">Correo Electrónico</div>
                <div class="detail-val"><a href="#" id="drawerAppEmail" style="color: var(--color-wild-berry); word-break: break-all;">juana@ejemplo.com</a></div>
            </div>
        </div>

        <!-- Respuestas Dinámicas al Formulario -->
        <div class="drawer-section">
            <h4 style="margin-bottom: 16px; font-size: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">Respuestas de la Postulación</h4>
            <div id="drawerAnswersContainer">
                <!-- Se inyecta dinámicamente mediante JS -->
            </div>
        </div>

        <!-- Controles de Evaluación y Notas -->
        <div class="drawer-section" style="border-bottom: none;">
            <h4 style="margin-bottom: 16px; font-size: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px;">Evaluación & Toma de Acción</h4>
            
            <?php if ($current_role === 'viewer'): ?>
                <!-- Deshabilitar edición para Lectores -->
                <div style="background-color: rgba(255,255,255,0.02); padding: 16px; border-radius: 6px; border: 1px solid var(--glass-border); font-size: 0.9rem; color: var(--color-text-secondary); margin-bottom: 16px;">
                    Su cuenta de **Lector** tiene acceso restringido en modo de solo lectura. No puede modificar las calificaciones, estados ni agregar anotaciones.
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="appStatusSelect">Estado del Proyecto</label>
                <select class="form-input" id="appStatusSelect" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?> style="background-color: #191b1e;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Preseleccionado">Preseleccionado</option>
                    <option value="Aprobado">Aprobado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <label class="form-label">Calificaciones Multidimensionales (Matriz de Ponderación)</label>
                
                <!-- Diferenciación -->
                <div class="rating-slider-group">
                    <div class="rating-header">
                        <span>Diferenciación y Diversificación (Innovación)</span>
                        <span id="sliderDiferenciacionVal" class="text-mono" style="font-weight: bold; color: var(--color-wild-berry);">0</span>
                    </div>
                    <input type="range" class="rating-slider" id="sliderDiferenciacion" min="0" max="5" value="0" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?>>
                </div>

                <!-- Impacto -->
                <div class="rating-slider-group">
                    <div class="rating-header">
                        <span>Impacto en la Matriz Turística (Derrame)</span>
                        <span id="sliderImpactoVal" class="text-mono" style="font-weight: bold; color: var(--color-wild-berry);">0</span>
                    </div>
                    <input type="range" class="rating-slider" id="sliderImpacto" min="0" max="5" value="0" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?>>
                </div>

                <!-- Perfil -->
                <div class="rating-slider-group">
                    <div class="rating-header">
                        <span>Perfil y Motivación del Emprendedor (Ganas)</span>
                        <span id="sliderPerfilVal" class="text-mono" style="font-weight: bold; color: var(--color-wild-berry);">0</span>
                    </div>
                    <input type="range" class="rating-slider" id="sliderPerfil" min="0" max="5" value="0" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?>>
                </div>

                <!-- Producto Físico -->
                <div class="rating-slider-group">
                    <div class="rating-header">
                        <span>Viabilidad de Producto Físico (Recuerdos)</span>
                        <span id="sliderProductoFisicoVal" class="text-mono" style="font-weight: bold; color: var(--color-wild-berry);">0</span>
                    </div>
                    <input type="range" class="rating-slider" id="sliderProductoFisico" min="0" max="5" value="0" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?>>
                </div>

                <!-- Viabilidad Operativa -->
                <div class="rating-slider-group">
                    <div class="rating-header">
                        <span>Viabilidad Operativa Mínima (Baja Inversión)</span>
                        <span id="sliderViabilidadVal" class="text-mono" style="font-weight: bold; color: var(--color-wild-berry);">0</span>
                    </div>
                    <input type="range" class="rating-slider" id="sliderViabilidad" min="0" max="5" value="0" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?>>
                </div>

                <!-- Promedio General -->
                <div class="rating-total">
                    <span>Puntaje Promedio General</span>
                    <span><span id="avgScoreVal" style="color: #ffc107;">0.0</span> / 5.0</span>
                </div>
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <label class="form-label" for="appNotesTextarea">Notas y Observaciones de Evaluación</label>
                <textarea class="form-input" id="appNotesTextarea" rows="4" <?php echo ($current_role === 'viewer') ? 'disabled' : ''; ?> placeholder="Escriba aquí los detalles sobre el avance, conclusiones de entrevista o cuellos de botella detectados..."></textarea>
            </div>

            <?php if ($current_role !== 'viewer'): ?>
                <button type="button" class="btn btn-primary" id="btnSaveActions" style="width: 100%; margin-top: 16px; background-color:#236f4c;">Guardar Cambios</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/crm.js"></script>
</body>
</html>
