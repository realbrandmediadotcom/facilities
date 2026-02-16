<?php
/********************************* 
 * uninstall.php
**********************************/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Define table names
$options_table         = $wpdb->prefix . 'facamen_options';
$categories_table      = $wpdb->prefix . 'facamen_categories';
$categoryoptions_table = $wpdb->prefix . 'facamen_categoryoptions';

// Drop all custom tables if they exist
$wpdb->query( "DROP TABLE IF EXISTS `$categoryoptions_table`" );
$wpdb->query( "DROP TABLE IF EXISTS `$categories_table`" );
$wpdb->query( "DROP TABLE IF EXISTS `$options_table`" );

// Remove any options/flags stored in wp_options
delete_option( 'facamen_options_table_exists' );
delete_option( 'facamen_categories_table_exists' );
delete_option( 'facamen_categoryoptions_table_exists' );
/* delete_option( 'facamen_notice_dismissed' ); */