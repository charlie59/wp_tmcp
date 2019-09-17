<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: Events Made Easy
Version: 2.0.10
Plugin URI: http://www.e-dynamics.be/wordpress
Description: Manage and display events and memberships. Also includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds; Paypal, 2Checkout and others.
Author: Franky Van Liedekerke
Author URI: http://www.e-dynamics.be/
Text Domain: events-made-easy
Domain Path: /langs
*/

/*
Copyright (c) 2010, Franky Van Liedekerke.
Copyright (c) 2011, Franky Van Liedekerke.
Copyright (c) 2012, Franky Van Liedekerke.
Copyright (c) 2013, Franky Van Liedekerke.
Copyright (c) 2014, Franky Van Liedekerke.
Copyright (c) 2015, Franky Van Liedekerke.
Copyright (c) 2016, Franky Van Liedekerke.
Copyright (c) 2017, Franky Van Liedekerke.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Setting constants
define('EME_DB_VERSION', 166);
define('EME_PLUGIN_URL', plugins_url('',plugin_basename(__FILE__)).'/'); //PLUGIN URL
define('EME_PLUGIN_DIR', ABSPATH.PLUGINDIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'/'); //PLUGIN DIRECTORY
define('EVENTS_TBNAME','eme_events');
define('RECURRENCE_TBNAME','eme_recurrence');
define('LOCATIONS_TBNAME','eme_locations');
define('BOOKINGS_TBNAME','eme_bookings');
define('PEOPLE_TBNAME','eme_people');
define('GROUPS_TBNAME','eme_groups');
define('USERGROUPS_TBNAME','eme_usergroups');
define('CATEGORIES_TBNAME', 'eme_categories');
define('HOLIDAYS_TBNAME', 'eme_holidays');
define('TEMPLATES_TBNAME', 'eme_templates');
define('FORMFIELDS_TBNAME', 'eme_formfields');
define('FIELDTYPES_TBNAME', 'eme_fieldtypes');
define('ANSWERS_TBNAME', 'eme_answers');
define('PAYMENTS_TBNAME', 'eme_payments');
define('DISCOUNTS_TBNAME', 'eme_discounts');
define('DISCOUNTGROUPS_TBNAME', 'eme_dgroups');
define('MQUEUE_TBNAME', 'eme_mqueue');
define('MAILINGS_TBNAME', 'eme_mailings');
define('MEMBERS_TBNAME', 'eme_members');
define('MEMBERSHIPS_TBNAME', 'eme_memberships');
define('MIN_CAPABILITY', 'edit_posts');   // Minimum user level to edit own events
define('AUTHOR_CAPABILITY', 'publish_posts');   // Minimum user level to put an event in public/private state
define('EDIT_CAPABILITY', 'edit_others_posts'); // Minimum user level to edit any event
define('SETTING_CAPABILITY', 'activate_plugins');  // Minimum user level to edit settings
define('DEFAULT_CAP_ADD_EVENT','edit_posts');
define('DEFAULT_CAP_AUTHOR_EVENT','publish_posts');
define('DEFAULT_CAP_PUBLISH_EVENT','publish_posts');
define('DEFAULT_CAP_LIST_EVENTS','edit_posts');
define('DEFAULT_CAP_EDIT_EVENTS','edit_others_posts');
define('DEFAULT_CAP_ADD_LOCATION','edit_others_posts');
define('DEFAULT_CAP_AUTHOR_LOCATION','edit_others_posts');
define('DEFAULT_CAP_EDIT_LOCATIONS','edit_others_posts');
define('DEFAULT_CAP_CATEGORIES','activate_plugins');
define('DEFAULT_CAP_HOLIDAYS','activate_plugins');
define('DEFAULT_CAP_TEMPLATES','activate_plugins');
define('DEFAULT_CAP_PEOPLE','edit_posts');
define('DEFAULT_CAP_DISCOUNTS','edit_posts');
define('DEFAULT_CAP_MEMBERS','edit_posts');
define('DEFAULT_CAP_APPROVE','edit_others_posts');
define('DEFAULT_CAP_REGISTRATIONS','edit_others_posts');
define('DEFAULT_CAP_FORMS','edit_others_posts');
define('DEFAULT_CAP_CLEANUP','activate_plugins');
define('DEFAULT_CAP_SETTINGS','activate_plugins');
define('DEFAULT_CAP_SEND_MAILS','edit_posts');
define('DEFAULT_CAP_SEND_OTHER_MAILS','edit_others_posts');
define('DEFAULT_EVENT_LIST_HEADER_FORMAT',"<ul class='eme_events_list'>");
define('DEFAULT_EVENT_LIST_ITEM_FORMAT', '<li>#_STARTDATE - #_STARTTIME<br /> #_LINKEDNAME<br />#_TOWN </li>');
define('DEFAULT_EVENT_LIST_FOOTER_FORMAT','</ul>');
define('DEFAULT_CAT_EVENT_LIST_HEADER_FORMAT',"<ul class='eme_events_list'>");
define('DEFAULT_CAT_EVENT_LIST_FOOTER_FORMAT','</ul>');
define('DEFAULT_SINGLE_EVENT_FORMAT', '<p>#_STARTDATE - #_STARTTIME</p><p>#_TOWN</p><p>#_NOTES</p><p>#_ADDBOOKINGFORM</p><p>#_MAP</p>'); 
define('DEFAULT_EVENTS_PAGE_TITLE',__('Events','events-made-easy') ) ;
define('DEFAULT_EVENT_PAGE_TITLE_FORMAT', '#_EVENTNAME'); 
define('DEFAULT_EVENT_HTML_TITLE_FORMAT', '#_EVENTNAME'); 
define('DEFAULT_ICAL_DESCRIPTION_FORMAT',"#_NOTES");
define('DEFAULT_ICAL_LOCATION_FORMAT',"#_LOCATIONNAME, #_ADDRESS, #_TOWN");
define('DEFAULT_RSS_DESCRIPTION_FORMAT',"#_STARTDATE - #_STARTTIME <br /> #_NOTES <br />#_LOCATIONNAME <br />#_ADDRESS <br />#_TOWN");
define('DEFAULT_RSS_TITLE_FORMAT',"#_EVENTNAME");
define('DEFAULT_ICAL_TITLE_FORMAT',"#_EVENTNAME");
define('DEFAULT_MAP_TEXT_FORMAT', '<strong>#_LOCATIONNAME</strong><p>#_ADDRESS</p><p>#_TOWN</p>');
define('DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT','<li>#_LINKEDNAME<ul><li>#_STARTDATE</li><li>#_TOWN</li></ul></li>');
define('DEFAULT_NO_EVENTS_MESSAGE', __('No events', 'events-made-easy'));
define('DEFAULT_SINGLE_LOCATION_FORMAT', '<p>#_ADDRESS</p><p>#_TOWN</p>#_DESCRIPTION #_MAP'); 
define('DEFAULT_LOCATION_PAGE_TITLE_FORMAT', '#_LOCATIONNAME'); 
define('DEFAULT_LOCATION_HTML_TITLE_FORMAT', '#_LOCATIONNAME'); 
define('DEFAULT_LOCATION_BALLOON_FORMAT', "<strong>#_LOCATIONNAME</strong><br />#_ADDRESS - #_TOWN<br /><a href='#_LOCATIONPAGEURL'>Details</a>");
define('DEFAULT_LOCATION_LIST_HEADER_FORMAT',"<ul class='eme_locations_list'>");
define('DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT', "<li>#_EVENTNAME - #_STARTDATE - #_STARTTIME</li>");
define('DEFAULT_LOCATION_LIST_FOOTER_FORMAT','</ul>');
define('DEFAULT_LOCATION_NO_EVENTS_MESSAGE', __('<li>No events in this location</li>', 'events-made-easy'));
define('DEFAULT_FULL_CALENDAR_EVENT_FORMAT', '<li>#_LINKEDNAME</li>');
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT', "#_EVENTNAME" );
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR', ", ");
define('DEFAULT_USE_SELECT_FOR_LOCATIONS', false);
define('DEFAULT_ATTRIBUTES_ENABLED', true);
define('DEFAULT_RECURRENCE_ENABLED', true);
define('DEFAULT_RSVP_ENABLED', true);
define('DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING', __('Send your booking', 'events-made-easy'));
define('DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING', __('Cancel your booking', 'events-made-easy'));
define('DEFAULT_ATTENDEES_LIST_FORMAT','<li>#_ATTENDNAME (#_ATTENDSPACES)</li>');
define('DEFAULT_BOOKINGS_LIST_FORMAT','<li>#_RESPNAME (#_RESPSPACES)</li>');
define('DEFAULT_BOOKINGS_LIST_HEADER_FORMAT',"<ul class='eme_bookings_list_ul'>");
define('DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT','</ul>');
define('DEFAULT_CATEGORIES_ENABLED', true);
define('DEFAULT_GMAP_ENABLED', true);
define('DEFAULT_GMAP_ZOOMING', true);
define('DEFAULT_GLOBAL_ZOOM_FACTOR', 3);
define('DEFAULT_INDIV_ZOOM_FACTOR', 14);
define('DEFAULT_GLOBAL_MAPTYPE', "ROADMAP");
define('DEFAULT_INDIV_MAPTYPE', "ROADMAP");
define('DEFAULT_SEO_PERMALINK', true);
define('DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT', "F, Y");
define('DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT', "Y");
define('DEFAULT_FILTER_FORM_FORMAT', "#_FILTER_CATS #_FILTER_LOCS");
define('EME_DEFAULT_CSV_SEPARATOR',';');
define('STATUS_PUBLIC', 1);
define('STATUS_PRIVATE', 2);
define('STATUS_DRAFT', 5);
define('MEMBER_STATUS_PENDING', 0);
define('MEMBER_STATUS_ACTIVE', 1);
define('MEMBER_STATUS_EXPIRED', 100);
$upload_info = wp_upload_dir();
define("IMAGE_UPLOAD_DIR", $upload_info['basedir']."/locations-pics");
define("IMAGE_UPLOAD_URL", $upload_info['baseurl']."/locations-pics");
define("CO_LIVE_URL","https://www.2checkout.com/checkout/purchase");
define("CO_SANDBOX_URL","https://sandbox.2checkout.com/checkout/purchase");
define("PAYPAL_LIVE_URL","https://www.paypal.com/cgi-bin/webscr");
define("PAYPAL_SANDBOX_URL","https://www.sandbox.paypal.com/cgi-bin/webscr");
define("WORLDPAY_SANDBOX_URL","https://secure-test.worldpay.com/wcc/purchase");
define("WORLDPAY_LIVE_URL","https://secure.worldpay.com/wcc/purchase");
define("GOOGLE_LIVE","production");
define("GOOGLE_SANDBOX","sandbox");
define("FDGG_SANDBOX_URL","https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing");
define("FDGG_LIVE_URL","https://connect.firstdataglobalgateway.com/IPGConnect/gateway/processing");
define("SAGEPAY_SANDBOX_URL","https://test.sagepay.com/gateway/service/vspform-register.vsp");
define("SAGEPAY_LIVE_URL","https://live.sagepay.com/gateway/service/vspform-register.vsp");

add_filter( 'plugin_row_meta', 'eme_plugin_row_meta', 10, 2 );
function eme_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'events-manager.php' ) !== false ) {
		$new_links = array(
				'donate' => '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=SMGDS4GLCYWNG&lc=BE&item_name=To%20support%20development%20of%20EME&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">Donate</a>',
                                'Support' => '<a href="http://www.e-dynamics.be/wordpress">Support</a>'
				);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'eme_add_action_links' );
function eme_add_action_links ( $links ) {
 $mylinks = array('<a href="admin.php?page=eme-options">Settings</a>');
return array_merge( $links, $mylinks );
}

// make sure the locale is set correct asap
add_filter('locale','eme_redefine_locale',10);  
function eme_load_textdomain() {
   $domain='events-made-easy';
   $thisDir = dirname( plugin_basename( __FILE__ ) );
   $locale = get_locale();
   // support custom translations first
   $locale = apply_filters('plugin_locale', get_locale(), $domain);
   load_textdomain($domain, WP_LANG_DIR.'/'.$thisDir.'/'.$domain.'-'.$locale.'.mo');
   // if the above succeeds, the following with not load the language file again
   load_plugin_textdomain($domain, false, $thisDir.'/langs'); 
}

// To enable activation through the activate function
register_activation_hook(__FILE__,'eme_install');
// when deactivation is needed
register_deactivation_hook(__FILE__,'eme_uninstall');
// when a new blog is added for network installation and the plugin is network activated
add_action( 'wpmu_new_blog', 'eme_new_blog', 10, 6);      
// to execute a db update after auto-update of EME
//add_action( 'plugins_loaded', 'eme_install' );

// filters for general events field (corresponding to those of  "the_title")
add_filter('eme_general', 'wptexturize');
add_filter('eme_general', 'convert_chars');
add_filter('eme_general', 'trim');
// filters for the notes field  (corresponding to those of  "the_content")
add_filter('eme_notes', 'wptexturize');
add_filter('eme_notes', 'convert_smilies');
add_filter('eme_notes', 'convert_chars');
if (get_option('eme_disable_wpautop'))
   add_filter('eme_notes', 'eme_nl2br');
else
   add_filter('eme_notes', 'wpautop');
add_filter('eme_notes', 'shortcode_unautop');
add_filter('eme_notes', 'prepend_attachment');
// RSS general filters (corresponding to those of  "the_content_rss")
add_filter('eme_general_rss', 'ent2ncr', 8);
// RSS excerpt filter (corresponding to those of  "the_excerpt_rss")
add_filter('eme_excerpt_rss', 'convert_chars', 8);
add_filter('eme_excerpt_rss', 'ent2ncr', 8);

// TEXT content filter
add_filter('eme_text', 'wp_strip_all_tags');

// we only want the google map javascript to be loaded if needed, so we set a global
// variable to 0 here and if we detect #_MAP, we set it to 1. In a footer filter, we then
// check if it is 1 and if so: include it
$eme_need_gmap_js=0;

// we only want the jquery for the calendar to load if/when needed
$eme_need_calendar_js=0;

// set some vars
$eme_timezone = get_option('timezone_string');
if (!$eme_timezone) {
	$offset = get_option('gmt_offset');
	$eme_timezone = timezone_name_from_abbr(null, $offset * 3600, false);
	if($eme_timezone === false) $eme_timezone = timezone_name_from_abbr(null, $offset * 3600, true);
}
$eme_wp_date_format = get_option('date_format');
$eme_wp_time_format = get_option('time_format');

// enable shortcodes in widgets, if wanted
if (!is_admin() && get_option('eme_shortcodes_in_widgets')) {
   add_filter('widget_text', 'do_shortcode', 11);
}

// the next is executed on activation/deactivation of EME, so as to set the rewriterules correctly
function eme_flushRules() {
   global $wp_rewrite;
   $wp_rewrite->flush_rules();
}

// Adding a new rule
function eme_insertMyRewriteRules($rules) {
   // using pagename as param to index.php causes rewrite troubles if the page is a subpage of another
   // luckily for us we have the page id, and this works ok
   $events_page_id = eme_get_events_page_id();
   $events_prefix=eme_permalink_convert(get_option('eme_permalink_events_prefix'));
   $locations_prefix=eme_permalink_convert(get_option('eme_permalink_locations_prefix'));
   $newrules = array();
   $newrules['(.*/)?'.$events_prefix.'(\d{4})-(\d{2})-(\d{2})/c(\d+).*'] = 'index.php?page_id='.$events_page_id.'&calendar_day=$matches[2]-$matches[3]-$matches[4]'.'&eme_event_cat=$matches[5]';
   $newrules['(.*/)?'.$events_prefix.'(\d{4})-(\d{2})-(\d{2}).*'] = 'index.php?page_id='.$events_page_id.'&calendar_day=$matches[2]-$matches[3]-$matches[4]';
   $newrules['(.*/)?'.$events_prefix.'(\d+).*'] = 'index.php?page_id='.$events_page_id.'&event_id=$matches[2]';
   $newrules['(.*/)?'.$events_prefix.'p/(.*)'] = 'index.php?page_id='.$events_page_id.'&eme_pmt_rndid=$matches[2]';
   $newrules['(.*/)?'.$events_prefix.'town/(.*)'] = 'index.php?page_id='.$events_page_id.'&eme_city=$matches[2]';
   $newrules['(.*/)?'.$events_prefix.'city/(.*)'] = 'index.php?page_id='.$events_page_id.'&eme_city=$matches[2]';
   $newrules['(.*/)?'.$events_prefix.'country/(.*)'] = 'index.php?page_id='.$events_page_id.'&eme_country=$matches[2]';
   $newrules['(.*/)?'.$events_prefix.'cat/(.*)'] = 'index.php?page_id='.$events_page_id.'&eme_event_cat=$matches[2]';
   $newrules['(.*/)?'.$locations_prefix.'(\d+).*'] = 'index.php?page_id='.$events_page_id.'&location_id=$matches[2]';
   return $newrules + $rules;
}
add_filter('rewrite_rules_array','eme_insertMyRewriteRules');

