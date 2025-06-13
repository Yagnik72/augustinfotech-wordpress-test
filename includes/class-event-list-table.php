<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Event_List_Table extends WP_List_Table {
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_items();

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

        $this->items = $this->get_items($per_page, $current_page);
    }

    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'event_manager' => 'Event Manager',
            'date' => 'Date',
            'time' => 'Time',
            'status' => 'Status',
            'created_at' => 'Created At'
        );
    }

    public function get_sortable_columns() {
        return array(
            'id' => array('id', true),
            'title' => array('title', false),
            'date' => array('date', false),
            'status' => array('status', false)
        );
    }

    private function get_total_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';
        
        $where = $this->get_where_clause();
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
    }

    private function get_items($per_page, $current_page) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'events';
        
        $where = $this->get_where_clause();
        $orderby = isset($_REQUEST['orderby']) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
        
        $offset = ($current_page - 1) * $per_page;
        
        $sql = $wpdb->prepare(
            "SELECT e.*, u.display_name as manager_name 
            FROM $table_name e 
            LEFT JOIN {$wpdb->users} u ON e.event_manager_id = u.ID 
            $where 
            ORDER BY $orderby $order 
            LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        
        return $wpdb->get_results($sql);
    }

    private function get_where_clause() {
        global $wpdb;
        $where = array();
        
        // Filter by status
        if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
            $status = sanitize_text_field($_REQUEST['status']);
            $where[] = $wpdb->prepare("status = %s", $status);
        }
        
        // Search by title
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $search = sanitize_text_field($_REQUEST['s']);
            $where[] = $wpdb->prepare("title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
        
        // If user is event manager, only show their events
        if (current_user_can('event_manager') && !current_user_can('administrator')) {
            $where[] = $wpdb->prepare("event_manager_id = %d", get_current_user_id());
        }
        
        return !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    public function column_default($item, $column_name) {
        return $item->$column_name;
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="event[]" value="%s" />',
            $item->id
        );
    }

    public function column_title($item) {
        $actions = array();
        
        // Add edit action if user has permission
        if (current_user_can('administrator') || $item->event_manager_id == get_current_user_id()) {
            $actions['edit'] = sprintf('<a href="?page=events&action=edit&event=%s">Edit</a>', $item->id);
        }
        
        // Add delete action if user has permission
        if (current_user_can('administrator') || $item->event_manager_id == get_current_user_id()) {
            $actions['delete'] = sprintf('<a href="?page=events&action=delete&event=%s" onclick="return confirm(\'Are you sure?\');">Delete</a>', $item->id);
        }
        
        // Add approve/reject actions for administrators
        if (current_user_can('administrator') && $item->status === 'pending') {
            $actions['approve'] = sprintf(
                '<a href="?page=events&action=approve&event=%s&_wpnonce=%s">Approve</a>',
                $item->id,
                wp_create_nonce('approve_event_' . $item->id)
            );
            $actions['reject'] = sprintf(
                '<a href="?page=events&action=reject&event=%s&_wpnonce=%s">Reject</a>',
                $item->id,
                wp_create_nonce('reject_event_' . $item->id)
            );
        }

        return sprintf('%1$s %2$s',
            $item->title,
            $this->row_actions($actions)
        );
    }

    public function column_status($item) {
        $status_labels = array(
            'pending' => '<span class="status-pending">Pending</span>',
            'approved' => '<span class="status-approved">Approved</span>',
            'rejected' => '<span class="status-rejected">Rejected</span>'
        );
        
        return isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status;
    }

    public function column_event_manager($item) {
        return $item->manager_name;
    }

    public function get_bulk_actions() {
        $actions = array();
        
        if (current_user_can('manage_options')) {
            $actions['approve'] = __('Approve', 'par-event');
            $actions['reject'] = __('Reject', 'par-event');
        }
        
        if (current_user_can('delete_events')) {
            $actions['delete'] = __('Delete', 'par-event');
        }
        
        return $actions;
    }
} 