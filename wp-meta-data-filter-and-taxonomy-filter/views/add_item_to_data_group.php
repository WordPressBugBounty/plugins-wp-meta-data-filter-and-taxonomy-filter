<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!isset($uniqid)) {
    $uniqid = 'medafi_' . uniqid();
}
?>
<a class="delete_item_from_data_group close-drag-holder" data-item-key="<?php echo esc_attr($uniqid); ?>" href="#"></a>

<h4>
    <?php esc_html_e("Item Name", 'meta-data-filter') ?>:
</h4>

<p>
    <input type="text" placeholder="<?php esc_html_e("enter item name here", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata) ? $itemdata['name'] : "") ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][name]" />
</p>

<br />
<a class="edit-slug button button-small mdf_admin_flter_item_box_toggle" href="#"><?php esc_html_e("Toggle", 'meta-data-filter') ?></a>
<div style="display:none;" class="mdf_admin_flter_item_box">

    <h4><?php esc_html_e("Item Type", 'meta-data-filter') ?>:&nbsp;</h4>

    <p>
        <label class="sel">
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][type]" class="data_group_item_select">
                <?php foreach (self::$items_types as $key => $name) : ?>
                    <option <?php echo(isset($itemdata) ? ($itemdata['type'] == $key ? "selected" : "") : "") ?> value="<?php echo esc_attr($key) ?>"><?php esc_html_e($name) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>

    <br />

    <div class="data_group_item_html">

        <div class="data_group_item_template_slider data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'slider' ? "block" : "none") : "none") ?>">
            <h4><?php esc_html_e("From^To", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("example", 'meta-data-filter') ?>:0^100" value="<?php echo esc_html(isset($itemdata) ? $itemdata['slider'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][slider]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Slider step", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("0 or empty mean auto step", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['slider_step']) ? $itemdata['slider_step'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][slider_step]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Prefix", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("Example: $", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['slider_prefix']) ? $itemdata['slider_prefix'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][slider_prefix]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Postfix", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("Example: €", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['slider_postfix']) ? $itemdata['slider_postfix'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][slider_postfix]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Prettify", 'meta-data-filter') ?></h4>
            <?php $slider_prettify = (isset($itemdata['slider_prettify']) ? $itemdata['slider_prettify'] : 1) ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][slider_prettify]">
                <option value="1" <?php if ($slider_prettify == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
                <option value="0" <?php if ($slider_prettify == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
            </select><br />




            <h4><?php esc_html_e("Search inside range", 'meta-data-filter') ?></h4>
            <?php $slider_range_value = (isset($itemdata['slider_range_value']) ? (int) $itemdata['slider_range_value'] : 0) ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][slider_range_value]">
                <option value="0" <?php if ($slider_range_value == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                <option value="1" <?php if ($slider_range_value == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
            </select><br />
            <i><?php esc_html_e("Enabling this mode allows you to set 2 values in post for range-slider - RANGE 'From' and 'To'. Not possible to reflect this. On the front range-slider is single! Doesn work with WooCommerce functionality.", 'meta-data-filter') ?></i>


            <?php if (class_exists('WooCommerce')): ?>
                <h4><?php esc_html_e("This is woo price, and I want to set range from min price to max price from database", 'meta-data-filter') ?></h4>
                <?php $woo_price_auto = (isset($itemdata['woo_price_auto']) ? $itemdata['woo_price_auto'] : 0) ?>
                <select name="html_item[<?php echo esc_attr($uniqid) ?>][woo_price_auto]">
                    <option value="0" <?php if ($woo_price_auto == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                    <option value="1" <?php if ($woo_price_auto == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
                </select><br />
<?php endif; ?>

            <div style="display: none;">
                <h4><?php esc_html_e("Is multi value", 'meta-data-filter') ?></h4>
<?php $slider_multi_value = (isset($itemdata['slider_multi_value']) ? (int) $itemdata['slider_multi_value'] : 0) ?>
                <select name="html_item[<?php echo esc_attr($uniqid) ?>][slider_multi_value]">
                    <option value="0" <?php if ($slider_multi_value == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                    <option value="1" <?php if ($slider_multi_value == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
                </select><br />
                <i><?php esc_html_e("ATTENTION: Do not use it if you do not need!! It takes more resources. Use this mode when you have not a lot of items to filter (about 100)", 'meta-data-filter') ?></i><br />
                <i><?php esc_html_e("Enabling this mode allows you to set more than 1 value in item for range-slider. For example: 34,45,96 - through comma.", 'meta-data-filter') ?></i>
            </div>
        </div>

        <!--start range  select   --> 

        <div class="data_group_item_template_range_select data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'range_select' ? "block" : "none") : "none") ?>">
            <h4><?php esc_html_e("From^To", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("example", 'meta-data-filter') ?>:0^100" value="<?php echo esc_html(isset($itemdata) ? $itemdata['range_select'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][range_select]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Slider step", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("0 or empty mean auto step", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['range_select_step']) ? $itemdata['range_select_step'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][range_select_step]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Prefix", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("Example: $", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['range_select_prefix']) ? $itemdata['range_select_prefix'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][range_select_prefix]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Postfix", 'meta-data-filter') ?></h4>
            <input type="text" placeholder="<?php esc_html_e("Example: €", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['range_select_postfix']) ? $itemdata['range_select_postfix'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][range_select_postfix]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Prettify", 'meta-data-filter') ?></h4>

<?php if (class_exists('WooCommerce')): ?>
                <h4><?php esc_html_e("This is woo price, and I want to set range from min price to max price from database", 'meta-data-filter') ?></h4>
                <?php $woo_price_auto = (isset($itemdata['woo_price_auto_range_select']) ? $itemdata['woo_price_auto_range_select'] : 0) ?>
                <select name="html_item[<?php echo esc_attr($uniqid) ?>][woo_price_auto_range_select]">
                    <option value="0" <?php if ($woo_price_auto == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                    <option value="1" <?php if ($woo_price_auto == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
                </select><br />
<?php endif; ?>

            <div style="display: none;">
                <h4><?php esc_html_e("Is multi value", 'meta-data-filter') ?></h4>
<?php $slider_multi_value = (isset($itemdata['slider_multi_value']) ? (int) $itemdata['slider_multi_value'] : 0) ?>
                <select name="html_item[<?php echo esc_attr($uniqid) ?>][slider_multi_value]">
                    <option value="0" <?php if ($slider_multi_value == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                    <option value="1" <?php if ($slider_multi_value == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
                </select><br />
                <i><?php esc_html_e("ATTENTION: Do not use it if you do not need!! It takes more resources. Use this mode when you have not a lot of items to filter (about 100)", 'meta-data-filter') ?></i><br />
                <i><?php esc_html_e("Enabling this mode allows you to set more than 1 value in item for range-slider. For example: 34,45,96 - through comma.", 'meta-data-filter') ?></i>
            </div>
        </div>
        <!-- //end range  select   --> 


        <!-- start search by author  --> 

        <div class="data_group_item_template_by_author data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'by_author' ? "block" : "none") : "none") ?>">
            <i><?php esc_html_e("Show search by author(select)", 'meta-data-filter') ?></i>
        </div>
        <!-- //end search by author    --> 
        <!-- start search label  --> 
        <div class="data_group_item_template_label data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'label' ? "block" : "none") : "none") ?>;">
            <input type="hidden" value="<?php echo(isset($itemdata) ? $itemdata['label'] : 0) ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][label]" />
            <input style="display: none" type="checkbox" <?php echo(isset($itemdata) ? ($itemdata['label'] ? "checked" : "") : "") ?> class="mdf_option_label" />
        </div>

        <!-- //end search label    --> 
        <div class="data_group_item_template_checkbox data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'checkbox' ? "block" : "none") : "none") ?>;">
            <input type="hidden" value="<?php echo esc_html(isset($itemdata) ? $itemdata['checkbox'] : 0) ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][checkbox]" />
            <input style="display: none" type="checkbox" <?php echo(isset($itemdata) ? ($itemdata['checkbox'] ? "checked" : "") : "") ?> class="mdf_option_checkbox" />
        </div>
        <div class="data_group_item_template_textinput data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'textinput' ? "block" : "none") : "none") ?>;">
            <input type="text" placeholder="<?php esc_html_e("enter placeholder text", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['textinput']) ? $itemdata['textinput'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][textinput]" />&nbsp;
<?php $target = (isset($itemdata['textinput_target']) ? $itemdata['textinput_target'] : 'self'); ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][textinput_target]" class="mdf_textinput_tag_selector">
                <option <?php selected('self', $target) ?> value="self"><?php esc_html_e("self", 'meta-data-filter') ?></option>
                <option <?php selected('title', $target) ?> value="title"><?php esc_html_e("post title", 'meta-data-filter') ?></option>
                <option <?php selected('content', $target) ?> value="content"><?php esc_html_e("post content", 'meta-data-filter') ?></option>
                <option <?php selected('title_or_content', $target) ?> value="title_or_content"><?php esc_html_e("title or content on the same time", 'meta-data-filter') ?></option>
                <option <?php selected('title_and_content', $target) ?> value="title_and_content"><?php esc_html_e("title and content on the same time", 'meta-data-filter') ?></option>
                <!-- <option <?php selected('tag', $target) ?> value="tag"><?php esc_html_e("tag", 'meta-data-filter') ?></option> -->
            </select>&nbsp;



<?php $textinput_mode = (isset($itemdata['textinput_mode']) ? $itemdata['textinput_mode'] : 'like'); ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][textinput_mode]">
                <option <?php selected('like', $textinput_mode) ?> value="like"><?php esc_html_e("like", 'meta-data-filter') ?></option>
                <option <?php selected('exact', $textinput_mode) ?> value="exact"><?php esc_html_e("exact", 'meta-data-filter') ?></option>
            </select>&nbsp;
<?php $textinput_inback_display_as = (isset($itemdata['textinput_inback_display_as']) ? $itemdata['textinput_inback_display_as'] : 'textinput'); ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][textinput_inback_display_as]" class="mdf_textinput_display_as_selector">
                <option <?php selected('textinput', $textinput_inback_display_as) ?> value="textinput"><?php esc_html_e("show as textinput in backend", 'meta-data-filter') ?></option>
                <option <?php selected('textarea', $textinput_inback_display_as) ?> value="textarea"><?php esc_html_e("show as textarea in backend", 'meta-data-filter') ?></option>
            </select>
        </div>
        <div class="data_group_item_template_calendar data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'calendar' ? "block" : "none") : "none") ?>;">

<?php esc_html_e("jQuery-ui calendar: from - to", 'meta-data-filter'); ?>

        </div>
        <div class="data_group_item_template_select data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'select' ? "block" : "none") : "none") ?>;">
            <h4><?php esc_html_e("Drop-down size", 'meta-data-filter') ?>:</h4>
            <input type="text" placeholder="<?php esc_html_e("enter a digit from 1 to ...", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['select_size']) ? $itemdata['select_size'] : 1) ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][select_size]" class="mdtf-w45p" /><br />
            <h4><?php esc_html_e("Drop-down first option title", 'meta-data-filter') ?>:</h4>
            <input type="text" placeholder="<?php esc_html_e("Any", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($itemdata['select_option_title']) ? $itemdata['select_option_title'] : '') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][select_option_title]" class="mdtf-w45p" /><br />
            <i><?php esc_html_e("Leave this field empty to use defined in widget string: Any", 'meta-data-filter') ?></i><br />

            <br />
            <a href="#" class="add_option_to_data_item_select button" data-append="0" data-group-index="" data-group-item-index="<?php echo esc_attr($uniqid) ?>"><?php esc_html_e("Prepend drop-down option", 'meta-data-filter') ?></a><br />
            <br /><br />
            <ul class="data_item_select_options">

<?php if (isset($itemdata)): ?>

                    <?php if (!empty($itemdata['select'])): ?>
                        <?php foreach ($itemdata['select'] as $k => $value) : ?>
                            <li>
                                <input type="text" name="html_item[<?php echo esc_attr($uniqid) ?>][select][]" value="<?php echo esc_html($value) ?>">&nbsp;<input type="text" class="data_item_select_option_key mdtf-w25p" placeholder="<?php esc_html_e("key for the current option - must be unique", 'meta-data-filter') ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][select_key][]" value="<?php echo esc_html((isset($itemdata['select_key'][$k]) AND!empty($itemdata['select_key'][$k])) ? /* sanitize_title */trim($itemdata['select_key'][$k]) : /* sanitize_title */trim($value)) ?>">&nbsp;<a class="delete_option_from_data_item_select remove-button" href="#"></a>
                                &nbsp;<img width="15" class="mdtf_drag_and_drope" src="<?php echo MetaDataFilter::get_application_uri() ?>images/drag_and_drope.png" title="<?php esc_html_e("drag and drope", 'meta-data-filter') ?>" alt="<?php esc_html_e("drag and drope", 'meta-data-filter') ?>" />
                                <br />
                            </li>
        <?php endforeach; ?>
    <?php endif; ?>

                <?php endif; ?>

            </ul>
            <br />
            <a href="#" class="add_option_to_data_item_select button" data-append="1" data-group-index="" data-group-item-index="<?php echo esc_attr($uniqid) ?>"><?php esc_html_e("Append drop-down option", 'meta-data-filter') ?></a><br />
            <br />
            <h4><?php esc_html_e("Search inside range.", 'meta-data-filter') ?></h4>
<?php $select_range_value = (isset($itemdata['select_range_value']) ? (int) $itemdata['select_range_value'] : 0) ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][select_range_value]">
                <option value="0" <?php if ($select_range_value == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                <option value="1" <?php if ($select_range_value == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
            </select><br />
            <i><?php esc_html_e("Enabling this mode allows you to set 1 decimal value in post for looking it in range. Options of such drop-down must have names as: 0-500, 501-1000, 1001-2000 and etc...", 'meta-data-filter') ?></i>

            <br />
            <h4><?php esc_html_e("Sort options by alphabetical order", 'meta-data-filter') ?></h4>
<?php $select_sort_value = (isset($itemdata['select_sort_value_by_alphabetical']) ? (int) $itemdata['select_sort_value_by_alphabetical'] : 0) ?>
            <select name="html_item[<?php echo esc_attr($uniqid) ?>][select_sort_value_by_alphabetical]">
                <option value="0" <?php if ($select_sort_value == 0): ?>selected=""<?php endif; ?>><?php esc_html_e("No", 'meta-data-filter') ?></option>
                <option value="1" <?php if ($select_sort_value == 1): ?>selected=""<?php endif; ?>><?php esc_html_e("Yes", 'meta-data-filter') ?></option>
            </select><br />
            <i><?php esc_html_e("Sort options by alphabetical order on the front", 'meta-data-filter') ?></i>



        </div>


        <div class="data_group_item_template_taxonomy data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'taxonomy' ? "block" : "none") : "none") ?>">
            <i><?php esc_html_e("By logic here must be taxonomies constructor. But, to add more flexibility to the plugin,  taxonomies are selected in the widget. So you can use the same group for different post types, or the same post type but different widgets. If there no taxonomies selected in the widget - nothing will appear on front of site in search form of the widget.", 'meta-data-filter') ?></i>
        </div>


        <div class="mdf_item_footer" <?php if (isset($itemdata) AND ( $itemdata['type'] == 'taxonomy' OR $itemdata['type'] == 'by_author')): ?>style="display: none;"<?php endif; ?>>

            <h4><?php esc_html_e("Item Description", 'meta-data-filter') ?>:</h4>

            <textarea name="html_item[<?php echo esc_attr($uniqid) ?>][description]"><?php echo(isset($itemdata) ? $itemdata['description'] : "") ?></textarea><br />


            <h4><?php esc_html_e("Item meta key", 'meta-data-filter') ?>:</h4>
            <input type="text" placeholder="medafi_total_sales" value="<?php echo esc_attr($uniqid) ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][meta_key]" class="mdtf-w45p" />&nbsp;<a href="#" class="button mdf_change_meta_key_butt" data-old-key="<?php echo esc_attr($uniqid) ?>"><?php esc_html_e("change meta key", 'meta-data-filter') ?></a><br />
            <i><b class="mdtf-red"><?php esc_html_e("Attention", 'meta-data-filter') ?></b>: <?php esc_html_e("meta key must be begin from medafi_ prefix, another way you will have problems! If you are using this key somewhere in code be ready to change it there! Change keys please for saved filter and filters items only, not for new and just created!", 'meta-data-filter') ?></i><br />
            <br />


            <div class="mdf_item_footer_reflection" <?php if (isset($itemdata) AND ( $itemdata['type'] == 'map' OR $itemdata['type'] == 'calendar' OR $itemdata['type'] == 'by_author')): ?>style="display: none;"<?php endif; ?>>

                <h4><?php esc_html_e("Reflect value from meta key", 'meta-data-filter') ?>:</h4>
<?php
$is_reflected = isset($itemdata['is_reflected']) ? $itemdata['is_reflected'] : 0;
$reflected_key = isset($itemdata['reflected_key']) ? $itemdata['reflected_key'] : '';
?>
                <input type="hidden" name="html_item[<?php echo esc_attr($uniqid) ?>][is_reflected]" value="<?php echo esc_html($is_reflected) ?>" />
                <input type="checkbox" class="mdf_is_reflected" <?php if ($is_reflected): ?>checked<?php endif; ?> />&nbsp;<input type="text" <?php if (!$is_reflected): ?>disabled<?php endif; ?> placeholder="<?php if (!$is_reflected): ?>disabled<?php else: ?>enabled<?php endif; ?>" value="<?php echo trim(esc_attr($reflected_key)) ?>" name="html_item[<?php echo esc_attr($uniqid) ?>][reflected_key]" class="mdtf-w45p" /><br />
                <i><?php esc_html_e("Example: _price, where _price is reflected meta field key in products of woocommerce. Reflection synchronizes data with already existing meta keys and makes them searchable!", 'meta-data-filter') ?></i>

            </div>
        </div>

        <div class="data_group_item_template_map data_group_input_type" style="display: <?php echo(isset($itemdata) ? ($itemdata['type'] == 'map' ? "block" : "none") : "none") ?>;">

<?php esc_html_e("Set map info in each post you are going to filter. The data will be applyed on the google map.", 'meta-data-filter'); ?>

        </div>


<?php
global $post;
$mdf_section_id = 0;
if (is_object($post)) {
    $mdf_section_id = $post->ID;
}
?>

        <input type="hidden" name="html_item[<?php echo esc_attr($uniqid) ?>][mdf_section_id]" value="<?php echo esc_html($mdf_section_id)?>" />


    </div>

</div>

<div class="mdf-drag-place"></div>
