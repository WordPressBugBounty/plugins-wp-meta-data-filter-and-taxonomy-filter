<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once MetaDataFilterCore::get_application_path() . 'classes/cron.php';
define("MDTF_MESSENGER_URI", plugin_dir_url(__FILE__));

class MDF_POSTS_MESSENGER {

    public $user_meta_key = "mdf_posts_messenger";
    public $subscribe_lang = "";
    public $date_expire = "no";
    public $count_message = -1;
    public $subscr_count = 2; //options
    public $priority_limit = 'both';
    public $subscr_period_option = "twicemonthly"; //options
    public $header_email = 'New posts';
    public $subject_email = '';
    public $text_email = '';
    public $cron = NULL;
    public $current_arg = array();
    public $cron_hook = 'mdf_posts_messenger_cron';
    public $wp_cron_period = WEEK_IN_SECONDS;
    public $messenger_label = "";
    public $customer_notes = "";
    public $has_atts = false;

    public function __construct() {
        $this->header_email = MetaDataFilterCore::get_setting('header_email_messenger');
        $this->subscribe_lang = esc_html__('Subscription', 'meta-data-filter');
        $this->subject_email = MetaDataFilterCore::get_setting('subject_email_messenger');
        $this->text_email = MetaDataFilterCore::get_setting('text_email_messenger');
        $this->count_message = MetaDataFilterCore::get_setting('count_message');
        $this->subscr_count = MetaDataFilterCore::get_setting('subscr_count');
        $this->subscr_period_option = MetaDataFilterCore::get_setting('wp_cron_period_messenger');
        $this->date_expire = MetaDataFilterCore::get_setting('date_expire_period_messenger');
        $this->priority_limit = MetaDataFilterCore::get_setting('priority_limit_messenger');
        $this->messenger_label = MetaDataFilterCore::get_setting('label_messenger');
        $this->customer_notes = MetaDataFilterCore::get_setting('notes_for_customer_messenger');
    }

    public function init() {
        // Cron
        add_action('mdf_posts_messenger_cron', array($this, 'mdf_posts_messenger'), 10);
        $this->cron = new PN_WP_CRON_MDTF_MAIN('mdf_messenger_wpcron');
        $this->wp_cron_period = (int) $this->get_mdf_cron_schedules($this->subscr_period_option);
        $this->make_send_emails();
        // Ajax  action
        add_action('wp_ajax_mdf_posts_messenger_add_subscr', array($this, 'mdf_add_subscr'));
        add_action('wp_ajax_nopriv_mdf_posts_messenger_add_subscr', array($this, 'mdf_add_subscr'));
        add_action('wp_ajax_mdf_posts_messenger_remove_subscr', array($this, 'mdf_remove_subscr'));
        add_action('wp_ajax_nopriv_mdf_posts_messenger_remove_subscr', array($this, 'mdf_remove_subscr'));
        //+++
        // add shortcode
        add_shortcode('mdf_posts_messenger', array($this, 'mdf_posts_messenger_shortcode'));
        //styles & scripts
        add_action('wp_footer', array($this, 'mdf_init_js_css'));
        //special functionality hooks
        add_filter('meta_data_filter_args', array($this, 'mdf_save_current_arg'));
        add_filter('mdf_filter_arg_page', array($this, 'mdf_get_page_by_link'));
        //add logic hooks
        add_action('init', array($this, 'mdf_unsubscr'));
    }

    public function mdf_unsubscr() {
        $this->mdf_external_cron_init(); // It checks key of the external cron

        if (!isset($_GET['id_user']) OR!isset($_GET['key']) OR!isset($_GET['mdf_skey'])) {
            return;
        }

        $sanit_get = $this->sanitaz_array_r($_GET);
        $subscr = get_user_meta($sanit_get['id_user'], $this->user_meta_key, true);
        $text = "";
        if ($subscr[$sanit_get['key']]['secret_key'] == $sanit_get['mdf_skey']) {
            unset($subscr[$sanit_get['key']]);
            update_user_meta($sanit_get['id_user'], $this->user_meta_key, $subscr);
        }

        $data['text'] = esc_html__('You are unsubscribed from the future products newsletters.', 'meta-data-filter');
        //Show unsabscr messeng  
        MetaDataFilterHtml::render_html_e(MetaDataFilterCore::get_application_path() . 'ext/mdf_posts_messenger/views' . DIRECTORY_SEPARATOR . 'unsubscr_template.php', $data);
        die();
    }

