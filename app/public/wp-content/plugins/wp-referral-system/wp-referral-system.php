<?php
/**
 * Plugin Name: WP Referral System
 * Plugin URI: 
 * Description: A WordPress plugin for managing referral system with commission tracking
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * Text Domain: wp-referral-system
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_REFERRAL_SYSTEM_VERSION', '1.0.0');
define('WP_REFERRAL_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_REFERRAL_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL', '₹');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    $prefix = 'WPReferralSystem\\';
    $base_dir = WP_REFERRAL_SYSTEM_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  
    // Try to load the file
    if (file_exists($file)) {
        require $file;
        return;
    }

    // If the file doesn't exist, try to find it in subdirectories
    $parts = explode('\\', $relative_class);
    $class_name = array_pop($parts);
    $subdir = strtolower(implode('/', $parts));
    
    $file = $base_dir . $subdir . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function wp_referral_system_init() {
    // Load plugin text domain
    load_plugin_textdomain('wp-referral-system', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize the plugin
    WPReferralSystem\Core\Plugin::get_instance();

    // Initialize blocks
    new WPReferralSystem\Blocks();
}
add_action('plugins_loaded', 'wp_referral_system_init');