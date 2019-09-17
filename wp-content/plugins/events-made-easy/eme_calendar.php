<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_get_calendar_shortcode($atts) { 
   extract(shortcode_atts(array(
         'category' => 0,
         'notcategory' => 0,
         'full' => 0,
         'month' => '',
         'year' => '',
         'echo' => 0,
         'long_events' => 0,
         'author' => '',
         'contact_person' => '',
         'location_id' => '',
         'template_id' => 0,
         'holiday_id' => 0,
         'htmltable' => 1,
         'htmldiv' => 0,
         'weekdays' => ''
      ), $atts)); 
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $full = ($full==="true" || $full==="1") ? true : $full;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $full = ($full==="false" || $full==="0") ? false : $full;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;

   // this allows people to use specific months/years to show the calendar on
   if(isset($_GET['calmonth']) && $_GET['calmonth'] != '')   {
      $month =  eme_sanitize_request($_GET['calmonth']) ;
   }
   if(isset($_GET['calyear']) && $_GET['calyear'] != '')   {
      $year =  eme_sanitize_request($_GET['calyear']) ;
   }

   // the filter list overrides the settings
   if (isset($_REQUEST['eme_eventAction']) && $_REQUEST['eme_eventAction'] == 'filter') {
      if (isset($_REQUEST['eme_scope_filter'])) {
         $scope = eme_sanitize_request($_REQUEST['eme_scope_filter']);
         if (preg_match ( "/^([0-9]{4})-([0-9]{2})-[0-9]{2}--[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope, $matches )) {
            $year=$matches[1];
            $month=$matches[2];
         }
      }
      if (isset($_REQUEST['eme_loc_filter'])) {
         if (is_array($_REQUEST['eme_loc_filter']))
            $location_id=join(',',eme_sanitize_request($_REQUEST['eme_loc_filter']));
         else
            $location_id=eme_sanitize_request($_REQUEST['eme_loc_filter']);
      }
      if (isset($_REQUEST['eme_city_filter'])) {
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
      if (isset($_REQUEST['eme_cat_filter'])) {
         if (is_array($_REQUEST['eme_cat_filter']))
            $category=join(',',eme_sanitize_request($_REQUEST['eme_cat_filter']));
         else
            $category=eme_sanitize_request($_REQUEST['eme_cat_filter']);
      }
   }

   $result = eme_get_calendar("full={$full}&month={$month}&year={$year}&echo={$echo}&long_events={$long_events}&category={$category}&author={$author}&contact_person={$contact_person}&location_id={$location_id}&notcategory={$notcategory}&template_id={$template_id}&weekdays={$weekdays}&holiday_id={$holiday_id}&htmltable={$htmltable}&htmldiv={$htmldiv}");
   return $result;
}

function eme_get_calendar($args="") {
   global $wp_locale;
   global $wpdb, $eme_timezone;
   // the calendar is being used, so we need the jquery for the calendar
   global $eme_need_calendar_js;
   $eme_need_calendar_js=1;

   $defaults = array(
      'category' => 0,
      'notcategory' => 0,
      'full' => 0,
      'month' => '',
      'year' => '',
      'echo' => 0,
      'long_events' => 0,
      'author' => '',
      'contact_person' => '',
      'location_id' => '',
      'template_id' => 0,
      'holiday_id' => 0,
      'htmltable' => 1,
      'htmldiv' => 0,
      'weekdays' => ''
   );
   $r = wp_parse_args( $args, $defaults );
   extract( $r );
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $full = ($full==="true" || $full==="1") ? true : $full;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $full = ($full==="false" || $full==="0") ? false : $full;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;

   if (!empty($weekdays))
      $weekday_arr=explode(',',$weekdays);
   else
      $weekday_arr=array();
   
   // this comes from global wordpress preferences
   $start_of_week = get_option('start_of_week');

   // allow month=next_month
   if ($month=="next_month") {
      $eme_date_obj_tmp=new ExpressiveDate(null,$eme_timezone);
      $eme_date_obj_tmp->startOfMonth()->addOneMonth();
      $month = $eme_date_obj_tmp->format('m');
   }
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);

   if (get_option('eme_use_client_clock') && isset($_SESSION['eme_client_mday']) && isset($_SESSION['eme_client_month']) && isset($_SESSION['eme_client_fullyear'])) {
      // these come from client unless their clock is wrong
      $iNowDay= sprintf("%02d",$_SESSION['eme_client_mday']);
      $iNowMonth= sprintf("%02d",$_SESSION['eme_client_month']);
      $iNowYear= sprintf("%04d",$_SESSION['eme_client_fullyear']);
   } else {
      // Get current year, month and day
      list($iNowYear, $iNowMonth, $iNowDay) = explode('-', $eme_date_obj->getDate());
   }

   $iSelectedYear = $year;
   $iSelectedMonth = $month;
   if ($iSelectedMonth == '') $iSelectedMonth = $iNowMonth;
   if ($iSelectedYear == '') $iSelectedYear = $iNowYear;
   $iSelectedMonth = sprintf("%02d",$iSelectedMonth);

   // Get name and number of days of specified month
   $eme_date_obj->setDay(1);
   $eme_date_obj->setMonth($iSelectedMonth);
   $eme_date_obj->setYear($iSelectedYear);
   $iDaysInMonth = (int)$eme_date_obj->getDaysInMonth();
   $fullMonthName = $wp_locale->get_month($eme_date_obj->format('m'));
   $shortMonthName = $wp_locale->get_month_abbrev($fullMonthName);
   // Get friendly month name, but since DateTime::format doesn't respect the locale, we need eme_localized_date
   if ($full)
      $sMonthName = $fullMonthName;
   else
      $sMonthName = $shortMonthName;
   // take into account some locale info: some always best show full month name, some show month after year, some have a year suffix
   $locale_code = substr ( get_locale (), 0, 2 );
   $showMonthAfterYear=0;
   $yearSuffix="";
   switch($locale_code) { 
      case "hu": $showMonthAfterYear=1;break;
      case "ja": $showMonthAfterYear=1;$sMonthName = $fullMonthName;$yearSuffix="年";break;
      case "ko": $showMonthAfterYear=1;$sMonthName = $fullMonthName;$yearSuffix="년";break;
      case "zh": $showMonthAfterYear=1;$sMonthName = $fullMonthName;$yearSuffix="年";break;
   }
   if ($showMonthAfterYear)
         $cal_datestring="$iSelectedYear$yearSuffix $sMonthName";
   else
         $cal_datestring="$sMonthName $iSelectedYear$yearSuffix";

   if ($full && has_filter('eme_cal_full_yearmonth')) $cal_datestring=apply_filters('eme_cal_full_yearmonth',$cal_datestring, $iSelectedMonth, $iSelectedYear);
   if (!$full && has_filter('eme_cal_small_yearmonth')) $cal_datestring=apply_filters('eme_cal_small_yearmonth',$cal_datestring, $iSelectedMonth, $iSelectedYear);

   // Get previous year and month
   $iPrevYear = $iSelectedYear;
   $iPrevMonth = $iSelectedMonth - 1;
   if ($iPrevMonth <= 0) {
	   $iPrevYear--;
	   $iPrevMonth = 12; // set to December
   }
   $iPrevMonth = sprintf("%02d",$iPrevMonth);

   // Get next year and month
   $iNextYear = $iSelectedYear;
   $iNextMonth = $iSelectedMonth + 1;
   if ($iNextMonth > 12) {
	   $iNextYear++;
	   $iNextMonth = 1;
   }
   $iNextMonth = sprintf("%02d",$iNextMonth);

   // Get number of days of previous month
   $eme_date_obj2=new ExpressiveDate(null,$eme_timezone);
   $eme_date_obj2->setDay(1);
   $eme_date_obj2->setMonth($iPrevMonth);
   $eme_date_obj2->setYear($iPrevYear);
   $iPrevDaysInMonth = (int)$eme_date_obj2->getDaysInMonth();

   // Get numeric representation of the day of the week of the first day of specified (current) month
   // remember: first day of week is a Sunday
   // if you want the day of the week to begin on Monday: start_of_week=1, Tuesday: start_of_week=2, etc ...
   // So, if e.g. the month starts on a Sunday and start_of_week=1 (Monday), then $iFirstDayDow is 6
   $iFirstDayDow = (int)$eme_date_obj->getDayOfWeekAsNumeric() - $start_of_week;
   if ($iFirstDayDow<0) $iFirstDayDow+=7;

   // On what day the previous month begins
   if ($iFirstDayDow>0)
      $iPrevShowFrom = $iPrevDaysInMonth - $iFirstDayDow + 1;
   else
      $iPrevShowFrom = $iPrevDaysInMonth;

  // we'll look for events in the requested month and 7 days before and after
   $calbegin="$iPrevYear-$iPrevMonth-$iPrevShowFrom";
   $calend="$iNextYear-$iNextMonth-07";
   $events = eme_get_events(0, "$calbegin--$calend", "ASC", 0, $location_id, $category , $author , $contact_person, 1, $notcategory );

   // because we want the eventfull days, we don't take the start/end time into account
   // since our loop always adds a day to the start object, and if the end time would be lower than the start time
   // the last day wouldn't be taken into account
   $eventful_days = array();
   if ($events) {   
      // go through the events and slot them into the right d-m index
      foreach($events as $event) {
         if ($event['event_status'] == STATUS_PRIVATE && !is_user_logged_in()) {
            continue;
         }
         $eme_date_obj_end=new ExpressiveDate($event['event_end_date'],$eme_timezone);
         $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
         // when hiding past events, we hide those which end date is lower than today, but since we don't take
         // the end hour/min/sec into account, we use the today function to set the compared hour/min/sec also to 0
         if (get_option('eme_cal_hide_past_events') && $eme_date_obj_end->lessThan($eme_date_obj_now->today())) {
            continue;
         }

         // if $long_events is set then show a date as eventful if there is an multi-day event which runs during that day
         if( $long_events ) {
            $eme_date_obj_tmp=new ExpressiveDate($event['event_start_date'],$eme_timezone);
            if ($eme_date_obj_end->lessThan($eme_date_obj_tmp))
                $eme_date_obj_end=$eme_date_obj_tmp->copy();
            while ($eme_date_obj_tmp->lessOrEqualTo($eme_date_obj_end)) {
               $event_eventful_date = $eme_date_obj_tmp->getDate();
               //Only show events on the day that they start
               if (isset($eventful_days[$event_eventful_date]) && is_array($eventful_days[$event_eventful_date]) ) {
                  $eventful_days[$event_eventful_date][] = $event;
               } else {
                  $eventful_days[$event_eventful_date] = array($event);
               }  
               $eme_date_obj_tmp->addOneDay();
            }
         } else {
            //Only show events on the day that they start
            if (isset($eventful_days[$event['event_start_date']]) && is_array($eventful_days[$event['event_start_date']]) ) {
               $eventful_days[$event['event_start_date']][] = $event; 
            } else {
               $eventful_days[$event['event_start_date']] = array($event);
            }
         }
      }
   }

   // we found all the events for the wanted days, now get them in the correct format with a good link
   if ($template_id)
      $event_format = eme_get_template_format($template_id);
   else
      $event_format = get_option('eme_full_calendar_event_format' );

   $event_title_format = eme_br2nl(get_option('eme_small_calendar_event_title_format'));
   $event_title_separator_format = eme_br2nl(get_option('eme_small_calendar_event_title_separator'));
   $cells = array() ;
   $holiday_titles = array();
   if ($holiday_id) {
      $holidays=eme_get_holiday_listinfo($holiday_id);
      if ($holidays) {
	   foreach ($holidays as $day_key=>$info) {
		   if (!empty($info['name'])) {
                           $holiday_title=trim(eme_esc_html($info['name']));
			   $eme_holiday_class="eme-cal-holidays";
			   if (empty($info['class']))
				   $class=$eme_holiday_class;
			   else
				   $class=$info['class'];

			   // if there's an event that day, the day-number is a link and will be set later on
			   // otherwise we set the day-number
			   if (isset($eventful_days[$day_key])) {
                                   $holiday_titles[$day_key]=$holiday_title;
				   $cells[$day_key]="<span class='$class'>".$info['name']."</span><br />";
			   } else {
				   $event_date = explode('-', $day_key);
				   $event_day = ltrim($event_date[2],'0');
                                   if ($full)
				      $cells[$day_key]="<span class='$eme_holiday_class'>$event_day</span><br /><span class='$class'>".$info['name']."</span><br />";
                                   else
				      $cells[$day_key]="<span class='$eme_holiday_class' title='$holiday_title'>$event_day</span>";
			   }
		   }
	   }
      }
   }

   foreach ($eventful_days as $day_key => $events) {
      // Set the date into the key
      $events_titles = array();
      if (isset($holiday_titles[$day_key]))
         $events_titles[] = $holiday_titles[$day_key];
      foreach($events as $event) { 
         $event_title = eme_replace_placeholders($event_title_format, $event,"html",0);
         $event_title = eme_replace_calendar_placeholders($event_title, $event, $day_key);
         $events_titles[] = $event_title;
      }
      $link_title = implode($event_title_separator_format,$events_titles);
      
      $cal_day_link = eme_calendar_day_url($day_key);
      // Let's add the possible options
      // template_id is not being used per event
      if (!empty($location_id))
         $cal_day_link = add_query_arg( array( 'location_id' => $location_id ), $cal_day_link );
      if (!empty($category))
         $cal_day_link = add_query_arg( array( 'category' => $category ), $cal_day_link );
      if (!empty($notcategory))
         $cal_day_link = add_query_arg( array( 'notcategory' => $scope ), $cal_day_link );
      if (!empty($author))
         $cal_day_link = add_query_arg( array( 'author' => $author ), $cal_day_link );
      if (!empty($contact_person))
         $cal_day_link = add_query_arg( array( 'contact_person' => $contact_person ), $cal_day_link );

      $event_date = explode('-', $day_key);
      $event_day = ltrim($event_date[2],'0');
      // there might already be something in the cell if there's a holiday
      if (isset($cells[$day_key])) $holiday_info=$cells[$day_key];
      else $holiday_info="";

      // if there is a specific class filter for the urls, do it
      $class="";
      if (has_filter('eme_calday_url_class_filter')) $class=apply_filters('eme_calday_url_class_filter',$class);
      if (!empty($class)) $class="class='$class'";

      $cells[$day_key] = "<a title='$link_title' href='$cal_day_link' $class>$event_day</a>";
      if ($full) {
         $cells[$day_key] .= "$holiday_info<ul class='eme-calendar-day-event'>";
      
         foreach($events as $event) {
            $cal_day_content = eme_replace_placeholders($event_format, $event, "html", 0);
            $cal_day_content = eme_replace_calendar_placeholders($cal_day_content, $event, $day_key);
            $cells[$day_key] .= $cal_day_content;
         } 
         $cells[$day_key] .= "</ul>";
      }
   }

   // If previous month
   $isPreviousMonth = ($iFirstDayDow > 0);

   // Initial day on the calendar
   $iCalendarDay = ($isPreviousMonth) ? $iPrevShowFrom : 1;

   $isNextMonth = false;
   $sCalTblRows = '';
   $sCalDivRows = '';

   // Generate rows for the calendar
   for ($i = 0; $i < 6; $i++) { // 6-weeks range
      if ($isNextMonth) continue;
      $sCalTblRows .= "<tr>";
      $sCalDivRows .= "<div class='emeDivTableRow'>";

      for ($j = 0; $j < 7; $j++) { // 7 days a week
         // we need the calendar day with 2 digits for the planned events
         $iCalendarDay_padded = sprintf("%02d",$iCalendarDay);
         if ($isPreviousMonth) $calstring="$iPrevYear-$iPrevMonth-$iCalendarDay_padded";
         elseif ($isNextMonth) $calstring="$iNextYear-$iNextMonth-$iCalendarDay_padded";
         else $calstring="$iSelectedYear-$iSelectedMonth-$iCalendarDay_padded";

         // each day in the calendar has the name of the day as a class by default
         $eme_date_obj=new ExpressiveDate($calstring,$eme_timezone);
         $sClass = $eme_date_obj->format('D');

         if (isset($cells[$calstring])) {
            if ($isPreviousMonth)
               $sClass .= " eventful-pre event-day-$iCalendarDay";
            elseif ($isNextMonth)
               $sClass .= " eventful-post event-day-$iCalendarDay";
            elseif ($calstring == "$iNowYear-$iNowMonth-$iNowDay")
               $sClass .= " eventful-today event-day-$iCalendarDay";
            else
               $sClass .= " eventful event-day-$iCalendarDay";
            $sCalTblCell = '<td class="'.$sClass.'">'.$cells[$calstring]. "</td>\n";
            $sCalDivCell = '<div class="emeDivTableCell '.$sClass.'">'.$cells[$calstring]. "</div>\n";
         } else {
            if ($isPreviousMonth)
               $sClass .= " eventless-pre";
            elseif ($isNextMonth)
               $sClass .= " eventless-post";
            elseif ($calstring == "$iNowYear-$iNowMonth-$iNowDay")
               $sClass .= " eventless-today";
            else
               $sClass .= " eventless";
            $sCalTblCell = '<td class="'.$sClass.'">'.$iCalendarDay. "</td>\n";
            $sCalDivCell = '<div class="emeDivTableCell '.$sClass.'">'.$iCalendarDay. "</div>\n";
         }

         // only show wanted columns
         if (count($weekday_arr)) {
            $day_of_week = $eme_date_obj->getDayOfWeekAsNumeric();
            if (eme_array_integers($weekday_arr) && in_array($day_of_week,$weekday_arr)) {
               $sCalTblRows .= $sCalTblCell;
               $sCalDivRows .= $sCalDivCell;
            }
         } else {
            $sCalTblRows .= $sCalTblCell;
            $sCalDivRows .= $sCalDivCell;
         }

         // Next day
         $iCalendarDay++;
         if ($isPreviousMonth && $iCalendarDay > $iPrevDaysInMonth) {
            $isPreviousMonth = false;
            $iCalendarDay = 1;
         }
         if (!$isPreviousMonth && !$isNextMonth && $iCalendarDay > $iDaysInMonth) {
            $isNextMonth = true;
            $iCalendarDay = 1;
         }
      }
      $sCalTblRows .= "</tr>\n";
      $sCalDivRows .= "</div>\n";
   }

   $weekday_names = array(__('Sunday'),__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'));
   $weekday_header_class = array('Sun_header','Mon_header','Tue_header','Wed_header','Thu_header','Fri_header','Sat_header');
   $sCalTblDayNames="";
   $sCalDivDayNames="";
   // respect the beginning of the week offset
   for ($i=$start_of_week; $i<$start_of_week+7; $i++) {
      $j=$i;
      if ($j>=7) $j-=7;
      // only show wanted columns
      if (!empty($weekday_arr)) {
         if (!eme_array_integers($weekday_arr) || !in_array($j,$weekday_arr))
            continue;
      }
      
      if ($full) {
         $sCalTblDayNames.= "<td class='".$weekday_header_class[$j]."'>".$wp_locale->get_weekday_abbrev($weekday_names[$j])."</td>";
         $sCalDivDayNames.= "<div class='emeDivTableHead ".$weekday_header_class[$j]."'>".$wp_locale->get_weekday_abbrev($weekday_names[$j])."</div>";
      } else {
         $sCalTblDayNames.= "<td class='".$weekday_header_class[$j]."'>".$wp_locale->get_weekday_initial($weekday_names[$j])."</td>";
         $sCalDivDayNames.= "<div class='emeDivTableHead ".$weekday_header_class[$j]."'>".$wp_locale->get_weekday_initial($weekday_names[$j])."</div>";
      }
   }

   // the real links are created via jquery when clicking on the prev-month or next-month class-links
   $random = (rand(100,200));
   $previous_link = "<a class='prev-month' href=\"#\">&lt;&lt;</a>"; 
   $next_link = "<a class='next-month' href=\"#\">&gt;&gt;</a>";

   $full ? $class = 'eme-calendar-full' : $class='eme-calendar';
   $calendar="<div class='$class' id='eme-calendar-$random'>";
   
   if (count($weekday_arr))
      $colspan=count($weekday_arr);
   else
      $colspan=7;
   if ($full)
      $fullclass = 'fullcalendar';
   else
      $fullclass='smallcalendar';
   
   $CalTblHead = "<td class='month_name' colspan='$colspan'>$previous_link $cal_datestring $next_link</td>\n";
   $CalDivHead = "<div class='emeDivTableHead month_name'>$previous_link $cal_datestring $next_link</div>\n";
   // Build the heading portion of the calendar table
   if ($htmltable && !$htmldiv) {
      $calendar .=  "<table class='eme-calendar-table $fullclass'>\n".
                 "<thead><tr>".$CalTblHead."</tr></thead>\n".
                 "<tr class='days-names'>".$sCalTblDayNames."</tr>\n";
      $calendar .= $sCalTblRows;
      $calendar .=  "</table>\n";
   } else {
      $calendar .=  "<div class='emeDivTable $fullclass'>\n".
                 "<div class='emeDivTableHeading'><div class='emeDivTableRow'>".$CalDivHead."</div></div>\n".
		 "</div><div class='emeDivTable $fullclass'>\n".
                 "<div class='emeDivTableRow days-names'>".$sCalDivDayNames."</div>\n";
      $calendar .= $sCalDivRows;
      $calendar .=  "</div>\n";
   }

   $language = eme_detect_lang();
   $calendar .= "<script type='text/javascript'>
         jQuery('#eme-calendar-".$random." a.prev-month').click(function(e){
            e.preventDefault();
            emeCalDiv = jQuery('#eme-calendar-".$random."');
            jQuery('#eme-calendar-".$random." a.prev-month').html('<img src=\"".EME_PLUGIN_URL."images/spinner.gif\">');
            loadCalendar(emeCalDiv, '$full', '$htmltable', '$htmldiv', '$long_events','$iPrevMonth','$iPrevYear','$category','$author','$contact_person','$location_id','$notcategory','$template_id','$holiday_id','$weekdays','$language');
         } );
         jQuery('#eme-calendar-".$random." a.next-month').click(function(e){
            e.preventDefault();
            emeCalDiv = jQuery('#eme-calendar-".$random."');
            jQuery('#eme-calendar-".$random." a.next-month').html('<img src=\"".EME_PLUGIN_URL."images/spinner.gif\">');
            loadCalendar(emeCalDiv, '$full', '$htmltable', '$htmldiv', '$long_events','$iNextMonth','$iNextYear','$category','$author','$contact_person','$location_id','$notcategory','$template_id','$holiday_id','$weekdays','$language');
         } );
         </script></div>";

   $output=$calendar;
   if ($echo)
      echo $output; 
   else
      return $output;

}

function eme_filter_calendar_ajax() {
   (isset($_POST['full']) && $_POST['full']) ? $full = 1 : $full = 0;
   (isset($_POST['htmltable']) && $_POST['htmltable']) ? $htmltable = 1 : $htmltable = 0;
   (isset($_POST['htmldiv']) && $_POST['htmldiv']) ? $htmldiv = 1 : $htmldiv = 0;
   (isset($_POST['long_events']) && $_POST['long_events']) ? $long_events = 1 : $long_events = 0;
   (isset($_POST['category'])) ? $category = $_POST['category'] : $category = 0;
   (isset($_POST['notcategory'])) ? $notcategory = $_POST['notcategory'] : $notcategory = 0;
   (isset($_POST['calmonth'])) ? $month = eme_sanitize_request($_POST['calmonth']) : $month = ''; 
   (isset($_POST['calyear'])) ? $year = eme_sanitize_request($_POST['calyear']) : $year = ''; 
   (isset($_POST['author'])) ? $author = eme_sanitize_request($_POST['author']) : $author = ''; 
   (isset($_POST['contact_person'])) ? $contact_person = eme_sanitize_request($_POST['contact_person']) : $contact_person = ''; 
   (isset($_POST['location_id'])) ? $location_id = eme_sanitize_request($_POST['location_id']) : $location_id = '';
   (isset($_POST['template_id'])) ? $template_id = eme_sanitize_request($_POST['template_id']) : $template_id = '';
   (isset($_POST['holiday_id'])) ? $holiday_id = eme_sanitize_request($_POST['holiday_id']) : $holiday_id = '';
   (isset($_POST['weekdays'])) ? $weekdays = eme_sanitize_request($_POST['weekdays']) : $weekdays = '';

   header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
   echo eme_get_calendar("full={$full}&month={$month}&year={$year}&long_events={$long_events}&category={$category}&author={$author}&contact_person={$contact_person}&location_id={$location_id}&notcategory={$notcategory}&template_id={$template_id}&weekdays={$weekdays}&holiday_id={$holiday_id}&htmltable={$htmltable}&htmldiv={$htmldiv}");
}

function eme_replace_calendar_placeholders($format, $event, $cal_day, $target="html", $lang='') {

   if (has_filter('eme_cal_format_prefilter')) $format=apply_filters('eme_cal_format_prefilter',$format, $event, $cal_day);

   preg_match_all("/#_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);

   usort($placeholders[0],'eme_sort_stringlenth');
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_IS_START_DAY/', $result)) {
         if ($cal_day==$event['event_start_date'])
            $replacement = 1;
         else
            $replacement = 0;
      } elseif (preg_match('/#_IS_END_DAY/', $result)) {
         if ($cal_day==$event['event_end_date'])
            $replacement = 1;
         else
            $replacement = 0;
      } elseif (preg_match('/#_IS_NOT_START_OR_END_DATE/', $result)) {
         if ($cal_day!=$event['event_start_date'] && $cal_day!=$event['event_end_date'])
            $replacement = 1;
         else
            $replacement = 0;
      } else {
         $found = 0;
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }

   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   return do_shortcode($format);   
}
?>
<?php

