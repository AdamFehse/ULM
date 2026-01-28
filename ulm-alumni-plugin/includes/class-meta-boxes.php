<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Meta_Boxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_alumni_meta_box' ) );
        add_action( 'save_post_ulm_alumni', array( $this, 'save_alumni_meta' ) );
    }

    public function add_alumni_meta_box() {
        add_meta_box(
            'ulm_alumni_details',
            'Alumni Information',
            array( $this, 'alumni_meta_box_html' ),
            'ulm_alumni',
            'normal',
            'high'
        );
    }

    public function alumni_meta_box_html( $post ) {
        wp_nonce_field( 'ulm_save_alumni', 'ulm_alumni_nonce' );

        $email = get_post_meta( $post->ID, 'alumni_email', true );
        $years = get_post_meta( $post->ID, 'alumni_years_active', true );
        $grad_year = get_post_meta( $post->ID, 'alumni_grad_year', true );
        $role = get_post_meta( $post->ID, 'alumni_role', true );
        $current_title = get_post_meta( $post->ID, 'alumni_current_title', true );
        $current_org = get_post_meta( $post->ID, 'alumni_current_org', true );
        $location = get_post_meta( $post->ID, 'alumni_location', true );
        $progression = get_post_meta( $post->ID, 'alumni_progression', true );
        $website = get_post_meta( $post->ID, 'alumni_website', true );
        ?>

        <p>
            <label for="alumni_grad_year"><strong>Graduation Year</strong></label><br>
            <input type="text"
                   id="alumni_grad_year"
                   name="alumni_grad_year"
                   value="<?php echo esc_attr( $grad_year ); ?>"
                   placeholder="e.g., 1972"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_email"><strong>Email Address</strong></label><br>
            <input type="email" 
                   id="alumni_email" 
                   name="alumni_email"
                   value="<?php echo esc_attr( $email ); ?>"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_years_active"><strong>Years Active</strong></label><br>
            <input type="text"
                   id="alumni_years_active"
                   name="alumni_years_active"
                   value="<?php echo esc_attr( $years ); ?>"
                   placeholder="e.g., 1964-1972"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_role"><strong>Role / Instrument</strong></label><br>
            <input type="text"
                   id="alumni_role"
                   name="alumni_role"
                   value="<?php echo esc_attr( $role ); ?>"
                   placeholder="e.g., Saxophone, Director"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_current_title"><strong>Current Title</strong></label><br>
            <input type="text"
                   id="alumni_current_title"
                   name="alumni_current_title"
                   value="<?php echo esc_attr( $current_title ); ?>"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_current_org"><strong>Current Organization</strong></label><br>
            <input type="text"
                   id="alumni_current_org"
                   name="alumni_current_org"
                   value="<?php echo esc_attr( $current_org ); ?>"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_location"><strong>Location</strong></label><br>
            <input type="text"
                   id="alumni_location"
                   name="alumni_location"
                   value="<?php echo esc_attr( $location ); ?>"
                   placeholder="City, State"
                   style="width: 100%;">
        </p>

        <p>
            <label for="alumni_progression"><strong>Progression</strong></label><br>
            <textarea id="alumni_progression"
                      name="alumni_progression"
                      rows="3"
                      style="width: 100%;"><?php echo esc_textarea( $progression ); ?></textarea>
        </p>

        <p>
            <label for="alumni_website"><strong>Website</strong></label><br>
            <input type="url"
                   id="alumni_website"
                   name="alumni_website"
                   value="<?php echo esc_attr( $website ); ?>"
                   placeholder="https://example.com"
                   style="width: 100%;">
        </p>

        <?php
    }

    public function save_alumni_meta( $post_id ) {
        if ( ! isset( $_POST['ulm_alumni_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['ulm_alumni_nonce'], 'ulm_save_alumni' ) ) return;
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['alumni_grad_year'] ) ) {
            $grad_year = sanitize_text_field( $_POST['alumni_grad_year'] );
            update_post_meta( $post_id, 'alumni_grad_year', $grad_year );
        }

        if ( isset( $_POST['alumni_email'] ) ) {
            $email = sanitize_email( $_POST['alumni_email'] );
            update_post_meta( $post_id, 'alumni_email', $email );
        }

        if ( isset( $_POST['alumni_years_active'] ) ) {
            $years = sanitize_text_field( $_POST['alumni_years_active'] );
            update_post_meta( $post_id, 'alumni_years_active', $years );
        }

        if ( isset( $_POST['alumni_role'] ) ) {
            $role = sanitize_text_field( $_POST['alumni_role'] );
            update_post_meta( $post_id, 'alumni_role', $role );
        }

        if ( isset( $_POST['alumni_current_title'] ) ) {
            $current_title = sanitize_text_field( $_POST['alumni_current_title'] );
            update_post_meta( $post_id, 'alumni_current_title', $current_title );
        }

        if ( isset( $_POST['alumni_current_org'] ) ) {
            $current_org = sanitize_text_field( $_POST['alumni_current_org'] );
            update_post_meta( $post_id, 'alumni_current_org', $current_org );
        }

        if ( isset( $_POST['alumni_location'] ) ) {
            $location = sanitize_text_field( $_POST['alumni_location'] );
            update_post_meta( $post_id, 'alumni_location', $location );
        }

        if ( isset( $_POST['alumni_progression'] ) ) {
            $progression = sanitize_textarea_field( $_POST['alumni_progression'] );
            update_post_meta( $post_id, 'alumni_progression', $progression );
        }

        if ( isset( $_POST['alumni_website'] ) ) {
            $website = esc_url_raw( $_POST['alumni_website'] );
            update_post_meta( $post_id, 'alumni_website', $website );
        }
    }
}
