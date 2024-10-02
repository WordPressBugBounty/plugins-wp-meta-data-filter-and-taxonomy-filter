<?php
global $wp_query;
$settings = MetaDataFilterCore::get_settings();
?>
<script>
    var mdf_is_search_going =<?php echo(self::is_page_mdf_data() ? 1 : 0) ?>;
    var mdf_tmp_order = 0;
    var mdf_tmp_orderby = 0;
    //+++
    var lang_one_moment = "<?php echo esc_html((isset($settings['loading_text']) AND!empty($settings['loading_text'])) ? $settings['loading_text'] : esc_html__("One Moment ...", 'meta-data-filter')); ?>";
    var mdf_lang_loading = "<?php esc_html_e("Loading ...", 'meta-data-filter') ?>";
    var mdf_lang_cancel = "<?php esc_html_e("Cancel", 'meta-data-filter') ?>";
    var mdf_lang_close = "<?php esc_html_e("Close", 'meta-data-filter') ?>";
    var mdf_lang_apply = "<?php esc_html_e("Apply", 'meta-data-filter') ?>";
    var mdf_tax_loader = '<?php MetaDataFilterHtml::draw_tax_loader(); ?>';
    var mdf_week_first_day =<?php echo get_option('start_of_week') ?>;
    var mdf_calendar_date_format = "<?php echo esc_html((isset($settings['calendar_date_format']) AND!empty($settings['calendar_date_format'])) ? $settings['calendar_date_format'] : 'mm/dd/yy'); ?>";
    var mdf_site_url = "<?php echo site_url() ?>";
    var mdf_plugin_url = "<?php echo MetaDataFilterCore::get_application_uri() ?>";
    var mdf_default_order_by = "<?php echo esc_html(MetaDataFilterCore::$default_order_by) ?>";
    var mdf_default_order = "<?php echo esc_html(MetaDataFilterCore::$default_order) ?>";
    var show_tax_all_childs =<?php echo (int) MetaDataFilterCore::get_setting('show_tax_all_childs') ?>;
    var mdf_current_term_id = <?php
if (is_tax()) {
    if (is_object($wp_query)) {
        $term = $wp_query->queried_object;
        if (is_object($term)) {
            echo (int) $term->term_id;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>;

    var mdf_current_tax = "<?php
if (is_tax()) {
    $term = $wp_query->queried_object;
    if (is_object($term)) {
        echo esc_html($term->taxonomy);
    }
} else {
    echo "";
}
?>";
    //admin
    var lang_no_ui_sliders = "<?php esc_html_e("no ui sliders in selected mdf category", 'meta-data-filter') ?>";
    var lang_updated = "<?php esc_html_e("Updated", 'meta-data-filter') ?>";
    //+++
    var mdf_slug_cat = "<?php echo esc_html(MetaDataFilterCore::$slug_cat); ?>";

    var mdf_tooltip_theme = "<?php echo esc_html($settings['tooltip_theme'] ? $settings['tooltip_theme'] : 'shadow'); ?>";
    var tooltip_max_width = parseInt(<?php echo((int) $settings['tooltip_max_width'] ? (int) $settings['tooltip_max_width'] : 220); ?>, 10);
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var mdf_front_qtrans_lang = "";
    var mdf_front_wpml_lang = "<?php
$lang = "";
if (class_exists('SitePress')) {
    $lang = (class_exists('SitePress') ? ICL_LANGUAGE_CODE : "");
    global $sitepress;
    if ($sitepress->get_default_language() == $lang) {
        $lang = "";
    }
}
echo esc_html($lang);
?>";
    var mdf_use_chosen_js_w =<?php echo (int) MetaDataFilterCore::get_setting('use_chosen_js_w') ?>;
    var mdf_use_chosen_js_s =<?php echo (int) MetaDataFilterCore::get_setting('use_chosen_js_s') ?>;
    var mdf_use_custom_scroll_bar =<?php echo (int) MetaDataFilterCore::get_setting('use_custom_scroll_bar') ?>;
<?php if (function_exists('qtrans_getLanguage')) : ?>
        mdf_front_qtrans_lang = "<?php echo qtrans_getLanguage() ?>";
<?php else: ?>
        mdf_front_qtrans_lang = "";
<?php endif; ?>
    var mdf_current_page_url = "<?php
if (is_home() OR is_front_page()) {
    echo home_url();
} elseif (is_page() OR is_single()) {
    the_permalink();
} elseif (is_tax() AND function_exists('preg_replace')) {
    if (isset($_SERVER['SCRIPT_URI'])) {
        $u = preg_replace('/\/page\/(\d)/i', '', sanitize_text_field($_SERVER['SCRIPT_URI']));
        echo esc_url_raw($u);
    } else {
        if (isset($_SERVER['REQUEST_URI'])) {
            $u = preg_replace('/\/page\/(\d)/i', '', sanitize_text_field($_SERVER['REQUEST_URI']));
            echo esc_url_raw($u);
        }
    }
} else {
    if (isset($_SERVER['SCRIPT_URI'])) {
        echo esc_url_raw($_SERVER['SCRIPT_URI']);
    } else {
        if (isset($_SERVER['REQUEST_URI'])) {
            echo esc_url_raw($_SERVER['REQUEST_URI']);
        }
    }
}
?>";

    var mdf_sort_order = "<?php echo esc_html(isset($_GET['order']) ? MetaDataFilterCore::escape($_GET['order']) : self::$default_order) ?>";
    var mdf_order_by = "<?php echo esc_html(isset($_GET['order_by']) ? MetaDataFilterCore::escape($_GET['order_by']) : self::$default_order_by) ?>";
    var mdf_toggle_close_sign = "<?php esc_html_e(MetaDataFilterCore::get_setting('toggle_close_sign') ? MetaDataFilterCore::get_setting('toggle_close_sign') : '-') ?>";
    var mdf_toggle_open_sign = "<?php esc_html_e(MetaDataFilterCore::get_setting('toggle_open_sign') ? MetaDataFilterCore::get_setting('toggle_open_sign') : '+') ?>";
    var tab_slideout_icon = "<?php echo esc_url_raw(MetaDataFilterCore::get_setting('tab_slideout_icon') ? MetaDataFilterCore::get_setting('tab_slideout_icon') : MetaDataFilterCore::get_application_uri() . 'images/icon_button_search.png') ?>";
    var tab_slideout_icon_w = "<?php echo esc_html(MetaDataFilterCore::get_setting('tab_slideout_icon_w') ? MetaDataFilterCore::get_setting('tab_slideout_icon_w') : 146) ?>";
    var tab_slideout_icon_h = "<?php echo esc_html(MetaDataFilterCore::get_setting('tab_slideout_icon_h') ? MetaDataFilterCore::get_setting('tab_slideout_icon_h') : 131) ?>";
    var mdf_use_custom_icheck = <?php echo(MetaDataFilterCore::get_setting('use_custom_icheck') ? 1 : 0) ?>;
<?php
$icheck_skin = MetaDataFilterCore::get_setting('icheck_skin');
$icheck_skin = explode('_', $icheck_skin);
?>
    var icheck_skin = {};
    icheck_skin.skin = "<?php echo esc_html($icheck_skin[0]) ?>";
    icheck_skin.color = "<?php echo esc_html($icheck_skin[1]) ?>";


    var mdtf_overlay_skin = "<?php echo esc_html(isset($settings['overlay_skin']) ? $settings['overlay_skin'] : 'default') ?>";





</script>
<?php
