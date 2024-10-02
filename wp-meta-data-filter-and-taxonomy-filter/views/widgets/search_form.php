<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
$unique_id = uniqid();
$settings = self::get_settings();

?>

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('title')); ?>"><?php esc_html_e('Title', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr($widget->get_field_id('title')); ?>" name="<?php echo esc_attr($widget->get_field_name('title')); ?>" value="<?php echo esc_html($instance['title']); ?>" />
</p>

<hr />

<p>
    <label for="<?php echo esc_attr($widget->get_field_id(MetaDataFilterCore::$slug_cat)); ?>"><?php esc_html_e('Meta Data Filter Category', 'meta-data-filter') ?>:</label>
    <?php
    wp_dropdown_categories(
            array(
                'hide_empty' => 0,
                'orderby' => 'name',
                'taxonomy' => MetaDataFilterCore::$slug_cat,
                'id' => $widget->get_field_id(MetaDataFilterCore::$slug_cat),
                'class' => 'widefat mdf_filter_category_selector',
                'show_option_none' => esc_html__('select category', 'meta-data-filter'),
                'name' => $widget->get_field_name(MetaDataFilterCore::$slug_cat),
                'selected' => @$instance[MetaDataFilterCore::$slug_cat]
            )
    );
    ?><br />
    <?php
    $filter_categories = get_terms(MetaDataFilterCore::$slug_cat);
    $filter_categories_saved = (array) $instance['filter_categories_addit'];
    ?>
    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('filter_categories_addit')); ?>" value="" />
    <?php if (!empty($filter_categories)): ?>
    <ul class="meta_data_filter_tax_ul" id="mdf_filter_categories_addit_<?php echo esc_attr($unique_id) ?>">
        <?php foreach ($filter_categories as $k => $fc) : ?>
            <li class="meta_data_filter_tax_ul_li">
                <input <?php checked(in_array($fc->term_id, (array) array_keys($filter_categories_saved))); ?> type="checkbox" id="<?php echo esc_attr($widget->get_field_id('filter_categories_addit')); ?>_<?php echo esc_attr($k) ?>" name="<?php echo esc_attr($widget->get_field_name('filter_categories_addit')); ?>[<?php echo esc_attr($fc->term_id) ?>]" value="true" />
                <label for="<?php echo esc_attr($widget->get_field_id('filter_categories_addit')); ?>_<?php echo esc_attr($k) ?>"><?php echo esc_html($fc->name) ?></label>
            </li>
        <?php endforeach; ?>
    </ul>
    <i><?php esc_html_e('You can check more filter categories to enable changing of it on front!', 'meta-data-filter') ?></i>
<?php endif; ?>
</p>

<hr />

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('meta_data_filter_slug')); ?>"><?php esc_html_e('Slug', 'meta-data-filter') ?>:</label>
    <?php
    if (isset($settings['post_types'])) {
        if (!empty($settings['post_types'])) {
            ?>
            <select class="widefat" id="<?php echo esc_attr($widget->get_field_id('meta_data_filter_slug')) ?>" name="<?php echo esc_attr($widget->get_field_name('meta_data_filter_slug')) ?>">
                <?php foreach ($settings['post_types'] as $post_name) : ?>
                    <option <?php if ($instance['meta_data_filter_slug'] == $post_name) echo 'selected="selected"'; ?> value="<?php echo esc_html($post_name) ?>" class="level-0"><?php echo esc_html($post_name) ?></option>
                <?php endforeach; ?>
            </select>
            <?php
        }
    }
    ?>
</p>

