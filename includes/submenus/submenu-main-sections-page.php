<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$can_edit = facamen_user_can_edit_plugin();
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'top';
$can_delete = facamen_user_can_delete_plugin();
$is_real_admin = facamen_user_is_real_admin();

// save_top button
if (isset($_POST['save_top']) &&
    check_admin_referer('facamen_save_top') &&
    /* facamen_user_can_edit_plugin() */
    $can_edit
) {
    $top_title = sanitize_text_field($_POST['top_title']);
    $top_description = sanitize_textarea_field($_POST['top_description']);

    // Check if both fields are filled
    if (empty($top_title) || empty($top_description)) {
        echo '<div class="error"><p>' . esc_html__('Both Top Title and Top Description are required. Nothing was saved.', 'facilities-amenities') . '</p></div>';
    } else {
        facamen_set_option('top_title', $top_title);
        facamen_set_option('top_description', $top_description);

        echo '<div class="updated"><p>' . esc_html__('Top Section settings saved.', 'facilities-amenities') . '</p></div>';
    }
} elseif (isset($_POST['save_top']) &&
          check_admin_referer('facamen_save_top') &&
          /* !facamen_user_can_edit_plugin() */
          !$can_edit
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>'; 
}

// save_two_button
if (isset($_POST['save_two_bottom']) &&
    check_admin_referer('facamen_save_two_bottom') &&
    $can_edit
) {
    $bottom_two_title   = sanitize_text_field($_POST['bottom_two_title']);
    $bottom_two_visible = isset($_POST['bottom_two_visible']) ? 'yes' : 'no';
    $bottom_two_description = sanitize_text_field($_POST['bottom_two_description']);
    $bottom_two_buttontext = sanitize_text_field($_POST['bottom_two_buttontext']);
    $bottom_two_buttonlinkurl = sanitize_text_field($_POST['bottom_two_buttonlinkurl']);
    $open_two_new_tab   = isset($_POST['open_two_new_tab']) ? 'yes' : 'no';
    $backgroundImage = esc_url_raw($_POST['backgroundImage']);
    $bottom_two_title_color = sanitize_hex_color($_POST['bottom_two_title_color']);
    $bottom_two_description_color = sanitize_hex_color($_POST['bottom_two_description_color']);
    $backgroundTwoColor = sanitize_hex_color($_POST['backgroundTwoColor']);
    $backgroundOverlay = sanitize_hex_color($_POST['backgroundOverlay']); 
    $apply_overlay = isset($_POST['apply_overlay']) ? 'yes' : 'no';   

    $background_overlay_opacity = isset($_POST['background_overlay_opacity'])
    ? floatval($_POST['background_overlay_opacity'])
    : 0.45;

    $background_overlay_opacity = max(0, min(1, $background_overlay_opacity));

    // Handle checkbox to remove background image
    $remove_image = isset($_POST['check_to_remove_image']) && $_POST['check_to_remove_image'] === 'yes';

    if ($remove_image) {
        // Delete the image option from database
        facamen_delete_option('bottom_two_backgroundImage');
        $backgroundImage = ''; // clear for UI consistency
        /* echo '<div class="updated"><p>' . esc_html__('Background image removed successfully.', 'facilities-amenities') . '</p></div>'; */
    }

    // Handle bottom_two_height input with range limit
    if (isset($_POST['bottom_two_height'])) {
        $bottom_two_height = intval($_POST['bottom_two_height']);
        $bottom_two_height = max(250, min(550, $bottom_two_height)); // limit to 250–550px
    } else {
        $bottom_two_height = 250; // default if not provided
    }

    // Check if required fields are filled
    if (empty($bottom_two_title) || empty($bottom_two_description) || empty($bottom_two_buttontext) || empty($bottom_two_buttonlinkurl)) {
        echo '<div class="error"><p>' . esc_html__('All Content Section Two fields are required. Nothing was saved.', 'facilities-amenities') . '</p></div>';
    } else {
        $changes_made = false;

        // Compare and save only if changed
        if ($bottom_two_title !== facamen_get_option('bottom_two_title', '')) {
            facamen_set_option('bottom_two_title', $bottom_two_title);
            $changes_made = true;
        }
        if ($bottom_two_description !== facamen_get_option('bottom_two_description', '')) {
            facamen_set_option('bottom_two_description', $bottom_two_description);
            $changes_made = true;
        }
        if ($bottom_two_buttontext !== facamen_get_option('bottom_two_buttontext', '')) {
            facamen_set_option('bottom_two_buttontext', $bottom_two_buttontext);
            $changes_made = true;
        }
        if ($bottom_two_buttonlinkurl !== facamen_get_option('bottom_two_buttonlinkurl', '')) {
            facamen_set_option('bottom_two_buttonlinkurl', $bottom_two_buttonlinkurl);
            $changes_made = true;
        }

        if ($bottom_two_visible !== facamen_get_option('bottom_two_visible', 'yes')) {
            facamen_set_option('bottom_two_visible', $bottom_two_visible);
            $changes_made = true;
        }

        if ($open_two_new_tab !== facamen_get_option('open_two_new_tab', 'no')) {
            facamen_set_option('open_two_new_tab', $open_two_new_tab);
            $changes_made = true;
        }

        if ($backgroundImage !== facamen_get_option('bottom_two_backgroundImage', '')) {
            facamen_set_option('bottom_two_backgroundImage', $backgroundImage);
            $changes_made = true;
        }

        if ($bottom_two_title_color !== facamen_get_option('bottom_two_title_color', '')) {
            facamen_set_option('bottom_two_title_color', $bottom_two_title_color);
            $changes_made = true;
        }

        if ($bottom_two_description_color !== facamen_get_option('bottom_two_description_color', '')) {
            facamen_set_option('bottom_two_description_color', $bottom_two_description_color);
            $changes_made = true;
        }

        if ($backgroundTwoColor !== facamen_get_option('backgroundTwoColor', '')) {
            facamen_set_option('backgroundTwoColor', $backgroundTwoColor);
            $changes_made = true;
        }  
        
        if ($backgroundOverlay !== facamen_get_option('backgroundOverlay', '')) {
            facamen_set_option('backgroundOverlay', $backgroundOverlay);
            $changes_made = true;
        }  
        
        if ($apply_overlay !== facamen_get_option('apply_overlay', 'no')) {
            facamen_set_option('apply_overlay', $apply_overlay);
            $changes_made = true;
        }

        if ($background_overlay_opacity !== floatval(facamen_get_option('background_overlay_opacity', '0.45'))) {
            facamen_set_option('background_overlay_opacity', $background_overlay_opacity);
            $changes_made = true;
        }
        
        // Compare & update height only if changed
        $current_height = intval(facamen_get_option('bottom_two_height', 250));
        if ($bottom_two_height !== $current_height) {
            facamen_set_option('bottom_two_height', $bottom_two_height);
            $changes_made = true;
        }

        // Show result messages
        if ($changes_made) {
            echo '<div class="updated"><p>' . esc_html__('Content Section Two settings saved.', 'facilities-amenities') . '</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>' . esc_html__('No changes detected. Database not updated.', 'facilities-amenities') . '</p></div>';
        }
    }

} elseif (isset($_POST['save_two_bottom']) &&
          check_admin_referer('facamen_save_two_bottom') &&
          !$can_edit
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>'; 
}

