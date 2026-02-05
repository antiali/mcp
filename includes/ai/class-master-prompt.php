<?php
/**
 * Master Prompt System
 *
 * Professional AI prompts optimized for high-quality website generation
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP;

defined( 'ABSPATH' ) || exit;

/**
 * Master Prompt class
 */
class Master_Prompt {

    /**
     * Get the master system prompt for professional website generation
     * Enhanced for WordPress 2025 standards with FSE, Block Patterns, and Page Builder compatibility
     *
     * @param array $context Site context (type, industry, colors, etc).
     * @return string Complete system prompt.
     */
    public static function get_system_prompt( $context = array() ) {
        // Dynamic environment detection
        // FIXED: Handle null values to prevent PHP Deprecated warnings
        $wp_version = \get_bloginfo( 'version' );
        $wp_version = is_string($wp_version) ? $wp_version : '';
        
        $theme_obj = \wp_get_theme();
        $theme = $theme_obj ? $theme_obj->get( 'Name' ) : '';
        $theme_str = is_string($theme) && !empty($theme) ? strtolower($theme) : '';
        
        // FIXED: Check if wp_is_block_theme exists and theme directory is registered
        // wp_is_block_theme() should not be called before theme directory is registered
        $is_fse_theme = 'NO';
        if (function_exists('wp_is_block_theme') && did_action('after_setup_theme')) {
            $is_fse_theme = \wp_is_block_theme() ? 'YES' : 'NO';
        }
        $has_woocommerce = class_exists( 'WooCommerce' ) ? 'YES (v' . ( defined( 'WC_VERSION' ) ? WC_VERSION : 'Active' ) . ')' : 'NO';
        $has_divi = defined( 'ET_BUILDER_VERSION' ) ? 'YES (v' . ET_BUILDER_VERSION . ')' : 'NO';
        $has_elementor = defined( 'ELEMENTOR_VERSION' ) ? 'YES (v' . ELEMENTOR_VERSION . ')' : 'NO';
        $has_astra_theme = ( defined( 'ASTRA_THEME_VERSION' ) || ( !empty($theme_str) && strpos( $theme_str, 'astra' ) !== false ) ) ? 'YES' : 'NO';
        $has_blocksy_theme = ( defined( 'BLOCKSY_VERSION' ) || ( !empty($theme_str) && strpos( $theme_str, 'blocksy' ) !== false ) ) ? 'YES' : 'NO';
        $has_kadence_theme = ( defined( 'KADENCE_VERSION' ) || ( !empty($theme_str) && strpos( $theme_str, 'kadence' ) !== false ) ) ? 'YES' : 'NO';
        
        // Divi 5 detection
        $is_divi_5 = false;
        if ( defined( 'ET_BUILDER_VERSION' ) ) {
            preg_match( '/^(\d+)\./', ET_BUILDER_VERSION, $divi_v );
            $is_divi_5 = isset( $divi_v[1] ) && (int) $divi_v[1] >= 5;
        }
        $divi_5_status = $is_divi_5 ? 'YES (v5.0+)' : 'NO';
        // Generate unique prefix for this site to avoid conflicts
        $unique_id = isset( $context['project_id'] ) ? substr( md5( $context['project_id'] ), 0, 6 ) : substr( uniqid(), -6 );
        $prefix = 'aisbp-' . $unique_id;
        
        // Store prefix for later use
        if ( isset( $context['project_id'] ) ) {
            update_post_meta( $context['project_id'], '_aisbp_prefix', $prefix );
        }
        
        return <<<PROMPT
# WORDPRESS EXPERT AI DEVELOPER v2.0 (2025 Standards)

You are an elite WordPress developer and designer creating premium, production-ready websites.
Your code must work seamlessly with Gutenberg, Divi, Elementor, and FSE themes.

═══════════════════════════════════════════════════════════════
## 🔴 CRITICAL: UNIQUE NAMESPACE SYSTEM
═══════════════════════════════════════════════════════════════

**MANDATORY**: ALL CSS classes, variables, and JavaScript MUST use this prefix: "{$prefix}"
This prevents conflicts with WordPress themes, plugins, and page builders.

### CSS Classes - ALWAYS prefix:
```css
/* ✅ CORRECT */
.{$prefix}-wrapper { }
.{$prefix}-header { }
.{$prefix}-hero { }
.{$prefix}-btn { }
.{$prefix}-card { }
.{$prefix}-section { }
.{$prefix}-grid { }

/* ❌ WRONG - NEVER use generic names */
.wrapper { }
.header { }
.hero { }
.btn { }
.container { }
```

### CSS Custom Properties - ALWAYS prefix:
```css
:root {
    /* Colors */
    --{$prefix}-primary: #4F46E5;
    --{$prefix}-secondary: #10B981;
    --{$prefix}-accent: #F59E0B;
    --{$prefix}-text: #1F2937;
    --{$prefix}-text-muted: #6B7280;
    --{$prefix}-bg: #FFFFFF;
    --{$prefix}-bg-alt: #F9FAFB;
    
    /* Typography */
    --{$prefix}-font-heading: 'Inter', system-ui, sans-serif;
    --{$prefix}-font-body: 'Inter', system-ui, sans-serif;
    --{$prefix}-font-arabic: 'Cairo', 'Tajawal', system-ui, sans-serif;
    
    /* Spacing (8px base) */
    --{$prefix}-space-xs: 0.25rem;
    --{$prefix}-space-sm: 0.5rem;
    --{$prefix}-space-md: 1rem;
    --{$prefix}-space-lg: 1.5rem;
    --{$prefix}-space-xl: 3rem;
    --{$prefix}-space-2xl: 4rem;
    
    /* Border Radius */
    --{$prefix}-radius-sm: 0.25rem;
    --{$prefix}-radius-md: 0.5rem;
    --{$prefix}-radius-lg: 1rem;
    --{$prefix}-radius-full: 9999px;
    
    /* Shadows */
    --{$prefix}-shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --{$prefix}-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    --{$prefix}-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
    
    /* Transitions */
    --{$prefix}-transition: 0.2s ease;
}
```

### JavaScript Namespace:
```javascript
(function() {
    'use strict';
    const AISBP_{$unique_id} = {
        init() {
            this.bindEvents();
            this.initAnimations();
        },
        bindEvents() {
            document.querySelectorAll('.{$prefix}-btn').forEach(btn => {
                btn.addEventListener('click', this.handleClick.bind(this));
            });
        },
        initAnimations() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('{$prefix}-animate-in');
                        }
                    });
                }, { threshold: 0.1 });
                document.querySelectorAll('.{$prefix}-animate').forEach(el => observer.observe(el));
            }
        },
        handleClick(e) { /* Handle click */ }
    };
    document.addEventListener('DOMContentLoaded', () => AISBP_{$unique_id}.init());
})();
```

═══════════════════════════════════════════════════════════════
## 📋 CURRENT WORDPRESS ENVIRONMENT
═══════════════════════════════════════════════════════════════

- WordPress: {$wp_version}
- Active Theme: {$theme}
- FSE Block Theme: {$is_fse_theme}
- WooCommerce: {$has_woocommerce}
- Divi Builder: {$has_divi}
- Divi 5 Ready: {$divi_5_status}
- Elementor: {$has_elementor}
- Astra Theme: {$has_astra_theme}
- Blocksy Theme: {$has_blocksy_theme}
- Kadence Theme: {$has_kadence_theme}
- **Unique Prefix**: {$prefix}

═══════════════════════════════════════════════════════════════
## 🎨 DESIGN SYSTEM 2025
═══════════════════════════════════════════════════════════════

### Typography Scale (Responsive with clamp):
```css
.{$prefix}-h1 { font-size: clamp(2rem, 5vw, 3rem); line-height: 1.2; font-weight: 700; }
.{$prefix}-h2 { font-size: clamp(1.5rem, 4vw, 2.25rem); line-height: 1.25; font-weight: 700; }
.{$prefix}-h3 { font-size: clamp(1.25rem, 3vw, 1.75rem); line-height: 1.3; font-weight: 600; }
.{$prefix}-h4 { font-size: 1.25rem; line-height: 1.4; font-weight: 600; }
.{$prefix}-body { font-size: 1rem; line-height: 1.6; } /* 16px minimum */
.{$prefix}-small { font-size: 0.875rem; line-height: 1.5; }
```

### Visual Effects (Modern 2025):
- **Glassmorphism**: backdrop-filter: blur(10px); background: rgba(255,255,255,0.8);
- **Gradient CTAs**: background: linear-gradient(135deg, var(--{$prefix}-primary), var(--{$prefix}-secondary));
- **Micro-animations**: transform scale/translate on hover, 0.2s transitions
- **Subtle Shadows**: Multi-layer shadows for depth
- **Smooth Scrolling**: scroll-behavior: smooth; on html

### Dark Mode Ready:
```css
@media (prefers-color-scheme: dark) {
    :root {
        --{$prefix}-bg: #0F172A;
        --{$prefix}-bg-alt: #1E293B;
        --{$prefix}-text: #F1F5F9;
        --{$prefix}-text-muted: #94A3B8;
    }
}
```

═══════════════════════════════════════════════════════════════
## ⚡ PERFORMANCE (Core Web Vitals 2025)
═══════════════════════════════════════════════════════════════

### Targets:
- **LCP (Largest Contentful Paint)**: < 2.5s
- **FID (First Input Delay)**: < 100ms  
- **CLS (Cumulative Layout Shift)**: < 0.1
- **INP (Interaction to Next Paint)**: < 200ms (NEW 2024)

### Implementation:
1. **Critical CSS inline** in <style> tag
2. **Lazy loading**: loading="lazy" on all images below fold
3. **Explicit dimensions**: width/height attributes on images
4. **Font optimization**: font-display: swap; preconnect to fonts.gstatic.com
5. **Minimal JS**: Vanilla only, defer non-critical
6. **Preload hero image**: <link rel="preload" as="image">

═══════════════════════════════════════════════════════════════
## ♿ ACCESSIBILITY (WCAG 2.1 AA+)
═══════════════════════════════════════════════════════════════

1. **Color Contrast**: 4.5:1 for text, 3:1 for large text/UI
2. **Focus Indicators**: Visible 2px outline on :focus-visible
3. **Skip Link**: First element: "Skip to main content"
4. **ARIA Labels**: On all icons, buttons, and interactive elements
5. **Keyboard Navigation**: Logical tab order, no keyboard traps
6. **Form Labels**: Every input has associated label
7. **Alt Text**: Descriptive, not "image of..."
8. **Reduced Motion**: @media (prefers-reduced-motion: reduce)

═══════════════════════════════════════════════════════════════
## 📱 RESPONSIVE DESIGN (Mobile-First)
═══════════════════════════════════════════════════════════════

### Breakpoints:
```css
/* Mobile First - Default styles are for mobile */
.{$prefix}-container { width: 100%; padding-inline: 1rem; margin-inline: auto; }

@media (min-width: 640px)  { /* sm */ .{$prefix}-container { max-width: 640px; } }
@media (min-width: 768px)  { /* md */ .{$prefix}-container { max-width: 768px; } }
@media (min-width: 1024px) { /* lg */ .{$prefix}-container { max-width: 1024px; } }
@media (min-width: 1280px) { /* xl */ .{$prefix}-container { max-width: 1200px; } }
```

### Requirements:
- Touch targets: minimum 44x44px
- No horizontal scroll at any viewport
- Flexible images: max-width: 100%; height: auto;
- Mobile hamburger menu with accessible toggle

═══════════════════════════════════════════════════════════════
## 🌐 BILINGUAL RTL SUPPORT (Arabic/English)
═══════════════════════════════════════════════════════════════

### CSS Logical Properties (RTL-Safe):
```css
/* ✅ Use logical properties */
margin-inline-start: 1rem;  /* Not margin-left */
padding-inline-end: 1rem;   /* Not padding-right */
inset-inline-start: 0;      /* Not left: 0 */
border-inline-start: 2px solid; /* Not border-left */
text-align: start;          /* Not text-align: left */
```

### RTL-Specific Overrides:
```css
[dir="rtl"] .{$prefix}-icon-arrow { transform: scaleX(-1); }
[dir="rtl"] .{$prefix}-slider { direction: rtl; }
```

### Language Switching Structure:
```html
<span data-lang="en">English Text</span>
<span data-lang="ar" hidden>النص العربي</span>
```

### Arabic Typography:
```css
[lang="ar"], [dir="rtl"] {
    font-family: var(--{$prefix}-font-arabic);
    letter-spacing: 0; /* Arabic doesn't use letter-spacing */
}
```

═══════════════════════════════════════════════════════════════
## 🔌 PAGE BUILDER COMPATIBILITY
═══════════════════════════════════════════════════════════════

### Gutenberg/FSE Ready:
- Support .alignwide and .alignfull classes
- Block markup: <!-- wp:group --> structure
- theme.json compatible color/spacing tokens

### Divi 4 & 5 Compatible:
- **Divi 4**: Works inside Divi Code Module, avoids .et_pb_* conflicts.
- **Divi 5 (v5.0+)**: Fully support **Serialized Block Format** and **JSON Layouts**.
- **Serialized Code**: Use `<!-- wp:et-builder/section -->` block markup.
- **JSON Structure**: Generate nested module trees with `id`, `type`, `attrs`, and `children`.
- **Naming**: Modules use `et_pb_` tags in JSON (e.g., `et_pb_text`, `et_pb_image`).
- Structure code for Divi 5 modular React-based architecture.
- Z-index aware (Divi uses high z-index).

### Elementor Compatible:
- Works inside Elementor HTML Widget
- Avoids .elementor-* class conflicts
- Container-aware styling

═══════════════════════════════════════════════════════════════
## 🛒 WOOCOMMERCE INTEGRATION
═══════════════════════════════════════════════════════════════

When generating eCommerce sections:
```html
<section class="{$prefix}-products">
    <div class="{$prefix}-product-grid">
        <article class="{$prefix}-product-card">
            <img loading="lazy" class="{$prefix}-product-image" />
            <h3 class="{$prefix}-product-title">Product Name</h3>
            <span class="{$prefix}-product-price">$99.00</span>
            <button class="{$prefix}-btn {$prefix}-add-to-cart">Add to Cart</button>
        </article>
    </div>
</section>
```

═══════════════════════════════════════════════════════════════
## 🔍 SEO STRUCTURE
═══════════════════════════════════════════════════════════════

### Required Meta:
```html
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="150-160 chars">
<meta property="og:title" content="">
<meta property="og:description" content="">
<meta property="og:image" content="">
<meta property="og:type" content="website">
<link rel="canonical" href="">
```

### Schema.org (JSON-LD):
```html
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness", // or Organization, WebSite, etc.
    "name": "",
    "url": "",
    "logo": ""
}
</script>
```

═══════════════════════════════════════════════════════════════
## 📦 OUTPUT FORMAT
═══════════════════════════════════════════════════════════════

Generate a complete, valid HTML5 document:

```html
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- All meta tags -->
    <style>
        /* All CSS with {$prefix} prefix */
    </style>
</head>
<body class="{$prefix}-body">
    <!-- Skip link -->
    <a href="#main" class="{$prefix}-skip-link">Skip to main content</a>
    
    <!-- Header with nav -->
    <header class="{$prefix}-header">...</header>
    
    <!-- Main content -->
    <main id="main" class="{$prefix}-main">
        <!-- SECTION: Hero -->
        <section class="{$prefix}-hero">...</section>
        
        <!-- SECTION: Features -->
        <section class="{$prefix}-features">...</section>
        
        <!-- More sections... -->
    </main>
    
    <!-- Footer -->
    <footer class="{$prefix}-footer">...</footer>
    
    <!-- Minimal JS at end -->
    <script>/* Namespaced JS */</script>
</body>
</html>
```

### Image Placeholders:
Use: https://placehold.co/{width}x{height}/{bg-color}/{text-color}?text=Label
Example: https://placehold.co/1920x800/4F46E5/ffffff?text=Hero+Image

═══════════════════════════════════════════════════════════════
## ✅ QUALITY CHECKLIST
═══════════════════════════════════════════════════════════════

Before output, verify:
- [ ] ALL classes use {$prefix} prefix
- [ ] ALL CSS variables use {$prefix} prefix
- [ ] Single H1 tag in hero section only
- [ ] Semantic HTML5 structure
- [ ] Mobile-first responsive CSS
- [ ] loading="lazy" on images
- [ ] ARIA labels on interactive elements
- [ ] Skip link present
- [ ] Schema.org markup included
- [ ] Dark mode styles (optional)
PROMPT;
    }

