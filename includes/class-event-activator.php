<?php
class Event_Activator {
    public static function activate() {
        self::create_events_table();
        self::create_event_manager_role();
        self::add_capabilities_to_admin();
    }

    private static function create_events_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text NOT NULL,
            event_manager_id bigint(20) NOT NULL,
            date date NOT NULL,
            time time NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_event_manager_role() {
        add_role(
            'event_manager',
            'Event Manager',
            array(
                'read' => true,
                'edit_events' => true,
                'delete_events' => true,
                'list_events' => true,
                'edit_others_events' => false,
                'delete_others_events' => false,
                'publish_events' => true
            )
        );
    }

    private static function add_capabilities_to_admin() {
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('edit_events');
            $admin_role->add_cap('delete_events');
            $admin_role->add_cap('list_events');
            $admin_role->add_cap('edit_others_events');
            $admin_role->add_cap('delete_others_events');
        }
    }
} 