// save_bottom button
if (isset($_POST['save_bottom']) &&
    check_admin_referer('facamen_save_bottom') &&
    $can_edit
) {
    $bottom_title = sanitize_text_field($_POST['bottom_title']);        
    $bottom_buttontext = sanitize_text_field($_POST['bottom_buttontext']);
    $bottom_description = sanitize_text_field($_POST['bottom_description']);
    $bottom_buttonlinkurl = sanitize_text_field($_POST['bottom_buttonlinkurl']);
    $bottom_visible = isset($_POST['bottom_visible']) ? 'yes' : 'no';
    $open_new_tab   = isset($_POST['open_new_tab']) ? 'yes' : 'no';

    // Check if required fields are filled
    if (empty($bottom_title) || empty($bottom_buttontext) || empty($bottom_buttonlinkurl)) {
        echo '<div class="error"><p>' . esc_html__('All Content Section One fields are required. Nothing was saved.', 'facilities-amenities') . '</p></div>';
    } else {
        $changes_made = false;

        // Compare before saving to prevent unnecessary updates
        if ($bottom_title !== facamen_get_option('bottom_title', '')) {
            facamen_set_option('bottom_title', $bottom_title);
            $changes_made = true;
        }
        if ($bottom_description !== facamen_get_option('bottom_description', '')) {
            facamen_set_option('bottom_description', $bottom_description);
            $changes_made = true;
        }
        if ($bottom_buttontext !== facamen_get_option('bottom_buttontext', '')) {
            facamen_set_option('bottom_buttontext', $bottom_buttontext);
            $changes_made = true;
        }
        if ($bottom_buttonlinkurl !== facamen_get_option('bottom_buttonlinkurl', '')) {
            facamen_set_option('bottom_buttonlinkurl', $bottom_buttonlinkurl);
            $changes_made = true;
        }

        // Compare both checkboxes in the change tracking
        if ($bottom_visible !== facamen_get_option('bottom_visible', 'yes')) {
            facamen_set_option('bottom_visible', $bottom_visible);
            $changes_made = true;
        }

        if ($open_new_tab !== facamen_get_option('open_new_tab', 'no')) {
            facamen_set_option('open_new_tab', $open_new_tab);
            $changes_made = true;
        }

        // Only show a message if a change was made
        if ($changes_made) {
            echo '<div class="updated"><p>' . esc_html__('Content Section One settings saved.', 'facilities-amenities') . '</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>' . esc_html__('No changes detected. Database not updated.', 'facilities-amenities') . '</p></div>';
        }
    }

} elseif (isset($_POST['save_bottom']) &&
          check_admin_referer('facamen_save_bottom') &&
          !$can_edit
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>'; 
}

