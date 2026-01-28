<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Timeline {

    public function __construct() {
        add_action( 'init', array( $this, 'register_timeline' ) );
        add_shortcode( 'ulm_timeline', array( $this, 'render_timeline' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function register_timeline() {
        $labels = array(
            'name' => 'Timeline Events',
            'singular_name' => 'Timeline Event',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Timeline Event',
            'edit_item' => 'Edit Timeline Event',
            'new_item' => 'New Timeline Event',
            'view_item' => 'View Timeline Event',
            'search_items' => 'Search Timeline Events',
            'not_found' => 'No events found',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-clock',
            'supports' => array( 'title', 'editor', 'thumbnail' ),
            'rewrite' => array( 'slug' => 'timeline' ),
        );

        register_post_type( 'ulm_timeline', $args );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'ulm-variables',
            ULM_PLUGIN_URL . 'assets/css/ulm-variables.css',
            array(),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-modal',
            ULM_PLUGIN_URL . 'assets/css/ulm-modal.css',
            array( 'ulm-variables' ),
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

    public function render_timeline( $atts ) {
        wp_enqueue_style(
            'ulm-modal',
            ULM_PLUGIN_URL . 'assets/css/ulm-modal.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        wp_enqueue_style(
            'ulm-timeline',
            ULM_PLUGIN_URL . 'assets/css/timeline.css',
            array( 'ulm-variables' ),
            ULM_VERSION
        );

        $events = get_posts( array(
            'post_type' => 'ulm_timeline',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'post_status' => 'publish',
        ) );

        ob_start();
        include ULM_PLUGIN_DIR . 'templates/shortcode-timeline.php';
        return ob_get_clean();
    }
}
