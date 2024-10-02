<style>
    /* THIS IS EMAIL TEMPLATE AND STYLES SHOULD BE HERE */
    .woof_notice{padding: 30px;background: #ececec;margin: 0 auto;margin-top: 8%;max-width: 400px;text-align: center;}   
</style>
<div class="woof_notice"><span>
        <?php echo wp_kses_post(wp_unslash($text)); ?>
    </span>        
</div>



