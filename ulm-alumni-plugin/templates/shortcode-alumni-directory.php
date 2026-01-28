<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Stats Dashboard -->
<?php
$stats = ULM_Alumni_Stats::get_all_stats();
?>
<div class="ulm-alumni-stats">
    <!-- Total Alumni -->
    <div class="ulm-stat-card total">
        <div class="ulm-stat-number"><?php echo esc_html( $stats['total'] ); ?></div>
        <div class="ulm-stat-label">Total Alumni</div>
    </div>

    <!-- Top Instruments -->
    <?php if ( ! empty( $stats['instruments'] ) ) : ?>
        <div class="ulm-stat-card instruments">
            <div class="ulm-stat-number"><?php echo esc_html( count( $stats['instruments'] ) ); ?></div>
            <div class="ulm-stat-label">Instruments</div>
            <?php if ( count( $stats['instruments'] ) > 0 ) : ?>
                <div class="ulm-stat-details">
                    <?php foreach ( array_slice( $stats['instruments'], 0, 3 ) as $instrument ) : ?>
                        <div class="ulm-stat-detail-item">
                            <span class="ulm-stat-detail-name"><?php echo esc_html( $instrument['name'] ); ?></span>
                            <span class="ulm-stat-detail-count"><?php echo esc_html( $instrument['count'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Decades -->
    <?php if ( ! empty( $stats['decades'] ) ) : ?>
        <div class="ulm-stat-card decades">
            <div class="ulm-stat-number"><?php echo esc_html( count( $stats['decades'] ) ); ?></div>
            <div class="ulm-stat-label">Decades</div>
            <div class="ulm-stat-details">
                <?php foreach ( array_slice( $stats['decades'], 0, 3 ) as $decade => $count ) : ?>
                    <div class="ulm-stat-detail-item">
                        <span class="ulm-stat-detail-name"><?php echo esc_html( $decade ); ?></span>
                        <span class="ulm-stat-detail-count"><?php echo esc_html( $count ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Locations -->
    <?php if ( ! empty( $stats['locations'] ) ) : ?>
        <div class="ulm-stat-card locations">
            <div class="ulm-stat-number"><?php echo esc_html( count( $stats['locations'] ) ); ?></div>
            <div class="ulm-stat-label">Locations</div>
            <div class="ulm-stat-details">
                <?php foreach ( array_slice( $stats['locations'], 0, 3 ) as $location => $count ) : ?>
                    <div class="ulm-stat-detail-item">
                        <span class="ulm-stat-detail-name"><?php echo esc_html( $location ); ?></span>
                        <span class="ulm-stat-detail-count"><?php echo esc_html( $count ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

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

<!-- Search, Filter & Sort Controls -->
<div class="ulm-container">
    <div class="ulm-alumni-filters">
        <input type="text" class="ulm-search" placeholder="Search alumni by name..." aria-label="Search alumni">

        <select class="ulm-filter-instrument" aria-label="Filter by instrument">
            <option value="">All Instruments</option>
        </select>

        <select class="ulm-filter-year" aria-label="Filter by graduation year">
            <option value="">All Years</option>
        </select>

        <select class="ulm-filter-location" aria-label="Filter by location">
            <option value="">All Locations</option>
        </select>

        <select class="ulm-sort" aria-label="Sort alumni">
            <option value="name-asc">Name (A-Z)</option>
            <option value="name-desc">Name (Z-A)</option>
            <option value="year-desc">Newest First</option>
            <option value="year-asc">Oldest First</option>
            <option value="years-active-recent">Years Active (Recent)</option>
        </select>

        <button class="ulm-clear-filters" type="button" aria-label="Clear all filters">Clear Filters</button>
    </div>

    <div class="ulm-results-count">
        Showing <span id="ulm-results-number">0</span> alumni
    </div>
</div>

<div class="ulm-container">
    <div class="ulm-alumni-grid">
        <?php if ( ! empty( $alumni ) ) : ?>
            <?php foreach ( $alumni as $person ) : setup_postdata( $person ); ?>
                <?php
                $photo = get_the_post_thumbnail_url( $person->ID, 'medium' );
                $full_name = ulm_get_field( 'alumni_full_name', $person->ID );
                $email = ulm_get_field( 'alumni_email', $person->ID );
                $years = ulm_get_field( 'alumni_years_active', $person->ID );
                $grad_year = ulm_get_field( 'alumni_grad_year', $person->ID );
                $role = ulm_get_field( 'alumni_role', $person->ID );
                $current_title = ulm_get_field( 'alumni_current_title', $person->ID );
                $current_org = ulm_get_field( 'alumni_current_org', $person->ID );
                $location = ulm_get_field( 'alumni_location', $person->ID );
                $progression = ulm_get_field( 'alumni_progression', $person->ID );
                $website = ulm_get_field( 'alumni_website', $person->ID );
                $bio = ulm_get_field( 'alumni_bio', $person->ID );
                $media_gallery = ulm_get_field_array( 'alumni_media_gallery', $person->ID );
                $instruments = wp_get_post_terms( $person->ID, 'ulm_instrument', array( 'fields' => 'names' ) );
                $display_name = $full_name ? $full_name : get_the_title( $person );

                // Calculate sorting names
                $sort_name = '';
                if ( $full_name ) {
                    $name_parts = explode( ' ', trim( $full_name ) );
                    if ( count( $name_parts ) > 1 ) {
                        $last_name = array_pop( $name_parts );
                        $first_name = implode( ' ', $name_parts );
                        $sort_name = $last_name . '-' . $first_name;
                    } else {
                        $sort_name = $full_name;
                    }
                }

                // Extract most recent year from years_active
                $sort_years_active = '';
                if ( $years ) {
                    $year_parts = explode( '-', $years );
                    $sort_years_active = end( $year_parts );
                }
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
                if ( ! $card_title && $role ) {
                    $card_title = $role;
                }
                if ( ! $card_title && ! empty( $instruments ) ) {
                    $card_title = implode( ', ', $instruments );
                }
                ?>

                <div class="ulm-alumni-card js-ulm-alumni-trigger" role="button" tabindex="0"
                     data-alumni="<?php echo esc_attr( wp_json_encode( $profile_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>"
                     data-name="<?php echo esc_attr( $display_name ); ?>"
                     data-sort-name="<?php echo esc_attr( $sort_name ); ?>"
                     data-instruments="<?php echo esc_attr( implode( ', ', $instruments ) ); ?>"
                     data-year="<?php echo esc_attr( $grad_year ); ?>"
                     data-sort-year="<?php echo esc_attr( $grad_year ); ?>"
                     data-sort-years-active="<?php echo esc_attr( $years ); ?>"
                     data-decade="<?php echo esc_attr( $grad_year ? ( (int) substr( $grad_year, 0, 3 ) . '0s' ) : '' ); ?>"
                     data-location="<?php echo esc_attr( $location ); ?>">
                    <?php if ( $photo ) : ?>
                        <img src="<?php echo esc_url( $photo ); ?>" 
                             alt="<?php echo esc_attr( $display_name ); ?>"
                             class="ulm-alumni-photo"
                             loading="lazy">
                    <?php else : ?>
                        <div class="ulm-alumni-photo ulm-photo-placeholder" aria-hidden="true"></div>
                    <?php endif; ?>

                    <div class="ulm-alumni-card-info">
                        <h3 class="ulm-alumni-name">
                            <?php echo esc_html( $display_name ); ?>
                        </h3>

                    <?php if ( $card_title ) : ?>
                        <div class="ulm-alumni-instrument">
                            <?php echo esc_html( $card_title ); ?>
                        </div>
                    <?php endif; ?>

                    </div>

                    <?php if ( $bio ) : ?>
                        <div class="ulm-alumni-bio">
                            <?php echo esc_html( $bio ); ?>
                        </div>
                    <?php elseif ( get_the_content( null, false, $person ) ) : ?>
                        <div class="ulm-alumni-bio">
                            <?php echo wp_kses_post( get_the_content( null, false, $person ) ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $progression ) : ?>
                        <div class="ulm-alumni-bio">
                            <?php echo esc_html( $progression ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $website ) : ?>
                        <div class="ulm-alumni-bio">
                            <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html( $website ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endforeach; wp_reset_postdata(); ?>
        <?php else : ?>
            <p>No alumni found.</p>
        <?php endif; ?>
    </div>
</div>
