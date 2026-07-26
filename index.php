<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Esquel LAB — Convocatoria 2026 para nuevas experiencias turísticas';
$pageDescription = 'Programa municipal gratuito. Ocho semanas de acompañamiento técnico para convertir tu oficio, tu campo o tu servicio en una experiencia turística lista para recibir visitantes. Se seleccionan hasta 18 proyectos. Postulaciones hasta el 9 de agosto de 2026.';
$activeNav = 'home';
$abierta = convocatoria_abierta();
$dias = dias_para_cierre();
require __DIR__ . '/includes/header.php';
?>

<!-- Barra de plazo -->
<div class="deadline-bar <?= $abierta ? '' : 'is-closed' ?>">
  <div class="container">
    <?php if ($abierta): ?>
      <span class="pill">Convocatoria abierta</span>
      <span>Las postulaciones cierran el <strong><?= e(fecha_larga(FECHA_CIERRE)) ?></strong><?= $dias > 0 ? ' · quedan ' . $dias . ' día' . ($dias === 1 ? '' : 's') : '' ?></span>
    <?php else: ?>
      <span class="pill">Convocatoria cerrada</span>
      <span>La postulación a la primera cohorte cerró el <strong><?= e(fecha_larga(FECHA_CIERRE)) ?></strong>. Va a haber más.</span>
    <?php endif; ?>
  </div>
</div>

<!-- ============================ HERO ============================ -->
<section class="hero has-topo">
  <div class="container hero-grid">
    <div class="hero-copy">
<?php /* "Convocatoria abierta" ya lo dice la barra de plazo, acá arriba. */ ?>
      <span class="eyebrow"><span class="dot"></span> Primera cohorte · 2026</span>
      <h1>Buscamos <em>18 proyectos</em> para las próximas experiencias turísticas de Esquel.</h1>
      <p class="hero-lede">
        Esquel LAB es un programa municipal gratuito. Durante ocho semanas un equipo trabaja
        con vos, en tu lugar, para que lo que ya hacés —tu oficio, tu campo, tu servicio—
        quede listo para recibir visitantes y para que una agencia pueda ofrecerlo.
      </p>

      <?php /* La fecha de cierre no va acá: ya está en la barra de plazo, justo arriba. */ ?>
      <dl class="hero-datos">
        <div><dt>Costo</dt><dd class="is-free">Gratuito</dd></div>
        <div><dt>Cupos</dt><dd>13 a 18 proyectos</dd></div>
        <div><dt>Dedicación</dt><dd>12 hs por semana</dd></div>
      </dl>

      <div class="hero-cta">
        <a href="inscribirse.php" class="btn btn-primary btn-lg">Postularme</a>
        <a href="#para-vos" class="btn btn-secondary btn-lg">Ver si es para mí</a>
      </div>
      <p class="hero-note">
        Todas las postulaciones se evalúan con una matriz de criterios acordada con las cámaras
        del sector: no es por orden de llegada. Para postularte no hace falta monotributo ni habilitación.
      </p>
    </div>

    <div class="hero-media">
      <figure class="hero-figure">
        <div class="hero-frame">
          <img src="assets/images/fotos/hero-esquel.jpg" alt="Dos personas de espaldas mirando el valle de Esquel y la cordillera al atardecer" data-parallax>
        </div>
        <figcaption class="hero-caption">Esquel, puerta del Parque Nacional Los Alerces.</figcaption>
      </figure>

      <div class="hero-org">
        <span class="hero-org-lbl">Un programa de</span>
        <div class="hero-org-logos">
          <img src="assets/images/logo-esquel-lab-horizontal.png" alt="Esquel LAB">
          <img src="assets/images/logo-municipio-esquel.png" alt="Municipio de Esquel" class="is-muni">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ ¿ES PARA VOS? ============================ -->
