<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/graficos.php';

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

// ------------------------------------------------------------------ actividad
//
// Dos fuentes, y conviene saber por qué.
//
// login_attempts existe desde la primera versión: guarda cada intento de
// ingreso, con su fecha y si salió bien. De ahí salen los ingresos y los
// fallidos, con todo el historial.
//
// sesiones_panel es nueva y es la única que sabe cuánto duró cada visita, así
// que el tiempo en el panel sólo existe desde que se publicó esta versión. Se
// mide entre el ingreso y la última pantalla que se abrió, con lo cual no
// cuenta el rato que alguien pasa leyendo la última antes de irse: es un piso.
$offset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime('now'));
$LOCAL = sprintf('%+d seconds', $offset);

$dias = (int) ($_GET['dias'] ?? 30);
if (!in_array($dias, [7, 30, 90, 0], true)) {
    $dias = 30;
}
$desdeAct = $dias === 0 ? '1970-01-01' : date('Y-m-d', strtotime('-' . ($dias - 1) . ' days'));
$hoyAct = date('Y-m-d');

$ingresos = $pdo->prepare(
    "SELECT username, COUNT(*) n, MAX(created_at) ultimo
       FROM login_attempts WHERE ok = 1 AND date(created_at, ?) BETWEEN ? AND ?
      GROUP BY username"
);
$ingresos->execute([$LOCAL, $desdeAct, $hoyAct]);
$porUsuario = [];
foreach ($ingresos->fetchAll() as $r) {
    $porUsuario[$r['username']] = ['ingresos' => (int) $r['n'], 'ultimo' => $r['ultimo'], 'fallidos' => 0,
                                   'segundos' => 0, 'sesiones' => 0, 'pantallas' => 0];
}

$fallidos = $pdo->prepare(
    "SELECT username, COUNT(*) n FROM login_attempts
      WHERE ok = 0 AND date(created_at, ?) BETWEEN ? AND ? GROUP BY username"
);
$fallidos->execute([$LOCAL, $desdeAct, $hoyAct]);
foreach ($fallidos->fetchAll() as $r) {
    if (!isset($porUsuario[$r['username']])) {
        $porUsuario[$r['username']] = ['ingresos' => 0, 'ultimo' => null, 'fallidos' => 0,
                                       'segundos' => 0, 'sesiones' => 0, 'pantallas' => 0];
    }
    $porUsuario[$r['username']]['fallidos'] = (int) $r['n'];
}

$tiempos = $pdo->prepare(
    "SELECT username, COUNT(*) sesiones, SUM(pantallas) pantallas,
            SUM(strftime('%s', ultima_actividad) - strftime('%s', inicio)) segundos
       FROM sesiones_panel WHERE date(inicio, ?) BETWEEN ? AND ? GROUP BY username"
);
$tiempos->execute([$LOCAL, $desdeAct, $hoyAct]);
foreach ($tiempos->fetchAll() as $r) {
    if (!isset($porUsuario[$r['username']])) {
        continue;
    }
    $porUsuario[$r['username']]['segundos']  = max(0, (int) $r['segundos']);
    $porUsuario[$r['username']]['sesiones']  = (int) $r['sesiones'];
    $porUsuario[$r['username']]['pantallas'] = (int) $r['pantallas'];
}

