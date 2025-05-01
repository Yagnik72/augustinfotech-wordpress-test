<?php
global $wpdb;

// Get total referrals
$total_referrals = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}referral_history");

// Get total commission
$total_commission = $wpdb->get_var("SELECT SUM(commission) FROM {$wpdb->prefix}referral_history");

// Get recent referrals
$recent_referrals = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}referral_history ORDER BY created_at DESC LIMIT 5"
);
?>
<div class="wrap">
    <h1><?php _e('Referral System Dashboard', 'wp-referral-system'); ?></h1>

    <div class="dashboard-stats">
        <div class="stat-box">
            <h2><?php _e('Total Referrals', 'wp-referral-system'); ?></h2>
            <p class="stat-number"><?php echo number_format($total_referrals); ?></p>
        </div>

        <div class="stat-box">
            <h2><?php _e('Total Commission', 'wp-referral-system'); ?></h2>
            <p class="stat-number"><?php echo WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL; ?><?php echo number_format($total_commission, 2); ?></p>
        </div>
    </div>

    <div class="recent-referrals">
        <h2><?php _e('Recent Referrals', 'wp-referral-system'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('User', 'wp-referral-system'); ?></th>
                    <th><?php _e('Referral User', 'wp-referral-system'); ?></th>
                    <th><?php _e('Commission', 'wp-referral-system'); ?></th>
                    <th><?php _e('Date', 'wp-referral-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_referrals as $referral) : ?>
                    <tr>
                        <td><?php echo esc_html(get_user_by('id', $referral->user_id)->user_login); ?></td>
                        <td><?php echo esc_html(get_user_by('id', $referral->referral_user_id)->user_login); ?></td>
                        <td><?php echo WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL; ?><?php echo number_format($referral->commission, 2); ?></td>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($referral->created_at)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 
