<?php

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.10
 */
if ( ! defined( 'WPINC' ) ) {
	die( 'No entry' );
}

if ( ! function_exists( 'wpd_get_abbreviation_from_slug' ) ) {
	function wpd_get_abbreviation_from_slug( $slug, $prefix = null, $prefix_delimiter = '-' ) {
		$slug_array = explode( '-', $slug );

		$abbv_array = array_map( function( $item ) {
			return $item[ 0 ];
		}, $slug_array );

		$abbv = implode( $abbv_array );

		if ( $prefix ) {
			return $prefix . $prefix_delimiter . $abbv;
		}

		return $abbv;
	}
}
