<?php
namespace WPReferralSystem\Core;

/**
 * Handles custom template registration and management
 */
class TemplateManager {
    /**
     * Template directory
     *
     * @var string
     */
    private $template_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->template_dir = 'wp-referral-system/templates/';
        
        add_filter('theme_page_templates', array($this, 'add_template_to_dropdown'));
        add_filter('page_template', array($this, 'register_template'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_template_assets'));
    }

    /**
     * Add template to page template dropdown
     */
    public function add_template_to_dropdown($templates) {
        $templates['frontend/registration-form.php'] = __('Registration Form', 'wp-referral-system');        
        return $templates;
    }

    /**
     * Register template
     */
    public function register_template($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }

        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template && file_exists(WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'templates/' . $page_template)) {
            return WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'templates/' . $page_template;
        }
        
        return $template;
    }

    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets() {
        global $post;
        
        if (!$post) {
            return;
        }

        wp_register_style(
            'wp-referral-system-template',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/css/frontend/template.css',
            array(),
            WP_REFERRAL_SYSTEM_VERSION
        );

        wp_register_script(
            'wp-referral-system-template',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/js/frontend/template.js',
            array('jquery'),
            WP_REFERRAL_SYSTEM_VERSION,
            true
        );

        wp_localize_script(
            'wp-referral-system-template',
            'wpReferralSystem',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_referral_system_nonce')
            )
        );
    }
} 