// facamen_save_settings
if (isset($_POST['save_settings']) &&
    check_admin_referer('facamen_save_settings') &&
    $can_edit
){
    $showanimation = isset($_POST['showanimation']) ? 'yes' : 'no';
    facamen_set_option('showanimation', $showanimation);
} elseif (isset($_POST['save_settings']) &&
          check_admin_referer('facamen_save_settings') &&
          !$can_edit
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to edit this section.', 'facilities-amenities') . '</p></div>'; 
}

// delete_bottom_two_section
if (isset($_POST['delete_bottom_two_section']) &&
    check_admin_referer('facamen_save_two_bottom') &&
    facamen_user_can_delete_plugin()
) {

    // HARD CHECK: only REAL administrators
    if (!facamen_user_is_real_admin()) {
        echo '<div class="error"><p>' .
            esc_html__('Only users with the Administrator role can delete this section.', 'facilities-amenities') .
            '</p></div>';
        return;
    }

    $present_two_title = facamen_get_option('bottom_two_title', '');
    $present_two_description = facamen_get_option('bottom_two_description', '');
    $present_two_buttontext = facamen_get_option('bottom_two_buttontext', '');
    $present_two_buttonlinkurl = facamen_get_option('bottom_two_buttonlinkurl', '');

    // Prevent delete if all fields are already empty
    /* if (empty($present_title) && empty($present_description) && empty($present_buttonlinkurl) && empty($present_two_title)) { */
    if (empty($present_two_title) && empty($present_two_description) && empty($present_two_buttontext) && empty($present_two_buttonlinkurl)) {
        echo '<div class="error"><p>' . esc_html__('Nothing to delete. Fields are already empty.', 'facilities-amenities') . '</p></div>';
    } else { 
        $fields_to_delete = [
            'bottom_two_visible', 
            'bottom_two_title', 
            'bottom_two_height', 
            'bottom_two_description', 
            'bottom_two_buttontext', 
            'bottom_two_buttonlinkurl', 
            'open_two_new_tab', 
            'bottom_two_backgroundImage', 
            'bottom_two_title_color',
            'bottom_two_description_color',
            'backgroundTwoColor',
            'backgroundOverlay',
            'apply_overlay',
            'background_overlay_opacity'
        ];
        foreach ($fields_to_delete as $field) {
            facamen_delete_option($field);
        }
        echo '<div class="updated"><p><strong>' . esc_html__('Content Section Two fields have been deleted from the database.', 'facilities-amenities') . '</strong></p></div>';
    }

} elseif (isset($_POST['delete_bottom_two_section']) &&
          check_admin_referer('facamen_save_two_bottom') &&
          !facamen_user_can_delete_plugin()
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to delete this section.', 'facilities-amenities') . '</p></div>';    
}

