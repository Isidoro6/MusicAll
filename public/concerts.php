<?php
require_once __DIR__ . '/../partials/app.php';
$GLOBALS['BASE_HOME'] = '/MusicAll/index.php';

// Solo conciertos próximos y ordenados por fecha asc
$concs = [];
$res = $conn->query("
  SELECT c.id, c.city, c.event_date, ar.id AS artist_id, ar.name AS artist_name
  FROM concerts c
  JOIN artists ar ON ar.id = c.artist_id
  WHERE c.event_date >= CURDATE()
  ORDER BY c.event_date ASC, ar.name ASC
");
if ($res) $concs = $res->fetch_all(MYSQLI_ASSOC);

// Agrupar por artista
$byArtist = [];
foreach ($concs as $c) {
    $aid = (int)$c['artist_id'];
    if (!isset($byArtist[$aid])) {
        $byArtist[$aid] = [
            'artist_id' => $aid,
            'artist_name' => $c['artist_name'],
            'items' => []
        ];
    }
    $byArtist[$aid]['items'][] = $c;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Conciertos | MusicAll</title>
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
        <h1 class="display-6 fw-bold hero-title mb-3">Próximos <span>Conciertos</span></h1>

        <div class="card bg-glass shadow">
            <div class="card-body p-4">

                <?php if (empty($byArtist)): ?>
                    <p class="text-muted mb-0">No hay conciertos próximos.</p>
                <?php else: ?>
                    <div class="accordion" id="accConcerts">
                        <?php $n = 0;
                        foreach ($byArtist as $a): $n++; ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="h<?= $n ?>">
                                    <button class="accordion-button <?= $n === 1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#c<?= $n ?>">
                                        <?= htmlspecialchars($a['artist_name']) ?>
                                    </button>
                                </h2>
                                <div id="c<?= $n ?>" class="accordion-collapse collapse <?= $n === 1 ? 'show' : '' ?>" data-bs-parent="#accConcerts">
                                    <div class="accordion-body">
                                        <div class="list-group">
                                            <?php foreach ($a['items'] as $c): ?>
                                                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                    href="/MusicAll/public/concert.php?id=<?= (int)$c['id'] ?>">
                                                    <div>
                                                        <div class="fw-semibold">Ver concierto</div>
                                                        <?php if (!$user): ?>
                                                            <div class="small text-muted">Inicia sesión para ver fecha/ciudad y comprar</div>
                                                        <?php else: ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($c['city']) ?> · <?= htmlspecialchars($c['event_date']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="text-muted">→</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/player.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>