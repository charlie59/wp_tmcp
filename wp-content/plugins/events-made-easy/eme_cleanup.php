<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_cleanup_unpaid($eme_number) {
   global $wpdb, $eme_timezone;

   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;

   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $today = $eme_date_obj->getDate();
   // the min is 5 minutes
   if ($eme_number<5) $eme_number=5;
   #$sql = $wpdb->prepare("SELECT booking_id FROM $bookings_table WHERE booking_paid = 0 AND booking_approved=0 AND UNIX_TIMESTAMP(creation_date)<UNIX_TIMESTAMP()-60*%d",$eme_number);
   $sql = $wpdb->prepare("SELECT bookings.booking_id FROM $bookings_table as bookings LEFT JOIN $events_table as events ON bookings.event_id=events.event_id WHERE bookings.booking_paid = 0 AND bookings.booking_approved=0 AND events.event_start_date > %s AND UNIX_TIMESTAMP(bookings.creation_date)<UNIX_TIMESTAMP()-60*%d",$today,$eme_number);
   $booking_ids=$wpdb->get_col($sql);
   foreach ($booking_ids as $booking_id) {
       $booking=eme_get_booking($booking_id);
       eme_delete_booking($booking_id);
       eme_email_rsvp_booking($booking,"cancelRegistration");
       // delete the booking answers after the mail is sent, so the answers can still be used in the mail
       eme_delete_booking_answers($booking_id);
   }
}

function eme_cleanup_events($eme_number,$eme_period) {
   global $wpdb, $eme_timezone;

   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;

   if ($eme_number<1) $eme_number=1;
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   switch ($eme_period) {
	   case 'day':
		   $eme_date_obj->minusDays($eme_number);
		   break;
	   case 'week':
		   $eme_date_obj->minusWeeks($eme_number);
		   break;
	   default:
		   $eme_date_obj->minusMonths($eme_number);
		   break;
   }
   $end_date=$eme_date_obj->getDate();
   $wpdb->query("DELETE FROM $bookings_table where event_id in (SELECT event_id from $events_table where event_end_date<'$end_date')");
   $wpdb->query("DELETE FROM $events_table where event_end_date<'$end_date'");
   $wpdb->query("DELETE FROM $recurrence_table where recurence_freq <> 'specific' AND recurrence_end_date<'$end_date'");
}

function eme_cleanup_all_event_related_data($other_data) {
   global $wpdb;

   $tables=array(EVENTS_TBNAME,BOOKINGS_TBNAME,LOCATIONS_TBNAME,RECURRENCE_TBNAME,ANSWERS_TBNAME,PAYMENTS_TBNAME,PEOPLE_TBNAME);
   if ($other_data) {
      $tables2=array(CATEGORIES_TBNAME,HOLIDAYS_TBNAME,TEMPLATES_TBNAME,FORMFIELDS_TBNAME);
      $tables = array_merge($tables,$tables2);
   }
   foreach ($tables as $table) {
      $wpdb->query("DELETE FROM ".$wpdb->prefix.$table);
   }
}

function eme_cleanup_page() {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;

   $message="";
   if (is_admin() && current_user_can( get_option('eme_cap_cleanup'))) {
      // do the actions if required
      if (isset($_POST['eme_admin_action'])) {
         check_admin_referer('eme_cleanup','eme_admin_nonce');
         if ($_POST['eme_admin_action'] == "eme_cleanup_events" && isset($_POST['eme_number']) && isset($_POST['eme_period'])) {
            $eme_number = intval($_POST['eme_number']);
            $eme_period = $_POST['eme_period'];
            if ( !in_array( $eme_period, array( 'day', 'week', 'month' ) ) ) 
               $eme_period = "month";

            if ($eme_number>1) {
		    eme_cleanup_events($eme_number,$eme_period);
		    $message = sprintf ( __ ( "Cleanup done: events (and corresponding booking data) older than %d %s(s) have been removed.",'events-made-easy'),$eme_number,$eme_period);
	    }
         } elseif ($_POST['eme_admin_action'] == "eme_cleanup_unpaid") {
            $eme_number = 0;
            if (isset($_POST['eme_number']))
               $eme_number = intval($_POST['eme_number']);
            if ($eme_number>=5) {
               eme_cleanup_unpaid($eme_number);
               $message = sprintf ( __ ( "Cleanup done: unpaid pending bookings older than %d minutes have been removed.",'events-made-easy'),$eme_number);
            }
         } elseif ($_POST['eme_admin_action'] == 'eme_empty_queue') {
            eme_remove_all_queued();
            $message = __("The mail queue has been cleared.",'events-made-easy');
         } elseif ($_POST['eme_admin_action'] == "eme_cleanup_all_event_related_data") {
            $other_data=0;
            if (isset($_POST['other_data'])) $other_data=1;
            eme_cleanup_all_event_related_data($other_data);
            $message = __ ( "Cleanup done: all data concerning events, locations and bookings have been removed.",'events-made-easy');
         }
      }
   }

   eme_cleanup_form($message);
}

