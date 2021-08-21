<?php

/**
 * BB Subscribe Form Integration
 *
 * @package     WPD\BeaverPopups\Integrations
 * @since       1.0.4
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Integrations;

use WPD\BeaverPopups\Plugin;

/**
 * Class BBSubscribeFormIntegration
 *
 * @package WPD\BeaverPopups\Integrations
 */
class BBSubscribeFormIntegration
{

	/**
	 *  Meta Key
	 */
	const WPD_BB_SUBSCRIBE_FORM_SUBSCRIBER_COUNT_META_KEY = '_wpd_bb_popups_subscribe_form_subscriber_count';

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
     * @since   1.0.4
     *
     * @return  void
     */
    public function registerHooks()
    {
        add_action('fl_builder_subscribe_form_submission_complete', [ __CLASS__, 'setPopupSubscriptionCount' ], 10, 6);
    }

    /**
     * Adds subscribe form data to popup meta for reporting
     *
     * @since   1.0.4
     *
     * @param   array $response Response from autoresponder service
     * @param   object $settings Module settings
     * @param   string $email Email address of subscriber
     * @param   string $name Name of subscriber
     * @param   string $template_id Template ID, if applicable
     * @param   int $post_id Post ID of the post containing the subscribe module
     *
     * @return  int
     */
    public static function setPopupSubscriptionCount($response, $settings, $email, $name, $template_id, $post_id)
    {
        if (Plugin::$config->wp_options[ 'public' ]['CUSTOM_POST_TYPE_POPUP'] !== get_post_type($post_id)) {
            return;
        }

        $currentSubscriberCount   = get_post_meta($post_id, self::WPD_BB_SUBSCRIBE_FORM_SUBSCRIBER_COUNT_META_KEY, true);
        $currentSubscriberCount   = $currentSubscriberCount ? (int) $currentSubscriberCount : 0;

        if (0 === $currentSubscriberCount) {
            $currentSubscriberCount = 1;
            add_post_meta($post_id, self::WPD_BB_SUBSCRIBE_FORM_SUBSCRIBER_COUNT_META_KEY, $currentSubscriberCount);
        } else {
            $currentSubscriberCount++;
            update_post_meta($post_id, self::WPD_BB_SUBSCRIBE_FORM_SUBSCRIBER_COUNT_META_KEY, $currentSubscriberCount);
        }

        return $currentSubscriberCount;
    }
}
