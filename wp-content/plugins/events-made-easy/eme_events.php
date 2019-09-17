<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_event() {
   global $eme_timezone;
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $today = $eme_date_obj->format('Y-m-d');
   $event = array (
      "event_id" => '',
      "event_name" => '',
      "event_status" => get_option('eme_event_initial_state'),
      "event_start_date" => $today,
      "event_start_time" => '',
      "event_end_date" => $today,
      "event_end_time" => '',
      "event_notes" => '',
      "event_rsvp" => get_option('eme_rsvp_reg_for_new_events')? 1:0,
      "use_paypal" => get_option('eme_paypal_business')? 1:0,
      "use_2co" => get_option('eme_2co_business')? 1:0,
      "use_webmoney" => get_option('eme_webmoney_purse')? 1:0,
      "use_fdgg" => get_option('eme_fdgg_store_name')? 1:0,
      "use_mollie" => get_option('eme_mollie_api_key')? 1:0,
      "use_sagepay" => get_option('eme_sagpay_vendor_name')? 1:0,
      "price" => get_option('eme_default_price'),
      "currency" => get_option('eme_default_currency'),
      "rsvp_number_days" => get_option('eme_rsvp_number_days'),
      "rsvp_number_hours" => get_option('eme_rsvp_number_hours'),
      "registration_requires_approval" => get_option('eme_rsvp_require_approval')? 1:0,
      "registration_wp_users_only" => get_option('eme_rsvp_registered_users_only')? 1:0,
      "event_seats" => get_option('eme_rsvp_default_number_spaces'),
      "location_id" => 0,
      "event_author" => 0,
      "event_contactperson_id" => get_option('eme_default_contact_person'),
      "event_category_ids" => '',
      "event_attributes" => array(),
      "event_properties" => array(),
      "event_page_title_format" => '',
      "event_single_event_format" => '',
      "event_contactperson_email_body" => '',
      "event_respondent_email_body" => '',
      "event_registration_pending_email_body" => '',
      "event_registration_updated_email_body" => '',
      "event_registration_cancelled_email_body" => '',
      "event_registration_paid_email_body" => '',
      "event_registration_denied_email_body" => '',
      "event_registration_form_format" => '',
      "event_cancel_form_format" => '',
      "event_registration_recorded_ok_html" => '',
      "event_slug" => '',
      "event_image_url" => '',
      "event_image_id" => 0,
      "event_external_ref" => '',
      "event_url" => '',
      "recurrence_id" => 0
   );
   $event['event_properties'] = eme_init_event_props($event['event_properties']);
   return $event;
}

function eme_init_event_props($props) {
   if (!isset($props['auto_approve']))
      $props['auto_approve']=0;
   if (!isset($props['ignore_pending']))
      $props['ignore_pending']=0;
   if (!isset($props['all_day']))
      $props['all_day']=0;
   if (!isset($props['take_attendance']))
      $props['take_attendance']=0;
   if (!isset($props['min_allowed']))
      $props['min_allowed']=get_option('eme_rsvp_addbooking_min_spaces');
   if (!isset($props['max_allowed']))
      $props['max_allowed']=get_option('eme_rsvp_addbooking_max_spaces');
   if (!isset($props['rsvp_end_target']))
      $props['rsvp_end_target']=get_option('eme_rsvp_end_target');
   if (!isset($props['rsvp_discount']))
      $props['rsvp_discount']='';
   if (!isset($props['waitinglist_seats']))
      $props['waitinglist_seats']=0;
   if (!isset($props['rsvp_discountgroup']))
      $props['rsvp_discountgroup']='';
   if (!isset($props['use_worldpay']))
      $props['use_worldpay']=get_option('eme_worldpay_instid')? 1:0;
   if (!isset($props['use_stripe']))
      $props['use_stripe']=0;
   if (!isset($props['use_braintree']))
      $props['use_braintree']=0;
   if (!isset($props['use_offline']))
      $props['use_offline']=get_option('eme_offline_payment')? 1:0;
   if (!isset($props['cancel_rsvp_days']))
      $props['cancel_rsvp_days']=get_option('eme_cancel_rsvp_days');

   $template_override=array('event_page_title_format_tpl','event_single_event_format_tpl','event_contactperson_email_body_tpl','event_registration_recorded_ok_html_tpl','event_respondent_email_body_tpl','event_registration_pending_email_body_tpl','event_registration_updated_email_body_tpl','event_registration_cancelled_email_body_tpl','event_registration_denied_email_body_tpl','event_registration_form_format_tpl','event_cancel_form_format_tpl','event_registration_paid_email_body_tpl');
   foreach ($template_override as $template) {
      if (!isset($props[$template]))
         $props[$template]=0;
   }

   return $props;
}

function eme_events_page() {
   global $wpdb, $eme_timezone;

   $extra_conditions = array();
   if (isset($_POST['eme_admin_action'])) {
	   $action = $_POST['eme_admin_action'];
   } elseif (isset($_GET['eme_admin_action'])) {
	   $action = $_GET['eme_admin_action'];
   } else {
	   $action = '';
   }
   $event_ID = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
   $recurrence_ID = isset($_GET['recurrence_id']) ? intval($_GET['recurrence_id']) : 0;
   $selectedEvents = isset($_GET['events']) ? $_GET['events'] : '';

   $current_userid=get_current_user_id();

   // DELETE action (when the delete button is pushed while editing an event)
   if (isset($_POST['event_delete_button'])) {
      check_admin_referer('eme_events','eme_admin_nonce');
      if (current_user_can(get_option('eme_cap_edit_events'))) {
         $ids_arr=array($event_ID);
         $res=eme_delete_events($ids_arr);
         if ($res==0) {
            $feedback_message = __ ( 'Event(s) deleted!', 'events-made-easy');
         } else {
            $feedback_message = __ ( 'Error deleting events!', 'events-made-easy');
         }
      } else {
         $feedback_message = __ ( 'You have no right to delete events!', 'events-made-easy');
      }
      eme_events_table ($feedback_message);
      return;
   }

   // DELETE action (when the delete button is pushed while editing a recurrence)
   if (isset($_POST['event_deleteRecurrence_button'])) {
      check_admin_referer('eme_events','eme_admin_nonce');
      if (current_user_can(get_option('eme_cap_edit_events'))) {
         $recurrence=eme_get_recurrence($recurrence_ID);
         $ids_arr=array($recurrence['event_id']);
         $res=eme_delete_recurrences($ids_arr);
         if ($res==0) {
            $feedback_message = __ ( 'Event(s) deleted!', 'events-made-easy');
         } else {
            $feedback_message = __ ( 'Error deleting events!', 'events-made-easy');
         }
      } else {
         $feedback_message = __ ( 'You have no right to delete events!', 'events-made-easy');
      }
      eme_events_table ($feedback_message);
      return;
   }

   // UPDATE or CREATE action
   if ($action == 'insert_event' || $action == 'update_event' || $action == 'update_recurrence') {
      check_admin_referer('eme_events','eme_admin_nonce');
      // if not the result of a POST, then just show the list
      if ($_SERVER['REQUEST_METHOD'] != 'POST') {
         eme_events_table ();
         return;
      }
      if ( ! (current_user_can( get_option('eme_cap_add_event')) || current_user_can( get_option('eme_cap_edit_events'))) ) {
         $feedback_message = __('You have no right to insert or update events','events-made-easy');
         eme_events_table ($feedback_message);
         return;
      }

      if ($action == 'insert_event') {
         $event=eme_new_event();
         $location = eme_new_location();
      } elseif ($action == 'update_event' && $event_ID) {
         $event = eme_get_event($event_ID);
         $location = eme_get_location($event['location_id']);
      } elseif ($action == 'update_recurrence' && $recurrence_ID) {
         $event = eme_get_event(eme_get_recurrence_first_eventid($recurrence_ID));
         $location = eme_get_location($event['location_id']);
      }
      $orig_event = $event;
      $post_vars=array('event_name','event_start_date','event_end_date','event_seats','price','rsvp_number_days','rsvp_number_hours','currency','event_contactperson_id','event_url','event_image_url','event_image_id','event_slug','event_page_title_format','event_single_event_format','event_contactperson_email_body','event_registration_recorded_ok_html','event_respondent_email_body','event_registration_pending_email_body','event_registration_updated_email_body','event_registration_cancelled_email_body','event_registration_denied_email_body','event_registration_form_format','event_cancel_form_format','event_registration_paid_email_body');
      foreach ($post_vars as $post_var) {
         // sanitizing comes later
         if (isset($_POST[$post_var])) $event[$post_var]=stripslashes($_POST[$post_var]);
      }
      // now for the select boxes, we need to set to 0 if not in the _POST
      $select_post_vars=array('use_paypal','use_2co','use_webmoney','use_fdgg','use_mollie','use_sagepay','event_rsvp','registration_requires_approval','registration_wp_users_only');
      foreach ($select_post_vars as $post_var) {
         // sanitizing comes later
         if (isset($_POST[$post_var])) $event[$post_var]=stripslashes($_POST[$post_var]);
         else $event[$post_var]=0;
      }
      //switched to WP TinyMCE field
      $event['event_notes'] = isset($_POST['content']) ? stripslashes($_POST['content']) : '';
      
      if (!current_user_can( get_option('eme_cap_publish_event')) ) {
         $event['event_status']=STATUS_DRAFT;
      } else {
         if (isset($_POST['event_status'])) $event['event_status'] = stripslashes($_POST['event_status']);
      }

      $eme_date_obj = new ExpressiveDate(null,$eme_timezone);
      if (isset($_POST['event_start_time']) && !empty($_POST['event_start_time'])) {
         $event['event_start_time'] = $eme_date_obj->setTimestampFromString($_POST['event_start_time']." ".$eme_timezone)->format("H:i:00");
      }
      if (isset($_POST['event_end_time']) && !empty($_POST['event_end_time'])) {
         $event['event_end_time'] = $eme_date_obj->setTimestampFromString($_POST['event_end_time']." ".$eme_timezone)->format("H:i:00");
      }
      $recurrence['recurrence_freq'] = isset($_POST['recurrence_freq']) ? $_POST['recurrence_freq'] : '';
      if ($recurrence['recurrence_freq'] == 'specific') {
         $recurrence['recurrence_specific_days'] = isset($_POST['recurrence_start_date']) ? $_POST['recurrence_start_date'] : $event['event_start_date'];
         $recurrence['recurrence_start_date'] = "";
         $recurrence['recurrence_end_date'] = "";
      } else {
         $recurrence['recurrence_specific_days'] = "";
         $recurrence['recurrence_start_date'] = isset($_POST['recurrence_start_date']) ? $_POST['recurrence_start_date'] : $event['event_start_date'];
         $recurrence['recurrence_end_date'] = isset($_POST['recurrence_end_date']) ? $_POST['recurrence_end_date'] : $event['event_end_date'];
      }
      if (!eme_is_date_valid($recurrence['recurrence_start_date']))
          $recurrence['recurrence_start_date'] = "";
      if (!eme_is_date_valid($recurrence['recurrence_end_date']))
          $recurrence['recurrence_end_date'] = $recurrence['recurrence_start_date'];
      if (!eme_are_dates_valid($recurrence['recurrence_specific_days']))
          $recurrence['recurrence_specific_days'] = "";
      if ($recurrence['recurrence_freq'] == 'weekly') {
         if (isset($_POST['recurrence_bydays'])) {
            $recurrence['recurrence_byday'] = implode ( ",", $_POST['recurrence_bydays']);
         } else {
            $recurrence['recurrence_byday'] = '';
         }
      } else {
         if (isset($_POST['recurrence_byday'])) {
            $recurrence['recurrence_byday'] = $_POST['recurrence_byday'];
         } else {
            $recurrence['recurrence_byday'] = '';
         }
      }
      $recurrence['recurrence_interval'] = isset($_POST['recurrence_interval']) ? $_POST['recurrence_interval'] : 1;
      if ($recurrence['recurrence_interval'] ==0)
         $recurrence['recurrence_interval']=1;
      $recurrence['recurrence_byweekno'] = isset($_POST['recurrence_byweekno']) ? $_POST['recurrence_byweekno'] : '';
      $recurrence['holidays_id'] = isset($_POST['holidays_id']) ? intval($_POST['holidays_id']) : 0;
      
      $post_vars=array('location_name','location_address1','location_address2','location_city','location_state','location_zip','location_country','location_latitude','location_longitude');
      foreach ($post_vars as $post_var) {
         // sanitizing comes later
         if (isset($_POST[$post_var])) $location[$post_var]=stripslashes($_POST[$post_var]);
      }
      $location['location_description'] = "";

      if (isset ($_POST['event_category_ids']) && eme_array_integers($_POST['event_category_ids'])) {
         $event ['event_category_ids']= join(',',$_POST['event_category_ids']);
      } else {
         $event ['event_category_ids']="";
      }

      $event_attributes = array();
      for($i=1 ; isset($_POST["eme_attr_{$i}_ref"]) && trim($_POST["eme_attr_{$i}_ref"])!='' ; $i++ ) {
         if(trim($_POST["eme_attr_{$i}_name"]) != '') {
            $event_attributes[$_POST["eme_attr_{$i}_ref"]] = stripslashes($_POST["eme_attr_{$i}_name"]);
         }
      }
      $event['event_attributes'] = serialize($event_attributes);

      $event_properties = array();
      $event_properties = eme_init_event_props($event_properties);
      // now for the select boxes, we need to set to 0 if not in the _POST
      $select_post_vars=array('use_offline');
      foreach ($select_post_vars as $post_var) {
         if (!isset($_POST['eme_prop_'.$post_var])) $event_properties[$post_var]=0;
      }
      foreach($_POST as $key=>$value) {
         if (preg_match('/eme_prop_(.+)/', $key, $matches)) {
            $event_properties[$matches[1]] = stripslashes($value);
         }
      }
      $event_rsvp_dyndata = eme_handle_dyndata_post_adminform();
      if (!empty($event_rsvp_dyndata))
	      $event_properties['rsvp_dyndata'] = $event_rsvp_dyndata;
      $event['event_properties'] = serialize($event_properties);
      
      $event = eme_sanitize_event($event);
      $location = eme_sanitize_location($location);
      $validation_result = eme_validate_event($event);
      if ($validation_result != "OK") {
         // validation unsuccessful       
         echo "<div id='message' class='error '>
                  <p>$validation_result</p>
              </div>";
         $title = sprintf(__("Edit Event '%s'",'events-made-easy'),$event['event_name']);
         eme_event_form ( $event, $title, $event_ID );
         return;
      }

      // validation successful
      if(isset($_POST['location-select-id']) && $_POST['location-select-id'] != "") {
         $event['location_id'] = $_POST['location-select-id'];
      } else {
	 if (isset($_POST['location_id']) && intval($_POST['location_id'])>0) {
	    $event['location_id'] = intval($_POST['location_id']);
	 } elseif (empty($location['location_name']) && empty($location['location_address1']) && empty($location['location_city'])) {
            $event['location_id'] = 0;
         } else {
            $related_location_id = eme_get_identical_location_id ($location);
            if ($related_location_id) {
               $event['location_id'] = $related_location_id;
            } else {
	       $validation_result = eme_validate_location ( $location );
	       if ($validation_result != "OK") {
                  echo "<div id='message' class='error '>
                        <p>$validation_result</p>
                        </div>";
               } else {
                  $new_location = eme_insert_location ( $location );
                  if (!$new_location) {
                     echo "<div id='message' class='error '>
                        <p>" . __ ( "Could not create the new location for this event: either you don't have the right to insert locations or there's a DB problem.", "eme" , 'events-made-easy') . "</p>
                        </div>";
                     return;
                  }
                  $event['location_id'] = $new_location['location_id'];
               }
            }
         }
      }
      if (! $event_ID && ! $recurrence_ID) {
         // new event or new recurrence
         if (isset($_POST['repeated_event']) && $_POST['repeated_event']) {
            //insert new recurrence
            $count=eme_db_insert_recurrence ( $recurrence, $event );
            if (!$count) {
	       $feedback_message = __ ( 'No recurrent event created since it contains 0 events', 'events-made-easy');
            } else {
               $feedback_message = sprintf(__ ( 'New recurrent event inserted containing %d events', 'events-made-easy'),$count);
            }
         } else {
            // INSERT new event 
            if (!eme_db_insert_event($event)) {
               $feedback_message = __ ( 'Database insert failed!', 'events-made-easy');
            } else {
               $feedback_message = __ ( 'New event successfully inserted!', 'events-made-easy');
            }
         }
      } else {
         // something exists
         if ($recurrence_ID) {
            $tmp_recurrence = eme_get_recurrence ( $recurrence_ID );
            if (current_user_can( get_option('eme_cap_edit_events')) ||
                (current_user_can( get_option('eme_cap_author_event')) && ($tmp_recurrence['event_author']==$current_userid || $tmp_recurrence['event_contactperson_id']==$current_userid))) {
               // UPDATE old recurrence
               $recurrence['recurrence_id'] = $recurrence_ID;
               $count=eme_db_update_recurrence ($recurrence,$event);
               if ($count) {
                  $feedback_message = sprintf(__ ( 'Recurrence updated, contains %d events', 'events-made-easy'),$count);
               } else {
                  $feedback_message = __ ( 'Recurrence no longer contains events, so it has been removed', 'events-made-easy');
               }
            } else {
               $feedback_message = sprintf(__("You have no right to update '%s'",'events-made-easy'),$orig_event['event_name']);
            }
         } else {
            if (current_user_can( get_option('eme_cap_edit_events')) ||
                (current_user_can( get_option('eme_cap_author_event')) && ($orig_event['event_author']==$current_userid || $orig_event['event_contactperson_id']==$current_userid))) {
               if (isset($_POST['repeated_event']) && $_POST['repeated_event']) {
                  // we go from single event to recurrence: create the recurrence and delete the single event
                  $count=eme_db_insert_recurrence ( $recurrence, $event );
		  if (!$count) {
	             $feedback_message = __ ( 'No recurrent events created!', 'events-made-easy');
		  } else {
		     eme_db_delete_event ( $orig_event );
                     $feedback_message = sprintf(__ ( 'New recurrent event inserted containing %d events', 'events-made-easy'),$count);
		  }
               } else {
                  // UPDATE old event
                  // unlink from recurrence in case it was generated by one
                  $event['recurrence_id'] = 0;
                  if (eme_db_update_event ($event,$event_ID)) {
                     $feedback_message = sprintf(__("Updated '%s'",'events-made-easy'),$event['event_name']);
                  } else {
                     $feedback_message = sprintf(__("Failed to update '%s'",'events-made-easy'),$event['event_name']);
                  }
               }
            } else {
               $feedback_message = sprintf(__("You have no right to update '%s'",'events-made-easy'),$orig_event['event_name']);
            }
         }
      }
         
      //$wpdb->query($sql); 
      eme_events_table ($feedback_message);
      return;
   }

   if ($action == 'add_new_event') {
         if (current_user_can( get_option('eme_cap_add_event'))) {
	    $event=eme_new_event();
            $title = __ ( "Insert New Event", 'events-made-easy');
            eme_event_form ( $event, $title );
         } else {
            $feedback_message = __('You have no right to add events!','events-made-easy');
            eme_events_table ($feedback_message);
         }
	 return;
   }

   if ($action == 'edit_event') {
         check_admin_referer('eme_events','eme_admin_nonce');
	 $event = eme_get_event ( $event_ID );
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            // UPDATE event
            $title = sprintf(__("Edit Event '%s'",'events-made-easy'),$event['event_name']);
            eme_event_form ( $event, $title, $event_ID );
         } else {
            $feedback_message = sprintf(__("You have no right to update '%s'",'events-made-easy'),$event['event_name']);
            eme_events_table ($feedback_message);
         }
	 return;
   }

   //Add duplicate event if requested
   if ($action == 'duplicate_event') {
      check_admin_referer('eme_events','eme_admin_nonce');
      $event = eme_get_event ( $event_ID );
      // make it look like a new event
      unset($event['event_id']);
      unset($event['recurrence_id']);
      $event['event_name'].= __(" (Copy)",'events-made-easy');

      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
         $title = sprintf(__("Edit event copy '%s'",'events-made-easy'),$event['event_name']);
         eme_event_form ( $event, $title );
      } else {
         $feedback_message = sprintf(__("You have no right to copy '%s'",'events-made-easy'),$event['event_name']);
         eme_events_table ($feedback_message);
      }
      return;
   }

   if ($action == 'edit_recurrence') {
      check_admin_referer('eme_events','eme_admin_nonce');
      $recurrence = eme_get_recurrence ( $recurrence_ID );
      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($recurrence['event_author']==$current_userid || $recurrence['event_contactperson_id']==$current_userid))) {
         $title = __ ( "Edit Recurrence", 'events-made-easy') . " '" . $recurrence['event_name'] . "'";
         eme_event_form ( $recurrence, $title, $recurrence_ID );
      } else {
         $feedback_message = __('You have no right to update','events-made-easy'). " '" . $recurrence['event_name'] . "' !";
         eme_events_table ($feedback_message);
      }
      return;
   }
   
   if ($action == "-1" || $action == "") {
      // No action, only showing the events list
      eme_events_table();
      return;
   }
}

// array of all pages, bypasses the filter I set up :)
function eme_get_all_pages() {
   global $wpdb;
   $query = "SELECT id, post_title FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status='publish'";
   $pages = $wpdb->get_results ( $query, ARRAY_A );
   // get_pages() is better, but uses way more memory and it might be filtered by eme_filter_get_pages()
   //$pages = get_pages();
   $output = array ();
   $output[] = __( 'Please select a page','events-made-easy');
   foreach ( $pages as $page ) {
      $output[$page['id']] = $page['post_title'];
   // $output[$page->ID] = $page->post_title;
   }
   return $output;
}

