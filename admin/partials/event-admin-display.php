<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'events';
$is_edit = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['event']);
$is_new = isset($_GET['action']) && $_GET['action'] === 'new';
$event = null;

if ($is_edit) {
    $event_id = intval($_GET['event']);
    $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $event_id));
    
    if (!$event || (!current_user_can('administrator') && $event->event_manager_id != get_current_user_id())) {
        wp_die(__('You do not have permission to edit this event.'));
    }
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Events', 'par-event'); ?></h1>
    <?php if (!$is_edit && !$is_new): ?>
    <a href="?page=events&action=new" class="page-title-action"><?php _e('Add New', 'par-event'); ?></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <?php if ($is_edit || $is_new): ?>
    <div class="wrap">
        <h1><?php echo $is_edit ? __('Edit Event', 'par-event') : __('Add New Event', 'par-event'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('add_event', 'event_nonce'); ?>
            <?php if ($is_edit): ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event->id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="event_title"><?php _e('Event Title', 'par-event'); ?></label></th>
                    <td>
                        <input type="text" name="event_title" id="event_title" class="regular-text" required 
                            value="<?php echo $is_edit ? esc_attr($event->title) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_description"><?php _e('Description', 'par-event'); ?></label></th>
                    <td>
                        <textarea name="event_description" id="event_description" class="large-text" rows="5" required><?php 
                            echo $is_edit ? esc_textarea($event->description) : ''; 
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_date"><?php _e('Date', 'par-event'); ?></label></th>
                    <td>
                        <input type="date" name="event_date" id="event_date" required 
                            value="<?php echo $is_edit ? esc_attr($event->date) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_time"><?php _e('Time', 'par-event'); ?></label></th>
                    <td>
                        <input type="time" name="event_time" id="event_time" required 
                            value="<?php echo $is_edit ? esc_attr($event->time) : ''; ?>">
                    </td>
                </tr>
                <?php if (current_user_can('administrator')): ?>
                <tr>
                    <th scope="row"><label for="event_status"><?php _e('Status', 'par-event'); ?></label></th>
                    <td>
                        <select name="event_status" id="event_status">
                            <option value="pending" <?php echo $is_edit && $event->status === 'pending' ? 'selected' : ''; ?>><?php _e('Pending', 'par-event'); ?></option>
                            <option value="approved" <?php echo $is_edit && $event->status === 'approved' ? 'selected' : ''; ?>><?php _e('Approved', 'par-event'); ?></option>
                            <option value="rejected" <?php echo $is_edit && $event->status === 'rejected' ? 'selected' : ''; ?>><?php _e('Rejected', 'par-event'); ?></option>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" 
                    value="<?php echo $is_edit ? __('Update Event', 'par-event') : __('Add Event', 'par-event'); ?>">
                <a href="?page=events" class="button"><?php _e('Cancel', 'par-event'); ?></a>
            </p>
        </form>
    </div>
    <?php else: ?>
    <form method="post">
        <?php
        $event_list_table->search_box(__('Search Events', 'par-event'), 'search_id');
        $event_list_table->display();
        ?>
    </form>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#event_date').attr('min', today);
});
</script>

