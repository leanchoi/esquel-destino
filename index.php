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
<?php /* Estado + nombre del programa: un título que informa no necesita convencer. */ ?>
      <span class="eyebrow"><span class="dot"></span> Esquel LAB · Primera cohorte 2026</span>
      <h1><em>Inscripciones abiertas</em> al programa de aceleración turística de Esquel</h1>
      <p class="hero-lede">
        Ocho semanas de trabajo con un equipo del municipio, en tu campo, tu taller o tu local,
        sobre lo que hace falta para poder recibir visitantes: el precio, la forma de reservarlo,
        el material de venta y la ficha con la que las agencias de la ciudad lo ofrecen.
      </p>

      <?php /* La fecha de cierre no va acá: ya está en la barra de plazo, justo arriba. */ ?>
      <dl class="hero-datos">
        <?php /* "Se seleccionan" y no "Cupos": la escasez sale del filtro de
                 admisión, que es real, y no de un contador que corre. */ ?>
        <div><dt>Costo</dt><dd class="is-free">Gratuito</dd></div>
        <div><dt>Se seleccionan</dt><dd>13 a 18 proyectos</dd></div>
        <div><dt>Dedicación</dt><dd>12 hs por semana</dd></div>
      </dl>

      <div class="hero-cta">
        <a href="inscribirse.php" class="btn btn-primary btn-lg">Postularme</a>
        <a href="#para-vos" class="btn btn-secondary btn-lg">Ver si es para mí</a>
      </div>
      <p class="hero-note">Podés presentarte sin monotributo ni habilitación.</p>
    </div>

    <div class="hero-media">
      <figure class="hero-figure">
        <div class="hero-frame">
          <?php /* Es la imagen más grande de la primera pantalla: el navegador
                   tiene que pedirla antes que el resto, no en orden de aparición. */ ?>
          <?= foto('hero-esquel', 'Dos personas de espaldas mirando el valle de Esquel y la cordillera al atardecer', ['data-parallax' => '', 'loading' => 'eager', 'fetchpriority' => 'high']) ?>
        </div>
        <figcaption class="hero-caption">Esquel, puerta del Parque Nacional Los Alerces.</figcaption>
      </figure>

      <div class="hero-org">
        <span class="hero-org-lbl">Un programa de</span>
        <div class="hero-org-logos">
          <img src="assets/images/web/logo-esquel-lab-horizontal.png" alt="Esquel LAB" width="338" height="96">
          <img src="assets/images/web/logo-municipio-esquel.png" alt="Municipio de Esquel" class="is-muni" width="459" height="92">
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
      <h2>La mayoría de los que buscamos no trabaja en turismo</h2>
      <p>Son oficios, campos y comercios de Esquel que un visitante pagaría por conocer, aunque quien está adentro rara vez los mire así. Si te reconocés en alguno de estos seis casos, presentate.</p>
    </div>

    <div class="profile-grid">
      <article class="profile-card">
        <figure><?= foto('perfil-gastronomia', 'Persona sirviendo té y torta casera en una mesa de madera con vista a la montaña') ?></figure>
        <div class="body">
          <h3>Tenés un negocio que funciona pero no le vende a turistas</h3>
          <p>Una casa de té, una panadería, un taller. La gente pasa, compra y se va. Nunca lo armaste como experiencia.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><?= foto('perfil-guia', 'Guía señalando un pico nevado a dos visitantes en un sendero de trekking') ?></figure>
        <div class="body">
          <h3>Sos guía o prestador y vendés solo por WhatsApp</h3>
          <p>Tenés el servicio. No tenés precio para agencias, ni forma de que te reserven sin escribirte.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><?= foto('perfil-chacra', 'Persona cosechando frambuesas entre hileras de cultivo con un invernadero al fondo') ?></figure>
        <div class="body">
          <h3>Tenés campo y no sabés si “eso” se puede visitar</h3>
          <p>La esquila, la cosecha, el proceso del dulce. Para vos es el trabajo de todos los días. Para un visitante es algo que nunca vio.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><?= foto('perfil-lana', 'Persona tejiendo en un telar con madejas de lana teñida colgando') ?></figure>
        <div class="body">
          <h3>Hacés algo con las manos y lo vendés suelto</h3>
          <p>Lana, cerámica, conservas. Sin relato, sin packaging y sin conexión con quien visita Esquel.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><?= foto('perfil-reabrir', 'Persona abriendo la persiana de su local, el Almacén de Montaña, con la cordillera de fondo') ?></figure>
        <div class="body">
          <h3>Tenés un emprendimiento turístico que se quedó</h3>
          <p>Antes funcionaba y hoy no. La idea sigue en pie; lo que hay que rehacer es cómo se ofrece.</p>
        </div>
      </article>

      <article class="profile-card">
        <figure><?= foto('perfil-idea', 'Cuaderno con anotaciones, lápiz y mate sobre una mesa de madera, con la cordillera de fondo') ?></figure>
        <div class="body">
          <h3>Tenés una idea y algo con qué arrancar</h3>
          <p>Todavía no abriste, pero ya está el lugar, la receta o el oficio. Falta armarlo y ponerlo a andar.</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ============================ QUÉ SE TRABAJA ============================ -->
