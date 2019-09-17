<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_formfield() {
   $formfield = array(
      'field_type' => 'text',
      'field_name' => '',
      'field_values' => '',
      'field_tags' => '',
      'admin_values' => '',
      'admin_tags' => '',
      'field_attributes' => '',
      'field_purpose' => '',
      'field_condition' => '',
      'field_required' => 0,
      'extra_charge' => 0
   );
   return $formfield;
}

function eme_formfields_page() {      
   global $wpdb;
   
   if (!current_user_can( get_option('eme_cap_forms')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update form fields!','events-made-easy');
      eme_formfields_table_layout($message);
      return;
   }
   
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_formfield") { 
      check_admin_referer('eme_formfields','eme_admin_nonce');
      // edit formfield  
      $field_id = intval($_GET['field_id']);
      eme_formfields_edit_layout($field_id);
      return;
   }

   if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_formfield") {
      check_admin_referer('eme_formfields','eme_admin_nonce');
      eme_formfields_edit_layout();
      return;
   }

   // Insert/Update/Delete Record
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME;
   $validation_result = '';
   $message = '';
   if (isset($_POST['eme_admin_action'])) {
      check_admin_referer('eme_formfields','eme_admin_nonce');
      if ($_POST['eme_admin_action'] == "do_editformfield") {
         $formfield = array();
         $field_id = intval($_POST['field_id']);
         $formfield['field_name'] = trim(stripslashes($_POST['field_name']));
         $formfield['field_type'] = trim(eme_esc_html($_POST['field_type']));
         $formfield['extra_charge'] = intval($_POST['extra_charge']);
         $formfield['field_required'] = intval($_POST['field_required']);
         $formfield['field_values'] = trim(stripslashes($_POST['field_values']));
         $formfield['field_tags'] = trim(stripslashes($_POST['field_tags']));
         $formfield['admin_values'] = trim(stripslashes($_POST['admin_values']));
         $formfield['admin_tags'] = trim(stripslashes($_POST['admin_tags']));
         $formfield['field_attributes'] = trim(stripslashes($_POST['field_attributes']));
         $formfield['field_purpose'] = trim(stripslashes($_POST['field_purpose']));
         $formfield['field_condition'] = trim(stripslashes($_POST['field_condition']));
         if (eme_is_multifield($formfield['field_type']) && empty($formfield['field_values'])) {
            $message = __('Error: the field value can not be empty for this type of field.','events-made-easy');
	    if ($field_id>0) {
		    eme_formfields_edit_layout($field_id,$message);
		    return;
	    }
         } elseif (eme_is_multifield($formfield['field_type']) &&
               eme_is_multi($formfield['field_values']) && !empty($formfield['field_tags']) && 
               count(eme_convert_multi2array($formfield['field_values'])) != count(eme_convert_multi2array($formfield['field_tags']))) {
            $message = __('Error: if you specify field tags, there need to be exact the same amount of tags as values.','events-made-easy');
	    if ($field_id>0) {
		    eme_formfields_edit_layout($field_id,$message);
		    return;
	    }
         } elseif (eme_is_multifield($formfield['field_type']) &&
               eme_is_multi($formfield['admin_values']) && !empty($formfield['admin_tags']) && 
               count(eme_convert_multi2array($formfield['admin_values'])) != count(eme_convert_multi2array($formfield['admin_tags']))) {
            $message = __('Error: if you specify field tags, there need to be exact the same amount of tags as values.','events-made-easy');
	    if ($field_id>0) {
		    eme_formfields_edit_layout($field_id,$message);
		    return;
	    }
         }
	 if ($field_id>0) {
            $validation_result = $wpdb->update( $formfields_table, $formfield, array('field_id' => $field_id) );
            if ($validation_result !== false )
               $message = __("Successfully edited the field", 'events-made-easy');
	    else
               $message = __("There was a problem editing the field", 'events-made-easy');
	 } else {
            $validation_result = $wpdb->insert( $formfields_table, $formfield );
            if ($validation_result !== false )
               $message = __("Successfully added the field", 'events-made-easy');
	    else
               $message = __("There was a problem adding the field", 'events-made-easy');
         }
      } elseif ($_POST['eme_admin_action'] == "do_deleteformfield" && isset($_POST['formfields'])) {
         // Delete formfield or multiple
         $formfields = $_POST['formfields'];
         if (!empty($formfields) && eme_array_integers($formfields)) {
               $validation_result = $wpdb->query( "DELETE FROM $formfields_table WHERE field_id IN (". implode(",", $formfields).")" );
               if ($validation_result !== false ) {
                  $message = __("Successfully deleted the field(s)", 'events-made-easy');
		  $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
                  $wpdb->query( "DELETE FROM $answers_table WHERE field_id IN (". implode(",", $formfields).")" );
	       } else {
                  $message = __("There was a problem deleting the selected field(s), please try again.",'events-made-easy');
	       }
         } else {
            $message = __("Couldn't delete the field(s). Incorrect IDs supplied. Please try again.",'events-made-easy');
         }
      }
   }

   eme_formfields_table_layout($message);
} 

function eme_formfields_table_layout($message="") {
   global $plugin_page;
   $field_types = eme_get_fieldtypes();
   array_unshift($field_types,__('Any','events-made-easy'));
   $nonce_field = wp_nonce_field('eme_formfields','eme_admin_nonce',false,false);
   $destination = admin_url("admin.php?page=$plugin_page"); 
   if (empty($message))
           $hidden_style="display:none;";
   else
           $hidden_style="";
?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <div id="formfields-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Form fields', 'events-made-easy') ?></h1>

   <div class="wrap">
         <form id="formfields-new" method="post" action="<?php echo $destination; ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_formfield" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add field', 'events-made-easy');?>" />
         </form>
   </div>
   <br /><br />
   <form action="#" method="post">
   <?php echo eme_ui_select('','search_type',$field_types); ?>
   <button id="FormfieldsLoadRecordsButton" class="button-secondary action"><?php _e('Filter fields','events-made-easy'); ?></button>
   </form>

   <form id='formfields-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteFormfields"><?php _e ( 'Delete selected fields','events-made-easy'); ?></option>
   </select>
   <button id="FormfieldsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="FormfieldsTableContainer"></div>
   </form>
   </div>
   </div>
   <?php
}

function eme_formfields_edit_layout($field_id=0,$message = "") {
   global $plugin_page;

   $formfield = eme_get_formfield($field_id);
   $field_types = eme_get_fieldtypes();
   $fieldpurposes = eme_get_fieldpurpose();
   $groups=eme_get_groups();
   $peoplefieldconditions=array();
   $peoplefieldconditions['group:0']='';
   foreach ($groups as $group) {
           $peoplefieldconditions['group:'.$group['group_id']]=$group['name'];
   }

   $nonce_field = wp_nonce_field('eme_formfields','eme_admin_nonce',false,false);
   if($field_id>0) {
   	   $used = eme_check_used_formfield($field_id);
           $formfield = eme_get_formfield($field_id);
           $h1_string=__('Edit field', 'events-made-easy');
           $action_string=__('Update field', 'events-made-easy');
   } else {
	   $used = 0;
           $formfield = eme_new_formfield();
           $h1_string=__('Create field', 'events-made-easy');
           $action_string=__('Add field', 'events-made-easy');
   }
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h1>".$h1_string."</h1>";   
      
   if($message != "") {
      $layout .= "
      <div id='message' class='updated fade below-h1' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
   }

   if ($used) {
	   $layout .= "
      <div id='eme_formfield_warning' class='updated below-h1' style='background-color: rgb(255, 251, 204);'>
         <p>".__('Warning: this field is already used in replies. Changing the field type or values  might result in unwanted side effects.', 'events-made-easy')."</p>
      </div>";
   }

   $layout .= "
      <div id='ajax-response'></div>

      <form name='edit_formfield' id='edit_formfield' method='post' action='".admin_url("admin.php?page=$plugin_page")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='do_editformfield' />
      $nonce_field
      <input type='hidden' name='field_id' value='".$field_id."' />
      
      <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_name'>".__('Field name', 'events-made-easy')."</label></th>
               <td><input name='field_name' id='field_name' type='text' value='".eme_esc_html($formfield['field_name'])."' size='40' /></td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_type'>".__('Field type', 'events-made-easy')."</label></th>
               <td>".eme_ui_select($formfield['field_type'],"field_type",$field_types)."
                    <br />".__("For the type 'Date (JS)' you can optionally enter a custom date format in 'HTML Field attributes' to be used when the field is shown.",'events-made-easy') ."
               </td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_purpose'>".__('Field purpose', 'events-made-easy')."</label></th>
	       <td>".eme_ui_select($formfield['field_purpose'],"field_purpose",$fieldpurposes)."
                    <br />".__("If you select 'People field' or 'Member field', this field will show up as an extra column in the overview table for people or members.",'events-made-easy') ."
                    <br />".__("If you select 'People field' you can add a condition to this field, meaning that if the person is in the group you selected in the condition, this is an extra field that will then be available to fill out for that person. This allows you to put people in e.g. a Volunteer group and then ask for more volunteer info.",'events-made-easy') ."
               </td>
            </tr>
            <tr id='tr_field_condition' class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_condition'>".__('Field condition', 'events-made-easy')."</label></th>
               <td>".eme_ui_select($formfield['field_condition'],"field_condition",$peoplefieldconditions)."
                   <br />".__('Only show this field if the person is member of the selected group. Leave empty to add this field to all people.','events-made-easy')."
               </td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_required'>".__('Required field', 'events-made-easy')."</label></th>
	       <td>". eme_ui_select_binary($formfield['field_required'],"field_required") ."
                  <br />".__('Use this if the field is required to be filled out.','events-made-easy')."
                  <br />".__('This overrides the use of "#REQ" when defining a field in a format.','events-made-easy')."
            </tr>
            <tr id='tr_extra_charge' class='form-field form-required'>
               <th scope='row' valign='top'><label for='extra_charge'>".__('Extra charge', 'events-made-easy')."</label></th>
	       <td>". eme_ui_select_binary($formfield['extra_charge'],"extra_charge") ."
                  <br />".__('Use this if the field indicates an extra charge to the total price','events-made-easy')."
                  <br />".__('This is only really useful for multivalue fields (like e.g. dropdown), in which case the field values should indicate the price for that selection (and the price needs to be unique).','events-made-easy')."
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_values'>".__('Field values', 'events-made-easy')."</label></th>
               <td><input name='field_values' id='field_values' type='text' value='".eme_esc_html($formfield['field_values'])."' size='40' />
                  <br />".__('Tip: for multivalue field types (like Drop Down), use "||" to separate the different values (e.g.: a1||a2||a3)','events-made-easy')."
               </td>
            </tr>
            <tr id='tr_field_tags' class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_tags'>".__('Field tags', 'events-made-easy')."</label></th>
               <td><input name='field_tags' id='field_tags' type='text' value='".eme_esc_html($formfield['field_tags'])."' size='40' />
                  <br />".__('For multivalue fields, you can here enter the "visible" tags people will see. If left empty, the field values will be used. Use "||" to separate the different tags (e.g.: a1||a2||a3)','events-made-easy')."
               </td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='admin_values'>".__('Admin Field values', 'events-made-easy')."</label></th>
               <td><input name='admin_values' id='admin_values' type='text' value='".eme_esc_html($formfield['admin_values'])."' size='40' />
                  <br />".__('If you want a bigger number of choices for e.g. dropdown fields in the admin interface, enter the possible values here','events-made-easy')."
               </td>
            </tr>
            <tr id='tr_admin_tags' class='form-field form-required'>
               <th scope='row' valign='top'><label for='admin_tags'>".__('Admin Field tags', 'events-made-easy')."</label></th>
               <td><input name='admin_tags' id='admin_tags' type='text' value='".eme_esc_html($formfield['admin_tags'])."' size='40' />
                  <br />".__('If you want a bigger number of choices for e.g. dropdown fields in the admin interface, enter the possible tags here','events-made-easy')."
               </td>
            </tr>
            <tr id='tr_field_attributes' class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_attributes'>".__('HTML field attributes', 'events-made-easy')."</label></th>
               <td><input name='field_attributes' id='field_attributes' type='text' value='".eme_esc_html($formfield['field_attributes'])."' size='40' />
                   <br />".__('Here you can specify extra html attributes for your field (like size, maxlength, pattern, ...','events-made-easy')."
                   <br />".__('For the Date(JS) fieldtype, enter a valid PHP-format of the date you like to see when entering/showing the value (unrecognized characters in the format will cause the result to be empty). If left empty, the wordpress settings for date format will be used.','events-made-easy')."
               </td>
            </tr>
      </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".$action_string."' /></p>
      </form>
         
   </div>
   <p>".__('For more information about form fields, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'events-made-easy')."</a></p>
   ";  
   echo $layout;
}

