<?php
/**
 * Remote Design Manager - مدير التصميم عن بُعد
 * 
 * يدعم التصميم الكامل عن بُعد مع:
 * - الروابط المرجعية
 * - الصور
 * - الملفات
 * - التكامل مع AI Models
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Remote_Design_Manager {
    
    /**
     * Process remote design request
     * 
     * @param array $params Request parameters
     * @return array|WP_Error Result or error
     */
    public function process_remote_design($params) {
        // Validate input
        $validation = $this->validate_remote_request($params);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        $remote_url = isset($params['remote_url']) ? $params['remote_url'] : '';
        $remote_api_key = isset($params['remote_api_key']) ? $params['remote_api_key'] : '';

        // Process references
        $reference_handler = new AWBU_Reference_Handler();
        $processed_references = array();
        
        if (isset($params['references']) && !empty($params['references'])) {
            $processed_references = $reference_handler->process($params['references']);
        }
        
        // Extract design information from references
        $design_info = $reference_handler->extract_design_info($processed_references);
        
        // Get design system (from remote if provided, otherwise local)
        $current_design_system = $this->get_target_design_system($remote_url, $remote_api_key);
        
        // Build enhanced prompt with references
        $enhanced_prompt = $this->build_enhanced_prompt($params, $processed_references, $design_info, $current_design_system);
        
        // Generate with AI
        $ai_orchestrator = AWBU()->get_ai_orchestrator();
        $generation_params = array_merge($params, array(
            'prompt' => $enhanced_prompt,
            'processed_references' => $processed_references,
            'design_info' => $design_info,
            'builder' => $current_design_system['builder'] ?? 'divi5',
        ));
        
        $result = $ai_orchestrator->generate($generation_params);
        
        // Apply design system / Deployment
        if (!is_wp_error($result) && isset($result['code'])) {
            if (!empty($remote_url) && !empty($remote_api_key)) {
                $result = $this->deploy_to_remote($result, $design_info, $remote_url, $remote_api_key);
            } else {
                $result = $this->apply_design_system($result, $design_info);
            }
        }
        
        return $result;
    }
    
    /**
     * Get target design system (Remote or Local)
     */
    private function get_target_design_system($url = '', $api_key = '') {
        if (empty($url) || empty($api_key)) {
            return $this->get_current_design_system();
        }

        $response = wp_remote_get($url . '/wp-json/ymcp-connector/v1/design-system', array(
            'headers' => array('X-YMCP-API-Key' => $api_key),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return $this->get_current_design_system();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data['success']) || !$data['success']) {
            return $this->get_current_design_system();
        }

        return $data['data'];
    }

    /**
     * Get current design system from active builder
     */
    private function get_current_design_system() {
        $builder = AWBU_Builder_Detector::detect();
        $adapter = AWBU_Adapter_Factory::create($builder);
        
        if (!$adapter || is_wp_error($adapter)) {
            return array('colors' => array(), 'variables' => array(), 'builder' => 'divi5');
        }
        
        return array(
            'builder' => $builder,
            'colors' => $adapter->get_colors(),
            'variables' => $adapter->get_variables(),
        );
    }

    /**
     * Deploy results to remote site
     */
    private function deploy_to_remote($result, $design_info, $url, $api_key) {
        $response = wp_remote_post($url . '/wp-json/ymcp-connector/v1/apply-design', array(
            'headers' => array(
                'X-YMCP-API-Key' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'code' => $result['code'],
                'css' => $result['css'] ?? '',
                'js' => $result['js'] ?? '',
                'design_info' => $design_info,
                'summary' => $result['summary'] ?? ''
            )),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            $result['deployment_error'] = $response->get_error_message();
            $result['status'] = 'draft_saved_locally';
            return $result;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['success']) && $data['success']) {
            $result['deployed'] = true;
            $result['remote_response'] = $data;
        } else {
            $result['deployed'] = false;
            $result['deployment_error'] = $data['message'] ?? 'Unknown remote error';
        }

        return $result;
    }
    
    /**
     * Validate remote request
     */
    private function validate_remote_request($params) {
        if (empty($params['description']) && empty($params['references'])) {
            return new WP_Error(
                'missing_description',
                __('Description or references are required.', 'ai-website-builder-unified'),
                array('status' => 400)
            );
        }
        
        // Validate references if provided
        if (isset($params['references']) && !is_array($params['references'])) {
            return new WP_Error(
                'invalid_references',
                __('References must be an array.', 'ai-website-builder-unified'),
                array('status' => 400)
            );
        }
        
        return true;
    }
    
    /**
     * Build enhanced prompt with references - IMPROVED VERSION
     * 
     * This prompt is optimized for AI models (GPT-4, Claude, DeepSeek, Gemini)
     * and editors like Cursor and Antigravity to execute design tasks.
     */
    private function build_enhanced_prompt($params, $processed_references, $design_info, $current_design_system) {
        $prompt_parts = array();
        
        // === SYSTEM CONTEXT ===
        $prompt_parts[] = $this->build_system_context($current_design_system);
        
        // === USER REQUEST ===
        if (!empty($params['description'])) {
            $prompt_parts[] = "## 📋 USER REQUEST\n";
            $prompt_parts[] = sanitize_textarea_field($params['description']) . "\n";
        }
        
        // === REFERENCE MATERIALS ===
        if (!empty($processed_references)) {
            $prompt_parts[] = $this->build_reference_section($processed_references);
        }
        
        // === EXTRACTED DESIGN INFO ===
        if (!empty($design_info) && $this->has_design_info($design_info)) {
            $prompt_parts[] = $this->build_design_info_section($design_info);
        }
        
        // === CURRENT DESIGN SYSTEM ===
        if (!empty($current_design_system['colors']) || !empty($current_design_system['variables'])) {
            $prompt_parts[] = $this->build_design_system_section($current_design_system);
        }
        
        // === OUTPUT INSTRUCTIONS ===
        $prompt_parts[] = $this->build_output_instructions($current_design_system['builder'] ?? 'divi5');
        
        return implode("\n", $prompt_parts);
    }
    
    /**
     * Build system context section
     */
    private function build_system_context($design_system) {
        $builder = $design_system['builder'] ?? 'divi5';
        
        return <<<CONTEXT
# 🎨 AI Website Builder - Remote Design System

You are an expert website designer and developer. Your task is to create high-quality, modern website designs using the **{$builder}** page builder.

## 🎯 YOUR MISSION
1. Analyze all provided reference materials (URLs, images, files, text)
2. Extract design inspiration (colors, layout, style, tone)
3. Create a complete, professional design that matches the references
4. Use the current design system variables and colors
5. Output valid, production-ready code

## ⚡ IMPORTANT RULES
- Use ONLY Global Variables for colors: `\$variable({gcid:{color-id}})$`
- Use ONLY Global Variables for spacing: `\$variable({vid:{variable-id}})$`
- Create responsive designs (desktop, tablet, mobile)
- Follow modern UI/UX best practices
- Include proper semantic HTML structure
- Add micro-animations and hover effects where appropriate

CONTEXT;
    }
    
    /**
     * Build reference materials section
     */
    private function build_reference_section($processed_references) {
        $section = "\n## 📚 REFERENCE MATERIALS\n\n";
        $section .= "> Use these materials as design inspiration and content source.\n\n";
        
        // Links
        if (!empty($processed_references['links'])) {
            $section .= "### 🔗 Reference Websites\n";
            foreach ($processed_references['links'] as $i => $link) {
                $num = $i + 1;
                $section .= "\n**Reference #{$num}**: {$link['url']}\n";
                if (!empty($link['title'])) {
                    $section .= "- **Title**: {$link['title']}\n";
                }
                if (!empty($link['description'])) {
                    $section .= "- **Description**: {$link['description']}\n";
                }
                if (!empty($link['content'])) {
                    $content = wp_trim_words($link['content'], 150);
                    $section .= "- **Content Preview**:\n```\n{$content}\n```\n";
                }
            }
        }
        
        // Images
        if (!empty($processed_references['images'])) {
            $section .= "\n### 🖼️ Reference Images\n";
            foreach ($processed_references['images'] as $i => $image) {
                $num = $i + 1;
                $url = isset($image['local_url']) ? $image['local_url'] : $image['url'];
                $section .= "\n**Image #{$num}**: {$url}\n";
                if (!empty($image['alt'])) {
                    $section .= "- **Alt Text**: {$image['alt']}\n";
                }
                if (!empty($image['caption'])) {
                    $section .= "- **Caption**: {$image['caption']}\n";
                }
                if (!empty($image['width']) && !empty($image['height'])) {
                    $section .= "- **Dimensions**: {$image['width']}x{$image['height']}px\n";
                }
            }
        }
        
        // Files
        if (!empty($processed_references['files'])) {
            $section .= "\n### 📄 Reference Files\n";
            foreach ($processed_references['files'] as $i => $file) {
                $num = $i + 1;
                $section .= "**File #{$num}**: {$file['filename']} ({$file['mime_type']})\n";
            }
        }
        
        // Text references
        if (!empty($processed_references['text'])) {
            $section .= "\n### 📝 Reference Text/Content\n";
            foreach ($processed_references['text'] as $i => $text) {
                $num = $i + 1;
                $title = !empty($text['title']) ? $text['title'] : "Text #{$num}";
                $section .= "\n**{$title}**:\n";
                $section .= "```\n{$text['content']}\n```\n";
            }
        }
        
        return $section;
    }
    
    /**
     * Build design info section (extracted from references)
     */
    private function build_design_info_section($design_info) {
        $section = "\n## 🎨 EXTRACTED DESIGN INFORMATION\n\n";
        $section .= "> These were automatically extracted from the reference materials.\n\n";
        
        if (!empty($design_info['colors'])) {
            $section .= "### Color Palette\n";
            $section .= "| # | Color | Suggested Use |\n";
            $section .= "|---|-------|---------------|\n";
            $uses = array('Primary', 'Secondary', 'Accent', 'Background', 'Text');
            foreach ($design_info['colors'] as $i => $color) {
                $use = isset($uses[$i]) ? $uses[$i] : 'Additional';
                $section .= "| " . ($i + 1) . " | `{$color}` | {$use} |\n";
            }
            $section .= "\n";
        }
        
        if (!empty($design_info['style'])) {
            $section .= "### Style Analysis\n";
            if (!empty($design_info['style']['tone'])) {
                $section .= "- **Tone**: {$design_info['style']['tone']}\n";
            }
            if (!empty($design_info['style']['keywords'])) {
                $keywords = array_slice($design_info['style']['keywords'], 0, 10);
                $section .= "- **Keywords**: " . implode(', ', $keywords) . "\n";
            }
        }
        
        if (!empty($design_info['fonts'])) {
            $section .= "### Fonts\n";
            foreach ($design_info['fonts'] as $font) {
                $section .= "- {$font}\n";
            }
        }
        
        return $section;
    }
    
    /**
     * Build current design system section
     */
    private function build_design_system_section($design_system) {
        $section = "\n## 🎛️ CURRENT DESIGN SYSTEM\n\n";
        $section .= "> **IMPORTANT**: Use these Global Variables in your output!\n\n";
        
        $builder = $design_system['builder'] ?? 'divi5';
        
        // Colors
        if (!empty($design_system['colors'])) {
            $section .= "### Available Global Colors\n";
            $section .= "| ID | Name | Color | Usage |\n";
            $section .= "|----|------|-------|-------|\n";
            foreach ($design_system['colors'] as $id => $color_data) {
                $name = is_array($color_data) ? ($color_data['name'] ?? $id) : $id;
                $color_value = is_array($color_data) ? ($color_data['color'] ?? '#000000') : $color_data;
                $usage = "`\$variable({gcid:{$id}})$`";
                $section .= "| {$id} | {$name} | {$color_value} | {$usage} |\n";
            }
            $section .= "\n";
        }
        
        // Variables
        if (!empty($design_system['variables'])) {
            $section .= "### Available Global Variables\n";
            $section .= "| ID | Name | Value | Usage |\n";
            $section .= "|----|------|-------|-------|\n";
            foreach ($design_system['variables'] as $id => $var_data) {
                $name = is_array($var_data) ? ($var_data['name'] ?? $id) : $id;
                $value = is_array($var_data) ? ($var_data['value'] ?? '') : $var_data;
                $usage = "`\$variable({vid:{$id}})$`";
                $section .= "| {$id} | {$name} | {$value} | {$usage} |\n";
            }
            $section .= "\n";
        }
        
        return $section;
    }
    
    /**
     * Build output instructions
     */
    private function build_output_instructions($builder) {
        $builder_name = strtoupper($builder);
        
        return <<<OUTPUT

## 📤 OUTPUT REQUIREMENTS

### Format
Return your response in the following JSON structure:

```json
{
  "success": true,
  "code": "<complete {$builder_name} JSON or HTML code>",
  "css": "<custom CSS if needed>",
  "js": "<custom JavaScript if needed>",
  "colors_used": ["<list of color IDs used>"],
  "variables_used": ["<list of variable IDs used>"],
  "summary": "<brief description of what was created>"
}
```

### Code Requirements
1. **Use Global Variables**: Replace hardcoded colors with `\$variable({gcid:{color-id}})$`
2. **Responsive Design**: Include tablet and mobile styles
3. **Modern UI**: Use gradients, shadows, rounded corners, animations
4. **Accessibility**: Include proper alt tags, ARIA labels, contrast
5. **Performance**: Optimize images, minimize custom CSS/JS

### Quality Checklist
- [ ] All colors use Global Variables
- [ ] Responsive on all devices
- [ ] Follows reference design style
- [ ] Clean, semantic structure
- [ ] Includes hover/interaction states

OUTPUT;
    }
    
    /**
     * Check if design info has meaningful data
     */
    private function has_design_info($design_info) {
        return !empty($design_info['colors']) || 
               !empty($design_info['fonts']) || 
               !empty($design_info['style']);
    }
    
    /**
     * Apply design system to generated result
     */
    private function apply_design_system($result, $design_info) {
        $builder = AWBU_Builder_Detector::detect();
        $adapter = AWBU_Adapter_Factory::create($builder);
        
        if (!$adapter || is_wp_error($adapter)) {
            return $result;
        }
        
        // Apply colors if extracted
        if (!empty($design_info['colors'])) {
            $colors = array();
            $color_names = array('Primary', 'Secondary', 'Accent', 'Background', 'Text');
            
            foreach ($design_info['colors'] as $index => $color) {
                $name = isset($color_names[$index]) ? $color_names[$index] : "Color " . ($index + 1);
                $colors["gcid-ref-{$index}"] = array(
                    'color' => $color,
                    'name' => "Reference: {$name}",
                );
            }
            
            try {
                $adapter->set_colors($colors);
                $result['colors_applied'] = count($colors);
            } catch (Exception $e) {
                error_log("AWBU: Failed to apply colors: " . $e->getMessage());
            }
        }
        
        return $result;
    }
}

