<?php

/**
 * WPD BB Additions Integration
 *
 * @package     WPD\BeaverPopups\Integrations
 * @since       1.0.2
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Integrations;

use FLBuilderModel;
use WPD\BeaverPopups\Helpers\Util;
use WPD\BeaverPopups\Plugin;

/**
 * Class WPDBBAdditionsIntegration
 *
 * @package WPD\BeaverPopups\Integrations
 */
class WPDBBAdditionsIntegration
{
	/**
	 * @var
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @since   1.0.11
	 *
	 * @return  mixed Instance of the class
	 */
	public static function getInstance()
	{
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since   1.0.11
	 */
	protected function __construct()
	{
		$this->registerHooks();
	}

	/**
	 * Register filters and actions hooks
	 *
	 * @since   1.0.2
	 *
	 * @return  void
	 */
	public function registerHooks() {
		if ( defined( 'WPD_BB_ADDITIONS_PLUGIN_SLUG' ) ) {
			add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'enableAutoplayOptionOnWPDOptimisedVideo' ], 10, 2 );
			add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'disableOpenVideoInLightBoxWPDOptimisedVideo' ], 11, 2 );
		}
	}

	/**
	 * Adds an 'autoplay' option on WPD Optimised Video
	 *
	 * @since   1.0.2
	 *
	 * @param   array  $form       Settings form
	 * @param   string $moduleSlug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function enableAutoplayOptionOnWPDOptimisedVideo( $form, $moduleSlug ) {
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) ) {
			if ( 'wpd-optimised-video' == $moduleSlug ) {
				$form[ 'general' ][ 'sections' ][ 'integrations' ] = [
					'title'  => __( 'Integrations', Plugin::$config->plugin_text_domain ),
					'fields' => [
						'trigger_autoplay' => [
							'type'    => 'select',
							'label'   => __( 'Autoplay when Beaver Popups popup opens (video will be muted to comply with Google Chrome media policies)', Plugin::$config->plugin_text_domain ),
							'options' => [
								'yes' => __( 'Yes', Plugin::$config->plugin_text_domain ),
								'no'  => __( 'No', Plugin::$config->plugin_text_domain ),
							],
							'default' => 'no',
						]
					]
				];
			}
		}

		return $form;
	}

	/**
	 * Removes 'open_video_in_lightbox' option on WPD Optimised Video
	 *
	 * @since   1.0.10
	 *
	 * @param   array  $form       Settings form
	 * @param   string $moduleSlug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function disableOpenVideoInLightBoxWPDOptimisedVideo( $form, $moduleSlug ) {
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) ) {
			if ( 'wpd-optimised-video' === $moduleSlug ) {
				unset( $form[ 'general' ][ 'sections' ][ 'open_video_in_lightbox' ] );
			}
		}

		return $form;
	}
}
