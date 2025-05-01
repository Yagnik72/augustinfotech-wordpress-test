<?php
namespace WPReferralSystem\Frontend;

/**
 * Handles AJAX functionality
 */
class Ajax {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wp_referral_system_get_stats', array($this, 'get_referral_stats'));
        add_action('wp_ajax_nopriv_wp_referral_system_get_stats', array($this, 'get_referral_stats'));
        
        // Add referral code validation handler
        add_action('wp_ajax_validate_referral_code', array($this, 'validate_referral_code'));
        add_action('wp_ajax_nopriv_validate_referral_code', array($this, 'validate_referral_code'));

        // Add registration handler
        add_action('wp_ajax_process_registration', array($this, 'process_registration'));
        add_action('wp_ajax_nopriv_process_registration', array($this, 'process_registration'));
    }

    /**
     * Validate referral code
     */
    public function validate_referral_code() {
        check_ajax_referer('wp_referral_system_nonce', 'nonce');

        $referral_code = isset($_POST['referral_code']) ? sanitize_text_field($_POST['referral_code']) : '';

        if (empty($referral_code)) {
            wp_send_json_error(array(
                'message' => __('Referral code is required.', 'wp-referral-system')
            ));
        }

        // Get user by referral code
        $user = $this->get_user_by_referral_code($referral_code);

        if ($user) {
            wp_send_json_success(array(
                'message' => __('Valid referral code.', 'wp-referral-system'),
                'user_id' => $user->ID,
                'user_name' => $user->display_name
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid referral code.', 'wp-referral-system')
            ));
        }
    }

    /**
     * Get user by referral code
     */
    private function get_user_by_referral_code($referral_code) {
        global $wpdb;
        
        // Get user ID from referral code
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key = 'referral_code' 
             AND meta_value = %s",
            $referral_code
        ));

        if ($user_id) {
            return get_user_by('id', $user_id);
        }

        return false;
    }

    /**
     * Get referral statistics
     */
    public function get_referral_stats() {
        check_ajax_referer('wp_referral_system_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('You must be logged in to view referral statistics.', 'wp-referral-system')
            ));
        }

        $user_id = get_current_user_id();
        $stats = $this->calculate_referral_stats($user_id);
        if($stats['total_commission'] == null){
            $stats['total_commission'] = 0;
        }
        wp_send_json_success($stats);
    }

    /**
     * Calculate referral statistics
     */
    private function calculate_referral_stats($user_id) {
        global $wpdb;
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

        // Get recent referrals (last 5)
        $recent_referrals = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT 5",
            $user_id
        ));

        $recent_referrals_formatted = array();
        foreach ($recent_referrals as $referral) {
            $referral_user = get_user_by('id', $referral->referral_user_id);
            $recent_referrals_formatted[] = array(
                'username' => $referral_user ? $referral_user->display_name : __('Unknown', 'wp-referral-system'),
                'commission' => $referral->commission,
                'date' => $referral->created_at
            );
        }

        return array(
            'total_referrals' => $total_referrals,
            'total_commission' => $total_commission,
            'recent_referrals' => $recent_referrals_formatted
        );
    }

    /**
     * Process registration form
     */
    public function process_registration() {
        check_ajax_referer('wp_referral_registration', 'nonce');

        $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
        $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
        $user_pass = isset($_POST['user_pass']) ? $_POST['user_pass'] : '';
        $referral_code = isset($_POST['referral_code']) ? sanitize_text_field($_POST['referral_code']) : '';

        // Validate required fields
        if (empty($user_login) || empty($user_email) || empty($user_pass)) {
            wp_send_json_error(array(
                'message' => __('All fields are required.', 'wp-referral-system')
            ));
        }

        // Check if username exists
        if (username_exists($user_login)) {
            wp_send_json_error(array(
                'message' => __('Username already exists.', 'wp-referral-system')
            ));
        }

        // Check if email exists
        if (email_exists($user_email)) {
            wp_send_json_error(array(
                'message' => __('Email already exists.', 'wp-referral-system')
            ));
        }

        // Create user
        $user_id = wp_create_user($user_login, $user_pass, $user_email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array(
                'message' => $user_id->get_error_message()
            ));
        }

        // Handle referral code
        if (!empty($referral_code)) {
            $referrer = $this->get_user_by_referral_code($referral_code);
            if ($referrer) {
                $this->create_referral($referrer->ID, $user_id);
            }
        } else {
            // Check for cookie-based referral
            $referrer_id = isset($_COOKIE['wp_referral_code']) ? $this->get_user_by_referral_code($_COOKIE['wp_referral_code']) : false;
            if ($referrer_id) {
                $this->create_referral($referrer_id->ID, $user_id);
            }
        }

        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_send_json_success(array(
            'message' => __('Registration successful!', 'wp-referral-system'),
            'redirect' => home_url('/')
        ));
    }

    /**
     * Create referral record
     */
    private function create_referral($referrer_id, $new_user_id) {
        global $wpdb;
        
        $commission = get_option('wp_referral_join_commission', 50);
        
        $wpdb->insert(
            $wpdb->prefix . 'referral_history',
            array(
                'user_id' => $referrer_id,
                'referral_user_id' => $new_user_id,
                'commission' => $commission,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%f', '%s')
        );
    }
} 