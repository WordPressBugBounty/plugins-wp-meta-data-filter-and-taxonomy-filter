<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<div class="woobe-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>

<div class="wrap">

    <h2><?php if (MetaDataFilter::$is_free): ?><a href="https://codecanyon.pluginus.net/item/wordpress-meta-data-taxonomies-filter/7002700" target="_blank" class="button button-primary"><?php esc_html_e("GET Premium version", 'meta-data-filter') ?></a>&nbsp;<?php endif; ?><?php esc_html_e("MDTF Settings", 'meta-data-filter') ?> v.<?php echo esc_html(MetaDataFilter::get_plugin_ver()) ?>&nbsp;&nbsp;&nbsp;<a href="https://wp-filter.com/documentation/" target="_blank" class="button"><?php esc_html_e("Read", 'meta-data-filter') ?></a>&nbsp;&amp;&nbsp; <a href="https://wp-filter.com/video/" target="_blank" class="button"><?php esc_html_e("Watch", 'meta-data-filter') ?></a></h2>


    <?php if (!empty($_POST)): ?>
        <div class="updated settings-error" id="setting-error-settings_updated"><p><strong><?php esc_html_e("Settings are saved.", 'meta-data-filter') ?></strong></p></div>
    <?php endif; ?>


    <form action="<?php echo wp_nonce_url(admin_url('edit.php?post_type=' . MetaDataFilterCore::$slug . '&page=mdf_settings'), "update_mdtf" . MetaDataFilterCore::$slug) ?>" method="post">

        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?php esc_html_e("Main settings", 'meta-data-filter') ?></a></li>
                <li><a href="#tabs-3"><?php esc_html_e("Front interface", 'meta-data-filter') ?></a></li>
                <li><a href="#tabs-2"><?php esc_html_e("Miscellaneous", 'meta-data-filter') ?></a></li>
                <?php if (class_exists('WooCommerce')): ?>
                    <li><a href="#tabs-4"><?php esc_html_e("WooCommerce", 'meta-data-filter') ?></a></li>
                <?php endif; ?>
                <li><a href="#tabs-5"><?php esc_html_e("In-Built Pagination", 'meta-data-filter') ?></a></li>


                <?php if (class_exists('MDF_POSTS_MESSENGER')): ?>
                    <li><a href="#tabs-6"><?php esc_html_e("Messenger", 'meta-data-filter') ?></a></li>
                <?php endif; ?>
                <?php if (class_exists('MDF_SEARCH_STAT')): ?>
                    <li><a href="#tabs-7"><?php esc_html_e("Statistic", 'meta-data-filter') ?></a></li>
                <?php endif; ?>

                <li><a href="#tabs-8"><?php esc_html_e("Advanced options", 'meta-data-filter') ?></a></li>
                <li><a href="#tabs-9"><?php esc_html_e("Info", 'meta-data-filter') ?></a></li>
            </ul>


            <div id="tabs-1">
                <p>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Search Result Page", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" value="<?php echo esc_html($data['search_url']) ?>" name="meta_data_filter_settings[search_url]">
                                <p class="description"><?php esc_html_e("Link to site page where shows searching results", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Output search template", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" value="<?php echo esc_html($data['output_tpl']) ?>" name="meta_data_filter_settings[output_tpl]">
                                <p class="description"><?php esc_html_e("Output template, search by default. For example: search,archive,content or your custom which is in current wp theme. If you want to set double name template, write it in such manner for example: content-special. If you do not understood - leave search!", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Supported post types", 'meta-data-filter') ?></label></th>
                            <td>

                                <?php foreach (MetaDataFilterCore::get_post_types() as $post_type => $post_type_name) : ?>

                                    <fieldset>
                                        <label>
                                            <input type="checkbox" <?php if (@in_array($post_type_name, $data['post_types'])) echo 'checked'; ?> value="<?php echo esc_html($post_type_name) ?>" name="meta_data_filter_settings[post_types][]" />
                                            <?php echo esc_html($post_type_name) ?>
                                        </label>
                                    </fieldset>

                                <?php endforeach; ?>
                                <p class="description"><?php esc_html_e("Check post types which should be searched", 'meta-data-filter') ?></p>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Reset custom link", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" value="<?php echo esc_html(isset($data['reset_link'])?$data['reset_link']:""); ?>" name="meta_data_filter_settings[reset_link]">
                                <p class="description"><?php esc_html_e("Leave this field empty if you do not need this. Of course each widget and shortcode has such option too.", 'meta-data-filter') ?></p>
                            </td>
                        </tr>

                         <tr valign="top">
                            <th scope="row" class="mdf_for_premium_label"><label><?php esc_html_e("Results per page", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" readonly="" value="0">
                                <p class="description"><?php esc_html_e("Leave this field empty if you want to use wordpress or your theme settings.", 'meta-data-filter') ?></p>

                            </td>
                        </tr>
                    </tbody>
                </table>
                </p>
            </div>
            <div id="tabs-2">
                <p>
                <table class="form-table">
                    <tbody>

                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Overlay skin", 'meta-data-filter') ?></label></th>
                            <td>

                                <?php
                                $skins = array(
                                    'default' => esc_html__('Default', 'meta-data-filter'),
                                    'plainoverlay' => esc_html__('Plainoverlay', 'meta-data-filter'),
                                    'loading-balls' => esc_html__('Loading balls', 'meta-data-filter'),
                                    'loading-bars' => esc_html__('Loading bars', 'meta-data-filter'),
                                    'loading-bubbles' => esc_html__('Loading bubbles', 'meta-data-filter'),
                                    'loading-cubes' => esc_html__('Loading cubes', 'meta-data-filter'),
                                    'loading-cylon' => esc_html__('Loading cyclone', 'meta-data-filter'),
                                    'loading-spin' => esc_html__('Loading spin', 'meta-data-filter'),
                                    'loading-spinning-bubbles' => esc_html__('Loading spinning bubbles', 'meta-data-filter'),
                                    'loading-spokes' => esc_html__('Loading spokes', 'meta-data-filter'),
                                );
                                if (!isset($data['overlay_skin'])) {
                                    $data['overlay_skin'] = 'default';
                                }
                                $skin = $data['overlay_skin'];
                                ?>

                                <select name="meta_data_filter_settings[overlay_skin]" class="mdtf-w300">
                                    <?php foreach ($skins as $scheme => $title) : ?>
                                        <option value="<?php echo esc_attr($scheme); ?>" <?php if ($skin == $scheme): ?>selected="selected"<?php endif; ?>><?php echo esc_html($title); ?></option>
                                    <?php endforeach; ?>
                                </select>&nbsp;<br />

                                <p class="description"><?php esc_html_e("Overlay skin while data loading", 'meta-data-filter') ?></p>

                                <?php
                                if (!isset($data['overlay_skin_bg_img'])) {
                                    $data['overlay_skin_bg_img'] = '';
                                }
                                $overlay_skin_bg_img = $data['overlay_skin_bg_img'];
                                ?>
                                <div <?php if ($skin == 'default'): ?>style="display: none;"<?php endif; ?>>

                                    <h4 class="mdtf-mb5"><?php esc_html_e('Overlay image background', 'meta-data-filter') ?></h4>
                                    <input type="text" class="mdtf_overlay_skin_bg_img" name="meta_data_filter_settings[overlay_skin_bg_img]" value="<?php echo esc_url_raw($overlay_skin_bg_img) ?>" /><br />
                                    <i><?php esc_html_e('Example', 'meta-data-filter') ?>: <?php echo self::get_application_uri() ?>images/overlay_bg.png</i><br />

                                    <div <?php if ($skin != 'plainoverlay'): ?>style="display: none;"<?php endif; ?>>
                                        <br />

                                        <?php
                                        if (!isset($data['plainoverlay_color'])) {
                                            $data['plainoverlay_color'] = '';
                                        }
                                        $plainoverlay_color = $data['plainoverlay_color'];
                                        ?>

                                        <h4 class="mdtf-mb5"><?php esc_html_e('Plainoverlay color', 'meta-data-filter') ?></h4>
                                        <input type="text" name="meta_data_filter_settings[plainoverlay_color]" value="<?php echo esc_html($plainoverlay_color) ?>" class="mdtf-color-picker" >

                                    </div>

                                </div>


                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="mdf_for_premium_label"><label><?php esc_html_e("Loading text", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" readonly="" class="regular-text" value="">
                                <p class="description"><?php esc_html_e("Example: One Moment ...", 'meta-data-filter') ?></p>
                                <br />
                                <hr />

                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Default order by", 'meta-data-filter') ?></label></th>
                            <td>
                                <?php
                                if (!isset($data['default_order_by']) OR empty($data['default_order_by'])) {
                                    $data['default_order_by'] = self::$default_order_by;
                                }
                                ?>
                                <input type="text" class="regular-text" value="<?php echo esc_html($data['default_order_by']) ?>" name="meta_data_filter_settings[default_order_by]">
                                <?php
                                $default_orders = array(
                                    'DESC' => esc_html__("DESC", 'meta-data-filter'),
                                    'ASC' => esc_html__("ASC", 'meta-data-filter')
                                );
                                if (!isset($data['default_order']) OR empty($data['default_order'])) {
                                    $data['default_order'] = self::$default_order;
                                }
                                ?>
                                <select name="meta_data_filter_settings[default_order]">
                                    <?php foreach ($default_orders as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php if (@$data['default_order'] == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php printf(esc_html__("Example: %s,_price. Default order-by of your filtered posts.", 'meta-data-filter'), implode(',', MetaDataFilterCore::$allowed_order_by)) ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_43"><?php esc_html_e("Default sort panel", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>

                                        <?php
                                        $sort_panels_query = new WP_Query(array(
                                            'post_type' => MetaDataFilterCore::$slug_woo_sort,
                                            'post_status' => array('publish'),
                                            'orderby' => 'name',
                                            'order' => 'ASC'
                                        ));
//+++
                                        if (!isset($data['default_sort_panel'])) {
                                            $data['default_sort_panel'] = 0;
                                        }
                                        ?>

                                        <?php if ($sort_panels_query->have_posts()): ?>
                                            <select name="meta_data_filter_settings[default_sort_panel]">
                                                <?php while ($sort_panels_query->have_posts()) : ?>
                                                    <?php $sort_panels_query->the_post(); ?>
                                                    <option value="<?php the_ID() ?>" <?php if ($data['default_sort_panel'] == get_the_ID()): ?>selected="selected"<?php endif; ?>><?php the_title() ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                            <?php
                                            wp_reset_postdata();
                                            wp_reset_query();
                                            ?>
                                        <?php else: ?>
                                            <?php esc_html_e("No one sort panel created!", 'meta-data-filter') ?>
                                            <input type="hidden" name="meta_data_filter_settings[default_sort_panel]" value="0" />
                                        <?php endif; ?>
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Will be shown if the searching is not going by default if no panel id is set.", 'meta-data-filter') ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row" class="mdf_for_premium_label"><label><?php esc_html_e("Toggle open sign", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" readonly="" class="regular-text" value="+">
                                <p class="description"><?php esc_html_e("Toggle open sign on front widget while using toggles for sections.", 'meta-data-filter') ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row" class="mdf_for_premium_label"><label><?php esc_html_e("Toggle close sign", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" readonly="" class="regular-text" value="-">
                                <p class="description"><?php esc_html_e("Toggle close sign on front widget while using toggles for sections.", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        
                        
                        <tr valign="top">
                            <th scope="row"><label for="hide_search_button_shortcode"><?php esc_html_e("Hide [mdf_search_button] on mobile devices", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="hide_search_button_shortcode" type="checkbox" <?php if (isset($data['hide_search_button_shortcode']) AND $data['hide_search_button_shortcode'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[hide_search_button_shortcode]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Hide button of search button shortcode on mobile devices", 'meta-data-filter') ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="ignore_sticky_posts"><?php esc_html_e("Ignore sticky posts", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="ignore_sticky_posts" type="checkbox" <?php if (isset($data['ignore_sticky_posts']) AND $data['ignore_sticky_posts'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[ignore_sticky_posts]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Ignore sticky posts in search results", 'meta-data-filter') ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="show_tax_all_childs"><?php esc_html_e("Show terms childs", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="show_tax_all_childs" type="checkbox" <?php if (isset($data['show_tax_all_childs']) AND $data['show_tax_all_childs'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[show_tax_all_childs]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Show terms childs always in taxonomies shown as checkboxes", 'meta-data-filter') ?></p>
                            </td>
                        </tr>

                    </tbody>
                </table>
                </p>
            </div>
            <div id="tabs-3">
                <p>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Tooltip theme", 'meta-data-filter') ?></label></th>
                            <td>
                                <?php
                                $tooltip_themes = array(
                                    'default' => esc_html__("Default", 'meta-data-filter'),
                                    'light' => esc_html__("Light", 'meta-data-filter'),
                                    'noir' => esc_html__("Noir", 'meta-data-filter'),
                                    'punk' => esc_html__("Punk", 'meta-data-filter'),
                                    'shadow' => esc_html__("Shadow", 'meta-data-filter')
                                );
                                ?>
                                <select name="meta_data_filter_settings[tooltip_theme]">
                                    <?php foreach ($tooltip_themes as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php if ($data['tooltip_theme'] == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Tooltip icon image URL", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" placeholder="<?php esc_html_e("default icon", 'meta-data-filter') ?>" value="<?php echo esc_attr($data['tooltip_icon']) ?>" name="meta_data_filter_settings[tooltip_icon]">
                                <p class="description"><?php esc_html_e("Link to png icon for tooltip in widgets and shortcodes", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Tooltip max width", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" value="<?php echo esc_html(isset($data['tooltip_max_width'])?$data['tooltip_max_width']:''); ?>" name="meta_data_filter_settings[tooltip_max_width]">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Tab slideout icon image settings", 'meta-data-filter') ?></label></th>
                            <td>
                                <input type="text" class="regular-text" placeholder="<?php esc_html_e("default icon url", 'meta-data-filter') ?>" value="<?php echo esc_url_raw(isset($data['tab_slideout_icon'])?$data['tab_slideout_icon']:''); ?>" name="meta_data_filter_settings[tab_slideout_icon]"><br />
                                <input type="text" class="regular-text" placeholder="<?php esc_html_e("default icon width", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($data['tab_slideout_icon_w'])?$data['tab_slideout_icon_w']:''); ?>" name="meta_data_filter_settings[tab_slideout_icon_w]"><br />
                                <input type="text" class="regular-text" placeholder="<?php esc_html_e("default icon height", 'meta-data-filter') ?>" value="<?php echo esc_html(isset($data['tab_slideout_icon_h'])?$data['tab_slideout_icon_h']:''); ?>" name="meta_data_filter_settings[tab_slideout_icon_h]"><br />
                                <p class="description"><?php esc_html_e("Link and width/height to png icon for tab slideout shortcode", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("jQuery-ui calendar date format", 'meta-data-filter') ?></label></th>
                            <td>
                                <?php
                                $calendar_date_formats = array(
                                    'mm/dd/yy' => esc_html__("Default - mm/dd/yy", 'meta-data-filter'),
                                    'dd-mm-yy' => esc_html__("Europe - dd-mm-yy", 'meta-data-filter'),
                                    'yy-mm-dd' => esc_html__("ISO 8601 - yy-mm-dd", 'meta-data-filter'),
                                    'd M, y' => esc_html__("Short - d M, y", 'meta-data-filter'),
                                    'd MM, y' => esc_html__("Medium - d MM, y", 'meta-data-filter'),
                                    'DD, d MM, yy' => esc_html__("Full - DD, d MM, yy", 'meta-data-filter')
                                );
                                $calendar_date_format = (isset($data['calendar_date_format']) ? $data['calendar_date_format'] : 'mm/dd/yy');
                                ?>
                                <select name="meta_data_filter_settings[calendar_date_format]">
                                    <?php foreach ($calendar_date_formats as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php if ($calendar_date_format == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Use chosen js library for drop-downs in the search forms", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" <?php if (isset($data['use_chosen_js_w']) AND $data['use_chosen_js_w'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[use_chosen_js_w]" />
                                        <?php esc_html_e("for widgets", 'meta-data-filter') ?>
                                    </label>
                                </fieldset>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" <?php if (isset($data['use_chosen_js_s']) AND $data['use_chosen_js_s'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[use_chosen_js_s]" />
                                        <?php esc_html_e("for shortcodes", 'meta-data-filter') ?>
                                    </label>
                                </fieldset>
                                <img src="<?php echo MetaDataFilter::get_application_uri() ?>images/chosen_selects.png" alt="" /><br />
                                <p class="description"><?php esc_html_e("Not compatible with all wp themes. Uncheck the checkbox it is works bad.", 'meta-data-filter') ?></p>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="use_custom_scroll_bar"><?php esc_html_e("Use custom scroll js bar", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="use_custom_scroll_bar" type="checkbox" <?php if (isset($data['use_custom_scroll_bar']) AND $data['use_custom_scroll_bar'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[use_custom_scroll_bar]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Beautiful js scroll bars. Sometimes not compatible with some wordpress themes", 'meta-data-filter') ?></p>
                            </td>
                        </tr>

                        <tr valign="top" colspan="2">
                            <th scope="row"><label for="use_custom_icheck"><?php esc_html_e("Use icheck js library for checkboxes", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="use_custom_icheck" type="checkbox" <?php if (isset($data['use_custom_icheck']) AND $data['use_custom_icheck'] == 1) echo 'checked'; ?> value="1" name="meta_data_filter_settings[use_custom_icheck]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("JS CUSTOMIZED CHECKBOXES AND RADIO BUTTONS FOR JQUERY: http://fronteed.com/iCheck/", 'meta-data-filter') ?></p>
                            </td>
                            <td>
                                <?php esc_html_e("Skin", 'meta-data-filter') ?>:<br />
                                <?php
                                $skins = array(
                                    'flat' => array(
                                        'flat_aero',
                                        'flat_blue',
                                        'flat_flat',
                                        'flat_green',
                                        'flat_grey',
                                        'flat_orange',
                                        'flat_pink',
                                        'flat_purple',
                                        'flat_red',
                                        'flat_yellow'
                                    ),
                                    'line' => array(
                                        //'line_aero',
                                        'line_blue',
                                        'line_green',
                                        'line_grey',
                                        'line_line',
                                        'line_orange',
                                        'line_pink',
                                        'line_purple',
                                        'line_red',
                                        'line_yellow'
                                    ),
                                    'minimal' => array(
                                        'minimal_aero',
                                        'minimal_blue',
                                        'minimal_green',
                                        'minimal_grey',
                                        'minimal_minimal',
                                        'minimal_orange',
                                        'minimal_pink',
                                        'minimal_purple',
                                        'minimal_red',
                                        'minimal_yellow'
                                    ),
                                    'square' => array(
                                        'square_aero',
                                        'square_blue',
                                        'square_green',
                                        'square_grey',
                                        'square_orange',
                                        'square_pink',
                                        'square_purple',
                                        'square_red',
                                        'square_yellow',
                                        'square_square'
                                    )
                                );
                                $skin = (isset($data['icheck_skin']) ? $data['icheck_skin'] : 'flat_blue');
                                ?>
                                <select name="meta_data_filter_settings[icheck_skin]">
                                    <?php foreach ($skins as $key => $schemes) : ?>
                                        <optgroup label="<?php echo esc_html($key) ?>">
                                            <?php foreach ($schemes as $scheme) : ?>
                                                <option value="<?php echo esc_attr($scheme); ?>" <?php if ($skin == $scheme): ?>selected="selected"<?php endif; ?>><?php echo esc_html($scheme); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>&nbsp;
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Range slider skin", 'meta-data-filter') ?></label></th>
                            <td>

                                <?php
                                $skins = array(
                                    'skinNice' => 'skinNice',
                                    'skinFlat' => 'skinFlat',
                                    'skinSimple' => 'skinSimple'
                                );

                                $skin = (isset($data['ion_slider_skin']) ? $data['ion_slider_skin'] : 'skinNice');
                                ?>

                                <div class="select-wrap">
                                    <select name="meta_data_filter_settings[ion_slider_skin]" class="chosen_select">
                                        <?php foreach ($skins as $key => $value) : ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php if ($skin == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <p class="description">
                                    <?php esc_html_e('Ion-Range slider js lib skin for range-sliders of the plugin', 'meta-data-filter') ?>
                                </p>

                            </td>
                        </tr>

                    </tbody>
                </table>
                </p>
            </div>

            <?php if (class_exists('WooCommerce')): ?>
                <div id="tabs-4">
                    <p>
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="mdtf_label_41"><?php esc_html_e("Exclude 'out of stock' products from search", 'meta-data-filter') ?></label></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input id="mdtf_label_41" type="checkbox" <?php if (isset($data['exclude_out_stock_products'])) echo 'checked'; ?> value="1" name="meta_data_filter_settings[exclude_out_stock_products]" />
                                        </label>
                                    </fieldset>
                                    <p class="description"></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="mdtf_label_42"><?php esc_html_e("Try to make shop page AJAXED", 'meta-data-filter') ?></label></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input id="mdtf_label_42" type="checkbox" <?php if (isset($data['try_make_shop_page_ajaxed'])) echo 'checked'; ?> value="1" name="meta_data_filter_settings[try_make_shop_page_ajaxed]" />
                                        </label>
                                    </fieldset>
                                    <p class="description"><?php esc_html_e("Check it if you want to make ajax searching directly on woocommerce shop page. BUT! It is not possible for 100% because a lot of wordpress themes developers not use in the right way woocommerce hooks woocommerce_before_shop_loop AND woocommerce_after_shop_loop. Works well with native woocommerce themes as Canvas and Primashop for example!", 'meta-data-filter') ?></p>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                    </p>
                </div>
            <?php endif; ?>

            <div id="tabs-5">
                <p>
                <table class="form-table">
                    <tbody>

                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_51"><?php esc_html_e('Pagination Label', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = esc_html__('Pages:', 'meta-data-filter');
                                        if (isset($data['ajax_pagination']['title'])) {
                                            $var = $data['ajax_pagination']['title'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_51" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][title]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The text/HTML to display before the list of pages.', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_52"><?php esc_html_e('Previous Page', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = '&laquo;';
                                        if (isset($data['ajax_pagination']['previouspage'])) {
                                            $var = $data['ajax_pagination']['previouspage'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_52" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][previouspage]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The text/HTML to display for the previous page link.', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_53"><?php esc_html_e('Next Page', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = '&raquo;';
                                        if (isset($data['ajax_pagination']['nextpage'])) {
                                            $var = $data['ajax_pagination']['nextpage'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_53" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][nextpage]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The text/HTML to display for the next page link.', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_54"><?php esc_html_e('Before Markup', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = '<div class="navigation">';
                                        if (isset($data['ajax_pagination']['before'])) {
                                            $var = $data['ajax_pagination']['before'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_54" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][before]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The HTML markup to display before the pagination code.', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_55"><?php esc_html_e('After Markup', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = '</div>';
                                        if (isset($data['ajax_pagination']['after'])) {
                                            $var = $data['ajax_pagination']['after'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_55" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][after]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The HTML markup to display after the pagination code.', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_56"><?php esc_html_e('Page Range', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = 3;
                                        if (isset($data['ajax_pagination']['range']) AND!empty($data['ajax_pagination']['range'])) {
                                            $var = $data['ajax_pagination']['range'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_56" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][range]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The number of page links to show before and after the current page. Recommended value: 3', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_57"><?php esc_html_e('Page Anchors', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = 1;
                                        if (isset($data['ajax_pagination']['anchor']) AND!empty($data['ajax_pagination']['anchor'])) {
                                            $var = $data['ajax_pagination']['anchor'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_57" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][anchor]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The number of links to always show at beginning and end of pagination. Recommended value: 1', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>



                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_58"><?php esc_html_e('Page Gap', 'meta-data-filter'); ?>:</label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <?php
                                        $var = 3;
                                        if (isset($data['ajax_pagination']['gap']) AND!empty($data['ajax_pagination']['gap'])) {
                                            $var = $data['ajax_pagination']['gap'];
                                        }
                                        ?>
                                        <input type="text" class="regular-text" id="mdtf_label_58" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($var)))) ?>" name="meta_data_filter_settings[ajax_pagination][gap]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('The minimum number of pages in a gap before an ellipsis (...) is added. Recommended value: 3', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_59"><?php esc_html_e("Markup Display", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="mdtf_label_59" type="checkbox" <?php if (isset($data['ajax_pagination']['empty'])) echo 'checked'; ?> value="1" name="meta_data_filter_settings[ajax_pagination][empty]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e('Show "Before Markup" and "After Markup", even if the page list is empty?', 'meta-data-filter'); ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="mdtf_label_510"><?php esc_html_e("Pagination CSS File", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="mdtf_label_510" type="checkbox" <?php if (isset($data['ajax_pagination']['css'])) echo 'checked'; ?> value="1" name="meta_data_filter_settings[ajax_pagination][css]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php printf(esc_html__('Include the default stylesheet tw-pagination.css? Pagination will first look for tw-pagination.css in your theme directory (themes/%s).', 'meta-data-filter'), get_template()); ?></p>
                            </td>
                        </tr>


                    </tbody>
                </table>
                </p>
            </div>


            <?php
            if (class_exists('MDF_POSTS_MESSENGER')):

                $data['label_messenger'] = (isset($data['label_messenger']) ? $data['label_messenger'] : 'Posts Messenger');
                $data['subscr_count'] = (isset($data['subscr_count']) ? $data['subscr_count'] : 2);
                if (!isset($data['use_external_cron'])OR empty($data['use_external_cron'])) {
                    $data['use_external_cron'] = bin2hex(random_bytes(12));
                }
                $data['header_email_messenger'] = (isset($data['header_email_messenger']) ? $data['header_email_messenger'] : esc_html__("New Posts by your request", 'meta-data-filter'));
                $data['subject_email_messenger'] = (isset($data['subject_email_messenger']) ? $data['subject_email_messenger'] : esc_html__("New Posts", 'meta-data-filter'));
                $data['text_email_messenger'] = (isset($data['text_email_messenger']) ? $data['text_email_messenger'] : esc_html__("Dear [DISPLAY_NAME], we increased the range of our products. Number of new products: [PRODUCT_COUNT]", 'meta-data-filter'));
                $data['count_message'] = (isset($data['count_message']) ? $data['count_message'] : -1);
                $data['notes_for_customer_messenger'] = (isset($data['notes_for_customer_messenger']) ? $data['notes_for_customer_messenger'] : "");
                ?>
                <div id="tabs-6">
                    <p>
                    <table class="form-table">
                        <tbody>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Short Description", 'meta-data-filter') ?></label></th>
                                <td>
                                    <a href="https://wp-filter.com/extension/post-messenger/" class="button" target="_blank"><?php esc_html_e("Read How to use here", 'meta-data-filter') ?></a>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Label", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($data['label_messenger']) ?>" name="meta_data_filter_settings[label_messenger]">
                                    <p class="description"><?php esc_html_e("The text before subscription block", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("WordPress cron period", 'meta-data-filter') ?></label></th>
                                <td>
                                    <?php
                                    $wp_cron_period = "no";
                                    if (isset($data['wp_cron_period_messenger'])) {
                                        $wp_cron_period = $data['wp_cron_period_messenger'];
                                    }
                                    $cron_periods = array(
                                        'no' => esc_html__('No', 'meta-data-filter'),
                                        'hourly' => esc_html__('hourly', 'meta-data-filter'),
                                        'twicedaily' => esc_html__('twicedaily', 'meta-data-filter'),
                                        'daily' => esc_html__('daily', 'meta-data-filter'),
                                        'week' => esc_html__('weekly', 'meta-data-filter'),
                                        'twicemonthly' => esc_html__('twicemonthly', 'meta-data-filter'),
                                        'month' => esc_html__('monthly', 'meta-data-filter'),
                                        'min1' => esc_html__('min1', 'meta-data-filter')
                                    );
                                    ?>
                                    <select name="meta_data_filter_settings[wp_cron_period_messenger]">
                                        <?php foreach ($cron_periods as $key => $txt): ?>
                                            <option <?php selected($wp_cron_period, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Period of emailing to subscribed users. Set it to "No" if you going to use external cron. ', 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("External cron key (is recommended as flexible for timetable)", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_attr($data['use_external_cron']); ?>" name="meta_data_filter_settings[use_external_cron]">
                                    <p class="description"><?php esc_html_e('For external cron use the next link', 'meta-data-filter'); ?>: <i class="woof_cron_link" ><b><?php echo get_home_url() . "?mdf_pm_cron_key="; echo esc_attr($data['use_external_cron']); ?></b></i><br />
                                        <?php esc_html_e('To reset the key, just delete it here and save the plugin settings OR write by hands your own. Key should be min 16 symbols.', 'meta-data-filter'); ?> </p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Max subscriptions per user", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($data['subscr_count']) ?>" name="meta_data_filter_settings[subscr_count]">
                                    <p class="description"><?php esc_html_e("Maximum number of subscriptions for a single registered user. ", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Title of the email", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($data['header_email_messenger']) ?>" name="meta_data_filter_settings[header_email_messenger]">
                                    <p class="description"><?php esc_html_e("Text in the email header.", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Subject of the email", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($data['subject_email_messenger']) ?>" name="meta_data_filter_settings[subject_email_messenger]">
                                    <p class="description"><?php esc_html_e("Subject of the email. ", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Text of the email", 'meta-data-filter') ?></label></th>
                                <td>
                                    <textarea class="regular-text mdtf_text_email_messenger" name="meta_data_filter_settings[text_email_messenger]" ><?php echo esc_textarea($data['text_email_messenger']) ?></textarea>
                                    <p class="description"><?php esc_html_e("Text in the body of the email. You can use next variables: [DISPLAY_NAME], [USER_NICENAME], [PRODUCT_COUNT]. ", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Subscription time", 'meta-data-filter') ?></label></th>
                                <td>
                                    <?php
                                    $date_expire_period = "no";
                                    if (isset($data['date_expire_period_messenger'])) {
                                        $date_expire_period = $data['date_expire_period_messenger'];
                                    }
                                    $date_expire = array(
                                        'no' => esc_html__('No limit', 'meta-data-filter'),
                                        'week' => esc_html__('One week', 'meta-data-filter'),
                                        'twicemonthly' => esc_html__('Two weeks', 'meta-data-filter'),
                                        'month' => esc_html__('One month', 'meta-data-filter'),
                                        'twomonth' => esc_html__('Two months', 'meta-data-filter'),
                                            //'min1' => esc_html__('min1', 'meta-data-filter')
                                    );
                                    ?>
                                    <select name="meta_data_filter_settings[date_expire_period_messenger]">
                                        <?php foreach ($date_expire as $key => $txt): ?>
                                            <option <?php selected($date_expire_period, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('How long user will get emails after subscription. ', 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Emails count", 'meta-data-filter') ?></label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo esc_html($data['count_message']) ?>" name="meta_data_filter_settings[count_message]">
                                    <p class="description"><?php esc_html_e("Maximum number of emails per one subscribed user. -1 means no limit. ", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Priority of limitations", 'meta-data-filter') ?></label></th>
                                <td>
                                    <?php
                                    $priority_limit_messenger = "both";
                                    if (isset($data['priority_limit_messenger'])) {
                                        $priority_limit_messenger = $data['priority_limit_messenger'];
                                    }
                                    $priority_limit = array(
                                        'by_date' => esc_html__('By date', 'meta-data-filter'),
                                        'by_count' => esc_html__('By emails count', 'meta-data-filter'),
                                        'both' => esc_html__('Both', 'meta-data-filter'),
                                    );
                                    ?>
                                    <select name="meta_data_filter_settings[priority_limit_messenger]">
                                        <?php foreach ($priority_limit as $key => $txt): ?>
                                            <option <?php selected($priority_limit_messenger, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Which limitation has priority. Event after which user stop getting the emails. Both - means that any first event ("Subscription time" or "Emails count") of two ones, will reset user subscription.', 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label><?php esc_html_e("Notes for customer", 'meta-data-filter') ?></label></th>
                                <td>
                                    <textarea class="regular-text mdtf_notes_for_customer_messenger" name="meta_data_filter_settings[notes_for_customer_messenger]" ><?php echo esc_textarea($data['notes_for_customer_messenger']) ?></textarea>
                                    <p class="description"><?php esc_html_e("Any text notes for customer under subscription form.", 'meta-data-filter') ?></p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    </p>
                </div>
            <?php endif; ?>

            <?php //+++++++++++++++++STAT++++++++++++++++++++++++++++++++  ?>
            <?php if (class_exists('MDF_SEARCH_STAT')): ?>
                <div id="tabs-7">
                    <?php do_action('mdf_print_applications_tabs_content_stat'); ?>
                </div>
            <?php endif; ?>



            <div id="tabs-8">
                <p>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="gmap_js_include_pages"><?php esc_html_e("Include Google Map JS on the next pages", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="gmap_js_include_pages" type="text" class="regular-text" placeholder="Example: 75,134,96" value="<?php echo esc_html(isset($data['gmap_js_include_pages'])?$data['gmap_js_include_pages']:''); ?>" name="meta_data_filter_settings[gmap_js_include_pages]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Some themes has already included google maps js, so maybe you will not need this option. But if you are need this - you can include it on pages (ID) on which you are using map, not on all pages of your site! Set -1 if you want to include it on all pages of your site.", 'meta-data-filter') ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="gmap_user_api_key"><?php esc_html_e("Google Map  API key", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input id="gmap_user_api_key" type="text" class="regular-text" placeholder="API key" value="<?php echo esc_html(isset($data['gmap_user_api_key'])?$data['gmap_user_api_key']:''); ?>" name="meta_data_filter_settings[gmap_user_api_key]" />
                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("", 'meta-data-filter') ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="keep_search_data_in"><?php esc_html_e("Where to keep search data", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>

                                        <?php
                                        $keep_search_data_in = array(
                                            'transients' => 'transients',
                                            'session' => 'session',
                                        );
                                        ?>

                                        <?php
                                        if (!isset($data['keep_search_data_in']) OR empty($data['keep_search_data_in'])) {
                                            $data['keep_search_data_in'] = self::$where_keep_search_data;
                                        }
                                        ?>
                                        <select name="meta_data_filter_settings[keep_search_data_in]">
                                            <?php foreach ($keep_search_data_in as $key => $value) : ?>
                                                <option value="<?php echo $key; ?>" <?php if ($data['keep_search_data_in'] == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>


                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Better keep search data in sessions, but in some of reasons it sometimes not possible, so set it to transients. But transients make additional queries to data base! Set it to sessions when your search is working fine to exclude case when search doesn't work because data cannot be keeps in the session!", 'meta-data-filter') ?></p>
                            </td>
                        </tr>



                        <tr valign="top">
                            <th scope="row"><label for="cache_count_data"><?php esc_html_e("Cache dynamic recount number for each item in filter", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>

                                        <?php
                                        $cache_count_data = array(
                                            0 => esc_html__("No", 'meta-data-filter'),
                                            1 => esc_html__("Yes", 'meta-data-filter')
                                        );
                                        ?>

                                        <?php
                                        if (!isset($data['cache_count_data']) OR empty($data['cache_count_data'])) {
                                            $data['cache_count_data'] = 0;
                                        }
                                        ?>
                                        <select name="meta_data_filter_settings[cache_count_data]">
                                            <?php foreach ($cache_count_data as $key => $value) : ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php if ($data['cache_count_data'] == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>


                                        <?php if ($data['cache_count_data']): ?>

                                        &nbsp;<a href="#" class="button js_cache_count_data_clear"><?php esc_html_e("clear cache", 'meta-data-filter') ?></a>&nbsp;<span class="mdtf-green-b"></span>

                                            &nbsp;
                                            <?php
                                            $clean_period = 0;
                                            if (isset($data['cache_count_data_auto_clean'])) {
                                                $clean_period = $data['cache_count_data_auto_clean'];
                                            }
                                            $periods = array(
                                                0 => esc_html__("do not clean cache automatically", 'meta-data-filter'),
                                                'hourly' => esc_html__("clean cache automatically hourly", 'meta-data-filter'),
                                                'twicedaily' => esc_html__("clean cache automatically twicedaily", 'meta-data-filter'),
                                                'daily' => esc_html__("clean cache automatically daily", 'meta-data-filter'),
                                                'days2' => esc_html__("clean cache automatically each 2 days", 'meta-data-filter'),
                                                'days3' => esc_html__("clean cache automatically each 3 days", 'meta-data-filter'),
                                                'days4' => esc_html__("clean cache automatically each 4 days", 'meta-data-filter'),
                                                'days5' => esc_html__("clean cache automatically each 5 days", 'meta-data-filter'),
                                                'days6' => esc_html__("clean cache automatically each 6 days", 'meta-data-filter'),
                                                'days7' => esc_html__("clean cache automatically each 7 days", 'meta-data-filter'),
                                            );
                                            ?>
                                            <select name="meta_data_filter_settings[cache_count_data_auto_clean]">
                                                <?php foreach ($periods as $key => $txt): ?>
                                                    <option <?php selected($clean_period, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        <?php endif; ?>

                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Useful thing when you already started your site and use dynamic recount -> it make recount very fast! Of course if you added new posts which have to be in search results you have to clean this cache! RECOMMENDED to set it to any time-term from the list to avoid query slowing in future!", 'meta-data-filter') ?></p>


                                <?php
                                global $wpdb;

                                $charset_collate = '';
                                if (method_exists($wpdb, 'has_cap') AND $wpdb->has_cap('collation')) {
                                    if (!empty($wpdb->charset)) {
                                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                                    }
                                    if (!empty($wpdb->collate)) {
                                        $charset_collate .= " COLLATE $wpdb->collate";
                                    }
                                }
                                //***
                                $sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . MetaDataFilterCore::$mdf_query_cache_table . "` (
                                    `mkey` varchar(64) NOT NULL,
                                    `mvalue` text NOT NULL,
                                     KEY `mkey` (`mkey`)
                                  ){$charset_collate}";

                                if ($wpdb->query($sql) === false) {
                                    ?>
                                    <p class="description"><?php esc_html_e("MDTF cannot create the database table! Make sure that your mysql user has the CREATE privilege! Do it manually using your host panel&phpmyadmin!", 'meta-data-filter') ?></p>
                                    <code><?php echo esc_sql($sql); ?></code>
                                    <input type="hidden" name="meta_data_filter_settings[cache_count_data]" value="0" />
                                    <?php
                                    echo esc_html($wpdb->last_error);
                                }
                                ?>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="cache_terms_data"><?php esc_html_e("Cache terms", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <label>

                                        <?php
                                        $cache_terms_data = array(
                                            0 => esc_html__("No", 'meta-data-filter'),
                                            1 => esc_html__("Yes", 'meta-data-filter')
                                        );
                                        ?>

                                        <?php
                                        if (!isset($data['cache_terms_data']) OR empty($data['cache_terms_data'])) {
                                            $data['cache_terms_data'] = 0;
                                        }
                                        ?>
                                        <select name="meta_data_filter_settings[cache_terms_data]">
                                            <?php foreach ($cache_terms_data as $key => $value) : ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php if ($data['cache_terms_data'] == $key): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>


                                        <?php if ($data['cache_terms_data']): ?>

                                        &nbsp;<a href="#" class="button js_cache_terms_data_clear"><?php esc_html_e("clear cache", 'meta-data-filter') ?></a>&nbsp;<span class="mdtf-green-b"></span>

                                            &nbsp;
                                            <?php
                                            $clean_period = 0;
                                            if (isset($data['cache_terms_data_auto_clean'])) {
                                                $clean_period = $data['cache_terms_data_auto_clean'];
                                            }
                                            $periods = array(
                                                0 => esc_html__("do not clean cache automatically", 'meta-data-filter'),
                                                'hourly' => esc_html__("clean cache automatically hourly", 'meta-data-filter'),
                                                'twicedaily' => esc_html__("clean cache automatically twicedaily", 'meta-data-filter'),
                                                'daily' => esc_html__("clean cache automatically daily", 'meta-data-filter'),
                                                'days2' => esc_html__("clean cache automatically each 2 days", 'meta-data-filter'),
                                                'days3' => esc_html__("clean cache automatically each 3 days", 'meta-data-filter'),
                                                'days4' => esc_html__("clean cache automatically each 4 days", 'meta-data-filter'),
                                                'days5' => esc_html__("clean cache automatically each 5 days", 'meta-data-filter'),
                                                'days6' => esc_html__("clean cache automatically each 6 days", 'meta-data-filter'),
                                                'days7' => esc_html__("clean cache automatically each 7 days", 'meta-data-filter'),
                                            );
                                            ?>
                                            <select name="meta_data_filter_settings[cache_terms_data_auto_clean]">
                                                <?php foreach ($periods as $key => $txt): ?>
                                                    <option <?php selected($clean_period, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        <?php endif; ?>

                                    </label>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Useful thing when you already set your site IN THE PRODUCTION MODE - its getting terms for filter faster without big MySQL queries! If you actively adds new terms every day or week you can set cron period for cleaning. Another way set: 'not clean cache automatically'!", 'meta-data-filter') ?></p>

                            </td>
                        </tr>

                        <?php if (isset($_SERVER['SCRIPT_URI']) OR isset($_SERVER['REQUEST_URI'])): ?>
                            <tr valign="top">
                                <th scope="row"><label for="init_on_pages_only"><?php esc_html_e("Init plugin on the next site pages ~only~", 'meta-data-filter') ?></label></th>
                                <td>
                                    <fieldset>
                                        <textarea name="meta_data_filter_settings[init_on_pages_only]" id="init_on_pages_only"><?php echo esc_textarea(isset($data['init_on_pages_only'])?trim($data['init_on_pages_only']):'') ?></textarea>
                                    </fieldset>
                                    <p class="description"><?php esc_html_e("This option excludes initialization of the plugin on all pages of the site except links in the textarea. One row - one link! Example: http://woocommerce.wp-filter.com/ajaxed-search-7/ - slash in the end of the link should be!", 'meta-data-filter') ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>


                        <tr valign="top">
                            <th scope="row"><label for="custom_css_code"><?php esc_html_e("Custom CSS code", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <textarea name="meta_data_filter_settings[custom_css_code]" id="custom_css_code"><?php echo esc_textarea(isset($data['custom_css_code'])?stripcslashes($data['custom_css_code']):$data['custom_css_code']); ?></textarea>
                                </fieldset>
                                <p class="description"><?php esc_html_e("If you are need to customize something and you don't want to lose your changes after update", 'meta-data-filter') ?></p>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label for="js_after_ajax_done"><?php esc_html_e("JavaScript code after AJAX is done", 'meta-data-filter') ?></label></th>
                            <td>
                                <fieldset>
                                    <textarea name="meta_data_filter_settings[js_after_ajax_done]" id="js_after_ajax_done"><?php if (isset($data['js_after_ajax_done'])): ?><?php echo stripcslashes(esc_js(str_replace('"', "'", $data['js_after_ajax_done']))) ?><?php endif; ?> </textarea>
                                </fieldset>
                                <p class="description"><?php esc_html_e("Use it when you are need additional action after AJAX redraw your products in shop page or in page with shortcode!", 'meta-data-filter') ?></p>
                            </td>
                        </tr>


                    </tbody>
                </table>
                </p>
            </div>


            <div id="tabs-9">
                <p>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Demo sites", 'meta-data-filter') ?></label></th>
                            <td>

                                <ul class="mdtf-m6">
                                    <li>
                                        <a href="http://wp-filter.com/demo-sites/" target="_blank">All MDTF demo-sites with zip archives for free downloading</a>
                                    </li>

                                </ul>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Easy entry video tutorial", 'meta-data-filter') ?></label></th>
                            <td>

                                <iframe width="560" height="315" src="https://www.youtube.com/embed/z31-zvX8TfM" frameborder="0" allowfullscreen></iframe>

                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><label><?php esc_html_e("Recommended plugins", 'meta-data-filter') ?></label></th>
                            <td>

                                <ul class="mdtf-m6">                                   

                                    <li>
                                        <a href="https://wordpress.org/plugins/woocommerce-currency-switcher/" target="_blank" class="mdtf-red">WooCommerce Currency Switcher</a><br />
                                        <p class="description"><?php esc_html_e("WooCommerce Currency Switcher – is the plugin that allows you to switch to different currencies and get their rates converted in the real time!", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://bulk-editor.com/" target="_blank" class="mdtf-red">WOOBE - WooCommerce Bulk Editor Professional</a><br />
                                        <p class="description"><?php esc_html_e("WordPress plugin for managing and bulk edit WooCommerce Products data in the reliable and flexible way! Be professionals with managing data of your e-shop!", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/inpost-gallery/" target="_blank">InPost Gallery</a><br />
                                        <p class="description"><?php esc_html_e("Insert Gallery in post, page and custom post types just in two clicks", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/custom-post-type-ui/" target="_blank">Custom Post Type UI</a><br />
                                        <p class="description"><?php esc_html_e("This plugin provides an easy to use interface to create and administer custom post types and taxonomies in WordPress.", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/widget-logic/other_notes/" target="_blank">Widget Logic</a><br />
                                        <p class="description"><?php esc_html_e("Widget Logic lets you control on which pages widgets appear using", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/wp-super-cache/" target="_blank">WP Super Cache</a><br />
                                        <p class="description"><?php esc_html_e("Cache pages, allow to make a lot of search queries on your site without high load on your server!", 'meta-data-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/taxonomy-terms-order/" target="_blank">Category Order and Taxonomy Terms Order</a><br />
                                        <p class="description"><?php esc_html_e("Order Categories and all custom taxonomies terms (hierarchically) and child terms using a Drag and Drop Sortable javascript capability", 'meta-data-filter') ?></p>
                                    </li>



                                    <li>
                                        <a href="http://codecanyon.pluginus.net/item/popping-sidebars-and-widgets-for-wordpress/8688220" target="_blank">Popping Sidebars and Widgets for WordPress</a><br />
                                        <p class="description"><?php esc_html_e("Create popping custom responsive layouts with sidebars and widgets in just a few clicks. Choose from variety of overlays, positioning, page visibility, active period, open/close events, custom styling, custom sidebars and much more.", 'meta-data-filter') ?></p>
                                    </li>


                                    <li>
                                        <a href="https://wordpress.org/plugins/autoptimize/" target="_blank">Autoptimize</a><br />
                                        <p class="description"><?php esc_html_e("It concatenates all scripts and styles, minifies and compresses them, adds expires headers, caches them, and moves styles to the page head, and scripts to the footer", 'meta-data-filter') ?></p>
                                    </li>


                                    <li>
                                        <a href="https://wordpress.org/plugins/pretty-link/" target="_blank">Pretty Link Lite</a><br />
                                        <p class="description"><?php esc_html_e("Shrink, beautify, track, manage and share any URL on or off of your WordPress website. Create links that look how you want using your own domain name!", 'meta-data-filter') ?></p>
                                    </li>




                                    <li>
                                        <a href="https://wordpress.org/plugins/duplicator/" target="_blank">Duplicator</a><br />
                                        <p class="description"><?php esc_html_e("Duplicate, clone, backup, move and transfer an entire site from one location to another.", 'meta-data-filter') ?></p>
                                    </li>

                                </ul>

                            </td>
                        </tr>


                    </tbody>
                </table>
                </p>
            </div>

            <div class="mdtf-powered-by">
                <a href="https://pluginus.net/" target="_blank">Powered by PluginUs.NET</a>
            </div>


        </div>
        <p class="submit mdtf-p9"><input type="submit" value="<?php esc_html_e("Save Settings", 'meta-data-filter') ?>" class="button button-primary" name="meta_data_filter_settings_submit"></p>
    </form>


    <?php if (MetaDataFilter::$is_free): ?>
        <hr />
        <br />

        <h3>READ: <a href="https://wp-filter.com/difference-free-premium-versions-plugin/" target="_blank">Difference between FREE and PREMIUM versions of the plugin</a></h3>

        <table class="mdtf-w100p">
            <tbody>
                <tr>
                    <td class="mdtf-w25p">
                        <h3 class="mdtf-red">UPGRADE to Full version:</h3>
                        <a href="https://codecanyon.pluginus.net/item/wordpress-meta-data-taxonomies-filter/7002700" alt="Get the plugin premium version" target="_blank"><img src="<?php echo esc_url_raw(self::get_application_uri()) ?>images/mdtf_banner.jpg" alt="" class="mdtf-w100p" /></a>
                    </td>
                    <td class="mdtf-w25p">
                        <h3>WOOCS - WooCommerce Currency Switcher:</h3>
                        <a href="https://currency-switcher.com/" target="_blank"><img src="<?php echo esc_url_raw(self::get_application_uri()) ?>images/woocs.png" alt="WOOCS - WooCommerce Currency Switcher" class="mdtf-w100p" /></a>
                    </td>

                    <td class="mdtf-w25p">
                        <h3>WOOT - WooCommerce Products Table:</h3>
                        <a href="https://products-tables.com/" target="_blank"><img src="<?php echo esc_url_raw(self::get_application_uri()) ?>images/woot.png" alt="WOOT - WooCommerce Products Table" class="mdtf-w100p" /></a>
                    </td>

                    <td class="mdtf-w25p">
                        <h3>BEAR - WooCommerce Bulk Editor Professional:</h3>
                        <a href="https://bulk-editor.com/" target="_blank"><img src="<?php echo esc_url_raw(self::get_application_uri()) ?>images/bear.png" width="300" alt="BEAR - WooCommerce Bulk Editor Professional" class="mdtf-w100p" /></a>
                    </td>

                </tr>
            </tbody>
        </table>

    <?php endif; ?>

    <hr />

    <p>
    <h3><?php esc_html_e("Mass assign filter-category ID to any posts types", 'meta-data-filter') ?></h3>
    <form action="<?php echo wp_nonce_url(admin_url('edit.php?post_type=' . MetaDataFilterCore::$slug . '&page=mdf_settings'), "mdtf_assign" . MetaDataFilterCore::$slug) ?>" method="post">
        <table class="form-table">
            <tbody>

                <tr valign="top">
                    <th scope="row"><label><?php esc_html_e("Supported post types", 'meta-data-filter') ?></label></th>
                    <td>
                        <select name="mass_filter_slug">
                            <?php foreach (MetaDataFilterCore::get_post_types() as $post_type => $post_type_name) : ?>
                                <option value="<?php echo esc_attr($post_type_name) ?>"><?php echo esc_html($post_type_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e("Check post types to which filter ID should be assign. Enter right data, do not joke with it!", 'meta-data-filter') ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php esc_html_e("Enter filter-category ID", 'meta-data-filter') ?></label></th>
                    <td>
                        <input type="text" class="regular-text" value="" name="mass_filter_id">
                        <p class="description">
                            <img src="<?php echo esc_url_raw(MetaDataFilter::get_application_uri()) ?>images/mass_filter_id.png" alt="" /><br />
                        </p>
                    </td>
                </tr>


            </tbody>
        </table>


        <p class="submit"><input type="submit" value="<?php esc_html_e("Assign", 'meta-data-filter') ?>" class="button button-primary" name="meta_data_filter_assign_filter_id"></p>
    </form>

</p>

</div>

