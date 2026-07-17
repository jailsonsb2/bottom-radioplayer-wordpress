<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'brp_general_settings' );
delete_option( 'brp_stations' );
delete_option( 'brp_appearance_settings' );
