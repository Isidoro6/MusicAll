<?php
// MusicAll/partials/header.php
// Requiere que exista $user (desde app.php)
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= htmlspecialchars($GLOBALS['BASE_HOME'] ?? '/MusicAll/index.php') ?>">
            <span class="fw-semibold">MusicAll</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($GLOBALS['BASE_HOME'] ?? '/MusicAll/index.php') ?>">Inicio</a>
                </li>

                <?php if (!$user): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/MusicAll/iniciarSesion.php">Iniciar sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <span class="nav-link">Hola, <?= htmlspecialchars($user['username']) ?></span>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="exploraDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Explora
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exploraDropdown">
                            <li><a class="dropdown-item" href="/MusicAll/public/artists.php">Artistas</a></li>
                            <li><a class="dropdown-item" href="/MusicAll/public/albums.php">Álbumes</a></li>
                            <li><a class="dropdown-item" href="/MusicAll/public/concerts.php">Conciertos</a></li>
                        </ul>
                    </li>

                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/MusicAll/admin/index.php">Admin</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/MusicAll/logout.php">Salir</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>