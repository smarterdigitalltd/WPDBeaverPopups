<?php

namespace WPD\BeaverPopups\Features;

use WPD\BeaverPopups\Plugin;
use WPD\BeaverPopups\Helpers\Util;

class ScrollTrigger {

	/**
	 * @var
	 */
	protected static $instance = null;

	/**
	 * PopupFromLink constructor.
	 */
	protected function __construct() {
		$this->registerHooks();
	}

	/**
	 * Get singleton instance
	 *
	 * @since   1.0.0
	 *
	 * @return  mixed Plugin Instance of the plugin
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register filters and actions hooks
	 */
	public function registerHooks() {
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'modifyRowSettingsForm' ], 10, 2 );
		add_filter( 'fl_builder_row_attributes', [ __CLASS__, 'addRowAttributesForScrollTrigger' ], 10, 2 );
		add_action( 'fl_builder_before_save_layout', [ __CLASS__, 'setScrollTriggeredPopupsToMeta' ], 12, 4 );
	}

	/*
	 * Add Settings in BB Row Form
	 *
	 * @since   1.0.0
	 *
	 * @param array $form
	 * @param integer $id
	 *
	 * @return array $form
	 */
	public static function modifyRowSettingsForm( $form, $id ) {
		if ( get_post_type( Util::getPostIdEarly() ) !== Plugin::$config->wp_options['public'][ 'CUSTOM_POST_TYPE_POPUP' ] && 'row' === $id ) {
			$form[ 'tabs' ][ 'advanced' ][ 'sections' ][ 'scroll_trigger' ] = [
				'title'  => __( 'Beaver Popups - Trigger Popup', Plugin::$config->plugin_text_domain ),
				'fields' => [
					'beaver_popups_scrolled_to_row_triggered_popup'  => [
						'type'   => 'suggest',
						'label'  => __( 'Popup', Plugin::$config->plugin_text_domain ),
						'action' => 'fl_as_posts',
						'data'   => Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ],
						'limit'  => 1,
						'help'   => __( 'Select the popup to display when the browser scrolls to the row.', Plugin::$config->plugin_text_domain )
					],
					'beaver_popups_scrolled_to_row_element_position' => [
						'type'    => 'select',
						'label'   => __( 'Open on', Plugin::$config->plugin_text_domain ),
						'options' => [
							'element_bottom' => __( 'Entrance: Top of row hits bottom of page', Plugin::$config->plugin_text_domain ),
							'element_middle' => __( 'Middle: Top of row hits middle of page', Plugin::$config->plugin_text_domain ),
							'element_top'    => __( 'Exit: Top of row hits top of page', Plugin::$config->plugin_text_domain ),
						],
						'help'    => __( 'Select the position of the row to display the popup when the browser is scrolled to', Plugin::$config->plugin_text_domain ),
						'default' => 'element_top'

					]
				]
			];
		}

		return $form;
	}

	/*
	 * Add Row Attributes
	 *
	 * @since   1.0.0
	 *
	 * @param array $attrs
	 * @param array $row
	 *
	 * @return array $attrs
	 */
	public static function addRowAttributesForScrollTrigger( $attrs, $row ) {
		if ( ! \FLBuilderModel::is_builder_active() && !empty( $row->settings->beaver_popups_scrolled_to_row_triggered_popup ) ) {
			$attrs[ 'data-wpd-bb-scroll-trigger-popup-id' ] = $row->settings->beaver_popups_scrolled_to_row_triggered_popup;
			if ( !empty( $row->settings->beaver_popups_scrolled_to_row_element_position ) ) {
				$attrs[ 'data-wpd-bb-popup-row-element-position' ] = $row->settings->beaver_popups_scrolled_to_row_element_position;
			}
		}
		return $attrs;
	}

	/**
	 * When a non popup layout is saved, we find all modules with a popup attached
	 * to the click event and then save them to an array in meta.
	 *
	 * We can then query this later for when we need to render popups in the DOM
	 *
	 * @since   1.0.0
	 *
	 * @param   integer $post_id  The post ID of the popup
	 * @param   boolean $publish  Publish this layout?
	 * @param   array   $data     Layout data
	 * @param   array   $settings Layout settings
	 *
	 * @return  void
	 */
	public static function setScrollTriggeredPopupsToMeta( $post_id, $publish, $data, $settings ) {
		if ( get_post_type() !== Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] ) {
			$rows = \FLBuilderModel::get_nodes('row');
			$scroll_triggered_popups = [];

			if( $rows ) {
				foreach ( $rows as $row ) {
					if ( isset( $row->settings->beaver_popups_scrolled_to_row_triggered_popup ) && Util::isPopup( $row->settings->beaver_popups_scrolled_to_row_triggered_popup ) ) {
						$scroll_triggered_popups[] = $row->settings->beaver_popups_scrolled_to_row_triggered_popup;
					}
				}
				update_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'SCROLL_TRIGGER_POPUPS' ], array_unique( $scroll_triggered_popups ) );
			}
		}
	}
}
