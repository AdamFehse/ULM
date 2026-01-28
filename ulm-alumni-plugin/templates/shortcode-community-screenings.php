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

<div class="ulm-container">
    <div class="ulm-screenings-placeholder">
        <p><?php esc_html_e( 'Community Screenings feature coming soon.', 'ulm-alumni' ); ?></p>
        <p><?php esc_html_e( 'This section will display alumni community screenings, events, and gatherings.', 'ulm-alumni' ); ?></p>
    </div>

    <div
        id="ulm-alumni-map"
        class="ulm-alumni-map"
        data-lat="39.8283"
        data-lng="-98.5795"
        data-zoom="4"
        aria-label="<?php esc_attr_e( 'Alumni map', 'ulm-alumni' ); ?>">
    </div>
</div>
