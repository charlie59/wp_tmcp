<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_client_clock_callback() {
   global $eme_timezone;
   eme_session_start();
   // Set php clock values in an array
   $phptime_obj = new ExpressiveDate(null,$eme_timezone);
   // if clock data not set
   if (!isset($_SESSION['eme_client_unixtime'])) {
      // Preset php clock values in client session variables for fall-back if valid client clock data isn't received.
      $_SESSION['eme_client_clock_valid'] = false; // Will be set true if all client clock data passes sanity tests
      $_SESSION['eme_client_php_difference'] = 0; // Client-php clock difference integer seconds
      $_SESSION['eme_client_unixtime'] = (int) $phptime_obj->format('U'); // Integer seconds since 1/1/1970 @ 12:00 AM
      $_SESSION['eme_client_seconds'] = (int) $phptime_obj->format('s'); // Integer second this minute (0-59)
      $_SESSION['eme_client_minutes'] = (int) $phptime_obj->format('i'); // Integer minute this hour (0-59)
      $_SESSION['eme_client_hours'] = (int) $phptime_obj->format('h'); // Integer hour this day (0-23)
      $_SESSION['eme_client_wday'] = (int) $phptime_obj->format('w'); // Integer day this week (0-6), 0 = Sunday, ... , 6 = Saturday
      $_SESSION['eme_client_mday'] = (int) $phptime_obj->format('j'); // Integer day this month 1-31)
      $_SESSION['eme_client_month'] = (int) $phptime_obj->format('n'); // Integer month this year (1-12)
      $_SESSION['eme_client_fullyear'] = (int) $phptime_obj->format('Y'); // Integer year (1970-9999)
      $ret = '1'; // reload from server
   } else {
      $ret = '0';
   }
   
   // Cast client clock values as integers to avoid mathematical errors and set in temporary local variables.
   $client_unixtime = (int) $_POST['client_unixtime'];
   $client_seconds = (int) $_POST['client_seconds'];
   $client_minutes = (int) $_POST['client_minutes'];
   $client_hours = (int) $_POST['client_hours'];
   $client_wday = (int) $_POST['client_wday'];
   $client_mday = (int) $_POST['client_mday'];
   $client_month = (int) $_POST['client_month'];
   $client_fullyear = (int) $_POST['client_fullyear'];
   
   // Client clock sanity tests
   $valid = true;
   if (abs($client_unixtime - $_SESSION['eme_client_unixtime']) > 300) $valid = false; // allow +/-5 min difference
   if (abs($client_seconds - 30) > 30) $valid = false; // Seconds <0 or >60
   if (abs($client_minutes - 30) > 30) $valid = false; // Minutes <0 or >60
   if (abs($client_hours - 12) > 12) $valid = false; // Hours <0 or >24
   if (abs($client_wday - 3) > 3) $valid = false; // Weekday <0 or >6
   if (abs($client_mday - $_SESSION['eme_client_mday']) > 30) $valid = false; // >30 day difference
   if (abs($client_month - $_SESSION['eme_client_month']) > 11) $valid = false; // >11 month difference
   if (abs($client_fullyear - $_SESSION['eme_client_fullyear']) > 1) $valid = false; // >1 year difference

   // To insure mutual consistency, don't use any client values unless they all passed the tests.
   if ($valid) {
      $_SESSION['eme_client_unixtime'] = $client_unixtime;
      $_SESSION['eme_client_seconds'] = $client_seconds;
      $_SESSION['eme_client_minutes'] = $client_minutes;
      $_SESSION['eme_client_hours'] = $client_hours;
      $_SESSION['eme_client_wday'] = $client_wday;
      $_SESSION['eme_client_mday'] = $client_mday;
      $_SESSION['eme_client_month'] = $client_month;
      $_SESSION['eme_client_fullyear'] = $client_fullyear;
      $_SESSION['eme_client_clock_valid'] = true;
      // Set  date & time clock strings
      $client_clock_str = "$client_fullyear-$client_month-$client_mday $client_hours:$client_minutes:$client_seconds";
      $client_clock_obj = new ExpressiveDate($client_clock_str,$eme_timezone);
      $_SESSION['eme_client_php_difference'] = (int) $client_clock_obj->getDifferenceInSeconds($phptime_obj);
   }
   
   // it is an ajax instance: echo the result
   echo $ret;
}

function eme_captcha_generate() {
        eme_session_start();
	if (isset($_GET['sessionvar']))
		$sessionvar=$_GET['sessionvar'];
	else
		$sessionvar='captcha';

	// 23 letters
	$alfabet="abcdefghjkmnpqrstuvwxyz";
	$random1 = substr($alfabet,rand(1,23)-1,1);
	$random2 = rand(2,9);
	$rand=rand(1,23)-1;
	$random3 = substr($alfabet,rand(1,23)-1,1);
	$random4 = rand(2,9);
	$rand=rand(1,23)-1;
	$random5 = substr($alfabet,rand(1,23)-1,1);

	$randomtext=$random1.$random2.$random3.$random4.$random5;
	$_SESSION[$sessionvar] = md5($randomtext);

	$im = imagecreatetruecolor(120, 38);

	// some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	$red = imagecolorallocate($im, 255, 0, 0);
	$blue = imagecolorallocate($im, 0, 0, 255);
	$green = imagecolorallocate($im, 0, 255, 0);
	$background_colors=array($red,$blue,$green,$black);

	// draw rectangle in random color
	$background_color=$background_colors[rand(0,3)];
	imagefilledrectangle($im, 0, 0, 120, 38, $background_color);

	// replace font.ttf with the location of your own ttf font file
	$font = EME_PLUGIN_DIR.'/font.ttf';

	// add shadow
	imagettftext($im, 25, 8, 15, 28, $grey, $font, $random1);
	imagettftext($im, 25, -8, 35, 28, $grey, $font, $random2);
	imagettftext($im, 25, 8, 55, 28, $grey, $font, $random3);
	imagettftext($im, 25, -8, 75, 28, $grey, $font, $random4);
	imagettftext($im, 25, 8, 95, 28, $grey, $font, $random5);

	// add text
	imagettftext($im, 25, 8, 8, 30, $white, $font, $random1);
	imagettftext($im, 25, -8, 28, 30, $white, $font, $random2);
	imagettftext($im, 25, 8, 48, 30, $white, $font, $random3);
	imagettftext($im, 25, -8, 68, 30, $white, $font, $random4);
	imagettftext($im, 25, 8, 88, 30, $white, $font, $random5);

	// give image back
	header ("Content-type: image/gif");
	imagegif($im);
	imagedestroy($im);
	exit;
}

