<?php
/**
 * Cleanup and Create Pages Script - FIXED VERSION
 * Run this directly on WordPress server or via WP-CLI
 * 
 * Usage: php cleanup-and-create-pages-fixed.php
 */

// Load WordPress
if (!defined('ABSPATH')) {
    $wp_load_paths = array(
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../wp-load.php',
    );
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('Error: Could not find wp-load.php. Please run this script from WordPress root or via WP-CLI.');
    }
}

// Required pages with FULL CONTENT embedded directly
$required_pages = array(
    'home' => array(
        'title' => 'Home - Peta Alex For Peralite Manufacturing',
        'slug' => 'home',
        'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}},"backgroundColor":"base","textColor":"contrast","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-contrast-color has-base-background-color has-text-color has-background" style="padding-top:120px;padding-bottom:120px">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"56px","fontWeight":"700"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h1 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:56px;font-weight:700">Peta Alex For Peralite Manufacturing</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"22px"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<p class="has-text-align-center" style="margin-bottom:30px;font-size:22px">Leading Egypt\'s Perlite Industry with Quality and Innovation</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"bottom":"40px"}}}} -->
<p class="has-text-align-center" style="margin-bottom:40px;font-size:18px">At Peta Alex, we provide the highest quality Expanded Perlite derivatives to bridge the demand gap in the Egyptian market. We are committed to supplying the best perlite for thermal insulation, industrial applications, and agricultural solutions.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"base","style":{"border":{"radius":"50px"},"spacing":{"padding":{"top":"18px","bottom":"18px","left":"40px","right":"40px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-primary-background-color has-text-color has-background wp-element-button" style="border-radius:50px;padding-top:18px;padding-right:40px;padding-bottom:18px;padding-left:40px" href="/products">Explore Products</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-2-background-color has-background" style="padding-top:100px;padding-bottom:100px">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|secondary"}}}},"fontSize":"small","textColor":"secondary"} -->
<p class="has-link-color has-secondary-color has-text-color has-small-font-size" style="text-transform:uppercase;letter-spacing:2px;font-weight:600">About Us</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"42px","fontWeight":"700"},"spacing":{"margin":{"top":"15px","bottom":"20px"}}}} -->
<h2 class="wp-block-heading" style="margin-top:15px;margin-bottom:20px;font-size:42px;font-weight:700">Excellence in Perlite Manufacturing</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<p style="margin-bottom:20px;font-size:17px;line-height:1.8">Founded in June 2025, Peta Alex is a leading manufacturer of perlite and its derivatives in Egypt. Located in the Third Industrial Zone of New Borg El Arab, Alexandria, we serve industrial, construction, and agricultural sectors with premium quality products.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<p style="margin-bottom:30px;font-size:17px;line-height:1.8">Our state-of-the-art facility is designed to produce high-grade expanded perlite that meets international standards while adhering to Egyptian regulations.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"secondary","textColor":"base","style":{"border":{"radius":"8px"},"spacing":{"padding":{"top":"15px","bottom":"15px","left":"35px","right":"35px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-secondary-background-color has-text-color has-background wp-element-button" style="border-radius:8px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px" href="/about">Learn More About Us</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"20px"}}} -->
<figure class="wp-block-image size-large" style="border-radius:20px"><img alt="Manufacturing Facility" src="https://via.placeholder.com/600x400/84B754/ffffff?text=Manufacturing+Facility"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:100px;padding-bottom:100px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"38px","fontWeight":"700"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:38px;font-weight:700">Target Sectors</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"bottom":"50px"}}}} -->
<p class="has-text-align-center" style="margin-bottom:50px;font-size:18px">We serve diverse industries with our premium perlite products</p>
<!-- /wp:paragraph -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🏭</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Industrial Manufacturing</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6">High-performance insulation for industrial equipment and processes</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🏗️</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Construction & Building</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6">Lightweight aggregate and thermal insulation for construction</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🌱</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Agriculture & Horticulture</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6">Soil amendment and growing media for optimal plant growth</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🛢️</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Oil Refinery Filtration</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6">Premium filter aid for vegetable oil refining processes</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background" style="padding-top:100px;padding-bottom:100px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"38px","fontWeight":"700"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:38px;font-weight:700">Our Products</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","opacity":"0.8"},"spacing":{"margin":{"bottom":"50px"}}}} -->
<p class="has-text-align-center" style="margin-bottom:50px;font-size:18px;opacity:0.8">Premium quality perlite products and insulation materials</p>
<!-- /wp:paragraph -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">💎</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Expanded Perlite</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6","opacity":"0.7"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6;opacity:0.7">High-quality expanded perlite for industrial, construction, and agricultural applications</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🧊</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Glass Wool</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6","opacity":"0.7"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6;opacity:0.7">Premium thermal and acoustic insulation for buildings and industrial facilities</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"30px","bottom":"40px","left":"30px"},"margin":{"bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:0;padding-top:40px;padding-right:30px;padding-bottom:40px;padding-left:30px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:48px">🪨</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:20px;font-weight:600">Rock Wool</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6","opacity":"0.7"}}} -->
<p class="has-text-align-center" style="font-size:15px;line-height:1.6;opacity:0.7">Fire-resistant insulation material for high-temperature applications</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"40px"}}}} -->
<div class="wp-block-buttons" style="margin-top:40px">
<!-- wp:button {"backgroundColor":"primary","textColor":"base","style":{"border":{"radius":"50px"},"spacing":{"padding":{"top":"18px","bottom":"18px","left":"40px","right":"40px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-primary-background-color has-text-color has-background wp-element-button" style="border-radius:50px;padding-top:18px;padding-right:40px;padding-bottom:18px;padding-left:40px" href="/products">View All Products</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}},"backgroundColor":"base","textColor":"contrast","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-contrast-color has-base-background-color has-text-color has-background" style="padding-top:80px;padding-bottom:80px">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%">
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"36px","fontWeight":"700"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:20px;font-size:36px;font-weight:700">Ready to Partner with Us?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","opacity":"0.9"}}} -->
<p style="font-size:18px;opacity:0.9">Contact us today to discuss your perlite and insulation needs</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"base","textColor":"secondary","style":{"border":{"radius":"50px"},"spacing":{"padding":{"top":"18px","bottom":"18px","left":"35px","right":"35px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-secondary-color has-base-background-color has-text-color has-background wp-element-button" style="border-radius:50px;padding-top:18px;padding-right:35px;padding-bottom:18px;padding-left:35px" href="https://wa.me/201063315112">📞 +201063315112</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
    ),
    'about' => array(
        'title' => 'About Us - Peta Alex',
        'slug' => 'about',
        'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-2-background-color has-background" style="padding-top:100px;padding-bottom:100px">
<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"700"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<h1 class="wp-block-heading" style="margin-bottom:30px;font-size:48px;font-weight:700">About Peta Alex</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<p style="margin-bottom:20px;font-size:18px;line-height:1.8">Founded in June 2025, Peta Alex is a leading manufacturer of perlite and its derivatives in Egypt. Located in the Third Industrial Zone of New Borg El Arab, Alexandria, we serve industrial, construction, and agricultural sectors with premium quality products.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<p style="margin-bottom:20px;font-size:18px;line-height:1.8">Our state-of-the-art facility is designed to produce high-grade expanded perlite that meets international standards while adhering to Egyptian regulations.</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"36px","fontWeight":"700"},"spacing":{"margin":{"top":"50px","bottom":"20px"}}}} -->
<h2 class="wp-block-heading" style="margin-top:50px;margin-bottom:20px;font-size:36px;font-weight:700">Our Mission</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"}}} -->
<p style="font-size:18px;line-height:1.8">To provide the highest quality Expanded Perlite derivatives to bridge the demand gap in the Egyptian market.</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"36px","fontWeight":"700"},"spacing":{"margin":{"top":"50px","bottom":"20px"}}}} -->
<h2 class="wp-block-heading" style="margin-top:50px;margin-bottom:20px;font-size:36px;font-weight:700">Our Vision</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"}}} -->
<p style="font-size:18px;line-height:1.8">To become the leading perlite manufacturer in Egypt and the Middle East region.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
    ),
    'products' => array(
        'title' => 'Products - Peta Alex',
        'slug' => 'products',
        'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:100px;padding-bottom:100px">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"700"},"spacing":{"margin":{"bottom":"50px"}}}} -->
<h1 class="wp-block-heading has-text-align-center" style="margin-bottom:50px;font-size:48px;font-weight:700">Our Products</h1>
<!-- /wp:heading -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"40px","bottom":"40px","left":"40px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:40px;padding-bottom:40px;padding-left:40px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"64px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:64px">💎</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"28px","fontWeight":"700"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:28px;font-weight:700">Expanded Perlite</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p class="has-text-align-center" style="font-size:16px;line-height:1.8">High-quality expanded perlite for industrial, construction, and agricultural applications. Perfect for thermal insulation, lightweight aggregate, and soil amendment.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"40px","bottom":"40px","left":"40px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:40px;padding-bottom:40px;padding-left:40px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"64px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:64px">🧊</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"28px","fontWeight":"700"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:28px;font-weight:700">Glass Wool</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p class="has-text-align-center" style="font-size:16px;line-height:1.8">Premium thermal and acoustic insulation for buildings and industrial facilities. Excellent fire resistance and energy efficiency.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","right":"40px","bottom":"40px","left":"40px"},"margin":{"bottom":"0"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-2-background-color has-background" style="margin-bottom:0;padding-top:40px;padding-right:40px;padding-bottom:40px;padding-left:40px">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"64px"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-bottom:20px;font-size:64px">🪨</h3>
<!-- /wp:heading -->
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"28px","fontWeight":"700"},"spacing":{"margin":{"bottom":"15px"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:15px;font-size:28px;font-weight:700">Rock Wool</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p class="has-text-align-center" style="font-size:16px;line-height:1.8">Fire-resistant insulation material for high-temperature applications. Ideal for industrial and commercial use.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
    ),
    'contact' => array(
        'title' => 'Contact Us - Peta Alex',
        'slug' => 'contact',
        'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"100px","bottom":"100px"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-2-background-color has-background" style="padding-top:100px;padding-bottom:100px">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"700"},"spacing":{"margin":{"bottom":"50px"}}}} -->
<h1 class="wp-block-heading has-text-align-center" style="margin-bottom:50px;font-size:48px;font-weight:700">Contact Us</h1>
<!-- /wp:heading -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"32px","fontWeight":"700"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:30px;font-size:32px;font-weight:700">Get in Touch</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<p style="margin-bottom:30px;font-size:18px;line-height:1.8">We\'d love to hear from you. Contact us to discuss your perlite and insulation needs.</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"10px","top":"30px"}}}} -->
<h3 class="wp-block-heading" style="margin-top:30px;margin-bottom:10px;font-size:20px;font-weight:600">📍 Address</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p style="font-size:16px;line-height:1.8">Plot No. 26, Block 18, Third Industrial Zone - New Borg El Arab, Alexandria Governorate, Egypt</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"10px","top":"30px"}}}} -->
<h3 class="wp-block-heading" style="margin-top:30px;margin-bottom:10px;font-size:20px;font-weight:600">📞 Phone</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p style="font-size:16px;line-height:1.8"><a href="tel:+201063315112">+20 106 331 5112</a></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p style="font-size:16px;line-height:1.8"><a href="tel:+201152656531">+20 115 265 6531</a></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"spacing":{"margin":{"bottom":"10px","top":"30px"}}}} -->
<h3 class="wp-block-heading" style="margin-top:30px;margin-bottom:10px;font-size:20px;font-weight:600">💬 WhatsApp</h3>
<!-- /wp:heading -->
<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"10px"}}}} -->
<div class="wp-block-buttons" style="margin-top:10px">
<!-- wp:button {"backgroundColor":"secondary","textColor":"base","style":{"border":{"radius":"50px"},"spacing":{"padding":{"top":"15px","bottom":"15px","left":"30px","right":"30px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-secondary-background-color has-text-color has-background wp-element-button" style="border-radius:50px;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:30px" href="https://wa.me/201063315112">📱 Chat on WhatsApp</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"32px","fontWeight":"700"},"spacing":{"margin":{"bottom":"30px"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:30px;font-size:32px;font-weight:700">Company Information</h2>
<!-- /wp:heading -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"},"margin":{"bottom":"20px"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-background-color has-background" style="margin-bottom:20px;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"10px"}}}} -->
<p style="margin-bottom:10px;font-size:16px;line-height:1.8"><strong>Company Name:</strong> Peta Alex For Peralite Manufacturing</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"10px"}}}} -->
<p style="margin-bottom:10px;font-size:16px;line-height:1.8"><strong>Founded:</strong> June 2025</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"},"spacing":{"margin":{"bottom":"10px"}}}} -->
<p style="margin-bottom:10px;font-size:16px;line-height:1.8"><strong>Registration No:</strong> 29660</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","lineHeight":"1.8"}}} -->
<p style="font-size:16px;line-height:1.8"><strong>Deposit No:</strong> 8853</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
    ),
);

