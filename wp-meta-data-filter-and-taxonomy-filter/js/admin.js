"use strict";

jQuery(function () {

    if (!jQuery('#pn_html_buffer').length) {
        jQuery('body').append('<div id="pn_html_buffer" class="info_popup" style="display: none;"></div>');
    }

    //***
    try {
        var sort_items_params = {
            opacity: 0.8,
            cursor: "crosshair",
            handle: '.mdf-drag-place'
        };
        jQuery("#data_group_items").sortable(sort_items_params);
        jQuery('.data_item_select_options').sortable();

        //***

        jQuery(".add_item_to_data_group").on('click', function () {
            var add_to = jQuery(this).data('add-to');
            var data = {
                action: "meta_data_filter_add_item_to_data_group"
            };
            jQuery.post(ajaxurl, data, function (template) {
                if (add_to == 'top') {
                    jQuery('#data_group_items').prepend('<li class="admin-drag-holder">' + template + '</li>');
                } else {
                    jQuery('#data_group_items').append('<li class="admin-drag-holder">' + template + '</li>');
                }

                jQuery("#data_group_items").sortable();
            });

            return false;
        });

        jQuery('body').on('click', ".delete_item_from_data_group", function () {
            jQuery(this).parent().hide(200, function () {
                jQuery(this).remove();
            });
            return false;
        });


        jQuery('body').on('change', ".data_group_item_select", function () {
            var value = jQuery(this).val();
            jQuery(this).parents('li').find(".data_group_input_type").hide(333);
            jQuery(this).parents('li').find(".data_group_item_template_" + value).show(333);
            if (value == 'taxonomy' || value == 'by_author') {
                jQuery(this).parents('li').find(".mdf_item_footer").hide(333);
            } else {
                jQuery(this).parents('li').find(".mdf_item_footer").show(333);
            }

            //+++

            if (value == 'map' || value == 'calendar') {
                jQuery(this).parents('li').find(".mdf_item_footer_reflection").hide(333);
            } else {
                jQuery(this).parents('li').find(".mdf_item_footer_reflection").show(333);
            }

        });


        jQuery('body').on('click', ".add_option_to_data_item_select", function () {
            var index = jQuery(this).data('group-item-index');
            var template = '<input type="text" value="" name="html_item[' + index + '][select][]" /><input type="text" style="display: none;" value="" name="html_item[' + index + '][select_key][]" placeholder="">&nbsp;<a href="#" class="delete_option_from_data_item_select remove-button"></a><br />';
            var append = jQuery(this).data('append');
            if (append) {
                jQuery(this).parent().find('.data_item_select_options').append('<li>' + template + '</li>');
            } else {
                jQuery(this).parent().find('.data_item_select_options').prepend('<li>' + template + '</li>');
            }

            jQuery('.data_item_select_options').sortable();
            return false;
        });


        jQuery('body').on('click', ".delete_option_from_data_item_select", function (e) {
            jQuery(this).parent().remove();
            return false;
        });



    } catch (e) {

    }

    jQuery('body').on('click', '.mdf_admin_flter_item_box_toggle', function () {
        jQuery('.mdf_admin_flter_item_box').not(jQuery(this).next('.mdf_admin_flter_item_box')).hide(200);
        jQuery(this).next('.mdf_admin_flter_item_box').slideToggle();
        return false;
    });

    jQuery('.meta_data_filter_sequence').on('change',function () {
        var data = {
            action: "meta_data_filter_set_sequence",
            post_id: jQuery(this).data('post-id'),
            sequence: jQuery(this).val(),
	    nonce_sequence: jQuery(this).data('nonce')
        };
        jQuery.post(ajaxurl, data, function (template) {
            mdf_show_info_popup(lang_updated, 1000);
        });
    });

    //+++

    jQuery('body').on('click', ".mdf_option_checkbox", function () {
        if (jQuery(this).is(":checked")) {
            jQuery(this).prev("input[type=hidden]").val(1);
            jQuery(this).next("input[type=hidden]").val(1);
            jQuery(this).val(1);
        } else {
            jQuery(this).prev("input[type=hidden]").val(0);
            jQuery(this).next("input[type=hidden]").val(0);
            jQuery(this).val(0);
        }
    });


    jQuery('#' + mdf_slug_cat).on('change',function () {
        mdf_set_post_category(this);
    });

    //change meta keys
    jQuery('body').on('click', '.mdf_change_meta_key_butt', function () {
        var old_key = jQuery(this).data('old-key');
        var new_key = jQuery(this).prev('input[type=text]').val();
        var _this = this;
        //+++
        var data = {
            action: "mdf_change_meta_key",
            post_id: mdf_current_post_id,
            old_key: old_key,
            new_key: new_key,
	    group_items_nonce: mdf_group_items_nonce
        };
        jQuery.post(ajaxurl, data, function (request) {
            request = JSON.parse(request);
            if (request.content.length > 0) {
                jQuery(_this).parents('.mdf_filter_item').html(request.content);
            }
            mdf_show_info_popup(request.notice, 5000);
        });

        return false;
    });
    //reflections
    jQuery('body').on('click', '.mdf_is_reflected', function () {
        if (jQuery(this).is(":checked")) {
            jQuery(this).prev("input[type=hidden]").val(1);
            jQuery(this).next("input[type=text]").prop('disabled', false);
            jQuery(this).next("input[type=text]").prop('placeholder', 'enabled');
            jQuery(this).val(1);
        } else {
            jQuery(this).prev("input[type=hidden]").val(0);
            jQuery(this).next("input[type=text]").prop('disabled', true);
            jQuery(this).next("input[type=text]").prop('placeholder', 'disabled');
            jQuery(this).val(0);
        }

        return true;
    });

    //calendar mdf_calendar
    mdf_init_calendars();

    //+++ for textinput filter-item in meta constructor
    jQuery('body').on('change', '.mdf_textinput_tag_selector', function () {
        if (jQuery(this).val() === 'tag') {
            jQuery(this).next('.mdf_textinput_tag').show();
        } else {
            jQuery(this).next('.mdf_textinput_tag').hide();
        }
    });
    //+++
    var mdf_section_flag = 0;
    jQuery('body').on('click', '.mdf-section-title-button', function () {
        if (mdf_section_flag) {
            return;
        }
        mdf_section_flag = 1;
        if (jQuery(this).hasClass('closed')) {
            jQuery(this).text('-');
            jQuery(this).removeClass('closed');
            jQuery(this).parents('h3').next('div').show();
        } else {
            jQuery(this).text('+');
            jQuery(this).addClass('closed');
            jQuery(this).parents('h3').next('div').hide();
        }
        mdf_section_flag = 0;
        return false;
    });


    //***
    mdf_select_options_checker();

});