function eme_check_captcha($post_var,$session_var="",$cleanup=1) {
   if (empty($session_var))
      $session_var="captcha";
   $eme_captcha_no_case=get_option('eme_captcha_no_case');
   if (!isset($_POST[$post_var]) || !isset($_SESSION[$session_var]) ||
       (!$eme_captcha_no_case && md5($_POST[$post_var]) != $_SESSION[$session_var]) ||
       ($eme_captcha_no_case && md5(strtolower($_POST[$post_var])) != strtolower($_SESSION[$session_var]))) {
      return __('You entered an incorrect code. Please fill in the correct code.', 'events-made-easy');
   } else {
      if ($cleanup==1) {
//         unset($_SESSION[$session_var]);
         unset($_POST[$post_var]);
      }
      return ('');
   }
}

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'eq' => '', 'notvalue' => '', 'ne' => '', 'lt' => '', 'le' => '',  'gt' => '', 'ge' => '', 'contains'=>'', 'notcontains'=>'', 'is_empty'=>0 ), $atts ) );
   if ($is_empty) {
      if (empty($tag)) return do_shortcode($content);
   } elseif (is_numeric($value) || !empty($value)) {
      if ($tag==$value) return do_shortcode($content);
   } elseif (is_numeric($eq) || !empty($eq)) {
      if ($tag==$eq) return do_shortcode($content);
   } elseif (is_numeric($ne) || !empty($ne)) {
      if ($tag!=$ne) return do_shortcode($content);
   } elseif (is_numeric($notvalue) || !empty($notvalue)) {
      if ($tag!=$notvalue) return do_shortcode($content);
   } elseif (is_numeric($lt) || !empty($lt)) {
      if ($tag<$lt) return do_shortcode($content);
   } elseif (is_numeric($le) || !empty($le)) {
      if ($tag<=$le) return do_shortcode($content);
   } elseif (is_numeric($gt) || !empty($gt)) {
      if ($tag>$gt) return do_shortcode($content);
   } elseif (is_numeric($ge) || !empty($ge)) {
      if ($tag>=$ge) return do_shortcode($content);
   } elseif (is_numeric($contains) || !empty($contains)) {
      if (strpos($tag,"$contains")!== false) return do_shortcode($content);
   } elseif (is_numeric($notcontains) || !empty($notcontains)) {
      if (strpos($tag,"$notcontains")===false) return do_shortcode($content);
   } else {
      if (!empty($tag)) return do_shortcode($content);
   }
}

function eme_for_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('min' => 1, 'max' => 0), $atts ) );
   $min = intval($min);
   $max = intval($max);
   $result="";
   while ($min <= $max) {
      $result .= do_shortcode($content);
      $min++;
   }
   return $result;
}


// Returns true if the page in question is the events page
function eme_is_events_page() {
   $events_page_id = eme_get_events_page_id();
   if ($events_page_id) {
      return is_page ( $events_page_id );
   } else {
      return false;
   }
}

function eme_get_events_page_id() {
   return get_option('eme_events_page');
}

function eme_get_events_page($justurl = 1, $text = '') {
   $events_page_id = eme_get_events_page_id();
   $page_link = get_permalink ($events_page_id);
   if ($justurl || empty($text)) {
      $result = $page_link;
   } else {
      $text = eme_esc_html($text);
      $result = "<a href='$page_link' title='$text'>$text</a>";
   }
   return $result;
}

function eme_is_single_day_page() {
   return (eme_is_events_page () && get_query_var('calendar_day'));
}

function eme_is_single_event_page() {
   return (eme_is_events_page () && get_query_var('event_id'));
}

function eme_is_multiple_events_page() {
   return (eme_is_events_page () && get_query_var('event_id'));
}

function eme_is_single_location_page() {
   return (eme_is_events_page () && get_query_var('location_id'));
}

function eme_is_multiple_locations_page() {
   return (eme_is_events_page () && get_query_var('location_id'));
}

function eme_get_contact($contact_id) {
   // suppose the user has been deleted ...
   if (!get_userdata($contact_id)) $contact_id = get_option('eme_default_contact_person');
   if ($contact_id < 1) {
      if (function_exists('is_multisite') && is_multisite()) {
         $thisblog = get_current_blog_id();
         $userinfo = get_user_by('email', get_blog_option($thisblog, 'admin_email'));
      } else {
         $userinfo = get_user_by('email', get_option('admin_email'));
      }
      #$contact_id = get_current_user_id();
   } else {
      $userinfo=get_userdata($contact_id);
   }
   return $userinfo;
}

function eme_get_event_contact($event=null) {
   if (!is_null($event) && isset($event['event_contactperson_id']) && $event['event_contactperson_id'] >0 )
      $contact_id = $event['event_contactperson_id'];
   else
      $contact_id = get_option('eme_default_contact_person');
   // suppose the user has been deleted ...
   if (!get_userdata($contact_id)) $contact_id = get_option('eme_default_contact_person');
   if ($contact_id < 1 && isset($event['event_author']) && $event['event_author']>0)
      $contact_id = $event['event_author'];
   if ($contact_id < 1) {
      if (function_exists('is_multisite') && is_multisite()) {
         $thisblog = get_current_blog_id();
         $userinfo = get_user_by('email', get_blog_option($thisblog, 'admin_email'));
      } else {
         $userinfo = get_user_by('email', get_option('admin_email'));
      }
      #$contact_id = get_current_user_id();
   } else {
      $userinfo=get_userdata($contact_id);
   }
   return $userinfo;
}

function eme_get_author($event) {
   $author_id = $event['event_author'];
   if ($author_id < 1) {
      if (function_exists('is_multisite') && is_multisite()) {
         $thisblog = get_current_blog_id();
         $userinfo = get_user_by('email', get_blog_option($thisblog, 'admin_email'));
      } else {
         $userinfo = get_user_by('email', get_option('admin_email'));
      }
      #$contact_id = get_current_user_id();
   } else {
      $userinfo=get_userdata($author_id);
   }
   return $userinfo;
}

function eme_get_user_phone($user_id) {
   return get_user_meta($user_id, 'eme_phone',true);
}

// got from http://davidwalsh.name/php-email-encode-prevent-spam
function eme_ascii_encode($e) {
    $output = "";
    if (has_filter('eme_email_obfuscate_filter')) {
       $output=apply_filters('eme_email_obfuscate_filter',$e);
    } else {
       for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }
    }
    return $output;
}