echo "=== Peta Alex Pages Management - FIXED VERSION ===\n\n";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

echo "Total pages found: " . count($all_pages) . "\n\n";

// Delete unused pages
$required_slugs = array_column($required_pages, 'slug');
$system_pages = array('sample-page', 'privacy-policy');
$deleted_count = 0;

echo "=== Deleting Unused Pages ===\n";
foreach ($all_pages as $page) {
    $slug = $page->post_name;
    
    if (!in_array($slug, $required_slugs) && 
        !in_array($slug, $system_pages) &&
        $page->post_status !== 'trash') {
        
        $deleted = wp_delete_post($page->ID, true);
        if ($deleted) {
            echo "✓ Deleted: ID {$page->ID} - {$page->post_title} (slug: {$slug})\n";
            $deleted_count++;
        } else {
            echo "✗ Failed to delete: ID {$page->ID} - {$page->post_title}\n";
        }
    }
}

echo "\nDeleted {$deleted_count} page(s)\n\n";

// Create or update required pages
echo "=== Creating/Updating Required Pages ===\n";
$created_count = 0;
$updated_count = 0;

foreach ($required_pages as $key => $page_data) {
    $title = $page_data['title'];
    $slug = $page_data['slug'];
    $content = $page_data['content'];
    
    if (empty($content)) {
        echo "⚠ Warning: Empty content for '{$title}', skipping...\n";
        continue;
    }
    
    // Check if page exists
    $existing_page = get_page_by_path($slug);
    
    $page_args = array(
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => $slug,
    );
    
    if ($existing_page) {
        $page_args['ID'] = $existing_page->ID;
        $page_id = wp_update_post($page_args, true);
        $action = 'updated';
        $updated_count++;
    } else {
        $page_id = wp_insert_post($page_args, true);
        $action = 'created';
        $created_count++;
    }
    
    if (is_wp_error($page_id)) {
        echo "✗ Error {$action} page '{$title}': " . $page_id->get_error_message() . "\n";
    } else {
        $url = get_permalink($page_id);
        $content_length = strlen($content);
        echo "✓ {$action}: ID {$page_id} - {$title} ({$slug})\n";
        echo "  URL: {$url}\n";
        echo "  Content length: {$content_length} characters\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total pages before: " . count($all_pages) . "\n";
echo "Pages deleted: {$deleted_count}\n";
echo "Pages created: {$created_count}\n";
echo "Pages updated: {$updated_count}\n";
echo "Required pages: " . count($required_pages) . "\n";

// Set homepage if needed
$home_page = get_page_by_path('home');
if ($home_page) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home_page->ID);
    echo "\n✓ Homepage set to: Home - Peta Alex For Peralite Manufacturing\n";
}

echo "\n=== Done! ===\n";

