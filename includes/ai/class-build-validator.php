<?php
/**
 * Build Validator for AISBP namespace
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Build Validator class
 * 
 * Validates generated code and build results
 */
class Build_Validator {

    /**
     * Build Logger instance
     *
     * @var Build_Logger
     */
    private $logger;

    /**
     * Validation errors
     *
     * @var array
     */
    private $errors = array();

    /**
     * Constructor
     *
     * @param Build_Logger $logger Logger instance.
     */
    public function __construct( $logger = null ) {
        $this->logger = $logger;
    }

    /**
     * Validate generated code
     *
     * @param string $code      Generated code.
     * @param string $code_type Code type (html, section, layout, etc.).
     * @return array Validation result with 'valid' and 'errors' keys.
     */
    public function validate_generated_code( $code, $code_type = 'html' ) {
        $this->errors = array();

        // Basic validation
        if ( empty( $code ) || ! is_string( $code ) ) {
            $this->add_error( __( 'الكود المولد فارغ أو غير صالح.', 'ai-site-builder-pro' ) );
            return array( 'valid' => false, 'errors' => $this->errors );
        }

        // Check minimum length
        if ( strlen( $code ) < 50 ) {
            $this->add_error( __( 'الكود المولد قصير جداً.', 'ai-site-builder-pro' ) );
        }

        // Check for HTML structure if code type is html
        if ( $code_type === 'html' && ! $this->has_html_structure( $code ) ) {
            $this->add_error( __( 'الكود المولد لا يحتوي على بنية HTML صحيحة.', 'ai-site-builder-pro' ) );
        }

        // Check for potentially dangerous content
        if ( $this->has_dangerous_content( $code ) ) {
            $this->add_error( __( 'الكود المولد يحتوي على محتوى خطير.', 'ai-site-builder-pro' ) );
        }

        $valid = empty( $this->errors );
        
        if ( $this->logger ) {
            if ( $valid ) {
                $this->logger->info( __( 'تم التحقق من الكود بنجاح', 'ai-site-builder-pro' ) );
            } else {
                $this->logger->error( sprintf( __( 'فشل التحقق: %s', 'ai-site-builder-pro' ), implode( ', ', $this->errors ) ) );
            }
        }

        return array(
            'valid' => $valid,
            'errors' => $this->errors,
        );
    }

    /**
     * Validate save operation
     *
     * @param int    $project_id Project ID.
     * @param string $code       Code to save.
     * @return array Validation result.
     */
    public function validate_save( $project_id, $code ) {
        $this->errors = array();

        if ( empty( $project_id ) || $project_id <= 0 ) {
            $this->add_error( __( 'معرف المشروع غير صالح.', 'ai-site-builder-pro' ) );
        }

        if ( empty( $code ) ) {
            $this->add_error( __( 'الكود فارغ ولا يمكن حفظه.', 'ai-site-builder-pro' ) );
        }

        return array(
            'valid' => empty( $this->errors ),
            'errors' => $this->errors,
        );
    }

    /**
     * Get first error message
     *
     * @return string|false First error message or false.
     */
    public function get_first_error() {
        return ! empty( $this->errors ) ? $this->errors[0] : false;
    }

    /**
     * Get all errors
     *
     * @return array Error messages.
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Add error message
     *
     * @param string $message Error message.
     */
    private function add_error( $message ) {
        $this->errors[] = $message;
    }

    /**
     * Check if code has HTML structure
     *
     * @param string $code Code to check.
     * @return bool True if has HTML structure.
     */
    private function has_html_structure( $code ) {
        // Check for common HTML tags
        return preg_match( '/<(html|body|div|section|header|footer|main|article|nav|aside|h[1-6]|p|span|a|img|ul|ol|li|table|form|input|button|script|style)/i', $code );
    }

    /**
     * Check for dangerous content
     *
     * @param string $code Code to check.
     * @return bool True if has dangerous content.
     */
    private function has_dangerous_content( $code ) {
        // Check for potentially dangerous patterns
        $dangerous_patterns = array(
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
            '/proc_open\s*\(/i',
            '/popen\s*\(/i',
            '/file_get_contents\s*\(\s*["\']?https?:\/\//i',
            '/curl_exec\s*\(/i',
            '/base64_decode\s*\(/i',
        );

        foreach ( $dangerous_patterns as $pattern ) {
            if ( preg_match( $pattern, $code ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate API response
     *
     * @param array  $response API response data.
     * @param string $model_id Model identifier.
     * @return array Validation result.
     */
    public function validate_api_response( $response, $model_id = '' ) {
        $this->errors = array();

        if ( empty( $response ) || ! is_array( $response ) ) {
            $this->add_error( __( 'استجابة API فارغة أو غير صالحة.', 'ai-site-builder-pro' ) );
            return array( 'valid' => false, 'errors' => $this->errors );
        }

        // Check for error in response
        if ( isset( $response['error'] ) ) {
            $error_msg = isset( $response['error']['message'] ) ? $response['error']['message'] : __( 'خطأ في API', 'ai-site-builder-pro' );
            $this->add_error( $error_msg );
            return array( 'valid' => false, 'errors' => $this->errors );
        }

        // Check for content/choices
        if ( ! isset( $response['content'] ) && ! isset( $response['choices'] ) ) {
            $this->add_error( __( 'استجابة API لا تحتوي على محتوى.', 'ai-site-builder-pro' ) );
            return array( 'valid' => false, 'errors' => $this->errors );
        }

        return array(
            'valid' => empty( $this->errors ),
            'errors' => $this->errors,
        );
    }
}

