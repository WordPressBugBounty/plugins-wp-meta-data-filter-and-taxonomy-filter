<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php if (!empty($filter_post['items'])): ?>
    <?php
    if (substr($filter_post['name'], 0, 1) !== '~'):
        $mdtf_title_sections = "h4";
        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
        ?>
        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb9"><?php esc_html_e($filter_post['name']); ?></<?php echo esc_attr($mdtf_title_sections) ?>>
    <?php endif; ?>
    <?php
    $icon = MetaDataFilter::get_application_uri() . 'images/tooltip-info.png';
    $settings = MetaDataFilter::get_settings();
    if (!empty($settings['tooltip_icon'])) {
        $icon = $settings['tooltip_icon'];
    }
    ?>
    <table class="mdtf-w100p">
        <tbody>
            <?php foreach ($filter_post['items'] as $key => $item) : $uid = uniqid(); ?>

                <?php
                $is_reflected = false;
                if (isset($item['is_reflected']) AND $item['is_reflected'] == 1) {
                    $is_reflected = true;
                }
                ?>

                <?php if ($item['type'] == 'slider'): ?>

                    <tr valign="top">
                        <td>
                            <div class="mdf_input_container mdtf-p0">
                                <label>
                                    <?php esc_html_e($item['name']); ?>:&nbsp;<?php echo MetaDataFilter::get_field($key, $post_id, $is_reflected) ?><?php if (!empty($item['description'])): ?>;
                                        <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                            <img alt="help" src="<?php echo esc_url_raw($icon) ?>">
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>


                <?php if ($item['type'] == 'checkbox'): ?>
                    <?php
                    $is_checked = (int) MetaDataFilter::get_field($key, $post_id, $is_reflected);
                    $to_show = true;
                    if (!$show_absent_items) {
                        if (!$is_checked) {
                            $to_show = false;
                        }
                    }
                    ?>
                    <?php if ($to_show): ?>
                        <tr valign="top">
                            <td>
                                <div class="mdf_input_container">
                                    <input disabled class="mdf_disabled_checkbox" type="checkbox" <?php if ($is_checked): ?>checked<?php endif; ?> />
                                    <label>&nbsp;<?php esc_html_e($item['name']) ?><?php if (!empty($item['description'])): ?>
                                            <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                <img alt="help" src="<?php echo esc_url_raw($icon) ?>">
                                            </span><?php endif; ?>
                                    </label>
                                </div>
                            </td>

                        </tr>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if ($item['type'] == 'select'): ?>
                    <?php if (!empty($item['select'])): ?>
                        <?php
                        $selected = MetaDataFilter::get_field($key, $post_id, $is_reflected);
                        $to_show = true;
                        if (!$show_absent_items) {
                            if (!$selected) {
                                $to_show = false;
                            }
                        }
                        //***
                        $val = "~";
                        foreach ($item['select'] as $kk => $value) {
                            $select_option_key = $item['select_key'][$kk];
                            if ($selected == $select_option_key) {
                                $val = $value;
                            }
                        }
                        ?>
                        <?php
                        if ($to_show AND!empty($val) AND $val != '~'):
                            $mdtf_title_sections = "h5";
                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                            ?>
                            <tr>
                                <td>
                                    <div class="mdf_input_container">
                                        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4">
                                        <?php esc_html_e($item['name']) ?>:
                                        &nbsp;<?php if (!empty($item['description'])): ?>
                                            <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                            </span>
                                        <?php endif; ?>
                                        </<?php echo esc_attr($mdtf_title_sections) ?>>
                                        <select disabled class="mdf_item_chars_select mdf_disabled_select">
                                            <option><?php echo esc_html($val); ?></option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
endif;
?>