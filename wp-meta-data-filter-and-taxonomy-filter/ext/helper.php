<?php

final class MDTF_HELPER {

    public static function get_post_featured_image($post_id, $alias) {
        $img_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'single-post-thumbnail');
        $img_src = $img_src[0];
        $url = self::get_image($img_src, $alias);
        return $url;
    }

    public static function get_image($img_src, $alias) {
        if (empty($alias)) {
            return $img_src;
        }

        $al = explode('x', $alias);
        include_once MetaDataFilterCore::get_application_path() . 'lib/aq_resizer.php';
        $new_img_src = aq_resize($img_src, $al[0], $al[1], true);

        if (!$new_img_src) {
            //***
        }

        return $new_img_src;
    }

    //we need it when have filter-item by post_title
    public static function mdf_post_title_filter($where = '') {
        if (!isset($_REQUEST['mdf_post_title_request'])) {
            return $where;
        }
        //classes/html.php#517
        $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_request']));
        if (isset($data[2]) AND!empty($data[2])) {
            $search = $data[2];
            $words = array();
            $compare = apply_filters('mdf_txtsearch_some_words_condition', "AND");
            if (apply_filters('mdf_txtsearch_some_words_behavior', true)) {
                $words = explode(" ", $search);
            } else {
                $words[] = $search;
            }
            $where .= "AND (";
            if ($data[0] == 'LIKE') {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_title LIKE '%{$word}%' ";
                }
            } else {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_title='{$word}' ";
                }
            }
            $where .= implode(" {$compare} ", $words) . ")";
        }

        return $where;
    }

    //we need it when have filter-item by post_content
    public static function mdf_post_content_filter($where = '') {
        if (!isset($_REQUEST['mdf_post_content_request'])) {
            return $where;
        }
        //classes/html.php#517
        $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_content_request']));
        if (isset($data[2]) AND!empty($data[2])) {
            $search = $data[2];
            $words = array();
            $compare = apply_filters('mdf_txtsearch_some_words_condition', "AND");
            if (apply_filters('mdf_txtsearch_some_words_behavior', true)) {
                $words = explode(" ", $search);
            } else {
                $words[] = $search;
            }
            $where .= "AND (";
            if ($data[0] == 'LIKE') {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content LIKE '%{$word}%' ";
                }
            } else {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content='{$word}' ";
                }
            }
            $where .= implode(" {$compare} ", $words) . ")";
        }

        return $where;
    }

    //we need it when have filter-item by title or post_content on the same time
    public static function mdf_post_title_or_content_filter($where = '') {
        if (!isset($_REQUEST['mdf_post_title_or_content_request'])) {
            return $where;
        }
        //classes/html.php#517
        $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_or_content_request']));
        if (isset($data[2]) AND!empty($data[2])) {
            $search = $data[2];
            $words = array();
            $compare = apply_filters('mdf_txtsearch_some_words_condition', "AND");
            if (apply_filters('mdf_txtsearch_some_words_behavior', true)) {
                $words = explode(" ", $search);
            } else {
                $words[] = $search;
            }
            $where .= "AND (";
            if ($data[0] == 'LIKE') {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content LIKE '%{$word}%' OR post_title LIKE '%{$word}%' OR post_excerpt LIKE '%{$word}%'";
                }
            } else {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content='{$word}' OR post_title='{$word}' OR post_excerpt='{$word}'";
                }
            }
            $where .= implode(" {$compare} ", $words) . ")";
        }

        return $where;
    }

    //we need it when have filter-item by title and post_content on the same time
    public static function mdf_post_title_and_content_filter($where = '') {
        if (!isset($_REQUEST['mdf_post_title_and_content_request'])) {
            return $where;
        }
        //classes/html.php#517
        $data = explode('^', sanitize_text_field($_REQUEST['mdf_post_title_and_content_request']));
        if (isset($data[2]) AND!empty($data[2])) {
            $search = $data[2];
            $words = array();
            $compare = apply_filters('mdf_txtsearch_some_words_condition', "AND");
            if (apply_filters('mdf_txtsearch_some_words_behavior', true)) {
                $words = explode(" ", $search);
            } else {
                $words[] = $search;
            }
            $where .= "AND (";
            if ($data[0] == 'LIKE') {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content LIKE '%{$word}%' AND post_title LIKE '%{$word}%'";
                }
            } else {
                foreach ($words as $key => $word) {
                    $words[$key] = " post_content='{$word}' AND post_title='{$word}'";
                }
            }
            $where .= implode(" {$compare} ", $words) . ")";
        }

        return $where;
    }

    public static function mdf_serch_by_author($where = "") {
        if (!isset($_REQUEST['mdf_search_by_author']) OR $_REQUEST['mdf_search_by_author'] == -1) {
            return $where;
        }
		$mdf_search_by_author = sanitize_text_field($_REQUEST['mdf_search_by_author']);
        $where .= " AND post_author={$mdf_search_by_author} ";
        return $where;
    }

    public static function cast_decimal_precision($where) {
        return str_replace('DECIMAL', 'DECIMAL(10,2)', $where);
    }

    //for _price in woocommerce
    public static function get_woo_min_max_price() {
        if (!class_exists('WooCommerce')) {
            return array();
        }
        //+++
        global $wpdb;
        if (version_compare(WOOCOMMERCE_VERSION, '2.6', '>')) {

            $prices = self::get_filtered_price();
            $max = ceil($prices->max_price);
            $min = floor($prices->min_price);
            return array('min' => $min, 'max' => $max);
        } else {
            if (sizeof(WC()->query->layered_nav_product_ids) === 0) {
                $data_sql = array(
                    array(
                        'val' => $wpdb->posts,
                        'type' => 'string',
                    ),
                    array(
                        'val' => $wpdb->postmeta,
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_price',
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_min_variation_price',
                        'type' => 'string',
                    ),
                );
                $min = floor($wpdb->get_var(
                                self::mdf_prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE ( meta_key = \'%3$s\' OR meta_key = \'%4$s\' )
					AND meta_value != ""
				', $data_sql)
                ));
                $data_sql = array(
                    array(
                        'val' => $wpdb->posts,
                        'type' => 'string',
                    ),
                    array(
                        'val' => $wpdb->postmeta,
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_price',
                        'type' => 'string',
                    ),
                );
                $max = ceil($wpdb->get_var(
                                self::mdf_prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key = \'%3$s\'
				', $data_sql)
                ));
            } else {
                $data_sql = array(
                    array(
                        'val' => $wpdb->posts,
                        'type' => 'string',
                    ),
                    array(
                        'val' => $wpdb->postmeta,
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_price',
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_min_variation_price',
                        'type' => 'string',
                    ),
                );
                $min = floor($wpdb->get_var(
                                self::mdf_prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE ( meta_key =\'%3$s\' OR meta_key =\'%4$s\' )
					AND meta_value != ""
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $data_sql
                )));
                $data_sql = array(
                    array(
                        'val' => $wpdb->posts,
                        'type' => 'string',
                    ),
                    array(
                        'val' => $wpdb->postmeta,
                        'type' => 'string',
                    ),
                    array(
                        'val' => '_price',
                        'type' => 'string',
                    ),
                );
                $max = ceil($wpdb->get_var(
                                self::mdf_prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key =\'%3$s\'
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $data_sql
                )));
            }

            return array('min' => $min, 'max' => $max);
        }
    }

    public static function get_filtered_price() {
        global $wpdb, $wp_the_query;

        $args = $wp_the_query->query_vars;
        $tax_query = isset($args['tax_query']) ? $args['tax_query'] : array();
        $meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();

        if (apply_filters('mdf_woo_price_dinamic_recount', false)) {
            $_REQUEST['mdf_get_query_args_only'] = true;
            do_shortcode('[meta_data_filter_results]');
            $args1 = self::sanitize_array_r($_REQUEST['meta_data_filter_args']);

            // Fix for woo price slider in ajax
            if (isset($args1)AND!empty($args1)) {
                $tax_query = isset($args1['tax_query']) ? $args1['tax_query'] : array();
                $meta_query = isset($args1['meta_query']) ? $args1['meta_query'] : array();
            }
            //++++    
        }
        if (!empty($args['taxonomy']) && !empty($args['term'])) {
            $tax_query[] = array(
                'taxonomy' => $args['taxonomy'],
                'terms' => array($args['term']),
                'field' => 'slug',
            );
        }

        if (!empty($meta_query) AND is_array($meta_query)) {
            foreach ($meta_query as $key => $query) {
                if (!empty($query['price_filter']) || !empty($query['rating_filter'])) {
                    unset($meta_query[$key]);
                }
            }
        }

        $meta_query = new WP_Meta_Query($meta_query);
        $tax_query = new WP_Tax_Query($tax_query);

        $meta_query_sql = $meta_query->get_sql('post', $wpdb->posts, 'ID');
        $tax_query_sql = $tax_query->get_sql($wpdb->posts, 'ID');
        $sql = "SELECT min( price_meta.meta_value + 0.0  ) as min_price, max( price_meta.meta_value + 0.0  )as max_price FROM {$wpdb->posts} ";
        $sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
        $sql .= " 	WHERE {$wpdb->posts}.post_type = 'product'
					AND {$wpdb->posts}.post_status = 'publish'
					AND price_meta.meta_key IN ('" . implode("','", array_map('esc_sql', apply_filters('woocommerce_price_filter_meta_keys', array('_price')))) . "')
					AND price_meta.meta_value > '' ";
        $sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

        return $wpdb->get_row($sql);
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
    //for range drop-down. Util func: look in range (0-500, 501-1000, 1001-2000 and etc...) 
    public static function is_slider_range_value($value) {
        $is_range = false;
        if (is_array($value)) {
            if (count($value) == 2) {
                $f = floatval($value[0]);
                $t = $value[1];
                if ($t === 'i') {
                    $t = MetaDataFilterCore::$max_int;
                } else {
                    $t = floatval($t);
                }
                if ($f < $t) {
                    $is_range = true;
                }
            }
        }

        return $is_range;
    }

    //log test data while makes debbuging
    public static function log($string) {
        $handle = fopen(MetaDataFilterCore::get_application_path() . 'log.txt', 'a+');
        $string .= PHP_EOL;
        fwrite($handle, $string);
        fclose($handle);
    }

    public static function mdf_prepare($query, $args) {
        if (is_null($query)) {
            return;
        }
        $sql_val = array();

        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('|(?<!%)%f|', '%F', $query); // Force floats to be locale unaware
        $query = preg_replace('|(?<!%)%s|', "'%s'", $query); // quote the strings, avoiding escaped strings like %%s
        if (!is_array($args)) {
            $args = array('val' => $args, 'type' => 'string');
        }
        foreach ($args as $item) {

            if (!is_array($item) OR!isset($item['val'])) {
                continue;
            }
            if (!isset($item['type'])) {
                $item['type'] = 'string';
            }
            $sql_val[] = self::mdf_escape_sql($item['type'], $item['val']);
        }
        return @vsprintf($query, $sql_val);
    }

    public static function mdf_escape_sql($type, $value) {
        switch ($type) {
            case'string':
                global $wpdb;
                return $wpdb->_real_escape($value);
                break;
            case'int':
                return intval($value);
                break;
            case'float':
                return floatval($value);
                break;
            default :
                global $wpdb;
                return $wpdb->_real_escape($value);
        }
    }

}
