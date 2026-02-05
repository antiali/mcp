<?php
/**
 * Reference Handler - معالج الملفات المرجعية
 * 
 * يدعم:
 * - الروابط (URLs)
 * - الصور (Images)
 * - الملفات (Files)
 * - النصوص المرجعية (Reference Text)
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Reference_Handler {
    
    /**
     * Process references (links, images, files)
     * 
     * @param array $references References array
     * @return array Processed references
     */
    public function process($references) {
        if (!is_array($references)) {
            return array();
        }
        
        $processed = array(
            'links' => array(),
            'images' => array(),
            'files' => array(),
            'text' => array(),
        );
        
        foreach ($references as $reference) {
            if (!is_array($reference)) {
                continue;
            }
            
            $type = isset($reference['type']) ? sanitize_text_field($reference['type']) : 'link';
            
            switch ($type) {
                case 'url':
                case 'link':
                    $processed['links'][] = $this->process_link($reference);
                    break;
                    
                case 'image':
                    $processed['images'][] = $this->process_image($reference);
                    break;
                    
                case 'file':
                    $processed['files'][] = $this->process_file($reference);
                    break;
                    
                case 'text':
                    $processed['text'][] = $this->process_text($reference);
                    break;
            }
        }
        
        return $processed;
    }
    
    /**
     * Process link reference
     * 
     * @param array $reference Reference data
     * @return array Processed link
     */
    private function process_link($reference) {
        $url = isset($reference['url']) ? esc_url_raw($reference['url']) : '';
        
        if (empty($url)) {
            return array('error' => 'URL is required');
        }
        
        // Fetch content from URL
        $content = $this->fetch_url_content($url);
        
        return array(
            'type' => 'link',
            'url' => $url,
            'title' => isset($reference['title']) ? sanitize_text_field($reference['title']) : '',
            'description' => isset($reference['description']) ? sanitize_textarea_field($reference['description']) : '',
            'content' => $content,
            'processed_at' => current_time('mysql'),
        );
    }
    
    /**
     * Process image reference
     * 
     * @param array $reference Reference data
     * @return array Processed image
     */
    private function process_image($reference) {
        $url = isset($reference['url']) ? esc_url_raw($reference['url']) : '';
        
        if (empty($url)) {
            return array('error' => 'Image URL is required');
        }
        
        // Download and upload image to WordPress
        $attachment_id = $this->download_image($url);
        
        if (is_wp_error($attachment_id)) {
            return array(
                'type' => 'image',
                'url' => $url,
                'error' => $attachment_id->get_error_message(),
            );
        }
        
        $image_data = wp_get_attachment_image_src($attachment_id, 'full');
        
        return array(
            'type' => 'image',
            'url' => $url,
            'attachment_id' => $attachment_id,
            'local_url' => $image_data[0] ?? $url,
            'width' => $image_data[1] ?? 0,
            'height' => $image_data[2] ?? 0,
            'alt' => isset($reference['alt']) ? sanitize_text_field($reference['alt']) : '',
            'caption' => isset($reference['caption']) ? sanitize_text_field($reference['caption']) : '',
            'processed_at' => current_time('mysql'),
        );
    }
    
    /**
     * Process file reference
     * 
     * @param array $reference Reference data
     * @return array Processed file
     */
    private function process_file($reference) {
        $url = isset($reference['url']) ? esc_url_raw($reference['url']) : '';
        
        if (empty($url)) {
            return array('error' => 'File URL is required');
        }
        
        // Download and upload file to WordPress
        $attachment_id = $this->download_file($url);
        
        if (is_wp_error($attachment_id)) {
            return array(
                'type' => 'file',
                'url' => $url,
                'error' => $attachment_id->get_error_message(),
            );
        }
        
        $file_url = wp_get_attachment_url($attachment_id);
        
        return array(
            'type' => 'file',
            'url' => $url,
            'attachment_id' => $attachment_id,
            'local_url' => $file_url,
            'filename' => basename($file_url),
            'mime_type' => get_post_mime_type($attachment_id),
            'size' => filesize(get_attached_file($attachment_id)),
            'processed_at' => current_time('mysql'),
        );
    }
    
    /**
     * Process text reference
     * 
     * @param array $reference Reference data
     * @return array Processed text
     */
    private function process_text($reference) {
        return array(
            'type' => 'text',
            'content' => isset($reference['content']) ? sanitize_textarea_field($reference['content']) : '',
            'title' => isset($reference['title']) ? sanitize_text_field($reference['title']) : '',
            'processed_at' => current_time('mysql'),
        );
    }
    
    /**
     * Fetch content from URL
     * 
     * @param string $url URL to fetch
     * @return string|WP_Error Content or error
     */
    private function fetch_url_content($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'user-agent' => 'AI Website Builder Unified/1.0',
        ));
        
        if (is_wp_error($response)) {
            return '';
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Extract text content (remove HTML tags)
        $content = wp_strip_all_tags($body);
        $content = wp_trim_words($content, 500); // Limit to 500 words
        
        return $content;
    }
    
    /**
     * Download image from URL
     * 
     * @param string $url Image URL
     * @return int|WP_Error Attachment ID or error
     */
    private function download_image($url) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        // Download file
        $tmp = download_url($url);
        
        if (is_wp_error($tmp)) {
            return $tmp;
        }
        
        // Get file extension
        $file_array = array(
            'name' => basename($url),
            'tmp_name' => $tmp,
        );
        
        // Upload to WordPress
        $attachment_id = media_handle_sideload($file_array, 0);
        
        // Clean up temp file
        @unlink($tmp);
        
        return $attachment_id;
    }
    
    /**
     * Download file from URL
     * 
     * @param string $url File URL
     * @return int|WP_Error Attachment ID or error
     */
    private function download_file($url) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        
        // Download file
        $tmp = download_url($url);
        
        if (is_wp_error($tmp)) {
            return $tmp;
        }
        
        // Get file extension
        $file_array = array(
            'name' => basename($url),
            'tmp_name' => $tmp,
        );
        
        // Upload to WordPress
        $attachment_id = media_handle_sideload($file_array, 0);
        
        // Clean up temp file
        @unlink($tmp);
        
        return $attachment_id;
    }
    
    /**
     * Extract design information from references
     * 
     * @param array $processed_references Processed references
     * @return array Design information
     */
    public function extract_design_info($processed_references) {
        $design_info = array(
            'colors' => array(),
            'fonts' => array(),
            'layout' => array(),
            'style' => array(),
        );
        
        // Extract from images (color palette)
        if (!empty($processed_references['images'])) {
            foreach ($processed_references['images'] as $image) {
                if (isset($image['local_url'])) {
                    $colors = $this->extract_colors_from_image($image['local_url']);
                    if (!empty($colors)) {
                        $design_info['colors'] = array_merge($design_info['colors'], $colors);
                    }
                }
            }
        }
        
        // Extract from links (content analysis)
        if (!empty($processed_references['links'])) {
            foreach ($processed_references['links'] as $link) {
                if (isset($link['content'])) {
                    $info = $this->analyze_content($link['content']);
                    $design_info = array_merge_recursive($design_info, $info);
                }
            }
        }
        
        return $design_info;
    }
    
    /**
     * Extract colors from image
     * 
     * @param string $image_url Image URL
     * @return array Colors array
     */
    private function extract_colors_from_image($image_url) {
        // Check if GD is available
        if (!function_exists('imagecreatefromstring')) {
            return array();
        }
        
        // Download image
        $response = wp_remote_get($image_url, array('timeout' => 15));
        if (is_wp_error($response)) {
            return array();
        }
        
        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            return array();
        }
        
        // Create image from string
        $image = @imagecreatefromstring($image_data);
        if (!$image) {
            return array();
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Sample pixels for color extraction
        $colors = array();
        $sample_count = 100;
        
        for ($i = 0; $i < $sample_count; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);
            
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
            $colors[] = $hex;
        }
        
        imagedestroy($image);
        
        // Get most common colors
        $color_counts = array_count_values($colors);
        arsort($color_counts);
        
        // Return top 5 unique colors
        return array_slice(array_keys($color_counts), 0, 5);
    }
    
    /**
     * Analyze content for design information
     * 
     * @param string $content Content to analyze
     * @return array Design information
     */
    private function analyze_content($content) {
        // Basic analysis
        return array(
            'style' => array(
                'tone' => $this->detect_tone($content),
                'keywords' => $this->extract_keywords($content),
            ),
        );
    }
    
    /**
     * Detect tone from content
     */
    private function detect_tone($content) {
        // Simple tone detection
        $positive_words = array('great', 'excellent', 'amazing', 'wonderful');
        $negative_words = array('bad', 'terrible', 'awful', 'horrible');
        
        $positive_count = 0;
        $negative_count = 0;
        
        foreach ($positive_words as $word) {
            $positive_count += substr_count(strtolower($content), $word);
        }
        
        foreach ($negative_words as $word) {
            $negative_count += substr_count(strtolower($content), $word);
        }
        
        if ($positive_count > $negative_count) {
            return 'positive';
        } elseif ($negative_count > $positive_count) {
            return 'negative';
        }
        
        return 'neutral';
    }
    
    /**
     * Extract keywords from content
     */
    private function extract_keywords($content) {
        // Simple keyword extraction
        $words = str_word_count(strtolower($content), 1);
        $word_count = array_count_values($words);
        arsort($word_count);
        
        return array_slice(array_keys($word_count), 0, 10);
    }
}