function eme_permalink_convert ($val) {
   // WP provides a function to convert accents to their ascii counterparts
   // called remove_accents, but we also want to replace spaces with "-"
   // and trim the last space. sanitize_title_with_dashes does all that
   // and then, add a trailing slash
   $val = sanitize_title_with_dashes(remove_accents($val));
   return trailingslashit($val);
}

function eme_event_url($event,$language="") {
   global $wp_rewrite;

   $def_language = eme_detect_lang();
   if (empty($language))
         $language = $def_language;
   if ($event['event_url'] != '' && get_option('eme_use_external_url')) {
      $the_link = $event['event_url'];
      $parsed = parse_url($the_link);
      if (empty($parsed['scheme'])) {
          $the_link = 'http://' . ltrim($the_link, '/');
      }
      $the_link = esc_url($the_link);
   } else {
      if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
         $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
         $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
         $name=$events_prefix.$event['event_id']."/".eme_permalink_convert($slug);
         $the_link = home_url();
         // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
         $the_link = preg_replace("/\/$def_language$/","",$the_link);
         $the_link = trailingslashit(remove_query_arg('lang',$the_link));
         if (!empty($language)) {
            $url_mode=eme_lang_url_mode();
            if ($url_mode==2) {
               $the_link = $the_link."$language/".user_trailingslashit($name);
            } else {
               $the_link = $the_link.user_trailingslashit($name);
               $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
            }
         } else {
            $the_link = $the_link.user_trailingslashit($name);
         }

      } else {
         $the_link = eme_get_events_page();
         // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
         $the_link = remove_query_arg('lang',$the_link);
         $the_link = add_query_arg( array( 'event_id' => $event['event_id'] ), $the_link );
         if (!empty($language))
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
      }
   }
   return $the_link;
}

function eme_location_url($location,$language="") {
   global $wp_rewrite;

   $def_language = eme_detect_lang();
   if (empty($language))
         $language = $def_language;
   $the_link = "";
   if ($location['location_url'] != '' && get_option('eme_use_external_url')) {
      $the_link = $location['location_url'];
      $parsed = parse_url($the_link);
      if (empty($parsed['scheme'])) {
          $the_link = 'http://' . ltrim($the_link, '/');
      }
      $the_link = esc_url($the_link);
   } else {
      $url_mode=eme_lang_url_mode();
      if (isset($location['location_id']) && isset($location['location_name'])) {
         if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
            $locations_prefix=eme_permalink_convert(get_option ( 'eme_permalink_locations_prefix'));
            $slug = $location['location_slug'] ? $location['location_slug'] : $location['location_name'];
            $name=$locations_prefix.$location['location_id']."/".eme_permalink_convert($slug);
            $the_link = home_url();
            // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
            $the_link = preg_replace("/\/$def_language$/","",$the_link);
            $the_link = trailingslashit(remove_query_arg('lang',$the_link));
            if (!empty($language)) {
               $url_mode=eme_lang_url_mode();
               if ($url_mode==2) {
                  $the_link = $the_link."$language/".user_trailingslashit($name);
               } else {
                  $the_link = $the_link.user_trailingslashit($name);
                  $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
               }
            } else {
               $the_link = $the_link.user_trailingslashit($name);
            }
         } else {
            $the_link = eme_get_events_page();
            // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
            $the_link = remove_query_arg('lang',$the_link);
            $the_link = add_query_arg( array( 'location_id' => $location['location_id'] ), $the_link );
            if (!empty($language))
               $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      }
   }
   return $the_link;
}

function eme_calendar_day_url($day) {
   global $wp_rewrite;

   $def_language = eme_detect_lang();
   $language = $def_language;

   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $name=$events_prefix.eme_permalink_convert($day);
      $the_link = home_url();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = preg_replace("/\/$def_language$/","",$the_link);
      $the_link = trailingslashit(remove_query_arg('lang',$the_link));
      if (!empty($language)) {
         $url_mode=eme_lang_url_mode();
         if ($url_mode==2) {
            $the_link = $the_link."$language/".user_trailingslashit($name);
         } else {
            $the_link = $the_link.user_trailingslashit($name);
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      } else {
         $the_link = $the_link.user_trailingslashit($name);
      }
   } else {
      $the_link = eme_get_events_page();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = remove_query_arg('lang',$the_link);
      $the_link = add_query_arg( array( 'calendar_day' => $day ), $the_link );
      if (!empty($language))
         $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   }
   return $the_link;
}

function eme_payment_url($payment_randomid) {
   global $wp_rewrite;

   $def_language = eme_detect_lang();
   $language = $def_language;
   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $name=$events_prefix."p/$payment_randomid";
      $the_link = home_url();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = preg_replace("/\/$def_language$/","",$the_link);
      $the_link = trailingslashit(remove_query_arg('lang',$the_link));
      if (!empty($language)) {
         $url_mode=eme_lang_url_mode();
         if ($url_mode==2) {
            $the_link = $the_link."$language/".user_trailingslashit($name);
         } else {
            $the_link = $the_link.user_trailingslashit($name);
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      } else {
         $the_link = $the_link.user_trailingslashit($name);
      }
   } else {
      $the_link = eme_get_events_page();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = remove_query_arg('lang',$the_link);
      $the_link = add_query_arg( array( 'eme_pmt_rndid' => $payment_randomid ), $the_link );
      if (!empty($language))
         $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   }
   return $the_link;
}

function eme_category_url($category) {
   global $wp_rewrite;

   $def_language = eme_detect_lang();
   $language = $def_language;
   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $slug = $category['category_slug'] ? $category['category_slug'] : $category['category_name'];
      $name=$events_prefix."cat/".eme_permalink_convert($slug);
      $the_link = home_url();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = preg_replace("/\/$def_language$/","",$the_link);
      $the_link = trailingslashit(remove_query_arg('lang',$the_link));
      if (!empty($language)) {
         $url_mode=eme_lang_url_mode();
         if ($url_mode==2) {
            $the_link = $the_link."$language/".user_trailingslashit($name);
         } else {
            $the_link = $the_link.user_trailingslashit($name);
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      } else {
         $the_link = $the_link.user_trailingslashit($name);
      }
   } else {
      $the_link = eme_get_events_page();
      // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
      $the_link = remove_query_arg('lang',$the_link);
      $slug = $category['category_slug'] ? $category['category_slug'] : $category['category_name'];
      $the_link = add_query_arg( array( 'eme_event_cat' => $slug ), $the_link );
      if (!empty($language))
         $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   }
   return $the_link;
}

