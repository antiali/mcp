<?php
/**
 * Cache Manager
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Cache_Manager {
    
    const CACHE_GROUP = 'awbu';
    const CACHE_EXPIRY = 3600;
    
    public static function get($key) {
        return wp_cache_get($key, self::CACHE_GROUP);
    }
    
    public static function get_colors_key($builder) {
        return 'awbu_colors_' . $builder;
    }
    
    public static function get_variables_key($builder) {
        return 'awbu_variables_' . $builder;
    }
    
    public static function set($key, $data, $expiry = null) {
        if (null === $expiry) {
            $expiry = self::CACHE_EXPIRY;
        }
        return wp_cache_set($key, $data, self::CACHE_GROUP, $expiry);
    }
    
    public static function delete($key) {
        return wp_cache_delete($key, self::CACHE_GROUP);
    }
    
    public static function clear_all() {
        self::delete('awbu_colors_cache');
        self::delete('awbu_variables_cache');
        self::delete('awbu_builder_cache');
        return true;
    }
}

