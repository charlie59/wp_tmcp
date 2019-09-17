<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_option_items($arr, $saved_value) {
   // make sure it is an array, otherwise just go back
   if (!is_array($arr)) return;

   $output = "";
   foreach($arr as $key => $item) {
      $selected ='';
      if (is_array($saved_value)) {
         in_array($key,$saved_value) ? $selected = "selected='selected' " : $selected = '';
      } else {
         "$key" == $saved_value ? $selected = "selected='selected' " : $selected = '';
      }
      $output .= "<option value='$key' $selected >$item</option>\n";
   } 
   echo $output;
}

function eme_checkbox_items($name, $arr, $saved_values, $horizontal = true) { 
   // make sure it is an array, otherwise just go back
   if (!is_array($arr)) return;

   $output = "";
   foreach($arr as $key => $item) {
      $checked = "";
      if (in_array($key, $saved_values))
         $checked = "checked='checked'";
      $output .=  "<input type='checkbox' name='$name' value='$key' $checked /> $item ";
      if(!$horizontal)  
         $output .= "<br />\n";
   }
   echo $output;
   
}

function eme_options_input_text($title, $name, $description, $type="text", $value=false) {
   if (!$value)
      $value= eme_nl2br(get_option($name));
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'events-made-easy') ?></th>
       <td>
         <input name="<?php echo $name; ?>" type="<?php echo $type; ?>" id="<?php echo $name; ?>" style="width: 95%" value="<?php echo eme_esc_html($value); ?>" size="45" />
                  <?php if (!empty($description)) echo "<br />".$description; ?>
       </td>
   </tr>
   <?php
}

function eme_options_input_int($title, $name, $description, $type="text", $value=false) {
   if (!$value)
      $value= intval(get_option($name));
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'events-made-easy') ?></th>
       <td>
         <input name="<?php echo $name; ?>" type="<?php echo $type; ?>" id="<?php echo $name; ?>" style="width: 95%" value="<?php echo eme_esc_html($value); ?>" size="45" />
                  <?php if (!empty($description)) echo "<br />".$description; ?>
       </td>
   </tr>
   <?php
}

function eme_options_input_password($title, $name, $description) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'events-made-easy') ?></th>
       <td>
         <input name="<?php echo $name; ?>" type="password" id="<?php echo $name; ?>" style="width: 95%" value="<?php echo get_option($name); ?>" size="45" />
                  <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
      </tr>
   <?php
}

function eme_options_textarea($title, $name, $description, $show_wp_editor=0, $show_full=0) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
   <th scope="row"><?php _e($title,'events-made-easy')?></th>
   <td>
   <?php if ($show_wp_editor) {
      if ($show_full)
         $eme_editor_settings = eme_get_editor_settings(true);
      else
         $eme_editor_settings = eme_get_editor_settings();
      wp_editor(get_option($name),$name,$eme_editor_settings);
   } else { ?>
      <textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" rows="6" style="width: 95%"><?php echo eme_esc_html(get_option($name));?></textarea>
   <?php } ?>
   <?php if (!empty($description)) echo "<br />".$description; ?>
   </td>
   </tr>
   <?php
}

function eme_options_radio_binary($title, $name, $description, $option_value=false) {
      if (!$option_value)
         $option_value = get_option($name);
      if ($name == "eme_permalink_events_prefix" || $name == "eme_permalink_locations_prefix") {
         $option_value = eme_permalink_convert($option_value);
      }
?>
       
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'events-made-easy'); ?></th>
            <td>
            <input id="<?php echo $name; ?>_yes" name="<?php echo $name; ?>" type="radio" value="1" <?php if($option_value) echo "checked='checked'"; ?> /><?php _e('Yes'); ?> <br />
            <input  id="<?php echo $name; ?>_no" name="<?php echo $name; ?>" type="radio" value="0" <?php if(!$option_value) echo "checked='checked'"; ?> /><?php _e('No'); ?>
            <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
         </tr>
<?php 
}

function eme_options_select($title, $name, $list, $description, $option_value=false) {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if (!$option_value)
      $option_value = get_option($name);
?>
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'events-made-easy'); ?></th>
            <td>
            <select name="<?php echo $name; ?>" > 
               <?php
                 foreach($list as $key => $value) {
                    "$key" == $option_value ? $selected = "selected='selected' " : $selected = '';
                    echo "<option value='$key' $selected>$value</option>";
                 }
               ?>
            </select>
            <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
         </tr>
<?php 
}

function eme_ui_select_binary ($option_value, $name, $required=0, $class="", $extra_attributes='') {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";
   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "<select $class_att $required_att name='$name' id='$name' $extra_attributes >";
   $selected_YES="";
   $selected_NO="";
   if ($option_value)
      $selected_YES = "selected='selected'";
   else
      $selected_NO = "selected='selected'";
   $val.= "<option value='0' $selected_NO>".__('No')."</option>";
   $val.= "<option value='1' $selected_YES>".__('Yes')."</option>";
   $val.=" </select>";
   return $val;
}

