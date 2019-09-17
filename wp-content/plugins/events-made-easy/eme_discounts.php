<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_discount() {
   $discount = array(
   'name' => '',
   'description' => '',
   'type' => 1,
   'value' => 0,
   'coupon' => '',
   'dgroup' => '',
   'expire' => '',
   'strcase' => 1,
   'count' => 0,
   'maxcount' => 0
   );
   return $discount;
}

function eme_new_discountgroup() {
   $discountgroup = array(
   'name' => '',
   'description' => '',
   'maxdiscounts' => 0
   );

   return $discountgroup;
}

function eme_discounts_page() {      
   
   if (!current_user_can( get_option('eme_cap_discounts')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to manage discounts!','events-made-easy');
      eme_categories_table_layout($message);
      return;
   }
  
   $message = '';
   $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

   // handle possible ations
   if (isset($_POST['eme_admin_action'])) {
      check_admin_referer('eme_discounts','eme_admin_nonce');
      if ($_POST['eme_admin_action'] == "do_importdiscounts" && isset($_FILES['eme_csv']) && current_user_can(get_option('eme_cap_cleanup'))) {
	 $inserted=0;
	 $errors=0;
	 $error_msg='';
	 //validate whether uploaded file is a csv file
         if (!empty($_FILES['eme_csv']['name']) && in_array($_FILES['eme_csv']['type'],$csvMimes)) {
		 if (is_uploaded_file($_FILES['eme_csv']['tmp_name'])) {
			 $handle = fopen($_FILES['eme_csv']['tmp_name'], "r");
			 // first line is the column headers
			 $headers = array_map('strtolower', fgetcsv($handle));
			 // check required columns
			 if (!in_array('name',$headers)||!in_array('type',$headers)||!in_array('coupon',$headers)||!in_array('value',$headers)) {
				 $message = __("Not all required fields present.",'events-made-easy');
			 } else {
				 while (($row = fgetcsv($handle)) !== FALSE) {
					 $discount = array_combine($headers, $row);
					 $res = eme_db_insert_discount($discount);
					 if ($res) {
						 $inserted++;
					 } else {
						 $errors++;
						 $error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					 }
				 }
				 $message = sprintf(__('Import finished: %d inserts, %d errors','events_made_eady'),$inserted,$errors);
				 if ($errors) $message.='<br />'.$error_msg;
			 }
			 fclose($handle);
		 } else {
			 $message = __('Problem detected while uploading the file','events_made_eady');
		 }
	 } else {
		 $message = sprintf(__('No CSV file detected: %s','events_made_eady'),$_FILES['eme_csv']['type']);
	 }
      } elseif ($_POST['eme_admin_action'] == "do_importdgroups" && isset($_FILES['eme_csv']) && current_user_can(get_option('eme_cap_cleanup'))) {
	 $inserted=0;
	 $errors=0;
	 $error_msg='';
	 //validate whether uploaded file is a csv file
         if (!empty($_FILES['eme_csv']['name']) && in_array($_FILES['eme_csv']['type'],$csvMimes)) {
		 if (is_uploaded_file($_FILES['eme_csv']['tmp_name'])) {
			 $handle = fopen($_FILES['eme_csv']['tmp_name'], "r");
			 // first line is the column headers
			 $headers = array_map('strtolower', fgetcsv($handle));
			 // check required columns
			 if (!in_array('name',$headers)) {
				 $message = __("Not all required fields present.",'events-made-easy');
			 } else {
				 while (($row = fgetcsv($handle)) !== FALSE) {
					 $discountgroup = array_combine($headers, $row);
					 $res = eme_db_insert_dgroup($discountgroup);
					 if ($res) {
						 $inserted++;
					 } else {
						 $errors++;
						 $error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					 }
				 }
				 $message = sprintf(__('Import finished: %d inserts, %d errors','events_made_eady'),$inserted,$errors);
				 if ($errors) $message.='<br />'.$error_msg;
			 }
			 fclose($handle);
		 } else {
			 $message = __('Problem detected while uploading the file','events_made_eady');
		 }
	 } else {
		 $message = sprintf(__('No CSV file detected: %s','events_made_eady'),$_FILES['eme_csv']['type']);
         }
      }
   }
   
   // now that we handled possible ations, let's show the wanted screen
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "discounts") { 
      eme_manage_discounts_layout($message);
      return;
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "dgroups") { 
      eme_manage_dgroups_layout($message);
      return;
   }
   eme_discounts_main_layout();
}

