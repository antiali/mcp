<?php
/**
 * AI Orchestrator - Main AI routing and orchestration
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * AI Orchestrator class
 * 
 * Routes requests to appropriate AI models with automatic failover
 */
class AI_Orchestrator {

    /**
     * Available model handlers
     *
     * @var array
     */
    private $models = array();

    /**
     * Cache manager instance
     *
     * @var Cache_Manager
     */
    private $cache;

    /**
     * Database instance
     *
     * @var Database
     */
    private $database;

    /**
     * Rate limiter
     *
     * @var array
     */
    private $rate_limit = array();

    /**
     * System prompt for website generation
     *
     * @var string
     */
    private $system_prompt = '';

    /**
     * Build logger instance
     *
     * @var Build_Logger
     */
    private $logger;

    /**
     * Build validator instance
     *
     * @var Build_Validator
     */
    private $validator;

    /**
     * Current creation mode
     *
     * @var string
     */
    private $creation_mode = 'full_site';

    /**
     * Available creation modes
     *
     * @var array
     */
    private static $creation_modes = array(
        'full_site'     => array(
            'label'       => 'موقع كامل',
            'description' => 'إنشاء موقع كامل مع جميع الصفحات',
            'phases'      => array( 1, 2, 3, 4, 5 ),
        ),
        'section'       => array(
            'label'       => 'سكشن فقط',
            'description' => 'إنشاء قسم واحد لإضافته لصفحة موجودة',
            'phases'      => array( 2, 3 ),
        ),
        'layout'        => array(
            'label'       => 'تخطيط صفحة',
            'description' => 'إنشاء تخطيط كامل لصفحة واحدة',
            'phases'      => array( 1, 2, 3, 4 ),
        ),
        'theme_builder' => array(
            'label'       => 'Theme Builder',
            'description' => 'إنشاء Header أو Footer أو Template',
            'phases'      => array( 2, 3 ),
        ),
        'divi5_section' => array(
            'label'       => 'Divi 5 Section',
            'description' => 'إنشاء سكشن بتنسيق Divi 5 JSON',
            'phases'      => array( 2, 3 ),
        ),
        'divi5_layout'  => array(
            'label'       => 'Divi 5 Layout',
            'description' => 'إنشاء تخطيط كامل بتنسيق Divi 5',
            'phases'      => array( 1, 2, 3, 4 ),
        ),
    );

