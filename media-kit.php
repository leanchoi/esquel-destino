<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Media kit · Esquel LAB';
$pageDescription = 'Textos listos para copiar y compartir sobre Esquel LAB, los datos del programa, cómo se eligen los participantes, gacetilla para medios y logos para descargar.';
$activeNav = 'prensa';
$abierta = convocatoria_abierta();
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><span class="dot"></span> Difusión y prensa</span>
    <h1>Media kit</h1>
    <p class="page-sub">Ayudanos a que le llegue a más gente</p>
    <p class="lede">
      La mayoría de la gente que debería postularse no se considera “del turismo”, así que no va a
      buscar esta página sola. Se entera porque alguien se la manda. Acá abajo hay textos escritos
      para copiar y pegar tal cual: en WhatsApp, en un grupo del barrio, al aire en una radio o en
      una nota. Usalos, cortalos o reescribilos.
    </p>
    <p class="lede" style="font-size:16px">
      <a href="#medios">¿Sos periodista? Andá directo al material para medios →</a>
    </p>
  </div>
</section>

<!-- ============================ PARA COMPARTIR ============================ -->
<section class="section section-white">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Para pasar la voz</span>
      <h2>Tres textos, según a quién se lo mandes</h2>
      <p>Están en castellano de acá, sin vueltas. El botón copia el texto completo.</p>
    </div>

    <div class="release">
      <span class="tag">Para un grupo de WhatsApp o una historia</span>
      <h3>El corto: se lee de una pasada</h3>
      <div class="release-body" id="txtCorto">
        <p>¿Viste esto? Abrió <strong>Esquel LAB</strong>: ocho semanas de acompañamiento
        gratuito para convertir lo que ya hacés en algo que un visitante pueda comprar. Sirve para casas
        de té, chacras, talleres, guías, artesanos, comercios con algo propio.</p>
        <p>No hace falta monotributo ni habilitación para anotarse. Entran hasta 18 proyectos y cierra
        el <?= e(fecha_larga(FECHA_CIERRE)) ?>. Está todo en <?= e(SITE_DOMINIO) ?></p>
      </div>
      <button class="copy-btn" data-copy="#txtCorto">Copiar texto</button>
    </div>

    <div class="release green">
      <span class="tag">Para mandarle a una persona en particular</span>
      <h3>El personal: cuando ya sabés quién tendría que anotarse</h3>
      <div class="release-body" id="txtPersonal">
        <p>Te mando esto porque me acordé de vos.</p>
        <p>Es gratis. Durante ocho semanas un equipo va a tu lugar
        —tu local, tu campo, tu taller— y te ayuda a armar lo que hacés como una experiencia para
        visitantes: ponerle precio, ordenar cómo se cuenta, tener dónde te reserven y dejarla lista
        para que las agencias de Esquel la ofrezcan.</p>
        <p>Ojo que no es solo para gente de turismo. Al revés: buscan oficios, campos y comercios que
        hoy no se ven a sí mismos como turísticos. Tampoco hace falta que tengas monotributo ni
        habilitación para postularte.</p>
        <p>Se anota en <?= e(SITE_DOMINIO) ?> y cierra el <?= e(fecha_larga(FECHA_CIERRE)) ?>. El
        formulario lleva unos 20 minutos y lo podés dejar por la mitad y seguir después.</p>
      </div>
      <button class="copy-btn" data-copy="#txtPersonal">Copiar texto</button>
    </div>

    <div class="release">
      <span class="tag">Para leer al aire · unos 40 segundos</span>
      <h3>El hablado: radio, altoparlante, reunión de vecinos</h3>
      <div class="release-body" id="txtRadio">
        <p>Abrió la convocatoria a Esquel LAB, un programa gratuito para
        emprendedores y productores, de la ciudad y del campo.</p>
        <p>Durante ocho semanas un equipo trabaja en el lugar de cada participante para convertir lo
        que ya hace en una experiencia turística: con precio, con una forma clara de reservarla y
        lista para que las agencias del pueblo la ofrezcan.</p>
        <p>Se seleccionan hasta dieciocho proyectos. No hace falta monotributo ni habilitación para
        postularse. Las inscripciones cierran el <?= e(fecha_larga(FECHA_CIERRE)) ?> y el formulario
        está en <?= e(SITE_DOMINIO) ?>.</p>
      </div>
      <button class="copy-btn" data-copy="#txtRadio">Copiar texto</button>
    </div>

    <div class="callout green">
      <span class="lbl">Si te preguntan “¿y a quién le sirve?”</span>
      <p>La respuesta corta: a cualquiera que haga algo que un visitante pagaría por ver, probar o
      llevarse. Una casa de té, una esquila, una cosecha, un telar, una panadería con historia, un
      circuito que alguien ya sabe hacer. Si dudás, esa duda es justamente la razón para postularse.</p>
    </div>
  </div>
