<?php
/**
 * Build Logger - Professional logging system for AI Site Builder Pro
 *
 * @package AISiteBuilderPro
 * @since 1.1.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Build Logger class
 * 
 * Provides comprehensive logging for all generation operations with
 * real-time updates, error tracking, and debugging capabilities.
 */
class Build_Logger {

    /**
     * Log levels
     */
    const LEVEL_DEBUG   = 'debug';
    const LEVEL_INFO    = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR   = 'error';
    const LEVEL_SUCCESS = 'success';

    /**
     * Current build session ID
     *
     * @var string
     */
    private $session_id;

    /**
     * Database instance
     *
     * @var Database
     */
    private $database;

    /**
     * Start time of current build
     *
     * @var float
     */
    private $start_time;

    /**
     * Log entries for current session
     *
     * @var array
     */
    private $entries = array();

    /**
     * Project ID for current build
     *
     * @var int
     */
    private $project_id = 0;

    /**
     * Constructor
     *
     * @param int $project_id Optional project ID.
     */
    public function __construct( $project_id = 0, $session_id = '' ) {
        $this->session_id = ! empty( $session_id ) ? $session_id : $this->generate_session_id();
        $this->start_time = microtime( true );
        $this->project_id = $project_id;
        
        // RELIABILITY: Initialize database with error handling
        try {
            $this->database = new Database();
        } catch ( \Throwable $e ) {
            error_log( 'AISBP Build_Logger: Failed to initialize Database: ' . $e->getMessage() );
            $this->database = null; // Continue without database logging
        } catch ( \Exception $e ) {
            error_log( 'AISBP Build_Logger: Failed to initialize Database: ' . $e->getMessage() );
            $this->database = null;
        }
    }

    /**
     * Generate unique session ID
     *
     * @return string
     */
    private function generate_session_id() {
        // COMPATIBILITY: Use wp_generate_password if available, otherwise use fallback
        // Note: function_exists() checks global namespace by default
        if ( function_exists( '\wp_generate_password' ) || function_exists( 'wp_generate_password' ) ) {
            $random = \wp_generate_password( 8, false );
        } else {
            // Fallback: Generate random string manually
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $random = '';
            $max = strlen( $chars ) - 1;
            for ( $i = 0; $i < 8; $i++ ) {
                $random .= $chars[ random_int( 0, $max ) ];
            }
        }
        return 'build_' . date( 'Ymd_His' ) . '_' . $random;
    }

    /**
     * Log a message
     *
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array  $context Additional context.
     * @return void
     */
    public function log( $level, $message, $context = array() ) {
        $entry = array(
            'timestamp'  => \current_time( 'mysql' ),
            'elapsed_ms' => round( ( microtime( true ) - $this->start_time ) * 1000 ),
            'level'      => $level,
            'message'    => $message,
            'context'    => $context,
        );

        $this->entries[] = $entry;

        // Store in transient for real-time access
        $this->update_live_log( $entry );

        // Log to WordPress debug log if enabled
        if ( defined( 'WP_DEBUG' ) && \WP_DEBUG ) {
            error_log( sprintf(
                '[AISBP %s] [%s] %s | Context: %s',
                strtoupper( $level ),
                $this->session_id,
                $message,
                \wp_json_encode( $context )
            ) );
        }
    }

    /**
     * Log debug message
     *
     * @param string $message Message.
     * @param array  $context Context.
     * @return void
     */
    public function debug( $message, $context = array() ) {
        $this->log( self::LEVEL_DEBUG, $message, $context );
    }

    /**
     * Log info message
     *
     * @param string $message Message.
     * @param array  $context Context.
     * @return void
     */
    public function info( $message, $context = array() ) {
        $this->log( self::LEVEL_INFO, $message, $context );
    }

    /**
     * Log warning message
     *
     * @param string $message Message.
     * @param array  $context Context.
     * @return void
     */
    public function warning( $message, $context = array() ) {
        $this->log( self::LEVEL_WARNING, $message, $context );
    }

    /**
     * Log error message
     *
     * @param string $message Message.
     * @param array  $context Context.
     * @return void
     */
    public function error( $message, $context = array() ) {
        $this->log( self::LEVEL_ERROR, $message, $context );
    }

    /**
     * Log success message
     *
     * @param string $message Message.
     * @param array  $context Context.
     * @return void
     */
    public function success( $message, $context = array() ) {
        $this->log( self::LEVEL_SUCCESS, $message, $context );
    }

