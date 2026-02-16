<?php

/********************************* 
 * data processing
**********************************/

/**
 * Create the tables on plugin activation
 */
function facamen_create_table() {
    global $wpdb;
    $options_table = $wpdb->prefix . 'facamen_options';
    $categories_table = $wpdb->prefix . 'facamen_categories';
    $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';

    // Check if options table exists
    $options_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s",
        $options_table
    ));

    if ($options_exists === $options_table) {
        update_option('facamen_options_table_exists', true);
    } else {
        facamen_build_table($options_table);
    }

    // Check if categories table exists
    $categories_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s",
        $categories_table
    ));

    if ($categories_exists === $categories_table) {
        update_option('facamen_categories_table_exists', true);
    } else {
        facamen_build_categories_table($categories_table);
    }

     // Check if category options table exists
    $category_options_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s",
        $category_options_table
    ));

    if ($category_options_exists === $category_options_table) {
        update_option('facamen_categoryoptions_table_exists', true);
    } else {
        facamen_build_categoryoptions_table($category_options_table);
    }

    // Store and Check Database Verison
    $current_version = get_option('facamen_db_version', '1.0.0');

    if (version_compare($current_version, FACAMEN_DB_VERSION, '<')) {
        facamen_upgrade_database_schema();
        update_option('facamen_db_version', FACAMEN_DB_VERSION);
    }  

    facamen_set_default_plugin_options();

}

/**
 * Build the facamen_options table
 */