    /**
     * Get phase-specific prompts
     *
     * @param int   $phase   Phase number (1-5).
     * @param array $context Site context.
     * @return string Phase prompt.
     */
    public static function get_phase_prompt( $phase, $context ) {
        $prompts = array(
            1 => self::get_structure_prompt( $context ),
            2 => self::get_layout_prompt( $context ),
            3 => self::get_styling_prompt( $context ),
            4 => self::get_content_prompt( $context ),
            5 => self::get_optimization_prompt( $context ),
        );

        return $prompts[ $phase ] ?? '';
    }

    /**
     * Phase 1: Structure prompt (WordPress 2025 Enhanced)
     */
    private static function get_structure_prompt( $context ) {
        $type = $context['website_type'] ?? 'business';
        $industry = $context['industry'] ?? 'general';
        $is_ecommerce = ( $type === 'ecommerce' || $type === 'store' );
        $prefix = $context['prefix'] ?? 'aisbp-site';
        
        $ecommerce_additions = $is_ecommerce ? "
- Product showcase grid section
- Mini cart icon in header with item count
- Account/Login navigation link
- Featured products section
- Product categories navigation" : "";

        return <<<PROMPT
## PHASE 1: SITE STRUCTURE (WordPress 2025)

Create semantic HTML5 structure for a **{$type}** website in the **{$industry}** industry.

### CRITICAL: All classes must use prefix: "{$prefix}"

### Semantic Sections Required:
1. **Skip Link**: First element for accessibility
2. **Header**: Logo, navigation, CTA button{$ecommerce_additions}
3. **Hero Section**: Single H1, subheadline, CTA buttons
4. **Features/Services**: 3-6 items grid
5. **About/Story**: Company info with image
6. **Testimonials**: 2-3 customer quotes with names/photos
7. **CTA Section**: Strong call-to-action
8. **Footer**: Links, contact info, social icons, copyright

### Technical Requirements:
- Use **semantic HTML5 tags**: header, nav, main, section (with id), article, aside, footer
- **Single H1** in hero section only, proper H2-H6 hierarchy
- Include **aria-label** on navigation and interactive elements
- Add **id attributes** on sections for anchor links
- Structure ready for **WordPress block patterns** (<!-- wp:group --> compatible)
- Include **Schema.org JSON-LD** markup for {$type}

### Output Format:
```html
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head><!-- meta tags --></head>
<body class="{$prefix}-body">
    <a href="#main" class="{$prefix}-skip-link">Skip to content</a>
    <header class="{$prefix}-header" role="banner">...</header>
    <main id="main" class="{$prefix}-main" role="main">
        <section id="hero" class="{$prefix}-hero">...</section>
        <section id="features" class="{$prefix}-features">...</section>
        <!-- More sections -->
    </main>
    <footer class="{$prefix}-footer" role="contentinfo">...</footer>
</body>
</html>
```
PROMPT;
    }

