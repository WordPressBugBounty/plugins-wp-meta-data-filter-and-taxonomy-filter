"use strict";

jQuery(function() {
    jQuery('.mdf_search_button').on('click',function() {
        var id = parseInt(jQuery(this).data('id'), 10);
        var popup_width = parseInt(jQuery(this).data('popup-width'), 10);
        var popup_title = jQuery(this).data('popup-title');
        if (id > 0) {
            mdf_show_stat_info_popup(lang_one_moment);
            var data = {
                action: "mdf_search_button_get_content",
                shortcode_id: id
            };
            jQuery.post(ajaxurl, data, function(html) {

                mdf_hide_stat_info_popup();

                var popup_params = {
                    content: html,
                    title: popup_title,
                    overlay: true,
                    width: popup_width,
                    open: function() {
                        jQuery("#meta_data_filter_" + jQuery('.advanced_wp_popup_content').find('form').data('unique-id')).parents('.mdf_shortcode_container').replaceWith(html);
                        mdf_init_selects();
                        mdf_tooltip_init();
                    },
                    buttons: {
                        0: {
                            name: 'Close',
                            action: 'close'
                        }
                    }
                };
                pn_advanced_wp_popup_mdf.popup(popup_params);
            });
        }

        return false;
    });
});
