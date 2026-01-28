<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!--
STUB: Community Screenings Feature

Planned Implementation:
- Display upcoming and past community screenings
- Filter by date, location, venue
- Show related alumni for each screening
- Link to screening media (posters, programs, photos)
- Calendar view of screening events
- Location-based screening map

Current Status: Feature stub - no active functionality
Dependencies: class-screenings.php (not yet registered)
Target: Phase 2 implementation
-->

<?php
$screenings = get_posts( array(
    'post_type' => 'ulm_screening',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
) );

$today = current_time( 'Y-m-d' );
$upcoming = array();
$past = array();
$undated = array();
$map_points = array();

foreach ( $screenings as $screening ) {
    $date = ulm_get_field( 'screening_date', $screening->ID );
    if ( $date ) {
        if ( $date >= $today ) {
            $upcoming[] = $screening;
        } else {
            $past[] = $screening;
        }
    } else {
        $undated[] = $screening;
    }

    $lat = get_post_meta( $screening->ID, 'screening_lat', true );
    $lng = get_post_meta( $screening->ID, 'screening_lng', true );
    if ( $lat !== '' && $lng !== '' ) {
        $map_points[] = array(
            'id' => $screening->ID,
            'name' => ulm_get_field( 'screening_title', $screening->ID ),
            'location' => ulm_get_field( 'screening_location', $screening->ID ),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        );
    }
}

usort( $upcoming, function( $a, $b ) {
    $date_a = ulm_get_field( 'screening_date', $a->ID );
    $date_b = ulm_get_field( 'screening_date', $b->ID );
    return strtotime( $date_a ) <=> strtotime( $date_b );
} );

usort( $past, function( $a, $b ) {
    $date_a = ulm_get_field( 'screening_date', $a->ID );
    $date_b = ulm_get_field( 'screening_date', $b->ID );
    return strtotime( $date_b ) <=> strtotime( $date_a );
} );
?>

