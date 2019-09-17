<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_set_wpmail_html_content_type() {
	return "text/html";
}

// phpmailer support
function eme_send_mail($subject,$body, $receiveremail, $receivername='', $replytoemail='', $replytoname='', $queue=0, $mailing_id=0, $person_id=0, $member_id=0) {
   // don't send empty mails
   $subject = preg_replace("/(^\s+|\s+$)/m","",$subject);
   if (empty($body) || empty($subject)) return true;

   if ($queue && get_option('eme_queue_mails')) {
	return eme_queue_mail($subject,$body, $receiveremail, $receivername, $replytoemail, $replytoname, $mailing_id, $person_id, $member_id);
   }

   $eme_rsvp_mail_send_method = get_option('eme_rsvp_mail_send_method');
   $eme_rsvp_send_html = get_option('eme_rsvp_send_html');
   if (get_option('eme_mail_sender_address') == "") {
      $fromMail = $replytoemail;
      $fromName = $replytoname;
   } else {
      $fromMail = get_option('eme_mail_sender_address');
      $fromName = get_option('eme_mail_sender_name'); // This is the from name in the email, you can put anything you like here
   }
   $eme_bcc_address= get_option('eme_mail_bcc_address');

   if ($eme_rsvp_mail_send_method == 'wp_mail') {
      // Set the correct mail headers
      $headers[] = "From: $fromName <$fromMail>";
      if ($replytoemail != "")
         $headers[] = "ReplyTo: $replytoname <$replytoemail>";
      if (!empty($eme_bcc_address))
         $headers[] = "Bcc: $eme_bcc_address";

      // set the correct content type
      if ($eme_rsvp_send_html)
          add_filter('wp_mail_content_type','eme_set_wpmail_html_content_type');

      // now send it
      $res = wp_mail( $receiveremail, $subject, $body, $headers );  

      // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
      if ($eme_rsvp_send_html)
         remove_filter('wp_mail_content_type','eme_set_wpmail_html_content_type');

      return $res;

   } else {
      require_once(ABSPATH . WPINC . "/class-phpmailer.php");
      // In the past there was a bug in class-phpmailer from wordpress, so we needed to copy class-smtp.php
      // in this dir for smtp to work, but no longer
      
      if (class_exists('PHPMailer')) {
         $mail = new PHPMailer();
         $mail->ClearAllRecipients();
         $mail->ClearAddresses();
         $mail->ClearAttachments();
         $mail->CharSet = 'utf-8';
         $mail->SetLanguage('en', dirname(__FILE__).'/');

         $mail->PluginDir = dirname(__FILE__).'/';
         if ($eme_rsvp_mail_send_method == 'qmail')
            $mail->IsQmail();
         else
            $mail->Mailer = $eme_rsvp_mail_send_method;

         if ($eme_rsvp_mail_send_method == 'smtp') {
            // let us keep a normal smtp timeout ...
            $mail->Timeout = 10;
            if (get_option('eme_smtp_host'))
               $mail->Host = get_option('eme_smtp_host');
            else
               $mail->Host = "localhost";

	    // we set optional encryption and port settings
	    // but if the Host contains ssl://, tls:// or port info, it will take precedence over these anyway
	    // so it is not bad at all :-)
	    if (get_option('eme_smtp_encryption') && get_option('eme_smtp_encryption') != "none") {
	       $mail->SMTPSecure=get_option('eme_smtp_encryption');
	       if (!get_option('eme_smtp_verify_cert')) {
	          $mail->SMTPOptions = array(
                     'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                     )
                  );
               }
            }

            if (get_option('eme_smtp_port'))
               $mail->Port = get_option('eme_smtp_port');
            else
               $mail->Port = 25;

            if (get_option('eme_rsvp_mail_SMTPAuth') == '1') {
               $mail->SMTPAuth = true;
               $mail->Username = get_option('eme_smtp_username');
               $mail->Password = get_option('eme_smtp_password');
            }
            if (get_option('eme_smtp_debug'))
               $mail->SMTPDebug = 2;
         }
         $mail->setFrom($fromMail,$fromName);
         if ($eme_rsvp_send_html) {
	    $mail->isHTML(true);
            // Convert all message body line breaks to CRLF, makes quoted-printable encoding work much better
            $mail->AltBody = $mail->normalizeBreaks($mail->html2text($body));
            $mail->Body = eme_nl2br_save_html($mail->normalizeBreaks($body));
	 } else {
            $mail->Body = $mail->normalizeBreaks($body);
	 }
         $mail->Subject = $subject;
         if (!empty($replytoemail))
            $mail->addReplyTo($replytoemail,$replytoname);
         if (!empty($eme_bcc_address))
            $mail->addBCC($eme_bcc_address);

         if (!empty($receiveremail)) {
            $mail->addAddress($receiveremail,$receivername);
            if(!$mail->send()){
               return false;
            } else {
               return true;
            }
         } else {
            return false;
         }
      }
   }
}

function eme_insert_mailing($mailing_name, $planned_on, $mail_count, $subject, $body, $replytoemail, $replytoname, $mail_text_html) {
	global $wpdb;
	$mailing_table = $wpdb->prefix.MAILINGS_TBNAME;
	$mailing = array(
		'name'=>$mailing_name,
		'planned_on'=>$planned_on,
		'mail_count'=>$mail_count,
		'subject'=>$subject,
		'body'=>$body,
		'replytoemail'=>$replytoemail,
		'replytoname'=>$replytoname,
		'mail_text_html'=>$mail_text_html,
		'creation_date'=>current_time('mysql', false)
	);
	if ($wpdb->insert($mailing_table, $mailing) === false) {
		return false;
	} else {
		return $wpdb->insert_id;
	}

}

function eme_queue_mailing_mail($mailing_id, $receiveremail, $receivername, $person_id, $member_id=0) {
	return eme_queue_mail('','', $receiveremail, $receivername, '', '', $mailing_id, $person_id, $member_id);
}

function eme_queue_mail($subject,$body, $receiveremail, $receivername, $replytoemail, $replytoname, $mailing_id=0, $person_id=0, $member_id=0) {
	global $wpdb;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$mail = array(
		'subject'=>$subject,
		'body'=>$body,
		'receiveremail'=>$receiveremail,
		'receivername'=>$receivername,
		'replytoemail'=>$replytoemail,
		'replytoname'=>$replytoname,
		'mailing_id'=>$mailing_id,
		'person_id'=>$person_id,
		'member_id'=>$member_id,
		'creation_date'=>current_time('mysql', false)
	);
	if ($wpdb->insert($mqueue_table, $mail) === false)
		return false;
	else return true;
}

