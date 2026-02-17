<?php
// admin/auth.php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../iniciarSesion.php");
    exit;
}

if (($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// CSRF token simple para formularios del admin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
