<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_get_recurrence_days($recurrence){
   global $eme_timezone;

   $matching_days = array(); 
   
   if($recurrence['recurrence_freq'] == 'specific') {
   	$specific_days = explode(",", $recurrence['recurrence_specific_days']);
	   foreach ($specific_days as $day) {
                array_push($matching_days, $day);
	   }
	   return $matching_days;
   }
 
   $start_date_obj = new ExpressiveDate($recurrence['recurrence_start_date'],$eme_timezone);
   $end_date_obj = new ExpressiveDate($recurrence['recurrence_end_date'],$eme_timezone);

   $holidays=array();
   if (isset($recurrence['holidays_id']) && $recurrence['holidays_id']>0) {
      $holidays=eme_get_holiday_listinfo($recurrence['holidays_id']);
   }
 
   $last_week_start = array(25, 22, 25, 24, 25, 24, 25, 25, 24, 25, 24, 25);
   $weekdays = explode(",", $recurrence['recurrence_byday']);
   
   $counter = 0;
   $daycounter = 0;
   $weekcounter = 0;
   $monthcounter=0;
   $start_monthday = $start_date_obj->format('j');
   $cycle_date_obj = $start_date_obj->copy();

   while ($cycle_date_obj->lessOrEqualTo($end_date_obj)) {
      $monthweek = floor((($cycle_date_obj->format('d')-1)/7))+1;
      $ymd=$cycle_date_obj->getDate();

      // skip holidays
      if (!empty($holidays) && isset($holidays[$ymd])) {
         $cycle_date_obj->addOneDay();
         continue;
      }

      if($recurrence['recurrence_freq'] == 'daily') {
         if($daycounter % $recurrence['recurrence_interval']== 0)
            array_push($matching_days, $ymd);
      }

      if($recurrence['recurrence_freq'] == 'weekly') {
         if (!$recurrence['recurrence_byday'] && eme_iso_N_date_value($cycle_date_obj)==eme_iso_N_date_value($start_date_obj)) {
         // no specific days given, so we use 7 days as interval
            //if($daycounter % 7*$recurrence['recurrence_interval'] == 0 ) {
            if($weekcounter % $recurrence['recurrence_interval'] == 0 )
               array_push($matching_days, $ymd);
         } elseif (in_array(eme_iso_N_date_value($cycle_date_obj), $weekdays )) {
         // specific days, so we only check for those days
            if($weekcounter % $recurrence['recurrence_interval'] == 0 )
               array_push($matching_days, $ymd);
         }
      }

      if($recurrence['recurrence_freq'] == 'monthly') { 
         $monthday = $cycle_date_obj->format('j');
         $month = $cycle_date_obj->format('n');
         // if recurrence_byweekno=0 ==> means to use the startday as repeating day
         if ( $recurrence['recurrence_byweekno'] == 0) {
            if ($monthday == $start_monthday) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $ymd);
               $counter++;
            }
         } elseif (in_array(eme_iso_N_date_value($cycle_date_obj), $weekdays )) {
               if(($recurrence['recurrence_byweekno'] == -1) && ($monthday >= $last_week_start[$month-1])) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $ymd);
            } elseif($recurrence['recurrence_byweekno'] == $monthweek) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $ymd);
            }
            $counter++;
         }
      }
      $cycle_date_obj->addOneDay();
      $daycounter++;
      if ($daycounter%7==0) {
         $weekcounter++;
      }
      if ($cycle_date_obj->format('j')==1) {
         $monthcounter++;
      }
   }
   
   return $matching_days ;
}

// backwards compatible: eme_insert_recurrent_event renamed to eme_db_insert_recurrence
function eme_insert_recurrent_event($event, $recurrence) {
   return eme_db_insert_recurrence($recurrence,$event);
}

