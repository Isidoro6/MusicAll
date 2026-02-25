<?php
require_once __DIR__ . '/../../db.php';

$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM artists";
$params = [];
if ($search !== '') {
    $sql .= " WHERE name LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param("s", $params[0]);
}

$stmt->execute();
$artists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<h3>Artistas</h3>

<form class="mb-3">
    <input type="hidden" name="section" value="artists">
    <input class="form-control" name="q" placeholder="Buscar artista..." value="<?= htmlspecialchars($search) ?>">
</form>

<a href="artist_create.php" class="btn btn-primary mb-3">+ Nuevo artista</a>

<table class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($artists as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td>
                <a href="artist_edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="post" action="artist_delete.php" class="d-inline" onsubmit="return confirm('¿Seguro?');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Borrar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>