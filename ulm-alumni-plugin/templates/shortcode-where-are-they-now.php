<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php if ( empty( $GLOBALS['ulm_modal_rendered'] ) ) : ?>
    <?php $GLOBALS['ulm_modal_rendered'] = true; ?>
    <div class="ulm-modal" id="ulm-alumni-modal" aria-hidden="true">
        <div class="ulm-modal__backdrop" data-ulm-modal-close></div>
        <div class="ulm-modal__content" role="dialog" aria-modal="true" aria-labelledby="ulm-modal-title">
            <button class="ulm-modal__close" type="button" aria-label="Close" data-ulm-modal-close>&times;</button>
            <div class="ulm-modal__header">
                <img class="ulm-modal__photo" src="" alt="" />
                <div>
                    <h3 class="ulm-modal__name" id="ulm-modal-title"></h3>
                    <div class="ulm-modal__meta"></div>
                </div>
            </div>
            <div class="ulm-modal__body"></div>
            <div class="ulm-modal__links"></div>
        </div>
    </div>
<?php endif; ?>

<?php if ( empty( $GLOBALS['ulm_lightbox_rendered'] ) ) : ?>
    <?php $GLOBALS['ulm_lightbox_rendered'] = true; ?>
    <!-- Lightbox for enlarged images -->
    <div class="ulm-lightbox" id="ulm-lightbox" aria-hidden="true">
        <button class="ulm-lightbox__close" type="button" aria-label="Close lightbox" data-ulm-lightbox-close>&times;</button>
        <button class="ulm-lightbox__prev" type="button" aria-label="Previous image" data-ulm-lightbox-prev>‹</button>
        <img class="ulm-lightbox__image" src="" alt="" />
        <button class="ulm-lightbox__next" type="button" aria-label="Next image" data-ulm-lightbox-next">›</button>
    </div>
<?php endif; ?>

<div class="ulm-container">
    <div class="ulm-section-header">
        <h2><?php esc_html_e( 'Where Are They Now', 'ulm-alumni' ); ?></h2>
        <p><?php esc_html_e( 'Alumni grouped by location.', 'ulm-alumni' ); ?></p>
    </div>
    <div class="ulm-where-are-they-now">
        <?php
        // Get all alumni with locations
        $alumni = get_posts( array(
            'post_type' => 'ulm_alumni',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ) );

        // Group by location
        $by_location = array();
        foreach ( $alumni as $person ) {
            $location = ulm_get_field( 'alumni_location', $person->ID );
            if ( empty( $location ) ) {
                continue;
            }
            if ( ! isset( $by_location[ $location ] ) ) {
                $by_location[ $location ] = array();
            }
            $by_location[ $location ][] = $person;
        }

        if ( empty( $by_location ) ) :
        ?>
            <div class="ulm-where-no-alumni">
                <p><?php esc_html_e( 'No alumni location data available.', 'ulm-alumni' ); ?></p>
            </div>
        <?php else :
            ksort( $by_location );
            foreach ( $by_location as $location => $people ) :
        ?>
            <div class="ulm-location-section">
                <h3 class="ulm-location-header" data-toggle-location>
                    <span><?php echo esc_html( $location ); ?></span>
                    <span class="ulm-alumni-count"><?php echo esc_html( count( $people ) ); ?></span>
                </h3>
                <div class="ulm-location-content" data-collapsible-content>
                    <div class="ulm-location-grid">
                        <?php foreach ( $people as $person ) : setup_postdata( $person ); ?>
                            <?php
                            $photo = get_the_post_thumbnail_url( $person->ID, 'medium' );
                            $full_name = ulm_get_field( 'alumni_full_name', $person->ID );
                            $display_name = $full_name ? $full_name : get_the_title( $person );
                            $current_title = ulm_get_field( 'alumni_current_title', $person->ID );
                            $current_org = ulm_get_field( 'alumni_current_org', $person->ID );
                            $years = ulm_get_field( 'alumni_years_active', $person->ID );
                            $grad_year = ulm_get_field( 'alumni_grad_year', $person->ID );
                            $role = ulm_get_field( 'alumni_role', $person->ID );
                            $bio = ulm_get_field( 'alumni_bio', $person->ID );
                            $progression = ulm_get_field( 'alumni_progression', $person->ID );
                            $website = ulm_get_field( 'alumni_website', $person->ID );
                            $media_gallery = ulm_get_field_array( 'alumni_media_gallery', $person->ID );
                            $location = ulm_get_field( 'alumni_location', $person->ID );
                            $instruments = wp_get_post_terms( $person->ID, 'ulm_instrument', array( 'fields' => 'names' ) );

                            // Build media gallery array
                            $media_items = array();
                            if ( ! empty( $media_gallery ) && is_array( $media_gallery ) ) {
                                foreach ( $media_gallery as $item ) {
                                    if ( ! empty( $item['file'] ) ) {
                                        $media_items[] = array(
                                            'url' => $item['file'],
                                            'caption' => $item['caption'] ? $item['caption'] : basename( $item['file'] ),
                                        );
                                    }
                                }
                            }

                            $profile_payload = array(
                                'name' => $display_name,
                                'photo' => $photo,
                                'role' => $role,
                                'years' => $years,
                                'gradYear' => $grad_year,
                                'current' => implode( ' · ', array_filter( array( $current_title, $current_org ) ) ),
                                'location' => $location,
                                'bio' => $bio ? $bio : wp_strip_all_tags( get_the_content( null, false, $person ) ),
                                'progression' => $progression,
                                'website' => $website,
                                'mediaItems' => $media_items,
                                'instruments' => ! empty( $instruments ) ? implode( ', ', $instruments ) : '',
                            );

                            $card_title = $current_title;
                            if ( ! $card_title && $current_org ) {
                                $card_title = $current_org;
                            }
                            if ( ! $card_title && $role ) {
                                $card_title = $role;
                            }
                            if ( ! $card_title && ! empty( $instruments ) ) {
                                $card_title = implode( ', ', $instruments );
                            }
                            ?>

                            <div class="ulm-location-alumni-card js-ulm-alumni-trigger" role="button" tabindex="0" data-alumni="<?php echo esc_attr( wp_json_encode( $profile_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
                                <?php if ( $photo ) : ?>
                                    <img src="<?php echo esc_url( $photo ); ?>"
                                         alt="<?php echo esc_attr( $display_name ); ?>"
                                         class="ulm-location-photo"
                                         loading="lazy">
                                <?php else : ?>
                                    <div class="ulm-location-photo ulm-photo-placeholder" aria-hidden="true"></div>
                                <?php endif; ?>
                                <div class="ulm-location-card-info">
                                    <div class="ulm-location-name"><?php echo esc_html( $display_name ); ?></div>
                                    <?php if ( $card_title ) : ?>
                                        <div class="ulm-location-title"><?php echo esc_html( $card_title ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </div>
                </div>
            </div>
        <?php
            endforeach;
        endif;
        ?>
    </div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {
    const headers = document.querySelectorAll( '[data-toggle-location]' );
    headers.forEach( function( header ) {
        header.addEventListener( 'click', function() {
            const content = this.nextElementSibling;
            this.classList.toggle( 'expanded' );
            content.classList.toggle( 'expanded' );
        } );
    } );
} );
</script>
