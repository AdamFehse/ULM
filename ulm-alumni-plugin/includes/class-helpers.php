<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper functions for getting post meta with Carbon Fields support
 * Falls back to WordPress post meta if Carbon Fields is not available
 */

if ( ! function_exists( 'ulm_get_field' ) ) {
	function ulm_get_field( $field_name, $post_id ) {
		// Try Carbon Fields first
		if ( function_exists( 'carbon_get_post_meta' ) ) {
			$value = carbon_get_post_meta( $post_id, $field_name );
			if ( $value !== '' && $value !== null ) {
				return $value;
			}
		}

		// Fall back to post meta
		return get_post_meta( $post_id, $field_name, true );
	}
}

if ( ! function_exists( 'ulm_get_field_array' ) ) {
	function ulm_get_field_array( $field_name, $post_id ) {
		// Try Carbon Fields first
		if ( function_exists( 'carbon_get_post_meta' ) ) {
			$value = carbon_get_post_meta( $post_id, $field_name );
			if ( is_array( $value ) && ! empty( $value ) ) {
				// Extract post IDs from association field format if needed
				$ids = array();
				foreach ( $value as $item ) {
					if ( is_array( $item ) && isset( $item['id'] ) ) {
						$ids[] = $item['id'];
					} elseif ( is_object( $item ) && property_exists( $item, 'id' ) ) {
						$ids[] = $item->id;
					} elseif ( is_numeric( $item ) ) {
						$ids[] = $item;
					}
				}
				if ( ! empty( $ids ) ) {
					return $ids;
				}
				return $value;
			}
		}

		// Fall back to post meta
		$value = get_post_meta( $post_id, $field_name, true );
		if ( is_array( $value ) ) {
			return $value;
		}

		return array();
	}
}
