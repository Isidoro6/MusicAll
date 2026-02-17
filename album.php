<?php
session_start();
require_once __DIR__ . '/db.php';

$album_id = (int)($_GET['id'] ?? 0);
if ($album_id <= 0) {
    header("Location: albums.php");
    exit;
}

$stmt = $conn->prepare("
  SELECT al.id, al.title, al.cover_url, al.release_date,
         ar.id AS artist_id, ar.name AS artist_name, ar.image_url AS artist_image
  FROM albums al
  JOIN artists ar ON ar.id = al.artist_id
  WHERE al.id = ? LIMIT 1
");
$stmt->bind_param("i", $album_id);
$stmt->execute();
$res = $stmt->get_result();
$album = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$album) {
    header("Location: albums.php");
    exit;
}

$songs = [];
$stmt = $conn->prepare("
  SELECT s.id, s.title,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  WHERE s.album_id = ?
  ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $album_id);
$stmt->execute();
$res = $stmt->get_result();
$songs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($album['title']) ?> | MusicAll</title>
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
            max-height: 280px;
            object-fit: cover;
            border-radius: 14px;
        }

        .song-row img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 12px;
        }

        .song-ph {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: rgba(0, 0, 0, .06);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicAll</a>
            <a class="btn btn-outline-light btn-sm" href="albums.php">Volver</a>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <?php if (!empty($album['cover_url'])): ?>
                            <img class="cover mb-3" src="<?= htmlspecialchars($album['cover_url']) ?>" alt="cover">
                        <?php endif; ?>
                        <h1 class="h4 fw-bold mb-1"><?= htmlspecialchars($album['title']) ?></h1>
                        <div class="text-muted mb-2">
                            <a class="text-decoration-none" href="artist.php?id=<?= (int)$album['artist_id'] ?>">
                                <?= htmlspecialchars($album['artist_name']) ?>
                            </a>
                        </div>
                        <div class="small text-muted">
                            <?= !empty($album['release_date']) ? 'Fecha: ' . htmlspecialchars($album['release_date']) : 'Sin fecha' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-3">Canciones del álbum</h2>

                        <?php if (empty($songs)): ?>
                            <p class="text-muted mb-0">Este álbum no tiene canciones.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($songs as $s): ?>
                                    <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 song-row"
                                        href="song.php?id=<?= (int)$s['id'] ?>">
                                        <?php if (!empty($s['display_image'])): ?>
                                            <img src="<?= htmlspecialchars($s['display_image']) ?>" alt="img">
                                        <?php else: ?>
                                            <div class="song-ph">♪</div>
                                        <?php endif; ?>
                                        <div class="fw-semibold flex-grow-1"><?= htmlspecialchars($s['title']) ?></div>
                                        <div class="text-muted">→</div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>