<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_ACF_Timeline {

    public function __construct() {
        add_action( 'init', array( $this, 'adjust_post_type_supports' ), 100 );
        add_action( 'admin_head', array( $this, 'hide_title_ui' ) );
        add_action( 'acf/init', array( $this, 'register_field_groups' ) );
        add_action( 'acf/save_post', array( $this, 'sync_event_title' ), 20 );
    }

    public function adjust_post_type_supports() {
        if ( post_type_exists( 'ulm_timeline' ) ) {
            remove_post_type_support( 'ulm_timeline', 'editor' );
            remove_post_type_support( 'ulm_timeline', 'title' );
        }
    }

    public function hide_title_ui() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'ulm_timeline' ) {
            return;
        }
        echo '<style>#titlediv{display:none !important;}</style>';
    }

    public function sync_event_title( $post_id ) {
        if ( get_post_type( $post_id ) !== 'ulm_timeline' ) {
            return;
        }

        $title = get_field( 'timeline_event_title', $post_id );
        if ( ! $title ) {
            return;
        }

        remove_action( 'acf/save_post', array( $this, 'sync_event_title' ), 20 );

        $post_data = array(
            'ID' => $post_id,
            'post_title' => $title,
        );

        $post = get_post( $post_id );
        if ( $post && $post->post_name === '' ) {
            $post_data['post_name'] = sanitize_title( $title );
        }

        wp_update_post( $post_data );

        add_action( 'acf/save_post', array( $this, 'sync_event_title' ), 20 );
    }

    public function register_field_groups() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_ulm_timeline',
            'title' => 'ULM Timeline Event',
            'fields' => array(
                array(
                    'key' => 'field_ulm_timeline_tab_basics',
                    'label' => 'Basics',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_ulm_timeline_event_title',
                    'label' => 'Event Title',
                    'name' => 'timeline_event_title',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'e.g., 1972 National Tour',
                ),
                array(
                    'key' => 'field_ulm_timeline_event_date',
                    'label' => 'Event Date',
                    'name' => 'ulm_event_date',
                    'type' => 'date_picker',
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_ulm_timeline_description',
                    'label' => 'Event Description',
                    'name' => 'timeline_event_description',
                    'type' => 'textarea',
                    'rows' => 5,
                ),
                array(
                    'key' => 'field_ulm_timeline_tab_related',
                    'label' => 'Related Alumni',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_ulm_timeline_related_alumni',
                    'label' => 'Related Alumni',
                    'name' => 'ulm_related_alumni',
                    'type' => 'relationship',
                    'post_type' => array( 'ulm_alumni' ),
                    'filters' => array( 'search' ),
                    'return_format' => 'id',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'ulm_timeline',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ) );
    }
}
