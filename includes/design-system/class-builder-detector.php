<?php
/**
 * Builder Detector - اكتشاف Page Builder النشط
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Builder_Detector {
    
    /**
     * Detect active page builder
     * 
     * @return string Builder identifier
     */
    public static function detect() {
        // Divi 5 (Priority - newest)
        if (class_exists('ET\Builder\Packages\GlobalData\GlobalData')) {
            return 'divi5';
        }
        
        // Divi 4
        if (class_exists('ET_Builder_Plugin') || defined('ET_BUILDER_VERSION')) {
            return 'divi4';
        }
        
        // Elementor
        if (defined('ELEMENTOR_VERSION')) {
            return 'elementor';
        }
        
        // Gutenberg / Full Site Editing
        if (function_exists('register_block_type') && 
            (function_exists('wp_is_block_theme') && wp_is_block_theme())) {
            return 'gutenberg';
        }
        
        // Astra
        if (defined('ASTRA_THEME_VERSION')) {
            return 'astra';
        }
        
        // Kadence
        if (defined('KADENCE_VERSION')) {
            return 'kadence';
        }
        
        // Blocksy
        if (defined('BLOCKSY_VERSION')) {
            return 'blocksy';
        }
        
        // Default / Classic Theme
        return 'default';
    }
    
    /**
     * Get builder name
     */
    public static function get_name($builder = null) {
        if (!$builder) {
            $builder = self::detect();
        }
        
        $names = array(
            'divi5' => 'Divi 5',
            'divi4' => 'Divi 4',
            'elementor' => 'Elementor',
            'gutenberg' => 'Gutenberg / Full Site Editing',
            'astra' => 'Astra',
            'kadence' => 'Kadence',
            'blocksy' => 'Blocksy',
            'default' => 'Default Theme',
        );
        
        return isset($names[$builder]) ? $names[$builder] : 'Unknown';
    }
    
    /**
     * Check if builder is active
     */
    public static function is_active($builder) {
        return self::detect() === $builder;
    }
    
    /**
     * Get all available builders
     */
    public static function get_available() {
        $available = array();
        
        $builders = array(
            'divi5' => class_exists('ET\Builder\Packages\GlobalData\GlobalData'),
            'divi4' => class_exists('ET_Builder_Plugin') || defined('ET_BUILDER_VERSION'),
            'elementor' => defined('ELEMENTOR_VERSION'),
            'gutenberg' => function_exists('register_block_type'),
            'astra' => defined('ASTRA_THEME_VERSION'),
            'kadence' => defined('KADENCE_VERSION'),
            'blocksy' => defined('BLOCKSY_VERSION'),
        );
        
        foreach ($builders as $builder => $is_available) {
            if ($is_available) {
                $available[] = $builder;
            }
        }
        
        return $available;
    }
}

