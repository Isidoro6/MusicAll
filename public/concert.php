<?php
require_once __DIR__ . '/../partials/app.php';
$GLOBALS['BASE_HOME'] = '/MusicAll/index.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: /MusicAll/public/concerts.php");
    exit;
}

$stmt = $conn->prepare("
  SELECT c.id, c.city, c.event_date, ar.id AS artist_id, ar.name AS artist_name
  FROM concerts c
  JOIN artists ar ON ar.id = c.artist_id
  WHERE c.id = ? LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$c = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$c) {
    header("Location: /MusicAll/public/concerts.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Concierto | MusicAll</title>
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
    </style>
</head>

<body>
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main class="container px-4 py-5 px-md-5 my-4">
        <h1 class="display-6 fw-bold hero-title mb-3">Concierto de <span><?= htmlspecialchars($c['artist_name']) ?></span></h1>

        <div class="card bg-glass shadow">
            <div class="card-body p-4">

                <?php if (!$user): ?>
                    <div class="alert alert-info">
                        Inicia sesión para ver los detalles del concierto y poder comprar entrada.
                        <div class="mt-2">
                            <a class="btn btn-primary btn-sm" href="/MusicAll/iniciarSesion.php">Iniciar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Artista</span><span class="fw-semibold"><?= htmlspecialchars($c['artist_name']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Ciudad</span><span class="fw-semibold"><?= htmlspecialchars($c['city']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Fecha</span><span class="fw-semibold"><?= htmlspecialchars($c['event_date']) ?></span>
                        </li>
                    </ul>

                    <a class="btn btn-primary" href="/MusicAll/public/buy_ticket.php?concert_id=<?= (int)$c['id'] ?>">
                        Comprar entrada
                    </a>
                <?php endif; ?>

                <a class="btn btn-outline-secondary ms-2" href="/MusicAll/public/concerts.php">Volver</a>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/player.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>