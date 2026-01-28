<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Post_Types {

    public function __construct() {
        add_action( 'init', array( $this, 'register_alumni' ) );
    }

    public function register_alumni() {
        $labels = array(
            'name' => 'Alumni',
            'singular_name' => 'Alumnus',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Alumnus',
            'edit_item' => 'Edit Alumnus',
            'new_item' => 'New Alumnus',
            'view_item' => 'View Alumnus',
            'search_items' => 'Search Alumni',
            'not_found' => 'No alumni found',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-groups',
            'supports' => array( 'title', 'thumbnail' ),
            'rewrite' => array( 'slug' => 'alumni' ),
        );

        register_post_type( 'ulm_alumni', $args );
    }
}
