<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
//options a widget has not saved yet (a new widget in the block widgets editor) are empty, as reading the missing keys gave
foreach (array('hide_meta_filter_values', 'hide_tax_filter_values', 'meta_data_filter_cat', 'meta_data_filter_slug', 'show_found_totally', 'taxonomies_options_post_recount_dyn', 'taxonomies_options_hide_terms_0', 'act_without_button', 'title_for_filter_button', 'show_reset_button', 'reset_link', 'ajax_items_recount') as $mdf_instance_key)
{
    if (!isset($instance[$mdf_instance_key]))
    {
        $instance[$mdf_instance_key] = null;
    }
}
//do not show form if such params
if ($instance['hide_meta_filter_values'] == 'true' AND $instance['hide_tax_filter_values'] == 'true')
{
    return false;
}

//the search on the page belongs to another widget or form (other post type or filter category, not offered by this widget):
//this widget is drawn as if there were no search
$mdf_foreign_search = false;
if (self::is_page_mdf_data())
{
    $mdf_search_data = self::get_page_mdf_data();
    $mdf_search_options = (isset($mdf_search_data['mdf_widget_options']) AND is_array($mdf_search_data['mdf_widget_options'])) ? $mdf_search_data['mdf_widget_options'] : array();
    $mdf_search_slug = isset($mdf_search_options['slug']) ? (string) $mdf_search_options['slug'] : '';
    $mdf_search_cat = !empty($mdf_search_options['meta_data_filter_cat']) ? (int) $mdf_search_options['meta_data_filter_cat'] : (isset($_REQUEST['mdf_cat']) ? (int) $_REQUEST['mdf_cat'] : 0);
    $mdf_own_cats = array((int) $instance['meta_data_filter_cat']);
    if (isset($instance['filter_categories_addit']) AND is_array($instance['filter_categories_addit']))
    {
        $mdf_own_cats = array_merge($mdf_own_cats, array_map('intval', array_keys($instance['filter_categories_addit'])));
    }
    //a search without the post type or the category (older links) stays shown, as before
    if (($mdf_search_slug !== '' AND $mdf_search_slug != (string) $instance['meta_data_filter_slug']) OR ($mdf_search_cat != 0 AND !in_array($mdf_search_cat, $mdf_own_cats, true)))
    {
        $mdf_foreign_search = true;
    }
}
MetaDataFilterCore::$ignore_page_mdf_data = $mdf_foreign_search;

//shows widget only with $_REQUEST['mdf_cat'] ID
$show_widget = true;
//+++
if ($show_widget):
    $uniqid = uniqid();


//+++
    if (isset($instance['search_result_page']) AND ! empty($instance['search_result_page']))
    {
        $search_url = $instance['search_result_page'];
    } else
    {
        $search_url = MetaDataFilterCore::get_search_url_page();
    }

    //output content by ajax
    $ajax_results = (isset($instance['ajax_results']) AND $instance['ajax_results'] == 'true') ? 1 : 0;

    //WPML compatibility
    if (class_exists('SitePress'))
    {
        $search_url = str_replace(site_url(), site_url() . '/' . ICL_LANGUAGE_CODE, $search_url);
    }
