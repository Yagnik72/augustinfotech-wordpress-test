
<div class="wrap">
    <h1><?php _e('Referral System Settings', 'wp-referral-system'); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('wp_referral_system_settings');
        do_settings_sections('wp-referral-settings');
        submit_button();
        ?>
    </form>

    <div class="shortcode-info">
        <h2><?php _e('Shortcode', 'wp-referral-system'); ?></h2>
        <p><?php _e('Use the following shortcode to display the registration form:', 'wp-referral-system'); ?></p>
        <code>[wp_referral_system show_commission="yes"]</code>
    </div>
</div> 