//This is the content of the event page
function eme_events_page_content() {
   global $wpdb;

   if (isset($_REQUEST['eme_cancel_booking'])) {
      // GET for cancel links, POST for the cancel form
      $payment_randomid=eme_strip_tags($_REQUEST['eme_cancel_booking']);
      return eme_cancel_confirm_form($payment_randomid);

   } elseif (isset($_POST['eme_confirm_cancel_booking']) && isset($_POST['eme_pmt_rndid'])) {
      $payment_randomid=eme_strip_tags($_POST['eme_pmt_rndid']);
      if (isset($_POST['eme_rsvp_nonce']) && wp_verify_nonce($_POST['eme_rsvp_nonce'],"cancel booking $payment_randomid")) {
         $booking_ids=eme_get_randompayment_booking_ids($payment_randomid);
         foreach ($booking_ids as $booking_id) {
            $booking=eme_get_booking($booking_id);
            // delete the booking before the mail is sent, so free spaces are correct
            eme_delete_booking($booking_id);
            eme_email_rsvp_booking($booking,"cancelRegistration");
            // delete the booking answers after the mail is sent, so the answers can still be used in the mail
            eme_delete_booking_answers($booking_id);
         }
         eme_delete_payment($booking['payment_id']);
      }
      return "<div class='eme-rsvp-message'>".__("The bookings have been cancelled",'events-made-easy')."</div>";

   } elseif (isset($_GET['eme_unsub'])) {
      return eme_unsub_form();

   } elseif (isset($_POST['eme_unsub_email'])) {
      $eme_email=eme_strip_tags($_POST['eme_unsub_email']);
      eme_unsub_send_mail($eme_email);
      return "<div class='eme-rsvp-message'>".__("A request for confirmation has been sent to the given email address.",'events-made-easy')."</div>";

   } elseif (isset($_GET['eme_unsub_confirm']) && isset($_GET['eme_unsub_nonce'])) {
      $eme_email=eme_strip_tags($_GET['eme_unsub_confirm']);
      if (wp_verify_nonce($_GET['eme_unsub_nonce'],"unsub $eme_email")) {
	      eme_unsub_do($eme_email);
	      return "<div class='eme-rsvp-message'>".__("You have been unsubscribed.",'events-made-easy')."</div>";
      } else {
	      return "<div class='eme-rsvp-message'>".__("Nothing happened",'events-made-easy')."</div>";
      }

   } elseif (isset($_GET['eme_gdpr']) && isset($_GET['eme_gdpr_nonce'])) {
      $eme_email=eme_strip_tags($_GET['eme_gdpr']);
      if (wp_verify_nonce($_GET['eme_gdpr_nonce'],"gdpr $eme_email")) {
	      return eme_gdpr_show($eme_email);
      } else {
	      return "<div class='eme-rsvp-message'>".__("Nothing happened",'events-made-easy')."</div>";
      }

   } elseif (get_query_var('eme_pmt_result') && get_query_var('eme_pmt_rndid') && get_option('eme_payment_show_custom_return_page')) {
      $payment_randomid=eme_strip_tags(get_query_var('eme_pmt_rndid'));
      $payment = eme_get_payment(0,$payment_randomid);
      $result=get_query_var('eme_pmt_result');
      if ($result == 'succes')
         $format = get_option('eme_payment_succes_format');
      else
         $format = get_option('eme_payment_fail_format');
      
      if ($payment['member_id']>0) {
	 if ($result == 'succes')
	      $format = get_option('eme_payment_member_succes_format');
	 else
	      $format = get_option('eme_payment_member_fail_format');
	 $member = eme_get_member($payment['member_id']);
         $membership = eme_get_membership($member['membership_id']);
	 return eme_replace_member_placeholders($format,$membership,$member);
      } else {
	 $booking_ids = eme_get_randompayment_booking_ids($payment_randomid);
         if ($booking_ids) {
            // since each booking is for a different event, we can't know which one to show
            // so we show only the first one
            $booking = eme_get_booking($booking_ids[0]);
	    $event = eme_get_event($booking['event_id']);
            $format = eme_replace_placeholders($format, $event, "html", 0);
            return eme_replace_booking_placeholders($format,$event,$booking);
         } else {
            return $format;
         }
      }
   } elseif (get_query_var('eme_pmt_rndid')) {
      $payment_randomid=eme_strip_tags(get_query_var('eme_pmt_rndid'));
      $payment=eme_get_payment(0,$payment_randomid);
      $page_body = eme_payment_form($payment['id']);
      return $page_body;
   }

   if (get_query_var('eme_city')) {
      $eme_city=eme_sanitize_request(get_query_var('eme_city'));
      $location_ids = join(',',eme_get_city_location_ids($eme_city));
      $stored_format = get_option('eme_event_list_item_format');
      if (count($location_ids)>0) {
         $format_header = get_option('eme_event_list_item_format_header' );
         if (empty($format_header)) $format_header = DEFAULT_EVENT_LIST_HEADER_FORMAT;
         $format_footer = get_option('eme_event_list_item_format_footer' );
         if (empty($format_footer)) $format_footer = DEFAULT_EVENT_LIST_FOOTER_FORMAT;
         $page_body = eme_get_events_list ( get_option('eme_event_list_number_items' ), "future", "ASC", $stored_format, $format_header,$format_footer, 0, '','',0,'','',0,$location_ids);
      } else {
         $page_body = "<span id='events-no-events'>" . get_option('eme_no_events_message') . "</span>";
      }
      return $page_body;
   }
   if (get_query_var('eme_country')) {
      $eme_country=eme_sanitize_request(get_query_var('eme_country'));
      $location_ids = join(',',eme_get_country_location_ids($eme_country));
      $stored_format = get_option('eme_event_list_item_format');
      if (count($location_ids)>0) {
         $format_header = get_option('eme_event_list_item_format_header' );
         if (empty($format_header)) $format_header = DEFAULT_EVENT_LIST_HEADER_FORMAT;
         $format_footer = get_option('eme_event_list_item_format_footer' );
         if (empty($format_footer)) $format_footer = DEFAULT_EVENT_LIST_FOOTER_FORMAT;
         $page_body = eme_get_events_list ( get_option('eme_event_list_number_items' ), "future", "ASC", $stored_format, $format_header,$format_footer, 0, '','',0,'','',0,$location_ids);
      } else {
         $page_body = "<span id='events-no-events'>" . get_option('eme_no_events_message') . "</span>";
      }
      return $page_body;
   }
   if (get_query_var('location_id')) {
      $location = eme_get_location ( intval(get_query_var('location_id')));
      $single_location_format = get_option('eme_single_location_format' );
      $page_body = eme_replace_locations_placeholders ( $single_location_format, $location );
      return $page_body;
   }
   if (!get_query_var('calendar_day') && get_query_var('eme_event_cat')) {
      $format_header = get_option('eme_cat_event_list_item_format_header' );
      if (empty($format_header)) $format_header = DEFAULT_CAT_EVENT_LIST_HEADER_FORMAT;
      $format_footer = get_option('eme_cat_event_list_item_format_footer' );
      if (empty($format_footer)) $format_footer = DEFAULT_CAT_EVENT_LIST_FOOTER_FORMAT;
      $eme_event_cat=eme_sanitize_request(get_query_var('eme_event_cat'));
      $cat_ids = join(',',eme_get_category_ids($eme_event_cat));
      $stored_format = get_option('eme_event_list_item_format');
      if (!empty($cat_ids)) {
         $page_body = eme_get_events_list ( get_option('eme_event_list_number_items' ), "future", "ASC", $stored_format, $format_header,$format_footer, 0, $cat_ids);
      } else {
         $page_body = "<span id='events-no-events'>" . get_option('eme_no_events_message') . "</span>";
      }
      return $page_body;
   }

   //if (isset ( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != '') {
   if (eme_is_single_event_page()) {
      // single event page
      $event_id = intval(get_query_var('event_id'));
      return eme_display_single_event($event_id);
   } elseif (get_query_var('calendar_day')) {
      // we don't use urldecode on the _GET params, since we pass them the url-way to eme_get_events_list
      $scope = eme_sanitize_request(get_query_var('calendar_day'));
      $location_id = isset( $_GET['location_id'] ) ? $_GET['location_id'] : '';
      $category = isset( $_GET['category'] ) ? $_GET['category'] : '';
      $notcategory = isset( $_GET['notcategory'] ) ? $_GET['notcategory'] : '';
      $author = isset( $_GET['author'] ) ? $_GET['author'] : '';
      $contact_person = isset( $_GET['contact_person'] ) ? $_GET['contact_person'] : '';
      $show_single_event = get_option('eme_cal_show_single')? 1 : 0;
      $page_body = eme_get_events_list ("limit=0&scope=$scope&category=$category&author=$author&contact_person=$contact_person&location_id=$location_id&notcategory=$notcategory&show_single_event=$show_single_event");
      return $page_body;
   } else {
      // Multiple events page
      $scope = isset($_GET['scope']) ? eme_sanitize_request($_GET['scope']) : "future";
      if (get_option('eme_display_calendar_in_events_page' )){
         $page_body = eme_get_calendar ('full=1');
      }else{
         $page_body = eme_get_events_list ("scope=$scope");
      }
      return $page_body;
   }
}

function eme_events_count_for($date) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions[] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions[] = "event_status=".STATUS_PUBLIC;
      }
   }
   $conditions[] = "((event_start_date  like '$date') OR (event_start_date <= '$date' AND event_end_date >= '$date'))";
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT COUNT(*) FROM  $table_name $where";
   return $wpdb->get_var ( $sql );
}

// filter function to call the event page when appropriate
function eme_filter_events_page($data) {

   global $wp_current_filter;

   // we need to make sure we do this only once. Reason being: other plugins can call the_content as well
   // Suppose you add a shortcode from another plugin to the detail part of an event and that other plugin
   // calls apply_filter('the_content'), then this would cause recursion since that call would call our filter again
   // If the_content is the current filter definition (last element in the array), when there's more than one
   // (this is possible since one filter can call another, apply_filters does this), we can be in such a loop
   // And since our event content is only meant to be shown as content of a page (the_content is then the only element
   // in the $wp_current_filter array), we can then skip it
   $eme_count_arr=array_count_values($wp_current_filter);
   $eme_event_parsed=0;
   $eme_loop_protection=get_option('eme_loop_protection');
   switch ($eme_loop_protection) {
      case "default":
         if (count($wp_current_filter)>1 && end($wp_current_filter)=='the_content')
            $eme_event_parsed=1;
         break;
      case "older":
         if (count($wp_current_filter)>1 && end($wp_current_filter)=='the_content' && $eme_count_arr['the_content']>1)
            $eme_event_parsed=1;
         break;
      case "desperate":
         if ((count($wp_current_filter)>1 && end($wp_current_filter)=='the_content') || $eme_count_arr['the_content']>1)
            $eme_event_parsed=1;
         break;
   }
   // we change the content of the page only if we're "in the loop",
   // otherwise this filter also gets applied if e.g. a widget calls
   // the_content or the_excerpt to get the content of a page
   if (in_the_loop() && eme_is_events_page() && !$eme_event_parsed) {
      return eme_events_page_content ();
   } else {
      return $data;
   }
}
add_filter ( 'the_content', 'eme_filter_events_page' );

function eme_page_title($data) {
   $events_page_id = eme_get_events_page_id();
   $events_page = get_page ( $events_page_id );
   $events_page_title = $events_page->post_title;

   // make sure we only replace the title for the events page, not anything
   // from the menu (which is also in the loop ...)
   if (($data == $events_page_title) && in_the_loop() && eme_is_events_page()) {
      if (get_query_var('calendar_day')) {
         
         $date = eme_sanitize_request(get_query_var('calendar_day'));
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request(get_query_var('calendar_day')));
            $event = $events[0];
            if (!empty($event['event_page_title_format']))
               $stored_page_title_format = $event['event_page_title_format'];
            elseif ($event['event_properties']['event_page_title_format_tpl']>0)
               $stored_page_title_format = eme_get_template_format($event['event_properties']['event_page_title_format_tpl']);
            else
               $stored_page_title_format = get_option('eme_event_page_title_format' );
            $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
            return $page_title;
         }
      }
      
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval(get_query_var('event_id'));
         $event = eme_get_event ( $event_ID );
         if (!empty($event['event_page_title_format']))
            $stored_page_title_format = $event['event_page_title_format'];
         elseif ($event['event_properties']['event_page_title_format_tpl']>0)
            $stored_page_title_format = eme_get_template_format($event['event_properties']['event_page_title_format_tpl']);
         else
            $stored_page_title_format = get_option('eme_event_page_title_format' );
         $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
         return $page_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval(get_query_var('location_id')));
         $stored_page_title_format = get_option('eme_location_page_title_format' );
         $page_title = eme_replace_locations_placeholders ( $stored_page_title_format, $location );
         return $page_title;
      } else {
         // Multiple events page
         $page_title = get_option('eme_events_page_title' );
         return $page_title;
      }
   } else {
      return $data;
   }
}

function eme_html_title($data) {
   if (eme_is_events_page()) {
      if (get_query_var('calendar_day')) {
         
         $date = eme_sanitize_request(get_query_var('calendar_day'));
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request(get_query_var('calendar_day')));
            $event = $events[0];
            $stored_html_title_format = get_option('eme_event_html_title_format' );
            $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
            return $html_title;
         }
      }
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval(get_query_var('event_id'));
         $event = eme_get_event ( $event_ID );
         $stored_html_title_format = get_option('eme_event_html_title_format' );
         $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
         return $html_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval(get_query_var('location_id')));
         $stored_html_title_format = get_option('eme_location_html_title_format' );
         $html_title = eme_strip_tags(eme_replace_locations_placeholders ( $stored_html_title_format, $location ));
         return $html_title;
      } else {
         // Multiple events page
         $html_title = get_option('eme_events_page_title' );
         return $html_title;
      }
   } else {
      return $data;
   }
}

// the filter single_post_title influences the html header title and the page title
// we want to prevent html tags in the html header title (if you add html in the 'single event title format', it will show)
add_filter ( 'single_post_title', 'eme_html_title' );
add_filter ( 'the_title', 'eme_page_title' );

// let's change the edit url used in wp themes for single events and locations
function eme_edit_post_link($data) {
      if (eme_is_single_event_page()) {
         $event_id = intval(get_query_var('event_id'));
	 return wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event_id),'eme_events','eme_admin_nonce');
      } elseif (eme_is_single_location_page()) {
         $location_id = intval(get_query_var('location_id'));
	 return wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=edit_location&amp;location_id=".$location_id),'eme_locations','eme_admin_nonce');
      } else {
	 return $data;
      }
}
add_filter ('get_edit_post_link', 'eme_edit_post_link');

function eme_template_redir() {
# We need to catch the request as early as possible, but
# since it needs to be working for both permalinks and normal,
# I can't use just any action hook. parse_query seems to do just fine
   if (get_query_var('event_id')) {
      $event_id = intval(get_query_var('event_id'));
      if (!eme_check_event_exists($event_id)) {
         status_header(404);
         nocache_headers();
         if ('' != get_404_template())
            include( get_404_template() );
         exit;
      }
   }
   if (get_query_var('location_id')) {
      $location_id = intval(get_query_var('location_id'));
      if (!eme_check_location_exists($location_id)) {
         status_header(404);
         nocache_headers();
         if ('' != get_404_template())
            include( get_404_template() );
         exit;
      }
   }

   if (eme_is_single_event_page() || eme_is_single_location_page()) {
      // remove rel_canonical from the header, EME generates it's own one
      remove_action( 'wp_head', 'rel_canonical' );
      // remove yoast SEO from the header, EME generates it's own one
      global $wpseo_front;
      if (isset($wpseo_front))
         remove_action( 'wp_head', array($wpseo_front, 'head'), 2 );
   }

}

// filter out the events page in the get_pages call
function eme_filter_get_pages($data) {
   $events_page_id = eme_get_events_page_id();
   foreach ($data as $key => $item) {
      if ($item->ID == $events_page_id) {
         unset($data[$key]);
      }
   }
   //return $output;
   return $data;
}
add_filter ( 'get_pages', 'eme_filter_get_pages' );

//filter out the events page in the admin section
function eme_exclude_this_page( $query ) {
   if( !is_admin() )
      return $query;

   global $pagenow;
   $events_page_id = eme_get_events_page_id();

   if( 'edit.php' == $pagenow && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ) )
      $query->set( 'post__not_in', array($events_page_id) );
   return $query;
}

// TEMPLATE TAGS

// exposed function, for theme  makers
   //Added a category option to the get events list method and shortcode
