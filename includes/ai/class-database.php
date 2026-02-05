<?php
/**
 * Database operations handler
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Database class
 */
class Database {

    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Table names
     */
    private $projects_table;
    private $generations_table;
    private $usage_table;
    private $history_table;
    private $templates_table;
    private $cache_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        
        $this->wpdb              = $wpdb;
        $this->projects_table    = $wpdb->prefix . 'aisbp_projects';
        $this->generations_table = $wpdb->prefix . 'aisbp_generations';
        $this->usage_table       = $wpdb->prefix . 'aisbp_token_usage';
        $this->history_table     = $wpdb->prefix . 'aisbp_history';
        $this->templates_table   = $wpdb->prefix . 'aisbp_templates';
        $this->cache_table       = $wpdb->prefix . 'aisbp_cache';
    }

    /**
     * Create a new project
     *
     * @param array $data Project data.
     * @return int|WP_Error Project ID or error.
     */
    public function create_project( $data ) {
        try {
            // RELIABILITY: Check if table exists before attempting insert
            if ( ! $this->table_exists( $this->projects_table ) ) {
                error_log( 'AISBP: Projects table does not exist. Attempting to create...' );
                
                // MULTISITE COMPATIBLE: Try to create table directly
                try {
                    if ( defined( 'ABSPATH' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                    } else {
                        require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
                    }
                    $charset_collate = $this->wpdb->get_charset_collate();
                    
                    $sql = "CREATE TABLE IF NOT EXISTS {$this->projects_table} (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        user_id BIGINT UNSIGNED NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        description TEXT,
                        website_type VARCHAR(100) DEFAULT 'business',
                        industry VARCHAR(100) DEFAULT '',
                        settings LONGTEXT,
                        inputs LONGTEXT,
                        generated_code LONGTEXT,
                        preview_url VARCHAR(500) DEFAULT '',
                        ai_model VARCHAR(50) DEFAULT 'deepseek',
                        creation_mode VARCHAR(50) DEFAULT 'full_site',
                        status VARCHAR(50) DEFAULT 'draft',
                        total_tokens INT UNSIGNED DEFAULT 0,
                        total_cost DECIMAL(10,6) DEFAULT 0.000000,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_user_id (user_id),
                        INDEX idx_status (status),
                        INDEX idx_user_status (user_id, status),
                        INDEX idx_updated_at (updated_at)
                    ) $charset_collate;";
                    
                    if ( function_exists( 'dbDelta' ) ) {
                        dbDelta( $sql );
                    } elseif ( function_exists( '\dbDelta' ) ) {
                        \dbDelta( $sql );
                    } else {
                        // Fallback: Direct query
                        $this->wpdb->query( $sql );
                    }
                    
                    // Check again after creation attempt
                    if ( ! $this->table_exists( $this->projects_table ) ) {
                        error_log( 'AISBP: Failed to create projects table. Last error: ' . $this->wpdb->last_error );
                        return new \WP_Error( 
                            'table_missing', 
                            \__( 'Database table does not exist. Please deactivate and reactivate the plugin.', 'ai-site-builder-pro' ),
                            array( 
                                'table' => $this->projects_table,
                                'db_error' => $this->wpdb->last_error,
                            )
                        );
                    }
                } catch ( \Throwable $e ) {
                    error_log( 'AISBP: Exception creating table: ' . $e->getMessage() );
                    return new \WP_Error( 
                        'table_creation_failed', 
                        \__( 'Failed to create database table.', 'ai-site-builder-pro' ),
                        array( 
                            'table' => $this->projects_table,
                            'exception' => $e->getMessage(),
                        )
                    );
                }
            }
            
            $defaults = array(
                'user_id'      => \get_current_user_id(),
                'name'         => '',
                'description'  => '',
                'website_type' => 'business',
                'industry'     => '',
                'settings'     => '{}',
                'inputs'       => '{}',
                'ai_model'     => \get_option( 'aisbp_default_model', 'deepseek' ),
                'status'       => 'draft',
            );

            $data = \wp_parse_args( $data, $defaults );

            // Sanitize
            $data = array(
                'user_id'      => \absint( $data['user_id'] ),
                'name'         => \sanitize_text_field( $data['name'] ),
                'description'  => \sanitize_textarea_field( $data['description'] ),
                'website_type' => \sanitize_text_field( $data['website_type'] ),
                'industry'     => \sanitize_text_field( $data['industry'] ),
                'settings'     => is_string( $data['settings'] ) ? $data['settings'] : \wp_json_encode( $data['settings'] ),
                'inputs'       => is_string( $data['inputs'] ) ? $data['inputs'] : \wp_json_encode( $data['inputs'] ),
                'ai_model'     => \sanitize_text_field( $data['ai_model'] ),
                'status'       => \sanitize_text_field( $data['status'] ),
            );

            $result = $this->wpdb->insert( $this->projects_table, $data );

            if ( false === $result ) {
                $error_msg = $this->wpdb->last_error ?: \__( 'Database insert failed.', 'ai-site-builder-pro' );
                error_log( 'AISBP create_project error: ' . $error_msg . ' | Table: ' . $this->projects_table . ' | Data keys: ' . implode( ', ', array_keys( $data ) ) );
                return new \WP_Error( 
                    'db_error', 
                    \__( 'Failed to create project.', 'ai-site-builder-pro' ),
                    array( 
                        'db_error' => $error_msg,
                        'table' => $this->projects_table,
                    )
                );
            }

            return $this->wpdb->insert_id;
        } catch ( \Throwable $e ) {
            error_log( 'AISBP create_project exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            return new \WP_Error( 
                'create_project_exception', 
                \__( 'Failed to create project.', 'ai-site-builder-pro' ),
                array( 'exception' => $e->getMessage() )
            );
        }
    }

    /**
     * Update a project
     *
     * @param int   $project_id Project ID.
     * @param array $data       Data to update.
     * @return bool|WP_Error True on success or error.
     */
    public function update_project( $project_id, $data ) {
        // Sanitize allowed fields
        $allowed = array(
            'name', 'description', 'website_type', 'industry',
            'settings', 'inputs', 'generated_code', 'preview_url',
            'status', 'ai_model', 'total_tokens', 'total_cost',
        );

        $update_data = array();
        foreach ( $allowed as $field ) {
            if ( isset( $data[ $field ] ) ) {
                switch ( $field ) {
                    case 'settings':
                    case 'inputs':
                    case 'generated_code':
                        $update_data[ $field ] = is_string( $data[ $field ] ) ? $data[ $field ] : \wp_json_encode( $data[ $field ] );
                        break;
                    case 'total_tokens':
                        $update_data[ $field ] = \absint( $data[ $field ] );
                        break;
                    case 'total_cost':
                        $update_data[ $field ] = floatval( $data[ $field ] );
                        break;
                    default:
                        $update_data[ $field ] = \sanitize_text_field( $data[ $field ] );
                }
            }
        }

        if ( empty( $update_data ) ) {
            return new \WP_Error( 'no_data', \__( 'No data to update.', 'ai-site-builder-pro' ) );
        }

        $result = $this->wpdb->update(
            $this->projects_table,
            $update_data,
            array( 'id' => \absint( $project_id ) )
        );

        if ( false === $result ) {
            return new \WP_Error( 'db_error', \__( 'Failed to update project.', 'ai-site-builder-pro' ) );
        }

        return true;
    }

    /**
     * Delete a project
     *
     * @param int $project_id Project ID.
     * @return bool|WP_Error True on success or error.
     */
    public function delete_project( $project_id ) {
        $project_id = \absint( $project_id );

        // Delete related generations
        $this->wpdb->delete( $this->generations_table, array( 'project_id' => $project_id ) );
        
        // Delete history
        $this->wpdb->delete( $this->history_table, array( 'project_id' => $project_id ) );

        // Delete project
        $result = $this->wpdb->delete( $this->projects_table, array( 'id' => $project_id ) );

        if ( false === $result ) {
            return new \WP_Error( 'db_error', \__( 'Failed to delete project.', 'ai-site-builder-pro' ) );
        }

        return true;
    }

    /**
     * Get a single project
     *
     * @param int $project_id Project ID.
     * @return array|null Project data or null.
     */
    public function get_project( $project_id ) {
        $project = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->projects_table} WHERE id = %d",
                \absint( $project_id )
            ),
            \ARRAY_A
        );

        if ( $project ) {
            $project['settings'] = json_decode( $project['settings'], true ) ?: array();
            $project['inputs'] = json_decode( $project['inputs'], true ) ?: array();
        }

        return $project;
    }

    /**
     * Get all projects for a user
     *
     * @param int    $user_id User ID.
     * @param string $status  Optional status filter.
     * @param int    $limit   Max results.
     * @param int    $offset  Offset for pagination.
     * @return array Projects.
     */
    public function get_projects( $user_id, $status = '', $limit = 20, $offset = 0 ) {
        $where = 'WHERE user_id = %d';
        $params = array( \absint( $user_id ) );

        if ( ! empty( $status ) ) {
            $where .= ' AND status = %s';
            $params[] = \sanitize_text_field( $status );
        }

        $params[] = \absint( $limit );
        $params[] = \absint( $offset );

        $projects = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT id, name, description, website_type, industry, status, ai_model, 
                        total_tokens, total_cost, preview_url, created_at, updated_at 
                 FROM {$this->projects_table} 
                 {$where} 
                 ORDER BY updated_at DESC 
                 LIMIT %d OFFSET %d",
                ...$params
            ),
            \ARRAY_A
        );

        return $projects ?: array();
    }

    /**
     * Get project count for a user
     *
     * @param int $user_id User ID.
     * @return int Count.
     */
    public function get_project_count( $user_id ) {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->projects_table} WHERE user_id = %d",
                \absint( $user_id )
            )
        );
    }

    /**
     * Save generation data
     *
     * @param array $data Generation data.
     * @return int|WP_Error Generation ID or error.
     */
    public function save_generation( $data ) {
        try {
            // Check if table exists, if not, return success (non-critical operation)
            if ( ! $this->table_exists( $this->generations_table ) ) {
                error_log( 'AISBP: Generations table does not exist. Skipping save.' );
                return true; // Return true instead of error to not break generation
            }
            
            $insert_data = array(
                'project_id'        => \absint( $data['project_id'] ?? 0 ),
                'phase'             => \absint( $data['phase'] ?? 0 ),
                'phase_name'        => \sanitize_text_field( $data['phase_name'] ?? '' ),
                'model'             => \sanitize_text_field( $data['model'] ?? '' ),
                'prompt'            => $data['prompt'] ?? '',
                'response'          => $data['response'] ?? '',
                'generated_code'    => $data['generated_code'] ?? '',
                'prompt_tokens'     => \absint( $data['prompt_tokens'] ?? 0 ),
                'completion_tokens' => \absint( $data['completion_tokens'] ?? 0 ),
                'cost_usd'          => floatval( $data['cost_usd'] ?? 0 ),
                'duration_ms'       => \absint( $data['duration_ms'] ?? 0 ),
                'status'            => \sanitize_text_field( $data['status'] ?? 'completed' ),
                'error_message'     => $data['error_message'] ?? '',
            );

            $result = $this->wpdb->insert( $this->generations_table, $insert_data );

            if ( false === $result ) {
                error_log( 'AISBP: Failed to save generation. Error: ' . $this->wpdb->last_error );
                // Return true instead of error to not break generation flow
                return true;
            }

            return $this->wpdb->insert_id;
        } catch ( \Throwable $e ) {
            error_log( 'AISBP: Exception in save_generation: ' . $e->getMessage() );
            // Return true to not break generation
            return true;
        }
    }
    
    /**
     * Check if table exists
     * 
     * @param string $table_name Table name.
     * @return bool True if table exists.
     */
    private function table_exists( $table_name ) {
        $table = $this->wpdb->get_var( $this->wpdb->prepare( 
            "SHOW TABLES LIKE %s", 
            $table_name 
        ) );
        return $table === $table_name;
    }

    /**
     * Get generations for a project
     *
     * @param int $project_id Project ID.
     * @return array Generations.
     */
    public function get_generations( $project_id ) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->generations_table} 
                 WHERE project_id = %d 
                 ORDER BY phase ASC, version DESC",
                \absint( $project_id )
            ),
            \ARRAY_A
        ) ?: array();
    }

    /**
     * Track token usage
     *
     * @param array $data Usage data.
     * @return bool Success.
     */
    public function track_usage( $data ) {
        $result = $this->wpdb->insert(
            $this->usage_table,
            array(
                'user_id'    => \absint( $data['user_id'] ?? \get_current_user_id() ),
                'project_id' => \absint( $data['project_id'] ?? 0 ),
                'model'      => \sanitize_text_field( $data['model'] ),
                'operation'  => \sanitize_text_field( $data['operation'] ?? '' ),
                'tokens_in'  => \absint( $data['tokens_in'] ?? 0 ),
                'tokens_out' => \absint( $data['tokens_out'] ?? 0 ),
                'cost_usd'   => floatval( $data['cost_usd'] ?? 0 ),
            )
        );

        return false !== $result;
    }

    /**
     * Get usage statistics
     *
     * @param int    $user_id User ID.
     * @param string $period  Time period (day, week, month, all).
     * @return array Stats.
     */
    public function get_usage_stats( $user_id, $period = 'month' ) {
        // SECURITY FIX: Validate period to prevent SQL injection
        $allowed_periods = array( 'day', 'week', 'month', 'all' );
        if ( ! in_array( $period, $allowed_periods, true ) ) {
            $period = 'month'; // Default fallback
        }
        
        $date_condition = '';
        
        switch ( $period ) {
            case 'day':
                $date_condition = "AND DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'all':
                $date_condition = '';
                break;
        }

        // Overall stats
        $overall = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT 
                    SUM(tokens_in) as total_tokens_in,
                    SUM(tokens_out) as total_tokens_out,
                    SUM(cost_usd) as total_cost,
                    COUNT(DISTINCT project_id) as projects_count
                 FROM {$this->usage_table} 
                 WHERE user_id = %d {$date_condition}",
                \absint( $user_id )
            ),
            \ARRAY_A
        );

        // By model
        $by_model = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                    model,
                    SUM(tokens_in) as tokens_in,
                    SUM(tokens_out) as tokens_out,
                    SUM(cost_usd) as cost,
                    COUNT(*) as requests
                 FROM {$this->usage_table} 
                 WHERE user_id = %d {$date_condition}
                 GROUP BY model",
                \absint( $user_id )
            ),
            \ARRAY_A
        );

        // Daily breakdown
        $daily = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(tokens_in + tokens_out) as tokens,
                    SUM(cost_usd) as cost
                 FROM {$this->usage_table} 
                 WHERE user_id = %d {$date_condition}
                 GROUP BY DATE(created_at)
                 ORDER BY date DESC
                 LIMIT 30",
                \absint( $user_id )
            ),
            \ARRAY_A
        );

        return array(
            'overall'  => $overall,
            'by_model' => $by_model ?: array(),
            'daily'    => $daily ?: array(),
        );
    }

    /**
     * Get usage breakdown by model
     *
     * @param int $user_id User ID.
     * @return array Usage by model.
     */
    public function get_usage_by_model( $user_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    model as model_id,
                    SUM(tokens_in + tokens_out) as total_tokens,
                    SUM(cost_usd) as total_cost,
                    COUNT(*) as generation_count
                FROM {$this->usage_table}
                WHERE user_id = %d
                GROUP BY model
                ORDER BY total_tokens DESC",
                \absint( $user_id )
            ),
            \ARRAY_A
        );
        
        return $results ?: array();
    }

    /**
     * Get daily usage statistics
     *
     * @param int $user_id User ID.
     * @param int $days    Number of days.
     * @return array Daily usage.
     */
    public function get_daily_usage( $user_id, $days = 30 ) {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(tokens_in + tokens_out) as total_tokens,
                    SUM(cost_usd) as total_cost,
                    COUNT(*) as generation_count
                FROM {$this->usage_table}
                WHERE user_id = %d
                    AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC",
                \absint( $user_id ),
                \absint( $days )
            ),
            \ARRAY_A
        );
        
        return $results ?: array();
    }


    /**
     * Add history entry
     *
     * @param array $data History data.
     * @return int|WP_Error History ID or error.
     */
    public function add_history( $data ) {
        $result = $this->wpdb->insert(
            $this->history_table,
            array(
                'project_id'         => \absint( $data['project_id'] ),
                'action_type'        => \sanitize_text_field( $data['action_type'] ),
                'action_description' => \sanitize_text_field( $data['action_description'] ?? '' ),
                'previous_state'     => is_string( $data['previous_state'] ) ? $data['previous_state'] : \wp_json_encode( $data['previous_state'] ),
                'current_state'      => is_string( $data['current_state'] ) ? $data['current_state'] : \wp_json_encode( $data['current_state'] ),
                'user_id'            => \absint( $data['user_id'] ?? \get_current_user_id() ),
            )
        );

        if ( false === $result ) {
            return new \WP_Error( 'db_error', \__( 'Failed to save history.', 'ai-site-builder-pro' ) );
        }

        // Cleanup old history (keep max 50)
        $max_states = \get_option( 'aisbp_max_history_states', 50 );
        $this->cleanup_history( $data['project_id'], $max_states );

        return $this->wpdb->insert_id;
    }

    /**
     * Undo last action
     *
     * @param int $project_id Project ID.
     * @return array|WP_Error Previous state or error.
     */
    public function undo( $project_id ) {
        $project_id = \absint( $project_id );

        // Get latest non-undone history
        $history = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->history_table} 
                 WHERE project_id = %d AND is_undone = 0 
                 ORDER BY created_at DESC 
                 LIMIT 1",
                $project_id
            ),
            \ARRAY_A
        );

        if ( ! $history ) {
            return new \WP_Error( 'no_history', \__( 'No more actions to undo.', 'ai-site-builder-pro' ) );
        }

        // Mark as undone
        $this->wpdb->update(
            $this->history_table,
            array( 'is_undone' => 1 ),
            array( 'id' => $history['id'] )
        );

        // Restore previous state
        $previous_state = json_decode( $history['previous_state'], true );
        
        if ( $previous_state && isset( $previous_state['generated_code'] ) ) {
            $this->update_project( $project_id, array(
                'generated_code' => $previous_state['generated_code'],
            ) );
        }

        return array(
            'state'       => $previous_state,
            'history_id'  => $history['id'],
            'action_type' => $history['action_type'],
        );
    }

    /**
     * Redo last undone action
     *
     * @param int $project_id Project ID.
     * @return array|WP_Error Current state or error.
     */
    public function redo( $project_id ) {
        $project_id = \absint( $project_id );

        // Get latest undone history
        $history = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->history_table} 
                 WHERE project_id = %d AND is_undone = 1 
                 ORDER BY created_at DESC 
                 LIMIT 1",
                $project_id
            ),
            \ARRAY_A
        );

        if ( ! $history ) {
            return new \WP_Error( 'no_history', \__( 'No more actions to redo.', 'ai-site-builder-pro' ) );
        }

        // Mark as not undone
        $this->wpdb->update(
            $this->history_table,
            array( 'is_undone' => 0 ),
            array( 'id' => $history['id'] )
        );

        // Restore current state
        $current_state = json_decode( $history['current_state'], true );
        
        if ( $current_state && isset( $current_state['generated_code'] ) ) {
            $this->update_project( $project_id, array(
                'generated_code' => $current_state['generated_code'],
            ) );
        }

        return array(
            'state'       => $current_state,
            'history_id'  => $history['id'],
            'action_type' => $history['action_type'],
        );
    }

    /**
     * Cleanup old history entries
     *
     * @param int $project_id Project ID.
     * @param int $max_states Max states to keep.
     */
    private function cleanup_history( $project_id, $max_states ) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->history_table} WHERE project_id = %d",
                \absint( $project_id )
            )
        );

        if ( $count > $max_states ) {
            $to_delete = $count - $max_states;
            $this->wpdb->query(
                $this->wpdb->prepare(
                    "DELETE FROM {$this->history_table} 
                     WHERE project_id = %d 
                     ORDER BY created_at ASC 
                     LIMIT %d",
                    \absint( $project_id ),
                    \absint( $to_delete )
                )
            );
        }
    }

    /**
     * Get templates
     *
     * @param array $filters Optional filters.
     * @return array Templates.
     */
    public function get_templates( $filters = array() ) {
        $where = 'WHERE 1=1';
        $params = array();

        if ( ! empty( $filters['category'] ) ) {
            $where .= ' AND category = %s';
            $params[] = \sanitize_text_field( $filters['category'] );
        }

        if ( ! empty( $filters['industry'] ) ) {
            $where .= ' AND industry = %s';
            $params[] = \sanitize_text_field( $filters['industry'] );
        }

        if ( isset( $filters['is_premium'] ) ) {
            $where .= ' AND is_premium = %d';
            $params[] = \absint( $filters['is_premium'] );
        }

        $sql = "SELECT * FROM {$this->templates_table} {$where} ORDER BY downloads DESC";

        // SECURITY FIX: Always use prepared statements, even when params is empty
        // This prevents SQL injection if $where is ever modified maliciously
        if ( ! empty( $params ) ) {
            $templates = $this->wpdb->get_results(
                $this->wpdb->prepare( $sql, ...$params ),
                ARRAY_A
            );
        } else {
            // Even with no params, use prepare for consistency and safety
            $templates = $this->wpdb->get_results(
                $this->wpdb->prepare( $sql ),
                ARRAY_A
            );
        }

        return $templates ?: array();
    }

    /**
     * Get single template
     *
     * @param int $template_id Template ID.
     * @return array|null Template data.
     */
    public function get_template( $template_id ) {
        $template = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->templates_table} WHERE id = %d",
                \absint( $template_id )
            ),
            \ARRAY_A
        );

        if ( $template ) {
            $template['template_data'] = json_decode( $template['template_data'], true ) ?: array();
            $template['settings'] = json_decode( $template['settings'], true ) ?: array();
        }

        return $template;
    }

    /**
     * Increment template downloads
     *
     * @param int $template_id Template ID.
     */
    public function increment_template_downloads( $template_id ) {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->templates_table} SET downloads = downloads + 1 WHERE id = %d",
                \absint( $template_id )
            )
        );
    }
}