function eme_payment_return_url($payment,$resultcode,$event=array()) {
   if (get_option('eme_payment_show_custom_return_page')) {
      $the_link = eme_get_events_page();
      if ($resultcode==1) {
         $res="succes";
      } else {
         $res="fail";
      }
      $the_link = add_query_arg( array( 'eme_pmt_result' => $res ), $the_link );
      $the_link = add_query_arg( array( 'eme_pmt_rndid' => $payment['random_id'] ), $the_link );
   } else {
      if (!empty($event))
	      $the_link=eme_event_url($event);
      else
	      $the_link = eme_get_events_page();
   }
   return $the_link;
}

function eme_cancel_url($payment_randomid) {
   $def_language = eme_detect_lang();
   $language = $def_language;

   $the_link = eme_get_events_page();
   // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
   $the_link = remove_query_arg('lang',$the_link);
   $the_link = add_query_arg( array( 'eme_cancel_booking' => $payment_randomid ), $the_link );
   if (!empty($language))
	   $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   return $the_link;
}

function eme_gdpr_url($email) {
   $def_language = eme_detect_lang();
   $language = $def_language;

   $the_link = eme_get_events_page();
   // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
   $the_link = remove_query_arg('lang',$the_link);
   $nonce = wp_create_nonce("gdpr $email");
   $the_link = add_query_arg( array( 'eme_gdpr' => $email, 'eme_gdpr_nonce'=>$nonce ), $the_link );
   if (!empty($language))
	   $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   return $the_link;
}

function eme_unsub_url() {
   $def_language = eme_detect_lang();
   $language = $def_language;

   $the_link = eme_get_events_page();
   // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
   $the_link = remove_query_arg('lang',$the_link);
   $the_link = add_query_arg( array( 'eme_unsub' => 1 ), $the_link );
   if (!empty($language))
	   $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   return $the_link;
}

function eme_unsub_confirm_url($email) {
   $def_language = eme_detect_lang();
   $language = $def_language;

   $the_link = eme_get_events_page();
   // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
   $the_link = remove_query_arg('lang',$the_link);
   $nonce = wp_create_nonce("unsub $email");
   $the_link = add_query_arg( array( 'eme_unsub_confirm' => $email, 'eme_unsub_nonce'=>$nonce ), $the_link );
   if (!empty($language))
	   $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   return $the_link;
}

function eme_captcha_url($sessionvar) {
   $the_link = "";
   $the_link = add_query_arg( array( 'eme_captcha' => 'generate','sessionvar' => $sessionvar ), $the_link );
   return $the_link;
}

function eme_attendees_report_link($title,$scope,$category,$notcategory,$event_template_id,$attend_template_id) {
   if (!is_user_logged_in()) return;

   $def_language = eme_detect_lang();
   $language = $def_language;

   $the_link = "";
   // some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
   $the_link = remove_query_arg('lang',$the_link);
   $the_link = add_query_arg( array( 'eme_attendees' => 'report' ), $the_link );
   $the_link = add_query_arg( array( 'scope' => esc_attr($scope) ), $the_link );
   $the_link = add_query_arg( array( 'event_template_id' => esc_attr($event_template_id) ), $the_link );
   $the_link = add_query_arg( array( 'attend_template_id' => esc_attr($attend_template_id) ), $the_link );
   $the_link = add_query_arg( array( 'category' => esc_attr($category) ), $the_link );
   $the_link = add_query_arg( array( 'notcategory' => esc_attr($notcategory) ), $the_link );
   if (!empty($language))
	   $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   return "<a href='$the_link' title='$title'>".$title."</a>";
}

function eme_check_event_exists($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_id='".$event_id."'";
   return $wpdb->get_var($sql);
}

function eme_check_location_exists($location_id) {
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME;
   $sql = "SELECT COUNT(*) from $locations_table WHERE location_id='".$location_id."'";
   return $wpdb->get_var($sql);
}

function eme_are_dates_valid($date) {
   // if it is a series of dates
   if (strstr($date, ',')) {
	$dates=explode(',',$date);
   	foreach ( $dates as $date ) {
		if (!eme_is_date_valid($date)) return false;
	}
   }
   return true;
}
	
function eme_is_date_valid($date) {
   // check the format yyyy-mm-dd
   if (strlen($date) != 10)
      return false;
   $year = intval(substr ( $date, 0, 4 ));
   $month = intval(substr ( $date, 5, 2 ));
   $day = intval(substr ( $date, 8 ));
   return (checkdate ( $month, $day, $year ));
}

function eme_capNamesCB ( $cap ) {
   $cap = str_replace('_', ' ', $cap);
   $cap = ucfirst($cap);
   return $cap;
}
function eme_get_all_caps() {
   global $wp_roles;
   $caps = array();
   $capabilities = array();

   foreach ( $wp_roles->roles as $role ) {
      if ($role['capabilities']) {
         foreach ( $role['capabilities'] as $cap=>$val ) {
           if (!preg_match("/^level/",$cap))
	      $capabilities[$cap]=eme_capNamesCB($cap);
         }
      }
   }

#   $sys_caps = get_option('syscaps');
#   if ( is_array($sys_caps) ) {
#      $capabilities = array_merge($sys_caps, $capabilities);
#   }

   asort($capabilities);
   return $capabilities;
}

function eme_delete_image_files($image_basename) {
   $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
   foreach($mime_types as $type) {
      if (file_exists($image_basename.".".$type))
         unlink($image_basename.".".$type);
   }
}

function eme_status_array() {
   $status_array = array();
   $status_array[STATUS_PUBLIC] = __ ( 'Public', 'events-made-easy');
   $status_array[STATUS_PRIVATE] = __ ( 'Private', 'events-made-easy');
   $status_array[STATUS_DRAFT] = __ ( 'Draft', 'events-made-easy');
   return $status_array;
}

function eme_member_status_array() {
   $status_array = array();
   $status_array[MEMBER_STATUS_PENDING] = __ ( 'Pending', 'events-made-easy');
   $status_array[MEMBER_STATUS_ACTIVE] = __ ( 'Active', 'events-made-easy');
   $status_array[MEMBER_STATUS_EXPIRED] = __ ( 'Expired', 'events-made-easy');
   return $status_array;
}