<div class="ulm-container">
    <div class="ulm-screenings">
        <div class="ulm-section-header">
            <h2><?php esc_html_e( 'Community Screenings', 'ulm-alumni' ); ?></h2>
            <p><?php esc_html_e( 'Upcoming and past events from the ULM community.', 'ulm-alumni' ); ?></p>
        </div>

        <?php if ( empty( $GLOBALS['ulm_screening_modal_rendered'] ) ) : ?>
            <?php $GLOBALS['ulm_screening_modal_rendered'] = true; ?>
            <div class="ulm-modal" id="ulm-screening-modal" aria-hidden="true">
                <div class="ulm-modal__backdrop" data-ulm-screening-close></div>
                <div class="ulm-modal__content" role="dialog" aria-modal="true" aria-labelledby="ulm-screening-modal-title">
                    <button class="ulm-modal__close" type="button" aria-label="Close" data-ulm-screening-close>&times;</button>
                    <div class="ulm-modal__header">
                        <img class="ulm-modal__photo" src="" alt="" />
                        <div>
                            <h3 class="ulm-modal__name" id="ulm-screening-modal-title"></h3>
                            <div class="ulm-modal__meta"></div>
                        </div>
                    </div>
                    <div class="ulm-modal__body"></div>
                    <div class="ulm-modal__links ulm-modal__links--buttons"></div>
                </div>
            </div>
        <?php endif; ?>

        <div
            id="ulm-alumni-map"
            class="ulm-alumni-map"
            data-lat="39.8283"
            data-lng="-98.5795"
            data-zoom="4"
            aria-label="<?php esc_attr_e( 'Alumni map', 'ulm-alumni' ); ?>">
        </div>

        <div class="ulm-screenings-section">
            <h3><?php esc_html_e( 'Upcoming', 'ulm-alumni' ); ?></h3>
            <?php if ( ! empty( $upcoming ) ) : ?>
                <div class="ulm-screenings-list">
                    <?php foreach ( $upcoming as $screening ) : ?>
                        <?php
                        $title = ulm_get_field( 'screening_title', $screening->ID );
                        $date = ulm_get_field( 'screening_date', $screening->ID );
                        $location = ulm_get_field( 'screening_location', $screening->ID );
                        $venue = ulm_get_field( 'screening_venue', $screening->ID );
                        $description = ulm_get_field( 'screening_description', $screening->ID );
                        $tickets_url = ulm_get_field( 'screening_tickets_url', $screening->ID );
                        $related_ids = ulm_get_field_array( 'screening_related_alumni', $screening->ID );
                        $photo = get_the_post_thumbnail_url( $screening->ID, 'medium' );
                        $media_gallery = ulm_get_field_array( 'screening_media', $screening->ID );
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

                        $payload = array(
                            'id' => $screening->ID,
                            'title' => $title ? $title : get_the_title( $screening ),
                            'date' => $date,
                            'dateDisplay' => $date ? date_i18n( 'F j, Y', strtotime( $date ) ) : '',
                            'location' => $location,
                            'venue' => $venue,
                            'description' => $description,
                            'photo' => $photo,
                            'mediaItems' => $media_items,
                            'ticketsUrl' => $tickets_url,
                            'lat' => $lat,
                            'lng' => $lng,
                        );
                        ?>
                        <article class="ulm-screening-card js-ulm-screening-trigger" role="button" tabindex="0" aria-label="<?php echo esc_attr( 'View screening: ' . ( $title ? $title : get_the_title( $screening ) ) ); ?>" data-screening-id="<?php echo esc_attr( $screening->ID ); ?>"
                            data-screening="<?php echo esc_attr( wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
                            <div class="ulm-screening-date">
                                <?php echo esc_html( $date ? date_i18n( 'F j, Y', strtotime( $date ) ) : '' ); ?>
                            </div>
                            <h4 class="ulm-screening-title"><?php echo esc_html( $title ? $title : get_the_title( $screening ) ); ?></h4>
                            <?php if ( $venue || $location ) : ?>
                                <div class="ulm-screening-meta">
                                    <?php echo esc_html( trim( implode( ' · ', array_filter( array( $venue, $location ) ) ) ) ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $description ) : ?>
                                <p class="ulm-screening-description"><?php echo esc_html( $description ); ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $related_ids ) ) : ?>
                                <div class="ulm-screening-related">
                                    <span><?php esc_html_e( 'Related alumni:', 'ulm-alumni' ); ?></span>
                                    <?php echo esc_html( implode( ', ', array_map( 'get_the_title', $related_ids ) ) ); ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="ulm-screenings-empty"><?php esc_html_e( 'No upcoming screenings yet.', 'ulm-alumni' ); ?></p>
            <?php endif; ?>
        </div>

        <div class="ulm-screenings-section">
            <h3><?php esc_html_e( 'Past Screenings', 'ulm-alumni' ); ?></h3>
            <?php if ( ! empty( $past ) ) : ?>
                <div class="ulm-screenings-list">
                    <?php foreach ( $past as $screening ) : ?>
                        <?php
                        $title = ulm_get_field( 'screening_title', $screening->ID );
                        $date = ulm_get_field( 'screening_date', $screening->ID );
                        $location = ulm_get_field( 'screening_location', $screening->ID );
                        $venue = ulm_get_field( 'screening_venue', $screening->ID );
                        $description = ulm_get_field( 'screening_description', $screening->ID );
                        $tickets_url = ulm_get_field( 'screening_tickets_url', $screening->ID );
                        $photo = get_the_post_thumbnail_url( $screening->ID, 'medium' );
                        $media_gallery = ulm_get_field_array( 'screening_media', $screening->ID );
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

                        $payload = array(
                            'id' => $screening->ID,
                            'title' => $title ? $title : get_the_title( $screening ),
                            'date' => $date,
                            'dateDisplay' => $date ? date_i18n( 'F j, Y', strtotime( $date ) ) : '',
                            'location' => $location,
                            'venue' => $venue,
                            'description' => $description,
                            'photo' => $photo,
                            'mediaItems' => $media_items,
                            'ticketsUrl' => $tickets_url,
                            'lat' => $lat,
                            'lng' => $lng,
                        );
                        ?>
                        <article class="ulm-screening-card js-ulm-screening-trigger" role="button" tabindex="0" aria-label="<?php echo esc_attr( 'View screening: ' . ( $title ? $title : get_the_title( $screening ) ) ); ?>" data-screening-id="<?php echo esc_attr( $screening->ID ); ?>"
                            data-screening="<?php echo esc_attr( wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
                            <div class="ulm-screening-date">
                                <?php echo esc_html( $date ? date_i18n( 'F j, Y', strtotime( $date ) ) : '' ); ?>
                            </div>
                            <h4 class="ulm-screening-title"><?php echo esc_html( $title ? $title : get_the_title( $screening ) ); ?></h4>
                            <?php if ( $venue || $location ) : ?>
                                <div class="ulm-screening-meta">
                                    <?php echo esc_html( trim( implode( ' · ', array_filter( array( $venue, $location ) ) ) ) ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $description ) : ?>
                                <p class="ulm-screening-description"><?php echo esc_html( $description ); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="ulm-screenings-empty"><?php esc_html_e( 'No past screenings yet.', 'ulm-alumni' ); ?></p>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $undated ) ) : ?>
            <div class="ulm-screenings-section">
                <h3><?php esc_html_e( 'Other Screenings', 'ulm-alumni' ); ?></h3>
                <div class="ulm-screenings-list">
                    <?php foreach ( $undated as $screening ) : ?>
                        <?php
                        $title = ulm_get_field( 'screening_title', $screening->ID );
                        $location = ulm_get_field( 'screening_location', $screening->ID );
                        $venue = ulm_get_field( 'screening_venue', $screening->ID );
                        $description = ulm_get_field( 'screening_description', $screening->ID );
                        $tickets_url = ulm_get_field( 'screening_tickets_url', $screening->ID );
                        $photo = get_the_post_thumbnail_url( $screening->ID, 'medium' );
                        $media_gallery = ulm_get_field_array( 'screening_media', $screening->ID );
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

                        $payload = array(
                            'id' => $screening->ID,
                            'title' => $title ? $title : get_the_title( $screening ),
                            'location' => $location,
                            'venue' => $venue,
                            'description' => $description,
                            'photo' => $photo,
                            'mediaItems' => $media_items,
                            'ticketsUrl' => $tickets_url,
                            'lat' => $lat,
                            'lng' => $lng,
                        );
                        ?>
                        <article class="ulm-screening-card js-ulm-screening-trigger" role="button" tabindex="0" aria-label="<?php echo esc_attr( 'View screening: ' . ( $title ? $title : get_the_title( $screening ) ) ); ?>" data-screening-id="<?php echo esc_attr( $screening->ID ); ?>"
                            data-screening="<?php echo esc_attr( wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
                            <h4 class="ulm-screening-title"><?php echo esc_html( $title ? $title : get_the_title( $screening ) ); ?></h4>
                            <?php if ( $venue || $location ) : ?>
                                <div class="ulm-screening-meta">
                                    <?php echo esc_html( trim( implode( ' · ', array_filter( array( $venue, $location ) ) ) ) ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $description ) : ?>
                                <p class="ulm-screening-description"><?php echo esc_html( $description ); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ( ! empty( $map_points ) ) : ?>
    <script>
        window.ULMScreeningsMapData = <?php echo wp_json_encode( $map_points ); ?>;
    </script>
<?php endif; ?>
