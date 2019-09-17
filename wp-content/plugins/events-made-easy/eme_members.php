<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_membership() {
   global $eme_timezone;
   $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
   $today=$eme_date_obj_now->getDate();

   $membership = array(
   'name' => '',
   'description' => '',
   'type' => '', // fixed/rolling
   'start_date' => $today, // only for fixed
   'duration_count' => 1,
   'duration_period' => 'years',
   'properties' => array() 
   );
   $membership['properties'] = eme_init_membership_props($membership['properties']);

   return $membership;
}

function eme_new_member() {
   global $eme_timezone;
   $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
   $now=$eme_date_obj_now->getDateTime();

   $member = array(
   'membership_id' => 0,
   'person_id' => 0,
   'status' => 0,
   'paid' => 0,
   'extra_charge' => 0,
   'status_automatic' => 1,
   'creation_date' => $now,
   'start_date' => '0000-00-00 00:00:00',
   'end_date' => '0000-00-00 00:00:00'
   );
   return $member;
}

function eme_init_membership_props($props) {
   if (!isset($props['reminder_days'])) {
      $props['reminder_days']=0;
   } else {
      $test_arr=explode(',',$props['reminder_days']);
      if (!eme_array_integers($test_arr)) $props['reminder_days']=0;
   }
   if (!isset($props['price']))
      $props['price']=0;
   if (!isset($props['contact_id']))
      $props['contact_id']=0;
   if (!isset($props['dyndata']))
      $props['dyndata']=array();
   if (!isset($props['currency']))
      $props['currency']=get_option('eme_default_currency');

   $payment_gateways=array('use_paypal','use_2co','use_webmoney','use_fdgg','use_mollie','use_sagepay','use_worldpay','use_stripe','use_braintree','use_offline');
   foreach ($payment_gateways as $gateway) {
	   if (!isset($props[$gateway]))
		   $props[$gateway]=0;
   }
   if (!isset($props['new_subject_format']))
      $props['new_subject_format']=__('Welcome #_FIRSTNAME','events-made-easy');
   if (!isset($props['extended_subject_format']))
      $props['extended_subject_format']=__('Membership extended','events-made-easy');
   if (!isset($props['updated_subject_format']))
      $props['updated_subject_format']=__('Membership updated','events-made-easy');
   if (!isset($props['paid_subject_format']))
      $props['paid_subject_format']=__('Payment received','events-made-easy');
   if (!isset($props['reminder_subject_format']))
      $props['reminder_subject_format']=__('Expiration reminder for #_MEMBERSHIPNAME','events-made-easy');
   if (!isset($props['stop_subject_format']))
      $props['stop_subject_format']=__('Goodbye #_FIRSTNAME','events-made-easy');
   if (!isset($props['contact_new_subject_format']))
      $props['contact_new_subject_format']=__('New member signed up','events-made-easy');
   if (!isset($props['contact_stop_subject_format']))
      $props['contact_stop_subject_format']=__('Member stopped','events-made-easy');
   if (!isset($props['contact_ipn_subject_format']))
      $props['contact_ipn_subject_format']=__('Member IPN received','events-made-easy');
   if (!isset($props['contact_paid_subject_format']))
      $props['contact_paid_subject_format']=__('Member payment received','events-made-easy');

   $templates=array('member_form_tpl','payment_form_header_tpl','payment_form_footer_tpl','new_body_format_tpl','updated_body_format_tpl','extended_body_format_tpl','paid_body_format_tpl','reminder_body_format_tpl','stop_body_format_tpl','contact_new_body_format_tpl','contact_stop_body_format_tpl','contact_ipn_body_format_tpl','contact_paid_body_format_tpl','offline_payment_tpl','member_added_tpl');
   foreach ($templates as $template) {
      if (!isset($props[$template]))
         $props[$template]=0;
   }
   return $props;
}

function eme_db_insert_membership($membership) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $wpdb->show_errors(true);
   if (!is_serialized($membership['properties']))
	   $membership['properties'] = serialize($membership['properties']);
   if (!$wpdb->insert ( $table, $membership )) {
      $wpdb->print_error();
      return false;
   } else {
      return $wpdb->insert_id;
   }
}

function eme_db_insert_member($member) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $wpdb->show_errors(true);

   $member['creation_date']=current_time('mysql', false);
   if (!$wpdb->insert ( $table, $member )) {
      $wpdb->print_error();
      return false;
   } else {
      return $wpdb->insert_id;
   }
}

function eme_db_update_member($member_id,$line,$membership) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $where = array();
   $where['member_id'] = intval($member_id);

   $member=eme_get_member($member_id);
   unset($member['member_id']);
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$member);
   $new_line=array_merge($member,$keys);
   if ($membership['duration_period']=='forever')
	   $new_line['end_date']='';

   if (!empty($new_line) && $wpdb->update($table, $new_line, $where) === false) {
      return false;
   } else {
      return $member_id;
   }
}

function eme_db_update_membership($membership_id,$line) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $where = array();
   $where['membership_id'] = intval($membership_id);

   $membership=eme_get_membership($membership_id);
   unset($membership['membership_id']);
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$membership);
   $new_line=array_merge($membership,$keys);
   if (!is_serialized($new_line['properties']))
	   $new_line['properties'] = serialize($new_line['properties']);

   if (!empty($new_line) && $wpdb->update($table, $new_line, $where) === false) {
      return false;
   } else {
      return $membership_id;
   }
}

function eme_get_members($member_ids,$extra_search='') {
   global $wpdb;
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships_table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $lines=array();
   if (!empty($member_ids) && eme_array_integers($member_ids)) {
      $tmp_ids=join(",",$member_ids);
      $sql = "SELECT members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
              FROM $members_table AS members
              LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
              LEFT JOIN $people_table as people ON members.person_id=people.person_id
              WHERE members.member_id IN ($tmp_ids)";
      if (!empty($extra_search)) $sql.=" AND $extra_search";
   } else {
      $sql = "SELECT members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
              FROM $members_table AS members
              LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
              LEFT JOIN $people_table as people ON members.person_id=people.person_id
              ";
      if (!empty($extra_search)) $sql.=" WHERE $extra_search";
   }
   $lines = $wpdb->get_results($sql, ARRAY_A);
   return $lines;
}

function eme_get_memberships() {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $sql = "SELECT * FROM $table";
   $rows = $wpdb->get_results($sql, ARRAY_A);
   foreach ($rows as $key=>$membership) {
	   $membership['properties']=eme_init_membership_props(unserialize($membership['properties']));
	   $rows[$key]=$membership;
   }
   return $rows;
}

function eme_get_membership($id,$name='') {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   if (!empty($name))
	   $sql = $wpdb->prepare("SELECT * FROM $table WHERE name=%s",$name);
   else
	   $sql = $wpdb->prepare("SELECT * FROM $table WHERE membership_id=%d",$id);
   $membership = $wpdb->get_row($sql, ARRAY_A);
   if ($membership) {
	   $membership['properties']=eme_init_membership_props(unserialize($membership['properties']));
	   return $membership;
   } else {
	   return false;
   }
}

function eme_membership_types() {
   $type_array=array('fixed'=>__('Fixed startdate','events-made-easy'),
	             'rolling'=>__('Rolling period','events-made-easy')
	     );
   return $type_array;
}

function eme_membership_durations() {
   $duration_array=array(''=>'',
	   		 'days'=>__('Days','events-made-easy'),
	                 'weeks'=>__('Weeks','events-made-easy'),
	                 'months'=>__('Months','events-made-easy'),
	                 'years'=>__('Years','events-made-easy'),
	                 'forever'=>__('Forever','events-made-easy'),
	     );
   return $duration_array;
}

function eme_get_member($id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE member_id=%d",$id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_member_by_personid_membershipid($person_id,$membership_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE person_id=%d AND membership_id=%d",$person_id,$membership_id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_member_by_paymentid($id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $table WHERE payment_id=%d",$id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_delete_member($member_id) {
   global $wpdb;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE member_id = %d",$member_id);
   $wpdb->query($sql);
   $sql = $wpdb->prepare("DELETE FROM $members_table WHERE member_id = %d",$member_id);
   $wpdb->query($sql);
}

function eme_delete_membership($membership_id) {
   global $wpdb;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships_table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE member_id IN (SELECT member_id from $members_table WHERE membership_id = %d)",$membership_id);
   $wpdb->query($sql);
   $sql = $wpdb->prepare("DELETE FROM $members_table WHERE membership_id = %d",$membership_id);
   $wpdb->query($sql);
   $sql = $wpdb->prepare("DELETE FROM $memberships_table WHERE membership_id = %d",$membership_id);
   $wpdb->query($sql);
}

// the next function returns the price for a specific member
function eme_get_total_member_price($member,$ignore_discount=0) {
   $membership=eme_get_membership($member['membership_id']);
   $price=$membership['properties']['price'];

   if (!empty($member['extra_charge']))
           $price += $member['extra_charge'];
   if (!$ignore_discount && !empty($member['discount']))
           $price -= $member['discount'];
   if ($price<0) $price=0;
   return $price;
}

function eme_members_page() {
   $message="";
   if (!current_user_can( get_option('eme_cap_members')) && (isset($_POST['eme_admin_action']) || isset($_GET['eme_admin_action']))) {
      $message = __('You have no right to manage members!','events-made-easy');
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'import' && isset($_FILES['eme_csv']) && current_user_can(get_option('eme_cap_cleanup')) ) {
      check_admin_referer('eme_members','eme_admin_nonce');
      $message = eme_import_csv_members();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'import_dynamic_answers' && isset($_FILES['eme_csv']) && current_user_can(get_option('eme_cap_cleanup')) ) {
      check_admin_referer('eme_members','eme_admin_nonce');
      $message = eme_import_csv_member_dynamic_answers();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_addmember") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $res=eme_add_update_member();
      $message=$res[0];
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_editmember") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $member_id = intval($_POST['member_id']);
      $res=eme_add_update_member($member_id);
      $message=$res[0];
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_member") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $member=eme_new_member();
      eme_member_edit_layout($member);
      return;
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_member") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $member_id = intval($_GET['member_id']);
      $member = eme_get_member($member_id);
      if ($member) {
	      eme_member_edit_layout($member);
	      return;
      }
   }
   eme_manage_members_layout($message);
}

function eme_memberships_page() {
   $message="";
   if (!current_user_can( get_option('eme_cap_members')) && (isset($_POST['eme_admin_action']) || isset($_GET['eme_admin_action']))) {
      $message = __('You have no right to manage memberships!','events-made-easy');
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_addmembership") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $message=eme_add_update_membership();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_editmembership") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $membership_id = intval($_POST['membership_id']);
      $message=eme_add_update_membership($membership_id);
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_membership") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $membership=eme_new_membership();
      eme_membership_edit_layout($membership);
      return;
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_membership") {
      check_admin_referer('eme_members','eme_admin_nonce');
      $membership_id = intval($_GET['membership_id']);
      $membership = eme_get_membership($membership_id);
      if ($membership) {
	      eme_membership_edit_layout($membership);
	      return;
      }
   }
   eme_manage_memberships_layout($message);
}

function eme_add_update_member($member_id=0) {
   $member=array();
   $payment_id=0;

   if (isset($_POST['membership_id'])) {
	   $member['membership_id'] = intval($_POST['membership_id']);
   } else {
	   return __('No valid membership selected','events-made-easy');
   }
   if (is_admin()) {
	   if (isset($_POST['status'])) $member['status'] = intval($_POST['status']);
	   if (isset($_POST['status_automatic'])) $member['status_automatic'] = intval($_POST['status_automatic']);
	   if (isset($_POST['start_date'])) $member['start_date'] = eme_strip_tags($_POST['start_date']);
	   if (isset($_POST['end_date'])) $member['end_date'] = eme_strip_tags($_POST['end_date']);
   }
   if ($member_id) {
	   // existing member, all person info remains unchanged
	   $membership = eme_get_membership($member['membership_id']);
           $res=eme_db_update_member($member_id,$member,$membership);
           if ($res) {
		   $member=eme_get_member($member_id);
                   eme_member_answers($membership,$member);
		   $result = __('Member updated','events-made-easy');
		   $payment_id = $member['payment_id'];
           } else {
                   $result = __('Problem dectected while updating member','events-made-easy');
           }
   } else {
	   if (isset($_POST['lastname']) && isset($_POST['firstname']) && isset($_POST['email'])) {
		   // if the person already exists: update him
		   $person = eme_get_person_by_name_and_email(eme_strip_tags($_POST['lastname']),eme_strip_tags($_POST['firstname']),eme_strip_tags($_POST['email']));
		   if ($person) {
			   $person_id=eme_db_update_person($person['person_id'],eme_strip_tags($_POST));
		   } else {
			   $person_id=eme_db_insert_person(eme_strip_tags($_POST));
		   }

		   if (!$person_id) {
			   $result = __('Problem dectected while adding member','events-made-easy');
		   } else {
			   if (eme_is_member($person_id,$member['membership_id'])) {
				   $result = __('This person is already a member','events-made-easy');
			   } else {
				   $member['person_id']=$person_id;
				   $member_id=eme_db_insert_member($member);
				   if ($member_id) {
					   $payment_id=eme_create_member_payment($member_id);
					   $member=eme_get_member($member_id);
					   $membership=eme_get_membership($member['membership_id']);
					   eme_member_answers($membership,$member);
					   if (!is_admin())
						   $res2 = eme_email_member_booking($member,'newMember');
					   else
						   $res2=1;
					   $added_format = eme_get_template_format($membership['properties']['member_added_tpl']);
					   if (!empty($added_format)) {
						   $result = eme_replace_member_placeholders($added_format,$membership,$member);
						   if (!$res2) $result.= __('Member added, but there were some problems sending out the mail','events-made-easy');
					   } else {
						   if ($res2)
							   $result = __('Member added','events-made-easy');
						   else
							   $result = __('Member added, but there were some problems sending out the mail','events-made-easy');
					   }
				   }
			   }
		   }
           } else {
                   $result = __('Problem dectected while adding personal info: at least lastname, firstname and email need to be present','events-made-easy');
           }
   }
   $res = array(0=>$result,1=>$payment_id);
   return $res;
}

