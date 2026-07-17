# 🎵 Bottom Radio Player — WordPress Plugin

[![Core component](https://img.shields.io/badge/core_component-bottom__radioplayer-5A0FC8)](https://github.com/jailsonsb2/bottom_radioplayer)
[![Not on wordpress.org](https://img.shields.io/badge/wordpress.org-not_published-orange)](#installation)

WordPress plugin wrapper for **[bottom_radioplayer](https://github.com/jailsonsb2/bottom_radioplayer)** — a bottom-bar HTML5 radio player whose signature feature is **seamless navigation**: internal link clicks swap only the page content, so **the audio never stops** while visitors browse the site.

This plugin adds a proper **wp-admin settings page** on top of the core component — configure stations, behavior and appearance entirely from the browser, no file editing required.

> This plugin is **not published on wordpress.org**. Install it manually from this repository (see below).

### Screenshot

![Bottom Radio Player running on WordPress, with Clip Mode open](https://i.imgur.com/MfCnzbw.png)

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

### Languages

The settings page follows your WordPress admin language automatically — no configuration needed. Currently translated: **English** (default, no file needed — it's the source language), **Português (pt_BR, pt_PT)**, **Español (es_ES)**. Missing your language? Translate `languages/bottom-radioplayer.pot` and open a pull request.

### Relationship to the core project

The player component itself (`js/radioplayer.js`, `css/`, seamless navigation, now-playing metadata, etc.) is developed in **[jailsonsb2/bottom_radioplayer](https://github.com/jailsonsb2/bottom_radioplayer)** — that's also where the plain (non-WordPress) drop-in install, the live demo, and the full feature list live. This repository only adds the WordPress-specific admin/settings layer around it.

### Support and Contributions

- Questions or issues about the WordPress plugin: open an issue in this repository.
- Issues about the player component itself (audio, seamless navigation, metadata, etc.): use the [core repository](https://github.com/jailsonsb2/bottom_radioplayer/issues).
- Contributions are welcome via pull request.
