<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'facamen_categories';

// Handle form submission to add new category
if (
    isset($_POST['facamen_add_category']) &&
    isset($_POST['facamen_add_category_nonce']) &&
    wp_verify_nonce($_POST['facamen_add_category_nonce'], 'facamen_add_category_action')
) {
    $category      = sanitize_text_field($_POST['category'] ?? '');
    $title         = sanitize_text_field($_POST['title'] ?? '');
    $buttontext    = sanitize_text_field($_POST['buttontext'] ?? '');
    $buttonlinkurl = esc_url_raw($_POST['buttonlinkurl'] ?? '');
    $imagelinkurl  = esc_url_raw($_POST['imagelinkurl'] ?? '');    

    if (!empty($category) && !empty($title)) {
        // Case-insensitive check for duplicates
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE category COLLATE utf8mb4_general_ci = %s",
                $category
            )
        );

        if ($existing > 0) {
            echo '<div class="error notice"><p>' . esc_html__('A category with that name already exists.', 'facilities-amenities') . '</p></div>';
        } else {  
            $enable = isset($_POST['enable_catorgies']) && $_POST['enable_catorgies'] == '1' ? 'yes' : 'no';          
            $wpdb->insert($table_name, [
                'category'       => $category,
                'title'          => $title,
                'buttontext'     => $buttontext,
                'buttonlinkurl'  => $buttonlinkurl,
                'opennewtab'     => 'yes', // set automatically
                'imagelinkurl'   => $imagelinkurl,
                'enable'         => $enable,                
                'created_at'     => current_time('mysql')
            ]);
            echo '<div class="updated notice"><p>' . esc_html__('Category added successfully.', 'facilities-amenities') . '</p></div>';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) &&
    facamen_user_can_delete_plugin()
) {
    $id = intval($_GET['id']);
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'facamen_delete_category_' . $id)) {
        // Manually delete category options first
        $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';
        $wpdb->delete($category_options_table, ['categoryid' => $id]);

        // Mark as trashed instead of deleting
        $wpdb->update($table_name, ['trashed' => 1], ['id' => $id]);

        echo '<div class="updated notice"><p>' . esc_html__('Category moved to trash.', 'facilities-amenities') . '</p></div>';
    } else {
        echo '<div class="error notice"><p>' . esc_html__('Delete failed: security check', 'facilities-amenities') . '</p></div>';
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && !facamen_user_can_delete_plugin()) {
    echo '<div class="error notice"><p>' . esc_html__('You do not have permission to move this category to trash.', 'facilities-amenities') . '</p></div>';
}

if (isset($_POST['facamen_edit_category']) && isset($_POST['facamen_edit_category_nonce']) && wp_verify_nonce($_POST['facamen_edit_category_nonce'], 'facamen_edit_category_action_' . intval($_POST['id']))) {
    $id            = intval($_POST['id']);
    $category      = trim(sanitize_text_field($_POST['category']));
    $title         = sanitize_text_field($_POST['title']);
    $buttontext    = sanitize_text_field($_POST['buttontext'] ?? '');
    $buttonlinkurl = esc_url_raw($_POST['buttonlinkurl'] ?? '');
    $imagelinkurl  = esc_url_raw($_POST['imagelinkurl'] ?? '');    

    if ($category && $title) {
        // Case-insensitive duplicate check (excluding current ID)
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE category COLLATE utf8mb4_general_ci = %s AND id != %d",
                $category,
                $id
            )
        );

        if ($existing > 0) {
            echo '<div class="error notice"><p>' . esc_html__('A category with that name already exists.', 'facilities-amenities') . '</p></div>';
        } else {
           
            $enable = isset($_POST['enable_catorgies']) && $_POST['enable_catorgies'] == '1' ? 'yes' : 'no';
            $enable_button_options = isset($_POST['enable_button_fields']) && $_POST['enable_button_fields'] == '1' ? 'yes' : 'no';
            $opennewtab = isset($_POST['enable_opennewtab']) && $_POST['enable_opennewtab'] == '1' ? 'yes' : 'no';

            $wpdb->update(
                $table_name,
                [
                    'category'      => $category,
                    'title'         => $title,
                    'buttontext'    => $buttontext,
                    'buttonlinkurl' => $buttonlinkurl,
                    'opennewtab'    => $opennewtab,
                    'imagelinkurl'  => $imagelinkurl,
                    'enable'        => $enable,
                    'enable_button_options' => $enable_button_options,
                ],
                ['id' => $id]
            );
            echo '<div class="updated notice"><p>' . esc_html__('Category updated.', 'facilities-amenities') . '</p></div>';
        }
    } else {
        echo '<div class="error notice"><p>' . esc_html__('Category and Title required.', 'facilities-amenities') . '</p></div>';
    }
} elseif (isset($_POST['facamen_edit_category'])) {
    echo '<div class="error notice"><p>' . esc_html__('Security check failed on edit.', 'facilities-amenities') . '</p></div>';
}