function eme_db_insert_recurrence($recurrence,$event){
   global $wpdb, $eme_timezone;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
      
   $recurrence['creation_date']=current_time('mysql', false);
   $recurrence['modif_date']=current_time('mysql', false);
   $recurrence['creation_date_gmt']=current_time('mysql', true);
   $recurrence['modif_date_gmt']=current_time('mysql', true);
   // never try to update a autoincrement value ...
   if (isset($recurrence['recurrence_id']))
      unset ($recurrence['recurrence_id']);

   // some sanity checks
   if ($recurrence['recurrence_freq'] != "specific") {
      $eme_date_obj1 = new ExpressiveDate($recurrence['recurrence_start_date'],$eme_timezone);
      $eme_date_obj2 = new ExpressiveDate($recurrence['recurrence_end_date'],$eme_timezone);
      if ($eme_date_obj2->lessThan($eme_date_obj1)) {
         $recurrence['recurrence_end_date']=$recurrence['recurrence_start_date'];
      }
   }

   //$wpdb->show_errors(true);
   $wpdb->insert($recurrence_table, $recurrence);
   $recurrence_id = $wpdb->insert_id;

   //print_r($recurrence);

   $recurrence['recurrence_id'] = $recurrence_id;
   $event['recurrence_id'] = $recurrence['recurrence_id'];
   $count=eme_insert_events_for_recurrence($recurrence,$event);
   if ($count) {
      if (has_action('eme_insert_recurrence_action')) do_action('eme_insert_recurrence_action',$event,$recurrence);
   } else {
      eme_db_delete_recurrence($recurrence);
      return false;
   }
   return $count;
}

function eme_insert_events_for_recurrence($recurrence,$event) {
   global $wpdb, $eme_timezone;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $matching_days = eme_get_recurrence_days($recurrence);
   sort($matching_days);
   $eme_date_obj1 = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
   if ($event['event_end_date']=='') {
      $DifferenceInSeconds = 0;
   } else {
      $eme_date_obj2 = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
      $DifferenceInSeconds = abs($eme_date_obj2->getDifferenceInSeconds($eme_date_obj1));
   }
   $count=0;
   foreach($matching_days as $day) {
      $event['event_start_date'] = $day;
      $eme_date_obj = new ExpressiveDate($day." ".$event['event_start_time'],$eme_timezone);
      $eme_date_obj->addSeconds($DifferenceInSeconds);
      $event['event_end_date'] = $eme_date_obj->getDate();
      eme_db_insert_event($event,1);
      $count++;
   }
   return $count;
}

// backwards compatible: eme_update_recurrence renamed to eme_db_update_recurrence
function eme_update_recurrence($event, $recurrence) {
   return eme_db_update_recurrence($recurrence,$event);
}

function eme_db_update_recurrence($recurrence,$event) {
   global $wpdb, $eme_timezone;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;

   $recurrence['modif_date']=current_time('mysql', false);
   $recurrence['modif_date_gmt']=current_time('mysql', true);

   // some sanity checks
   $eme_date_obj1 = new ExpressiveDate($recurrence['recurrence_start_date'],$eme_timezone);
   $eme_date_obj2 = new ExpressiveDate($recurrence['recurrence_end_date'],$eme_timezone);
   if ($eme_date_obj2->lessThan($eme_date_obj1)) {
      $recurrence['recurrence_end_date']=$recurrence['recurrence_start_date'];
   }

   $where = array('recurrence_id' => $recurrence['recurrence_id']);
   $wpdb->show_errors(true);
   $wpdb->update($recurrence_table, $recurrence, $where); 
   $wpdb->show_errors(false);
   $event['recurrence_id'] = $recurrence['recurrence_id'];
   $count=eme_update_events_for_recurrence($recurrence,$event); 
   if ($count) {
      if (has_action('eme_update_recurrence_action'))
         do_action('eme_update_recurrence_action',$event,$recurrence);
      return $count;
   } else {
      eme_db_delete_recurrence($recurrence);
      return false;
   }
}