    //Check special constant link 
    public function mdf_get_page_by_link($arg) {
        if (isset($_REQUEST['mdf_mess_link']) AND isset($_REQUEST['mdf_mess_id']) AND!isset($_REQUEST['mdf_page'])) {
            $arg = $this->get_mdf_string(sanitize_text_field($_REQUEST['mdf_mess_link']), sanitize_text_field($_REQUEST['mdf_mess_id']));
        }
        return $arg;
    }

    //get arg   for  MDTF page
    function get_mdf_string($key, $id) {
        $subscr = array();
        $subscr = get_user_meta($id, $this->user_meta_key, true);
        return $subscr[$key]['attr'];
    }

    //+++++++++++++++++
    // Save current  query args
    public function mdf_save_current_arg($atts) {
        if (!is_user_logged_in()) {
            return $atts;
        }

        $string = base64_encode(json_encode($atts));

        $key_string = "mdf_current_arg" . get_current_user_id();
        //save 
        if (MetaDataFilterCore::$where_keep_search_data == 'session') {
            if (!isset($_SESSION['mdf_curr_arg'])) {
                $_SESSION['mdf_curr_arg'] = array();
            }
            $_SESSION['mdf_curr_arg'][$key_string] = $string;
        } else {
            set_transient($key_string, $string, DAY_IN_SECONDS);
        }

        return $atts;
    }

    // Get data 
    public function get_current_arg($key_string) {
        $value = "";
        //+++
        if (!is_numeric($key_string)) {
            if (MetaDataFilterCore::$where_keep_search_data == 'session') {

                if (!isset($_SESSION['mdf_curr_arg'])) {
                    $_SESSION['mdf_curr_arg'] = array();
                }

                if (isset($_SESSION['mdf_curr_arg'][$key_string])) {
                    $value = $_SESSION['mdf_curr_arg'][$key_string];
                }
            } else {
                $value = (string) get_transient($key_string);
            }
        }

        return $value;
    }

    public function mdf_add_subscr() {
        global $wpdb, $wp_query;

        if (!isset($_POST['attr']) OR!isset($_POST['user_id']) OR!isset($_POST['curr_link'])) {
            die("Wrong data");
        }

        //***

        $data = array();
        $sanit_user_id = sanitize_key($_POST['user_id']);
        if ($sanit_user_id < 1) {
            die("User id error"); //if user id - wrong!!!
        }

        $attr = $this->sanitaz_array_r(json_decode(base64_decode($_POST['attr']), true));
        $data['attr'] = $attr;
        $key = uniqid('mdtfms_'); // Create   key for this subscr
        $data['key'] = $key;

        $data['secret_key'] = bin2hex(random_bytes(9)); //Key for check link from email

        $data['user_id'] = $sanit_user_id;

        //create special constant link
        $link_slug = $attr["mdf_widget_options"]['slug'];
        $link_cat = $attr["mdf_widget_options"]["meta_data_filter_cat"];
        $link_key = $key;
        $link_id = $sanit_user_id;
        $data['link'] = $attr["mdf_widget_options"]['search_result_page'];
        if (empty($data['link']) OR $data['link'] == "self") {
            $temp_link = array();
            $temp_link = explode("?", esc_url($_POST['curr_link']));
            $data['link'] = $temp_link[0];
        }
        $data['link'] .= sprintf("?slg=%s&mdf_cat=%s&mdf_mess_link=%s&mdf_mess_id=%s", $link_slug, $link_cat, $link_key, $link_id);
        //+++++
        $data['request'] = "";
        $data['post_ids'] = array();
        $curr_arg = json_decode(base64_decode($this->get_current_arg("mdf_current_arg" . $sanit_user_id)), true);
        if ($curr_arg) {
            //add args
            $curr_arg['nopaging'] = true; // pagination -1
            $curr_arg['fields'] = "ids"; // Get only ID

            $query = new WP_Query($curr_arg);
            $data['request'] = $query->request;
            $data['post_ids'] = $query->posts;
        }

        //  Create html  for tooltip and email
        $data['get'] = MetaDataFilterHtml::render_html(MetaDataFilterCore::get_application_path() . 'ext/mdf_posts_messenger/views' . DIRECTORY_SEPARATOR . 'show_terms_meta_names.php', $data['attr']);
        ; //show_terms_meta_names

        $subscr = array();
        $subscr = get_user_meta($data['user_id'], $this->user_meta_key, true); //Get all data of the user
        if (!is_array($subscr)) {
            $subscr = array();
        }
        if (count($subscr) >= $this->subscr_count) {
            die('count is max'); // Check limit count on backend
        }

        $data['subscr_lang'] = apply_filters('mdf_subscribe_lang', $this->subscribe_lang); //Text of  the subscriptions

        $data['count'] = ((int) $this->count_message != -1) ? (int) $this->count_message : PHP_INT_MAX; // not limit* million times
        $data['date'] = time();
        if ($this->get_mdf_cron_schedules($this->date_expire)) {
            $data['date'] = $data['date'] + $this->get_mdf_cron_schedules($this->date_expire); // date of expire
        } else {
            $data['date'] = $data['date'] + YEAR_IN_SECONDS * 10; // not limit*  10 years
        }
        $subscr[$key] = array();
        $subscr[$key] = $data;
        update_user_meta($data['user_id'], $this->user_meta_key, $subscr); //add new item
        //for Ajax redraw
        $content = MetaDataFilterHtml::render_html(MetaDataFilterCore::get_application_path() . 'ext/mdf_posts_messenger/views' . DIRECTORY_SEPARATOR . 'item_list_subscr.php', $data);

        die($content);
    }

