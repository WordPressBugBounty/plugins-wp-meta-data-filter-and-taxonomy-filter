"use strict";

jQuery(function () {
    jQuery("#mdf_add_markering_links_list").sortable();

    jQuery('#mdf_add_markering_link_prefix').on('click', function () {
        jQuery('#mdf_add_markering_links_list').append(jQuery('#mdf_prefix_input_tpl').html());
        return false;
    });
    jQuery('body').on('click', '.mdf_del_markering_link_prefix', function () {
        jQuery(this).parents('li').hide(220, function () {
            jQuery(this).remove();
        });
        return false;
    });
});

