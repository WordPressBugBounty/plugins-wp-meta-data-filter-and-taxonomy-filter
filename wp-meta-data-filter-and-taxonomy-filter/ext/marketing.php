<?php

class MDF_Marketing extends MetaDataFilterCore {

    public static $settings = array();

    public static function init() {
        if (!is_admin()) {
            self::check_for_marketing_link();
        }

        $args = array(
            'labels' => array(
                'name' => esc_html__('MDTF Marketing', 'meta-data-filter'),
                'singular_name' => esc_html__('Marketing Links', 'meta-data-filter'),
                'add_new' => esc_html__('Add New Link', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Link', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Link', 'meta-data-filter'),
                'new_item' => esc_html__('New Link', 'meta-data-filter'),
                'view_item' => esc_html__('View Link', 'meta-data-filter'),
                'search_items' => esc_html__('Search Links', 'meta-data-filter'),
                'not_found' => esc_html__('No Links found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Links found in Trash', 'meta-data-filter'),
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
            'supports' => array('title', 'excerpt'),
            'rewrite' => array('slug' => self::$slug_links),
            'show_in_admin_bar' => false,
            'menu_icon' => '',
            'taxonomies' => array(self::$slug_links_cat)
        );


        register_taxonomy(self::$slug_links_cat, array(self::$slug_links), array(
            "labels" => array(
                'name' => esc_html__('Links Types', 'meta-data-filter'),
                'singular_name' => esc_html__('MDTF Marketing Links Types', 'meta-data-filter'),
                'add_new' => esc_html__('Add New', 'meta-data-filter'),
                'add_new_item' => esc_html__('Add New Marketing Type', 'meta-data-filter'),
                'edit_item' => esc_html__('Edit Marketing Types', 'meta-data-filter'),
                'new_item' => esc_html__('New Marketing Type', 'meta-data-filter'),
                'view_item' => esc_html__('View Marketing Type', 'meta-data-filter'),
                'search_items' => esc_html__('Search Marketing Types', 'meta-data-filter'),
                'not_found' => esc_html__('No Marketing Types found', 'meta-data-filter'),
                'not_found_in_trash' => esc_html__('No Marketing Types found in Trash', 'meta-data-filter'),
                'parent_item_colon' => ''
            ),
            "singular_label" => esc_html__("Marketing Types", 'meta-data-filter'),
            'show_in_nav_menus' => true,
            'capabilities' => array('manage_terms'),
            'show_ui' => true,
            'term_group' => true,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::$slug_links),
            'orderby' => 'name'
        ));

        register_post_type(self::$slug_links, $args);

        self::$settings = get_option('mdf_marketing_settings');

        add_action('admin_init', array(__CLASS__, 'admin_init'), 1);
        add_action('admin_menu', array(__CLASS__, 'admin_menu'), 1);
        add_action('save_post', array(__CLASS__, 'save_post'), 1);
        add_action("manage_" . self::$slug_links . "_posts_custom_column", array(__CLASS__, "show_edit_columns_content"));
        add_filter("manage_" . self::$slug_links . "_posts_columns", array(__CLASS__, "show_edit_columns"));
        add_filter('parse_query', array(__CLASS__, "parse_query"));
        add_action('restrict_manage_posts', array(__CLASS__, "restrict_manage_posts"));
       
    }

    public static function admin_menu() {
        add_submenu_page('edit.php?post_type=' . self::$slug_links, esc_html__("Marketing Settings", 'meta-data-filter'), esc_html__("Marketing Settings", 'meta-data-filter'), 'manage_options', 'mdf_marketing_settings', array(__CLASS__, 'draw_settings_page'));
    }

    public static function admin_init() {
        add_meta_box("mdf_marketing_options", esc_html__("MDTF Marketing Options", 'meta-data-filter'), array(__CLASS__, 'draw_options_meta_box'), self::$slug_links, "normal", "high");
    }

    public static function draw_options_meta_box($post) {
        $data = array();
        $data['post_id'] = $post->ID;
        $data['mdf_link_seo_prefix'] = get_post_meta($post->ID, 'mdf_link_seo_prefix', TRUE);
        if (!$data['mdf_link_seo_prefix']) {
            $data['mdf_link_seo_prefix'] = 'mdf';
        }
        $data['mdf_link_seo_suffix'] = get_post_meta($post->ID, 'mdf_link_seo_suffix', TRUE);
        self::render_html_e(self::get_application_path() . 'views/marketing/metabox.php', $data);
    }

