<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="ulm-container">
    <div
        id="ulm-alumni-map"
        class="ulm-alumni-map"
        style="<?php echo esc_attr( 'height: ' . $map_config['height'] . ';' ); ?>"
        data-lat="<?php echo esc_attr( $map_config['lat'] ); ?>"
        data-lng="<?php echo esc_attr( $map_config['lng'] ); ?>"
        data-zoom="<?php echo esc_attr( $map_config['zoom'] ); ?>"
        aria-label="<?php esc_attr_e( 'Alumni map', 'ulm-alumni' ); ?>">
    </div>
</div>
