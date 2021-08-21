<?php

/**
 * PowerPack Integration
 *
 * @package     WPD\BeaverPopups\Integrations
 * @since       1.0.1
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Integrations;

use FLBuilderModel;
use WPD\BeaverPopups\Helpers\PopupHelper;
use WPD\BeaverPopups\Helpers\AssetHelper;
use WPD\BeaverPopups\Helpers\Util;
use WPD\BeaverPopups\Plugin;

class PowerpackIntegration
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
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public function registerHooks()
	{
		if (class_exists('BB_PowerPack')) {
			add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
			add_filter('fl_builder_register_settings_form', [__CLASS__, 'enablePopupLinkOptionOnButton'], 10, 2);
			add_filter('fl_builder_register_settings_form', [__CLASS__, 'enableClosePopupOptionOnButton'], 10, 2);
			add_filter('fl_builder_module_attributes', [__CLASS__, 'setButtonAttributes'], 10, 2);
		}
	}

	/**
	 * Adds 'popup' to normal BB button click actions. This is disabled on
	 * buttons within popups
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $form       Settings form
	 * @param   string $moduleSlug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function enablePopupLinkOptionOnButton($form, $moduleSlug)
	{
		if (Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] !== get_post_type(Util::getPostIdEarly())) {
			if ('pp-smart-button' == $moduleSlug) {
				$linkFields = $form[ 'general' ][ 'sections' ][ 'link' ][ 'fields' ];

				$form[ 'general' ][ 'sections' ][ 'link' ][ 'fields' ] = array_merge([
					'click_action' => [
						'type'    => 'select',
						'label'   => __('Click Action', Plugin::$config->plugin_text_domain),
						'options' => [
							'link'  => __('Link', Plugin::$config->plugin_text_domain),
							'popup' => __('Popup', Plugin::$config->plugin_text_domain),
						],
						'default' => 'link',
						'toggle'  => [
							'link'  => [
								'fields' => [
									'link',
									'link_target'
								]
							],
							'popup' => [
								'fields' => ['popup']
							]
						]
					]
				], $linkFields);

				$form[ 'general' ][ 'sections' ][ 'link' ][ 'fields' ][ 'popup' ] = [
					'type'   => 'suggest',
					'label'  => __('Select Popup', Plugin::$config->plugin_text_domain),
					'action' => 'fl_as_posts',
					'data'   => Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ],
					'limit'  => 1,
				];
			}
		}

		return $form;
	}

	/**
	 * Adds 'close_popup' to PPBB button click actions. This is disabled on
	 * buttons not within popups
	 *
	 * @since   1.0.6
	 *
	 * @param   array  $form       Settings form
	 * @param   string $moduleSlug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function enableClosePopupOptionOnButton($form, $moduleSlug)
	{
		if (Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type(Util::getPostIdEarly())) {
			if ('pp-smart-button' == $moduleSlug) {
				$linkFields = $form[ 'general' ][ 'sections' ][ 'link' ][ 'fields' ];

				$form[ 'general' ][ 'sections' ][ 'link' ][ 'fields' ] = array_merge([
					'click_action' => [
						'type'    => 'select',
						'label'   => __('Click Action', Plugin::$config->plugin_text_domain),
						'options' => [
							'link'        => __('Link', Plugin::$config->plugin_text_domain),
							'close_popup' => __('Close Popup', Plugin::$config->plugin_text_domain),
						],
						'default' => 'link',
						'toggle'  => [
							'link' => [
								'fields' => [
									'link',
									'link_target'
								]
							]
						]
					]
				], $linkFields);
			}
		}

		return $form;
	}

	/**
	 * Updates button attributes if popup is selected as the click action
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $attrs  Existing array of attributes for a module
	 * @param   object $module Module object
	 *
	 * @return  array button attributes
	 */
	public static function setButtonAttributes($attrs, $module)
	{
		if ('pp-smart-button' == $module->slug) {
			if ('popup' == $module->settings->click_action && $module->settings->popup) {
				if (Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post($module->settings->popup)->post_type && 'publish' === get_post($module->settings->popup)->post_status) {
					$attrs[ 'data-wpd-bb-popup-id' ] = $module->settings->popup;
					$attrs[ 'class' ][]              = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__button--enabled';

					/**
					 * Add the popup to the global variable for future use
					 */
					$popup                    = new \stdClass();
					$popup->id                = (int) $module->settings->popup;
					$activePopupsOnThisPage   = PopupHelper::getActivePopupsOnCurrentPage();
					$activePopupsOnThisPage[] = $popup;
				}
				else {
					$attrs[ 'class' ][] = 'wpd-orphaned-popup-button';
				}
			}

			if ('close_popup' == $module->settings->click_action) {
				$attrs[ 'class' ][] = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__close-button';
			}
		}

		return $attrs;
	}

	/**
	 * Enqueues styles and scripts
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function enqueueAssets()
	{
		/**
		 * Scripts
		 */
		// Builder only scripts
		if (FLBuilderModel::is_builder_active()) {
			// Ascertain which script handle to use as a dependency
			$fl_builder_handle = defined('WP_DEBUG') && WP_DEBUG ? 'fl-builder' : 'fl-builder-min';

			wp_enqueue_script('wpd-bb-popups-page-builder');
			wp_enqueue_script('wpd-bb-popups-powerpack-page-builder', AssetHelper::getHashedAssetUri('js/powerpack.js'), [
				'jquery',
				$fl_builder_handle
			], false, true);
		}
	}
}
