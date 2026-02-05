<?php
/**
 * Design Manager - مدير نظام التصميم الموحد
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Design_Manager {
    
    /**
     * Update design system for all builders
     * 
     * @param array $colors Colors array
     * @param array $variables Variables array
     * @param UDS_Builder_Adapter_Interface $adapter Builder adapter
     * @return array|WP_Error Result or error
     */
    public function update_design_system($colors, $variables, $adapter) {
        // Validate adapter
        if (!$adapter instanceof UDS_Builder_Adapter_Interface) {
            return new WP_Error(
                'invalid_adapter',
                __('Invalid adapter provided.', 'ai-website-builder-unified'),
                array('status' => 400)
            );
        }
        
        if (!$adapter->is_available()) {
            return new WP_Error(
                'adapter_unavailable',
                __('Builder adapter is not available.', 'ai-website-builder-unified'),
                array('status' => 503)
            );
        }
        
        $results = array();
        $errors = array();
        
        // Update colors
        if (!empty($colors)) {
            try {
                $color_result = $adapter->set_colors($colors);
                if (is_wp_error($color_result)) {
                    $errors[] = $color_result;
                } else {
                    $results['colors'] = $color_result;
                }
            } catch (Exception $e) {
                $errors[] = new WP_Error('color_update_failed', $e->getMessage());
            }
        }
        
        // Update variables (only if colors succeeded)
        if (!empty($variables) && empty($errors)) {
            try {
                $var_result = $adapter->set_variables($variables);
                if (is_wp_error($var_result)) {
                    $errors[] = $var_result;
                } else {
                    $results['variables'] = $var_result;
                }
            } catch (Exception $e) {
                $errors[] = new WP_Error('variable_update_failed', $e->getMessage());
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error(
                'update_failed',
                __('Failed to update design system.', 'ai-website-builder-unified'),
                $errors
            );
        }
        
        // Clear cache only on success
        $adapter->clear_cache();
        
        // Trigger action
        do_action('awbu_design_system_updated', array(
            'colors' => $colors,
            'variables' => $variables,
            'builder' => $adapter->get_builder_name(),
        ));
        
        return array(
            'success' => true,
            'builder' => $adapter->get_builder_name(),
            'results' => $results,
        );
    }
    
    /**
     * Sync all design system data
     */
    public function sync_all($adapter) {
        $colors = $adapter->get_colors();
        $variables = $adapter->get_variables();
        
        // Re-apply to ensure consistency
        if (!empty($colors)) {
            $adapter->set_colors($colors);
        }
        
        if (!empty($variables)) {
            $adapter->set_variables($variables);
        }
        
        $adapter->clear_cache();
        
        return array(
            'success' => true,
            'colors_count' => count($colors),
            'variables_count' => count($variables),
        );
    }
    
    /**
     * Export design system
     */
    public function export($adapter) {
        return array(
            'builder' => $adapter->get_builder_name(),
            'colors' => $adapter->get_colors(),
            'variables' => $adapter->get_variables(),
            'exported_at' => current_time('mysql'),
        );
    }
    
    /**
     * Import design system
     */
    public function import($data, $adapter) {
        $colors = isset($data['colors']) ? $data['colors'] : array();
        $variables = isset($data['variables']) ? $data['variables'] : array();
        
        return $this->update_design_system($colors, $variables, $adapter);
    }
}

