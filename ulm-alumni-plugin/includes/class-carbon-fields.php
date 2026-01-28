<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class ULM_Carbon_Fields {

	public function __construct() {
		add_action( 'init', array( $this, 'adjust_post_type_supports' ), 100 );
		add_action( 'admin_head', array( $this, 'hide_title_ui' ) );
		add_action( 'admin_menu', array( $this, 'remove_taxonomy_metaboxes' ) );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );
		add_action( 'save_post_ulm_alumni', array( $this, 'sync_alumni_title' ), 30 );
		add_action( 'save_post_ulm_timeline', array( $this, 'sync_event_title' ), 30 );
		add_action( 'save_post_ulm_screening', array( $this, 'sync_screening_title' ), 30 );
		add_action( 'carbon_fields_post_meta_container_saved', array( $this, 'sync_alumni_title_after_fields' ), 20, 2 );
		add_action( 'carbon_fields_post_meta_container_saved', array( $this, 'sync_event_title_after_fields' ), 20, 2 );
		add_action( 'carbon_fields_post_meta_container_saved', array( $this, 'sync_screening_title_after_fields' ), 20, 2 );
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
	}

	public function title_placeholder( $title, $post ) {
		if ( $post->post_type === 'ulm_alumni' ) {
			return 'Alumni name';
		}
		if ( $post->post_type === 'ulm_timeline' ) {
			return 'Event title';
		}
		if ( $post->post_type === 'ulm_screening' ) {
			return 'Screening title';
		}
		return $title;
	}

	public function adjust_post_type_supports() {
		if ( post_type_exists( 'ulm_alumni' ) ) {
			remove_post_type_support( 'ulm_alumni', 'editor' );
			remove_post_type_support( 'ulm_alumni', 'title' );
		}
		if ( post_type_exists( 'ulm_timeline' ) ) {
			remove_post_type_support( 'ulm_timeline', 'editor' );
			remove_post_type_support( 'ulm_timeline', 'title' );
		}
		if ( post_type_exists( 'ulm_screening' ) ) {
			remove_post_type_support( 'ulm_screening', 'editor' );
			remove_post_type_support( 'ulm_screening', 'title' );
		}
	}

	public function hide_title_ui() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( $screen->post_type === 'ulm_alumni' || $screen->post_type === 'ulm_timeline' || $screen->post_type === 'ulm_screening' ) {
			echo '<style>#titlediv{display:none !important;}</style>';
		}
	}

	public function remove_taxonomy_metaboxes() {
		// Removed to allow default instrument taxonomy UI
	}

	public function sync_alumni_title( $post_id ) {
		if ( get_post_type( $post_id ) !== 'ulm_alumni' ) {
			return;
		}

		// Prevent infinite loop
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if already synced
		if ( get_post_meta( $post_id, '_ulm_alumni_title_synced', true ) ) {
			return;
		}

		$full_name = carbon_get_post_meta( $post_id, 'alumni_full_name' );
		if ( ! $full_name ) {
			return;
		}

		// Temporarily mark as synced to prevent recursion
		update_post_meta( $post_id, '_ulm_alumni_title_synced', true );

		$post_data = array(
			'ID' => $post_id,
			'post_title' => $full_name,
		);

		$post = get_post( $post_id );
		if ( $post && $post->post_name === '' ) {
			$post_data['post_name'] = sanitize_title( $full_name );
		}

		wp_update_post( $post_data );

		// Clean up the flag
		delete_post_meta( $post_id, '_ulm_alumni_title_synced' );
	}

	public function sync_event_title( $post_id ) {
		if ( get_post_type( $post_id ) !== 'ulm_timeline' ) {
			return;
		}

		// Prevent infinite loop
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if already synced
		if ( get_post_meta( $post_id, '_ulm_event_title_synced', true ) ) {
			return;
		}

		$title = carbon_get_post_meta( $post_id, 'timeline_event_title' );
		if ( ! $title ) {
			return;
		}

		// Temporarily mark as synced to prevent recursion
		update_post_meta( $post_id, '_ulm_event_title_synced', true );

		$post_data = array(
			'ID' => $post_id,
			'post_title' => $title,
		);

		$post = get_post( $post_id );
		if ( $post && $post->post_name === '' ) {
			$post_data['post_name'] = sanitize_title( $title );
		}

		wp_update_post( $post_data );

		// Clean up the flag
		delete_post_meta( $post_id, '_ulm_event_title_synced' );
	}

	public function sync_alumni_title_after_fields( $post_id, $container ) {
		if ( get_post_type( $post_id ) !== 'ulm_alumni' ) {
			return;
		}

		if ( ! is_object( $container ) || ! method_exists( $container, 'get_title' ) ) {
			return;
		}

		if ( $container->get_title() !== 'Alumni Details' ) {
			return;
		}

		$this->sync_alumni_title( $post_id );
	}

	public function sync_event_title_after_fields( $post_id, $container ) {
		if ( get_post_type( $post_id ) !== 'ulm_timeline' ) {
			return;
		}

		if ( ! is_object( $container ) || ! method_exists( $container, 'get_title' ) ) {
			return;
		}

		if ( $container->get_title() !== 'Timeline Event Details' ) {
			return;
		}

		$this->sync_event_title( $post_id );
	}

	public function sync_screening_title( $post_id ) {
		if ( get_post_type( $post_id ) !== 'ulm_screening' ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( get_post_meta( $post_id, '_ulm_screening_title_synced', true ) ) {
			return;
		}

		$title = carbon_get_post_meta( $post_id, 'screening_title' );
		if ( ! $title ) {
			return;
		}

		update_post_meta( $post_id, '_ulm_screening_title_synced', true );

		$post_data = array(
			'ID' => $post_id,
			'post_title' => $title,
		);

		$post = get_post( $post_id );
		if ( $post && $post->post_name === '' ) {
			$post_data['post_name'] = sanitize_title( $title );
		}

		wp_update_post( $post_data );

		delete_post_meta( $post_id, '_ulm_screening_title_synced' );
	}

	public function sync_screening_title_after_fields( $post_id, $container ) {
		if ( get_post_type( $post_id ) !== 'ulm_screening' ) {
			return;
		}

		if ( ! is_object( $container ) || ! method_exists( $container, 'get_title' ) ) {
			return;
		}

		if ( $container->get_title() !== 'Screening Details' ) {
			return;
		}

		$this->sync_screening_title( $post_id );
	}

	public function register_fields() {
		$this->register_alumni_fields();
		$this->register_timeline_fields();
		$this->register_screening_fields();
	}

	private function register_alumni_fields() {
		Container::make( 'post_meta', 'Alumni Details' )
			->where( 'post_type', '=', 'ulm_alumni' )
			->add_tab( __( 'Basics', 'ulm-alumni' ), array(
				Field::make( 'text', 'alumni_full_name', __( 'Full Name', 'ulm-alumni' ) )
					->set_required( true ),
				Field::make( 'text', 'alumni_years_active', __( 'Years Active', 'ulm-alumni' ) )
					->set_help_text( __( 'Format: 1964-1972', 'ulm-alumni' ) ),
				Field::make( 'text', 'alumni_grad_year', __( 'Graduation Year', 'ulm-alumni' ) )
					->set_help_text( __( 'Format: 1972', 'ulm-alumni' ) ),
				Field::make( 'text', 'alumni_role', __( 'Role / Instrument', 'ulm-alumni' ) )
					->set_help_text( __( 'E.g., Saxophone, Director', 'ulm-alumni' ) ),
				Field::make( 'textarea', 'alumni_bio', __( 'Short Bio', 'ulm-alumni' ) )
					->set_rows( 4 ),
			) )
			->add_tab( __( 'Current', 'ulm-alumni' ), array(
				Field::make( 'text', 'alumni_current_title', __( 'Current Title', 'ulm-alumni' ) ),
				Field::make( 'text', 'alumni_current_org', __( 'Current Organization', 'ulm-alumni' ) ),
				Field::make( 'text', 'alumni_location', __( 'Location', 'ulm-alumni' ) )
					->set_help_text( __( 'Format: City, State', 'ulm-alumni' ) ),
				Field::make( 'textarea', 'alumni_progression', __( 'Progression / Career Notes', 'ulm-alumni' ) )
					->set_rows( 3 ),
			) )
			->add_tab( __( 'Contact', 'ulm-alumni' ), array(
				Field::make( 'text', 'alumni_email', __( 'Email Address', 'ulm-alumni' ) )
					->set_attribute( 'type', 'email' ),
				Field::make( 'text', 'alumni_website', __( 'Website', 'ulm-alumni' ) )
					->set_attribute( 'type', 'url' )
					->set_help_text( __( 'https://example.com', 'ulm-alumni' ) ),
			) )
			->add_tab( __( 'Media', 'ulm-alumni' ), array(
				Field::make( 'complex', 'alumni_media_gallery', __( 'Media Gallery', 'ulm-alumni' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_help_text( __( 'Add multiple audio/video files or PDFs with captions.', 'ulm-alumni' ) )
					->add_fields( array(
						Field::make( 'file', 'file', __( 'Media File', 'ulm-alumni' ) )
							->set_type( array( 'audio', 'video', 'application' ) )
							->set_value_type( 'url' ),
						Field::make( 'text', 'caption', __( 'Caption', 'ulm-alumni' ) )
							->set_help_text( __( 'E.g., 1972 performance recording', 'ulm-alumni' ) ),
					) ),
			) );
	}

	private function register_timeline_fields() {
		Container::make( 'post_meta', 'Timeline Event Details' )
			->where( 'post_type', '=', 'ulm_timeline' )
			->add_tab( __( 'Basics', 'ulm-alumni' ), array(
				Field::make( 'text', 'timeline_event_title', __( 'Event Title', 'ulm-alumni' ) )
					->set_required( true )
					->set_help_text( __( 'E.g., 1972 National Tour', 'ulm-alumni' ) ),
				Field::make( 'date', 'ulm_event_date', __( 'Event Date', 'ulm-alumni' ) )
					->set_storage_format( 'Y-m-d' ),
				Field::make( 'textarea', 'timeline_event_description', __( 'Event Description', 'ulm-alumni' ) )
					->set_rows( 5 ),
			) )
			->add_tab( __( 'Related Alumni', 'ulm-alumni' ), array(
				Field::make( 'association', 'ulm_related_alumni', __( 'Related Alumni', 'ulm-alumni' ) )
					->set_types( array(
						array(
							'type' => 'post',
							'post_type' => 'ulm_alumni',
						),
					) ),
			) );
	}

	private function register_screening_fields() {
		Container::make( 'post_meta', 'Screening Details' )
			->where( 'post_type', '=', 'ulm_screening' )
			->add_tab( __( 'Basics', 'ulm-alumni' ), array(
				Field::make( 'text', 'screening_title', __( 'Screening Title', 'ulm-alumni' ) )
					->set_required( true ),
				Field::make( 'date', 'screening_date', __( 'Screening Date', 'ulm-alumni' ) )
					->set_storage_format( 'Y-m-d' ),
				Field::make( 'text', 'screening_location', __( 'Location', 'ulm-alumni' ) )
					->set_help_text( __( 'Format: City, State', 'ulm-alumni' ) ),
				Field::make( 'text', 'screening_venue', __( 'Venue', 'ulm-alumni' ) ),
				Field::make( 'textarea', 'screening_description', __( 'Description', 'ulm-alumni' ) )
					->set_rows( 4 ),
			) )
			->add_tab( __( 'Related Alumni', 'ulm-alumni' ), array(
				Field::make( 'association', 'screening_related_alumni', __( 'Related Alumni', 'ulm-alumni' ) )
					->set_types( array(
						array(
							'type' => 'post',
							'post_type' => 'ulm_alumni',
						),
					) ),
			) )
			->add_tab( __( 'Media', 'ulm-alumni' ), array(
				Field::make( 'complex', 'screening_media', __( 'Screening Media', 'ulm-alumni' ) )
					->set_layout( 'tabbed-horizontal' )
					->set_help_text( __( 'Add posters, programs, or photos.', 'ulm-alumni' ) )
					->add_fields( array(
						Field::make( 'file', 'file', __( 'Media File', 'ulm-alumni' ) )
							->set_type( array( 'image', 'application' ) )
							->set_value_type( 'url' ),
						Field::make( 'text', 'caption', __( 'Caption', 'ulm-alumni' ) ),
					) ),
			) );
	}
}