function eme_get_dyndata_conditions() {
        $data=array(
                'eq' => __('equal to','events-made-easy'),
                'lt' => __('lower than','events-made-easy'),
                'gt' => __('greater than','events-made-easy'),
                'ge' => __('greater than or equal to','events-made-easy'),
        );

        return $data;
}

function eme_get_used_formfield_ids(){
   global $wpdb;
   $table = $wpdb->prefix.ANSWERS_TBNAME; 
   return $wpdb->get_col("SELECT DISTINCT field_id FROM $table");
}

function eme_check_used_formfield($field_id){
   global $wpdb;
   $table = $wpdb->prefix.ANSWERS_TBNAME; 
   $query = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE field_id=%d",$field_id);
   return $wpdb->get_var($query);
}

function eme_get_formfields($ids="",$purpose=""){
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $where="";
   $where_arr=array();
   if (!empty($ids))
	   $where_arr[]="field_id IN ($ids)";
   if (!empty($purpose))
	   $where_arr[]="field_purpose='".esc_sql($purpose)."'";
   if (!empty($where_arr))
      $where="WHERE ".join('AND ',$where_arr);
   return $wpdb->get_results("SELECT * FROM $formfields_table $where", ARRAY_A);
}

function eme_get_formfield($field_info) { 
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   if (is_numeric($field_info))
	   $sql = $wpdb->prepare("SELECT * FROM $formfields_table WHERE field_id=%d",$field_info);
   else
	   $sql = $wpdb->prepare("SELECT * FROM $formfields_table WHERE field_name=%s LIMIT 1",$field_info);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_fieldpurpose($purpose=""){
   $uses = array(
	   'generic'=>__('Generic','events-made-easy'),
	   'people'=>__('People field','events-made-easy'),
	   'members'=>__('Members field','events-made-easy')
   );
   if ($purpose) {
	   if (isset($uses[$purpose]))
		   return $uses[$purpose];
	   else
		   return $uses['generic'];
   } else {
	   return $uses;
   }
}

function eme_get_fieldtypes(){
   $types = array(
	   'text'=>__('Text','events-made-easy'),
	   'textarea'=>__('Textarea','events-made-easy'),
	   'dropdown'=>__('Dropdown','events-made-easy'),
	   'dropdown_multi'=>__('Dropdown (multiple)','events-made-easy'),
	   'radiobox'=>__('Radiobox','events-made-easy'),
	   'radiobox_vertical'=>__('Radiobox (vertical)','events-made-easy'),
	   'checkbox'=>__('Checkbox','events-made-easy'),
	   'checkbox_vertical'=>__('Checkbox (vertical)','events-made-easy'),
	   'date_js'=>__('Date (Javascript)','events-made-easy'),
	   'date'=>__('Date (HTML5)','events-made-easy'),
	   'datetime-local'=>__('Datetime-local (HTML5)','events-made-easy'),
	   'month'=>__('Month (HTML5)','events-made-easy'),
	   'week'=>__('Week (HTML5)','events-made-easy'),
	   'time'=>__('Time (HTML5)','events-made-easy'),
	   'color'=>__('Color (HTML5)','events-made-easy'),
	   'email'=>__('Email (HTML5)','events-made-easy'),
	   'number'=>__('Number (HTML5)','events-made-easy'),
	   'range'=>__('Range (HTML5)','events-made-easy'),
	   'tel'=>__('Tel (HTML5)','events-made-easy'),
	   'url'=>__('Url (HTML5)','events-made-easy')
   );
   return $types;
}

function eme_get_fieldtype($type){
   $fieldtypes = eme_get_fieldtypes();
   return $fieldtypes[$type];
}

function eme_is_multifield($type){
   global $wpdb;
   return in_array($type, array('dropdown', 'dropdown_multi','radiobox','radiobox_vertical','checkbox','checkbox_vertical'));
}

function eme_get_formfield_html($field_id, $field_name, $entered_val, $required=0, $class='') {
   $formfield = eme_get_formfield($field_id);
   if (!$formfield) return;

   if ($class)
	   $class_att="class='$class'";
   else
	   $class_att="";

   if ($formfield['field_required'])
      $required=1;

   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   if (is_admin() && isset($_REQUEST['eme_admin_action'])) {
	   // fields can have a different value for front/backend for multi-fields
	   if (!empty($formfield['admin_values']))
		   $field_values = $formfield['admin_values'];
	   else
		   $field_values = $formfield['field_values'];
	   if (!empty($formfield['admin_tags']))
		   $field_tags = $formfield['admin_tags'];
	   else
		   $field_tags = $formfield['field_tags'];
   } else {
	   $field_values = $formfield['field_values'];
	   $field_tags = $formfield['field_tags'];
   }
   $field_attributes = eme_esc_html($formfield['field_attributes']);
   if (empty($field_tags))
      $field_tags=$field_values;
   if (empty($field_name))
      $field_name='FIELD'.$field_id;
   switch($formfield['field_type']) {
      case 'text':
      case 'date':
      case 'datetime-local':
      case 'month':
      case 'week':
      case 'time':
      case 'color':
      case 'email':
      case 'number':
      case 'range':
      case 'tel':
      case 'url':
	 # for text field
         $value=$entered_val;
         if (empty($value))
            $value=eme_translate($field_tags);
         if (empty($value))
            $value=$field_values;
         $value = eme_esc_html($value);
         $html = "<input $required_att type='".$formfield['field_type']."' name='$field_name' id='$field_name' value='$value' $field_attributes $class_att />";
         break;
      case 'dropdown':
         # dropdown
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
	 // since the values for a dropdown field need not be unique, we give them as an array to be built with eme_ui_select
         foreach ($values as $key=>$val) {
            $tag=eme_translate($tags[$key]);
	    $new_el=array(0=>$val,1=>$tag);
            $my_arr[]=$new_el;
         }
	 $html = eme_ui_select($entered_val,$field_name,$my_arr,'',$required,$class,$field_attributes);
         break;
      case 'dropdown_ulti':
         # dropdown, multiselect
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
	 // since the values for a dropdown field need not be unique, we give them as an array to be built with eme_ui_select
         foreach ($values as $key=>$val) {
            $tag=eme_translate($tags[$key]);
	    $new_el=array(0=>$val,1=>$tag);
            $my_arr[]=$new_el;
         }
	 $html = eme_ui_multiselect($entered_val,$field_name,$my_arr,5,$required,$class." eme_select2_class");
         break;
      case 'textarea':
         # textarea
         $value=$entered_val;
         if (empty($value))
            $value=eme_translate($field_tags);
         if (empty($value))
            $value=$field_values;
         $value = eme_esc_html($value);
         $html = "<textarea $class_att $required_att name='$field_name' $id='$field_name' field_attributes>$value</textarea>";
         break;
      case 'radiobox':
         # radiobox
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_radio($entered_val,$field_name,$my_arr,true,$required,$class);
         break;
      case 'radiobox_vertical':
         # radiobox, vertical
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_radio($entered_val,$field_name,$my_arr,false,$required,$class);
         break;
      case 'checkbox':
      	# checkbox
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_checkbox($entered_val,$field_name,$my_arr,true,$required,$class);
         break;
      case 'checkbox_vertical':
      	 # checkbox, vertical
         $values = eme_convert_multi2array($field_values);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_checkbox($entered_val,$field_name,$my_arr,false,$required,$class);
         break;
       case 'date_js':
	 # for date JS field
         $value=$entered_val;
         if (empty($value))
            $value=eme_translate($field_tags);
         if (empty($value))
            $value=$field_values;
         $value = eme_esc_html($value);
	 if (empty($field_attributes)) $field_attributes=get_option('date_format');
	 $dateformat=eme_wp_date_format_php_to_js($field_attributes);
         $html = "<input type='hidden' name='$field_name' id='$field_name' value='$value' $class_att />";
         $html .= "<input $required_att readonly='readonly' type='text' name='dp_${field_name}' id='dp_${field_name}' data-date='$value' data-dateformat='$dateformat' data-altfield='$field_name' class='eme_formfield_date $class' />";
         break;
   }
   return $html;
}

function eme_replace_cancelformfields_placeholders ($event) {
   // not used from the admin backend, but we check to be sure
   if (is_admin()) return;

   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only && !is_user_logged_in()) return '';

   if ($registration_wp_users_only) {
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }

   if (!empty($event['event_cancel_form_format']))
      $format = $event['event_cancel_form_format'];
   elseif ($event['event_properties']['event_cancel_form_format_tpl']>0)
      $format = eme_get_template_format($event['event_properties']['event_cancel_form_format_tpl']);
   else
      $format = get_option('eme_cancel_form_format' );


   $eme_captcha_for_booking=get_option('eme_captcha_for_booking') && !is_admin();

   $required_fields_count = 0;
   // We need at least #_NAME, #_EMAIL and #_SUBMIT
   $required_fields_min = 3;
   // if we require the captcha: add 1
   if ($eme_captcha_for_booking)
      $required_fields_min++;

   $bookerLastName="";
   $bookerFirstName="";
   $bookerEmail="";
   $bookerCancelComment="";
   if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      $bookerLastName=$current_user->user_lastname;
      if (empty($bookerLastName))
               $bookerLastName=$current_user->display_name;
      $bookerFirstName=$current_user->user_firstname;
      $bookerEmail=$current_user->user_email;
   }

   // the 2 placeholders that can contain extra text are treated separately first
   // the question mark is used for non greedy (minimal) matching
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   if (preg_match('/#_SUBMIT\{.+\}/', $format)) {
      $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html('$1')."' />" ,$format );
      $required_fields_count++;
   }

   // now the normal placeholders
   preg_match_all("/#(REQ)?_[A-Za-z0-9_]+/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }

      // also support RESPNAME, RESPEMAIL, ...
      if (strstr($result,'#_RESP')) {
         $result = str_replace("#_RESP","#_",$result);
      }

      if (preg_match('/#_NAME|#_LASTNAME/', $result)) {
         $replacement = "<input required='required' type='text' name='lastname' id='lastname' value='$bookerLastName' $readonly />";
         $required_fields_count++;
         // #_NAME is always required
         $required=1;
      } elseif (preg_match('/#_FIRSTNAME/', $result)) {
         if (!empty($bookerFirstName))
            $replacement = "<input $required_att type='text' name='firstname' id='firstname' value='$bookerFirstName' $readonly />";
         else
            $replacement = "<input $required_att type='text' name='firstname' id='firstname' value='$bookerFirstName' />";
      } elseif (preg_match('/#_EMAIL|#_HTML5_EMAIL/', $result)) {
         $replacement = "<input required='required' type='email' name='email' id='email' value='$bookerEmail' $readonly />";
         $required_fields_count++;
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_CANCELCOMMENT/', $result)) {
         $replacement = "<textarea $required_att name='eme_cancelcomment'>$bookerCancelComment</textarea>";
      } elseif (preg_match('/#_CAPTCHA/', $result)) {
	 if ($eme_captcha_for_booking) {
		 $captcha_url=eme_captcha_url("eme_del_booking");
		 $replacement = "<img id='eme_captcha_img' src='$captcha_url'><br /><input required='required' type='text' name='captcha_check' autocomplete='off' class='nodynamicupdates' />";
		 $required_fields_count++;
	 }
      } elseif (preg_match('/#_SUBMIT/', $result, $matches)) {
         $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html(get_option('eme_rsvp_delbooking_submit_string'))."' />";
         $required_fields_count++;
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "<div class='eme-required-field'>".get_option('eme_rsvp_required_field_string')."</div>";

      if ($found) {
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);

   // now, replace any language tags found in the format itself
   $format = eme_translate($format);

   if ($required_fields_count >= $required_fields_min) {
      return $format;
   } else {
      return __('Not all required fields are present in the form.', 'events-made-easy');
   }
}

// the event param in eme_replace_extra_multibooking_formfields_placeholders
// is only there for generic replacements, like e.g. currency
function eme_replace_extra_multibooking_formfields_placeholders ($format,$event) {
   $bookerLastName="";
   $bookerFirstName="";
   $bookerAddress1="";
   $bookerAddress2="";
   $bookerCity="";
   $bookerState="";
   $bookerZip="";
   $bookerCountry="";
   $bookerEmail="";
   $bookerComment="";
   $bookerPhone="";
   $massmail=NULL;

   if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      $bookerLastName=$current_user->user_lastname;
      if (empty($bookerLastName))
               $bookerLastName=$current_user->display_name;
      $bookerFirstName=$current_user->user_firstname;
      $bookerEmail=$current_user->user_email;
      $bookerPhone=get_user_meta($current_user->ID, 'eme_phone', true);
   }

   $eme_captcha_for_booking=get_option('eme_captcha_for_booking') && !is_admin();

   // the 2 placeholders that can contain extra text are treated separately first
   // the question mark is used for non greedy (minimal) matching
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   if (preg_match('/#_SUBMIT\{.+\}/', $format)) {
      $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html('$1')."' />" ,$format );
   }

   // now the normal placeholders
   preg_match_all("/#(REQ)?_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless

   # first we check if people desire dynamic pricing on it's own, if not: we set the relevant price class to empty
   if (in_array('#_DYNAMICPRICE',$placeholders))
	   $dynamic_price_class_basic='dynamicprice';
   else
	   $dynamic_price_class_basic='';

   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }

      // also support RESPNAME, RESPEMAIL, ...
      if (strstr($result,'#_RESP')) {
         $result = str_replace("#_RESP","#_",$result);
      }

      if (preg_match('/#_NAME|#_LASTNAME/', $result)) {
         $replacement = "<input required='required' type='text' name='lastname' id='lastname' value='$bookerLastName' />";
         // #_NAME is always required
         $required=1;
      } elseif (preg_match('/#_FIRSTNAME/', $result)) {
         $replacement = "<input $required_att type='text' name='firstname' id='firstname' value='$bookerFirstName' />";
      } elseif (preg_match('/#_ADDRESS1/', $result)) {
         $replacement = "<input $required_att type='text' name='address1' id=address1' value='$bookerAddress1' />";
      } elseif (preg_match('/#_ADDRESS2/', $result)) {
         $replacement = "<input $required_att type='text' name='address2' id='address2' value='$bookerAddress2' />";
      } elseif (preg_match('/#_CITY/', $result)) {
         $replacement = "<input $required_att type='text' name='city' id='city' value='$bookerCity' />";
      } elseif (preg_match('/#_STATE/', $result)) {
         $replacement = "<input $required_att type='text' name='state' id='state' value='$bookerState' />";
      } elseif (preg_match('/#_ZIP/', $result)) {
         $replacement = "<input $required_att type='text' name='zip' id='zip' value='$bookerZip' />";
      } elseif (preg_match('/#_COUNTRY/', $result)) {
         $replacement = "<input $required_att type='text' name='country' id='country' value='$bookerCountry' />";
      } elseif (preg_match('/#_EMAIL|#_HTML5_EMAIL/', $result)) {
         $replacement = "<input required='required' type='email' name='email' id='email' value='$bookerEmail' />";
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_OPT_OUT/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 1;
         $replacement = eme_ui_select_binary($selected_massmail,"${var_prefix}massmail${var_postfix}");
      } elseif (preg_match('/#_OPT_IN/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 0;
         $replacement = eme_ui_select_binary($selected_massmail,"${var_prefix}massmail${var_postfix}");
      } elseif (preg_match('/#_PHONE|#_HTML5_PHONE/', $result)) {
         $replacement = "<input $required_att type='tel' name='phone' id='phone' value='$bookerPhone' />";
      } elseif (preg_match('/#_COMMENT/', $result)) {
         $replacement = "<textarea $required_att name='eme_rsvpcomment'>$bookerComment</textarea>";
      } elseif (preg_match('/#_CAPTCHA/', $result)) {
	 if ($eme_captcha_for_booking) {
		 $captcha_url=eme_captcha_url("eme_add_booking");
		 $replacement = "<img id='eme_captcha_img' src='$captcha_url'><br /><input required='required' type='text' name='captcha_check' autocomplete='off' class='nodynamicupdates' />";
	 }
      } elseif (preg_match('/#_SUBMIT/', $result, $matches)) {
         $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html(get_option('eme_rsvp_addbooking_submit_string'))."' />";
      } elseif (preg_match('/#_DYNAMICPRICE$/', $result)) {
	 $replacement = "<span id='eme_calc_bookingprice'></span>";
      } elseif (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
	 $field_id = $formfield['field_id'];
         $postfield_name="FIELD".$field_id;
         $entered_val = "";
         if ($formfield['extra_charge'])
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required,$dynamic_price_class_basic);
         else
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required);
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "<div class='eme-required-field'>".get_option('eme_rsvp_required_field_string')."</div>";

      if ($found) {
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);

   // now, replace any language tags found in the format itself
   $format = eme_translate($format);
   return $format;
}

