<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$u = requiere_rol('admin');
$pdo = db();

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $msg = ['tipo' => 'error', 'texto' => 'La sesión expiró. Volvé a intentar.'];
    } else {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear') {
            $nuevo = trim((string) ($_POST['username'] ?? ''));
            $rol   = (string) ($_POST['role'] ?? 'viewer');

            if ($nuevo === '' || !array_key_exists($rol, ROLES)) {
                $msg = ['tipo' => 'error', 'texto' => 'Completá el nombre de usuario y elegí un rol.'];
            } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $nuevo)) {
                $msg = ['tipo' => 'error', 'texto' => 'El usuario admite entre 3 y 30 caracteres: letras, números, punto, guión y guión bajo.'];
            } else {
                $provisoria = bin2hex(random_bytes(5));
                try {
                    // created_at explícito: en bases migradas la columna se
                    // agregó con ALTER TABLE y SQLite no acepta ahí un default
                    // CURRENT_TIMESTAMP, así que sin esto quedaría en NULL.
                    $pdo->prepare("INSERT INTO users (username, password, role, must_change_password, created_at) VALUES (?, ?, ?, 1, datetime('now'))")
                        ->execute([$nuevo, password_hash($provisoria, PASSWORD_DEFAULT), $rol]);
                    // Las llaves no son decorativas: PHP acepta bytes UTF-8 en los
                    // nombres de variable, así que "$nuevo»" busca la variable
                    // $nuevo» y el nombre del usuario desaparecía del mensaje.
                    $msg = ['tipo' => 'ok', 'texto' => "Usuario «{$nuevo}» creado. Contraseña provisoria: {$provisoria} — pasásela y va a tener que cambiarla al entrar."];
                } catch (PDOException $ex) {
                    $msg = ['tipo' => 'error', 'texto' => 'Ese nombre de usuario ya existe.'];
                }
            }
        } elseif ($accion === 'rol') {
            $id  = (int) ($_POST['id'] ?? 0);
            $rol = (string) ($_POST['role'] ?? '');
            if ($id === (int) $u['id']) {
                $msg = ['tipo' => 'error', 'texto' => 'No podés cambiarte el rol a vos mismo.'];
            } elseif (array_key_exists($rol, ROLES)) {
                $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$rol, $id]);
                $msg = ['tipo' => 'ok', 'texto' => 'Rol actualizado.'];
            }
        } elseif ($accion === 'reset') {
            $id = (int) ($_POST['id'] ?? 0);
            $provisoria = bin2hex(random_bytes(5));
            $pdo->prepare('UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?')
                ->execute([password_hash($provisoria, PASSWORD_DEFAULT), $id]);
            $msg = ['tipo' => 'ok', 'texto' => "Contraseña reseteada. Nueva provisoria: $provisoria"];
        } elseif ($accion === 'borrar') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id === (int) $u['id']) {
                $msg = ['tipo' => 'error', 'texto' => 'No podés eliminar tu propio usuario.'];
            } else {
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                $msg = ['tipo' => 'ok', 'texto' => 'Usuario eliminado.'];
            }
        }
    }
}

$usuarios = $pdo->query('SELECT id, username, role, must_change_password, created_at FROM users ORDER BY created_at ASC')->fetchAll();

$pageTitle = 'Usuarios';
$nav = 'usuarios';
require __DIR__ . '/_header.php';
?>

<div class="admin-topbar"><h1>Usuarios del panel</h1></div>

<div class="admin-content">
  <?php if ($msg): ?>
    <div class="form-alert <?= $msg['tipo'] === 'ok' ? '' : 'error' ?>"
         <?= $msg['tipo'] === 'ok' ? 'style="background:var(--green-soft);color:var(--green-ink);border:1px solid rgba(35,111,76,.3)"' : '' ?>>
      <?= e($msg['texto']) ?>
    </div>
  <?php endif; ?>

  <div class="cols-2">
    <div class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($usuarios as $usr): ?>
              <tr>
                <td><strong><?= e($usr['username']) ?></strong><?= (int) $usr['id'] === (int) $u['id'] ? ' <span class="sub">(vos)</span>' : '' ?></td>
                <td>
                  <?php if ((int) $usr['id'] === (int) $u['id']): ?>
                    <span class="chip" style="--c:var(--berry)"><?= e($usr['role']) ?></span>
                  <?php else: ?>
                    <form method="post" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="accion" value="rol">
                      <input type="hidden" name="id" value="<?= (int) $usr['id'] ?>">
                      <select name="role" onchange="this.form.submit()">
                        <?php foreach (array_keys(ROLES) as $r): ?>
                          <option value="<?= e($r) ?>" <?= $usr['role'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php endif; ?>
                </td>
                <td class="sub"><?= $usr['must_change_password'] ? 'Contraseña provisoria' : 'Activo' ?></td>
                <td class="right nowrap">
                  <form method="post" class="inline" onsubmit="return confirm('¿Resetear la contraseña de <?= e($usr['username']) ?>?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="reset">
                    <input type="hidden" name="id" value="<?= (int) $usr['id'] ?>">
                    <button class="btn btn-secondary btn-sm">Resetear</button>
                  </form>
                  <?php if ((int) $usr['id'] !== (int) $u['id']): ?>
                    <form method="post" class="inline" onsubmit="return confirm('¿Eliminar a <?= e($usr['username']) ?>?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="accion" value="borrar">
                      <input type="hidden" name="id" value="<?= (int) $usr['id'] ?>">
                      <button class="btn btn-secondary btn-sm danger">Eliminar</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel">
      <h2 class="panel-title">Agregar usuario</h2>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="crear">
        <div class="field">
          <label class="lbl" for="username">Nombre de usuario</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="field">
          <label class="lbl" for="role">Rol</label>
          <select id="role" name="role">
            <option value="viewer">viewer — solo lectura y descarga</option>
            <option value="editor">editor — evalúa y cambia estados</option>
            <option value="admin">admin — todo, incluidos usuarios</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Crear usuario</button>
        <p class="hint" style="margin-top:12px">Se genera una contraseña provisoria que vas a ver una sola vez, acá. La persona tiene que cambiarla en su primer ingreso.</p>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
