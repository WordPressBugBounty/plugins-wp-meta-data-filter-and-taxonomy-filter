<?php
$unsobscr_link = "";
$link = explode("?", $subscr['link']);
$unsobscr_link = $link[0] . "?id_user=" . $subscr['user_id'] . "&key=" . $subscr['key'] . "&mdf_skey=" . $subscr['secret_key'];

$search_terms = $subscr['get'];
$products = new WP_Query(
        array('post_type' => 'product', 'post__in' => $posts, 'orderby' => 'date', 'order' => 'DESC', 'posts_per_page' => -1)
);
$product_count = count($products->posts);
$text_var = array($user->display_name, $user->user_nicename, $product_count);
$text_str = array('[DISPLAY_NAME]', '[USER_NICENAME]', '[PRODUCT_COUNT]');
$text_email = str_replace($text_str, $text_var, $text_email);
?>
<style>
    /* THIS IS EMAIL TEMPLATE AND STYLES SHOULD BE HERE */
    .mdf_mail{
        padding-left: 0px !important;
        list-style: none;
    }
    ul.mdf_mail li{
        margin-left: 0px;
        width: 32%;
        display: inline-block;
        position: relative;
    }
    ul.mdf_mail li a:nth-child(2){
        display: none;
    }
    ul.mdf_mail li a h3{
        height: 80px;
    }
    ul.mdf_mail li .onsale{
        display: none;
    }
    .mdf_more_text p a:hover{
        background: blue;
    }
    .mdf_terms{
        margin-left: 5px;
        padding: 3px;
        background: #E6E6E6;
    }
    .mdf_author_name{
        margin-left: 5px;
        padding: 3px;
        background: #E6E6E6;
    }
    ul li a img{
        width: 100%!important;
    }
    .products.mdf_mail  li h2{
        padding: 0px 10px 0px 5px !important;
        min-height: 70px;
    }
</style>

<div class="mdf_text_email" style="color: #4a4a4e; margin-bottom: 30px;" ><?php echo wp_kses_post(wp_unslash($text_email)) ?></div>
<style>
    /* THIS IS EMAIL TEMPLATE AND STYLES SHOULD BE HERE */
    .mdf_search_terms p span{
        margin-left: 10px;
        padding: 3px;
        background: #dfdfdf;
    }
</style>
<div class="mdf_search_terms"><p style="color:#6a6969;">

        <?php esc_html_e('Terms', 'meta-data-filter') ?>:&nbsp;<?php echo wp_kses_post(wp_unslash($search_terms)) ?>
    </p></div>
<?php if ($last_email) { ?>
    <div class="last_email"><?php esc_html_e('Attention! This is the last email. If you want to continue get such emails -> Go by next link and subscribe again', 'meta-data-filter') ?>
        - <a href="<?php echo esc_url_raw($subscr['link']) . "&order_by=date&order=DESC" ?>"><?php esc_html_e('Subscribe', 'meta-data-filter'); ?></a>
    </div>
<?php } ?>
<div class="mdf_subscr"><p><?php esc_html_e('If you want to Unsubscribe from this newsletter', 'meta-data-filter') ?> - <a href="<?php echo esc_url_raw($unsobscr_link) ?>"><?php esc_html_e('unsubscribe', 'meta-data-filter') ?></a> </p></div>
<ul class="products mdf_mail" >
    <?php
    if ($products->have_posts()) {
        $i = 0;
        while ($products->have_posts()) : $products->the_post();
            wc_get_template_part('content', 'product');
            if (++$i >= 9) {
                break;
            }
        endwhile;

        if ($i < count($products->posts)) {
            ?>
            <div style="margin-top: 20px" class="mdf_more_text" >
                <p style="text-align: center;font-size: 18px;" ><a style="padding: 4px;border: 2px solid #557DA1; text-decoration: none;" href="<?php echo $subscr['link'] . "&order_by=date&order=DESC" ?>"><?php esc_html_e('See more new products...', 'meta-data-filter') ?></a></p>
            </div> 
            <?php
        }
    } else {
        echo esc_html__('No products found', 'meta-data-filter');
    }
    wp_reset_postdata();
    ?>
</ul><!--/.products-->
<div class="woof_subscr"><p><?php esc_html_e('If you want to Unsubscribe from this newsletter', 'meta-data-filter') ?> - <a href="<?php echo esc_url_raw($unsobscr_link) ?>"><?php esc_html_e('unsubscribe', 'meta-data-filter') ?></a> </p></div>
