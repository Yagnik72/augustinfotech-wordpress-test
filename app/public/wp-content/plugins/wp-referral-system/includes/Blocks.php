<?php
namespace WPReferralSystem;

/**
 * Handles block registration and functionality
 */
class Blocks {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
    }

    /**
     * Register blocks
     */
    public function register_blocks() {
        // Register block script and style
        wp_register_script(
            'wp-referral-system-top-blogs',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'build/index.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-editor',
                'wp-components',
                'wp-i18n',
                'wp-data',
                'wp-core-data',
                'wp-server-side-render'
            ),
            WP_REFERRAL_SYSTEM_VERSION,
            true
        );

        // Register editor style
        wp_register_style(
            'wp-referral-system-top-blogs-editor',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'build/index.css',
            array(),
            WP_REFERRAL_SYSTEM_VERSION
        );

        // Register frontend style
        wp_register_style(
            'wp-referral-system-top-blogs',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'build/index.css',
            array(),
            WP_REFERRAL_SYSTEM_VERSION
        );

        // Register block
        register_block_type(
            WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'blocks/top-blogs/block.json',
            array(
                'editor_style' => 'wp-referral-system-top-blogs-editor',
                'style' => 'wp-referral-system-top-blogs',
                'render_callback' => function($attributes) {
                    require_once WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'blocks/top-blogs/render.php';
                    return render_top_blogs_block($attributes);
                }
            )
        );
    }
} 