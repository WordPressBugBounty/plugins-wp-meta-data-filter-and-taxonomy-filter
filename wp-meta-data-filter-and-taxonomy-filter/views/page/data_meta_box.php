<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
wp_dropdown_categories(
        array(
            'hide_empty' => 1,
            'orderby' => 'name',
            'taxonomy' => MetaDataFilterCore::$slug_cat,
            'id' => MetaDataFilterCore::$slug_cat,
            'show_option_none' => esc_html__('category is not selected', 'meta-data-filter'),
            'name' => MetaDataFilterCore::$slug_cat,
            'selected' => $cat_id
        )
);
?>

<div id="meta_data_filter_area"><?php echo MetaDataFilterPage::draw_back_page_items($cat_id, $post_id) ?></div>


