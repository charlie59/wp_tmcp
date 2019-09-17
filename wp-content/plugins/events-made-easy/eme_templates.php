<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_template() {
   $template = array(
      'name' => '',
      'description' => '',
      'format' => '',
      'type' => '',
      'properties' => array()
   );
   $template['properties'] = eme_init_template_props($template['properties']);
   return $template;
}

function eme_init_template_props($props) {
   if (!isset($props['pdf_width']))
      $props['pdf_width']=0;
   if (!isset($props['pdf_height']))
      $props['pdf_height']=0;
   if (!isset($props['pdf_size']))
      $props['pdf_size']='a4';
   if (!isset($props['pdf_orientation']))
      $props['pdf_orientation']='portrait';
   if (!isset($props['pdf_margins']))
      $props['pdf_margins']='1cm';
   return $props;
}

function eme_template_types() {
	$arr=array(
		'' => __('All','events-made-easy'),
		'event' => __('Event','events-made-easy'),
		'rsvpform' => __('RSVP form','events-made-easy'),
		'rsvpmail' => __('RSVP related mail','events-made-easy'),
		'membershipform' => __('Membership form','events-made-easy'),
		'membershipmail' => __('Membership related mail','events-made-easy'),
		'mail' => __('Generic mail','events-made-easy'),
		'shortcodes' => __('Only used in shortcodes','events-made-easy'),
		'pdf' => __('PDF template','events-made-easy')
	   );
	return $arr;
}

function eme_templates_page() {      
   global $wpdb;
   
   if (!current_user_can( get_option('eme_cap_templates')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update templates!','events-made-easy');
      eme_templates_table_layout($message);
      return;
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_entry") { 
      // edit template  
      check_admin_referer('eme_templates','eme_admin_nonce');
      eme_templates_edit_layout();
      return;
   }
   if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_template") { 
      // edit template  
      check_admin_referer('eme_templates','eme_admin_nonce');
      eme_templates_edit_layout();
      return;
   }

   // Insert/Update/Delete Record
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $validation_result = '';
   $message = '';
   if (isset($_POST['eme_admin_action'])) {
      check_admin_referer('eme_templates','eme_admin_nonce');
      if ($_POST['eme_admin_action'] == "do_edittemplate" && isset($_POST['description']) && isset($_POST['template_format']) ) {
         // template update required  
         $template = array();
         $properties = array();
         $template['name'] = eme_strip_tags($_POST['name']);
         $template['description'] = eme_strip_tags($_POST['description']);
         $template['type'] = eme_strip_tags($_POST['type']);
         $template['format'] = eme_strip_js($_POST['template_format']);
	 if (isset($_POST['properties'])) $properties = eme_strip_tags($_POST['properties']);
	 $template['properties'] = serialize(eme_init_membership_props($properties));

	 if (isset($_POST['template_id']) && intval($_POST['template_id'])>0) {
		 $validation_result = $wpdb->update( $templates_table, $template, array('id' => intval($_POST['template_id'])) );
		 if ($validation_result !== false) {
			 $message = __("Successfully edited the template.", 'events-made-easy');
		 } else {
			 $message = __("There was a problem editing your template, please try again.",'events-made-easy');
		 }
	 } else {
		 $validation_result = $wpdb->insert($templates_table, $template);
		 if ($validation_result !== false) {
			 $message = __("Successfully added the template.", 'events-made-easy');
		 } else {
			 $message = __("There was a problem adding your template, please try again.",'events-made-easy');
		 }
	 }
      } elseif ($_POST['eme_admin_action'] == "do_deletetemplate" && isset($_POST['templates'])) {
         // Delete template or multiple
         $templates = $_POST['templates'];
         if (!empty($templates) && eme_array_integers($templates)) {
               $validation_result = $wpdb->query( "DELETE FROM $templates_table WHERE id IN (". implode(",",$templates) .")" );
               if ($validation_result !== false)
                  $message = __("Successfully deleted the selected template(s).",'events-made-easy');
	       else
                  $message = __("There was a problem deleting the selected template(s), please try again.",'events-made-easy');
         } else {
            $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.",'events-made-easy');
         }
      }
   }
   eme_templates_table_layout($message);
} 

function eme_templates_table_layout($message = "") {
   global $plugin_page;

   $template_types = eme_template_types();
   $destination = admin_url("admin.php?page=$plugin_page"); 
   $nonce_field = wp_nonce_field('eme_templates','eme_admin_nonce',false,false);
   echo "
      <div class='wrap nosubsub'>
      <div id='poststuff'>
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h1>".__('Templates', 'events-made-easy')."</h1>\n ";   
         
   if($message != "") {
            echo "
            <div id='message' class='updated fade below-h1' style='background-color: rgb(255, 251, 204);'>
               <p>$message</p>
            </div>";
   }
?>
   <div class="wrap">
	 <form id="templates-new" method="post" action="<?php echo $destination; ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_template" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add template', 'events-made-easy');?>" />
         </form>
   </div>
   <br /><br />
   <form action="#" method="post">
   <?php echo eme_ui_select('','search_type',$template_types); ?>
   <button id="TemplatesLoadRecordsButton" class="button-secondary action"><?php _e('Filter templates','events-made-easy'); ?></button>
   </form>

   <form id='templates-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteTemplates"><?php _e ( 'Delete selected templates','events-made-easy'); ?></option>
   </select>
   <button id="TemplatesActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="TemplatesTableContainer"></div>
   </form>
   </div>
   </div>
   <?php
}

