<?php
if (!defined('ABSPATH')) exit;

class Facamen_Permissions {

    private $roles_cache = null;
    private $options;

    public function __construct() {
        $this->options = new Facamen_Options();
    }

    public function map_permissions() {
        return [
            'facamen_permissions_create' => ['create_facamen_items'],
            'facamen_permissions_edit'   => ['edit_facamen_items'],
            'facamen_permissions_delete' => ['delete_facamen_items'],
        ];
    }

    public function set_default_permissions() {

        if ($this->options->get('facamen_permissions_default') === 'yes') {
            return;
        }

        $all_roles = ['administrator'];

        $permissions = [
            'facamen_permissions_create' => $all_roles,
            'facamen_permissions_edit'   => $all_roles,
            'facamen_permissions_delete' => $all_roles,
        ];

        foreach ($permissions as $perm => $roles) {
            $this->options->update($perm, $roles);
            $this->assign_capabilities($perm, $roles);
        }

        $this->options->update('facamen_permissions_default', 'yes');
    }

    public function assign_capabilities($permission, $role_names, $permissions_map = []) {

        if (empty($permissions_map)) {
            $permissions_map = $this->map_permissions();
        }

		// Guard clause to prevent undefined index errors
		if (!isset($permissions_map[$permission])) {
			return;
		}
		
        $all_roles = array_keys(wp_roles()->roles);

        // Always ensure administrator keeps caps
        if (!in_array('administrator', $role_names, true)) {
            $role_names[] = 'administrator';
        }

        $roles_to_remove = array_diff($all_roles, $role_names);

        foreach ($role_names as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($permissions_map[$permission] as $cap) {
                    $role->add_cap($cap);
                }
            }
        }

        foreach ($roles_to_remove as $role_name) {
            if ($role_name === 'administrator') continue;

            $role = get_role($role_name);
            if ($role) {
                foreach ($permissions_map[$permission] as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    public function update_permissions($posted_data) {

        $permissions = $this->map_permissions();

        foreach ($permissions as $perm => $caps) {

            $roles = isset($posted_data[$perm])
                ? array_map('sanitize_text_field', $posted_data[$perm])
                : [];

            if (!in_array('administrator', $roles, true)) {
                $roles[] = 'administrator';
            }

            $this->options->update($perm, $roles);
            $this->assign_capabilities($perm, $roles, $permissions);
        }

        add_settings_error(
            'facamen_permissions',
            'permissions_saved',
            __('Permissions updated.'),
            'updated'
        );
    }

    public function get_roles() {

        if ($this->roles_cache !== null) {
            return $this->roles_cache;
        }

        $wp_roles = wp_roles();
        $roles = $wp_roles->get_names();

        $filtered = [];

        foreach ($roles as $role_name => $label) {
            $role_obj = get_role($role_name);
            if ($role_obj && $role_obj->has_cap('edit_posts')) {
                $filtered[$role_name] = $label;
            }
        }

        $this->roles_cache = $filtered;

        return $this->roles_cache;
    }

    public function render_field($field) {

        $roles = $this->get_roles();
        $selected = (array) $this->options->get($field, []);

        echo '<select name="' . esc_attr($field) . '[]" multiple class="facamen-select2">';

        foreach ($roles as $role => $label) {

            $is_selected = in_array($role, $selected, true) || $role === 'administrator'
                ? 'selected'
                : '';

            $is_disabled = $role === 'administrator'
                ? 'disabled'
                : '';

            echo '<option value="' . esc_attr($role) . '" ' . $is_selected . ' ' . $is_disabled . '>' . esc_html($label) . '</option>';
        }

        echo '</select>';
    }
}