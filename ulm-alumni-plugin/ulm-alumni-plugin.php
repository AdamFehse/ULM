<?php
/**
 * Plugin Name: ULM Alumni Platform
 * Description: Alumni directory, timeline, and community features for Ugly Little Monkeys
 * Version: 1.0.0
 * Author: Adam Fehse
 * Text Domain: ulm-alumni
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ULM_VERSION', '1.0.0' );
define( 'ULM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ULM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ULM_PLUGIN_DIR . 'includes/class-helpers.php';
require_once ULM_PLUGIN_DIR . 'includes/class-post-types.php';
require_once ULM_PLUGIN_DIR . 'includes/class-taxonomies.php';
require_once ULM_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once ULM_PLUGIN_DIR . 'includes/class-alumni-stats.php';
require_once ULM_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once ULM_PLUGIN_DIR . 'includes/class-members-area.php';
require_once ULM_PLUGIN_DIR . 'includes/class-memory-form.php';
require_once ULM_PLUGIN_DIR . 'includes/class-carbon-fields.php';
require_once ULM_PLUGIN_DIR . 'includes/class-timeline.php';
require_once ULM_PLUGIN_DIR . 'includes/class-screenings.php';

// Load Carbon Fields if available (from Composer vendor or bundled)
if ( file_exists( ULM_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once ULM_PLUGIN_DIR . 'vendor/autoload.php';
    add_action( 'after_setup_theme', 'ulm_load_carbon_fields' );
    function ulm_load_carbon_fields() {
        if ( class_exists( 'Carbon_Fields\Carbon_Fields' ) ) {
            \Carbon_Fields\Carbon_Fields::boot();
        }
    }
}

function ulm_init() {
    new ULM_Post_Types();
    new ULM_Taxonomies();
    new ULM_Shortcodes();
    new ULM_Timeline();
    new ULM_Members_Area();
    new ULM_Memory_Form();
    new ULM_Carbon_Fields();

    add_action( 'init', array( 'ULM_Screenings', 'register_post_type' ) );
    add_action( 'save_post_ulm_screening', array( 'ULM_Screenings', 'maybe_geocode_screening' ), 20, 3 );
}
add_action( 'plugins_loaded', 'ulm_init' );
