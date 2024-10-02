"use strict";

var mdf_stat_data = new Array();
var mdf_operative_tables = null;
jQuery(".chosen_select").chosen({width: "50%"});

jQuery(function ($) {
    mdf_stat_init_calendars();
    //reset cache of "Statistical parameters" drop-down
    jQuery("#mdf_stat_snippet option[selected]").removeAttr("selected");

    //+++
    //*** Load the Visualization API and the corechart package.
    try {
        google.charts.load('current', {'packages': ['corechart', 'bar']});
    } catch (e) {
        console.log('Google charts library not loaded! If site is on localhost just disable statistic extension in tab Extensions!');
    }
    //+++
    jQuery('.mdf_cron_system').on('change',function () {
        var state = parseInt(jQuery(this).val(), 10);
        if (state === 1) {
            //external
            jQuery('.mdf_external_cron_option').show(200);
            jQuery('.mdf_wp_cron_option').hide(200);
        } else {
            jQuery('.mdf_external_cron_option').hide(200);
            jQuery('.mdf_wp_cron_option').show(200);
        }
    });
});

//+++

//stat
function mdf_stat_draw_graphs() {
    mdf_stat_process_monitor('drawing graphs ...');

    try {
        if (mdf_stat_data.length) {
            var graph1 = {};
            //***

            var counter = 1;
            var templ_search = mdf_stat_get_request_snippets();
            if (templ_search.tax === null && templ_search.meta === null) {
                var data1 = mdf_stat_data[0];
                counter = 1;
                for (tn in data1) {
                    if (counter > parseInt(mdf_stat_vars.max_items_per_graph, 10)) {
                        break;
                    }
                    graph1[tn] = data1[tn];
                    counter++;
                }

                //+++
                var data2 = mdf_stat_data[1];
                counter = 1;
                var graph_count = 0;
                for (i in data2) {

                    var graph = {};
                    var html = "";
                    var id = 'chart_div_1_set_' + graph_count;
                    html = '<div class="mdf_stat_one_graph"><span class="mdf_stat_graph_title">' + data2[i]['tax_name'] + '</span>';
                    html += "<div id='" + id + "' style='width: 100%; height: 500px;'></div></div>";
                    jQuery('#chart_div_1_set').append(html);
                    counter = 1;

                    for (term_name in data2[i]['terms']) {
                        if (counter > parseInt(mdf_stat_vars.max_items_per_graph, 10)) {
                            break;
                        }
                        //+++
                        graph[term_name] = parseInt(data2[i]['terms'][term_name], 10);
                        counter++;
                    }

                    drawChart1(graph, id);
                    graph_count++;
                }

            } else {
                var counter = 1;
                jQuery(mdf_stat_data).each(function (i, request_block) {
                    jQuery(request_block).each(function (ii, item) {
                        if (counter > parseInt(mdf_stat_vars.max_items_per_graph, 10)) {
                            return;
                        }
                        //+++
                        if (graph1[item.vname] !== undefined) {
                            graph1[item.vname] = graph1[item.vname] + parseInt(item.val, 10);
                        } else {
                            graph1[item.vname] = parseInt(item.val, 10);
                        }

                        counter++;
                    });
                });
            }
            drawChart1(graph1, 'chart_div_1');
        }

        mdf_stat_process_monitor('finished!');
        jQuery('#mdf_stat_print_btn').show(200);
    } catch (e) {
        console.log('Looks like troubles with JavaScript!', 'meta-data-filter');
    }

    return false;
}


//+++


function drawChart1(graph1, id) {

    var data = new google.visualization.DataTable();
    data.addColumn('string', 'X');
    data.addColumn('number', 'Y');
    var rows_data = [];

    jQuery.each(graph1, function (index, value) {
        rows_data.push([index + " (" + value + ")", value]);
    });
    data.addRows(rows_data);
    // Set chart options
    var options = {
        title: 'Graph 1',
        chartArea: {left: 0, top: 0, width: "100%", height: "100%"}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById(id));
    chart.draw(data, options);
}


