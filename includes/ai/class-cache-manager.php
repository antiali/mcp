<?php
/**
 * Cache Manager for AISBP namespace
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Cache Manager class
 * 
 * Handles caching for AI generation requests
 */
class Cache_Manager {

    /**
     * Generate cache key
     *
     * @param string $model_id Model identifier.
     * @param string $prompt   Prompt text.
     * @param array  $params   Additional parameters.
     * @return string Cache key.
     */
    public function generate_key( $model_id, $prompt, $params = array() ) {
        $key_data = array(
            'model' => $model_id,
            'prompt' => substr( $prompt, 0, 200 ), // First 200 chars
            'params' => isset( $params['phase'] ) ? $params['phase'] : 0,
        );
        
        return 'aisbp_cache_' . md5( serialize( $key_data ) );
    }

    /**
     * Get cached value
     *
     * @param string $key Cache key.
     * @return mixed|false Cached value or false.
     */
    public function get( $key ) {
        return wp_cache_get( $key, 'aisbp' );
    }

    /**
     * Set cache value
     *
     * @param string $key     Cache key.
     * @param mixed  $value   Value to cache.
     * @param int    $expiry  Expiry time in seconds. Default 1 hour.
     * @return bool Success.
     */
    public function set( $key, $value, $expiry = 3600 ) {
        return wp_cache_set( $key, $value, 'aisbp', $expiry );
    }

    /**
     * Delete cache
     *
     * @param string $key Cache key.
     * @return bool Success.
     */
    public function delete( $key ) {
        return wp_cache_delete( $key, 'aisbp' );
    }

    /**
     * Clear all AISBP cache
     *
     * @return bool Success.
     */
    public function clear_all() {
        // WordPress object cache doesn't have a direct "clear group" method
        // This would need to be implemented by the cache plugin
        return true;
    }
}

