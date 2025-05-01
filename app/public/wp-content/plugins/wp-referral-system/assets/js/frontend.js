/**
 * Frontend JavaScript
 */
(function($) {
    'use strict';

    // Handle referral code validation
    $('#referral_code').on('input', function() {
        var referralCode = $(this).val();
        var statusElement = $('.referral-code-status');
        
        if (referralCode.length > 0) {
            $.ajax({
                url: wpReferralSystem.ajaxurl,
                type: 'POST',
                data: {
                    action: 'validate_referral_code',
                    referral_code: referralCode,
                    nonce: wpReferralSystem.nonce
                },
                beforeSend: function() {
                    statusElement.html('<span class="loading">' + wpReferralSystem.validating + '</span>');
                },
                success: function(response) {
                    if (response.success) {
                        statusElement.html('<span class="valid">' + wpReferralSystem.valid + '</span>');
                    } else {
                        statusElement.html('<span class="invalid">' + wpReferralSystem.invalid + '</span>');
                    }
                },
                error: function() {
                    statusElement.html('<span class="error">' + wpReferralSystem.error + '</span>');
                }
            });
        } else {
            statusElement.html('');
        }
    });

    // Handle form submission
    $('#wp-referral-registration-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var $message = $form.find('.wp-referral-message');
        var referralCode = $('#referral_code').val();
        
        // If referral code is provided, validate it first
        if (referralCode.length > 0) {
            $.ajax({
                url: wpReferralSystem.ajaxurl,
                type: 'POST',
                data: {
                    action: 'validate_referral_code',
                    referral_code: referralCode,
                    nonce: wpReferralSystem.nonce
                },
                beforeSend: function() {
                    $submitButton.prop('disabled', true);
                    $message.html('<div class="notice notice-info"><p>Validating referral code...</p></div>');
                },
                success: function(response) {
                    if (response.success) {
                        // If valid, proceed with registration
                        submitRegistrationForm($form, $submitButton, $message);
                    } else {
                        $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        $submitButton.prop('disabled', false);
                    }
                },
                error: function() {
                    $message.html('<div class="notice notice-error"><p>Error validating referral code.</p></div>');
                    $submitButton.prop('disabled', false);
                }
            });
        } else {
            // If no referral code, proceed directly with registration
            submitRegistrationForm($form, $submitButton, $message);
        }
    });

    /**
     * Submit registration form
     */
    function submitRegistrationForm($form, $submitButton, $message) {
        $.ajax({
            url: wpReferralSystem.ajaxurl,
            type: 'POST',
            data: {
                action: 'process_registration',
                user_login: $('#user_login').val(),
                user_email: $('#user_email').val(),
                user_pass: $('#user_pass').val(),
                referral_code: $('#referral_code').val(),
                nonce: $('#wp_referral_nonce').val()
            },
            beforeSend: function() {
                $submitButton.prop('disabled', true);
                $message.html('<div class="notice notice-info"><p>Processing registration...</p></div>');
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
            },
            complete: function() {
                $submitButton.prop('disabled', false);
            }
        });
    }

})(jQuery); 