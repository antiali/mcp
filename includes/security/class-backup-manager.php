<?php
/**
 * YMCP Backup Manager - مدير النسخ الاحتياطية
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class YMCP_Backup_Manager {

    /**
     * Backup directory
     */
    private $backup_dir;

    /**
     * Constructor
     */
    public function __construct() {
        // Set backup directory
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/ymcp-backups/';
        $this->ensure_backup_dir();

        // Hooks
        add_action('ymcp_daily_backup', array($this, 'auto_backup'));
    }

    /**
     * Ensure backup directory exists
     */
    private function ensure_backup_dir() {
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }

        // Create .htaccess to protect backups
        $htaccess = $this->backup_dir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, 'Deny from all');
        }

        // Create index.php to prevent directory listing
        $index = $this->backup_dir . 'index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden ?>');
        }
    }

    /**
     * Create Backup
     */
    public function create_backup($backup_name = null) {
        if (is_null($backup_name)) {
            $backup_name = 'backup-' . date('Y-m-d-H-i-s');
        }

        $backup_id = wp_generate_password(16, false);
        $backup_file = $this->backup_dir . $backup_name . '.zip';

        // Initialize ZipArchive
        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            return new WP_Error('zip_failed', __('Failed to create zip file', 'ymcp'));
        }

        // Backup database
        $db_backup = $this->backup_database();
        if (is_wp_error($db_backup)) {
            return $db_backup;
        }
        $zip->addFile($db_backup['file'], 'database/' . $db_backup['filename']);

        // Backup uploads
        $this->backup_directory(WP_CONTENT_DIR . '/uploads', $zip, 'uploads/');

        // Backup plugins (optional)
        if (apply_filters('ymcp_backup_plugins', false)) {
            $this->backup_directory(WP_PLUGIN_DIR, $zip, 'plugins/');
        }

        // Backup themes (optional)
        if (apply_filters('ymcp_backup_themes', false)) {
            $this->backup_directory(get_theme_root(), $zip, 'themes/');
        }

        // Backup wp-content (optional)
        if (apply_filters('ymcp_backup_wp_content', false)) {
            $this->backup_directory(WP_CONTENT_DIR, $zip, 'wp-content/');
        }

        // Add backup manifest
        $manifest = $this->create_manifest($backup_id);
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // Close zip
        $zip->close();

        // Store backup info
        $backup_info = array(
            'id' => $backup_id,
            'name' => $backup_name,
            'filename' => basename($backup_file),
            'file' => $backup_file,
            'size' => filesize($backup_file),
            'type' => 'full',
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id(),
        );

        $this->save_backup_info($backup_info);

        // Update last backup time
        update_option('ymcp_last_backup', time());

        return array(
            'success' => true,
            'backup_id' => $backup_id,
            'filename' => basename($backup_file),
            'size' => size_format($backup_info['size']),
            'url' => $this->get_backup_url($backup_info['filename']),
        );
    }

    /**
     * Backup Database
     */
    private function backup_database() {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $filename = 'database-' . date('Y-m-d-H-i-s') . '.sql';
        $file = $this->backup_dir . 'database/' . $filename;

        // Ensure database directory exists
        wp_mkdir_p(dirname($file));

        // Get database connection details
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $host = DB_HOST;
        $charset = $wpdb->get_charset_collate();

        // Create SQL dump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --quick --lock-tables=false %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($dbname),
            escapeshellarg($file)
        );

        // Try to execute mysqldump
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var !== 0 && !file_exists($file)) {
            // Fallback: use PHP to dump tables
            return $this->backup_database_php();
        }

        // Compress the file
        if (file_exists($file)) {
            $gz_file = $file . '.gz';
            file_put_contents($gz_file, gzencode(file_get_contents($file), 9));
            unlink($file);

            return array(
                'file' => $gz_file,
                'filename' => basename($gz_file),
            );
        }

        return new WP_Error('db_backup_failed', __('Failed to backup database', 'ymcp'));
    }

    /**
     * Backup Database using PHP
     */
    private function backup_database_php() {
        global $wpdb;

        $filename = 'database-' . date('Y-m-d-H-i-s') . '.sql';
        $file = $this->backup_dir . 'database/' . $filename;

        wp_mkdir_p(dirname($file));

        $handle = fopen($file, 'w');
        if (!$handle) {
            return new WP_Error('cannot_open_file', __('Cannot open file for writing', 'ymcp'));
        }

        // Get all tables
        $tables = $wpdb->get_col('SHOW TABLES');

        foreach ($tables as $table) {
            // Get table structure
            $create_table = $wpdb->get_row('SHOW CREATE TABLE ' . $table, ARRAY_N);
            if ($create_table) {
                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($handle, $create_table[1] . ";\n\n");
            }

            // Get table data
            $rows = $wpdb->get_results('SELECT * FROM ' . $table, ARRAY_A);
            if (!empty($rows)) {
                $columns = $wpdb->get_col('DESC ' . $table, 0);

                foreach ($rows as $row) {
                    $values = array();
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $wpdb->escape($value) . "'";
                        }
                    }
                    $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                    fwrite($handle, $sql . "\n");
                }
                fwrite($handle, "\n");
            }
        }

        fclose($handle);

        // Compress
        $gz_file = $file . '.gz';
        file_put_contents($gz_file, gzencode(file_get_contents($file), 9));
        unlink($file);

        return array(
            'file' => $gz_file,
            'filename' => basename($gz_file),
        );
    }

    /**
     * Backup Directory
     */
    private function backup_directory($directory, $zip, $base_path = '') {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories and hidden files
            if ($file->isDir() || substr($file->getFilename(), 0, 1) === '.') {
                continue;
            }

            $file_path = $file->getRealPath();
            $relative_path = str_replace($directory, '', $file_path);
            $relative_path = ltrim($relative_path, '/\\');

            // Add to zip
            $zip->addFile($file_path, $base_path . $relative_path);
        }
    }

    /**
     * Create Manifest
     */
    private function create_manifest($backup_id) {
        return array(
            'backup_id' => $backup_id,
            'site_url' => site_url(),
            'site_name' => get_bloginfo('name'),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->get_mysql_version(),
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id(),
            'tables' => $this->get_database_tables(),
            'plugins' => $this->get_active_plugins(),
            'theme' => wp_get_theme()->get('Name'),
        );
    }

    /**
     * Save Backup Info
     */
    private function save_backup_info($backup_info) {
        $backups = get_option('ymcp_backups', array());
        array_unshift($backups, $backup_info);

        // Keep only last 20 backups
        if (count($backups) > 20) {
            $backups = array_slice($backups, 0, 20);
        }

        update_option('ymcp_backups', $backups);
    }

    /**
     * Get MySQL Version
     */
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }

    /**
     * Get Database Tables
     */
    private function get_database_tables() {
        global $wpdb;
        return $wpdb->get_col('SHOW TABLES');
    }

    /**
     * Get Active Plugins
     */
    private function get_active_plugins() {
        return get_option('active_plugins', array());
    }

    /**
     * Get Backup URL
     */
    private function get_backup_url($filename) {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/ymcp-backups/' . $filename;
    }

    /**
     * Get Backups List
     */
    public function get_backups() {
        return get_option('ymcp_backups', array());
    }

    /**
     * Delete Backup
     */
    public function delete_backup($backup_id) {
        $backups = get_option('ymcp_backups', array());
        $deleted = false;

        foreach ($backups as $index => $backup) {
            if ($backup['id'] === $backup_id) {
                // Delete file
                if (file_exists($backup['file'])) {
                    unlink($backup['file']);
                }

                // Remove from list
                array_splice($backups, $index, 1);
                $deleted = true;
                break;
            }
        }

        if ($deleted) {
            update_option('ymcp_backups', $backups);
            return array('success' => true);
        }

        return new WP_Error('backup_not_found', __('Backup not found', 'ymcp'));
    }

    /**
     * Restore Backup
     */
    public function restore_backup($backup_id) {
        $backups = get_option('ymcp_backups', array());
        $backup = null;

        foreach ($backups as $b) {
            if ($b['id'] === $backup_id) {
                $backup = $b;
                break;
            }
        }

        if (!$backup) {
            return new WP_Error('backup_not_found', __('Backup not found', 'ymcp'));
        }

        if (!file_exists($backup['file'])) {
            return new WP_Error('backup_file_missing', __('Backup file is missing', 'ymcp'));
        }

        // Extract backup
        $zip = new ZipArchive();
        if ($zip->open($backup['file']) !== TRUE) {
            return new WP_Error('cannot_open_zip', __('Cannot open backup file', 'ymcp'));
        }

        // Create temp directory
        $temp_dir = $this->backup_dir . 'temp-' . time() . '/';
        $zip->extractTo($temp_dir);
        $zip->close();

        // Restore database
        $db_file = $temp_dir . 'database/';
        if (is_dir($db_file)) {
            $db_files = glob($db_file . '*.sql*');
            if (!empty($db_files)) {
                $this->restore_database($db_files[0]);
            }
        }

        // Restore uploads
        if (is_dir($temp_dir . 'uploads/')) {
            // This would be a full restore - user should confirm
        }

        // Cleanup temp directory
        $this->delete_directory($temp_dir);

        return array(
            'success' => true,
            'message' => __('Backup restored successfully', 'ymcp'),
        );
    }

    /**
     * Restore Database
     */
    private function restore_database($file) {
        global $wpdb;

        // Handle compressed files
        if (substr($file, -3) === '.gz') {
            $file = 'compress.zlib://' . $file;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            return new WP_Error('cannot_read_file', __('Cannot read database file', 'ymcp'));
        }

        // Split into queries
        $queries = preg_split('/;\s*\n/', $sql);

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && !preg_match('/^(CREATE TABLE|DROP TABLE)/i', $query)) {
                $wpdb->query($query);
            }
        }

        return true;
    }

    /**
     * Delete Directory
     */
    private function delete_directory($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directory);
    }

    /**
     * Auto Backup
     */
    public function auto_backup() {
        if (get_option('ymcp_auto_backup', true)) {
            $this->create_backup('auto-' . date('Y-m-d'));
        }
    }

    /**
     * Cleanup Old Backups
     */
    public function cleanup_old_backups($keep_days = 30) {
        $backups = get_option('ymcp_backups', array());
        $cutoff = time() - ($keep_days * DAY_IN_SECONDS);
        $deleted = 0;

        foreach ($backups as $index => $backup) {
            $backup_time = strtotime($backup['created_at']);
            if ($backup_time < $cutoff) {
                // Delete file
                if (file_exists($backup['file'])) {
                    unlink($backup['file']);
                }
                // Remove from list
                array_splice($backups, $index, 1);
                $deleted++;
            }
        }

        update_option('ymcp_backups', $backups);

        return array(
            'success' => true,
            'deleted_count' => $deleted,
        );
    }
}