//+++
    if (substr_count($search_url, '?'))
    {
        $search_url = $search_url . '&';
    } else
    {
        $search_url = $search_url . '?';
    }
    ?>


    <?php
    if (isset($args['before_widget']))
    {
        echo wp_kses_post(wp_unslash($args['before_widget']));
    }
    ?>

    <div class="widget widget-meta-data-filter">


        <?php if (!empty($instance['title'])): ?>

            <?php
            if (isset($instance['after_title']))
            {
                echo wp_kses_post(wp_unslash($args['before_title']));
            }
            ?>

            <h3><?php esc_html_e($instance['title']) ?></h3>


            <?php
            if (isset($instance['after_title']))
            {
                echo wp_kses_post(wp_unslash($args['after_title']));
            }
            ?>

        <?php endif; ?>


        <?php
        //+++ using filter categories selector
        $meta_data_filter_cat = $instance['meta_data_filter_cat'];
        $_REQUEST['meta_data_filter_cat_form'] = $meta_data_filter_cat;
        if (isset($_REQUEST['mdf_cat']) AND !$mdf_foreign_search)
        {
            $instance['meta_data_filter_cat'] = $meta_data_filter_cat = (int)$_REQUEST['mdf_cat'];
        }
        $filter_categories = get_terms(MetaDataFilterCore::$slug_cat);
        $filter_categories_saved = (isset($instance['filter_categories_addit']) ? (array) $instance['filter_categories_addit'] : array());
        $current_fc_data = null;
        if (!empty($filter_categories))
        {
            //unset selected filter category
            foreach ($filter_categories as $k => $fc)
            {
                if ($fc->term_id == $meta_data_filter_cat)
                {
                    $current_fc_data = $filter_categories[$k];
                }

                if (!in_array($fc->term_id, array_keys($filter_categories_saved)) OR $fc->term_id == $meta_data_filter_cat)
                {
                    unset($filter_categories[$k]);
                }
            }
        }

        if (!empty($filter_categories) AND is_object($current_fc_data))
        {
            ?>
            <select class="mdf_filter_categories" data-slug="<?php echo esc_attr($instance['meta_data_filter_slug']) ?>">
                <option value="<?php echo esc_attr($current_fc_data->term_id) ?>"><?php echo esc_html($current_fc_data->name) ?></option>
                <?php foreach ($filter_categories as $k => $fc) : ?>
                    <option value="<?php echo esc_attr($fc->term_id) ?>"><?php echo esc_html($fc->name) ?></option>
                <?php endforeach; ?>
            </select>
            <?php
        }
        ?>

        <?php if (isset($_REQUEST['meta_data_filter_count']) AND $instance['show_found_totally'] == 'true' AND !$mdf_foreign_search): ?>
            <i class="mdf_widget_found_count">
                <?php
                $show_items_count_text = sprintf(__('Found <span>%s</span> items', 'meta-data-filter'), (int)$_REQUEST['meta_data_filter_count']);
                if (isset($instance['show_items_count_text']) AND ! empty($instance['show_items_count_text']))
                {
                    $show_items_count_text = sprintf($instance['show_items_count_text'], (int)$_REQUEST['meta_data_filter_count']);
                }

                echo wp_kses_post(wp_unslash($show_items_count_text));
                ?>
            </i><br />
        <?php endif; ?>

		<?php
		if (!isset($widget_id)) {
			$widget_id = '';
		}
		?>
        <form method="get" action="" id="meta_data_filter_<?php echo esc_attr($uniqid) ?>" data-search-url="<?php echo esc_attr($search_url) ?>" data-unique-id="<?php echo esc_attr($uniqid) ?>" data-slug="<?php echo esc_attr($instance['meta_data_filter_slug']) ?>" data-sidebar-name="<?php echo esc_attr($sidebar_name) ?>" data-sidebar-id="<?php echo esc_attr($sidebar_id) ?>" data-widget-id="<?php echo esc_attr($widget_id) ?>"<?php if (!empty($widget_redraw_data)): ?> data-widget-redraw="<?php echo esc_attr($widget_redraw_data) ?>" data-widget-redraw-hash="<?php echo esc_attr($widget_redraw_hash) ?>"<?php endif; ?> class="mdf_search_form mdf_widget_form <?php if ($instance['ajax_items_recount'] AND $instance['act_without_button']!='true'): ?>mdf_ajax_auto_recount<?php endif; ?> <?php if ($instance['ajax_items_recount'] AND $instance['act_without_button'] !='true' AND $ajax_results): ?>mdf_ajax_content_redraw<?php endif; ?>">
            <div class="mdf_one_moment_txt">
                <span><?php MetaDataFilterHtml::draw_tax_loader(); ?></span>
            </div>
            <h6>Meta Data and Taxonomies Filter</h6>
            <?php
            //+++
            echo MetaDataFilterPage::draw_front_page_items($instance, $uniqid);
			if(!isset($instance['and_or'])){
				$instance['and_or'] = 'and';
			}
			if(!isset($instance['show_filter_button_after_each_block'])){
				$instance['show_filter_button_after_each_block'] = false;
			}
			//AND/OR for meta fields: widget option, can be changed by the hook (shortcode ID is 0 for widgets)
			$widget_and_or = strtoupper(trim((string) apply_filters('mdf_meta_data_filter_bool', strtoupper((string) $instance['and_or']), 0, $instance)));
            ?>

            <?php if ($widget_and_or == 'BOTH'): ?>
                <?php
                //the visitor selects the mode, keep the selection of the current search
                $meta_data_filter_bool = 'AND';
                if (self::is_page_mdf_data())
                {
                    $mdf_current_data = self::get_page_mdf_data();
                    $meta_data_filter_bool = self::get_meta_data_filter_bool(isset($mdf_current_data['mdf_widget_options']) ? $mdf_current_data['mdf_widget_options'] : array());
                } elseif (isset($_GET['meta_data_filter_bool']))
                {
                    $meta_data_filter_bool = self::normalize_meta_data_filter_bool($_GET['meta_data_filter_bool']);
                }
                ?>
                <input type="radio" name="meta_data_filter_bool" value="OR" <?php if ($meta_data_filter_bool == 'OR'): ?>checked<?php endif; ?> />&nbsp;<?php esc_html_e("OR", 'meta-data-filter') ?>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="meta_data_filter_bool" value="AND" <?php if ($meta_data_filter_bool == 'AND'): ?>checked<?php endif; ?> />&nbsp;<?php esc_html_e("AND", 'meta-data-filter') ?>
                <br /><br />
                <input type="hidden" name="mdf[mdf_widget_options][meta_data_filter_bool]" value="" />
            <?php else: ?>
                <?php $meta_data_filter_bool = self::normalize_meta_data_filter_bool($widget_and_or); ?>
                <input type="hidden" name="meta_data_filter_bool" value="<?php echo esc_attr($meta_data_filter_bool) ?>" />
                <input type="hidden" name="mdf[mdf_widget_options][meta_data_filter_bool]" value="<?php echo esc_attr($meta_data_filter_bool) ?>" />
            <?php endif; ?>


            <?php
            //AND/OR between taxonomies: widget option, can be changed by the hook (shortcode ID is 0 for widgets)
            $mdf_tax_bool = self::normalize_meta_data_filter_bool(apply_filters('mdf_tax_bool', self::get_widget_tax_bool($instance), 0, $instance));
            ?>


            <?php
            $reset_link = self::get_setting('reset_link');

            if (!empty($instance['reset_link']))
            {
                $reset_link = $instance['reset_link'];
            }
            ?>

            <input type="hidden" class="hidden_page_mdf_for_ajax" value="<?php echo(self::is_page_mdf_data() ? self::get_page_mdf_string() : '') ?>" />
            <input type="hidden" name="mdf_tax_bool" value="<?php echo esc_attr($mdf_tax_bool) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][mdf_tax_bool]" value="<?php echo esc_attr($mdf_tax_bool) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][slug]" value="<?php echo esc_html($instance['meta_data_filter_slug']) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][meta_data_filter_cat]" value="<?php echo esc_html($meta_data_filter_cat) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][show_items_count_dynam]" value="<?php echo(isset($instance['show_items_count_dynam']) AND $instance['show_items_count_dynam'] == 'true' ? 1 : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][taxonomies_options_post_recount_dyn]" value="<?php echo($instance['taxonomies_options_post_recount_dyn'] == 'true' ? 1 : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][taxonomies_options_hide_terms_0]" value="<?php echo($instance['taxonomies_options_hide_terms_0'] == 'true' ? 1 : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][hide_meta_filter_values]" value="<?php echo($instance['hide_meta_filter_values'] == 'true' ? 1 : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][hide_tax_filter_values]" value="<?php echo($instance['hide_tax_filter_values'] == 'true' ? 1 : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][search_result_page]" value="<?php echo esc_html((isset($instance['search_result_page']) AND ! empty($instance['search_result_page'])) ? $instance['search_result_page'] : '') ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][search_result_tpl]" value="<?php echo esc_html((isset($instance['search_result_tpl']) AND ! empty($instance['search_result_tpl'])) ? $instance['search_result_tpl'] : '') ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][woo_search_panel_id]" value="<?php echo esc_html((isset($instance['woo_search_panel_id']) AND ! empty($instance['woo_search_panel_id'])) ? $instance['woo_search_panel_id'] : 0) ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][additional_taxonomies]" value="<?php echo esc_html((isset($instance['additional_taxonomies']) AND ! empty($instance['additional_taxonomies'])) ? MetaDataFilter::service_additional_tax_query_string($instance['additional_taxonomies']) : '') ?>" />
            <input type="hidden" name="mdf[mdf_widget_options][reset_link]" value="<?php echo esc_html($reset_link) ?>" />


            <input type="hidden" value="<?php echo esc_html($meta_data_filter_cat) ?>" name="<?php echo esc_html(MetaDataFilterCore::$slug_cat) ?>" />



            <div class="mdtf-clear"></div>

            <?php if (!$instance['show_filter_button_after_each_block'] AND ! $instance['act_without_button'] AND ! $ajax_results): ?>
                <div class="mdf_submit_button_container">
                    <input type="submit" class="button mdf_button" name="" value="<?php echo esc_html($instance['title_for_filter_button']); ?>" />
                </div>
            <?php endif; ?>

            <?php if ($instance['show_reset_button'] AND self::is_page_mdf_data()): ?>                
                <div class="mdf_reset_button_container">
                    <?php
                    if ($reset_link == 'self')
                    {
                        if (is_page() OR is_single())
                        {
                            global $wp_query;
                            $reset_link = esc_url(apply_filters('the_permalink', get_permalink($wp_query->queried_object->ID)));
                        } else
                        {
                            if (isset($_SERVER['SCRIPT_URI']))
                            {
                                $reset_link = sanitize_text_field($_SERVER['SCRIPT_URI']);
                            } else
                            {
                                //SCRIPT_URI is not set on every server: current URL without the search parameters
                                $reset_link = remove_query_arg(array('slg', 'mdf_cat', 'page_mdf', 'mdf_page_num'));
                            }
                        }
                    }

                    $reset_class="";
                    if (defined('DOING_AJAX') AND DOING_AJAX AND $instance['reset_link']=='self' AND $instance['ajax_items_recount'] AND $instance['act_without_button'] !='true' ) { 
                        $reset_class="mdf_reset_ajax_bone";                        
                    }

                    $reset_link=  trim($reset_link,'"\'');
                    ?>
                    <input type="button" href="<?php echo esc_url_raw(empty($reset_link) ? '#' : $reset_link) ?>" class="button mdf_button mdf_reset_button <?php echo esc_attr($reset_class) ?> " value="<?php esc_html_e('Reset', 'meta-data-filter') ?>" /><br />
                </div>
            <?php endif; ?>

            <div class="mdtf-clear"></div>

        </form>
        <br />



        <script>
            jQuery(function () {
                //DYNAMIC SCRIPT DEPENDING OF WIDGET SETTINGS
				if (typeof mdf_init_search_form !== 'undefined'){
					mdf_init_search_form("<?php echo esc_attr($uniqid) ?>", "<?php echo esc_html($instance['meta_data_filter_slug']) ?>", "<?php echo esc_html($search_url) ?>", <?php echo($instance['act_without_button'] === 'true' ? 1 : 0) ?>, <?php echo($instance['ajax_items_recount'] === 'true' ? 1 : 0) ?>, 0);
				}
			});
        </script>

    </div>

    <?php
    if (isset($args['after_widget']))
    {
        echo wp_kses_post(wp_unslash($args['after_widget']));
    }
    ?>
<?php endif; ?>
<?php
//the next widget or form sees the search of the page again
MetaDataFilterCore::$ignore_page_mdf_data = false;
?>
