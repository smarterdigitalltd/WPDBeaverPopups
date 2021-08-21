<?php

namespace WPD\BeaverPopups\Features;

use WPD\BeaverPopups\Plugin;

/**
 * Class PopupFromLink
 *
 * @package WPD\BeaverPopups\Features
 * @since 1.1.1
 */
class PopupFromLink {

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
	 * PopupFromLink constructor.
	 */
	protected function __construct()
	{
		$this->registerHooks();
	}

	/**
	 * Register filters and actions hooks
	 */
	protected function registerHooks()
	{
		add_action( 'save_post', [ __CLASS__, 'maybeSetMetaIfShortcodePopupFound' ], 10, 1 );
		add_action( 'save_post', [ __CLASS__, 'deletePopupsForState' ], 10, 1 );
		add_shortcode( Plugin::$config->wp_options[ 'public' ]['SHORTCODE_ADDED_POPUPS'], [ __CLASS__, 'registerPopupLinkShortcodes' ] );
	}

	/**
	 * Register beaver_popup_link shortcode
	 * @since 1.0.10
	 * @param array $atts
	 * @param null  $content
	 *
	 * @return null
	 */
	public static function registerPopupLinkShortcodes( array $atts = [], $content = null ){
		extract( shortcode_atts( array(
			'id'    => null,
			'title' => null,
		), $atts ) );

		if( $id ){
			ob_start(); ?>
			<a href="javascript:void(0);" target="_self"<?= ! empty( $title ) ? " title=$title" : ''; ?> class="wpd-bb-popup__link--enabled" role="button" data-wpd-bb-popup-id="<?= esc_attr( $id ); ?>"><?= $content; ?></a>
			<?php
			$output = ob_get_clean();
			return $output;
		}
	}

	/**
	 * Check if the post has beaver_popup_link shortcode within its content
	 *
	 * @param $postId
	 *
	 * @since 1.0.10
	 */
	public static function maybeSetMetaIfShortcodePopupFound( $postId )
	{
		$post = get_post( $postId );

		if ( $post->post_type === Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] ) {
			return;
		}

		$wpd_shortcode = Plugin::$config->wp_options[ 'public' ]['SHORTCODE_ADDED_POPUPS'];

		$pattern = get_shortcode_regex();

		$popup_ids = [];

		if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches )
		     && array_key_exists( 2, $matches )
		     && in_array( $wpd_shortcode, $matches[ 2 ] ) ) {
			$keys   = [];
			$result = [];
			foreach ( $matches[ 0 ] as $key => $value ) {
				if ( strpos( $value, $wpd_shortcode ) !== false ) {
					$get = str_replace( " ", "&", $matches[ 3 ][ $key ] );
					parse_str( $get, $output );
					$keys     = array_unique( array_merge( $keys, array_keys( $output ) ) );
					$result[] = $output;
				}
			}

			if ( $keys && $result ) {
				foreach ( $result as $key => $value ) {
					foreach ( $keys as $attr_key ) {
						if ( $attr_key === 'id' ) {
							$popup_id = str_replace( '"', '', $result[ $key ][ $attr_key ] );
							if ( $popup_id ) {
								$popup_ids[] = (int) $popup_id;
							}
						}
					}
				}
				if ( ! empty( $popup_ids ) ) {
					$popup_ids = array_unique( $popup_ids );
				}
			}
		}
		/**
		 * Update meta to post if the popup links exists
		 */
		update_post_meta( $postId, Plugin::$config->wp_options[ 'public' ]['SHORTCODE_ADDED_POPUPS_KEY'], $popup_ids );
	}

	/**
	 * Delete PopupsForState transient when Popups are saved
	 *
	 * @param $postId
	 *
	 * @since 1.0.10
	 */
	public static function deletePopupsForState( $postId ) {
		$post = get_post( $postId );

		if ( $post->post_type !== Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] ) {
			return;
		}

		delete_transient( Plugin::$config->transient_prefix . 'PopupsForState' );
	}
}
