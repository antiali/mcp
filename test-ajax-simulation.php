<?php
/**
 * AJAX Simulation Test
 * 
 * Simulates AJAX request to test ajax_generate
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

echo "=== AJAX Generate Simulation Test ===\n\n";

// Simulate POST data
$_POST = array(
    'action' => 'aisbp_generate',
    'nonce' => wp_create_nonce('awbu_nonce'),
    'model' => 'deepseek',
    'description' => 'Test website',
    'phase' => 1,
    'creation_mode' => 'full_site',
    'session_id' => 'test_' . time(),
);

echo "Simulating AJAX request...\n";
echo "POST data: " . json_encode($_POST, JSON_PRETTY_PRINT) . "\n\n";

// Capture output
ob_start();

// Call the method directly (simulating AJAX)
try {
    $awbu->ajax_generate();
    $output = ob_get_clean();
    
    echo "Output captured:\n";
    echo substr($output, 0, 1000) . "\n";
    
    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "\n✓ Valid JSON response\n";
        echo "Status: " . (isset($json['success']) ? 'success' : 'error') . "\n";
        if (isset($json['data'])) {
            echo "Has data: yes\n";
        }
    } else {
        echo "\n✗ Invalid JSON response\n";
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo "Output: " . substr($output, 0, 500) . "\n";
} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Output: " . substr($output, 0, 500) . "\n";
}

echo "\n=== Test Complete ===\n";

