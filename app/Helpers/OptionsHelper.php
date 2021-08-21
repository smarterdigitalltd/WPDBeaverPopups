<?php

namespace WPD\BeaverPopups\Helpers;

use WPD\BeaverPopups\Plugin;

/**
 * Class OptionsHelper simplifies access to options.
 * Wraps get_option, update_option, delete_option.
 * We need it to simplify encryption of wp options
 * in case if customer's wp db is compromised.
 *
 * @package WPD\BeaverPopups\Helpers
 */
class OptionsHelper
{
    /**
     * Gets option value.
     * If $key value is prefixed with '_' (e.g. '_something'),
     * decryption will be applied.
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get($key, $default = '')
    {
        $key = preg_replace('/^_/', '', $key);
        $value = get_option(Plugin::$config->wp_options_prefix . $key, $default);

        return $value;
    }

    /**
     * Sets option value explicitly.
     * If $key value is prefixed with '_' (e.g. '_something'),
     * encryption will be applied.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public static function set($key, $value)
    {
        $key = preg_replace('/^_/', '', $key);

        return update_option(Plugin::$config->wp_options_prefix . $key, $value);
    }

    /**
     * Delete option
     *
     * @param $key
     *
     * @return bool
     */
    public static function del($key)
    {
        $key = preg_replace('/^_/', '', $key);

        return delete_option(Plugin::$config->wp_options_prefix . $key);
    }

    /**
     * Updates option value.
     * Sets non empty $value, or removes option if $value is empty.
     * If $key value is prefixed with '_' (e.g. '_something'),
     * encryption will be applied.
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public static function update($key, $value)
    {
        return $value ? self::set($key, $value) : self::del($key);
    }
}
