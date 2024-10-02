<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

include_once MetaDataFilterCore::get_application_path() . 'classes/cron.php';

final class MDF_SEARCH_STAT {

    private $table_stat_buffer = 'mdf_stat_buffer';
    private $table_stat_tmp = 'mdf_stat_tmp';
    public $type = 'application';
    public $folder_name = 'mdf_stat'; //should be defined!!
    //***
    public $is_enabled = false;
    public $cache_folder = '';
    public $post_type_for_stat = array();
    public $items_for_stat = array();
    public $user_max_requests = 10;
    public $request_max_deep = 5;
    private $is_expire = false;
    public $pdo = NULL; //for stat asembling
    public $cron_system = 0; //wp by default
    public $cron = NULL;
    public $wp_cron_period = 'daily';
    public $max_items_per_graph = 10;
    public $mdf_settings = array();

    public function __construct() {
        $mdf_settings = MetaDataFilter::get_settings();
        global $wpdb;
        $this->table_stat_buffer = $wpdb->prefix . $this->table_stat_buffer;
        $this->table_stat_tmp = $wpdb->prefix . $this->table_stat_tmp;

        //***
        if (isset($mdf_settings['stat_is_enabled'])) {
            $this->is_enabled = (bool) $mdf_settings['stat_is_enabled'];
            $this->set_start_statistic_period();
        }
        //***
        $cache_folder = '_woof_stat_cache';
        if (isset($mdf_settings['cache_folder']) AND!empty($mdf_settings['cache_folder'])) {
            $cache_folder = sanitize_title($mdf_settings['cache_folder']);
        }
        //***
        if (isset($mdf_settings['post_type_for_stat']) AND!empty($mdf_settings['post_type_for_stat'])) {
            $this->post_type_for_stat = (array) $mdf_settings['post_type_for_stat'];
        }
        //***
        if (isset($mdf_settings['items_for_stat']) AND!empty($mdf_settings['items_for_stat'])) {
            $this->items_for_stat = (array) $mdf_settings['items_for_stat'];
        }
        //***
        if (isset($mdf_settings['user_max_requests']) AND!empty($mdf_settings['user_max_requests'])) {
            $this->user_max_requests = intval($mdf_settings['user_max_requests']);
            if ($this->user_max_requests <= 0) {
                $this->user_max_requests = 10;
            }
        }
        //***
        if (isset($mdf_settings['request_max_deep']) AND!empty($mdf_settings['request_max_deep'])) {
            $this->request_max_deep = intval($mdf_settings['request_max_deep']);
            if ($this->request_max_deep <= 0) {
                $this->request_max_deep = 5;
            }
        }
        //***
        if (isset($mdf_settings['cron_system']) AND!empty($mdf_settings['cron_system'])) {
            $this->cron_system = intval($mdf_settings['cron_system']);
        }

        //***
        if (isset($mdf_settings['wp_cron_period']) AND!empty($mdf_settings['wp_cron_period'])) {
            $this->wp_cron_period = $mdf_settings['wp_cron_period'];
        }
        //***
        if (isset($mdf_settings['max_items_per_graph']) AND!empty($mdf_settings['max_items_per_graph'])) {
            $max_items_per_graph = (int) $mdf_settings['max_items_per_graph'];
            if ($max_items_per_graph > 0) {
                $this->max_items_per_graph = $max_items_per_graph;
            }
        }
        //***
        $this->cache_folder = WP_CONTENT_DIR . '/' . $cache_folder . '/';

        /// Get search data
        add_filter('meta_data_filter_args', array($this, 'mdf_get_request_data'));
        //***
        $this->init();
        //***

        if ($this->is_enabled) {
            $this->cron = new PN_WP_CRON_MDTF_MAIN('mdf_stat_cron');
            $this->init_pdo();
            //***

            if ($this->cron_system === 1) {
                $this->mdf_stat_wpcron_init(true);
                if (isset($_GET['mdf_stat_collection'])) {
                    $cron_secret_key = 'mdf_stat_updating';
                    if (isset($mdf_settings['cron_secret_key']) AND!empty($mdf_settings['cron_secret_key'])) {
                        $cron_secret_key = sanitize_title($mdf_settings['cron_secret_key']);
                    }
                    if ($_GET['mdf_stat_collection'] === $cron_secret_key) {
                        $this->assemble_stat();
                        die('mdf stat assembling done!');
                    }
                }
            } else {
                add_action('mdf_stat_wpcron', array($this, 'assemble_stat'), 10);
                $this->mdf_stat_wpcron_init();
            }
        }

        //***
        $this->get_stat_tables();

        //ajax 
        add_action('wp_ajax_mdf_get_operative_tables', array($this, 'get_operative_tables'));
        add_action('wp_ajax_mdf_get_stat_data', array($this, 'mdf_get_stat_data'));
        add_action('wp_ajax_mdf_get_top_terms', array($this, 'mdf_get_top_terms'));
        add_action('wp_ajax_mdf_stat_check_connection', array($this, 'mdf_stat_check_connection'));
        add_action('wp_ajax_draw_mdf_taxmeta_var', array(__CLASS__, 'ajax_draw_tax_and_meta_var'));
        add_action('wp_ajax_nopriv_draw_mdf_taxmeta_var', array(__CLASS__, 'ajax_draw_tax_and_meta_var'));
    }

