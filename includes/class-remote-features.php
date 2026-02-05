<?php
/**
 * YMCP Remote Features - AJAX Handlers & Templates System
 *
 * @package AIWebsiteBuilderUnified
 */

defined('ABSPATH') || exit;

/**
 * YMCP Remote Features Class
 */
class AWBU_Remote_Features {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // AJAX Handlers for Remote Sites
        add_action('wp_ajax_ymcp_save_remote_site', array($this, 'save_remote_site'));
        add_action('wp_ajax_ymcp_get_site_details', array($this, 'get_site_details'));
        add_action('wp_ajax_ymcp_test_connection', array($this, 'test_connection'));
        add_action('wp_ajax_ymcp_delete_site', array($this, 'delete_site'));
        
        // Templates System
        add_action('wp_ajax_ymcp_save_template', array($this, 'save_template'));
        add_action('wp_ajax_ymcp_load_templates', array($this, 'load_templates'));
        add_action('wp_ajax_ymcp_import_template', array($this, 'import_template'));
        add_action('wp_ajax_ymcp_export_template', array($this, 'export_template'));
        add_action('wp_ajax_ymcp_delete_template', array($this, 'delete_template'));
        
        // Diagnostics
        add_action('wp_ajax_ymcp_run_diagnostics', array($this, 'run_diagnostics'));
        
