<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Facamen_Categories_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'facamen_category',
            'plural'   => 'facamen_categories',
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'category'      => __('Category', 'facilities-amenities'),
            'title'         => __('Title', 'facilities-amenities'),
            'buttontext'    => __('Button Text', 'facilities-amenities'),
            'buttonlinkurl' => __('Button Link URL', 'facilities-amenities'),
            'imagelinkurl'  => __('Image', 'facilities-amenities'),
            'enable'        => __('Enabled', 'facilities-amenities'),
        ];
    }

    public function get_hidden_columns() {
        return ['id', 'created_at'];
    }

    public function get_sortable_columns() {
        return [
            'category'      => ['category', false],
            'title'         => ['title', false],
            'buttontext'    => ['buttontext', false],
            'buttonlinkurl' => ['buttonlinkurl', false],
            'imagelinkurl'  => ['imagelinkurl', false],
            'enable'        => ['enable', false],
        ];
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            esc_attr($item['id'])
        );
    }

    public function column_category($item) {
        $edit_url = admin_url('admin.php?page=facamen-categories&action=edit&id=' . $item['id']);
        $delete_url = wp_nonce_url(admin_url('admin.php?page=facamen-categories&action=delete&id=' . $item['id']), 'facamen_delete_category_' . $item['id']);
        $actions = [];        

        if ($item['trashed'] == 1) {
            $restore_url = wp_nonce_url(admin_url('admin.php?page=facamen-categories&action=restore&id=' . $item['id']), 'facamen_restore_category_' . $item['id']);
            $delete_perm_url = wp_nonce_url(admin_url('admin.php?page=facamen-categories&action=delete_permanently&id=' . $item['id']), 'facamen_delete_perm_category_' . $item['id']);

            $actions['restore'] = sprintf('<a href="%s">Restore</a>', esc_url($restore_url));
            $actions['delete'] = sprintf('<a href="%s" class="delete-permanently" onclick="return confirm(\'Are you sure you want to permanently delete this item?\')">Delete Permanently</a>', esc_url($delete_perm_url));
        } else {
            $edit_url = admin_url('admin.php?page=facamen-categories&action=edit&id=' . $item['id']);
            $delete_url = wp_nonce_url(admin_url('admin.php?page=facamen-categories&action=delete&id=' . $item['id']), 'facamen_delete_category_' . $item['id']);

            $actions['edit'] = sprintf('<a href="%s">Edit</a>', esc_url($edit_url));
            $actions['trash'] = sprintf('<a href="%s" class="trash-color" onclick="return confirm(\'Are you sure you want to move this item to trash?\')">Trash</a>', esc_url($delete_url));
        }
        return sprintf('%1$s %2$s', esc_html($item['category']), $this->row_actions($actions));
    }

    public function column_enable($item) {
        return ($item['enable'] === 'yes')
            ? '<span style="color:green;font-weight:bold;">Yes</span>'
            : '<span style="color:red;font-weight:bold;">No</span>';
    }

    public function prepare_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'facamen_categories';

        // Bulk delete handler
        if ('delete' === $this->current_action()) {
            $ids_to_delete = isset($_POST['bulk-delete']) ? array_map('intval', $_POST['bulk-delete']) : [];
            if (!empty($ids_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($ids_to_delete), '%d'));                
                $updated = $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET trashed = 1 WHERE id IN ($placeholders)",
                    ...$ids_to_delete
                ));                
               if ($updated !== false) {
                    set_transient('facamen_bulk_delete_success', true, 10);                    
                }
            }
        }

        // Sorting
        $orderby = (!empty($_REQUEST['orderby'])) ? esc_sql($_REQUEST['orderby']) : 'id';
        $order   = (!empty($_REQUEST['order'])) ? esc_sql($_REQUEST['order']) : 'DESC';

        // Searching
        $search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
        $where_clauses = [];
        $view = isset($_GET['view']) ? $_GET['view'] : 'all';

        if ($view === 'trash') {
            $where_clauses[] = "trashed = 1";
        } else {
            $where_clauses[] = "trashed = 0";
        }

        $params = [];

        if (!empty($search)) {
            $where_clauses[] = "(category LIKE %s OR title LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $where = ' WHERE ' . implode(' AND ', $where_clauses);

        // Pagination setup
        $per_page = 10;
        $current_page = $this->get_pagenum();

        // Count total items (without LIMIT)
        $total_items_sql = "SELECT COUNT(*) FROM $table_name" . $where;
        $total_items = !empty($params)
            ? $wpdb->get_var($wpdb->prepare($total_items_sql, ...$params))
            : $wpdb->get_var($total_items_sql);

        $offset = ( $current_page - 1 ) * $per_page;

        // Main query with LIMIT
        $sql = "SELECT * FROM $table_name" . $where . " ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        $data = $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A);

        $this->items = $data;

        // Register table headers
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Pagination args
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    } 

    public function get_bulk_actions() {
        if (!facamen_user_can_delete_plugin()) {
            return [];
        }

        $view = isset($_GET['view']) ? $_GET['view'] : 'all';

        if ($view === 'trash') {
            return [
                'restore' => __('Restore', 'facilities-amenities'),
                'delete_permanently' => __('Delete Permanently', 'facilities-amenities'),
            ];
        } else {
            return [
                'delete' => __('Move to Trash', 'facilities-amenities'),
            ];
        }
    }   
     
    public function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'facamen_categories';

        if (!facamen_user_can_delete_plugin()) {
            return;
        }

        $action = $this->current_action();
        $ids = isset($_POST['bulk-delete']) ? array_map('intval', $_POST['bulk-delete']) : [];

        if (empty($ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        switch ($action) {
            case 'delete':
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET trashed = 1 WHERE id IN ($placeholders)", ...$ids));
                set_transient('facamen_bulk_delete_success', true, 10);
                break;

            case 'restore':
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET trashed = 0 WHERE id IN ($placeholders)", ...$ids));
                set_transient('facamen_bulk_restore_success', true, 10);
                break;

            case 'delete_permanently':
                // First delete associated options
                $options_table = $wpdb->prefix . 'facamen_categoryoptions';
                foreach ($ids as $id) {
                    $wpdb->delete($options_table, ['categoryid' => $id]);
                }

                // Then delete the categories
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($placeholders)", ...$ids));
                set_transient('facamen_bulk_delete_perm_success', true, 10);
                break;
        }
    }

    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
    }

    //display the image instead of showing the raw URL
    public function column_imagelinkurl($item) {
        $image_url = esc_url($item['imagelinkurl']);
        
        // Optional: fallback image
        if (empty($image_url)) {
            $image_url = plugins_url('/img/spacer.png', dirname(__FILE__));
        }

        return sprintf('<img src="%s" alt="%s" style="max-width:80px;height:auto;" />', $image_url, esc_attr($item['title']));        
    }

    public function get_views() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'facamen_categories';

        // Get counts
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE trashed = 0");
        $trashed_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE trashed = 1");

        $current = isset($_GET['view']) ? $_GET['view'] : 'all';
        $base_url = admin_url('admin.php?page=facamen-categories');

        $views = [];      
        
        // All items view
        $views['all'] = sprintf(
            '<a href="%s"%s>All <span class="count">(%d)</span></a>',
            esc_url($base_url),
            $current === 'all' ? ' class="current"' : '',
            $total_items
        );

        // Trash view — only if user has permission
        if (function_exists('facamen_user_can_delete_plugin') && facamen_user_can_delete_plugin()) {
            $views['trash'] = sprintf(
                '<a href="%s&view=trash"%s>Trash <span class="count">(%d)</span></a>',
                esc_url($base_url),
                $current === 'trash' ? ' class="current"' : '',
                $trashed_items
            );
        }

        return $views;
    }

}  //Facamen_Categories_Table