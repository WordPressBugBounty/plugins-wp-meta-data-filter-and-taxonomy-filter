<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MetaDataFilterCore {

    public static $items_types = array();
    public static $tax_items_types = array();
//+++
    public static $slug = 'meta_data_filter';
    public static $slug_cat = 'meta_data_filter_cat';
    public static $slug_links = 'mdf_links';
    public static $slug_links_cat = 'mdf_links_cat';
    public static $slug_shortcodes = 'mdf_shortcodes';
    public static $slug_woo_sort = 'mdf_woo_sort';
    public static $slug_const_links = 'mdf_const_links';
    public static $max_int = 2147483647;
    public static $session_id = 0;
    public static $default_order_by = 'date';
    //http://codex.wordpress.org/Class_Reference/WP_Query
    public static $allowed_order_by = array('ID', 'author', 'title', 'name', 'date', 'modified', 'rand', 'comment_count', 'menu_order');
    public static $default_order = 'DESC';
    public static $where_keep_search_data = 'transients'; //transients,session
    public static $mdf_query_cache_table = 'mdf_query_cache';

    public static function init() {
        $where_keep_search_data = self::get_setting('keep_search_data_in');
        if (!empty($where_keep_search_data)) {
            self::$where_keep_search_data = $where_keep_search_data;
        }

        if (!session_id() AND $where_keep_search_data == "session") {
            try {
                @session_start([
                            'read_and_close' => true,
                ]);
            } catch (Exception $e) {
                //***
            }
        }

        self::$session_id = session_id();
//+++

        $default_order_by = self::get_setting('default_order_by');
        $default_order = self::get_setting('default_order');
//+++
        if (!empty($default_order)) {
            self::$default_order = $default_order;
        }
        if (!empty($default_order_by)) {
            self::$default_order_by = $default_order_by;
        }
    }

    public static function get_application_path() {
        return plugin_dir_path(__FILE__);
    }

    public static function get_application_uri() {
        return plugin_dir_url(__FILE__);
    }

    public static function get_html_items($post_id) {
        return get_post_meta($post_id, 'html_items', true);
    }

    public static function get_settings() {
        return get_option('meta_data_filter_settings');
    }

    public static function get_setting($key) {
        $settings = self::get_settings();
        return (isset($settings[$key]) ? $settings[$key] : 0);
    }

    public static function get_marketing_settings() {
        return get_option('mdf_marketing_settings');
    }

    public static function get_marketing_setting($key) {
        $settings = self::get_marketing_settings();
        return (isset($settings[$key]) ? $settings[$key] : 0);
    }

    public static function get_search_url_page() {
        $s = self::get_settings();
        return $s['search_url'];
    }

    public static function get_post_types() {
        $all_post_types = get_post_types();
        unset($all_post_types['nav_menu_item']);
        unset($all_post_types['revision']);
        unset($all_post_types[self::$slug]);
        unset($all_post_types[self::$slug_woo_sort]);
        unset($all_post_types[self::$slug_shortcodes]);
        unset($all_post_types[self::$slug_links]);
        unset($all_post_types[self::$slug_const_links]);

        return $all_post_types;
    }

    public static function get_page_mdf_string() {
//we need this to simply redraw search form for another selected filter-category
        if (isset($_REQUEST['mdf_ajax_request']) AND isset($_REQUEST['simple_form_redraw']) AND $_REQUEST['simple_form_redraw'] == 1) {
            return "";
        }


        $key_string = "";
        if (isset($_REQUEST['page_mdf'])) {
            $key_string = sanitize_text_field($_REQUEST['page_mdf']);
        }
		
        $string = self::get_page_mdf_session($key_string);

        return $string;
    }

    public static function set_page_mdf_session($string) {
		
//$string is base64 encoded
        $key_string = md5($string);

//save long search data string to avoid it from browser link
        if (self::$where_keep_search_data == 'session') {

            if (!isset($_SESSION['page_mdf'])) {
                $_SESSION['page_mdf'] = array();
            }
            $_SESSION['page_mdf'][$key_string] = $string;
        } else {
            set_transient($key_string, $string, DAY_IN_SECONDS);
        }


        return $key_string;
    }

    public static function get_page_mdf_session($key_string) {

        static $value = "";
        if (!empty($value)) {
            return $value;
        }
        //+++
        if (!is_numeric($key_string)) {
            if (self::$where_keep_search_data == 'session') {

                if (!isset($_SESSION['page_mdf'])) {
                    $_SESSION['page_mdf'] = array();
                }

                if (isset($_SESSION['page_mdf'][$key_string])) {
                    $value = $_SESSION['page_mdf'][$key_string];
                }
            } else {
                $value = (string) get_transient($key_string);
            }
        } else {
            $value = get_post_meta($key_string, 'page_mdf_string', TRUE);
        }

        return $value;
    }

    public static function get_page_mdf_data() {
        static $page_meta_data_filter;
//+++
        if (!empty($page_meta_data_filter)) {
            $_GLOBALS['MDF_META_DATA_FILTER'] = $page_meta_data_filter;
            return $page_meta_data_filter;
        }
//+++
		
        $page_mdf_string = self::get_page_mdf_string();

//is base64
        //create filter for posts messenger 
        $page_meta_data_filter = apply_filters("mdf_filter_arg_page", json_decode(base64_decode($page_mdf_string), true));

//***

        $_GLOBALS['MDF_META_DATA_FILTER'] = $page_meta_data_filter;
        return $page_meta_data_filter;
    }

    public static function is_page_mdf_data() {//if isset get page_mdf
        $data = self::get_page_mdf_data();

        if (!empty($data)) {
            return true;
        }

        return false;
    }

    public static function get_html_items_by_cat($cat_id) {
        $args = array(
            'post_type' => self::$slug,
            'orderby' => 'meta_value_num meta_value',
            'meta_key' => 'sequence',
            'order' => 'ASC',
            'ignore_sticky_posts' => self::get_setting('ignore_sticky_posts'),
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => self::$slug_cat,
                    'field' => 'term_id',
                    'terms' => $cat_id
                )
            ),
            'post_status' => array('publish'),
            'nopaging' => true,
            'suppress_filters' => true
        );
        $res = array();
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $tmp = array();
                $tmp['post_title'] = $post->post_title;
                $tmp['html_items'] = self::get_html_items($post->ID);
                $res[$post->ID] = $tmp;
            }
        }

        return $res;
    }

    public static function front_script_includer() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
        wp_enqueue_style('meta_data_filter_front', MetaDataFilterCore::get_application_uri() . 'css/front.css');
