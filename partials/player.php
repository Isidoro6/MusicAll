<?php
// partials/player.php
$user = $_SESSION['user'] ?? null;
?>
<link rel="stylesheet" href="/MusicAll/public/assets/player.css">

<div id="globalPlayer" class="player">
    <div class="player__left">
        <img id="plCover" class="player__cover" src="" alt="" style="display:none;">
        <div class="player__meta">
            <div id="plTitle" class="player__title">Reproductor</div>
            <div id="plArtist" class="player__artist">
                <?= $user ? 'Listo para reproducir' : 'Inicia sesión para escuchar música' ?>
            </div>
        </div>
    </div>

    <div class="player__center">
        <button id="plPrev" class="player__btn" <?= $user ? '' : 'disabled' ?>>⏮</button>
        <button id="plPlay" class="player__btn player__btn--main" <?= $user ? '' : 'disabled' ?>>▶</button>
        <button id="plNext" class="player__btn" <?= $user ? '' : 'disabled' ?>>⏭</button>
    </div>

    <div class="player__right">
        <span id="plTime" class="player__time">0:00</span>
        <input id="plSeek" class="player__seek" type="range" min="0" max="100" value="0" <?= $user ? '' : 'disabled' ?>>
        <span id="plDur" class="player__time">0:00</span>
        <input id="plVol" class="player__vol" type="range" min="0" max="100" value="90" <?= $user ? '' : 'disabled' ?>>
    </div>
</div>

<audio id="plAudio" preload="metadata"></audio>

<script>
    window.__PLAYER_LOGGED__ = <?= $user ? 'true' : 'false' ?>;
</script>
<script src="/MusicAll/public/assets/player.js"></script>