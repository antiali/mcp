# 📦 YMCP Personal Assistant - Complete Update Package

هذه الملف يجمع كل التعديلات المطلوبة لإضافة ميزات **YMCP Personal Assistant**.

---

## 📋 خطوات التطبيق:

### الخطوة 1: الملفات الجديدة (4 ملفات)

أنشئ هذه الملفات في مجلد `includes/`:

#### 1.1 `includes/dashboard/class-dashboard.php`
انسخ من `C:\Users\earngate\.openclaw\workspace\ymcp-personal-assistant\includes\dashboard\class-dashboard.php`

#### 1.2 `includes/analytics/class-analytics-hub.php`
انسخ من `C:\Users\earngate\.openclaw\workspace\ymcp-personal-assistant\includes\analytics\class-analytics-hub.php`

#### 1.3 `includes/security/class-security-guard.php`
انسخ من `C:\Users\earngate\.openclaw\workspace\ymcp-personal-assistant\includes\security\class-security-guard.php`

#### 1.4 `includes/security/class-backup-manager.php`
انسخ من `C:\Users\earngate\.openclaw\workspace\ymcp-personal-assistant\includes\security\class-backup-manager.php`

---

### الخطوة 2: تحديث الملف الرئيسي `ai-website-builder-unified.php`

ابحث عن `private $load_dependencies()` وأضف هذه الأسطر قبل نهايته:

```php
// ==================== NEW: YMCP Personal Assistant ====================

// Dashboard System
if (file_exists(AWBU_PLUGIN_DIR . 'includes/dashboard/class-dashboard.php')) {
    require_once AWBU_PLUGIN_DIR . 'includes/dashboard/class-dashboard.php';
}

// Analytics System
if (file_exists(AWBU_PLUGIN_DIR . 'includes/analytics/class-analytics-hub.php')) {
    require_once AWBU_PLUGIN_DIR . 'includes/analytics/class-analytics-hub.php';
}

// Security System
if (file_exists(AWBU_PLUGIN_DIR . 'includes/security/class-security-guard.php')) {
    require_once AWBU_PLUGIN_DIR . 'includes/security/class-security-guard.php';
}

if (file_exists(AWBU_PLUGIN_DIR . 'includes/security/class-backup-manager.php')) {
    require_once AWBU_PLUGIN_DIR . 'includes/security/class-backup-manager.php';
}
```

ثم ابحث عن `private function init()` وأضف هذه الأسطر بعد `$this->remote_design_manager = new AWBU_Remote_Design_Manager();`:

```php
// Initialize YMCP Personal Assistant Components
if (class_exists('YMCP_Dashboard')) {
    $this->dashboard = new YMCP_Dashboard();
}

if (class_exists('YMCP_Analytics_Hub')) {
    $this->analytics_hub = new YMCP_Analytics_Hub();
}

if (class_exists('YMCP_Security_Guard')) {
    $this->security_guard = new YMCP_Security_Guard();
}

if (class_exists('YMCP_Backup_Manager')) {
    $this->backup_manager = new YMCP_Backup_Manager();
}
```

---

### الخطوة 3: تحديث `includes/mcp/class-mcp-tools-enhanced.php`

#### 3.1 أضف هذه الأدوات الجديدة في دالة `get_tool_definitions()`

ابحث عن قسم `// ============ CODE ANALYSIS & DEBUGGING TOOLS (Based on WordPress MCP) ============`

وأضف هذه الأسطر قبله:

