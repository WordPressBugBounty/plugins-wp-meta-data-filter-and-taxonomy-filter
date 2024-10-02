"use strict";

function mdf_init_posts_messenger() {

    jQuery('#mdf_add_subscr').attr('data-href', location.href);
    jQuery('#mdf_add_subscr').on('click', function () {
        var data = {
            action: "mdf_posts_messenger_add_subscr",
            user_id: jQuery(this).attr('data-user'),
            attr: jQuery(".hidden_page_mdf_for_ajax").val(),
            curr_link: window.location.href
        };
        jQuery.post(ajaxurl, data, function (content) {
            if (content) {
                var req = content;
                mdf_redraw_subscr(req);
            }
        });

        return false;
    });

    function mdf_redraw_subscr(data) {
        jQuery('.mdf_subscr_list ul').append(data);
        mdf_check_count_subscr();
        mdf_init_remove_btn();
    }
    mdf_init_remove_btn();
    mdf_check_request_attr();


}
mdf_init_posts_messenger();

function mdf_init_remove_btn() {
    jQuery('.mdf_remove_subscr').on('click', function () {
        if (!confirm(mdf_posts_messenger_data.mdf_confirm_lang)) {
            return false;
        }
        var data = {
            action: "mdf_posts_messenger_remove_subscr",
            user_id: jQuery(this).attr('data-user'),
            key: jQuery(this).attr('data-key')
        };

        jQuery.post(ajaxurl, data, function (content) {
            var req = JSON.parse(content);
            jQuery('.mdf_subscr_item_' + req.key).remove();
            mdf_check_count_subscr();

        });

        return false;
    });

}

function mdf_check_request_attr() {
    if (jQuery(".hidden_page_mdf_for_ajax").val() == undefined || jQuery(".hidden_page_mdf_for_ajax").val().length < 1) {
        jQuery('.mdf_add_subscr_cont').hide();
    } else {
        mdf_check_count_subscr();
    }

}

function mdf_check_count_subscr() {
    var count_li = jQuery('.mdf_subscr_list ul').first().find('li').length;
    var max_count = jQuery('.mdf_add_subscr_cont input').attr('data-count');

    if (count_li >= max_count) {
        jQuery('.mdf_add_subscr_cont').hide();
    } else {
        jQuery('.mdf_add_subscr_cont').show();
        if (jQuery(".hidden_page_mdf_for_ajax").val().length < 1 || jQuery(".hidden_page_mdf_for_ajax").val() == undefined) {
            jQuery('.mdf_add_subscr_cont').hide();
        }
    }
}