// deleteBottomSection
if (isset($_POST['deleteBottomSection']) &&
    check_admin_referer('facamen_save_bottom') &&
    facamen_user_can_delete_plugin()
) {

    // HARD CHECK: only REAL administrators
    if (!facamen_user_is_real_admin()) {
        echo '<div class="error"><p>' .
            esc_html__('Only users with the Administrator role can delete this section.', 'facilities-amenities') .
            '</p></div>';
        return;
    }

    $present_title = facamen_get_option('bottom_title', '');
    $present_description = facamen_get_option('bottom_buttontext', '');
    $present_buttonlinkurl = facamen_get_option('bottom_buttonlinkurl', '');

    // Prevent delete if all fields are already empty
    if (empty($present_title) && empty($present_description) && empty($present_buttonlinkurl)) {
        echo '<div class="error"><p>' . esc_html__('Nothing to delete. Fields are already empty.', 'facilities-amenities') . '</p></div>';
    } else {
        /* $fields_to_delete = ['bottom_title', 'bottom_buttontext', 'bottom_buttonlinkurl']; */
        $fields_to_delete = ['bottom_title', 'bottom_description', 'bottom_buttontext', 'bottom_buttonlinkurl', 'bottom_visible', 'open_new_tab'];
        foreach ($fields_to_delete as $field) {
            facamen_delete_option($field);
        }
        echo '<div class="updated"><p><strong>' . esc_html__('Content Section One fields have been deleted from the database.', 'facilities-amenities') . '</strong></p></div>';
    }

} elseif (isset($_POST['deleteBottomSection']) &&
          check_admin_referer('facamen_save_bottom') &&
          !facamen_user_can_delete_plugin()
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to delete this section.', 'facilities-amenities') . '</p></div>';    
}

