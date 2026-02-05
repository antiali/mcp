<?php
/**
 * AI Model Handler - Abstract base class for AI connectors
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Model Handler class
 * 
 * Provides unified interface for all AI model integrations
 */
abstract class Model_Handler {

    /**
     * Model identifier
     *
     * @var string
     */
    protected $model_id = '';

    /**
     * Model display name
     *
     * @var string
     */
    protected $model_name = '';

    /**
     * API endpoint
     *
     * @var string
     */
    protected $api_endpoint = '';

    /**
     * API key
     *
     * @var string
     */
    protected $api_key = '';

    /**
     * Default parameters
     *
     * @var array
     */
    protected $default_params = array(
        'temperature'    => 0.7,
        'max_tokens'     => 4096,
        'top_p'          => 1.0,
        'stream'         => false,
    );

    /**
     * Cost per 1M input tokens (USD)
     *
     * @var float
     */
    protected $cost_per_input_token = 0.0;

    /**
     * Cost per 1M output tokens (USD)
     *
     * @var float
     */
    protected $cost_per_output_token = 0.0;

    /**
     * Request timeout in seconds
     *
     * @var int
     */
    protected $timeout = 180;
    
    /**
     * Maximum retry attempts
     *
     * @var int
     */
    protected $max_retries = 3;
    
    /**
     * Retry delay in seconds (will be exponential)
     *
     * @var int
     */
    protected $retry_delay = 2;

    /**
     * Last request metadata
     *
     * @var array
     */
    protected $last_request = array();

    /**
     * Constructor
     *
     * @param string $api_key API key for authentication.
     */
    public function __construct( $api_key = '' ) {
        $this->api_key = $api_key;
    }

    /**
     * Send a request to the AI model
     *
     * @param string $prompt      The prompt to send.
     * @param array  $params      Additional parameters.
     * @param array  $images      Optional images for vision models.
     * @return array|WP_Error Response data or error.
     */
    abstract public function request( $prompt, $params = array(), $images = array() );

    /**
     * Validate API key
     *
     * @param string $api_key API key to validate.
     * @return bool|WP_Error True if valid or error.
     */
    abstract public function validate_key( $api_key );

    /**
     * Check if model supports vision
     *
     * @return bool Whether model supports image analysis.
     */
    abstract public function supports_vision();

    /**
     * Check if model supports streaming
     *
     * @return bool Whether model supports streaming responses.
     */
    public function supports_streaming() {
        return true;
    }

    /**
     * Get model info
     *
     * @return array Model information.
     */
    public function get_info() {
        return array(
            'id'                => $this->model_id,
            'name'              => $this->model_name,
            'supports_vision'   => $this->supports_vision(),
            'supports_streaming' => $this->supports_streaming(),
            'cost_per_input'    => $this->cost_per_input_token,
            'cost_per_output'   => $this->cost_per_output_token,
        );
    }

    /**
     * Calculate cost for a request
     *
     * @param int $input_tokens  Number of input tokens.
     * @param int $output_tokens Number of output tokens.
     * @return float Cost in USD.
     */
    public function calculate_cost( $input_tokens, $output_tokens ) {
        $input_cost  = ( $input_tokens / 1000000 ) * $this->cost_per_input_token;
        $output_cost = ( $output_tokens / 1000000 ) * $this->cost_per_output_token;
        
        return round( $input_cost + $output_cost, 6 );
    }

    /**
     * Get last request metadata
     *
     * @return array Request metadata.
     */
    public function get_last_request() {
        return $this->last_request;
    }

