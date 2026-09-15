<?php
/**
 * Vista del estudiante ISET 815.
 *
 * Es la única pantalla que ve un estudiante, y tiene que responder cuatro
 * preguntas sin que tenga que preguntarle nada a nadie:
 *
 *   ¿con qué emprendimiento trabajo?   ¿qué tengo que hacer ahora?
 *   ¿cuándo es la próxima reunión?     ¿cuántas horas llevo?
 *
 * Qué NO se le muestra, y es a propósito: los datos de contacto del
 * emprendedor. El estudiante trabaja con el proyecto, no le escribe al titular
 * por su cuenta —eso lo coordina el consultor— y el teléfono y el correo de
 * una persona no tienen por qué circular más de lo necesario. Lo que sí ve es
 * todo el contexto del caso: diagnóstico, trabas, ejes y la minuta de arranque.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/estudiantes.php';

$u = requiere_estudiante();
$pdo = db();

// El admin puede espiar la vista de cualquiera con ?est=, para poder
// acompañar a un estudiante que no entiende qué tiene que hacer.
$ficha = null;
if (($u['role'] ?? '') === 'admin' && !empty($_GET['est'])) {
    $st = $pdo->prepare(
        "SELECT e.*, c.nombre, c.color, p.nombre AS proyecto_nombre, p.titular, p.celula, p.linea
           FROM lab_estudiantes e
           JOIN lab_consultores c ON c.id = e.consultor_id
           LEFT JOIN lab_proyectos p ON p.id = e.proyecto_id
          WHERE e.consultor_id = ?"
    );
    $st->execute([(string) $_GET['est']]);
    $ficha = $st->fetch() ?: null;
} else {
    $ficha = ficha_estudiante($u);
}

$pageTitle = 'Mi emprendimiento';
$nav = 'estudiante';

if (!$ficha || empty($ficha['proyecto_id'])) {
    $nombreAlumno = $ficha ? ($ficha['nombre'] ?: $u['username']) : $u['username'];
    $pageTitle = 'Esquel LAB · Prácticas ISET 815';
    require __DIR__ . '/_header.php';
    ?>
    <style>
    .espera-container {
      max-width: 860px;
      margin: 16px auto 40px;
    }
    .espera-hero {
      background: linear-gradient(135deg, #132B43 0%, #2F5D7C 100%);
      color: #ffffff;
      border-radius: 16px;
      padding: 34px 30px;
      box-shadow: 0 12px 32px -8px rgba(19, 43, 67, 0.35);
      margin-bottom: 22px;
    }
    .espera-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.28);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 14px;
    }
    .espera-hero h1 {
      font-size: 26px;
      margin: 0 0 12px 0;
      font-family: var(--font-title, serif);
      line-height: 1.25;
      color: #ffffff;
    }
    .espera-hero p {
      font-size: 15px;
      line-height: 1.6;
      opacity: 0.94;
      margin: 0;
      max-width: 720px;
    }

    /* Cuenta Regresiva */
    .countdown-card {
      background: #ffffff;
      border: 1.5px solid #e2e8f0;
      border-radius: 14px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 4px 16px -4px rgba(0,0,0,0.06);
      text-align: center;
    }
    .countdown-title {
      font-size: 17px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 6px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .countdown-sub {
      font-size: 14px;
      color: #64748b;
      margin: 0 auto 18px;
      max-width: 620px;
      line-height: 1.5;
    }
    .countdown-sub strong {
      color: #dc2626;
    }
    .countdown-grid {
      display: flex;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .countdown-box {
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      min-width: 84px;
      padding: 12px 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .countdown-num {
      font-size: 32px;
      font-weight: 800;
      color: #2F5D7C;
      font-family: var(--font-mono, monospace);
      line-height: 1;
    }
    .countdown-lbl {
      font-size: 11px;
      text-transform: uppercase;
      color: #64748b;
      font-weight: 700;
      margin-top: 6px;
      letter-spacing: 0.5px;
    }

    /* Tarjetas Explicativas */
    .grid-info-espera {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 22px;
    }
    @media (max-width: 700px) {
      .grid-info-espera { grid-template-columns: 1fr; }
    }
    .card-info-espera {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .card-info-espera h3 {
      margin: 0;
      font-size: 16.5px;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .card-info-espera p, .card-info-espera li {
      font-size: 13.5px;
      line-height: 1.55;
      color: #334155;
    }
    .card-info-espera ul {
      margin: 0;
      padding-left: 20px;
    }

    .pasos-consignas {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 14px;
      padding: 22px 24px;
      margin-top: 10px;
    }
    .pasos-consignas h4 {
      margin: 0 0 14px 0;
      color: #166534;
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .pasos-lista {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .paso-item {
      display: flex;
      gap: 12px;
      font-size: 13.5px;
      color: #1e293b;
      align-items: flex-start;
    }
    .paso-pill {
      background: #166534;
      color: #ffffff;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 4px;
      height: fit-content;
      white-space: nowrap;
    }
    </style>

    <div class="admin-content espera-container">
      <!-- Banner Hero -->
      <div class="espera-hero">
        <div class="espera-tag">🎓 Convenio ISET 815 · Cohorte 2026</div>
        <h1>¡Hola, <?= e($nombreAlumno) ?>! Tu postulación fue registrada</h1>
        <p>
          Recibimos correctamente tu formulario y respuestas de autopercepción. Estamos en la etapa de inscripción de toda la cohorte del instituto. Cuando todos los compañeros y compañeras terminen de completar sus fichas, la coordinación evaluará cada perfil para asignarles estratégicamente el emprendimiento turístico que acompañarán durante las 7 semanas.
        </p>
      </div>

      <!-- Cuenta Regresiva -->
      <div class="countdown-card">
        <div class="countdown-title">
          <span>⏳ Cierre definitivo de postulaciones</span>
        </div>
        <div class="countdown-sub">
          El plazo máximo para registrarse es este <strong>viernes 18 de septiembre a las 21:00 hs</strong>. Luego de ese horario se cierra el padrón y no podremos vincular al acompañamiento a quienes no hayan completado el formulario.
        </div>

        <div class="countdown-grid" id="countdownGrid">
          <div class="countdown-box">
            <span class="countdown-num" id="cd-dias">00</span>
            <span class="countdown-lbl">Días</span>
          </div>
          <div class="countdown-box">
            <span class="countdown-num" id="cd-horas">00</span>
            <span class="countdown-lbl">Horas</span>
          </div>
          <div class="countdown-box">
            <span class="countdown-num" id="cd-min">00</span>
            <span class="countdown-lbl">Minutos</span>
          </div>
          <div class="countdown-box">
            <span class="countdown-num" id="cd-seg">00</span>
            <span class="countdown-lbl">Segundos</span>
          </div>
        </div>
        <div id="countdownFin" style="display:none;font-weight:700;color:#166534;font-size:15px;margin-top:10px;">
          ✓ ¡Plazo de postulación completado! La coordinación está definiendo las asignaciones estratégicas.
        </div>
      </div>

      <!-- Explicativo Esquel LAB y Rol -->
      <div class="grid-info-espera">
        <div class="card-info-espera">
          <h3>🚀 ¿Qué es Esquel LAB?</h3>
          <p>
            Es el programa oficial de aceleración turística impulsado por la <strong>Subsecretaría de Turismo, Deporte y Cultura de la Municipalidad de Esquel</strong>.
          </p>
          <p>
            Acompaña a <strong>18 emprendimientos turísticos locales</strong> de alto valor diferenciador (agroturismo, experiencias de montaña, artesanías, gastronomía con identidad y operadores receptivos) para potenciar su modelo de negocio y posicionamiento en el destino.
          </p>
        </div>

        <div class="card-info-espera">
          <h3>🤝 ¿Cómo colaborarás vos?</h3>
          <p>
            Cada estudiante del ISET 815 tendrá una <strong>asignación 1 a 1</strong> con un emprendimiento específico, acreditando aproximadamente <strong>60 horas de práctica profesionalizante</strong> en territorio real.
          </p>
          <p>
            No harás tareas administrativas ficticias ni resúmenes escolares: trabajarás codo a codo con los <strong>consultores seniors y juniors</strong> en reuniones de trabajo reales y directas con el emprendedor.
          </p>
        </div>
      </div>

      <!-- Cómo funciona la dinámica de consignas -->
      <div class="pasos-consignas">
        <h4>📋 Los tres momentos de tu aporte en cada reunión:</h4>
        <div class="pasos-lista">
          <div class="paso-item">
            <span class="paso-pill">1. ANTES</span>
            <div>
              <strong>Insumo previo de preparación:</strong> Investigás precios de mercado, normativa que aplica, proveedores de la zona y competidores para que el consultor senior entre informado a la reunión.
            </div>
          </div>
          <div class="paso-item">
            <span class="paso-pill">2. DURANTE</span>
            <div>
              <strong>Presencia viva y registro fiel:</strong> Participás en la mesa de consultoría capturando frases textuales del titular, números clave del negocio y oportunidades que surjan en la conversación.
            </div>
          </div>
          <div class="paso-item">
            <span class="paso-pill">3. DESPUÉS</span>
            <div>
              <strong>Entregable concreto:</strong> Desarrollás una pieza tangible que haga avanzar el proyecto: una tabla de costos, una ficha de venta o una propuesta de itinerario.
            </div>
          </div>
        </div>
      </div>

      <!-- Próximos Pasos -->
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;margin-top:22px;display:flex;align-items:center;gap:16px;">
        <div style="font-size:36px;flex-shrink:0;">🔔</div>
        <div style="font-size:14px;color:#334155;line-height:1.5;">
          <strong>¿Qué tenés que hacer ahora?</strong><br>
          Nada más por el momento. Una vez cumplido el plazo del viernes a las 21:00 hs, la coordinación te notificará tu emprendimiento asignado y se activará en esta misma pantalla tu caso con toda su información, fechas de reuniones y consignas de trabajo.
        </div>
      </div>
    </div>

    <script>
    // Countdown hacia el viernes 18 de septiembre de 2026 a las 21:00 hs (ART / UTC-3)
    (function() {
      const deadline = new Date('2026-09-18T21:00:00-03:00').getTime();

      function actualizarReloj() {
        const ahora = new Date().getTime();
        const resto = deadline - ahora;

        if (resto <= 0) {
          const grid = document.getElementById('countdownGrid');
          const fin = document.getElementById('countdownFin');
          if (grid) grid.style.display = 'none';
          if (fin) fin.style.display = 'block';
          return;
        }

        const dias = Math.floor(resto / (1000 * 60 * 60 * 24));
        const horas = Math.floor((resto % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((resto % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((resto % (1000 * 60)) / 1000);

        const pad = (n) => String(n).padStart(2, '0');

        const elDias = document.getElementById('cd-dias');
        const elHoras = document.getElementById('cd-horas');
        const elMin = document.getElementById('cd-min');
        const elSeg = document.getElementById('cd-seg');

        if (elDias) elDias.textContent = pad(dias);
        if (elHoras) elHoras.textContent = pad(horas);
        if (elMin) elMin.textContent = pad(minutos);
        if (elSeg) elSeg.textContent = pad(segundos);
      }

      actualizarReloj();
      setInterval(actualizarReloj, 1000);
    })();
    </script>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$estudianteId = $ficha['consultor_id'];
$horas = horas_estudiante($pdo, $estudianteId);

// El proyecto completo, sin los datos de contacto del titular.
$proy = $pdo->prepare('SELECT * FROM lab_proyectos WHERE id = ?');
$proy->execute([$ficha['proyecto_id']]);
$proyecto = $proy->fetch() ?: [];

// Sus reuniones, con quién más va a estar
$reu = $pdo->prepare(
    "SELECT r.*, (
        SELECT GROUP_CONCAT(c2.nombre, ' · ')
          FROM lab_reunion_asistentes a2
          JOIN lab_consultores c2 ON c2.id = a2.consultor_id
         WHERE a2.reunion_id = r.id AND c2.tipo <> 'estudiante'
     ) AS equipo
       FROM lab_reuniones r
       JOIN lab_reunion_asistentes a ON a.reunion_id = r.id AND a.consultor_id = ?
      ORDER BY r.fecha, r.hora_inicio"
);
$reu->execute([$estudianteId]);
$reuniones = $reu->fetchAll();

// Sus consignas
$tar = $pdo->prepare('SELECT * FROM lab_tareas_estudiante WHERE estudiante_id = ? ORDER BY vence_at');
$tar->execute([$estudianteId]);
$tareas = $tar->fetchAll();

$hoy = date('Y-m-d H:i');
$pendientes = array_values(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente'));
$entregadas = array_values(array_filter($tareas, fn($t) => $t['estado'] !== 'pendiente'));
$vencidas = array_values(array_filter($pendientes, fn($t) => $t['vence_at'] !== '' && $t['vence_at'] < $hoy));

$proximaReunion = null;
foreach ($reuniones as $r) {
    if ($r['fecha'] >= date('Y-m-d')) { $proximaReunion = $r; break; }
}

/** Lista JSON guardada en el proyecto, tolerante a basura. */
function lista_json(?string $raw): array
{
    $x = json_decode((string) $raw, true);
    return is_array($x) ? $x : [];
}

require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Mi emprendimiento</h1>
  <span class="admin-count"><?= e($ficha['nombre']) ?> · <?= e($ficha['instituto']) ?></span>
</div>

<div class="admin-content est-vista">

  <!-- El caso -->
  <section class="panel est-caso">
    <div class="est-caso-head">
      <div>
        <span class="est-eyebrow">Tu emprendimiento para toda la cohorte</span>
        <h2><?= e($ficha['proyecto_nombre'] ?? '—') ?></h2>
        <p class="est-sub"><?= e($proyecto['titular'] ?? '') ?> · Línea <?= e($proyecto['linea'] ?? '—') ?> · Célula <?= (int) ($proyecto['celula'] ?? 0) ?></p>
      </div>
      <div class="est-horas" title="Presupuesto del convenio: <?= (int) $horas['presupuesto'] ?> horas">
        <span class="est-horas-n"><?= number_format($horas['usadas'], 1, ',', '') ?> <small>de <?= (int) $horas['presupuesto'] ?> h</small></span>
        <div class="est-barra"><span style="width:<?= (int) $horas['porcentaje'] ?>%"></span></div>
        <span class="est-horas-d"><?= $horas['cantidad_reuniones'] ?> reuniones · <?= number_format($horas['tareas'], 1, ',', '') ?> h de trabajo propio</span>
      </div>
    </div>

    <?php if ($proximaReunion): ?>
      <div class="est-proxima">
        <span class="k">Próxima reunión</span>
        <strong><?= e(fecha_larga($proximaReunion['fecha'])) ?><?= $proximaReunion['hora_inicio'] ? ' · ' . e($proximaReunion['hora_inicio']) : '' ?></strong>
        <span><?= e($proximaReunion['titulo']) ?><?= $proximaReunion['equipo'] ? ' · con ' . e($proximaReunion['equipo']) : '' ?></span>
      </div>
    <?php endif; ?>
  </section>

  <!-- Lo que hay que hacer -->
  <section>
    <h2 class="est-h2">Lo que tenés que hacer
      <?php if ($vencidas): ?><span class="est-alerta"><?= count($vencidas) ?> vencida<?= count($vencidas) === 1 ? '' : 's' ?></span><?php endif; ?>
    </h2>

    <?php if (!$pendientes): ?>
      <div class="empty-state">No tenés nada pendiente. Cuando se acerque la próxima reunión te va a aparecer acá.</div>
    <?php endif; ?>

    <?php foreach ($pendientes as $t):
      $m = MOMENTOS_ESTUDIANTE[$t['momento']] ?? ['label' => $t['momento']];
      $vencida = $t['vence_at'] !== '' && $t['vence_at'] < $hoy;
      $reunion = null;
      foreach ($reuniones as $r) { if ($r['id'] === $t['reunion_id']) { $reunion = $r; break; } }
    ?>
      <article class="est-tarea<?= $vencida ? ' es-vencida' : '' ?>" data-tarea="<?= (int) $t['id'] ?>">
        <header class="est-tarea-head">
          <div>
            <span class="est-momento est-m-<?= e($t['momento']) ?>"><?= e($m['label']) ?></span>
            <h3><?= e($t['titulo']) ?></h3>
            <?php if ($reunion): ?>
              <p class="est-sub"><?= e($reunion['titulo']) ?> · <?= e(fecha_larga($reunion['fecha'])) ?></p>
            <?php endif; ?>
          </div>
          <span class="est-vence<?= $vencida ? ' es-vencida' : '' ?>">
            <?= $t['vence_at'] === '' ? 'Sin fecha' : ($vencida ? 'Venció el ' : 'Hasta el ') . e(fecha_corta($t['vence_at'], true)) ?>
          </span>
        </header>

        <p class="est-consigna"><?= e($t['consigna']) ?></p>

        <form class="est-form" data-id="<?= (int) $t['id'] ?>">
          <?php if ($t['momento'] === 'durante'): ?>
            <?php foreach (ESPECIFICOS_REUNION as $clave => $def): ?>
              <div class="field">
                <label class="lbl" for="e<?= (int) $t['id'] ?>-<?= e($clave) ?>">
                  <?= e($def['label']) ?>
                  <span class="est-min"><?= $def['minimo'] ?> como mínimo, uno por línea</span>
                </label>
                <p class="hint"><?= e($def['ayuda']) ?></p>
                <textarea id="e<?= (int) $t['id'] ?>-<?= e($clave) ?>" name="esp[<?= e($clave) ?>]" rows="3" data-telemetria></textarea>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="field">
              <label class="lbl" for="e<?= (int) $t['id'] ?>-entrega">Tu entrega</label>
              <textarea id="e<?= (int) $t['id'] ?>-entrega" name="entrega" rows="10" data-telemetria
                        placeholder="Escribí acá. Podés guardar borradores todas las veces que quieras."></textarea>
            </div>
          <?php endif; ?>

          <div class="est-acciones">
            <span class="est-contador" data-contador data-min="<?= (int) $t['min_caracteres'] ?>"></span>
            <button type="button" class="btn btn-secondary btn-sm" data-accion="borrador">Guardar borrador</button>
            <button type="submit" class="btn btn-primary btn-sm">Entregar</button>
            <span class="drawer-msg" data-msg></span>
          </div>
        </form>
      </article>
    <?php endforeach; ?>
  </section>

  <!-- Contexto del caso -->
  <section class="panel est-contexto">
    <h2 class="est-h2">El caso, para que puedas trabajar sin haber estado antes</h2>
    <p class="est-sub" style="margin-bottom:18px">Esto es lo que el equipo relevó antes de que entraras. Leelo antes de cada reunión: la consigna de sala te pide justamente lo que <em>contradice</em> esto.</p>

    <?php
      $bloques = [
        'diagnostico' => 'Diagnóstico',
        'trabas'      => 'Trabas detectadas',
        'ejes'        => 'Ejes de trabajo',
        'entregables' => 'Entregables comprometidos',
      ];
      foreach ($bloques as $campo => $label):
        $items = lista_json($proyecto[$campo] ?? '');
        if (!$items) continue;
    ?>
      <div class="est-bloque">
        <h3><?= e($label) ?></h3>
        <ul>
          <?php foreach ($items as $i): ?>
            <li><?= e(is_array($i) ? ($i['texto'] ?? json_encode($i, JSON_UNESCAPED_UNICODE)) : (string) $i) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($proyecto['minuta_cero'])): ?>
      <div class="est-bloque">
        <h3>Minuta de arranque</h3>
        <div class="est-minuta"><?= e($proyecto['minuta_cero']) ?></div>
      </div>
    <?php endif; ?>
  </section>

  <!-- Historial -->
  <?php if ($entregadas): ?>
  <section>
    <h2 class="est-h2">Lo que ya entregaste</h2>
    <div class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead><tr><th>Consigna</th><th>Entregada</th><th class="nowrap">Caracteres</th><th>Valoración del consultor</th></tr></thead>
          <tbody>
            <?php foreach (array_reverse($entregadas) as $t): ?>
              <tr>
                <td><strong><?= e($t['titulo']) ?></strong><div class="sub"><?= e(MOMENTOS_ESTUDIANTE[$t['momento']]['label'] ?? '') ?></div></td>
                <td class="nowrap sub"><?= e(fecha_corta($t['entregado_at'], true)) ?></td>
                <td class="num"><?= (int) $t['caracteres'] ?></td>
                <td>
                  <?php if ($t['valoracion'] === ''): ?>
                    <span class="sub">Todavía sin revisar</span>
                  <?php else: $v = VALORACIONES_APORTE[$t['valoracion']] ?? ['label' => $t['valoracion']]; ?>
                    <span class="est-val est-v-<?= e($t['valoracion']) ?>"><?= e($v['label']) ?></span>
                    <?php if ($t['valoracion_nota'] !== ''): ?><div class="sub"><?= e($t['valoracion_nota']) ?></div><?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div>

<script>
  window.ESQUEL_TAREAS_API = 'estudiantes_api.php';
  window.ESQUEL_CSRF = <?= json_encode(csrf_token()) ?>;
</script>
<script src="../<?= asset('assets/js/estudiante.js') ?>"></script>
<?php require __DIR__ . '/_footer.php'; ?>
