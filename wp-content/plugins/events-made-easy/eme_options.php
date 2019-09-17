<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_add_options($reset=0) {
   $contact_person_email_subject_localizable = __("New booking for '#_EVENTNAME'",'events-made-easy');
   $contact_person_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) will attend #_EVENTNAME on #_STARTDATE. They want to reserve #_RESPSPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'events-made-easy') ;
   $contactperson_cancelled_email_subject_localizable = __("A reservation has been cancelled for '#_EVENTNAME'",'events-made-easy');
   $contactperson_cancelled_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) has cancelled for #_EVENTNAME on #_STARTDATE. <br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'events-made-easy') ;
   $contact_person_pending_email_subject_localizable = __("Approval required for new booking for '#_EVENTNAME'",'events-made-easy');
   $contact_person_pending_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) would like to attend #_EVENTNAME on #_STARTDATE. They want to reserve #_RESPSPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'events-made-easy') ;
   $respondent_email_subject_localizable = __("Reservation for '#_EVENTNAME' confirmed",'events-made-easy');
   $respondent_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>You have successfully reserved #_RESPSPACES space(s) for #_EVENTNAME.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_pending_email_subject_localizable = __("Reservation for '#_EVENTNAME' is pending",'events-made-easy');
   $registration_pending_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>Your request to reserve #_RESPSPACES space(s) for #_EVENTNAME is pending.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_cancelled_email_subject_localizable = __("Reservation for '#_EVENTNAME' cancelled",'events-made-easy');
   $registration_cancelled_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>Your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been cancelled.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_denied_email_subject_localizable = __("Reservation for '#_EVENTNAME' denied",'events-made-easy');
   $registration_denied_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>Your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been denied.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_updated_email_subject_localizable = __("Reservation for '#_EVENTNAME' updated",'events-made-easy');
   $registration_updated_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>Your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been updated.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_paid_email_subject_localizable = __("Reservation for '#_EVENTNAME' paid",'events-made-easy');
   $registration_paid_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>Your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been paid for.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'events-made-easy');
   $registration_recorded_ok_html_localizable = __('Your booking has been recorded','events-made-easy');
   $registration_form_format_localizable = "<table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Last Name', 'events-made-easy')."*:</th><td>#_LASTNAME</td></tr>
            <tr><th scope='row'>".__('First Name', 'events-made-easy')."*:</th><td>#REQ_FIRSTNAME</td></tr>
            <tr><th scope='row'>".__('E-Mail', 'events-made-easy')."*:</th><td>#_EMAIL</td></tr>
            <tr><th scope='row'>".__('Phone number', 'events-made-easy').":</th><td>#_PHONE</td></tr>
            <tr><th scope='row'>".__('Seats', 'events-made-easy')."*:</th><td>#_SPACES</td></tr>
            <tr><th scope='row'>".__('Comment', 'events-made-easy').":</th><td>#_COMMENT</td></tr>
            #_CAPTCHAHTML{<tr><th scope='row'>Please fill in the code displayed here:</th><td>#_CAPTCHA</td></tr>}
            </table>
            #_SUBMIT
            ";
   $cancel_form_format_localizable = "<table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Last Name', 'events-made-easy')."*:</th><td>#_LASTNAME</td></tr>
            <tr><th scope='row'>".__('First Name', 'events-made-easy')."*:</th><td>#REQ_FIRSTNAME</td></tr>
            <tr><th scope='row'>".__('E-Mail', 'events-made-easy')."*:</th><td>#_EMAIL</td></tr>
            #_CAPTCHAHTML{<tr><th scope='row'>Please fill in the code displayed here:</th><td>#_CAPTCHA</td></tr>}
            </table>
            #_SUBMIT
            ";
   $eme_gdpr_subject_localizable = __("Personal info request",'events-made-easy');
   $eme_gdpr_body_localizable = __("Hi, please copy/paste this link in your browser to be able to see all your personal info: #_GDPR_URL",'events-made-easy') ;
   $eme_unsub_subject_localizable = __("Unsubscription request",'events-made-easy');
   $eme_unsub_body_localizable = __("Hi, please copy/paste this link in your browser to unsubscribe: #_UNSUB_CONFIRM_URL",'events-made-easy') ;
   $eme_payment_button_label_localizable = __('Pay via %s','events-made-easy');
   $eme_payment_button_above_localizable = "<br />".__("You can pay for this event via %s. If you wish to do so, click the button below.",'events-made-easy');

   $eme_rsvp_no_longer_allowed_localizable = __('Bookings no longer allowed on this date.','events-made-easy');
   $eme_rsvp_cancel_no_longer_allowed_localizable = __('Annulations no longer allowed on this date.','events-made-easy');
   $eme_rsvp_required_field_localizable = __('Required field','events-made-easy');
   $eme_options = array('eme_event_list_item_format' => DEFAULT_EVENT_LIST_ITEM_FORMAT,
   'eme_event_list_item_format_header' => DEFAULT_EVENT_LIST_HEADER_FORMAT,
   'eme_cat_event_list_item_format_header' => DEFAULT_CAT_EVENT_LIST_HEADER_FORMAT,
   'eme_event_list_item_format_footer' => DEFAULT_EVENT_LIST_FOOTER_FORMAT,
   'eme_cat_event_list_item_format_footer' => DEFAULT_CAT_EVENT_LIST_FOOTER_FORMAT,
   'eme_display_calendar_in_events_page' => 0,
   'eme_single_event_format' => DEFAULT_SINGLE_EVENT_FORMAT,
   'eme_event_page_title_format' => DEFAULT_EVENT_PAGE_TITLE_FORMAT,
   'eme_event_html_title_format' => DEFAULT_EVENT_HTML_TITLE_FORMAT,
   'eme_show_period_monthly_dateformat' => DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT,
   'eme_show_period_yearly_dateformat' => DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT,
   'eme_filter_form_format' => DEFAULT_FILTER_FORM_FORMAT,
   'eme_events_page_title' => DEFAULT_EVENTS_PAGE_TITLE,
   'eme_no_events_message' => DEFAULT_NO_EVENTS_MESSAGE,
   'eme_location_page_title_format' => DEFAULT_LOCATION_PAGE_TITLE_FORMAT,
   'eme_location_html_title_format' => DEFAULT_LOCATION_HTML_TITLE_FORMAT,
   'eme_location_baloon_format' => DEFAULT_LOCATION_BALLOON_FORMAT,
   'eme_location_event_list_item_format' => DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT,
   'eme_location_no_events_message' => DEFAULT_LOCATION_NO_EVENTS_MESSAGE,
   'eme_single_location_format' => DEFAULT_SINGLE_LOCATION_FORMAT,
   'eme_ical_title_format' => DEFAULT_ICAL_TITLE_FORMAT,
   'eme_ical_description_format' => DEFAULT_ICAL_DESCRIPTION_FORMAT,
   'eme_ical_location_format' => DEFAULT_ICAL_LOCATION_FORMAT,
   'eme_ical_quote_tzid' => 0,
   'eme_rss_main_title' => get_bloginfo('title')." - ".__('Events', 'events-made-easy'),
   'eme_rss_main_description' => get_bloginfo('description')." - ".__('Events', 'events-made-easy'),
   'eme_rss_description_format' => DEFAULT_RSS_DESCRIPTION_FORMAT,
   'eme_rss_title_format' => DEFAULT_RSS_TITLE_FORMAT,
   'eme_rss_show_pubdate' => 1,
   'eme_rss_pubdate_startdate' => 0,
   'eme_gmap_is_active'=> DEFAULT_GMAP_ENABLED,
   'eme_gmap_api_key'=> '',
   'eme_gmap_zooming'=> DEFAULT_GMAP_ZOOMING,
   'eme_global_zoom_factor'=> DEFAULT_GLOBAL_ZOOM_FACTOR,
   'eme_indiv_zoom_factor'=> DEFAULT_INDIV_ZOOM_FACTOR,
   'eme_global_maptype'=> DEFAULT_GLOBAL_MAPTYPE,
   'eme_indiv_maptype'=> DEFAULT_INDIV_MAPTYPE,
   'eme_seo_permalink'=> DEFAULT_SEO_PERMALINK,
   'eme_permalink_events_prefix' => 'events',
   'eme_permalink_locations_prefix' => 'locations',
   'eme_default_contact_person' => -1,
   'eme_captcha_for_booking' => 0 ,
   'eme_captcha_no_case' => 0 ,
   'eme_rsvp_mail_notify_is_active' => 1 ,
   'eme_rsvp_mail_notify_pending' => 1 ,
   'eme_rsvp_mail_notify_paid' => 0 ,
   'eme_rsvp_mail_notify_approved' => 1 ,
   'eme_rsvp_end_target' => 'start',
   'eme_rsvp_check_required_fields' => 1,
   'eme_contactperson_email_subject' => $contact_person_email_subject_localizable,
   'eme_contactperson_email_body' => eme_br2nl($contact_person_email_body_localizable),
   'eme_contactperson_cancelled_email_subject' => $contactperson_cancelled_email_subject_localizable,
   'eme_contactperson_cancelled_email_body' => eme_br2nl($contactperson_cancelled_email_body_localizable),
   'eme_contactperson_pending_email_subject' => $contact_person_pending_email_subject_localizable,
   'eme_contactperson_pending_email_body' => eme_br2nl($contact_person_pending_email_body_localizable),
   'eme_contactperson_ipn_email_subject' => '',
   'eme_contactperson_ipn_email_body' => '',
   'eme_respondent_email_subject' => $respondent_email_subject_localizable,
   'eme_respondent_email_body' => eme_br2nl($respondent_email_body_localizable),
   'eme_registration_pending_email_subject' => $registration_pending_email_subject_localizable,
   'eme_registration_pending_email_body' => eme_br2nl($registration_pending_email_body_localizable),
   'eme_registration_cancelled_email_subject' => $registration_cancelled_email_subject_localizable,
   'eme_registration_cancelled_email_body' => eme_br2nl($registration_cancelled_email_body_localizable),
   'eme_registration_denied_email_subject' => $registration_denied_email_subject_localizable,
   'eme_registration_denied_email_body' => eme_br2nl($registration_denied_email_body_localizable),
   'eme_registration_updated_email_subject' => $registration_updated_email_subject_localizable,
   'eme_registration_updated_email_body' => eme_br2nl($registration_updated_email_body_localizable),
   'eme_registration_paid_email_subject' => $registration_paid_email_subject_localizable,
   'eme_registration_paid_email_body' => eme_br2nl($registration_paid_email_body_localizable),
   'eme_registration_recorded_ok_html' => $registration_recorded_ok_html_localizable,
   'eme_registration_form_format' => $registration_form_format_localizable,
   'eme_cancel_form_format' => $cancel_form_format_localizable,
   'eme_gdpr_subject' => $eme_gdpr_subject_localizable,
   'eme_gdpr_body' => $eme_gdpr_body_localizable,
   'eme_unsub_subject' => $eme_unsub_subject_localizable,
   'eme_unsub_body' => $eme_unsub_body_localizable,
   'eme_cancel_rsvp_days' => 0,
   'eme_smtp_host' => 'localhost',
   'eme_smtp_port' => 25,
   'eme_smtp_encryption' => '',
   'eme_smtp_verify_cert' => 1,
   'eme_mail_sender_name' => '',
   'eme_mail_sender_address' => '',
   'eme_mail_bcc_address' => '',
   'eme_rsvp_mail_send_method' => 'smtp',
   'eme_rsvp_send_html' => 0,
   'eme_rsvp_mail_SMTPAuth' => 0,
   'eme_rsvp_registered_users_only' => 0,
   'eme_rsvp_reg_for_new_events' => 0,
   'eme_rsvp_require_approval' => 0,
   'eme_rsvp_show_form_after_booking' => 1,
   'eme_rsvp_hide_full_events' => 0,
   'eme_rsvp_hide_rsvp_ended_events' => 0,
   'eme_rsvp_admin_allow_overbooking' => 0,
   'eme_attendees_list_format' => DEFAULT_ATTENDEES_LIST_FORMAT,
   'eme_attendees_list_ignore_pending' => 0,
   'eme_bookings_list_format' => DEFAULT_BOOKINGS_LIST_FORMAT,
   'eme_bookings_list_ignore_pending' => 0,
   'eme_bookings_list_header_format' => DEFAULT_BOOKINGS_LIST_HEADER_FORMAT,
   'eme_bookings_list_footer_format' => DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT,
   'eme_full_calendar_event_format' => DEFAULT_FULL_CALENDAR_EVENT_FORMAT,
   'eme_small_calendar_event_title_format' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT,
   'eme_small_calendar_event_title_separator' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR, 
   'eme_cal_hide_past_events' => 0,
   'eme_cal_show_single' => 1,
   'eme_hello_to_user' => 1,
   'eme_smtp_debug' => 0,
   'eme_shortcodes_in_widgets' => 0,
   'eme_load_js_in_header' => 0,
   'eme_use_client_clock' => 0,
   'eme_donation_done' => 0,
   'eme_event_list_number_items'  => 10,
   'eme_use_select_for_locations' => DEFAULT_USE_SELECT_FOR_LOCATIONS,
   'eme_attributes_enabled' => DEFAULT_ATTRIBUTES_ENABLED,
   'eme_recurrence_enabled' => DEFAULT_RECURRENCE_ENABLED,
   'eme_rsvp_enabled' => DEFAULT_RSVP_ENABLED,
   'eme_rsvp_addbooking_submit_string' => DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING,
   'eme_rsvp_addbooking_min_spaces' => 1,
   'eme_rsvp_addbooking_max_spaces' => 10,
   'eme_rsvp_delbooking_submit_string' => DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING,
   'eme_rsvp_required_field_string' => $eme_rsvp_required_field_localizable,
   'eme_rsvp_no_longer_allowed_string' => $eme_rsvp_no_longer_allowed_localizable,
   'eme_rsvp_cancel_no_longer_allowed_string' => $eme_rsvp_cancel_no_longer_allowed_localizable,
   'eme_categories_enabled' => DEFAULT_CATEGORIES_ENABLED,
   'eme_cap_add_event' => DEFAULT_CAP_ADD_EVENT, 
   'eme_cap_author_event' => DEFAULT_CAP_AUTHOR_EVENT, 
   'eme_cap_publish_event' => DEFAULT_CAP_PUBLISH_EVENT,
   'eme_cap_list_events' => DEFAULT_CAP_LIST_EVENTS,
   'eme_cap_edit_events' => DEFAULT_CAP_EDIT_EVENTS,
   'eme_cap_add_locations' => DEFAULT_CAP_ADD_LOCATION,
   'eme_cap_author_locations' => DEFAULT_CAP_AUTHOR_LOCATION,
   'eme_cap_edit_locations' => DEFAULT_CAP_EDIT_LOCATIONS,
   'eme_cap_categories' => DEFAULT_CAP_CATEGORIES,
   'eme_cap_holidays' => DEFAULT_CAP_HOLIDAYS,
   'eme_cap_templates' => DEFAULT_CAP_TEMPLATES,
   'eme_cap_people' => DEFAULT_CAP_PEOPLE,
   'eme_cap_members' => DEFAULT_CAP_MEMBERS,
   'eme_cap_discounts' => DEFAULT_CAP_DISCOUNTS,
   'eme_cap_approve' => DEFAULT_CAP_APPROVE,
   'eme_cap_registrations' => DEFAULT_CAP_REGISTRATIONS,
   'eme_cap_forms' => DEFAULT_CAP_FORMS,
   'eme_cap_cleanup' => DEFAULT_CAP_CLEANUP,
   'eme_cap_settings' => DEFAULT_CAP_SETTINGS,
   'eme_cap_send_mails' => DEFAULT_CAP_SEND_MAILS,
   'eme_cap_send_other_mails' => DEFAULT_CAP_SEND_OTHER_MAILS,
   'eme_html_header' => '',
   'eme_html_footer' => '',
   'eme_event_html_headers_format' => '',
   'eme_location_html_headers_format' => '',
   'eme_offline_payment' => '',
   'eme_paypal_url' => PAYPAL_LIVE_URL,
   'eme_paypal_business' => '',
   'eme_paypal_s_encrypt' => 0,
   'eme_paypal_no_tax' => 0,
   'eme_paypal_s_pubcert' => '',
   'eme_paypal_s_privkey' => '',
   'eme_paypal_s_paypalcert' => '',
   'eme_paypal_s_certid' => '',
   'eme_paypal_cost' => 0,
   'eme_paypal_cost2' => 0,
   'eme_paypal_button_label' => sprintf($eme_payment_button_label_localizable,"Paypal"),
   'eme_paypal_button_img_url' => '',
   'eme_paypal_button_above' => sprintf($eme_payment_button_above_localizable,"Paypal"),
   'eme_paypal_button_below' => '',
   'eme_2co_demo' => 0,
   'eme_2co_business' => '',
   'eme_2co_secret' => '',
   'eme_2co_cost' => 0,
   'eme_2co_cost2' => 0,
   'eme_2co_button_label' => sprintf($eme_payment_button_label_localizable,"2Checkout"),
   'eme_2co_button_img_url' => '',
   'eme_2co_button_above' => sprintf($eme_payment_button_above_localizable,"2Checkout"),
   'eme_2co_button_below' => '',
   'eme_webmoney_demo' => 0,
   'eme_webmoney_purse' => '',
   'eme_webmoney_secret' => '',
   'eme_webmoney_cost' => 0,
   'eme_webmoney_cost2' => 0,
   'eme_webmoney_button_label' => sprintf($eme_payment_button_label_localizable,"Webmoney"),
   'eme_webmoney_button_img_url' => '',
   'eme_webmoney_button_above' => sprintf($eme_payment_button_above_localizable,"Webmoney"),
   'eme_webmoney_button_below' => '',
   'eme_worldpay_demo' => 1,
   'eme_worldpay_instid' => '',
   'eme_worldpay_md5_secret' => '',
   'eme_worldpay_md5_parameters' => 'instId:cartId:currency:amount',
   'eme_worldpay_test_pwd' => '',
   'eme_worldpay_live_pwd' => '',
   'eme_worldpay_cost' => 0,
   'eme_worldpay_cost2' => 0,
   'eme_worldpay_button_label' => sprintf($eme_payment_button_label_localizable,"Worldpay"),
   'eme_worldpay_button_img_url' => '',
   'eme_worldpay_button_above' => sprintf($eme_payment_button_above_localizable,"Worldpay"),
   'eme_worldpay_button_below' => '',
   'eme_braintree_private_key' => '',
   'eme_braintree_public_key' => '',
   'eme_braintree_merchant_id' => '',
   'eme_braintree_env' => 'production',
   'eme_braintree_cost' => 0,
   'eme_braintree_cost2' => 0,
   'eme_braintree_button_label' => sprintf($eme_payment_button_label_localizable,"Braintree"),
   'eme_braintree_button_img_url' => '',
   'eme_braintree_button_above' => sprintf($eme_payment_button_above_localizable,"Braintree"),
   'eme_braintree_button_below' => '',
   'eme_stripe_private_key' => '',
   'eme_stripe_public_key' => '',
   'eme_stripe_cost' => 0,
   'eme_stripe_cost2' => 0,
   'eme_stripe_button_label' => sprintf($eme_payment_button_label_localizable,"Stripe"),
   'eme_stripe_button_img_url' => '',
   'eme_stripe_button_above' => sprintf($eme_payment_button_above_localizable,"Stripe"),
   'eme_stripe_button_below' => '',
   'eme_sagepay_demo' => 1,
   'eme_sagepay_vendor_name' => '',
   'eme_sagepay_test_pwd' => '',
   'eme_sagepay_live_pwd' => '',
   'eme_sagepay_cost' => 0,
   'eme_sagepay_cost2' => 0,
   'eme_sagepay_button_label' => sprintf($eme_payment_button_label_localizable,"Sage Pay"),
   'eme_sagepay_button_img_url' => '',
   'eme_sagepay_button_above' => sprintf($eme_payment_button_above_localizable,"Sage Pay"),
   'eme_sagepay_button_below' => '',
   'eme_fdgg_url' => FDGG_LIVE_URL,
   'eme_fdgg_store_name' => '',
   'eme_fdgg_shared_secret' => '',
   'eme_fdgg_cost' => 0,
   'eme_fdgg_cost2' => 0,
   'eme_fdgg_button_label' => sprintf($eme_payment_button_label_localizable,"First Data"),
   'eme_fdgg_button_img_url' => '',
   'eme_fdgg_button_above' => sprintf($eme_payment_button_above_localizable,"First Data"),
   'eme_fdgg_button_below' => '',
   'eme_mollie_api_key' => '',
   'eme_mollie_cost' => 0,
   'eme_mollie_cost2' => 0,
   'eme_mollie_button_label' => sprintf($eme_payment_button_label_localizable,"Mollie"),
   'eme_mollie_button_img_url' => '',
   'eme_mollie_button_above' => sprintf($eme_payment_button_above_localizable,"Mollie"),
   'eme_mollie_button_below' => __('Using Mollie, you can pay using one of the following methods:','events-made-easy')."<br />",
   'eme_event_initial_state' => STATUS_DRAFT,
   'eme_use_external_url' => 1,
   'eme_default_currency' => 'EUR',
   'eme_default_price' => '0',
   'eme_rsvp_number_days' => 0,
   'eme_rsvp_number_hours' => 0,
   'eme_thumbnail_size' => 'thumbnail',
   'eme_payment_form_header_format' => '',
   'eme_payment_form_footer_format' => '',
   'eme_multipayment_form_header_format' => '',
   'eme_multipayment_form_footer_format' => '',
   'eme_payment_show_custom_return_page' => 0,
   'eme_payment_succes_format' => '',
   'eme_payment_fail_format' => '',
   'eme_payment_member_succes_format' => '',
   'eme_payment_member_fail_format' => '',
   'eme_loop_protection' => 'simple',
   'eme_enable_notes_placeholders' => 0,
   'eme_uninstall_drop_data' => 0,
   'eme_uninstall_drop_settings' => 0,
   'eme_csv_separator' => EME_DEFAULT_CSV_SEPARATOR,
   'eme_decimals' => 2,
   'eme_localize_price' => 1,
   'eme_autocomplete_sources' => 'none',
   'eme_clean_session_data' => 0,
   'eme_cron_cleanup_unpaid_minutes' => 1440,
   'eme_cron_queue_count' => 50,
   'eme_disable_wpautop' => 0,
   'eme_queue_mails' => 0,
   'eme_people_massmail' => 1
   );
   
   foreach($eme_options as $key => $value){
      eme_add_option($key, $value, $reset);
   }
}