function eme_cleanup_form($message = "") {
$areyousure = __('Are you sure you want to do this?','events-made-easy');
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
<h1><?php _e ('Cleanup actions','events-made-easy'); ?></h1>
   <form action="" method="post">
<label for="eme_number"><?php _e('Remove events older than','events-made-easy'); ?></label>
<?php echo wp_nonce_field('eme_cleanup','eme_admin_nonce',false,false); ?>
   <input type='hidden' name='page' value='eme-cleanup' />
   <input type='hidden' name='eme_admin_action' value='eme_cleanup_events' />
   <input type="number" id="eme_number" name="eme_number" size="3" maxlength="3" min="1" max="999" step="1"  />
   <select name="eme_period">
   <option value="day" selected="selected"><?php _e ( 'Day(s)','events-made-easy'); ?></option>
   <option value="week"><?php _e ( 'Week(s)','events-made-easy'); ?></option>
   <option value="month"><?php _e ( 'Month(s)','events-made-easy'); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" onclick="return areyousure('<?php echo $areyousure; ?>');" />
   </form>

<br /><br />
   <form action="" method="post">
<label for="eme_number"><?php _e('Remove unpaid pending bookings older than','events-made-easy'); ?></label>
<?php echo wp_nonce_field('eme_cleanup','eme_admin_nonce',false,false); ?>
   <input type='hidden' name='page' value='eme-cleanup' />
   <input type='hidden' name='eme_admin_action' value='eme_cleanup_unpaid' />
   <input type="number" id="eme_number" name="eme_number" size="6" maxlength="6" min="5" max="999999" step="1" />
   <?php _e ( 'minutes','events-made-easy'); ?>
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" onclick="return areyousure('<?php echo $areyousure; ?>');" />
   </form>
   
<br /><br />
   <form action="" method="post">
<?php _e('Remove all data concerning events, locations, people and bookings','events-made-easy'); ?>
<?php echo wp_nonce_field('eme_cleanup','eme_admin_nonce',false,false); ?>
   <input type='hidden' name='page' value='eme-cleanup' />
   <input type='hidden' name='eme_admin_action' value='eme_cleanup_all_event_related_data' />
   <input id="other_data" type="checkbox" value="1" name="other_data"> <?php _e ( 'Also delete defined categories, templates, holidays and form fields' , 'events-made-easy'); ?><br />
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" onclick="return areyousure('<?php echo $areyousure; ?>');" />
   </form>
<br /><br />

<?php
   $eme_queued_count=eme_get_queued_count();
   if ($eme_queued_count>1)
           echo sprintf(__('There are %d messages in the mail queue.','events-made-easy'),$eme_queued_count);
   elseif ($eme_queued_count)
           echo sprintf(__('There is 1 message in the mail queue.','events-made-easy'),$eme_queued_count);
   else
           _e('There are no messages in the mail queue.','events-made-easy');
   if ($eme_queued_count) {
?>
   <form action="" method="post">
<?php _e('Empty the mail queue','events-made-easy'); ?>
<?php echo wp_nonce_field('eme_cleanup','eme_admin_nonce',false,false); ?>
   <input type='hidden' name='eme_admin_action' value='eme_empty_queue' />
   <input type="submit" value="<?php _e ( 'Apply' , 'events-made-easy'); ?>" name="doaction" id="eme_doaction" class="button-primary action" onclick="return areyousure('<?php echo $areyousure; ?>');" />
   </form>
<br /><br />
<?php
   }
?>

</div>
<?php
}

?>