// Adding the id var so that WP recognizes it
function eme_insertMyRewriteQueryVars($vars) {
    array_push($vars, 'event_id');
    array_push($vars, 'location_id');
    array_push($vars, 'membership_id');
    array_push($vars, 'calendar_day');
    array_push($vars, 'eme_city');
    array_push($vars, 'eme_country');
    array_push($vars, 'eme_event_cat');
    // a bit cryptic for the booking id
    array_push($vars, 'eme_pmt_rndid');
    // for the payment result
    array_push($vars, 'eme_pmt_result');
    return $vars;
}
add_filter('query_vars','eme_insertMyRewriteQueryVars');

function eme_cron_schedules($schedules){
   if(!isset($schedules["5min"])){
      $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes','events-made-easy'));
   }
   if(!isset($schedules["15min"])){
      $schedules["15min"] = array(
            'interval' => 15*60,
            'display' => __('Once every 15 minutes','events-made-easy'));
   }
   if(!isset($schedules["30min"])){
      $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes','events-made-easy'));
   }
   if(!isset($schedules["4weeks"])){
      $schedules["4weeks"] = array(
            'interval' => 60*60*24*28,
            'display' => __('Once every 4 weeks','events-made-easy'));
   }
   return $schedules;
}
add_filter('cron_schedules','eme_cron_schedules');

// INCLUDES
// We let the includes happen at the end, so all init-code is done
// (like eg. the load_textdomain). Some includes do stuff based on _GET
// so they need the correct info before doing stuff
require_once("eme_options.php");
require_once("eme_functions.php");
require_once("eme_filters.php");
require_once("eme_events.php");
require_once("eme_calendar.php");
require_once("eme_widgets.php");
require_once("eme_rsvp.php");
require_once("eme_locations.php"); 
require_once("eme_people.php");
require_once("eme_recurrence.php");
require_once("eme_UI_helpers.php");
require_once("eme_categories.php");
require_once("eme_holidays.php");
require_once("eme_templates.php");
require_once("eme_attributes.php");
require_once("eme_ical.php");
require_once("eme_cleanup.php");
require_once("eme_cron.php");
require_once("eme_formfields.php");
require_once("eme_shortcodes.php");
require_once("eme_actions.php");
require_once("eme_payments.php");
require_once("eme_discounts.php");
require_once("eme_members.php");
require_once("ExpressiveDate.php");
require_once("eme_mailer.php") ;

function eme_install($networkwide) {
   global $wpdb;
   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($networkwide) {
         //$old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_install();
            restore_current_blog();
         }
         //switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_install();     
}

// the private function; for activation
function _eme_install() {
   eme_add_options();

   $db_version = get_option('eme_version');
   if ($db_version == EME_DB_VERSION) {
      return;
   }
   eme_update_options($db_version);

   // Create events page if necessary
   $events_page_id = eme_get_events_page_id();
   if ($events_page_id) {
      if (!get_page($events_page_id))
         eme_create_events_page(); 
   } else {
      eme_create_events_page(); 
   }

   eme_create_tables();

   // some cron we want
   if (! wp_next_scheduled ( 'eme_cron_member_daily_actions' )) {
      wp_schedule_event(time(), 'daily', 'eme_cron_member_daily_actions');
   }

   // SEO rewrite rules
   eme_flushRules();
   
   // now set the version correct
   update_option('eme_version', EME_DB_VERSION); 
}

function eme_uninstall($networkwide) {
   global $wpdb;

   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($networkwide) {
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_uninstall();
         }
         switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_uninstall();
}

function _eme_uninstall($force_drop=0) {
   $drop_data = get_option('eme_uninstall_drop_data');
   $drop_settings = get_option('eme_uninstall_drop_settings');

   if (wp_next_scheduled('eme_cron_cleanup_unpaid')) 
      wp_clear_scheduled_hook('eme_cron_cleanup_unpaid');
   if (wp_next_scheduled('eme_cron_reminder_unpaid')) 
      wp_clear_scheduled_hook('eme_cron_reminder_unpaid');
   if (wp_next_scheduled('eme_cron_send_queued')) 
      wp_clear_scheduled_hook('eme_cron_send_queued');
   if (wp_next_scheduled('eme_cron_member_actions')) 
      wp_clear_scheduled_hook('eme_cron_member_actions');

   if ($drop_data || $force_drop) {
      eme_drop_table(EVENTS_TBNAME);
      eme_drop_table(RECURRENCE_TBNAME);
      eme_drop_table(LOCATIONS_TBNAME);
      eme_drop_table(BOOKINGS_TBNAME);
      eme_drop_table(PEOPLE_TBNAME);
      eme_drop_table(GROUPS_TBNAME);
      eme_drop_table(USERGROUPS_TBNAME);
      eme_drop_table(CATEGORIES_TBNAME);
      eme_drop_table(HOLIDAYS_TBNAME);
      eme_drop_table(TEMPLATES_TBNAME);
      eme_drop_table(FORMFIELDS_TBNAME);
      eme_drop_table(FIELDTYPES_TBNAME);
      eme_drop_table(ANSWERS_TBNAME);
      eme_drop_table(PAYMENTS_TBNAME);
      eme_drop_table(DISCOUNTS_TBNAME);
      eme_drop_table(DISCOUNTGROUPS_TBNAME);
      eme_drop_table(MQUEUE_TBNAME);
      eme_drop_table(MAILINGS_TBNAME);
      eme_drop_table(MEMBERS_TBNAME);
      eme_drop_table(MEMBERSHIPS_TBNAME);
   }
   if ($drop_settings || $force_drop) {
      eme_delete_events_page();
      eme_options_delete();
      eme_metabox_options_delete();
   }
   if ($drop_data && !$drop_settings) {
      delete_option('eme_version');
   }

    // SEO rewrite rules
    eme_flushRules();
}

function eme_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
   global $wpdb;
 
   if (is_plugin_active_for_network(plugin_basename( __FILE__ ))) {
      $old_blog = $wpdb->blogid;
      switch_to_blog($blog_id);
      _eme_install();
      switch_to_blog($old_blog);
   }
}

function eme_create_tables() {
   global $wpdb;
   // Creates the events table if necessary
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   $charset="";
   $collate="";
   if ( $wpdb->has_cap('collation') ) {
      if ( ! empty($wpdb->charset) )
         $charset = "DEFAULT CHARACTER SET $wpdb->charset";
      if ( ! empty($wpdb->collate) )
         $collate = "COLLATE $wpdb->collate";
   }
   $db_version = get_option('eme_version');
   eme_create_events_table($charset,$collate,$db_version);
   eme_create_recurrence_table($charset,$collate,$db_version);
   eme_create_locations_table($charset,$collate,$db_version);
   eme_create_bookings_table($charset,$collate,$db_version);
   eme_create_people_table($charset,$collate,$db_version);
   eme_create_members_table($charset,$collate,$db_version);
   eme_create_categories_table($charset,$collate,$db_version);
   eme_create_holidays_table($charset,$collate,$db_version);
   eme_create_templates_table($charset,$collate,$db_version);
   eme_create_formfields_table($charset,$collate,$db_version);
   eme_create_answers_table($charset,$collate,$db_version);
   eme_create_payments_table($charset,$collate,$db_version);
   eme_create_discounts_table($charset,$collate,$db_version);
   eme_create_discountgroups_table($charset,$collate,$db_version);
   eme_create_mqueue_table($charset,$collate,$db_version);
}

function eme_drop_table($table) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $wpdb->query("DROP TABLE IF EXISTS $table");
}

function eme_convert_charset($table,$charset,$collate) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $sql = "ALTER TABLE $table CONVERT TO $charset $collate;";
   $wpdb->query($sql);
}

