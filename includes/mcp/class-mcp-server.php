<?php
/**
 * MCP Server - خادم MCP Protocol
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_MCP_Server {
    
    private $tools;
    
    public function __construct() {
        $this->tools = new AWBU_MCP_Tools_Enhanced();
    }
    
    /**
     * Get server information (MCP Protocol requirement)
     * 
     * COMPATIBILITY: Enhanced to work with any IDE (Cursor, Antigravity, etc.)
     * and any AI model (GPT-4, Claude, DeepSeek, Gemini, etc.)
     * 
     * @return array Server info including name, version, and capabilities
     */
    public function get_server_info() {
        return array(
            'protocolVersion' => '2024-11-05', // MCP Protocol version
            'capabilities' => array(
                'tools' => array(
                    'listChanged' => true,
                    'call' => true, // Explicitly enable tool calls
                ),
                'resources' => array(
                    'subscribe' => false,
                    'listChanged' => true,
                    'get' => true, // Explicitly enable resource access
                ),
                'serverInfo' => true, // Enable server info endpoint
                'listOfferings' => true, // Enable offerings endpoint
            ),
            'serverInfo' => array(
                'name' => 'ai-website-builder-unified',  // FIXED: no spaces, MCP tool names use this as prefix
                'version' => defined('AWBU_VERSION') ? AWBU_VERSION : '1.0.1',
                'description' => 'MCP server for remote website design with AI. Compatible with all AI models and IDEs.',
                'author' => 'AWBU Team',
            ),
        );
    }
    
    /**
     * List offerings (MCP Protocol requirement)
     * 
     * @return array Available server offerings
     */
    /**
     * List offerings (MCP Protocol requirement)
     * 
     * FINAL FIX: Ensure serverInfo is properly nested
     * Cursor expects: { serverInfo: { serverInfo: {...}, protocolVersion, capabilities }, tools, resources }
     * 
     * @return array Available server offerings
     */
    public function list_offerings() {
        $server_info = $this->get_server_info();
        
        // FINAL FIX: Return proper MCP Protocol format
        // Cursor expects serverInfo to be nested inside the response
        return array(
            'serverInfo' => isset($server_info['serverInfo']) ? $server_info['serverInfo'] : array(
                'name' => 'ai-website-builder-unified',  // FIXED: no spaces
                'version' => defined('AWBU_VERSION') ? AWBU_VERSION : '1.0.1',
                'description' => 'MCP server for remote website design with AI. Compatible with all AI models and IDEs.',
                'author' => 'AWBU Team',
            ),
            'tools' => $this->list_tools(),
            'resources' => $this->list_resources(),
        );
    }
    
    /**
     * List all available tools
     */
    public function list_tools() {
        return $this->tools->get_tool_definitions();
    }
    
    /**
     * Call a tool
     * 
     * SECURITY & RELIABILITY: Enhanced error handling and validation
     */
    public function call_tool($tool_name, $params) {
        try {
            // Validate tool_name
            if ( ! is_string( $tool_name ) || empty( trim( $tool_name ) ) ) {
                return new WP_Error(
                    'invalid_tool_name',
                    __( 'Tool name must be a non-empty string.', 'ai-website-builder-unified' ),
                    array( 'status' => 400 )
                );
            }
            
            // Sanitize tool_name
            $tool_name = sanitize_text_field( $tool_name );
            
            // Validate tools instance
            if ( ! is_object( $this->tools ) || ! method_exists( $this->tools, 'execute_tool' ) ) {
                error_log( 'AWBU MCP: Tool executor not available' );
                return new WP_Error(
                    'tool_executor_missing',
                    __( 'Tool executor not available.', 'ai-website-builder-unified' ),
                    array( 'status' => 503 )
                );
            }
            
            // Execute tool
            $result = $this->tools->execute_tool( $tool_name, $params );
            
            // Log errors for debugging
            if ( is_wp_error( $result ) ) {
                error_log( sprintf(
                    'AWBU MCP Tool Error [%s]: %s (Code: %s)',
                    $tool_name,
                    $result->get_error_message(),
                    $result->get_error_code()
                ) );
            }
            
            return $result;
        } catch ( \Throwable $e ) {
            error_log( sprintf(
                'AWBU MCP Tool Exception [%s]: %s in %s:%d',
                $tool_name,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ) );
            
            return new WP_Error(
                'tool_exception',
                sprintf( 
                    __( 'Tool execution failed: %s', 'ai-website-builder-unified' ), 
                    $e->getMessage() 
                ),
                array( 
                    'status' => 500,
                    'file' => basename( $e->getFile() ),
                    'line' => $e->getLine(),
                )
            );
        } catch ( \Exception $e ) {
            // Fallback for older PHP versions
            error_log( 'AWBU MCP Tool Exception: ' . $e->getMessage() );
            
            return new WP_Error(
                'tool_exception',
                __( 'Tool execution failed.', 'ai-website-builder-unified' ),
                array( 'status' => 500 )
            );
        }
    }
    
    /**
     * List resources
     */
    public function list_resources() {
        return array(
            array(
                'uri' => 'awbu://design-system',
                'name' => 'Design System',
                'description' => 'Current design system state',
            ),
            array(
                'uri' => 'awbu://references',
                'name' => 'References',
                'description' => 'Processed references',
            ),
        );
    }
    
    /**
     * Get resource
     * 
     * SECURITY: Enhanced validation and error handling
     */
    public function get_resource($uri) {
        // Validate URI format
        if ( ! is_string( $uri ) || empty( trim( $uri ) ) ) {
            return new WP_Error(
                'invalid_uri',
                __( 'Invalid resource URI. URI must be a non-empty string.', 'ai-website-builder-unified' ),
                array( 'status' => 400 )
            );
        }
        
        // Sanitize URI (for security, though we expect specific format)
        $uri = esc_url_raw( $uri );
        
        try {
            switch ($uri) {
            case 'awbu://design-system':
                $builder = AWBU_Builder_Detector::detect();
                $adapter = AWBU_Adapter_Factory::create($builder);
                return array(
                    'contents' => array(
                        array(
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => wp_json_encode(array(
                                'builder' => $builder,
                                'colors' => $adapter->get_colors(),
                                'variables' => $adapter->get_variables(),
                            )),
                        ),
                    ),
                );
                
            default:
                return new WP_Error(
                    'resource_not_found', 
                    sprintf( __( 'Resource not found: %s', 'ai-website-builder-unified' ), $uri ),
                    array( 'status' => 404 )
                );
            }
        } catch ( \Throwable $e ) {
            error_log( sprintf(
                'AWBU MCP Get Resource Exception [%s]: %s in %s:%d',
                $uri,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ) );
            
            return new WP_Error(
                'resource_exception',
                sprintf( 
                    __( 'Failed to retrieve resource: %s', 'ai-website-builder-unified' ), 
                    $e->getMessage() 
                ),
                array( 'status' => 500 )
            );
        }
    }
}