<p>
    <?php
    $checked = "";
    if ($instance['hide_meta_filter_values'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('hide_meta_filter_values')); ?>" name="<?php echo esc_attr($widget->get_field_name('hide_meta_filter_values')); ?>" value="true" <?php echo esc_attr($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('hide_meta_filter_values')); ?>"><?php esc_html_e('Hide meta values filter options', 'meta-data-filter') ?>:</label>
<div class="mdtf-widget-fix2"><?php esc_html_e('Hide meta values filter options necessarily if you are going to filter by taxonomies only!', 'meta-data-filter') ?></div>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['hide_tax_filter_values'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('hide_tax_filter_values')); ?>" name="<?php echo esc_attr($widget->get_field_name('hide_tax_filter_values')); ?>" value="true" <?php echo esc_attr($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('hide_tax_filter_values')); ?>"><?php esc_html_e('Hide taxonomies values filter options', 'meta-data-filter') ?>:</label>
</p>


<p>
    <label for="<?php echo esc_attr($widget->get_field_id('search_result_page')); ?>"><?php esc_html_e('Results output page link', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr($widget->get_field_id('search_result_page')); ?>" name="<?php echo esc_attr($widget->get_field_name('search_result_page')); ?>" value="<?php echo esc_html($instance['search_result_page']); ?>" /><br />
    <i><?php esc_html_e('Leave this field empty if you want to use the default value from settings. Use word [self] to redirect on the same page.', 'meta-data-filter') ?></i>
</p>


<p>
    <label for="<?php echo esc_attr($widget->get_field_id('search_result_tpl')); ?>"><?php esc_html_e('Results output template', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('search_result_tpl'); ?>" name="<?php echo esc_attr($widget->get_field_name('search_result_tpl')); ?>" value="<?php echo esc_html($instance['search_result_tpl']); ?>" /><br />
    <i><?php esc_html_e('Leave this field empty if you want to use the default value from settings.', 'meta-data-filter') ?></i>
</p>

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('reset_link')); ?>"><?php esc_html_e('Custom reset link/url', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr($widget->get_field_id('reset_link')); ?>" name="<?php echo esc_attr($widget->get_field_name('reset_link')); ?>" value="<?php echo esc_html($instance['reset_link']); ?>" /><br />
    <i><?php esc_html_e('Leave this field empty if you want to use the default value from settings. Use word [self] to redirect on the same page.', 'meta-data-filter') ?></i>
</p>

<p>
    <label for="<?php echo $widget->get_field_id('show_items_count_text'); ?>"><?php esc_html_e('Custom text for search results', 'meta-data-filter') ?>:</label>
    <input class="widefat" 
		   type="text" 
		   id="<?php echo esc_attr($widget->get_field_id('show_items_count_text')); ?>" 
		   name="<?php echo esc_attr($widget->get_field_name('show_items_count_text')); ?>" 
		   value="<?php echo wp_kses_post(wp_unslash(str_replace('"', '\'', $instance['show_items_count_text']))); ?>" /><br />
    <i><?php esc_html_e('Example: Found &lt;span&gt;%s&lt;/span&gt; items', 'meta-data-filter') ?></i>
</p>

<p>
    <?php
    $checked = "";
    if ($instance['show_found_totally'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_found_totally')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_found_totally')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_found_totally')); ?>"><?php esc_html_e('Show found items count text', 'meta-data-filter') ?>:</label>
</p>

<hr />

<h3><?php esc_html_e('Meta options', 'meta-data-filter') ?></h3>


<p style="display:none;">
    <label for="<?php echo esc_attr($widget->get_field_id('and_or')); ?>"><?php esc_html_e('AND/OR behaviour for meta filelds', 'meta-data-filter') ?>:</label>
    <?php
    $sett = array(
        'AND' => esc_html__('AND', 'meta-data-filter'),
        'OR' => esc_html__('OR', 'meta-data-filter'),
            //'BOTH'=>esc_html__('show AND/OR filter', 'meta-data-filter'),
    );
    ?>
    <select class="widefat" id="<?php echo esc_attr($widget->get_field_id('and_or')) ?>" name="<?php echo esc_attr($widget->get_field_name('and_or')) ?>">
        <?php foreach ($sett as $k => $val) : ?>
            <option <?php if ($instance['and_or'] == $k) echo 'selected="selected"'; ?> value="<?php echo esc_html($k) ?>" class="level-0"><?php echo esc_html($val) ?></option>
        <?php endforeach; ?>
    </select>

</p>

<p>
    <?php
    $checked = "";
    if ($instance['show_checkbox_items_count'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_checkbox_items_count')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_checkbox_items_count')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_checkbox_items_count')); ?>"><?php esc_html_e('Show near checkbox count of posts', 'meta-data-filter') ?>:</label>
</p>

<p>
    <?php
    $checked = "";
    if ($instance['show_select_items_count'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_select_items_count')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_select_items_count')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_select_items_count')); ?>"><?php esc_html_e('Show count of posts in select option', 'meta-data-filter') ?>:</label>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['show_slider_items_count'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_slider_items_count')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_slider_items_count')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_slider_items_count')); ?>"><?php esc_html_e('Show count of posts in range slider', 'meta-data-filter') ?>:</label>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['show_items_count_dynam'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_items_count_dynam')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_items_count_dynam')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_items_count_dynam')); ?>"><?php esc_html_e('Dynamic post items recount', 'meta-data-filter') ?>:</label>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['hide_items_where_count_0'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('hide_items_where_count_0')); ?>" name="<?php echo esc_attr($widget->get_field_name('hide_items_where_count_0')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('hide_items_where_count_0')); ?>"><?php esc_html_e('Hide items where count of items is 0', 'meta-data-filter') ?>:</label>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['show_filter_button_after_each_block'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_filter_button_after_each_block')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_filter_button_after_each_block')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_filter_button_after_each_block')); ?>"><?php esc_html_e('Show filter button after each block', 'meta-data-filter') ?>:</label>
