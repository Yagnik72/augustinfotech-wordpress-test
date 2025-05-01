<?php
$referral_table = new \WPReferralSystem\Admin\ReferralHistory();
$referral_table->prepare_items();
?>
<div class="wrap">
    <h1><?php _e('Referral History', 'wp-referral-system'); ?></h1>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
        <?php
        $referral_table->search_box(__('Search', 'wp-referral-system'), 'search_id');
        $referral_table->display();
        ?>
    </form>
</div> 
