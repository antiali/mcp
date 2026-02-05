<?php
/**
 * Default Theme Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Default_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        return get_option('awbu_default_colors', array());
    }
    
    public function set_colors($colors) {
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        update_option('awbu_default_colors', $validated);
        $this->generate_css($validated);
        $this->clear_cache();
        return true;
    }
    
    public function get_variables() {
        return get_option('awbu_default_variables', array());
    }
    
    public function set_variables($variables) {
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_variables($variables);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        update_option('awbu_default_variables', $validated);
        $this->generate_css_variables($validated);
        $this->clear_cache();
        return true;
    }
    
    public function clear_cache() {
        AWBU_Cache_Manager::clear_all();
        return true;
    }
    
    public function get_builder_name() {
        return 'Default Theme';
    }
    
    public function is_available() {
        return true;
    }
    
    private function generate_css($colors) {
        $css = ':root {';
        foreach ($colors as $key => $data) {
            $hex = is_array($data) ? $data['color'] : $data;
            $var_name = '--' . str_replace('_', '-', $key);
            $css .= "\n  {$var_name}: {$hex};";
        }
        $css .= "\n}";
        
        update_option('awbu_custom_css', $css);
    }
    
    private function generate_css_variables($variables) {
        $css = get_option('awbu_custom_css', '');
        $css .= "\n:root {";
        foreach ($variables as $key => $data) {
            $value = is_array($data) ? ($data['value'] . ($data['unit'] ?? '')) : $data;
            $var_name = '--' . str_replace('_', '-', $key);
            $css .= "\n  {$var_name}: {$value};";
        }
        $css .= "\n}";
        
        update_option('awbu_custom_css', $css);
    }
}

