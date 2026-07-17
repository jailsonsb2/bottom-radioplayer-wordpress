<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Front-end output: enqueues the player bundled inside this plugin and
 * prints the admin-configured Custom CSS. Never runs on wp-admin screens —
 * there is no reason to load the player component there at all.
 */
class BRP_Frontend {

    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
        add_action( 'wp_footer', array( __CLASS__, 'print_custom_css' ), 100 );
    }

    public static function enqueue() {
        if ( is_admin() ) {
            return;
        }

        $stations = get_option( 'brp_stations', array() );
        if ( empty( $stations ) ) {
            return; // nothing configured yet, don't inject a broken player
        }

        $general = get_option( 'brp_general_settings', array() );

        wp_register_script(
            'brp-radioplayer',
            BRP_PLUGIN_URL . 'js/radioplayer.js',
            array(),
            BRP_VERSION,
            true // in_footer: matches the original end-of-<body> placement
        );

        $streams = array(
            'timeRefresh' => isset( $general['time_refresh'] ) ? (int) $general['time_refresh'] : 10000,
            'seamless'    => ! isset( $general['seamless'] ) || (bool) $general['seamless'],
            'stations'    => $stations,
        );

        $inline = 'window.streams = ' . wp_json_encode(
            $streams,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) . ';';

        wp_add_inline_script( 'brp-radioplayer', $inline, 'before' );
        wp_enqueue_script( 'brp-radioplayer' );

        // Clip mode: auto-detected per song from the now-playing API
        // (youtubeId field) — no per-station configuration needed.
        wp_enqueue_style(
            'brp-clip-mode',
            BRP_PLUGIN_URL . 'css/clip-mode.css',
            array(),
            BRP_VERSION
        );

        wp_enqueue_script(
            'brp-clip-mode',
            BRP_PLUGIN_URL . 'js/clip-mode.js',
            array( 'brp-radioplayer' ),
            BRP_VERSION,
            true
        );
    }

    public static function print_custom_css() {
        if ( is_admin() ) {
            return;
        }

        $appearance = get_option( 'brp_appearance_settings', array() );
        $css        = isset( $appearance['custom_css'] ) ? trim( $appearance['custom_css'] ) : '';

        if ( '' === $css ) {
            return;
        }

        echo '<style id="brp-custom-css">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw CSS by design, sanitized (</style breakout stripped) at save time in BRP_Sanitize::appearance()
    }
}
