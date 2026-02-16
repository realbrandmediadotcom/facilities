<?php

/********************************* 
 * admin content functions
**********************************/
/**
 * Inject the CSS globally
 */
function facamen_admin_menu_icon_style() {
    echo '<style>
        #adminmenu .toplevel_page_facamen-options .wp-menu-image:before {
            color: yellow !important;
        }
    </style>';
}
add_action('admin_head', 'facamen_admin_menu_icon_style');

/**
 * Admin Menu Page
 */
add_action('admin_menu', 'facamen_admin_menu');

function facamen_admin_menu() {
    add_menu_page(
        __('Facilities', 'facilities-amenities'),
        __('Facilities', 'facilities-amenities'),        
        'read', // Let anyone logged in access the menu
        'facamen-options',
        'facamen_admin_page',        
        'dashicons-text-page'
    );

    // Submenu pages    
    $main_sections_hook = add_submenu_page(
        'facamen-options',
        __('Main Sections', 'facilities-amenities'),
        __('Main Sections', 'facilities-amenities'),        
        'read',
        'facamen-main-sections',
        'facamen_main_sections_page'
    );
    add_action("load-$main_sections_hook", 'facamen_main_sections_help_tab');

    $categories_hook = add_submenu_page(   
        'facamen-options',
        __('Categories', 'facilities-amenities'),
        __('Categories', 'facilities-amenities'),
        'read',        
        'facamen-categories',
        'facamen_categories_page'
    );
    add_action("load-$categories_hook", 'facamen_categories_help_tab');    

    $categoryOptions_hook = add_submenu_page(
        'facamen-options',
        __('Category Options', 'facilities-amenities'),
        __('Category Options', 'facilities-amenities'),
        'read',
        'facamen-category-options',
        'facamen_category_options_page'
    ); 
    add_action("load-$categoryOptions_hook", 'facamen_categoryOptions_help_tab');

    $categoryAdd_hook = add_submenu_page(
        'facamen-options',
        __('Category Add Option', 'facilities-amenities'),
        __('Category Add Option', 'facilities-amenities'),
        'read',
        'facamen-category-add',
        'facamen_category_add_page'
    );
    add_action("load-$categoryAdd_hook", 'facamen_categoryAdd_help_tab');

    // hide it from the submenu list
    add_action('admin_head', function () {
        remove_submenu_page('facamen-options', 'facamen-category-add');
    }); 

    // Show this submenu only to administrators
    if (current_user_can('administrator')) {
        $perms_roles_hook = add_submenu_page(
            'facamen-options',
            __('Permissions / Roles', 'facilities-amenities'),
            __('Permissions / Roles', 'facilities-amenities'),
            'manage_options',
            'facamen-permissions-roles',
            'facamen_permissions_roles_page'
        );

        // Add help tabs or other load-time logic
        add_action("load-$perms_roles_hook", 'facamen_permissions_roles_help_tab');
    }

    // Remove the duplicate first submenu (points to top-level page)
    add_action('admin_head', function () {
        remove_submenu_page('facamen-options', 'facamen-options');
    });

}

/**
 * Used for testing only
 */
/* add_action('current_screen', function($screen) {
    error_log('Current screen ID: ' . $screen->id);
}); */

/**
 * Check Permission for Subscriber
 */
function facamen_user_is_subscriber() {
    if (current_user_can('subscriber')) {
        return true; // Subscriber is not allowed
    }
}

/**
 * Check Permission for edit
 */
function facamen_user_can_edit_plugin() {
    if (current_user_can('administrator')) {
        return true; // Admin always allowed
    }

    // pull from custom table
    $allowed_roles = facamen_get_option('facamen_permissions_edit', []);
    $current_user = wp_get_current_user();

    foreach ($current_user->roles as $role) {
        if (in_array($role, $allowed_roles)) {
            return true;
        }
    }

    return false;
}

/**
 * Check Permission for create
 */
