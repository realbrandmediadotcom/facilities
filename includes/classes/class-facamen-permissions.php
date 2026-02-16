<?php
if (!defined('ABSPATH')) exit;

class Facamen_Permissions {

    private $options;

    public function __construct() {
        $this->options = new Facamen_Options();

        add_action('admin_init', [$this, 'update_permissions']);
        $this->set_default_permissions();
    }

    public function map_permissions() {
        return [
            'facamen_permissions_create' => ['create_facamen_items'],
            'facamen_permissions_edit'   => ['edit_facamen_items'],
            'facamen_permissions_delete' => ['delete_facamen_items'],
        ];
    }

    public function set_default_permissions() {
        if ($this->options->get('facamen_permissions_default')) return;

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

        $all_roles = array_keys($this->get_roles());
        $roles_to_remove = array_diff($all_roles, $role_names);

        // Ensure administrator is always in roles with caps
        if (!in_array('administrator', $role_names, true)) {
            $role_names[] = 'administrator';
        }

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

    public function update_permissions() {
        if (!current_user_can('manage_options') || empty($_POST['facamen_save_permissions'])) return;
        if (!check_admin_referer('facamen_save_permissions')) return;

        $permissions = $this->map_permissions();

        foreach ($permissions as $perm => $caps) {
            $roles = isset($_POST[$perm]) ? array_map('sanitize_text_field', $_POST[$perm]) : [];

            if (!in_array('administrator', $roles, true)) {
                $roles[] = 'administrator';
            }

            $this->options->update($perm, $roles);
            $this->assign_capabilities($perm, $roles, $permissions);
        }

        add_settings_error('facamen_permissions', 'permissions_saved', __('Permissions updated.'), 'updated');
    }

    public function get_roles() {
        if (!function_exists('wp_roles')) {
            require_once ABSPATH . 'wp-includes/class-wp-roles.php';
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

        return $filtered;
    }

    public function render_field($field) {
        $roles = $this->get_roles();
        $selected = $this->options->get($field, []);

        echo '<select name="' . esc_attr($field) . '[]" multiple class="facamen-select2">';
        foreach ($roles as $role => $label) {
            $is_selected = in_array($role, $selected) || $role === 'administrator' ? 'selected' : '';
            $is_disabled = $role === 'administrator' ? 'disabled' : '';
            echo '<option value="' . esc_attr($role) . '" ' . $is_selected . ' ' . $is_disabled . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

}