<?php
/**
 * Template Name: Registration form template with referral code validation
 */
get_header();
?>
<div class="wp-referral-system-registration">
    <form id="wp-referral-registration-form" class="wp-referral-form" method="post">
        <?php wp_nonce_field('wp_referral_registration', 'wp_referral_nonce'); ?>
        
        <div class="form-group">
            <label for="user_login"><?php _e('Username', 'wp-referral-system'); ?> *</label>
            <input type="text" name="user_login" id="user_login" class="input" required />
        </div>

        <div class="form-group">
            <label for="user_email"><?php _e('Email', 'wp-referral-system'); ?> *</label>
            <input type="email" name="user_email" id="user_email" class="input" required />
        </div>

        <div class="form-group">
            <label for="user_pass"><?php _e('Password', 'wp-referral-system'); ?> *</label>
            <input type="password" name="user_pass" id="user_pass" class="input" required />
        </div>

        <div class="form-group">
            <label for="referral_code"><?php _e('Referral Code', 'wp-referral-system'); ?></label>
            <div class="referral-code-container">
                <input type="text" name="referral_code" id="referral_code" class="input" value="<?php echo isset($_COOKIE['wp_referral_code']) ? $_COOKIE['wp_referral_code'] : ''; ?>"/>
                <span class="referral-code-status"></span>                  
            </div>
            <p class="description"><?php _e('Enter your referral code if you have one', 'wp-referral-system'); ?></p>
        </div>

        <div class="form-group">
            <button type="submit" class="button button-primary"><?php _e('Register', 'wp-referral-system'); ?></button>
        </div>

        <div class="wp-referral-message"></div>
    </form>
</div>


<?php get_footer(); ?>