    /**
     * Log phase start
     *
     * @param int    $phase_num  Phase number.
     * @param string $phase_name Phase name.
     * @return void
     */
    public function phase_start( $phase_num, $phase_name ) {
        $this->info( sprintf( 
            /* translators: 1: phase number, 2: phase name */
            \__( 'بدء المرحلة %1$d: %2$s', 'ai-site-builder-pro' ),
            $phase_num,
            $phase_name
        ), array(
            'phase'  => $phase_num,
            'action' => 'start',
        ) );
    }

    /**
     * Log phase complete
     *
     * @param int   $phase_num Phase number.
     * @param array $stats     Phase statistics.
     * @return void
     */
    public function phase_complete( $phase_num, $stats = array() ) {
        $this->success( sprintf(
            /* translators: phase number */
            \__( 'اكتملت المرحلة %d بنجاح', 'ai-site-builder-pro' ),
            $phase_num
        ), array_merge( array(
            'phase'  => $phase_num,
            'action' => 'complete',
        ), $stats ) );
    }

    /**
     * Log phase failure
     *
     * @param int    $phase_num Phase number.
     * @param string $error     Error message.
     * @return void
     */
    public function phase_failed( $phase_num, $error ) {
        $this->error( sprintf(
            /* translators: 1: phase number, 2: error message */
            \__( 'فشلت المرحلة %1$d: %2$s', 'ai-site-builder-pro' ),
            $phase_num,
            $error
        ), array(
            'phase'  => $phase_num,
            'action' => 'failed',
            'error'  => $error,
        ) );
    }

    /**
     * Log API request
     *
     * @param string $model  Model ID.
     * @param int    $tokens Token count.
     * @return void
     */
    public function api_request( $model, $tokens = 0 ) {
        $this->info( sprintf(
            /* translators: 1: model name, 2: token count */
            \__( 'إرسال طلب إلى %1$s (%2$d tokens)', 'ai-site-builder-pro' ),
            $model,
            $tokens
        ), array(
            'model'  => $model,
            'tokens' => $tokens,
            'action' => 'api_request',
        ) );
    }

    /**
     * Log API response
     *
     * @param string $model       Model ID.
     * @param int    $tokens      Token count.
     * @param float  $duration_ms Duration in milliseconds.
     * @return void
     */
    public function api_response( $model, $tokens = 0, $duration_ms = 0 ) {
        $this->success( sprintf(
            /* translators: 1: model name, 2: token count, 3: duration */
            \__( 'استلام رد من %1$s (%2$d tokens في %3$dms)', 'ai-site-builder-pro' ),
            $model,
            $tokens,
            round( $duration_ms )
        ), array(
            'model'       => $model,
            'tokens'      => $tokens,
            'duration_ms' => $duration_ms,
            'action'      => 'api_response',
        ) );
    }

    /**
     * Log API error
     *
     * @param string $model Model ID.
     * @param string $error Error message.
     * @param string $code  Error code.
     * @return void
     */
    public function api_error( $model, $error, $code = '' ) {
        $this->error( sprintf(
            /* translators: 1: model name, 2: error message */
            \__( 'فشل API من %1$s: %2$s', 'ai-site-builder-pro' ),
            $model,
            $error
        ), array(
            'model'  => $model,
            'error'  => $error,
            'code'   => $code,
            'action' => 'api_error',
        ) );
    }

    /**
     * Log validation result
     *
     * @param bool   $passed  Whether validation passed.
     * @param string $type    Validation type.
     * @param array  $details Details.
     * @return void
     */
    public function validation( $passed, $type, $details = array() ) {
        if ( $passed ) {
            $this->success( sprintf(
                /* translators: validation type */
                \__( 'نجح التحقق: %s', 'ai-site-builder-pro' ),
                $type
            ), array_merge( array(
                'type'   => $type,
                'action' => 'validation_passed',
            ), $details ) );
        } else {
            $this->error( sprintf(
                /* translators: validation type */
                \__( 'فشل التحقق: %s', 'ai-site-builder-pro' ),
                $type
            ), array_merge( array(
                'type'   => $type,
                'action' => 'validation_failed',
            ), $details ) );
        }
    }

    /**
     * Update live log in transient for real-time access
     *
     * @param array $entry Log entry.
     * @return void
     */
    private function update_live_log( $entry ) {
        $transient_key = 'aisbp_live_log_' . $this->session_id;
        $live_log = \get_transient( $transient_key );
        
        if ( ! is_array( $live_log ) ) {
            $live_log = array();
        }
        
        $live_log[] = $entry;
        
        // Keep only last 100 entries in live log
        if ( count( $live_log ) > 100 ) {
            $live_log = array_slice( $live_log, -100 );
        }
        
        \set_transient( $transient_key, $live_log, \HOUR_IN_SECONDS );
    }

    /**
     * Get live log entries
     *
     * @return array
     */
    public function get_live_log() {
        $transient_key = 'aisbp_live_log_' . $this->session_id;
        return \get_transient( $transient_key ) ?: array();
    }

