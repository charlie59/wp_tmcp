<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_actions_init() {
   // first the no cache headers
   //nocache_headers();
   eme_load_textdomain();

   // now, first update the DB if needed
   $db_version = get_option('eme_version');
   if ($db_version && $db_version != EME_DB_VERSION) {
      // add possible new options
      eme_add_options();
      eme_update_options($db_version);

      // update the DB tables
      // to do: check if the DB update succeeded ...
      eme_create_tables();
      eme_flushRules();

      // some cron we want
      if (! wp_next_scheduled ( 'eme_cron_member_daily_actions' )) {
         wp_schedule_event(time(), 'daily', 'eme_cron_member_daily_actions');
      }

      // now set the version correct
      update_option('eme_version', EME_DB_VERSION);
   }

   // now first all ajax ops: exit needed
   if (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public_single' && isset ( $_GET ['event_id'] )) {
      eme_ical_single();
      exit;
   }
   if (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public') {
      eme_ical();
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'add_bookings' && !is_admin()) {
      if (get_option('eme_captcha_for_booking'))
         eme_session_start();
      eme_add_bookings_ajax();
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'add_member' && !is_admin()) {
      if (get_option('eme_captcha_for_booking'))
         eme_session_start();
      eme_add_member_ajax();
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'delete_bookings') {
      if (get_option('eme_captcha_for_booking'))
         eme_session_start();
      eme_delete_bookings_ajax();
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'rsvp_autocomplete_people' && isset($_POST['lastname'])) {
      $no_wp_die=1;
      
      if ((!isset($_POST['eme_rsvp_nonce']) && !isset($_POST['eme_admin_nonce'])) ||
         (isset($_POST['eme_rsvp_nonce']) && !wp_verify_nonce( $_POST['eme_rsvp_nonce'], 'add_booking' )) ||
         (isset($_POST['eme_admin_nonce']) && !wp_verify_nonce( $_POST['eme_admin_nonce'], 'eme_rsvp' ))) { 
         header("Content-type: application/json; charset=utf-8");
         echo json_encode($_POST['eme_admin_nonce']);
      } elseif (is_admin()) {
         eme_people_autocomplete_ajax($no_wp_die);
      } elseif (is_user_logged_in() && isset($_POST['eme_event_ids'])) {
         $event=eme_get_event(intval($_POST['eme_event_ids'][0]));
         $current_userid=get_current_user_id();
         if (current_user_can( get_option('eme_cap_edit_events')) ||
            (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            eme_people_autocomplete_ajax($no_wp_die);
         }
      } else {
         header("Content-type: application/json; charset=utf-8");
         echo json_encode(array());
      }
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && in_array($_POST['eme_ajax_action'], array('previewmail','genericmail','testmail','eventmail'))) {
      eme_send_mails_ajax_actions($_POST['eme_ajax_action']);
      exit;
   }

   if (isset($_POST['eme_ajaxCalendar']) && $_POST['eme_ajaxCalendar'] == true) {
      eme_filter_calendar_ajax();
      exit;
   }
   if (isset($_POST['eme_ajaxCalendar']) && $_POST['eme_ajaxCalendar'] == true) {
      eme_filter_calendar_ajax();
      exit;
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'client_clock_submit') {
      eme_client_clock_callback();
      exit;
   }
   if (isset ( $_GET['eme_rss'] ) && $_GET['eme_rss'] == 'main') {
      eme_rss();
      exit;
   }
   if (isset ( $_GET['eme_captcha'] ) && $_GET['eme_captcha'] == 'generate') {
      eme_captcha_generate();
      exit;
   }
   if (isset ( $_GET['eme_attendees'] ) && $_GET['eme_attendees'] == 'report') {
      if (isset($_GET['scope']) && isset($_GET['event_template_id']) && isset($_GET['attend_template_id']) && is_user_logged_in()) {
         eme_attendees_report(eme_sanitize_request($_GET['scope']),eme_sanitize_request($_GET['category']),eme_sanitize_request($_GET['notcategory']),intval($_GET['event_template_id']),intval($_GET['attend_template_id']));
      }
      exit;
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == 'autocomplete_locations' && is_admin()) {
      eme_locations_search_ajax();
      exit;
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == 'booking_printable' && is_admin() && isset($_GET['event_id'])) {
      eme_printable_booking_report(intval($_GET['event_id']));
      exit();
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == 'booking_csv' && is_admin() && isset($_GET['event_id'])) {
      eme_csv_booking_report(intval($_GET['event_id']));
      exit();
   }

   if (isset($_GET['query']) && $_GET['query'] == 'GlobalMapData') {
      $eventful = isset($_GET['eventful'])?$_GET['eventful']:false;
      $map_id = isset($_GET['map_id'])?$_GET['map_id']:0;
      $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
      $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;
      eme_global_map_json((bool)$eventful,$_GET['scope'],$_GET['category'],$map_id);
      exit();
   }

   if (isset($_POST['eme_override_eventAction']) && $_POST['eme_override_eventAction']=="calc_memberprice") {
      eme_calc_memberprice_ajax();
      exit();
   }
   if (isset($_POST['eme_override_eventAction']) && $_POST['eme_override_eventAction']=="dynmemberdata") {
      eme_dyndata_member_ajax();
      exit();
   }
   if (isset($_POST['eme_override_eventAction']) && $_POST['eme_override_eventAction']=="calc_bookingprice") {
      eme_calc_bookingprice_ajax();
      exit();
   }
   if (isset($_POST['eme_override_eventAction']) && $_POST['eme_override_eventAction']=="dynbookingdata") {
      eme_dyndata_rsvp_ajax();
      exit();
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action']=="dynpersondata") {
      eme_dyndata_people_ajax();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && ($_GET['eme_eventAction']=="paypal_notification" || $_GET['eme_eventAction']=="paypal_ipn")) {
      eme_paypal_notification();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && ($_GET['eme_eventAction']=="2co_notification" || $_GET['eme_eventAction']=="2co_ins")) {
      eme_2co_notification();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && $_GET['eme_eventAction']=="webmoney_notification") {
      eme_webmoney_notification();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && $_GET['eme_eventAction']=="mollie_notification") {
      eme_mollie_notification();
      exit();
   }
   if (isset($_POST['eme_eventAction']) && ($_POST['eme_eventAction']=="fdgg_notification" || $_POST['eme_eventAction']=="fdgg_ipn")) {
      eme_fdgg_notification();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && $_GET['eme_eventAction']=="worldpay_notification") {
      eme_worldpay_notification();
      exit();
   }
   if (isset($_GET['eme_eventAction']) && $_GET['eme_eventAction']=="sagepay_notification") {
      eme_sagepay_notification();
      // sagepay doesn't use a notification url, but sends the status along as part of the return url, so we just check
      // the status and set paid or not, but then we continue regular flow of events
   }

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction']=="stripe_charge") {
      eme_stripe_charge();
      // stripe uses a local charge function, so we charge the card, set paid or not and then continue regular flow of events
   }
   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction']=="braintree_charge") {
      eme_braintree_charge();
      // braintree uses a local charge function, so we charge the card, set paid or not and then continue regular flow of events
   }
}
add_action('init','eme_actions_init',1);

