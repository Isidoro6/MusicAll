<?php
session_start();
require_once __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;

// 4 canciones “Éxitos actuales” (de momento por created_at DESC)
$songs = [];
$stmt = $conn->prepare("
  SELECT
    s.id, s.title,
    ar.name AS artist_name,
    al.title AS album_title,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  ORDER BY s.created_at DESC
  LIMIT 4
");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $songs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Artistas (para la sección de inicio)
$artists = [];
$stmt = $conn->prepare("SELECT id, name, image_url FROM artists ORDER BY created_at DESC LIMIT 6");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $artists = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MusicAll</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <style>
        :root {
            --bg1: hsl(218, 41%, 15%);
            --t1: hsl(218, 81%, 95%);
            --t2: hsl(218, 81%, 75%);
            --soft: hsl(218, 81%, 85%);
            --accent: hsl(190, 85%, 55%);
            /* cian llamativo */
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

        .tag-accent {
            color: var(--accent);
            font-weight: 700;
            letter-spacing: .2px;
        }

        /* Sidebar */
        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar-desktop {
                display: none;
            }
        }

        .sidebar-card {
            position: sticky;
            top: 18px;
        }

        .side-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 10px;
            color: rgba(255, 255, 255, .9);
            text-decoration: none;
        }

        .side-link:hover {
            background: rgba(255, 255, 255, .08);
            color: white;
        }

        .side-muted {
            color: rgba(255, 255, 255, .75);
        }

        /* Cards */
        .media-card img {
            height: 160px;
            object-fit: cover;
        }

        .media-placeholder {
            height: 160px;
            background: rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: 600;
        }

        footer a {
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="page-wrap">

        <!-- HEADER -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                    <img src="https://getbootstrap.com/docs/5.2/assets/brand/bootstrap-logo.svg" width="38" height="38" alt="Logo">
                    <span class="fw-semibold">MusicAll</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="artists.php">Artistas</a></li>
                        <li class="nav-item"><a class="nav-link" href="albums.php">Álbumes</a></li>
                        <li class="nav-item"><a class="nav-link" href="songs.php">Canciones</a></li>

                        <?php if (!$user): ?>
                            <li class="nav-item"><a class="nav-link" href="registro.php">Registro</a></li>
                            <li class="nav-item"><a class="nav-link" href="iniciarSesion.php">Iniciar sesión</a></li>
                        <?php else: ?>
                            <li class="nav-item"><span class="nav-link">Hola, <?= htmlspecialchars($user['username']) ?></span></li>
                            <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                                <li class="nav-item"><a class="nav-link" href="admin/index.php">Panel Admin</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Salir</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="container px-4 py-5 px-md-5 my-4">
            <div class="mb-4">
                <h1 class="display-5 fw-bold hero-title">Bienvenido a <span>MusicAll</span></h1>
                <p class="lead opacity-75 text-soft mb-0">Disfruta de tu música favorita, descubre nuevos artistas y comparte tus playlists.</p>
            </div>

            <div class="layout">
                <!-- Sidebar desktop -->
                <aside class="sidebar-desktop">
                    <div class="card bg-glass shadow sidebar-card">
                        <div class="card-body p-3">
                            <div class="fw-semibold mb-2">Explorar</div>
                            <a class="side-link bg-dark" href="artists.php">
                                <span>Artistas</span><span class="side-muted">→</span>
                            </a>
                            <div class="my-2"></div>
                            <a class="side-link bg-dark" href="albums.php">
                                <span>Álbumes</span><span class="side-muted">→</span>
                            </a>
                            <div class="my-2"></div>
                            <a class="side-link bg-dark" href="songs.php">
                                <span>Canciones</span><span class="side-muted">→</span>
                            </a>
                            <hr class="text-white-50">
                            <div class="small side-muted">
                                Tip: desde Artistas podrás ver sus álbumes y luego sus canciones.
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Content -->
                <section>
                    <!-- Canciones -->
                    <div class="card bg-glass shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                                <div>
                                    <h2 class="h5 fw-semibold mb-1">Canciones</h2>
                                    <div class="tag-accent">ÉXITOS ACTUALES</div>
                                </div>
                                <a class="btn btn-sm btn-outline-secondary" href="songs.php">Ver todas</a>
                            </div>

                            <?php if (empty($songs)): ?>
                                <p class="text-muted mb-0">Aún no hay canciones. (Añádelas desde Panel Admin → Canciones)</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($songs as $s): ?>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <a href="song.php?id=<?= (int)$s['id'] ?>" class="text-decoration-none text-dark">
                                                <div class="card h-100 media-card">
                                                    <?php if (!empty($s['display_image'])): ?>
                                                        <img src="<?= htmlspecialchars($s['display_image']) ?>" class="card-img-top" alt="img">
                                                    <?php else: ?>
                                                        <div class="media-placeholder">Sin imagen</div>
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h3 class="h6 fw-semibold mb-1"><?= htmlspecialchars($s['title']) ?></h3>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($s['artist_name']) ?>
                                                            <?= !empty($s['album_title']) ? ' · ' . htmlspecialchars($s['album_title']) : '' ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Artistas -->
                    <div class="card bg-glass shadow">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-semibold mb-0">Artistas</h2>
                                <a class="btn btn-sm btn-outline-secondary" href="artists.php">Ver todos</a>
                            </div>

                            <?php if (empty($artists)): ?>
                                <p class="text-muted mb-0">Aún no hay artistas.</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($artists as $a): ?>
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <a href="artist.php?id=<?= (int)$a['id'] ?>" class="text-decoration-none text-dark">
                                                <div class="card h-100 media-card">
                                                    <?php if (!empty($a['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars($a['image_url']) ?>" class="card-img-top" alt="img">
                                                    <?php else: ?>
                                                        <div class="media-placeholder">Sin imagen</div>
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h3 class="h6 fw-semibold mb-0"><?= htmlspecialchars($a['name']) ?></h3>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="bg-dark text-light mt-auto">
            <div class="container">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 py-5 border-top">
                    <div class="col mb-3">
                        <img src="https://getbootstrap.com/docs/5.1/assets/brand/bootstrap-logo.svg" width="44" height="44" class="mb-3" alt="Logo">
                        <p class="text-muted mb-0">© <?= date('Y') ?> MusicAll</p>
                    </div>
                    <div class="col mb-3">
                        <h5 class="fw-semibold">Explorar</h5>
                        <ul class="list-unstyled">
                            <li><a href="artists.php" class="text-light">Artistas</a></li>
                            <li><a href="albums.php" class="text-light">Álbumes</a></li>
                            <li><a href="songs.php" class="text-light">Canciones</a></li>
                        </ul>
                    </div>
                    <div class="col mb-3">
                        <h5 class="fw-semibold">Cuenta</h5>
                        <ul class="list-unstyled">
                            <li><a href="registro.php" class="text-light">Registro</a></li>
                            <li><a href="iniciarSesion.php" class="text-light">Iniciar sesión</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>