<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wrap">

    <input type="hidden" name="mdf_const_links_values" value="" />
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Use this link on your site", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <?php echo esc_url_raw($page_mdf_link) ?>
                    </fieldset>
                </td>
            </tr>


            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Drop new link here", 'meta-data-filter') ?></label></th>
                <td>
                    <fieldset>
                        <textarea name="mdf_new_link" class="mdf_new_link" placeholder="<?php esc_html_e("Drop here right link only for updating constant link!!", 'meta-data-filter') ?>"></textarea>
                    </fieldset>
                </td>
            </tr>


            <?php if (empty($page_mdf_string)): ?>

                <tr valign="top">
                    <th scope="row" class="mdtf-red"><label><?php esc_html_e("ATTENTION", 'meta-data-filter') ?></label></th>
                    <td>
                        <fieldset>
                            <?php esc_html_e("Link is inspired. Recreate this one on the front on the same browser!", 'meta-data-filter') ?>
                        </fieldset>
                    </td>
                </tr>

            <?php else: ?>
                <tr valign="top">
                    <th scope="row" class="mdtf-green-b"><label><?php esc_html_e("ALL IS OK", 'meta-data-filter') ?></label></th>
                    <td>
                        <fieldset>
                        </fieldset>
                    </td>
                </tr>

            <?php endif; ?>
        </tbody>
    </table>



    <div class="clear"></div>

</div>

