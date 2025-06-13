

<div class="wrap">
    <h1><?php echo isset($_GET['event']) ? __('Edit Event', 'par-event') : __('Add New Event', 'par-event'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('add_event', 'event_nonce'); ?>
        
        <?php if (isset($_GET['event'])): ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($_GET['event']); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="event_title"><?php _e('Event Title', 'par-event'); ?></label>
                </th>
                <td>
                    <input type="text" name="event_title" id="event_title" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="event_description"><?php _e('Description', 'par-event'); ?></label>
                </th>
                <td>
                    <textarea name="event_description" id="event_description" class="large-text" rows="5" required></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="event_date"><?php _e('Date', 'par-event'); ?></label>
                </th>
                <td>
                    <input type="date" name="event_date" id="event_date" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="event_time"><?php _e('Time', 'par-event'); ?></label>
                </th>
                <td>
                    <input type="time" name="event_time" id="event_time" required>
                </td>
            </tr>
            <?php if (current_user_can('manage_options')): ?>
            <tr>
                <th scope="row">
                    <label for="event_status"><?php _e('Status', 'par-event'); ?></label>
                </th>
                <td>
                    <select name="event_status" id="event_status">
                        <option value="pending"><?php _e('Pending', 'par-event'); ?></option>
                        <option value="approved"><?php _e('Approved', 'par-event'); ?></option>
                        <option value="rejected"><?php _e('Rejected', 'par-event'); ?></option>
                    </select>
                </td>
            </tr>
            <?php endif; ?>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($_GET['event']) ? __('Update Event', 'par-event') : __('Add Event', 'par-event'); ?>">
            <a href="<?php echo admin_url('admin.php?page=events'); ?>" class="button"><?php _e('Cancel', 'par-event'); ?></a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#event_date').attr('min', today);
});
</script> 