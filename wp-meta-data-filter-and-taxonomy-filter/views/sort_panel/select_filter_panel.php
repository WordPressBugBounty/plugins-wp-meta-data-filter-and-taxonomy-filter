<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<span class="mdf_sort_panel">
    <span class="mdf_sort_panel_select">
        <select class="mdf_sort_panel_order_by">
            <option value="0"><?php esc_html_e("Default", 'meta-data-filter'); ?></option>
            <?php foreach ($settings as $value) : ?>
                <?php $value = explode('^', $value); ?>
                <option <?php selected($order_by, $value[0]) ?> value="<?php echo esc_attr($value[0]) ?>"><?php echo esc_html($value[1]) ?></option>
            <?php endforeach; ?>
        </select>&nbsp;
        <select class="mdf_sort_panel_ordering">
            <option value="DESC" <?php selected($order, 'DESC') ?>><?php esc_html_e("descending", 'meta-data-filter'); ?></option>
            <option value="ASC" <?php selected($order, 'ASC') ?>><?php esc_html_e("ascending", 'meta-data-filter'); ?></option>
        </select>&nbsp;
    </span>
</span>

