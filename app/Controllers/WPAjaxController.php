<?php

/**
 * AJAX controller
 *
 * @package     WPD\BeaverPopups\Controllers
 * @since       1.0.0
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Controllers;

use WPD\BeaverPopups\Plugin;

/**
 * Class WPAjaxController
 *
 * @package WPD\BeaverPopups\Controllers
 */
class WPAjaxController {

	/**
	 * Meta Key
	 */
	const WPD_POPUP_IMPRESSION_COUNT_META_KEY = '_wpd_bb_popups_impression_count';

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
	 * Register Hooks
	 */
	public function registerHooks()
    {
        add_action( 'wp_ajax_nopriv_wpd_bb_popups_count_popup_impression', [ __CLASS__ , 'countPopupImpressionAjaxHandler' ] );
    }

	/**
	 * Count Popup Impression
	 */
	public function countPopupImpressionAjaxHandler()
    {
        if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], Plugin::$config->plugin_nonce ) ) {
            exit( 'Are you kidding?' );
        }

        $popupId = '';

        if ( $popupId = filter_var( $_REQUEST[ 'popupId' ], FILTER_SANITIZE_NUMBER_INT ) ) {
            $currentImpressionCount   = get_post_meta( $popupId, self::WPD_POPUP_IMPRESSION_COUNT_META_KEY, true );
            $currentImpressionCount   = $currentImpressionCount ? (int) $currentImpressionCount : 0;

            if ( 0 === $currentImpressionCount ) {
                $currentImpressionCount = 1;
                add_post_meta( $popupId, self::WPD_POPUP_IMPRESSION_COUNT_META_KEY, $currentImpressionCount );
            }
            else {
                $currentImpressionCount++;
                update_post_meta( $popupId, self::WPD_POPUP_IMPRESSION_COUNT_META_KEY, $currentImpressionCount );
            }
        }

        wp_die();
    }
}
