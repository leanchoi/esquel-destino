<?php
/**
 * Términos y condiciones de participación.
 *
 * OJO: este texto lo redactó el equipo del sitio a partir de cómo funciona el
 * programa. Antes de publicarlo como documento vinculante conviene que lo
 * revise el área legal del municipio, sobre todo los puntos de datos
 * personales, uso de imagen y propiedad de lo producido.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Términos y condiciones de participación · Esquel LAB';
$pageDescription = 'Condiciones para postularse y participar de Esquel LAB: quién puede presentarse, cómo se selecciona, qué compromisos asume cada participante y cómo se tratan los datos personales.';
$activeNav = '';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Documento</span>
    <h1>Términos y condiciones de participación</h1>
    <p class="lede">
      Estas son las reglas de la convocatoria <?= e(fecha_larga(FECHA_APERTURA)) ?> — <?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026,
      primera cohorte de Esquel LAB. Al enviar el formulario de postulación estás aceptando lo que sigue.
    </p>
  </div>
</section>

<section class="section">
  <div class="container legal">

    <h2>1. Qué es Esquel LAB</h2>
    <p>
      Esquel LAB (Laboratorio de Destino Esquel) es un programa de la Municipalidad de Esquel, ejecutado por las
      Subsecretarías de Turismo y de Producción. Acompaña técnicamente a emprendedores y productores locales para
      convertir actividades que ya realizan en experiencias turísticas en condiciones de ser ofrecidas al público.
    </p>
    <p>
      El programa se organiza en dos líneas: <strong>Esquel Acelera</strong>, para proyectos urbanos, y
      <strong>Raíz</strong>, para proyectos rurales.
    </p>

    <h2>2. Quién puede postularse</h2>
    <ul>
      <li>Personas mayores de 18 años, o personas jurídicas, con un proyecto que se desarrolle en el ejido de Esquel o su zona de influencia.</li>
      <li>No se exige inscripción en monotributo, habilitación comercial ni registro previo para postularse.</li>
      <li>Se puede presentar más de un proyecto, cargando una postulación separada por cada uno.</li>
      <li>No pueden postularse quienes integren el Cuadro Técnico ni el personal municipal afectado directamente a la ejecución del programa.</li>
    </ul>

    <h2>3. Cómo se seleccionan los proyectos</h2>
    <p>
      La selección está a cargo del <strong>Cuadro Técnico</strong>, integrado por las Subsecretarías de Turismo y de
      Producción junto a CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA Filial Esquel.
    </p>
    <ul>
      <li>Las postulaciones se puntúan con una matriz de criterios acordada antes de la apertura y publicada junto con la convocatoria. <a href="media-kit.php#evaluacion">Se puede consultar acá</a>.</li>
      <li>La selección <strong>no</strong> es por orden de llegada.</li>
      <li>Se seleccionan entre 13 y 18 proyectos: de 8 a 10 en la línea urbana y de 5 a 8 en la rural. El programa puede ajustar esa distribución según las postulaciones recibidas.</li>
      <li>La decisión del Cuadro Técnico es inapelable. Se notifica por correo electrónico a todos los postulantes, hayan sido seleccionados o no.</li>
      <li>Postularse no genera derecho a ser seleccionado ni a recibir compensación alguna.</li>
    </ul>

    <h2>4. Qué se compromete a hacer quien participa</h2>
    <p>Quien resulte seleccionado asume, durante las ocho semanas del programa:</p>
    <ul>
      <li>Dedicar aproximadamente <strong>12 horas semanales</strong> al desarrollo de su propuesta, sumando talleres, reuniones y trabajo propio.</li>
      <li>Asistir a los talleres grupales y recibir al equipo en su lugar de trabajo en las visitas acordadas.</li>
      <li>Aportar información veraz sobre su actividad, sus costos y su capacidad operativa.</li>
      <li>Avisar cuanto antes si deja de poder sostener la dedicación comprometida, para liberar el cupo.</li>
      <li>Cumplir la normativa vigente que corresponda a su actividad. El programa acompaña los trámites de formalización cuando hacen falta, pero no los reemplaza ni los garantiza.</li>
    </ul>

    <h2>5. Qué ofrece el programa y qué no</h2>
    <p><strong>Ofrece:</strong> acompañamiento técnico gratuito, talleres grupales, visitas individuales, apoyo en el armado de precios, guión de la experiencia, canal de reservas y registro fotográfico, y la presentación pública de la experiencia ante agencias receptivas y prensa local.</p>
    <p><strong>No ofrece:</strong> aportes de dinero, subsidios, créditos, equipamiento, obras ni habilitaciones. Tampoco garantiza ventas, reservas ni la incorporación de la experiencia a la oferta comercial de ninguna agencia, que decide de manera independiente.</p>

    <h2>6. Datos personales</h2>
    <p>
      Los datos que se cargan en el formulario los recibe la Municipalidad de Esquel y se usan únicamente para evaluar
      la postulación, comunicarse con el postulante y elaborar estadísticas del programa. El tratamiento se ajusta a la
      <strong>Ley Nacional 25.326 de Protección de los Datos Personales</strong>.
    </p>
    <ul>
      <li>Los datos de contacto se comparten con los integrantes del Cuadro Técnico a los fines de la evaluación.</li>
      <li>No se ceden ni se venden a terceros con fines comerciales.</li>
      <li>Las postulaciones no seleccionadas quedan registradas para futuras convocatorias, salvo pedido expreso de baja.</li>
      <li>El titular puede pedir acceder, rectificar o suprimir sus datos escribiendo a <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a>.</li>
    </ul>
    <p>
      El sitio registra además estadísticas de uso agregadas (páginas visitadas, dispositivo, sitio de procedencia).
      No se guardan direcciones IP en claro ni se usan cookies de publicidad o de seguimiento entre sitios.
    </p>

    <h2>7. Imágenes y material de difusión</h2>
    <p>
      Durante el programa se toman fotografías y se producen materiales de la experiencia de cada participante. Al
      participar se autoriza a la Municipalidad de Esquel a usar ese material, con mención del proyecto, para difundir
      el programa y la oferta turística del destino, sin límite temporal y sin contraprestación económica.
    </p>
    <p>
      Quien no quiera que aparezcan su imagen o la de su equipo puede decirlo por escrito en cualquier momento y se
      deja de usar ese material en piezas nuevas.
    </p>

    <h2>8. Propiedad de lo que se desarrolla</h2>
    <p>
      La experiencia turística, la marca, las recetas, los productos y todo saber previo siguen siendo de quien
      participa. El programa no adquiere derechos sobre el negocio ni participación alguna en sus ingresos.
    </p>
    <p>
      Las metodologías, plantillas y materiales de trabajo que aporta el programa son de la Municipalidad de Esquel y
      quedan disponibles para que cada participante los siga usando en su proyecto.
    </p>

    <h2>9. Baja y desvinculación</h2>
    <p>
      Quien participa puede darse de baja avisando por escrito. El programa puede desvincular a un participante que
      deje de cumplir la dedicación comprometida, falte reiteradamente sin aviso, haya aportado información falsa en su
      postulación o incurra en conductas que afecten al resto de la cohorte. En ambos casos el cupo puede reasignarse.
    </p>

    <h2>10. Cambios en el cronograma</h2>
    <p>
      Las fechas publicadas —postulaciones hasta el <?= e(fecha_larga(FECHA_CIERRE)) ?>, trabajo en territorio del
      <?= e(fecha_larga(FECHA_INICIO)) ?> al <?= e(fecha_larga(FECHA_FIN)) ?>— pueden ajustarse por razones operativas
      o de fuerza mayor. Cualquier cambio se comunica por correo a los postulantes y se publica en este sitio.
      La convocatoria puede declararse desierta si no se reciben postulaciones suficientes.
    </p>

    <h2>11. Aceptación</h2>
    <p>
      Al enviar el formulario de postulación se declara haber leído y aceptado estas condiciones, y que la información
      aportada es veraz.
    </p>

    <div class="callout green">
      <span class="lbl">Dudas sobre estas condiciones</span>
      <p>Escribinos a <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a> y te respondemos antes de que te postules.</p>
    </div>

    <p class="legal-fecha">Última actualización: <?= e(fecha_larga(FECHA_APERTURA)) ?> de 2026 · Municipalidad de Esquel, Chubut.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