<section class="section section-white" id="para-vos">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">A quién buscamos</span>
      <h2>No hace falta que seas “turístico”. Hace falta que tengas algo para mostrar.</h2>
      <p>Muchos de los proyectos que buscamos hoy ni siquiera se presentan como turismo: son oficios, campos y comercios que un visitante pagaría por conocer. Si te reconocés en alguno de estos seis casos, la convocatoria es para vos.</p>
    </div>

    <div class="profile-grid">
      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-gastronomia.jpg" alt="Persona sirviendo té y torta casera en una mesa de madera con vista a la montaña" loading="lazy"></figure>
        <div class="body">
          <h3>Tenés un negocio que funciona pero no le vende a turistas</h3>
          <p>Una casa de té, una panadería, un taller. La gente pasa, compra y se va. Nunca lo armaste como experiencia.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-guia.jpg" alt="Guía señalando un pico nevado a dos visitantes en un sendero de trekking" loading="lazy"></figure>
        <div class="body">
          <h3>Sos guía o prestador y vendés solo por WhatsApp</h3>
          <p>Tenés el servicio. No tenés precio para agencias, ni forma de que te reserven sin escribirte.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-chacra.jpg" alt="Persona cosechando frambuesas entre hileras de cultivo con un invernadero al fondo" loading="lazy"></figure>
        <div class="body">
          <h3>Tenés campo y no sabés si “eso” se puede visitar</h3>
          <p>La esquila, la cosecha, el proceso del dulce. Para vos es el trabajo de todos los días. Para un visitante es algo que nunca vio.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-lana.jpg" alt="Persona tejiendo en un telar con madejas de lana teñida colgando" loading="lazy"></figure>
        <div class="body">
          <h3>Hacés algo con las manos y lo vendés suelto</h3>
          <p>Lana, cerámica, conservas. Sin relato, sin packaging y sin conexión con quien visita Esquel.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-reabrir.jpg" alt="Persona abriendo la persiana de su local, el Almacén de Montaña, con la cordillera de fondo" loading="lazy"></figure>
        <div class="body">
          <h3>Tenés un emprendimiento turístico que se quedó</h3>
          <p>Antes funcionaba y hoy no. Necesitás rearmar el producto, no empezar de cero.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><img src="assets/images/fotos/perfil-idea.jpg" alt="Cuaderno con anotaciones, lápiz y mate sobre una mesa de madera, con la cordillera de fondo" loading="lazy"></figure>
        <div class="body">
          <h3>Tenés una idea y algo con qué arrancar</h3>
          <p>No hace falta que ya esté funcionando. Hace falta que puedas dedicarle tiempo real durante ocho semanas.</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ============================ QUÉ TE LLEVÁS ============================ -->
<section class="section section-alt" id="que-te-llevas">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Resultados concretos</span>
      <h2>El 2 de octubre te vas con esto en la mano</h2>
      <p>No con un diagnóstico ni un PDF de recomendaciones. Con la experiencia armada y funcionando.</p>
    </div>

    <div class="ba-wrap">
      <table class="ba-table">
        <thead>
          <tr><th>&nbsp;</th><th>Antes</th><th>Después</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="ba-key">Precio</td>
            <td class="ba-before">“Y… depende, hacemé una oferta.”</td>
            <td class="ba-after">Precio al público y precio neto para agencias, con tus costos calculados.</td>
          </tr>
          <tr>
            <td class="ba-key">Reservas</td>
            <td class="ba-before">WhatsApp, cuando te acordás de contestar.</td>
            <td class="ba-after">Un canal digital donde te reservan y te llega el aviso.</td>
          </tr>
          <tr>
            <td class="ba-key">Cómo lo contás</td>
            <td class="ba-before">Improvisado, distinto cada vez.</td>
            <td class="ba-after">Un guión de la experiencia: qué pasa, en qué orden y cuánto dura.</td>
          </tr>
          <tr>
            <td class="ba-key">Fotos</td>
            <td class="ba-before">Las del celular, cuando salieron bien.</td>
            <td class="ba-after">Registro fotográfico hecho para promoción.</td>
          </tr>
          <tr>
            <td class="ba-key">Quién lo vende</td>
            <td class="ba-before">Vos, y nadie más.</td>
            <td class="ba-after">Tu ficha en manos de las agencias receptivas de Esquel.</td>
          </tr>
          <tr>
            <td class="ba-key">Qué se lleva el visitante</td>
            <td class="ba-before">El recuerdo, y nada más.</td>
            <td class="ba-after">El recuerdo y un producto físico tuyo, con identidad de Esquel.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="hours-split" style="margin-top:56px">
      <div>
        <h3 style="font-size:24px">La “Economía de los Recuerdos”, sin vueltas</h3>
        <p>Un visitante que se lleva algo tuyo a su casa te sigue recordando —y te sigue recomendando— mucho después del viaje. Por eso el programa trabaja, cuando se puede, en asociar a cada experiencia un producto físico con identidad local: un dulce, una madeja, una pieza.</p>
        <p>No es obligatorio para postularse, y no todas las propuestas lo van a tener. Cuando aplica, se trabaja también el envase, la etiqueta y el relato, y se evalúa el vínculo con el sello municipal <strong>“Hecho en Esquel”</strong>.</p>
      </div>
      <figure style="margin:0">
        <img src="assets/images/fotos/economia-recuerdos.jpg" alt="Frasco de dulce casero, botella de cerveza artesanal y madeja de lana teñida sobre una mesa de madera" style="border-radius:var(--r);border:1px solid var(--line)" loading="lazy">
      </figure>
    </div>
  </div>
