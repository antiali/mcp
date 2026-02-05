<?php
/**
 * Plugin Name: YMCP Connector
 * Plugin URI: https://yoursite.com/ymcp-connector
 * Description: إضافة خفيفة للربط عن بُعد مع YMCP - تسمح بالتحكم الكامل في الموقع من IDE أو Dashboard
 * Version: 1.0.0
 * Author: Expert Developer
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * Text Domain: ymcp-connector
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

define('YMCP_CONNECTOR_VERSION', '1.0.0');
define('YMCP_CONNECTOR_DIR', plugin_dir_path(__FILE__));

/**
 * YMCP Connector - Full Remote Control
 */
class YMCP_Connector {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register REST API Routes
     */
    public function register_routes() {
        $namespace = 'ymcp-connector/v1';
        
        // Site Info & Health
        register_rest_route($namespace, '/info', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_info'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/health', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_health'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/diagnose', array(
            'methods' => 'GET',
            'callback' => array($this, 'diagnose_site'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Pages Management
        register_rest_route($namespace, '/pages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pages'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/pages/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_page'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/pages/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_page'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/pages/delete', array(
            'methods' => 'POST',
            'callback' => array($this, 'delete_page'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Posts Management
        register_rest_route($namespace, '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/posts/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_post'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/posts/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_post'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Theme Builder (Divi)
        register_rest_route($namespace, '/theme-builder/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_theme_templates'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/theme-builder/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_theme_template'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/theme-builder/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_theme_template'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Database Operations
        register_rest_route($namespace, '/database/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'database_search'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/database/replace', array(
            'methods' => 'POST',
            'callback' => array($this, 'database_replace'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Options Management
        register_rest_route($namespace, '/options/get', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_options'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/options/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_options'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Media Upload
        register_rest_route($namespace, '/media/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_media'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Plugins & Themes
        register_rest_route($namespace, '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_plugins'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/themes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_themes'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Change Log & Rollback
        register_rest_route($namespace, '/changelog', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_changelog'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        register_rest_route($namespace, '/rollback', array(
            'methods' => 'POST',
            'callback' => array($this, 'rollback_change'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Execute Custom Action
        register_rest_route($namespace, '/execute', array(
            'methods' => 'POST',
            'callback' => array($this, 'execute_action'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Manage Pages - List, Delete, Create/Update
        register_rest_route($namespace, '/pages/manage', array(
            'methods' => 'POST',
            'callback' => array($this, 'manage_pages'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }
    
    /**
     * Check API Permission
     * Enhanced to support multiple authentication methods for easier connection
     */
    public function check_permission($request) {
        // Method 1: Check for Application Password authentication
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return true;
        }
        
        // Method 2: Check custom API key from header
        $api_key = $request->get_header('X-YMCP-API-Key');
        if (empty($api_key)) {
            // Try alternative header names
            $api_key = $request->get_header('X-MCP-API-Key');
        }
        if (empty($api_key)) {
            // Try Authorization header
            $auth_header = $request->get_header('Authorization');
            if ($auth_header && strpos($auth_header, 'Bearer ') === 0) {
                $api_key = substr($auth_header, 7);
            }
        }
        if (empty($api_key)) {
            // Try query parameter (for testing)
            $api_key = $request->get_param('api_key');
        }
        
        $stored_key = get_option('ymcp_connector_api_key');
        
        // If API key provided and matches stored key
        if ($api_key && $stored_key && hash_equals($stored_key, $api_key)) {
            return true;
        }
        
        // Method 3: First time setup - if no stored key exists, allow access with any API key
        // This allows initial connection before API key is set
        if (empty($stored_key) && !empty($api_key)) {
            // Auto-generate and store API key for future use
            update_option('ymcp_connector_api_key', $api_key);
            return true;
        }
        
        // Method 4: For MCP endpoints, be more lenient (allow if user is logged in)
        $route = $request->get_route();
        if (strpos($route, '/mcp') !== false && is_user_logged_in()) {
            return true;
        }
        
        return new WP_Error('rest_forbidden', 'غير مصرح - يرجى التحقق من API Key', array('status' => 403));
    }
    
    /**
     * Get Site Info
     */
    public function get_site_info() {
        global $wp_version;
        
        $theme = wp_get_theme();
        $is_divi = in_array($theme->get_template(), array('Divi', 'divi'));
        $divi_version = defined('ET_BUILDER_VERSION') ? ET_BUILDER_VERSION : null;
        
        return array(
            'success' => true,
            'data' => array(
                'site_url' => get_site_url(),
                'site_name' => get_bloginfo('name'),
                'wp_version' => $wp_version,
                'php_version' => PHP_VERSION,
                'theme' => array(
                    'name' => $theme->get('Name'),
                    'version' => $theme->get('Version'),
                    'is_divi' => $is_divi,
                ),
                'divi_version' => $divi_version,
                'is_multisite' => is_multisite(),
                'language' => get_locale(),
                'timezone' => wp_timezone_string(),
                'connector_version' => YMCP_CONNECTOR_VERSION,
            ),
        );
    }
    
    /**
     * Get Site Health
     */
    public function get_site_health() {
        $health = array(
            'status' => 'good',
            'issues' => array(),
            'metrics' => array(),
        );
        
        // Check memory
        $memory_limit = ini_get('memory_limit');
        $health['metrics']['memory_limit'] = $memory_limit;
        
        // Check max execution time
        $max_execution = ini_get('max_execution_time');
        $health['metrics']['max_execution_time'] = $max_execution;
        
        // Check upload size
        $upload_size = ini_get('upload_max_filesize');
        $health['metrics']['upload_max_filesize'] = $upload_size;
        
        // Check if Divi installed
        if (!defined('ET_BUILDER_VERSION')) {
            $health['issues'][] = array(
                'type' => 'warning',
                'message' => 'Divi Builder غير مثبت',
            );
        }
        
        // Check SSL
        if (!is_ssl()) {
            $health['issues'][] = array(
                'type' => 'warning',
                'message' => 'الموقع لا يستخدم HTTPS',
            );
        }
        
        // Check debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $health['issues'][] = array(
                'type' => 'info',
                'message' => 'وضع التصحيح (Debug) مفعل',
            );
        }
        
        if (count($health['issues']) > 0) {
            $health['status'] = 'has_issues';
        }
        
        return array('success' => true, 'data' => $health);
    }
    
    /**
     * Diagnose Site - Full Analysis
     */
    public function diagnose_site() {
        $diagnosis = array(
            'plugins' => array(),
            'theme' => array(),
            'database' => array(),
            'performance' => array(),
            'security' => array(),
        );
        
        // Active Plugins
        $active_plugins = get_option('active_plugins', array());
        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false);
            $diagnosis['plugins'][] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'file' => $plugin,
            );
        }
        
        // Theme Info
        $theme = wp_get_theme();
        $diagnosis['theme'] = array(
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'parent' => $theme->parent() ? $theme->parent()->get('Name') : null,
        );
        
        // Database Stats
        global $wpdb;
        $diagnosis['database']['prefix'] = $wpdb->prefix;
        $diagnosis['database']['tables_count'] = count($wpdb->get_results("SHOW TABLES"));
        
        // Posts count
        $diagnosis['database']['posts'] = wp_count_posts()->publish;
        $diagnosis['database']['pages'] = wp_count_posts('page')->publish;
        
        // Performance
        $diagnosis['performance']['object_cache'] = wp_using_ext_object_cache();
        $diagnosis['performance']['opcache'] = function_exists('opcache_get_status');
        
        // Security
        $diagnosis['security']['file_editing'] = !defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT;
        $diagnosis['security']['debug_display'] = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
        
        return array('success' => true, 'data' => $diagnosis);
    }
    
    /**
     * Get Pages
     */
    public function get_pages($request) {
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => $request->get_param('limit') ?: 50,
            'post_status' => 'any',
        );
        
        $pages = get_posts($args);
        $result = array();
        
        foreach ($pages as $page) {
            $result[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'status' => $page->post_status,
                'url' => get_permalink($page->ID),
                'modified' => $page->post_modified,
                'template' => get_page_template_slug($page->ID),
            );
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Create Page
     */
    public function create_page($request) {
        $params = $request->get_json_params();
        
        $page_data = array(
            'post_type' => 'page',
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => $params['content'] ?? '',
            'post_status' => $params['status'] ?? 'draft',
            'post_name' => sanitize_title($params['slug'] ?? $params['title']),
        );
        
        $page_id = wp_insert_post($page_data);
        
        if (is_wp_error($page_id)) {
            return array('success' => false, 'error' => $page_id->get_error_message());
        }
        
        // Set Divi 5 content if provided (check if content is JSON)
        $content = $params['content'] ?? '';
        if (!empty($content)) {
            $trimmed_content = trim($content);
            // Check if it's Divi 5 JSON format (starts with JSON array or object)
            if (substr($trimmed_content, 0, 1) === '[' || substr($trimmed_content, 0, 1) === '{') {
                // Validate JSON
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                    // Valid JSON - set as Divi 5 content
                    update_post_meta($page_id, '_et_pb_use_builder', 'on');
                    update_post_meta($page_id, '_et_builder_version', defined('ET_BUILDER_VERSION') ? ET_BUILDER_VERSION : '5.0');
                    update_post_meta($page_id, '_et_pb_old_content', '');
                    update_post_meta($page_id, '_et_pb_page_layout', 'et_no_sidebar');
                    wp_update_post(array('ID' => $page_id, 'post_content' => $content));
                }
            }
        }
        
        // Set Divi content if provided (legacy support)
        if (!empty($params['divi_content'])) {
            update_post_meta($page_id, '_et_pb_use_builder', 'on');
            update_post_meta($page_id, '_et_builder_version', ET_BUILDER_VERSION ?? '4.0');
            update_post_meta($page_id, '_et_pb_old_content', '');
            wp_update_post(array('ID' => $page_id, 'post_content' => $params['divi_content']));
        }
        
        // Set page template
        if (!empty($params['template'])) {
            update_post_meta($page_id, '_wp_page_template', $params['template']);
        }
        
        // Log change
        $this->log_change('page_created', $page_id, $page_data);
        
        return array(
            'success' => true,
            'data' => array(
                'id' => $page_id,
                'url' => get_permalink($page_id),
                'edit_url' => admin_url('post.php?post=' . $page_id . '&action=edit'),
            ),
        );
    }
    
    /**
     * Update Page
     */
    public function update_page($request) {
        $params = $request->get_json_params();
        $page_id = intval($params['id']);
        
        if (!$page_id) {
            return array('success' => false, 'error' => 'Page ID required');
        }
        
        // Store snapshot for rollback
        $this->create_snapshot($page_id);
        
        $update_data = array('ID' => $page_id);
        
        if (isset($params['title'])) {
            $update_data['post_title'] = sanitize_text_field($params['title']);
        }
        if (isset($params['content'])) {
            $update_data['post_content'] = $params['content'];
        }
        if (isset($params['status'])) {
            $update_data['post_status'] = $params['status'];
        }
        
        $result = wp_update_post($update_data);
        
        if (is_wp_error($result)) {
            return array('success' => false, 'error' => $result->get_error_message());
        }
        
        // Update Divi meta if provided
        if (!empty($params['divi_content'])) {
            update_post_meta($page_id, '_et_pb_use_builder', 'on');
            wp_update_post(array('ID' => $page_id, 'post_content' => $params['divi_content']));
        }
        
        $this->log_change('page_updated', $page_id, $params);
        
        return array('success' => true, 'data' => array('id' => $page_id));
    }
    
    /**
     * Delete Page
     */
    public function delete_page($request) {
        $params = $request->get_json_params();
        $page_id = intval($params['id']);
        
        $this->create_snapshot($page_id);
        
        $result = wp_delete_post($page_id, $params['force'] ?? false);
        
        $this->log_change('page_deleted', $page_id, array());
        
        return array('success' => (bool)$result);
    }
    
    /**
     * Get Posts
     */
    public function get_posts($request) {
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $request->get_param('limit') ?: 50,
            'post_status' => 'any',
        );
        
        $posts = get_posts($args);
        $result = array();
        
        foreach ($posts as $post) {
            $result[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'status' => $post->post_status,
                'url' => get_permalink($post->ID),
                'modified' => $post->post_modified,
            );
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Create Post
     */
    public function create_post($request) {
        $params = $request->get_json_params();
        
        $post_data = array(
            'post_type' => 'post',
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => $params['content'] ?? '',
            'post_status' => $params['status'] ?? 'draft',
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array('success' => false, 'error' => $post_id->get_error_message());
        }
        
        $this->log_change('post_created', $post_id, $post_data);
        
        return array('success' => true, 'data' => array('id' => $post_id));
    }
    
    /**
     * Update Post
     */
    public function update_post($request) {
        $params = $request->get_json_params();
        $post_id = intval($params['id']);
        
        $this->create_snapshot($post_id);
        
        $update_data = array('ID' => $post_id);
        
        if (isset($params['title'])) $update_data['post_title'] = $params['title'];
        if (isset($params['content'])) $update_data['post_content'] = $params['content'];
        if (isset($params['status'])) $update_data['post_status'] = $params['status'];
        
        $result = wp_update_post($update_data);
        
        $this->log_change('post_updated', $post_id, $params);
        
        return array('success' => !is_wp_error($result), 'data' => array('id' => $post_id));
    }
    
    /**
     * Get Theme Builder Templates (Divi)
     */
    public function get_theme_templates() {
        $templates = get_posts(array(
            'post_type' => 'et_template',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $result = array();
        foreach ($templates as $template) {
            $result[] = array(
                'id' => $template->ID,
                'title' => $template->post_title,
                'type' => get_post_meta($template->ID, '_et_template_type', true),
                'status' => $template->post_status,
            );
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Create Theme Builder Template
     */
    public function create_theme_template($request) {
        $params = $request->get_json_params();
        
        $template_data = array(
            'post_type' => 'et_template',
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => $params['content'] ?? '',
            'post_status' => 'publish',
        );
        
        $template_id = wp_insert_post($template_data);
        
        if (is_wp_error($template_id)) {
            return array('success' => false, 'error' => $template_id->get_error_message());
        }
        
        // Set template type (header, footer, body)
        if (!empty($params['type'])) {
            update_post_meta($template_id, '_et_template_type', $params['type']);
        }
        
        // Set conditions
        if (!empty($params['conditions'])) {
            update_post_meta($template_id, '_et_template_conditions', $params['conditions']);
        }
        
        $this->log_change('template_created', $template_id, $params);
        
        return array('success' => true, 'data' => array('id' => $template_id));
    }
    
    /**
     * Update Theme Builder Template
     */
    public function update_theme_template($request) {
        $params = $request->get_json_params();
        $template_id = intval($params['id']);
        
        $this->create_snapshot($template_id);
        
        $update_data = array('ID' => $template_id);
        
        if (isset($params['title'])) $update_data['post_title'] = $params['title'];
        if (isset($params['content'])) $update_data['post_content'] = $params['content'];
        
        wp_update_post($update_data);
        
        if (!empty($params['conditions'])) {
            update_post_meta($template_id, '_et_template_conditions', $params['conditions']);
        }
        
        $this->log_change('template_updated', $template_id, $params);
        
        return array('success' => true);
    }
    
    /**
     * Database Search
     */
    public function database_search($request) {
        global $wpdb;
        $params = $request->get_json_params();
        
        $search = $params['search'] ?? '';
        $table = $params['table'] ?? 'posts';
        
        if (empty($search)) {
            return array('success' => false, 'error' => 'Search term required');
        }
        
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_content LIKE %s OR post_title LIKE %s LIMIT 100",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        ));
        
        return array('success' => true, 'data' => array('count' => count($results), 'results' => $results));
    }
    
    /**
     * Database Replace
     */
    public function database_replace($request) {
        global $wpdb;
        $params = $request->get_json_params();
        
        $search = $params['search'] ?? '';
        $replace = $params['replace'] ?? '';
        $table = $params['table'] ?? 'posts';
        $column = $params['column'] ?? 'post_content';
        
        if (empty($search)) {
            return array('success' => false, 'error' => 'Search term required');
        }
        
        $table_name = $wpdb->prefix . $table;
        
        // Count matches first
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $column LIKE %s",
            '%' . $wpdb->esc_like($search) . '%'
        ));
        
        // Perform replace
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET $column = REPLACE($column, %s, %s)",
            $search,
            $replace
        ));
        
        $this->log_change('database_replace', null, array(
            'search' => $search,
            'replace' => $replace,
            'table' => $table,
            'affected' => $count,
        ));
        
        return array('success' => true, 'data' => array('affected' => $count));
    }
    
    /**
     * Get Options
     */
    public function get_options($request) {
        $params = $request->get_json_params();
        $keys = $params['keys'] ?? array();
        
        $result = array();
        foreach ($keys as $key) {
            $result[$key] = get_option($key);
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Update Options
     */
    public function update_options($request) {
        $params = $request->get_json_params();
        $options = $params['options'] ?? array();
        
        foreach ($options as $key => $value) {
            update_option($key, $value);
        }
        
        $this->log_change('options_updated', null, array('keys' => array_keys($options)));
        
        return array('success' => true);
    }
    
    /**
     * Upload Media
     */
    public function upload_media($request) {
        $files = $request->get_file_params();
        
        if (empty($files['file'])) {
            return array('success' => false, 'error' => 'No file provided');
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('file', 0);
        
        if (is_wp_error($attachment_id)) {
            return array('success' => false, 'error' => $attachment_id->get_error_message());
        }
        
        return array(
            'success' => true,
            'data' => array(
                'id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
            ),
        );
    }
    
    /**
     * Get Plugins
     */
    public function get_plugins() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        $result = array();
        foreach ($all_plugins as $file => $data) {
            $result[] = array(
                'file' => $file,
                'name' => $data['Name'],
                'version' => $data['Version'],
                'active' => in_array($file, $active_plugins),
            );
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Get Themes
     */
    public function get_themes() {
        $themes = wp_get_themes();
        $active = get_stylesheet();
        
        $result = array();
        foreach ($themes as $slug => $theme) {
            $result[] = array(
                'slug' => $slug,
                'name' => $theme->get('Name'),
                'version' => $theme->get('Version'),
                'active' => $slug === $active,
            );
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Get Change Log
     */
    public function get_changelog($request) {
        $limit = $request->get_param('limit') ?: 50;
        $logs = get_option('ymcp_change_log', array());
        
        return array('success' => true, 'data' => array_slice($logs, 0, $limit));
    }
    
    /**
     * Rollback Change
     */
    public function rollback_change($request) {
        $params = $request->get_json_params();
        $snapshot_id = $params['snapshot_id'] ?? '';
        
        $snapshots = get_option('ymcp_snapshots', array());
        
        if (!isset($snapshots[$snapshot_id])) {
            return array('success' => false, 'error' => 'Snapshot not found');
        }
        
        $snapshot = $snapshots[$snapshot_id];
        
        // Restore post content
        wp_update_post(array(
            'ID' => $snapshot['post_id'],
            'post_title' => $snapshot['title'],
            'post_content' => $snapshot['content'],
        ));
        
        $this->log_change('rollback', $snapshot['post_id'], array('snapshot' => $snapshot_id));
        
        return array('success' => true);
    }
    
    /**
     * Execute Custom Action
     */
    public function execute_action($request) {
        $params = $request->get_json_params();
        $action = $params['action'] ?? '';
        $data = $params['data'] ?? array();
        
        // Allow custom hooks
        $result = apply_filters('ymcp_connector_execute_' . $action, null, $data);
        
        if ($result === null) {
            return array('success' => false, 'error' => 'Unknown action');
        }
        
        return array('success' => true, 'data' => $result);
    }
    
    /**
     * Log Change
     */
    private function log_change($type, $object_id, $data) {
        $logs = get_option('ymcp_change_log', array());
        
        array_unshift($logs, array(
            'id' => uniqid(),
            'type' => $type,
            'object_id' => $object_id,
            'data' => $data,
            'user' => get_current_user_id(),
            'time' => current_time('mysql'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ));
        
        // Keep last 500 entries
        $logs = array_slice($logs, 0, 500);
        
        update_option('ymcp_change_log', $logs);
    }
    
    /**
     * Create Snapshot for Rollback
     */
    private function create_snapshot($post_id) {
        $post = get_post($post_id);
        if (!$post) return;
        
        $snapshots = get_option('ymcp_snapshots', array());
        
        $snapshot_id = 'snap_' . $post_id . '_' . time();
        $snapshots[$snapshot_id] = array(
            'post_id' => $post_id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'meta' => get_post_meta($post_id),
            'time' => current_time('mysql'),
        );
        
        // Keep last 100 snapshots
        if (count($snapshots) > 100) {
            $snapshots = array_slice($snapshots, -100, null, true);
        }
        
        update_option('ymcp_snapshots', $snapshots);
    }
    
    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_options_page(
            'YMCP Connector',
            'YMCP Connector',
            'manage_options',
            'ymcp-connector',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register Settings
     */
    public function register_settings() {
        register_setting('ymcp_connector', 'ymcp_connector_api_key');
        register_setting('ymcp_connector', 'ymcp_connector_enabled');
    }
    
    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        $api_key = get_option('ymcp_connector_api_key');
        if (!$api_key) {
            $api_key = wp_generate_password(32, false);
            update_option('ymcp_connector_api_key', $api_key);
        }
        ?>
        <div class="wrap">
            <h1>YMCP Connector</h1>
            <form method="post" action="options.php">
                <?php settings_fields('ymcp_connector'); ?>
                <table class="form-table">
                    <tr>
                        <th>API Endpoint</th>
                        <td><code><?php echo rest_url('ymcp-connector/v1/'); ?></code></td>
                    </tr>
                    <tr>
                        <th>API Key</th>
                        <td>
                            <input type="text" name="ymcp_connector_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" readonly>
                            <button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js($api_key); ?>'); alert('تم النسخ!');">نسخ</button>
                        </td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td>
                            <span style="color: green;">✓ جاهز للاتصال</span>
                        </td>
                    </tr>
                </table>
                
                <h2>نقاط النهاية المتاحة (API Endpoints)</h2>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>الوظيفة</th>
                            <th>النقطة</th>
                            <th>الطريقة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>معلومات الموقع</td><td><code>/info</code></td><td>GET</td></tr>
                        <tr><td>فحص صحة الموقع</td><td><code>/health</code></td><td>GET</td></tr>
                        <tr><td>تشخيص كامل</td><td><code>/diagnose</code></td><td>GET</td></tr>
                        <tr><td>قائمة الصفحات</td><td><code>/pages</code></td><td>GET</td></tr>
                        <tr><td>إنشاء صفحة</td><td><code>/pages/create</code></td><td>POST</td></tr>
                        <tr><td>تعديل صفحة</td><td><code>/pages/update</code></td><td>POST</td></tr>
                        <tr><td>Theme Builder</td><td><code>/theme-builder/*</code></td><td>GET/POST</td></tr>
                        <tr><td>بحث بقاعدة البيانات</td><td><code>/database/search</code></td><td>POST</td></tr>
                        <tr><td>استبدال بقاعدة البيانات</td><td><code>/database/replace</code></td><td>POST</td></tr>
                        <tr><td>سجل التغييرات</td><td><code>/changelog</code></td><td>GET</td></tr>
                        <tr><td>التراجع</td><td><code>/rollback</code></td><td>POST</td></tr>
                    </tbody>
                </table>
                
                <?php submit_button('حفظ الإعدادات'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Manage Pages - Delete unused and create/update required
     */
    public function manage_pages($request) {
        $params = $request->get_json_params();
        $action = $params['action'] ?? 'cleanup'; // cleanup, list, delete_all_unused
        
        // Required pages for Peta Alex
        $required_pages = array(
            'home' => array('title' => 'Home - Peta Alex For Peralite Manufacturing', 'slug' => 'home'),
            'about' => array('title' => 'About Us - Peta Alex', 'slug' => 'about'),
            'products' => array('title' => 'Products - Peta Alex', 'slug' => 'products'),
            'contact' => array('title' => 'Contact Us - Peta Alex', 'slug' => 'contact'),
        );
        
        $all_pages = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $result = array(
            'total' => count($all_pages),
            'required' => count($required_pages),
            'deleted' => array(),
            'errors' => array(),
        );
        
        if ($action === 'delete_all_unused' || $action === 'cleanup') {
            $required_slugs = array_column($required_pages, 'slug');
            $system_pages = array('sample-page', 'privacy-policy');
            
            foreach ($all_pages as $page) {
                $slug = $page->post_name;
                
                if (!in_array($slug, $required_slugs) && 
                    !in_array($slug, $system_pages) &&
                    $page->post_status !== 'trash') {
                    
                    $deleted = wp_delete_post($page->ID, true);
                    if ($deleted) {
                        $result['deleted'][] = array(
                            'id' => $page->ID,
                            'title' => $page->post_title,
                            'slug' => $slug,
                        );
                    } else {
                        $result['errors'][] = "Failed to delete page ID: {$page->ID}";
                    }
                }
            }
        }
        
        $result['success'] = true;
        $result['deleted_count'] = count($result['deleted']);
        
        return array('success' => true, 'data' => $result);
    }
}

// Initialize
YMCP_Connector::instance();
