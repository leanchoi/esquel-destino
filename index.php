<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

// La sesión se abre acá arriba, antes de imprimir una sola línea de HTML.
//
// Es la única forma de que funcione el token del formulario del pop-up. Ese
// token lo genera csrf_field(), que está al final de la página: para entonces
// las cabeceras ya salieron y PHP no puede mandar la cookie de sesión. El
// token quedaba guardado en una sesión que el navegador nunca recibía, y al
// mandar el formulario el servidor lo rechazaba con "la página estuvo abierta
// demasiado tiempo". Sin JavaScript no pasaba, porque avisame.php sí abre la
// sesión antes de imprimir nada.
iniciar_sesion();

$pageTitle = 'Esquel LAB — Convocatoria 2026 para nuevas experiencias turísticas';
$pageDescription = 'Programa gratuito. Ocho semanas de acompañamiento técnico para convertir tu oficio, tu campo o tu servicio en una experiencia turística lista para recibir visitantes. Se seleccionan hasta 18 proyectos. Postulaciones hasta el 9 de agosto de 2026.';
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
      <?php if ($abierta): ?>
        <span class="eyebrow"><span class="dot"></span> Esquel LAB · <?= e(COHORTE_ACTUAL) ?></span>
        <h1><em>Inscripciones abiertas</em> al programa de aceleración turística de Esquel</h1>
        <p class="hero-lede">
          Ocho semanas de trabajo con un equipo, en tu campo, tu taller o tu local,
          sobre lo que hace falta para poder recibir visitantes: el precio, la forma de reservarlo,
          el material de venta y la ficha con la que las agencias de la ciudad lo ofrecen.
        </p>
      <?php else: ?>
        <?php /* Con la convocatoria cerrada el título no puede seguir diciendo
                 "Inscripciones abiertas". Y el que llega tarde no necesita que
                 le expliquen que llegó tarde: necesita saber que hay una próxima. */ ?>
        <span class="eyebrow"><span class="dot"></span> Esquel LAB · <?= e(COHORTE_ACTUAL) ?> en marcha</span>
        <h1>La primera cohorte ya está trabajando. <em>La próxima puede ser la tuya.</em></h1>
        <p class="hero-lede">
          Un grupo de emprendedores de Esquel está ocho semanas dándole forma a su experiencia turística:
          el precio, la forma de reservarla, el material con el que se vende.
          Cuando abra la segunda convocatoria, queremos que te enteres primero.
        </p>
      <?php endif; ?>

      <?php /* La fecha de cierre no va acá: ya está en la barra de plazo, justo arriba. */ ?>
      <dl class="hero-datos">
        <?php /* "Se seleccionan" y no "Cupos": la escasez sale del filtro de
                 admisión, que es real, y no de un contador que corre. */ ?>
        <div><dt>Costo</dt><dd class="is-free">Gratuito</dd></div>
        <div><dt>Se seleccionan</dt><dd>13 a 18 proyectos</dd></div>
        <div><dt>Dedicación</dt><dd>12 hs por semana</dd></div>
      </dl>

      <?php if ($abierta): ?>
        <div class="hero-cta">
          <a href="inscribirse.php" class="btn btn-primary btn-lg">Postularme</a>
          <a href="#para-vos" class="btn btn-secondary btn-lg">Ver si es para mí</a>
        </div>
        <p class="hero-note">Podés presentarte sin monotributo ni habilitación.</p>
      <?php else: ?>
        <div class="hero-cta">
          <button type="button" class="btn btn-primary btn-lg" data-abrir-avisame="hero">Avisame de la próxima</button>
          <a href="#para-vos" class="btn btn-secondary btn-lg">Ver si es para mí</a>
        </div>
        <p class="hero-note">Sin compromiso: es para que no te enteres tarde otra vez.</p>
      <?php endif; ?>
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
        <img src="<?= asset('assets/images/web/logo-esquel-lab-horizontal.png') ?>" alt="Esquel LAB" class="hero-org-marca" width="338" height="96">
        <div class="hero-org-apoyo">
          <span class="hero-org-lbl">Con el acompañamiento de</span>
          <img src="<?= asset('assets/images/web/logo-municipio-esquel.png') ?>" alt="<?= e(APOYO_INSTITUCION) ?>" class="is-muni" width="459" height="92">
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
          <p>Nada. El programa es <strong>gratuito</strong>. Lo hace posible el apoyo de la <?= e(APOYO_INSTITUCION) ?>, a través de <?= e(APOYO_AREAS) ?>.</p>
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
    <h2 style="font-size:26px">La selección no la hace una sola parte</h2>
    <div class="gov-logos">
      <span>CAMOCH</span>
      <span>Cámara de Prestadores Turísticos de Esquel</span>
      <span>FEHGRA Filial Esquel</span>
    </div>
    <p style="max-width:64ch;margin:0 auto;color:var(--ink-2);font-size:15.5px">
      Estas tres instituciones del sector privado integran el Cuadro Técnico junto a <?= e(APOYO_AREAS) ?>.
      Acuerdan los criterios antes de abrir la convocatoria y participan de la evaluación, para que el proceso no dependa de un solo actor
      ni beneficie únicamente a los proyectos ya consolidados. <a href="media-kit.php#evaluacion">Ver los criterios y cuánto pesa cada uno →</a>
    </p>
  </div>
</section>