function eme_actions_admin_init() {
   global $current_user, $eme_timezone;
   $eme_date_obj = new ExpressiveDate(null,$eme_timezone);
   eme_options_register();

   // make sure the captcha doesn't cause problems
   if (get_option('eme_captcha_for_booking') && !function_exists('imagecreatetruecolor'))
      update_option('eme_captcha_for_booking', 0);

   $user_id = $current_user->ID;
   if (isset($_GET['eme_notice_ignore']) && ($_GET['eme_notice_ignore']=='hello'))
      add_user_meta($user_id, 'eme_hello_notice_ignore', $eme_date_obj->format('Ymd'), true);
   if (isset($_GET['eme_notice_ignore']) && ($_GET['eme_notice_ignore']=='donate'))
      add_user_meta($user_id, 'eme_donate_notice_ignore', $eme_date_obj->format('Ymd'), true);

   // flush the SEO rules if the event page has been changed
   eme_handle_get();
}
add_action('admin_init','eme_actions_admin_init');

function eme_actions_widgets_init() {
   register_widget( 'WP_Widget_eme_list' );
   register_widget( 'WP_Widget_eme_calendar' );
}
add_action( 'widgets_init', 'eme_actions_widgets_init' );

// Client clock usage and captcha need a session
if (get_option('eme_use_client_clock') || get_option('eme_captcha_for_booking')) {
   // If needed, add high priority action to enable session variables.
   add_action('init', 'eme_session_start', 1);
   if (get_option('eme_clean_session_data')) {
      add_action('wp_logout', 'eme_session_destroy');
      add_action('wp_login', 'eme_session_destroy');
   }
}

