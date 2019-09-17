<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// add an action for the cronjob to map to the reminder function,
add_action('eme_cron_reminder_unpaid', 'eme_cron_reminder_unpaid_function');
function eme_cron_reminder_unpaid_function() {
   global $wpdb, $eme_timezone;

   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;

   $mailing_is_active = get_option('eme_rsvp_mail_notify_is_active');
   if (!$mailing_is_active) return;
   $mailing_pending = get_option('eme_rsvp_mail_notify_pending');
   if (!$mailing_pending) return;

   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $today = $eme_date_obj->getDate();
   $eme_number=intval(get_option('eme_cron_reminder_unpaid_minutes'));
   // the min is 5 minutes
   if ($eme_number<5) $eme_number=5;
   $sql = $wpdb->prepare("SELECT bookings.booking_id FROM $bookings_table as bookings LEFT JOIN $events_table as events ON bookings.event_id=events.event_id WHERE bookings.booking_paid = 0 AND bookings.booking_approved=0 AND UNIX_TIMESTAMP(bookings.creation_date)<UNIX_TIMESTAMP()-60*%d AND events.event_start_date > %s AND reminder<UNIX_TIMESTAMP()",$eme_number,$today);
   $booking_ids=$wpdb->get_col($sql);
   foreach ($booking_ids as $booking_id) {
       $booking=eme_get_booking($booking_id);
       $ret= eme_email_rsvp_booking($booking,"pendingReminder");
       if ($ret) {
          $sql = $wpdb->prepare("UPDATE $bookings_table SET reminder = UNIX_TIMESTAMP()+60*%d  WHERE booking_id=%d",$eme_number,$booking_id); 
          $wpdb->query($sql);
       }
   }
}

add_action('eme_cron_send_new_events', 'eme_cron_send_new_events_funtion');
function eme_cron_send_new_events_function() {
   $days=intval(get_option('eme_cron_new_events_days'));
   $mail_subject=eme_get_template_format(get_option('eme_cron_new_events_subject'));
   $header=eme_get_template_format(get_option('eme_cron_new_events_header'));
   $entry=eme_get_template_format(get_option('eme_cron_new_events_entry'));
   $footer=eme_get_template_format(get_option('eme_cron_new_events_footer'));
   // get templates, replace people placeholders and then:
   $mail_message=eme_get_events_list(0, "+".$days."d", "ASC", $entry, $header, $footer);

   $persons = eme_get_massmail_persons();
   $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
   $queue = 1;
   $mail_text_html = get_option('eme_rsvp_send_html')?"html":"text";
   $contact_email = get_option('eme_mail_sender_address');
   $contact_name = get_option('eme_mail_sender_name');
   if (empty($contact_email)) {
	   $blank_event = eme_new_event();
	   $contact = eme_get_event_contact($blank_event);
	   $contact_email = $contact->user_email;
	   $contact_name = $contact->display_name;
   }

   foreach ( $persons as $person ) {
	   $tmp_message = eme_replace_people_placeholders($mail_message, $person, $mail_text_html,0,$attendee['lang']);
	   $tmp_message = eme_translate($tmp_message,$person['lang']);
	   $person_name=$person['lastname'].' '.$person['firstname'];
	   $mail_res=eme_send_mail($mail_subject,$tmp_message, $person['email'], $person_name, $contact_email, $contact_name, $queue);
	   if (!$mail_res) $mail_problems=true;
   }
}

// add an action for the cronjob to map to the cleanup function,
add_action('eme_cron_cleanup_unpaid', 'eme_cron_cleanup_unpaid_function');
function eme_cron_cleanup_unpaid_function() {
   $eme_number=intval(get_option('eme_cron_cleanup_unpaid_minutes'));
   eme_cleanup_unpaid($eme_number);
}

add_action('eme_cron_send_queued', 'eme_cron_send_queued');
function eme_cron_send_queued() {
	eme_send_queued();
	eme_remove_old_queued();
}

add_action('eme_cron_member_daily_actions', 'eme_cron_member_daily_actions');
function eme_cron_member_daily_actions() {
	eme_member_recalculate_status();
	eme_member_send_expiration_reminders();
}

