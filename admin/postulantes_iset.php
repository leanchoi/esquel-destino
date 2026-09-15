<?php
/**
 * Dashboard de Postulantes ISET 815 · Onboarding y Perfilado Psicotécnico
 *
 * Permite a la conducción y equipo de consultores auditar los perfiles
 * psicológicos y técnicos de los estudiantes del ISET 815 para la asignación
 * estratégica a los 18 emprendimientos turísticos acelerados de Esquel LAB.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/estudiantes.php';

$u = requiere_rol('admin');
$pdo = db();

function calcular_edad(?string $fechaNac): ?int
{
    if (!$fechaNac || trim($fechaNac) === '') return null;
    try {
        $nac = new DateTime($fechaNac);
        $hoy = new DateTime();
        return $hoy->diff($nac)->y;
    } catch (Exception $e) {
        return null;
    }
}

// Consultar nómina de estudiantes con datos de perfil y respuestas
$st = $pdo->query("
    SELECT e.*, c.nombre AS consultor_nombre, c.activo AS consultor_activo,
           u.username, u.created_at AS user_created_at,
           p.nombre AS proyecto_nombre
      FROM lab_estudiantes e
      JOIN lab_consultores c ON c.id = e.consultor_id
      LEFT JOIN users u ON u.id = e.user_id
      LEFT JOIN lab_proyectos p ON p.id = e.proyecto_id
     ORDER BY (e.r_planificacion != '' OR e.foto_perfil != '') DESC, e.created_at DESC, e.consultor_id ASC
");
$postulantes = $st->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================================
// EXPORTACIÓN ZIP (CSV tabulado + Carpeta con fotos de perfil)
// ==========================================================================
if (($_GET['export'] ?? '') === 'zip') {
    if (!class_exists('ZipArchive')) {
        die('La extensión ZipArchive de PHP no está disponible en este servidor.');
    }

    $zip = new ZipArchive();
    $tmpZip = tempnam(sys_get_temp_dir(), 'iset_zip_');
    if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die('No se pudo crear el archivo ZIP temporal.');
    }

    // 1. Generar CSV tabulado en UTF-8 con BOM
    $csvBuffer = fopen('php://temp', 'r+');
    fwrite($csvBuffer, "\xEF\xBB\xBF");

    $headers = [
        'ID Consultor',
        'Usuario',
        'Nombre',
        'Apellido',
        'Fecha Nacimiento',
        'Edad',
        'Instagram',
        'Teléfono',
        'Dirección',
        'Grupo Sanguíneo',
        'Contacto Emergencia',
        'Trabaja Actualmente',
        'Lugar de Trabajo',
        'Horario de Trabajo',
        'Rol de Trabajo',
        'Emprendimiento Asignado',
        'Iniciativa (Locus 1-5)',
        'Tolerancia a la Frustración (1-5)',
        'Visión de Sistema (1-5)',
        'Ambición Profesional (1-5)',
        'Creatividad Operativa (1-5)',
        'Autonomía de Decisión (1-5)',
        'Empatía Comercial (1-5)',
        'Gestión del Tiempo (1-5)',
        'Promedio Matriz',
        'R. Planificación y Viabilidad',
        'R. Desarrollo de Producto',
        'R. Comercialización',
        'R. Pensamiento Crítico',
        'Foto Archivo',
        'Fecha de Registro',
    ];
    fputcsv($csvBuffer, $headers);

    $zip->addEmptyDir('fotos');

    foreach ($postulantes as $p) {
        $edad = calcular_edad($p['fecha_nacimiento'] ?? null);
        $nombre = $p['nombre'] ?: $p['consultor_nombre'];
        $apellido = $p['apellido'] ?: '';

        $promedioMatriz = round((
            ($p['m_locus'] ?? 3) +
            ($p['m_frustracion'] ?? 3) +
            ($p['m_vision'] ?? 3) +
            ($p['m_ambicion'] ?? 3) +
            ($p['m_creatividad'] ?? 3) +
            ($p['m_autonomia'] ?? 3) +
            ($p['m_empatia'] ?? 3) +
            ($p['m_gestion'] ?? 3)
        ) / 8, 2);

        $row = [
            $p['consultor_id'],
            $p['username'] ?? '',
            $nombre,
            $apellido,
            $p['fecha_nacimiento'] ?? '',
            $edad !== null ? $edad : '',
            $p['instagram_user'] ?? '',
            $p['telefono'] ?? '',
            $p['direccion'] ?? '',
            $p['grupo_sanguineo'] ?? '',
            $p['contacto_emergencia'] ?? '',
            !empty($p['trabaja_actualmente']) ? 'Sí' : 'No',
            $p['trabajo_lugar'] ?? '',
            $p['trabajo_horario'] ?? '',
            $p['trabajo_rol'] ?? '',
            $p['proyecto_nombre'] ?? 'Sin asignar',
            $p['m_locus'] ?? 3,
            $p['m_frustracion'] ?? 3,
            $p['m_vision'] ?? 3,
            $p['m_ambicion'] ?? 3,
            $p['m_creatividad'] ?? 3,
            $p['m_autonomia'] ?? 3,
            $p['m_empatia'] ?? 3,
            $p['m_gestion'] ?? 3,
            $promedioMatriz,
            $p['r_planificacion'] ?? '',
            $p['r_producto'] ?? '',
            $p['r_comercializacion'] ?? '',
            $p['r_resolucion'] ?? '',
            $p['foto_perfil'] ?? '',
            $p['created_at'] ?? '',
        ];
        fputcsv($csvBuffer, $row);

        // Añadir foto física al ZIP si existe
        if (!empty($p['foto_perfil'])) {
            $fotoFisica = __DIR__ . '/../' . ltrim($p['foto_perfil'], '/');
            if (file_exists($fotoFisica) && is_file($fotoFisica)) {
                $zip->addFile($fotoFisica, 'fotos/' . basename($fotoFisica));
            }
        }
    }

    rewind($csvBuffer);
    $csvContent = stream_get_contents($csvBuffer);
    fclose($csvBuffer);

    $zip->addFromString('postulantes_iset_815.csv', $csvContent);
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="postulantes-iset-815-' . date('Y-m-d') . '.zip"');
    header('Content-Length: ' . filesize($tmpZip));
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($tmpZip);
    @unlink($tmpZip);
    exit;
}

$pageTitle = 'Postulantes ISET 815 · Onboarding';
$nav = 'postulantes_iset';
require __DIR__ . '/_header.php';
?>

<style>
/* Grilla y Tarjetas de Postulantes */
.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.btn-zip {
  background: #1e293b;
  color: #ffffff;
  padding: 9px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  transition: all 0.2s;
  border: 1px solid #334155;
}
.btn-zip:hover {
  background: #0f172a;
  transform: translateY(-1px);
}

