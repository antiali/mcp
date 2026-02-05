<?php
/**
 * DeepSeek Connector - رابط DeepSeek
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_DeepSeek_Connector {
    
    private $api_key;
    private $model = 'deepseek-chat';

    public function __construct() {
        // CRITICAL FIX: Read from both option names and individual options for maximum compatibility
        $awbu_keys = \get_option('awbu_api_keys', array());
        $aisbp_keys = \get_option('aisbp_api_keys', array());
        
        // Merge: awbu_api_keys takes priority, then aisbp_api_keys fills gaps
        $api_keys = array_merge($aisbp_keys, $awbu_keys);
        
        $this->api_key = isset($api_keys['deepseek']) ? $api_keys['deepseek'] : '';
        
        // Fallback to individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('awbu_deepseek_api_key', '');
        }
        
        // Final fallback to AISBP individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('aisbp_deepseek_api_key', '');
        }
    }

    /**
     * Request method - Unified interface for AI orchestrator
     * 
     * @param string $prompt The prompt text
     * @param array $params Request parameters
     * @param array $images Optional images for vision models
     * @return array|WP_Error Response data or error
     */
    public function request($prompt, $params = array(), $images = array()) {
        // Convert to generate() format
        $generate_params = array(
            'model_id' => $params['model_id'] ?? $this->model,
            'system' => $params['system'] ?? '',
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 4096,
        );
        
        $result = $this->generate($prompt, $generate_params);
        
        // Convert generate() response to orchestrator format
        if (\is_wp_error($result)) {
            return $result;
        }
        
        // Extract token usage from API response if available
        // For now, return the result as-is since generate() already returns the correct format
        // But ensure it has 'content' key
        if (isset($result['content'])) {
            return $result;
        }
        
        // Fallback: if generate() returned something unexpected
        return new \WP_Error('invalid_response_format', \__('Invalid response format from connector.', 'ai-website-builder-unified'));
    }

    public function generate($prompt, $params = array()) {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', \__('DeepSeek API Key is missing.', 'ai-website-builder-unified'), array('status' => 401));
        }

        $model = isset($params['model_id']) ? $params['model_id'] : $this->model;

        $response = \wp_remote_post('https://api.deepseek.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $params['system'] ?? 'You are a professional web designer.'),
                    array('role' => 'user', 'content' => $prompt),
                ),
                'temperature' => isset($params['temperature']) ? floatval($params['temperature']) : 0.7,
                'max_tokens' => isset($params['max_tokens']) ? intval($params['max_tokens']) : 4096,
            )),
            'timeout' => 180,
        ));

        if (\is_wp_error($response)) {
            $response->add_data(array('status' => 502));
            return $response;
        }
        
        $body = json_decode(\wp_remote_retrieve_body($response), true);
        $http_code = \wp_remote_retrieve_response_code($response);
        
        // Log for debugging
        error_log('[AWBU DeepSeek] HTTP Code: ' . $http_code);
        error_log('[AWBU DeepSeek] Response body: ' . substr(\wp_remote_retrieve_body($response), 0, 500));
        
        // Safety check for empty or invalid response
        if (empty($body) || !is_array($body)) {
            return new \WP_Error('deepseek_invalid_response', \__('Invalid response from DeepSeek API.', 'ai-website-builder-unified') . ' (HTTP ' . $http_code . ')', array('status' => 502));
        }
        
        if (isset($body['error'])) {
            // FIXED: Extract detailed error message from API response
            $error_msg = isset($body['error']['message']) ? $body['error']['message'] : json_encode($body['error']);
            $error_type = isset($body['error']['type']) ? $body['error']['type'] : 'unknown';
            $error_code = isset($body['error']['code']) ? $body['error']['code'] : 'api_error';
            
            // Enhanced error logging
            error_log('[AWBU DeepSeek] API Error (' . $http_code . '): ' . $error_msg);
            error_log('[AWBU DeepSeek] Error Type: ' . $error_type . ', Code: ' . $error_code);
            
            // Provide user-friendly error messages
            if ($http_code === 401) {
                $error_msg = \__('مفتاح API غير صالح أو منتهي الصلاحية. تحقق من الإعدادات.', 'ai-website-builder-unified');
            } elseif ($http_code === 429) {
                $error_msg = \__('تم تجاوز حد الطلبات. انتظر دقيقة ثم حاول مرة أخرى.', 'ai-website-builder-unified');
            } elseif ($http_code === 500 || $http_code === 503) {
                $error_msg = \__('خادم API مشغول حالياً. حاول مرة أخرى بعد دقائق.', 'ai-website-builder-unified');
            }
            
            return new \WP_Error('deepseek_error', $error_msg, array(
                'status' => $http_code,
                'type' => $error_type,
                'code' => $error_code
            ));
        }
        
        // Safety check for missing choices
        if (!isset($body['choices']) || empty($body['choices']) || !isset($body['choices'][0]['message']['content'])) {
            error_log('[AWBU DeepSeek] Malformed response - No choices found');
            return new \WP_Error('deepseek_malformed_response', \__('Malformed response from DeepSeek API.', 'ai-website-builder-unified'), array('status' => 502));
        }

        $content = $body['choices'][0]['message']['content'];
        
        // Extract token usage if available
        $prompt_tokens = isset($body['usage']['prompt_tokens']) ? intval($body['usage']['prompt_tokens']) : 0;
        $completion_tokens = isset($body['usage']['completion_tokens']) ? intval($body['usage']['completion_tokens']) : 0;
        $total_tokens = isset($body['usage']['total_tokens']) ? intval($body['usage']['total_tokens']) : ($prompt_tokens + $completion_tokens);
        
        // Calculate cost (DeepSeek pricing: $0.14 per 1M input tokens, $0.28 per 1M output tokens)
        $cost = ($prompt_tokens / 1000000 * 0.14) + ($completion_tokens / 1000000 * 0.28);
        
        return array(
            'content' => $content,
            'code' => $this->extract_code($content, 'html'),
            'css' => $this->extract_code($content, 'css'),
            'js' => $this->extract_code($content, 'javascript'),
            'prompt_tokens' => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'total_tokens' => $total_tokens,
            'cost' => $cost,
        );
    }

    private function extract_code($content, $lang) {
        $pattern = '/```' . $lang . '(.*?)```/s';
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
}
