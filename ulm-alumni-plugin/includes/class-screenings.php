<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Community Screenings Post Type and Fields
 */

class ULM_Screenings {
    private const GEOCODE_CACHE_OPTION = 'ulm_screening_geocode_cache';
    private const GEOCODE_LAST_REQUEST_OPTION = 'ulm_nominatim_last_request';

    /**
     * Register the screening post type
     */
    public static function register_post_type() {
        $labels = array(
            'name' => 'Community Screenings',
            'singular_name' => 'Screening',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Screening',
            'edit_item' => 'Edit Screening',
            'new_item' => 'New Screening',
            'view_item' => 'View Screening',
            'search_items' => 'Search Screenings',
            'not_found' => 'No screenings found',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-format-video',
            'supports' => array( 'title', 'thumbnail' ),
            'rewrite' => array( 'slug' => 'screening' ),
        );

        register_post_type( 'ulm_screening', $args );
    }

    public static function maybe_geocode_screening( $post_id, $post, $update ) {
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $location = function_exists( 'ulm_get_field' ) ? ulm_get_field( 'screening_location', $post_id ) : get_post_meta( $post_id, 'screening_location', true );
        $lat = function_exists( 'ulm_get_field' ) ? ulm_get_field( 'screening_lat', $post_id ) : get_post_meta( $post_id, 'screening_lat', true );
        $lng = function_exists( 'ulm_get_field' ) ? ulm_get_field( 'screening_lng', $post_id ) : get_post_meta( $post_id, 'screening_lng', true );

        if ( $lat !== '' && $lng !== '' ) {
            $geocoded_for = get_post_meta( $post_id, '_screening_geocoded_location', true );
            if ( $geocoded_for && $location ) {
                $location_key = strtolower( preg_replace( '/\s+/', ' ', trim( $location ) ) );
                if ( $geocoded_for === $location_key ) {
                    return;
                }
            } else {
                return;
            }
        }

        if ( ! $location ) {
            return;
        }

        $location_key = strtolower( preg_replace( '/\s+/', ' ', trim( $location ) ) );
        $cache = get_option( self::GEOCODE_CACHE_OPTION, array() );
        if ( isset( $cache[ $location_key ]['lat'], $cache[ $location_key ]['lng'] ) ) {
            update_post_meta( $post_id, 'screening_lat', $cache[ $location_key ]['lat'] );
            update_post_meta( $post_id, 'screening_lng', $cache[ $location_key ]['lng'] );
            update_post_meta( $post_id, '_screening_geocoded_location', $location_key );
            return;
        }

        self::respect_rate_limit();
        $result = self::geocode_location( $location );
        if ( $result ) {
            update_post_meta( $post_id, 'screening_lat', $result['lat'] );
            update_post_meta( $post_id, 'screening_lng', $result['lng'] );
            update_post_meta( $post_id, '_screening_geocoded_location', $location_key );

            $cache[ $location_key ] = array(
                'lat' => $result['lat'],
                'lng' => $result['lng'],
                'time' => time(),
            );
            update_option( self::GEOCODE_CACHE_OPTION, $cache, false );
        }
    }

    /**
     * Get screenings for a specific location
     *
     * Used in "Where Are They Now" view to show related screenings
     */
    public static function get_screenings_by_location( $location ) {
        if ( ! $location ) {
            return array();
        }

        return get_posts( array(
            'post_type' => 'ulm_screening',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'screening_location',
                    'value' => $location,
                    'compare' => 'LIKE',
                ),
            ),
        ) );
    }

    /**
     * Get screenings related to an alumnus
     *
     * Used in alumni modal to show related screening events
     */
    public static function get_screenings_for_alumni( $alumni_id ) {
        if ( ! $alumni_id ) {
            return array();
        }

        return get_posts( array(
            'post_type' => 'ulm_screening',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'screening_related_alumni',
                    'value' => '"' . absint( $alumni_id ) . '"',
                    'compare' => 'LIKE',
                ),
            ),
        ) );
    }

    /**
     * Get screenings ordered for timeline display
     */
    public static function get_timeline_screenings() {
        return get_posts( array(
            'post_type' => 'ulm_screening',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'meta_value',
            'meta_key' => 'screening_date',
            'order' => 'ASC',
        ) );
    }

    private static function respect_rate_limit() {
        $last = (int) get_option( self::GEOCODE_LAST_REQUEST_OPTION, 0 );
        $elapsed = time() - $last;
        if ( $elapsed < 1 ) {
            usleep( ( 1 - $elapsed ) * 1000000 );
        }
        update_option( self::GEOCODE_LAST_REQUEST_OPTION, time(), false );
    }

    private static function geocode_location( $location ) {
        $url = add_query_arg(
            array(
                'q' => $location,
                'format' => 'json',
                'limit' => 1,
            ),
            'https://nominatim.openstreetmap.org/search'
        );

        $user_agent_parts = array(
            'ULM Alumni Platform',
            home_url(),
        );
        $admin_email = get_option( 'admin_email' );
        if ( $admin_email ) {
            $user_agent_parts[] = $admin_email;
        }
        $user_agent = implode( ' ', $user_agent_parts );

        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => $user_agent,
                'Referer' => home_url( '/' ),
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        if ( ! $body ) {
            return null;
        }

        $data = json_decode( $body, true );
        if ( empty( $data ) || ! isset( $data[0]['lat'], $data[0]['lon'] ) ) {
            return null;
        }

        return array(
            'lat' => $data[0]['lat'],
            'lng' => $data[0]['lon'],
        );
    }
}
