<?php
/**
 * YMCP Dashboard - لوحة التحكم الرئيسية
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class YMCP_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        // Hooks
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    /**
     * Add Admin Menu
     */
    public function add_menu() {
        // Dashboard already added in main plugin file
    }

    /**
     * Add Dashboard Widgets
     */
    public function add_dashboard_widgets() {
        if (current_user_can('manage_options')) {
            wp_add_dashboard_widget(
                'ymcp_site_overview',
                __('YMCP Site Overview', 'ymcp'),
                array($this, 'render_site_overview_widget')
            );

            wp_add_dashboard_widget(
                'ymcp_quick_actions',
                __('YMCP Quick Actions', 'ymcp'),
                array($this, 'render_quick_actions_widget')
            );

            wp_add_dashboard_widget(
                'ymcp_recent_activity',
                __('YMCP Recent Activity', 'ymcp'),
                array($this, 'render_recent_activity_widget')
            );
        }
    }

    /**
     * Get Dashboard Data
     */
    public function get_dashboard_data() {
        global $wpdb;

        $data = array(
            'site_info' => $this->get_site_info(),
            'site_health' => $this->get_site_health(),
            'quick_stats' => $this->get_quick_stats(),
            'recent_activity' => $this->get_recent_activity(),
            'alerts' => $this->get_alerts(),
            'tasks' => $this->get_tasks(),
        );

        return $data;
    }

    /**
     * Get Site Info
     */
    private function get_site_info() {
        return array(
            'url' => site_url(),
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'theme' => wp_get_theme()->get('Name'),
            'active_plugins' => count(get_option('active_plugins', array())),
            'posts_count' => wp_count_posts()->publish,
            'pages_count' => wp_count_posts('page')->publish,
        );
    }

    /**
     * Get Site Health
     */
    private function get_site_health() {
        if (!function_exists('get_option')) {
            return array('status' => 'unknown');
        }

        // Get WordPress site health status
        $health_status = get_option('site_health_status', array());

        return array(
            'status' => isset($health_status['status']) ? $health_status['status'] : 'unknown',
            'issues' => isset($health_status['issues']) ? $health_status['issues'] : 0,
            'last_check' => isset($health_status['last_checked']) ? $health_status['last_checked'] : null,
        );
    }

    /**
     * Get Quick Stats
     */
    private function get_quick_stats() {
        return array(
            'visitors_today' => $this->get_visitors_today(),
            'visitors_this_week' => $this->get_visitors_week(),
            'comments_today' => $this->get_comments_today(),
            'orders_today' => $this->get_orders_today(),
            'revenue_today' => $this->get_revenue_today(),
            'security_score' => $this->get_security_score(),
            'performance_score' => $this->get_performance_score(),
            'seo_score' => $this->get_seo_score(),
        );
    }

    /**
     * Get Recent Activity
     */
    private function get_recent_activity($limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'activity';

        $activities = array();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
                $limit
            ));

            foreach ($results as $result) {
                $activities[] = array(
                    'id' => $result->id,
                    'type' => $result->type,
                    'message' => $result->message,
                    'data' => json_decode($result->data, true),
                    'user_id' => $result->user_id,
                    'created_at' => $result->created_at,
                );
            }
        }

        return $activities;
    }

    /**
     * Get Alerts
     */
    private function get_alerts() {
        $alerts = array();

        // Security alerts
        if ($this->security_scan_needed()) {
            $alerts[] = array(
                'type' => 'warning',
                'title' => __('Security scan needed', 'ymcp'),
                'message' => __('Last security scan was more than 7 days ago.', 'ymcp'),
                'action' => 'ymcp_security_scan',
            );
        }

        // Backup alerts
        if ($this->backup_needed()) {
            $alerts[] = array(
                'type' => 'warning',
                'title' => __('Backup needed', 'ymcp'),
                'message' => __('Last backup was more than 30 days ago.', 'ymcp'),
                'action' => 'ymcp_create_backup',
            );
        }

        // Plugin update alerts
        $update_plugins = get_site_transient('update_plugins');
        if (!empty($update_plugins->response)) {
            $count = count($update_plugins->response);
            $alerts[] = array(
                'type' => 'info',
                'title' => sprintf(__('%d plugin updates available', 'ymcp'), $count),
                'message' => __('Some plugins need to be updated.', 'ymcp'),
                'action' => 'update_plugins',
            );
        }

        return $alerts;
    }

    /**
     * Get Tasks
     */
    private function get_tasks() {
        $tasks = array();

        // Auto-generated tasks based on site state
        if ($this->security_scan_needed()) {
            $tasks[] = array(
                'id' => 'security_scan',
                'title' => __('Run security scan', 'ymcp'),
                'priority' => 'high',
                'status' => 'pending',
            );
        }

        if ($this->backup_needed()) {
            $tasks[] = array(
                'id' => 'create_backup',
                'title' => __('Create backup', 'ymcp'),
                'priority' => 'medium',
                'status' => 'pending',
            );
        }

        return $tasks;
    }

    // ==================== Helper Methods ====================

    /**
     * Get visitors today
     */
    private function get_visitors_today() {
        // This would use analytics data
        return array('count' => 0, 'change' => 0);
    }

    /**
     * Get visitors this week
     */
    private function get_visitors_week() {
        return array('count' => 0, 'change' => 0);
    }

    /**
     * Get comments today
     */
    private function get_comments_today() {
        $args = array(
            'date_query' => array(
                array(
                    'after' => 'today',
                    'inclusive' => true,
                ),
            ),
            'count' => true,
        );
        return wp_count_comments($args);
    }

    /**
     * Get orders today
     */
    private function get_orders_today() {
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            return 0;
        }
        return 0;
    }

    /**
     * Get revenue today
     */
    private function get_revenue_today() {
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            return 0;
        }
        return 0;
    }

    /**
     * Get security score
     */
    private function get_security_score() {
        // Based on security scans
        return get_option('ymcp_security_score', 85);
    }

    /**
     * Get performance score
     */
    private function get_performance_score() {
        // Based on performance tests
        return get_option('ymcp_performance_score', 80);
    }

    /**
     * Get SEO score
     */
    private function get_seo_score() {
        // Based on SEO analysis
        return get_option('ymcp_seo_score', 75);
    }

    /**
     * Check if security scan needed
     */
    private function security_scan_needed() {
        $last_scan = get_option('ymcp_last_security_scan', 0);
        return (time() - $last_scan) > (7 * DAY_IN_SECONDS);
    }

    /**
     * Check if backup needed
     */
    private function backup_needed() {
        $last_backup = get_option('ymcp_last_backup', 0);
        return (time() - $last_backup) > (30 * DAY_IN_SECONDS);
    }

    // ==================== Widget Renderers ====================

    /**
     * Render Site Overview Widget
     */
    public function render_site_overview_widget() {
        $data = $this->get_dashboard_data();
        include YMCP_PLUGIN_DIR . 'templates/dashboard/site-overview-widget.php';
    }

    /**
     * Render Quick Actions Widget
     */
    public function render_quick_actions_widget() {
        include YMCP_PLUGIN_DIR . 'templates/dashboard/quick-actions-widget.php';
    }

    /**
     * Render Recent Activity Widget
     */
    public function render_recent_activity_widget() {
        $activity = $this->get_recent_activity(5);
        include YMCP_PLUGIN_DIR . 'templates/dashboard/recent-activity-widget.php';
    }

    /**
     * Log Activity
     */
    public function log_activity($type, $message, $data = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'activity';

        // Create table if not exists
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            message text NOT NULL,
            data longtext,
            user_id bigint(20) NOT NULL,
            ip_address varchar(45),
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Insert activity
        $wpdb->insert(
            $table_name,
            array(
                'type' => $type,
                'message' => $message,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );
    }

    /**
     * Get client IP
     */
    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }
}