function eme_discounts_main_layout() {
   $nonce_field = wp_nonce_field('eme_discounts','eme_admin_nonce',false,false);
   $discounts_destination = admin_url("admin.php?page=eme-discounts&amp;eme_admin_action=discounts");
   $dgroups_destination = admin_url("admin.php?page=eme-discounts&amp;eme_admin_action=dgroups");
   $html = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h1>".__('Discounts', 'events-made-easy')."</h1>
   ";
         
   $html .= "<h2>".__('Manage discounts', 'events-made-easy')."</h2>";
   $html .= "<a href='$discounts_destination'>".__("Manage discounts",'events-made-easy')."</a><br />";
   $html .= "<h2>".__('Manage discountgroups', 'events-made-easy')."</h2>";
   $html .= "<a href='$dgroups_destination'>".__("Manage discountgroups",'events-made-easy')."</a><br />";
   echo $html;  
}

function eme_manage_discounts_layout($message="") {
   $nonce_field = wp_nonce_field('eme_discounts','eme_admin_nonce',false,false);
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
         
         <div id="discounts-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Discounts', 'events-made-easy') ?></h1>

   <?php if (current_user_can(get_option('eme_cap_cleanup'))) { ?>
   <span class="eme_import_form_img">
   <?php _e('Click on the icon to show the import form','events-made-easy'); ?>
   <img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="div_import" style="cursor: pointer; vertical-align: middle; ">
   </span>
   <div id='div_import' name='div_import' style='display:none;'>
   <form id='discount-import' method='post' enctype='multipart/form-data' action='#'>
   <?php echo $nonce_field; ?>
   <input type="file" name="eme_csv" />
   <input type="hidden" name="eme_admin_action" value="do_importdiscounts" />
   <input type="submit" value="<?php _e ( 'Import','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   <?php _e('If you want, use this to import discounts into the database', 'events-made-easy'); ?>
   </form>
   </div>
   <br />
   <?php } ?>
   <br />
   <form id='discounts-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteDiscounts"><?php _e ( 'Delete selected discounts','events-made-easy'); ?></option>
   </select>
   <button id="DiscountsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="DiscountsTableContainer"></div>
   </form>
      </div> 
   </div>
   <?php
}

function eme_manage_dgroups_layout($message="") {
   $nonce_field = wp_nonce_field('eme_discounts','eme_admin_nonce',false,false);
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
         
	 <div id="discountgroups-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Discountgroups', 'events-made-easy') ?></h1>

   <?php if (current_user_can(get_option('eme_cap_cleanup'))) { ?>
   <span class="eme_import_form_img">
   <?php _e('Click on the icon to show the import form','events-made-easy'); ?>
   <img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="div_import" style="cursor: pointer; vertical-align: middle; ">
   </span>
   <div id='div_import' name='div_import' style='display:none;'>
   <form id='discountgroups-import' method='post' enctype='multipart/form-data' action='#'>
   <?php echo $nonce_field; ?>
   <input type="file" name="eme_csv" />
   <input type="hidden" name="eme_admin_action" value="do_importdgroups" />
   <input type="submit" value="<?php _e ( 'Import','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   <?php _e('If you want, use this to import discountgroups into the database', 'events-made-easy'); ?>
   </form>
   </div>
   <br />
   <?php } ?>
   <br />
   <form id='discountgroups-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteDiscountGroups"><?php _e ( 'Delete selected discountgroups','events-made-easy'); ?></option>
   </select>
   <button id="DiscountGroupsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="DiscountGroupsTableContainer"></div>
   </form>
   </div> 
   </div>
   <?php
}