//tooltipster
        wp_enqueue_style('tooltipster', self::get_application_uri() . 'js/tooltipster/css/tooltipster.css');
        $tooltip_theme = self::get_setting('tooltip_theme');
        if ($tooltip_theme != 'default') {
            wp_enqueue_style('tooltipster_theme', self::get_application_uri() . 'js/tooltipster/css/themes/tooltipster-' . $tooltip_theme . '.css');
        }
        wp_enqueue_script('tooltipster', self::get_application_uri() . 'js/tooltipster/js/jquery.tooltipster.min.js', array('jquery'));
//beauty scrolling malihu-custom-scrollbar
//http://manos.malihu.gr/jquery-custom-content-scroller/
        if (self::get_setting('use_custom_scroll_bar')) {
            wp_enqueue_script('mousewheel', self::get_application_uri() . 'js/malihu-custom-scrollbar/jquery.mousewheel.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar', self::get_application_uri() . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar-concat', self::get_application_uri() . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.concat.min.js', array('jquery'));
            wp_enqueue_style('malihu-custom-scrollbar', self::get_application_uri() . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.css');
        }
//beauty range-sliders ion.range-slider
//http://ionden.com/a/plugins/ion.rangeSlider/
        wp_enqueue_script('ion.range-slider', self::get_application_uri() . 'js/ion.range-slider/ion.rangeSlider.min.js', array('jquery'));
        wp_enqueue_style('ion.range-slider', self::get_application_uri() . 'js/ion.range-slider/css/ion.rangeSlider.css');
        $ion_slider_skin = 'skinNice';
        $settings = self::get_settings();
        if (isset($settings['ion_slider_skin'])) {
            $ion_slider_skin = $settings['ion_slider_skin'];
        }
        wp_enqueue_style('ion.range-slider-skin', self::get_application_uri() . 'js/ion.range-slider/css/ion.rangeSlider.' . $ion_slider_skin . '.css');

        if (self::get_setting('use_chosen_js_w') OR self::get_setting('use_chosen_js_s')) {
//chosen - beautiful drop-downs http://harvesthq.github.io/chosen/
            wp_enqueue_script('chosen-drop-down', self::get_application_uri() . 'js/chosen/chosen.jquery.min.js', array('jquery'));
            wp_enqueue_style('chosen-drop-down', self::get_application_uri() . 'js/chosen/chosen.min.css');
        }

        if (self::get_setting('use_custom_icheck')) {
//http://fronteed.com/iCheck/
            $skin = self::get_setting('icheck_skin');
            if (!$skin) {
                $skin = 'flat_blue';
            }
            $skin = explode('_', $skin);
            wp_enqueue_script('icheck-jquery', self::get_application_uri() . 'js/icheck/icheck.js', array('jquery'));
            wp_enqueue_style('icheck-jquery-color', self::get_application_uri() . 'js/icheck/skins/' . $skin[0] . '/' . $skin[1] . '.css');
        }


        if (self::get_setting('overlay_skin') != 'default') {
            wp_enqueue_script('mdft_plainoverlay', self::get_application_uri() . 'js/plainoverlay/jquery.plainoverlay.min.js', array('jquery'));
            wp_enqueue_style('mdft_plainoverlay', self::get_application_uri() . 'css/plainoverlay.css');
			// change  to  https://github.com/anseki/plain-overlay
        }
    }

    public static function render_html($pagepath, $data = array()) {
        if (isset($data['pagepath'])) {
            unset($data['pagepath']);
        }
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }
    public static function render_html_e($pagepath, $data = array()) {
        if (isset($data['pagepath'])) {
            unset($data['pagepath']);
        }
        @extract($data);

        include($pagepath);
    }	

