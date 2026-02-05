<?php
/**
 * Cleanup and Create Pages Script
 * Run this directly on WordPress server or via WP-CLI
 * 
 * Usage: php cleanup-and-create-pages.php
 * Or access via browser: yoursite.com/wp-content/plugins/YMCP/cleanup-and-create-pages.php
 */

// Load WordPress
if (!defined('ABSPATH')) {
    // Try to find wp-load.php
    $wp_load_paths = array(
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../wp-load.php',
    );
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('Error: Could not find wp-load.php. Please run this script from WordPress root or via WP-CLI.');
    }
}

// Required pages - Content embedded directly
$required_pages = array(
    'home' => array(
        'title' => 'Home - Peta Alex For Peralite Manufacturing',
        'slug' => 'home',
        'content' => '', // Will be set below
    ),
    'about' => array(
        'title' => 'About Us - Peta Alex',
        'slug' => 'about',
        'content' => '', // Will be set below
    ),
    'products' => array(
        'title' => 'Products - Peta Alex',
        'slug' => 'products',
        'content' => '', // Will be set below
    ),
    'contact' => array(
        'title' => 'Contact Us - Peta Alex',
        'slug' => 'contact',
        'content' => '', // Will be set below
    ),
);

// Try to load content from files, fallback to embedded content
$content_files = array(
    'home' => __DIR__ . '/peta-alex-website/pages-content/home-gutenberg.txt',
    'about' => __DIR__ . '/peta-alex-website/pages-content/about-gutenberg.txt',
    'products' => __DIR__ . '/peta-alex-website/pages-content/products-gutenberg.txt',
    'contact' => __DIR__ . '/peta-alex-website/pages-content/contact-gutenberg.txt',
);

foreach ($content_files as $key => $file_path) {
    if (file_exists($file_path) && is_readable($file_path)) {
        $required_pages[$key]['content'] = file_get_contents($file_path);
    }
}

echo "=== Peta Alex Pages Management ===\n\n";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

echo "Total pages found: " . count($all_pages) . "\n\n";

// Delete unused pages
$required_slugs = array_column($required_pages, 'slug');
$system_pages = array('sample-page', 'privacy-policy');
$deleted_count = 0;

echo "=== Deleting Unused Pages ===\n";
foreach ($all_pages as $page) {
    $slug = $page->post_name;
    
    if (!in_array($slug, $required_slugs) && 
        !in_array($slug, $system_pages) &&
        $page->post_status !== 'trash') {
        
        $deleted = wp_delete_post($page->ID, true);
        if ($deleted) {
            echo "✓ Deleted: ID {$page->ID} - {$page->post_title} (slug: {$slug})\n";
            $deleted_count++;
        } else {
            echo "✗ Failed to delete: ID {$page->ID} - {$page->post_title}\n";
        }
    }
}

echo "\nDeleted {$deleted_count} page(s)\n\n";

// Create or update required pages
echo "=== Creating/Updating Required Pages ===\n";
$created_count = 0;
$updated_count = 0;

foreach ($required_pages as $key => $page_data) {
    $title = $page_data['title'];
    $slug = $page_data['slug'];
    $content = isset($page_data['content']) ? $page_data['content'] : '';
    
    // Check if file exists, if not use default content
    if (empty($content) && isset($page_data['file_path'])) {
        $file_path = __DIR__ . '/' . ltrim($page_data['file_path'], '/');
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
        }
    }
    
    // Fallback content if still empty
    if (empty($content)) {
        $content = "<!-- wp:paragraph -->\n<p>Content for {$title}</p>\n<!-- /wp:paragraph -->";
    }
    
    // Check if page exists
    $existing_page = get_page_by_path($slug);
    
    $page_args = array(
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => $slug,
    );
    
    if ($existing_page) {
        $page_args['ID'] = $existing_page->ID;
        $page_id = wp_update_post($page_args, true);
        $action = 'updated';
        $updated_count++;
    } else {
        $page_id = wp_insert_post($page_args, true);
        $action = 'created';
        $created_count++;
    }
    
    if (is_wp_error($page_id)) {
        echo "✗ Error {$action} page '{$title}': " . $page_id->get_error_message() . "\n";
    } else {
        $url = get_permalink($page_id);
        echo "✓ {$action}: ID {$page_id} - {$title} ({$slug})\n";
        echo "  URL: {$url}\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total pages before: " . count($all_pages) . "\n";
echo "Pages deleted: {$deleted_count}\n";
echo "Pages created: {$created_count}\n";
echo "Pages updated: {$updated_count}\n";
echo "Required pages: " . count($required_pages) . "\n";

// Set homepage if needed
$home_page = get_page_by_path('home');
if ($home_page) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home_page->ID);
    echo "\n✓ Homepage set to: Home - Peta Alex For Peralite Manufacturing\n";
}

echo "\n=== Done! ===\n";

