<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<input type="hidden" name="shortcode_options[filters]" value="" />
<?php if (!empty($data)): ?>
    <?php foreach ($data as $filter_post_id => $filter) : ?>        
        <li>
            <input type="hidden" name="html_items[<?php echo esc_attr($filter_post_id) ?>]" />
            <h3 style="color: brown;"><?php esc_html_e($filter['post_title']) ?> (<?php esc_html_e('Hide', 'meta-data-filter') ?>:&nbsp;<input type="hidden" value="<?php echo (int) @$shortcode_options['filters'][$filter_post_id]['is_hidden']; ?>" name="shortcode_options[filters][<?php echo esc_attr($filter_post_id) ?>][is_hidden]" /> <input <?php checked((int) @$shortcode_options['filters'][$filter_post_id]['is_hidden'], 1); ?> class="mdf_hide_shortcode_html_block" type="checkbox" />)</h3>
            <?php if (isset($filter['html_items']) AND!empty($filter['html_items']) AND is_array($filter['html_items'])): ?>
                <ul class="options_meta_box_html_items" <?php if (@$shortcode_options['filters'][$filter_post_id]['is_hidden']): ?>style="display: none;"<?php endif; ?>>
                    <?php foreach ($filter['html_items'] as $html_item_key => $html_item) : ?>
                        <li>
                            <input type="hidden" name="html_items[<?php echo esc_attr($filter_post_id) ?>][<?php echo esc_attr($html_item_key) ?>]" />
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e($html_item['name']) ?><br /></th>
                                        <td>
                                            <ul>
                                                <li>
                                                    <i class="mdtf-h11">
                                                        <?php
                                                        if ($html_item['is_reflected'] == 1) {
                                                            printf(esc_html__('is reflected by %s', 'meta-data-filter'), $html_item['reflected_key']);
                                                        } else {
                                                            echo esc_html($html_item_key);
                                                        }
                                                        ?>
                                                    </i></li>
                                                <li>
                                                    <b><?php esc_html_e('Type', 'meta-data-filter') ?></b>: <?php echo esc_html($html_item['type']) ?><br />
                                                </li>
                                                <?php if ($html_item['type'] == 'slider'): ?>
                                                    <li><b><?php esc_html_e('Range', 'meta-data-filter') ?></b>: <?php echo esc_html($html_item['slider']) ?><br /></li>
                                                <?php endif; ?>                                               
                                                <li>
                                                    <b><?php esc_html_e('Hide', 'meta-data-filter') ?></b>:&nbsp;
                                                    <input type="hidden" name="shortcode_options[filters][<?php echo esc_attr($filter_post_id) ?>][<?php echo esc_attr($html_item_key) ?>]" value="<?php echo (int) @$shortcode_options['filters'][$filter_post_id][$html_item_key]; ?>">
                                                    <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['filters'][$filter_post_id][$html_item_key]): ?>checked<?php endif; ?> /> <br />
                                                </li>                                                
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </li>
<?php endif; ?>





