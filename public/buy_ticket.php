<?php
session_start();
require_once __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;

$concert_id = (int)($_GET['concert_id'] ?? ($_POST['concert_id'] ?? 0));
if ($concert_id <= 0) {
    header("Location: concerts.php");
    exit;
}

// Traer concierto + artista
$stmt = $conn->prepare("
  SELECT
    c.id AS concert_id, c.city, c.venue, c.concert_date, c.price_eur,
    a.id AS artist_id, a.name AS artist_name
  FROM concerts c
  JOIN artists a ON a.id = c.artist_id
  WHERE c.id = ? LIMIT 1
");
$stmt->bind_param("i", $concert_id);
$stmt->execute();
$res = $stmt->get_result();
$concert = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$concert) {
    header("Location: concerts.php");
    exit;
}

$errors = [];
$success = null;

$buyer_name = '';
$buyer_email = '';
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buyer_name = trim($_POST['buyer_name'] ?? '');
    $buyer_email = trim($_POST['buyer_email'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($buyer_name === '' || mb_strlen($buyer_name) < 2) $errors[] = "El nombre debe tener al menos 2 caracteres.";
    if ($buyer_email === '' || !filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";
    if ($quantity <= 0 || $quantity > 10) $errors[] = "La cantidad debe estar entre 1 y 10.";

    if (!$errors) {
        $user_id = isset($user['id']) ? (int)$user['id'] : null;
        $unitPrice = (float)$concert['price_eur'];
        $total = $unitPrice * (float)$quantity;

        $stmt = $conn->prepare("
          INSERT INTO ticket_orders (concert_id, user_id, buyer_name, buyer_email, quantity, total_price_eur)
          VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissid", $concert_id, $user_id, $buyer_name, $buyer_email, $quantity, $total);

        if ($stmt->execute()) {
            $success = "Compra registrada (simulación). ¡Entrada reservada!";
            // limpiamos form
            $buyer_name = '';
            $buyer_email = '';
            $quantity = 1;
        } else {
            $errors[] = "No se pudo completar la compra: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comprar entrada | MusicAll</title>

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
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="../index.php">MusicAll</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="concerts.php?artist_id=<?= (int)$concert['artist_id'] ?>">Volver</a>
            </div>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="mb-3">
            <h1 class="display-6 fw-bold hero-title">Comprar <span>entrada</span></h1>
            <div class="text-white-50">
                <?= htmlspecialchars($concert['artist_name']) ?> · <?= htmlspecialchars($concert['city']) ?>
                <?= !empty($concert['concert_date']) ? ' · ' . htmlspecialchars($concert['concert_date']) : '' ?>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-3">Resumen</h2>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Artista</span>
                                <span class="fw-semibold"><?= htmlspecialchars($concert['artist_name']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Ciudad</span>
                                <span class="fw-semibold"><?= htmlspecialchars($concert['city']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Fecha</span>
                                <span class="fw-semibold"><?= htmlspecialchars($concert['concert_date']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Sala</span>
                                <span class="fw-semibold"><?= htmlspecialchars($concert['venue'] ?? '—') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Precio</span>
                                <span class="fw-semibold"><?= htmlspecialchars(number_format((float)$concert['price_eur'], 2)) ?> €</span>
                            </li>
                        </ul>
                        <div class="small text-muted mt-3">
                            Esto es una compra simulada: se registra en <code>ticket_orders</code>.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-3">Datos del comprador</h2>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="buy_ticket.php">
                            <input type="hidden" name="concert_id" value="<?= (int)$concert_id ?>">

                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input class="form-control" name="buyer_name" value="<?= htmlspecialchars($buyer_name) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="buyer_email" type="email" value="<?= htmlspecialchars($buyer_email) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Cantidad (1–10)</label>
                                <input class="form-control" name="quantity" type="number" min="1" max="10" value="<?= (int)$quantity ?>" required>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">
                                Confirmar compra
                            </button>

                            <div class="text-center mt-3">
                                <a class="text-decoration-none" href="concerts.php?artist_id=<?= (int)$concert['artist_id'] ?>">← Volver a conciertos</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>