<?php
/**
 * Blocksy Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Blocksy_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        if (!defined('BLOCKSY_VERSION')) {
            return array();
        }
        
        $colors = array();
        $palette = get_theme_mod('colorPalette', array());
        
        if (is_array($palette)) {
            foreach ($palette as $index => $color) {
                $colors['blocksy-color-' . $index] = array(
                    'color' => $color,
                    'name' => 'Color ' . ($index + 1),
                );
            }
        }
        
        return $colors;
    }
    
    public function set_colors($colors) {
        if (!defined('BLOCKSY_VERSION')) {
            return false;
        }
        
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $palette = array();
        foreach ($validated as $key => $data) {
            $hex = is_array($data) ? $data['color'] : $data;
            $palette[] = sanitize_hex_color($hex);
        }
        
        set_theme_mod('colorPalette', $palette);
        $this->clear_cache();
        return true;
    }
    
    public function get_variables() {
        return array();
    }
    
    public function set_variables($variables) {
        return true;
    }
    
    public function clear_cache() {
        AWBU_Cache_Manager::clear_all();
        return true;
    }
    
    public function get_builder_name() {
        return 'Blocksy';
    }
    
    public function is_available() {
        return defined('BLOCKSY_VERSION');
    }
}

