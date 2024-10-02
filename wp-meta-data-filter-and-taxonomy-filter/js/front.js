"use strict";

var mdf_range_update = false;
var mdf_ajax_content_redraw_page = 0;


try {
    var mdf_woo_catalog_order_by = mdf_default_order_by;
    var mdf_woo_catalog_order = mdf_default_order;
} catch (e) {

}
//***
jQuery(function () {
    mdf_init();
});
//***
function mdf_init() {

    if (!jQuery('#pn_html_buffer').length) {
        jQuery('body').append('<div id="pn_html_buffer" class="mdf_info_popup" style="display: none;"></div>');
        jQuery('body').append('<div id="pn_html_buffer2" style="display: none;"></div>');
    }
    //+++

    mdf_hide_empty_blocks_titles();

    //+++
    mdf_tooltip_init();
    mdf_init_checkboxes_scroll();
    mdf_init_selects();
    mdf_init_checkboxes();
    mdf_init_range_select();
    mdf_init_multislect_emulator();
    //+++

    jQuery('body').on('change', '.mdf_range_min', function () {
        var slider_id = jQuery(this).data('slider-id');
        mdf_range_update = true;
        jQuery("#" + slider_id).ionRangeSlider("update", {
            from: parseFloat(jQuery(this).val(), 10)
        });
        mdf_range_update = false;
        mdf_ajax_content_redraw_page = 0;
    });


    jQuery('body').on('change', '.mdf_range_max', function () {
        var slider_id = jQuery(this).data('slider-id');
        mdf_range_update = true;
        jQuery("#" + slider_id).ionRangeSlider("update", {
            to: parseFloat(jQuery(this).val(), 10)
        });
        mdf_range_update = false;
        mdf_ajax_content_redraw_page = 0;
    });
    //css selects
    mdf_init_selects();
    //work with taxonomy
    //select
    jQuery('body').on('change', '.mdf_taxonomy', function () {
        mdf_deinit_chosen_selects();
        var tax_name = jQuery(this).data('tax-name');
        //+++
        jQuery(this).next('.mdf_taxonomy_child_container').show(200);
        var _this = this;
        var is_auto_submit = jQuery(this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit');
        var is_tax_ajax_autorecount = jQuery(this).parents('.mdf_input_container').hasClass('mdf_tax_ajax_autorecount');
        var slug = jQuery(this).parents('form').find('input[name="mdf[mdf_widget_options][slug]"]').val();
        var form = jQuery(this).parents('form');
        var tax_title = jQuery(this).data('tax_title');
        mdf_ajax_content_redraw_page = 0;
        var data = {
            action: "mdf_draw_term_childs",
            type: 'select',
            tax_name: tax_name,
            mdf_parent_id: jQuery(this).val(),
            hide: jQuery(this).data('hide'),
            page_mdf: jQuery(this).parents('form').find('.hidden_page_mdf_for_ajax').val(),
            mdf_cat: jQuery(this).parents('form').find('input[name="mdf[mdf_widget_options][meta_data_filter_cat]"]').val(),
            tax_title: tax_title,
            slug: slug,
            is_auto_submit: is_auto_submit
        };
        jQuery.post(ajaxurl, data, function (content) {
            var cont = jQuery(_this).parents(".mdf_section_tax");
            var child_id = jQuery(cont).find(".mdf_taxonomy").length + 1;
            jQuery(cont).find("[data-level-id='" + child_id + "']").hide();
            //+++
            if (is_auto_submit || is_tax_ajax_autorecount) {
                jQuery(_this).next('.mdf_taxonomy_child_container').hide();
                jQuery(_this).next('.mdf_taxonomy_child_container2').hide();
            }

            //+++

            jQuery(_this).next('.mdf_taxonomy_child_container').html(content);

            if (jQuery(_this).next('.mdf_taxonomy_child_container').next('.mdf_taxonomy_child_container2').length) {
                jQuery(_this).next('.mdf_taxonomy_child_container').next('.mdf_taxonomy_child_container2').remove();
            } else {
                jQuery(_this).next('.mdf_taxonomy_child_container').next('.mdf_taxonomy_child_container').remove();
            }

            //+++

            if (!content) {
                jQuery(_this).next('.mdf_taxonomy_child_container').hide().html(mdf_tax_loader);
            }
            if (jQuery(_this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit')) {
                jQuery(_this).parents('form').submit();
            }

            //ajax recount
            if (jQuery(form).hasClass('mdf_ajax_auto_recount')) {
                mdf_ajax_data_recount(jQuery(form).attr('id'), slug, false);
            }
        });

        return true;
    });

    //checkbox
    jQuery('body').on('change', '.mdf_taxonomy_checkbox', function () {
        var tax_name = jQuery(this).data('tax-name');
        var is_auto_submit = jQuery(this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit');
        var form = jQuery(this).parents('form');
        if (!jQuery(this).hasClass('mdf_has_childs') && !jQuery(form).hasClass('mdf_ajax_auto_recount')) {
            if (is_auto_submit) {
                jQuery(this).parents('form').submit();
            }
            return true;
        }

        //+++
        var _this = this;
        var term_id = jQuery(this).val();
        var slug = jQuery(this).parents('form').find('input[name="mdf[mdf_widget_options][slug]"]').val();
        //+++
        mdf_ajax_content_redraw_page = 0;
        if (jQuery(this).is(":checked")) {
            jQuery(this).prev("input[type=hidden]").val(term_id);

            if (!show_tax_all_childs)
            {
                if (mdf_use_custom_icheck) {
                    jQuery(_this).parent().parent().find('.mdf_taxonomy_child_container').show(200);
                } else {
                    jQuery(_this).parent().find('.mdf_taxonomy_child_container').show(200);
                }

                var data = {
                    action: "mdf_draw_term_childs",
                    type: 'checkbox',
                    tax_name: tax_name,
                    mdf_parent_id: term_id,
                    hide: jQuery(this).data('hide'),
                    page_mdf: jQuery(this).parents('form').find('.hidden_page_mdf_for_ajax').val(),
                    mdf_cat: jQuery(this).parents('form').find('input[name="mdf[mdf_widget_options][meta_data_filter_cat]"]').val(),
                    slug: slug,
                    is_auto_submit: is_auto_submit
                };
                jQuery.post(ajaxurl, data, function (content) {
                    if (is_auto_submit) {
                        jQuery(_this).parent().find('.mdf_taxonomy_child_container').hide();
                    }

                    if (mdf_use_custom_icheck) {
                        jQuery(_this).parent().parent().find('.mdf_taxonomy_child_container').html(content);
                        mdf_init_checkboxes();
                    } else {
                        jQuery(_this).parent().find('.mdf_taxonomy_child_container').html(content);
                    }

                    if (!content) {
                        jQuery(_this).parent().find('.mdf_taxonomy_child_container').hide().html(mdf_tax_loader);
                    }
                    if (jQuery(_this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit')) {
                        jQuery(_this).parents('form').submit();
                    }

                    //ajax recount
                    if (jQuery(form).hasClass('mdf_ajax_auto_recount')) {
                        mdf_ajax_data_recount(jQuery(form).attr('id'), slug, false);
                    }
                });
            } else {
                if (jQuery(_this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit')) {
                    jQuery(_this).parents('form').submit();
                }

                //ajax recount
                if (jQuery(form).hasClass('mdf_ajax_auto_recount')) {
                    mdf_ajax_data_recount(jQuery(form).attr('id'), slug, false);
                }
            }
        } else {

            if (!show_tax_all_childs) {
                if (mdf_use_custom_icheck) {
                    jQuery(_this).parent().parent().find('.mdf_taxonomy_child_container').hide().html(mdf_tax_loader);
                } else {
                    jQuery(_this).parent().find('.mdf_taxonomy_child_container').hide().html(mdf_tax_loader);
                }
            }
            //***
            if (jQuery(this).parents('.mdf_input_container').hasClass('mdf_tax_auto_submit')) {
                jQuery(this).parents('form').submit();
            }
            //ajax recount
            if (jQuery(form).hasClass('mdf_ajax_auto_recount')) {
                mdf_ajax_data_recount(jQuery(form).attr('id'), slug, false);
            }
        }

        return true;
    });

    //+++
    //for shortcode
    try {
        jQuery('.mdf_widget_found_count span').html(mdf_found_totally);
    } catch (e) {

    }

    jQuery('.mdf_sort_panel_order_by').on('change', function () {
        var val = jQuery(this).val();
        if (val == 0) {
            val = mdf_default_order_by;
        }
        var href = window.location.href;

        if (mdf_is_search_going == 1) {
            href = mdtf_remove_param('order_by', href);
            href = mdtf_remove_param('order', href);
            window.location.href = href + '&order_by=' + val + '&order=' + mdf_sort_order;
        } else {
            mdf_util_order_no_search(mdf_sort_order, val);
            return false;
        }

    });


    jQuery('.mdf_sort_panel_ordering').on('change', function () {
        var val = jQuery(this).val();
        var href = window.location.href;
        if (mdf_is_search_going == 1) {
            href = mdtf_remove_param('order_by', href);
            href = mdtf_remove_param('order', href);
            window.location.href = href + '&order_by=' + mdf_order_by + '&order=' + val;
        } else {
            mdf_util_order_no_search(val, mdf_order_by);
            return false;
        }
    });

    jQuery('.mdf_custom_filter_panel a').on('click', function () {
        var href = window.location.href;

        if (mdf_is_search_going == 1) {
            href = mdtf_remove_param('order_by', href);
            href = mdtf_remove_param('order', href);
            window.location.href = href + '&order_by=' + jQuery(this).data('order-by') + '&order=' + jQuery(this).data('order');
        } else {
            mdf_util_order_no_search(jQuery(this).data('order'), jQuery(this).data('order-by'));
            return false;
        }
        return false;
    });


    //+++
    jQuery('body').on('click', '.mdf_front_toggle', function () {
        var condition = jQuery(this).data('condition');
        if (condition == 'opened') {
            jQuery(this).removeClass('mdf_front_toggle_opened');
            jQuery(this).addClass('mdf_front_toggle_closed');
            jQuery(this).data('condition', 'closed');
            jQuery(this).text(mdf_toggle_open_sign);
            jQuery(this).parent().prev('.mdf_filter_post_blocks_toggles').val(2);
        } else {
            jQuery(this).addClass('mdf_front_toggle_opened');
            jQuery(this).removeClass('mdf_front_toggle_closed');
            jQuery(this).data('condition', 'opened');
            jQuery(this).text(mdf_toggle_close_sign);
            jQuery(this).parent().prev('.mdf_filter_post_blocks_toggles').val(1);
        }

        if (jQuery(this).parent().next('.mdf_filter_section, .mdf_tax_filter_section').hasClass('mdf_front_toggle_closed_section')) {
            jQuery(this).parent().next('.mdf_filter_section, .mdf_tax_filter_section').hide();
            jQuery(this).parent().next('.mdf_filter_section, .mdf_tax_filter_section').removeClass('mdf_front_toggle_closed_section');
        }
        jQuery(this).parent().next('.mdf_filter_section, .mdf_tax_filter_section').toggle(500);
        return false;
    });

    //activate submit button
    jQuery('body').on('click', jQuery('.mdf_widget_form, .mdf_shortcode_form').find('input'), function () {
        jQuery(this).parents('form').find("input[type=submit]").removeProp('disabled');
    });

    jQuery('body').on('change', jQuery('.mdf_widget_form, .mdf_shortcode_form').find('input'), function () {
        jQuery(this).parents('form').find("input[type=submit]").removeProp('disabled');
    });

    jQuery('body').on('change', jQuery('.mdf_widget_form, .mdf_shortcode_form').find('select'), function () {
        jQuery(this).parents('form').find("input[type=submit]").removeProp('disabled');
    });

    //+++
    jQuery('body').on('change', '.mdf_filter_categories', function () {
        var term_id = jQuery(this).val();
        //for ajax searching
        if (jQuery(this).parents('.widget-meta-data-filter').find('form').hasClass('mdf_ajax_content_redraw')) {
            var s = jQuery('#mdf_results_by_ajax').data('shortcode');
            var old_mdf_cat = jQuery(this).parents('.widget-meta-data-filter').find('form').find('input[name="mdf[mdf_widget_options][meta_data_filter_cat]"]').val();
            s = s.replace('meta_data_filter_cat=' + old_mdf_cat, 'meta_data_filter_cat=' + term_id);
            jQuery('#mdf_results_by_ajax').data('shortcode', s);
        }
        jQuery(this).parents('.widget-meta-data-filter').find('form').find('input[name="mdf[mdf_widget_options][meta_data_filter_cat]"]').val(term_id);
        jQuery(this).parents('.widget-meta-data-filter').find('form').find('input[name="meta_data_filter_cat"]').val(term_id);

        mdf_ajax_data_recount(jQuery(this).parents('.widget-meta-data-filter').find('form').attr('id'), jQuery(this).data('slug'), true);
    });

    //fix for textinput if its under ajax redraw (another way redirect works)
    jQuery('form.mdf_ajax_content_redraw .mdf_textinput, form.mdf_ajax_auto_recount .mdf_textinput').on('keydown', function (e) {
        if (e.keyCode == 13) { // enter key was pressed
            jQuery(this).blur();
            return false; // prevent execution of rest of the script + event propagation / event bubbling + prevent default behaviour
        }
    });

    //+++ remove empty taxonomies selects
    remove_empty_mdf_taxonomy();
}

//if we do sort while no searching
function mdf_util_order_no_search(order, order_by) {
    mdf_tmp_order = order;
    mdf_tmp_orderby = order_by;
    var form = jQuery('.mdf_search_form').eq(0);
    if (form.hasClass('mdf_ajax_content_redraw')) {
        mdf_ajax_data_recount(form.attr('id'), form.data('slug'), false);
    } else {
        form.submit();
    }
}

//fixed 20-07-2014
function remove_empty_mdf_taxonomy() {
    jQuery.each(jQuery('.mdf_widget_form select.mdf_taxonomy'), function (index, select) {
        var len = jQuery(select).children('option').length;
        if (len < 2) {
            jQuery(select).parent().remove();
        }
    });
}

function mdf_draw_ui_slider_items(act_without_button, uniqid) {
    var items = jQuery(".ui_slider_item_" + uniqid);
    mdf_ajax_content_redraw_page = 0;
    jQuery.each(items, function (key, item) {
        var input = jQuery(item).next('input');
        mdf_init_range_sliders(item, input, act_without_button, uniqid);
    });
}


function mdf_get_ui_slider_step(input) {
    var step = jQuery(input).data('step');
    if (!step) {
        step = Math.ceil(parseInt((jQuery(input).data('max') - jQuery(input).data('min')) / 100, 10));
    }
    return step;
}

function mdf_init_range_sliders(item, input, act_without_button, uniqid) {
    try {
        var type = jQuery(input).data('type');
        jQuery(item).ionRangeSlider({
            min: jQuery(input).data('min'),
            max: jQuery(input).data('max'),
            from: jQuery(input).data('min-now'),
            to: jQuery(input).data('max-now'),
            type: type,
            prefix: jQuery(input).data('slider-prefix'),
            postfix: jQuery(input).data('slider-postfix'),
            //maxPostfix: "+",
            prettify: jQuery(input).data('slider-prettify'),
            hideMinMax: false,
            hideFromTo: false,
            hasGrid: true,
            step: mdf_get_ui_slider_step(input),
            onFinish: function (ui) {

                jQuery(input).val(ui.fromNumber + '^' + ui.toNumber);
                jQuery(input).parent().find('.mdf_range .mdf_range_min').val(ui.fromNumber);
                jQuery(input).parent().find('.mdf_range .mdf_range_max').val(ui.toNumber);
                if (act_without_button) {
                    jQuery("#meta_data_filter_" + uniqid).submit();
                }

                //ajax recount
                if (jQuery("#meta_data_filter_" + uniqid).hasClass('mdf_ajax_auto_recount')) {
                    mdf_ajax_data_recount(jQuery("#meta_data_filter_" + uniqid).attr('id'), jQuery("#meta_data_filter_" + uniqid).data('slug'), false);
                }
                return false;
            },
            onChange: function (ui) {
                jQuery(input).val(ui.fromNumber + '^' + ui.toNumber);
                jQuery(input).parent().find('.mdf_range .mdf_range_min').val(ui.fromNumber);
                jQuery(input).parent().find('.mdf_range .mdf_range_max').val(ui.toNumber);
                jQuery(input).parents('form').find("input[type=submit]").removeProp('disabled');
                mdf_ajax_content_redraw_page = 0;
            },
            onLoad: function (ui) {
                if (mdf_range_update) {
                    jQuery(input).val(ui.fromNumber + '^' + ui.toNumber);
                    jQuery(input).parent().find('.mdf_range .mdf_range_min').val(ui.fromNumber);
                    jQuery(input).parent().find('.mdf_range .mdf_range_max').val(ui.toNumber);
                    if (act_without_button) {
                        jQuery("#meta_data_filter_" + uniqid).submit();
                    }

                    //ajax recount
                    if (jQuery("#meta_data_filter_" + uniqid).hasClass('mdf_ajax_auto_recount')) {
                        mdf_ajax_data_recount(jQuery("#meta_data_filter_" + uniqid).attr('id'), jQuery("#meta_data_filter_" + uniqid).data('slug'), false);
                    }
                    return false;
                }
            }
        });
    } catch (e) {

    }
}

function mdf_click_checkbox(_this) {
    if (jQuery(_this).is(":checked")) {
        jQuery(_this).prev("input[type=hidden]").val(1);
        jQuery(_this).next("input[type=hidden]").val(1);
        jQuery(_this).val(1);
    } else {
        jQuery(_this).prev("input[type=hidden]").val('~');
        jQuery(_this).next("input[type=hidden]").val('~');
        jQuery(_this).val('~');
    }

    return true;
}


function mdf_init_search_form(uniqid, slug, search_url, act_without_button, ajax_searching) {

    if (act_without_button === 1) {
        //checkbox actions
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_option_checkbox", function () {
            mdf_click_checkbox(this);
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });
        //select actions
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_filter_select", function () {
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });

        jQuery('body').on('click', "#meta_data_filter_" + uniqid + " .mdf_label_item", function () {
            mdf_check_labels(this);
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });

        jQuery('body').on('click', "#meta_data_filter_" + uniqid + " .mdf_tax_label_item", function () {
            mdf_tax_check_labels(this);
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });
        //textinput
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_textinput", function () {
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });

        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_range_select", function () {
            jQuery("#meta_data_filter_" + uniqid).submit();
            return true;
        });
    } else {

        jQuery("#meta_data_filter_" + uniqid + " .mdf_option_checkbox").off('click');
        jQuery("#meta_data_filter_" + uniqid + " .mdf_option_checkbox").off('change');
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_option_checkbox", function () {
	    
            mdf_click_checkbox(this);
            //recount items count by ajax
            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
            }
        });
        jQuery('body').on('click', "#meta_data_filter_" + uniqid + " .mdf_label_item", function () {
            mdf_check_labels(this);

            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
            }
        });
        jQuery('body').on('click', "#meta_data_filter_" + uniqid + " .mdf_tax_label_item", function () {
            mdf_tax_check_labels(this);
            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
            }
        });
        jQuery("#meta_data_filter_" + uniqid + " .mdf_filter_select").off('change');

        //recount items count by ajax
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_filter_select", function () {

            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
            }
        });
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_textinput", function () {
            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);

            }
        });
        jQuery('body').on('change', "#meta_data_filter_" + uniqid + " .mdf_range_select", function () {
            if (ajax_searching) {
                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
            }
        });

        //***
        redraw_ajax_pagination(slug);
    }

    //+++
    mdf_draw_ui_slider_items(act_without_button, uniqid);
    //+++

    mdf_init_submit_button(uniqid, slug, search_url);
    //calendar
    mdf_init_calendars(uniqid, act_without_button, ajax_searching, slug);
}