function eme_is_member($person_id,$membership_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $sql = $wpdb->prepare("SELECT COUNT(person_id) FROM $table WHERE membership_id=%d AND person_id=%d",$membership_id,$person_id);
   return $wpdb->get_var($sql);
}

function eme_add_update_membership($membership_id=0) {
   $membership=array();
   $properties=array();

   $membership['name'] = isset($_POST['name']) ? eme_strip_tags($_POST['name']) : '';
   $membership['description'] = isset($_POST['description']) ? eme_strip_tags($_POST['description']) : '';
   $membership['duration_count'] = isset($_POST['duration_count']) ? intval($_POST['duration_count']) : 0;
   $membership['duration_period'] = isset($_POST['duration_period']) ? eme_strip_tags($_POST['duration_period']) : '';
   $membership['type'] = isset($_POST['type']) ? eme_strip_tags($_POST['type']) : '';
   if (isset($_POST['properties'])) $membership['properties'] = eme_strip_tags($_POST['properties']);
   if (isset($_POST['start_date']) && eme_is_date_valid($_POST['start_date']))
	   $membership['start_date'] = $_POST['start_date'];
   else
	   $membership['start_date'] = '';

   $eme_dyndata = eme_handle_dyndata_post_adminform();
   if (!empty($eme_dyndata))
              $membership['properties']['dyndata'] = $eme_dyndata;

   // some common sense logic
   if (empty($membership['duration_period'])) $membership['duration_count']=0;
   if ($membership['duration_period'] == 'forever') $membership['properties']['reminder_days']='';

   if ($membership_id) {
           $res=eme_db_update_membership($membership_id,$membership);
           if ($res) {
                   return __('Membership updated','events-made-easy');
           } else {
                   return __('Problem dectected while updating membership','events-made-easy');
           }
   } else {
	   if (isset($_POST['membership_id'])) $member['membership_id'] = intval($_POST['membership_id']);
           $membership_id=eme_db_insert_membership($membership);
           if ($membership_id) {
                   return __('Membership added','events-made-easy');
           } else {
                   return __('Problem dectected while adding membership','events-made-easy');
           }
   }
}

function eme_member_edit_layout($member) {
   global $plugin_page;

   if (!isset($member['member_id'])) {
      $action="add";
   } else {
      $action="edit";
   }
   $nonce_field = wp_nonce_field('eme_members','eme_admin_nonce',false,false);
   ?>
   <div class="wrap">
      <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <?php if ($action=="add" && isset($_POST['membership_id']) && !empty($_POST['membership_id'])) {
		  $membership_id=intval($_POST['membership_id']);
                  eme_admin_edit_memberform($member,$membership_id);
               }
               if ($action=="edit") { 
	          $membership_id=$member['membership_id'];
                  eme_admin_edit_memberform($member,$membership_id);
	       }
?>
      </div>
   </div>
<?php
}

function eme_admin_edit_memberform($member,$membership_id) {
	$nonce_field = wp_nonce_field('eme_members','eme_admin_nonce',false,false);
	$eme_member_status_array = eme_member_status_array();
	$js_dateformat=eme_wp_date_format_php_to_js(get_option('date_format'));

	if (!isset($member['member_id'])) {
		$action="add";
		$h1_message=__('Add member','events-made-easy');
		$member['start_date'] = eme_get_next_start_date($membership_id,$member);
		$member['end_date'] = eme_get_next_end_date($membership_id,$member['start_date']);
	} else {
		$action="edit";
		$h1_message=__('Edit member','events-made-easy');
		if (empty($member['start_date']) || ($member['start_date']=='0000-00-00')) {
			$member['start_date'] = eme_get_next_start_date($membership_id,$member);
			$member['end_date'] = eme_get_next_end_date($membership_id,$member['start_date']);
		}
	}
	echo "<h1>$h1_message </h1>";
	if ($action == "edit") {
		echo "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-people&amp;eme_admin_action=edit_person&amp;person_id=".$member['person_id']),'eme_people','eme_admin_nonce')."' title='".__('Click on this link to edit the corresponding person info','events-made-easy')."'>".__('Click on this link to edit the corresponding person info','events-made-easy')."</a><br /><br />";
	}
?>
	<form method='post' action='#' name='eme-member-adminform' id='eme-member-adminform'>
	<?php echo $nonce_field; ?>
         <?php if ($action == "add") { ?>
         <input type='hidden' name='eme_admin_action' value='do_addmember' />
        <?php _e('If you want, select an existing person to become a member', 'events-made-easy') ?><br />
	<input type='text' id='chooseperson' name='chooseperson' class="clearable" placeholder="<?php _e('Start typing a name','events-made-easy'); ?>">
        <br /><br />
         <?php } else { ?>
	<input type='hidden' name='eme_admin_action' value='do_editmember' />
	<input type='hidden' name='member_id' value='<?php echo $member['member_id']; ?>' />
         <?php } ?>

	<table>
        <tr><td><?php _e('Start date', 'events-made-easy'); ?></td>
        <td>
           <input type='hidden' name='start_date' id='start_date' value='<?php echo $member['start_date']; ?>'>
	   <input type='text' readonly='readonly' name='dp_start_date' id='dp_start_date' data-date='<?php echo $member['start_date']; ?>' data-dateformat='<?php echo $js_dateformat; ?>' data-altfield='start_date' class='eme_formfield_date' />
	</td></tr>
	<tr><td><?php _e('End date', 'events-made-easy'); ?></td>
        <td>
           <input type='hidden' name='end_date' id='end_date' value='<?php echo $member['end_date']; ?>'>
	   <input type='text' readonly='readonly' name='dp_end_date' id='dp_end_date' data-date='<?php echo $member['end_date']; ?>' data-dateformat='<?php echo $js_dateformat; ?>' data-altfield='end_date' class='eme_formfield_date' />
	</td></tr>
	<tr><td><?php _e('Member status calculated automatically','events-made-easy'); ?></td>
	<td><?php echo eme_ui_select_binary($member['status_automatic'],"status_automatic", 0, "nodynamicupdates"); ?>
	    <br />
	    <?php echo "<p class='eme_smaller'>". __('If set to automatic, the status will be recalculated on a daily basis.','events-made-easy')."</p>"; ?>
        </td></tr>
        <tr><td><?php _e('Member status','events-made-easy'); ?></td>
	<td><?php echo eme_ui_select($member['status'],"status",$eme_member_status_array, '', 0, "nodynamicupdates"); ?>
	</td></tr>
        </table><br /><br />
        <?php
	_e('Member info','events-made-easy');
	echo '<br />';
	echo eme_member_form($member,$membership_id,1);
        ?>
        </form>
<?php
}

function eme_member_form($member,$membership_id,$from_backend=0) {
	$membership=eme_get_membership($membership_id);
	$form_html="";
	if (!$from_backend) {
		$form_html="<div id='eme-member-addmessage-ok' class='eme-member-message eme-member-message-success'></div><div id='eme-member-addmessage-error' class='eme-member-message eme-member-message-error'></div><div id='div_eme-payment-form' class='eme-payment-form'></div><div id='div_eme-member-form'><form name='eme-member-form' id='eme-member-form' method='post' action='#'>";
	}
	$form_html.="<input type='hidden' name='membership_id' value='$membership_id'>";
	$format=eme_nl2br_save_html(eme_get_template_format($membership['properties']['member_form_tpl']));
	$form_html.=eme_replace_membership_formfields_placeholders($membership,$member,$format);

	if (!$from_backend) {
		$form_html.="</form></div>";
	}
	return $form_html;
}

function eme_membership_edit_layout($membership) {
   global $plugin_page;

   if (!isset($membership['membership_id'])) {
      $action="add";
   } else {
      $action="edit";
   }
   $nonce_field = wp_nonce_field('eme_members','eme_admin_nonce',false,false);
   ?>
   <div class="wrap">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <h1><?php if ($action=="add")
                     _e('Add a membership definition', 'events-made-easy');
                   else
                     _e('Edit a membership definition', 'events-made-easy');
             ?></h1>

         <div id="ajax-response"></div>
         <form name="editmembership" id="editmembership" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>" class="validate">
         <?php echo $nonce_field; ?>
         <?php if ($action == "add") { ?>
         <input type="hidden" name="eme_admin_action" value="do_addmembership" />
         <?php } else { ?>
         <input type="hidden" name="eme_admin_action" value="do_editmembership" />
         <input type="hidden" name="membership_id" value="<?php echo $membership['membership_id'] ?>" />
         <?php } ?>

         <!-- we need titlediv and title for qtranslate as ID -->
         <div id="poststuff">
         <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
<div id="membership-tabs" style="display: none;">
  <ul>
    <li><a href="#tab-membershipdetails"><?php _e('Membership details','events-made-easy');?></a></li>
    <li><a href="#tab-mailformats"><?php _e('Mail formats','events-made-easy');?></a></li>
  </ul>
  <div id="tab-membershipdetails">
<?php eme_meta_box_div_membershipdetails($membership); ?>
  </div>
  <div id="tab-mailformats">
<?php eme_meta_box_div_membershipmailformats($membership); ?>
  </div>
</div> <!-- end membership-tabs -->
         	<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php if ($action=="add") _e('Add membership', 'events-made-easy'); else _e('Update membership', 'events-made-easy'); ?>" /></p>
            </div>
         </div>
         </div>
         </form>
   </div>
<?php
}

