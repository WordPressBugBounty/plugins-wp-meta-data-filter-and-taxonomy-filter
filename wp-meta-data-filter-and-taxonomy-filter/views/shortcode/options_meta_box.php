<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Select filter category', 'meta-data-filter') ?><br /></th>
            <td>
                <fieldset>

                    <?php
                    if (!empty($mdf_terms)) {
                        ?>
                        <select id="mdf_shortcode_cat" name="shortcode_options[shortcode_cat]">
                            <option value="-1"><?php esc_html_e('Select filter category', 'meta-data-filter') ?></option>
                            <?php foreach ($mdf_terms as $term) : ?>
                                <option <?php echo selected($shortcode_options['shortcode_cat'], $term->term_id) ?> value="<?php echo esc_attr($term->term_id) ?>"><?php echo esc_html($term->name) ?></option>
                            <?php endforeach; ?>
                        </select><br />
                        <?php
                    } else {
                        ?>
                        <input type="hidden" name="shortcode_options[shortcode_cat]" value="-1" />
                        <?php
                        esc_html_e('No one filter MDTF Category created!', 'meta-data-filter');
                    }
                    ?>

                </fieldset>
            </td>
        </tr>

    </tbody>
</table>

<ul id="mdf_custom_popup_selected_filters">
    <?php
    if ($shortcode_options['shortcode_cat'] > 0) {
        echo MetaDataFilterShortcodes::draw_shortcode_html_items($shortcode_options, $html_items);
    }
    ?>

</ul>

<br /><br /><br />
<h3><?php esc_html_e('Shortcode options', 'meta-data-filter') ?></h3>