function eme_mark_mail_sent($id,$subject="",$body="") {
	global $wpdb;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$where = array();
	$fields = array();
	$where['id'] = intval($id);
	$fields['status'] = 1;
	$fields['sent_datetime']=current_time('mysql', false);
	if (!empty($subject))
		$fields['subject']=$subject;
	if (!empty($body))
		$fields['body']=$body;
	if ($wpdb->update($mqueue_table, $fields, $where) === false)
		return false;
	else return true;
}

function eme_mark_mail_fail($id) {
	global $wpdb;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$where = array();
	$fields = array();
	$where['id'] = intval($id);
	$fields['status'] = 2;
	if ($wpdb->update($mqueue_table, $fields, $where) === false)
		return false;
	else return true;
}

function eme_remove_all_queued() {
	global $wpdb;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "DELETE FROM $mqueue_table WHERE status=0";
	return $wpdb->query($sql);
}

function eme_remove_old_queued() {
	global $wpdb;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "DELETE FROM $mqueue_table WHERE status=0 AND DATE_SUB(CURDATE(),INTERVAL 7 DAY) >= creation_date";
	return $wpdb->query($sql);
}

function eme_get_queued_count() {
	global $wpdb, $eme_timezone;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
	$now=$eme_date_obj_now->getDateTime();
	$sql = "SELECT COUNT(*) FROM $mqueue_table as mqueue LEFT JOIN $mailings_table as mailings ON mqueue.mailing_id=mailings.id WHERE mqueue.status=0 AND (mqueue.mailing_id=0 OR (mqueue.mailing_id>0 and mailings.planned_on<'$now'))";
        return $wpdb->get_var($sql);
}

function eme_get_queued() {
	global $wpdb, $eme_timezone;
	$mqueue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
	$now=$eme_date_obj_now->getDateTime();
	$sql = "SELECT mqueue.* FROM $mqueue_table AS mqueue LEFT JOIN $mailings_table AS mailings ON mqueue.mailing_id=mailings.id WHERE mqueue.status=0 AND (mqueue.mailing_id=0 OR (mqueue.mailing_id>0 and mailings.planned_on<'$now'))";
	$eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
	if ($eme_cron_queue_count>0)
		$sql .= " LIMIT $eme_cron_queue_count";
        return $wpdb->get_results($sql, ARRAY_A);
}

function eme_send_queued() {
	$mails = eme_get_queued();
	foreach ($mails as $mail) {
		// if placeholders were not yet replaced for member/person mails, check for mailing id and replace them as needed
		// currently we replace the placeholders at queueing time, so not needed yet
		$res = eme_send_mail($mail['subject'],$mail['body'], $mail['receiveremail'], $mail['receivername'], $mail['replytoemail'], $mail['replytoname']);
		if ($res) {
			eme_mark_mail_sent($mail['id']);
		} else {
			eme_mark_mail_fail($mail['id']);
		}
		// be nice, sleep for half a second
		usleep(500000);
	}
}

function eme_cancel_mailing($id) {
	global $wpdb;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$sql = "UPDATE $mailings_table set cancelled=1 where id=$id";
        $wpdb->query($sql);
	$queue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "UPDATE $queue_table set status=3 where mailing_id=$id";
        $wpdb->query($sql);
}

function eme_delete_mailing($id) {
	global $wpdb;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$sql = "DELETE FROM $mailings_table where id=$id";
        $wpdb->query($sql);
	$queue_table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "DELETE FROM $queue_table where mailing_id=$id";
        $wpdb->query($sql);
}

