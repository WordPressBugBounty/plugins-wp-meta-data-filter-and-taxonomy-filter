<?php
/*
  Plugin Name: MDTF - Meta Data & Taxonomies Filter
  Plugin URI: https://wp-filter.com/
  Description: Powerful and Flexible Filter Tools. Let your clients find all things they are want with the ease on your site.
  Requires at least: WP 4.1.0
  Tested up to: WP 6.6
  Author: realmag777
  Author URI: https://pluginus.net/
  Version: 1.3.3.5
  Tags: ajax filter, custom fields filter, ecommerce filter, filter, filter for posts, posts filter, wordpress filter, jigoshop filter, taxonomies filter, meta filter, products filter, search, woocommerce, taxonomies filter widget, woocommerce filter  Text Domain: meta-data-filter
  Text Domain: meta-data-filter
  Domain Path: /languages
  Forum URI: https://pluginus.net/support/forum/mdtf-wordpress-meta-data-taxonomies-filter/
  WC requires at least: 2.6.0
  WC tested up to: 9.3
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

define('MDTF_PLUGIN_NAME', plugin_basename(__FILE__));

//+++
require plugin_dir_path(__FILE__) . 'core.php';
require plugin_dir_path(__FILE__) . 'classes/html.php';
require plugin_dir_path(__FILE__) . 'classes/page.php';
require plugin_dir_path(__FILE__) . 'classes/shortcodes.php';
require plugin_dir_path(__FILE__) . 'classes/widgets.php';
//extensions
require plugin_dir_path(__FILE__) . 'ext/helper.php';
require plugin_dir_path(__FILE__) . 'ext/sort_panel.php';
require plugin_dir_path(__FILE__) . 'ext/mdtf-pagination/tw-pagination.php';
require plugin_dir_path(__FILE__) . 'ext/const_links.php';
require plugin_dir_path(__FILE__) . 'ext/gmap.php';
require plugin_dir_path(__FILE__) . 'ext/mdf_posts_messenger/mdf_posts_messenger.php';
require plugin_dir_path(__FILE__) . 'ext/mdf_stat/index.php';

//30-09-2024
class MetaDataFilter extends MetaDataFilterCore {

    const WIDGET_TAXONOMIES_ONLY = -1;

    public static $is_inited = true;
    public static $is_free = true; //just flag for notices, no funcs

    public static function init() {

        if (!self::is_should_init()) {
            return;
        }

        //***

        parent::init();
        load_plugin_textdomain('meta-data-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');

        //***

        $args = array(
            'labels' => array(
                'name' => esc_html__('MDTF Filters', 'meta-data-filter'),
                'singular_name' => esc_html__('Filters sections', 'meta-data-filter'),
                'add_new' => esc_html__('Add New Filter Section', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Filter Section', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Filter Section', 'meta-data-filter'),
                'new_item' => esc_html__('New Filter Section', 'meta-data-filter'),
                'view_item' => esc_html__('View Filter Section', 'meta-data-filter'),
                'search_items' => esc_html__('Search Filter sections', 'meta-data-filter'),
                'not_found' => esc_html__('No Filter sections found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Filter sections found in Trash', 'meta-data-filter'),
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
            'supports' => array('title', 'excerpt', 'tags'),
            'rewrite' => array('slug' => self::$slug),
            'show_in_admin_bar' => false,
            'menu_icon' => '',
            'taxonomies' => array(self::$slug_cat) // this is IMPORTANT
        );

        register_taxonomy(self::$slug_cat, array(self::$slug), array(
            "labels" => array(
                'name' => esc_html__('MDTF Categories', 'meta-data-filter'),
                'singular_name' => esc_html__('MDTF Categories', 'meta-data-filter'),
                'add_new' => esc_html__('Add New', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Category', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Categories', 'meta-data-filter'),
                'new_item' => esc_html__('New Category', 'meta-data-filter'),
                'view_item' => esc_html__('View Category', 'meta-data-filter'),
                'search_items' => esc_html__('Search Categories', 'meta-data-filter'),
                'not_found' => esc_html__('No Categories found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Categories found in Trash', 'meta-data-filter'),
                'parent_item_colon' => ''
            ),
            "singular_label" => esc_html__("Category", 'meta-data-filter'),
            'show_in_nav_menus' => false,
            'capabilities' => array('manage_terms'),
            'show_ui' => true,
            'term_group' => true,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::$slug_cat),
            'orderby' => 'name'
        ));

        register_post_type(self::$slug, $args);
//+++
        $settings = MetaDataFilterCore::get_settings();
        $stxt = " function mdf_js_after_ajax_done() {  "
                . (isset($settings['js_after_ajax_done']) ? stripcslashes(html_entity_decode($settings['js_after_ajax_done'])) : '') .
                "}";
        wp_add_inline_script('jquery', $stxt, 'after');
        wp_enqueue_script('jquery');

//***
        add_action("admin_init", array(__CLASS__, 'admin_init'), 1);
        add_action('admin_menu', array(__CLASS__, 'admin_menu'), 1);
        add_action('save_post', array(__CLASS__, 'save_post'), 1);
        add_action('edit_attachment', array(__CLASS__, 'save_post'), 1);
        if (!is_admin()) {
//!!! important to call front.js here, another way slider js for shortcodes doesn work
            wp_enqueue_script('meta_data_filter_widget', self::get_application_uri() . 'js/front.js', array('jquery', 'jquery-ui-core'));
            $data_array = array(
                'hide_empty_title' => apply_filters('mdf_hide_title_empty_section', 1),
            );
            wp_localize_script('meta_data_filter_widget', 'mdf_settings_data', $data_array);
        }
        add_action('wp_head', array(__CLASS__, 'wp_head'), 999);
        add_action('wp_footer', array(__CLASS__, 'wp_footer'), 9999);
        add_action('admin_head', array(__CLASS__, 'admin_head'), 1);
        if (is_admin() AND isset($_GET['page']) AND $_GET['page'] == 'mdf_settings') {
            //should be here to load loader first
            wp_enqueue_style('mdf_settings', self::get_application_uri() . 'css/mdf_settings.css');
            wp_enqueue_script('mdf_settings', self::get_application_uri() . 'js/mdf_settings.js', array('jquery', 'jquery-ui-core'));
        }
//***

        add_action('restrict_manage_posts', array(__CLASS__, "restrict_manage_posts"));
        add_filter('parse_query', array(__CLASS__, "parse_query"), 9999);
        add_action('pre_get_posts', array(__CLASS__, 'pre_get_posts'));
        add_filter("manage_" . self::$slug . "_posts_columns", array(__CLASS__, "show_edit_columns"));
        add_action("manage_" . self::$slug . "_posts_custom_column", array(__CLASS__, "show_edit_columns_content"));
        add_filter("manage_edit-" . self::$slug . "_sortable_columns", array(__CLASS__, "show_edit_sortable_columns"));
        add_action('load-edit.php', array(__CLASS__, "show_edit_sortable_columns_act"));

//***
        add_action('wp_ajax_meta_data_filter_set_sequence', array(__CLASS__, 'ajx_set_sequence'));
        add_action('wp_ajax_mdf_get_tax_options_in_widget', array(__CLASS__, 'get_tax_options_in_widget'));
        add_action('wp_ajax_mdf_change_meta_key', array(__CLASS__, 'change_meta_key'));
        add_action('wp_ajax_mdf_add_filter_item_to_widget', array(__CLASS__, 'add_filter_item_to_widget'));
        add_action('wp_ajax_mdf_cache_count_data_clear', array(__CLASS__, 'cache_count_data_clear'));
        add_action('wp_ajax_mdf_cache_terms_data_clear', array(__CLASS__, 'cache_terms_data_clear'));
//front ajax
        add_action('wp_ajax_mdf_draw_term_childs', array(__CLASS__, 'draw_term_childs_ajx'));
        add_action('wp_ajax_nopriv_mdf_draw_term_childs', array(__CLASS__, 'draw_term_childs_ajx'));
//+++
        add_filter('mdf_filter_taxonomies', array(__CLASS__, 'filter_mdf_filter_taxonomies'), 1);
        add_filter('mdf_filter_taxonomies2', array(__CLASS__, 'filter_mdf_filter_taxonomies2'), 1);
        $_REQUEST['meta_data_filter_args'] = array();
        add_filter('meta_data_filter_args', array(__CLASS__, 'filter_meta_data_filter_args'), 1);
        add_filter('plugin_action_links_' . MDTF_PLUGIN_NAME, array(__CLASS__, 'plugin_action_links'));
        add_filter('widget_text', 'do_shortcode');
        add_filter('the_title', array(__CLASS__, 'the_title'));
        add_filter('the_content', array(__CLASS__, 'the_content'));
//we need it when have filter-item by post_title OR content
        add_filter('posts_where', array('MDTF_HELPER', 'mdf_serch_by_author'));
        add_filter('posts_where', array('MDTF_HELPER', 'mdf_post_title_filter'));
        add_filter('posts_where', array('MDTF_HELPER', 'mdf_post_content_filter'));
        add_filter('posts_where', array('MDTF_HELPER', 'mdf_post_title_or_content_filter'));
        add_filter('posts_where', array('MDTF_HELPER', 'mdf_post_title_and_content_filter'));
        add_filter('posts_where', array('MDTF_HELPER', 'cast_decimal_precision'), 9999);

        add_action('body_class', array(__CLASS__, 'body_class'), 9999);

        //for essential-grid plugin compatibility
        if (class_exists('Essential_Grid')) {
            add_filter('essgrid_modify_posts', array(__CLASS__, 'essgrid_modify_posts'), 9999);
        }

        add_action('woocommerce_before_shop_loop', array(__CLASS__, 'woocommerce_before_shop_loop'));

        add_filter('cron_schedules', array(__CLASS__, 'cron_schedules'), 10, 1);
        //sheduling for dynamic recount
        if (isset($settings['cache_count_data_auto_clean']) AND $settings['cache_count_data_auto_clean']) {
            add_action('mdtf_cache_count_data_auto_clean', array(__CLASS__, 'cache_count_data_clear'));
            if (!wp_next_scheduled('mdtf_cache_count_data_auto_clean')) {
                wp_schedule_event(time(), $settings['cache_count_data_auto_clean'], 'mdtf_cache_count_data_auto_clean');
            }
        }

        //for terms
        if (isset($settings['cache_terms_data_auto_clean']) AND $settings['cache_terms_data_auto_clean']) {
            add_action('mdtf_cache_terms_data_auto_clean', array(__CLASS__, 'cache_terms_data_clear'));
            if (!wp_next_scheduled('mdtf_cache_terms_data_auto_clean')) {
                wp_schedule_event(time(), $settings['cache_terms_data_auto_clean'], 'mdtf_cache_terms_data_auto_clean');
            }
        }

        //+++

        MetaDataFilterHtml::init();
        MetaDataFilterPage::init();
        MetaDataFilterShortcodes::init();
        MDTF_SORT_PANEL::init();
        MDTF_CONST_LINKS::init();
        MDF_GMAP::init();
        if (class_exists('MDTF_UTILITIES')) {
            MDTF_UTILITIES::init();
        }
    }

    private static function is_should_init() {

        if (is_admin()) {
            return true;
        }

        //***

        $settings = self::get_settings();

        //stop loading the plugins filters and its another functionality on all pages of the site
        if (isset($settings['init_on_pages_only'])) {
            if (!empty($settings['init_on_pages_only'])) {
                $links = explode(PHP_EOL, trim($settings['init_on_pages_only']));
                $server_link = '';
                if (isset($_SERVER['SCRIPT_URI'])) {
                    $server_link = sanitize_text_field($_SERVER['SCRIPT_URI']);
                } else {

                    if (isset($_SERVER['REQUEST_URI'])) {
                        $server_link = site_url() . sanitize_text_field($_SERVER['REQUEST_URI']);
                    }
                }
                //***

                if (!empty($server_link)) {
                    $temp_links = array();
                    $temp_links = explode('?', $server_link);

                    if (!empty($temp_links) AND count($temp_links) > 1) {
                        $server_link = $temp_links[0];
                    }

                    $temp_links = explode('/page/', $server_link);
                    if (!empty($temp_links) AND count($temp_links) > 1) {
                        $server_link = $temp_links[0];
                    }

                    foreach ($links as $key => $url) {
                        $links[$key] = trim($url);
                    }

                    if (!in_array(trim($server_link), $links)) {
                        self::$is_inited = false;
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function cron_schedules($schedules) {
        //$schedules stores all recurrence schedules within WordPress
        for ($i = 2; $i <= 7; $i++) {
            $schedules['days' . $i] = array(
                'interval' => $i * DAY_IN_SECONDS,
                'display' => sprintf(esc_html__("each %s days", 'meta-data-filter'), $i)
            );
        }

        return (array) $schedules;
    }

    public static function body_class($classes) {
        if (self::is_page_mdf_data()) {
            $classes[] = 'mdf_search_is_going';
        }

        return $classes;
    }

    public static function draw_sort_by_filter() {
//for compatibility only
    }

    /**
     * Show action links on the plugin screen
     */
    public static function plugin_action_links($links) {
        return array_merge(array(
            '<a href="' . admin_url('edit.php?post_type=meta_data_filter&page=mdf_settings') . '">' . esc_html__('Settings', 'meta-data-filter') . '</a>',
            '<a target="_blank" href="' . esc_url('http://wp-filter.com/documentation/') . '">' . esc_html__('Documentation', 'meta-data-filter') . '</a>'
                ), $links);
    }

    public static function get_plugin_ver() {
        $data = get_plugin_data(__FILE__);
        return $data['Version'];
    }

    public static function filter_meta_data_filter_args($args) {
        return array_merge($args, self::sanitize_array_r($_REQUEST['meta_data_filter_args']));
    }

