<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wrap">
    <h2><?php esc_html_e("MDTF Custom Utilities", 'meta-data-filter') ?></h2>

    <ol>
        <li>
            <h2><?php esc_html_e("Terms to meta", 'meta-data-filter') ?></h2>

            <?php
            $post_type = 'product';
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            ?>

            <form method="post" action="" id="mdf_term_meta_form">
                <?php echo esc_html($post_type) ?>:&nbsp;
                <select id="mdf_term_meta_kind" required="">
                    <option value="">Select what drop to meta field</option>
                    <option value="term_slug">term slug</option>
                    <option value="term_name">term name</option>
                </select>&nbsp;

                <select id="mdf_term_meta_tax" required="">
                    <option value="">Select taxonomy</option>
                    <?php if (!empty($taxonomies)): ?>
                        <?php foreach ($taxonomies as $tax): ?>
                            <option value="<?php echo esc_attr($tax->name) ?>"><?php echo esc_html($tax->label) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>&nbsp;
                <input type="text" value="" placeholder="enter meta field key" required="" id="mdf_term_meta_key" />&nbsp;
                <input type="submit" />
            </form>

        </li>
        <li>
            ---
        </li>
    </ol>

</div>
