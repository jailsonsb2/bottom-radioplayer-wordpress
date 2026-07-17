/* ============================================================
   Bottom Radio Player — Clip Mode add-on
   ------------------------------------------------------------
   Optional module, separate from radioplayer.js. When the
   now-playing API returns a `youtubeId` for the current track, a
   "Clipe" button appears in the player; turning it on opens a
   floating video mini-player synced to the song's elapsed time.
   Also handles mutual exclusion between radio audio and any
   embedded YouTube video on the page (playing one pauses the
   other). Listens to window.RadioPlayer / the `radioplayer:track`
   event exposed by radioplayer.js — no config needed.
   ============================================================ */
(function () {
    "use strict";

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text) node.textContent = text;
        return node;
    }

    // Sobrevive às trocas de página do seamless (que reexecutam este script)
    window.__videoState = window.__videoState || { resume: false, playing: new Set() };
    const videoState = window.__videoState;

    function handleYouTubeMessage(event) {
        if (!/(^|\.)youtube(-nocookie)?\.com$/.test((() => { try { return new URL(event.origin).hostname; } catch (e) { return ""; } })())) return;
        let data;
        try { data = JSON.parse(event.data); } catch (e) { return; }
        const state = data && data.info && typeof data.info.playerState === "number" ? data.info.playerState : null;
        if (state === null) return;

        const id = data.id || "yt";
        if (state === 1) { // tocando
            videoState.playing.add(id);
            if (window.RadioPlayer && !window.RadioPlayer.audio.paused) {
                videoState.resume = true;
                window.RadioPlayer.pause();
            }
        } else if (state === 2 || state === 0) { // pausado ou terminou
            videoState.playing.delete(id);
            if (videoState.resume && videoState.playing.size === 0 && window.RadioPlayer && window.RadioPlayer.audio.paused) {
                window.RadioPlayer.play();
                if (state === 0) videoState.resume = false;
            }
            if (state === 2 && localStorage.getItem("brp:clipmode") === "1") {
                localStorage.setItem("brp:clipmode", "0");
                const clipButton = window.RadioPlayer && window.RadioPlayer.root && window.RadioPlayer.root.querySelector(".player-button-clip");
                if (clipButton) clipButton.classList.remove("is-active");
                videoState.lastClipId = null;
                closeVideoDock();
            }
        }
    }

    if (window.__brpYtWatcher) window.removeEventListener("message", window.__brpYtWatcher);
    window.__brpYtWatcher = handleYouTubeMessage;
    window.addEventListener("message", window.__brpYtWatcher);

    function closeVideoDock() {
        const dock = document.getElementById("brp-video-dock");
        if (!dock) return;
        dock.remove();
        videoState.playing.clear();
        videoState.lastClipId = null;
        if (videoState.resume && window.RadioPlayer && window.RadioPlayer.audio.paused) {
            window.RadioPlayer.play();
        }
        videoState.resume = false;
    }

    function openVideoDock(video, index) {
        let dock = document.getElementById("brp-video-dock");
        if (!dock) {
            dock = el("div", "brp-video-dock");
            dock.id = "brp-video-dock";
            dock.setAttribute("data-seamless-keep", "");

            const header = el("div", "brp-video-dock-header");
            header.appendChild(el("span", "brp-video-dock-title"));
            const close = el("button", "brp-video-dock-close", "✕");
            close.type = "button";
            close.setAttribute("aria-label", "Fechar vídeo");
            close.addEventListener("click", closeVideoDock);
            header.appendChild(close);

            dock.appendChild(header);
            dock.appendChild(el("div", "brp-video-dock-body"));
            document.body.appendChild(dock);
        }

        dock.querySelector(".brp-video-dock-title").textContent = video.title;

        const body = dock.querySelector(".brp-video-dock-body");
        body.innerHTML = "";
        const iframe = el("iframe");
        iframe.src = `https://www.youtube-nocookie.com/embed/${video.id}?autoplay=1&enablejsapi=1` + (video.start ? `&start=${video.start}` : "");
        iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
        iframe.allowFullscreen = true;
        iframe.title = video.title;
        iframe.addEventListener("load", () => {
            const hello = JSON.stringify({ event: "listening", id: "video-" + index, channel: "widget" });
            iframe.contentWindow.postMessage(hello, "*");
        });
        body.appendChild(iframe);

        if (window.RadioPlayer && !window.RadioPlayer.audio.paused) {
            videoState.resume = true;
            window.RadioPlayer.pause();
        }
    }

    const CLIP_KEY = "brp:clipmode";
    const clipModeOn = () => localStorage.getItem(CLIP_KEY) === "1";

    function openClip(track) {
        if (!track.youtubeId || videoState.lastClipId === track.youtubeId) return;
        videoState.lastClipId = track.youtubeId;

        let start = 0;
        if (track.elapsed && track.receivedAt) {
            start = Math.floor(track.elapsed + (Date.now() - track.receivedAt) / 1000);
            if (track.duration && start >= track.duration - 5) start = 0;
            if (start < 8) start = 0;
        }

        openVideoDock({ id: track.youtubeId, title: track.title + " — " + track.artist, start }, "clip");
    }

    function ensureClipButton() {
        if (!window.RadioPlayer || !window.RadioPlayer.root) return;
        const right = window.RadioPlayer.root.querySelector(".player-right");
        if (!right || right.querySelector(".player-button-clip")) return;

        const button = el("button", "player-button player-button-clip");
        button.type = "button";
        button.title = "Modo clipe: mostra o clipe da música que está tocando";
        button.innerHTML = '<svg class="i" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="3"></rect><path d="m10 9 5 3-5 3z"></path></svg>Clipe';
        button.classList.toggle("is-active", clipModeOn());

        button.addEventListener("click", () => {
            const turningOn = !clipModeOn();
            localStorage.setItem(CLIP_KEY, turningOn ? "1" : "0");
            button.classList.toggle("is-active", turningOn);
            if (turningOn) {
                const track = window.RadioPlayer.currentTrack;
                if (track && track.youtubeId) openClip(track);
            } else {
                videoState.lastClipId = null;
                closeVideoDock();
            }
        });

        right.insertBefore(button, right.querySelector(".player-button-history"));
    }

    function onTrackChange(event) {
        const track = event.detail;
        if (track.youtubeId) ensureClipButton();
        if (!clipModeOn()) return;

        if (track.youtubeId) {
            openClip(track);
        } else {
            videoState.lastClipId = null;
            closeVideoDock();
        }
    }

    if (window.__brpClipWatcher) document.removeEventListener("radioplayer:track", window.__brpClipWatcher);
    window.__brpClipWatcher = onTrackChange;
    document.addEventListener("radioplayer:track", window.__brpClipWatcher);

    if (window.RadioPlayer && window.RadioPlayer.currentTrack && window.RadioPlayer.currentTrack.youtubeId) {
        ensureClipButton();
    }
})();