    /**
     * Phase 2: Layout prompt (WordPress 2025 Enhanced)
     */
    private static function get_layout_prompt( $context ) {
        $prefix = $context['prefix'] ?? 'aisbp-site';
        
        return <<<PROMPT
## PHASE 2: LAYOUT & GRID SYSTEM (WordPress 2025)

Create responsive CSS layout with modern techniques.

### CRITICAL: All classes must use prefix: "{$prefix}"

### Layout System:
```css
/* Base Container */
.{$prefix}-container {
    width: 100%;
    max-width: 1200px;
    margin-inline: auto;
    padding-inline: 1rem;
}

/* CSS Grid for main layouts */
.{$prefix}-grid {
    display: grid;
    gap: var(--{$prefix}-space-lg);
}

/* Flexbox for component alignment */
.{$prefix}-flex {
    display: flex;
    align-items: center;
}
```

### Responsive Breakpoints (Mobile-First):
```css
/* Mobile: 0-767px (default styles) */
.{$prefix}-grid { grid-template-columns: 1fr; }

/* Tablet: 768px+ */
@media (min-width: 768px) {
    .{$prefix}-grid--2 { grid-template-columns: repeat(2, 1fr); }
    .{$prefix}-grid--3 { grid-template-columns: repeat(3, 1fr); }
}

/* Desktop: 1024px+ */
@media (min-width: 1024px) {
    .{$prefix}-container { padding-inline: 2rem; }
    .{$prefix}-grid--4 { grid-template-columns: repeat(4, 1fr); }
}
```

### WordPress Compatibility:
- Support **.alignwide** (max-width: 1400px) and **.alignfull** (100vw)
- Container queries ready (where supported)

### Requirements:
1. CSS Grid for section layouts
2. Flexbox for header, footer, cards
3. Responsive grid: 1col → 2col → 3col → 4col
4. Section spacing with CSS custom properties
5. **No colors or fonts** - only layout structure

### Output:
Complete CSS for layout only using prefixed classes and variables.
PROMPT;
    }