function drawChart2(graph2) {
    var data = google.visualization.arrayToDataTable(graph2);

    // Set chart options
    var options = {
        title: 'Graph 2',
        chartArea: {left: 0, top: 0, width: "100%", height: "100%"}
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_2'));
    chart.draw(data, options);

}
jQuery('#mdf_stat_post_type').on('change', function () {
    var data = {
        action: "draw_mdf_taxmeta_var",
        mdf_stat_post_type: jQuery(this).val(),
    };
    jQuery.post(ajaxurl, data, function (content) {
        jQuery('#mdf_stat_redraw_var').html(content);
        jQuery(".chosen_select").chosen({width: "50%"});
    });
});
jQuery('#mdf_stat_connection').on('click', function () {
    var data = {
        action: "mdf_stat_check_connection",
        mdf_stat_host: jQuery("input[name='meta_data_filter_settings[server_options][host]']").val(),
        mdf_stat_user: jQuery("input[name='meta_data_filter_settings[server_options][host_user]']").val(),
        mdf_stat_name: jQuery("input[name='meta_data_filter_settings[server_options][host_db_name]']").val(),
        mdf_stat_pswd: jQuery("input[name='meta_data_filter_settings[server_options][host_pass]']").val(),

    };
    jQuery.post(ajaxurl, data, function (content) {
        alert(content);
    });
});

jQuery('select[name="meta_data_filter_settings[cron_system]"]').on('click', function () {
    if (parseInt(jQuery(this).val()) === 1) {
        jQuery('.cron_sys_1').css('visibility', 'visible');
        jQuery('.cron_sys_0').css('visibility', 'hidden');
    } else {
        jQuery('.cron_sys_0').css('visibility', 'visible');
        jQuery('.cron_sys_1').css('visibility', 'hidden');
    }
});


function mdf_stat_get_request_snippets() {
    //*** assemble request_snippets
    var request_snippets = {};
    request_snippets.meta = jQuery('#mdf_stat_snippet_meta').val();
    request_snippets.tax = jQuery('#mdf_stat_snippet_tax').val();
    return request_snippets;
}

function mdf_stat_calculate() {

    var calendar_from = parseInt(jQuery('#mdf_stat_calendar_from').val(), 10);
    var calendar_to = parseInt(jQuery('#mdf_stat_calendar_to').val(), 10);
    var request_snippets = mdf_stat_get_request_snippets();

    jQuery('#chart_div_1').html("");
    jQuery('#chart_div_1_set').html("");
    jQuery('#woof_stat_print_btn').hide();

    if (calendar_from == 0 || calendar_to == 0) {
        alert(mdf_stat_vars.mdf_stat_sel_date_range);
        return false;
    }



    mdf_stat_data = new Array();
    jQuery('#mdf_stat_get_monitor').html("");
    mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_get_oper_tbls);
    var curr_post_type = jQuery('#mdf_stat_post_type').val();
    var data = {
        action: "mdf_get_operative_tables",
        curr_post_type: curr_post_type,
        calendar_from: calendar_from,
        calendar_to: calendar_to
    };
    jQuery.post(ajaxurl, data, function (tables) {
        tables = JSON.parse(tables);
        if (tables.length > 0) {
            mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_oper_tbls_prep);
            if (tables.length) {
                mdf_stat_request_tables_data(0, tables);
            }
        } else {
            mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_done);
            alert(mdf_stat_vars.mdf_stat_no_data);
        }
    });

    return false;
}

function mdf_stat_request_tables_data(index, tables) {
    var calendar_from = parseInt(jQuery('#mdf_stat_calendar_from').val(), 10);
    var calendar_to = parseInt(jQuery('#mdf_stat_calendar_to').val(), 10);
    var curr_post_type = jQuery('#mdf_stat_post_type').val();

    mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_getting_dftbls + ' ' + tables[index] + ' ...');
    var data = {
        action: "mdf_get_stat_data",
        curr_post_type: curr_post_type,
        table: tables[index],
        request_snippets: mdf_stat_get_request_snippets(),
        calendar_from: calendar_from,
        calendar_to: calendar_to
    };
    jQuery.post(ajaxurl, data, function (stat_data) {
        stat_data = JSON.parse(stat_data);
        mdf_stat_data.push(stat_data);

        //+++
        if ((index + 1) < tables.length) {
            mdf_stat_request_tables_data(index + 1, tables);
        } else {
            var templ_search = mdf_stat_get_request_snippets();
            if (templ_search.tax === null && templ_search.meta === null) {
                var data = {
                    action: "mdf_get_top_terms",
                    curr_post_type: curr_post_type,
                    mdf_stat_data: mdf_stat_data
                };
                jQuery.post(ajaxurl, data, function (stat_data) {
                    mdf_stat_data = JSON.parse(stat_data);
                    mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_done);
                    mdf_stat_draw_graphs();
                });
            } else {

                mdf_stat_process_monitor(mdf_stat_vars.mdf_stat_done);
                mdf_stat_draw_graphs();
            }
        }
    });
}


function mdf_stat_process_monitor(text) {
    jQuery('#mdf_stat_get_monitor').prepend('<li>' + text + '</li>');
}

function mdf_stat_init_calendars() {
    jQuery(".mdf_stat_calendar").datepicker(
            {
                showWeek: true,
                firstDay: mdf_stat_vars.week_first_day,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                maxDate: 'today',
                onSelect: function (selectedDate, self) {
                    var date = new Date(parseInt(self.currentYear, 10), parseInt(self.currentMonth, 10), parseInt(self.currentDay, 10), 23, 59, 59);
                    var mktime = (date.getTime() / 1000);
                    var css_class = 'mdf_stat_calendar_from';
                    if (jQuery(this).hasClass('mdf_stat_calendar_from')) {
                        css_class = 'mdf_stat_calendar_to';
                        jQuery(this).parent().find('.' + css_class).datepicker("option", "minDate", selectedDate);
                    } else {
                        jQuery(this).parent().find('.' + css_class).datepicker("option", "maxDate", selectedDate);
                    }
                    jQuery(this).prev('input[type=hidden]').val(mktime);
                }
            }
    );
    jQuery(".mdf_stat_calendar").datepicker("option", "minDate", new Date(mdf_stat_vars.min_year, mdf_stat_vars.min_month - 1, 1));
    jQuery(".mdf_stat_calendar").datepicker("option", "dateFormat", mdf_stat_vars.calendar_date_format);
    jQuery(".mdf_stat_calendar").datepicker("option", "showAnim", 'fadeIn');
    //+++
    jQuery('body').on('keyup', ".mdf_stat_calendar", function (e) {
        if (e.keyCode == 8 || e.keyCode == 46) {
            jQuery.datepicker._clearDate(this);
            jQuery(this).prev('input[type=hidden]').val("");
        }
    });

    jQuery(".mdf_stat_calendar").each(function () {
        var mktime = parseInt(jQuery(this).prev('input[type=hidden]').val(), 10);
        if (mktime > 0) {
            var date = new Date(mktime * 1000);
            jQuery(this).datepicker('setDate', new Date(date));
        }
    });

}