function eme_templates_edit_layout($message = "") {
   global $plugin_page;

   if(isset($_GET['id'])) {
	   $template_id = intval($_GET['id']);
	   $template = eme_get_template($template_id);
	   $h1_string=__('Edit template', 'events-made-easy');
	   $action_string=__('Update template', 'events-made-easy');
   } else {
	   $template_id=0;
	   $template = eme_new_template();
	   $h1_string=__('Create template', 'events-made-easy');
	   $action_string=__('Add template', 'events-made-easy');
   }
   $template_types = eme_template_types();
   $nonce_field = wp_nonce_field('eme_templates','eme_admin_nonce',false,false);
   $eme_editor_settings = eme_get_editor_settings(true,true,10);
   $orientation_array = array(
	   'portrait'  => __('Portrait','events-made-easy'),
	   'landscape' => __('Landscape','events-made-easy')
   );

   $size_array=array('custom'=>__('Custom','events-made-easy'));
   require_once("dompdf/autoload.inc.php");
   foreach (Dompdf\Adapter\CPDF::$PAPER_SIZES as $key=>$val) {
	   $size_array[$key]=strtoupper($key);
   }

   echo "
   <div class='wrap'>
      <div id='poststuff'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h1>".$h1_string."</h1>";   
      
      if($message != "") {
         echo "
      <div id='message' class='updated fade below-h1' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
      }
?> 
      <div id='ajax-response'></div>
      <form name='edit_template' id='edit_template' method='post' action='<?php echo admin_url("admin.php?page=$plugin_page"); ?>' class='validate'>
      <input type='hidden' name='eme_admin_action' value='do_edittemplate' />
      <input type='hidden' name='template_id' value='<?php echo $template_id; ?>' />
      <?php echo $nonce_field; ?>
      <table>
            <tr>
            <td><?php _e('Name', 'events-made-easy') ?></label></td>
            <td><input required='required' id='name' name='name' type='text' value='<?php echo eme_esc_html($template['name']); ?>' size='40' /></td>
            </tr>
            <tr>
            <td><?php _e('Description', 'events-made-easy') ?></label></td>
            <td><input id='description' name='description' type='text' value='<?php echo eme_esc_html($template['description']); ?>' size='40' /></td>
            </tr>
            <tr>
            <td><?php _e('Format', 'events-made-easy') ?></label></td>
            <td><?php wp_editor($template['format'],'template_format',$eme_editor_settings); ?>
            </tr>
            <tr>
            <td><?php _e('Type', 'events-made-easy') ?></label></td>
            <td><?php echo eme_ui_select($template['type'],'type',$template_types); ?>
	    <br /><?php _e("The type allows you to indicate where you want to use this template. This helps to limit the dropdown list of templates to chose from in other parts of EME.",'events-made-easy'); ?>
	    <br /><?php _e("The type 'All' means it can be selected anywhere where template selections are possible.",'events-made-easy'); ?>
            <br /><?php _e("The type 'PDF' is used for PDF templating and allows more settings concerning page size, orientation, ...",'events-made-easy'); ?>
            <br /><?php _e("If you know the template is only used in/for shortcodes, use the type 'Shortcode'.",'events-made-easy'); ?>
            </td>
            </tr>
      </table>

      <table class='form-table' id='pdf_properties'>
            <tr class='form-field'>
	    <th scope='row' valign='top'><?php _e('PDF size', 'events-made-easy'); ?></th>
	       <td><?php echo eme_ui_select($template['properties']['pdf_size'],'properties[pdf_size]',$size_array); ?><br />
		   <?php _e("If you select 'Custom', you can enter your own widht/height below.", 'events-made-easy'); ?></td>
            </tr>
            <tr class='form-field'>
	       <th scope='row' valign='top'><?php _e('PDF orientation', 'events-made-easy'); ?></th>
	       <td><?php echo eme_ui_select($template['properties']['pdf_orientation'],'properties[pdf_orientation]',$orientation_array); ?></td>
            </tr>
            <tr class='form-field'>
	       <th scope='row' valign='top'><?php _e('PDF margins', 'events-made-easy'); ?></th>
	       <td><input type='text' name='properties[pdf_margins]' id='properties[pdf_margins]' value='<?php echo eme_esc_html($template['properties']['pdf_margins']); ?>' size='40' /><br />
		 <?php _e("See <a href='https://www.w3schools.com/cssref/pr_margin.asp'>this page</a> for info on what you can enter here.", 'events-made-easy'); ?></td>
            </tr>
            <tr class='form-field template-pdf-custom'>
	       <th scope='row' valign='top'><?php _e('PDF width', 'events-made-easy'); ?></th>
	       <td><input type='text' name='properties[pdf_width]' id='properties[pdf_width]' value='<?php echo eme_esc_html($template['properties']['pdf_width']); ?>' size='40' /><br />
		 <?php _e('The width of the PDF document (in pt)', 'events-made-easy'); ?></td>
            </tr>
            <tr class='form-field template-pdf-custom'>
	       <th scope='row' valign='top'><?php _e('PDF height', 'events-made-easy'); ?></th>
	       <td><input type='text' name='properties[pdf_height]' id='properties[pdf_height]' value='<?php echo eme_esc_html($template['properties']['pdf_height']); ?>' size='40' /><br />
		 <?php _e('The heigth of the PDF document (in pt)', 'events-made-easy'); ?></td>
            </tr>
      </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='<?php echo $action_string; ?>' /></p>
      </form>
   </div>
   </div>
<?php
}