</section>

<!-- ============================ LÍNEAS ============================ -->
<section class="section has-topo" id="lineas">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Dos líneas, un mismo método</span>
      <h2>Elegí la que es tuya</h2>
      <p>Comparten equipo, cronograma y forma de trabajo. Lo que cambia es el terreno. Los cupos se reparten entre las dos.</p>
    </div>

    <div class="lines-grid">
      <article class="line-card line-acelera">
        <div class="line-figure">
          <img src="assets/images/fotos/linea-acelera.jpg" alt="Calle comercial de Esquel con la cordillera de fondo" loading="lazy">
        </div>
        <div class="line-body">
          <img src="assets/images/logo-esquel-acelera.png" alt="Esquel Acelera" class="line-logo">
          <span class="line-badge">Urbano</span>
          <p class="line-for">Si tu proyecto está en la ciudad</p>
          <p>Gastronomía, casas de té, talleres artesanales, comercios con un saber propio, circuitos históricos, guías y actividades urbanas. También negocios que hoy no son turísticos pero podrían serlo.</p>
          <div class="line-meta">
            <div><div class="num">8 a 10</div><div class="lbl">Proyectos</div></div>
            <div><div class="num">8</div><div class="lbl">Semanas</div></div>
          </div>
          <a href="inscribirse.php?linea=Acelera" class="btn btn-primary">Postularme a Acelera</a>
        </div>
      </article>

      <article class="line-card line-raiz">
        <div class="line-figure">
          <img src="assets/images/fotos/linea-raiz.jpg" alt="Campo con ovejas, un perro de trabajo y un galpón, con la cordillera de fondo" loading="lazy">
        </div>
        <div class="line-body">
          <img src="assets/images/logo-esquel-raiz.png" alt="Raíz" class="line-logo">
          <span class="line-badge">Rural</span>
          <p class="line-for">Si tu proyecto está en el campo</p>
          <p>Chacras, estancias, crianceros, viñedos y microcervecerías, productores de lana, fruta fina y dulces regionales. Todo lo que tenga un proceso que se pueda mostrar y visitar.</p>
          <div class="line-meta">
            <div><div class="num">5 a 8</div><div class="lbl">Proyectos</div></div>
            <div><div class="num">8</div><div class="lbl">Semanas</div></div>
          </div>
          <a href="inscribirse.php?linea=Raiz" class="btn btn-green">Postularme a Raíz</a>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ============================ CÓMO ES EL TRABAJO ============================ -->