</p>


<hr />

<h3><?php esc_html_e('Taxonomies options', 'meta-data-filter') ?></h3>


<p class="mdtf-widget-fix1">
    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies')); ?>"><b><?php esc_html_e('Taxonomies for the selected slug', 'meta-data-filter') ?></b>:</label>
    <?php
    if (empty($instance['meta_data_filter_slug'])) {
        if (empty($settings['post_types'])) {
            $settings['post_types'] = array();
        }
        $instance['meta_data_filter_slug'] = reset($settings['post_types']);
    }
    $taxonomies = get_object_taxonomies($instance['meta_data_filter_slug'], 'objects');

    if (!empty($taxonomies)) {
        ?>
        <input type="hidden" name="" value="<?php echo esc_attr($widget->get_field_name('taxonomies')) ?>[]" />
    <ul id="meta_data_filter_tax_ul_<?php echo esc_attr($unique_id) ?>" class="meta_data_filter_tax_ul">
        <?php
        //sort them as were saved
        $taxonomies_sorted = (array) $instance['taxonomies'];
        foreach ($taxonomies as $tax) {
            $taxonomies_sorted[$tax->name] = $tax->label;
        }
        if (isset($taxonomies_sorted['post_format'])) {
            unset($taxonomies_sorted['post_format']);
        }
        //removing values from not current post type
        foreach ($taxonomies_sorted as $name => $label) {
            if ($label === 'true') {
                unset($taxonomies_sorted[$name]);
            }
        }
        //+++
        ?>
        <?php foreach ($taxonomies_sorted as $name => $label) : ?>
            <li>
                <?php
                $checked = "";
                if (isset($instance['taxonomies'][$name])) {
                    $checked = 'checked="checked"';
                }
                ?>
                <div class="mdtf-fl">
                    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('taxonomies')); ?>_<?php echo esc_attr($name) ?>" name="<?php echo esc_attr($widget->get_field_name('taxonomies')); ?>[<?php echo esc_attr($name) ?>]" value="true" <?php echo esc_html($checked); ?> />
                    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies')); ?>_<?php echo esc_attr($name) ?>"><?php echo esc_html($label) ?>:</label>
                </div>
                <div class="mdtf-fr">
                    <?php
                    $show_how = 'select';
                    if (is_array($instance['taxonomies_options_show_how']) AND isset($instance['taxonomies_options_show_how'][$name])) {
                        $show_how = $instance['taxonomies_options_show_how'][$name];
                    }

                    $select_size = 1;
                    if (is_array($instance['taxonomies_options_select_size']) AND isset($instance['taxonomies_options_select_size'][$name])) {
                        $select_size = $instance['taxonomies_options_select_size'][$name];
                    }

                    $show_child_terms = 0;
                    if (is_array($instance['taxonomies_options_show_child_terms']) AND isset($instance['taxonomies_options_show_child_terms'][$name])) {
                        $show_child_terms = $instance['taxonomies_options_show_child_terms'][$name];
                    }

                    $terms_section_toggle = 0;
                    if (is_array($instance['taxonomies_options_terms_section_toggle']) AND isset($instance['taxonomies_options_terms_section_toggle'][$name])) {
                        $terms_section_toggle = $instance['taxonomies_options_terms_section_toggle'][$name];
                    }

                    $tax_title = '';
                    if (is_array($instance['taxonomies_options_tax_title']) AND isset($instance['taxonomies_options_tax_title'][$name])) {
                        $tax_title = $instance['taxonomies_options_tax_title'][$name];
                    }

                    $checkbox_max_height = 0;
                    if (is_array($instance['taxonomies_options_checkbox_max_height']) AND isset($instance['taxonomies_options_checkbox_max_height'][$name])) {
                        $checkbox_max_height = $instance['taxonomies_options_checkbox_max_height'][$name];
                    }

                    $hide = '';
                    if (is_array($instance['taxonomies_options_hide']) AND isset($instance['taxonomies_options_hide'][$name])) {
                        $hide = trim($instance['taxonomies_options_hide'][$name], ', ');
                    }
                    ?>
                    <a href="#" class="button mdf_tax_options" data-tax-name="<?php echo esc_html($name) ?>"
                       data-hide="<?php echo esc_attr($widget->get_field_id('taxonomies_options_hide')); ?>_<?php echo esc_attr($name) ?>"
                       data-show-how="<?php echo esc_attr($widget->get_field_id('taxonomies_options_show_how')); ?>_<?php echo esc_attr($name) ?>"
                       data-select-size="<?php echo esc_attr($widget->get_field_id('taxonomies_options_select_size')); ?>_<?php echo esc_attr($name) ?>"
                       data-show-child-terms="<?php echo esc_attr($widget->get_field_name('taxonomies_options_show_child_terms')); ?>[<?php echo esc_attr($name) ?>]"
                       data-terms-section-toggle="<?php echo esc_attr($widget->get_field_name('taxonomies_options_terms_section_toggle')); ?>[<?php echo esc_attr($name) ?>]"
                       data-select-title="<?php echo esc_attr($widget->get_field_id('taxonomies_options_tax_title')); ?>_<?php echo esc_attr($name) ?>"
                       data-checkbox-max-height="<?php echo esc_attr($widget->get_field_id('taxonomies_options_checkbox_max_height')); ?>_<?php echo esc_attr($name) ?>"
                       ><?php esc_html_e('Options', 'meta-data-filter'); ?></a>
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_hide')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_hide')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($hide); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_show_how')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_show_how')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($show_how); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_select_size')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_select_size')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($select_size); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_show_child_terms')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_show_child_terms')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($show_child_terms); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_terms_section_toggle')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_terms_section_toggle')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($terms_section_toggle); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_tax_title')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_tax_title')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($tax_title); ?>" />
                    <input type="hidden" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_checkbox_max_height')); ?>[<?php echo esc_attr($name) ?>]" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_checkbox_max_height')); ?>_<?php echo esc_attr($name) ?>" value="<?php echo esc_html($checkbox_max_height); ?>" />
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="mdtf-widget-fix2"><?php esc_html_e('To show slugs taxonomies you have to add in selected Meta Data Filter Category a data constructor block of type "taxonomy". It must appear one time only in search form of one widget! Otherwise maybe problems.', 'meta-data-filter'); ?></div>
    <?php
} else {
    echo '<br />';
    esc_html_e('No Taxonomies exists for selected slug', 'meta-data-filter');
}
?>
</p>

