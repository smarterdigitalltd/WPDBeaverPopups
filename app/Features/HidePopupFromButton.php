<?php

namespace WPD\BeaverPopups\Features;

use WPD\BeaverPopups\Plugin;
use WPD\BeaverPopups\Helpers\Util;

/**
 * Class HidePopupFromButton
 *
 * @package WPD\BeaverPopups\Features
 * @since 1.1.1
 */
class HidePopupFromButton {

	/**
	 * @var
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @since   1.0.0
	 *
	 * @return  mixed Plugin Instance of the plugin
	 */
	public static function getInstance()
	{
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * HidePopupFromButton constructor.
	 */
	protected function __construct()
	{
		$this->registerHooks();
	}

	/**
	 * Register Hooks
	 */
	protected function registerHooks()
	{
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'addSettingsForm' ], 10, 2 );
		add_filter( 'fl_builder_module_attributes', [ __CLASS__, 'setButtonAttributes' ], 10, 2 );
		add_filter( 'fl_builder_node_settings', [ __CLASS__, 'setButtonLinkProperty' ], 10, 2 );
	}

	/**
	 * Add settings form for Button Module
	 * when the BB Builder is active
	 * @param $form
	 * @param $moduleSlug
	 *
	 * @return mixed
	 */
	public static function addSettingsForm( $form, $moduleSlug )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) && 'button' === $moduleSlug ) {
			$form[ 'general' ][ 'sections' ][ 'general' ][ 'fields' ][ 'click_action' ][ 'options' ][ 'enable_dont_show_popup_again' ] = __( "Close (and don't open again)", Plugin::$config->plugin_text_domain );
		}
		return $form;
	}

	/**
	 * Updates button attributes if popup or popup_close is selected as the click action
	 *
	 * @since   1.1.1
	 *
	 * @param   array  $attrs  Existing array of attributes for a module
	 * @param   object $module Module object
	 *
	 * @return  array button attributes
	 */
	public static function setButtonAttributes( $attrs, $module )
	{
		if ( 'button' == $module->slug ) {
			if ( 'enable_dont_show_popup_again' == $module->settings->click_action ) {
				$attrs[ 'class' ][] = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__close-button';
				$attrs[ 'class' ][] = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__close-button--hide-popup';
			}
		}

		return $attrs;
	}

	/**
	 * Updates button link to # if popup or popup_close is selected as the click action
	 *
	 * @since   1.1.1
	 *
	 * @param   object $settings Settings object of current module
	 * @param   object $node     Full node/module object
	 *
	 * @return  object New settings
	 */
	public static function setButtonLinkProperty( $settings, $node )
	{
		if ( 'module' === $node->type && isset( $node->settings->click_action ) && ( 'enable_dont_show_popup_again' === $node->settings->click_action ) ) {
			$settings->link = '#';
		}

		return $settings;
	}
}