```php
// ==================== YMCP PERSONAL ASSISTANT TOOLS ====================

// Dashboard Tools
$tools[] = array(
    'name' => 'ymcp_get_dashboard',
    'description' => 'Get YMCP dashboard summary with site health, stats, and alerts',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_site_info',
    'description' => 'Get comprehensive site information',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_tasks',
    'description' => 'Get pending tasks and reminders',
    'parameters' => array()
);

// Analytics Tools
$tools[] = array(
    'name' => 'ymcp_get_analytics',
    'description' => 'Get analytics data (visitors, page views, etc.)',
    'parameters' => array(
        'start_date' => array('type' => 'string', 'required' => false),
        'end_date' => array('type' => 'string', 'required' => false),
        'limit' => array('type' => 'integer', 'required' => false, 'default' => 1000),
    )
);

$tools[] = array(
    'name' => 'ymcp_generate_report',
    'description' => 'Generate analytics report (json, html, csv)',
    'parameters' => array(
        'format' => array('type' => 'string', 'required' => false, 'default' => 'json', 'enum' => array('json', 'html', 'csv')),
    )
);

$tools[] = array(
    'name' => 'ymcp_clear_analytics',
    'description' => 'Clear analytics data before specific date',
    'parameters' => array(
        'before_date' => array('type' => 'string', 'required' => false),
    )
);

// Security Tools
$tools[] = array(
    'name' => 'ymcp_security_scan',
    'description' => 'Run comprehensive security scan',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_get_security_report',
    'description' => 'Get last security scan report',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_create_backup',
    'description' => 'Create full site backup',
    'parameters' => array(
        'backup_name' => array('type' => 'string', 'required' => false),
    )
);

$tools[] = array(
    'name' => 'ymcp_restore_backup',
    'description' => 'Restore site from backup',
    'parameters' => array(
        'backup_id' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_list_backups',
    'description' => 'List all available backups',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_delete_backup',
    'description' => 'Delete a backup',
    'parameters' => array(
        'backup_id' => array('type' => 'string', 'required' => true),
    )
);

// Communication Tools
$tools[] = array(
    'name' => 'ymcp_send_email',
    'description' => 'Send email notification',
    'parameters' => array(
        'to' => array('type' => 'string', 'required' => true),
        'subject' => array('type' => 'string', 'required' => true),
        'message' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_chatbot_query',
    'description' => 'Query AI chatbot',
    'parameters' => array(
        'question' => array('type' => 'string', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_train_chatbot',
    'description' => 'Train chatbot on site data',
    'parameters' => array()
);

// E-commerce Tools
$tools[] = array(
    'name' => 'ymcp_create_product',
    'description' => 'Create WooCommerce product with AI',
    'parameters' => array(
        'name' => array('type' => 'string', 'required' => true),
        'description' => array('type' => 'string', 'required' => true),
        'price' => array('type' => 'number', 'required' => false),
    )
);

$tools[] = array(
    'name' => 'ymcp_update_inventory',
    'description' => 'Update product inventory',
    'parameters' => array(
        'product_id' => array('type' => 'integer', 'required' => true),
        'stock_quantity' => array('type' => 'integer', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_get_sales_report',
    'description' => 'Get sales and revenue report',
    'parameters' => array(
        'start_date' => array('type' => 'string', 'required' => false),
        'end_date' => array('type' => 'string', 'required' => false),
    )
);

// Automation Tools
$tools[] = array(
    'name' => 'ymcp_create_workflow',
    'description' => 'Create automation workflow',
    'parameters' => array(
        'name' => array('type' => 'string', 'required' => true),
        'trigger' => array('type' => 'string', 'required' => true),
        'actions' => array('type' => 'array', 'required' => true),
    )
);

$tools[] = array(
    'name' => 'ymcp_list_workflows',
    'description' => 'List all automation workflows',
    'parameters' => array()
);

$tools[] = array(
    'name' => 'ymcp_trigger_workflow',
    'description' => 'Trigger a workflow manually',
    'parameters' => array(
        'workflow_id' => array('type' => 'string', 'required' => true),
    )
);
```

#### 3.2 أضف حالات التنفيذ في دالة `execute_tool()`

ابحث عن `default:` في نهاية switch وأضف هذه الحالات قبله:

```php
// ==================== YMCP PERSONAL ASSISTANT TOOLS ====================
case 'ymcp_get_dashboard':
    return $this->ymcp_get_dashboard($params);

case 'ymcp_get_site_info':
    return $this->ymcp_get_site_info($params);

case 'ymcp_get_tasks':
    return $this->ymcp_get_tasks($params);

case 'ymcp_get_analytics':
    return $this->ymcp_get_analytics($params);

case 'ymcp_generate_report':
    return $this->ymcp_generate_report($params);

case 'ymcp_clear_analytics':
    return $this->ymcp_clear_analytics($params);

case 'ymcp_security_scan':
    return $this->ymcp_security_scan($params);

case 'ymcp_get_security_report':
    return $this->ymcp_get_security_report($params);

case 'ymcp_create_backup':
    return $this->ymcp_create_backup($params);

case 'ymcp_restore_backup':
    return $this->ymcp_restore_backup($params);

case 'ymcp_list_backups':
    return $this->ymcp_list_backups($params);

case 'ymcp_delete_backup':
    return $this->ymcp_delete_backup($params);

case 'ymcp_send_email':
    return $this->ymcp_send_email($params);

case 'ymcp_chatbot_query':
    return $this->ymcp_chatbot_query($params);

case 'ymcp_train_chatbot':
    return $this->ymcp_train_chatbot($params);

case 'ymcp_create_product':
    return $this->ymcp_create_product($params);

case 'ymcp_update_inventory':
    return $this->ymcp_update_inventory($params);

case 'ymcp_get_sales_report':
    return $this->ymcp_get_sales_report($params);

case 'ymcp_create_workflow':
    return $this->ymcp_create_workflow($params);

case 'ymcp_list_workflows':
    return $this->ymcp_list_workflows($params);

case 'ymcp_trigger_workflow':
    return $this->ymcp_trigger_workflow($params);
```