    /**
     * Get session ID
     *
     * @return string
     */
    public function get_session_id() {
        return $this->session_id;
    }

    /**
     * Get all entries for current session
     *
     * @return array
     */
    public function get_entries() {
        return $this->entries;
    }

    /**
     * Get entries by level
     *
     * @param string $level Log level.
     * @return array
     */
    public function get_entries_by_level( $level ) {
        return array_filter( $this->entries, function( $entry ) use ( $level ) {
            return $entry['level'] === $level;
        } );
    }

    /**
     * Check if build has errors
     *
     * @return bool
     */
    public function has_errors() {
        return count( $this->get_entries_by_level( self::LEVEL_ERROR ) ) > 0;
    }

    /**
     * Get error count
     *
     * @return int
     */
    public function get_error_count() {
        return count( $this->get_entries_by_level( self::LEVEL_ERROR ) );
    }

    /**
     * Get total elapsed time
     *
     * @return float Time in seconds.
     */
    public function get_elapsed_time() {
        return microtime( true ) - $this->start_time;
    }

    /**
     * Finalize and save log to database
     *
     * @param string $status Final status (completed, failed, cancelled).
     * @return int|false Log ID or false.
     */
    public function finalize( $status = 'completed' ) {
        // RELIABILITY: Handle finalize with error handling to prevent 500 errors
        try {
            global $wpdb;

            $table_name = $wpdb->prefix . 'aisbp_build_logs';

            // Create table if not exists (with error handling)
            try {
                $this->maybe_create_table();
            } catch ( \Throwable $e ) {
                error_log( 'AISBP Build_Logger: Failed to create table: ' . $e->getMessage() );
                // Continue without table - logging is not critical
            }

            $summary = array(
                'total_entries' => count( $this->entries ),
                'errors'        => $this->get_error_count(),
                'warnings'      => count( $this->get_entries_by_level( self::LEVEL_WARNING ) ),
                'elapsed_sec'   => round( $this->get_elapsed_time(), 2 ),
            );

            $result = $wpdb->insert(
                $table_name,
                array(
                    'session_id'  => $this->session_id,
                    'project_id'  => $this->project_id,
                    'status'      => $status,
                    'entries'     => \wp_json_encode( $this->entries ),
                    'summary'     => \wp_json_encode( $summary ),
                    'created_at'  => \current_time( 'mysql' ),
                    'duration_ms' => round( $this->get_elapsed_time() * 1000 ),
                ),
                array( '%s', '%d', '%s', '%s', '%s', '%s', '%d' )
            );

            // Clean up transient (always, even if save fails)
            \delete_transient( 'aisbp_live_log_' . $this->session_id );

            return $result ? $wpdb->insert_id : false;
        } catch ( \Throwable $e ) {
            error_log( 'AISBP Build_Logger finalize error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            // Clean up transient even if save fails
            try {
                \delete_transient( 'aisbp_live_log_' . $this->session_id );
            } catch ( \Throwable $e2 ) {
                // Ignore transient cleanup errors
            }
            return false; // Return false instead of throwing error
        } catch ( \Exception $e ) {
            error_log( 'AISBP Build_Logger finalize exception: ' . $e->getMessage() );
            try {
                \delete_transient( 'aisbp_live_log_' . $this->session_id );
            } catch ( \Exception $e2 ) {
                // Ignore
            }
            return false;
        }
    }

    /**
     * Create logs table if not exists
     *
     * @return void
     */
    private function maybe_create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aisbp_build_logs';

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            project_id bigint(20) unsigned DEFAULT 0,
            status varchar(32) NOT NULL DEFAULT 'pending',
            entries longtext,
            summary text,
            created_at datetime NOT NULL,
            duration_ms int(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY project_id (project_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
        \dbDelta( $sql );
    }

    /**
     * Get recent build logs
     *
     * @param int $limit Number of logs to retrieve.
     * @return array
     */
    public static function get_recent_logs( $limit = 20 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aisbp_build_logs';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT id, session_id, project_id, status, summary, created_at, duration_ms 
             FROM {$table_name} 
             ORDER BY created_at DESC 
             LIMIT %d",
            $limit
        ), \ARRAY_A );
    }

    /**
     * Get log by session ID
     *
     * @param string $session_id Session ID.
     * @return array|null
     */
    public static function get_log( $session_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aisbp_build_logs';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE session_id = %s",
            $session_id
        ), \ARRAY_A );
    }

    /**
     * Delete old logs
     *
     * @param int $days Number of days to keep.
     * @return int Number of deleted rows.
     */
    public static function cleanup_old_logs( $days = 30 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aisbp_build_logs';

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );
    }
}