function eme_get_events_list($limit, $scope = "future", $order = "ASC", $format = '', $format_header='', $format_footer='', $echo = 0, $category = '',$showperiod = '', $long_events = 0, $author = '', $contact_person='', $paging=0, $location_id = "", $user_registered_only = 0, $show_ongoing=1, $link_showperiod=0, $notcategory = '', $show_recurrent_events_once= 0, $template_id = 0, $template_id_header=0, $template_id_footer=0, $no_events_message="", $show_single_event=0) {
   global $post, $eme_timezone;
   if ($limit === "") {
      $limit = get_option('eme_event_list_number_items' );
   }
   if (strpos ( $limit, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $eme_event_list_number_events=get_option('eme_event_list_number_items' );
      $defaults = array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'echo' => 1 , 'category' => '', 'showperiod' => '', 'author' => '', 'contact_person' => '', 'paging'=>0, 'long_events' => 0, 'location_id' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '', 'show_recurrent_events_once' => 0, 'template_id' => 0, 'template_id_header' => 0, 'template_id_footer' => 0, 'no_events_message' => '','show_single_event' => 0);
      $r = wp_parse_args ( $limit, $defaults );
      extract ( $r );
      // for AND categories: the user enters "+" and this gets translated to " " by wp_parse_args
      // so we fix it again
      $category = preg_replace("/ /","+",$category);
      $notcategory = preg_replace("/ /","+",$notcategory);
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $paging = ($paging==="true" || $paging==="1") ? true : $paging;
   $show_ongoing = ($show_ongoing==="true" || $show_ongoing==="1") ? true : $show_ongoing;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;
   $paging = ($paging==="false" || $paging==="0") ? false : $paging;
   $show_ongoing = ($show_ongoing==="false" || $show_ongoing==="0") ? false : $show_ongoing;
   if ($scope == "")
      $scope = "future";

   if ($template_id) {
      $format = eme_get_template_format($template_id);
   }
   if ($template_id_header) {
      $format_header = eme_get_template_format($template_id_header);
   }
   if ($template_id_footer) {
      $format_footer = eme_get_template_format($template_id_footer);
   }
   if (empty($format)) {
      $format = get_option('eme_event_list_item_format' );
      if (empty($format_header)) {
	      $format_header = get_option('eme_event_list_item_format_header' );
         if (empty($format_header)) $format_header = DEFAULT_EVENT_LIST_HEADER_FORMAT;
      }
      if (empty($format_footer)) {
	      $format_footer = get_option('eme_event_list_item_format_footer' );
         if (empty($format_footer)) $format_footer = DEFAULT_EVENT_LIST_FOOTER_FORMAT;
      }
   }

   // for registered users: we'll add a list of event_id's for that user only
   $extra_conditions = "";
   if ($user_registered_only == 1 && is_user_logged_in()) {
      $current_userid=get_current_user_id();
      $list_of_event_ids=join(",",eme_get_event_ids_for($current_userid));
      if (!empty($list_of_event_ids)) {
         $extra_conditions = " (event_id in ($list_of_event_ids))";
      } else {
         // user has no registered events, then make sure none are shown
         $extra_conditions = " (event_id = 0)";
      }
   }

   $prev_text = "";
   $next_text = "";
   $limit_start=0;
   $limit_end=0;

   // for browsing: if limit=0,paging=1 and only for this_week,this_month or today
   if ($limit>0 && $paging==1 && isset($_GET['eme_offset'])) {
      $limit_offset=intval($_GET['eme_offset']);
   } else {
      $limit_offset=0;
   }

   // if extra scope_filter is found (from the eme_filter shortcode), then no paging
   // since it won't work anyway
   if (isset($_REQUEST["eme_scope_filter"]))
      $paging=0;

   if ($paging==1 && $limit==0) {
      $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
      $scope_offset=0;
      $scope_text = "";
      if (isset($_GET['eme_offset']))
         $scope_offset=$_GET['eme_offset'];
      $prev_offset=$scope_offset-1;
      $next_offset=$scope_offset+1;
      if ($scope=="this_week") {
         $start_of_week = get_option('start_of_week');
         $eme_date_obj->setWeekStartDay($start_of_week);
         $eme_date_obj->modifyWeeks($scope_offset);
         $limit_start=$eme_date_obj->startOfWeek()->format('Y-m-d');
         $limit_end=$eme_date_obj->endOfWeek()->format('Y-m-d');
         $scope = "$limit_start--$limit_end";
         $scope_text = eme_localized_date($limit_start." ".$eme_timezone)." -- ".eme_localized_date($limit_end." ".$eme_timezone);
         $prev_text = __('Previous week','events-made-easy');
         $next_text = __('Next week','events-made-easy');

      } elseif ($scope=="this_month") {
         // we first set the current date to the beginning of this month, otherwise the offset flips (e.g. if you're
         // on August 31 and call modifyMonths(1), it will give you October because Sept 31 doesn't exist
         $eme_date_obj->startOfMonth()->modifyMonths($scope_offset);
         $limit_start = $eme_date_obj->startOfMonth()->format('Y-m-d');
         $limit_end   = $eme_date_obj->endOfMonth()->format('Y-m-d');
         $scope = "$limit_start--$limit_end";
         $scope_text = eme_localized_date($limit_start." ".$eme_timezone,get_option('eme_show_period_monthly_dateformat'));
         $prev_text = __('Previous month','events-made-easy');
         $next_text = __('Next month','events-made-easy');

      } elseif ($scope=="this_year") {
         $eme_date_obj->modifyYears($scope_offset);
         $year=$eme_date_obj->getYear();
         $limit_start = "$year-01-01";
         $limit_end   = "$year-12-31";
         $scope = "$limit_start--$limit_end";
         $scope_text = eme_localized_date($limit_start." ".$eme_timezone,get_option('eme_show_period_yearly_dateformat'));
         $prev_text = __('Previous year','events-made-easy');
         $next_text = __('Next year','events-made-easy');

      } elseif ($scope=="today") {
         $scope = $eme_date_obj->modifyDays($scope_offset)->format('Y-m-d');
         $limit_start = $scope;
         $limit_end   = $scope;
         $scope_text = eme_localized_date($limit_start." ".$eme_timezone);
         $prev_text = __('Previous day','events-made-easy');
         $next_text = __('Next day','events-made-easy');

      } elseif ($scope=="tomorrow") {
         $scope_offset++;
         $scope = $eme_date_obj->modifyDays($scope_offset)->format('Y-m-d');
         $limit_start = $scope;
         $limit_end   = $scope;
         $scope_text = eme_localized_date($limit_start." ".$eme_timezone);
         $prev_text = __('Previous day','events-made-easy');
         $next_text = __('Next day','events-made-easy');
      }
   }
   // We request $limit+1 events, so we know if we need to show the pagination link or not.
   if ($limit==0) {
      $events = eme_get_events ( 0, $scope, $order, $limit_offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
   } else {
      $events = eme_get_events ( $limit+1, $scope, $order, $limit_offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
   }
   $events_count=count($events);
   
   if ($events_count==1 && $show_single_event) {
      $event = $events[0];
      $output =  eme_display_single_event($event['event_id']);
      return $output;
   }
   // get the paging output ready
   $pagination_top = "<div id='events-pagination-top'> ";
   $nav_hidden_class="style='visibility:hidden;'";
   if ($paging==1 && $limit>0) {
      // for normal paging and there're no events, we go back to offset=0 and try again
      if ($events_count==0) {
         $limit_offset=0;
         $events = eme_get_events ( $limit+1, $scope, $order, $limit_offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
         $events_count=count($events);
      }
      $prev_text=__('Previous page','events-made-easy');
      $next_text=__('Next page','events-made-easy');
      $page_number = floor($limit_offset/$limit) + 1;
      $this_page_url=get_permalink($post->ID);
      //$this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= remove_query_arg('eme_offset',$this_page_url);

      // we add possible fields from the filter section
      $eme_filters["eme_eventAction"]=1;
      $eme_filters["eme_cat_filter"]=1;
      $eme_filters["eme_loc_filter"]=1;
      $eme_filters["eme_city_filter"]=1;
      $eme_filters["eme_country_filter"]=1;
      $eme_filters["eme_scope_filter"]=1;
      foreach ($_REQUEST as $key => $item) {
         if (isset($eme_filters[$key])) {
            # if you selected multiple items, $item is an array, but rawurlencode needs a string
            if (is_array($item)) $item=join(',',eme_sanitize_request($item));
            $this_page_url=add_query_arg(array($key=>$item),$this_page_url);
         }
      }

      // we always provide the text, so everything stays in place (but we just hide it if needed, and change the link to empty
      // to prevent going on indefinitely and thus allowing search bots to go on for ever
      if ($events_count > $limit) {
         $forward = $limit_offset + $limit;
         $backward = $limit_offset - $limit;
         if ($backward < 0)
            $pagination_top.= "<a class='eme_nav_left' $nav_hidden_class href='#'>&lt;&lt; $prev_text</a>";
         else
            $pagination_top.= "<a class='eme_nav_left' href='".add_query_arg(array('eme_offset'=>$backward),$this_page_url)."'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' href='".add_query_arg(array('eme_offset'=>$forward),$this_page_url)."'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','events-made-easy').$page_number."</span>";
      }
      if ($events_count <= $limit && $limit_offset>0) {
         $forward = 0;
         $backward = $limit_offset - $limit;
         if ($backward < 0)
            $pagination_top.= "<a class='eme_nav_left' $nav_hidden_class href='#'>&lt;&lt; $prev_text</a>";
         else
            $pagination_top.= "<a class='eme_nav_left' href='".add_query_arg(array('eme_offset'=>$backward),$this_page_url)."'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' $nav_hidden_class href='#'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','events-made-easy').$page_number."</span>";
      }
   }
   if ($paging==1 && $limit==0) {
      $this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= remove_query_arg('eme_offset',$this_page_url);

      // we add possible fields from the filter section
      $eme_filters["eme_eventAction"]=1;
      $eme_filters["eme_cat_filter"]=1;
      $eme_filters["eme_loc_filter"]=1;
      $eme_filters["eme_city_filter"]=1;
      $eme_filters["eme_country_filter"]=1;
      $eme_filters["eme_scope_filter"]=1;
      foreach ($_REQUEST as $key => $item) {
         if (isset($eme_filters[$key])) {
            # if you selected multiple items, $item is an array, but rawurlencode needs a string
            if (is_array($item)) $item=join(',',eme_sanitize_request($item));
            $this_page_url=add_query_arg(array($key=>$item),$this_page_url);
         }
      }

      // to prevent going on indefinitely and thus allowing search bots to go on for ever,
      // we stop providing links if there are no more events left
      $older_events=eme_get_events ( 1, "--".$limit_start, $order, 0, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
      $newer_events=eme_get_events ( 1, "++".$limit_end, $order, 0, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
      if (count($older_events)>0)
         $pagination_top.= "<a class='eme_nav_left' href='".add_query_arg(array('eme_offset'=>$prev_offset),$this_page_url) ."'>&lt;&lt; $prev_text</a>";
      else
         $pagination_top.= "<a class='eme_nav_left' $nav_hidden_class href='#'>&lt;&lt; $prev_text</a>";

      if (count($newer_events)>0)
         $pagination_top.= "<a class='eme_nav_right' href='".add_query_arg(array('eme_offset'=>$next_offset),$this_page_url) ."'>$next_text &gt;&gt;</a>";
      else
         $pagination_top.= "<a class='eme_nav_right' $nav_hidden_class href='#'>$next_text &gt;&gt;</a>";

      $pagination_top.= "<span class='eme_nav_center'>$scope_text</span>";
   }
   $pagination_top.= "</div>";
   $pagination_bottom = str_replace("events-pagination-top","events-pagination-bottom",$pagination_top);

   $output = "";
   if ($events_count>0) {
      # we first need to determine on which days events occur
      # this code is identical to that in eme_calendar.php for "long events"
	   $eventful_days= array();
	   $i=1;
	   foreach ( $events as $event ) {
		   // we requested $limit+1 events, so we need to break at the $limit, if reached
		   if ($limit>0 && $i>$limit)
			   break;
		   $eme_date_obj_tmp=new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
		   $eme_date_obj_end=new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
		   if ($eme_date_obj_end->lessThan($eme_date_obj_tmp))
			   $eme_date_obj_end=$eme_date_obj_tmp->copy();
		   if ($long_events) {
			   //Show events on every day that they are still going on
			   while( $eme_date_obj_tmp->lessOrEqualTo($eme_date_obj_end)) {
				   $event_eventful_date = $eme_date_obj_tmp->getDate();
				   if(isset($eventful_days[$event_eventful_date]) &&  is_array($eventful_days[$event_eventful_date]) ) {
					   $eventful_days[$event_eventful_date][] = $event;
				   } else {
					   $eventful_days[$event_eventful_date] = array($event);
				   }
				   $eme_date_obj_tmp->addOneDay();
			   }
		   } else {
			   //Only show events on the day that they start
			   if ( isset($eventful_days[$event['event_start_date']]) && is_array($eventful_days[$event['event_start_date']]) ) {
				   $eventful_days[$event['event_start_date']][] = $event;
			   } else {
				   $eventful_days[$event['event_start_date']] = array($event);
			   }
		   }
		   $i++;
	   }

      if (! empty ( $showperiod )) {
         # now that we now the days on which events occur, loop through them
         $curyear="";
         $curmonth="";
         $curday="";
         foreach($eventful_days as $day_key => $day_events) {
            $eme_date_obj=new ExpressiveDate($day_key,$eme_timezone);
            list($theyear, $themonth, $theday) = explode('-', $eme_date_obj->getDate());
            if ($showperiod == "yearly" && $theyear != $curyear) {
               $output .= "<li class='eme_period'>".eme_localized_date ($day_key." ".$eme_timezone,get_option('eme_show_period_yearly_dateformat'))."</li>";
            } elseif ($showperiod == "monthly" && "$theyear$themonth" != "$curyear$curmonth") {
               $output .= "<li class='eme_period'>".eme_localized_date ($day_key." ".$eme_timezone,get_option('eme_show_period_monthly_dateformat'))."</li>";
            } elseif ($showperiod == "daily" && "$theyear$themonth$theday" != "$curyear$curmonth$curday") {
               $output .= "<li class='eme_period'>";
               if ($link_showperiod) {
                   // if there is a specific class filter for the urls, do it
                  $class="";
                  if (has_filter('eme_calday_url_class_filter')) $class=apply_filters('eme_calday_url_class_filter',$class);
                  if (!empty($class)) $class="class='$class'";

                  $eme_link=eme_calendar_day_url($theyear."-".$themonth."-".$theday);
                  $output .= "<a href='$eme_link' $class>".eme_localized_date ($day_key." ".$eme_timezone)."</a>";
               } else {
                  $output .= eme_localized_date ($day_key." ".$eme_timezone);
               }
               $output .= "</li>";
            }
            $curyear=$theyear;
            $curmonth=$themonth;
            $curday=$theday;
            foreach($day_events as $event) {
               $output .= eme_replace_placeholders ( $format, $event );
            }
         }
      } else {
         $i=1;
         foreach($eventful_days as $day_key => $day_events) {
            foreach($day_events as $event) {
               $output .= eme_replace_placeholders ( $format, $event );
            }
         }
      } // end if (! empty ( $showperiod )) {

      //Add headers and footers to output
      $empty_event = eme_new_event();
      $output =  eme_replace_placeholders($format_header, $empty_event) .  $output . eme_replace_placeholders($format_footer,$empty_event);
   } else {
      if (empty($no_events_message))
         $no_events_message=get_option('eme_no_events_message');
      $output = "<span id='events-no-events'>" . $no_events_message . "</span>";
   }

   // add the pagination if needed
   if ($paging==1 && $events_count>0)
   	$output = $pagination_top . $output . $pagination_bottom;
  
   // see how to return the output
   if ($echo)
      echo $output;
   else
      return $output;
}

function eme_get_events_list_shortcode($atts) {
   $eme_event_list_number_events=get_option('eme_event_list_number_items' );
   extract ( shortcode_atts ( array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'category' => '', 'showperiod' => '', 'author' => '', 'contact_person' => '', 'paging' => 0, 'long_events' => 0, 'location_id' => 0, 'user_registered_only' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '', 'show_recurrent_events_once' => 0, 'template_id' => 0, 'template_id_header' => 0, 'template_id_footer' => 0, 'no_events_message' => '' ), $atts ) );

   // the filter list overrides the settings
   if (isset($_REQUEST['eme_eventAction']) && $_REQUEST['eme_eventAction'] == 'filter') {
      if (isset($_REQUEST['eme_scope_filter']) && !empty($_REQUEST['eme_scope_filter'])) {
         $scope = eme_sanitize_request($_REQUEST['eme_scope_filter']);
      }

      if (isset($_REQUEST['eme_loc_filter']) && !empty($_REQUEST['eme_loc_filter'])) {
         if (is_array($_REQUEST['eme_loc_filter']))
            $location_id=join(',',eme_sanitize_request($_REQUEST['eme_loc_filter']));
         else
            $location_id=eme_sanitize_request($_REQUEST['eme_loc_filter']);
      }
      if (isset($_REQUEST['eme_city_filter']) && !empty($_REQUEST['eme_city_filter'])) {
         $cities=eme_sanitize_request($_REQUEST['eme_city_filter']);
         if (empty($location_id))
            $location_id = join(',',eme_get_city_location_ids($cities));
         else
            $location_id .= ",".join(',',eme_get_city_location_ids($cities));
      }
      if (isset($_REQUEST['eme_country_filter'])) {
         $countries=eme_sanitize_request($_REQUEST['eme_country_filter']);
         if (empty($location_id))
            $location_id = join(',',eme_get_country_location_ids($countries));
         else
            $location_id .= ",".join(',',eme_get_country_location_ids($countries));
      }
      if (isset($_REQUEST['eme_cat_filter']) && !empty($_REQUEST['eme_cat_filter'])) {
         if (is_array($_REQUEST['eme_cat_filter']))
            $category=join(',',eme_sanitize_request($_REQUEST['eme_cat_filter']));
         else
            $category=eme_sanitize_request($_REQUEST['eme_cat_filter']);
      }
   }

   // if format is given as argument, sometimes people need url-encoded strings inside so wordpress doesn't get confused, so we decode them here again
   $format = urldecode($format);
   // for format: sometimes people want to give placeholders as options, but when using the shortcode inside
   // another (e.g. when putting[eme_events format="#_EVENTNAME"] inside the "display single event" setting,
   // the replacement of the placeholders happens too soon (placeholders get replaced first, before any other
   // shortcode is interpreted). So we add the option that people can use "#OTHER_", and we replace this with
   // "#_" here
   $format = preg_replace('/#OTHER/', "#", $format);
   $result = eme_get_events_list ( $limit,$scope,$order,$format,'','',0,$category,$showperiod,$long_events,$author,$contact_person,$paging,$location_id,$user_registered_only,$show_ongoing,$link_showperiod,$notcategory,$show_recurrent_events_once,$template_id,$template_id_header,$template_id_footer,$no_events_message);
   return $result;
}

function eme_display_single_event($event_id,$template_id=0,$ignore_url=0) {
   $page_body="";
   $event = eme_get_event ( intval($event_id) );
   // also take into account the generic option for using the external url
   if (!$ignore_url) $ignore_url=!get_option('eme_use_external_url');
   if ($event['event_url'] != '' && !$ignore_url) {
      // url not empty, so we redirect to it
      $page_body = '<script type="text/javascript">window.location.href="'.$event['event_url'].'";</script>';
      return $page_body;
   } elseif ($template_id) {
      $single_event_format= eme_get_template_format($template_id);
   } else {
      if (!empty($event['event_single_event_format']))
         $single_event_format = $event['event_single_event_format'];
      elseif ($event['event_properties']['event_single_event_format_tpl']>0)
         $single_event_format = eme_get_template_format($event['event_properties']['event_single_event_format_tpl']);
      else
         $single_event_format = get_option('eme_single_event_format' );
   }
   if ((!is_user_logged_in() && $event['event_status'] == STATUS_PUBLIC) || is_user_logged_in())
      $page_body = eme_replace_placeholders ($single_event_format, $event);
   return $page_body;
}

function eme_display_single_event_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>'','template_id'=>0,'ignore_url'=>0), $atts ) );
   return eme_display_single_event($id,$template_id,$ignore_url);
}

function eme_get_events_page_shortcode($atts) {
   // we don't want just the url, but the clickable link by default for the shortcode
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => get_option ( 'eme_events_page_title' ) ), $atts ) );
   $result = eme_get_events_page ( $justurl,$text );
   return $result;
}

// API function
function eme_are_events_available($scope = "future",$order = "ASC", $location_id = "", $category = '', $author = '', $contact_person = '') {
   if ($scope == "")
      $scope = "future";
   $events = eme_get_events ( 1, $scope, $order, 0, $location_id, $category, $author, $contact_person);
   
   if (empty ( $events ))
      return FALSE;
   else
      return TRUE;
}

function eme_count_events_older_than($scope) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_start_date<'".$scope."'";
   return $wpdb->get_var($sql);
}

function eme_count_events_newer_than($scope) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_end_date>'".$scope."'";
   return $wpdb->get_var($sql);
}

function eme_search_events($name,$scope) {
   global $wpdb, $eme_timezone;
   $table = $wpdb->prefix.EVENTS_TBNAME;
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $start_of_week = get_option('start_of_week');
   $eme_date_obj->setWeekStartDay($start_of_week);
   $today = $eme_date_obj->getDate();
   // the LIKE needs "%", but for prepare to work, we need to escape % using %%
   // and then the prepare is a sprintf, so we need %s for the search string too
   // This results in 3 %-signs, but it is what it is :-)
   if ($scope == 'past')
	   $condition = " AND event_start_date < '$today'";
   elseif ($scope == 'future')
	   $condition = " AND event_start_date >= '$today'";
   else
	   $condition="";
   $query = "SELECT * FROM $table WHERE ((event_name LIKE '%%%s%%') OR
           (event_notes LIKE '%%%s%%')) $condition ORDER BY event_start_date";

   $sql=$wpdb->prepare($query,$name,$name);
   return $wpdb->get_results ( $sql, ARRAY_A );
}

// main function querying the database event table
function eme_get_events($o_limit=0, $scope = "future", $order = "ASC", $o_offset = 0, $location_id = "", $category = "", $author = "", $contact_person = "",  $show_ongoing=1, $notcategory = "", $show_recurrent_events_once=0, $extra_conditions = "", $count=0) {
   global $wpdb, $eme_timezone;

   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME;

   if (strpos ( $o_limit, "=" )) {
      // allows the use of arguments
      $defaults = array ('o_limit' => 0, 'scope' => 'future', 'order' => 'ASC', 'o_offset' => 0, 'location_id' => '', 'category' => '', 'author' => '', 'contact_person' => '', 'show_ongoing'=>1, 'notcategory' => '', 'show_recurrent_events_once'=>0, 'extra_conditions' => '' );
      
      $r = wp_parse_args ( $o_limit, $defaults );
      extract ( $r );
   }
   if ($o_limit === "") {
      $o_limit = get_option('eme_event_list_number_items' );
   }
   if ($o_limit > 0) {
      $limit = "LIMIT ".intval($o_limit);
   } else {
      $limit="";
   }
   if ($o_offset >0) {
      if ($o_limit == 0) {
          $limit = "LIMIT ".intval($o_offset);
      }
      $offset = "OFFSET ".intval($o_offset);
   } else {
      $offset="";
   }

   if ($count)
      $column='COUNT(*)';
   else
      $column='*';

   // we can provide our own order statements
   $orderby="";
   if ($order == "ASC" || $order=="DESC")
      $orderby= "ORDER BY event_start_date $order, event_start_time $order, event_name $order";
   elseif (!empty($order) && preg_match("/^[\w_\-\, ]+$/",$order))
      $orderby= "ORDER BY $order";

   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $start_of_week = get_option('start_of_week');
   $eme_date_obj->setWeekStartDay($start_of_week);
   $today = $eme_date_obj->getDate();
   $this_time = $eme_date_obj->getTime();
   $this_datetime = $eme_date_obj->getDateTime();
   
   $conditions = array ();
   // extra sql conditions we put in front, most of the time this is the most
   // effective place
   if ($extra_conditions != "") {
      $conditions[] = $extra_conditions;
   }

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions[] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions[] = "event_status=".STATUS_PUBLIC;
      }
      if (get_option('eme_rsvp_hide_full_events')) {
         // COALESCE is used in case the SUM returns NULL
         // this is a correlated subquery, so the FROM clause should specify events_table again, so it will search in the outer query for events_table.event_id
         $conditions[] = "(event_rsvp=0 OR (event_rsvp=1 AND event_seats > (SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE $bookings_table.event_id = $events_table.event_id)))";
      }
      if (get_option('eme_rsvp_hide_rsvp_ended_events')) {
         $conditions[] = "(event_rsvp=0 OR (event_rsvp=1 AND (event_end_date < '$today' OR UNIX_TIMESTAMP(CONCAT(event_start_date,' ',event_start_time))-rsvp_number_days*60*60*24-rsvp_number_hours*60*60 > UNIX_TIMESTAMP()) ))";
      }
   }

   if (preg_match ( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope )) {
      //$conditions[] = " event_start_date like '$scope'";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date LIKE '$scope') OR (event_start_date <= '$scope' AND event_end_date >= '$scope'))";
      else
         $conditions[] = " (event_start_date LIKE '$scope') ";
   } elseif (preg_match ( "/^--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
         $limit_start = $matches[1];
         if ($show_ongoing)
            $conditions[] = " (event_start_date < '$limit_start') ";
         else
            $conditions[] = " (event_end_date < '$limit_start') ";
   } elseif (preg_match ( "/^\+\+([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
         $limit_start = $matches[1];
         $conditions[] = " (event_start_date > '$limit_start') ";
   } elseif (preg_match ( "/^0000-([0-9]{2})$/", $scope, $matches )) {
      $month=$matches[1];
      $eme_date_obj->setMonth($month);
      $year=$eme_date_obj->getYear();
      $number_of_days_month=$eme_date_obj->getDaysInMonth();

      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_week") {
      // this comes from global wordpress preferences
      $start_of_week = get_option('start_of_week');
      $eme_date_obj->setWeekStartDay($start_of_week);
      $limit_start=$eme_date_obj->startOfWeek()->format('Y-m-d');
      $limit_end=$eme_date_obj->endOfWeek()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "next_week") {
      // this comes from global wordpress preferences
      $start_of_week = get_option('start_of_week');
      $eme_date_obj->setWeekStartDay($start_of_week);
      $eme_date_obj->addOneWeek();
      $limit_start=$eme_date_obj->startOfWeek()->format('Y-m-d');
      $limit_end=$eme_date_obj->endOfWeek()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month") {
      $limit_start=$eme_date_obj->startOfMonth()->format('Y-m-d');
      $limit_end=$eme_date_obj->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "next_month") {
      $eme_date_obj->startOfMonth()->addOneMonth();
      $limit_start=$eme_date_obj->startOfMonth()->format('Y-m-d');
      $limit_end=$eme_date_obj->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year") {
      $year=$eme_date_obj->getYear();
      $limit_start = "$year-01-01";
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "next_year") {
      $year=$eme_date_obj->getYear()+1;
      $limit_start = "$year-01-01";
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$matches[2];
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--today$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^today--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$today;
      $limit_end=$matches[1];
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\+(\d+)d$/", $scope, $matches )) {
      $days=$matches[1];
      $limit_start = $today;
      $limit_end=$eme_date_obj->addDays($days)->getDate();
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\-(\d+)d$/", $scope, $matches )) {
      $days=$matches[1];
      $limit_start=$eme_date_obj->minusDays($days)->getDate();
      $limit_end = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^(\-?\+?\d+)d--(\-?\+?\d+)d$/", $scope, $matches )) {
      $day1=$matches[1];
      $day2=$matches[2];
      $limit_start=$eme_date_obj->copy()->modifyDays($day1)->getDate();
      $limit_end=$eme_date_obj->copy()->modifyDays($day2)->getDate();
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^relative\-(\d+)d--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $days=$matches[1];
      $limit_end=$matches[2];
      $eme_date_obj->setTimestampFromString($limit_end." ".$eme_timezone);
      $limit_start=$eme_date_obj->minusDays($days)->getDate(). " ".$eme_timezone;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--relative\+(\d+)d$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $days=$matches[2];
      $eme_date_obj->setTimestampFromString($limit_start." ".$eme_timezone);
      $limit_end=$eme_date_obj->addDays($days)->getDate(). " ".$eme_timezone;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\+(\d+)m$/", $scope, $matches )) {
      $months_in_future=$matches[1]++;
      $limit_start= $eme_date_obj->startOfMonth()->format('Y-m-d');
      $eme_date_obj->addMonths($months_in_future);
      $limit_end = $eme_date_obj->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\-(\d+)m$/", $scope, $matches )) {
      $months_in_past=$matches[1]++;
      $limit_start = $eme_date_obj->startOfMonth()->minusMonths($months_in_past)->startOfMonth()->format('Y-m-d');
      $limit_end = $eme_date_obj->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^(\-?\+?\d+)m--(\-?\+?\d+)m$/", $scope, $matches )) {
      $months1=$matches[1];
      $months2=$matches[2];
      $limit_start = $eme_date_obj->copy()->startOfMonth()->modifyMonths($months1)->startOfMonth()->format('Y-m-d');
      $limit_end = $eme_date_obj->copy()->startOfMonth()->modifyMonths($months2)->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_week") {
      $limit_start = $today;
      $limit_end   = $eme_date_obj->endOfWeek()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_week_plus_one") {
      $limit_start = $today;
      $limit_end   = $eme_date_obj->endOfWeek()->addOneDay()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
    } elseif ($scope == "today--this_month") {
      $limit_start = $today;
      $limit_end   = $eme_date_obj->endOfMonth()->format('Y-m-d');
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_year") {
      $year=$eme_date_obj->getYear();
      $limit_start = $today;
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
    } elseif ($scope == "this_week--today") {
      $limit_start = $eme_date_obj->startOfWeek()->format('Y-m-d');
      $limit_end   = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month--today") {
      $limit_start = $eme_date_obj->startOfMonth()->format('Y-m-d');
      $limit_end   = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year--today") {
      $limit_start = "$year-01-01";
      $limit_end = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "tomorrow--future") {
      if ($show_ongoing)
         $conditions[] = " (event_start_date > '$today' OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
      else
         $conditions[] = " (event_start_date > '$today')";
   } elseif ($scope == "past") {
      //$conditions[] = " (event_end_date < '$today' OR (event_end_date = '$today' and event_end_time < '$this_time' )) ";
      // not taking the hour into account until we can enter timezone info as well
      if ($show_ongoing)
         $conditions[] = " event_end_date < '$today'";
      else
         $conditions[] = " event_start_date < '$today'";
   } elseif ($scope == "today") {
      if ($show_ongoing)
         $conditions[] = " (event_start_date = '$today' OR (event_start_date <= '$today' AND event_end_date >= '$today'))";
      else
         $conditions[] = " (event_start_date = '$today')";
   } elseif ($scope == "tomorrow") {
      $tomorrow = $eme_date_obj->addOneDay()->getDate();
      if ($show_ongoing)
         $conditions[] = " (event_start_date = '$tomorrow' OR (event_start_date <= '$tomorrow' AND event_end_date >= '$tomorrow'))";
      else
         $conditions[] = " (event_start_date = '$tomorrow')";
   } elseif ($scope == "ongoing") {
      // only shows ongoing events, for this we try to use the date and time, but it might be incorrect since there's no user timezone info
      $conditions[] = " (CONCAT(event_start_date,' ',event_start_time)<='$this_datetime' AND CONCAT(event_end_date,' ',event_end_time)>= '$this_datetime')";
   } else {
      if ($scope != "all")
         $scope = "future";
      if ($scope == "future") {
         //$conditions[] = " ((event_start_date = '$today' AND event_start_time >= '$this_time') OR (event_start_date > '$today') OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL) OR (event_end_date = '$today' AND event_end_time >= '$this_time'))";
         // not taking the hour into account until we can enter timezone info as well
         if ($show_ongoing)
            $conditions[] = " (event_start_date >= '$today' OR (event_end_date >= '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
         else
            $conditions[] = " (event_start_date >= '$today')";
      }
   }
   
   // when used inside a location description, you can use this_location to indicate the current location being viewed
   if ($location_id == "this_location" && get_query_var('location_id')) {
      $location_id = get_query_var('location_id');
   }

   // since we do a LEFT JOIN with the location table, the column location_id
   // appears 2 times in the sql result, so we need to specify which one to use
   if (is_numeric($location_id)) {
      if ($location_id>0)
         $conditions[] = " $events_table.location_id = $location_id";
   } elseif ($location_id == "none") {
      $conditions[] = " $events_table.location_id = ''";
   } elseif ( preg_match('/,/', $location_id) ) {
      $location_ids=explode(',', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0) {
            $location_conditions[] = " $events_table.location_id = $loc";
         } elseif ($loc == "none") {
            $location_conditions[] = " $events_table.location_id = ''";
         }
      }
      $conditions[] = "(".implode(' OR', $location_conditions).")";
   } elseif ( preg_match('/\+/', $location_id) ) {
      $location_ids=explode('+', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " $events_table.location_id = $loc";
         }
         $conditions[] = "(".implode(' AND', $location_conditions).")";
   } elseif ( preg_match('/ /', $location_id) ) {
      // url decoding of '+' is ' '
      $location_ids=explode(' ', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " $events_table.location_id = $loc";
         }
         $conditions[] = "(".implode(' AND', $location_conditions).")";
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($category)) {
         if ($category>0)
            $conditions[] = " FIND_IN_SET($category,event_category_ids)";
      } elseif ($category == "none") {
         $conditions[] = "event_category_ids = ''";
      } elseif ( preg_match('/,/', $category) ) {
         $category = explode(',', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0) {
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
            } elseif ($cat == "none") {
               $category_conditions[] = " event_category_ids = ''";
            }
         }
         $conditions[] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $category) ) {
         $category = explode('+', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      } elseif ( preg_match('/ /', $category) ) {
         // url decoding of '+' is ' '
         $category = explode(' ', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($notcategory)) {
         if ($notcategory>0)
            $conditions[] = " NOT FIND_IN_SET($notcategory,event_category_ids)";
      } elseif ($notcategory == "none") {
         $conditions[] = "event_category_ids != ''";
      } elseif ( preg_match('/,/', $notcategory) ) {
         $notcategory = explode(',', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0) {
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
            } elseif ($cat == "none") {
               $category_conditions[] = " event_category_ids != ''";
            }
         }
         $conditions[] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $notcategory) ) {
         $notcategory = explode('+', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      } elseif ( preg_match('/ /', $notcategory) ) {
         // url decoding of '+' is ' '
         $notcategory = explode(' ', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
         }
      }
   }

   // now filter the author ID
   if ($author != '' && !preg_match('/,/', $author)){
      $authinfo=get_user_by('login', $author);
      $conditions[] = " event_author = ".$authinfo->ID;
   }elseif( preg_match('/,/', $author) ){
      $authors = explode(',', $author);
      $author_conditions = array();
      foreach($authors as $authname) {
            $authinfo=get_user_by('login', $authname);
            $author_conditions[] = " event_author = ".$authinfo->ID;
      }
      $conditions[] = "(".implode(' OR ', $author_conditions).")";
   }

   // now filter the contact ID
   if ($contact_person != '' && !preg_match('/,/', $contact_person)){
      $authinfo=get_user_by('login', $contact_person);
      $conditions[] = " event_contactperson_id = ".$authinfo->ID;
   }elseif( preg_match('/,/', $contact_person) ){
      $contact_persons = explode(',', $contact_person);
      $contact_person_conditions = array();
      foreach($contact_persons as $authname) {
            $authinfo=get_user_by('login', $authname);
            $contact_person_conditions[] = " event_contactperson_id = ".$authinfo->ID;
      }
      $conditions[] = "(".implode(' OR ', $contact_person_conditions).")";
   }

   // extra conditions for authors: if we're in the admin itf, return only the events for which you have the right to change anything
   $current_userid=get_current_user_id();
   if (is_admin() && !current_user_can( get_option('eme_cap_edit_events')) && !current_user_can( get_option('eme_cap_list_events')) && current_user_can( get_option('eme_cap_author_event'))) {
      $conditions[] = "(event_author = $current_userid OR event_contactperson_id= $current_userid)";
   }
   
   $where = implode ( " AND ", $conditions );
   if ($show_recurrent_events_once) {
      if ($where != "")
         $where = " AND " . $where;
       $sql = "SELECT $column FROM $events_table LEFT JOIN $locations_table ON $events_table.location_id=$locations_table.location_id
         WHERE (recurrence_id>0 $where)
         group by recurrence_id union all
         SELECT $column FROM $events_table LEFT JOIN $locations_table ON $events_table.location_id=$locations_table.location_id
         WHERE (recurrence_id=0 $where)
         $orderby $limit $offset";
   } else {
      if ($where != "")
         $where = " WHERE " . $where;
         $sql = "SELECT $column FROM $events_table LEFT JOIN $locations_table ON $events_table.location_id=$locations_table.location_id
         $where $orderby $limit $offset";
   }
   $wpdb->show_errors = true;
   if ($count) {
      $count = $wpdb->get_var ($sql);
      return $count;
   } else {
      $events = $wpdb->get_results ( $sql, ARRAY_A );
      $inflated_events = array ();
      if (! empty ( $events )) {
         //$wpdb->print_error(); 
         foreach ( $events as $this_event ) {
            $this_event = eme_get_extra_event_data($this_event);
            array_push ( $inflated_events, $this_event );
         }
         if (has_filter('eme_event_list_filter')) $inflated_events=apply_filters('eme_event_list_filter',$inflated_events);
      }
      return $inflated_events;
   }
}

