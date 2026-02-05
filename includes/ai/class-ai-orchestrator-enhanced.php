<?php
/**
 * AI Orchestrator Enhanced - منسق AI محسّن
 * 
 * يدعم:
 * - المراجع (References)
 * - Design Tokens
 * - Multiple AI Models
 * - Wrapper for AISBP\AI_Orchestrator
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_AI_Orchestrator {
    
    /**
     * Internal AISBP orchestrator instance
     * 
     * @var AISBP\AI_Orchestrator
     */
    private $orchestrator = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        // PERFORMANCE: Reduced logging in constructor (called frequently)
        // Only log errors, not every initialization
        $should_log = defined('WP_DEBUG') && WP_DEBUG && defined('AWBU_DEBUG_ORCHESTRATOR') && AWBU_DEBUG_ORCHESTRATOR;
        
        // Use the full-featured AISBP orchestrator if available
        // AISBP\AI_Orchestrator will handle its own dependencies internally
        if (class_exists('AISBP\AI_Orchestrator')) {
            try {
                $this->orchestrator = new \AISBP\AI_Orchestrator();
                if ($should_log) {
                    error_log('[AWBU ORCHESTRATOR] Initialized successfully: ' . get_class($this->orchestrator));
                }
            } catch (\Throwable $e) {
                // Always log errors
                error_log('AWBU: Failed to initialize AISBP\AI_Orchestrator: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                $this->orchestrator = null;
            } catch (\Exception $e) {
                // Always log errors
                error_log('AWBU: Failed to initialize AISBP\AI_Orchestrator: ' . $e->getMessage());
                $this->orchestrator = null;
            }
        }
    }
    
    /**
     * Generate with AI
     * 
     * @param array $params Generation parameters
     * @return array|WP_Error Result or error
     */
    public function generate($params) {
        // PERFORMANCE: Only enable detailed tracking if explicitly requested
        $should_track = defined('WP_DEBUG') && WP_DEBUG && defined('AWBU_DEBUG_GENERATE') && AWBU_DEBUG_GENERATE;
        
        if ($should_track) {
            $track_id = 'orchestrator_gen_' . time() . '_' . (function_exists('wp_generate_password') ? wp_generate_password(6, false) : uniqid());
            $log_step = function($step, $data = null) use ($track_id) {
                error_log(sprintf('[AWBU ORCHESTRATOR GEN TRACK %s] Step: %s | Data: %s', 
                    $track_id, 
                    $step,
                    $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : 'null'
                ));
            };
            $log_step('GENERATE_START', array('params_keys' => array_keys($params)));
        } else {
            $log_step = function($step, $data = null) {}; // No-op
        }
        
        // If we have the full orchestrator, use it
        if ($this->orchestrator !== null) {
            $log_step('USING_FULL_ORCHESTRATOR', array('class' => get_class($this->orchestrator)));
            try {
                $log_step('CALLING_ORCHESTRATOR_GENERATE');
                $result = $this->orchestrator->generate($params);
                $log_step('ORCHESTRATOR_GENERATE_RETURNED', array(
                    'is_wp_error' => is_wp_error($result),
                    'type' => gettype($result)
                ));
                return $result;
            } catch (\Throwable $e) {
                // Always log errors
                error_log('AWBU Orchestrator Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
                $log_step('ORCHESTRATOR_GENERATE_ERROR', array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ));
                return new \WP_Error('orchestrator_error', $e->getMessage(), array('status' => 500));
            } catch (\Exception $e) {
                // Always log errors
                error_log('AWBU Orchestrator Exception: ' . $e->getMessage());
                $log_step('ORCHESTRATOR_GENERATE_EXCEPTION', array('error' => $e->getMessage()));
                return new \WP_Error('orchestrator_error', $e->getMessage(), array('status' => 500));
            }
        }
        
        $log_step('USING_SIMPLE_FALLBACK');
        // Fallback to simple implementation
        return $this->generate_simple($params);
    }
    
    /**
     * Simple generation fallback
     */
    private function generate_simple($params) {
        // Validate parameters
        $validation = $this->validate_params($params);
        if (\is_wp_error($validation)) {
            return $validation;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($params);
        
        // Apply filters for design tokens and references
        $prompt = apply_filters('awbu_ai_build_prompt', $prompt, $params);
        
        // Get AI model
        $model = isset($params['model']) ? \sanitize_text_field($params['model']) : \get_option('awbu_default_model', 'deepseek');
        
        // Get connector
        $connector = $this->get_connector($model);
        if (\is_wp_error($connector)) {
            return $connector;
        }
        
        // Generate
        try {
            $result = $connector->generate($prompt, $params);
            
            if (\is_wp_error($result)) {
                return $result;
            }
            
            // Process result
            $processed = $this->process_result($result, $params);
            
            // Apply filters
            $processed = apply_filters('awbu_ai_process_result', $processed, $params);
            
            return $processed;
            
        } catch (\Throwable $e) {
            error_log('AWBU Simple Generation Error: ' . $e->getMessage());
            return new \WP_Error('generation_error', $e->getMessage(), array('status' => 500));
        } catch (\Exception $e) {
            error_log('AWBU Simple Generation Exception: ' . $e->getMessage());
            return new \WP_Error('generation_error', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Validate parameters
     */
    private function validate_params($params) {
        if (empty($params['description']) && empty($params['references'])) {
            return new \WP_Error(
                'missing_description',
                \__('Description or references are required.', 'ai-website-builder-unified'),
                array('status' => 400)
            );
        }
        
        return true;
    }
    
    /**
     * Build prompt
     */
    private function build_prompt($params) {
        $prompt = isset($params['description']) ? \sanitize_textarea_field($params['description']) : '';
        
        // Add mode instructions
        $mode = isset($params['creation_mode']) ? \sanitize_text_field($params['creation_mode']) : (isset($params['mode']) ? \sanitize_text_field($params['mode']) : 'full_site');
        $prompt .= "\n\nMode: {$mode}\n";
        
        return $prompt;
    }
    
    /**
     * Get AI connector
     */
    private function get_connector($model) {
        $connectors = array(
            'openai' => 'AWBU_OpenAI_Connector',
            'claude' => 'AWBU_Claude_Connector',
            'gemini' => 'AWBU_Gemini_Connector',
            'deepseek' => 'AWBU_DeepSeek_Connector',
        );
        
        $connector_class = isset($connectors[$model]) ? $connectors[$model] : 'AWBU_DeepSeek_Connector';
        
        if (class_exists($connector_class)) {
            return new $connector_class();
        }
        
        return new \WP_Error('connector_not_found', \__('AI connector not found.', 'ai-website-builder-unified'));
    }
    
    /**
     * Process result
     */
    private function process_result($result, $params) {
        $code = isset($result['code']) ? $result['code'] : '';
        $html = isset($result['html']) ? $result['html'] : $code;
        $css = isset($result['css']) ? $result['css'] : '';
        $js = isset($result['js']) ? $result['js'] : '';
        $content = isset($result['content']) ? $result['content'] : '';
        
        // Build full code if not present
        $full_code = $html;
        if (!empty($css)) {
            $full_code = "<style>\n{$css}\n</style>\n" . $full_code;
        }
        if (!empty($js)) {
            $full_code .= "\n<script>\n{$js}\n</script>";
        }
        
        return array(
            'success' => true,
            'code' => $code,
            'html' => $html,
            'css' => $css,
            'js' => $js,
            'content' => $content,
            'full_code' => $full_code,
            'phases' => array(
                $params['phase'] ?? 1 => array(
                    'code' => $full_code,
                    'status' => 'completed'
                )
            )
        );
    }
    
    /**
     * Chat with AI for modifications
     * 
     * @param array $params Chat parameters
     * @return array|WP_Error Response or error
     */
    public function chat($params) {
        // If we have the full orchestrator, use it
        if ($this->orchestrator !== null && method_exists($this->orchestrator, 'chat')) {
            try {
                return $this->orchestrator->chat($params);
            } catch (\Throwable $e) {
                error_log('AWBU Orchestrator Chat Error: ' . $e->getMessage());
                return new \WP_Error('chat_error', $e->getMessage(), array('status' => 500));
            } catch (\Exception $e) {
                error_log('AWBU Orchestrator Chat Exception: ' . $e->getMessage());
                return new \WP_Error('chat_error', $e->getMessage(), array('status' => 500));
            }
        }
        
        // Fallback: use generate with chat-like behavior
        $chat_params = array(
            'description' => isset($params['message']) ? $params['message'] : (isset($params['prompt']) ? $params['prompt'] : ''),
            'mode' => 'chat',
            'model' => isset($params['model']) ? $params['model'] : 'deepseek',
            'context' => isset($params['context']) ? $params['context'] : '',
            'page_id' => isset($params['page_id']) ? $params['page_id'] : null,
        );
        
        return $this->generate($chat_params);
    }
    
    /**
     * Get available models
     * 
     * @return array Models info
     */
    public function get_available_models() {
        if ($this->orchestrator !== null && method_exists($this->orchestrator, 'get_available_models')) {
            return $this->orchestrator->get_available_models();
        }
        
        return array(
            'deepseek' => array('name' => 'DeepSeek', 'available' => true),
            'openai' => array('name' => 'OpenAI GPT-4', 'available' => true),
            'claude' => array('name' => 'Anthropic Claude', 'available' => true),
            'gemini' => array('name' => 'Google Gemini', 'available' => true),
        );
    }
}