// Load table class
require_once plugin_dir_path(dirname(__FILE__)) . '../includes/classes/class-facamen-categories-table.php';
$table = new Facamen_Categories_Table();
$table->prepare_items();

// If clicking "Edit" link
if (isset($_GET['action'], $_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);

    // check permissions
    if (!facamen_user_can_edit_plugin()) {
        echo '<div class="error notice"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>';
        ?>
        <a href="<?php echo admin_url('admin.php?page=facamen-categories'); ?>"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
        <?php
        return;
    }

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id), ARRAY_A);
    if (!$row) {
        echo '<div class="error notice"><p>' . esc_html__('Invalid category ID.', 'facilities-amenities') . '</p></div>';
    } else {
        ?>
        <div class="wrap">
            <h4><?php esc_html_e('Edit Category', 'facilities-amenities'); ?></h4>
            <a href="<?php echo admin_url('admin.php?page=facamen-categories'); ?>" class="button-secondary space-bottom"><?php esc_html_e('Back to list', 'facilities-amenities'); ?></a>
            <hr>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>">
                <table class="form-table">
                    <?php foreach (['category','title','buttontext','buttonlinkurl','imagelinkurl'] as $field): ?>
                        <tr>
                            <th>
                                <?php
                                    $field_labels = [
                                        'category' => 'Category Name',
                                        'title' => 'Title',
                                        'buttontext' => 'Button Text',
                                        'buttonlinkurl' => 'Button Link URL',
                                        'imagelinkurl' => 'Image URL'
                                    ];
                                ?>
                                <label for="<?php echo $field; ?>"><?php echo esc_html($field_labels[$field]); ?></label>
                            </th>
                            <td>                                                         
                                <?php if ($field === 'buttontext'): ?>           
                                    <input type="text" name="buttontext" id="buttontext" class="regular-text" 
                                        value="<?php echo esc_attr($row['buttontext']); ?>" <?php echo ($row['enable_button_options'] !== 'yes') ? 'readonly' : ''; ?>>
                                        <input type="checkbox" 
                                                id="enable_button_fields" 
                                                name="enable_button_fields" 
                                                value="1"
                                                class="moveRight"
                                                <?php checked($row['enable_button_options'], 'yes'); ?>>
                                        <label for="enable_button_fields">
                                            <?php esc_html_e('Enable to edit button fields', 'facilities-amenities'); ?>
                                        </label>                                        
                                <?php elseif ($field === 'buttonlinkurl'): ?>
                                    <input type="url" name="buttonlinkurl" id="buttonlinkurl" class="regular-text" 
                                        value="<?php echo esc_attr($row['buttonlinkurl']); ?>" <?php echo ($row['enable_button_options'] !== 'yes') ? 'readonly' : ''; ?>>
                                    <input type="checkbox"
                                            id="enable_opennewtab"
                                            name="enable_opennewtab"
                                            value="1"
                                            class="moveLeft"
                                            <?php checked($row['opennewtab'], 'yes'); ?>>
                                    <label for="enable_opennewtab">
                                        <?php esc_html_e('Open Link in New Tab', 'facilities-amenities'); ?>
                                    </label> 
                                <?php elseif ($field === 'imagelinkurl'): ?>                                   
                                    <input name="imagelinkurl" type="url" class="regular-text imagelinkurl-input" value="<?php echo esc_attr($row['imagelinkurl']); ?>" readonly>
                                    <input type="button" class="button button-secondary upload-image-button" value="<?php esc_attr_e('Upload Image', 'facilities-amenities'); ?>">
                                    <div class="image-preview-wrapper" style="margin-top:10px;">
                                        <img class="image-preview" src="<?php echo esc_url($row['imagelinkurl']); ?>" alt="Image Preview"
                                            style="max-width:300px; height:auto; <?php echo empty($row['imagelinkurl']) ? 'display:none;' : ''; ?>" />
                                    </div>
                                <?php else: ?>
                                    <!-- <input type="text" name="<?php //echo $field; ?>" id="<?php //echo $field; ?>" class="regular-text" 
                                           value="<?php //echo esc_attr($row[$field]); ?>"> -->
                                    <input type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" class="regular-text" 
                                        value="<?php echo esc_attr($row[$field]); ?>"
                                        <?php echo ($field === 'category') ? 'readonly' : ''; ?>
                                    >
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php
                wp_nonce_field('facamen_edit_category_action_' . $row['id'], 'facamen_edit_category_nonce');
                ?>                
                    <!-- Enable / Disable -->
                    <div class="facamen-form-field facamen-topandbottom">
                        <label for="enable_catorgies">
                            <input type="checkbox" id="enable_catorgies" name="enable_catorgies" value="1"
                                <?php checked($row['enable'], 'yes'); ?>>
                            <?php esc_html_e('Enable this category to display it on the site.', 'facilities-amenities'); ?>
                        </label>
                    </div>
                <?php
                submit_button(__('Save Changes', 'facilities-amenities'), 'primary', 'facamen_edit_category');
                ?>
            </form>
            <hr>
            <a href="<?php echo admin_url('admin.php?page=facamen-categories'); ?>"><?php esc_html_e('← Back to list', 'facilities-amenities'); ?></a>
        </div>
        <?php
        return;
    }
}