function eme_get_dyndata_people_fields($condition) {
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $formfields_table where field_purpose='people' AND field_condition=%s",$condition);
   return $wpdb->get_results($sql,ARRAY_A);
}

function eme_replace_dynamic_rsvp_formfields_placeholders ($event,$booking,$format,$grouping,$i=0) {
   $event_id=$event['event_id'];
   $is_admin=is_admin();
   $dynamic_price_class='dynamicprice';
   $dynamic_field_class='dynamicfield';
   if ($is_admin && isset($booking['booking_id'])) {
      $editing_booking_from_backend=1;
      $dyn_answer = eme_get_dyndata_booking_answer($booking['booking_id'],$grouping,$i);
   } else {
      $editing_booking_from_backend=0;
      $dyn_answer = array();
   }
   preg_match_all("/#(REQ)?_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      $var_prefix="dynamic_bookings[$event_id][$grouping][$i][";
      $var_postfix="]";
      $postvar_arr=array('dynamic_bookings',$event_id,$grouping,$i);

      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }
      if (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
	 $postfield_name="${var_prefix}FIELD".$field_id.$var_postfix;
	 array_push($postvar_arr,"FIELD".$field_id);

	 // when we edit a booking, there's nothing in $_POST until a field condition changes
	 // so the first time entered_val=''
         $entered_val = eme_getValueFromPath($_POST,$postvar_arr);
	 // if from backend and entered_val ='', then get it from the stored answer
         if ($editing_booking_from_backend && empty($entered_val)) {
            foreach ($dyn_answer as $answer) {
               if ($answer['field_id'] == $field_id) {
                  // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
                  // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
                  $entered_val = $answer['answer'];
                  if (eme_is_multi($entered_val)) {
                     $entered_val = eme_convert_multi2array($entered_val);
                  }
               }
            }
         }
         if ($formfield['extra_charge'])
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required,$dynamic_price_class.' '.$dynamic_field_class);
         else
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required,$dynamic_field_class);
      } elseif (preg_match('/#_FIELDCOUNTER/', $result, $matches)) {
         $replacement = intval($i);
      } elseif (preg_match('/#_FIELDGROUPINDEX/', $result, $matches)) {
         $replacement = intval($grouping);
      } else {
         $found = 0;
      }

      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );

   }
   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);
   return $format;
}

