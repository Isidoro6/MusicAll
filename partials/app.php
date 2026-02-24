<?php
// MusicAll/partials/app.php
session_start();
require_once __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;

function fmt_duration($sec): string
{
    if ($sec === null || $sec === '' || (int)$sec <= 0) return '—';
    $sec = (int)$sec;
    $m = intdiv($sec, 60);
    $s = $sec % 60;
    return sprintf("%d:%02d", $m, $s);
}
