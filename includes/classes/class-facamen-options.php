<?php
if (!defined('ABSPATH')) exit;

class Facamen_Options {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'facamen_options';
    }

    public function get($name, $default = false) {
        global $wpdb;

        $value = $wpdb->get_var(
            $wpdb->prepare("SELECT value FROM {$this->table_name} WHERE name = %s", $name)
        );

        return $value !== null ? maybe_unserialize($value) : $default;
    }

    public function update($name, $value) {
        global $wpdb;

        $value = maybe_serialize($value);

        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE name = %s", $name)
        );

        if ($existing) {
            $wpdb->update(
                $this->table_name,
                ['value' => $value],
                ['name' => $name],
                ['%s'],
                ['%s']
            );
        } else {
            $wpdb->insert(
                $this->table_name,
                [
                    'name'       => $name,
                    'value'      => $value,
                    'created_at' => current_time('mysql'),
                ],
                ['%s', '%s', '%s']
            );
        }
    }
}