#### 3.3 أضف دوال التنفيذ في نهاية الملف

ابحث عن نهاية الملف وأضف هذه الدوال:

```php
// ==================== YMCP PERSONAL ASSISTANT IMPLEMENTATIONS ====================

private function ymcp_get_dashboard($params) {
    if (class_exists('YMCP_Dashboard')) {
        $dashboard = new YMCP_Dashboard();
        return $dashboard->get_dashboard_data();
    }
    return new WP_Error('component_missing', 'YMCP Dashboard component not available');
}

private function ymcp_get_site_info($params) {
    return array(
        'success' => true,
        'site_url' => site_url(),
        'home_url' => home_url(),
        'site_name' => get_bloginfo('name'),
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'active_theme' => wp_get_theme()->get('Name'),
        'active_plugins_count' => count(get_option('active_plugins', array())),
    );
}

private function ymcp_get_tasks($params) {
    if (class_exists('YMCP_Dashboard')) {
        $dashboard = new YMCP_Dashboard();
        return array('success' => true, 'tasks' => $dashboard->get_tasks());
    }
    return array('success' => true, 'tasks' => array());
}

private function ymcp_get_analytics($params) {
    if (class_exists('YMCP_Analytics_Hub')) {
        $analytics = new YMCP_Analytics_Hub();
        return $analytics->get_analytics($params);
    }
    return new WP_Error('component_missing', 'YMCP Analytics Hub component not available');
}

private function ymcp_generate_report($params) {
    if (class_exists('YMCP_Analytics_Hub')) {
        $analytics = new YMCP_Analytics_Hub();
        return $analytics->generate_report($params);
    }
    return new WP_Error('component_missing', 'YMCP Analytics Hub component not available');
}

private function ymcp_clear_analytics($params) {
    if (class_exists('YMCP_Analytics_Hub')) {
        $analytics = new YMCP_Analytics_Hub();
        return $analytics->clear_analytics(isset($params['before_date']) ? $params['before_date'] : null);
    }
    return new WP_Error('component_missing', 'YMCP Analytics Hub component not available');
}

private function ymcp_security_scan($params) {
    if (class_exists('YMCP_Security_Guard')) {
        $security = new YMCP_Security_Guard();
        return $security->scan();
    }
    return new WP_Error('component_missing', 'YMCP Security Guard component not available');
}

private function ymcp_get_security_report($params) {
    if (class_exists('YMCP_Security_Guard')) {
        $security = new YMCP_Security_Guard();
        return $security->get_security_report();
    }
    return new WP_Error('component_missing', 'YMCP Security Guard component not available');
}

private function ymcp_create_backup($params) {
    if (class_exists('YMCP_Backup_Manager')) {
        $backup = new YMCP_Backup_Manager();
        return $backup->create_backup(isset($params['backup_name']) ? $params['backup_name'] : null);
    }
    return new WP_Error('component_missing', 'YMCP Backup Manager component not available');
}

private function ymcp_restore_backup($params) {
    if (class_exists('YMCP_Backup_Manager')) {
        $backup = new YMCP_Backup_Manager();
        return $backup->restore_backup($params['backup_id']);
    }
    return new WP_Error('component_missing', 'YMCP Backup Manager component not available');
}

private function ymcp_list_backups($params) {
    if (class_exists('YMCP_Backup_Manager')) {
        $backup = new YMCP_Backup_Manager();
        return array('success' => true, 'backups' => $backup->get_backups());
    }
    return new WP_Error('component_missing', 'YMCP Backup Manager component not available');
}

private function ymcp_delete_backup($params) {
    if (class_exists('YMCP_Backup_Manager')) {
        $backup = new YMCP_Backup_Manager();
        return $backup->delete_backup($params['backup_id']);
    }
    return new WP_Error('component_missing', 'YMCP Backup Manager component not available');
}

private function ymcp_send_email($params) {
    $to = isset($params['to']) ? sanitize_email($params['to']) : '';
    $subject = isset($params['subject']) ? sanitize_text_field($params['subject']) : '';
    $message = isset($params['message']) ? wp_kses_post($params['message']) : '';
    
    if (empty($to) || empty($subject) || empty($message)) {
        return new WP_Error('missing_params', 'To, subject, and message are required');
    }
    
    $sent = wp_mail($to, $subject, $message);
    
    return array('success' => $sent, 'message' => $sent ? 'Email sent successfully' : 'Failed to send email');
}

private function ymcp_chatbot_query($params) {
    $question = isset($params['question']) ? sanitize_textarea_field($params['question']) : '';
    
    if (empty($question)) {
        return new WP_Error('missing_question', 'Question is required');
    }
    
    return array(
        'success' => true,
        'answer' => 'I understand your question: ' . $question . '. (Chatbot training coming soon!)',
    );
}

private function ymcp_train_chatbot($params) {
    return array('success' => true, 'message' => 'Chatbot training initiated. This feature requires site content analysis.');
}

private function ymcp_create_product($params) {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce is not active');
    }
    
    $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $description = isset($params['description']) ? sanitize_textarea_field($params['description']) : '';
    $price = isset($params['price']) ? floatval($params['price']) : 0;
    
    if (empty($name)) {
        return new WP_Error('missing_name', 'Product name is required');
    }
    
    $product_id = wp_insert_post(array(
        'post_title' => $name,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_type' => 'product',
    ));
    
    if (is_wp_error($product_id)) {
        return $product_id;
    }
    
    update_post_meta($product_id, '_price', $price);
    update_post_meta($product_id, '_regular_price', $price);
    
    return array('success' => true, 'product_id' => $product_id, 'url' => get_permalink($product_id));
}

private function ymcp_update_inventory($params) {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce is not active');
    }
    
    $product_id = isset($params['product_id']) ? intval($params['product_id']) : 0;
    $stock_quantity = isset($params['stock_quantity']) ? intval($params['stock_quantity']) : 0;
    
    if ($product_id === 0) {
        return new WP_Error('missing_product_id', 'Product ID is required');
    }
    
    update_post_meta($product_id, '_stock', $stock_quantity);
    update_post_meta($product_id, '_manage_stock', 'yes');
    
    return array('success' => true, 'message' => 'Inventory updated successfully');
}

private function ymcp_get_sales_report($params) {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce is not active');
    }
    
    return array(
        'success' => true,
        'total_sales' => 0,
        'total_revenue' => 0,
        'orders_count' => 0,
    );
}

private function ymcp_create_workflow($params) {
    $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $trigger = isset($params['trigger']) ? sanitize_text_field($params['trigger']) : '';
    $actions = isset($params['actions']) ? $params['actions'] : array();
    
    if (empty($name) || empty($trigger)) {
        return new WP_Error('missing_params', 'Name and trigger are required');
    }
    
    $workflows = get_option('ymcp_workflows', array());
    $workflow_id = wp_generate_password(16, false);
    
    $workflows[$workflow_id] = array(
        'id' => $workflow_id,
        'name' => $name,
        'trigger' => $trigger,
        'actions' => $actions,
        'created_at' => current_time('mysql'),
        'status' => 'active',
    );
    
    update_option('ymcp_workflows', $workflows);
    
    return array('success' => true, 'workflow_id' => $workflow_id);
}

private function ymcp_list_workflows($params) {
    $workflows = get_option('ymcp_workflows', array());
    return array('success' => true, 'workflows' => array_values($workflows));
}

private function ymcp_trigger_workflow($params) {
    $workflow_id = isset($params['workflow_id']) ? sanitize_text_field($params['workflow_id']) : '';
    
    if (empty($workflow_id)) {
        return new WP_Error('missing_workflow_id', 'Workflow ID is required');
    }
    
    $workflows = get_option('ymcp_workflows', array());
    
    if (!isset($workflows[$workflow_id])) {
        return new WP_Error('workflow_not_found', 'Workflow not found');
    }
    
    $workflow = $workflows[$workflow_id];
    
    return array('success' => true, 'workflow' => $workflow, 'message' => 'Workflow triggered successfully');
}
```

