<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Community Screenings Post Type and Fields
 *
 * STUB: Placeholder for future feature implementation
 *
 * Planned Features:
 * - Register 'ulm_screening' custom post type
 * - Store screening details (title, date, location, venue, description)
 * - Link screenings to related alumni
 * - Display screenings alongside timeline events
 * - Show screenings by location in "Where Are They Now"
 *
 * Field structure (when implemented):
 * - screening_title (text, synced to post title)
 * - screening_date (date picker)
 * - screening_location (text)
 * - screening_venue (text)
 * - screening_description (textarea)
 * - screening_media (files: posters, programs, photos)
 * - related_alumni (relationship to ulm_alumni)
 */

class ULM_Screenings {

    /**
     * FUTURE: Register the screening post type
     *
     * Will be called from main plugin initialization
     * Currently disabled to avoid admin clutter
     */
    public static function register_post_type() {
        // TODO: Uncomment when ready to implement
        /*
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
        */
    }

    /**
     * FUTURE: Register screening fields with Carbon Fields
     *
     * Planned fields:
     * - screening_title (synced to post title)
     * - screening_date
     * - screening_location
     * - screening_venue
     * - screening_description
     * - screening_media (files)
     * - related_alumni (associations)
     */
    public static function register_fields() {
        // TODO: Implement with Carbon Fields when ready
        // See Carbon Fields documentation: https://carbonfields.net/docs/containers/post-meta/
    }

    /**
     * FUTURE: Get screenings for a specific location
     *
     * Used in "Where Are They Now" view to show related screenings
     */
    public static function get_screenings_by_location( $location ) {
        // TODO: Query screenings with location matching
        return array();
    }

    /**
     * FUTURE: Get screenings related to an alumnus
     *
     * Used in alumni modal to show related screening events
     */
    public static function get_screenings_for_alumni( $alumni_id ) {
        // TODO: Query screenings where related_alumni includes this alumnus
        return array();
    }

    /**
     * FUTURE: Display screenings in a timeline view
     *
     * Integrate screenings alongside timeline events
     */
    public static function get_timeline_screenings() {
        // TODO: Return screenings formatted for timeline display
        return array();
    }
}