// Handle restore action
if (isset($_GET['action']) && $_GET['action'] === 'restore' && isset($_GET['id']) && facamen_user_can_delete_plugin()) {
    $id = intval($_GET['id']);
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'facamen_restore_category_' . $id)) {
        $wpdb->update($table_name, ['trashed' => 0], ['id' => $id]);
        echo '<div class="updated notice"><p>' . esc_html__('Category restored successfully.', 'facilities-amenities') . '</p></div>';
    } else {
        echo '<div class="error notice"><p>' . esc_html__('Restore failed: security check', 'facilities-amenities') . '</p></div>';
    }
}

// Handle permanent delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete_permanently' && isset($_GET['id']) && facamen_user_can_delete_plugin()) {
    $id = intval($_GET['id']);
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'facamen_delete_perm_category_' . $id)) {
        // First delete related options
        $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';
        $wpdb->delete($category_options_table, ['categoryid' => $id]);

        // Then delete the category permanently
        $wpdb->delete($table_name, ['id' => $id]);

        echo '<div class="updated notice"><p>' . esc_html__('Category permanently deleted.', 'facilities-amenities') . '</p></div>';
    } else {
        echo '<div class="error notice"><p>' . esc_html__('Permanent delete failed: security check.', 'facilities-amenities') . '</p></div>';
    }
}

// Load the table
require_once plugin_dir_path(dirname(__FILE__)) . '../includes/classes/class-facamen-categories-table.php';
$table = new Facamen_Categories_Table();

