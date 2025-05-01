<?php
namespace WPReferralSystem\Frontend;

/**
 * Handles shortcode functionality
 */
class Shortcode {
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('wp_referral_system', array($this, 'render_shortcode'));
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Referral System', 'wp-referral-system'),
            'show_commission' => 'yes'
        ), $atts);

        if (!is_user_logged_in()) {
            return $this->render_login_form();
        }

        wp_enqueue_style( 'wp-referral-system-template');
        wp_enqueue_script( 'wp-referral-system-template' );

        $user_id = get_current_user_id();
        $referral_code = get_user_meta($user_id, 'referral_code', true);
        
        if (empty($referral_code)) {
            $referral_code = $this->generate_referral_code($user_id);
            update_user_meta($user_id, 'referral_code', $referral_code);
        }

        $referral_url = home_url('/?ref=' . $referral_code);
        $commission = get_option('wp_referral_join_commission', 50);

        ob_start();
        include WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'templates/frontend/shortcode-template.php';

        // Display referral statistics if user is logged in
        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();
            $table_name = $wpdb->prefix . 'referral_history';
            
            // Get total referrals
            $total_referrals = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d",
                $user_id
            ));
            
            // Get total commission
            $total_commission = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(commission) FROM {$table_name} WHERE user_id = %d",
                $user_id
            ));
            if($total_commission == null){
                $total_commission = 0;
            }
            ?>
            <div class="wp-referral-system-stats">
                <h3><?php echo esc_html__('Your Referral Statistics', 'wp-referral-system'); ?></h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <h4><?php echo esc_html__('Total Referrals', 'wp-referral-system'); ?></h4>
                        <p class="stat-value"><?php echo esc_html($total_referrals); ?></p>
                    </div>
                    <div class="stat-item">
                        <h4><?php echo esc_html__('Total Commission', 'wp-referral-system'); ?></h4>
                        <p class="stat-value"><?php echo esc_html($total_commission); ?><?php echo WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL; ?></p>
                    </div>
                </div>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Render login form
     */
    private function render_login_form() {
        ob_start();
        ?>
        <div class="wp-referral-system-login">
            <p><?php echo esc_html__('Please login to view your referral information.', 'wp-referral-system'); ?></p>
            <?php wp_login_form(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate referral code
     */
    private function generate_referral_code($user_id) {
        $user = get_user_by('id', $user_id);
        $username = $user->user_login;
        $random = substr(md5(uniqid(rand(), true)), 0, 4);
        return strtolower($username . $random);
    }
} 