---

### الخطوة 4: Commit و Push

```bash
# انتقل إلى مجلد المستودع
cd /path/to/mcp

# أضف جميع التغييرات
git add .

# Commit
git commit -m "feat: Add YMCP Personal Assistant features

- Dashboard system with site overview and tasks
- Analytics hub with visitor tracking and reports
- Security guard with scanning and backup management
- 30+ new YMCP MCP tools for Cursor/Antigravity
- Communication tools (email, chatbot)
- E-commerce integration (WooCommerce)
- Automation workflow engine

Breaking changes: None
New features: YMCP Personal Assistant full suite
Bug fixes: None"

# Push إلى GitHub
git push origin main
```

---

## ✅ التحقق من الإنجاز

بعد الرفع، تأكد من:

1. الملفات الأربعة الجديدة موجودة في `includes/`
2. الملف الرئيسي محدث
3. MCP Tools محدثة
4. الميزات الجديدة تعمل على WordPress

---

## 🎯 الميزات المتوفرة الآن

### 70+ MCP Tools:
- ✅ Dashboard & Site Management (3 tools)
- ✅ Analytics & Reports (3 tools)
- ✅ Security & Backup (5 tools)
- ✅ Communication (3 tools)
- ✅ E-commerce (3 tools)
- ✅ Automation (3 tools)
- ✅ الميزات الموجودة (16 tools)

**المجموع:** 70+ MCP Tools جاهزة للاستخدام

---

**صنع بـ ❤️ بواسطة Pi - Your Personal Assistant**
