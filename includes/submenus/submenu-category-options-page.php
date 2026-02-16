<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;

$fa_icons = ['fa-user', 'fa-people-roof', 'fa-heart', 'fa-camera', 'fa-home', 'fa-bolt', 'fa-car', 'fa-book', 'fa-envelope',
    'fa-campground', 'fa-tent', 'fa-tents', 'fa-tree', 'fa-mountain', 'fa-route', 'fa-hand-point-right', 'fa-fire', 'fa-fire-alt', 'fa-water', 
    'fa-faucet', 'fa-toilet-paper', 'fa-shower', 'fa-bath', 'fa-plug', 'fa-wifi', 'fa-dog', 'fa-paw', 'fa-hiking', 'fa-fish', 'fa-swimmer', 'fa-binoculars',
    'fa-trailer', 'fa-caravan', 'fa-map', 'fa-map-marked-alt', 'fa-first-aid', 'fa-info-circle', 'fa-comments', 'fa-bell', 'fa-check', 'fa-phone',
    'fa-paperclip', 'fa-mug-hot', 'fa-umbrella', 'fa-gift', 'fa-bicycle', 'fa-anchor', 'fa-snowflake', 'fa-compass', 'fa-frog', 'fa-map-signs', 
    'fa-trash', 'fa-music', 'fa-face-smile', 'fa-trophy', 'fa-arrow-right', 'fa-circle-xmark', 'fa-star', 'fa-thumbtack', 'fa-thumbs-down'];
//, 'fa-bottle-water'

$selected_id = intval($_POST['selected_category_id'] ?? $_POST['category_id'] ?? 0);
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : '';
$category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
$option_icon = isset($_POST['option_icon']) ? sanitize_text_field($_POST['option_icon']) : '';
$option_value = isset($_POST['option_value']) ? sanitize_text_field($_POST['option_value']) : '';
$error_message = '';

// Get categories from the table
$categories_table = $wpdb->prefix . 'facamen_categories';
$categories = $wpdb->get_results("SELECT id, category FROM $categories_table");

// Get value from the table
$fa_icon_class = facamen_get_option('value', '');

$table_name = $wpdb->prefix . 'facamen_categoryoptions';

if (isset($_POST['update_item'])) {
    $id = intval($_POST['id']);
    $category_id = intval($_POST['category_id']);

    $enable_value = isset($_POST['enable_categoryoptions']) ? 'yes' : 'no';

    $data = [
        'categoryname' => sanitize_text_field($_POST['categoryname']),
        'name' => sanitize_text_field($_POST['option_icon']),
        'value'        => sanitize_textarea_field($_POST['value']),
        'enable'       => $enable_value,
    ];

    $wpdb->update($table_name, $data, ['id' => $id]);

    echo '<div class="updated"><p>' . esc_html__('Item updated successfully.', 'facilities-amenities') . '</p></div>';
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize_text_field($_GET['action']);
    $id = intval($_GET['id']);
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;    

    if ($action === 'delete') {
        if (facamen_user_can_delete_plugin()) {
            $wpdb->update($table_name, ['trashed' => 1], ['id' => $id]);
            echo '<div class="updated"><p>' . esc_html__('Item moved to trash.', 'facilities-amenities') . '</p></div>';
        } else {
            // User does not have permission to delete
            facamen_admin_notice(__('You do not have permission to move this item to trash.', 'facilities-amenities'), 'error');
        }
    }

    if ($action === 'restore') {
        if (facamen_user_can_delete_plugin()) {
            $wpdb->update($table_name, ['trashed' => 0], ['id' => $id]);
            echo '<div class="updated"><p>' . esc_html__('Item restored successfully.', 'facilities-amenities') . '</p></div>';            
        }  else {
            // User does not have permission to restore
            facamen_admin_notice(__('You do not have permission to restore this item.', 'facilities-amenities'), 'error');
        }
    }
  

    if ($action === 'delete_perm') {
        if (facamen_user_can_delete_plugin()) {
            $wpdb->delete($table_name, ['id' => $id]);
            echo '<div class="updated"><p>' . esc_html__('Item permanently deleted.', 'facilities-amenities') . '</p></div>';
        } else {
            // User does not have permission to delete perm
            facamen_admin_notice(__('You do not have permission to delete this item.', 'facilities-amenities'), 'error');
        }
    }

    if ($action === 'edit') {
        // check permissions
        if (!facamen_user_can_edit_plugin()) {
            echo '<div class="error notice"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>';
            ?>
            <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
            <?php
            return;
        }

        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        if ($item) {
            ?>
            <h4><?php esc_html_e('Edit Category Option', 'facilities-amenities'); ?></h4>
            <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>" class="button-secondary space-bottom"><?php esc_html_e('Back to list', 'facilities-amenities'); ?></a>
            <hr>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo esc_attr($item->id); ?>">
                <input type="hidden" name="category_id" value="<?php echo esc_attr($category_id); ?>">
                <input type="hidden" name="view" value="<?php echo esc_attr($_GET['view'] ?? 'all'); ?>">

                <!-- Category -->
                <p class="facamen-form-field">
                    <label for="categoryname"><?php esc_html_e('Category', 'facilities-amenities'); ?></label>
                    <input type="text" id="categoryname" class="long-input" name="categoryname" value="<?php echo esc_attr($item->categoryname); ?>" readonly>
                </p>

                <!-- Icon Picker -->
                <div class="facamen-form-field">
                    <label for="edit_option_value_icon"><?php esc_html_e('Icon', 'facilities-amenities'); ?></label>

                    <input type="hidden" name="option_icon" id="edit_option_value_icon" value="<?php echo esc_attr($item->name); ?>">

                    <div id="edit-fa-icon-picker" class="fa-icon-grid">
                        <?php
                        foreach ($fa_icons as $index => $icon) {
                            $full_icon_class = 'fa-solid ' . $icon;
                            $active = ($item->name === $full_icon_class) ? 'active' : '';
                            echo '<span class="fa-icon ' . esc_attr($active) . '" data-icon="' . esc_attr($full_icon_class) . '"><i class="' . esc_attr($full_icon_class) . '"></i></span>';
                        }
                        ?>
                    </div>

                    <div class="facamen-selected-icon facamen-topandbottom">
                        <span><?php _e('Currently saved icon:', 'facilities-amenities'); ?></span>
                        <i id="edit-fa-selected-icon" class="<?php echo esc_attr($item->name); ?>"></i>
                    </div>
                </div>

                <!-- Description -->
                <div class="facamen-form-field">
                    <label for="value"><?php esc_html_e('Description', 'facilities-amenities'); ?></label>
                    <input type="text" class="long-input" id="value" name="value" value="<?php echo esc_textarea($item->value); ?>" required> 
                </div>

                <!-- Enable / Disable -->
                <div class="facamen-form-field facamen-topandbottom">
                    <label for="enable_categoryoptions">
                        <input type="checkbox" id="enable_categoryoptions" name="enable_categoryoptions" value="1"
                            <?php checked($item->enable, 'yes'); ?>>
                        <?php esc_html_e('Enable this category option to display it on the site.', 'facilities-amenities'); ?>
                    </label>
                </div>

                <!-- Submit -->
                <p>
                    <input type="submit" name="update_item" class="button button-primary space-bottom" value="<?php esc_attr_e('Save Changes', 'facilities-amenities'); ?>">
                </p>
            </form>
            <hr>
            <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
            <?php
        }
        return;
    }    
}

