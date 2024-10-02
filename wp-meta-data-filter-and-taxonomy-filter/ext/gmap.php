<?php

class MDF_GMAP extends MetaDataFilterCore
{

    public static function init()
    {
        parent::init();
        add_action('wp_head', array(__CLASS__, 'wp_head'), 1);
        add_shortcode('mdf_gmap', array(__CLASS__, 'mdf_gmap_shortcode'));
        add_shortcode('mdf_gmap_const', array(__CLASS__, 'mdf_gmap_shortcode_const'));
    }

    public static function wp_head()
    {
        wp_enqueue_script('jquery');
        $gmap_pages = (string) self::get_setting('gmap_js_include_pages');
        $gm_key=trim((string) self::get_setting('gmap_user_api_key'));
        $gm_key_str="";
        if($gm_key){
            $gm_key_str="&key=".$gm_key;
        }
        if (!empty($gmap_pages))
        {
            $gmap_pages = explode(',', $gmap_pages);
            if ($gmap_pages[0] == -1)
            {
                wp_enqueue_script('maps.google.com', 'https://maps.google.com/maps/api/js?v=3.14&sensor=false'.$gm_key_str, array('jquery'));
            }
            if (is_page() OR is_single())
            {
                global $post;
                if (is_object($post))
                {
                    if (in_array($post->ID, $gmap_pages))
                    {
                        wp_enqueue_script('maps.google.com', 'https://maps.google.com/maps/api/js?v=3.14&sensor=false'.$gm_key_str, array('jquery'));
                    }
                }
            }
        }
    }

    //shortcode
    public static function mdf_gmap_shortcode($attributes = array())
    {
        return self::render_html(self::get_application_path() . 'views/gmap/mdf_gmap.php', $attributes);
    }

    public static function mdf_gmap_shortcode_const($atts)
    {
        extract(shortcode_atts(array(
            'page' => 1,
            'post_type' => 'post',
            'per_page' => MetaDataFilter::get_setting('results_per_page'),
            'meta_data_filter_cat' => 0,
            'taxonomies' => ''
                        ), $atts));


        $meta_query = array();
        if ($meta_data_filter_cat > 0)
        {
            $buffer = array(
                'key' => 'meta_data_filter_cat',
                'value' => $meta_data_filter_cat,
                'compare' => '='
            );
            $meta_query[] = $buffer;
        }
        //***

        if (!empty($taxonomies))
        {
            $taxonomies = MetaDataFilter::add_additional_taxonomies($taxonomies, false, '+');
        } else
        {
            $taxonomies = array();
        }

        //***
        $args = array(
            'post_type' => $post_type,
            'post_status' => array('publish'),
            'ignore_sticky_posts' => self::get_setting('ignore_sticky_posts'),
            'posts_per_page' => $per_page,
            'meta_query' => $meta_query,
            'tax_query' => $taxonomies,
            'fields' => 'ids'
        );

        //fix - rewrite if under searching
        $args = MetaDataFilter::rewrite_search_query_args($args);

        //+++
        $pp = $page;
        if (get_query_var('page'))
        {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged'))
        {
            $pp = get_query_var('paged');
        }

        if ($pp > 1)
        {
            $args['paged'] = $pp;
        } else
        {
            $args['paged'] = (isset($_REQUEST['content_redraw_page']) ? sanitize_text_field($_REQUEST['content_redraw_page']) : ((get_query_var('page')) ? get_query_var('page') : $page));
        }
        //+++
        $_REQUEST['content_redraw_page'] = $args['paged'];
        $args['posts_per_page'] = $per_page;
        if (!empty($taxonomies))
        {
            $args['tax_query'] = $taxonomies;
        }
        //+++
        $args['orderby'] = 'title';
        unset($args['meta_key']);
        $args['fields'] = 'ids';
        //+++
        $mdf_loop = new WP_Query($args);
        //for wrong page numbers
        if (!$mdf_loop->found_posts)
        {
            $args['paged'] = 1;
            $args['posts_per_page'] = $per_page;
            $mdf_loop = new WP_Query($args);
        }
        //+++
        $atts['posts_ids'] = $mdf_loop->posts;
        //+++       
        return self::render_html(self::get_application_path() . 'views/gmap/mdf_gmap_const.php', $atts);
    }

}
