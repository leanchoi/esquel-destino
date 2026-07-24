<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Sala de prensa · Esquel LAB';
$pageDescription = 'Kit de prensa de Esquel LAB: qué es el programa, cómo funciona la evaluación, datos clave, notas listas para publicar y logos para descargar.';
$activeNav = 'prensa';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><span class="dot"></span> Sala de prensa</span>
    <h1>Todo lo que necesitás para escribir sobre Esquel LAB</h1>
    <p class="lede">
      El resumen del programa, los números, cómo se eligen los participantes y notas listas para copiar y adaptar.
      Si necesitás algo que no está acá, escribinos.
    </p>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- ---------------- descargas ---------------- -->
    <div class="mk-grid">
      <div class="mk-card">
        <h3>Logos oficiales</h3>
        <p>Isologotipos de Esquel LAB, Acelera y Raíz en PNG con fondo transparente.</p>
        <div class="dl">
          <a href="assets/images/logo-esquel-lab.png" download class="btn btn-secondary btn-sm">Esquel LAB (color)</a>
          <a href="assets/images/logo-esquel-lab-blanco.png" download class="btn btn-secondary btn-sm">Esquel LAB (blanco)</a>
          <a href="assets/images/logo-esquel-acelera.png" download class="btn btn-secondary btn-sm">Esquel Acelera</a>
          <a href="assets/images/logo-esquel-raiz.png" download class="btn btn-secondary btn-sm">Raíz</a>
        </div>
      </div>

      <div class="mk-card">
        <h3>Datos del programa</h3>
        <p>Fechas, cupos, líneas de trabajo y composición del Cuadro Técnico, todo en esta misma página para copiar.</p>
        <div class="dl">
          <a href="#numeros" class="btn btn-secondary btn-sm">Ver los números</a>
          <a href="#evaluacion" class="btn btn-secondary btn-sm">Cómo se evalúa</a>
        </div>
      </div>

      <div class="mk-card">
        <h3>Contacto de prensa</h3>
        <p>Para coordinar entrevistas con el municipio o con las cámaras que integran el Cuadro Técnico.</p>
        <div class="dl">
          <a href="mailto:<?= e(EMAIL_PRENSA) ?>" class="btn btn-primary btn-sm">Escribir a prensa</a>
        </div>
      </div>
    </div>

    <!-- ---------------- boilerplate ---------------- -->
    <h2 style="font-size:28px">Qué es Esquel LAB, en 100 palabras</h2>
    <p style="color:var(--ink-2)">Párrafo de contexto listo para insertar al pie de una nota.</p>
    <div class="release">
      <div class="release-body" id="boilerplate">
        <p>Esquel LAB (Laboratorio de Destino Esquel) es un programa municipal gratuito de las Subsecretarías de Turismo y de Producción que acompaña a emprendedores urbanos y productores rurales a transformar servicios y saberes que ya existen en experiencias turísticas que se puedan vender. Se organiza en dos líneas: Esquel Acelera, para el ámbito urbano, y Raíz, para el rural. Cada edición dura ocho semanas de trabajo intensivo, con acompañamiento individual en el lugar de trabajo de cada participante y talleres grupales, y cierra con la presentación pública de las experiencias ante agencias receptivas y prensa. La selección está a cargo de un Cuadro Técnico mixto integrado por el municipio y tres cámaras del sector privado.</p>
      </div>
      <button class="copy-btn" data-copy="#boilerplate">Copiar texto</button>
    </div>

    <!-- ---------------- números ---------------- -->
    <h2 id="numeros" style="font-size:28px;margin-top:56px">Los números</h2>
    <table class="facts-table">
      <tr><td>Costo para el participante</td><td>Gratuito</td></tr>
      <tr><td>Líneas</td><td>2 — Esquel Acelera (urbano) y Raíz (rural)</td></tr>
      <tr><td>Postulaciones</td><td><?= e(fecha_larga(FECHA_APERTURA)) ?> al <?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026</td></tr>
      <tr><td>Trabajo en territorio</td><td><?= e(fecha_larga(FECHA_INICIO)) ?> al <?= e(fecha_larga(FECHA_FIN)) ?> de 2026 (8 semanas)</td></tr>
      <tr><td>Proyectos por edición</td><td>13 a 18 en total — 8 a 10 urbanos y 5 a 8 rurales</td></tr>
      <tr><td>Dedicación exigida</td><td>Mínimo 12 horas semanales por proyecto</td></tr>
      <tr><td>Requisitos de entrada</td><td>No se exige monotributo ni habilitación previa para postularse</td></tr>
      <tr><td>Gobernanza</td><td>Municipio (Turismo y Producción) + CAMOCH + Cámara de Prestadores Turísticos + FEHGRA Filial Esquel</td></tr>
    </table>

    <!-- ---------------- evaluación ---------------- -->
    <h2 id="evaluacion" style="font-size:28px;margin-top:56px">Cómo se eligen los participantes</h2>
    <p style="color:var(--ink-2);max-width:70ch">
      La selección no la hace sólo el municipio. Un <strong>Cuadro Técnico</strong> integrado por las Subsecretarías de
      Turismo y de Producción junto a <strong>CAMOCH</strong> (Cámara de Comercio, Industria, Producción y Turismo del Oeste
      de Chubut), la <strong>Cámara de Prestadores Turísticos de Esquel</strong> y <strong>FEHGRA Filial Esquel</strong>
      acuerda los criterios antes de abrir la convocatoria y participa de la evaluación.
    </p>
    <p style="color:var(--ink-2);max-width:70ch">
      Se pondera con una matriz de cinco dimensiones. El objetivo explícito de acordarla de antemano es que el proceso
      no dependa de un solo actor y no beneficie únicamente a los proyectos ya consolidados, dándole lugar real a los
      que recién empiezan.
    </p>

    <div class="ba-wrap" style="margin-top:22px">
      <table class="ba-table">
        <thead><tr><th>Criterio</th><th>Qué mira</th><th>Peso</th></tr></thead>
        <tbody>
          <?php foreach (CRITERIOS as $def): ?>
            <tr>
              <td class="ba-key"><?= e($def['label']) ?></td>
              <td class="ba-before" style="color:var(--ink-2)"><?= e($def['ayuda']) ?></td>
              <td class="ba-after"><?= e(number_format($def['peso'], 1)) ?>×</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="font-size:14.5px;color:var(--ink-3);margin-top:12px">
      El perfil y la motivación del postulante pesan más que el resto por decisión del programa: una propuesta simple
      con alguien decidido a sostenerla llega más lejos que una propuesta redonda sin nadie que la empuje.
    </p>

    <!-- ---------------- comparativa ---------------- -->
    <h2 style="font-size:28px;margin-top:56px">Acelera y Raíz, comparadas</h2>
    <div class="ba-wrap">
      <table class="ba-table">
        <thead><tr><th>&nbsp;</th><th>Esquel Acelera</th><th>Raíz</th></tr></thead>
        <tbody>
          <tr><td class="ba-key">Ámbito</td><td class="ba-before" style="color:var(--ink-2)">Urbano</td><td class="ba-after" style="font-weight:400">Rural</td></tr>
          <tr><td class="ba-key">A quién apunta</td><td class="ba-before" style="color:var(--ink-2)">Emprendedores, organizaciones y empresas de servicios turísticos de la ciudad</td><td class="ba-after" style="font-weight:400">Productores y prestadores del campo</td></tr>
          <tr><td class="ba-key">Cupo</td><td class="ba-before" style="color:var(--ink-2)">8 a 10 proyectos</td><td class="ba-after" style="font-weight:400">5 a 8 proyectos</td></tr>
          <tr><td class="ba-key">Producto físico asociado</td><td class="ba-before" style="color:var(--ink-2)">Se evalúa caso por caso</td><td class="ba-after" style="font-weight:400">Vínculo directo: lana, dulces, fruta fina, bebidas artesanales</td></tr>
          <tr><td class="ba-key">Sello vinculado</td><td class="ba-before" style="color:var(--ink-2)">“Hecho en Esquel”</td><td class="ba-after" style="font-weight:400">“Hecho en Esquel” y nexo con “Origen Chubut”</td></tr>
        </tbody>
      </table>
    </div>

    <!-- ---------------- notas ---------------- -->
    <h2 style="font-size:28px;margin-top:56px">Notas listas para publicar</h2>
    <p style="color:var(--ink-2)">Redactadas para adaptar y publicar. Copialas, cortalas o reescribilas como te sirva.</p>

    <div class="release">
      <span class="tag">Eje: transparencia y gobernanza</span>
      <h3>El sector privado de Esquel asume un rol activo en la selección de emprendimientos turísticos</h3>
      <div class="release-body" id="nota1">
        <p>ESQUEL, CHUBUT · Con el lanzamiento de la primera cohorte del Laboratorio de Destino Esquel, el municipio y el sector privado local pusieron en marcha un esquema de gobernanza mixta para elegir a los participantes del programa. Las instituciones que representan al comercio, la hotelería y la prestación turística —CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA Filial Esquel— no sólo acompañan la iniciativa: integran el Cuadro Técnico que pondera cada postulación.</p>
        <p>Los criterios se acordaron antes de abrir la convocatoria y se publicaron junto con ella. Se evalúan cinco dimensiones: el perfil y la motivación de quien se postula, la diferenciación de la propuesta, su impacto en la oferta turística del destino, la viabilidad operativa y el potencial de asociar un producto físico de identidad local. El peso mayor recae sobre la motivación y el compromiso, una definición deliberada del programa.</p>
        <p>El objetivo declarado del esquema es evitar que el acompañamiento beneficie únicamente a los emprendimientos ya consolidados y garantizar lugar a quienes recién empiezan. El programa es gratuito y no exige monotributo ni habilitación previa para postularse.</p>
      </div>
      <button class="copy-btn" data-copy="#nota1">Copiar nota</button>
    </div>

    <div class="release green">
      <span class="tag">Eje: producción local</span>
      <h3>Esquel integra el turismo con sus productores a través de la “Economía de los Recuerdos”</h3>
      <div class="release-body" id="nota2">
        <p>ESQUEL, CHUBUT · Las Subsecretarías de Turismo y de Producción de Esquel incorporaron al Laboratorio de Destino el enfoque de la “Economía de los Recuerdos”, que parte de una premisa simple: el recuerdo es el bien más valioso que produce un destino turístico, y un objeto tangible lo mantiene vivo mucho después del viaje.</p>
        <p>En la práctica, esto significa que cada experiencia desarrollada en el programa evalúa la posibilidad de asociar un producto físico con identidad territorial: un dulce, una madeja de lana, una pieza de cerámica, una conserva. Cuando la propuesta lo permite, el acompañamiento incluye trabajar el envase, la etiqueta y el relato que lo acompaña, y evaluar su vínculo con el sello municipal “Hecho en Esquel”.</p>
        <p>El impacto buscado es doble: elevar el gasto promedio del visitante y abrir un canal de venta directa para artesanos y productores locales que hoy comercializan sin conexión con el circuito turístico. El programa trabaja con dos líneas —Esquel Acelera en el ámbito urbano y Raíz en el rural— y cierra el 2 de octubre con la presentación pública de las experiencias ante agencias receptivas.</p>
      </div>
      <button class="copy-btn" data-copy="#nota2">Copiar nota</button>
    </div>

    <div class="release">
      <span class="tag">Nota breve</span>
      <h3>Un programa municipal gratuito y pensado como proceso continuo</h3>
      <div class="release-body" id="nota3">
        <p>El Laboratorio de Destino Esquel prevé abrir cohortes sucesivas a lo largo del año. El cupo acotado de esta primera etapa —8 a 10 proyectos urbanos y 5 a 8 rurales— responde a que el acompañamiento es individual y en territorio: el equipo trabaja en el lugar de cada participante durante ocho semanas, con una dedicación exigida de al menos 12 horas semanales de su parte. Las postulaciones que no resulten seleccionadas quedan registradas para las próximas convocatorias y para el diseño de programas complementarios.</p>
      </div>
      <button class="copy-btn" data-copy="#nota3">Copiar nota</button>
    </div>

    <!-- ---------------- preguntas ---------------- -->
    <h2 style="font-size:28px;margin-top:56px">Preguntas que suelen hacernos</h2>
    <div class="faq">
      <details class="faq-item">
        <summary>¿Esto es sólo para quienes ya tienen capital para invertir?</summary>
        <div class="faq-body">
          <p>No. Esquel LAB no es un régimen de inversión: es acompañamiento técnico gratuito para emprendedores y productores que ya están operando con lo que tienen y necesitan estructura comercial —precio, canal de venta, materiales— para poder vender.</p>
          <p>El municipio tiene además un Régimen de Promoción de Inversiones Turísticas, orientado a proyectos de mayor escala. Son instrumentos distintos y complementarios: uno moviliza capital, el otro pone equipo técnico al servicio de quien ya está trabajando.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary>¿Hace falta estar formalizado para participar?</summary>
        <div class="faq-body">
          <p>No para postularse. Si la propuesta necesita alguna habilitación para poder comercializarse, ordenar eso forma parte del acompañamiento. La profesionalización es el objetivo del programa, no su requisito de entrada.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary>¿Qué pasa con quienes no queden seleccionados?</summary>
        <div class="faq-body">
          <p>Sus postulaciones quedan registradas. Esta es la primera cohorte de un proceso concebido como continuo: de sus resultados van a salir las próximas convocatorias y los programas complementarios.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary>¿Hay antecedentes de este tipo de programa en la región?</summary>
        <div class="faq-body">
          <p>Esta es la primera cohorte de Esquel LAB. La metodología tiene un precedente regional cercano: un programa comparable trabajó con este enfoque en El Bolsón, priorizando a los vecinos frente a las grandes inversiones externas y poniendo en valor saberes locales que no estaban integrados a la oferta turística.</p>
        </div>
      </details>
    </div>

    <div class="callout" style="margin-top:48px">
      <span class="lbl">Contacto</span>
      <p>Subsecretaría de Turismo de Esquel · <a href="mailto:<?= e(EMAIL_PRENSA) ?>"><?= e(EMAIL_PRENSA) ?></a></p>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
