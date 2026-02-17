<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$errors = [];
$csrf = $_SESSION['csrf_token'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: songs.php?error=" . urlencode("ID inválido."));
    exit;
}

// Cargar canción
$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$song = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$song) {
    header("Location: songs.php?error=" . urlencode("Canción no encontrada."));
    exit;
}

$title = $song['title'] ?? '';
$artist_id = (string)($song['artist_id'] ?? '');
$album_id = $song['album_id']; // puede ser null
$duration_sec = $song['duration_sec'];
$image_url = $song['image_url'] ?? '';
$audio_url = $song['audio_url'] ?? '';

// Selects
$artists = [];
$albums = [];

$res = $conn->query("SELECT id, name FROM artists ORDER BY name ASC");
if ($res) $artists = $res->fetch_all(MYSQLI_ASSOC);

$res = $conn->query("
  SELECT al.id, al.title, ar.name AS artist_name
  FROM albums al
  JOIN artists ar ON ar.id = al.artist_id
  ORDER BY ar.name ASC, al.title ASC
");
if ($res) $albums = $res->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors[] = "Token inválido. Refresca la página e inténtalo de nuevo.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $artist_id_int = (int)($_POST['artist_id'] ?? 0);

        $album_id_raw = trim($_POST['album_id'] ?? '');
        $album_id = ($album_id_raw === '') ? null : (int)$album_id_raw;

        $duration_raw = trim($_POST['duration_sec'] ?? '');
        $duration_sec = ($duration_raw === '') ? null : (int)$duration_raw;

        $image_url = trim($_POST['image_url'] ?? '');
        $audio_url = trim($_POST['audio_url'] ?? '');

        if ($title === '' || mb_strlen($title) < 2) $errors[] = "El título debe tener al menos 2 caracteres.";
        if ($artist_id_int <= 0) $errors[] = "Selecciona un artista.";

        if (!$errors) {
            $stmt = $conn->prepare("
        UPDATE songs
        SET artist_id = ?, album_id = ?, title = ?, duration_sec = ?, image_url = ?, audio_url = ?
        WHERE id = ?
      ");
            $stmt->bind_param(
                "iisissi",
                $artist_id_int,
                $album_id,
                $title,
                $duration_sec,
                $image_url,
                $audio_url,
                $id
            );

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: songs.php?success=" . urlencode("Canción actualizada."));
                exit;
            } else {
                $errors[] = "Error al actualizar: " . $conn->error;
            }
            $stmt->close();
        }

        $artist_id = (string)$artist_id_int;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar canción | Admin</title>

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
                <a class="btn btn-outline-light btn-sm" href="songs.php">Volver</a>
                <a class="btn btn-danger btn-sm" href="../logout.php">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <main class="container px-4 py-5 px-md-5 my-4">
        <div class="row g-4">
            <div class="col-12">
                <h1 class="display-6 fw-bold hero-title">Editar <span>Canción</span></h1>
                <p class="text-soft opacity-75 mb-0">ID: <?= (int)$id ?></p>
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

                        <form method="post" action="song_edit.php?id=<?= (int)$id ?>" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                            <div class="mb-3">
                                <label class="form-label" for="title">Título</label>
                                <input class="form-control" id="title" name="title" required value="<?= htmlspecialchars($title) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="artist_id">Artista</label>
                                <select class="form-select" id="artist_id" name="artist_id" required>
                                    <option value="">— Selecciona artista —</option>
                                    <?php foreach ($artists as $a): ?>
                                        <option value="<?= (int)$a['id'] ?>" <?= ((string)$artist_id === (string)$a['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($a['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="album_id">Álbum (opcional)</label>
                                <select class="form-select" id="album_id" name="album_id">
                                    <option value="">— Sin álbum —</option>
                                    <?php foreach ($albums as $al): ?>
                                        <option value="<?= (int)$al['id'] ?>" <?= ((string)$album_id === (string)$al['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($al['artist_name'] . " — " . $al['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="duration_sec">Duración (segundos)</label>
                                    <input class="form-control" id="duration_sec" name="duration_sec" inputmode="numeric" value="<?= htmlspecialchars((string)$duration_sec) ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="image_url">Imagen de la canción (URL opcional)</label>
                                    <input class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($image_url) ?>" placeholder="https://...">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label" for="audio_url">Audio (URL opcional, para más adelante)</label>
                                <input class="form-control" id="audio_url" name="audio_url" value="<?= htmlspecialchars($audio_url) ?>" placeholder="https://...">
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button class="btn btn-primary" type="submit">Guardar cambios</button>
                                <a class="btn btn-outline-secondary" href="songs.php">Cancelar</a>
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