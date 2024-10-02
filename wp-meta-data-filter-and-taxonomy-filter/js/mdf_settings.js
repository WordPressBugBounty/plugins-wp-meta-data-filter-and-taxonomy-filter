"use strict";

//pre-loader
jQuery(window).on('load', function () {
    jQuery(".woobe-admin-preloader").fadeOut("slow");
    jQuery(".mdf-admin-preloader").fadeOut("slow");
});
//***
jQuery(function () {
    try {
        jQuery("#tabs").tabs();
        jQuery('.mdtf-color-picker').wpColorPicker();
    } catch (e) {
        //+++
    }

    jQuery('.js_cache_count_data_clear').on('click', function () {
        jQuery(this).next('span').html('clearing ...');
        var _this = this;
        var data = {
            action: "mdf_cache_count_data_clear"
        };
        jQuery.post(ajaxurl, data, function () {
            jQuery(_this).next('span').html('cleared!');
        });

        return false;
    });
    //+++
    jQuery('.js_cache_terms_data_clear').on('click', function () {
        jQuery(this).next('span').html('clearing ...');
        var _this = this;
        var data = {
            action: "mdf_cache_terms_data_clear"
        };
        jQuery.post(ajaxurl, data, function () {
            jQuery(_this).next('span').html('cleared!');
        });

        return false;
    });
    jQuery('#mdf_show_stat_options').on('click', function () {
        jQuery('.mdf_stat_option').toggle();
    });

});