function mdf_init_submit_button(uniqid, slug, search_url) {

    var submit_mode = 'submit';
    var type = 'widget';
    var form_id = "meta_data_filter_" + uniqid;
    if (jQuery("#" + form_id).hasClass('mdf_shortcode_form')) {
        type = 'shortcode';
        shortcode_id = jQuery("#" + form_id).data('shortcode-id');
    }

    //+++

    jQuery('#' + form_id + ' .mdf_reset_button').on('click', function () {

        submit_mode = 'reset';
        if (jQuery("#" + form_id).hasClass('mdf_ajax_content_redraw')) {

            mdf_ajax_content_redraw_page = 0;
            mdf_ajax_data_recount(form_id, slug, true);
            return;
        }

        if (jQuery(this).hasClass('mdf_reset_ajax_bone')) {
            window.location = location.protocol + '//' + location.host + location.pathname;
            return;
        }

        if (jQuery(this).attr('href') && !jQuery("#" + form_id).hasClass('mdf_ajax_content_redraw')) {
            var href = jQuery(this).attr('href');
            if (href.length > 1) {
                window.location = href;
                return;
            }
        }

        jQuery("#" + form_id).submit();
        return false;
    });



    var is_ajaxed_reset = false;
    //check is form inserted in popup
    var is_in_popup = false;
    if (jQuery(this).parents('.advanced_wp_popup_content')) {
        is_in_popup = true;
        is_ajaxed_reset = true;
    }
    //***

    var shortcode_id = 0;
    var widget_id = 0;
    var sidebar_name = "";
    var sidebar_id = 0;


    if (type == 'widget') {
        sidebar_name = jQuery("#" + form_id).data('sidebar-name');
        sidebar_id = jQuery("#" + form_id).data('sidebar-id');
        widget_id = jQuery("#" + form_id).data('widget-id');
    }




    jQuery("#" + form_id).on('submit', function () {
        jQuery(this).find("input[type='submit'], .mdf_reset_button").replaceWith(mdf_tax_loader);
        jQuery("#" + form_id + " .mdf_one_moment_txt span").show();

        var mdf_widget_search_url = search_url + "slg=" + slug + "&";

        mdf_show_stat_info_popup(lang_one_moment);

        var data = {
            action: "mdf_encode_search_get_params",
            vars: jQuery(this).serialize(),
            mode: submit_mode,
            mdf_front_qtrans_lang: mdf_front_qtrans_lang,
            mdf_front_wpml_lang: mdf_front_wpml_lang,
            type: type,
            shortcode_id: shortcode_id,
            sidebar_id: sidebar_id,
            sidebar_name: sidebar_name,
            widget_id: widget_id,
            is_ajaxed_reset: is_ajaxed_reset
        };
        jQuery.post(ajaxurl, data, function (response) {

            if (is_ajaxed_reset && submit_mode == 'reset' && type == 'shortcode') {
                if (response.indexOf('mdf_ajax_content_redraw') != -1) {
                    //ajax content redraw
                    var parent = jQuery("#" + form_id).parent('.mdf_shortcode_container');
                    jQuery(parent).replaceWith('<div id="mdtf_replace_me"></div>');
                    jQuery('#mdtf_replace_me').html(response);
                    mdf_ajax_content_redraw_page = 0;
                    var f = jQuery('#mdtf_replace_me').find('.mdf_ajax_content_redraw').eq(0);
                    jQuery(f).parent().unwrap();
                    mdf_ajax_data_recount(jQuery(f).attr('id'), slug, false);
                } else {
                    jQuery("#" + form_id).parents('.mdf_shortcode_container').replaceWith(response);
                    mdf_init_selects();
                }
            } else {
                if (mdf_widget_search_url.substring(0, 4) == 'self') {
                    mdf_widget_search_url = mdf_widget_search_url.replace('self', (mdf_current_page_url.length > 0 ? mdf_current_page_url : window.location.href));
                }

                if (mdf_widget_search_url.match(/\?/g).length > 1) {
                    var index = mdf_widget_search_url.lastIndexOf('?');
                    mdf_widget_search_url = mdf_widget_search_url.substr(0, index) + '&' + mdf_widget_search_url.substr(index + 1);

                }
                //only for project TODO
                var redirect_url = mdf_widget_search_url + response;


                if (mdf_front_qtrans_lang.length) {
                    redirect_url = redirect_url + '&lang=' + mdf_front_qtrans_lang;
                }

                //ordering when sort panel is not while search going
                if (mdf_tmp_order != 0 && mdf_tmp_orderby != 0) {
                    redirect_url += '&order_by=' + mdf_tmp_orderby + '&order=' + mdf_tmp_order;
                }
                //+++

                window.location = redirect_url;
            }


        });

        return false;
    });
}