function eme_booking_discount($event,$booking,$do_update=1) {
   $total_discount=0;
   $discount_id=0;
   $discountgroup_id=0;
   $event_id=$event['event_id'];
   if (is_admin()) {
      if (isset($_POST['DISCOUNT']) && !empty($_POST['DISCOUNT']))
         $total_discount=sprintf("%01.2f",$_POST['DISCOUNT']);
   } elseif ($event['event_properties']['rsvp_discountgroup']) {
      $discount_group = eme_get_discountgroup_by_name($event['event_properties']['rsvp_discountgroup']);
      $discountgroup_id=$discount_group['id'];
      if (!$discountgroup_id) return false;

      $discount_ids = eme_get_discountids_by_groupname($event['event_properties']['rsvp_discountgroup']);
      $group_count=0;
      $max_discounts=$discount_group['maxdiscounts'];

      $applied_discountids=array();
      foreach ($discount_ids as $id) {
	 // a discount can only be applied once
         if (isset($applied_discountids['id'])) continue;
         if ($max_discounts==0 || $group_count<$max_discounts) {
            $discount = eme_get_discount($id);
            if ($res=eme_calc_booking_discount($discount,$booking)) {
               $total_discount+=$res;
               $group_count++;
               $applied_discountids[$id]=1;
            }
         }
      }
   } elseif ($event['event_properties']['rsvp_discount']) {
      $discount = eme_get_discount_by_name($event['event_properties']['rsvp_discount']);
      $discount_id=$discount['id'];
      if ($res=eme_calc_booking_discount($discount,$booking))
         $total_discount=$res;
   }

   if ($total_discount>0 && $do_update) {
      global $wpdb;
      $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
      $where = array();
      $fields = array();
      $where['booking_id'] = $booking['booking_id'];
      $fields['discount'] = $total_discount;
      $fields['discountid'] = $discount_id;
      $fields['dgroupid'] = $discountgroup_id;
      $wpdb->update($bookings_table, $fields, $where);
   }
   return $total_discount;
}

function eme_get_discounts() {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTS_TBNAME;
   $sql = "SELECT * FROM $table";
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_get_dgroups() {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTGROUPS_TBNAME;
   $sql = "SELECT * FROM $table";
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_db_insert_discount($line) {
	global $wpdb;
	$table = $wpdb->prefix.DISCOUNTS_TBNAME;

	$discount=eme_new_discount();
	// we only want the columns that interest us
	$keys=array_intersect_key($line,$discount);
	$new_line=array_merge($discount,$keys);
	return $wpdb->insert($table, $new_line);
}

function eme_db_insert_dgroup($line) {
	global $wpdb;
	$table = $wpdb->prefix.DISCOUNTGROUPS_TBNAME;

	$discount=eme_new_discountgroup();
	// we only want the columns that interest us
	$keys=array_intersect_key($line,$discount);
	$new_line=array_merge($discount,$keys);
	return $wpdb->insert($table, $new_line);
}

function eme_get_discountgroup($id) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTGROUPS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE id = %s",$id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_discountgroup_by_name($name) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTGROUPS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE name = %s",$name);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_discountids_by_groupname($name) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTS_TBNAME;
   $sql = $wpdb->prepare("SELECT id FROM $table WHERE dgroup = %s",$name);
   return $wpdb->get_col($sql);
}

function eme_increase_discount_count($id) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTS_TBNAME;
   $sql = $wpdb->prepare("UPDATE $table SET count=count+1 WHERE id = %d",$id);
   return $wpdb->query($sql);
}

function eme_get_discount($id) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE id = %d",$id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_discount_by_name($name) {
   global $wpdb;
   $table = $wpdb->prefix.DISCOUNTS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE name = %s",$name);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_calc_booking_discount($discount,$booking) {
   // check if not expired
   if ($discount['expire']) {
      global $eme_timezone;
      $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
      $eme_expire_obj = new ExpressiveDate($discount['expire'],$eme_timezone);
      if ($eme_expire_obj->lessThan($eme_date_obj_now))
         return false;
   }

   // check if not max usage count reached
   if ($discount['maxcount']>0 && $discount['count']>=$discount['maxcount'])
      return false;

   $event_id=$booking['event_id'];

   $res=0;
   // discount type=code: via own filters, based on the discount name
   if ($discount['type']==3 && has_filter('eme_discount_'.$discount['name'])) {
         $res=apply_filters('eme_discount_'.$discount['name'],$booking);
         if ($res)
            eme_increase_discount_count($discount['id']);
         return sprintf("%01.2f",$res);
   }

   if (isset($_POST['bookings'][$event_id])) {
      foreach($_POST['bookings'][$event_id] as $key =>$value) {
         if (preg_match('/^DISCOUNT/', $key, $matches)) {
            $res=eme_calc_discount($discount,$booking,$value);
            if ($res) break;
         }
      }
   }

   // if the discount matches, increase the usage count
   if ($res) {
      eme_increase_discount_count($discount['id']);
      return $res;
   }
   return 0;
}

