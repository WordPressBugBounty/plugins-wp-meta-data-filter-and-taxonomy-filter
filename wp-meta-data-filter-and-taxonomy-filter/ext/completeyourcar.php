<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MDTF_COMPLETE_CAR extends MetaDataFilterCore {

    public static function init() {
        if (class_exists('displayProduct')) {
            add_filter('the_title', array(__CLASS__, 'the_title'), 9999);
            add_action("woocommerce_short_description", array(__CLASS__, 'woocommerce_short_description'), 9999);
        }
    }

    public static function the_title($title) {
        global $post;
        if ($post->post_type == 'product') {
            if (self::is_page_mdf_data()) {
                $year = get_post_meta($post->ID, 'medafi_years', true);
                $title.=(". Implements for - " . $year . " " . self::results_tax_string());
            }
        }

        return $title;
    }

    public static function woocommerce_short_description($desc) {
        global $post;
        $additional = "";
        $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'parent' => 0);
        $terms = wp_get_post_terms($post->ID, 'makers', $args);
        //*** get all parents
        $parents = array();
        if (!empty($terms)) {
            foreach ($terms as $key => $value) {
                if ($value->parent == 0) {
                    $parents[] = $value;
                } else {
                    break;
                }
            }
        }
        //***
        $strings = array();
        if (!empty($parents)) {
            foreach ($parents as $parent) {
                $tmp1 = $parent->name . ' ';
                foreach ($terms as $child2) {
                    if ($child2->parent == $parent->term_id) {
                        $year = get_post_meta($post->ID, 'medafi_years', true);
                        $strings[] = $year . " " . $tmp1 . $child2->name;
                    }
                }
            }
        }


        $additional = implode('<br />', $strings);
        if (!empty($additional)) {
            $desc.='<h4>Suitable for the following models:</h4>' . $additional;
        }
        return $desc;
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
    public static function results_tax_string() {
        if (self::is_page_mdf_data() AND isset($_REQUEST['meta_data_filter_args']) AND isset($_REQUEST['meta_data_filter_args']['tax_query'])) {
            $taxes = self::sanitize_array_r($_REQUEST['meta_data_filter_args']['tax_query']);
            unset($taxes['relation']);

            if (!empty($taxes)) {
                $tmp = array();
                foreach ($taxes as $tax) {
                    $tmp[$tax['taxonomy']] = $tax['terms'];
                }
                //+++
                $output_string = ""; // and prepare our output buffer
                foreach ($tmp as $tax_name => $terms) {
                    $output="";
                    if (is_array($terms)) {
                        foreach ($terms as $term_id) { // now loop through all the slugs
                            $term = get_term_by('id', $term_id, $tax_name); // and based on that slug, get the term object
                            $output .= $term->name . '&nbsp;'; // and add the term's name to our output buffer followed by a comma (,)
                        }
                    } else {
                        $term = get_term_by('id', $terms, $tax_name); // and based on that slug, get the term object
                        $parents = self::get_taxonomy_parents_all($term->term_id, $tax_name);
                        $buffer = array();
                        foreach ($parents as $term) {
                            $buffer[] = $term->name . '&nbsp;';
                        }
                        $buffer = array_reverse($buffer);
                        $buffer = implode(" ", $buffer);
                        $output.=$buffer;
                    }
                    $output_string.= rtrim($output, ", ") . " + ";  // when we're finished with terms, remove the last comma
                }

                return rtrim($output_string, " + "); // when we're finished with taxonomies, remove the last '+'
            }
        }
    }

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

}
