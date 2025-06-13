<?php
class Event_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, EVENT_PLUGIN_URL . 'admin/css/event-admin.css', array(), $this->version, 'all');
    }


    public function add_plugin_admin_menu() {

        // * Handle event form submission - start
        if (isset($_POST['submit']) && isset($_POST['event_nonce']) && wp_verify_nonce($_POST['event_nonce'], 'add_event')) {
            $this->handle_event_form_submission();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['event'])) {
            $this->handle_delete_event(intval($_GET['event']));
        }

        if (isset($_GET['action']) && in_array($_GET['action'], array('approve', 'reject')) && isset($_GET['event'])) {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to perform this action.'));
            }

            $event_id = intval($_GET['event']);
            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            
            if ($_GET['action'] === 'approve' && wp_verify_nonce($nonce, 'approve_event_' . $event_id)) {
                $this->approve_event($event_id);
            } elseif ($_GET['action'] === 'reject' && wp_verify_nonce($nonce, 'reject_event_' . $event_id)) {
                $this->reject_event($event_id);
            }
        }

        if (isset($_REQUEST['action2']) && isset($_REQUEST['event'])) {
            $this->bulk_action_handle($_REQUEST['event'], $_REQUEST['action2']);
        }
        // * Handle event form submission - end


        // Add menu page for administrators
        add_menu_page(
            'Events',
            'Events',
            'list_events', // 'manage_options'
            'events',
            array($this, 'display_plugin_admin_page'),
            'dashicons-calendar-alt',
            26
        );

    }

    public function display_plugin_admin_page() {
        if (!current_user_can('edit_events')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            include_once EVENT_PLUGIN_DIR . 'admin/partials/event-admin-form.php';
            return;
        }

        $event_list_table = new Event_List_Table();
        $event_list_table->prepare_items();
        include_once EVENT_PLUGIN_DIR . 'admin/partials/event-admin-display.php';
    }

    private function handle_event_form_submission() {
        if (!current_user_can('edit_events')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $title = sanitize_text_field($_POST['event_title']);
        $description = sanitize_textarea_field($_POST['event_description']);
        $date = sanitize_text_field($_POST['event_date']);
        $time = sanitize_text_field($_POST['event_time']);
        
        $status = current_user_can('manage_options') ? 
            (isset($_POST['event_status']) ? sanitize_text_field($_POST['event_status']) : 'approved') : 
            'pending';

        global $wpdb;
        $table_name = $wpdb->prefix . 'events';

        // Check if this is an edit operation
        if (isset($_POST['event_id'])) {
            $event_id = intval($_POST['event_id']);
            
            // Simple permission check for editing
            $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
            if (!$event || (!current_user_can('manage_options') && $event->event_manager_id != get_current_user_id())) {
                wp_die(__('You do not have permission to edit this event.'));
            }

            // Update event
            $data = array(
                'title' => $title,
                'description' => $description,
                'date' => $date,
                'time' => $time
            );

            if (current_user_can('manage_options')) {
                $data['status'] = $status;
            }

            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $event_id)
            );

    
        } else {
            // Create new event
            $data = array(
                'title' => $title,
                'description' => $description,
                'event_manager_id' => get_current_user_id(),
                'date' => $date,
                'time' => $time,
                'status' => $status
            );

            $result = $wpdb->insert($table_name, $data);

            if ($result) {
                // Send email to admin if event manager created the event
                    $admin_email = get_option('admin_email');
                    wp_mail(
                        $admin_email,
                        'New Event Pending Approval',
                        sprintf(
                            'A new event "%s" has been created by %s and is pending your approval.',
                            $title,
                            wp_get_current_user()->display_name
                        )
                    );

            }
        }

        wp_redirect(admin_url('admin.php?page=events'));
        exit;
    }

    private function handle_delete_event($event_id) {
        if (!current_user_can('delete_events')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'events';

        // Simple permission check for deletion
        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
        if (!$event || (!current_user_can('manage_options') && $event->event_manager_id != get_current_user_id())) {
            wp_die(__('You do not have permission to delete this event.'));
        }

        $result = $wpdb->delete($table_name, array('id' => $event_id));

        wp_redirect(admin_url('admin.php?page=events'));
        exit;
    }

    private function bulk_action_handle($event_ids, $action) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $event_ids = array_map('intval', $event_ids);
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';

        // Check if user has permission for all selected events
        if (!current_user_can('manage_options')) {
            $user_id = get_current_user_id();
            $owned_events = $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM $table_name WHERE id IN (" . implode(',', $event_ids) . ") AND event_manager_id = %d",
                $user_id
            ));
            
            if (count($owned_events) !== count($event_ids)) {
                wp_die(__('You do not have permission to perform this action on some of the selected events.', 'par-event'));
            }
        }

        switch ($action) {
            case 'delete':
                if (!current_user_can('delete_events')) {
                    wp_die(__('You do not have permission to delete events.', 'par-event'));
                }
                foreach ($event_ids as $event_id) {
                    $wpdb->delete($table_name, array('id' => $event_id));
                }
                break;

            case 'approve':
            case 'reject':
                $status = $action === 'approve' ? 'approved' : 'rejected';
                foreach ($event_ids as $event_id) {
                    $wpdb->update(
                        $table_name,
                        array('status' => $status),
                        array('id' => $event_id)
                    );

                    // Send email notification
                    $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
                    if ($event) {
                        $event_manager = get_user_by('id', $event->event_manager_id);
                        if ($event_manager) {
                            wp_mail(
                                $event_manager->user_email,
                                'Event ' . ucfirst($status),
                                sprintf('Your event "%s" has been %s.', $event->title, $status)
                            );
                        }
                    }
                }
                break;
        }

        wp_redirect(add_query_arg(array('page' => 'events'), admin_url('admin.php')));
        exit;
    }

    private function approve_event($event_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';
        
        $wpdb->update(
            $table_name,
            array('status' => 'approved'),
            array('id' => $event_id),
            array('%s'),
            array('%d')
        );

        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
        if ($event) {
            $event_manager = get_user_by('id', $event->event_manager_id);
            if ($event_manager) {
                wp_mail(
                    $event_manager->user_email,
                    'Event Approved',
                    'Your event "' . $event->title . '" has been approved.'
                );
            }
        }


        wp_safe_redirect(admin_url('admin.php?page=events'));
        exit;
    }

    private function reject_event($event_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';
        
        $wpdb->update(
            $table_name,
            array('status' => 'rejected'),
            array('id' => $event_id),
            array('%s'),
            array('%d')
        );

        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
        if ($event) {
            $event_manager = get_user_by('id', $event->event_manager_id);
            if ($event_manager) {
                wp_mail(
                    $event_manager->user_email,
                    'Event Rejected',
                    'Your event "' . $event->title . '" has been rejected.'
                );
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=events'));
        exit;
    }
} 