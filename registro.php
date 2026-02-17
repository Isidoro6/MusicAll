<?php
require_once __DIR__ . '/db.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || mb_strlen($username) < 3) {
        $errors[] = "El usuario debe tener al menos 3 caracteres.";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El correo no es válido.";
    }
    if ($password === '' || strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if (!$errors) {
        // Comprobar duplicados
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $res = $check->get_result();
        if ($res && $res->num_rows > 0) {
            $errors[] = "Ese usuario o correo ya está registrado.";
        }
        $check->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hash);

        if ($stmt->execute()) {
            $success = "Usuario creado correctamente. Ya puedes iniciar sesión.";
        } else {
            $errors[] = "Error al registrar: " . $conn->error;
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro | MusicAll</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous">

    <style>
        :root{
            --bg1: hsl(218, 41%, 15%);
            --t1: hsl(218, 81%, 95%);
            --t2: hsl(218, 81%, 75%);
            --soft: hsl(218, 81%, 85%);
        }

        body{
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
                    hsl(218, 41%, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%);
            background-attachment: fixed;
        }

        #radius-shape-1 {
            height: 220px;
            width: 220px;
            top: -60px;
            left: -130px;
            background: radial-gradient(#44006b, #ad1fff);
            overflow: hidden;
            position: absolute;
            border-radius: 50%;
        }

        #radius-shape-2 {
            border-radius: 38% 62% 63% 37% / 70% 33% 67% 30%;
            bottom: -60px;
            right: -110px;
            width: 320px;
            height: 320px;
            background: radial-gradient(#44006b, #ad1fff);
            overflow: hidden;
            position: absolute;
        }

        .bg-glass {
            background-color: hsla(0, 0%, 100%, 0.92) !important;
            backdrop-filter: saturate(200%) blur(25px);
            border: 1px solid rgba(255,255,255,.35);
        }

        .hero-title{ color: var(--t1); }
        .hero-title span{ color: var(--t2); }
        .text-soft{ color: var(--soft); }

        .page{
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
<section class="page position-relative overflow-hidden">
    <div id="radius-shape-1"></div>
    <div id="radius-shape-2"></div>

    <div class="container px-4 py-5 px-md-5">
        <div class="row gx-lg-5 align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0" style="z-index:10;">
                <h1 class="display-5 fw-bold hero-title">
                    Regístrate <br><span>en MusicAll</span>
                </h1>
                <p class="opacity-75 text-soft mb-4">
                    Crea tu cuenta para acceder a la plataforma.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-light" href="index.php">Volver al inicio</a>
                    <a class="btn btn-primary" href="iniciarSesion.php">Ya tengo cuenta</a>
                </div>
            </div>

            <div class="col-lg-6" style="z-index:10;">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-semibold mb-3">Crear cuenta</h2>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="registro.php" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label" for="username">Nombre de usuario</label>
                                <input type="text" id="username" name="username" class="form-control"
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password">Contraseña</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Registrarme
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>
</html>
