<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: albums.php?error=" . urlencode("Método no permitido."));
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
    header("Location: albums.php?error=" . urlencode("Token inválido."));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: albums.php?error=" . urlencode("ID inválido."));
    exit;
}

// Al borrar álbum, las canciones se quedan con album_id = NULL por FK ON DELETE SET NULL
$stmt = $conn->prepare("DELETE FROM albums WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: albums.php?success=" . urlencode("Álbum eliminado."));
    exit;
}

$stmt->close();
header("Location: albums.php?error=" . urlencode("No se pudo eliminar: " . $conn->error));
exit;
