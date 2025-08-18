<?php

/**
 * Wheels List Table
 *
 * @package SWN_Deluxe
 */

namespace SWN_Deluxe;

if (! defined('ABSPATH')) exit;

if (! class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Wheels_List_Table extends \WP_List_Table
{

    public function __construct()
    {
        parent::__construct([
            'singular' => __('Wheel', 'swn-deluxe'),
            'plural'   => __('Wheels', 'swn-deluxe'),
            'ajax'     => false,
        ]);
    }

    /**
     * Define table columns
     */
    public function get_columns()
    {
        return [
            'id'          => __('ID', 'swn-deluxe'),
            'name'        => __('Name', 'swn-deluxe'),
            'display_name' => __('Display Name', 'swn-deluxe'),
            'slug'        => __('Slug', 'swn-deluxe'),
            'status'      => __('Status', 'swn-deluxe'),
            'created_at'  => __('Created At', 'swn-deluxe'),
        ];
    }

    /**
     * Default column rendering
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'display_name':
            case 'slug':
            case 'status':
            case 'created_at':
                return esc_html($item[$column_name]);
            default:
                return '';
        }
    }

    /**
     * Name column with row actions
     */
    protected function column_name($item)
    {
        $settings_url   = admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_EDIT_PAGE'] . '&id=' . intval($item['id']));
        $items_url  = admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE'] . '&wheel_id=' . intval($item['id']));

        $shortcode = sprintf('[' . Shortcode::SHORTCODE_NAME . ' slug="%s"]', esc_attr($item['slug']));

        $title = sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url($settings_url),
            esc_html($item['name'])
        );

        $actions = [
            'settings'  => sprintf('<a href="%s">%s</a>', esc_url($settings_url), __('Wheel Settings', 'swn-deluxe')),
            'items'     => sprintf('<a href="%s">%s</a>', esc_url($items_url), __('Items', 'swn-deluxe')),
            'shortcode' => sprintf(
                '<a href="#" class="copy-shortcode" data-shortcode="%s">%s</a>',
                esc_attr($shortcode),
                __('Copy Shortcode', 'swn-deluxe')
            ),
        ];

        return $title . $this->row_actions($actions);
    }

    /**
     * Sortable columns
     */
    protected function get_sortable_columns()
    {
        return [
            'id'          => ['id', false],
            'name'        => ['name', false],
            'display_name' => ['display_name', false],
            'slug'        => ['slug', false],
            'status'      => ['status', false],
            'created_at'  => ['created_at', false],
        ];
    }

    /**
     * Prepare items
     */
    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $data = array_map(function ($wheel) {
            return [
                'id'          => $wheel->id,
                'name'        => $wheel->name,
                'display_name' => $wheel->display_name,
                'slug'        => $wheel->slug,
                'status'      => ucfirst($wheel->status),
                'created_at'  => $wheel->created_at,
            ];
        }, Wheels::get_all());

        // Pagination
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = count($data);

        $this->items = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }
}