// Cada ingreso como un punto: fecha y hora exacta, para el gráfico de momentos.
$puntos = $pdo->prepare(
    "SELECT username, date(created_at, ?) fecha,
            CAST(strftime('%H', created_at, ?) AS INTEGER)
              + CAST(strftime('%M', created_at, ?) AS INTEGER) / 60.0 hora,
            datetime(created_at, ?) cuando
       FROM login_attempts WHERE ok = 1 AND date(created_at, ?) BETWEEN ? AND ?
      ORDER BY created_at"
);
$puntos->execute([$LOCAL, $LOCAL, $LOCAL, $LOCAL, $LOCAL, $desdeAct, $hoyAct]);
$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$eventos = [];
$anterior = [];
foreach ($puntos->fetchAll() as $r) {
    $h = (float) $r['hora'];
    $ts = strtotime($r['cuando']);
    $lineas = [date('H:i', $ts) . ' del ' . strtolower($diasSemana[(int) date('w', $ts)]) . ' ' . fecha_larga($r['fecha'])];
    // La franja del día en palabras: leer "23:40" y leer "de madrugada" no
    // cuesta lo mismo cuando lo que se busca es el patrón.
    $lineas[] = $h < 6 ? 'De madrugada' : ($h < 12 ? 'A la mañana' : ($h < 19 ? 'A la tarde' : 'A la noche'));
    if (isset($anterior[$r['username']])) {
        $d = (int) round(($ts - $anterior[$r['username']]) / 86400);
        $lineas[] = $d <= 0 ? 'Volvió a entrar el mismo día'
                  : ($d === 1 ? 'Un día después del anterior' : $d . ' días después del anterior');
    } else {
        $lineas[] = 'Primer ingreso del período';
    }
    $anterior[$r['username']] = $ts;

    $eventos[] = [
        'fecha'  => $r['fecha'],
        'hora'   => $h,
        'quien'  => $r['username'],
        'titulo' => $r['username'],
        'lineas' => $lineas,
    ];
}
$primerEvento = $eventos ? min(array_column($eventos, 'fecha')) : $hoyAct;

