<?php
/**
 * GLM Connector - رابط GLM من zero.ai
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class AWBU_GLM_Connector {

    /**
     * API Base URL
     */
    private $api_base = 'https://open.bigmodel.cn/api/paas/v4/chat/completions';

    /**
     * API Models Endpoint
     */
    private $models_endpoint = 'https://open.bigmodel.cn/api/paas/v4/models';

    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = $this->get_api_key();
    }

    /**
     * Get API Key
     */
    private function get_api_key() {
        // Check both option formats
        $api_keys = get_option('awbu_api_keys', array());
        
        if (isset($api_keys['glm'])) {
            return $api_keys['glm'];
        }
        
        // Check individual option
        $individual_key = get_option('awbu_glm_api_key', '');
        if (!empty($individual_key)) {
            return $individual_key;
        }
        
        return '';
    }

    /**
     * Get Available Models
     */
    public function get_models() {
        $response = wp_remote_get($this->models_endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('glm_request_failed', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('glm_invalid_response', 'Invalid JSON response from GLM API');
        }

        // Extract models
        $models = array();
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $model_data) {
                if (isset($model_data['id']) && strpos($model_data['id'], 'glm') !== false) {
                    $models[] = array(
                        'id' => $model_data['id'],
                        'name' => $model_data['id'],
                        'type' => isset($model_data['type']) ? $model_data['type'] : 'chat',
                        'max_tokens' => isset($model_data['max_tokens']) ? $model_data['max_tokens'] : 4096,
                    );
                }
            }
        }

        // Return common GLM models if API fails
        if (empty($models)) {
            $models = array(
                array(
                    'id' => 'glm-4',
                    'name' => 'GLM-4',
                    'type' => 'chat',
                    'max_tokens' => 4096,
                    'description' => 'General Language Model 4 - 128K',
                ),
                array(
                    'id' => 'glm-4-flash',
                    'name' => 'GLM-4-Flash',
                    'type' => 'chat',
                    'max_tokens' => 8192,
                    'description' => 'GLM-4 Flash - faster inference',
                ),
                array(
                    'id' => 'glm-4-air',
                    'name' => 'GLM-4-Air',
                    'type' => 'chat',
                    'max_tokens' => 32768,
                    'description' => 'GLM-4 Air - longest context',
                ),
                array(
                    'id' => 'glm-3-turbo',
                    'name' => 'GLM-3-Turbo',
                    'type' => 'chat',
                    'max_tokens' => 8192,
                    'description' => 'GLM-3 Turbo - fast and cost-effective',
                ),
            );
        }

        return $models;
    }

    /**
     * Chat Completion
     */
    public function chat_completion($params) {
        $model = isset($params['model']) ? $params['model'] : 'glm-4-flash';
        $messages = isset($params['messages']) ? $params['messages'] : array();
        $temperature = isset($params['temperature']) ? floatval($params['temperature']) : 0.7;
        $max_tokens = isset($params['max_tokens']) ? intval($params['max_tokens']) : 2048;
        $top_p = isset($params['top_p']) ? floatval($params['top_p']) : 1;
        $stream = isset($params['stream']) ? (bool) $params['stream'] : false;

        // Build request payload
        $payload = array(
            'model' => $model,
            'messages' => $this->format_messages($messages),
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'top_p' => $top_p,
            'stream' => $stream,
        );

        // Send request
        $response = wp_remote_post($this->api_base, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($payload),
            'timeout' => 60,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('glm_request_failed', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : "HTTP Error: {$code}";
            
            return new WP_Error('glm_api_error', $error_message);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('glm_invalid_response', 'Invalid JSON response from GLM API');
        }

        // Parse response
        $result = array(
            'success' => true,
            'model' => $model,
            'usage' => isset($data['usage']) ? $data['usage'] : array(),
        );

        // Handle streaming response
        if (isset($data['choices']) && !empty($data['choices'])) {
            $choice = $data['choices'][0];
            
            $result['content'] = isset($choice['message']['content']) 
                ? $choice['message']['content'] 
                : '';
            
            $result['finish_reason'] = isset($choice['finish_reason']) 
                ? $choice['finish_reason'] 
                : 'stop';
            
            $result['tokens_used'] = isset($choice['message']['content_tokens'])
                ? $choice['message']['content_tokens'] + (isset($choice['message']['prompt_tokens']) ? $choice['message']['prompt_tokens'] : 0)
                : 0;
        }

        return $result;
    }

    /**
     * Format Messages for GLM API
     */
    private function format_messages($messages) {
        $formatted = array();

        foreach ($messages as $message) {
            $formatted_message = array();

            if (is_array($message)) {
                // GLM expects 'role' and 'content'
                $role = isset($message['role']) ? $message['role'] : 'user';
                $content = isset($message['content']) ? $message['content'] : '';

                // Map common roles to GLM roles
                $role_map = array(
                    'system' => 'system',
                    'user' => 'user',
                    'assistant' => 'assistant',
                );

                if (isset($role_map[$role])) {
                    $formatted_message['role'] = $role_map[$role];
                    $formatted_message['content'] = $content;
                }
            } elseif (is_string($message)) {
                $formatted_message['role'] = 'user';
                $formatted_message['content'] = $message;
            }

            if (!empty($formatted_message)) {
                $formatted[] = $formatted_message;
            }
        }

        return $formatted;
    }

    /**
     * Test API Key
     */
    public function test_api_key($api_key) {
        $response = wp_remote_get($this->models_endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'error' => $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 200 || $code === 401) {
            // 200 = valid, 401 = key exists but no access to models
            return array(
                'valid' => true,
                'message' => 'API key is valid',
            );
        } else {
            return array(
                'valid' => false,
                'error' => "HTTP Error: {$code}",
            );
        }
    }

    /**
     * Get Model Info
     */
    public function get_model_info($model_id = 'glm-4-flash') {
        $models = $this->get_models();

        if (is_wp_error($models)) {
            return new WP_Error('glm_models_failed', $models->get_error_message());
        }

        foreach ($models as $model) {
            if ($model['id'] === $model_id) {
                return $model;
            }
        }

        // Return default model if not found
        return array(
            'id' => 'glm-4-flash',
            'name' => 'GLM-4-Flash',
            'type' => 'chat',
            'max_tokens' => 2048,
            'description' => 'GLM-4 Flash - fast and efficient',
        );
    }

    /**
     * Embedding (Optional - for advanced features)
     */
    public function get_embedding($text) {
        $endpoint = 'https://open.bigmodel.cn/api/paas/v4/embeddings';
        
        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'embedding-2',
                'input' => $text,
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('glm_embedding_failed', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('glm_invalid_response', 'Invalid JSON response');
        }

        if (isset($data['data'][0]['embedding'])) {
            return array(
                'embedding' => $data['data'][0]['embedding'],
                'usage' => isset($data['usage']) ? $data['usage'] : array(),
            );
        }

        return new WP_Error('glm_no_embedding', 'No embedding returned');
    }
}
