<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

$checked_data = array();
$name = array();
if (isset($filter_data['taxonomy'])) {

    foreach ($filter_data['taxonomy'] as $html_item => $tax_type) {

        foreach ($tax_type as $key => $val) {
            if (empty($val)) {
                continue;
            }
            if (is_array($val)) {

                foreach ($val as $term_id) {
                    if ($term_id < 1) {
                        continue;
                    }
                    $tax_name = get_term_by('id', $term_id, $key)->name;
                    if ($tax_name) {
                        $select = "";
                        if ($html_item == "checkbox" OR $html_item == "multi_select") {
                            $select = "input[value='" . $term_id . "']";
                        } elseif ($html_item == "select") {
                            $select = "select option[value='" . $term_id . "']";
                        }
                        $checked_data[] = array(
                            'name' => $tax_name,
                            'type' => 'taxonomy',
                            'slug' => $key,
                            'id' => $term_id,
                            'html_item' => $html_item,
                            'text_select' => $select,
                        );
                    }
                }
            } elseif ($val != -1) {
                $tax_name = get_term_by('id', $val, $key)->name;
                if ($tax_name) {
                    $select = "";
                    if ($html_item == "checkbox" OR $html_item == "multi_select") {
                        $select = "input[value='" . $term_id . "']";
                    } elseif ($html_item == "select") {
                        $select = "select option[value='" . $term_id . "']";
                    }

                    $checked_data[] = array(
                        'name' => $tax_name,
                        'type' => 'taxonomy',
                        'slug' => $key,
                        'id' => $val,
                        'html_item' => $html_item,
                    );
                }
            }
        }
    }
}

if (!$filter_data OR!is_array($filter_data['filter_post_blocks'])) {
    $filter_data['filter_post_blocks'] = array();
}
foreach ($filter_data['filter_post_blocks'] as $id) {
    $html_items = MetaDataFilterPage::get_html_items($id);
    if (!is_array($html_items)) {
        $html_items = array();
    }
    foreach ($html_items as $key => $val) {

        if (isset($filter_data[$key])) {

            switch ($val['type']) {
                case "checkbox":
                    $checked_data[] = array(
                        'name' => $val['name'],
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "input[name='mdf[" . $key . "]']"
                    );

                    break;
                case "label":
                    $checked_data[] = array(
                        'name' => $val['name'],
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => 'checkbox',
                        'text_select' => "input[name='mdf[" . $key . "]']"
                    );

                    break;
                case "calendar":
                    if (empty($filter_data[$key]['from']) AND empty($filter_data[$key]['to'])) {
                        break;
                    }
                    $checked_data[] = array(
                        'name' => "By date ",
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "input[name='mdf[" . $key . "][from]']"
                    );

                    break;
                case "by_author":
                    if (empty($filter_data[$key]) OR $filter_data[$key] == -1) {
                        break;
                    }
                    $checked_data[] = array(
                        'name' => "By author ",
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "select[name='mdf[" . $key . "]']"
                    );

                    break;
                case 'slider':
                    if (empty($filter_data[$key])) {
                        break;
                    }

                    $range = array();
                    if (empty($filter_data[$key]) OR!is_string($filter_data[$key])) {
                        $filter_data[$key] = "1^100";
                    }
                    $range = explode("^", $filter_data[$key]);
                    if (!empty($val['slider'])) {
                        if ($val['slider'] == $filter_data[$key]) {
                            break;
                        }
                    }
                    $from = "";
                    $to = "";
                    if (count($range) > 1 AND (!empty($range[1]) )) {
                        $from = sprintf(esc_html__("From:%s %s %s", 'meta-data-filter'), $val["slider_prefix"], $range[0], $val["slider_postfix"]);
                        $to = sprintf(esc_html__("to:%s %s %s", 'meta-data-filter'), $val["slider_prefix"], $range[1], $val["slider_postfix"]);
                    } else {
                        $from = $val["slider_prefix"] . $range[0] . $val["slider_postfix"];
                    }

                    if (empty($from) AND empty($to)) {
                        break;
                    }
                    $checked_data[] = array(
                        'name' => sprintf("%s: %s  %s", $val['name'], $from, $to),
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "input[name='mdf[" . $key . "]']"
                    );

                    break;
                case"textinput":
                    if (empty($filter_data[$key])) {
                        break;
                    }
                    $checked_data[] = array(
                        'name' => "By text",
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "input[name='mdf[" . $key . "]']"
                    );
                    break;
                case "range_select":
                    $from = "";
                    $to = "";

                    // if you want hide rang sellect without changes 
                    if (!empty($val['range_select'])) {
                        $range = array();
                        $range = explode("^", $val['range_select']);
                        if ($filter_data[$key]['from'] == $range[0] AND $filter_data[$key]['to'] == $range[1]) {
                            break;
                        }
                    }



                    if (!empty($filter_data[$key]['from'])) {
                        $from = esc_html__("From:", 'meta-data-filter') . $val["range_select_prefix"] . $filter_data[$key]['from'] . $val["range_select_postfix"];
                    }
                    if (!empty($filter_data[$key]['to'])) {
                        $to = esc_html__("to:", 'meta-data-filter') . $val["range_select_prefix"] . $filter_data[$key]['to'] . $val["range_select_postfix"];
                    }
                    if (empty($from) AND empty($to)) {
                        break;
                    }
                    $checked_data[] = array(
                        'name' => sprintf("%s- %s  %s", $val['name'], $from, $to),
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "select[name='mdf[" . $key . "][from]']"
                    );

                    break;
                case "select":
                    $label = "";
                    $index_res = array_search($filter_data[$key], $val['select_key'], true);
                    if ($index_res) {
                        $label = $val['select'][$index_res];
                    } else {
                        $label = $filter_data[$key];
                    }
                    $checked_data[] = array(
                        'name' => sprintf("%s: %s", $val['name'], $label),
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "select[name='mdf[" . $key . "]']"
                    );

                    break;
                default :
                    $checked_data[] = array(
                        'name' => $filter_data[$key],
                        'type' => 'meta_data',
                        'key' => $key,
                        'html_item' => $val['type'],
                        'text_select' => "input[name='mdf[" . $key . "]']"
                    );
                    break;
            }
        }
    }
}
?>
<div class="mdf_panel_container">
    <?php
    foreach ($checked_data as $item) {
        $data_select = "";
        if (isset($item['text_select'])) {
            $data_select = $item['text_select'];
        }

        if (empty($item)) {
            continue;
        }
        ?>
        <div class="mdf_terms_panel"> <?php echo esc_html($item['name']) ?>
            <a class="mdf_panel_remove">
                <span class="mdf_remove_icon" data-type="<?php echo esc_attr($item['type']) ?>" data-html="<?php echo esc_html($item['html_item']) ?>" data-select="<?php echo esc_html($data_select) ?>">
                    <img src="<?php echo MetaDataFilter::get_application_uri() ?>/images/failmark.png">
                </span>
            </a>
        </div>
        <?php
    }
    ?>
</div>