function eme_localized_date($mydate,$date_format='') {
   global $eme_wp_date_format, $eme_timezone;
   if (empty($date_format))
      $date_format = $eme_wp_date_format;
   // $mydate contains the timezone, but in case it doesn't we provide it
   $eme_date_obj = new ExpressiveDate($mydate,$eme_timezone);
   // Currently in the backend, the timezone is UTC, but maybe that changes in future wp versions
   //   so we search for the current timezone using date_default_timezone_get
   // Since DateTime::format doesn't respect the locale, we use date_i18n here
   //   but date_i18n uses the WP backend timezone, so we need to account for the timezone difference
   // All this because we don't want to use date_default_timezone_set() and wp doesn't set the backend
   //   timezone correctly ...
   $wp_date = new ExpressiveDate($eme_date_obj->getDateTime(),date_default_timezone_get());
   $tz_diff=$eme_date_obj->getOffset()-$wp_date->getOffset();
   $result = date_i18n($date_format, $eme_date_obj->getTimestamp()+$tz_diff);
   return $result;
}

function eme_localized_time($mydate) {
   global $eme_wp_time_format;
   $result = eme_localized_date($mydate,$eme_wp_time_format);
   if (get_option('eme_time_remove_leading_zeros')) {
      $result = str_replace(":00","",$result);
      $result = str_replace(":0",":",$result);
   }
   return $result;
}

// the following is the same as eme_localized_date, but for rfc822 format
function eme_rfc822_date($mydate) {
   $result = date('r', strtotime($mydate));
   return $result;
}

function eme_localized_currencysymbol($cur,$target="html") {
   if (!class_exists('NumberFormatter')) {
	   return $cur;
   } else {
	   $locale = get_locale();
	   $formatter = new NumberFormatter( $locale."@currency=$cur", NumberFormatter::CURRENCY );
	   return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
   }
}

function eme_localized_price($price,$cur,$target="html") {
   // number_format needs a floating point, so if price is empty (for e.g. discounts), make it 0
   if (empty($price)) $price=0;
   if (eme_is_multi($price))
	   $price_arr=eme_convert_multi2array($price);
   else
	   $price_arr=array($price);

   $locale = get_locale();
   if (class_exists('NumberFormatter') && get_option('eme_localize_price'))
	   $eme_localize_price=1;
   else
	   $eme_localize_price=0;

   if ($eme_localize_price)
	   $formatter = new NumberFormatter( $locale."@currency=$cur", NumberFormatter::CURRENCY );

   $eme_zero_decimal_currencies_arr=eme_zero_decimal_currencies();
   if (in_array($cur,$eme_zero_decimal_currencies_arr))
	   $decimals = 0;
   else
	   $decimals=intval(get_option('eme_decimals'));

   $res=array();
   foreach ($price_arr as $t_price) {
	   //$result = number_format_i18n($t_price,$decimals);
	   if ($eme_localize_price)
		   $result = $formatter->formatCurrency($t_price, $cur);
	   else
		   $result = number_format_i18n($t_price,$decimals);

	   // the result can contain html entities, for e.g. text mails we don't want that of course
	   if ($target == "html") {
		   $res[] = $result;
	   } else {
		   $res[] = html_entity_decode($result);
	   }
   }
   return implode("||",$res);
}

function eme_currency_array() {
   $currency_array = array ();
   $currency_array ['AUD'] = __ ( 'Australian Dollar', 'events-made-easy');
   $currency_array ['CAD'] = __ ( 'Canadian Dollar', 'events-made-easy');
   $currency_array ['CZK'] = __ ( 'Czech Koruna', 'events-made-easy');
   $currency_array ['DKK'] = __ ( 'Danish Krone', 'events-made-easy');
   $currency_array ['EUR'] = __ ( 'Euro', 'events-made-easy');
   $currency_array ['HKD'] = __ ( 'Hong Kong Dollar', 'events-made-easy');
   $currency_array ['HUF'] = __ ( 'Hungarian Forint', 'events-made-easy');
   $currency_array ['ILS'] = __ ( 'Israeli New Sheqel', 'events-made-easy');
   $currency_array ['JPY'] = __ ( 'Japanese Yen', 'events-made-easy');
   $currency_array ['MXN'] = __ ( 'Mexican Peso', 'events-made-easy');
   $currency_array ['NOK'] = __ ( 'Norwegian Krone', 'events-made-easy');
   $currency_array ['NZD'] = __ ( 'New Zealand Dollar', 'events-made-easy');
   $currency_array ['PHP'] = __ ( 'Philippine Peso', 'events-made-easy');
   $currency_array ['PLN'] = __ ( 'Polish Zloty', 'events-made-easy');
   $currency_array ['GBP'] = __ ( 'Pound Sterling', 'events-made-easy');
   $currency_array ['SGD'] = __ ( 'Singapore Dollar', 'events-made-easy');
   $currency_array ['SEK'] = __ ( 'Swedish Krona', 'events-made-easy');
   $currency_array ['CHF'] = __ ( 'Swiss Franc', 'events-made-easy');
   $currency_array ['THB'] = __ ( 'Thai Baht', 'events-made-easy');
   $currency_array ['USD'] = __ ( 'U.S. Dollar', 'events-made-easy');
   $currency_array ['CNY'] = __ ( 'Chinese Yuan Renminbi', 'events-made-easy');
   $currency_array ['ZAR'] = __ ( 'South African Rand', 'events-made-easy');

   # the next filter allows people to add extra currencies:
   if (has_filter('eme_add_currencies')) $currency_array=apply_filters('eme_add_currencies',$currency_array);
   return $currency_array;
}

function eme_zero_decimal_currencies() {
   # returns an array of currencies that don't have decimals
   $currency_array = array (
    'BIF',
    'CLP',
    'DJF',
    'GNF',
    'JPY',
    'KMF',
    'KRW',
    'MGA',
    'PYG',
    'RWF',
    'VND',
    'VUV',
    'XAF',
    'XOF',
    'XPF'
   );
   # the next filter allows people to add extra currencies:
   if (has_filter('eme_add_zero_decimal_currencies')) $currency_array=apply_filters('eme_add_zero_decimal_currencies',$currency_array);
   return $currency_array;
}

function eme_thumbnail_sizes() {
   global $_wp_additional_image_sizes;
   $sizes = array();
   foreach ( get_intermediate_image_sizes() as $s ) {
      $sizes[ $s ] = $s;
   }
   return $sizes;
}

function eme_transfer_nbr_be97($my_nbr) {
   $transfer_nbr_be97_main=sprintf("%010d",$my_nbr);
   // the control number is the %97 result, or 97 in case %97=0
   $transfer_nbr_be97_check=$transfer_nbr_be97_main % 97;
   if ($transfer_nbr_be97_check==0)
      $transfer_nbr_be97_check = 97 ;
   $transfer_nbr_be97_check=sprintf("%02d",$transfer_nbr_be97_check);
   $transfer_nbr_be97 = $transfer_nbr_be97_main.$transfer_nbr_be97_check;
   $transfer_nbr_be97 = substr($transfer_nbr_be97,0,3)."/".substr($transfer_nbr_be97,3,4)."/".substr($transfer_nbr_be97,7,5);
   return $transfer_nbr_be97_main.$transfer_nbr_be97_check;
}