//action to hook taxonomies array in query for shortcode [meta_data_filter_results]
    public static function filter_mdf_filter_taxonomies($tax_array) {
        static $tax_query_array = array();

        if (empty($tax_query_array)) {
            $tax_query_array = $tax_array;
        } else {
            if (!empty($tax_array)) {
                foreach ($tax_array as $taxes) {
                    $tax_query_array[] = $taxes;
                }
            }
        }

        return $tax_query_array;
    }

//for recount
    public static function filter_mdf_filter_taxonomies2($tax_array) {
        if (isset($_REQUEST['MDF_ADDITIONAL_TAXONOMIES']) AND !empty($_REQUEST['MDF_ADDITIONAL_TAXONOMIES'])) {
            $additional_tax = self::sanitize_array_r($_REQUEST['MDF_ADDITIONAL_TAXONOMIES']);
            foreach ($additional_tax as $tax) {
                $tax_array[] = $tax;
            }
        }


        return $tax_array;
    }

    public static function admin_menu() {
        add_submenu_page('edit.php?post_type=' . self::$slug, esc_html__("MDTF Settings", 'meta-data-filter'), esc_html__("MDTF Settings", 'meta-data-filter'), 'manage_options', 'mdf_settings', array(__CLASS__, 'draw_settings_page'));
        if (class_exists('MDTF_UTILITIES')) {
            add_submenu_page('edit.php?post_type=' . self::$slug, esc_html__("MDTF Utilities", 'meta-data-filter'), esc_html__("MDTF Utilities", 'meta-data-filter'), 'manage_options', 'mdf_utilities', array(__CLASS__, 'draw_utilities_page'));
        }
    }

    public static function draw_utilities_page() {
        wp_enqueue_script('mdf_utilities', self::get_application_uri() . 'js/utilities.js', array('jquery'));
        self::render_html_e(self::get_application_path() . 'views/utilities.php');
    }

    public static function wp_head() {
        wp_enqueue_script('jquery');
        try {
            if (is_product_taxonomy()) {
                $_REQUEST['MDF_IS_WOO_CAT'] = TRUE;
                global $wp_query;
                $cat = $wp_query->get_queried_object();
                $additional_tax_query_array = array();
                $additional_tax_query_array[] = array(
                    'taxonomy' => $cat->taxonomy,
                    'field' => 'term_id',
                    'terms' => array($cat->term_id)
                );

                $_REQUEST['MDF_ADDITIONAL_TAXONOMIES'] = $additional_tax_query_array;
            }
        } catch (Exception $e) {
            
        }
        //***

        $settings = self::get_settings();
        ?>
        <style>
            /* DYNAMIC CSS STYLES DEPENDING OF SETTINGS */
            <?php if (isset($settings['overlay_skin']) AND $settings['overlay_skin'] != 'default'): ?>

                <?php if (isset($settings['plainoverlay_color']) AND !empty($settings['plainoverlay_color'])): ?>
                    .jQuery-plainOverlay-progress {
                        border-top: 12px solid <?php echo esc_attr(trim($settings['plainoverlay_color'])) ?> !important;
                    }
                <?php endif; ?>


                <?php if (isset($settings['overlay_skin_bg_img']) AND !empty($settings['overlay_skin_bg_img'])): ?>
                    .plainoverlay {
                        background-image: url('<?php echo esc_url_raw(trim($settings['overlay_skin_bg_img'])) ?>');
                    }
                <?php endif; ?>

            <?php endif; ?>

            <?php
            if (isset($settings['custom_css_code'])) {
                echo wp_kses_post(wp_unslash(stripcslashes($settings['custom_css_code'])));
            }
            ?>
        </style>
        <?php
        //***
        include self::get_application_path() . 'js/js_vars.php';
    }

    public static function woocommerce_before_shop_loop() {
        if (!defined('DOING_AJAX')) {
            if (is_product_taxonomy() OR is_shop()) {
                if (class_exists('MetaDataFilter') AND MetaDataFilter::is_page_mdf_data()) {
                    $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
                    $_REQUEST['mdf_get_query_args_only'] = true;
                    do_shortcode('[meta_data_filter_results]');
                    $args = self::sanitize_array_r($_REQUEST['meta_data_filter_args']);
                    global $wp_query;
                    $wp_query = new WP_Query($args);
                    // woo3.3
                    if (version_compare(WOOCOMMERCE_VERSION, '3.3', '>=')) {
                        wc_set_loop_prop('is_paginated', true);
                        wc_set_loop_prop('total_pages', $wp_query->max_num_pages);
                        wc_set_loop_prop('current_page', (int) max(1, $wp_query->get('paged', 1)));
                        wc_set_loop_prop('per_page', (int) $wp_query->get('posts_per_page'));
                        wc_set_loop_prop('total', (int) $wp_query->found_posts);
                        //wc_set_loop_prop( 'columns',$columns);
                    }
                    $_REQUEST['meta_data_filter_found_posts'] = $wp_query->found_posts;
                }
            }
        }
    }

    public static function mdf_shortcode_quick_js_injection() {
//not need, only for compatibility for old versions (<=1.1.3)
    }

    public static function wp_footer() {
        ?>
        <script>
            //DYNAMIC SCRIPT DEPENDING OF SETTINGS
        <?php
        $page_meta_data_filter = self::get_page_mdf_data();

        if (isset($page_meta_data_filter['mdf_widget_options']) AND $page_meta_data_filter['mdf_widget_options']['search_result_page'] != 'self'):
            ?>
            <?php if (isset($_REQUEST['meta_data_filter_found_posts']) AND $_REQUEST['meta_data_filter_found_posts'] != 0): ?>
                    var mdf_found_totally =<?php echo(isset($_REQUEST['meta_data_filter_found_posts']) ? intval($_REQUEST['meta_data_filter_found_posts']) : 0); ?>;
            <?php else: ?>
                    var mdf_found_totally =<?php echo(isset($_REQUEST['meta_data_filter_count']) ? intval($_REQUEST['meta_data_filter_count']) : 0); ?>;
            <?php endif; ?>
        <?php else: ?>
                var mdf_found_totally =<?php
            if (!isset($_REQUEST['meta_data_filter_found_posts'])) {
                $a = self::sanitize_array_r($_REQUEST['meta_data_filter_args']);
//WPML compatibility
                if (class_exists('SitePress')) {
                    $a['lang'] = ICL_LANGUAGE_CODE;
                }
                $query = new WP_QueryMDFCounter($a);
                echo (int) $query->found_posts;
            } else {
                echo (int) $_REQUEST['meta_data_filter_found_posts'];
            }
            ?>;
        <?php endif; ?>

        </script>
        <?php
    }

    public static function admin_head() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('meta_data_filter_admin_total', self::get_application_uri() . 'css/admin_total.css');
        include MetaDataFilterCore::get_application_path() . 'js/js_vars.php';
    }

    public static function the_title($title) {

        if (isset($_REQUEST['mdf_post_title_request']) OR isset($_REQUEST['mdf_post_title_or_content_request']) OR isset($_REQUEST['mdf_post_title_and_content_request'])) {

            if (isset($_REQUEST['mdf_post_title_or_content_request'])) {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_or_content_request']));
            } elseif (isset($_REQUEST['mdf_post_title_and_content_request'])) {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_and_content_request']));
            } else {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_request']));
            }



            $txt = str_replace('#', '', $data[2]);
            $title = preg_replace('#(' . $txt . ')#i', '<span class="mdf_highlighted_title">$1</span>', $title);
        }

        return $title;
    }

    public static function the_content($content) {

        if (isset($_REQUEST['mdf_post_content_request']) OR isset($_REQUEST['mdf_post_title_or_content_request']) OR isset($_REQUEST['mdf_post_title_and_content_request'])) {
            if (isset($_REQUEST['mdf_post_title_or_content_request'])) {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_or_content_request']));
            } elseif (isset($_REQUEST['mdf_post_title_and_content_request'])) {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_and_content_request']));
            } else {
                $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_content_request']));
            }


            $txt = $data[2];
            $content = preg_replace('/(' . $txt . ')/i', '<span class="mdf_highlighted_content">$1</span>', $content);
        }

        return $content;
    }

    public static function widgets_init() {
        if (self::is_should_init()) {
            register_widget('MetaDataFilter_Search');
            register_widget('MetaDataFilter_TaxSearch');
        }
    }

    public static function admin_init() {
        add_meta_box("meta_data_filter_options", esc_html__("Meta Data Filter Options", 'meta-data-filter'), array(__CLASS__, 'data_meta_box'), self::$slug, "normal", "high");
        add_meta_box("meta_data_filter_sequence", esc_html__("Filter sequence on front", 'meta-data-filter'), array(__CLASS__, 'data_meta_box_sequence'), self::$slug, "side", "high");
        add_meta_box("meta_data_filter_style", esc_html__("Meta Data Filter Style", 'meta-data-filter'), array(__CLASS__, 'data_meta_box_style'), self::$slug, "side", "high");
        MetaDataFilterPage::add_meta_box();
//***
        global $pagenow;

        if (!get_option('meta_data_filter_was_activated')) {

            $args = array(
                'ID' => 0,
                'post_status' => 'publish',
                'post_title' => esc_html__("MDTF Results Page", 'meta-data-filter'),
                'post_content' => '[meta_data_filter_results]',
                'post_type' => 'page'
            );

            $post_id = wp_insert_post($args);
            update_post_meta($post_id, '_wp_page_template', 'template-meta-data-filter.php');

//set settings
            $data = array(
                'search_url' => home_url(),
                'reset_link' => home_url(),
                'post_types' => array('post'),
                'output_tpl' => 'search',
                'tooltip_theme' => 'shadow',
                'tooltip_icon' => '',
                'tooltip_icon_w' => '',
                'tooltip_icon_h' => '',
                'tooltip_max_width' => 220,
                'ajax_searching' => 0,
                'marketing_short_links' => 1,
                'use_chosen_js_w' => 0,
                'use_chosen_js_s' => 0,
                'use_custom_scroll_bar' => 1,
                'use_custom_icheck' => 1,
                'results_per_page' => 0,
                'loading_text' => esc_html__("One Moment ...", 'meta-data-filter'),
                'hide_search_button_shortcode' => 0,
                'ignore_sticky_posts' => 1,
                'icheck_skin' => 'flat_aero',
                'try_make_shop_page_ajaxed' => 0,
                'show_tax_all_childs' => 0
            );
            update_option('meta_data_filter_settings', $data);

//***
            @copy(self::get_application_path() . 'views/to_theme/template-meta-data-filter.php', get_template_directory() . '/template-meta-data-filter.php');
            update_option('meta_data_filter_was_activated', 1);
        }
//}
//}
        add_action('admin_notices', array(__CLASS__, 'admin_notices'));
    }

    public static function admin_notices($notices = "") {
        $notices = "";
//watch for template file existing
        if (!file_exists(get_template_directory() . '/template-meta-data-filter.php')) {
            if (@!copy(self::get_application_path() . 'views/to_theme/template-meta-data-filter.php', get_template_directory() . '/template-meta-data-filter.php')) {
                $notices .= '<div class="error fade"><p>' . esc_html__("Meta Data & Taxonomies Filter plugin can't copy the template file to your current theme from wp-content/plugins/meta-data-filter/views/to_theme/template-meta-data-filter.php. Please upload this file using ftp to your current theme folder from: plugins/meta-data-filter/veiws/to_theme.", 'meta-data-filter') . '</p></div>';
            }
        }

        echo wp_kses_post(wp_unslash($notices));
    }

    public static function data_meta_box() {
        global $post;
        wp_enqueue_script('meta_data_filter_admin', self::get_application_uri() . 'js/admin.js', array('jquery'));
        wp_enqueue_style('meta_data_filter_admin', self::get_application_uri() . 'css/admin.css');
        self::render_html_e(self::get_application_path() . 'views/data_meta_box.php', array('html_items' => self::get_html_items($post->ID)));
    }

    public static function data_meta_box_sequence() {
        global $post;
        ?>
        <input type="text" placeholder="<?php esc_html_e("integer number", 'meta-data-filter') ?>" name="sequence" value="<?php echo (int) get_post_meta($post->ID, 'sequence', true) ?>" />
        <?php
    }

    public static function data_meta_box_style() {
        global $post;
        ?>
        <h4><?php esc_html_e("The section max-heigh", 'meta-data-filter') ?></h4>
        <i><?php esc_html_e("If you have a lot of elements in the filter section you can set max-height. Set 0 if you do not need it.", 'meta-data-filter') ?></i><br />
        <br />
        <h5 class="mdtf-m0"><?php esc_html_e("On front (widget)", 'meta-data-filter') ?>:</h5>
        <input type="text" name="widget_section_max_height" placeholder="px" value="<?php echo (int) get_post_meta($post->ID, 'widget_section_max_height', true) ?>" /><br />
        <br />
        <h5 class="mdtf-m0"><?php esc_html_e("On backend", 'meta-data-filter') ?>:</h5>
        <input type="text" name="backend_section_max_height" placeholder="px" value="<?php echo (int) get_post_meta($post->ID, 'backend_section_max_height', true) ?>" /><br />
        <br />
        <h4><?php esc_html_e("The section toggle", 'meta-data-filter') ?>:</h4>
        <?php
        $toggles = array(
            0 => esc_html__("No toogle", 'meta-data-filter'),
            1 => esc_html__("Opened", 'meta-data-filter'),
            2 => esc_html__("Closed", 'meta-data-filter')
        );
        $toggle = get_post_meta($post->ID, 'widget_section_toggle', true);
        ?>
        <select name="widget_section_toggle" class="postform">
            <?php foreach ($toggles as $key => $value) : ?>
                <option <?php echo selected($toggle, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
        </select><br />
        <h4><?php esc_html_e("Show as multiple checkbox select", 'meta-data-filter') ?>:</h4>
        <?php
        $choice = array(
            0 => esc_html__("No", 'meta-data-filter'),
            1 => esc_html__("Yes", 'meta-data-filter'),
        );
        $sel_emulator = get_post_meta($post->ID, 'widget_section_sel_emulator', true);
        ?>
        <select name="widget_section_sel_emulator" class="postform">
            <?php foreach ($choice as $key => $value) : ?>
                <option <?php echo selected($sel_emulator, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
        </select><br />
        <p class="description">
            <?php esc_html_e("Example", 'meta-data-filter') ?>: <a href="http://codepen.io/elmahdim/pen/hlmri" target="_blank">http://codepen.io/elmahdim/pen/hlmri</a><br />
            <?php esc_html_e("Uses ONLY for current section with checkboxes. Not for taxonomies ...", 'meta-data-filter') ?><br />
        </p>
        <?php
    }

    public static function save_post() {
        if (!empty($_POST)) {
            global $post;
            if (is_object($post)) {

                if ($post->post_type == self::$slug) {
//saving items of constructor
                    if (isset($_POST['html_item'])) {
                        update_post_meta($post->ID, 'html_items', self::sanitize_array_r($_POST['html_item']));
                    }
                    self::set_sequence($post->ID, self::escape($_POST['sequence']));
                    update_post_meta($post->ID, 'widget_section_max_height', self::escape($_POST['widget_section_max_height']));
                    update_post_meta($post->ID, 'backend_section_max_height', self::escape($_POST['backend_section_max_height']));
                    update_post_meta($post->ID, 'widget_section_toggle', self::escape($_POST['widget_section_toggle']));
                    update_post_meta($post->ID, 'widget_section_sel_emulator', self::escape($_POST['widget_section_sel_emulator']));
                } else {
//saving for posts/pages or custom post types


                    if (isset($_POST[self::$slug_cat])) {

                        global $wpdb;
                        if ($_POST[self::$slug_cat] == -1 OR $_POST[self::$slug_cat] != get_post_meta($post->ID, self::$slug_cat, true)) {
//clean old values, because otherwise search is bad
                            $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'medafi_%' AND post_id=$post->ID");
                        }
//***
                        if (isset($_POST['page_meta_data_filter'])) {
                            $page_meta_data_filter = self::sanitize_array_r($_POST['page_meta_data_filter']);
                            update_post_meta($post->ID, 'page_meta_data_filter', $page_meta_data_filter); //synhro saving
                            if (!empty($_POST['page_meta_data_filter']) AND is_array($page_meta_data_filter)) {
                                foreach ($_POST['page_meta_data_filter'] as $key => $value) {
                                    update_post_meta($post->ID, $key, $value); //this is for better searching
                                }
                            }
                        }
                        update_post_meta($post->ID, self::$slug_cat, self::escape($_POST[self::$slug_cat]));
                    }
                }
            }
        }
    }

//***********************************************************

    public static function show_edit_columns_content($column) {
        global $post;

        switch ($column) {
            case "items_count":
                echo(count(self::get_html_items($post->ID)));
                break;
            case self::$slug_cat:
                echo get_the_term_list($post->ID, self::$slug_cat, '', ',');
                break;
            case "sequence":
                wp_enqueue_style('meta_data_filter_admin', self::get_application_uri() . 'css/admin.css');
                wp_enqueue_script('meta_data_filter_admin', self::get_application_uri() . 'js/admin.js', array('jquery'));
                ?>
                <input type="text" pattern="[0-9]*" name="xxx" placeholder="<?php esc_html_e("digits only", 'meta-data-filter') ?>" data-nonce="<?php echo wp_create_nonce('nonce_sequence_' . $post->ID); ?>" value="<?php echo get_post_meta($post->ID, 'sequence', true) ?>" data-post-id="<?php echo esc_attr($post->ID) ?>" class="meta_data_filter_sequence" />
                <?php
                break;
        }
    }

    public static function show_edit_columns($columns) {
        $columns = array(
            "cb" => '<input type="checkbox" />',
            "title" => esc_html__("Title", 'meta-data-filter'),
            "items_count" => esc_html__("Html Items Count", 'meta-data-filter'),
            self::$slug_cat => esc_html__("Categories", 'meta-data-filter'),
            "sequence" => esc_html__("Filter sequence on front", 'meta-data-filter'),
        );

        return $columns;
    }

    public static function show_edit_sortable_columns($columns) {
        $columns['sequence'] = 'sequence';
        $columns[self::$slug_cat] = self::$slug_cat;
        return $columns;
    }

    public static function show_edit_sortable_columns_act() {
        add_filter('request', array(__CLASS__, 'sequence_col_sort_logic'));
        add_filter('request', array(__CLASS__, 'cat_col_sort_logic'));
    }

    public static function sequence_col_sort_logic($vars) {

        if (isset($vars['post_type']) AND self::$slug == $vars['post_type']) {
            if (isset($vars['orderby']) AND 'sequence' == $vars['orderby']) {
                $vars = array_merge($vars, array('meta_key' => 'sequence', 'orderby' => 'meta_value_num meta_value'));
            }
        }

        return $vars;
    }

    public static function cat_col_sort_logic($vars) {
        return $vars;
    }

    public static function restrict_manage_posts() {
        global $typenow;
        global $wp_query;
        if ($typenow == self::$slug) {
            $mdf_taxonomy = get_taxonomy(self::$slug_cat);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("Show All", 'meta-data-filter') . " " . $mdf_taxonomy->label,
                'taxonomy' => self::$slug_cat,
                'name' => self::$slug_cat,
                'orderby' => 'name',
                'selected' => isset($wp_query->query[self::$slug_cat]) ? $wp_query->query[self::$slug_cat] : null,
                'hierarchical' => true,
                'depth' => 3,
                'show_count' => true, // Show # listings in parens
                'hide_empty' => true,
            ));
        }
    }

    public static function parse_query($wp_query) {
        if (!is_admin()) {
//for result output
            if (!isset($_REQUEST['meta_data_filter_works'])) {
                if (self::is_page_mdf_data() AND $wp_query->is_main_query()) {
                    $mdf_data = self::get_page_mdf_data();
                    $output_tpl = "";
                    if (isset($mdf_data['mdf_widget_options']['search_result_tpl']) AND !empty($mdf_data['mdf_widget_options']['search_result_tpl'])) {
                        $output_tpl = $mdf_data['mdf_widget_options']['search_result_tpl'];
                    } else {
                        $settings = MetaDataFilter::get_settings();
                        if (empty($settings['output_tpl'])) {
                            $output_tpl = 'search';
                        } else {
                            $output_tpl = $settings['output_tpl'];
                        }
                    }

                    if ($output_tpl == 'search') {
                        $wp_query->set('post_type', self::escape($_GET['slg']));
                        if ($_GET['slg'] != 'post') {
                            $wp_query->is_post_type_archive = true;
                        }

                        $wp_query->is_tax = false;
                        $wp_query->is_tag = false;
                        $wp_query->is_home = false;
                        $wp_query->is_search = true;
                    }
                }
            }
        }

//***
//for sorting in admin
        if (is_admin()) {
            global $pagenow;
            $post_type = self::$slug; // change HERE
            $taxonomy = self::$slug_cat; // change HERE
            $q_vars = &$wp_query->query_vars;
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                $q_vars[$taxonomy] = $term->slug;
            }
        }
    }

    public static function pre_get_posts($query) {
        if (!is_admin()) {
//for result output
            if (!isset($_REQUEST['meta_data_filter_works'])) {
                if (self::is_page_mdf_data()) {
                    $mdf_data = self::get_page_mdf_data();
                    if (isset($mdf_data['mdf_widget_options']['search_result_tpl']) AND !empty($mdf_data['mdf_widget_options']['search_result_tpl'])) {
                        $output_tpl = $mdf_data['mdf_widget_options']['search_result_tpl'];
                    } else {
                        $settings = MetaDataFilter::get_settings();
                        if (empty($settings['output_tpl'])) {
                            $output_tpl = 'search';
                        } else {
                            $output_tpl = $settings['output_tpl'];
                        }
                    }

                    $prohibited_array = apply_filters('mdtf_prohibited_post_types', array('acf-post-type', 'acf-taxonomy', 'wpcf7_contact_form'), $query);

                    if ($output_tpl == 'search' && !in_array($query->query['post_type'], $prohibited_array)) {
                        $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
                        do_shortcode('[meta_data_filter_results]');
                    }
                }
            }
        }
    }

