<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu, settings registration and tab shell. Each tab is its own
 * form posting to options.php (Settings API), giving us free nonce /
 * capability / redirect handling while still owning the field markup
 * ourselves (needed for the stations repeater).
 */
class BRP_Settings_Page {

    const PAGE_SLUG = 'bottom-radioplayer';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
    }

    public static function add_menu() {
        add_menu_page(
            __( 'Radio Player', 'bottom-radioplayer' ),
            __( 'Radio Player', 'bottom-radioplayer' ),
            'manage_options',
            self::PAGE_SLUG,
            array( __CLASS__, 'render_page' ),
            'dashicons-format-audio'
        );
    }

    public static function register_settings() {
        register_setting( 'brp_general_group', 'brp_general_settings', array(
            'sanitize_callback' => array( 'BRP_Sanitize', 'general' ),
            'default'           => array(
                'seamless'     => true,
                'time_refresh' => 10000,
            ),
        ) );

        register_setting( 'brp_stations_group', 'brp_stations', array(
            'sanitize_callback' => array( 'BRP_Sanitize', 'stations' ),
            'default'           => array(),
        ) );

        register_setting( 'brp_appearance_group', 'brp_appearance_settings', array(
            'sanitize_callback' => array( 'BRP_Sanitize', 'appearance' ),
            'default'           => array( 'custom_css' => '' ),
        ) );
    }

    public static function enqueue_admin_assets( $hook_suffix ) {
        if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'brp-admin',
            BRP_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            BRP_VERSION
        );

        wp_enqueue_script(
            'brp-admin',
            BRP_PLUGIN_URL . 'admin/js/admin.js',
            array(),
            BRP_VERSION,
            true
        );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $tabs = array(
            'general'    => __( 'General', 'bottom-radioplayer' ),
            'stations'   => __( 'Stations', 'bottom-radioplayer' ),
            'appearance' => __( 'Appearance', 'bottom-radioplayer' ),
        );

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector, no state change
        if ( ! isset( $tabs[ $active_tab ] ) ) {
            $active_tab = 'general';
        }

        echo '<div class="wrap brp-settings">';
        echo '<h1>' . esc_html__( 'Bottom Radio Player', 'bottom-radioplayer' ) . '</h1>';

        if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, options.php already verified the nonce for the save
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'bottom-radioplayer' ) . '</p></div>';
        }

        if ( empty( get_option( 'brp_stations', array() ) ) ) {
            $stations_url = add_query_arg( array( 'page' => self::PAGE_SLUG, 'tab' => 'stations' ), admin_url( 'admin.php' ) );
            echo '<div class="notice notice-warning"><p>' . sprintf(
                /* translators: %s: link to the Stations tab */
                esc_html__( 'The player will not appear on your site until you add at least one station with a stream URL, on the %s tab.', 'bottom-radioplayer' ),
                '<a href="' . esc_url( $stations_url ) . '">' . esc_html__( 'Stations', 'bottom-radioplayer' ) . '</a>'
            ) . '</p></div>';
        }

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $label ) {
            $url   = add_query_arg( array( 'page' => self::PAGE_SLUG, 'tab' => $slug ), admin_url( 'admin.php' ) );
            $class = 'nav-tab' . ( $active_tab === $slug ? ' nav-tab-active' : '' );
            echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
        }
        echo '</h2>';

        echo '<form method="post" action="options.php" class="brp-settings-form">';

        switch ( $active_tab ) {
            case 'stations':
                settings_fields( 'brp_stations_group' );
                BRP_Settings_Fields::render_stations();
                break;
            case 'appearance':
                settings_fields( 'brp_appearance_group' );
                BRP_Settings_Fields::render_appearance();
                break;
            default:
                settings_fields( 'brp_general_group' );
                BRP_Settings_Fields::render_general();
                break;
        }

        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
