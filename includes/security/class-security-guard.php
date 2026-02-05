<?php
/**
 * YMCP Security Guard - نظام حماية متقدم
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class YMCP_Security_Guard {

    /**
     * Security issues found
     */
    private $security_issues = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Hooks
        add_action('init', array($this, 'security_check'));
        add_action('admin_notices', array($this, 'security_notices'));
    }

    /**
     * Run Security Scan
     */
    public function scan() {
        $this->security_issues = array();

        // 1. Check WordPress version
        $this->check_wp_version();

        // 2. Check plugins for vulnerabilities
        $this->check_plugins();

        // 3. Check themes for vulnerabilities
        $this->check_themes();

        // 4. Check file permissions
        $this->check_file_permissions();

        // 5. Check database security
        $this->check_database_security();

        // 6. Check SSL
        $this->check_ssl();

        // 7. Check admin protection
        $this->check_admin_protection();

        // 8. Check login security
        $this->check_login_security();

        // 9. Check XML-RPC
        $this->check_xmlrpc();

        // 10. Check for malware
        $this->check_malware();

        // Calculate security score
        $score = $this->calculate_score();

        // Save results
        update_option('ymcp_security_scan_results', $this->security_issues);
        update_option('ymcp_security_score', $score);
        update_option('ymcp_last_security_scan', time());

        return array(
            'success' => true,
            'score' => $score,
            'issues' => $this->security_issues,
            'scan_date' => current_time('mysql'),
        );
    }

    /**
     * Check WordPress Version
     */
    private function check_wp_version() {
        global $wp_version;
        $latest_version = get_transient('ymcp_latest_wp_version');

        if (!$latest_version) {
            $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
            if (!is_wp_error($response)) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $latest_version = $body['offers'][0]['version'];
                set_transient('ymcp_latest_wp_version', $latest_version, DAY_IN_SECONDS);
            }
        }

        if (version_compare($wp_version, $latest_version, '<')) {
            $this->add_issue('critical', 'outdated_wordpress', sprintf(__('WordPress is outdated (v%s, latest: v%s)', 'ymcp'), $wp_version, $latest_version));
        }
    }

    /**
     * Check Plugins
     */
    private function check_plugins() {
        $plugins = get_plugins();
        $update_plugins = get_plugin_updates();

        foreach ($plugins as $plugin_file => $plugin_data) {
            // Check for updates
            if (isset($update_plugins[$plugin_file])) {
                $this->add_issue('warning', 'outdated_plugin', sprintf(__('Plugin "%s" is outdated', 'ymcp'), $plugin_data['Name']));
            }

            // Check for inactive plugins
            if (is_plugin_inactive($plugin_file)) {
                $this->add_issue('low', 'inactive_plugin', sprintf(__('Plugin "%s" is inactive', 'ymcp'), $plugin_data['Name']));
            }
        }
    }

    /**
     * Check Themes
     */
    private function check_themes() {
        $themes = wp_get_themes();
        $update_themes = get_theme_updates();

        foreach ($themes as $theme_slug => $theme) {
            // Check for updates
            if (isset($update_themes[$theme_slug])) {
                $this->add_issue('warning', 'outdated_theme', sprintf(__('Theme "%s" is outdated', 'ymcp'), $theme->get('Name')));
            }

            // Check for active theme vulnerabilities
            if ($theme->is_active()) {
                // Check against known vulnerabilities (simplified)
                if ($theme->get('Author') === 'Unknown') {
                    $this->add_issue('warning', 'unknown_theme_author', sprintf(__('Theme "%s" has unknown author', 'ymcp'), $theme->get('Name')));
                }
            }
        }
    }

    /**
     * Check File Permissions
     */
    private function check_file_permissions() {
        $wp_config = ABSPATH . 'wp-config.php';
        $uploads_dir = wp_upload_dir();

        // Check wp-config.php
        if (file_exists($wp_config)) {
            $perms = substr(sprintf('%o', fileperms($wp_config)), -4);
            if (octdec($perms) > 0644) {
                $this->add_issue('warning', 'wp_config_permissions', sprintf(__('wp-config.php has too permissive permissions (%s)', 'ymcp'), $perms));
            }
        }

        // Check uploads directory
        if (is_writable($uploads_dir['basedir'])) {
            // This is actually good - uploads should be writable
        }
    }

    /**
     * Check Database Security
     */
    private function check_database_security() {
        global $wpdb;

        // Check for table prefix
        if ($wpdb->prefix === 'wp_') {
            $this->add_issue('low', 'default_table_prefix', __('Database table prefix is still default (wp_)', 'ymcp'));
        }

        // Check for exposed database (simplified check)
        $test_url = site_url('/wp-admin/install.php');
        $response = wp_remote_head($test_url);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $this->add_issue('critical', 'install_php_accessible', __('install.php is accessible', 'ymcp'));
        }
    }

    /**
     * Check SSL
     */
    private function check_ssl() {
        if (!is_ssl()) {
            $this->add_issue('warning', 'no_ssl', __('Site is not using SSL', 'ymcp'));
        }
    }

    /**
     * Check Admin Protection
     */
    private function check_admin_protection() {
        // Check if admin URL is changed
        if (class_exists('WPS_Hide_Login')) {
            // Good - admin URL is hidden
        } else {
            $this->add_issue('low', 'default_admin_url', __('Admin URL is still default (wp-admin)', 'ymcp'));
        }

        // Check for admin IP restriction
        if (!defined('YMCP_ADMIN_IP_RESTRICTION')) {
            $this->add_issue('low', 'no_ip_restriction', __('No admin IP restriction configured', 'ymcp'));
        }
    }

    /**
     * Check Login Security
     */
    private function check_login_security() {
        // Check for brute force protection
        if (!class_exists('Limit_Login_Attempts_Reloaded') && !class_exists('Wordfence')) {
            $this->add_issue('warning', 'no_brute_force_protection', __('No brute force protection installed', 'ymcp'));
        }

        // Check for two-factor authentication
        if (!class_exists('Two_Factor_Core') && !class_exists('Wordfence')) {
            $this->add_issue('low', 'no_2fa', __('No two-factor authentication configured', 'ymcp'));
        }
    }

    /**
     * Check XML-RPC
     */
    private function check_xmlrpc() {
        if (get_option('enable_xmlrpc', false)) {
            $this->add_issue('warning', 'xmlrpc_enabled', __('XML-RPC is enabled (security risk)', 'ymcp'));
        }
    }

    /**
     * Check for Malware
     */
    private function check_malware() {
        // Check for suspicious files
        $suspicious_files = array('.htaccess.old', 'php.ini.php', 'wp-config.php~');
        $found = array();

        foreach ($suspicious_files as $file) {
            if (file_exists(ABSPATH . $file)) {
                $found[] = $file;
            }
        }

        if (!empty($found)) {
            $this->add_issue('critical', 'suspicious_files', sprintf(__('Suspicious files found: %s', 'ymcp'), implode(', ', $found)));
        }

        // Check for suspicious code in wp-config.php
        $wp_config = file_get_contents(ABSPATH . 'wp-config.php');
        $suspicious_patterns = array('/eval\s*\(/', '/base64_decode\s*\(/', '/gzinflate\s*\(/');

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $wp_config)) {
                $this->add_issue('critical', 'suspicious_code', __('Suspicious code found in wp-config.php', 'ymcp'));
                break;
            }
        }
    }

    /**
     * Add Security Issue
     */
    private function add_issue($severity, $code, $message) {
        $this->security_issues[] = array(
            'severity' => $severity,
            'code' => $code,
            'message' => $message,
            'timestamp' => current_time('mysql'),
        );
    }

    /**
     * Calculate Security Score
     */
    private function calculate_score() {
        $score = 100;
        $penalties = array(
            'critical' => 25,
            'warning' => 10,
            'low' => 5,
        );

        foreach ($this->security_issues as $issue) {
            $score -= $penalties[$issue['severity']];
        }

        return max(0, $score);
    }

    /**
     * Get Security Report
     */
    public function get_security_report() {
        $results = get_option('ymcp_security_scan_results', array());
        $score = get_option('ymcp_security_score', 100);
        $last_scan = get_option('ymcp_last_security_scan', 0);

        return array(
            'score' => $score,
            'issues' => $results,
            'last_scan' => $last_scan ? date('Y-m-d H:i:s', $last_scan) : null,
            'scan_needed' => (time() - $last_scan) > (7 * DAY_IN_SECONDS),
        );
    }

    /**
     * Security Check
     */
    public function security_check() {
        // Run periodic checks
        $last_check = get_option('ymcp_last_security_check', 0);

        if ((time() - $last_check) > DAY_IN_SECONDS) {
            // Run security scan
            $this->scan();
            update_option('ymcp_last_security_check', time());
        }
    }

    /**
     * Security Notices
     */
    public function security_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $report = $this->get_security_report();

        if ($report['score'] < 80) {
            $class = $report['score'] < 50 ? 'error' : 'warning';
            echo '<div class="notice notice-' . $class . ' is-dismissible">';
            echo '<p><strong>' . __('YMCP Security Alert:', 'ymcp') . '</strong> ';
            printf(__('Your site security score is %d. Run a security scan to improve it.', 'ymcp'), $report['score']);
            echo ' <a href="' . admin_url('admin.php?page=ymcp-security') . '">' . __('View Details', 'ymcp') . '</a></p>';
            echo '</div>';
        }
    }

    /**
     * Fix Security Issue
     */
    public function fix_issue($issue_code) {
        switch ($issue_code) {
            case 'default_table_prefix':
                // Cannot fix automatically - user action required
                return new WP_Error('manual_fix', __('This issue requires manual fixing.', 'ymcp'));

            case 'xmlrpc_enabled':
                update_option('enable_xmlrpc', false);
                return array('success' => true, 'message' => __('XML-RPC disabled', 'ymcp'));

            default:
                return new WP_Error('unknown_issue', __('Unknown issue code', 'ymcp'));
        }
    }
}
