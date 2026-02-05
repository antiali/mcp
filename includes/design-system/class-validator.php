<?php
/**
 * Validator
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Validator {
    
    const MAX_COLORS = 100;
    const MAX_VARIABLES = 200;
    const MAX_CODE_SIZE = 10 * 1024 * 1024;
    
    public static function validate_colors($colors) {
        if (!is_array($colors)) {
            return new WP_Error('invalid_colors', __('Colors must be an array.', 'ai-website-builder-unified'));
        }
        
        if (count($colors) > self::MAX_COLORS) {
            return new WP_Error('too_many_colors', sprintf(__('Maximum %d colors allowed.', 'ai-website-builder-unified'), self::MAX_COLORS));
        }
        
        $validated = array();
        foreach ($colors as $key => $data) {
            $key = sanitize_key($key);
            
            if (is_array($data)) {
                $color = isset($data['color']) ? sanitize_hex_color($data['color']) : '';
                if (!empty($color) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    return new WP_Error('invalid_color', sprintf(__('Invalid color format: %s', 'ai-website-builder-unified'), $data['color']));
                }
                
                $validated[$key] = array(
                    'color' => $color,
                    'name' => isset($data['name']) ? sanitize_text_field($data['name']) : '',
                    'active' => isset($data['active']) ? sanitize_text_field($data['active']) : 'on',
                );
            } else {
                $color = sanitize_hex_color($data);
                if (!$color || !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    return new WP_Error('invalid_color', sprintf(__('Invalid color format: %s', 'ai-website-builder-unified'), $data));
                }
                $validated[$key] = $color;
            }
        }
        
        return $validated;
    }
    
    public static function validate_variables($variables) {
        if (!is_array($variables)) {
            return new WP_Error('invalid_variables', __('Variables must be an array.', 'ai-website-builder-unified'));
        }
        
        if (count($variables) > self::MAX_VARIABLES) {
            return new WP_Error('too_many_variables', sprintf(__('Maximum %d variables allowed.', 'ai-website-builder-unified'), self::MAX_VARIABLES));
        }
        
        $validated = array();
        foreach ($variables as $key => $data) {
            $key = sanitize_key($key);
            
            if (is_array($data)) {
                $validated[$key] = array(
                    'value' => isset($data['value']) ? sanitize_text_field($data['value']) : '',
                    'unit' => isset($data['unit']) ? sanitize_text_field($data['unit']) : '',
                    'type' => isset($data['type']) ? sanitize_text_field($data['type']) : 'number',
                    'label' => isset($data['label']) ? sanitize_text_field($data['label']) : '',
                );
            } else {
                $validated[$key] = sanitize_text_field($data);
            }
        }
        
        return $validated;
    }
    
    public static function validate_code($code) {
        if (!is_string($code)) {
            return new WP_Error('invalid_code', __('Code must be a string.', 'ai-website-builder-unified'));
        }
        
        if (strlen($code) > self::MAX_CODE_SIZE) {
            return new WP_Error('code_too_large', sprintf(__('Code size exceeds maximum of %d bytes.', 'ai-website-builder-unified'), self::MAX_CODE_SIZE));
        }
        
        if (!mb_check_encoding($code, 'UTF-8')) {
            return new WP_Error('invalid_encoding', __('Code contains invalid encoding.', 'ai-website-builder-unified'));
        }
        
        return true;
    }
}