//+++

    public static function set_sequence($post_id, $sequence) {
        update_post_meta($post_id, 'sequence', (int) $sequence);
    }

    public static function ajx_set_sequence() {
        if (!current_user_can('manage_options')) {
            die('0');
        }
        if (!isset($_REQUEST['nonce_sequence']) || !wp_verify_nonce($_REQUEST['nonce_sequence'], 'nonce_sequence_' . (int) $_REQUEST['post_id'])) {
            die('0');
        }
        self::set_sequence((int) $_REQUEST['post_id'], (int) $_REQUEST['sequence']);
        exit;
    }

    public static function draw_settings_page() {
        wp_enqueue_script('jquery');
        wp_enqueue_script("jquery-ui-core");
        wp_enqueue_script("jquery-ui-tabs");
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('jquery-ui', self::get_application_uri() . 'css/jquery-ui.custom.css');
        wp_enqueue_style('meta_data_filter_admin', self::get_application_uri() . 'css/admin.css');
//***
        if (isset($_POST['meta_data_filter_settings_submit']) AND is_admin()) {
            check_admin_referer("update_mdtf" . MetaDataFilterCore::$slug);

            $service1_before = self::get_setting('marketing_short_links');

            $post_data = self::sanitize_post_data($_POST['meta_data_filter_settings']);
            update_option('meta_data_filter_settings', $post_data);
            $service1_after = self::get_setting('marketing_short_links');

//is state of mdf marketing were changed
            if ($service1_before != $service1_after) {
                ?>
                <script>
                    //DYNAMIC SCRIPT COMMAND
                    window.location = "<?php echo admin_url("edit.php?post_type=" . self::$slug . "&page=mdf_settings") ?>";
                </script>
                <?php
            }
        }
//***
        if (isset($_POST['meta_data_filter_assign_filter_id']) AND is_admin() AND !empty($_POST['mass_filter_id'])) {

            check_admin_referer("mdtf_assign" . MetaDataFilterCore::$slug);

            global $wpdb;
            $mass_filter_slug = self::escape($_POST['mass_filter_slug']);
            $posts = $wpdb->get_results("SELECT ID from {$wpdb->posts} WHERE post_type='{$mass_filter_slug}'");
            if (!empty($posts)) {
                $meta_key = 'meta_data_filter_cat';
                foreach ($posts as $post) {
                    update_post_meta($post->ID, $meta_key, self::escape($_POST['mass_filter_id']));
                }
            }
        }
//***
        $data = self::get_settings();
        self::render_html_e(self::get_application_path() . 'views/settings.php', $data);
    }

    public static function sanitize_post_data($data) {
        $urls = array("search_url", "reset_link", "overlay_skin_bg_img");
        $js = array("js_after_ajax_done");
        $text_area = array("text_email_messenger", "notes_for_customer_messenger", "init_on_pages_only", "custom_css_code");
        $html = array("previouspage", "nextpage", "before", "after");
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                self::sanitize_post_data($item);
                continue;
            }
            if (in_array($key, $urls)) {
                $data[$key] = esc_url_raw($item);
            } elseif (in_array($key, $js)) {
                $data[$key] = sanitize_textarea_field($item);
            } elseif (in_array($key, $text_area)) {
                $data[$key] = sanitize_textarea_field($item);
            } elseif (in_array($key, $html)) {
                $data[$key] = wp_filter_post_kses($item);
            } else {
                $data[$key] = sanitize_text_field($item);
            }
        }
        return $data;
    }

