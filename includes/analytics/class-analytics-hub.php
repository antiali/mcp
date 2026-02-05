<?php
/**
 * YMCP Analytics Hub - مركز التحليلات الذكي
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class YMCP_Analytics_Hub {

    /**
     * Constructor
     */
    public function __construct() {
        // Hooks
        add_action('init', array($this, 'init_tracking'));
        add_action('wp_head', array($this, 'tracking_script'));
    }

    /**
     * Initialize Tracking
     */
    public function init_tracking() {
        if (!get_option('ymcp_analytics_tracking', false)) {
            return;
        }

        // Track page view
        $this->track_page_view();
    }

    /**
     * Track Page View
     */
    private function track_page_view() {
        // Don't track admin or AJAX requests
        if (is_admin() || wp_is_json_request()) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'analytics';

        // Create table if not exists
        $this->create_analytics_table();

        // Get visitor ID
        $visitor_id = $this->get_or_create_visitor_id();

        // Track page view
        $wpdb->insert(
            $table_name,
            array(
                'visitor_id' => $visitor_id,
                'session_id' => $this->get_session_id(),
                'page_url' => $this->get_current_url(),
                'page_title' => wp_get_document_title(),
                'referrer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                'ip_address' => $this->get_client_ip(),
                'device_type' => $this->detect_device(),
                'browser' => $this->detect_browser(),
                'os' => $this->detect_os(),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Create Analytics Table
     */
    private function create_analytics_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'analytics';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            visitor_id varchar(50) NOT NULL,
            session_id varchar(50) NOT NULL,
            page_url varchar(500) NOT NULL,
            page_title varchar(255),
            referrer varchar(500),
            user_agent text,
            ip_address varchar(45),
            device_type varchar(20),
            browser varchar(50),
            os varchar(50),
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY visitor_id (visitor_id),
            KEY session_id (session_id),
            KEY page_url (page_url),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get or Create Visitor ID
     */
    private function get_or_create_visitor_id() {
        if (!isset($_COOKIE['ymcp_visitor_id'])) {
            $visitor_id = wp_generate_uuid4();
            setcookie('ymcp_visitor_id', $visitor_id, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        } else {
            $visitor_id = sanitize_text_field($_COOKIE['ymcp_visitor_id']);
        }
        return $visitor_id;
    }

    /**
     * Get Session ID
     */
    private function get_session_id() {
        if (!isset($_COOKIE['ymcp_session_id'])) {
            $session_id = wp_generate_uuid4();
            setcookie('ymcp_session_id', $session_id, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        } else {
            $session_id = sanitize_text_field($_COOKIE['ymcp_session_id']);
        }
        return $session_id;
    }

    /**
     * Get Current URL
     */
    private function get_current_url() {
        return home_url(add_query_arg(null, null));
    }

    /**
     * Get Client IP
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

    /**
     * Detect Device Type
     */
    private function detect_device() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if (preg_match('/mobile/i', $user_agent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $user_agent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Detect Browser
     */
    private function detect_browser() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if (preg_match('/Firefox/i', $user_agent)) {
            return 'Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/i', $user_agent)) {
            return 'Edge';
        } elseif (preg_match('/Opera/i', $user_agent)) {
            return 'Opera';
        } elseif (preg_match('/MSIE/i', $user_agent)) {
            return 'IE';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Detect OS
     */
    private function detect_os() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if (preg_match('/Windows/i', $user_agent)) {
            return 'Windows';
        } elseif (preg_match('/Mac/i', $user_agent)) {
            return 'MacOS';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            return 'Linux';
        } elseif (preg_match('/Android/i', $user_agent)) {
            return 'Android';
        } elseif (preg_match('/iOS/i', $user_agent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get Analytics Data
     */
    public function get_analytics($params = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'analytics';

        // Default parameters
        $start_date = isset($params['start_date']) ? $params['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($params['end_date']) ? $params['end_date'] : date('Y-m-d');
        $limit = isset($params['limit']) ? intval($params['limit']) : 1000;

        $where = $wpdb->prepare(
            "WHERE created_at >= %s AND created_at <= %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );

        // Get page views
        $page_views = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name $where"
        );

        // Get unique visitors
        $unique_visitors = $wpdb->get_var(
            "SELECT COUNT(DISTINCT visitor_id) FROM $table_name $where"
        );

        // Get sessions
        $sessions = $wpdb->get_var(
            "SELECT COUNT(DISTINCT session_id) FROM $table_name $where"
        );

        // Get bounce rate (single page view sessions)
        $bounce_rate = 0;
        $total_sessions = $sessions;
        if ($total_sessions > 0) {
            $bounce_sessions = $wpdb->get_var(
                "SELECT COUNT(*) FROM (
                    SELECT session_id, COUNT(*) as page_count
                    FROM $table_name $where
                    GROUP BY session_id
                    HAVING page_count = 1
                ) AS bounce_table"
            );
            $bounce_rate = ($bounce_sessions / $total_sessions) * 100;
        }

        // Get avg session duration (placeholder - would need session tracking)
        $avg_duration = 0;

        // Get top pages
        $top_pages = $wpdb->get_results($wpdb->prepare(
            "SELECT page_url, page_title, COUNT(*) as views
            FROM $table_name $where
            GROUP BY page_url
            ORDER BY views DESC
            LIMIT %d",
            10
        ));

        // Get device breakdown
        $device_breakdown = $wpdb->get_results(
            "SELECT device_type, COUNT(*) as count
            FROM $table_name $where
            GROUP BY device_type"
        );

        // Get browser breakdown
        $browser_breakdown = $wpdb->get_results(
            "SELECT browser, COUNT(*) as count
            FROM $table_name $where
            GROUP BY browser"
        );

        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'overview' => array(
                'page_views' => intval($page_views),
                'unique_visitors' => intval($unique_visitors),
                'sessions' => intval($sessions),
                'bounce_rate' => round($bounce_rate, 2),
                'avg_session_duration' => $avg_duration,
            ),
            'top_pages' => $top_pages,
            'device_breakdown' => $device_breakdown,
            'browser_breakdown' => $browser_breakdown,
        );
    }

    /**
     * Generate Report
     */
    public function generate_report($params = array()) {
        $format = isset($params['format']) ? $params['format'] : 'json';
        $data = $this->get_analytics($params);

        if ($format === 'json') {
            return $data;
        } elseif ($format === 'html') {
            return $this->generate_html_report($data);
        } elseif ($format === 'csv') {
            return $this->generate_csv_report($data);
        } else {
            return new WP_Error('invalid_format', __('Invalid report format', 'ymcp'));
        }
    }

    /**
     * Generate HTML Report
     */
    private function generate_html_report($data) {
        $html = '<div class="ymcp-analytics-report">';
        $html .= '<h2>' . __('Analytics Report', 'ymcp') . '</h2>';
        $html .= '<p>' . sprintf(__('Period: %s to %s', 'ymcp'), $data['period']['start_date'], $data['period']['end_date']) . '</p>';

        $html .= '<h3>' . __('Overview', 'ymcp') . '</h3>';
        $html .= '<ul>';
        $html .= '<li>' . sprintf(__('Page Views: %d', 'ymcp'), $data['overview']['page_views']) . '</li>';
        $html .= '<li>' . sprintf(__('Unique Visitors: %d', 'ymcp'), $data['overview']['unique_visitors']) . '</li>';
        $html .= '<li>' . sprintf(__('Sessions: %d', 'ymcp'), $data['overview']['sessions']) . '</li>';
        $html .= '<li>' . sprintf(__('Bounce Rate: %.2f%%', 'ymcp'), $data['overview']['bounce_rate']) . '</li>';
        $html .= '</ul>';

        $html .= '<h3>' . __('Top Pages', 'ymcp') . '</h3>';
        $html .= '<ul>';
        foreach ($data['top_pages'] as $page) {
            $html .= '<li>' . esc_html($page->page_title) . ' - ' . $page->views . ' views</li>';
        }
        $html .= '</ul>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSV Report
     */
    private function generate_csv_report($data) {
        $csv = 'Page URL,Page Title,Views' . "\n";

        foreach ($data['top_pages'] as $page) {
            $csv .= '"' . $page->page_url . '","' . $page->page_title . '",' . $page->views . "\n";
        }

        return $csv;
    }

    /**
     * Clear Analytics Data
     */
    public function clear_analytics($before_date = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . YMCP_DB_PREFIX . 'analytics';

        if ($before_date) {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE created_at < %s",
                $before_date
            ));
        } else {
            $wpdb->query("TRUNCATE TABLE $table_name");
        }

        return array('success' => true);
    }

    /**
     * Export Analytics
     */
    public function export_analytics($params = array()) {
        $format = isset($params['format']) ? $params['format'] : 'json';
        $report = $this->generate_report($params);

        if (is_wp_error($report)) {
            return $report;
        }

        $filename = 'ymcp-analytics-' . date('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $report;
            exit;
        } else {
            return $report;
        }
    }
}
