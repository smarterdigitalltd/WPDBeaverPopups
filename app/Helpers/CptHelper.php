<?php

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;

/**
 * Class CptHelper is responsible for creating the CPT
 *
 * @package WPD\BeaverPopups\Helpers
 */
class CptHelper {

	/**
	 * The path to the template that is used to remove all page elements from
	 * the page builder when editing a popup (no header, footer or sidebar)
	 *
	 * @since 1.0
	 */
	const POPUP_CPT_TEMPLATE_PATH = 'app/Templates/PopupCptTemplate.php';

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
	 */
	public function registerHooks()
	{
		add_action( 'init', [ __CLASS__, 'registerCustomPostType' ] );
		add_action( 'admin_menu', [ __CLASS__, 'removePageAttributesMetaBoxOnPopupCpt' ] );
		add_filter( 'template_include', [ __CLASS__, 'setTemplateForPopupCpt' ], 110, 1 );
	}

	/**
	 * Register custom post type 'wpd-bb-popup'
	 *
	 */
	public static function registerCustomPostType()
	{
		$labels = [
			'name'                  => _x( 'Beaver Popups', 'Post Type General Name', Plugin::$config->plugin_text_domain ),
			'singular_name'         => _x( 'Popup', 'Post Type Singular Name', Plugin::$config->plugin_text_domain ),
			'menu_name'             => __( 'Beaver Popups', Plugin::$config->plugin_text_domain ),
			'name_admin_bar'        => __( 'Beaver Popup', Plugin::$config->plugin_text_domain ),
			'archives'              => __( 'Popup Archives', Plugin::$config->plugin_text_domain ),
			'parent_item_colon'     => __( 'Parent Popup:', Plugin::$config->plugin_text_domain ),
			'all_items'             => __( 'All Popups', Plugin::$config->plugin_text_domain ),
			'add_new_item'          => __( 'Add New Popup', Plugin::$config->plugin_text_domain ),
			'add_new'               => __( 'Add New', Plugin::$config->plugin_text_domain ),
			'new_item'              => __( 'New Popup', Plugin::$config->plugin_text_domain ),
			'edit_item'             => __( 'Edit Popup', Plugin::$config->plugin_text_domain ),
			'update_item'           => __( 'Update Popup', Plugin::$config->plugin_text_domain ),
			'view_item'             => __( 'View Popup', Plugin::$config->plugin_text_domain ),
			'search_items'          => __( 'Search Popup', Plugin::$config->plugin_text_domain ),
			'not_found'             => __( 'Not found', Plugin::$config->plugin_text_domain ),
			'not_found_in_trash'    => __( 'Not found in Trash', Plugin::$config->plugin_text_domain ),
			'featured_image'        => __( 'Featured Image', Plugin::$config->plugin_text_domain ),
			'set_featured_image'    => __( 'Set featured image', Plugin::$config->plugin_text_domain ),
			'remove_featured_image' => __( 'Remove featured image', Plugin::$config->plugin_text_domain ),
			'use_featured_image'    => __( 'Use as featured image', Plugin::$config->plugin_text_domain ),
			'insert_into_item'      => __( 'Insert into popup', Plugin::$config->plugin_text_domain ),
			'uploaded_to_this_item' => __( 'Uploaded to this popup', Plugin::$config->plugin_text_domain ),
			'items_list'            => __( 'Popups list', Plugin::$config->plugin_text_domain ),
			'items_list_navigation' => __( 'Popups list navigation', Plugin::$config->plugin_text_domain ),
			'filter_items_list'     => __( 'Filter popups list', Plugin::$config->plugin_text_domain ),
		];

		$args = [
			'label'               => __( 'Popup', Plugin::$config->plugin_text_domain ),
			'description'         => __( 'Beaver Popups', Plugin::$config->plugin_text_domain ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'page-attributes' ],
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => Plugin::$config->plugin_menu_icon,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite' => [
				'with_front'   => true,
				'rewrite_base' => Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP']
			],
			'capability_type'    => 'page',
		];

		register_post_type( Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'], $args );
	}

	/**
	 * Forces the wpd-bb-popup CPT to use a specific template that removes
	 * all unnecessary page elements, such as page header, footer & sidebar
	 *
	 * @since 1.0
	 *
	 * @param $single
	 *
	 * @return string Template path
	 */
	public static function setTemplateForPopupCpt( $single )
	{
		global $post;
		$path = Plugin::path( self::POPUP_CPT_TEMPLATE_PATH );

		if ( $post && $post->post_type === Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] ) {
			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		return $single;
	}

	/**
	 * Removes unnecessary meta boxes from Popup CPT
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function removePageAttributesMetaBoxOnPopupCpt()
	{
		remove_meta_box( 'pageparentdiv', Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'], 'side' );
	}
}
