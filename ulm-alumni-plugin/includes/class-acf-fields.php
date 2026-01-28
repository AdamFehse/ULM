<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_ACF_Fields {

    public function __construct() {
        add_action( 'init', array( $this, 'adjust_post_type_supports' ), 100 );
        add_action( 'admin_head', array( $this, 'hide_title_ui' ) );
        add_action( 'admin_menu', array( $this, 'remove_taxonomy_metaboxes' ) );
        add_action( 'acf/init', array( $this, 'register_field_groups' ) );
        add_action( 'acf/save_post', array( $this, 'sync_alumni_title' ), 20 );
        add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
    }

    public function title_placeholder( $title, $post ) {
        if ( $post->post_type === 'ulm_alumni' ) {
            return 'Alumni name';
        }

        return $title;
    }

    public function adjust_post_type_supports() {
        if ( post_type_exists( 'ulm_alumni' ) ) {
            remove_post_type_support( 'ulm_alumni', 'editor' );
            remove_post_type_support( 'ulm_alumni', 'title' );
        }
    }

    public function hide_title_ui() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'ulm_alumni' ) {
            return;
        }
        echo '<style>#titlediv{display:none !important;}</style>';
    }

    public function remove_taxonomy_metaboxes() {
        remove_meta_box( 'tagsdiv-ulm_instrument', 'ulm_alumni', 'side' );
    }

    public function sync_alumni_title( $post_id ) {
        if ( get_post_type( $post_id ) !== 'ulm_alumni' ) {
            return;
        }

        $full_name = get_field( 'alumni_full_name', $post_id );
        if ( ! $full_name ) {
            return;
        }

        remove_action( 'acf/save_post', array( $this, 'sync_alumni_title' ), 20 );

        $post_data = array(
            'ID' => $post_id,
            'post_title' => $full_name,
        );

        $post = get_post( $post_id );
        if ( $post && $post->post_name === '' ) {
            $post_data['post_name'] = sanitize_title( $full_name );
        }

        wp_update_post( $post_data );

        add_action( 'acf/save_post', array( $this, 'sync_alumni_title' ), 20 );
    }

    public function register_field_groups() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_ulm_alumni_profile',
            'title' => 'ULM Alumni Profile',
            'fields' => array(
                array(
                    'key' => 'field_ulm_tab_basics',
                    'label' => 'Basics',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_ulm_alumni_full_name',
                    'label' => 'Full Name',
                    'name' => 'alumni_full_name',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'e.g., Alex Johnson',
                ),
                array(
                    'key' => 'field_ulm_alumni_years_active',
                    'label' => 'Years Active',
                    'name' => 'alumni_years_active',
                    'type' => 'text',
                    'placeholder' => 'e.g., 1964-1972',
                ),
                array(
                    'key' => 'field_ulm_alumni_grad_year',
                    'label' => 'Graduation Year',
                    'name' => 'alumni_grad_year',
                    'type' => 'text',
                    'placeholder' => 'e.g., 1972',
                ),
                array(
                    'key' => 'field_ulm_alumni_role',
                    'label' => 'Role / Instrument',
                    'name' => 'alumni_role',
                    'type' => 'text',
                    'placeholder' => 'e.g., Saxophone, Director',
                ),
                array(
                    'key' => 'field_ulm_alumni_instruments',
                    'label' => 'Instruments',
                    'name' => 'alumni_instruments',
                    'type' => 'taxonomy',
                    'taxonomy' => 'ulm_instrument',
                    'field_type' => 'checkbox',
                    'add_term' => 1,
                    'save_terms' => 1,
                    'load_terms' => 1,
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_ulm_alumni_bio',
                    'label' => 'Short Bio',
                    'name' => 'alumni_bio',
                    'type' => 'textarea',
                    'rows' => 4,
                ),
                array(
                    'key' => 'field_ulm_tab_current',
                    'label' => 'Current',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_ulm_alumni_current_title',
                    'label' => 'Current Title',
                    'name' => 'alumni_current_title',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_ulm_alumni_current_org',
                    'label' => 'Current Organization',
                    'name' => 'alumni_current_org',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_ulm_alumni_location',
                    'label' => 'Location',
                    'name' => 'alumni_location',
                    'type' => 'text',
                    'placeholder' => 'City, State',
                ),
                array(
                    'key' => 'field_ulm_alumni_progression',
                    'label' => 'Progression / Career Notes',
                    'name' => 'alumni_progression',
                    'type' => 'textarea',
                    'rows' => 3,
                ),
                array(
                    'key' => 'field_ulm_tab_contact',
                    'label' => 'Contact',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_ulm_alumni_email',
                    'label' => 'Email Address',
                    'name' => 'alumni_email',
                    'type' => 'email',
                ),
                array(
                    'key' => 'field_ulm_alumni_website',
                    'label' => 'Website',
                    'name' => 'alumni_website',
                    'type' => 'url',
                    'placeholder' => 'https://example.com',
                ),
                array(
                    'key' => 'field_ulm_tab_media',
                    'label' => 'Media',
                    'type' => 'tab',
                    'placement' => 'top',
                    'instructions' => 'Use the Featured Image box to set the profile photo.',
                ),
                array(
                    'key' => 'field_ulm_alumni_media_file',
                    'label' => 'Profile Media File',
                    'name' => 'alumni_media_file',
                    'type' => 'file',
                    'return_format' => 'array',
                    'library' => 'all',
                    'mime_types' => 'mp3,wav,m4a,mp4,mov,avi,pdf',
                    'instructions' => 'Upload an audio/video file or a PDF related to this alumnus.',
                ),
                array(
                    'key' => 'field_ulm_alumni_media_caption',
                    'label' => 'Media Caption',
                    'name' => 'alumni_media_caption',
                    'type' => 'text',
                    'placeholder' => 'e.g., 1972 performance recording',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'ulm_alumni',
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
