<?php
/**
 * Plugin Name: Bottom Radio Player
 * Plugin URI: https://github.com/jailsonsb2/bottom-radioplayer-wordpress
 * Description: Bottom-bar radio player with seamless navigation (audio never stops while visitors browse the site). Configure stations, behavior and appearance from an admin settings page — no file editing required.
 * Version: 1.0.0
 * Author: Jailson
 * Author URI: https://github.com/jailsonsb2
 * License: GPL-2.0-or-later
 * Text Domain: bottom-radioplayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BRP_VERSION', '1.0.0' );
define( 'BRP_PLUGIN_FILE', __FILE__ );
define( 'BRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once BRP_PLUGIN_DIR . 'includes/class-brp-sanitize.php';
require_once BRP_PLUGIN_DIR . 'includes/class-brp-settings-fields.php';
require_once BRP_PLUGIN_DIR . 'includes/class-brp-settings-page.php';
require_once BRP_PLUGIN_DIR . 'includes/class-brp-frontend.php';

BRP_Settings_Page::init();
BRP_Frontend::init();
