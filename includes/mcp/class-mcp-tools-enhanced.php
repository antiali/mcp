<?php
/**
 * MCP Tools Enhanced - أدوات MCP محسّنة للتصميم عن بُعد
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_MCP_Tools_Enhanced {
    
    /**
     * Get all tool definitions
     */
    public function get_tool_definitions() {
        $tools = array();
        
        // ============ DESIGN SYSTEM TOOLS ============
        $tools[] = array(
            'name' => 'awbu_get_design_system',
            'description' => 'Get current design system (colors and variables)',
            'parameters' => array()
        );
        
        $tools[] = array(
            'name' => 'awbu_update_design_system',
            'description' => 'Update design system (colors and variables)',
            'parameters' => array(
                'colors' => array('type' => 'object', 'required' => false),
                'variables' => array('type' => 'object', 'required' => false),
            )
        );
        
        // ============ REMOTE DESIGN TOOLS ============
        $tools[] = array(
            'name' => 'awbu_remote_design',
            'description' => 'Design website remotely with references (links, images, files)',
            'parameters' => array(
                'description' => array('type' => 'string', 'required' => true),
                'references' => array(
                    'type' => 'array',
                    'required' => false,
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'type' => array('type' => 'string', 'enum' => array('url', 'image', 'file', 'text')),
                            'url' => array('type' => 'string'),
                            'title' => array('type' => 'string'),
                            'description' => array('type' => 'string'),
                            'content' => array('type' => 'string'),
                        )
                    )
                ),
                'model' => array('type' => 'string', 'required' => false),
                'mode' => array('type' => 'string', 'required' => false),
            )
        );
        
        $tools[] = array(
            'name' => 'awbu_process_references',
            'description' => 'Process references (links, images, files) and extract design information',
            'parameters' => array(
                'references' => array(
                    'type' => 'array',
                    'required' => true,
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'type' => array('type' => 'string', 'enum' => array('url', 'image', 'file', 'text')),
                            'url' => array('type' => 'string'),
                            'title' => array('type' => 'string'),
                            'description' => array('type' => 'string'),
                            'content' => array('type' => 'string'),
                        )
                    )
                )
            )
        );
        
        // ============ AI GENERATION TOOLS ============
        $tools[] = array(
            'name' => 'awbu_generate_with_references',
            'description' => 'Generate website content with AI using references',
            'parameters' => array(
                'description' => array('type' => 'string', 'required' => true),
                'references' => array('type' => 'array', 'required' => false),
                'model' => array('type' => 'string', 'required' => false),
                'mode' => array('type' => 'string', 'required' => false),
            )
        );
        
        // ============ PAGE BUILDER TOOLS ============
        $tools[] = array(
            'name' => 'awbu_create_page_with_design',
            'description' => 'Create page with design system applied. Supports Divi 5 JSON format. Can read from file_path or use content directly.',
            'parameters' => array(
                'title' => array('type' => 'string', 'required' => true, 'description' => 'Page title'),
                'content' => array('type' => 'string', 'required' => false, 'description' => 'Page content (HTML or Divi 5 JSON). Optional if file_path is provided.'),
                'file_path' => array('type' => 'string', 'required' => false, 'description' => 'Path to JSON file to read content from. Can be relative to plugin directory, WordPress root, or absolute path.'),
                'apply_design_system' => array('type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Apply design system colors and variables'),
            )
        );
        
        // ============ NEW: COMPREHENSIVE REMOTE DESIGN TOOLS ============
        
        // 1. Extract design from reference URL
        $tools[] = array(
            'name' => 'awbu_analyze_reference_url',
            'description' => 'Analyze a reference URL to extract colors, fonts, and layout information for design inspiration',
            'parameters' => array(
                'url' => array('type' => 'string', 'required' => true, 'description' => 'The URL to analyze'),
                'extract_colors' => array('type' => 'boolean', 'required' => false, 'default' => true),
                'extract_content' => array('type' => 'boolean', 'required' => false, 'default' => true),
            )
        );
        
        // 2. Extract colors from reference image
        $tools[] = array(
            'name' => 'awbu_extract_colors_from_image',
            'description' => 'Extract color palette from a reference image for design inspiration',
            'parameters' => array(
                'image_url' => array('type' => 'string', 'required' => true, 'description' => 'The image URL'),
                'num_colors' => array('type' => 'integer', 'required' => false, 'default' => 5),
            )
        );
        
        // 3. Full website design with all references
        $tools[] = array(
            'name' => 'awbu_design_full_website',
            'description' => 'Design a complete website using multiple references (URLs, images, files, text). Returns Divi 5 compatible code.',
            'parameters' => array(
                'site_name' => array('type' => 'string', 'required' => true, 'description' => 'Website name'),
                'site_description' => array('type' => 'string', 'required' => true, 'description' => 'Detailed description'),
                'references' => array(
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Reference materials (URLs, images, files)',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'type' => array('type' => 'string', 'enum' => array('url', 'image', 'file', 'text')),
                            'url' => array('type' => 'string'),
                            'title' => array('type' => 'string'),
                            'description' => array('type' => 'string'),
                            'content' => array('type' => 'string'),
                        )
                    )
                ),
                'pages' => array(
                    'type' => 'array',
                    'required' => false,
                    'default' => array('home'),
                    'description' => 'Pages to create (home, about, services, contact, etc.)'
                ),
                'model' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'deepseek',
                    'description' => 'AI model to use (deepseek, gpt-4o, claude, gemini)'
                ),
            )
        );
        
        // 4. Apply design system from reference
        $tools[] = array(
            'name' => 'awbu_apply_design_from_reference',
            'description' => 'Apply design system (colors, variables) extracted from a reference to the current site',
            'parameters' => array(
                'reference_url' => array('type' => 'string', 'required' => true),
                'apply_colors' => array('type' => 'boolean', 'required' => false, 'default' => true),
                'apply_fonts' => array('type' => 'boolean', 'required' => false, 'default' => true),
            )
        );
        
        // 5. Get available AI models
        $tools[] = array(
            'name' => 'awbu_get_available_models',
            'description' => 'Get list of available AI models and their status',
            'parameters' => array()
        );
        
        // 6. Sync design system
        $tools[] = array(
            'name' => 'awbu_sync_design_system',
            'description' => 'Sync all design system variables and colors to ensure they are applied correctly',
            'parameters' => array()
        );
        
        // ============ CODE ANALYSIS & DEBUGGING TOOLS

// ==================== YMCP PERSONAL ASSISTANT TOOLS ====================

// Dashboard Tools
$tools[] = array(
    'name' => 'ymcp_get_dashboard',
    'description' => 'Get YMCP dashboard summary with site health, stats, and alerts',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_site_info',
    'description' => 'Get comprehensive site information',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_tasks',
    'description' => 'Get pending tasks and reminders',
    'parameters' => array()
);

// Analytics Tools
$tools[] = array(
    'name' => 'ymcp_get_analytics',
    'description' => 'Get analytics data (visitors, page views, etc.)',
    'parameters' => array(
        'start_date' => array('type' => 'string', 'required' => false),
        'end_date' => array('type' => 'string', 'required' => false),
        'limit' => array('type' => 'integer', 'required' => false, 'default' => 1000),
    )
);

$tools[] = array(
    'name' => 'ymcp_generate_report',
    'description' => 'Generate analytics report (json, html, csv)',
    'parameters' => array(
        'format' => array('type' => 'string', 'required' => false, 'default' => 'json', 'enum' => array('json', 'html', 'csv')),
    )
);

$tools[] = array(
    'name' => 'ymcp_clear_analytics',
    'description' => 'Clear analytics data before specific date',
    'parameters' => array(
        'before_date' => array('type' => 'string', 'required' => false),
    )
);

// Security Tools
$tools[] = array(
    'name' => 'ymcp_security_scan',
    'description' => 'Run comprehensive security scan',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_security_report',
    'description' => 'Get last security scan report',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_create_backup',
    'description' => 'Create full site backup',
    'parameters' => array(
        'backup_name' => array('type' => 'string', 'required' => false),
    )
);