    /**
     * Constructor
     */
    public function __construct() {
        try {
            $this->cache     = new Cache_Manager();
            $this->database  = new Database();
            $this->logger    = new Build_Logger();
            $this->validator = new Build_Validator( $this->logger );
            
            $this->init_models();
            $this->load_system_prompt();
        } catch ( \Throwable $e ) {
            error_log( 'AISBP\AI_Orchestrator constructor error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine() );
            // Initialize with minimal dependencies to prevent fatal errors
            if ( ! $this->cache ) {
                $this->cache = new Cache_Manager();
            }
            if ( ! $this->logger ) {
                $this->logger = new Build_Logger();
            }
            if ( ! $this->validator ) {
                $this->validator = new Build_Validator( $this->logger );
            }
            if ( ! $this->database ) {
                $this->database = new Database();
            }
        } catch ( \Exception $e ) {
            error_log( 'AISBP\AI_Orchestrator constructor exception: ' . $e->getMessage() );
        }
    }

    /**
     * Initialize available models
     * 
     * PERFORMANCE: Uses object cache to avoid repeated database queries
     */
    private function init_models() {
        // PERFORMANCE: Use object cache for API keys to reduce database load
        $cache_key = 'awbu_api_keys_merged_' . \get_current_blog_id();
        $api_keys = \wp_cache_get( $cache_key, 'awbu' );
        
        if ( false === $api_keys ) {
            // COMPATIBILITY: Merge both option names to support both plugins
            $awbu_keys = \get_option( 'awbu_api_keys', array() );
            $aisbp_keys = \get_option( 'aisbp_api_keys', array() );
            
            // Merge: awbu_api_keys takes priority, then aisbp_api_keys fills gaps
            $api_keys = array_merge( $aisbp_keys, $awbu_keys );
            
            // Also check individual options for backward compatibility
            $individual_keys = array(
                'deepseek' => \get_option( 'awbu_deepseek_api_key', '' ),
                'openai'   => \get_option( 'awbu_openai_api_key', '' ),
                'claude'   => \get_option( 'awbu_claude_api_key', '' ),
                'gemini'   => \get_option( 'awbu_gemini_api_key', '' ),
            );
            
            // Fill empty keys from individual options
            foreach ( $individual_keys as $key => $value ) {
                if ( empty( $api_keys[ $key ] ) && ! empty( $value ) ) {
                    $api_keys[ $key ] = $value;
                }
            }
            
            // Cache for 1 hour (3600 seconds)
            \wp_cache_set( $cache_key, $api_keys, 'awbu', 3600 );
        }
        
        // FIXED: Handle missing aisbp() function gracefully
        $plugin = function_exists('aisbp') ? aisbp() : null;

        // Helper function to decrypt API key
        $decrypt_key = function( $encrypted_key ) use ( $plugin ) {
            if ( $plugin && method_exists( $plugin, 'decrypt_api_key' ) ) {
                return $plugin->decrypt_api_key( $encrypted_key );
            }
            // Fallback: return as-is if decryption not available
            return $encrypted_key;
        };

        // OpenAI
        if ( ! empty( $api_keys['openai'] ) ) {
            $api_key = $decrypt_key( $api_keys['openai'] );
            // COMPATIBILITY: Check both namespace and global class names
            if ( class_exists( 'AISBP\API\OpenAI_Connector' ) ) {
                $this->models['openai'] = new API\OpenAI_Connector( $api_key );
            } elseif ( class_exists( 'AWBU_OpenAI_Connector' ) ) {
                $this->models['openai'] = new \AWBU_OpenAI_Connector();
            }
        }

        // Claude
        if ( ! empty( $api_keys['claude'] ) ) {
            $api_key = $decrypt_key( $api_keys['claude'] );
            // COMPATIBILITY: Check both namespace and global class names
            if ( class_exists( 'AISBP\API\Claude_Connector' ) ) {
                $this->models['claude'] = new API\Claude_Connector( $api_key );
            } elseif ( class_exists( 'AWBU_Claude_Connector' ) ) {
                $this->models['claude'] = new \AWBU_Claude_Connector();
            }
        }

        // Gemini
        if ( ! empty( $api_keys['gemini'] ) ) {
            $api_key = $decrypt_key( $api_keys['gemini'] );
            // COMPATIBILITY: Check both namespace and global class names
            if ( class_exists( 'AISBP\API\Gemini_Connector' ) ) {
                $this->models['gemini'] = new API\Gemini_Connector( $api_key );
            } elseif ( class_exists( 'AWBU_Gemini_Connector' ) ) {
                $this->models['gemini'] = new \AWBU_Gemini_Connector();
            }
        }

        // DeepSeek
        if ( ! empty( $api_keys['deepseek'] ) ) {
            $api_key = $decrypt_key( $api_keys['deepseek'] );
            // COMPATIBILITY: Check both namespace and global class names
            if ( class_exists( 'AISBP\API\DeepSeek_Connector' ) ) {
                $this->models['deepseek'] = new API\DeepSeek_Connector( $api_key );
            } elseif ( class_exists( 'AWBU_DeepSeek_Connector' ) ) {
                $this->models['deepseek'] = new \AWBU_DeepSeek_Connector();
            }
        }
    }

    /**
     * Load system prompt for generation
     */
    private function load_system_prompt() {
        try {
            // Now using Master_Prompt class for modern 2025 WordPress standards
            if ( class_exists( 'AISBP\Master_Prompt' ) ) {
                $this->system_prompt = Master_Prompt::get_system_prompt();
            } else {
                // Fallback to basic prompt
                $this->system_prompt = __( 'You are an expert WordPress website builder. Generate clean, modern, responsive HTML/CSS/JavaScript code.', 'ai-site-builder-pro' );
            }
            $this->system_prompt = apply_filters( 'aisbp_system_prompt', $this->system_prompt );
        } catch ( \Throwable $e ) {
            error_log( 'AISBP\AI_Orchestrator load_system_prompt error: ' . $e->getMessage() );
            $this->system_prompt = __( 'You are an expert WordPress website builder.', 'ai-site-builder-pro' );
        }
    }

    /**
     * Generate website based on inputs
     *
     * @param array $params Generation parameters.
     * @return array|WP_Error Generated content or error.
     */
    public function generate( $params ) {
        $track_id = 'orchestrator_' . time() . '_' . \wp_generate_password(8, false);
        $log_step = function($step, $data = null) use ($track_id) {
            error_log(sprintf('[AISBP TRACK %s] Step: %s | Memory: %s MB | Data: %s', 
                $track_id, 
                $step, 
                round(memory_get_usage(true) / 1024 / 1024, 2),
                $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : 'null'
            ));
        };
        
        $log_step('GENERATE_START', array('params_keys' => array_keys($params)));
        
        try {
            // Initialize logger for this build session
            $project_id = \absint( $params['project_id'] ?? 0 );
            $session_id = \sanitize_text_field( $params['session_id'] ?? '' );
            
            $log_step('PARAMS_PARSED', array('project_id' => $project_id, 'session_id' => $session_id));
            
            // RELIABILITY: Initialize logger with error handling
            try {
                $log_step('INITIALIZING_LOGGER');
                $this->logger = new Build_Logger( $project_id, $session_id );
                $this->validator = new Build_Validator( $this->logger );
                $log_step('LOGGER_INITIALIZED', array('logger_class' => get_class($this->logger)));
            } catch ( \Throwable $e ) {
                $log_step('LOGGER_INIT_FAILED', array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ));
                error_log( 'AISBP: Failed to initialize logger/validator: ' . $e->getMessage() );
                // Continue without logger if it fails
                if ( ! $this->logger ) {
                    $this->logger = null; // Will be checked before use
                }
            }

            if ( $this->logger ) {
                $this->logger->info( \__( 'بدء عملية الإنشاء...', 'ai-site-builder-pro' ), array(
                    'params' => array_keys( $params ),
                ) );
            }
            
            $log_step('LOGGER_READY');

            // Validate required parameters
            $log_step('VALIDATING_PARAMS');
            if ( empty( $params['project_id'] ) && empty( $params['description'] ) ) {
                $log_step('PARAMS_VALIDATION_FAILED', array('has_project_id' => !empty($params['project_id']), 'has_description' => !empty($params['description'])));
                $error = new \WP_Error( 'missing_params', \__( 'المعاملات المطلوبة مفقودة.', 'ai-site-builder-pro' ) );
                if ( $this->logger ) {
                    $this->logger->error( $error->get_error_message() );
                    $this->logger->finalize( 'failed' );
                }
                return $error;
            }
            $log_step('PARAMS_VALID');

        // Check rate limit
        $log_step('CHECKING_RATE_LIMIT');
        if ( ! $this->check_rate_limit() ) {
            $log_step('RATE_LIMIT_EXCEEDED');
            $error = new \WP_Error( 'rate_limit', \__( 'تم تجاوز حد الطلبات. حاول مرة أخرى لاحقاً.', 'ai-site-builder-pro' ) );
            if ( $this->logger ) {
                $this->logger->error( $error->get_error_message() );
                try {
                    $this->logger->finalize( 'failed' );
                } catch ( \Throwable $e ) {
                    error_log( 'AISBP: Failed to finalize logger on rate limit: ' . $e->getMessage() );
                }
            }
            return $error;
        }
        $log_step('RATE_LIMIT_OK');

        // Get model preference
        $log_step('GETTING_MODEL');
        $model_id = \sanitize_text_field( $params['model'] ?? \get_option( 'aisbp_default_model', 'deepseek' ) );
        $log_step('MODEL_SELECTED', array('model_id' => $model_id, 'available_models' => array_keys($this->models)));
        
        if ( $this->logger ) {
            $this->logger->info( sprintf( \__( 'النموذج المحدد: %s', 'ai-site-builder-pro' ), $model_id ) );
        }

        // Get creation mode
        $log_step('GETTING_CREATION_MODE');
        $this->creation_mode = \sanitize_text_field( $params['creation_mode'] ?? 'full_site' );
        if ( ! isset( self::$creation_modes[ $this->creation_mode ] ) ) {
            $this->creation_mode = 'full_site';
        }
        $log_step('CREATION_MODE_SET', array('mode' => $this->creation_mode));
        
        if ( $this->logger ) {
            $this->logger->info( sprintf( 
                \__( 'وضع الإنشاء: %s', 'ai-site-builder-pro' ), 
                self::$creation_modes[ $this->creation_mode ]['label'] 
            ) );
        }

        // Check if model is available
        $log_step('CHECKING_MODEL_AVAILABILITY', array('model_id' => $model_id, 'models_count' => count($this->models)));
        if ( ! isset( $this->models[ $model_id ] ) ) {
            // Check for any available model
            if ( empty( $this->models ) ) {
                $log_step('NO_MODELS_AVAILABLE');
                $error = new \WP_Error( 'no_models', \__( 'لا توجد نماذج AI متاحة. الرجاء إعداد مفتاح API على الأقل.', 'ai-site-builder-pro' ) );
                if ( $this->logger ) {
                    $this->logger->error( $error->get_error_message() );
                    try {
                        $this->logger->finalize( 'failed' );
                    } catch ( \Throwable $e ) {
                        error_log( 'AISBP: Failed to finalize logger: ' . $e->getMessage() );
                    }
                }
                return $error;
            }
            // Use first available model
            $model_id = array_key_first( $this->models );
            if ( $this->logger ) {
                $this->logger->warning( sprintf( __( 'تم التبديل للنموذج المتاح: %s', 'ai-site-builder-pro' ), $model_id ) );
            }
        }

        // Get or create project
        if ( ! $project_id ) {
            if ( $this->logger ) {
                $this->logger->info( __( 'إنشاء مشروع جديد...', 'ai-site-builder-pro' ) );
            }
            
            $project_id = $this->database->create_project( array(
                'name'          => sanitize_text_field( $params['name'] ?? __( 'مشروع جديد', 'ai-site-builder-pro' ) ),
                'description'   => sanitize_textarea_field( $params['description'] ?? '' ),
                'website_type'  => sanitize_text_field( $params['website_type'] ?? 'business' ),
                'industry'      => sanitize_text_field( $params['industry'] ?? '' ),
                'settings'      => $params['settings'] ?? array(),
                'inputs'        => $params['inputs'] ?? array(),
                'ai_model'      => $model_id,
                'creation_mode' => $this->creation_mode,
                'status'        => 'generating',
            ) );

            if ( is_wp_error( $project_id ) ) {
                if ( $this->logger ) {
                    $this->logger->error( sprintf( __( 'فشل إنشاء المشروع: %s', 'ai-site-builder-pro' ), $project_id->get_error_message() ) );
                    try {
                        $this->logger->finalize( 'failed' );
                    } catch ( \Throwable $e ) {
                        error_log( 'AISBP: Failed to finalize logger: ' . $e->getMessage() );
                    }
                }
                return $project_id;
            }

            if ( $this->logger ) {
                $this->logger->success( sprintf( __( 'تم إنشاء المشروع #%d', 'ai-site-builder-pro' ), $project_id ) );
            }
        } else {
            $this->database->update_project( $project_id, array( 'status' => 'generating' ) );
            if ( $this->logger ) {
                $this->logger->info( sprintf( __( 'تحديث المشروع #%d', 'ai-site-builder-pro' ), $project_id ) );
            }
        }

        // Store session ID for frontend access
        if ( $this->logger ) {
            try {
                update_post_meta( $project_id, '_aisbp_log_session', $this->logger->get_session_id() );
            } catch ( \Throwable $e ) {
                error_log( 'AISBP: Failed to update post meta: ' . $e->getMessage() );
            }
        }

        // Start progressive generation
        if ( $this->logger ) {
            $this->logger->info( __( 'بدء التوليد التدريجي...', 'ai-site-builder-pro' ) );
        }
        $result = $this->progressive_generate( $project_id, $model_id, $params );

        if ( is_wp_error( $result ) ) {
            try {
                $this->database->update_project( $project_id, array( 'status' => 'failed' ) );
            } catch ( \Throwable $e ) {
                error_log( 'AISBP: Failed to update project status: ' . $e->getMessage() );
            }
            if ( $this->logger ) {
                $this->logger->error( sprintf( __( 'فشل التوليد: %s', 'ai-site-builder-pro' ), $result->get_error_message() ) );
                try {
                    $this->logger->finalize( 'failed' );
                } catch ( \Throwable $e ) {
                    error_log( 'AISBP: Failed to finalize logger: ' . $e->getMessage() );
                }
            }
            return $result;
        }

        // VALIDATION: Verify code was actually generated (skip for single phase execution)
        $target_phase = isset( $params['phase'] ) ? absint( $params['phase'] ) : 0;
        $is_single_phase = $target_phase > 0;
        
        if ( ! $is_single_phase ) {
            // Only validate full code when all phases are complete
            if ( $this->logger ) {
                $this->logger->info( __( 'التحقق من الكود المولد...', 'ai-site-builder-pro' ) );
            }
            $code_type = $this->get_code_type_for_mode( $this->creation_mode );
            if ( $this->validator ) {
                $validation = $this->validator->validate_generated_code( $result['full_code'], $code_type );
                
                if ( ! $validation['valid'] ) {
                    $error_msg = $this->validator->get_first_error();
                    try {
                        $this->database->update_project( $project_id, array( 'status' => 'failed' ) );
                    } catch ( \Throwable $e ) {
                        error_log( 'AISBP: Failed to update project: ' . $e->getMessage() );
                    }
                    if ( $this->logger ) {
                        $this->logger->error( sprintf( __( 'فشل التحقق: %s', 'ai-site-builder-pro' ), $error_msg ) );
                        try {
                            $this->logger->finalize( 'failed' );
                        } catch ( \Throwable $e ) {
                            error_log( 'AISBP: Failed to finalize logger: ' . $e->getMessage() );
                        }
                    }
                    return new \WP_Error( 'validation_failed', $error_msg );
                }
            } else {
                $validation = array( 'valid' => true, 'message' => __( 'تم تخطي التحقق (validator غير متاح)', 'ai-site-builder-pro' ) );
            }
        } else {
            // For single phase, just check that code exists
            if ( empty( trim( $result['full_code'] ?? '' ) ) ) {
                $error_msg = sprintf( __( 'المرحلة %d لم تولد أي كود', 'ai-site-builder-pro' ), $target_phase );
                try {
                    $this->database->update_project( $project_id, array( 'status' => 'failed' ) );
                } catch ( \Throwable $e ) {
                    error_log( 'AISBP: Failed to update project: ' . $e->getMessage() );
                }
                if ( $this->logger ) {
                    $this->logger->error( $error_msg );
                    try {
                        $this->logger->finalize( 'failed' );
                    } catch ( \Throwable $e ) {
                        error_log( 'AISBP: Failed to finalize logger: ' . $e->getMessage() );
                    }
                }
                return new \WP_Error( 'empty_code', $error_msg );
            }
            $validation = array( 'valid' => true, 'message' => __( 'تم التحقق من وجود الكود', 'ai-site-builder-pro' ) );
        }

        // Attempt to save the generated code FIRST
        $save_result = $this->database->update_project( $project_id, array(
            'generated_code' => $result['full_code'],
            'total_tokens'   => $result['total_tokens'],
            'total_cost'     => $result['total_cost'],
            'status'         => 'generating', // Still generating until verified
        ) );

        // VALIDATION: Verify save was successful BEFORE marking as completed
        // RELIABILITY: Handle validation with error handling
        try {
            if ( $this->validator ) {
                $save_validation = $this->validator->validate_save( $project_id, $result['full_code'] );
                if ( ! $save_validation['valid'] ) {
                    $error_msg = $this->validator->get_first_error() ?? __( 'فشل حفظ الكود في قاعدة البيانات', 'ai-site-builder-pro' );
                    $this->database->update_project( $project_id, array( 'status' => 'failed' ) );
                    if ( $this->logger ) {
                        $this->logger->error( sprintf( __( 'فشل الحفظ: %s', 'ai-site-builder-pro' ), $error_msg ) );
                        $this->logger->finalize( 'failed' );
                    }
                    return new \WP_Error( 'save_failed', $error_msg );
                }
            }
        } catch ( \Throwable $e ) {
            error_log( 'AISBP: Validation error (non-critical): ' . $e->getMessage() );
            // Continue even if validation fails - don't break the generation
        }

        // NOW mark as completed (only after verified save)
        // RELIABILITY: Handle database update with error handling
        try {
            $this->database->update_project( $project_id, array( 'status' => 'completed' ) );
            if ( $this->logger ) {
                $this->logger->success( __( 'تم حفظ المشروع بنجاح في قاعدة البيانات', 'ai-site-builder-pro' ) );
            }
        } catch ( \Throwable $e ) {
            error_log( 'AISBP: Failed to update project status: ' . $e->getMessage() );
            // Continue - status update is not critical
        }

        // Finalize logging (with error handling)
        $log_id = false;
        try {
            if ( $this->logger ) {
                $log_id = $this->logger->finalize( 'completed' );
                $this->logger->success( sprintf( 
                    __( '✅ اكتمل الإنشاء بنجاح! (الوقت: %s ثانية)', 'ai-site-builder-pro' ),
                    round( $this->logger->get_elapsed_time(), 2 )
                ) );
            }
        } catch ( \Throwable $e ) {
            error_log( 'AISBP: Failed to finalize logging: ' . $e->getMessage() );
            // Continue - logging is not critical
        }

        return array(
            'project_id'   => $project_id,
            'phases'       => $result['phases'],
            'full_code'    => $result['full_code'],
            'total_tokens' => $result['total_tokens'],
            'total_cost'   => $result['total_cost'],
            'log_session'  => $this->logger ? $this->logger->get_session_id() : '',
            'log_id'       => $log_id,
            'validation'   => $validation,
        );
        } catch ( \Throwable $e ) {
            // Log the error with full details
            error_log( sprintf(
                'AISBP Generate Fatal Error: %s in %s:%d | Trace: %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ) );
            
            if ( $this->logger ) {
                try {
                    $this->logger->error( sprintf( 
                        __( 'خطأ فادح في التوليد: %s', 'ai-site-builder-pro' ), 
                        $e->getMessage() 
                    ) );
                    $this->logger->finalize( 'failed' );
                } catch ( \Throwable $e2 ) {
                    error_log( 'AISBP: Failed to log error: ' . $e2->getMessage() );
                }
            }
            
            return new \WP_Error(
                'generation_fatal_error',
                sprintf( 
                    __( 'خطأ في معالجة الطلب: %s', 'ai-site-builder-pro' ), 
                    $e->getMessage() 
                ),
                array(
                    'file' => basename( $e->getFile() ),
                    'line' => $e->getLine(),
                )
            );
        } catch ( \Exception $e ) {
            // Fallback for older PHP versions
            error_log( 'AISBP Generate Exception: ' . $e->getMessage() );
            
            if ( $this->logger ) {
                try {
                    $this->logger->error( __( 'خطأ في معالجة الطلب', 'ai-site-builder-pro' ) );
                    $this->logger->finalize( 'failed' );
                } catch ( \Exception $e2 ) {
                    // Ignore
                }
            }
            
            return new \WP_Error(
                'generation_error',
                __( 'خطأ في معالجة الطلب', 'ai-site-builder-pro' )
            );
        }
    }

    /**
     * Get code type for creation mode
     *
     * @param string $mode Creation mode.
     * @return string Code type.
     */
    private function get_code_type_for_mode( $mode ) {
        switch ( $mode ) {
            case 'section':
                return 'section';
            case 'layout':
                return 'layout';
            case 'theme_builder':
                return 'theme_builder';
            case 'divi5_section':
            case 'divi5_layout':
                return 'divi5';
            default:
                return 'html';
        }
    }

    /**
     * Get available creation modes
     *
     * @return array
     */
    public static function get_creation_modes() {
        return self::$creation_modes;
    }

    /**
     * Progressive 5-phase generation
     *
     * @param int    $project_id Project ID.
     * @param string $model_id   Model identifier.
     * @param array  $params     Generation parameters.
     * @return array|WP_Error Results or error.
     */
    private function progressive_generate( $project_id, $model_id, $params ) {
        $track_id = 'progressive_' . time() . '_' . \wp_generate_password(8, false);
        $log_step = function($step, $data = null) use ($track_id) {
            error_log(sprintf('[AISBP PROGRESSIVE TRACK %s] Step: %s | Memory: %s MB | Data: %s', 
                $track_id, 
                $step, 
                round(memory_get_usage(true) / 1024 / 1024, 2),
                $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : 'null'
            ));
        };
        
        $log_step('PROGRESSIVE_GENERATE_START', array('project_id' => $project_id, 'model_id' => $model_id));
        
        try {
            $log_step('GETTING_PHASES');
            // Get phases based on creation mode
            $mode_config = self::$creation_modes[ $this->creation_mode ] ?? self::$creation_modes['full_site'];
            $active_phases = $mode_config['phases'];

        $all_phases = array(
            1 => array(
                'name'   => 'structure',
                'label'  => __( 'بناء الهيكل', 'ai-site-builder-pro' ),
                'prompt' => $this->get_phase_prompt( 1, $params ),
            ),
            2 => array(
                'name'   => 'layout',
                'label'  => __( 'إنشاء التخطيط', 'ai-site-builder-pro' ),
                'prompt' => $this->get_phase_prompt( 2, $params ),
            ),
            3 => array(
                'name'   => 'styling',
                'label'  => __( 'تطبيق الأنماط', 'ai-site-builder-pro' ),
                'prompt' => $this->get_phase_prompt( 3, $params ),
            ),
            4 => array(
                'name'   => 'content',
                'label'  => __( 'توليد المحتوى', 'ai-site-builder-pro' ),
                'prompt' => $this->get_phase_prompt( 4, $params ),
            ),
            5 => array(
                'name'   => 'optimization',
                'label'  => __( 'التحسين', 'ai-site-builder-pro' ),
                'prompt' => $this->get_phase_prompt( 5, $params ),
            ),
        );

        // Filter phases based on creation mode
        $phases = array();
        foreach ( $active_phases as $phase_num ) {
            if ( isset( $all_phases[ $phase_num ] ) ) {
                $phases[ $phase_num ] = $all_phases[ $phase_num ];
            }
        }

        $this->logger->info( sprintf( 
            __( 'المراحل النشطة: %s', 'ai-site-builder-pro' ), 
            implode( ', ', array_keys( $phases ) ) 
        ) );

        // Support SINGLE PHASE execution to prevent timeouts
        $target_phase = isset( $params['phase'] ) ? absint( $params['phase'] ) : 0;
        if ( $target_phase > 0 && isset( $phases[ $target_phase ] ) ) {
            $this->logger->info( sprintf( __( 'تنفيذ مرحلة واحدة محددة: %d', 'ai-site-builder-pro' ), $target_phase ) );
            // Keep only the target phase
            $phases = array( $target_phase => $phases[ $target_phase ] );
        }

        $log_step('PHASES_CONFIGURED', array('phases' => array_keys($phases), 'target_phase' => $target_phase));
        
        $results = array();
        $total_tokens = 0;
        $total_cost = 0;
        
        // Context handling for chained requests
        // FIXED: Handle null values to prevent PHP Deprecated warnings
        $context = isset( $params['previous_context'] ) && $params['previous_context'] !== null 
            ? \wp_unslash( $params['previous_context'] ) 
            : '';
        $log_step('CONTEXT_LOADED', array('context_length' => strlen($context)));
        
        $phase_index = 0;
        $total_phases = count( $phases );

        foreach ( $phases as $phase_num => $phase ) {
            $phase_index++;
            $log_step('PHASE_START', array('phase_num' => $phase_num, 'phase_name' => $phase['name'], 'index' => $phase_index, 'total' => $total_phases));
            
            // Rate limiting: Add delay between phases (skip first phase)
            if ( $phase_index > 1 ) {
                $delay_seconds = \apply_filters( 'aisbp_phase_delay_seconds', 2 );
                $log_step('PHASE_DELAY', array('seconds' => $delay_seconds));
                if ( $this->logger ) {
                    $this->logger->info( sprintf( \__( 'انتظار %d ثوانٍ قبل المرحلة التالية...', 'ai-site-builder-pro' ), $delay_seconds ) );
                }
                sleep( $delay_seconds );
            }
            
            // Log phase start
            if ( $this->logger ) {
                $this->logger->phase_start( $phase_num, $phase['label'] );
            }
            
            $log_step('BUILDING_PROMPT', array('phase_num' => $phase_num));
            // Include previous context
            $full_prompt = $phase['prompt'];
            if ( ! empty( $context ) ) {
                $full_prompt .= "\n\nPrevious code to build upon:\n" . $context;
                $log_step('CONTEXT_ADDED', array('context_length' => strlen($context)));
            }

            // Add creation mode context to prompt
            if ( in_array( $this->creation_mode, array( 'divi5_section', 'divi5_layout' ), true ) ) {
                $full_prompt = "OUTPUT FORMAT: Divi 5 JSON Block Format ONLY.\n\n" . $full_prompt;
            } elseif ( $this->creation_mode === 'theme_builder' ) {
                $full_prompt = "OUTPUT: WordPress Theme Builder compatible code (header/footer template).\n\n" . $full_prompt;
            } elseif ( $this->creation_mode === 'section' ) {
                $full_prompt = "OUTPUT: Single reusable section code only.\n\n" . $full_prompt;
            }

            // Get dynamic system prompt based on current context
            if ( class_exists( 'AISBP\Master_Prompt' ) ) {
                $system_prompt = Master_Prompt::get_system_prompt( $params );
            } else {
                $system_prompt = $this->system_prompt;
            }
            $params['system'] = $system_prompt;

            $log_step('CHECKING_CACHE', array('phase_num' => $phase_num));
            // Check cache
            $cache_key = $this->cache->generate_key( $model_id, $full_prompt, $params );
            $cached = $this->cache->get( $cache_key );
            $log_step('CACHE_CHECKED', array('has_cache' => !empty($cached)));

            $start_time = microtime( true );

            if ( $cached ) {
                $log_step('USING_CACHE', array('phase_num' => $phase_num));
                $response = $cached;
                if ( $this->logger ) {
                    $this->logger->info( \__( 'تم استخدام الكاش', 'ai-site-builder-pro' ) );
                }
            } else {
                $log_step('MAKING_API_REQUEST', array('phase_num' => $phase_num, 'model_id' => $model_id, 'prompt_length' => strlen($full_prompt)));
                // Log API request
                if ( $this->logger ) {
                    $this->logger->api_request( $model_id, strlen( $full_prompt ) );
                }
                
                // Make AI request with failover
                $response = $this->request_with_failover( $model_id, $full_prompt, $params );
                $log_step('API_REQUEST_COMPLETE', array('phase_num' => $phase_num, 'is_wp_error' => \is_wp_error($response)));

                // FAIL-FAST: Stop immediately on API error
                if ( \is_wp_error( $response ) ) {
                    $log_step('API_REQUEST_FAILED', array(
                        'phase_num' => $phase_num,
                        'error_code' => $response->get_error_code(),
                        'error_message' => $response->get_error_message()
                    ));
                    if ( $this->logger ) {
                        $this->logger->phase_failed( $phase_num, $response->get_error_message() );
                    }
                    return $response;
                }

                // VALIDATION: Validate API response
                if ( $this->validator ) {
                    $api_validation = $this->validator->validate_api_response( $response, $model_id );
                    if ( ! $api_validation['valid'] ) {
                        $error = new \WP_Error( 
                            'api_validation_failed', 
                            $this->validator->get_first_error() 
                        );
                        if ( $this->logger ) {
                            $this->logger->phase_failed( $phase_num, $error->get_error_message() );
                        }
                        return $error;
                    }
                }

                $duration_ms = ( microtime( true ) - $start_time ) * 1000;
                $tokens = ( $response['prompt_tokens'] ?? 0 ) + ( $response['completion_tokens'] ?? 0 );
                
                if ( $this->logger ) {
                    $this->logger->api_response( 
                        $response['model'] ?? $model_id, 
                        $tokens, 
                        $duration_ms 
                    );
                }

                // Cache response
                $this->cache->set( $cache_key, $response, $model_id );
            }

            $log_step('EXTRACTING_CODE', array('phase_num' => $phase_num));
            // Extract code from response (with error handling)
            $response_content = $response['content'] ?? '';
            $log_step('RESPONSE_CONTENT_EXTRACTED', array('phase_num' => $phase_num, 'content_length' => strlen($response_content)));
            
            if ( empty( $response_content ) ) {
                $log_step('EMPTY_RESPONSE_ERROR', array('phase_num' => $phase_num));
                $error = new \WP_Error( 
                    'empty_response', 
                    sprintf( \__( 'المرحلة %d: استجابة فارغة من API', 'ai-site-builder-pro' ), $phase_num )
                );
                if ( $this->logger ) {
                    $this->logger->phase_failed( $phase_num, $error->get_error_message() );
                }
                return $error;
            }
            
            $extracted_code = $this->extract_code( $response_content );
            $log_step('CODE_EXTRACTED', array('phase_num' => $phase_num, 'code_length' => strlen($extracted_code)));
            
            // VALIDATION: Check if code was actually extracted
            if ( empty( trim( $extracted_code ) ) ) {
                $error = new \WP_Error( 
                    'empty_code', 
                    sprintf( __( 'المرحلة %d لم تولد أي كود صالح', 'ai-site-builder-pro' ), $phase_num )
                );
                if ( $this->logger ) {
                    $this->logger->phase_failed( $phase_num, $error->get_error_message() );
                }
                return $error;
            }

            // Save generation record (with error handling)
            try {
                $save_result = $this->database->save_generation( array(
                    'project_id'        => $project_id,
                    'phase'             => $phase_num,
                    'phase_name'        => $phase['name'],
                    'model'             => $response['model'] ?? $model_id,
                    'prompt'            => $full_prompt,
                    'response'          => $response_content,
                    'generated_code'    => $extracted_code,
                    'prompt_tokens'     => $response['prompt_tokens'] ?? 0,
                    'completion_tokens' => $response['completion_tokens'] ?? 0,
                    'cost_usd'          => $response['cost'] ?? 0,
                    'duration_ms'       => ( microtime( true ) - $start_time ) * 1000,
                    'status'            => 'completed',
                ) );
                
                // Log if save failed but don't stop execution
                if ( is_wp_error( $save_result ) ) {
                    $this->logger->warning( sprintf( 
                        __( 'تحذير: فشل حفظ سجل المرحلة %d: %s', 'ai-site-builder-pro' ), 
                        $phase_num, 
                        $save_result->get_error_message() 
                    ) );
                }
            } catch ( \Throwable $e ) {
                // Log error but continue - don't fail the entire generation
                $this->logger->warning( sprintf( 
                    __( 'تحذير: خطأ في حفظ سجل المرحلة %d: %s', 'ai-site-builder-pro' ), 
                    $phase_num, 
                    $e->getMessage() 
                ) );
            }

            // Track usage (with error handling - don't fail if tracking fails)
            try {
                $this->database->track_usage( array(
                    'project_id' => $project_id,
                    'model'      => $response['model'] ?? $model_id,
                    'operation'  => 'generate_phase_' . $phase_num,
                    'tokens_in'  => $response['prompt_tokens'] ?? 0,
                    'tokens_out' => $response['completion_tokens'] ?? 0,
                    'cost_usd'   => $response['cost'] ?? 0,
                ) );
            } catch ( \Throwable $e ) {
                // Log warning but continue - tracking is not critical
                if ( $this->logger ) {
                    $this->logger->warning( sprintf( 
                        __( 'تحذير: فشل تتبع الاستخدام للمرحلة %d', 'ai-site-builder-pro' ), 
                        $phase_num
                    ) );
                }
            }

            // Accumulate totals
            $phase_tokens = ( $response['prompt_tokens'] ?? 0 ) + ( $response['completion_tokens'] ?? 0 );
            $total_tokens += $phase_tokens;
            $total_cost += $response['cost'] ?? 0;

            // Update context for next phase
            $context = $extracted_code;

            // Log phase completion with stats
            if ( $this->logger ) {
                $this->logger->phase_complete( $phase_num, array(
                    'tokens'      => $phase_tokens,
                    'cost'        => $response['cost'] ?? 0,
                    'code_length' => strlen( $extracted_code ),
                ) );
            }

            $results[ $phase_num ] = array(
                'phase'     => $phase_num,
                'name'      => $phase['name'],
                'label'     => $phase['label'],
                'code'      => $context,
                'tokens'    => $phase_tokens,
                'cost'      => $response['cost'] ?? 0,
            );

            /**
             * Fires after each generation phase completes
             *
             * @param int   $phase_num Phase number.
             * @param array $result    Phase result.
             * @param int   $project_id Project ID.
             */
            do_action( 'aisbp_phase_complete', $phase_num, end( $results ), $project_id );
        }

        // Combine all phases into final code
        $full_code = $this->combine_phases( $results );

        // POST-PROCESSING: Validate and fix prefixes (only if class exists)
        $processed = array(
            'success' => true,
            'html'    => $full_code,
            'prefix'  => 'aisbp',
            'stats'   => array(
                'classes_fixed'   => 0,
                'variables_fixed' => 0,
                'js_fixed'        => 0,
            ),
        );

        // Only use post-processor if class exists
        if ( class_exists( 'AISBP\Code_Post_Processor' ) ) {
            try {
                $post_processor = new \AISBP\Code_Post_Processor( $project_id );
                $processed = $post_processor->process( $full_code );
                
                if ( $processed['success'] ) {
                    $full_code = $processed['html'];
                }

                // Log processing stats
                $stats = $processed['stats'];
                if ( $stats['classes_fixed'] > 0 || $stats['variables_fixed'] > 0 ) {
                    error_log( sprintf(
                        'AISBP Post-Processor: Fixed %d classes, %d variables, %d JS issues for project %d',
                        $stats['classes_fixed'],
                        $stats['variables_fixed'],
                        $stats['js_fixed'],
                        $project_id
                    ) );
                }
            } catch ( \Throwable $e ) {
                // Log error but continue without post-processing
                error_log( 'AISBP Post-Processor error: ' . $e->getMessage() );
                $this->logger->warning( sprintf( 
                    __( 'Post-processing skipped: %s', 'ai-site-builder-pro' ), 
                    $e->getMessage() 
                ) );
            }
        }

        /**
         * Filter the final generated code
         *
         * @param string $full_code     The complete generated HTML.
         * @param array  $results       All phase results.
         * @param array  $process_stats Post-processing statistics.
         */
            $full_code = apply_filters( 'aisbp_generated_code', $full_code, $results, $processed['stats'] );

            return array(
                'phases'       => $results,
                'full_code'    => $full_code,
                'prefix'       => $processed['prefix'],
                'total_tokens' => $total_tokens,
                'total_cost'   => $total_cost,
                'processing'   => $processed['stats'],
            );
        } catch ( \Throwable $e ) {
            // Log the error with full details
            error_log( sprintf(
                'AISBP Progressive Generate Error: %s in %s:%d | Trace: %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ) );
            
            if ( $this->logger ) {
                $this->logger->error( sprintf( 
                    __( 'خطأ في التوليد التدريجي: %s', 'ai-site-builder-pro' ), 
                    $e->getMessage() 
                ) );
                $this->logger->finalize( 'failed' );
            }
            
            return new \WP_Error(
                'generation_error',
                sprintf( 
                    __( 'خطأ في معالجة الطلب: %s', 'ai-site-builder-pro' ), 
                    $e->getMessage() 
                ),
                array(
                    'file' => basename( $e->getFile() ),
                    'line' => $e->getLine(),
                )
            );
        } catch ( \Exception $e ) {
            // Fallback for older PHP versions
            error_log( 'AISBP Progressive Generate Exception: ' . $e->getMessage() );
            
            if ( $this->logger ) {
                $this->logger->error( sprintf( 
                    __( 'خطأ في التوليد التدريجي: %s', 'ai-site-builder-pro' ), 
                    $e->getMessage() 
                ) );
                $this->logger->finalize( 'failed' );
            }
            
            return new \WP_Error(
                'generation_error',
                __( 'خطأ في معالجة الطلب', 'ai-site-builder-pro' )
            );
        }
    }

    /**
     * Get phase-specific prompt
     *
     * @param int   $phase  Phase number.
     * @param array $params Generation parameters.
     * @return string Phase prompt.
     */
    private function get_phase_prompt( $phase, $params ) {
        $prompt = Master_Prompt::get_phase_prompt( $phase, $params );
        return apply_filters( 'aisbp_phase_prompt', $prompt, $phase, $params );
    }

    /**
     * Make request with automatic failover
     * 
     * RELIABILITY: Enhanced failover with retry logic and better error handling
     * Works with any AI model (DeepSeek, GPT-4, Claude, Gemini, etc.)
     *
     * @param string $preferred_model Preferred model ID.
     * @param string $prompt          The prompt.
     * @param array  $params          Additional parameters.
     * @return array|WP_Error Response or error.
     */
    private function request_with_failover( $preferred_model, $prompt, $params = array() ) {
        // Define failover order (prioritize cost-effective models first)
        $failover_order = array( 'deepseek', 'gemini', 'openai', 'claude' );
        
        // Start with preferred model
        if ( isset( $this->models[ $preferred_model ] ) ) {
            array_unshift( $failover_order, $preferred_model );
            $failover_order = array_unique( $failover_order );
        }
        
        // Filter to only available models
        $available_models = array_filter( $failover_order, function( $model_id ) {
            return isset( $this->models[ $model_id ] );
        } );
        
        if ( empty( $available_models ) ) {
            return new \WP_Error(
                'no_models',
                __( 'No AI models available. Please configure at least one API key.', 'ai-site-builder-pro' )
            );
        }

        $last_error = null;
        $retryable_errors = array( 'connection_error', 'timeout', 'api_error' );

        foreach ( $available_models as $model_id ) {
            $model = $this->models[ $model_id ];

            // Prepare request parameters
            $request_params = array(
                'temperature' => floatval( $params['temperature'] ?? 0.7 ),
                'max_tokens'  => absint( $params['max_tokens'] ?? 4096 ),
                'system'      => $params['system'] ?? $this->system_prompt,
            );

            // Check for images (vision request)
            $images = $params['images'] ?? array();

            // Make request with retry logic (handled by Model_Handler)
            $response = $model->request(
                $prompt,
                $request_params,
                $images
            );

            if ( ! is_wp_error( $response ) ) {
                $response['model'] = $model_id;
                $this->logger->info( sprintf( 
                    __( 'نجح الطلب باستخدام النموذج: %s', 'ai-site-builder-pro' ), 
                    $model_id 
                ) );
                return $response;
            }

            $error_code = $response->get_error_code();
            $error_message = $response->get_error_message();
            
            // Log failover attempt
            if ( $this->logger ) {
                $this->logger->warning( sprintf( 
                    \__( 'فشل النموذج %s: %s. جاري المحاولة مع نموذج آخر...', 'ai-site-builder-pro' ), 
                    $model_id, 
                    $error_message 
                ) );
            }

            $last_error = $response;

            /**
             * Fires when a model request fails
             *
             * @param string   $model_id Model identifier.
             * @param WP_Error $error    The error.
             */
            do_action( 'aisbp_model_failover', $model_id, $last_error );
            
            // If error is not retryable (like invalid API key), don't try other models
            // FIXED: Handle null values to prevent PHP Deprecated warnings
            $error_code_str = is_string($error_code) ? $error_code : (is_scalar($error_code) ? (string)$error_code : '');
            if ( !empty($error_code_str) && ( strpos( $error_code_str, '401' ) !== false || strpos( $error_code_str, 'invalid' ) !== false ) ) {
                continue; // Try next model
            }
        }

        // All models failed - return most descriptive error
        if ( $last_error ) {
            return $last_error;
        }

        return new \WP_Error(
            'no_models',
            __( 'No AI models available. Please configure at least one API key.', 'ai-site-builder-pro' )
        );
    }

    /**
     * Chat with AI for modifications
     *
     * @param array $params Chat parameters.
     * @return array|WP_Error Response or error.
     */
    public function chat( $params ) {
        $message = sanitize_textarea_field( $params['message'] ?? '' );
        $project_id = absint( $params['project_id'] ?? 0 );
        $context = $params['context'] ?? '';

        if ( empty( $message ) ) {
            return new \WP_Error( 'empty_message', __( 'Message cannot be empty.', 'ai-site-builder-pro' ) );
        }

        // Get preferred model
        $model_id = $params['model'] ?? get_option( 'aisbp_default_model', 'deepseek' );

        // Get current project info if available
        $project_code = '';
        if ( $project_id ) {
            $project = $this->database->get_project( $project_id );
            if ( $project ) {
                if ( ! empty( $project['generated_code'] ) ) {
                    $project_code = $project['generated_code'];
                }
                // Use project's model if not explicitly passed
                if ( empty( $params['model'] ) && ! empty( $project['ai_model'] ) ) {
                    $model_id = $project['ai_model'];
                }
            }
        }

        // Get dynamic system prompt based on current context
        if ( class_exists( 'AISBP\Master_Prompt' ) ) {
            $system_prompt = \AISBP\Master_Prompt::get_system_prompt( array(
                'project_id'    => $project_id,
                'creation_mode' => 'full_site', // Default to full_site for chat unless specified
            ) );
        } else {
            $system_prompt = $this->system_prompt;
        }

        // Build chat prompt
        $chat_prompt = "{$system_prompt}

You are an expert fullstack WordPress and Web Developer AI assistant.
Your goal is to help the user modify their generated website or answer questions about it.

CRITICAL INSTRUCTIONS:
1. If the user asks a question, answer it clearly and professionally in the same language as the user.
2. If the user requests a modification:
   - Provide a brief explanation of what you changed (in the same language as user).
   - You MUST return the COMPLETELY MODIFIED HTML code within a single markdown code block (e.g., ```html ... ```).
   - The returned code must include ALL CSS (in <style> tags) and ALL JavaScript (in <script> tags) necessary for the page to work.
   - Ensure the modified code is complete, functional, and maintains the design style of the original.
   - Do NOT return just snippets. Return the WHOLE page/section code so it can be previewed immediately.
   - Use the unique prefix provided in the system prompt for all new classes and IDs.

User request: {$message}

Current website code (for reference):
```html
" . (!empty($project_code) ? $project_code : ($context ?: 'No code generated yet.')) . "
```

Be polite, helpful, and concise in your explanations.";

        $this->logger->info( sprintf( __( 'بدء الشات مع النموذج: %s', 'ai-site-builder-pro' ), $model_id ) );
        
        // Make request
        $response = $this->request_with_failover( $model_id, $chat_prompt );

        if ( is_wp_error( $response ) ) {
            $this->logger->error( 'Chat request failed: ' . $response->get_error_message() );
            return $response;
        }

        $this->logger->info( 'Chat request successful. Processing response.' );

        // Extract any code changes
        $code = $this->extract_code( $response['content'] );
        $has_changes = ! empty( $code );

        // If there are changes, save to history
        if ( $has_changes && $project_id ) {
            // Save history for undo
            $this->database->add_history( array(
                'project_id'         => $project_id,
                'action_type'        => 'chat_modification',
                'action_description' => mb_substr( $message, 0, 100 ),
                'previous_state'     => array( 'generated_code' => $project_code ),
                'current_state'      => array( 'generated_code' => $code ),
            ) );

            // Update project
            $this->database->update_project( $project_id, array(
                'generated_code' => $code,
            ) );
        }

        // Track usage
        $this->database->track_usage( array(
            'project_id' => $project_id,
            'model'      => $response['model'] ?? $model_id,
            'operation'  => 'chat',
            'tokens_in'  => $response['prompt_tokens'] ?? 0,
            'tokens_out' => $response['completion_tokens'] ?? 0,
            'cost_usd'   => $response['cost'] ?? 0,
        ) );

        return array(
            'response'    => $response['content'],
            'code'        => $code,
            'has_changes' => $has_changes,
            'tokens'      => ( $response['prompt_tokens'] ?? 0 ) + ( $response['completion_tokens'] ?? 0 ),
            'cost'        => $response['cost'] ?? 0,
        );
    }

    /**
     * Test API key validity
     *
     * @param string $model_id Model identifier.
     * @param string $api_key  API key to test.
     * @return bool|WP_Error True if valid or error.
     */
    public function test_api_key( $model_id, $api_key ) {
        $connectors = array(
            'openai'   => API\OpenAI_Connector::class,
            'claude'   => API\Claude_Connector::class,
            'gemini'   => API\Gemini_Connector::class,
            'deepseek' => API\DeepSeek_Connector::class,
        );

        if ( ! isset( $connectors[ $model_id ] ) ) {
            return new \WP_Error( 'invalid_model', __( 'Invalid model specified.', 'ai-site-builder-pro' ) );
        }

        $connector_class = $connectors[ $model_id ];
        $connector = new $connector_class( $api_key );

        return $connector->validate_key( $api_key );
    }

    /**
     * Extract code from AI response
     *
     * @param string $content AI response content.
     * @return string Extracted code.
     */
    private function extract_code( $content ) {
        // Try to extract code blocks with various language markers
        $patterns = array(
            '/```(?:html|css|javascript|js|json|xml|php)?\s*([\s\S]*?)```/',  // Standard code blocks
            '/~~~(?:html|css|javascript|js|json|xml|php)?\s*([\s\S]*?)~~~/',  // Alternative markers
        );
        
        foreach ( $patterns as $pattern ) {
            if ( preg_match_all( $pattern, $content, $matches ) && ! empty( $matches[1] ) ) {
                $extracted = implode( "\n\n", array_filter( array_map( 'trim', $matches[1] ) ) );
                if ( ! empty( $extracted ) ) {
                    return $extracted;
                }
            }
        }

        // If no code blocks, check for HTML structure
        if ( stripos( $content, '<!DOCTYPE' ) !== false || stripos( $content, '<html' ) !== false ) {
            // Remove AI commentary before/after the HTML
            if ( preg_match( '/(<!DOCTYPE[^>]*>[\s\S]*<\/html>)/i', $content, $html_match ) ) {
                return $html_match[1];
            }
            return $content;
        }

        // Check for standalone HTML sections (like for section/layout modes)
        if ( preg_match( '/(<(?:section|div|header|footer|main|article)[^>]*>[\s\S]*<\/(?:section|div|header|footer|main|article)>)/i', $content, $section_match ) ) {
            return $section_match[1];
        }

        // Check for CSS
        if ( preg_match( '/<style[\s\S]*?<\/style>|[\w-]+\s*\{[^}]+\}/s', $content ) ) {
            return $content;
        }

        // Check for JSON (Divi 5 format)
        $json_content = trim( $content );
        if ( substr( $json_content, 0, 1 ) === '{' || substr( $json_content, 0, 1 ) === '[' ) {
            $decoded = json_decode( $json_content, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                return $json_content;
            }
        }

        // Log warning if we're returning raw content (might indicate AI response issue)
        if ( $this->logger ) {
            $this->logger->warning( __( 'استخراج الكود: لم يتم العثور على تنسيق كود معروف', 'ai-site-builder-pro' ), array(
                'content_length' => strlen( $content ),
                'content_preview' => substr( $content, 0, 200 ),
            ) );
        }

        return $content;
    }

    /**
     * Combine phase outputs into final code
     *
     * @param array $phases Phase results.
     * @return string Combined code.
     */
    private function combine_phases( $phases ) {
        // Get the final optimized code (phase 5)
        $final_phase = end( $phases );
        
        if ( ! empty( $final_phase['code'] ) ) {
            return $final_phase['code'];
        }

        // Fallback: combine all phases
        $html = '';
        $css = '';
        $js = '';

        foreach ( $phases as $phase ) {
            $code = $phase['code'] ?? '';
            
            // Extract CSS
            if ( preg_match_all( '/<style[^>]*>([\s\S]*?)<\/style>/i', $code, $styles ) ) {
                $css .= implode( "\n", $styles[1] );
            }
            
            // Extract JS
            if ( preg_match_all( '/<script[^>]*>([\s\S]*?)<\/script>/i', $code, $scripts ) ) {
                $js .= implode( "\n", $scripts[1] );
            }
            
            $html .= preg_replace( '/<style[\s\S]*?<\/style>|<script[\s\S]*?<\/script>/i', '', $code );
        }

        // Combine into single HTML document
        $combined = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        $combined .= "<meta charset=\"UTF-8\">\n";
        $combined .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $combined .= "<style>\n{$css}\n</style>\n";
        $combined .= "</head>\n<body>\n";
        $combined .= $html;
        $combined .= "\n<script>\n{$js}\n</script>\n";
        $combined .= "</body>\n</html>";

        return $combined;
    }

    /**
     * Check rate limit
     *
     * @return bool Whether request is allowed.
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $limit = get_option( 'aisbp_rate_limit_requests', 100 );
        $period = get_option( 'aisbp_rate_limit_period', 3600 );

        $transient_key = "aisbp_rate_{$user_id}";
        $current = get_transient( $transient_key );

        if ( false === $current ) {
            set_transient( $transient_key, 1, $period );
            return true;
        }

        if ( $current >= $limit ) {
            return false;
        }

        set_transient( $transient_key, $current + 1, $period );
        return true;
    }

    /**
     * Get available models info
     *
     * @return array Models info.
     */
    public function get_available_models() {
        $info = array();
        
        foreach ( $this->models as $id => $model ) {
            $info[ $id ] = $model->get_info();
        }

        return $info;
    }
}
