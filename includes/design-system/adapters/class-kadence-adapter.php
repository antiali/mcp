<?php
/**
 * Kadence Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Kadence_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        if (!defined('KADENCE_VERSION')) {
            return array();
        }
        
        $colors = array();
        for ($i = 1; $i <= 9; $i++) {
            $color = get_theme_mod('palette-color-' . $i, '');
            if ($color) {
                $colors['kadence-color-' . $i] = array(
                    'color' => $color,
                    'name' => 'Color ' . $i,
                );
            }
        }
        
        return $colors;
    }
    
    public function set_colors($colors) {
        if (!defined('KADENCE_VERSION')) {
            return false;
        }
        
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $index = 1;
        foreach ($validated as $key => $data) {
            $hex = is_array($data) ? $data['color'] : $data;
            set_theme_mod('palette-color-' . $index, sanitize_hex_color($hex));
            $index++;
            if ($index > 9) break;
        }
        
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
        return 'Kadence';
    }
    
    public function is_available() {
        return defined('KADENCE_VERSION');
    }
}

