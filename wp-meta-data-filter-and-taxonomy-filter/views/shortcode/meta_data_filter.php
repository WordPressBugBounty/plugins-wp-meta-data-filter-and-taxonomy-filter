<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $wp_query;

if (!isset($_REQUEST['meta_data_filter_works']) AND $wp_query->is_main_query()) {//anti endless loop
    $_REQUEST['meta_data_filter_works'] = 1; //anti endless loop
    wp_reset_query();
    wp_reset_postdata();
    //+++
    $meta_query_array = array();
    $filter_post_blocks_data = array();

    //+++
    if (self::is_page_mdf_data()) {
        $page_meta_data_filter = self::get_page_mdf_data();

        //+++
        if (!empty($page_meta_data_filter)) {
            $taxonomies = array();
            if (isset($page_meta_data_filter['taxonomy'])) {
                $taxonomies = $page_meta_data_filter['taxonomy'];
                unset($page_meta_data_filter['taxonomy']);
            }
            //+++
            list($meta_query_array, $filter_post_blocks_data, $widget_options) = MetaDataFilterHtml::get_meta_query_array($page_meta_data_filter);
            $meta_data_filter_bool = isset($_GET['meta_data_filter_bool']) ? MetaDataFilterCore::escape($_GET['meta_data_filter_bool']) : 'AND';
        }
    } else {
        return;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //Taxonomies in process ...

    $tax_query_array = MetaDataFilterHtml::get_tax_query_array($taxonomies);
    $mdf_tax_bool = 'AND';
    if (!empty($tax_query_array)) {
        $mdf_tax_bool = isset($_GET['mdf_tax_bool']) ? MetaDataFilterCore::escape($_GET['mdf_tax_bool']) : 'AND';
    }
    //*** additional taxonomies for Pre-sale question:
    //I have woocommerce with brands plugin installed, each brand page shows the products of this brand,
    //can this plugin filter those products based on category?
    if (isset($widget_options['additional_taxonomies']) AND!empty($widget_options['additional_taxonomies'])) {
        MetaDataFilter::add_additional_taxonomies($widget_options['additional_taxonomies']);
    }
    //***
    $tax_query_array = apply_filters('mdf_filter_taxonomies', $tax_query_array); //for 3 part scripts
    $tax_query_array = apply_filters('mdf_filter_taxonomies2', $tax_query_array); //for 3 part scripts
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    if (isset($_REQUEST['mdf_cat']) AND $_REQUEST['mdf_cat'] > 0) {
        $tmp_add = true;

        if (empty($meta_query_array)) {
            $tmp_add = true;
        }

        if ($widget_options['hide_meta_filter_values']) {
            $tmp_add = false;
        }

        if ($tmp_add) {
            if ((int) $_REQUEST['mdf_cat'] !== 0) {//fix for searching by title by shortcode 28-01-2015
                $meta_query_array[] = array(
                    'key' => "meta_data_filter_cat",
                    'value' => (int) $_REQUEST['mdf_cat'],
                    'compare' => '='
                );
            }
        }
    }

    if (!empty($meta_query_array)) {
        $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), $meta_query_array);
    }

    //for WOOCOMMERCE hidden products ***
    if ($_GET['slg'] == 'product' AND class_exists('WooCommerce')) {

        if (version_compare(WOOCOMMERCE_VERSION, '3.0', '>=')) {
            $tax_query_array[] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => array('exclude-from-catalog'),
                'operator' => 'NOT IN',
            );
        } elseif (version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
            $buffer = array(
                'key' => '_visibility',
                'value' => array('catalog', 'visible'),
                'compare' => 'IN'
            );
            if (empty($meta_query_array)) {
                $meta_query_array = array_merge(array('relation' => $meta_data_filter_bool), $buffer);
            } else {
                $meta_query_array[] = $buffer;
            }
        }



        //for out stock products
        if (MetaDataFilterCore::get_setting('exclude_out_stock_products')) {
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
    $meta_query_array[] = array(
        'key' => 'mdf_hide_post',
        'value' => 'out',
        'compare' => 'NOT EXISTS'
    );
    //***

    if (!empty($tax_query_array)) {
        $tax_query_array = array_merge(array('relation' => $mdf_tax_bool), $tax_query_array);
    }
    //***

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    if (isset($_GET['mdf_page_num'])) {
        $paged = MetaDataFilterCore::escape($_GET['mdf_page_num']);
    }

    $args = array(
        'post_type' => MetaDataFilterCore::escape($_GET['slg']),
        'ignore_sticky_posts' => MetaDataFilterCore::get_setting('ignore_sticky_posts'),
        'meta_query' => $meta_query_array,
        'tax_query' => $tax_query_array,
        'post_status' => array('publish'),
        'paged' => $paged
    );

    if (MetaDataFilterCore::get_setting('results_per_page')) {
        $args['posts_per_page'] = MetaDataFilterCore::get_setting('results_per_page');
    }

    //WPML compatibility
    if (class_exists('SitePress')) {
        $args['lang'] = ICL_LANGUAGE_CODE;
    }

    //print_r($args);
//***
    $order_by_array = MetaDataFilterCore::$allowed_order_by;

    if (isset($_REQUEST['order_by'])) {
        if (in_array(sanitize_text_field($_REQUEST['order_by']), $order_by_array)) {
            $args['orderby'] = sanitize_text_field($_REQUEST['order_by']);
        } else {
            $args['meta_key'] = sanitize_text_field($_REQUEST['order_by']);
            $args['orderby'] = 'meta_value_num meta_value';
        }
    } else {
        if (in_array(self::$default_order_by, $order_by_array)) {
            $args['orderby'] = self::$default_order_by;
        } else {
            $args['meta_key'] = self::$default_order_by;
            $args['orderby'] = 'meta_value_num meta_value';
        }
    }

    $args['order'] = self::$default_order;
    if (isset($_REQUEST['order'])) {
        $args['order'] = sanitize_text_field($_REQUEST['order']);
    }
//***
    $args = apply_filters('meta_data_filter_args', $args);
    if (!isset($_REQUEST['mdf_get_query_args_only'])) {
        global $wpdb;

        //fix 04-08-2014 for reseting in case tax is empty
        if ($widget_options['meta_data_filter_cat'] == -1
                AND empty($args['tax_query'])
                AND!(defined('DOING_AJAX') && DOING_AJAX)) {
            if (isset($widget_options['reset_link']) AND!empty($widget_options['reset_link'])) {
                ?>
                <script>
                    //DYNAMIC SCRIPT DEPENDING OF SETTINGS
                    window.location.href = '<?php echo esc_url_raw($widget_options['reset_link']) ?>';
                </script>
                <?php
                exit;
            }
        }


        $wp_query = new WP_Query($args);
    }

    $_REQUEST['meta_data_filter_count'] = $wp_query->found_posts;
    if ($wp_query->found_posts == 1 AND 'page' == $wp_query->posts[0]->post_type) {
        $_REQUEST['meta_data_filter_count'] = 0;
    }

    $_REQUEST['meta_data_filter_args'] = $args;
    //***

    $output_tpl = "";
    $settings = MetaDataFilter::get_settings();
    if (empty($settings['output_tpl'])) {
        $output_tpl = 'search';
    } else {
        $output_tpl = $settings['output_tpl'];
    }

    if (isset($widget_options['search_result_tpl']) AND!empty($widget_options['search_result_tpl'])) {
        $output_tpl = $widget_options['search_result_tpl'];
    }
    //***
    if (!isset($_REQUEST['mdf_do_not_render_shortcode_tpl']) AND $output_tpl != 'self') {

        $tpl = explode('-', $output_tpl);
        if (!isset($tpl[1])) {
            $tpl[1] = '';
        }

        get_template_part($tpl[0], $tpl[1]);
    }

    //***
    wp_reset_postdata();
}