var mdf_ajax_lock = false;//remove twice ajax request on the same time
function mdf_ajax_data_recount(form_id, slug, simple_form_redraw, load_more) {
    if (mdf_ajax_lock) {
        return;
    }
    mdf_ajax_lock = true;
    //+++
    mdf_show_stat_info_popup(lang_one_moment);
    var type = 'widget';
    var shortcode_id = 0;
    var widget_id = 0;
    var sidebar_name = "";
    var sidebar_id = 0;

    if (jQuery("#" + form_id).hasClass('mdf_shortcode_form')) {
        type = 'shortcode';
        shortcode_id = jQuery("#" + form_id).data('shortcode-id');
    }

    if (type == 'widget') {
        sidebar_id = jQuery("#" + form_id).data('sidebar-id');
        sidebar_name = jQuery("#" + form_id).data('sidebar-name');
        widget_id = jQuery("#" + form_id).data('widget-id');
    }

    //***
    var mdf_ajax_content_redraw = false;
    if (jQuery("#" + form_id).hasClass('mdf_ajax_content_redraw')) {
        mdf_ajax_content_redraw = true;
    }

    var data = {
        action: "mdf_get_ajax_auto_recount_data",
        vars: jQuery("#" + form_id).serialize(),
        slug: slug,
        type: type,
        shortcode_id: shortcode_id,
        sidebar_id: sidebar_id,
        sidebar_name: sidebar_name,
        widget_id: widget_id,
        mode: 'submit',
        mdf_current_term_id: mdf_current_term_id,
        mdf_current_tax: mdf_current_tax,
        simple_form_redraw: (simple_form_redraw ? 1 : 0), //need for filter-category changing
        mdf_front_qtrans_lang: mdf_front_qtrans_lang,
        mdf_front_wpml_lang: mdf_front_wpml_lang,
        mdf_ajax_content_redraw: mdf_ajax_content_redraw,
        shortcode_txt: jQuery('#mdf_results_by_ajax').data('shortcode'),
        content_redraw_page: mdf_ajax_content_redraw_page,
        mdf_tmp_order: mdf_tmp_order, //only when we do sort before searching
        mdf_tmp_orderby: mdf_tmp_orderby, //only when we do sort before searching
        mdf_is_search_going: mdf_is_search_going
    };
    mdf_is_search_going = 1;
    //+++
    if ((mdf_ajax_content_redraw || data.content_redraw_page > 1) && jQuery('#mdf_results_by_ajax').length) {
        data.order_by = mdf_woo_catalog_order_by;
        data.order = mdf_woo_catalog_order;
    }
    //***
    jQuery.post(ajaxurl, data, function (response) {
        response = JSON.parse(response);
        //+++
        mdf_hide_stat_info_popup();
        if (type == 'shortcode') {
            jQuery("#" + form_id).parents('.mdf_shortcode_container').replaceWith(response.form);
        } else {
            jQuery('#pn_html_buffer2').html(response.form);
            var widget = jQuery('#pn_html_buffer2').find('.widget-meta-data-filter').clone();
            //jQuery("#" + form_id).parents('.widget-meta-data-filter').replaceWith(widget);
            jQuery("#" + form_id).parents('.widget-meta-data-filter').replaceWith(response.form);
            jQuery('#pn_html_buffer2').html("");
            mdf_draw_ui_slider_items(false, jQuery(widget).find('form').data('unique-id'));

            mdf_hide_empty_blocks_titles();
            mdf_init_submit_button(jQuery(widget).find('form').data('unique-id'), slug, jQuery(widget).find('form').data('search-url'));

        }
        remove_empty_mdf_taxonomy();
        mdf_tooltip_init();
        mdf_init_checkboxes_scroll();
        mdf_init_checkboxes();
        mdf_init_selects();
        mdf_init_multislect_emulator();
        mdf_init_range_select();
        mdf_ajax_lock = false;
        //next code row need when show disabled drop-downs - butaforia
        jQuery(".mdf_input_container").find('.mdf_taxonomy_child_container2').next('.mdf_taxonomy_child_container').remove();

        //redraw posts if we need do this by options
        if ((mdf_ajax_content_redraw || data.content_redraw_page > 0) && jQuery('#mdf_results_by_ajax').length) {
            //

            //load more
            if (typeof load_more != 'undefined' && load_more) {

                var posts = jQuery('#mdf_results_by_ajax').find('.mdtf_posts');
                if (typeof posts != 'undefined' && posts.length) {
                    jQuery(posts).append(jQuery(response.content).find('.mdtf_post'));
                    ;
                }

                var products = jQuery('#mdf_results_by_ajax').find('.products');
                if (typeof products != 'undefined' && products.length) {
                    jQuery(products).append(jQuery(response.content).find('.product'));
                }

                jQuery('#mdf_results_by_ajax').find('.tw-pagination').replaceWith(jQuery(response.content).find('.tw-pagination').last());
                //load more
            } else {
                jQuery('#mdf_results_by_ajax').html(response.content);
            }


            jQuery('.woo-pagination').remove();
            redraw_ajax_pagination(slug);
            //+++

            if (jQuery('.mdf_sort_panel_select').length) {

                jQuery('.mdf_sort_panel_order_by').on('change', function () {
                    var form_id = jQuery('.mdf_ajax_content_redraw.mdf_ajax_auto_recount').attr('id');
                    mdf_woo_catalog_order_by = jQuery(this).val();
                    mdf_ajax_data_recount(form_id, slug, false);
                });
                jQuery('.mdf_sort_panel_ordering').on('change', function () {
                    var form_id = jQuery('.mdf_ajax_content_redraw.mdf_ajax_auto_recount').attr('id');
                    mdf_woo_catalog_order = jQuery(this).val();
                    mdf_ajax_data_recount(form_id, slug, false);
                });
            }

            if (jQuery('.mdf_custom_filter_panel').length) {

                jQuery('.mdf_custom_filter_panel a').off('click');
                jQuery('.mdf_custom_filter_panel a').on('click', function () {
                    var form_id = jQuery('.mdf_ajax_content_redraw.mdf_ajax_auto_recount').attr('id');
                    mdf_woo_catalog_order_by = jQuery(this).data('order-by');
                    mdf_woo_catalog_order = jQuery(this).data('order');
                    mdf_ajax_data_recount(form_id, slug, false);

                });
            }
        }


        //fix for textinput if its under ajax redraw (another way redirect works)
        jQuery('form.mdf_ajax_content_redraw .mdf_textinput, form.mdf_ajax_auto_recount .mdf_textinput').on('keydown', function (e) {
            if (e.keyCode == 13) { // enter key was pressed
                jQuery(this).blur();
                return false; // prevent execution of rest of the script + event propagation / event bubbling + prevent default behaviour
            }
        });

        mdf_js_after_ajax_done();
        //copatibility for post messenger
        if (typeof mdf_init_posts_messenger == 'function') {
            mdf_check_request_attr();
        }
        //hide title of the empty section 
        mdf_check_empty_sections();
        // init sort panel
        container_serch_panel();

        //hide woo standard sort panel
        if (jQuery('form.woocommerce-ordering').length) {
            jQuery('form.woocommerce-ordering').hide();
        }

    });
}