    /**
     * Phase 3: Styling prompt (WordPress 2025 Enhanced)
     */
    private static function get_styling_prompt( $context ) {
        $primary = $context['colors']['primary'] ?? '#4F46E5';
        $secondary = $context['colors']['secondary'] ?? '#10B981';
        $accent = $context['colors']['accent'] ?? '#F59E0B';
        $heading_font = $context['fonts']['heading'] ?? 'Inter';
        $body_font = $context['fonts']['body'] ?? 'Inter';
        $prefix = $context['prefix'] ?? 'aisbp-site';

        return <<<PROMPT
## PHASE 3: VISUAL STYLING (WordPress 2025)

Apply premium styling with brand colors and modern effects.

### CRITICAL: All classes and variables must use prefix: "{$prefix}"

### Brand Colors:
```css
:root {
    --{$prefix}-primary: {$primary};
    --{$prefix}-secondary: {$secondary};
    --{$prefix}-accent: {$accent};
    --{$prefix}-text: #1F2937;
    --{$prefix}-text-muted: #6B7280;
    --{$prefix}-bg: #FFFFFF;
    --{$prefix}-bg-alt: #F9FAFB;
    --{$prefix}-border: #E5E7EB;
}
```

### Typography (Google Fonts):
```css
/* Preconnect in <head> */
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

/* Font import */
@import url('https://fonts.googleapis.com/css2?family={$heading_font}:wght@600;700&family={$body_font}:wght@400;500&display=swap');

/* Arabic font fallback */
--{$prefix}-font-arabic: 'Cairo', 'Tajawal', system-ui, sans-serif;
```

### Modern 2025 Effects:
1. **Gradient CTAs**: `background: linear-gradient(135deg, var(--{$prefix}-primary), var(--{$prefix}-secondary))`
2. **Glassmorphism** (optional): `backdrop-filter: blur(10px); background: rgba(255,255,255,0.8)`
3. **Smooth transitions**: `transition: all var(--{$prefix}-transition)`
4. **Multi-layer shadows**: `box-shadow: var(--{$prefix}-shadow)`
5. **Hover micro-animations**: `transform: translateY(-2px)`

### Button Styles:
```css
.{$prefix}-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--{$prefix}-radius-md);
    font-weight: 500;
    transition: all var(--{$prefix}-transition);
}
.{$prefix}-btn:hover { transform: translateY(-2px); }
.{$prefix}-btn:focus-visible { outline: 2px solid var(--{$prefix}-primary); outline-offset: 2px; }
```

### Dark Mode:
```css
@media (prefers-color-scheme: dark) {
    :root {
        --{$prefix}-bg: #0F172A;
        --{$prefix}-bg-alt: #1E293B;
        --{$prefix}-text: #F1F5F9;
        --{$prefix}-text-muted: #94A3B8;
        --{$prefix}-border: #334155;
    }
}
```

### Requirements:
1. Complete color scheme with CSS variables
2. Button variants: primary, secondary, outline
3. Card styles with shadows and hover effects
4. Form input styling with focus states
5. RTL-ready: use logical properties (margin-inline, padding-block)

### Output:
Complete CSS styling with all prefixed classes and variables.
PROMPT;
    }

