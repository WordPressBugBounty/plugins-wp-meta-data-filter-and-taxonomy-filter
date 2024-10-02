<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class MDTF_UTILITIES {

    public static function init() {
        add_action('wp_ajax_mdf_util_term_to_meta', array(__CLASS__, 'util_term_to_meta'));
    }

    public function util_term_to_meta() {
        global $wpdb;
        $post_type = sanitize_text_field(esc_html($_REQUEST['post_type']));
        $posts = $wpdb->get_results("SELECT ID from {$wpdb->posts} WHERE post_type='{$post_type}'");
        //***
        if (!empty($posts)) {
            $meta_key = sanitize_text_field($_REQUEST['meta_key']);
            foreach ($posts as $post) {
                $terms = wp_get_post_terms($post->ID, sanitize_text_field($_REQUEST['tax']), array());

                if (!empty($terms)) {
                    $term = $terms[0];
                    if ($_REQUEST['type'] == 'term_slug') {
                        //slug
                        update_post_meta($post->ID, $meta_key, $term->slug);
                    } else {
                        //name
                        update_post_meta($post->ID, $meta_key, $term->name);
                    }
                }
            }
        }

        die('done!');
    }

}
