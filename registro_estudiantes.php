<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

iniciar_sesion();
$pdo = db();

$errores = [];
$exito = false;
$usuarioCreado = '';
$nombreCreado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $errores['general'] = 'La sesión expiró mientras completabas el formulario. Por favor volvé a intentar.';
    } else {
        // Datos Personales
        $nombre             = trim((string) ($_POST['nombre'] ?? ''));
        $apellido           = trim((string) ($_POST['apellido'] ?? ''));
        $fechaNacimiento    = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
        $instagramUser      = trim((string) ($_POST['instagram_user'] ?? ''));
        $telefono           = trim((string) ($_POST['telefono'] ?? ''));
        $direccion          = trim((string) ($_POST['direccion'] ?? ''));
        $grupoSanguineo     = trim((string) ($_POST['grupo_sanguineo'] ?? ''));
        $contactoEmergencia = trim((string) ($_POST['contacto_emergencia'] ?? ''));

        // Situación Laboral
        $trabajaActualmente = (int) ($_POST['trabaja_actualmente'] ?? 0) === 1 ? 1 : 0;
        $trabajoLugar       = $trabajaActualmente ? trim((string) ($_POST['trabajo_lugar'] ?? '')) : '';
        $trabajoHorario     = $trabajaActualmente ? trim((string) ($_POST['trabajo_horario'] ?? '')) : '';
        $trabajoRol         = $trabajaActualmente ? trim((string) ($_POST['trabajo_rol'] ?? '')) : '';

        // Matriz de Autopercepción (1-5)
        $mLocus       = max(1, min(5, (int) ($_POST['m_locus'] ?? 3)));
        $mFrustracion = max(1, min(5, (int) ($_POST['m_frustracion'] ?? 3)));
        $mVision      = max(1, min(5, (int) ($_POST['m_vision'] ?? 3)));
        $mAmbicion    = max(1, min(5, (int) ($_POST['m_ambicion'] ?? 3)));
        $mCreatividad = max(1, min(5, (int) ($_POST['m_creatividad'] ?? 3)));
        $mAutonomia   = max(1, min(5, (int) ($_POST['m_autonomia'] ?? 3)));
        $mEmpatia     = max(1, min(5, (int) ($_POST['m_empatia'] ?? 3)));
        $mGestion     = max(1, min(5, (int) ($_POST['m_gestion'] ?? 3)));

        // Criterio Turístico
        $rPlanificacion    = trim((string) ($_POST['r_planificacion'] ?? ''));
        $rProducto         = trim((string) ($_POST['r_producto'] ?? ''));
        $rComercializacion = trim((string) ($_POST['r_comercializacion'] ?? ''));
        $rResolucion       = trim((string) ($_POST['r_resolucion'] ?? ''));

        // Credenciales
        $usernamePropuesto = trim((string) ($_POST['username'] ?? ''));
        $password          = (string) ($_POST['password'] ?? '');
        $passwordConfirm   = (string) ($_POST['password_confirm'] ?? '');

        // Validaciones obligatorias
        if ($nombre === '') $errores['nombre'] = 'Ingresá tu nombre.';
        if ($apellido === '') $errores['apellido'] = 'Ingresá tu apellido.';
        if ($fechaNacimiento === '') $errores['fecha_nacimiento'] = 'Ingresá tu fecha de nacimiento.';
        if ($instagramUser === '') $errores['instagram_user'] = 'Ingresá tu usuario de Instagram.';
        if ($telefono === '') $errores['telefono'] = 'Ingresá tu teléfono o celular.';
        if ($direccion === '') $errores['direccion'] = 'Ingresá tu dirección.';
        if ($grupoSanguineo === '') $errores['grupo_sanguineo'] = 'Seleccioná tu grupo sanguíneo.';
        if ($contactoEmergencia === '') $errores['contacto_emergencia'] = 'Ingresá un contacto de emergencia (nombre y teléfono).';

        if ($trabajaActualmente === 1) {
            if ($trabajoLugar === '') $errores['trabajo_lugar'] = 'Indicá dónde trabajás actualmente.';
            if ($trabajoHorario === '') $errores['trabajo_horario'] = 'Indicá tu horario laboral habitual.';
            if ($trabajoRol === '') $errores['trabajo_rol'] = 'Indicá qué tareas o rol desempeñás.';
        }

        if ($rPlanificacion === '') $errores['r_planificacion'] = 'Completá la respuesta sobre Planificación y Viabilidad.';
        if ($rProducto === '') $errores['r_producto'] = 'Completá la respuesta sobre Desarrollo de Producto.';
        if ($rComercializacion === '') $errores['r_comercializacion'] = 'Completá la respuesta sobre Comercialización.';
        if ($rResolucion === '') $errores['r_resolucion'] = 'Completá la respuesta sobre Pensamiento Crítico.';

        if (strlen($password) < 6) {
            $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($password !== $passwordConfirm) {
            $errores['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        // Validación y subida de Foto de Perfil
        $fotoRelPath = '';
        if (empty($_FILES['foto_perfil']['name'])) {
            $errores['foto_perfil'] = 'Por favor subí una foto de perfil clara.';
        } else {
            $fileError = $_FILES['foto_perfil']['error'];
            if ($fileError !== UPLOAD_ERR_OK) {
                $errores['foto_perfil'] = 'Hubo un error al subir la foto. Probá con otra imagen.';
            } else {
                $tmpPath = $_FILES['foto_perfil']['tmp_name'];
                $fileSize = $_FILES['foto_perfil']['size'];
                
                if ($fileSize > 8 * 1024 * 1024) {
                    $errores['foto_perfil'] = 'La foto no debe superar los 8 MB.';
                } else {
                    $imgInfo = @getimagesize($tmpPath);
                    if ($imgInfo === false) {
                        $errores['foto_perfil'] = 'El archivo subido no es una imagen válida.';
                    } else {
                        $mime = $imgInfo['mime'] ?? '';
                        $extMap = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/webp' => 'webp',
                        ];
                        if (!isset($extMap[$mime])) {
                            $errores['foto_perfil'] = 'Formato no soportado. Usá JPG, PNG o WEBP.';
                        } else {
                            $ext = $extMap[$mime];
                            $uploadDir = __DIR__ . '/uploads/estudiantes';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                        }
                    }
                }
            }
        }

        // Si no hay errores, procesar la inserción
        if (empty($errores)) {
            // Normalizar y deducir username
            $nombreLimpio = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
            $apellidoLimpio = iconv('UTF-8', 'ASCII//TRANSLIT', $apellido);
            $nombreSlug = preg_replace('/[^a-z0-9]/', '', strtolower($nombreLimpio ?: $nombre));
            $apellidoSlug = preg_replace('/[^a-z0-9]/', '', strtolower($apellidoLimpio ?: $apellido));

            $baseUsername = $nombreSlug . '.' . $apellidoSlug;
            if ($baseUsername === '.') {
                $baseUsername = 'estudiante.' . time();
            }

            // Manejo de duplicados: concatenar número incremental
            $usernameFinal = $baseUsername;
            $i = 1;
            $chk = $pdo->prepare("SELECT 1 FROM users WHERE LOWER(username) = ? UNION SELECT 1 FROM lab_consultores WHERE LOWER(id) = ?");
            while (true) {
                $chk->execute([strtolower($usernameFinal), 'est-' . strtolower($usernameFinal)]);
                if (!$chk->fetchColumn()) {
                    break;
                }
                $i++;
                $usernameFinal = $baseUsername . $i;
            }

            // Mover la foto con el username y hash único
            $hashFoto = substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
            $nombreArchivo = $usernameFinal . '_' . $hashFoto . '.' . $ext;
            $destinoFisico = $uploadDir . '/' . $nombreArchivo;

            if (!move_uploaded_file($tmpPath, $destinoFisico)) {
                $errores['foto_perfil'] = 'No se pudo guardar la foto de perfil en el servidor.';
            } else {
                $fotoRelPath = 'uploads/estudiantes/' . $nombreArchivo;

                // Transacción para Inserción Dual e Inserción en Users
                try {
                    $pdo->beginTransaction();

                    $passHash = password_hash($password, PASSWORD_DEFAULT);

                    // 1. Inserción en users (para autenticación en el panel /admin)
                    $stUser = $pdo->prepare("
                        INSERT INTO users (username, password, role, must_change_password, created_at)
                        VALUES (?, ?, 'estudiante', 0, datetime('now'))
                    ");
                    $stUser->execute([$usernameFinal, $passHash]);
                    $numericUserId = (int) $pdo->lastInsertId();

                    // 2. Inserción en lab_consultores (rol = 0, CERO, innegociable, tipo = 'estudiante')
                    $consultorId = 'est-' . $usernameFinal;
                    $nombreCompleto = trim($nombre . ' ' . $apellido);
                    $stCons = $pdo->prepare("
                        INSERT INTO lab_consultores (id, user_id, nombre, password, rol, tipo, color, activo, telefono)
                        VALUES (?, ?, ?, ?, '0', 'estudiante', '#2F5D7C', 1, ?)
                    ");
                    $stCons->execute([$consultorId, $usernameFinal, $nombreCompleto, $passHash, $telefono]);

                    // 3. Inserción en lab_estudiantes con todo el perfil, matriz y respuestas
                    $stEst = $pdo->prepare("
                        INSERT INTO lab_estudiantes (
                            consultor_id, user_id, nombre, apellido, fecha_nacimiento,
                            instituto, legajo, horas_presupuesto, alta_desde, activo,
                            telefono, direccion, grupo_sanguineo, contacto_emergencia, instagram_user,
                            trabaja_actualmente, trabajo_horario, trabajo_lugar, trabajo_rol,
                            foto_perfil,
                            m_locus, m_frustracion, m_vision, m_ambicion, m_creatividad, m_autonomia, m_empatia, m_gestion,
                            r_planificacion, r_producto, r_comercializacion, r_resolucion
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            'ISET 815', '', 60, '2026-10-01', 1,
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?,
                            ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?
                        )
                    ");
                    $stEst->execute([
                        $consultorId, $numericUserId, $nombre, $apellido, $fechaNacimiento,
                        $telefono, $direccion, $grupoSanguineo, $contactoEmergencia, $instagramUser,
                        $trabajaActualmente, $trabajoHorario, $trabajoLugar, $trabajoRol,
                        $fotoRelPath,
                        $mLocus, $mFrustracion, $mVision, $mAmbicion, $mCreatividad, $mAutonomia, $mEmpatia, $mGestion,
                        $rPlanificacion, $rProducto, $rComercializacion, $rResolucion
                    ]);

                    $pdo->commit();
                    $exito = true;
                    $usuarioCreado = $usernameFinal;
                    $nombreCreado = $nombreCompleto;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    if (file_exists($destinoFisico)) {
                        @unlink($destinoFisico);
                    }
                    error_log('[registro_estudiantes] Error: ' . $e->getMessage());
                    $errores['general'] = 'Ocurrió un error al guardar tu postulación. Por favor intentá de nuevo.';
                }
            }
        }
    }
}

$pageTitle = 'Onboarding Estudiantes · Convenio ISET 815 en Esquel LAB';
$pageDescription = 'Formulario oficial de registro y perfilado de estudiantes de la Tecnicatura Superior en Turismo (ISET 815) para Esquel LAB.';
require __DIR__ . '/includes/header.php';
?>

<style>
/* Estilos del Wizard de Onboarding */
.onboarding-wrap {
  max-width: 780px;
  margin: 40px auto 80px;
  padding: 0 20px;
}
.wizard-card {
  background: var(--surface, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 16px;
  padding: 36px 32px;
  box-shadow: 0 10px 30px -10px rgba(0,0,0,0.07);
}
@media (max-width: 640px) {
  .wizard-card { padding: 24px 18px; }
}

/* Indicador de pasos */
.wizard-stepper {
  display: flex;
  justify-content: space-between;
  position: relative;
  margin-bottom: 32px;
}
.wizard-stepper::before {
  content: "";
  position: absolute;
  top: 18px;
  left: 20px;
  right: 20px;
  height: 3px;
  background: var(--border, #e2e8f0);
  z-index: 1;
}
.step-item {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  text-align: center;
}
.step-bubble {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--surface, #ffffff);
  border: 2px solid var(--border, #cbd5e1);
  color: var(--ink-3, #94a3b8);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.25s ease;
}
.step-item.is-active .step-bubble {
  background: #2F5D7C;
  border-color: #2F5D7C;
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(47, 93, 124, 0.18);
}
.step-item.is-complete .step-bubble {
  background: #10b981;
  border-color: #10b981;
  color: #ffffff;
}
.step-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--ink-3, #64748b);
  margin-top: 8px;
}
.step-item.is-active .step-label {
  color: #2F5D7C;
  font-weight: 700;
}

/* Encabezados de paso */
.step-header {
  margin-bottom: 24px;
  border-bottom: 1px solid var(--border, #e2e8f0);
  padding-bottom: 16px;
}
.step-header h2 {
  font-size: 22px;
  margin: 0 0 6px 0;
  color: var(--ink-1, #1e293b);
  font-family: var(--font-title, serif);
}
.step-header p {
  margin: 0;
  color: var(--ink-2, #64748b);
  font-size: 14.5px;
}

/* Campos de Formulario */
.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}
@media (max-width: 600px) {
  .form-grid-2 { grid-template-columns: 1fr; }
}
.field-group {
  margin-bottom: 20px;
}
.field-label {
  display: block;
  font-size: 13.5px;
  font-weight: 700;
  margin-bottom: 6px;
  color: var(--ink-1, #334155);
}
.field-label .req {
  color: #e11d48;
}
.input-text, .select-custom, .textarea-custom {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid var(--border, #cbd5e1);
  border-radius: 8px;
  font-size: 14.5px;
  color: var(--ink-1, #0f172a);
  background: var(--surface-alt, #f8fafc);
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.input-text:focus, .select-custom:focus, .textarea-custom:focus {
  outline: none;
  border-color: #2F5D7C;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(47, 93, 124, 0.15);
}
.field-help {
  font-size: 12px;
  color: var(--ink-3, #64748b);
  margin-top: 4px;
}
.field-error {
  font-size: 12px;
  color: #e11d48;
  margin-top: 4px;
  font-weight: 600;
}

/* Subida de Foto */
.upload-box {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 16px;
  background: #f8fafc;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.upload-box:hover {
  border-color: #2F5D7C;
  background: #f1f5f9;
}
.upload-preview {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: #e2e8f0;
  object-fit: cover;
  flex-shrink: 0;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.upload-text {
  flex: 1;
}

/* Radio buttons de trabajo */
.radio-toggle-group {
  display: flex;
  gap: 16px;
  margin-top: 6px;
}
.radio-label-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: 1px solid var(--border, #cbd5e1);
  border-radius: 8px;
  cursor: pointer;
  background: #f8fafc;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}
.radio-label-card input[type="radio"] {
  accent-color: #2F5D7C;
}
.radio-label-card:hover {
  border-color: #2F5D7C;
}

/* Sliders de Personalidad */
.matrix-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px 20px;
  margin-bottom: 18px;
}
.matrix-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.matrix-dimension-name {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
}
.matrix-badge-val {
  background: #2F5D7C;
  color: #ffffff;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 0.5px;
}
.slider-control {
  width: 100%;
  accent-color: #2F5D7C;
  height: 6px;
  cursor: pointer;
  margin: 10px 0;
}
.matrix-extremes {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  font-size: 12px;
  line-height: 1.35;
}
.extreme-left {
  color: #64748b;
  max-width: 46%;
  text-align: left;
}
.extreme-right {
  color: #2F5D7C;
  max-width: 46%;
  text-align: right;
  font-weight: 600;
}

/* Textareas de Criterio */
.textarea-custom {
  min-height: 120px;
  resize: vertical;
  line-height: 1.5;
}
.prompt-box {
  background: #eff6ff;
  border-left: 4px solid #2F5D7C;
  padding: 14px 18px;
  border-radius: 0 8px 8px 0;
  margin-bottom: 24px;
}
.prompt-box p {
  margin: 0;
  font-size: 14.5px;
  color: #1e3a8a;
  font-weight: 600;
}

/* Credenciales */
.user-preview-card {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 24px;
  text-align: center;
}
.user-preview-card .u-tag {
  font-size: 20px;
  font-weight: 800;
  color: #166534;
  font-family: monospace;
  background: #ffffff;
  padding: 4px 14px;
  border-radius: 6px;
  border: 1px solid #86efac;
  display: inline-block;
  margin-top: 8px;
}

/* Botonera de Navegación */
.wizard-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--border, #e2e8f0);
}
.btn-wizard-next {
  background: #2F5D7C;
  color: #ffffff;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 15px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.btn-wizard-next:hover {
  background: #23475f;
  transform: translateY(-1px);
}
.btn-wizard-prev {
  background: transparent;
  color: #64748b;
  padding: 12px 18px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-wizard-prev:hover {
  background: #f1f5f9;
  color: #334155;
}

/* Ocultamiento de pasos */
.wizard-step {
  display: block;
}
.wizard-step.is-hidden {
  display: none !important;
}

/* Pantalla de Éxito */
.success-card {
  text-align: center;
  padding: 40px 24px;
}
.success-icon {
  width: 72px;
  height: 72px;
  background: #dcfce7;
  color: #15803d;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}
</style>

<div class="onboarding-wrap">

<?php if ($exito): ?>

  <div class="wizard-card success-card">
    <div class="success-icon">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <h1 style="font-family:var(--font-title, serif);font-size:28px;margin:0 0 12px 0;">¡Postulación recibida con éxito!</h1>
    <p style="font-size:16px;color:#475569;max-width:540px;margin:0 auto 24px;">
      Muchas gracias, <strong><?= e($nombreCreado) ?></strong>. Tu perfil psicológico, técnico y de criterio turístico ha sido registrado correctamente en la plataforma de <strong>Esquel LAB</strong>.
    </p>

    <div class="user-preview-card" style="max-width:440px;margin:0 auto 28px;">
      <p style="margin:0;font-size:14px;color:#166534;font-weight:600;">Tus datos de acceso para el panel:</p>
      <div class="u-tag"><?= e($usuarioCreado) ?></div>
      <p style="margin:10px 0 0 0;font-size:12.5px;color:#15803d;">
        La contraseña es la que elegiste en el paso 4.
      </p>
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;max-width:540px;margin:0 auto 30px;text-align:left;font-size:13.5px;color:#475569;line-height:1.5;">
      <strong>¿Cómo sigue el proceso?</strong><br>
      La conducción del programa evaluará los perfiles para realizar la asignación estratégica 1 a 1 a los 18 emprendimientos turísticos acelerados. Una vez confirmada la nómina, tu usuario quedará habilitado para consultar tu emprendimiento y consignas de acompañamiento.
    </div>

    <a href="admin/login.php" class="btn-wizard-next" style="text-decoration:none;">
      Ir al Acceso del Sistema →
    </a>
  </div>

<?php else: ?>

  <div class="wizard-card">

    <div style="text-align:center;margin-bottom:28px;">
      <span style="font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#2F5D7C;background:#e2ecf2;padding:4px 12px;border-radius:20px;display:inline-block;margin-bottom:8px;">
        Convenio ISET 815 · Esquel LAB
      </span>
      <h1 style="font-family:var(--font-title, serif);font-size:27px;margin:0 0 6px 0;">Onboarding de Prácticas Profesionalizantes</h1>
      <p style="color:#64748b;font-size:15px;margin:0;">
        Completá tus datos, autopercepción y visión para asignarte estratégicamente a uno de los 18 emprendimientos.
      </p>
    </div>

    <?php if (!empty($errores['general'])): ?>
      <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:14px 18px;border-radius:8px;font-size:14px;margin-bottom:24px;">
        <?= e($errores['general']) ?>
      </div>
    <?php endif; ?>

    <!-- Indicador de pasos -->
    <div class="wizard-stepper">
      <div class="step-item is-active" id="stepIndicator1">
        <div class="step-bubble">1</div>
        <div class="step-label">Datos</div>
      </div>
      <div class="step-item" id="stepIndicator2">
        <div class="step-bubble">2</div>
        <div class="step-label">Matriz</div>
      </div>
      <div class="step-item" id="stepIndicator3">
        <div class="step-bubble">3</div>
        <div class="step-label">Criterio</div>
      </div>
      <div class="step-item" id="stepIndicator4">
        <div class="step-bubble">4</div>
        <div class="step-label">Acceso</div>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" id="formOnboarding" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="username" id="inputHiddenUsername" value="<?= e($_POST['username'] ?? '') ?>">

      <!-- ================= PASO 1 ================= -->
      <div class="wizard-step" id="paso1">
        <div class="step-header">
          <h2>Paso 1 · Datos Personales y Situación</h2>
          <p>Tus datos de contacto, emergencia y situación laboral actual.</p>
        </div>

        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="nombre">Nombre <span class="req">*</span></label>
            <input type="text" id="nombre" name="nombre" class="input-text" value="<?= e($_POST['nombre'] ?? '') ?>" placeholder="Ej: Leandro" required>
            <?php if (!empty($errores['nombre'])): ?><div class="field-error"><?= e($errores['nombre']) ?></div><?php endif; ?>
          </div>
          <div class="field-group">
            <label class="field-label" for="apellido">Apellido <span class="req">*</span></label>
            <input type="text" id="apellido" name="apellido" class="input-text" value="<?= e($_POST['apellido'] ?? '') ?>" placeholder="Ej: Choi" required>
            <?php if (!empty($errores['apellido'])): ?><div class="field-error"><?= e($errores['apellido']) ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="fecha_nacimiento">Fecha de Nacimiento <span class="req">*</span></label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input-text" value="<?= e($_POST['fecha_nacimiento'] ?? '') ?>" required>
            <?php if (!empty($errores['fecha_nacimiento'])): ?><div class="field-error"><?= e($errores['fecha_nacimiento']) ?></div><?php endif; ?>
          </div>
          <div class="field-group">
            <label class="field-label" for="instagram_user">Usuario de Instagram <span class="req">*</span></label>
            <input type="text" id="instagram_user" name="instagram_user" class="input-text" value="<?= e($_POST['instagram_user'] ?? '') ?>" placeholder="@tu.usuario" required>
            <?php if (!empty($errores['instagram_user'])): ?><div class="field-error"><?= e($errores['instagram_user']) ?></div><?php endif; ?>
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Foto de Perfil <span class="req">*</span></label>
          <label class="upload-box" for="foto_perfil">
            <img src="assets/images/placeholder-avatar.svg" id="previewFoto" class="upload-preview" alt="Foto">
            <div class="upload-text">
              <strong style="color:#1e293b;font-size:14px;display:block;">Seleccionar imagen clara</strong>
              <span class="field-help" style="margin:0;">Formatos JPG, PNG o WEBP (máximo 8 MB). Rostro visible de frente.</span>
            </div>
            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display:none;" <?= empty($errores) ? 'required' : '' ?>>
          </label>
          <?php if (!empty($errores['foto_perfil'])): ?><div class="field-error"><?= e($errores['foto_perfil']) ?></div><?php endif; ?>
        </div>

        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="telefono">Teléfono / WhatsApp <span class="req">*</span></label>
            <input type="tel" id="telefono" name="telefono" class="input-text" value="<?= e($_POST['telefono'] ?? '') ?>" placeholder="Ej: 2945 123456" required>
            <?php if (!empty($errores['telefono'])): ?><div class="field-error"><?= e($errores['telefono']) ?></div><?php endif; ?>
          </div>
          <div class="field-group">
            <label class="field-label" for="direccion">Dirección en Esquel / Trevelin <span class="req">*</span></label>
            <input type="text" id="direccion" name="direccion" class="input-text" value="<?= e($_POST['direccion'] ?? '') ?>" placeholder="Ej: San Martín 450" required>
            <?php if (!empty($errores['direccion'])): ?><div class="field-error"><?= e($errores['direccion']) ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="grupo_sanguineo">Grupo Sanguíneo <span class="req">*</span></label>
            <select id="grupo_sanguineo" name="grupo_sanguineo" class="select-custom" required>
              <option value="">Seleccioná una opción...</option>
              <?php foreach (['0+', '0-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'No lo sé'] as $gs): ?>
                <option value="<?= $gs ?>" <?= ($_POST['grupo_sanguineo'] ?? '') === $gs ? 'selected' : '' ?>><?= $gs ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errores['grupo_sanguineo'])): ?><div class="field-error"><?= e($errores['grupo_sanguineo']) ?></div><?php endif; ?>
          </div>
          <div class="field-group">
            <label class="field-label" for="contacto_emergencia">Contacto de Emergencia <span class="req">*</span></label>
            <input type="text" id="contacto_emergencia" name="contacto_emergencia" class="input-text" value="<?= e($_POST['contacto_emergencia'] ?? '') ?>" placeholder="Ej: Mamá (María) - 2945 654321" required>
            <?php if (!empty($errores['contacto_emergencia'])): ?><div class="field-error"><?= e($errores['contacto_emergencia']) ?></div><?php endif; ?>
          </div>
        </div>

        <!-- Situación Laboral -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:18px;border-radius:12px;">
          <label class="field-label">¿Trabajás actualmente? <span class="req">*</span></label>
          <div class="radio-toggle-group">
            <label class="radio-label-card">
              <input type="radio" name="trabaja_actualmente" value="0" <?= (int)($_POST['trabaja_actualmente'] ?? 0) === 0 ? 'checked' : '' ?>>
              <span>No, dedicación exclusiva</span>
            </label>
            <label class="radio-label-card">
              <input type="radio" name="trabaja_actualmente" value="1" <?= (int)($_POST['trabaja_actualmente'] ?? 0) === 1 ? 'checked' : '' ?>>
              <span>Sí, tengo un empleo</span>
            </label>
          </div>

          <div id="bloqueTrabajoExtra" style="display:<?= (int)($_POST['trabaja_actualmente'] ?? 0) === 1 ? 'block' : 'none' ?>;margin-top:18px;padding-top:16px;border-top:1px dashed #cbd5e1;">
            <div class="form-grid-2">
              <div class="field-group">
                <label class="field-label" for="trabajo_lugar">¿Dónde trabajás? <span class="req">*</span></label>
                <input type="text" id="trabajo_lugar" name="trabajo_lugar" class="input-text" value="<?= e($_POST['trabajo_lugar'] ?? '') ?>" placeholder="Empresa, comercio o institución">
                <?php if (!empty($errores['trabajo_lugar'])): ?><div class="field-error"><?= e($errores['trabajo_lugar']) ?></div><?php endif; ?>
              </div>
              <div class="field-group">
                <label class="field-label" for="trabajo_horario">¿En qué horario? <span class="req">*</span></label>
                <input type="text" id="trabajo_horario" name="trabajo_horario" class="input-text" value="<?= e($_POST['trabajo_horario'] ?? '') ?>" placeholder="Ej: Lunes a Viernes 8 a 13 hs">
                <?php if (!empty($errores['trabajo_horario'])): ?><div class="field-error"><?= e($errores['trabajo_horario']) ?></div><?php endif; ?>
              </div>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="trabajo_rol">¿Qué hacés ahí? (Rol o tareas) <span class="req">*</span></label>
              <input type="text" id="trabajo_rol" name="trabajo_rol" class="input-text" value="<?= e($_POST['trabajo_rol'] ?? '') ?>" placeholder="Ej: Atención al público, mozo, administración">
              <?php if (!empty($errores['trabajo_rol'])): ?><div class="field-error"><?= e($errores['trabajo_rol']) ?></div><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="wizard-footer" style="justify-content:flex-end;">
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(2)">
            Continuar a Autopercepción →
          </button>
        </div>
      </div>

      <!-- ================= PASO 2 ================= -->
      <div class="wizard-step is-hidden" id="paso2">
        <div class="step-header">
          <h2>Paso 2 · Matriz de Autopercepción</h2>
          <p>Mové los sliders del 1 al 5 según tu forma real de actuar y trabajar. No hay respuestas malas: buscamos honestidad para asignarte el emprendimiento indicado.</p>
        </div>

        <?php
        $dimensiones = [
            'm_locus' => [
                'titulo' => 'Iniciativa',
                'izq' => 'Me siento mas comodo/a cuando me inidcan qué hacer',
                'der' => 'Propongo ideas y asumo el control de mi área',
            ],
            'm_frustracion' => [
                'titulo' => 'Tolerancia a la Frustración',
                'izq' => 'Me desmotivo rápido ante un obstáculo',
                'der' => 'Recalculo y busco alternativas hasta resolverlo',
            ],
            'm_vision' => [
                'titulo' => 'Visión de Sistema',
                'izq' => 'Me enfoco solo en mi tarea aislada',
                'der' => 'Entiendo cómo mi trabajo impacta en todo el proyecto',
            ],
            'm_ambicion' => [
                'titulo' => 'Ambición Profesional',
                'izq' => 'Me conformo con cumplir lo básico aprobado',
                'der' => 'Busco absorber responsabilidades y destacarme',
            ],
            'm_creatividad' => [
                'titulo' => 'Creatividad Operativa',
                'izq' => 'Prefiero seguir manuales y recetas conocidas',
                'der' => 'Invento soluciones laterales con los recursos que tengo',
            ],
            'm_autonomia' => [
                'titulo' => 'Autonomía de Decisión',
                'izq' => 'Necesito validar cada paso antes de avanzar',
                'der' => 'Tomo decisiones fundamentadas y luego informo',
            ],
            'm_empatia' => [
                'titulo' => 'Empatía Comercial',
                'izq' => 'Me centro en las características técnicas del producto',
                'der' => 'Entiendo instintivamente lo que el turista realmente valora',
            ],
            'm_gestion' => [
                'titulo' => 'Gestión del Tiempo',
                'izq' => 'Suelo reaccionar sobre la marcha a las urgencias',
                'der' => 'Planifico, priorizo y cumplo estrictamente los plazos',
            ],
        ];

        foreach ($dimensiones as $k => $d):
            $val = (int) ($_POST[$k] ?? 3);
        ?>
          <div class="matrix-item">
            <div class="matrix-title-row">
              <span class="matrix-dimension-name"><?= e($d['titulo']) ?></span>
              <span class="matrix-badge-val" id="badge_<?= $k ?>">Nivel <?= $val ?></span>
            </div>
            <input type="range" min="1" max="5" step="1" name="<?= $k ?>" id="range_<?= $k ?>" value="<?= $val ?>" class="slider-control" oninput="actualizarSliderBadge('<?= $k ?>', this.value)">
            <div class="matrix-extremes">
              <span class="extreme-left">1: "<?= e($d['izq']) ?>"</span>
              <span class="extreme-right">5: "<?= e($d['der']) ?>"</span>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(1)">
            ← Volver a Datos
          </button>
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(3)">
            Continuar a Criterio Turístico →
          </button>
        </div>
      </div>

      <!-- ================= PASO 3 ================= -->
      <div class="wizard-step is-hidden" id="paso3">
        <div class="step-header">
          <h2>Paso 3 · Criterio Turístico y Profesional</h2>
          <p>Queremos conocer tu capacidad analítica y tu mirada técnica sobre el destino.</p>
        </div>

        <div class="prompt-box">
          <p>💡 Queremos conocer tu forma de pensar. Desarrollá tus respuestas.</p>
        </div>

        <div class="field-group">
          <label class="field-label" for="r_planificacion">
            1. Planificación y Viabilidad <span class="req">*</span>
          </label>
          <p class="field-help" style="margin-bottom:8px;">
            "Si un emprendedor local quiere abrir un servicio de turismo activo fuera de la temporada alta, ¿qué 3 variables críticas de riesgo y viabilidad analizarías antes de decirle que avance?"
          </p>
          <textarea id="r_planificacion" name="r_planificacion" class="textarea-custom" placeholder="Detallá las 3 variables de riesgo y tu fundamentación..." required><?= e($_POST['r_planificacion'] ?? '') ?></textarea>
          <?php if (!empty($errores['r_planificacion'])): ?><div class="field-error"><?= e($errores['r_planificacion']) ?></div><?php endif; ?>
        </div>

        <div class="field-group">
          <label class="field-label" for="r_producto">
            2. Desarrollo de Producto <span class="req">*</span>
          </label>
          <p class="field-help" style="margin-bottom:8px;">
            "Elegí un recurso natural o cultural subexplotado de la región. Describí brevemente un producto turístico innovador basado en él que atraiga a un público diferente al tradicional."
          </p>
          <textarea id="r_producto" name="r_producto" class="textarea-custom" placeholder="Describí el recurso, el público objetivo y la experiencia que proponés..." required><?= e($_POST['r_producto'] ?? '') ?></textarea>
          <?php if (!empty($errores['r_producto'])): ?><div class="field-error"><?= e($errores['r_producto']) ?></div><?php endif; ?>
        </div>

        <div class="field-group">
          <label class="field-label" for="r_comercializacion">
            3. Comercialización <span class="req">*</span>
          </label>
          <p class="field-help" style="margin-bottom:8px;">
            "Muchos prestadores excelentes solo venden por el 'boca a boca'. ¿Cuál sería tu estrategia paso a paso para digitalizar sus ventas sin que pierdan su identidad local?"
          </p>
          <textarea id="r_comercializacion" name="r_comercializacion" class="textarea-custom" placeholder="Explicá las etapas para incorporar herramientas digitales preservando su esencia..." required><?= e($_POST['r_comercializacion'] ?? '') ?></textarea>
          <?php if (!empty($errores['r_comercializacion'])): ?><div class="field-error"><?= e($errores['r_comercializacion']) ?></div><?php endif; ?>
        </div>

        <div class="field-group">
          <label class="field-label" for="r_resolucion">
            4. Pensamiento Crítico <span class="req">*</span>
          </label>
          <p class="field-help" style="margin-bottom:8px;">
            "Contame de una situación real donde tuviste que resolver un problema complejo con muy pocos recursos. ¿Cómo lo abordaste y qué resultado obtuviste?"
          </p>
          <textarea id="r_resolucion" name="r_resolucion" class="textarea-custom" placeholder="Relatá el contexto, el obstáculo y la solución que encontraste..." required><?= e($_POST['r_resolucion'] ?? '') ?></textarea>
          <?php if (!empty($errores['r_resolucion'])): ?><div class="field-error"><?= e($errores['r_resolucion']) ?></div><?php endif; ?>
        </div>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(2)">
            ← Volver a Matriz
          </button>
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(4)">
            Generar Credenciales →
          </button>
        </div>
      </div>

      <!-- ================= PASO 4 ================= -->
      <div class="wizard-step is-hidden" id="paso4">
        <div class="step-header">
          <h2>Paso 4 · Generación de Credenciales</h2>
          <p>Definí la contraseña con la que ingresarás al panel de acompañamiento técnico.</p>
        </div>

        <div class="user-preview-card">
          <p style="margin:0;font-size:15px;color:#1e293b;">
            Tu usuario generado es:
          </p>
          <div class="u-tag" id="labelUsernameGenerado">estudiante</div>
          <p style="margin:10px 0 0 0;font-size:13.5px;color:#475569;">
            Por favor, elegí tu contraseña para completar la postulación.
          </p>
        </div>

        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="password">Contraseña <span class="req">*</span></label>
            <input type="password" id="password" name="password" class="input-text" minlength="6" placeholder="Mínimo 6 caracteres" required>
            <?php if (!empty($errores['password'])): ?><div class="field-error"><?= e($errores['password']) ?></div><?php endif; ?>
          </div>
          <div class="field-group">
            <label class="field-label" for="password_confirm">Confirmar Contraseña <span class="req">*</span></label>
            <input type="password" id="password_confirm" name="password_confirm" class="input-text" minlength="6" placeholder="Repetí tu contraseña" required>
            <?php if (!empty($errores['password_confirm'])): ?><div class="field-error"><?= e($errores['password_confirm']) ?></div><?php endif; ?>
          </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:14px 18px;border-radius:8px;font-size:13px;color:#64748b;margin-bottom:24px;">
          🔒 Al presionar "Finalizar y Enviar Postulación", tus datos serán enviados a la coordinación del Esquel LAB y tu cuenta de acceso quedará registrada.
        </div>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(3)">
            ← Volver a Criterio
          </button>
          <button type="submit" class="btn-wizard-next" style="background:#15803d;" id="btnEnviarPostulacion">
            Finalizar y Enviar Postulación ✓
          </button>
        </div>
      </div>

    </form>
  </div>

<?php endif; ?>

</div>

<script>
// Estado del Wizard
let pasoActual = 1;

function actualizarSliderBadge(nombre, val) {
  const b = document.getElementById('badge_' + nombre);
  if (b) b.textContent = 'Nivel ' + val;
}

function normalizarSlug(str) {
  return str.normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]/g, "");
}

function calcularUsername() {
  const nom = normalizarSlug(document.getElementById('nombre').value.trim());
  const ape = normalizarSlug(document.getElementById('apellido').value.trim());
  let user = 'estudiante';
  if (nom && ape) {
    user = nom + '.' + ape;
  } else if (nom) {
    user = nom;
  } else if (ape) {
    user = ape;
  }
  document.getElementById('labelUsernameGenerado').textContent = user;
  document.getElementById('inputHiddenUsername').value = user;
}

function validarPaso(n) {
  if (n === 1) {
    const campos = ['nombre', 'apellido', 'fecha_nacimiento', 'instagram_user', 'telefono', 'direccion', 'grupo_sanguineo', 'contacto_emergencia'];
    for (let c of campos) {
      const el = document.getElementById(c);
      if (!el || !el.value.trim()) {
        alert('Por favor completá todos los campos obligatorios del Paso 1.');
        el && el.focus();
        return false;
      }
    }
    const foto = document.getElementById('foto_perfil');
    // Si no se seleccionó foto y es nuevo envío
    const prevSrc = document.getElementById('previewFoto').getAttribute('src');
    if ((!foto || !foto.files || !foto.files[0]) && prevSrc.indexOf('placeholder') !== -1) {
      alert('Por favor subí una foto de perfil clara.');
      foto && foto.focus();
      return false;
    }

    const trabaja = document.querySelector('input[name="trabaja_actualmente"]:checked');
    if (trabaja && trabaja.value === '1') {
      const elLugar = document.getElementById('trabajo_lugar');
      const elHorario = document.getElementById('trabajo_horario');
      const elRol = document.getElementById('trabajo_rol');
      if (!elLugar.value.trim() || !elHorario.value.trim() || !elRol.value.trim()) {
        alert('Por favor completá los 3 datos de tu empleo actual.');
        return false;
      }
    }
  }

  if (n === 3) {
    const r1 = document.getElementById('r_planificacion').value.trim();
    const r2 = document.getElementById('r_producto').value.trim();
    const r3 = document.getElementById('r_comercializacion').value.trim();
    const r4 = document.getElementById('r_resolucion').value.trim();

    if (!r1 || !r2 || !r3 || !r4) {
      alert('Por favor desarrollá tus respuestas a las 4 preguntas de criterio turístico.');
      return false;
    }
  }

  return true;
}

function irAlPaso(destino) {
  if (destino > pasoActual) {
    if (!validarPaso(pasoActual)) {
      return;
    }
  }

  // Ocultar todos los pasos
  for (let i = 1; i <= 4; i++) {
    const stepEl = document.getElementById('paso' + i);
    const indEl = document.getElementById('stepIndicator' + i);
    if (stepEl) stepEl.classList.add('is-hidden');
    if (indEl) {
      indEl.classList.remove('is-active');
      if (i < destino) {
        indEl.classList.add('is-complete');
      } else {
        indEl.classList.remove('is-complete');
      }
    }
  }

  // Si va al paso 4, calcular username
  if (destino === 4) {
    calcularUsername();
  }

  // Mostrar el paso de destino
  const destinoEl = document.getElementById('paso' + destino);
  const destinoInd = document.getElementById('stepIndicator' + destino);
  if (destinoEl) destinoEl.classList.remove('is-hidden');
  if (destinoInd) destinoInd.classList.add('is-active');

  pasoActual = destino;
  window.scrollTo({ top: 120, behavior: 'smooth' });
}

// Escuchar cambio en foto de perfil para preview inmediato
document.addEventListener('DOMContentLoaded', () => {
  const inputFoto = document.getElementById('foto_perfil');
  const imgPreview = document.getElementById('previewFoto');
  if (inputFoto && imgPreview) {
    inputFoto.addEventListener('change', () => {
      const file = inputFoto.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          imgPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Toggle de campos laborales
  const radiosTrabajo = document.querySelectorAll('input[name="trabaja_actualmente"]');
  const bloqueExtra = document.getElementById('bloqueTrabajoExtra');
  radiosTrabajo.forEach(r => {
    r.addEventListener('change', () => {
      if (bloqueExtra) {
        bloqueExtra.style.display = (r.value === '1') ? 'block' : 'none';
      }
    });
  });

  // Validación final en submit
  const form = document.getElementById('formOnboarding');
  if (form) {
    form.addEventListener('submit', (e) => {
      const p1 = document.getElementById('password').value;
      const p2 = document.getElementById('password_confirm').value;
      if (p1.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres.');
        e.preventDefault();
        return;
      }
      if (p1 !== p2) {
        alert('Las contraseñas no coinciden.');
        e.preventDefault();
        return;
      }
    });
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
