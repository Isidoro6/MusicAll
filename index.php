<?php
session_start();
require_once __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;

function fmt_duration($sec)
{
    if ($sec === null || $sec === '' || (int)$sec <= 0) return '—';
    $sec = (int)$sec;
    $m = intdiv($sec, 60);
    $s = $sec % 60;
    return sprintf("%d:%02d", $m, $s);
}

// 4 canciones destacadas (aleatorias)
$topSongs = [];
$stmt = $conn->prepare("
  SELECT
    s.id, s.title, s.duration_sec,
    ar.name AS artist_name,
    al.title AS album_title,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  ORDER BY RAND()
  LIMIT 4
");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $topSongs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Artistas sugeridos (3, random)
$artists = [];
$res = $conn->query("
  SELECT id, name, image_url
  FROM artists
  ORDER BY RAND()
  LIMIT 3
");
if ($res) $artists = $res->fetch_all(MYSQLI_ASSOC);

// Playlists por “género” (tabs horizontales). 10 canciones aleatorias por género (demo)
$genres = ['Rock', 'Indie', 'Classic', 'Pop', 'Jazz'];
$genreLists = [];
foreach ($genres as $g) {
    $list = [];
    $stmt = $conn->prepare("
      SELECT
        s.id, s.title, s.duration_sec,
        ar.name AS artist_name,
        al.title AS album_title,
        COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
      FROM songs s
      JOIN artists ar ON ar.id = s.artist_id
      LEFT JOIN albums al ON al.id = s.album_id
      ORDER BY RAND()
      LIMIT 10
    ");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $list = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
    $genreLists[$g] = $list;
}

// Sugerencias de conciertos:
// - SOLO artistas con concierto próximo
// - mostramos el PRÓXIMO concierto por artista
// - ordenado del más cercano al más lejano
$concertSuggestions = [];
$res = $conn->query("
  SELECT
    a.id AS artist_id,
    a.name AS artist_name,
    c.id AS concert_id,
    c.city, c.venue, c.concert_date, c.price_eur
  FROM artists a
  JOIN concerts c
    ON c.id = (
      SELECT c2.id
      FROM concerts c2
      WHERE c2.artist_id = a.id
        AND c2.concert_date >= CURDATE()
      ORDER BY c2.concert_date ASC
      LIMIT 1
    )
  ORDER BY c.concert_date ASC
  LIMIT 6
");
if ($res) $concertSuggestions = $res->fetch_all(MYSQLI_ASSOC);

// Tab por defecto (si no hay, Rock)
$defaultGenre = $genres[0] ?? 'Rock';
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

        /* Layout: contenido + columna derecha */
        .layout {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 18px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        .right-col-sticky {
            position: sticky;
            top: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Menú explorar (derecha) */
        .side-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 10px;
            color: rgba(255, 255, 255, .92);
            text-decoration: none;
        }

        .side-link:hover {
            background: rgba(255, 255, 255, .08);
            color: white;
        }

        .side-muted {
            color: rgba(255, 255, 255, .75);
        }

        /* Cards media */
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

        /* “Barras” de canciones sin huecos */
        .song-bars .list-group-item {
            border-radius: 0 !important;
        }

        .song-bar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .song-bar img {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 8px;
            flex: 0 0 auto;
        }

        .song-bar .ph {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(0, 0, 0, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #555;
            flex: 0 0 auto;
        }

        .song-meta {
            min-width: 0;
            flex: 1;
        }

        .song-meta .title {
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .song-meta .sub {
            font-size: .88rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dur {
            font-size: .9rem;
            color: #555;
            flex: 0 0 auto;
            margin-left: 10px;
        }

        /* Tabs (géneros) horizontales */
        .genre-tabs .nav-link {
            border-radius: 10px !important;
            margin-right: 8px;
        }

        .genre-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
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
                        <li class="nav-item"><a class="nav-link" href="public/artists.php">Artistas</a></li>
                        <li class="nav-item"><a class="nav-link" href="public/albums.php">Álbumes</a></li>
                        <li class="nav-item"><a class="nav-link" href="public/songs.php">Canciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="public/concerts.php">Conciertos</a></li>

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
                <p class="lead opacity-75 text-soft mb-0">Disfruta de tu música favorita, descubre nuevos artistas y busca conciertos.</p>
            </div>

            <div class="layout">

                <!-- COLUMNA CENTRAL -->
                <section>

                    <!-- Canciones destacadas -->
                    <div class="card bg-glass shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                                <div>
                                    <h2 class="h5 fw-semibold mb-1">Canciones</h2>
                                    <div class="tag-accent">LAS MÁS ESCUCHADAS (demo)</div>
                                </div>
                                <a class="btn btn-sm btn-outline-secondary" href="public/songs.php">Ver todas</a>
                            </div>

                            <?php if (empty($topSongs)): ?>
                                <p class="text-muted mb-0">Aún no hay canciones. (Añádelas desde Panel Admin → Canciones)</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($topSongs as $s): ?>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <a href="public/song.php?id=<?= (int)$s['id'] ?>" class="text-decoration-none text-dark">
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
                                                            <?= !empty($s['duration_sec']) ? ' · ' . htmlspecialchars(fmt_duration($s['duration_sec'])) : '' ?>
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

                    <!-- Listas por género (TABS horizontales) -->
                    <div class="card bg-glass shadow mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <h2 class="h5 fw-semibold mb-1">Listas por género</h2>
                                    <div class="text-muted small">Ejemplo: aún no asignamos género a canciones, se muestran aleatorias.</div>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <ul class="nav nav-pills genre-tabs mb-3" id="genreTab" role="tablist">
                                <?php $i = 0;
                                foreach ($genres as $g): $i++; ?>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link <?= $i === 1 ? 'active' : '' ?>"
                                            id="tab-<?= $i ?>"
                                            data-bs-toggle="pill"
                                            data-bs-target="#pane-<?= $i ?>"
                                            type="button"
                                            role="tab"
                                            aria-controls="pane-<?= $i ?>"
                                            aria-selected="<?= $i === 1 ? 'true' : 'false' ?>">
                                            <?= htmlspecialchars($g) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Panels -->
                            <div class="tab-content" id="genreTabContent">
                                <?php $i = 0;
                                foreach ($genres as $g): $i++;
                                    $list = $genreLists[$g] ?? []; ?>
                                    <div
                                        class="tab-pane fade <?= $i === 1 ? 'show active' : '' ?>"
                                        id="pane-<?= $i ?>"
                                        role="tabpanel"
                                        aria-labelledby="tab-<?= $i ?>">

                                        <div class="mb-2">
                                            <span class="badge bg-dark"><?= htmlspecialchars($g) ?></span>
                                        </div>

                                        <?php if (empty($list)): ?>
                                            <div class="text-muted">No hay canciones para mostrar.</div>
                                        <?php else: ?>
                                            <div class="list-group list-group-flush song-bars">
                                                <?php foreach ($list as $s): ?>
                                                    <a class="list-group-item list-group-item-action"
                                                        href="public/song.php?id=<?= (int)$s['id'] ?>">
                                                        <div class="song-bar">
                                                            <?php if (!empty($s['display_image'])): ?>
                                                                <img src="<?= htmlspecialchars($s['display_image']) ?>" alt="img">
                                                            <?php else: ?>
                                                                <div class="ph">♪</div>
                                                            <?php endif; ?>

                                                            <div class="song-meta">
                                                                <div class="title"><?= htmlspecialchars($s['title']) ?></div>
                                                                <div class="sub">
                                                                    <?= htmlspecialchars($s['artist_name']) ?>
                                                                    <?= !empty($s['album_title']) ? ' · ' . htmlspecialchars($s['album_title']) : '' ?>
                                                                </div>
                                                            </div>

                                                            <div class="dur"><?= htmlspecialchars(fmt_duration($s['duration_sec'])) ?></div>
                                                        </div>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                    <!-- Artistas sugeridos (3, random) -->
                    <div class="card bg-glass shadow">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-semibold mb-0">Artistas sugeridos para ti</h2>
                                <a class="btn btn-sm btn-outline-secondary" href="public/artists.php">Ver todos</a>
                            </div>

                            <?php if (empty($artists)): ?>
                                <p class="text-muted mb-0">Aún no hay artistas.</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($artists as $a): ?>
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <a href="public/artist.php?id=<?= (int)$a['id'] ?>" class="text-decoration-none text-dark">
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

                <!-- COLUMNA DERECHA -->
                <aside class="right-col-sticky">

                    <!-- Explorar -->
                    <div class="card bg-glass shadow">
                        <div class="card-body p-3">
                            <div class="fw-semibold mb-2">Explorar</div>
                            <a class="side-link bg-dark" href="public/artists.php">
                                <span>Artistas</span><span class="side-muted">→</span>
                            </a>
                            <div class="my-2"></div>
                            <a class="side-link bg-dark" href="public/albums.php">
                                <span>Álbumes</span><span class="side-muted">→</span>
                            </a>
                            <div class="my-2"></div>
                            <a class="side-link bg-dark" href="public/songs.php">
                                <span>Canciones</span><span class="side-muted">→</span>
                            </a>
                            <div class="my-2"></div>
                            <a class="side-link bg-dark" href="public/concerts.php">
                                <span>Conciertos</span><span class="side-muted">→</span>
                            </a>
                            <hr class="text-white-50">
                            <div class="small side-muted">
                                Tip: entra en Conciertos para ver próximos eventos por artista y comprar entradas.
                            </div>
                        </div>
                    </div>

                    <!-- Sugerencias de conciertos (ordenado por fecha asc, solo próximos) -->
                    <div class="card bg-glass shadow">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-semibold">Sugerencias</div>
                                <a class="btn btn-sm btn-outline-secondary" href="public/concerts.php">Ver</a>
                            </div>

                            <?php if (empty($concertSuggestions)): ?>
                                <div class="text-muted small">
                                    No hay conciertos próximos todavía. (Inserta algunos en la tabla <code>concerts</code>)
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($concertSuggestions as $c): ?>
                                        <a class="list-group-item list-group-item-action"
                                            href="public/concerts.php?artist_id=<?= (int)$c['artist_id'] ?>">
                                            <div class="fw-semibold"><?= htmlspecialchars($c['artist_name']) ?></div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($c['city']) ?>
                                                <?= !empty($c['concert_date']) ? ' · ' . htmlspecialchars($c['concert_date']) : '' ?>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </aside>

            </div>
        </main>

        <footer class="bg-dark text-light mt-auto">
            <div class="container py-4 border-top">
                <small class="text-muted">© <?= date('Y') ?> MusicAll</small>
            </div>
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>