        // Project Management
        add_action('wp_ajax_awbu_delete_project', array($this, 'delete_project'));
        add_action('wp_ajax_awbu_duplicate_project', array($this, 'duplicate_project'));
        add_action('wp_ajax_awbu_export_project', array($this, 'export_project'));
    }
    
    /**
     * Save Remote Site
     */
    public function save_remote_site() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $site = $_POST['site'];
        $site_id = 'site_' . sanitize_title($site['name']) . '_' . time();
        
        $sites = get_option('ymcp_remote_sites', array());
        
        $sites[$site_id] = array(
            'name' => sanitize_text_field($site['name']),
            'url' => esc_url_raw($site['url']),
            'api_key' => sanitize_text_field($site['api_key']),
            'added' => current_time('mysql'),
            'status' => 'unknown',
        );
        
        update_option('ymcp_remote_sites', $sites);
        
        wp_send_json_success(array('site_id' => $site_id));
    }
    
    /**
     * Get Site Details
     */
    public function get_site_details() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $site_id = sanitize_text_field($_POST['site_id']);
        $tab = sanitize_text_field($_POST['tab'] ?? 'overview');
        
        $sites = get_option('ymcp_remote_sites', array());
        
        if (!isset($sites[$site_id])) {
            wp_send_json_error('Site not found');
        }
        
        $site = $sites[$site_id];
        
        // Call remote API
        $response = $this->call_remote_api($site, '/info');
        
        ob_start();
        
        if ($tab === 'overview') {
            $this->render_overview_tab($site, $response);
        } elseif ($tab === 'pages') {
            $pages_response = $this->call_remote_api($site, '/pages');
            $this->render_pages_tab($site, $pages_response);
        } elseif ($tab === 'templates') {
            $templates_response = $this->call_remote_api($site, '/theme-builder/templates');
            $this->render_templates_tab($site, $templates_response);
        } elseif ($tab === 'logs') {
            $logs_response = $this->call_remote_api($site, '/changelog');
            $this->render_logs_tab($site, $logs_response);
        }
        
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Render Overview Tab
     */
    private function render_overview_tab($site, $response) {
        $data = $response['data'] ?? array();
        ?>
        <div class="ymcp-overview">
            <div class="ymcp-stat-grid">
                <div class="ymcp-stat">
                    <span class="ymcp-stat-label">WordPress</span>
                    <span class="ymcp-stat-value"><?php echo esc_html($data['wp_version'] ?? '?'); ?></span>
                </div>
                <div class="ymcp-stat">
                    <span class="ymcp-stat-label">PHP</span>
                    <span class="ymcp-stat-value"><?php echo esc_html($data['php_version'] ?? '?'); ?></span>
                </div>
                <div class="ymcp-stat">
                    <span class="ymcp-stat-label">Theme</span>
                    <span class="ymcp-stat-value"><?php echo esc_html($data['theme']['name'] ?? '?'); ?></span>
                </div>
                <div class="ymcp-stat">
                    <span class="ymcp-stat-label">Divi</span>
                    <span class="ymcp-stat-value"><?php echo $data['divi_version'] ? esc_html($data['divi_version']) : 'Not installed'; ?></span>
                </div>
            </div>
            
            <div class="ymcp-actions-panel">
                <h4>إجراءات سريعة</h4>
                <button class="aisbp-btn aisbp-btn-secondary ymcp-action" data-action="create-page">
                    <span class="dashicons dashicons-plus"></span> إنشاء صفحة
                </button>
                <button class="aisbp-btn aisbp-btn-secondary ymcp-action" data-action="create-header">
                    <span class="dashicons dashicons-admin-customizer"></span> إنشاء Header
                </button>
                <button class="aisbp-btn aisbp-btn-secondary ymcp-action" data-action="create-footer">
                    <span class="dashicons dashicons-admin-customizer"></span> إنشاء Footer
                </button>
                <button class="aisbp-btn aisbp-btn-secondary ymcp-action" data-action="search-replace">
                    <span class="dashicons dashicons-search"></span> بحث واستبدال
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Pages Tab
     */
    private function render_pages_tab($site, $response) {
        $pages = $response['data'] ?? array();
        ?>
        <div class="ymcp-pages-list">
            <?php foreach ($pages as $page) : ?>
                <div class="ymcp-page-item">
                    <div class="ymcp-page-title"><?php echo esc_html($page['title']); ?></div>
                    <div class="ymcp-page-meta">
                        <span class="ymcp-page-status <?php echo esc_attr($page['status']); ?>"><?php echo esc_html($page['status']); ?></span>
                        <a href="<?php echo esc_url($page['url']); ?>" target="_blank">عرض</a>
                    </div>
                    <div class="ymcp-page-actions">
                        <button class="aisbp-btn aisbp-btn-sm ymcp-edit-page" data-id="<?php echo esc_attr($page['id']); ?>">تعديل</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render Templates Tab
     */
    private function render_templates_tab($site, $response) {
        $templates = $response['data'] ?? array();
        ?>
        <div class="ymcp-templates-list">
            <button class="aisbp-btn aisbp-btn-primary ymcp-create-template">
                <span class="dashicons dashicons-plus"></span> إنشاء قالب جديد
            </button>
            
            <?php foreach ($templates as $template) : ?>
                <div class="ymcp-template-item">
                    <div class="ymcp-template-icon">
                        <?php 
                        $icon = $template['type'] === 'header' ? 'align-wide' : ($template['type'] === 'footer' ? 'align-left' : 'layout');
                        ?>
                        <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                    </div>
                    <div class="ymcp-template-info">
                        <div class="ymcp-template-title"><?php echo esc_html($template['title']); ?></div>
                        <div class="ymcp-template-type"><?php echo esc_html($template['type']); ?></div>
                    </div>
                    <div class="ymcp-template-actions">
                        <button class="aisbp-btn aisbp-btn-sm ymcp-edit-template" data-id="<?php echo esc_attr($template['id']); ?>">تعديل</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render Logs Tab
     */
    private function render_logs_tab($site, $response) {
        $logs = $response['data'] ?? array();
        ?>
        <div class="ymcp-logs-list">
            <?php foreach (array_slice($logs, 0, 20) as $log) : ?>
                <div class="ymcp-log-item">
                    <div class="ymcp-log-time"><?php echo esc_html($log['time']); ?></div>
                    <div class="ymcp-log-type <?php echo esc_attr($log['type']); ?>"><?php echo esc_html($log['type']); ?></div>
                    <div class="ymcp-log-actions">
                        <?php if (!empty($log['id'])) : ?>
                            <button class="aisbp-btn aisbp-btn-sm aisbp-btn-warning ymcp-rollback" data-snapshot="<?php echo esc_attr($log['id']); ?>">
                                تراجع
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Call Remote API
     */
    private function call_remote_api($site, $endpoint, $method = 'GET', $body = null) {
        $url = trailingslashit($site['url']) . 'wp-json/ymcp-connector/v1' . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'X-YMCP-API-Key' => $site['api_key'],
                'Content-Type' => 'application/json',
            ),
        );
        
        if ($body) {
            $args['body'] = json_encode($body);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        return json_decode(wp_remote_retrieve_body($response), true);
    }
    
    /**
     * Save Template to Library
     */
    public function save_template() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $template = array(
            'id' => 'tpl_' . time() . '_' . wp_rand(1000, 9999),
            'name' => sanitize_text_field($_POST['name']),
            'category' => sanitize_text_field($_POST['category'] ?? 'general'),
            'type' => sanitize_text_field($_POST['type'] ?? 'page'),
            'content' => wp_kses_post($_POST['content']),
            'thumbnail' => esc_url_raw($_POST['thumbnail'] ?? ''),
            'created' => current_time('mysql'),
            'author' => get_current_user_id(),
        );
        
        $templates = get_option('ymcp_template_library', array());
        $templates[$template['id']] = $template;
        update_option('ymcp_template_library', $templates);
        
        wp_send_json_success($template);
    }
    
    /**
     * Load Templates Library
     */
    public function load_templates() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $templates = get_option('ymcp_template_library', array());
        
        if ($category) {
            $templates = array_filter($templates, function($t) use ($category) {
                return $t['category'] === $category;
            });
        }
        
        wp_send_json_success($templates);
    }
    
    /**
     * Import Template
     */
    public function import_template() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $file = $_FILES['template_file'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded');
        }
        
        $content = file_get_contents($file['tmp_name']);
        $template = json_decode($content, true);
        
        if (!$template || empty($template['name'])) {
            wp_send_json_error('Invalid template file');
        }
        
        $template['id'] = 'tpl_' . time() . '_' . wp_rand(1000, 9999);
        $template['imported'] = current_time('mysql');
        
        $templates = get_option('ymcp_template_library', array());
        $templates[$template['id']] = $template;
        update_option('ymcp_template_library', $templates);
        
        wp_send_json_success($template);
    }
    
    /**
     * Export Template
     */
    public function export_template() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $template_id = sanitize_text_field($_POST['template_id']);
        $templates = get_option('ymcp_template_library', array());
        
        if (!isset($templates[$template_id])) {
            wp_send_json_error('Template not found');
        }
        
        wp_send_json_success($templates[$template_id]);
    }
    
    /**
     * Delete Template
     */
    public function delete_template() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        $template_id = sanitize_text_field($_POST['template_id']);
        $templates = get_option('ymcp_template_library', array());
        
        if (isset($templates[$template_id])) {
            unset($templates[$template_id]);
            update_option('ymcp_template_library', $templates);
        }
        
        wp_send_json_success();
    }
    
    /**
     * Run Site Diagnostics
     */
    public function run_diagnostics() {
        check_ajax_referer('awbu_nonce', 'nonce');
        
        global $wpdb;
        
        $diagnostics = array(
            'wordpress' => array(),
            'server' => array(),
            'database' => array(),
            'plugins' => array(),
            'security' => array(),
            'performance' => array(),
        );
        
        // WordPress Checks
        $diagnostics['wordpress'][] = array(
            'name' => 'WordPress Version',
            'value' => get_bloginfo('version'),
            'status' => version_compare(get_bloginfo('version'), '6.0', '>=') ? 'good' : 'warning',
        );
        
        $diagnostics['wordpress'][] = array(
            'name' => 'Active Theme',
            'value' => wp_get_theme()->get('Name'),
            'status' => 'info',
        );
        
        // Server Checks
        $diagnostics['server'][] = array(
            'name' => 'PHP Version',
            'value' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'critical',
        );
        
        $diagnostics['server'][] = array(
            'name' => 'Memory Limit',
            'value' => ini_get('memory_limit'),
            'status' => intval(ini_get('memory_limit')) >= 256 ? 'good' : 'warning',
        );
        
        $diagnostics['server'][] = array(
            'name' => 'Max Execution Time',
            'value' => ini_get('max_execution_time') . 's',
            'status' => intval(ini_get('max_execution_time')) >= 30 ? 'good' : 'warning',
        );
        
        // Database Checks
        $db_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $diagnostics['database'][] = array(
            'name' => 'Database Size',
            'value' => size_format($db_size),
            'status' => 'info',
        );
        
        $diagnostics['database'][] = array(
            'name' => 'Tables Count',
            'value' => count($wpdb->get_results("SHOW TABLES")),
            'status' => 'info',
        );
        
        // Plugin Conflicts Check
        $conflict_plugins = array('wordfence', 'sucuri-scanner', 'ithemes-security');
        $active = get_option('active_plugins', array());
        $conflicts = array_filter($conflict_plugins, function($p) use ($active) {
            foreach ($active as $a) {
                if (strpos($a, $p) !== false) return true;
            }
            return false;
        });
        
        $diagnostics['plugins'][] = array(
            'name' => 'Potential Conflicts',
            'value' => empty($conflicts) ? 'None' : implode(', ', $conflicts),
            'status' => empty($conflicts) ? 'good' : 'warning',
        );
        
        // Security Checks
        $diagnostics['security'][] = array(
            'name' => 'SSL',
            'value' => is_ssl() ? 'Active' : 'Not Active',
            'status' => is_ssl() ? 'good' : 'critical',
        );
        
        $diagnostics['security'][] = array(
            'name' => 'Debug Mode',
            'value' => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
            'status' => !WP_DEBUG ? 'good' : 'warning',
        );
        
        // Performance Checks
        $diagnostics['performance'][] = array(
            'name' => 'Object Cache',
            'value' => wp_using_ext_object_cache() ? 'Active' : 'Not Active',
            'status' => wp_using_ext_object_cache() ? 'good' : 'info',
        );
        
        wp_send_json_success($diagnostics);
    }
    
    /**
     * Delete Project
     */
    public function delete_project() {
        // Try multiple nonce names for compatibility
        $nonce_valid = wp_verify_nonce($_POST['nonce'] ?? '', 'awbu_nonce') || 
                       wp_verify_nonce($_POST['nonce'] ?? '', 'aisbp_nonce');
        
        if (!$nonce_valid && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $project_id = intval($_POST['project_id'] ?? 0);
        
        if (!$project_id) {
            wp_send_json_error(array('message' => 'Invalid project ID'));
        }
        
        // Try to delete from database if class exists
        if (class_exists('AISBP_Database') || class_exists('AISBP\Database')) {
            try {
                $db = new AISBP_Database();
                if (method_exists($db, 'delete_project')) {
                    $db->delete_project($project_id);
                }
            } catch (Exception $e) {
                // Fallback: delete as post
            }
        }
        
        // Try deleting as WordPress post
        $post = get_post($project_id);
        if ($post && in_array($post->post_type, array('aisbp_project', 'awbu_project', 'post', 'page'))) {
            wp_delete_post($project_id, true);
            wp_send_json_success(array('message' => 'Project deleted'));
        }
        
        // Fallback: delete from option storage
        $projects = get_option('awbu_projects', array());
        if (isset($projects[$project_id])) {
            unset($projects[$project_id]);
            update_option('awbu_projects', $projects);
            wp_send_json_success(array('message' => 'Project deleted'));
        }
        
        wp_send_json_success(array('message' => 'Project removed'));
    }
    
    /**
     * Duplicate Project
     */
    public function duplicate_project() {
        $nonce_valid = wp_verify_nonce($_POST['nonce'] ?? '', 'awbu_nonce') || 
                       wp_verify_nonce($_POST['nonce'] ?? '', 'aisbp_nonce');
        
        if (!$nonce_valid && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $project_id = intval($_POST['project_id'] ?? 0);
        
        if (!$project_id) {
            wp_send_json_error(array('message' => 'Invalid project ID'));
        }
        
        // Try from database
        if (class_exists('AISBP_Database') || class_exists('AISBP\Database')) {
            try {
                $db = new AISBP_Database();
                if (method_exists($db, 'duplicate_project')) {
                    $new_id = $db->duplicate_project($project_id);
                    wp_send_json_success(array('new_id' => $new_id));
                }
            } catch (Exception $e) {
                // Fallback
            }
        }
        
        // Duplicate as post
        $post = get_post($project_id);
        if ($post) {
            $new_post = array(
                'post_title' => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status' => 'draft',
                'post_type' => $post->post_type,
                'post_author' => get_current_user_id(),
            );
            
            $new_id = wp_insert_post($new_post);
            
            if ($new_id && !is_wp_error($new_id)) {
                // Copy meta
                $meta = get_post_meta($project_id);
                foreach ($meta as $key => $values) {
                    foreach ($values as $value) {
                        add_post_meta($new_id, $key, maybe_unserialize($value));
                    }
                }
                
                wp_send_json_success(array('new_id' => $new_id));
            }
        }
        
        wp_send_json_error(array('message' => 'Could not duplicate project'));
    }
    
    /**
     * Export Project
     */
    public function export_project() {
        $project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);
        
        if (!$project_id) {
            wp_die('Invalid project ID');
        }
        
        $post = get_post($project_id);
        if (!$post) {
            wp_die('Project not found');
        }
        
        $export = array(
            'title' => $post->post_title,
            'content' => $post->post_content,
            'type' => $post->post_type,
            'status' => $post->post_status,
            'meta' => get_post_meta($project_id),
            'exported_at' => current_time('mysql'),
            'plugin' => 'AI Website Builder Unified',
        );
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="project-' . $project_id . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    }
}

// Initialize
AWBU_Remote_Features::instance();