$tools[] = array(
    'name' => 'ymcp_restore_backup',
    'description' => 'Restore site from backup',
    'parameters' => array(
        'backup_id' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_list_backups',
    'description' => 'List all available backups',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_delete_backup',
    'description' => 'Delete a backup',
    'parameters' => array(
        'backup_id' => array('type' => 'string', 'required' => true),
    )
);

// Communication Tools
$tools[] = array(
    'name' => 'ymcp_send_email',
    'description' => 'Send email notification',
    'parameters' => array(
        'to' => array('type' => 'string', 'required' => true),
        'subject' => array('type' => 'string', 'required' => true),
        'message' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_chatbot_query',
    'description' => 'Query AI chatbot',
    'parameters' => array(
        'question' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_train_chatbot',
    'description' => 'Train chatbot on site data',
    'parameters' => array()
);

// E-commerce Tools
$tools[] = array(
    'name' => 'ymcp_create_product',
    'description' => 'Create WooCommerce product with AI',
    'parameters' => array(
        'name' => array('type' => 'string', 'required' => true),
        'description' => array('type' => 'string', 'required' => true),
        'price' => array('type' => 'number', 'required' => false),
    )
);

$tools[] = array(
    'name' => 'ymcp_update_inventory',
    'description' => 'Update product inventory',
    'parameters' => array(
        'product_id' => array('type' => 'integer', 'required' => true),
        'stock_quantity' => array('type' => 'integer', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_get_sales_report',
    'description' => 'Get sales and revenue report',
    'parameters' => array(
        'start_date' => array('type' => 'string', 'required' => false),
        'end_date' => array('type' => 'string', 'required' => false),
    )
);

// Automation Tools
$tools[] = array(
    'name' => 'ymcp_create_workflow',
    'description' => 'Create automation workflow',
    'parameters' => array(
        'name' => array('type' => 'string', 'required' => true),
        'trigger' => array('type' => 'string', 'required' => true),
        'actions' => array('type' => 'array', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_list_workflows',
    'description' => 'List all automation workflows',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_trigger_workflow',
    'description' => 'Trigger a workflow manually',
    'parameters' => array(
        'workflow_id' => array('type' => 'string', 'required' => true),
    )
);
 (Based on WordPress MCP) ============
        
        // 7. Detect code errors and issues
        $tools[] = array(
            'name' => 'awbu_detect_code_errors',
            'description' => 'Detect syntax errors, security issues, and code problems in PHP/JavaScript/CSS files',
            'parameters' => array(
                'file_path' => array('type' => 'string', 'required' => false, 'description' => 'Specific file path to check (optional, checks all if not provided)'),
                'check_types' => array(
                    'type' => 'array',
                    'required' => false,
                    'default' => array('syntax', 'security', 'performance', 'standards'),
                    'description' => 'Types of checks to perform',
                    'items' => array('type' => 'string', 'enum' => array('syntax', 'security', 'performance', 'standards', 'deprecated'))
                ),
            )
        );
        
        // 8. Search and replace in files
        $tools[] = array(
            'name' => 'awbu_search_replace_files',
            'description' => 'Search and replace text in files (supports regex, multiple files, backup)',
            'parameters' => array(
                'search' => array('type' => 'string', 'required' => true, 'description' => 'Text or regex pattern to search for'),
                'replace' => array('type' => 'string', 'required' => true, 'description' => 'Replacement text'),
                'file_pattern' => array('type' => 'string', 'required' => false, 'description' => 'File pattern (e.g., "*.php", "includes/**/*.js")'),
                'use_regex' => array('type' => 'boolean', 'required' => false, 'default' => false, 'description' => 'Use regex pattern'),
                'create_backup' => array('type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Create backup before replacement'),
                'dry_run' => array('type' => 'boolean', 'required' => false, 'default' => false, 'description' => 'Preview changes without applying'),
            )
        );
        
        // 9. Search and replace in database
        $tools[] = array(
            'name' => 'awbu_search_replace_database',
            'description' => 'Search and replace text in WordPress database (posts, options, meta, etc.) with safety checks',
            'parameters' => array(
                'search' => array('type' => 'string', 'required' => true, 'description' => 'Text to search for'),
                'replace' => array('type' => 'string', 'required' => true, 'description' => 'Replacement text'),
                'tables' => array(
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Specific tables to search (default: all WordPress tables)',
                    'items' => array('type' => 'string')
                ),
                'columns' => array(
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Specific columns to search (default: all text columns)',
                    'items' => array('type' => 'string')
                ),
                'create_backup' => array('type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Create database backup before replacement'),
                'dry_run' => array('type' => 'boolean', 'required' => false, 'default' => false, 'description' => 'Preview changes without applying'),
            )
        );
        
        // 10. Design single page
        $tools[] = array(
            'name' => 'awbu_design_page',
            'description' => 'Design or modify a single page with AI. Can create new page or update existing one.',
            'parameters' => array(
                'page_id' => array('type' => 'integer', 'required' => false, 'description' => 'Existing page ID to modify (creates new if not provided)'),
                'title' => array('type' => 'string', 'required' => true, 'description' => 'Page title'),
                'description' => array('type' => 'string', 'required' => true, 'description' => 'Detailed description of what to design'),
                'design_style' => array('type' => 'string', 'required' => false, 'default' => 'modern', 'enum' => array('modern', 'classic', 'minimal', 'bold', 'elegant')),
                'apply_design_system' => array('type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Apply current design system'),
                'model' => array('type' => 'string', 'required' => false, 'default' => 'deepseek'),
            )
        );
        
        // 11. Modify existing design
        $tools[] = array(
            'name' => 'awbu_modify_design',
            'description' => 'Modify existing page design based on natural language instructions',
            'parameters' => array(
                'page_id' => array('type' => 'integer', 'required' => true, 'description' => 'Page ID to modify'),
                'instructions' => array('type' => 'string', 'required' => true, 'description' => 'Natural language instructions for modifications'),
                'model' => array('type' => 'string', 'required' => false, 'default' => 'deepseek'),
            )
        );
        
        // 12. List WordPress pages
        $tools[] = array(
            'name' => 'awbu_list_pages',
            'description' => 'Get list of all WordPress pages with their IDs, titles, and slugs',
            'parameters' => array(
                'status' => array('type' => 'string', 'required' => false, 'default' => 'any', 'description' => 'Page status (publish, draft, any)'),
                'limit' => array('type' => 'integer', 'required' => false, 'default' => 50, 'description' => 'Maximum number of pages to return'),
            )
        );
        
        // 13. Delete WordPress page
        $tools[] = array(
            'name' => 'awbu_delete_page',
            'description' => 'Delete a WordPress page by ID',
            'parameters' => array(
                'page_id' => array('type' => 'integer', 'required' => true, 'description' => 'Page ID to delete'),
                'force' => array('type' => 'boolean', 'required' => false, 'default' => false, 'description' => 'Force delete (skip trash)'),
            )
        );
        
        // 14. Update WordPress page content
        $tools[] = array(
            'name' => 'awbu_update_page',
            'description' => 'Update existing WordPress page content directly in database',
            'parameters' => array(
                'page_id' => array('type' => 'integer', 'required' => true, 'description' => 'Page ID to update'),
                'title' => array('type' => 'string', 'required' => false, 'description' => 'Page title'),
                'content' => array('type' => 'string', 'required' => false, 'description' => 'Page content (HTML or Gutenberg blocks)'),
                'status' => array('type' => 'string', 'required' => false, 'description' => 'Page status (publish, draft, etc.)'),
            )
        );
        
        // 15. Inject multiple pages from files
        $tools[] = array(
            'name' => 'awbu_inject_pages_batch',
            'description' => 'Inject multiple pages from HTML files in batch. Automatically enqueues CSS/JS assets.',
            'parameters' => array(
                'pages' => array(
                    'type' => 'array',
                    'required' => true,
                    'description' => 'Array of pages with title, slug, and file_path',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'title' => array('type' => 'string'),
                            'slug' => array('type' => 'string'),
                            'file_path' => array('type' => 'string'),
                        )
                    )
                ),
                'delete_unused' => array('type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Delete pages not in the list'),
                'set_homepage' => array('type' => 'string', 'required' => false, 'description' => 'Slug of page to set as homepage'),
            )
        );
        
        // 16. Cleanup and manage pages
        $tools[] = array(
            'name' => 'awbu_manage_pages',
            'description' => 'Delete unused pages and create/update required pages. Perfect for cleaning up and setting up site structure.',
            'parameters' => array(
                'action' => array('type' => 'string', 'required' => false, 'default' => 'cleanup', 'enum' => array('cleanup', 'list', 'delete_all_unused'), 'description' => 'Action to perform'),
                'required_pages' => array(
                    'type' => 'array',
                    'required' => false,
                    'description' => 'Array of required pages with title, slug, and content',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'title' => array('type' => 'string'),
                            'slug' => array('type' => 'string'),
                            'content' => array('type' => 'string'),
                        )
                    )
                ),
            )
        );
        
        // 16. List files for search
        $tools[] = array(
            'name' => 'awbu_list_files',
            'description' => 'List files matching pattern for search/replace operations',
            'parameters' => array(
                'pattern' => array('type' => 'string', 'required' => false, 'default' => '*', 'description' => 'File pattern (e.g., "*.php", "includes/**/*.js")'),
                'directory' => array('type' => 'string', 'required' => false, 'description' => 'Directory to search (default: plugin directory)'),
            )
        );
        
        // FIXED: Convert parameters to proper JSON Schema format for MCP
        // MCP requires 'inputSchema' with type:object, not a raw array
        return array_map(function($tool) {
            $properties = array();
            $required = array();
            
            if (!empty($tool['parameters']) && is_array($tool['parameters'])) {
                foreach ($tool['parameters'] as $param_name => $param_def) {
                    // Convert our simple format to JSON Schema
                    $prop = array(
                        'type' => isset($param_def['type']) ? $param_def['type'] : 'string',
                    );
                    if (isset($param_def['description'])) {
                        $prop['description'] = $param_def['description'];
                    }
                    if (isset($param_def['default'])) {
                        $prop['default'] = $param_def['default'];
                    }
                    if (isset($param_def['enum'])) {
                        $prop['enum'] = $param_def['enum'];
                    }
                    if (isset($param_def['items'])) {
                        $prop['items'] = $param_def['items'];
                    }
                    $properties[$param_name] = $prop;
                    
                    // Track required fields
                    if (isset($param_def['required']) && $param_def['required'] === true) {
                        $required[] = $param_name;
                    }
                }
            }
            
            // Build proper JSON Schema for inputSchema
            $input_schema = array(
                'type' => 'object',
                'properties' => !empty($properties) ? $properties : new \stdClass(), // {} not []
            );
            
            if (!empty($required)) {
                $input_schema['required'] = $required;
            }
            
            return array(
                'name' => $tool['name'],
                'description' => $tool['description'],
                'inputSchema' => $input_schema,
            );
        }, $tools);
    }
    
    /**
     * Execute tool
     */
    public function execute_tool($tool_name, $params) {
        switch ($tool_name) {
            case 'awbu_get_design_system':
                return $this->get_design_system($params);
                
            case 'awbu_update_design_system':
                return $this->update_design_system($params);
                
            case 'awbu_remote_design':
                return $this->remote_design($params);
                
            case 'awbu_process_references':
                return $this->process_references($params);
                
            case 'awbu_generate_with_references':
                return $this->generate_with_references($params);
                
            case 'awbu_create_page_with_design':
                return $this->create_page_with_design($params);
            
            // NEW TOOLS
            case 'awbu_analyze_reference_url':
                return $this->analyze_reference_url($params);
                
            case 'awbu_extract_colors_from_image':
                return $this->extract_colors_from_image($params);
                
            case 'awbu_design_full_website':
                return $this->design_full_website($params);
                
            case 'awbu_apply_design_from_reference':
                return $this->apply_design_from_reference($params);
                
            case 'awbu_get_available_models':
                return $this->get_available_models($params);
                
            case 'awbu_sync_design_system':
                return $this->sync_design_system($params);
            
            // NEW: Code Analysis & Debugging Tools
            case 'awbu_detect_code_errors':
                return $this->detect_code_errors($params);
                
            case 'awbu_search_replace_files':
                return $this->search_replace_files($params);
                
            case 'awbu_search_replace_database':
                return $this->search_replace_database($params);
                
            case 'awbu_design_page':
                return $this->design_page($params);
                
            case 'awbu_modify_design':
                return $this->modify_design($params);
                
            case 'awbu_list_files':
                return $this->list_files($params);
            
            case 'awbu_list_pages':
                return $this->list_pages($params);
            
            case 'awbu_delete_page':
                return $this->delete_page($params);
            
            case 'awbu_update_page':
                return $this->update_page($params);
            
            case 'awbu_inject_pages_batch':
                return $this->inject_pages_batch($params);
            
            default:
                error_log('AWBU MCP: Unknown tool requested: ' . $tool_name);
                return new \WP_Error('unknown_tool', \sprintf(\__('Unknown tool: %s', 'ai-website-builder-unified'), $tool_name), array('status' => 404));
        }
    }
    
    /**
     * Get design system
     */
    private function get_design_system($params) {
        $adapter = $this->get_adapter();
        if (\is_wp_error($adapter)) {
            return $adapter;
        }
        
        return array(
            'success' => true,
            'builder' => AWBU_Builder_Detector::detect(),
            'colors' => $adapter->get_colors(),
            'variables' => $adapter->get_variables(),
        );
    }
    
    /**
     * Update design system
     */
    private function update_design_system($params) {
        $colors = isset($params['colors']) ? AWBU_Validator::validate_colors($params['colors']) : array();
        if (\is_wp_error($colors)) {
            return $colors;
        }
        
        $variables = isset($params['variables']) ? AWBU_Validator::validate_variables($params['variables']) : array();
        if (\is_wp_error($variables)) {
            return $variables;
        }
        
        $adapter = $this->get_adapter();
        if (\is_wp_error($adapter)) {
            return $adapter;
        }
        
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $design_system = $awbu_instance->get_design_system();
        return $design_system->update_design_system($colors, $variables, $adapter);
    }
    
    /**
     * Remote design
     */
    private function remote_design($params) {
        $remote_manager = $this->get_awbu_instance()->get_remote_design_manager();
        return $remote_manager->process_remote_design($params);
    }
    
    /**
     * Process references
     */
    private function process_references($params) {
        if (empty($params['references'])) {
            return new \WP_Error('missing_references', \__('References are required.', 'ai-website-builder-unified'));
        }
        
        $reference_handler = new AWBU_Reference_Handler();
        $processed = $reference_handler->process($params['references']);
        $design_info = $reference_handler->extract_design_info($processed);
        
        return array(
            'success' => true,
            'processed_references' => $processed,
            'design_info' => $design_info,
        );
    }
    
    /**
     * Generate with references
     */
    private function generate_with_references($params) {
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $remote_manager = $awbu_instance->get_remote_design_manager();
        return $remote_manager->process_remote_design($params);
    }
    
    /**
     * Create page with design
     */
    private function create_page_with_design($params) {
        // Validate title
        if (empty($params['title'])) {
            return new \WP_Error('missing_title', \__('Title is required.', 'ai-website-builder-unified'));
        }
        
        $title = \sanitize_text_field($params['title']);
        $raw_content = isset($params['content']) ? $params['content'] : '';
        $file_path = isset($params['file_path']) ? $params['file_path'] : '';
        $apply_design = isset($params['apply_design_system']) ? (bool) $params['apply_design_system'] : true;
        
        // Validate that either content or file_path is provided
        if (empty($raw_content) && empty($file_path)) {
            return new \WP_Error('missing_content', \__('Either content or file_path must be provided.', 'ai-website-builder-unified'));
        }
        
        // If file_path is provided, read content from file
        if (!empty($file_path) && empty($raw_content)) {
            // Support both absolute and relative paths
            $resolved_path = $file_path;
            
            // Try absolute path first
            if (!file_exists($resolved_path)) {
            // Try relative to plugin directory (YMCP)
            $plugin_dir = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : dirname(dirname(__DIR__));
            $resolved_path = $plugin_dir . '/' . ltrim($file_path, '/');
            
            // Also try YMCP directory specifically
            if (!file_exists($resolved_path)) {
                $ymcp_dir = dirname(dirname(__DIR__));
                $resolved_path = $ymcp_dir . '/' . ltrim($file_path, '/');
            }
            }
            
            // Try relative to WordPress root
            if (!file_exists($resolved_path)) {
                $resolved_path = ABSPATH . ltrim($file_path, '/');
            }
            
            // Try relative to current working directory
            if (!file_exists($resolved_path)) {
                $resolved_path = getcwd() . '/' . ltrim($file_path, '/');
            }
            
            if (file_exists($resolved_path) && is_readable($resolved_path)) {
                $raw_content = file_get_contents($resolved_path);
                if ($raw_content === false) {
                    return new \WP_Error('file_read_error', \sprintf(\__('Could not read file: %s', 'ai-website-builder-unified'), $resolved_path));
                }
            } else {
                return new \WP_Error('file_not_found', \sprintf(\__('File not found. Tried: %s, %s, %s, %s', 'ai-website-builder-unified'), $file_path, $plugin_dir . '/' . ltrim($file_path, '/'), ABSPATH . ltrim($file_path, '/'), getcwd() . '/' . ltrim($file_path, '/')));
            }
        }
        
        // Check if content is Divi 5 JSON format or Gutenberg blocks
        $is_divi5_json = false;
        $is_gutenberg = false;
        $content = '';
        
        if (!empty($raw_content)) {
            $trimmed_content = trim($raw_content);
            
            // Check if it's Gutenberg blocks (starts with <!-- wp:)
            if (strpos($trimmed_content, '<!-- wp:') === 0) {
                $is_gutenberg = true;
                // Gutenberg blocks - use as-is
                $content = $raw_content;
            }
            // Check if it starts with JSON array or object
            elseif (substr($trimmed_content, 0, 1) === '[' || substr($trimmed_content, 0, 1) === '{') {
                $is_divi5_json = true;
                // Validate JSON
                $decoded = json_decode($raw_content, true);
                if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                    // Valid JSON - use as-is (Divi 5 expects JSON string in post_content)
                    $content = $raw_content;
                } else {
                    // Invalid JSON - sanitize and use
                    $content = \sanitize_textarea_field($raw_content);
                }
            } else {
                // Regular HTML content - wrap in Gutenberg HTML block for proper rendering
                // Don't use wp_kses_post as it strips <style> tags
                $content = "<!-- wp:html -->\n" . $raw_content . "\n<!-- /wp:html -->";
            }
        }
        
        // Check if page already exists by title or slug
        $page_slug = \sanitize_title($title);
        $existing_page = \get_page_by_path($page_slug);
        
        // Also check by title match
        if (!$existing_page) {
            $pages_by_title = \get_pages(array(
                'post_type' => 'page',
                'post_status' => 'any',
                'number' => 1,
                'title' => $title,
            ));
            if (!empty($pages_by_title)) {
                $existing_page = $pages_by_title[0];
            }
        }
        
        // Create or update page
        if ($existing_page) {
            $page_id = \wp_update_post(array(
                'ID' => $existing_page->ID,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_name' => $page_slug,
            ), true);
        } else {
            $page_id = \wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $page_slug,
            ), true);
        }
        
        if (\is_wp_error($page_id)) {
            return $page_id;
        }
        
        // Set Divi 5 meta fields if JSON content detected
        if ($is_divi5_json) {
            \update_post_meta($page_id, '_et_pb_use_builder', 'on');
            \update_post_meta($page_id, '_et_builder_version', defined('ET_BUILDER_VERSION') ? ET_BUILDER_VERSION : '5.0');
            \update_post_meta($page_id, '_et_pb_old_content', '');
            \update_post_meta($page_id, '_et_pb_page_layout', 'et_no_sidebar');
        }
        
        // Apply design system if requested
        if ($apply_design) {
            $adapter = $this->get_adapter();
            if (!\is_wp_error($adapter)) {
                // Get current design system
                $colors = $adapter->get_colors();
                $variables = $adapter->get_variables();
                
                // Apply to page content
                // This would be builder-specific
            }
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'url' => \get_permalink($page_id),
            'edit_url' => \admin_url('post.php?post=' . $page_id . '&action=edit'),
            'is_divi5' => $is_divi5_json,
        );
    }
    
    // ============ NEW TOOL IMPLEMENTATIONS ============
    
    /**
     * Analyze reference URL
     */
    private function analyze_reference_url($params) {
        $url = isset($params['url']) ? \esc_url_raw($params['url']) : '';
        
        if (empty($url)) {
            return new \WP_Error('missing_url', \__('URL is required.', 'ai-website-builder-unified'));
        }
        
        $reference_handler = new AWBU_Reference_Handler();
        $link_data = $reference_handler->process(array(
            array('type' => 'url', 'url' => $url)
        ));
        
        $design_info = $reference_handler->extract_design_info($link_data);
        
        return array(
            'success' => true,
            'url' => $url,
            'content' => isset($link_data['links'][0]['content']) ? $link_data['links'][0]['content'] : '',
            'design_info' => $design_info,
        );
    }
    
    /**
     * Extract colors from image
     */
    private function extract_colors_from_image($params) {
        $image_url = isset($params['image_url']) ? \esc_url_raw($params['image_url']) : '';
        $num_colors = isset($params['num_colors']) ? \absint($params['num_colors']) : 5;
        
        if (empty($image_url)) {
            return new \WP_Error('missing_image_url', \__('Image URL is required.', 'ai-website-builder-unified'));
        }
        
        $reference_handler = new AWBU_Reference_Handler();
        $image_data = $reference_handler->process(array(
            array('type' => 'image', 'url' => $image_url)
        ));
        
        $design_info = $reference_handler->extract_design_info($image_data);
        
        return array(
            'success' => true,
            'image_url' => $image_url,
            'colors' => isset($design_info['colors']) ? array_slice($design_info['colors'], 0, $num_colors) : array(),
            'attachment_id' => isset($image_data['images'][0]['attachment_id']) ? $image_data['images'][0]['attachment_id'] : null,
        );
    }
    
    /**
     * Design full website
     */
    private function design_full_website($params) {
        $site_name = isset($params['site_name']) ? \sanitize_text_field($params['site_name']) : '';
        $site_description = isset($params['site_description']) ? \sanitize_textarea_field($params['site_description']) : '';
        
        if (empty($site_name) || empty($site_description)) {
            return new \WP_Error('missing_params', \__('Site name and description are required.', 'ai-website-builder-unified'));
        }
        
        $references = isset($params['references']) ? $params['references'] : array();
        $pages = isset($params['pages']) ? $params['pages'] : array('home');
        $model = isset($params['model']) ? \sanitize_text_field($params['model']) : 'deepseek';
        
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $remote_manager = $awbu_instance->get_remote_design_manager();
        
        return $remote_manager->process_remote_design(array(
            'site_name' => $site_name,
            'description' => $site_description,
            'remote_url' => isset($params['remote_url']) ? \esc_url_raw($params['remote_url']) : '',
            'remote_api_key' => isset($params['remote_api_key']) ? \sanitize_text_field($params['remote_api_key']) : '',
            'references' => $references,
            'pages' => $pages,
            'model' => $model,
        ));
    }
    
    /**
     * Apply design from reference
     */
    private function apply_design_from_reference($params) {
        $reference_url = isset($params['reference_url']) ? \esc_url_raw($params['reference_url']) : '';
        
        if (empty($reference_url)) {
            return new \WP_Error('missing_url', \__('Reference URL is required.', 'ai-website-builder-unified'));
        }
        
        // Extract design info
        $reference_handler = new AWBU_Reference_Handler();
        $link_data = $reference_handler->process(array(
            array('type' => 'url', 'url' => $reference_url)
        ));
        
        $design_info = $reference_handler->extract_design_info($link_data);
        
        // Apply to design system
        $builder = AWBU_Builder_Detector::detect();
        $adapter = $this->get_adapter();
        if (\is_wp_error($adapter)) {
            return $adapter;
        }
        
        $applied = array();
        
        if (!empty($design_info['colors']) && (!isset($params['apply_colors']) || $params['apply_colors'])) {
            $colors = array();
            foreach ($design_info['colors'] as $index => $color) {
                $colors["gcid-ref-color-{$index}"] = array(
                    'color' => $color,
                    'name' => "Reference Color " . ($index + 1),
                );
            }
            $adapter->set_colors($colors);
            $applied['colors'] = count($colors);
        }
        
        return array(
            'success' => true,
            'reference_url' => $reference_url,
            'design_info' => $design_info,
            'applied' => $applied,
        );
    }
    
    /**
     * Get available AI models
     * 
     * COMPATIBILITY: Returns all available models for any IDE or AI client
     */
    private function get_available_models($params) {
        // Check both option formats for maximum compatibility
        $api_keys_awbu = \get_option('awbu_api_keys', array());
        $api_keys_aisbp = \get_option('aisbp_api_keys', array());
        
        $models = array(
            array(
                'id' => 'deepseek',
                'name' => 'DeepSeek',
                'available' => !empty(\get_option('awbu_deepseek_api_key')) || 
                              !empty($api_keys_awbu['deepseek']) || 
                              !empty($api_keys_aisbp['deepseek']),
                'description' => 'Cost-effective, great for code generation',
                'provider' => 'DeepSeek',
            ),
            array(
                'id' => 'openai',
                'name' => 'GPT-4o',
                'available' => !empty(\get_option('awbu_openai_api_key')) || 
                              !empty($api_keys_awbu['openai']) || 
                              !empty($api_keys_aisbp['openai']),
                'description' => 'Most capable, best for complex tasks',
                'provider' => 'OpenAI',
            ),
            array(
                'id' => 'claude',
                'name' => 'Claude',
                'available' => !empty(\get_option('awbu_claude_api_key')) || 
                              !empty($api_keys_awbu['claude']) || 
                              !empty($api_keys_aisbp['claude']),
                'description' => 'Excellent for creative and design tasks',
                'provider' => 'Anthropic',
            ),
            array(
                'id' => 'gemini',
                'name' => 'Gemini',
                'available' => !empty(\get_option('awbu_gemini_api_key')) || 
                              !empty($api_keys_awbu['gemini']) || 
                              !empty($api_keys_aisbp['gemini']),
                'description' => 'Free tier available, good for testing',
                'provider' => 'Google',
            ),
        );
        
        // Filter to only available models
        $available_models = array_filter($models, function($model) {
            return $model['available'];
        });
        
        return array(
            'success' => true,
            'models' => array_values($available_models), // Re-index array
            'all_models' => $models, // Include all for reference
            'default' => \get_option('awbu_default_model', \get_option('aisbp_default_model', 'deepseek')),
            'total_available' => count($available_models),
        );
    }
    
    /**
     * Sync design system
     */
    private function sync_design_system($params) {
        $adapter = $this->get_adapter();
        if (\is_wp_error($adapter)) {
            return $adapter;
        }
        
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $design_system = $awbu_instance->get_design_system();
        $result = $design_system->sync_all($adapter);
        
        return array(
            'success' => true,
            'builder' => AWBU_Builder_Detector::detect(),
            'synced' => $result,
            'timestamp' => \current_time('mysql'),
        );
    }
    
    /**
     * Helper to get adapter safely
     */
    private function get_adapter() {
        $builder = AWBU_Builder_Detector::detect();
        $adapter = AWBU_Adapter_Factory::create($builder, true);
        
        if (!$adapter || \is_wp_error($adapter)) {
            return new \WP_Error(
                'adapter_error', 
                \__('Could not initialize design system adapter.', 'ai-website-builder-unified')
            );
        }
        
        return $adapter;
    }
    
    /**
     * Helper to get AWBU instance safely
     */
    private function get_awbu_instance() {
        if (function_exists('AWBU')) {
            return AWBU();
        }
        
        // Fallback: try to get global instance
        global $awbu_instance;
        if (isset($awbu_instance) && is_object($awbu_instance)) {
            return $awbu_instance;
        }
        
        // Last resort: try class name
        if (class_exists('AI_Website_Builder_Unified')) {
            return AI_Website_Builder_Unified::instance();
        }
        
        return new \WP_Error('awbu_not_found', \__('AWBU instance not found.', 'ai-website-builder-unified'));
    }
    
    // ============ NEW TOOL IMPLEMENTATIONS: CODE ANALYSIS & DEBUGGING ============

