<?php
require_once __DIR__ . '/auth.php';

$section = trim($_GET['section'] ?? 'artists');
$allowed = ['artists', 'albums', 'songs', 'concerts', 'shop'];
if (!in_array($section, $allowed, true)) $section = 'artists';

function isActive($current, $value)
{
    return $current === $value ? 'active' : '';
}

$map = [
    'artists'  => __DIR__ . '/pages/artists.php',
    'albums'   => __DIR__ . '/pages/albums.php',
    'songs'    => __DIR__ . '/pages/songs.php',
    'concerts' => __DIR__ . '/pages/concerts.php',
    'shop'     => __DIR__ . '/pages/shop.php',
];
$view = $map[$section];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin | MusicAll</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

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

        .admin-shell {
            min-height: 100vh;
            display: flex;
            gap: 24px;
            padding: 28px 18px;
        }

        .admin-sidebar {
            width: 260px;
            flex: 0 0 260px;
        }

        .admin-content {
            flex: 1;
            min-width: 0;
        }

        .sidebar-title {
            color: var(--t1);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 10px;
        }

        .sidebar-card {
            border-radius: 14px;
            overflow: hidden;
        }

        .list-group-item.active {
            background: #0d6efd;
            border-color: #0d6efd;
        }

        .top-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>

<body>

    <div class="container-fluid admin-shell">

        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="mb-3">
                <div class="sidebar-title">MusicAll Admin</div>
                <div class="text-soft opacity-75 small">Panel de gestión</div>
            </div>

            <div class="card bg-glass shadow sidebar-card">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action <?= isActive($section, 'artists') ?>"
                            href="index.php?section=artists">Artistas</a>

                        <a class="list-group-item list-group-item-action <?= isActive($section, 'albums') ?>"
                            href="index.php?section=albums">Álbumes</a>

                        <a class="list-group-item list-group-item-action <?= isActive($section, 'songs') ?>"
                            href="index.php?section=songs">Canciones</a>

                        <a class="list-group-item list-group-item-action <?= isActive($section, 'concerts') ?>"
                            href="index.php?section=concerts">Conciertos</a>

                        <a class="list-group-item list-group-item-action <?= isActive($section, 'shop') ?>"
                            href="index.php?section=shop">Tienda</a>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-grid gap-2">
                <a class="btn btn-outline-light" href="../index.php">Volver</a>
                <a class="btn btn-danger" href="../logout.php">Cerrar sesión</a>
            </div>
        </aside>

        <!-- Content -->
        <main class="admin-content">
            <div class="mb-3">
                <h1 class="display-6 fw-bold hero-title mb-1">
                    Panel <span>Administrador</span>
                </h1>
                <div class="text-soft opacity-75">
                    Gestiona el contenido de MusicAll desde un único panel.
                </div>
            </div>

            <div class="card bg-glass shadow">
                <div class="card-body p-4">
                    <?php include $view; ?>
                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>