<?php
/**
 * Cache management for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Cache {
    
    private static $cache_keys = [
        'k_latest_products_data',
        'k_sales_products_data',
        'k_girls_products_data',
        'k_boys_products_data',
        'k_sport_products_data',
        'k_blog_posts_data',
    ];
    
    /**
     * Clear all product caches
     */
    public static function clear_products_cache() {
        foreach (self::$cache_keys as $key) {
            delete_transient($key);
        }
        
        // Clear extra accordion caches
        $extra_accordions = get_option('k_extra_accordions', []);
        foreach ($extra_accordions as $index => $accordion) {
            delete_transient('k_extra_accordion_' . $index);
        }
    }
    
    /**
     * Clear specific cache
     */
    public static function clear($key) {
        delete_transient($key);
    }
    
    /**
     * Get cached data
     */
    public static function get($key) {
        return get_transient($key);
    }
    
    /**
     * Set cached data
     */
    public static function set($key, $data, $expiration = HOUR_IN_SECONDS) {
        set_transient($key, $data, $expiration);
    }
}
