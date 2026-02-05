<?php
/**
 * MCP Endpoint Test Script
 * 
 * Tests MCP endpoints directly
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Initialize plugin
if (class_exists('AI_Website_Builder_Unified')) {
    $awbu = AI_Website_Builder_Unified::instance();
} else {
    die('Plugin not loaded');
}

echo "=== MCP Endpoint Test ===\n\n";

// Test 1: Get server info via REST
echo "Test 1: Testing rest_get_server_info...\n";
try {
    $request = new WP_REST_Request('GET', '/awbu/v1/mcp/server-info');
    $response = $awbu->rest_get_server_info($request);
    
    if (is_wp_error($response)) {
        echo "✗ Error: " . $response->get_error_message() . "\n";
    } else {
        $data = $response->get_data();
        echo "✓ Success!\n";
        echo "  Protocol Version: " . (isset($data['protocolVersion']) ? $data['protocolVersion'] : 'missing') . "\n";
        echo "  Has Capabilities: " . (isset($data['capabilities']) ? 'yes' : 'no') . "\n";
        echo "  Has Server Info: " . (isset($data['serverInfo']) ? 'yes' : 'no') . "\n";
        if (isset($data['serverInfo']['name'])) {
            echo "  Server Name: " . $data['serverInfo']['name'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Initialize endpoint
echo "Test 2: Testing rest_mcp_initialize...\n";
try {
    $request = new WP_REST_Request('POST', '/awbu/v1/mcp/initialize');
    $request->set_body(json_encode(array(
        'clientInfo' => array(
            'name' => 'Test Client',
            'version' => '1.0.0'
        )
    )));
    $response = $awbu->rest_mcp_initialize($request);
    
    if (is_wp_error($response)) {
        echo "✗ Error: " . $response->get_error_message() . "\n";
    } else {
        $data = $response->get_data();
        echo "✓ Success!\n";
        echo "  Protocol Version: " . (isset($data['protocolVersion']) ? $data['protocolVersion'] : 'missing') . "\n";
        echo "  Has Capabilities: " . (isset($data['capabilities']) ? 'yes' : 'no') . "\n";
        echo "  Has Server Info: " . (isset($data['serverInfo']) ? 'yes' : 'no') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";

