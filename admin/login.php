<?php
// Creado para el Laboratorio de Destino Esquel
// admin/login.php

session_start();
require_once 'db_config.php';

// Redireccionar si ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error_msg = 'Por favor complete todos los campos.';
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Generar sesión segura
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error_msg = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Error en el inicio de sesión: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Interno · Esquel LAB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="bg-vignette"></div>

    <main class="section" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;">
        <div class="auth-card card" style="width: 100%; border-color: rgba(255, 255, 255, 0.05); animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
            <div style="text-align: center; margin-bottom: 32px;">
                <a href="../index.php">
                    <img src="../assets/images/logo-lab-white.png" alt="Esquel LAB" style="height: 48px; margin-bottom: 16px;">
                </a>
                <h3 style="margin-bottom: 4px;">Acceso de Gestión</h3>
                <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0;">Laboratorio de Destino Esquel</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="card" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; padding: 12px; margin-bottom: 24px; text-align: center; border-radius: 6px;">
                    <p style="color: #f87171; margin-bottom: 0; font-size: 0.85rem;"><?php echo htmlspecialchars($error_msg); ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Usuario</label>
                    <input class="form-input" type="text" id="username" name="username" required autocomplete="username" placeholder="Ingrese su usuario">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-input" type="password" id="password" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">Ingresar al Panel</button>
            </form>

            <div style="text-align: center; margin-top: 24px;">
                <a href="../index.php" style="font-size: 0.8rem; color: var(--color-text-secondary); hover:color: var(--color-text-primary);">← Volver al sitio público</a>
            </div>
        </div>
    </main>
</body>
</html>
