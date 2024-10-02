<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MetaDataFilterShortcodes extends MetaDataFilterCore {

    public static $cached_filter_posts = array(); //for features panel shortcode

    public static function init() {
        parent::init();
        $args = array(
            'labels' => array(
                'name' => esc_html__('MDTF Shortcodes', 'meta-data-filter'),
                'singular_name' => esc_html__('Shortcodes Links', 'meta-data-filter'),
                'add_new' => esc_html__('Add New Shortcode', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Shortcode', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Shortcode', 'meta-data-filter'),
                'new_item' => esc_html__('New Shortcode', 'meta-data-filter'),
                'view_item' => esc_html__('View Shortcode', 'meta-data-filter'),
                'search_items' => esc_html__('Search Shortcodes', 'meta-data-filter'),
                'not_found' => esc_html__('No Shortcodes found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Shortcodes found in Trash', 'meta-data-filter'),
                'parent_item_colon' => ''
            ),
            'public' => false,
            'archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => true,
            'menu_position' => null,
            'show_in_menu' => 'edit.php?post_type=' . self::$slug,
            'supports' => array('title', 'excerpt'),
            'rewrite' => array('slug' => self::$slug_shortcodes),
            'show_in_admin_bar' => false,
            'menu_icon' => '',
            'taxonomies' => array()
        );

        register_post_type(self::$slug_shortcodes, $args);

        //+++
        add_action('admin_init', array(__CLASS__, 'admin_init'), 1);

        //+++
        add_shortcode('meta_data_filter_results', array(__CLASS__, 'do_meta_data_filter_shortcode'));
        add_shortcode('mdf_search_form', array(__CLASS__, 'do_mdf_search_form'));
        add_shortcode('mdf_search_button', array(__CLASS__, 'do_mdf_search_button'));
        add_shortcode('mdf_force_searching', array(__CLASS__, 'force_searching'));
        add_shortcode('mdf_value', array(__CLASS__, 'mdf_value'));
        add_shortcode('mdf_select_title', array(__CLASS__, 'mdf_select_title'));
        add_shortcode('mdf_post_features_panel', array(__CLASS__, 'post_features_panel'));
        add_shortcode('mdf_results_tax_navigation', array(__CLASS__, 'results_tax_navigation'));
        add_shortcode('mdf_results_by_ajax', array(__CLASS__, 'results_by_ajax'));
        add_shortcode('mdf_range_select', array(__CLASS__, 'range_select'));
        add_shortcode('mdf_search_panel', array(__CLASS__, 'show_search_panel'));
        //woocommerce overloaded
        add_shortcode('mdf_products', array(__CLASS__, 'mdf_products'));
        add_shortcode('mdf_custom', array(__CLASS__, 'mdf_custom'));
        add_action('load-post.php', array(__CLASS__, "post_inits"));
        add_action('load-post-new.php', array(__CLASS__, "post_inits"));
        add_action('save_post', array(__CLASS__, 'save_post'), 1);
        add_action("manage_" . self::$slug_shortcodes . "_posts_custom_column", array(__CLASS__, "show_edit_columns_content"));
        add_filter("manage_" . self::$slug_shortcodes . "_posts_columns", array(__CLASS__, "show_edit_columns"));
        add_action('wp_ajax_mdf_draw_shortcode_html_items', array(__CLASS__, 'draw_shortcode_html_items_ajx'));
        //for woocommerce
        add_action('woocommerce_before_shop_loop', array(__CLASS__, 'woocommerce_before_shop_loop'), 8);
        add_action('woocommerce_after_shop_loop', array(__CLASS__, 'woocommerce_after_shop_loop'), 22);
        //***
        add_action('wp_ajax_mdf_search_button_get_content', array(__CLASS__, 'mdf_search_button_get_content'));
        add_action('wp_ajax_nopriv_mdf_search_button_get_content', array(__CLASS__, 'mdf_search_button_get_content'));
        add_action('wp_ajax_mdf_search_panel', array(__CLASS__, 'mdf_search_panel'));
        add_action('wp_ajax_nopriv_mdf_search_panel', array(__CLASS__, 'mdf_search_panel'));
    }

    public static function admin_init() {
        add_meta_box("mdf_shortcode_options", esc_html__("MDTF Shortcode Options", 'meta-data-filter'), array(__CLASS__, 'draw_options_meta_box'), self::$slug_shortcodes, "normal", "high");
        add_meta_box("mdf_shortcode_shortcode", esc_html__("Shortcode", 'meta-data-filter'), array(__CLASS__, 'draw_shortcode_meta_box'), self::$slug_shortcodes, "side", "high");
    }

    public static function draw_shortcode_meta_box() {
        global $post;
        echo '<b>' . esc_html__("Search form shortcode", 'meta-data-filter') . '</b>';
        ?>
        <br />
        <i>[mdf_search_form id="<?php echo esc_html($post->ID) ?>"]</i><br /><br />
        <?php
        echo '<b>' . esc_html__("<b>Button shortcode</b>", 'meta-data-filter') . '</b>';
        echo '<br /><i>';
		echo esc_html('[mdf_search_button id="' . esc_html($post->ID) . '" title="' . esc_html($post->post_title) . '" popup_title="' . $post->post_title . '" popup_width=800]');
		echo '</i>';
    }

    public static function show_edit_columns($columns) {
        $columns = array(
            "cb" => '<input type="checkbox" />',
            "title" => esc_html__("Title", 'meta-data-filter'),
            "shortcode" => esc_html__("Shortcode", 'meta-data-filter'),
            "button_shortcode" => esc_html__("Button Shortcode", 'meta-data-filter'),
            "date" => esc_html__("Date", 'meta-data-filter'),
        );

        return $columns;
    }

    public static function show_edit_columns_content($column) {
        global $post;

        switch ($column) {
            case "shortcode":
                echo esc_html('[mdf_search_form id="' . $post->ID . '"]');
                break;
            case "button_shortcode":
                echo esc_html('[mdf_search_button id="' . $post->ID . '" title="' . $post->post_title . '" popup_title="' . $post->post_title . '"]');
                break;
        }
    }

    public static function get_shortcode_options($post_id) {
        $shortcode_options = (array) get_post_meta($post_id, 'shortcode_options', true);

        if (!isset($shortcode_options['shortcode_cat']) OR empty($shortcode_options['shortcode_cat'])) {
            $shortcode_options['shortcode_cat'] = -1;
        }

        if (!isset($shortcode_options['options']['post_type']) OR empty($shortcode_options['options']['post_type'])) {
            $shortcode_options['options']['post_type'] = 'post';
        }

        return $shortcode_options;
    }

    public static function draw_options_meta_box($post) {
        $data = array();
        $data['post_id'] = $post->ID;
        $data['mdf_terms'] = get_terms(self::$slug_cat, array(
            'hide_empty' => FALSE
        ));
        //+++
        $data['shortcode_options'] = self::get_shortcode_options($post->ID);
        $html_items = (array) get_post_meta($post->ID, 'html_items', true);
        $data['html_items'] = $html_items;
        self::render_html_e(self::get_application_path() . 'views/shortcode/options_meta_box.php', $data);
    }

    public static function post_inits() {

        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('mdf_shortcode', self::get_application_uri() . 'js/shortcode.js', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable'));
        wp_enqueue_script('mdf_popup', self::get_application_uri() . 'js/pn_popup/pn_advanced_wp_popup.js', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable'));
        wp_enqueue_style('mdf_popup', self::get_application_uri() . 'js/pn_popup/styles.css');
    }

    public static function mce_buttons($buttons) {
        return $buttons;
    }

    public static function mce_add_rich_plugins($plugin_array) {
        return $plugin_array;
    }

//basic shortcode to show filtered results
    public static function do_meta_data_filter_shortcode() {
        $data = array();
        self::render_html_e(self::get_application_path() . 'views/shortcode/meta_data_filter.php', $data);
    }

    //[mdf_search_form id="742" slideout=1 location="right" action="click" onloadslideout=1]
    public static function do_mdf_search_form($args) {
        if (isset($args['id'])) {
            $data = array();
            $id = (int) $args['id'];
            $data['shortcode_id'] = $id;
            $post = get_post($id);
            if (is_object($post) AND $post->post_status == 'publish' AND $post->post_type == self::$slug_shortcodes) {
                self::front_script_includer();
                //+++

                $data['shortcode_options'] = self::get_shortcode_options($id);
                $data['html_items'] = self::sort_html_items((array) get_post_meta($post->ID, 'html_items', true), self::get_html_items_by_cat($data['shortcode_options']['shortcode_cat']));
                if (!is_dir(self::get_application_path() . 'views/shortcode/skins/' . $data['shortcode_options']['options']['skin'])) {
                    $data['shortcode_options']['options']['skin'] = 'default';
                }
                $data['page_meta_data_filter'] = self::get_page_mdf_data();
                wp_enqueue_style('mdf_skin_' . $data['shortcode_options']['options']['skin'], self::get_application_uri() . 'views/shortcode/skins/' . $data['shortcode_options']['options']['skin'] . '/styles.css');
                $res = self::render_html(self::get_application_path() . 'views/shortcode/do_mdf_search_form.php', $data);

                if (isset($args['slideout']) AND $args['slideout'] == 1) {
                    $action = isset($args['action']) ? $args['action'] : 'click';
                    $location = isset($args['location']) ? $args['location'] : 'right';
                    $speed = isset($args['speed']) ? $args['speed'] : 300;
                    $toppos = isset($args['toppos']) ? $args['toppos'] : 150;
                    $fixedposition = isset($args['fixedposition']) ? $args['fixedposition'] : 0;
                    $onloadslideout = isset($args['onloadslideout']) ? $args['onloadslideout'] : 0;
                    wp_enqueue_script('mdf-slideout', self::get_application_uri() . 'js/jquery.tabSlideOut.v1.3.js', array('jquery'));
                    wp_enqueue_script('mdf-slideout-init', self::get_application_uri() . 'js/slideout_init.js', array('jquery'));
                    $res = '<div class="mdf-slide-out-div" data-action="' . $action . '" data-location="' . $location . '" data-speed="' . $speed . '" data-toppos="' . $toppos . '" data-fixedposition="' . $fixedposition . '" data-onloadslideout="' . $onloadslideout . '"><a class="mdf-handle" href="#">Content</a>' . $res . '</div>';
                }

                return $res;
            } else {
                return esc_html__('This shortcode does not exists!', 'meta-data-filter');
            }
        }
    }

//ajax
    public static function draw_shortcode_html_items_ajx() {
        wp_die(self::draw_shortcode_html_items(array('shortcode_cat' => (int) $_REQUEST['term_id'])));
    }

    //admin
    public static function draw_shortcode_html_items($shortcode_options/* if new */, $html_items = array()) {
        $data = array();
        if (!isset($shortcode_options['options'])) {//if new
            $shortcode_options['options'] = array();
        }
        if (!isset($shortcode_options['options']['post_type'])) {//if new
            $shortcode_options['options']['post_type'] = 'post';
        }
        $data['shortcode_options'] = $shortcode_options;
        $html_items_by_cat = self::get_html_items_by_cat($shortcode_options['shortcode_cat']);

        //lets sort html_items as in shortcode panel
        $data['data'] = self::sort_html_items($html_items, $html_items_by_cat);

        return self::render_html(self::get_application_path() . 'views/shortcode/options_meta_box_html_items.php', $data);
    }

    //lets sort html_items as in shortcode panel
    private static function sort_html_items($html_items, $html_items_by_cat) {
        $html_items_by_cat_sorted = array();
        if (!empty($html_items)) {
            //sorting blocks
            foreach ($html_items as $filter_id => $meta_keys) {
                if (key_exists($filter_id, $html_items_by_cat)) {
                    $html_items_by_cat_sorted[$filter_id] = $html_items_by_cat[$filter_id];
                }
            }
            //sorting blocks items
            foreach ($html_items as $filter_id => $meta_keys) {
                if (!empty($meta_keys) AND is_array($meta_keys)) {
                    $tmp = array();
                    foreach ($meta_keys as $meta_key => $empty_val) {
                        if (isset($html_items_by_cat_sorted[$filter_id]['html_items'][$meta_key])) {
                            $tmp[$meta_key] = $html_items_by_cat_sorted[$filter_id]['html_items'][$meta_key];
                        }
                    }
                    $html_items_by_cat_sorted[$filter_id]['html_items'] = $tmp;
                }
            }
        } else {
            $html_items_by_cat_sorted = $html_items_by_cat;
        }

        return $html_items_by_cat_sorted;
    }

    public static function save_post() {
        if (!empty($_POST)) {
            global $post;
            if (is_object($post)) {
                if ($post->post_type == self::$slug_shortcodes) {
                    if (isset($_POST['shortcode_options'])) {
						$shortcode_options = self::sanitize_array_r($_POST['shortcode_options']);
						$html_items = self::sanitize_array_r($_POST['html_items']);
                        update_post_meta($post->ID, 'shortcode_options', $shortcode_options);
                        update_post_meta($post->ID, 'html_items', $html_items); //for sorting in shortcode
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
			if ($key == 'show_items_count_text') {
				$newArr[sanitize_key($key)] = wp_filter_post_kses($value);
			} else {
				$newArr[sanitize_key($key)] = ( is_array($value) ) ? self::sanitize_array_r($value) : sanitize_text_field($value);
			}
            
        }
        return $newArr;		
	}
    public static function get_sh_skins() {
        $skins = array();
        $src = self::get_application_path() . 'views/shortcode/skins/';
        $dir = opendir($src);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) AND ( $file != '..' )) {
                if (is_dir($src . $file)) {
                    $skins[] = $file;
                }
            }
        }
        closedir($dir);
        return $skins;
    }

    //**********************************
    public static function do_mdf_search_button($args) {
        if (self::isMobile() AND self::get_setting('hide_search_button_shortcode')) {
            return "";
        }
        if (isset($args['id']) AND is_numeric($args['id'])) {
            $rel_post = get_post($args['id']);
            if (!$rel_post) {
                return sprintf(esc_html__('Shortcode with ID %s doesn exists', 'meta-data-filter'), $args['id']);
            }
			
			$args = self::sanitize_array_r($args);
			
            //+++
            $shortcode_options = self::get_shortcode_options($args['id']);
            wp_enqueue_script('mdf_search_button', self::get_application_uri() . 'js/shortcodes/mdf_search_button.js', array('jquery'));
            wp_enqueue_script('mdf_popup', self::get_application_uri() . 'js/pn_popup/pn_advanced_wp_popup.js', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable'));
            wp_enqueue_style('mdf_popup', self::get_application_uri() . 'js/pn_popup/styles.css');
            wp_enqueue_style('mdf_skin_' . $shortcode_options['options']['skin'], self::get_application_uri() . 'views/shortcode/skins/' . $shortcode_options['options']['skin'] . '/styles.css');
            self::front_script_includer();
            return '<a href="#" class="mdf_search_button button" data-popup-width="' . ((isset($args['popup_width']) AND $args['popup_width'] > 0) ? esc_attr($args['popup_width']) : 800) . '" data-popup-title="' . (!empty($args['popup_title']) ? esc_attr($args['popup_title']) : 'Meta Data & Taxonomies Filter') . '" data-id="' . esc_attr($args['id']) . '">' . esc_html__($args['title']) . '</a>';
        }
    }

    //ajax
    public static function mdf_search_button_get_content() {
        echo '<div class="widget widget-meta-data-filter">';
        echo(do_shortcode('[mdf_search_form id="' . (int) $_REQUEST['shortcode_id'] . '"]'));
        echo '</div>';
        exit;
    }

    //shortcode
    public static function force_searching() {
        if (self::is_page_mdf_data()) {
            $tpl = self::get_setting('output_tpl');
            if ($tpl == 'search') {
                $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
                $_REQUEST['mdf_get_query_args_only'] = true;
                do_shortcode('[meta_data_filter_results]');
                $args =  self::sanitize_array_r($_REQUEST['meta_data_filter_args']);
                global $wp_query;
                $wp_query = new WP_Query($args);
                $_REQUEST['meta_data_filter_count'] = $wp_query->found_posts;
            }
        }
    }

    //shortcode
    public static function post_features_panel($args = array()) {
        $panel = "";
        global $post;
        $post_id = 0;
        if (is_object($post)) {
            $post_id = $post->ID;
        }

        if (isset($_REQUEST['current_post_id'])) {
            $post_id = (int)$_REQUEST['current_post_id'];
        }


        if ($post_id > 0) {
            static $title_collector = array();
            if (in_array($post_id, $title_collector)) {
                return "";
            }

            //+++
            $data = array();
            //just for any case, but is unlogical just now
            if (isset($args['meta_data_filter_cat'])) {
                $data['cat_id'] = $args['meta_data_filter_cat'];
            } else {
                $data['cat_id'] = get_post_meta($post_id, self::$slug_cat, TRUE);
            }

            $data['post_id'] = $post_id;
            //caching queries data while loop titles
            if (!isset(self::$cached_filter_posts[$data['cat_id']])) {
                self::$cached_filter_posts[$data['cat_id']] = MetaDataFilterPage::get_fiters_posts($data['cat_id']);
            }
            $data['filter_posts'] = self::$cached_filter_posts[$data['cat_id']];
            $data['page_meta_data_filter'] = $data['cat_id'] > 0 ? get_post_meta($post_id, 'page_meta_data_filter', true) : array();
            $title_collector[] = $post_id;
            $panel = self::render_html(self::get_application_path() . 'views/shortcode/post_features_panel.php', $data);
        }


        return $panel;
    }

    //shortcode
    public static function results_tax_navigation() {
        if (self::is_page_mdf_data() AND isset($_REQUEST['meta_data_filter_args']) AND isset($_REQUEST['meta_data_filter_args']['tax_query'])) {
            $taxes =  self::sanitize_array_r($_REQUEST['meta_data_filter_args']['tax_query']);
            unset($taxes['relation']);

            if (!empty($taxes)) {
                $tmp = array();
                foreach ($taxes as $tax) {
                    $tmp[$tax['taxonomy']] = $tax['terms'];
                }
                //+++
                $output_string = ""; // and prepare our output buffer
                foreach ($tmp as $tax_name => $terms) {

                    if ($tax_name == 'product_visibility') {
                        continue;
                    }
                    $output = MetaDataFilterHtml::get_term_label_by_name($tax_name) . ": "; // display the name followed by ":"

                    if (is_array($terms)) {
                        foreach ($terms as $term_id) { // now loop through all the slugs
                            $term = get_term_by('id', $term_id, $tax_name); // and based on that slug, get the term object
                            if ($term) {
                                $output .= '<a target="_blank" href="' . get_term_link($term, $tax_name) . '">' . $term->name . '</a>&nbsp;&nbsp;'; // and add the term's name to our output buffer followed by a comma (,)
                            }
                        }
                    } else {
                        $term = get_term_by('id', $terms, $tax_name); // and based on that slug, get the term object
                        $parents = self::get_taxonomy_parents_all($term->term_id, $tax_name);
                        $buffer = array();
                        foreach ($parents as $term) {
                            $buffer[] = '<a target="_blank" href="' . get_term_link($term, $tax_name) . '">' . $term->name . '</a>&nbsp;&nbsp;';
                        }
                        $buffer = array_reverse($buffer);
                        $buffer = implode(" -> ", $buffer);
                        $output .= $buffer;
                    }
                    $output_string .= rtrim($output, ", ") . " + ";  // when we're finished with terms, remove the last comma
                }

                return rtrim($output_string, " + "); // when we're finished with taxonomies, remove the last '+'
            }
        }
    }

    //+++
    //shortcode
    public static function results_by_ajax($atts, $content = null) {
        extract(shortcode_atts(array(
            'shortcode' => 'products',
            'load_more' => 0,
            'load_more_text' => esc_html__("Load more", 'meta-data-filter'),
            'columns' => '4',
            'animate' => 0,
            'animate_target' => '#mdf_results_by_ajax'//body
                        ), $atts));


        $class = "mdf_standard_paginate";
        if ($load_more) {
            $class = "mdf_load_more";
        }

        return "<div id='mdf_results_by_ajax' class='{$class}' data-animate='{$animate}' data-load_text='{$load_more_text}' data-animate-target='{$animate_target}' data-shortcode='{$shortcode}'>" . do_shortcode("[$shortcode]") . "</div>";
    }

    public static function woocommerce_before_shop_loop() {
        //for ajax output
        if (self::get_setting('try_make_shop_page_ajaxed')) {
            echo '<div id="mdf_results_by_ajax" data-shortcode="mdf_products">';
        }
    }

    public static function woocommerce_after_shop_loop() {
        //for ajax output
        if (self::get_setting('try_make_shop_page_ajaxed')) {
            echo '</div>';
        }
    }

    //woo overloaded
    //plugins\woocommerce\includes\class-wc-shortcodes.php#295
    //[mdf_results_by_ajax shortcode="mdf_products columns=2 per_page=6" animate=1 animate_target=body]
    public static function mdf_products($atts) {

        extract(shortcode_atts(array(
            'columns' => apply_filters('loop_shop_columns', 4),
            'orderby' => self::$default_order_by,
            'order' => self::$default_order,
            'page' => 1,
            'per_page' => self::get_setting('results_per_page'),
            'pagination' => 'b',
            'meta_data_filter_cat' => 0,
            'panel_id' => 0,
            'taxonomies' => ""
                        ), $atts));

        //*** compatibility with WOOCS
        if (class_exists('WOOCS')) {
            $_REQUEST['action'] = 'mdf_ajax_request';
        }

        $meta_query = array('relation' => 'AND');

        //for out stock products
        if (self::get_setting('exclude_out_stock_products')) {
            $buffer = array(
                'key' => '_stock_status',
                'value' => array('instock'),
                'compare' => 'IN'
            );
            $meta_query[] = $buffer;
        }

        if ($meta_data_filter_cat > 0) {
            $buffer = array(
                'key' => 'meta_data_filter_cat',
                'value' => $meta_data_filter_cat,
                'compare' => '='
            );
            $meta_query[] = $buffer;
        }

        //***
        //*** fix for reset data in ajax mode 16-01-2015
        if (isset($_REQUEST['mdf_current_term_id']) AND $_REQUEST['mdf_current_term_id'] > 0 AND ( defined('DOING_AJAX') && DOING_AJAX)) {
            if (!empty($_REQUEST['mdf_current_tax'])) {
                $taxonomies = sanitize_text_field($_REQUEST['mdf_current_tax']) . '+' . sanitize_text_field($_REQUEST['mdf_current_term_id']);
            }
        }
        if (!empty($taxonomies)) {
            $taxonomies = MetaDataFilter::add_additional_taxonomies($taxonomies, false, '+');
        } else {
            $taxonomies = array();
        }
        //***

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts' => self::get_setting('ignore_sticky_posts'),
            'posts_per_page' => $per_page,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => $meta_query,
            'tax_query' => $taxonomies
        );
        //woo3.3 //product visibility
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            $keys = array('exclude-from-search', 'exclude-from-catalog');
            if (self::get_setting('exclude_out_stock_products')) {
                $keys[] = 'outofstock';
            }
            $arr_ads = wc_get_product_visibility_term_ids();
            $product_not_in = array();
            foreach ($keys as $key) {
                if (isset($arr_ads[$key]) OR!empty($arr_ads[$key])) {
                    $product_not_in[] = $arr_ads[$key];
                }
            }
            if (!empty($product_not_in)) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'term_taxonomy_id',
                    'terms' => $product_not_in,
                    'operator' => 'NOT IN',
                );
            }

            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            );
        }

        //fix - rewrite if under searching        
        $args = MetaDataFilter::rewrite_search_query_args($args);
        $order_by_array = self::$allowed_order_by;
        //  But this completely surprised the sorting of posts according to the specified parameters in the shortcode    
        $args = self::mdf_check_sort_attr_shortcode($args, $order_by_array, $orderby, $order);
        $orderby = $args['orderby'];
        $order = $args['order'];
        if (in_array($orderby, $order_by_array)) {
            unset($args['meta_key']);
        }
        //+++
        $pp = $page;
        if (get_query_var('page')) {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged')) {
            $pp = get_query_var('paged');
        }

        if ($pp > 1) {
            $args['paged'] = $pp;
        } else {
            $args['paged'] = (isset($_REQUEST['content_redraw_page']) ? sanitize_text_field($_REQUEST['content_redraw_page']) : ((get_query_var('page')) ? get_query_var('page') : $page));
        }
        //+++
        $_REQUEST['content_redraw_page'] = $args['paged'];
        $args['posts_per_page'] = $per_page;
        if (!empty($taxonomies)) {
            $args['tax_query'] = array_merge($args['tax_query'], $taxonomies);
        }


        //*** fix for ordering by meta key
        if (!in_array($orderby, $order_by_array)) {
            $args['orderby'] = 'meta_value_num meta_value';
            if (!isset($args['meta_key'])) {
                if (!defined('DOING_AJAX')) {
                    $args['meta_key'] = $orderby;
                } else {
                    $args['meta_key'] = isset($_REQUEST['order_by']) ? sanitize_text_field($_REQUEST['order_by']) : $orderby;
                }
            }
        }
        //***


        $wr = apply_filters('woocommerce_shortcode_products_query', $args, $atts, "");

        $products = new WP_Query($wr);
        $_REQUEST['meta_data_filter_found_posts'] = $products->found_posts;
        $_REQUEST['mdf_panel_id'] = $panel_id;
        //***
        ob_start();
        global $woocommerce_loop;
        $woocommerce_loop['columns'] = $columns;
        $woocommerce_loop['loop'] = 0;
        //woo3.3
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            wc_set_loop_prop('is_paginated', true);
            wc_set_loop_prop('total_pages', $products->max_num_pages);
            wc_set_loop_prop('current_page', (int) max(1, $products->get('paged', 1)));
            wc_set_loop_prop('per_page', (int) $products->get('posts_per_page'));
            wc_set_loop_prop('total', (int) $products->found_posts);
            wc_set_loop_prop('columns', $columns);
        }

        if ($products->have_posts()) :
            add_filter('post_class', array(__CLASS__, 'woo_post_class'));
            ?>

            <?php do_action('woocommerce_before_shop_loop'); ?>

            <?php //woocommerce_product_loop_start();       ?>

            <?php
            ob_start();
            wc_get_template('loop/loop-start.php');
            echo ob_get_clean();
            ?>
            <?php while ($products->have_posts()) : $products->the_post(); ?>

                <?php wc_get_template_part('content', 'product'); ?>

            <?php endwhile; // end of the loop.  ?>
            <?php
            ob_start();
            wc_get_template('loop/loop-end.php');
            echo ob_get_clean();
            ?>

            <?php //woocommerce_product_loop_end();       ?>

            <?php
        else:
            esc_html_e('No products found', 'meta-data-filter');
        endif;

        wp_reset_postdata();
        //woo3.3
        if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
            wc_reset_loop();
        }

        //***
        $_REQUEST['mdtf_in_shortcode'] = 1;
        switch ($pagination) {
            case 'b':
                $result = '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>' . mdtf_pagination($products);
                break;
            case 't':
                $result = mdtf_pagination($products) . '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
                break;
            case 'tb':
                $result = mdtf_pagination($products) . '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>' . mdtf_pagination($products);
                break;

            default:
                break;
        }



        return $result;
    }

    //[mdf_results_by_ajax shortcode="mdf_custom template=woocommerce/layout1 post_type=product per_page=6 pagination=tb" animate=1 animate_target=body]
    public static function mdf_custom($atts) {
        extract(shortcode_atts(array(
            'orderby' => self::$default_order_by,
            'order' => self::$default_order,
            'page' => 1,
            'template' => '',
            'post_type' => 'post',
            'per_page' => self::get_setting('results_per_page'),
            'pagination' => 'b',
            'meta_data_filter_cat' => 0,
            'taxonomies' => '',
            'panel_id' => 0,
            'essential' => 0,
            'custom_id' => 0//for recognizing any shortcode personally
                        ), $atts));

        if (empty($template)) {
            wp_die(esc_html__('Please set template option in shortcode!', 'meta-data-filter'));
        }

        $meta_query = array();
        if ($meta_data_filter_cat > 0) {
            $buffer = array(
                'key' => 'meta_data_filter_cat',
                'value' => $meta_data_filter_cat,
                'compare' => '='
            );
            $meta_query[] = $buffer;
        }
        //***
        //*** fix for reset data in ajax mode 16-01-2015
        if (isset($_REQUEST['mdf_current_term_id']) AND $_REQUEST['mdf_current_term_id'] > 0 AND ( defined('DOING_AJAX') && DOING_AJAX)) {
            if (!empty($_REQUEST['mdf_current_tax'])) {
                $taxonomies = sanitize_text_field($_REQUEST['mdf_current_tax']) . '+' . sanitize_text_field($_REQUEST['mdf_current_term_id']);
            }
        }
        if (!empty($taxonomies)) {
            $taxonomies = MetaDataFilter::add_additional_taxonomies($taxonomies, false, '+');
        } else {
            $taxonomies = array();
        }

        //***
        $args = array(
            'post_type' => $post_type,
            'post_status' => array('publish'),
            'ignore_sticky_posts' => self::get_setting('ignore_sticky_posts'),
            'posts_per_page' => $per_page,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => $meta_query,
            'tax_query' => $taxonomies
        );

        //fix - rewrite if under searching
        $args = MetaDataFilter::rewrite_search_query_args($args);
        $order_by_array = self::$allowed_order_by;
        //  But this completely surprised the sorting of posts according to the specified parameters in the shortcode    
        $args = self::mdf_check_sort_attr_shortcode($args, $order_by_array, $orderby, $order);
        $orderby = $args['orderby'];
        $order = $args['order'];

        if (in_array($orderby, $order_by_array)) {
            unset($args['meta_key']);
        }
        //+++
        $pp = $page;
        if (get_query_var('page')) {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged')) {
            $pp = get_query_var('paged');
        }

        if ($pp > 1) {
            $args['paged'] = $pp;
        } else {
            $args['paged'] = (isset($_REQUEST['content_redraw_page']) ? sanitize_text_field($_REQUEST['content_redraw_page']) : ((get_query_var('page')) ? get_query_var('page') : $page));
        }
        //+++
        $_REQUEST['content_redraw_page'] = $args['paged'];
        $args['posts_per_page'] = $per_page;
        if (!empty($taxonomies)) {
            $args['tax_query'] = array_merge($args['tax_query'], $taxonomies);
        }
        //*** fix for ordering by meta key
        if (!in_array($orderby, $order_by_array)) {
            $args['orderby'] = 'meta_value_num meta_value';
            if (!isset($args['meta_key'])) {
                if (!defined('DOING_AJAX')) {
                    $args['meta_key'] = $orderby;
                } else {
                    $args['meta_key'] = isset($_REQUEST['order_by']) ? sanitize_text_field($_REQUEST['order_by']) : $orderby;
                }
            }
        }
        //*** 
        $_REQUEST['mdf_panel_id'] = $panel_id;
        $GLOBALS['mdf_args'] = $args;
        $GLOBALS['essential'] = $essential;
        $args = apply_filters('mdf_custom_shortcode_query_args', $args, $custom_id);
        $mdf_loop = new WP_Query($args);
        $GLOBALS['mdf_loop'] = $mdf_loop;
        $_REQUEST['meta_data_filter_found_posts'] = $mdf_loop->found_posts;
        //***
        //for wrong page numbers
        if (!$mdf_loop->found_posts) {
            $args['paged'] = 1;
            $_REQUEST['content_redraw_page'] = $args['paged'];
            $args['posts_per_page'] = $per_page;
            $mdf_loop = new WP_Query($args);
            $GLOBALS['mdf_loop'] = $mdf_loop;
            $_REQUEST['meta_data_filter_found_posts'] = $mdf_loop->found_posts;
        }

        //+++
        ob_start();
        get_template_part('mdf_templates/' . $template . '/index');
        $out = ob_get_clean();
        wp_reset_postdata();

        //***
        $_REQUEST['mdtf_in_shortcode'] = 1;

        if ($mdf_loop->found_posts > 0) {
            switch ($pagination) {
                case 'b':
                    $result = $out . '<br />' . mdtf_pagination($mdf_loop);
                    break;
                case 't':
                    $result = mdtf_pagination($mdf_loop) . $out;
                    break;
                case 'tb':
                    $result = mdtf_pagination($mdf_loop) . $out . '<br />' . mdtf_pagination($mdf_loop);
                    break;

                default:
                    break;
            }
        } else {
            $result = esc_html__('No items found', 'meta-data-filter');
        }

        return $result;
    }

    //for shortcode mdf_products
    public static function woo_post_class($classes) {
        global $post;
        $classes[] = 'product';
        $classes[] = 'type-product';
        $classes[] = 'status-publish';
        $classes[] = 'has-post-thumbnail';
        $classes[] = 'post-' . $post->ID;
        return $classes;
    }

    //+++

    private static function get_taxonomy_parents_all($term_id, $taxonomy) {
        $parent = get_term_by('id', $term_id, $taxonomy);
        $res = array();
        $res[] = $parent;
        while ($parent->parent != 0) {
            $parent = get_term_by('id', $parent->parent, $taxonomy);
            $res[] = $parent;
        }
        return $res;
    }

    //shortcode
    //[mdf_value post_id=5 key='medafi_price' reflection=1]
    public static function mdf_value($atts) {
        if (isset($atts['key'])) {
            global $post;
            $post_id = 0;
            if (is_single()) {
                $post_id = $post->ID;
            }

            if (isset($atts['post_id']) AND $atts['post_id'] > 0) {
                $post_id = $atts['post_id'];
            }

            $look_for_reflection = 0;

            if (isset($atts['reflection']) AND $atts['reflection'] > 0) {
                $look_for_reflection = 1;
            }

            if ($post_id > 0) {
                return self::get_field($atts['key'], $post_id, $look_for_reflection);
            }
        }

        return "";
    }