//for widget

    public static function add_filter_item_to_widget($val = '') {
        $res = MetaDataFilterPage::get_html_sliders_by_filter_cat((int) $_REQUEST['filter_cat']);
        ?>
        <?php if (!empty($res)): ?>
            <li><select class="widefat mdf_delete_filter_item_select" name="<?php echo esc_attr($_REQUEST['field_name']) ?>[]">
                    <?php foreach ($res as $key => $name) : ?>
                        <option value="<?php echo esc_attr($key) ?>" <?php if ($key == $val): ?>selected=""<?php endif; ?> class="level-0"><?php esc_html_e($name) ?></option>
                    <?php endforeach; ?>
                </select>&nbsp;<a href="#" class="button mdf_delete_filter_item">x</a></li>
        <?php endif; ?>
        <?php
        if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'mdf_add_filter_item_to_widget') {
            exit;
        }
        return;
    }

//ajax - for option of any taxonomy in the widget
    public static function get_tax_options_in_widget() {
        $terms_list = self::get_terms(sanitize_text_field($_REQUEST['tax_name']));
        if (!empty($terms_list)) {
            $data = array();
            $data['tax_name'] = sanitize_text_field($_REQUEST['tax_name']);
            $data['show_how'] = sanitize_text_field($_REQUEST['show_how']);
            $data['select_size'] = isset($_REQUEST['select_size']) ? sanitize_text_field($_REQUEST['select_size']) : 1;
            $data['show_child_terms'] = isset($_REQUEST['show_child_terms']) ? sanitize_text_field($_REQUEST['show_child_terms']) : 0;
            $data['terms_section_toggle'] = isset($_REQUEST['terms_section_toggle']) ? sanitize_text_field($_REQUEST['terms_section_toggle']) : 0;
            $data['tax_title'] = isset($_REQUEST['tax_title']) ? sanitize_text_field($_REQUEST['tax_title']) : 1;
            $data['checkbox_max_height'] = isset($_REQUEST['checkbox_max_height']) ? sanitize_text_field($_REQUEST['checkbox_max_height']) : 0;
            $data['hidden_term'] = explode(',', sanitize_text_field($_REQUEST['hidden_terms']));
            $data['terms_list'] = $terms_list;

            self::render_html_e(self::get_application_path() . 'views/widgets/search_form/popup_terms_list.php', $data);
        } else {
            printf(esc_html__('No term are created for %s', 'meta-data-filter'), sanitize_text_field($_REQUEST['tax_name']));
        }
        exit;
    }

    public static function get_terms($taxonomy, $hide_empty = false, $get_childs = true, $selected = 0, $category_parent = 0) {

        $settings = self::get_settings();

        //***
        if (isset($settings['cache_terms_data']) AND $settings['cache_terms_data'] == 1) {
            $cache_key = 'mdf_terms_cache_' . md5($taxonomy . '-' . (int) $hide_empty . '-' . (int) $get_childs . '-' . (int) $selected . '-' . (int) $category_parent);
            if (false !== ( $cats = get_transient($cache_key) )) {
                return $cats;
            }
        }
        //***

        $orderby = apply_filters('mdf_terms_orderby', 'name', $taxonomy);
        $order = apply_filters('mdf_terms_order', 'ASC', $taxonomy);

        $cats_objects = get_categories(array(
            'orderby' => $orderby, //id,name,slug,count,term_group
            'order' => $order,
            'style' => 'list',
            'show_count' => 0,
            'hide_empty' => $hide_empty,
            'use_desc_for_title' => 1,
            'child_of' => 0,
            'hierarchical' => true,
            'title_li' => '',
            'show_option_none' => '',
            'number' => NULL,
            'echo' => 0,
            'depth' => 0,
            'current_category' => $selected,
            'pad_counts' => 0,
            'taxonomy' => $taxonomy,
            'walker' => 'Walker_Category'));

        $cats = array();
        if (!empty($cats_objects)) {
            foreach ($cats_objects as $value) {
                if (is_object($value) AND $value->category_parent == $category_parent) {
                    $cats[$value->term_id] = array();
                    $cats[$value->term_id]['term_id'] = $value->term_id;
                    $cats[$value->term_id]['name'] = $value->name;
                    $cats[$value->term_id]['count'] = $value->count;
                    if ($get_childs) {
                        $cats[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
                    }
                }
            }
        }


        //***
        if (isset($settings['cache_terms_data']) AND $settings['cache_terms_data'] == 1) {
            $period = 0;

            $periods = array(
                0 => 0,
                'hourly' => HOUR_IN_SECONDS,
                'twicedaily' => 12 * HOUR_IN_SECONDS,
                'daily' => DAY_IN_SECONDS,
                'days2' => 2 * DAY_IN_SECONDS,
                'days3' => 3 * DAY_IN_SECONDS,
                'days4' => 4 * DAY_IN_SECONDS,
                'days5' => 5 * DAY_IN_SECONDS,
                'days6' => 6 * DAY_IN_SECONDS,
                'days7' => 7 * DAY_IN_SECONDS
            );

            if (isset($settings['cache_terms_data_auto_clean'])) {
                $period = $settings['cache_terms_data_auto_clean'];

                if (!$period) {
                    $period = 0;
                }
            }

            set_transient($cache_key, $cats, $periods[$period]);
        }
        //***

        return $cats;
    }

//just for get_terms
    private static function assemble_terms_childs($cats_objects, $parent_id) {
        $res = array();
        foreach ($cats_objects as $value) {
            if ($value->category_parent == $parent_id) {
                $res[$value->term_id]['term_id'] = $value->term_id;
                $res[$value->term_id]['name'] = $value->name;
                $res[$value->term_id]['count'] = $value->count;
                $res[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
            }
        }

        return $res;
    }

//ajax - draw html items for selected term in the widget
    public static function draw_term_childs_ajx() {
        $widget_options = array(
            'taxonomies_options_show_count' => ($_REQUEST['is_auto_submit'] == 'true' OR empty($_REQUEST['page_mdf'])) ? 'false' : 'true',
            //if auto submit do not count items count in childs
            'taxonomies_options_post_recount_dyn' => 'true',
            'taxonomies_options_hide_terms_0' => 'true',
            'meta_data_filter_cat' => sanitize_text_field($_REQUEST['mdf_cat']),
            'meta_data_filter_slug' => sanitize_text_field($_REQUEST['slug']),
                //'hide_meta_filter_values' => $_REQUEST['slug'],
        );
        if (isset($_REQUEST['tax_title'])) {
            $widget_options['taxonomies_options_tax_title'][sanitize_text_field($_REQUEST['tax_name'])] = sanitize_text_field($_REQUEST['tax_title']);
        }
        $_GLOBALS['MDF_META_DATA_FILTER'] = sanitize_text_field($_REQUEST['page_mdf']);
        self::draw_term_childs(sanitize_text_field($_REQUEST['type']), sanitize_text_field($_REQUEST['mdf_parent_id']), 0, sanitize_text_field($_REQUEST['tax_name']), true, sanitize_text_field($_REQUEST['hide']), $widget_options);
        exit;
    }

    public static function draw_term_childs($type = 'select', $mdf_parent_id = 0, $selected_id = 0, $tax_name = 0, $is_ajax = true, $hide_string_ids = '', $widget_options = array()) {
        $hide = array();
        if (!empty($hide_string_ids)) {
            $hide = explode(',', $hide_string_ids);
        }

//+++
        $hide_empty = false;
//taxonomies_options_hide_terms_0
        if (isset($widget_options['taxonomies_options_hide_terms_0'])) {
            $hide_empty = (bool) ($widget_options['taxonomies_options_hide_terms_0']);
        }
//print_r($widget_options);
        $terms = self::get_terms($tax_name, $hide_empty, false, 0, $mdf_parent_id);
        switch ($type) {
            case 'select':
                self::draw_term_childs_select($terms, $mdf_parent_id, $selected_id, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options);
                break;
            case 'checkbox':
                self::draw_term_childs_checkbox($terms, $mdf_parent_id, (array) $selected_id, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options);
                break;
            case 'label':
                self::draw_term_childs_label($terms, $mdf_parent_id, (array) $selected_id, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options);
                break;
            case 'multi_select':
                self::draw_term_multi_select($terms, $mdf_parent_id, (array) $selected_id, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options);
                break;
            default:
                break;
        }
    }

    private static function draw_term_childs_select($terms, $mdf_parent_id = 0, $selected_id = 0, $hide = array(), $tax_name = 0, $is_ajax = true, $hide_string_ids = '', $widget_options = array()) {
        if (!empty($terms)) {
            $select_size = isset($widget_options['taxonomies_options_select_size'][$tax_name]) ? (int) $widget_options['taxonomies_options_select_size'][$tax_name] : 1;
            $tax_title = "";
            if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND !empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
            }
            ?>
            <select size="<?php echo esc_attr($select_size); ?>" name="mdf[taxonomy][select][<?php echo esc_attr($tax_name) ?>][]" class="mdf_taxonomy" data-tax-name="<?php echo esc_attr($tax_name) ?>" data-hide="<?php echo esc_attr($hide_string_ids) ?>" data-tax_title="<?php echo esc_attr($tax_title) ?>">
                <option value="-1">
                    <?php
                    $tax_title = "";
                    $tax_title_busy_key = 0;
                    $tax_title_array = "";
                    if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND !empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                        $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                        if (substr_count($tax_title, '^')) {
                            $tax_title_array = explode('^', $tax_title);
                            $tax_title = explode('^', $tax_title);
                            if ($mdf_parent_id == 0) {
                                $tax_title = $tax_title[0];
                            } else {
                                $parent = get_term_by('id', $mdf_parent_id, $tax_name);
                                $index = 1;
                                while ($parent->parent != 0) {
                                    $parent = get_term_by('id', $parent->parent, $tax_name);
                                    $index++;
                                }
                                if (isset($tax_title[$index])) {
                                    $tax_title = $tax_title[$index];
                                    $tax_title_busy_key = $index;
                                } else {
                                    $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                }
                            }
                        }
                    } else if (isset($widget_options['shortcode_taxonomies_title'][$tax_name]) AND !empty($widget_options['shortcode_taxonomies_title'][$tax_name])) {
                        //for shortcodes
                        $tax_title = $widget_options['shortcode_taxonomies_title'][$tax_name];
                        if (substr_count($tax_title, '^')) {
                            $tax_title_array = explode('^', $tax_title);
                            $tax_title = explode('^', $tax_title);
                            if ($mdf_parent_id == 0) {
                                $tax_title = $tax_title[0];
                            } else {
                                $parent = get_term_by('id', $mdf_parent_id, $tax_name);
                                $index = 1;
                                while ($parent->parent != 0) {
                                    $parent = get_term_by('id', $parent->parent, $tax_name);
                                    $index++;
                                }
                                if (isset($tax_title[$index])) {
                                    $tax_title = $tax_title[$index];
                                    $tax_title_busy_key = $index;
                                } else {
                                    $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                }
                            }
                        }
                    } else {
                        $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                    }
                    echo esc_html($tax_title);
                    ?>
                </option>
                <?php foreach ($terms as $term_id => $term) : ?>
                    <?php
                    //do not output hidden terms
                    if (in_array($term_id, $hide)) {
                        continue;
                    }
                    ?>
                    <?php
                    $tax_count = -1;
                    $tax_count_strting = "";
                    $show_option = true;
                    if ($widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1) {
                        $tax_count = self::get_tax_count($term, $tax_name, $widget_options, 'select');
                        $tax_count_strting = ' (' . $tax_count . ')';
                        if ($widget_options['taxonomies_options_post_recount_dyn'] == 'true' OR $widget_options['taxonomies_options_post_recount_dyn'] == 1) {
                            if ($widget_options['taxonomies_options_hide_terms_0'] == 'true' OR $widget_options['taxonomies_options_hide_terms_0'] == 1) {
                                if (!$tax_count) {
                                    $show_option = false;
                                }
                            }
                        }
                    }
                    ?>


                    <?php if ($show_option): ?>
                        <option <?php if ($tax_count == 0 AND ( $widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1)): ?>disabled<?php endif; ?> value="<?php echo esc_attr($term_id) ?>" <?php if ($selected_id == $term_id): ?>selected<?php endif; ?>>
                            <?php esc_html_e($term['name']) ?><?php echo wp_kses_post(wp_unslash($tax_count_strting)); ?>
                        </option>
                    <?php endif; ?>


                <?php endforeach; ?>
            </select>


            <?php
            $show_child_terms1 = (isset($widget_options['taxonomies_options_show_child_terms']) AND $widget_options['taxonomies_options_show_child_terms'][$tax_name] == 1) ? true : false;
            $show_child_terms2 = (isset($widget_options['taxonomies_show_child_terms'][$tax_name]) AND $widget_options['taxonomies_show_child_terms'][$tax_name] == 1) ? true : false;
            $show_child_terms = ($show_child_terms1 OR $show_child_terms2);
            ?>

            <?php if ($show_child_terms): ?>

                <div class="mdf_taxonomy_child_container  mdf_tax_select" ><?php MetaDataFilterHtml::draw_tax_loader(); ?></div>


                <?php
                $show_butaforia = false;

                if (!empty($tax_title_array) AND is_array($tax_title_array)) {
                    foreach ($tax_title_array as $key => $value) {
                        if ($key <= $tax_title_busy_key) {
                            unset($tax_title_array[$key]);
                        } else {
                            break;
                        }
                    }


                    if ($selected_id == -1 OR $selected_id == 0) {
                        $show_butaforia = true;
                    }

                    if (count($tax_title_array) == 0) {
                        $show_butaforia = true;
                    }
                }
                ?>


                <?php if ($show_butaforia): ?>
                    <?php foreach ($tax_title_array as $k => $title) : ?>
                        <div class="mdf_taxonomy_child_container2">
                            <select data-level-id="<?php echo esc_attr(($k + 1)) ?>" disabled="disabled">
                                <option value="-1"><?php echo esc_html($title) ?></option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>


            <?php else: ?>
                <?php if ($is_ajax): ?>
                    <div class="mdf_taxonomy_child_container mdf_tax_select"><?php MetaDataFilterHtml::draw_tax_loader(); ?></div>
                <?php endif; ?>
            <?php endif; ?>




            <?php
        }
        if ($is_ajax) {
            exit;
        }
    }

    private static function draw_term_childs_checkbox($terms, $mdf_parent_id = 0, $selected_ids = array(), $hide = array(), $tax_name = 0, $is_ajax = true, $hide_string_ids = '', $widget_options = array()) {
        if (!empty($terms)) {
            $show_tax_all_childs = (int) self::get_setting('show_tax_all_childs');
            ?>
            <ul class="mdf_taxonomy_check_list">

                <?php foreach ($terms as $term_id => $term) : ?>
                    <?php
                    //do not output hidden terms
                    if (in_array($term_id, $hide)) {
                        continue;
                    }
                    ?>
                    <li>
                        <?php
                        $tax_count = -1;
                        $tax_count_strting = "";
                        $show_option = true;
                        if ($widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1) {
                            $tax_count = self::get_tax_count($term, $tax_name, $widget_options, 'checkbox');
                            $tax_count_strting = ' <span class="mdf_term_count_string">(' . $tax_count . ')</span>';
                            if ($widget_options['taxonomies_options_post_recount_dyn'] == 'true' OR $widget_options['taxonomies_options_post_recount_dyn'] == 1) {
                                if ($widget_options['taxonomies_options_hide_terms_0'] == 'true' OR $widget_options['taxonomies_options_hide_terms_0'] == 1) {
                                    if (!$tax_count) {
                                        $show_option = false;
                                    }
                                }
                            }
                        }
                        $childs_ids = array();
                        if ($show_option) {
                            $childs_ids = get_term_children($term_id, $tax_name);
                        }

                        $unique_id = uniqid();
                        ?>

                        <?php if ($show_option): ?>


                            <input <?php if ($tax_count == 0 AND ( $widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1)): ?>disabled<?php endif; ?> type="checkbox" name="mdf[taxonomy][checkbox][<?php echo esc_attr($tax_name) ?>][]" class="mdf_taxonomy_checkbox <?php if (!empty($childs_ids)): ?>mdf_has_childs<?php endif; ?>" value="<?php echo esc_attr($term_id) ?>" <?php if (in_array($term_id, $selected_ids)): ?>checked<?php endif; ?> data-tax-name="<?php echo esc_attr($tax_name) ?>" data-hide="<?php echo esc_html($hide_string_ids) ?>" id="tax_check_<?php echo esc_attr($term_id) ?>_<?php echo esc_attr($unique_id) ?>" />&nbsp;<label <?php if (in_array($term_id, $selected_ids)): ?>class="mdf_taxonomy_checked"<?php endif; ?> for="tax_check_<?php echo esc_attr($term_id) ?>_<?php echo esc_attr($unique_id) ?>"><?php esc_html_e($term['name']); ?><?php echo wp_kses_post(wp_unslash($tax_count_strting)); ?></label>

                            <?php if ($show_tax_all_childs OR ( array_intersect($childs_ids, $selected_ids) OR (!empty($childs_ids) AND in_array($term_id, $selected_ids)))): ?>
                                <div class="mdf_taxonomy_child_container" style="display: block;">
                                    <?php
                                    $terms = self::get_terms($tax_name, true, false, 0, $term_id);
                                    self::draw_term_childs_checkbox($terms, $term_id, $selected_ids, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options);
                                    ?>
                                </div>
                            <?php else: ?>
                                <div class="mdf_taxonomy_child_container">
                                    <?php MetaDataFilterHtml::draw_tax_loader(); ?>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>
            </ul>

            <?php
        }
        if ($is_ajax) {
            exit;
        }
    }

    private static function draw_term_childs_label($terms, $mdf_parent_id = 0, $selected_ids = array(), $hide = array(), $tax_name = 0, $is_ajax = true, $hide_string_ids = '', $widget_options = array()) {
        if (!empty($terms)) {
            $show_tax_all_childs = (int) self::get_setting('show_tax_all_childs');
            ?>
            <ul class="mdf_taxonomy_label_list">

                <?php foreach ($terms as $term_id => $term) : ?>
                    <?php
                    //do not output hidden terms
                    if (in_array($term_id, $hide)) {
                        continue;
                    }
                    ?>
                    <li>
                        <?php
                        $tax_count = -1;
                        $tax_count_strting = "";
                        $show_option = true;
                        if ($widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1) {
                            $tax_count = self::get_tax_count($term, $tax_name, $widget_options, 'checkbox');
                            $tax_count_strting = '<span class="mdf_label_count">' . $tax_count . '</span>';

                            if ($widget_options['taxonomies_options_post_recount_dyn'] == 'true' OR $widget_options['taxonomies_options_post_recount_dyn'] == 1) {
                                if ($widget_options['taxonomies_options_hide_terms_0'] == 'true' OR $widget_options['taxonomies_options_hide_terms_0'] == 1) {
                                    if (!$tax_count) {
                                        $show_option = false;
                                    }
                                }
                            }
                        }
                        $childs_ids = array();
                        if ($show_option) {
                            $childs_ids = get_term_children($term_id, $tax_name);
                        }

                        $unique_id = uniqid();
                        ?>

                        <?php if ($show_option): ?>

                            <?php
                            if (!in_array($term_id, $selected_ids)):
                                echo $tax_count_strting;
                            endif;
                            ?>
                            <span class="mdf_tax_label_item <?php if (in_array($term_id, $selected_ids)): ?>checked<?php endif; ?>">
                                <?php esc_html_e($term['name']); ?>
                                <input style="display:none;"<?php if ($tax_count == 0 AND ( $widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1)): ?>disabled<?php endif; ?> type="checkbox" name="mdf[taxonomy][checkbox][<?php echo esc_attr($tax_name) ?>][]" class="mdf_taxonomy_label" value="<?php echo esc_attr($term_id) ?>" <?php if (in_array($term_id, $selected_ids)): ?>checked<?php endif; ?> data-tax-name="<?php echo esc_attr($tax_name) ?>" data-hide="<?php echo esc_html($hide_string_ids) ?>" id="tax_check_<?php echo esc_attr($term_id) ?>_<?php echo esc_attr($unique_id) ?>" />

                            </span>
                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>
            </ul>

            <?php
        }
        if ($is_ajax) {
            exit;
        }
    }

    private static function draw_term_multi_select($terms, $mdf_parent_id = 0, $selected_ids = array(), $hide = array(), $tax_name = 0, $is_ajax = true, $hide_string_ids = '', $widget_options = array()) {
        if (true) {
            if (isset($widget_options['shortcode_options'])) {
                $section_height = isset($widget_options['shortcode_options']['taxonomies_checkbox_maxheight'][$tax_name]) ? $widget_options['shortcode_options']['taxonomies_checkbox_maxheight'][$tax_name] : 0;
            } else {
                $section_height = isset($widget_options['taxonomies_options_checkbox_max_height'][$tax_name]) ? $widget_options['taxonomies_options_checkbox_max_height'][$tax_name] : 0;
            }
            ?>
            <div class="mdf_select_emulator_container">
                <dl class="dropdown">

                    <dt>
                        <a href="javascript: void(0);">
                            <span class="hida">

                                <?php
                                $tax_title = "";
                                if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND !empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                    $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                } else if (isset($widget_options['shortcode_taxonomies_title'][$tax_name]) AND !empty($widget_options['shortcode_taxonomies_title'][$tax_name])) {
                                    //for shortcodes
                                    $tax_title = $widget_options['shortcode_taxonomies_title'][$tax_name];
                                } else {
                                    $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                }
                                echo esc_html($tax_title);
                                ?>

                            </span>
                        </a>
                    </dt>

                    <dd>
                        <div class="mutliSelect">
                            <ul <?php if ($section_height > 0): ?>style="max-height: <?php echo esc_attr($section_height) ?>px; height: auto;"<?php endif; ?>>
                                <?php
                                //Draw checkbox  special function
                                self::recursive_show_checkboxеs($terms, 0, $selected_ids, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options, 0)
                                ?>
                            </ul>
                        </div>
                    </dd>
                </dl>
            </div>
            <?php
        }
        if ($is_ajax) {
            //exit;
        }
    }

    private static function recursive_show_checkboxеs($terms, $term_id = 0, $selected_ids = array(), $hide = array(), $tax_name = 0, $is_ajax = true, $hide_string_ids = "", $widget_options = array(), $child_levl = 0) {
        if (!empty($terms)):
            $show_tax_all_childs = (int) self::get_setting('show_tax_all_childs');
            ?>
            <?php foreach ($terms as $term_id => $term) : ?>
                <?php
                //do not output hidden terms
                if (in_array($term_id, $hide)) {
                    continue;
                }
                ?>
                <li style="margin-left:<?php echo esc_attr($child_levl) ?>px">
                    <?php
                    $tax_count = -1;
                    $tax_count_strting = "";
                    $show_option = true;
                    if ($widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1) {
                        $tax_count = self::get_tax_count($term, $tax_name, $widget_options, 'checkbox');
                        $tax_count_strting = ' <span class="mdf_term_count_string">(' . $tax_count . ')</span>';
                        if ($widget_options['taxonomies_options_post_recount_dyn'] == 'true' OR $widget_options['taxonomies_options_post_recount_dyn'] == 1) {
                            if ($widget_options['taxonomies_options_hide_terms_0'] == 'true' OR $widget_options['taxonomies_options_hide_terms_0'] == 1) {
                                if (!$tax_count) {
                                    $show_option = false;
                                }
                            }
                        }
                    }

                    $childs_ids = array();
                    if ($term_id) {
                        $childs_ids = get_term_children($term_id, $tax_name);
                    }

                    $unique_id = uniqid();
                    ?>

                    <?php if ($show_option): ?>

                        <input <?php if ($tax_count == 0 AND ( $widget_options['taxonomies_options_show_count'] == 'true' OR $widget_options['taxonomies_options_show_count'] == 1)): ?>disabled<?php endif; ?> type="checkbox" name="mdf[taxonomy][checkbox][<?php echo esc_attr($tax_name) ?>][]" class="mdf_taxonomy_checkbox " value="<?php echo esc_attr($term_id) ?>" <?php if (in_array($term_id, $selected_ids)): ?>checked<?php endif; ?> data-tax-name="<?php echo esc_attr($tax_name) ?>" data-hide="<?php echo esc_html($hide_string_ids) ?>" id="tax_check_<?php echo esc_attr($term_id) ?>_<?php echo esc_attr($unique_id) ?>" />&nbsp;
                        <label <?php if (in_array($term_id, $selected_ids)): ?>class="mdf_taxonomy_checked"<?php endif; ?> for="tax_check_<?php echo esc_attr($term_id) ?>_<?php echo esc_attr($unique_id) ?>"><?php esc_html_e($term['name']); ?><?php echo wp_kses_post(wp_unslash($tax_count_strting)) ?></label>

                    <?php endif; ?>
                </li>
                <?php
                //print childs;
                if ((count($childs_ids) > 0 AND $childs_ids) AND ( $show_tax_all_childs OR apply_filters("mdf_multy_select_show_all_childs", false))) {
                    $terms = self::get_terms($tax_name, true, false, 0, $term_id);
                    self::recursive_show_checkboxеs($terms, $term_id, $selected_ids, $hide, $tax_name, $is_ajax, $hide_string_ids, $widget_options, $child_levl + 10);
                }
                ?>
            <?php endforeach; ?>

        <?php else: ?>
            <li>
                <?php esc_html_e("no available items", 'meta-data-filter') ?>
            </li>
        <?php endif; ?>
        <?php
    }

