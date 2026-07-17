<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sanitize callbacks for the plugin's three stored options. These are the
 * authoritative validation step — the admin JS (repeater, auto-slug) is only
 * a UX convenience, never trusted on its own.
 */
class BRP_Sanitize {

    const SOCIAL_KEYS = array( 'facebook', 'twitter', 'instagram', 'youtube', 'tiktok', 'whatsapp' );

    public static function general( $input ) {
        $input = is_array( $input ) ? $input : array();

        $time_refresh = isset( $input['time_refresh'] ) ? (int) $input['time_refresh'] : 10000;
        if ( $time_refresh < 3000 ) {
            $time_refresh = 3000; // floor: avoid hammering the now-playing API
        }

        return array(
            'seamless'     => ! empty( $input['seamless'] ),
            'time_refresh' => $time_refresh,
        );
    }

    public static function appearance( $input ) {
        $input = is_array( $input ) ? $input : array();
        $css   = isset( $input['custom_css'] ) ? (string) $input['custom_css'] : '';

        // Breakout guard: this is printed verbatim inside a <style> tag.
        $css = preg_replace( '/<\/style/i', '', $css );

        if ( strlen( $css ) > 50000 ) {
            $css = substr( $css, 0, 50000 );
        }

        return array( 'custom_css' => $css );
    }

    public static function stations( $input ) {
        $input = is_array( $input ) ? $input : array();
        $clean = array();
        $used_hashes = array();

        foreach ( $input as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $name        = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
            $stream_url  = isset( $row['stream_url'] ) ? esc_url_raw( $row['stream_url'] ) : '';

            if ( '' === $name && '' === $stream_url ) {
                continue; // blank/removed row
            }

            $hash = isset( $row['hash'] ) ? sanitize_title( $row['hash'] ) : '';
            if ( '' === $hash ) {
                $hash = sanitize_title( $name );
            }
            if ( '' === $hash ) {
                $hash = 'station';
            }
            $hash = self::dedupe_hash( $hash, $used_hashes );
            $used_hashes[] = $hash;

            $social = array();
            if ( isset( $row['social'] ) && is_array( $row['social'] ) ) {
                foreach ( self::SOCIAL_KEYS as $key ) {
                    if ( ! empty( $row['social'][ $key ] ) ) {
                        $social[ $key ] = esc_url_raw( $row['social'][ $key ] );
                    }
                }
            }

            $clean[] = array(
                'name'        => $name,
                'hash'        => $hash,
                'description' => isset( $row['description'] ) ? sanitize_text_field( $row['description'] ) : '',
                'logo'        => isset( $row['logo'] ) ? esc_url_raw( $row['logo'] ) : '',
                'album'       => isset( $row['album'] ) ? esc_url_raw( $row['album'] ) : '',
                'cover'       => isset( $row['cover'] ) ? esc_url_raw( $row['cover'] ) : '',
                'api'         => isset( $row['api'] ) ? esc_url_raw( $row['api'] ) : '',
                'stream_url'  => $stream_url,
                'tv_url'      => isset( $row['tv_url'] ) ? esc_url_raw( $row['tv_url'] ) : '',
                'server'      => isset( $row['server'] ) ? sanitize_text_field( $row['server'] ) : '',
                'social'      => $social,
            );
        }

        return array_values( $clean );
    }

    private static function dedupe_hash( $hash, array $used ) {
        if ( ! in_array( $hash, $used, true ) ) {
            return $hash;
        }
        $i = 2;
        while ( in_array( $hash . '-' . $i, $used, true ) ) {
            $i++;
        }
        return $hash . '-' . $i;
    }
}
