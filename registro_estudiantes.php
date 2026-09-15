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

        // Datos Operativos / Logísticos
        $movilidad          = trim((string) ($_POST['movilidad'] ?? ''));
        $dispositivoTrabajo = trim((string) ($_POST['dispositivo_trabajo'] ?? ''));

        // Situación Laboral
        $trabajaActualmente = (int) ($_POST['trabaja_actualmente'] ?? 0) === 1 ? 1 : 0;
        $trabajoLugar       = $trabajaActualmente ? trim((string) ($_POST['trabajo_lugar'] ?? '')) : '';
        $trabajoHorario     = $trabajaActualmente ? trim((string) ($_POST['trabajo_horario'] ?? '')) : '';
        $trabajoRol         = $trabajaActualmente ? trim((string) ($_POST['trabajo_rol'] ?? '')) : '';

        // Matriz de Trabajo (8 Filas · 1 a 5)
        $mArrancar   = max(1, min(5, (int) ($_POST['m_arrancar'] ?? 3)));
        $mTraba      = max(1, min(5, (int) ($_POST['m_traba'] ?? 3)));
        $mRitmo      = max(1, min(5, (int) ($_POST['m_ritmo'] ?? 3)));
        $mRendir     = max(1, min(5, (int) ($_POST['m_rendir'] ?? 3)));
        $mGente      = max(1, min(5, (int) ($_POST['m_gente'] ?? 3)));
        $mComunicar  = max(1, min(5, (int) ($_POST['m_comunicar'] ?? 3)));
        $mCorreccion = max(1, min(5, (int) ($_POST['m_correccion'] ?? 3)));
        $mDestino    = max(1, min(5, (int) ($_POST['m_destino'] ?? 3)));

        // Conducta Pasada (5 Preguntas)
        $cpOrganizar  = trim((string) ($_POST['cp_organizar'] ?? ''));
        $cpContactar  = trim((string) ($_POST['cp_contactar'] ?? ''));
        $cpInconcluso = trim((string) ($_POST['cp_inconcluso'] ?? ''));
        $cpCobrar     = trim((string) ($_POST['cp_cobrar'] ?? ''));
        $cpCritica    = trim((string) ($_POST['cp_critica'] ?? ''));

        // Doble Área de Interés
        $interesFuerte = trim((string) ($_POST['interes_fuerte'] ?? ''));
        $interesDebil  = trim((string) ($_POST['interes_debil'] ?? ''));

        // Preguntas de Criterio
        $rPosicion    = trim((string) ($_POST['r_posicion'] ?? ''));
        
        // P2: Producto (6 campos estructurados)
        $p2Lugar      = trim((string) ($_POST['p2_lugar'] ?? ''));
        $p2Incluye    = trim((string) ($_POST['p2_incluye'] ?? ''));
        $p2Duracion   = trim((string) ($_POST['p2_duracion'] ?? ''));
        $p2Capacidad  = trim((string) ($_POST['p2_capacidad'] ?? ''));
        $p2Horario    = trim((string) ($_POST['p2_horario'] ?? ''));
        $p2Precio     = trim((string) ($_POST['p2_precio'] ?? ''));
        $p2Falta      = trim((string) ($_POST['p2_falta'] ?? ''));

        $rMovimientos = trim((string) ($_POST['r_movimientos'] ?? ''));
        $rCuenta      = trim((string) ($_POST['r_cuenta'] ?? ''));
        $rExpectativa = trim((string) ($_POST['r_expectativa'] ?? ''));

        // Credenciales
        $usernamePropuesto = trim((string) ($_POST['username'] ?? ''));
        $password          = (string) ($_POST['password'] ?? '');
        $passwordConfirm   = (string) ($_POST['password_confirm'] ?? '');

        // Validaciones Paso 1
        if ($nombre === '') $errores['nombre'] = 'Ingresá tu nombre.';
        if ($apellido === '') $errores['apellido'] = 'Ingresá tu apellido.';
        if ($fechaNacimiento === '') $errores['fecha_nacimiento'] = 'Ingresá tu fecha de nacimiento.';
        if ($instagramUser === '') $errores['instagram_user'] = 'Ingresá tu usuario de Instagram.';
        if ($telefono === '') $errores['telefono'] = 'Ingresá tu teléfono o celular.';
        if ($direccion === '') $errores['direccion'] = 'Ingresá tu dirección.';
        if ($grupoSanguineo === '') $errores['grupo_sanguineo'] = 'Seleccioná tu grupo sanguíneo.';
        if ($contactoEmergencia === '') $errores['contacto_emergencia'] = 'Ingresá un contacto de emergencia (nombre y teléfono).';

        $movilidadesValidas = ['a pie', 'bici', 'colectivo', 'moto', 'auto propio', 'auto de la familia/prestado'];
        if (!in_array($movilidad, $movilidadesValidas, true)) {
            $errores['movilidad'] = 'Seleccioná cómo te movilizás habitualmente.';
        }

        $dispositivosValidos = ['solo celular', 'notebook/PC propia', 'PC prestada o del instituto', 'celular + PC'];
        if (!in_array($dispositivoTrabajo, $dispositivosValidos, true)) {
            $errores['dispositivo_trabajo'] = 'Indicá qué equipamiento tenés para trabajar.';
        }

        if ($trabajaActualmente === 1) {
            if ($trabajoLugar === '') $errores['trabajo_lugar'] = 'Indicá dónde trabajás actualmente.';
            if ($trabajoHorario === '') $errores['trabajo_horario'] = 'Indicá tu horario laboral habitual.';
            if ($trabajoRol === '') $errores['trabajo_rol'] = 'Indicá qué tareas o rol desempeñás.';
        }

        // Validaciones Paso 3: Conducta Pasada y Áreas de Interés
        $opcionesConducta = ['Nunca', 'Una vez', 'Algunas veces', 'Muchas veces'];
        if (!in_array($cpOrganizar, $opcionesConducta, true)) $errores['cp_organizar'] = 'Completá esta pregunta sobre conducta pasada.';
        if (!in_array($cpContactar, $opcionesConducta, true)) $errores['cp_contactar'] = 'Completá esta pregunta sobre conducta pasada.';
        if (!in_array($cpInconcluso, $opcionesConducta, true)) $errores['cp_inconcluso'] = 'Completá esta pregunta sobre conducta pasada.';
        if (!in_array($cpCobrar, $opcionesConducta, true)) $errores['cp_cobrar'] = 'Completá esta pregunta sobre conducta pasada.';
        if (!in_array($cpCritica, $opcionesConducta, true)) $errores['cp_critica'] = 'Completá esta pregunta sobre conducta pasada.';

        $areasValidas = ['Planificación', 'Desarrollo de Producto', 'Comercialización', 'Comunicación'];
        if (!in_array($interesFuerte, $areasValidas, true)) $errores['interes_fuerte'] = 'Elegí el área en la que más te gustaría meterte.';
        if (!in_array($interesDebil, $areasValidas, true)) $errores['interes_debil'] = 'Elegí el área en la que sentís mayor debilidad.';

        // Validaciones Paso 4: Criterio
        if (mb_strlen($rPosicion) < 500) {
            $errores['r_posicion'] = 'La respuesta sobre planificación y promoción requiere al menos 500 caracteres (actual: ' . mb_strlen($rPosicion) . ').';
        }

        if ($p2Lugar === '' || $p2Incluye === '' || $p2Duracion === '' || $p2Capacidad === '' || $p2Horario === '' || $p2Precio === '' || $p2Falta === '') {
            $errores['r_producto'] = 'Completá los 6 campos de la propuesta de producto comprable.';
        }

        if (mb_strlen($rMovimientos) < 500) {
            $errores['r_movimientos'] = 'Los movimientos comerciales y justificación requieren al menos 500 caracteres (actual: ' . mb_strlen($rMovimientos) . ').';
        }

        if (mb_strlen($rCuenta) < 500) {
            $errores['r_cuenta'] = 'El análisis de la cuenta de Instagram requiere al menos 500 caracteres (actual: ' . mb_strlen($rCuenta) . ').';
        }

        if ($rExpectativa === '') {
            $errores['r_expectativa'] = 'Escribí brevemente tus expectativas para el acompañamiento.';
        }

        // Validación Credenciales
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

            $subido = is_uploaded_file($tmpPath) ? move_uploaded_file($tmpPath, $destinoFisico) : copy($tmpPath, $destinoFisico);
            if (!$subido) {
                $errores['foto_perfil'] = 'No se pudo guardar la foto de perfil en el servidor.';
            } else {
                $fotoRelPath = 'uploads/estudiantes/' . $nombreArchivo;

                // Estructurar Producto JSON y texto para retrocompatibilidad
                $productoArray = [
                    'lugar_persona' => $p2Lugar,
                    'incluye'       => $p2Incluye,
                    'duracion'      => $p2Duracion,
                    'capacidad'     => $p2Capacidad,
                    'horario'       => $p2Horario,
                    'precio'        => $p2Precio,
                    'falta'         => $p2Falta,
                ];
                $productoJson = json_encode($productoArray, JSON_UNESCAPED_UNICODE);

                $rProductoTexto = "Lugar / Persona: " . ($p2Lugar ?: 'No especificado') . "\n"
                    . "• Qué incluye: " . $p2Incluye . "\n"
                    . "• Cuánto dura: " . $p2Duracion . "\n"
                    . "• Cuánta gente por vez: " . $p2Capacidad . "\n"
                    . "• Día y horario: " . $p2Horario . "\n"
                    . "• Precio y cálculo: " . $p2Precio . "\n"
                    . "• Qué le falta hoy: " . $p2Falta;

                // Transacción para Inserción Dual
                try {
                    $pdo->beginTransaction();

                    $passHash = password_hash($password, PASSWORD_DEFAULT);

                    // 1. Inserción en users
                    $stUser = $pdo->prepare("
                        INSERT INTO users (username, password, role, must_change_password, created_at)
                        VALUES (?, ?, 'estudiante', 0, datetime('now'))
                    ");
                    $stUser->execute([$usernameFinal, $passHash]);
                    $numericUserId = (int) $pdo->lastInsertId();

                    // 2. Inserción en lab_consultores (rol = '0', innegociable, tipo = 'estudiante')
                    $consultorId = 'est-' . $usernameFinal;
                    $nombreCompleto = trim($nombre . ' ' . $apellido);
                    $stCons = $pdo->prepare("
                        INSERT INTO lab_consultores (id, user_id, nombre, password, rol, tipo, color, activo, telefono)
                        VALUES (?, ?, ?, ?, '0', 'estudiante', '#2F5D7C', 1, ?)
                    ");
                    $stCons->execute([$consultorId, $usernameFinal, $nombreCompleto, $passHash, $telefono]);

                    // 3. Inserción en lab_estudiantes con nuevo y viejo esquema
                    $stEst = $pdo->prepare("
                        INSERT INTO lab_estudiantes (
                            consultor_id, user_id, nombre, apellido, fecha_nacimiento,
                            instituto, legajo, horas_presupuesto, alta_desde, activo,
                            telefono, direccion, grupo_sanguineo, contacto_emergencia, instagram_user,
                            movilidad, dispositivo_trabajo,
                            trabaja_actualmente, trabajo_horario, trabajo_lugar, trabajo_rol,
                            foto_perfil,
                            interes_fuerte, interes_debil,
                            m_locus, m_frustracion, m_vision, m_ambicion, m_creatividad, m_autonomia, m_empatia, m_gestion,
                            m_arrancar, m_traba, m_ritmo, m_rendir, m_gente, m_comunicar, m_correccion, m_destino,
                            cp_organizar, cp_contactar, cp_inconcluso, cp_cobrar, cp_critica,
                            r_planificacion, r_producto, r_comercializacion, r_resolucion,
                            r_posicion, r_producto_json, r_movimientos, r_cuenta, r_expectativa
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            'ISET 815', '', 60, '2026-10-01', 1,
                            ?, ?, ?, ?, ?,
                            ?, ?,
                            ?, ?, ?, ?,
                            ?,
                            ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?, ?
                        )
                    ");
                    $stEst->execute([
                        $consultorId, $numericUserId, $nombre, $apellido, $fechaNacimiento,
                        $telefono, $direccion, $grupoSanguineo, $contactoEmergencia, $instagramUser,
                        $movilidad, $dispositivoTrabajo,
                        $trabajaActualmente, $trabajoHorario, $trabajoLugar, $trabajoRol,
                        $fotoRelPath,
                        $interesFuerte, $interesDebil,
                        $mArrancar, $mTraba, $mRitmo, $mRendir, $mGente, $mComunicar, $mCorreccion, $mDestino,
                        $mArrancar, $mTraba, $mRitmo, $mRendir, $mGente, $mComunicar, $mCorreccion, $mDestino,
                        $cpOrganizar, $cpContactar, $cpInconcluso, $cpCobrar, $cpCritica,
                        $rPosicion, $rProductoTexto, $rMovimientos, $rCuenta,
                        $rPosicion, $productoJson, $rMovimientos, $rCuenta, $rExpectativa
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
  max-width: 820px;
  margin: 36px auto 80px;
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
  font-size: 14px;
  line-height: 1.5;
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
  line-height: 1.4;
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

/* Radio buttons / Píldoras */
.radio-toggle-group {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 6px;
}
.radio-label-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  border: 1px solid var(--border, #cbd5e1);
  border-radius: 8px;
  cursor: pointer;
  background: #f8fafc;
  font-size: 13.5px;
  font-weight: 600;
  transition: all 0.2s;
}
.radio-label-card input[type="radio"] {
  accent-color: #2F5D7C;
}
.radio-label-card:hover {
  border-color: #2F5D7C;
  background: #f1f5f9;
}

/* Filas de la Matriz Neutral (1 a 5) */
.matrix-row-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 16px;
  transition: border-color 0.2s;
}
.matrix-row-card:hover {
  border-color: #cbd5e1;
}
.matrix-row-header {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.matrix-row-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 16px;
}
@media (max-width: 680px) {
  .matrix-row-grid {
    grid-template-columns: 1fr;
    gap: 10px;
    text-align: center;
  }
}
.pole-text-left {
  font-size: 12.5px;
  color: #475569;
  text-align: right;
  line-height: 1.35;
}
.pole-text-right {
  font-size: 12.5px;
  color: #475569;
  text-align: left;
  line-height: 1.35;
}
@media (max-width: 680px) {
  .pole-text-left, .pole-text-right {
    text-align: center;
  }
}
.matrix-scale-radios {
  display: flex;
  gap: 8px;
  justify-content: center;
  align-items: center;
}
.matrix-scale-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}
.matrix-scale-option input[type="radio"] {
  display: none;
}
.matrix-scale-bubble {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1.5px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s ease;
}
.matrix-scale-option input[type="radio"]:checked + .matrix-scale-bubble {
  background: #2F5D7C;
  border-color: #2F5D7C;
  color: #ffffff;
  box-shadow: 0 0 0 3px rgba(47, 93, 124, 0.2);
  transform: scale(1.08);
}
.matrix-scale-bubble:hover {
  border-color: #2F5D7C;
}

/* Bloque Conducta Pasada */
.conducta-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 16px;
}
.conducta-pregunta {
  font-size: 14.5px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 12px;
  line-height: 1.45;
}
.conducta-options-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
@media (max-width: 600px) {
  .conducta-options-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
.conducta-option-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1.5px solid #cbd5e1;
  background: #ffffff;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  transition: all 0.2s;
  text-align: center;
}
.conducta-option-card input[type="radio"] {
  accent-color: #2F5D7C;
}
.conducta-option-card:hover {
  border-color: #2F5D7C;
  background: #f0f7ff;
}

/* Contador de Caracteres */
.char-counter {
  display: flex;
  justify-content: space-between;
  font-size: 11.5px;
  margin-top: 5px;
  color: #64748b;
  font-weight: 600;
}
.char-counter.is-valid {
  color: #166534;
}
.char-counter.is-invalid {
  color: #e11d48;
}

/* Cuadrícula de Producto de 6 campos */
.grid-producto-6 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
  margin-top: 14px;
}
@media (max-width: 640px) {
  .grid-producto-6 { grid-template-columns: 1fr; }
}

/* Credenciales y preview */
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

/* Banner de borrador restaurado */
.draft-toast {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>

<div class="onboarding-wrap">

<?php if ($exito): ?>

  <div class="wizard-card success-card" style="text-align:center;padding:40px 24px;">
    <div style="width:72px;height:72px;background:#dcfce7;color:#15803d;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <h1 style="font-family:var(--font-title, serif);font-size:27px;margin:0 0 12px 0;">¡Postulación recibida!</h1>
    
    <div class="user-preview-card" style="max-width:440px;margin:0 auto 24px;">
      <p style="margin:0;font-size:14px;color:#166534;font-weight:700;">Ya tenés tu usuario. Anotalo ahora: es con esto con lo que vas a entrar todas las veces.</p>
      <div class="u-tag"><?= e($usuarioCreado) ?></div>
      <p style="margin:10px 0 0 0;font-size:12.5px;color:#15803d;">
        Tu contraseña es la que definiste recién.
      </p>
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px 22px;max-width:560px;margin:0 auto 30px;text-align:left;font-size:14px;color:#334155;line-height:1.55;">
      Cuando arranque el convenio vas a ver acá adentro qué emprendimiento te tocó, cuándo son las reuniones y qué tenés que preparar para cada una. Todavía no hay nada asignado: eso lo decide la coordinación después de leer todas las fichas.
    </div>

    <a href="admin/login.php" class="btn-wizard-next" style="text-decoration:none;">
      Ir al Acceso del Sistema →
    </a>
  </div>

<?php else: ?>

  <div class="wizard-card">

    <!-- Intro Oficial -->
    <div style="margin-bottom:28px;">
      <span style="font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#2F5D7C;background:#e2ecf2;padding:4px 12px;border-radius:20px;display:inline-block;margin-bottom:10px;">
        Convenio ISET 815 · Esquel LAB
      </span>
      <h1 style="font-family:var(--font-title, serif);font-size:26px;margin:0 0 10px 0;color:#0f172a;">
        Onboarding de Prácticas Profesionalizantes
      </h1>
      <p style="font-size:15px;color:#334155;line-height:1.55;margin:0 0 10px 0;">
        Vas a acompañar a un emprendimiento de Esquel durante toda la cohorte. Esta ficha sirve para decidir cuál: qué caso te va a servir a vos y a qué emprendedor le vas a servir vos.
      </p>
      <p style="font-size:14px;color:#475569;line-height:1.55;margin:0 0 10px 0;">
        No es un examen. No se aprueba ni se desaprueba y no va nota al instituto. Por eso hay preguntas que no son de trámite: cuanto más honesto contestes, mejor te va a tocar.
      </p>
      <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#0284c7;background:#f0f9ff;padding:8px 12px;border-radius:6px;border:1px solid #bae6fd;">
        <span>💾</span>
        <span>Son unos 20 minutos. Lo que escribís se guarda solo en este navegador, así que podés cerrar y seguir después desde el mismo aparato.</span>
      </div>
    </div>

    <div id="draftRestoredBanner" class="draft-toast" style="display:none;">
      <span>✓ Restauramos tus respuestas anteriores guardadas en este dispositivo.</span>
      <button type="button" onclick="limpiarBorrador()" style="background:none;border:none;color:#047857;font-size:12px;font-weight:700;cursor:pointer;text-decoration:underline;">Borrar borrador</button>
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
        <div class="step-label">Conducta</div>
      </div>
      <div class="step-item" id="stepIndicator4">
        <div class="step-bubble">4</div>
        <div class="step-label">Criterio</div>
      </div>
      <div class="step-item" id="stepIndicator5">
        <div class="step-bubble">5</div>
        <div class="step-label">Acceso</div>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" id="formOnboarding" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="username" id="inputHiddenUsername" value="<?= e($_POST['username'] ?? '') ?>">

      <!-- ================= PASO 1: DATOS DUROS Y SITUACIÓN ================= -->
      <div class="wizard-step" id="paso1">
        <div class="step-header">
          <h2>Paso 1 · Datos Personales, Salud y Logística</h2>
          <p>Tu información de contacto, equipamiento y movilidad para coordinar las salidas a terreno.</p>
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
              <strong style="color:#1e293b;font-size:14px;display:block;">Subir foto clara</strong>
              <span class="field-help" style="margin:0;">Formatos JPG, PNG o WEBP (máx. 8 MB). Rostro visible de frente para identificarte en el equipo.</span>
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

        <!-- Salud y Emergencia con Disclaimer -->
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px 18px;margin-bottom:20px;">
          <div style="font-size:13px;color:#991b1b;line-height:1.45;margin-bottom:14px;">
            ⚠️ <strong>Importante sobre datos de salud:</strong> Vas a salir a visitar emprendimientos, algunos en el campo. Esto lo pedimos por eso y se usa solo para eso: lo ve la coordinación del programa y nadie más. No va a tu legajo del instituto.
          </div>
          <div class="form-grid-2">
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="grupo_sanguineo">Grupo Sanguíneo <span class="req">*</span></label>
              <select id="grupo_sanguineo" name="grupo_sanguineo" class="select-custom" required>
                <option value="">Seleccioná una opción...</option>
                <?php foreach (['0+', '0-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'No lo sé'] as $gs): ?>
                  <option value="<?= $gs ?>" <?= ($_POST['grupo_sanguineo'] ?? '') === $gs ? 'selected' : '' ?>><?= $gs ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errores['grupo_sanguineo'])): ?><div class="field-error"><?= e($errores['grupo_sanguineo']) ?></div><?php endif; ?>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="contacto_emergencia">Contacto de Emergencia <span class="req">*</span></label>
              <input type="text" id="contacto_emergencia" name="contacto_emergencia" class="input-text" value="<?= e($_POST['contacto_emergencia'] ?? '') ?>" placeholder="Ej: Mamá (María) - 2945 654321" required>
              <?php if (!empty($errores['contacto_emergencia'])): ?><div class="field-error"><?= e($errores['contacto_emergencia']) ?></div><?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Movilidad y Dispositivo -->
        <div class="form-grid-2">
          <div class="field-group">
            <label class="field-label" for="movilidad">¿Cómo te movés? <span class="req">*</span></label>
            <select id="movilidad" name="movilidad" class="select-custom" required>
              <option value="">Seleccioná tu transporte principal...</option>
              <?php 
              $movs = [
                'a pie' => 'A pie',
                'bici' => 'Bicicleta',
                'colectivo' => 'Colectivo',
                'moto' => 'Moto',
                'auto propio' => 'Auto propio',
                'auto de la familia/prestado' => 'Auto de la familia / prestado'
              ];
              foreach ($movs as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= ($_POST['movilidad'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
            <p class="field-help">Varios emprendimientos están a 10 o 15 km de Esquel. Esto define a quién podemos mandar adónde.</p>
            <?php if (!empty($errores['movilidad'])): ?><div class="field-error"><?= e($errores['movilidad']) ?></div><?php endif; ?>
          </div>

          <div class="field-group">
            <label class="field-label" for="dispositivo_trabajo">¿Con qué trabajás? <span class="req">*</span></label>
            <select id="dispositivo_trabajo" name="dispositivo_trabajo" class="select-custom" required>
              <option value="">Seleccioná tu equipamiento...</option>
              <?php 
              $disps = [
                'solo celular' => 'Solo celular',
                'notebook/PC propia' => 'Notebook / PC propia',
                'PC prestada o del instituto' => 'PC prestada o del instituto',
                'celular + PC' => 'Celular + PC'
              ];
              foreach ($disps as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= ($_POST['dispositivo_trabajo'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
            <p class="field-help">Hay entregables que son tablas y planillas. Si solo tenés celular, no es un problema, pero lo tenemos que saber antes y no después.</p>
            <?php if (!empty($errores['dispositivo_trabajo'])): ?><div class="field-error"><?= e($errores['dispositivo_trabajo']) ?></div><?php endif; ?>
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
            Continuar a Cómo Trabajás →
          </button>
        </div>
      </div>

      <!-- ================= PASO 2: CÓMO TRABAJÁS (MATRIZ 8 FILAS) ================= -->
      <div class="wizard-step is-hidden" id="paso2">
        <div class="step-header">
          <h2>Cómo trabajás</h2>
          <p>
            Ocho filas, del 1 al 5. No se puntúa y no hay respuestas buenas: las dos puntas de cada fila son formas válidas de trabajar. Lo que cambia es con qué emprendimiento te conviene ir.<br>
            <strong>Si ponés todo 5, lo único que conseguís es que te toque un caso que no va con vos.</strong>
          </p>
        </div>

        <?php
        $filasMatriz = [
            'm_arrancar' => [
                'titulo' => '1. Para arrancar',
                'izq'    => 'Prefiero confirmar antes de moverme, para no hacer trabajo al pedo',
                'der'    => 'Prefiero arrancar y corregir sobre la marcha'
            ],
            'm_traba' => [
                'titulo' => '2. Cuando algo se traba',
                'izq'    => 'Pido ayuda rápido, para no perder días',
                'der'    => 'Insisto por mi cuenta hasta destrabarlo'
            ],
            'm_ritmo' => [
                'titulo' => '3. Ritmo y detalle',
                'izq'    => 'Prefiero avanzar rápido aunque queden cabos sueltos',
                'der'    => 'Prefiero ir más lento y que quede chequeado'
            ],
            'm_rendir' => [
                'titulo' => '4. Cómo rendís mejor',
                'izq'    => 'Con alguien que me vaya marcando',
                'der'    => 'Si me dan el objetivo y me dejan solo'
            ],
            'm_gente' => [
                'titulo' => '5. Gente que no conocés',
                'izq'    => 'Me cansa; rindo mejor con tiempo a solas',
                'der'    => 'Me carga pilas'
            ],
            'm_comunicar' => [
                'titulo' => '6. Cómo contás lo que hiciste',
                'izq'    => 'Me sale mejor hablándolo',
                'der'    => 'Me sale mejor escribiéndolo'
            ],
            'm_correccion' => [
                'titulo' => '7. Cómo preferís que te corrijan',
                'izq'    => 'En el momento, aunque haya otros delante',
                'der'    => 'Después, a solas'
            ],
            'm_destino' => [
                'titulo' => '8. Adónde querés llegar',
                'izq'    => 'Un trabajo estable, con horario y sueldo seguro',
                'der'    => 'Algo propio, aunque sea inestable'
            ],
        ];

        foreach ($filasMatriz as $k => $f):
            $val = (int) ($_POST[$k] ?? 3);
        ?>
          <div class="matrix-row-card">
            <div class="matrix-row-header">
              <span><?= e($f['titulo']) ?></span>
            </div>
            <div class="matrix-row-grid">
              <div class="pole-text-left">
                <strong>(1)</strong> <?= e($f['izq']) ?>
              </div>
              <div class="matrix-scale-radios">
                <?php for ($n = 1; $n <= 5; $n++): ?>
                  <label class="matrix-scale-option" title="Puntaje <?= $n ?>">
                    <input type="radio" name="<?= $k ?>" value="<?= $n ?>" <?= $val === $n ? 'checked' : '' ?>>
                    <span class="matrix-scale-bubble"><?= $n ?></span>
                  </label>
                <?php endfor; ?>
              </div>
              <div class="pole-text-right">
                <strong>(5)</strong> <?= e($f['der']) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(1)">
            ← Volver a Datos
          </button>
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(3)">
            Continuar a Conducta Pasada →
          </button>
        </div>
      </div>

      <!-- ================= PASO 3: QUÉ TE PASÓ ÚLTIMAMENTE Y ÁREAS ================= -->
      <div class="wizard-step is-hidden" id="paso3">
        <div class="step-header">
          <h2>Qué te pasó últimamente</h2>
          <p>
            En los últimos seis meses, ¿cuántas veces te pasó esto? No hay respuesta correcta: contestá lo que es.
          </p>
        </div>

        <?php
        $preguntasConducta = [
            'cp_organizar' => [
                'p' => 'Organizaste algo que dependía de que otros aparecieran: una juntada, un viaje, un torneo, un trabajo grupal donde vos repartiste las partes.'
            ],
            'cp_contactar' => [
                'p' => 'Le escribiste a alguien que no conocías para pedirle algo: información, un presupuesto, una changa, una entrevista.'
            ],
            'cp_inconcluso' => [
                'p' => 'Empezaste algo por tu cuenta y lo dejaste por la mitad.'
            ],
            'cp_cobrar' => [
                'p' => 'Cobraste plata por algo que hiciste vos. No un sueldo: algo que vendiste, un trabajo suelto, un servicio.'
            ],
            'cp_critica' => [
                'p' => 'Alguien te hizo una crítica dura y terminaste cambiando lo que estabas haciendo.'
            ],
        ];

        foreach ($preguntasConducta as $k => $c):
            $valCp = (string) ($_POST[$k] ?? '');
        ?>
          <div class="conducta-item">
            <div class="conducta-pregunta">
              <?= e($c['p']) ?>
            </div>
            <div class="conducta-options-grid">
              <?php foreach (['Nunca', 'Una vez', 'Algunas veces', 'Muchas veces'] as $opt): ?>
                <label class="conducta-option-card">
                  <input type="radio" name="<?= $k ?>" value="<?= $opt ?>" <?= $valCp === $opt ? 'checked' : '' ?> required>
                  <span><?= $opt ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($errores[$k])): ?><div class="field-error"><?= e($errores[$k]) ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>

        <!-- Áreas de interés doble -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;margin-top:24px;">
          <h3 style="font-size:16px;margin:0 0 14px 0;color:#1e293b;font-family:var(--font-title, serif);">
            Preferencias de Aprendizaje en la Cohorte
          </h3>
          <div class="form-grid-2">
            <div class="field-group">
              <label class="field-label" for="interes_fuerte">¿En cuál de las cuatro te gustaría meterte más? <span class="req">*</span></label>
              <select id="interes_fuerte" name="interes_fuerte" class="select-custom" required>
                <option value="">Seleccioná un área prioritaria...</option>
                <?php foreach (['Planificación', 'Desarrollo de Producto', 'Comercialización', 'Comunicación'] as $ar): ?>
                  <option value="<?= $ar ?>" <?= ($_POST['interes_fuerte'] ?? '') === $ar ? 'selected' : '' ?>><?= $ar ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errores['interes_fuerte'])): ?><div class="field-error"><?= e($errores['interes_fuerte']) ?></div><?php endif; ?>
            </div>
            <div class="field-group">
              <label class="field-label" for="interes_debil">¿En cuál te sentís más flojo? <span class="req">*</span></label>
              <select id="interes_debil" name="interes_debil" class="select-custom" required>
                <option value="">Seleccioná un área a reforzar...</option>
                <?php foreach (['Planificación', 'Desarrollo de Producto', 'Comercialización', 'Comunicación'] as $ar): ?>
                  <option value="<?= $ar ?>" <?= ($_POST['interes_debil'] ?? '') === $ar ? 'selected' : '' ?>><?= $ar ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errores['interes_debil'])): ?><div class="field-error"><?= e($errores['interes_debil']) ?></div><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(2)">
            ← Volver a Cómo Trabajás
          </button>
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(4)">
            Continuar a Criterio y Casos →
          </button>
        </div>
      </div>

      <!-- ================= PASO 4: CRITERIO TURÍSTICO (P1 A P5) ================= -->
      <div class="wizard-step is-hidden" id="paso4">
        <div class="step-header">
          <h2>Paso 4 · Criterio Turístico y Casos Reales</h2>
          <p>Evaluamos cómo abordás casos reales del destino y tu capacidad para estructurar entregables.</p>
        </div>

        <!-- P1: Planificación (min 500) -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;">
          <label class="field-label" for="r_posicion" style="font-size:15px;">
            Pregunta 1 — Tomá posición <span class="req">*</span>
          </label>
          <div style="font-size:12px;font-weight:700;color:#2F5D7C;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
            Área: Planificación · Mínimo 500 caracteres
          </div>
          <p style="font-size:14px;color:#334155;line-height:1.5;margin:0 0 10px 0;">
            Mucha gente del turismo repite que lo que le falta a Esquel es más promoción.<br>
            <strong>¿Estás de acuerdo?</strong> Decí que sí o que no —no las dos cosas— y bancá tu respuesta con algo concreto: algo que viste, algo que te contaron, un lugar puntual.<br>
            Si decís que no, decí entonces qué es lo que falta.
          </p>
          <textarea id="r_posicion" name="r_posicion" class="textarea-custom" style="min-height:130px;" placeholder="Tomá posición y desarrollá tu fundamento con casos concretos..." required oninput="actualizarContador('r_posicion', 500)"><?= e($_POST['r_posicion'] ?? '') ?></textarea>
          <div id="counter_r_posicion" class="char-counter">
            <span>Mínimo requerido: 500 caracteres</span>
            <span id="char_val_r_posicion">0 / 500</span>
          </div>
          <?php if (!empty($errores['r_posicion'])): ?><div class="field-error"><?= e($errores['r_posicion']) ?></div><?php endif; ?>
        </div>

        <!-- P2: Producto (6 campos) -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;">
          <label class="field-label" style="font-size:15px;">
            Pregunta 2 — De lo que alguien hace a algo que se pueda comprar <span class="req">*</span>
          </label>
          <div style="font-size:12px;font-weight:700;color:#2F5D7C;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
            Área: Producto · Sin mínimo de caracteres generales · Seis campos obligatorios
          </div>
          <p style="font-size:14px;color:#334155;line-height:1.5;margin:0 0 12px 0;">
            Nombrá a alguien real de Esquel, Trevelin o la comarca que produzca o haga algo: una chacra, un taller, una cocinera, un criancero, un artesano, un guía. Poné el nombre del lugar o de la persona. Tiene que ser real. Si no se te ocurre ninguno, decilo y contá por qué.<br>
            <strong>Ahora convertí lo que hace en algo que un visitante pueda comprar:</strong>
          </p>

          <div class="field-group" style="margin-bottom:12px;">
            <label class="field-label" for="p2_lugar" style="font-size:13px;">Nombre del prestador, productor o lugar real <span class="req">*</span></label>
            <input type="text" id="p2_lugar" name="p2_lugar" class="input-text" value="<?= e($_POST['p2_lugar'] ?? '') ?>" placeholder="Ej: Chacra Los Álamos / Doña Rosa" required>
          </div>

          <div class="grid-producto-6">
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_incluye" style="font-size:13px;">1. Qué incluye <span class="req">*</span></label>
              <textarea id="p2_incluye" name="p2_incluye" class="textarea-custom" style="min-height:70px;" placeholder="Detallá los componentes de la experiencia..." required><?= e($_POST['p2_incluye'] ?? '') ?></textarea>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_duracion" style="font-size:13px;">2. Cuánto dura <span class="req">*</span></label>
              <input type="text" id="p2_duracion" name="p2_duracion" class="input-text" value="<?= e($_POST['p2_duracion'] ?? '') ?>" placeholder="Ej: 2 horas y media" required>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_capacidad" style="font-size:13px;">3. Cuánta gente por vez <span class="req">*</span></label>
              <input type="text" id="p2_capacidad" name="p2_capacidad" class="input-text" value="<?= e($_POST['p2_capacidad'] ?? '') ?>" placeholder="Ej: Máximo 8 personas, mínimo 2" required>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_horario" style="font-size:13px;">4. Qué día y a qué hora <span class="req">*</span></label>
              <input type="text" id="p2_horario" name="p2_horario" class="input-text" value="<?= e($_POST['p2_horario'] ?? '') ?>" placeholder="Ej: Jueves a Sábados 16:30 hs" required>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_precio" style="font-size:13px;">5. Precio por persona y de dónde sacaste el número <span class="req">*</span></label>
              <input type="text" id="p2_precio" name="p2_precio" class="input-text" value="<?= e($_POST['p2_precio'] ?? '') ?>" placeholder="Ej: $18.000 (calculado según insumos + 2hs de guía)" required>
            </div>
            <div class="field-group" style="margin-bottom:0;">
              <label class="field-label" for="p2_falta" style="font-size:13px;">6. Qué le falta hoy para poder venderlo <span class="req">*</span></label>
              <input type="text" id="p2_falta" name="p2_falta" class="input-text" value="<?= e($_POST['p2_falta'] ?? '') ?>" placeholder="Ej: Habilitación bromatológica, posnet, seguro" required>
            </div>
          </div>
          <?php if (!empty($errores['r_producto'])): ?><div class="field-error"><?= e($errores['r_producto']) ?></div><?php endif; ?>
        </div>

        <!-- P3: Comercialización (min 500) -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;">
          <label class="field-label" for="r_movimientos" style="font-size:15px;">
            Pregunta 3 — Tres movimientos, en orden <span class="req">*</span>
          </label>
          <div style="font-size:12px;font-weight:700;color:#2F5D7C;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
            Área: Comercialización · Mínimo 500 caracteres
          </div>
          <p style="font-size:14px;color:#334155;line-height:1.5;margin:0 0 10px 0;">
            Un emprendimiento está a 12 km de Esquel. No tiene cartel en la ruta, no tiene web, tiene una cuenta de Instagram con 200 seguidores y recibió 40 visitantes en todo el año. La dueña puede dedicarle dos días por semana y casi no tiene plata para invertir.<br>
            <strong>¿Cuáles son los tres primeros movimientos?</strong> Escribilo como se lo pasarías a ella, no como un informe:<br>
            <em>…qué se hace · quién lo hace · para cuándo</em><br>
            Y al final, en dos líneas: <strong>por qué ese orden y no otro</strong>.
          </p>
          <textarea id="r_movimientos" name="r_movimientos" class="textarea-custom" style="min-height:130px;" placeholder="1. Qué se hace · Quién lo hace · Para cuándo&#10;2. Qué se hace · Quién lo hace · Para cuándo&#10;3. Qué se hace · Quién lo hace · Para cuándo&#10;&#10;Por qué este orden:" required oninput="actualizarContador('r_movimientos', 500)"><?= e($_POST['r_movimientos'] ?? '') ?></textarea>
          <div id="counter_r_movimientos" class="char-counter">
            <span>Mínimo requerido: 500 caracteres</span>
            <span id="char_val_r_movimientos">0 / 500</span>
          </div>
          <?php if (!empty($errores['r_movimientos'])): ?><div class="field-error"><?= e($errores['r_movimientos']) ?></div><?php endif; ?>
        </div>

        <!-- P4: Comunicación (min 500) -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;">
          <label class="field-label" for="r_cuenta" style="font-size:15px;">
            Pregunta 4 — Una cuenta de verdad <span class="req">*</span>
          </label>
          <div style="font-size:12px;font-weight:700;color:#2F5D7C;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
            Área: Comunicación · Mínimo 500 caracteres
          </div>
          <p style="font-size:14px;color:#334155;line-height:1.5;margin:0 0 10px 0;">
            Abrí Instagram y entrá a la cuenta de un emprendimiento turístico de Esquel, Trevelin o la comarca.<br>
            <strong>Poné el @ y contá de qué es la última publicación que subieron.</strong><br>
            Ahora: <strong>una cosa que hacen bien y dos que cambiarías</strong>. Cada una con el motivo y con lo que viste que te lo hace decir.<br>
            <span style="color:#b91c1c;">(No vale "que suba más seguido" ni "que mejore las fotos": eso se puede escribir sin haber mirado nada).</span>
          </p>
          <textarea id="r_cuenta" name="r_cuenta" class="textarea-custom" style="min-height:130px;" placeholder="@cuenta_real&#10;Última publicación: ...&#10;1 acierto fundado: ...&#10;Cambio 1 con motivo: ...&#10;Cambio 2 con motivo: ..." required oninput="actualizarContador('r_cuenta', 500)"><?= e($_POST['r_cuenta'] ?? '') ?></textarea>
          <div id="counter_r_cuenta" class="char-counter">
            <span>Mínimo requerido: 500 caracteres</span>
            <span id="char_val_r_cuenta">0 / 500</span>
          </div>
          <?php if (!empty($errores['r_cuenta'])): ?><div class="field-error"><?= e($errores['r_cuenta']) ?></div><?php endif; ?>
        </div>

        <!-- P5: Expectativa (sin mínimo) -->
        <div class="field-group" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:12px;">
          <label class="field-label" for="r_expectativa" style="font-size:15px;">
            Pregunta 5 — Mini expectativa (2 o 3 líneas) <span class="req">*</span>
          </label>
          <p style="font-size:14px;color:#334155;line-height:1.5;margin:0 0 10px 0;">
            En dos o tres líneas: <strong>¿por qué estás acá y qué te querés llevar de estos dos meses?</strong><br>
            <em>Contestá lo que es, no lo que queda bien: nos sirve para saber qué darte.</em>
          </p>
          <textarea id="r_expectativa" name="r_expectativa" class="textarea-custom" style="min-height:85px;" placeholder="Tu respuesta honesta en 2 o 3 líneas..." required><?= e($_POST['r_expectativa'] ?? '') ?></textarea>
          <?php if (!empty($errores['r_expectativa'])): ?><div class="field-error"><?= e($errores['r_expectativa']) ?></div><?php endif; ?>
        </div>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(3)">
            ← Volver a Conducta
          </button>
          <button type="button" class="btn-wizard-next" onclick="irAlPaso(5)">
            Generar Acceso y Enviar →
          </button>
        </div>
      </div>

      <!-- ================= PASO 5: CREDENCIALES Y ENVÍO ================= -->
      <div class="wizard-step is-hidden" id="paso5">
        <div class="step-header">
          <h2>Paso 5 · Generación de Acceso al Panel</h2>
          <p>Definí tu contraseña personal para acceder a tus asignaciones, reuniones y consignas.</p>
        </div>

        <div class="user-preview-card">
          <p style="margin:0;font-size:15px;color:#1e293b;">
            Tu usuario en el sistema será:
          </p>
          <div class="u-tag" id="labelUsernameGenerado">estudiante</div>
          <p style="margin:10px 0 0 0;font-size:13px;color:#475569;">
            Generado automáticamente a partir de tu nombre y apellido.
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

        <!-- Cláusula de confidencialidad oficial al pie -->
        <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:16px 18px;border-radius:10px;font-size:13px;color:#475569;line-height:1.5;margin-bottom:24px;">
          🔒 <strong>Privacidad y custodia:</strong> Esto lo lee la coordinación del programa. No lo ven los otros estudiantes, no lo ve el emprendedor con el que trabajes, y tu DNI, tu grupo sanguíneo y tu contacto de emergencia no los ve nadie más.
        </div>

        <div class="wizard-footer">
          <button type="button" class="btn-wizard-prev" onclick="irAlPaso(4)">
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
let pasoActual = 1;
const DRAFT_KEY = 'esquel_lab_onboarding_draft';

function normalizarSlug(str) {
  return str.normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]/g, "");
}

function calcularUsername() {
  const nom = normalizarSlug((document.getElementById('nombre')?.value || '').trim());
  const ape = normalizarSlug((document.getElementById('apellido')?.value || '').trim());
  let user = 'estudiante';
  if (nom && ape) {
    user = nom + '.' + ape;
  } else if (nom) {
    user = nom;
  } else if (ape) {
    user = ape;
  }
  const lbl = document.getElementById('labelUsernameGenerado');
  const inp = document.getElementById('inputHiddenUsername');
  if (lbl) lbl.textContent = user;
  if (inp) inp.value = user;
}

function actualizarContador(id, minimo) {
  const el = document.getElementById(id);
  const span = document.getElementById('char_val_' + id);
  const wrap = document.getElementById('counter_' + id);
  if (!el || !span || !wrap) return;
  const len = el.value.length;
  span.textContent = len + ' / ' + minimo;
  if (len >= minimo) {
    wrap.classList.remove('is-invalid');
    wrap.classList.add('is-valid');
  } else {
    wrap.classList.remove('is-valid');
    wrap.classList.add('is-invalid');
  }
}

// Autosave en localStorage
function guardarBorrador() {
  try {
    const data = {};
    const textInputs = document.querySelectorAll('#formOnboarding input[type="text"], #formOnboarding input[type="tel"], #formOnboarding input[type="date"], #formOnboarding select, #formOnboarding textarea');
    textInputs.forEach(inp => {
      if (inp.name && inp.type !== 'password' && inp.name !== 'csrf_token') {
        data[inp.name] = inp.value;
      }
    });
    const radios = document.querySelectorAll('#formOnboarding input[type="radio"]:checked');
    radios.forEach(r => {
      if (r.name) data[r.name] = r.value;
    });
    localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
  } catch (e) {}
}

function restaurarBorrador() {
  try {
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    const data = JSON.parse(raw);
    let algoRestaurado = false;
    for (const k in data) {
      const val = data[k];
      const el = document.querySelector(`[name="${k}"]`);
      if (el) {
        if (el.type === 'radio') {
          const matchingRadio = document.querySelector(`input[name="${k}"][value="${val}"]`);
          if (matchingRadio && !matchingRadio.checked) {
            matchingRadio.checked = true;
            algoRestaurado = true;
          }
        } else if (el.value === '' && val !== '') {
          el.value = val;
          algoRestaurado = true;
        }
      }
    }
    if (algoRestaurado) {
      const toast = document.getElementById('draftRestoredBanner');
      if (toast) toast.style.display = 'flex';
      ['r_posicion', 'r_movimientos', 'r_cuenta'].forEach(id => {
        actualizarContador(id, 500);
      });
      // Revisar toggle de trabajo
      const trabaja = document.querySelector('input[name="trabaja_actualmente"]:checked');
      const bExtra = document.getElementById('bloqueTrabajoExtra');
      if (bExtra && trabaja) {
        bExtra.style.display = (trabaja.value === '1') ? 'block' : 'none';
      }
    }
  } catch (e) {}
}

function limpiarBorrador() {
  localStorage.removeItem(DRAFT_KEY);
  const toast = document.getElementById('draftRestoredBanner');
  if (toast) toast.style.display = 'none';
}

function validarPaso(n) {
  if (n === 1) {
    const campos = ['nombre', 'apellido', 'fecha_nacimiento', 'instagram_user', 'telefono', 'direccion', 'grupo_sanguineo', 'contacto_emergencia', 'movilidad', 'dispositivo_trabajo'];
    for (let c of campos) {
      const el = document.getElementById(c);
      if (!el || !el.value.trim()) {
        alert('Por favor completá todos los campos obligatorios del Paso 1.');
        el && el.focus();
        return false;
      }
    }
    const foto = document.getElementById('foto_perfil');
    const prevSrc = document.getElementById('previewFoto')?.getAttribute('src') || '';
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
    const cps = ['cp_organizar', 'cp_contactar', 'cp_inconcluso', 'cp_cobrar', 'cp_critica'];
    for (let cp of cps) {
      const chk = document.querySelector(`input[name="${cp}"]:checked`);
      if (!chk) {
        alert('Por favor respondé las 5 preguntas del bloque "Qué te pasó últimamente".');
        return false;
      }
    }
    const i1 = document.getElementById('interes_fuerte')?.value || '';
    const i2 = document.getElementById('interes_debil')?.value || '';
    if (!i1 || !i2) {
      alert('Por favor completá ambas preferencias de aprendizaje (área de mayor interés y área a reforzar).');
      return false;
    }
  }

  if (n === 4) {
    const p1 = (document.getElementById('r_posicion')?.value || '').trim();
    if (p1.length < 500) {
      alert('La Pregunta 1 (Planificación) requiere al menos 500 caracteres para fundamentar tu postura. Llevás ' + p1.length + '.');
      document.getElementById('r_posicion')?.focus();
      return false;
    }

    const p2Campos = ['p2_lugar', 'p2_incluye', 'p2_duracion', 'p2_capacidad', 'p2_horario', 'p2_precio', 'p2_falta'];
    for (let c of p2Campos) {
      const el = document.getElementById(c);
      if (!el || !el.value.trim()) {
        alert('Por favor completá los 6 campos individuales de la Pregunta 2 (Producto).');
        el && el.focus();
        return false;
      }
    }

    const p3 = (document.getElementById('r_movimientos')?.value || '').trim();
    if (p3.length < 500) {
      alert('La Pregunta 3 (Comercialización) requiere al menos 500 caracteres (tres movimientos con qué, quién, cuándo y justificación del orden). Llevás ' + p3.length + '.');
      document.getElementById('r_movimientos')?.focus();
      return false;
    }

    const p4 = (document.getElementById('r_cuenta')?.value || '').trim();
    if (p4.length < 500) {
      alert('La Pregunta 4 (Comunicación) requiere al menos 500 caracteres (análisis de la cuenta de Instagram con @, última publicación, acierto y 2 cambios fundados). Llevás ' + p4.length + '.');
      document.getElementById('r_cuenta')?.focus();
      return false;
    }

    const p5 = (document.getElementById('r_expectativa')?.value || '').trim();
    if (!p5) {
      alert('Por favor completá la mini expectativa personal (Pregunta 5).');
      document.getElementById('r_expectativa')?.focus();
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

  for (let i = 1; i <= 5; i++) {
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

  if (destino === 5) {
    calcularUsername();
  }

  const destinoEl = document.getElementById('paso' + destino);
  const destinoInd = document.getElementById('stepIndicator' + destino);
  if (destinoEl) destinoEl.classList.remove('is-hidden');
  if (destinoInd) destinoInd.classList.add('is-active');

  pasoActual = destino;
  window.scrollTo({ top: 120, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', () => {
  // Restaurar borrador de localStorage
  restaurarBorrador();

  // Escuchar inputs para autosave
  const form = document.getElementById('formOnboarding');
  if (form) {
    form.addEventListener('input', guardarBorrador);
    form.addEventListener('change', guardarBorrador);
  }

  // Inicializar contadores de caracteres
  ['r_posicion', 'r_movimientos', 'r_cuenta'].forEach(id => {
    actualizarContador(id, 500);
  });

  // Preview de foto
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

  // Toggle situación laboral
  const radiosTrabajo = document.querySelectorAll('input[name="trabaja_actualmente"]');
  const bloqueExtra = document.getElementById('bloqueTrabajoExtra');
  radiosTrabajo.forEach(r => {
    r.addEventListener('change', () => {
      if (bloqueExtra) {
        bloqueExtra.style.display = (r.value === '1') ? 'block' : 'none';
      }
    });
  });

  // Limpiar localStorage tras envío exitoso
  if (form) {
    form.addEventListener('submit', (e) => {
      const p1 = document.getElementById('password')?.value || '';
      const p2 = document.getElementById('password_confirm')?.value || '';
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
      // Al enviar, removemos el borrador para que no persista tras completar
      localStorage.removeItem(DRAFT_KEY);
    });
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>