function facamen_user_can_create_plugin() {
    if (current_user_can('administrator')) {
        return true; // Admin always allowed
    }

    // pull from custom table
    $allowed_roles = facamen_get_option('facamen_permissions_create', []);
    $current_user = wp_get_current_user();

    foreach ($current_user->roles as $role) {
        if (in_array($role, $allowed_roles)) {
            return true;
        }
    }

    return false;

}

/**
 * Check Permission for delete
 */
function facamen_user_can_delete_plugin() {
    if (current_user_can('administrator')) {
        return true; // Admin always allowed
    }

    // pull from custom table
    $allowed_roles = facamen_get_option('facamen_permissions_delete', []);
    $current_user = wp_get_current_user();

    foreach ($current_user->roles as $role) {
        if (in_array($role, $allowed_roles)) {
            return true;
        }
    }

    return false;

}

/**
 * Check Permission for real administator role
 */
function facamen_user_is_real_admin() {
    $user = wp_get_current_user();
    return in_array('administrator', (array) $user->roles, true);
}

function facamen_categories_page() {
    // Categories      
    
    // Check for subscriber
    if ( facamen_user_is_subscriber() ) {
        facamen_admin_notice( __( 'You do not have access to view or edit this plugin.', 'facilities-amenities' ) );
        return;
    }


    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Categories', 'facilities-amenities' ) . '</h1>';
    echo '<hr>';

    /**
     * Include the Categories submenu page.
     * This file should contain markup for the Categories admin section.
     */
    $template = plugin_dir_path( __FILE__ ) . 'submenus/submenu-categories-page.php';
    if ( file_exists( $template ) ) {
        include $template;
    } else {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Categories template not found.', 'facilities-amenities' );
        echo '</p></div>';
    }

    echo '</div>';

    if (!class_exists('WP_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }

}

/**
 * Help tabs for categories
 */
function facamen_categories_help_tab() {
    $screen = get_current_screen();

    if ( $screen->id !== 'facilities_page_facamen-categories' ) {
        return;
    }

    $screen->add_help_tab([
        'id'      => 'categories_overview',
        'title'   => __('Overview', 'facilities-amenities'),
        'content' => '<p>' . __('The Facilities plugin allows you to have many different categories.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Each category will need a title and the title is what will be shown on the frontend.', 'facilities-amenities') . '</p>' .
        '<p>' . __('You can upload an image to your liking. If you decide not to change the image, the plugin will use a spacer.png image set in default.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Provide the visitors with a button that has a meaningful name along with an URL so when the visitor clicks on the button they will be sent to that given URL.', 'facilities-amenities') . '</p>',
    ]);

     $screen->add_help_tab([
        'id'      => 'categories_details',
        'title'   => __('How to Use', 'facilities-amenities'),
        'content' => '<p>' . __('For further information view each section under the How to Use.', 'facilities-amenities') . '</p>' .
            '<p>' . __('1. Add New Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('2. Editing Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('3. Delete Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('4. Search Categories', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'add_new_categories',
        'title'   => __('Add New Category', 'facilities-amenities'),
        'content' => '<p>' . __('Create a new category. e.g. Facilities', 'facilities-amenities') . '</p>' .
            '<p>' . __('Create a new title. e.g. Campground Facilities', 'facilities-amenities') . '</p>' .
            '<p>' . __('Upload an Image or photograph that you would like to show your visitors.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add the name that will appear on the button text. e.g. BOOK ONLINE', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add the button URL. e.g. https://example.com', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to click the Add Category button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you want the new category to be seen on the frontend of the site, make sure that the checkbox is checked to enable category.', 'facilities-amenities') . '</p>' .
            '<p>' . __('In the table under enabled, you will see either yes or no for the category.', 'facilities-amenities') . '</p>' .
            '<p>' . __('**Enabled** to view a category on the frontend, you must have Enabled set to Yes.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To change enabled click, edit under the Category in the list table. The checkbox must be checked to be enabled. You will find this next to Enable this category to display it on the site.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'edit_categories',
        'title'   => __('Editing Category', 'facilities-amenities'),
        'content' => '<p>' . __('To edit you will find the edit link under the category name in the table row. Click on edit to go to the edit category section.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit a category name, make the changes you want in the text field. The plugin will not allow you to have the same two category names.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit a category title, make the changes you want in the text field.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit the button text or button link URL, first you need to enable the fields but clicking on the checkbox - Enable to edit button fields.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit a button text, make the changes you want in the text field.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit a button link URL, make the changes you want in the text field.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you do not want the URL link to open into a new tab, uncheck the checkbox next to Open Link in New Tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit the image URL, click on the upload image button. You can upload files to the media library. Or you can select an image from the media library. Click on the image and click on using this image button.
', 'facilities-amenities') . '</p>' .
            '<p>' . __('* Note that by default, WordPress Contributors cannot upload media (they don\'t have the upload_files capability).', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you no longer want the category to be seen on the site, make sure that the checkbox is unchecked for Enable this category to display it on the site.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>' .            
            '<p>' . __('Both back to list buttons and back to list link will take you back to the categories page.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'delete_categories',
        'title'   => __('Delete Category', 'facilities-amenities'),
        'content' => '<p>' . __('To delete a category, you will find the Trash under the Category column within the table.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can move multiple categories to the Trash at once.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Select the category you want to act on using the checkboxes, then select the action you want to take from the Bulk actions menu and click Apply.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The category will be moved into the trash. Once in the trash you can either restore the category or delete permanently.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If the Administrator sets the user up with delete permissions that said user will be allowed to delete the data.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'search_categories',
        'title'   => __('Search Category', 'facilities-amenities'),
        'content' => '<p>' . __('To search for a specific category or title.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Type out the wording that you want to search in the textbox next to the search categories button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Next, click on the search categories button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To see the full table click on the x in the textbox and click on the search categories button.', 'facilities-amenities') . '</p>',
    ]);

    /* $screen->set_help_sidebar(
        '<p><strong>' . __('Need more help?', 'facilities-amenities') . '</strong></p>' .
        '<p><a href="https://example.com/docs" target="_blank">' . __('View Documentation', 'facilities-amenities') . '</a></p>'
    ); */
}

function facamen_category_options_page() {	
	// Category Options

    // Check for subscriber
    if ( facamen_user_is_subscriber() ) {
        facamen_admin_notice( __( 'You do not have access to view or edit this plugin.', 'facilities-amenities' ) );
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Category Options', 'facilities-amenities' ) . '</h1>';
    echo '<hr>';

    /**
     * Include the Category Options submenu page.
     * This file should contain markup for the Category Options admin section.
     */
    $template = plugin_dir_path( __FILE__ ) . 'submenus/submenu-category-options-page.php';
    if ( file_exists( $template ) ) {
        include $template;
    } else {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Category Options template not found.', 'facilities-amenities' );
        echo '</p></div>';
    }

    echo '</div>';

}

/**
 * Help tabs for Category Options
 */
function facamen_categoryOptions_help_tab() {
    $screen = get_current_screen();

    if ( $screen->id !== 'facilities_page_facamen-category-options' ) {
        return;
    }

    $screen->add_help_tab([
        'id'      => 'categoryOptions_overview',
        'title'   => __('Overview', 'facilities-amenities'),
        'content' => '<p>' . __('The Facilities plugin allows you to give your visitors unlimited detailed descriptions or options about the categories.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Each description/option has an icon that you can change if you choose.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Provide the visitors with a button that has a meaningful name along with an URL so when the visitor clicks on the button they will be sent to that given URL.', 'facilities-amenities') . '</p>',
    ]);

     $screen->add_help_tab([
        'id'      => 'categoryOptions_details',
        'title'   => __('How to Use', 'facilities-amenities'),
        'content' => '<p>' . __('For further information view each section under the How to Use.', 'facilities-amenities') . '</p>' .
            '<p>' . __('1. Add Category Options', 'facilities-amenities') . '</p>' .
            '<p>' . __('2. Editing Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('3. Delete Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('4. Search Category', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'add_new_categoryOptions',
        'title'   => __('Add Category Options', 'facilities-amenities'),
        'content' => '<p>' . __('To create a new option, you will need to select a category from the dropdown list. Once you have chosen a category click on the select button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('By default, the icon is a pointing finger, however if you want to change that icon, use the show icon picker toggle', 'facilities-amenities') . '</p>' .
            '<p>' . __('These said icons are the icons that are sat in front of the description for the facilities.', 'facilities-amenities') . '</p>' .
            '<p>' . __('When adding a different icon to the category options, you can select from 60 different available icons.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The selected icon will show which icon is currently selected.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To unselect an icon, you will need to click on it again. The selected icon will show the default icon which will be the pointing finger.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To add a description to the category options, type the description into the field. e.g. Large clubhouse with cozy fireplace', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure that you click and add new option button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Both back to list buttons and back to list link will take you back to the category options page.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'edit_categoryOptions',
        'title'   => __('Editing Category', 'facilities-amenities'),
        'content' => '<p>' . __('To edit you will find the edit link under the category name in the table row. Click on edit to go to the edit category options section.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The category name cannot be edited. Once you have created the name it must be deleted either with the administrator, or the user must have delete permissions.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To change an icon to another icon, click on the desired icon. You have 60 icons to choose from.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit the description, type the desired wording that you want to have read on the front end. e.g. Large clubhouse with comfortable fireplace.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you no longer want the category option to be seen on the site, make sure that the checkbox is unchecked for Enable this category option to display it on the site.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>' .            
            '<p>' . __('Both back to list buttons and back to list link will take you back to the category options page.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'delete_categoryOptions',
        'title'   => __('Delete Category', 'facilities-amenities'),
        'content' => '<p>' . __('To delete a category within the category options table, you will find the Trash under the Category column within the table.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can move multiple categories to the Trash at once.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Select the category you want to act on using the checkboxes, then select the action you want to take from the Bulk actions menu and click Apply.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The category will be moved into the trash. Once in the trash you can either restore the category or delete permanently.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If the Administrator sets the user up with delete permissions that said user will be allowed to delete the data.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'search_categoryOptions',
        'title'   => __('Search Category', 'facilities-amenities'),
        'content' => '<p>' . __('To search for a specific category, icon or description.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Type out the wording that you want to search in the textbox next to the search category button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Next, click on the search category button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To see the full table click on the x in the textbox and click on the search category button.', 'facilities-amenities') . '</p>',
    ]);

    /* $screen->set_help_sidebar(
        '<p><strong>' . __('Need more help?', 'facilities-amenities') . '</strong></p>' .
        '<p><a href="https://example.com/docs" target="_blank">' . __('View Documentation', 'facilities-amenities') . '</a></p>'
    ); */

}

/**
 * Help tabs for Category Add
 */
function facamen_categoryAdd_help_tab() {
    $screen = get_current_screen();

    if ( $screen->id !== 'facilities_page_facamen-category-add' ) {
        return;
    }

    $screen->add_help_tab([
        'id'      => 'categoryOptions_overview',
        'title'   => __('Overview', 'facilities-amenities'),
        'content' => '<p>' . __('The Facilities plugin allows you to give your visitors unlimited detailed descriptions or options about the categories.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Each description/option has an icon that you can change if you choose.', 'facilities-amenities') . '</p>' .
        '<p>' . __('Provide the visitors a button that has a meaningful name along with an URL so when the visitor clicks on the button they will be sent to that given URL.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'categoryOptions_details',
        'title'   => __('How to Use', 'facilities-amenities'),
        'content' => '<p>' . __('For further information view each section under the How to Use.', 'facilities-amenities') . '</p>' .
            '<p>' . __('1. Add Category Options', 'facilities-amenities') . '</p>' .
            '<p>' . __('2. Editing Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('3. Delete Category', 'facilities-amenities') . '</p>' .
            '<p>' . __('4. Search Category', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'add_new_categoryOptions',
        'title'   => __('Add Category Options', 'facilities-amenities'),
        'content' => '<p>' . __('To create a new option, you will need to select a category from the dropdown list. Once you have chosen a category click on the select button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('By default, the icon is a pointing finger, however if you want to change that icon, use the show icon picker toggle', 'facilities-amenities') . '</p>' .
            '<p>' . __('These said icons are the icons that are sat in front of the description for the facilities.', 'facilities-amenities') . '</p>' .
            '<p>' . __('When adding a different icon to the category options, you can select from 60 different available icons.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The selected icon will show which icon is currently selected.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To unselect an icon, you will need to click on it again. The selected icon will show the default icon which will be the pointing finger.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To add a description to the category options, type the description into the field. e.g. Large clubhouse with cozy fireplace', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure that you click and add new option button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Both back to list buttons and back to list link will take you back to the category options page.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'edit_categoryOptions',
        'title'   => __('Editing Category', 'facilities-amenities'),
        'content' => '<p>' . __('To edit you will find the edit link under the category name in the table row. Click on edit to go to the edit category options section.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The category name cannot be edited. Once you have created the name it must be deleted either with the administrator, or the user must have deleted permissions.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To change an icon to another icon, click on the desired icon. You have 60 icons to choose from.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To edit the description, type the desired wording that you want to have read on the front end. e.g. Large clubhouse with comfortable fireplace.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>' .            
            '<p>' . __('Both back to list buttons and back to list link will take you back to the category options page.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'delete_categoryOptions',
        'title'   => __('Delete Category', 'facilities-amenities'),
        'content' => '<p>' . __('To delete a category within the category options table, you will find the deletion under the Category column within the table.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If the Administrator sets the user up with delete permissions that said user will be allowed to delete the data.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you want to delete several records at a time, make sure to check the checkbox next to each record that you want to delete.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Next, click the Bulk Action dropdown and select Delete, then click the Apply button.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'search_categoryOptions',
        'title'   => __('Search Category', 'facilities-amenities'),
        'content' => '<p>' . __('To search for a specific category, icon or description.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Type out the wording that you want to search in the textbox next to the search category button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Next, click on the search category button.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To see the full table click on the x in the textbox and click on the search category button.', 'facilities-amenities') . '</p>',
    ]);

    /* $screen->set_help_sidebar(
        '<p><strong>' . __('Need more help?', 'facilities-amenities') . '</strong></p>' .
        '<p><a href="https://example.com/docs" target="_blank">' . __('View Documentation', 'facilities-amenities') . '</a></p>'
    ); */
}

function facamen_main_sections_page() {        
    // Top / Bottom Sections

    // Check for subscriber
    if ( facamen_user_is_subscriber() ) {
        facamen_admin_notice( __( 'You do not have access to view or edit this plugin.', 'facilities-amenities' ) );
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Main Sections', 'facilities-amenities' ) . '</h1>';
    echo '<hr>';

    /**
     * Include the main submenu page.
     * This file should contain markup for the main admin section.
     */
    $template = plugin_dir_path( __FILE__ ) . 'submenus/submenu-main-sections-page.php';
    if ( file_exists( $template ) ) {
        include $template;
    } else {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Main sections template not found.', 'facilities-amenities' );
        echo '</p></div>';
    }

    echo '</div>';
    
}

/**
 * Help tabs for main sections
 */
function facamen_main_sections_help_tab() {
    $screen = get_current_screen();

    if ( $screen->id !== 'facilities_page_facamen-main-sections' ) {
        return;
    }

    $screen->add_help_tab([
        'id'      => 'main_sections_overview',
        'title'   => __('Overview', 'facilities-amenities'),
        'content' => '<p>' . __('The shortcode for the plugin is [facilities]. You will need to place [facilities] on a page to be able to view the plugin on the frontend.', 'facilities-amenities') . '</p>' .
        '<p>' . __('The Top Section allows for a title and description that can explain what the page is about.', 'facilities-amenities') . '</p>' .
        '<p>' . __('The Bottom Section has two separate content sections. Content section one, and content section two.', 'facilities-amenities') . '</p>' .
        '<p>' . __('In either section, you can show the data or hide the data.', 'facilities-amenities') . '</p>' .
        '<p>' . __('The Settings tab will give you a choice to either show the data with animation or as a static layout.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'main_sections_details',
        'title'   => __('How to Use', 'facilities-amenities'),
        'content' => '<p>' . __('For further information view each section under the How to Use.', 'facilities-amenities') . '</p>' .            
            '<p>' . __('1. Top Section tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('2. Bottom Section tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('3. Settings tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Copy the shortcode [facilities] and paste it onto a new page.') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'top_sections_details',
        'title'   => __('Top Section Tab', 'facilities-amenities'),
        'content' => '<p>' . __('Enter a main title for the campground facilities or amenities. e.g. Facilities at The Family Campground', 'facilities-amenities') . '</p>' .
            '<p>' . __('Enter a good description. e.g. We offer first-class amenities, activities, and services. The Family Campground is a community designed to fulfill every wish of today’s discerning RV owner. Residents and visitors in this wonderful resort community will have access to a complete suite of luxury amenities.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'bottom_sections_details',
        'title'   => __('Bottom Section Tab', 'facilities-amenities'),
        'content' => '<p>' . __('1. Content Section One (1)', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you do not want to display the content from section one, make sure that the checkbox is unchecked. Otherwise, the content from section one will be displayed on the site.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Enter the main title. e.g. Events Calendar', 'facilities-amenities') . '</p>' .
            '<p>' . __('Enter a meaningful description. Click the button below to view our events calendar.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add a descriptive name that will be shown on the button, this is the button text. e.g. Events Calendar', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add the URL link to the button after it has been clicked. e.g. https://example.com', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you do not want the URL link to open into a new tab, uncheck the checkbox next to Open Link in New Tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The delete fields button will delete all the fields data from the database table.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>' .
            /* '<p>' . __('<br/>', 'facilities-amenities') . '</p>' . */
            '<p>' . __('2. Content Section Two (2)', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you do not want to display the content from section two, make sure that the checkbox is unchecked. Otherwise, the content from section two will be displayed on the site.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You may adjust the height of the element. You can choose from 250 - 550 pixels by scrolling the toggle.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Enter the main title. e.g. CONTACT US TODAY.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can change the title text color.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Enter a meaningful description. If you have any questions, please feel free to reach out to us today. We are here to help answer any question that you might have.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can change the description text color.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add a descriptive name that will be shown on the button, this is the button text. e.g. Get in Touch', 'facilities-amenities') . '</p>' .
            '<p>' . __('Add the URL link to the button after it has been clicked. e.g. https://example.com', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you do not want the URL link to open into a new tab, uncheck the checkbox next to Open Link in New Tab.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you want to add a background image to the element, click on the upload image button and select an image from the media library or upload an image of your choice. ', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you no longer want to use the selected image, click on the checkbox - Check to remove image and click Save Changes.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can choice a color for the background color of the element.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The background overlay can be used with either background image or without an image selected.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can change the background overlay opacity. You have a choice from 0 - 1 range.', 'facilities-amenities') . '</p>' .
            '<p>' . __('The delete fields button will delete all the fields data from the database table.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>',
    ]);

    $screen->add_help_tab([
        'id'      => 'settings_sections_details',
        'title'   => __('Settings Tab', 'facilities-amenities'),
        'content' => '<p>' . __('You have a choice to either show the data with animation or as a static layout.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If you want to show your visitors, the plugin data with animation make sure the checkbox is checked.', 'facilities-amenities') . '</p>' .
            '<p>' . __('If the checkbox is unchecked the displaying of the plugin will show as a static layout.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>',
    ]);

    /* $screen->set_help_sidebar(
        '<p><strong>' . __('Need more help?', 'facilities-amenities') . '</strong></p>' .
        '<p><a href="https://example.com/docs" target="_blank">' . __('View Documentation', 'facilities-amenities') . '</a></p>'
    ); */

}

function facamen_permissions_roles_page() {
    // Permissions / Roles
    require_once 'submenus/submenu-permissions-roles-page.php';
}

/**
 * Help tabs for Permissions
 */
function facamen_permissions_roles_help_tab() {
    $screen = get_current_screen();

    // Log the screen ID for debugging
    //error_log( 'Current screen ID: ' . $screen->id );

    if ( $screen->id !== 'facilities_page_facamen-permissions-roles' ) {
        return;
    }

    $screen->add_help_tab([
        'id'      => 'permissions_overview',
        'title'   => __('Overview', 'facilities-amenities'),
        'content' => '<p>' . __('Manage which WordPress roles can access and edit the plugin settings.', 'facilities-amenities') . '</p>' .
        '<p>' . __('The Facilities plugin allows the Administrator to set different roles for each permission such as creating new data, editing the data and deleting the data.', 'facilities-amenities') . '</p>' .
        '<p>' . __('* Note that Administrator is set by default for all permissions and cannot be removed.', 'facilities-amenities') . '</p>',
    ]);

     $screen->add_help_tab([
        'id'      => 'permissions_details',
        'title'   => __('How to Use', 'facilities-amenities'),
        'content' => '<p>' . __('To add a role to create new data for the facilities click inside the text area and a dropdown will appear with the roles that you can select. Click on the role that you want to choose to add to the permission.', 'facilities-amenities') . '</p>' .
            '<p>' . __('To remove a role, you click on the "x" next to the role. e.g. x|Editor or x|Author or x|Contributor.', 'facilities-amenities') . '</p>' .
            '<p>' . __('You can take the same steps to edit the data and delete the data.', 'facilities-amenities') . '</p>' .
            '<p>' . __('Make sure to save changes.', 'facilities-amenities') . '</p>',
    ]);

    /* $screen->set_help_sidebar(
        '<p><strong>' . __('Need more help?', 'facilities-amenities') . '</strong></p>' .
        '<p><a href="https://example.com/docs" target="_blank">' . __('View Documentation', 'facilities-amenities') . '</a></p>'
    ); */

}

/**
 * Admin Notice
 */
function facamen_admin_notice( $message, $type = 'error' ) {
    $type_class = ( $type === 'error' ) ? 'notice-error' : 'notice-success'; 
    echo '<div class="notice ' . esc_attr( $type_class ) . '"><p>' . esc_html( $message ) . '</p></div>';
}

/**
 * Admin Page HTML Output
 */
function facamen_admin_page() {  
    
    // Check for subscriber
    if ( facamen_user_is_subscriber() ) {
        facamen_admin_notice( __( 'You do not have access to view or edit this plugin.', 'facilities-amenities' ) );
        return;
    }
   
    if (isset($_GET['table_reset'])) {
        echo '<div class="updated"><p><strong>' . esc_html__('Facilities Table was successfully overwritten.', 'facilities-amenities') . '</strong></p></div>';
    }    
}

function facamen_category_add_page() {    

    // Check for subscriber
    if ( facamen_user_is_subscriber() ) {
        facamen_admin_notice( __( 'You do not have access to view or edit this plugin.', 'facilities-amenities' ) );
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Add Category Options', 'facilities-amenities' ) . '</h1>';

    /**
     * Include the Category Add Option submenu page.
     * This file should contain markup for the Category Add Option admin section.
     */
    $template = plugin_dir_path( __FILE__ ) . 'submenus/submenu-category-add-page.php';
    if ( file_exists( $template ) ) {
        include $template;
    } else {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Category Add Option template not found.', 'facilities-amenities' );
        echo '</p></div>';
    }

    echo '</div>';

}