<section class="section section-alt" id="que-se-trabaja">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Qué se trabaja</span>
      <h2>Lo que se arma durante las ocho semanas</h2>
      <p>El trabajo se ordena alrededor de estos puntos. Cuáles pesan más depende de cada proyecto.</p>
    </div>

    <div class="trabajo-split">
      <ol class="trabajo-lista">
        <li>
          <h3>El precio</h3>
          <p>Al público y para agencias, con los costos sacados y el margen a la vista.</p>
        </li>
        <li>
          <h3>Las reservas</h3>
          <p>Un lugar donde te reserven y te llegue el aviso, sin depender de que contestes un mensaje.</p>
        </li>
        <li>
          <h3>El relato</h3>
          <p>Qué pasa en la experiencia, en qué orden y cuánto dura. Escrito, para que salga igual cada vez.</p>
        </li>
        <li>
          <h3>Las fotos y el video</h3>
          <p>Registro hecho para promoción, que después usás en tus redes y en el material de las agencias.</p>
        </li>
        <li>
          <h3>La ficha comercial</h3>
          <p>El documento con el que las agencias receptivas de Esquel pueden ofrecer tu experiencia.</p>
        </li>
        <li>
          <h3>El producto, cuando la propuesta da</h3>
          <p>Un dulce, una madeja, una pieza. Se trabaja el envase, la etiqueta y de dónde viene, y se ve si puede llevar el sello municipal <strong>Hecho en Esquel</strong>. Varias experiencias van a ser solo servicio, y para postularte no hace falta que tengas uno.</p>
        </li>
      </ol>

      <figure class="trabajo-foto">
        <?= foto('economia-recuerdos', 'Frasco de dulce casero con etiqueta escrita a mano, botella de cerveza artesanal y madeja de lana teñida sobre una mesa de madera') ?>
        <figcaption>El visitante lo abre en su casa tres semanas después del viaje. Ahí es cuando te recomienda a alguien.</figcaption>
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
          <?= foto('linea-acelera', 'Calle comercial de Esquel con la cordillera de fondo') ?>
        </div>
        <div class="line-body">
          <img src="assets/images/web/logo-esquel-acelera.png" alt="Esquel Acelera" class="line-logo" width="180" height="208">
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
          <?= foto('linea-raiz', 'Campo con ovejas, un perro de trabajo y un galpón, con la cordillera de fondo') ?>
        </div>
        <div class="line-body">
          <img src="assets/images/web/logo-esquel-raiz.png" alt="Raíz" class="line-logo" width="165" height="208">
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
      <h2>Un equipo puesto a trabajar sobre tu proyecto</h2>
      <p>El trabajo es uno a uno, en tu lugar, durante ocho semanas.</p>
    </div>

    <div class="ayuda-grid">
      <article class="ayuda-card">
        <h3>Un equipo sentado con vos</h3>
        <p>Gente con oficio en turismo, producto y comercialización, trabajando sobre tu caso: qué ofrecés, cuánto te cuesta y quién te lo compraría.</p>
      </article>
      <article class="ayuda-card">
        <h3>El material se produce acá</h3>
        <p>Fotos y video de tu experiencia, el diseño de tu etiqueta, los textos con los que la vas a ofrecer y tu presencia en internet. El programa pone los recursos técnicos y audiovisuales, y el material se produce durante el proceso, con vos adentro. Es lo que después vas a usar para vender.</p>
      </article>
      <article class="ayuda-card">
        <h3>Herramientas que quedan</h3>
        <p>Lo que se produce en estas semanas te queda a vos: el precio, el material, la ficha comercial y las cuentas ordenadas.</p>
      </article>
    </div>

    <div class="callout">
      <span class="lbl">Lo que se pide de tu lado</span>
      <p>El programa pone el equipo y los recursos; vos ponés tiempo: alrededor de <strong>12 horas por semana</strong>. Lo confirmás por escrito al postularte, porque un cupo ocupado por alguien que no puede sostenerlo es un cupo que le sacamos a otro.</p>
    </div>
  </div>