//for mdf_ajax_content_redraw mode
function redraw_ajax_pagination(slug) {
    var pagination_links = jQuery('#mdf_results_by_ajax .tw-pagination li a');

    //load  more

    var load_more_page = jQuery('#mdf_results_by_ajax.mdf_load_more .tw-pagination');
    jQuery(load_more_page).find('li').hide();
    jQuery(load_more_page).find('li.next_li').last().show();
    var load_more_text = jQuery('#mdf_results_by_ajax.mdf_load_more').data('load_text');
    if (!load_more_text) {
        load_more_text = "Load more!";
    }
    jQuery(load_more_page).find('li.next_li a').text(load_more_text).append("<span class='dashicons dashicons-update'></span>");
    //load  more

    jQuery.each(pagination_links, function (i, o) {
        jQuery(o).on('click', function () {
            var load_more = false;
            mdf_ajax_content_redraw_page = parseInt(jQuery(o).attr('title'), 10);
            var form_id = jQuery('.mdf_ajax_content_redraw.mdf_ajax_auto_recount').attr('id');

            if (jQuery('#mdf_results_by_ajax').hasClass('mdf_load_more')) {
                load_more = true;
            }
            mdf_ajax_data_recount(form_id, slug, false, load_more);
            return false;
        });
    });


    if (jQuery('#mdf_results_by_ajax').data('animate')) {
        var target = jQuery('#mdf_results_by_ajax').data('animate-target');
        if (jQuery(target).length)
        {
            var top = jQuery(target).offset().top;
            jQuery('html,body').animate({scrollTop: top}, 577);
            return false;
        }
    }

    //here you can do smth after ajax is done


}