add_action('wp_head', 'eme_general_head' );
add_action('wp_footer', 'eme_general_footer');
//if (get_option('eme_load_js_in_header')) {
//   add_action('wp_head', 'eme_ajaxize_calendar');
//} else {
//   add_action('wp_footer', 'eme_ajaxize_calendar');
//}

function eme_admin_register_scripts() {
   wp_register_script( 'eme-jquery-plugin', EME_PLUGIN_URL.'js/jquery-datepick/jquery.plugin.min.js');
   wp_register_script( 'eme-jquery-datepick',EME_PLUGIN_URL.'js/jquery-datepick/jquery.datepick.js',array( 'jquery','eme-jquery-plugin' ));
   wp_register_script( 'eme-jquery-mousewheel', EME_PLUGIN_URL.'js/jquery-mousewheel/jquery.mousewheel.min.js', array('jquery'));
   wp_register_script( 'eme-jquery-timeentry', EME_PLUGIN_URL.'js/timeentry/jquery.timeentry.js', array('jquery','eme-jquery-plugin','eme-jquery-mousewheel'));
   wp_register_script( 'eme-select2', EME_PLUGIN_URL.'js/jquery-select2/select2-4.0.4/dist/js/select2.min.js', 'eme-jquery-mousewheel');
   wp_register_script( 'eme-jquery-ui-datetimepicker', EME_PLUGIN_URL.'js/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.js', array('jquery-ui-datepicker'));
   wp_register_script( 'eme-jquery-datatables', EME_PLUGIN_URL."js/jquery-datatables/js/jquery.dataTables.min.js",array( 'jquery' ));
   #wp_register_script( 'eme-datatables-clearsearch', EME_PLUGIN_URL."js/jquery-datatables/plugins/datatables_clearsearch.js");
   #wp_register_script( 'eme-datatables-colreorder', EME_PLUGIN_URL."js/jquery-datatables/extensions/ColReorder/js/dataTables.colReorder.js");
   #wp_register_script( 'eme-datatables-buttons', EME_PLUGIN_URL."js/jquery-datatables/extensions/Buttons/js/dataTables.buttons.js");
   #wp_register_script( 'eme-datatables-buttons-print', EME_PLUGIN_URL."js/jquery-datatables/extensions/Buttons/js/buttons.print.js");
   #wp_register_script( 'eme-datatables-buttons-html5', EME_PLUGIN_URL."js/jquery-datatables/extensions/Buttons/js/buttons.html5.js");
   #wp_register_script( 'eme-datatables-buttons-colvis', EME_PLUGIN_URL."js/jquery-datatables/extensions/Buttons/js/buttons.colVis.js");
   $gmap_api_key = get_option('eme_gmap_api_key' );
   if (!empty($gmap_api_key)) $gmap_api_key="key=$gmap_api_key";
   wp_register_script( 'eme-print', EME_PLUGIN_URL.'js/jquery.printelement.js',array('jquery'));
   wp_register_script( 'eme-jquery-jtable', EME_PLUGIN_URL.'js/jtable-2.5.0/jquery.jtable.min.js',array( 'jquery-ui-core','jquery-ui-widget', 'jquery-ui-datepicker', 'jquery-ui-dialog' ));
   wp_register_script( 'eme-jtable-search', EME_PLUGIN_URL.'js/jtable-2.5.0/extensions/jquery.jtable.toolbarsearch.js',array( 'eme-jquery-jtable' ));
   wp_register_script( 'eme-basic', EME_PLUGIN_URL.'js/eme.js',array('jquery','eme-select2'));
   wp_register_script( 'eme-admin', EME_PLUGIN_URL.'js/eme_admin.js', array('jquery', 'eme-jquery-jtable'));
   wp_register_script( 'eme-google-maps', '//maps.google.com/maps/api/js?'.$gmap_api_key);
   wp_register_script( 'eme-admin-maps', EME_PLUGIN_URL.'js/eme_admin_maps.js', array('jquery','eme-google-maps'));
   wp_register_script( 'eme-autocomplete-rsvp', EME_PLUGIN_URL.'js/eme_autocomplete_rsvp.js',array( 'jquery-ui-autocomplete' ));
   wp_register_script( 'eme-options', EME_PLUGIN_URL.'js/eme_admin_options.js',array( 'jquery' ));
   wp_register_script( 'eme-formfields', EME_PLUGIN_URL.'js/eme_admin_fields.js',array( 'jquery' ) );

   $locale_code = get_locale();
   $locale_code = preg_replace( "/_/","-", $locale_code );
   $locale_file = EME_PLUGIN_DIR. "js/jtable-2.5.0/localization/jquery.jtable.$locale_code.js";
   $locale_file_url = EME_PLUGIN_URL. "js/jtable-2.5.0/localization/jquery.jtable.$locale_code.js";
   // for english, no translation code is needed)
   if ($locale_code != "en-US") {
	   if (!file_exists($locale_file)) {
		   $locale_code = substr ( $locale_code, 0, 2 );
		   $locale_file = EME_PLUGIN_DIR. "js/jtable-2.5.0/localization/jquery.jtable.$locale_code.js";
		   $locale_file_url = EME_PLUGIN_URL. "js/jtable-2.5.0/localization/jquery.jtable.$locale_code.js";
	   }
	   if (file_exists($locale_file))
		   wp_register_script('eme-jtable-locale',$locale_file_url);
   }

   wp_register_script( 'eme-rsvp', EME_PLUGIN_URL.'js/eme_admin_rsvp.js',array( 'eme-jtable-search', 'eme-autocomplete-rsvp', 'eme-print' ));
   wp_register_script( 'eme-sendmails', EME_PLUGIN_URL.'js/eme_admin_sendmails.js',array('jquery-ui-autocomplete', 'jquery-ui-tabs'));
   wp_register_script( 'eme-discounts', EME_PLUGIN_URL.'js/eme_admin_discounts.js',array( 'eme-jtable-search' ));
   wp_register_script( 'eme-people', EME_PLUGIN_URL.'js/eme_admin_people.js',array('jquery-ui-autocomplete', 'eme-print'));
   wp_register_script( 'eme-templates', EME_PLUGIN_URL.'js/eme_admin_templates.js');
   wp_register_script( 'eme-members', EME_PLUGIN_URL.'js/eme_admin_members.js',array('jquery-ui-autocomplete', 'eme-print', 'jquery-ui-tabs'));
   wp_register_script( 'eme-events', EME_PLUGIN_URL.'js/eme_admin_events.js',array('jquery-ui-autocomplete', 'jquery-ui-tabs', 'eme-jquery-timeentry', 'eme-print'));
   wp_register_script( 'eme-locations', EME_PLUGIN_URL.'js/eme_admin_locations.js',array('eme-print'));
   wp_register_style('eme_stylesheet',EME_PLUGIN_URL."css/eme.css");
   $eme_css_name=get_stylesheet_directory()."/eme.css";
   if (file_exists($eme_css_name)) {
      $eme_css_url=get_stylesheet_directory_uri()."/eme.css";
      wp_register_style('eme_stylesheet_extra',get_stylesheet_directory_uri().'/eme.css','eme_stylesheet');
   }
   wp_register_style('eme-jquery-ui-autocomplete',EME_PLUGIN_URL."css/jquery.autocomplete.css");
   wp_register_style('eme-jquery-ui-css',EME_PLUGIN_URL."css/jquery-ui-theme-smoothness-1.11.3/jquery-ui.min.css");
   wp_register_style('eme-jquery-ui-timepicker-css',EME_PLUGIN_URL."js/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.css");
   wp_register_style('eme-jquery-jtable-css',EME_PLUGIN_URL."js/jtable-2.5.0/themes/jqueryui/jtable_jqueryui.css");
   wp_register_style('eme-jquery-select2-css',EME_PLUGIN_URL."js/jquery-select2/select2-4.0.4/dist/css/select2.min.css");
   wp_register_style('eme-jtables-css',EME_PLUGIN_URL."css/jquery.jtables.css");
   eme_admin_enqueue_js();
}
add_action('admin_enqueue_scripts','eme_admin_register_scripts');

