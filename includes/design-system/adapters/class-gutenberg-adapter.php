<?php
/**
 * Gutenberg / Full Site Editing Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Gutenberg_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        $theme_json = $this->get_theme_json();
        
        $colors = array();
        if (isset($theme_json['settings']['color']['palette'])) {
            foreach ($theme_json['settings']['color']['palette'] as $color) {
                $colors['gutenberg-color-' . $color['slug']] = array(
                    'color' => $color['color'],
                    'name' => $color['name'],
                );
            }
        }
        
        return $colors;
    }
    
    public function set_colors($colors) {
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $theme_json = $this->get_theme_json();
        
        if (!isset($theme_json['settings'])) {
            $theme_json['settings'] = array();
        }
        if (!isset($theme_json['settings']['color'])) {
            $theme_json['settings']['color'] = array();
        }
        
        $palette = array();
        foreach ($validated as $key => $data) {
            $hex = is_array($data) ? $data['color'] : $data;
            $name = is_array($data) && isset($data['name']) ? $data['name'] : ucwords(str_replace('_', ' ', $key));
            $slug = str_replace('gutenberg-color-', '', $key);
            
            $palette[] = array(
                'slug' => $slug,
                'color' => sanitize_hex_color($hex),
                'name' => $name,
            );
        }
        
        $theme_json['settings']['color']['palette'] = $palette;
        $this->save_theme_json($theme_json);
        
        $this->clear_cache();
        return true;
    }
    
    public function get_variables() {
        $theme_json = $this->get_theme_json();
        return isset($theme_json['settings']['custom']) ? $theme_json['settings']['custom'] : array();
    }
    
    public function set_variables($variables) {
        $theme_json = $this->get_theme_json();
        if (!isset($theme_json['settings'])) {
            $theme_json['settings'] = array();
        }
        $theme_json['settings']['custom'] = $variables;
        $this->save_theme_json($theme_json);
        $this->clear_cache();
        return true;
    }
    
    public function clear_cache() {
        AWBU_Cache_Manager::clear_all();
        return true;
    }
    
    public function get_builder_name() {
        return 'Gutenberg / Full Site Editing';
    }
    
    public function is_available() {
        return function_exists('register_block_type');
    }
    
    private function get_theme_json() {
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        if (file_exists($theme_json_path)) {
            $json = file_get_contents($theme_json_path);
            return json_decode($json, true) ?: array();
        }
        return get_option('theme_json', array());
    }
    
    private function save_theme_json($theme_json) {
        update_option('theme_json', $theme_json);
        $theme_json_path = get_stylesheet_directory() . '/theme.json';
        if (is_writable(dirname($theme_json_path))) {
            file_put_contents($theme_json_path, wp_json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

