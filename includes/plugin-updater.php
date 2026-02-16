<?php
/***********************************************
 * FACAMEN – Plugin Update Handler
 ***********************************************/

if (!defined('ABSPATH')) exit;

/**
 * Run this on every page load (admin + frontend)
 * to check whether plugin or database needs updating.
 */
function facamen_maybe_run_updates() {

    // Get stored versions, leave these numbers and do not change them.
    $stored_plugin_version = get_option('facamen_plugin_version', '1.0.0');
    $stored_db_version     = get_option('facamen_db_version', '1.0.0');

    // Check if plugin version changed
    if (version_compare($stored_plugin_version, FACAMEN_PLUGIN_VERSION, '<')) {

        /**
         * ▪️ Plugin Updates (non-DB changes)
         * - Refresh default settings
         * - Clear caches
         * - File structure updates
         * - Any tasks that should run once per update
         */
        facamen_run_plugin_updates($stored_plugin_version);

        // Update stored plugin version
        update_option('facamen_plugin_version', FACAMEN_PLUGIN_VERSION);
    }

    // Check if DB version changed
    if (version_compare($stored_db_version, FACAMEN_DB_VERSION, '<')) {

        /**
         * ▪️ Database Schema Updates
         * Runs only when the DB version changes.
         */
        facamen_upgrade_database_schema();

        // Update stored DB version
        update_option('facamen_db_version', FACAMEN_DB_VERSION);
    }
}
add_action('plugins_loaded', 'facamen_maybe_run_updates');

/*******************************************************
 * PLUGIN UPDATE TASKS (Non-Database)
 *******************************************************/
function facamen_run_plugin_updates($old_version) {

    /**
     * Example upgrade path — add your own here.
     */

    // If updating from before 1.2.0, add future tasks:
    if (version_compare($old_version, '1.2.0', '<')) {

        // Re-fill defaults if needed
        facamen_set_default_plugin_options();

        // Flush rewrite rules if plugin registers CPTs (optional)
        // flush_rewrite_rules();
    }

    // Add future upgrade blocks here:
    /*
    if (version_compare($old_version, '1.3.0', '<')) {
        // add new upgrade actions here
    }
    */
}