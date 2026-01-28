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
    <div class="ulm-timeline">
        <?php if ( ! empty( $events ) ) : ?>
            <?php foreach ( $events as $event ) : setup_postdata( $event ); ?>
                <?php
                $event_date = ulm_get_field( 'ulm_event_date', $event->ID );
                $related_alumni_ids = ulm_get_field_array( 'ulm_related_alumni', $event->ID );
                $event_description = ulm_get_field( 'timeline_event_description', $event->ID );
                if ( empty( $event_description ) ) {
                    $event_description = get_the_content( null, false, $event );
                }
                $related_alumni = ! empty( $related_alumni_ids ) ? get_posts( array(
                    'post_type' => 'ulm_alumni',
                    'posts_per_page' => -1,
                    'post__in' => $related_alumni_ids,
                    'orderby' => 'post__in',
                ) ) : array();
                ?>

                <div class="ulm-timeline-item">
                    <div class="ulm-timeline-date">
                        <?php echo esc_html( $event_date ? date_i18n( 'F j, Y', strtotime( $event_date ) ) : '' ); ?>
                    </div>
                    <div class="ulm-timeline-content">
                        <h3 class="ulm-timeline-title">
                            <?php echo esc_html( get_the_title( $event ) ); ?>
                        </h3>
                        <div class="ulm-timeline-description">
                            <?php echo wp_kses_post( $event_description ); ?>
                        </div>

                        <?php if ( ! empty( $related_alumni ) ) : ?>
                            <div class="ulm-timeline-related">
                                <span class="ulm-timeline-related-label">Related alumni:</span>
                                <div class="ulm-timeline-related-grid">
                                    <?php foreach ( $related_alumni as $person ) : ?>
                                        <?php
                                        $photo = get_the_post_thumbnail_url( $person->ID, 'thumbnail' );
                                        $full_name = ulm_get_field( 'alumni_full_name', $person->ID );
                                        $display_name = $full_name ? $full_name : get_the_title( $person );
                                        $years = ulm_get_field( 'alumni_years_active', $person->ID );
                                        $grad_year = ulm_get_field( 'alumni_grad_year', $person->ID );
                                        $role = ulm_get_field( 'alumni_role', $person->ID );
                                        $current_title = ulm_get_field( 'alumni_current_title', $person->ID );
                                        $current_org = ulm_get_field( 'alumni_current_org', $person->ID );
                                        $location = ulm_get_field( 'alumni_location', $person->ID );
                                        $bio = ulm_get_field( 'alumni_bio', $person->ID );
                                        $progression = ulm_get_field( 'alumni_progression', $person->ID );
                                        $website = ulm_get_field( 'alumni_website', $person->ID );
                                        $media_gallery = ulm_get_field_array( 'alumni_media_gallery', $person->ID );
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
                                        ?>
                                        <div class="ulm-timeline-related-card js-ulm-alumni-trigger" role="button" tabindex="0" data-alumni="<?php echo esc_attr( wp_json_encode( $profile_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
                                            <?php if ( $photo ) : ?>
                                                <img src="<?php echo esc_url( $photo ); ?>"
                                                     alt="<?php echo esc_attr( $display_name ); ?>"
                                                     class="ulm-timeline-related-photo"
                                                     loading="lazy">
                                            <?php else : ?>
                                                <div class="ulm-timeline-related-photo ulm-photo-placeholder" aria-hidden="true"></div>
                                            <?php endif; ?>
                                            <div class="ulm-timeline-related-card-info">
                                                <div class="ulm-timeline-related-name">
                                                    <?php echo esc_html( $display_name ); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; wp_reset_postdata(); ?>
        <?php else : ?>
            <p>No timeline events found.</p>
        <?php endif; ?>
    </div>
</div>
