<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Taxonomies {

    public function __construct() {
        add_action( 'init', array( $this, 'register_instrument' ) );
    }

    public function register_instrument() {
        $labels = array(
            'name' => 'Instruments',
            'singular_name' => 'Instrument',
            'search_items' => 'Search Instruments',
            'all_items' => 'All Instruments',
            'edit_item' => 'Edit Instrument',
            'add_new_item' => 'Add New Instrument',
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
        );

        register_taxonomy( 'ulm_instrument', array( 'ulm_alumni' ), $args );
    }
}