//return count value for taxonomies
    private static function get_tax_count($term, $tax_name, $widget_options, $html_type) {
        $count = 0;
        if (!$widget_options['taxonomies_options_show_count']) {
            return 0;
        }

        $slug = $widget_options['meta_data_filter_slug'];

        if ($widget_options['taxonomies_options_show_count']) {
            if ($widget_options['taxonomies_options_post_recount_dyn'] AND self::is_page_mdf_data()) {
                $_GLOBALS['MDF_TAX_USE_CACHE_ARRAY'] = false;
                $page_meta_data_filter = self::get_page_mdf_data();

                if (isset($page_meta_data_filter['taxonomy'])) {
                    $taxonomies = $page_meta_data_filter['taxonomy'];
                }

                if (!isset($taxonomies[$html_type])) {
                    $taxonomies[$html_type] = array();
                }
                if (!isset($taxonomies[$html_type][$tax_name]) OR !is_array($taxonomies[$html_type][$tax_name])) {
                    $taxonomies[$html_type][$tax_name] = array();
                }

                if ($html_type == 'select') {
                    if (!in_array($term['term_id'], $taxonomies[$html_type][$tax_name])) {
                        $taxonomies[$html_type][$tax_name][] = $term['term_id'];
                    }
                }


                if ($html_type == 'checkbox') {
                    $taxonomies[$html_type][$tax_name] = array();
                    $taxonomies[$html_type][$tax_name][] = $term['term_id'];
                }


                $page_meta_data_filter['taxonomy'] = $taxonomies;

                $count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, 0, 0, $slug, 'tax_item');

                $_GLOBALS['MDF_TAX_USE_CACHE_ARRAY'] = true; //!!!important, another way logic error
                $_GLOBALS['MDF_TAX_QUERY_ARRAY'] = array(); //!!!important, another way logic error
            } else {
                $tax_query_array = array();
                $tax_query_array[] = array(
                    'taxonomy' => $tax_name,
                    'field' => 'term_id',
                    'terms' => array($term['term_id'])
                );
                $tax_query_array = apply_filters('mdf_filter_taxonomies2', $tax_query_array);
//+++
                $meta_query_array = array();
//for WOOCOMMERCE hidden products ***
                if ($slug == 'product' AND class_exists('WooCommerce')) {
                    if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                        $tax_query_array[] = array(
                            'taxonomy' => 'product_visibility',
                            'field' => 'name',
                            'terms' => array('exclude-from-catalog'),
                            'operator' => 'NOT IN',
                        );
                    } elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
                        $meta_query_array[] = array(
                            'key' => '_visibility',
                            'value' => array('catalog', 'visible'),
                            'compare' => 'IN'
                        );
                    }
//for out stock products
                    if (self::get_setting('exclude_out_stock_products')) {
                        $meta_query_array[] = array(
                            'key' => '_stock_status',
                            'value' => array('instock'),
                            'compare' => 'IN'
                        );
                    }
//***
                }
