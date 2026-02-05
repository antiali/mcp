<?php
/**
 * AWBU Plugin Test Script
 * 
 * Run this from command line: php test-awbu.php
 * Or access via browser: https://yoursite.com/wp-content/plugins/YMCP/test-awbu.php
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

echo "=== AWBU Plugin Test Suite ===\n\n";

$tests_passed = 0;
$tests_failed = 0;

function test($name, $callback) {
    global $tests_passed, $tests_failed;
    echo "Testing: $name... ";
    try {
        $result = $callback();
        if ($result === true) {
            echo "✓ PASSED\n";
            $tests_passed++;
        } else {
            echo "✗ FAILED: $result\n";
            $tests_failed++;
        }
    } catch (Exception $e) {
        echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
}

// Test 1: Plugin loaded
test('Plugin loaded', function() {
    return class_exists('AI_Website_Builder_Unified');
});

// Test 2: MCP Server class exists
test('MCP Server class exists', function() {
    return class_exists('AWBU_MCP_Server');
});

// Test 3: MCP Server can be instantiated
test('MCP Server can be instantiated', function() {
    try {
        $server = new AWBU_MCP_Server();
        return is_object($server);
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// Test 4: MCP Server get_server_info works
test('MCP Server get_server_info works', function() {
    try {
        $server = new AWBU_MCP_Server();
        $info = $server->get_server_info();
        if (!isset($info['protocolVersion'])) {
            return "Missing protocolVersion";
        }
        if (!isset($info['capabilities'])) {
            return "Missing capabilities";
        }
        if (!isset($info['serverInfo'])) {
            return "Missing serverInfo";
        }
        return true;
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// Test 5: AI Orchestrator class exists
test('AI Orchestrator class exists', function() {
    return class_exists('AWBU_AI_Orchestrator') || class_exists('AISBP\AI_Orchestrator');
});

// Test 6: REST API endpoints registered
test('REST API endpoints registered', function() {
    $routes = rest_get_server()->get_routes();
    $mcp_routes = array_filter($routes, function($key) {
        return strpos($key, 'awbu/v1/mcp') !== false;
    }, ARRAY_FILTER_USE_KEY);
    return count($mcp_routes) > 0;
});

// Test 7: MCP initialize endpoint accessible
test('MCP initialize endpoint accessible', function() {
    $routes = rest_get_server()->get_routes();
    return isset($routes['/awbu/v1/mcp/initialize']);
});

// Test 8: MCP server-info endpoint accessible
test('MCP server-info endpoint accessible', function() {
    $routes = rest_get_server()->get_routes();
    return isset($routes['/awbu/v1/mcp/server-info']);
});

// Test 9: AI Connectors exist
test('AI Connectors exist', function() {
    $connectors = array(
        'AWBU_DeepSeek_Connector',
        'AWBU_OpenAI_Connector',
        'AWBU_Claude_Connector',
        'AWBU_Gemini_Connector',
    );
    foreach ($connectors as $connector) {
        if (!class_exists($connector)) {
            return "Missing: $connector";
        }
    }
    return true;
});

// Test 10: Database class exists
test('Database class exists', function() {
    return class_exists('AISBP\Database');
});

echo "\n=== Test Results ===\n";
echo "Passed: $tests_passed\n";
echo "Failed: $tests_failed\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";

if ($tests_failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed. Please check the errors above.\n";
    exit(1);
}