if (isset($_POST['add_category_option'])) {
    $options_table = $wpdb->prefix . 'facamen_categoryoptions';

    if (empty($option_icon)) {
        $option_icon = 'fa-solid fa-hand-point-right';
    }

    // Validate    
    if (empty($option_value)) {
        $error_message = '<div class="error"><p><strong>' . esc_html__('Error:', 'facilities-amenities') . '</strong> ' 
                 . esc_html__('Please enter a description.', 'facilities-amenities') 
                 . '</p></div>';
    } else { 
        $inserted = $wpdb->insert(
            $options_table,
            [
                'categoryid'    => $category_id,
                'categoryname'  => $category_name,
                'name'          => $option_icon,
                'value'         => $option_value
            ],
            ['%d', '%s', '%s', '%s']
        );

        if ($inserted) {
            echo '<div class="updated"><p>' . esc_html__('Option added successfully!', 'facilities-amenities') . '</p></div>';

            // Clear form fields after success
            $option_icon = '';
            $option_value = '';
        } else {
            $error_message = '<div class="error"><p>' . esc_html__('Failed to add option.', 'facilities-amenities') . '</p></div>';
        }
    }
}
?>
<div class="wrap">
    <h4><?php esc_html_e('Add New Category Options', 'facilities-amenities'); ?></h4>      
    <a href="<?php echo admin_url('admin.php?page=facamen-category-add'); ?>" class="button-secondary"><?php esc_html_e('Add Category Options', 'facilities-amenities'); ?></a>
    <div class="facamen-topandbottom"><?php esc_html_e('To view all Categories, clear out the Search Textbox.', 'facilities-amenities'); ?></div>
    <?php
    // Include the class if not already included
    if (!class_exists('Facamen_Categoryoptions_Table')) {
        require_once plugin_dir_path(__FILE__) . '../classes/class-facamen-categoryoptions-table.php';
    }                    

    $filter_category_id = !empty($selected_id) ? $selected_id : 0;

    $search_term = $_POST['s'] ?? '';
    $table = new Facamen_Categoryoptions_Table($filter_category_id, $search_term);   

    $table->process_bulk_action(); 
    $table->prepare_items();       

    // Render the table inside a form (for bulk actions or future extensions)
    ?>    
    <form method="post">
        <input type="hidden" name="selected_category_id" value="<?php echo esc_attr($filter_category_id); ?>">
        
        <p class="search-box">
            <label class="screen-reader-text" for="category-option-search-input"><?php esc_html_e('Search Category Options:', 'facilities-amenities'); ?></label>
            <input type="search" id="category-option-search-input" name="s" value="<?php echo esc_attr($search_term); ?>">
            <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Category', 'facilities-amenities'); ?>">
        </p>      
        <?php
        foreach ($_GET as $key => $value) {
            if (is_array($value)) continue;
            printf('<input type="hidden" name="%s" value="%s" />', esc_attr($key), esc_attr($value));
        }
        ?>
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">        
        <?php 
        echo '<ul class="subsubsub">';
        echo '<li>' . implode( ' | </li><li>', $table->get_views() ) . '</li>';
        echo '</ul>';
        $table->display(); 
        ?>
    </form> 
</div>
    <?php 
        //Check and show the success message
        if (get_transient('facamen_bulk_delete_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items moved to trash successfully.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_delete_success');
        }

        // Success messages
        if (get_transient('facamen_bulk_delete_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items moved to trash.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_delete_success');
        }

        if (get_transient('facamen_bulk_restore_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items restored.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_restore_success');
        }

        if (get_transient('facamen_bulk_delete_perm_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items permanently deleted.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_delete_perm_success');
        }