<input type="hidden" name="shortcode_options[options]" value="" />
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Taxomies only in filter', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][tax_only_in_filter]" value="<?php echo (int) @$shortcode_options['options']['tax_only_in_filter']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['tax_only_in_filter']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Ignore filter-category in wp_query', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][ignore_meta_data_filter_cat]" value="<?php echo (int) @$shortcode_options['options']['ignore_meta_data_filter_cat']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['ignore_meta_data_filter_cat']): ?>checked<?php endif; ?> /> <br />
                <i><?php esc_html_e('Check this if you want to make searching by title/content in all posts of the selected post type slug!', 'meta-data-filter') ?></i>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Results output page link', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="text" class="widefat" placeholder="<?php esc_html_e('leave this field empty to use output page from the plugin settings', 'meta-data-filter') ?>" name="shortcode_options[options][search_result_page]" value="<?php echo esc_html(isset($shortcode_options['options']['search_result_page'])?$shortcode_options['options']['search_result_page']:''); ?>"><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Results output template', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="text" class="widefat" placeholder="<?php esc_html_e('leave this field empty to use output page from the plugin settings', 'meta-data-filter') ?>" name="shortcode_options[options][search_result_tpl]" value="<?php echo esc_html(isset($shortcode_options['options']['search_result_tpl'])?$shortcode_options['options']['search_result_tpl']:''); ?>"><br />
            </td>
        </tr>
        <tr valign="top">
			<?php
			$count_text = isset($shortcode_options['options']['show_items_count_text'])?$shortcode_options['options']['show_items_count_text']:''; 
			?>
            <th scope="row"><?php esc_html_e('Custom text for search results', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="text" 
					   class="widefat" 
					   placeholder="<?php esc_html_e('Example: Found &lt;span&gt;%s&lt;/span&gt; items', 'meta-data-filter') ?>" 
					   name="shortcode_options[options][show_items_count_text]" 
					   value="<?php echo  wp_kses_post(wp_unslash(str_replace('"', '\'', $count_text))); ?>"><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Custom reset link', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="text" class="widefat" placeholder="<?php esc_html_e('Custom reset link. Leave this field empty to reset the form inputs ONLY by ajax.', 'meta-data-filter') ?>" name="shortcode_options[options][custom_reset_link]" value="<?php echo esc_html(isset($shortcode_options['options']['custom_reset_link'])?$shortcode_options['options']['custom_reset_link']:''); ?>"><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Auto submit', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][auto_submit]" value="<?php echo (int) @$shortcode_options['options']['auto_submit']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['auto_submit']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('AJAX Auto recount', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][ajax_auto_submit]" value="<?php echo (int) @$shortcode_options['options']['ajax_auto_submit']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['ajax_auto_submit']): ?>checked<?php endif; ?> />&nbsp;
                <?php esc_html_e('To use this option please uncheck "Auto submit". Use it to stay on the same page and see results filtered by AJAX.', 'meta-data-filter') ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('AJAX items output', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][ajax_results]" value="<?php echo (int) @$shortcode_options['options']['ajax_results']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['ajax_results']): ?>checked<?php endif; ?> />&nbsp;
                <?php esc_html_e('To use this option please uncheck "Auto submit" and check "AJAX Auto recount"', 'meta-data-filter') ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Show count', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][show_count]" value="<?php echo (int) @$shortcode_options['options']['show_count']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['show_count']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Hide items where count is 0', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][hide_items_where_count_0]" value="<?php echo (int) @$shortcode_options['options']['hide_items_where_count_0']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['hide_items_where_count_0']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_html_e('Show reset button', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][show_reset_button]" value="<?php echo (int) @$shortcode_options['options']['show_reset_button']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['show_reset_button']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Show found items count text', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="hidden" name="shortcode_options[options][show_found_totally]" value="<?php echo (int) @$shortcode_options['options']['show_found_totally']; ?>">
                <input type="checkbox" class="mdf_shortcode_options" <?php if ((int) @$shortcode_options['options']['show_found_totally']): ?>checked<?php endif; ?> /> <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Filter button text', 'meta-data-filter') ?><br /></th>
            <td>
                <input type="text" class="widefat" name="shortcode_options[options][filter_button_text]" value="<?php echo esc_html(isset($shortcode_options['options']['filter_button_text'])? $shortcode_options['options']['filter_button_text']:''); ?>"><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Post type', 'meta-data-filter') ?><br /></th>
            <td>
                <?php
                $settings = self::get_settings();
                if (isset($settings['post_types'])) {
                    if (!empty($settings['post_types'])) {
                        ?>
                        <select name="shortcode_options[options][post_type]" onchange="alert('<?php esc_html_e('To manage by taxonomies of the selected post type you should press Update button!', 'meta-data-filter') ?>')">
                            <?php foreach ($settings['post_types'] as $post_name) : ?>
                                <option <?php echo selected(@$shortcode_options['options']['post_type'], $post_name) ?> value="<?php echo esc_attr($post_name) ?>" class="level-0"><?php echo esc_attr($post_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                    }
                }
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Taxonomies options', 'meta-data-filter') ?><br /></th>
            <td>

                <?php
                $taxonomies_tmp = get_object_taxonomies($shortcode_options['options']['post_type'], 'objects');
                $activated_taxonomies = @$shortcode_options['options']['taxonomies'];
                $output_tax_array = array();
                $taxonomies = array();
                if (!empty($taxonomies_tmp)) {
                    if (!empty($activated_taxonomies) AND is_array($activated_taxonomies)) {
                        foreach ($activated_taxonomies as $tax_key => $val) {
                            if ($val) {
                                if (!isset($taxonomies_tmp[$tax_key])) {
                                    continue;
                                }
                                $output_tax_array[$tax_key] = $taxonomies_tmp[$tax_key]->labels->name;
                            }
                        }
                    }
                    //+++
                    foreach ($taxonomies_tmp as $tax_key => $tax) {
                        if (!key_exists($tax_key, $output_tax_array)) {
                            $taxonomies[$tax_key] = $tax->labels->name;
                        }
                    }
                }
                $output_tax_array = array_merge($output_tax_array, $taxonomies);
                ?>
                <?php if (!empty($output_tax_array)): ?>
                    <ul id="mdf_shotcode_taxonomies">
                        <?php foreach ($output_tax_array as $tax_key => $tax_name) : ?>
                            <li class="mdf_shotcode_taxonomies_li">
                                <?php $is_checked = (int) @$shortcode_options['options']['taxonomies'][$tax_key]; ?>
                                <input type="hidden" name="shortcode_options[options][taxonomies][<?php echo esc_attr($tax_key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                <input type="checkbox" <?php echo checked($is_checked, 1) ?> class="mdf_shortcode_options" />&nbsp;<?php echo esc_attr($tax_name); ?><br />
                                <div class="mdtf-h5"></div>
                                <input type="text" class="mdtf-w400" placeholder="<?php esc_html_e('taxonomy custom title', 'meta-data-filter') ?>" name="shortcode_options[options][taxonomies_title][<?php echo esc_attr($tax_key) ?>]" value="<?php echo esc_html(isset($shortcode_options['options']['taxonomies_title'][$tax_key])?$shortcode_options['options']['taxonomies_title'][$tax_key]:'') ?>">&nbsp;
                                <input type="text" class="mdtf-w120" placeholder="<?php esc_html_e('block max-height', 'meta-data-filter') ?>" name="shortcode_options[options][taxonomies_checkbox_maxheight][<?php echo esc_attr($tax_key) ?>]" value="<?php echo esc_html(isset($shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_key])?$shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_key]:'') ?>">&nbsp;
                                <?php
                                $taxonomies_type = 'select';
                                if (isset($shortcode_options['options']['taxonomies_type'][$tax_key])) {
                                    $taxonomies_type = $shortcode_options['options']['taxonomies_type'][$tax_key];
                                }
                                //***
                                $types = array_merge(self::$tax_items_types, array('multi_select' => esc_html__('Multiple checkbox select', 'meta-data-filter')));
                                ?>
                                <select name="shortcode_options[options][taxonomies_type][<?php echo esc_attr($tax_key) ?>]">
                                    <?php foreach ($types as $ikey => $iname) : ?>
                                        <option <?php selected($taxonomies_type, $ikey) ?> value="<?php echo esc_attr($ikey) ?>"><?php echo esc_html($iname) ?></option>
                                    <?php endforeach; ?>
                                </select>&nbsp;
                                <?php
                                $show_child_terms = 0;
                                if (isset($shortcode_options['options']['taxonomies_show_child_terms'][$tax_key])) {
                                    $show_child_terms = $shortcode_options['options']['taxonomies_show_child_terms'][$tax_key];
                                }
                                ?>
                                <select style="<?php if ($taxonomies_type != 'select'): ?>display: none;<?php endif; ?>" name="shortcode_options[options][taxonomies_show_child_terms][<?php echo esc_attr($tax_key) ?>]">
                                    <option <?php selected($show_child_terms, 0) ?> value="0"><?php echo esc_html_e('One child below', 'meta-data-filter'); ?></option>
                                    <option <?php selected($show_child_terms, 1) ?> value="1"><?php echo esc_html_e('All childs below', 'meta-data-filter'); ?></option>
                                </select>&nbsp;
                                <?php $is_checked = (int) @$shortcode_options['options']['taxonomies'][$tax_key]; ?>

                                <input type="text" class="text mdtf-w100p" placeholder="<?php printf(esc_html__('exluded terms ids for %s. Example: 11,22,33', 'meta-data-filter'), $tax_name) ?>" name="shortcode_options[options][excluded_terms][<?php echo esc_attr($tax_key) ?>]" value="<?php echo esc_html(isset($shortcode_options['options']['excluded_terms'][$tax_key])?$shortcode_options['options']['excluded_terms'][$tax_key]:'') ?>"><br />
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Additional taxonomies conditions', 'meta-data-filter') ?><br /></th>
            <td>
                <textarea class="widefat" name="shortcode_options[options][additional_taxonomies]"><?php echo esc_textarea(isset($shortcode_options['options']['additional_taxonomies'])?$shortcode_options['options']['additional_taxonomies']:''); ?></textarea><br />
                <i><b><?php esc_html_e('Example', 'meta-data-filter') ?></b>: product_cat=96,3^boutiques=77,88. <b><?php esc_html_e('Syntax is', 'meta-data-filter') ?></b>: taxonomy_name=term_id,term_id,term_id ^ taxonomy_name2=term_id,term_id,term_id <br />...</i>
            </td>
        </tr>

        <?php
        if (!isset($shortcode_options['options']['woo_search_panel_id'])) {
            $shortcode_options['options']['woo_search_panel_id'] = '';
        }
        ?>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Sort panel', 'meta-data-filter') ?><br /></th>
            <td>
                <?php MDTF_SORT_PANEL::draw_options_select(@$shortcode_options['options']['woo_search_panel_id'], 'shortcode_options[options][woo_search_panel_id]'); ?>
            </td>
        </tr>



        <tr valign="top">
            <th scope="row"><?php esc_html_e('Shortcode front skin', 'meta-data-filter') ?><br /></th>
            <td>
                <?php $themes = MetaDataFilterShortcodes::get_sh_skins(); ?>
                <select name="shortcode_options[options][skin]">
                    <?php foreach ($themes as $theme) : ?>
                        <option <?php echo selected(@$shortcode_options['options']['skin'], $theme) ?> value="<?php echo esc_html($theme) ?>"><?php echo esc_html($theme) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>

