<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: artists.php?error=" . urlencode("Método no permitido."));
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
    header("Location: artists.php?error=" . urlencode("Token inválido."));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: artists.php?error=" . urlencode("ID inválido."));
    exit;
}

$stmt = $conn->prepare("DELETE FROM artists WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: artists.php?success=" . urlencode("Artista eliminado."));
    exit;
}

$stmt->close();
header("Location: artists.php?error=" . urlencode("No se pudo eliminar: " . $conn->error));
exit;