$table->process_bulk_action();
$table->prepare_items();

// Always define the default spacer image
$default_img = plugins_url('/img/spacer.png', dirname(__FILE__));
?>

<div class="wrap">
    <?php if (facamen_user_can_create_plugin()) : ?>
    <h4><?php esc_html_e('Add New Category', 'facilities-amenities'); ?></h4>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="category"><?php esc_html_e('Category', 'facilities-amenities'); ?></label></th>
                <td><input name="category" type="text" id="category" class="regular-text" required></td>
                <th scope="row"><label for="buttontext"><?php esc_html_e('Button Text', 'facilities-amenities'); ?></label></th>
                <td><div class="red-label"><?php esc_html_e('optional', 'facilities-amenities'); ?></div><input name="buttontext" type="text" id="buttontext" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="title"><?php esc_html_e('Title', 'facilities-amenities'); ?></label></th>
                <td><input name="title" type="text" id="title" class="regular-text" required></td>
                <th scope="row"><label for="buttonlinkurl"><?php esc_html_e('Button Link URL', 'facilities-amenities'); ?></label>
                </th>
                <td>
                    <input name="buttonlinkurl" type="url" id="buttonlinkurl" class="regular-text"><div class="red-label"><?php esc_html_e('optional', 'facilities-amenities'); ?></div>                    
                </td>             
            </tr>            
            <tr>
                <th scope="row"><label for="imagelinkurl"><?php esc_html_e('Image URL', 'facilities-amenities'); ?></label></th>
                <td>
                     <input 
                        name="imagelinkurl" 
                        type="url" 
                        class="regular-text imagelinkurl-input" 
                        value="<?php echo !empty($row['imagelinkurl']) ? esc_attr($row['imagelinkurl']) : $default_img; ?>" 
                        placeholder="<?php $default_img; ?>" 
                        readonly
                    >
                    <input type="button" class="button button-secondary upload-image-button" value="<?php esc_attr_e('Upload Image', 'facilities-amenities'); ?>">

                    <div class="image-preview-wrapper" style="margin-top:10px;">
                        <img class="image-preview" src="" alt="Image Preview" style="max-width:300px; height:auto; display:none;" />
                    </div>           
                </td>                
            </tr>
            <tr>
                <th scope="row"><label for="enable_catorgies"><?php esc_html_e('Enable Category', 'facilities-amenities'); ?></label></th>
                <td>
                    <input type="checkbox" id="enable_catorgies" name="enable_catorgies" value="1">
                    <label for="enable_catorgies"><?php esc_html_e('Check to enable this category', 'facilities-amenities'); ?></label>
                </td>
            </tr>
        </table>
        <?php
        // Nonce for security
        wp_nonce_field('facamen_add_category_action', 'facamen_add_category_nonce');
        submit_button(__('Save Changes', 'facilities-amenities'), 'primary', 'facamen_add_category');
        ?>
    </form>
    <hr>    
    <?php endif; ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="facamen-categories" />        
        <?php
        // Render search box
        $table->search_box(__('Search Categories', 'facilities-amenities'), 'facamen_category');
        if (!empty($_GET['s'])): ?>
            <input type="submit" value="<?php esc_attr_e('See All Records', 'facilities-amenities'); ?>" class="button" onclick="document.getElementsByName('s')[0].value='';" />
        <?php endif; ?>
    </form>
    <form method="post">
        <?php 
            echo $table->views();
            $table->display(); 
        ?>
    </form>
</div>
    <?php 
        if (get_transient('facamen_bulk_delete_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items moved to trash.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_delete_success');
        }

        if (get_transient('facamen_bulk_restore_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items restored successfully.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_restore_success');            
        }

        if (get_transient('facamen_bulk_delete_perm_success')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Selected items permanently deleted.', 'facilities-amenities') . '</p></div>';
            delete_transient('facamen_bulk_delete_perm_success');
        }