"use strict";

var mdf_popup_terms_hidden = [];//for working with terms popup data with hidden data
var mdf_popup_terms_show_how = 'select';
var mdf_popup_terms_select_size = 1;
var mdf_popup_terms_show_child_terms = 0;
var mdf_popup_terms_section_toggles = 0;
var mdf_popup_terms_tax_title = '';
var mdf_popup_checkbox_max_height = 0;
//+++


jQuery(function () {

    jQuery('body').on('click', ".mdf_delete_filter_item", function (e) {
        jQuery(this).parent().remove();
        return false;
    });

    if (!jQuery('#pn_html_buffer').length) {
        jQuery('body').append('<div id="pn_html_buffer" class="info_popup" style="display: none;"></div>');
    }

    jQuery('body').on('click', '.meta_data_filter_tax_ul a.mdf_tax_options', function () {
        var tax_name = jQuery(this).data('tax-name');
        var id_hide = jQuery(this).data('hide');
        var id_show_how = jQuery(this).data('show-how');
        var id_select_size = jQuery(this).data('select-size');
        var name_show_child_terms = jQuery(this).data('show-child-terms');
        var name_section_toggle = jQuery(this).data('terms-section-toggle');
        var id_tax_title = jQuery(this).data('select-title');
        var id_checkbox_max_height = jQuery(this).data('checkbox-max-height');
        mdf_popup_terms_hidden = jQuery('#' + id_hide).val();
        if (mdf_popup_terms_hidden)
        {
            mdf_popup_terms_hidden = mdf_popup_terms_hidden.split(',');
        } else {
            mdf_popup_terms_hidden = [];
        }
        mdf_popup_terms_show_how = jQuery('#' + id_show_how).val();
        mdf_popup_terms_select_size = jQuery('#' + id_select_size).val();
        mdf_popup_terms_show_child_terms = jQuery('input[name="' + name_show_child_terms + '"]').val();
        mdf_popup_terms_section_toggles = jQuery('input[name="' + name_section_toggle + '"]').val();
        mdf_popup_terms_tax_title = jQuery('#' + id_tax_title).val();
        mdf_popup_checkbox_max_height = jQuery('#' + id_checkbox_max_height).val();

        var widget = jQuery(this).closest('div.widget');
        //+++
        mdf_show_stat_info_popup(mdf_lang_loading);
        var data = {
            action: "mdf_get_tax_options_in_widget",
            tax_name: tax_name,
            hidden_terms: jQuery('#' + id_hide).val(),
            show_how: mdf_popup_terms_show_how,
            select_size: mdf_popup_terms_select_size,
            show_child_terms: mdf_popup_terms_show_child_terms,
            terms_section_toggle: mdf_popup_terms_section_toggles,
            tax_title: mdf_popup_terms_tax_title,
            checkbox_max_height: mdf_popup_checkbox_max_height
        };

        jQuery.post(ajaxurl, data, function (html) {
            mdf_hide_stat_info_popup();
            var popup_params = {
                content: html,
                title: tax_name,
                overlay: true,
                open: function () {
                    //***
                },
                buttons: {
                    0: {
                        name: mdf_lang_apply,
                        action: function (__self) {
                            if (mdf_popup_terms_hidden) {
                                jQuery('#' + id_hide).val(mdf_popup_terms_hidden.join(','));
                            } else {
                                jQuery('#' + id_hide).val('');
                            }
                            //+++
                            jQuery('#' + id_show_how).val(mdf_popup_terms_show_how);
                            jQuery('#' + id_select_size).val(mdf_popup_terms_select_size);
                            jQuery('input[name="' + name_show_child_terms + '"]').val(mdf_popup_terms_show_child_terms);
                            jQuery('input[name="' + name_section_toggle + '"]').val(mdf_popup_terms_section_toggles);
                            jQuery('#' + id_tax_title).val(mdf_popup_terms_tax_title);
                            jQuery('#' + id_checkbox_max_height).val(mdf_popup_checkbox_max_height);
                            mdf_popup_terms_hidden = [];
                            mdf_popup_terms_show_how = '';
                            mdf_popup_terms_select_size = 1;
                            mdf_popup_terms_show_child_terms = 0;
                            mdf_popup_terms_section_toggles = 0;
                            mdf_popup_terms_tax_title = '',
                                    mdf_popup_checkbox_max_height = 0;
                            wpWidgets.save(widget, 0, 1, 0);
                        },
                        close: true
                    },
                    1: {
                        name: mdf_lang_close,
                        action: 'close'
                    }
                }
            };
            pn_advanced_wp_popup_mdf.popup(popup_params);
        });

        return false;
    });
    //+++work in popup terms list
    jQuery('body').on('click', '.mdf_popup_terms_checkbox', function () {
        if (jQuery(this).is(":checked")) {
            mdf_popup_terms_hidden.push(parseInt(jQuery(this).data('term-id'), 10));
            jQuery(this).closest('li').find('ul').eq(0).hide();
        } else {
            mdf_popup_terms_hidden.splice(mdf_popup_terms_hidden.indexOf(jQuery(this).data('term-id').toString()), 1);
            jQuery(this).closest('li').find('ul').eq(0).show();
        }

        return true;
    });

    jQuery('body').on('change', '.mdf_popup_terms_show_how', function () {
        mdf_popup_terms_show_how = jQuery(this).val();
    });

    jQuery('body').on('change', '.mdf_popup_terms_select_size', function () {
        mdf_popup_terms_select_size = jQuery(this).val();
    });

    jQuery('body').on('change', '.mdf_popup_terms_show_child_terms', function () {
        mdf_popup_terms_show_child_terms = jQuery(this).val();
    });

    jQuery('body').on('change', '.mdf_popup_terms_section_toggles', function () {
        mdf_popup_terms_section_toggles = jQuery(this).val();
    });

    jQuery('body').on('change', '.mdf_popup_terms_tax_title', function () {
        mdf_popup_terms_tax_title = jQuery(this).val();
    });

    jQuery('body').on('change', '.mdf_popup_terms_checkbox_max_height', function () {
        mdf_popup_checkbox_max_height = jQuery(this).val();
    });
    
    jQuery('body').on('click', '.mdf_taxonomies_options_show_count input', function () {
	if (jQuery(this).is(":checked")) {
	    jQuery(this).parent().siblings('.mdf_taxonomies_options_show_count').show(200);
	} else {
	    jQuery(this).parent().siblings('.mdf_taxonomies_options_show_count').hide(200);
	}
    });   
    
    jQuery('body').on('click','input[id*="act_without_button"]', function () {
	if (jQuery(this).is(":checked")) {
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_items_recoun"]').parent().hide(200);
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_results"]').parent().hide(200);

	} else {
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_items_recount"]').parent().show(200);
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_results"]').parent().show(200);	    

	}
    });    
    jQuery('body').on('click','input[id*="ajax_items_recount"]', function () {
	if (jQuery(this).is(":checked")) {
	    jQuery(this).parents('.widget-content').find('input[id*="act_without_button"]').parent().hide(200);
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_results"]').parent().hide(200);

	} else {
	    jQuery(this).parents('.widget-content').find('input[id*="act_without_button"]').parent().show(200);
	    jQuery(this).parents('.widget-content').find('input[id*="ajax_results"]').parent().show(200);

	}
    });

    (function ($) {
	$(document).on('widget-added', function ( event, widgetContainer ) {
	    //console.log(jQuery(widgetContainer));
	    jQuery('ul[id*="meta_data_filter_ul_"],ul[id*="meta_data_filter_tax_ul_"], [id*="mdf_filter_categories_addit_"]').sortable();

	});
    })(jQuery);

    jQuery('body').on('change', 'select[id*="meta_data_filter_slug"], select[id*="meta_data_filter_cat"]', function () {
	var is_blocks = jQuery('.wp-block-widget-area');
	if(is_blocks.length == 0) {
	    var widget = jQuery(this).closest('div.widget');
	    wpWidgets.save(widget, 0, 1, 0);
	} else {
	    
	    jQuery('.edit-widgets-header__actions .components-button.is-primary').trigger('click');
	    alert(mdtf_lang.update_widget);
	}

    });
    
    jQuery('body').on('change', '.mdf_popup_terms_show_how', function () {
        switch (jQuery(this).val()) {
            case 'select':
                jQuery('.mdf_selects_item').show(200);
                jQuery('.mdf_checkboxes_item').hide(200);
                jQuery('.mdf_popup_terms_section_toggles').show(200);
                break;
            case 'checkbox':
                jQuery('.mdf_selects_item').hide(200);
                jQuery('.mdf_checkboxes_item').show(200);
                jQuery('.mdf_popup_terms_section_toggles').show(200);
                break;
            case 'multi_select':
                jQuery('.mdf_selects_item').hide(200);
                jQuery('.mdf_checkboxes_item').show(200);
                jQuery('.mdf_popup_terms_section_toggles').hide(200);
                break;
        }
    });

});

function mdf_add_filter_item_to_widget(unique_id, field_name, select_cat_id) {
    var data = {
        action: "mdf_add_filter_item_to_widget",
        field_name: field_name,
        filter_cat: jQuery('#' + select_cat_id).val()
    };
    jQuery.post(ajaxurl, data, function (select) {
        if (select.length.trim() > 1) {
            jQuery('#meta_data_filter_ul_' + unique_id).append('<li>' + select + '</li>');
            jQuery('#meta_data_filter_ul_' + unique_id).sortable();
        } else {
            alert(lang_no_ui_sliders);
        }
    });

    return false;
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



