<?php
/*
Plugin Name: Event
Plugin URI: 
Description: A WordPress plugin for managing events with event manager role
Version: 1.0
Author: 
Author URI: 
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: par-event
*/

if (!defined('ABSPATH')) exit;

define('EVENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EVENT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once EVENT_PLUGIN_DIR . 'includes/class-event-activator.php';
require_once EVENT_PLUGIN_DIR . 'includes/class-event-deactivator.php';
require_once EVENT_PLUGIN_DIR . 'includes/class-event.php';

register_activation_hook(__FILE__, array('Event_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Event_Deactivator', 'deactivate'));

function run_event_plugin() {
    $plugin = new Event();
    $plugin->run();
}

run_event_plugin(); 