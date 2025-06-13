<?php
class Event_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css', [], '5.11.3');
        wp_enqueue_style($this->plugin_name, EVENT_PLUGIN_URL . 'public/css/event-public.css', [], $this->version);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', [], '5.11.3', true);
        wp_enqueue_script($this->plugin_name, EVENT_PLUGIN_URL . 'public/js/event-public.js', ['jquery', 'fullcalendar'], $this->version, true);
        wp_localize_script($this->plugin_name, 'event_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('event_nonce')
        ]);
    }

    public function event_calendar_shortcode($atts) {
        $atts = shortcode_atts(['status' => 'approved'], $atts, 'event_calendar');
        ob_start();
        ?>
        <div id="event-calendar"></div>
        <div id="event-modal" class="event-modal">
            <div class="event-modal-content">
                <span class="event-modal-close">&times;</span>
                <h2 id="event-modal-title"></h2>
                <div id="event-modal-description"></div>
                <div id="event-modal-details"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function get_events() {
        check_ajax_referer('event_nonce', 'nonce');
        global $wpdb;
        
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'approved';
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}events WHERE status = %s",
            $status
        ));

        $formatted_events = array_map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->date . 'T' . $event->time,
                'description' => $event->description,
                'event_manager' => get_user_by('id', $event->event_manager_id)->display_name
            ];
        }, $events);

        wp_send_json($formatted_events);
    }
}