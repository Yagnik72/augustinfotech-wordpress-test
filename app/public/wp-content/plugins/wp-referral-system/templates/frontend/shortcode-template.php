<?php
/**
 * Template for the referral system shortcode
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wp-referral-system-shortcode">
    <h2><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="wp-referral-system-info">
        <p><?php echo esc_html__('Your Referral Code:', 'wp-referral-system'); ?> 
           <strong><?php echo esc_html($referral_code); ?></strong></p>
        
        <p><?php echo esc_html__('Your Referral Link:', 'wp-referral-system'); ?></p>
        <input type="text" 
               value="<?php echo esc_url($referral_url); ?>" 
               readonly 
               class="wp-referral-system-link" />
        
        <?php if ($atts['show_commission'] === 'yes') : ?>
            <p><?php echo esc_html__('Commission Rate:', 'wp-referral-system'); ?> 
               <strong><?php echo esc_html($commission); ?><?php echo WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL; ?></strong></p>
        <?php endif; ?>
    </div>
</div> 