function mdf_hide_empty_blocks_titles() {
    var section = jQuery('.widget-meta-data-filter .mdf_filter_section');
    jQuery.each(section, function (index, value) {
        var count = jQuery(value).find('table').find('tr').length;
        if (!count) {
            jQuery(value).hide();
            jQuery(value).find('table').hide();
            jQuery(value).prev('h4.data-filter-section-title').hide();
        }
    });
}

function mdf_tooltip_init() {
    try {
        jQuery('.mdf_tooltip').tooltipster({
            maxWidth: tooltip_max_width,
            animation: 'fade',
            delay: 200,
            theme: 'tooltipster-' + mdf_tooltip_theme,
            touchDevices: false,
            trigger: 'hover',
            contentAsHTML: true
        });
    } catch (e) {
        //+++
    }
}


function mdf_init_checkboxes_scroll() {
    try {
        if (!mdf_use_custom_scroll_bar) {
            return;
        }
    } catch (e) {
        console.log('Just an info: ' + e);
    }
    //+++
    try {
        jQuery(".mdf_filter_section_scrolled").mCustomScrollbar('destroy');
        jQuery(".mdf_filter_section_scrolled").mCustomScrollbar({
            scrollButtons: {
                enable: true
            },
            advanced: {
                updateOnContentResize: true,
                updateOnBrowserResize: true
            },
            theme: "dark-2",
            horizontalScroll: false,
            mouseWheel: true,
            scrollType: 'pixels',
            contentTouchScroll: true
        });
    } catch (e) {
        //+++
    }
}