    /**
     * Make HTTP request
     *
     * @param string $url     Request URL.
     * @param array  $body    Request body.
     * @param array  $headers Custom headers.
     * @return array|WP_Error Response or error.
     */
    protected function make_request( $url, $body, $headers = array() ) {
        $start_time = microtime( true );

        $default_headers = array(
            'Content-Type' => 'application/json',
        );

        $headers = wp_parse_args( $headers, $default_headers );

        // Intelligent SSL Handling for Local Servers (Ampps, XAMPP, LocalWP)
        $is_local = in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ), true ) || strpos( site_url(), '.local' ) !== false || strpos( site_url(), 'localhost' ) !== false;
        $ssl_verify = apply_filters( 'aisbp_ssl_verify', ! $is_local );

        // PERFORMANCE: Calculate dynamic timeout based on prompt length
        $calculated_timeout = $this->calculate_timeout( strlen( wp_json_encode( $body ) ) );
        $request_timeout = apply_filters( 'aisbp_request_timeout', $calculated_timeout, $this->timeout );

        // PERFORMANCE & RELIABILITY: Make request with retry logic
        $response = $this->make_request_with_retry( $url, array(
            'headers'   => $headers,
            'body'      => wp_json_encode( $body ),
            'timeout'   => $request_timeout,
            'sslverify' => $ssl_verify,
        ) );

        $duration = round( ( microtime( true ) - $start_time ) * 1000 );

        if ( is_wp_error( $response ) ) {
            $error_msg = $response->get_error_message();
            
            // Provide specific guidance for common errors
            // FIXED: Handle null values to prevent PHP Deprecated warnings
            $error_msg_str = is_string($error_msg) ? $error_msg : '';
            if ( !empty($error_msg_str) && ( strpos( $error_msg_str, 'cURL error 28' ) !== false || strpos( $error_msg_str, 'timed out' ) !== false ) ) {
                $error_msg = \__( 'انتهت مهلة الاتصال بالـ API. جرب تقليل طول الوصف أو استخدام نموذج أسرع.', 'ai-site-builder-pro' );
            } elseif ( !empty($error_msg_str) && ( strpos( $error_msg_str, 'cURL error 6' ) !== false || strpos( $error_msg_str, 'resolve host' ) !== false ) ) {
                $error_msg = \__( 'تعذر الاتصال بخادم الـ API. تحقق من اتصال الإنترنت.', 'ai-site-builder-pro' );
            }
            
            $this->log_error( 'Request failed after retries: ' . $error_msg );
            return new \WP_Error( 'connection_error', $error_msg );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        $this->last_request = array(
            'url'         => $url,
            'status_code' => $status_code,
            'duration_ms' => $duration,
        );

        if ( $status_code >= 400 ) {
            $error_data = json_decode( $response_body, true );
            
            // FIXED: Enhanced error message extraction
            $error_message = '';
            if (isset($error_data['error']['message'])) {
                $error_message = $error_data['error']['message'];
            } elseif (isset($error_data['error'])) {
                if (is_string($error_data['error'])) {
                    $error_message = $error_data['error'];
                } else {
                    $error_message = json_encode($error_data['error']);
                }
            } else {
                $error_message = "HTTP Error: {$status_code}";
            }
            
            // Enhanced error logging
            $this->log_error( "API Error ({$status_code}): {$error_message}" );
            if (isset($error_data['error'])) {
                error_log( '[AISBP] Full error data: ' . json_encode($error_data['error']) );
            }
            
            // Provide specific guidance for common API errors
            if ( $status_code === 429 ) {
                $error_message = __( 'تم تجاوز حد الطلبات (Rate Limit). انتظر دقيقة ثم حاول مرة أخرى.', 'ai-site-builder-pro' );
            } elseif ( $status_code === 401 ) {
                $error_message = __( 'مفتاح الـ API غير صالح أو منتهي الصلاحية. تحقق من الإعدادات.', 'ai-site-builder-pro' );
            } elseif ( $status_code === 503 ) {
                $error_message = __( 'خادم الـ API مشغول حالياً. حاول مرة أخرى بعد دقائق.', 'ai-site-builder-pro' );
            } elseif ( $status_code === 500 ) {
                $error_message = __( 'خطأ داخلي في خادم الـ API. حاول مرة أخرى أو استخدم نموذجاً آخر.', 'ai-site-builder-pro' );
            }
            
            return new \WP_Error( 
                'api_error', 
                $error_message,
                array( 
                    'status' => $status_code, 
                    'response' => $error_data,
                    'original_message' => isset($error_data['error']['message']) ? $error_data['error']['message'] : ''
                )
            );
        }

        return json_decode( $response_body, true );
    }

    /**
     * Estimate token count for text
     * 
     * Approximation: ~4 characters per token for English text
     *
     * @param string $text Text to estimate.
     * @return int Estimated token count.
     */
    protected function estimate_tokens( $text ) {
        // More accurate estimation based on word count
        $word_count = str_word_count( $text );
        $char_count = strlen( $text );
        
        // Average between word-based and char-based estimation
        $word_estimate = $word_count * 1.3;
        $char_estimate = $char_count / 4;
        
        return (int) round( ( $word_estimate + $char_estimate ) / 2 );
    }

    /**
     * Log error message
     *
     * @param string $message Error message.
     */
    protected function log_error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "[AISBP {$this->model_id}] Error: {$message}" );
        }

        /**
         * Fires when an AI model error occurs
         *
         * @param string $message Error message.
         * @param string $model_id Model identifier.
         */
        do_action( 'aisbp_model_error', $message, $this->model_id );
    }

    /**
     * Log debug message
     *
     * @param string $message Debug message.
     */
    protected function log_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( "[AISBP {$this->model_id}] Debug: {$message}" );
        }
    }

    /**
     * Build messages array for chat completion
     *
     * @param string $prompt     User prompt.
     * @param string $system     System prompt.
     * @param array  $history    Previous messages.
     * @return array Messages array.
     */
    protected function build_messages( $prompt, $system = '', $history = array() ) {
        $messages = array();

        // System message
        if ( ! empty( $system ) ) {
            $messages[] = array(
                'role'    => 'system',
                'content' => $system,
            );
        }

        // History messages
        foreach ( $history as $msg ) {
            $messages[] = array(
                'role'    => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            );
        }

        // Current user message
        $messages[] = array(
            'role'    => 'user',
            'content' => $prompt,
        );

        return $messages;
    }

    /**
     * Build messages with images for vision models
     *
     * @param string $prompt User prompt.
     * @param array  $images Image URLs or base64 data.
     * @param string $system System prompt.
     * @return array Messages array.
     */
    protected function build_vision_messages( $prompt, $images, $system = '' ) {
        $messages = array();

        if ( ! empty( $system ) ) {
            $messages[] = array(
                'role'    => 'system',
                'content' => $system,
            );
        }

        // Build content array with text and images
        $content = array();
        
        // Add text
        $content[] = array(
            'type' => 'text',
            'text' => $prompt,
        );

        // Add images
        foreach ( $images as $image ) {
            if ( filter_var( $image, FILTER_VALIDATE_URL ) ) {
                $content[] = array(
                    'type'      => 'image_url',
                    'image_url' => array(
                        'url'    => $image,
                        'detail' => 'high',
                    ),
                );
            } else {
                // Assume base64
                $content[] = array(
                    'type'      => 'image_url',
                    'image_url' => array(
                        'url' => 'data:image/jpeg;base64,' . $image,
                    ),
                );
            }
        }

        $messages[] = array(
            'role'    => 'user',
            'content' => $content,
        );

        return $messages;
    }

    /**
     * Sanitize and validate prompt
     *
     * @param string $prompt Raw prompt.
     * @return string Sanitized prompt.
     */
    protected function sanitize_prompt( $prompt ) {
        // Remove potentially harmful content
        $prompt = wp_kses_post( $prompt );
        
        // Normalize whitespace
        $prompt = preg_replace( '/\s+/', ' ', $prompt );
        
        // Trim
        $prompt = trim( $prompt );

        return $prompt;
    }

    /**
     * Merge parameters with defaults
     *
     * @param array $params User parameters.
     * @return array Merged parameters.
     */
    protected function merge_params( $params ) {
        return wp_parse_args( $params, $this->default_params );
    }

    /**
     * Parse response to extract content
     *
     * @param array $response Raw API response.
     * @return array Parsed response with content, tokens, etc.
     */
    abstract protected function parse_response( $response );
}