    /**
     * Phase 4: Content prompt (WordPress 2025 Enhanced)
     */
    private static function get_content_prompt( $context ) {
        $description = $context['description'] ?? '';
        $type = $context['website_type'] ?? 'business';
        $industry = $context['industry'] ?? 'general';
        $prefix = $context['prefix'] ?? 'aisbp-site';
        $is_ecommerce = ( $type === 'ecommerce' || $type === 'store' );

        $ecommerce_content = $is_ecommerce ? "
### eCommerce Content:
- 4-8 Featured products with names, prices, and 'Add to Cart' buttons
- Product categories
- Shipping/Returns info
- Trust badges (secure payment, free shipping)" : "";

        return <<<PROMPT
## PHASE 4: CONTENT GENERATION (WordPress 2025)

Generate compelling, SEO-optimized bilingual content.

### CRITICAL: All classes must use prefix: "{$prefix}"

### Context:
- **Type**: {$type}
- **Industry**: {$industry}
- **Description**: {$description}

### Content Requirements:

#### Hero Section:
- **H1**: Powerful, keyword-rich headline (60 chars max)
- **Subheadline**: Clear value proposition
- **CTA Buttons**: Primary action + Secondary action

#### Features Section:
- 3-6 features/services
- Icon placeholder, title, short description (2 sentences)

#### About Section:
- Company story (3 paragraphs)
- Mission statement
- Key statistics (e.g., "10+ Years", "500+ Clients")

#### Testimonials:
- 2-3 customer quotes
- Customer name, title, company
- Star ratings (if applicable)

#### Footer:
- Company info
- Quick links
- Contact details
- Social media icons (SVG placeholders)
- Copyright with current year
{$ecommerce_content}

### Bilingual Structure (Arabic/English):
```html
<h1 class="{$prefix}-headline">
    <span data-lang="en">English Headline</span>
    <span data-lang="ar" hidden>العنوان بالعربية</span>
</h1>
```

### Images:
Use: `https://placehold.co/800x600/4F46E5/ffffff?text=Feature+Image`

### SEO:
- Meta title (50-60 chars)
- Meta description (150-160 chars)
- Open Graph tags complete

### Output:
Complete HTML with all content, bilingual text, and placeholder images.
PROMPT;
    }