function eme_create_events_table($charset,$collate,$db_version) {
   global $wpdb, $eme_timezone;
   
   $table_name = $wpdb->prefix.EVENTS_TBNAME;
   
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      // Creating the events table
      $sql = "CREATE TABLE ".$table_name." (
         event_id mediumint(9) NOT NULL AUTO_INCREMENT,
         event_status mediumint(9) DEFAULT 1,
         event_author mediumint(9) DEFAULT 0,
         event_name text NOT NULL,
         event_slug text default NULL,
         event_url text default NULL,
         event_start_time time NOT NULL,
         event_end_time time NOT NULL,
         event_start_date date NOT NULL,
         event_end_date date NULL, 
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         event_notes longtext DEFAULT NULL,
         event_rsvp bool DEFAULT 0,
         use_paypal bool DEFAULT 0,
         use_2co bool DEFAULT 0,
         use_webmoney bool DEFAULT 0,
         use_fdgg bool DEFAULT 0,
         use_mollie bool DEFAULT 0,
         use_sagepay bool DEFAULT 0,
         price text DEFAULT NULL,
         currency text DEFAULT NULL,
         rsvp_number_days tinyint unsigned DEFAULT 0,
         rsvp_number_hours tinyint unsigned DEFAULT 0,
         event_seats text DEFAULT NULL,
         event_contactperson_id mediumint(9) DEFAULT 0,
         location_id mediumint(9) DEFAULT 0,
         recurrence_id mediumint(9) DEFAULT 0,
         event_category_ids text default NULL,
         event_attributes text NULL, 
         event_properties text NULL, 
         event_page_title_format text NULL, 
         event_single_event_format text NULL, 
         event_contactperson_email_body text NULL, 
         event_respondent_email_body text NULL, 
         event_registration_recorded_ok_html text NULL, 
         event_registration_pending_email_body text NULL, 
         event_registration_updated_email_body text NULL, 
         event_registration_cancelled_email_body text NULL, 
         event_registration_paid_email_body text NULL, 
         event_registration_denied_email_body text NULL, 
         event_registration_form_format text NULL, 
         event_cancel_form_format text NULL, 
         registration_requires_approval bool DEFAULT 0,
         registration_wp_users_only bool DEFAULT 0,
         event_image_url text NULL,
         event_image_id mediumint(9) DEFAULT 0 NOT NULL,
         event_external_ref text NULL, 
         UNIQUE KEY (event_id)
         ) $charset $collate;";
      
      maybe_create_table($table_name,$sql);
      // insert a few events in the new table
      // get the current timestamp into an array
      $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
      $eme_date_obj->addDays(7);
      $in_one_week = $eme_date_obj->getDate();
      $eme_date_obj->minusDays(7);
      $eme_date_obj->addWeeks(4);
      $in_four_weeks = $eme_date_obj->getDate();
      $eme_date_obj->minusWeeks(4);
      $eme_date_obj->addOneYear();
      $in_one_year = $eme_date_obj->getDate();
      
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
            VALUES ('Orality in James Joyce Conference', '$in_one_week', '16:00:00', '18:00:00', 1)");
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
            VALUES ('Traditional music session', '$in_four_weeks', '20:00:00', '22:00:00', 2)");
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
               VALUES ('6 Nations, Italy VS Ireland', '$in_one_year','22:00:00', '24:00:00', 3)");
   } else {
      // eventual maybe_add_column() for later versions
      maybe_add_column($table_name, 'event_status', "alter table $table_name add event_status mediumint(9) DEFAULT 1;"); 
      maybe_add_column($table_name, 'event_start_date', "alter table $table_name add event_start_date date NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_date', "alter table $table_name add event_end_date date NULL;");
      maybe_add_column($table_name, 'event_start_time', "alter table $table_name add event_start_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_time', "alter table $table_name add event_end_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_rsvp', "alter table $table_name add event_rsvp bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_paypal', "alter table $table_name add use_paypal bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_2co', "alter table $table_name add use_2co bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_webmoney', "alter table $table_name add use_webmoney bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_fdgg', "alter table $table_name add use_fdgg bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_mollie', "alter table $table_name add use_mollie bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_sagepay', "alter table $table_name add use_sagepay bool DEFAULT 0;");
      maybe_add_column($table_name, 'rsvp_number_days', "alter table $table_name add rsvp_number_days tinyint DEFAULT 0;");
      maybe_add_column($table_name, 'rsvp_number_hours', "alter table $table_name add rsvp_number_hours tinyint DEFAULT 0;");
      maybe_add_column($table_name, 'price', "alter table $table_name add price text DEFAULT NULL;");
      maybe_add_column($table_name, 'currency', "alter table $table_name add currency text DEFAULT NULL;");
      maybe_add_column($table_name, 'event_seats', "alter table $table_name add event_seats text DEFAULT NULL;");
      maybe_add_column($table_name, 'location_id', "alter table $table_name add location_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'recurrence_id', "alter table $table_name add recurrence_id mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'event_contactperson_id', "alter table $table_name add event_contactperson_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'event_attributes', "alter table $table_name add event_attributes text NULL;"); 
      maybe_add_column($table_name, 'event_properties', "alter table $table_name add event_properties text NULL;"); 
      maybe_add_column($table_name, 'event_url', "alter table $table_name add event_url text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_slug', "alter table $table_name add event_slug text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_category_ids', "alter table $table_name add event_category_ids text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_page_title_format', "alter table $table_name add event_page_title_format text NULL;"); 
      maybe_add_column($table_name, 'event_single_event_format', "alter table $table_name add event_single_event_format text NULL;"); 
      maybe_add_column($table_name, 'event_contactperson_email_body', "alter table $table_name add event_contactperson_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_respondent_email_body', "alter table $table_name add event_respondent_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_pending_email_body', "alter table $table_name add event_registration_pending_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_updated_email_body', "alter table $table_name add event_registration_updated_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_cancelled_email_body', "alter table $table_name add event_registration_cancelled_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_paid_email_body', "alter table $table_name add event_registration_paid_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_denied_email_body', "alter table $table_name add event_registration_denied_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_recorded_ok_html', "alter table $table_name add event_registration_recorded_ok_html text NULL;"); 
      maybe_add_column($table_name, 'registration_requires_approval', "alter table $table_name add registration_requires_approval bool DEFAULT 0;"); 
      $registration_wp_users_only=get_option('eme_rsvp_registered_users_only');
      maybe_add_column($table_name, 'registration_wp_users_only', "alter table $table_name add registration_wp_users_only bool DEFAULT $registration_wp_users_only;"); 
      maybe_add_column($table_name, 'event_author', "alter table $table_name add event_author mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'event_registration_form_format', "alter table $table_name add event_registration_form_format text NULL;"); 
      maybe_add_column($table_name, 'event_cancel_form_format', "alter table $table_name add event_cancel_form_format text NULL;"); 
      maybe_add_column($table_name, 'event_image_url', "alter table $table_name add event_image_url text NULL;"); 
      maybe_add_column($table_name, 'event_image_id', "alter table $table_name add event_image_id mediumint(9) DEFAULT 0 NOT NULL;"); 
      maybe_add_column($table_name, 'event_external_ref', "alter table $table_name add event_external_ref text NULL;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_name text;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_notes longtext;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name CHANGE event_category_id event_category_ids text default NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_author mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_contactperson_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_seats mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY location_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<5) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<11) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN event_author;");
         $wpdb->query("ALTER TABLE $table_name CHANGE event_creator_id event_author mediumint(9) DEFAULT 0;");
      }
      if ($db_version<29) {
         $wpdb->query("ALTER TABLE $table_name MODIFY price text default NULL;");
      }
      if ($db_version<33) {
         $post_table_name = $wpdb->prefix."posts";
         $wpdb->query("UPDATE $table_name SET event_image_id = (select ID from $post_table_name where post_type = 'attachment' AND guid = $table_name.event_image_url);");
      }
      if ($db_version<38) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_seats text default NULL;");
      }
      if ($db_version<68) {
         $wpdb->query("ALTER TABLE $table_name MODIFY rsvp_number_days tinyint DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY rsvp_number_hours tinyint DEFAULT 0;");
      }
      if ($db_version<70) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN use_google;");
      }
   }
}

function eme_create_recurrence_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.RECURRENCE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE ".$table_name." (
         recurrence_id mediumint(9) NOT NULL AUTO_INCREMENT,
         recurrence_start_date date NOT NULL,
         recurrence_end_date date NOT NULL,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         recurrence_interval tinyint NOT NULL, 
         recurrence_freq tinytext NOT NULL,
         recurrence_byday tinytext NOT NULL,
         recurrence_byweekno tinyint NOT NULL,
         recurrence_specific_days text NULL,
         holidays_id mediumint(9) DEFAULT 0,
         UNIQUE KEY (recurrence_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'recurrence_specific_days', "alter table $table_name add recurrence_specific_days text NULL;"); 
      maybe_add_column($table_name, 'holidays_id', "alter table $table_name add holidays_id mediumint(9) DEFAULT 0;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_byday tinytext NOT NULL ;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN recurrence_name, DROP COLUMN recurrence_start_time, DROP COLUMN recurrence_end_time, DROP COLUMN recurrence_notes, DROP COLUMN location_id, DROP COLUMN event_contactperson_id, DROP COLUMN event_category_id, DROP COLUMN event_page_title_format, DROP COLUMN event_single_event_format, DROP COLUMN event_contactperson_email_body, DROP COLUMN event_respondent_email_body, DROP COLUMN registration_requires_approval ");
      }
      if ($db_version<13) {
         $wpdb->query("UPDATE $table_name set creation_date=NOW() where creation_date='0000-00-00 00:00:00'");
         $wpdb->query("UPDATE $table_name set modif_date=NOW() where modif_date='0000-00-00 00:00:00'");
      }
   }
}

function eme_create_locations_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         location_id mediumint(9) NOT NULL AUTO_INCREMENT,
         location_name text NOT NULL,
         location_slug text default NULL,
         location_url text default NULL,
         location_address1 tinytext DEFAULT '', 
         location_address2 tinytext DEFAULT '', 
         location_city tinytext DEFAULT '', 
         location_state tinytext DEFAULT '', 
         location_zip tinytext DEFAULT '', 
         location_country tinytext DEFAULT '', 
         location_latitude float DEFAULT NULL,
         location_longitude float DEFAULT NULL,
         location_description text DEFAULT NULL,
         location_author mediumint(9) DEFAULT 0,
         location_category_ids text default NULL,
         location_creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_image_url text NULL,
         location_image_id mediumint(9) DEFAULT 0 NOT NULL,
         location_attributes text NULL, 
         location_properties text NULL, 
         location_external_ref text NULL, 
         UNIQUE KEY (location_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
      
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address1, location_city, location_latitude, location_longitude)
               VALUES ('Arts Millenium Building', 'Newcastle Road','Galway', 53.275, -9.06532)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address1, location_city, location_latitude, location_longitude)
               VALUES ('The Crane Bar', '2, Sea Road','Galway', 53.2683224, -9.0626223)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address1, location_city, location_latitude, location_longitude)
               VALUES ('Taaffes Bar', '19 Shop Street','Galway', 53.2725, -9.05321)");
   } else {
      maybe_add_column($table_name, 'location_author', "alter table $table_name add location_author mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'location_category_ids', "alter table $table_name add location_category_ids text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_creation_date', "alter table $table_name add location_creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_creation_date_gmt', "alter table $table_name add location_creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_url', "alter table $table_name add location_url text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_slug', "alter table $table_name add location_slug text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_image_url', "alter table $table_name add location_image_url text NULL;"); 
      maybe_add_column($table_name, 'location_image_id', "alter table $table_name add location_image_id mediumint(9) DEFAULT 0 NOT NULL;"); 
      maybe_add_column($table_name, 'location_attributes', "alter table $table_name add location_attributes text NULL;"); 
      maybe_add_column($table_name, 'location_properties', "alter table $table_name add location_properties text NULL;"); 
      maybe_add_column($table_name, 'location_external_ref', "alter table $table_name add location_external_ref text NULL;");    
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY location_name text NOT NULL ;");
      }
      if ($db_version<33) {
         $post_table_name = $wpdb->prefix."posts";
         $wpdb->query("UPDATE $table_name SET location_image_id = (select ID from $post_table_name where post_type = 'attachment' AND guid = $table_name.location_image_url);");
      }
      if ($db_version<110) {
         $wpdb->query("ALTER TABLE $table_name CHANGE location_address location_address1 tinytext DEFAULT '';");
         $wpdb->query("ALTER TABLE $table_name CHANGE location_town location_city tinytext DEFAULT '';");
         maybe_add_column($table_name, 'location_address2', "ALTER TABLE $table_name add location_address2 tinytext DEFAULT '';"); 
         maybe_add_column($table_name, 'location_state', "ALTER TABLE $table_name add location_state tinytext DEFAULT '';"); 
         maybe_add_column($table_name, 'location_zip', "ALTER TABLE $table_name add location_zip tinytext DEFAULT '';"); 
         maybe_add_column($table_name, 'location_country', "ALTER TABLE $table_name add location_country tinytext DEFAULT '';"); 
      }
      if ($db_version<130) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN modif_date;");
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN modif_date_gmt;");
      }
   }
}

