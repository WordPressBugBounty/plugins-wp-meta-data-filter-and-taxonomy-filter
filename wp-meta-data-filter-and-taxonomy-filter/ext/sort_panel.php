<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MDTF_SORT_PANEL extends MetaDataFilterCore {

    public static function init() {
        parent::init();
        $args = array(
            'labels' => array(
                'name' => esc_html__('MDTF Sort panels', 'meta-data-filter'),
                'singular_name' => esc_html__('Sort panels', 'meta-data-filter'),
                'add_new' => esc_html__('Add New Sort panel', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Sort panel', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Sort panel', 'meta-data-filter'),
                'new_item' => esc_html__('New Sort panel', 'meta-data-filter'),
                'view_item' => esc_html__('View Sort panel', 'meta-data-filter'),
                'search_items' => esc_html__('Search Sort panels', 'meta-data-filter'),
                'not_found' => esc_html__('No Sort panels found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Sort panels found in Trash', 'meta-data-filter'),
                'parent_item_colon' => ''
            ),
            'public' => false,
            'archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => true,
            'menu_position' => null,
            'supports' => array('title'),
            'show_in_menu' => 'edit.php?post_type=' . self::$slug,
            'rewrite' => array('slug' => self::$slug_woo_sort),
            'show_in_admin_bar' => false,
            'menu_icon' => '',
            'taxonomies' => array() // this is IMPORTANT
        );

        register_post_type(self::$slug_woo_sort, $args);

        add_action('admin_init', array(__CLASS__, 'admin_init'), 1);
        add_action('save_post', array(__CLASS__, 'save_post'), 1);

        add_action('woocommerce_before_shop_loop', array(__CLASS__, 'mdtf_catalog_ordering'));
        add_shortcode('mdf_sort_panel', array(__CLASS__, 'mdf_sort_panel'));
    }

    public static function admin_init() {
        add_meta_box("mdf_woo_sort", esc_html__("Sort panel options", 'meta-data-filter'), array(__CLASS__, 'draw_options_meta_box'), self::$slug_woo_sort, "normal", "high");
    }

    public static function draw_options_meta_box($post) {
        $data = array();
        $data['post_id'] = $post->ID;
        $data['settings'] = self::get_woo_search_values($post->ID);
        $data['show_results_tax_navigation'] = get_post_meta($post->ID, 'show_results_tax_navigation', TRUE);
        $data['panel_type'] = get_post_meta($post->ID, 'panel_type', TRUE);
        $data['default_order'] = get_post_meta($post->ID, 'default_order', TRUE);
        $data['default_order_by'] = get_post_meta($post->ID, 'default_order_by', TRUE);
        if (!$data['panel_type']) {
            $data['panel_type'] = 'select';
        }
        if (!$data['default_order']) {
            $data['default_order'] = self::$default_order;
        }
        if (!$data['default_order_by']) {
            $data['default_order_by'] = self::$default_order_by;
        }

        wp_enqueue_script('mdf_sort_panel', self::get_application_uri() . 'js/sort_panel.js', array('jquery', 'jquery-ui-core'));
        self::render_html_e(self::get_application_path() . 'views/sort_panel/metabox.php', $data);
    }

    public static function save_post() {
        if (!empty($_POST)) {
            global $post;
            if (is_object($post)) {
                if ($post->post_type == self::$slug_woo_sort) {
                    if (isset($_POST['mdf_woo_search_values'])) {
                        update_post_meta($post->ID, 'default_order', MetaDataFilterCore::escape($_POST['default_order']));
                        update_post_meta($post->ID, 'default_order_by', MetaDataFilterCore::escape($_POST['default_order_by']));
                        update_post_meta($post->ID, 'panel_type', MetaDataFilterCore::escape($_POST['panel_type']));
                        update_post_meta($post->ID, 'mdf_woo_search_values', self::sanitize_array_r($_POST['mdf_woo_search_values']));
                        if (isset($_POST['show_results_tax_navigation'])) {
                            update_post_meta($post->ID, 'show_results_tax_navigation', sanitize_text_field($_POST['show_results_tax_navigation']));
                        } else {
                            update_post_meta($post->ID, 'show_results_tax_navigation', 0);
                        }
                    }
                }
            }
        }
    }
	public static function sanitize_array_r($arr) {
        $newArr = array();
		if(!is_array($arr)){
			return sanitize_text_field($arr);
		}
        foreach ($arr as $key => $value) {
            $newArr[sanitize_key($key)] = ( is_array($value) ) ? self::sanitize_array_r($value) : sanitize_text_field($value);
        }
        return $newArr;		
	}
    public static function get_woo_search_values($post_id) {
        $settings = get_post_meta($post_id, 'mdf_woo_search_values', TRUE);
        return $settings;
    }

    public static function draw_options_select($selected = 0, $name = "", $id = "") {
        $args = array(
            'posts_per_page' => -1,
            'offset' => 0,
            'category' => '',
            'orderby' => 'ID',
            'order' => 'ASC',
            'include' => '',
            'exclude' => '',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => self::$slug_woo_sort,
            'post_mime_type' => '',
            'post_parent' => '',
            'post_status' => 'publish',
            'suppress_filters' => true);
        $posts = get_posts($args);
        if (!empty($posts) AND is_array($posts)) {
            ?>
            <select class="widefat" name="<?php echo esc_attr($name) ?>" id="<?php echo esc_attr($id) ?>">
                <option value="0"><?php esc_html_e("Not using", 'meta-data-filter'); ?></option>
                <?php
                foreach ($posts as $post) {
                    ?>
                    <option <?php selected($selected, $post->ID) ?> value="<?php echo esc_attr($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
    }

    public static function mdtf_catalog_ordering($atts = array()) {
        if (self::is_page_mdf_data()) {
            $page_meta_data_filter = self::get_page_mdf_data();
            list($meta_query_array, $filter_post_blocks_data, $widget_options) = MetaDataFilterHtml::get_meta_query_array($page_meta_data_filter);
            if (isset($widget_options['woo_search_panel_id']) AND intval($widget_options['woo_search_panel_id']) > 0) {
                remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
                $post_id = intval($widget_options['woo_search_panel_id']);
                $data = array();
                $data['settings'] = self::get_woo_search_values($post_id);
                if (!empty($data['settings']) AND is_array($data['settings'])) {
                    $data['order_by'] = isset($_REQUEST['order_by']) ? sanitize_text_field($_REQUEST['order_by']) : self::$default_order_by;
                    $data['order'] = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : self::$default_order;
                    $panel_type = get_post_meta($post_id, 'panel_type', TRUE);
                    if (!$panel_type) {
                        $panel_type = 'select';
                    }
                    //***
                    if ($panel_type == 'select') {
                        self::render_html_e(self::get_application_path() . 'views/sort_panel/select_filter_panel.php', $data);
                    } else {
                        self::render_html_e(self::get_application_path() . 'views/sort_panel/button_filter_panel.php', $data);
                    }
                    //+++
                    $show_results_tax_navigation = get_post_meta($post_id, 'show_results_tax_navigation', true);
                    if ($show_results_tax_navigation) {
                        echo do_shortcode('[mdf_results_tax_navigation]');
                    }
                }
            }
        } else {
            $post_id = 0;
            if (isset($atts['panel_id'])) {
                $post_id = $atts['panel_id'];
            } else {
                $post_id = (int) self::get_setting('default_sort_panel');
            }

            if (isset($_REQUEST['mdf_panel_id']) AND $_REQUEST['mdf_panel_id'] > 0) {
                $post_id = (int)$_REQUEST['mdf_panel_id'];
            }

            //+++
            $data = array();
            $data['settings'] = self::get_woo_search_values($post_id);
            if (!empty($data['settings']) AND is_array($data['settings'])) {
                $data['order_by'] = isset($_REQUEST['order_by']) ? sanitize_text_field($_REQUEST['order_by']) : self::$default_order_by;
                $data['order'] = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : self::$default_order;
                $panel_type = get_post_meta($post_id, 'panel_type', TRUE);
                if (!$panel_type) {
                    $panel_type = 'select';
                }
                //***
                if ($panel_type == 'select') {
                    self::render_html_e(self::get_application_path() . 'views/sort_panel/select_filter_panel.php', $data);
                } else {
                    self::render_html_e(self::get_application_path() . 'views/sort_panel/button_filter_panel.php', $data);
                }
                //+++
                $show_results_tax_navigation = get_post_meta($post_id, 'show_results_tax_navigation', true);
                if ($show_results_tax_navigation) {
                    echo do_shortcode('[mdf_results_tax_navigation]');
                }
            }
        }
    }

    public static function mdf_sort_panel($atts = array()) {
        ob_start();
        self::mdtf_catalog_ordering($atts);
        return ob_get_clean();
    }

}
