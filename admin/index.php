<?php
require_once __DIR__ . '/auth.php';
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
            --soft: hsl(218, 81%, 85%);
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
        }

        .page-wrap {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
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

        .text-soft {
            color: var(--soft);
        }
    </style>
</head>

<body>
    <div class="page-wrap">

        <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
            <div class="container">
                <a class="navbar-brand" href="../index.php">MusicAll</a>
                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-outline-light btn-sm" href="../index.php">Volver</a>
                    <a class="btn btn-danger btn-sm" href="../logout.php">Cerrar sesión</a>
                </div>
            </div>
        </nav>

        <main class="container px-4 py-5 px-md-5 my-4">
            <div class="row g-4">
                <div class="col-12">
                    <h1 class="display-6 fw-bold hero-title">
                        Panel <span>Administrador</span>
                    </h1>
                    <p class="text-soft opacity-75 mb-0">
                        Desde aquí vas a poder gestionar artistas, canciones, conciertos, entradas y productos.
                    </p>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card bg-glass shadow h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold">Artistas</h5>
                            <p class="text-muted mb-3">Crear / editar / borrar artistas.</p>
                            <a class="btn btn-primary w-100" href="artists.php">Gestionar</a>

                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card bg-glass shadow h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold">Canciones</h5>
                            <p class="text-muted mb-3">Añadir canciones y audio/URL.</p>
                            <a class="btn btn-primary w-100" href="songs.php">Gestionar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card bg-glass shadow h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold">Conciertos</h5>
                            <p class="text-muted mb-3">Eventos y tipos de entrada.</p>
                            <a class="btn btn-primary w-100 disabled" href="#">Gestionar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card bg-glass shadow h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold">Productos</h5>
                            <p class="text-muted mb-3">CDs, vinilos y stock.</p>
                            <a class="btn btn-primary w-100 disabled" href="#">Gestionar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card bg-glass shadow h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold">Albumes</h5>
                            <p class="text-muted mb-3">Crear, editar y borrar álbumes.</p>
                            <a class="btn btn-primary w-100" href="albums.php">Gestionar</a>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <footer class="bg-dark text-light mt-auto">
            <div class="container py-4 border-top">
                <small class="text-muted">© <?= date('Y') ?> MusicAll — Panel Admin</small>
            </div>
        </footer>

    </div>
</body>

</html>