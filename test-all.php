<?php
/**
 * Comprehensive AWBU Plugin Test Suite
 * 
 * Run: php test-all.php
 * Or via browser (if WordPress is accessible)
 */

// Prevent direct access
if (php_sapi_name() !== 'cli' && !defined('ABSPATH')) {
    // Try to load WordPress if accessed via browser
    $wp_load = '../../../wp-load.php';
    if (file_exists($wp_load)) {
        require_once($wp_load);
    } else {
        die('WordPress not found. Run from command line or ensure wp-load.php is accessible.');
    }
}

if (!defined('ABSPATH')) {
    die('WordPress not loaded. Please run from WordPress root or ensure wp-load.php is accessible.');
}

echo "=== AWBU Comprehensive Test Suite ===\n\n";

$results = array('passed' => 0, 'failed' => 0, 'errors' => array());

function test($name, $callback) {
    global $results;
    echo "Testing: $name... ";
    try {
        $result = $callback();
        if ($result === true) {
            echo "✓ PASSED\n";
            $results['passed']++;
            return true;
        } else {
            echo "✗ FAILED: $result\n";
            $results['failed']++;
            $results['errors'][] = "$name: $result";
            return false;
        }
    } catch (Exception $e) {
        echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
        $results['failed']++;
        $results['errors'][] = "$name: Exception - " . $e->getMessage();
        return false;
    } catch (Throwable $e) {
        echo "✗ FATAL: " . $e->getMessage() . "\n";
        $results['failed']++;
        $results['errors'][] = "$name: Fatal - " . $e->getMessage();
        return false;
    }
}

// ============================================
// CORE PLUGIN TESTS
// ============================================
echo "--- Core Plugin Tests ---\n";

test('Plugin main class exists', function() {
    return class_exists('AI_Website_Builder_Unified');
});

test('Plugin instance can be created', function() {
    try {
        $instance = AI_Website_Builder_Unified::instance();
        return is_object($instance);
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// ============================================
// MCP SERVER TESTS
// ============================================
echo "\n--- MCP Server Tests ---\n";

test('MCP Server class exists', function() {
    return class_exists('AWBU_MCP_Server');
});

test('MCP Server can be instantiated', function() {
    try {
        $server = new AWBU_MCP_Server();
        return is_object($server);
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

test('MCP Server get_server_info returns valid data', function() {
    try {
        $server = new AWBU_MCP_Server();
        $info = $server->get_server_info();
        
        if (!is_array($info)) {
            return "Not an array";
        }
        if (!isset($info['protocolVersion'])) {
            return "Missing protocolVersion";
        }
        if (!isset($info['capabilities'])) {
            return "Missing capabilities";
        }
        if (!isset($info['serverInfo'])) {
            return "Missing serverInfo";
        }
        if (!isset($info['serverInfo']['name'])) {
            return "Missing serverInfo.name";
        }
        return true;
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

test('MCP Server list_tools works', function() {
    try {
        $server = new AWBU_MCP_Server();
        $tools = $server->list_tools();
        return is_array($tools) && count($tools) > 0;
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// ============================================
// REST API TESTS
// ============================================
echo "\n--- REST API Tests ---\n";

test('REST API routes registered', function() {
    $routes = rest_get_server()->get_routes();
    $awbu_routes = array_filter($routes, function($key) {
        return strpos($key, 'awbu/v1') !== false;
    }, ARRAY_FILTER_USE_KEY);
    return count($awbu_routes) > 0;
});

test('MCP initialize endpoint exists', function() {
    $routes = rest_get_server()->get_routes();
    return isset($routes['/awbu/v1/mcp/initialize']);
});

test('MCP server-info endpoint exists', function() {
    $routes = rest_get_server()->get_routes();
    return isset($routes['/awbu/v1/mcp/server-info']);
});

test('MCP handler endpoint exists', function() {
    $routes = rest_get_server()->get_routes();
    return isset($routes['/awbu/v1/mcp']);
});

// Test REST endpoints directly
test('rest_get_server_info returns valid response', function() {
    try {
        $awbu = AI_Website_Builder_Unified::instance();
        $request = new WP_REST_Request('GET', '/awbu/v1/mcp/server-info');
        $response = $awbu->rest_get_server_info($request);
        
        if (is_wp_error($response)) {
            return "WP_Error: " . $response->get_error_message();
        }
        
        $data = $response->get_data();
        if (!isset($data['protocolVersion'])) {
            return "Missing protocolVersion in response";
        }
        if (!isset($data['serverInfo'])) {
            return "Missing serverInfo in response";
        }
        return true;
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// ============================================
// AI ORCHESTRATOR TESTS
// ============================================
echo "\n--- AI Orchestrator Tests ---\n";

test('AI Orchestrator class exists', function() {
    return class_exists('AWBU_AI_Orchestrator') || class_exists('AISBP\AI_Orchestrator');
});

test('AI Orchestrator can be instantiated', function() {
    try {
        if (class_exists('AWBU_AI_Orchestrator')) {
            $orchestrator = new AWBU_AI_Orchestrator();
        } elseif (class_exists('AISBP\AI_Orchestrator')) {
            $orchestrator = new \AISBP\AI_Orchestrator();
        } else {
            return "No orchestrator class found";
        }
        return is_object($orchestrator);
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
});

// ============================================
// AI CONNECTORS TESTS
// ============================================
echo "\n--- AI Connectors Tests ---\n";

$connectors = array(
    'AWBU_DeepSeek_Connector',
    'AWBU_OpenAI_Connector',
    'AWBU_Claude_Connector',
    'AWBU_Gemini_Connector',
);

foreach ($connectors as $connector) {
    test("$connector class exists", function() use ($connector) {
        return class_exists($connector);
    });
    
    test("$connector can be instantiated", function() use ($connector) {
        try {
            $instance = new $connector();
            return is_object($instance);
        } catch (Exception $e) {
            return "Exception: " . $e->getMessage();
        }
    });
}

// ============================================
// DATABASE TESTS
// ============================================
echo "\n--- Database Tests ---\n";

test('Database class exists', function() {
    return class_exists('AISBP\Database');
});

// ============================================
// RESULTS
// ============================================
echo "\n=== Test Results ===\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";
echo "Total: " . ($results['passed'] + $results['failed']) . "\n";

if (!empty($results['errors'])) {
    echo "\nErrors:\n";
    foreach ($results['errors'] as $error) {
        echo "  - $error\n";
    }
}

if ($results['failed'] === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed. Please check the errors above.\n";
    exit(1);
}