//[mdf_range_select meta_key='medafi_23jz2iu'  min=10  max=100 step=1 ]
    public static function range_select($atts) {
        $data = shortcode_atts(array(
            'meta_key' => '',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'cur_max' => 0,
            'cur_min' => 0,
            'title' => '',
            'postfix' => '',
            'prefix' => '',
                ), $atts);

        self::render_html_e(self::get_application_path() . 'views/shortcode/range_select.php', $data);
    }

    public static function mdf_select_title($atts) {
        if (isset($atts['post_id']) AND isset($atts['meta_key'])) {
            return self::get_val_as_select_title($atts['post_id'], $atts['meta_key']);
        }

        return esc_html__("shortcode data is not full: post_id, meta_key", 'meta-data-filter');
    }

    public static function mdf_check_sort_attr_shortcode($arg, $order_by_array, $orderby = 'date', $order = 'DESC') {
        if (!is_array($arg))
            return $arg;
        if (isset($_REQUEST['order_by'])) {
            if (in_array($_REQUEST['order_by'], $order_by_array)) {
                $arg['orderby'] = sanitize_text_field($_REQUEST['order_by']);
            } else {
                $arg['meta_key'] = sanitize_text_field($_REQUEST['order_by']);
                $arg['orderby'] = 'meta_value_num meta_value';
            }
        } else {
            if (in_array($orderby, $order_by_array)) {
                $arg['orderby'] = $orderby;
            } else {
                $arg['meta_key'] = $orderby;
                $arg['orderby'] = 'meta_value_num meta_value';
            }
        }
        if (isset($_REQUEST['order'])) {
            $arg['order'] = sanitize_text_field($_REQUEST['order']);
        } else {
            $arg['order'] = $order;
        }

        return $arg;
    }

    public static function show_search_panel() {
        ?>
        <div class="mdf_searh_panel"><ul class="container_serch_panel "></ul></div>
        <?php
    }

    public static function mdf_search_panel() {
        if (isset($_POST['mdf_search_terms'])) {

            $data = array();
			$mdf_search_terms = sanitize_text_field($_POST['mdf_search_terms']);
            $data['filter_data'] = json_decode(base64_decode($mdf_search_terms), true);

            $content = self::render_html(self::get_application_path() . 'views/shortcode/search_panel_terms.php', $data);

            die($content);
        }
    }


}
