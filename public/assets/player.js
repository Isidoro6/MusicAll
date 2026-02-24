(() => {
    const logged = !!window.__PLAYER_LOGGED__;
    const audio = document.getElementById('plAudio');

    const elCover = document.getElementById('plCover');
    const elTitle = document.getElementById('plTitle');
    const elArtist = document.getElementById('plArtist');
    const elPlay = document.getElementById('plPlay');
    const elPrev = document.getElementById('plPrev');
    const elNext = document.getElementById('plNext');
    const elSeek = document.getElementById('plSeek');
    const elVol = document.getElementById('plVol');
    const elTime = document.getElementById('plTime');
    const elDur = document.getElementById('plDur');

    let queue = [];
    let idx = 0;
    let ready = false;

    const fmt = (sec) => {
        if (!isFinite(sec)) return "0:00";
        sec = Math.max(0, Math.floor(sec));
        const m = Math.floor(sec / 60);
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    };

    async function loadQueue() {
        if (!logged) return;

        const res = await fetch('/MusicAll/public/api/player_queue.php?limit=60', { credentials: 'same-origin' });
        if (!res.ok) {
            elArtist.textContent = 'No se pudo cargar música';
            return;
        }
        const data = await res.json();
        queue = (data.items || []).filter(x => x.audio_url && x.audio_url.trim() !== '');

        if (queue.length === 0) {
            elTitle.textContent = 'Reproductor';
            elArtist.textContent = 'No hay canciones reproducibles (audio_url vacío)';
            ready = false;
            return;
        }

        ready = true;
        setTrack(0, false);
    }

    function setTrack(newIdx, autoplay = false) {
        if (!ready) return;
        idx = (newIdx + queue.length) % queue.length;
        const t = queue[idx];

        elTitle.textContent = t.title || 'Sin título';
        elArtist.textContent = t.artist_name || '';
        if (t.display_image) {
            elCover.src = t.display_image;
            elCover.style.display = '';
        } else {
            elCover.style.display = 'none';
        }

        audio.src = t.audio_url;
        audio.load();

        if (autoplay) {
            audio.play().catch(() => { });
            elPlay.textContent = '⏸';
        } else {
            elPlay.textContent = '▶';
        }
    }

    function togglePlay() {
        if (!ready) return;

        if (audio.paused) {
            audio.play().then(() => {
                elPlay.textContent = '⏸';
            }).catch(() => { });
        } else {
            audio.pause();
            elPlay.textContent = '▶';
        }
    }

    function next(autoplay = true) {
        setTrack(idx + 1, autoplay);
    }

    function prev(autoplay = true) {
        setTrack(idx - 1, autoplay);
    }

    // Eventos
    elPlay?.addEventListener('click', togglePlay);
    elNext?.addEventListener('click', () => next(true));
    elPrev?.addEventListener('click', () => prev(true));

    elVol?.addEventListener('input', () => {
        audio.volume = Math.max(0, Math.min(1, (parseInt(elVol.value, 10) || 0) / 100));
    });
    audio.volume = 0.9;

    audio.addEventListener('loadedmetadata', () => {
        elDur.textContent = fmt(audio.duration);
    });

    audio.addEventListener('timeupdate', () => {
        elTime.textContent = fmt(audio.currentTime);
        if (isFinite(audio.duration) && audio.duration > 0) {
            const p = Math.floor((audio.currentTime / audio.duration) * 100);
            elSeek.value = String(p);
        }
    });

    elSeek?.addEventListener('input', () => {
        if (!isFinite(audio.duration) || audio.duration <= 0) return;
        const p = (parseInt(elSeek.value, 10) || 0) / 100;
        audio.currentTime = audio.duration * p;
    });

    audio.addEventListener('ended', () => next(true));

    // Arranque
    loadQueue().catch(() => {
        if (logged) elArtist.textContent = 'Error cargando música';
    });
})();