if (isset($_POST['deleteTopSection']) &&
    check_admin_referer('facamen_save_top') &&
    facamen_user_can_delete_plugin()
) {

    // HARD CHECK: only REAL administrators
    if (!facamen_user_is_real_admin()) {
        echo '<div class="error"><p>' .
            esc_html__('Only users with the Administrator role can delete this section.', 'facilities-amenities') .
            '</p></div>';
        return;
    }

    $current_title = facamen_get_option('top_title', '');
    $current_description = facamen_get_option('top_description', '');

    // Prevent delete if both fields are already empty
    if (empty($current_title) && empty($current_description)) {
        echo '<div class="error"><p>' . esc_html__('Nothing to delete. Fields are already empty.', 'facilities-amenities') . '</p></div>';
    } else {
        $fields_to_delete = ['top_title', 'top_description'];
        foreach ($fields_to_delete as $field) {
            facamen_delete_option($field);
        }
        echo '<div class="updated"><p><strong>' . esc_html__('Top Main Title and Top Description fields deleted from the database.', 'facilities-amenities') . '</strong></p></div>';
    }
} elseif (isset($_POST['deleteTopSection']) &&
          check_admin_referer('facamen_save_top') &&
          !facamen_user_can_delete_plugin()
) {
    echo '<div class="error"><p>' . esc_html__('You do not have permission to delete this section.', 'facilities-amenities') . '</p></div>'; 
}
?>
<div class="wrap">
    <h2 class="nav-tab-wrapper">
        <a href="?page=facamen-main-sections&tab=top" class="nav-tab <?php echo $active_tab === 'top' ? 'nav-tab-active' : ''; ?>"><?php _e('Top Section', 'facilities-amenities'); ?></a>
        <a href="?page=facamen-main-sections&tab=bottom" class="nav-tab <?php echo $active_tab === 'bottom' ? 'nav-tab-active' : ''; ?>"><?php _e('Bottom Section', 'facilities-amenities'); ?></a>
        <a href="?page=facamen-main-sections&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'facilities-amenities'); ?></a>
    </h2>

    <!-- Top Tab -->
    <div id="top" class="tab-content" style="<?php echo $active_tab === 'top' ? '' : 'display:none;'; ?>">     
        <div class="facamen-topandbottom"><label class="red-label"><?php printf( esc_html__('Shortcode to use - %s', 'facilities-amenities'), '[facilities]' ); ?></label></div>
        <div><label><?php _e('Top Area Section', 'facilities-amenities'); ?></label></div>
        <form method="post" action="<?php echo esc_url(add_query_arg('tab', $_GET['tab'] ?? 'top')); ?>">
            <?php wp_nonce_field('facamen_save_top'); ?>

            <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">

            <?php
            $top_title_value = esc_attr(facamen_get_option('top_title', ''));
            $top_description_value = esc_textarea(facamen_get_option('top_description', ''));
            ?>

            <table class="form-table">
                <tr>
                    <th><label for="top_title"><?php _e('Top Main Title', 'facilities-amenities'); ?></label></th>
                    <td><input type="text" name="top_title" id="top_title" value="<?php echo $top_title_value; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="top_description"><?php _e('Top Description', 'facilities-amenities'); ?></label></th>
                    <td><textarea name="top_description" id="top_description" class="regular-text" rows="5"><?php echo $top_description_value; ?></textarea></td>
                </tr>
            </table>        
            
            <p>
                <?php if ($can_edit): ?>
                    <input type="submit" name="save_top"
                        class="button button-primary"
                        value="<?php _e('Save Changes', 'facilities-amenities'); ?>" />
                <?php endif; ?>

                <?php if ($can_delete): ?>
                    <input type="submit"
                        name="deleteTopSection"
                        class="button button-secondary"
                        value="<?php _e('Delete Fields', 'facilities-amenities'); ?>"
                        <?php echo $is_real_admin ? '' : 'disabled="disabled"'; ?>
                        <?php if ($is_real_admin): ?>
                            onclick="return confirm('<?php _e('Are you sure you want to delete the fields from the database?', 'facilities-amenities'); ?>');"
                        <?php endif; ?>
                    />                    
                <?php endif; ?>

                <?php if (!$can_edit && !$can_delete): ?>
                    <p><em><?php _e('You do not have permission to make changes.', 'facilities-amenities'); ?></em></p>
                <?php endif; ?>
            </p>
            
        </form>
    </div>
    <!-- /Top Tab -->

    <!-- Bottom Section -->
    <div id="bottom" class="tab-content" style="<?php echo $active_tab === 'bottom' ? '' : 'display:none;'; ?>">
        <div class="facamen-topandbottom"><label class="red-label"><?php printf( esc_html__('Shortcode to use - %s', 'facilities-amenities'), '[facilities]' ); ?></label></div>
        <div><label><?php _e('Bottom Area Section', 'facilities-amenities'); ?></label></div>
        <div class="space-top"><label><?php _e('Content Section One (1)', 'facilities-amenities'); ?></label></div>
        <form method="post" action="<?php echo esc_url(add_query_arg('tab', $_GET['tab'] ?? 'bottom')); ?>">
            <?php wp_nonce_field('facamen_save_bottom'); ?>
            <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
            <?php
            $bottom_title_value = esc_attr(facamen_get_option('bottom_title', ''));    
            $bottom_description_value = esc_attr(facamen_get_option('bottom_description', ''));    
			$bottom_bntText_value = esc_attr(facamen_get_option('bottom_buttontext', ''));
            $bottom_bntLinkURL_value = esc_attr(facamen_get_option('bottom_buttonlinkurl', ''));
            $bottom_visible = isset($_POST['bottom_visible']) ? 'yes' : 'no';
            $open_new_tab = isset($_POST['open_new_tab']) ? 'yes' : 'no';             
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="bottom_visible"><?php _e('Display Content Section One?', 'facilities-amenities'); ?></label>
                    </th>
                    <td>
                        <?php $bottom_visible = facamen_get_option('bottom_visible', 'yes'); ?>
                        <input type="checkbox" name="bottom_visible" id="bottom_visible" value="yes" <?php checked($bottom_visible, 'yes'); ?> />
                        <label for="bottom_visible"><?php _e('Enable to display the content section one on the site.', 'facilities-amenities'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_title"><?php _e('Title', 'facilities-amenities'); ?></label></th>
                    <td><input type="text" name="bottom_title" id="bottom_title" value="<?php echo $bottom_title_value; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_description"><?php _e('Description', 'facilities-amenities'); ?></label></th>
                    <td><textarea name="bottom_description" id="bottom_description" class="regular-text" rows="5"><?php echo $bottom_description_value; ?></textarea></td>
                </tr>                
				<tr>
                    <th scope="row"><label for="bottom_buttontext"><?php _e('Button Text', 'facilities-amenities'); ?></label></th>
                    <td><input type="text" name="bottom_buttontext" id="bottom_buttontext" value="<?php echo $bottom_bntText_value; ?>" class="regular-text" /></td> 
                </tr>  
                <tr>
                    <th scope="row"><label for="bottom_buttonlinkurl"><?php _e('Button Link URL', 'facilities-amenities'); ?></label></th>
                    <td>                        
                        <input type="url" name="bottom_buttonlinkurl" id="bottom_buttonlinkurl" value="<?php echo $bottom_bntLinkURL_value; ?>" class="regular-text" />
                        <?php $open_new_tab = facamen_get_option('open_new_tab', 'yes'); ?>
                        <input type="checkbox" name="open_new_tab" id="open_new_tab" value="yes" <?php checked($open_new_tab, 'yes'); ?> />
                        <label for="open_new_tab"><?php _e('Open Link in New Tab', 'facilities-amenities'); ?></label>
                    </td>                    
                </tr>				
            </table>      
            <p class="space-bottom">
                <?php if ($can_edit): ?>
                    <input type="submit" name="save_bottom" class="button button-primary" value="<?php _e('Save Changes', 'facilities-amenities'); ?>" />                     
                <?php endif; ?>

                <?php if ($can_delete): ?>
                    <input type="submit" 
                        name="deleteBottomSection" 
                        class="button button-secondary" 
                        value="<?php _e('Delete Fields', 'facilities-amenities'); ?>" 
                        <?php echo $is_real_admin ? '' : 'disabled="disabled"'; ?>
                        <?php if ($is_real_admin): ?>
                            onclick="return confirm('<?php _e('Are you sure you want to delete the fields from the database?', 'facilities-amenities'); ?>');" />
                        <?php endif; ?>                
                <?php endif; ?>

                <?php if (!$can_edit && !$can_delete): ?>                
                    <p><em><?php _e('You do not have permission to make changes.', 'facilities-amenities'); ?></em></p>
                <?php endif; ?> 
            </p>
        </form>
        <hr>
        <!-- Content Section Two (2) -->
        <div class="facamen-topandbottom"><label><?php _e('Content Section Two (2)', 'facilities-amenities'); ?></label></div>
        <form method="post" action="<?php echo esc_url(add_query_arg('tab', $_GET['tab'] ?? 'bottomTwo')); ?>">
        <?php wp_nonce_field('facamen_save_two_bottom'); ?>
            <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
            <?php
            $bottom_two_visible = isset($_POST['bottom_two_visible']) ? 'yes' : 'no';
            $bottom_two_height = esc_attr(facamen_get_option('bottom_two_height', 250));
            $bottom_two_title_value = esc_attr(facamen_get_option('bottom_two_title', ''));
            $bottom_two_description_value = esc_attr(facamen_get_option('bottom_two_description', '')); 
            $bottom_two_bntText_value = esc_attr(facamen_get_option('bottom_two_buttontext', ''));
            $bottom_two_bntLinkURL_value = esc_attr(facamen_get_option('bottom_two_buttonlinkurl', ''));
            $open_two_new_tab = isset($_POST['open_two_new_tab']) ? 'yes' : 'no'; 
            $apply_overlay = isset($_POST['apply_overlay']) ? 'yes' : 'no';
            
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="bottom_two_visible"><?php _e('Display Content Section Two?', 'facilities-amenities'); ?></label></th>
                    <td>
                        <?php $bottom_two_visible = facamen_get_option('bottom_two_visible', 'yes'); ?>
                        <input type="checkbox" name="bottom_two_visible" id="bottom_two_visible" value="yes" <?php checked($bottom_two_visible, 'yes'); ?> />
                        <label for="bottom_two_visible"><?php _e('Enable to display the content section two on the site.', 'facilities-amenities'); ?></label>
                    </td>                    
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_two_height"><?php _e('Element Height', 'facilities-amenities'); ?></label></th>
                    <td>                       
                        <!-- <input type="number" 
                            min="250" 
                            max="550" 
                            step="10" 
                            name="bottom_two_height" 
                            id="bottom_two_height" 
                            value="<?php //echo $bottom_two_height; ?>" 
                            class="small-text" />
                        <label for="bottom_two_height"><?php //_e('Height in pixels (250–550px)', 'facilities-amenities'); ?></label>  -->
                        <input type="range" 
                            min="250" 
                            max="550" 
                            step="10" 
                            name="bottom_two_height" 
                            id="bottom_two_height" 
                            value="<?php echo $bottom_two_height; ?>" 
                            oninput="document.getElementById('bottom_two_height_value').innerText = this.value + 'px';" />
                        <span id="bottom_two_height_value"><?php echo $bottom_two_height; ?>px</span>
                        <label for="bottom_two_height"><?php _e('/ Adjust the height (250–550px)', 'facilities-amenities'); ?></label>
                    </td>
                </tr>                              
                <tr>
                    <th scope="row"><label for="bottom_two_title"><?php _e('Title', 'facilities-amenities'); ?></label></th>
                    <td>
                        <input type="text" name="bottom_two_title" id="bottom_two_title" value="<?php echo $bottom_two_title_value; ?>" class="regular-text" />
                        <input type="color" 
                            id="bottom_two_title_color" 
                            name="bottom_two_title_color" 
                            value="<?php echo esc_attr(facamen_get_option('bottom_two_title_color', '#000000')); ?>">
                        <label for="bottom_two_title_color"><?php _e('Title Text Color', 'facilities-amenities'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_two_description"><?php _e('Description', 'facilities-amenities'); ?></label></th>
                    <td>
                        <textarea name="bottom_two_description" id="bottom_two_description" class="regular-text" rows="5"><?php echo $bottom_two_description_value; ?></textarea>
                        <input type="color" 
                            id="bottom_two_description_color" 
                            name="bottom_two_description_color" 
                            value="<?php echo esc_attr(facamen_get_option('bottom_two_description_color', '#000000')); ?>">
                        <label for="bottom_two_description_color"><?php _e('Description Text Color', 'facilities-amenities'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_two_buttontext"><?php _e('Button Text', 'facilities-amenities'); ?></label></label></th>
                    <td><input type="text" name="bottom_two_buttontext" id="bottom_two_buttontext" value="<?php echo $bottom_two_bntText_value; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bottom_two_buttonlinkurl"><?php _e('Button Link URL', 'facilities-amenities'); ?></label></th>
                    <td>
                        <input type="url" name="bottom_two_buttonlinkurl" id="bottom_two_buttonlinkurl" value="<?php echo $bottom_two_bntLinkURL_value; ?>" class="regular-text" />
                        <?php $open_two_new_tab_value = facamen_get_option('open_two_new_tab', 'yes'); ?>
                        <input type="checkbox" name="open_two_new_tab" id="open_two_new_tab" value="yes" <?php checked($open_two_new_tab_value, 'yes'); ?> />
                        <label for="open_two_new_tab"><?php _e('Open Link in New Tab', 'facilities-amenities'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="backgroundImage"><?php esc_html_e('Background Image', 'facilities-amenities'); ?></label></th>
                    <td>
                        <input 
                            name="backgroundImage"                             
                            type="url" 
                            class="regular-text imagelinkurl-input" 
                            value="<?php echo esc_attr(facamen_get_option('bottom_two_backgroundImage', '')); ?>"
                            placeholder="<?php $default_img; ?>" 
                            readonly
                        >
                        <input type="button" class="button button-secondary upload-image-button" value="<?php esc_attr_e('Upload Image', 'facilities-amenities'); ?>">  
                        <input type="checkbox" name="check_to_remove_image" id="check_to_remove_image" value="no" />
                        <label for="check_to_remove_image"><?php _e('Check to remove image', 'facilities-amenities'); ?></label>

                        <?php $bg_image_url = esc_url(facamen_get_option('bottom_two_backgroundImage', '')); ?>
                        <div class="image-preview-wrapper" style="margin-top:10px;">
                            <img class="image-preview" 
                                src="<?php echo $bg_image_url; ?>" 
                                alt="Image Preview"
                                style="max-width:300px; height:auto; <?php echo empty($bg_image_url) ? 'display:none;' : ''; ?>" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="backgroundTwoColor"><?php esc_html_e('Background Color', 'facilities-amenities'); ?></label></th>
                    <td>
                        <input type="color" 
                            id="backgroundTwoColor" 
                            name="backgroundTwoColor" 
                            value="<?php echo esc_attr(facamen_get_option('backgroundTwoColor', '#ffffff')); ?>">
                        <label><?php esc_html_e('Fallback background color (shown when no image is selected)', 'facilities-amenities'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="backgroundOverlay"><?php esc_html_e('Background Overlay', 'facilities-amenities'); ?></label></th>
                    <td>
                        <input type="color" 
                            id="backgroundOverlay" 
                            name="backgroundOverlay" 
                            value="<?php echo esc_attr(facamen_get_option('backgroundOverlay', '#000000')); ?>">                        
			            <?php $apply_overlay_value = facamen_get_option('apply_overlay', 'yes'); ?>
                        <input type="checkbox" name="apply_overlay" id="apply_overlay" value="no" <?php checked($apply_overlay_value, 'yes'); ?> /> 
                        <label for="apply_overlay"><?php _e('Check to apply overlay', 'facilities-amenities'); ?></label>                      
                    </td>
                </tr> 
                <tr>
                    <th scope="row"><label for="background_overlay_opacity"><?php esc_html_e('Background Overlay Opacity', 'facilities-amenities'); ?></label></th>
                    <td>
                        <?php $background_overlay_opacity = esc_attr(facamen_get_option('background_overlay_opacity', '0.45')); ?>
                        <input type="range" 
                            min="0" 
                            max="1.0" 
                            step="0.01" 
                            name="background_overlay_opacity" 
                            id="background_overlay_opacity" 
                            value="<?php echo $background_overlay_opacity; ?>" 
                            oninput="document.getElementById('background_overlay_opacity_value').innerText = this.value" />
                        <span id="background_overlay_opacity_value"><?php echo $background_overlay_opacity; ?></span>
                        <label for="background_overlay_opacity"><?php _e('/ Adjust the opacity (0-1)', 'facilities-amenities'); ?></label>
                    </td>
                </tr>               
            </table>
            <p>
                <?php if ($can_edit): ?>
                    <input type="submit" name="save_two_bottom" class="button button-primary" value="<?php _e('Save Changes', 'facilities-amenities'); ?>" />                     
                <?php endif; ?>

                <?php if ($can_delete): ?>
                        <input type="submit" 
                        name="delete_bottom_two_section" 
                        class="button button-secondary" 
                        value="<?php _e('Delete Fields', 'facilities-amenities'); ?>" 
                        <?php echo $is_real_admin ? '' : 'disabled="disabled"'; ?>
                        <?php if ($is_real_admin): ?>
                            onclick="return confirm('<?php _e('Are you sure you want to delete the fields from the database?', 'facilities-amenities'); ?>');" />                                      
                        <?php endif; ?>                    
                <?php endif; ?>

                <?php if (!$can_edit && !$can_delete): ?>                
                    <p><em><?php _e('You do not have permission to make changes.', 'facilities-amenities'); ?></em></p>
                <?php endif; ?>
            </p>
        </form>
    </div> 
    <!-- /Bottom Section -->   

    <!-- Settings --> 
     <div id="settings" class="tab-content" style="<?php echo $active_tab === 'settings' ? '' : 'display:none;'; ?>">
        <div class="facamen-topandbottom"><label class="red-label"><?php printf( esc_html__('Shortcode to use - %s', 'facilities-amenities'), '[facilities]' ); ?></label></div>
        <div><label><?php _e('Settings for displaying plugin animation on the site.', 'facilities-amenities'); ?></label></div>
        <form method="post" action="<?php echo esc_url(add_query_arg('tab', $_GET['tab'] ?? 'settings')); ?>">
            <?php wp_nonce_field('facamen_save_settings'); ?>
            <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
            <?php
                $showanimation = isset($_POST['showanimation']) ? 'yes' : 'no';
            ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="showanimation"><?php _e('Display Animation?', 'facilities-amenities'); ?></label>
                </th>
                <td>
                    <?php $showanimation = facamen_get_option('showanimation', 'yes'); ?>
                    <input type="checkbox" name="showanimation" id="showanimation" value="yes" <?php checked($showanimation, 'yes'); ?> />
                    <label for="showanimation"><?php _e('Enable to display animation on the site.', 'facilities-amenities'); ?></label>
                </td>
            </tr>
        </table>

        <p>
            <?php if ($can_edit): ?>
                <input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'facilities-amenities'); ?>" />
            <?php endif; ?> 
        </p> 

        </form>
     </div>
    <!-- /Settings --> 
</div>