function mdf_show_info_popup(text, delay) {
    jQuery("#pn_html_buffer").text(text);
    jQuery("#pn_html_buffer").fadeTo(400, 0.9);
    window.setTimeout(function () {
        jQuery("#pn_html_buffer").fadeOut(400);
    }, delay);
}

function mdf_show_stat_info_popup(text) {
    jQuery("#pn_html_buffer").text(text);
    jQuery("#pn_html_buffer").fadeTo(400, 0.9);
}


function mdf_hide_stat_info_popup() {
    window.setTimeout(function () {
        jQuery("#pn_html_buffer").fadeOut(400);
    }, 500);
}

//to assign to post its filter category
function mdf_set_post_category(_this) {
    var cat_id = jQuery(_this).val();
    if (cat_id > 0) {
        var data = {
            action: "meta_data_filter_get_data_group_topage_items",
            cat_id: jQuery(_this).val()
        };
        jQuery.post(ajaxurl, data, function (html) {
            jQuery('#meta_data_filter_area').html(html);
            mdf_init_calendars();
        });
    } else {
        jQuery('#meta_data_filter_area').html('<input type="hidden" name="page_meta_data_filter" value="" />');
    }
}

function mdf_init_calendars() {
    jQuery(".mdf_calendar").datepicker(
            {
                showWeek: true,
                firstDay: mdf_week_first_day,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                onSelect: function (selectedDate, self) {
                    var date = new Date(parseInt(self.currentYear, 10), parseInt(self.currentMonth, 10), parseInt(self.currentDay, 10), 23, 59, 59);
                    var mktime = (date.getTime() / 1000);
                    var css_class = 'mdf_calendar_from';
                    if (jQuery(this).hasClass('mdf_calendar_from')) {
                        css_class = 'mdf_calendar_to';
                        jQuery(this).parent().find('.' + css_class).datepicker("option", "minDate", selectedDate);
                    } else {
                        jQuery(this).parent().find('.' + css_class).datepicker("option", "maxDate", selectedDate);
                    }
                    jQuery(this).prev('input[type=hidden]').val(mktime);
                }
            }
    );
    jQuery(".mdf_calendar").datepicker("option", "dateFormat", mdf_calendar_date_format);
    jQuery(".mdf_calendar").datepicker("option", "showAnim", 'fadeIn');
    //+++
    jQuery('body').on('keyup', ".mdf_calendar", function (e) {
        if (e.keyCode == 8 || e.keyCode == 46) {
            jQuery.datepicker._clearDate(this);
            jQuery(this).prev('input[type=hidden]').val("");
        }
    });
    //+++
    jQuery(".mdf_calendar").each(function () {
        var mktime = parseInt(jQuery(this).prev('input[type=hidden]').val(), 10);
        if (mktime > 0) {
            var date = new Date(mktime * 1000);
            jQuery(this).datepicker('setDate', new Date(date));
        }
    });
}

//avoid the same keys in the drop-down options
function mdf_select_options_checker() {
    jQuery(".data_item_select_options").each(function () {
        var keys = jQuery(this).find('.data_item_select_option_key');
        var values = [];
        jQuery(keys).each(function (idx, item) {
            values.push(jQuery(item).val());
        });

        var no_unique_values = [];
        jQuery(values).each(function (idx, val) {
            var times = 0;
            jQuery(values).each(function (i, v) {
                if (v === val) {
                    times++;
                }
            });

            if ((times > 1 && jQuery.inArray(val, no_unique_values) == -1) || val.length == 0) {
                no_unique_values.push(val);
            }

        });

        jQuery(keys).each(function (idx, item) {
            if (jQuery.inArray(jQuery(item).val(), no_unique_values) >= 0) {
                jQuery(item).addClass('data_item_select_option_key_wrong');
            }
        });


    });
}
