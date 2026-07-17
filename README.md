# 🎵 Bottom Radio Player — WordPress Plugin

[![Core component](https://img.shields.io/badge/core_component-bottom__radioplayer-5A0FC8)](https://github.com/jailsonsb2/bottom_radioplayer)
[![Not on wordpress.org](https://img.shields.io/badge/wordpress.org-not_published-orange)](#installation)

WordPress plugin wrapper for **[bottom_radioplayer](https://github.com/jailsonsb2/bottom_radioplayer)** — a bottom-bar HTML5 radio player whose signature feature is **seamless navigation**: internal link clicks swap only the page content, so **the audio never stops** while visitors browse the site.

This plugin adds a proper **wp-admin settings page** on top of the core component — configure stations, behavior and appearance entirely from the browser, no file editing required.

> This plugin is **not published on wordpress.org**. Install it manually from this repository (see below).

### Screenshot

![Bottom Radio Player running on WordPress, with Clip Mode open](https://i.imgur.com/hdTgRIH.png)

### Features

- **Settings page** (General / Stations / Appearance tabs) under a "Radio Player" admin menu.
- **Stations repeater** — add/remove stations, images picked via the native WordPress media library.
- Automatically **skips `/wp-admin/`** — the player is never loaded on admin screens.
- **Seamless navigation** — internal link clicks swap page content via AJAX, audio keeps playing. Also auto-excludes `/wp-admin/` and `wp-login.php` links from interception.
- **Clip Mode** — when the now-playing API returns a YouTube video ID for the current song, a "Clipe" button appears automatically and opens a floating video mini-player synced to the song's position. No configuration needed.
- **Custom CSS** field to override the player's appearance.
- Clean uninstall (removes its own options, nothing else).

### Installation

1. Download **[`bottom-radioplayer.zip`](bottom-radioplayer.zip)** from this repository (or clone the repo — the zip is already built at the root, ready to upload).
2. In your WordPress admin: **Plugins → Add New → Upload Plugin**, choose the zip, **Install Now**, then **Activate**.
   - Alternative: extract the folder directly into `wp-content/plugins/` via FTP/file manager.
3. Go to the new **Radio Player** menu in wp-admin and fill in the **General**, **Stations** and **Appearance** tabs — Save.
4. The player appears automatically on the front-end of every page.

> ⚠️ **The player will not appear until you configure at least one station** (name + stream URL, on the Stations tab) **and save.** With no stations set, there's nothing to play, so the plugin doesn't load anything on the front-end — the settings page shows a reminder about this until you do.

Updating: repeat steps 1–2 with a newer `bottom-radioplayer.zip`. Settings are stored in the WordPress database and survive the update.

### Customizing the appearance (Custom CSS)

The **Appearance** tab has a raw CSS textarea, printed on every front-end page inside a `<style>` tag placed at the very end of `<body>` — after the player's own default styles, so your rules win the cascade without needing `!important` in most cases (a few base rules already use `!important` themselves, like the play button's background; matching that in your override may be needed for those specific properties).

**What you can do:** anything CSS can do — colors, spacing, fonts, sizes, animations, hide/show elements (`display: none`), `@media` queries, `@font-face`/`@import` for a custom font, etc.
**What you can't do:** add or remove HTML elements, change player behavior/JS, or fix layout bugs that aren't CSS — this field only restyles what's already there.

Always scope your selectors under `#app-player` (the player's root element) — the `<style>` tag is global to the page, so unscoped selectors (e.g. a bare `button { ... }`) can leak into the rest of your site.

**Key selectors:**

| Element | Selector |
|---|---|
| Player root / dock | `#app-player .player` |
| Play/pause button | `.player-button-play` |
| Previous / next buttons | `.player-button-backward-step` / `.player-button-forward-step` |
| Volume, history, share, lyrics, stations buttons | `.player-right button.player-button` |
| Clip Mode button | `.player-button-clip` |
| Live TV button (`tv_url`) | `.player-button-tv` |
| Station list items | `.station` (active one: `.station.is-active`) |
| Station cover/logo | `.player-artwork img`, `.player-station img` |
| Song title / artist | `.song-name`, `.song-artist` |
| Dropdowns (volume) | `.dropdown` |
| Panels (history, stations list) | `.offcanvas-player` |
| Modals (share, lyrics) | `.player-modal`, `.modal-content` |
| Clip Mode video mini-player | `.brp-video-dock` |
| Live TV fullscreen modal | `.modal-video` |

**The accent color:** the player extracts a dominant color from the current cover art and exposes it as the CSS custom property `--accent` on the player root — most colored details (play button gradient, active station border, top hairline, clip video border) read from it via `var(--accent, <fallback>)`. To pin a fixed color instead of letting it change per song:

```css
#app-player { --accent: #ff6600; }
```

**Other examples:**

```css
/* Hide the live TV button */
#app-player .player-button-tv { display: none; }

/* Make the play button bigger */
#app-player .player-button-play { padding: 1.4rem; }

/* Square corners instead of rounded */
#app-player .player { border-radius: 0; }
```

### Languages

The settings page follows your WordPress admin language automatically — no configuration needed. Currently translated: **English** (default, no file needed — it's the source language), **Português (pt_BR, pt_PT)**, **Español (es_ES)**. Missing your language? Translate `languages/bottom-radioplayer.pot` and open a pull request.

### Relationship to the core project

The player component itself (`js/radioplayer.js`, `css/`, seamless navigation, now-playing metadata, etc.) is developed in **[jailsonsb2/bottom_radioplayer](https://github.com/jailsonsb2/bottom_radioplayer)** — that's also where the plain (non-WordPress) drop-in install, the live demo, and the full feature list live. This repository only adds the WordPress-specific admin/settings layer around it.

### Support and Contributions

- Questions or issues about the WordPress plugin: open an issue in this repository.
- Issues about the player component itself (audio, seamless navigation, metadata, etc.): use the [core repository](https://github.com/jailsonsb2/bottom_radioplayer/issues).
- Contributions are welcome via pull request.