function eme_redefine_locale($locale) {
   if (function_exists('pll_current_language') && function_exists('pll_languages_list')) {
      $languages=pll_languages_list();
      if (!$languages) return $locale;
      $locale="";
      foreach ($languages as $tmp_lang) {
         if (preg_match("/^$tmp_lang\/|\/$tmp_lang\//",$_SERVER['REQUEST_URI']))
               $locale=$tmp_lang.'_'.strtoupper($tmp_lang);
      }
      if (empty($locale))
         $locale=pll_current_language('locale');
   }
   return $locale;
}

function eme_detect_lang_js_trans_function() {
   if (function_exists('ppqtrans_use')) {
      $function_name="pqtrans_use";
   } elseif (function_exists('qtrans_use')) {
      $function_name="qtrans_use";
   } else {
      $function_name="";
   }
   return $function_name;
}

function eme_detect_lang() {
   $language="";
   if (function_exists('qtrans_getLanguage')) {
      // if permalinks are on, $_GET doesn't contain lang as a parameter
      // so we get it like this to be sure
      $language=qtrans_getLanguage();
   } elseif (function_exists('ppqtrans_getLanguage')) {
      $language=ppqtrans_getLanguage();
   } elseif (function_exists('qtranxf_getLanguage')) {
      $language=qtranxf_getLanguage();
   } elseif (function_exists('pll_current_language') && function_exists('pll_languages_list')) {
      $languages=pll_languages_list();
      if (is_array($languages)) {
          foreach ($languages as $tmp_lang) {
             if (preg_match("/^$tmp_lang\/|\/$tmp_lang\//",$_SERVER['REQUEST_URI']))
                   $language=$tmp_lang;
          }
      }
      if (empty($language))
         $language=pll_current_language('slug');
   } elseif (defined('ICL_LANGUAGE_CODE')) {
      // Both polylang and wpml define this constant, so check polylang first (above)
      // if permalinks are on, $_GET doesn't contain lang as a parameter
      // so we get it like this to be sure
      $language=ICL_LANGUAGE_CODE;
   } elseif (isset($_GET['lang'])) {
      $language=eme_strip_tags($_GET['lang']);
   } else {
      $language="";
   }
   return $language;
}

function eme_lang_url_mode() {
   $url_mode=1;
   if (function_exists('mqtranslate_conf')) {
      // only some functions in mqtrans are different, but the options are named the same as for qtranslate
      $url_mode=get_option('mqtranslate_url_mode');
   } elseif (function_exists('qtrans_getLanguage')) {
      $url_mode=get_option('qtranslate_url_mode');
   } elseif (function_exists('ppqtrans_getLanguage')) {
      $url_mode=get_option('pqtranslate_url_mode');
   } elseif (function_exists('qtranxf_getLanguage')) {
      $url_mode=get_option('qtranslate_url_mode');
   } elseif (function_exists('pll_current_language')) {
      $url_mode=2;
   }
   return $url_mode;
}

# support older php version for array_replace_recursive
if (!function_exists('array_replace_recursive')) {
   function array_replace_recursive($array, $array1) {
      function recurse($array, $array1) {
         foreach ($array1 as $key => $value) {
            // create new key in $array, if it is empty or not an array
            if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
               $array[$key] = array();
            }

            // overwrite the value in the base array
            if (is_array($value)) {
               $value = recurse($array[$key], $value);
            }
            $array[$key] = $value;
         }
         return $array;
      }

      // handle the arguments, merge one by one
      $args = func_get_args();
      $array = $args[0];
      if (!is_array($array)) {
         return $array;
      }
      for ($i = 1; $i < count($args); $i++) {
         if (is_array($args[$i])) {
            $array = recurse($array, $args[$i]);
         }
      }
      return $array;
   }
}

// returns 1 if each element of array1 is > than the correspondig element of array2 
function eme_array_gt($array1, $array2) {
   if (count($array1) != count($array2))
      return false;
   foreach ($array1 as $key => $value) {
      if ($array1[$key]<=$array2[$key])
         return 0;
   }
   return 1;
}

// returns 1 if each element of array1 is >= the correspondig element of array2 
function eme_array_ge($array1, $array2) {
   if (count($array1) != count($array2))
      return false;
   foreach ($array1 as $key => $value) {
      if ($array1[$key]<$array2[$key])
         return 0;
   }
   return 1;
}

// returns 1 if each element of array1 is < than the correspondig element of array2 
function eme_array_lt($array1, $array2) {
   if (count($array1) != count($array2))
      return false;
   foreach ($array1 as $key => $value) {
      if ($array1[$key]>=$array2[$key])
         return 0;
   }
   return 1;
}

// returns 1 if each elements of array1 is <= than the correspondig element of array2 
function eme_array_le($array1, $array2) {
   if (count($array1) != count($array2))
      return false;
   foreach ($array1 as $key => $value) {
      if ($array1[$key]>$array2[$key])
         return 0;
   }
   return 1;
}

function eme_get_query_arg($arg) {
   if (isset($_GET[$arg]))
      return eme_strip_tags($_GET[$arg]);
   else
      return false;
}

// returns true if the array values are all integers
function eme_array_integers($only_integers) {
   if (!is_array($only_integers)) return false;
   return array_filter($only_integers,'is_numeric') === $only_integers;
}

function eme_nl2br($arg) {
   return preg_replace("/\r\n?|\n\r?/","<br />",$arg);
}

function eme_br2nl($arg) {
   return preg_replace("/<br ?\/?>/", "\n", $arg);
}

function eme_is_multi($element) {
   if (preg_match("/\|\|/",$element))
      return 1;
   else
      return 0;
}

function eme_convert_multi2array($multistring) {
   return explode("||",$multistring);
}

function eme_convert_array2multi($multiarr) {
   return join("||",$multiarr);
}

function eme_session_start() {
   if (!session_id()) session_start();
}

function eme_session_destroy() {
   if (session_id()) {
      // Unset all of the session variables.
      $_SESSION = array();

      // If it's desired to kill the session, also delete the session cookie.
      // Note: This will destroy the session, and not just the session data!
      if (ini_get("session.use_cookies")) {
         $params = session_get_cookie_params();
         setcookie(session_name(), '', time() - 42000,
               $params["path"], $params["domain"],
               $params["secure"], $params["httponly"]
               );
      }

      // Finally, destroy the session.
      session_destroy();
   }
}