//+++
//Trick - how to hide post from search
                $meta_query_array[] = array(
                    'key' => 'mdf_hide_post',
                    'value' => 'out',
                    'compare' => 'NOT EXISTS'
                );
//*** fixed 20-10-2014
                if ($widget_options['meta_data_filter_cat'] > 0) {
                    $meta_query_array[] = array(
                        'key' => 'meta_data_filter_cat',
                        'value' => (int) $widget_options['meta_data_filter_cat'],
                        'compare' => '='
                    );
                }
//***
                $args = array(
                    'post_type' => $slug,
                    'meta_query' => $meta_query_array,
                    'tax_query' => $tax_query_array,
                    'post_status' => array('publish'),
                    'nopaging' => true,
                    'fields' => 'ids',
                    'ignore_sticky_posts' => self::get_setting('ignore_sticky_posts'),
                    'suppress_filters' => false,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'cache_results' => false,
                    'showposts' => false,
                    'comments_popup' => false
                );

//WPML compatibility
                if (class_exists('SitePress')) {
                    $args['lang'] = ICL_LANGUAGE_CODE;
                }

                $counter_obj = new WP_QueryMDFCounter($args);
                $count = $counter_obj->post_count;
            }
        }

        return $count;
    }

//ajax
    public static function change_meta_key() {
        if (!isset($_REQUEST['group_items_nonce']) || !wp_verify_nonce($_REQUEST['group_items_nonce'], 'mdtf_group_items_nonce')) {
            die('0');
        }
        $post_id = intval($_REQUEST['post_id']);
        $old_key = sanitize_key($_REQUEST['old_key']);
        $new_key = sanitize_key($_REQUEST['new_key']);

        global $wpdb;
        $request = array('content' => '', 'notice' => '');
//+++
//check for 'medafi_' prefix
        if ($new_key == 'medafi_') {
            $request['notice'] = esc_html__('Attention! Meta key must be begin from medafi_ but can not such the name!!', 'meta-data-filter');
            wp_die(json_encode($request));
        }

        if (substr($new_key, 0, strlen('medafi_')) != 'medafi_') {
            $request['notice'] = esc_html__('Attention! Meta key must be begin from medafi_!!', 'meta-data-filter');
            wp_die(json_encode($request));
        }

//firstly lets check for new key existing in current post of data constructor
        $items = array_keys(self::get_html_items($post_id));
        if (in_array($new_key, $items)) {
            $request['notice'] = esc_html__('Such key already exists in current filter or its the same fileld. Try another key!', 'meta-data-filter');
            wp_die(json_encode($request));
        }
//second check the new key in other filters
        $data_sql = array(
            array(
                'val' => self::$slug,
                'type' => 'string',
            ),
        );
        $sql = MDTF_HELPER::mdf_prepare("SELECT ID FROM $wpdb->posts WHERE post_type='%s'", $data_sql);
        $results = $wpdb->get_results($sql, ARRAY_N);
        $ids = array();
        if (!empty($results)) {
            foreach ($results as $value) {
                if ($value[0] == $post_id) {
                    continue;
                }
                $ids[] = $value[0];
            }
        }
        if (!empty($ids)) {
            foreach ($ids as $p_id) {
                $get_html_items = self::get_html_items($p_id);
                if (is_array($get_html_items)) {
                    $items = array_keys($get_html_items);
                    if (is_array($items) AND in_array($new_key, $items)) {
                        $post_info = get_post($p_id);
                        $term_list = wp_get_post_terms($p_id, self::$slug_cat, array("fields" => "names"));
                        $term_list = implode(',', $term_list);
//***
                        $request['notice'] = sprintf(esc_html__('Filter `%s` (%s) already has such key. Filter status: `%s`!', 'meta-data-filter'), $post_info->post_title, $term_list, $post_info->post_status);
                        wp_die(json_encode($request));
                    }
                }
            }
        }
//third - all OK, lets change it
        $items = self::get_html_items($post_id);
        $new_items = array();
        if (!empty($items) AND is_array($items)) {
            foreach ($items as $key => $value) {
                if ($key == $old_key) {
                    $value['meta_key'] = $new_key;
                    $new_items[$new_key] = $value;
                    continue;
                }
                $new_items[$key] = $value;
            }
            update_post_meta($post_id, 'html_items', $new_items);
        }

//replace old key by new one in posts which are already uses old key
//get all posts whish are uses old key and replace keys in synhro_data_array
        $data_sql = array(
            array(
                'val' => $old_key,
                'type' => 'string',
            ),
        );
        $sql = MDTF_HELPER::mdf_prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='%s'", $data_sql);
        $results = $wpdb->get_results($sql, ARRAY_N);
        if (!empty($results) AND is_array($results)) {
            foreach ($results as $value) {
                $p_id = $value[0];
                $synhro_data_array = get_post_meta($p_id, 'page_meta_data_filter', true);
                if (!empty($synhro_data_array) AND is_array($synhro_data_array)) {
                    $tmp_new_data = array();
                    foreach ($synhro_data_array as $key => $value) {
                        if ($key == $old_key) {
                            $tmp_new_data[$new_key] = $value;
                            continue;
                        }
                        $tmp_new_data[$key] = $value;
                    }
                    update_post_meta($p_id, 'page_meta_data_filter', $tmp_new_data);
                }
            }
        }
//update old keys in posts which are using it
        $data_sql = array(
            array(
                'val' => $new_key,
                'type' => 'string',
            ),
            array(
                'val' => $old_key,
                'type' => 'string',
            ),
        );
        $wpdb->query(MDTF_HELPER::mdf_prepare("UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s", $data_sql));
        $request['notice'] = esc_html__('Updated', 'meta-data-filter');
        $request['content'] = self::render_html(self::get_application_path() . 'views/add_item_to_data_group.php', array('itemdata' => @$new_items[$new_key], 'uniqid' => $new_key));
        wp_die(json_encode($request));
    }

    public static function add_additional_taxonomies($additional_taxonomies, $register_request = TRUE, $eq = '=') {
        $additional_tax_query_array = array();

        if (!empty($additional_taxonomies)) {
            $tmp = explode('^', $additional_taxonomies);
            if (!empty($tmp) AND is_array($tmp)) {
                foreach ($tmp as $tmp2) {
                    $tmp2 = explode($eq, $tmp2);
                    $taxonomy = $tmp2[0];
                    if (!empty($tmp2) AND is_array($tmp2) AND isset($tmp2[1])) {
                        $terms = explode(',', $tmp2[1]);
                        $terms = self::service_additional_tax_query_array($taxonomy, $terms);
//***
                        if (!empty($terms)) {
                            $additional_tax_query_array[] = array(
                                'taxonomy' => $taxonomy,
                                'field' => 'term_id',
                                'terms' => $terms
                            );
                        }
                    }
                }
                if ($register_request) {
                    $_REQUEST['MDF_ADDITIONAL_TAXONOMIES'] = $additional_tax_query_array;
                }
            }
        }

        return $additional_tax_query_array;
    }

