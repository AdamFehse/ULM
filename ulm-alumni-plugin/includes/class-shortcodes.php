<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Shortcodes {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_variables' ) );
        add_shortcode( 'ulm_alumni_directory', array( $this, 'alumni_directory' ) );
        add_shortcode( 'ulm_where_are_they_now', array( $this, 'where_are_they_now' ) );
        add_shortcode( 'ulm_community_screenings', array( $this, 'community_screenings' ) );
    }

    public function enqueue_variables() {
        wp_enqueue_style(
            'ulm-variables',
            ULM_PLUGIN_URL . 'assets/css/ulm-variables.css',
            array(),
            ULM_VERSION
        );

        wp_enqueue_script(
            'ulm-scripts',
            ULM_PLUGIN_URL . 'assets/js/ulm-scripts.js',
            array(),
            ULM_VERSION,
            true
        );
    }

    public function alumni_directory( $atts ) {
        wp_enqueue_style(
            'ulm-alumni-stats',
            ULM_PLUGIN_URL . 'assets/css/alumni-stats.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-alumni-filters',
            ULM_PLUGIN_URL . 'assets/css/alumni-filters.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-alumni-grid',
            ULM_PLUGIN_URL . 'assets/css/alumni-grid.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-modal',
            ULM_PLUGIN_URL . 'assets/css/ulm-modal.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_script(
            'ulm-alumni-filters',
            ULM_PLUGIN_URL . 'assets/js/alumni-filters.js',
            array(),
            ULM_VERSION,
            true
        );

        $alumni = get_posts( array(
            'post_type' => 'ulm_alumni',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ) );

        ob_start();
        include ULM_PLUGIN_DIR . 'templates/shortcode-alumni-directory.php';
        return ob_get_clean();
    }

    public function where_are_they_now( $atts ) {
        wp_enqueue_style(
            'ulm-where-are-they-now',
            ULM_PLUGIN_URL . 'assets/css/where-are-they-now.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-modal',
            ULM_PLUGIN_URL . 'assets/css/ulm-modal.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        ob_start();
        include ULM_PLUGIN_DIR . 'templates/shortcode-where-are-they-now.php';
        return ob_get_clean();
    }

    public function community_screenings( $atts ) {
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        wp_style_add_data( 'leaflet', 'integrity', 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=' );
        wp_style_add_data( 'leaflet', 'crossorigin', '' );

        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );
        wp_script_add_data( 'leaflet', 'integrity', 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=' );
        wp_script_add_data( 'leaflet', 'crossorigin', '' );

        wp_enqueue_style(
            'ulm-community-screenings',
            ULM_PLUGIN_URL . 'assets/css/community-screenings.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-alumni-map',
            ULM_PLUGIN_URL . 'assets/css/alumni-map.css',
            array( 'ulm-variables', 'leaflet' ),
            ULM_VERSION
        );

        wp_enqueue_script(
            'ulm-alumni-map',
            ULM_PLUGIN_URL . 'assets/js/alumni-map-stub.js',
            array( 'leaflet' ),
            ULM_VERSION,
            true
        );

        ob_start();
        include ULM_PLUGIN_DIR . 'templates/shortcode-community-screenings.php';
        return ob_get_clean();
    }

}
