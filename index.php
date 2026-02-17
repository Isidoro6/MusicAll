<?php
session_start();
require_once __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MusicAll</title>

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
                    hsl(218, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%);
            background-attachment: fixed;
        }

        .page-wrap{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main{ flex: 1; }

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

        footer a{ text-decoration: none; }
        footer a:hover{ text-decoration: underline; }
    </style>
</head>

<body>
<div class="page-wrap">

    <!-- HEADER -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="https://getbootstrap.com/docs/5.2/assets/brand/bootstrap-logo.svg"
                     width="38" height="38" alt="Logo">
                <span class="fw-semibold">MusicAll</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Inicio</a></li>
                    <?php if (!$user): ?>
                        <li class="nav-item"><a href="registro.php" class="nav-link">Registro</a></li>
                        <li class="nav-item"><a href="iniciarSesion.php" class="nav-link">Iniciar sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><span class="nav-link">Hola, <?= htmlspecialchars($user['username']) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="position-relative overflow-hidden">
        <div id="radius-shape-1"></div>
        <div id="radius-shape-2"></div>

        <div class="container px-4 py-5 px-md-5 my-4" style="z-index:10; position:relative;">
            <div class="row gx-lg-5 align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold hero-title">
                        Bienvenido a <span>MusicAll</span>
                    </h1>
                    <p class="lead opacity-75 text-soft mb-4">
                        Diseño unificado. El sistema de usuarios ya funciona (registro + login).
                        Los artículos los añadimos después.
                    </p>

                    </div>

                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="card bg-glass shadow">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-semibold mb-2">Artículos</h2>
                            <p class="mb-0 text-muted">
                                Esta sección la conectamos luego a la tabla de artículos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-dark text-light mt-auto">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 py-5 border-top">
                <div class="col mb-3">
                    <img src="https://getbootstrap.com/docs/5.1/assets/brand/bootstrap-logo.svg"
                         width="44" height="44" class="mb-3" alt="Logo">
                    <p class="text-muted mb-0">© <?= date('Y') ?> MusicAll</p>
                </div>

                <div class="col mb-3">
                    <h5 class="fw-semibold">Cuenta</h5>
                    <ul class="list-unstyled">
                        <li><a href="registro.php" class="text-light">Registro</a></li>
                        <li><a href="iniciarSesion.php" class="text-light">Iniciar sesión</a></li>
                    </ul>
                </div>

                <div class="col mb-3">
                    <h5 class="fw-semibold">Proyecto</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">Sobre</a></li>
                        <li><a href="#" class="text-light">Contacto</a></li>
                    </ul>
                </div>

                <div class="col mb-3">
                    <h5 class="fw-semibold">Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">Privacidad</a></li>
                        <li><a href="#" class="text-light">Términos</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>
</html>
