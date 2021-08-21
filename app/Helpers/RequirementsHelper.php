<?php

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;
use WPD\Toolset\Utilities\AdminNotice;

/**
 * Class RequirementsHelper
 *
 * @package WPD\BeaverPopups\Helpers
 */
class RequirementsHelper
{
	/**
	 * @var
	 */
	protected static $config;
	/**
	 * Add admin notices
	 */
	protected static function addAdminNotice()
	{
		// WP version notice
		if ( version_compare( get_bloginfo( 'version' ), Plugin::$config->plugin_wp_minimum_version, '<' ) ) {
			AdminNotice::add( wpautop( sprintf( __( 'Beaver Popups requires at least WordPress %s+. You are running WordPress %s. Please upgrade and try again.', Plugin::$config->plugin_text_domain ), Plugin::$config->plugin_wp_minimum_version, get_bloginfo( 'version' ) ) ), 'error' );
		}

		// PHP version notice
		if ( version_compare( PHP_VERSION, Plugin::$config->plugin_php_minimum_version, '<' ) ) {
			AdminNotice::add( wpautop( sprintf( __( 'Beaver Popups requires at least PHP %s+. You are running PHP %s. Please upgrade and try again.', Plugin::$config->plugin_text_domain ), Plugin::$config->plugin_php_minimum_version, PHP_VERSION ) ), 'error' );
		}

		// BeaverBuilder availability check
		if ( ! class_exists( 'FLBuilder' ) ) {
			// Beaver Builder not active
			AdminNotice::add( wpautop( __( 'Beaver Popups requires <a href="https://www.wpbeaverbuilder.com/" target="_blank">Beaver Builder Plugin</a>. Please install and activate it before continuing.', Plugin::$config->plugin_text_domain ) ), 'error' );
		}
		else if ( version_compare( FL_BUILDER_VERSION, Plugin::$config->plugin_bb_minimum_version, '<' ) ) {
			// BB Plugin Active
			AdminNotice::add( wpautop( sprintf( __( 'Beaver Popups requires at least Beaver Builder %s+. You are running Beaver Builder %s. Please upgrade and try again.', Plugin::$config->plugin_text_domain ), Plugin::$config->plugin_bb_minimum_version, FL_BUILDER_VERSION ) ), 'error' );
		}
	}

	/**
	 * Check if installation have min requirements.
	 *
	 * @param $config
	 *
	 * @return bool true if server have min requirement
	 */
	public static function isCompatible()
	{
		$pass = true;

		// WP Version Check
		if ( version_compare( get_bloginfo( 'version' ), Plugin::$config->plugin_wp_minimum_version, '<' ) ) {
			$pass = false;
		}
		// PHP Check
		else if ( version_compare( PHP_VERSION, Plugin::$config->plugin_php_minimum_version, '<' ) ) {
			$pass = false;
		}
		// Beaver Builder Check
		else if ( ! class_exists( 'FLBuilder' ) || class_exists( 'FLBuilder' ) && version_compare( FL_BUILDER_VERSION, Plugin::$config->plugin_bb_minimum_version, '<' ) ) {
			$pass = false;
		}

		if ( ! $pass ) {
			self::addAdminNotice();
		}

		return $pass;
	}
}
