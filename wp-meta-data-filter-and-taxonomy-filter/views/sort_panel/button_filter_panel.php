<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<span class="mdf_sort_panel">
    <ul class="mdf_custom_filter_panel">
        <?php foreach ($settings as $value) : ?>
            <?php $value = explode('^', $value); ?>
            <li <?php if ($value[0] == $order_by): ?>class="meta_data_filter_order_<?php echo esc_attr(strtolower($order)) ?>"<?php endif; ?>>
                <a data-order-by="<?php echo esc_attr($value[0]) ?>" data-order="<?php echo($order == 'ASC' ? 'DESC' : 'ASC') ?>" href="javascript:void(0);"><?php echo esc_html($value[1]) ?></a>
            </li>
        <?php endforeach; ?>
    </ul><br />
</span>

