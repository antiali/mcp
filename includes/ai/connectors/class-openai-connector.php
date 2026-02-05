<?php
/**
 * OpenAI Connector - رابط OpenAI
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_OpenAI_Connector {
    
    private $api_key;
    private $model = 'gpt-4o';

    public function __construct() {
        // CRITICAL FIX: Read from both option names and individual options for maximum compatibility
        $awbu_keys = \get_option('awbu_api_keys', array());
        $aisbp_keys = \get_option('aisbp_api_keys', array());
        
        // Merge: awbu_api_keys takes priority, then aisbp_api_keys fills gaps
        $api_keys = array_merge($aisbp_keys, $awbu_keys);
        
        $this->api_key = isset($api_keys['openai']) ? $api_keys['openai'] : '';
        
        // Fallback to individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('awbu_openai_api_key', '');
        }
        
        // Final fallback to AISBP individual option
        if (empty($this->api_key)) {
            $this->api_key = \get_option('aisbp_openai_api_key', '');
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
            return new \WP_Error('missing_api_key', \__('OpenAI API Key is missing.', 'ai-website-builder-unified'));
        }

        $model = isset($params['model']) && $params['model'] === 'openai' ? 'gpt-4o' : (isset($params['model_id']) ? $params['model_id'] : $this->model);

        $response = \wp_remote_post('https://api.openai.com/v1/chat/completions', array(
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
            return $response;
        }

        $body = json_decode(\wp_remote_retrieve_body($response), true);
        if (isset($body['error'])) {
            return new \WP_Error('openai_error', $body['error']['message']);
        }

        $content = $body['choices'][0]['message']['content'];
        
        // Extract token usage if available
        $prompt_tokens = isset($body['usage']['prompt_tokens']) ? intval($body['usage']['prompt_tokens']) : 0;
        $completion_tokens = isset($body['usage']['completion_tokens']) ? intval($body['usage']['completion_tokens']) : 0;
        $total_tokens = isset($body['usage']['total_tokens']) ? intval($body['usage']['total_tokens']) : ($prompt_tokens + $completion_tokens);
        
        // Calculate cost (GPT-4o pricing: $2.50 per 1M input tokens, $10.00 per 1M output tokens)
        $cost = ($prompt_tokens / 1000000 * 2.50) + ($completion_tokens / 1000000 * 10.00);
        
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