function eme_create_bookings_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.BOOKINGS_TBNAME;

   // column discount: effective calculated discount value
   // columns discountid , dgroupid: pointer to discount/discout group applied
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         booking_id mediumint(9) NOT NULL AUTO_INCREMENT,
         event_id mediumint(9) NOT NULL,
         person_id mediumint(9) NOT NULL, 
         payment_id mediumint(9) DEFAULT NULL, 
         booking_seats mediumint(9) NOT NULL,
         booking_seats_mp varchar(250),
         booking_approved bool DEFAULT 0,
         waitinglist bool DEFAULT 0,
         booking_comment text DEFAULT NULL,
         booking_price text DEFAULT NULL,
         extra_charge tinytext DEFAULT '',
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         payment_date datetime DEFAULT '0000-00-00 00:00:00',
         booking_paid bool DEFAULT 0,
         reminder INT(11) DEFAULT 0,
         transfer_nbr_be97 varchar(20),
         wp_id bigint(20) unsigned DEFAULT NULL,
         lang varchar(10) DEFAULT '',
         ip varchar(250),
         discount tinytext DEFAULT '',
         discountid INT(11) DEFAULT 0,
         dgroupid INT(11) DEFAULT 0,
         UNIQUE KEY  (booking_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'booking_comment', "ALTER TABLE $table_name add booking_comment text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'booking_approved', "ALTER TABLE $table_name add booking_approved bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'waitinglist', "ALTER TABLE $table_name add waitinglist bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'payment_date', "alter table $table_name add payment_date datetime DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'transfer_nbr_be97', "alter table $table_name add transfer_nbr_be97 varchar(20);"); 
      maybe_add_column($table_name, 'booking_seats_mp', "alter table $table_name add booking_seats_mp varchar(250);"); 
      maybe_add_column($table_name, 'booking_price', "alter table $table_name add booking_price text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'wp_id', "ALTER TABLE $table_name add wp_id bigint(20) unsigned DEFAULT NULL;"); 
      maybe_add_column($table_name, 'lang', "ALTER TABLE $table_name add lang varchar(10) DEFAULT '';"); 
      maybe_add_column($table_name, 'ip', "ALTER TABLE $table_name add ip varchar(250) DEFAULT '';"); 
      maybe_add_column($table_name, 'extra_charge', "alter table $table_name add extra_charge tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'discount', "alter table $table_name add discount tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'discountid', "alter table $table_name add discountid INT(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'dgroupid', "alter table $table_name add dgroupid INT(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'reminder', "alter table $table_name add reminder INT(11) DEFAULT 0;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY person_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY booking_seats mediumint(9) NOT NULL;");
      }
      if ($db_version<47) {
         $people_table_name = $wpdb->prefix.PEOPLE_TBNAME;
         $wpdb->query("update $table_name a JOIN $people_table_name b on (a.person_id = b.person_id)  set a.wp_id=b.wp_id;");
      }
      if ($db_version<92) {
         maybe_add_column($table_name, 'payment_id', "ALTER TABLE $table_name add payment_id mediumint(9) DEFAULT NULL;"); 
         $payment_table_name = $wpdb->prefix.PAYMENTS_TBNAME;
         $sql = "SELECT id,booking_ids from $payment_table_name";

         $rows = $wpdb->get_results ( $sql, ARRAY_A );
         if ($rows!==false && !empty($rows)) {
            foreach ( $rows as $row ) {
               $booking_ids = explode(',', $row['booking_ids']);
               if (is_array($booking_ids) && count($booking_ids)>0) {
                  foreach ($booking_ids as $booking_id) {
                     $sql = $wpdb->prepare("UPDATE $table_name SET payment_id=%d WHERE booking_id=%d",$row['id'],$booking_id);
                     $wpdb->query($sql);
                  }
               }
            }
            $wpdb->query("ALTER TABLE $payment_table_name DROP COLUMN booking_ids;");
         }
      }
      if ($db_version<107) {
         $wpdb->query("ALTER TABLE $table_name CHANGE booking_payed booking_paid bool DEFAULT 0;");
      }
      if ($db_version<130) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN modif_date;");
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN modif_date_gmt;");
      }
   }
}

function eme_create_people_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.PEOPLE_TBNAME;
   $grouptable_name = $wpdb->prefix.GROUPS_TBNAME;
   $usergrouptable_name = $wpdb->prefix.USERGROUPS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         person_id mediumint(9) NOT NULL AUTO_INCREMENT,
         lastname tinytext NOT NULL, 
         firstname tinytext DEFAULT '', 
         email tinytext NOT NULL,
         phone tinytext DEFAULT NULL,
         wp_id bigint(20) unsigned DEFAULT NULL,
         address1 tinytext DEFAULT '', 
         address2 tinytext DEFAULT '', 
         city tinytext DEFAULT '', 
         state tinytext DEFAULT '', 
         zip tinytext DEFAULT '', 
         country tinytext DEFAULT '', 
         lang varchar(10) DEFAULT '',
         massmail bool DEFAULT 1,
         properties text DEFAULT NULL,
         UNIQUE KEY (person_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'wp_id', "ALTER TABLE $table_name add wp_id bigint(20) unsigned DEFAULT NULL;"); 
      maybe_add_column($table_name, 'firstname', "ALTER TABLE $table_name add firstname tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'address1', "ALTER TABLE $table_name add address1 tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'address2', "ALTER TABLE $table_name add address2 tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'city', "ALTER TABLE $table_name add city tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'state', "ALTER TABLE $table_name add state tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'zip', "ALTER TABLE $table_name add zip tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'country', "ALTER TABLE $table_name add country tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'lang', "ALTER TABLE $table_name add lang varchar(10) DEFAULT '';"); 
      maybe_add_column($table_name, 'massmail', "ALTER TABLE $table_name add massmail bool DEFAULT 1;"); 
      maybe_add_column($table_name, 'properties', "ALTER TABLE $table_name add properties text DEFAULT NULL;"); 
      if ($db_version<10) {
         $wpdb->query("ALTER TABLE $table_name MODIFY person_phone tinytext DEFAULT 0;");
      }
      if ($db_version<78) {
         $wpdb->query("ALTER TABLE $table_name CHANGE person_phone phone tinytext DEFAULT NULL;");
         $wpdb->query("ALTER TABLE $table_name CHANGE person_name lastname tinytext NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name CHANGE person_email email tinytext NOT NULL;");
      }
      if ($db_version<163) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN image_id");
      }
   }
   if($wpdb->get_var("SHOW TABLES LIKE '$grouptable_name'") != $grouptable_name) {
      $sql = "CREATE TABLE ".$grouptable_name." (
         group_id int(11) NOT NULL auto_increment,
         name varchar(50) DEFAULT NULL,
         description tinytext DEFAULT NULL,
         UNIQUE KEY  (group_id)
         ) $charset $collate;";
      maybe_create_table($grouptable_name,$sql);
   }
   if($wpdb->get_var("SHOW TABLES LIKE '$usergrouptable_name'") != $usergrouptable_name) {
      $sql = "CREATE TABLE ".$usergrouptable_name." (
         person_id int(11),
         group_id int(11)
         ) $charset $collate;";
      maybe_create_table($usergrouptable_name,$sql);
   }
 } 

function eme_create_categories_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.CATEGORIES_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         category_id int(11) NOT NULL auto_increment,
         category_name tinytext NOT NULL,
         description text DEFAULT NULL,
         category_slug text default NULL,
         UNIQUE KEY  (category_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'category_slug', "alter table $table_name add category_slug text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'description', "alter table $table_name add description text DEFAULT NULL;"); 
      if ($db_version<66) {
         $categories = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
         foreach ($categories as $this_category) {
             $where = array();
             $fields = array();
             $where['category_id'] = $this_category['category_id'];
             $fields['category_slug'] = untrailingslashit(eme_permalink_convert($this_category['category_name']));
             $wpdb->update($table_name, $fields, $where);
         }
      }
   }
}

function eme_create_holidays_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.HOLIDAYS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         name tinytext NOT NULL,
         list text NOT NULL,
         UNIQUE KEY  (id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   }
}

function eme_create_templates_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.TEMPLATES_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         name tinytext DEFAULT NULL,
         description tinytext DEFAULT NULL,
         format text NOT NULL,
         type tinytext DEFAULT '',
         properties text DEFAULT NULL,
         UNIQUE KEY  (id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'type', "ALTER TABLE $table_name add type tinytext DEFAULT NULL;"); 
      maybe_add_column($table_name, 'properties', "ALTER TABLE $table_name add properties text DEFAULT NULL;"); 
      if ($db_version<41) {
         $wpdb->query("ALTER TABLE $table_name MODIFY format text NOT NULL;");
      }
      if ($db_version<144) {
         $wpdb->query("ALTER TABLE $table_name MODIFY type tinytext DEFAULT '';");
         $wpdb->query("UPDATE $table_name SET type='' WHERE type IS NULL;");
      }
      if ($db_version<151) {
         maybe_add_column($table_name, 'name', "ALTER TABLE $table_name add name tinytext DEFAULT NULL;"); 
         $wpdb->query("UPDATE $table_name SET name = description;");
      }
   }
}

function eme_create_formfields_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.FORMFIELDS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         field_id int(11) NOT NULL auto_increment,
         field_type tinytext NOT NULL,
         field_name tinytext NOT NULL,
         field_values text NOT NULL,
         admin_values text,
         field_tags text,
         admin_tags text,
         field_attributes tinytext,
         field_purpose tinytext,
         field_condition tinytext,
         field_required bool DEFAULT 0,
         extra_charge bool DEFAULT 0,
         UNIQUE KEY  (field_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'field_tags', "ALTER TABLE $table_name add field_tags text;"); 
      maybe_add_column($table_name, 'admin_values', "ALTER TABLE $table_name add admin_values text;"); 
      maybe_add_column($table_name, 'admin_tags', "ALTER TABLE $table_name add admin_tags text;"); 
      maybe_add_column($table_name, 'field_attributes', "ALTER TABLE $table_name add field_attributes tinytext DEFAULT NULL;"); 
      maybe_add_column($table_name, 'extra_charge', "alter table $table_name add extra_charge bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'field_condition', "alter table $table_name add field_condition tinytext;"); 
      maybe_add_column($table_name, 'field_required', "alter table $table_name add field_required bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'field_purpose', "alter table $table_name add field_purpose tinytext;"); 
      if ($db_version<104) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN field_pattern");
      }
      if ($db_version<154) {
         $wpdb->query("ALTER TABLE $table_name CHANGE field_info field_values text NOT NULL;");
      }
      if ($db_version<54) {
         $wpdb->query("UPDATE ".$table_name." SET field_tags=field_values");
      }
      if ($db_version<166) {
         $wpdb->query("ALTER TABLE $table_name CHANGE field_type old_type mediumint(9);");
	 maybe_add_column($table_name, 'field_type', "alter table $table_name add field_type tinytext NOT NULL;"); 
         $wpdb->query("UPDATE ".$table_name." SET field_type='text' WHERE old_type=1;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='dropdown' WHERE old_type=2;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='textarea' WHERE old_type=3;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='radiobox' WHERE old_type=4;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='radiobox'_vertical WHERE old_type=5;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='checkbox' WHERE old_type=6;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='checkbox'_vertical WHERE old_type=7;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='date' WHERE old_type=8;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='date_js' WHERE old_type=9;");
         $wpdb->query("UPDATE ".$table_name." SET field_type='dropdown_multi' WHERE old_type=10;");
	 $table_name = $wpdb->prefix.FIELDTYPES_TBNAME;
	 eme_drop_table($table_name);
      }
   }
}