function eme_get_client_ip() {
   // Just get the headers if we can or else use the SERVER global
   if (function_exists('apache_request_headers')) {
      $headers = apache_request_headers();
   } else {
      $headers = $_SERVER;
   }
   // Get the forwarded IP if it exists
   if (array_key_exists('X-Forwarded-For',$headers) && filter_var($headers['X-Forwarded-For'],FILTER_VALIDATE_IP)) {
      $the_ip = $headers['X-Forwarded-For'];
   } elseif (array_key_exists('HTTP_X_FORWARDED_FOR',$headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'],FILTER_VALIDATE_IP)) {
      $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
   } else {
      $the_ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
   }
   if (has_filter('eme_get_client_ip')) $the_ip=apply_filters('eme_get_client_ip',$the_ip);
   return $the_ip;
}

// add this function so people can call this from there theme's search.php
function eme_wordpress_search_locations() {
   if (isset($_REQUEST['s']))
      return eme_search_locations($_REQUEST['s']);
}

// add this function so people can call this from there theme's search.php
function eme_wordpress_search($scope="future") {
      return eme_wordpress_search_events($scope);
}
function eme_wordpress_search_events($scope="future") {
   if (isset($_REQUEST['s']))
      return eme_search_events($_REQUEST['s'],$scope);
}

function eme_calc_price_fake_booking($event) {
      $booking=eme_new_booking();
      $bookedSeats = 0;
      $bookedSeats_mp = array();
      $event_id=$event['event_id'];
      if (!eme_is_multi($event['price'])) {
         if (isset($_POST['bookings'][$event_id]['bookedSeats']))
            $bookedSeats = intval($_POST['bookings'][$event_id]['bookedSeats']);
         else
            $bookedSeats = 0;
      } else {
         // for multiple prices, we have multiple booked Seats as well
         // the next foreach is only valid when called from the frontend

         // make sure the array contains the correct keys already, since
         // later on in the function eme_record_booking we do a join
         $booking_prices_mp=eme_convert_multi2array($event['price']);
         foreach ($booking_prices_mp as $key=>$value) {
            $bookedSeats_mp[$key] = 0;
         }
         foreach($_POST['bookings'][$event_id] as $key=>$value) {
            if (preg_match('/bookedSeats(\d+)/', $key, $matches)) {
               $field_id = intval($matches[1])-1;
               $bookedSeats += $value;
               $bookedSeats_mp[$field_id]=$value;
            }
         }
      }

      $booking['event_id']=$event['event_id'];
      $booking['booking_seats']=$bookedSeats;
      $booking['booking_seats_mp']=eme_convert_array2multi($bookedSeats_mp);
      $booking['booking_price']=$event['price'];
      $booking['extra_charge']=eme_booking_answers($event,$booking,0);
      $booking['discount']=eme_booking_discount($event,$booking,0);

      return eme_get_total_booking_price($booking);
}

function eme_calc_bookingprice_ajax() {
   header("Content-type: application/json; charset=utf-8");
   // first detect multibooking
   $event_ids=array();
   if (isset($_POST['bookings'])) {
      foreach($_POST['bookings'] as $key=>$val) {
         $event_ids[]=intval($key);
      }
   }
   $total=0;
   $cur='';
   foreach ($event_ids as $event_id) {
      $event=eme_get_event($event_id);
      $total += eme_calc_price_fake_booking($event);
      $cur = $event['currency'];
   }
   $locale = get_locale();
   $result = eme_localized_price($total,$cur);
   echo json_encode(array('total'=>$result));
}

function eme_dyndata_people_ajax() {
   header("Content-type: application/json; charset=utf-8");
   if (!(is_admin())) {
	   echo json_encode(array('result'=>''));
	   return;
   }
   // for new persons, the id=0
   if (isset($_POST['person_id']))
	   $person_id=intval($_POST['person_id']);
   else
	   $person_id=0;
   $var_prefix="dynamic_personfields[$person_id][";
   $var_postfix="]";

   if ($person_id)
	   $dyn_answers = eme_get_dyndata_person_answers($person_id);
   else
	   $dyn_answers = array();

   if (isset($_POST['groups']) && eme_array_integers($_POST['groups']))
	   $groups=$_POST['groups'];
   else
	   $groups=array();
   // We need the groupid 0 as the first element, to show fields not belonging to a specific group (the array keys are not important here, only the values)
   array_unshift($groups,"0");
   $form_html="";
   foreach ($groups as $group_id) {
	   $group = eme_get_group($group_id);
           $fields = eme_get_dyndata_people_fields('group:'.$group_id);
	   if (!empty($fields)) {
		   $form_html.="<hr><div><span id='eme-people-dyndata-group-".$group_id."'>".eme_esc_html(__('Group','events-made-easy').' '.$group['name'])."</span><table>";
		   foreach ($fields as $formfield) {
			   $field_id=$formfield['field_id'];
			   $form_html.="<tr><td>";
			   $name = eme_trans_esc_html($formfield['field_name']);
			   $form_html.="$name</td><td>";
			   $postfield_name="${var_prefix}FIELD".$field_id.$var_postfix;
			   $entered_val = "";
			   foreach ($dyn_answers as $answer) {
				   if ($answer['field_id'] == $field_id) {
					   // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
					   // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
					   $entered_val = $answer['answer'];
					   if (eme_is_multi($entered_val)) {
						   $entered_val = eme_convert_multi2array($entered_val);
					   }
				   }
			   }
			   $form_html.= eme_get_formfield_html($field_id,$postfield_name,$entered_val);
			   $form_html.="</td></tr>";
		   }
		   $form_html.="</table></div>";
	   }
   }
   echo json_encode(array('result'=>$form_html));
}

function eme_dyndata_rsvp_ajax() {
   header("Content-type: application/json; charset=utf-8");
   // first detect multibooking
   $event_ids=array();
   if (isset($_POST['bookings'])) {
      foreach($_POST['bookings'] as $key=>$val) {
         $event_ids[]=intval($key);
      }
   }

   if (isset($_POST['booking_id'])) {
	   $booking=eme_get_booking($_POST['booking_id']);
	   check_admin_referer('eme_rsvp','eme_admin_nonce');
   } else {
	   $booking=eme_new_booking();
   }

   $total=0;
   $cur='';
   $form_html='';
   foreach ($event_ids as $event_id) {
      $event=eme_get_event($event_id);
      $is_multiseat = eme_is_multi($event['event_seats']);
      $total += eme_calc_price_fake_booking($event);
      $cur = $event['currency'];
      if (isset($event['event_properties']['rsvp_dyndata'])) {
	      $conditions=$event['event_properties']['rsvp_dyndata'];
	      foreach ($conditions as $count=>$condition) {
		      // the next check is mostly to eliminate older conditions that didn't have the field-param
		      if (!isset($condition['field']) || empty($condition['field'])) continue;
	              $fieldname_path = eme_replace_rsvp_formfields_placeholders($event,$booking,$condition['field'],0,1);
		      $entered_val = eme_getValueFromPath($_POST, $fieldname_path);
		      if ($condition['condition'] == 'eq' && $entered_val == $condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count);
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_footer']));
		      }
		      if ($condition['condition'] == 'lt' && $entered_val<$condition['condval']) {
			      $template=eme_nl2br_save_html(eme_get_template_format($condition['template_id']));
			      $form_html.=eme_translate(eme_get_template_format($condition['template_id_header']));
			      if ($condition['repeat']) {
				      $entered_val=intval($entered_val);
				      $condition['condval']=intval($condition['condval']);
				      for ($i=$entered_val;$i<$condition['condval'];$i++) {
					      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count,$i-$entered_val);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count);
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
					      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count,$i-$condition['condval']);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count);
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
					      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count,$i-$condition['condval']);
				      }
			      } else {
				      $form_html.=eme_replace_dynamic_rsvp_formfields_placeholders($event,$booking,$template,$count);
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

function eme_strip_js_single($html) {
   // first brute-force remove script tags
   $html=trim(stripslashes($html));
   $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
   return $html;
   // then try to catch what was left ...
   if (class_exists('DOMDocument')) {
	   $doc = new DOMDocument();

	   // load the HTML string we want to strip
	   @$doc->loadHTML($html);

	   // for each tag, remove it from the DOM
	   while (($r = $doc->getElementsByTagName("script")) && $r->length) {
		   $r->item(0)->parentNode->removeChild($r->item(0));
	   }   

	   // get the HTML string back
	   $no_script_html_string = $doc->saveHTML();
   }
   return $no_script_html_string;
}

function eme_strip_js( $value ) {
   if (!is_array($value)) {
      $value=eme_strip_js_single($value);
   } else {
      foreach ($value as $key=>$val) {
         $value[$key]=eme_strip_js_single($val);
      }
   }
   return $value;
}

function eme_strip_weird_single( $value ) {
   $value = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
                         '|[\x00-\x7F][\x80-\xBF]+'.
                         '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
                         '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
                         '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
                          '?', $value);

    //reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
    $value = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
                          '|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $value);
    return $value;
}

