<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<a class="button button-primary button-medium add_item_to_data_group" data-add-to='top' href="#"><?php esc_html_e("Prepend Filter Item", 'meta-data-filter') ?></a><br />
<br />
<?php global $post; ?>
<script>
    /*DYNAMIC SCRIPT*/
    var mdf_current_post_id = <?php echo esc_attr($post->ID) ?>;
	var mdf_group_items_nonce = '<?php echo  wp_create_nonce( 'mdtf_group_items_nonce' ) ?>';
</script>
<ul id="data_group_items">
    <?php if (!empty($html_items) AND is_array($html_items)): ?>
        <?php foreach ($html_items as $key => $value) : ?>
            <li class="admin-drag-holder mdf_filter_item"><?php MetaDataFilterHtml::render_html_e(MetaDataFilter::get_application_path() . 'views/add_item_to_data_group.php', array('itemdata' => $value, 'uniqid' => $key)); ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<br />
<a class="button button-primary button-medium add_item_to_data_group" data-add-to='bottom' href="#"><?php esc_html_e("Append Filter Item", 'meta-data-filter') ?></a><br />