function eme_create_answers_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.ANSWERS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         answer_id int(11) NOT NULL auto_increment,
         booking_id mediumint(9) DEFAULT 0,
         person_id mediumint(9) DEFAULT 0,
         member_id mediumint(9) DEFAULT 0,
         field_id int(11) DEFAULT 0,
         answer text NOT NULL,
         grouping int(11) DEFAULT 0,
         occurence int(11) DEFAULT 0,
         UNIQUE KEY  (answer_id),
         KEY  (booking_id),
         KEY  (person_id),
         KEY  (member_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      if ($db_version==23) {
         $wpdb->query("ALTER TABLE ".$table_name." DROP PRIMARY KEY");
         $wpdb->query("ALTER TABLE ".$table_name." ADD KEY (booking_id)");
      }
      if ($db_version<112) {
         maybe_add_column($table_name, 'field_id', "ALTER TABLE $table_name add field_id INT(11) DEFAULT 0;"); 
         $formfield_table_name = $wpdb->prefix.FORMFIELDS_TBNAME;
         $res=$wpdb->query("UPDATE $table_name SET field_id = (select field_id from $formfield_table_name where field_name = $table_name.field_name LIMIT 1);");
         if ($res!==false)
            $wpdb->query("ALTER TABLE $table_name DROP COLUMN field_name");
      }
      if ($db_version<125) {
         maybe_add_column($table_name, 'grouping', "ALTER TABLE $table_name add grouping INT(11) DEFAULT 0;"); 
         maybe_add_column($table_name, 'occurence', "ALTER TABLE $table_name add occurence INT(11) DEFAULT 0;"); 
      }
      if ($db_version<138) {
	 $wpdb->query("ALTER TABLE $table_name MODIFY booking_id mediumint(9) DEFAULT 0;");
         maybe_add_column($table_name, 'person_id', "ALTER TABLE $table_name add person_id MEDIUMINT(9) DEFAULT 0;"); 
         $wpdb->query("ALTER TABLE ".$table_name." ADD KEY (person_id)");
      }
      if ($db_version<143) {
         maybe_add_column($table_name, 'member_id', "ALTER TABLE $table_name add member_id MEDIUMINT(9) DEFAULT 0;"); 
         $wpdb->query("ALTER TABLE ".$table_name." ADD KEY (member_id)");
      }
      if ($db_version<156) {
	 $wpdb->query("ALTER TABLE ".$table_name." ADD answer_id INT(11) PRIMARY KEY AUTO_INCREMENT;");
      }
      if ($db_version<161) {
	 $wpdb->query("ALTER TABLE $table_name CHANGE answer_id answer_id INT(11) PRIMARY KEY AUTO_INCREMENT;");
      }
   }
}

function eme_create_payments_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.PAYMENTS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         random_id tinytext NOT NULL,
         member_id INT(11) DEFAULT 0,
         UNIQUE KEY  (id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'random_id', "alter table $table_name add random_id tinytext NOT NULL;"); 
      maybe_add_column($table_name, 'member_id', "alter table $table_name add member_id INT(11) DEFAULT 0;"); 
      if ($db_version<80) {
         $payment_ids = $wpdb->get_col("SELECT id FROM $table_name");
         foreach ($payment_ids as $payment_id) {
            $random_id=eme_payment_random_id();
            $sql = $wpdb->prepare("UPDATE $table_name SET random_id = %s WHERE id = %d",$random_id,$payment_id);
            $wpdb->query($sql);
         }
      }
      if ($db_version<104) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN booking_ids");
      }
   }
}

function eme_create_discounts_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.DISCOUNTS_TBNAME;

   // coupon types: 1=fixed,2=percentage,3=code (filter),4=fixed_per_seat
   // column coupon: text to be entered by booker
   // column value: the applied discount (converted in php to floating point)
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         name varchar(50) DEFAULT NULL,
         description tinytext DEFAULT NULL,
         type tinyint UNSIGNED DEFAULT 0,
         coupon tinytext DEFAULT NULL,
         dgroup tinytext DEFAULT NULL,
         value tinytext DEFAULT NULL,
         maxcount tinyint UNSIGNED DEFAULT 0,
         count tinyint UNSIGNED DEFAULT 0,
         strcase bool DEFAULT 1,
         expire date DEFAULT NULL, 
         UNIQUE KEY  (id),
         UNIQUE KEY  (name)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'expire', "alter table $table_name add expire date DEFAULT NULL;"); 
      maybe_add_column($table_name, 'strcase', "alter table $table_name add strcase bool DEFAULT 1;"); 
   }
}

function eme_create_discountgroups_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.DISCOUNTGROUPS_TBNAME;

   // column maxdiscounts: max number of discounts in a group that can
   // be used, 0 for no max (this to avoid hackers from adding discount fields
   // to a form)
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         description tinytext DEFAULT NULL,
         name varchar(50) DEFAULT NULL,
         maxdiscounts tinyint UNSIGNED DEFAULT 0,
         UNIQUE KEY  (id),
         UNIQUE KEY  (name)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'description', "alter table $table_name add description tinytext DEFAULT NULL;"); 
   }
}

function eme_create_mqueue_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.MQUEUE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         mailing_id int(11) DEFAULT 0,
         person_id int(11) DEFAULT 0,
         member_id int(11) DEFAULT 0,
         status tinyint DEFAULT 0,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         sent_datetime datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         receiveremail tinytext DEFAULT NULL,
         receivername tinytext DEFAULT NULL,
         replytoemail tinytext DEFAULT NULL,
         replytoname tinytext DEFAULT NULL,
         subject tinytext DEFAULT NULL,
         body text DEFAULT NULL,
         UNIQUE KEY  (id),
         KEY  (status)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'mailing_id', "alter table $table_name add mailing_id int(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'person_id', "alter table $table_name add person_id int(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'member_id', "alter table $table_name add member_id int(11) DEFAULT 0;"); 
      if ($db_version<140) {
         $wpdb->query("ALTER TABLE $table_name DROP KEY is_sent");
         $wpdb->query("ALTER TABLE $table_name CHANGE is_sent state tinyint DEFAULT 0;");
	 $wpdb->query("ALTER TABLE $table_name ADD KEY (state)");
      }
      if ($db_version<153) {
         $wpdb->query("ALTER TABLE $table_name CHANGE state status tinyint DEFAULT 0;");
      }
   }

   $table_name = $wpdb->prefix.MAILINGS_TBNAME;
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         name varchar(50) DEFAULT NULL,
	 planned_on datetime DEFAULT '0000-00-00 00:00:00', 
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         mail_count int DEFAULT 0,
         subject tinytext DEFAULT NULL,
         body text DEFAULT NULL,
         replytoemail tinytext DEFAULT NULL,
         replytoname tinytext DEFAULT NULL,
         mail_text_html tinytext DEFAULT NULL,
         cancelled bool DEFAULT 0,
         UNIQUE KEY  (id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
	   maybe_add_column($table_name, 'replytoemail', "alter table $table_name add replytoemail tinytext DEFAULT NULL;"); 
	   maybe_add_column($table_name, 'mail_text_html', "alter table $table_name add mail_text_html tinytext DEFAULT NULL;"); 
	   maybe_add_column($table_name, 'replytoname', "alter table $table_name add replytoname tinytext DEFAULT NULL;"); 
	   maybe_add_column($table_name, 'subject', "alter table $table_name add subject tinytext DEFAULT NULL;"); 
	   maybe_add_column($table_name, 'body', "alter table $table_name add body text DEFAULT NULL;"); 
	   if ($db_version<158) {
		   $wpdb->query("ALTER TABLE $table_name CHANGE mail_count mail_count int DEFAULT 0;");
	   }
   }
 
}
function eme_create_members_table($charset,$collate,$db_version) {
   global $wpdb;
   $table_name = $wpdb->prefix.MEMBERS_TBNAME;

   // state contains the defined/calculated state
   // autostate indicates if the state needs to be calculated automatically
   //    or if it is set manually
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         member_id int(11) NOT NULL auto_increment,
         membership_id int(11) DEFAULT 0,
         person_id int(11) DEFAULT 0,
         status tinyint DEFAULT 0,
         status_automatic BOOL DEFAULT 1,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         start_date date NOT NULL DEFAULT '0000-00-00', 
         end_date date NOT NULL DEFAULT '0000-00-00', 
         reminder INT(11) DEFAULT 0,
         reminder_date datetime DEFAULT '0000-00-00 00:00:00',
         transfer_nbr_be97 varchar(20),
         payment_id mediumint(9) DEFAULT NULL, 
         payment_date datetime DEFAULT '0000-00-00 00:00:00',
         paid bool DEFAULT 0,
         extra_charge tinyint DEFAULT 0,
         discount tinytext DEFAULT '',
         discountid INT(11) DEFAULT 0,
         dgroupid INT(11) DEFAULT 0,
         UNIQUE KEY  (member_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'transfer_nbr_be97', "alter table $table_name add transfer_nbr_be97 varchar(20);"); 
      maybe_add_column($table_name, 'discount', "alter table $table_name add discount tinytext DEFAULT '';"); 
      maybe_add_column($table_name, 'discountid', "alter table $table_name add discountid INT(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'dgroupid', "alter table $table_name add dgroupid INT(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'reminder', "alter table $table_name add reminder INT(11) DEFAULT 0;"); 
      maybe_add_column($table_name, 'payment_id', "alter table $table_name add payment_id INT(9) DEFAULT NULL;"); 
      maybe_add_column($table_name, 'payment_date', "alter table $table_name add payment_date datetime DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'reminder_date', "alter table $table_name add reminder_date datetime DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'paid', "alter table $table_name add paid bool DEFAULT 0;"); 
      if ($db_version<153) {
         $wpdb->query("ALTER TABLE $table_name CHANGE state status tinyint DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name CHANGE state_automatic status_automatic BOOL DEFAULT 1;");
      }
   }

   // properties: templ.id's for form, and subject/body for payment, confirm, reminder, stop templates
   //             and info concerning payment gateways
   //             and info concerning reminder days before sending reminder
   // type: fixed/rolling
   // duration_count+duration_period form a logical date format: 1 days, 2 days, 3 weeks, etc ...
   // start date: only used for type fixed
   $table_name = $wpdb->prefix.MEMBERSHIPS_TBNAME;
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         membership_id int(11) NOT NULL auto_increment,
         name varchar(50) DEFAULT NULL,
         description tinytext DEFAULT NULL,
         type varchar(50) DEFAULT NULL,
         start_date date DEFAULT '0000-00-00', 
         duration_count tinyint DEFAULT 0,
         duration_period varchar(50) DEFAULT '',
         properties text DEFAULT NULL,
         UNIQUE KEY  (membership_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   }
}

function eme_create_events_page() {
   global $wpdb;
   $postarr = array(
      'post_title'     => wp_strip_all_tags(__('Events','events-made-easy')),
      'post_content'   => __("This page is used by Events Made Easy. Don't change it, don't use it in your menu's, don't delete it. Just make sure the EME setting called 'Events page' points to this page. EME uses this page to render any and all events, locations, bookings, maps, ... anything. If you do want to delete this page, create a new one EME can use and update the EME setting 'Events page' accordingly.",'events-made-easy'),
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'ping_status'    => 'closed'
   );
   if ($int_post_id = wp_insert_post($postarr)) {
      update_option('eme_events_page', $int_post_id);
   }
}

function eme_delete_events_page() {
   $events_page_id = eme_get_events_page_id();
   if ($events_page_id)
      wp_delete_post($events_page_id);
}