<section class="section section-dark ridge-top" id="como-es">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Cómo es el trabajo</span>
      <h2>12 horas por semana. Así se reparten.</h2>
      <p>La mayor parte del tiempo es trabajo sobre lo tuyo, no reuniones.</p>
    </div>

    <div class="hours-split">
      <div>
        <ul class="hours-list">
          <li>
            <span class="h">2 hs</span>
            <span class="t"><b>Taller grupal</b><span>Con el resto de la cohorte. Precio, relato, canales de venta.</span></span>
          </li>
          <li>
            <span class="h">1 a 2 hs</span>
            <span class="t"><b>Reunión individual</b><span>En tu lugar de trabajo, sobre tu proyecto puntual.</span></span>
          </li>
          <li>
            <span class="h">8 a 9 hs</span>
            <span class="t"><b>Trabajo tuyo</b><span>Costos, fotos, textos, probar la experiencia y corregirla.</span></span>
          </li>
        </ul>

        <div class="callout">
          <span class="lbl">Por qué lo pedimos por escrito</span>
          <p>Un cupo ocupado por alguien que no puede sostenerlo es un cupo que le sacamos a otro. Si hoy no podés garantizar ese tiempo, va a haber próximas cohortes.</p>
        </div>
      </div>

      <div class="steps-flow">
        <div class="step-row">
          <div class="step-num">1</div>
          <div>
            <h4>Te postulás</h4>
            <p>Completás el formulario. Te lleva entre 15 y 25 minutos y podés guardarlo a medio hacer.</p>
          </div>
        </div>
        <div class="step-row">
          <div class="step-num">2</div>
          <div>
            <h4>El Cuadro Técnico —municipio y cámaras del sector— evalúa tu postulación</h4>
            <p>No es por orden de llegada: se puntúa con una matriz de criterios acordada antes de abrir la convocatoria.</p>
          </div>
        </div>
        <div class="step-row">
          <div class="step-num">3</div>
          <div>
            <h4>Vamos a tu lugar</h4>
            <p>Primera visita de diagnóstico: qué tenés, qué falta y por dónde se empieza.</p>
          </div>
        </div>
        <div class="step-row">
          <div class="step-num">4</div>
          <div>
            <h4>Ocho semanas de trabajo conjunto</h4>
            <p>Talleres grupales y acompañamiento individual, en paralelo, sobre tu proyecto.</p>
          </div>
        </div>
        <div class="step-row">
          <div class="step-num">5</div>
          <div>
            <h4>Presentás lo que armaste</h4>
            <p>Ante las agencias receptivas de Esquel y la prensa local, con la experiencia terminada y lista para recibir reservas.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ PREGUNTAS ============================ -->
<section class="section section-white" id="preguntas">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Preguntas frecuentes</span>
      <h2>Lo que todos preguntan</h2>
    </div>

    <div class="faq">
      <details class="faq-item" open>
        <summary>¿Cuánto cuesta?</summary>
        <div class="faq-body">
          <p>Nada. El programa es <strong>gratuito</strong>. Está financiado por el municipio a través de las Subsecretarías de Turismo y de Producción.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Necesito monotributo, habilitación o estar inscripto en algún registro?</summary>
        <div class="faq-body">
          <p><strong>Para postularte, no.</strong> Podés participar aunque hoy no estés formalizado.</p>
          <p>Ahora bien: si tu propuesta necesita alguna habilitación para poder venderse en serio, ordenar eso es parte del camino, y el programa te acompaña a hacerlo. La profesionalización es el objetivo, no el requisito de entrada.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>No tengo un emprendimiento turístico. ¿Igual puedo?</summary>
        <div class="faq-body">
          <p>Sí, y es parte del objetivo. Varias de las propuestas que buscamos hoy no se dedican al turismo: son oficios, producciones o negocios locales que pueden convertirse en una experiencia.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Y si no quedo seleccionado?</summary>
        <div class="faq-body">
          <p>Tu postulación queda registrada. Esta es la primera cohorte de un proceso pensado como continuo: de lo que veamos acá van a salir las próximas convocatorias y los programas complementarios. Si tu propuesta no entra ahora, sabemos que existe.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Tengo que tener un local o un lugar físico?</summary>
        <div class="faq-body">
          <p>No necesariamente. Hay experiencias que ocurren en un recorrido, en el campo o en la casa de quien las ofrece.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Puedo postularme con más de un proyecto?</summary>
        <div class="faq-body">
          <p>Sí, pero cargá cada uno por separado. Tené en cuenta que el compromiso de 12 horas semanales es por proyecto.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Qué pasa si me seleccionan y después no puedo sostener las horas?</summary>
        <div class="faq-body">
          <p>Avisanos apenas lo sepas. Preferimos liberar el cupo a mitad de camino y que lo aproveche otra propuesta, antes que perderlo entero.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Hasta cuándo puedo postularme?</summary>
        <div class="faq-body">
          <p>Hasta el <strong><?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026</strong> inclusive. Es una fecha de cierre real: pasada esa fecha el formulario deja de recibir postulaciones.</p>
        </div>
      </details>
    </div>
  </div>
</section>

