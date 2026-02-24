<?php
// public/api/player_queue.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    http_response_code(401);
    echo json_encode(["error" => "LOGIN_REQUIRED"]);
    exit;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 30;
if ($limit <= 0 || $limit > 200) $limit = 30;

// Solo canciones con audio_url válido
$sql = "
  SELECT
    s.id,
    s.title,
    ar.name AS artist_name,
    COALESCE(s.image_url, al.cover_url, ar.image_url) AS display_image,
    s.audio_url
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  WHERE s.audio_url IS NOT NULL 
    AND s.audio_url <> ''
  ORDER BY s.created_at DESC
  LIMIT ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

echo json_encode(["items" => $rows], JSON_UNESCAPED_UNICODE);