// Create the Manage Events and the Options submenus 
add_action('admin_menu','eme_create_events_submenu');
function eme_create_events_submenu () {
   # just in case: make sure the Settings page can be reached if something is not correct with the security settings
   if (get_option('eme_cap_settings') =='')
      $cap_settings=DEFAULT_CAP_SETTINGS;
   else
      $cap_settings=get_option('eme_cap_settings');

   $events_page_id = eme_get_events_page_id();
   if (!$events_page_id || !get_page($events_page_id))
      add_action('admin_notices', "eme_explain_events_page_missing");

   if(function_exists('add_submenu_page')) {
      add_menu_page(__('Events Made Easy', 'events-made-easy'),__('Events Made Easy', 'events-made-easy'),get_option('eme_cap_list_events'),'eme-manager','eme_events_page', EME_PLUGIN_URL.'images/calendar-16.png');
      // Add a submenu to the custom top-level menu: 
      // edit event also needs just "add" as capability, otherwise you will not be able to edit own created events
      $plugin_page = add_submenu_page('eme-manager', __('All events', 'events-made-easy'),__('Events', 'events-made-easy'),get_option('eme_cap_list_events'),'eme-manager','eme_events_page');
      $plugin_page = add_submenu_page('eme-manager', __('Locations', 'events-made-easy'), __('Locations', 'events-made-easy'), get_option('eme_cap_add_locations'), 'eme-locations', "eme_locations_page");
      if (get_option('eme_categories_enabled')) {
         $plugin_page = add_submenu_page('eme-manager', __('Categories','events-made-easy'),__('Categories','events-made-easy'), get_option('eme_cap_categories'), "eme-categories", 'eme_categories_page');
      }
      $plugin_page = add_submenu_page('eme-manager', __('Holidays','events-made-easy'),__('Holidays','events-made-easy'), get_option('eme_cap_holidays'), "eme-holidays", 'eme_holidays_page');
      $plugin_page = add_submenu_page('eme-manager', __('Form Fields','events-made-easy'),__('Form Fields','events-made-easy'), get_option('eme_cap_forms'), "eme-formfields", 'eme_formfields_page');
      $plugin_page = add_submenu_page('eme-manager', __('Templates','events-made-easy'),__('Templates','events-made-easy'), get_option('eme_cap_templates'), "eme-templates", 'eme_templates_page');
      if (get_option('eme_rsvp_enabled')) {
         $plugin_page = add_submenu_page('eme-manager', __('Discounts', 'events-made-easy'), __('Discounts', 'events-made-easy'), get_option('eme_cap_discounts'), 'eme-discounts', "eme_discounts_page");
         $plugin_page = add_submenu_page('eme-manager', __('Pending Approvals', 'events-made-easy'), __('Pending Approvals', 'events-made-easy'), get_option('eme_cap_approve'), 'eme-registration-approval', "eme_registration_approval_page");
         $plugin_page = add_submenu_page('eme-manager', __('Change Registration', 'events-made-easy'), __('Change Registration', 'events-made-easy'), get_option('eme_cap_registrations'), 'eme-registration-seats', "eme_registration_seats_page");
      }
      $plugin_page = add_submenu_page('eme-manager', __('People', 'events-made-easy'), __('People', 'events-made-easy'), get_option('eme_cap_people'), 'eme-people', "eme_people_page");
      $plugin_page = add_submenu_page('eme-manager', __('Groups', 'events-made-easy'), __('Groups', 'events-made-easy'), get_option('eme_cap_people'), 'eme-groups', "eme_groups_page");
      $plugin_page = add_submenu_page('eme-manager', __('Members', 'events-made-easy'), __('Members', 'events-made-easy'), get_option('eme_cap_members'), 'eme-members', "eme_members_page");
      $plugin_page = add_submenu_page('eme-manager', __('Memberships', 'events-made-easy'), __('Memberships', 'events-made-easy'), get_option('eme_cap_members'), 'eme-memberships', "eme_memberships_page");
      if (get_option('eme_rsvp_mail_notify_is_active')) {
         $plugin_page = add_submenu_page('eme-manager', __('Send Mails', 'events-made-easy'), __('Send Mails', 'events-made-easy'), get_option('eme_cap_send_mails'), 'eme-send-mails', "eme_send_mails_page");
      }
      $plugin_page = add_submenu_page('eme-manager', __('CRON', 'events-made-easy'), __('CRON', 'events-made-easy'), $cap_settings, 'eme-cron', "eme_cron_page");
      $plugin_page = add_submenu_page('eme-manager', __('Cleanup actions', 'events-made-easy'), __('Cleanup actions', 'events-made-easy'), get_option('eme_cap_cleanup'), 'eme-cleanup', "eme_cleanup_page");

      $plugin_page = add_submenu_page('eme-manager', __('Events Made Easy Settings','events-made-easy'),__('Settings','events-made-easy'), $cap_settings, "eme-options", 'eme_options_page');
      //add_action( 'admin_head-'. $plugin_page, 'eme_admin_options_script' );
      // do some option checking after the options have been updated
      // add_action( 'load-'. $plugin_page, 'eme_admin_options_save');
   }
}

