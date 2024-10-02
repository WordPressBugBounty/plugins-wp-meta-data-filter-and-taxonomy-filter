"use strict";

jQuery(function ($) {
    $('#mdf_term_meta_form').on('submit', function () {
        var data = {
            action: "mdf_util_term_to_meta",
            type: $('#mdf_term_meta_kind').val(),
            tax: $('#mdf_term_meta_tax').val(),
            post_type: "product",
            meta_key: $('#mdf_term_meta_key').val()
        };
        jQuery.post(ajaxurl, data, function (response) {
            alert(response);
        });
        return false;
    });
});

