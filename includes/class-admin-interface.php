<?php
/**
 * Admin Interface
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

namespace AISBP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Interface class
 * 
 * Handles admin page rendering and AJAX operations
 */
class Admin_Interface {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );
        add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );
    }

    /**
     * Add custom body class for our pages
     *
     * @param string $classes Existing classes.
     * @return string Modified classes.
     */
    public function add_body_class( $classes ) {
        $screen = get_current_screen();
        
        if ( $screen && strpos( $screen->id, 'ai-site-builder' ) !== false ) {
            $classes .= ' aisbp-admin-page';
        }

        return $classes;
    }

    /**
     * Display admin notices
     */
    public function display_notices() {
        $screen = get_current_screen();
        
        if ( ! $screen || strpos( $screen->id, 'ai-site-builder' ) === false ) {
            return;
        }

        // Check for missing API keys
        $api_keys = get_option( 'aisbp_api_keys', array() );
        
        if ( empty( $api_keys ) ) {
            ?>
            <div class="notice notice-warning is-dismissible aisbp-notice">
                <p>
                    <strong><?php esc_html_e( 'AI Site Builder Pro:', 'ai-site-builder-pro' ); ?></strong>
                    <?php esc_html_e( 'Please configure at least one AI model API key to start generating websites.', 'ai-site-builder-pro' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ai-site-builder-settings' ) ); ?>">
                        <?php esc_html_e( 'Go to Settings', 'ai-site-builder-pro' ); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get website types
     *
     * @return array Website types.
     */
    public static function get_website_types() {
        return array(
            'business'   => array(
                'label' => __( 'Business', 'ai-site-builder-pro' ),
                'icon'  => 'briefcase',
                'desc'  => __( 'Corporate, company, startup websites', 'ai-site-builder-pro' ),
            ),
            'portfolio'  => array(
                'label' => __( 'Portfolio', 'ai-site-builder-pro' ),
                'icon'  => 'palette',
                'desc'  => __( 'Personal, creative, professional portfolios', 'ai-site-builder-pro' ),
            ),
            'blog'       => array(
                'label' => __( 'Blog', 'ai-site-builder-pro' ),
                'icon'  => 'newspaper',
                'desc'  => __( 'News, magazine, personal blogs', 'ai-site-builder-pro' ),
            ),
            'ecommerce'  => array(
                'label' => __( 'E-commerce', 'ai-site-builder-pro' ),
                'icon'  => 'shopping-cart',
                'desc'  => __( 'Online store, product catalog', 'ai-site-builder-pro' ),
            ),
            'restaurant' => array(
                'label' => __( 'Restaurant', 'ai-site-builder-pro' ),
                'icon'  => 'utensils',
                'desc'  => __( 'Restaurant, cafe, food service', 'ai-site-builder-pro' ),
            ),
            'medical'    => array(
                'label' => __( 'Medical', 'ai-site-builder-pro' ),
                'icon'  => 'heart-pulse',
                'desc'  => __( 'Healthcare, clinic, medical practice', 'ai-site-builder-pro' ),
            ),
            'education'  => array(
                'label' => __( 'Education', 'ai-site-builder-pro' ),
                'icon'  => 'graduation-cap',
                'desc'  => __( 'School, courses, e-learning', 'ai-site-builder-pro' ),
            ),
            'realestate' => array(
                'label' => __( 'Real Estate', 'ai-site-builder-pro' ),
                'icon'  => 'home',
                'desc'  => __( 'Property listings, real estate agency', 'ai-site-builder-pro' ),
            ),
            'agency'     => array(
                'label' => __( 'Agency', 'ai-site-builder-pro' ),
                'icon'  => 'bullhorn',
                'desc'  => __( 'Marketing, creative, digital agency', 'ai-site-builder-pro' ),
            ),
            'nonprofit'  => array(
                'label' => __( 'Non-profit', 'ai-site-builder-pro' ),
                'icon'  => 'hand-holding-heart',
                'desc'  => __( 'Charity, foundation, NGO', 'ai-site-builder-pro' ),
            ),
            'saas'       => array(
                'label' => __( 'SaaS / App', 'ai-site-builder-pro' ),
                'icon'  => 'cloud',
                'desc'  => __( 'Software, application landing page', 'ai-site-builder-pro' ),
            ),
            'landing'    => array(
                'label' => __( 'Landing Page', 'ai-site-builder-pro' ),
                'icon'  => 'rocket',
                'desc'  => __( 'Single product, campaign, event', 'ai-site-builder-pro' ),
            ),
        );
    }

    /**
     * Get industries list
     *
     * @return array Industries.
     */
    public static function get_industries() {
        return array(
            'technology'    => __( 'Technology', 'ai-site-builder-pro' ),
            'finance'       => __( 'Finance & Banking', 'ai-site-builder-pro' ),
            'healthcare'    => __( 'Healthcare', 'ai-site-builder-pro' ),
            'education'     => __( 'Education', 'ai-site-builder-pro' ),
            'retail'        => __( 'Retail & Fashion', 'ai-site-builder-pro' ),
            'hospitality'   => __( 'Hospitality & Travel', 'ai-site-builder-pro' ),
            'construction'  => __( 'Construction', 'ai-site-builder-pro' ),
            'automotive'    => __( 'Automotive', 'ai-site-builder-pro' ),
            'legal'         => __( 'Legal Services', 'ai-site-builder-pro' ),
            'entertainment' => __( 'Entertainment', 'ai-site-builder-pro' ),
            'food'          => __( 'Food & Beverage', 'ai-site-builder-pro' ),
            'sports'        => __( 'Sports & Fitness', 'ai-site-builder-pro' ),
            'beauty'        => __( 'Beauty & Wellness', 'ai-site-builder-pro' ),
            'professional'  => __( 'Professional Services', 'ai-site-builder-pro' ),
            'nonprofit'     => __( 'Non-profit', 'ai-site-builder-pro' ),
            'other'         => __( 'Other', 'ai-site-builder-pro' ),
        );
    }

    /**
     * Get popular fonts
     *
     * @return array Font pairings.
     */
    public static function get_font_pairings() {
        return array(
            array(
                'heading' => 'Inter',
                'body'    => 'Inter',
                'style'   => 'Modern & Clean',
            ),
            array(
                'heading' => 'Poppins',
                'body'    => 'Open Sans',
                'style'   => 'Friendly & Professional',
            ),
            array(
                'heading' => 'Montserrat',
                'body'    => 'Lato',
                'style'   => 'Bold & Contemporary',
            ),
            array(
                'heading' => 'Playfair Display',
                'body'    => 'Source Sans Pro',
                'style'   => 'Elegant & Classic',
            ),
            array(
                'heading' => 'Roboto',
                'body'    => 'Roboto',
                'style'   => 'Universal & Versatile',
            ),
            array(
                'heading' => 'Outfit',
                'body'    => 'DM Sans',
                'style'   => 'Trendy & Modern',
            ),
            array(
                'heading' => 'Space Grotesk',
                'body'    => 'Work Sans',
                'style'   => 'Tech & Futuristic',
            ),
            array(
                'heading' => 'Sora',
                'body'    => 'Inter',
                'style'   => 'Minimal & Premium',
            ),
        );
    }

    /**
     * Get color presets
     *
     * @return array Color presets.
     */
    public static function get_color_presets() {
        return array(
            'modern' => array(
                'name'      => __( 'Modern Indigo', 'ai-site-builder-pro' ),
                'primary'   => '#4F46E5',
                'secondary' => '#10B981',
                'accent'    => '#F59E0B',
                'dark'      => '#1F2937',
                'light'     => '#F9FAFB',
            ),
            'ocean' => array(
                'name'      => __( 'Ocean Blue', 'ai-site-builder-pro' ),
                'primary'   => '#0EA5E9',
                'secondary' => '#14B8A6',
                'accent'    => '#F97316',
                'dark'      => '#0F172A',
                'light'     => '#F0F9FF',
            ),
            'sunset' => array(
                'name'      => __( 'Sunset Glow', 'ai-site-builder-pro' ),
                'primary'   => '#EC4899',
                'secondary' => '#8B5CF6',
                'accent'    => '#F59E0B',
                'dark'      => '#1E1B4B',
                'light'     => '#FDF4FF',
            ),
            'forest' => array(
                'name'      => __( 'Forest Green', 'ai-site-builder-pro' ),
                'primary'   => '#059669',
                'secondary' => '#0D9488',
                'accent'    => '#CA8A04',
                'dark'      => '#14532D',
                'light'     => '#F0FDF4',
            ),
            'midnight' => array(
                'name'      => __( 'Midnight Dark', 'ai-site-builder-pro' ),
                'primary'   => '#6366F1',
                'secondary' => '#22D3EE',
                'accent'    => '#FBBF24',
                'dark'      => '#0F0F23',
                'light'     => '#1E1E3F',
            ),
            'coral' => array(
                'name'      => __( 'Coral Reef', 'ai-site-builder-pro' ),
                'primary'   => '#F43F5E',
                'secondary' => '#FB923C',
                'accent'    => '#A855F7',
                'dark'      => '#27272A',
                'light'     => '#FFF1F2',
            ),
            'monochrome' => array(
                'name'      => __( 'Monochrome', 'ai-site-builder-pro' ),
                'primary'   => '#18181B',
                'secondary' => '#52525B',
                'accent'    => '#3B82F6',
                'dark'      => '#09090B',
                'light'     => '#FAFAFA',
            ),
            'nature' => array(
                'name'      => __( 'Nature Fresh', 'ai-site-builder-pro' ),
                'primary'   => '#84CC16',
                'secondary' => '#22C55E',
                'accent'    => '#06B6D4',
                'dark'      => '#365314',
                'light'     => '#ECFCCB',
            ),
        );
    }
}