function eme_replace_notes_placeholders($format, $event="", $target="html") {
   if ($event && preg_match_all('/#(ESC)?_(DETAILS|NOTES|EXCERPT|EVENTDETAILS|NOEXCERPT)/', $format, $placeholders)) {
      foreach($placeholders[0] as $result) {
         $need_escape = 0;
         $orig_result = $result;
         $found = 1;
         if (strstr($result,'#ESC')) {
            $result = str_replace("#ESC","#",$result);
            $need_escape=1;
         }
         $field = "event_".ltrim(strtolower($result), "#_");
         // to catch every alternative (we just need to know if it is an excerpt or not)
         $show_excerpt=0;
         $show_rest=0;
         if ($field == "event_excerpt")
            $show_excerpt=1;
         if ($field == "event_noexcerpt")
            $show_rest=1;

         $replacement = "";
         if (isset($event['event_notes'])) {
            // first translate, since for "noexcerpt" the language indication is not there (it is only at the beginning of the notes, not after the separator)
            $event_notes = eme_translate($event['event_notes']);

            // make sure no windows line endings are in
            $event_notes = preg_replace('/\r\n|\n\r/',"\n",$event_notes);
            if ($show_excerpt) {
               // If excerpt, use the part before the more delimiter, removing a possible line ending
               $matches=preg_split('/\n?<\!--more-->/',$event_notes);
               $replacement = $matches[0];
            } elseif ($show_rest) {
               // If the rest is wanted, use the part after the more delimiter, removing a possible line ending
               $matches=preg_split('/<\!--more-->\n?/',$event_notes);
               if (isset($matches[1]))
		  $replacement = $matches[1];
            } else {
               // remove the more-delimiter, but if it was on a line by itself, replace by a linefeed
               $replacement = preg_replace('/\n<\!--more-->\n/', "\n" ,$event_notes );
               $replacement = preg_replace('/<\!--more-->/', '' ,$replacement );
            }
         }
         if ($target == "html") {
            $replacement = apply_filters('eme_notes', $replacement);
         } else {
            if ($target == "rss") {
               if ($show_excerpt)
                  $replacement = apply_filters('eme_excerpt_rss', $replacement);
               else
                  $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }
         if ($found) {
            if ($need_escape)
               $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
            $format = str_replace($orig_result, $replacement ,$format );
         }
      }
   }
   return $format;
}

function eme_replace_placeholders($format, $event, $target="html", $do_shortcode=1, $lang='') {
   global $eme_need_gmap_js, $eme_timezone;

   // an initial filter for the format, in case people want to change anything before the placeholders get replaced
   if (has_filter('eme_events_format_prefilter')) $format=apply_filters('eme_events_format_prefilter',$format, $event);

   // some variables we'll use further down more than once
   $current_userid=get_current_user_id();
   $eme_enable_notes_placeholders = get_option('eme_enable_notes_placeholders'); 

   // first replace the notes sections, since these can contain other placeholders
   if ($eme_enable_notes_placeholders)
      $format = eme_replace_notes_placeholders ( $format, $event, $target );

   // then we do the custom attributes, since these can contain other placeholders
   preg_match_all("/#(ESC|URL)?_ATT\{.+?\}(\{.+?\})?/", $format, $results);
   foreach($results[0] as $resultKey => $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      //Strip string of placeholder and just leave the reference
      $attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
      if (isset($event['event_attributes'][$attRef])) {
         $replacement = $event['event_attributes'][$attRef];
      }
      if( trim($replacement) == ''
         && isset($results[2][$resultKey])
         && $results[2][$resultKey] != '' ) {
         //Check to see if we have a second set of braces;
         $replacement = substr( $results[2][$resultKey], 1, strlen(trim($results[2][$resultKey]))-2 );
      }

      if ($need_escape)
         $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);

   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');

   $eme_date_obj_now=new ExpressiveDate(null,$eme_timezone);
   foreach($placeholders[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      $found = 1;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      // matches all fields placeholder
      if ($event && preg_match('/#_EDITEVENTLINK/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = "<a href=' ".wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id']),'eme_events','eme_admin_nonce')."'>".__('Edit', 'events-made-easy')."</a>";
         }

      } elseif ($event && preg_match('/#_EDITEVENTURL/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = wp_nonce_url(admin_url("admin.php?page=eme-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id']),'eme_events','eme_admin_nonce');
         }

      } elseif ($event && preg_match('/#_EVENTPRINTBOOKINGSLINK/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = "<a href=' ".admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=".$event['event_id'])."'>".__('Printable view of bookings','events-made-easy')."</a>";
         }

      } elseif ($event && preg_match('/#_EVENTPRINTBOOKINGSURL/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=".$event['event_id']);
         }

      } elseif ($event && preg_match('/#_EVENTCSVBOOKINGSLINK/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = "<a href=' ".admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id'])."'>".__('Printable view of bookings','events-made-easy')."</a>";
         }

      } elseif ($event && preg_match('/#_EVENTCSVBOOKINGSURL/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = admin_url("admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id']);
         }

      } elseif ($event && preg_match('/#_STARTDATE/', $result)) { 
         $replacement = eme_localized_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone);

      } elseif ($event && preg_match('/#_STARTTIME/', $result)) { 
         $replacement = eme_localized_time($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone);

      } elseif ($event && preg_match('/#_ENDDATE/', $result)) { 
         $replacement = eme_localized_date($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone);

      } elseif ($event && preg_match('/#_ENDTIME/', $result)) { 
         $replacement = eme_localized_time($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone);

      } elseif ($event && preg_match('/#_24HSTARTTIME/', $result)) { 
         $replacement = substr($event['event_start_time'], 0,5);

      } elseif ($event && preg_match('/#_24HENDTIME$/', $result)) {
         $replacement = substr($event['event_end_time'], 0,5);

      } elseif ($event && preg_match('/#_PAST_FUTURE_CLASS/', $result)) { 
         $eme_start_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
         $eme_end_obj = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
         if ($eme_start_obj->greaterThan($eme_date_obj_now)) {
            $replacement="eme-future-event";
         } elseif ($eme_start_obj->lessOrEqualTo($eme_date_obj_now) && $eme_end_obj->greaterOrEqualTo($eme_date_obj_now)) {
            $replacement="eme-ongoing-event";
         } else {
            $replacement="eme-past-event";
         }

      } elseif ($event && preg_match('/#_12HSTARTTIME$/', $result)) {
         $replacement = $eme_date_obj_now->copy()->setTimestampFromString($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone)->format('h:i A');

      } elseif ($event && preg_match('/#_12HENDTIME$/', $result)) {
         $replacement = $eme_date_obj_now->copy()->setTimestampFromString($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone)->format('h:i A');

      } elseif ($event && preg_match('/#_12HSTARTTIME_NOLEADINGZERO/', $result)) {
         $replacement = $eme_date_obj_now->copy()->setTimestampFromString($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone)->format('g:i A');
         if (get_option('eme_time_remove_leading_zeros')) {
            $replacement = str_replace(":00","",$replacement);
            $replacement = str_replace(":0",":",$replacement);
         }

      } elseif ($event && preg_match('/#_12HENDTIME_NOLEADINGZERO/', $result)) {
         $replacement = $eme_date_obj_now->copy()->setTimestampFromString($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone)->format('g:i A');
         if (get_option('eme_time_remove_leading_zeros')) {
            $replacement = str_replace(":00","",$replacement);
            $replacement = str_replace(":0",":",$replacement);
         }

      } elseif ($event && preg_match('/#_EVENTS_FILTERFORM/', $result)) {
         if ($target == "rss" || $target == "text" || eme_is_single_event_page()) {
            $replacement = "";
         } else {
            $replacement = eme_filter_form();
         }

      } elseif ($event && preg_match('/#_ADDBOOKINGFORM$/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } else {
            $replacement = eme_add_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_ADDBOOKINGFORM_IF_NOT_REGISTERED/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } else {
            $only_if_not_registered=1;
            $replacement = eme_add_booking_form($event['event_id'],$only_if_not_registered);
         }

      } elseif ($event && preg_match('/#_REMOVEBOOKINGFORM$|#_DELBOOKINGFORM/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } else {
            // when the booking just happened and the user needs to pay, we don't show the remove booking form
            if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'pay_bookings' && isset($_POST['eme_message']) && isset($_POST['eme_payment_id']))
               $replacement = "";
            else
               $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_REMOVEBOOKINGFORM_IF_REGISTERED|#_DELBOOKINGFORM_IF_REGISTERED/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } elseif (is_user_logged_in() ) {
            // when the booking just happened and the user needs to pay, we don't show the remove booking form
            // also show the form if a delete-message needs to be shown (that will actuallyonly shown the message anyway)
            if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'pay_bookings' && isset($_POST['eme_message']) && isset($_POST['eme_payment_id']))
               $replacement = "";
            elseif (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'delmessage')
               $replacement = eme_delete_booking_form($event['event_id']);
            elseif (eme_get_booking_ids_by_wp_event_id($current_userid,$event['event_id']))
               $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_WAITING_LIST_ACTIVATED$/', $result)) {
	 $waitinglist_seats = intval($event['event_properties']['waitinglist_seats']);
         $avail_seats = eme_get_available_seats($event['event_id']);
         if ($waitinglist_seats>0 && $avail_seats<=$waitinglist_seats && !eme_is_multi($event['event_seats'])) {
              $replacement=1;
         }
      
      } elseif ($event && preg_match('/#_WAITING_LIST_CLOSED$/', $result)) {
	 $waitinglist_seats = intval($event['event_properties']['waitinglist_seats']);
         $avail_seats = eme_get_available_seats($event['event_id']);
         if ($waitinglist_seats>0 && $avail_seats<=0 && !eme_is_multi($event['event_seats'])) {
              $replacement=1;
         }
      
      } elseif ($event && preg_match('/#_WAITINGLISTSEATS$/', $result)) {
	 $replacement = intval($event['event_properties']['waitinglist_seats']);

      } elseif ($event && preg_match('/#_AVAILABLEWAITINGLISTSEATS$/', $result)) {
	 $replacement = intval($event['event_properties']['waitinglist_seats']);
         $avail_seats = eme_get_available_seats($event['event_id']);
         if ($waitinglist_seats>0 && $avail_seats<=$waitinglist_seats && !eme_is_multi($event['event_seats'])) {
              $replacement=$avail_seats;
         }

      } elseif ($event && preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)$/', $result)) {
         $avail_seats = eme_get_available_seats($event['event_id']);
	 $waitinglist_seats = $event['event_properties']['waitinglist_seats'];
         $avail_seats -= $waitinglist_seats;
	 if ($avail_seats<0) $avail_seats=0;
	 $replacement = $avail_seats;

      } elseif (preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_available_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(TOTALSPACES|TOTALSEATS)$/', $result)) {
         $total_seats = $event['event_seats'];
	 $waitinglist_seats = $event['event_properties']['waitinglist_seats'];
         $total_seats -= $waitinglist_seats;
	 $replacement = $total_seats;

      } elseif (preg_match('/#_(TOTALSPACES|TOTALSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         $replacement = 0;
         if (eme_is_multi($event['event_seats'])) {
            $seats = eme_convert_multi2array($event['event_seats']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         $replacement = eme_get_booked_seats($event['event_id']);

      } elseif (preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         $replacement = 0;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_booked_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(PENDINGSPACES|PENDINGSEATS)$/', $result)) {
         $replacement = eme_get_pending_seats($event['event_id']);

      } elseif ($event && preg_match('/#_(PENDINGSPACES|PENDINGSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
	 $replacement = 0;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_pending_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(APPROVEDSPACES|APPROVEDSEATS)$/', $result)) {
         $replacement = eme_get_approved_seats($event['event_id']);

      } elseif ($event && preg_match('/#_(APPROVEDSPACES|APPROVEDSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         $replacement = 0;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_approved_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         if (is_user_logged_in()) {
            $replacement = eme_get_booked_seats_by_wp_event_id($current_userid,$event['event_id']);
         }

      } elseif ($event && preg_match('/#_LINKEDNAME/', $result)) {
         $event_link = eme_event_url($event,$lang);
         if ($target == "html") {
		 $replacement="<a href='$event_link' title='".eme_trans_esc_html($event['event_name'],$lang)."'>".eme_trans_esc_html($event['event_name'],$lang)."</a>";
         } else {
            $replacement=eme_translate($event['event_name'],$lang);
         }
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_EXTERNALURL/', $result)) {
         if ($event['event_url'] != '')
            $replacement=$event['event_url'];

      } elseif ($event && preg_match('/#_ICALLINK/', $result)) {
         $url = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);
         $replacement = "<a href='$url'>ICAL</a>";
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_ICALURL/', $result)) {
         $replacement = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);

      } elseif (preg_match('/#_EVENTIMAGETITLE$/', $result)) {
         if (!empty($event['event_image_id'])) {
              $info = eme_get_wp_image($event['event_image_id']);
              $replacement = $info['title'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_EVENTIMAGEALT$/', $result)) {
         if (!empty($event['event_image_id'])) {
              $info = eme_get_wp_image($event['event_image_id']);
              $replacement = $info['alt'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_EVENTIMAGECAPTION$/', $result)) {
         if (!empty($event['event_image_id'])) {
              $info = eme_get_wp_image($event['event_image_id']);
              $replacement = $info['caption'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_EVENTIMAGEDESCRIPTION$/', $result)) {
         if (!empty($event['event_image_id'])) {
              $info = eme_get_wp_image($event['event_image_id']);
              $replacement = $info['description'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
	 
      } elseif ($event && preg_match('/#_EVENTIMAGE$/', $result)) {
         if (!empty($event['event_image_id'])) {
            $replacement = wp_get_attachment_image($event['event_image_id'], 'full', 0, array('class'=>'eme_event_image') );
	 } elseif(!empty($event['event_image_url'])) {
            $url = esc_url($event['event_image_url']);
            $replacement = "<img src='$url' alt='".eme_trans_esc_html($event['event_name'],$lang)."'/>";
         }
         if(!empty($replacement)) {
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGEURL$/', $result)) {
         if (!empty($event['event_image_id']))
		 $replacement = wp_get_attachment_image_url($event['event_image_id'], 'full' );
         elseif(!empty($event['event_image_url']))
		 $replacement = esc_url($event['event_image_url']);

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMB$/', $result)) {
         if (!empty($event['event_image_id'])) {
            $replacement = wp_get_attachment_image($event['event_image_id'], get_option('eme_thumbnail_size'), 0, array('class'=>'eme_event_image') );
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMBURL$/', $result)) {
         if (!empty($event['event_image_id'])) {
		 $replacement = wp_get_attachment_image_url($event['event_image_id'], get_option('eme_thumbnail_size') );
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMB\{(.+)\}/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
            $replacement = wp_get_attachment_image($event['event_image_id'], $matches[1], 0, array('class'=>'eme_event_image'));
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMBURL\{(.+)\}/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
		 $replacement = wp_get_attachment_image_url($event['event_image_id'], $matches[1]);
         }

      } elseif (preg_match('/#_EVENTFIELD\{(.+)\}/', $result, $matches)) {
         $tmp_attkey=$matches[1];
         if (isset($event[$tmp_attkey]) && !is_array($event[$tmp_attkey])) {
            $replacement = $event[$tmp_attkey];
            if ($target == "html") {
               $replacement = eme_trans_esc_html($replacement,$lang);
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = eme_translate($replacement,$lang);
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = eme_translate($replacement,$lang);
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTATT\{(.+)\}\{(.+)\}/', $result, $matches)) {
         $tmp_event_id=intval($matches[1]);
         $tmp_event_attkey=$matches[2];
         $tmp_event = eme_get_event($tmp_event_id);
         if (isset($tmp_event['event_attributes'][$tmp_event_attkey])) {
            $replacement = $tmp_event['event_attributes'][$tmp_event_attkey];
            if ($target == "html") {
               $replacement = eme_trans_esc_html($replacement,$lang);
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = eme_translate($replacement,$lang);
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = eme_translate($replacement,$lang);
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTPAGEURL\{(.+)\}/', $result, $matches)) {
         $events_page_link = eme_get_events_page();
         $replacement = add_query_arg(array('event_id'=>intval($matches[1])),$events_page_link);
         if (!empty($lang))
            $replacement = add_query_arg(array('lang'=>$lang),$replacement);

      } elseif ($event && preg_match('/#_EVENTPAGEURL$/', $result)) {
         $replacement = eme_event_url($event,$lang);

      } elseif ($event && preg_match('/#_(NAME|EVENTNAME)$/', $result)) {
         $field = "event_name";
         if (isset($event[$field]))  $replacement = $event[$field];
         if ($target == "html") {
            $replacement = eme_trans_esc_html($replacement,$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate($replacement,$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate($replacement,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_EVENTID/', $result)) {
         $field = "event_id";
         $replacement = intval($event[$field]);

      } elseif ($event && preg_match('/#_SINGLE_EVENTPAGE_EVENTID/', $result)) {
         // returns the event id of the single event page currently shown
         if (eme_is_single_event_page())
               $replacement = intval(get_query_var('event_id'));

      } elseif ($event && preg_match('/#_DAYS_TILL_START/', $result)) {
         $eme_date_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
         $replacement = $eme_date_obj_now->getDifferenceInDays($eme_date_obj);

      } elseif ($event && preg_match('/#_DAYS_TILL_END/', $result)) {
         $eme_date_obj = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
         $replacement = $eme_date_obj_now->getDifferenceInDays($eme_date_obj);

      } elseif ($event && preg_match('/#_HOURS_TILL_START/', $result)) {
         $eme_date_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
         $replacement = round($eme_date_obj_now->getDifferenceInHours($eme_date_obj));

      } elseif ($event && preg_match('/#_HOURS_TILL_END/', $result)) {
         $eme_date_obj = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
         $replacement = round($eme_date_obj_now->getDifferenceInHours($eme_date_obj));

      } elseif ($event && preg_match('/#_EVENTPRICE$|#_PRICE$/', $result)) {
         $field = "price";
         if ($event[$field]) {
		 if ($need_escape)
			 $replacement=$event[$field];
		 else
			 $replacement = eme_localized_price($event[$field],$event['currency'],$target);
	 }
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(EVENT)?PRICE\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2]-1);
         if ($event["price"] && eme_is_multi($event["price"])) {
            $prices = eme_convert_multi2array($event["price"]);
            if (is_array($prices) && array_key_exists($field_id,$prices)) {
	       if ($need_escape)
		       $replacement=$prices[$field_id];
	       else
		       $replacement = eme_localized_price($prices[$field_id],$event['currency'],$target);
               if ($target == "html") {
                  $replacement = apply_filters('eme_general', $replacement);
               } elseif ($target == "rss")  {
                  $replacement = apply_filters('eme_general_rss', $replacement);
               } else {
                  $replacement = apply_filters('eme_text', $replacement);
               }
            }
         }

      } elseif ($event && preg_match('/#_CURRENCY$/', $result)) {
         $field = "currency";
         // currency is only important if the price is not empty as well
         if ($event['price'])
            $replacement = $event[$field];
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_CURRENCYSYMBOL$/', $result)) {
         $field = "currency";
         // currency is only important if the price is not empty as well
         if ($event['price'])
		 $replacement = eme_localized_currencysymbol($event[$field]);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_ATTENDEES/', $result)) {
         $replacement=eme_get_attendees_list_for($event);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_BOOKINGS/', $result)) {
         $replacement=eme_get_bookings_list_for_event($event);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(CONTACTNAME|CONTACTPERSON)/', $result)) {
         $contact = eme_get_event_contact($event);
         if ($contact)
            $replacement = $contact->display_name;
         $replacement = eme_trans_esc_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(CONTACTEMAIL|PLAIN_CONTACTEMAIL)/', $result)) {
         $contact = eme_get_event_contact($event);
         if ($contact) {
            $replacement = $contact->user_email;
            if ($target == "html") {
               // ascii encode for primitive harvesting protection ...
               $replacement = eme_ascii_encode($replacement);
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_CONTACTPHONE/', $result)) {
         $contact = eme_get_event_contact($event);
         if ($contact) {
            $phone = eme_get_user_phone($contact->ID);
            // ascii encode for primitive harvesting protection ...
            $replacement=eme_ascii_encode($phone);
         }
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_AUTHORNAME/', $result)) {
         $author = eme_get_author($event);
         if ($author)
            $replacement = $author->display_name;
         $replacement = eme_trans_esc_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_AUTHOREMAIL/', $result)) {
         $author = eme_get_author($event);
         if ($author) {
            $replacement = $author->user_email;
            if ($target == "html") {
               // ascii encode for primitive harvesting protection ...
               $replacement = eme_ascii_encode($replacement);
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_AUTHORPHONE/', $result)) {
         $author = eme_get_author($event);
         if ($author) {
            $phone = eme_get_user_phone($author->ID);
            // ascii encode for primitive harvesting protection ...
            $replacement=eme_ascii_encode($phone);
         }
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_EVENTCREATIONDATE/', $result)) {
         $replacement = eme_localized_date($event['creation_date']." ".$eme_timezone);
      } elseif (preg_match('/#_EVENTMODIFDATE/', $result)) {
         $replacement = eme_localized_date($event['modif_date']." ".$eme_timezone);
      } elseif (preg_match('/#_EVENTCREATIONTIME/', $result)) {
         $replacement = eme_localized_time($event['creation_date']." ".$eme_timezone);
      } elseif (preg_match('/#_EVENTMODIFTIME/', $result)) {
         $replacement = eme_localized_time($event['modif_date']." ".$eme_timezone);

      } elseif ($event && preg_match('/#[A-Za-z]$/', $result)) {
         // matches all PHP date placeholders for startdate-time
         $replacement=eme_localized_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone,ltrim($result,"#"));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif ($event && preg_match('/#@[A-Za-z]$/', $result)) {
         // matches all PHP time placeholders for enddate-time
         $replacement=eme_localized_date($event['event_end_date']." ".$event['event_end_time']." ".$eme_timezone,ltrim($result,"#@"));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#@i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif ($event && preg_match('/#_EVENTCATEGORYIDS$/', $result) && get_option('eme_categories_enabled')) {
         $categories = $event['event_category_ids'];
         if ($target == "html") {
            $replacement = eme_trans_esc_html($categories,$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_trans_esc_html($categories,$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_trans_esc_html($categories,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(EVENT)?CATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_category_names($event['event_id']);
         if ($target == "html") {
            $replacement = eme_trans_esc_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(EVENT)?CATEGORIES_CSS$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_category_names($event['event_id']);
         if ($target == "html") {
            $replacement = eme_trans_esc_html(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(EVENT)?CATEGORYDESCRIPTIONS$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_category_descriptions($event['event_id']);
         if ($target == "html") {
            $replacement = eme_trans_esc_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_LINKED(EVENT)?CATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_categories($event['event_id']);
         $cat_links = array();
         foreach ($categories as $category) {
            $cat_link=eme_category_url($category);
            $cat_name=$category['category_name'];
            if ($target == "html")
               array_push($cat_links,"<a href='$cat_link' title='".eme_trans_esc_html($cat_name,$lang)."'>".eme_trans_esc_html($cat_name,$lang)."</a>");
            else
               array_push($cat_links,eme_translate($cat_name,$lang));
         }
         $replacement = join(", ",$cat_links);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/^#_(EVENT)?CATEGORIES\{(.*?)\}\{(.*?)\}/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_category_names($event['event_id'],$extra_conditions);
         if ($target == "html") {
            $replacement = eme_trans_esc_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/^#_(EVENT)?CATEGORIES_CSS\{(.*?)\}\{(.*?)\}/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_category_names($event['event_id'],$extra_conditions);
         if ($target == "html") {
            $replacement = eme_trans_esc_html(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(" ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_LINKED(EVENT)?CATEGORIES\{(.*?)\}\{(.*?)\}/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_categories($event['event_id'],$extra_conditions);
         $cat_links = array();
         foreach ($categories as $category) {
            $cat_link=eme_category_url($category);
            $cat_name=$category['category_name'];
            if ($target == "html")
               array_push($cat_links,"<a href='$cat_link' title='".eme_trans_esc_html($cat_name,$lang)."'>".eme_trans_esc_html($cat_name,$lang)."</a>");
            else
               array_push($cat_links,eme_translate($cat_name,$lang));
         }
         $replacement = join(", ",$cat_links);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_CALENDAR_DAY/', $result)) {
         $day_key = get_query_var('calendar_day');
         $replacement=eme_localized_date($day_key." ".$eme_timezone);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_RECURRENCE_DESC|#_RECURRENCEDESC/', $result)) {
         if ($event ['recurrence_id']) {
            $replacement = eme_get_recurrence_desc ( $event ['recurrence_id'] );
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_RECURRENCE_NBR/', $result)) {
         // returns the sequence number of an event in a recurrence series
         if ($event ['recurrence_id']) {
            $event_ids = eme_get_recurrence_eventids ( $event ['recurrence_id'] );
            $nbr = array_search($event['event_id'],$event_ids);
            if ($nbr !== false) {
               $replacement = $nbr+1;
            }
         }

      } elseif ($event && preg_match('/#_RSVPEND/', $result)) {
         // show the end date+time for which a user can rsvp for an event
         if (eme_is_event_rsvp($event)) {
               $rsvp_number_days=$event['rsvp_number_days'];
               $rsvp_number_hours=$event['rsvp_number_hours'];
               $rsvp_end_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
               $rsvp_end_obj->minusDays($rsvp_number_days);
               $rsvp_end_obj->minusHours($rsvp_number_hours);
               $rsvp_end_date = eme_localized_date($rsvp_end_obj->getDateTime()." ".$eme_timezone);
               $rsvp_end_time = eme_localized_time($rsvp_end_obj->getDateTime()." ".$eme_timezone);
               $replacement = $rsvp_end_date." ".$rsvp_end_time;
         }

      } elseif ($event && preg_match('/#_IS_RSVP_ENDED/', $result)) {
         if (eme_is_event_rsvp($event)) {
            $rsvp_number_days=$event['rsvp_number_days'];
            $rsvp_number_hours=$event['rsvp_number_hours'];
            $rsvp_end_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
            $rsvp_end_obj->minusDays($rsvp_number_days);
            $rsvp_end_obj->minusHours($rsvp_number_hours);
            if ($rsvp_end_obj->lessThan($eme_date_obj_now))
               $replacement = 1;
            else
               $replacement = 0;
         }

      } elseif ($event && preg_match('/#_EVENT_EXTERNAL_REF/', $result)) {
         if (!empty($event['event_external_ref'])) {
            // remove the 'fb_' prefix
            $replacement=preg_replace('/fb_/','',$event['event_external_ref']);
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif (preg_match('/#_IS_SINGLE_DAY/', $result)) {
         if (eme_is_single_day_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_SINGLE_EVENT/', $result)) {
         if (eme_is_single_event_page())
            $replacement = 1;
         else
            $replacement = 0;

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

      } elseif ($event && preg_match('/#_IS_RSVP_ENABLED/', $result)) {
         if (eme_is_event_rsvp($event))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_PRIVATE_EVENT/', $result)) {
         if ($event ['event_status'] == STATUS_PRIVATE)
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_RECURRENT_EVENT/', $result)) {
         if ($event ['recurrence_id'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_ONGOING_EVENT/', $result)) {
         $eme_start_obj = new ExpressiveDate($event['event_start_date']." ".$event['event_start_time'],$eme_timezone);
         $eme_end_obj = new ExpressiveDate($event['event_end_date']." ".$event['event_end_time'],$eme_timezone);
         if ($eme_start_obj->lessOrEqualTo($eme_date_obj_now) &&
             $eme_end_obj->greaterOrEqualTo($eme_date_obj_now))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_REGISTERED/', $result)) {
         if (is_user_logged_in() && eme_get_booking_ids_by_wp_event_id($current_userid,$event['event_id']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTIPRICE/', $result)) {
         if (eme_is_multi($event['price']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTISEAT/', $result)) {
         if (eme_is_multi($event['event_seats']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_ALLDAY/', $result)) {
         if ($event['event_properties']['all_day'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_ATTENDANCE/', $result)) {
         if ($event['event_properties']['take_attendance'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_AUTHOR$/', $result)) {
         if ($event['event_author']==$current_userid)
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_CONTACTPERSON/', $result)) {
         if ($event['event_contactperson_id']==$current_userid)
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_AUTHOR_OR_CONTACTPERSON/', $result)) {
         if ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTIDAY/', $result)) {
         if ($event['event_start_date'] != $event['event_end_date'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_FIRST_RECURRENCE/', $result)) {
         // returns 1 if the event is the first event in a recurrence series
         if ($event ['recurrence_id']) {
            $event_ids = eme_get_recurrence_eventids ( $event ['recurrence_id'] );
            $nbr = array_search($event['event_id'],$event_ids);
            if ($nbr !== false && $nbr==0) {
               $replacement = 1;
            }
         }

      } elseif ($event && preg_match('/#_IS_LAST_RECURRENCE/', $result)) {
         // returns 1 if the event is the last event in a recurrence series
         if ($event ['recurrence_id']) {
            $event_ids = eme_get_recurrence_eventids ( $event ['recurrence_id'] );
            $nbr = array_search($event['event_id'],$event_ids);
            $last_index = count($events)-1;
            if ($nbr !== false && $nbr==$last_index) {
               $replacement = 1;
            }
         }

      } elseif (preg_match('/#_LOCALE/', $result)) {
         $replacement = get_locale();

      } elseif (preg_match('/#_LANG/', $result)) {
	 if (empty($lang))
	    $replacement=eme_detect_lang();
         else
            $replacement=$lang;

      } else {
         $found = 0;
      }

      if ($found) {
         if ($need_escape)
            $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
         if ($need_urlencode)
            $replacement = rawurlencode($replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   # now handle all possible location placeholders
   # but the eme_replace_locations_placeholders can't do "do_shortcode" at the end, because
   # this would cause [eme_if] tags to be replaced here already, while some placeholders of the
   # event haven't been replaced yet (like time placeholders, and event details)
   $format = eme_replace_event_location_placeholders ( $format, $event, $target, 0, $lang );

  // for extra date formatting, eg. #_{d/m/Y}
   preg_match_all("/#(ESC|URL)?@?_\{.*?\}/", $format, $results);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($results[0],'eme_sort_stringlenth');
   foreach($results[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = '';
      if(substr($result, 0, 3 ) == "#@_") {
         $my_date = "event_end_date";
         $my_time = "event_end_time";
         $offset = 4;
      } else {
         $my_date = "event_start_date";
         $my_time = "event_start_time";
         $offset = 3;
      }

      $replacement = eme_localized_date($event[$my_date]." ".$event[$my_time]." ".$eme_timezone,substr($result, $offset, (strlen($result)-($offset+1)) ));

      if ($need_escape)
         $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   # we handle NOTES the last, this used to be the default behavior
   # so no placeholder replacement happened accidentaly in possible shortcodes inside #_NOTES
   # but since we have templates to aid in all that ...
   if (!$eme_enable_notes_placeholders)
      $format = eme_replace_notes_placeholders ( $format, $event, $target );
 
   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   if ($do_shortcode)
      return do_shortcode($format);
   else
      return $format;
}

function eme_sanitize_text_field($value) {
	return sanitize_text_field(stripslashes($value));
}

function eme_sanitize_textarea($value) {
	if (function_exists('sanitize_textarea_field'))
		return sanitize_textarea_field(stripslashes($value));
	else
		return sanitize_text_field(stripslashes($value));
}

function eme_sanitize_request($value ) {
   if (!is_array($value))
      $value = esc_sql(strip_shortcodes($value));
   else
      array_walk_recursive($value, "eme_single_sanitize_request");
   return $value;
}

function eme_single_sanitize_request(&$value) {
   $value = esc_sql(strip_shortcodes($value));
}

function eme_sort_stringlenth($a,$b){
   return strlen($b)-strlen($a);
}

//backwards compat
function eme_trans_sanitize_html( $value, $lang='') {
   return eme_trans_esc_html( $value, $lang);
}

function eme_trans_esc_html( $value, $lang='') {
   return eme_esc_html(eme_translate( $value,$lang));
}

function eme_translate ( $value, $lang='') {
   //if (empty($lang))
   //   $lang=eme_detect_lang();
   $value = __($value,'events-made-easy');
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') && function_exists('qtrans_use')) {
      if (empty($lang))
         return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
      else
         return qtrans_use($lang,$value);
   } elseif (function_exists('ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') && function_exists('ppqtrans_use')) {
      if (empty($lang))
         return ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
      else
         return ppqtrans_use($lang,$value);
   } elseif (function_exists('qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage') && function_exists('qtranxf_use')) {
      if (empty($lang))
         return qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
      else
         return qtranxf_use($lang,$value);
   } elseif (function_exists('pll_translate_string') && function_exists('pll__')) {
      if (empty($lang))
         return pll__($value);
      else
         return pll_translate_string($value,$lang);
   } else {
      return $value;
   }
}

function eme_sanitize_rss( $value ) {
   #$value =  str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", $value ) );
   return "<![CDATA[".$value."]]>";
}

function eme_htmlspecialchars(&$value) {
  //$value = htmlspecialchars($value, ENT_QUOTES');
  if (!is_null($value))
   $value = esc_html($value);
}

function eme_esc_html( $value ) {
   //return htmlentities($value,ENT_QUOTES,get_option('blog_charset'));
   if (is_null($value))
      return $value;
   if (!is_array($value))
      eme_htmlspecialchars($value);
   else
      array_walk_recursive($value, "eme_htmlspecialchars");
   return $value;
}

function eme_strip_tags_single(&$value) {
   $value = preg_replace("/^\s*$/","",wp_strip_all_tags(stripslashes($value)));
   $value = eme_strip_weird($value);
}

function eme_strip_tags ($value ) {
   if (is_null($value))
      return $value;
   if (!is_array($value)) {
      eme_strip_tags_single($value);
   } else {
      array_walk_recursive($value, "eme_strip_tags_single");
   }
   return $value;
}

function eme_explain_events_page_missing() {
   $advice = sprintf(__("Error: the special events page is not set or no longer exist, please set the option '%s' to an existing page or EME will not work correctly!",'events-made-easy'),__ ( 'Events page', 'events-made-easy'));
   ?>
   <div id="message" class="error"><p> <?php echo $advice; ?> </p></div>
   <?php
}

?>
