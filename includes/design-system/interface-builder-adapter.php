<?php
/**
 * Builder Adapter Interface
 * 
 * @package AI_Website_Builder_Unified
 */

if (!defined('ABSPATH')) exit;

interface UDS_Builder_Adapter_Interface {
    public function get_colors();
    public function set_colors($colors);
    public function get_variables();
    public function set_variables($variables);
    public function clear_cache();
    public function get_builder_name();
    public function is_available();
}

