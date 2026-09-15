<?php
/**
 * Dashboard de Postulantes ISET 815 · Onboarding y Perfilado Psicotécnico
 *
 * Permite a la conducción y equipo de consultores auditar los perfiles
 * psicológicos, técnicos y de conducta pasada de los estudiantes del ISET 815
 * para la asignación estratégica a los 18 emprendimientos turísticos acelerados de Esquel LAB.
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

// Consultar nómina de estudiantes con datos de perfil y respuestas (excluyendo mocks)
$st = $pdo->query("
    SELECT e.*, c.nombre AS consultor_nombre, c.activo AS consultor_activo,
           u.username, u.created_at AS user_created_at,
           p.nombre AS proyecto_nombre
      FROM lab_estudiantes e
      JOIN lab_consultores c ON c.id = e.consultor_id
      LEFT JOIN users u ON u.id = e.user_id
      LEFT JOIN lab_proyectos p ON p.id = e.proyecto_id
     WHERE (u.username NOT LIKE 'iset%' AND u.username NOT GLOB 'iset[0-9][0-9]') OR u.username IS NULL
     ORDER BY (e.r_posicion != '' OR e.r_planificacion != '' OR e.foto_perfil != '') DESC, e.created_at DESC, e.consultor_id ASC
");
$postulantes = $st->fetchAll(PDO::FETCH_ASSOC);

// Emprendimientos para asignación estratégica 1 a 1
$proyectosDisponibles = $pdo->query("
    SELECT id, nombre, celula
      FROM lab_proyectos
     ORDER BY celula ASC, nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

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
        'Movilidad',
        'Dispositivo de Trabajo',
        'Trabaja Actualmente',
        'Lugar de Trabajo',
        'Horario de Trabajo',
        'Rol de Trabajo',
        'Emprendimiento Asignado',
        'Interés Más Fuerte',
        'Área Más Floja (Reforzar)',
        '1. Para arrancar (1=Confirmar, 5=Arrancar)',
        '2. Cuando algo se traba (1=Pedir ayuda, 5=Insistir solo)',
        '3. Ritmo y detalle (1=Rápido, 5=Lento y chequeado)',
        '4. Cómo rendís mejor (1=Marcando, 5=Objetivo y solo)',
        '5. Gente que no conocés (1=Cansa/solas, 5=Carga pilas)',
        '6. Cómo contás lo hecho (1=Hablando, 5=Escribiendo)',
        '7. Cómo preferís corrección (1=En el momento, 5=A solas)',
        '8. Adónde querés llegar (1=Estable/seguro, 5=Propio)',
        'CP 1: Organizar con otros',
        'CP 2: Contactar desconocido',
        'CP 3: Dejar algo a mitad',
        'CP 4: Cobrar plata propia',
        'CP 5: Crítica dura',
        'P1: Tomá posición (Planificación)',
        'P2: Producto (Estructurado / Detalle)',
        'P3: Tres movimientos (Comercialización)',
        'P4: Una cuenta de verdad (Comunicación)',
        'P5: Expectativa personal',
        'Foto Archivo',
        'Fecha de Registro',
    ];
    fputcsv($csvBuffer, $headers);

    $zip->addEmptyDir('fotos');

    foreach ($postulantes as $p) {
        $edad = calcular_edad($p['fecha_nacimiento'] ?? null);
        $nombre = $p['nombre'] ?: $p['consultor_nombre'];
        $apellido = $p['apellido'] ?: '';

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
            $p['movilidad'] ?? '',
            $p['dispositivo_trabajo'] ?? '',
            !empty($p['trabaja_actualmente']) ? 'Sí' : 'No',
            $p['trabajo_lugar'] ?? '',
            $p['trabajo_horario'] ?? '',
            $p['trabajo_rol'] ?? '',
            $p['proyecto_nombre'] ?? 'Sin asignar',
            $p['interes_fuerte'] ?? '',
            $p['interes_debil'] ?? '',
            $p['m_arrancar'] ?? ($p['m_locus'] ?? 3),
            $p['m_traba'] ?? ($p['m_frustracion'] ?? 3),
            $p['m_ritmo'] ?? ($p['m_vision'] ?? 3),
            $p['m_rendir'] ?? ($p['m_ambicion'] ?? 3),
            $p['m_gente'] ?? ($p['m_creatividad'] ?? 3),
            $p['m_comunicar'] ?? ($p['m_autonomia'] ?? 3),
            $p['m_correccion'] ?? ($p['m_empatia'] ?? 3),
            $p['m_destino'] ?? ($p['m_gestion'] ?? 3),
            $p['cp_organizar'] ?? '',
            $p['cp_contactar'] ?? '',
            $p['cp_inconcluso'] ?? '',
            $p['cp_cobrar'] ?? '',
            $p['cp_critica'] ?? '',
            $p['r_posicion'] ?: ($p['r_planificacion'] ?? ''),
            $p['r_producto'] ?? '',
            $p['r_movimientos'] ?: ($p['r_comercializacion'] ?? ''),
            $p['r_cuenta'] ?: ($p['r_resolucion'] ?? ''),
            $p['r_expectativa'] ?? '',
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
/* Barra Superior y Botones */
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

