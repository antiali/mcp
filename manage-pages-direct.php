<?php
/**
 * Direct Page Management Script
 * Run this via WordPress REST API or directly
 */

// Required pages
$required_pages = array(
    'home' => array(
        'title' => 'Home - Peta Alex For Peralite Manufacturing',
        'slug' => 'home',
    ),
    'about' => array(
        'title' => 'About Us - Peta Alex',
        'slug' => 'about',
    ),
    'products' => array(
        'title' => 'Products - Peta Alex',
        'slug' => 'products',
    ),
    'contact' => array(
        'title' => 'Contact Us - Peta Alex',
        'slug' => 'contact',
    ),
);

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

echo "=== Current Pages ===\n";
$pages_to_delete = array();
$required_slugs = array_column($required_pages, 'slug');
$system_pages = array('sample-page', 'privacy-policy');

foreach ($all_pages as $page) {
    $slug = $page->post_name;
    $title = $page->post_title;
    $id = $page->ID;
    
    echo "ID: {$id} | Slug: {$slug} | Title: {$title} | Status: {$page->post_status}\n";
    
    // Mark for deletion if not required and not system page
    if (!in_array($slug, $required_slugs) && 
        !in_array($slug, $system_pages) &&
        $page->post_status !== 'trash') {
        $pages_to_delete[] = $id;
    }
}

echo "\n=== Deleting Unused Pages ===\n";
foreach ($pages_to_delete as $page_id) {
    $page = get_post($page_id);
    if ($page) {
        $deleted = wp_delete_post($page_id, true);
        if ($deleted) {
            echo "✓ Deleted: ID {$page_id} - {$page->post_title}\n";
        } else {
            echo "✗ Failed to delete: ID {$page_id}\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Total pages found: " . count($all_pages) . "\n";
echo "Pages deleted: " . count($pages_to_delete) . "\n";
echo "Required pages: " . count($required_pages) . "\n";

return array(
    'deleted' => count($pages_to_delete),
    'required' => count($required_pages),
);