//utilit func: for additional syntax in widgets and shortcodes
    private static function service_additional_tax_query_array($taxonomy, $terms) {
        global $wp_query;

        foreach ($terms as $kk => $term_id) {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if ($term_id == 'current') {
                    $terms[$kk] = (int) $_REQUEST['mdf_current_term_id'];
                }
            } else {
                if ($term_id == 'current' AND is_tax($taxonomy)) {
                    $term = $wp_query->queried_object;
                    if ($term->taxonomy == $taxonomy) {
                        $terms[$kk] = $term->term_id;
                    }
                }
            }
        }

//clean of 'current'
        foreach ($terms as $kk => $term_id) {
            if ($term_id == 'current') {
                unset($terms[$kk]);
            }
        }



//check for zero term_id
        if (!empty($terms) AND is_array($terms)) {
            foreach ($terms as $k => $tid) {
                if ($tid == 0) {
                    unset($terms[$k]);
                }
            }
        }

        return $terms;
    }

//util func
    public static function service_additional_tax_query_string($additional_taxonomies_string) {
        $result_string = "";
        $tax_array = self::add_additional_taxonomies($additional_taxonomies_string, false);
        if (!empty($tax_array)) {
            foreach ($tax_array as $value) {
                $result_string .= $value['taxonomy'] . '=' . implode(',', $value['terms']);
            }
        }

        return $result_string;
    }