function eme_update_options($db_version) {
	if ($db_version) {
		if ($db_version>0 && $db_version<49) {
			delete_option('eme_events_admin_limit');
		}
		if ($db_version>0 && $db_version<55) {
			$smtp_port=get_option('eme_rsvp_mail_port');
			delete_option('eme_rsvp_mail_port');
			update_option('eme_smtp_port', $smtp_port);
		}
		if ($db_version<70) {
			delete_option('eme_google_checkout_type');
			delete_option('eme_google_merchant_id');
			delete_option('eme_google_merchant_key');
			delete_option('eme_google_cost');
		}
		if ($db_version<105) {
			delete_option('eme_phpold');
			delete_option('eme_conversion_needed');
		}
		if ($db_version<119) {
			// remove some deprecated options
			$options = array ('eme_image_max_width', 'eme_image_max_height', 'eme_image_max_size','eme_legacy','eme_deprecated','eme_legacy_warning','eme_list_events_page','eme_deny_mail_event_edit','eme_fb_app_id');
			foreach ( $options as $opt ) {
				delete_option ( $opt );
			}
			// rename some options
			$options2 =array(
				'eme_cron_reminder_unpayed_minutes'=>'eme_cron_reminder_unpaid_minutes',
				'eme_cron_cleanup_unpayed_minutes'=>'eme_cron_cleanup_unpaid_minutes',
				'eme_cron_reminder_unpayed_subject'=>'eme_cron_reminder_unpaid_subject',
				'eme_cron_reminder_unpayed_body'=>'eme_cron_reminder_unpaid_body',
			);
			foreach ( $options2 as $old_option=>$new_option ) {
				if (get_option($old_option)) {
					update_option($new_option,get_option($old_option));
					delete_option($old_option);
				}
			}
			// remove old schedule
			if (wp_next_scheduled('eme_cron_cleanup_unpayed'))
				wp_clear_scheduled_hook('eme_cron_cleanup_unpayed');
		}
		if ($db_version<127) {
			delete_option('eme_calc_price_dynamically');
		}
		if ($db_version<157) {
			delete_option('eme_rsvp_force_nl2br');
		}
		if ($db_version<164) {
			delete_option('eme_payment_add_bookingid_to_return');
		}
	}
	// make sure the captcha doesn't cause problems
	if (!function_exists('imagecreatetruecolor'))
		update_option('eme_captcha_for_booking', 0);

	// always reset the drop data option
	update_option('eme_uninstall_drop_data', 0);
	update_option('eme_uninstall_drop_settings', 0);

	// always reset the donation option
	update_option('eme_donation_done', 0);
}