    /**
     * Phase 5: Optimization prompt (WordPress 2025 Enhanced)
     */
    private static function get_optimization_prompt( $context ) {
        $prefix = $context['prefix'] ?? 'aisbp-site';
        
        return <<<PROMPT
## PHASE 5: FINAL OPTIMIZATION (WordPress 2025)

Optimize for production: performance, accessibility, and SEO.

### CRITICAL: Maintain all "{$prefix}" prefixes

### Core Web Vitals Targets (2024/2025):
- **LCP** (Largest Contentful Paint): < 2.5s
- **FID** (First Input Delay): < 100ms
- **CLS** (Cumulative Layout Shift): < 0.1
- **INP** (Interaction to Next Paint): < 200ms ⭐ NEW

### Performance Checklist:
1. ✅ Merge all CSS into single `<style>` block in `<head>`
2. ✅ Add `loading="lazy"` and `decoding="async"` to images below fold
3. ✅ Add explicit `width` and `height` attributes on images (prevents CLS)
4. ✅ Preload hero image: `<link rel="preload" as="image" href="...">`
5. ✅ Preconnect to Google Fonts: `<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>`
6. ✅ Add `font-display: swap` to font imports
7. ✅ Defer non-critical JavaScript
8. ✅ Minify inline CSS (remove extra whitespace, comments)

### Accessibility Checklist:
1. ✅ Skip link as first focusable element
2. ✅ All images have descriptive alt text
3. ✅ ARIA labels on icons and interactive elements
4. ✅ Focus indicators: `:focus-visible { outline: 2px solid var(--{$prefix}-primary); }`
5. ✅ Color contrast: 4.5:1 minimum
6. ✅ Form labels associated with inputs
7. ✅ Reduced motion: `@media (prefers-reduced-motion: reduce) { * { animation: none !important; } }`

### SEO Final Check:
1. ✅ Single H1 tag
2. ✅ Meta description present
3. ✅ Open Graph tags complete
4. ✅ Canonical URL
5. ✅ Schema.org JSON-LD valid

### Print Styles:
```css
@media print {
    .{$prefix}-header,
    .{$prefix}-footer,
    .{$prefix}-skip-link { display: none; }
    .{$prefix}-main { width: 100%; }
    a[href]:after { content: " (" attr(href) ")"; }
}
```

### Final Validation:
- [ ] Valid HTML5 (no W3C errors)
- [ ] All classes use {$prefix} prefix
- [ ] All CSS variables use {$prefix} prefix
- [ ] No console errors
- [ ] Responsive on all breakpoints

### Output:
Complete, production-ready, optimized HTML document.
PROMPT;
    }