function eme_replace_dynamic_membership_formfields_placeholders ($membership,$member,$format,$grouping,$i=0) {
   $membership_id=$membership['membership_id'];
   $is_admin=is_admin();
   $dynamic_price_class='dynamicprice';
   $dynamic_field_class='dynamicfield';
   if ($is_admin && isset($member['member_id'])) {
      $editing_booking_from_backend=1;
      $dyn_answer = eme_get_dyndata_member_answer($member['member_id'],$grouping,$i);
   } else {
      $editing_booking_from_backend=0;
      $dyn_answer = array();
   }
   // if we require the captcha: add 1
   preg_match_all("/#(REQ)?_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      $var_prefix="dynamic_member[$membership_id][$grouping][$i][";
      $var_postfix="]";
      $postvar_arr=array('dynamic_member',$membership_id,$grouping,$i);

      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }
      if (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
	 $postfield_name="${var_prefix}FIELD".$field_id.$var_postfix;
	 array_push($postvar_arr,"FIELD".$field_id);

	 // when we edit a booking, there's nothing in $_POST until a field condition changes
	 // so the first time entered_val=''
         $entered_val = eme_getValueFromPath($_POST,$postvar_arr);
	 // if from backend and entered_val ='', then get it from the stored answer
         if ($editing_booking_from_backend && empty($entered_val)) {
            foreach ($dyn_answer as $answer) {
               if ($answer['field_id'] == $field_id) {
                  // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
                  // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
                  $entered_val = $answer['answer'];
                  if (eme_is_multi($entered_val)) {
                     $entered_val = eme_convert_multi2array($entered_val);
                  }
               }
            }
         }
         if ($formfield['extra_charge'])
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required,$dynamic_price_class.' '.$dynamic_field_class);
         else
                 $replacement = eme_get_formfield_html($field_id,$postfield_name,$entered_val,$required,$dynamic_field_class);
      } elseif (preg_match('/#_FIELDCOUNTER/', $result, $matches)) {
         $replacement = intval($i);
      } elseif (preg_match('/#_FIELDGROUPINDEX/', $result, $matches)) {
         $replacement = intval($grouping);
      } else {
         $found = 0;
      }

      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );

   }
   $format = eme_replace_membership_placeholders($format,$membership);
   return $format;
}

