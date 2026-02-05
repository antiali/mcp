<?php
/**
 * Integration Layer - طبقة التكامل الموحدة
 * 
 * تربط بين:
 * - Design System
 * - AI Orchestrator
 * - MCP Server
 * - Remote Design Manager
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Integration_Layer {
    
    /**
     * Design System instance
     */
    private $design_system;
    
    /**
     * AI Orchestrator instance
     */
    private $ai_orchestrator;
    
    /**
     * MCP Server instance
     */
    private $mcp_server;
    
    /**
     * Remote Design Manager instance
     */
    private $remote_design_manager;
    
    /**
     * Constructor
     */
    public function __construct($design_system, $ai_orchestrator, $mcp_server, $remote_design_manager) {
        $this->design_system = $design_system;
        $this->ai_orchestrator = $ai_orchestrator;
        $this->mcp_server = $mcp_server;
        $this->remote_design_manager = $remote_design_manager;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Design System → AI Integration
        add_filter('awbu_ai_build_prompt', array($this, 'inject_design_tokens'), 10, 2);
        add_filter('awbu_ai_process_result', array($this, 'apply_design_system_to_result'), 10, 2);
        
        // References → AI Integration
        add_filter('awbu_ai_build_prompt', array($this, 'inject_references'), 20, 2);
        
        // Design System → MCP Integration
        add_action('awbu_design_system_updated', array($this, 'notify_mcp'), 10, 1);
        
        // Remote Design → All Systems
        add_action('awbu_remote_design_complete', array($this, 'sync_all_systems'), 10, 1);
    }
    
    /**
     * Inject design tokens into AI prompts
     */
    public function inject_design_tokens($prompt, $params = array()) {
        $builder = AWBU_Builder_Detector::detect();
        $adapter = AWBU_Adapter_Factory::create($builder);
        
        $colors = $adapter->get_colors();
        $variables = $adapter->get_variables();
        
        if (!empty($colors) || !empty($variables)) {
            $prompt .= "\n\n=== AVAILABLE DESIGN SYSTEM TOKENS ===\n";
            $prompt .= wp_json_encode(array(
                'colors' => $colors,
                'variables' => $variables,
            ), JSON_PRETTY_PRINT);
            $prompt .= "\n\nIMPORTANT: Use these tokens instead of hardcoded values!\n";
        }
        
        return $prompt;
    }
    
    /**
     * Inject references into AI prompts
     */
    public function inject_references($prompt, $params = array()) {
        if (empty($params['processed_references'])) {
            return $prompt;
        }
        
        $reference_processor = new AWBU_Reference_Processor();
        return $reference_processor->enhance_prompt($prompt, $params['processed_references']);
    }
    
    /**
     * Apply design system to AI result
     */
    public function apply_design_system_to_result($result, $params = array()) {
        if (!isset($result['code']) || empty($result['code'])) {
            return $result;
        }
        
        // Validate code
        $validation = AWBU_Validator::validate_code($result['code']);
        if (is_wp_error($validation)) {
            return $result;
        }
        
        // Replace hardcoded values with tokens
        $builder = AWBU_Builder_Detector::detect();
        $adapter = AWBU_Adapter_Factory::create($builder);
        $colors = $adapter->get_colors();
        
        if (!empty($colors)) {
            $code = $result['code'];
            foreach ($colors as $gcid => $data) {
                $hex = is_array($data) ? $data['color'] : $data;
                if (!empty($hex)) {
                    $code = preg_replace(
                        '/["\']' . preg_quote($hex, '/') . '["\']/i',
                        "'{$gcid}'",
                        $code
                    );
                }
            }
            $result['code'] = $code;
        }
        
        return $result;
    }
    
    /**
     * Notify MCP when design system is updated
     */
    public function notify_mcp($data) {
        // Trigger MCP sync
        do_action('awbu_mcp_sync_design_system', $data);
    }
    
    /**
     * Sync all systems after remote design
     */
    public function sync_all_systems($data) {
        // Sync design system
        if (isset($data['design_info'])) {
            $builder = AWBU_Builder_Detector::detect();
            $adapter = AWBU_Adapter_Factory::create($builder);
            $this->design_system->update_design_system(
                $data['design_info']['colors'] ?? array(),
                $data['design_info']['variables'] ?? array(),
                $adapter
            );
        }
        
        // Clear all caches
        $adapter = AWBU_Adapter_Factory::create(AWBU_Builder_Detector::detect());
        $adapter->clear_cache();
    }
}

