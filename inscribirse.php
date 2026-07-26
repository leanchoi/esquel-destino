<?php
require_once __DIR__ . '/includes/db.php';

iniciar_sesion();

$abierta = convocatoria_abierta();
$errores = [];
$enviado = false;

// Campos del formulario. El orden refleja los pasos.
$campos = [
    'program', 'situacion',
    'name', 'contact_name', 'email', 'phone', 'ubicacion', 'antiguedad', 'redes',
    'descripcion', 'diferencial', 'visitable',
    'conexiones', 'producto_fisico', 'producto_fisico_cual',
    'recursos', 'falta',
    'motivacion', 'equipo', 'material',
];
$v = array_fill_keys($campos, '');
$v['compromiso'] = '';

// Preselección de línea desde el home (?linea=Acelera|Raiz)
if (isset($_GET['linea']) && array_key_exists($_GET['linea'], PROGRAMAS)) {
    $v['program'] = $_GET['linea'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $abierta) {
    foreach ($campos as $c) {
        $v[$c] = trim((string) ($_POST[$c] ?? ''));
    }
    $v['compromiso'] = isset($_POST['compromiso']) ? 'on' : '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $errores['general'] = 'La sesión expiró mientras completabas el formulario. Revisá los datos y volvé a enviarlo.';
    } elseif (!empty($_POST['sitio_web'])) {
        // Honeypot: campo oculto que sólo completa un bot.
        $errores['general'] = 'No pudimos procesar el envío.';
    } else {
        if (!array_key_exists($v['program'], PROGRAMAS)) {
            $errores['program'] = 'Elegí una de las dos líneas.';
        }
        if ($v['name'] === '')          $errores['name'] = 'Poné el nombre de tu proyecto.';
        if ($v['contact_name'] === '')  $errores['contact_name'] = 'Necesitamos saber con quién hablamos.';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errores['email'] = 'Revisá el correo: es por donde te vamos a contactar.';
        if ($v['phone'] === '')         $errores['phone'] = 'Dejanos un teléfono de contacto.';
        if ($v['descripcion'] === '')   $errores['descripcion'] = 'Contanos qué hacés hoy. Sin esto no podemos evaluar la propuesta.';
        if ($v['diferencial'] === '')   $errores['diferencial'] = 'Este campo es de los que más pesan en la evaluación.';
        if ($v['motivacion'] === '')    $errores['motivacion'] = 'Contanos por qué querés participar. Es el criterio de mayor peso.';
        if ($v['compromiso'] !== 'on')  $errores['compromiso'] = 'Necesitamos que confirmes la disponibilidad de 12 horas semanales.';
        if ($v['material'] !== '' && !filter_var($v['material'], FILTER_VALIDATE_URL)) {
            $errores['material'] = 'Ese enlace no parece válido. Revisalo o dejalo vacío.';
        }

        if (!$errores) {
            $pdo = db();
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO applications (name, contact_name, email, phone, program, stage)
                     VALUES (:name, :contact_name, :email, :phone, :program, 'Pendiente')"
                );
                $stmt->execute([
                    ':name' => $v['name'],
                    ':contact_name' => $v['contact_name'],
                    ':email' => $v['email'],
                    ':phone' => $v['phone'],
                    ':program' => $v['program'],
                ]);
                $appId = (int) $pdo->lastInsertId();

                // Todo lo demás va a application_details, con la clave que
                // usa ETIQUETAS_DETALLE para mostrarlo y exportarlo.
                $detalles = [
                    'situacion' => $v['situacion'],
                    'ubicacion' => $v['ubicacion'],
                    'antiguedad' => $v['antiguedad'],
                    'redes' => $v['redes'],
                    'descripcion' => $v['descripcion'],
                    'diferencial' => $v['diferencial'],
                    'visitable' => $v['visitable'],
                    'conexiones' => $v['conexiones'],
                    'producto_fisico' => $v['producto_fisico'],
                    'producto_fisico_cual' => $v['producto_fisico_cual'],
                    'recursos' => $v['recursos'],
                    'falta' => $v['falta'],
                    'motivacion' => $v['motivacion'],
                    'equipo' => $v['equipo'],
                    'material' => $v['material'],
                ];

                $ins = $pdo->prepare("INSERT INTO application_details (application_id, field_key, field_value) VALUES (?, ?, ?)");
                foreach ($detalles as $k => $val) {
                    if ($val !== '') {
                        $ins->execute([$appId, $k, $val]);
                    }
                }

                $pdo->commit();
                $enviado = true;
            } catch (PDOException $ex) {
                $pdo->rollBack();
                error_log('[esquel-lab] error guardando postulación: ' . $ex->getMessage());
                $errores['general'] = 'No pudimos guardar tu postulación. Probá de nuevo en unos minutos; si sigue fallando, escribinos a ' . EMAIL_PROGRAMA . '.';
            }
        }
    }
}

