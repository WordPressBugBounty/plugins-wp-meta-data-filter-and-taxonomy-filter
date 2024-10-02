<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>


<?php
$res = array();
if (!empty($filter_posts)) {
    if (is_array($filter_posts)) {
        foreach ($filter_posts as $pid) {
            $tmp = MetaDataFilterPage::get_html_items($pid);
            if (!empty($tmp)) {
                if (is_array($tmp)) {
                    foreach ($tmp as $key => $value) {
                        $res[$key] = $value;
                    }
                }
            }
        }
    }
}
?>

<?php if (isset($_REQUEST['in_essentil_block'])): ?>

    <?php if (!empty($res) AND ! empty($page_meta_data_filter)): ?>

        <div class="mdf_post_features_panel_essential" id="features_panel_essential_<?php echo uniqid() ?>">

            <?php foreach ($page_meta_data_filter as $key => $value) : ?>
                <?php
                // Show Date
                $tmp_calendar_from = str_replace("_from", "", $key);
                if (isset($res[$tmp_calendar_from]['type']) AND $res[$tmp_calendar_from]['type'] == 'calendar'):
                    ?>
                    <li class="mdf_post_feature">
                        <?php
                        if (!empty($value)) {
                            esc_html_e('From:','meta-data-filter');
                            echo date("d.m.y", (int) $value);
                        }
                        if (!empty($page_meta_data_filter[$tmp_calendar_from . "_to"])) {

                            esc_html_e(' - to:', 'meta-data-filter');

                            echo date("d.m.y", (int) $page_meta_data_filter[$tmp_calendar_from . "_to"]);
                        }
                        ?>

                    </li>
                <?php endif;  //end show date
                ?>
                <?php if (isset($res[$key])): ?>


                    <?php
                    if ($res[substr($key, 0, -3)]['type'] == 'calendar'):
                        $new_key = substr($key, 0, -3);
                        $date_format = (MetaDataFilterCore::get_setting('calendar_date_format') != "") ? MetaDataFilterCore::get_setting('calendar_date_format') : "mm/dd/yy";
                        $date_format = preg_replace('~(.)\1+~i', '\\1', $date_format);
                        //print_r($date_format);
                        ?>
                        <li class="mdf_post_feature mdf_title_data_checkbox <?php echo esc_attr($new_key) ?>">
                            <?php esc_html_e($res[$new_key]['name']) ?> <?php if (!empty($res[$new_key]['description'])): ?>
                                <span class="mdf_tooltip2"><?php echo wp_kses_post(wp_unslash(strip_tags(esc_html__($res[$new_key]['description'])))); ?></span>
                            <?php endif; ?><?php
                            if (get_post_meta($post_id, $new_key . "_from", true) != "") {
                                echo date($date_format, get_post_meta($post_id, $new_key . "_from", true));
                            }
                            ?>
                    <?php
                    if (get_post_meta($post_id, $new_key . "_to", true) != "") {
                        echo " - ", date($date_format, get_post_meta($post_id, $new_key . "_to", true));
                    }
                    ?>
                        </li>
                    <?php endif;
                    ?>


                    <?php if (($res[$key]['type'] == 'label' OR $res[$key]['type'] == 'checkbox') AND (int) $value == 1): ?>
                        <div class="mdf_post_feature mdf_title_data_checkbox <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?></div>
                <?php endif; ?>

                <?php if ($res[$key]['type'] == 'select' AND $value != 'none'): ?>

                        <?php
                        $tmp = floatval($value);
                        if ($tmp > 0):
                            ?>

                            <div class="mdf_post_feature mdf_title_data_select <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name'] . ':&nbsp;' . $value ) ?></div>


                        <?php else: ?>


                            <?php
                            if (!empty($res[$key]['select'])) {
                                $val = "~";
                                foreach ($res[$key]['select'] as $k => $v) {
                                    $select_option_key = $res[$key]['select_key'][$k];
                                    if ($select_option_key == $value) {
                                        $val = $v;
                                        break;
                                    }
                                }
                            }
                            ?>

                        <?php if ($val != '~'): ?>
                                <div class="mdf_post_feature mdf_title_data_select <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name'] . ':&nbsp;' . $val ) ?></div>
                            <?php endif; ?>

                        <?php endif; ?>


                    <?php endif; ?>


                    <?php if ($res[$key]['type'] == 'slider'): ?>
                        <?php

                        if ($res[$key]['slider_prettify'] == 1) {
                            $value = number_format($value, 0,',','');
                        }
                        if (!isset($res[$key]['slider_range_value'])) {
                            $res[$key]['slider_range_value'] = 0;
                        }
                        ?>

                        <?php if ($res[$key]['slider_range_value'] == 1): ?>
                            <?php
                            $t = get_post_meta($post_id, $key . '_to', true);
                            $f = $res[$key]['slider_prefix'] . $value . ' - ' . $t . $res[$key]['slider_postfix'];
                            ?>
                            <div class="mdf_post_feature mdf_title_data_slider <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?>: <i><?php echo esc_html($f); ?></i></div>
                        <?php else: ?>
                            <?php $value = $res[$key]['slider_prefix'] . $value . $res[$key]['slider_postfix']; ?>
                            <div class="mdf_post_feature mdf_title_data_slider <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?>: <i><?php echo esc_html($value); ?></i></div>
                    <?php endif; ?>

                <?php endif; ?>


            <?php endif; ?>
                <?php endforeach; ?>
        </div>
            <?php endif; ?>

        <?php else: ?>

                <?php if (!empty($res) AND ! empty($page_meta_data_filter)): ?>
        <span class="mdf_title_data">
            <ul class="mdf_post_features_panel">
                    <?php foreach ($page_meta_data_filter as $key => $value) : ?>
                        <?php
                        // Show Date
                        $tmp_calendar_from = str_replace("_from", "", $key);
                        if (isset($res[$tmp_calendar_from]['type']) AND $res[$tmp_calendar_from]['type'] == 'calendar'):
                            ?>
                        <li class="mdf_post_feature">
                        <?php
                        if (!empty($value)) {
                            esc_html_e('From:','meta-data-filter');
                            echo date("d.m.y", (int) $value);
                        }
                        if (!empty($page_meta_data_filter[$tmp_calendar_from . "_to"])) {

                            esc_html_e(' - to:', 'meta-data-filter');

                            echo date("d.m.y", (int) $page_meta_data_filter[$tmp_calendar_from . "_to"]);
                        }
                        ?>

                        </li>
                    <?php endif;  //end show date
                    ?>




            <?php if (isset($res[$key])): ?>


                        <?php if (($res[$key]['type'] == 'label' OR $res[$key]['type'] == 'checkbox') AND (int) $value == 1): ?>
                            <li class="mdf_post_feature mdf_title_data_checkbox <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?><?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>
                <?php endif; ?>

                        <?php if ($res[$key]['type'] == 'select' AND $value != 'none'): ?>

                            <?php
                            $tmp = floatval($value);
                            if ($tmp > 0):
                                ?>

                                <li class="mdf_post_feature mdf_title_data_select <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name'] . ':&nbsp;<i>' . $value . '</i>') ?>&nbsp;<?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>


                            <?php else: ?>


                                <?php
                                if (!empty($res[$key]['select'])) {
                                    $val = "~";
                                    foreach ($res[$key]['select'] as $k => $v) {
                                        $select_option_key = $res[$key]['select_key'][$k];
                                        if ($select_option_key == $value) {
                                            $val = $v;
                                            break;
                                        }
                                    }
                                }
                                ?>

                        <?php if ($val != '~'): ?>
                                    <li class="mdf_post_feature mdf_title_data_select <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name'] . ':&nbsp;' . $val) ?>&nbsp;<?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>
                                <?php endif; ?>

                    <?php endif; ?>


                        <?php endif; ?>
                        <?php
                        //start range  select    
                        if ($res[$key]['type'] == 'range_select' AND $value != 'none') {
                            $tmp = floatval($value);
                            ?>
                            <li class="mdf_post_feature mdf_title_data_select <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name'] . ':&nbsp;' . $value ) ?>&nbsp;<?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>    

                        <?php }
                        //end range  select    
                        ?>


                        <?php if ($res[$key]['type'] == 'slider'): ?>
                            <?php
                            //print_r($res[$key]);
                            if ($res[$key]['slider_prettify'] == 1) {
                                $value = number_format($value, 2);
                            }
                            if (!isset($res[$key]['slider_range_value'])) {
                                $res[$key]['slider_range_value'] = 0;
                            }
                            ?>

                            <?php if ($res[$key]['slider_range_value'] == 1): ?>
                                <?php
                                $t = get_post_meta($post_id, $key . '_to', true);
                                $f = $res[$key]['slider_prefix'] . $value . ' - ' . $t . $res[$key]['slider_postfix'];
                                ?>
                                <li class="mdf_post_feature mdf_title_data_slider <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?>: <i><?php echo esc_html($f); ?></i><?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>
                    <?php else: ?>
                        <?php $value = $res[$key]['slider_prefix'] . $value . $res[$key]['slider_postfix']; ?>
                                <li class="mdf_post_feature mdf_title_data_slider <?php echo esc_attr($key) ?>"><?php esc_html_e($res[$key]['name']) ?>: <i><?php echo esc_html($value); ?></i><?php if (!empty($res[$key]['description'])): ?><span class="mdf_tooltip2"><?php echo strip_tags(esc_html__($res[$key]['description'])); ?></span><?php endif; ?></li>
                    <?php endif; ?>

                <?php endif; ?>


            <?php endif; ?>
        <?php endforeach; ?>
            </ul>
            <div style="clear: both;"></div>
        </span>
    <?php endif; ?>



<?php endif; ?>