//***

    public static function draw_front_toggle($section_toggle = 0) {
        if ($section_toggle > 0) {
            if ($section_toggle == 1) {
//opened
                ?>
                <a href="#" class="mdf_front_toggle mdf_front_toggle_opened" data-condition="opened"><?php esc_html_e(MetaDataFilterCore::get_setting('toggle_close_sign') ? MetaDataFilterCore::get_setting('toggle_close_sign') : '-') ?></a>
                <?php
            } else {
//closed
                ?>
                <a href="#" class="mdf_front_toggle mdf_front_toggle_closed" data-condition="closed"><?php esc_html_e(MetaDataFilterCore::get_setting('toggle_open_sign') ? MetaDataFilterCore::get_setting('toggle_open_sign') : '+') ?></a>
                <?php
            }
        }
    }

//need to hack wp query of any 3th party shortcodes
    public static function rewrite_search_query_args($args = array()) {
        if (self::is_page_mdf_data()) {
            $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
            $_REQUEST['mdf_get_query_args_only'] = true;
            do_shortcode('[meta_data_filter_results]');
            $args = self::sanitize_array_r($_REQUEST['meta_data_filter_args']);
        }

        return $args;
    }

    //for essential-grid plugin compatibility
    public static function essgrid_modify_posts($arrPosts) {
        if (isset($_REQUEST['mdf_is_essential'])) {
            global $mdf_loop;
            $arrPosts = $mdf_loop->get_posts();
            if (!empty($arrPosts)) {
                foreach ($arrPosts as $key => $value) {
                    $arrPosts[$key] = (array) $value;
                }
            }
        }


        return $arrPosts;
    }
}

add_action('init', array('MetaDataFilter', 'init'), 1);
add_action('widgets_init', array('MetaDataFilter', 'widgets_init'));

