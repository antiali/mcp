<?php
/**
 * Gemini Connector - رابط Gemini
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Gemini_Connector {
    
    private $api_key;
    private $model = 'gemini-1.5-pro';

    public function __construct() {
        // CRITICAL FIX: Read from both option names and individual options for maximum compatibility
        $awbu_keys = \get_option('awbu_api_keys', array());
        $aisbp_keys = \get_option('aisbp_api_keys', array());
        
        // Merge: awbu_api_keys takes priority, then aisbp_api_keys fills gaps
        $api_keys = array_merge($aisbp_keys, $awbu_keys);
        
        $this->api_key = isset($api_keys['gemini']) ? $api_keys['gemini'] : '';
        
        // Fallback to individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('awbu_gemini_api_key', '');
        }
        
        // Final fallback to AISBP individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('aisbp_gemini_api_key', '');
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
        
        return $this->generate($prompt, $generate_params);
    }

    public function generate($prompt, $params = array()) {
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', \__('Gemini API Key is missing.', 'ai-website-builder-unified'));
        }

        $model = isset($params['model_id']) ? $params['model_id'] : $this->model;
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $this->api_key;

        $response = \wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => ($params['system'] ?? '') . "\n\n" . $prompt)
                        )
                    )
                ),
                'generationConfig' => array(
                    'temperature' => isset($params['temperature']) ? floatval($params['temperature']) : 0.7,
                    'maxOutputTokens' => isset($params['max_tokens']) ? intval($params['max_tokens']) : 4096,
                )
            )),
            'timeout' => 180,
        ));

        if (\is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(\wp_remote_retrieve_body($response), true);
        if (isset($body['error'])) {
            return new \WP_Error('gemini_error', $body['error']['message']);
        }

        if (empty($body['candidates'][0]['content']['parts'][0]['text'])) {
            return new \WP_Error('gemini_empty', \__('Gemini returned an empty response.', 'ai-website-builder-unified'));
        }

        $content = $body['candidates'][0]['content']['parts'][0]['text'];
        
        // Extract token usage if available (Gemini uses different format)
        $prompt_tokens = isset($body['usageMetadata']['promptTokenCount']) ? intval($body['usageMetadata']['promptTokenCount']) : 0;
        $completion_tokens = isset($body['usageMetadata']['candidatesTokenCount']) ? intval($body['usageMetadata']['candidatesTokenCount']) : 0;
        $total_tokens = isset($body['usageMetadata']['totalTokenCount']) ? intval($body['usageMetadata']['totalTokenCount']) : ($prompt_tokens + $completion_tokens);
        
        // Calculate cost (Gemini 1.5 Pro pricing: $1.25 per 1M input tokens, $5.00 per 1M output tokens)
        $cost = ($prompt_tokens / 1000000 * 1.25) + ($completion_tokens / 1000000 * 5.00);
        
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
