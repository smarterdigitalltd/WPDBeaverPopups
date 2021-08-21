<?php

/**
 * Beaver Builder management
 *
 * @package     WPD\BeaverPopups\Helpers
 * @since       1.0.0
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Helpers;

use FLBuilder;
use FLBuilderAJAX;
use FLBuilderIcons;
use FLBuilderModel;
use WPD\BeaverPopups\Plugin;

class BeaverBuilderHelper
{

	/**
	 * The path to the template part that contains the popup content once
	 * it's loaded on the front end
	 *
	 * @since   1.0.0
	 */
	const POPUP_TEMPLATE_CONTENT_PART_PATH = 'app/Templates/PopupContent.php';

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
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueueAssets' ] );
		add_action( 'init', [ __CLASS__, 'registerPopupStyleSettingsForm' ] );
		add_action( 'wp', [ __CLASS__, 'addPopupSettingsAjaxHandler' ], 5 );
		add_action( 'wp_footer', [ __CLASS__, 'renderPopupsInDom' ], 10 );
		add_action( 'wp_footer', [ __CLASS__, 'outputPopupJsConfigToFooter' ], 11 );
		add_action( 'add_meta_boxes_' . Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ], [
			__CLASS__,
			'forcePageBuilderOnPopup',
		], 11 );
		add_filter( 'body_class', [ __CLASS__, 'addBodyClassToPopup' ], 10, 1 );
		add_filter( 'body_class', [ __CLASS__, 'addBodyClassIfPopupExists' ], 10, 1 );
		add_action( 'fl_builder_before_save_layout', [ __CLASS__, 'saveDraftPopupStyleSettingsIntoPublished' ], 11, 4 );
		add_action( 'fl_builder_before_save_layout', [ __CLASS__, 'setClickTriggeredPopupsToMeta' ], 11, 4 );
		add_action( 'fl_builder_after_save_layout', [
			__CLASS__,
			'setClickTriggeredPopupsToMetaForSavedModules',
		], 10, 4 );
		add_filter( 'fl_builder_post_types', [ __CLASS__, 'enablePageBuilderOnPopupCpt' ], 10, 1 );
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'enablePopupLinkOptionOnBbButton' ], 10, 2 );
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'enableClosePopupOptionOnBbButton' ], 10, 2 );
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'hideDeprecatedSettings' ], 20, 2 );
		add_filter( 'fl_builder_module_attributes', [ __CLASS__, 'setButtonAttributes' ], 10, 2 );
		add_filter( 'fl_builder_register_settings_form', [ __CLASS__, 'addRowCSSField' ], 10, 2 );
		add_filter( 'fl_builder_render_css', [ __CLASS__, 'renderCustomRowCSS' ], 10, 4 );
		add_filter( 'fl_builder_ui_bar_buttons', [ __CLASS__, 'addPopupOptionsButtonToBuilderBar' ], 10, 1 );
		add_filter( 'fl_builder_render_css', [ __CLASS__, 'saveCustomPopupCssIntoStylesheet' ], 10, 4 );
		add_filter( 'fl_builder_global_posts', [ __CLASS__, 'setPopupsAsGlobalPosts' ], 10, 1 );
		add_filter( 'fl_builder_node_settings', [ __CLASS__, 'setButtonLinkProperty' ], 10, 2 );
	}

	/**
	 * Enables the page builder on Popup CPT
	 *
	 * @since   1.0.0
	 *
	 * @param   string $post_types Post types the builder is enabled for
	 *
	 * @return  string Updated array of post types the builder is enabled for
	 */
	public static function enablePageBuilderOnPopupCpt( $post_types )
	{
		$post_types[] = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ];

		return $post_types;
	}

	/**
	 * Sets the page builder as 'enabled' on CPT
	 *
	 * @since   1.0.0
	 *
	 * @param   $post Post object
	 *
	 * @return  void
	 */
	public static function forcePageBuilderOnPopup( $post )
	{
		FLBuilderModel::enable();
	}

	/**
	 * Adds a body class to single popup to mimic the style when editing/previewing
	 *
	 * @since   1.0.0
	 *
	 * @param   string $body_classes Existing body classes
	 *
	 * @return  string Updated array of body classes
	 */
	public static function addBodyClassToPopup( $body_classes )
	{
		if ( is_singular( $cpt = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) ) {
			$popup_type_class = isset( self::getPopupStyleSettings( null, get_the_ID() )->popup_type ) ? self::getPopupStyleSettings( null, get_the_ID() )->popup_type : 'modal';

			$body_classes[] = 'fl-builder-panel--open';
			$body_classes[] = $cpt . '--active';
			$body_classes[] = $cpt . '__' . $popup_type_class . '--active';
			$body_classes[] = $cpt . '-' . get_the_id() . '--active';

			/*
             * Add extra body class if the BB is of version 1
			 */
			if ( Util::isVersion1() ) {
				$body_classes[] = 'wpd-bb-version--1';
			}
		}

		return $body_classes;
	}

	/**
	 * Adds a body class to a page if a popup exists
	 *
	 * @since   1.0.12
	 *
	 * @param   string $body_classes Existing body classes
	 *
	 * @return  string Updated array of body classes
	 */
	public static function addBodyClassIfPopupExists( $body_classes )
	{
		if ( get_post_type() !== Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] && count( PopupHelper::getActivePopupsOnCurrentPage() ) ) {
			$body_classes[] = 'page--has-' . Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ];
		}

		return $body_classes;
	}

	/**
	 * Get the popup settings
	 *
	 * @since   1.0.0
	 *
	 * @param   string $status  Either published or draft.
	 * @param   int    $post_id The ID of the popup to get settings for.
	 *
	 * @return  object
	 */
	public static function getPopupStyleSettings( $status = null, $post_id = null )
	{
		$status   = ! $status ? FLBuilderModel::get_node_status() : $status;
		$post_id  = ! $post_id ? FLBuilderModel::get_post_id() : $post_id;
		$key      = 'published' == $status ? '_wpd_bb_popup_style_settings' : '_wpd_bb_popup_style_draft_settings';
		$settings = get_metadata( 'post', $post_id, $key, true );
		$defaults = FLBuilderModel::get_settings_form_defaults( 'wpd-bb-popup-styles-settings-form' );

		if ( ! $settings ) {
			$settings = new \stdClass();
		}

		$settings = ( object ) array_merge( ( array ) $defaults, ( array ) $settings );

		return apply_filters( 'wpd_bb_popup_style_settings', $settings, $status, $post_id );
	}

	/**
	 * Enqueues either the font pack specified or enqueues all custom sets
	 *
	 * @since   1.0.0
	 *
	 * @param   string $icon_css_class CSS class
	 *
	 * @return  void
	 */
	public static function enqueueIconPackFromCssClass( $icon_css_class )
	{
		if ( $icon_pack = self::getIconPackName( $icon_css_class ) ) {
			wp_enqueue_style( $icon_pack );
		}
		else {
			FLBuilderIcons::enqueue_all_custom_icons_styles();
		}
	}

	/**
	 * Gets the name of a icon pack based on the class name used. The icon font will then be enqueued
	 *
	 * @since   1.0.0
	 *
	 * @param   string $icon_css_class CSS class
	 *
	 * @return  string Icon pack name
	 */
	public static function getIconPackName( $icon_css_class )
	{
		if ( stristr( $icon_css_class, 'fa-' ) ) {
			return 'font-awesome';
		}
		else if ( stristr( $icon_css_class, 'fi-' ) ) {
			return 'foundation-icons';
		}
		else if ( stristr( $icon_css_class, 'dashicon' ) ) {
			return 'dashicons';
		}

		return null;
	}

	/**
	 * Adds 'Popup Options' to page builder bar
	 *
	 * @todo    'use' the FLBuilderUserAccess Class when BB 1.10 is more widely adopted
	 *
	 * @since   1.0.0
	 *
	 * @param array $buttons
	 *
	 * @return  array Buttons in row
	 */
	public static function addPopupOptionsButtonToBuilderBar( $buttons )
	{
		if ( class_exists( '\FLBuilderUserAccess' ) && method_exists( '\FLBuilderUserAccess', 'current_user_can' ) ) {
			$show = \FLBuilderUserAccess::current_user_can( 'unrestricted_editing' ) && Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] == get_post_type();
		}
		else {
			$show = \FLBuilderModel::current_user_has_editing_capability() && Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] == get_post_type();
		}
		$glow_effect = false;

		$current_user_id = get_current_user_id();
		if ( ! get_user_meta( $current_user_id, '_wpd_bb_popups_popup_option_click_count', true ) ) {
			$glow_effect = true;
		}

		$buttons[ 'popup-options' ] = [
			'label' => __( 'Popup Options', Plugin::$config->plugin_text_domain ),
			'class' => $glow_effect ? 'wpd-bb-popup-options-not-clicked' : '',
			'show'  => $show,
		];

		return $buttons;
	}

	/**
	 * Adds 'popup' to normal BB button click actions. This is disabled on
	 * buttons within popups
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $form        Settings form
	 * @param   string $module_slug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function enablePopupLinkOptionOnBbButton( $form, $module_slug )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] !== get_post_type( Util::getPostIdEarly() ) ) {
			if ( 'button' === $module_slug ) {
				$form[ 'general' ][ 'sections' ][ 'general' ][ 'fields' ][ 'click_action' ][ 'options' ][ 'popup' ] = __( 'Popup', Plugin::$config->plugin_text_domain );
				$form[ 'general' ][ 'sections' ][ 'general' ][ 'fields' ][ 'click_action' ][ 'toggle' ][ 'popup' ]  = [
					'sections' => [
						'popup',
					],
				];

				$form[ 'general' ][ 'sections' ][ 'popup' ] = [
					'title'  => __( 'Popup', Plugin::$config->plugin_text_domain ),
					'fields' => [
						'popup' => [
							'type'   => 'suggest',
							'label'  => __( 'Select Popup', Plugin::$config->plugin_text_domain ),
							'action' => 'fl_as_posts',
							'data'   => Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ],
							'limit'  => 1,
						],
					],
				];
			}
		}

		return $form;
	}

	/**
	 * Adds 'close' to normal BB button click actions when within a popup. This is disabled on
	 * buttons not within popups
	 *
	 * @since   1.0.6
	 *
	 * @param   array  $form        Settings form
	 * @param   string $module_slug Module slug
	 *
	 * @return  array Settings form
	 */
	public static function enableClosePopupOptionOnBbButton( $form, $module_slug )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) ) {
			if ( 'button' == $module_slug ) {
				$form[ 'general' ][ 'sections' ][ 'general' ][ 'fields' ][ 'click_action' ][ 'options' ][ 'close_popup' ] = __( 'Close Popup', Plugin::$config->plugin_text_domain );
			}
		}

		return $form;
	}

	/**
	 * Hide deprecated sections & fields in settings fields
	 *
	 * @since   1.2.0
	 *
	 * @param   array  $form        Settings form
	 * @param   string $module_slug Module slug
	 *
	 * @hooked  fl_builder_register_settings_form | 20
	 * @return  array Settings form
	 */
	public static function hideDeprecatedSettings( $form, $module_slug )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) ) {
			// @todo implement here

            if (!apply_filters('wpd_beaver_popups/enable_deprecated_fields', false)) {
	            // loop / array_search() & check for deprecated flag
	            // unset / filter out deprecated items and save in $form
	            // we might have done this before - but maybe array_search is shorter?
            }
		}

		return $form;
	}

	/**
	 * Updates button attributes if popup or popup_close is selected as the click action
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $attrs  Existing array of attributes for a module
	 * @param   object $module Module object
	 *
	 * @return  array button attributes
	 */
	public static function setButtonAttributes( $attrs, $module )
	{
		if ( 'button' == $module->slug ) {
			if ( 'popup' == $module->settings->click_action && $module->settings->popup ) {
				if ( Util::isPopup( $module->settings->popup ) ) {
					$attrs[ 'data-wpd-bb-popup-id' ] = $module->settings->popup;
					$attrs[ 'class' ][]              = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__button--enabled';
				}
				else {
					$attrs[ 'class' ][] = 'wpd-orphaned-popup-button';
				}
			}

			if ( 'close_popup' == $module->settings->click_action ) {
				$attrs[ 'class' ][] = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] . '__close-button';
			}
		}

		return $attrs;
	}

	/**
	 * Updates button link to # if popup or popup_close is selected as the click action
	 *
	 * @since   1.0.6
	 *
	 * @param   object $settings Settings object of current module
	 * @param   object $node     Full node/module object
	 *
	 * @return  object New settings
	 */
	public static function setButtonLinkProperty( $settings, $node )
	{
		if ( 'module' === $node->type && isset( $node->settings->click_action ) && ( 'close_popup' === $node->settings->click_action || 'popup' === $node->settings->click_action ) ) {
			$settings->link = '#';
		}

		return $settings;
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
	public static function setClickTriggeredPopupsToMeta( $post_id, $publish, $data, $settings )
	{
		if ( get_post_type() !== Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
			$modules                 = FLBuilderModel::get_nodes( 'module' );
			$button_triggered_popups = [];

			foreach ( $modules as $module ) {
				if ( 'popup' === $module->settings->click_action && Util::isPopup( $module->settings->popup ) ) {
					$button_triggered_popups[] = $module->settings->popup;
				}
			}

			update_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], array_unique( $button_triggered_popups ) );
		}
	}

	/**
	 * Set ClickTriggeredPopup Meta Data to the post if
	 * the post contains saved modules with the button module with the popup action
	 *
	 * @since   1.0.11
	 *
	 * @param $post_id
	 * @param $publish
	 * @param $data
	 * @param $settings
	 */
	public static function setClickTriggeredPopupsToMetaForSavedModules( $post_id, $publish, $data, $settings )
	{
		$parent_page_button_triggered_popups  = (array) get_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], true );
		$layouts                              = \FLBuilderModel::get_layout_data( 'published', $post_id );
		$saved_module_button_triggered_popups = [];
		if ( $layouts ) {
			foreach ( $layouts as $node => $layout ) {
				$layout_id          = \FLBuilderModel::get_node_template_post_id( $layout->template_id );
				$layout_popup_metas = (array) get_metadata( 'post', $layout_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], true );
				if ( $layout_popup_metas ) {
					foreach ( $layout_popup_metas as $layout_popup_meta ) {
						$saved_module_button_triggered_popups[] = $layout_popup_meta;
					}
				}
			}
			update_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], array_unique( array_merge( $parent_page_button_triggered_popups, $saved_module_button_triggered_popups ) ) );
		}
	}

	/**
	 * Adds a CSS field to rows inside the popup builder
	 *
	 * @since   1.0.2
	 *
	 * @param   array  $form The form
	 * @param   string $id   The ID (type) of the form
	 *
	 * @return  array New form
	 */
	public static function addRowCSSField( $form, $id )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type( Util::getPostIdEarly() ) && 'row' == $id ) {
			$form[ 'tabs' ][ 'advanced' ][ 'sections' ][ 'row_css' ] = [
				'title'  => __( 'Row CSS', Plugin::$config->plugin_text_domain ),
				'fields' => [
					'row_css' => [
						'label'  => __( 'Row CSS', Plugin::$config->plugin_text_domain ),
						'help'   => __( 'Add CSS here to automatically apply it to the row, without using the node ID', Plugin::$config->plugin_text_domain ),
						'type'   => 'code',
						'editor' => 'html',
						'rows'   => '18',
					],
				],
			];
		}

		return $form;
	}

	/**
	 * Renders the custom CSS added to rows
	 *
	 * @since   1.0.2
	 *
	 * @param   mixed  $css             The compiled CSS as part of render_css process
	 * @param   array  $nodes           The nodes on the page
	 * @param   object $global_settings Beaver Builder global settings
	 *
	 * @return  mixed New CSS
	 */
	public static function renderCustomRowCSS( $css, $nodes, $global_settings, $global )
	{
		ob_start();

		foreach ( $nodes as $node_group ) {
			foreach ( $node_group as $node => $node_object ) {
				if ( 'row' === $node_object->type && isset( $node_object->settings->row_css ) && ! empty( $node_object->settings->row_css ) ) : ?>
                    .fl-node-<?= $node_object->node; ?> .fl-row-content-wrap {
					<?= $node_object->settings->row_css; ?>
                    }
				<?php endif;
			}
		}

		$css .= ob_get_clean();

		return $css;
	}

	/**
	 * Add AJAX handlers to display and save settings forms
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function addPopupSettingsAjaxHandler()
	{
		FLBuilderAJAX::add_action( 'wpd_render_bb_popup_styles_settings_form', [
			__CLASS__,
			'renderPopupStylesSettingsForm',
		] );
		FLBuilderAJAX::add_action( 'wpd_save_bb_popup_styles_settings', [
			__CLASS__,
			'savePopupStyleSettings',
		], [ 'settings' ] );
	}

	/**
	 * Register settings forms for per-popup settings. These are accessed via the header bar in the builder
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function registerPopupStyleSettingsForm()
	{
		FLBuilder::register_settings_form( 'wpd-bb-popup-styles-settings-form', [
			'title' => __( 'Popup Settings', Plugin::$config->plugin_text_domain ),
			'tabs'  => [
				'general' => [
					'title'    => __( 'General', Plugin::$config->plugin_text_domain ),
					'sections' => [
						'popup_type'            => [
							'title'  => __( 'Popup Type', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'popup_type' => [
									'type'    => 'select',
									'label'   => __( 'Popup Type', Plugin::$config->plugin_text_domain ),
									'default' => 'modal',
									'options' => [
										'modal'   => __( 'Modal', Plugin::$config->plugin_text_domain ),
										'fly_out' => __( 'Fly out', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'modal'   => [
											'sections' => [ 'modal_style' ],
											'fields'   => [ 'modal_close_icon_position', 'height' ],
										],
										'fly_out' => [
											'sections' => [ 'fly_out_style' ],
										],
									],
								],
							],
						],
						'modal_style'           => [
							'title'  => __( 'Popup Modal Style', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'modal_overlay_background_type'           => [
									'type'    => 'select',
									'label'   => __( 'Overlay Type', Plugin::$config->plugin_text_domain ),
									'options' => [
										'color' => __( 'Color', Plugin::$config->plugin_text_domain ),
										'image' => __( 'Image', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'color' => [
											'fields' => [ 'modal_overlay_background_color' ],
										],
										'image' => [
											'fields' => [
												'modal_overlay_background_image',
												'modal_overlay_background_image_repeat',
												'modal_overlay_background_image_size',
												'modal_overlay_background_image_position',
											],
										],
									],
									'default' => 'color',
								],
								'modal_overlay_background_color'          => [
									'type'       => 'color',
									'label'      => __( 'Popup Overlay Color', Plugin::$config->plugin_text_domain ),
									'show_alpha' => true,
								],
								'modal_overlay_background_image'          => [
									'type'  => 'photo',
									'label' => __( 'Popup Overlay Image', Plugin::$config->plugin_text_domain ),
								],
								'modal_overlay_background_image_repeat'   => [
									'type'    => 'select',
									'label'   => __( 'Popup Overlay Image Repeat', Plugin::$config->plugin_text_domain ),
									'options' => [
										'no_repeat' => __( 'No Repeat', Plugin::$config->plugin_text_domain ),
										'repeat'    => __( 'Repeat', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'no_repeat',
								],
								'modal_overlay_background_image_size'     => [
									'type'    => 'select',
									'label'   => __( 'Popup Overlay Image Size', Plugin::$config->plugin_text_domain ),
									'options' => [
										'cover'   => __( 'Cover', Plugin::$config->plugin_text_domain ),
										'contain' => __( 'Contain', Plugin::$config->plugin_text_domain ),
										'initial' => __( 'Image size', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'cover',
								],
								'modal_overlay_background_image_position' => [
									'type'    => 'select',
									'label'   => __( 'Popup Overlay Image Position', Plugin::$config->plugin_text_domain ),
									'options' => [
										'center' => __( 'Center', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'center',
								],
								'modal_close_on_overlay_click'            => [
									'type'    => 'select',
									'label'   => __( 'Close the popup when you click on the overlay', Plugin::$config->plugin_text_domain ),
									'options' => [
										'yes' => __( 'Yes', Plugin::$config->plugin_text_domain ),
										'no'  => __( 'No', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'yes',
								],
								'modal_disable_close_icon'                => [
									'type'    => 'select',
									'label'   => __( 'Hide Close Icon', Plugin::$config->plugin_text_domain ),
									'options' => [
										'no'  => __( 'No', Plugin::$config->plugin_text_domain ),
										'yes' => __( 'Yes', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'no' => [
											'sections' => [ 'close_icon_style' ],
										],
									],
									'default' => 'no',
								],
								'block_browser_scroll'                    => [
									'type'        => 'select',
									'label'       => __( 'Block browser scroll', 'wpd' ),
									'description' => __( 'Block browser scroll when popup appears', 'wpd' ),
									'options'     => [
										'no'  => __( 'No', 'wpd' ),
										'yes' => __( 'Yes', 'wpd' ),
									],
									'default'     => 'yes',
								],
							],
						],
						'fly_out_style'         => [
							'title'  => __( 'Fly Out Popup Style', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'fly_out_x_position' => [
									'label'   => __( 'Fly Out X Position', Plugin::$config->plugin_text_domain ),
									'type'    => 'select',
									'options' => [
										'right'  => __( 'Right', Plugin::$config->plugin_text_domain ),
										'center' => __( 'Center', Plugin::$config->plugin_text_domain ),
										'left'   => __( 'Left', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'left',
								],
								'fly_out_y_position' => [
									'label'   => __( 'Fly Out Y Position', Plugin::$config->plugin_text_domain ),
									'type'    => 'select',
									'options' => [
										'top'    => __( 'Top', Plugin::$config->plugin_text_domain ),
										'bottom' => __( 'Bottom', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'top',
								],
							],
						],
						'close_icon_style'      => [
							'title'  => __( 'Close Icon Style', Plugin::$config->plugin_text_domain ),
							'fields' => [
//                                'close_icon' => [
//                                    'type' => 'icon',
//                                    'label' => __( 'Icon', Plugin::$config->plugin_text_domain ),
//                                    'default' => 'fa fa-close'
//                                ],
								'close_icon_size'                => [
									'type'        => 'unit',
									'label'       => __( 'Icon Size', Plugin::$config->plugin_text_domain ),
									'description' => 'px',
									'responsive'  => true,
									'default'     => '32',
								],
								'close_icon_color'               => [
									'type'    => 'color',
									'label'   => __( 'Icon Color', Plugin::$config->plugin_text_domain ),
									'default' => '000',
								],
								'modal_close_icon_position'      => [
									'label'   => __( 'Icon Position', Plugin::$config->plugin_text_domain ),
									'type'    => 'select',
									'options' => [
										'overlay' => __( 'Relative to Overlay', Plugin::$config->plugin_text_domain ),
										'box'     => __( 'Relative to Popup', Plugin::$config->plugin_text_domain ),
									],
									'default' => 'overlay',
								],
//                                'close_icon_vertical_position' => [
//                                    'label' => __( 'Vertical Position', Plugin::$config->plugin_text_domain ),
//                                    'type' => 'select',
//                                    'options' => [
//                                        'top' => __( 'Top' ),
//                                        'bottom' => __( 'Bottom' ),
//                                    ],
//                                    'default' => 'top'
//                                ],
								'close_icon_vertical_distance'   => [
									'label'       => __( 'Distance from Top', Plugin::$config->plugin_text_domain ),
									'type'        => 'unit',
									'description' => 'px',
									'default'     => '0',
								],
//                                'close_icon_horizontal_position' => [
//                                    'label' => __( 'Horizontal Position', Plugin::$config->plugin_text_domain ),
//                                    'type' => 'select',
//                                    'options' => [
//                                        'top' => __( 'Left' ),
//                                        'bottom' => __( 'Right' ),
//                                    ],
//                                    'default' => 'top'
//                                ],
								'close_icon_horizontal_distance' => [
									'label'       => __( 'Distance from Right', Plugin::$config->plugin_text_domain ),
									'type'        => 'unit',
									'description' => 'px',
									'default'     => '0',
								],
							],
						],
						'popup_structure'       => [
							'title'  => __( 'Popup Structure', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'width'         => [
									'type'        => 'unit',
									'label'       => __( 'Width', Plugin::$config->plugin_text_domain ),
									'description' => 'px',
									'responsive'  => false,
									'default'     => '600',
								],
								'height'        => [
									'type'        => 'unit',
									'label'       => __( 'Height', Plugin::$config->plugin_text_domain ),
									'description' => 'px (Leave blank for auto)',
									'responsive'  => false,
									'default'     => '',
								],
								'border_radius' => [
									'type'        => 'unit',
									'label'       => __( 'Border Radius', Plugin::$config->plugin_text_domain ),
									'description' => 'px',
									'default'     => '0',
									'deprecated'  => true,
								],
							],
						],
						'popup_box_shadow'      => [
							'deprecated' => true,
							'title'      => __( 'Box Shadow', Plugin::$config->plugin_text_domain ),
							'fields'     => [
								'add_box_shadow'               => [
									'type'    => 'select',
									'label'   => __( 'Add Box Shadow?', Plugin::$config->plugin_text_domain ),
									'options' => [
										'no'  => __( 'No', Plugin::$config->plugin_text_domain ),
										'yes' => __( 'Yes', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'yes' => [
											'fields' => [
												'box_shadow_color',
												'box_shadow_horizontal_length',
												'box_shadow_vertical_length',
												'box_shadow_spread_radius',
												'box_shadow_blur_radius',
												'box_shadow_color_opacity',
											],
										],
									],
								],
								'box_shadow_color'             => [
									'type'       => 'color',
									'label'      => __( 'Box Shadow Color', Plugin::$config->plugin_text_domain ),
									'show_reset' => true,
									'default'    => '000',
								],
								'box_shadow_horizontal_length' => [
									'type'        => 'unit',
									'label'       => __( 'Box Shadow Horizontal Length', Plugin::$config->plugin_text_domain ),
									'description' => __( 'px', Plugin::$config->plugin_text_domain ),
									'default'     => '0',
								],
								'box_shadow_vertical_length'   => [
									'type'        => 'unit',
									'label'       => __( 'Box Shadow Vertical Length', Plugin::$config->plugin_text_domain ),
									'description' => __( 'px', Plugin::$config->plugin_text_domain ),
									'default'     => '0',
								],
								'box_shadow_spread_radius'     => [
									'type'        => 'unit',
									'label'       => __( 'Box Shadow Spread', Plugin::$config->plugin_text_domain ),
									'description' => __( 'px', Plugin::$config->plugin_text_domain ),
									'default'     => '5',
								],
								'box_shadow_blur_radius'       => [
									'type'        => 'unit',
									'label'       => __( 'Box Shadow Blur', Plugin::$config->plugin_text_domain ),
									'description' => __( 'px', Plugin::$config->plugin_text_domain ),
									'default'     => '0',
								],
								'box_shadow_color_opacity'     => [
									'type'    => 'text',
									'label'   => __( 'Box Shadow Opacity', Plugin::$config->plugin_text_domain ),
									'help'    => __( 'Between 0 and 1', Plugin::$config->plugin_text_domain ),
									'default' => '0.5',
								],
							],
						],
						'popup_animations'      => [
							'title'  => __( 'Popup Animations', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'open_animation'            => [
									'type'    => 'select',
									'label'   => __( 'Open Animation', Plugin::$config->plugin_text_domain ),
									'options' => [
										'none'    => __( 'Select Animation', Plugin::$config->plugin_text_domain ),
										'zoomIn'  => __( 'Zoom In', Plugin::$config->plugin_text_domain ),
										'zoomOut' => __( 'Zoom Out', Plugin::$config->plugin_text_domain ),
										'pulse'   => __( 'Pulse', Plugin::$config->plugin_text_domain ),
										'slide'   => __( 'Slide', Plugin::$config->plugin_text_domain ),
										'move'    => __( 'Move', Plugin::$config->plugin_text_domain ),
										'flip'    => __( 'Flip', Plugin::$config->plugin_text_domain ),
										'tada'    => __( 'Tada', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'slide' => [
											'fields' => [ 'open_animation_direction' ],
										],
										'move'  => [
											'fields' => [ 'open_animation_direction' ],
										],
									],
								],
								'open_animation_direction'  => [
									'type'    => 'select',
									'label'   => __( 'Open Animation Direction', Plugin::$config->plugin_text_domain ),
									'options' => [
										'top'    => __( 'To Top', Plugin::$config->plugin_text_domain ),
										'right'  => __( 'To Right', Plugin::$config->plugin_text_domain ),
										'bottom' => __( 'To Bottom', Plugin::$config->plugin_text_domain ),
										'left'   => __( 'To Left', Plugin::$config->plugin_text_domain ),
									],
								],
								'close_animation'           => [
									'type'    => 'select',
									'label'   => __( 'Close Animation', Plugin::$config->plugin_text_domain ),
									'options' => [
										'none'    => __( 'Select Animation', Plugin::$config->plugin_text_domain ),
										'zoomIn'  => __( 'Zoom In', Plugin::$config->plugin_text_domain ),
										'zoomOut' => __( 'Zoom Out', Plugin::$config->plugin_text_domain ),
										'pulse'   => __( 'Pulse', Plugin::$config->plugin_text_domain ),
										'slide'   => __( 'Slide', Plugin::$config->plugin_text_domain ),
										'move'    => __( 'Move', Plugin::$config->plugin_text_domain ),
										'flip'    => __( 'Flip', Plugin::$config->plugin_text_domain ),
										'tada'    => __( 'Tada', Plugin::$config->plugin_text_domain ),
									],
									'toggle'  => [
										'slide' => [
											'fields' => [ 'close_animation_direction' ],
										],
										'move'  => [
											'fields' => [ 'close_animation_direction' ],
										],
									],
								],
								'close_animation_direction' => [
									'type'    => 'select',
									'label'   => __( 'Close Animation Direction', Plugin::$config->plugin_text_domain ),
									'options' => [
										'top'    => __( 'To Top', Plugin::$config->plugin_text_domain ),
										'right'  => __( 'To Right', Plugin::$config->plugin_text_domain ),
										'bottom' => __( 'To Bottom', Plugin::$config->plugin_text_domain ),
										'left'   => __( 'To Left', Plugin::$config->plugin_text_domain ),
									],
								],
							],
						],
						'popup_display_options' => [
							'title'  => __( 'Popup Display Options', Plugin::$config->plugin_text_domain ),
							'fields' => [
								'popup_disable_on_devices' => [
									'type'         => 'select',
									'label'        => __( 'Disable on devices', Plugin::$config->plugin_text_domain ),
									'default'      => 'null',
									'options'      => [
										'mobile'  => __( 'Mobile', Plugin::$config->plugin_text_domain ),
										'tablet'  => __( 'Tablet', Plugin::$config->plugin_text_domain ),
										'desktop' => __( 'Desktop', Plugin::$config->plugin_text_domain ),
									],
									'multi-select' => true,
									'help'         => __( 'Hold CTRL + Click (or CMD + Click for Mac) to de-select screen sizes', Plugin::$config->plugin_text_domain ),
								],
							],
						],
					],
				],
			],
		] );
	}

	/**
	 * Called via AJAX to render styles settings form for popup.
	 *
	 * @since   1.0.0
	 *
	 * @return  array Form settings
	 */
	public static function renderPopupStylesSettingsForm()
	{
		self::storeUserClickEvent();
		$settings = self::getPopupStyleSettings();
		$form     = FLBuilderModel::$settings_forms[ 'wpd-bb-popup-styles-settings-form' ];

		return FLBuilder::render_settings( [
			'class'     => 'wpd-bb-popup-styles-settings-form',
			'title'     => $form[ 'title' ],
			'tabs'      => $form[ 'tabs' ],
			'resizable' => true,
		], $settings );
	}

	/*
	 * Store a value in user meta to say whether a user has clicked
	 * the Popup Options button or not.
	 *
	 * @since 1.0.10
	 *
	 *
	 */
	public static function storeUserClickEvent()
	{
		$current_user_id = get_current_user_id();
		if ( ! get_user_meta( $current_user_id, '_wpd_bb_popups_popup_option_click_count', true ) ) {
			add_user_meta( $current_user_id, '_wpd_bb_popups_popup_option_click_count', 1 );
		}
	}

	/**
	 * Called via AJAX to save the popup styles settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $settings The new layout settings.
	 * @param   string $status   Either published or draft.
	 * @param   int    $post_id  The ID of the post to update.
	 *
	 * @return  object Updated settings
	 */
	static public function savePopupStyleSettings( $settings = [], $status = null, $post_id = null )
	{
		return self::updatePopupStyleSettings( $settings, $status, $post_id );
	}

	/**
	 * Updates popup settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $settings The new popup settings.
	 * @param   string $status   Either published or draft.
	 * @param   int    $post_id  The ID of the popup to update.
	 *
	 * @return  object Settings object
	 */
	public static function updatePopupStyleSettings( $settings = [], $status = null, $post_id = null )
	{
		$status       = ! $status ? FLBuilderModel::get_node_status() : $status;
		$post_id      = ! $post_id ? FLBuilderModel::get_post_id() : $post_id;
		$key          = 'published' == $status ? '_wpd_bb_popup_style_settings' : '_wpd_bb_popup_style_draft_settings';
		$raw_settings = get_metadata( 'post', $post_id, $key );
		$old_settings = self::getPopupStyleSettings( $status, $post_id );
		$new_settings = ( object ) array_merge( ( array ) $old_settings, ( array ) $settings );

		if ( 0 === count( $raw_settings ) ) {
			add_metadata( 'post', $post_id, $key, FLBuilderModel::slash_settings( $new_settings ) );
		}
		else {
			update_metadata( 'post', $post_id, $key, FLBuilderModel::slash_settings( $new_settings ) );
		}

		return $new_settings;
	}

	/**
	 * When a layout is saved, we switch the 'draft'/unpublished settings into
	 * the 'published' settings
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
	public static function saveDraftPopupStyleSettingsIntoPublished( $post_id, $publish, $data, $settings )
	{
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type() ) {
			$popup_settings = self::getPopupStyleSettings( 'draft', $post_id );

			// Delete old popup settings
			self::deletePopupStyleSettings( 'published', $post_id );

			// Save new popup settings
			self::updatePopupStyleSettings( $popup_settings, 'published', $post_id );
		}
	}

	/**
	 * Delete the settings for a popup.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $status  Either published or draft.
	 * @param   int    $post_id The ID of a popup whose settings to delete.
	 *
	 * @return  void
	 */
	public static function deletePopupStyleSettings( $status = null, $post_id = null )
	{
		$status  = ! $status ? FLBuilderModel::get_node_status() : $status;
		$post_id = ! $post_id ? FLBuilderModel::get_post_id() : $post_id;
		$key     = 'published' == $status ? '_wpd_bb_popup_style_settings' : '_wpd_bb_popup_style_draft_settings';

		update_metadata( 'post', $post_id, $key, [] );
	}

	/**
	 * Hook into the fl_builder_render_css filter and merge the custom popup styles into it
	 *
	 * @since   1.0.0
	 * @updated 1.0.2 Refactor for Themer compatibility. Include 4th param of $global
	 *
	 * @param   string $css
	 * @param   array  $nodes
	 * @param   array  $global_settings
	 * @param   bool   $global
	 *
	 * @return  string CSS
	 */
	public static function saveCustomPopupCssIntoStylesheet( $css, $nodes, $global_settings, $global )
	{
		if ( get_post_type() === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
			$popup     = new \stdClass();
			$popup->ID = get_the_ID();
			$settings  = self::getPopupStyleSettings();
			$cpt       = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ];
			$cssFiles  = [
				'Common--Height',
				'Common--Width',
				'Common--BorderRadius',
				'Common--BoxShadow',
				'Common--CloseButton',
				'ModalPopup',
				'FlyoutPopup',
			];

			if ( ! empty( $settings ) ) {
				ob_start();

				foreach ( $cssFiles as $file ) {
					include( Plugin::path( "app/Includes/{$file}.css.php" ) );
				}

				$css .= ob_get_clean();
			}
		}

		return $css;
	}

	/**
	 * Adds popups to the global posts array so that assets are rendered separately
	 *
	 * @since   1.0.4
	 *
	 * @param   array $global_posts
	 *
	 * @return  array
	 */
	public static function setPopupsAsGlobalPosts( $global_posts )
	{
		$activePopupsOnThisPage = PopupHelper::getActivePopupsOnCurrentPage();
		foreach ( $activePopupsOnThisPage as $popup ) {
			$global_posts[] = $popup->id;
		}

		return $global_posts;
	}

	/**
	 * Adds popup to the DOM in wp_footer, if we're not in the builder
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function renderPopupsInDom()
	{
		$activePopupsOnThisPage = PopupHelper::getActivePopupsOnCurrentPage();
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] !== get_post_type() && ! FLBuilderModel::is_builder_active() && 'fl-builder-template' != get_post_type() && count( $activePopupsOnThisPage ) ) {
			/**
			 * Used in template
			 */
			$cpt = Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ];

			/**
			 * To output into DOM, we need a unique array to avoid duplicate popups.
			 * As the array is of objects, we need to just get the ID
			 */
			$popups = [];
			foreach ( $activePopupsOnThisPage as $popup ) {
				$popups[] = $popup->id;
			}

			/**
			 * Loop through
			 */
			foreach ( array_unique( $popups ) as $popupId ) {
				/**
				 * Problem: plugins filtering the_content to add stuff before, to and after the_content
				 * such as share buttons. Provisionally removing content filters.
				 *
				 * Seems to have removed the share buttons without impacting the page that the popup
				 * was triggered on. That is to say, share buttons on the underlying page (not the popup)
				 * still have share buttons, capital_p_dangit still works, but share buttons removed from
				 * the popup
				 */
				remove_all_filters( 'the_content' );

				// Include the template file (therefore passing all variables through
				include( Plugin::path( self::POPUP_TEMPLATE_CONTENT_PART_PATH ) );

				/**
				 * Dequeue this individual popup's layout script, as we'll inject in via popup open callback in JS
				 */
				wp_dequeue_script( 'fl-builder-layout-' . $popupId );
			}
		}
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
		self::setupNonBuilderPagesJs();

		/**
		 * Scripts
		 */
		// Popup Builder only scripts
		if ( FLBuilderModel::is_builder_active() && Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type() ) {

			// Ascertain which script handle to use as a dependency
			$fl_builder_handle = defined( 'WP_DEBUG' ) && WP_DEBUG ? 'fl-builder' : 'fl-builder-min';

			wp_enqueue_script( 'wpd-bb-popups-popup-builder', AssetHelper::getHashedAssetUri( 'js/popupBuilder.js' ), [
				'jquery',
				'jquery-validate',
				$fl_builder_handle,
			], false, true );
		}

		// Page Builder only scripts
		// Only registered - enqueued elsewhere (integrations), when necessary
		// To provide babel support to integration JS when NOT building a popup
		if ( FLBuilderModel::is_builder_active() && Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] !== get_post_type() ) {
			wp_register_script( 'wpd-bb-popups-page-builder', AssetHelper::getHashedAssetUri( 'js/pageBuilder.js' ), [ 'jquery' ] );
		}

		// CPT/preview only assets
		if ( Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] === get_post_type() ) {
			// Stop Beaver Themer from killing the styles!
			self::dequeueBeaverThemerBundleStyles();
		}

		/**
		 * Front end
		 */
		if ( ! FLBuilderModel::is_builder_active() && get_post_type() !== Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] && 'fl-builder-template' !== get_post_type() ) {
			// Front end scripts
			wp_enqueue_script( 'wpd-bb-popups-front-end', AssetHelper::getHashedAssetUri( 'js/frontend.js' ), [
				'jquery',
			], false, true );

		}
		/**
		 * Everywhere
		 */
		// Front end styles
		wp_enqueue_style( 'wpd-bb-popups-front-end', AssetHelper::getHashedAssetUri( 'css/frontend.css' ) );
	}

	/**
	 * Output popup config into JS object in footer
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function outputPopupJsConfigToFooter()
	{
		$wpd_popup_config       = [
			'wpdPopupCpt' => Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ],
			'pageID'      => get_the_ID(),
			'siteUrl'     => Plugin::getSiteUrl(),
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( Plugin::$config->plugin_nonce ),
		];
		$activePopupsOnThisPage = PopupHelper::getActivePopupsOnCurrentPage();

		foreach ( $activePopupsOnThisPage as $popup ) {
			$wpd_popup_config[ 'activePopups' ][] = array_merge( get_object_vars( $popup ), [
				'settings' => self::getPopupStyleSettings( 'published', $popup->id ),
				'script'   => [
					// Full script URL
					'source'  => isset( wp_scripts()->registered[ 'fl-builder-layout-' . $popup->id ]->src ) ? wp_scripts()->registered[ 'fl-builder-layout-' . $popup->id ]->src : null,
					// Cache-buster
					'version' => isset( wp_scripts()->registered[ 'fl-builder-layout-' . $popup->id ]->ver ) ? wp_scripts()->registered[ 'fl-builder-layout-' . $popup->id ]->ver : null,
				],
			] );
		} ?>

        <script>
            /* <![CDATA[ */
            WPDPopupConfig = <?= json_encode( $wpd_popup_config ); ?>;
            /* ]]> */
        </script>

		<?php
	}

	/**
	 * Dequeue Beaver Theme bundle styles
	 *
	 * @since   1.0.4
	 *
	 * @return  void
	 */
	protected static function dequeueBeaverThemerBundleStyles()
	{
		global $wp_styles;
		$beaver_themer_style = null;

		foreach ( $wp_styles->registered as $style ) {
			if ( false !== strpos( $style->handle, 'fl-builder-layout-bundle' ) ) {
				$beaver_themer_style = $style->handle;
			}
		}

		wp_dequeue_style( $beaver_themer_style );
	}

	/**
	 * Enqueue JS if it doesn't exist. This helps as some popups that don't contain
	 * any JS-based modules, but we need global JS enqueued to get access to config
	 * for things like BB breakpoints
	 *
	 * @since 1.9.1
	 */
	protected static function setupNonBuilderPagesJs()
	{
		$id         = get_the_id();
		$asset_info = \FLbuilderModel::get_asset_info();
		$asset_ver  = \FLbuilderModel::get_asset_version();

		if ( ! \FLBuilderModel::is_builder_enabled() ) {
			if ( ! file_exists( $asset_info[ 'js' ] ) ) {
				\FLBuilder::render_js();
			}

			wp_enqueue_script( 'fl-builder-layout-' . $id, $asset_info[ 'js_url' ], null, $asset_ver );
		}
	}
}
