<?php

/**
 * Render main dashboard page
 *
 * @package     WPD\BeaverPopups\Admin
 * @since       1.0.0
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Admin;

use WPD\BeaverPopups\Plugin;
use WPD\BeaverPopups\Helpers\AssetHelper;

/**
 * Class PopupAdminShortCodeGenerator is responsible for rendering of ShortCode Generator WP Dashboard page
 *
 * @package WPD\BeaverPopups\Admin
 */
class PopupAdminShortCodeGenerator extends AbstractAdminSubPage
{
	/**
	 * Page slug
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $parentSlug = 'edit.php?post_type=wpd-bb-popup';

	/**
	 * Admin page slug that is a part of it's url
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $pageSlug = 'beaver-popups-shortcode-generator';

	/**
	 * Admin page title
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $pageTitle = 'Beaver Popups Shortcode Generator';

	/**
	 * Admin page menu title
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $menuTitle = 'Shortcode Generator';

	/**
	 * Admin page menu icon url or dashicons alias
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $menuIcon = 'dashicons-forms';

	/**
	 * Admin page menu position
	 *
	 * @since   1.0.0
	 *
	 * @var     int
	 */
	protected $menuPosition = 50;

	/**
	 * User capability required to access page
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $requiredUserCapability = 'manage_options';

	/**
	 * Render page
	 *
	 * @since   1.0.0
	 *
	 * @param   array $options Array of options to display
	 * @param   string $message Message to display
	 * @param   bool $isError Whether the message is an error or not
	 *
	 * @return  void
	 */
	protected function renderPage( $options, $message, $isError )
	{
		wp_enqueue_script( 'beaver-popups-admin', AssetHelper::getHashedAssetUri( 'js/admin.js' ) );
		wp_enqueue_style( 'beaver-popups-admin', AssetHelper::getHashedAssetUri( 'css/admin.css' ) );
		?>
		<script>
            window.WPD = {
                siteUrl: '<?php echo Plugin::getSiteUrl(); ?>'
            };
		</script>

		<div id="beaver-popups-shortcode-generator-app"></div>
		<?php
	}
}