//by chosen js
function mdf_init_selects() {
    mdf_deinit_chosen_selects();

    try {
        if (mdf_use_chosen_js_w) {
            jQuery(".mdf_widget_form select").chosen({disable_search_threshold: 10});
	    	    	    
        }
        if (mdf_use_chosen_js_s) {
            jQuery(".mdf_shortcode_container select").chosen({disable_search_threshold: 10});
        }
	jQuery(".mdf_widget_form select").each(function (index) {
	    if (jQuery(this).find('option').length < 2) {
		jQuery(this).parent('.mdf_input_container').hide();
	    }
	});
	jQuery(".mdf_widget_form select").each(function (index) {
	    if (jQuery(this).find('option').length < 2) {
		jQuery(this).parent('.mdf_input_container').hide();
	    }
	});	
    } catch (e) {

    }
}

//http://fronteed.com/iCheck/
function mdf_init_checkboxes() {
    try {
        if (mdf_use_custom_icheck == 0) {
            return;
        }

        //+++

        var selectors = ".mdf_shortcode_form input, .mdf_widget_form input";

        jQuery(selectors).iCheck({
            checkboxClass: 'icheckbox_' + icheck_skin.skin + '-' + icheck_skin.color,
            radioClass: 'iradio_flat'
        });


        jQuery(selectors).on('ifChecked', function (event) {
            if (jQuery(this).hasClass('mdf_option_checkbox')) {
                jQuery(this).parent().prev("input[type=hidden]").val(1);
                jQuery(this).parent().next("input[type=hidden]").val(1);
                jQuery(this).val(1);
            }
            jQuery(this).trigger('change');
        });

        jQuery(selectors).on('ifUnchecked', function (event) {
            if (jQuery(this).hasClass('mdf_option_checkbox')) {
                jQuery(this).parent().prev("input[type=hidden]").val('~');
                jQuery(this).parent().next("input[type=hidden]").val('~');
                jQuery(this).val('~');
            }
            jQuery(this).trigger('change');
        });
    } catch (e) {

    }
}

