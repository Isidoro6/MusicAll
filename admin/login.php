<?php
session_start();
require_once __DIR__ . '/../db.php';

// Si ya está logueado como admin → directo al panel
if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'user') === 'admin') {
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') $errors[] = "Introduce tu usuario.";
    if ($password === '') $errors[] = "Introduce tu contraseña.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = "Usuario o contraseña incorrectos.";
        } else {
            if (($user['role'] ?? 'user') !== 'admin') {
                $errors[] = "Este usuario no tiene permisos de administrador.";
            } else {
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | MusicAll</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">

    <style>
        :root {
            --bg1: hsl(218, 41%, 15%);
            --t1: hsl(218, 81%, 95%);
            --t2: hsl(218, 81%, 75%);
        }

        body {
            min-height: 100vh;
            background-color: var(--bg1);
            background-image:
                radial-gradient(650px circle at 0% 0%,
                    hsl(218, 41%, 35%) 15%,
                    hsl(218, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%),
                radial-gradient(1250px circle at 100% 100%,
                    hsl(218, 41%, 45%) 15%,
                    hsl(218, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: hsla(0, 0%, 100%, 0.95);
            backdrop-filter: saturate(200%) blur(20px);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 12px;
        }

        .login-title {
            color: var(--t1);
        }

        .logo-badge {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #44006b, #ad1fff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin: 0 auto 15px auto;
        }

        .top-title {
            text-align: center;
            color: var(--t1);
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    <div class="text-center position-absolute top-0 start-0 w-100 mt-5">
        <h2 class="login-title">Panel de Administración</h2>
    </div>

    <div class="login-card shadow p-4">

        <div class="logo-badge">B</div>

        <h4 class="text-center mb-4">Login Administrador</h4>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="username" class="form-control"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Entrar al panel
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                Solo usuarios con rol <strong>admin</strong>
            </small>
        </div>

        <div class="text-center mt-3">
            <a href="../iniciarSesion.php" class="text-decoration-none">
                ← Volver al login de usuarios
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</body>

</html>