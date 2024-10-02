<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wrap">

    <input type="hidden" name="mdf_woo_search_values" value="" />
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Panel type", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <?php
                            $panel_types = array(
                                'select' => esc_html__("Drop-down", 'meta-data-filter'),
                                'buttons' => esc_html__("Buttons", 'meta-data-filter')
                            );
                            ?>
                            <select name="panel_type">
                                <?php foreach ($panel_types as $key => $value) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php if ($panel_type == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </fieldset>

                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Show results taxonomies navigation", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" <?php checked(1, $show_results_tax_navigation) ?> value="1" name="show_results_tax_navigation" />
                        </label>
                    </fieldset>
                    <p class="description">
                        <img src="<?php echo MetaDataFilter::get_application_uri() ?>images/show_results_tax_navigation.png" alt="<?php esc_html_e("Show results taxonomies navigation", 'meta-data-filter') ?>" /><br />
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Sort panel meta keys", 'meta-data-filter') ?></label></th>
                <td>
                    <a href="#" class="button" id="mdf_add_woo_search_value"><?php esc_html_e("Add meta key", 'meta-data-filter') ?></a><br />
                    <ul id="mdf_woo_search_values">
                        <?php if (!empty($settings) AND is_array($settings)): ?>
                            <?php foreach ($settings as $value) : ?>
                                <li>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($value) ?>" name="mdf_woo_search_values[]">&nbsp;<a href="#" class="button mdf_del_woo_search_value">X</a><br />
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul> 
                    <p class="description"><?php printf(esc_html__("Special words: %s. Example: title^Title", 'meta-data-filter'), implode(',', MetaDataFilterCore::$allowed_order_by)) ?></p>
                </td>
            </tr>

            <tr valign="top" style="display:none;">
                <th scope="row"><label><?php esc_html_e("Default order", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <?php
                            $default_orders = array(
                                'DESC' => esc_html__("DESC", 'meta-data-filter'),
                                'ASC' => esc_html__("ASC", 'meta-data-filter')
                            );
                            ?>
                            <select name="default_order">
                                <?php foreach ($default_orders as $key => $value) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php if ($default_order == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </fieldset>

                </td>
            </tr>


            <tr valign="top" style="display:none;">
                <th scope="row"><label><?php esc_html_e("Default order by", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <input class="wide" type="text" name="default_order_by" value="<?php echo esc_html($default_order_by) ?>" />
                        </label>
                    </fieldset>

                </td>
            </tr>

        </tbody>
    </table>



    <div class="clear"></div>
    <div style="display: none;">
        <div id="mdf_woo_search_values_input_tpl">
            <li><input type="text" class="regular-text" placeholder="<?php esc_html_e("Example: _price^Price", 'meta-data-filter') ?>" value="" name="__NAME__[]">&nbsp;<a href="#" class="button mdf_del_woo_search_value">X</a><br /></li>
        </div>
    </div>
  
</div>

