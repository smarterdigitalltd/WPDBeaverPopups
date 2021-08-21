<?php

/**
 * Plugin bootstrap file
 *
 * @package     WPD\BeaverPopups\Plugin
 * @since       1.0.0
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups;

use WPD\BeaverPopups\Helpers\AssetHelper;
use WPD\BeaverPopups\Helpers\BeaverBuilderHelper;
use WPD\BeaverPopups\Helpers\CptHelper;
use WPD\BeaverPopups\Helpers\PopupHelper;
use WPD\BeaverPopups\Integrations\BBSubscribeFormIntegration;
use WPD\BeaverPopups\Integrations\PowerpackIntegration;
use WPD\BeaverPopups\Integrations\UABBIntegration;
use WPD\BeaverPopups\Integrations\WPDBBAdditionsIntegration;
use WPD\BeaverPopups\Controllers\WPAjaxController;
use WPD\BeaverPopups\Helpers\RequirementsHelper;
use WPD\BeaverPopups\Features\PopupFromLink;
use WPD\BeaverPopups\Features\ScrollTrigger;
use WPD\BeaverPopups\Features\HidePopupFromButton;

class Plugin {

	/**
	 * Plugin config
	 *
	 * @since   1.0.10
	 */
	public static $config;
    /**
     * Plugin version
     *
     * @since   1.0.0
     */
    const VERSION = '{{ PLUGIN_VERSION }}';

    /**
     * Plugin name extracted from the path
     *
     * @since   1.0.0
     *
     * @var     string
     */
    protected static $name = '';

    /**
     * Base plugin root dir
     *
     * @since   1.0.0
     *
     * @var     string
     */
    protected static $rootDir = '';

    /**
     * Main plugin file
     *
     * @since   1.0.0
     *
     * @var     string
     */
    protected static $rootFile = '';

    /**
     * Singleton instance
     *
     * @since   1.0.0
     *
     * @var     self null
     */
    protected static $instance = null;

	/**
	 * Plugin constructor.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $rootFile The entry point file
	 */
    public function __construct( $rootFile )
    {
        self::$rootFile = $rootFile;
        self::$rootDir = realpath( dirname( $rootFile ) );
        self::$name = plugin_basename( $rootFile );

        $this->setConfig();
        $this->setupTextDomain();

        if ( RequirementsHelper::isCompatible() ) {
            $this->setup();
            $this->adminStyles();

            return true;
        }

        return false;
    }

    /**
     * Get singleton instance
     *
     * @param   string $rootFile The entry point file
     *
     * @return  bool|Plugin Instance of the plugin
     */
    public static function getInstance( $rootFile = '' )
    {
        if ( ! self::$instance ) {
            self::$instance = new self( $rootFile );
        }

        return self::$instance;
    }

    /**
     * Get main plugin filename
     *
     * @since   1.0.0
     *
     * @return  string The filename
     */
    public static function filename()
    {
        return self::$rootFile;
    }

    /**
     * Returns root dir path
     *
     * @since   1.0.0
     *
     * @param   string $relPath Directory path to item, assuming the
     *                          root is the current plugin dir
     *
     * @return  string The complete directory path
     */
    public static function path( $relPath = '' )
    {
        return self::$rootDir . '/' . $relPath;
    }

    /**
     * Returns root dir url
     *
     * @since   1.0.0
     *
     * @param   string $relPath Directory path to item, assuming the
     *                          root is the current plugin dir
     *
     * @return  string The URL to the item
     */
    public static function url( $relPath = '' )
    {
        return plugins_url( $relPath, dirname( __FILE__ ) );
    }

    /**
     * Returns root dir of dist directory
     *
     * @since   1.0.0
     *
     * @param   string $path Directory path to item
     *
     * @return  string The path to a dist item
     */
    public static function assetDistDir( $path = null )
    {
        return self::path( 'res/dist/' . $path );
    }

    /**
     * Returns root dir of dist directory URL
     *
     * @since   1.0.0
     *
     * @param   string $path Directory path to item
     *
     * @return  string The URL to the dist path item
     */
    public static function assetDistUri( $path = null )
    {
        return self::url( 'res/dist/' . $path );
    }

    /**
     * Returns the base URL of the site
     *
     * @return string
     */
    public static function getSiteUrl()
    {
        return get_site_url();
    }

    /**
     * Returns the base URL of the API
     *
     * @return string
     */
    public static function getApiBaseUrl()
    {
        return self::getSiteUrl() . '/api/wpd/beaver-popups';
    }

	/**
	 * Set plugin configuration
	 *
	 * @since 1.0.10
	 *
	 * @return void
	 */
	private function setConfig()
	{
		self::$config = self::getConfig();
	}

	/**
	 * Get plugin configuration
	 *
	 * @since 1.0.10
	 *
	 * @return object
	 */
	public static function getConfig()
	{
		$config = include self::$rootDir . '/config/config.php';
		return $config;
	}

    /**
     * Add admin styles
     *
     * @since   1.0.5
     *
     * @return  void
     */
    public function adminStyles()
    {
        add_action( 'admin_menu', function() {
            // Get the logo icon aligned correctly
            wp_add_inline_style('wp-admin', '#menu-posts-wpd-bb-popup img { padding-top: 7px !important; max-width: 20px; }');
        } );
    }

	/**
	 * Setup text domain
	 *
	 * @since 1.0.10
	 */
    public function setupTextDomain()
    {
	    load_plugin_textdomain( Plugin::$config->plugin_text_domain, false, self::$rootDir . '/languages' );
    }

    /**
     * Setup
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function setup()
    {
		$this->registerComponents();
	    new Admin\PopupsAdminPage();
	    new Admin\PopupAdminShortCodeGenerator();
	    new Controllers\PopupsController();
    }

	/**
	 * Register filters and actions hooks
	 *
	 * @since   1.0.10
	 *
	 * @return  void
	 */
    private function registerComponents()
    {
	    AssetHelper::getInstance();
	    CptHelper::getInstance();
	    PopupHelper::getInstance();
	    BeaverBuilderHelper::getInstance();
	    WPAjaxController::getInstance();
	    PowerpackIntegration::getInstance();
	    UABBIntegration::getInstance();
	    WPDBBAdditionsIntegration::getInstance();
	    BBSubscribeFormIntegration::getInstance();
	    /**
	     * Added Feature from Add ons version
	     * @since 1.1.1
	     */
	    PopupFromLink::getInstance();
	    ScrollTrigger::getInstance();
	    /**
	     * Added Features from 1.1.1
	     */
	    HidePopupFromButton::getInstance();
    }
}
