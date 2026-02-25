<?php
require_once __DIR__ . '/../../db.php';

$csrf = $_SESSION['csrf_token'];
$q = trim($_GET['q'] ?? '');

$sql = "
  SELECT
    s.id, s.title, s.duration_sec, s.audio_url,
    ar.name AS artist_name,
    al.title AS album_title
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  LEFT JOIN albums al ON al.id = s.album_id
  WHERE 1=1
";

$params = [];
$types = '';

if ($q !== '') {
    $sql .= " AND (s.title LIKE ? OR ar.name LIKE ?) ";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= "ss";
}

$sql .= " ORDER BY s.id DESC ";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$songs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Canciones</h2>
    <a class="btn btn-primary" href="song_create.php">+ Nueva canción</a>
</div>

<form class="mb-3">
    <input type="hidden" name="section" value="songs">
    <input class="form-control" name="q" placeholder="Buscar canción (título o artista)..." value="<?= htmlspecialchars($q) ?>">
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
            <tr>
                <th style="width:80px;">ID</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Álbum</th>
                <th>Duración</th>
                <th>Audio</th>
                <th style="width:240px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$songs): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay canciones.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($songs as $s): ?>
                    <tr>
                        <td><?= (int)$s['id'] ?></td>
                        <td><?= htmlspecialchars($s['title'] ?? '') ?></td>
                        <td><?= htmlspecialchars($s['artist_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($s['album_title'] ?? '') ?></td>
                        <td><?= htmlspecialchars((string)($s['duration_sec'] ?? '')) ?></td>
                        <td class="text-truncate" style="max-width:220px;"><?= htmlspecialchars($s['audio_url'] ?? '') ?></td>
                        <td class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="song_edit.php?id=<?= (int)$s['id'] ?>">Editar</a>

                            <form method="post" action="song_delete.php" class="m-0"
                                onsubmit="return confirm('¿Seguro que quieres borrar esta canción?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>