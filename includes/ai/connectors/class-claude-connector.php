<?php
/**
 * Claude Connector - رابط Claude
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Claude_Connector {
    
    private $api_key;
    private $model = 'claude-3-5-sonnet-20240620';

    public function __construct() {
        // CRITICAL FIX: Read from both option names and individual options for maximum compatibility
        $awbu_keys = \get_option('awbu_api_keys', array());
        $aisbp_keys = \get_option('aisbp_api_keys', array());
        
        // Merge: awbu_api_keys takes priority, then aisbp_api_keys fills gaps
        $api_keys = array_merge($aisbp_keys, $awbu_keys);
        
        $this->api_key = isset($api_keys['claude']) ? $api_keys['claude'] : '';
        
        // Fallback to individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('awbu_claude_api_key', '');
        }
        
        // Final fallback to AISBP individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('aisbp_claude_api_key', '');
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
            return new \WP_Error('missing_api_key', \__('Claude API Key is missing.', 'ai-website-builder-unified'));
        }

        $model = isset($params['model_id']) ? $params['model_id'] : $this->model;

        $response = \wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'user', 'content' => $prompt),
                ),
                'system' => $params['system'] ?? 'You are a professional web designer.',
                'max_tokens' => isset($params['max_tokens']) ? intval($params['max_tokens']) : 4096,
                'temperature' => isset($params['temperature']) ? floatval($params['temperature']) : 0.7,
            )),
            'timeout' => 180,
        ));

        if (\is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(\wp_remote_retrieve_body($response), true);
        if (isset($body['error'])) {
            return new \WP_Error('claude_error', $body['error']['message']);
        }

        $content = $body['content'][0]['text'];
        
        // Extract token usage if available
        $prompt_tokens = isset($body['usage']['input_tokens']) ? intval($body['usage']['input_tokens']) : 0;
        $completion_tokens = isset($body['usage']['output_tokens']) ? intval($body['usage']['output_tokens']) : 0;
        $total_tokens = $prompt_tokens + $completion_tokens;
        
        // Calculate cost (Claude 3.5 Sonnet pricing: $3.00 per 1M input tokens, $15.00 per 1M output tokens)
        $cost = ($prompt_tokens / 1000000 * 3.00) + ($completion_tokens / 1000000 * 15.00);
        
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
