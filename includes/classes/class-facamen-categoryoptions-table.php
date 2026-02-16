<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Facamen_Categoryoptions_Table extends WP_List_Table {

    private $category_id = 0;

    private $search_term = '';

    public function __construct($category_id = 0, $search_term = '') {
        parent::__construct([
            'singular' => 'facamen_category_option',
            'plural'   => 'facamen_category_options',
            'ajax'     => false
        ]);

        $this->category_id = intval($category_id);
        $this->search_term = sanitize_text_field($search_term);
    }

    /** Define the columns to display */
    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />', // Checkbox for bulk actions
            'categoryname'  => __('Category', 'facilities-amenities'),
            'name'          => __('Icon', 'facilities-amenities'),
            'value'         => __('Description', 'facilities-amenities'),
            'enable'        => __('Enabled', 'facilities-amenities'),
        ];
    }

    /** Enable checkbox for bulk actions */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="category_option[]" value="%d" />',
            $item->id
        );
    }
   
    /** Prepare the data for each column */
    public function column_name($item) {
        // Show the icon only
        return '<i class="' . esc_attr($item->name) . '"></i>';
        
        // This will show the icon and full name from the database.
        // return '<i class="' . esc_attr($item->name) . '"></i> ' . esc_html($item->name);
    }

    /** Default column rendering */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'categoryname':
            case 'value':
                return esc_html($item->$column_name); 

           case 'enable':
                $enabled = $item->enable === 'yes';
                $color = $enabled ? 'green' : 'red';
                $text = $enabled ? __('Yes', 'facilities-amenities') : __('No', 'facilities-amenities');
                return '<span style="color:' . esc_attr($color) . '; font-weight:bold;">' . esc_html($text) . '</span>';
                                
            default:
                return isset($item->$column_name) ? esc_html($item->$column_name) : '';
        }
    }

    /** Sortable Columns */
    public function get_sortable_columns() {
        return [
            'categoryname' => ['categoryname', false], 
            'name'         => ['name', false],
            'value'        => ['value', false],
            'enable'       => ['enable', false],
        ];
    }

    /** Prepare the items for display */
    public function prepare_items() {
        global $wpdb;

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $table_name = $wpdb->prefix . 'facamen_categoryoptions';
        
        $view = $_GET['view'] ?? 'all';
        $trashed_condition = ($view === 'trash') ? 'trashed = 1' : 'trashed = 0';

        // Handle bulk delete
        if ('delete' === $this->current_action()) {
            $ids_to_delete = isset($_POST['category_option']) ? array_map('intval', $_POST['category_option']) : [];
            if (!empty($ids_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($ids_to_delete), '%d'));                
                    $updated = $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET trashed = 1 WHERE id IN ($placeholders)",
                    ...$ids_to_delete
                ));
                
                if ($updated !== false) {
                    // Store a short-lived flag
                    set_transient('facamen_bulk_delete_success', true, 10);                    
                }
            }
        }
 
        // Sorting
        $allowed_columns = ['categoryname', 'name', 'value', 'id'];

        $orderby = (!empty($_GET['orderby']) && in_array($_GET['orderby'], $allowed_columns))
            ? $_GET['orderby']
            : 'categoryname';

        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']))
            ? strtoupper($_GET['order']) : 'ASC';

        // Count total items
        if ($this->category_id > 0) {
            $total_items = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE categoryid = %d", $this->category_id)
            );
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }      

        $search_sql = '';
        $search_args = [];

        if (!empty($this->search_term)) {
            $search_sql = " AND (categoryname LIKE %s OR name LIKE %s OR value LIKE %s)";
            $like = '%' . $wpdb->esc_like($this->search_term) . '%';
            $search_args = [$like, $like, $like];
        }

        if ($this->category_id > 0) {           
            $base_query = "FROM $table_name WHERE categoryid = %d AND $trashed_condition $search_sql";
            $query_args = array_merge([$this->category_id], $search_args);
        } else {            
            $view = $_GET['view'] ?? 'all';
            $trashed_condition = ($view === 'trash') ? 'trashed = 1' : 'trashed = 0';

            $base_query = "FROM $table_name WHERE $trashed_condition $search_sql";
            $query_args = $search_args;
        }

        // Get total items count
        $total_items_sql = "SELECT COUNT(*) " . $base_query;
        $total_items = !empty($query_args)
            ? $wpdb->get_var($wpdb->prepare($total_items_sql, ...$query_args))
            : $wpdb->get_var($total_items_sql);

        // Fetch actual data
        $data_sql = "SELECT id, categoryid, categoryname, name, value, enable " . $base_query . " ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $data_args = array_merge($query_args, [$per_page, $offset]);

        $data = !empty($data_args)
            ? $wpdb->get_results($wpdb->prepare($data_sql, ...$data_args))
            : $wpdb->get_results($data_sql);

        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns()
        ];
    }

    /** Custom column rendering for Category Name with actions */
    public function column_categoryname($item) {

        // Row action links
        $view = $_GET['view'] ?? 'all';

        if ($view === 'trash') {
            $actions = [                
                'restore' => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(
                        wp_nonce_url(
                            add_query_arg([
                                'page' => $_REQUEST['page'],
                                'action' => 'restore',
                                'id' => $item->id,
                            ]),
                            'facamen_restore_category_' . $item->id
                        )
                    ),
                    __('Restore', 'facilities-amenities')
                ),
                'delete_perm' => sprintf(
                    '<a href="?page=%s&action=delete_perm&id=%d" class="delete-permanently" onclick="return confirm(\'Are you sure you want to permanently delete this item?\')">%s</a>',
                    esc_attr($_REQUEST['page']),
                    $item->id,
                    __('Delete Permanently', 'facilities-amenities')
                ),
            ];
        } else {
            $actions = [
                'edit' => sprintf(
                    '<a href="?page=%s&action=edit&id=%d">%s</a>',
                    esc_attr($_REQUEST['page']),
                    $item->id,
                    __('Edit', 'facilities-amenities')
                ),
                'delete' => sprintf(
                    '<a href="?page=%s&action=delete&id=%d" class="trash-color" onclick="return confirm(\'Are you sure you want to move this item to trash?\')">%s</a>',
                    esc_attr($_REQUEST['page']),
                    $item->id,
                    __('Trash', 'facilities-amenities')
                ),
            ];
        }

        // Return the name + row actions
        return sprintf(
            '%1$s %2$s',
            esc_html($item->categoryname),
            $this->row_actions($actions)
        );
    }
  
    public function get_bulk_actions() {
        if (!facamen_user_can_delete_plugin()) {
            return [];
        }

        $view = $_GET['view'] ?? 'all';

        if ($view === 'trash') {
            return [
                'restore'       => __('Restore', 'facilities-amenities'),
                'delete_perm'   => __('Delete Permanently', 'facilities-amenities'),
            ];
        } else {
            return [
                'delete' => __('Move to Trash', 'facilities-amenities'),
            ];
        }
    }

    public function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'facamen_categoryoptions';

        if (!facamen_user_can_delete_plugin()) {
            return;
        }

        $action = $this->current_action();

        $ids = isset($_POST['category_option']) ? array_map('intval', $_POST['category_option']) : [];

        if (empty($ids)) return;

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        switch ($action) {
            case 'delete': // move to trash
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET trashed = 1 WHERE id IN ($placeholders)",
                    ...$ids
                ));
                set_transient('facamen_bulk_delete_success', true, 10);
                break;

            case 'restore':
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET trashed = 0 WHERE id IN ($placeholders)",
                    ...$ids
                ));
                set_transient('facamen_bulk_restore_success', true, 10);
                break;

            case 'delete_perm':
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN ($placeholders)",
                    ...$ids
                ));
                set_transient('facamen_bulk_delete_perm_success', true, 10);
                break;
        }        
    }

    public function get_views() {
        
        global $wpdb;

        $table_name = $wpdb->prefix . 'facamen_categoryoptions';
        $current_view = $_GET['view'] ?? 'all';

        $total_all = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE trashed = 0");
        $total_trash = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE trashed = 1");

        $base_url = remove_query_arg(['view']);
        $page_url = esc_url(add_query_arg([], $base_url));

        $views = [];

        // All items view
        $views['all'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            esc_url(add_query_arg('view', 'all', $page_url)),
            ($current_view === 'all') ? 'current' : '',
            __('All', 'facilities-amenities'),
            $total_all
        );

        if (facamen_user_can_delete_plugin()) {
            $views['trash'] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url(add_query_arg('view', 'trash', $page_url)),
                ($current_view === 'trash') ? 'current' : '',
                __('Trash', 'facilities-amenities'),
                $total_trash
            );
        }

        return $views;
        
    }
    
}