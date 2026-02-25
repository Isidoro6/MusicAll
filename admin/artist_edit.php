<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$errors = [];
$csrf = $_SESSION['csrf_token'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php?section=artists");
    exit;
}

$stmt = $conn->prepare("SELECT id, name, bio, image_url FROM artists WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$artist = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$artist) {
    header("Location: index.php?section=artists");
    exit;
}

$name = $artist['name'] ?? '';
$bio = $artist['bio'] ?? '';
$image_url = $artist['image_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = "Token inválido.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if ($name === '' || mb_strlen($name) < 2) {
            $errors[] = "El nombre debe tener al menos 2 caracteres.";
        }

        if (!$errors) {
            $stmt = $conn->prepare("UPDATE artists SET name = ?, bio = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $bio, $image_url, $id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?section=artists");
                exit;
            }
            $stmt->close();
            $errors[] = "Error al actualizar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar artista | Admin</title>
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

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-12">
                <h1 class="display-6 fw-bold hero-title">Editar <span>Artista</span></h1>
                <p class="text-soft opacity-75 mb-0">ID: <?= (int)$id ?></p>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card bg-glass shadow">
                    <div class="card-body p-4">

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="artist_edit.php?id=<?= (int)$id ?>" autocomplete="off">
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
                                <button class="btn btn-primary" type="submit">Guardar cambios</button>
                                <a class="btn btn-outline-secondary" href="index.php?section=artists">Cancelar</a>
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