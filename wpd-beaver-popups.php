<?php

/**
 * {{ PLUGIN_NAME }} entry point file
 *
 * @package         WPD\BeaverPopups
 * @author          smarterdigitalltd
 * @license         GPL-2.0+
 * @link            https://smarter.uk.com
 *
 * Plugin Name:     {{ PLUGIN_NAME }}
 * Plugin URI:      {{ PLUGIN_WEBSITE }}
 * Description:     Take full control over your popups. Design and build with Beaver Builder
 * Version:         {{ PLUGIN_VERSION }}
 * Author:          {{ PLUGIN_AUTHOR }}
 * Author URI:      {{ PLUGIN_AUTHOR_URI }}
 * Text Domain:     wpd-beaver-popups
 * Requires WP:     {{ PLUGIN_MINIMUM_WP }}
 * Requires PHP:    {{ PLUGIN_MINIMUM_PHP }}
 * Requires BB:     {{ PLUGIN_MINIMUM_BB }}
 */

namespace WPD\BeaverPopups;

/**
 * Require dependencies
 *
 * @since 1.0.0
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( ! defined( 'WPINC' ) ) {
    die( 'No entry' );
}

/**
 * Plugin Init
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', function() {
    Plugin::getInstance(__FILE__);
} );

/**
 * Run actions when plugin is activated
 *
 * @since 1.0.0
 *
 * @return void
 */
function registerHooks()
{
    register_activation_hook( __FILE__, __NAMESPACE__ . '\flushRewriteRules' );
    register_deactivation_hook( __FILE__, __NAMESPACE__ . '\flushRewriteRules' );
    register_uninstall_hook( __FILE__, __NAMESPACE__ . '\flushRewriteRules' );
}

/**
 * Flush rewrite rules
 *
 * @since 1.0.0
 *
 * @return void
 */
function flushRewriteRules()
{
    delete_option( 'rewrite_rules' );
}

registerHooks();
