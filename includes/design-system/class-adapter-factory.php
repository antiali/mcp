<?php
/**
 * Adapter Factory - مصنع الـ Adapters
 * 
 * Factory Pattern لإنشاء Builder Adapters
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Adapter_Factory {
    
    /**
     * Adapter cache for singleton-like behavior
     */
    private static $adapter_cache = array();
    
    /**
     * Create adapter instance with error handling
     * 
     * @param string $builder Builder identifier
     * @param bool $return_error Whether to return WP_Error on failure
     * @return UDS_Builder_Adapter_Interface|WP_Error|null
     */
    public static function create($builder, $return_error = false) {
        // Validate input
        if (empty($builder) || !is_string($builder)) {
            $builder = 'default';
        }
        
        $builder = sanitize_key($builder);
        
        // Return cached adapter if available
        if (isset(self::$adapter_cache[$builder])) {
            return self::$adapter_cache[$builder];
        }
        
        $adapters = array(
            'divi5' => 'UDS_Divi5_Adapter',
            'divi4' => 'UDS_Divi4_Adapter',
            'elementor' => 'UDS_Elementor_Adapter',
            'gutenberg' => 'UDS_Gutenberg_Adapter',
            'astra' => 'UDS_Astra_Adapter',
            'kadence' => 'UDS_Kadence_Adapter',
            'blocksy' => 'UDS_Blocksy_Adapter',
            'default' => 'UDS_Default_Adapter',
        );
        
        $adapter_class = isset($adapters[$builder]) ? $adapters[$builder] : 'UDS_Default_Adapter';
        
        // Check if adapter class exists
        if (!class_exists($adapter_class)) {
            error_log("AWBU Adapter Factory: Adapter class '{$adapter_class}' not found for builder '{$builder}'");
            
            // Try fallback to default
            if (class_exists('UDS_Default_Adapter')) {
                $adapter = new UDS_Default_Adapter();
                self::$adapter_cache[$builder] = $adapter;
                return $adapter;
            }
            
            // Return error if requested
            if ($return_error) {
                return new WP_Error(
                    'adapter_not_found',
                    sprintf(__('Adapter for builder "%s" not found.', 'ai-website-builder-unified'), $builder)
                );
            }
            
            return null;
        }
        
        try {
            $adapter = new $adapter_class();
            
            // Verify adapter implements interface
            if (!($adapter instanceof UDS_Builder_Adapter_Interface)) {
                error_log("AWBU Adapter Factory: Adapter '{$adapter_class}' does not implement UDS_Builder_Adapter_Interface");
                
                if ($return_error) {
                    return new WP_Error(
                        'invalid_adapter',
                        sprintf(__('Adapter "%s" does not implement required interface.', 'ai-website-builder-unified'), $adapter_class)
                    );
                }
                
                // Fallback to default
                if (class_exists('UDS_Default_Adapter')) {
                    $adapter = new UDS_Default_Adapter();
                }
            }
            
            self::$adapter_cache[$builder] = $adapter;
            return $adapter;
            
        } catch (Exception $e) {
            error_log("AWBU Adapter Factory: Error creating adapter '{$adapter_class}': " . $e->getMessage());
            
            if ($return_error) {
                return new WP_Error('adapter_creation_failed', $e->getMessage());
            }
            
            return null;
        }
    }
    
    /**
     * Check if a specific builder's adapter is available
     * 
     * @param string $builder Builder identifier
     * @return bool
     */
    public static function is_available($builder) {
        $adapter = self::create($builder, true);
        return !is_wp_error($adapter) && $adapter !== null;
    }
    
    /**
     * Get list of available adapters
     * 
     * @return array
     */
    public static function get_available_adapters() {
        $adapters = array('divi5', 'divi4', 'elementor', 'gutenberg', 'astra', 'kadence', 'blocksy', 'default');
        $available = array();
        
        foreach ($adapters as $builder) {
            if (self::is_available($builder)) {
                $available[] = $builder;
            }
        }
        
        return $available;
    }
    
    /**
     * Clear adapter cache
     */
    public static function clear_cache() {
        self::$adapter_cache = array();
    }
}