    public static function draw_settings_page() {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        if (isset($_POST) AND!empty($_POST)) {
            if (isset($_POST['mdf_marketing_settings'])) {
				$mdf_marketing_settings = self::sanitize_array_r($_POST['mdf_marketing_settings']);
                if (isset($mdf_marketing_settings['link_prefix']) AND !empty($mdf_marketing_settings['link_prefix'])) {
                    foreach ($mdf_marketing_settings['link_prefix'] as $k => $prefix) {
                        $mdf_marketing_settings['link_prefix'][$k] = trim($prefix, ' ,');
                    }
                }
				$_POST['mdf_marketing_settings'] = $mdf_marketing_settings;
                update_option('mdf_marketing_settings', $mdf_marketing_settings);
            }
        }
        $data = array();
        $data['settings'] = self::get_marketing_settings();
        
        wp_enqueue_script('mdf_marketing', self::get_application_uri() . 'js/marketing.js', array('jquery', 'jquery-ui-core'));
        self::render_html_e(self::get_application_path() . 'views/marketing/settings.php', $data);
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
    public static function get_link_prefixes() {
        $prefixes = self::get_marketing_setting('link_prefix');
        if (empty($prefixes)) {
            $prefixes = array();
        }
        return array_merge(array('mdf'), $prefixes);
    }

    public static function get_post_link_prefix($post_id) {
        return get_post_meta($post_id, 'mdf_link_seo_prefix', TRUE);
    }

    public static function save_post() {
        if (!empty($_POST)) {
            global $post;
            if (is_object($post)) {
                if ($post->post_type == self::$slug_links) {
                    if (isset($_POST['mdf_link_seo_suffix'])) {
                        update_post_meta($post->ID, 'mdf_link_seo_prefix', self::sanitize_array_r($_POST['mdf_link_seo_prefix']));
                        update_post_meta($post->ID, 'mdf_link_seo_suffix', self::sanitize_array_r($_POST['mdf_link_seo_suffix']));
                    }
                }
            }
        }
    }

    public static function show_edit_columns($columns) {
        $columns = array(
            "cb" => '<input type="checkbox" />',
            "title" => esc_html__("Title", 'meta-data-filter'),
            "link" => esc_html__("Link", 'meta-data-filter'),
            self::$slug_links_cat => esc_html__("Types", 'meta-data-filter'),
            "date" => esc_html__("Date", 'meta-data-filter'),
        );

        return $columns;
    }

    public static function show_edit_columns_content($column) {
        global $post;

        switch ($column) {
            case "link":
                echo home_url() . '/';
				echo esc_attr(self::get_post_link_prefix($post->ID));
				echo '/';
				echo esc_attr($post->ID);
				echo '/' . get_post_meta($post->ID, 'mdf_link_seo_suffix', TRUE);
                break;
            case self::$slug_links_cat:
                echo get_the_term_list($post->ID, self::$slug_links_cat, '', ',');
                break;
        }
    }

    public static function restrict_manage_posts() {
        global $typenow;
        global $wp_query;
        if ($typenow == self::$slug_links) {
            $mdf_taxonomy = get_taxonomy(self::$slug_links_cat);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("Show All", 'meta-data-filter') . " " . $mdf_taxonomy->label,
                'taxonomy' => self::$slug_links_cat,
                'name' => self::$slug_links_cat,
                'orderby' => 'name',
                'selected' => @$wp_query->query[self::$slug_links_cat],
                'hierarchical' => true,
                'depth' => 3,
                'show_count' => true, // Show # listings in parens
                'hide_empty' => true,
            ));
        }
    }

    public static function parse_query($query) {
        global $pagenow;
        $post_type = self::$slug_links; // change HERE
        $taxonomy = self::$slug_links_cat; // change HERE
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public static function check_for_marketing_link() {
        $parsed_url = trim(sanitize_text_field($_SERVER['REQUEST_URI']), ' /');
        $path_array = explode('/', $parsed_url);
        if (isset($path_array[0]) AND in_array(trim($path_array[0]), self::get_link_prefixes())) {
            $post_id = $path_array[1];
            if (self::get_post_link_prefix($post_id) === trim($path_array[0])) {
                $location = get_post_field('post_excerpt', $post_id);
                $status = get_post_field('post_status', $post_id);
                //for qtranslate
                if (function_exists('qtrans_enableLanguage')) {
                    if (!substr_count($location, 'lang=')) {
                        $location = str_replace('?', '?lang=' . $GLOBALS['q_config']['language'] . '&', $location);
                    }
                }
                if (!empty($location) AND $status == 'publish') {
                    //https://support.google.com/webmasters/answer/93633?hl=en
                    wp_redirect($location, 301);
                    exit;
                }
            }
        }
    }

}