function eme_replace_rsvp_formfields_placeholders ($event,$booking,$format="",$is_multibooking=0,$return_postfieldname=0) {
   $event_id=$event['event_id'];
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only && !is_user_logged_in()) {
      return '';
   }

   $is_admin=is_admin();
   $current_user = wp_get_current_user();
   if ($is_admin && isset($booking['booking_id'])) {
      $editing_booking_from_backend=1;
   } else {
      $editing_booking_from_backend=0;
   }
   if ($is_admin && get_option('eme_rsvp_admin_allow_overbooking')) {
      $allow_overbooking=1;
   } else {
      $allow_overbooking=0;
   }

   $bookerLastName="";
   $bookerFirstName="";
   $bookerAddress1="";
   $bookerAddress2="";
   $bookerCity="";
   $bookerState="";
   $bookerZip="";
   $bookerCountry="";
   $bookerEmail="";
   $bookerComment="";
   $bookerPhone="";
   $bookedSeats=0;
   $massmail=NULL;

   // don't fill out the basic info if in the backend, but do it only if in the frontend
   if (is_user_logged_in() && !$is_admin) {
      $bookerLastName=$current_user->user_lastname;
      if (empty($bookerLastName))
               $bookerLastName=$current_user->display_name;
      $bookerFirstName=$current_user->user_firstname;
      $bookerEmail=$current_user->user_email;
      $bookerPhone=get_user_meta($current_user->ID, 'eme_phone', true);
   }

   if ($editing_booking_from_backend) {
      $person = eme_get_person ($booking['person_id']);
      // when editing a booking
      $bookerLastName = eme_esc_html($person['lastname']);
      $bookerFirstName = eme_esc_html($person['firstname']);
      $bookerAddress1 = eme_esc_html($person['address1']);
      $bookerAddress2 = eme_esc_html($person['address2']);
      $bookerCity = eme_esc_html($person['city']);
      $bookerState = eme_esc_html($person['state']);
      $bookerZip = eme_esc_html($person['zip']);
      $bookerCountry = eme_esc_html($person['country']);
      $bookerEmail = eme_esc_html($person['email']);
      $bookerPhone = eme_esc_html($person['phone']);
      $massmail = intval($person['massmail']);
      $bookerComment = eme_esc_html($booking['booking_comment']);
      $bookedSeats = eme_esc_html($booking['booking_seats']);
      if ($booking['booking_seats_mp']) {
         $booking_seats_mp=eme_convert_multi2array($booking['booking_seats_mp']);
         foreach ($booking_seats_mp as $key=>$val) {
            $field_index=$key+1;
            ${"bookedSeats".$field_index}=eme_esc_html($val);
         }
      }
   } else {
      $booking_seats_mp=array();
   }

   // if not in the backend and wp membership is required
   // or when editing an existing booking via backend (not a new)
   if ($registration_wp_users_only && !$is_admin) {
      $search_tables=get_option('eme_autocomplete_sources');
      if ($search_tables=='none') {
         $readonly="disabled='disabled'";
      } elseif (current_user_can( get_option('eme_cap_edit_events')) ||
         (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_user->ID || $event['event_contactperson_id']==$current_user->ID))) {
         $readonly="";
      } else {
         $readonly="disabled='disabled'";
      }
   } elseif ($editing_booking_from_backend) {
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }

   if (empty($format)) {
      if (!empty($event['event_registration_form_format']))
         $format = $event['event_registration_form_format'];
      elseif ($event['event_properties']['event_registration_form_format_tpl']>0)
         $format = eme_get_template_format($event['event_properties']['event_registration_form_format_tpl']);
      else
         $format = get_option('eme_registration_form_format' );
   }

   $min_allowed = $event['event_properties']['min_allowed'];
   $max_allowed = $event['event_properties']['max_allowed'];
   //if ($event['event_properties']['take_attendance']) {
   //   $min_allowed = 0;
   //   $max_allowed = 1;
   //}

   $waitinglist=0;
   $waitinglist_seats = intval($event['event_properties']['waitinglist_seats']);
   if ($allow_overbooking) {
      // in the admin itf, and allowing overbooking
      // then the avail seats are the total seats
      $avail_seats = eme_get_total($event['event_seats']);
   } else {
      // the next gives the number of available seats, even for multiprice
      $avail_seats = eme_get_available_seats($event_id);
      if ($waitinglist_seats>0 && $avail_seats>$waitinglist_seats && !eme_is_multi($event['event_seats'])) {
	      $avail_seats-=$waitinglist_seats;
      } elseif ($waitinglist_seats>0 && $avail_seats<=$waitinglist_seats && !eme_is_multi($event['event_seats'])) {
	      $waitinglist=1;
      }
   }

   $booked_places_options = array();
   if (eme_is_multi($max_allowed)) {
      $multi_max_allowed=eme_convert_multi2array($max_allowed);
      $max_allowed_is_multi=1;
   } else {
      $max_allowed_is_multi=0;
   }
   if (eme_is_multi($min_allowed)) {
      $multi_min_allowed=eme_convert_multi2array($min_allowed);
      $min_allowed_is_multi=1;
   } else {
      $min_allowed_is_multi=0;
   }
   if (eme_is_multi($event['event_seats'])) {
      // in the admin itf, and allowing overbooking
      // then the avail seats are the total seats
      if ($allow_overbooking)
         $multi_avail = eme_convert_multi2array($event['event_seats']);
      else
         $multi_avail = eme_get_available_multiseats($event_id);

      foreach ($multi_avail as $key => $avail_seats) {
         $booked_places_options[$key] = array();
         if ($max_allowed_is_multi)
            $real_max_allowed=$multi_max_allowed[$key];
         else
            $real_max_allowed=$max_allowed;
	 
         // don't let people choose more seats than available
         if ($real_max_allowed>$avail_seats || $real_max_allowed==0)
            $real_max_allowed=$avail_seats;

	 if ($editing_booking_from_backend && isset($booking_seats_mp[$key])) { 
		 // when editing a booking in the backend, the number available seats are in fact the number of free seats+number of booked seat
		 $real_max_allowed+=$booking_seats_mp[$key];
		 // now also respect the set max for the event
		 if ($max_allowed_is_multi && $real_max_allowed>$multi_max_allowed[$key])
			 $real_max_allowed = $multi_max_allowed[$key];
		 elseif ($real_max_allowed>$max_allowed)
			 $real_max_allowed = $max_allowed;
	 }
         
         if ($min_allowed_is_multi)
            $real_min_allowed=$multi_min_allowed[$key];
         else
            // it's no use to have a non-multi minimum for multiseats
            $real_min_allowed=0;
         
         for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++) 
            $booked_places_options[$key][$i]=$i;
      }
   } elseif (eme_is_multi($event['price'])) {
      // we just need to loop through the same amount of seats as there are prices
      foreach (eme_convert_multi2array($event['price']) as $key => $value) {
         $booked_places_options[$key] = array();
         if ($max_allowed_is_multi)
            $real_max_allowed=$multi_max_allowed[$key];
         else
            $real_max_allowed=$max_allowed;

         // don't let people choose more seats than available
         if ($real_max_allowed>$avail_seats || $real_max_allowed==0)
            $real_max_allowed=$avail_seats;

	 if ($editing_booking_from_backend && isset($booking_seats_mp[$key])) { 
		 // when editing a booking in the backend, the number available seats are in fact the number of free seats+number of booked seat
		 $real_max_allowed+=$booking_seats_mp[$key];
		 // now also respect the set max for the event
		 if ($max_allowed_is_multi && $real_max_allowed>$multi_max_allowed[$key])
			 $real_max_allowed = $multi_max_allowed[$key];
		 elseif ($real_max_allowed>$max_allowed)
			 $real_max_allowed = $max_allowed;
	 }
         
         if ($min_allowed_is_multi)
            $real_min_allowed=$multi_min_allowed[$key];
         else
            // it's no use to have a non-multi minimum for multiseats/multiprice
            $real_min_allowed=0;

         for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++)
            $booked_places_options[$key][$i]=$i;
      }
   } else {
      if ($max_allowed_is_multi)
         $real_max_allowed=$multi_max_allowed[0];
      else
         $real_max_allowed=$max_allowed;

      // don't let people choose more seats than available
      if ($real_max_allowed > $avail_seats || $real_max_allowed==0)
         $real_max_allowed = $avail_seats;

      // let's make sure that when editing a booking in the backend, at least the same amount of seats are shown as there were booked seats
      if ($editing_booking_from_backend && $real_max_allowed<$bookedSeats) {
	 $real_max_allowed+=$bookedSeats;
	 if ($max_allowed_is_multi && $real_max_allowed>$multi_max_allowed[0])
		 $real_max_allowed = $multi_max_allowed[0];
	 elseif ($real_max_allowed>$max_allowed)
		 $real_max_allowed = $max_allowed;
      }

      if ($min_allowed_is_multi)
         $real_min_allowed=$multi_min_allowed[0];
      else
         $real_min_allowed=$min_allowed;

      for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++) 
         $booked_places_options[$i]=$i;
   }

   $required_fields_count = 0;
   $discount_fields_count = 0;
   $eme_captcha_for_booking=get_option('eme_captcha_for_booking') && !$is_admin;
   # we need 4 required fields: #_NAME, #_EMAIL, #_SEATS and #_SUBMIT
   # for multiprice: 3 + number of possible prices (we add those later on)
   if (eme_is_multi($event['price']))
      $required_fields_min = 3;
   else
      $required_fields_min = 4;
   // if we require the captcha: add 1
   if ($eme_captcha_for_booking)
      $required_fields_min++;

   // for multi booking forms, the required field count per booking form is 1 (SEATS)
   if (!$is_admin && $is_multibooking) {
      if (eme_is_multi($event['price']))
         $required_fields_min = 0;
      else
         $required_fields_min =1;
   }

   // first we do the custom attributes, since these can contain other placeholders
   preg_match_all("/#(ESC|URL)?_ATT\{.+?\}(\{.+?\})?/", $format, $results);
   foreach($results[0] as $resultKey => $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      //Strip string of placeholder and just leave the reference
      $attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
      if (isset($event['event_attributes'][$attRef])) {
         $replacement = $event['event_attributes'][$attRef];
      }
      if( trim($replacement) == ''
            && isset($results[2][$resultKey])
            && $results[2][$resultKey] != '' ) {
         //Check to see if we have a second set of braces;
         $replacement = substr( $results[2][$resultKey], 1, strlen(trim($results[2][$resultKey]))-2 );
      }

      if ($need_escape)
         $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   // the 2 placeholders that can contain extra text are treated separately first
   // the question mark is used for non greedy (minimal) matching
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   if (preg_match('/#_SUBMIT\{.+\}/', $format)) {
      if ($editing_booking_from_backend)
         $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".__('Update booking','events-made-easy')."' />" ,$format );
      else
         $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html('$1')."' />" ,$format );
      if (!$is_multibooking)
         $required_fields_count++;
   }

   // check which fields are used in the event definition for dynamic data
   $eme_dyndatafields=array();
   if (isset($event['event_properties']['rsvp_dyndata'])) {
      foreach ($event['event_properties']['rsvp_dyndata'] as $dynfield) {
         $eme_dyndatafields[]=$dynfield['field'];
      }
   }

   // now the normal placeholders
   preg_match_all("/#(REQ)?_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless

   # first we check if people desire dynamic pricing on it's own, if not: we set the relevant price class to empty
   if (in_array('#_DYNAMICPRICE',$placeholders[0])) {
	   $dynamic_price_class="class='dynamicprice'";
	   $dynamic_price_class_basic='dynamicprice';
   } else {
	   $dynamic_price_class='';
	   $dynamic_price_class_basic='';
   }

   # check also if dynamic data is requested
   if (in_array('#_DYNAMICDATA',$placeholders[0]) && !empty($eme_dyndatafields)) {
	   $dynamic_data_wanted=1;
   } else {
	   $dynamic_data_wanted=0;
   }

   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }

      // also support RESPNAME, RESPEMAIL, ...
      if (strstr($result,'#_RESP')) {
         $result = str_replace("#_RESP","#_",$result);
      }
 
      // check for dynamic field class for this field
      if ($dynamic_data_wanted && !$is_multibooking && in_array($result, $eme_dyndatafields)) {
	   $dynamic_field_class="class='dynamicupdates'";
	   $dynamic_field_class_basic='dynamicupdates';
      } else {
	   $dynamic_field_class="class='nodynamicupdates'";
	   $dynamic_field_class_basic='nodynamicupdates';
      }

      if ($is_multibooking) {
         $var_prefix="bookings[$event_id][";
	 $var_postfix="]";
	 $postvar_arr=array('bookings',$event_id);
      } else {
         $var_prefix='';
         $var_postfix='';
	 $postvar_arr=array();
      }

      if (preg_match('/#_NAME|#_LASTNAME/', $result)) {
	 $fieldname="${var_prefix}lastname${var_postfix}";
	 array_push($postvar_arr,"lastname");
	 if ($return_postfieldname) return $postvar_arr;
         if (!$is_multibooking) {
            $replacement = "<input required='required' type='text' name='$fieldname' value='$bookerLastName' $readonly $dynamic_field_class />";
            $required_fields_count++;
            // #_NAME is always required
            $required=1;
         }
      } elseif (preg_match('/#_FIRSTNAME/', $result)) {
	 $fieldname="${var_prefix}firstname${var_postfix}";
	 array_push($postvar_arr,"firstname");
	 if ($return_postfieldname) return $postvar_arr;
         if (!empty($bookerFirstName))
            $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerFirstName' $readonly $dynamic_field_class />";
         else
            $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerFirstName' $dynamic_field_class />";
      } elseif (preg_match('/#_ADDRESS1/', $result)) {
	 $fieldname="${var_prefix}address1${var_postfix}";
	 array_push($postvar_arr,"address1");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerAddress1' $dynamic_field_class />";
      } elseif (preg_match('/#_ADDRESS2/', $result)) {
	 $fieldname="${var_prefix}address2${var_postfix}";
	 array_push($postvar_arr,"address2");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerAddress2' $dynamic_field_class />";
      } elseif (preg_match('/#_CITY/', $result)) {
	 $fieldname="${var_prefix}city${var_postfix}";
	 array_push($postvar_arr,"city");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerCity' $dynamic_field_class />";
      } elseif (preg_match('/#_STATE/', $result)) {
	 $fieldname="${var_prefix}state${var_postfix}";
	 array_push($postvar_arr,"state");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerState' $dynamic_field_class />";
      } elseif (preg_match('/#_ZIP/', $result)) {
	 $fieldname="${var_prefix}zip${var_postfix}";
	 array_push($postvar_arr,"zip");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerZip' $dynamic_field_class />";
      } elseif (preg_match('/#_COUNTRY/', $result)) {
	 $fieldname="${var_prefix}country${var_postfix}";
	 array_push($postvar_arr,"country");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerCountry' $dynamic_field_class />";
      } elseif (preg_match('/#_EMAIL|#_HTML5_EMAIL/', $result)) {
	 $fieldname="${var_prefix}email${var_postfix}";
	 array_push($postvar_arr,"email");
	 if ($return_postfieldname) return $postvar_arr;
         if (!$is_multibooking) {
            // there still exist people without email, so in the backend we allow it ...
	    if ($is_admin)
		    $replacement = "<input type='email' name='$fieldname' value='$bookerEmail' $readonly $dynamic_field_class />";
	    else
		    $replacement = "<input required='required' type='email' name='$fieldname' value='$bookerEmail' $readonly $dynamic_field_class />";
            $required_fields_count++;
            // #_EMAIL is always required
            $required=1;
         }
      } elseif (preg_match('/#_PHONE|#_HTML5_PHONE/', $result)) {
	 $fieldname="${var_prefix}phone${var_postfix}";
	 array_push($postvar_arr,"phone");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='tel' name='$fieldname' value='$bookerPhone' $dynamic_field_class />";
      } elseif (preg_match('/#_OPT_OUT/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 1;
	 $fieldname="${var_prefix}massmail${var_postfix}";
	 array_push($postvar_arr,"massmail");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = eme_ui_select_binary($selected_massmail,$fieldname,0,$dynamic_field_class_basic);
      } elseif (preg_match('/#_OPT_IN/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 0;
	 $fieldname="${var_prefix}massmail${var_postfix}";
	 array_push($postvar_arr,"massmail");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = eme_ui_select_binary($selected_massmail,$fieldname,0,$dynamic_field_class_basic);
      } elseif (preg_match('/#_DYNAMICPRICE$/', $result)) {
         if (!$is_multibooking)
		 $replacement = "<span id='eme_calc_bookingprice'></span>";
      } elseif (preg_match('/#_DYNAMICDATA$/', $result)) {
	 if (isset($event['event_properties']['rsvp_dyndata']) && !empty($event['event_properties']['rsvp_dyndata']))
                 $replacement = "<div id='eme_dyndata'></div>";
      } elseif (preg_match('/#_SEATS$|#_SPACES$/', $result)) {
         $var_prefix="bookings[$event_id][";
         $var_postfix="]";
	 $postvar_arr=array('bookings',$event_id);
         $fieldname="${var_prefix}bookedSeats${var_postfix}";
	 array_push($postvar_arr,"bookedSeats");
	 if ($return_postfieldname) return $postvar_arr;
         if ($editing_booking_from_backend && isset($bookedSeats))
            $entered_val=$bookedSeats;
         else
            $entered_val=0;

         if ($event['event_properties']['take_attendance']) {
            // if we require 1 seat at the minimum, we set it to that
            // it could even be a hidden field then ...
            if (!$min_allowed_is_multi && $min_allowed>0) {
               $replacement = "<input type='hidden' name='$fieldname' value='1'>";
            } else {
               $replacement = eme_ui_select_binary($entered_val,$fieldname,0,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
            }
         } else {
            $replacement = eme_ui_select($entered_val,$fieldname,$booked_places_options,'',0,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
	    if ($waitinglist) {
		    $replacement.="<span id='eme_waitinglist'>".__("This reservation will be put on the waiting list","events-made-easy")."</span>";
	    }
         }
         $required_fields_count++;

      } elseif (preg_match('/#_(SEATS|SPACES)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2]);
         $var_prefix="bookings[$event_id][";
         $var_postfix="]";
	 $postvar_arr=array('bookings',$event_id);
         $fieldname="${var_prefix}bookedSeats".$field_id.$var_postfix;
	 array_push($postvar_arr,"bookedSeats".$field_id);
	 if ($return_postfieldname) return $postvar_arr;

         if ($editing_booking_from_backend && isset(${"bookedSeats".$field_id}))
            $entered_val=${"bookedSeats".$field_id};
         else
            $entered_val=0;

	 if ($event['event_properties']['take_attendance']) {
            $replacement = eme_ui_select_binary($entered_val,$fieldname,0,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
	 } else {
            if (eme_is_multi($event['event_seats']) || eme_is_multi($event['price'])) {
               $replacement = eme_ui_select($entered_val,$fieldname,$booked_places_options[$field_id-1],'',0,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
            } else {
               $replacement = eme_ui_select($entered_val,$fieldname,$booked_places_options,'',0,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
            }
	 }
         $required_fields_count++;
      } elseif (preg_match('/#_COMMENT/', $result)) {
	 $fieldname="${var_prefix}eme_rsvpcomment${var_postfix}";
	 array_push($postvar_arr,"eme_rsvpcomment");
	 if ($return_postfieldname) return $postvar_arr;
         if (!$is_multibooking)
            $replacement = "<textarea $required_att name='$fieldname' $dynamic_field_class>$bookerComment</textarea>";
      } elseif (preg_match('/#_CAPTCHA/', $result)) {
         if ($eme_captcha_for_booking && !$is_multibooking) {
            $captcha_url=eme_captcha_url("eme_add_booking");
            $replacement = "<img id='eme_captcha_img' src='$captcha_url'><br /><input required='required' type='text' name='captcha_check' autocomplete='off' class='nodynamicupdates' />";
            $required_fields_count++;
         }
      } elseif (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
	 $field_id = $formfield['field_id'];
         $fieldname="${var_prefix}FIELD".$field_id.$var_postfix;
	 array_push($postvar_arr,"FIELD".$field_id);
	 if ($return_postfieldname) return $postvar_arr;
         $entered_val = "";
         if ($editing_booking_from_backend) {
            $answers = eme_get_booking_answers($booking['booking_id']);
            foreach ($answers as $answer) {
               if ($answer['field_id'] == $field_id) {
                  // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
                  // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
                  $entered_val = $answer['answer'];
                  if (eme_is_multi($entered_val)) {
                     $entered_val = eme_convert_multi2array($entered_val);
                  }
               }
            }
         }
         if ($formfield['extra_charge'])
                 $replacement = eme_get_formfield_html($field_id,$fieldname,$entered_val,$required,$dynamic_price_class_basic." ".$dynamic_field_class_basic);
         else
                 $replacement = eme_get_formfield_html($field_id,$fieldname,$entered_val,$required,$dynamic_field_class_basic);

      } elseif (preg_match('/#_DISCOUNT$/', $result)) {
         $var_prefix="bookings[$event_id][";
         $var_postfix="]";
	 $postvar_arr=array('bookings',$event_id);
         // we need an ID to have a unique name per DISCOUNT input field
         $discount_fields_count++;
         if (!$is_admin) {
            $postfield_name="${var_prefix}DISCOUNT${discount_fields_count}${var_postfix}";
            $entered_val = "";
            $replacement = "<input $dynamic_price_class type='text' name='$postfield_name' value='$entered_val' />";
         } else {
            if ($discount_fields_count==1) {
               // only 1 (fixed) discount field in the admin itf
               $postfield_name="DISCOUNT";
               $replacement = "<input $dynamic_price_class type='text' name='$postfield_name' value='' /><br />".sprintf(__('Enter a new fixed discount value if wanted, or leave empty to keep the calculated value %s','events-made-easy'),eme_localized_price($booking['discount'],$event['currency']));
            } else {
               $replacement = __('Only one discount field can be used in the admin backend, the others are not rendered','events-made-easy');
            }
         }
      } elseif (preg_match('/#_SUBMIT/', $result)) {
         if (!$is_multibooking) {
            if ($editing_booking_from_backend)
               $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".__('Update booking','events-made-easy')."' />";
            else
               $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html(get_option('eme_rsvp_addbooking_submit_string'))."' />";
            $required_fields_count++;
         }
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "<div class='eme-required-field'>".get_option('eme_rsvp_required_field_string')."</div>";

      if ($found) {
         // $format = str_replace($orig_result, $replacement ,$format );
         // only replace first found occurence, this helps to e.g. replace 2 occurences of #_DISCOUNT by 2 different things
         // preg_replace could do it too, but is less performant
	 $pos = strpos($format, $orig_result);
	 if ($pos !== false) {
		 $format = substr_replace($format, $replacement, $pos, strlen($orig_result));
	 }
      }
   }

   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);

   # we need 4 required fields: #_NAME, #_EMAIL, #_SEATS and #_SUBMIT
   # for multiprice: 3 + number of possible prices
   # if these are not present: we don't replace anything and the form is worthless
   if (eme_is_multi($event['price'])) {
      $matches=eme_convert_multi2array($event['price']);
      $count=count($matches);

      // the count can be >3+$count if conditional tags are used to combine a form for single and multiple prices
      if ($required_fields_count >= $required_fields_min+$count) {
         return $format;
      } else {
         $res = __('Not all required fields are present in the form.', 'events-made-easy');
         $res.= '<br />'.__("Since this is a multiprice event, make sure you changed the setting 'Registration Form Format' for the event to include #_SEATxx placeholders for each price.",'events-made-easy');
	 if ($eme_captcha_for_booking)
		 $res.= '<br />'.__("Also check that the placeholder #_CAPTCHA is present in the form.",'events-made-easy');
         return "<div id='message' class='eme-rsvp-message'>$res</div>";
      }
   } elseif ($required_fields_count >= $required_fields_min) {
      // the count can be > 4 if conditional tags are used to combine a form for single and multiple prices
      return $format;
   } else {
      $res = __('Not all required fields are present in the form.', 'events-made-easy');
      if ($eme_captcha_for_booking)
	      $res.= '<br />'.__("Also check that the placeholder #_CAPTCHA is present in the form.",'events-made-easy');
      return "<div id='message' class='eme-rsvp-message'>$res</div>";
   }
}

function eme_replace_membership_formfields_placeholders($membership,$member,$format="",$return_postfieldname=0) {
   $membership_id=$membership['membership_id'];

   $is_admin=is_admin();
   $current_user = wp_get_current_user();
   if ($is_admin && isset($member['member_id'])) {
      $editing_from_backend=1;
   } else {
      $editing_from_backend=0;
   }

   $bookerLastName="";
   $bookerFirstName="";
   $bookerAddress1="";
   $bookerAddress2="";
   $bookerCity="";
   $bookerState="";
   $bookerZip="";
   $bookerCountry="";
   $bookerEmail="";
   $bookerPhone="";
   $bookedSeats=0;
   $massmail=NULL;

   // don't fill out the basic info if in the backend, but do it only if in the frontend
   if (is_user_logged_in() && !$is_admin) {
      $bookerLastName=$current_user->user_lastname;
      if (empty($bookerLastName))
               $bookerLastName=$current_user->display_name;
      $bookerFirstName=$current_user->user_firstname;
      $bookerEmail=$current_user->user_email;
      $bookerPhone=get_user_meta($current_user->ID, 'eme_phone', true);
   }

   if ($editing_from_backend) {
      $person = eme_get_person ($member['person_id']);
      $bookerLastName = eme_esc_html($person['lastname']);
      $bookerFirstName = eme_esc_html($person['firstname']);
      $bookerAddress1 = eme_esc_html($person['address1']);
      $bookerAddress2 = eme_esc_html($person['address2']);
      $bookerCity = eme_esc_html($person['city']);
      $bookerState = eme_esc_html($person['state']);
      $bookerZip = eme_esc_html($person['zip']);
      $bookerCountry = eme_esc_html($person['country']);
      $bookerEmail = eme_esc_html($person['email']);
      $bookerPhone = eme_esc_html($person['phone']);
      $massmail = intval($person['massmail']);
   }

   // when editing an existing member via backend (not a new)
   // we disable the editing of person info
   if ($editing_from_backend) {
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }

   if (empty($format)) {
	   return;
   }

   // check which fields are used in the event definition for dynamic data
   $eme_dyndatafields=array();
   if (isset($membership['properties']['dyndata'])) {
      foreach ($membership['properties']['dyndata'] as $dynfield) {
         $eme_dyndatafields[]=$dynfield['field'];
      }
   }

   $eme_captcha_for_booking=get_option('eme_captcha_for_booking') && !$is_admin;
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   $required_fields_count = 0;
   // We need at least #_LASTNAME, #_FIRSTNAME, #_EMAIL and #_SUBMIT
   $required_fields_min = 4;
   if ($eme_captcha_for_booking)
	   $required_fields_min++;

   // now the normal placeholders
   preg_match_all("/#(REQ)?_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
 
   # first we check if people desire dynamic pricing on it's own, if not: we set the relevant price class to empty
   if (in_array('#_DYNAMICPRICE',$placeholders[0])) {
           $dynamic_price_class="class='dynamicprice'";
           $dynamic_price_class_basic='dynamicprice';
   } else {
           $dynamic_price_class='';
           $dynamic_price_class_basic='';
   }

   # check also if dynamic data is requested
   if (in_array('#_DYNAMICDATA',$placeholders[0]) && !empty($eme_dyndatafields)) {
	   $dynamic_data_wanted=1;
   } else {
	   $dynamic_data_wanted=0;
   }

   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $required_att="";
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
         $required_att="required='required'";
      }

      // check for dynamic field class
      if ($dynamic_data_wanted && in_array($result, $eme_dyndatafields)) {
	   $dynamic_field_class="class='dynamicupdates'";
	   $dynamic_field_class_basic='dynamicupdates';
      } else {
	   $dynamic_field_class="class='nodynamicupdates'";
	   $dynamic_field_class_basic='nodynamicupdates';
      }

      $var_prefix='';
      $var_postfix='';
      $postvar_arr=array();

      if (preg_match('/#_NAME|#_LASTNAME/', $result)) {
	 $fieldname="${var_prefix}lastname${var_postfix}";
	 array_push($postvar_arr,"lastname");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input required='required' type='text' name='$fieldname' value='$bookerLastName' $readonly $dynamic_field_class />";
	 $required_fields_count++;
      } elseif (preg_match('/#_FIRSTNAME/', $result)) {
	 $fieldname="${var_prefix}firstname${var_postfix}";
	 array_push($postvar_arr,"firstname");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input required='required' type='text' name='$fieldname' value='$bookerFirstName' $readonly $dynamic_field_class />";
	 $required_fields_count++;
      } elseif (preg_match('/#_ADDRESS1/', $result)) {
	 $fieldname="${var_prefix}address1${var_postfix}";
	 array_push($postvar_arr,"address1");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerAddress1' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_ADDRESS2/', $result)) {
	 $fieldname="${var_prefix}address2${var_postfix}";
	 array_push($postvar_arr,"address2");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerAddress2' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_CITY/', $result)) {
	 $fieldname="${var_prefix}city${var_postfix}";
	 array_push($postvar_arr,"city");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerCity' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_STATE/', $result)) {
	 $fieldname="${var_prefix}state${var_postfix}";
	 array_push($postvar_arr,"state");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerState' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_ZIP/', $result)) {
	 $fieldname="${var_prefix}zip${var_postfix}";
	 array_push($postvar_arr,"zip");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerZip' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_COUNTRY/', $result)) {
	 $fieldname="${var_prefix}country${var_postfix}";
	 array_push($postvar_arr,"country");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='text' name='$fieldname' value='$bookerCountry' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_EMAIL|#_HTML5_EMAIL/', $result)) {
	 $fieldname="${var_prefix}email${var_postfix}";
	 array_push($postvar_arr,"email");
	 if ($return_postfieldname) return $postvar_arr;
	 if ($is_admin)
		 $replacement = "<input type='email' name='$fieldname' value='$bookerEmail' $readonly $dynamic_field_class />";
	 else
		 $replacement = "<input required='required' type='email' name='$fieldname' value='$bookerEmail' $readonly $dynamic_field_class />";
	 $required_fields_count++;
      } elseif (preg_match('/#_PHONE|#_HTML5_PHONE/', $result)) {
	 $fieldname="${var_prefix}phone${var_postfix}";
	 array_push($postvar_arr,"phone");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = "<input $required_att type='tel' name='$fieldname' value='$bookerPhone' $readonly $dynamic_field_class/>";
      } elseif (preg_match('/#_OPT_OUT/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 1;
	 $fieldname="${var_prefix}massmail${var_postfix}";
	 array_push($postvar_arr,"massmail");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = eme_ui_select_binary($selected_massmail,$fieldname,0,$dynamic_field_class_basic, $readonly);
      } elseif (preg_match('/#_OPT_IN/', $result)) {
	 $selected_massmail = (isset($massmail)) ? $massmail : 0;
	 $fieldname="${var_prefix}massmail${var_postfix}";
	 array_push($postvar_arr,"massmail");
	 if ($return_postfieldname) return $postvar_arr;
         $replacement = eme_ui_select_binary($selected_massmail,$fieldname,0,$dynamic_field_class_basic, $readonly);
      } elseif (preg_match('/#_DYNAMICPRICE$/', $result)) {
	 $replacement = "<span id='eme_calc_memberprice'></span>";
      } elseif (preg_match('/#_DYNAMICDATA$/', $result)) {
	 if (isset($membership['properties']['dyndata']) && !empty($membership['properties']['dyndata']))
                 $replacement = "<div id='eme_dyndata'></div>";
      } elseif (preg_match('/#_CAPTCHA$/', $result)) {
         if ($eme_captcha_for_booking) {
		 $captcha_url=eme_captcha_url("eme_add_member");
		 $replacement = "<img id='eme_captcha_img' src='$captcha_url'><br /><input required='required' type='text' name='captcha_check' autocomplete='off' class='nodynamicupdates' />";
		 $required_fields_count++;
	 }
      } elseif (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
	 $field_id = $formfield['field_id'];
         $fieldname="${var_prefix}FIELD".$field_id.$var_postfix;
	 array_push($postvar_arr,"FIELD".$field_id);
	 if ($return_postfieldname) return $postvar_arr;
         $entered_val = "";
         if ($editing_from_backend) {
            $answers = eme_get_member_answers($member['member_id']);
            foreach ($answers as $answer) {
               if ($answer['field_id'] == $field_id) {
                  // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
                  // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
                  $entered_val = $answer['answer'];
                  if (eme_is_multi($entered_val)) {
                     $entered_val = eme_convert_multi2array($entered_val);
                  }
               }
            }
         }
         if ($formfield['extra_charge'])
                 $replacement = eme_get_formfield_html($field_id,$fieldname,$entered_val,$required,$dynamic_price_class_basic);
         else
                 $replacement = eme_get_formfield_html($field_id,$fieldname,$entered_val,$required,$dynamic_field_class_basic);

      } elseif (preg_match('/#_SUBMIT\{(.+)\}/', $result, $matches)) {
	 if ($editing_from_backend)
	      $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".__('Update member','events-made-easy')."' />";
	 else
	      $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".eme_trans_esc_html($matches[1])."' />";
	 $required_fields_count++;

      } elseif (preg_match('/#_SUBMIT$/', $result)) {
         if ($editing_from_backend)
            $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".__('Update member','events-made-easy')."' />";
         else
            $replacement = "<img id='loading_gif' src='".EME_PLUGIN_URL."images/spinner.gif' style='display:none;'><input name='eme_submit_button' class='eme_submit_button' type='submit' value='".__('Become member','events-made-easy')."' />";
	 $required_fields_count++;
      } else {
         $found = 0;
      }

      if ($found) {
         // $format = str_replace($orig_result, $replacement ,$format );
         // only replace first found occurence, this helps to e.g. replace 2 occurences of #_DISCOUNT by 2 different things
         // preg_replace could do it too, but is less performant
	 $pos = strpos($format, $orig_result);
	 if ($pos !== false) {
		 $format = substr_replace($format, $replacement, $pos, strlen($orig_result));
	 }
      }
   }

   $format = eme_replace_membership_placeholders($format,$membership);
   if ($required_fields_count >= $required_fields_min) {
      return $format;
   } else {
      $res = __('Not all required fields are present in the form.', 'events-made-easy');
      if ($eme_captcha_for_booking)
	      $res.= '<br />'.__("Also check that the placeholder #_CAPTCHA is present in the form.",'events-made-easy');
      return "<div id='message' class='eme-rsvp-message'>$res</div>";
   }
   return $format;
}

function eme_find_required_formfields ($format) {
   preg_match_all("/#REQ_?[A-Za-z0-9_]+(\{.*?\})?/", $format, $placeholders);
   usort($placeholders[0],'eme_sort_stringlenth');
   // #_NAME and #REQ_NAME should be using _LASTNAME
   $result=preg_replace("/_NAME/","_LASTNAME",$placeholders[0]);
   // We just want the fieldnames: FIELD1, FIELD2, ... like they are POST'd via the form
   $result=preg_replace("/#REQ_|\{|\}/","",$result);
   // just to be sure: remove leading zeros in the names: FIELD01 should be FIELD1
   $result=preg_replace("/FIELD0+/","FIELD",$result);
   return $result;
}

function eme_dyndata_adminform($eme_data,$templates_array) {
   $eme_dyndata_conditions=eme_get_dyndata_conditions();
   ?>
   <div id="div_dyndata">
      <b><?php _e('Dynamically show fields based on a number of conditions','events-made-easy'); ?></b>
      <table class="eme_dyndata">
         <thead>
            <tr>
               <td><strong><?php _e('Field','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Condition','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Condition value','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Header template','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Template','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Footer template','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Repeat','events-made-easy'); ?></strong></td>
               <td><strong><?php _e('Grouping index','events-made-easy'); ?></strong></td>
            </tr>
         </thead>    
         <tbody id="eme_dyndata_body">
            <?php
            if(!is_array($eme_data) || count($eme_data) == 0) {
		 $info=array('field'=>'','condval'=>'','condition'=>'','template_id_header'=>0,'template_id'=>0,'template_id_footer'=>0,'repeat'=>0,'grouping'=>1);
		 $eme_data[1]=$info;
		 $required="";
	    } else {
		 $required="required='required'";
	    }
            foreach( $eme_data as $count => $info){
		  // to account for older conditions that didn't have the field-param
		  if (!isset($info['field'])) {
			  $info['field']='';$info['condval']='';$info['condition']='';$info['template_id_header']=0;$info['template_id']=0;$info['template_id_footer']=0;$info['repeat']=0;$info['grouping']=1;
		  };
		  if (!isset($info['grouping'])) $info['grouping']=$count;
                  ?>
                  <tr id="eme_dyndata_<?php echo $count ?>">
                     <td>
			<input <?php echo $required; ?> id="eme_dyndata_<?php echo $count; ?>_field" name="eme_dyndata_<?php echo $count; ?>_field" size="12" value="<?php echo $info['field']; ?>">
                     </td>
                     <td>
			<?php echo eme_ui_select($info['condition'],"eme_dyndata_".$count."_condition",$eme_dyndata_conditions); ?>
                     </td>
                     <td>
			<input <?php echo $required; ?> id="eme_dyndata_<?php echo $count; ?>_condval" name="eme_dyndata_<?php echo $count; ?>_condval" size="12" value="<?php echo $info['condval']; ?>">
                     </td>
                     <td>
			<?php echo eme_ui_select($info['template_id_header'],"eme_dyndata_".$count."_template_id_header",$templates_array); ?>
                     </td>
                     <td>
			<?php echo eme_ui_select($info['template_id'],"eme_dyndata_".$count."_template_id",$templates_array); ?>
                     </td>
                     <td>
			<?php echo eme_ui_select($info['template_id_footer'],"eme_dyndata_".$count."_template_id_footer",$templates_array); ?>
                     </td>
                     <td>
			<?php echo eme_ui_select_binary($info['repeat'],"eme_dyndata_".$count."_repeat"); ?>
                        <a href="#"><?php _e('Remove','events-made-easy'); ?></a>
                     </td>
                     <td>
			<input <?php echo $required; ?> id="eme_dyndata_<?php echo $count; ?>_grouping" name="eme_dyndata_<?php echo $count; ?>_grouping" size="5" value="<?php echo $info['grouping']; ?>">
                     </td>
                  </tr>
            <?php
            }
            ?>
         </tbody>
         <tfoot>
            <tr>
               <td colspan="2"><a href="#" id="eme_dyndata_add_tag"><?php _e('Add new condition','events-made-easy'); ?></a></td>
            </tr>
         </tfoot>
      </table>
      <p class='eme_smaller'>
      <?php _e('This will additionally show the selected template in the form if the condition is met.','events-made-easy'); ?>
	    <br />
      <?php _e("The 'Field' parameter is to be filled out with any valid placeholder allowed in the form.",'events-made-easy'); ?>
	    <br />
      <?php _e("The 'Grouping index' parameter should be a unique index per condition. This is used to set/retrieve all the entered info based on this condition in the database (so once set, always keep it to the same value for that condition)",'events-made-easy'); ?>
	    <br />
      <?php _e("The selected template will be shown several times if the repeat option is used (based on the number of times the field is different from the condition value. This is not used for the 'equal to' condition selector.",'events-made-easy'); ?>
	    <br />
      <?php _e('The selected template can contain html and also have placeholders for custom form fields (no other placeholders allowed).','events-made-easy'); ?>
      <?php _e('Use the placeholder #_DYNAMICDATA to show the dynamic forms in your form.','events-made-easy'); ?>
      </p>
   </div>
<?php
}

function eme_handle_dyndata_post_adminform() {
      $eme_dyndata = array();
      $biggest_grouping_seen=0;
      $groupings_seen=array();
      for($i=1 ; isset($_POST["eme_dyndata_{$i}_template_id"]) ; $i++ ) {
         if (($_POST["eme_dyndata_{$i}_template_id"])>0) {
                 $eme_dyndata[$i]['field']= $_POST["eme_dyndata_{$i}_field"];
                 $eme_dyndata[$i]['condition']= $_POST["eme_dyndata_{$i}_condition"];
                 $eme_dyndata[$i]['template_id']= intval($_POST["eme_dyndata_{$i}_template_id"]);
                 if (isset($_POST["eme_dyndata_{$i}_repeat"]) && $_POST["eme_dyndata_{$i}_repeat"]==1) {
                         $eme_dyndata[$i]['repeat']= intval($_POST["eme_dyndata_{$i}_repeat"]);
                         $eme_dyndata[$i]['condval']= intval($_POST["eme_dyndata_{$i}_condval"]);
                 } else {
                         $eme_dyndata[$i]['repeat']= 0;
                         $eme_dyndata[$i]['condval']= $_POST["eme_dyndata_{$i}_condval"];
                 }
                 if (isset($_POST["eme_dyndata_{$i}_template_id_header"]))
                         $eme_dyndata[$i]['template_id_header']= intval($_POST["eme_dyndata_{$i}_template_id_header"]);
                 else
                         $eme_dyndata[$i]['template_id_header']= 0;
                 if (isset($_POST["eme_dyndata_{$i}_template_id_footer"]))
                         $eme_dyndata[$i]['template_id_footer']= intval($_POST["eme_dyndata_{$i}_template_id_footer"]);
                 else
                         $eme_dyndata[$i]['template_id_footer']= 0;
                 if (isset($_POST["eme_dyndata_{$i}_grouping"])) {
			 // to make sure people don't use 2 times the same id
			 $grouping=intval($_POST["eme_dyndata_{$i}_grouping"]);
			 if (in_array($grouping,$groupings_seen)) {
				 $eme_dyndata[$i]['grouping']=$biggest_grouping_seen+1;
				 $biggest_grouping_seen++;
				 $groupings_seen[]=$biggest_grouping_seen;
			 } else {
				 $eme_dyndata[$i]['grouping']= $grouping;
				 $groupings_seen[]=$grouping;
			 }
			 if ($biggest_grouping_seen<$grouping) $biggest_grouping_seen=$grouping;
		 } else {
                         $eme_dyndata[$i]['grouping']=$biggest_grouping_seen+1;
			 $biggest_grouping_seen++;
			 $groupings_seen[]=$biggest_grouping_seen;
		 }
         }
      }
      return $eme_dyndata;
}

add_action( 'wp_ajax_eme_formfields_list', 'eme_ajax_formfields_list' );
add_action( 'wp_ajax_eme_manage_formfields', 'eme_ajax_manage_formfields' );

function eme_ajax_formfields_list() {
   global $wpdb;
   $table = $wpdb->prefix.FORMFIELDS_TBNAME;
   $used_formfield_ids = eme_get_used_formfield_ids();
   $jTableResult = array();
   $search_type = isset($_REQUEST['search_type']) ? eme_sanitize_request($_REQUEST['search_type']) : '';
   $where ='';
   $where_arr = array();
   if(!empty($search_type)) {
      $where_arr[] = "(field_type = '$search_type')";
   }
   if ($where_arr)
      $where = "WHERE ".implode(" AND ",$where_arr);

   if (current_user_can( get_option('eme_cap_forms'))) {
      $sql = "SELECT COUNT(*) FROM $table $where";
      $recordCount = $wpdb->get_var($sql);
      $start=intval($_REQUEST["jtStartIndex"]);
      $pagesize=intval($_REQUEST["jtPageSize"]);
      $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
      $sql="SELECT * FROM $table $where $sorting LIMIT $start,$pagesize";
      $rows=$wpdb->get_results($sql,ARRAY_A);
      $res=array();
      foreach ($rows as $key=>$row) {
	      $rows[$key]['field_type']=eme_get_fieldtype($row['field_type']);
	      $rows[$key]['field_required']=($row['field_required']==1) ? __('Yes'): __('No');
	      $rows[$key]['field_purpose']=eme_get_fieldpurpose($row['field_purpose']);
	      $rows[$key]['extra_charge']=($row['extra_charge']==1) ? __('Yes'): __('No');
	      $rows[$key]['used']=in_array($row['field_id'],$used_formfield_ids) ? __('Yes'): __('No');
	      $rows[$key]['edit_link']="<a href='".wp_nonce_url(admin_url("admin.php?page=eme-formfields&amp;eme_admin_action=edit_formfield&amp;field_id=".$row['field_id']),'eme_formfields','eme_admin_nonce')."'><img src='".EME_PLUGIN_URL."images/edit.png' alt='".__('Edit','events-made-easy')."'></a>";
      }
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = $recordCount;
      $jTableResult['Records'] = $rows;
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
   }
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_manage_formfields() {
   check_ajax_referer('eme_formfields','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteFormfields':
              eme_ajax_record_delete(FORMFIELDS_TBNAME, 'eme_cap_forms', 'field_id');
              break;
      }
   }
   wp_die();
}

?>
