<?php
session_start();
require_once __DIR__ . '/db.php';

$artist_id = (int)($_GET['id'] ?? 0);
if ($artist_id <= 0) {
    header("Location: artists.php");
    exit;
}

// Artista
$stmt = $conn->prepare("SELECT id, name, bio, image_url FROM artists WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$res = $stmt->get_result();
$artist = $res ? $res->fetch_assoc() : null;
$stmt->close();
if (!$artist) {
    header("Location: artists.php");
    exit;
}

// Álbumes del artista
$albums = [];
$stmt = $conn->prepare("SELECT id, title, cover_url FROM albums WHERE artist_id = ? ORDER BY release_date DESC, created_at DESC");
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$res = $stmt->get_result();
$albums = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// Álbum seleccionado
$selected_album_id = (int)($_GET['album_id'] ?? 0);
if ($selected_album_id <= 0 && !empty($albums)) {
    $selected_album_id = (int)$albums[0]['id'];
}

// Canciones (del álbum seleccionado si hay, si no todas del artista)
$songs = [];
if ($selected_album_id > 0) {
    $stmt = $conn->prepare("
    SELECT s.id, s.title,
      COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
    FROM songs s
    JOIN artists ar ON ar.id = s.artist_id
    LEFT JOIN albums al ON al.id = s.album_id
    WHERE s.artist_id = ? AND s.album_id = ?
    ORDER BY s.created_at DESC
  ");
    $stmt->bind_param("ii", $artist_id, $selected_album_id);
} else {
    $stmt = $conn->prepare("
    SELECT s.id, s.title,
      COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
    FROM songs s
    JOIN artists ar ON ar.id = s.artist_id
    LEFT JOIN albums al ON al.id = s.album_id
    WHERE s.artist_id = ?
    ORDER BY s.created_at DESC
  ");
    $stmt->bind_param("i", $artist_id);
}
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
    <title><?= htmlspecialchars($artist['name']) ?> | MusicAll</title>
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
            max-height: 260px;
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
            <a class="btn btn-outline-light btn-sm" href="artists.php">Volver</a>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <?php if (!empty($artist['image_url'])): ?>
                            <img class="cover mb-3" src="<?= htmlspecialchars($artist['image_url']) ?>" alt="img">
                        <?php endif; ?>
                        <h1 class="h4 fw-bold mb-2"><?= htmlspecialchars($artist['name']) ?></h1>
                        <?php if (!empty($artist['bio'])): ?>
                            <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($artist['bio'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted mb-0">Sin biografía por ahora.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card bg-glass shadow mt-4">
                    <div class="card-body p-4">
                        <div class="fw-semibold mb-2">Álbumes</div>
                        <?php if (empty($albums)): ?>
                            <p class="text-muted mb-0">Este artista no tiene álbumes.</p>
                        <?php else: ?>
                            <div class="d-grid gap-2">
                                <?php foreach ($albums as $al): ?>
                                    <a class="btn <?= ((int)$al['id'] === $selected_album_id) ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                        href="artist.php?id=<?= (int)$artist_id ?>&album_id=<?= (int)$al['id'] ?>">
                                        <?= htmlspecialchars($al['title']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-semibold mb-0">Canciones</h2>
                            <?php if ($selected_album_id > 0): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="album.php?id=<?= (int)$selected_album_id ?>">Ver álbum</a>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($songs)): ?>
                            <p class="text-muted mb-0">No hay canciones para este álbum.</p>
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
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?= htmlspecialchars($s['title']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($artist['name']) ?></div>
                                        </div>
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