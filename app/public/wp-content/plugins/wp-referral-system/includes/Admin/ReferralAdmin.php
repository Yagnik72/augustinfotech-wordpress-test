<?php
namespace WPReferralSystem\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
    require_once(ABSPATH . 'wp-admin/includes/template.php');
}

/**
 * Handles admin functionality
 */
class ReferralAdmin extends \WP_List_Table {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => __('Referral', 'wp-referral-system'),
            'plural' => __('Referrals', 'wp-referral-system'),
            'ajax' => false
        ));
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Referral System', 'wp-referral-system'),
            __('Referral System', 'wp-referral-system'),
            'manage_options',
            'wp-referral-system',
            array($this, 'render_admin_page'),
            'dashicons-groups',
            30
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_wp-referral-system' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wp-referral-system-admin',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/css/admin/admin.css',
            array(),
            WP_REFERRAL_SYSTEM_VERSION
        );

        wp_enqueue_script(
            'wp-referral-system-admin',
            WP_REFERRAL_SYSTEM_PLUGIN_URL . 'assets/js/admin/admin.js',
            array('jquery'),
            WP_REFERRAL_SYSTEM_VERSION,
            true
        );
    }

    /**
     * Get columns
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'user' => __('User', 'wp-referral-system'),
            'referral_user' => __('Referral User', 'wp-referral-system'),
            'commission' => __('Commission', 'wp-referral-system'),
            'created_at' => __('Date', 'wp-referral-system'),
            'actions' => __('Actions', 'wp-referral-system')
        );
    }

    /**
     * Get sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'user' => array('user', false),
            'referral_user' => array('referral_user', false),
            'commission' => array('commission', false),
            'created_at' => array('created_at', true)
        );
    }

    /**
     * Get bulk actions
     */
    public function get_bulk_actions() {
        return array(
            'delete' => __('Delete', 'wp-referral-system')
        );
    }

    /**
     * Prepare items
     */
    public function prepare_items() {
        global $wpdb;

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Get data
        $sql = "SELECT * FROM {$wpdb->prefix}referral_history";
        
        // Add search
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $search = sanitize_text_field($_REQUEST['s']);
            $sql .= $wpdb->prepare(" WHERE user_id IN (SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s) 
                                   OR referral_user_id IN (SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        // Add sorting
        $orderby = isset($_REQUEST['orderby']) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
        $sql .= " ORDER BY $orderby $order";

        // Get total items
        $total_items = $wpdb->get_var(str_replace('SELECT *', 'SELECT COUNT(*)', $sql));

        // Add pagination
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);

        $this->items = $wpdb->get_results($sql);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    /**
     * Column default
     */
    public function column_default($item, $column_name) {
        return $item->$column_name;
    }

    /**
     * Column user
     */
    public function column_user($item) {
        $user = get_user_by('id', $item->user_id);
        return $user ? $user->display_name : __('Unknown', 'wp-referral-system');
    }

    /**
     * Column referral user
     */
    public function column_referral_user($item) {
        $user = get_user_by('id', $item->referral_user_id);
        return $user ? $user->display_name : __('Unknown', 'wp-referral-system');
    }

    /**
     * Column commission
     */
    public function column_commission($item) {
        return $item->commission . '' . WP_REFERRAL_SYSTEM_CURRENCY_SYMBOL;
    }

    /**
     * Column created at
     */
    public function column_created_at($item) {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->created_at));
    }

    /**
     * Column actions
     */
    public function column_actions($item) {
        $actions = array(
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>',
                'wp-referral-system',
                'delete',
                $item->id,
                wp_create_nonce('delete_referral_' . $item->id),
                __('Are you sure you want to delete this referral?', 'wp-referral-system'),
                __('Delete', 'wp-referral-system')
            )
        );

        return $this->row_actions($actions);
    }

    /**
     * Column checkbox
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="referral[]" value="%s" />',
            $item->id
        );
    }

    /**
     * Get bulk actions
     */
    public function handle_actions() {
        global $wpdb;
    
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $id = absint($_GET['id']);
            $nonce = $_GET['_wpnonce'] ?? '';
    
            if (!wp_verify_nonce($nonce, 'delete_referral_' . $id)) {
                wp_die(__('Security check failed.', 'wp-referral-system'));
            }
    
            $table = $wpdb->prefix . 'referral_history';
            $wpdb->delete($table, ['id' => $id], ['%d']);
    
            wp_redirect(admin_url('admin.php?page=wp-referral-system&deleted=1'));
            exit;
        }
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $this->handle_actions(); 

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WP Referral System', 'wp-referral-system'); ?></h1>
            
            <form method="post">
                <?php
                $this->prepare_items();
                $this->search_box(__('Search Referrals', 'wp-referral-system'), 'search_id');
                $this->display();
                ?>
            </form>
        </div>
        <?php
    }
} 
