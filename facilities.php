<?php
/*
* Plugin Name: Facilities
* Description: A plugin that will list all Facilities and Amenities for your campground website.
* Version: 1.2.2
* Author: Jasen Haslacker
* Author URI: https://realbrandmedia.com
* Text Domain: facilities-amenities
* Domain Path: /languages
*/

define('FACAMEN_PLUGIN_VERSION', '1.2.2');
define('FACAMEN_DB_VERSION', '1.1.1');

// exit if directly accessed.
if (!defined('ABSPATH')) exit;

// Hide the title for either post or page.
function facamen_remove_title_for_posts_and_pages($title, $id = null) { 
    if (is_singular(['post', 'page']) && in_the_loop() && is_main_query()) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'facamen_remove_title_for_posts_and_pages', 10, 2);

// languages
function facamen_load_textdomain() {
    load_plugin_textdomain(
        'facilities-amenities',
        false,
        plugin_basename(dirname(__FILE__)) . '/languages'
    );
}
add_action('init', 'facamen_load_textdomain');

/*
 * Include files and classes.
*/
/* register_activation_hook(__FILE__, 'facamen_create_table'); */
register_activation_hook(__FILE__, 'facamen_on_activate');
function facamen_on_activate() {

    // Always create tables if missing
    facamen_create_table();

    // Set initial versions if they don't exist
    if (!get_option('facamen_plugin_version')) {
        update_option('facamen_plugin_version', FACAMEN_PLUGIN_VERSION);
    }

    if (!get_option('facamen_db_version')) {
        update_option('facamen_db_version', FACAMEN_DB_VERSION);
    }
}

require_once plugin_dir_path(__FILE__) . '/includes/data-processing.php';
require_once plugin_dir_path(__FILE__) . '/includes/scripts.php';
require_once plugin_dir_path(__FILE__) . '/includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . '/includes/display-functions.php';
require_once plugin_dir_path(__FILE__) . '/includes/classes/class-facamen-options.php';
require_once plugin_dir_path(__FILE__) . '/includes/classes/class-facamen-permissions.php';
require_once plugin_dir_path(__FILE__) . '/includes/plugin-updater.php';
add_action('plugins_loaded', function () {
    // Instantiate classes safely
    $facamen_options = new Facamen_Options();
    $facamen_permissions = new Facamen_Permissions();
});