function facamen_build_table($table_name) {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(191) NOT NULL UNIQUE,
        value longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Build the facamen_categories table
 */
function facamen_build_categories_table($table_name) {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category varchar(191) NOT NULL,
        title varchar(191) NOT NULL,
        buttontext varchar(191),
        buttonlinkurl text,
        opennewtab ENUM('yes','no') NOT NULL DEFAULT 'yes',
        imagelinkurl text,
        enable ENUM('yes','no') NOT NULL DEFAULT 'no',
        enable_button_options ENUM('yes','no') NOT NULL DEFAULT 'no',
        trashed TINYINT(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Build the facamen_categoryoptions table
 */
function facamen_build_categoryoptions_table($table_name) {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $categories_table = $wpdb->prefix . 'facamen_categories';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        categoryid mediumint(9) NOT NULL,
        categoryname varchar(191) NOT NULL,
        name varchar(191) NOT NULL,
        value longtext NOT NULL,
        enable ENUM('yes','no') NOT NULL DEFAULT 'yes',
        trashed TINYINT(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (categoryid) REFERENCES $categories_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Prefill default options on activation or upgrade
 */
function facamen_set_default_plugin_options() {
    $defaults = [
        'bottom_visible'      => 'yes',
        'open_new_tab'        => 'yes',
        'showanimation'       => 'no',
        'bottom_two_visible'  => 'yes',
        'open_two_new_tab'    => 'yes',
    ];

    foreach ($defaults as $key => $value) {
        if (!facamen_get_option($key)) {
            facamen_set_option($key, $value);
        }
    }
}

/**
 * Upgrade existing tables to match latest schema
 */
function facamen_upgrade_database_schema() {
    global $wpdb;
    $categories_table = $wpdb->prefix . 'facamen_categories';
    $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';

    // Ensure 'trashed' column exists in category options table
    $trashed_column_exists = $wpdb->get_var("SHOW COLUMNS FROM $category_options_table LIKE 'trashed'");
    if (!$trashed_column_exists) {
        $wpdb->query("ALTER TABLE $category_options_table ADD COLUMN trashed TINYINT(1) NOT NULL DEFAULT 0");
    }

    // Ensure 'enable' column exists in category options table
    $enable_column_exists = $wpdb->get_var("SHOW COLUMNS FROM $category_options_table LIKE 'enable'");
    if (!$enable_column_exists) {
        $wpdb->query("ALTER TABLE $category_options_table ADD COLUMN enable ENUM('yes','no') NOT NULL DEFAULT 'yes'");
    }

    // Ensure 'opennewtab' column exists in categories table
    $opennewtab_exists = $wpdb->get_var("SHOW COLUMNS FROM $categories_table LIKE 'opennewtab'");
    if (!$opennewtab_exists) {
        $wpdb->query("ALTER TABLE $categories_table ADD COLUMN opennewtab ENUM('yes','no') NOT NULL DEFAULT 'yes'");
    }

    // Refill defaults just in case
    facamen_set_default_plugin_options();
}


/**
 * Wrapper function to set/update an option
 */
function facamen_set_option($name, $value) {
    global $wpdb;
    $table = $wpdb->prefix . 'facamen_options';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE name = %s", $name
    ));

    if ($exists) {
        $wpdb->update($table, ['value' => maybe_serialize($value)], ['name' => $name]);
    } else {
        $wpdb->insert($table, ['name' => $name, 'value' => maybe_serialize($value)]);
    }
}

/**
 * Wrapper function to get an option
 */
function facamen_get_option($name, $default = false) {
    global $wpdb;
    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT value FROM {$wpdb->prefix}facamen_options WHERE name = %s", $name
    ));
    return $value !== null ? maybe_unserialize($value) : $default;
}

/**
 * Wrapper function to delete an option
 */
function facamen_delete_option($name) {
    global $wpdb;
    return $wpdb->delete($wpdb->prefix . 'facamen_options', ['name' => $name]);
}

/**
 * Wrapper function to delete a category row by ID
 */
function facamen_delete_category($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'facamen_categories';

    return $wpdb->delete($table, ['id' => intval($id)]);
}

/**
 * Wrapper function to delete a category option row by ID
 */
function facamen_delete_category_option($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'facamen_categoryoptions';

    return $wpdb->delete($table, ['id' => intval($id)]);
}

/**
 * Overwrite handler
 */
add_action('admin_init', 'facamen_handle_table_overwrite');

function facamen_handle_table_overwrite() {
    if (
        isset($_GET['facamen_overwrite_table']) &&
        $_GET['facamen_overwrite_table'] == '1' &&
        current_user_can('manage_options') &&
        check_admin_referer('facamen_overwrite_nonce')
    ) {
        global $wpdb;

        $options_table = $wpdb->prefix . 'facamen_options';
        $categories_table = $wpdb->prefix . 'facamen_categories';
        $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';

        // Drop tables
        $wpdb->query("DROP TABLE IF EXISTS `$category_options_table`");
        $wpdb->query("DROP TABLE IF EXISTS `$categories_table`");
        $wpdb->query("DROP TABLE IF EXISTS `$options_table`");        

        // Recreate tables
        facamen_build_table($options_table);
        facamen_build_categories_table($categories_table);
        facamen_build_categoryoptions_table($category_options_table);

        // Remove all flags and dismissals
        delete_option('facamen_options_table_exists');
        delete_option('facamen_categories_table_exists');
        delete_option('facamen_categoryoptions_table_exists');
        /* delete_option('facamen_notice_dismissed'); */ // add this line

        wp_redirect(admin_url('admin.php?page=facamen-options&table_reset=1'));
        exit;
    }
}


/**
 * Dismissal handler
 */
add_action('admin_init', 'facamen_handle_dismiss_notice');
function facamen_handle_dismiss_notice() {
    if (
        isset($_GET['facamen_dismiss_notice']) &&
        $_GET['facamen_dismiss_notice'] == '1' &&
        current_user_can('manage_options') &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce($_GET['_wpnonce'], 'facamen_dismiss_notice_nonce')
    ) {
        // Remove the flag in DB
        delete_option('facamen_options_table_exists');
       /*  delete_option('facamen_notice_dismissed'); */

        // Redirect to clean URL
        wp_redirect(remove_query_arg(['facamen_dismiss_notice', '_wpnonce']));
        exit;
    }
}

/**
 * Get categories with their options
 */
function facamen_get_categories_with_options() {
    global $wpdb;
    $categories_table = $wpdb->prefix . 'facamen_categories';
    $category_options_table = $wpdb->prefix . 'facamen_categoryoptions';
    
    $results = $wpdb->get_results("
        SELECT 
            c.id, 
            c.title, 
            c.buttontext, 
            c.buttonlinkurl, 
            c.imagelinkurl, 
            c.enable,
            c.opennewtab,
            o.id AS option_id, 
            o.name AS option_name, 
            o.value AS option_value,
            o.enable AS option_enable
        FROM $categories_table c
        LEFT JOIN $category_options_table o 
            ON c.id = o.categoryid AND o.enable = 'yes'
        WHERE c.enable = 'yes'
        ORDER BY c.id ASC, o.id ASC
    ");

    return $results;
}