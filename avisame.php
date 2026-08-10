<?php
/**
 * "Avisame cuando abra la próxima."
 *
 * Con la primera cohorte cerrada, lo único que el sitio puede pedirle a alguien
 * que llega tarde es el correo. Este archivo hace las tres cosas:
 *
 *   - Responde en JSON cuando lo llama el pop-up o la sección de la home.
 *   - Acepta el mismo formulario como envío común, para quien no tiene
 *     JavaScript. Ahí no hay JSON: hay redirección y una página que agradece.
 *   - Sirve como página propia, para poder mandar el enlace suelto por
 *     WhatsApp o ponerlo en una publicación.
 *
 * Es una lista de espera, no una postulación: se pide lo mínimo. Cada campo de
 * más es gente que abandona a mitad, y acá lo único imprescindible es poder
 * volver a escribirle.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

iniciar_sesion();

$esJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$errores = [];
$listo = isset($_GET['listo']);
$v = ['nombre' => '', 'email' => '', 'telefono' => '', 'linea' => '', 'instagram' => '', 'cuenta' => ''];

/** Devuelve JSON y corta, para las llamadas del pop-up. */
function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($datos, JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($v) as $c) {
        $v[$c] = trim((string) ($_POST[$c] ?? ''));
    }
    $origen = (string) ($_POST['origen'] ?? 'web');

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta demasiado tiempo. Recargala y probá de nuevo.';
    } elseif (!empty($_POST['sitio_web'])) {
        // Trampa antispam: campo invisible que sólo completa un robot. No se
        // le avisa que lo detectamos, se le agradece y no se guarda nada.
        if ($esJson) {
            responder(['ok' => true]);
        }
        redirect('avisame.php?listo=1');
    } else {
        if ($v['nombre'] === '') {
            $errores['nombre'] = 'Decinos cómo te llamás.';
        }
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Revisá el correo: es por donde te vamos a avisar.';
        }
        // El handle se guarda sin arroba y sin la URL entera: la gente pega
        // "instagram.com/loquesea/" o "@loquesea" y hay que poder buscarlo.
        $v['instagram'] = preg_replace('~^.*instagram\.com/~i', '', $v['instagram']);
        $v['instagram'] = trim((string) preg_replace('/[^A-Za-z0-9._]/', '', $v['instagram']));

        foreach (['nombre' => 120, 'telefono' => 40, 'instagram' => 40, 'cuenta' => 1200] as $campo => $tope) {
            if (mb_strlen($v[$campo]) > $tope) {
                $v[$campo] = mb_substr($v[$campo], 0, $tope);
            }
        }
        if (!array_key_exists($v['linea'], PROGRAMAS) && $v['linea'] !== '') {
            $v['linea'] = '';
        }
    }

    if (!$errores) {
        try {
            // El correo repetido no duplica: actualiza lo que dejó y suma una
            // vuelta al contador. Alguien que insiste es alguien más
            // interesado, y eso conviene saberlo cuando se abra la próxima.
            db()->prepare(
                "INSERT INTO interesados (nombre, email, telefono, linea, instagram, cuenta, origen, veces, created_at, updated_at)
                 VALUES (:nombre, :email, :telefono, :linea, :instagram, :cuenta, :origen, 1, datetime('now'), datetime('now'))
                 ON CONFLICT (email) DO UPDATE SET
                   nombre = excluded.nombre,
                   telefono = CASE WHEN excluded.telefono <> '' THEN excluded.telefono ELSE interesados.telefono END,
                   linea = CASE WHEN excluded.linea <> '' THEN excluded.linea ELSE interesados.linea END,
                   instagram = CASE WHEN excluded.instagram <> '' THEN excluded.instagram ELSE interesados.instagram END,
                   cuenta = CASE WHEN excluded.cuenta <> '' THEN excluded.cuenta ELSE interesados.cuenta END,
                   veces = interesados.veces + 1,
                   updated_at = datetime('now')"
            )->execute([
                ':nombre'   => $v['nombre'],
                ':email'    => mb_strtolower($v['email']),
                ':telefono' => $v['telefono'],
                ':linea'    => $v['linea'],
                ':instagram' => $v['instagram'],
                ':cuenta'   => $v['cuenta'],
                ':origen'   => mb_substr($origen, 0, 30),
            ]);

            if ($esJson) {
                responder(['ok' => true, 'nombre' => $v['nombre']]);
            }
            redirect('avisame.php?listo=1');
        } catch (PDOException $ex) {
            error_log('[esquel-lab] error guardando interesado: ' . $ex->getMessage());
            $errores['general'] = 'No pudimos guardarlo. Probá de nuevo en un rato.';
        }
    }

    if ($esJson) {
        responder(['ok' => false, 'errores' => $errores], 422);
    }
}

$pageTitle = 'Avisame de la próxima cohorte · Esquel LAB';
$pageDescription = 'La primera cohorte de Esquel LAB ya está en marcha. Dejanos tus datos y te avisamos cuando abra la próxima convocatoria.';
$activeNav = '';
require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:680px">
    <?php if ($listo): ?>
      <span class="eyebrow"><span class="dot"></span> Listo</span>
      <h1>Te anotamos</h1>
      <p class="hero-lede">
        Cuando abra la próxima cohorte te escribimos. Si mientras tanto aparece algo que le sirva
        a tu proyecto, también te lo contamos.
      </p>
      <p><a href="index.php" class="btn btn-secondary btn-lg">Volver al inicio</a></p>
    <?php else: ?>
      <span class="eyebrow"><span class="dot"></span> <?= e(PROXIMA_COHORTE) ?></span>
      <h1>Avisame cuando abra la próxima</h1>
      <p class="hero-lede">
        La primera cohorte cerró el <?= e(fecha_larga(FECHA_CIERRE)) ?> y ya está trabajando.
        Dejanos por dónde encontrarte y te avisamos apenas se abra la siguiente.
      </p>

      <?php if (!empty($errores['general'])): ?>
        <div class="form-alert error"><?= e($errores['general']) ?></div>
      <?php endif; ?>

      <form method="post" action="avisame.php" class="form-avisame">
        <?= csrf_field() ?>
        <input type="hidden" name="origen" value="pagina">
        <?= campos_avisame($v, $errores, 'pag') ?>
        <button type="submit" class="btn btn-primary btn-lg">Avisame</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
