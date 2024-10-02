<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
if (!empty($filter_block['items'])):

    $mdtf_title_sections = "h4";
    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
    ?>   
    <input type="hidden" name="mdf[filter_post_blocks][]" value="<?php echo esc_attr($filter_post_id) ?>" />
    <?php
    $section_toggle = (int) get_post_meta($filter_post_id, 'widget_section_toggle', true);
    if (isset($page_meta_data_filter['filter_post_blocks_toggles'][$counter_block])) {
        $section_toggle = $page_meta_data_filter['filter_post_blocks_toggles'][$counter_block];
    }
    //***
    $section_sel_emulator = (int) get_post_meta($filter_post_id, 'widget_section_sel_emulator', true);
    ?>
    <input type="hidden" class="mdf_filter_post_blocks_toggles" name="mdf[filter_post_blocks_toggles][]" value="<?php echo esc_attr($section_toggle) ?>" />
    <?php if (substr($filter_block['name'], 0, 1) !== '~'): ?>
        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title">
        <?php esc_html_e($filter_block['name']) ?><?php MetaDataFilter::draw_front_toggle($section_toggle); ?>
        </<?php echo esc_attr($mdtf_title_sections) ?>>
    <?php endif; ?>

    <?php
    $counter = 0;
    $icon = MetaDataFilter::get_application_uri() . 'images/tooltip-info.png';
    $settings = MetaDataFilter::get_settings();
    if (!empty($settings['tooltip_icon'])) {
        $icon = $settings['tooltip_icon'];
    }
    $section_height = (int) get_post_meta($filter_post_id, 'widget_section_max_height', true);

    //*** additional taxonomies for Pre-sale question:
    //I have woocommerce with brands plugin installed, each brand page shows the products of this brand,
    //can this plugin filter those products based on category?
    if (isset($widget_options['additional_taxonomies']) AND!empty($widget_options['additional_taxonomies'])) {
        MetaDataFilter::add_additional_taxonomies($widget_options['additional_taxonomies']);
    }
    //***
    ?>
    <div class="mdf_filter_section mdf_filter_section_<?php echo esc_attr($counter_block) ?> <?php if ($section_height > 0 AND!$section_sel_emulator): ?>mdf_filter_section_scrolled<?php endif; ?> <?php if ($section_toggle == 2): ?>mdf_front_toggle_closed_section<?php endif; ?>" style="<?php if ($section_height): ?>max-height:<?php echo esc_html($section_height); ?>px;<?php endif; ?>">


        <?php if (!$section_sel_emulator): ?>
            <table class="mdtf-w98p">
                <?php foreach ($filter_block['items'] as $key => $item) : $uid = uniqid(); ?>

                    <?php
                    $search_key = $key;
                    if (isset($item['is_reflected']) AND $item['is_reflected'] == 1) {
                        $search_key = $item['reflected_key'];
                    }

                    //+++
                    if ($widget_options['hide_meta_filter_values'] == 'true') {
                        if ($item['type'] != 'taxonomy') {
                            continue;
                        }
                    }

                    if ($widget_options['hide_tax_filter_values'] == 'true') {
                        if ($item['type'] == 'taxonomy') {
                            continue;
                        }
                    }
                    ?>
                    <tr>

                        <?php if ($item['type'] == 'taxonomy'): ?>
                            <?php if (!empty($widget_options['taxonomies']) AND is_array($widget_options['taxonomies'])): ?>
                                <td>
                                    <?php foreach ($widget_options['taxonomies'] as $tax_name => $v) : ?>
                                        <div class="mdf_input_container <?php if ($widget_options['act_without_button']): ?>mdf_tax_auto_submit<?php endif; ?> <?php if ($widget_options['ajax_items_recount']): ?>mdf_tax_ajax_autorecount<?php endif; ?> mdf_section_tax mdf_section_tax_<?php echo esc_attr($tax_name) ?>">
                                            <?php $show_how = $widget_options['taxonomies_options_show_how'][$tax_name]; ?>

                                            <input type="hidden" name="mdf[taxonomy][<?php echo esc_attr($show_how) ?>][<?php echo esc_attr($tax_name) ?>]" value="" />
                                            <?php
                                            $hide = '';
                                            if (isset($widget_options['taxonomies_options_hide'][$tax_name])) {
                                                $hide = $widget_options['taxonomies_options_hide'][$tax_name];
                                            }
                                            $section_tax_toggle = 0;
                                            if (isset($widget_options['taxonomies_options_terms_section_toggle'][$tax_name])) {
                                                $section_tax_toggle = (int) $widget_options['taxonomies_options_terms_section_toggle'][$tax_name];
                                            }
                                            if (isset($page_meta_data_filter['filter_post_blocks_toggles_tax'][$tax_name])) {
                                                $section_tax_toggle = $page_meta_data_filter['filter_post_blocks_toggles_tax'][$tax_name];
                                            }
                                            //***
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

                                                //+++
                                                //draw selects
                                                if ($show_how == 'select') {

                                                    if (!empty($terms_ids) AND is_array($terms_ids)) {
                                                        foreach ($terms_ids as $k => $term_id) {
                                                            if ($term_id == -1 AND $term_id == 0) {
                                                                unset($terms_ids[$k]);
                                                            }
                                                        }
                                                    }
                                                    ?>


                                                    <?php
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
                                                    $mdtf_title_sections = "h4";
                                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                    $section_max_height = isset($widget_options['taxonomies_options_checkbox_max_height'][$tax_name]) ? $widget_options['taxonomies_options_checkbox_max_height'][$tax_name] : 0;
                                                    ?>
                                                    <input type="hidden" class="mdf_filter_post_blocks_toggles" name="mdf[filter_post_blocks_toggles_tax][<?php echo esc_attr($tax_name) ?>]" value="<?php echo esc_html($section_tax_toggle) ?>" />

                                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title">
                                                    <?php
                                                    $tax_title = "";
                                                    if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND!empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                                        $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                                    } else {
                                                        $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                    }
                                                    echo esc_html($tax_title);
                                                    ?><?php MetaDataFilter::draw_front_toggle($section_tax_toggle);
                                                    ?>
                                                    </<?php echo esc_attr($mdtf_title_sections) ?>>
                                                    <div class="mdf_tax_filter_section mdf_tax_filter_section_<?php echo $tax_name ?> <?php if ($section_max_height): ?>mdf_filter_section_scrolled<?php endif; ?> <?php if ($section_tax_toggle == 2): ?>mdf_front_toggle_closed_section<?php endif; ?>" <?php if ($section_max_height): ?>style="max-height:<?php echo esc_html($section_max_height); ?>px;"<?php endif; ?>>
                                                        <?php
                                                        $parent_id = 0;
                                                        MetaDataFilter::draw_term_childs('checkbox', $parent_id, $terms_ids, $tax_name, false, $hide, $widget_options);
                                                        ?>
                                                    </div>
                                                    <?php
                                                }
                                                //*** draw labels
                                                if ($show_how == 'label') {
                                                    $mdtf_title_sections = "h4";
                                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                    $section_max_height = isset($widget_options['taxonomies_options_checkbox_max_height'][$tax_name]) ? $widget_options['taxonomies_options_checkbox_max_height'][$tax_name] : 0;
                                                    ?>
                                                    <input type="hidden" class="mdf_filter_post_blocks_toggles" name="mdf[filter_post_blocks_toggles_tax][<?php echo esc_attr($tax_name) ?>]" value="<?php echo esc_html($section_tax_toggle) ?>" />

                                                    <<?php echo $mdtf_title_sections ?> class="data-filter-section-title">
                                                    <?php
                                                    $tax_title = "";
                                                    if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND!empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                                        $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                                    } else {
                                                        $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                    }
                                                    echo esc_html($tax_title);
                                                    ?><?php MetaDataFilter::draw_front_toggle($section_tax_toggle);
                                                    ?>
                                                    </<?php echo esc_attr($mdtf_title_sections) ?>>

                                                    <div class="mdf_tax_filter_section mdf_tax_filter_section_<?php echo esc_attr($tax_name) ?> <?php if ($section_max_height): ?>mdf_filter_section_scrolled<?php endif; ?> <?php if ($section_tax_toggle == 2): ?>mdf_front_toggle_closed_section<?php endif; ?>" <?php if ($section_max_height): ?>style="max-height:<?php echo esc_html($section_max_height); ?>px;"<?php endif; ?>>
                                                        <?php
                                                        $parent_id = 0;
                                                        MetaDataFilter::draw_term_childs('label', $parent_id, $terms_ids, $tax_name, false, $hide, $widget_options);
                                                        ?>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                if ($show_how == 'label') {
                                                    $mdtf_title_sections = "h4";
                                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                    $terms_ids = 0;
                                                    $section_max_height = isset($widget_options['taxonomies_options_checkbox_max_height'][$tax_name]) ? $widget_options['taxonomies_options_checkbox_max_height'][$tax_name] : 0;
                                                    ?>
                                                    <input type="hidden" class="mdf_filter_post_blocks_toggles" name="mdf[filter_post_blocks_toggles_tax][<?php echo esc_attr($tax_name) ?>]" value="<?php echo esc_html($section_tax_toggle) ?>" />

                                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title">
                                                    <?php
                                                    $tax_title = "";
                                                    if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND!empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                                        $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                                    } else {
                                                        $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                    }
                                                    echo esc_html($tax_title);
                                                    ?><?php MetaDataFilter::draw_front_toggle($section_tax_toggle);
                                                    ?>
                                                    </<?php echo esc_attr($mdtf_title_sections) ?>>

                                                    <div class="mdf_tax_filter_section mdf_tax_filter_section_<?php echo esc_attr($tax_name) ?> <?php if ($section_max_height): ?>mdf_filter_section_scrolled<?php endif; ?> <?php if ($section_tax_toggle == 2): ?>mdf_front_toggle_closed_section<?php endif; ?>" <?php if ($section_max_height): ?>style="max-height:<?php echo esc_html($section_max_height); ?>px;"<?php endif; ?>>
                                                        <?php
                                                        $parent_id = 0;
                                                        MetaDataFilter::draw_term_childs('label', $parent_id, $terms_ids, $tax_name, false, $hide, $widget_options);
                                                        ?>
                                                    </div>
                                                    <?php
                                                }

                                                if ($show_how == 'checkbox') {
                                                    $mdtf_title_sections = "h4";
                                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                                    $section_max_height = isset($widget_options['taxonomies_options_checkbox_max_height'][$tax_name]) ? $widget_options['taxonomies_options_checkbox_max_height'][$tax_name] : 0;
                                                    ?>
                                                    <input type="hidden" class="mdf_filter_post_blocks_toggles" name="mdf[filter_post_blocks_toggles_tax][<?php echo esc_attr($tax_name) ?>]" value="<?php echo esc_html($section_tax_toggle) ?>" />
                                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title">
                                                    <?php
                                                    $tax_title = "";
                                                    if (isset($widget_options['taxonomies_options_tax_title'][$tax_name]) AND!empty($widget_options['taxonomies_options_tax_title'][$tax_name])) {
                                                        $tax_title = $widget_options['taxonomies_options_tax_title'][$tax_name];
                                                    } else {
                                                        $tax_title = esc_html__(MetaDataFilterHtml::get_term_label_by_name($tax_name));
                                                    }
                                                    echo esc_html($tax_title);
                                                    ?><?php MetaDataFilter::draw_front_toggle($section_tax_toggle);
                                                    ?>
                                                    </<?php echo esc_attr($mdtf_title_sections) ?>>
                                                    <div class="mdf_tax_filter_section mdf_tax_filter_section_<?php echo esc_attr($tax_name) ?> <?php if ($section_max_height): ?>mdf_filter_section_scrolled<?php endif; ?> <?php if ($section_tax_toggle == 2): ?>mdf_front_toggle_closed_section<?php endif; ?>" <?php if ($section_max_height): ?>style="max-height:<?php echo esc_html($section_max_height); ?>px;"<?php endif; ?>>
                                                        <?php
                                                    }
                                                    if ($show_how != 'multi_select' AND $show_how != 'label') {
                                                        MetaDataFilter::draw_term_childs($show_how, 0, 0, $tax_name, false, $hide, $widget_options);
                                                    }
                                                    ?>
                                                    <div class="mdf_taxonomy_child_container"><?php MetaDataFilterHtml::draw_tax_loader(); ?></div>
                                                </div>
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
                                                <div class="mdf_input_container mdf_tax_filter_section_<?php echo esc_attr($tax_name) ?> <?php if ($widget_options['act_without_button']): ?>mdf_tax_auto_submit<?php endif; ?>">
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
                                </td>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                        //start range  select    
                        if ($item['type'] == 'range_select') {
                            ?>
                            <?php
                            $min = $max = 0;
                            if (isset($page_meta_data_filter[$key])) {
                                $min = empty($page_meta_data_filter[$key]['from']) ? 0 : $page_meta_data_filter[$key]['from'];
                                $max = empty($page_meta_data_filter[$key]['to']) ? 100 : $page_meta_data_filter[$key]['to'];
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

                            if (empty($page_meta_data_filter[$key])) {
                                $page_meta_data_filter[$key] = (int) $min . '^' . $max;
                            }

                            //+++
                            $this_item_is_hidden = false;
                            $items_count = 'xxx';
                            if ($widget_options['show_slider_items_count'] AND!$this_item_is_hidden) {
                                $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, array($min, $max), $slug, 'slider');
                                if (!$items_count) {
                                    if ($widget_options['hide_items_where_count_0']) {
                                        $this_item_is_hidden = true;
                                    }
                                }
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
                            <td>
                                <div class="mdf_input_container mdf_range_select_container_<?php echo esc_attr($key) ?> " <?php if ($this_item_is_hidden): ?>style="display: none;"<?php endif; ?>>
                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:&nbsp;<span class="mdf_range">
                                    </span> &nbsp;
									<?php if ($widget_options['show_slider_items_count'] AND !$this_item_is_hidden){ 
										?>
										<span class="<?php echo esc_attr($key) ?>  medafi_dyn_number">(<?php echo esc_html($items_count); ?>)</span>
										<?php
										}?> &nbsp;
									<?php if (!empty($item['description'])): ?>
                                        <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                            <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                        </span>
                                    <?php endif; ?>
                                    </<?php echo esc_attr($mdtf_title_sections) ?>>
                                    <?php
                                    echo do_shortcode("[mdf_range_select meta_key='" . $key . "'  min=" . $preset_min . "  max=" . $preset_max . " step=" . $range_select_step . " cur_max=" . $max . "  cur_min=" . $min . "  prefix='" . $range_select_prefix . "' postfix='" . $range_select_postfix . "'  ]");
                                    ?></div>
                            </td>
                            <?php
                        }
                        //end range  select    
                        ?>

                        <?php
                        //start by_author
                        if ($item['type'] == 'by_author') {
                            $mdtf_title_sections = "h5";
                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                            $selected_option = isset($page_meta_data_filter[$key]) ? $page_meta_data_filter[$key] : NULL;
                            ?>
                            <td>
                                <div class="mdf_input_container mdf_by_author_container_<?php echo esc_attr($key) ?> " >
                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:&nbsp;<span class="mdf_range">
                                    </span> 
                                    </<?php echo esc_attr($mdtf_title_sections) ?>>
                                    <?php
                                    $users = get_users(array('count_total' => false, 'fields' => array('ID', 'display_name', 'user_nicename'),));
                                    ?>
                                    <select name="mdf[<?php echo esc_attr($key) ?>]" id="<?php echo esc_attr($key) ?>" class="mdf_filter_select" >
                                        <option value="-1"> <?php esc_html_e("Select author", 'meta-data-filter') ?></option>
                                        <?php
                                        foreach ($users as $user) {
                                            ?>
                                            <option value="<?php echo esc_attr($user->ID) ?>" <?php echo($user->ID == $selected_option ? 'selected' : '') ?> > <?php echo esc_html($user->user_nicename) ?></option> 
                                            <?php
                                        }
                                        ?>
                                    </select>

                                </div>
                            </td>

                            <?php
                        }
                        //end by_author
                        ?>



                        <?php if ($item['type'] == 'slider'): ?>
                            <?php
                            wp_enqueue_style('mdf_front_slider', self::get_application_uri() . 'css/front_slider.css');
                            $min = $max = 0;
                            if (isset($page_meta_data_filter[$key]) AND is_string($page_meta_data_filter[$key])) {
                                list($min, $max) = explode('^', $page_meta_data_filter[$key]);
                            } else {
                                $page_meta_data_filter[$key] = "";
                            }
                            if (empty($item['slider'])) {
                                $item['slider'] = "0^100";
                            }
                            list($preset_min, $preset_max) = explode('^', $item['slider']);

                            $slider_step = isset($item['slider_step']) ? $item['slider_step'] : 0;
                            $slider_prefix = isset($item['slider_prefix']) ? $item['slider_prefix'] : '';
                            $slider_postfix = isset($item['slider_postfix']) ? $item['slider_postfix'] : '';
                            $slider_prettify = isset($item['slider_prettify']) ? $item['slider_prettify'] : 1;

                            if ($min == $max AND $max == 0) {
                                $min = $preset_min;
                                $max = $preset_max;
                            }
                            if (empty($page_meta_data_filter[$key])) {
                                $page_meta_data_filter[$key] = (int) $min . '^' . $max;
                            }
                            //+++

                            if ($widget_options['show_slider_items_count']) {
                                $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, array($min, $max), $slug, 'slider');
                            }

                            //+++
                            ?>
                            <td>
                                <?php
                                if ($search_key === '_price' AND ( isset($item['woo_price_auto']) AND $item['woo_price_auto'] == 1)) {
                                    $mm = MDTF_HELPER::get_woo_min_max_price();
                                    if (!empty($mm)) {
                                        $preset_max = $mm['max'];
                                        $preset_min = $mm['min'];
                                        if (!self::is_page_mdf_data()) {
                                            $max = $mm['max'];
                                            $min = $mm['min'];
                                        }
                                        if ($preset_max < $max) {
                                            $max = $preset_max;
                                        }
                                        if ($preset_min > $min) {
                                            $min = $preset_min;
                                        }
                                    }
                                }
                                ?>
                                <div class="mdf_input_container mdf_slider_<?php echo esc_attr($key) ?>">

                                    <?php
                                    $range_slider_type = 'double';
                                    if (!isset($item['slider_range_value'])) {
                                        $item['slider_range_value'] = 0;
                                    }
                                    if ($item['slider_range_value'] == 1) {
                                        $range_slider_type = 'single';
                                    }

                                    $mdtf_title_sections = "h5";
                                    $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                    ?>

                                    <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdf_range_slider_header mdtf-mb4">
									<span class="mdf_range_slider_header_txt"><?php esc_html_e($item['name']) ?>:&nbsp;</span>
									<span class="mdf_range">
										<input type="text" value="<?php echo esc_attr($min) ?>" class="mdf_range_min" data-slider-id="ui_slider_item_<?php echo esc_attr($uid) ?>" />
                                        <?php if ($range_slider_type == 'double'): ?>
                                            &nbsp;-&nbsp;<input type="text" value="<?php echo esc_attr($max) ?>" class="mdf_range_max" data-slider-id="ui_slider_item_<?php echo esc_attr($uid) ?>" />
                                        <?php endif; ?>
                                    </span> &nbsp;<?php if ($widget_options['show_slider_items_count']) echo esc_html('(' . $items_count . ')'); ?>&nbsp;<?php if (!empty($item['description'])): ?>
                                        <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                            <img src="<?php echo esc_url($icon) ?>" alt="help" />
                                        </span>
                                    <?php endif; ?></<?php echo esc_attr($mdtf_title_sections) ?> >
                                    <div class="ui_slider_item ui_slider_item_<?php echo esc_attr($form_uniqid) ?>" id="ui_slider_item_<?php echo esc_attr($uid) ?>"></div>



                                    <input type="hidden" name="mdf[<?php echo esc_attr($key) ?>]" id="<?php echo esc_attr($uid) ?>" data-type="<?php echo esc_attr($range_slider_type) ?>" data-min="<?php echo esc_attr($preset_min) ?>" data-max="<?php echo esc_attr($preset_max) ?>" data-min-now="<?php echo esc_attr($min) ?>" data-max-now="<?php echo esc_attr($max) ?>" data-step="<?php echo esc_attr($slider_step) ?>" data-slider-prefix="<?php echo esc_attr($slider_prefix) ?>" data-slider-postfix="<?php echo esc_attr($slider_postfix) ?>" data-slider-prettify="<?php echo esc_attr($slider_prettify) ?>" value="<?php echo esc_html($page_meta_data_filter[$key]) ?>" />
                                </div>
                            </td>
                        <?php endif; ?>


                        <?php if ($item['type'] == 'checkbox'): ?>
                            <?php
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
                            ?>
                            <td>
                                <div class="mdf_input_container">
                                    <input type="hidden" name="mdf[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                    <input <?php if ($items_count == 0 AND $widget_options['show_checkbox_items_count']): ?>disabled<?php endif; ?> type="checkbox" class="mdf_option_checkbox" id="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>" <?php if ($is_checked AND $is_checked != '~'): ?>checked<?php endif; ?> />
                                    <label for="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>">&nbsp;<?php esc_html_e($item['name']); ?> <?php if ($widget_options['show_checkbox_items_count']) echo '<span class="medafi_dyn_number">('; echo esc_html($items_count); echo ')</span>'; ?>
                                        <?php if (!empty($item['description'])): ?>
                                            <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </td>
                        <?php endif; ?>
                        <?php if ($item['type'] == 'label'): ?>
                            <?php
                            $items_count = -1;
                            $count_string = "";
                            if ($widget_options['show_checkbox_items_count']) {
                                $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, 1, $slug, 'checkbox');
                                if (!$items_count) {
                                    if ($widget_options['hide_items_where_count_0']) {
                                        continue; //IF NO ONE ITEM WHY NOT TO HIDE THIS?!
                                    }
                                }
                                $count_string = '<span class="mdf_label_count">' . $items_count . '</span>';
                            }
                            //+++
                            $is_checked = isset($page_meta_data_filter[$key]) ? (int) $page_meta_data_filter[$key] : '~';
                            ?>
                            <td>
                                <div class="mdf_input_container">  
                                    <?php
                                    if ($widget_options['show_checkbox_items_count'] AND!($is_checked AND $is_checked != '~')) {
                                        echo $count_string;
                                    }
                                    ?>
                                    <span class="mdf_label_item <?php if ($is_checked AND $is_checked != '~') echo 'checked'; ?>">
                                        <?php esc_html_e($item['name']); ?>
                                        <input class="mdf_label_hiden" type="hidden" name="mdf[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                        <input style="display: none;"  class="mdf_label_term mdf_option_label"  <?php if ($items_count == 0 AND $widget_options['show_checkbox_items_count']): ?>disabled<?php endif; ?> type="checkbox"  id="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>" <?php if ($is_checked AND $is_checked != '~'): ?>checked<?php endif; ?> />
                                    </span>

                                </div>
                            </td>
                        <?php endif; ?>


                        <?php if ($item['type'] == 'textinput'): ?>
                            <?php
                            $text = isset($page_meta_data_filter[$key]) ? $page_meta_data_filter[$key] : '';

                            $mdtf_title_sections = "h5";
                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                            ?>
                            <td>
                                <div class="mdf_input_container">
                                    <?php if (substr($item['name'], 0, 1) !== '~'): ?>
                                        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php esc_html_e($item['name']) ?>:
                                        <?php if (!empty($item['description'])): ?>
                                            <label for="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>">

                                                <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                    <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                </span>

                                            </label>
                                        <?php endif; ?>
                                        </<?php echo esc_attr($mdtf_title_sections) ?>>
                                    <?php endif; ?>
                                    <input type="text" class="mdf_textinput" placeholder="<?php esc_html_e($item['textinput']) ?>" name="mdf[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($text) ?>" />&nbsp;

                                </div>
                            </td>
                        <?php endif; ?>


                        <?php if ($item['type'] == 'calendar'): ?>
                            <?php
                            //wp_enqueue_style('jquery-ui-styles', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css');
							wp_enqueue_style('jquery-ui-styles', MetaDataFilterCore::get_application_uri() . '/css/jquery-ui-themes/themes/smoothness/jquery-ui.css');
                            $calendar_date = isset($page_meta_data_filter[$key]) ? $page_meta_data_filter[$key] : '';
                            $from = "";
                            $to = "";
                            if (!empty($calendar_date) AND is_array($calendar_date)) {
                                $from = $calendar_date['from'];
                                $to = $calendar_date['to'];
                            }
                            $mdtf_title_sections = "h5";
                            $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                            ?>
                            <td>
                                <div class="mdf_input_container">
                                    <?php if (empty($item['name']) OR substr($item['name'], 0, 1) !== '~'): ?>
                                        <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title"><?php esc_html_e($item['name']) ?>:
                                        <?php if (!empty($item['description'])): ?>
                                            <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                            </span>
                                        <?php endif; ?>
                                        </<?php echo esc_attr($mdtf_title_sections) ?>>
                                    <?php endif; ?>
                                    <input type="hidden" name="mdf[<?php echo esc_attr($key) ?>][from]" value="<?php echo esc_attr($from) ?>" />
                                    <input type="text" readonly="readonly" class="mdf_calendar mdf_calendar_from" placeholder="<?php esc_html_e('from', 'meta-data-filter') ?>" />
                                    &nbsp;-&nbsp;
                                    <input type="hidden" name="mdf[<?php echo esc_attr($key) ?>][to]" value="<?php echo esc_attr($to) ?>" />
                                    <input type="text" readonly="readonly" class="mdf_calendar mdf_calendar_to" placeholder="<?php esc_html_e('to', 'meta-data-filter') ?>" />&nbsp;<label for="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>">

                                    </label>
                                </div>
                            </td>
                        <?php endif; ?>

                        <?php if ($item['type'] == 'select'): ?>
                            <?php if (!empty($item['select'])): ?>
                                <?php
                                $selected = isset($page_meta_data_filter[$key]) ? $page_meta_data_filter[$key] : NULL;
                                $select_option_title = (isset($item['select_option_title']) AND!empty($item['select_option_title'])) ? $item['select_option_title'] : esc_html__($widget_options['title_for_any']);
                                $mdtf_title_sections = "h5";
                                $mdtf_title_sections = apply_filters('mdf_tag_title_sections', $mdtf_title_sections);
                                ?>
                                <td>
                                    <div class="mdf_input_container">
                                        <?php if (substr($item['name'], 0, 1) !== '~'): ?>
                                            <<?php echo esc_attr($mdtf_title_sections) ?> class="data-filter-section-title mdtf-mb4"><?php esc_html_e($item['name']) ?>:&nbsp;<?php if (!empty($item['description'])): ?>
                                                <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
                                                    <img src="<?php echo esc_url_raw($icon) ?>" alt="help" />
                                                </span>
                                            <?php endif; ?>
                                            </<?php echo esc_attr($mdtf_title_sections) ?>>
                                        <?php endif; ?>
                                        <select size="<?php echo (isset($item['select_size']) ? abs((int) $item['select_size']) : 1) ?>" name="mdf[<?php echo esc_attr($key) ?>]" id="<?php echo esc_attr($key) ?>" class="mdf_filter_select">
                                            <option value="~"><?php echo $select_option_title ?></option>

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
                                                if (isset($item['select_range_value']) AND $item['select_range_value'] == 1) {
                                                    $tmp = explode('-', $select_option_key);
                                                    $is_range = MDTF_HELPER::is_slider_range_value($tmp); //range drop down for price                            
                                                }

                                                //***

                                                if ($widget_options['show_select_items_count']) {
                                                    $items_count = MetaDataFilterHtml::get_item_posts_count($page_meta_data_filter, $search_key, $select_option_key, $slug, 'select');
                                                    if (!$items_count) {
                                                        if ($widget_options['hide_items_where_count_0'] AND!$is_range) {
                                                            continue; //IF NO ONE ITEM WHY NOT TO HIDE THIS?!
                                                        }
                                                    }
                                                }
                                                ?>

                                                <?php if ($is_range): ?>
                                                    <option value="<?php echo esc_html($select_option_key) ?>" <?php echo($selected == $select_option_key ? 'selected' : '') ?>><?php esc_html_e($value); ?></option>
                                                <?php else: ?>
                                                    <option <?php if ($items_count == 0 AND $widget_options['show_select_items_count']): ?>disabled<?php endif; ?> value="<?php echo esc_html($select_option_key) ?>" <?php echo($selected == $select_option_key ? 'selected' : '') ?>><?php esc_html_e($value); ?> <?php if ($widget_options['show_select_items_count']) echo esc_html('(' . $items_count . ')'); ?></option>
                                                <?php endif; ?>

                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            <?php endif; ?>
                        <?php endif; ?>


                    </tr>


                    <?php $counter++; ?>
                <?php endforeach; ?>

            </table>

        <?php else: ?>
            <?php if ($widget_options['hide_meta_filter_values'] != 'true'): ?>
                <table class="mdtf-w98p">
                    <tr>
                        <td>
                            <div class="mdf_select_emulator_container">
                                <dl class="dropdown">

                                    <dt>
                                        <a href="javascript: void(0);">
                                            <span class="hida"><?php echo trim(esc_html__($filter_block['name']), '~') ?></span>
                                        </a>
                                    </dt>

                                    <dd>
                                        <div class="mutliSelect">
                                            <ul <?php if ($section_height > 0): ?>style="max-height: <?php echo esc_attr($section_height) ?>px; height: auto;"<?php endif; ?>>

                                                <?php $li_counter = 0; ?>

                                                <?php foreach ($filter_block['items'] as $key => $item) : $uid = uniqid(); ?>
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
                                                        <label for="<?php echo esc_attr($key) ?>_<?php echo esc_attr($uid) ?>">&nbsp;<?php esc_html_e($item['name']); ?> <?php if ($widget_options['show_checkbox_items_count']) echo '<span class="medafi_dyn_number">('; echo esc_html($items_count); echo ')</span>'; ?>
                                                            <?php if (!empty($item['description'])): ?>
                                                                <span class="mdf_tooltip" title="<?php echo str_replace('"', "'", esc_html__($item['description'])); ?>">
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
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ((isset($widget_options['show_filter_button_after_each_block']) AND $widget_options['show_filter_button_after_each_block']) AND $counter > 0): ?>
            <table class="mdf_update_button_table_<?php echo esc_attr($filter_post_id) ?>">
                <tr>
                    <td>
                        <input type="submit" class="mdf_button" name="" value="<?php esc_html_e($widget_options['title_for_filter_button']) ?>" /><br />
                        <br />
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>



    <?php
endif;
?>