function eme_cron_page() {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;

   $message="";
   if (is_admin() && current_user_can( get_option('eme_cap_settings'))) {
      // do the actions if required
      if (isset($_POST['eme_admin_action'])) {
         check_admin_referer('eme_cron','eme_admin_nonce');
         if ($_POST['eme_admin_action'] == "eme_cron_reminder_unpaid") {
            $eme_cron_reminder_schedule = $_POST['eme_cron_reminder_schedule'];
            $eme_cron_reminder_unpaid_minutes = intval($_POST['eme_cron_reminder_unpaid_minutes']);
            $eme_cron_reminder_unpaid_subject = intval($_POST['eme_cron_reminder_unpaid_subject']);
            $eme_cron_reminder_unpaid_body = intval($_POST['eme_cron_reminder_unpaid_body']);
	    if (wp_next_scheduled('eme_cron_reminder_unpaid'))
		    wp_clear_scheduled_hook('eme_cron_reminder_unpaid');
	    if ($eme_cron_reminder_unpaid_minutes>=5) {
		    if ($eme_cron_reminder_unpaid_minutes != get_option('eme_cron_reminder_unpaid_minutes'))
			    $diff_found=1;
		    else
			    $diff_found=0;

		    if ($eme_cron_reminder_schedule) {
			    $schedules = wp_get_schedules();
			    if (isset($schedules[$eme_cron_reminder_schedule])) {
				    $schedule = $schedules[$eme_cron_reminder_schedule];
				    wp_schedule_event(time(), $eme_cron_reminder_schedule, 'eme_cron_reminder_unpaid');
				    update_option('eme_cron_reminder_unpaid_minutes',$eme_cron_reminder_unpaid_minutes);
				    update_option('eme_cron_reminder_unpaid_subject',$eme_cron_reminder_unpaid_subject);
				    update_option('eme_cron_reminder_unpaid_body',$eme_cron_reminder_unpaid_body);
				    $message = sprintf ( __ ( "A reminder mail will be sent to unpaid pending bookings every %d minutes. This will be checked for '%s'",'events-made-easy'),$eme_cron_reminder_unpaid_minutes,$schedule['display']);
				    if ($diff_found) {
					    $sql = "UPDATE $bookings_table SET reminder = 0";
					    $wpdb->query($sql);
					    $message .= "<br />".__("Since the new reminder setting is different than the old one in minutes, all old reminder info has been reset.");
				    }
			    }
		    } else {
			    $message = __ ( "No automatic reminder of unpaid pending bookings will be sent.",'events-made-easy');
		    }
            } else {
                  $message = __ ( "No automatic reminder of unpaid pending bookings will be sent.",'events-made-easy');
            }
         } elseif ($_POST['eme_admin_action'] == "eme_cron_cleanup_unpaid") {
            $eme_cron_cleanup_schedule = $_POST['eme_cron_cleanup_schedule'];
            $eme_cron_cleanup_unpaid_minutes = intval($_POST['eme_cron_cleanup_unpaid_minutes']);
	    if (wp_next_scheduled('eme_cron_cleanup_unpaid'))
		    wp_clear_scheduled_hook('eme_cron_cleanup_unpaid');
	    if ($eme_cron_cleanup_unpaid_minutes>=5) {
		    if ($eme_cron_cleanup_schedule) {
			    $schedules = wp_get_schedules();
			    if (isset($schedules[$eme_cron_cleanup_schedule])) {
				    $schedule = $schedules[$eme_cron_cleanup_schedule];
				    wp_schedule_event(time(), $eme_cron_cleanup_schedule, 'eme_cron_cleanup_unpaid');
				    update_option('eme_cron_cleanup_unpaid_minutes',$eme_cron_cleanup_unpaid_minutes);
				    $message = sprintf ( __ ( "Cleanup of unpaid pending bookings older than %d minutes will be done %s",'events-made-easy'),$eme_cron_cleanup_unpaid_minutes,$schedule['display']);
			    }
		    } else {
			    $message = __ ( "No automatic cleanup of unpaid pending bookings will be done.",'events-made-easy');
		    }
	    } else {
		    $message = __ ( "No automatic cleanup of unpaid pending bookings will be done.",'events-made-easy');
	    }
          } elseif ($_POST['eme_admin_action'] == "eme_cron_send_queued") {
            $eme_cron_queued_schedule = $_POST['eme_cron_queued_schedule'];
            $eme_cron_queue_count = intval($_POST['eme_cron_queue_count']);
	    if (wp_next_scheduled('eme_cron_send_queued'))
		    wp_clear_scheduled_hook('eme_cron_send_queued');
	    if ($eme_cron_queue_count==0) $eme_cron_queue_count=1;
	    if ($eme_cron_queued_schedule) {
		    $schedules = wp_get_schedules();
		    if (isset($schedules[$eme_cron_queued_schedule])) {
			    $schedule = $schedules[$eme_cron_queued_schedule];
			    wp_schedule_event(time(), $eme_cron_queued_schedule, 'eme_cron_send_queued');
			    update_option('eme_cron_queue_count',$eme_cron_queue_count);
			    $message = sprintf ( __ ( "Queued mails will be send out in batches of %d %s",'events-made-easy'),$eme_cron_queue_count,$schedule['display']);
		    }
	    } else {
		    $message = __ ( "Queued mails will not be send out.",'events-made-easy');
	    }
          } elseif ($_POST['eme_admin_action'] == "eme_cron_send_new_events") {
            $eme_cron_new_events_schedule = $_POST['eme_cron_new_events_schedule'];
            $eme_cron_new_events_days = intval($_POST['eme_cron_new_events_days']);
	    if (wp_next_scheduled('eme_cron_send_new_events'))
		    wp_clear_scheduled_hook('eme_cron_send_new_events');
	    if ($eme_cron_new_events_days>0) {
		    if ($eme_cron_new_events_schedule) {
			    $schedules = wp_get_schedules();
			    if (isset($schedules[$eme_cron_new_events_schedule])) {
				    $schedule = $schedules[$eme_cron_new_events_schedule];
				    wp_schedule_event(time(), $eme_cron_new_events_schedule, 'eme_cron_send_new_events');
				    update_option('eme_cron_new_events_days',$eme_cron_new_events_days);
				    update_option('eme_cron_new_events_subject',$eme_cron_new_events_subject);
				    update_option('eme_cron_new_events_header',$eme_cron_new_events_header);
				    update_option('eme_cron_new_events_entry',$eme_cron_new_events_entry);
				    update_option('eme_cron_new_events_footer',$eme_cron_new_events_footer);
				    $message = sprintf ( __ ( "Queued mails will be send out in batches of %d %s",'events-made-easy'),$eme_cron_queue_count,$schedule['display']);
			    }
		    } else {
			    $message = __ ( "New events will not be mailed to EME registered people.",'events-made-easy');
		    }
	    } else {
		    $message = __ ( "New events will not be mailed to EME registered people.",'events-made-easy');
	    }
          }
      }
   }

   eme_cron_form($message);
}

