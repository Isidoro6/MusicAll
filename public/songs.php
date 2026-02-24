<?php
session_start();
require_once __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Canciones | MusicAll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
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
                radial-gradient(650px circle at 0% 0%, hsl(218, 41%, 35%) 15%, hsl(218, 41%, 30%) 35%, hsl(218, 41%, 20%) 75%, hsl(218, 41%, 19%) 80%, transparent 100%),
                radial-gradient(1250px circle at 100% 100%, hsl(218, 41%, 45%) 15%, hsl(218, 41%, 30%) 35%, hsl(218, 41%, 20%) 75%, hsl(218, 41%, 19%) 80%, transparent 100%);
            background-attachment: fixed;
        }

        .bg-glass {
            background-color: hsla(0, 0%, 100%, 0.92) !important;
            backdrop-filter: saturate(200%) blur(25px);
            border: 1px solid rgba(255, 255, 255, .35);
        }

        .hero-title {
            color: var(--t1);
        }

        .hero-title span {
            color: var(--t2);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="../index.php">MusicAll</a>

            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-light btn-sm" href="../index.php">Inicio</a>
                <?php if (!$user): ?>
                    <a class="btn btn-primary btn-sm" href="../iniciarSesion.php">Iniciar sesión</a>
                <?php else: ?>
                    <span class="text-white-50 small">Hola, <?= htmlspecialchars($user['username']) ?></span>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Explora</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../index.php#generos">Géneros</a></li>
                            <li><a class="dropdown-item" href="artists.php">Artistas</a></li>
                            <li><a class="dropdown-item" href="albums.php">Álbumes</a></li>
                            <li><a class="dropdown-item" href="concerts.php">Conciertos</a></li>
                        </ul>
                    </div>
                    <a class="btn btn-danger btn-sm" href="../logout.php">Salir</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="card bg-glass shadow">
            <div class="card-body p-4">
                <h1 class="display-6 fw-bold hero-title mb-2">Canciones <span>(deshabilitado)</span></h1>
                <p class="text-muted mb-4">
                    La vista “ver todas las canciones” se ha desactivado para evitar lentitud.
                    Desde el inicio puedes acceder a canciones desde las listas por género o canciones destacadas.
                </p>
                <a class="btn btn-primary" href="../index.php">Volver al inicio</a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>