function eme_add_option($key, $value, $reset) {
   $option_val = get_option($key,"non_existing");
   if ($option_val=="non_existing" || $reset) {
      update_option($key, $value);
   }
}

////////////////////////////////////
// WP options registration/deletion
////////////////////////////////////
function eme_options_delete() {
   $all_options = wp_load_alloptions();
   foreach( $all_options as $name => $value ) {
      if (preg_match('/^eme_/',$name))
         delete_option($name);
   }
}

function eme_metabox_options_delete() {
   global $wpdb;
   $screens = array( 'events_page_eme-new_event', 'toplevel_page_eme-manager' );
   foreach ($screens as $screen) {
      foreach ( array( 'metaboxhidden', 'closedpostboxes', 'wp_metaboxorder','meta-box-order', 'screen_layout' ) as $option )
         $keys[] = "'{$option}_{$screen}'";
   }
   $keys = '( ' . implode( ', ', $keys ) . ' )';
   $wpdb->query( "
         DELETE FROM {$wpdb->usermeta}
         WHERE meta_key IN {$keys}
         " );
}

function eme_options_register() {

   // only the options you want changed in the Settings page, not eg. eme_hello_to_user, eme_donation_done
   // and only those for the tab shown, otherwise the others get reset to empty values
   // The tab value is set in the form in the function eme_options_page. It needs to be set there as a hidden value when calling options.php, otherwise
   //    it won't be known here and all values will be lost.
   if (!isset($_POST['option_page']) || ($_POST['option_page'] != "eme-options"))
      return;
   $options = array();
   $tab = isset( $_POST['tab'] ) ? esc_attr($_POST['tab']) : 'general';
   switch ( $tab ){
	      case 'general' :
                 $options = array ('eme_use_select_for_locations','eme_recurrence_enabled', 'eme_rsvp_enabled', 'eme_categories_enabled', 'eme_attributes_enabled', 'eme_gmap_is_active', 'eme_load_js_in_header','eme_use_client_clock','eme_uninstall_drop_data','eme_uninstall_drop_settings','eme_shortcodes_in_widgets','eme_loop_protection','eme_enable_notes_placeholders','eme_autocomplete_sources','eme_clean_session_data','eme_captcha_for_booking','eme_captcha_no_case');
	         break;
	      case 'seo' :
                 $options = array ('eme_seo_permalink','eme_permalink_events_prefix','eme_permalink_locations_prefix');
	         break;
	      case 'access' :
                 $options = array ('eme_cap_add_event', 'eme_cap_author_event', 'eme_cap_publish_event', 'eme_cap_list_events', 'eme_cap_edit_events', 'eme_cap_add_locations', 'eme_cap_author_locations', 'eme_cap_edit_locations', 'eme_cap_categories', 'eme_cap_holidays', 'eme_cap_templates', 'eme_cap_people', 'eme_cap_members', 'eme_cap_discounts', 'eme_cap_approve', 'eme_cap_registrations', 'eme_cap_forms', 'eme_cap_cleanup', 'eme_cap_settings','eme_cap_send_mails','eme_cap_send_other_mails');
	         break;
	      case 'events' :
                 $options = array ('eme_events_page','eme_display_calendar_in_events_page','eme_event_list_number_items','eme_event_initial_state','eme_time_remove_leading_zeros','eme_event_list_item_format_header','eme_cat_event_list_item_format_header','eme_event_list_item_format','eme_event_list_item_format_footer','eme_cat_event_list_item_format_footer','eme_event_page_title_format','eme_event_html_title_format','eme_single_event_format','eme_show_period_monthly_dateformat','eme_show_period_yearly_dateformat','eme_events_page_title','eme_no_events_message','eme_filter_form_format');
	         break;
	      case 'calendar' :
                 $options = array ('eme_small_calendar_event_title_format','eme_small_calendar_event_title_separator','eme_full_calendar_event_format','eme_cal_hide_past_events','eme_cal_show_single');
	         break;
	      case 'locations' :
                 $options = array ('eme_location_list_format_header','eme_location_list_format_item','eme_location_list_format_footer','eme_location_page_title_format','eme_location_html_title_format','eme_single_location_format','eme_location_baloon_format','eme_location_event_list_item_format','eme_location_no_events_message',);
	         break;
	      case 'rss' :
                 $options = array ('eme_rss_main_title','eme_rss_main_description','eme_rss_title_format','eme_rss_description_format','eme_rss_show_pubdate','eme_rss_pubdate_startdate','eme_ical_description_format','eme_ical_location_format','eme_ical_title_format','eme_ical_quote_tzid');
	         break;
	      case 'rsvp' :
                 $options = array ('eme_default_contact_person','eme_rsvp_registered_users_only','eme_rsvp_reg_for_new_events','eme_rsvp_require_approval','eme_rsvp_default_number_spaces','eme_rsvp_addbooking_min_spaces','eme_rsvp_addbooking_max_spaces','eme_rsvp_hide_full_events','eme_rsvp_hide_rsvp_ended_events','eme_rsvp_show_form_after_booking','eme_rsvp_addbooking_submit_string','eme_rsvp_delbooking_submit_string','eme_rsvp_required_field_string','eme_rsvp_no_longer_allowed_string','eme_rsvp_no_cancel_longer_allowed_string','eme_attendees_list_format','eme_attendees_list_ignore_pending','eme_bookings_list_ignore_pending','eme_bookings_list_header_format','eme_bookings_list_format','eme_bookings_list_footer_format','eme_registration_recorded_ok_html','eme_registration_form_format', 'eme_cancel_form_format', 'eme_rsvp_number_days', 'eme_rsvp_number_hours','eme_rsvp_end_target','eme_rsvp_check_required_fields','eme_cancel_rsvp_days','eme_rsvp_admin_allow_overbooking');
	         break;
	      case 'mail' :
                 $options = array ('eme_rsvp_mail_notify_is_active','eme_rsvp_mail_notify_pending','eme_rsvp_mail_notify_paid','eme_rsvp_mail_notify_approved','eme_mail_sender_name','eme_mail_sender_address','eme_rsvp_mail_send_method','eme_smtp_host','eme_smtp_port','eme_smtp_encryption','eme_rsvp_mail_SMTPAuth','eme_smtp_username','eme_smtp_password', 'eme_smtp_debug','eme_rsvp_send_html','eme_mail_bcc_address','eme_smtp_verify_cert','eme_queue_mails','eme_people_massmail');
	         break;
	      case 'mailtemplates' :
                 $options = array ('eme_contactperson_email_subject', 'eme_contactperson_cancelled_email_subject', 'eme_contactperson_pending_email_subject','eme_contactperson_email_body','eme_contactperson_cancelled_email_body','eme_contactperson_pending_email_body','eme_contactperson_ipn_email_subject','eme_contactperson_ipn_email_body','eme_respondent_email_subject','eme_respondent_email_body','eme_registration_pending_email_subject','eme_registration_pending_email_body','eme_registration_cancelled_email_subject','eme_registration_cancelled_email_body','eme_registration_denied_email_subject','eme_registration_denied_email_body','eme_registration_updated_email_subject','eme_registration_updated_email_body','eme_registration_paid_email_subject','eme_registration_paid_email_body','eme_unsub_subject','eme_unsub_body','eme_gdpr_subject','eme_gdpr_body');
	         break;
	      case 'payments' :
                 $options = array ('eme_payment_form_header_format','eme_payment_form_footer_format','eme_multipayment_form_header_format','eme_multipayment_form_footer_format','eme_payment_show_custom_return_page','eme_payment_succes_format','eme_payment_fail_format','eme_payment_member_succes_format','eme_payment_member_fail_format','eme_default_currency','eme_default_price','eme_paypal_url','eme_paypal_business','eme_2co_demo','eme_2co_business','eme_2co_secret','eme_webmoney_purse', 'eme_webmoney_secret', 'eme_webmoney_demo', 'eme_paypal_s_encrypt', 'eme_paypal_s_pubcert', 'eme_paypal_s_privkey', 'eme_paypal_s_paypalcert', 'eme_paypal_s_certid','eme_fdgg_url','eme_fdgg_store_name','eme_fdgg_shared_secret','eme_2co_cost','eme_paypal_cost','eme_fdgg_cost','eme_webmoney_cost','eme_2co_cost2','eme_paypal_cost2','eme_fdgg_cost2','eme_webmoney_cost2','eme_mollie_api_key','eme_mollie_cost','eme_mollie_cost2','eme_paypal_button_label','eme_paypal_button_above','eme_paypal_button_below','eme_2co_button_label','eme_2co_button_above','eme_2co_button_below','eme_fdgg_button_label','eme_fdgg_button_above','eme_fdgg_button_below','eme_webmoney_button_label','eme_webmoney_button_above','eme_webmoney_button_below','eme_mollie_button_label','eme_mollie_button_above','eme_mollie_button_below','eme_paypal_button_img_url','eme_2co_button_img_url','eme_fdgg_button_img_url','eme_webmoney_button_img_url','eme_mollie_button_img_url','eme_sagepay_demo', 'eme_sagepay_vendor_name', 'eme_sagepay_test_pwd', 'eme_sagepay_live_pwd', 'eme_sagepay_cost', 'eme_sagepay_cost2', 'eme_sagepay_button_label', 'eme_sagepay_button_img_url', 'eme_sagepay_button_above', 'eme_sagepay_button_below','eme_paypal_no_tax','eme_worldpay_demo','eme_worldpay_instid','eme_worldpay_md5_secret','eme_worldpay_md5_parameters','eme_worldpay_test_pwd','eme_worldpay_live_pwd','eme_worldpay_cost','eme_worldpay_cost2','eme_worldpay_button_label','eme_worldpay_button_img_url','eme_worldpay_button_above','eme_worldpay_button_below','eme_braintree_private_key','eme_braintree_public_key','eme_braintree_merchant_id','eme_braintree_env','eme_braintree_cost','eme_braintree_cost2','eme_braintree_button_label','eme_braintree_button_img_url','eme_braintree_button_above','eme_braintree_button_below','eme_stripe_private_key','eme_stripe_public_key','eme_stripe_cost','eme_stripe_cost2','eme_stripe_button_label','eme_stripe_button_img_url','eme_stripe_button_above','eme_stripe_button_below','eme_offline_payment');
	         break;
	      case 'maps' :
                 $options = array ('eme_global_zoom_factor','eme_indiv_zoom_factor','eme_global_maptype','eme_indiv_maptype','eme_gmap_api_key', 'eme_gmap_zooming');
	         break;
	      case 'other' :
                 $options = array ('eme_thumbnail_size','eme_image_max_width','eme_image_max_height','eme_image_max_size','eme_html_header','eme_html_footer','eme_event_html_headers_format','eme_location_html_headers_format','eme_csv_separator','eme_use_external_url','eme_decimals','eme_localize_price','eme_disable_wpautop');
	         break;
   }

   // allow js only in very specific header settings
   $allow_js_arr=array('eme_html_header','eme_html_footer','eme_payment_form_header_format','eme_payment_form_footer_format','eme_multipayment_form_header_format','eme_multipayment_form_footer_format','eme_payment_succes_format','eme_payment_fail_format','eme_payment_member_succes_format','eme_payment_member_fail_format');
   foreach ( $options as $opt ) {
	   if (!is_array($opt)) {
		   if (in_array($opt,$allow_js_arr))
			   register_setting ( 'eme-options', $opt );
		   else
			   register_setting ( 'eme-options', $opt, 'eme_sanitize_options' );
	   } else {
		   register_setting ( 'eme-options', $opt, 'eme_sanitize_options' );
	   }
   }
}

function eme_sanitize_options($input) {
   // allow js only in very specific header settings
   $allow_js_arr=array('eme_html_header','eme_html_footer','eme_payment_form_header_format','eme_payment_form_footer_format','eme_multipayment_form_header_format','eme_multipayment_form_footer_format','eme_payment_succes_format','eme_payment_fail_format','eme_payment_member_succes_format','eme_payment_member_fail_format');
   if (is_array($input)) {
      $output=array();
      foreach ($input as $key=>$value) {
         if (in_array($key,$allow_js_arr))
            $output[$key]=$value;
         else
            $output[$key]=eme_strip_js($value);
      }
   } else {
         $output=eme_strip_js($input);
   }
   return $output;
}

function eme_handle_get() {
   global $plugin_page;
   if ( !is_admin() || !preg_match('/^eme-/', $plugin_page) )
      return;

   // if settings have been changed, check if the SEO rules need to be flushed
   if ($plugin_page == 'eme-options' && isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
      $old_events_page_id=get_option('eme_old_events_page');
      $events_page_id=eme_get_events_page_id();
      if ($events_page_id != $old_events_page_id) {
         eme_flushRules();
         update_option('eme_old_events_page',$events_page_id);
      }
   }
}

function eme_admin_tabs( $current = 'homepage' ) {
    $tabs = array( 'general' => __('General','events-made-easy'),
                   'access' => __('Access','events-made-easy'),
                   'seo' => __('SEO','events-made-easy'),
                   'events' => __('Events','events-made-easy'),
                   'locations' => __('Locations','events-made-easy'),
                   'calendar' => __('Calendar','events-made-easy'),
                   'rss' =>__('RSS','events-made-easy'),
                   'rsvp' =>__('RSVP','events-made-easy'),
                   'mail' =>__('Mail','events-made-easy'),
                   'mailtemplates' =>__('Mail templates','events-made-easy'),
                   'payments' =>__('Payments','events-made-easy'),
                   'maps' =>__('Maps','events-made-easy'),
                   'other' =>__('Other','events-made-easy')
                 );
    echo '<div id="icon-themes" class="icon32"><br /></div>';
    echo '<h1 class="nav-tab-wrapper">';
    $eme_options_url=admin_url("admin.php?page=eme-options");
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='$eme_options_url&tab=$tab'>$name</a>";
    }
    echo '</h1>';
}