function eme_register_scripts() {
   // the frontend also needs the datepicker (the month filter)
   $locale_code = get_locale();
   $locale_code = preg_replace( "/_/","-", $locale_code );

   if (get_option('eme_load_js_in_header' )) {
      $load_js_in_footer=false;
   } else {
      $load_js_in_footer=true;
   }
   $language = eme_detect_lang();
   if (!empty($language)) {
      $jquery_override_lang=", lang: '".$language."'";
   } else {
      $jquery_override_lang="";
   }

//   wp_register_script( 'eme-calendar', EME_PLUGIN_URL.'js/eme_calendar.js',array('jquery'),'', false);
   wp_register_script( 'eme-jquery-plugin', EME_PLUGIN_URL.'js/jquery-datepick/jquery.plugin.min.js','','',$load_js_in_footer);
   wp_register_script( 'eme-jquery-mousewheel', EME_PLUGIN_URL.'js/jquery-mousewheel/jquery.mousewheel.min.js', array('jquery'));
   wp_register_script( 'eme-select2', EME_PLUGIN_URL.'js/jquery-select2/select2-4.0.4/dist/js/select2.min.js', 'eme-jquery-mousewheel');
   wp_register_script( 'eme-jquery-datepick',EME_PLUGIN_URL.'js/jquery-datepick/jquery.datepick.js',array( 'jquery','eme-jquery-plugin' ),'', $load_js_in_footer);
   wp_register_script( 'eme-basic', EME_PLUGIN_URL.'js/eme.js',array('jquery','jquery-ui-datepicker','eme-select2'),'', $load_js_in_footer);
   $translation_array = array(
	   'translate_plugin_url' => EME_PLUGIN_URL,
	   'translate_clear' => __('Clear','events-made-easy')
                             );
   wp_localize_script( 'eme-basic', 'emebasic', $translation_array );
   wp_enqueue_script('eme-basic');

   if (get_option('eme_use_client_clock') && !isset($_SESSION['eme_client_unixtime'])) {
        // client clock should be executed asap, so load it in the header, and no defer
   	wp_enqueue_script( 'eme-client_clock_submit', EME_PLUGIN_URL.'js/client-clock.js', array('jquery'));
   }

   // the frontend also needs the autocomplete (rsvp form)
   $search_tables=get_option('eme_autocomplete_sources');
   if ($search_tables!='none' && is_user_logged_in()) {
      wp_register_script( 'eme-autocomplete-rsvp', EME_PLUGIN_URL.'js/eme_autocomplete_rsvp.js',array( 'jquery-ui-autocomplete' ),'', $load_js_in_footer);
   }
	
   wp_enqueue_style('eme-jquery-ui-css',EME_PLUGIN_URL."css/jquery-ui-theme-smoothness-1.11.3/jquery-ui.min.css");
   wp_enqueue_style('eme-jquery-ui-autocomplete',EME_PLUGIN_URL."css/jquery.autocomplete.css");
   wp_enqueue_style('eme-jquery-select2-css',EME_PLUGIN_URL."js/jquery-select2/select2-4.0.4/dist/css/select2.min.css");
   wp_enqueue_style('eme_stylesheet',EME_PLUGIN_URL."css/eme.css");
   $eme_css_name=get_stylesheet_directory()."/eme.css";
   if (file_exists($eme_css_name)) {
      wp_enqueue_style('eme_stylesheet_extra',get_stylesheet_directory_uri().'/eme.css','eme_stylesheet');
   }
}
add_action('wp_enqueue_scripts','eme_register_scripts');

