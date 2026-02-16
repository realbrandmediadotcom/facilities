<?php

/********************************* 
 * script control
**********************************/

// Make sure to Enqueue Block Library
function facamen_enqueue_block_styles() {
    wp_enqueue_style('wp-block-library');
}
add_action('wp_enqueue_scripts', 'facamen_enqueue_block_styles');

// Enqueue frontend CSS and JS
function facamen_load_scripts() {

    if (is_singular()) {

        // Front-end CSS
        wp_enqueue_style(
            'facilities-amenities-view',
            plugin_dir_url(__FILE__) . 'css/facamen-view.css',
            [],
            FACAMEN_PLUGIN_VERSION,
            'all'
        );

        // Front-end JS
        wp_enqueue_script(
            'facilities-amenities-view-js',
            plugin_dir_url(__FILE__) . 'js/facamen-view.js',
            ['jquery'],
            FACAMEN_PLUGIN_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'facamen_load_scripts', 100);

// Front-end Font Awesome
function facamen_enqueue_fontawesome() {
    wp_enqueue_style(
        'facamen-fontawesome',
        plugin_dir_url(__FILE__) . 'fontawesome/css/all.min.css',
        [],
        FACAMEN_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'facamen-fontawesome-js',
        plugin_dir_url(__FILE__) . 'fontawesome/js/all.min.js',
        [],
        FACAMEN_PLUGIN_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'facamen_enqueue_fontawesome');

// Admin CSS & JS
function facamen_admin_scripts($hook) {

    if (strpos($hook, 'facamen') === false) return;

    // Admin CSS
    wp_enqueue_style(
        'facilities-amenities-admin-style',
        plugin_dir_url(__FILE__) . 'css/facamen-admin.css',
        [],
        FACAMEN_PLUGIN_VERSION
    );

    // Select2 CSS
    wp_enqueue_style(
        'select2-css',
        plugin_dir_url(__FILE__) . 'css/select2.min.css',
        [],
        FACAMEN_PLUGIN_VERSION
    );

    // Select2 JS
    wp_enqueue_script(
        'select2-js',
        plugin_dir_url(__FILE__) . 'js/select2.min.js',
        ['jquery'],
        FACAMEN_PLUGIN_VERSION,
        true
    );

    // Admin JS
    wp_enqueue_script(
        'facamen-admin-js',
        plugin_dir_url(__FILE__) . 'js/facamen-admin.js',
        ['jquery', 'select2-js'],
        FACAMEN_PLUGIN_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'facamen_admin_scripts');

// Admin Font Awesome
function facamen_admin_enqueue_fontawesome() {

    wp_enqueue_style(
        'facamen-fontawesome-admin',
        plugin_dir_url(__FILE__) . 'fontawesome/css/all.min.css',
        [],
        FACAMEN_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'facamen-fontawesome-admin-js',
        plugin_dir_url(__FILE__) . 'fontawesome/js/all.min.js',
        [],
        FACAMEN_PLUGIN_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'facamen_admin_enqueue_fontawesome');

// Media Uploader
add_action('admin_enqueue_scripts', 'facamen_enqueue_media_uploader');
function facamen_enqueue_media_uploader() {

    wp_enqueue_media();

    wp_enqueue_script(
        'facamen-media-uploader',
        plugin_dir_url(__FILE__) . 'js/facamen-media-uploader.js',
        ['jquery'],
        FACAMEN_PLUGIN_VERSION,
        true
    );
}