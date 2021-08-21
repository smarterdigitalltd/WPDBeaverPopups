<?php

/**
 * Asset helper methods
 *
 * @package     WPD\BeaverPopups\Helpers
 * @since       1.0.0
 * @author      smarterdigitalltd
 * @link        https://www.smarter.uk.com
 * @license     GNU-2.0+
 */

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;

/**
 * Class AssetHelper contains a set of handy methods for handling assets
 *
 * @package WPD\BeaverPopups\Helpers
 */
class AssetHelper
{
	/**
	 * @var
	 */
	public static $manifest;

	/**
	 * @var
	 */
	public static $distUri;

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
		$this->init();
	}

	/**
	 * @param null $manifestPath
	 * @param null $distUri
	 */
	public function init($manifestPath = null, $distUri = null)
    {
        $manifestPath = isset($manifestPath) ? $manifestPath : Plugin::assetDistDir('manifest.json');
        $distUri = isset($distUri) ? $distUri : Plugin::assetDistUri();
        self::$manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        self::$distUri = $distUri;
    }

	/**
	 * @param $asset
	 *
	 * @return null
	 */
	public static function getAssetFromManifest($asset)
    {
        return isset(self::$manifest[ $asset ]) ? self::$manifest[ $asset ] : null;
    }

	/**
	 * @param $asset
	 *
	 * @return string
	 */
	public static function getHashedAssetUri($asset)
    {
        $assetHandle = strpos($asset, '/') ? explode('/', $asset)[1] : $asset;
        $distPath = self::$distUri;
        $assetPath = ! is_null(self::getAssetFromManifest($assetHandle)) ? self::getAssetFromManifest($assetHandle) : $asset;

        return $distPath . $assetPath;
    }
}
