<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MetaDataFilterHtml extends MetaDataFilterCore
{

    public static function init()
    {
        parent::init();
        add_action('wp_ajax_meta_data_filter_add_item_to_data_group', array(__CLASS__, 'add_item_to_data_group'));
        self::$items_types = array(
            'checkbox' => esc_html__("checkbox", 'meta-data-filter'),
            'select' => esc_html__("drop-down", 'meta-data-filter'),
            'slider' => esc_html__("range slider", 'meta-data-filter'),
            'taxonomy' => esc_html__("taxonomy", 'meta-data-filter'),
            'textinput' => esc_html__("textinput", 'meta-data-filter'),
            'calendar' => esc_html__("calendar", 'meta-data-filter'),
            'range_select' => esc_html__("Range select", 'meta-data-filter'),
            'label' => esc_html__("Label", 'meta-data-filter'),
            'by_author' => esc_html__("Search by author", 'meta-data-filter'),
            'map' => esc_html__("map", 'meta-data-filter')
        );

        self::$tax_items_types = array(
            'select' => esc_html__("drop-down", 'meta-data-filter'),
            'checkbox' => esc_html__("checkbox", 'meta-data-filter'),
            'label' => esc_html__("Label", 'meta-data-filter'),
        );
    }
//ajax
    public static function add_item_to_data_group()
    {
        wp_die(self::render_html(self::get_application_path() . 'views/add_item_to_data_group.php'));
    }

    //works perfectly but is slow a little
    public static function get_item_posts_count($page_meta_data_filter, $meta_key, $meta_value, $slug, $html_type)
    {
        $post_count = 0;
        global $wpdb;
        //+++

        if (self::is_page_mdf_data())
        {

            list($meta_query_array, $filter_post_blocks_data, $widget_options) = self::get_meta_query_array($page_meta_data_filter);
            $show_items_count_dynam = isset($widget_options['show_items_count_dynam']) ? $widget_options['show_items_count_dynam'] : 0;


            //if count tax items we need to ask next param
            if ($html_type == 'tax_item')
            {
                $show_items_count_dynam = isset($widget_options['taxonomies_options_post_recount_dyn']) ? $widget_options['taxonomies_options_post_recount_dyn'] : 0;
            }

            if ($show_items_count_dynam == 1)
            {
                //process ******************************************************
                $in_array = false;
                $pos_in_array = 0; //to remove item if it is in $meta_query_array
                if (!empty($meta_query_array))
                {
                    foreach ($meta_query_array as $k => $value)
                    {
                        if ($value['key'] == $meta_key)
                        {
                            $in_array = true;
                            $pos_in_array = $k;
                            break;
                        }
                    }
                }
                //+++
                if ($html_type != 'tax_item')
                {
                    switch ($html_type)
                    {
                        case 'checkbox':
                            if (!$in_array)
                            {
                                $meta_query_array[] = array(
                                    'key' => $meta_key,
                                    'value' => 1,
                                    'compare' => '='
                                );
                            }
                            break;
                        case 'select':
                            //we have to exclude all siblings keys in current select
                            if ($in_array)
                            {
                                unset($meta_query_array[$pos_in_array]);
                            }


                            //look in range (0-500, 501-1000, 1001-2000 and etc...)
                            $tmp = explode('-', $meta_value);
                            $is_range = MDTF_HELPER::is_slider_range_value($tmp);
                            //+++
                            if ($is_range)
                            {
                                if ($tmp[1] === 'i')
                                {
                                    $tmp[1] = self::$max_int;
                                }

                                $meta_query_array[] = array(
                                    'key' => $meta_key,
                                    'value' => array(floatval($tmp[0]), floatval($tmp[1])),
                                    'compare' => "BETWEEN",
                                    'type' => "DECIMAL"
                                );
                            } else
                            {
                                //and add itself
                                $meta_query_array[] = array(
                                    'key' => $meta_key,
                                    'value' => $meta_value,
                                    'compare' => '='
                                );
                            }


                            break;
                        case 'slider':
                            break;
                        default:
                            break;
                    }
                }


                //+++


                $tax_query_array = array();
                //+++
                $mdf_tax_bool = isset($_REQUEST['mdf_tax_bool']) ? sanitize_text_field($_REQUEST['mdf_tax_bool']) : 'AND';

                if (isset($page_meta_data_filter['taxonomy']))
                {
                    $tax_query_array = self::get_tax_query_array($page_meta_data_filter['taxonomy']);
                }
                $tax_query_array = apply_filters('mdf_filter_taxonomies2', $tax_query_array);
                //+++
                //AND empty($tax_query_array) - if only taxonomies in search form
                if (isset($_REQUEST['mdf_cat']))
                {
                    $tmp_add = true;

                    if (empty($meta_query_array))
                    {
                        $tmp_add = true;
                    }

                    $hide_meta_filter_values = isset($widget_options['hide_meta_filter_values']) ? $widget_options['hide_meta_filter_values'] : false;
                    if ($hide_meta_filter_values)
                    {
                        $tmp_add = false;
                    }

                    if ($tmp_add)
                    {
                        if (isset($_REQUEST['mdf_cat']) AND $_REQUEST['mdf_cat'] > 0)
                        {
                            $meta_query_array[] = array(
                                'key' => "meta_data_filter_cat",
                                'value' => (int)$_REQUEST['mdf_cat'],
                                'compare' => '='
                            );
                        }
                    }
                }


                $meta_data_filter_bool = isset($_REQUEST['meta_data_filter_bool']) ? sanitize_text_field($_REQUEST['meta_data_filter_bool']) : 'AND';
                if (!empty($meta_query_array))
                {
                    $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), $meta_query_array);
                }

                //for WOOCOMMERCE hidden products ***
                if ($slug == 'product' AND class_exists('WooCommerce'))
                {
                    if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                        $tax_query_array[]=array(
                            'taxonomy' => 'product_visibility',
                            'field' => 'name',
                            'terms' => array('exclude-from-catalog'),
                            'operator' => 'NOT IN',
                        );
                        
                    }elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
                           $buffer = array(
                                'key' => '_visibility',
                                'value' => array('catalog', 'visible'),
                                'compare' => 'IN'
                            );
                            if (empty($meta_query_array))
                            {
                                $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), $buffer);
                            } else
                            {
                                $meta_query_array[] = $buffer;
                            }
                    }
                   

                    //for out stock products
                    if (self::get_setting('exclude_out_stock_products'))
                    {
                        $buffer = array(
                            'key' => '_stock_status',
                            'value' => array('instock'),
                            'compare' => 'IN'
                        );
                        $meta_query_array[] = $buffer;
                    }
                    //***
                }

                //+++
                //Trick - how to hide post from search
                if (empty($meta_query_array))
                {
                    $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), array(
                        'key' => 'mdf_hide_post',
                        'value' => 'out',
                        'compare' => 'NOT EXISTS'
                    ));
                } else
                {
                    $meta_query_array[] = array(
                        'key' => 'mdf_hide_post',
                        'value' => 'out',
                        'compare' => 'NOT EXISTS'
                    );
                }

                //***



                if (!empty($tax_query_array))
                {
                    $tax_query_array = array_merge(array('relation' => $mdf_tax_bool), $tax_query_array);
                }


                $args = array(
                    'post_type' => $slug,
                    'meta_query' => $meta_query_array,
                    'tax_query' => apply_filters('mdf_dyn_tax_recount_args', $tax_query_array),
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
                if (class_exists('SitePress'))
                {
                    $args['lang'] = ICL_LANGUAGE_CODE;
                }

                $counter_obj = new WP_QueryMDFCounter($args);
                $post_count = $counter_obj->post_count;
            }
        }

        //****************
        if ($html_type != 'tax_item')
        {

            if (!self::is_page_mdf_data() OR ( isset($widget_options['show_items_count_dynam']) AND $widget_options['show_items_count_dynam'] == 0))
            {
                //if we are not on search page or NO show_items_count_dynam
                $meta_query_array = array();
                switch ($html_type)
                {
                    case 'slider':
                        //http://codex.wordpress.org/Class_Reference/WP_Meta_Query
                        /*
                          $meta_query_array[] = array(
                          'key'=>$meta_key,
                          //'value'=>array($meta_value[0], $meta_value[1]),
                          'value'=>array( $meta_value[0], self::$max_int ),
                          'type'=>'DECIMAL',
                          'compare'=>'BETWEEN'
                          );
                         */
                        $meta_query_array[] = self::range_slider_multicontroller($slug, $meta_key, $meta_value[0], self::$max_int);
                        break;
                        case 'range_select':
                        //http://codex.wordpress.org/Class_Reference/WP_Meta_Query
                        /*
                          $meta_query_array[] = array(
                          'key'=>$meta_key,
                          //'value'=>array($meta_value[0], $meta_value[1]),
                          'value'=>array( $meta_value[0], self::$max_int ),
                          'type'=>'DECIMAL',
                          'compare'=>'BETWEEN'
                          );
                         */
                        $meta_query_array[] = self::range_slider_multicontroller($slug, $meta_key, $meta_value[0], self::$max_int);
                        break;

                    case 'select':
                        $tmp = explode('-', $meta_value);
                        $is_range = MDTF_HELPER::is_slider_range_value($tmp);
                        //+++
                        if ($is_range)
                        {
                            if ($tmp[1] === 'i')
                            {
                                $tmp[1] = self::$max_int;
                            }
                            $meta_query_array[] = array(
                                'key' => $meta_key,
                                'value' => array(floatval($tmp[0]), floatval($tmp[1])),
                                'compare' => "BETWEEN",
                                'type' => "DECIMAL"
                            );
                        } else
                        {
                            $meta_query_array[] = array(
                                'key' => "$meta_key",
                                'value' => "$meta_value",
                                'compare' => '='
                            );
                        }

                        break;
                        case 'by_author':
                                        if (!empty($value))
                                        {
                                            $_REQUEST['mdf_search_by_author'] =$value;
                                        } 
                        break;
                    default:
                        $meta_query_array[] = array(
                            'key' => "$meta_key",
                            'value' => "$meta_value",
                            'compare' => '='
                        );

                        break;
                }
               $tax_query_array = apply_filters('mdf_filter_taxonomies', array());
                $tax_query_array = apply_filters('mdf_filter_taxonomies2', $tax_query_array);

                //for WOOCOMMERCE hidden products ***
                if ($slug == 'product' AND class_exists('WooCommerce'))
                {
                    if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
                        $tax_query_array[]=array(
                            'taxonomy' => 'product_visibility',
                            'field' => 'name',
                            'terms' => array('exclude-from-catalog'),
                            'operator' => 'NOT IN',
                        );
                        
                    }elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
                           $buffer = array(
                               'key' => '_visibility',
                               'value' => array('catalog', 'visible'),
                               'compare' => 'IN'
                           );
                           if (empty($meta_query_array))
                           {
                               $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), $buffer);
                           } else
                           {
                               $meta_query_array[] = $buffer;
                           }
                    }
                    
                    //for out stock products
                    if (self::get_setting('exclude_out_stock_products'))
                    {
                        $buffer = array(
                            'key' => '_stock_status',
                            'value' => array('instock'),
                            'compare' => 'IN'
                        );
                        $meta_query_array[] = $buffer;
                    }
                    //***
                }
                //+++
                //Trick - how to hide post from search
                if (empty($meta_query_array))
                {
                    $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), array(
                        'key' => 'mdf_hide_post',
                        'value' => 'out',
                        'compare' => 'NOT EXISTS'
                    ));
                } else
                {
                    $meta_query_array[] = array(
                        'key' => 'mdf_hide_post',
                        'value' => 'out',
                        'compare' => 'NOT EXISTS'
                    );
                }

                //***


                


                //count data for selected mdf_cat
                if (isset($_REQUEST['meta_data_filter_cat_form']))
                {
                    $tmp_add = true;

                    if (empty($meta_query_array))
                    {
                        $tmp_add = false;
                    }

                    if ($tmp_add)
                    {
                        if (isset($_REQUEST['mdf_cat']) AND $_REQUEST['mdf_cat'] > 0)
                        {
                            $meta_query_array[] = array(
                                'key' => "meta_data_filter_cat",
                                'value' => (isset($_REQUEST['mdf_cat']) ? $_REQUEST['mdf_cat'] : sanitize_text_field($_REQUEST['meta_data_filter_cat_form'])),
                                'compare' => '='
                            );
                        } else
                        {
                            //fixed 25052015
                            if ($_REQUEST['meta_data_filter_cat_form'] > 0)
                            {
                                $meta_query_array[] = array(
                                    'key' => "meta_data_filter_cat",
                                    'value' => sanitize_text_field($_REQUEST['meta_data_filter_cat_form']),
                                    'compare' => '='
                                );
                            }
                        }
                    }
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
                if (class_exists('SitePress'))
                {
                    $args['lang'] = ICL_LANGUAGE_CODE;
                }



                $counter_obj = new WP_QueryMDFCounter($args);
                $post_count = $counter_obj->post_count;
            }
        }

        return $post_count;
    }

    public static function get_meta_query_array($page_meta_data_filter)
    {
        if (isset($_GLOBALS['MDF_META_QUERY_ARRAY']))
        {
            return $_GLOBALS['MDF_META_QUERY_ARRAY']; //query array is cached already
        }
        //+++
        $meta_query_array = array();
        if (!empty($page_meta_data_filter))
        {
            //here we get all data about meta keys in constructor!!!
            $filter_post_blocks = array();
            if (isset($page_meta_data_filter['filter_post_blocks']))
            {
                $filter_post_blocks = $page_meta_data_filter['filter_post_blocks'];
                unset($page_meta_data_filter['filter_post_blocks']);
                unset($page_meta_data_filter['filter_post_blocks_toggles']);
                unset($page_meta_data_filter['filter_post_blocks_toggles_tax']);
            }
            $filter_post_blocks_data = array();
            if (!empty($filter_post_blocks) AND is_array($filter_post_blocks))
            {
                foreach ($filter_post_blocks as $p_id)
                {
                    $html_items = MetaDataFilterPage::get_html_items($p_id);
                    if (is_array($html_items))
                    {
                        $filter_post_blocks_data = array_merge($filter_post_blocks_data, $html_items);
                    }
                }
            }
            //+++get widget options
            $widget_options = array();
            if (isset($page_meta_data_filter['mdf_widget_options']))
            {
                $widget_options = $page_meta_data_filter['mdf_widget_options'];
                unset($page_meta_data_filter['mdf_widget_options']);
            }
            //+++
            if (isset($page_meta_data_filter['taxonomy']))
            {
                unset($page_meta_data_filter['taxonomy']);
            }
            $meta_data_filter_bool = isset($_REQUEST['meta_data_filter_bool']) ? sanitize_text_field($_REQUEST['meta_data_filter_bool']) : 'AND';
            //+++
            if (is_array($page_meta_data_filter))
            {
                foreach ($page_meta_data_filter as $key => $value)
                {
                    if ($value AND ! empty($value))
                    {
                        $html_item_data = array();
                        if (isset($filter_post_blocks_data[$key]))
                        {
                            $html_item_data = $filter_post_blocks_data[$key];
                        }
                        //+++
                        $search_key = $key;
                        if (isset($html_item_data['is_reflected']) AND $html_item_data['is_reflected'] == 1)
                        {
                            $search_key = $html_item_data['reflected_key'];
                        }

                        if (!empty($html_item_data))
                        {
                            switch ($html_item_data['type'])
                            {
                                case 'textinput':
                                    if (!empty($value))
                                    {
                                        $textinput_target = (isset($html_item_data['textinput_target']) ? $html_item_data['textinput_target'] : 'self');
                                        $textinput_mode = (isset($html_item_data['textinput_mode']) ? $html_item_data['textinput_mode'] : 'like');
                                        $compare = 'LIKE';
                                        if ($textinput_mode == 'exact')
                                        {
                                            $compare = '=';
                                        }
                                        
                                        //fix  for  multiple spaces 
                                        $value =preg_replace("/\s{2,}/",' ',trim($value));
                                               
 
                                        switch ($textinput_target)
                                        {
                                            case 'self':
                                                $meta_query_array[] = array(
                                                    'key' => "$search_key",
                                                    'value' => "$value",
                                                    'compare' => $compare
                                                );
                                                break;
                                            case 'title':
                                                $_REQUEST['mdf_post_title_request'] = $compare . '^' . $search_key . '^' . $value;
                                                break;
                                            case 'content':
                                                $_REQUEST['mdf_post_content_request'] = $compare . '^' . $search_key . '^' . $value;
                                                break;
                                            case 'title_or_content':
                                                $_REQUEST['mdf_post_title_or_content_request'] = $compare . '^' . $search_key . '^' . $value;
                                                break;
                                            case 'title_and_content':
                                                $_REQUEST['mdf_post_title_and_content_request'] = $compare . '^' . $search_key . '^' . $value;
                                                break;
                                        }
                                    }

                                    break;
                                case 'by_author':
                                        if (!empty($value))
                                        {
                                            $_REQUEST['mdf_search_by_author'] =$value;
                                        } 
                                    break;

                                case 'calendar':

                                    $from = 0;
                                    $to = 0;

                                    if (!empty($value) AND is_array($value) AND ( !empty($value['from']) OR ! empty($value['to'])))
                                    {
                                        $from = intval($value['from']);
                                        $to = intval($value['to']);
                                    }

                                    if ($from > 0 OR $to > 0)
                                    {

                                        if ($from == 0 AND $to > 0)
                                        {
                                            $meta_query_array[] = array(
                                                'key' => $search_key . "_to",
                                                'value' => $to,
                                                'compare' => "<="
                                            );
                                        }

                                        //+++

                                        if ($from > 0 AND $to == 0)
                                        {
                                            $meta_query_array[] = array(
                                                'key' => $search_key . "_from",
                                                'value' => $from,
                                                'compare' => ">="
                                            );
                                        }

                                        //+++

                                        if ($from == $to)
                                        {
                                            $meta_query_array[] = array(
                                                'key' => $search_key . "_from",
                                                'value' => $from,
                                                'compare' => "<="
                                            );

                                            $meta_query_array[] = array(
                                                'key' => $search_key . "_to",
                                                'value' => $to,
                                                'compare' => ">="
                                            );
                                        }

                                        //+++
                                        //fixed 08-10-2014
                                        if ($from < $to AND $from > 0 AND $to > 0)
                                        {
                                            /*
                                              $meta_query_array[] = array(
                                              'key' => $search_key . "_from",
                                              'value' => $from,
                                              'compare' => ">="
                                              );

                                              $meta_query_array[] = array(
                                              'key' => $search_key . "_to",
                                              'value' => $to,
                                              'compare' => "<="
                                              );
                                             */

                                            $meta_query_array[] = array(
                                                'key' => $search_key . "_from",
                                                'value' => array($from, $to),
                                                'compare' => "BETWEEN"
                                            );
                                        }
                                    }

                                    break;

                                case 'slider':

                                    //slider
                                    if ($meta_data_filter_bool != 'OR')
                                    {//IMPORTANT! THIS IS FUSE! WITHOUT this: MODE 'OR' BREAK SQL EXEC
                                        if (!isset($html_item_data['slider_range_value']))
                                        {
                                            $html_item_data['slider_range_value'] = 0;
                                        }
                                        if ($html_item_data['slider_range_value'] == 1)
                                        {
                                            //double value slider slider
                                            list($single_val, $f) = explode('^', $value);

                                            if ($single_val != 0)
                                            {
                                                $meta_query_array[] = array(
                                                    'key' => $search_key,
                                                    'value' => $single_val,
                                                    'type' => 'DECIMAL',
                                                    'compare' => '<='
                                                );

                                                $meta_query_array[] = array(
                                                    'key' => $search_key . '_to',
                                                    'value' => $single_val,
                                                    'type' => 'DECIMAL',
                                                    'compare' => '>='
                                                );
                                            }

                                            //print_r($meta_query_array);exit;
                                        } else
                                        {
                                            //usual 1 value slider
                                            if(!is_string($value)){
                                                    $value="1^100";
                                            }
                                            $min_max=explode('^', $value,2);
                                            if(count($min_max)<1){
                                                $min_max=array(1,100);
                                            }
                                            list($s, $f) = $min_max;
                                            //+++
                                            if(empty($html_item_data['slider']) OR !is_string($html_item_data['slider'])){
                                                    $html_item_data['slider']="1^100";
                                            }
                                            list($min, $max) = explode('^', $html_item_data['slider']);
                                            if (floatval($max) === floatval($s))
                                            {//to make range +
                                                $f = self::$max_int;
                                            }

                                            if (floatval($f) === floatval($max))
                                            {
                                                $f = self::$max_int;
                                            }
                                            //+++
                                            //http://codex.wordpress.org/Class_Reference/WP_Meta_Query
                                            /*
                                              $meta_query_array[] = array(
                                              'key'=>$search_key,
                                              'value'=>array( (float) $s, (float) $f ),
                                              'type'=>'DECIMAL',
                                              'compare'=>'BETWEEN'
                                              );
                                             */

                                            $meta_query_array[] = self::range_slider_multicontroller($widget_options['slug'], $search_key, (float) $s, (float) $f);
                                        }
                                    }
                                    break;

                                case 'select':
                                    if (!isset($html_item_data['select_range_value']))
                                    {
                                        $html_item_data['select_range_value'] = 0;
                                    }

                                    if ($html_item_data['select_range_value'] == 1)
                                    {
                                        //look in range (0-500, 501-1000, 1001-2000 and etc...)
                                        list($from, $to) = explode('-', trim($value));
                                        $from=trim($from);
                                        $to=trim($to);

                                        //for infinity searching
                                        if ($to === 'i')
                                        {
                                            $to = self::$max_int;
                                        }
                                        $meta_query_array[] = array(
                                            'key' => $search_key,
                                            'value' => array(floatval($from), floatval($to)),
                                            'compare' => "BETWEEN",
                                            'type' => "DECIMAL"
                                        );
                                    } else
                                    {
                                        if ($value != '~')
                                        {
                                            $meta_query_array[] = array(
                                                'key' => "$search_key",
                                                'value' => "$value",
                                                'compare' => '='
                                            );
                                        }
                                    }


                                    //~ - mean ANY in select and checkbox

                                    break;
                                case 'range_select':
                                    $from = 0;
                                    $to = 0;
                                    if (!empty($value) AND is_array($value) AND ( !empty($value['from']) OR ! empty($value['to'])))
                                    {
                                        $from = (isset($value['from']))?floatval($value['from']):0;
                                        $to = (isset($value['to']))?floatval($value['to']):0;
                                    }
                                     $meta_query_array[] = array(
                                            'key' => $search_key,
                                            'value' => array(floatval($from), floatval($to)),
                                            'compare' => "BETWEEN",
                                            'type' => "DECIMAL"
                                        );
                                    
                                    break;
                                default:
                                    //checkbox
                                    if ($value != '~')
                                    {
                                        $meta_query_array[] = array(
                                            'key' => "$search_key",
                                            'value' => "$value",
                                            'compare' => '='
                                        );
                                    }
                                    //~ - mean ANY in select and checkbox
                                    break;
                            }
                        }
                    }
                }
            }
        }
        //lets cache it
        $_GLOBALS['MDF_META_QUERY_ARRAY'] = array($meta_query_array, $filter_post_blocks_data, $widget_options);
        return $_GLOBALS['MDF_META_QUERY_ARRAY'];
    }

    public static function get_tax_query_array($taxonomies)
    {

        $use_cache = true;
        if (isset($_GLOBALS['MDF_TAX_USE_CACHE_ARRAY']))
        {//uses for get_tax_count in index.php
            $use_cache = $_GLOBALS['MDF_TAX_USE_CACHE_ARRAY']; //uses for get_tax_count in index.php
        }
        if (isset($_GLOBALS['MDF_TAX_QUERY_ARRAY']) AND ! empty($_GLOBALS['MDF_TAX_QUERY_ARRAY']) AND $use_cache)
        {
            return $_GLOBALS['MDF_TAX_QUERY_ARRAY']; //tax query array is cached already
        }
        //+++
        $tax_query_array = array();
        if (!empty($taxonomies))
        {
            if (isset($taxonomies['select']) AND ! empty($taxonomies['select']))
            {
                foreach ($taxonomies['select'] as $tax_name => $terms_ids)
                {
                    if (!empty($terms_ids) AND is_array($terms_ids))
                    {
                        foreach ($terms_ids as $k => $val)
                        {
                            if ($val == -1 OR $val == 0)
                            {
                                unset($terms_ids[$k]);
                            }
                        }
                        //+++
                        if (!empty($terms_ids))
                        {
                            $tax_query_array[] = array(
                                'taxonomy' => $tax_name,
                                'field' => 'term_id',
                                'terms' => end($terms_ids),
                                    //'operator' => 'AND',//ATTENTION
                                    //'include_children' => false
                            );
                        }
                    }
                }
            }
//+++
            if (isset($taxonomies['checkbox']) AND ! empty($taxonomies['checkbox']))
            {
                foreach ($taxonomies['checkbox'] as $tax_name => $terms_ids)
                {
                    if (!empty($terms_ids) AND is_array($terms_ids))
                    {
                        /*
                          foreach ($terms_ids as $k => $val) {
                          if ($val == -1 OR $val == 0) {
                          unset($terms_ids[$k]);
                          }
                          }
                         *
                         */
//+++
//lets check for childs and if we them has exlude parents
                        foreach ($terms_ids as $k => $term_id)
                        {
                            $childs_ids = get_term_children($term_id, $tax_name);
                            if (is_a($childs_ids, 'WP_Error'))
                            {
                                die(print_r($childs_ids, true));
                            }
                            if (array_intersect($childs_ids, $terms_ids))
                            {
                                unset($terms_ids[$k]);
                            }
                        }
                        shuffle($terms_ids); //let keys begin from 0
//+++
                        if (!empty($terms_ids))
                        {
                            $tax_query_array[] = array(
                                'taxonomy' => $tax_name,
                                'field' => 'term_id',
                                'terms' => $terms_ids,
                                    //'operator' => 'AND',//ATTENTION
                                    //'include_children' => false
                            );
                        }
                    }
                }
            }
        }
		
//lets cache it
        if ($use_cache)
        {
            $_GLOBALS['MDF_TAX_QUERY_ARRAY'] = $tax_query_array;
        }

        return $tax_query_array;
    }

    public static function draw_tax_loader()
    {
        echo '<img src="' . self::get_application_uri() . 'images/tax_loader.gif" alt="loader" />';
    }

    public static function get_term_label_by_name($tax_name)
    {
        $the_tax = get_taxonomy($tax_name);
        return $the_tax->labels->name;
    }

}