</section>

<!-- ============================ LOS DATOS ============================ -->
<section class="section has-topo">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Los datos</span>
      <h2>Todo lo que te pueden preguntar</h2>
      <p>Si alguien te repregunta, la respuesta está acá.</p>
    </div>

    <table class="facts-table">
      <tr><td>Qué es</td><td>Un programa que acompaña a emprendedores y productores a convertir lo que ya hacen en una experiencia turística lista para operar</td></tr>
      <tr><td>Cuánto cuesta</td><td>Nada. Es gratuito, con el apoyo de la <?= e(APOYO_INSTITUCION) ?></td></tr>
      <tr><td>Quién lo acompaña</td><td><?= e(APOYO_INSTITUCION) ?>, a través de <?= e(APOYO_AREAS) ?></td></tr>
      <tr><td>Las dos líneas</td><td>Esquel Acelera (proyectos en la ciudad) y Raíz (proyectos en el campo)</td></tr>
      <tr><td>Cuántos entran</td><td>13 a 18 proyectos: 8 a 10 urbanos y 5 a 8 rurales</td></tr>
      <tr><td>Hasta cuándo se anota</td><td><?= e(fecha_larga(FECHA_APERTURA)) ?> al <?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026. Es cierre real: pasada esa fecha el formulario se cierra solo</td></tr>
      <tr><td>Cuándo se trabaja</td><td><?= e(fecha_larga(FECHA_INICIO)) ?> al <?= e(fecha_larga(FECHA_FIN)) ?> de 2026, ocho semanas</td></tr>
      <tr><td>Cuánto tiempo hay que ponerle</td><td>Unas 12 horas por semana: 2 de taller grupal, 1 o 2 de reunión en tu lugar y el resto trabajo propio</td></tr>
      <tr><td>Qué hace falta para postularse</td><td>Ni monotributo ni habilitación. Si tu propuesta después necesita habilitarse, el programa te acompaña a hacerlo</td></tr>
      <tr><td>Con qué se termina</td><td>Precio al público y para agencias, guión de la experiencia, canal de reservas, fotos y una ficha en manos de las agencias receptivas</td></tr>
      <tr><td>Quién elige</td><td>Un Cuadro Técnico mixto: las Subsecretarías más CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA Filial Esquel</td></tr>
      <tr><td>Dónde se anota</td><td><a href="inscribirse.php"><?= e(SITE_DOMINIO) ?>/inscribirse.php</a></td></tr>
    </table>
  </div>
</section>

<!-- ============================ CÓMO SE ELIGE ============================ -->
<section class="section section-white" id="evaluacion">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Cómo se elige</span>
      <h2>No es por orden de llegada, y los criterios se publicaron antes de abrir</h2>
      <p>
        La selección no la hace una sola parte. Un <strong>Cuadro Técnico</strong> —las Subsecretarías
        de Turismo y de Producción junto a CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA
        Filial Esquel— acordó estos cinco criterios <em>antes</em> de abrir la convocatoria, para que el
        proceso no dependa de un solo actor ni favorezca únicamente a los proyectos que ya están armados.
      </p>
    </div>

    <div class="ba-wrap">
      <table class="ba-table">
        <thead><tr><th>Criterio</th><th>Qué se mira</th><th>Cuánto pesa</th></tr></thead>
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

    <div class="callout" style="margin-top:22px">
      <span class="lbl">Por qué la motivación pesa más</span>
      <p>
        Es una decisión deliberada del programa: una propuesta simple con alguien decidido a sostenerla
        llega más lejos que una propuesta redonda que nadie empuja. Por eso el perfil y la motivación
        valen una vez y media, y el producto físico —que no todas las propuestas van a tener— vale la mitad.
      </p>
    </div>
  </div>
