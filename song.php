<?php
session_start();
require_once __DIR__ . '/db.php';

$song_id = (int)($_GET['id'] ?? 0);
if ($song_id <= 0) {
    header("Location: songs.php");
    exit;
}

$stmt = $conn->prepare("
  SELECT
    s.id, s.title, s.duration_sec, s.audio_url, s.created_at,
    ar.id AS artist_id, ar.name AS artist_name, ar.image_url AS artist_image,
    al.id AS album_id, al.title AS album_title, al.cover_url AS album_cover,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  WHERE s.id = ? LIMIT 1
");
$stmt->bind_param("i", $song_id);
$stmt->execute();
$res = $stmt->get_result();
$song = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$song) {
    header("Location: songs.php");
    exit;
}

function fmt_duration($sec)
{
    if ($sec === null || $sec === '' || (int)$sec <= 0) return '—';
    $sec = (int)$sec;
    $m = intdiv($sec, 60);
    $s = $sec % 60;
    return sprintf("%d:%02d", $m, $s);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($song['title']) ?> | MusicAll</title>
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

        .cover {
            width: 100%;
            max-height: 340px;
            object-fit: cover;
            border-radius: 16px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicAll</a>
            <a class="btn btn-outline-light btn-sm" href="songs.php">Volver</a>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <?php if (!empty($song['display_image'])): ?>
                            <img class="cover mb-3" src="<?= htmlspecialchars($song['display_image']) ?>" alt="cover">
                        <?php endif; ?>

                        <h1 class="h4 fw-bold mb-1"><?= htmlspecialchars($song['title']) ?></h1>
                        <div class="text-muted mb-3">
                            <a class="text-decoration-none" href="artist.php?id=<?= (int)$song['artist_id'] ?>">
                                <?= htmlspecialchars($song['artist_name']) ?>
                            </a>
                            <?php if (!empty($song['album_id'])): ?>
                                · <a class="text-decoration-none" href="album.php?id=<?= (int)$song['album_id'] ?>">
                                    <?= htmlspecialchars($song['album_title']) ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2 small text-muted">
                            <div class="col-6">Duración: <strong><?= htmlspecialchars(fmt_duration($song['duration_sec'])) ?></strong></div>
                            <div class="col-6">ID: <strong><?= (int)$song['id'] ?></strong></div>
                        </div>

                        <hr>

                        <div class="fw-semibold mb-2">Reproducción</div>
                        <?php if (!empty($song['audio_url'])): ?>
                            <audio controls class="w-100">
                                <source src="<?= htmlspecialchars($song['audio_url']) ?>" type="audio/mpeg">
                                Tu navegador no soporta audio HTML5.
                            </audio>
                            <div class="small text-muted mt-2">Si el audio no carga, revisa que `audio_url` sea accesible.</div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                Esta canción aún no tiene audio asignado (campo <code>audio_url</code> vacío).
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-3">Ficha</h2>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Canción</span><span class="fw-semibold"><?= htmlspecialchars($song['title']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Artista</span>
                                <span class="fw-semibold">
                                    <a class="text-decoration-none" href="artist.php?id=<?= (int)$song['artist_id'] ?>"><?= htmlspecialchars($song['artist_name']) ?></a>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Álbum</span>
                                <span class="fw-semibold">
                                    <?= !empty($song['album_id']) ? htmlspecialchars($song['album_title']) : '—' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Duración</span><span class="fw-semibold"><?= htmlspecialchars(fmt_duration($song['duration_sec'])) ?></span>
                            </li>
                        </ul>

                        <div class="small text-muted mt-3">
                            Luego podemos añadir aquí: letra, género, año, créditos, etc.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>