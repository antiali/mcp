<?php
/**
 * Elementor Adapter
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Elementor_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        if (!defined('ELEMENTOR_VERSION')) {
            return array();
        }
        
        $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
        $settings = $kit->get_settings();
        
        $colors = array();
        if (isset($settings['system_colors'])) {
            foreach ($settings['system_colors'] as $color) {
                $colors['elementor-color-' . $color['_id']] = array(
                    'color' => $color['color'],
                    'name' => $color['title'],
                );
            }
        }
        
        return $colors;
    }
    
    public function set_colors($colors) {
        if (!defined('ELEMENTOR_VERSION')) {
            return false;
        }
        
        // SECURITY: Validate input
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
        $settings = $kit->get_settings();
        
        $system_colors = array();
        foreach ($validated as $key => $data) {
            $hex = is_array($data) ? $data['color'] : $data;
            $name = is_array($data) && isset($data['name']) ? $data['name'] : ucwords(str_replace('_', ' ', $key));
            
            $system_colors[] = array(
                '_id' => uniqid('color_'),
                'title' => $name,
                'color' => sanitize_hex_color($hex),
            );
        }
        
        $settings['system_colors'] = $system_colors;
        $kit->save($settings);
        
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
        if (defined('ELEMENTOR_VERSION')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        AWBU_Cache_Manager::clear_all();
        return true;
    }
    
    public function get_builder_name() {
        return 'Elementor';
    }
    
    public function is_available() {
        return defined('ELEMENTOR_VERSION');
    }
}