<!-- ============================ FECHAS ============================ -->
<section class="section section-alt" id="fechas">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Cronograma</span>
      <h2>Las fechas que importan</h2>
    </div>

    <div class="dates-grid">
      <div class="date-card <?= $abierta ? 'now' : '' ?>">
        <div class="d">23 jul — 9 ago</div>
        <h4>Postulación y evaluación</h4>
        <p>Recibimos formularios y el Cuadro Técnico los evalúa en paralelo.</p>
      </div>
      <div class="date-card">
        <div class="d">10 de agosto</div>
        <h4>Aviso y arranque</h4>
        <p>Avisamos a todos, hayan quedado o no, y empieza el trabajo en territorio.</p>
      </div>
      <div class="date-card">
        <div class="d">Agosto — septiembre</div>
        <h4>Ocho semanas de trabajo</h4>
        <p>Talleres grupales, visitas individuales y desarrollo de cada experiencia.</p>
      </div>
      <div class="date-card">
        <div class="d">2 de octubre</div>
        <h4>Presentación pública</h4>
        <p>Lanzamiento de las experiencias ante agencias receptivas y prensa.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================ GOBERNANZA ============================ -->
<section class="section section-sm section-line">
  <div class="container" style="text-align:center">
    <span class="eyebrow" style="justify-content:center">Quién decide</span>
    <h2 style="font-size:26px">La selección no la hace solo el municipio</h2>
    <div class="gov-logos">
      <span>CAMOCH</span>
      <span>Cámara de Prestadores Turísticos de Esquel</span>
      <span>FEHGRA Filial Esquel</span>
    </div>
    <p style="max-width:64ch;margin:0 auto;color:var(--ink-2);font-size:15.5px">
      Estas tres instituciones del sector privado integran el Cuadro Técnico junto a las Subsecretarías de Turismo y de Producción.
      Acuerdan los criterios antes de abrir la convocatoria y participan de la evaluación, para que el proceso no dependa de un solo actor
      ni beneficie únicamente a los proyectos ya consolidados. <a href="media-kit.php#evaluacion">Ver los criterios y cuánto pesa cada uno →</a>
    </p>
  </div>
</section>

<!-- ============================ COMUNIDAD ============================ -->
<section class="section civic">
  <div class="container" style="max-width:820px">
    <span class="eyebrow"><span class="dot"></span> Para la comunidad de Esquel</span>
    <h2>“Acá no hay nada para hacer” es justo lo que este programa viene a desmentir</h2>
    <p>
      Esquel es la puerta al Parque Nacional Los Alerces, a La Trochita y a La Hoya. Lo que falta no es paisaje
      ni gente con algo para ofrecer: falta que eso se convierta en algo que se pueda vender, con precio, con
      canal y con quién lo comercialice.
    </p>
    <div class="callout">
      <span class="lbl">Sobre el régimen de inversiones</span>
      <p>
        Esquel tiene además un Régimen de Promoción de Inversiones Turísticas para proyectos de capital grande,
        y es una herramienta necesaria. Pero no es la única puerta. Esquel LAB es la que no te pide capital para
        invertir: te pide que tengas algo para mostrar y ganas de trabajarlo.
      </p>
    </div>
    <p>
      Esta primera cohorte prioriza a quienes hoy pueden dedicarle tiempo real al proceso, porque son quienes
      pueden mostrar el camino más rápido. De ahí van a salir los aprendizajes para identificar nuevos grupos
      y diseñar los programas que siguen. No es una convocatoria de una sola vez.
    </p>
    <a href="inscribirse.php" class="btn btn-primary btn-lg" style="margin-top:12px">Postularme</a>
  </div>
</section>

<?php if ($abierta): ?>
<div class="sticky-cta" id="stickyCta" aria-hidden="true">
  <div class="container">
    <p>
      <?php if ($dias > 0): ?>
        <strong>Quedan <?= (int) $dias ?> día<?= $dias === 1 ? '' : 's' ?></strong> para postularte a la primera cohorte.
      <?php else: ?>
        <strong>Último día</strong> para postularte a la primera cohorte.
      <?php endif; ?>
    </p>
    <a href="inscribirse.php" class="btn btn-primary btn-sm">Postularme</a>
    <button type="button" class="sticky-cta-close" id="stickyCtaClose" aria-label="Ocultar esta barra">&times;</button>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