/* Grilla y Tarjetas de Postulantes */
.grid-postulantes {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
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
  margin-bottom: 14px;
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
  gap: 6px;
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--border, #f1f5f9);
  font-size: 11.5px;
}
.pill-base {
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.pill-trabajo { background: #fef3c7; color: #92400e; }
.pill-exclusivo { background: #dcfce7; color: #166534; }
.pill-movilidad { background: #e0f2fe; color: #0369a1; }
.pill-disp { background: #f3e8ff; color: #6b21a8; }
.pill-interes { background: #fae8ff; color: #86198f; font-weight: 700; margin-left: auto; }

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
  max-width: 980px;
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
  padding: 16px 24px;
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
.btn-imprimir:hover { background: #23475f; }
.btn-eliminar-modal {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
  padding: 7px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}
.btn-eliminar-modal:hover {
  background: #ef4444;
  color: #ffffff;
  border-color: #dc2626;
}
.btn-borrar-card {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  padding: 2px 7px;
  border-radius: 4px;
  line-height: 1;
  transition: all 0.15s;
}
.btn-borrar-card:hover {
  background: #fee2e2;
  color: #dc2626;
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
  gap: 12px 20px;
  margin-top: 10px;
  font-size: 13.5px;
  color: #475569;
}
.ficha-contacto-item strong {
  color: #1e293b;
}

/* Bloque Interpretativo y Guía */
.guia-box {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 20px;
  font-size: 13px;
  color: #166534;
  line-height: 1.5;
}
.guia-box strong {
  color: #14532d;
}

/* Secciones del Modal */
.seccion-admin-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}
.seccion-admin-box h4 {
  margin: 0 0 14px 0;
  font-size: 16px;
  color: #0f172a;
  font-family: var(--font-title, serif);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Grilla de Barras de Matriz */
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

/* Conducta Pasada List */
.lista-conducta {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.conducta-fila {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.conducta-txt {
  font-size: 13.5px;
  color: #1e293b;
  flex: 1;
  line-height: 1.4;
}
.badge-conducta {
  padding: 4px 10px;
  border-radius: 14px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}
.badge-nunca { background: #f1f5f9; color: #64748b; }
.badge-una { background: #fef3c7; color: #92400e; }
.badge-algunas { background: #dbeafe; color: #1e40af; }
.badge-muchas { background: #dcfce7; color: #166534; font-weight: 800; }

/* Respuestas de Texto y Producto */
.bloque-respuesta {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #2F5D7C;
  border-radius: 8px;
  padding: 16px 18px;
  margin-bottom: 16px;
}
.bloque-pregunta {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.bloque-subarea {
  font-size: 11.5px;
  color: #2F5D7C;
  text-transform: uppercase;
  font-weight: 700;
}
.bloque-texto {
  font-size: 14px;
  line-height: 1.6;
  color: #334155;
  white-space: pre-wrap;
  margin: 0;
}

/* Tabla estructurada de producto */
.tabla-prod-6 {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  font-size: 13.5px;
}
.tabla-prod-6 th {
  text-align: left;
  background: #f1f5f9;
  padding: 8px 12px;
  color: #475569;
  font-weight: 700;
  border: 1px solid #e2e8f0;
  width: 32%;
}
.tabla-prod-6 td {
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  line-height: 1.45;
}

/* Cabecera para Impresión A4 */
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
  .admin-header, .admin-topbar, .admin-nav, .grid-postulantes, .no-print,
  .btn-cerrar-modal, .btn-imprimir, .modal-actions, .modal-header {
    display: none !important;
  }
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
  .seccion-admin-box, .bloque-respuesta {
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
}
</style>

<div class="admin-topbar">
  <div>
    <h1>Postulantes ISET 815 · Onboarding</h1>
    <p style="margin:4px 0 0 0;font-size:14px;color:var(--ink-2);">
      Perfilado psicológico, técnico y de conducta pasada para asignación estratégica a los 18 emprendimientos.
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
    ?>
      <div class="card-postulante" id="card-postulante-<?= e($p['consultor_id']) ?>" onclick="abrirFicha(<?= $idx ?>)">
        <div class="card-header-post">
          <img src="<?= e($foto) ?>" alt="<?= e($nombre) ?>" class="card-foto-grande" onerror="this.src='../assets/images/placeholder-avatar.svg'">
          <div class="card-info-post">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;">
              <div class="card-nombre"><?= e($nombre . ' ' . $apellido) ?></div>
              <?php if (($u['role'] ?? '') === 'admin'): ?>
                <button type="button" class="btn-borrar-card no-print" onclick="eliminarEstudianteDirecto(event, '<?= e($p['consultor_id']) ?>', '<?= e(addslashes($nombre . ' ' . $apellido)) ?>')" title="Eliminar estudiante definitivamente">
                  ✕
                </button>
              <?php endif; ?>
            </div>
            <div class="card-username-badge">@<?= e($p['username'] ?: $p['consultor_id']) ?></div>
            <div class="card-edad">
              <?= $edad !== null ? e($edad) . ' años' : 'Edad sin informar' ?>
            </div>
          </div>
        </div>

        <div id="card-badge-proy-<?= e($p['consultor_id']) ?>" style="font-size:13px;color:#475569;margin-bottom:12px;line-height:1.4;">
          <?php if (!empty($p['proyecto_nombre'])): ?>
            🏢 <strong>Asignado:</strong> <span class="txt-badge-proy"><?= e($p['proyecto_nombre']) ?></span>
          <?php else: ?>
            <span class="txt-badge-proy" style="color:#64748b;">⏳ Pendiente de asignación</span>
          <?php endif; ?>
        </div>

        <div class="card-meta-pills">
          <?php if (!empty($p['trabaja_actualmente'])): ?>
            <span class="pill-base pill-trabajo" title="<?= e($p['trabajo_rol'] . ' en ' . $p['trabajo_lugar']) ?>">💼 Empleado</span>
          <?php else: ?>
            <span class="pill-base pill-exclusivo">🎓 Exclusivo</span>
          <?php endif; ?>

          <?php if (!empty($p['movilidad'])): ?>
            <span class="pill-base pill-movilidad" title="Movilidad principal">
              🛵 <?= e(ucfirst($p['movilidad'])) ?>
            </span>
          <?php endif; ?>

          <?php if (!empty($p['dispositivo_trabajo'])): ?>
            <span class="pill-base pill-disp" title="Equipamiento">
              💻 <?= e(ucfirst($p['dispositivo_trabajo'])) ?>
            </span>
          <?php endif; ?>

          <?php if (!empty($p['interes_fuerte'])): ?>
            <span class="pill-base pill-interes" title="Interés preferente">
              🎯 <?= e($p['interes_fuerte']) ?>
            </span>
          <?php endif; ?>
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
        <?php if (($u['role'] ?? '') === 'admin'): ?>
          <button type="button" class="btn-eliminar-modal no-print" onclick="eliminarDesdeModal()" title="Eliminar estudiante definitivamente">
            🗑️ Eliminar
          </button>
        <?php endif; ?>
        <button type="button" class="btn-imprimir" onclick="window.print()">
          🖨️ Imprimir / Guardar PDF
        </button>
        <button type="button" class="btn-cerrar-modal" onclick="cerrarModal()">
          &times;
        </button>
      </div>
    </div>

    <div class="modal-body" id="modalFichaContent">
      <!-- Inyectado dinámicamente mediante JS -->
    </div>

  </div>
</div>

<script>
const postulantesData = <?= json_encode($postulantes, JSON_UNESCAPED_UNICODE) ?>;
const proyectosLista = <?= json_encode($proyectosDisponibles, JSON_UNESCAPED_UNICODE) ?>;
const csrfToken = <?= json_encode(csrf_token()) ?>;
let currentFichaIndex = null;

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function linkifyInstagram(text) {
  if (!text) return '';
  const escaped = escapeHtml(text);
  return escaped.replace(/@([a-zA-Z0-9._]+)/g, function(match, username) {
    return `<a href="https://instagram.com/${encodeURIComponent(username)}" target="_blank" rel="noopener noreferrer" style="color:#2F5D7C;font-weight:700;text-decoration:none;">@${username} ↗</a>`;
  });
}

function badgeConducta(val) {
  if (!val) return '<span class="badge-conducta badge-nunca">Sin respuesta</span>';
  val = String(val).trim();
  if (val === 'Nunca') return '<span class="badge-conducta badge-nunca">Nunca</span>';
  if (val === 'Una vez') return '<span class="badge-conducta badge-una">Una vez</span>';
  if (val === 'Algunas veces') return '<span class="badge-conducta badge-algunas">Algunas veces</span>';
  if (val === 'Muchas veces') return '<span class="badge-conducta badge-muchas">Muchas veces ★</span>';
  return `<span class="badge-conducta badge-algunas">${escapeHtml(val)}</span>`;
}

function abrirFicha(index) {
  currentFichaIndex = index;
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

  // Selector de asignación a emprendimiento
  let proyectosOptionsHtml = '';
  proyectosLista.forEach(pr => {
    const asignadoAOtro = postulantesData.find(other => other.consultor_id !== p.consultor_id && other.proyecto_id === pr.id);
    const selected = (p.proyecto_id === pr.id) ? 'selected' : '';
    const tagOcupado = asignadoAOtro ? ` (Ocupado: ${asignadoAOtro.nombre || asignadoAOtro.consultor_nombre})` : '';
    proyectosOptionsHtml += `<option value="${escapeHtml(pr.id)}" ${selected}>${escapeHtml(pr.nombre)}${escapeHtml(tagOcupado)}</option>`;
  });

  // Instagram link directo
  let igHtml = '—';
  if (p.instagram_user && p.instagram_user.trim() !== '') {
    const igClean = p.instagram_user.replace(/^@/, '').trim();
    igHtml = `<a href="https://instagram.com/${encodeURIComponent(igClean)}" target="_blank" rel="noopener noreferrer" style="color:#2F5D7C;font-weight:700;text-decoration:none;">@${escapeHtml(igClean)} ↗</a>`;
  }

  // 1. Matriz de Trabajo (8 Filas Neutrales)
  const dimensiones = [
    { k: 'm_arrancar', fallback: 'm_locus', t: '1. Para arrancar', izq: 'Confirmar antes de moverme', der: 'Arrancar y corregir sobre la marcha' },
    { k: 'm_traba', fallback: 'm_frustracion', t: '2. Cuando algo se traba', izq: 'Pedir ayuda rápido', der: 'Insistir solo hasta destrabarlo' },
    { k: 'm_ritmo', fallback: 'm_vision', t: '3. Ritmo y detalle', izq: 'Avanzar rápido', der: 'Lento y chequeado' },
    { k: 'm_rendir', fallback: 'm_ambicion', t: '4. Cómo rendís mejor', izq: 'Con alguien marcando', der: 'Objetivo y me dejan solo' },
    { k: 'm_gente', fallback: 'm_creatividad', t: '5. Gente que no conocés', izq: 'Me cansa / a solas', der: 'Me carga pilas' },
    { k: 'm_comunicar', fallback: 'm_autonomia', t: '6. Cómo contás lo hecho', izq: 'Hablándolo', der: 'Escribiéndolo' },
    { k: 'm_correccion', fallback: 'm_empatia', t: '7. Cómo preferís corrección', izq: 'En el momento', der: 'Después, a solas' },
    { k: 'm_destino', fallback: 'm_gestion', t: '8. Adónde querés llegar', izq: 'Estable y seguro', der: 'Algo propio' },
  ];

  let matrizHtml = '';
  dimensiones.forEach(d => {
    let val = parseInt(p[d.k] || p[d.fallback] || 3, 10);
    if (isNaN(val) || val < 1 || val > 5) val = 3;
    const pct = (val / 5) * 100;
    matrizHtml += `
      <div class="barra-item">
        <div class="barra-label-row">
          <span>${d.t}</span>
          <span style="color:#2F5D7C;font-weight:800;">${val} / 5</span>
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

  // 2. Conducta Pasada
  const preguntasCp = [
    { k: 'cp_organizar', t: 'Organizaste algo que dependía de que otros aparecieran (juntada, viaje, torneo, trabajo grupal)' },
    { k: 'cp_contactar', t: 'Le escribiste a alguien que no conocías para pedir algo (info, presupuesto, changa, entrevista)' },
    { k: 'cp_inconcluso', t: 'Empezaste algo por tu cuenta y lo dejaste por la mitad' },
    { k: 'cp_cobrar', t: 'Cobraste plata por algo propio (venta, trabajo suelto, servicio, no sueldo)' },
    { k: 'cp_critica', t: 'Alguien te hizo una crítica dura y terminaste cambiando lo que estabas haciendo' },
  ];

  let conductaHtml = '';
  preguntasCp.forEach(cp => {
    conductaHtml += `
      <div class="conducta-fila">
        <div class="conducta-txt">${cp.t}</div>
        <div>${badgeConducta(p[cp.k])}</div>
      </div>
    `;
  });

  // 3. Desglose de Producto (P2)
  let p2RenderHtml = '';
  let jsonProd = null;
  if (p.r_producto_json && p.r_producto_json.trim() !== '' && p.r_producto_json !== '{}') {
    try {
      jsonProd = JSON.parse(p.r_producto_json);
    } catch(e) {}
  }

  if (jsonProd && (jsonProd.incluye || jsonProd.duracion || jsonProd.precio)) {
    p2RenderHtml = `
      <table class="tabla-prod-6">
        <tr><th>Lugar / Productor real</th><td><strong>${escapeHtml(jsonProd.lugar_persona || 'No especificado')}</strong></td></tr>
        <tr><th>1. Qué incluye</th><td>${escapeHtml(jsonProd.incluye || '—')}</td></tr>
        <tr><th>2. Cuánto dura</th><td>${escapeHtml(jsonProd.duracion || '—')}</td></tr>
        <tr><th>3. Cuánta gente por vez</th><td>${escapeHtml(jsonProd.capacidad || '—')}</td></tr>
        <tr><th>4. Día y horario</th><td>${escapeHtml(jsonProd.horario || '—')}</td></tr>
        <tr><th>5. Precio y cálculo</th><td>${escapeHtml(jsonProd.precio || '—')}</td></tr>
        <tr><th>6. Qué le falta hoy para venderlo</th><td>${escapeHtml(jsonProd.falta || '—')}</td></tr>
      </table>
    `;
  } else {
    p2RenderHtml = `<p class="bloque-texto">${p.r_producto ? escapeHtml(p.r_producto) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>`;
  }

  // Textos de P1, P3, P4, P5
  const textoP1 = p.r_posicion || p.r_planificacion || '';
  const textoP3 = p.r_movimientos || p.r_comercializacion || '';
  const textoP4 = p.r_cuenta || p.r_resolucion || '';
  const textoP5 = p.r_expectativa || '';

  // Armado completo del Modal
  const content = `
    <!-- Cabecera Oficial para Impresión A4 -->
    <div class="print-header-dossier">
      <div>
        <img src="../assets/images/logo-esquel-lab.png" alt="Esquel LAB">
      </div>
      <div class="meta-print">
        <strong>Convenio ISET 815 · Cohorte 2026</strong><br>
        Dossier Técnico y de Conducta de Postulante<br>
        Generado el: ${new Date().toLocaleDateString('es-AR')}
      </div>
    </div>

    <!-- Perfil Superior -->
    <div class="ficha-perfil-top">
      <img src="${escapeHtml(fotoSrc)}" alt="${escapeHtml(nombreCompleto)}" class="ficha-foto-modal" onerror="this.src='../assets/images/placeholder-avatar.svg'">
      <div class="ficha-datos-persona" style="flex:1;">
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
            <strong>Emergencia:</strong> ${escapeHtml(p.contacto_emergencia || '—')}
          </div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
          <span class="pill-base pill-movilidad">🛵 Movilidad: <strong>${escapeHtml(p.movilidad || 'Sin dato')}</strong></span>
          <span class="pill-base pill-disp">💻 Dispositivo: <strong>${escapeHtml(p.dispositivo_trabajo || 'Sin dato')}</strong></span>
          ${p.trabaja_actualmente == 1 
            ? `<span class="pill-base pill-trabajo">💼 Empleado: ${escapeHtml(p.trabajo_rol || '')} en ${escapeHtml(p.trabajo_lugar || '')} (${escapeHtml(p.trabajo_horario || '')})</span>` 
            : `<span class="pill-base pill-exclusivo">🎓 Dedicación exclusiva al terciario</span>`}
        </div>

        <!-- Asignación de Emprendimiento Esquel LAB -->
        <div class="ficha-asignacion-box" style="margin-top:14px;background:#f0f7ff;border:1.5px solid #bae6fd;padding:12px 16px;border-radius:10px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="flex:1;min-width:200px;">
              <label for="select-proy-${escapeHtml(p.consultor_id)}" style="display:block;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#0284c7;margin-bottom:3px;">
                🏢 Emprendimiento Asignado (Esquel LAB)
              </label>
              <div style="font-size:13.5px;color:#1e293b;" id="estado-asignacion-${escapeHtml(p.consultor_id)}">
                ${p.proyecto_nombre ? `Vinculado actualmente con <strong>${escapeHtml(p.proyecto_nombre)}</strong>` : '<span style="color:#64748b;">⏳ Sin vinculación activa todavía</span>'}
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <select id="select-proy-${escapeHtml(p.consultor_id)}"
                      class="select-asignar-proy no-print"
                      onchange="guardarAsignacionProyecto('${escapeHtml(p.consultor_id)}', this.value, ${index})"
                      style="font-size:13.5px;padding:7px 12px;border:1.5px solid #0284c7;border-radius:6px;background:#ffffff;color:#0f172a;font-weight:600;outline:none;cursor:pointer;max-width:320px;">
                <option value="">— Sin asignar / Pendiente —</option>
                ${proyectosOptionsHtml}
              </select>
              <span id="spinner-proy-${escapeHtml(p.consultor_id)}" style="display:none;font-size:12.5px;color:#0284c7;font-weight:600;">⏳ Guardando...</span>
            </div>
          </div>
          <div id="feedback-proy-${escapeHtml(p.consultor_id)}" style="display:none;margin-top:8px;font-size:12.5px;padding:6px 10px;border-radius:6px;font-weight:600;"></div>
        </div>
      </div>
    </div>

    <!-- Áreas de Interés (Doble select) -->
    <div style="background:#fdf4ff;border:1px solid #f0abfc;border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;font-size:13.5px;">
      <div>
        🎯 <strong>Área de mayor interés:</strong> <span style="color:#86198f;font-weight:700;">${escapeHtml(p.interes_fuerte || 'Sin especificar')}</span>
      </div>
      <div>
        ⚠️ <strong>Área en la que se siente más flojo/a:</strong> <span style="color:#9d174d;font-weight:700;">${escapeHtml(p.interes_debil || 'Sin especificar')}</span>
      </div>
    </div>

    <!-- Sección 1: Cómo Trabaja (Autopercepción · 8 filas) -->
    <div class="seccion-admin-box">
      <h4>
        <span>Cómo trabaja (Autopercepción · 8 filas · 1 a 5)</span>
      </h4>
      <div style="background:#eff6ff;border-left:3px solid #3b82f6;padding:10px 14px;border-radius:4px;font-size:12.5px;color:#1e40af;margin-bottom:14px;line-height:1.4;">
        ℹ️ <em>La grilla de ocho filas es autopercepción: mide cómo se ve la persona, no cómo es. Sirve para elegir con qué caso ponerla y qué preguntarle en la primera charla; no sirve para ordenar de mejor a peor.</em>
      </div>
      <div class="grid-barras">
        ${matrizHtml}
      </div>
    </div>

    <!-- Sección 2: Conducta Pasada -->
    <div class="seccion-admin-box">
      <h4>
        <span>Conducta Pasada ("Qué te pasó últimamente")</span>
      </h4>
      
      <!-- Guía de lectura NPC vs Main Character -->
      <div class="guia-box">
        <strong>💡 Criterio técnico para leer conducta pasada (NPC vs. Main Character):</strong>
        <ul style="margin:6px 0 0 0;padding-left:18px;line-height:1.45;">
          <li><strong>El ítem 3 ("dejó algo por la mitad")</strong> parece un defecto y es el más importante: quien nunca dejó nada por la mitad es, casi siempre, quien nunca empezó nada.</li>
          <li><strong>"Nunca" en 1, 2 y 4 y también "nunca" en 3:</strong> Perfil que no arranca solo. Requiere un consultor encima y consignas cerradas. Asignar a un emprendimiento ordenado con titular activo.</li>
          <li><strong>"Algunas/muchas" en 1, 2 y 4, con 3 en "algunas":</strong> El que arranca. Va al emprendimiento trabado donde hay que empujar proactivamente.</li>
          <li><strong>Alto en 2:</strong> Sale a la calle (relevamientos, proveedores, entrevistas).</li>
          <li><strong>Alto en 5:</strong> Aguanta la corrección técnica (se le pueden pedir borradores y devolvérselos corregidos).</li>
        </ul>
      </div>

      <div class="lista-conducta">
        ${conductaHtml}
      </div>
    </div>

    <!-- Sección 3: Criterio Turístico y Casos Reales -->
    <div class="seccion-admin-box">
      <h4>Criterio Turístico y Respuestas a Casos Reales</h4>
      
      <div class="bloque-respuesta">
        <div class="bloque-pregunta">
          <span>1. Planificación: ¿Le falta promoción a Esquel?</span>
          <span class="bloque-subarea">Min. 500 chars · Long: ${textoP1.length}</span>
        </div>
        <p class="bloque-texto">${textoP1 ? escapeHtml(textoP1) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
      </div>

      <div class="bloque-respuesta">
        <div class="bloque-pregunta">
          <span>2. Desarrollo de Producto Comprable (6 campos estructurados)</span>
          <span class="bloque-subarea">Producto</span>
        </div>
        ${p2RenderHtml}
      </div>

      <div class="bloque-respuesta">
        <div class="bloque-pregunta">
          <span>3. Comercialización: Tres movimientos para caso a 12 km</span>
          <span class="bloque-subarea">Min. 500 chars · Long: ${textoP3.length}</span>
        </div>
        <p class="bloque-texto">${textoP3 ? escapeHtml(textoP3) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
      </div>

      <div class="bloque-respuesta">
        <div class="bloque-pregunta">
          <span>4. Comunicación: Auditoría de cuenta real de Instagram</span>
          <span class="bloque-subarea">Min. 500 chars · Long: ${textoP4.length}</span>
        </div>
        <p class="bloque-texto">${textoP4 ? linkifyInstagram(textoP4) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
      </div>

      <div class="bloque-respuesta">
        <div class="bloque-pregunta">
          <span>5. Expectativa Personal: ¿Por qué estás acá y qué te querés llevar?</span>
          <span class="bloque-subarea">Mini expectativa</span>
        </div>
        <p class="bloque-texto">${textoP5 ? escapeHtml(textoP5) : '<em style="color:#94a3b8;">Sin respuesta registrada.</em>'}</p>
      </div>
    </div>
  `;

  document.getElementById('modalFichaContent').innerHTML = content;
  document.getElementById('modalFichaOverlay').classList.add('is-open');
}

function cerrarModal() {
  document.getElementById('modalFichaOverlay').classList.remove('is-open');
  currentFichaIndex = null;
}

function cerrarFicha(event) {
  if (event.target.id === 'modalFichaOverlay') {
    cerrarModal();
  }
}

async function guardarAsignacionProyecto(consultorId, proyId, index) {
  const spinner = document.getElementById(`spinner-proy-${consultorId}`);
  const feedback = document.getElementById(`feedback-proy-${consultorId}`);
  const estadoTxt = document.getElementById(`estado-asignacion-${consultorId}`);
  const cardBadge = document.getElementById(`card-badge-proy-${consultorId}`);

  if (spinner) spinner.style.display = 'inline';
  if (feedback) feedback.style.display = 'none';

  try {
    const res = await fetch('estudiantes_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        csrf: csrfToken,
        accion: 'asignar_proyecto',
        estudiante_id: consultorId,
        proyecto_id: proyId
      })
    });

    const data = await res.json();
    if (spinner) spinner.style.display = 'none';

    if (data.ok) {
      postulantesData[index].proyecto_id = data.proyecto_id;
      postulantesData[index].proyecto_nombre = data.proyecto_nombre;

      if (data.proyecto_id) {
        postulantesData.forEach((other, oIdx) => {
          if (oIdx !== index && other.proyecto_id === data.proyecto_id) {
            other.proyecto_id = null;
            other.proyecto_nombre = null;
            const otherBadge = document.getElementById(`card-badge-proy-${other.consultor_id}`);
            if (otherBadge) {
              otherBadge.innerHTML = '<span class="txt-badge-proy" style="color:#64748b;">⏳ Pendiente de asignación</span>';
            }
          }
        });
      }

      if (estadoTxt) {
        estadoTxt.innerHTML = data.proyecto_nombre
          ? `Vinculado actualmente con <strong>${escapeHtml(data.proyecto_nombre)}</strong>`
          : '<span style="color:#64748b;">⏳ Sin vinculación activa todavía</span>';
      }

      if (cardBadge) {
        cardBadge.innerHTML = data.proyecto_nombre
          ? `🏢 <strong>Asignado:</strong> <span class="txt-badge-proy">${escapeHtml(data.proyecto_nombre)}</span>`
          : '<span class="txt-badge-proy" style="color:#64748b;">⏳ Pendiente de asignación</span>';
      }

      if (feedback) {
        feedback.style.display = 'block';
        feedback.style.background = '#dcfce7';
        feedback.style.color = '#166534';
        feedback.style.border = '1px solid #bbf7d0';
        feedback.textContent = '✓ ' + data.mensaje;
      }
    } else {
      if (feedback) {
        feedback.style.display = 'block';
        feedback.style.background = '#fee2e2';
        feedback.style.color = '#991b1b';
        feedback.style.border = '1px solid #fecaca';
        feedback.textContent = '✕ Error: ' + (data.error || 'No se pudo guardar la asignación.');
      }
    }
  } catch (err) {
    if (spinner) spinner.style.display = 'none';
    if (feedback) {
      feedback.style.display = 'block';
      feedback.style.background = '#fee2e2';
      feedback.style.color = '#991b1b';
      feedback.style.border = '1px solid #fecaca';
      feedback.textContent = '✕ Error de conexión al guardar.';
    }
  }
}

async function eliminarEstudiante(consultorId, nombre) {
  const seguro = confirm(`¿Estás seguro de que deseás eliminar a «${nombre}»?\n\nEsta acción es definitiva y borrará su ficha, usuario, respuestas de autopercepción y foto de perfil.`);
  if (!seguro) return false;

  try {
    const res = await fetch('estudiantes_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        csrf: csrfToken,
        accion: 'eliminar_estudiante',
        estudiante_id: consultorId
      })
    });
    const data = await res.json();
    if (data.ok) {
      if (currentFichaIndex !== null && postulantesData[currentFichaIndex] && postulantesData[currentFichaIndex].consultor_id === consultorId) {
        cerrarModal();
      }

      const card = document.getElementById(`card-postulante-${consultorId}`);
      if (card) {
        card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.92)';
        setTimeout(() => {
          card.remove();
          const restantes = document.querySelectorAll('.card-postulante');
          if (restantes.length === 0) {
            window.location.reload();
          }
        }, 260);
      } else {
        window.location.reload();
      }
      return true;
    } else {
      alert('Error: ' + (data.error || 'No se pudo eliminar al estudiante.'));
      return false;
    }
  } catch (err) {
    alert('Error de conexión al intentar eliminar.');
    return false;
  }
}

function eliminarDesdeModal() {
  if (currentFichaIndex === null || !postulantesData[currentFichaIndex]) return;
  const p = postulantesData[currentFichaIndex];
  const nombre = (p.nombre ? p.nombre : p.consultor_nombre) + (p.apellido ? ' ' + p.apellido : '');
  eliminarEstudiante(p.consultor_id, nombre);
}

function eliminarEstudianteDirecto(event, consultorId, nombre) {
  event.stopPropagation();
  eliminarEstudiante(consultorId, nombre);
}
</script>

<?php require __DIR__ . '/_footer.php'; ?>