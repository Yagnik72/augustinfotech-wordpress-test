<?php
namespace WPReferralSystem\Core;

/**
 * Main plugin class that initializes all components
 */
class Plugin {
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_components();
        $this->enqueue_frontend_assets();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Register activation and deactivation hooks
        register_activation_hook(WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'wp-referral-system.php', array($this, 'activate'));
        register_deactivation_hook(WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'wp-referral-system.php', array($this, 'deactivate'));
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize admin components
        if (is_admin()) {
            new \WPReferralSystem\Admin\ReferralAdmin();
            new \WPReferralSystem\Admin\ReferralSettings();
        }

        // Initialize frontend components
        new \WPReferralSystem\Frontend\Shortcode();
        new \WPReferralSystem\Frontend\Registration();
        new \WPReferralSystem\Frontend\Ajax();
        
        
        // Initialize template manager
        new TemplateManager();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}referral_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            referral_user_id bigint(20) NOT NULL,
            commission decimal(10,2) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Add default options
        add_option('wp_referral_join_commission', 50);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if necessary
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'wp-referral-system-frontend',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/css/frontend/registration.css',
            array(),
            WP_REFERRAL_SYSTEM_VERSION
        );

        wp_enqueue_script(
            'wp-referral-system-frontend',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WP_REFERRAL_SYSTEM_VERSION,
            true
        );

        wp_localize_script(
            'wp-referral-system-frontend',
            'wpReferralSystem',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_referral_system_nonce'),
                'validating' => __('Validating...', 'wp-referral-system'),
                'valid' => __('Valid', 'wp-referral-system'),
                'invalid' => __('Invalid', 'wp-referral-system'),
                'error' => __('Error', 'wp-referral-system'),
                'currency_symbol' => WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL
            )
        );
    }
} 