    public function mdf_remove_subscr() {
        if (!isset($_POST['key']) OR!isset($_POST['user_id'])) {
            die('No data!');
        }

        $user_id = sanitize_key($_POST['user_id']);
        $key = sanitize_key($_POST['key']);
        $subscr = get_user_meta($user_id, $this->user_meta_key, true);
        unset($subscr[$key]);
        update_user_meta($user_id, $this->user_meta_key, $subscr);
        $arg = array('key' => $key);
        die(json_encode($arg));
    }

    // wp cron  functions
    public function make_send_emails($reset = false) {

        if ($this->subscr_period_option != 'no' AND!empty($this->subscr_period_option)) {
            if ($this->wp_cron_period) {
                $this->mdf_wpcron_init();
            }
        }
    }

    public function mdf_wpcron_init($remove = false) {
        if ($remove) {
            $this->cron->remove($this->cron_hook);
            return;
        }

        if ($this->wp_cron_period) {

            if (!$this->cron->is_attached($this->cron_hook, $this->wp_cron_period)) {
                $this->cron->attach($this->cron_hook, time(), $this->wp_cron_period);
            }
            $this->cron->process();
        }
    }

    public function mdf_posts_messenger() {
        add_action('init', array($this, 'mdf_do_mesenger_action'), 999); // init messeng function
    }

    //++++++++++++++
    //External cron
    public function mdf_external_cron_init() {

        //check secret key  ( min  16 symbol )  
        if (!isset($_GET['mdf_pm_cron_key']) OR empty($_GET['mdf_pm_cron_key']) OR strlen($_GET['mdf_pm_cron_key']) < 16) {
            return false;
        }

        $sanitazed_key = MetaDataFilterCore::escape($_GET['mdf_pm_cron_key']);
        $cron_key = MetaDataFilterCore::get_setting('use_external_cron');
        if (!$cron_key OR empty($cron_key)) {
            return false;
        }

        if ($sanitazed_key AND $cron_key == $sanitazed_key) {
            $this->mdf_do_mesenger_action();
        } else {
            return false;
        }
    }

    //+++++++++++++++



    public function get_mdf_cron_schedules($key = '') {
        $schedules = array(
            'hourly' => HOUR_IN_SECONDS,
            'twicedaily' => HOUR_IN_SECONDS * 12,
            'daily' => DAY_IN_SECONDS,
            'week' => WEEK_IN_SECONDS,
            'twicemonthly' => WEEK_IN_SECONDS * 2,
            'month' => WEEK_IN_SECONDS * 4,
            'twomonth' => WEEK_IN_SECONDS * 9,
            'min1' => MINUTE_IN_SECONDS, // only for test
        );
        if (!empty($key) AND isset($schedules[$key])) {
            return (int) $schedules[$key];
        } else {
            return NULL;
        }

        return $schedules;
    }