// Function composing the options page
function eme_options_page() {
   global $plugin_page;
   if ($plugin_page == 'eme-options') {
      $tab = isset( $_GET['tab'] ) ? esc_attr($_GET['tab']) : 'general';
      eme_admin_tabs($tab);
   ?>
<div class="wrap">
<div id='icon-options-general' class='icon32'><br />
</div>
<h1><?php _e ( 'Event Manager Options', 'events-made-easy'); ?></h1>
<p> 
<?php printf(__( "Please also check <a href='%s'>your profile</a> for some per-user EME settings.", 'events-made-easy'),admin_url('profile.php')); ?>
</p>
<form id="eme_options_form" method="post" action="options.php">
<input type='hidden' name='tab' value='<?php echo $tab;?>' />
<?php
   settings_fields ( 'eme-options' );
   switch ( $tab ) {
	      case 'general' :
?>

<h3><?php _e ( 'General options', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Use dropdown for locations?' , 'events-made-easy'), 'eme_use_select_for_locations', __ ( 'Select yes to select location from a drop-down menu; location selection will be faster, but you will lose the ability to insert locations with events.','events-made-easy')."<br />".__ ( 'When the qtranslate plugin is installed and activated, this setting will be ignored and always considered \'Yes\'.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use recurrence?' , 'events-made-easy'), 'eme_recurrence_enabled', __ ( 'Select yes to enable the possibility to create recurrent events.','events-made-easy') ); 
   eme_options_radio_binary ( __ ( 'Use RSVP?' , 'events-made-easy'), 'eme_rsvp_enabled', __ ( 'Select yes to enable the RSVP feature so people can register for an event and book places.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use categories?' , 'events-made-easy'), 'eme_categories_enabled', __ ( 'Select yes to enable the category features.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use attributes?' , 'events-made-easy'), 'eme_attributes_enabled', __ ( 'Select yes to enable the attributes feature.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable Google Maps integration?' , 'events-made-easy'), 'eme_gmap_is_active', __ ( 'Check this option to enable Google Map integration.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Always include JS in header?' , 'events-made-easy'), 'eme_load_js_in_header', __ ( 'Some themes are badely designed and can have issues showing the google maps or advancing in the calendar. If so, try activating this option which will cause the javascript to always be included in the header of every page (off by default).','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use the client computer clock for the calendar', 'events-made-easy'), 'eme_use_client_clock', __ ( 'Check this option if you want to use the clock of the client as base to calculate current day for the calendar.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Clean session data upon login/logout', 'events-made-easy'), 'eme_clean_session_data', __ ( 'If you decide to use the client clock or the captcha in the RSVP form, EME uses sessions. As an extra security setting, you can use this setting to make sure the session is clean upon login/logout too. Since this might interfere with other plugins, it is disabled by default.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use captcha for forms?', 'events-made-easy'), 'eme_captcha_for_booking', __ ( 'Check this option if you want to use a captcha on the booking/cancel/membership forms, to thwart spammers a bit.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Captcha case insensitive?', 'events-made-easy'), 'eme_captcha_no_case', __ ( 'Sometimes entering a captcha with correct case can be a dounting task, especially on mobile devices. Use this option to make the captcha case insensitive.', 'events-made-easy') );
   eme_options_select ( __('Autocomplete sources','events-made-easy'), 'eme_autocomplete_sources', array ('none'=>__('None','events-made-easy'), 'people' => __('Signed up people','events-made-easy'), 'wp_users' => __ ( 'Wordpress users', 'events-made-easy'), 'both' => __('Both signed up people and WP users','events-made-easy')), __('When/where autocompletion is used for rsvp, select if you want to search signed up people, WP users or both.','events-made-easy') );
   eme_options_select ( __('Theme loop protection','events-made-easy'), 'eme_loop_protection', array ('simple' => __('Simple loop protection (default)','events-made-easy'), 'older' => __ ( 'Loop protection for older or misbehaving themes', 'events-made-easy'), 'desperate' => __('Last attempt at loop protection (if all else fails)','events-made-easy')), __('Choose the level of loop protection against the_content filter you want. Depending on the theme you may need to change this.','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Delete all stored EME data when upgrading or deactivating?', 'events-made-easy'), 'eme_uninstall_drop_data', __ ( 'Check this option if you want to delete all EME data concerning events, bookings, ... when upgrading or deactivating the plugin.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Delete all EME settings when upgrading or deactivating?', 'events-made-easy'), 'eme_uninstall_drop_settings', __ ( 'Check this option if you want to delete all EME settings when upgrading or deactivating the plugin.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable shortcodes in widgets', 'events-made-easy'), 'eme_shortcodes_in_widgets', __ ( 'Check this option if you want to enable the use of shortcodes in widgets (affects shortcodes of any plugin used in widgets, so use with care).', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable placeholders in event notes', 'events-made-easy'), 'eme_enable_notes_placeholders', __ ( 'Check this option if you want to enable the use of placeholders in the event notes. By default placeholders in notes are not being touched at all so as not to interfere with possible format settings for other shortcodes you can/want to use, so use with care.', 'events-made-easy') );
   ?>
</table>

<?php
	      break;
	      case 'seo' :
?>

<h3><?php _e ( 'Permalink options', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Enable event permalinks if possible?','events-made-easy'), 'eme_seo_permalink', __ ( 'If Yes, EME will render SEO permalinks if permalinks are activated.', 'events-made-easy') . "<br \><strong>" . __ ( 'It is necessary to click \'Save Changes\' on the  WordPress \'Settings/Permalinks\' page before you will see the effect of this change.','events-made-easy')."</strong>");
   eme_options_input_text ( __('Events permalink prefix', 'events-made-easy'), 'eme_permalink_events_prefix', __( 'The permalink prefix used for events and the calendar.','events-made-easy') );
   eme_options_input_text ( __('Locations permalink prefix', 'events-made-easy'), 'eme_permalink_locations_prefix', __( 'The permalink prefix used for locations.','events-made-easy') );
   ?>
</table>

<?php
	      break;
	      case 'access' :
?>

<h3><?php _e ( 'Access rights', 'events-made-easy'); ?></h3>
<p><?php _e ( 'Tip: Use a plugin like "User Role Editor" to add/edit capabilities and roles.', 'events-made-easy'); ?></p>
<table class="form-table">
   <?php
   eme_options_select (__('Add event','events-made-easy'), 'eme_cap_add_event', eme_get_all_caps (), sprintf(__('Permission needed to add a new event. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_ADD_EVENT)) );
   eme_options_select (__('Author event','events-made-easy'), 'eme_cap_author_event', eme_get_all_caps (), sprintf(__('Permission needed to edit own events. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_AUTHOR_EVENT)) );
   eme_options_select (__('Publish event','events-made-easy'), 'eme_cap_publish_event', eme_get_all_caps (), sprintf(__('Permission needed to make an event public. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_PUBLISH_EVENT)) );
   eme_options_select (__('List events','events-made-easy'), 'eme_cap_list_events', eme_get_all_caps (), sprintf(__('Permission needed to just list all events, useful for CSV exports for bookings and such. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_LIST_EVENTS)) . "<br /><b>". __('All your event admins need this as well, otherwise the menu will not show.','events-made-easy')."</b>" );
   eme_options_select (__('Edit events','events-made-easy'), 'eme_cap_edit_events', eme_get_all_caps (), sprintf(__('Permission needed to edit all events. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_EDIT_EVENTS)) );
   eme_options_select (__('Add location','events-made-easy'), 'eme_cap_add_locations', eme_get_all_caps (), sprintf(__('Permission needed to add locations. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_ADD_LOCATION)) );
   eme_options_select (__('Author location','events-made-easy'), 'eme_cap_author_locations', eme_get_all_caps (), sprintf(__('Permission needed to edit own locations. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_AUTHOR_LOCATION)) );
   eme_options_select (__('Edit location','events-made-easy'), 'eme_cap_edit_locations', eme_get_all_caps (), sprintf(__('Permission needed to edit all locations. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_EDIT_LOCATIONS)) );
   eme_options_select (__('Edit categories','events-made-easy'), 'eme_cap_categories', eme_get_all_caps (), sprintf(__('Permission needed to edit all categories. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_CATEGORIES)) );
   eme_options_select (__('Holidays','events-made-easy'), 'eme_cap_holidays', eme_get_all_caps (), sprintf(__('Permission needed to manage holidays. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_HOLIDAYS)) );
   eme_options_select (__('Edit templates','events-made-easy'), 'eme_cap_templates', eme_get_all_caps (), sprintf(__('Permission needed to edit all templates. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_TEMPLATES)) );
   eme_options_select (__('Manage disounts','events-made-easy'), 'eme_cap_discounts', eme_get_all_caps (), sprintf(__('Permission needed to manage discounts. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_DISCOUNTS)) );
   eme_options_select (__('View people','events-made-easy'), 'eme_cap_people', eme_get_all_caps (), sprintf(__('Permission needed to view registered people info. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_PEOPLE)) );
   eme_options_select (__('Membership management','events-made-easy'), 'eme_cap_members', eme_get_all_caps (), sprintf(__('Permission needed to manage members and memberships. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_MEMBERS)) );
   eme_options_select (__('Approve registrations','events-made-easy'), 'eme_cap_approve', eme_get_all_caps (), sprintf(__('Permission needed to approve pending registrations. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_APPROVE)) );
   eme_options_select (__('Edit registrations','events-made-easy'), 'eme_cap_registrations', eme_get_all_caps (), sprintf(__('Permission needed to edit approved registrations. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_REGISTRATIONS)) );
   eme_options_select (__('Send Mails','events-made-easy'), 'eme_cap_send_mails', eme_get_all_caps (), sprintf(__('Permission needed to send mails for own events. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_SEND_MAILS)) );
   eme_options_select (__('Send Other Mails','events-made-easy'), 'eme_cap_send_other_mails', eme_get_all_caps (), sprintf(__('Permission needed to send mails for any event. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_SEND_OTHER_MAILS)) );
   eme_options_select (__('Edit form fields','events-made-easy'), 'eme_cap_forms', eme_get_all_caps (), sprintf(__('Permission needed to edit form fields. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_FORMS)) );
   eme_options_select (__('Actions','events-made-easy'), 'eme_cap_cleanup', eme_get_all_caps (), sprintf(__('Permission needed to execute cleanup actions, manage cron settings and import actions. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_CLEANUP)) );
   eme_options_select (__('Edit settings','events-made-easy'), 'eme_cap_settings', eme_get_all_caps (),sprintf(__('Permission needed to edit settings. Default: %s','events-made-easy'), eme_capNamesCB(DEFAULT_CAP_SETTINGS)) );
   ?>
</table>

<?php
	      break;
	      case 'events' :
?>

<h3><?php _e ( 'Events page', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_select ( __ ( 'Events page', 'events-made-easy'), 'eme_events_page', eme_get_all_pages (), __ ( 'This option allows you to select which page to use as an events page.', 'events-made-easy')."<br /><strong>".__ ( 'The content of this page (including shortcodes of any kind) will be ignored completely and dynamically replaced by events data.','events-made-easy')."</strong>" );
   eme_options_radio_binary ( __ ( 'Display calendar in events page?', 'events-made-easy'), 'eme_display_calendar_in_events_page', __ ( 'This option allows to display the calendar in the events page, instead of the default list. It is recommended not to display both the calendar widget and a calendar page.','events-made-easy') );
   eme_options_input_text ( __('Number of events to show in lists', 'events-made-easy'), 'eme_event_list_number_items', __( 'The number of events to show in a list if no specific limit is specified (used in the shortcode eme_events, RSS feed, the placeholders #_NEXTEVENTS and #_PASTEVENTS, ...). Use 0 for no limit.','events-made-easy') );
   eme_options_select (__('State for new event','events-made-easy'), 'eme_event_initial_state', eme_status_array(), __ ('Initial state for a new event','events-made-easy') );
   ?>
</table>
<h3><?php _e ( 'Events format', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Remove leading zeros from minutes?', 'events-made-easy'), 'eme_time_remove_leading_zeros', __ ( 'PHP date/time functions have no notation to show minutes without leading zeros. Checking this option will return e.g. 9 for 09 and empty for 00.', 'events-made-easy') ); 
   eme_options_textarea ( __ ( 'Default event list format header', 'events-made-easy'), 'eme_event_list_item_format_header', sprintf(__('This content will appear just above your code for the default event list format. If you leave this empty, the value <code>%s</code> will be used.','events-made-easy'),eme_esc_html(DEFAULT_EVENT_LIST_HEADER_FORMAT)));
   eme_options_textarea ( __ ( 'Default categories event list format header', 'events-made-easy'), 'eme_cat_event_list_item_format_header', sprintf(__('This content will appear just above your code for the event list format when showing events for a specific category. If you leave this empty, the value <code>%s</code> will be used.','events-made-easy'),eme_esc_html(DEFAULT_CAT_EVENT_LIST_HEADER_FORMAT)));
   eme_options_textarea ( __ ( 'Default event list format', 'events-made-easy'), 'eme_event_list_item_format', __ ( 'The format of any events in a list.','events-made-easy') .'<br />'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Default event list format footer', 'events-made-easy'), 'eme_event_list_item_format_footer', sprintf(__('This content will appear just below your code for the default event list format. If you leave this empty, the value <code>%s</code> will be used.','events-made-easy'),eme_esc_html(DEFAULT_EVENT_LIST_FOOTER_FORMAT)));
   eme_options_textarea ( __ ( 'Default categories event list format footer', 'events-made-easy'), 'eme_cat_event_list_item_format_footer', sprintf(__('This content will appear just below your code for the default event list format when showing events for a specific category. If you leave this empty, the value <code>%s</code> will be used.','events-made-easy'),eme_esc_html(DEFAULT_CAT_EVENT_LIST_FOOTER_FORMAT)));
   eme_options_input_text ( __ ( 'Single event page title format', 'events-made-easy'), 'eme_event_page_title_format', __ ( 'The format of a single event page title. Follow the previous formatting instructions.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Single event html title format', 'events-made-easy'), 'eme_event_html_title_format', __ ( 'The format of a single event html page title. Follow the previous formatting instructions.', 'events-made-easy'). __( ' The default is: ','events-made-easy'). eme_esc_html(DEFAULT_EVENT_HTML_TITLE_FORMAT));
   eme_options_textarea ( __ ( 'Default single event format', 'events-made-easy'), 'eme_single_event_format', __ ( 'The format of a single event page.<br/>Follow the previous formatting instructions. <br/>Use <code>#_MAP</code> to insert a map.<br/>Use <code>#_CONTACTNAME</code>, <code>#_CONTACTEMAIL</code>, <code>#_CONTACTPHONE</code> to insert respectively the name, e-mail address and phone number of the designated contact person. <br/>Use <code>#_ADDBOOKINGFORM</code> to insert a form to allow the user to respond to your events reserving one or more places (RSVP).<br/> Use <code>#_REMOVEBOOKINGFORM</code> to insert a form where users, inserting their name and e-mail address, can remove their bookings.', 'events-made-easy').__('<br/>Use <code>#_ADDBOOKINGFORM_IF_NOT_REGISTERED</code> to insert the booking form only if the user has not registered yet. Similar use <code>#_REMOVEBOOKINGFORM_IF_REGISTERED</code> to insert the booking removal form only if the user has already registered before. These two codes only work for WP users.','events-made-easy').__('<br/> Use <code>#_DIRECTIONS</code> to insert a form so people can ask directions to the event.','events-made-easy').__('<br/> Use <code>#_CATEGORIES</code> to insert a comma-separated list of categories an event is in.','events-made-easy').__('<br/> Use <code>#_ATTENDEES</code> to get a list of the names attending the event.','events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_input_text ( __ ( 'Monthly period date format', 'events-made-easy'), 'eme_show_period_monthly_dateformat', __ ( 'The format of the date-string used when you use showperiod=monthly as an option to &#91;the eme_events] shortcode, also used for monthly pagination. Use php date() compatible settings.', 'events-made-easy') . __( ' The default is: ','events-made-easy'). DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Yearly period date format', 'events-made-easy'), 'eme_show_period_yearly_dateformat', __ ( 'The format of the date-string used when you use showperiod=yearly as an option to &#91;the eme_events] shortcode, also used for yearly pagination. Use php date() compatible settings.', 'events-made-easy') . __( ' The default is: ','events-made-easy'). DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Events page title', 'events-made-easy'), 'eme_events_page_title', __ ( 'The title on the multiple events page.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'No events message', 'events-made-easy'), 'eme_no_events_message', __ ( 'The message displayed when no events are available.', 'events-made-easy') );
   ?>
</table>
<h3><?php _e ( 'Events filtering format', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default event list filtering format', 'events-made-easy'), 'eme_filter_form_format', __ ( 'This defines the layout of the event list filtering form when using the shortcode <code>[eme_filterform]</code>. Use <code>#_FILTER_CATS</code>, <code>#_FILTER_LOCS</code>, <code>#_FILTER_TOWNS</code>, <code>#_FILTER_WEEKS</code>, <code>#_FILTER_MONTHS</code>.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=28'>".__('the documentation', 'events-made-easy').'</a>' );
   ?>
</table>

<?php
	      break;
	      case 'calendar' :
?>

<h3><?php _e ( 'Calendar options', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Hide past events?', 'events-made-easy'), 'eme_cal_hide_past_events', __ ( 'Check this option if you want to hide past events in the calendar.', 'events-made-easy') ); 
   eme_options_radio_binary ( __ ( 'Show single event?', 'events-made-easy'), 'eme_cal_show_single', __ ( 'Check this option if you want to immediately show the single event and not a list of events if there is only one event on a specific day.', 'events-made-easy') ); 
   ?>
</table>
<h3><?php _e ( 'Calendar format', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'Small calendar title', 'events-made-easy'), 'eme_small_calendar_event_title_format', __ ( 'The format of the title, corresponding to the text that appears when hovering on an eventful calendar day.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Small calendar title separator', 'events-made-easy'), 'eme_small_calendar_event_title_separator', __ ( 'The separator appearing on the above title when more than one event is taking place on the same day.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Full calendar events format', 'events-made-easy'), 'eme_full_calendar_event_format', __ ( 'The format of each event when displayed in the full calendar. Remember to include <code>li</code> tags before and after the event.', 'events-made-easy') );
   ?>
</table>

<?php
	      break;
	      case 'locations' :
?>

<h3><?php _e ( 'Locations format', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default location list format header', 'events-made-easy'), 'eme_location_list_format_header', sprintf(__( 'This content will appear just above your code for the default location list format. If you leave this empty, the value <code>%s</code> will be used.<br/>Used by the shortcode <code>[eme_locations]</code>', 'events-made-easy'),eme_esc_html(DEFAULT_LOCATION_LIST_HEADER_FORMAT)));
   eme_options_textarea ( __ ( 'Default location list item format', 'events-made-easy'), 'eme_location_list_format_item', sprintf(__ ( 'The format of a location in a location list. If you leave this empty, the value <code>%s</code> will be used.<br/>See the documentation for a list of available placeholders for locations.<br/>Used by the shortcode <code>[eme_locations]</code>', 'events-made-easy'),eme_esc_html(DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT)) .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Default location list format footer', 'events-made-easy'), 'eme_location_list_format_footer', sprintf(__( 'This content will appear just below your code for the default location list format. If you leave this empty, the value <code>%s</code> will be used.<br/>Used by the shortcode <code>[eme_locations]</code>', 'events-made-easy'),eme_esc_html(DEFAULT_LOCATION_LIST_FOOTER_FORMAT)));

   eme_options_input_text ( __ ( 'Single location page title format', 'events-made-easy'), 'eme_location_page_title_format', __ ( 'The format of a single location page title.<br/>Follow the previous formatting instructions.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Single location html title format', 'events-made-easy'), 'eme_location_html_title_format', __ ( 'The format of a single location html page title.<br/>Follow the previous formatting instructions.', 'events-made-easy'). __( ' The default is: ','events-made-easy'). DEFAULT_LOCATION_HTML_TITLE_FORMAT);
   eme_options_textarea ( __ ( 'Default single location page format', 'events-made-easy'), 'eme_single_location_format', __ ( 'The format of a single location page.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Default location balloon format', 'events-made-easy'), 'eme_location_baloon_format', __ ( 'The format of the text appearing in the balloon describing the location in the map.', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Default location event list format', 'events-made-easy'), 'eme_location_event_list_item_format', __ ( 'The format of the events list inserted in the location page through the <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> element. <br/> Follow the events formatting instructions', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Default no events message', 'events-made-easy'), 'eme_location_no_events_message', __ ( 'The message to be displayed in the list generated by <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> when no events are available.', 'events-made-easy') );
   ?>
</table>

<?php
	      break;
	      case 'rss' :
?>

<h3><?php _e ( 'RSS and ICAL feed format', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'RSS main title', 'events-made-easy'), 'eme_rss_main_title', __ ( 'The main title of your RSS events feed.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'RSS main description', 'events-made-easy'), 'eme_rss_main_description', __ ( 'The main description of your RSS events feed.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'RSS title format', 'events-made-easy'), 'eme_rss_title_format', __ ( 'The format of the title of each item in the events RSS feed.', 'events-made-easy') );
   eme_options_textarea ( __ ( 'RSS description format', 'events-made-easy'), 'eme_rss_description_format', __ ( 'The format of the description of each item in the events RSS feed. Follow the previous formatting instructions.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'RSS Pubdate usage', 'events-made-easy'), 'eme_rss_show_pubdate', __ ( 'Show the event creation/modification date as PubDate info in the in the events RSS feed.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'RSS Pubdate is start date', 'events-made-easy'), 'eme_rss_pubdate_startdate', __ ( 'If you select this, the pubDate field in RSS will be the event start date, not the modification date.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'ICAL title format', 'events-made-easy'), 'eme_ical_title_format', __ ( 'The format of the title of each item in the events ICAL feed.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'ICAL description format', 'events-made-easy'), 'eme_ical_description_format', __ ( 'The format of the description of each item in the events ICAL feed. Follow the previous formatting instructions.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'ICAL location format', 'events-made-easy'), 'eme_ical_location_format', __ ( 'The format of the location of each item in the events ICAL feed (if a location is defined for that event). Use any regular location placeholders.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Quote ICAL timezone', 'events-made-easy'), 'eme_ical_quote_tzid', __ ( 'Some ical clients need the timezone info in the ICAL output to be quoted despite the RFC (i.e. Outlook), so activate this if your ICAL client requires it.', 'events-made-easy') );
   ?>
</table>

<?php
	      break;
	      case 'rsvp' :
?>

<h3><?php _e ( 'RSVP: registrations and bookings', 'events-made-easy'); ?></h3>
<table class='form-table'>
     <?php
   $indexed_users[-1]=__('Event author','events-made-easy');
   $indexed_users+=eme_get_indexed_users();
   eme_options_select ( __ ( 'Default contact person', 'events-made-easy'), 'eme_default_contact_person', $indexed_users, __ ( 'Select the default contact person. This user will be employed whenever a contact person is not explicitly specified for an event', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'By default enable registrations for new events?', 'events-made-easy'), 'eme_rsvp_reg_for_new_events', __ ( 'Check this option if you want to enable registrations by default for new events.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'By default require approval for registrations?', 'events-made-easy'), 'eme_rsvp_require_approval', __ ( 'Check this option if you want by default that new registrations require approval.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'By default require WP membership to be able to register?', 'events-made-easy'), 'eme_rsvp_registered_users_only', __ ( 'Check this option if you want by default that only WP registered users can book for an event.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Check required fields upon submit?', 'events-made-easy'), 'eme_rsvp_check_required_fields', __ ( 'Check this option if you want to check on the server-side if all required fields have been completed upon RSVP form submit. Consider using a captcha if disabling this.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Default number of spaces', 'events-made-easy'), 'eme_rsvp_default_number_spaces', __ ( 'The default number of spaces an event has.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Min number of spaces to book', 'events-made-easy'), 'eme_rsvp_addbooking_min_spaces', __ ( 'The minimum number of spaces a person can book in one go (it can be 0, for e.g. just an attendee list).', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Max number of spaces to book', 'events-made-easy'), 'eme_rsvp_addbooking_max_spaces', __ ( 'The maximum number of spaces a person can book in one go.', 'events-made-easy') );
   $eme_rsvp_number_days=get_option('eme_rsvp_number_days');
   $eme_rsvp_number_hours=get_option('eme_rsvp_number_hours');
   $eme_rsvp_end_target=get_option('eme_rsvp_end_target');
   ?>
   <tr valign="top" id='eme_rsvp_number_row'>
      <th scope="row"><?php _e('By default allow RSVP until this many', 'events-made-easy') ?></th>
      <td>
      <input name="eme_rsvp_number_days" type="text" id="eme_rsvp_number_days" value="<?php echo eme_esc_html($eme_rsvp_number_days); ?>" size="4" /> <?php _e('days', 'events-made-easy') ?>
      <input name="eme_rsvp_number_hours" type="text" id="eme_rsvp_number_hours" value="<?php echo eme_esc_html($eme_rsvp_number_hours); ?>" size="4" /> <?php _e('hours', 'events-made-easy') ?>
      <?php
      $eme_rsvp_end_target_list = array('start'=>__('starts','events-made-easy'),'end'=>__('ends','events-made-easy'));
      _e ( 'before the event ','events-made-easy');
      echo eme_ui_select($eme_rsvp_end_target,'eme_rsvp_end_target',$eme_rsvp_end_target_list);
      ?>
      </td>
   </tr>
   <?php
   eme_options_input_text ( __ ( 'RSVP cancel cutoff', 'events-made-easy'), 'eme_cancel_rsvp_days', __ ( 'Allow RSVP cancellation until this many days before the event starts.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Hide fully booked events?', 'events-made-easy'), 'eme_rsvp_hide_full_events', __ ( 'Check this option if you want to hide events that are fully booked from the calendar and events listing in the front.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Hide RSVP ended events?', 'events-made-easy'), 'eme_rsvp_hide_rsvp_ended_events', __ ( 'Check this option if you want to hide events which RSVP registration period has already ended.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Add booking form submit text', 'events-made-easy'), 'eme_rsvp_addbooking_submit_string', __ ( "The string of the submit button on the add booking form", 'events-made-easy') );
   eme_options_input_text ( __ ( 'Delete booking form submit text', 'events-made-easy'), 'eme_rsvp_delbooking_submit_string', __ ( "The string of the submit button on the delete booking form", 'events-made-easy') );
   eme_options_input_text ( __ ( 'Required field text', 'events-made-easy'), 'eme_rsvp_required_field_string', __ ( "The text shown next to a form field when it is a required field", 'events-made-easy') );
   eme_options_input_text ( __ ( 'RSVP no longer allowed text', 'events-made-easy'), 'eme_rsvp_no_longer_allowed_string', __ ( "The text shown on the RSVP form when the deadline for RSVP for an event has passed.", 'events-made-easy') );
   eme_options_input_text ( __ ( 'Cancel no longer allowed text', 'events-made-easy'), 'eme_rsvp_cancel_no_longer_allowed_string', __ ( "The text shown on the cancel form when the deadline for RSVP for an event has passed.", 'events-made-easy') );
   eme_options_input_text ( __ ( 'Attendees list format', 'events-made-easy'), 'eme_attendees_list_format', __ ( "The format for the attendees list when using the <code>#_ATTENDEES</code> placeholder.", 'events-made-easy'). __('For all placeholders you can use here, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=48'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_radio_binary ( __ ( 'Attendees list ignore pending', 'events-made-easy'), 'eme_attendees_list_ignore_pending', __ ( "Whether or not to ignore pending bookings when using the <code>#_ATTENDEES</code> placeholder.", 'events-made-easy'));
   eme_options_input_text ( __ ( 'Bookings list header format', 'events-made-easy'), 'eme_bookings_list_header_format', __ ( "The header format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'events-made-easy'). sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_BOOKINGS_LIST_HEADER_FORMAT)));
   eme_options_input_text ( __ ( 'Bookings list format', 'events-made-easy'), 'eme_bookings_list_format', __ ( "The format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'events-made-easy'). __('For all placeholders you can use here, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=45'>".__('the documentation', 'events-made-easy').'</a>' .__('For more information about form fields, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_input_text ( __ ( 'Bookings list footer format', 'events-made-easy'), 'eme_bookings_list_footer_format', __ ( "The footer format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'events-made-easy'). sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT)));
   eme_options_radio_binary ( __ ( 'Bookings list ignore pending', 'events-made-easy'), 'eme_bookings_list_ignore_pending', __ ( "Whether or not to ignore pending bookings when using the <code>#_BOOKINGS</code> placeholder.", 'events-made-easy'));
   eme_options_textarea ( __ ( 'Booking recorded message', 'events-made-easy'), 'eme_registration_recorded_ok_html', __ ( "The text (html allowed) shown to the user when the booking has been made successfully.", 'events-made-easy'), 1 );
   eme_options_radio_binary ( __ ( 'Show RSVP form again after booking?', 'events-made-easy'), 'eme_rsvp_show_form_after_booking', __ ( "Uncheck this option if you don't want to show the RSVP booking form again after a successful booking.", 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Allow overbooking?', 'events-made-easy'), 'eme_rsvp_admin_allow_overbooking', __ ( "Check this option if you want to allow overbookings when adding/editing an booking in the admin interface.", 'events-made-easy') );
   ?>
</table>

<h3><?php _e ( 'RSVP: form format', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_textarea (__('Form format','events-made-easy'),'eme_registration_form_format', __("The look and feel of the form for registrations. #_NAME, #_EMAIL and #_SEATS are obligated fields, if not present then the form will not be shown.",'events-made-easy')  .'<br/>'.__('For more information about form fields, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'events-made-easy').'</a>', 1);
      eme_options_textarea (__('Cancel form format','events-made-easy'),'eme_cancel_form_format', __("The look and feel of the cancel form for registrations. #_NAME and #_EMAIL are obligated fields, if not present then the form will not be shown.", 'events-made-easy').'<br/>'.__('For more information about form fields, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=5950'>".__('the documentation', 'events-made-easy').'</a>', 1);
   ?>
</table>


<?php
	      break;
	      case 'mail' :
?>

<h3><?php _e ( 'Mail options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Enable the RSVP e-mail notifications?', 'events-made-easy'), 'eme_rsvp_mail_notify_is_active', __ ( 'Check this option if you want to receive an email when someone books places for your events.', 'events-made-easy') );
   ?>
</table>
<table id="rsvp_mail_notify-data" class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Enable pending RSVP e-mails?', 'events-made-easy'), 'eme_rsvp_mail_notify_pending', __ ( 'Check this option if you want to send mails for pending registrations.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable approved RSVP e-mails?', 'events-made-easy'), 'eme_rsvp_mail_notify_approved', __ ( 'Check this option if you want to send mails for approved registrations.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable paid RSVP e-mails?', 'events-made-easy'), 'eme_rsvp_mail_notify_paid', __ ( 'Check this option if you want to send mails when a payment arrives via a payment gateway or when a booking is marked as paid.', 'events-made-easy') );
   ?>
</table>
<table class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Send HTML mails', 'events-made-easy'), 'eme_rsvp_send_html', __ ( 'Check this option if you want to use html in the mails being sent.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Queue mails for later?', 'events-made-easy'), 'eme_queue_mails', sprintf(__ ( 'If activated, actions that result in a number of mails that surpass a certain limit, will be queued for later sending. Go in the <a href="%s">CRON submenu</a> to configure the limit and the queue interval.', 'events-made-easy'), admin_url("admin.php?page=eme-cron") ));
   eme_options_radio_binary ( __ ( 'MassMail', 'events-made-easy'), 'eme_people_massmail', __("Should new persons in the database be considered for massmailing or not? This setting is used if you don't ask for opt-in/out info in e.g. the rsvp form",'events-made-easy'));
   eme_options_input_text ( __ ( 'Notification sender name', 'events-made-easy'), 'eme_mail_sender_name', __ ( "Insert the display name of the notification sender.", 'events-made-easy') );
   eme_options_input_text ( __ ( 'Notification sender address', 'events-made-easy'), 'eme_mail_sender_address', __ ( "Insert the address of the notification sender. It must correspond with your Gmail account user if you use Gmail to send mails.", 'events-made-easy'), "email" );
   eme_options_input_text ( __ ( 'Notification BCC address', 'events-made-easy'), 'eme_mail_bcc_address', __ ( "Insert an address that will be added in Bcc to all outgoing mails. Can be left empty.", 'events-made-easy'), "email" );
   eme_options_select ( __ ( 'Mail sending method', 'events-made-easy'), 'eme_rsvp_mail_send_method', array ('smtp' => 'SMTP', 'mail' => __ ( 'PHP mail function', 'events-made-easy'), 'sendmail' => 'Sendmail', 'qmail' => 'Qmail', 'wp_mail' => 'WP Mail' ), __ ( 'Select the method to send email notification.', 'events-made-easy') );
   eme_options_input_text (__( 'SMTP host','events-made-easy'), 'eme_smtp_host', __ ( "The SMTP host. Usually it corresponds to 'localhost'.", 'events-made-easy') );
   eme_options_input_text ( __('Mail sending port','events-made-easy'), 'eme_smtp_port', __ ( "The port through which you e-mail notifications will be sent. Make sure the firewall doesn't block this port", 'events-made-easy') );
   eme_options_select ( __ ( 'SMTP encryption method', 'events-made-easy'), 'eme_smtp_encryption', array ('none' => __('None','events-made-easy'), 'tls' => __ ( 'TLS', 'events-made-easy'), 'ssl' => __ ( 'SSL', 'events-made-easy') ), __ ( 'Select the SMTP encryption method.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use SMTP authentication?', 'events-made-easy'), 'eme_rsvp_mail_SMTPAuth', __ ( 'SMTP authentication is often needed. If you use Gmail, make sure to set this parameter to Yes', 'events-made-easy') );
   eme_options_input_text ( __ ( 'SMTP username', 'events-made-easy'), 'eme_smtp_username', __ ( "Insert the username to be used to access your SMTP server.", 'events-made-easy') );
   eme_options_input_password ( __ ( 'SMTP password', 'events-made-easy'), 'eme_smtp_password', __ ( "Insert the password to be used to access your SMTP server", 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Verify SMTP certificates?', 'events-made-easy'), 'eme_smtp_verify_cert', __ ( 'Uncheck this option if you have issues sending mail via secure SMTP due to mismatching certificates. Since this in fact defeats the purpose of having certificates, it is not recommended to use it, but sometimes it is needed at specific hosting providers.', 'events-made-easy') );
   eme_options_radio_binary ( __ ( 'Debug SMTP?', 'events-made-easy'), 'eme_smtp_debug', __ ( 'Check this option if you have issues sending mail via SMTP. Only do this for debugging purposes and deactivate it afterwards!', 'events-made-easy') );
   echo "<tr><th colspan='2'>".__("Hint: after you changed your mail settings, go to the 'Send Mails' submenu to send a test mail.",'events-made-easy').'</td></tr>';
   ?>
</table>
<?php
	      break;
	      case 'mailtemplates' :
?>

<h3><?php _e ( 'Mail format templates', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
   if (get_option('eme_rsvp_send_html') == '1') {
      $use_html_editor=1;
      $use_full=1;
   } else {
      $use_html_editor=0;
      $use_full=0;
   }
   eme_options_input_text ( __ ( 'Contact person email subject format', 'events-made-easy'), 'eme_contactperson_email_subject', __ ( 'The format of the email subject which will be sent to the contact person.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Contact person email format', 'events-made-easy'), 'eme_contactperson_email_body', __ ( 'The format of the email which will be sent to the contact person.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Contact person cancelled email subject format', 'events-made-easy'), 'eme_contactperson_cancelled_email_subject', __ ( 'The format of the email subject which will be sent to the contact person for a cancellation.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Contact person cancelled email format', 'events-made-easy'), 'eme_contactperson_cancelled_email_body', __ ( 'The format of the email which will be sent to the contact person for a cancellation.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Contact person pending email subject format', 'events-made-easy'), 'eme_contactperson_pending_email_subject', __ ( 'The format of the email subject which will be sent to the contact person if approval is needed.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Contact person pending email format', 'events-made-easy'), 'eme_contactperson_pending_email_body', __ ( 'The format of the email which will be sent to the contact person if approval is needed.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Contact person payment notification subject format', 'events-made-easy'), 'eme_contactperson_ipn_email_subject', __ ( 'The format of the email subject which will be sent to the contact person when a payment notification is received.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Contact person payment notification email format', 'events-made-easy'), 'eme_contactperson_ipn_email_body', __ ( 'The format of the email which will be sent to the contact person when a payment notification is received.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Respondent email subject format', 'events-made-easy'), 'eme_respondent_email_subject', __ ( 'The format of the email subject which will be sent to the respondent.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Respondent email format', 'events-made-easy'), 'eme_respondent_email_body', __ ( 'The format of the email which will be sent to the respondent.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Registration pending email subject format', 'events-made-easy'), 'eme_registration_pending_email_subject', __ ( 'The format of the email subject which will be sent to the respondent when the event requires registration approval.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Registration pending email format', 'events-made-easy'), 'eme_registration_pending_email_body', __ ( 'The format of the email which will be sent to the respondent when the event requires registration approval.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Registration cancelled email subject format', 'events-made-easy'), 'eme_registration_cancelled_email_subject', __ ( 'The format of the email subject which will be sent to the respondent when the respondent cancels the registrations for an event.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Registration cancelled email format', 'events-made-easy'), 'eme_registration_cancelled_email_body', __ ( 'The format of the email which will be sent to the respondent when the respondent cancels the registrations for an event.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Registration denied email subject format', 'events-made-easy'), 'eme_registration_denied_email_subject', __ ( 'The format of the email subject which will be sent to the respondent when the admin denies the registration request if the event requires registration approval.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Registration denied email format', 'events-made-easy'), 'eme_registration_denied_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin denies the registration request if the event requires registration approval.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Registration updated email subject format', 'events-made-easy'), 'eme_registration_updated_email_subject', __ ( 'The format of the email subject which will be sent to the respondent when the admin updates the registration request.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Registration updated email format', 'events-made-easy'), 'eme_registration_updated_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin updates the registration request.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Registration paid email subject format', 'events-made-easy'), 'eme_registration_paid_email_subject', __ ( 'The format of the email subject which will be sent to the respondent when payment has been received for the registration request.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>' );
   eme_options_textarea ( __ ( 'Registration paid email format', 'events-made-easy'), 'eme_registration_paid_email_body', __ ( 'The format of the email which will be sent to the respondent when payment has been received for the registration request.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>', $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Unsubscribe email subject', 'events-made-easy'), 'eme_unsub_subject', __ ( 'The subject of the email which will be sent to the person asking to unsubscribe.', 'events-made-easy') .'<br/>'.__('No placeholders can be used.', 'events-made-easy'));
   eme_options_textarea ( __ ( 'Unsubscribe email body', 'events-made-easy'), 'eme_unsub_body', __ ( 'The body of the email which will be sent to the person asking to unsubscribe.', 'events-made-easy') .'<br/>'.__('Can only contain 1 placeholder, namely #_UNSUB_CONFIRM_URL.', 'events-made-easy'), $use_html_editor, $use_full );
   eme_options_input_text ( __ ( 'Personal info email subject', 'events-made-easy'), 'eme_gdpr_subject', __ ( 'The subject of the email which will be sent to the person asking for his personal info.', 'events-made-easy') .'<br/>'.__('No placeholders can be used.', 'events-made-easy'));
   eme_options_textarea ( __ ( 'Personal info email body', 'events-made-easy'), 'eme_gdpr_body', __ ( 'The body of the email which will be sent to the person asking for his personal info.', 'events-made-easy') .'<br/>'.__('Can only contain 1 placeholder, namely #_GDPR_URL.', 'events-made-easy'), $use_html_editor, $use_full );
   ?>
</table>

<?php
	      break;
	      case 'payments' :
            $events_page_link = eme_get_events_page();
?>

<h3><?php _e ( 'RSVP: price options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
   eme_options_select ( __ ( 'Default currency', 'events-made-easy'), 'eme_default_currency', eme_currency_array(), __ ( 'Select the default currency for payments.', 'events-made-easy') );
   eme_options_input_text ( __ ( 'Default price', 'events-made-easy'), 'eme_default_price', __ ( 'The default price for an event.', 'events-made-easy') );
   eme_options_textarea ( __ ( 'RSVP Payment form header format', 'events-made-easy'), 'eme_payment_form_header_format', __ ( 'The format of the text shown above the payment buttons. If left empty, a default message will be shown.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>',1 );
   eme_options_textarea ( __ ( 'RSVP Payment form footer format', 'events-made-easy'), 'eme_payment_form_footer_format', __ ( 'The format of the text shown below the payment buttons. Default: empty.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'events-made-easy').'</a>',1 );
   eme_options_textarea ( __ ( 'Multibooking payment form header format', 'events-made-easy'), 'eme_multipayment_form_header_format', __ ( 'The format of the text shown above the payment buttons in the multibooking form. If left empty, a default message will be shown.', 'events-made-easy').'<br/>'.__('Although the same placeholders as for the regular payment form header format can be used, it is advised to only use multibooking related placeholders.', 'events-made-easy'),1 );
   eme_options_textarea ( __ ( 'Multibooking payment form footer format', 'events-made-easy'), 'eme_multipayment_form_footer_format', __ ( 'The format of the text shown below the payment buttons in the multibooking form. Default: empty.', 'events-made-easy').'<br/>'.__('Although the same placeholders as for the regular payment form header format can be used, it is advised to only use multibooking related placeholders.', 'events-made-easy'),1 );
   eme_options_radio_binary ( __ ( 'Show custom payment return page', 'events-made-easy'), 'eme_payment_show_custom_return_page', __ ( 'Check this option if you want to define a custom page format for the sucess or failure of the payment.', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Payment RSVP succes return page format', 'events-made-easy'), 'eme_payment_succes_format', __ ( 'The format of the return page when the payment is succesfull for RSVP bookings.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>',1 );
   eme_options_textarea ( __ ( 'Payment RSVP failure return page format', 'events-made-easy'), 'eme_payment_fail_format', __ ( 'The format of the return page when the payment failed or has been canceled for RSVP bookings.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>',1 );
   eme_options_textarea ( __ ( 'Payment membership succes return page format', 'events-made-easy'), 'eme_payment_member_succes_format', __ ( 'The format of the return page when the payment is succesfull for membership signup.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>',1 );
   eme_options_textarea ( __ ( 'Payment membership failure return page format', 'events-made-easy'), 'eme_payment_member_fail_format', __ ( 'The format of the return page when the payment failed or has been canceled for membership signup.', 'events-made-easy') .'<br/>'.__('For all possible placeholders, see ', 'events-made-easy')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'events-made-easy').'</a>',1 );
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: offline payment info', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_textarea (__('Offline payment info','events-made-easy'),'eme_offline_payment', __("The text containing all info for offline payment. Can contain HTML and placeholders like the payment header/footer settings.",'events-made-easy'),1);
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: paypal options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      $notification_link = add_query_arg(array('eme_eventAction'=>'paypal_notification'),$events_page_link);
      eme_options_select ( __('PayPal live or test','events-made-easy'), 'eme_paypal_url', array (PAYPAL_SANDBOX_URL => __('Paypal Sandbox (for testing)','events-made-easy'), PAYPAL_LIVE_URL => __ ( 'Paypal Live', 'events-made-easy')), __('Choose wether you want to test paypal in a paypal sandbox or go live and really use paypal.','events-made-easy') );
      eme_options_input_text (__('PayPal business info','events-made-easy'),'eme_paypal_business', __("Paypal business ID or email.",'events-made-easy'));
      eme_options_radio_binary ( __ ( 'Use paypal encryption?','events-made-easy'), 'eme_paypal_s_encrypt', __ ( 'Select yes to encrypt the paypal button using certificates.','events-made-easy') );
      eme_options_radio_binary ( __ ( 'Ignore paypal tax setting?','events-made-easy'), 'eme_paypal_no_tax', __ ( 'Select yes to ignore the tax setting in your paypal profile.','events-made-easy') );
      eme_options_input_text (__('Paypal public cert','events-made-easy'),'eme_paypal_s_paypalcert', __("Path to paypal public certificate file.",'events-made-easy'));
      eme_options_input_text (__('Own public cert','events-made-easy'),'eme_paypal_s_pubcert', __("Path to own public certificate file.",'events-made-easy'));
      eme_options_input_text (__('Own private key','events-made-easy'),'eme_paypal_s_privkey', __("Path to own private key file.",'events-made-easy'));
      eme_options_input_text (__('Certificate ID','events-made-easy'),'eme_paypal_s_certid', __("Certificate ID of your cert at paypal.",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_paypal_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_paypal_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_paypal_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_paypal_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_paypal_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_paypal_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: the url for payment notifications is: ','events-made-easy').$notification_link.'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: 2Checkout options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      $notification_link = add_query_arg(array('eme_eventAction'=>'2co_notification'),$events_page_link);

      eme_options_select ( __('2Checkout live or test','events-made-easy'), 'eme_2co_demo', array (2 => __('2Checkout Sandbox (for testing)','events-made-easy'), 1 => __('2Checkout Test (the "demo" mode)','events-made-easy'), 0 => __ ( '2Checkout Live', 'events-made-easy')), __('Choose wether you want to test 2Checkout in a sandbox or go live and really use 2Checkout.','events-made-easy') );
      eme_options_input_text (__('2Checkout Account number','events-made-easy'),'eme_2co_business', __("2Checkout Account number.",'events-made-easy'));
      eme_options_input_password (__('2Checkout Secret','events-made-easy'),'eme_2co_secret', __("2Checkout secret.",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_2co_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_2co_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_2co_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_2co_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_2co_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_2co_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: the url for payment notifications is: ','events-made-easy').$notification_link.'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Webmoney options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      $notification_link = add_query_arg(array('eme_eventAction'=>'webmoney_notification'),$events_page_link);

      eme_options_select ( __('Webmoney live or test','events-made-easy'), 'eme_webmoney_demo', array (1 => __('Webmoney Sandbox (for testing)','events-made-easy'), 0 => __ ( 'Webmoney Live', 'events-made-easy')), __('Choose wether you want to test Webmoney in a sandbox or go live and really use Webmoney.','events-made-easy') );
      eme_options_input_text (__('Webmoney Purse','events-made-easy'),'eme_webmoney_purse', __("Webmoney Purse.",'events-made-easy'));
      eme_options_input_password (__('Webmoney Secret','events-made-easy'),'eme_webmoney_secret', __("Webmoney secret.",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_webmoney_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_webmoney_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_webmoney_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_webmoney_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_webmoney_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_webmoney_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: the url for payment notifications is: ','events-made-easy').$notification_link.'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: First Data options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      $notification_link = add_query_arg(array('eme_eventAction'=>'fdgg_notification'),$events_page_link);

      eme_options_select ( __('First Data live or test','events-made-easy'), 'eme_fdgg_url', array (FDGG_SANDBOX_URL => __('First Data Sandbox (for testing)','events-made-easy'), FDGG_LIVE_URL => __ ( 'First Data Live', 'events-made-easy')), __('Choose wether you want to test First Data in a sandbox or go live and really use First Datal.','events-made-easy') );
      eme_options_input_text (__('First Data Store Name','events-made-easy'),'eme_fdgg_store_name', __("First Data Store Name.",'events-made-easy'));
      eme_options_input_password (__('First Data Shared Secret','events-made-easy'),'eme_fdgg_shared_secret', __("First Data Shared Secret.",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_fdgg_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_fdgg_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_fdgg_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_fdgg_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_fdgg_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_fdgg_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: the url for payment notifications is: ','events-made-easy').$notification_link.'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Mollie options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      $notification_link = add_query_arg(array('eme_eventAction'=>'mollie_notification'),$events_page_link);

      eme_options_input_text (__('Mollie API key','events-made-easy'),'eme_mollie_api_key', __('Mollie API key','events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_mollie_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_mollie_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_mollie_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_mollie_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_mollie_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_mollie_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: the url for payment notifications is: ','events-made-easy').$notification_link.'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Sage Pay options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('Sage Pay live or test','events-made-easy'), 'eme_sagepay_demo', array (1 => __('Sage Pay Sandbox (for testing)','events-made-easy'), 0 => __ ( 'Sage Pay Live', 'events-made-easy')), __('Choose wether you want to test Sage Pay in a sandbox or go live and really use Sage Pay.','events-made-easy') );
      eme_options_input_text (__('Sage Pay Vendor Name','events-made-easy'),'eme_sagepay_vendor_name', __("Sage Pay Vendor Name",'events-made-easy'));
      eme_options_input_password (__('Sage Pay Test Password','events-made-easy'),'eme_sagepay_test_pwd', __("Sage Pay password for testing purposes",'events-made-easy'));
      eme_options_input_password (__('Sage Pay Live Password','events-made-easy'),'eme_sagepay_live_pwd', __("Sage Pay password when using Sage Pay for real",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_sagepay_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_sagepay_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_sagepay_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_sagepay_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_sagepay_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_sagepay_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      echo "<tr><td colspan='2'>".__('Info: for Sage Pay to work, your PHP installation must have the mcrypt module installed and activated. Search the internet for which extra PHP package to install and/or which line in php.ini to change.','events-made-easy').'</td></tr>';
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Worldpay options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('Worldpay live or test','events-made-easy'), 'eme_worldpay_demo', array (1 => __('Worldpay Sandbox (for testing)','events-made-easy'), 0 => __ ( 'Worldpay Live', 'events-made-easy')), __('Choose wether you want to test Worldpay in a sandbox or go live and really use Worldpay.','events-made-easy') );
      eme_options_input_text (__('Worldpay installation ID','events-made-easy'),'eme_worldpay_instid', __("Worldpay installation ID",'events-made-easy'));
      eme_options_input_text (__('Worldpay MD5 secret','events-made-easy'),'eme_worldpay_md5_secret', __("Worldpay MD5 secret used when submitting payments",'events-made-easy'));
      eme_options_input_text (__('Worldpay MD5 parameters','events-made-easy'),'eme_worldpay_md5_parameters', __("Worldpay parameters used to generate the MD5 signature, separated by ':'. Only use these 4 in the order of your choice: instId,cartId,currency and/or amount",'events-made-easy'));
      eme_options_input_password (__('Worldpay Test Password','events-made-easy'),'eme_worldpay_test_pwd', __("Worldpay password for payment notifications when testing",'events-made-easy'));
      eme_options_input_password (__('Worldpay Live Password','events-made-easy'),'eme_worldpay_live_pwd', __("Worldpay password for payment notifications when using Worldpay for real",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_worldpay_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_worldpay_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_worldpay_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_worldpay_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_worldpay_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_worldpay_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Stripe options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_input_password (__('Stripe Secret Key','events-made-easy'),'eme_stripe_private_key', __("Stripe Secret Key",'events-made-easy'));
      eme_options_input_password (__('Stripe Public Key','events-made-easy'),'eme_stripe_public_key', __("Stripe Public Key",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_stripe_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_stripe_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_stripe_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_stripe_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_stripe_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_stripe_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
   ?>
</table>
<hr />

<h3><?php _e ( 'RSVP: Braintree options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('Braintree live or test','events-made-easy'), 'eme_braintree_env', array ('sandbox' => __('Braintree Sandbox (for testing)','events-made-easy'), 'production' => __ ( 'Braintree Live', 'events-made-easy')), __('Choose wether you want to test Braintree in a sandbox or go live and really use Braintree.','events-made-easy') );
      eme_options_input_password (__('Braintree Merchant ID','events-made-easy'),'eme_braintree_merchant_id', __("Braintree Merchant ID",'events-made-easy'));
      eme_options_input_password (__('Braintree Private Key','events-made-easy'),'eme_braintree_private_key', __("Braintree Private Key",'events-made-easy'));
      eme_options_input_password (__('Braintree Public Key','events-made-easy'),'eme_braintree_public_key', __("Braintree Public Key",'events-made-easy'));
      eme_options_input_text (__('Extra charge','events-made-easy'),'eme_braintree_cost', __("Extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Extra charge 2','events-made-easy'),'eme_braintree_cost2', __("Second extra charge added when paying for an event. Can either be an absolute number or a percentage. E.g. 2 or 5%",'events-made-easy'));
      eme_options_input_text (__('Payment button label','events-made-easy'),'eme_braintree_button_label', __('The text shown inside the payment button','events-made-easy'));
      eme_options_input_text (__('Payment button image','events-made-easy'),'eme_braintree_button_img_url', __('The url to an image for the payment button that replaces the standard submit button with the label mentioned above.','events-made-easy'));
      eme_options_input_text (__('Text above payment button','events-made-easy'),'eme_braintree_button_above', __('The text shown just above the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
      eme_options_input_text (__('Text below payment button','events-made-easy'),'eme_braintree_button_below', __('The text shown just below the payment button, you can use #_EXTRACHARGE and #_CURRENCY to indicate the extra charge calculated if wanted','events-made-easy'));
   ?>
</table>

<?php
	      break;
	      case 'maps' :
?>
<h3><?php _e ( 'Map options', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __('Google Maps API Key', 'events-made-easy'), 'eme_gmap_api_key', __( 'Your Google Maps API key. See <a href=https://developers.google.com/maps/documentation/javascript/get-api-key>Get Google Maps API Key</a> for more info.','events-made-easy').'<br />'.__( 'Remark: without this API key, Google Maps will not function! ','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Enable map scroll-wheel zooming?' , 'events-made-easy'), 'eme_gmap_zooming', __ ( 'Yes, enables map scroll-wheel zooming. No, enables scroll-wheel page scrolling over maps. (It will be necessary to refresh your web browser on a map page to see the effect of this change.)', 'events-made-easy') );
   eme_options_input_text ( __('Global map zoom factor','events-made-easy'), 'eme_global_zoom_factor', __('The zoom factor used for the global map (max: 14).','events-made-easy').sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_GLOBAL_ZOOM_FACTOR)) );
   eme_options_input_text ( __('Individual map zoom factor','events-made-easy'), 'eme_indiv_zoom_factor', __('The zoom factor used when showing a single map (max: 14).','events-made-easy').sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_INDIV_ZOOM_FACTOR))  );
   eme_options_select ( __('Global map type','events-made-easy'), 'eme_global_maptype', array('ROADMAP' => __('Road map view','events-made-easy'),'SATELLITE' => __('Google Earth satellite images','events-made-easy'),'HYBRID' => __('Hybrid: a mixture of normal and satellite views','events-made-easy'), 'TERRAIN' => __('Terrain: a physical map based on terrain information', 'events-made-easy')), __('The map type used for the global map (max: 14).','events-made-easy').sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_GLOBAL_MAPTYPE)) );
   eme_options_select ( __('Individual map type','events-made-easy'), 'eme_indiv_maptype', array('ROADMAP' => __('Road map view','events-made-easy'),'SATELLITE' => __('Google Earth satellite images','events-made-easy'),'HYBRID' => __('Hybrid: a mixture of normal and satellite views','events-made-easy'), 'TERRAIN' => __('Terrain: a physical map based on terrain information', 'events-made-easy')), __('The map type used when showing a single map (max: 14).','events-made-easy').sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(DEFAULT_INDIV_MAPTYPE))  );
   ?>
</table>

<?php
	      break;
	      case 'other' :
?>

<h3><?php _e ( 'Other settings', 'events-made-easy'); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __('CSV separator','events-made-easy'), 'eme_csv_separator', __('Set the separator used in CSV exports.','events-made-easy').sprintf(__(" The default is '%s'",'events-made-easy'),eme_esc_html(EME_DEFAULT_CSV_SEPARATOR)) );
   eme_options_radio_binary ( __ ( 'Localize price', 'events-made-easy'), 'eme_localize_price', __ ( "If selected, all prices will be shown in the current locale with the price symbol. If you don't want this, deselect this option to regain the old behavior of localized prices where you need to take care of the decimal accuracy, the currency symbol and it's location yourself. This option only works if the php class NumberFormatter is present, which is always the case in newer php versions.", 'events-made-easy') ); 
   eme_options_input_text ( __('Decimals accuracy','events-made-easy'), 'eme_decimals', __('EME tries to show the prices in the frontend in the current locale, with the decimals accuracy set here. Defaults to 2.','events-made-easy') . '<br />'. __('This option is not used if the localize price option above is active and the php class NumberFormatter is present.','events-made-easy') );
   eme_options_select ( __('Thumbnail size','events-made-easy'), 'eme_thumbnail_size', eme_thumbnail_sizes(), __('Choose the default thumbnail size to be shown when using placeholders involving thumbnails like e.g. #_EVENTIMAGETHUMB, #_LOCATIONIMAGETHUMB, ...','events-made-easy') );
   eme_options_radio_binary ( __ ( 'Use external url for single events or locations?', 'events-made-easy'), 'eme_use_external_url', __ ( 'If selected, clicking on the single event or location url for details will go to the defined external url for that event or location if present.', 'events-made-easy') ); 
   eme_options_radio_binary ( __ ( 'Disable paragraphs around event and location notes?', 'events-made-easy'), 'eme_disable_wpautop', __ ( 'By default extra html paragraph tags are used around event and locations notes and excerpt, disable this if not desired.', 'events-made-easy') ); 
   ?>
</table>

<h3><?php _e ( 'Extra html headers', 'events-made-easy'); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Extra html header', 'events-made-easy'), 'eme_html_header', __ ( 'Here you can define extra html headers, no placeholders can be used, no html will be stripped. Can be used to add custom javascript, ...', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Extra html footer', 'events-made-easy'), 'eme_html_footer', __ ( 'Here you can define extra html footer, no placeholders can be used, no html will be stripped. Can be used to add custom javascript, ...', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Extra event html headers', 'events-made-easy'), 'eme_event_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single event, typically used to add meta tags for facebook or SEO. All event placeholders can be used, but will be stripped from resulting html.', 'events-made-easy') );
   eme_options_textarea ( __ ( 'Extra location html headers', 'events-made-easy'), 'eme_location_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single location, typically used to add meta tags for facebook or SEO. All location placeholders can be used, but will be stripped from resulting html.', 'events-made-easy') );
   ?>
</table>

<?php
	      break;
         }
?>


<p class="submit"><input type="submit" class="button-primary" id="eme_options_submit" name="Submit" value="<?php _e ( 'Save Changes' , 'events-made-easy')?>" /></p>
</form>
</div>
<?php
   }
}
?>