function eme_ui_select($option_value, $name, $list, $add_empty_first='', $required=0,$class="", $extra_attributes='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";
   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "<select $class_att $required_att id='$name' name='$name' $extra_attributes >";
   if (!empty($add_empty_first)) {
      $val.= "<option value=''>$add_empty_first</option>";
   }
   foreach($list as $key => $value) {
      if (is_array($value)) {
         $t_key=$value[0];
         $t_value=eme_esc_html($value[1]);
      } else {
         $t_key=$key;
         $t_value=eme_esc_html($value);
      }
      if (empty($t_value)) $t_value='&nbsp;';
      "$t_key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      $val.= "<option value='".eme_esc_html($t_key)."' $selected>$t_value</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_select_key_value($option_value, $name, $list, $key, $value, $add_empty_first='', $required=0,$class='', $extra_attributes='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";
   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "<select $class_att $required_att id='$name' name='$name' $extra_attributes >";
   if (!empty($add_empty_first)) {
      $val.= "<option value=''>".eme_esc_html($add_empty_first)."</option>";
   }
   foreach($list as $line) {
      $t_key=$line[$key];
      $t_value=eme_esc_html($line[$value]);
      if (empty($t_value)) $t_value='&nbsp;';
      "$t_key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      $val.= "<option value='".eme_esc_html($t_key)."' $selected>$t_value</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_multiselect($option_value, $name, $list, $size=5, $required=0, $class='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "<select $required_att $class_att multiple='multiple' name='${name}[]' id='${name}' size='$size'>";
   foreach($list as $key => $value) {
      if (is_array($value)) {
         $t_key=$value[0];
         $t_value=eme_esc_html($value[1]);
      } else {
         $t_key=$key;
         $t_value=eme_esc_html($value);
      }
      if (is_array($option_value)) {
         in_array($t_key,$option_value) ? $selected = "selected='selected' " : $selected = '';
      } else {
         "$t_key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      }
      $val.= "<option value='".eme_esc_html($t_key)."' $selected>$t_value</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_multiselect_key_value($option_value, $name, $list, $key, $value, $add_empty_first='', $size=3, $required=0, $class='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "<select $required_att $class_att multiple='multiple' name='${name}[]' id='${name}' size='$size'>";
   if (!empty($add_empty_first)) {
      $val.= "<option value=''>".eme_esc_html($add_empty_first)."</option>";
   }
   foreach($list as $line) {
      $t_key=$line[$key];
      $t_value=eme_esc_html($line[$value]);
      if (is_array($option_value)) {
         in_array($t_key,$option_value) ? $selected = "selected='selected' " : $selected = '';
      } else {
         "$t_key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      }
      if (empty($t_value)) $t_value='&nbsp;';
      $val.= "<option value='".eme_esc_html($t_key)."' $selected>$t_value</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_radio($option_value, $name, $list,$horizontal = true, $required=0, $class='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "";
   $counter=0;
   foreach($list as $key => $value) {
      if (is_array($value)) {
         $t_key=$value[0];
         $t_value=$value[1];
      } else {
         $t_key=$key;
         $t_value=$value;
      }
      "$t_key" == $option_value ? $selected = "checked='checked' " : $selected = '';
      $val.= "<input $required_att type='radio' id='${name}_${counter}' name='$name' $class_att value='".eme_esc_html($t_key)."' $selected /><label for='${name}_${counter}'>".eme_esc_html($t_value)."</label>";
      if(!$horizontal)  
         $val .= "<br />\n";
      $counter++;
   }
   return $val;
}

function eme_ui_checkbox($option_value, $name, $list, $horizontal = true, $required=0, $class='') {
   // make sure it is an array, otherwise just go back
   if (!is_array($list)) return;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   if ($class)
      $class_att="class='$class'";
   else
      $class_att="";

   $val = "";
   $counter=0;
   foreach($list as $key => $value) {
      if (is_array($option_value)) {
         in_array($key,$option_value) ? $selected = "checked='checked' " : $selected = '';
      } else {
         "$key" == $option_value ? $selected = "checked='checked' " : $selected = '';
      }
      $val.= "<input $required_att type='checkbox' name='${name}[]' id='${name}_${counter}' $class_att value='".eme_esc_html($key)."' $selected /><label for='${name}_${counter}'>".eme_esc_html($value)."</label>";
      if(!$horizontal)  
         $val .= "<br />\n";
      $counter++;
   }
   return $val;
}

function eme_ui_use_checkbox($option_value, $name, $label) {
   $val = "";
   $counter=0;
   "1" == $option_value ? $selected = "checked='checked' " : $selected = '';
   $val.= "<input type='checkbox' name='${name}' id='${name}' value='1' $selected /><label for='${name}'>".eme_esc_html($label)."</label>";
   return $val;
}

?>