function eme_get_event($event_id) {
   global $wpdb;

   if (!$event_id) {
      return eme_new_event();
   }

   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   $event_id = intval($event_id);

   $sql = "SELECT * FROM $events_table WHERE event_id = $event_id";

   //$wpdb->show_errors(true);
   $event = $wpdb->get_row ( $sql, ARRAY_A );
   //$wpdb->print_error();
   if (!$event) {
         return eme_new_event();
   }
   $event = eme_get_extra_event_data($event);
   return $event;
}


function eme_get_event_arr($event_ids) {
   global $wpdb;

   // remove possible empty elements
   $event_ids = array_filter($event_ids,'strlen');
   if (empty($event_ids)) {
      return;
   }

   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   $conditions[] = "event_id IN (".join(',',$event_ids).")";

   // in the frontend and not logged in, only show public events
   // since this function is only called from the frontend for the multibooking form, we can drop the is_admin, but hey ...
   if (!is_admin() && !is_user_logged_in()) {
      $conditions[] = "event_status=".STATUS_PUBLIC;
   }
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;

   // the 'order by' is of course only useful if the event_id argument for the function was an array of event id's
   $sql = "SELECT * FROM $events_table
      $where ORDER BY event_start_date ASC, event_start_time ASC";

   $events = $wpdb->get_results ( $sql, ARRAY_A );
   foreach ( $events as $key=>$event ) {
      $events[$key] = eme_get_extra_event_data($event);
   }
   return $events;
}

function eme_get_extra_event_data($event) {
   if ($event['event_end_date'] == "") {
      $event['event_end_date'] = $event['event_start_date'];
   }
      
   if (is_serialized($event['event_attributes']))
         $event['event_attributes'] = @unserialize($event['event_attributes']);
   $event['event_attributes'] = (!is_array($event['event_attributes'])) ?  array() : $event['event_attributes'] ;

   if (is_serialized($event['event_properties']))
      $event['event_properties'] = @unserialize($event['event_properties']);
   $event['event_properties'] = (!is_array($event['event_properties'])) ?  array() : $event['event_properties'] ;
   $event['event_properties'] = eme_init_event_props($event['event_properties']);

   // don't forget the images (for the older events that didn't use the wp gallery)
   if (empty($event['event_image_id']) && empty($event['event_image_url']))
      $event['event_image_url'] = eme_image_url_for_event($event);
   if (has_filter('eme_event_filter')) $event=apply_filters('eme_event_filter',$event);
   return $event;
}

function eme_events_table($message="") {
   global $eme_timezone;

   if (!empty($message)) {
         echo "<div id='message' class='notice is-dismissible'><p>".eme_trans_esc_html($message)."</p></div>";
   }
   $scope_names = array ();
   $scope_names['past'] = __ ( 'Past events', 'events-made-easy');
   $scope_names['all'] = __ ( 'All events', 'events-made-easy');
   $scope_names['future'] = __ ( 'Future events', 'events-made-easy');

   $event_status_array = eme_status_array ();
   $categories = eme_get_categories();

?>

<div class="wrap nosubsub">
<div id="poststuff">
   <div id="icon-edit" class="icon32">
   <br />
   </div>

   <h1><?php _e('Add a new event', 'events-made-easy') ?></h1>
   <div class="wrap">
      <form id="locations-filter" method="post" action="<?php echo admin_url("admin.php?page=eme-manager"); ?>">
         <input type="hidden" name="eme_admin_action" value="add_new_event" />
         <input type="submit" class="button-primary" name="submit" value="<?php _e('Add event', 'events-made-easy');?>" />
      </form>
   </div>

   <h1><?php _e('Events', 'events-made-easy') ?></h1>
   <form method='post' action="#">
   <select id="scope" name="scope">
   <?php
   foreach ( $scope_names as $key => $value ) {
      $selected = "";
      if ($key == 'future')
         $selected = "selected='selected'";
      echo "<option value='$key' $selected>$value</option>  ";
   }
   ?>
   </select>
   <select id="status" name="status">
      <option value="0"><?php _e('Event Status','events-made-easy'); ?></option>
      <?php
      if (isset($_GET['status']))
         $get_status=intval($_GET['status']);
      else
	 $get_status=0;
      foreach($event_status_array as $event_status_key => $event_status_value) {
	 echo "<option value='$event_status_key' ".selected($get_status,$event_status_key)."> $event_status_value</option>";
      }
      ?>
   </select>
   <select id="category" name="category">
   <option value='0'><?php _e('All categories','events-made-easy'); ?></option>
   <?php
   foreach ( $categories as $category) {
      echo "<option value='".$category['category_id']."'>".$category['category_name']."</option>";
   }
   ?>
   </select>
   <input type="text" name="search_name" id="search_name" placeholder="<?php _e('Event name','events-made-easy'); ?>" size=10 />
   <input id="eme_localized_start_date" type="text" name="eme_localized_start_date" value="" style="background: #FCFFAA;" readonly="readonly" placeholder="<?php _e('Filter on startdate','events-made-easy'); ?>" size=15 />
   <input id="search_start_date" type="hidden" name="search_start_date" value="" />
   <button id="EventsLoadRecordsButton" class="button-secondary action"><?php _e('Filter events','events-made-easy'); ?></button>
   </form>
   <form action="#" method="post">
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteEvents"><?php _e ( 'Delete selected events','events-made-easy'); ?></option>
   <option value="deleteRecurrence"><?php _e ( 'Delete selected recurrent events','events-made-easy'); ?></option>
   <option value="publicEvents"><?php _e ( 'Publish selected events','events-made-easy'); ?></option>
   <option value="privateEvents"><?php _e ( 'Make selected events private','events-made-easy'); ?></option>
   <option value="draftEvents"><?php _e ( 'Make selected events draft','events-made-easy'); ?></option>
   </select>
   <button id="EventsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="EventsTableContainer"></div>
</div>
</div>
<?php
}

function eme_event_form($event, $title, $event_ID=0) {
   
   global $plugin_page, $eme_timezone;
   $event_status_array = eme_status_array ();
   $currency_array = eme_currency_array();
   $nonce_field = wp_nonce_field('eme_events','eme_admin_nonce',false,false);

   // let's determine if it is a new event, handy
   // or, in case of validation errors, $event can already contain info, but no $event_ID
   // so we create a new event and copy over the info into $event for the elements that do not exist
   if (! $event_ID) {
      $is_new_event=1;
      $new_event=eme_new_event();
      $event = array_replace_recursive($new_event,$event);
   } else {
      $is_new_event=0;
   }

   // some checks and unserialize if needed
   $event = eme_get_extra_event_data($event);

   $show_recurrent_form = 0;
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_recurrence") {
      $pref = "recurrence";
      $form_destination = "admin.php?page=eme-manager&amp;eme_admin_action=update_recurrence&amp;recurrence_id=" . $event_ID;
      $show_recurrent_form = 1;
   } else {
      $pref = "event";
      // even for new events, after the 'save' button is clicked, we want to go to the list of events
      // so we use page=eme-manager too, not page=eme-new_event
      if ($is_new_event)
         $form_destination = "admin.php?page=eme-manager&amp;eme_admin_action=insert_event";
      else
         $form_destination = "admin.php?page=eme-manager&amp;eme_admin_action=update_event&amp;event_id=" . $event_ID;

      if (isset($event['recurrence_id']) && $event['recurrence_id']) {
         # editing a single event of an recurrence: don't show the recurrence form
         $show_recurrent_form = 0;
      } else {
         # for single non-recurrent events: we show the form, so we can make it recurrent if we want to
         # Also: in the case that bookings already took place for this event, we don't allow the conversion
         # to a recurrent event, as that would cause the bookings to be lost
         $booking_ids=eme_get_bookingids_for($event_ID);
         if (empty($booking_ids))
            $show_recurrent_form = 1;
         else
            $show_recurrent_form = 2;
      }

      // we need to set the recurrence fields, otherwise we get warnings in the meta box functions
      $event['recurrence_freq'] = '';
      $event['recurrence_start_date'] = $event['event_start_date'];
      $event['recurrence_end_date'] = $event['event_end_date'];
      $event['recurrence_interval'] = '';
      $event['recurrence_byweekno'] = '';
      $event['holidays_id'] = 0;
      $event['recurrence_byday'] = '';
      $event['recurrence_specific_days'] = '';
   }
   
   if (!isset($event['recurrence_start_date'])) $event['recurrence_start_date']=$event['event_start_date'];
   if (!isset($event['recurrence_end_date'])) $event['recurrence_end_date']=$event['event_end_date'];

   $event_number_spaces=$event['event_seats'];
   $registration_wp_users_only = ($event['registration_wp_users_only']) ? "checked='checked'" : "";
   $registration_requires_approval = ($event['registration_requires_approval']) ? "checked='checked'" : "";

   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);

// the next javascript will fill in the values for localized-start-date, ... form fields and jquery datepick will fill in also to "to_submit" form fields
   ?>

