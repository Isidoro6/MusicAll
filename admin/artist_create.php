<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$errors = [];
$name = '';
$bio = '';
$image_url = '';
$csrf = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors[] = "Token inválido. Refresca la página e inténtalo de nuevo.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if ($name === '' || mb_strlen($name) < 2) {
            $errors[] = "El nombre debe tener al menos 2 caracteres.";
        }

        if (!$errors) {
            $stmt = $conn->prepare("INSERT INTO artists (name, bio, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $bio, $image_url);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: artists.php?success=" . urlencode("Artista creado correctamente."));
                exit;
            } else {
                if ($conn->errno === 1062) $errors[] = "Ya existe un artista con ese nombre.";
                else $errors[] = "Error al crear: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nuevo artista | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <style>
        :root {
            --bg1: hsl(218, 41%, 15%);
            --t1: hsl(218, 81%, 95%);
            --t2: hsl(218, 81%, 75%);
            --soft: hsl(218, 81%, 85%);
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

        .text-soft {
            color: var(--soft);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand" href="../index.php">MusicAll</a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="artists.php">Volver</a>
                <a class="btn btn-danger btn-sm" href="../logout.php">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-12">
                <h1 class="display-6 fw-bold hero-title">Nuevo <span>Artista</span></h1>
                <p class="text-soft opacity-75 mb-0">Crea un artista para asociarle álbumes, canciones y conciertos.</p>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">
                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="artist_create.php" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                            <div class="mb-3">
                                <label class="form-label" for="name">Nombre</label>
                                <input class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="image_url">Imagen (URL opcional)</label>
                                <input class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($image_url) ?>" placeholder="https://...">
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="bio">Biografía (opcional)</label>
                                <textarea class="form-control" id="bio" name="bio" rows="5"><?= htmlspecialchars($bio) ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit">Guardar</button>
                                <a class="btn btn-outline-secondary" href="artists.php">Cancelar</a>
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