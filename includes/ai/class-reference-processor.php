<?php
/**
 * Reference Processor - معالج المراجع للـ AI
 * 
 * يحسن الـ Prompts بناءً على المراجع المقدمة
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

class AWBU_Reference_Processor {
    
    /**
     * Enhance prompt with references
     * 
     * @param string $prompt Original prompt
     * @param array $references References array
     * @return string Enhanced prompt
     */
    public function enhance_prompt($prompt, $references) {
        if (empty($references)) {
            return $prompt;
        }
        
        $enhanced = $prompt;
        
        // Add references section
        $enhanced .= "\n\n=== REFERENCE MATERIALS ===\n";
        $enhanced .= "Use these references as inspiration and guidance:\n\n";
        
        // Process each reference type
        if (!empty($references['links'])) {
            $enhanced .= $this->format_links($references['links']);
        }
        
        if (!empty($references['images'])) {
            $enhanced .= $this->format_images($references['images']);
        }
        
        if (!empty($references['files'])) {
            $enhanced .= $this->format_files($references['files']);
        }
        
        if (!empty($references['text'])) {
            $enhanced .= $this->format_text($references['text']);
        }
        
        // Add instructions
        $enhanced .= "\n\n=== INSTRUCTIONS ===\n";
        $enhanced .= "- Analyze the reference materials carefully\n";
        $enhanced .= "- Extract design patterns, colors, and styles\n";
        $enhanced .= "- Apply similar design principles to the generated content\n";
        $enhanced .= "- Maintain consistency with reference aesthetics\n";
        
        return $enhanced;
    }
    
    /**
     * Format links for prompt
     */
    private function format_links($links) {
        $formatted = "--- Reference Links ---\n";
        
        foreach ($links as $link) {
            $formatted .= "URL: {$link['url']}\n";
            
            if (!empty($link['title'])) {
                $formatted .= "Title: {$link['title']}\n";
            }
            
            if (!empty($link['description'])) {
                $formatted .= "Description: {$link['description']}\n";
            }
            
            if (!empty($link['content'])) {
                $formatted .= "Content Summary: " . wp_trim_words($link['content'], 200) . "\n";
            }
            
            $formatted .= "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Format images for prompt
     */
    private function format_images($images) {
        $formatted = "--- Reference Images ---\n";
        
        foreach ($images as $image) {
            $formatted .= "Image URL: {$image['local_url']}\n";
            
            if (!empty($image['alt'])) {
                $formatted .= "Description: {$image['alt']}\n";
            }
            
            if (!empty($image['caption'])) {
                $formatted .= "Caption: {$image['caption']}\n";
            }
            
            if (isset($image['width']) && isset($image['height'])) {
                $formatted .= "Dimensions: {$image['width']}x{$image['height']}\n";
            }
            
            $formatted .= "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Format files for prompt
     */
    private function format_files($files) {
        $formatted = "--- Reference Files ---\n";
        
        foreach ($files as $file) {
            $formatted .= "File: {$file['local_url']}\n";
            $formatted .= "Type: {$file['mime_type']}\n";
            $formatted .= "Size: " . size_format($file['size']) . "\n";
            $formatted .= "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Format text for prompt
     */
    private function format_text($texts) {
        $formatted = "--- Reference Text ---\n";
        
        foreach ($texts as $text) {
            if (!empty($text['title'])) {
                $formatted .= "Title: {$text['title']}\n";
            }
            
            $formatted .= "Content: {$text['content']}\n";
            $formatted .= "\n";
        }
        
        return $formatted;
    }
}

