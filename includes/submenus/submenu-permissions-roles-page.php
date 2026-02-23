<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// check permissions
if ( facamen_user_is_subscriber() ) {
    echo '<div class="notice notice-error"><p>' . esc_html__('You do not have access to view or edit this plugin.', 'facilities-amenities') . '</p></div>';
    return;
}

/* add_action('admin_head', function () {
    echo '<script>document.documentElement.classList.add("js-enabled");</script>';
});
 */
$facamen_permissions = new Facamen_Permissions();

/*
|--------------------------------------------------------------------------
| Handle Form Submission (ONLY when submitted)
|--------------------------------------------------------------------------
*/
if (
    isset($_POST['facamen_save_permissions']) &&
    current_user_can('manage_options') &&
    check_admin_referer('facamen_save_permissions')
) {
    $facamen_permissions->update_permissions($_POST);
}

$permissions = $facamen_permissions->map_permissions();


/**
 * Define rows explicitly (NO foreach over permissions dump)
 */
$permission_rows = [
    'facamen_permissions_create' => [
        'label'       => __('Create New Data', 'facilities-amenities'),
        'description' => __('Users that have at least one of these roles will be able to create new data.', 'facilities-amenities'),
    ],
    'facamen_permissions_edit' => [
        'label'       => __('Edit The Data', 'facilities-amenities'),
        'description' => __('Users that have at least one of these roles will be able to edit existing data.', 'facilities-amenities'),
    ],
    'facamen_permissions_delete' => [
        'label'       => __('Delete The Data', 'facilities-amenities'),
        'description' => __('Users that have at least one of these roles will be able to delete data.', 'facilities-amenities'),
    ],
];
?>

<div class="wrap">
    <h1><?php esc_html_e('Permissions / Roles Settings', 'facilities-amenities'); ?></h1>
    <hr class="spaced-hr">
    <?php settings_errors('facamen_permissions'); ?>
    <form method="post">
        <?php wp_nonce_field('facamen_save_permissions'); ?>
        <table class="form-table">
            <tbody>
                <?php foreach ($permission_rows as $permission_key => $data): ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr($permission_key); ?>">
                                <?php echo esc_html($data['label']); ?>
                            </label>
                        </th>

                        <td class="facamen-js-field">
                            <?php
                            if (isset($permissions[$permission_key])) {
                                $facamen_permissions->render_field($permission_key);
                            }
                            ?>

                            <p class="description">
                                <?php echo esc_html($data['description']); ?>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <input type="submit" name="facamen_save_permissions" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'facilities-amenities'); ?>">
        </p>
    </form>
</div>