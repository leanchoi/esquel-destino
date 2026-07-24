<?php
/**
 * Cambio de contraseña. Es la única pantalla accesible mientras el usuario
 * tenga la contraseña provisoria marcada para cambio obligatorio.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

iniciar_sesion();
$usuario = usuario_actual();
if (!$usuario) {
    redirect('login.php');
}

$error = '';
$ok = false;
$obligatorio = !empty($usuario['must_change']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión expiró. Volvé a intentar.';
    } else {
        $actual  = (string) ($_POST['actual'] ?? '');
        $nueva   = (string) ($_POST['nueva'] ?? '');
        $repetir = (string) ($_POST['repetir'] ?? '');

        $stmt = db()->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$usuario['id']]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($actual, $hash)) {
            $error = 'La contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 8) {
            $error = 'La contraseña nueva tiene que tener al menos 8 caracteres.';
        } elseif ($nueva === $actual) {
            $error = 'La contraseña nueva tiene que ser distinta de la actual.';
        } elseif ($nueva !== $repetir) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } else {
            db()->prepare('UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?')
                ->execute([password_hash($nueva, PASSWORD_DEFAULT), $usuario['id']]);
            $_SESSION['admin_user']['must_change'] = false;
            $obligatorio = false;
            $ok = true;
        }
    }
}

$pageTitle = 'Cambiar contraseña';
require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Cambiar contraseña</h1>
</div>

<div class="admin-content" style="max-width:520px">
  <?php if ($ok): ?>
    <div class="panel">
      <div class="form-alert" style="background:var(--green-soft);color:var(--green-ink);border:1px solid rgba(35,111,76,.3)">
        Listo, tu contraseña se actualizó.
      </div>
      <a href="dashboard.php" class="btn btn-primary">Ir al panel</a>
    </div>
  <?php else: ?>
    <?php if ($obligatorio): ?>
      <div class="form-alert warn">
        Estás usando la contraseña provisoria. Cambiala antes de seguir: el panel tiene datos de contacto de los postulantes.
      </div>
    <?php endif; ?>

    <form method="post" class="panel" novalidate>
      <?= csrf_field() ?>
      <?php if ($error): ?><div class="form-alert error"><?= e($error) ?></div><?php endif; ?>
      <div class="field">
        <label class="lbl" for="actual">Contraseña actual</label>
        <input type="password" id="actual" name="actual" autocomplete="current-password" required>
      </div>
      <div class="field">
        <label class="lbl" for="nueva">Contraseña nueva</label>
        <p class="hint">Mínimo 8 caracteres.</p>
        <input type="password" id="nueva" name="nueva" autocomplete="new-password" required>
      </div>
      <div class="field">
        <label class="lbl" for="repetir">Repetir contraseña nueva</label>
        <input type="password" id="repetir" name="repetir" autocomplete="new-password" required>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
