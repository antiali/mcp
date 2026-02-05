<?php
/**
 * Divi 5 Adapter - متوافق مع بنية Divi 5 الرسمية
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class UDS_Divi5_Adapter implements UDS_Builder_Adapter_Interface {
    
    public function get_colors() {
        // PERFORMANCE: Check cache first
        $cache_key = AWBU_Cache_Manager::get_colors_key('divi5');
        $cached = AWBU_Cache_Manager::get($cache_key);
        if (false !== $cached) {
            return $cached;
        }
        
        // Method 1: Use Divi 5 GlobalData class (official)
        if (class_exists('ET\Builder\Packages\GlobalData\GlobalData')) {
            try {
                $colors = ET\Builder\Packages\GlobalData\GlobalData::get_global_colors();
                if (!empty($colors)) {
                    $formatted = $this->format_colors($colors);
                    AWBU_Cache_Manager::set($cache_key, $formatted);
                    return $formatted;
                }
            } catch (Exception $e) {
                error_log('AWBU Divi5: Error using GlobalData class - ' . $e->getMessage());
            }
        }
        
        // Method 2: Read from et_global_data (primary location)
        $et_global_data = get_option('et_global_data', array());
        if (isset($et_global_data['global_colors']) && !empty($et_global_data['global_colors'])) {
            $formatted = $this->format_colors($et_global_data['global_colors']);
            AWBU_Cache_Manager::set($cache_key, $formatted);
            return $formatted;
        }
        
        return array();
    }
    
    public function set_colors($colors) {
        // SECURITY: Validate input first
        $validated = AWBU_Validator::validate_colors($colors);
        if (is_wp_error($validated)) {
            return $validated;
        }
        
        $formatted_colors = $this->prepare_colors($validated);
        
        // Method 1: Use Divi 5 GlobalData class (official - preferred)
        if (class_exists('ET\Builder\Packages\GlobalData\GlobalData')) {
            try {
                ET\Builder\Packages\GlobalData\GlobalData::set_global_colors($formatted_colors);
                $this->sync_to_all_locations($formatted_colors);
                $this->clear_cache();
                return true;
            } catch (Exception $e) {
                error_log('AWBU Divi5: Error using GlobalData::set_global_colors - ' . $e->getMessage());
            }
        }
        
        // Method 2: Direct database update (fallback)
        return $this->save_colors_direct($formatted_colors);
    }
    
    public function get_variables() {
        if (class_exists('ET\Builder\Packages\GlobalData\GlobalData')) {
            try {
                $variables = ET\Builder\Packages\GlobalData\GlobalData::get_global_variables();
                if (!empty($variables)) {
                    return $variables;
                }
            } catch (Exception $e) {
                error_log('AWBU Divi5: Error getting variables - ' . $e->getMessage());
            }
        }
        
        $et_global_data = get_option('et_global_data', array());
        if (isset($et_global_data['global_variables']) && !empty($et_global_data['global_variables'])) {
            return $et_global_data['global_variables'];
        }
        
        return array();
    }
    
    public function set_variables($variables) {
        if (class_exists('ET\Builder\Packages\GlobalData\GlobalData')) {
            try {
                ET\Builder\Packages\GlobalData\GlobalData::set_global_variables($variables);
                $this->sync_variables_to_all_locations($variables);
                $this->clear_cache();
                return true;
            } catch (Exception $e) {
                error_log('AWBU Divi5: Error setting variables - ' . $e->getMessage());
            }
        }
        
        return $this->save_variables_direct($variables);
    }
    
    public function clear_cache() {
        if (class_exists('ET_Core_PageResource')) {
            ET_Core_PageResource::remove_static_resources('all', 'all', true);
        }
        if (function_exists('et_core_clear_cache')) {
            et_core_clear_cache();
        }
        if (function_exists('et_core_clear_wp_cache')) {
            et_core_clear_wp_cache();
        }
        if (function_exists('et_builder_invalidate_cache')) {
            et_builder_invalidate_cache();
        }
        
        wp_cache_delete('et_global_data', 'options');
        wp_cache_delete('et_divi', 'options');
        wp_cache_delete('et_core_global_colors', 'options');
        AWBU_Cache_Manager::clear_all();
        
        // SECURITY FIX: Use prepared statements
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_et_') . '%',
            $wpdb->esc_like('_transient_timeout_et_') . '%'
        ));
        
        return true;
    }
    
    public function get_builder_name() {
        return 'Divi 5';
    }
    
    public function is_available() {
        return class_exists('ET\Builder\Packages\GlobalData\GlobalData');
    }
    
    private function format_colors($colors) {
        $formatted = array();
        foreach ($colors as $gcid => $data) {
            if (is_array($data)) {
                $formatted[$gcid] = array(
                    'color' => isset($data['color']) ? $data['color'] : '',
                    'name' => isset($data['name']) ? $data['name'] : ucwords(str_replace(array('gcid-', '-'), array('', ' '), $gcid)),
                    'active' => isset($data['active']) ? $data['active'] : 'on',
                );
            } else {
                $formatted[$gcid] = array(
                    'color' => $data,
                    'name' => ucwords(str_replace(array('gcid-', '-'), array('', ' '), $gcid)),
                    'active' => 'on',
                );
            }
        }
        return $formatted;
    }
    
    private function prepare_colors($colors) {
        $prepared = array();
        foreach ($colors as $key => $data) {
            $gcid = (strpos($key, 'gcid-') === 0) ? $key : 'gcid-' . str_replace('_', '-', $key);
            if (is_array($data)) {
                $prepared[$gcid] = array(
                    'color' => isset($data['color']) ? sanitize_hex_color($data['color']) : '',
                    'active' => isset($data['active']) ? $data['active'] : 'on',
                    'name' => isset($data['name']) ? $data['name'] : ucwords(str_replace(array('gcid-', '-'), array('', ' '), $gcid)),
                );
            } else {
                $prepared[$gcid] = array(
                    'color' => sanitize_hex_color($data),
                    'active' => 'on',
                    'name' => ucwords(str_replace(array('gcid-', '-'), array('', ' '), $gcid)),
                );
            }
        }
        return $prepared;
    }
    
    private function sync_to_all_locations($colors) {
        $et_global_data = get_option('et_global_data', array());
        if (!is_array($et_global_data)) $et_global_data = array();
        $et_global_data['global_colors'] = $colors;
        update_option('et_global_data', $et_global_data);
        
        $divi_options = get_option('et_divi', array());
        if (!is_array($divi_options)) $divi_options = array();
        if (!isset($divi_options['et_global_data'])) {
            $divi_options['et_global_data'] = array();
        }
        $divi_options['et_global_data']['global_colors'] = $colors;
        $divi_options['et_global_colors'] = $colors;
        update_option('et_divi', $divi_options);
        
        update_option('et_core_global_colors', $colors);
        update_option('et_global_colors', $colors);
        
        if (function_exists('et_update_option')) {
            et_update_option('et_global_colors', $colors);
        }
    }
    
    private function sync_variables_to_all_locations($variables) {
        $et_global_data = get_option('et_global_data', array());
        if (!is_array($et_global_data)) $et_global_data = array();
        $et_global_data['global_variables'] = $variables;
        update_option('et_global_data', $et_global_data);
        
        $divi_options = get_option('et_divi', array());
        if (!is_array($divi_options)) $divi_options = array();
        if (!isset($divi_options['et_global_data'])) {
            $divi_options['et_global_data'] = array();
        }
        $divi_options['et_global_data']['global_variables'] = $variables;
        update_option('et_divi', $divi_options);
    }
    
    private function save_colors_direct($colors) {
        $this->sync_to_all_locations($colors);
        $this->clear_cache();
        return true;
    }
    
    private function save_variables_direct($variables) {
        $this->sync_variables_to_all_locations($variables);
        $this->clear_cache();
        return true;
    }
}