function eme_update_events_for_recurrence($recurrence,$event) {
   global $wpdb, $eme_timezone;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $matching_days = eme_get_recurrence_days($recurrence);
   //print_r($matching_days);  
   sort($matching_days);
   $eme_date_obj1 = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
   if ($event['event_end_date']=='') {
      $DifferenceInSeconds = 0;
   } else {
      $eme_date_obj2 = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
      $DifferenceInSeconds = abs($eme_date_obj2->getDifferenceInSeconds($eme_date_obj1));
   }


   // 2 steps for updating events for a recurrence:
   // First step: check the existing events and if they still match the recurrence days, update them
   //       otherwise delete the old event
   // Reason for doing this: we want to keep possible booking data for a recurrent event as well
   // and just deleting all current events for a recurrence and inserting new ones would break the link
   // between booking id and event id
   // Second step: check all days of the recurrence and if no event exists yet, insert it
   $sql = $wpdb->prepare("SELECT * FROM $events_table WHERE recurrence_id = %d",$recurrence['recurrence_id']);
   $events = $wpdb->get_results($sql, ARRAY_A);


   $count=0;
   // Doing step 1
   foreach($events as $existing_event) {
      $update_done=0;
      foreach($matching_days as $day) {
         if (!$update_done && $existing_event['event_start_date'] == $day) {
            $event['event_start_date'] = $day;
	    $eme_date_obj = new ExpressiveDate($day." ".$event['event_start_time'],$eme_timezone);
	    $eme_date_obj->addSeconds($DifferenceInSeconds);
	    $event['event_end_date'] = $eme_date_obj->getDate();
            eme_db_update_event($event, $existing_event['event_id'], 1); 
            $update_done=1; 
	    $count++;
            continue;
         }
      }
      if (!$update_done) {
         eme_db_delete_event($existing_event,1);
      }
   }
   // Doing step 2
   foreach($matching_days as $day) {
      $insert_needed=1;
      $event['event_start_date'] = $day;
      $eme_date_obj = new ExpressiveDate($day." ".$event['event_start_time'],$eme_timezone);
      $eme_date_obj->addSeconds($DifferenceInSeconds);
      $event['event_end_date'] = $eme_date_obj->getDate();
      foreach($events as $existing_event) {
         if ($insert_needed && $existing_event['event_start_date'] == $event['event_start_date']) {
            $insert_needed=0;
         }
      }
      if ($insert_needed==1) {
	 $count++;
         eme_db_insert_event($event,1);
      }
   }
   return $count;
}

function eme_db_delete_recurrence($recurrence,$event=NULL) {
   global $wpdb;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $recurrence_id=$recurrence['recurrence_id'];
   $sql = $wpdb->prepare("DELETE FROM $recurrence_table WHERE recurrence_id = %d",$recurrence_id);
   $wpdb->query($sql);
   eme_remove_events_for_recurrence_id($recurrence_id);
   $image_basename= IMAGE_UPLOAD_DIR."/recurrence-".$recurrence_id;
   eme_delete_image_files($image_basename);
   if (!is_null($event) && has_action('eme_delete_recurrence_action'))
      do_action('eme_delete_recurrence_action',$event,$recurrence);
}

function eme_remove_events_for_recurrence_id($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $events_table WHERE recurrence_id = %d",$recurrence_id);
   $wpdb->query($sql);
}

function eme_get_recurrence_first_eventid($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE recurrence_id = %d LIMIT 1",$recurrence_id);
   return $wpdb->get_var($sql);
}

function eme_get_recurrence_eventids($recurrence_id,$future_only=0) {
   global $wpdb, $eme_timezone;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   if ($future_only) {
      $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
      $today = $eme_date_obj->format('Y-m-d');
      $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE recurrence_id = %d AND event_start_date > %s ORDER BY event_start_date ASC, event_start_time ASC",$recurrence_id,$today);
   } else {
      $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE recurrence_id = %d ORDER BY event_start_date ASC, event_start_time ASC",$recurrence_id);
   }
   return $wpdb->get_col($sql);
}

function eme_get_recurrence($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $recurrence_table WHERE recurrence_id = %d",$recurrence_id);
   $recurrence = $wpdb->get_row($sql, ARRAY_A);

   // now add the info that has no column in the recurrence table
   // for that, we take the info from the first occurence
   $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE recurrence_id = %d ORDER BY event_start_date ASC, event_start_time ASC LIMIT 1",$recurrence_id);
   $event_id = $wpdb->get_var($sql);
   $event = eme_get_event($event_id);
   foreach ($event as $key=>$val) {
      $recurrence[$key]=$val;
   }

   // now add the location info
   $location = eme_get_location($recurrence['location_id']);
   $recurrence['location_name'] = $location['location_name'];
   $recurrence['location_address1'] = $location['location_address1'];
   $recurrence['location_address2'] = $location['location_address2'];
   $recurrence['location_city'] = $location['location_city'];
   $recurrence['location_state'] = $location['location_state'];
   $recurrence['location_zip'] = $location['location_zip'];
   $recurrence['location_country'] = $location['location_country'];
   $recurrence['recurrence_description'] = eme_get_recurrence_desc($recurrence_id);
   return $recurrence;
}