function mdf_deinit_chosen_selects() {
    try {
        if (mdf_use_chosen_js_w) {
            jQuery(".mdf_widget_form select").chosen('destroy').trigger("liszt:updated");
        }
        if (mdf_use_chosen_js_s) {
            jQuery(".mdf_shortcode_container select").chosen('destroy').trigger("liszt:updated");
        }

    } catch (e) {

    }

}

function mdf_show_stat_info_popup(text) {
    if (mdtf_overlay_skin == 'default') {
        jQuery("#pn_html_buffer").text(text);
        jQuery("#pn_html_buffer").fadeTo(200, 0.9);
    } else {
        switch (mdtf_overlay_skin) {
            case 'loading-balls':
            case 'loading-bars':
            case 'loading-bubbles':
            case 'loading-cubes':
            case 'loading-cylon':
            case 'loading-spin':
            case 'loading-spinning-bubbles':
            case 'loading-spokes':

                jQuery('body').plainOverlay('show', {progress: function () {
                        return jQuery('<div id="woof_svg_load_container"><img style="height: 100%;width: 100%" src="' + mdf_plugin_url + 'images/loading-master/' + mdtf_overlay_skin + '.svg" alt=""></div>');
                    }});
                break;
            default:
                jQuery('body').plainOverlay('show', {duration: -1});
                break;
        }
    }
}


function mdf_hide_stat_info_popup() {
    if (mdtf_overlay_skin == 'default') {
        window.setTimeout(function () {
            jQuery("#pn_html_buffer").fadeOut(400);
        }, 500);
    } else {
        jQuery('body').plainOverlay('hide');
    }



}

function mdf_init_calendars(uniqid, act_without_button, ajax_searching, slug) {
    try {
        jQuery("#meta_data_filter_" + uniqid + " .mdf_calendar").datepicker(
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

                        //+++

                        if (ajax_searching) {
                            window.setTimeout(function () {
                                mdf_ajax_data_recount("meta_data_filter_" + uniqid, slug, false);
                            }, 300);
                        }

                        if (act_without_button) {
                            window.setTimeout(function () {
                                jQuery("#meta_data_filter_" + uniqid).submit();
                            }, 300);
                        }

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
                //+++
                var css_class = 'mdf_calendar_from';
                var selectedDate = jQuery(this).datepicker('getDate');
                if (jQuery(this).hasClass('mdf_calendar_from')) {
                    css_class = 'mdf_calendar_to';
                    jQuery(this).parent().find('.' + css_class).datepicker("option", "minDate", selectedDate);
                } else {
                    jQuery(this).parent().find('.' + css_class).datepicker("option", "maxDate", selectedDate);
                }
            }
        });
    } catch (e) {

    }
}

//start range  select    
function mdf_init_range_select() {

    jQuery('.mdf_range_select').on('change', function () {
        var parent = jQuery(this).parent('.mdf_range_select_cont');
        var key = parent.attr('data-key');
        var val = jQuery(this).val();
        var set_select = {};
        if (jQuery(this).attr('id') == key + "_from") {
            set_select = jQuery(parent).children("." + key + "_to");
            mdf_set_from_value(set_select, val, key);
        } else if (jQuery(this).attr('id') == key + "_to") {
            set_select = jQuery(parent).children("." + key + "_from");
            mdf_set_to_value(set_select, val, key);
        }
        return true;
    });
    mdf_init_values();
}
function mdf_init_values() {

    jQuery('.mdf_range_select_cont').each(function () {
        var key = jQuery(this).attr('data-key');
        var select_from = jQuery(this).children("." + key + "_from");
        var select_to = jQuery(this).children("." + key + "_to")
        var min_val = select_from.val();
        var max_val = select_to.val();

        if (min_val != undefined && max_val != undefined) {
            if ((+min_val) >= (+max_val)) {
                max_val = +min_val + 1
            }

            mdf_set_from_value(select_to, min_val, key);
            mdf_set_to_value(select_from, max_val, key);
        }

    })
}


