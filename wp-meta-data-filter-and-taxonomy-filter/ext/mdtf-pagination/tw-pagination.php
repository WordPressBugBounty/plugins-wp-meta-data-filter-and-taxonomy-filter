<?php
//Customized for MDTF by realmag777

if (!class_exists('MDTF_Pagination')) {

    class MDTF_Pagination {

        //	@var string (The plugin version)
        var $version = '1.1';
        //	@var string (The options string name for this plugin)
        var $optionsName = 'mdtf_pagination_options';
        //	@var string $pluginurl (The url to this plugin)
        var $pluginurl = '';
        //	@var string $pluginpath (The path to this plugin)
        var $pluginpath = '';
        //	@var array $options (Stores the options for this plugin)
        var $options = array();
        //	@var string $type (The post type)
        var $type = 'posts';

        function __construct() {
            //Setup constants
            $this->pluginurl = MetaDataFilterCore::get_application_uri() . 'ext/mdtf-pagination/';
            $this->pluginpath = MetaDataFilterCore::get_application_path() . 'ext/mdtf-pagination/';
            //Initialize the options
            $this->get_options();
            //Actions
            add_action('admin_menu', array(&$this, 'admin_menu_link'));

            if ($this->options ['css']) {
                add_action('wp_print_styles', array(&$this, 'mdtf_pagination_css'));
            }
        }

        //	Pagination based on options/args
        function paginate($query = 'global', $args = false) {
            if ($this->type === 'comments' && !get_option('page_comments'))
                return;

            $ar = wp_parse_args($args, $this->options);
            extract($ar, EXTR_SKIP);

            if (!isset($page) && !isset($pages)) {
                if ($query !== 'global' && is_object($query))
                    $wp_query = $query;
                else
                    global $wp_query;

                if ($this->type === 'posts') {
                    //fixed
                    if (isset($_REQUEST['content_redraw_page'])) {
                        $page = sanitize_text_field($_REQUEST['content_redraw_page']);
                    } else {
                        $page = get_query_var('cpage');
                    }
                    //***
                    $pages = $wp_query->max_num_pages;
                } else {
                    //fixed
                    if (isset($_REQUEST['content_redraw_page'])) {
                        $page = sanitize_text_field($_REQUEST['content_redraw_page']);
                    } else {
                        $page = get_query_var('cpage');
                    }
                    //***
                    $comments_per_page = get_option('comments_per_page');
                    $pages = get_comment_pages_count();
                }
                $page = !empty($page) ? intval($page) : 1;
            }
            $prevlink = ($this->type === 'posts') ? esc_url(get_pagenum_link($page - 1)) : get_comments_pagenum_link($page - 1);
            $nextlink = ($this->type === 'posts') ? esc_url(get_pagenum_link($page + 1)) : get_comments_pagenum_link($page + 1);
            if (isset($before)) {
                $output = stripslashes($before);
            }
            if ($pages > 1) {
                $output .= sprintf('<ol class="tw-pagination%s">', ($this->type === 'posts') ? '' : ' tw-pagination-comments');
                if (!empty($title)) {
                    $output .= sprintf('<li><span class="title">%s</span></li>', stripslashes($title));
                }
                $ellipsis = "<li><span class='gap'>...</span></li>";

                if ($page > 1 && !empty($previouspage)) {
                    $output .= sprintf('<li><a title="%s" href="%s" class="prev">%s</a></li>', ($page - 1), $prevlink, stripslashes($previouspage));
                }
                $min_links = $range * 2 + 1;
                $block_min = min($page - $range, $pages - $min_links);
                $block_high = max($page + $range, $min_links);
                $left_gap = (($block_min - $anchor - $gap) > 0) ? true : false;
                $right_gap = (($block_high + $anchor + $gap) < $pages) ? true : false;
                if ($left_gap && !$right_gap) {
                    $output .= sprintf('%s%s%s', $this->paginate_loop(1, $anchor), $ellipsis, $this->paginate_loop($block_min, $pages, $page));
                } else if ($left_gap && $right_gap) {
                    $output .= sprintf('%s%s%s%s%s', $this->paginate_loop(1, $anchor), $ellipsis, $this->paginate_loop($block_min, $block_high, $page), $ellipsis, $this->paginate_loop(($pages - $anchor + 1), $pages));
                } else if ($right_gap && !$left_gap) {
                    $output .= sprintf('%s%s%s', $this->paginate_loop(1, $block_high, $page), $ellipsis, $this->paginate_loop(($pages - $anchor + 1), $pages));
                } else {
                    $output .= $this->paginate_loop(1, $pages, $page);
                }
                if ($page < $pages && !empty($nextpage)) {
                    $output .= sprintf('<li class="next_li"><a title="%s" href="%s" class="next">%s</a></li>', ($page + 1), $nextlink, stripslashes($nextpage));
                }
                $output .= "</ol>";
            }
            if (isset($after)) {
                $output .= stripslashes($after);
            }
            if ($pages > 1 || $empty) {
                //fixed
                if (isset($_REQUEST['mdtf_in_shortcode'])) {
                    return $output;
                } else {
                    echo wp_kses_post(wp_unslash($output));
                }
            }
        }

        //	Helper function for pagination which builds the page links.
        function paginate_loop($start, $max, $page = 0) {
            $output = "";
            for ($i = $start; $i <= $max; $i++) {
                $p = ($this->type === 'posts') ? esc_url(get_pagenum_link($i)) : get_comments_pagenum_link($i);
                $output .= ($page == intval($i)) ? "<li><span class='page current'>$i</span></li>" : "<li><a href='$p' title='$i' class='page'>$i</a></li>";
            }
            return $output;
        }

        function mdtf_pagination_css() {
            $name = "tw-pagination.css";
            if (false !== @file_exists(TEMPLATEPATH . "/$name")) {
                $css = get_template_directory_uri() . "/$name";
            } else {
                $css = $this->pluginurl . $name;
            }

            wp_enqueue_style('mdtf_pagination', $css, false, $this->version, 'screen');
        }

        //	Retrieves the plugin options from the database. (@return array)
        function get_options() {
            $options = MetaDataFilterCore::get_setting('ajax_pagination');
            if (empty($options)) {
                $options = array();
            }
            //***
            if (!isset($options['css'])) {
                $options['css'] = 0;
            }
            if (!isset($options['empty'])) {
                $options['empty'] = 0;
            }
            //***
            if (empty($options)) {
                $options = array(
                    'title' => esc_html__('Pages:', 'meta-data-filter'),
                    'nextpage' => '&raquo;',
                    'previouspage' => '&laquo;',
                    'css' => 1,
                    'before' => '<div class="navigation">',
                    'after' => '</div>',
                    'empty' => 0,
                    'range' => 3,
                    'anchor' => 1,
                    'gap' => 3
                );
            }

            $this->options = $options;
        }

        //	Saves the admin options to the database.
        function save_admin_options() {
            return update_option($this->optionsName, $this->options);
        }

        //	Adds the options subpanel
        function admin_menu_link() {
            
        }

        //	Adds the Settings link to the plugin activate/deactivate page
        function filter_plugin_actions($links, $file) {
            $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . esc_html__('Settings', 'meta-data-filter') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        //	Adds settings/options page
        function admin_options_page() {
            if (isset($_POST ['mdtf_pagination_save'])) {
                if (wp_verify_nonce($_POST ['_wpnonce'], 'tw-pagination-update-options')) {
                    $this->options ['title'] = MetaDataFilterCore::escape($_POST ['title']);
                    $this->options ['previouspage'] = MetaDataFilterCore::escape($_POST ['previouspage']);
                    $this->options ['nextpage'] = MetaDataFilterCore::escape($_POST ['nextpage']);
                    $this->options ['before'] = MetaDataFilterCore::escape($_POST ['before']);
                    $this->options ['after'] = MetaDataFilterCore::escape($_POST ['after']);
                    $this->options ['empty'] = (isset($_POST ['empty']) && $_POST ['empty'] === 'on') ? true : false;
                    $this->options ['css'] = (isset($_POST ['css']) && $_POST ['css'] === 'on') ? true : false;
                    $this->options ['range'] = intval($_POST ['range']);
                    $this->options ['anchor'] = intval($_POST ['anchor']);
                    $this->options ['gap'] = intval($_POST ['gap']);

                    $this->save_admin_options();
                    echo '<div class="updated"><p>' . esc_html__('Success! Your changes were successfully saved!', 'meta-data-filter') . '</p></div>';
                } else {
                    echo '<div class="error"><p>' . esc_html__('Whoops! There was a problem with the data you posted. Please try again.', 'meta-data-filter') . '</p></div>';
                }
            }
            ?>

            <div class="wrap">
                <div class="icon32" id="icon-options-general"><br/></div>
                <h2>MDTF AJAX Pagination Settings</h2>
                <form method="post" id="mdtf_pagination_options">
                    <?php wp_nonce_field('tw-pagination-update-options'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Pagination Label:', 'meta-data-filter'); ?></th>
                            <td><input name="title" type="text" id="title" size="40" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($this->options['title'])))); ?>"/>
                                <span class="description"><?php esc_html_e('The text/HTML to display before the list of pages.', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Previous Page:', 'meta-data-filter'); ?></th>
                            <td><input name="previouspage" type="text" id="previouspage" size="40" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($this->options['previouspage'])))); ?>"/>
                                <span class="description"><?php esc_html_e('The text/HTML to display for the previous page link.', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Next Page:', 'meta-data-filter'); ?></th>
                            <td><input name="nextpage" type="text" id="nextpage" size="40" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($this->options['nextpage'])))); ?>"/>
                                <span class="description"><?php esc_html_e('The text/HTML to display for the next page link.', 'meta-data-filter'); ?></span></td>
                        </tr>
                    </table>
                    <p>&nbsp;</p>
                    <h3><?php esc_html_e('Advanced Settings', 'meta-data-filter'); ?></h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Before Markup:', 'meta-data-filter'); ?></th>
                            <td><input name="before" type="text" id="before" size="40" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($this->options['before'])))); ?>"/>
                                <span class="description"><?php esc_html_e('The HTML markup to display before the pagination code.', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('After Markup:', 'meta-data-filter'); ?></th>
                            <td><input name="after" type="text" id="after" size="40" value="<?php echo wp_kses_post(wp_unslash(stripslashes(htmlspecialchars($this->options['after'])))); ?>"/>
                                <span class="description"><?php esc_html_e('The HTML markup to display after the pagination code.', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Markup Display:', 'meta-data-filter'); ?></th>
                            <td><label for="empty">
                                    <input type="checkbox" id="empty" name="empty" <?php echo ($this->options['empty']) ? "checked='checked'" : ""; ?>/> <?php esc_html_e('Show Before Markup and After Markup, even if the page list is empty?', 'meta-data-filter'); ?></label></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Pagination CSS File:', 'meta-data-filter'); ?></th>
                            <td><label for="css">
                                    <input type="checkbox" id="css" name="css" <?php echo ($this->options['css']) ? "checked='checked'" : ""; ?>/> <?php printf(esc_html__('Include the default stylesheet tw-pagination.css? Pagination will first look for tw-pagination.css in your theme directory (themes/%s).', 'meta-data-filter'), get_template()); ?></label></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Page Range:', 'meta-data-filter'); ?></th>
                            <td>
                                <select name="range" id="range">
                                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                                        <option value="<?php echo esc_attr($i); ?>" <?php echo ($i == $this->options['range']) ? "selected='selected'" : ""; ?>><?php echo esc_attr($i); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="description"><?php esc_html_e('The number of page links to show before and after the current page. Recommended value: 3', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Page Anchors:', 'meta-data-filter'); ?></th>
                            <td>
                                <select name="anchor" id="anchor">
                                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                                        <option value="<?php echo esc_attr($i); ?>" <?php echo ($i == $this->options['anchor']) ? "selected='selected'" : ""; ?>><?php echo esc_attr($i); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="description"><?php esc_html_e('The number of links to always show at beginning and end of pagination. Recommended value: 1', 'meta-data-filter'); ?></span></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Page Gap:', 'meta-data-filter'); ?></th>
                            <td>
                                <select name="gap" id="gap">
                                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                                        <option value="<?php echo esc_attr($i); ?>" <?php echo ($i == $this->options['gap']) ? "selected='selected'" : ""; ?>><?php echo esc_attr($i); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="description"><?php esc_html_e('The minimum number of pages in a gap before an ellipsis (...) is added. Recommended value: 3', 'meta-data-filter'); ?></span></td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" value="Save Changes" name="mdtf_pagination_save" class="button-primary" /></p>
                </form>
            </div>

            <?php
        }

    }

}

//	Instantiate the class
if (class_exists('MDTF_Pagination')) {
    $mdtf_pagination = new MDTF_Pagination();
}

//	Pagination function to use for posts
function mdtf_pagination($query = 'global', $args = false) {
    global $mdtf_pagination;
    return $mdtf_pagination->paginate($query, $args);
}

//	Pagination function to use for post comments
function mdtf_pagination_comments($query = 'global', $args = false) {
    global $mdtf_pagination;
    $mdtf_pagination->type = 'comments';
    return $mdtf_pagination->paginate($query, $args);
}
