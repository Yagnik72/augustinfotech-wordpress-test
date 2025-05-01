jQuery(document).ready(function($) {
    // Copy referral link to clipboard
    $('.wp-referral-system-link').on('click', function() {
        $(this).select();
        document.execCommand('copy');
        
        // Show copied message
        const originalText = $(this).val();
        $(this).val('Copied!');
        
        setTimeout(() => {
            $(this).val(originalText);
        }, 2000);
    });

    // Refresh stats periodically (every 30 seconds)
    if ($('.wp-referral-system-stats').length) {
        setInterval(function() {
            $.ajax({
                url: wpReferralSystem.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_referral_system_get_stats',
                    nonce: wpReferralSystem.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.stat-value').eq(0).text(response.data.total_referrals);
                        $('.stat-value').eq(1).text(response.data.total_commission + '₹');
                    }
                }
            });
        }, 30000);
    }
}); 