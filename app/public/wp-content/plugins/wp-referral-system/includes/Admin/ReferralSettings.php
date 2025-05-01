<?php
namespace WPReferralSystem\Admin;

/**
 * Handles plugin settings
 */
class ReferralSettings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'wp-referral-system',
            __('Settings', 'wp-referral-system'),
            __('Settings', 'wp-referral-system'),
            'manage_options',
            'wp-referral-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wp_referral_system_settings',
            'wp_referral_join_commission',
            array(
                'type' => 'number',
                'sanitize_callback' => 'absint',
                'default' => 50
            )
        );

        add_settings_section(
            'wp_referral_system_general',
            __('General Settings', 'wp-referral-system'),
            array($this, 'render_general_section'),
            'wp-referral-settings'
        );

        add_settings_field(
            'wp_referral_join_commission',
            __('Join Commission', 'wp-referral-system'),
            array($this, 'render_commission_field'),
            'wp-referral-settings',
            'wp_referral_system_general'
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Render general section
     */
    public function render_general_section() {
        echo '<p>' . esc_html__('Configure general settings for the referral system.', 'wp-referral-system') . '</p>';
    }

    /**
     * Render commission field
     */
    public function render_commission_field() {
        $commission = get_option('wp_referral_join_commission', 50);
        ?>
        <input type="number" 
               name="wp_referral_join_commission" 
               value="<?php echo esc_attr($commission); ?>" 
               min="0" 
               max="100" 
               step="1" />
        <p class="description">
            <?php echo esc_html__('Commission percentage for each successful referral.', 'wp-referral-system'); ?>
        </p>
        <?php
    }
} 