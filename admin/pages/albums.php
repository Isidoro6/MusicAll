<?php
require_once __DIR__ . '/../../db.php';

$csrf = $_SESSION['csrf_token'];

$search = trim($_GET['q'] ?? '');
$artist_filter = (int)($_GET['artist'] ?? 0);

// Cargar artistas para filtro
$artists = [];
$res = $conn->query("SELECT id, name FROM artists ORDER BY name ASC");
if ($res) $artists = $res->fetch_all(MYSQLI_ASSOC);

// Query albums
$sql = "
  SELECT al.id, al.title, al.release_date, al.cover_url, al.artist_id,
         ar.name AS artist_name
  FROM albums al
  JOIN artists ar ON ar.id = al.artist_id
  WHERE 1=1
";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND al.title LIKE ? ";
    $params[] = "%$search%";
    $types .= 's';
}

if ($artist_filter > 0) {
    $sql .= " AND al.artist_id = ? ";
    $params[] = $artist_filter;
    $types .= 'i';
}

$sql .= " ORDER BY ar.name ASC, al.title ASC ";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$albums = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Álbumes</h2>
    <a class="btn btn-primary" href="album_create.php">+ Nuevo álbum</a>
</div>

<form class="row g-2 mb-3">
    <input type="hidden" name="section" value="albums">

    <div class="col-md-6">
        <input class="form-control" name="q" placeholder="Buscar álbum por título..." value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="col-md-6">
        <select class="form-select" name="artist" onchange="this.form.submit()">
            <option value="0">Filtrar por cantante (todos)</option>
            <?php foreach ($artists as $a): ?>
                <option value="<?= (int)$a['id'] ?>" <?= ($artist_filter === (int)$a['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
            <tr>
                <th style="width:80px;">ID</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Fecha</th>
                <th>Cover</th>
                <th style="width:240px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$albums): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay álbumes.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($albums as $al): ?>
                    <tr>
                        <td><?= (int)$al['id'] ?></td>
                        <td><?= htmlspecialchars($al['title'] ?? '') ?></td>
                        <td><?= htmlspecialchars($al['artist_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($al['release_date'] ?? '') ?></td>
                        <td class="text-truncate" style="max-width:260px;"><?= htmlspecialchars($al['cover_url'] ?? '') ?></td>
                        <td class="d-flex gap-2">
                            <!-- ✅ CORRECTO: editar álbum -->
                            <a class="btn btn-sm btn-outline-primary" href="album_edit.php?id=<?= (int)$al['id'] ?>">Editar</a>

                            <form method="post" action="album_delete.php" class="m-0"
                                onsubmit="return confirm('¿Seguro que quieres borrar este álbum?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int)$al['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>