    //ajax
    public function mdf_stat_check_connection() {
        $pdo_options = array();
        $pdo_options['host'] = sanitize_text_field($_REQUEST['mdf_stat_host']);
        $pdo_options['host_db_name'] = sanitize_text_field($_REQUEST['mdf_stat_name']);
        $pdo_options['host_user'] = sanitize_text_field($_REQUEST['mdf_stat_user']);
        $pdo_options['host_pass'] = sanitize_text_field($_REQUEST['mdf_stat_pswd']);

        try {
            $this->pdo = new PDO("mysql:host={$pdo_options['host']};dbname={$pdo_options['host_db_name']}", $pdo_options['host_user'], $pdo_options['host_pass']);
        } catch (PDOException $e) {
            die(esc_html__("Database not connected!    ERROR! ", 'meta-data-filter'));
        }
        die(esc_html__("Database successfully connected!!!", 'meta-data-filter'));
    }

    //ajax
    public function get_operative_tables() {
        if (current_user_can('create_users')) {
            $calendar_from = (int) $_REQUEST['calendar_from'];
            $calendar_from = mktime(0, 0, 0, date('n', $calendar_from), date('d', $calendar_from), date('y', $calendar_from));
            $calendar_to = (int) $_REQUEST['calendar_to'];
            $calendar_to = mktime(23, 59, 59, date('n', $calendar_to), date('d', $calendar_to), date('y', $calendar_to));

            //+++

            $tables = $this->get_stat_tables();
            $request_tables = array();
            $start_year = date('Y', $calendar_from);
            $start_month = date('n', $calendar_from);
            $finish_year = date('Y', $calendar_to);
            $finish_month = date('n', $calendar_to);
            //***
            $current_y = $start_year;
            $current_m = $start_month;
            while (true) {
                $t = $current_y . '_' . $current_m;
                if (in_array($t, $tables)) {
                    $request_tables[] = $t;
                }

                if ($current_y >= $finish_year AND $current_m >= $finish_month) {
                    break;
                }

                $current_m++;
                if ($current_m > 12) {
                    $current_m = 1;
                    $current_y++;
                }
            }

            die(json_encode($request_tables));
        }

        die('not permitted');
    }