function eme_get_recurrence_desc($recurrence_id) {
   global $wpdb, $eme_timezone;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $recurrence_table WHERE recurrence_id = %d",$recurrence_id);
   $recurrence = $wpdb->get_row($sql, ARRAY_A);

   $weekdays_name = array(__('Monday', 'events-made-easy'),__('Tuesday', 'events-made-easy'),__('Wednesday', 'events-made-easy'),__('Thursday', 'events-made-easy'),__('Friday', 'events-made-easy'),__('Saturday', 'events-made-easy'),__('Sunday', 'events-made-easy'));
   $monthweek_name = array('1' => __('the first %s of the month', 'events-made-easy'),'2' => __('the second %s of the month', 'events-made-easy'), '3' => __('the third %s of the month', 'events-made-easy'), '4' => __('the fourth %s of the month', 'events-made-easy'), '5' => __('the fifth %s of the month', 'events-made-easy'), '-1' => __('the last %s of the month', 'events-made-easy'));
   $output = sprintf (__('From %1$s to %2$s', 'events-made-easy'),  eme_localized_date($recurrence['recurrence_start_date']." ".$eme_timezone), eme_localized_date($recurrence['recurrence_end_date']." ".$eme_timezone)).", ";
   if ($recurrence['recurrence_freq'] == 'daily')  {
      $freq_desc =__('everyday', 'events-made-easy');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc = sprintf (__("every %s days", 'events-made-easy'), $recurrence['recurrence_interval']);
      }
   }
   elseif ($recurrence['recurrence_freq'] == 'weekly')  {
      if (!$recurrence['recurrence_byday']) {
         # no weekdays given for the recurrence, so we use the
         # day of the week of the startdate as reference
         $recurrence['recurrence_byday']= eme_localized_date($recurrence['recurrence_start_date']." ".$eme_timezone,'w');
         # Sunday is 7, not 0
         if ($recurrence['recurrence_byday']==0)
            $recurrence['recurrence_byday']=7; 
      }
      $weekday_array = explode(",", $recurrence['recurrence_byday']);
      $natural_days = array();
      foreach($weekday_array as $day)
         array_push($natural_days, $weekdays_name[$day-1]);
      $and_string=__(" and ",'events-made-easy');
      $output .= implode($and_string, $natural_days);
      $freq_desc =", ".__('every week', 'events-made-easy');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc = ", ".sprintf (__("every %s weeks", 'events-made-easy'), $recurrence['recurrence_interval']);
      }
   } 
   elseif ($recurrence['recurrence_freq'] == 'monthly')  {
      if (!$recurrence['recurrence_byday']) {
         # no monthday given for the recurrence, so we use the
         # day of the month of the startdate as reference
         $recurrence['recurrence_byday']= eme_localized_date($recurrence['recurrence_start_date']." ".$eme_timezone,'e');
      }
      $weekday_array = explode(",", $recurrence['recurrence_byday']);
      $natural_days = array();
      foreach($weekday_array as $day)
         array_push($natural_days, $weekdays_name[$day-1]);
      $and_string=__(" and ",'events-made-easy');
      $freq_desc = sprintf (($monthweek_name[$recurrence['recurrence_byweekno']]), implode($and_string, $natural_days));
      $freq_desc =", ".__('every month', 'events-made-easy');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc .= ", ".sprintf (__("every %s months",'events-made-easy'), $recurrence['recurrence_interval']);
      }
   } elseif ($recurrence['recurrence_freq'] == 'specific')  {
      if (is_admin()) {
         return __("Specific days",'events-made-easy');
      } else {
	 $specific_days = explode(",", $recurrence['recurrence_specific_days']);
         $natural_days = array();
         foreach ($specific_days as $day) {
              $natural_days[]=eme_localized_date($day." ".$eme_timezone);
         }
         return implode(", ", $natural_days);
      }
   } else {
      $freq_desc = "";
   }
   $output .= $freq_desc;
   return  $output;
}

function eme_recurrence_count($recurrence_id) {
   # return the number of events for an recurrence
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT COUNT(*) FROM $events_table WHERE recurrence_id = %d",$recurrence_id);
   return $wpdb->get_var($sql);
}

function eme_iso_N_date_value($date_obj) {
   // date("N", $cycle_date)
   $n = $date_obj->format('w');
   if ($n == 0)
      $n = 7;
   return $n;
}
?>