</section>

<!-- ============================ PARA MEDIOS ============================ -->
<section class="section section-alt" id="medios">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Para medios</span>
      <h2>Material de prensa</h2>
      <p>Gacetilla, párrafo de contexto, logos y contacto directo.</p>
    </div>

    <div class="release">
      <span class="tag">Gacetilla</span>
      <h3>Esquel abre la convocatoria a un programa gratuito para convertir oficios locales en experiencias turísticas</h3>
      <div class="release-body" id="gacetilla">
        <p>ESQUEL, CHUBUT · Abrió la convocatoria a la primera cohorte de
        Esquel LAB, un programa gratuito —con el acompañamiento de la <?= e(APOYO_INSTITUCION) ?>, a
        través de <?= e(APOYO_AREAS) ?>— que acompaña
        a emprendedores urbanos y productores rurales a convertir lo que ya hacen en experiencias
        turísticas listas para operar.</p>

        <p>El programa trabaja ocho semanas con cada participante, en su propio lugar de trabajo. Al
        final, cada proyecto tiene precio al público y precio para agencias, un guión de la experiencia,
        un canal donde recibir reservas, registro fotográfico y una ficha comercial en manos de las
        agencias receptivas de la ciudad. Se organiza en dos líneas: Esquel Acelera, para proyectos
        urbanos, y Raíz, para los rurales.</p>

        <p>Se seleccionan entre 13 y 18 proyectos. La convocatoria no exige monotributo ni habilitación
        previa para postularse: la formalización, cuando hace falta, forma parte del acompañamiento. La
        contrapartida que sí se pide es tiempo, unas 12 horas semanales durante las ocho semanas.</p>

        <p>La selección está a cargo de un Cuadro Técnico mixto que integran las Subsecretarías y tres cámaras
        del sector privado —CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA Filial
        Esquel—, que acordaron los criterios de evaluación antes de abrir la inscripción y los
        publicaron junto con la convocatoria. La ponderación da mayor peso al perfil y la motivación de
        quien se postula que a lo consolidada que esté la propuesta, con el objetivo declarado de dejar
        lugar a quienes recién empiezan.</p>

        <p>Las postulaciones se reciben hasta el <?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026 en
        <?= e(SITE_DOMINIO) ?>. El trabajo en territorio va del <?= e(fecha_larga(FECHA_INICIO)) ?> al
        <?= e(fecha_larga(FECHA_FIN)) ?>, cuando las experiencias se presentan públicamente ante
        agencias receptivas y prensa.</p>
      </div>
      <button class="copy-btn" data-copy="#gacetilla">Copiar gacetilla</button>
    </div>

    <div class="release green">
      <span class="tag">Párrafo de contexto</span>
      <h3>Para cerrar una nota</h3>
      <div class="release-body" id="boilerplate">
        <p>Esquel LAB (Laboratorio de Destino Esquel) es un programa gratuito, con el acompañamiento
        de la <?= e(APOYO_INSTITUCION) ?> a través de <?= e(APOYO_AREAS) ?>, que acompaña a emprendedores urbanos y productores
        rurales a convertir servicios y saberes que ya existen en experiencias turísticas comercializables.
        Se organiza en dos líneas —Esquel Acelera, urbana, y Raíz, rural— y trabaja ocho semanas con cada
        cohorte, con talleres grupales y acompañamiento individual en el lugar de trabajo de cada
        participante. La selección está a cargo de un Cuadro Técnico integrado por las Subsecretarías y tres
        cámaras del sector privado local.</p>
      </div>
      <button class="copy-btn" data-copy="#boilerplate">Copiar párrafo</button>
    </div>

    <div class="mk-grid" style="margin-top:36px">
      <div class="mk-card">
        <h3>Logos</h3>
        <p>PNG con fondo transparente. La versión horizontal conviene cuando hay poco alto disponible.</p>
        <div class="dl">
          <a href="assets/images/logo-esquel-lab-horizontal.png" download class="btn btn-secondary btn-sm">Esquel LAB (horizontal)</a>
          <a href="assets/images/logo-esquel-lab.png" download class="btn btn-secondary btn-sm">Esquel LAB (apilado)</a>
          <a href="assets/images/logo-esquel-lab-blanco.png" download class="btn btn-secondary btn-sm">Esquel LAB (blanco)</a>
          <a href="assets/images/logo-esquel-acelera.png" download class="btn btn-secondary btn-sm">Esquel Acelera</a>
          <a href="assets/images/logo-esquel-raiz.png" download class="btn btn-secondary btn-sm">Raíz</a>
          <a href="assets/images/logo-municipio-esquel.png" download class="btn btn-secondary btn-sm"><?= e(APOYO_INSTITUCION) ?></a>
        </div>
      </div>

      <div class="mk-card">
        <h3>Datos y criterios</h3>
        <p>La tabla de datos y la matriz de evaluación con sus pesos están en esta misma página, listas para citar.</p>
        <div class="dl">
          <a href="#evaluacion" class="btn btn-secondary btn-sm">Cómo se elige</a>
        </div>
      </div>

      <div class="mk-card">
        <h3>Entrevistas</h3>
        <p>Para coordinar con las Subsecretarías o con las cámaras que integran el Cuadro Técnico.</p>
        <div class="dl">
          <a href="mailto:<?= e(EMAIL_PRENSA) ?>" class="btn btn-primary btn-sm">Escribir a prensa</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ PREGUNTAS ============================ -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Preguntas</span>
      <h2>Las que más nos hacen</h2>
    </div>

    <div class="faq">
      <details class="faq-item" open>
        <summary>¿Esto es solo para el que ya tiene plata para invertir?</summary>
        <div class="faq-body">
          <p>No. Esquel LAB pone equipo técnico al lado de gente que ya está trabajando con lo que
          tiene y necesita ordenar la parte comercial: precio, canal de venta, materiales.</p>
          <p>La <?= e(APOYO_INSTITUCION) ?> tiene aparte un Régimen de Promoción de Inversiones Turísticas, para proyectos
          de otra escala. Son dos herramientas distintas: una moviliza capital, esta acompaña a quien ya
          está haciendo algo.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>No tengo nada que ver con el turismo. ¿Igual sirve?</summary>
        <div class="faq-body">
          <p>Sí, y es buena parte de lo que se busca. Muchas de las propuestas que entran no se dedican
          al turismo: son oficios, producciones o comercios locales que pueden convertirse en algo que
          un visitante quiera conocer. La esquila, la cosecha, el proceso del dulce, un taller que
          trabaja a la vista.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Hay que estar en regla para participar?</summary>
        <div class="faq-body">
          <p>Para postularse, no. Si tu propuesta necesita alguna habilitación para poder venderse en
          serio, ordenar eso es parte del camino y el programa te acompaña. La profesionalización es el
          objetivo, no el requisito de entrada.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Y el que no queda seleccionado?</summary>
        <div class="faq-body">
          <p>Su postulación queda registrada. Esta es la primera cohorte de un proceso pensado como
          continuo: de acá van a salir las próximas convocatorias y los programas complementarios. Que
          una propuesta no entre ahora no significa que el programa no sepa que existe.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary>¿Se hizo algo así antes en la zona?</summary>
        <div class="faq-body">
          <p>Es la primera cohorte de Esquel LAB. La metodología tiene un antecedente regional cercano:
          un programa parecido trabajó con este enfoque en El Bolsón, priorizando a los vecinos por sobre
          las grandes inversiones de afuera y poniendo en valor saberes locales que no estaban integrados
          a la oferta turística.</p>
        </div>
      </details>
    </div>

    <?php if ($abierta): ?>
      <div class="callout" style="margin-top:40px">
        <span class="lbl">Lo más útil que podés hacer con esta página</span>
        <p>
          Pensá en una sola persona de Esquel que hace algo que un visitante pagaría por conocer, y
          mandale el texto de arriba. La convocatoria cierra el <strong><?= e(fecha_larga(FECHA_CIERRE)) ?></strong>.
        </p>
      </div>
    <?php endif; ?>

    <div class="callout green" style="margin-top:20px">
      <span class="lbl">Contacto</span>
      <p>Subsecretaría de Turismo de Esquel · <a href="mailto:<?= e(EMAIL_PRENSA) ?>"><?= e(EMAIL_PRENSA) ?></a></p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