//API

    /** Get meta value of post
     *
     * @param string $key Meta Key
     * @param int $post_id Post ID, if its == 0, ID will try to get from globals
     * @param bool $look_for_reflection Set to true if you now or you think that param is reflected
     *
     * @return mixed Depends of what meta field is keeps
     */
    public static function get_field($key, $post_id = 0, $look_for_reflection = false) {
        $res = "";
        if ($post_id == 0) {
            $post_id = get_the_ID();
            if (!$post_id) {
                return esc_html__('something wrong with post ID', 'meta-data-filter');
            }
        }
//***
        if ($look_for_reflection) {
            global $wpdb;
            $filter_post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_value LIKE '%{$key}%'");
            $html_items = self::get_html_items($filter_post_id);
            if (isset($html_items[$key])) {
                if (isset($html_items[$key]['is_reflected']) AND $html_items[$key]['is_reflected'] == 1) {
                    $reflected_key = $html_items[$key]['reflected_key'];
                    $res = get_post_meta($post_id, $reflected_key, TRUE);
                } else {
                    $res = get_post_meta($post_id, $key, TRUE);
                }
            } else {
                return esc_html__('something wrong with meta key', 'meta-data-filter');
            }
        } else {
            $res = get_post_meta($post_id, $key, TRUE);
        }

        return $res;
    }

    public static function get_val_as_select_title($post_id, $meta_key) {
        global $wpdb;
        $option_key = get_post_meta($post_id, $meta_key, true);

        $data = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='html_items' AND meta_value LIKE %s ORDER BY post_id ASC", '%' . $wpdb->esc_like($meta_key) . '%'), ARRAY_N);
        $section_id = 0;
        if (!empty($data)) {
            $section_id = $data[0][0];
        }

        if ($section_id) {
            $html_items = self::get_html_items($section_id);
            if (isset($html_items[$meta_key])) {
                if ($html_items[$meta_key]['select_key']) {
                    foreach ($html_items[$meta_key]['select_key'] as $key => $value) {
                        if ($value == $option_key) {
                            return $html_items[$meta_key]['select'][$key];
                        }
                    }
                }
            }
        }


        return esc_html__('something wrong with meta key', 'meta-data-filter');
    }

