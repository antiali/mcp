<?php
/**
 * Test MCP Connection Script
 * 
 * Tests MCP endpoints to ensure they work correctly
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Get REST API base URL
$rest_url = rest_url('awbu/v1/');
$site_url = site_url();

echo "=== MCP Connection Test ===\n\n";
echo "Site URL: {$site_url}\n";
echo "REST API Base: {$rest_url}\n\n";

// Test 1: Server Info (GET)
echo "Test 1: GET /mcp/server-info\n";
echo "URL: {$rest_url}mcp/server-info\n";
$response = wp_remote_get($rest_url . 'mcp/server-info', array(
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'timeout' => 10,
));

if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "\n\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    echo "Status Code: {$code}\n";
    echo "Response: " . substr($body, 0, 500) . "\n";
    
    $data = json_decode($body, true);
    if ($data && isset($data['serverInfo'])) {
        echo "✅ Server Info Found!\n";
        echo "   Name: " . (isset($data['serverInfo']['name']) ? $data['serverInfo']['name'] : 'N/A') . "\n";
        echo "   Version: " . (isset($data['serverInfo']['version']) ? $data['serverInfo']['version'] : 'N/A') . "\n";
    } else {
        echo "❌ Server Info Missing!\n";
    }
}
echo "\n";

// Test 2: Server Info (POST)
echo "Test 2: POST /mcp/server-info\n";
echo "URL: {$rest_url}mcp/server-info\n";
$response = wp_remote_post($rest_url . 'mcp/server-info', array(
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'body' => json_encode(array('method' => 'server-info')),
    'timeout' => 10,
));

if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "\n\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    echo "Status Code: {$code}\n";
    echo "Response: " . substr($body, 0, 500) . "\n";
    
    $data = json_decode($body, true);
    if ($data && isset($data['serverInfo'])) {
        echo "✅ Server Info Found!\n";
    } else {
        echo "❌ Server Info Missing!\n";
    }
}
echo "\n";

// Test 3: Initialize
echo "Test 3: POST /mcp/initialize\n";
echo "URL: {$rest_url}mcp/initialize\n";
$response = wp_remote_post($rest_url . 'mcp/initialize', array(
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'body' => json_encode(array(
        'protocolVersion' => '2024-11-05',
        'capabilities' => array(),
        'clientInfo' => array(
            'name' => 'Test Client',
            'version' => '1.0.0',
        ),
    )),
    'timeout' => 10,
));

if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "\n\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    echo "Status Code: {$code}\n";
    echo "Response: " . substr($body, 0, 500) . "\n";
    
    $data = json_decode($body, true);
    if ($data && isset($data['serverInfo'])) {
        echo "✅ Initialize Success!\n";
        echo "   Protocol Version: " . (isset($data['protocolVersion']) ? $data['protocolVersion'] : 'N/A') . "\n";
        echo "   Server Name: " . (isset($data['serverInfo']['name']) ? $data['serverInfo']['name'] : 'N/A') . "\n";
    } else {
        echo "❌ Initialize Failed - Missing serverInfo!\n";
    }
}
echo "\n";

// Test 4: Generic MCP Handler
echo "Test 4: POST /mcp (with method: server-info)\n";
echo "URL: {$rest_url}mcp\n";
$response = wp_remote_post($rest_url . 'mcp', array(
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'body' => json_encode(array(
        'method' => 'server-info',
    )),
    'timeout' => 10,
));

if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "\n\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    echo "Status Code: {$code}\n";
    echo "Response: " . substr($body, 0, 500) . "\n";
    
    $data = json_decode($body, true);
    if ($data && isset($data['serverInfo'])) {
        echo "✅ Generic Handler Works!\n";
    } else {
        echo "❌ Generic Handler Failed!\n";
    }
}
echo "\n";

// Test 5: Alternative endpoint (serverInfo)
echo "Test 5: GET /mcp/serverInfo (camelCase)\n";
echo "URL: {$rest_url}mcp/serverInfo\n";
$response = wp_remote_get($rest_url . 'mcp/serverInfo', array(
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'timeout' => 10,
));

if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "\n\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    echo "Status Code: {$code}\n";
    echo "Response: " . substr($body, 0, 500) . "\n";
    
    $data = json_decode($body, true);
    if ($data && isset($data['serverInfo'])) {
        echo "✅ Alternative Endpoint Works!\n";
    } else {
        echo "❌ Alternative Endpoint Failed!\n";
    }
}
echo "\n";

echo "=== Test Complete ===\n";

