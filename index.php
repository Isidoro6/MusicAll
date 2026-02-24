<?php
require_once __DIR__ . '/partials/app.php';

// Para header.php
$GLOBALS['BASE_HOME'] = '/MusicAll/index.php';

// 4 canciones random (las “más escuchadas demo”)
$songs = [];
$stmt = $conn->prepare("
  SELECT
    s.id, s.title, s.duration_sec, s.audio_url,
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
    $songs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Artistas sugeridos (3 random)
$artists = [];
$stmt = $conn->prepare("SELECT id, name, image_url FROM artists ORDER BY RAND() LIMIT 3");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $artists = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Sugerencias de conciertos (solo próximos, ordenados por fecha asc)
$concs = [];
// OJO: esto asume que tienes tabla concerts con campos: id, artist_id, city, date (o event_date)
// Si tu columna se llama distinto, me dices el nombre y lo ajusto.
$res = $conn->query("
  SELECT c.id, c.city, c.event_date, ar.name AS artist_name
  FROM concerts c
  JOIN artists ar ON ar.id = c.artist_id
  WHERE c.event_date >= CURDATE()
  ORDER BY c.event_date ASC
  LIMIT 3
");
if ($res) $concs = $res->fetch_all(MYSQLI_ASSOC);

// Géneros demo
$genres = ["Rock", "Indie", "Classic", "Pop", "Jazz"];

// 10 canciones random para cada pestaña (demo)
$genreLists = [];
$stmt = $conn->prepare("
  SELECT
    s.id, s.title, s.duration_sec, s.audio_url,
    ar.name AS artist_name,
    al.title AS album_title,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  ORDER BY RAND()
  LIMIT 10
");
foreach ($genres as $g) {
    if ($stmt) {
        $stmt->execute();
        $res2 = $stmt->get_result();
        $genreLists[$g] = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        $genreLists[$g] = [];
    }
}
if ($stmt) $stmt->close();
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
        }

        .layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 18px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

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

        .side-card {
            position: sticky;
            top: 16px;
        }

        /* Tabs género */
        .genre-tabs .nav-link {
            border-radius: 10px;
            padding: 8px 12px;
            font-weight: 600;
        }

        /* Lista horizontal compacta (sin hueco) */
        .track-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        .track-row:last-child {
            border-bottom: 0;
        }

        .track-img {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            object-fit: cover;
            background: rgba(0, 0, 0, .06);
            flex: 0 0 auto;
        }

        .track-main {
            flex: 1;
            min-width: 0;
        }

        .track-title {
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-sub {
            font-size: 12px;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .track-dur {
            font-size: 12px;
            color: #6c757d;
            width: 42px;
            text-align: right;
        }

        .mini-muted {
            color: #adb5bd;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="mb-4">
            <h1 class="display-5 fw-bold hero-title">Bienvenido a <span>MusicAll</span></h1>
            <p class="lead opacity-75 text-soft mb-0">Disfruta de tu música favorita, descubre nuevos artistas y busca conciertos.</p>
        </div>

        <div class="layout">

            <!-- Columna principal -->
            <section>

                <!-- Canciones (demo) -->
                <div class="card bg-glass shadow mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                            <div>
                                <h2 class="h5 fw-semibold mb-1">Canciones</h2>
                                <div class="tag-accent">LAS MÁS ESCUCHADAS (demo)</div>
                            </div>
                            <!-- Quitado "Ver todas" a propósito -->
                        </div>

                        <?php if (empty($songs)): ?>
                            <p class="text-muted mb-0">Aún no hay canciones.</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($songs as $s): ?>
                                    <div class="col-12 col-sm-6 col-lg-3">
                                        <div class="card h-100 media-card">
                                            <?php if (!empty($s['display_image'])): ?>
                                                <img src="<?= htmlspecialchars($s['display_image']) ?>" class="card-img-top" alt="img">
                                            <?php else: ?>
                                                <div class="media-placeholder">Sin imagen</div>
                                            <?php endif; ?>

                                            <div class="card-body">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="min-w-0">
                                                        <h3 class="h6 fw-semibold mb-1 text-truncate"><?= htmlspecialchars($s['title']) ?></h3>
                                                        <div class="small text-muted text-truncate">
                                                            <?= htmlspecialchars($s['artist_name']) ?>
                                                            <?= !empty($s['album_title']) ? ' · ' . htmlspecialchars($s['album_title']) : '' ?>
                                                            <?= $s['duration_sec'] ? ' · ' . htmlspecialchars(fmt_duration($s['duration_sec'])) : '' ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2 mt-3">
                                                    <a class="btn btn-sm btn-outline-secondary" href="/MusicAll/public/song.php?id=<?= (int)$s['id'] ?>">Ver</a>

                                                    <?php if ($user && !empty($s['audio_url'])): ?>
                                                        <button
                                                            class="btn btn-sm btn-primary"
                                                            type="button"
                                                            onclick='MusicAllPlayer.playTrack(<?= json_encode([
                                                                                                    "id" => (int)$s["id"],
                                                                                                    "title" => $s["title"],
                                                                                                    "subtitle" => trim(($s["artist_name"] ?? "") . (!empty($s["album_title"]) ? " · " . $s["album_title"] : "")),
                                                                                                    "image" => $s["display_image"] ?? "",
                                                                                                    "audio_url" => $s["audio_url"]
                                                                                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>Reproducir</button>
                                                    <?php elseif (!$user): ?>
                                                        <a class="btn btn-sm btn-outline-primary" href="/MusicAll/iniciarSesion.php">Inicia sesión</a>
                                                    <?php else: ?>
                                                        <span class="small text-muted align-self-center">Sin audio</span>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Listas por género (tabs izquierda->derecha, siempre visible una) -->
                <div class="card bg-glass shadow mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-1">Listas por género</h2>
                        <div class="small text-muted mb-3">Ejemplo: aún no asignamos género a canciones, se muestran aleatorias.</div>

                        <ul class="nav nav-pills genre-tabs mb-3" role="tablist">
                            <?php foreach ($genres as $i => $g): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                                        id="tab-<?= htmlspecialchars($g) ?>"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pane-<?= htmlspecialchars($g) ?>"
                                        type="button" role="tab">
                                        <?= htmlspecialchars($g) ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="tab-content">
                            <?php foreach ($genres as $i => $g): ?>
                                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="pane-<?= htmlspecialchars($g) ?>" role="tabpanel">
                                    <div class="badge bg-dark mb-2"><?= htmlspecialchars($g) ?></div>

                                    <div class="card">
                                        <div class="card-body p-0">
                                            <?php foreach (($genreLists[$g] ?? []) as $row): ?>
                                                <div class="track-row">
                                                    <?php if (!empty($row['display_image'])): ?>
                                                        <img class="track-img" src="<?= htmlspecialchars($row['display_image']) ?>" alt="">
                                                    <?php else: ?>
                                                        <div class="track-img"></div>
                                                    <?php endif; ?>

                                                    <div class="track-main">
                                                        <div class="track-title"><?= htmlspecialchars($row['title']) ?></div>
                                                        <div class="track-sub">
                                                            <?= htmlspecialchars($row['artist_name']) ?>
                                                            <?= !empty($row['album_title']) ? ' · ' . htmlspecialchars($row['album_title']) : '' ?>
                                                        </div>
                                                    </div>

                                                    <div class="track-right">
                                                        <div class="track-dur"><?= htmlspecialchars(fmt_duration($row['duration_sec'])) ?></div>

                                                        <a class="btn btn-sm btn-outline-secondary" href="/MusicAll/public/song.php?id=<?= (int)$row['id'] ?>">Ver</a>

                                                        <?php if ($user && !empty($row['audio_url'])): ?>
                                                            <button
                                                                class="btn btn-sm btn-primary"
                                                                type="button"
                                                                onclick='MusicAllPlayer.playTrack(<?= json_encode([
                                                                                                        "id" => (int)$row["id"],
                                                                                                        "title" => $row["title"],
                                                                                                        "subtitle" => trim(($row["artist_name"] ?? "") . (!empty($row["album_title"]) ? " · " . $row["album_title"] : "")),
                                                                                                        "image" => $row["display_image"] ?? "",
                                                                                                        "audio_url" => $row["audio_url"]
                                                                                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>▶</button>
                                                        <?php elseif (!$user): ?>
                                                            <a class="btn btn-sm btn-outline-primary" href="/MusicAll/iniciarSesion.php">Login</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>

                <!-- Artistas -->
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Artistas sugeridos para ti</h2>
                            <?php if ($user): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="/MusicAll/public/artists.php">Ver más</a>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($artists)): ?>
                            <p class="text-muted mb-0">Aún no hay artistas.</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($artists as $a): ?>
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <a href="/MusicAll/public/artist.php?id=<?= (int)$a['id'] ?>" class="text-decoration-none text-dark">
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

            <!-- Columna derecha -->
            <aside class="side-card">

                <div class="card bg-glass shadow mb-3">
                    <div class="card-body p-3">
                        <div class="fw-semibold mb-2">Explorar</div>

                        <?php if (!$user): ?>
                            <div class="alert alert-info mb-0">
                                Inicia sesión para desbloquear el reproductor y ver conciertos con detalle.
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-2">
                                <a class="btn btn-dark text-start" href="/MusicAll/public/artists.php">Artistas →</a>
                                <a class="btn btn-dark text-start" href="/MusicAll/public/albums.php">Álbumes →</a>
                                <a class="btn btn-dark text-start" href="/MusicAll/public/concerts.php">Conciertos →</a>
                            </div>
                        <?php endif; ?>

                        <hr class="text-black-50">
                        <div class="mini-muted">
                            Tip: entra en Conciertos para ver próximos eventos por artista y comprar entradas.
                        </div>
                    </div>
                </div>

                <div class="card bg-glass shadow">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-semibold">Sugerencias</div>
                            <a class="btn btn-sm btn-outline-secondary" href="/MusicAll/public/concerts.php">Ver</a>
                        </div>

                        <?php if (empty($concs)): ?>
                            <p class="text-muted mb-0">No hay conciertos próximos.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($concs as $c): ?>
                                    <a class="list-group-item list-group-item-action"
                                        href="/MusicAll/public/concert.php?id=<?= (int)$c['id'] ?>">
                                        <div class="fw-semibold"><?= htmlspecialchars($c['artist_name']) ?></div>

                                        <?php if (!$user): ?>
                                            <div class="small text-muted">Inicia sesión para ver fecha y ciudad</div>
                                        <?php else: ?>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($c['city']) ?> · <?= htmlspecialchars($c['event_date']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </aside>

        </div>
    </main>

    <?php require __DIR__ . '/partials/player.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>