<?php if (!$abierta): ?>
<!-- ======================= DÓNDE ESTAMOS (cohortes) ======================= -->
<?php /*
  Con la convocatoria cerrada, esta es la sección que reemplaza al "postulate
  ya". Cuenta en una línea de tiempo que la primera cohorte cerró, está
  trabajando y va a haber una segunda, con el botón para anotarse en el último
  hito, que es donde la pregunta "¿y yo?" aparece sola.
*/ ?>
<section class="section section-white" id="cohortes">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Dónde estamos</span>
      <h2>La primera ya arrancó</h2>
      <p>Esto es lo que está pasando ahora mismo, y lo que viene después.</p>
    </div>
    <?php require_once __DIR__ . '/includes/cohortes.php'; echo linea_cohortes(); ?>
  </div>
</section>
<?php endif; ?>

<!-- ============================ CIERRE ============================ -->
<?php /*
  El cierre va en oscuro y con el video de Esquel de fondo. Antes era del
  mismo color que el pie de página —los dos #EBE7E0— y separados sólo por una
  línea de 1px, así que los dos juntos parecían un pie larguísimo: no se veía
  dónde terminaba la página y empezaba la letra chica. El contraste corta eso.

  El video se carga recién cuando el bloque se acerca a la pantalla, para no
  cobrarle un iframe de YouTube a alguien que nunca baja hasta acá.
*/ ?>
<section class="section section-dark cierre-video"
  <?php if (CIERRE_VIDEO_YT !== ''): ?>data-video="<?= e(CIERRE_VIDEO_YT) ?>" data-video-desde="<?= (int) CIERRE_VIDEO_DESDE ?>"<?php endif; ?>>

  <div class="cierre-fondo" data-parallax="52">
    <picture>
      <source srcset="<?= asset(CIERRE_VIDEO_POSTER . '.webp') ?>" type="image/webp">
      <img src="<?= asset(CIERRE_VIDEO_POSTER . '.jpg') ?>" alt="" width="1120" height="475" loading="lazy" decoding="async">
    </picture>
  </div>
  <div class="cierre-velo"></div>

  <div class="container cierre">
    <?php if ($abierta): ?>
      <span class="eyebrow">
        <span class="dot"></span>
        <?= $dias > 0 ? 'Quedan ' . (int) $dias . ' día' . ($dias === 1 ? '' : 's') : 'Último día' ?>
      </span>
      <h2>Postulate antes del <?= e(fecha_larga(FECHA_CIERRE)) ?></h2>
      <p>
        Son unos 20 minutos y se puede guardar a medio hacer. Si tenés dudas antes de empezar,
        escribinos a <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a>.
      </p>
      <a href="inscribirse.php" class="btn btn-primary btn-lg">Postularme</a>
      <p class="cierre-nota">Podés presentarte sin monotributo ni habilitación.</p>
    <?php else: ?>
      <span class="eyebrow"><span class="dot"></span> <?= e(PROXIMA_COHORTE) ?></span>
      <h2>Vos podés ser el que sigue</h2>
      <p>
        Dejanos tus datos y te avisamos apenas abra la próxima convocatoria. Si mientras tanto
        aparece algo que le sirva a lo tuyo, también te lo contamos.
      </p>
      <button type="button" class="btn btn-primary btn-lg" data-abrir-avisame="cierre">Avisame cuando abra</button>
      <p class="cierre-nota">Son dos datos y treinta segundos.</p>
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

<?php if (!$abierta): ?>
<?php /*
  El pop-up. Sale una sola vez y no vuelve: si alguien lo cerró, ya contestó
  que no. Volver a mostrarlo en cada visita no convence a nadie, molesta.

  Va en el HTML y no lo arma el JavaScript porque los mismos campos tienen que
  poder enviarse sin JavaScript: el <form> apunta a avisame.php y funciona
  igual, sólo que recargando la página en vez de quedarse acá.
*/ ?>
<div class="modal" id="modalAvisame" hidden>
  <div class="modal-fondo" data-cerrar-avisame></div>
  <div class="modal-caja" role="dialog" aria-modal="true" aria-labelledby="modalAvisameTitulo">
    <button type="button" class="modal-x" data-cerrar-avisame aria-label="Cerrar">&times;</button>

    <div class="modal-cuerpo" id="modalAvisameCuerpo">
      <span class="eyebrow"><span class="dot"></span> <?= e(PROXIMA_COHORTE) ?></span>
      <h2 id="modalAvisameTitulo">La primera camada ya está manos a la obra</h2>
      <p class="modal-lede">
        Emprendedores de Esquel y del campo están armando ahora mismo la experiencia que van a
        vender. <strong>El próximo podés ser vos</strong>: dejanos tus datos y te avisamos cuando
        abra la segunda convocatoria, o si aparece algo que le sirva a lo tuyo antes.
      </p>

      <form method="post" action="avisame.php" class="form-avisame" id="formAvisame">
        <?= csrf_field() ?>
        <input type="hidden" name="origen" value="popup" id="avisameOrigen">
        <?= campos_avisame(['nombre' => '', 'email' => '', 'telefono' => '', 'linea' => '', 'instagram' => '', 'cuenta' => ''], [], 'pop') ?>
        <p class="form-error" id="avisameError" hidden></p>
        <div class="modal-acciones">
          <button type="submit" class="btn btn-primary btn-lg">Avisame</button>
          <button type="button" class="btn btn-secondary" data-cerrar-avisame>Ahora no</button>
        </div>
        <p class="modal-fine">Sólo para avisarte de esto. No compartimos tus datos con nadie.</p>
      </form>
    </div>

    <?php /* Lo que se ve después de mandar, sin recargar. */ ?>
    <div class="modal-gracias" id="modalAvisameGracias" hidden>
      <div class="gracias-tilde" aria-hidden="true">
        <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l8 8 15-16"/></svg>
      </div>
      <h2>Listo, te anotamos</h2>
      <p>Cuando abra la próxima cohorte sos de los primeros en enterarte.</p>
      <button type="button" class="btn btn-secondary" data-cerrar-avisame>Cerrar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