function eme_add_defer_attribute($tag, $handle) {
   // add script handles to the array below
   // $scripts_to_defer = array('eme-basic', 'another-handle');
   $scripts_to_defer = array('eme-basic');
   
   foreach($scripts_to_defer as $defer_script) {
      if ($defer_script === $handle) {
         return str_replace(' src', ' defer="defer" src', $tag);
      }
   }
   return $tag;
}
add_filter('script_loader_tag', 'eme_add_defer_attribute', 10, 2);

add_action('template_redirect', 'eme_template_redir' );
add_action('admin_notices', 'eme_admin_notices' );

function eme_admin_notices() {
   global $pagenow, $plugin_page, $eme_timezone;
   $current_user = wp_get_current_user();
   $user_id = $current_user->ID;
   $eme_date_obj = new ExpressiveDate(null,$eme_timezone);

   $events_page_id = eme_get_events_page_id();
   if ($pagenow == 'post.php' && isset ($_GET['action']) && $_GET['action'] == 'edit' && isset ($_GET['post']) && $_GET['post'] == "$events_page_id") {
      $message = sprintf ( __ ( "This page corresponds to <strong>Events Made Easy</strong> events page. Its content will be overriden by <strong>Events Made Easy</strong>. If you want to display your content, you can can assign another page to <strong>Events Made Easy</strong> in the the <a href='%s'>Settings</a>. ", 'events-made-easy'), 'admin.php?page=eme-options' );
      $notice = "<div class='error'><p>$message</p></div>";
      echo $notice;
   }

   $single = true;
   $eme_hello_notice_ignore=get_user_meta($user_id, 'eme_hello_notice_ignore', $single);
   $eme_donate_notice_ignore=get_user_meta($user_id, 'eme_donate_notice_ignore', $single);
   // let's show the donate notice again after 1 month
   if ($eme_donate_notice_ignore && (intval($eme_date_obj->format('Ymd'))-intval($eme_donate_notice_ignore) > 30)) {
      delete_user_meta($user_id, 'eme_donate_notice_ignore');
      $eme_donate_notice_ignore=0;
   }
   if (!$eme_hello_notice_ignore && preg_match('/^eme-/',$plugin_page)) { ?>
      <div class="updated notice"><p> <?php echo sprintf ( __ ( "<p>Hey, <strong>%s</strong>, welcome to <strong>Events Made Easy</strong>! We hope you like it around here.</p><p>Now it's time to insert events lists through <a href='%s' title='Widgets page'>widgets</a>, <a href='%s' title='Template tags documentation'>template tags</a> or <a href='%s' title='Shortcodes documentation'>shortcodes</a>.</p><p>By the way, have you taken a look at the <a href='%s' title='Change settings'>Settings page</a>? That's where you customize the way events and locations are displayed.</p><p>What? Tired of seeing this advice? I hear you, <a href=\"%s\" title=\"Don't show this advice again\">click here</a> and you won't see this again!</p>", 'events-made-easy'), $current_user->display_name, admin_url("widgets.php"), 'http://www.e-dynamics.be/wordpress/#template-tags', 'http://www.e-dynamics.be/wordpress/#shortcodes', admin_url("admin.php?page=eme-options"), add_query_arg (array("eme_notice_ignore"=>"hello"), remove_query_arg("eme_notice_ignore")) ); ?> </p></div>
   <?php
   }

   if (!$eme_donate_notice_ignore && preg_match('/^eme-/',$plugin_page)) {
   ?>
<div class="updated notice" style="padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
    <div>
        <h3><?php echo __('Donate to the development of Events Made Easy', 'events-made-easy'); ?></h3>
<?php
_e('If you find <strong>Events Made Easy</strong> useful to you, please consider making a small donation to help contribute to my time invested and to further development. Thanks for your kind support!', 'events-made-easy');
?>
  <br /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<?php
echo sprintf ( __ ( "<a href=\"%s\" title=\"I already donated\">I already donated.</a>", 'events-made-easy'), add_query_arg (array("eme_notice_ignore"=>"donate"), remove_query_arg("eme_notice_ignore")));
?>
</form>

   </div>
</div>

   <?php
   }
}

