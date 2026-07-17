<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field/repeater markup for each settings tab. Rendered server-side from
 * the current stored options so a page reload never shows stale/blank
 * data; admin/js/admin.js only adds/removes station cards client-side
 * (the sanitize callback is what actually validates everything on save).
 */
class BRP_Settings_Fields {

    public static function render_general() {
        $general = wp_parse_args( get_option( 'brp_general_settings', array() ), array(
            'seamless'     => true,
            'time_refresh' => 10000,
        ) );
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="brp-seamless"><?php esc_html_e( 'Seamless navigation', 'bottom-radioplayer' ); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="brp-seamless" name="brp_general_settings[seamless]" value="1" <?php checked( ! empty( $general['seamless'] ) ); ?> />
                        <?php esc_html_e( 'Keep the audio playing while visitors navigate between pages (recommended).', 'bottom-radioplayer' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="brp-time-refresh"><?php esc_html_e( 'Now-playing refresh interval (ms)', 'bottom-radioplayer' ); ?></label></th>
                <td>
                    <input type="number" min="3000" step="1000" id="brp-time-refresh" name="brp_general_settings[time_refresh]" value="<?php echo esc_attr( $general['time_refresh'] ); ?>" class="small-text" />
                    <p class="description"><?php esc_html_e( 'How often the player polls for the current track. Minimum 3000ms.', 'bottom-radioplayer' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function render_appearance() {
        $appearance = wp_parse_args( get_option( 'brp_appearance_settings', array() ), array(
            'custom_css' => '',
        ) );
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="brp-custom-css"><?php esc_html_e( 'Custom CSS', 'bottom-radioplayer' ); ?></label></th>
                <td>
                    <textarea id="brp-custom-css" name="brp_appearance_settings[custom_css]" rows="16" class="large-text code" spellcheck="false"><?php echo esc_textarea( $appearance['custom_css'] ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Raw CSS printed on every front-end page, after the player\'s default styles — use it to override colors, spacing, fonts, etc.', 'bottom-radioplayer' ); ?></p>
                    <p class="description"><?php echo wp_kses(
                        sprintf(
                            /* translators: 1: #app-player selector, 2: --accent CSS variable, 3: link to the selector reference */
                            __( 'Scope your rules under %1$s so they don\'t leak into the rest of the site. The dominant color extracted from the cover art is exposed as the %2$s CSS variable. Full selector reference and examples: %3$s.', 'bottom-radioplayer' ),
                            '<code>#app-player</code>',
                            '<code>--accent</code>',
                            '<a href="https://github.com/jailsonsb2/bottom-radioplayer-wordpress#customizing-the-appearance-custom-css" target="_blank" rel="noopener noreferrer">README</a>'
                        ),
                        array(
                            'code' => array(),
                            'a'    => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
                        )
                    ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function render_stations() {
        $stations = get_option( 'brp_stations', array() );
        ?>
        <p class="description"><?php esc_html_e( 'Add one or more stations. Visitors can switch between them from the player\'s station list.', 'bottom-radioplayer' ); ?></p>
        <div id="brp-stations-list">
            <?php
            if ( empty( $stations ) ) {
                self::station_card( 0, array() );
            } else {
                foreach ( array_values( $stations ) as $index => $station ) {
                    self::station_card( $index, $station );
                }
            }
            ?>
        </div>
        <p>
            <button type="button" class="button" id="brp-add-station"><?php esc_html_e( '+ Add station', 'bottom-radioplayer' ); ?></button>
        </p>

        <template id="brp-station-template">
            <?php self::station_card( '__INDEX__', array(), true ); ?>
        </template>
        <?php
    }

    private static function station_card( $index, $station, $is_template = false ) {
        $station = wp_parse_args( $station, array(
            'name'        => '',
            'hash'        => '',
            'description' => '',
            'logo'        => '',
            'album'       => '',
            'cover'       => '',
            'api'         => '',
            'stream_url'  => '',
            'tv_url'      => '',
            'server'      => '',
            'social'      => array(),
        ) );
        $social = wp_parse_args( $station['social'], array(
            'facebook'  => '',
            'twitter'   => '',
            'instagram' => '',
            'youtube'   => '',
            'tiktok'    => '',
            'whatsapp'  => '',
        ) );

        $name = 'brp_stations[' . $index . ']';
        ?>
        <div class="brp-station-card" data-index="<?php echo esc_attr( $index ); ?>">
            <div class="brp-station-card-header">
                <strong class="brp-station-title" data-placeholder="<?php esc_attr_e( 'New station', 'bottom-radioplayer' ); ?>"><?php echo $station['name'] ? esc_html( $station['name'] ) : esc_html__( 'New station', 'bottom-radioplayer' ); ?></strong>
                <button type="button" class="button-link brp-remove-station" aria-label="<?php esc_attr_e( 'Remove station', 'bottom-radioplayer' ); ?>">&times;</button>
            </div>

            <div class="brp-station-grid">
                <p>
                    <label><?php esc_html_e( 'Name', 'bottom-radioplayer' ); ?>
                        <input type="text" class="regular-text brp-field-name" name="<?php echo esc_attr( $name ); ?>[name]" value="<?php echo esc_attr( $station['name'] ); ?>" />
                    </label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Hash (unique ID)', 'bottom-radioplayer' ); ?>
                        <input type="text" class="regular-text brp-field-hash" data-auto="<?php echo $is_template || '' === $station['hash'] ? '1' : '0'; ?>" name="<?php echo esc_attr( $name ); ?>[hash]" value="<?php echo esc_attr( $station['hash'] ); ?>" />
                    </label>
                </p>
                <p class="brp-span-2">
                    <label><?php esc_html_e( 'Description', 'bottom-radioplayer' ); ?>
                        <input type="text" class="large-text" name="<?php echo esc_attr( $name ); ?>[description]" value="<?php echo esc_attr( $station['description'] ); ?>" />
                    </label>
                </p>

                <?php foreach ( array(
                    'logo'  => __( 'Logo image', 'bottom-radioplayer' ),
                    'album' => __( 'Album art', 'bottom-radioplayer' ),
                    'cover' => __( 'Background cover', 'bottom-radioplayer' ),
                ) as $field => $label ) : ?>
                    <p class="brp-image-field">
                        <label><?php echo esc_html( $label ); ?></label>
                        <span class="brp-image-picker">
                            <img class="brp-image-preview" src="<?php echo esc_url( $station[ $field ] ); ?>" style="<?php echo $station[ $field ] ? '' : 'display:none;'; ?>" alt="" />
                            <input type="text" class="regular-text brp-image-url" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $station[ $field ] ); ?>" />
                            <button type="button" class="button brp-choose-image" data-title="<?php esc_attr_e( 'Select an image', 'bottom-radioplayer' ); ?>"><?php esc_html_e( 'Choose image', 'bottom-radioplayer' ); ?></button>
                        </span>
                    </p>
                <?php endforeach; ?>

                <p>
                    <label><?php esc_html_e( 'Stream URL', 'bottom-radioplayer' ); ?>
                        <input type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[stream_url]" value="<?php echo esc_attr( $station['stream_url'] ); ?>" />
                    </label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Now-playing API URL (optional)', 'bottom-radioplayer' ); ?>
                        <input type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[api]" value="<?php echo esc_attr( $station['api'] ); ?>" />
                    </label>
                </p>
                <p class="brp-span-2">
                    <label><?php esc_html_e( 'Live video stream URL (optional)', 'bottom-radioplayer' ); ?>
                        <input type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[tv_url]" value="<?php echo esc_attr( $station['tv_url'] ); ?>" />
                    </label>
                    <span class="description"><?php esc_html_e( 'Embeddable player URL for a 24/7 video simulcast (opens in a "Tv" button). This is unrelated to the automatic "Clipe" button, which appears on its own whenever the now-playing API returns a YouTube ID for the current song.', 'bottom-radioplayer' ); ?></span>
                </p>
                <p>
                    <label><?php esc_html_e( 'Metadata source (optional)', 'bottom-radioplayer' ); ?>
                        <input type="text" class="regular-text" placeholder="itunes" name="<?php echo esc_attr( $name ); ?>[server]" value="<?php echo esc_attr( $station['server'] ); ?>" />
                    </label>
                </p>
            </div>

            <p class="brp-social-label"><?php esc_html_e( 'Social links (optional)', 'bottom-radioplayer' ); ?></p>
            <div class="brp-station-grid brp-social-grid">
                <?php foreach ( $social as $network => $url ) : ?>
                    <p>
                        <label><?php echo esc_html( ucfirst( $network ) ); ?>
                            <input type="url" class="regular-text" name="<?php echo esc_attr( $name ); ?>[social][<?php echo esc_attr( $network ); ?>]" value="<?php echo esc_attr( $url ); ?>" />
                        </label>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
