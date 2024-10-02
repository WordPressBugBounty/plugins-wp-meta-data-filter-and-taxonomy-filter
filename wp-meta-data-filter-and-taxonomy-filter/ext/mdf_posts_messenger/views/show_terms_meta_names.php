<?php
// for taxonomies
if(isset($taxonomy)){  
    foreach ($taxonomy as $tax_type){
        $label="";
        $terms="";
        foreach ($tax_type as $key=>$val){
            if(empty($val) OR $val==-1){
                continue;
            }
            $tax=get_taxonomy($key);
                 if(isset($tax->labels->name) AND !empty($tax->labels->name)){
                     $label = $tax->labels->name;
                 }else{
                   $label= $tax->label;
                 }
                 if(is_array($val)){
                     $name=array();
                     foreach($val as $term_id){
                         $term=get_term_by( 'id',$term_id , $key );                         
                         if(is_object($term)){
                                $name[]= $term->name;
                         }

                     }
                     if(!empty($name)){
                         $terms.= implode(",",$name);
                     }
                     
                 }else{
                      $terms.= get_term_by( 'id',$val , $key )->name;
                 }
                 if(empty($terms)){
                     continue;
                 }
                ?>
            <span> <?php printf("%s: %s",$label, $terms )  ?>  </span> <?php
            $terms="";
        }
    }
}
// For meta data
foreach($filter_post_blocks as $id){
    foreach (MetaDataFilterPage::get_html_items($id) as $key=>$val){
        if(isset(${$key})){

            switch ($val['type']){
                case "checkbox":
                    ?>
                     <span><?php echo esc_html($val['name']) ?></span>
                    <?php
                    break;
                case "label":
                    ?>
                     <span><?php echo esc_html($val['name']) ?></span>
                    <?php
                    break;
                case "calendar":
                    $from="";
                    $to="";
                     if(!empty(${$key}['from'])){
                        $from= esc_html__("From:",'meta-data-filter'). date("d.m.y",(int)${$key}['from']);
                       
                     }
                     if(!empty(${$key}['to'])){
                        $to= esc_html__("to:",'meta-data-filter'). date("d.m.y",(int)${$key}['to']);
                     }
                     if(empty($from) AND empty($to)){
                         break;
                     }
                    ?>             
                     <span><?php printf("%s- %s  %s",$val['name'],$from,$to)  ?></span>
                    <?php
                     
                    break;
                    case "range_select":
                    $from="";
                    $to="";
                     if(!empty(${$key}['from']) OR ${$key}['from']==0 ){
                        $from= esc_html__("From:",'meta-data-filter').$val["range_select_prefix"].${$key}['from'].$val["range_select_postfix"];
                     }
                     if(!empty(${$key}['to'])){
                        $to= esc_html__("to:",'meta-data-filter').$val["range_select_prefix"].${$key}['to'].$val["range_select_postfix"];
                     }
                
                     if(empty($from) AND empty($to)){
                         break;
                     }
                    ?>             
                     <span><?php printf("%s- %s  %s",$val['name'],$from,$to)  ?></span>
                    <?php
                     
                    break;
                   
                    case "by_author":
                        if(empty(${$key}) OR ${$key}==-1){
                            break;
                        } 
                        $author=get_userdata(${$key});
                       
                        $user_name= $author->get('user_nicename');
                        if(!empty($user_name)){
                            ?>             
                             <span><?php printf(esc_html__("Author: %s",'meta-data-filter'),$user_name)  ?></span>
                            <?php
                        }

                    break;
                    case 'slider':
                        if(empty(${$key})){
                            break;
                        } 
                    $range=array();    
                    $range=explode("^",${$key});
                    $from="";
                    $to="";
                    if(count($range)>1 AND( !empty($range[1])AND $range[1]!=0 )  ){
                            $from=sprintf(esc_html__("From:%s %s %s",'meta-data-filter'),$val["slider_prefix"],$range[0],$val["slider_postfix"]); 
                            $to=sprintf(esc_html__("to:%s %s %s",'meta-data-filter'),$val["slider_prefix"], $range[1],$val["slider_postfix"]);
                  
                    }else{
                        $from=$val["slider_prefix"].$range[0].$val["slider_postfix"];
                    }
                  
                     if(empty($from) AND empty($to)){ 
                         break;
                     }
                    ?>             
                     <span><?php printf("%s: %s  %s",$val['name'],$from,$to)  ?></span>
                    <?php
                    
                    break;
                    case"textinput":
                        if(empty(${$key})){
                            break;
                        } 
                        ?>
                        <span><?php printf("%s: \"%s\"",$val['name'],${$key})  ?></span>
                        <?php
                        break;
                    default:
			?>
                        <span><?php printf("%s: %s",$val['name'],${$key})  ?></span>
                       <?php
                    break;
            }
            
        }

    }
}