$pageTitle = 'Postulación · Esquel LAB';
$pageDescription = 'Formulario de postulación a Esquel Acelera y Raíz. Convocatoria abierta hasta el 9 de agosto de 2026.';
require __DIR__ . '/includes/header.php';
?>

<section class="form-page">
  <div class="container form-shell">

  <?php if ($enviado): ?>

    <div class="done-card">
      <div class="done-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      </div>
      <h1>Listo, ya la tenemos</h1>
      <p style="font-size:17.5px;color:var(--ink-2)">
        Tu postulación entró al proceso de evaluación. Te vamos a escribir a
        <strong><?= e($v['email']) ?></strong> con el resultado, hayas quedado o no.
      </p>
      <div class="callout" style="margin:26px 0">
        <span class="lbl">Qué sigue</span>
        <p>
          El Cuadro Técnico evalúa las postulaciones hasta el <strong><?= e(fecha_larga(FECHA_CIERRE)) ?></strong>.
          El <strong><?= e(fecha_larga(FECHA_INICIO)) ?></strong> avisamos a todos y arranca el trabajo con los proyectos seleccionados.
        </p>
      </div>
      <p style="font-size:15.5px;color:var(--ink-2)">
        Mientras tanto: si conocés a alguien que también debería postularse, pasale el dato. La convocatoria sigue abierta hasta el <?= e(fecha_larga(FECHA_CIERRE)) ?>.
      </p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
      </div>
    </div>

  <?php elseif (!$abierta): ?>

    <div class="done-card">
      <h1>La convocatoria cerró</h1>
      <p style="font-size:17px;color:var(--ink-2)">
        Las postulaciones a la primera cohorte se recibieron del <?= e(fecha_larga(FECHA_APERTURA)) ?>
        al <?= e(fecha_larga(FECHA_CIERRE)) ?> de 2026, y esa fecha de cierre ya pasó.
      </p>
      <p style="font-size:17px;color:var(--ink-2)">
        Esta es la primera cohorte de un proceso continuo: va a haber próximas convocatorias.
        Si te interesa participar, escribinos a <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a>
        y quedás en el registro para la siguiente.
      </p>
      <a href="index.php" class="btn btn-primary" style="margin-top:10px">Volver al inicio</a>
    </div>

  <?php else: ?>

    <div class="form-intro">
      <span class="eyebrow"><span class="dot"></span> Cierra el <?= e(fecha_larga(FECHA_CIERRE)) ?></span>
      <h1>Postulación</h1>
      <p>
        Son 6 pasos y te va a llevar entre 15 y 25 minutos. Cuanto más completo y pensado lo dejes,
        mejor te podemos evaluar: en varias preguntas nos importa más cómo lo contás que el dato en sí.
        Podés cerrar la pestaña y volver después, se guarda solo en este navegador.
      </p>
    </div>

    <?php if (!empty($errores['general'])): ?>
      <div class="form-alert error"><?= e($errores['general']) ?></div>
    <?php elseif ($errores): ?>
      <div class="form-alert warn">Faltan algunos datos. Te marcamos abajo cuáles.</div>
    <?php endif; ?>

    <div class="progress" id="progress" hidden>
      <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>
      <div class="progress-row">
        <p class="progress-label" id="progressLabel">Paso 1 de 6</p>
        <span class="aviso-borrador" id="avisoBorrador" role="status" aria-live="polite"></span>
      </div>
    </div>

    <form method="post" id="formPostulacion" class="form-card" novalidate data-con-errores="<?= $errores ? '1' : '0' ?>">
      <?= csrf_field() ?>
      <input type="text" name="sitio_web" tabindex="-1" autocomplete="off" class="visually-hidden" aria-hidden="true">

      <!-- ============ PASO 1 ============ -->
      <fieldset class="fstep" data-step="1">
        <legend>Paso 1 de 6</legend>
        <h2>¿Dónde está tu proyecto?</h2>
        <p class="help">De esto depende con qué equipo vas a trabajar y qué te vamos a preguntar después.</p>

        <div class="field">
          <div class="opt-grid">
            <label class="opt">
              <input type="radio" name="program" value="Acelera" <?= $v['program'] === 'Acelera' ? 'checked' : '' ?>>
              <span class="opt-t">Esquel Acelera <span class="opt-tag">Urbano</span></span>
              <span class="opt-d">En la ciudad: gastronomía, talleres, comercios, circuitos, guías, oficios.</span>
            </label>
            <label class="opt opt-raiz">
              <input type="radio" name="program" value="Raiz" <?= $v['program'] === 'Raiz' ? 'checked' : '' ?>>
              <span class="opt-t">Raíz <span class="opt-tag">Rural</span></span>
              <span class="opt-d">En el campo: chacras, estancias, crianceros, viñedos, lana, fruta fina.</span>
            </label>
          </div>
          <p class="err" data-err="program"><?= e($errores['program'] ?? '') ?></p>
        </div>

        <div class="field">
          <label class="lbl">¿En qué situación estás hoy?</label>
          <p class="hint">No hay respuesta mejor que otra. Nos sirve para saber desde dónde arrancamos.</p>
          <div class="opt-list">
            <?php
            $situaciones = [
                'Funcionando' => 'Ya estoy funcionando, pero no le vendo a turistas',
                'Turistico'   => 'Ya le vendo a turistas y quiero mejorar cómo lo hago',
                'Parado'      => 'Tuve algo andando y hoy está parado o necesita rearmarse',
                'Idea'        => 'Todavía no arranqué, tengo la idea y con qué empezar',
            ];
            foreach ($situaciones as $val => $txt): ?>
              <label class="opt">
                <input type="radio" name="situacion" value="<?= e($val) ?>" <?= $v['situacion'] === $val ? 'checked' : '' ?>>
                <span class="opt-t" style="font-size:15.5px"><?= e($txt) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </fieldset>

      <!-- ============ PASO 2 ============ -->
      <fieldset class="fstep" data-step="2">
        <legend>Paso 2 de 6</legend>
        <h2>Quién sos</h2>
        <p class="help">Datos de contacto. Todo lo marcado con asterisco es obligatorio.</p>

        <div class="field">
          <label class="lbl" for="name">Nombre de tu proyecto, emprendimiento u organización *</label>
          <input type="text" id="name" name="name" value="<?= e($v['name']) ?>" autocomplete="organization" placeholder="Ej.: Casa de Té Las Rosas, Chacra El Ñire, Cerámica del Valle">
          <p class="err" data-err="name"><?= e($errores['name'] ?? '') ?></p>
        </div>

        <div class="field">
          <label class="lbl" for="contact_name">Tu nombre y apellido *</label>
          <input type="text" id="contact_name" name="contact_name" value="<?= e($v['contact_name']) ?>" autocomplete="name">
          <p class="err" data-err="contact_name"><?= e($errores['contact_name'] ?? '') ?></p>
        </div>

        <div class="field-row">
          <div class="field">
            <label class="lbl" for="email">Correo electrónico *</label>
            <input type="email" id="email" name="email" value="<?= e($v['email']) ?>" autocomplete="email" inputmode="email" spellcheck="false">
            <p class="err" data-err="email"><?= e($errores['email'] ?? '') ?></p>
          </div>
          <div class="field">
            <label class="lbl" for="phone">Teléfono o WhatsApp *</label>
            <input type="tel" id="phone" name="phone" value="<?= e($v['phone']) ?>" autocomplete="tel" inputmode="tel" placeholder="2945 123456">
            <p class="err" data-err="phone"><?= e($errores['phone'] ?? '') ?></p>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label class="lbl" for="ubicacion">¿Dónde está?</label>
            <input type="text" id="ubicacion" name="ubicacion" value="<?= e($v['ubicacion']) ?>" placeholder="Barrio, paraje o zona">
          </div>
          <div class="field">
            <label class="lbl" for="antiguedad">¿Hace cuánto estás en esto?</label>
            <input type="text" id="antiguedad" name="antiguedad" value="<?= e($v['antiguedad']) ?>" placeholder="Ej.: 3 años, recién arranco, toda la vida">
          </div>
        </div>

        <div class="field">
          <label class="lbl" for="redes">Redes sociales o página web</label>
          <p class="hint">Si no tenés, dejalo vacío. No es un requisito.</p>
          <input type="text" id="redes" name="redes" value="<?= e($v['redes']) ?>" placeholder="@tuusuario">
        </div>
      </fieldset>

      <!-- ============ PASO 3 ============ -->
      <fieldset class="fstep" data-step="3">
        <legend>Paso 3 de 6</legend>
        <h2>Qué hacés y qué te hace distinto</h2>
        <p class="help">Este es el paso que más pesa en la evaluación. Tomate el tiempo: se puntúa lo que contás acá.</p>

        <div class="field">
          <label class="lbl" for="descripcion">Contanos qué hacés hoy *</label>
          <p class="hint">Qué ofrecés, cómo es tu lugar, a quién le vendés ahora. No hace falta que ya sea turismo.</p>
          <textarea id="descripcion" name="descripcion"><?= e($v['descripcion']) ?></textarea>
          <p class="err" data-err="descripcion"><?= e($errores['descripcion'] ?? '') ?></p>
        </div>

        <div class="field">
          <label class="lbl" for="diferencial">¿Qué tiene tu propuesta que no tenga ninguna otra en Esquel? *</label>
          <p class="hint">Contanos la historia, no solo el dato. Un saber familiar, una receta, un lugar, una forma de hacer las cosas.</p>
          <textarea id="diferencial" name="diferencial"><?= e($v['diferencial']) ?></textarea>
          <p class="err" data-err="diferencial"><?= e($errores['diferencial'] ?? '') ?></p>
        </div>

        <div class="field">
          <label class="lbl" for="visitable">¿Qué podría ver, hacer o probar un visitante?</label>
          <p class="hint">Ej.: ver la esquila, amasar, recorrer la chacra, participar de la cosecha, entrar al taller.</p>
          <textarea id="visitable" name="visitable" rows="4"><?= e($v['visitable']) ?></textarea>
        </div>
      </fieldset>

      <!-- ============ PASO 4 ============ -->
      <fieldset class="fstep" data-step="4">
        <legend>Paso 4 de 6</legend>
        <h2>Cómo se conecta con el resto de Esquel</h2>
        <p class="help">Nos interesa que tu propuesta sume a la oferta del destino, no que quede aislada.</p>

        <div class="field">
          <label class="lbl" for="conexiones">¿Con qué otros lugares, personas o negocios de Esquel se podría conectar?</label>
          <p class="hint">Otros prestadores, agencias, alojamientos, productores. Si trabajás con alguien hoy, contanos con quién.</p>
          <textarea id="conexiones" name="conexiones" rows="4"><?= e($v['conexiones']) ?></textarea>
        </div>

        <div class="field">
          <label class="lbl">¿Hay algún producto físico que el visitante podría llevarse?</label>
          <p class="hint">Un dulce, una madeja, una pieza, una conserva. Es lo que llamamos “Economía de los Recuerdos”. No es obligatorio.</p>
          <div class="opt-list">
            <?php foreach ([
                'Si' => 'Sí, ya tengo algo así',
                'Podria' => 'Todavía no, pero podría desarrollarlo',
                'No' => 'No lo veo por ahora',
            ] as $val => $txt): ?>
              <label class="opt">
                <input type="radio" name="producto_fisico" value="<?= e($val) ?>" <?= $v['producto_fisico'] === $val ? 'checked' : '' ?>>
                <span class="opt-t" style="font-size:15.5px"><?= e($txt) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label class="lbl" for="producto_fisico_cual">Si marcaste “sí” o “podría”, contanos cuál</label>
          <textarea id="producto_fisico_cual" name="producto_fisico_cual" rows="3"><?= e($v['producto_fisico_cual']) ?></textarea>
        </div>
      </fieldset>

      <!-- ============ PASO 5 ============ -->
      <fieldset class="fstep" data-step="5">
        <legend>Paso 5 de 6</legend>
        <h2>Con qué contás hoy</h2>
        <p class="help">El programa prioriza propuestas que puedan salir a la venta sin necesitar una gran inversión.</p>

        <div class="field">
          <label class="lbl" for="recursos">¿Con qué contás para arrancar?</label>
          <p class="hint">Espacio, herramientas, vehículo, equipo de gente, materia prima, tiempo.</p>
          <textarea id="recursos" name="recursos" rows="4"><?= e($v['recursos']) ?></textarea>
        </div>

        <div class="field">
          <label class="lbl" for="falta">¿Qué te falta para poder vender esto?</label>
          <p class="hint">Sé concreto. Saber qué falta es la mitad del trabajo.</p>
          <textarea id="falta" name="falta" rows="4"><?= e($v['falta']) ?></textarea>
        </div>
      </fieldset>

      <!-- ============ PASO 6 ============ -->
      <fieldset class="fstep" data-step="6">
        <legend>Paso 6 de 6 · El criterio que más pesa</legend>
        <h2>Por qué vos</h2>
        <p class="help">
          Las ganas y el compromiso pesan tanto como cualquier criterio técnico, a veces más.
          Una propuesta simple con alguien que le pone todo llega más lejos que una propuesta redonda sin nadie que la empuje.
        </p>

        <div class="field">
          <label class="lbl" for="motivacion">¿Por qué querés participar y qué esperás lograr en estas ocho semanas? *</label>
          <textarea id="motivacion" name="motivacion" rows="6"><?= e($v['motivacion']) ?></textarea>
          <p class="err" data-err="motivacion"><?= e($errores['motivacion'] ?? '') ?></p>
        </div>

        <div class="field">
          <label class="lbl" for="equipo">¿Quiénes participarían del proceso además de vos?</label>
          <input type="text" id="equipo" name="equipo" value="<?= e($v['equipo']) ?>" placeholder="Yo solo/a, un socio, mi familia, dos empleados…">
        </div>

        <div class="field">
          <label class="lbl" for="material">Si tenés fotos o material para mostrar, pegá un enlace</label>
          <p class="hint">Drive, Instagram, lo que tengas. Opcional.</p>
          <input type="url" id="material" name="material" value="<?= e($v['material']) ?>" placeholder="https://">
          <p class="err" data-err="material"><?= e($errores['material'] ?? '') ?></p>
        </div>

        <div class="field" style="margin-top:28px">
          <label class="check">
            <input type="checkbox" name="compromiso" <?= $v['compromiso'] === 'on' ? 'checked' : '' ?>>
            <span>
              Me comprometo a dedicar un mínimo de <strong>12 horas semanales</strong> al proceso entre el
              <?= e(fecha_larga(FECHA_INICIO)) ?> y el <?= e(fecha_larga(FECHA_FIN)) ?> de 2026, en caso de ser seleccionado. *
            </span>
          </label>
          <p class="err" data-err="compromiso"><?= e($errores['compromiso'] ?? '') ?></p>
        </div>

        <div class="aviso-legal">
          <p>
            Al enviar esta postulación aceptás los
            <a href="terminos.php" target="_blank" rel="noopener">términos y condiciones de participación</a>,
            que incluyen cómo se selecciona, qué se compromete cada participante y cómo se tratan tus datos personales.
            Se abren en una pestaña nueva: no vas a perder lo que cargaste.
          </p>
        </div>

        <div id="revision" hidden>
          <h3 style="font-size:19px;margin:30px 0 12px">Antes de enviar, un repaso</h3>
          <dl class="review-list" id="revisionLista"></dl>
        </div>
      </fieldset>

      <div class="form-nav">
        <button type="button" class="btn btn-secondary" id="btnPrev" hidden>← Anterior</button>
        <span class="spacer"></span>
        <button type="button" class="btn btn-primary" id="btnNext" hidden>Siguiente →</button>
        <button type="submit" class="btn btn-primary" id="btnSubmit">Enviar postulación</button>
      </div>
    </form>

  <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