function eme_cron_form($message = "") {
   $schedules = wp_get_schedules();
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<?php if($message != "") { ?>
<h1><?php _e ('Action info','events-made-easy'); ?></h1>
   <div id='message' class='updated' style='background-color: rgb(255, 251, 204);'>
   <p><?php echo $message; ?></p>
   </div>
<?php } ?>
<h1><?php _e ('Automatic actions','events-made-easy'); ?></h1>
   <form action="" method="post">
   <label for="eme_cron_cleanup_unpaid_minutes">
   <?php echo wp_nonce_field('eme_cron','eme_admin_nonce',false,false);
      $minutes=intval(get_option('eme_cron_cleanup_unpaid_minutes'));
      _e('Schedule the automatic removal of unpaid pending bookings older than','events-made-easy');
   ?></label>
   <input type="number" id="eme_cron_cleanup_unpaid_minutes" name="eme_cron_cleanup_unpaid_minutes" size="6" maxlength="6" min="5" max="999999" step="1" value="<?php echo $minutes; ?>" />
   <?php _e ( 'minutes','events-made-easy'); ?>
   <input type='hidden' name='eme_admin_action' value='eme_cron_cleanup_unpaid' />
   <select name="eme_cron_cleanup_schedule">
   <option value=""><?php _e ( 'Not scheduled','events-made-easy'); ?></option>
   <?php
   $scheduled= wp_get_schedule('eme_cron_cleanup_unpaid');
   foreach ($schedules as $key=>$schedule) {
      $selected=($key==$scheduled)? 'selected="selected"':'';
      print "<option $selected value='$key'>".$schedule['display']."</option>";
   }
   ?>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" />
   </form>

<br /><br />
<hr />
   <form action="" method="post">
   <label for="eme_cron_reminder_unpaid_minutes">
   <?php echo wp_nonce_field('eme_cron','eme_admin_nonce',false,false);
      $minutes=intval(get_option('eme_cron_reminder_unpaid_minutes'));
      $subject=intval(get_option('eme_cron_reminder_unpaid_subject'));
      $body=intval(get_option('eme_cron_reminder_unpaid_body'));
      _e('Send a reminder mail to unpaid pending bookings every','events-made-easy');
   ?></label>
   <input type="number" id="eme_cron_reminder_unpaid_minutes" name="eme_cron_reminder_unpaid_minutes" size="6" maxlength="6" min="5" max="999999" step="1" value="<?php echo $minutes; ?>" />
   <?php $templates_array=eme_get_templates_array_by_id('rsvpmail'); ?>
   <?php _e ( 'minutes','events-made-easy'); ?>
   <?php _e ( 'Mail subject template','events-made-easy'); echo eme_ui_select($subject,'eme_cron_reminder_unpaid_subject',$templates_array); ?>
   <?php _e ( 'Mail body template','events-made-easy'); echo eme_ui_select($body,'eme_cron_reminder_unpaid_body',$templates_array); ?>
   <input type='hidden' name='eme_admin_action' value='eme_cron_reminder_unpaid' />
   <select name="eme_cron_reminder_schedule">
   <option value=""><?php _e ( 'Not scheduled','events-made-easy'); ?></option>
   <?php
   $scheduled= wp_get_schedule('eme_cron_reminder_unpaid');
   foreach ($schedules as $key=>$schedule) {
      $selected=($key==$scheduled)? 'selected="selected"':'';
      print "<option $selected value='$key'>".$schedule['display']."</option>";
   }
   ?>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" />
   </form>

<br /><br />
<hr />
<?php
   $eme_queued_count=eme_get_queued_count();
   if ($eme_queued_count>1)
	   echo sprintf(__('There are %d messages in the mail queue.','events-made-easy'),$eme_queued_count);
   elseif ($eme_queued_count)
	   echo sprintf(__('There is 1 message in the mail queue.','events-made-easy'),$eme_queued_count);
   else
	   _e('There are no messages in the mail queue.','events-made-easy');

   if ($eme_queued_count && (!get_option('eme_queue_mails') || !get_option('eme_cron_queue_count') || !wp_next_scheduled('eme_cron_send_queued')) ) {
      echo '<br />';
      _e('WARNING: messages found in the queue but the mail queue is not configured correctly, so they will not be sent out','events-made-easy');
   } 

   if (get_option('eme_queue_mails')>0) {
?>
   <br />
   <form action="" method="post">
   <label for="eme_cron_send_queued">
   <?php echo wp_nonce_field('eme_cron','eme_admin_nonce',false,false);
      $eme_cron_queue_count=intval(get_option('eme_cron_queue_count'));
      _e('Send out queued mails in batches of ','events-made-easy');
   ?></label>
   <input type="number" id="eme_cron_queue_count" name="eme_cron_queue_count" size="6" maxlength="6" min="1" max="999999" step="1" value="<?php echo $eme_cron_queue_count; ?>" />&nbsp;
   <input type='hidden' name='eme_admin_action' value='eme_cron_send_queued' />
   <select name="eme_cron_queued_schedule">
   <option value=""><?php _e ( 'Not scheduled','events-made-easy'); ?></option>
   <?php
   $scheduled = wp_get_schedule('eme_cron_send_queued');
   foreach ($schedules as $key=>$schedule) {
      $selected=($key==$scheduled)? 'selected="selected"':'';
      print "<option $selected value='$key'>".$schedule['display']."</option>";
   }
   ?>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" />
   </form>
<br /><br />

<hr />
   <h2><?php _e('Newsletter','events-made-easy'); ?></h2>
   <form action="" method="post">
   <?php echo wp_nonce_field('eme_cron','eme_admin_nonce',false,false);
      $eme_cron_new_events=intval(get_option('eme_cron_new_events'));
      $days=intval(get_option('eme_cron_new_events_days'));
      $subject=intval(get_option('eme_cron_new_events_subject'));
      $header=intval(get_option('eme_cron_new_events_header'));
      $entry=intval(get_option('eme_cron_new_events_entry'));
      $footer=intval(get_option('eme_cron_new_events_footer'));
      _e('Send a mail to all EME registered people for upcoming events that will happen in the next','events-made-easy');
   ?>
   <input type="number" id="eme_cron_new_events_days" name="eme_cron_new_events_days" size="6" maxlength="6" min="1" max="999999" step="1" value="<?php echo $days; ?>" /><?php _e ( 'days','events-made-easy');?><br />
   <?php $templates_array=eme_get_templates_array_by_id('rsvpmail'); ?>
   <?php _e ( 'Mail subject template','events-made-easy'); echo eme_ui_select($subject,'eme_cron_new_events_subject',$templates_array); ?>
   <?php _e ( 'Mail body header','events-made-easy'); echo eme_ui_select($header,'eme_cron_new_events_subject',$templates_array); ?>
   <?php _e ( 'Mail body single event entry','events-made-easy'); echo eme_ui_select($entry,'eme_cron_new_events_entry',$templates_array); ?>
   <?php _e ( 'Mail body footer','events-made-easy'); echo eme_ui_select($footer,'eme_cron_new_events_footer',$templates_array); ?>
   <input type='hidden' name='eme_admin_action' value='eme_cron_send_new_events' />
   <br />
   <select name="eme_cron_new_events_schedule">
   <option value=""><?php _e ( 'Not scheduled','events-made-easy'); ?></option>
   <?php
   $scheduled= wp_get_schedule('eme_cron_send_new_events');
   foreach ($schedules as $key=>$schedule) {
      $selected=($key==$scheduled)? 'selected="selected"':'';
      print "<option $selected value='$key'>".$schedule['display']."</option>";
   }
   ?>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" />
   </form>

<?php
   } else {
	   echo '<br />';
	   _e('Mail queueing is not activated.' , 'events-made-easy');
	   echo '<br />';
	   _e('Because mail queueing is not activated, the newsletter functionality is not available.' , 'events-made-easy');
   }
?>


</div>
<?php
}

?>
