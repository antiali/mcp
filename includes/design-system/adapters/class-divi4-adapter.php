<?php
/**
 * Divi 4 Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Divi4_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        $global_colors = get_option('et_global_colors', array());
        return $this->format_colors($global_colors);
    }
    
    public function set_colors($colors) {
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $formatted = $this->prepare_colors($validated);
        
        if (function_exists('et_update_option')) {
            et_update_option('et_global_colors', $formatted);
        } else {
            update_option('et_global_colors', $formatted);
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
        if (class_exists('ET_Core_PageResource')) {
            ET_Core_PageResource::remove_static_resources('all', 'all', true);
        }
        AWBU_Cache_Manager::clear_all();
        return true;
    }
    
    public function get_builder_name() {
        return 'Divi 4';
    }
    
    public function is_available() {
        return class_exists('ET_Builder_Plugin') || defined('ET_BUILDER_VERSION');
    }
    
    private function format_colors($colors) {
        $formatted = array();
        foreach ($colors as $gcid => $data) {
            $formatted[$gcid] = array(
                'color' => is_array($data) ? ($data['color'] ?? '') : $data,
                'name' => is_array($data) ? ($data['name'] ?? '') : '',
            );
        }
        return $formatted;
    }
    
    private function prepare_colors($colors) {
        $prepared = array();
        foreach ($colors as $key => $data) {
            $gcid = (strpos($key, 'gcid-') === 0) ? $key : 'gcid-' . str_replace('_', '-', $key);
            $prepared[$gcid] = array(
                'color' => is_array($data) ? ($data['color'] ?? '') : $data,
                'active' => 'on',
            );
        }
        return $prepared;
    }
}

