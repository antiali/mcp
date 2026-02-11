<?php
/**
 * YTrip Quick Fix & Content Creator
 * Upload this file to: /wp-content/plugins/ytrip/
 * Then access: https://zakharioustours.de/wp-content/plugins/ytrip/quick-fix.php
 *
 * @package YTrip
 */

// Load WordPress
require_once(ABSPATH . 'wp-load.php');

// Check authentication
if (!is_user_logged_in()) {
    wp_die('Please <a href="' . wp_login_url() . '">login to WordPress</a> first.');
}

if (!current_user_can('manage_options')) {
    wp_die('You need Administrator permissions to run this script.');
}

echo '<!DOCTYPE html><html><head>';
echo '<meta charset="UTF-8">';
echo '<title>YTrip Quick Fix & Content Creator</title>';
echo '<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; background: #f0f0f1; }
.container { max-width: 1200px; margin: 0 auto; }
.card { background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
h1, h2 { margin-top: 0; }
.success { color: #22c55e; }
.error { color: #ef4444; }
.button { display: inline-block; padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.button:hover { background: #135e96; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #f6f6f6; }
</style>';
echo '</head><body>';

echo '<div class="container">';
echo '<h1>🔧 YTrip Quick Fix & Content Creator</h1>';
echo '<p>User: <strong>' . wp_get_current_user()->display_name . '</strong></p>';

// Process actions
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    echo '<div class="card"><h2>Processing...</h2>';

    switch ($action) {
        case 'create_content':
            ytrip_create_demo_content();
            break;
        case 'fix_capabilities':
            ytrip_fix_capabilities();
            break;
        case 'flush_permalinks':
            ytrip_flush_permalinks();
            break;
    }

    echo '</div>';
}

// Show actions
echo '<div class="card">';
echo '<h2>🚀 Quick Actions</h2>';

echo '<form method="post">';
echo '<p><strong>Step 1: Fix Settings Access</strong></p>';
echo '<p>This fixes the "Sorry, you are not allowed to access this page" error.</p>';
echo '<input type="hidden" name="action" value="fix_capabilities">';
echo '<button type="submit" class="button">✅ Fix Admin Access</button>';
echo '</form>';

echo '<hr>';

echo '<form method="post">';
echo '<p><strong>Step 2: Create Demo Content</strong></p>';
echo '<p>This will create categories, destinations, and tours.</p>';
echo '<label>Number of Categories: <input type="number" name="num_categories" value="4" min="1" max="10"></label><br><br>';
echo '<label>Tours per Destination: <input type="number" name="num_tours" value="4" min="1" max="10"></label><br><br>';
echo '<input type="hidden" name="action" value="create_content">';
echo '<button type="submit" class="button">📦 Create Content</button>';
echo '</form>';

echo '<hr>';

echo '<form method="post">';
echo '<p><strong>Step 3: Flush Permalinks</strong></p>';
echo '<p>Flush permalinks to ensure URLs work correctly.</p>';
echo '<input type="hidden" name="action" value="flush_permalinks">';
echo '<button type="submit" class="button">🔄 Flush Permalinks</button>';
echo '</form>';
echo '</div>';

// Show current stats
echo '<div class="card">';
echo '<h2>📊 Current Stats</h2>';
echo '<table>';
echo '<tr><th>Item</th><th>Count</th></tr>';
echo '<tr><td>Tour Categories</td><td>' . wp_count_terms('ytrip_category') . '</td></tr>';
echo '<tr><td>Destinations</td><td>' . wp_count_terms('ytrip_destination') . '</td></tr>';
echo '<tr><td>Tours (Published)</td><td>' . wp_count_posts('ytrip_tour')->publish . '</td></tr>';
echo '<tr><td>Tours (Drafts)</td><td>' . wp_count_posts('ytrip_tour')->draft . '</td></tr>';
echo '</table>';
echo '</div>';

// Quick links
echo '<div class="card">';
echo '<h2>🔗 Quick Links</h2>';
echo '<p>';
echo '<a href="' . admin_url('admin.php?page=ytrip-settings') . '" class="button">⚙️ YTrip Settings</a> ';
echo '<a href="' . admin_url('edit.php?post_type=ytrip_tour') . '" class="button">🎫 Manage Tours</a> ';
echo '<a href="' . admin_url('edit-tags.php?taxonomy=ytrip_category&post_type=ytrip_tour') . '" class="button">📂 Categories</a> ';
echo '<a href="' . admin_url('edit-tags.php?taxonomy=ytrip_destination&post_type=ytrip_tour') . '" class="button">🌍 Destinations</a> ';
echo '<a href="' . admin_url('post-new.php?post_type=ytrip_tour') . '" class="button">➕ Add Tour</a>';
echo '</p>';
echo '</div>';

echo '</div></body></html>';

/**
 * Functions
 */

function ytrip_create_demo_content() {
    $num_categories = isset($_POST['num_categories']) ? intval($_POST['num_categories']) : 4;
    $num_tours = isset($_POST['num_tours']) ? intval($_POST['num_tours']) : 4;

    echo '<h3>Creating Content...</h3>';

    // Categories
    $categories = array(
        array('name' => 'Adventure Tours', 'description' => 'Exciting adventures for thrill-seekers'),
        array('name' => 'Cultural Experiences', 'description' => 'Immerse yourself in local culture'),
        array('name' => 'Beach & Relaxation', 'description' => 'Sun, sand, and pure relaxation'),
        array('name' => 'City Breaks', 'description' => 'Explore vibrant cities and urban life'),
        array('name' => 'Nature & Wildlife', 'description' => 'Discover amazing wildlife'),
        array('name' => 'Food & Culinary', 'description' => 'Taste local cuisine'),
        array('name' => 'Historical Tours', 'description' => 'Journey through history'),
    );

    echo '<h4>Categories:</h4><ul>';
    $category_ids = array();
    for ($i = 0; $i < min($num_categories, count($categories)); $i++) {
        $cat = $categories[$i];
        $term = wp_insert_term($cat['name'], 'ytrip_category', array(
            'description' => $cat['description'],
            'slug' => sanitize_title($cat['name']),
        ));
        if (!is_wp_error($term)) {
            $category_ids[] = $term['term_id'];
            echo '<li class="success">✓ ' . esc_html($cat['name']) . '</li>';
        } else {
            echo '<li class="error">✗ ' . esc_html($cat['name']) . '</li>';
        }
    }
    echo '</ul>';

    // Destinations
    $destinations = array(
        array('name' => 'Germany', 'description' => 'Beautiful landscapes and history'),
        array('name' => 'Egypt', 'description' => 'Ancient wonders and beaches'),
        array('name' => 'France', 'description' => 'Romantic cities and cuisine'),
        array('name' => 'Italy', 'description' => 'Art, history, and food'),
        array('name' => 'Spain', 'description' => 'Vibrant culture and beaches'),
        array('name' => 'Greece', 'description' => 'Ancient ruins and crystal waters'),
    );

    echo '<h4>Destinations:</h4><ul>';
    $destination_ids = array();
    foreach ($destinations as $dest) {
        $existing = get_term_by('name', $dest['name'], 'ytrip_destination');
        if (!$existing) {
            $term = wp_insert_term($dest['name'], 'ytrip_destination', array(
                'description' => $dest['description'],
                'slug' => sanitize_title($dest['name']),
            ));
            if (!is_wp_error($term)) {
                $destination_ids[] = $term['term_id'];
                echo '<li class="success">✓ ' . esc_html($dest['name']) . '</li>';
            } else {
                echo '<li class="error">✗ ' . esc_html($dest['name']) . '</li>';
            }
        } else {
            $destination_ids[] = $existing->term_id;
            echo '<li>ℹ ' . esc_html($dest['name']) . ' (already exists)</li>';
        }
    }
    echo '</ul>';

    // Tours
    $tour_templates = array(
        array(
            'title' => 'Amazing {destination} Adventure',
            'duration' => 7,
            'price' => 1299,
            'description' => 'Experience the best of {destination} with this incredible adventure tour.',
        ),
        array(
            'title' => '{destination} Cultural Journey',
            'duration' => 10,
            'price' => 1899,
            'description' => 'Immerse yourself in the rich culture of {destination}.',
        ),
        array(
            'title' => 'Ultimate {destination} Experience',
            'duration' => 14,
            'price' => 2499,
            'description' => 'The complete {destination} experience from cities to countryside.',
        ),
        array(
            'title' => '{destination} Explorer Package',
            'duration' => 5,
            'price' => 899,
            'description' => 'A comprehensive tour of {destination} highlights.',
        ),
    );

    echo '<h4>Tours:</h4><ul>';
    $tour_count = 0;
    foreach ($destination_ids as $dest_id) {
        $destination = get_term($dest_id, 'ytrip_destination');
        $dest_name = $destination->name;

        for ($i = 0; $i < min($num_tours, count($tour_templates)); $i++) {
            $template = $tour_templates[$i];
            $title = str_replace('{destination}', $dest_name, $template['title']);
            $description = str_replace('{destination}', $dest_name, $template['description']);
            $price = $template['price'] + rand(-100, 100);

            $post_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $description,
                'post_status' => 'publish',
                'post_type' => 'ytrip_tour',
                'post_excerpt' => wp_trim_words($description, 20),
            ));

            if (!is_wp_error($post_id)) {
                wp_set_object_terms($post_id, array($dest_id), 'ytrip_destination');
                if (!empty($category_ids)) {
                    wp_set_object_terms($post_id, array($category_ids[array_rand($category_ids)]), 'ytrip_category');
                }
                update_post_meta($post_id, 'ytrip_tour_details', array(
                    'duration' => $template['duration'],
                    'price' => $price,
                    'max_group_size' => rand(10, 30),
                    'min_age' => rand(6, 18),
                    'difficulty' => array('Easy', 'Moderate', 'Hard')[array_rand(array('Easy', 'Moderate', 'Hard'))],
                    'inquiry_email' => get_option('admin_email'),
                ));
                $tour_count++;
                echo '<li class="success">✓ ' . esc_html($title) . ' - €' . $price . '</li>';
            }
        }
    }
    echo '</ul>';

    echo '<p class="success"><strong>✅ Created ' . $tour_count . ' tours!</strong></p>';
}

function ytrip_fix_capabilities() {
    echo '<h3>Fixing Admin Capabilities...</h3>';

    // Check if user is admin
    $user = wp_get_current_user();
    if (in_array('administrator', $user->roles)) {
        echo '<p class="success">✅ User is Administrator - capabilities should be fine.</p>';
        echo '<p>Try accessing <a href="' . admin_url('admin.php?page=ytrip-settings') . '">YTrip Settings</a></p>';
    } else {
        echo '<p class="error">⚠️ User is not Administrator!</p>';
        echo '<p>Current roles: ' . implode(', ', $user->roles) . '</p>';
        echo '<p>Please log in as an Administrator account.</p>';
    }

    // Flush capabilities
    wp_flush_roles();
    echo '<p class="success">✅ Flushed user roles and capabilities.</p>';
}

function ytrip_flush_permalinks() {
    echo '<h3>Flushing Permalinks...</h3>';
    flush_rewrite_rules();
    echo '<p class="success">✅ Permalinks flushed successfully.</p>';
    echo '<p>REST API should now work at:</p>';
    echo '<code>https://zakharioustours.de/wp-json/ytrip/v1/test-access</code>';
}