    //ajax
    public function mdf_get_stat_data() {
        if (current_user_can('create_users')) {
            $table = sanitize_text_field($_REQUEST['table']);
            $calendar_from = (int) $_REQUEST['calendar_from'];
            $calendar_from = mktime(0, 0, 0, date('n', $calendar_from), date('d', $calendar_from), date('y', $calendar_from));
            $calendar_to = (int) $_REQUEST['calendar_to'];
            $calendar_to = mktime(23, 59, 59, date('n', $calendar_to), date('d', $calendar_to), date('y', $calendar_to));

            $tax_data = array();
            $meta_data = array();
            $curr_post_type = 'post';
            if (isset($_REQUEST['curr_post_type']) AND!empty($_REQUEST['curr_post_type'])) {
                $curr_post_type = sanitize_text_field($_REQUEST['curr_post_type']);
            }
            foreach ($this->get_all_taxonomies($curr_post_type) as $slug => $t) {
                //missing taxonomies which are not selected in the stat options
                if (!in_array(urldecode($slug), $this->items_for_stat[$curr_post_type]['tax'])) {
                    continue;
                }

                $tax_data[$slug] = $t->labels->name;
            }
            // check meta selected in the stat options
            $temp_meta = explode(PHP_EOL, (isset($this->items_for_stat['items_for_stat'][$curr_post_type]['meta'])) ? $this->items_for_stat['items_for_stat'][$curr_post_type]['meta'] : "");
            foreach ($temp_meta as $meta_key) {
                $meta_data[$meta_key] = $meta_key;
            }

            $search_template_temp = self::sanitize_array_r((array) $_REQUEST['request_snippets']);
            $search_template = array();
            if (!empty($search_template_temp['meta']) AND is_array($search_template_temp['meta'])) {
                $search_template = $search_template_temp['meta'];
            }
            if (!empty($search_template_temp['tax']) AND is_array($search_template_temp['tax'])) {
                $search_template = array_merge($search_template_temp['tax'], $search_template);
            }
            //+++
            if (!is_null($this->pdo)) {
                $sql = "SELECT type,filter_id,key_id,value FROM {$table} WHERE time>=:calendar_from AND time<=:calendar_to AND post_type=:curr_post_type";
                //+++
                if (!empty($search_template)) {
                    $sql = "SELECT hash,type,filter_id,key_id,value FROM {$table} WHERE time>=:calendar_from AND time<=:calendar_to AND post_type=:curr_post_type";
                    $sql_tale = " AND (";
                    foreach ($search_template as $k => $key) {
                        if ($k > 0) {
                            $sql_tale .= " OR ";
                        }

                        $sql_tale .= "key_id='" . trim($key) . "'";
                    }
                    $sql_tale .= ')';
                    $sql .= $sql_tale;
                }

                //+++
                $sth = $this->pdo->prepare($sql);
                $sth->bindParam(':calendar_from', $calendar_from, PDO::PARAM_INT);
                $sth->bindParam(':calendar_to', $calendar_to, PDO::PARAM_INT);
                $sth->bindParam(':curr_post_type', $curr_post_type, PDO::PARAM_STR);
                $sth->execute();

                $operative_data1 = $sth->fetchAll(PDO::FETCH_ASSOC);

                //if db table is empty
                if (empty($operative_data1)) {
                    die(json_encode(array(), JSON_UNESCAPED_UNICODE));
                }

                //+++

                if (empty($search_template)) {
                    //get top of terms             
                    $operative_data2 = array();
                    if (!empty($operative_data1)) {
                        foreach ($operative_data1 as $block) {
                            //+++
                            $t = $block['type'];
                            $f = $block['filter_id'];
                            $k = $block['key_id'];
                            $v = $block['value'];

                            //+++
                            if (!isset($operative_data2[$k])) {
                                $operative_data2[$k] = array();
                                $operative_data2[$k]['type'] = $t;
                                $operative_data2[$k]['filter_id'] = $f;
                            }

                            if (!isset($operative_data2[$k]['keys'][$v])) {
                                $operative_data2[$k]['keys'][$v] = 0;
                            }

                            $operative_data2[$k]['keys'][$v] += 1;
                        }

                        foreach ($operative_data2 as &$b) {
                            asort($b, SORT_NUMERIC);
                        }

                        die(json_encode($operative_data2, JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    $operative_data2 = array(); //grouping by hash key
                    if (!empty($operative_data1)) {
                        foreach ($operative_data1 as $key => $value) {
                            $tmp = $value;
                            unset($tmp['hash']);
                            $operative_data2[$value['hash']][] = $tmp;
                            unset($operative_data1[$key]); //remove it from memory                       
                        }
                        //***
                        $operative_data3 = array();
                        $search_template_count = count($search_template);

                        //+++

                        foreach ($operative_data2 as $key => $value) { {
                                $is = true;
                                $tax_should_be = array_flip($search_template);
                                foreach ($value as &$item) {
                                    if ($item['value'] == 0) {
                                        $is = false;
                                        break;
                                    }

                                    if ($item['type'] == 'tax') { //get name for tax
                                        $item['name'] = $tax_data[$item['key_id']];
                                        $t = get_term_by('id', $item['value'], $item['key_id']);
                                        $item['value_name'] = $t->name;
                                    } elseif ($item['type'] === 'meta') { //get name for meta
                                        $item['name'] = $this->get_only_meta_name($item['key_id'], $item['filter_id']);
                                        $item['value_name'] = $this->get_only_val($item['key_id'], $item['filter_id'], $item['value']);
                                        if ($item['value_name'] == 'mdf_label' OR $item['value_name'] == 'mdf_checkbox') {
                                            $item['value_name'] = $item['name'];
                                        }
                                    }

                                    $tax_should_be[$item['key_id']] = -1;
                                    unset($tax_should_be[trim($item['key_id'])]);
                                }

                                //+++
                                if (!empty($tax_should_be)) {
                                    $is = false;
                                }

                                //+++
                                if ($is) {
                                    if (!empty($value)) {
                                        $tmp = array();
                                        foreach ($value as $it) {
                                            $tmp[$it['key_id']] = $it;
                                        }
                                        //+++
                                        ksort($tmp, SORT_STRING);
                                        $tmp2 = array();
                                        foreach ($tmp as $it) {
                                            $tmp2[] = $it;
                                        }

                                        $operative_data3[] = $tmp2;
                                    }
                                }
                            }
                        }
                    }

                    $operative_data4 = array();
                    if (!empty($operative_data3)) {
                        foreach ($operative_data3 as $v) {
                            $k4 = "";
                            $vn4 = "";
                            $tn4 = "";
                            foreach ($v as $kk => $vv) {
                                if ($kk > 0) {
                                    $k4 .= "_";
                                    $tn4 .= "+";
                                    $vn4 .= " - ";
                                }

                                $k4 .= $vv['value'];
                                $tn4 .= $vv['name'];
                                $vn4 .= $vv['value_name'];
                            }
                            $operative_data4[$k4]['val'] += 1;
                            $operative_data4[$k4]['tname'] = $tn4;
                            $operative_data4[$k4]['vname'] = $vn4;
                        }
                    }
                    //***
                    usort($operative_data4, array($this, "cmp"));
                    die(json_encode($operative_data4, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        die('PHP PDO ext is not activated!');
    }

    //ajax
    public function mdf_get_top_terms() {
        if (current_user_can('create_users')) {
            $stat_data = self::sanitize_array_r($_REQUEST['mdf_stat_data']);
            $taxonomies = array();
            $tax_data = array();
            $meta_data = array();
            //  get post type for analiz
            $curr_post_type = 'post';
            if (isset($_REQUEST['curr_post_type']) AND!empty($_REQUEST['curr_post_type'])) {
                $curr_post_type = sanitize_text_field($_REQUEST['curr_post_type']);
            }
            //++++
            foreach ($this->get_all_taxonomies($curr_post_type) as $slug => $t) {
                if (!in_array(urldecode($slug), $this->items_for_stat[$curr_post_type]['tax'])) {
                    continue;
                }

                $tax_data[$slug] = $t->labels->name;
            }
            //***
            if (!empty($stat_data)) {
                $operative_data1 = array(); {
                    foreach ($stat_data as $i => $block) {
                        //+++
                        if (!empty($block)) {
                            foreach ($block as $key => $data) {
                                if (!empty($data['keys'])) {
                                    foreach ($data['keys'] as $value => $count) {
                                        if (!isset($operative_data1[$key])) {
                                            $operative_data1[$key] = array(
                                                'type' => $data['type'],
                                                'filter_id' => $data['filter_id']
                                            );
                                        }
                                        if (!isset($operative_data1[$key]['keys'][$value])) {
                                            $operative_data1[$key]['keys'][$value] = 0;
                                        }
                                        $operative_data1[$key]['keys'][$value] += $count;
                                    }
                                }
                            }
                        }
                    }
                }

                $block_tax_diff = array();
                $block_tax_each = array();

                foreach ($operative_data1 as $key => $item) {
                    $name = $key;
                    if ($item['type'] == 'meta') {
                        $name = $this->get_only_meta_name($key, $item['filter_id']);
                    } else {
                        $name = $tax_data[$key];
                    }

                    foreach ($item['keys'] as $value => $count) {
                        if (!isset($block_tax_diff[$name])) {
                            $block_tax_diff[$name] = 0;
                        }
                        $block_tax_diff[$name] += intval($count);
                    }
                }

                arsort($block_tax_diff, SORT_NUMERIC);

                //+++

                foreach ($operative_data1 as $key_id => $item) {
                    asort($block, SORT_NUMERIC);
                    $block = array_reverse($block, true);
                    $block = array_slice($block, 0, $this->max_items_per_graph, true);
                    if ($item['type'] == 'meta') {
                        $block_tax_each[$key_id]['tax_name'] = $this->get_only_meta_name($key_id, $item['filter_id']);
                    } else {
                        $block_tax_each[$key_id]['tax_name'] = $tax_data[$key_id];
                    }
                    $block_tax_each[$key_id]['terms'] = array();
                    if (!empty($item['keys'])) {
                        foreach ($item['keys'] as $term_id => $count) {
                            if ($item['type'] == 'tax') {
                                $t = get_term_by('id', $term_id, $key_id);
                                if (!empty($t->name)) {
                                    $block_tax_each[$key_id]['terms'][$t->name] = $count;
                                }
                            } elseif ($item['type'] == 'meta') {
                                $val_name = $this->get_only_val($key_id, $item['filter_id'], $term_id);
                                //Collects all meta fields in one graph (only for labels and checkboxes)
                                if ($val_name) {
                                    if ($val_name == 'mdf_label') {
                                        $tmp_val = $block_tax_each[$key_id]['tax_name'];
                                        if ($tmp_val) {
                                            unset($block_tax_each[$key_id]);
                                            $block_tax_each['mdf_label']['tax_name'] = 'Labels';
                                            $block_tax_each['mdf_label']['terms'][$tmp_val] = $count;
                                        }
                                    } elseif ($val_name == 'mdf_checkbox') {
                                        $tmp_val = $block_tax_each[$key_id]['tax_name'];
                                        if ($tmp_val) {
                                            unset($block_tax_each[$key_id]);
                                            $block_tax_each['mdf_checkbox']['tax_name'] = 'Checkboxes';
                                            $block_tax_each['mdf_checkbox']['terms'][$tmp_val] = $count;
                                        }
                                        //+++                                          
                                    } else {
                                        $block_tax_each[$key_id]['terms'][$val_name] = $count;
                                    }
                                }
                            }
                        }
                    }
                }

                die(json_encode(array($block_tax_diff, $block_tax_each), JSON_UNESCAPED_UNICODE));
            }
        }
    }

    //writing all search request from the customers
    public function mdf_get_request_data($data) {
        $meta_data = array();
        $tax_data = array();
        global $wpdb;
        if (!$this->is_enabled) {
            return $data;
        }
        if (!in_array($data["post_type"], $this->post_type_for_stat)) {
            return $data;
        }

        $meta_data = $this->render_meta_query($data['meta_query'], $data["post_type"]);
        $tax_data = $this->render_taxonomy_query($data['tax_query'], $data["post_type"]);

        $user_ip = $this->get_the_user_ip();

        if ((!empty($meta_data['meta_data']) OR!empty($tax_data) ) AND ( $this->get_user_requests_count($user_ip) < $this->user_max_requests)) {

            //lets control here max deep
            if ((count($meta_data['meta_data']) + count($tax_data) ) < $this->request_max_deep) {
                $new_request = array();
                $tmp_data = array();
                //***
                $hash = md5(json_encode($meta_data['meta_data']) . json_encode($tax_data) . $user_ip . date('d-m-Y'));

                //lets check for the same request from the same user
                $data_sql = array(
                    array(
                        'val' => $user_ip,
                        'type' => 'string',
                    ),
                    array(
                        'val' => $hash,
                        'type' => 'string',
                    ),
                );
                $the_same_hash = $wpdb->get_var(MDTF_HELPER::mdf_prepare("SELECT hash FROM $this->table_stat_tmp WHERE user_ip = %s AND hash = %s", $data_sql));
                //***
                if (empty($the_same_hash)) {
                    $tax_data = json_encode($tax_data, JSON_UNESCAPED_UNICODE);
                    $meta_data = json_encode($meta_data, JSON_UNESCAPED_UNICODE);
                    $time = time();
                    $data_sql = array(
                        array(
                            'val' => $user_ip,
                            'type' => 'string',
                        ),
                        array(
                            'val' => $data["post_type"],
                            'type' => 'string',
                        ),
                        array(
                            'val' => $tax_data,
                            'type' => 'string',
                        ),
                        array(
                            'val' => $meta_data,
                            'type' => 'string',
                        ),
                        array(
                            'val' => $hash,
                            'type' => 'string',
                        ),
                        array(
                            'val' => $time,
                            'type' => 'int',
                        ),
                    );

                    $insert = MDTF_HELPER::mdf_prepare("(%s,%s, %s, %s, %s, %d)", $data_sql);
                    $wpdb->query("INSERT INTO {$this->table_stat_tmp} (user_ip,post_type,tax_data,meta_data,hash,time) VALUES " . $insert);
                }
            }
        }

        return $data;
    }

    public function get_ext_path() {
        return plugin_dir_path(__FILE__);
    }

    public function get_ext_link() {
        return plugin_dir_url(__FILE__);
    }

    public function init() {
        // show stat settings
        wp_enqueue_style('mdf_search_stat', $this->get_ext_link() . 'css/stat.css');
        add_action('mdf_print_applications_tabs_content_stat', array($this, 'mdf_print_applications_tabs_content'), 10, 1);
    }

    public function mdf_print_applications_tabs_content() {

        //woof_stat_calendar_date_format, woof_stat_week_first_day
        wp_enqueue_script('mdf_google_charts', 'https://www.gstatic.com/charts/loader.js');
        wp_enqueue_script('jquery-ui-core');
        $wp_scripts = wp_scripts();
        //wp_enqueue_style('plugin_name-admin-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('plugin_name-admin-ui-css', MetaDataFilterCore::get_application_uri() . '/css/jquery-ui-themes/themes/smoothness/jquery-ui.css');
		//***
        $data = array();
        $data['stat_min_date'] = $this->get_stat_min_date_db();

        $data['is_expire'] = $this->is_expire;
        //***
        wp_enqueue_script('chosen-drop-down', MetaDataFilterCore::get_application_uri() . 'js/chosen/chosen.jquery.min.js', array('jquery'));
        wp_enqueue_style('chosen-drop-down', MetaDataFilterCore::get_application_uri() . 'js/chosen/chosen.min.css');
        wp_register_script('mdf_stat', $this->get_ext_link() . 'js/admin.js');
        $localize_script = array(
            'calendar_date_format' => 'DD, d MM, yy',
            'week_first_day' => get_option('start_of_week'),
            'max_items_per_graph' => $this->max_items_per_graph,
            'mdf_stat_leave_empty' => esc_html__('leave it empty to see all terms', 'meta-data-filter'),
            'mdf_stat_sel_date_range' => esc_html__('Select date range for statistic!', 'meta-data-filter'),
            'mdf_stat_calc' => esc_html__('Statistic calculation ...', 'meta-data-filter'),
            'mdf_stat_get_oper_tbls' => esc_html__('getting of operative tables ...', 'meta-data-filter'),
            'mdf_stat_oper_tbls_prep' => esc_html__('operative tables are prepared', 'meta-data-filter'),
            'mdf_stat_getting_dftbls' => esc_html__('getting the data from the table', 'meta-data-filter'),
            'mdf_stat_done' => esc_html__('done!', 'meta-data-filter'),
            'mdf_stat_no_data' => esc_html__('No data for the selected time period!', 'meta-data-filter'),
        );

        if (isset($data['stat_min_date'][0])) {
            $localize_script['min_year'] = $data['stat_min_date'][0];
            $localize_script['min_month'] = $data['stat_min_date'][1];
        } else {
            $localize_script['min_year'] = date('Y');
            $localize_script['min_month'] = date('n');
        }

        wp_localize_script('mdf_stat', 'mdf_stat_vars', $localize_script);
        wp_enqueue_script('mdf_stat', array('jquery', 'jquery-ui-core'));


        //***
        if (!extension_loaded('pdo_mysql')) {
            echo '<div class="error"><p class="description">' . sprintf(esc_html__('PHP extension PDO_MYSQL is not enabled on your server, not possible to collect statistic data! Contact your hosting support to about enabling PDO_MYSQL.', 'meta-data-filter')) . '</p></div>';
        }

        $min_memory_mb = 268435456;
        $memory = $this->let_to_num(WP_MEMORY_LIMIT);
        if (function_exists('memory_get_usage')) {
            $system_memory = $this->let_to_num(@ini_get('memory_limit'));
            $memory = max($memory, $system_memory);
        }

        if (version_compare($memory, $min_memory_mb, '<')) {
            echo '<div class="error"><p class="description">' . sprintf(esc_html__('Very recommend for the statistic not less than %s of the memory to avoid malfunctionality. Now is on the site %s', 'meta-data-filter'), size_format($min_memory_mb), size_format($memory)) . '</p></div>';
        }
        //***

        $data['table_stat_buffer'] = $this->table_stat_buffer;
        $data['table_stat_tmp'] = $this->table_stat_tmp;
        $data['folder_name'] = $this->folder_name;
        $data['data'] = MetaDataFilter::get_settings();

        MetaDataFilter::render_html_e($this->get_ext_path() . 'views/tabs_content.php', $data);
    }

    public function init_pdo() {
        $settings_t = MetaDataFilter::get_settings();
        if ($settings_t['server_options']) {
            if (extension_loaded('pdo_mysql')) {
                $pdo_options = $settings_t['server_options'];

                if (!empty($pdo_options['host']) AND!empty($pdo_options['host_db_name']) AND!empty($pdo_options['host_user'])) {
                    try {
                        $this->pdo = new PDO("mysql:host={$pdo_options['host']};dbname={$pdo_options['host_db_name']}", $pdo_options['host_user'], $pdo_options['host_pass']);
                    } catch (PDOException $e) {
                        echo '<div class="error"><p class="description">' . sprintf(esc_html__('Wrong data for "Server options for statistic stock" options. Please fill right data AND click "Check DB connection" button and if all right Save the plugin options.', 'meta-data-filter')) . '</p></div>';
                    }
                }
            }
        }
    }

    public function mdf_stat_wpcron_init($reset = false) {
        $hook = 'mdf_stat_wpcron';

        if ($reset) {
            $this->cron->remove($hook);
            return;
        }

        if ($this->cron_system === 0) {//wp cron
            if (!$this->cron->is_attached($hook, $this->get_mdf_cron_schedules($this->wp_cron_period))) {
                $this->cron->attach($hook, time(), $this->get_mdf_cron_schedules($this->wp_cron_period));
            }
            $this->cron->process();
        }
    }

    //stat assembling by cron
    public function assemble_stat() {
        if (!$this->is_enabled) {
            return;
        }
        //***
        global $wpdb;
        $terms = array();
        $step_num = 0;
        $step = 100;
        $data_sql = array(
            array(
                'val' => 0,
                'type' => 'int',
            ),
        );
        $count = $wpdb->get_var(MDTF_HELPER::mdf_prepare("SELECT COUNT(*)
			FROM {$this->table_stat_tmp} WHERE is_collected = %d
			ORDER BY user_ip", $data_sql));

        while (true) {
            $next = $step * ($step_num + 1);
            $data_sql = array(
                array(
                    'val' => 0,
                    'type' => 'int',
                ),
                array(
                    'val' => $step_num,
                    'type' => 'int',
                ),
                array(
                    'val' => $next,
                    'type' => 'int',
                ),
            );
            $res = $wpdb->get_results(MDTF_HELPER::mdf_prepare("SELECT *
			FROM {$this->table_stat_tmp} WHERE is_collected = %d
			LIMIT %d,%d", $data_sql), ARRAY_A);


            if (!empty($res)) {
                foreach ($res as $row) {
                    $meta_data = json_decode($row['meta_data'], true);
                    $tax_data = json_decode($row['tax_data'], true);
                    $filter_id = "-1";
                    if (!empty($tax_data)) {
                        $type = 'tax';
                        foreach ($tax_data as $taxonomy) {
                            $data_sql = array(
                                array(
                                    'val' => $row['hash'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['user_ip'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['post_type'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $type,
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => -1,
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $taxonomy['taxonomy'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $taxonomy['id_term'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['time'],
                                    'type' => 'int',
                                ),
                            );
                            $insert = MDTF_HELPER::mdf_prepare("(%s, %s,%s, %s, %s, %s, %s, %d)", $data_sql);
                            $wpdb->query("INSERT INTO {$this->table_stat_buffer} (hash,user_ip,post_type,type,filter_id,key_id,value,time) VALUES " . $insert);
                        }
                    }

                    if (!empty($meta_data)) {
                        $type = 'meta';
                        if (isset($meta_data['mdf_filter_id'])AND!empty($meta_data['mdf_filter_id'])) {
                            $filter_id = $meta_data['mdf_filter_id'];
                        }
                        foreach ($meta_data['meta_data'] as $meta) {
                            $value = "";
                            if (empty($meta['value'])) {
                                continue;
                            }

                            if (is_array($meta['value'])) {
                                $value = implode('^', $meta['value']);
                            } else {
                                $value = $meta['value'];
                            }
                            $data_sql = array(
                                array(
                                    'val' => $row['hash'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['user_ip'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['post_type'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $type,
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $filter_id,
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $meta['key'],
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $value,
                                    'type' => 'string',
                                ),
                                array(
                                    'val' => $row['time'],
                                    'type' => 'int',
                                ),
                            );

                            $insert = MDTF_HELPER::mdf_prepare("(%s, %s,%s, %s, %s, %s, %s, %d)", $data_sql);
                            $wpdb->query("INSERT INTO {$this->table_stat_buffer} (hash,user_ip,post_type,type,filter_id,key_id,value,time) VALUES " . $insert);
                        }
                    }
                    $data_sql = array(
                        array(
                            'val' => 1,
                            'type' => 'int',
                        ),
                        array(
                            'val' => $row['hash'],
                            'type' => 'string',
                        ),
                    );
                    $wpdb->query(MDTF_HELPER::mdf_prepare("UPDATE {$this->table_stat_tmp} SET is_collected = %d WHERE hash = %s", $data_sql));
                }
            }


//***
            if ($next > $count) {
                break;
            }
        }
//***
        $data_sql = array(
            array(
                'val' => 1,
                'type' => 'int',
            ),
        );
        $wpdb->query(MDTF_HELPER::mdf_prepare("DELETE FROM {$this->table_stat_tmp} WHERE is_collected = %d", $data_sql));
        if ($this->place_statdata_into_db()) {//placing data into dedicated DB
            //remove data from woof_stat
            $wpdb->query("TRUNCATE TABLE {$this->table_stat_buffer}");
        }
//***

        return false;
    }

    public function place_statdata_into_db() {
        if (!$this->is_enabled) {
            return;
        }

        //***

        global $wpdb;
        try {
            //get all distinct hash keys to get data blocks. 1 block == 1 user search request
            $res = $wpdb->get_results("SELECT DISTINCT hash FROM {$this->table_stat_buffer}", ARRAY_N);

            $hash_array = array();
            if (!empty($res)) {
                foreach ($res as $value) {
                    $hash_array[] = $value[0];
                }
                //***
                foreach ($hash_array as $hash) {
                    $data_sql = array(
                        array(
                            'val' => $hash,
                            'type' => 'string',
                        ),
                    );

                    $res = $wpdb->get_results(MDTF_HELPER::mdf_prepare("SELECT * FROM {$this->table_stat_buffer} WHERE hash = %s", $data_sql), ARRAY_A);

                    if (!empty($res)) {
                        $time = $res[0]['time'];

                        //PDO here

                        if (!is_null($this->pdo)) {
                            $table = date('Y', $time) . '_' . date('n', $time);

                            $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `hash` text COLLATE utf8_unicode_ci NOT NULL,
                            `user_ip` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'user IP',
                            `post_type` text COLLATE utf8_unicode_ci NOT NULL,
                            `type` text COLLATE utf8_unicode_ci NOT NULL,
                            `filter_id` int(11) NOT NULL,
                            `key_id` text COLLATE utf8_unicode_ci NOT NULL,
                            `value` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'value',
                            `time` int(11) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
                          ";
                            $stmt = $this->pdo->prepare($sql);
                            $stmt->execute();

                            //**
                            $sql = "INSERT INTO {$table}(
                                            hash,
                                            user_ip,
                                            post_type,
                                            type,
                                            filter_id,
                                            key_id,
                                            value,
                                            time) VALUES (
                                            :hash,
                                            :user_ip,
                                            :post_type,
                                            :type,
                                            :filter_id,
                                            :key_id,
                                            :value,
                                            :time)";

                            foreach ($res as $row) {

                                $stmt = $this->pdo->prepare($sql);
                                $stmt->bindParam(':hash', $row['hash'], PDO::PARAM_STR);
                                $stmt->bindParam(':user_ip', $row['user_ip'], PDO::PARAM_STR);
                                $stmt->bindParam(':post_type', $row['post_type'], PDO::PARAM_STR);
                                $stmt->bindParam(':type', $row['type'], PDO::PARAM_STR);
                                $stmt->bindParam(':filter_id', $row['filter_id'], PDO::PARAM_STR);
                                $stmt->bindParam(':key_id', $row['key_id'], PDO::PARAM_STR);
                                $stmt->bindParam(':value', $row['value'], PDO::PARAM_STR);
                                $stmt->bindParam(':time', $row['time'], PDO::PARAM_INT);

                                $stmt->execute();
                            }
                        }
                    }
                }
            }

            return true;
        } catch (Exception $ex) {

            return false;
        }

        return true;
    }

    public function get_stat_tables() {
        if ($this->pdo) {
            $sth = $this->pdo->prepare("SHOW TABLES");
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_COLUMN);
        }

        return array();
    }

    public function get_stat_min_date_db() {
        $res = get_option('mdf_stat_start_data', 0);
        if (!$res) {
            $res = array();
            $tables = $this->get_stat_tables();
            natsort($tables);
            $tables = array_values($tables);
            if (!empty($tables)) {
                $res = explode('_', $tables[0]);
                update_option('mdf_stat_start_data', $res);
            }
        }

        return $res;
    }

    //additional functions+++++++++++++++++++++++++++++++
    private function cmp($a, $b) {
        return $a["val"] < $b["val"];
    }

    public static function ajax_draw_tax_and_meta_var() {
        if (isset($_POST['mdf_stat_post_type'])) {
            $post_type = MetaDataFilter::escape($_POST['mdf_stat_post_type']);

            die(self::draw_tax_and_meta_var($post_type));
        }
        die("No data!!! ");
    }

    //for $this->user_max_requests
    private function get_user_requests_count($user_ip) {
        global $wpdb;
        $data_sql = array(
            array(
                'val' => $user_ip,
                'type' => 'string',
            ),
        );
        return (int) $wpdb->get_var(MDTF_HELPER::mdf_prepare("SELECT COUNT(*) as count FROM $this->table_stat_tmp WHERE user_ip = %s", $data_sql));
    }

    public static function get_all_taxonomies($post_type) {
        $taxonomies = array();

        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if ($post_type == 'product') {
            unset($taxonomies['product_shipping_class']);
            unset($taxonomies['product_type']);
        }
        return $taxonomies;
    }

    public static function draw_tax_and_meta_var($post_type) {

        $data = MetaDataFilter::get_settings();
        $arg['all_tax'] = self::get_all_taxonomies($post_type);
        $arg['tax'] = (isset($data['items_for_stat'][$post_type]['tax'])) ? $data['items_for_stat'][$post_type]['tax'] : array();
        $arg['meta'] = explode(PHP_EOL, (isset($data['items_for_stat'][$post_type]['meta'])) ? $data['items_for_stat'][$post_type]['meta'] : "");

        $pagepath = MetaDataFilterCore::get_application_path() . 'ext/mdf_stat/views' . DIRECTORY_SEPARATOR . 'draw_tax_and_meta_var.php';
        return MetaDataFilter::render_html($pagepath, $arg);
    }

    public function get_the_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//*** check ip from share internet
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//*** to check ip is pass from proxy
            $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        return $ip;
    }

    //Preparing a meta_query for writing to a table
    public function render_meta_query($meta_query = array(), $post_type = '') {
        $parsed_meta = array(
            'mdf_filter_id' => -1,
            'meta_data' => array()
        );
        $use_meta = explode(PHP_EOL, (isset($this->items_for_stat[$post_type]['meta'])) ? $this->items_for_stat[$post_type]['meta'] : "");
        $use_meta = array_map('trim', $use_meta);
        foreach ($meta_query as $meta_item) {
            if (!isset($meta_item['key'])) {
                continue;
            }
            if ($meta_item['key'] == 'meta_data_filter_cat') {
                $parsed_meta['mdf_filter_id'] = $meta_item['value'];
                continue;
            }
            if (stripos($meta_item['key'], 'medafi_') !== false AND in_array($meta_item['key'], $use_meta)) {
                $parsed_meta['meta_data'][] = $meta_item;
            }
        }
        return $parsed_meta;
    }

    // get  name for meta ( select from MDF category )
    public function get_only_meta_name($key_id, $filter_id) {
        foreach (MetaDataFilterPage::get_fiters_posts($filter_id) as $id) {
            foreach (MetaDataFilterPage::get_html_items($id) as $key => $val) {
                if ($key == trim($key_id) AND!empty($val['name'])) {
                    return $val['name'];
                } elseif ($key . '_from' == $key_id OR $key . '_to' == $key_id) {
                    return $val['name'];
                }
            }
        }
        return $key_id;
    }

    // get  name of the value for meta ( select from MDF category )
    public function get_only_val($key_id, $filter_id, $value) {
        foreach (MetaDataFilterPage::get_fiters_posts($filter_id) as $id) {
            foreach (MetaDataFilterPage::get_html_items($id) as $key => $val) {
                if ($key == $key_id) {
                    switch ($val['type']) {
                        case 'checkbox':
                            if ($value) {
                                return 'mdf_checkbox'; // To collect in one chart 
                                return esc_html__('Yes', 'meta-data-filter');
                            } else {
                                return esc_html__('No', 'meta-data-filter');
                            }
                            break;
                        case 'label':
                            return 'mdf_label'; // To collect in one chart
                            if ($value) {
                                return esc_html__('Yes', 'meta-data-filter');
                            } else {
                                return esc_html__('No', 'meta-data-filter');
                            }
                            break;
                        case "by_author":
                            $author = get_userdata($value);
                            if ($author) {
                                return $author->get('user_nicename');
                            } else {
                                return $value;
                            }
                            break;
                        case "slider":
                            $range = array();
                            $range = explode("^", $value);
                            if (count($range) > 1 AND (!empty($range[1])AND $range[1] != 0 )) {
                                return sprintf(esc_html__("From:%s to: %s", 'meta-data-filter'), $range[0], $range[1]);
                            } else {
                                return $range[0];
                            }
                            break;
                        case "range_select":

                            $range = array();
                            $range = explode("^", $value);
                            if (count($range) > 1 AND (!empty($range[1])AND $range[1] != 0 )) {
                                return sprintf(esc_html__("From:%s to: %s", 'meta-data-filter'), $range[0], $range[1]);
                            } else {
                                return $range[0];
                            }
                            break;
                        default :
                            return $value;
                            break;
                    }
                } elseif ($key . '_from' == $key_id) {
                    if (!empty($value)) {
                        if ($val['type'] == 'calendar') {
                            $range = array();
                            $range = explode("^", $value);
                            if (count($range) > 1 AND (!empty($range[1])AND $range[1] != 0 )) {
                                return sprintf(esc_html__("From:%s to: %s", 'meta-data-filter'), date("m.d.y", intval($range[0])), date("m.d.y", intval($range[1])));
                            } else {
                                return esc_html__('From:', 'meta-data-filter') . date("m.d.y", intval($range[0]));
                            }
                        } else {
                            return esc_html__('From:', 'meta-data-filter') . $value;
                        }
                    }
                } elseif ($key . '_to' == $key_id) {
                    if (!empty($value)) {
                        if ($val['type'] == 'calendar') {
                            return esc_html__('To:', 'meta-data-filter') . date("m.d.y", intval($value));
                        } else {
                            return esc_html__('To:', 'meta-data-filter') . $value;
                        }
                    }
                }
            }
        }
    }

    //Preparing a tax_query for writing to a table
    public function render_taxonomy_query($tax_query, $post_type) {
        $parsed_tax = array();

        foreach ($tax_query as $tax_item) {
            if (isset($tax_item['taxonomy']) AND in_array($tax_item['taxonomy'], $this->items_for_stat[$post_type]['tax'])) {

                if (is_array($tax_item['terms'])) {
                    foreach ($tax_item['terms'] as $id_term) {
                        $parsed_tax[] = array(
                            'taxonomy' => $tax_item['taxonomy'],
                            'id_term' => $id_term
                        );
                    }
                } else {
                    $parsed_tax[] = array(
                        'taxonomy' => $tax_item['taxonomy'],
                        'id_term' => $tax_item['terms']
                    );
                }
            }
        }

        return $parsed_tax;
    }

    function let_to_num($size) {
        $l = substr($size, -1);
        $ret = substr($size, 0, -1);
        switch (strtoupper($l)) {
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
        }
        return $ret;
    }

    public function get_mdf_cron_schedules($key = '') {
        $schedules = array(
            'hourly' => HOUR_IN_SECONDS,
            'twicedaily' => HOUR_IN_SECONDS * 12,
            'daily' => DAY_IN_SECONDS,
            'week' => WEEK_IN_SECONDS,
            'month' => WEEK_IN_SECONDS * 4,
            'min1' => MINUTE_IN_SECONDS,
        );

        if (!empty($key)) {
            return $schedules[$key];
        }

        return $schedules;
    }
	public static function sanitize_array_r($arr) {
        $newArr = array();
		if(!is_array($arr)){
			return sanitize_text_field($arr);
		}
        foreach ($arr as $key => $value) {
            $newArr[sanitize_key($key)] = ( is_array($value) ) ? self::sanitize_array_r($value) : sanitize_text_field($value);
        }
        return $newArr;		
	}
    public function set_start_statistic_period() {

        $start_data = NULL;

        if (false) {
            $start_data = get_option("mdf_start_period_stat");
            if (!$start_data) {
                update_option("mdf_start_period_stat", time());
            } else {
                if (MetaDataFilter::$is_free) {
                    if (( $start_data + YEAR_IN_SECONDS / 2) < time()) {
                        $this->is_expire = true;
                    }
                }
            }
        }

        return $start_data;
    }

}

// start statistic
add_action('init', function () {
    $mdf_stat = new MDF_SEARCH_STAT();
}, 99);

