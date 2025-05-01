/**
 * Admin JavaScript
 */
(function($) {
    'use strict';

    // Handle bulk actions
    $('.bulkactions .button').on('click', function(e) {
        var action = $('select[name="action"]').val();
        var action2 = $('select[name="action2"]').val();
        var selectedAction = action !== '-1' ? action : action2;

        if (selectedAction === 'delete') {
            if (!confirm(wpReferralSystem.confirmDelete)) {
                e.preventDefault();
            }
        }
    });

    // Handle individual delete actions
    $('.row-actions .delete a').on('click', function(e) {
        if (!confirm(wpReferralSystem.confirmDelete)) {
            e.preventDefault();
        }
    });

})(jQuery); 