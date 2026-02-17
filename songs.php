<?php
session_start();
require_once __DIR__ . '/db.php';

$songs = [];
$res = $conn->query("
  SELECT
    s.id, s.title,
    ar.name AS artist_name,
    al.title AS album_title,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  ORDER BY s.created_at DESC
  LIMIT 60
");
if ($res) $songs = $res->fetch_all(MYSQLI_ASSOC);
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
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicAll</a>
            <a class="btn btn-outline-light btn-sm" href="index.php">Volver</a>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <h1 class="display-6 fw-bold hero-title mb-3">Todas las <span>Canciones</span></h1>

        <div class="card bg-glass shadow">
            <div class="card-body p-4">
                <?php if (empty($songs)): ?>
                    <p class="text-muted mb-0">No hay canciones todavía.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($songs as $s): ?>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <a href="song.php?id=<?= (int)$s['id'] ?>" class="text-decoration-none text-dark">
                                    <div class="card h-100 media-card">
                                        <?php if (!empty($s['display_image'])): ?>
                                            <img class="card-img-top" src="<?= htmlspecialchars($s['display_image']) ?>" alt="img">
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
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>