<script type="text/javascript">
   jQuery(document).ready( function() {
   var dateFormat = jQuery("#localized-start-date").datepick( "option", "dateFormat" );

   var loc_start_date = jQuery.datepick.newDate(<?php echo $eme_date_obj->setTimestampFromString($event['event_start_date']." ".$eme_timezone)->format('Y,n,j'); ?>);
   jQuery("#localized-start-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, loc_start_date));

   var loc_end_date = jQuery.datepick.newDate(<?php echo $eme_date_obj->setTimestampFromString($event['event_end_date']." ".$eme_timezone)->format('Y,n,j'); ?>);
   jQuery("#localized-end-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, loc_end_date));
   <?php if ($pref == "recurrence" && $event['recurrence_freq'] == 'specific') { ?>
      var mydates = [];
      <?php foreach (explode(',',$event['recurrence_specific_days']) as $specific_day) { ?>
	      mydates.push(jQuery.datepick.newDate(<?php echo $eme_date_obj->setTimestampFromString($specific_day." ".$eme_timezone)->format('Y,n,j'); ?>));
      <?php } ?>
      jQuery("#localized-rec-start-date").datepick("setDate", mydates);
   <?php } else { ?>
      var rec_start_date = jQuery.datepick.newDate(<?php echo $eme_date_obj->setTimestampFromString($event['recurrence_start_date']." ".$eme_timezone)->format('Y,n,j'); ?>);
      jQuery("#localized-rec-start-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, rec_start_date));
   <?php } ?>
      var rec_end_date = jQuery.datepick.newDate(<?php echo $eme_date_obj->setTimestampFromString($event['recurrence_end_date']." ".$eme_timezone)->format('Y,n,j'); ?>);
   jQuery("#localized-rec-end-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, rec_end_date));
 });
</script>

   <div class="wrap">
   <form id="eventForm" name="eventForm" method="post" enctype="multipart/form-data" action="<?php echo $form_destination; ?>">
   <?php echo $nonce_field; ?>
         <div id="icon-events" class="icon32"><br /></div>
         <h1><?php echo eme_trans_esc_html($title); ?></h1>
         <?php
         if ($event['recurrence_id']) {
            ?>
         <p id='recurrence_warning'>
            <?php
               if (isset ( $_GET['eme_admin_action'] ) && ($_GET['eme_admin_action'] == 'edit_recurrence')) {
                  _e('WARNING: This is a recurring event.','events-made-easy');
                  echo "<br />";
                  _e('Modifying these data all the events linked to this recurrence will be rescheduled','events-made-easy');
               
               } else {
                  _e('WARNING: This is a recurring event.','events-made-easy');
                  echo "<br />";
                  _e('If you change these data and save, this will become an independent event.', 'events-made-easy');
                  echo "<br /> <a href='".wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_recurrence&amp;recurrence_id=".$event['recurrence_id']),'eme_events','eme_admin_nonce')."'>";
                  _e('Edit Recurrence','events-made-easy');
                  echo "</a>";
               }
               ?>
         </p>
         <?php
         }
         ?>
         <div id="poststuff">
         <div id="post-body" class="metabox-holder columns-2">
            <!-- MAIN -->
            <div id="post-body-content">
               <?php 
               $templates_array=eme_get_templates_array_by_id('event');
		?>
<div id="event-tabs" style="display: none;">
  <ul>
    <li><a href="#tab-eventdetails"><?php _e('Event','events-made-easy');?></a></li>
    <li><a href="#tab-locationdetails"><?php _e('Location','events-made-easy');?></a></li>
<?php if(get_option('eme_rsvp_enabled')) : ?>
    <li><a href="#tab-rsvp"><?php _e('RSVP','events-made-easy');?></a></li>
<?php endif; ?>
    <li><a href="#tab-mailformats"><?php _e('Mail formats','events-made-easy');?></a></li>
<?php if(get_option('eme_attributes_enabled')) : ?>
    <li><a href="#tab-eventattributes"><?php _e('Attributes','events-made-easy');?></a></li>
<?php endif; ?>
  </ul>
  <div id="tab-eventdetails">
<?php
eme_meta_box_div_event_name($event);
eme_meta_box_div_event_date($event);
eme_meta_box_div_recurrence_date($event);
eme_meta_box_div_event_page_title_format($event,$templates_array);
eme_meta_box_div_event_single_event_format($event,$templates_array);
eme_meta_box_div_event_notes($event);
eme_meta_box_div_event_image($event);
eme_meta_box_div_event_url($event);
?>
  </div>
  <div id="tab-locationdetails">
<?php
eme_meta_box_div_location_name($event);
?>
  </div>
<?php if(get_option('eme_rsvp_enabled')) : ?>
  <div id="tab-rsvp">
<?php
eme_meta_box_div_event_rsvp($event);
$templates_array=eme_get_templates_array_by_id('rsvpform');
if (isset($event['event_properties']['rsvp_dyndata']))
   $eme_data = $event['event_properties']['rsvp_dyndata'];
else
   $eme_data=array();
eme_dyndata_adminform($eme_data,$templates_array);
eme_meta_box_div_event_registration_form_format($event,$templates_array);
eme_meta_box_div_event_registration_recorded_ok_html($event,$templates_array);
eme_meta_box_div_event_cancel_form_format($event,$templates_array);
?>
  </div>
<?php endif; ?>
  <div id="tab-mailformats">
<?php
$templates_array=eme_get_templates_array_by_id('rsvpmail');
eme_meta_box_div_event_contactperson_email_body($event,$templates_array);
eme_meta_box_div_event_respondent_email_body($event,$templates_array);
eme_meta_box_div_event_registration_pending_email_body($event,$templates_array);
eme_meta_box_div_event_registration_updated_email_body($event,$templates_array);
eme_meta_box_div_event_registration_cancelled_email_body($event,$templates_array);
eme_meta_box_div_event_registration_denied_email_body($event,$templates_array);
eme_meta_box_div_event_registration_paid_email_body($event,$templates_array);
?>
  </div>
<?php if(get_option('eme_attributes_enabled')) : ?>
  <div id="tab-eventattributes">
<?php
eme_meta_box_div_event_attributes($event);
?>
  </div>
<?php endif; ?>
</div> <!-- end event-tabs -->

               <p class="submit">
                  <?php if ($is_new_event) { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Save' , 'events-made-easy'); ?> &raquo;" />
                  <?php } else { 
                     $delete_button_text=__ ( 'Are you sure you want to delete this event?', 'events-made-easy');
                     $deleteRecurrence_button_text=__ ( 'Are you sure you want to delete this recurrence?', 'events-made-easy');
                  ?>
                     <?php if ($pref == "recurrence") { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Update' , 'events-made-easy'); ?> &raquo;" />
                     <?php } else { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Update' , 'events-made-easy'); ?> &raquo;" />
                     <?php } ?>
                     <input type="submit" class="button-primary" id="event_delete_button" name="event_delete_button" value="<?php _e ( 'Delete Event', 'events-made-easy'); ?> &raquo;" onclick="return areyousure('<?php echo $delete_button_text; ?>');" />
                     <?php if ($event['recurrence_id']) { ?>
                     <input type="submit" class="button-primary" id="event_deleteRecurrence_button" name="event_deleteRecurrence_button" value="<?php _e ( 'Delete Recurrence', 'events-made-easy'); ?> &raquo;" onclick="return areyousure('<?php echo $deleteRecurrence_button_text; ?>');" />
                     <?php } ?> 
                  <?php } ?>
               </p>
            </div>
            <!-- END OF MAIN -->
            <!-- SIDEBAR -->
            <div id="postbox-container-1" class="postbox-container">
               <div id='side-sortables' class="meta-box-sortables ui-sortable">
                  <?php if(current_user_can( get_option('eme_cap_author_event'))) { ?>
                  <!-- status postbox -->
                  <div class="postbox" id="eme_statusdiv">
                     <h2 class='hndle'><span>
                        <?php _e ( 'Event Status', 'events-made-easy'); ?>
                        </span></h2>
                     <div class="inside">
                        <p><?php _e('Status','events-made-easy'); ?>
                        <select id="status" name="event_status">
                        <?php
                           foreach ( $event_status_array as $key=>$value) {
                              if ($event['event_status'] && ($event['event_status']==$key)) {
                                 $selected = "selected='selected'";
                              } else {
                                 $selected = "";
                              }
                              echo "<option value='$key' $selected>$value</option>";
                           }
                        ?>
                        </select><br />
                        <?php
                           _e('Private events are only visible for logged in users, draft events are not visible from the front end.','events-made-easy');
                        ?>
                        </p>
                     </div>
                  </div>
                  <?php } ?>

                  <?php if($event['event_author']) : ?>
                  <!-- author postbox -->
                  <div class="postbox" id="eme_authordiv">
                     <h2 class='hndle'><span>
                        <?php _e ( 'Author', 'events-made-easy'); ?>
                        </span></h2>
                     <div class="inside">
                        <p><?php _e('Author of this event: ','events-made-easy'); ?>
                           <?php
                           $owner_user_info = get_userdata($event['event_author']);
                           echo eme_esc_html($owner_user_info->display_name);
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>

                  <!-- contact postbox -->
                  <div class="postbox" id="eme_contactdiv">
                     <h2 class='hndle'><span>
                        <?php _e ( 'Contact Person', 'events-made-easy'); ?>
                        </span></h2>
                     <div class="inside">
                        <p><?php _e('Contact','events-made-easy'); ?>
                           <?php
                           wp_dropdown_users ( array ('name' => 'event_contactperson_id', 'show_option_none' => __ ( "Event author", 'events-made-easy'), 'selected' => $event['event_contactperson_id'] ) );
                           // if it is not a new event and there's no contact person defined, then the event author becomes contact person
                           // So let's display a warning what this means if there's no author (like when submitting via the frontend submission form)
                           if (!$is_new_event && $event['event_contactperson_id']<1 && $event['event_author']<1)
                              print "<br />". __( 'Since the author is undefined for this event, any reference to the contact person (like when using #_CONTACTPERSON when sending mails), will use the admin user info.', 'events-made-easy');
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php if(get_option('eme_categories_enabled')) :?>
                  <div class="postbox" id="eme_categoriesdiv">
                     <h2 class='hndle'><span>
                        <?php _e ( 'Category', 'events-made-easy'); ?>
                        </span></h2>
                     <div class="inside">
                     <?php
                     $categories = eme_get_categories();
		     if (empty($categories)) {
                     ?>
                        <span><?php _e ( 'No categories defined.', 'events-made-easy'); ?></span>
                     <?php
		     } else {
                        foreach ( $categories as $category) {
                           if ($event['event_category_ids'] && in_array($category['category_id'],explode(",",$event['event_category_ids']))) {
                              $selected = "checked='checked'";
                           } else {
                              $selected = "";
                           }
                     ?>
            <input type="checkbox" name="event_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo eme_trans_esc_html($category['category_name']); ?><br />
                     <?php
                        } // end foreach
                     } // end if
                     ?>
                     </div>
                  </div> 
                  <?php endif; ?>
               </div>
            </div>
            <!-- END OF SIDEBAR -->
         </div>
         </div>
   </form>
   </div>
<?php
}

function eme_validate_event($event) {
   $required_fields = array("event_name" => __('The event name', 'events-made-easy'), "start_date" => __('The start date','events-made-easy'));
   $troubles = "";
   if (empty($event['event_start_date']) || $event['event_start_date']=='0000-00-00') {
      $troubles .= "<li>".$required_fields['start_date'].__(" is missing!", 'events-made-easy')."</li>";
   }
   if (empty($event['event_name'])) {
      $troubles .= "<li>".$required_fields['event_name'].__(" is missing!", 'events-made-easy')."</li>";
   }  
   if (isset($_POST['repeated_event']) && $_POST['repeated_event'] == "1" && (!isset($_POST['recurrence_end_date']) || $_POST['recurrence_end_date'] == ""))
      $troubles .= "<li>".__ ( 'Since the event is repeated, you must specify an event date for the recurrence.', 'events-made-easy')."</li>";

   if (eme_is_multi($event['event_seats']) && !eme_is_multi($event['price']))
      $troubles .= "<li>".__ ( 'Since the event contains multiple seat categories (multiseat), you must specify the price per category (multiprice) as well.', 'events-made-easy')."</li>";
   if (eme_is_multi($event['event_seats']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event['event_seats']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since the event contains multiple seat categories (multiseat), you must specify the exact same amount of prices (multiprice) as well.', 'events-made-easy')."</li>";
   }

   if (is_serialized($event['event_properties']))
         $event_properties = unserialize($event['event_properties']);
   else
         $event_properties = $event['event_properties'];
   if (eme_is_multi($event_properties['max_allowed']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event_properties['max_allowed']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since this is a multiprice event and you decided to limit the max amount of seats to book (for one booking) per price category, you must specify the exact same amount of "max seats to book" as you did for the prices.', 'events-made-easy')."</li>";
   }
   if (eme_is_multi($event_properties['min_allowed']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event_properties['min_allowed']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since this is a multiprice event and you decided to limit the min amount of seats to book (for one booking) per price category, you must specify the exact same amount of "min seats to book" as you did for the prices.', 'events-made-easy')."</li>";
   }

   if (empty($troubles)) {
      return "OK";
   } else {
      $message = __('Ach, some problems here:', 'events-made-easy')."<ul>$troubles</ul>";
      return $message; 
   }
}

// General script to make sure hidden fields are shown when containing data
function eme_admin_event_script() {
   // check if the user wants AM/PM or 24 hour notation
   // make sure that escaped characters are filtered out first
   $time_format = preg_replace('/\\\\./','',get_option('time_format'));
   $show24Hours = 'true';
   if (preg_match ( "/g|h/", $time_format ))
      $show24Hours = 'false';
   
   // jquery ui locales are with dashes, not underscores
   $datepick_locale_code = get_locale();
   $datepick_locale_code = preg_replace( "/_/","-", $datepick_locale_code );
   $use_select_for_locations = get_option('eme_use_select_for_locations')?1:0;
   $lang = eme_detect_lang();
   if (!empty($lang)) {
      $use_select_for_locations=1;
   }

?>
<script type="text/javascript">
   //<![CDATA[
var show24Hours = <?php echo $show24Hours;?>;
var datepick_locale_code = '<?php echo $datepick_locale_code;?>';
var gmap_enabled = <?php echo get_option('eme_gmap_is_active')?1:0; ?>;
var use_select_for_locations = <?php echo $use_select_for_locations; ?>;

function eme_event_page_title_format(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_event_page_title_format' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_single_event_format(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_single_event_format' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_contactperson_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_contactperson_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_respondent_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_respondent_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_recorded_ok_html(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_recorded_ok_html' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_cancelled_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_cancelled_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_denied_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_denied_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_pending_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_updated_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_updated_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_paid_email_body(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_paid_email_body' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_registration_form_format(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_registration_form_format' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

function eme_cancel_form_format(){
   var tmp_value='<?php echo rawurlencode(get_option('eme_cancel_form_format' )); ?>';
   tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
   return tmp_value;
}

//]]>
</script>

<?php
}

//function eme_admin_options_save() {
//   if (is_admin() && isset($_GET['settings-updated']) && $_GET['settings-updated']) {
//     return; 
//   }
//}

function eme_meta_box_div_event_name($event){
?>
<div id="titlediv">
   <!-- we need title for qtranslate as ID -->
   <input type="text" id="title" name="event_name" required="required" placeholder="<?php _e('Event name','events-made-easy');?>" value="<?php echo eme_esc_html($event['event_name']); ?>" />
   <br />
   <br />
   <?php if ($event['event_id'] && $event['event_name'] != "") {
      echo "<b>". __ ('Permalink: ', 'events-made-easy') . "</b>";
      echo trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_events_prefix')).$event['event_id']."/";
      $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
      $slug = untrailingslashit(eme_permalink_convert($slug));
      ?>
         <input type="text" id="slug" name="event_slug" value="<?php echo $slug; ?>" /><?php echo user_trailingslashit(""); ?>
         <?php
   }
?>
</div>
<?php
}

function eme_meta_box_div_event_date($event){
   global $eme_timezone;
   // check if the user wants AM/PM or 24 hour notation
   // make sure that escaped characters are filtered out first
   $time_format = preg_replace('/\\\\./','',get_option('time_format'));
   $hours_locale = '24';
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   if (preg_match ( "/g|h/", $time_format )) {
      $event_start_time = $eme_date_obj->setTimestampFromString($event['event_start_time']." ".$eme_timezone)->format('h:iA');
      $event_end_time = $eme_date_obj->setTimestampFromString($event['event_end_time']." ".$eme_timezone)->format('h:iA');
   } else {
      $event_start_time = $event['event_start_time'];
      $event_end_time = $event['event_end_time'];
   }
   $eme_prop_all_day_checked = ($event['event_properties']['all_day']) ? "checked='checked'" : "";
   $eme_recurrence_checked = ($event['recurrence_id']) ? "checked='checked' disabled='disabled'" : "";
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_recurrence") {
         $show_recurrent_form = 1;
   } else {
      if (isset($event['recurrence_id']) && $event['recurrence_id']) {
         # editing a single event of an recurrence: don't show the recurrence form
         $show_recurrent_form = 0;
      } else {
         # for single non-recurrent events: we show the form, so we can make it recurrent if we want to
         # Also: in the case that bookings already took place for this event, we don't allow the conversion
         # to a recurrent event, as that would cause the bookings to be lost
	 if (isset($event['event_id']))
            $booking_ids=eme_get_bookingids_for($event['event_id']);
	 else
	    $booking_ids="";
         if (empty($booking_ids))
            $show_recurrent_form = 1;
         else
            $show_recurrent_form = 2;
      }
   }
?>
<div id="div_event_date">
      <br />
      <?php
	echo "<b>". __('Event date', 'events-made-easy') . "</b>";
      ?>
      <input id="localized-start-date" type="text" name="localized_event_start_date" value="" style="background: #FCFFAA;" readonly="readonly" />
      <input id="start-date-to-submit" type="hidden" name="event_start_date" value="" />
      <input id="localized-end-date" type="text" name="localized_event_end_date" value="" style="background: #FCFFAA;" readonly="readonly" />
      <input id="end-date-to-submit" type="hidden" name="event_end_date" value="" />
      <p class="eme_smaller">
      <?php _e ( 'The event beginning and end date.', 'events-made-easy'); ?>
      <span id='event-date-recursive-explanation'>
      <br /><b>
      <?php _e ( 'In case of a recurrent event, use the beginning and end date to just indicate the duration of one event in days. The real start date is determined by the recurrence scheme being used.', 'events-made-easy'); ?>
      </b></span>
      </p>
      <br />
      <div id="time-selector">
      <?php
	echo "<b>". __('Event time', 'events-made-easy') . "</b>";
      ?>
      <input id="start-time" type="text" size="8" maxlength="8" name="event_start_time" value="<?php echo $event_start_time; ?>" />
      -
      <input id="end-time" type="text" size="8" maxlength="8" name="event_end_time" value="<?php echo $event_end_time; ?>" />
      <p class="eme_smaller">
      <?php _e ( 'The time of the event beginning and end', 'events-made-easy')?>
      </p>
      </div>
      <br />
      <input id="eme_prop_all_day" name='eme_prop_all_day' value='1' type='checkbox' <?php echo $eme_prop_all_day_checked; ?> />
      <?php _e ( 'This event lasts all day', 'events-made-easy'); ?>
      <br />
      <?php
          if ($show_recurrent_form==1) {
      ?>
             <input id="event-recurrence" type="checkbox" name="repeated_event" value="1" <?php echo $eme_recurrence_checked; ?> />
      <?php  _e ( 'Check if your event happens more than once.', 'events-made-easy');
          } else { 
             _e ( 'Bookings found for this event, so not possible to convert to a recurring event.', 'events-made-easy');
          }
      ?>
</div>
<?php
}

function eme_meta_box_div_recurrence_date($event){
   global $wp_locale;
   $freq_options = array ("daily" => __ ( 'Daily', 'events-made-easy'), "weekly" => __ ( 'Weekly', 'events-made-easy'), "monthly" => __ ( 'Monthly', 'events-made-easy'), "specific" => __('Specific days', 'events-made-easy') );
   $days_names = array (1 => $wp_locale->get_weekday_abbrev(__('Monday')), 2 => $wp_locale->get_weekday_abbrev(__('Tuesday')), 3 => $wp_locale->get_weekday_abbrev(__('Wednesday')), 4 => $wp_locale->get_weekday_abbrev(__('Thursday')), 5 => $wp_locale->get_weekday_abbrev(__('Friday')), 6 => $wp_locale->get_weekday_abbrev(__('Saturday')), 7 => $wp_locale->get_weekday_abbrev(__('Sunday')) );
   $saved_bydays = explode ( ",", $event['recurrence_byday'] );
   $weekno_options = array ("1" => __ ( 'first', 'events-made-easy'), '2' => __ ( 'second', 'events-made-easy'), '3' => __ ( 'third', 'events-made-easy'), '4' => __ ( 'fourth', 'events-made-easy'), '5' => __ ( 'fifth', 'events-made-easy'), '-1' => __ ( 'last', 'events-made-easy'), "0" => __('Start day', 'events-made-easy') );
   $holidays_array_by_id=eme_get_holidays_array_by_id();
?>
<div id="div_recurrence_date" style="background-color: lightgrey;">
      <br />
      <?php
	echo "<b>". __('Recurrence dates', 'events-made-easy') . "</b>";
      ?>
   <input id="localized-rec-start-date" type="text" name="localized_recurrence_date" value="" style="background: #FCFFAA;" readonly="readonly" />
   <input id="rec-start-date-to-submit" type="hidden" name="recurrence_start_date" value="" />
   <input id="localized-rec-end-date" type="text" name="localized_recurrence_end_date" value="" style="background: #FCFFAA;" readonly="readonly" />
   <input id="rec-end-date-to-submit" type="hidden" name="recurrence_end_date" value="" />
   <p class="eme_smaller" id='recurrence-dates-explanation'>
   <?php _e ( 'The recurrence beginning and end date.', 'events-made-easy'); ?>
   </p>
   <span id='recurrence-dates-explanation-specificdates'>
   <?php _e ( 'Select all the dates you want the event to begin on.', 'events-made-easy'); ?>
   </span>
   <div id="event_recurrence_pattern">
      <?php _e('Frequency:','events-made-easy'); ?>
      <select id="recurrence-frequency" name="recurrence_freq">
            <?php eme_option_items ( $freq_options, $event['recurrence_freq'] ); ?>
      </select>
	   <div id="recurrence-intervals">
                           <p>
                              <?php _e ( 'Every', 'events-made-easy')?>
                              <input id="recurrence-interval" name='recurrence_interval'
                                size='2' value='<?php if (isset ($event['recurrence_interval'])) echo $event['recurrence_interval']; ?>' />
                              <span class='interval-desc' id="interval-daily-singular">
                              <?php _e ( 'day', 'events-made-easy')?>
                              </span> <span class='interval-desc' id="interval-daily-plural">
                              <?php _e ( 'days', 'events-made-easy') ?>
                              </span> <span class='interval-desc' id="interval-weekly-singular">
                              <?php _e ( 'week', 'events-made-easy')?>
                              </span> <span class='interval-desc' id="interval-weekly-plural">
                              <?php _e ( 'weeks', 'events-made-easy')?>
                              </span> <span class='interval-desc' id="interval-monthly-singular">
                              <?php _e ( 'month', 'events-made-easy')?>
                              </span> <span class='interval-desc' id="interval-monthly-plural">
                              <?php _e ( 'months', 'events-made-easy')?>
                              </span><br />
                           <span class="alternate-selector" id="weekly-selector">
                              <?php eme_checkbox_items ( 'recurrence_bydays[]', $days_names, $saved_bydays ); ?>
                              <br />
                              <?php _e ( 'If you leave this empty, the recurrence start date will be used as a reference.', 'events-made-easy')?>
                           </span>
                           <span class="alternate-selector" id="monthly-selector">
                              <?php _e ( 'Every', 'events-made-easy')?>
                              <select id="monthly-modifier" name="recurrence_byweekno">
                                 <?php eme_option_items ( $weekno_options, $event['recurrence_byweekno'] ); ?>
                              </select>
                              <select id="recurrence-weekday" name="recurrence_byday">
                                 <?php eme_option_items ( $days_names, $event['recurrence_byday'] ); ?>
                              </select>
                              <?php _e ( 'Day of month', 'events-made-easy')?>
                              <br />
                              <?php _e ( 'If you use "Start day" as day of the month, the recurrence start date will be used as a reference.', 'events-made-easy')?>
                              &nbsp;
                          </span>
                          </p>
     </div>
   <?php 
      if (!empty($holidays_array_by_id)) {
         _e('Holidays: ','events-made-easy');
         echo eme_ui_select($event['holidays_id'],'holidays_id',$holidays_array_by_id);
	 ?><p class="eme_smaller"><?php
         _e('No events will be created on days matching an entry in the holidays list','events-made-easy');
	 ?></p><?php
      }
   ?>
  </div>
</div>
<?php
}

function eme_meta_box_div_event_page_title_format($event,$templates_array) {
	if (empty($event['event_page_title_format']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_page_title_format">
      <br />
      <?php
	echo "<b>". __('Single Event Title Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller">
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_page_title_format_tpl'],'eme_prop_event_page_title_format_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_page_title_format" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_page_title_format" id="event_page_title_format" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_page_title_format']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_single_event_format($event,$templates_array) {
	if (empty($event['event_single_event_format']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_single_event_format">
      <br />
      <?php
	echo "<b>". __('Single Event Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller">
   <?php _e ( 'The format of the single event page.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_single_event_format_tpl'],'eme_prop_event_single_event_format_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_single_event_format" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_single_event_format" id="event_single_event_format" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_single_event_format']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_contactperson_email_body($event,$templates_array) {
	if (empty($event['event_contactperson_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_contactperson_email_body">
      <br />
      <?php
	echo "<b>". __('Contact Person Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the contact person.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_contactperson_email_body_tpl'],'eme_prop_event_contactperson_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_contactperson_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_contactperson_email_body" id="event_contactperson_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_contactperson_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_recorded_ok_html($event,$templates_array) {
	if (empty($event['event_registration_recorded_ok_html']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_recorded_ok_html">
      <br />
      <?php
	echo "<b>". __('Booking recorded html Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The text (html allowed) shown to the user when the booking has been made successfully.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_recorded_ok_html_tpl'],'eme_prop_event_registration_recorded_ok_html_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_recorded_ok_html" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_recorded_ok_html" id="event_registration_recorded_ok_html" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_recorded_ok_html']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_respondent_email_body($event,$templates_array) {
	if (empty($event['event_respondent_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_respondent_email_body">
      <br />
      <?php
	echo "<b>". __('Respondent Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_respondent_email_body_tpl'],'eme_prop_event_respondent_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_respondent_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_respondent_email_body" id="event_respondent_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_respondent_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_pending_email_body($event,$templates_array) {
	if (empty($event['event_registration_pending_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_pending_email_body">
      <br />
      <?php
	echo "<b>". __('Registration Pending Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent if the registration is pending.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_pending_email_body_tpl'],'eme_prop_event_registration_pending_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_pending_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_pending_email_body" id="event_registration_pending_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_pending_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_updated_email_body($event,$templates_array) {
	if (empty($event['event_registration_updated_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_updated_email_body">
      <br />
      <?php
	echo "<b>". __('Registration Updated Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent if the registration has been updated by an admin.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_updated_email_body_tpl'],'eme_prop_event_registration_updated_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_updated_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_updated_email_body" id="event_registration_updated_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_updated_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_cancelled_email_body($event,$templates_array) {
	if (empty($event['event_registration_cancelled_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_cancelled_email_body">
      <br />
      <?php
	echo "<b>". __('Registration Cancelled Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent if the registration is cancelled.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_cancelled_email_body_tpl'],'eme_prop_event_registration_cancelled_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_cancelled_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_cancelled_email_body" id="event_registration_cancelled_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_cancelled_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_paid_email_body($event,$templates_array) {
	if (empty($event['event_registration_paid_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_paid_email_body">
      <br />
      <?php
	echo "<b>". __('Registration Paid Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent if the registration is paid for.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_paid_email_body_tpl'],'eme_prop_event_registration_paid_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_paid_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_paid_email_body" id="event_registration_paid_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_paid_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_denied_email_body($event,$templates_array) {
	if (empty($event['event_registration_denied_email_body']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_denied_email_body">
      <br />
      <?php
	echo "<b>". __('Registration Denied Email Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The format of the email which will be sent to the respondent if the registration is denied.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_denied_email_body_tpl'],'eme_prop_event_registration_denied_email_body_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_denied_email_body" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_denied_email_body" id="event_registration_denied_email_body" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_denied_email_body']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_registration_form_format($event,$templates_array) {
	if (empty($event['event_registration_form_format']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_registration_form_format">
      <br />
      <?php
	echo "<b>". __('Registration Form Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The registration form format.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_registration_form_format_tpl'],'eme_prop_event_registration_form_format_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_registration_form_format" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_registration_form_format" id="event_registration_form_format" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_registration_form_format']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_event_cancel_form_format($event,$templates_array) {
	if (empty($event['event_cancel_form_format']))
		$showhide_style='style="display:none; width:100%;"';
	else
		$showhide_style='style="width:100%;"';
?>
<div id="div_event_cancel_form_format">
      <br />
      <?php
	echo "<b>". __('Cancel Registration Form Format', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'The cancel registration form format.','events-made-easy');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'events-made-easy');?>
   </p>
   <?php _e('Either choose from a template: ','events-made-easy'); echo eme_ui_select($event['event_properties']['event_cancel_form_format_tpl'],'eme_prop_event_cancel_form_format_tpl',$templates_array); ?><br />
   <?php _e('Or enter your own (if anything is entered here, it takes precedence over the selected template): ','events-made-easy');?>
	<img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="event_cancel_form_format" style="cursor: pointer; vertical-align: middle; ">
   <br />
   <textarea name="event_cancel_form_format" id="event_cancel_form_format" rows="6" <?php echo $showhide_style; ?>><?php echo eme_esc_html($event['event_cancel_form_format']);?></textarea>
</div>
<?php
}

function eme_meta_box_div_location_name($event) {
   $use_select_for_locations = get_option('eme_use_select_for_locations');
   // qtranslate there? Then we need the select, otherwise locations will be created again...
   $lang = eme_detect_lang();
   if (!empty($lang)) {
      $use_select_for_locations=1;
   }
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $location = eme_get_location ( $event['location_id'] );
?>
<div id="div_location_name">
   <table id="eme-location-data">
   <?php
   if($use_select_for_locations) {
      $location_0 = eme_new_location();
      $location_0['location_id']=0;
      $locations = eme_get_locations();
   ?>
      <tr>
      <th><?php _e('Location','events-made-easy') ?></th>
      <td> 
      <select name="location-select-id" id='location-select-id' size="1">
      <option value="<?php echo $location_0['location_id'] ?>" ><?php echo eme_trans_esc_html($location_0['location_name']) ?></option>
      <?php 
      $selected_location=$location_0;
      foreach($locations as $tmp_location) {
         $selected = "";
         if (isset($location['location_id']) && $location['location_id'] == $tmp_location['location_id']) {
            $selected_location=$location;
            $selected = "selected='selected' ";
         }
         ?>
         <option value="<?php echo $tmp_location['location_id'] ?>" <?php echo $selected ?>><?php echo eme_trans_esc_html($tmp_location['location_name']) ?></option>
      <?php
      }
      ?>
      </select>
      <input type='hidden' name='location-select-name' value='<?php echo eme_trans_esc_html($selected_location['location_name'])?>' />
      <input type='hidden' name='location-select-city' value='<?php echo eme_trans_esc_html($selected_location['location_city'])?>' />
      <input type='hidden' name='location-select-address1' value='<?php echo eme_trans_esc_html($selected_location['location_address1'])?>' />
      <input type='hidden' name='location-select-address2' value='<?php echo eme_trans_esc_html($selected_location['location_address2'])?>' />
      <input type='hidden' name='location-select-state' value='<?php echo eme_trans_esc_html($selected_location['location_state'])?>' />
      <input type='hidden' name='location-select-zip' value='<?php echo eme_trans_esc_html($selected_location['location_zip'])?>' />
      <input type='hidden' name='location-select-country' value='<?php echo eme_trans_esc_html($selected_location['location_country'])?>' />
      <input type='hidden' name='location-select-latitude' value='<?php echo eme_trans_esc_html($selected_location['location_latitude'])?>' />
      <input type='hidden' name='location-select-longitude' value='<?php echo eme_trans_esc_html($selected_location['location_longitude'])?>' />
      </td>
      <?php
      if ($gmap_is_active) {
      ?>
         <td>
         <div id='eme-admin-map-not-found'>
         <p>
         <?php _e ( 'Map not found','events-made-easy'); ?>
         </p>
         </div>
         <div id='eme-admin-location-map'></div></td>
      <?php
      }
      ?>
      </tr>
       <tr >
       <td colspan='2'  rowspan='5' style='vertical-align: top'>
       <?php _e ( 'Select a location for your event', 'events-made-easy')?>
       </td>
       </tr>
   <?php
   } else {
   ?>
      <tr>
      <th><?php _e ( 'Name','events-made-easy')?>&nbsp;</th>
      <td>
	 <input id="location_id" type="hidden" name="location_id" value="<?php echo eme_trans_esc_html($location['location_id'])?>" />
	 <input id="location_name" class='clearable' type="text" name="location_name" value="<?php echo eme_trans_esc_html($location['location_name'])?>" />
      </td>
      <?php
      if ($gmap_is_active) {
      ?>
         <td rowspan='11'>
         <div id='eme-admin-map-not-found'>
         <p>
         <?php _e ( 'Map not found','events-made-easy'); ?>
         </p>
         </div>
         <div id='eme-admin-location-map'></div></td>
      <?php
      }
      ?>
      </tr>
      <tr>
      <td colspan='2'>
      <?php _e ( 'The name of the location where the event takes place. You can use the name of a venue, a square, etc', 'events-made-easy');?>
      <br />
      <?php _e ( 'If you leave this empty, the map will NOT be shown for this event', 'events-made-easy');?>
      </td>
      </tr>
       <tr>
       <th><?php _e ( 'Address1:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_address1" type="text" name="location_address1" value="<?php echo $location['location_address1']; ?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'Address2:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_address2" type="text" name="location_address2" value="<?php echo $location['location_address2']; ?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'City:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_city" type="text" name="location_city" value="<?php echo $location['location_city']?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'State:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_state" type="text" name="location_state" value="<?php echo $location['location_state']; ?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'Zip:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_zip" type="text" name="location_zip" value="<?php echo $location['location_zip']; ?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'Country:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_country" type="text" name="location_country" value="<?php echo $location['location_country']; ?>" /></td>
       </tr>
       <tr>
       <td colspan='2'>
       <?php _e ( 'If you\'re using the Google Map integration and are really serious about the correct place, use these.', 'events-made-easy')?>
       </td>
       </tr>
       <tr>
       <th><?php _e ( 'Latitude:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_latitude" type="text" name="location_latitude" value="<?php echo $location['location_latitude']?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'Longitude:', 'events-made-easy')?> &nbsp;</th>
       <td><input id="location_longitude" type="text" name="location_longitude" value="<?php echo $location['location_longitude']?>" /></td>
       </tr>
    <?php
    }
    ?>
    </table>
</div>
<?php
}
 
function eme_meta_box_div_event_notes($event) {
   $eme_editor_settings = eme_get_editor_settings(true,true,10);
?>
<div id="div_event_notes">
      <br />
      <?php
	echo "<b>". __('Details', 'events-made-easy') . "</b>";
      ?>
   <p class="eme_smaller"><?php _e ( 'Details about the event', 'events-made-easy')?></p>
   <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
   <!-- we need content for qtranslate as ID -->
   <?php wp_editor($event['event_notes'],"content",$eme_editor_settings); ?>
   </div>
</div>
<?php
}

function eme_meta_box_div_event_image($event) {
    if (isset($event['event_image_id']) && !empty($event['event_image_id']))
       $event['event_image_url'] = wp_get_attachment_url($event['event_image_id']);
?>
<div id="div_event_image">
      <br />
      <?php
	echo "<b>". __('Event image', 'events-made-easy') . "</b>";
      ?>
   <div id="event_no_image" class="postarea">
      <?php _e('No image set','events-made-easy'); ?>
   </div>
   <div id="event_current_image" class="postarea">
   <?php if (isset($event['event_image_url']) && !empty($event['event_image_url'])) {
      _e('Current image:', 'events-made-easy');
      echo "<img id='eme_event_image_example' alt='". __('Event image','events-made-easy')."' src='".$event['event_image_url']."' width='200' />";
      echo "<input type='hidden' name='event_image_url' id='event_image_url' value='".$event['event_image_url']."' />";
   } else {
      echo "<img id='eme_event_image_example' src='' alt='". __('Event image','events-made-easy')."' width='200' />";
      echo "<input type='hidden' name='event_image_url' id='event_image_url' />";
   }
   if (isset($event['event_image_id']) && !empty($event['event_image_id'])) {
      echo "<input type='hidden' name='event_image_id' id='event_image_id' value='".$event['event_image_id']."' />";
   } else {
      echo "<input type='hidden' name='event_image_id' id='event_image_id' />";
   }
   // based on code found at http://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
   ?>
   </div>
   <br />

   <div class="uploader">
   <input type="button" name="event_image_button" id="event_image_button" value="<?php _e ( 'Set a featured image', 'events-made-easy')?>" />
   <input type="button" id="eme_remove_old_image" name="eme_remove_old_image" value=" <?php _e ( 'Unset featured image', 'events-made-easy')?>" />
   </div>
</div>
<?php
}

function eme_meta_box_div_event_attributes($event) {
?>
<div id="div_event_attributes">
      <br />
      <?php
	echo "<b>". __('Attributes', 'events-made-easy') . "</b>";
      ?>
<?php
    eme_attributes_form($event);
?>
</div>
<?php
}

function eme_meta_box_div_event_url($event) {
?>
<div id="div_event_url">
      <br />
      <?php
	echo "<b>". __('External link', 'events-made-easy') . "</b>";
      ?>
   <input type="text" id="event_url" name="event_url" value="<?php echo eme_esc_html($event['event_url']); ?>" />
   <br />
   <p class="eme_smaller"><?php _e ( 'If this is filled in, the single event URL will point to this url instead of the standard event page.', 'events-made-easy')?></p>
</div>
<?php
}

function eme_meta_box_div_event_rsvp($event) {
   $currency_array = eme_currency_array();
   $event_number_spaces=$event['event_seats'];
   $registration_wp_users_only = ($event['registration_wp_users_only']) ? "checked='checked'" : "";
   $registration_requires_approval = ($event['registration_requires_approval']) ? "checked='checked'" : "";
   $event_RSVP_checked = ($event['event_rsvp']) ? "checked='checked'" : "";

   $use_paypal_checked = ($event['use_paypal']) ? "checked='checked'" : "";
   $use_2co_checked = ($event['use_2co']) ? "checked='checked'" : "";
   $use_webmoney_checked = ($event['use_webmoney']) ? "checked='checked'" : "";
   $use_fdgg_checked = ($event['use_fdgg']) ? "checked='checked'" : "";
   $use_mollie_checked = ($event['use_mollie']) ? "checked='checked'" : "";
   $use_sagepay_checked = ($event['use_sagepay']) ? "checked='checked'" : "";

   // all properties
   $eme_prop_auto_approve_checked = ($event['event_properties']['auto_approve']) ? "checked='checked'" : "";
   $eme_prop_ignore_pending_checked = ($event['event_properties']['ignore_pending']) ? "checked='checked'" : "";
   $eme_prop_take_attendance = ($event['event_properties']['take_attendance']) ? "checked='checked'" : "";
   $eme_prop_all_day_checked = ($event['event_properties']['all_day']) ? "checked='checked'" : "";
   $eme_prop_use_worldpay = ($event['event_properties']['use_worldpay']) ? "checked='checked'" : "";
   $eme_prop_use_stripe = ($event['event_properties']['use_stripe']) ? "checked='checked'" : "";
   $eme_prop_use_braintree = ($event['event_properties']['use_braintree']) ? "checked='checked'" : "";
   $eme_prop_use_offline = ($event['event_properties']['use_offline']) ? "checked='checked'" : "";

   $eme_prop_rsvp_discount = ($event['event_properties']['rsvp_discount']) ? $event['event_properties']['rsvp_discount'] : "";
   $eme_prop_rsvp_discountgroup = ($event['event_properties']['rsvp_discountgroup']) ? $event['event_properties']['rsvp_discountgroup'] : "";
   $eme_prop_waitinglist_seats = ($event['event_properties']['waitinglist_seats']) ? $event['event_properties']['waitinglist_seats'] : 0;
   $eme_prop_cancel_rsvp_days = $event['event_properties']['cancel_rsvp_days'];

?>
                     <div class="inside">
                        <p id='p_rsvp'>
                           <input id="rsvp-checkbox" name='event_rsvp' value='1' type='checkbox' <?php echo $event_RSVP_checked; ?> />
                           <label for="rsvp-checkbox"><?php _e ( 'Enable registration for this event', 'events-made-easy')?></label>
                        </p>
                     </div>

<div id="div_event_rsvp">
                           <p id='p_approval_required'>
                              <input id="approval_required-checkbox" name='registration_requires_approval' value='1' type='checkbox' <?php echo $registration_requires_approval; ?> />
                              <label for="approval_required-checkbox"><?php _e ( 'Require approval for registration','events-made-easy'); ?></label>
                           </p><p id='p_auto_approve'>
                              <input id="eme_prop_auto_approve" name='eme_prop_auto_approve' value='1' type='checkbox' <?php echo $eme_prop_auto_approve_checked; ?> />
                              <label for="eme_prop_auto_approve"><?php _e ( 'Auto-approve registration upon payment','events-made-easy'); ?></label>
                           </p><p id='p_ignore_pending'>
                              <input id="eme_prop_ignore_pending" name='eme_prop_ignore_pending' value='1' type='checkbox' <?php echo $eme_prop_ignore_pending_checked; ?> />
                              <label for="eme_prop_ignore_pending"><?php _e ( 'Consider pending registrations as available seats for new bookings','events-made-easy'); ?></label>
                           </p><p id='p_wp_member_required'>
                              <input id="wp_member_required-checkbox" name='registration_wp_users_only' value='1' type='checkbox' <?php echo $registration_wp_users_only; ?> />
                              <label for="wp_member_required-checkbox"><?php _e ( 'Require WP membership for registration','events-made-easy'); ?></label>
                           </p><p id='p_take_attendance'>
                              <input id="eme_prop_take_attendance" name='eme_prop_take_attendance' value='1' type='checkbox' <?php echo $eme_prop_take_attendance; ?> />
                              <label for="eme_prop_take_attendance"><?php _e ( 'Only take attendance (0 or 1 seat) for this event','events-made-easy'); ?></label>
                           </p><table>
                              <tr id='row_seats'>
                              <td><label for='seats-input'><?php _e ( 'Spaces','events-made-easy'); ?> :</label></td>
                              <td><input id="seats-input" type="text" name="event_seats" size='8' maxlength='125' title="<?php _e('For multiseat events, separate the values by \'||\'','events-made-easy'); ?>" value="<?php echo $event_number_spaces; ?>" /></td>
                              </tr>
                              <tr id='row_price'>
                              <td><label for='price'><?php _e ( 'Price: ','events-made-easy'); ?></label></td>
                              <td><input id="price" type="text" name="price" size='8' maxlength='125' title="<?php _e('For multiprice events, separate the values by \'||\'','events-made-easy'); ?>" value="<?php echo $event['price']; ?>" />
                              <select id="currency" name="currency">
                              <?php
                                 foreach ( $currency_array as $key=>$value) {
                                    if ($event['currency'] && ($event['currency']==$key)) {
                                       $selected = "selected='selected'";
                                    } elseif (!$event['currency'] && ($key==get_option('eme_default_currency'))) {
                                       $selected = "selected='selected'";
                                    } else {
                                       $selected = "";
                                    }
                                    echo "<option value='$key' $selected>$value</option>";
                                 }
                              ?>
                              </select></td>
                              </tr>
                              <tr id='row_waitinglist_seats'>
                              <td><label for='eme_prop_waitinglist_seats'><?php _e ( 'Waitinglist seats','events-made-easy'); ?></label></td>
                              <td><input id="eme_prop_waitinglist_seats" type="text" name="eme_prop_waitinglist_seats" maxlength='125' size='8' title="<?php _e('The number of seats considered to be a waiting list.','events-made-easy'); ?>" value="<?php echo $event['event_properties']['waitinglist_seats']; ?>" /></td>
                              </tr>
                              <tr id='row_max_allowed'>
                              <td><label for='eme_prop_max_allowed'><?php _e ( 'Max number of spaces to book','events-made-easy'); ?></label></td>
                              <td><input id="eme_prop_max_allowed" type="text" name="eme_prop_max_allowed" maxlength='125' size='8' title="<?php _e('The maximum number of spaces a person can book in one go.','events-made-easy').' '._e('(is multi-compatible)','events-made-easy'); ?>" value="<?php echo $event['event_properties']['max_allowed']; ?>" /></td>
                              </tr>
                              <tr id='row_min_allowed'>
                              <td><label for='eme_prop_min_allowed'><?php _e ( 'Min number of spaces to book','events-made-easy'); ?></label></td>
                              <td><input id="eme_prop_min_allowed" type="text" name="eme_prop_min_allowed" maxlength='125' size='8' title="<?php echo __('The minimum number of spaces a person can book in one go (it can be 0, for e.g. just an attendee list).','events-made-easy').' '.__('(is multi-compatible)','events-made-easy'); ?>" value="<?php echo $event['event_properties']['min_allowed']; ?>" /></td>
                              </tr>
                              <tr id='row_discount'>
                              <td><label for='eme_prop_rsvp_discount'><?php _e ( 'Discount to apply','events-made-easy'); ?></label></td>
                              <td><input id="eme_prop_rsvp_discount" type="text" name="eme_prop_rsvp_discount" maxlength='125' size='8' title="<?php _e('The discount name you want to apply (is overridden by discount group if used).','events-made-easy'); ?>" value="<?php echo $event['event_properties']['rsvp_discount']; ?>" /></td>
                              </tr>
                              <tr id='row_discountgroup'>
                              <td><label for='eme_prop_rsvp_discountgroup'><?php _e ( 'Discount group to apply','events-made-easy'); ?></label></td>
                              <td><input id="eme_prop_rsvp_discountgroup" type="text" name="eme_prop_rsvp_discountgroup" maxlength='125' size='8' title="<?php _e('The discount group name you want applied (overrides the discount).','events-made-easy'); ?>" value="<?php echo $event['event_properties']['rsvp_discountgroup']; ?>" /></td>
                              </tr></table>
			   <p id='span_rsvp_allowed_until'>
                              <?php _e ( 'Allow RSVP until ','events-made-easy'); ?>
                              <input id="rsvp_number_days" type="text" name="rsvp_number_days" maxlength='2' size='2' value="<?php echo $event['rsvp_number_days']; ?>" />
                              <?php _e ( 'days','events-made-easy'); ?>
                              <input id="rsvp_number_hours" type="text" name="rsvp_number_hours" maxlength='2' size='2' value="<?php echo $event['rsvp_number_hours']; ?>" />
                              <?php _e ( 'hours','events-made-easy'); ?>
                              <?php _e ( 'before the event ','events-made-easy');
                                 $eme_rsvp_end_target_list = array('start'=>__('starts','events-made-easy'),'end'=>__('ends','events-made-easy'));
                                 echo eme_ui_select($event['event_properties']['rsvp_end_target'],'eme_prop_rsvp_end_target',$eme_rsvp_end_target_list);
                              ?>
			   </p>
			   <p id='span_rsvp_cutoff'>
                              <?php _e ( 'RSVP cancel cutoff','events-made-easy'); ?>
                              <input id="eme_prop_cancel_rsvp_days" type="text" name="eme_prop_cancel_rsvp_days" maxlength='2' size='2' value="<?php echo $event['event_properties']['cancel_rsvp_days']; ?>" />
                              <?php _e ( 'Allow RSVP cancellation until this many days before the event starts.', 'events-made-easy'); ?>
			   </p>
			   <p id='span_payment_methods'>
                              <?php _e ( 'Payment methods','events-made-easy'); ?><br />
                              <input id="paypal-checkbox" name='use_paypal' value='1' type='checkbox' <?php echo $use_paypal_checked; ?> /><?php _e ( 'Paypal','events-made-easy'); ?><br />
                              <input id="2co-checkbox" name='use_2co' value='1' type='checkbox' <?php echo $use_2co_checked; ?> /><?php _e ( '2Checkout','events-made-easy'); ?><br />
                              <input id="webmoney-checkbox" name='use_webmoney' value='1' type='checkbox' <?php echo $use_webmoney_checked; ?> /><?php _e ( 'Webmoney','events-made-easy'); ?><br />
                              <input id="fdgg-checkbox" name='use_fdgg' value='1' type='checkbox' <?php echo $use_fdgg_checked; ?> /><?php _e ( 'First Data','events-made-easy'); ?><br />
                              <input id="mollie-checkbox" name='use_mollie' value='1' type='checkbox' <?php echo $use_mollie_checked; ?> /><?php _e ( 'Mollie','events-made-easy'); ?><br />
                              <input id="sagepay-checkbox" name='use_sagepay' value='1' type='checkbox' <?php echo $use_sagepay_checked; ?> /><?php _e ( 'Sage Pay','events-made-easy'); ?><br />
                              <input id="eme_prop_use_worldpay" name='eme_prop_use_worldpay' value='1' type='checkbox' <?php echo $eme_prop_use_worldpay; ?> /><?php _e ( 'Worldpay','events-made-easy'); ?><br />
                              <input id="eme_prop_use_stripe" name='eme_prop_use_stripe' value='1' type='checkbox' <?php echo $eme_prop_use_stripe; ?> /><?php _e ( 'Stripe','events-made-easy'); ?><br />
                              <input id="eme_prop_use_braintree" name='eme_prop_use_braintree' value='1' type='checkbox' <?php echo $eme_prop_use_braintree; ?> /><?php _e ( 'Braintree','events-made-easy'); ?><br />
                              <input id="eme_prop_use_offline" name='eme_prop_use_offline' value='1' type='checkbox' <?php echo $eme_prop_use_offline; ?> /><?php _e ( 'Offline','events-made-easy'); ?><br />
			   </p>
</div>
<?php
}

function eme_rss_link($justurl = 0, $echo = 1, $text = "RSS", $scope="future", $order = "ASC",$category='',$author='',$contact_person='',$limit=5, $location_id='',$title='') {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5, 'location_id' => '', 'title' => '' );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = $r['echo'];
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $echo = ($echo==="false" || $echo==="O") ? false : $echo;
   if ($text == '')
      $text = "RSS";
   $url = site_url ("/?eme_rss=main&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person&limit=$limit&location_id=$location_id&title=".urlencode($title));
   $link = "<a href='$url'>$text</a>";
   
   if ($justurl)
      $result = $url;
   else
      $result = $link;
   if ($echo)
      echo $result;
   else
      return $result;
}

function eme_rss_link_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5, 'location_id' => '', 'title' => '' ), $atts ) );
   $result = eme_rss_link ( "justurl=$justurl&echo=0&text=$text&limit=$limit&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person&location_id=$location_id&title=".urlencode($title) );
   return $result;
}

function eme_rss() {
      global $eme_timezone;

      if (isset($_GET['limit'])) {
         $limit=intval($_GET['limit']);
      } else {
         $limit=get_option('eme_event_list_number_items' );
      }
      if (isset($_GET['author'])) {
         $author=$_GET['author'];
      } else {
         $author="";
      }
      if (isset($_GET['contact_person'])) {
         $contact_person=$_GET['contact_person'];
      } else {
         $contact_person="";
      }
      if (isset($_GET['order'])) {
         $order=$_GET['order'];
      } else {
         $order="ASC";
      }
      if (isset($_GET['category'])) {
         $category=$_GET['category'];
      } else {
         $category=0;
      }
      if (isset($_GET['location_id'])) {
         $location_id=$_GET['location_id'];
      } else {
         $location_id='';
      }
      if (isset($_GET['scope'])) {
         $scope=$_GET['scope'];
      } else {
         $scope="future";
      }
      if (isset($_GET['title'])) {
         $main_title=$_GET['title'];
      } else {
         $main_title=get_option('eme_rss_main_title' );
      }
      
      header ( "Content-type: text/xml" );
      echo "<?xml version='1.0'?>\n";
      
      ?>
<rss version="2.0">
<channel>
<title><?php
      echo eme_sanitize_rss($main_title);
      ?></title>
<link><?php
      $events_page_link = eme_get_events_page();
      echo eme_sanitize_rss($events_page_link);
      ?></link>
<description><?php
      echo eme_sanitize_rss(get_option('eme_rss_main_description' ));
      ?></description>
<docs>
http://blogs.law.harvard.edu/tech/rss
</docs>
<generator>
Weblog Editor 2.0
</generator>
<?php
      $title_format = get_option('eme_rss_title_format');
      $description_format = get_option('eme_rss_description_format');
      $events = eme_get_events ( $limit, $scope, $order, 0, $location_id, $category, $author, $contact_person );
      # some RSS readers don't like it when an empty feed without items is returned, so we add a dummy item then
      if (empty ( $events )) {
         echo "<item>\n";
         echo "<title></title>\n";
         echo "<link></link>\n";
         echo "</item>\n";
      } else {
         foreach ( $events as $event ) {
             $title = eme_sanitize_rss(eme_replace_placeholders ( $title_format, $event, "rss" ));
             $description = eme_sanitize_rss(eme_replace_placeholders ( $description_format, $event, "rss" ));
             $event_link = eme_sanitize_rss(eme_event_url($event));
             echo "<item>\n";
             echo "<title>$title</title>\n";
             echo "<link>$event_link</link>\n";
             if (get_option('eme_rss_show_pubdate' )) {
                if (get_option('eme_rss_pubdate_startdate' )) {
                   echo "<pubDate>".eme_rfc822_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone)."</pubDate>\n";
                } else {
                   echo "<pubDate>".eme_rfc822_date($event['modif_date_gmt'])."</pubDate>\n";
                }
             }
             echo "<description>$description</description>\n";
             if (get_option('eme_categories_enabled')) {
                $categories = eme_sanitize_rss(eme_replace_placeholders ( "#_CATEGORIES", $event, "rss" ));
                echo "<category>$categories</category>\n";
             }
             echo "</item>\n";
         }
      }
      ?>

</channel>
</rss>

<?php
}

function eme_general_head() {
   $extra_html_header=get_option('eme_html_header');
   $extra_html_header=trim(preg_replace('/\r\n/', "\n", $extra_html_header));
   if (!empty($extra_html_header))
      echo $extra_html_header."\n";

   if (eme_is_single_event_page()) {
      $event=eme_get_event(get_query_var('event_id'));
      // I don't know if the canonical rel-link is needed, but since WP adds it by default ...
      $canon_url=eme_event_url($event);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_event_html_headers_format');
      if (!empty($extra_headers_format)) {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the text format already removes most of html code, so let's use that
            $extra_header = strip_shortcodes(eme_replace_placeholders ($extra_header_format, $event, "text",0 ));
            # the text format converts \n to \r\n but we want one line only
            $extra_header = trim(preg_replace('/\r\n/', "", $extra_header));
            if ($extra_header != "")
               echo $extra_header."\n";
         }
      }
   } elseif (eme_is_single_location_page()) {
      $location=eme_get_location(get_query_var('location_id'));
      $canon_url=eme_location_url($location);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_location_html_headers_format');
      if (!empty($extra_headers_format)) {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the text format already removes most of html code, so let's use that
            $extra_header = strip_shortcodes(eme_replace_locations_placeholders ($extra_header_format, $location, "text", 0 ));
            # the text format converts \n to \r\n but we want one line only
            $extra_header = trim(preg_replace('/\r\n/', "", $extra_header));
            if (!empty($extra_header))
               echo $extra_header."\n";
         }
      }
   }
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $gmap_api_key = get_option('eme_gmap_api_key' );
   if (!empty($gmap_api_key)) $gmap_api_key="key=$gmap_api_key";
   $load_js_in_header = get_option('eme_load_js_in_header' );
   if ($gmap_is_active && $load_js_in_header) {
	   // in the header: no async, it doesn't work
	   // but since we don't load the google api async, we can't use
	   // the callback option either, so we need to call our script
	   // when dom is ready
      echo "<script type='text/javascript' src='//maps.google.com/maps/api/js?".$gmap_api_key."'></script>\n";
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map.js'></script>\n";
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map_header.js'></script>\n";
   }
}

function eme_general_footer() {
   global $eme_need_gmap_js;
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $gmap_api_key = get_option('eme_gmap_api_key' );
   if (!empty($gmap_api_key)) $gmap_api_key="key=$gmap_api_key";
   $load_js_in_header = get_option('eme_load_js_in_header' );
   // we only include the map js if wanted/needed
   if (!$load_js_in_header && $gmap_is_active && $eme_need_gmap_js) {
      //echo "<script type='text/javascript' src='//maps.google.com/maps/api/js?".$gmap_api_key."&callback=loadGMap' async='async' defer='defer'></script>\n";
      // we don't do async/defer of our own eme_location_map.js, so as to be sure it is loaded before the callback gets executed
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map.js'></script>\n";
      echo "<script type='text/javascript' src='//maps.google.com/maps/api/js?".$gmap_api_key."&callback=loadGMap' defer='defer' async='async'></script>\n";
   }
   $extra_html_footer=get_option('eme_html_footer');
   $extra_html_footer=trim(preg_replace('/\r\n/', "\n", $extra_html_footer));
   if (!empty($extra_html_footer))
      echo $extra_html_footer."\n";
}

function eme_sanitize_event($event) {
   global $eme_timezone;
   // remove possible unwanted fields
   if (isset($event['event_id'])) {
      unset($event['event_id']);
   }
   $event['modif_date']=current_time('mysql', false);
   $event['modif_date_gmt']=current_time('mysql', true);

   // some sanity checks
   if ($event['event_end_date']<$event['event_start_date']) {
      $event['event_end_date']=$event['event_start_date'];
   }
   if (!is_serialized($event['event_attributes']))
      $event['event_attributes'] = serialize($event['event_attributes']);

   if (!is_serialized($event['event_properties']))
      $event['event_properties'] = serialize($event['event_properties']);

   $event_properties = @unserialize($event['event_properties']);
   if ($event_properties['all_day']) {
      $event['event_start_time']="00:00:00";
      $event['event_end_time']="23:59:59";
   }
   // if the end day/time is lower than the start day/time, then put
   // the end day one day ahead, but only if
   // the end time has been filled in, if it is empty then we keep
   // the end date as it is
   if ($event['event_end_time'] != "00:00:00") {
      $eme_date_obj1=new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
      $eme_date_obj2=new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
      if ($eme_date_obj2->lessThan($eme_date_obj1)) {
         $event['event_end_date']=$eme_date_obj1->addOneDay()->format('Y-m-d');
      }
   } elseif ($event['event_end_date']==$event['event_start_date']) {
      $event['event_end_time']="23:59:59";
   }

   if (eme_is_multi($event['event_seats'])) {
	   // waiting list only for simple events (no multiseats)
	   $event_properties['waitinglist_seats']=0;
	   $multiseat=eme_convert_multi2array($event['event_seats']);
	   foreach ($multiseat as $key=>$value) {
		   if (!is_numeric($value)) $multiseat[$key]=0;
	   }
	   $event['event_seats'] = eme_convert_array2multi($multiseat);
   } else {
	   if (!is_numeric($event['event_seats'])) $event['event_seats'] = 0;
	   $event_properties['waitinglist_seats']=intval($event_properties['waitinglist_seats']);
	   if ($event_properties['waitinglist_seats']>=$event['event_seats'])
		   $event_properties['waitinglist_seats']=0;
   }

   if (eme_is_multi($event['price'])) {
	   $multiprice=eme_convert_multi2array($event['price']);
	   foreach ($multiprice as $key=>$value) {
		   if (!is_numeric($value)) $multiprice[$key]=0;
	   }
	   $event['price'] = eme_convert_array2multi($multiprice);
   } else {
	   if (!is_numeric($event['price'])) $event['price'] = 0;
   }

   if (!eme_is_date_valid($event['event_start_date']))
	   $event['event_start_date'] = "";
   if (!eme_is_date_valid($event['event_end_date']))
	   $event['event_end_date'] = "";

   // check all variables that need to be urls
   $url_vars = array('event_url','event_image_url');
   foreach ($url_vars as $url_var) {
      if (!empty($event[$url_var])) {
           //make sure url's have a correct prefix
	   $parsed = parse_url($event[$url_var]);
	   if (empty($parsed['scheme'])) {
              $scheme  = is_ssl() ? 'https://' : 'http://';
              $event[$url_var] = $scheme . ltrim($event[$url_var], '/');
	   }
           //make sure url's are correctly escaped
	   $event[$url_var] = esc_url_raw ( $event[$url_var] ) ;
      }
   }

   if (!empty($event['event_slug']))
	$event['event_slug'] = eme_permalink_convert(eme_strip_tags($event['event_slug']));
   else
	$event['event_slug'] = eme_permalink_convert(eme_strip_tags($event['event_name']));

   // some things just need to be integers, let's brute-force them
   $int_vars=array('event_contactperson_id','event_rsvp','rsvp_number_days','rsvp_number_hours','registration_requires_approval','registration_wp_users_only','use_paypal','use_2co','use_webmoney','use_fdgg','use_mollie','use_sagepay','event_image_id');
   foreach ($int_vars as $int_var) {
	   $event[$int_var]=intval($event[$int_var]);
   }

   // serialize the props again
   $event['event_properties'] = serialize($event_properties);

   return eme_strip_js(eme_strip_weird($event));
}

function eme_db_insert_event($event,$event_is_part_of_recurrence=0) {
   global $wpdb, $eme_timezone;
   $current_userid=get_current_user_id();
   $table_name = $wpdb->prefix . EVENTS_TBNAME;

   $event['creation_date']=current_time('mysql', false);
   $event['creation_date_gmt']=current_time('mysql', true);
   $event['event_author']=$current_userid;
   // never try to update a autoincrement value ...
   if (isset($event['event_id']))
      unset ($event['event_id']);

   if (has_filter('eme_event_preinsert_filter')) $event=apply_filters('eme_event_preinsert_filter',$event);

   $wpdb->show_errors(true);
   if (!$wpdb->insert ( $table_name, $event )) {
      $wpdb->print_error();
      return false;
   } else {
      $event_ID = $wpdb->insert_id;
      $event['event_id']=$event_ID;
      // the eme_insert_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_insert_event_action')) {
	      $event['event_attributes']=unserialize($event['event_attributes']);
	      $event['event_properties']=unserialize($event['event_properties']);
	      do_action('eme_insert_event_action',$event);
      }
      return $event_ID;
   }
}

function eme_db_update_event($event,$event_id,$event_is_part_of_recurrence=0) {
   global $wpdb, $eme_timezone;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   // never try to update a autoincrement value ...
   if (isset($event['event_id']))
      unset ($event['event_id']);

   // backwards compatible: older versions gave directly the where array instead of the event_id
   if (!is_array($event_id)) 
      $where=array('event_id' => $event_id);
   else
      $where = $event_id;

   if (has_filter('eme_event_preupdate_filter')) $event=apply_filters('eme_event_preupdate_filter',$event);
   $wpdb->show_errors(true);
   if ($wpdb->update ( $table_name, $event, $where ) === false) {
      $wpdb->print_error();
      $wpdb->show_errors(false);
      return false;
   } else {
      $event['event_id']=$event_id;
      // the eme_update_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_update_event_action')) {
         // we do this call so all parameters for the event are filled, otherwise for an update this might not be the case
         $event = eme_get_event($event_id);
         do_action('eme_update_event_action',$event);
      }
      $wpdb->show_errors(false);
      return true;
   }
}

function eme_change_event_status($events,$state) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;

   if (is_array($events))
      $events_to_change=join(',',$events);
   else
      $event_to_change=$events;

   $sql = "UPDATE $table_name set event_status=$state WHERE event_id in (".$events_to_change.")";
   $wpdb->query($sql);
}

function eme_db_delete_event($event,$event_is_part_of_recurrence=0) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $wpdb->show_errors(false);
   $sql = $wpdb->prepare("DELETE FROM $table_name WHERE event_id = %d",$event['event_id']);
   // also delete associated image
   $image_basename= IMAGE_UPLOAD_DIR."/event-".$event['event_id'];
   eme_delete_image_files($image_basename);
   if ($wpdb->query($sql)) {
      eme_delete_all_bookings_for_event_id($event['event_id']);
      // the eme_delete_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_delete_event_action')) do_action('eme_delete_event_action',$event);
   }
}

function eme_admin_enqueue_js(){
   global $plugin_page, $eme_wp_date_format, $eme_wp_time_format;
   $locale_code = get_locale();
   $locale_code = preg_replace( "/_/","-", $locale_code );

   if (preg_match('/^eme-/',$plugin_page)) {
      // remove some scripts from widgetopts that get loaded everywhere ...
      remove_action( 'admin_enqueue_scripts', 'widgetopts_load_admin_scripts', 100 );
      $translation_array = array(
	      			'translate_plugin_url' => EME_PLUGIN_URL,
	      			'translate_error' => __('An error has occurred','events-made-easy'),
				'translate_clear' => __('Clear','events-made-easy')
			);
      wp_localize_script('eme-basic', 'emebasic',$translation_array);
      wp_enqueue_script('eme-basic');
      $translation_array = array(
				'translate_nonce' => wp_create_nonce( 'eme-dismissible-notice' ),
				'translate_selectpersons' => __('Select one or more persons','events-made-easy'),
				'translate_selectmembers' => __('Select one or more members','events-made-easy'),
				'translate_peoplenonce' => wp_create_nonce( 'eme_people' ),
				'translate_membernonce' => wp_create_nonce( 'eme_members' )
			   );
      wp_localize_script('eme-admin', 'emeadmin', $translation_array);
      wp_enqueue_script('eme-admin');
      wp_enqueue_style('eme-jquery-ui-css');
      //wp_enqueue_style("wp-jquery-ui-dialog");
      wp_enqueue_style('eme-jquery-jtable-css');
      wp_enqueue_style('eme-jtables-css');
      wp_enqueue_style('eme-jquery-select2-css');
      if (wp_script_is('eme-jtable-locale','registered'))
            wp_enqueue_script('eme-jtable-locale');
   }
   if ($plugin_page == 'eme-new_event' || (in_array( $plugin_page, array('eme-locations', 'eme-manager')) && isset($_REQUEST['eme_admin_action'])) ) {
      // we need this to have the "postbox" javascript loaded, so closing/opening works for those divs
      // wp_enqueue_script('post');
      //if (get_option('eme_gmap_is_active' ) && get_option('eme_gmap_api_key')) {
      if (get_option('eme_gmap_is_active')) {
        $translation_array = array(
				'translate_lang' => eme_detect_lang(),
				'translate_js_trans_function' => eme_detect_lang_js_trans_function(),
				'translate_eme_gmap_zooming' => get_option('eme_gmap_zooming') ? 'true' : 'false',
				'translate_eme_gmap_is_active' => get_option('eme_gmap_is_active') ? 'true' : 'false'
			   );
        wp_localize_script('eme-admin-maps', 'emeadmin', $translation_array);
        wp_enqueue_script('eme-admin-maps');
	// we use add_action admin_head, to include the eme_admin_map_script javascript after all the other javascripts
	// defined by enqueue script are loaded in the header, otherwise we get the 'green screen of death' for the map in the beginning
	// since the eme_admin_map_script javascript would get executed before the google map api got loaded
        // add_action('admin_head', 'eme_admin_map_script');
      }
   }
   if ( in_array( $plugin_page, array('eme-new_event', 'eme-manager','eme-options', 'eme-registration-approval','eme-registration-seats') ) ) {
      eme_enqueue_datepick();
   }
   if ( in_array( $plugin_page, array('eme-new_event', 'eme-manager') ) ) {
      wp_enqueue_style('eme-jquery-ui-autocomplete');
      // Now we can localize the script with our data.
      $translation_array = array(
                                 'translate_events' => __('Events','events-made-easy'),
                                 'translate_rsvp' => __('RSVP','events-made-easy'),
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_status' => __('Status','events-made-easy'),
                                 'translate_copy' => __('Copy','events-made-easy'),
                                 'translate_csv' => __('CSV','events-made-easy'),
                                 'translate_print' => __('Print','events-made-easy'),
                                 'translate_location' => __('Location','events-made-easy'),
                                 'translate_recinfo' => __('Recurrence info','events-made-easy'),
                                 'translate_date' => __('Date','events-made-easy'),
                                 'translate_datetime' => __('Date and time','events-made-easy'),
                                 'translate_fields_missing' => __('Some required fields are missing:','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_selectfeaturedimg' => __('Select the image to be used as featured image','events-made-easy'),
                                 'translate_setfeaturedimg' => __('Set featured image','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure you want to delete the selected records?','events-made-easy'),
                                 'translate_enddate_required' => __('Since the event is repeated, you must specify an end date','events-made-easy'),
                                 'translate_startenddate_identical' => __("In a recurrence, start and end date can't be identical",'events-made-easy'),
                                 'translate_nonce' => wp_create_nonce( 'eme_events' ),
                                 'translate_locale_code' => $locale_code,
                                 'translate_firstDayOfWeek' => get_option('start_of_week')
                                );
      wp_localize_script( 'eme-events', 'eme', $translation_array );
      wp_enqueue_script( 'eme-events');
       // some inline js that gets shown at the top
      eme_admin_event_script();
   }
   if ( in_array( $plugin_page, array('eme-templates') ) ) {
      $translation_array = array(
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_templates' => __('Templates','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_description' => __('Description','events-made-easy'),
                                 'translate_type' => __('Type','events-made-easy'),
                                 'translate_edit' => __('Edit','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
                                 'translate_nonce' => wp_create_nonce( 'eme_templates' )
                                );
      wp_localize_script('eme-templates', 'emetemplates', $translation_array);
      wp_enqueue_script('eme-templates');
   }
   if ( in_array( $plugin_page, array('eme-formfields') ) ) {
      $translation_array = array(
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_type' => __('Type','events-made-easy'),
                                 'translate_extracharge' => __('Extra charge','events-made-easy'),
                                 'translate_purpose' => __('Purpose','events-made-easy'),
                                 'translate_used' => __('Used in replies','events-made-easy'),
                                 'translate_required' => __('Required','events-made-easy'),
                                 'translate_edit' => __('Edit','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
                                 'translate_nonce' => wp_create_nonce( 'eme_formfields' )
                                );
      wp_localize_script('eme-formfields', 'emeformfields', $translation_array);
      wp_enqueue_script('eme-formfields');
   }
   if ( in_array( $plugin_page, array('eme-options') ) ) {
      wp_enqueue_script('eme-options');
   }
   if ( in_array( $plugin_page, array('eme-discounts') ) ) {
      $translation_array = array(
                                 'translate_discounts' => __('Discounts','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_description' => __('Description','events-made-easy'),
                                 'translate_discountgroup' => __('Discountgroup','events-made-easy'),
                                 'translate_discountgroups' => __('Discountgroups','events-made-easy'),
                                 'translate_coupon' => __('Coupon','events-made-easy'),
                                 'translate_casesensitive' => __('Case sensitive','events-made-easy'),
                                 'translate_value' => __('Value','events-made-easy'),
                                 'translate_type' => __('Type','events-made-easy'),
                                 'translate_maxusage' => __('Max Usage','events-made-easy'),
                                 'translate_usage' => __('Usage','events-made-easy'),
                                 'translate_expiration' => __('Expiration','events-made-easy'),
                                 'translate_maxdiscounts' => __('Max Discounts','events-made-easy'),
                                 'translate_no' => __('No'),
                                 'translate_yes' => __('Yes'),
                                 'translate_edit' => __('Edit','events-made-easy'),
                                 'translate_added' => __('Record added','events-made-easy'),
                                 'translate_updated' => __('Record updated','events-made-easy'),
                                 'translate_deleted' => __('Records deleted','events-made-easy'),
                                 'translate_fixed' => __('Fixed','events-made-easy'),
                                 'translate_fixed_per_seat' => __('Fixed per seat','events-made-easy'),
                                 'translate_percentage' => __('Percentage','events-made-easy'),
                                 'translate_code' => __('Code','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
                                 'translate_nonce' => wp_create_nonce( 'eme_discounts' )
                                );
      wp_localize_script( 'eme-discounts', 'emediscounts', $translation_array );
      wp_enqueue_script('eme-discounts');
   }
   if ( in_array( $plugin_page, array('eme-locations') ) ) {
      $translation_array = array(
                                 'translate_locations' => __('Locations','events-made-easy'),
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_copy' => __('Copy','events-made-easy'),
                                 'translate_address1' => __('Address1','events-made-easy'),
                                 'translate_address2' => __('Address2','events-made-easy'),
                                 'translate_city' => __('City','events-made-easy'),
                                 'translate_zip' => __('Zip','events-made-easy'),
                                 'translate_state' => __('State','events-made-easy'),
                                 'translate_country' => __('Country','events-made-easy'),
                                 'translate_latitude' => __('Latitude','events-made-easy'),
                                 'translate_longitude' => __('Longitude','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure you want to delete the selected records?','events-made-easy'),
                                 'translate_csv' => __('CSV','events-made-easy'),
                                 'translate_print' => __('Print','events-made-easy'),
				 'translate_selectfeaturedimage' => __('Select the image to be used as featured image','events-made-easy'),
				 'translate_setfeaturedimage' => __('Set featured image','events-made-easy')
                                );
      wp_localize_script( 'eme-locations', 'eme', $translation_array );
      wp_enqueue_script('eme-locations');
   }
   if ( in_array( $plugin_page, array('eme-people','eme-groups') ) ) {
	   wp_enqueue_media();
	   $translation_array = array(
		   'translate_plugin_url' => EME_PLUGIN_URL,
			 'translate_people' => __('People','events-made-easy'),
			 'translate_groups' => __('Groups','events-made-easy'),
			 'translate_description' => __('Description','events-made-easy'),
			 'translate_name' => __('Name','events-made-easy'),
			 'translate_lastname' => __('Last Name','events-made-easy'),
			 'translate_firstname' => __('First Name','events-made-easy'),
			 'translate_address1' => __('Address1','events-made-easy'),
			 'translate_address2' => __('Address2','events-made-easy'),
			 'translate_city' => __('City','events-made-easy'),
			 'translate_zip' => __('Zip','events-made-easy'),
			 'translate_state' => __('State','events-made-easy'),
			 'translate_country' => __('Country','events-made-easy'),
			 'translate_email' => __('E-mail','events-made-easy'),
			 'translate_phone' => __('Phone number','events-made-easy'),
			 'translate_lang' => __('Language','events-made-easy'),
			 'translate_pleasewait' => __('Please wait','events-made-easy'),
			 'translate_apply' => __('Apply','events-made-easy'),
			 'translate_clear' => __('Clear','events-made-easy'),
			 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
			 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
			 'translate_showallbookings' => __('Show all bookings','events-made-easy'),
			 'translate_bookingsmade' => __('Reservations made','events-made-easy'),
			 'translate_selectimg' => __('Select the image to be used as person image','events-made-easy'),
			 'translate_setimg' => __('Set image','events-made-easy'),
			 'translate_csv' => __('CSV','events-made-easy'),
			 'translate_print' => __('Print','events-made-easy'),
			 'translate_no' => __('No'),
			 'translate_yes' => __('Yes'),
			 'translate_answers' => __('Answers','events-made-easy'),
			 'translate_added' => __('Record added','events-made-easy'),
			 'translate_updated' => __('Record updated','events-made-easy'),
			 'translate_deleted' => __('Records deleted','events-made-easy'),
			 'translate_massmail' => __('MassMail','events-made-easy'),
			 'translate_option_massmail' => get_option('eme_people_massmail'),
			 'translate_groupcount' => __('Nbr People','events-made-easy'),
			 'translate_nonce' => wp_create_nonce('eme_people'),
			 'translate_admin_sendmails_url' => admin_url("admin.php?page=eme-send-mails")
		 );
		 wp_localize_script( 'eme-people', 'eme', $translation_array );
		 wp_enqueue_script('eme-people');
   }
   if ( in_array( $plugin_page, array('eme-members','eme-memberships') ) ) {
	   $translation_array = array(
		   'translate_plugin_url' => EME_PLUGIN_URL,
                                 'translate_members' => __('Members','events-made-easy'),
                                 'translate_memberships' => __('Memberships','events-made-easy'),
                                 'translate_membership' => __('Membership','events-made-easy'),
                                 'translate_description' => __('Description','events-made-easy'),
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_contact' => __('Contact','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_lastname' => __('Last Name','events-made-easy'),
                                 'translate_firstname' => __('First Name','events-made-easy'),
                                 'translate_email' => __('E-mail','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
				 'translate_clear' => __('Clear','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
                                 'translate_csv' => __('CSV','events-made-easy'),
                                 'translate_print' => __('Print','events-made-easy'),
                                 'translate_no' => __('No'),
                                 'translate_yes' => __('Yes'),
                                 'translate_answers' => __('Answers','events-made-easy'),
                                 'translate_edit' => __('Edit','events-made-easy'),
                                 'translate_membercount' => __('Nbr Active Members','events-made-easy'),
                                 'translate_startdate' => __('Start','events-made-easy'),
                                 'translate_enddate' => __('End','events-made-easy'),
                                 'translate_registrationdate' => __('Registered on','events-made-easy'),
                                 'translate_paymentdate' => __('Paid on','events-made-easy'),
                                 'translate_paid' => __('Paid','events-made-easy'),
                                 'translate_lastreminder' => __('Last reminder','events-made-easy'),
                                 'translate_nbrreminder' => __('Reminders sent','events-made-easy'),
                                 'translate_status' => __('Status','events-made-easy'),
                                 'translate_nonce' => wp_create_nonce('eme_members'),
				 'translate_admin_sendmails_url' => admin_url("admin.php?page=eme-send-mails")
                                );
      wp_localize_script( 'eme-members', 'eme', $translation_array );
      wp_enqueue_script('eme-members');
   }
   if ( in_array( $plugin_page, array('eme-registration-approval','eme-registration-seats') ) ) {
      $translation_array = array(
                                 'translate_bookings' => __('Bookings','events-made-easy'),
                                 'translate_id' => __('ID','events-made-easy'),
                                 'translate_rsvp' => __('RSVP','events-made-easy'),
                                 'translate_eventname' => __('Name','events-made-easy'),
                                 'translate_datetime' => __('Date and time','events-made-easy'),
                                 'translate_booker' => __('Booker','events-made-easy'),
                                 'translate_bookingdate' => __('Booking date','events-made-easy'),
                                 'translate_seats' => __('Seats','events-made-easy'),
                                 'translate_eventprice' => __('Event price','events-made-easy'),
                                 'translate_totalprice' => __('Total price','events-made-easy'),
                                 'translate_uniquenbr' => __('Unique nbr','events-made-easy'),
                                 'translate_paymentid' => __('Payment ID','events-made-easy'),
                                 'translate_paid' => __('Paid','events-made-easy'),
                                 'translate_paymentdate' => __('Payment date','events-made-easy'),
                                 'translate_paidandapprove' => __('Mark paid and approve','events-made-easy'),
                                 'translate_no' => __('No'),
                                 'translate_yes' => __('Yes'),
                                 'translate_edit' => __('Edit','events-made-easy'),
                                 'translate_csv' => __('CSV','events-made-easy'),
                                 'translate_print' => __('Print','events-made-easy'),
                                 'translate_lastreminder' => __('Last reminder','events-made-easy'),
                                 'translate_discount' => __('Discount','events-made-easy'),
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_apply' => __('Apply','events-made-easy'),
                                 'translate_pleaseselectrecords' => __('Please select some records first.','events-made-easy'),
                                 'translate_areyousuretodeleteselected' => __('Are you sure to delete the selected records?','events-made-easy'),
                                 'translate_locale_code' => $locale_code,
                                 'translate_nonce' => wp_create_nonce('eme_rsvp'),
                                 'translate_firstDayOfWeek' => get_option('start_of_week'),
			         'translate_admin_sendmails_url' => admin_url("admin.php?page=eme-send-mails")
                                );
      wp_localize_script( 'eme-rsvp', 'eme', $translation_array );
      wp_enqueue_script('eme-rsvp');
   }
   if ( in_array( $plugin_page, array('eme-send-mails') ) ) {
      wp_enqueue_style('eme-jquery-ui-timepicker-css');
      wp_enqueue_style('eme-jquery-ui-autocomplete');
      $translation_array = array(
                                 'translate_pleasewait' => __('Please wait','events-made-easy'),
                                 'translate_sendmail' => __('Send Mail','events-made-easy'),
                                 'translate_sentdatetime' => __('Sent on','events-made-easy'),
                                 'translate_name' => __('Name','events-made-easy'),
                                 'translate_email' => __('E-mail','events-made-easy'),
                                 'translate_status' => __('Status','events-made-easy'),
                                 'translate_action' => __('Action','events-made-easy'),
                                 'translate_mailingreport' => __('Mailing report','events-made-easy'),
                                 'translate_selectgroups' => __('Select one or more groups','events-made-easy'),
                                 'translate_selectmemberships' => __('Select one or more memberships','events-made-easy'),
                                 'translate_selectevents' => __('Select one or more events','events-made-easy'),
                                 'translate_htmlmail' => get_option('eme_rsvp_send_html')? "yes":"no",
                                 'translate_dateformat' => eme_wp_date_format_php_to_js($eme_wp_date_format),
                                 'translate_timeformat' => eme_wp_time_format_php_to_js($eme_wp_time_format)
                                );
      wp_localize_script( 'eme-sendmails', 'eme', $translation_array );
      wp_enqueue_script('eme-sendmails');
      wp_enqueue_script('eme-jquery-ui-datetimepicker');
   }

   if (preg_match('/^eme-/',$plugin_page)) {
      wp_enqueue_style('eme_stylesheet');
      wp_enqueue_style('eme_stylesheet_extra');
   }
}

# return number of days until next event or until the specified event
function eme_countdown($atts) {
   global $eme_timezone;
   extract ( shortcode_atts ( array ('id'=>''), $atts ) );

   if ($id!="") {
      $event=eme_get_event($id);
   } else {
      $newest_event_array=eme_get_events(1);
      $event=$newest_event_array[0];
   }
   $eme_date_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
   $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
   return intval($eme_date_obj_now->getDifferenceInDays($eme_date_obj));
}

function eme_image_url_for_event($event) {
   if (isset($event['recurrence_id']) && $event['recurrence_id']>0) {
      $image_basename= IMAGE_UPLOAD_DIR."/recurrence-".$event['recurrence_id'];
      $image_baseurl= IMAGE_UPLOAD_URL."/recurrence-".$event['recurrence_id'];
   } else {
      $image_basename= IMAGE_UPLOAD_DIR."/event-".$event['event_id'];
      $image_baseurl= IMAGE_UPLOAD_URL."/event-".$event['event_id'];
   }
   $mime_types = array('gif','jpg','png');
   foreach($mime_types as $type) {
      $file_path = $image_basename.".".$type;
      $file_url = $image_baseurl.".".$type;
      if (file_exists($file_path)) {
         return $file_url;
      }
   }
   return '';
}

function eme_ajax_events_search() {
   global $wpdb, $eme_timezone;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $return = array();
   if (isset($_REQUEST['q']))
      $q = strtolower($_REQUEST['q']);
   header("Content-type: application/json; charset=utf-8");
   if (!isset($_REQUEST['q']) || empty($q)) {
      echo json_encode($return);
      return;
   }

   $where=array();
   $where[]="event_name LIKE '%".esc_sql($q)."%'";
   // if the event id is set, we exclude it
   if (isset($_REQUEST['not_event_id']))
      $where[]="event_id != ".intval($_REQUEST['not_event_id']);
   if (isset($_REQUEST['event_rsvp']))
      $where[]="event_rsvp = ".intval($_REQUEST['event_rsvp']);
   $where = " WHERE ".implode(" AND ",$where);
   $sql = "SELECT event_id,event_name,event_start_date,event_start_time from $table_name $where ORDER BY event_start_date ASC, event_start_time ASC LIMIT 500";
   $events=$wpdb->get_results ( $sql, ARRAY_A );
   foreach($events as $event) {
      $record = array();
      $record['event_id'] = $event['event_id'];
      $record['eventinfo'] = esc_html($event['event_name']." (".eme_localized_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone).")");
      $return[] = $record;
   }
   echo json_encode($return);
   wp_die();
}
add_action( 'wp_ajax_eme_events_list', 'eme_ajax_events_list' );
add_action( 'wp_ajax_eme_manage_events', 'eme_ajax_manage_events' );
add_action( 'wp_ajax_eme_autocomplete_event', 'eme_ajax_events_search' );

function eme_ajax_events_list() {
   global $eme_timezone;

   $jtStartIndex= (isset($_REQUEST['jtStartIndex'])) ? intval($_REQUEST['jtStartIndex']) : 0;
   $jtPageSize= (isset($_REQUEST['jtPageSize'])) ? intval($_REQUEST['jtPageSize']) : 10;
   $jtSorting = (isset($_REQUEST['jtSorting'])) ? eme_sanitize_request($_REQUEST['jtSorting']) : 'ASC';
   $scope = (isset($_REQUEST['scope'])) ? eme_sanitize_request($_REQUEST['scope']) : 'future';
   $category = isset($_REQUEST['category']) ? intval($_REQUEST['category']) : 0;
   $status = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : '';
   $search_name = isset($_REQUEST['search_name']) ? eme_sanitize_request($_REQUEST['search_name']) : '';
   $search_start_date = isset($_REQUEST['search_start_date']) ? esc_sql($_REQUEST['search_start_date']) : '';
   $where ='';
   $where_arr = array();
   if(!empty($search_name)) {
      $where_arr[] = "event_name like '%".$search_name."%'";
   }
   if (!empty($search_start_date) && eme_validateDate($search_start_date,'Y-m-d')) {
      $where_arr[] = "event_start_date = '$search_start_date'";
      $scope='all';
   }
   if(!empty($status)) {
      $where_arr[] = 'event_status = '.$status;
   }
   if ($where_arr)
      $where = implode(" AND ",$where_arr);

   // we ask only for the event_id column here, more efficient
   $count_only=1;
   $events_count = eme_get_events ( 0, $scope, '', 0, "", $category, '', '', 1, '', 0, $where, $count_only);

   // datetime is a column in the javascript definition of the events jtable, but of course the db doesn't know this
   // so we need to change this into something known, but since eme_get_events can work with "ASC/DESC" only and
   // then sorts on date/time, we just remove the word here
   $jtSorting = str_replace("datetime ","",$jtSorting);
   $events = eme_get_events ( $jtPageSize, $scope, $jtSorting, $jtStartIndex, "", $category, '', '', 1, '', 0, $where);
   $event_status_array = eme_status_array ();
   $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);

   $rows=array();
   foreach ($events as $event) {
      $line=array();
      $line['event_id'] = $event['event_id'];
      $date_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);

      $line['event_name'] = "<strong><a href='".wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id']),'eme_events','eme_admin_nonce')."' title='".__('Edit event','events-made-easy')."'>".eme_trans_esc_html($event['event_name'])."</a></strong>";
         if (!empty($event['event_category_ids'])) {
            $categories = explode(',', $event['event_category_ids']);
            $line['event_name'] .= "<br /><span class='eme_categories_small' title='".__('Category','events-made-easy')."'>";
            $cat_names=array();
            foreach($categories as $cat){
               $category = eme_get_category($cat);
               if($category)
                  $cat_names[] = eme_trans_esc_html($category['category_name']);
            }
            $line['event_name'] .= implode(', ',$cat_names);
            $line['event_name'] .= "</span>";
         }
         if ($event['event_rsvp']) {
            $booked_seats = eme_get_booked_seats($event['event_id']);
            $available_seats = eme_get_available_seats($event['event_id']);
            $pending_seats = eme_get_pending_seats($event['event_id']);
            $total_seats = eme_get_total($event['event_seats']);
            if (eme_is_multi($event['event_seats'])) {
               $available_seats_string = $available_seats.' ('.eme_convert_array2multi(eme_get_available_multiseats($event['event_id'])).')';
               $pending_seats_string = $pending_seats.' ('.eme_convert_array2multi(eme_get_pending_multiseats($event['event_id'])).')';
               $total_seats_string = $total_seats .' ('.$event['event_seats'].')';
            } else {
               $available_seats_string = $available_seats;
               $pending_seats_string = $pending_seats;
               $total_seats_string = $total_seats;
            }
            $line['event_name'] .= "<br />".__('RSVP Info: ','events-made-easy').__('Free: ','events-made-easy').$available_seats_string.", ";
            if ($pending_seats >0)
               $line['event_name'] .= "<a href='".admin_url("admin.php?page=eme-registration-approval&amp;event_id=".$event['event_id'])."'>".__('Pending: ','events-made-easy')."$pending_seats_string</a>, ";
            $line['event_name'] .= __('Max: ','events-made-easy').$total_seats_string;
            if ($booked_seats>0 || $pending_seats >0) {
               $printable_address = admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=".$event['event_id']);
               $csv_address = admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id']);
               $line['event_name'] .= " <br />(<a id='booking_printable_".$event['event_id']."' href='$printable_address'>".__('Printable view','events-made-easy')."</a>)";
               $line['event_name'] .= " (<a id='booking_csv_".$event['event_id']."' href='$csv_address'>".__('CSV export','events-made-easy')."</a>)";
            }
         }

      if (empty($event['location_name'])) {
	      $line['location_name']="";
      } else {
	      $line['location_name']= "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=edit_location&amp;location_id=".$event['location_id']),'eme_locations','eme_admin_nonce')."' title='".__('Edit location','events-made-easy')."'>".eme_trans_esc_html($event['location_name'])."</a>";
	      if (!$event['location_latitude'] && !$event['location_longitude']) {
		      $line['location_name'] .= "&nbsp;<img style='vertical-align: middle;' src='".EME_PLUGIN_URL."images/warning.png' alt='warning' title='".__('Location map coordinates are empty! Please edit the location to correct this, otherwise it will not show correctly on your website.','events-made-easy')."'>";
	      }
      }

      if (!empty($event['location_address1']) || !empty($event['location_address2']))
         $line['location_name'] .= "<br />". eme_trans_esc_html($event['location_address1']) ." ".eme_trans_esc_html($event['location_address2']);
      if (!empty($event['location_city']) || !empty($event['location_state']) || !empty($event['location_zip']) || !empty($event['location_country']))
         $line['location_name'] .= "<br />". eme_trans_esc_html($event['location_city']) ." ".eme_trans_esc_html($event['location_state'])." ".eme_trans_esc_html($event['location_zip'])." ".eme_trans_esc_html($event['location_country']);

      if (isset ($event_status_array[$event['event_status']])) {
         $line['event_status'] = $event_status_array[$event['event_status']];
         $event_url = eme_event_url($event);
         if ($event['event_status'] == STATUS_DRAFT)
            $line['event_status'] .= "<br /> <a href='$event_url'>".__('Preview event','events-made-easy')."</a>";
         else
            $line['event_status'] .= "<br /> <a href='$event_url'>".__('View event','events-made-easy')."</a>";
      }

      $line['copy'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=duplicate_event&amp;event_id=".$event['event_id']),'eme_events','eme_admin_nonce')."' title='".__('Duplicate this event','events-made-easy')."'><img src='".EME_PLUGIN_URL."images/copy_24.png'/></a>";

      if ($event['event_rsvp']) {
         if ($event['registration_requires_approval'])
            $page="eme-registration-approval";
         else
            $page="eme-registration-seats";

         $line['rsvp'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=$page&amp;eme_admin_action=newRegistration&amp;event_id=".$event['event_id']),'eme_rsvp','eme_admin_nonce')."' title='".__('Add registration for this event','events-made-easy')."'>".__('RSVP','events-made-easy')."</a>";
      } else {
         $line['rsvp'] = "";
      }

      $localized_start_date = eme_localized_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone);
      $localized_start_time = eme_localized_time($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone);
      $localized_end_date = eme_localized_date($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone);
      $localized_end_time = eme_localized_time($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone);
      $line['datetime']= $localized_start_date;
      if ($localized_end_date !='' && $localized_end_date!=$localized_start_date)
         $line['datetime'] .=" - " . $localized_end_date;
      $line['datetime'] .= "<br />";
      if ($event['event_properties']['all_day']==1)
         $line['datetime'] .=__('All day','events-made-easy');
      else
         $line['datetime'] .= "$localized_start_time - $localized_end_time";
      if ($date_obj->lessThan($eme_date_obj_now)) {
         $line['datetime'] = "<span style='text-decoration: line-through;'>".$line['datetime']."</span>";
      }

      if ($event['recurrence_id']) {
         $recurrence_desc = eme_get_recurrence_desc ( $event['recurrence_id'] );
         $line['recinfo'] = "$recurrence_desc <br /> <a href='".wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_recurrence&amp;recurrence_id=".$event['recurrence_id']),'eme_events','eme_admin_nonce')."'>";
         $line['recinfo'] .= __('Edit Recurrence','events-made-easy');
         $line['recinfo'] .= "</a>";
      }

      $rows[]=$line;
   }

   //if ($events_count>0) {
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = $events_count;
      $jTableResult['Records'] = $rows; 
   //} else {
      //$jTableResult['Result'] = "Error";
      //$jTableResult['Message'] = __('No events yet!','events-made-easy');
   //}
   print json_encode($jTableResult);
   wp_die();

}

function eme_ajax_manage_events() {
   check_ajax_referer('eme_events','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteEvents':
              eme_ajax_action_events_delete();
              break;
         case 'deleteRecurrence':
              eme_ajax_action_recurrences_delete();
              break;
         case 'publicEvents':
              eme_ajax_action_events_status(STATUS_PUBLIC);
              break;
         case 'privateEvents':
              eme_ajax_action_events_status(STATUS_PRIVATE);
              break;
         case 'draftEvents':
              eme_ajax_action_events_status(STATUS_DRAFT);
              break;
      }
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('No action defined!','events-made-easy');
      print json_encode($jTableResult);
   }
   wp_die();
}
   
function eme_ajax_action_events_delete() {
   $postvar="event_id";
   check_ajax_referer('eme_events','eme_admin_nonce');

   if (current_user_can(get_option('eme_cap_edit_events')) && isset($_REQUEST[$postvar])) {
      // check the POST var
      $ids_arr=explode(',',$_REQUEST[$postvar]);
      $res=eme_delete_events($ids_arr);
      if ($res==0) {
         $jTableResult['Result'] = "OK";
      } else {
         $jTableResult['Result'] = "Error";
         $jTableResult['Message'] = __('Corrupt event ids detected!','events-made-easy');
      }
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
   }
   print json_encode($jTableResult);
}


function eme_ajax_action_recurrences_delete() {
   $postvar="event_id";
   if (current_user_can(get_option('eme_cap_edit_events')) && isset($_REQUEST[$postvar])) {
      // check the POST var
      $ids_arr=explode(',',$_REQUEST[$postvar]);

      $res=eme_delete_recurrences($ids_arr);
      if ($res==0) {
         $jTableResult['Result'] = "OK";
      } else {
         $jTableResult['Result'] = "Error";
         $jTableResult['Message'] = __('Corrupt event ids detected!','events-made-easy');
      }
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
   }
   print json_encode($jTableResult);
}

function eme_ajax_action_events_status($status) {
   $postvar="event_id";
   if (current_user_can(get_option('eme_cap_edit_events')) && isset($_REQUEST[$postvar])) {
      // check the POST var
      $ids_arr=explode(',',$_REQUEST[$postvar]);

      if (eme_array_integers($ids_arr)) {
         eme_change_event_status($ids_arr,$status);
         $jTableResult['Result'] = "OK";
      } else {
         $jTableResult['Result'] = "Error";
         $jTableResult['Message'] = __('Corrupt event ids detected!','events-made-easy');
      }
   } else {
      $jTableResult['Result'] = "Error";
      $jTableResult['Message'] = __('Access denied!','events-made-easy');
   }
   print json_encode($jTableResult);
}

function eme_delete_events($ids_arr) {
   if (eme_array_integers($ids_arr)) {
      foreach ( $ids_arr as $event_id ) {
         $tmp_event = array();
         $tmp_event = eme_get_event ( $event_id );
         if ($tmp_event['recurrence_id']>0) {
            # if the event is part of a recurrence and it is the last event of the recurrence, delete the recurrence
            # else just delete the singe event
            if (eme_recurrence_count($tmp_event['recurrence_id'])==1) {
               $tmp_recurrence=eme_get_recurrence($tmp_event['recurrence_id']);
               eme_db_delete_recurrence ($tmp_recurrence,$tmp_event );
            } else {
               eme_db_delete_event($tmp_event);
            }
         } else {
            eme_db_delete_event($tmp_event);
         }
      }
      return 0;
   } else {
      return 1;
   }
}

function eme_delete_recurrences($ids_arr) {
   if (eme_array_integers($ids_arr)) {
      foreach ( $ids_arr as $event_id ) {
         $tmp_event = array();
         $tmp_event = eme_get_event($event_id);
         if ($tmp_event['recurrence_id']>0) {
            $tmp_recurrence=eme_get_recurrence($tmp_event['recurrence_id']);
            eme_db_delete_recurrence ($tmp_recurrence,$tmp_event );
         }
      }
      return 0;
   } else {
      return 1;
   }
}

?>
