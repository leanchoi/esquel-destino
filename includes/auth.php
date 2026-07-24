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
        http_response_code(403);
        exit('No tenés permisos para esta sección.');
    }
    return $u;
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