</section>

<!-- ============================ EL CAMINO ============================ -->
<section class="section" id="el-camino">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Cronograma</span>
      <h2>Qué pasa después de que te postulás</h2>
      <p>Las fechas de esta primera cohorte.</p>
    </div>

    <ol class="camino">
      <li class="camino-paso">
        <span class="camino-num">1</span>
        <span class="camino-fecha">Hasta el <?= e(fecha_larga(FECHA_CIERRE)) ?></span>
        <h3>Te postulás</h3>
        <p>El formulario lleva unos 20 minutos. Podés dejarlo por la mitad y volver más tarde.</p>
      </li>
      <li class="camino-paso">
        <span class="camino-num">2</span>
        <span class="camino-fecha">Julio y agosto</span>
        <h3>Se evalúa tu postulación</h3>
        <p>El Cuadro Técnico la puntúa con la matriz acordada antes de abrir. Se lee todo al cierre, así que no es por orden de llegada.</p>
      </li>
      <li class="camino-paso">
        <span class="camino-num">3</span>
        <span class="camino-fecha"><?= e(fecha_larga(FECHA_INICIO)) ?></span>
        <h3>Te avisamos</h3>
        <p>Escribimos a todos los que se postularon, hayan quedado o no.</p>
      </li>
      <li class="camino-paso">
        <span class="camino-num">4</span>
        <span class="camino-fecha">Agosto y septiembre</span>
        <h3>Ocho semanas de trabajo</h3>
        <p>Visitas a tu lugar, encuentros con el resto de la cohorte y desarrollo de la experiencia.</p>
      </li>
      <li class="camino-paso">
        <span class="camino-num">5</span>
        <span class="camino-fecha"><?= e(fecha_larga(FECHA_FIN)) ?></span>
        <h3>Presentás lo que armaste</h3>
        <p>Ante las agencias receptivas y la prensa local, con la experiencia lista para recibir reservas.</p>
      </li>
    </ol>
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
          <p><strong>Para postularte, no.</strong> Podés presentarte aunque hoy estés trabajando de manera informal.</p>
          <p>Después es otra cosa: si tu propuesta necesita alguna habilitación para poder venderse en serio, ordenar eso forma parte del trabajo de las ocho semanas y el programa te acompaña en los trámites. Se entra como estás y se sale más formal.</p>
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

<!-- ============================ CIERRE ============================ -->
<section class="section section-alt">
  <div class="container cierre">
    <?php if ($abierta): ?>
      <h2>Postulate antes del <?= e(fecha_larga(FECHA_CIERRE)) ?></h2>
      <p>
        Son unos 20 minutos y se puede guardar a medio hacer. Si tenés dudas antes de empezar,
        escribinos a <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a>.
      </p>
      <a href="inscribirse.php" class="btn btn-primary btn-lg">Postularme</a>
    <?php else: ?>
      <h2>La primera cohorte ya cerró</h2>
      <p>
        Las postulaciones cerraron el <?= e(fecha_larga(FECHA_CIERRE)) ?>. Va a haber más convocatorias:
        escribinos y te avisamos cuando abra la próxima.
      </p>
      <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>" class="btn btn-secondary btn-lg">Escribirnos</a>
    <?php endif; ?>
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