function eme_get_mail($id) {
	global $wpdb;
	$table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "SELECT * from $table WHERE id=$id";
        return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_mailing($id) {
	global $wpdb;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$sql = "SELECT * from $mailings_table WHERE id=$id";
        return $wpdb->get_row($sql, ARRAY_A);
}
function eme_get_mailings() {
	global $wpdb;
	$mailings_table = $wpdb->prefix.MAILINGS_TBNAME;
	$sql = "SELECT * from $mailings_table ORDER by planned_on,name";
        return $wpdb->get_results($sql, ARRAY_A);
}

function eme_mailing_states() {
	$states = array(
		0 => 'planned',
		1 => 'sent',
		2 => 'failed',
		3 => 'cancelled'
	);
	return $states;
}
function eme_mailing_localizedstates() {
	$states = array(
		0 => __('Planned','events-made-easy'),
		1 => __('Sent','events-made-easy'),
		2 => __('Failed','events-made-easy'),
		3 => __('Cancelled','events-made-easy')
	);
	return $states;
}

function eme_get_mailing_stats() {
	global $wpdb;
	$table = $wpdb->prefix.MQUEUE_TBNAME;
	$sql = "SELECT COUNT(*) as count,status,mailing_id from $table GROUP by mailing_id,status";
        $lines = $wpdb->get_results($sql, ARRAY_A);
	$res=array();
	$states=eme_mailing_states();
	foreach ($lines as $line) {
		$id=$line['mailing_id'];
		$status=$states[$line['status']];
		$res[$id][$status]=$line['count'];
	}
	return $res;
}

add_action( 'wp_ajax_eme_mailingreport_list', 'eme_mailingreport_list' );
function eme_mailingreport_list() {
   global $wpdb;
   $table = $wpdb->prefix.MQUEUE_TBNAME;
   if (!isset($_REQUEST['mailing_id'])) return;
   $mailing_id=intval($_REQUEST['mailing_id']);
   $search_name = isset($_REQUEST['search_name']) ? esc_sql($_REQUEST['search_name']) : '';
   $where="";
   $where_arr=array();
   $where_arr[]= '(mailing_id='.intval($_REQUEST['mailing_id']) . ')';
   if (!empty($search_name)) {
      $where_arr[] = "(receivername like '%$search_name%' OR receiveremail like '%$search_name%')";
   }

   if (!empty($where_arr))
      $where = " WHERE ".implode(" AND ",$where_arr);

   $jTableResult = array();
   $sql = "SELECT COUNT(*) FROM $table $where";
   $recordCount = $wpdb->get_var($sql);
   $start=intval($_REQUEST["jtStartIndex"]);
   $pagesize=intval($_REQUEST["jtPageSize"]);
   $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
   $sql="SELECT * FROM $table $where $sorting LIMIT $start,$pagesize";
   $rows=$wpdb->get_results($sql,ARRAY_A);
   $records=array();
   $states=eme_mailing_localizedstates();
   foreach ($rows as $item) {
         $record = array();
         $id= $item['id'];
         $record['receiveremail']= $item['receiveremail'];
         $record['receivername']= $item['receivername'];
         $record['status']= $states[$item['status']];
	 if ($item['status']>0) {
		 $localized_datetime = eme_localized_date($item['sent_datetime']).' '.eme_localized_time($item['sent_datetime']);
		 $record['sent_datetime']=$localized_datetime; 
		 $record['action'] = " <a href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=reuse_mail&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Reuse','events-made-easy')."</a>";
	 } else {
		 $record['sent_datetime']=''; 
		 $record['action']=''; 
	 }
         $records[]  = $record;
   }
   $jTableResult['Result'] = "OK";
   $jTableResult['TotalRecordCount'] = $recordCount;
   $jTableResult['Records'] = $records;
   print json_encode($jTableResult);
   wp_die();
}

function eme_send_mails_ajax_actions($action) {
   $event_ids = isset($_POST['event_ids']) ? $_POST['event_ids'] : 0;
   $ajaxResult = array();

   if ($action == 'testmail') {
      $testmail_to = stripslashes_deep($_POST['testmail_to']);
      if (!is_email($testmail_to)) {
	 $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please enter a valid mail address','events-made-easy')."</p></div>";
	 $ajaxResult['result'] = "ERROR";
	 echo json_encode($ajaxResult);
	 return;
      }

      $contact_email = get_option('eme_mail_sender_address');
      $contact_name = get_option('eme_mail_sender_name');
      if (empty($contact_email)) {
	      $blank_event = eme_new_event();
	      $contact = eme_get_event_contact($blank_event);
	      $contact_email = $contact->user_email;
	      $contact_name = $contact->display_name;
      }
      $person_name = "test recipient EME";
      $tmp_subject = "test subject EME";
      $tmp_message = "test message EME";
      $mail_res=eme_send_mail($tmp_subject,$tmp_message, $testmail_to, $person_name, $contact_email, $contact_name);
      if ($mail_res) {
         $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The mail has been sent.','events-made-easy')."</p></div>";
	 $ajaxResult['result'] = "OK";
      } else {
	 $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There were some problems while sending mail.','events-made-easy')."</p></div>";
	 $ajaxResult['result'] = "ERROR";
      }
      echo json_encode($ajaxResult);
      return;
   }

   if ($action == 'genericmail' || $action == 'previewmail') {
	   $queue=intval(get_option('eme_queue_mails'));
	   if (isset($_POST['generic_mail_subject']) && !empty($_POST['generic_mail_subject']))
		   $mail_subject = stripslashes_deep($_POST['generic_mail_subject']);
	   elseif (isset($_POST['generic_subject_template']) && intval($_POST['generic_subject_template'])>0)
		   $mail_subject = eme_get_template_format(intval($_POST['generic_subject_template']));
	   else
		   $mail_subject = "";

	   if (isset($_POST['generic_mail_message']) && !empty($_POST['generic_mail_message']))
		   $mail_message = stripslashes_deep($_POST['generic_mail_message']);
	   elseif (isset($_POST['generic_message_template']) && intval($_POST['generic_message_template'])>0)
		   $mail_message = eme_get_template_format(intval($_POST['generic_message_template']));
	   else
		   $mail_message = "";
	   if (empty($mail_subject) || empty($mail_message)) {
		 $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please enter both subject and message for the mail to be sent.','events-made-easy')."</p></div>";
		 $ajaxResult['result'] = "ERROR";
		 echo json_encode($ajaxResult);
		 return;
	   }

	   if ($action == 'genericmail') {
		 if (isset($_POST['send_now']) && $_POST['send_now']==1) {
			 $queue=0;
		 }
		 if ($queue && (!isset($_POST['mailing_name']) || empty($_POST['mailing_name']))) {
			 $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please enter a name for the mailing.','events-made-easy')."</p></div>";
			 $ajaxResult['result'] = "ERROR";
			 echo json_encode($ajaxResult);
			 return;
		 }
	   }

	   $mail_text_html=get_option('eme_rsvp_send_html')?"html":"text";
	   $contact_email = get_option('eme_mail_sender_address');
	   $contact_name = get_option('eme_mail_sender_name');
	   if (empty($contact_email)) {
		   $blank_event = eme_new_event();
		   $contact = eme_get_event_contact($blank_event);
		   $contact_email = $contact->user_email;
		   $contact_name = $contact->display_name;
	   }
	   $mail_problems=false;
	   $mailing_id=0;
	   $person_ids = array();  
	   $member_ids = array();  
	   if ($action=='previewmail') {
		   // no queueing for test email
		   $queue = 0;
		   $preview_mail_to = intval($_POST['send_previewmailto_id']);
		   if ($preview_mail_to==0) {
			   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please select a person to send the preview mail to.','events-made-easy')."</p></div>";
			   $ajaxResult['result'] = "ERROR";
			   echo json_encode($ajaxResult);
			   return;
		   } else {
			   $person_ids[] = $preview_mail_to;
		   }
	   } else {
		   if (isset($_POST['eme_send_all_people'])) {
			   $person_ids = eme_get_massmail_persons();
		   } else {
			   if (isset($_POST['eme_send_persons']) && !empty($_POST['eme_send_persons']) && eme_array_integers($_POST['eme_send_persons'])) {
			   	$person_ids = $_POST['eme_send_persons'];
			   }
			   if (isset($_POST['eme_send_members']) && !empty($_POST['eme_send_members']) && eme_array_integers($_POST['eme_send_members'])) {
			   	$member_ids = $_POST['eme_send_members'];
			   }
			   if (isset($_POST['eme_send_groups']) && !empty($_POST['eme_send_groups']) && eme_array_integers($_POST['eme_send_groups'])) {
			   	$group_ids = join(',',$_POST['eme_send_groups']);
			   	$person_ids = array_unique($person_ids + eme_get_massmail_groups($group_ids));
			   }
			   if (isset($_POST['eme_send_memberships']) && !empty($_POST['eme_send_memberships']) && eme_array_integers($_POST['eme_send_memberships'])) {
			   	$membership_ids = join(',',$_POST['eme_send_memberships']);
			   	$member_ids = array_unique($member_ids + eme_get_massmail_members($membership_ids));
			   }
		   }
		   if ($queue) {
			   $mailing_name = $_POST['mailing_name'];
			   $mailing_datetime = $_POST['actualstartdate'];
			   $mail_count = count($person_ids)+count($member_ids);
			   if (empty($mailing_name)) {
				   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please enter a mailing name.','events-made-easy')."</p></div>";
				   $ajaxResult['result'] = "ERROR";
				   echo json_encode($ajaxResult);
				   return;
			   } elseif ($mail_count==0) {
				   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('No recipients provided, mailing will not be done.','events-made-easy')."</p></div>";
				   $ajaxResult['result'] = "ERROR";
				   echo json_encode($ajaxResult);
				   return;
			   } else {
				   $mailing_id = eme_insert_mailing($mailing_name,$mailing_datetime, $mail_count, $mail_subject, $mail_message, $contact_email, $contact_name, $mail_text_html);
			   }
		   }
	   }
	   $not_sent=array();
	   foreach ( $member_ids as $member_id ) {
		   $member = eme_get_member($member_id);
		   $person = eme_get_person($member['person_id']);
		   $person_name=$person['lastname'].' '.$person['firstname'];
		   if (is_email($person['email'])) {
			   // we could postpone the placeholder replacement until the moment of actual sending (for large number of mails)
			   // but that complicates the queue-code and is in fact ugly (I did it, but removed it on 2017-12-04)
			   // Once I hit execution timeouts I'll rethink it again
			   $membership=eme_get_membership($member['membership_id']);
			   $tmp_subject = eme_replace_member_placeholders($mail_subject, $membership, $member, "text",0,$person['lang']);
			   $tmp_message = eme_replace_member_placeholders($mail_message, $membership, $member, $mail_text_html,0,$person['lang']);
			   if ($mailing_id) {
				   // $mail_res = eme_queue_mailing_mail($mailing_id, $person['email'], $person_name, 0, $member_id);
				   $mail_res = eme_queue_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name, $mailing_id, 0, $member_id);
			   } else {
				   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name);
				   if (!$mail_res) $not_sent[]=$person_name;
			   }
			   if (!$mail_res) $mail_problems=true;
		   } else {
			   $mail_problems=true;
			   $not_sent[]=$person_name;
		   }
	   }
	   foreach ( $person_ids as $person_id ) {
		   $person = eme_get_person($person_id);
		   $person_name=$person['lastname'].' '.$person['firstname'];
		   if (is_email($person['email'])) {
			   $tmp_subject = eme_replace_people_placeholders($mail_subject, $person, "text",0,$person['lang']);
			   $tmp_message = eme_replace_people_placeholders($mail_message, $person, $mail_text_html,0,$person['lang']);
			   if ($mailing_id) {
				   //$mail_res = eme_queue_mailing_mail($mailing_id, $person['email'], $person_name, $person_id);
				   $mail_res = eme_queue_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name, $mailing_id, $person_id);
			   } else {
				   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name);
				   if (!$mail_res) $not_sent[]=$person_name;
			   }
			   if (!$mail_res) $mail_problems=true;
		   } else {
			   $mail_problems=true;
			   $not_sent[]=$person_name;
		   }
	   }
	   if (!$mail_problems) {
		   if ($queue) {
			   if (!wp_next_scheduled('eme_cron_send_queued'))
				   $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The mailing has been put on the queue, but you have not yet configured the queueing. Go in the CRON submenu and configure it now.','events-made-easy')."</p></div>";
			   else
				   $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The mailing has been planned.','events-made-easy')."</p></div>";
		   } else {
			   $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The mails have been sent.','events-made-easy')."</p></div>";
		   }
		   $ajaxResult['result'] = "OK";
	   } else {
		   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There were some problems while sending mail.','events-made-easy')."</p></div>";
		   if (!empty($not_sent)) {
			   $ajaxResult['htmlmessage'] .= "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Mail to the following persons has not been sent:','events-made-easy')." ".join(', ',$not_sent)."</p></div>";
		   }
		   $ajaxResult['result'] = "ERROR";
	   }
	   echo json_encode($ajaxResult);
	   return;
   }

   if ($action == 'eventmail') {
	   if (isset($_POST ['event_mail_subject']) && !empty($_POST ['event_mail_subject']))
		   $mail_subject = stripslashes_deep($_POST ['event_mail_subject']);
	   elseif (isset($_POST ['event_subject_template']) && intval($_POST ['event_subject_template'])>0)
		   $mail_subject = eme_get_template_format(intval($_POST ['event_subject_template']));
	   else
		   $mail_subject = "";

	   if (isset($_POST ['event_mail_message']) && !empty($_POST ['event_mail_message']))
		   $mail_message = stripslashes_deep($_POST ['event_mail_message']);
	   elseif (isset($_POST ['event_message_template']) && intval($_POST ['event_message_template'])>0)
		   $mail_message = eme_get_template_format(intval($_POST ['event_message_template']));
	   else
		   $mail_message = "";

	   if (!eme_array_integers($event_ids)) {
		   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please select at least 1 event.','events-made-easy')."</p></div>";
		   $ajaxResult['result'] = "ERROR";
		   echo json_encode($ajaxResult);
		   return;
	   }
	   if (empty($mail_subject) || empty($mail_message)) {
		   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Please enter both subject and message for the mail to be sent.','events-made-easy')."</p></div>";
		   $ajaxResult['result'] = "ERROR";
		   echo json_encode($ajaxResult);
		   return;
	   }

	   $pending_approved = isset($_POST ['pending_approved']) ? intval($_POST ['pending_approved']) : 0;
	   $only_unpaid = isset($_POST ['only_unpaid']) ? intval($_POST ['only_unpaid']) : 0;
	   $eme_mail_type = isset($_POST ['eme_mail_type']) ? $_POST ['eme_mail_type'] : 'attendees';
	   $current_userid=get_current_user_id();
	   $mail_access_problems=false;
	   $mail_problems=false;
	   foreach ($event_ids as $event_id) {
		   $event = eme_get_event($event_id);
		   if (current_user_can( get_option('eme_cap_send_other_mails')) ||
			   (current_user_can( get_option('eme_cap_send_mails')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {  
			   $event_name = $event['event_name'];
			   $contact = eme_get_event_contact ($event);
			   $contact_email = $contact->user_email;
			   $contact_name = $contact->display_name;
			   $mail_text_html=get_option('eme_rsvp_send_html')?"html":"text";

			   if ($eme_mail_type == 'attendees') {
				   $attendees = eme_get_attendees_for($event_id,$pending_approved,$only_unpaid);
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($attendees) > $eme_cron_queue_count)?1:0;
				   foreach ( $attendees as $attendee ) {
					   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text",0,$attendee['lang']);
					   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html,0,$attendee['lang']);
					   $tmp_subject = eme_replace_attendees_placeholders($tmp_subject, $event, $attendee, "text",0,$attendee['lang']);
					   $tmp_message = eme_replace_attendees_placeholders($tmp_message, $event, $attendee, $mail_text_html,0,$attendee['lang']);
					   $tmp_subject = eme_translate($tmp_subject,$attendee['lang']);
					   $tmp_message = eme_translate($tmp_message,$attendee['lang']);
					   $person_name=$attendee['lastname'].' '.$attendee['firstname'];
					   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $attendee['email'], $person_name, $contact_email, $contact_name, $queue);
					   if (!$mail_res) $mail_problems=true;
				   }
			   } elseif ($eme_mail_type == 'bookings') {
				   $bookings = eme_get_bookings_for($event_id,$pending_approved,$only_unpaid);
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($bookings) > $eme_cron_queue_count)?1:0;
				   foreach ( $bookings as $booking ) {
					   // we use the language done in the booking for the mails, not the attendee lang in this case
					   $attendee = eme_get_person($booking['person_id']);
					   if ($attendee && is_array($attendee)) {
						   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text",0,$booking['lang']);
						   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html,0,$booking['lang']);
						   $tmp_subject = eme_replace_booking_placeholders($tmp_subject, $event, $booking, "text",0,$booking['lang']);
						   $tmp_message = eme_replace_booking_placeholders($tmp_message, $event, $booking, $mail_text_html,0,$booking['lang']);
						   $tmp_subject = eme_translate($tmp_subject,$booking['lang']);
						   $tmp_message = eme_translate($tmp_message,$booking['lang']);
						   $person_name=$attendee['lastname'].' '.$attendee['firstname'];
						   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $attendee['email'], $person_name, $contact_email, $contact_name, $queue);
						   if (!$mail_res) $mail_problems=true;
					   }
				   }
			   } elseif ($eme_mail_type == 'all_people') {
				   $persons = eme_get_massmail_persons();
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($attendees) > $eme_cron_queue_count)?1:0;
				   foreach ( $persons as $person ) {
					   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text",0,$person['lang']);
					   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html,0,$person['lang']);
					   $tmp_subject = eme_replace_attendees_placeholders($tmp_subject, $event, $person, "text",0,$person['lang']);
					   $tmp_message = eme_replace_attendees_placeholders($tmp_message, $event, $person, $mail_text_html,0,$person['lang']);
					   $tmp_subject = eme_translate($tmp_subject,$person['lang']);
					   $tmp_message = eme_translate($tmp_message,$person['lang']);
					   $person_name=$person['lastname'].' '.$person['firstname'];
					   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name, $queue);
					   if (!$mail_res) $mail_problems=true;
				   }
			   } elseif ($eme_mail_type == 'all_people_not_registered') {
				   $persons = eme_get_massmail_persons($event_id);
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($attendees) > $eme_cron_queue_count)?1:0;
				   foreach ( $persons as $person ) {
					   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text",0,$person['lang']);
					   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html,0,$person['lang']);
					   $tmp_subject = eme_replace_attendees_placeholders($tmp_subject, $event, $person, "text",0,$person['lang']);
					   $tmp_message = eme_replace_attendees_placeholders($tmp_message, $event, $person, $mail_text_html,0,$person['lang']);
					   $tmp_subject = eme_translate($tmp_subject,$person['lang']);
					   $tmp_message = eme_translate($tmp_message,$person['lang']);
					   $person_name=$person['lastname'].' '.$person['firstname'];
					   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name, $queue);
					   if (!$mail_res) $mail_problems=true;
				   }
			   } elseif ($eme_mail_type == 'all_wp') {
				   $wp_users = get_users();
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($wp_users) > $eme_cron_queue_count)?1:0;
				   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text");
				   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html);
				   foreach ( $wp_users as $wp_user ) {
					   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $wp_user->user_email, $wp_user->display_name, $contact_email, $contact_name, $queue);
					   if (!$mail_res) $mail_problems=true;
				   }
			   } elseif ($eme_mail_type == 'all_wp_not_registered') {
				   $wp_users = get_users();
				   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
				   $queue = ($eme_cron_queue_count && count($wp_users) > $eme_cron_queue_count)?1:0;
				   $attendee_wp_ids = eme_get_wp_ids_for($event_id);
				   $tmp_subject = eme_replace_placeholders($mail_subject, $event, "text");
				   $tmp_message = eme_replace_placeholders($mail_message, $event, $mail_text_html);
				   foreach ( $wp_users as $wp_user ) {
					   if (!in_array($wp_user->ID,$attendee_wp_ids)) {
						   $mail_res=eme_send_mail($tmp_subject,$tmp_message, $wp_user->user_email, $wp_user->display_name, $contact_email, $contact_name, $queue);
						   if (!$mail_res) $mail_problems=true;
					   }
				   }
			   }
		   } else {
			   $mail_access_problems=true;
			   $mail_problems=true;
		   }
	   }
	   if (!$mail_problems) {
		   $ajaxResult['htmlmessage'] = "<div id='message' class='updated' style='background-color: rgb(255, 251, 204);'><p>".__('The mail has been sent.','events-made-easy')."</p></div>";
		   $ajaxResult['result'] = "OK";
	   } else {
		   if ($mail_access_problems)
			   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('Only mails for events you have the right to send mails for have been sent.','events-made-easy')."</p></div>";
		   else
			   $ajaxResult['htmlmessage'] = "<div id='message' class='error' style='background-color: rgb(255, 251, 204);'><p>".__('There were some problems while sending mail.','events-made-easy')."</p></div>";
		   $ajaxResult['result'] = "ERROR";
	   }
	   echo json_encode($ajaxResult);
	   return;
   }
}