function eme_meta_box_div_membershipdetails($membership) {
   global $eme_timezone;
   $templates_array = eme_get_templates_array_by_id('membershipform');
   $templates_array2 = eme_get_templates('membershipform');
   $currency_array = eme_currency_array();
   $type_array=eme_membership_types();
   $duration_array=eme_membership_durations();
   $js_dateformat=eme_wp_date_format_php_to_js(get_option('date_format'));
?>
   <table class="eme_membership_admin_table">
   <tr>
   <td><label for="name"><?php _e('Name', 'events-made-easy') ?></label></td>
   <td><input required='required' id="name" name="name" type="text" value="<?php echo eme_esc_html($membership['name']); ?>" size="40" /></td>
   </tr>
   <tr>
   <td><label for="description"><?php _e('Description', 'events-made-easy') ?></label></td>
   <td><input id="description" name="description" type="text" value="<?php echo eme_esc_html($membership['description']); ?>" size="40" /></td>
   </tr>
   <tr>
   <td><label for="type"><?php _e('Type', 'events-made-easy') ?></label></td>
   <td><?php echo eme_ui_select($membership['type'],"type",$type_array);?></td>
   </tr>
   <tr id='startdate'>
   <td><label for="start_date"><?php _e('Start date', 'events-made-easy') ?></label></td>
   <td><input type='hidden' name='start_date' id='start_date' value='<?php echo $membership['start_date']; ?>'>
	   <input type='text' readonly='readonly' name='dp_start_date' id='dp_start_date' data-date=<?php echo $membership['start_date']; ?> data-dateformat='<?php echo $js_dateformat; ?>' data-altfield='start_date' class='eme_formfield_date' />
   </td>
   </tr>
   <tr>
   <td><label for="duration_count"><?php _e('Duration', 'events-made-easy') ?></label></td>
   <td><input type="integer" id="duration_count" name="duration_count" value="<?php echo $membership['duration_count']; ?>" size="4"><?php echo eme_ui_select($membership['duration_period'],"duration_period",$duration_array);?></td>
   </tr>
   <tr id='reminder'>
   <td><label for="properties[reminder_days]"><?php _e('Reminder', 'events-made-easy') ?></label></td>
   <td><input type="text" id="properties[reminder_days]" name="properties[reminder_days]" value="<?php echo $membership['properties']['reminder_days']; ?>" size="40">
       <br /><p class='eme_smaller'><?php _e('Set the number of days before membership expiration a reminder will be sent out.','events-made-easy'); ?>
       <br /><?php _e('If you want to send out multiple reminders, seperate the days here by commas.','events-made-easy'); ?></p>
   </td>
   </tr>
   <tr>
   <td><label for="properties[price]"><?php _e('Price', 'events-made-easy') ?></label></td>
   <td><input id="price" name="properties[price]" type="text" value="<?php echo eme_esc_html($membership['properties']['price']); ?>" size="4" /></td>
   </tr>
   <tr>
   <td><label for="properties[currency]"><?php _e('Currency', 'events-made-easy') ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['currency'],"properties[currency]",$currency_array); ?></td>
   </tr>
   <tr>
   <td><label for="properties[contact_id]"><?php _e('Contact person', 'events-made-easy') ?></label></td>
   <td><?php wp_dropdown_users ( array ('name' => 'properties[contact_id]', 'show_option_none' => __( "WP Admin", 'events-made-easy'), 'selected' => $membership['properties']['contact_id'] ) ); ?></td>
   </tr>
   <tr>
   <td><label for="properties[member_form_tpl]"><?php _e('Member Form Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select_key_value($membership['properties']['member_form_tpl'],"properties[member_form_tpl]",$templates_array2,'id','name',__('Please select a template','events-made-easy'),1);?>
       <br /><p class='eme_smaller'><?php  _e( 'The template should at least contain the placeholders #_LASTNAME, #_FIRSTNAME, #_EMAIL and #_SUBMIT. If not, the form will not be shown.', 'events-made-easy'); ?></p>
   </td>
   </tr>
   <tr>
   <td><label for="properties[member_added_tpl]"><?php _e('Member Added Message Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['member_added_tpl'],"properties[member_added_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php echo __( 'The format of the text shown after someone subscribed. If left empty, a default message will be shown.', 'events-made-easy') .'<br />'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/category/documentation/7-placeholders/7-14-members/'>".__('the documentation', 'events-made-easy').'</a>'; ?></p>
   </td>
   </tr>
   <tr>
   <td><label for="properties[payment_form_header_tpl]"><?php _e('Payment Form Header Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['payment_form_header_tpl'],"properties[payment_form_header_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php echo __( 'The format of the text shown above the payment buttons. If left empty, a default message will be shown.', 'events-made-easy') .'<br />'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/category/documentation/7-placeholders/7-14-members/'>".__('the documentation', 'events-made-easy').'</a>'; ?></p>
   </td>
   </tr>
   <tr>
   <td><label for="properties[payment_form_footer_tpl]"><?php _e('Payment Form Footer Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['payment_form_footer_tpl'],"properties[payment_form_footer_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php echo __( 'The format of the text shown below the payment buttons. Default: empty.', 'events-made-easy') .'<br />'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/category/documentation/7-placeholders/7-14-members/'>".__('the documentation', 'events-made-easy').'</a>'; ?></p>
   </td>
   </tr>
   <tr>
   <td><?php _e('Payment methods', 'events-made-easy') ?></label></td>
   <td>
       <?php echo eme_ui_use_checkbox($membership['properties']['use_paypal'],"properties[use_paypal]", __('Paypal','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_2co'],"properties[use_2co]",__('2Checkout','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_webmoney'],"properties[use_webmoney]", __('Webmoney','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_fdgg'],"properties[use_fdgg]",__( 'First Data','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_mollie'],"properties[use_mollie]",__( 'Mollie','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_sagepay'],"properties[use_sagepay]",__( 'Sage Pay','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_worldpay'],"properties[use_worldpay]",__( 'Worldpay','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_stripe'],"properties[use_stripe]",__( 'Stripe','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_braintree'],"properties[use_braintree]",__( 'Braintree','events-made-easy')); ?><br />
       <?php echo eme_ui_use_checkbox($membership['properties']['use_offline'],"properties[use_offline]",__( 'Offline','events-made-easy')); ?><br />
   </td>
   </tr>
   <tr>
   <td><label for="properties[offline_payment_tpl]"><?php _e('Offline Payment Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['offline_payment_tpl'],"properties[offline_payment_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php echo __( 'The format of the text shown for the offline payment method. Default: empty.', 'events-made-easy') .'<br />'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/category/documentation/7-placeholders/7-14-members/'>".__('the documentation', 'events-made-easy').'</a>'; ?></p>
   </td>
   </tr>
   </table>
   <br />
   <br />
<?php

   $templates_array = eme_get_templates_array_by_id('membershipform');
   if (isset($membership['properties']['dyndata']))
	   $eme_data = $membership['properties']['dyndata'];
   else
	   $eme_data=array();
   eme_dyndata_adminform($eme_data,$templates_array);
}

function eme_meta_box_div_membershipmailformats($membership) {
   $templates_array = eme_get_templates_array_by_id('membershipmail');
?>
   <table class="eme_membership_admin_table">
   <tr>
   <td><label for="name"><?php _e('New member Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[new_subject_format]" name="properties[new_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['new_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the person signing up as a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('New member Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['new_body_format_tpl'],"properties[new_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the person signing up as a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Extended member Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[extended_subject_format]" name="properties[extended_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['extended_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the member when the membership is extended','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Extended member Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['extended_body_format_tpl'],"properties[extended_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the member when the membership is extended','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Updated member Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[updated_subject_format]" name="properties[updated_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['updated_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the member upon changes','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Updated member Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['updated_body_format_tpl'],"properties[updated_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the member upon changes','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Paid Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[paid_subject_format]" name="properties[paid_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['paid_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the member when marked as paid','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Paid Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['paid_body_format_tpl'],"properties[paid_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the member when marked as paid','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Reminder Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[reminder_subject_format]" name="properties[reminder_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['reminder_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the member when membership is about to expire. These reminders will be sent once a day, based on the reminder settings of the defined membership.','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Reminder Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['reminder_body_format_tpl'],"properties[reminder_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the member when membership is about to expire. These reminders will be sent once a day, based on the reminder settings of the defined membership.','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Stop Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[stop_subject_format]" name="properties[stop_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['stop_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the member when membership expired or marked as stopped','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Stop Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['stop_body_format_tpl'],"properties[stop_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the member when membership expired or marked as stopped','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson New member Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[contact_new_subject_format]" name="properties[contact_new_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['contact_new_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the contactperson when someone signes up as a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson New member Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['contact_new_body_format_tpl'],"properties[contact_new_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the contactperson when someone signes up as a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson Paid Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[contact_paid_subject_format]" name="properties[contact_paid_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['contact_paid_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the contactperson after a member is marked as paid','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson Paid Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['contact_paid_body_format_tpl'],"properties[contact_paid_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the contactperson after a member is marked as paid','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson Stop Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[contact_stop_subject_format]" name="properties[contact_stop_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['contact_stop_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the contactperson when a membership expired or stopped','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson Stop Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['contact_stop_body_format_tpl'],"properties[contact_stop_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the contactperson when a membership expired or stopped','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson IPN Subject Format:', 'events-made-easy'); ?></label></td>
   <td><input id="properties[contact_ipn_subject_format]" name="properties[contact_ipn_subject_format]" type="text" value="<?php echo eme_esc_html($membership['properties']['contact_ipn_subject_format']); ?>" size="40" />
       <br /><p class='eme_smaller'><?php _e('The subject of the mail sent to the contactperson when an IPN arrives from a payment gateway for a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   <tr>
   <td><label for="name"><?php _e('Contactperson IPN Body Format Template:', 'events-made-easy'); ?></label></td>
   <td><?php echo eme_ui_select($membership['properties']['contact_ipn_body_format_tpl'],"properties[contact_ipn_body_format_tpl]",$templates_array);?>
       <br /><p class='eme_smaller'><?php _e('The body of the mail sent to the contactperson when an IPN arrives from a payment gateway for a member','events-made-easy'); ?></p>
       <br />
   </td>
   </tr>
   </table>
<?php
}

function eme_manage_members_layout($message) {
   global $plugin_page;

   $memberships = eme_get_memberships();
   $pdftemplates = eme_get_templates('pdf');
   $eme_member_status_array = array(''=>__('Membership status','events-made-easy')) + eme_member_status_array();
   $nonce_field = wp_nonce_field('eme_members','eme_admin_nonce',false,false);

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

         <div id="members-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Add a new member', 'events-made-easy') ?></h1>
         <div class="wrap">
         <form id="members-filter" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>">
            <input type="hidden" name="eme_admin_action" value="add_member" />
	    <?php echo $nonce_field; echo eme_ui_select_key_value('','membership_id',$memberships,'membership_id','name'); ?>
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add member', 'events-made-easy');?>" />
         </form>
         </div>
         
         <h1><?php _e('Members', 'events-made-easy') ?></h1>

   <?php if (current_user_can(get_option('eme_cap_cleanup'))) { ?>
   <span class="eme_import_form_img">
   <?php _e('Click on the icon to show the import form','events-made-easy'); ?>
   <img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="div_import" style="cursor: pointer; vertical-align: middle; ">
   </span>
   <div id='div_import' name='div_import' style='display:none;'>
   <form id='member-import' method='post' enctype='multipart/form-data' action='#'>
   <?php echo $nonce_field; ?>
   <input type="file" name="eme_csv" />
   <input type="hidden" name="eme_admin_action" value="import" />
   <input type="submit" value="<?php _e ( 'Import members','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   <?php _e('If you want, use this to import members info into the database', 'events-made-easy'); ?>
   </form>
   <form id='member-import-answers' method='post' enctype='multipart/form-data' action='#'>
   <?php echo $nonce_field; ?>
   <input type="file" name="eme_csv" />
   <input type="hidden" name="eme_admin_action" value="import_dynamic_answers" />
   <input type="submit" value="<?php _e ( 'Import dynamic field answers','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   <?php _e('Once you finished importing members, use this to import dynamic field answers into the database', 'events-made-easy'); ?>
   </form>
   </div>
   <br />
   <?php } ?>
   <br />

   <form id="eme-admin-regsearchform" name="eme-admin-regsearchform" action="#" method="post">
   <?php
   echo eme_ui_select_key_value('','search_membershipid',$memberships,'membership_id','name',__('Select a membership','events-made-easy'));
   ?>
   <?php
   echo eme_ui_select('','search_status',$eme_member_status_array);
   ?>
   <input type="text" name="search_person" id="search_person" class="clearable" placeholder="<?php _e('Filter on person','events-made-easy'); ?>" size=15 />
   <input type="text" name="search_customfields" id="search_customfields" class="clearable" placeholder="<?php _e('Filter on custom field answer','events-made-easy'); ?>" size=15 />
   <?php
   $formfields=eme_get_formfields('','members');
   $extrafields_arr=array();
   $extrafieldnames_arr=array();
   foreach ($formfields as $formfield) {
           $extrafields_arr[]=$formfield['field_id'];
           $extrafieldnames_arr[]=eme_esc_html($formfield['field_name']);
   }
   // these 2 values are used as data-fields to the container-div, and are used by the js to create extra columns
   $extrafields=join(',',$extrafields_arr);
   $extrafieldnames=join(',',$extrafieldnames_arr);

   echo eme_ui_multiselect_key_value('','search_customfieldids',$formfields,'field_id','field_name','',5,0,'eme_select2_filter_class');
   ?>
   <input type="text" name="search_memberid" id="search_memberid" class="clearable" placeholder="<?php _e('Filter on member ID','events-made-easy'); ?>" size=15 />
   <button id="MembersLoadRecordsButton" class="button action"><?php _e('Filter members','events-made-easy'); ?></button>
   </form>

   <form id='members-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="markPaid"><?php _e ( 'Set membership to paid','events-made-easy'); ?></option>
   <option value="markUnpaid"><?php _e ( 'Set membership unpaid','events-made-easy'); ?></option>
   <option value="extendMembership"><?php _e ( 'Extend membership','events-made-easy'); ?></option>
   <option value="renewExpiredMembership"><?php _e ( 'Renew expired membership','events-made-easy'); ?></option>
   <option value="stopMembership"><?php _e ( 'Stop membership','events-made-easy'); ?></option>
   <option value="deleteMembers"><?php _e ( 'Delete selected members','events-made-easy'); ?></option>
   <option value="resendPendingMember"><?php _e ( 'Resend the mail for pending members','events-made-easy'); ?></option>
   <option value="sendMails"><?php _e ( 'Send email','events-made-easy'); ?></option>
   <option value="pdf"><?php _e ( 'PDF output','events-made-easy'); ?></option>
   </select>
   <span id="span_sendmails">
   <?php _e('Send mails to members upon changes being made?','events-made-easy'); echo eme_ui_select_binary(1,"send_mail"); ?>
   </span>
   <span id="span_pdftemplate">
   <?php echo eme_ui_select_key_value('',"pdf_template",$pdftemplates,'id','name',__('Please select a template','events-made-easy'),1);?>
   </span>
   <button id="MembersActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="MembersTableContainer" data-extrafields='<?php echo $extrafields;?>' data-extrafieldnames='<?php echo $extrafieldnames;?>'></div>
   </form>
   </div>
   </div>
   <?php
}

function eme_manage_memberships_layout($message) {
   global $plugin_page;

   $nonce_field = wp_nonce_field('eme_members','eme_admin_nonce',false,false);
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

         <div id="memberships-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Add a new membership definition', 'events-made-easy') ?></h1>
         <div class="wrap">
         <form id="memberships-filter" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_membership" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add membership', 'events-made-easy');?>" />
         </form>
         </div>

         <h1><?php _e('Memberships', 'events-made-easy') ?></h1>

   <form id='memberships-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteMemberships"><?php _e ( 'Delete selected memberships','events-made-easy'); ?></option>
   </select>
   <button id="MembershipsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span><br />
   <div id="MembershipsTableContainer"></div>
   </form>
   </div>
   </div>
   <?php
}

function eme_calc_memberprice_ajax() {
   header("Content-type: application/json; charset=utf-8");
   $membership_ids=array();
   if (isset($_POST['member'])) {
      foreach($_POST['member'] as $key=>$val) {
         $membership_ids[]=intval($key);
      }
   }
   $total=0;
   $cur='';
   foreach ($membership_ids as $membership_id) {
      $membership=eme_get_membership($membership_id);
      $total += $membership['properties']['price'];
      $cur = $membership['properties']['currency'];
   }
   $locale = get_locale();
   $result = eme_localized_price($total,$cur);
   echo json_encode(array('total'=>$result));
}

function eme_dyndata_member_ajax() {
   header("Content-type: application/json; charset=utf-8");
   // first detect multibooking
   $membership_id=0;
   if (isset($_POST['membership_id'])) {
         $membership_id=intval($_POST['membership_id']);
   }

   if (isset($_POST['member_id'])) {
	   $member=eme_get_member($_POST['member_id']);
	   check_admin_referer('eme_members','eme_admin_nonce');
   } else {
	   $member=eme_new_member();
   }

   $total=0;
   $cur='';
   $form_html='';
   if ($membership_id) {
      $membership=eme_get_membership($membership_id);
      //$total += eme_calc_price_fake_member($membership);
      $total += $membership['properties']['price'];
      $cur = $membership['properties']['currency'];
      if (isset($membership['properties']['dyndata'])) {
	      $conditions=$membership['properties']['dyndata'];
	      foreach ($conditions as $count=>$condition) {
		      // the next check is mostly to eliminate older conditions that didn't have the field-param
		      if (!isset($condition['field']) || empty($condition['field'])) continue;
		      // sensible values ...
		      if (!isset($condition['grouping']) || empty($condition['grouping']))
			      $grouping=$count;
		      else
			      $grouping=intval($condition['grouping']);
	              $fieldname_path = eme_replace_membership_formfields_placeholders($membership,$member,$condition['field'],1);
		      $entered_val = eme_getValueFromPath($_POST, $fieldname_path);
		      if ($condition['condition'] == 'eq' && $entered_val == $condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping);
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_footer']));
		      }
		      if ($condition['condition'] == 'lt' && $entered_val<$condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      if ($condition['repeat']) {
				      $entered_val=intval($entered_val);
				      $condition['condval']=intval($condition['condval']);
				      for ($i=$entered_val;$i<$condition['condval'];$i++) {
					      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping,$i-$entered_val);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping);
			      }
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_footer']));
		      }
		      if ($condition['condition'] == 'gt' && $entered_val>$condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      if ($condition['repeat']) {
				      $entered_val=intval($entered_val);
				      $condition['condval']=intval($condition['condval']);
				      for ($i=$condition['condval'];$i<$entered_val;$i++) {
					      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping,$i-$condition['condval']);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping);
			      }
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_footer']));
		      }
		      if ($condition['condition'] == 'ge' && $entered_val>=$condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      if ($condition['repeat']) {
				      $entered_val=intval($entered_val);
				      $condition['condval']=intval($condition['condval']);
				      for ($i=$condition['condval'];$i<=$entered_val;$i++) {
					      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping,$i-$condition['condval']);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_membership_formfields_placeholders($membership,$member,$template,$grouping);
			      }
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_footer']));
		      }
	      }

      }
   }
   $locale = get_locale();
   $localized_price = eme_localized_price($total,$cur);
   echo json_encode(array('result'=>$form_html,'total'=>$localized_price));
}

function eme_member_answers($membership,$member,$do_update=1) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME; 
   $fields_seen=array();

   $extra_charge=0;
   $membership_id=$membership['membership_id'];
   $all_answers=array();
   if ($do_update) {
	   $member_id=$member['member_id'];
	   if ($member_id>0) {
		   $all_answers=eme_get_member_answers_all($member_id);
	   }
   } else {
	   $member_id=0;
   }

   $answer_ids_seen=array();

   // do the dynamic answers if any
   // this is a little tricky: dynamic answers are in fact grouped by a seat condition when filled out, and there can be more than 1 of the same group
   // so we need a little more looping here ...
   if (isset($_POST['dynamic_member'][$membership_id])) {
	   foreach($_POST['dynamic_member'][$membership_id] as $group_id =>$group_value) {
		   foreach($group_value as $occurence_id => $occurence_value) {
			   foreach($occurence_value as $key => $value) {
				   if (preg_match('/^FIELD(\d+)$/', $key, $matches)) { 
					   $field_id = intval($matches[1]);
					   $formfield = eme_get_formfield($field_id);
					   if ($formfield) {
						   // for multivalue fields like checkbox, the value is in fact an array
						   // to store it, we make it a simple "multi" string using eme_convert_array2multi, so later on when we need to parse the values 
						   if (is_array($value)) $value=eme_convert_array2multi($value);
						   if ($formfield['field_type']=='textarea')
							   $value=eme_sanitize_textarea($value);
						   else
							   $value=eme_sanitize_text_field($value);
						   if ($formfield['extra_charge'] && is_numeric($value))
						   	   $extra_charge+=$value;
						   if ($do_update) {
							   $answer_id=eme_return_answerid($all_answers,0,0,$member_id,$field_id,$group_id,$occurence_id);
							   if ($answer_id) {
								   $sql = $wpdb->prepare("UPDATE $answers_table SET answer=%s WHERE answer_id=%d",$value,$answer_id);
								   $answer_ids_seen[]=$answer_id;
							   } else {
								   $sql = $wpdb->prepare("INSERT INTO $answers_table (member_id,field_id,grouping,occurence,answer) VALUES (%d,%d,%d,%d,%s)",$member_id,$field_id,$group_id,$occurence_id,$value);
							   }
							   $wpdb->query($sql);
						   }
					   }
				   }
			   }
		   }
	   }
   }

   foreach($_POST as $key =>$value) {
      if (preg_match('/^FIELD(\d+)$/', $key, $matches)) { 
         $field_id = intval($matches[1]);
         $formfield = eme_get_formfield($field_id);
	 if ($formfield) {
		 // for multivalue fields like checkbox, the value is in fact an array
		 // to store it, we make it a simple "multi" string using eme_convert_array2multi, so later on when we need to parse the values 
		 if (is_array($value)) $value=eme_convert_array2multi($value);
		 if ($formfield['field_type']=='textarea')
			 $value=eme_sanitize_textarea($value);
		 else
			 $value=eme_sanitize_text_field($value);
		 if ($formfield['extra_charge'] && is_numeric($value))
			 $extra_charge+=$value;
		 if ($do_update) {
		    $answer_id=eme_return_answerid($all_answers,0,0,$member_id,$field_id);
		    if ($answer_id) {
		       $sql = $wpdb->prepare("UPDATE $answers_table SET answer=%s WHERE answer_id=%d",$value,$answer_id);
		       $answer_ids_seen[]=$answer_id;
		    } else {
		       $sql = $wpdb->prepare("INSERT INTO $answers_table (member_id,field_id,answer) VALUES (%d,%d,%s)",$member_id,$field_id,$value);
		    }
		    $wpdb->query($sql);
		 }
	 }
      }
   }

   // delete old answer_ids
   if ($do_update) {
      foreach ($all_answers as $answer) {
	   if (!in_array($answer['answer_id'],$answer_ids_seen) && $member_id>0) {
		   // the where member_id=%d is actually not needed, answer_id is unique, but we add it as a precaution
		   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE member_id = %d and answer_id=%d",$member_id,$answer['answer_id']);
		   $wpdb->query($sql);
	   }
      }
   }

   // now put the extra charge found in the members table
   if ($do_update) {
      $members_table = $wpdb->prefix.MEMBERS_TBNAME; 
      $sql = $wpdb->prepare("UPDATE $members_table SET extra_charge = %s WHERE member_id = %d",$extra_charge,$member_id);
      $wpdb->query($sql);
   }
   return $extra_charge;
}

function eme_get_member_answers_all($member_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $answers_table WHERE member_id=%d",$member_id);
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_get_member_answers($member_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $formfield_table_name = $wpdb->prefix.FORMFIELDS_TBNAME;
   $sql = $wpdb->prepare("SELECT a.*, b.field_name FROM $answers_table a, $formfield_table_name b WHERE a.member_id=%d AND a.grouping=0 AND b.field_id=a.field_id",$member_id);
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_get_dyndata_member_answers($member_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $formfield_table_name = $wpdb->prefix.FORMFIELDS_TBNAME;
   $sql = $wpdb->prepare("SELECT a.*, b.field_name FROM $answers_table a, $formfield_table_name b WHERE a.member_id=%d AND a.grouping>0 AND b.field_id=a.field_id ORDER BY a.grouping,a.occurence",$member_id);
   return $wpdb->get_results($sql, ARRAY_A);
}
function eme_get_dyndata_member_answer($member_id,$grouping=0,$occurence=0) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $formfield_table_name = $wpdb->prefix.FORMFIELDS_TBNAME;
   $sql = $wpdb->prepare("SELECT a.*, b.field_name FROM $answers_table a, $formfield_table_name b WHERE a.member_id=%d AND a.grouping=%d AND a.occurence=%d AND b.field_id=a.field_id",$member_id,$grouping,$occurence);
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_delete_member_answers($member_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE member_id=%d",$member_id);
   $wpdb->query($sql);
}

function eme_get_next_start_date($membership_id,$member,$renew_expired=0) {
	global $eme_timezone;

	if (!$membership_id) return;
	$membership=eme_get_membership($membership_id);
	if (!$membership) return;

	$eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
	if ($membership['type']=="rolling") {
		if ($renew_expired) {
			// expired members that want a renewal? Base it on today
			return $eme_date_obj_now->getDate();
		} elseif ((empty($member['start_date']) || $member['start_date']=="0000-00-00") && !empty($member['creation_date'])) {
			// new members have an empty start date, so base it on the creation date
			$eme_date_obj=ExpressiveDate::createfromformat('Y-m-d H:i:s',$member['creation_date'],$eme_timezone);
			// check the return code to make sure we can return something sensible
			if ($eme_date_obj !== false)
				return $eme_date_obj->getDate();
			else
				return $eme_date_obj_now->getDate();
		} else {
			// just in case ...
			return $eme_date_obj_now->getDate();
		}
	} else {
		$base_date_obj=ExpressiveDate::createfromformat('Y-m-d',$membership['start_date'],$eme_timezone);
		$interval=DateInterval::createFromDateString($membership['duration_count']." ".$membership['duration_period']);
		while ($base_date_obj->lessThan($eme_date_obj_now)) {
			$base_date_obj->add($interval);
		}
		return $base_date_obj->getDate();
	}
}
function eme_get_next_end_date($membership_id,$start_date) {
	global $eme_timezone;

	if (!$membership_id) return;
	$membership=eme_get_membership($membership_id);
	if (!$membership) return;
	if ($membership['duration_period']=='forever') return '';

	$eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
	// set at midnight from today
	$eme_date_obj_now->today();
	$base_date_obj=ExpressiveDate::createfromformat('Y-m-d',$start_date,$eme_timezone);
	$interval=DateInterval::createFromDateString($membership['duration_count']." ".$membership['duration_period']);
	$base_date_obj->add($interval);
	while ($base_date_obj->lessThan($eme_date_obj_now)) {
		$base_date_obj->add($interval);
	}
	return $base_date_obj->getDate();
}

// for CRON
function eme_member_recalculate_status() {
   global $wpdb;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships_table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $sql = "SELECT a.member_id, a.status, a.start_date, a.end_date, b.duration_period FROM $members_table a LEFT JOIN $memberships_table b ON a.membership_id=b.membership_id WHERE a.status_automatic=1 AND a.paid=1";
   $rows=$wpdb->get_results($sql,ARRAY_A);
   foreach ($rows as $item) {
      $status_calculated=eme_member_calc_status($item['start_date'],$item['end_date'],$item['duration_period']);
      if ($item['status'] != $status_calculated) {
	      if ($status_calculated == MEMBER_STATUS_EXPIRED ) {
		      eme_stop_member($item['member_id']);
		      $member = eme_get_member($item['member_id']);
                      eme_email_member_booking($member,"stopMember");
	      } else {
		      eme_member_set_status($item['member_id'],$status_calculated);
	      }
      }
   }
}

// for CRON
function eme_member_send_expiration_reminders() {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships=eme_get_memberships();
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   foreach ($memberships as $membership) {
      $membership_id=$membership['membership_id'];
      if ($membership['duration_period']=='forever')
	      continue;
      $reminder_days=explode(',',$membership['properties']['reminder_days']);
      foreach ($reminder_days as $reminder_day) {
	      $day=intval($reminder_day);
	      // only send a reminder if really needed
	      if ($day>0) {
		      $sql = "SELECT member_id from $table WHERE membership_id=$membership_id AND status <> ".MEMBER_STATUS_EXPIRED." AND DATEDIFF(end_date,CURDATE())=$day";
		      $member_ids=$wpdb->get_col($sql);
		      $queue = ($eme_cron_queue_count && count($member_ids) > $eme_cron_queue_count)?1:0;
		      foreach ($member_ids as $member_id) {
			      eme_member_send_reminder($member_id,$queue);
		      }
	      }
      } 
   }
}

function eme_member_send_reminder($member_id,$queue) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $member = eme_get_member($member_id);
   eme_email_member_booking($member, "remindMember",$queue);
   $sql = "UPDATE $table SET reminder=reminder+1,reminder_date=CURDATE() WHERE member_id=$member_id";
   $wpdb->query($sql);
}

function eme_member_calc_status($start_date,$end_date,$duration='') {
	global $eme_timezone;

	$eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
	// set at midnight from today
	$eme_date_obj_now->today();
	$start_date_obj=ExpressiveDate::createfromformat('Y-m-d',$start_date,$eme_timezone);
	if ($start_date_obj!==false)
		$start_date_obj->setHour(0)->setMinute(0)->setSecond(0);

	$end_date_obj=ExpressiveDate::createfromformat('Y-m-d',$end_date,$eme_timezone);
	if ($end_date_obj!==false)
		$end_date_obj->setHour(0)->setMinute(0)->setSecond(0);

	if (!empty($start_date) && $start_date_obj!==false && $start_date_obj->lessOrEqualTo($eme_date_obj_now) && $duration=='forever')
		return MEMBER_STATUS_ACTIVE;
	if (!empty($end_date) && $end_date_obj!==false && $end_date_obj->lessThan($eme_date_obj_now))
		return MEMBER_STATUS_EXPIRED;
	if (!empty($start_date) && $start_date_obj!==false && $start_date_obj->lessOrEqualTo($eme_date_obj_now))
		return MEMBER_STATUS_ACTIVE;
	if (!empty($start_date) && $start_date_obj!==false && $eme_date_obj_now->lessThan($start_date_obj))
		return MEMBER_STATUS_PENDING;

	// a default return
	return MEMBER_STATUS_PENDING;
}

function eme_member_set_paid($member_id) {
   global $wpdb, $eme_timezone;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $member=eme_get_member($member_id);
   if ($member['paid']) return 0;

   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;
   $fields['paid'] = 1;
   $fields['payment_date']=current_time('mysql', false);

   // if a membership is paid, set the start and end date if need be
   // and recalc the status in case of automatic
   if (empty($member['start_date']) || ($member['start_date']=='0000-00-00')) {
	   $fields['start_date'] = eme_get_next_start_date($member['membership_id'],$member);
	   $fields['end_date'] = eme_get_next_end_date($member['membership_id'],$fields['start_date']);
	   if ($member['status_automatic'])
		   $fields['status']=eme_member_calc_status($fields['start_date'],$fields['end_date']);
   } elseif (empty($member['end_date']) || ($member['end_date']=='0000-00-00')) {
	   $fields['end_date'] = eme_get_next_end_date($member['membership_id'],$member['start_date']);
	   if ($member['status_automatic'])
		   $fields['status']=eme_member_calc_status($member['start_date'],$fields['end_date']);
   } else {
	   if ($member['status_automatic'])
		   $fields['status']=eme_member_calc_status($member['start_date'],$member['end_date']);
   }

   return $wpdb->update($table, $fields, $where);
}

function eme_member_set_unpaid($member_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $member=eme_get_member($member_id);
   if (!$member['paid']) return 0;

   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;
   $fields['paid'] = 0;
   $fields['payment_date']="";

   return $wpdb->update($table, $fields, $where);
}

function eme_extend_member($member_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;

   $member=eme_get_member($member_id);
   // don't do anything if expired
   if ($member['status']==MEMBER_STATUS_EXPIRED) {
	   return 1;
   }
   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;
   $fields['paid'] = 1;
   $fields['payment_date']=current_time('mysql', false);
   $fields['end_date'] = eme_get_next_end_date($member['membership_id'],$member['end_date']);
   return $wpdb->update($table, $fields, $where);
}

function eme_renew_expired_member($member_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;

   $member=eme_get_member($member_id);
   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;
   $fields['paid'] = 1;
   $fields['payment_date']=current_time('mysql', false);
   // set the third option to eme_get_next_start_date to 1, to force a new startdate (only has an effect for rolling-type memberships)
   $fields['start_date'] = eme_get_next_start_date($member['membership_id'],$member,1);
   $fields['end_date'] = eme_get_next_end_date($member['membership_id'],$fields['start_date']);
   $fields['status_automatic']=1;
   $fields['status']=eme_member_calc_status($fields['start_date'],$fields['end_date']);
   return $wpdb->update($table, $fields, $where);
}

function eme_member_set_status($member_id,$status) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;

   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;

   $fields['status']=$status;
   return $wpdb->update($table, $fields, $where);
}

function eme_stop_member($member_id) {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERS_TBNAME;

   $where = array();
   $fields = array();
   $where['member_id'] = $member_id;

   $fields['status_automatic']=0;
   $fields['status']=MEMBER_STATUS_EXPIRED;
   return $wpdb->update($table, $fields, $where);
}

function eme_email_member_booking($member, $action, $queue=0) {
   // first check if a mail should be send at all

   $person = eme_get_person ($member['person_id']);
   $person_email = $person['email'];
   $membership = eme_get_membership($member['membership_id']);

   $contact = eme_get_contact($membership['properties']['contact_id']);
   $contact_email = $contact->user_email;
   $contact_name = $contact->display_name;
   $mail_text_html=get_option('eme_rsvp_send_html')?"html":"text";
   
   // and now send the wanted mails
   $booker_subject_vars=array('member_new_subject','member_paid_subject','member_stop_subject','member_reminder_subject','member_updated_subject','member_extended_subject');
   $booker_body_vars=array('member_new_body','member_paid_body','member_stop_body','member_reminder_body','member_updated_body','member_extended_body');
   $contact_subject_vars=array('contact_new_subject','contact_paid_subject','contact_stop_subject','contact_ipn_subject');
   $contact_body_vars=array('contact_new_body','contact_paid_body','contact_stop_body','contact_ipn_body');

   // first get the initial values
   $member_new_subject = $membership['properties']['new_subject_format'];
   $member_new_body = eme_get_template_format($membership['properties']['new_body_format_tpl']);
   $member_extended_subject = $membership['properties']['extended_subject_format'];
   $member_extended_body = eme_get_template_format($membership['properties']['extended_body_format_tpl']);
   $member_updated_subject = $membership['properties']['updated_subject_format'];
   $member_updated_body = eme_get_template_format($membership['properties']['updated_body_format_tpl']);
   $member_paid_subject = $membership['properties']['paid_subject_format'];
   $member_paid_body = eme_get_template_format($membership['properties']['paid_body_format_tpl']);
   $member_stop_subject = $membership['properties']['stop_subject_format'];
   $member_stop_body = eme_get_template_format($membership['properties']['stop_body_format_tpl']);
   $member_reminder_subject = $membership['properties']['reminder_subject_format'];
   $member_reminder_body = eme_get_template_format($membership['properties']['reminder_body_format_tpl']);
   $contact_new_subject = $membership['properties']['contact_new_subject_format'];
   $contact_new_body = eme_get_template_format($membership['properties']['contact_new_body_format_tpl']);
   $contact_paid_subject = $membership['properties']['contact_paid_subject_format'];
   $contact_paid_body = eme_get_template_format($membership['properties']['contact_paid_body_format_tpl']);
   $contact_stop_subject = $membership['properties']['contact_stop_subject_format'];
   $contact_stop_body = eme_get_template_format($membership['properties']['contact_stop_body_format_tpl']);
   $contact_ipn_subject = $membership['properties']['contact_ipn_subject_format'];
   $contact_ipn_body = eme_get_template_format($membership['properties']['contact_ipn_body_format_tpl']);

   // replace needed placeholders
   foreach ($contact_subject_vars as $var) {
      $$var = eme_replace_member_placeholders($$var, $membership, $member, "text");
      // possible translations are handled last 
      $$var = eme_translate($$var);
   }
   foreach ($contact_body_vars as $var) {
      $$var = eme_replace_member_placeholders($$var, $membership, $member, $mail_text_html);
      // possible translations are handled last 
      $$var = eme_translate($$var);
   }
   foreach ($booker_subject_vars as $var) {
      $$var = eme_replace_member_placeholders($$var, $membership, $member, "text",$person['lang']);
      // possible translations are handled last 
      $$var = eme_translate($$var,$person['lang']);
   }
   foreach ($booker_body_vars as $var) {
      $$var = eme_replace_member_placeholders($$var, $membership, $member, $mail_text_html,$person['lang']);
      // possible translations are handled last 
      $$var = eme_translate($$var,$person['lang']);
   }

   
   $person_name=$person['lastname'].' '.$person['firstname'];
   if ($action == 'remindMember') {
      return eme_send_mail($member_reminder_subject,$member_reminder_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'extendMember') {
      // can only be called from within the backend interface
      // so we don't send the mail to the event contact 
      return eme_send_mail($member_extended_subject,$member_extended_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'updateMember') {
      // can only be called from within the backend interface
      // so we don't send the mail to the event contact 
      return eme_send_mail($member_updated_subject,$member_updated_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'stopMember') {
      eme_send_mail($contact_stop_subject, $contact_stop_body, $contact_email, $contact_name, $contact_email, $contact_name, $queue);
      return eme_send_mail($member_stop_subject,$member_stop_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'markPaid') {
      eme_send_mail($contact_paid_subject, $contact_paid_body, $contact_email,$contact_name, $contact_email, $contact_name, $queue);
      return eme_send_mail($member_paid_subject,$member_paid_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'ipnReceived') {
      return eme_send_mail($contact_ipn_subject, $contact_ipn_body, $contact_email,$contact_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'remindNewMember') {
      eme_send_mail($contact_new_subject, $contact_new_body, $contact_email,$contact_name, $contact_email, $contact_name, $queue);
      return eme_send_mail($member_new_subject,$member_new_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   } elseif ($action == 'newMember' || empty($action)) {
      eme_send_mail($contact_new_subject, $contact_new_body, $contact_email,$contact_name, $contact_email, $contact_name, $queue);
      return eme_send_mail($member_new_subject,$member_new_body, $person_email, $person_name, $contact_email, $contact_name, $queue);
   }
}

function eme_add_member_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>0,'name'=>''), $atts));
   if (!empty($name)) {
	   $membership = eme_get_membership(0,$name);
	   $id = $membership['membership_id'];
   }
   $member = eme_new_member();
   if ($id)
      return eme_member_form($member,$id);
}

function eme_add_member_ajax() {
      $payment_id=0;
      if (get_option('eme_captcha_for_booking')) {
	      $captcha_err = eme_check_captcha("captcha_check","eme_add_member");
      } else {
	      $captcha_err = "";
      }
      if(!empty($captcha_err)) {
	      $result = __('You entered an incorrect code','events-made-easy');
	      echo json_encode(array('result'=>'NOK','htmlmessage'=>$result));
	      return;
      }

      if (!isset($_POST['membership_id'])) {
         $form_html = __('No membership selected','events-made-easy');
         echo json_encode(array('result'=>'NOK','htmlmessage'=>$form_html));
         return;
      } else {
         $membership = eme_get_membership(intval($_POST['membership_id']));
      }

      if (has_filter('eme_eval_member_form_post_filter'))
            $eval_filter_return=apply_filters('eme_eval_member_form_post_filter',$membership);
      else
            $eval_filter_return=array(0=>1,1=>'');
      
      if (is_array($eval_filter_return) && !$eval_filter_return[0]) {
         // the result of own eval rules failed, so let's use that as a result
         $form_result_message = $eval_filter_return[1];
      } else {
	 $member_res = eme_add_update_member();
         $form_result_message = $member_res[0];
         $payment_id = $member_res[1];
      }

      // let's decide for the first event wether or not payment is needed
      if ($payment_id && eme_membership_can_pay_online($membership)) {
         $total_price = eme_get_member_payment_price($payment_id);
         if ($total_price>0) {
            $payment_form = eme_payment_member_form($payment_id);
            echo json_encode(array('result'=>'OK','htmlmessage'=>$form_result_message,'paymentform'=>$payment_form));
         } else {
            // price=0
            echo json_encode(array('result'=>'OK','htmlmessage'=>$form_result_message));
         }
      } elseif ($payment_id) {
         echo json_encode(array('result'=>'OK','keep_form'=>0,'htmlmessage'=>$form_result_message));
      } else {
         // failed
         echo json_encode(array('result'=>'NOK','htmlmessage'=>$form_result_message));
      }
}

function eme_replace_member_placeholders($format, $membership, $member, $target="html") {
   global $eme_timezone;
   preg_match_all("/#_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);
   usort($placeholders[0],'eme_sort_stringlenth');
   $answers = eme_get_member_answers($member['member_id']);
   $person = eme_get_person($member['person_id']);
   $lang = $person['lang'];

   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_MEMBERID/', $result)) {
         $replacement = $member['member_id'];
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERPRICE/', $result)) {
         $price = eme_get_total_member_price($member);
	 $replacement = eme_localized_price($price,$membership['properties']['currency'],$target);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERCREATIONDATE\{(.+)\}/', $result,$matches)) {
	 if (!empty($member['creation_date']) && ($member['creation_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['creation_date']." ".$eme_timezone,$matches[1]);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERSTARTDATE\{(.+)\}/', $result,$matches)) {
	 if (!empty($member['start_date']) && ($member['start_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['start_date']." ".$eme_timezone,$matches[1]);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERENDDATE\{(.+)\}/', $result,$matches)) {
	 if (!empty($member['end_date']) && ($member['end_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['end_date']." ".$eme_timezone,$matches[1]);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERCREATIONDATE$/', $result)) {
	 if (!empty($member['creation_date']) && ($member['creation_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['creation_date']." ".$eme_timezone);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERSTARTDATE$/', $result)) {
	 if (!empty($member['start_date']) && ($member['start_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['start_date']." ".$eme_timezone);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MEMBERENDDATE$/', $result)) {
	 if (!empty($member['end_date']) && ($member['end_date']!='0000-00-00'))
		 $replacement = eme_localized_date($member['end_date']." ".$eme_timezone);
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
       } elseif (preg_match('/#_PAYMENT_URL/', $result)) {
	    $payment = eme_get_payment($member['payment_id']);
            $replacement = eme_payment_url($payment['random_id']);
       } elseif (preg_match('/#_DYNAMICDATA/', $result)) {
              # this should return something without br-tags, so html-mails don't get confused and
              # the function eme_nl2br_save_html can still do it's stuff based on the rest of the mail content/templates
              if (isset($membership['properties']['dyndata'])) {
                      $dyn_answers = eme_get_dyndata_member_answers($member['member_id']);
                      if (!empty($dyn_answers) && $target == "html") {
                              $replacement="<table style='border-collapse: collapse;border: 1px solid black;' class='eme_dyndata_table'";
                      }
		      $old_grouping=1;$old_occurence=0;
                      foreach ($dyn_answers as $answer) {
                              $grouping=$answer['grouping'];
                              $occurence=$answer['occurence'];
                              $class="eme_print_formfield".$answer['field_id'];
                              $tmp_formfield=eme_get_formfield($answer['field_id']);
                              if ($target == "html") {
				      if ($old_grouping!=$grouping || $old_occurence!=$occurence) {
					      $replacement.="</table><br /><br /><table style='border-collapse: collapse;border: 1px solid black;' class='eme_dyndata_table'";
					      $old_grouping=$grouping;
					      $old_occurence=$occurence;
				      }
                                      $replacement.="<tr class='eme_dyndata_row'><td style='border: 1px solid black;padding: 5px;' class='eme_dyndata_column'>".eme_esc_html($tmp_formfield['field_name']).":</td><td style='border: 1px solid black;padding: 5px;' class='eme_dyndata_column'> ".eme_esc_html(eme_convert_answer2tag($answer['answer'],$tmp_formfield))."</td></tr>";
			      } else {
                                      $replacement.=$tmp_formfield['field_name'].": ".eme_convert_answer2tag($answer['answer'],$tmp_formfield)."\n";
			      }
                      }
		      // to close the last table
                      if (!empty($dyn_answers) && $target == "html") {
                              $replacement.="</table>";
                      }
                      if ($target == "html")
                              $replacement = apply_filters('eme_general', $replacement);
                      else
                              $replacement = apply_filters('eme_text', $replacement);
	      }
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
         $field_replace = "";
         foreach ($answers as $answer) {
            if ($answer['field_id'] == $field_id) {
               $tmp_answer=eme_convert_answer2tag($answer['answer'],$formfield);
               $field_replace=$tmp_answer;
            }
         }
         if ($target == "html") {
            $replacement = eme_trans_esc_html($field_replace,$lang);
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = eme_translate($field_replace,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }
      } elseif (preg_match('/#_FIELDVALUE\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
         $field_replace = "";
         foreach ($answers as $answer) {
            if ($answer['field_id'] == $field_id) {
               if (is_array($answer['answer'])) {
                  $tmp_answer=eme_convert_array2multi($answer['answer']);
               } else {
                  $tmp_answer=$answer['answer'];
                  if ($formfield['extra_charge'])
                          $tmp_answer = eme_localized_price($tmp_answer,$event['currency']);
                  if ($formfield['field_type']==9) {
                          $frontend_format=$formfield['field_attributes'];
                          $myDateTime = DateTime::createFromFormat('Y-m-d', $tmp_answer);
                          $tmp_answer = $myDateTime->format($frontend_format);
                  }
               }
               $field_replace=$tmp_answer;
            }
         }
         if ($target == "html") {
            $replacement = eme_trans_esc_html($field_replace,$lang);
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = eme_translate($field_replace,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

       } else {
          $found = 0;
       }
       if ($found)
          $format = str_replace($orig_result, $replacement ,$format );
   }
   $format = eme_replace_membership_placeholders($format, $membership, $target, $lang);
   $format = eme_replace_people_placeholders($format, $person, $target, $lang);
   $format = eme_translate($format,$lang);
   return $format;   
}

function eme_replace_membership_placeholders($format, $membership, $target="html", $lang='') {
	preg_match_all("/#_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);
	usort($placeholders[0],'eme_sort_stringlenth');

	foreach($placeholders[0] as $result) {
		$replacement='';
		$found = 1;
		$orig_result = $result;
		if (preg_match('/#_MEMBERSHIPNAME/', $result)) {
			$replacement = $membership['name'];
			if ($target == "html") {
				$replacement = eme_esc_html($replacement);
				$replacement = apply_filters('eme_general', $replacement);
			} else {
				$replacement = apply_filters('eme_text', $replacement);
			}
		} elseif (preg_match('/#_MEMBERSHIPDESCRIPTION/', $result)) {
			$replacement = $membership['description'];
			if ($target == "html") {
				$replacement = eme_esc_html($replacement);
				$replacement = apply_filters('eme_general', $replacement);
			} else {
				$replacement = apply_filters('eme_text', $replacement);
			}
		} elseif (preg_match('/#_MEMBERSHIPPRICE/', $result)) {
			$price = $membership['properties']['price'];
			$currency = $membership['properties']['currency'];
			$replacement = eme_localized_price($price,$currency);
			if ($target == "html") {
				$replacement = eme_esc_html($replacement);
				$replacement = apply_filters('eme_general', $replacement);
			} else {
				$replacement = apply_filters('eme_text', $replacement);
			}
		} elseif (preg_match('/#_IS_LOGGED_IN/', $result)) {
			if (is_user_logged_in())
				$replacement = 1;
			else
				$replacement = 0;

		} elseif (preg_match('/#_IS_ADMIN|#_IS_ADMIN_PAGE/', $result)) {
			if (is_admin())
				$replacement = 1;
			else
				$replacement = 0;
		} else {
			$found = 0;
		}
		if ($found)
			$format = str_replace($orig_result, $replacement ,$format );
	}
	$format = eme_translate($format);
	return do_shortcode($format);
}

function eme_import_csv_members() {
	global $wpdb;
	$answers_table = $wpdb->prefix.ANSWERS_TBNAME;

	if (!current_user_can(get_option('eme_cap_cleanup'))) {
		return __('Access denied','events_made_eady');
	}
	//validate whether uploaded file is a csv file
	$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	if (empty($_FILES['eme_csv']['name']) || !in_array($_FILES['eme_csv']['type'],$csvMimes)) {
		return sprintf(__('No CSV file detected: %s','events_made_eady'),$_FILES['eme_csv']['type']);
	}
	if (!is_uploaded_file($_FILES['eme_csv']['tmp_name'])) {
		return __('Problem detected while uploading the file','events_made_eady');
	}
	$updated=0;
	$inserted=0;
	$errors=0;
	$error_msg='';
	$handle = fopen($_FILES['eme_csv']['tmp_name'], "r");
	// get the first row as keys and lowercase them
	$headers = array_map('strtolower', fgetcsv($handle));

	// check required columns
	if (!in_array('lastname',$headers)||!in_array('firstname',$headers)||!in_array('email',$headers)||!in_array('membership',$headers)||!in_array('start_date',$headers)) {
		$result = __("Not all required fields present.",'events-made-easy');
		return $result;
	} else {
		// now loop over the rest
		while (($row = fgetcsv($handle)) !== FALSE) {
			$line = array_combine($headers, $row);
			// remove columns with empty values
			$line = array_filter($line,'strlen');
			// we need at least 4 fields present, otherwise nothing will be done
			if (isset($line['lastname']) && isset($line['firstname']) && isset($line['email']) && isset($line['membership'])) {
				// if the person already exists: update him
				$person_id=0;
				$membership = eme_get_membership(0,$line['membership']);
				if ($membership) {
					$person = eme_get_person_by_name_and_email($line['lastname'],$line['firstname'],$line['email']);
					if ($person) {
						$person_id=$person['person_id'];
					} else {
						$person_id=eme_db_insert_person($line);
						if (!$person_id) {
							$errors++;
							$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
						}
					}
				} else {
					// if membership doesn't exist
					$errors++;
					$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
				}
				if ($membership && $person_id) {
					$member = eme_new_member();
					$member['person_id']=$person_id;
					$member['membership_id']=$membership['membership_id'];
					$member['status'] = isset($line['status'])? intval($line['status']) : MEMBER_STATUS_PENDING;
					$member['status_automatic'] = isset($line['status_automatic'])? intval($line['status_automatic']) : 1;
					$member['paid'] = isset($line['paid'])? intval($line['paid']) : 1;
					if (isset($line['start_date'])) {
						$member['start_date'] = $line['start_date'];
						$member['end_date'] = isset($line['end_date']) ? $line['end_date'] : eme_get_next_end_date($membership['membership_id'],$line['start_date']);
					} else {
						$member['start_date'] = '';
						$member['end_date'] = '';
					}
					$member_id=eme_db_insert_member($member);
                                        if ($member_id) {
						$inserted++;
						// now handle all the extra info, in the CSV they need to be named like 'answer_XX_fieldname' (with XX being a number starting from 0, e.g. answer_0_myfieldname)
						foreach ($line as $key=>$value) {
							if (preg_match('/^answer_(.*)$/', $key, $matches)) {
								$grouping = 0;
								$field_name = $matches[1];
								$formfield = eme_get_formfield($field_name);
								if ($formfield) {
									$field_id=$formfield['field_id'];
									$sql = $wpdb->prepare("INSERT INTO $answers_table (member_id,field_id,answer,grouping) VALUES (%d,%d,%s,%d)",$member_id,$field_id,$value,$grouping);
									$wpdb->query($sql);
								}
							}
						}
					} else {
						$errors++;
						$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					}
				}
			} else {
				// if lastname, firstname or email is empty
				$errors++;
				$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
			}

		}
	}
	fclose($handle);
	$result = sprintf(__('Import finished: %d inserts, %d updates, %d errors','events_made_eady'),$inserted,$updated,$errors);
	if ($errors) $result.='<br />'.$error_msg;
	eme_member_recalculate_status();
	return $result;
}

function eme_import_csv_member_dynamic_answers() {
	global $wpdb;
	$answers_table = $wpdb->prefix.ANSWERS_TBNAME;

        if (!current_user_can(get_option('eme_cap_cleanup'))) {
                return __('Access denied','events_made_eady');
        }

	//validate whether uploaded file is a csv file
	$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	if (empty($_FILES['eme_csv']['name']) || !in_array($_FILES['eme_csv']['type'],$csvMimes)) {
		return sprintf(__('No CSV file detected: %s','events_made_eady'),$_FILES['eme_csv']['type']);
	}
	if (!is_uploaded_file($_FILES['eme_csv']['tmp_name'])) {
		return __('Problem detected while uploading the file','events_made_eady');
	}
	$inserted=0;
	$errors=0;
	$error_msg='';
	$handle = fopen($_FILES['eme_csv']['tmp_name'], "r");
	// get the first row as keys and lowercase them
	$headers = array_map('strtolower', fgetcsv($handle));

	// check required columns
	if (!in_array('lastname',$headers)||!in_array('firstname',$headers)||!in_array('email',$headers)||!in_array('membership',$headers)) {
		$result = __("Not all required fields present.",'events-made-easy');
		return $result;
	} else {
		// now loop over the rest
		// a simple array to be able to increase occurence counter based on memberid and grouping
		$occurences=array();
		while (($row = fgetcsv($handle)) !== FALSE) {
			$line = array_combine($headers, $row);
			// remove columns with empty values
			$line = array_filter($line,'strlen');
			// we need at least 4 fields present, otherwise nothing will be done
			if (isset($line['lastname']) && isset($line['firstname']) && isset($line['email']) && isset($line['membership'])) {
				// if the person already exists: update him
				$person_id=0;
				$membership = eme_get_membership(0,$line['membership']);
				if ($membership) {
					$person = eme_get_person_by_name_and_email($line['lastname'],$line['firstname'],$line['email']);
					if ($person) {
						$person_id=$person['person_id'];
					} else {
						$errors++;
						$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					}
				}
				if ($membership && $person_id) {
					$member = eme_get_member_by_personid_membershipid($person_id,$membership['membership_id']);
					$member_id=$member['member_id'];
                                        if ($member_id) {
						if (!isset($occurences[$member_id])) $occurences[$member_id]=array();
						// make sure grouping contains a sensible value
						if (isset($line['grouping'])) {
							$grouping=intval($line['grouping']);
							if ($grouping<1) $grouping=1;
						} else {
							$grouping = 1;
						}
						if (!isset($occurences[$member_id][$grouping])) {
							$occurence=0;
						} else {
							$occurence = $occurences[$member_id][$grouping];
							$occurence++;
						}
						$occurences[$member_id][$grouping]=$occurence;
						// handle all the extra info, in the CSV they need to be named like 'answer_XX_fieldname' (with XX being a number starting from 0, e.g. answer_0_myfieldname)
						foreach ($line as $key=>$value) {
							if (preg_match('/^answer_(.*)$/', $key, $matches)) {
								$field_name = $matches[1];
								$formfield = eme_get_formfield($field_name);
								if ($formfield) {
									$field_id=$formfield['field_id'];
									$sql = $wpdb->prepare("INSERT INTO $answers_table (member_id,field_id,answer,grouping,occurence) VALUES (%d,%d,%s,%d,%d)",$member_id,$field_id,$value,$grouping,$occurence);
									$wpdb->query($sql);
									$inserted++;
								}
							}
						}
					} else {
						$errors++;
						$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					}
				} else {
					// if membership doesn't exist
					$errors++;
					$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
				}
			} else {
				// if lastname, firstname or email is empty
				$errors++;
				$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
			}

		}
	}
	fclose($handle);
	$result = sprintf(__('Import finished: %d inserts, %d errors','events_made_eady'),$inserted,$errors);
	if ($errors) $result.='<br />'.$error_msg;
	return $result;
}

add_action( 'wp_ajax_eme_members_list', 'eme_ajax_members_list' );
add_action( 'wp_ajax_eme_members_select2', 'eme_ajax_members_select2' );
add_action( 'wp_ajax_eme_memberships_list', 'eme_ajax_memberships_list' );
add_action( 'wp_ajax_eme_manage_members', 'eme_ajax_manage_members' );
add_action( 'wp_ajax_eme_manage_memberships', 'eme_ajax_manage_memberships' );

function eme_ajax_memberships_list() {
   global $wpdb;
   $table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $ajaxResult = array();

   $sql = "SELECT COUNT(*) FROM $table";
   $recordCount = $wpdb->get_var($sql);
   $start=intval($_REQUEST["jtStartIndex"]);
   $pagesize=intval($_REQUEST["jtPageSize"]);
   $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';

   $sql = "SELECT membership_id,COUNT(*) as membercount FROM $members_table WHERE status=".MEMBER_STATUS_ACTIVE." GROUP BY membership_id";
   $res=$wpdb->get_results($sql,ARRAY_A);
   $membercount=array();
   foreach ($res as $val) {
          $membercount[$val['membership_id']]=$val['membercount'];
   }

   $sql="SELECT * FROM $table $sorting LIMIT $start,$pagesize";
   $rows=$wpdb->get_results($sql,ARRAY_A);
   $records=array();
   foreach ($rows as $item) {
	 $item['properties']=eme_init_membership_props(unserialize($item['properties']));
	 $contact = eme_get_contact($item['properties']['contact_id']);
	 $contact_email = $contact->user_email;
	 $contact_name = $contact->display_name;

         $record = array();
         $record['membership_id']= $item['membership_id'];
         $record['name'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-memberships&amp;eme_admin_action=edit_membership&amp;membership_id=".$item['membership_id']),'eme_members','eme_admin_nonce')."' title='".__('Edit membership','events-made-easy')."'>".eme_esc_html($item['name'])."</a>";
         $record['description'] = eme_esc_html($item['description']);
	 $record['membercount'] = isset($membercount[$item['membership_id']]) ? $membercount[$item['membership_id']] : 0;
	 $record['contact'] = eme_esc_html("$contact_name ($contact_email)");

         $records[]  = $record;
   }
   $ajaxResult['Result'] = "OK";
   $ajaxResult['TotalRecordCount'] = $recordCount;
   $ajaxResult['Records'] = $records;
   print json_encode($ajaxResult);
   wp_die();
}

function eme_ajax_members_list() {
   global $wpdb;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships_table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $eme_member_status_array = eme_member_status_array();
   $ajaxResult = array();

   $answer_member_ids=array();
   $where_arr = array();

   // when searching for custom answers, we store the found answer_ids in an array per member
   // reason: later on we use this info to combine the relevant answers in a readable output for the table results
   // so people can see what answer triggered the found line too
   // And we ask for the fields to be returned too, so we can choose what we see in the answer column
   if (isset($_REQUEST['search_customfields']) && !empty($_REQUEST['search_customfields'])) {
	   $search_customfields=esc_sql($_REQUEST['search_customfields']);
   } else {
	   $search_customfields="";
   }
   if (isset($_REQUEST['search_person']) && !empty($_REQUEST['search_person'])) {
	   $search_person = esc_sql($_REQUEST['search_person']);
	   $where_arr[]="(people.lastname LIKE '%$search_person%' OR people.firstname LIKE '%$search_person%' OR people.email LIKE '%$search_person%')";
   }
   if (isset($_REQUEST['search_memberid']) && !empty($_REQUEST['search_memberid'])) {
	   $search_memberid=intval($_REQUEST['search_memberid']);
	   $where_arr[]="(members.member_id = $search_memberid)";
   }
   if (isset($_REQUEST['search_membershipid']) && !empty($_REQUEST['search_membershipid'])) {
	   $search_membershipid=intval($_REQUEST['search_membershipid']);
	   $where_arr[]="(members.membership_id = $search_membershipid)";
   }
   // search_status can be 0 too, for pending
   if (isset($_REQUEST['search_status']) && (!empty($_REQUEST['search_status']) || $_REQUEST['search_status']==='0')) {
	   $search_status=intval($_REQUEST['search_status']);
	   $where_arr[]="(members.status = $search_status)";
   }
   if (!empty($where_arr)) {
	   $where = 'WHERE '. join(' AND ',$where_arr);
   } else {
	   $where="";
   }


   $start=intval($_REQUEST["jtStartIndex"]);
   $pagesize=intval($_REQUEST["jtPageSize"]);
   $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
   // we can sort on member_id, but in our constructed sql below, we have members.member_id and ans.member_id
   // some mysql databases don't like it if you then just sort on member_id, so we'll change it to members.member_id
   $sorting = str_replace("member_id","members.member_id",$sorting);
   if (isset($_REQUEST['search_customfieldids']) && !empty($_REQUEST['search_customfieldids'])) {
	   $field_ids=join(',',$_REQUEST['search_customfieldids']);
	   $formfields = eme_get_formfields($field_ids);
   } else {
	   $formfields = array();
	   $field_ids='';
   }
   if (!empty($search_customfields)) {
	   if (empty($formfields)) {
		   // by default we filter based on members and joined with memberships table (for membership name) and people (for person name and email)
		   // since we search on custom fields we join the answerstable where we find the memberids that interest us based on the answer found
		   // the "GROUP BY a.member_id" is to be sure we only return 1 line per found member
		   $sql1 = "SELECT
			   COUNT(*)
			   FROM $members_table AS members
			   LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
			   LEFT JOIN $people_table as people ON members.person_id=people.person_id
			   JOIN (SELECT
			         a.member_id
			         FROM $answers_table a
				 WHERE a.answer LIKE '%$search_customfields%' AND a.member_id>0
			         GROUP BY a.member_id
				 ) ans
                           ON members.member_id=ans.member_id $where";
		   $sql = "SELECT
			   members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
			   FROM $members_table AS members
			   LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
			   LEFT JOIN $people_table as people ON members.person_id=people.person_id
			   JOIN (SELECT
			         a.member_id
			         FROM $answers_table a
				 WHERE a.answer LIKE '%$search_customfields%' AND a.member_id>0
			         GROUP BY a.member_id
				 ) ans
                           ON members.member_id=ans.member_id $where $sorting LIMIT $start,$pagesize";
	   } else {
		   // by default we filter based on members and joined with memberships table (for membership name) and people (for person name and email)
		   // since we search on custom fields we join the answerstable where we find the memberids that interest us based on the answer found
		   // and since we want custom answers to be returned we do a group_concat (that works on the grouping done by GROUP BY a.member_id, a.grouping, a.occurence)
		   // so we return 1 line per member,grouping,occurence
		   $group_concat_sql="";
		   foreach ($formfields as $formfield) {
			   $field_id=$formfield['field_id'];
			   $group_concat_sql.="GROUP_CONCAT(CASE WHEN a.field_id = $field_id THEN a.answer END) AS 'answer_$field_id',";
		   }
		   $sql1 = "SELECT
			   COUNT(*)
                           FROM $members_table AS members
                           LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
                           LEFT JOIN $people_table as people ON members.person_id=people.person_id
                           JOIN (SELECT $group_concat_sql
			         a.member_id
			         FROM $answers_table a
                                 JOIN (SELECT grouping,occurence,member_id
				       FROM $answers_table WHERE answer LIKE '%$search_customfields%' AND member_id>0 AND field_id IN ($field_ids)
                                      ) c
                                 ON a.member_id=c.member_id AND a.grouping=c.grouping AND a.occurence=c.occurence
                                 GROUP BY a.member_id, a.grouping, a.occurence
                                 ) ans
                           ON members.member_id=ans.member_id $where";
		   $sql = "SELECT
                           ans.*,members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
                           FROM $members_table AS members
                           LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
                           LEFT JOIN $people_table as people ON members.person_id=people.person_id
                           JOIN (SELECT $group_concat_sql
			         a.member_id
			         FROM $answers_table a
                                 JOIN (SELECT grouping,occurence,member_id
				       FROM $answers_table WHERE answer LIKE '%$search_customfields%' AND member_id>0 AND field_id IN ($field_ids)
                                      ) c
                                 ON a.member_id=c.member_id AND a.grouping=c.grouping AND a.occurence=c.occurence
                                 GROUP BY a.member_id, a.grouping, a.occurence
                                 ) ans
                           ON members.member_id=ans.member_id $where $sorting LIMIT $start,$pagesize";
	   }

   } else {
	if (empty($formfields)) {
	   // by default we filter based on members and joined with memberships table (for membership name) and people (for person name and email)
	   $sql1="SELECT
		  COUNT(*) FROM $members_table AS members
		  LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
		  LEFT JOIN $people_table as people ON members.person_id=people.person_id $where";
	   $sql="SELECT
		 members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
		 FROM $members_table AS members
                 LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
                 LEFT JOIN $people_table as people ON members.person_id=people.person_id $where $sorting LIMIT $start,$pagesize";
	} else {
	   // by default we filter based on members and joined with memberships table (for membership name) and people (for person name and email)
	   // and since we want custom answers to be returned we do a group_concat (that works on the grouping done by GROUP BY a.member_id, a.grouping, a.occurence)
	   // so we return 1 line per member,grouping,occurence
           $group_concat_sql="";
	   foreach ($formfields as $formfield) {
		$field_id=$formfield['field_id'];
		$group_concat_sql.="GROUP_CONCAT(CASE WHEN a.field_id = $field_id THEN a.answer END) AS 'answer_$field_id',";
	   }
	   $sql1="SELECT
		  COUNT(*) FROM $members_table AS members
		  LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
		  LEFT JOIN $people_table as people ON members.person_id=people.person_id 
                  LEFT JOIN (SELECT $group_concat_sql
		       a.member_id
		       FROM $answers_table a
                       WHERE a.member_id>0 AND a.field_id IN ($field_ids)
                       GROUP BY a.member_id, a.grouping, a.occurence
                       ) ans
                  ON members.member_id=ans.member_id $where";
	   $sql="SELECT
		 ans.*, members.*, people.lastname, people.firstname, people.email, memberships.name AS membership_name
		 FROM $members_table AS members
                 LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
                 LEFT JOIN $people_table as people ON members.person_id=people.person_id
                 LEFT JOIN (SELECT $group_concat_sql
		       a.member_id
		       FROM $answers_table a
                       WHERE a.member_id>0 AND a.field_id IN ($field_ids)
                       GROUP BY a.member_id, a.grouping, a.occurence
                       ) ans
                 ON members.member_id=ans.member_id $where $sorting LIMIT $start,$pagesize";
	}
   }
   $recordCount = $wpdb->get_var($sql1);

   $rows=$wpdb->get_results($sql,ARRAY_A);
   $records=array();
   foreach ($rows as $item) {
         $record = array();
         $record['member_id']= $item['member_id'];
         $record['lastname'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-members&amp;eme_admin_action=edit_member&amp;member_id=".$item['member_id']),'eme_members','eme_admin_nonce')."' title='".__('Edit member','events-made-easy')."'>".eme_esc_html($item['lastname'])."</a>";
         $record['firstname'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-members&amp;eme_admin_action=edit_member&amp;member_id=".$item['member_id']),'eme_members','eme_admin_nonce')."' title='".__('Edit member','events-made-easy')."'>".eme_esc_html($item['firstname'])."</a>";
         $record['email'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-members&amp;eme_admin_action=edit_member&amp;member_id=".$item['member_id']),'eme_members','eme_admin_nonce')."' title='".__('Edit member','events-made-easy')."'>".eme_esc_html($item['email'])."</a>";
         $record['membership_name'] = eme_esc_html($item['membership_name']);
         $record['start_date'] = ($item['start_date'] == '0000-00-00' || $item['start_date'] == '' ) ? '' : eme_localized_date($item['start_date']);
         $record['end_date'] = ($item['end_date'] == '0000-00-00' || $item['end_date'] == '') ? '' : eme_localized_date($item['end_date']);
         $record['creation_date'] = ($item['creation_date'] == '0000-00-00' || $item['creation_date'] == '') ? '' : eme_localized_date($item['creation_date']);
         $record['payment_date'] = ($item['payment_date'] == '0000-00-00 00:00:00' || $item['payment_date'] == '') ? '' : eme_localized_date($item['payment_date']);
         $record['reminder'] = intval($item['reminder']);
         $record['reminder_date'] = ($item['reminder_date'] == '0000-00-00 00:00:00' || $item['reminder_date'] == '') ? '' : eme_localized_date($item['reminder_date']);
         $record['paid'] = ($item['paid']==1) ? __('Yes', 'events-made-easy') : __('No', 'events-made-easy');
         $record['status'] = $eme_member_status_array[$item['status']];
	 if (!empty($formfields)) {
		 foreach ($formfields as $formfield) {
		     foreach ($item as $key=>$val) {
			 // the beginning of the key ('answer_') comes from the group-concat statement above
			 if ($key=='answer_'.$formfield['field_id'] && $val!='') {
				 $tmp_answer=eme_convert_answer2tag($val,$formfield);
				 // the 'FIELD_' value is used by the container-js
				 $record['FIELD_'.$formfield['field_id']]=$tmp_answer;
			 }
		     }
		 }
	 }
         $records[]  = $record;
   }
   $ajaxResult['Result'] = "OK";
   $ajaxResult['TotalRecordCount'] = $recordCount;
   $ajaxResult['Records'] = $records;
   print json_encode($ajaxResult);
   wp_die();
}

function eme_ajax_members_select2() {
   global $wpdb;

   check_ajax_referer('eme_members','eme_admin_nonce');

   $table = $wpdb->prefix.MEMBERS_TBNAME;
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $memberships_table = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   $jTableResult = array();
   $q = isset($_REQUEST['q']) ? strtolower($_REQUEST['q']) : '';
   if (!empty($q)) {
           $where = "(people.lastname LIKE '%".esc_sql($q)."%' OR people.firstname LIKE '%".esc_sql($q)."%')";
   } else {
           $where = '(1=1)';
   }
   $pagesize = intval($_REQUEST["pagesize"]);
   //$start= isset($_REQUEST["page"]) ? intval($_REQUEST["page"])*$pagesize : 0;
   $start= (isset($_REQUEST["page"]) && intval($_REQUEST["page"])>0) ? (intval($_REQUEST["page"])-1)*$pagesize : 0;
   $sql = "SELECT members.member_id, people.lastname, people.firstname, memberships.name AS membership_name
           FROM $members_table AS members
           LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
           LEFT JOIN $people_table as people ON members.person_id=people.person_id
           WHERE $where ORDER BY people.lastname, people.firstname LIMIT $start,$pagesize";
   $count_sql = "SELECT count(*)
           FROM $members_table AS members
           LEFT JOIN $memberships_table AS memberships ON members.membership_id=memberships.membership_id
           LEFT JOIN $people_table as people ON members.person_id=people.person_id
           WHERE $where";

   $records=array();
   $recordCount = $wpdb->get_var($count_sql);
   $members = $wpdb->get_results($sql,ARRAY_A);
   foreach ($members as $member) {
         $record = array();
         $record['id']= $member['member_id'];
         // no eme_esc_html here, select2 does it own escaping upon arrival
         $record['text'] = $member['lastname'].' '.$member['firstname'].' ('.$member['membership_name'].')';
         $records[]  = $record;
   }
   $jTableResult['TotalRecordCount'] = $recordCount;
   $jTableResult['Records'] = $records;
   print json_encode($jTableResult);
   wp_die();
}


function eme_get_grouped_member_answer($answer_id,$field_ids) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME;
   if (!empty($field_ids))
	   $selected_fields="AND a.field_id IN (". join(',',$field_ids).")";
   else
	   $selected_fields="";
   $sql = "select CONCAT_WS(': ',field_name,answer) from $answers_table a LEFT JOIN $formfields_table b ON b.field_id=a.field_id JOIN (select grouping,occurence,member_id from $answers_table where answer_id=$answer_id) c WHERE a.member_id=c.member_id and a.grouping=c.grouping and a.occurence=c.occurence $selected_fields";
   $lines = $wpdb->get_col($sql);
   return join(', ',$lines);
}

function eme_ajax_manage_members() {
   check_ajax_referer('eme_members','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
      $do_action=eme_sanitize_request($_REQUEST['do_action']);
      $send_mail=(isset($_POST['send_mail'])) ? intval($_POST['send_mail']) : 1;
      $pdf_template=(isset($_POST['pdf_template'])) ? intval($_POST['pdf_template']) : 0;
      $ids_arr=explode(',',$_POST['member_id']);

      switch ($do_action) {
         case 'deleteMembers':
              eme_ajax_action_delete_members($ids_arr);
              break;
         case 'markPaid':
              eme_ajax_action_set_member_paid($ids_arr, 'markPaid', $send_mail);
              break;
         case 'markUnpaid':
              eme_ajax_action_set_member_unpaid($ids_arr, 'updateMember', $send_mail);
              break;
         case 'extendMembership':
              eme_ajax_action_extend_membership($ids_arr, 'extendMember', $send_mail);
              break;
         case 'renewExpiredMembership':
              eme_ajax_action_renew_expired_membership($ids_arr, 'extendMember', $send_mail);
              break;
         case 'stopMembership':
              eme_ajax_action_stop_membership($ids_arr, 'stopMember', $send_mail);
              break;
         case 'resendPendingMember':
              eme_ajax_action_resend_pending_member($ids_arr, 'remindNewMember');
              break;
         case 'pdf':
	      $template=eme_get_template($pdf_template);
              eme_ajax_generate_member_pdf($ids_arr,$template);
              break;
      }
   }
   wp_die();
}

function eme_ajax_manage_memberships() {
   $ajaxResult=array();
   check_ajax_referer('eme_members','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
      $do_action=eme_sanitize_request($_REQUEST['do_action']);
      $ids_arr=explode(',',$_POST['membership_id']);
      switch ($do_action) {
         case 'deleteMemberships':
              if (eme_array_integers($ids_arr) && current_user_can( get_option('eme_cap_members'))) {
                 foreach ($ids_arr as $membership_id) {
                    eme_delete_membership($membership_id);
                 }
                 $ajaxResult['result'] = "OK";
		 $ajaxResult['htmlmessage'] = __('Memberships deleted.','events-made-easy');
              } else {
                 $ajaxResult['result'] = "Error";
                 $ajaxResult['htmlmessage'] = __('Access denied!','events-made-easy');
              }
              print json_encode($ajaxResult);
              break;
      }
   }
   wp_die();
}

function eme_ajax_action_delete_members($ids_arr) {
   $ajaxResult=array();
   if (eme_array_integers($ids_arr) && current_user_can( get_option('eme_cap_members'))) {
	   foreach ($ids_arr as $member_id) {
		   eme_delete_member($member_id);
	   }
	   $ajaxResult['result'] = "OK";
	   $ajaxResult['htmlmessage'] = __('Members deleted.','events-made-easy');
   } else {
	   $ajaxResult['result'] = "Error";
	   $ajaxResult['htmlmessage'] = __('Access denied!','events-made-easy');
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_set_member_paid($ids_arr,$action,$send_mail) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;

   foreach ($ids_arr as $member_id) {
       $res = eme_member_set_paid($member_id);
       if ($res) {
               if ($send_mail) {
                       $member = eme_get_member($member_id);
                       $res2 = eme_email_member_booking($member,$action,$queue);
                       if (!$res2) $mails_ok=0;
               }
       } else {
               $action_ok=0;
       }
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_set_member_unpaid($ids_arr,$action,$send_mail) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;

   foreach ($ids_arr as $member_id) {
       $res = eme_member_set_unpaid($member_id);
       if ($res) {
               if ($send_mail) {
                       $member = eme_get_member($member_id);
                       $res2 = eme_email_member_booking($member,$action,$queue);
                       if (!$res2) $mails_ok=0;
               }
       } else {
               $action_ok=0;
       }
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_extend_membership($ids_arr,$action,$send_mail) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;
   foreach ($ids_arr as $member_id) {
       // expired memberships will return a 1, to not trigger a warning
       $res = eme_extend_member($member_id);
       if ($res) {
               if ($send_mail) {
                       $member = eme_get_member($member_id);
                       $res2 = eme_email_member_booking($member,$action,$queue);
                       if (!$res2) $mails_ok=0;
               }
       } else {
               $action_ok=0;
       }
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_renew_expired_membership($ids_arr,$action,$send_mail) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;
   foreach ($ids_arr as $member_id) {
       $res = eme_renew_expired_member($member_id);
       if ($res) {
               if ($send_mail) {
                       $member = eme_get_member($member_id);
                       $res2 = eme_email_member_booking($member,$action,$queue);
                       if (!$res2) $mails_ok=0;
               }
       } else {
               $action_ok=0;
       }
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_stop_membership($ids_arr,$action,$send_mail) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;
   foreach ($ids_arr as $member_id) {
       $res = eme_stop_member($member_id);
       if ($res) {
               if ($send_mail) {
                       $member = eme_get_member($member_id);
                       $res2 = eme_email_member_booking($member,$action,$queue);
                       if (!$res2) $mails_ok=0;
               }
       } else {
               $action_ok=0;
       }
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_action_resend_pending_member($ids_arr,$action) {
   $action_ok=1;
   $mails_ok=1;
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = ($eme_cron_queue_count && count($ids_arr) > $eme_cron_queue_count)?1:0;
   foreach ($ids_arr as $member_id) {
        $member = eme_get_member($member_id);
	if ($member['status']==MEMBER_STATUS_PENDING) {
		$res2 = eme_email_member_booking($member,$action,$queue);
		if (!$res2) $mails_ok=0;
	}
   }
   $ajaxResult=array();
   if ($mails_ok && $action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "OK";
   } elseif ($action_ok) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('The action has been executed successfully but there were some problems while sending mail.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   } else {
         $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There was a problem executing the desired action, please check your logs.','events-made-easy')."</p></div>";
         $ajaxResult['result'] = "ERROR";
   }
   print json_encode($ajaxResult);
}

function eme_ajax_generate_member_pdf($ids_arr,$template) {
	require_once("dompdf/autoload.inc.php");
	// instantiate and use the dompdf class
	$dompdf = new Dompdf\Dompdf();
	$margin_info = "margin: ".$template['properties']['pdf_margins'].";";
	$orientation = $template['properties']['pdf_orientation'];
	$pagesize = $template['properties']['pdf_size'];
	if ($pagesize == "custom" )
		$pagesize = array(0,0,$template['properties']['pdf_width'],$template['properties']['pdf_height']);
		
	$dompdf->setPaper($pagesize, $orientation);
	$html="
<html>
<head>
<style>
    @page {
        $margin_info;
    }
    div.page-break {
        page-break-before: always;
    }
</style>
</head>

<body>
";
	$total = count($ids_arr);
	$i=1;
	foreach ($ids_arr as $member_id) {
		$member=eme_get_member($member_id);
		$membership=eme_get_membership($member['membership_id']);
		$html.=eme_replace_member_placeholders($template['format'],$membership,$member);
		if ($i < $total) {
			// dompdf uses a style to detect forced page breaks
			$html.='<div class="page-break"></div>';
			$i++;
		}
	}
	$html .= '
</body>
</html>';

	$dompdf->loadHtml($html);
	$dompdf->render();
	$dompdf->stream();
}

?>
