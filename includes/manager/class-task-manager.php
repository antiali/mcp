<?php
/**
 * YMCP Task Manager - إدارة مهام متقدمة
 *
 * @package YMCP
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class YMCP_Task_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_tables();
    }

    /**
     * Initialize Hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_ymcp_create_task', array($this, 'ajax_create_task'));
        add_action('wp_ajax_ymcp_update_task', array($this, 'ajax_update_task'));
        add_action('wp_ajax_ymcp_delete_task', array($this, 'ajax_delete_task'));
        add_action('wp_ajax_ymcp_get_tasks', array($this, 'ajax_get_tasks'));
        add_action('wp_ajax_ymcp_complete_task', array($this, 'ajax_complete_task'));
    }

    /**
     * Initialize Tables
     */
    private function init_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            status varchar(50) NOT NULL DEFAULT 'pending',
            priority tinyint(1) NOT NULL DEFAULT 3,
            category varchar(50) DEFAULT 'general',
            assigned_to bigint(20),
            created_by bigint(20) NOT NULL,
            due_date datetime,
            completed_at datetime,
            estimated_hours decimal(5,2),
            actual_hours decimal(5,2),
            tags longtext,
            attachments longtext,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY priority (priority),
            KEY category (category),
            KEY assigned_to (assigned_to),
            KEY due_date (due_date),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create Task
     */
    public function create_task($task_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $defaults = array(
            'status' => 'pending',
            'priority' => 3,
            'category' => 'general',
            'estimated_hours' => 0,
        );

        $task_data = wp_parse_args($task_data, $defaults);

        $result = $wpdb->insert(
            $table_name,
            array(
                'title' => sanitize_text_field($task_data['title']),
                'description' => sanitize_textarea_field($task_data['description']),
                'status' => $task_data['status'],
                'priority' => intval($task_data['priority']),
                'category' => sanitize_text_field($task_data['category']),
                'assigned_to' => isset($task_data['assigned_to']) ? intval($task_data['assigned_to']) : null,
                'due_date' => isset($task_data['due_date']) && $task_data['due_date'] ? $task_data['due_date'] : null,
                'estimated_hours' => floatval($task_data['estimated_hours']),
                'tags' => isset($task_data['tags']) ? json_encode($task_data['tags']) : null,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%f', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result) {
            do_action('ymcp_task_created', $wpdb->insert_id, $task_data);
        }

        return $result ? $wpdb->insert_id : new WP_Error('task_create_failed', 'Failed to create task');
    }

    /**
     * Update Task
     */
    public function update_task($task_id, $task_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $update_data = array(
            'updated_at' => current_time('mysql'),
        );

        if (isset($task_data['title'])) {
            $update_data['title'] = sanitize_text_field($task_data['title']);
        }

        if (isset($task_data['description'])) {
            $update_data['description'] = sanitize_textarea_field($task_data['description']);
        }

        if (isset($task_data['status'])) {
            $update_data['status'] = $task_data['status'];
            if ($task_data['status'] === 'completed' && empty($task_data['completed_at'])) {
                $update_data['completed_at'] = current_time('mysql');
            }
        }

        if (isset($task_data['priority'])) {
            $update_data['priority'] = intval($task_data['priority']);
        }

        if (isset($task_data['category'])) {
            $update_data['category'] = sanitize_text_field($task_data['category']);
        }

        if (isset($task_data['assigned_to'])) {
            $update_data['assigned_to'] = intval($task_data['assigned_to']);
        }

        if (isset($task_data['due_date'])) {
            $update_data['due_date'] = $task_data['due_date'] ? $task_data['due_date'] : null;
        }

        if (isset($task_data['actual_hours'])) {
            $update_data['actual_hours'] = floatval($task_data['actual_hours']);
        }

        if (isset($task_data['tags'])) {
            $update_data['tags'] = json_encode($task_data['tags']);
        }

        if (isset($task_data['attachments'])) {
            $update_data['attachments'] = json_encode($task_data['attachments']);
        }

        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => intval($task_id))
        );

        if ($result) {
            do_action('ymcp_task_updated', $task_id, $update_data);
        }

        return $result;
    }

    /**
     * Delete Task
     */
    public function delete_task($task_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $result = $wpdb->delete(
            $table_name,
            array('id' => intval($task_id))
        );

        if ($result) {
            do_action('ymcp_task_deleted', $task_id);
        }

        return $result;
    }

    /**
     * Complete Task
     */
    public function complete_task($task_id, $actual_hours = null) {
        $task_data = array(
            'status' => 'completed',
            'completed_at' => current_time('mysql'),
        );

        if ($actual_hours !== null) {
            $task_data['actual_hours'] = floatval($actual_hours);
        }

        return $this->update_task($task_id, $task_data);
    }

    /**
     * Get Tasks
     */
    public function get_tasks($filters = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $where = array('1=1');
        $args = array();

        if (isset($filters['status']) && !empty($filters['status'])) {
            $where[] = 'status = %s';
            $args[] = $filters['status'];
        }

        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $where[] = 'priority = %d';
            $args[] = intval($filters['priority']);
        }

        if (isset($filters['category']) && !empty($filters['category'])) {
            $where[] = 'category = %s';
            $args[] = $filters['category'];
        }

        if (isset($filters['assigned_to']) && !empty($filters['assigned_to'])) {
            $where[] = 'assigned_to = %d';
            $args[] = intval($filters['assigned_to']);
        }

        if (isset($filters['due_before']) && !empty($filters['due_before'])) {
            $where[] = 'due_date <= %s';
            $args[] = $filters['due_before'];
        }

        if (isset($filters['overdue'])) {
            $where[] = 'due_date < %s AND status != %s';
            $args[] = current_time('mysql');
            $args[] = 'completed';
        }

        $orderby = isset($filters['orderby']) ? $filters['orderby'] : 'created_at DESC';
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;

        $where_sql = implode(' AND ', $where);
        $sql = "SELECT * FROM $table_name WHERE $where_sql ORDER BY $orderby LIMIT %d";

        if (!empty($args)) {
            $sql = $wpdb->prepare($sql, $args);
        } else {
            $sql = $sql . $limit;
        }

        $tasks = $wpdb->get_results($sql);

        foreach ($tasks as &$task) {
            $task->tags = isset($task->tags) ? json_decode($task->tags, true) : array();
            $task->attachments = isset($task->attachments) ? json_decode($task->attachments, true) : array();
            $task->status_class = $this->get_status_class($task->status);
            $task->priority_class = $this->get_priority_class($task->priority);
        }

        return $tasks;
    }

    /**
     * Get Task Statistics
     */
    public function get_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ymcp_tasks';

        $stats = array();

        // Status breakdown
        $status_query = "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status";
        $status_results = $wpdb->get_results($status_query);
        
        foreach ($status_results as $result) {
            $stats['status'][$result->status] = intval($result->count);
        }

        // Priority breakdown
        $priority_query = "SELECT priority, COUNT(*) as count FROM $table_name GROUP BY priority";
        $priority_results = $wpdb->get_results($priority_query);
        
        foreach ($priority_results as $result) {
            $stats['priority'][$result->priority] = intval($result->count);
        }

        // Overdue tasks
        $overdue_query = "SELECT COUNT(*) as count FROM $table_name WHERE due_date < %s AND status != %s";
        $overdue_count = $wpdb->get_var($wpdb->prepare($overdue_query, current_time('mysql'), 'completed'));
        $stats['overdue'] = intval($overdue_count);

        // Completed this week
        $week_query = "SELECT COUNT(*) as count FROM $table_name WHERE completed_at >= DATE_SUB(%s, INTERVAL 7 DAY)";
        $week_count = $wpdb->get_var($wpdb->prepare($week_query, current_time('mysql')));
        $stats['completed_this_week'] = intval($week_count);

        // Total hours tracking
        $hours_query = "SELECT SUM(actual_hours) as total, SUM(estimated_hours) as estimated FROM $table_name";
        $hours = $wpdb->get_row($hours_query);
        
        $stats['hours'] = array(
            'actual' => floatval($hours->total),
            'estimated' => floatval($hours->estimated),
        );

        return $stats;
    }

    /**
     * Get Status Class
     */
    private function get_status_class($status) {
        $classes = array(
            'pending' => 'status-pending',
            'in_progress' => 'status-in-progress',
            'completed' => 'status-completed',
            'on_hold' => 'status-on-hold',
            'cancelled' => 'status-cancelled',
        );

        return isset($classes[$status]) ? $classes[$status] : 'status-pending';
    }

    /**
     * Get Priority Class
     */
    private function get_priority_class($priority) {
        $classes = array(
            1 => 'priority-critical',
            2 => 'priority-high',
            3 => 'priority-medium',
            4 => 'priority-low',
        );

        return isset($classes[$priority]) ? $classes[$priority] : 'priority-medium';
    }

    /**
     * AJAX: Create Task
     */
    public function ajax_create_task() {
        check_ajax_referer('ymcp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ymcp')));
        }

        $task_data = $_POST['task'] ?? array();

        if (empty($task_data['title'])) {
            wp_send_json_error(array('message' => __('Task title is required', 'ymcp')));
        }

        $result = $this->create_task($task_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Task created successfully', 'ymcp'),
            'task_id' => $result
        ));
    }

    /**
     * AJAX: Update Task
     */
    public function ajax_update_task() {
        check_ajax_referer('ymcp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ymcp')));
        }

        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
        $task_data = isset($_POST['task']) ? $_POST['task'] : array();

        if ($task_id === 0) {
            wp_send_json_error(array('message' => __('Task ID is required', 'ymcp')));
        }

        $result = $this->update_task($task_id, $task_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Task updated successfully', 'ymcp'),
        'task_id' => $task_id
        ));
    }

    /**
     * AJAX: Delete Task
     */
    public function ajax_delete_task() {
        check_ajax_referer('ymcp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ymcp')));
        }

        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

        if ($task_id === 0) {
            wp_send_json_error(array('message' => __('Task ID is required', 'ymcp')));
        }

        $result = $this->delete_task($task_id);

        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to delete task', 'ymcp')));
        }

        wp_send_json_success(array(
            'message' => __('Task deleted successfully', 'ymcp'),
            'task_id' => $task_id
        ));
    }

    /**
     * AJAX: Get Tasks
     */
    public function ajax_get_tasks() {
        check_ajax_referer('ymcp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ymcp')));
        }

        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        $tasks = $this->get_tasks($filters);
        $stats = $this->get_statistics();

        wp_send_json_success(array(
            'tasks' => $tasks,
            'statistics' => $stats
        ));
    }

    /**
     * AJAX: Complete Task
     */
    public function ajax_complete_task() {
        check_ajax_referer('ymcp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ymcp')));
        }

        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
        $actual_hours = isset($_POST['actual_hours']) ? $_POST['actual_hours'] : null;

        if ($task_id === 0) {
            wp_send_json_error(array('message' => __('Task ID is required', 'ymcp')));
        }

        $result = $this->complete_task($task_id, $actual_hours);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Task completed successfully', 'ymcp'),
            'task_id' => $task_id
        ));
    }
}