// when editing other profiles then your own
add_action('edit_user_profile', 'eme_user_profile') ;
add_action('edit_user_profile_update','eme_update_user_profile');
// when editing your own profile
add_action('show_user_profile', 'eme_user_profile') ;
add_action('personal_options_update','eme_update_user_profile');

add_action( 'wp_ajax_eme_dismiss_admin_notice', 'eme_dismiss_admin_notice' );
function eme_dismiss_admin_notice() {
   $option_name        = eme_sanitize_text_field( $_POST['option_name'] );
   $dismissible_length = eme_sanitize_text_field( $_POST['dismissible_length'] );

   if ( 'forever' != $dismissible_length ) {
      $dismissible_length = strtotime( absint( $dismissible_length ) . ' days' );
   }

   check_ajax_referer( 'eme-dismissible-notice', 'nonce' );
   update_option( $option_name, $dismissible_length );
   wp_die();
}

function eme_enqueue_datepick() {
   wp_enqueue_script('eme-jquery-datepick');
   wp_enqueue_style('eme-jquery-datepick',EME_PLUGIN_URL."js/jquery-datepick/jquery.datepick.css");
   // jquery ui locales are with dashes, not underscores
   $locale_code = get_locale();
   $locale_code = preg_replace( "/_/","-", $locale_code );
   $locale_file = EME_PLUGIN_DIR. "js/jquery-datepick/jquery.datepick-$locale_code.js";
   $locale_file_url = EME_PLUGIN_URL. "js/jquery-datepick/jquery.datepick-$locale_code.js";
   // for english, no translation code is needed)
   if ($locale_code != "en-US") {
      if (!file_exists($locale_file)) {
         $locale_code = substr ( $locale_code, 0, 2 );
	 $locale_file = EME_PLUGIN_DIR. "js/jquery-datepick/jquery.datepick-$locale_code.js";
	 $locale_file_url = EME_PLUGIN_URL. "js/jquery-datepick/jquery.datepick-$locale_code.js";
      }
      if (file_exists($locale_file))
	 wp_enqueue_script('eme-jquery-datepick-locale',$locale_file_url);
   }
}

function eme_add_my_quicktags() {
   global $plugin_page;
   if (preg_match('/^eme-/',$plugin_page) && wp_script_is('quicktags')) {
?>
<script type="text/javascript">
if (typeof QTags != 'undefined') {
   QTags.addButton( 'br', 'br', '<br />' );
   QTags.addButton( 'p', 'p', '<p>', '</p>' );
}
</script>
<?php
   }
}
// the eme_add_my_quicktags action will be added when needed, see function eme_get_editor_settings
// add_action('admin_print_footer_scripts', 'eme_add_my_quicktags');

?>
