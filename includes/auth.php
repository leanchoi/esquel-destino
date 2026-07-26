<?php
/**
 * Autenticación del panel /admin.
 * Roles: admin (todo) > editor (evalúa) > viewer (solo lectura).
 */

require_once __DIR__ . '/db.php';

const ROLES = ['viewer' => 1, 'editor' => 2, 'admin' => 3];

function usuario_actual(): ?array
{
    iniciar_sesion();
    return $_SESSION['admin_user'] ?? null;
}

function requiere_login(): array
{
    $u = usuario_actual();
    if (!$u) {
        redirect('login.php');
    }
    // Nadie opera el panel con la contraseña provisoria todavía puesta.
    if (!empty($u['must_change']) && basename($_SERVER['SCRIPT_NAME']) !== 'password.php') {
        redirect('password.php');
    }
    return $u;
}

function requiere_rol(string $minimo): array
{
    $u = requiere_login();
    if ((ROLES[$u['role']] ?? 0) < (ROLES[$minimo] ?? 99)) {
        pagina_sin_permiso($u, $minimo);
    }
    return $u;
}

/**
 * Página de "no tenés permiso".
 *
 * Antes era un exit() con una línea de texto pelado sobre fondo blanco, que
 * no se distingue de un sitio roto. Ahora dice con qué usuario entraste, qué
 * rol tenés, cuál hace falta y cómo salir de ahí.
 */
function pagina_sin_permiso(array $u, string $minimo): void
{
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    $usuario = e((string) ($u['username'] ?? '—'));
    $rol     = e((string) ($u['role'] ?? '—'));
    $falta   = e($minimo);
    $css     = e(asset('assets/css/style.css'));
    echo <<<HTML
<!doctype html>
<html lang="es"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sin permiso · Panel Esquel LAB</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="../{$css}">
</head><body>
<main class="container" style="max-width:560px;padding-top:72px;padding-bottom:72px">
  <h1 style="font-size:30px">Esta sección es solo para administradores</h1>
  <p style="color:var(--ink-2)">
    Entraste como <strong>{$usuario}</strong>, con rol <strong>{$rol}</strong>.
    Para abrir esta página hace falta el rol <strong>{$falta}</strong>.
  </p>
  <p style="color:var(--ink-2)">
    Si tendrías que tener acceso, pedile a un administrador que te cambie el rol desde <em>Usuarios</em>.
  </p>
  <p style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
    <a class="btn btn-primary" href="dashboard.php">Volver a las postulaciones</a>
    <a class="btn btn-secondary" href="logout.php">Salir y entrar con otro usuario</a>
  </p>
</main>
</body></html>
HTML;
    exit;
}

function puede(string $minimo): bool
{
    $u = usuario_actual();
    return $u && (ROLES[$u['role']] ?? 0) >= (ROLES[$minimo] ?? 99);
}

/** Login con límite de intentos por usuario + IP. */
function intentar_login(string $username, string $password): array
{
    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE username = ? AND ip = ? AND ok = 0 AND created_at > datetime('now', '-15 minutes')"
    );
    $stmt->execute([$username, $ip]);
    if ((int) $stmt->fetchColumn() >= 6) {
        return ['ok' => false, 'error' => 'Demasiados intentos fallidos. Esperá 15 minutos antes de volver a probar.'];
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $ok = $user && password_verify($password, $user['password']);
    $pdo->prepare('INSERT INTO login_attempts (username, ip, ok) VALUES (?, ?, ?)')
        ->execute([$username, $ip, $ok ? 1 : 0]);

    if (!$ok) {
        return ['ok' => false, 'error' => 'Usuario o contraseña incorrectos.'];
    }

    iniciar_sesion();
    session_regenerate_id(true);
    $_SESSION['admin_user'] = [
        'id'          => (int) $user['id'],
        'username'    => $user['username'],
        'role'        => $user['role'],
        'must_change' => (bool) $user['must_change_password'],
    ];

    return ['ok' => true, 'must_change' => (bool) $user['must_change_password']];
}

function cerrar_sesion(): void
{
    iniciar_sesion();
    $_SESSION = [];
    session_destroy();
}

/**
 * Que un error del panel no salga como pantalla en blanco.
 *
 * En el hosting display_errors está apagado, así que un fatal devuelve una
 * página vacía y desde afuera no se distingue de un sitio caído. Fue
 * exactamente lo que pasó con el panel de usuarios cuando faltaba una columna:
 * el resto andaba y esa página estaba en blanco, sin ninguna pista.
 *
 * Se registra al cargar este archivo, que es el que incluyen todas las páginas
 * de /admin. api.php responde JSON, así que ahí el error también sale en JSON.
 */
set_exception_handler(function (Throwable $ex): void {
    error_log('Esquel LAB panel: ' . $ex->getMessage() . ' — ' . $ex->getFile() . ':' . $ex->getLine());

    $esApi = basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'api.php';
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: ' . ($esApi ? 'application/json' : 'text/html') . '; charset=utf-8');
    }

    if ($esApi) {
        echo json_encode(['ok' => false, 'error' => 'Error interno del panel. Quedó registrado en el log.']);
        return;
    }

    $detalle = e($ex->getMessage() . ' (' . basename($ex->getFile()) . ':' . $ex->getLine() . ')');
    echo <<<HTML
<!doctype html><html lang="es"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error en el panel</title><meta name="robots" content="noindex">
<style>
 body{font-family:system-ui,sans-serif;max-width:620px;margin:70px auto;padding:0 24px;color:#1F1D1B;line-height:1.6}
 h1{font-size:26px;margin:0 0 12px} code{background:#EBE7E0;padding:3px 7px;border-radius:5px;font-size:13.5px;word-break:break-word}
 a{color:#8A1E47}
</style></head><body>
<h1>Se rompió esta página del panel</h1>
<p>El resto del panel puede seguir funcionando. Esto es lo que falló:</p>
<p><code>{$detalle}</code></p>
<p>Pasale ese texto a quien mantiene el sitio: alcanza para ubicar el problema.</p>
<p><a href="dashboard.php">Volver a las postulaciones</a></p>
</body></html>
HTML;
});