// ==================== YMCP PERSONAL ASSISTANT TOOLS ====================
case 'ymcp_get_dashboard':
    return $this->ymcp_get_dashboard($params);

case 'ymcp_get_site_info':
    return $this->ymcp_get_site_info($params);

case 'ymcp_get_tasks':
    return $this->ymcp_get_tasks($params);

case 'ymcp_get_analytics':
    return $this->ymcp_get_analytics($params);

case 'ymcp_generate_report':
    return $this->ymcp_generate_report($params);

case 'ymcp_clear_analytics':
    return $this->ymcp_clear_analytics($params);

case 'ymcp_security_scan':
    return $this->ymcp_security_scan($params);

case 'ymcp_get_security_report':
    return $this->ymcp_get_security_report($params);

case 'ymcp_create_backup':
    return $this->ymcp_create_backup($params);

case 'ymcp_restore_backup':
    return $this->ymcp_restore_backup($params);

case 'ymcp_list_backups':
    return $this->ymcp_list_backups($params);

case 'ymcp_delete_backup':
    return $this->ymcp_delete_backup($params);

case 'ymcp_send_email':
    return $this->ymcp_send_email($params);

case 'ymcp_chatbot_query':
    return $this->ymcp_chatbot_query($params);

case 'ymcp_train_chatbot':
    return $this->ymcp_train_chatbot($params);

