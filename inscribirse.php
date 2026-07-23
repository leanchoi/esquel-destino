<?php
// Creado para el Laboratorio de Destino Esquel
// inscribirse.php

require_once 'admin/db_config.php';

$success = false;
$error_msg = '';

// Leer pre-selección de línea por parámetro GET
$linea_previa = isset($_GET['linea']) ? $_GET['linea'] : '';
if ($linea_previa !== 'Acelera' && $linea_previa !== 'Raiz') {
    $linea_previa = '';
}

// Procesar formulario enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos básicos
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $contact_name = isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $program = isset($_POST['program']) ? trim($_POST['program']) : '';
    
    // Compromiso horario obligatorio
    $compromiso = isset($_POST['compromiso_tiempo']) ? true : false;

    if (empty($name) || empty($contact_name) || empty($email) || empty($phone) || empty($program) || !$compromiso) {
        $error_msg = 'Por favor complete todos los datos obligatorios y acepte el compromiso de dedicación horaria.';
    } else {
        try {
            // Iniciar transacción para asegurar coherencia
            $db->beginTransaction();

            // Insertar postulante básico
            $stmt = $db->prepare("INSERT INTO applications (name, contact_name, email, phone, program, stage) 
                                  VALUES (:name, :contact_name, :email, :phone, :program, 'Pendiente')");
            $stmt->execute([
                ':name' => $name,
                ':contact_name' => $contact_name,
                ':email' => $email,
                ':phone' => $phone,
                ':program' => $program
            ]);
            
            $application_id = $db->lastInsertId();

            // Guardar detalles específicos condicionalmente
            $details = [];

            // Campo de motivación (común a ambos)
            if (isset($_POST['motivacion'])) {
                $details['motivacion'] = trim($_POST['motivacion']);
            }

            if ($program === 'Acelera') {
                if (isset($_POST['acelera_descripcion'])) $details['descripcion'] = trim($_POST['acelera_descripcion']);
                if (isset($_POST['acelera_etapa'])) $details['etapa'] = trim($_POST['acelera_etapa']);
                if (isset($_POST['acelera_producto'])) $details['producto_integracion'] = trim($_POST['acelera_producto']);
            } else if ($program === 'Raiz') {
                if (isset($_POST['raiz_tipo'])) $details['tipo_establecimiento'] = trim($_POST['raiz_tipo']);
                if (isset($_POST['raiz_proceso'])) $details['proceso_visitable'] = trim($_POST['raiz_proceso']);
                if (isset($_POST['raiz_producto'])) $details['producto_conector'] = trim($_POST['raiz_producto']);
            }

            // Guardar en la base de datos
            $stmt_detail = $db->prepare("INSERT INTO application_details (application_id, field_key, field_value) VALUES (:app_id, :key, :val)");
            foreach ($details as $key => $val) {
                $stmt_detail->execute([
                    ':app_id' => $application_id,
                    ':key' => $key,
                    ':val' => $val
                ]);
            }

            // Confirmar transacción
            $db->commit();
            $success = true;
        } catch (Exception $e) {
            $db->rollBack();
            $error_msg = 'Error al registrar la postulación: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulación · Laboratorio de Destino Esquel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-vignette"></div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/logo-lab-white.png" alt="Esquel LAB" class="logo-img">
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="index.php" class="nav-link">Inicio</a></li>
                    <li><a href="index.php#metodo" class="nav-link">Metodología</a></li>
                    <li><a href="index.php#programas" class="nav-link">Programas</a></li>
                    <li><a href="media-kit.php" class="nav-link">Sala de Prensa</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="section" style="padding-top: 140px; min-height: 90vh;">
        <div class="container">
            
            <?php if ($success): ?>
                <!-- Success State Screen -->
                <div class="card" style="max-width: 600px; margin: 40px auto; text-align: center; border-color: var(--color-wild-berry); animation: fadeIn 0.5s ease-out;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background-color: rgba(176, 42, 83, 0.1); border: 2px solid var(--color-wild-berry); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto;">
                        <span style="font-size: 2rem; color: var(--color-wild-berry); line-height: 1;">✓</span>
                    </div>
                    <h2 style="font-size: 2.2rem; margin-bottom: 16px;">¡Postulación Recibida!</h2>
                    <p style="font-size: 1.05rem; color: #e2e8f0; margin-bottom: 24px;">
                        Muchas gracias por completar la postulación a conciencia. Tu proyecto ha sido ingresado al sistema de selección del Laboratorio de Destino Esquel.
                    </p>
                    <div class="scarcity-box" style="text-align: left; background-color: rgba(255, 255, 255, 0.02);">
                        <h5 style="margin-bottom: 6px;">Próximos Pasos</h5>
                        <p style="font-size: 0.9rem; margin-bottom: 0; color: #a1a5ab;">
                            El Cuadro Técnico (CAMOCH, Prestadores y FEHGRA) evaluará las solicitudes del **23 de julio al 9 de agosto**. Los proyectos seleccionados para esta primera cohorte serán notificados el **10 de agosto** para iniciar de inmediato los trabajos técnicos territoriales.
                        </p>
                    </div>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 16px;">Volver al Inicio</a>
                </div>
            <?php else: ?>

                <!-- Form step container -->
                <div class="form-step-container">
                    <div style="text-align: center; margin-bottom: 40px;">
                        <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.85rem; font-weight: 600;">Cohorte de Fomento 2026</span>
                        <h2 style="font-size: 2.5rem; margin-top: 8px; margin-bottom: 12px;">Formulario de Postulación</h2>
                        <p style="font-size: 0.95rem; max-width: 550px; margin: 0 auto 16px auto;">
                            Por favor llene este formulario a conciencia. Evaluamos el espíritu de trabajo, compromiso e innovación para dar el acompañamiento técnico personalizado de 8 semanas.
                        </p>
                        <div style="display: inline-block; background-color: rgba(176, 42, 83, 0.15); border: 1px solid var(--color-wild-berry); border-radius: 6px; padding: 10px 20px; font-size: 0.9rem; font-weight: 500; color: var(--color-text-primary);">
                            ⚠️ Convocatoria abierta únicamente hasta el <strong>9 de agosto</strong> para esta primera cohorte.
                        </div>
                    </div>

                    <?php if (!empty($error_msg)): ?>
                        <div class="card" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; padding: 16px; margin-bottom: 24px; text-align: center;">
                            <p style="color: #f87171; margin-bottom: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($error_msg); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <!-- Step Dots indicators -->
                        <div class="step-indicator">
                            <div class="step-dot active">1</div>
                            <div class="step-dot">2</div>
                            <div class="step-dot">3</div>
                        </div>

                        <form id="postulacionForm" action="inscribirse.php" method="POST">
                            
                            <!-- ETAPA 1: Datos de contacto -->
                            <div class="form-step active">
                                <h3 style="margin-bottom: 24px;">Paso 1: Datos de Contacto</h3>
                                
                                <div class="form-group">
                                    <label class="form-label" for="name">Nombre de tu Proyecto / Emprendimiento <span>*</span></label>
                                    <input class="form-input" type="text" id="name" name="name" required placeholder="Ej: Casa de Té Las Rosas, Dulces La Estepa, Paseos del Aconcagua">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="contact_name">Nombre y Apellido del Responsable <span>*</span></label>
                                    <input class="form-input" type="text" id="contact_name" name="contact_name" required placeholder="Ej: Juana Gómez">
                                </div>

                                <div class="grid-2" style="gap: 16px;">
                                    <div class="form-group">
                                        <label class="form-label" for="email">Correo Electrónico <span>*</span></label>
                                        <input class="form-input" type="email" id="email" name="email" required placeholder="juana@ejemplo.com">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="phone">Teléfono de Contacto <span>*</span></label>
                                        <input class="form-input" type="tel" id="phone" name="phone" required placeholder="Ej: 2945 123456">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Línea del Laboratorio a la que te postulás <span>*</span></label>
                                    <div class="form-radio-group">
                                        <input type="radio" id="prog_acelera" name="program" value="Acelera" class="form-radio-input" <?php echo ($linea_previa === 'Acelera' || $linea_previa === '') ? 'checked' : ''; ?>>
                                        <label for="prog_acelera" class="form-radio-label">
                                            <span class="radio-title">Esquel Acelera <span class="status-badge badge-acelera" style="padding: 2px 8px; font-size:0.65rem;">Urbano</span></span>
                                            <span class="radio-desc">Servicios urbanos, comercios, talleres artísticos o gastronómicos dentro del ejido urbano.</span>
                                        </label>

                                        <input type="radio" id="prog_raiz" name="program" value="Raiz" class="form-radio-input" <?php echo ($linea_previa === 'Raiz') ? 'checked' : ''; ?>>
                                        <label for="prog_raiz" class="form-radio-label">
                                            <span class="radio-title">Raíz <span class="status-badge badge-raiz" style="padding: 2px 8px; font-size:0.65rem;">Rural</span></span>
                                            <span class="radio-desc">Productores de campo, chacras, lana, frutas finas, destilerías artesanales y entornos rurales.</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-navigation">
                                    <div></div>
                                    <button type="button" class="btn btn-primary btn-next">Siguiente Paso</button>
                                </div>
                            </div>

                            <!-- ETAPA 2: Preguntas condicionales -->
                            <div class="form-step">
                                <h3 style="margin-bottom: 24px;">Paso 2: Detalles de tu Propuesta</h3>

                                <!-- CAMPOS ESQUEL ACELERA -->
                                <div id="aceleraFields">
                                    <div class="form-group">
                                        <label class="form-label" for="acelera_descripcion">Descripción detallada del servicio o negocio urbano que querés convertir en experiencia turística <span>*</span></label>
                                        <textarea class="form-input" id="acelera_descripcion" name="acelera_descripcion" rows="4" placeholder="Contanos qué hacés, cómo es tu espacio y qué tiene de único tu propuesta para Esquel..."></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="acelera_etapa">¿En qué etapa de desarrollo se encuentra tu propuesta actualmente? <span>*</span></label>
                                        <input class="form-input" type="text" id="acelera_etapa" name="acelera_etapa" placeholder="Ej: Idea en mente, en operación hace 1 año, negocio tradicional sin foco turístico">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="acelera_producto">¿Cómo visualizás integrar un producto físico complementario de Esquel (Economía de los Recuerdos) en la vivencia del turista? <span>*</span></label>
                                        <textarea class="form-input" id="acelera_producto" name="acelera_producto" rows="3" placeholder="Ej: Al terminar la degustación de té, el visitante puede comprar las hebras enlatadas o vajilla cerámica artesanal local..."></textarea>
                                    </div>
                                </div>

                                <!-- CAMPOS ESQUEL RAÍZ -->
                                <div id="raizFields">
                                    <div class="form-group">
                                        <label class="form-label" for="raiz_tipo">Describí tu establecimiento rural y ubicación <span>*</span></label>
                                        <textarea class="form-input" id="raiz_tipo" name="raiz_tipo" rows="3" placeholder="Contanos qué tipo de chacra o estancia tenés, qué producen y en qué zona de Esquel/aledaños se ubica..."></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="raiz_proceso">¿Qué tareas de campo, saberes productivos tradicionales o artesanías son visitables y explicables al público? <span>*</span></label>
                                        <textarea class="form-input" id="raiz_proceso" name="raiz_proceso" rows="4" placeholder="Ej: El proceso de esquila tradicional, cosecha de berries, destilación de gin artesanal, etc..."></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="raiz_producto">¿Qué producto propio (lana, fruta fina, dulces, bebidas) querés potenciar como el objeto conector de tu experiencia rural? <span>*</span></label>
                                        <textarea class="form-input" id="raiz_producto" name="raiz_producto" rows="3" placeholder="Ej: Dulce envasado con etiquetas de la marca, madejas de lana teñidas naturalmente en el campo..."></textarea>
                                    </div>
                                </div>

                                <div class="form-navigation">
                                    <button type="button" class="btn btn-secondary btn-prev">Anterior</button>
                                    <button type="button" class="btn btn-primary btn-next">Siguiente Paso</button>
                                </div>
                            </div>

                            <!-- ETAPA 3: Motivación y compromiso -->
                            <div class="form-step">
                                <h3 style="margin-bottom: 24px;">Paso 3: Motivación y Compromiso</h3>

                                <div class="form-group">
                                    <label class="form-label" for="motivacion">Explayate sobre tu motivación y voluntad de co-crear junto al equipo técnico del Laboratorio <span>*</span></label>
                                    <textarea class="form-input" id="motivacion" name="motivacion" rows="5" required placeholder="¿Por qué te entusiasma este programa? Comentanos sobre tu perfil emprendedor y por qué valorás que hagamos cosas juntos..."></textarea>
                                </div>

                                <div class="form-group" style="margin-top: 32px; background: rgba(176, 42, 83, 0.03); padding: 24px; border-radius: 8px; border: 1px solid var(--glass-border);">
                                    <label class="checkbox-container">
                                        <input type="checkbox" id="compromiso_tiempo" name="compromiso_tiempo" class="checkbox-input" required>
                                        <span class="checkbox-text">
                                            <strong>Compromiso de dedicación horaria:</strong> Declaro conocer que la convocatoria y evaluación es del 23 de julio al 9 de agosto, y en caso de resultar seleccionado, me comprometo formalmente a **dedicar un mínimo de 12 horas semanales** al proceso de acompañamiento técnico del **10 de agosto al 2 de octubre**.
                                        </span>
                                    </label>
                                </div>

                                <div class="form-navigation">
                                    <button type="button" class="btn btn-secondary btn-prev">Anterior</button>
                                    <button type="submit" class="btn btn-primary" style="background-color: #236f4c;">Enviar Postulación</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Laboratorio de Destino Esquel. Subsecretaría de Turismo y Subsecretaría de Producción.</p>
            <p style="font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 4px;">Municipalidad de Esquel, Chubut, Patagonia Argentina.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