function eme_get_templates($type='') {
   global $wpdb;
   $table = $wpdb->prefix.TEMPLATES_TBNAME;
   if (!empty($type)) {
	   $sql=$wpdb->prepare("SELECT * FROM $table WHERE type='' OR type=%s ORDER BY type,description",$type);
	   return $wpdb->get_results ( $sql, ARRAY_A );
   } else {
	   return $wpdb->get_results("SELECT * FROM $table ORDER BY type,description", ARRAY_A);
   }
}

function eme_get_templates_array_by_id($type='') {
   $templates = eme_get_templates($type);
   $templates_by_id=array();
   if (is_array($templates) && count($templates)>0)
      $templates_by_id[0]='&nbsp;';
   else
      $templates_by_id[0]=__('No templates defined yet!','events-made-easy');
   foreach ($templates as $template) {
      $templates_by_id[$template['id']]=$template['name'];
   }
   return $templates_by_id;
}

function eme_get_template($template_id) {
   global $wpdb;
   $template_id = intval($template_id);
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $sql = "SELECT * FROM $templates_table WHERE id ='$template_id'";
   $template = $wpdb->get_row($sql, ARRAY_A);
   if ($template !== false) {
           $template['properties']=eme_init_template_props(unserialize($template['properties']));
           return $template;
   } else {
           return false;
   }
}

function eme_get_template_format($template_id) { 
   global $wpdb;
   $template_id = intval($template_id);
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $sql = "SELECT format FROM $templates_table WHERE id ='$template_id'";   
   return $wpdb->get_var($sql);
}

add_action( 'wp_ajax_eme_templates_list', 'eme_ajax_templates_list' );
add_action( 'wp_ajax_eme_manage_templates', 'eme_ajax_manage_templates' );
add_action( 'wp_ajax_eme_get_template', 'eme_ajax_get_template' );

function eme_ajax_templates_list() {
   global $wpdb;
   $table = $wpdb->prefix.TEMPLATES_TBNAME;
   $template_types = eme_template_types();
   $jTableResult = array();
   $search_type = isset($_REQUEST['search_type']) ? eme_sanitize_request($_REQUEST['search_type']) : '';
   $where ='';
   $where_arr = array();
   if(!empty($search_type)) {
      $where_arr[] = "(type = '$search_type')";
   }
   if ($where_arr)
      $where = "WHERE ".implode(" AND ",$where_arr);

   if (current_user_can( get_option('eme_cap_templates'))) {
      $sql = "SELECT COUNT(*) FROM $table $where";
      $recordCount = $wpdb->get_var($sql);
      $start=intval($_REQUEST["jtStartIndex"]);
      $pagesize=intval($_REQUEST["jtPageSize"]);
      $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
      $sql="SELECT * FROM $table $where $sorting LIMIT $start,$pagesize";
      $rows=$wpdb->get_results($sql,ARRAY_A);
      foreach ($rows as $key=>$row) {
	      $rows[$key]['type']=$template_types[$row['type']];
	      $rows[$key]['edit_link']="<a href='".wp_nonce_url(admin_url("admin.php?page=eme-templates&amp;eme_admin_action=edit_entry&amp;id=".$row['id']),'eme_templates','eme_admin_nonce')."'><img src='".EME_PLUGIN_URL."images/edit.png' alt='".__('Edit','events-made-easy')."'></a>";
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
function eme_ajax_manage_templates() {
   check_ajax_referer('eme_templates','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteTemplates':
              eme_ajax_record_delete(TEMPLATES_TBNAME, 'eme_cap_templates', 'id');
              break;
      }
   }
   wp_die();
}

function eme_ajax_get_template() {
   $return=array();
   if (isset($_REQUEST['template_id']) && intval($_REQUEST['template_id'])>0) {
	   $return['htmlmessage']=eme_get_template_format($_REQUEST['template_id']);
   } else {
	   $return['htmlmessage']='';
   }
   echo json_encode($return);
   wp_die();
}

?>
