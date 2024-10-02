<li class="mdf_subscr_item mdf_subscr_item_<?php echo esc_attr($key) ?>">
    <?php
    
    if (!isset($counter)) {
	$counter = esc_html__('new', 'meta-data-filter');
    }
    ?>
    <a class="mdf_link_to_subscr"  href="<?php echo esc_url_raw($link) ?>" target="blank" >#<?php echo esc_html($counter) ?>.&nbsp;<?php echo esc_html($subscr_lang) ?></a>
    <p class="mdf_tooltip"><span class="mdf_tooltip_data"><?php echo wp_kses_post(wp_unslash($get)); ?></span>  <span class="mdf_icon_subscr"></span></p>   
    <a href="#" class="mdf_remove_subscr" data-user="<?php echo esc_attr($user_id) ?>" data-key="<?php echo esc_attr($key) ?>"><img src="<?php echo MDTF_MESSENGER_URI ?>images/delete.png" height="12" width="12" alt="" /></a>
</li>
