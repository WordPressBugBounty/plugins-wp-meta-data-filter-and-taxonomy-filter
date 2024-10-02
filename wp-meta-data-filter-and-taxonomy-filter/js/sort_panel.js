"use strict";

jQuery(function () {
    jQuery("#mdf_woo_search_values").sortable();

    jQuery('#mdf_add_woo_search_value').on('click', function () {
        var html = jQuery('#mdf_woo_search_values_input_tpl').html();
        html = html.replace(/__NAME__/gi, 'mdf_woo_search_values');
        jQuery('#mdf_woo_search_values').append(html);
        return false;
    });
    jQuery('body').on('click', '.mdf_del_woo_search_value', function () {
        jQuery(this).parents('li').hide(220, function () {
            jQuery(this).remove();
        });
        return false;
    });
});

