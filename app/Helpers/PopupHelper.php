<?php

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;

/**
 * Class PopupHelper is responsible for operations with popup entities and bindings
 *
 * @package WPD\BeaverPopups\Helpers
 */
class PopupHelper
{

	/**
	 * An array of post IDs of popups active on the current page
	 *
	 * @since 1.0
	 */
	public static $activePopupsOnThisPage = [];

	/**
	 * Site popups setup
	 *
	 * @var object|null
	 */
	protected static $siteSetup = null;

	/**
	 * @since 1.1.o
	 * @var null
	 */
	protected static $postExclusionSetup = [];

	/**
	 * Custom post types setup
	 *
	 * @var null
	 */
	protected static $cptSetup = null;

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
		add_action( 'template_redirect', [ __CLASS__, 'setActivePopupsOnCurrentPage' ] );
		add_action( 'before_delete_post', [ __CLASS__, 'removePopupFromSetup' ], 10, 1 );
		add_action( 'trashed_post', [ __CLASS__, 'removePopupFromSetup' ], 10, 1 );
		add_filter( 'manage_edit-' . Plugin::$config->plugin_slug . '_columns', [
			__CLASS__,
			'removePostTypeFromQuickAndBulkEdit',
		] );
	}

	/**
	 * Get site popups setup
	 *
	 * @param bool $refresh
	 *
	 * @return object
	 */
	public static function getSitePopups( $refresh = false )
	{
		if ( ! self::$siteSetup || $refresh ) {
			$json            = OptionsHelper::get( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_SITE' ], '{}' );
			self::$siteSetup = json_decode( $json, false );

			if ( empty( self::$siteSetup ) ) {
				self::$siteSetup = (object) [];
			}

			if ( ! isset( self::$siteSetup->global ) ) {
				self::$siteSetup->global = (object) [
					'title' => 'Site wide',
					'rules' => (object) [],
				];
			}
		}

		return self::$siteSetup;
	}

	/**
	 * Set site popups setup
	 *
	 * @param $scopeSubject 'site'|'search'|'archive'|'not-found'
	 * @param $popupId
	 * @param $trigger
	 * @param $triggerSetup
	 *
	 * @return mixed
	 */
	public static function setSitePopup( $popupId, $scopeSubject, $trigger, $triggerSetup = [] )
	{
		$setup      = self::getSitePopups();
		$scopeSetup = Util::getItem( $setup, $scopeSubject, (object) [ 'rules' => (object) [] ] );

		if ( $popupId ) {
			if ( ! Util::isPopup( $popupId ) ) {
				return;
			}
			if ( empty( $triggerSetup ) ) {
				$triggerSetup = [];
			}

			$triggerSetup[ 'id' ] = $popupId;
			if ( empty( $scopeSetup->rules ) ) {
				$scopeSetup->rules = (object) [];
			}

			$scopeSetup->rules->$trigger = $triggerSetup;
		}
		else if ( isset( $scopeSetup->rules->$trigger ) ) {
			unset( $scopeSetup->rules->$trigger );
		}

		$setup->$scopeSubject = $scopeSetup;
		$json                 = JsonHelper::encode( $setup, false );
		OptionsHelper::set( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_SITE' ], $json );

		return $scopeSetup;
	}

	/**
	 * Remove popup from site setup, used when popup itself is removed from db
	 *
	 * @param $popupId
	 *
	 * @return object
	 */
	public static function removeSitePopup( $popupId )
	{
		$setup = self::getSitePopups();

		foreach ( $setup as $scopeSubject => $scopeSetup ) {
			foreach ( $scopeSetup->rules as $trigger => $triggerSetup ) {
				$id = Util::getItem( $triggerSetup, 'id' );
				if ( $id == $popupId ) {
					unset( $setup->$scopeSubject->rules->$trigger );
				}
			}
		}

		$json = JsonHelper::encode( $setup, false );
		OptionsHelper::set( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_SITE' ], $json );

		return self::$siteSetup = $setup;
	}

	/**
	 * Get custom post types popups setup
	 *
	 * @param bool $refresh
	 *
	 * @return object
	 */
	public static function getCustomPostTypePopups( $refresh = false )
	{
		if ( ! self::$cptSetup || $refresh ) {
			$json = OptionsHelper::get( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_CPT' ], '{}' );

			self::$cptSetup = json_decode( $json, false );

			if ( empty( self::$cptSetup ) ) {
				self::$cptSetup = (object) [];
			}

			$types = get_post_types( [
				'public' => true,
//                '_builtin' => false,
			], 'objects' );

			foreach ( $types as $type ) {
				$name = $type->name;
				if ( ! isset( self::$cptSetup->$name ) && $name !== 'fl-builder-template' && $name !== 'attachment' ) {
					self::$cptSetup->$name = (object) [
						'title' => $type->label,
						'rules' => (object) [],
					];
				}
			}
		}

		return self::$cptSetup;
	}

	/**
	 * Set custom post types popups setup
	 *
	 * @param $postType 'page'|'post'|'news'|'gallery'
	 * @param $popupId
	 * @param $trigger
	 * @param $triggerSetup
	 *
	 * @return mixed
	 */
	public static function setCustomPostTypePopup( $popupId, $postType, $trigger, $triggerSetup = [] )
	{
		$setup      = self::getCustomPostTypePopups();
		$scopeSetup = Util::getItem( $setup, $postType, (object) [ 'rules' => (object) [] ] );

		if ( $popupId ) {
			if ( ! Util::isPopup( $popupId ) ) {
				return;
			}
			if ( empty( $triggerSetup ) ) {
				$triggerSetup = [];
			}
			$triggerSetup[ 'id' ] = $popupId;
			if ( empty( $scopeSetup->rules ) ) {
				$scopeSetup->rules = (object) [];
			}
			$scopeSetup->rules->$trigger = $triggerSetup;
		}
		else if ( isset( $scopeSetup->rules->$trigger ) ) {
			unset( $scopeSetup->rules->$trigger );
		}

		$setup->$postType = $scopeSetup;
		$json             = JsonHelper::encode( $setup, false );
		OptionsHelper::set( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_CPT' ], $json );

		return $scopeSetup;
	}

	/**
	 * Remove popup from cpt setup, used when popup itself is removed from db
	 *
	 * @param $popupId
	 *
	 * @return object
	 */
	public static function removeCustomPostTypePopup( $popupId )
	{
		$setup = self::getCustomPostTypePopups();

		foreach ( $setup as $scopeSubject => $scopeSetup ) {
			foreach ( $scopeSetup->rules as $trigger => $triggerSetup ) {
				$id = Util::getItem( $triggerSetup, 'id' );
				if ( $id == $popupId ) {
					unset( $setup->$scopeSubject->rules->$trigger );
				}
			}
		}

		$json = JsonHelper::encode( $setup, false );
		OptionsHelper::set( Plugin::$config->wp_options[ 'public' ][ 'OPTION_POPUPS_CPT' ], $json );

		return self::$cptSetup = $setup;
	}

	/**
	 * @param      $post
	 * @param bool $exclude
	 *
	 * @return array
	 */
	public static function packIndividualPost( $post, $exclude = false )
	{
		$packedPost = [
			'id'    => $post->ID,
			'name'  => $post->post_name,
			'title' => $post->post_title,
			'type'  => $post->post_type,
		];
		if ( ! $exclude ) {
			$packedPost[ 'rules' ] = self::getIndividualPostPopups( $post->ID );
		}

		return $packedPost;
	}

	/**
	 * Select all the posts with individual setup
	 *
	 * @return array
	 */
	public static function getIndividualPostsPopups()
	{
		$query = new \WP_Query( [
			'post_type'      => 'any',
			'posts_per_page' => - 1,
			'meta_key'       => Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ],
			'meta_value'     => '',
			'meta_compare'   => '>',
		] );

		$posts = $query->get_posts();

		$data = array_map( function ( $post ) {
			return self::packIndividualPost( $post );
		}, $posts );

		$ids = array_column( $data, 'id' );

		return array_combine( $ids, $data );
	}

	/**
	 * Get post popups setup
	 *
	 * @param int|string|\WP_Post $post
	 *
	 * @return object
	 */
	public static function getIndividualPostPopups( $post )
	{
		$postId = 0;

		if ( is_numeric( $post ) ) {
			$postId = $post;
		}
		else if ( is_string( $post ) ) {
			$query  = new \WP_Query( [
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'name'           => $post,
			] );
			$posts  = $query->get_posts();
			$post   = reset( $posts );
			$postId = $post->ID;
		}
		else if ( is_object( $post ) && $post instanceof \WP_Post ) {
			$postId = $post->ID;
		}
		$json  = get_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ], true );
		$setup = $json ? json_decode( $json ) : null;

		return $setup;
	}

	/**
	 * Set individual post popups setup
	 *
	 * @param $popupId
	 * @param $postId
	 * @param $trigger
	 * @param $triggerSetup
	 *
	 * @return mixed
	 */
	public static function setIndividualPostPopup( $popupId, $postId, $trigger, $triggerSetup = [] )
	{
		$scopeSetup = self::getIndividualPostPopups( $postId );

		if ( $popupId ) {
			if ( ! Util::isPopup( $popupId ) ) {
				return;
			}
			if ( empty( $triggerSetup ) ) {
				$triggerSetup = [];
			}
			$triggerSetup[ 'id' ] = $popupId;
			if ( empty( $scopeSetup ) ) {
				$scopeSetup = (object) [];
			}
			$scopeSetup->$trigger = $triggerSetup;
		}
		else if ( isset( $scopeSetup->$trigger ) ) {
			unset( $scopeSetup->$trigger );
		}
		if ( ! empty( $scopeSetup ) ) {
			$json = JsonHelper::encode( $scopeSetup, false );
			update_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ], $json );
		}
		else {
			delete_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ] );
		}

		$post = get_post( $postId );

		return self::packIndividualPost( $post );
	}

	/**
	 * Set individual post exclusion popups
	 *
	 * @param $popupExclusions
	 *
	 * @return mixed
	 */
	public static function setIndividualPostsExclusionPopup( $popupExclusions )
	{
		$setup = [];

		if ( $popupExclusions ) {
			foreach ( $popupExclusions as $popupExclusion ) {
				if ( ! ( is_single( $popupExclusion ) || ! is_page( $popupExclusion ) ) ) {
					return;
				}
				$post = get_post( $popupExclusion );

				$setup[] = self::packIndividualPost( $post, true );
			}
		}
		$json = JsonHelper::encode( $setup, false );
		OptionsHelper::set( Plugin::$config->wp_options[ 'public' ][ 'POST_EXCLUSION_META_POPUPS' ], $json );

		return $setup;
	}


	/**
	 * Get site popups setup
	 *
	 * @since 1.1.1
	 *
	 *
	 * @return object
	 */
	public static function getIndividualPostExclusionSetup()
	{
		if ( ! self::$postExclusionSetup ) {
			$json                     = OptionsHelper::get( Plugin::$config->wp_options[ 'public' ][ 'POST_EXCLUSION_META_POPUPS' ], '{}' );
			self::$postExclusionSetup = json_decode( $json, true );

			if ( empty( self::$postExclusionSetup ) ) {
				self::$postExclusionSetup = [];
			}
		}

		return self::$postExclusionSetup;
	}

	/**
	 * Remove popup from individual posts setup, used when popup itself is removed from db
	 *
	 * @param $popupId
	 *
	 * @return object
	 */
	public static function removeIndividualPostsPopup( $popupId )
	{
		$setup = self::getIndividualPostsPopups();

		foreach ( $setup as $postId => $postSetup ) {
			foreach ( $postSetup[ 'rules' ] as $trigger => $triggerSetup ) {
				$id = Util::getItem( $triggerSetup, 'id' );
				if ( $id == $popupId ) {
					unset( $postSetup[ 'rules' ]->$trigger );
					$json = JsonHelper::encode( $postSetup[ 'rules' ], false );
					update_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ], $json );
				}
			}
		}

		return $setup;
	}

	/**
	 * Remove popup setup
	 *
	 * @param $popupId
	 */
	public static function removePopupSetup( $popupId )
	{
		self::removeSitePopup( $popupId );
		self::removeCustomPostTypePopup( $popupId );
		self::removeIndividualPostsPopup( $popupId );
	}

	/**
	 * On post (popup remove)
	 *
	 * @param $postId
	 */
	public static function removePopupFromSetup( $postId )
	{
		$post = get_post( $postId );

		if ( $post->post_type === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
			self::removePopupSetup( $postId );
		}
	}

	/**
	 * Add empty popup setup to post, so that it will be requested by list query
	 *
	 * @param $postId
	 */
	public static function addIndividualPostSetup( $postId )
	{
		update_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ], '{}' );
	}

	/**
	 * Remove individual post setup
	 *
	 * @param $postId
	 */
	public static function removeIndividualPostSetup( $postId )
	{
		delete_post_meta( $postId, Plugin::$config->wp_options[ 'public' ][ 'POST_META_POPUPS' ] );
	}

	/**
	 * Calculate which popups are active on this page
	 *
	 * @since 1.0
	 */
	public static function setActivePopupsOnCurrentPage()
	{
		/**
		 * Bail early if on a single popup page
		 */
		if ( get_post_type() === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
			self::$activePopupsOnThisPage = [];
		}

		/**
		 * Exclude Page for any popups
		 *
		 * @since 1.1.1
		 */
		if ( self::isPopupExcludedForPost() ) {
			self::$activePopupsOnThisPage = [];

			return;
		}

		/**
		 * If the admin has toggled popups off, then return nothing
		 *
		 * @todo this is just a placeholder and doesn't do anything yet
		 */
		if ( current_user_can( 'edit_others_pages' ) && OptionsHelper::get( Plugin::$config->wp_options[ 'public' ][ 'DISABLE_POPUPS_SITEWIDE_FOR_ADMINS_KEY' ], false ) ) {
			self::$activePopupsOnThisPage = [];
		}

		$post = null;

		/**
		 * Our future popup setup for all events
		 */
		$popups = [
			'entrance' => null,
			'exit'     => null,
			'scroll'   => null,
		];

		/**
		 * Our scope setup
		 */
		$setup = [
			'site' => null,
			'cpt'  => null,
			'post' => null,
		];

		if ( is_single() || is_page() ) {
			/**
			 * If we are on the single post entry get individual setup
			 */
			$post = get_post();

			$postSetup = self::getIndividualPostPopups( $post );

			if ( $postSetup ) {
				$setup[ 'post' ] = $postSetup;
			}
		}
		else if ( is_archive() ) {
			/**
			 * If we are on archive page simply fetch one post to get post_type
			 */
			$post = get_post();
		}

		/**
		 * We have a post and can fetch post_type popup bindings
		 */
		if ( $post ) {
			$cptSetup = Util::getItem( self::getCustomPostTypePopups(), $post->post_type, null );

			if ( $cptSetup ) {
				$setup[ 'cpt' ] = $cptSetup->rules;
			}
		}

		/**
		 * Fetch global popup bindings
		 */
		$setup[ 'site' ] = Util::getItem( self::getSitePopups(), 'global' )->rules;

		/**
		 * Loop through all scopes beginning from the lowest priority and set trigger setups.
		 * In the end we'll have $popups split by trigger events with
		 * highest prioritized popup setup available for each trigger event
		 */
		foreach ( $setup as $scope => $scopeSetup ) {
			$onEntrance = Util::getItem( $scopeSetup, 'entrance' );

			if ( $onEntrance ) {
				$popups[ 'entrance' ]        = $onEntrance;
				$popups[ 'entrance' ]->scope = $scope;
			}

			$onExit = Util::getItem( $scopeSetup, 'exit' );

			if ( $onExit ) {
				$popups[ 'exit' ]        = $onExit;
				$popups[ 'exit' ]->scope = $scope;
			}

			$onScroll = Util::getItem( $scopeSetup, 'scroll' );

			if ( $onScroll ) {
				$popups[ 'scroll' ]        = $onScroll;
				$popups[ 'scroll' ]->scope = $scope;
			}
		}

		/**
		 * Rearrange $popups by popup id
		 */
		foreach ( $popups as $trigger => $triggerSetup ) {
			if ( $triggerSetup ) {
				$triggerSetup->trigger = $trigger;
				if ( Util::isPopup( $triggerSetup->id ) ) {
					self::$activePopupsOnThisPage[] = $triggerSetup;
				}
			}
		}

		/**
		 * Include button popups
		 */
		if ( is_single() || is_page() ) {
			$post_id = get_the_ID();

			$click_triggered_popups = (array) get_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], true );

			/**
			 * Account for themer layouts. If a button is added inside a theme layout
			 * then the popup IDs will be saved to the theme layout's meta, and not the
			 * single item
			 */
			if ( class_exists( '\FLThemeBuilder' ) ) {
				if ( \FLThemeBuilder::has_layout() ) {
					$global_saved_button_modules = [];
					foreach ( \FLThemeBuilderLayoutData::get_current_page_layout_ids() as $page_layout_id ) {
						$themer_click_triggered_popups = (array) get_metadata( 'post', $page_layout_id, Plugin::$config->wp_options[ 'public' ][ 'CLICK_TRIGGER_POPUPS' ], true );
						$click_triggered_popups        = array_merge( $click_triggered_popups, $themer_click_triggered_popups, $global_saved_button_modules );
					}
				}
			}

			foreach ( $click_triggered_popups as $click_triggered_popup ) {
				$popup                          = new \stdClass();
				$popup->id                      = (int) $click_triggered_popup;
				self::$activePopupsOnThisPage[] = $popup;

			}

			/*
			 * Include Scroll Triggered Popups
			 */
			$scroll_triggered_popups = (array) get_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'SCROLL_TRIGGER_POPUPS' ], true );

			foreach ( $scroll_triggered_popups as $scroll_triggered_popup ) {
				$popup = new \stdClass();
				if ( get_post_type( $scroll_triggered_popup ) === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {

					$popup->id                      = (int) $scroll_triggered_popup;
					self::$activePopupsOnThisPage[] = $popup;
				}
			}

			/**
			 * Include opening modal from link shortcode
			 */
			$post_content_click_triggered_popups = (array) get_metadata( 'post', $post_id, Plugin::$config->wp_options[ 'public' ][ 'SHORTCODE_ADDED_POPUPS_KEY' ], true );

			if ( ! empty( $post_content_click_triggered_popups ) ) {
				foreach ( $post_content_click_triggered_popups as $post_content_click_triggered_popup ) {
					$post_content_popup = new \stdClass();

					if ( ! empty( $post_content_click_triggered_popup ) && is_array( $post_content_click_triggered_popup ) ) {
						foreach ( $post_content_click_triggered_popup as $post_click_triggered_popup ) {
							if ( get_post_type( $post_click_triggered_popup ) === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
								$post_content_popup->id         = (int) $post_click_triggered_popup;
								self::$activePopupsOnThisPage[] = $post_content_popup;
							}
						}
					}
					else {
						if ( get_post_type( $post_content_click_triggered_popup ) === Plugin::$config->wp_options[ 'public' ][ 'CUSTOM_POST_TYPE_POPUP' ] ) {
							$post_content_popup->id         = (int) $post_content_click_triggered_popup;
							self::$activePopupsOnThisPage[] = $post_content_popup;
						}
					}
				}
			}
		}
	}

	/**
	 * Return array of active popups in the current page
	 *
	 * @since   1.0.11
	 * @return array
	 */
	public static function getActivePopupsOnCurrentPage()
	{
		return apply_filters( 'wpd_beaver_popups/popups_on_page', self::$activePopupsOnThisPage );
	}

	/**
	 * Remove Post Type Select Box from Quick Edit in the BB Popups List Screen
	 *
	 * @since 1.0.9
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function removePostTypeFromQuickAndBulkEdit( $columns )
	{
		// Remove Post Type Columns
		if ( isset( $columns[ 'post_type' ] ) ) {
			unset( $columns[ 'post_type' ] );
		}

		return $columns;
	}

	/**
	 * Check if the popup is disabled for the page/post or not
	 *
	 * @since 1.1.1
	 *
	 * @param $postId
	 *
	 * @return bool
	 */
	public static function isPopupExcludedForPost( $postId = false )
	{
		if ( ! $postId ) {
			$postId = get_the_ID();
		}
		$popupExcludesPages = self::getIndividualPostExclusionSetup();
		if ( ( is_single( $postId ) || is_page( $postId ) ) && ! empty( $popupExcludesPages ) ) {
			$isExcluded = array_filter( $popupExcludesPages,
				function ( $popupExcludesPage ) use ( $postId ) {
					return $popupExcludesPage->id === $postId;
				}
			);

			return $isExcluded;
		}

		return false;
	}
}
