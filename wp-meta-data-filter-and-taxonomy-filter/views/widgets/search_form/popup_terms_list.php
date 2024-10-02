<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<h2><?php printf(esc_html__('Terms items of "%s"', 'meta-data-filter'), esc_html($tax_name)); ?></h2>
<ul>
    <li>
        <select class="mdf_popup_terms_show_how mdtf-w50p">
            <?php
            $types = array_merge(self::$tax_items_types, array('multi_select' => esc_html__('Multiple checkbox select', 'meta-data-filter')));
            ?>
            <?php foreach ($types as $key => $value) : ?>
                <option <?php if ($show_how == $key): ?>selected<?php endif; ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
        </select>&nbsp;<b><?php echo esc_html_e('Show as', 'meta-data-filter'); ?></b>:
    </li>
    <li>
        <input class="mdf_popup_terms_tax_title" type="text" name="" value="<?php echo esc_html($tax_title); ?>" /><b>&nbsp;<?php echo esc_html_e('Custom name/title for taxonomies', 'meta-data-filter'); ?></b><br />
        <i><?php echo esc_html_e('Leave this field empty to show taxonomy name as title. Use syntax like: Country^Region^City to display parents and childs with another titles in first drop-down option.', 'meta-data-filter'); ?></i>
    </li>
    <li class="mdf_selects_item" <?php if ($show_how != 'select'): ?>style="display: none;"<?php endif; ?>>
        <input class="mdf_popup_terms_select_size" type="text" name="" value="<?php echo esc_attr($select_size); ?>" />&nbsp;<b><?php echo esc_html_e('Drop-down size', 'meta-data-filter'); ?></b><br />
    </li>
    <li class="mdf_checkboxes_item" <?php if ($show_how != 'checkbox'): ?>style="display: none;"<?php endif; ?>>
        <input class="mdf_popup_terms_checkbox_max_height" placeholder="px" type="text" name="" value="<?php echo esc_html($checkbox_max_height); ?>" />&nbsp;<b><?php echo esc_html_e('Block max height', 'meta-data-filter'); ?></b><br />
        <i><?php echo esc_html_e('Set 0 if you do not want to set max-height for this taxonomy', 'meta-data-filter'); ?></i>
    </li>

    <?php
    if (!isset($show_child_terms))
    {
        $show_child_terms = 0;
    }
    ?>
    <li class="mdf_selects_item" <?php if ($show_how != 'select'): ?>style="display: none;"<?php endif; ?>>
        <select class="mdf_popup_terms_show_child_terms">
            <option <?php selected($show_child_terms, 0) ?> value="0"><?php echo esc_html_e('No', 'meta-data-filter'); ?></option>
            <option <?php selected($show_child_terms, 1) ?> value="1"><?php echo esc_html_e('Yes', 'meta-data-filter'); ?></option>
        </select>&nbsp;<b><?php echo esc_html_e('Show all child terms at once below', 'meta-data-filter'); ?></b><br />
        <table class="mdtf-w100p">
            <tr>
                <td>
                    <img src="<?php echo MetaDataFilter::get_application_uri() ?>images/show_child_terms.png" alt="<?php echo esc_html_e('Show all child terms at once below', 'meta-data-filter'); ?>" /><br />
                </td>
                <td class="mdtf-w100p">
                    <i><?php echo esc_html_e('Use this option if you are using title like Country^Region^City', 'meta-data-filter'); ?></i>
                </td>
            </tr>
        </table>
    </li>
    <li>
        <?php
        $toggles = array(
            0 => esc_html__("No toogle", 'meta-data-filter'),
            1 => esc_html__("Opened", 'meta-data-filter'),
            2 => esc_html__("Closed", 'meta-data-filter')
        );
        if (!isset($terms_section_toggle))
        {
            $terms_section_toggle = 0;
        }
        ?>
        <select name="mdf_popup_terms_section_toggles" class="mdf_popup_terms_section_toggles">
            <?php foreach ($toggles as $key => $value) : ?>
                <option <?php echo selected($terms_section_toggle, $key) ?> value="<?php echo esc_html($key) ?>"><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
        </select><br />
        <i><?php echo esc_html_e('How to use toggle with the current taxonomy block', 'meta-data-filter'); ?></i>
    </li>
</ul>

<ul class="mdf_childs_terms">
    <?php mdf_print_childs_terms($terms_list, $hidden_term, 0); ?>
</ul>


<?php

function mdf_print_childs_terms($terms, $hidden_term, $level)
{
    ?>
    <?php foreach ($terms as $term_id => $term) : ?>
        <li>
            <div>
                <b><?php esc_html_e('Name', 'meta-data-filter'); ?></b>:&nbsp;<i class="mdtf-black"><?php echo esc_html($term['name']) ?></i><br />
                <b><?php esc_html_e('Post Count', 'meta-data-filter'); ?></b>:&nbsp;<?php echo esc_html($term['count']) ?><br />
                <b><?php esc_html_e('Hide', 'meta-data-filter'); ?></b>:&nbsp;<input type="checkbox" <?php echo(in_array($term_id, $hidden_term)) ? 'checked' : '' ?> data-term-id="<?php echo esc_html($term_id) ?>" class="mdf_popup_terms_checkbox" value="0" /><br />
            </div>
            <?php if (!empty($term['childs']) AND is_array($term['childs'])): ?>
                <ul style="<?php echo(in_array($term_id, $hidden_term)) ? 'display:none' : '' ?>">
                    <?php mdf_print_childs_terms($term['childs'], $hidden_term, $level + 1); ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    <?php
}
