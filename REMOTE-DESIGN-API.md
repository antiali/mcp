# 🚀 AI Website Builder - Remote Design Documentation

## Overview

This documentation covers how to use the AI Website Builder Unified plugin for **remote website design** using references (URLs, images, files) through any AI model or editor.

---

## 🔧 Available MCP Tools

### Design System Tools

| Tool | Description | Required Parameters |
|------|-------------|---------------------|
| `awbu_get_design_system` | Get current design system (colors, variables) | None |
| `awbu_update_design_system` | Update design system | `colors`, `variables` (objects) |
| `awbu_sync_design_system` | Sync design system to all locations | None |

### Remote Design Tools

| Tool | Description | Required Parameters |
|------|-------------|---------------------|
| `awbu_remote_design` | Design with references | `description` (string) |
| `awbu_process_references` | Process references only | `references` (array) |
| `awbu_analyze_reference_url` | Analyze URL for design info | `url` (string) |
| `awbu_extract_colors_from_image` | Extract colors from image | `image_url` (string) |
| `awbu_design_full_website` | Design complete website | `site_name`, `site_description` |
| `awbu_apply_design_from_reference` | Apply design from URL | `reference_url` (string) |

### AI & Page Tools

| Tool | Description | Required Parameters |
|------|-------------|---------------------|
| `awbu_generate_with_references` | Generate content with AI | `description` (string) |
| `awbu_create_page_with_design` | Create page with design system | `title`, `content` |
| `awbu_get_available_models` | List available AI models | None |

---

## 📋 Reference Types

### 1. URL References
```json
{
  "type": "url",
  "url": "https://example.com",
  "title": "Reference Site",
  "description": "Modern corporate design"
}
```

### 2. Image References
```json
{
  "type": "image",
  "url": "https://example.com/logo.png",
  "alt": "Company logo",
  "caption": "Brand colors reference"
}
```

### 3. File References
```json
{
  "type": "file",
  "url": "https://example.com/brand-guide.pdf"
}
```

### 4. Text References
```json
{
  "type": "text",
  "title": "Brand Guidelines",
  "content": "Primary color: #2563eb, Font: Inter..."
}
```

---

## 🎨 Usage Examples

### Example 1: Design from Reference URL

```bash
curl -X POST "https://your-site.com/wp-json/awbu/v1/tools/call" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "awbu_remote_design",
    "params": {
      "description": "Create a homepage inspired by this design",
      "references": [
        {
          "type": "url",
          "url": "https://stripe.com",
          "description": "Modern fintech design"
        }
      ],
      "model": "deepseek"
    }
  }'
```

### Example 2: Extract Colors from Logo

```bash
curl -X POST "https://your-site.com/wp-json/awbu/v1/tools/call" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "awbu_extract_colors_from_image",
    "params": {
      "image_url": "https://example.com/logo.png",
      "num_colors": 5
    }
  }'
```

### Example 3: Full Website Design

```bash
curl -X POST "https://your-site.com/wp-json/awbu/v1/tools/call" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "awbu_design_full_website",
    "params": {
      "site_name": "Acme Corporation",
      "site_description": "A modern tech company website with dark theme",
      "references": [
        {
          "type": "url",
          "url": "https://vercel.com",
          "description": "Dark modern tech aesthetic"
        },
        {
          "type": "image",
          "url": "https://example.com/brand-colors.png"
        }
      ],
      "pages": ["home", "about", "services", "contact"],
      "model": "gpt-4o"
    }
  }'
```

---

## 🤖 Integration with AI Editors

### Cursor / Antigravity Integration

These editors can use MCP tools directly. Example workflow:

1. **Get current design system**:
   ```
   Call: awbu_get_design_system
   ```

2. **Analyze reference site**:
   ```
   Call: awbu_analyze_reference_url
   Params: { "url": "https://reference-site.com" }
   ```

3. **Design with references**:
   ```
   Call: awbu_remote_design
   Params: {
     "description": "Create a modern homepage",
     "references": [...]
   }
   ```

4. **Create page with design**:
   ```
   Call: awbu_create_page_with_design
   Params: {
     "title": "Home",
     "content": "<generated content>"
   }
   ```

---

## 🎛️ Global Variables Syntax

### Colors (Divi 5)
```
$variable({gcid:color-id})$
```

### Number Variables
```
$variable({vid:variable-id})$
```

### Examples
```css
/* Color */
background-color: $variable({gcid:primary-color})$;

/* Spacing */
padding: $variable({vid:spacing-md})$;
```

---

## 📡 REST API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/awbu/v1/design-system` | GET | Get design system |
| `/wp-json/awbu/v1/design-system` | POST | Update design system |
| `/wp-json/awbu/v1/remote-design` | POST | Process remote design |
| `/wp-json/awbu/v1/tools/list` | GET | List available tools |
| `/wp-json/awbu/v1/tools/call` | POST | Execute a tool |

---

## ✅ Best Practices

1. **Always provide context** in your description
2. **Include multiple references** for better results
3. **Use image references** for color extraction
4. **Specify the AI model** based on task complexity
5. **Review and sync** design system after changes

---

## 🔒 Authentication

All endpoints require:
- **WordPress authentication** (logged in as admin)
- **X-WP-Nonce header** for write operations
- **API Key** for external access (via X-MCP-API-Key header)