    public function mdf_init_js_css() {
        wp_enqueue_script('mdf_posts_messeger_js', MDTF_MESSENGER_URI . '/js/posts_messenger.js', array('jquery'));
        wp_enqueue_style('mdf_posts_messeger_css', MDTF_MESSENGER_URI . '/css/posts_messenger.css');
        $translation_array = array(
            'mdf_confirm_lang' => esc_html__('Are you sure?', 'meta-data-filter')
        );
        wp_localize_script('mdf_posts_messeger_js', 'mdf_posts_messenger_data', $translation_array);
    }

    // Now it not used
    public static function set_current_request() {
        $wp_mess = null;
        if (class_exists('MetaDataFilter') AND MetaDataFilter::is_page_mdf_data()) {
            $_REQUEST['mdf_do_not_render_shortcode_tpl'] = true;
            $_REQUEST['mdf_get_query_args_only'] = true;
            do_shortcode('[meta_data_filter_results]');
            $args = self::sanitize_array_r($_REQUEST['meta_data_filter_args']);
            $wp_mess = new WP_Query($args);
        }
        if ($wp_mess) {
            $key_string = "mdf_messenger_curr_request_" . get_current_user_id();
            set_transient($key_string, $wp_mess->request, DAY_IN_SECONDS);
            return true;
        } else {
            return false;
        }
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
    //Send messenges functions
    public function mdf_do_mesenger_action() { //return;
        global $wpdb;
        // get all users
        $users = get_users(array('count_total' => false, 'fields' => array('ID', 'display_name', 'user_login', 'user_nicename', 'user_email'),));

        foreach ($users as $user) {
            $data_user = get_user_meta($user->ID, $this->user_meta_key, true); // get subscribtion of user 
            if (empty($data_user) OR count($data_user) <= 0 OR!is_array($data_user)) {
                continue;
            }
            foreach ($data_user as $key => $data_subscr) {    // check subcr
                $data_email = array();
                $data_email['posts'] = array();
                $products = $wpdb->get_results($data_subscr['request']);
                foreach ($products as $p) {      // if it has new products
                    if (!in_array($p->ID, $data_subscr['post_ids'])) {
                        $data_email['posts'][] = $p->ID;
                    }
                }

                if (count($data_email['posts']) > 0) {
                    $last_email = $by_date = $by_count = false;
                    (int) $data_user[$key]['count']--;
                    if (((int) $data_user[$key]['count']) <= 0) {
                        $by_count = true;
                    }
                    if (((int) $data_subscr['date']) < time()) {
                        $by_date = true;
                    }
                    if ($this->priority_limit == 'by_date' AND $by_date) {   //priority by date  
                        $last_email = true;
                    } elseif ($this->priority_limit == 'by_count' AND $by_count) { //priority by count 
                        $last_email = true;
                    } elseif ($this->priority_limit == 'both' AND ( $by_count OR $by_date)) {   //both
                        $last_email = true;
                    }
                    $data_email['user'] = $user;
                    $data_email['text_email'] = $this->text_email;
                    $data_email['header_email'] = $this->header_email;
                    $data_email['subscr'] = $data_subscr;
                    $data_email['last_email'] = $last_email;


                    if ($last_email) {
                        unset($data_user[$key]);
                    } else {
                        $data_user[$key]['post_ids'] = array_merge($data_subscr['post_ids'], $data_email['posts']);
                    }
                    $successful_sending = $this->create_new_email($data_email);
                    //safe all info. If the wp_email does not work, the data is not updated

                    if ($successful_sending) {
                        update_user_meta($user->ID, $this->user_meta_key, $data_user);
                    }
                }
            }
        }
    }

    public function create_new_email($data) {

        $message = "";
        if ($data['subscr']['attr']["mdf_widget_options"]["slug"] == 'product' AND isset($GLOBALS['woocommerce'])) {
            $message = $this->create_email_content_by_woocommerce($data); //if is products of the woocommerce
        } else {
            $message = MetaDataFilterHtml::render_html(MetaDataFilterCore::get_application_path() . "ext" . DIRECTORY_SEPARATOR . "mdf_posts_messenger" . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'mdf_email_template.php', $data);
        }

        // get the preview email subject
        $subject = $this->subject_email;
        $headers = array();
        $headers[] = 'content-type: text/html';

        //create new  email name
        $home_url = array();
        $home_url = explode("//", home_url());
        $site_name = "messenger.mdtf";
        if (isset($home_url[1])) {
            $site_name = explode("/", $home_url[1]);
            $site_name = $site_name[0];
        } else {
            $site_name = explode("/", $home_url[0]);
            $site_name = $site_name[0];
        }
        $headers[] = esc_html__('From: ', 'meta-data-filter') . get_bloginfo('name') . ' <no-reply@' . $site_name . '>';
        //+++
        // send email
        return wp_mail($data['user']->user_email, $subject, $message, $headers);
    }

    public function create_email_content_by_woocommerce($data) {
        $mailer = $GLOBALS['woocommerce']->mailer();

        // get the preview email content
        $message = MetaDataFilterHtml::render_html(MetaDataFilterCore::get_application_path() . "ext" . DIRECTORY_SEPARATOR . "mdf_posts_messenger" . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'woo_email_template.php', $data);

        // create a new email
        $email = new WC_Email();

        // wrap the content with the email template and then add styles
        $message = apply_filters('woocommerce_mail_content', $email->style_inline($mailer->wrap_message($this->header_email, $message)));
        return $message;
    }

    //++++++++++++++++++++++++++++++
//[mdf_posts_messenger]
    public function mdf_posts_messenger_shortcode($atts) {
        $data = shortcode_atts(array(
            'in_filter' => 0
                ), $atts);
        ob_start();
        if (is_user_logged_in()) {

            $subscr_count = $this->subscr_count;
            $cur_user_id = get_current_user_id();
            $user_data_mess = get_user_meta($cur_user_id, $this->user_meta_key, true);
            ?>
            <div data-css-class="mdf_posts_messenger_container" class="mdf_posts_messenger_container mdf_container ">
                <div class="mdf_container_inner">

                    <?php
                    // Title
                    if (!empty($this->messenger_label)) {
                        ?>    
                        <<?php echo esc_attr(apply_filters('mdf_tag_title_sections', 'h4')); ?>>
                        <?php
                        echo esc_html($this->messenger_label);
                        ?> 
                        </<?php echo esc_attr(apply_filters('mdf_tag_title_sections', 'h4')); ?>>
                        <?php
                    }
                    //+++
                    ?>
                    <?php
                    if (!is_array($user_data_mess)) {
                        $user_data_mess = array();
                    }
                    ?>
                    <div class="mdf_subscr_list">
                        <ul> 
                            <?php
                            $counter = 1;

                            foreach ($user_data_mess as $data) {
                                //  var_dump($data);
                                //echo date('D, d M Y H:i:s',$data['date']),"*****",$data['count'],"<br>";
                                $data['counter'] = $counter;
                                MetaDataFilterHtml::render_html_e(MetaDataFilterCore::get_application_path() . 'ext/mdf_posts_messenger/views/item_list_subscr.php', $data);
                                $counter++;
                            }
                            ?>
                        </ul>
                    </div> 
                    <?php
                    $visible = 'none';
                    if ($subscr_count > count($user_data_mess)) {
                        $visible = 'block';
                    }
                    ?>
                    <div class="mdf_add_subscr_cont" style="display: <?php echo esc_attr($visible) ?>" >
                        <input name="mdf_add_subscr_messenger" data-count="<?php echo esc_attr($subscr_count) ?>" type="button" id="mdf_add_subscr" data-user="<?php echo esc_attr($cur_user_id) ?>" value="<?php esc_html_e('Subscribe on the current search request', 'meta-data-filter') ?>"  >
                    </div>
                    <?php if (!empty($this->customer_notes)): ?>
                        <span class="mdf_posts_messenger_notes_for_customer"><?php echo wp_kses_post(wp_unslash(wp_do_shortcode($this->customer_notes))); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        return ob_get_clean();
    }

    public function sanitaz_array_r($arr) {
        $newArr = array();
        foreach ($arr as $key => $value) {
            $newArr[MetaDataFilterCore::escape($key)] = ( is_array($value) ) ? $this->sanitaz_array_r($value) : MetaDataFilterCore::escape($value);
        }
        return $newArr;
    }

}

$mdf_messenger = new MDF_POSTS_MESSENGER();
$mdf_messenger->init();