function mdf_set_from_value(select, val, key) {


    jQuery(select).children('option').each(function () {
        if (+val >= +jQuery(this).val()) {
            if (jQuery(this).attr('selected') == 'selected') {
                var next = jQuery(select).parent('.mdf_range_select_cont').attr('data-step');
                jQuery(select).val(+val + next);
                jQuery(select).children('option [value="' + (+val + next) + '+"]').attr('selected', 'selected');
            }
            jQuery(this).css({'display': 'none'}
            )
        } else {
            jQuery(this).css({'display': 'block'})
        }
    });
}
function mdf_set_to_value(select, val, key) {
    jQuery(select).children('option').each(function () {
        if (+val <= +jQuery(this).val()) {
            if (jQuery(this).attr('selected') == 'selected') {
                var next = jQuery(select).parent('.mdf_range_select_cont').attr('data-step');
                select.val(+val - next);
                select.children('option [value="' + (+val - next) + '+"]').attr('selected', 'selected');
            }
            ;
            jQuery(this).css({'display': 'none'})
        } else {
            jQuery(this).css({'display': 'block'})
        }
    });

}
//end range  select       

function mdf_init_multislect_emulator() {
    jQuery(".dropdown dt a").on('click', function () {
        var is_visible = false;
        if (jQuery(this).parents('dl.dropdown').find('dd ul').css('display') != 'none') {
            is_visible = true;
        }

        jQuery('body dl.dropdown').find('dd ul').hide();

        if (!is_visible) {
            jQuery(this).parents('dl.dropdown').find('dd ul').slideToggle('fast');
        }
        return false;
    });

    jQuery(".dropdown dd ul li a").on('click', function () {
        jQuery(this).parents('dl.dropdown').find('dd ul').hide();
        return false;
    });


    jQuery(document).on('click', function (e) {
        var $clicked = jQuery(e.target);
        if (!$clicked.parents().hasClass("dropdown")) {
            jQuery('body dl.dropdown').find('dd ul').hide();
        }
    });

}

//hide title of the empty section 
function mdf_check_empty_sections() {
    if (mdf_settings_data.hide_empty_title != undefined && mdf_settings_data.hide_empty_title == 1) {
        jQuery('.data-filter-section-title').each(function (i, elem) {
            if (jQuery(elem).siblings().text().trim() == "") {
                jQuery(this).hide();
            }
        });
    }

}
jQuery(document).ready(function () {
    mdf_check_empty_sections();
    container_serch_panel()
})
//++++++++


function container_serch_panel() {

    var data = {
        action: "mdf_search_panel",
        mdf_search_terms: jQuery(".hidden_page_mdf_for_ajax").val()
    };
    jQuery.post(ajaxurl, data, function (content) {
        if (content) {
            jQuery(".container_serch_panel").html(content);
            mdf_init_tepms_panel();
        }
    });
}

// init  terms panel

function mdf_init_tepms_panel() {
    jQuery('.mdf_remove_icon').on('click', function () {
        var remove_obj = jQuery(jQuery(this).attr('data-select'));
        if (remove_obj.length < 1) {
            jQuery(this).parent('.mdf_panel_remove').parent('.mdf_terms_panel').remove();
            jQuery('.mdf_search_form input.hidden_page_mdf_for_ajax').each(function (i, elem) {

                if (jQuery(this).val() != '') {
                    jQuery(this).parent('.mdf_search_form').submit();
                    return false;
                }
            });
        }
        if (jQuery(this).attr('data-type') == "taxonomy") {
            if (jQuery(this).attr('data-html') == 'checkbox' || jQuery(this).attr('data-html') == 'multi_select') {
                remove_obj.removeAttr('checked');
            }
            if (jQuery(this).attr('data-html') == 'select') {
                jQuery(remove_obj.parent('select').parent('div').find('select.mdf_taxonomy')).val(-1);
            }
            remove_obj.parents('form').submit();
            return;
        } else if (jQuery(this).attr('data-type') === "meta_data") {

            if (jQuery(this).attr('data-html') == 'checkbox') {
                remove_obj.val('~');
                jQuery(remove_obj.parent('.mdf_input_container').find("input[type='checkbox']")).removeAttr('checked');
            }
            if (jQuery(this).attr('data-html') == 'select') {
                remove_obj.val('~');
            }
            if (jQuery(this).attr('data-html') == 'slider') {
                remove_obj.val(remove_obj.attr('data-min') + '^' + remove_obj.attr('data-max'));
            }
            if (jQuery(this).attr('data-html') == 'by_author') {
                remove_obj.val('-1');
            }
            if (jQuery(this).attr('data-html') == 'textinput') {
                remove_obj.val('');
            }
            if (jQuery(this).attr('data-html') == 'range_select') {
                remove_obj.val(jQuery(jQuery(this).attr('data-select') + ' :first').attr('value'));
                var options_to = remove_obj.siblings('.mdf_range_select_to').children("option");
                remove_obj.siblings('.mdf_range_select_to').val(jQuery(options_to[options_to.length - 1]).attr('value'));
            }
            if (jQuery(this).attr('data-html') == 'calendar') {
                remove_obj.val('');
                remove_obj.siblings("input[type='hidden']").val('');
            }

            remove_obj.parents('form').submit();

        }
    });
}

//labels

function  mdf_check_labels(item) {
    var checkbox = jQuery(item).find('input.mdf_label_term').eq(0);
    var hiden_checkbox = jQuery(item).find('input.mdf_label_hiden').eq(0);
    if (jQuery(checkbox).is(':checked')) {
        jQuery(checkbox).attr("checked", false);
        jQuery(hiden_checkbox).val('~');
        jQuery(item).removeClass("checked");
    } else {
        jQuery(checkbox).attr("checked", true);
        jQuery(item).addClass("checked");
        jQuery(hiden_checkbox).val('1');
    }

}
function  mdf_tax_check_labels(item) {
    var checkbox = jQuery(item).find('input.mdf_taxonomy_label').eq(0);
    if (jQuery(checkbox).is(':checked')) {
        jQuery(checkbox).attr("checked", false);
        jQuery(item).removeClass("checked");
    } else {
        jQuery(checkbox).attr("checked", true);
        jQuery(item).addClass("checked");
    }

}

function mdtf_remove_param(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        if (params_arr.length)
            rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}