/** "2 h 14 min", "8 min", "—". Los segundos sueltos no le importan a nadie acá. */
function duracion(int $s): string
{
    if ($s <= 0) {
        return '—';
    }
    if ($s < 60) {
        return $s . ' s';
    }
    $m = (int) round($s / 60);
    return $m < 60 ? $m . ' min' : intdiv($m, 60) . ' h ' . ($m % 60 ? ($m % 60) . ' min' : '');
}

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
        <table class="crm-table tabla-usuarios">
          <thead><tr><th>Usuario</th><th>Rol</th><th>Vota</th><th>Estado</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($usuarios as $usr): ?>
              <tr>
                <td><strong><?= e($usr['username']) ?></strong><?= (int) $usr['id'] === (int) $u['id'] ? ' <span class="sub">(vos)</span>' : '' ?></td>
                <td data-col="Rol">
                  <?php if ((int) $usr['id'] === (int) $u['id']): ?>
                    <span class="chip" style="--c:var(--berry)"><?= e(rol_label($usr['role'])) ?></span>
                  <?php else: ?>
                    <form method="post" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="accion" value="rol">
                      <input type="hidden" name="id" value="<?= (int) $usr['id'] ?>">
                      <select name="role" onchange="this.form.submit()" aria-label="Rol de <?= e($usr['username']) ?>">
                        <?php foreach (ROLES_INFO as $r => $info): ?>
                          <option value="<?= e($r) ?>" <?= $usr['role'] === $r ? 'selected' : '' ?>><?= e($info['label']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php endif; ?>
                </td>
                <td data-col="Vota"><?= !empty(ROLES_INFO[$usr['role']]['vota'])
                      ? '<span class="jurado-sello is-completo"><span class="js-n">sí</span></span>'
                      : '<span class="sub">no</span>' ?></td>
                <td class="sub" data-col="Estado"><?= $usr['must_change_password'] ? 'Contraseña provisoria' : 'Activo' ?></td>
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
            <?php foreach (ROLES_INFO as $r => $info): ?>
              <option value="<?= e($r) ?>" <?= $r === 'editor' ? 'selected' : '' ?>><?= e($info['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Crear usuario</button>
        <p class="hint" style="margin-top:12px">Se genera una contraseña provisoria que vas a ver una sola vez, acá. La persona tiene que cambiarla en su primer ingreso.</p>
      </form>

      <h2 class="panel-title" style="margin-top:28px">Qué puede cada uno</h2>
      <dl class="roles-lista">
        <?php foreach (ROLES_INFO as $r => $info): ?>
          <div>
            <dt>
              <?= e($info['label']) ?>
              <?php if (!empty($info['vota'])): ?><span class="rol-vota">vota</span><?php endif; ?>
            </dt>
            <dd><?= e($info['ayuda']) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
      <p class="hint">
        El jurado se arma solo con los evaluadores: si sumás uno nuevo, aparece como
        pendiente en todas las postulaciones, también en las que ya tenían votos.
      </p>
    </div>
  </div>

  <!-- ---------------- actividad ---------------- -->
  <div class="hoy-head" style="margin:34px 0 6px">
    <div>
      <h2 class="panel-title" style="font-size:20px;margin:0">Quién entra al panel</h2>
      <p class="hint" style="margin:2px 0 0">
        <?= $dias === 0 ? 'Todo el historial' : 'Últimos ' . $dias . ' días' ?>,
        en hora de Esquel.
      </p>
    </div>
    <div class="rango">
      <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días', 0 => 'Todo'] as $d => $lbl): ?>
        <a href="?dias=<?= $d ?>" class="<?= $dias === $d ? 'is-active' : '' ?>"><?= e($lbl) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="panel">
    <?php if (!$porUsuario): ?>
      <p class="hint" style="margin:0">Nadie entró al panel en este período.</p>
    <?php else: ?>
      <div class="actividad">
        <?php
        // Ordenados por quién usa más el panel, que es lo que se viene a ver.
        uasort($porUsuario, fn($a, $b) => $b['ingresos'] <=> $a['ingresos']);
        foreach ($porUsuario as $nombre => $d):
          $rol = null;
          foreach ($usuarios as $usr) {
              if ($usr['username'] === $nombre) { $rol = $usr['role']; break; }
          }
          $promedio = $d['sesiones'] > 0 ? (int) round($d['segundos'] / $d['sesiones']) : 0;
        ?>
          <article class="act-card">
            <header>
              <span class="act-nombre"><?= e($nombre) ?></span>
              <span class="act-rol"><?= $rol === null ? 'ya no existe' : e(rol_label($rol)) ?></span>
            </header>
            <dl class="act-datos">
              <div><dt>Ingresos</dt><dd><?= $d['ingresos'] ?></dd></div>
              <div><dt>Tiempo total</dt><dd><?= e(duracion($d['segundos'])) ?></dd></div>
              <div><dt>Por sesión</dt><dd><?= e(duracion($promedio)) ?></dd></div>
              <div><dt>Pantallas</dt><dd><?= $d['pantallas'] ?: '—' ?></dd></div>
            </dl>
            <p class="act-pie">
              <?php if ($d['ultimo']): ?>
                Último ingreso: <?= e(fecha_corta(date('Y-m-d H:i:s', strtotime($d['ultimo']) + $offset), true)) ?>
              <?php else: ?>
                Sin ingresos logrados en el período
              <?php endif; ?>
              <?php if ($d['fallidos'] > 0): ?>
                <span class="act-fallidos"><?= $d['fallidos'] ?> <?= $d['fallidos'] === 1 ? 'intento fallido' : 'intentos fallidos' ?></span>
              <?php endif; ?>
            </p>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($eventos): ?>
        <h3 class="panel-title" style="margin-top:28px">Cada ingreso, en su momento</h3>
        <p class="hint" style="margin-top:-8px">
          Una franja por persona: la fecha va a lo ancho y la hora del día a lo alto,
          con medianoche abajo. Sirve para ver en qué momento trabaja cada uno, que es
          justo lo que un promedio esconde.
        </p>
        <?= svg_momentos($eventos, $primerEvento, $hoyAct) ?>
      <?php endif; ?>

      <p class="hint" style="margin-top:20px">
        Los ingresos salen del registro de accesos, que existe desde la primera versión del panel.
        El <strong>tiempo</strong> se empezó a medir recién ahora, y va desde el ingreso hasta la
        última pantalla que se abrió: no cuenta el rato que alguien pasa leyendo la última antes
        de irse, así que es un piso y no un cronómetro.
      </p>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
