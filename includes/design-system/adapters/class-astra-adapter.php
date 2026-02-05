<?php
/**
 * Astra Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Astra_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        if (!defined('ASTRA_THEME_VERSION')) {
            return array();
        }
        
        $colors = array();
        $color_options = array(
            'astra-color-palette-1' => 'Primary',
            'astra-color-palette-2' => 'Secondary',
            'astra-color-palette-3' => 'Accent',
        );
        
        foreach ($color_options as $option => $name) {
            $color = get_theme_mod($option, '');
            if ($color) {
                $colors['astra-' . $option] = array(
                    'color' => $color,
                    'name' => $name,
                );
            }
        }
        
        return $colors;
    }
    
    public function set_colors($colors) {
        if (!defined('ASTRA_THEME_VERSION')) {
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
            set_theme_mod('astra-color-palette-' . $index, sanitize_hex_color($hex));
            $index++;
            if ($index > 3) break;
        }
        
        set_theme_mod('astra-color-palette-updated', time());
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
        return 'Astra';
    }
    
    public function is_available() {
        return defined('ASTRA_THEME_VERSION');
    }
}