.grid-postulantes {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap: 22px;
  margin-top: 24px;
}
.card-postulante {
  background: var(--surface, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 14px;
  padding: 22px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
  display: flex;
  flex-direction: column;
  position: relative;
}
.card-postulante:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px -8px rgba(0,0,0,0.09);
  border-color: #2F5D7C;
}

.card-header-post {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}
.card-foto-grande {
  width: 74px;
  height: 74px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #cbd5e1;
  background: #f1f5f9;
  flex-shrink: 0;
}
.card-info-post {
  flex: 1;
  min-width: 0;
}
.card-nombre {
  font-size: 17px;
  font-weight: 700;
  color: var(--ink-1, #0f172a);
  margin: 0 0 4px 0;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-username-badge {
  display: inline-block;
  font-family: monospace;
  font-size: 12px;
  color: #2F5D7C;
  background: #e6f0f5;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
}
.card-edad {
  font-size: 13px;
  color: var(--ink-2, #64748b);
  margin-top: 4px;
}

.card-meta-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--border, #f1f5f9);
  font-size: 12px;
}
.pill-trabajo {
  background: #fef3c7;
  color: #92400e;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
}
.pill-exclusivo {
  background: #dcfce7;
  color: #166534;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
}
.pill-promedio {
  background: #e0e7ff;
  color: #3730a3;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 700;
  margin-left: auto;
}

/* Modal de Ficha Técnica */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
}
.modal-overlay.is-open {
  opacity: 1;
  pointer-events: auto;
}
.modal-dialog {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 960px;
  max-height: 90dvh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  transform: translateY(12px);
  transition: transform 0.2s ease;
  padding-top: max(16px, env(safe-area-inset-top));
}
.modal-overlay.is-open .modal-dialog {
  transform: translateY(0);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}
.modal-title {
  font-size: 19px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  font-family: var(--font-title, serif);
}
.modal-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
.btn-imprimir {
  background: #2F5D7C;
  color: #ffffff;
  padding: 7px 16px;
  border-radius: 6px;
  font-size: 13.5px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s;
}
.btn-imprimir:hover {
  background: #23475f;
}
.btn-cerrar-modal {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #64748b;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  line-height: 1;
}
.btn-cerrar-modal:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.modal-body {
  padding: 24px 28px;
  overflow-y: auto;
  font-size: 14.5px;
  line-height: 1.5;
  color: #334155;
}

/* Ficha del Estudiante dentro del Modal */
.ficha-perfil-top {
  display: flex;
  gap: 24px;
  align-items: center;
  padding-bottom: 22px;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 24px;
}
@media (max-width: 680px) {
  .ficha-perfil-top { flex-direction: column; text-align: center; }
}
.ficha-foto-modal {
  width: 104px;
  height: 104px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #2F5D7C;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.ficha-datos-persona h3 {
  margin: 0 0 6px 0;
  font-size: 24px;
  color: #0f172a;
  font-family: var(--font-title, serif);
}
.ficha-lista-contacto {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 24px;
  margin-top: 10px;
  font-size: 13.5px;
  color: #475569;
}
.ficha-contacto-item strong {
  color: #1e293b;
}

/* Barras de la Matriz */
.seccion-matriz {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}
.seccion-matriz h4 {
  margin: 0 0 16px 0;
  font-size: 16px;
  color: #0f172a;
  font-family: var(--font-title, serif);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.grid-barras {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px 28px;
}
@media (max-width: 680px) {
  .grid-barras { grid-template-columns: 1fr; }
}
.barra-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.barra-label-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}
.barra-track {
  height: 10px;
  background: #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  position: relative;
}
.barra-fill {
  height: 100%;
  background: linear-gradient(90deg, #4A7FA8, #2F5D7C);
  border-radius: 6px;
  transition: width 0.3s ease;
}
.barra-subtext {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #64748b;
  line-height: 1.25;
}

/* Respuestas de Texto */
.seccion-respuestas h4 {
  margin: 0 0 16px 0;
  font-size: 16px;
  color: #0f172a;
  font-family: var(--font-title, serif);
}
.bloque-respuesta {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #2F5D7C;
  border-radius: 8px;
  padding: 16px 18px;
  margin-bottom: 16px;
}
.bloque-pregunta {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}
.bloque-texto {
  font-size: 14px;
  line-height: 1.6;
  color: #334155;
  white-space: pre-wrap;
  margin: 0;
}

/* Cabecera para Impresión (oculta en pantalla) */
.print-header-dossier {
  display: none;
}

/* ==========================================================================
   CSS @MEDIA PRINT (Impresión limpia en hoja A4)
   ========================================================================== */
@media print {
  @page {
    size: A4 portrait;
    margin: 1.2cm 1.5cm;
  }
  body, .admin-body, html {
    background: #ffffff !important;
    color: #000000 !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  /* Ocultar toda la interfaz administrativa circundante */
  .admin-header, .admin-topbar, .admin-nav, .grid-postulantes, .no-print,
  .btn-cerrar-modal, .btn-imprimir, .modal-actions, .modal-header {
    display: none !important;
  }
  /* Desplegar el modal plano como documento */
  .modal-overlay {
    position: static !important;
    background: transparent !important;
    padding: 0 !important;
    display: block !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }
  .modal-dialog {
    max-width: 100% !important;
    width: 100% !important;
    box-shadow: none !important;
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    transform: none !important;
  }
  .modal-body {
    padding: 0 !important;
    overflow: visible !important;
  }

  /* Cabecera institucional A4 */
  .print-header-dossier {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #2F5D7C;
    padding-bottom: 12px;
    margin-bottom: 20px;
  }
  .print-header-dossier img {
    height: 48px;
  }
  .print-header-dossier .meta-print {
    text-align: right;
    font-size: 11px;
    color: #475569;
  }

  /* Asegurar colores de barras y fondos en PDF */
  .seccion-matriz, .bloque-respuesta {
    page-break-inside: avoid;
    break-inside: avoid;
  }
  .barra-fill {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: #2F5D7C !important;
  }
  .barra-track {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: #e2e8f0 !important;
  }
  .bloque-respuesta {
    border: 1px solid #cbd5e1 !important;
    border-left: 4px solid #2F5D7C !important;
  }
}
</style>

<div class="admin-topbar">
  <div>
    <h1>Postulantes ISET 815 · Onboarding</h1>
    <p style="margin:4px 0 0 0;font-size:14px;color:var(--ink-2);">
      Perfilado psicológico, técnico y de criterio turístico para asignación estratégica a los 18 emprendimientos.
    </p>
  </div>
  <div class="topbar-actions">
    <a href="?export=zip" class="btn-zip">
      📥 Descargar Todos (.ZIP)
    </a>
  </div>
</div>

<?php if (empty($postulantes)): ?>

  <div class="card" style="text-align:center;padding:48px 24px;margin-top:24px;">
    <div style="font-size:48px;margin-bottom:12px;">🎓</div>
    <h2 style="font-size:20px;margin:0 0 8px 0;">Todavía no hay postulaciones de estudiantes</h2>
    <p style="color:var(--ink-2);max-width:520px;margin:0 auto 20px;font-size:14.5px;">
      Los alumnos de la Tecnicatura Superior en Turismo (ISET 815) pueden registrarse y completar el formulario de autopercepción desde el enlace público.
    </p>
    <div style="background:#f1f5f9;padding:12px 18px;border-radius:8px;display:inline-block;font-family:monospace;font-size:13.5px;color:#2F5D7C;">
      <?= e(url('/registro_estudiantes.php')) ?>
    </div>
  </div>

<?php else: ?>

  <div class="grid-postulantes">
    <?php foreach ($postulantes as $idx => $p):
        $edad = calcular_edad($p['fecha_nacimiento'] ?? null);
        $nombre = $p['nombre'] ?: $p['consultor_nombre'];
        $apellido = $p['apellido'] ?: '';
        $foto = !empty($p['foto_perfil']) ? '../' . ltrim($p['foto_perfil'], '/') : '../assets/images/placeholder-avatar.svg';

        $promedioMatriz = round((
            ($p['m_locus'] ?? 3) +
            ($p['m_frustracion'] ?? 3) +
            ($p['m_vision'] ?? 3) +
            ($p['m_ambicion'] ?? 3) +
            ($p['m_creatividad'] ?? 3) +
            ($p['m_autonomia'] ?? 3) +
            ($p['m_empatia'] ?? 3) +
            ($p['m_gestion'] ?? 3)
        ) / 8, 1);
    ?>
      <div class="card-postulante" onclick="abrirFicha(<?= $idx ?>)">
        <div class="card-header-post">
          <img src="<?= e($foto) ?>" alt="<?= e($nombre) ?>" class="card-foto-grande" onerror="this.src='../assets/images/placeholder-avatar.svg'">
          <div class="card-info-post">
            <div class="card-nombre"><?= e($nombre . ' ' . $apellido) ?></div>
            <div class="card-username-badge">@<?= e($p['username'] ?: $p['consultor_id']) ?></div>
            <div class="card-edad">
              <?= $edad !== null ? e($edad) . ' años' : 'Edad sin informar' ?>
            </div>
          </div>
        </div>

        <div style="font-size:13px;color:#475569;margin-bottom:12px;line-height:1.4;">
          <?php if (!empty($p['proyecto_nombre'])): ?>
            🏢 <strong>Asignado:</strong> <?= e($p['proyecto_nombre']) ?>
          <?php else: ?>
            <span style="color:#64748b;">⏳ Pendiente de asignación</span>
          <?php endif; ?>
        </div>

        <div class="card-meta-pills">
          <?php if (!empty($p['trabaja_actualmente'])): ?>
            <span class="pill-trabajo" title="<?= e($p['trabajo_rol'] . ' en ' . $p['trabajo_lugar']) ?>">💼 Empleado</span>
          <?php else: ?>
            <span class="pill-exclusivo">🎓 Dedicación plena</span>
          <?php endif; ?>

          <span class="pill-promedio" title="Promedio de los 8 factores de la matriz">
            ★ <?= $promedioMatriz ?> / 5
          </span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<!-- ========================================================================
     MODAL DE FICHA TÉCNICA
     ======================================================================== -->
<div class="modal-overlay" id="modalFichaOverlay" onclick="cerrarFicha(event)">
  <div class="modal-dialog" onclick="event.stopPropagation()">

    <div class="modal-header">
      <h2 class="modal-title">Ficha Técnica de Postulante · ISET 815</h2>
      <div class="modal-actions">
        <button type="button" class="btn-imprimir" onclick="window.print()">
          🖨️ Imprimir / Guardar PDF
        </button>
        <button type="button" class="btn-cerrar-modal" onclick="cerrarModal()">
          &times;
        </button>
      </div>
    </div>

    <div class="modal-body" id="modalFichaContent">
      <!-- El contenido se inyecta dinámicamente mediante JS -->
    </div>

  </div>
</div>

<script>
const postulantesData = <?= json_encode($postulantes, JSON_UNESCAPED_UNICODE) ?>;

function abrirFicha(index) {
  const p = postulantesData[index];
  if (!p) return;

  const hoy = new Date();
  let edadTexto = '—';
  if (p.fecha_nacimiento) {
    const fn = new Date(p.fecha_nacimiento);
    let edad = hoy.getFullYear() - fn.getFullYear();
    const m = hoy.getMonth() - fn.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < fn.getDate())) {
      edad--;
    }
    edadTexto = edad + ' años';
  }

  const nombreCompleto = (p.nombre ? p.nombre : p.consultor_nombre) + (p.apellido ? ' ' + p.apellido : '');
  const fotoSrc = p.foto_perfil ? '../' + p.foto_perfil.replace(/^\/+/, '') : '../assets/images/placeholder-avatar.svg';

  // Instagram link directo
  let igHtml = '—';
  if (p.instagram_user && p.instagram_user.trim() !== '') {
    const igClean = p.instagram_user.replace(/^@/, '').trim();
    igHtml = `<a href="https://instagram.com/${encodeURIComponent(igClean)}" target="_blank" rel="noopener noreferrer" style="color:#2F5D7C;font-weight:700;text-decoration:none;">@${escapeHtml(igClean)} ↗</a>`;
  }

  // Dimensiones de la Matriz
  const dimensiones = [
    { k: 'm_locus', t: 'Iniciativa', izq: 'Indicaciones previas', der: 'Control y propuesta' },
    { k: 'm_frustracion', t: 'Tolerancia Frustración', izq: 'Desmotivación rápida', der: 'Recalcula y resuelve' },
    { k: 'm_vision', t: 'Visión de Sistema', izq: 'Tarea aislada', der: 'Impacto en todo el proyecto' },
    { k: 'm_ambicion', t: 'Ambición Profesional', izq: 'Básico aprobado', der: 'Destacarse y absorber' },
    { k: 'm_creatividad', t: 'Creatividad Operativa', izq: 'Recetas conocidas', der: 'Soluciones laterales' },
    { k: 'm_autonomia', t: 'Autonomía de Decisión', izq: 'Validar cada paso', der: 'Decide y luego informa' },
    { k: 'm_empatia', t: 'Empatía Comercial', izq: 'Aspecto técnico', der: 'Valor para el turista' },
    { k: 'm_gestion', t: 'Gestión del Tiempo', izq: 'Reacciona al día', der: 'Planifica y cumple plazos' }
  ];

  let matrizHtml = '';
  dimensiones.forEach(d => {
    const val = parseInt(p[d.k] || 3, 10);
    const pct = (val / 5) * 100;
    matrizHtml += `
      <div class="barra-item">
        <div class="barra-label-row">
          <span>${d.t}</span>
          <span style="color:#2F5D7C;">${val} / 5</span>
        </div>
        <div class="barra-track">
          <div class="barra-fill" style="width: ${pct}%;"></div>
        </div>
        <div class="barra-subtext">
          <span>1: ${d.izq}</span>
          <span>5: ${d.der}</span>
        </div>
      </div>
    `;
  });

  // Respuestas de desarrollo
  const respuestasHtml = `
    <div class="bloque-respuesta">
      <div class="bloque-pregunta">1. Planificación y Viabilidad (Riesgos fuera de temporada alta)</div>
      <p class="bloque-texto">${p.r_planificacion ? escapeHtml(p.r_planificacion) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
    </div>
    <div class="bloque-respuesta">
      <div class="bloque-pregunta">2. Desarrollo de Producto (Recurso subexplotado innovador)</div>
      <p class="bloque-texto">${p.r_producto ? escapeHtml(p.r_producto) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
    </div>
    <div class="bloque-respuesta">
      <div class="bloque-pregunta">3. Comercialización (Digitalización sin perder identidad local)</div>
      <p class="bloque-texto">${p.r_comercializacion ? escapeHtml(p.r_comercializacion) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
    </div>
    <div class="bloque-respuesta">
      <div class="bloque-pregunta">4. Pensamiento Crítico (Resolución con muy pocos recursos)</div>
      <p class="bloque-texto">${p.r_resolucion ? escapeHtml(p.r_resolucion) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
    </div>
  `;

  // Armado del contenido modal
  const content = `
    <!-- Cabecera Oficial para Impresión A4 -->
    <div class="print-header-dossier">
      <div>
        <img src="../assets/images/logo-esquel-lab.png" alt="Esquel LAB">
      </div>
      <div class="meta-print">
        <strong>Convenio ISET 815 · Cohorte 2026</strong><br>
        Ficha Técnica y Psicotécnica de Onboarding<br>
        Generado el: ${new Date().toLocaleDateString('es-AR')}
      </div>
    </div>

    <!-- Perfil Superior -->
    <div class="ficha-perfil-top">
      <img src="${escapeHtml(fotoSrc)}" alt="${escapeHtml(nombreCompleto)}" class="ficha-foto-modal" onerror="this.src='../assets/images/placeholder-avatar.svg'">
      <div class="ficha-datos-persona">
        <h3>${escapeHtml(nombreCompleto)}</h3>
        <div style="font-size:14px;color:#64748b;margin-bottom:8px;">
          Usuario: <strong style="color:#2F5D7C;font-family:monospace;">@${escapeHtml(p.username || p.consultor_id)}</strong> · ${escapeHtml(edadTexto)} (${p.fecha_nacimiento || 'Sin fecha'})
        </div>
        <div class="ficha-lista-contacto">
          <div class="ficha-contacto-item">
            <strong>WhatsApp:</strong> <a href="https://wa.me/${encodeURIComponent((p.telefono || '').replace(/[^0-9]/g, ''))}" target="_blank" rel="noopener noreferrer" style="color:#059669;font-weight:700;text-decoration:none;">${escapeHtml(p.telefono || '—')} ↗</a>
          </div>
          <div class="ficha-contacto-item">
            <strong>Instagram:</strong> ${igHtml}
          </div>
          <div class="ficha-contacto-item">
            <strong>Dirección:</strong> ${escapeHtml(p.direccion || '—')}
          </div>
          <div class="ficha-contacto-item">
            <strong>Grupo Sanguíneo:</strong> <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:4px;font-weight:700;">${escapeHtml(p.grupo_sanguineo || '—')}</span>
          </div>
          <div class="ficha-contacto-item">
            <strong>Contacto Emergencia:</strong> ${escapeHtml(p.contacto_emergencia || '—')}
          </div>
        </div>

        <div style="margin-top:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 14px;border-radius:8px;font-size:13.5px;">
          <strong>Situación Laboral:</strong> ${p.trabaja_actualmente == 1 ? `💼 <em>Trabaja en ${escapeHtml(p.trabajo_lugar || '—')} (${escapeHtml(p.trabajo_horario || '—')}) como ${escapeHtml(p.trabajo_rol || '—')}</em>` : '🎓 <em>Dedicación exclusiva al terciario y prácticas</em>'}
        </div>
      </div>
    </div>

    <!-- Matriz de Autopercepción -->
    <div class="seccion-matriz">
      <h4>
        <span>Matriz de Autopercepción (8 Dimensiones · 1 a 5)</span>
      </h4>
      <div class="grid-barras">
        ${matrizHtml}
      </div>
    </div>

    <!-- Respuestas Abiertas -->
    <div class="seccion-respuestas">
      <h4>Criterio Turístico y Pensamiento Crítico</h4>
      ${respuestasHtml}
    </div>
  `;

  document.getElementById('modalFichaContent').innerHTML = content;
  document.getElementById('modalFichaOverlay').classList.add('is-open');
}

function cerrarModal() {
  document.getElementById('modalFichaOverlay').classList.remove('is-open');
}

function cerrarFicha(event) {
  if (event.target === document.getElementById('modalFichaOverlay')) {
    cerrarModal();
  }
}

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    cerrarModal();
  }
});

function escapeHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php require __DIR__ . '/_footer.php'; ?>
