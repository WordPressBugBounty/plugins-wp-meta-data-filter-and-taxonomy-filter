<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php if (!empty($filter['items'])): ?>

    <h3 class="mdf-section-title">
        <?php echo esc_html($filter['name']) ?>
        <div class="mdf-section-title-wrap">
            <a href="#" class="button mdf-section-title-button closed">+</a>
        </div>
    </h3>

    <?php 
    
    $backend_section_max_height = (int) get_post_meta($filter_post_id, 'backend_section_max_height', true); ?>
    <div style="display:none; <?php if ($backend_section_max_height > 0): ?>max-height: <?php echo esc_html($backend_section_max_height) ?>px; overflow: auto;<?php endif; ?>">
        <table class="form-table">
            <tbody>
                <?php foreach ($filter['items'] as $key => $item) : $uid = uniqid(); ?>
                    <?php
                 
                    $is_reflected = isset($item['is_reflected']) ? $item['is_reflected'] : 0;
                    $reflected_key = isset($item['reflected_key']) ? $item['reflected_key'] : '';
                    ?>
                    <?php if ($item['type'] == 'slider'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']); ?>

                                <?php
                                if (!isset($item['slider_range_value']))
                                {
                                    $item['slider_range_value'] = 0;
                                }
                                //***
                                if ($item['slider_range_value'] == 1)
                                {
                                    ?>
                                    <br /><i class="mdtf-fs11">(<?php esc_html_e("from/to", 'meta-data-filter') ?>)</i>
                                    <?php
                                }
                                ?>

                                <?php
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">';
									echo esc_html($key);
									echo '</i>';
                                }

                                //***
                                ?>
                            </th>
                            <td>


                                <?php
                                if (!isset($item['slider_range_value']))
                                {
                                    $item['slider_range_value'] = 0;
                                }
                                //***
                                if ($item['slider_range_value'] == 1):
                                    ?>
                                    <!-- double value slider slider -->

                                    <fieldset>
                                        <legend class="screen-reader-text"><span><?php esc_html_e($item['name']) ?></span></legend>
                                        <label for="<?php echo esc_attr($key) ?>">
                                            <?php list($min, $max) = explode('^', $item['slider']); ?>
                                            <?php
                                            $key_from = $key;
                                            $key_to = $key . '_to';
                                            $slider_value_from = floatval(get_post_meta($post_id, $key_from, true));
                                            $slider_value_to = floatval(get_post_meta($post_id, $key_to, true));
                                            ?>
                                            <input type="text" placeholder="<?php esc_html_e("from", 'meta-data-filter') ?>" id="<?php echo esc_attr($key_from) ?>" name="page_meta_data_filter[<?php echo esc_attr($key_from) ?>]" value="<?php echo esc_html($slider_value_from) ?>" />
                                            &nbsp;<input type="text" placeholder="<?php esc_html_e("to", 'meta-data-filter') ?>" id="<?php echo esc_attr($key_to) ?>" name="page_meta_data_filter[<?php echo esc_attr($key_to) ?>]" value="<?php echo esc_html($slider_value_to) ?>" />

                                            <br /><i><?php echo(esc_html__('min', 'meta-data-filter') . ' :' . $min . ', ' . esc_html__('max', 'meta-data-filter') . ': ' . $max) ?>. <?php esc_html_e($item['description']) ?></i>
                                        </label>
                                    </fieldset>

                                <?php else: ?>

                                    <!-- usual 1 value slider -->
                                    <fieldset>
                                        <legend class="screen-reader-text"><span><?php esc_html_e($item['name']) ?></span></legend>
                                        <label for="<?php echo esc_attr($key) ?>">
                                            <?php
                                            if(!empty( $item['slider'])){
                                                list($min, $max) = explode('^', $item['slider']);
                                            }else{
                                                $min=0;
                                                $max=100;
                                            }
                                             ?>
                                            <?php if (!$is_reflected): ?>
                                                <?php
                                                $slider_value = get_post_meta($post_id, $key, true);
                                                if (empty($slider_value))
                                                {
                                                    $slider_value = 0;
                                                }
                                                ?>
                                                <input type="text"  placeholder="<?php esc_html_e("example", 'meta-data-filter') ?>:<?php echo($min + 1) ?>" id="<?php echo esc_attr($key) ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_html($slider_value) ?>" />
                                            <?php else: ?>
                                                <?php
                                                if (!empty($reflected_key))
                                                {
                                                    echo get_post_meta($post_id, $reflected_key, TRUE);
                                                } else
                                                {
                                                    esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                                }
                                                ?>
                                            <?php endif; ?>
                                            <br /><i><?php echo esc_html__('min', 'meta-data-filter') . ' :';
											echo esc_html($min) ;
											echo ', ' . esc_html__('max', 'meta-data-filter') . ': ';
											echo esc_html($max);
											esc_html_e($item['description']) ?></i>
                                        </label>
                                    </fieldset>


                                <?php endif; ?>



                            </td>
                        </tr>

                    <?php endif; ?>


                    <?php if ($item['type'] == 'checkbox'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?>
                                <?php
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">' . $key . '</i>';
                                }
                                ?>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']); ?></span></legend>
                                    <?php if (!$is_reflected): ?>
                                        <?php
                                        $is_checked = (bool) get_post_meta($post_id, $key, TRUE);
                                        ?>
                                        <label for="<?php echo $key ?>">
                                            <input type="hidden" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                            <input type="checkbox" class="mdf_option_checkbox" id="<?php echo esc_attr($key) ?>" <?php if ($is_checked): ?>checked<?php endif; ?> />
                                            <i><?php esc_html_e($item['description']) ?></i>
                                        </label>
                                    <?php else: ?>
                                        <?php
                                        if (!empty($reflected_key))
                                        {
                                            $val = get_post_meta($post_id, $reflected_key, TRUE);
                                            ?>
                                            <input disabled type="checkbox" <?php if ($val): ?>checked<?php endif; ?> />
                                            <?php
                                        } else
                                        {
                                            esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                        }
                                        ?>
                                    <?php endif; ?>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>
                   <?php if ($item['type'] == 'label'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?>
                                <?php
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">' ;
									echo esc_attr($key); 
									echo '</i>';
                                }
                                ?>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']); ?></span></legend>
                                    <?php if (!$is_reflected): ?>
                                        <?php
                                        $is_checked = (bool) get_post_meta($post_id, $key, TRUE);
                                        ?>
                                        <label for="<?php echo esc_attr($key) ?>">
                                            <input type="hidden" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($is_checked) ?>">
                                            <input type="checkbox" class="mdf_option_checkbox" id="<?php echo esc_attr($key) ?>" <?php if ($is_checked): ?>checked<?php endif; ?> />
                                            <i><?php esc_html_e($item['description']) ?></i>
                                        </label>
                                    <?php else: ?>
                                        <?php
                                        if (!empty($reflected_key))
                                        {
                                            $val = get_post_meta($post_id, $reflected_key, TRUE);
                                            ?>
                                            <input disabled type="checkbox" <?php if ($val): ?>checked<?php endif; ?> />
                                            <?php
                                        } else
                                        {
                                            esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                        }
                                        ?>
                                    <?php endif; ?>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>

                    <?php if ($item['type'] == 'textinput'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?>
                                <?php
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">';
									echo esc_attr($key);
									echo '</i>';
                                }
                                ?>
                            </th>
                            <td>
                                <fieldset>
                                    <?php
                                    $text = get_post_meta($post_id, $key, TRUE);
                                    $target = 'self';
                                    $textinput_inback_display_as = 'textinput';
                                    if (isset($item['textinput_inback_display_as']))
                                    {
                                        $textinput_inback_display_as = $item['textinput_inback_display_as'];
                                    }
                                    if (isset($item['textinput_target']) AND ! empty($item['textinput_target']))
                                    {
                                        $target = $item['textinput_target'];
                                    }
                                    ?>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']); ?></span></legend>
                                    <?php if (!$is_reflected): ?>

                                        <?php if ($target == 'self'): ?>
                                            <?php if ($textinput_inback_display_as == 'textinput'): ?>
                                                <input type="text" class="widefat" placeholder="<?php echo $item['textinput'] ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_html($text) ?>" /><br />
                                            <?php endif; ?>

                                            <?php if ($textinput_inback_display_as == 'textarea'): ?>
                                                <textarea class="textarea widefat" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]"><?php echo esc_textarea($text) ?></textarea><br />
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($target == 'title'): ?>
                                            <?php if ($textinput_inback_display_as == 'textinput'): ?>
                                                <input type="text" readonly="" class="widefat" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo the_title() ?>" /><br />
                                            <?php endif; ?>

                                            <?php if ($textinput_inback_display_as == 'textarea'): ?>
                                                <textarea class="textarea widefat" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]"><?php echo the_title() ?></textarea><br />
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($target == 'title'): ?> <i>(<small><?php esc_html_e("title", 'meta-data-filter'); ?></small>)</i>&nbsp;<?php endif; ?>
                                        <i><?php esc_html_e($item['description']) ?></i>
                                    <?php else: ?>
                                        <?php
                                        if (!empty($reflected_key))
                                        {
                                            $val = get_post_meta($post_id, $reflected_key, TRUE);
                                            ?>
                                            <input type="text" class="wide" value="<?php echo esc_html($val) ?>" disabled="" />
                                            <?php
                                        } else
                                        {
                                            esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                        }
                                        ?>
                                    <?php endif; ?>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>


                    <?php if ($item['type'] == 'calendar'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?>
                                <?php
                                //wp_enqueue_style('jquery-ui-styles', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css');
								wp_enqueue_style('jquery-ui-styles', MetaDataFilterCore::get_application_uri() . '/css/jquery-ui-themes/themes/smoothness/jquery-ui.css');
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">';
									echo esc_html($key);
									echo '</i>';
                                }
                                ?>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']); ?></span></legend>
                                    <?php if (!$is_reflected): ?>
                                        <?php
                                        $from = get_post_meta($post_id, $key . '_from', TRUE);
                                        $to = get_post_meta($post_id, $key . '_to', TRUE);
                                        ?>
                                        <input type="hidden" name="page_meta_data_filter[<?php echo esc_attr($key) ?>_from]" value="<?php echo esc_html($from) ?>" />
                                        <input type="text" readonly="readonly" class="text_input mdf_calendar mdf_calendar_from" placeholder="<?php esc_html_e('from', 'meta-data-filter') ?>" />
                                        &nbsp;-&nbsp;
                                        <input type="hidden" name="page_meta_data_filter[<?php echo esc_attr($key) ?>_to]" value="<?php echo esc_html($to) ?>" />
                                        <input type="text" readonly="readonly" class="text_input mdf_calendar mdf_calendar_to" placeholder="<?php esc_html_e('to', 'meta-data-filter') ?>" /><br />
                                        <i><?php esc_html_e($item['description']) ?></i>
                                    <?php else: ?>
                                        <?php
                                        if (!empty($reflected_key))
                                        {
                                            $val = get_post_meta($post_id, $reflected_key, TRUE);
                                            ?>
                                            <input type="text" class="text_input" value="<?php echo date(get_option('date_format_custom'), $val) ?>" disabled="" />
                                            <?php
                                        } else
                                        {
                                            esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                        }
                                        ?>
                                    <?php endif; ?>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>


                    <?php
                  
  //renge_select
                    if ($item['type'] == 'range_select'): 
                        ?>                       
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']); ?>
                                <?php
                                if ($is_reflected)
                                {
                                    printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                } else
                                {
                                    echo '<br /><i class="mdtf-fs11">';
									echo esc_html($key);
									echo '</i>';
                                }

                                ?>
                            </th>
                            <td>


                                    <!-- usual 1 value slider -->
                                    <fieldset>
                                        <legend class="screen-reader-text"><span><?php esc_html_e($item['name']) ?></span></legend>
                                        <label for="<?php echo esc_attr($key) ?>">
                                            <?php 
                                            $range_tmp=array();
                                            $range_tmp= explode('^', $item['range_select']);
                                            $min=$max=0;
                                            if(!empty($range_tmp[0])){
                                                $min =$range_tmp[0];
                                            }
                                            if(isset($range_tmp[1]) AND !empty($range_tmp[1])){
                                                $max =$range_tmp[1];
                                            }
                                                    ?>
                                            <?php if (!$is_reflected): ?>
                                                <?php
                                                $range_select_value = get_post_meta($post_id, $key, true);
                                                if (empty($range_select_value))
                                                {
                                                    $range_select_value = 0;
                                                }
                                                ?>
                                                <input type="text"  placeholder="<?php esc_html_e("example", 'meta-data-filter') ?>:<?php echo esc_html($min + 1) ?>" id="<?php echo esc_attr($key) ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_html($range_select_value) ?>" />
                                            <?php else: ?>
                                                <?php
                                                if (!empty($reflected_key))
                                                {
                                                    echo get_post_meta($post_id, $reflected_key, TRUE);
                                                } else
                                                {
                                                    esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                                }
                                                ?>
                                            <?php endif; ?>
                                            <br /><i><?php
                                            if($min!=0 AND $max!=0){
                                             echo esc_html__('min', 'meta-data-filter') . ' :' ;
											 echo esc_html($min); 
											 echo ', ' . esc_html__('max', 'meta-data-filter') . ': ';
											 echo esc_html($max);
											 esc_html_e($item['description']) ;
                                            }
                                            ?></i>
                                        </label>
                                    </fieldset>

                            </td>
                        </tr>

                    <?php endif; 
  //renge_select
                    ?>


                            
                     <?php if ($item['type'] == 'select'): ?>
                        <?php if (!empty($item['select'])): ?>
                            <tr>
                                <th scope="row"><label for="<?php echo $key ?>"><?php esc_html_e($item['name']) ?>
                                        <?php
                                        if (!isset($item['select_range_value']))
                                        {
                                            $item['select_range_value'] = 0;
                                        }
                                        if ($item['select_range_value'] == 1)
                                        {
                                            echo '<br /><i class="mdtf-fs11">is range</i>';
                                        }
                                        //***
                                        if ($is_reflected)
                                        {
                                            printf('<br /><i class="mdtf-fs11">' . esc_html__('is reflected by %s', 'meta-data-filter') . '</i>', $reflected_key);
                                        } else
                                        {
                                            echo '<br /><i class="mdtf-fs11">' . $key . '</i>';
                                        }
                                        ?>
                                    </label></th>
                                <td>

                                    <?php if (!$is_reflected): ?>
                                        <?php if ($item['select_range_value'] == 1): ?>
                                            <?php $val = floatval(get_post_meta($post_id, $key, TRUE)); ?>
                                            <input type="text" class="widefat" placeholder="<?php esc_html_e('Enter value. Empty means zero.', 'meta-data-filter') ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" value="<?php echo esc_html($val) ?>" /><br />
                                        <?php else: ?>

                                            <?php
                                            $selected = get_post_meta($post_id, $key, TRUE);
                                            ?>
                                            <select name="page_meta_data_filter[<?php echo esc_attr($key) ?>]" id="<?php echo esc_attr($key) ?>">
                                                <option value="~"><?php esc_html_e('none', 'meta-data-filter') ?></option>
                                                <?php foreach ($item['select'] as $k => $value) : ?>
                                                    <?php
                                                    $option_value = /* sanitize_title */trim($value);
                                                    if (isset($item['select_key'][$k]) AND ! empty($item['select_key'][$k]))
                                                    {
                                                        $option_value = /* sanitize_title */trim($item['select_key'][$k]);
                                                    }
                                                    ?>
                                                    <option value="<?php echo esc_html($option_value); ?>" <?php echo($selected == $option_value ? 'selected' : '') ?>><?php esc_html_e($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php endif; ?>



                                    <?php else: ?>
                                        <?php
                                        if (!empty($reflected_key))
                                        {
                                            echo get_post_meta($post_id, $reflected_key, TRUE);
                                        } else
                                        {
                                            esc_html_e('key is empty, please check filter data!', 'meta-data-filter');
                                        }
                                        ?>
                                    <?php endif; ?>





                                    <i><?php esc_html_e($item['description']) ?></i>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                            
                            
                            
                    <?php if ($item['type'] == 'map'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?></th>
                            <td class="mdtf-p0">
                                <table class="mdtf-w100p">
                                    <tr>
                                        <td class="mdtf-w49p">
                                            <fieldset>
                                                <?php
                                                $latitude = get_post_meta($post_id, $key . '_latitude', TRUE);
                                                ?>
                                                <input type="text" class="widefat" placeholder="<?php esc_html_e('enter latitude', 'meta-data-filter'); ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>_latitude]" value="<?php echo esc_html($latitude) ?>" /><br />
                                            </fieldset>
                                        </td>
                                        <td class="mdtf-w49p">
                                            <fieldset>
                                                <?php
                                                $longitude = get_post_meta($post_id, $key . '_longitude', TRUE);
                                                ?>
                                                <input type="text" class="widefat" placeholder="<?php esc_html_e('enter longitude', 'meta-data-filter'); ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>_longitude]" value="<?php echo esc_html($longitude) ?>" /><br />
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <fieldset>
                                                <?php
                                                $desc = get_post_meta($post_id, $key . '_desc', TRUE);
                                                ?>
                                                <input type="text" class="widefat" placeholder="<?php esc_html_e('enter pin description', 'meta-data-filter'); ?>" name="page_meta_data_filter[<?php echo esc_attr($key) ?>_desc]" value="<?php echo esc_html($desc) ?>" /><br />
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    <?php endif; ?>


                    <?php if ($item['type'] == 'taxonomy'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']) ?></span></legend>
                                    <label for="<?php echo esc_attr($key) ?>">

                                        <?php esc_html_e('Taxonomy block is placed here', 'meta-data-filter') ?>

                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>
                        <?php if ($item['type'] == 'by_author'): ?>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e($item['name']) ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php esc_html_e($item['name']) ?></span></legend>
                                    <label for="<?php echo esc_attr($key) ?>">&nbsp;<?php  esc_html_e('By author', 'meta-data-filter') ?>&nbsp;</label>
                                </fieldset>
                            </td>
                        </tr>

                    <?php endif; ?>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>