function eme_calc_discount($discount,$booking,$coupon) {
   $res=0;
   if  (($discount['strcase'] && strcmp($discount['coupon'],$coupon)===0) ||
       (!$discount['strcase'] && strcasecmp($discount['coupon'],$coupon)===0)) {
      if ($discount['type']==1) {
         $res=$discount['value'];
      } elseif ($discount['type']==2) {
         // eme_get_total_booking_price by default takes the discount into account
         // not that it matters, as for now the discount is 0, but let's make sure
         $ignore_discount=1;
         $price=eme_get_total_booking_price($booking,$ignore_discount);
         $res=sprintf("%01.2f",$price*$discount['value']/100);
      } elseif ($discount['type']==4) {
         $res=$discount['value']*$booking['booking_seats'];
      }
   }
   return $res;
}

function eme_discounttype_to_text($type) {
   switch($type) {
      case 1:
         return __('Fixed','events-made-easy');break;
      case 2:
         return __('Percentage','events-made-easy');break;
      case 3:
         return __('Code','events-made-easy');break;
      case 4:
         return __('Fixed per seat','events-made-easy');break;
   }
}

add_action( 'wp_ajax_eme_discount_list', 'eme_ajax_discount_list' );
add_action( 'wp_ajax_eme_manage_discounts', 'eme_ajax_manage_discounts' );
add_action( 'wp_ajax_eme_discount_edit', 'eme_ajax_discount_edit' );
add_action( 'wp_ajax_eme_discountgroups_list', 'eme_ajax_discountgroups_list' );
add_action( 'wp_ajax_eme_manage_discountgroups', 'eme_ajax_manage_discountgroups' );
add_action( 'wp_ajax_eme_discountgroups_edit', 'eme_ajax_discountgroups_edit' );

function eme_ajax_discount_list() {
   eme_ajax_record_list(DISCOUNTS_TBNAME, 'eme_cap_discounts');
}
function eme_ajax_discountgroups_list() {
   eme_ajax_record_list(DISCOUNTGROUPS_TBNAME, 'eme_cap_discounts');
}
function eme_ajax_manage_discounts() {
   check_ajax_referer('eme_discounts','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteDiscounts':
              eme_ajax_record_delete(DISCOUNTS_TBNAME, 'eme_cap_discounts', 'id');
              break;
      }
   }
   wp_die();
}
function eme_ajax_manage_discountgroups() {
   check_ajax_referer('eme_discounts','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteDiscountGroups':
              eme_ajax_record_delete(DISCOUNTGROUPS_TBNAME, 'eme_cap_discounts', 'id');
              break;
      }
   }
   wp_die();
}
function eme_ajax_discount_edit() {
   if (isset($_POST['id'])) {
      $discount=eme_get_discount(intval($_POST['id']));
      $update=1;
   } else {
      $discount=eme_new_discount();
      $update=0;
   }
   foreach ($discount as $key=>$val) {
      if (isset($_POST[$key]))
         $discount[$key]=eme_strip_tags($_POST[$key]);
   }
   // unchecked checkboxes don't get sent in forms
   if (!isset($_POST['strcase']))
      $discount['strcase']=0;
   if (!is_numeric($discount['type']) || $discount['type']<0 || $discount['type']>4)
      $discount['type']=1;
   if (!is_numeric($discount['strcase']) || $discount['strcase']<0 || $discount['strcase']>1)
      $discount['strcase']=1;
   if (empty($discount['expire']) || $discount['expire']=="0000-00-00")
      $discount['expire']=NULL;

   eme_ajax_record_edit(DISCOUNTS_TBNAME,'eme_cap_discounts','id',$discount,'eme_get_discount',$update);
}