case 'ymcp_create_product':
    return $this->ymcp_create_product($params);

case 'ymcp_update_inventory':
    return $this->ymcp_update_inventory($params);

case 'ymcp_get_sales_report':
    return $this->ymcp_get_sales_report($params);

case 'ymcp_create_workflow':
    return $this->ymcp_create_workflow($params);

case 'ymcp_list_workflows':
    return $this->ymcp_list_workflows($params);

case 'ymcp_trigger_workflow':
    return $this->ymcp_trigger_workflow($params);

    
    /**
     * Detect code errors
     * Based on WordPress MCP error detection patterns
     */
    private function detect_code_errors($params) {
        $file_path = isset($params['file_path']) ? sanitize_text_field($params['file_path']) : '';
        $check_types = isset($params['check_types']) ? $params['check_types'] : array('syntax', 'security', 'performance', 'standards');
        
        $errors = array();
        $warnings = array();
        
        if (!empty($file_path)) {
            // Check specific file
            $full_path = $this->resolve_file_path($file_path);
            if (!file_exists($full_path)) {
                return new \WP_Error('file_not_found', \__('File not found.', 'ai-website-builder-unified'));
            }
            
            $file_errors = $this->analyze_file($full_path, $check_types);
            $errors = array_merge($errors, $file_errors['errors']);
            $warnings = array_merge($warnings, $file_errors['warnings']);
        } else {
            // Check all plugin files
            $plugin_dir = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : plugin_dir_path(__FILE__);
            $files = $this->get_php_files($plugin_dir);
            
            foreach ($files as $file) {
                $file_errors = $this->analyze_file($file, $check_types);
                $errors = array_merge($errors, $file_errors['errors']);
                $warnings = array_merge($warnings, $file_errors['warnings']);
            }
        }
        
        return array(
            'success' => true,
            'errors' => $errors,
            'warnings' => $warnings,
            'total_errors' => count($errors),
            'total_warnings' => count($warnings),
        );
    }
    
    /**
     * Search and replace in files
     */
    private function search_replace_files($params) {
        $search = isset($params['search']) ? $params['search'] : '';
        $replace = isset($params['replace']) ? $params['replace'] : '';
        $file_pattern = isset($params['file_pattern']) ? sanitize_text_field($params['file_pattern']) : '*';
        $use_regex = isset($params['use_regex']) ? (bool) $params['use_regex'] : false;
        $create_backup = isset($params['create_backup']) ? (bool) $params['create_backup'] : true;
        $dry_run = isset($params['dry_run']) ? (bool) $params['dry_run'] : false;
        
        if (empty($search)) {
            return new \WP_Error('missing_search', \__('Search text is required.', 'ai-website-builder-unified'));
        }
        
        $plugin_dir = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : \plugin_dir_path(__FILE__);
        $files = $this->get_files_by_pattern($plugin_dir, $file_pattern);
        
        $results = array();
        $total_replacements = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }
            
            $matches = 0;
            if ($use_regex) {
                $new_content = preg_replace($search, $replace, $content, -1, $matches);
                if ($new_content === null) {
                    $results[] = array(
                        'file' => $file,
                        'status' => 'error',
                        'message' => 'Invalid regex pattern',
                    );
                    continue;
                }
            } else {
                $new_content = str_replace($search, $replace, $content, $matches);
            }
            
            if ($matches > 0) {
                if ($create_backup && !$dry_run) {
                    $backup_file = $file . '.bak.' . time();
                    file_put_contents($backup_file, $content);
                }
                
                if (!$dry_run) {
                    file_put_contents($file, $new_content);
                }
                
                $results[] = array(
                    'file' => $file,
                    'status' => 'success',
                    'replacements' => $matches,
                    'backup' => $create_backup && !$dry_run ? ($file . '.bak.' . time()) : null,
                );
                
                $total_replacements += $matches;
            }
        }
        
        return array(
            'success' => true,
            'dry_run' => $dry_run,
            'total_files' => count($files),
            'files_modified' => count($results),
            'total_replacements' => $total_replacements,
            'results' => $results,
        );
    }
    
    /**
     * Search and replace in database
     */
    private function search_replace_database($params) {
        global $wpdb;
        
        $search = isset($params['search']) ? $params['search'] : '';
        $replace = isset($params['replace']) ? $params['replace'] : '';
        $tables = isset($params['tables']) ? $params['tables'] : array();
        $columns = isset($params['columns']) ? $params['columns'] : array();
        $create_backup = isset($params['create_backup']) ? (bool) $params['create_backup'] : true;
        $dry_run = isset($params['dry_run']) ? (bool) $params['dry_run'] : false;
        
        if (empty($search)) {
            return new \WP_Error('missing_search', \__('Search text is required.', 'ai-website-builder-unified'));
        }
        
        // Get WordPress tables if not specified
        if (empty($tables)) {
            $tables = $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . '%'));
        }
        
        $results = array();
        $total_replacements = 0;
        
        foreach ($tables as $table) {
            // Get text columns if not specified
            if (empty($columns)) {
                $table_columns = $wpdb->get_col($wpdb->prepare(
                    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s 
                     AND DATA_TYPE IN ('varchar', 'text', 'longtext', 'mediumtext', 'tinytext', 'char')",
                    DB_NAME,
                    $table
                ));
            } else {
                $table_columns = $columns;
            }
            
            foreach ($table_columns as $column) {
                // Count matches
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE %s",
                    '%' . $wpdb->esc_like($search) . '%'
                ));
                
                if ($count > 0) {
                    if (!$dry_run) {
                        $updated = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, %s, %s) WHERE `{$column}` LIKE %s",
                            $search,
                            $replace,
                            '%' . $wpdb->esc_like($search) . '%'
                        ));
                    } else {
                        $updated = $count;
                    }
                    
                    $results[] = array(
                        'table' => $table,
                        'column' => $column,
                        'matches' => $count,
                        'updated' => $updated,
                    );
                    
                    $total_replacements += $updated;
                }
            }
        }
        
        return array(
            'success' => true,
            'dry_run' => $dry_run,
            'total_replacements' => $total_replacements,
            'results' => $results,
        );
    }
    
    /**
     * Design single page
     */
    private function design_page($params) {
        $page_id = isset($params['page_id']) ? absint($params['page_id']) : 0;
        $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
        $description = isset($params['description']) ? sanitize_textarea_field($params['description']) : '';
        $design_style = isset($params['design_style']) ? sanitize_text_field($params['design_style']) : 'modern';
        $apply_design_system = isset($params['apply_design_system']) ? (bool) $params['apply_design_system'] : true;
        $model = isset($params['model']) ? sanitize_text_field($params['model']) : 'deepseek';
        
        if (empty($title) || empty($description)) {
            return new WP_Error('missing_params', __('Title and description are required.', 'ai-website-builder-unified'));
        }
        
        // Get AI orchestrator
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $orchestrator = $awbu_instance->get_ai_orchestrator();
        if (!$orchestrator) {
            return new \WP_Error('orchestrator_missing', \__('AI Orchestrator not available.', 'ai-website-builder-unified'));
        }
        
        // Generate page design
        $result = $orchestrator->generate(array(
            'description' => $description,
            'model' => $model,
            'phase' => 1,
            'creation_mode' => 'single_page',
            'website_type' => 'page',
        ));
        
        if (\is_wp_error($result)) {
            return $result;
        }
        
        // Create or update page
        $page_data = array(
            'post_title' => $title,
            'post_content' => isset($result['full_code']) ? $result['full_code'] : '',
            'post_status' => 'publish',
            'post_type' => 'page',
        );
        
        if ($page_id > 0) {
            $page_data['ID'] = $page_id;
            $page_id = \wp_update_post($page_data);
        } else {
            $page_id = \wp_insert_post($page_data);
        }
        
        if (\is_wp_error($page_id)) {
            return $page_id;
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'url' => \get_permalink($page_id),
            'design_style' => $design_style,
        );
    }
    
    /**
     * Modify existing design
     */
    private function modify_design($params) {
        $page_id = isset($params['page_id']) ? \absint($params['page_id']) : 0;
        $instructions = isset($params['instructions']) ? \sanitize_textarea_field($params['instructions']) : '';
        $model = isset($params['model']) ? \sanitize_text_field($params['model']) : 'deepseek';
        
        if ($page_id <= 0) {
            return new \WP_Error('missing_page_id', \__('Page ID is required.', 'ai-website-builder-unified'));
        }
        
        if (empty($instructions)) {
            return new \WP_Error('missing_instructions', \__('Modification instructions are required.', 'ai-website-builder-unified'));
        }
        
        $page = \get_post($page_id);
        if (!$page) {
            return new \WP_Error('page_not_found', \__('Page not found.', 'ai-website-builder-unified'));
        }
        
        // Get AI orchestrator
        $awbu_instance = $this->get_awbu_instance();
        if (\is_wp_error($awbu_instance)) {
            return $awbu_instance;
        }
        
        $orchestrator = $awbu_instance->get_ai_orchestrator();
        if (!$orchestrator) {
            return new \WP_Error('orchestrator_missing', \__('AI Orchestrator not available.', 'ai-website-builder-unified'));
        }
        
        // Use chat to modify
        $result = $orchestrator->chat(array(
            'message' => $instructions,
            'project_id' => 0,
            'context' => $page->post_content,
            'model' => $model,
        ));
        
        if (\is_wp_error($result)) {
            return $result;
        }
        
        // Update page
        $updated = \wp_update_post(array(
            'ID' => $page_id,
            'post_content' => isset($result['code']) ? $result['code'] : $page->post_content,
        ));
        
        if (\is_wp_error($updated)) {
            return $updated;
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'url' => \get_permalink($page_id),
        );
    }
    
    /**
     * List WordPress pages
     */
    private function list_pages($params) {
        $status = isset($params['status']) ? sanitize_text_field($params['status']) : 'any';
        $limit = isset($params['limit']) ? absint($params['limit']) : 50;
        
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => $limit,
            'post_status' => $status,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $pages = \get_posts($args);
        $result = array();
        
        foreach ($pages as $page) {
            $result[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'status' => $page->post_status,
                'url' => \get_permalink($page->ID),
                'modified' => $page->post_modified,
                'template' => \get_page_template_slug($page->ID),
            );
        }
        
        return array(
            'success' => true,
            'total' => count($result),
            'pages' => $result,
        );
    }
    
    /**
     * Delete WordPress page
     */
    private function delete_page($params) {
        $page_id = isset($params['page_id']) ? absint($params['page_id']) : 0;
        $force = isset($params['force']) ? (bool) $params['force'] : false;
        
        if ($page_id <= 0) {
            return new \WP_Error('missing_page_id', \__('Page ID is required.', 'ai-website-builder-unified'));
        }
        
        $page = \get_post($page_id);
        if (!$page) {
            return new \WP_Error('page_not_found', \__('Page not found.', 'ai-website-builder-unified'));
        }
        
        if ($page->post_type !== 'page') {
            return new \WP_Error('invalid_post_type', \__('Post is not a page.', 'ai-website-builder-unified'));
        }
        
        $result = \wp_delete_post($page_id, $force);
        
        if (!$result) {
            return new \WP_Error('delete_failed', \__('Failed to delete page.', 'ai-website-builder-unified'));
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'title' => $page->post_title,
            'deleted' => true,
        );
    }
    
    /**
     * Update WordPress page content directly in database
     */
    private function update_page($params) {
        $page_id = isset($params['page_id']) ? absint($params['page_id']) : 0;
        
        if ($page_id <= 0) {
            return new \WP_Error('missing_page_id', \__('Page ID is required.', 'ai-website-builder-unified'));
        }
        
        $page = \get_post($page_id);
        if (!$page) {
            return new \WP_Error('page_not_found', \__('Page not found.', 'ai-website-builder-unified'));
        }
        
        if ($page->post_type !== 'page') {
            return new \WP_Error('invalid_post_type', \__('Post is not a page.', 'ai-website-builder-unified'));
        }
        
        $update_data = array('ID' => $page_id);
        
        if (isset($params['title'])) {
            $update_data['post_title'] = \sanitize_text_field($params['title']);
        }
        
        if (isset($params['content'])) {
            // Check if content is Gutenberg blocks or HTML
            $content = $params['content'];
            $trimmed_content = trim($content);
            
            // If it's HTML but not wrapped in Gutenberg blocks, wrap it
            if (strpos($trimmed_content, '<!-- wp:') === false && strpos($trimmed_content, '<') !== false) {
                $update_data['post_content'] = "<!-- wp:html -->\n" . $content . "\n<!-- /wp:html -->";
            } else {
                $update_data['post_content'] = $content;
            }
        }
        
        if (isset($params['status'])) {
            $update_data['post_status'] = \sanitize_text_field($params['status']);
        }
        
        $result = \wp_update_post($update_data, true);
        
        if (\is_wp_error($result)) {
            return $result;
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'url' => \get_permalink($page_id),
            'updated' => true,
        );
    }
    
    /**
     * Inject multiple pages from files in batch
     */
    private function inject_pages_batch($params) {
        $pages = isset($params['pages']) ? $params['pages'] : array();
        $delete_unused = isset($params['delete_unused']) ? (bool) $params['delete_unused'] : true;
        $set_homepage = isset($params['set_homepage']) ? sanitize_text_field($params['set_homepage']) : '';
        
        if (empty($pages) || !is_array($pages)) {
            return new \WP_Error('missing_pages', \__('Pages array is required.', 'ai-website-builder-unified'));
        }
        
        $results = array();
        $plugin_dir = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : dirname(dirname(__DIR__));
        
        // Delete unused pages if requested
        if ($delete_unused) {
            $all_pages = \get_posts(array(
                'post_type' => 'page',
                'posts_per_page' => -1,
                'post_status' => 'any',
            ));
            
            $keep_slugs = array_column($pages, 'slug');
            $deleted = 0;
            
            foreach ($all_pages as $page) {
                $slug = $page->post_name;
                if (!in_array($slug, $keep_slugs) && $page->post_type === 'page') {
                    \wp_delete_post($page->ID, true);
                    $deleted++;
                    $results['deleted'][] = array(
                        'id' => $page->ID,
                        'title' => $page->post_title,
                        'slug' => $slug,
                    );
                }
            }
            $results['deleted_count'] = $deleted;
        }
        
        // Create or update pages
        foreach ($pages as $page_data) {
            $title = isset($page_data['title']) ? \sanitize_text_field($page_data['title']) : '';
            $slug = isset($page_data['slug']) ? \sanitize_text_field($page_data['slug']) : '';
            $file_path = isset($page_data['file_path']) ? $page_data['file_path'] : '';
            
            if (empty($title) || empty($slug)) {
                $results['errors'][] = \__('Title and slug are required for each page.', 'ai-website-builder-unified');
                continue;
            }
            
            // Read content from file
            $content = '';
            if (!empty($file_path)) {
                $resolved_path = $file_path;
                
                // Try multiple paths
                if (!file_exists($resolved_path)) {
                    $resolved_path = $plugin_dir . '/' . ltrim($file_path, '/');
                }
                if (!file_exists($resolved_path)) {
                    $resolved_path = ABSPATH . ltrim($file_path, '/');
                }
                
                if (file_exists($resolved_path) && is_readable($resolved_path)) {
                    $content = file_get_contents($resolved_path);
                } else {
                    $results['errors'][] = \sprintf(\__('File not found: %s', 'ai-website-builder-unified'), $file_path);
                    continue;
                }
            }
            
            if (empty($content)) {
                $results['errors'][] = \sprintf(\__('Empty content for page: %s', 'ai-website-builder-unified'), $title);
                continue;
            }
            
            // Check if it's Gutenberg blocks (starts with <!-- wp:)
            $is_gutenberg = (strpos(trim($content), '<!-- wp:') === 0);
            
            // Create or update page
            $existing_page = \get_page_by_path($slug);
            
            $page_args = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $slug,
            );
            
            if ($existing_page) {
                $page_args['ID'] = $existing_page->ID;
                $page_id = \wp_update_post($page_args, true);
                $action = 'updated';
            } else {
                $page_id = \wp_insert_post($page_args, true);
                $action = 'created';
            }
            
            if (\is_wp_error($page_id)) {
                $results['errors'][] = \sprintf(\__('Error %s page %s: %s', 'ai-website-builder-unified'), $action, $title, $page_id->get_error_message());
            } else {
                $results['pages'][] = array(
                    'id' => $page_id,
                    'title' => $title,
                    'slug' => $slug,
                    'action' => $action,
                    'url' => \get_permalink($page_id),
                );
                
                // Set as homepage if requested
                if ($slug === $set_homepage) {
                    \update_option('show_on_front', 'page');
                    \update_option('page_on_front', $page_id);
                    $results['homepage_set'] = $page_id;
                }
            }
        }
        
        return array(
            'success' => true,
            'total_pages' => count($pages),
            'processed' => count($results['pages'] ?? array()),
            'results' => $results,
        );
    }
    
    /**
     * Manage pages - Delete unused and create/update required
     */
    private function manage_pages($params) {
        $action = isset($params['action']) ? sanitize_text_field($params['action']) : 'cleanup';
        $required_pages = isset($params['required_pages']) ? $params['required_pages'] : array();
        
        $result = array(
            'action' => $action,
            'deleted' => array(),
            'created' => array(),
            'updated' => array(),
            'errors' => array(),
        );
        
        // Get all pages
        $all_pages = \get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        // Delete unused pages
        if ($action === 'cleanup' || $action === 'delete_all_unused') {
            $required_slugs = !empty($required_pages) ? array_column($required_pages, 'slug') : array('home', 'about', 'products', 'contact');
            $system_pages = array('sample-page', 'privacy-policy');
            
            foreach ($all_pages as $page) {
                $slug = $page->post_name;
                
                if (!in_array($slug, $required_slugs) && 
                    !in_array($slug, $system_pages) &&
                    $page->post_status !== 'trash') {
                    
                    $deleted = \wp_delete_post($page->ID, true);
                    if ($deleted) {
                        $result['deleted'][] = array(
                            'id' => $page->ID,
                            'title' => $page->post_title,
                            'slug' => $slug,
                        );
                    }
                }
            }
        }
        
        // Create/update required pages
        if (!empty($required_pages)) {
            foreach ($required_pages as $page_data) {
                $title = isset($page_data['title']) ? \sanitize_text_field($page_data['title']) : '';
                $slug = isset($page_data['slug']) ? \sanitize_text_field($page_data['slug']) : '';
                $content = isset($page_data['content']) ? $page_data['content'] : '';
                
                if (empty($title) || empty($slug)) {
                    $result['errors'][] = 'Title and slug required for page';
                    continue;
                }
                
                $existing_page = \get_page_by_path($slug);
                
                $page_args = array(
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug,
                );
                
                if ($existing_page) {
                    $page_args['ID'] = $existing_page->ID;
                    $page_id = \wp_update_post($page_args, true);
                    $action_type = 'updated';
                } else {
                    $page_id = \wp_insert_post($page_args, true);
                    $action_type = 'created';
                }
                
                if (\is_wp_error($page_id)) {
                    $result['errors'][] = $page_id->get_error_message();
                } else {
                    $result[$action_type][] = array(
                        'id' => $page_id,
                        'title' => $title,
                        'slug' => $slug,
                    );
                }
            }
        }
        
        return array(
            'success' => true,
            'total_pages' => count($all_pages),
            'deleted_count' => count($result['deleted']),
            'created_count' => count($result['created']),
            'updated_count' => count($result['updated']),
            'result' => $result,
        );
    }
    
    /**
     * List files
     */
    private function list_files($params) {
        $pattern = isset($params['pattern']) ? sanitize_text_field($params['pattern']) : '*';
        $directory = isset($params['directory']) ? sanitize_text_field($params['directory']) : '';
        
        if (empty($directory)) {
            $directory = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : plugin_dir_path(__FILE__);
        }
        
        $files = $this->get_files_by_pattern($directory, $pattern);
        
        return array(
            'success' => true,
            'pattern' => $pattern,
            'directory' => $directory,
            'total_files' => count($files),
            'files' => array_map(function($file) {
                return array(
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                );
            }, $files),
        );
    }
    
    // ============ HELPER METHODS ============
    
    private function resolve_file_path($file_path) {
        if (file_exists($file_path)) {
            return $file_path;
        }
        
        $plugin_dir = defined('AWBU_PLUGIN_DIR') ? AWBU_PLUGIN_DIR : plugin_dir_path(__FILE__);
        $full_path = $plugin_dir . ltrim($file_path, '/');
        
        if (file_exists($full_path)) {
            return $full_path;
        }
        
        return $file_path;
    }
    
    private function get_php_files($directory) {
        $files = array();
        
        // Normalize directory path
        $directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        
        // Check if RecursiveIteratorIterator is available
        if (!class_exists('RecursiveIteratorIterator') || !class_exists('RecursiveDirectoryIterator')) {
            // Fallback: simple directory scan using glob
            $files = glob($directory . '**/*.php', GLOB_BRACE);
            return $files ?: array();
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\Exception $e) {
            error_log('AWBU: Error scanning directory: ' . $e->getMessage());
            // Fallback: use glob
            $files = glob($directory . '**/*.php', GLOB_BRACE);
            return $files ?: array();
        }
        
        return $files;
    }
    
    private function get_files_by_pattern($directory, $pattern) {
        $files = array();
        
        // Normalize directory path
        $directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        
        // Check if RecursiveIteratorIterator is available
        if (!class_exists('RecursiveIteratorIterator') || !class_exists('RecursiveDirectoryIterator')) {
            // Fallback: use glob
            $glob_pattern = $directory . $pattern;
            $files = glob($glob_pattern, GLOB_BRACE);
            return $files ?: array();
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            // Convert glob pattern to regex
            $regex = str_replace(
                array('*', '?', '/'),
                array('.*', '.', '\/'),
                $pattern
            );
            $regex = '/^' . preg_quote($directory, '/') . $regex . '$/';
            
            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match($regex, $file->getPathname())) {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\Exception $e) {
            error_log('AWBU: Error scanning directory: ' . $e->getMessage());
            // Fallback: use glob
            $glob_pattern = $directory . $pattern;
            $files = glob($glob_pattern, GLOB_BRACE);
            return $files ?: array();
        }
        
        return $files;
    }
    
    private function analyze_file($file_path, $check_types) {
        $errors = array();
        $warnings = array();
        
        $content = file_get_contents($file_path);
        if ($content === false) {
            return array('errors' => array(), 'warnings' => array());
        }
        
        // Syntax check
        if (in_array('syntax', $check_types) && pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
            $syntax_check = $this->check_php_syntax($content);
            if ($syntax_check['valid'] === false) {
                $errors[] = array(
                    'type' => 'syntax',
                    'file' => $file_path,
                    'message' => $syntax_check['error'],
                    'line' => $syntax_check['line'] ?? null,
                );
            }
        }
        
        // Security checks
        if (in_array('security', $check_types)) {
            $security_issues = $this->check_security($content, $file_path);
            $errors = array_merge($errors, $security_issues);
        }
        
        // Performance checks
        if (in_array('performance', $check_types)) {
            $performance_issues = $this->check_performance($content, $file_path);
            $warnings = array_merge($warnings, $performance_issues);
        }
        
        return array(
            'errors' => $errors,
            'warnings' => $warnings,
        );
    }
    
    private function check_php_syntax($code) {
        // Use PHP lint
        $tmp_file = tempnam(sys_get_temp_dir(), 'php_check_');
        file_put_contents($tmp_file, $code);
        $output = array();
        $return_var = 0;
        exec("php -l {$tmp_file} 2>&1", $output, $return_var);
        unlink($tmp_file);
        
        if ($return_var !== 0) {
            $error = implode("\n", $output);
            preg_match('/on line (\d+)/', $error, $matches);
            return array(
                'valid' => false,
                'error' => $error,
                'line' => isset($matches[1]) ? intval($matches[1]) : null,
            );
        }
        
        return array('valid' => true);
    }
    
    private function check_security($content, $file_path) {
        $issues = array();
        
        // Check for common security issues
        $security_patterns = array(
            '/eval\s*\(/' => 'Use of eval() is dangerous',
            '/exec\s*\(/' => 'Use of exec() may be unsafe',
            '/system\s*\(/' => 'Use of system() may be unsafe',
            '/\$_(GET|POST|REQUEST|COOKIE)\s*\[/' => 'Direct use of superglobals without sanitization',
            '/mysql_query\s*\(/' => 'Deprecated mysql_query(), use wpdb instead',
        );
        
        $lines = explode("\n", $content);
        foreach ($lines as $line_num => $line) {
            foreach ($security_patterns as $pattern => $message) {
                if (preg_match($pattern, $line)) {
                    $issues[] = array(
                        'type' => 'security',
                        'file' => $file_path,
                        'message' => $message,
                        'line' => $line_num + 1,
                        'code' => trim($line),
                    );
                }
            }
        }
        
        return $issues;
    }
    
    private function check_performance($content, $file_path) {
        $warnings = array();
        
        // Check for performance issues
        if (preg_match_all('/get_posts\s*\(/', $content, $matches)) {
            if (count($matches[0]) > 5) {
                $warnings[] = array(
                    'type' => 'performance',
                    'file' => $file_path,
                    'message' => 'Multiple get_posts() calls detected, consider using WP_Query',
                );
            }
        }
        
        return $warnings;
    }
}

