<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MetaDataFilterPage extends MetaDataFilterCore
{

    public static function init()
    {
        parent::init();
        add_action('wp_ajax_meta_data_filter_get_data_group_topage_items', array(__CLASS__, 'get_data_group_topage_items'));
        add_action('wp_ajax_mdf_encode_search_get_params', array(__CLASS__, 'encode_search_get_params'));
        add_action('wp_ajax_nopriv_mdf_encode_search_get_params', array(__CLASS__, 'encode_search_get_params'));
        //+++
        add_action('wp_ajax_mdf_get_ajax_auto_recount_data', array(__CLASS__, 'get_ajax_auto_recount_data'));
        add_action('wp_ajax_nopriv_mdf_get_ajax_auto_recount_data', array(__CLASS__, 'get_ajax_auto_recount_data'));
    }

    public static function add_meta_box()
    {
        $settings = self::get_settings();
        if (isset($settings['post_types']))
        {
            if (!empty($settings['post_types']))
            {
                foreach ($settings['post_types'] as $post_name)
                {
                    add_meta_box("meta_data_filter_options", esc_html__("Meta Data Filter Options", 'meta-data-filter'), array(__CLASS__, 'data_meta_box'), $post_name, "normal", "high");
                }
            }
        }
    }

    //ajax - get new items by cat select
    public static function get_data_group_topage_items()
    {
        self::draw_back_page_items((int)$_REQUEST['cat_id']);
        exit;
    }

    //admin page or *? panel
    public static function draw_back_page_items($cat_id = 0, $post_id = 0)
    {
        if ($cat_id == 0)
        {
            $cat_id = get_post_meta($post_id, self::$slug_cat, TRUE);
        }
        $blocks = self::get_fiters_posts_datablocks(self::get_fiters_posts($cat_id));
        //***
        if (!empty($blocks))
        {
            $page_meta_data_filter = $cat_id > 0 ? get_post_meta($post_id, 'page_meta_data_filter', true) : array();
            foreach ($blocks as $filter_post_id => $filter)
            {
                self::render_html_e(self::get_application_path() . 'views/page/draw_back_page_items.php', array(
                    'filter' => $filter,
                    'post_id' => $post_id,
                    'filter_post_id' => $filter_post_id,
                    'page_meta_data_filter' => $page_meta_data_filter
                ));
            }
        } else
        {
            ?>
            <input type="hidden" name="page_meta_data_filter" value="" />
            <?php

        }
    }

    //for single page widget
    public static function draw_single_page_items($post_id = 0, $show_absent_items = true)
    {
        wp_enqueue_script('meta_data_filter_widget', self::get_application_uri() . 'js/front.js', array('jquery'));
        $cat_id = get_post_meta($post_id, self::$slug_cat, TRUE);
        $blocks = self::get_fiters_posts_datablocks(self::get_fiters_posts($cat_id));
        //***
        $page_meta_data_filter = $cat_id > 0 ? get_post_meta($post_id, 'page_meta_data_filter', true) : array();
        foreach ($blocks as $filter_post_id => $filter_post)
        {
            self::render_html_e(self::get_application_path() . 'views/page/draw_single_page_items.php', array(
                'filter_post' => $filter_post,
                'post_id' => $post_id,
                'page_meta_data_filter' => $page_meta_data_filter,
                'show_absent_items' => $show_absent_items
            ));
        }
    }

    public static function draw_front_page_items($widget_options, $form_uniqid)
    {
        
        if ($widget_options[MetaDataFilterCore::$slug_cat] == MetaDataFilter::WIDGET_TAXONOMIES_ONLY)
        {
            $blocks = array(
                array(
                    'name' => '~',
                    'items' => array(
                        array(
                            'name' => '~',
                            'type' => 'taxonomy',
                            'description' => '',
                            'meta_key' => 'medafi_tax_only'
                        )
                    )
                )
            );
        } else
        {
            
            $blocks = self::get_fiters_posts_datablocks(self::get_fiters_posts($widget_options[self::$slug_cat]));
            
        }


        $page_meta_data_filter = self::get_page_mdf_data();
        //***
        if (!empty($blocks))
        {
            wp_enqueue_style('meta_data_filter_front', self::get_application_uri() . 'css/front.css');
            //http://wordpress.stackexchange.com/questions/34754/add-title-attribute-to-stylesheets-with-wp-enqueue-style
            //global $wp_styles;
            //$wp_styles->add_data('meta_data_filter_front', 'property', 'stylesheet');
            //***
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core', array('jquery'));
            //wp_enqueue_script('jquery-ui-slider', array('jquery', 'jquery-ui-core'));
            //***
            $counter_block = 0;
            foreach ($blocks as $filter_post_id => $filter_block)
            {
                self::render_html_e(self::get_application_path() . 'views/page/draw_front_page_items.php', array(
                    'form_uniqid' => $form_uniqid,
                    'filter_block' => $filter_block,
                    'filter_post_id' => $filter_post_id,
                    'page_meta_data_filter' => $page_meta_data_filter,
                    'slug' => $widget_options['meta_data_filter_slug'],
                    'widget_options' => $widget_options,
                    'counter_block' => $counter_block
                ));
                $counter_block ++;
            }
        }
    }

    public static function encode_search_get_params()
    {
        $data = array();
        parse_str($_REQUEST['vars'], $data);
		$data = MetaDataFilter::sanitize_array_r($data);
        if (!empty($data) AND is_array($data))
        {
			
			
            if (isset($data['mdf']))
            {
                if (!empty($data['mdf']))
                {
                    foreach ($data['mdf'] as $key => $value)
                    {
                        if ($value == '~')
                        {
                            unset($data['mdf'][$key]); //clearing not need data
                        }
                    }
                }
            }
        }
        if(!isset($data['mdf'])){
            $data['mdf']=array();
        }
        //fixed 24-10-2014
        //$responce = 'meta_data_filter_bool=' . $data['meta_data_filter_bool'] . '&mdf_tax_bool=' . $data['mdf_tax_bool'] . '&' . self::$slug_cat . '=' . $data[self::$slug_cat];
      
		// gtranslate correct response content-type
		header('Content-Type: text/plain');	
		
		if (isset($data[self::$slug_cat]))
        {
            $responce = 'mdf_cat=' . $data[self::$slug_cat];
        } else
        {
            $responce = 'mdf_cat=-1';
        }

        //reset
        if ($_REQUEST['mode'] == 'reset')
        {
            //shortcodes always reset by ajax
            if ($_REQUEST['is_ajaxed_reset'])
            {
                if ($_REQUEST['type'] == 'shortcode')
                {
                    $shortcode_id = sanitize_title($_REQUEST['shortcode_id']);
					echo do_shortcode('[mdf_search_form id="' . esc_attr($shortcode_id) . '"]');
                    exit;
                }
            }

            //***

            $tmp = array();
            $tmp['filter_post_blocks'] = $data['mdf']['filter_post_blocks'];
            $tmp['mdf_widget_options'] = $data['mdf']['mdf_widget_options'];
            if (isset($data['mdf']['taxonomy']))
            {
                $tmp['taxonomy'] = $data['mdf']['taxonomy'];
            } else
            {
                $tmp['taxonomy'] = array();
            }

            if (!empty($tmp['taxonomy']) AND is_array($tmp['taxonomy']))
            {
                foreach ($tmp['taxonomy'] as $h_type => $taxes)
                {
                    if (!empty($taxes) OR is_array($taxes))
                    {
                        foreach ($taxes as $tax_name => $value)
                        {
                            if ($h_type == 'select')
                            {
                                $tmp['taxonomy'][$h_type][$tax_name] = array(-1);
                            } else
                            {
                                $tmp['taxonomy'][$h_type][$tax_name] = ("");
                            }
                        }
                    }
                }
            }
            $data['mdf'] = $tmp;
        }


        //base64_
        $page_mdf = self::set_page_mdf_session(base64_encode(json_encode($data['mdf'])));
        $responce.='&page_mdf=' . $page_mdf;
        if (isset($_REQUEST['no_wp_die']))
        {
            return $responce;
        } else
        {
            wp_die($responce);
        }
    }

    //ajax auto submit mode
    public static function get_ajax_auto_recount_data()
    {
        //for qtranslate
        if (!empty($_REQUEST['mdf_front_qtrans_lang']))
        {
            if (function_exists('qtrans_enableLanguage'))
            {
                $GLOBALS['q_config']['language'] = sanitize_text_field($_REQUEST['mdf_front_qtrans_lang']);
            }
        }
        //+++
        //for WPML
        if (!empty($_REQUEST['mdf_front_wpml_lang']))
        {
            if (class_exists('SitePress'))
            {
                global $sitepress;
                $sitepress->switch_lang(sanitize_text_field($_REQUEST['mdf_front_wpml_lang']));
            }
        }
        //+++
        //when we do sort before searching
        if ((int) $_REQUEST['mdf_is_search_going'] == 0 OR ( (int) $_REQUEST['mdf_is_search_going'] == 1 AND (!isset($_REQUEST['order_by']) OR $_REQUEST['order_by']==null) ))
        {
            if ($_REQUEST['mdf_tmp_order'] != '0' AND $_REQUEST['mdf_tmp_orderby'] != '0')
            {
                $_REQUEST['order'] = self::$default_order = sanitize_text_field($_REQUEST['mdf_tmp_order']);

                $_REQUEST['order_by'] = self::$default_order_by = sanitize_text_field($_REQUEST['mdf_tmp_orderby']);
               
            }
        }

        //+++
        $_REQUEST['no_wp_die'] = TRUE;
        $_REQUEST['mdf_ajax_request'] = TRUE;
        $link_data = explode('&', self::encode_search_get_params());
        $data = array();
        if (!empty($link_data))
        {
            foreach ($link_data as $pair)
            {
                $tmp = explode('=', $pair);
                $data[$tmp[0]] = $tmp[1];
            }
        }

        //+++
        $_REQUEST['mdf_cat'] = $data['mdf_cat'];
        $_REQUEST['page_mdf'] = $data['page_mdf'];
        $slug = sanitize_text_field($_REQUEST['slug']);
        //print_r($_REQUEST);
        $mdf_data = self::get_page_mdf_data();
        //+++
        set_query_var('paged', 0);
        $_GET['slg'] = $slug;
        $_REQUEST['mdf_do_not_render_shortcode_tpl'] = TRUE;
        //***
        do_shortcode('[meta_data_filter_results]');
        $result = array();
        if ($_REQUEST['type'] == 'shortcode')
        {
			$shortcode_id = sanitize_title($_REQUEST['shortcode_id']);
			
            $result['form'] = do_shortcode('[mdf_search_form id="' . esc_attr($shortcode_id) . '"]');
        } else
        {
            ob_start();
            if(isset($_REQUEST['widget_id']) AND isset($_REQUEST['sidebar_id'])){
                self::dynamic_sidebar(sanitize_text_field($_REQUEST['widget_id']), sanitize_text_field($_REQUEST['sidebar_id']));
            }
            $result['form'] = ob_get_clean();
        }

        //return data if we use ajax content redrawing
        if ($_REQUEST['mdf_ajax_content_redraw'] AND isset($_REQUEST['shortcode_txt']))
        {
			$shortcode_txt = self::check_shortcode('products', "[" . strip_shortcodes(sanitize_text_field($_REQUEST['shortcode_txt'])) . "]");
            $result['content'] = do_shortcode($shortcode_txt);
        }
        echo json_encode($result);
        exit;
    }
	public static function check_shortcode($tag = "", $text = "") {
		$tags = array(
			'mdf_custom',
			'mdf_products',
			$tag
		);

		$pattern = get_shortcode_regex($tags);
		preg_match_all("/$pattern/", $text, $matches);
		if (isset($matches[0][0]) AND !empty($matches[0][0])) {
			return $matches[0][0];
		} else {
			return "";
		}
	}
    //need only for AJAX mode recount
    private static function dynamic_sidebar($widget_id, $index = 1)
    {

        global $wp_registered_sidebars, $wp_registered_widgets;

        if (is_int($index))
        {
            $index = "sidebar-$index";
        } else
        {
            $index = sanitize_title($index);
            foreach ((array) $wp_registered_sidebars as $key => $value)
            {
                if (sanitize_title($value['name']) == $index)
                {
                    $index = $key;
                    break;
                }
            }
        }


        //+++ CODE EDITION
        //$sidebars_widgets = wp_get_sidebars_widgets();
        $sidebars_widgets = array();
        $sidebars_widgets[$index] = array();
        $sidebars_widgets[$index][] = $widget_id;
        //+++

        if (empty($wp_registered_sidebars[$index]) || empty($sidebars_widgets[$index]) || !is_array($sidebars_widgets[$index]))
        {
            return false;
        }

        $sidebar = $wp_registered_sidebars[$index];

        $did_one = false;
        foreach ((array) $sidebars_widgets[$index] as $id)
        {

            if (!isset($wp_registered_widgets[$id]))
                continue;

            $params = array_merge(
                    array(array_merge($sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']))), (array) $wp_registered_widgets[$id]['params']
            );

            // Substitute HTML id and class attributes into before_widget
            $classname_ = '';
            foreach ((array) $wp_registered_widgets[$id]['classname'] as $cn)
            {
                if (is_string($cn))
                    $classname_ .= '_' . $cn;
                elseif (is_object($cn))
                    $classname_ .= '_' . get_class($cn);
            }
            $classname_ = ltrim($classname_, '_');
            $params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

            $params = apply_filters('dynamic_sidebar_params', $params);

            $callback = $wp_registered_widgets[$id]['callback'];

            do_action('dynamic_sidebar', $wp_registered_widgets[$id]);

            if (is_callable($callback))
            {
                call_user_func_array($callback, $params);
                $did_one = true;
            }
        }

        return $did_one;
    }

    public static function get_fiters_posts($cat_id)
    {
        $args = array(
            'post_type' => self::$slug,
            'orderby' => self::$slug_cat,
            'numberposts' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => self::$slug_cat,
                    'field' => 'id',
                    'terms' => $cat_id,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'sequence',
                    'value' => null,
                    'compare' => '!=',
                    'type' => 'BOOLEAN'
                )
            ),
            'meta_key' => 'sequence',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'fields' => 'ids',
            'suppress_filters' => true
        );


        //WPML compatibility
        if (class_exists('SitePress'))
        {
            global $sitepress;
            $sitepress->switch_lang($sitepress->get_default_language());
        }
        $res = get_posts($args);
        if (class_exists('SitePress'))
        {
            $sitepress->switch_lang();
        }

        return $res;
    }

    public static function get_fiters_posts_datablocks($post_ids)
    {
        $blocks = array();
        if (!empty($post_ids))
        {
            foreach ($post_ids as $id)
            {
                $res = array();
                $res['name'] = get_the_title($id);
                $res['items'] = self::get_html_items($id);
                $blocks[$id] = $res;
            }
        }

        return $blocks;
    }

    public static function data_meta_box()
    {
        global $post;
        wp_enqueue_script('meta_data_filter_admin', self::get_application_uri() . 'js/admin.js', array('jquery'));
        self::render_html_e(self::get_application_path() . 'views/page/data_meta_box.php', array(
            'html_items' => self::get_html_items($post->ID),
            'cat_id' => get_post_meta($post->ID, self::$slug_cat, TRUE),
            'post_id' => $post->ID
        ));
    }

    //widget, sort by functionality
    public static function get_html_sliders_by_filter_cat($filter_cat)
    {
        $posts = self::get_fiters_posts($filter_cat);
        $res = array();
        //+++
        if (!empty($posts))
        {
            foreach ($posts as $post_id)
            {
                $fields = self::get_html_items($post_id);
                if (!empty($fields))
                {
                    if (is_array($fields))
                    {
                        foreach ($fields as $key => $value)
                        {
                            if ($value['type'] == 'slider')
                            {
                                $res[$key . '~' . $post_id] = $value['name'] . ' (' . $value['slider'] . ')';
                            }
                        }
                    }
                }
            }
        }

        return $res;
    }

}
