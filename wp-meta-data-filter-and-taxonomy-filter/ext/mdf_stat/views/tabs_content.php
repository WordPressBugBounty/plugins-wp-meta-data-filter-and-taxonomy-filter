<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<p>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row"><label><?php esc_html_e('Notice:', 'meta-data-filter') ?></label></th>
            <td>
                <a href="https://wp-filter.com/extension/statistic/" class="button" target="_blank"><?php echo esc_html__('Read How to use here', 'meta-data-filter') ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <a href="javascript:void(0);" id="mdf_show_stat_options"  class="button"><?php esc_html_e('Show/Hide statistic options', 'meta-data-filter') ?></a>
            </th>
            <td>
                <p class="description mdtf-red">
                    <?php
                    $is_enabled = 0;
                    if (isset($data['stat_is_enabled'])) {
                        $is_enabled = $data['stat_is_enabled'];
                    }
                    $stat_activated_mode = array(
                        0 => esc_html__('Disabled', 'meta-data-filter'),
                        1 => esc_html__('Enabled', 'meta-data-filter')
                    );

                    if (!$is_enabled) {
                        esc_html_e('Statistics collection is not enabled. Click on the previous button to see activation drop-down and DataBase options.', 'meta-data-filter');
                    }
                    ?>
                </p>
            </td>
        </tr>

        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("Statistic", 'meta-data-filter') ?>:</label></th>
            <td>               

                <select name="meta_data_filter_settings[stat_is_enabled]">
                    <?php foreach ($stat_activated_mode as $key => $txt): ?>
                        <option <?php selected($is_enabled, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Should be enabled to start assembling.', 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("Server options for statistic", 'meta-data-filter') ?>:</label></th>
            <td>
                <?php
                if (!isset($data['server_options']) OR empty($data['server_options'])) {
                    $data['server_options'] = array(
                        'host' => '',
                        'host_db_name' => '',
                        'host_user' => '',
                        'host_pass' => '',
                    );
                }

                $server_options = $data['server_options'];
                if ($is_enabled AND (empty($server_options['host']) OR empty($server_options['host_user']) OR empty($server_options['host_db_name']) OR empty($server_options['host_pass']))) {
                    echo '<div class="error"><p class="description">' . sprintf(esc_html__('Statistic -> Statistic options -> "Stat server options" inputs should be filled in by right data, another way not possible to collect statistical data!', 'meta-data-filter')) . '</p></div>';
                }
                ?>


                <label class="mdtf-mb5ib"><?php esc_html_e('Host', 'meta-data-filter') ?></label>:
                <input type="text" class="regular-text" name="meta_data_filter_settings[server_options][host]" value="<?php echo esc_html($server_options['host']) ?>" /><br />
                <br />
                <label class="mdtf-mb5ib"><?php esc_html_e('User', 'meta-data-filter') ?></label>:
                <input type="text" class="regular-text" name="meta_data_filter_settings[server_options][host_user]" value="<?php echo esc_html($server_options['host_user']) ?>" /><br />
                <br />
                <label class="mdtf-mb5ib"><?php esc_html_e('DB Name', 'meta-data-filter') ?></label>:
                <input type="text" class="regular-text" name="meta_data_filter_settings[server_options][host_db_name]" value="<?php echo esc_html($server_options['host_db_name']) ?>" /><br />
                <br />
                <label class="mdtf-mb5ib"><?php esc_html_e('Password', 'meta-data-filter') ?></label>:
                <input type="text" class="regular-text" name="meta_data_filter_settings[server_options][host_pass]" value="<?php echo esc_html($server_options['host_pass']) ?>" /><br />
                <br/>
                <span id="mdf_stat_connection"  class="button"><?php esc_html_e('Check DB connection', 'meta-data-filter') ?></span>
                <p class="description"><?php esc_html_e('This data is very important for assembling statistics data, so please fill fields very responsibly. To collect statistical data uses a separate MySQL table.', 'meta-data-filter') ?>

                </p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("Collect Statistic for", 'meta-data-filter') ?>:</label></th>
            <td>
                <?php
                if (!isset($data['post_type_for_stat']) OR empty($data['post_type_for_stat']) OR!is_array($data['post_type_for_stat'])) {
                    $data['post_type_for_stat'] = array();
                }
                if (is_array($data['post_types']) AND!empty($data['post_types'])) {
                    ?>
                    <select multiple="" name="meta_data_filter_settings[post_type_for_stat][]"  class="chosen_select">
                        <?php foreach ($data['post_types'] as $post_t) { ?>
                            <option value="<?php echo esc_html($post_t) ?>" <?php if (in_array($post_t, $data['post_type_for_stat'])): ?>selected="selected"<?php endif; ?>><?php echo esc_html($post_t) ?></option>
                        <?php } ?>
                    </select><br />
                    <p class="description"><?php esc_html_e('Select taxonomies and metadata which you want to track for each post type.', 'meta-data-filter') ?></p>

                <?php } ?>
                <?php
                foreach ($data['post_type_for_stat'] as $post_type) {
                    ?>
                    <div class="mdf_stat_post_type">
                        <h4><?php
                            esc_html_e("Post type:", 'meta-data-filter');
                            echo esc_html($post_type)
                            ?> </h4>
                        <?php
                        $all_items = array();
                        $taxonomies = MDF_SEARCH_STAT::get_all_taxonomies($post_type);
                        if (!empty($taxonomies)) {
                            foreach ($taxonomies as $slug => $t) {
                                $all_items[urldecode($slug)] = $t->labels->name;
                            }
                        }

                        asort($all_items);
                        if (!isset($data['items_for_stat'][$post_type]['tax']) OR empty($data['items_for_stat'][$post_type]['tax'])) {
                            $data['items_for_stat'][$post_type]['tax'] = array();
                        }
                        if (!isset($data['items_for_stat'][$post_type]['meta']) OR empty($data['items_for_stat'][$post_type]['meta'])) {
                            $data['items_for_stat'][$post_type]['meta'] = "";
                        }
                        $items_for_stat = (array) $data['items_for_stat'][$post_type]['tax'];
                        if (!empty($all_items)) {
                            ?>
                            <label><?php esc_html_e("Taxonomies:", 'meta-data-filter'); ?></label><br>
                            <select multiple="" name="meta_data_filter_settings[items_for_stat][<?php echo esc_attr($post_type) ?>][tax][]" class="chosen_select">
                                <?php foreach ($all_items as $key => $value) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, $items_for_stat)): ?>selected="selected"<?php endif; ?>><?php echo esc_html($value); ?></option>
                                <?php endforeach; ?>
                            </select><br>
                        <?php } ?>
                        <label><?php esc_html_e("Meta data:", 'meta-data-filter'); ?></label><br>
                        <textarea class="regular-text mdf_items_for_stat" name="meta_data_filter_settings[items_for_stat][<?php echo esc_attr($post_type) ?>][meta]" ><?php echo esc_textarea($data['items_for_stat'][$post_type]['meta']) ?></textarea>
                        <p class="description"><?php esc_html_e("Insert meta keys separated by end of line (press enter button).", 'meta-data-filter') ?></p>


                    </div> <hr>
                    <?php
                }
                ?>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e('Max requests per unique user', 'meta-data-filter') ?></label></th>
            <?php
            if (!isset($data['user_max_requests']) OR empty($data['user_max_requests'])) {
                $data['user_max_requests'] = 10;
            }
            $user_max_requests = intval($data['user_max_requests']);
            if ($user_max_requests <= 0) {
                $user_max_requests = 10;
            }
            ?>
            <td>
                <input type="text" class="regular-text" value="<?php echo esc_attr($user_max_requests) ?>" name="meta_data_filter_settings[user_max_requests]">
                <p class="description"><?php esc_html_e("How many search requests will be catched and written down into the statistical mySQL table per 1 unique user before cron will assemble the data ", 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("Max deep of the search request", 'meta-data-filter') ?></label></th>
            <?php
            if (!isset($data['request_max_deep']) OR empty($data['request_max_deep'])) {
                $data['request_max_deep'] = 5;
            }
            $request_max_deep = intval($data['request_max_deep']);
            if ($request_max_deep <= 0) {
                $request_max_deep = 5;
            }
            ?>
            <td>
                <input type="text" class="regular-text" value="<?php echo esc_attr($request_max_deep) ?>" name="meta_data_filter_settings[request_max_deep]">
                <p class="description"><?php esc_html_e("How many taxonomies per one search request will be written down into the statistical mySQL table for 1 unique user. The excess data will be truncated! Number 5 is recommended. More depth - more space in the DataBase will be occupied by the data. ", 'meta-data-filter') ?></p>
            </td>
        </tr>

        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("How to assemble statistic", 'meta-data-filter') ?></label></th>
            <td>
                <?php
                $cron_systems = array(
                    0 => esc_html__('WordPress Cron', 'meta-data-filter'),
                    1 => esc_html__('External Cron', 'meta-data-filter')
                );

                if (!isset($data['cron_system'])) {
                    $data['cron_system'] = 0;
                }
                $cron_system = $data['cron_system'];
                ?>

                <select name="meta_data_filter_settings[cron_system]">
                    <?php foreach ($cron_systems as $key => $txt): ?>
                        <option <?php selected($cron_system, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Use WordPress Cron if your site has a lot of traffic, and external cron if the site traffic is not big. External cron is more predictable with time of execution, but additional knowledge how to set it correctly is required (External cron will be ready in the next version of the extension)', 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option cron_sys_1" style="visibility: <?php echo($cron_system == 1 ? 'visible' : 'hidden') ?>;">
            <th scope="row"><label><?php esc_html_e("Secret key for external cron", 'meta-data-filter') ?></label></th>
            <?php
            if (!isset($data['cron_secret_key']) OR empty($data['cron_secret_key'])) {
                $data['cron_secret_key'] = 'mdtf_stat_updating';
            }
            $cron_secret_key = sanitize_title($data['cron_secret_key']);
            ?>

            <td>
                <input type="text" class="regular-text" value="<?php echo esc_html($data['cron_secret_key']) ?>" name="meta_data_filter_settings[cron_secret_key]">
                <p class="description"><?php printf(esc_html__("Enter any random text in the field and use it in the external cron with link like: %s", 'meta-data-filter'), get_site_url() . '?mdf_stat_collection=' . $data['cron_secret_key']) ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option cron_sys_0"style="visibility: <?php echo($cron_system == 0 ? 'visible' : 'hidden') ?>;">
            <th scope="row">
                <label><?php esc_html_e("WordPress Cron period", 'meta-data-filter') ?></label></th>
            <td>
                <?php
                $wp_cron_periods = array(
                    'hourly' => esc_html__('hourly', 'meta-data-filter'),
                    'twicedaily' => esc_html__('twicedaily', 'meta-data-filter'),
                    'daily' => esc_html__('daily', 'meta-data-filter'),
                    'week' => esc_html__('weekly', 'meta-data-filter'),
                    'month' => esc_html__('monthly', 'meta-data-filter'),
                        //'min1' => esc_html__('min1', 'meta-data-filter')
                );

                if (!isset($data['wp_cron_period'])) {
                    $data['wp_cron_period'] = 'daily';
                }
                $wp_cron_period = $data['wp_cron_period'];
                ?>

                <select name="meta_data_filter_settings[wp_cron_period]">
                    <?php foreach ($wp_cron_periods as $key => $txt): ?>
                        <option <?php selected($wp_cron_period, $key) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($txt); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('12 hours recommended', 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" class="mdf_stat_option">
            <th scope="row"><label><?php esc_html_e("Max terms or taxonomies per graph", 'meta-data-filter') ?></label></th>
            <?php
            if (!isset($data['max_items_per_graph']) OR empty($data['max_items_per_graph'])) {
                $data['max_items_per_graph'] = 10;
            }
            $max_items_per_graph = intval($data['max_items_per_graph']);
            if ($max_items_per_graph <= 0) {
                $max_items_per_graph = 10;
            }
            ?>
            <td>
                <input type="text" class="regular-text" value="<?php echo esc_attr($max_items_per_graph) ?>" name="meta_data_filter_settings[max_items_per_graph]">
                <p class="description"><?php esc_html_e("How many taxonomies and terms to show on the graphs. Use no more than 10 to understand situation with statistical data", 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <?php global $wp_locale; ?>
        <tr valign="top" >
            <th scope="row"><label><?php esc_html_e("Select period:", 'meta-data-filter') ?></label></th>
            <td>
                <?php if (!empty($stat_min_date)): ?>
                <div class="mdtf-stat-collected"><?php printf(esc_html__('(Statistic collected from: %s %d)', 'meta-data-filter'), $wp_locale->get_month($stat_min_date[1]), $stat_min_date[0]) ?></div>
                <?php endif; ?>

                <input type="hidden" id="mdf_stat_calendar_from" value="0" />
                <input type="text" readonly="readonly" class="regular-text mdf_stat_calendar mdf_stat_calendar_from" placeholder="<?php esc_html_e('From', 'meta-data-filter') ?>" />
                &nbsp;
                <input type="hidden" id="mdf_stat_calendar_to" value="0" />
                <input type="text" readonly="readonly" class="regular-text mdf_stat_calendar mdf_stat_calendar_to" placeholder="<?php esc_html_e('To', 'meta-data-filter') ?>" /><br />

                <br />
                <p class="description"><?php esc_html_e("Select the time period for which you want to see statistical data", 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" >
            <th scope="row"><label><?php esc_html_e("Statistical parameters:", 'meta-data-filter') ?></label></th>
            <td>
                <?php
                if (!empty($data['post_type_for_stat'])) {

                    $default_type = $data['post_type_for_stat'][0];
                    ?>
                    <select id="mdf_stat_post_type">
                        <?php foreach ($data['post_type_for_stat'] as $post_type): ?>
                            <option value="<?php echo esc_attr($post_type) ?>"<?php ($post_type == $default_type) ? "seleckted" : ""; ?>><?php echo esc_html($post_type) ?></option>
                        <?php endforeach; ?>
                    </select><br />

                    <div id="mdf_stat_snipet_var">
                        <?php echo MDF_SEARCH_STAT::draw_tax_and_meta_var($default_type) ?>
                    </div>
                <?php } else {
                    ?> <p class="description"><?php esc_html_e("Choose post type in the statistics settings!", 'meta-data-filter') ?></p> <?php }
                ?>
                <a href="javascript: mdf_stat_calculate();" class="button button-primary button-large"><?php esc_html_e('Calculate Statistics', 'meta-data-filter') ?></a><br />
                <p class="description"><?php esc_html_e('Select taxonomy(-ies) and/or meta_data (combination) OR leave this field empty to see general data for all the most requested taxonomies', 'meta-data-filter') ?></p>
            </td>
        </tr>
        <!--+++++++++++++-->
        <tr valign="top" >
            <th scope="row"><label><?php esc_html_e("Graphics:", 'meta-data-filter') ?></label></th>
            <td>
                <div class="mdf-control mdf-upload-style-wrap mdtf-w100p">

                    <ul id="mdf_stat_get_monitor"></ul>

                    <div id="mdf_stat_charts_list">
                        <div id="chart_div_1"></div>
                        <div id="chart_div_1_set"></div>
                    </div>

                </div>


            </td>
        </tr>

        <!--+++++++++++++-->

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
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_stat_buffer}` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `hash` text COLLATE utf8_unicode_ci NOT NULL,
                                    `user_ip` text COLLATE utf8_unicode_ci NOT NULL,
                                    `post_type` text COLLATE utf8_unicode_ci NOT NULL,
                                    `type` text COLLATE utf8_unicode_ci NOT NULL,
                                    `filter_id` int(11) NOT NULL,
                                    `key_id` text COLLATE utf8_unicode_ci NOT NULL,
                                    `value` text COLLATE utf8_unicode_ci NOT NULL,
                                    `time` int(11) NOT NULL,
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=MyISAM {$charset_collate};";

        if ($wpdb->query($sql) === false) {
            ?>
        <p class="description"><?php esc_html_e("MDTF cannot create database table for statistic! Make sure that your mysql user has the CREATE privilege! Do it manually using your host panel&amp;phpmyadmin!", 'meta-data-filter') ?></p>
        <code><?php echo esc_html($sql); ?></code>
        <?php
        echo wp_kses_post(wp_unslash($wpdb->last_error));
    }

    //***
    $sql = "CREATE TABLE IF NOT EXISTS `{$table_stat_tmp}` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `user_ip` text COLLATE utf8_unicode_ci NOT NULL,
                                    `post_type` text COLLATE utf8_unicode_ci NOT NULL,
                                    `tax_data` text COLLATE utf8_unicode_ci NOT NULL,
                                    `meta_data` text COLLATE utf8_unicode_ci NOT NULL,
                                    `hash` text COLLATE utf8_unicode_ci NOT NULL,
                                    `time` int(11) NOT NULL,
                                    `is_collected` int(1) NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=MyISAM  {$charset_collate};";

    if ($wpdb->query($sql) === false) {
        ?>
        <p class="description"><?php esc_html_e("MDTF cannot create database table for statistic! Make sure that your mysql user has the CREATE privilege! Do it manually using your host panel&amp;phpmyadmin!", 'meta-data-filter') ?></p>
        <code><?php echo esc_html($sql); ?></code>
        <?php
        echo wp_kses_post(wp_unslash($wpdb->last_error));
    }
    ?>
</tbody>
</table>
</p>
