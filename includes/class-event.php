<?php
class Event {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'event';
        $this->version = '1.0.0';
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once EVENT_PLUGIN_DIR . 'includes/class-event-loader.php'; // loader all class maethod abract   

        require_once EVENT_PLUGIN_DIR . 'admin/class-event-admin.php';
        require_once EVENT_PLUGIN_DIR . 'includes/class-event-list-table.php';

        require_once EVENT_PLUGIN_DIR . 'public/class-event-public.php';
        
        $this->loader = new Event_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new Event_Admin($this->plugin_name, $this->version);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }

    private function define_public_hooks() {
        $plugin_public = new Event_Public($this->plugin_name, $this->version);
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_shortcode('event_calendar', $plugin_public, 'event_calendar_shortcode');
        $this->loader->add_action('wp_ajax_get_events', $plugin_public, 'get_events');
        $this->loader->add_action('wp_ajax_nopriv_get_events', $plugin_public, 'get_events');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
} 