//is customer look the site from mobile device
    public static function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

//this function needs for multi range sider
    public static function range_slider_multicontroller($slug, $meta_key, $from, $to) {
//*** is key is multy values
        $is_multi = false;
        if ($is_multi) {
            //TODO - for usual range-slider more than 1 value
        }

//if not multi val range-slider
        return array(
            'key' => $meta_key,
            'value' => array($from, $to),
            'type' => 'DECIMAL',
            'compare' => 'BETWEEN'
        );
    }

    //ajax
    public static function cache_count_data_clear() {
        global $wpdb;
        $sql = "TRUNCATE TABLE " . $wpdb->prefix . self::$mdf_query_cache_table;
        $wpdb->query($sql);
    }

    //ajax
    public static function cache_terms_data_clear() {
        global $wpdb;
        $res = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_mdf_terms_cache_%'");

        if (!empty($res)) {
            foreach ($res as $transient) {
                delete_transient(str_replace('_transient_', '', $transient->option_name));
            }
        }

    }

    public static function escape($value) {
        return sanitize_text_field(esc_html($value));
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
}

class WP_QueryMDFCounter {

    public $post_count = 0;
    public $found_posts = 0;
    public $key_string = "";
    public $table = "";

    public function __construct($query) {
        global $wpdb;
        $query = (array) $query;
        
        $key = md5(json_encode($query));
        $this->key_string = 'mdf_count_cache_' . $key;
        $this->table = $wpdb->prefix . MetaDataFilterCore::$mdf_query_cache_table;
        //***
        if (MetaDataFilterCore::get_setting('cache_count_data')) {
            $value = $this->get_value();
            if ($value != -1) {
                $this->post_count = $this->found_posts = $value;
            } else {
                $q = new WP_QueryMDFCounterIn($query);
                $this->post_count = $this->found_posts = $q->post_count;
                unset($q);
                $this->set_value();
            }
        } else {
            $q = new WP_QueryMDFCounterIn($query);
            $this->post_count = $this->found_posts = $q->post_count;
            unset($q);
        }
    }

    private function set_value() {
        global $wpdb;
        $data_sql = array(
            array(
                'val' => $this->key_string,
                'type' => 'string',
            ),
            array(
                'val' => $this->post_count,
                'type' => 'int',
            ),
        );
        $wpdb->query(MDTF_HELPER::mdf_prepare("INSERT INTO {$this->table} (mkey, mvalue) VALUES (%s, %d)", $data_sql));
    }

    private function get_value() {
        global $wpdb;
        $result = -1;
        $data_sql = array(
            array(
                'val' => $this->key_string,
                'type' => 'string',
            ),
        );
        $sql = MDTF_HELPER::mdf_prepare("SELECT mkey,mvalue FROM {$this->table} WHERE mkey=%s", $data_sql);
        $value = $wpdb->get_results($sql);

        if (!empty($value)) {
            $value = end($value);
            if (isset($value->mkey)) {
                $result = $value->mvalue;
            }
        }

        return $result;
    }

}

class WP_QueryMDFCounterIn extends WP_Query {

    function set_found_posts($q, $limits) {
        return false;
    }

}

if (!function_exists('is_product_taxonomy')) {

    /**
     * is_product_taxonomy - Returns true when viewing a product taxonomy archive.
     *
     * @access public
     * @return bool
     */
    function is_product_taxonomy() {
        return is_tax(get_object_taxonomies('product'));
    }

}

