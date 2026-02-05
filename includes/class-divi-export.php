<?php
/**
 * Divi 5 JSON Export System
 * 
 * Export Theme Builder templates as JSON for manual import to Divi 5
 *
 * @package AIWebsiteBuilderUnified
 */

defined('ABSPATH') || exit;

class AWBU_Divi_Export {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // AJAX handlers for export
        add_action('wp_ajax_awbu_export_divi_json', array($this, 'export_divi_json'));
        add_action('wp_ajax_awbu_get_exportable_templates', array($this, 'get_exportable_templates'));
        add_action('wp_ajax_awbu_export_all_templates', array($this, 'export_all_templates'));
        
        // Add export page
        add_action('admin_menu', array($this, 'add_export_menu'), 99);
    }
    
    /**
     * Add Export submenu
     */
    public function add_export_menu() {
        add_submenu_page(
            'ai-website-builder',
            __('Export JSON', 'ai-website-builder-unified'),
            __('Export JSON', 'ai-website-builder-unified'),
            'edit_posts',
            'ai-website-builder-export',
            array($this, 'render_export_page')
        );
    }
    
    /**
     * Render Export Page
     */
    public function render_export_page() {
        $templates = $this->get_all_divi_templates();
        ?>
        <div class="wrap awbu-export-page">
            <h1><?php esc_html_e('Export Divi 5 JSON', 'ai-website-builder-unified'); ?></h1>
            <p class="description"><?php esc_html_e('Download JSON files to import manually into Divi 5 Theme Builder', 'ai-website-builder-unified'); ?></p>
            
            <div class="awbu-export-container" style="margin-top: 20px;">
                
                <!-- Quick Export All -->
                <div class="awbu-export-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <h2 style="color: white; margin-top: 0;">📦 <?php esc_html_e('Export All Templates', 'ai-website-builder-unified'); ?></h2>
                    <p><?php esc_html_e('Download a ZIP file containing all templates as individual JSON files', 'ai-website-builder-unified'); ?></p>
                    <button type="button" class="button button-large" id="export-all-btn" style="background: white; color: #764ba2; border: none; font-weight: bold;">
                        <?php esc_html_e('Download All as ZIP', 'ai-website-builder-unified'); ?>
                    </button>
                </div>
                
                <!-- Headers -->
                <div class="awbu-export-section">
                    <h2>🎨 <?php esc_html_e('Headers', 'ai-website-builder-unified'); ?></h2>
                    <div class="awbu-template-grid">
                        <?php 
                        $headers = array_filter($templates, function($t) { return ($t['type'] ?? '') === 'header'; });
                        if (empty($headers)) : ?>
                            <p class="no-items"><?php esc_html_e('No headers found', 'ai-website-builder-unified'); ?></p>
                        <?php else :
                            foreach ($headers as $template) : ?>
                                <div class="awbu-template-item">
                                    <span class="template-title"><?php echo esc_html($template['title']); ?></span>
                                    <button type="button" class="button export-single-btn" 
                                            data-id="<?php echo esc_attr($template['id']); ?>"
                                            data-type="header">
                                        <?php esc_html_e('Download JSON', 'ai-website-builder-unified'); ?>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                
                <!-- Footers -->
                <div class="awbu-export-section">
                    <h2>📃 <?php esc_html_e('Footers', 'ai-website-builder-unified'); ?></h2>
                    <div class="awbu-template-grid">
                        <?php 
                        $footers = array_filter($templates, function($t) { return ($t['type'] ?? '') === 'footer'; });
                        if (empty($footers)) : ?>
                            <p class="no-items"><?php esc_html_e('No footers found', 'ai-website-builder-unified'); ?></p>
                        <?php else :
                            foreach ($footers as $template) : ?>
                                <div class="awbu-template-item">
                                    <span class="template-title"><?php echo esc_html($template['title']); ?></span>
                                    <button type="button" class="button export-single-btn" 
                                            data-id="<?php echo esc_attr($template['id']); ?>"
                                            data-type="footer">
                                        <?php esc_html_e('Download JSON', 'ai-website-builder-unified'); ?>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                
                <!-- Body Templates -->
                <div class="awbu-export-section">
                    <h2>📄 <?php esc_html_e('Body Templates', 'ai-website-builder-unified'); ?></h2>
                    <div class="awbu-template-grid">
                        <?php 
                        $bodies = array_filter($templates, function($t) { return ($t['type'] ?? '') === 'body'; });
                        if (empty($bodies)) : ?>
                            <p class="no-items"><?php esc_html_e('No body templates found', 'ai-website-builder-unified'); ?></p>
                        <?php else :
                            foreach ($bodies as $template) : ?>
                                <div class="awbu-template-item">
                                    <span class="template-title"><?php echo esc_html($template['title']); ?></span>
                                    <button type="button" class="button export-single-btn" 
                                            data-id="<?php echo esc_attr($template['id']); ?>"
                                            data-type="body">
                                        <?php esc_html_e('Download JSON', 'ai-website-builder-unified'); ?>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                
                <!-- Pages -->
                <div class="awbu-export-section">
                    <h2>📑 <?php esc_html_e('Pages with Divi Builder', 'ai-website-builder-unified'); ?></h2>
                    <div class="awbu-template-grid">
                        <?php 
                        $pages = $this->get_divi_pages();
                        if (empty($pages)) : ?>
                            <p class="no-items"><?php esc_html_e('No Divi pages found', 'ai-website-builder-unified'); ?></p>
                        <?php else :
                            foreach ($pages as $page) : ?>
                                <div class="awbu-template-item">
                                    <span class="template-title"><?php echo esc_html($page->post_title); ?></span>
                                    <span class="template-meta"><?php echo esc_html($page->post_status); ?></span>
                                    <button type="button" class="button export-single-btn" 
                                            data-id="<?php echo esc_attr($page->ID); ?>"
                                            data-type="page">
                                        <?php esc_html_e('Download JSON', 'ai-website-builder-unified'); ?>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .awbu-export-page { max-width: 1200px; }
        .awbu-export-section { 
            background: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .awbu-export-section h2 { margin-top: 0; color: #1e293b; }
        .awbu-template-grid { display: flex; flex-direction: column; gap: 10px; }
        .awbu-template-item { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .awbu-template-item:hover { background: #f1f5f9; }
        .template-title { font-weight: 500; color: #334155; }
        .template-meta { color: #64748b; font-size: 12px; margin-left: auto; margin-right: 15px; }
        .no-items { color: #94a3b8; font-style: italic; }
        .export-single-btn { background: #4f46e5 !important; color: white !important; border: none !important; }
        .export-single-btn:hover { background: #4338ca !important; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Export single template
            $('.export-single-btn').on('click', function() {
                const id = $(this).data('id');
                const type = $(this).data('type');
                const $btn = $(this);
                
                $btn.prop('disabled', true).text('<?php esc_html_e('Exporting...', 'ai-website-builder-unified'); ?>');
                
                // Create form and submit to get file download
                const form = $('<form>', {
                    method: 'POST',
                    action: ajaxurl
                });
                
                form.append($('<input>', { type: 'hidden', name: 'action', value: 'awbu_export_divi_json' }));
                form.append($('<input>', { type: 'hidden', name: 'template_id', value: id }));
                form.append($('<input>', { type: 'hidden', name: 'template_type', value: type }));
                form.append($('<input>', { type: 'hidden', name: 'nonce', value: '<?php echo wp_create_nonce('awbu_export'); ?>' }));
                
                $('body').append(form);
                form.submit();
                form.remove();
                
                setTimeout(function() {
                    $btn.prop('disabled', false).text('<?php esc_html_e('Download JSON', 'ai-website-builder-unified'); ?>');
                }, 2000);
            });
            
            // Export all as ZIP
            $('#export-all-btn').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true).text('<?php esc_html_e('Creating ZIP...', 'ai-website-builder-unified'); ?>');
                
                const form = $('<form>', {
                    method: 'POST',
                    action: ajaxurl
                });
                
                form.append($('<input>', { type: 'hidden', name: 'action', value: 'awbu_export_all_templates' }));
                form.append($('<input>', { type: 'hidden', name: 'nonce', value: '<?php echo wp_create_nonce('awbu_export'); ?>' }));
                
                $('body').append(form);
                form.submit();
                form.remove();
                
                setTimeout(function() {
                    $btn.prop('disabled', false).text('<?php esc_html_e('Download All as ZIP', 'ai-website-builder-unified'); ?>');
                }, 3000);
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get all Divi Theme Builder templates
     */
    private function get_all_divi_templates() {
        $templates = array();
        
        // Get et_template posts
        $args = array(
            'post_type' => 'et_template',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft'),
        );
        
        $query = new WP_Query($args);
        
        foreach ($query->posts as $post) {
            $type = get_post_meta($post->ID, '_et_template_type', true);
            
            $templates[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $type ?: 'template',
                'status' => $post->post_status,
            );
        }
        
        return $templates;
    }
    
    /**
     * Get pages with Divi Builder enabled
     */
    private function get_divi_pages() {
        global $wpdb;
        
        $pages = $wpdb->get_results("
            SELECT p.ID, p.post_title, p.post_status, p.post_content
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'page'
            AND p.post_status IN ('publish', 'draft')
            AND pm.meta_key = '_et_pb_use_builder'
            AND pm.meta_value = 'on'
            ORDER BY p.post_title ASC
            LIMIT 100
        ");
        
        return $pages ?: array();
    }
    
    /**
     * Export single Divi JSON
     */
    public function export_divi_json() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'awbu_export')) {
            wp_die('Invalid nonce');
        }
        
        $template_id = intval($_POST['template_id'] ?? 0);
        $template_type = sanitize_text_field($_POST['template_type'] ?? 'template');
        
        if (!$template_id) {
            wp_die('Invalid template ID');
        }
        
        $post = get_post($template_id);
        if (!$post) {
            wp_die('Template not found');
        }
        
        // Build Divi 5 compatible JSON
        $json_data = $this->build_divi_json($post, $template_type);
        
        // Sanitize filename
        $filename = sanitize_file_name($template_type . '-' . $post->post_title . '-' . $template_id . '.json');
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json_data));
        
        echo $json_data;
        exit;
    }
    
    /**
     * Build Divi 5 compatible JSON
     */
    private function build_divi_json($post, $type = 'template') {
        $content = $post->post_content;
        
        // Get Divi specific meta
        $et_pb_old_content = get_post_meta($post->ID, '_et_pb_old_content', true);
        $et_builder_version = get_post_meta($post->ID, '_et_builder_version', true);
        $template_conditions = get_post_meta($post->ID, '_et_template_conditions', true);
        
        // Check if it's Divi 5 JSON format (starts with JSON structure)
        $is_divi5 = false;
        if (substr(trim($content), 0, 1) === '{' || substr(trim($content), 0, 1) === '[') {
            $is_divi5 = true;
        }
        
        // Build export structure
        $export = array(
            'version' => '5.0',
            'type' => $type,
            'title' => $post->post_title,
            'created' => current_time('mysql'),
            'plugin' => 'AI Website Builder Unified',
            'builder_version' => $et_builder_version ?: '5.0',
        );
        
        if ($is_divi5) {
            // Already JSON, decode and include
            $decoded = json_decode($content, true);
            if ($decoded) {
                $export['content'] = $decoded;
            } else {
                $export['content_raw'] = $content;
            }
        } else {
            // Shortcode format - include as-is for conversion
            $export['content_shortcode'] = $content;
            $export['old_content'] = $et_pb_old_content;
        }
        
        // Include template conditions for Theme Builder templates
        if ($template_conditions) {
            $export['conditions'] = maybe_unserialize($template_conditions);
        }
        
        // Add all relevant meta
        $export['meta'] = array(
            'use_builder' => get_post_meta($post->ID, '_et_pb_use_builder', true),
            'template_type' => get_post_meta($post->ID, '_et_template_type', true),
            'page_layout' => get_post_meta($post->ID, '_et_pb_page_layout', true),
            'side_nav' => get_post_meta($post->ID, '_et_pb_side_nav', true),
        );
        
        return json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Export all templates as ZIP
     */
    public function export_all_templates() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'awbu_export')) {
            wp_die('Invalid nonce');
        }
        
        // Check for ZipArchive
        if (!class_exists('ZipArchive')) {
            wp_die('ZipArchive not available on this server');
        }
        
        $templates = $this->get_all_divi_templates();
        $pages = $this->get_divi_pages();
        
        // Create temp file
        $zip_file = tempnam(sys_get_temp_dir(), 'divi_export_') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
            wp_die('Could not create ZIP file');
        }
        
        // Add templates
        foreach ($templates as $template) {
            $post = get_post($template['id']);
            if ($post) {
                $json = $this->build_divi_json($post, $template['type']);
                $filename = sanitize_file_name($template['type'] . '/' . $post->post_title . '-' . $post->ID . '.json');
                $zip->addFromString($filename, $json);
            }
        }
        
        // Add pages
        foreach ($pages as $page) {
            $json = $this->build_divi_json($page, 'page');
            $filename = sanitize_file_name('pages/' . $page->post_title . '-' . $page->ID . '.json');
            $zip->addFromString($filename, $json);
        }
        
        // Add README
        $readme = "# Divi 5 Export\n\n";
        $readme .= "Exported: " . current_time('mysql') . "\n";
        $readme .= "Templates: " . count($templates) . "\n";
        $readme .= "Pages: " . count($pages) . "\n\n";
        $readme .= "## How to Import\n\n";
        $readme .= "1. Open Divi Theme Builder in WordPress admin\n";
        $readme .= "2. Click 'Add New Template' or edit existing\n";
        $readme .= "3. Use Divi's import feature to upload JSON files\n";
        $readme .= "4. For pages, create a new page and import the JSON content\n";
        
        $zip->addFromString('README.md', $readme);
        
        $zip->close();
        
        // Send ZIP
        $download_name = 'divi-export-' . date('Y-m-d-His') . '.zip';
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($zip_file));
        
        readfile($zip_file);
        unlink($zip_file);
        
        exit;
    }
}

// Initialize
AWBU_Divi_Export::instance();
