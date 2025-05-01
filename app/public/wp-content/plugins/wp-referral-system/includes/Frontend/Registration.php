<?php

namespace WPReferralSystem\Frontend;

/**
 * Handles user registration with referral tracking
 */
class Registration {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'check_referral_code'));
    }

    /**
     * Check for referral code in URL
     */
    public function check_referral_code() {
        if (isset($_GET['ref']) && !is_user_logged_in()) {
            $referral_code = sanitize_text_field($_GET['ref']);
            setcookie('wp_referral_code', $referral_code, time() + (86400 * 30), '/'); // 30 days
        }
    }

   
} 
