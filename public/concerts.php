<?php
session_start();
require_once __DIR__ . '/../db.php';

$q = trim($_GET['q'] ?? '');
$artist_id_focus = (int)($_GET['artist_id'] ?? 0);

// Solo artistas con concierto próximo:
// seleccionamos el PRÓXIMO concierto por artista y ordenamos por fecha ASC
$sql = "
  SELECT
    a.id, a.name, a.image_url,
    c.id AS concert_id, c.city, c.venue, c.concert_date, c.price_eur
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
";

$params = [];
$types = "";

if ($q !== '') {
    $sql .= " WHERE a.name LIKE ? ";
    $params[] = "%" . $q . "%";
    $types .= "s";
}

$sql .= " ORDER BY c.concert_date ASC, a.name ASC";

$artists = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
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

        .artist-img {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            object-fit: cover;
        }

        .ph {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(0, 0, 0, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #555;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="../index.php">MusicAll</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="../index.php">Volver</a>
            </div>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
            <div>
                <h1 class="display-6 fw-bold hero-title mb-1">Buscar <span>Conciertos</span></h1>
                <div class="text-white-50">Mostramos solo artistas con concierto próximo, ordenados por fecha.</div>
            </div>

            <form class="d-flex gap-2" method="get" action="concerts.php">
                <input class="form-control" style="max-width:260px" name="q" placeholder="Buscar artista..." value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </form>
        </div>

        <div class="card bg-glass shadow">
            <div class="card-body p-4">
                <?php if (empty($artists)): ?>
                    <p class="text-muted mb-0">No hay conciertos próximos para mostrar.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($artists as $a): ?>
                            <button type="button"
                                class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                data-bs-toggle="modal"
                                data-bs-target="#concertModal"
                                data-artist-id="<?= (int)$a['id'] ?>"
                                data-artist-name="<?= htmlspecialchars($a['name'], ENT_QUOTES) ?>"
                                data-concert-id="<?= (int)($a['concert_id'] ?? 0) ?>"
                                data-city="<?= htmlspecialchars(($a['city'] ?? ''), ENT_QUOTES) ?>"
                                data-venue="<?= htmlspecialchars(($a['venue'] ?? ''), ENT_QUOTES) ?>"
                                data-date="<?= htmlspecialchars(($a['concert_date'] ?? ''), ENT_QUOTES) ?>"
                                data-price="<?= htmlspecialchars((string)($a['price_eur'] ?? ''), ENT_QUOTES) ?>">
                                <?php if (!empty($a['image_url'])): ?>
                                    <img class="artist-img" src="<?= htmlspecialchars($a['image_url']) ?>" alt="img">
                                <?php else: ?>
                                    <div class="ph">♪</div>
                                <?php endif; ?>

                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($a['name']) ?></div>
                                    <div class="small text-muted">
                                        Próximo concierto: <?= htmlspecialchars($a['city']) ?>
                                        <?= !empty($a['concert_date']) ? ' · ' . htmlspecialchars($a['concert_date']) : '' ?>
                                    </div>
                                </div>

                                <div class="text-muted">→</div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Modal -->
    <div class="modal fade" id="concertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Concierto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBodyContent" class="text-muted">Cargando...</div>
                </div>
                <div class="modal-footer">
                    <a id="buyBtn" class="btn btn-primary d-none" href="#">Comprar entrada</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        const modal = document.getElementById('concertModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBodyContent');
        const buyBtn = document.getElementById('buyBtn');

        modal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;

            const artistName = btn.getAttribute('data-artist-name');
            const concertId = parseInt(btn.getAttribute('data-concert-id') || '0', 10);

            const city = btn.getAttribute('data-city') || '';
            const venue = btn.getAttribute('data-venue') || '';
            const date = btn.getAttribute('data-date') || '';
            const price = btn.getAttribute('data-price') || '';

            modalTitle.textContent = artistName;

            const venueLine = venue ? `<div><strong>Sala:</strong> ${venue}</div>` : '';
            const priceLine = price !== '' ? `<div><strong>Precio:</strong> ${price} €</div>` : '';

            modalBody.innerHTML = `
                <div><strong>Ciudad:</strong> ${city || '—'}</div>
                <div><strong>Fecha:</strong> ${date || '—'}</div>
                ${venueLine}
                ${priceLine}
                <div class="small text-muted mt-2">Pulsa comprar para simular la compra de la entrada.</div>
            `;

            buyBtn.classList.remove('d-none');
            buyBtn.setAttribute('href', `buy_ticket.php?concert_id=${concertId}`);
        });

        // Si vienes desde index con ?artist_id=..., abrimos modal automáticamente
        (function() {
            const focusArtistId = <?= (int)$artist_id_focus ?>;
            if (!focusArtistId) return;

            const buttons = document.querySelectorAll('[data-bs-target="#concertModal"]');
            for (const b of buttons) {
                if (parseInt(b.getAttribute('data-artist-id'), 10) === focusArtistId) {
                    b.click();
                    break;
                }
            }
        })();
    </script>
</body>

</html>