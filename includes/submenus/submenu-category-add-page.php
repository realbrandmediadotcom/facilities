<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Check permissions
if (!facamen_user_can_create_plugin()) {
    echo '<div class="error notice"><p>' . esc_html__('You do not have permission to the add category options.', 'facilities-amenities') . '</p></div>';
    ?>
    <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
    <?php
    return;
}

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
            /* echo '<div class="notice notice-info"><p>' . esc_html__('Add another Option for, or use the Back to list to go back!', 'facilities-amenities') . '</p></div>'; */
           echo '<div class="notice notice-info"><p>' . 
                wp_kses_post(sprintf(
                    __('Add another Option for <strong>%s</strong>, or use the Back to list to go back!', 'facilities-amenities'),
                    esc_html($category_name)
                )) . 
                '</p></div>';

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
    <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>" class="button-secondary"><?php esc_html_e('Back to list', 'facilities-amenities'); ?></a>
</div>

<div class="wrap">
    <hr>
    <h4><?php esc_html_e('Start by selecting a category from the dropdown. Once selected, assign an option to that category.', 'facilities-amenities'); ?></h4>
    <div class="category-select-wrapper">
    <form method="post">        
        <select name="selected_category_id" id="selected_category_id" class="medium-input">
            <option value=""><?php echo esc_html__('-- Select Category --', 'facilities-amenities'); ?></option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->id); ?>" <?php selected($cat->id, $selected_id); ?>>
                    <?php echo esc_html($cat->category); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="select_category" class="button button-secondary" value="<?php esc_attr_e('Select', 'facilities-amenities'); ?>" />
    </form>
    </div>
    <?php
    // If category selected, show the form to add options    
    $should_show_form = (isset($_POST['select_category']) && !empty($_POST['selected_category_id'])) || isset($_POST['add_category_option']);

    if ($should_show_form):
        $selected_id = intval($_POST['selected_category_id'] ?? $_POST['category_id'] ?? 0); // works for both forms

        // Get category name from DB again for consistency
        $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $categories_table WHERE id = %d", $selected_id));

        if (empty($option_default_icon)) {
            $option_default_icon = 'fa-solid fa-hand-point-right';
        }

        if ($category):
            if (!empty($error_message)) echo $error_message;   
    ?>
        <h4>
            <?php 
            /* translators: %s: category name */
            printf(
                wp_kses_post(__('Adding Options for Category: <strong>%s</strong>', 'facilities-amenities')),
                esc_html($category->category)
            );
            ?>
        </h4>
        <p>
            <?php esc_html_e('Uses this default icon unless changed:', 'facilities-amenities'); ?> 
            <i id="fa-default-icon" class="<?php echo esc_attr($option_default_icon); ?>"></i>
        </p>        
        <form method="post">
            <input type="hidden" name="category_id" value="<?php echo esc_attr($category->id); ?>">
            <input type="hidden" name="category_name" value="<?php echo esc_attr($category->category); ?>">
            <p><?php esc_html_e('Want a different icon? Turn on the toggle to see more.', 'facilities-amenities'); ?></p>
            <table class="form-table">                
                <tr>
                    <th><label for="toggle_icon_picker"><?php esc_html_e('Toggle', 'facilities-amenities'); ?></label></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" id="toggle_icon_picker">
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>                
                <tr id="icon_picker_row" style="display:none;">                    
                    <th>
                        <label for="option_name"><?php esc_html_e('Choose a new icon', 'facilities-amenities'); ?></label>
                    </th>
                    <td>
                        <div>
                            <input type="hidden" name="option_icon" id="option_value_icon" value="<?php echo esc_attr($option_icon); ?>">
                            <div id="fa-icon-picker" class="fa-icon-grid">
                                <?php                              
                                $counter = 0;
                                $total = count($fa_icons);

                                echo '<div class="fa-icon-group">';
                                foreach ($fa_icons as $index => $icon) {
                                    $active = ($option_icon === 'fa-solid ' . $icon) ? 'active' : '';
                                    echo '<span class="fa-icon ' . esc_attr($active) . '" data-icon="fa-solid ' . esc_attr($icon) . '"><i class="fa-solid ' . esc_attr($icon) . '"></i></span>';
                                    $counter++;
                                    if ($counter % 20 === 0 && $index + 1 < $total) {
                                        echo '</div><div class="fa-icon-group">';
                                    }
                                }
                                echo '</div>';
                                ?>
                            </div>
                        </div>
                        <p>
                            <?php esc_html_e('This is your selected icon:', 'facilities-amenities'); ?> 
                            <i id="fa-selected-icon" class="<?php echo esc_attr($option_icon); ?>"></i>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="option_value"><?php esc_html_e('New Description', 'facilities-amenities'); ?></label></th>
                    <td><input type="text" name="option_value" id="option_value" class="long-input" required value="<?php echo esc_attr($option_value); ?>"></td>
                </tr>
            </table>                          
            <?php 
                if (facamen_user_can_create_plugin()) {
            ?>
            <p><input type="submit" name="add_category_option" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'facilities-amenities'); ?>"></p>
            <?php 
                }
            ?>
        </form>
    <?php
        endif;
    endif;
?>
</div>

<div class="wrap">
    <hr>
    <a href="<?php echo admin_url('admin.php?page=facamen-category-options'); ?>" class="space-top"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
</div>