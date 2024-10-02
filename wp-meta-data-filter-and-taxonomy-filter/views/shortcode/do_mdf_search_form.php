<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
$uniqid = uniqid();

if (!isset($shortcode_options['options'])) {
    $shortcode_options['options'] = array();
}
if (!isset($shortcode_options['options']['hide_items_where_count_0'])) {
    $shortcode_options['options']['hide_items_where_count_0'] = 0;
}
if (!isset($shortcode_options['options']['hide_items_where_count_0'])) {
    $shortcode_options['options']['hide_items_where_count_0'] = 0;
}
if (!isset($shortcode_options['options']['show_count'])) {
    $shortcode_options['options']['show_count'] = 0;
}
if (!isset($shortcode_options['options']['auto_submit'])) {
    $shortcode_options['options']['auto_submit'] = 0;
}
if (!isset($shortcode_options['options']['taxonomies_title'])) {
    $shortcode_options['options']['taxonomies_title'] = 0;
}
if (!isset($shortcode_options['options']['taxonomies_title'])) {
    $shortcode_options['options']['taxonomies_title'] = 0;
}
if (!isset($shortcode_options['options']['taxonomies_show_child_terms'])) {
    $shortcode_options['options']['taxonomies_show_child_terms'] = 0;
}

$slug = $shortcode_options['options']['post_type'];
$ajax_results = (isset($shortcode_options['options']['ajax_results'])) ? $shortcode_options['options']['ajax_results'] : 0;
//*** additional taxonomies for Pre-sale question:
//I have woocommerce with brands plugin installed, each brand page shows the products of this brand,
//can this plugin filter those products based on category?
if (isset($shortcode_options['options']['additional_taxonomies']) AND!empty($shortcode_options['options']['additional_taxonomies'])) {
    MetaDataFilter::add_additional_taxonomies($shortcode_options['options']['additional_taxonomies']);
}
?>
<div class="mdf_shortcode_container <?php if ($shortcode_options['options']['auto_submit']): ?>mdf_shortcode_auto_submit<?php endif; ?> mdf_shortcode_skin_<?php echo esc_attr($shortcode_options['options']['skin']) ?>">
    <form method="get" action="" data-unique-id="<?php echo esc_attr($uniqid); ?>" data-slug="<?php echo esc_attr($slug) ?>" data-shortcode-id="<?php echo esc_attr($shortcode_id) ?>" id="meta_data_filter_<?php echo esc_attr($uniqid) ?>" class="mdf_search_form <?php if ($shortcode_options['options']['ajax_auto_submit'] == 1 AND $ajax_results): ?>mdf_ajax_content_redraw<?php endif; ?> <?php if ($shortcode_options['options']['ajax_auto_submit'] == 1 AND!$shortcode_options['options']['auto_submit']): ?>mdf_ajax_auto_recount<?php endif; ?> mdf_shortcode_form mdf_shortcode_form_<?php echo esc_attr($shortcode_id) ?>">
        <?php if (self::is_page_mdf_data() AND $shortcode_options['options']['show_found_totally']): ?>
            <div class="mdf_widget_found_count"><?php
                $show_items_count_text = __('Found <span>%s</span> items', 'meta-data-filter');
                if (isset($shortcode_options['options']['show_items_count_text']) AND!empty($shortcode_options['options']['show_items_count_text'])) {
                    $show_items_count_text = str_replace('%s', '<span>%s</span>', $shortcode_options['options']['show_items_count_text']);
                }
                //***
                echo wp_kses_post(wp_unslash(sprintf($show_items_count_text, (isset($_REQUEST['meta_data_filter_count']) ? (int)$_REQUEST['meta_data_filter_count'] : 0))));
                ?></div>
        <?php endif; ?>

        <?php
        $meta_data_filter_bool = 'AND';
        $mdf_tax_bool = 'AND';

        $widget_options = array(
            'meta_data_filter_cat' => $shortcode_options['shortcode_cat'],
            'meta_data_filter_slug' => $slug,
            'hide_items_where_count_0' => $shortcode_options['options']['hide_items_where_count_0'],
            'taxonomies_options_hide_terms_0' => $shortcode_options['options']['hide_items_where_count_0'],
            'taxonomies_options_show_count' => $shortcode_options['options']['show_count'],
            'taxonomies_options_post_recount_dyn' => 1,
            'show_slider_items_count' => $shortcode_options['options']['show_count'],
            'show_select_items_count' => $shortcode_options['options']['show_count'],
            'show_checkbox_items_count' => $shortcode_options['options']['show_count'],
            'act_without_button' => $shortcode_options['options']['auto_submit'],
            'shortcode_taxonomies_title' => $shortcode_options['options']['taxonomies_title'],
            'taxonomies_show_child_terms' => $shortcode_options['options']['taxonomies_show_child_terms'],
            'shortcode_options' => $shortcode_options['options'],
        );

        $_REQUEST['meta_data_filter_cat_form'] = $shortcode_options['shortcode_cat'];

        $shortcode_has_taxonomies = 0;
        $shortcode_has_meta = 0;

        $icon = MetaDataFilter::get_application_uri() . 'images/tooltip-info.png';
        $settings = MetaDataFilter::get_settings();
        if (!empty($settings['tooltip_icon'])) {
            $icon = $settings['tooltip_icon'];
        }
        //+++
        if (empty($html_items) AND $shortcode_options['options']['tax_only_in_filter']) {
            $html_items = array(
                array(
                    'post_title' => '~Taxonomies',
                    'html_items' => array(
                        'medafi_shortcode_tax_only' => array(
                            'name' => '~Taxonomies',
                            'type' => 'taxonomy',
                            'meta_key' => 'medafi_shortcode_tax_only',
                        )
                    )
                )
            );
        }
        //+++
        if (!empty($html_items) AND is_array($html_items)) {
            $css_block_counter = 0;
            foreach ($html_items as $filter_id => $h_items) {
                //check it for hiding
                $this_block_is_hidden = false;
                if (isset($shortcode_options['filters'][$filter_id])) {
                    if ($shortcode_options['filters'][$filter_id]['is_hidden'] == 1) {
                        $this_block_is_hidden = true;
                    }
                }
                ?>
                <div class="mdf_input_container_block mdf_input_container_block_<?php echo esc_attr($css_block_counter++) ?>" <?php if ($this_block_is_hidden): ?>style="display: none;"<?php endif; ?>>
                    <?php
                    if (isset($h_items['post_title']) AND!empty($h_items['post_title']) AND substr($h_items['post_title'], 0, 1) !== '~'):
                        $mdtf_title_sections = "h4";
                        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                        ?>
                        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php esc_html_e($h_items['post_title']) ?></<?php echo esc_attr($mdtf_title_sections) ?>>
                    <?php endif; ?>
                    <?php
                    //+++
                    $section_sel_emulator = (int) get_post_meta($filter_id, 'widget_section_sel_emulator', true);
                    if (!$section_sel_emulator) {
                        if (!empty($h_items['html_items'])) {
                            foreach ($h_items['html_items'] as $meta_key => $item) {
                                //check is this html option is hidden
                                $this_item_is_hidden = false;
                                if (isset($shortcode_options['filters'][$filter_id])) {
                                    if ($shortcode_options['filters'][$filter_id][$meta_key] == 1) {
                                        $this_item_is_hidden = true;
                                    }
                                }
                                if ($this_block_is_hidden) {
                                    $this_item_is_hidden = true;
                                }
                                //+++
                                $search_key = $meta_key;
                                if (isset($item['is_reflected']) AND $item['is_reflected'] == 1) {
                                    $search_key = $item['reflected_key'];
                                }
                                $uid = uniqid();
                                //+++

                                switch ($item['type']) {
                                    case 'slider':
                                        $shortcode_has_meta++;
                                        $min = $max = 0;


                                        if (isset($page_meta_data_filter[$meta_key])) {
                                            list($min, $max) = explode('^', $page_meta_data_filter[$meta_key]);
                                        }

                                        if (!empty($item['slider'])) {
                                            list($preset_min, $preset_max) = explode('^', $item['slider']);
                                        } else {
                                            $preset_min = 0;
                                            $preset_max = 100;
                                        }

                                        $slider_step = isset($item['slider_step']) ? $item['slider_step'] : 0;
                                        $slider_prefix = isset($item['slider_prefix']) ? $item['slider_prefix'] : '';
                                        $slider_postfix = isset($item['slider_postfix']) ? $item['slider_postfix'] : '';
                                        $slider_prettify = isset($item['slider_prettify']) ? $item['slider_prettify'] : 1;

                                        if ($min == $max AND $max == 0) {
                                            $min = $preset_min;
                                            $max = $preset_max;
                                        }

                                        if (empty($page_meta_data_filter[$meta_key])) {
                                            $page_meta_data_filter[$meta_key] = (int) $min . '^' . $max;
                                        }

                                        //+++
                                        $items_count = 'xxx';
                                        if ($widget_options['show_slider_items_count'] AND!$this_item_is_hidden) {
                                            $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, array($min, $max), $slug, 'slider');
                                        }
                                        //++++++++++++++++++++++++++++++++++++
                                        if ($search_key === '_price' AND ( isset($item['woo_price_auto']) AND $item['woo_price_auto'] == 1)) {
                                            $mm = MDTF_HELPER::get_woo_min_max_price();
                                            if (!empty($mm)) {
                                                $preset_max = $mm['max'];
                                                $preset_min = $mm['min'];
                                                if (!self::is_page_mdf_data()) {
                                                    $max = $mm['max'];
                                                    $min = $mm['min'];
                                                }
                                            }
                                        }
                                        $mdtf_title_sections = "h5";
                                        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                        ?>
                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                            <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:&nbsp;<span class="mdf_range"><input type="text" value="<?php echo esc_attr($min) ?>" class="mdf_range_min" data-slider-id="ui_slider_item_<?php echo esc_attr($uid) ?>" />

                                                <?php
                                                $range_slider_type = 'double';
                                                if (!isset($item['slider_range_value'])) {
                                                    $item['slider_range_value'] = 0;
                                                }
                                                if ($item['slider_range_value'] == 1) {
                                                    $range_slider_type = 'single';
                                                }
                                                ?>

                                                <?php if ($range_slider_type == 'double'): ?>
                                                    &nbsp;-&nbsp;<input type="text" value="<?php echo esc_attr($max) ?>" class="mdf_range_max" data-slider-id="ui_slider_item_<?php echo esc_attr($uid) ?>" />
                                                <?php endif; ?>

                                            </span> &nbsp;
											<?php if ($widget_options['show_slider_items_count'] AND !$this_item_is_hidden){
												?> <span class="<?php echo esc_attr($meta_key);?> medafi_dyn_number">(<?php echo esc_attr($items_count);?>)</span>
												<?php 
											} ?>&nbsp;
											<?php if (!empty($item['description'])): ?>
                                                <span class="mdf_tooltip" title="<?php echo wp_kses_post(wp_unslash(str_replace('"', "'", esc_html__($item['description'])))); ?>">
                                                    <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                </span>
                                            <?php endif; ?>
                                            </<?php echo esc_attr($mdtf_title_sections) ?>>
                                            <div class="ui_slider_item ui_slider_item_<?php echo esc_attr($uniqid) ?>" id="ui_slider_item_<?php echo esc_attr($uid) ?>"></div>



                                            <input type="hidden" name="mdf[<?php echo esc_attr($meta_key) ?>]" id="<?php echo esc_attr($uid) ?>" data-type="<?php echo esc_attr($range_slider_type) ?>" data-min="<?php echo esc_attr($preset_min) ?>" data-max="<?php echo esc_attr($preset_max) ?>" data-min-now="<?php echo esc_attr($min) ?>" data-max-now="<?php echo esc_attr($max) ?>" data-step="<?php echo esc_attr($slider_step) ?>" data-slider-prefix="<?php echo esc_html($slider_prefix) ?>" data-slider-postfix="<?php echo esc_html($slider_postfix) ?>" data-slider-prettify="<?php echo esc_attr($slider_prettify) ?>" value="<?php echo esc_html($page_meta_data_filter[$meta_key]) ?>" />
                                        </div>
                                        <?php
                                        break;
                                    case 'range_select':
                                        //start range  select
                                        $shortcode_has_meta++;
                                        $min = $max = 0;
                                        if (isset($page_meta_data_filter[$meta_key])) {
                                            $min = empty($page_meta_data_filter[$meta_key]['from']) ? 0 : $page_meta_data_filter[$meta_key]['from'];
                                            $max = empty($page_meta_data_filter[$meta_key]['to']) ? 100 : $page_meta_data_filter[$meta_key]['to'];
                                        }

                                        if (isset($item['range_select']) AND!empty($item['range_select'])) {
                                            list($preset_min, $preset_max) = explode('^', $item['range_select']);
                                        } else {
                                            $preset_min = $preset_max = 0;
                                        }
                                        $range_select_step = isset($item['range_select_step']) ? $item['range_select_step'] : 0;
                                        $range_select_prefix = isset($item['range_select_prefix']) ? $item['range_select_prefix'] : '';
                                        $range_select_postfix = isset($item['range_select_postfix']) ? $item['range_select_postfix'] : '';

                                        if ($min == $max AND $max == 0) {
                                            $min = $preset_min;
                                            $max = $preset_max;
                                        }

                                        if (empty($page_meta_data_filter[$meta_key])) {
                                            $page_meta_data_filter[$meta_key] = (int) $min . '^' . $max;
                                        }

                                        //+++
                                        $items_count = 'xxx';
                                        if ($widget_options['show_slider_items_count'] AND!$this_item_is_hidden) {
                                            $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, array($min, $max), $slug, 'slider');
                                        }
                                        //++++++++++++++++++++++++++++++++++++
                                        if ($search_key === '_price' AND ( isset($item['woo_price_auto_range_select']) AND $item['woo_price_auto_range_select'] == 1)) {
                                            $mm = MDTF_HELPER::get_woo_min_max_price();

                                            if (!empty($mm)) {
                                                $preset_max = $mm['max'];
                                                $preset_min = $mm['min'];
                                                if (!self::is_page_mdf_data()) {
                                                    $max = $mm['max'];
                                                    $min = $mm['min'];
                                                }
                                            }
                                        }
                                        $mdtf_title_sections = "h5";
                                        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                        if ($preset_max == $preset_min) {
                                            $this_item_is_hidden = true;
                                        }
                                        ?>
                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                            <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:&nbsp;<span class="mdf_range">
                                            </span> &nbsp;
												<?php if ($widget_options['show_slider_items_count'] AND!$this_item_is_hidden){ 
													?> 
											      <span class="<?php echo esc_attr($meta_key);?> medafi_dyn_number">(<?php echo esc_attr($items_count);?>)</span>;
													<?php } ?>&nbsp;
												<?php if (!empty($item['description'])): ?>
                                                <span class="mdf_tooltip" title="<?php echo wp_kses_post(wp_unslash(str_replace('"', "'", esc_html__($item['description'])))); ?>">
                                                    <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                </span>
                                            <?php endif; ?>
                                            </<?php echo esc_attr($mdtf_title_sections) ?>>
                                            <?php
                                            echo do_shortcode("[mdf_range_select meta_key='" . $meta_key . "'  min=" . $preset_min . "  max=" . $preset_max . " step=" . $range_select_step . " cur_max=" . $max . "  cur_min=" . $min . "  prefix='" . $range_select_prefix . "' postfix='" . $range_select_postfix . "'  ]");
                                            ?></div><?php
                                        //end range  select
                                        break;
                                    case 'by_author' :
                                        //start by_author
                                        $mdtf_title_sections = "h5";
                                        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                        $selected_option = isset($page_meta_data_filter[$meta_key]) ? $page_meta_data_filter[$meta_key] : NULL;
                                        //var_dump($page_meta_data_filter);
                                        ?>
                                        <div class="mdf_input_container mdf_by_author_container_<?php echo esc_attr($meta_key) ?> " >
                                            <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>
                                            </<?php echo esc_attr($mdtf_title_sections) ?>>
                                            <?php
                                            $users = get_users(array('count_total' => false, 'fields' => array('ID', 'display_name', 'user_nicename'),));
                                            ?>
                                            <select name="mdf[<?php echo esc_attr($meta_key) ?>]" id="<?php echo esc_attr($meta_key) ?>" class="mdf_filter_select" >
                                                <option value="-1"> <?php esc_html_e("Select author", 'meta-data-filter') ?></option>
                                                <?php
                                                foreach ($users as $user) {
                                                    ?>
                                                    <option value="<?php echo esc_attr($user->ID) ?>" <?php echo($user->ID == $selected_option ? 'selected' : '') ?> > <?php echo esc_html($user->user_nicename) ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>

                                        </div><div class="mdf_clear"></div>
                                        <?php
                                        //end by_author

                                        break;
                                    case 'select':
                                        $shortcode_has_meta++;
                                        if (!empty($item['select'])) {
                                            $selected = isset($page_meta_data_filter[$meta_key]) ? $page_meta_data_filter[$meta_key] : NULL;
                                            $select_option_title = (isset($item['select_option_title']) AND!empty($item['select_option_title'])) ? $item['select_option_title'] : esc_html__('Any', 'meta-data-filter');
                                            ?>
                                            <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>

                                                <?php
                                                if (substr($item['name'], 0, 1) !== '~'):
                                                    $mdtf_title_sections = "h5";
                                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                    ?>
                                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:
                                                    &nbsp;<?php if (!empty($item['description'])): ?>
                                                        <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                            <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                        </span>
                                                    <?php endif; ?>
                                                    </<?php echo esc_attr($mdtf_title_sections) ?>>
                                                <?php endif; ?>

                                                <select name="mdf[<?php echo esc_attr($meta_key) ?>]" id="<?php echo esc_attr($meta_key) ?>" class="mdf_filter_select">
                                                    <option value="~"><?php echo esc_html($select_option_title); ?></option>

                                                    <?php
                                                    //if enabled Sort options by alphabetical order
                                                    if (isset($item['select_sort_value_by_alphabetical']) AND $item['select_sort_value_by_alphabetical'] == 1) {
                                                        asort($item['select']);
                                                    }
                                                    ?>


                                                    <?php foreach ($item['select'] as $kk => $value) : ?>
                                                        <?php
                                                        $items_count = -1;

                                                        $select_option_key = $item['select_key'][$kk];
                                                        //***
                                                        $is_range = false;
                                                        if (isset($item['select_range_value']) && $item['select_range_value'] == 1) {
                                                            $tmp = explode('-', $select_option_key);

                                                            $is_range = MDTF_HELPER::is_slider_range_value($tmp); //range drop down for price
                                                        }
                                                        //***
                                                        if ($widget_options['show_select_items_count'] AND!$this_item_is_hidden) {
                                                            $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, $select_option_key, $slug, 'select');
                                                            if (!$items_count) {
                                                                if ($widget_options['hide_items_where_count_0'] AND!$is_range) {
                                                                    continue; //IF NO ONE ITEM WHY NOT TO HIDE THIS?!
                                                                }
                                                            }
                                                        }
                                                        ?>

                                                        <?php if ($is_range): ?>
                                                            <option value="<?php echo esc_html($select_option_key) ?>" <?php echo($selected == $select_option_key ? 'selected' : '') ?>><?php esc_html_e($value) ?></option>
                                                        <?php else: ?>
                                                            <option <?php if ($items_count == 0 AND $widget_options['show_select_items_count']): ?>disabled<?php endif; ?> value="<?php echo esc_html($select_option_key) ?>" <?php echo($selected == $select_option_key ? 'selected' : '') ?>><?php esc_html_e($value) ?> <?php if ($widget_options['show_select_items_count']) echo esc_html('(' . $items_count . ')'); ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php
                                        }
                                        break;

                                    case 'checkbox':
                                        $shortcode_has_meta++;
                                        $items_count = -1;
                                        if ($widget_options['show_checkbox_items_count'] AND!$this_item_is_hidden) {
                                            $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, 1, $slug, 'checkbox');
                                            
                                        }
                                        //+++
                                        $is_checked = isset($page_meta_data_filter[$meta_key]) ? (int) $page_meta_data_filter[$meta_key] : '~';
                                        ?>

                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                            <input type="hidden" name="mdf[<?php echo esc_attr($meta_key) ?>]" value="<?php echo esc_html($is_checked) ?>">
                                            <input <?php if ($items_count == 0 AND $widget_options['show_checkbox_items_count']): ?>disabled<?php endif; ?> type="checkbox" class="mdf_option_checkbox" id="<?php echo esc_attr($meta_key) ?>_<?php echo esc_attr($uid) ?>" <?php if ($is_checked AND $is_checked != '~'): ?>checked<?php endif; ?> />
                                            <label for="<?php echo esc_attr($meta_key) ?>_<?php echo esc_attr($uid) ?>">&nbsp;<?php esc_html_e($item['name']) ?> 
												<?php if ($widget_options['show_checkbox_items_count']){ ?>
												
												<span class="<?php echo esc_attr($meta_key); ?> medafi_dyn_number">(<?php echo esc_html($items_count)?>)</span>
													
												<?php } ?>
                                                <?php if (!empty($item['description'])): ?>
                                                    <span class="mdf_tooltip" title="<?php echo wp_kses_post(wp_unslash(str_replace('"', "'", esc_html__($item['description'])))); ?>">
                                                        <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                    </span>
                                                <?php endif; ?>
                                            </label>
                                        </div>

                                        <?php
                                        break;
                                    case 'label':
                                        $shortcode_has_meta++;
                                        $items_count = -1;
                                        $count_string = "";
                                        if ($widget_options['show_checkbox_items_count'] AND!$this_item_is_hidden) {
                                            $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, 1, $slug, 'checkbox');
                                            if (!$items_count) {
                                                if ($widget_options['hide_items_where_count_0']) {
                                                    break;
                                                }
                                            }
                                            $count_string = '<span class="mdf_label_count">' . $items_count . '</span>';
                                        }
                                        //+++
                                        $is_checked = isset($page_meta_data_filter[$meta_key]) ? (int) $page_meta_data_filter[$meta_key] : '~';
                                        ?>

                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>



                                            <?php
                                            if ($widget_options['show_checkbox_items_count'] AND!($is_checked AND $is_checked != '~')) {
                                                echo wp_kses_post(wp_unslash($count_string));
                                            }
                                            ?>
                                            <span class=" mdf_label_item <?php if ($is_checked AND $is_checked != '~') echo 'checked'; ?>">

                                                <?php esc_html_e($item['name']); ?>
                                                <input class="mdf_label_hiden" type="hidden" name="mdf[<?php echo esc_attr($meta_key) ?>]" value="<?php echo esc_html($is_checked) ?>">
                                                <input style="display: none;"  class="mdf_label_term mdf_option_label"  <?php if ($items_count == 0 AND $widget_options['show_checkbox_items_count']): ?>disabled<?php endif; ?> type="checkbox"  id="<?php echo esc_attr($meta_key) ?>_<?php echo esc_attr($uid) ?>" <?php if ($is_checked AND $is_checked != '~'): ?>checked<?php endif; ?> />
                                            </span>

                                        </div>

                                        <?php
                                        break;

                                    case 'calendar':
                                        $shortcode_has_meta++;
                                        //wp_enqueue_style('jquery-ui-styles', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css');
										wp_enqueue_style('jquery-ui-styles', MetaDataFilterCore::get_application_uri() . '/css/jquery-ui-themes/themes/smoothness/jquery-ui.css');

                                        $calendar_date = isset($page_meta_data_filter[$meta_key]) ? $page_meta_data_filter[$meta_key] : '';
                                        $from = "";
                                        $to = "";
                                        if (!empty($calendar_date) AND is_array($calendar_date)) {
                                            $from = $calendar_date['from'];
                                            $to = $calendar_date['to'];
                                        }
                                        ?>

                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                            <?php
                                            if (empty($item['name']) OR $item['name'][0] !== '~'):
                                                $mdtf_title_sections = "h5";
                                                $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                ?>
                                                <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php esc_html_e($item['name']) ?>:
                                                <?php if (!empty($item['description'])): ?>
                                                    <label for="<?php echo esc_attr($meta_key) ?>_<?php echo esc_attr($uid) ?>">
                                                        <span class="mdf_tooltip" title="<?php echo wp_kses_post(wp_unslash(str_replace('"', "'", esc_html__($item['description'])))); ?>">
                                                            <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                        </span>
                                                    </label>
                                                <?php endif; ?>
                                                </<?php echo esc_attr($mdtf_title_sections) ?>>

                                            <?php endif; ?>
                                            <input type="hidden" name="mdf[<?php echo esc_attr($meta_key) ?>][from]" value="<?php echo esc_html($from) ?>" />
                                            <input type="text" readonly="readonly" class="mdf_calendar mdf_calendar_from" placeholder="<?php esc_html_e('from', 'meta-data-filter') ?>" />
                                            &nbsp;-&nbsp;
                                            <input type="hidden" name="mdf[<?php echo esc_attr($meta_key) ?>][to]" value="<?php echo esc_html($to) ?>" />
                                            <input type="text" readonly="readonly" class="mdf_calendar mdf_calendar_to" placeholder="<?php esc_html_e('to', 'meta-data-filter') ?>" />
                                        </div>

                                        <?php
                                        break;


                                    case 'textinput':
                                        $shortcode_has_meta++;
                                        $text = isset($page_meta_data_filter[$meta_key]) ? $page_meta_data_filter[$meta_key] : '';
                                        ?>

                                        <div class="mdf_input_container" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                            <?php
                                            if (substr($item['name'], 0, 1) !== '~') {
                                                $mdtf_title_sections = "h5";
                                                $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                ?>
                                                <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php if (!empty($item['name'])) esc_html_e($item['name'] . ':'); ?>
                                                <?php if (!empty($item['description'])): ?>
                                                    <label for="<?php echo esc_attr($meta_key) ?>_<?php echo esc_attr($uid) ?>">
                                                        <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                            <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                        </span>
                                                    </label>
                                                <?php endif; ?>
                                                </<?php echo esc_attr($mdtf_title_sections) ?>>
                                            <?php }; ?>
                                            <input type="text" class="mdf_textinput text" placeholder="<?php esc_html_e($item['textinput']) ?>" name="mdf[<?php echo esc_attr($meta_key) ?>]" value="<?php echo esc_html($text) ?>" />
                                        </div>

                                        <?php
                                        break;

                                    case 'taxonomy':
                                        $shortcode_has_taxonomies++;
                                        ?>


                                        <div class="mdf_input_container_taxonomies">
                                            <?php foreach ($shortcode_options['options']['taxonomies'] as $tax_name => $v) : ?>

                                                <?php
                                                if (!$v) {
                                                    continue;
                                                }
                                                ?>


                                                <div class="mdf_input_container mdf_taxonomy_<?php echo esc_attr($tax_name) ?> <?php if ($widget_options['act_without_button']): ?>mdf_tax_auto_submit<?php endif; ?> <?php if (isset($widget_options['ajax_items_recount']) AND $widget_options['ajax_items_recount']): ?>mdf_tax_ajax_autorecount<?php endif; ?>" <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                                    <?php $show_how = $shortcode_options['options']['taxonomies_type'][$tax_name]; ?>

                                                    <?php
                                                    if (isset($item['name']) AND substr($item['name'], 0, 1) !== '~' AND $show_how != 'checkbox'):
                                                        $mdtf_title_sections = "h5";
                                                        $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                        ?>
                                                        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title" style="display: none;"><?php esc_html_e($item['name']) ?>:</<?php echo esc_attr($mdtf_title_sections) ?>>
                                                    <?php endif; ?>


                                                    <input type="hidden" name="mdf[taxonomy][<?php echo esc_attr($show_how) ?>][<?php echo esc_attr($tax_name) ?>]" value="" />
                                                    <?php
                                                    $hide = '';

                                                    if (isset($shortcode_options['options']['excluded_terms'][$tax_name])) {
                                                        $hide = $shortcode_options['options']['excluded_terms'][$tax_name];
                                                    }

                                                    //***
                                                    if ($this_item_is_hidden) {
                                                        $widget_options['taxonomies_options_show_count'] = 0;
                                                    }
                                                    //+++
                                                    if (self::is_page_mdf_data()) {
                                                        $taxonomies = array();
                                                        if (isset($page_meta_data_filter['taxonomy'])) {
                                                            $taxonomies = $page_meta_data_filter['taxonomy'];
                                                        }
                                                        //+++
                                                        $terms_ids = array();
                                                        if (isset($taxonomies[$show_how])) {
                                                            if (array_key_exists($tax_name, $taxonomies[$show_how])) {
                                                                $terms_ids = $taxonomies[$show_how][$tax_name];
                                                            }
                                                        }
                                                        //+++
                                                        //check situation when data transfered by shortcode and is in another html item
                                                        if (empty($terms_ids)) {
                                                            $tmp = $taxonomies;
                                                            unset($tmp[$show_how]);
                                                            if (!empty($tmp) AND is_array($tmp)) {
                                                                foreach ($tmp as $s_h => $txs) {
                                                                    if (array_key_exists($tax_name, $txs)) {
                                                                        $terms_ids = $tmp[$s_h][$tax_name];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        //draw selects
                                                        if ($show_how == 'select') {
                                                            if (!empty($terms_ids) AND is_array($terms_ids)) {
                                                                foreach ($terms_ids as $k => $term_id) {
                                                                    if ($term_id == -1 AND $term_id == 0) {
                                                                        unset($terms_ids[$k]);
                                                                    }
                                                                }
                                                            }
                                                            //+++
                                                            $cc = 0;
                                                            if (!empty($terms_ids) AND is_array($terms_ids)) {
                                                                foreach ($terms_ids as $term_id) {
                                                                    $parent_id = 0;
                                                                    if ($cc > 0) {
                                                                        $parent_id = $terms_ids[$cc - 1];
                                                                    }

                                                                    MetaDataFilter::draw_term_childs('select', $parent_id, $term_id, $tax_name, false, $hide, $widget_options);
                                                                    echo '<div class="mdf_taxonomy_child_container" style="display:block;">';
                                                                    $cc++;
                                                                }
                                                            } else {
                                                                MetaDataFilter::draw_term_childs('select', 0, 0, $tax_name, false, $hide, $widget_options);
                                                                echo '<div class="mdf_taxonomy_child_container" style="display:block;"></div>';
                                                            }
                                                            //+++

                                                            for ($i = 0; $i < $cc; $i++) {
                                                                echo '</div>';
                                                            }
                                                        }
                                                        //+++
                                                        //*** draw checkboxes
                                                        if ($show_how == 'checkbox') {
                                                            $section_max_height = (isset($shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name]) AND!empty($shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name])) ? $shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name] : 0;
                                                            $mdtf_title_sections = "h5";
                                                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                            ?>
                                                            <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php
                                                            $title = "";
                                                            if (isset($shortcode_options['options']['taxonomies_title'][$tax_name])) {
                                                                $title = $shortcode_options['options']['taxonomies_title'][$tax_name];
                                                            }

                                                            if (empty($title)) {
                                                                $title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                            }
                                                            echo esc_html($title);
                                                            ?>:</<?php echo esc_attr($mdtf_title_sections) ?>>
                                                            <span style="display: inline-block; <?php if (!empty($section_max_height)): ?>max-height: <?php echo esc_attr($section_max_height) ?>px;<?php endif; ?>" class="mdf_tax_filter_section <?php if ($widget_options['act_without_button']): ?>mdf_tax_auto_submit<?php endif; ?>">
                                                                <?php
                                                                $parent_id = 0;
                                                                MetaDataFilter::draw_term_childs('checkbox', $parent_id, $terms_ids, $tax_name, false, $hide, $widget_options);
                                                                ?>
                                                            </span>
                                                            <?php
                                                        }
                                                    } else {
                                                        if ($show_how == 'checkbox') {
                                                            $section_max_height = (isset($shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name]) AND!empty($shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name])) ? $shortcode_options['options']['taxonomies_checkbox_maxheight'][$tax_name] : 0;
                                                            $mdtf_title_sections = "h5";
                                                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                            ?>
                                                            <<?php echo esc_attr($mdtf_title_sections); ?> class="data-filter-section-title"><?php
                                                            $title = "";
                                                            if (isset($shortcode_options['options']['taxonomies_title'][$tax_name])) {
                                                                $title = $shortcode_options['options']['taxonomies_title'][$tax_name];
                                                            }

                                                            if (empty($title)) {
                                                                $title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                            }
                                                            echo esc_html($title);
                                                            ?>:</<?php echo esc_attr($mdtf_title_sections) ?>>
                                                            <span style="display: inline-block; <?php if (!empty($section_max_height)): ?>max-height: <?php echo esc_attr($section_max_height) ?>px;<?php endif; ?>" class="mdf_tax_filter_section">
                                                                <?php
                                                            }
                                                            if ($show_how != 'multi_select') {
                                                                MetaDataFilter::draw_term_childs($show_how, 0, 0, $tax_name, false, $hide, $widget_options);
                                                            }
                                                            ?>
                                                            <div class="mdf_taxonomy_child_container"><?php MetaDataFilterHtml::draw_tax_loader(); ?></div>
                                                        </span>
                                                        <?php
                                                    }

                                                    //***

                                                    if ($show_how == 'multi_select') {
                                                        ?>

                                                        <!-- <h4 class="data-filter-section-title"><?php
                                                        $tax_title = "";
                                                        if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND!empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                                            $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                                        } else {
                                                            $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                        }
                                                        echo esc_html($tax_title);
                                                        ?></h4> -->
                                                        <div class="mdf_tax_filter_section_<?php echo esc_attr($tax_name) ?>">
                                                            <?php
                                                            $parent_id = 0;
                                                            $terms_ids = array();
                                                            //***
                                                            $taxonomies = array();
                                                            if (isset($page_meta_data_filter['taxonomy'])) {
                                                                $taxonomies = $page_meta_data_filter['taxonomy'];
                                                            }
                                                            if (isset($taxonomies[$show_how])) {
                                                                if (array_key_exists($tax_name, $taxonomies[$show_how])) {
                                                                    $terms_ids = $taxonomies[$show_how][$tax_name];
                                                                }
                                                            }
                                                            //check situation when data transfered by shortcode and data is in another html item
                                                            if (empty($terms_ids)) {
                                                                $tmp = $taxonomies;
                                                                unset($tmp[$show_how]);
                                                                if (!empty($tmp) AND is_array($tmp)) {
                                                                    foreach ($tmp as $s_h => $txs) {
                                                                        if (array_key_exists($tax_name, $txs)) {
                                                                            $terms_ids = $tmp[$s_h][$tax_name];
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            //***

                                                            MetaDataFilter::draw_term_childs('multi_select', $parent_id, $terms_ids, $tax_name, false, $hide, $widget_options);
                                                            ?>
                                                        </div>

                                                        <?php
                                                    }
                                                    ?>

                                                </div>

                                            <?php endforeach; ?>
                                        </div>


                                        <?php
                                        break;

                                    default:
                                        break;
                                }
                            }
                        }
                    } else {
                        $section_height = (int) get_post_meta($filter_id, 'widget_section_max_height', true);
                        ?>
                        <div class="mdf_select_emulator_container">
                            <dl class="dropdown">

                                <dt>
                                    <a href="javascript: void(0);">
                                        <span class="hida"><?php echo trim(esc_html__($h_items['post_title']), '~') ?></span>
                                    </a>
                                </dt>

                                <dd>
                                    <div class="mutliSelect">
                                        <ul <?php if ($section_height > 0): ?>style="max-height: <?php echo esc_attr($section_height) ?>px; height: auto;"<?php endif; ?>>

                                            <?php $li_counter = 0; ?>
                                            <?php foreach ($h_items['html_items'] as $key => $item) : $uid = uniqid(); ?>
                                                <?php
                                                $search_key = $key;
                                                if (isset($item['is_reflected']) AND $item['is_reflected'] == 1) {
                                                    $search_key = $item['reflected_key'];
                                                }

                                                //***

                                                $items_count = -1;
                                                if ($widget_options['show_checkbox_items_count']) {
                                                    $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, 1, $slug, 'checkbox');
                                                    if (!$items_count) {
                                                        if ($widget_options['hide_items_where_count_0']) {
                                                            continue; //IF NO ONE ITEM WHY NOT TO HIDE THIS?!
                                                        }
                                                    }
                                                }
                                                //+++
                                                $is_checked = isset($page_meta_data_filter[$key]) ? (int) $page_meta_data_filter[$key] : '~';
                                                $li_counter++;
                                                ?>



                                                <li>
                                                    <input type="hidden" name="mdf[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                                    <input <?php if ($items_count == 0 AND $widget_options['show_checkbox_items_count']): ?>disabled<?php endif; ?> type="checkbox" class="mdf_option_checkbox" id="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>" <?php if ($is_checked AND $is_checked != '~'): ?>checked<?php endif; ?> />
                                                    <label for="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>">&nbsp;<?php esc_html_e($item['name']); ?> 
														<?php if ($widget_options['show_checkbox_items_count']){ ?>
														
															<span class="medafi_dyn_number">(<?php echo esc_html($items_count);?> )</span>; 
															
														<?php }?>
                                                        <?php if (!empty($item['description'])): ?>
                                                            <span class="mdf_tooltip" title="<?php echo wp_kses_post(wp_unslash(str_replace('"', "'", esc_html__($item['description'])))); ?>">
                                                                <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                            </span>
                                                        <?php endif; ?>
                                                    </label>
                                                </li>

                                            <?php endforeach; ?>


                                            <?php if ($li_counter == 0): ?>
                                                <li>
                                                    <?php esc_html_e("no available items", 'meta-data-filter') ?>
                                                </li>
                                            <?php endif; ?>

                                        </ul>
                                    </div>
                                </dd>
                            </dl>

                        </div>
                        <?php
                    }
                    ?>
                    <input type="hidden" value="<?php echo esc_attr($filter_id) ?>" name="mdf[filter_post_blocks][]">
                </div>
                <?php
            }
        }
        ?>




        <input type="hidden" class="hidden_page_mdf_for_ajax" value="<?php echo(self::is_page_mdf_data() ? self::get_page_mdf_string() : '') ?>" />
        <input type="hidden" name="mdf_tax_bool" value="<?php echo esc_attr($mdf_tax_bool) ?>" />
        <input type="hidden" name="meta_data_filter_bool" value="<?php echo esc_attr($meta_data_filter_bool) ?>" />
        <input type="hidden" name="mdf[mdf_widget_options][slug]" value="<?php echo esc_attr($slug) ?>" />


        <?php
        if (isset($shortcode_options['options']['ignore_meta_data_filter_cat'])) {
            if ($shortcode_options['options']['ignore_meta_data_filter_cat'] == 1) {
                $shortcode_options['shortcode_cat'] = -1;
            }
        }
        ?>
        <input type="hidden" name="mdf[mdf_widget_options][meta_data_filter_cat]" value="<?php echo esc_html($shortcode_options['shortcode_cat']) ?>" />

        <input type="hidden" name="mdf[mdf_widget_options][show_items_count_dynam]" value="1" />
        <input type="hidden" name="mdf[mdf_widget_options][taxonomies_options_post_recount_dyn]" value="1" />
        <input type="hidden" name="mdf[mdf_widget_options][taxonomies_options_show_count]" value="1" />
        <input type="hidden" name="mdf[mdf_widget_options][taxonomies_options_hide_terms_0]" value="<?php echo esc_html($widget_options['taxonomies_options_hide_terms_0']) ?>" />

        <input type="hidden" name="meta_data_filter_cat" value="<?php echo esc_html($shortcode_options['shortcode_cat']) ?>" />



        <input type="hidden" name="mdf[mdf_widget_options][hide_meta_filter_values]" value="<?php echo(!$shortcode_has_meta ? 1 : 0); ?>" />

        <input type="hidden" name="mdf[mdf_widget_options][hide_tax_filter_values]" value="<?php echo(!$shortcode_has_taxonomies ? 1 : 0); ?>" />

        <input type="hidden" name="mdf[mdf_widget_options][search_result_page]" value="<?php echo esc_html((isset($shortcode_options['options']['search_result_page'])) ? $shortcode_options['options']['search_result_page'] : '') ?>" />
        <input type="hidden" name="mdf[mdf_widget_options][search_result_tpl]" value="<?php echo esc_html((isset($shortcode_options['options']['search_result_tpl'])) ? $shortcode_options['options']['search_result_tpl'] : '') ?>" />
        <input type="hidden" name="mdf[mdf_widget_options][woo_search_panel_id]" value="<?php echo esc_html((isset($shortcode_options['options']['woo_search_panel_id'])) ? $shortcode_options['options']['woo_search_panel_id'] : '') ?>" />
        <input type="hidden" name="mdf[mdf_widget_options][tax_only_in_filter]" value="<?php echo esc_html((isset($shortcode_options['options']['tax_only_in_filter'])) ? $shortcode_options['options']['tax_only_in_filter'] : 0) ?>" />



        <?php if ($shortcode_options['options']['auto_submit'] != 1 AND!$ajax_results): ?>
            <div class="mdf_shortcode_submit_button">
                <?php
                $filter_button_text = (isset($shortcode_options['options']['filter_button_text']) AND!empty($shortcode_options['options']['filter_button_text'])) ? $shortcode_options['options']['filter_button_text'] : esc_html__("Filter", 'meta-data-filter');
                ?>
                <input type="submit" class="button mdf_button" name="" value="<?php esc_html_e($filter_button_text) ?>" />
            </div>
        <?php endif; ?>

        <?php if ($shortcode_options['options']['show_reset_button'] AND self::is_page_mdf_data()): ?>
            <div class="mdf_shortcode_reset_button">
                <?php
                $reset_link = $shortcode_options['options']['custom_reset_link'];
                if ($reset_link == 'self') {
                    if (is_page() OR is_single()) {
                        global $wp_query;
                        $reset_link = esc_url(apply_filters('the_permalink', get_permalink($wp_query->queried_object->ID)));
                    } else {
                        if (isset($_SERVER['SCRIPT_URI'])) {
                            $reset_link = sanitize_text_field($_SERVER['SCRIPT_URI']);
                        }
                    }
                }

                $reset_link = trim($reset_link, '"');
                ?>
                <input type="button" <?php if (!empty($reset_link)): ?>onclick='window.location = "<?php echo esc_url_raw($reset_link) ?>"'<?php endif; ?> class="button mdf_button mdf_reset_button" name="" value="<?php esc_html_e('Reset', 'meta-data-filter') ?>" />
            </div>
        <?php endif; ?>


        <div class="mdf_one_moment_txt" style="display: none;">
            <span><?php MetaDataFilterHtml::draw_tax_loader(); ?></span>
        </div>

    </form>

    <?php
//+++
    if (isset($shortcode_options['options']['search_result_page']) AND !empty($shortcode_options['options']['search_result_page'])) {
        $search_url = $shortcode_options['options']['search_result_page'];
    } else {
        $search_url = MetaDataFilterCore::get_search_url_page();
    }

//WPML compatibility
    if (class_exists('SitePress')) {
        $search_url = str_replace(site_url(), site_url() . '/' . ICL_LANGUAGE_CODE, $search_url);
    }

//+++
    if (substr_count($search_url, 'page_id')) {
        $search_url = $search_url . '&';
    } else {
        $search_url = $search_url . '?';
    }
    ?>
    <script>
        //DYNAMIC SCRIPT DEPENDING OF SETTINGS
        jQuery(function () {
            mdf_init_search_form("<?php echo esc_attr($uniqid) ?>", "<?php echo esc_attr($slug) ?>", "<?php echo esc_html($search_url) ?>", <?php echo esc_attr($shortcode_options['options']['auto_submit']) ?>, <?php echo esc_attr($shortcode_options['options']['ajax_auto_submit']) ?>);
        });
    </script>
    <div class="clear"></div>
</div>