function eme_ajax_discountgroups_edit() {
   if (isset($_POST['id'])) {
      $discountgroup=eme_get_discountgroup(intval($_POST['id']));
      $update=1;
   } else {
      $discountgroup=eme_new_discountgroup();
      $update=0;
   }
   foreach ($discountgroup as $key=>$val) {
      if (isset($_POST[$key]))
         $discountgroup[$key]=eme_strip_tags($_POST[$key]);
   }

   eme_ajax_record_edit(DISCOUNTGROUPS_TBNAME,'eme_cap_discounts','id',$discountgroup,'eme_get_discountgroup',$update);
}

function eme_ajax_record_list($tablename, $cap, $edit_column="", $edit_page="", $edit_nonce="") {
   global $wpdb;
   $table = $wpdb->prefix.$tablename;
   $jTableResult = array();
   // The toolbar search input
   $q = isset($_REQUEST['q'])?$_REQUEST['q']:"";
   $opt = isset($_REQUEST['opt'])?$_REQUEST['opt']:"";
   $where ='';
   $where_array = array();
   if ($q) {
	for ($i = 0; $i < count($opt); $i++) {
		$fld = esc_sql($opt[$i]);
		$where_array[] = $fld." like '%".esc_sql($q[$i])."%'";
	}
	$where = " WHERE ".implode(" AND ",$where_array);
   }
   if (current_user_can( get_option($cap))) {
      $sql = "SELECT COUNT(*) FROM $table $where";
      $recordCount = $wpdb->get_var($sql);
      $start=intval($_REQUEST["jtStartIndex"]);
      $pagesize=intval($_REQUEST["jtPageSize"]);
      $sorting=esc_sql($_REQUEST["jtSorting"]);
      $sql="SELECT * FROM $table $where ORDER BY $sorting LIMIT $start,$pagesize";
      $rows=$wpdb->get_results($sql,ARRAY_A);
      if (!empty($edit_nonce)) {
	      foreach ($rows as $key=>$row) {
		      $rows[$key]['edit_link']="<a href='".wp_nonce_url(admin_url("admin.php?page=$edit_page&amp;eme_admin_action=edit_entry&amp;$edit_column=".$row[$edit_column]),$edit_nonce,'eme_admin_nonce')."'><img src='".EME_PLUGIN_URL."images/edit.png' alt='".__('Edit','events-made-easy')."'></a>";
	      }
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

function eme_ajax_record_delete($tablename,$cap,$postvar) {
   global $wpdb;
   $table = $wpdb->prefix.$tablename;
   $jTableResult = array();

   if (current_user_can(get_option($cap)) && isset($_POST[$postvar])) {
      // check the POST var
      $ids_arr=explode(',',$_POST[$postvar]);
      if (eme_array_integers($ids_arr)) {
         $wpdb->query("DELETE FROM $table WHERE $postvar in ( ".$_POST[$postvar].")");
      }
      $jTableResult['Result'] = "OK";
      $jTableResult['htmlmessage'] = __('Records deleted!','events-made-easy');
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
      $jTableResult['htmlmessage'] = __('Access denied!','events-made-easy');
   }
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_record_edit($tablename,$cap,$id_column,$record,$record_function='',$update=0) {
   global $wpdb;
   $table = $wpdb->prefix.$tablename;
   $jTableResult = array();
   if (!$record) {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = "No such record";
      print json_encode($jTableResult);
      wp_die();
   }
   $wpdb->show_errors(false);
   if (current_user_can( get_option($cap))) {
      if ($update)
         $wpdb->update($table,$record,array($id_column => $record[$id_column]));
      else
         $wpdb->insert($table,$record);
      if ($wpdb->last_error !== '') {
         $jTableResult['Result'] = "Error";
         if ($update)
            $jTableResult['Message'] = __('Update failed: ','events-made-easy').$wpdb->last_error;
         else
            $jTableResult['Message'] = __('Insert failed: ','events-made-easy').$wpdb->last_error;
      } else {
         $jTableResult['Result'] = "OK";
         if (!$update) {
            $record_id = $wpdb->insert_id;
            if ($record_function)
               $record=$record_function($record_id);
            else
               $record[$id_column]=$record_id;
            $jTableResult['Record'] = eme_esc_html($record);
         }
      }
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
   }

   //Return result to jTable
   print json_encode($jTableResult);
   wp_die();
}

?>