function eme_send_mails_page() {
   global $eme_timezone;
   if (!get_option('eme_queue_mails') || !get_option('eme_cron_queue_count') || !wp_next_scheduled('eme_cron_send_queued')) {
	   $eme_queue_mails = 0;
   } else {
	   $eme_queue_mails = 1;
   }
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $this_datetime = $eme_date_obj->getDateTime();
   $localized_datetime = eme_localized_date($this_datetime).' '.eme_localized_time($this_datetime);

   $mygroups=array();
   $mymembergroups=array();
   $person_ids=array();
   $member_ids=array();
   // if we get a request for mailings, set the active tab to the 'tab-genericmails' tab (which is index 1)
   if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action']=='new_mailing') {
      $data_forced_tab="data-showtab=1";
      if (isset($_POST['booking_ids'])) {
	      // when editing, select2 needs a populated list of selected items
	      $booking_ids=$_POST['booking_ids'];
	      $person_ids=eme_get_booking_personids($booking_ids);
	      $persons=eme_get_persons($person_ids);
	      foreach ($persons as $person) {
		      $mygroups[$person['person_id']]=$person['lastname'].' '.$person['firstname'];
	      }
      }
      if (isset($_POST['person_ids'])) {
	      // when editing, select2 needs a populated list of selected items
	      $person_ids=explode(',',$_POST['person_ids']);
	      $persons=eme_get_persons($person_ids);
	      foreach ($persons as $person) {
		      $mygroups[$person['person_id']]=$person['lastname'].' '.$person['firstname'];
	      }
      }
      if (isset($_POST['member_ids'])) {
	      // when editing, select2 needs a populated list of selected items
	      $member_ids=explode(',',$_POST['member_ids']);
	      $members=eme_get_members($member_ids);
	      foreach ($members as $member) {
		      $mymembergroups[$member['member_id']]=$member['lastname'].' '.$member['firstname'];
	      }
      }
      $send_to_all_people_checked="";
   } else {
      $send_to_all_people_checked="checked='checked'";
      $data_forced_tab="";
   }
   $generic_mail_subject='';
   $generic_mail_message='';

   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action']=='reuse_mail' && isset($_GET['id'])) {
	   check_admin_referer('eme_mailings','eme_admin_nonce');
	   $id=intval($_GET['id']);
	   $mail=eme_get_mail($id);
	   if ($mail) {
		   $generic_mail_subject=$mail['subject'];
		   $generic_mail_message=$mail['body'];
		   if ($mail['person_id']>0) {
			   $person_ids[]=$mail['person_id'];
			   $person=eme_get_person($mail['person_id']);
			   $mygroups[$person['person_id']]=$person['lastname'].' '.$person['firstname'];
			   $send_to_all_people_checked="";
		   } elseif ($mail['member_id']>0) {
			   $member_ids[]=$mail['member_id'];
			   $member=eme_get_member($mail['member_id']);
			   $person=eme_get_person($member['person_id']);
			   $mymembergroups[$member['member_id']]=$person['lastname'].' '.$person['firstname'];
			   $send_to_all_people_checked="";
		   }
		   $data_forced_tab="data-showtab=1";
	   }
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action']=='reuse_mailing' && isset($_GET['id'])) {
	   check_admin_referer('eme_mailings','eme_admin_nonce');
	   $id=intval($_GET['id']);
	   $mailing=eme_get_mailing($id);
	   if ($mailing) {
		   $generic_mail_subject=$mailing['subject'];
		   $generic_mail_message=$mailing['body'];
		   $data_forced_tab="data-showtab=1";
	   }
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action']=='delete_mailing' && isset($_GET['id'])) {
	   $id=intval($_GET['id']);
	   check_admin_referer('eme_mailings','eme_admin_nonce');
	   eme_delete_mailing($id);
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action']=='cancel_mailing' && isset($_GET['id'])) {
	   $id=intval($_GET['id']);
	   check_admin_referer('eme_mailings','eme_admin_nonce');
	   eme_cancel_mailing($id);
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action']=='report_mailing' && isset($_GET['id'])) {
	   // the id param will be captured by js to fill out the report table via jtable
	   check_admin_referer('eme_mailings','eme_admin_nonce');
   ?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>
         <h1><?php _e('Mailing report', 'events-made-easy') ?></h1>
   <form action="#" method="post">
   <input type="text" name="search_name" id="search_name" class="clearable" placeholder="<?php _e('Person name','events-made-easy'); ?>" size=10>
   <button id="ReportLoadRecordsButton" class="button-secondary action"><?php _e('Filter','events-made-easy'); ?></button>
   </form>
	 <div id="MailingReportTableContainer"></div>
       </div>
      </div>
   <?php
	   return;
   }

   // now show the form
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<div id="mail-tabs" style="display: none;" <?php echo $data_forced_tab; ?>>
  <ul>
    <li><a href="#tab-eventmails"><?php _e('Event related mails','events-made-easy');?></a></li>
    <li><a href="#tab-genericmails"><?php _e('Generic mails','events-made-easy');?></a></li>
    <li><a href="<?php echo wp_nonce_url(admin_url("admin-ajax.php?action=eme_get_mailings_div"),'eme_mailings','eme_admin_nonce') ;?>"><?php _e('Mailings','events-made-easy');?></a></li>
    <li><a href="#tab-testmail"><?php _e('Test mail','events-made-easy');?></a></li>
  </ul>
  <div id="tab-eventmails">
   <h1><?php _e ('Send Mails to attendees or bookings for a event','events-made-easy'); ?></h1>
