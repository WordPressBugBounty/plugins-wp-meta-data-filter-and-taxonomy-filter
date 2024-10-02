<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php if (is_singular($instance['meta_data_filter_slug'])): ?>
    <div class="widget widget-meta-data-filter widget-meta-data-single">

        <h3><?php echo esc_html($instance['title']) ?></h3>
        <?php
        global $post;
        MetaDataFilterPage::draw_single_page_items($post->ID, $instance['show_absent_items']);
        ?>

    </div>
    <?php

 endif;