function eme_strip_weird( $value ) {
   if (!is_array($value)) {
      $value=eme_strip_weird_single($value);
   } else {
      foreach ($value as $key=>$val) {
         $value[$key]=eme_strip_weird($val);
      }
   }
   return $value;
}

function eme_validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function eme_get_editor_settings($tinymce=false,$quicktags=true,$rows=10) {
   if (!$tinymce && !has_action('eme_add_my_quicktags')) 
	add_action('admin_print_footer_scripts', 'eme_add_my_quicktags');

   return array( 'textarea_rows' => $rows, 'tinymce'=>$tinymce, 'quicktags'=>$quicktags );
}

function eme_nl2br_save_html($string) {
    // avoid looping if no tags in the string.
    if (!preg_match("#</.*>#", $string))
        return nl2br($string);

    // if br is found, first replace it by \n
    $string = preg_replace("/<br\W*?\/>\n?/", "\n", $string);
    // then replace other lineendings
    $string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);

    // now, let's see if the last line has a \n, if not we'll need to remove the last \n we add later on
    if (preg_match('/\n$/', $test))
	    $lastline_has_newline=1;
    else
	    $lastline_has_newline=0;

    $lines=explode("\n", $string);
    $output='';
    foreach($lines as $line) {
        $line = rtrim($line);
        // See if the line finished with has an html opening or closing tag
        if (!preg_match("#</?[^/<>]*>$#", $line))
            $line .= '<br />';
        $output .= $line . "\n";
    }

    if (!$lastline_has_newline)
	    $output = preg_replace('/(<br \/>)?\n$/','',$output);

    return $output;
}

function eme_wp_date_format_php_to_js( $dateFormat ) {
	$chars = array(
		// Day
		'd' => 'dd',
		'j' => 'd',
		'l' => 'DD',
		'D' => 'D',
		'z' => 'o',
		// Month
		'm' => 'mm',
		'n' => 'm',
		'F' => 'MM',
		'M' => 'M',
		// Year
		'Y' => 'yy',
		'y' => 'y',
	);
	return strtr( (string) $dateFormat, $chars );
}
function eme_wp_time_format_php_to_js( $timeFormat ) {
	$chars = array(
		'a' => 'tt',
		'A' => 'TT',
		'g' => 'h',
		'h' => 'hh',
		'G' => 'H',
		'H' => 'HH',
		'i' => get_option('eme_time_remove_leading_zeros')? 'm':'mm',
		's' => 'ss',
		'T' => 'z',
	);
	return strtr( (string) $timeFormat, $chars );
}

function eme_getValueFromPath($arr, $path) {
    // todo: add checks on $path
    $dest = $arr;
    $finalKey = array_pop($path);
    foreach ($path as $key) {
	if (!isset($dest[$key])) return '';
        $dest = $dest[$key];
    }
    if (!isset($dest[$finalKey])) return '';
    return $dest[$finalKey];
}

function eme_return_answerid($answers,$booking_id=0,$person_id=0,$member_id=0,$field_id=0,$grouping=0,$occurence=0) {
	foreach ($answers as $answer) {
		if ($answer['booking_id']==$booking_id &&
		    $answer['person_id']==$person_id &&
		    $answer['member_id']==$member_id &&
		    $answer['field_id']==$field_id &&
		    $answer['grouping']==$grouping &&
		    $answer['occurence']==$occurence)
		    return $answer['answer_id'];
	}
	return 0;
}

function eme_get_wp_image($image_id) {
	$image = get_post($image_id );
	return array(
		'alt' => get_post_meta( $image->ID, '_wp_attachment_image_alt', true ),
		'caption' => $image->post_excerpt,
		'description' => $image->post_content,
		'href' => get_permalink( $image->ID ),
		'src' => $image->guid,
		'title' => $image->post_title
	);
}
?>