<p style="display: none;">
    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies_options_behaviour')); ?>"><?php esc_html_e('AND/OR behaviour for taxonomies', 'meta-data-filter') ?>:</label>
    <?php
    $sett = array(
        'AND' => esc_html__('AND', 'meta-data-filter'),
        'OR' => esc_html__('OR', 'meta-data-filter')
    );
    ?>
    <select class="widefat" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_behaviour')) ?>" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_behaviour') )?>">
        <?php foreach ($sett as $k => $val) : ?>
            <option <?php if ($instance['taxonomies_options_behaviour'] == $k) echo 'selected="selected"'; ?> value="<?php echo esc_html($k) ?>" class="level-0"><?php echo esc_html($val) ?></option>
        <?php endforeach; ?>
    </select>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['taxonomies_options_show_count'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_show_count')); ?>" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_show_count')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies_options_show_count')); ?>"><?php esc_html_e('Show count of posts/custom', 'meta-data-filter') ?>:</label>
</p>




<p class="mdf_taxonomies_options_show_count" <?php if (!$instance['taxonomies_options_show_count']): ?>style="display: none;"<?php endif; ?>>
    <?php
    $checked = "";
    if ($instance['taxonomies_options_post_recount_dyn'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_post_recount_dyn')); ?>" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_post_recount_dyn')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies_options_post_recount_dyn')); ?>"><?php esc_html_e('Dynamic post recount', 'meta-data-filter') ?>:</label>
</p>

<p class="mdf_taxonomies_options_show_count" <?php if (!$instance['taxonomies_options_show_count']): ?>style="display: none;"<?php endif; ?>>
    <?php
    $checked = "";
    if ($instance['taxonomies_options_hide_terms_0'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('taxonomies_options_hide_terms_0')); ?>" name="<?php echo esc_attr($widget->get_field_name('taxonomies_options_hide_terms_0')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('taxonomies_options_hide_terms_0')); ?>"><?php esc_html_e('Hide terms where count of items is 0', 'meta-data-filter') ?>:</label>
</p>


<hr />

<p>
    <?php
    $checked = "";
    if ($instance['show_reset_button'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('show_reset_button')); ?>" name="<?php echo esc_attr($widget->get_field_name('show_reset_button')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('show_reset_button')); ?>"><?php esc_html_e('Show reset button', 'meta-data-filter') ?>:</label>
</p>





<p <?php if ($instance['ajax_items_recount'] == 'true'): ?>style="display:none;"<?php endif; ?>>
    <?php
    $checked = "";
    if ($instance['act_without_button'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('act_without_button')); ?>" name="<?php echo esc_attr($widget->get_field_name('act_without_button')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('act_without_button')); ?>"><?php esc_html_e('Auto submit form', 'meta-data-filter') ?>:</label>
</p>

<p <?php if ($instance['act_without_button'] == 'true'): ?>style="display:none;"<?php endif; ?>>
    <?php
    $checked = "";
    if ($instance['ajax_items_recount'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('ajax_items_recount')); ?>" name="<?php echo esc_attr($widget->get_field_name('ajax_items_recount')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('ajax_items_recount')); ?>"><?php esc_html_e('AJAX items recount', 'meta-data-filter') ?>:</label>
</p>


<p <?php if ($instance['act_without_button'] == 'true'): ?>style="display:none;"<?php endif; ?>>
    <?php
    $checked = "";
    if ($instance['ajax_results'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr($widget->get_field_id('ajax_results')); ?>" name="<?php echo esc_attr($widget->get_field_name('ajax_results')); ?>" value="true" <?php echo esc_html($checked); ?> />
    <label for="<?php echo esc_attr($widget->get_field_id('ajax_results')); ?>"><?php esc_html_e('AJAX items output', 'meta-data-filter') ?>:</label>
</p>


<hr />

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('title_for_any')); ?>"><?php esc_html_e('Title for Any', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('title_for_any')); ?>" name="<?php echo esc_attr($widget->get_field_name('title_for_any')); ?>" value="<?php echo esc_html($instance['title_for_any']); ?>" />
</p>

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('title_for_filter_button')); ?>"><?php esc_html_e('Title for filter button', 'meta-data-filter') ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr($widget->get_field_id('title_for_filter_button')); ?>" name="<?php echo esc_attr($widget->get_field_name('title_for_filter_button')); ?>" value="<?php echo esc_html($instance['title_for_filter_button']); ?>" />
</p>
<hr />


<p>
    <label for="<?php echo esc_attr($widget->get_field_id('woo_search_panel_id')); ?>"><?php esc_html_e('Sort panel', 'meta-data-filter') ?>:</label>
    <?php MDTF_SORT_PANEL::draw_options_select($instance['woo_search_panel_id'], $widget->get_field_name('woo_search_panel_id'), $widget->get_field_id('woo_search_panel_id')); ?>
</p>

<hr />

<p>
    <label for="<?php echo esc_attr($widget->get_field_id('additional_taxonomies')); ?>"><?php esc_html_e('Additional taxonomies conditions', 'meta-data-filter') ?>:</label>
    <textarea class="widefat" id="<?php echo esc_attr($widget->get_field_id('additional_taxonomies')); ?>" name="<?php echo esc_attr($widget->get_field_name('additional_taxonomies')); ?>"><?php echo esc_html($instance['additional_taxonomies']); ?></textarea>
    <i><b><?php esc_html_e('Example', 'meta-data-filter') ?></b>: product_cat=96,3^boutiques=77,88. <b><?php esc_html_e('Syntax is', 'meta-data-filter') ?></b>: taxonomy_name=term_id,term_id,term_id ^ taxonomy_name2=term_id,term_id,term_id <br />...</i>
</p>



<hr />

<script>

    jQuery(function () {
        //DYNAMIC SCRIPT DEPENDING OF WIDGET SETTINGS
        jQuery('#meta_data_filter_ul_<?php echo esc_attr($unique_id) ?>,#meta_data_filter_tax_ul_<?php echo esc_attr($unique_id) ?>, #mdf_filter_categories_addit_<?php echo esc_attr($unique_id) ?>').sortable();
    });
</script>
</p>