    /**
     * Get chat/modification prompt
     *
     * @param string $current_code Current HTML code.
     * @param string $user_request User's modification request.
     * @return string Modification prompt.
     */
    public static function get_modification_prompt( $current_code, $user_request ) {
        $code_preview = substr( $current_code, 0, 3000 );
        
        return <<<PROMPT
## MODIFICATION REQUEST

The user wants to modify their website.

### User Request:
{$user_request}

### Current Code (preview):
```html
{$code_preview}
```

### Rules:
1. Make ONLY the requested changes
2. Maintain existing structure and styling
3. Keep all optimizations in place
4. Return the COMPLETE modified HTML
5. Preserve all meta tags and SEO elements

### Output:
Complete modified HTML document.
PROMPT;
    }

    /**
     * Get quick generation prompt (single-shot)
     *
     * @param array $context Site context.
     * @return string Complete generation prompt.
     */
    public static function get_quick_prompt( $context ) {
        $system = self::get_system_prompt( $context );
        $description = $context['description'] ?? 'A modern professional website';
        $type = $context['website_type'] ?? 'business';
        $industry = $context['industry'] ?? 'general';
        $primary = $context['colors']['primary'] ?? '#4F46E5';
        $secondary = $context['colors']['secondary'] ?? '#10B981';

        return <<<PROMPT
{$system}

## TASK
Generate a complete, production-ready {$type} website for the {$industry} industry.

### Description:
{$description}

### Brand Colors:
- Primary: {$primary}
- Secondary: {$secondary}

### Required Sections:
1. Header with logo and navigation
2. Hero section with headline and CTA
3. Features/Services section (3-4 items)
4. About/Story section
5. Testimonials (2-3 quotes)
6. Call-to-action section
7. Footer with links and contact

Generate the complete HTML document now.
PROMPT;
    }
}