<?php
   if (!$eme_queue_mails) {
?>
   <div id='message' class='updated'><p>
<?php
      _e('Warning: using this functionality to send mails to attendees can result in a php timeout, so not everybody will receive the mail then. This depends on the number of attendees, the load on the server, ... . If this happens, activate and configure mail queueing.','events-made-easy');
?>
   </p></div>
<?php
   }
   $all_events=eme_get_events(0,"future");
   $current_userid=get_current_user_id();
   $templates_array=eme_get_templates_array_by_id('rsvpmail');
?>
   <form id='send_mail' name='send_mail' action="#" method="post" onsubmit="return false;">
   <div id='send_event_mail_div'>
   <p>
      <table>
      <tr>
      <td><?php _e('Select the event(s)','events-made-easy'); ?></td>
      <td>
	   <select name="event_ids[]" id="event_ids[]" multiple="multiple" size="5" class='eme_select2_events_class'>
           <?php
   	   foreach ( $all_events as $tmp_event ) {
              $option_text=$tmp_event['event_name']." (".eme_localized_date($tmp_event['event_start_date']." ".$tmp_event['event_start_time']." ".$eme_timezone).")";
              if ($tmp_event['event_rsvp'] && current_user_can( get_option('eme_cap_send_other_mails')) ||
                 (current_user_can( get_option('eme_cap_send_mails')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {  
                  echo "<option value='".$tmp_event['event_id']."' >".$option_text."</option>  ";
              }
           }
           ?>
           </select>
      </td>
      </tr>
      <tr>
      <td><?php _e('Select the type of mail','events-made-easy'); ?></td>
      <td>
           <select name="eme_mail_type" required='required'>
           <option value=''>&nbsp;</option>
           <option value='attendees'><?php _e('Attendee mails','events-made-easy'); ?></option>
           <option value='bookings'><?php _e('Booking mails','events-made-easy'); ?></option>
           <option value='all_people'><?php _e('Mail to all people registered in EME','events-made-easy'); ?></option>
           <option value='all_people_not_registered'><?php _e('All EME people except those registered for the event','events-made-easy'); ?></option>
           <option value='all_wp'><?php _e('Mail to all WP users','events-made-easy'); ?></option>
           <option value='all_wp_not_registered'><?php _e('All WP users except those registered for the event','events-made-easy'); ?></option>
           </select>
      </td>
      </tr>
      <tr id="eme_pending_approved_row">
      <td><?php _e('Select your target audience','events-made-easy'); ?></td>
      <td>
           <select name="pending_approved">
           <option value=0><?php _e('All registered persons','events-made-easy'); ?></option>
           <option value=2><?php _e('Exclude pending registrations','events-made-easy'); ?></option>
           <option value=1><?php _e('Only pending registrations','events-made-easy'); ?></option>
           </select>
      </td>
      </tr>
      <tr id="eme_only_unpaid_row">
      <td><?php _e('Only send mails to attendees who did not pay yet','events-made-easy'); ?>&nbsp;</td>
      <td>
           <input type="checkbox" name="only_unpaid" value="1" />
      </td>
      </tr>
      </table>
	   <div id="titlediv" class="form-field form-required"><p>
      <b><?php _e('Subject','events-made-easy'); ?></b><br />
      <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select(0,'event_subject_template',$templates_array); ?><br />
      <?php _e('Or enter your own: ','events-made-easy');?>
      <input type="text" name="event_mail_subject" id="event_mail_subject" value="" /></p>
	   </div>
	   <div class="form-field form-required"><p>
	   <b><?php _e('Message','events-made-easy'); ?></b><br />
      <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select(0,'event_message_template',$templates_array); ?><br />
      <?php _e('Or enter your own: ','events-made-easy');
          if (get_option('eme_rsvp_send_html')) {
		  // for mails, let enable the full html editor
		  // and since we do ajax submit, disable the quicktags section and in the eme_admin_sendmail.js we will save the tinymce content before submit
             $eme_editor_settings = eme_get_editor_settings(true,false,10);
             wp_editor('','event_mail_message',$eme_editor_settings);
          } else {
             echo '<textarea name="event_mail_message" id="event_mail_message" rows=10></textarea>';
          }
      ?>
           </p>
	   </div>
	   <div>
	   <?php _e('You can use any placeholders mentioned here:','events-made-easy');
	   print "<br /><a href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('Event placeholders','events-made-easy')."</a>";
	   print "<br /><a href='http://www.e-dynamics.be/wordpress/?cat=48'>".__('Attendees placeholders','events-made-easy')."</a> (".__('for ','events-made-easy').__('Attendee mails','events-made-easy').")";
	   print "<br /><a href='http://www.e-dynamics.be/wordpress/?cat=45'>".__('Booking placeholders','events-made-easy')."</a> (".__('for ','events-made-easy').__('Booking mails','events-made-easy').")";
	   ?>
	   </div>
           <br />
	   <button id='eventmailButton' class="button-primary action"> <?php _e ( 'Send Mail', 'events-made-easy'); ?></button>
   </div>
   </form>
   <div id="eventmail-message" style="display:none;" ></div>
  </div>

  <div id="tab-genericmails">
   <h1><?php _e ('Send a mail','events-made-easy'); ?></h1>
   <?php _e ( "Use the below form to send a generic mail. Don't forget to use the #_UNSUB_URL for unsubscribe possibility.", 'events-made-easy');
	   if (!$eme_queue_mails) {
		   print '<br />';
		   _e('Warning: mail queueing is either not activated or not configured, you might experience timeout issues!','events-made-easy');
	   }
   ?>
   <form id='send_generic_mail' name='send_generic_mail' action="#" method="post" onsubmit="return false;">
	   <div class="form-field form-required">
		<p>
		<b><?php _e('Target audience:','events-made-easy'); ?></b><br />
		<label for='eme_send_all_people'><?php _e('Send to all EME people','events-made-easy'); ?></label>
		<input id="eme_send_all_people" name='eme_send_all_people' value='1' type='checkbox' <?php echo $send_to_all_people_checked; ?>><br />
		<?php
	           _e('Deselect this to select specific groups and/or memberships for your mailing','events-made-easy');
		   $groups=eme_get_groups();
		   $memberships=eme_get_memberships();
		?>
		<div id='div_eme_send_groups'><table class='widefat'>
		<tr><td width='20%'><label for='eme_send_people'><?php _e('Send to a number of people','events-made-easy'); ?></label></td>
                <td><?php echo eme_ui_multiselect($person_ids,'eme_send_persons',$mygroups,5,0,'eme_select2_people_class'); ?></td></tr>
		<tr><td width='20%'><label for='eme_send_groups'><?php _e('Send to a number of groups','events-made-easy'); ?></label></td>
		<td><?php echo eme_ui_multiselect_key_value('','eme_send_groups',$groups,'group_id','name','', 5,0,'eme_select2_groups_class'); ?></td></tr>
		<tr><td width='20%'><label for='eme_send_people'><?php _e('Send to a number of members','events-made-easy'); ?></label></td>
                <td><?php echo eme_ui_multiselect($member_ids,'eme_send_members',$mymembergroups,5,0,'eme_select2_members_class'); ?></td></tr>
		<tr><td width='20%'><label for='eme_send_memberships'><?php _e('Send to active members belonging to','events-made-easy'); ?></label></td>
		<td><?php echo eme_ui_multiselect_key_value('','eme_send_memberships',$memberships,'membership_id','name','', 5,0,'eme_select2_memberships_class'); ?></td></tr>
                </table>
		</div>
		</p>
	   </div>
	   <div id="titlediv" class="form-field form-required">
		<p>
		<b><?php _e('Subject','events-made-easy'); ?></b><br />
		<input type="text" name="generic_mail_subject" id="generic_mail_subject" value="<?php echo $generic_mail_subject; ?>" required='required' size='40' />
		</p>
	   </div>
	   <div class="form-field form-required">
		<p>
		<b><?php _e('Message','events-made-easy'); ?></b><br />
		<?php $templates_array=eme_get_templates_array_by_id('mail'); ?>
		<?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select(0,'generic_message_template',$templates_array); ?><br />
		<?php _e('Or enter your own: ','events-made-easy');
		if (get_option('eme_rsvp_send_html')) {
		  	// for mails, let enable the full html editor
		  	// and since we do ajax submit, disable the quicktags section and in the eme_admin_sendmail.js we will save the tinymce content before submit
			$eme_editor_settings = eme_get_editor_settings(true,false);
			wp_editor($generic_mail_message,'generic_mail_message',$eme_editor_settings);
		} else {
			echo "<textarea name='generic_mail_message' id='generic_mail_message' rows='10' required='required'>$generic_mail_message</textarea>";
		}
		?>
		</p>
	   </div>
	   <div>
		<?php _e('You can use any placeholders mentioned here:','events-made-easy');
		print "<br /><a href='http://www.e-dynamics.be/wordpress/category/documentation/7-placeholders/7-12-people/'>".__('People placeholders','events-made-easy')."</a> ";
		?>
	   </div>
	   <?php
	   if ($eme_queue_mails) { ?>
	   <div id='div_mailing_definition'>
		<p>
		<b><?php _e('Set mailing name and start date and time','events-made-easy'); ?></b><br />
                <?php _e('Mailing name: ','events-made-easy'); ?> <input type='text' name='mailing_name' id='mailing_name' value='' required='required'><br />
                <?php _e('Start date and time: ','events-made-easy'); ?>
		<input type='hidden' name='actualstartdate' id='actualstartdate' value='<?php echo $this_datetime; ?>'>
		<input type='text' name='startdate' id='startdate' value='<?php echo $localized_datetime; ?>' style="background: #FCFFAA;" readonly="readonly">
		</p>
	   </div>
	   <div>
		<p>
		<b><?php _e('Or check this option to send the mail immediately:','events-made-easy'); ?></b>
                <input id="send_now" name='send_now' value='1' type='checkbox' />
		</p>
	   </div>
	   <?php } ?>
      <br />
      <label for="mail_to"><?php _e('Enter a test recipient','events-made-easy'); ?></label>
      <input type="hidden" name="send_previewmailto_id" id="send_previewmailto_id" value="" />
      <input type='text' id='chooseperson' name='chooseperson' class="clearable" placeholder="<?php _e('Start typing a name','events-made-easy'); ?>">
      <button id='previewmailButton' class="button-primary action"> <?php _e ( 'Send Preview Mail', 'events-made-easy'); ?></button>
      <div id="previewmail-message" style="display:none;" ></div>
      <br />
      <button id='genericmailButton' class="button-primary action"> <?php _e ( 'Send Mail', 'events-made-easy'); ?></button>
   </form>
   <div id="genericmail-message" style="display:none;" ></div>
  </div>

  <div id="tab-testmail">
   <h1><?php _e ('Test mail settings','events-made-easy'); ?></h1>
    <div id="testmail-message" style="display:none;" ></div>
   <?php _e ( 'Use the below form to send a test mail', 'events-made-easy'); ?>
   <form id='send_testmail' name='send_testmail' action="#" method="post" onsubmit="return false;">
   <label for="testmail_to"><?php _e('Enter the recipient','events-made-easy'); ?></label>
   <input type="text" name="testmail_to" id="testmail_to" value="" />
   <button id='testmailButton' class="button-primary action"> <?php _e ( 'Send Mail', 'events-made-easy'); ?></button>
   </form>
  </div>
</div> <!-- mail-tabs -->

</div> <!-- wrap -->
   <?php
}

add_action( 'wp_ajax_eme_get_mailings_div', 'eme_ajax_mailings_div' );
function eme_ajax_mailings_div() {
   check_ajax_referer('eme_mailings','eme_admin_nonce');
   ?>
   <h1><?php _e ('Mailings overview','events-made-easy'); ?></h1>
   <div id="mailings-message" class="display:none;" ></div>
   <?php
   _e ( 'Here you can find an overview of all planned, ongoing or completed mailings', 'events-made-easy');
   $mailings = eme_get_mailings();
   $stats = eme_get_mailing_stats();
   $areyousure = __('Are you sure you want to do this?','events-made-easy');
   print "<table class='eme_mailings_table'>";
   print "<thead><tr>";
   print "<th>".__('Name','events-made-easy')."</th>";
   print "<th>".__('Planned execution time','events-made-easy')."</th>";
   print "<th>".__('Status','events-made-easy')."</th>";
   print "<th>".__('Extra info','events-made-easy')."</th>";
   print "<th>".__('Report','events-made-easy')."</th>";
   print "<th>".__('Action','events-made-easy')."</th>";
   print "</tr></thead><tbody>";
   foreach ($mailings as $mailing) {
	   $id=$mailing['id'];
	   if (!isset($stats[$id]['planned'])) $stats[$id]['planned']=0;
	   if (!isset($stats[$id]['sent'])) $stats[$id]['sent']=0;
	   if (!isset($stats[$id]['failed'])) $stats[$id]['failed']=0;
	   if (!isset($stats[$id]['cancelled'])) $stats[$id]['cancelled']=0;
	   if ($mailing['cancelled']==1) {
		   $status=__('Cancelled','events-made-easy');
		   $extra=sprintf(__('%d mails delivered, %d mails failed, %d mails cancelled','events-made-easy'),$stats[$id]['sent'],$stats[$id]['failed'],$stats[$id]['cancelled']);
		   $action = "<a onclick='return areyousure(\"$areyousure\");' href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=delete_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Delete','events-made-easy')."</a>";
	   } elseif ($stats[$id]['planned']==$mailing['mail_count']) {
		   $status=__('Planned','events-made-easy');
		   $extra=sprintf(__('%d mails left','events-made-easy'),$mailing['mail_count']);
		   $action = "<a onclick='return areyousure(\"$areyousure\");' href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=cancel_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Cancel','events-made-easy')."</a>";
	   } elseif ($stats[$id]['planned']>0) {
		   $status=__('Ongoing','events-made-easy');
		   $extra=sprintf(__('%d mails delivered, %d mails failed, %d mails left','events-made-easy'),$stats[$id]['sent'],$stats[$id]['failed'],$stats[$id]['planned']);
		   $action = "<a onclick='return areyousure(\"$areyousure\");' href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=cancel_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Cancel','events-made-easy')."</a>";
	   } else {
		   $status=__('Completed','events-made-easy');
		   $extra=sprintf(__('%d mails delivered, %d mails failed','events-made-easy'),$stats[$id]['sent'],$stats[$id]['failed']);
		   $action = "<a onclick='return areyousure(\"$areyousure\");' href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=delete_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Delete','events-made-easy')."</a>";
	   }
	   if (!empty($mailing['subject']) && !empty($mailing['body']))
		   $action .= " <a href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=reuse_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Reuse','events-made-easy')."</a>";
	   print "<tr>";
	   print "<td>".eme_esc_html($mailing['name'])."</td>";
	   print "<td>".eme_localized_date($mailing['planned_on']).' '.eme_localized_time($mailing['planned_on'])."</td>";
	   print "<td>".eme_esc_html($status)."</td>";
	   print "<td>".eme_esc_html($extra)."</td>";
	   print "<td><a href='".wp_nonce_url(admin_url("admin.php?page=eme-send-mails&amp;eme_admin_action=report_mailing&amp;id=".$id),'eme_mailings','eme_admin_nonce')."'>".__('Report','events-made-easy')."</a></td>";
	   print "<td>".$action."</td>";
	   print "</tr>";
   }
   print "</tbody></table>";
   wp_die();
}

?>
