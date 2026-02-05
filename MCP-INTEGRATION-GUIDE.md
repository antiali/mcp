# دليل التكامل مع MCP - MCP Integration Guide

## 🎯 نظرة عامة

الإضافة تدعم التصميم الكامل عن بُعد عبر MCP Protocol مع دعم كامل للروابط والصور والملفات المرجعية.

---

## 🔌 MCP Tools المتاحة

### 1. `awbu_remote_design` - التصميم عن بُعد الكامل

**الوصف**: تصميم موقع كامل عن بُعد مع دعم المراجع

**المعاملات**:
```json
{
  "description": "Create a modern business website",
  "references": [
    {
      "type": "url",
      "url": "https://example.com/reference-site",
      "title": "Reference Site",
      "description": "Use this as inspiration"
    },
    {
      "type": "image",
      "url": "https://example.com/image.jpg",
      "alt": "Design inspiration",
      "caption": "Color scheme reference"
    },
    {
      "type": "file",
      "url": "https://example.com/brand-guidelines.pdf"
    },
    {
      "type": "text",
      "title": "Brand Guidelines",
      "content": "Use blue (#0066CC) as primary color..."
    }
  ],
  "model": "deepseek",
  "mode": "full_site"
}
```

**الاستجابة**:
```json
{
  "success": true,
  "code": "...",
  "design_info": {
    "colors": ["#0066CC", "#FFFFFF"],
    "style": {...}
  }
}
```

---

### 2. `awbu_process_references` - معالجة المراجع

**الوصف**: معالجة المراجع واستخراج معلومات التصميم

**المعاملات**:
```json
{
  "references": [
    {
      "type": "url",
      "url": "https://example.com"
    },
    {
      "type": "image",
      "url": "https://example.com/image.jpg"
    }
  ]
}
```

**الاستجابة**:
```json
{
  "success": true,
  "processed_references": {
    "links": [...],
    "images": [...],
    "files": [...]
  },
  "design_info": {
    "colors": [...],
    "fonts": [...],
    "style": {...}
  }
}
```

---

### 3. `awbu_generate_with_references` - التوليد مع المراجع

**الوصف**: توليد محتوى بالـ AI مع استخدام المراجع

**المعاملات**: نفس `awbu_remote_design`

---

### 4. `awbu_get_design_system` - الحصول على نظام التصميم

**الوصف**: الحصول على الألوان والمتغيرات الحالية

**الاستجابة**:
```json
{
  "success": true,
  "builder": "divi5",
  "colors": {
    "gcid-primary": {
      "color": "#0066CC",
      "name": "Primary"
    }
  },
  "variables": {...}
}
```

---

### 5. `awbu_update_design_system` - تحديث نظام التصميم

**الوصف**: تحديث الألوان والمتغيرات

**المعاملات**:
```json
{
  "colors": {
    "gcid-primary": {
      "color": "#0066CC",
      "name": "Primary Color"
    }
  },
  "variables": {
    "gvid-space-4": {
      "value": "16",
      "unit": "px"
    }
  }
}
```

---

## 🚀 أمثلة الاستخدام

### مثال 1: تصميم موقع مع مرجع

```bash
curl -X POST https://yoursite.com/wp-json/mcp/v1/tools/awbu_remote_design \
  -H "X-MCP-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Create a modern e-commerce website",
    "references": [
      {
        "type": "url",
        "url": "https://shopify.com",
        "title": "Shopify Reference"
      },
      {
        "type": "image",
        "url": "https://example.com/design.jpg",
        "alt": "Design inspiration"
      }
    ],
    "model": "deepseek"
  }'
```

### مثال 2: معالجة المراجع فقط

```bash
curl -X POST https://yoursite.com/wp-json/mcp/v1/tools/awbu_process_references \
  -H "X-MCP-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "references": [
      {
        "type": "url",
        "url": "https://example.com"
      }
    ]
  }'
```

---

## 🔗 التكامل مع Cursor / Antigravity

### استخدام Cursor:

1. **إضافة MCP Server**:
```json
{
  "mcpServers": {
    "awbu": {
      "url": "https://yoursite.com/wp-json/mcp/v1",
      "apiKey": "YOUR_API_KEY"
    }
  }
}
```

2. **استخدام في Cursor**:
```
Design a website with these references:
- URL: https://example.com
- Image: https://example.com/image.jpg

Use awbu_remote_design tool
```

### استخدام Antigravity:

مشابه لـ Cursor - استخدم نفس MCP endpoints

---

## 📊 تدفق العمل

```
1. إرسال طلب تصميم مع المراجع
   ↓
2. معالجة المراجع (تحميل الصور/الملفات)
   ↓
3. استخراج معلومات التصميم
   ↓
4. بناء Prompt محسّن
   ↓
5. توليد بالـ AI
   ↓
6. تطبيق نظام التصميم
   ↓
7. إرجاع النتيجة
```

---

## ✅ الميزات

- ✅ دعم الروابط (URLs)
- ✅ دعم الصور (Images) - تحميل تلقائي
- ✅ دعم الملفات (Files) - تحميل تلقائي
- ✅ دعم النصوص المرجعية
- ✅ استخراج معلومات التصميم تلقائياً
- ✅ تكامل كامل مع AI Models
- ✅ تطبيق نظام التصميم تلقائياً

---

**الحالة**: ✅ جاهز للاستخدام

