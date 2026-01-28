<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ULM_Alumni_Stats {

	/**
	 * Get total alumni count
	 */
	public static function get_total_count() {
		return (int) wp_count_posts( 'ulm_alumni' )->publish;
	}

	/**
	 * Get alumni count by instrument (top N)
	 */
	public static function get_instruments_stats( $limit = 5 ) {
		$terms = get_terms( array(
			'taxonomy' => 'ulm_instrument',
			'hide_empty' => true,
			'number' => $limit,
			'orderby' => 'count',
			'order' => 'DESC',
		) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return array_map( function( $term ) {
			return array(
				'name' => $term->name,
				'count' => $term->count,
			);
		}, $terms );
	}

	/**
	 * Get alumni count by graduation decade
	 */
	public static function get_decade_stats() {
		$alumni = get_posts( array(
			'post_type' => 'ulm_alumni',
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		$decades = array();

		foreach ( $alumni as $alumni_id ) {
			$grad_year = ulm_get_field( 'alumni_grad_year', $alumni_id );
			if ( empty( $grad_year ) ) {
				continue;
			}

			$decade = (int) substr( $grad_year, 0, 3 ) . '0s';
			if ( ! isset( $decades[ $decade ] ) ) {
				$decades[ $decade ] = 0;
			}
			$decades[ $decade ]++;
		}

		// Sort by decade (newest first)
		krsort( $decades );

		return $decades;
	}

	/**
	 * Get alumni count by location (top N)
	 */
	public static function get_location_stats( $limit = 5 ) {
		$alumni = get_posts( array(
			'post_type' => 'ulm_alumni',
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		$locations = array();

		foreach ( $alumni as $alumni_id ) {
			$location = ulm_get_field( 'alumni_location', $alumni_id );
			if ( empty( $location ) ) {
				continue;
			}

			if ( ! isset( $locations[ $location ] ) ) {
				$locations[ $location ] = 0;
			}
			$locations[ $location ]++;
		}

		// Sort by count (highest first)
		arsort( $locations );

		// Return top N
		return array_slice( $locations, 0, $limit, true );
	}

	/**
	 * Format stats for display
	 */
	public static function get_all_stats() {
		return array(
			'total' => self::get_total_count(),
			'instruments' => self::get_instruments_stats(),
			'decades' => self::get_decade_stats(),
			'locations' => self::get_location_stats(),
		);
	}
}
