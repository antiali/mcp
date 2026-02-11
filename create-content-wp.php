<?php
/**
 * WordPress Remote Content Creator
 * Use this via command line: php create-content-wp.php
 * @author Pi
 */

// WordPress Configuration
$site_url = 'https://zakharioustours.de';
$username = 'zakharious';
$app_password = 'tDeO iZyX kOIo Eg4K kLd2 DPxx';

/**
 * Create Content via WordPress REST API
 */

// Base64 encode credentials
$credentials = base64_encode("$username:$app_password");

// Function to make REST API requests
function wp_rest_request($endpoint, $method = 'GET', $data = null) {
    global $site_url, $credentials;

    $url = $site_url . '/wp-json' . $endpoint;

    $headers = array(
        'Authorization' => 'Basic ' . $credentials,
        'Content-Type' => 'application/json',
    );

    $args = array(
        'method' => $method,
        'headers' => $headers,
        'timeout' => 30,
    );

    if ($data !== null) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'error' => $response->get_error_message(),
        );
    }

    $body = wp_remote_retrieve_body($response);
    $code = wp_remote_retrieve_response_code($response);

    if ($code !== 200 && $code !== 201) {
        return array(
            'success' => false,
            'error' => "HTTP $code",
            'body' => $body,
        );
    }

    return array(
        'success' => true,
        'data' => json_decode($body, true),
    );
}

// Check if WordPress is available
echo "Checking WordPress REST API...\n";
$check = wp_rest_request('/');

if (!$check['success']) {
    echo "❌ Error: " . $check['error'] . "\n";
    echo "Make sure:\n";
    echo "1. WordPress is running at: $site_url\n";
    echo "2. REST API is enabled\n";
    echo "3. Application password is correct\n";
    echo "4. User has 'edit_posts' capability\n";
    exit(1);
}

echo "✅ WordPress REST API is working\n";
echo "Authenticated as: " . ($check['data']['name'] ?? 'Unknown') . "\n\n";

// Check if ytrip endpoints exist
echo "Checking YTrip endpoints...\n";
$endpoints_check = wp_rest_request('/ytrip/v1/test-access');

if (!$endpoints_check['success']) {
    echo "❌ YTrip endpoints not found (404)\n";
    echo "This means the plugin needs to be updated on the server.\n";
    echo "Please upload quick-fix.php to: $site_url/wp-content/plugins/ytrip/\n";
    echo "Then access: $site_url/wp-content/plugins/ytrip/quick-fix.php\n";
    echo "Click 'Flush Permalinks' to enable REST API endpoints.\n\n";
} else {
    echo "✅ YTrip REST API endpoints are available\n\n";

    // Create Content
    echo "Creating demo content...\n";

    $create_response = wp_rest_request('/ytrip/v1/create-content', 'POST', array(
        'num_categories' => 4,
        'num_tours' => 4,
    ));

    if ($create_response['success']) {
        echo "✅ Content created successfully!\n";
        echo "   Categories: " . $create_response['data']['created']['categories'] . "\n";
        echo "   Destinations: " . $create_response['data']['created']['destinations'] . "\n";
        echo "   Tours: " . $create_response['data']['created']['tours'] . "\n\n";
        echo "View tours at: $site_url/tours/\n";
    } else {
        echo "❌ Error creating content: " . $create_response['error'] . "\n";
        if (isset($create_response['body'])) {
            echo "Response: " . $create_response['body'] . "\n";
        }
    }
}
