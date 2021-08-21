<?php

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;

class Util {

	/**
	 * Organize array of items by some item field
	 *
	 * @param $array
	 * @param $keyOrGetter
	 *
	 * @return array
	 */
	public static function organizeArrayByKey( $array, $keyOrGetter )
	{
		$res   = [];
		$first = reset( $array );
		if ( $first ) {
			if ( is_object( $first ) && method_exists( $first, $keyOrGetter ) ) {
				foreach ( $array as $item ) {
					$key         = call_user_func( [ $item, $keyOrGetter ] );
					$res[ $key ] = $item;
				}
			}
			else {
				foreach ( $array as $item ) {
					$key         = self::getItem( $item, $keyOrGetter );
					$res[ $key ] = $item;
				}
			}
		}

		return $res;
	}

	/**
	 * Returns object's property or array's element by key
	 * in case of absence returns default value
	 *
	 * @param array|object $data to extract element from
	 * @param string       $key
	 * @param mixed        $defaultValue
	 *
	 * @return mixed
	 */
	public static function getItem( $data, $key, $defaultValue = "" )
	{
		$value = $defaultValue;
		if ( is_object( $data ) && isset( $data->$key ) ) {
			$value = $data->$key;
		}
		if ( is_array( $data ) && isset( $data[ $key ] ) ) {
			$value = $data[ $key ];
		}

		return $value;
	}

	/**
	 * Create a box shadow in PHP for dynamic CSS
	 *
	 * @param int    $horizontalLength Horizontal length for box shadow
	 * @param int    $verticalLength   Vertical length for box shadow
	 * @param int    $spreadRadius     Spread of box shadow
	 * @param int    $blurRadius       Blur of box shadow
	 * @param string $color            Box shadow color
	 * @param float  $colorOpacity     Box shadow color opacity
	 *
	 * @return string
	 */
	public static function createBoxShadow( $horizontalLength = 0, $verticalLength = 0, $spreadRadius = 0, $blurRadius = 0, $color = '000', $colorOpacity = 0.5 )
	{
		$boxShadowProps = [];
		$boxShadowValue = null;
		$i              = 0;

		$boxShadowProps[ 'horizontalLength' ] = $horizontalLength ? $horizontalLength . 'px' : $horizontalLength;
		$boxShadowProps[ 'verticalLength' ]   = $verticalLength ? $verticalLength . 'px' : $verticalLength;
		$boxShadowProps[ 'spreadRadius' ]     = $spreadRadius ? $spreadRadius . 'px' : $spreadRadius;
		$boxShadowProps[ 'blurRadius' ]       = $blurRadius ? $blurRadius . 'px' : $blurRadius;
		$boxShadowProps[ 'color' ]            = Util::hex2rgba( $color, $colorOpacity, true );

		foreach ( $boxShadowProps as $prop => $value ) :
			if ( $i ) {
				$boxShadowValue .= ' ';
			}

			$boxShadowValue .= $value;

			$i ++;
		endforeach;

		return $boxShadowValue;
	}

	/**
	 * Hex to RGB(A)
	 *
	 * @param string $color   Hex value
	 * @param int    $opacity Opacity (0 - 1)
	 * @param bool   $include_prefix
	 *
	 * @return string
	 */
	public static function hex2rgba( $color, $opacity = 1, $include_prefix = true )
	{
		if ( '#' === $color[ 0 ] ) {
			$color = substr( $color, 1 );
		}

		if ( 6 === strlen( $color ) ) {
			list( $r, $g, $b ) = [
				$color[ 0 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 3 ],
				$color[ 4 ] . $color[ 5 ]
			];
		}
		elseif ( 3 === strlen( $color ) ) {
			list( $r, $g, $b ) = [
				$color[ 0 ] . $color[ 0 ],
				$color[ 1 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 2 ]
			];
		}
		else {
			return false;
		}

		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		$rgba = $r . ',' . $g . ',' . $b . ',' . $opacity;

		if ( $include_prefix ) {
			$rgba = 'rgba(' . $rgba . ')';
		}

		return $rgba;
	}

	/**
	 * Check if a given ID is an active/published popup
	 *
	 * @param $popupId ID of the popup to check
	 *
	 * @return boolean
	 */
	public static function isPopup( $popupId )
	{
		return Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] === get_post( $popupId )->post_type && 'publish' === get_post( $popupId )->post_status;
	}

	/**
	 * Get a BB Popup ID early
	 *
	 * @since 1.0.7
	 *
	 * @return int|bool
	 */
	public static function getPostIdEarly()
	{
		$post_id = null;

		if ( false === strpos( $_SERVER[ 'REQUEST_URI' ], Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] ) ) {
			return false;
		}

		if ( isset( $_GET[ 'p' ] ) ) {
			$post_id = $_GET[ 'p' ];
		}
		else if ( $post_id_from_url = url_to_postid( ( isset( $_SERVER[ 'HTTPS' ] ) ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ) ) {
			$post_id = $post_id_from_url;
		}
		else {
			global $wpdb;

			$parts     = explode( '/', $_SERVER[ 'REQUEST_URI' ] );

			// domain.com/?post_type=post_slug
			if ( false !== strpos( $parts[ 1 ], '?', 0 ) ) {
				$sub_parts = explode( '=', $parts[ 1 ] );

				$post_type = str_replace( '?', '', $sub_parts[ 0 ] );

				// slug&somequerystring
				if ( false !== strpos( $sub_parts[ 1 ], '&' ) ) {
					$sub_sub_parts = explode( '&', $sub_parts[ 1 ] );

					$post_slug = $sub_sub_parts[ 0 ];
				}
			}
			else {
				$post_type = $parts[ 1 ];
				$post_slug = $parts[ 2 ];
			}

			$post_id   = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s", $post_slug, $post_type ) );
		}

		return (int) $post_id;
	}

	/**
	 * Pass a hex colour with or without the prepending #
	 *
	 * @param string $hex  The value
	 * @param bool   $with Prepend a # or not
	 *
	 * @return mixed
	 */
	public static function ensureHex( $hex, $with = true )
	{
		$hasHash = strpos( $hex, '#' );

		if ( $with ) {
			if ( $hasHash ) {
				return $hex;
			}
			else {
				return '#' . $hex;
			}
		}
		else {
			if ( $hasHash ) {
				return str_replace( '#', '', $hex );
			}
			else {
				return $hex;
			}
		}
	}

	/**
	 * Check if BB installation is of Version 1.
	 *
	 * @return bool true if BB is of Version 1.
	 */
	public static function isVersion1(){
		$version = explode( '.', FL_BUILDER_VERSION );
		return $version[0] == 1;
	}
}
