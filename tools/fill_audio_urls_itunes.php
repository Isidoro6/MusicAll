<?php
// MusicAll/tools/fill_audio_urls_itunes.php
// Ejecuta desde navegador: http://localhost/MusicAll/tools/fill_audio_urls_itunes.php
// O desde CLI: php tools/fill_audio_urls_itunes.php

require_once __DIR__ . '/../db.php';

header('Content-Type: text/plain; charset=utf-8');

function itunes_search_preview(string $term, string $country = 'ES'): ?array
{
    $url = "https://itunes.apple.com/search?media=music&entity=song&limit=1&country=" . urlencode($country) . "&term=" . urlencode($term);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'MusicAll/1.0 (localhost)'
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $code >= 400) return null;

    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['results'][0])) return null;

    $r = $data['results'][0];
    if (empty($r['previewUrl'])) return null;

    return [
        'previewUrl' => $r['previewUrl'],
        'trackName' => $r['trackName'] ?? null,
        'artistName' => $r['artistName'] ?? null,
    ];
}

// 1) Cargar canciones sin audio_url (o vacío)
$sql = "
  SELECT s.id, s.title, ar.name AS artist_name
  FROM songs s
  JOIN artists ar ON ar.id = s.artist_id
  WHERE (s.audio_url IS NULL OR s.audio_url = '')
  ORDER BY s.id ASC
  LIMIT 5000
";
$res = $conn->query($sql);
if (!$res) {
    echo "Error query: " . $conn->error . PHP_EOL;
    exit;
}
$rows = $res->fetch_all(MYSQLI_ASSOC);

if (!$rows) {
    echo "No hay canciones pendientes (audio_url ya relleno)." . PHP_EOL;
    exit;
}

$upd = $conn->prepare("UPDATE songs SET audio_url = ? WHERE id = ?");
if (!$upd) {
    echo "Error prepare UPDATE: " . $conn->error . PHP_EOL;
    exit;
}

$ok = 0;
$fail = 0;

foreach ($rows as $r) {
    $id = (int)$r['id'];
    $term = trim($r['title'] . " " . $r['artist_name']);

    $hit = itunes_search_preview($term, 'ES');
    if (!$hit) {
        $fail++;
        echo "[FAIL] #$id {$r['artist_name']} - {$r['title']}" . PHP_EOL;
        usleep(150000);
        continue;
    }

    $preview = $hit['previewUrl'];
    $upd->bind_param("si", $preview, $id);

    if ($upd->execute()) {
        $ok++;
        echo "[OK]   #$id {$r['artist_name']} - {$r['title']}  =>  $preview" . PHP_EOL;
    } else {
        $fail++;
        echo "[ERR]  #$id update DB: " . $conn->error . PHP_EOL;
    }

    // Pequeña pausa para no spamear
    usleep(150000);
}

$upd->close();
echo PHP_EOL . "Hecho. OK=$ok FAIL=$fail" . PHP_EOL;
