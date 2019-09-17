<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_payment_form($payment_id) {

   $payment = eme_get_payment($payment_id);
   if ($payment['member_id']) {
	   return eme_payment_member_form($payment_id);
   }

   $booking_ids = eme_get_payment_booking_ids($payment_id);
   $unpaid_count = eme_payment_count_unpaid_bookings($payment_id);
   if (!$booking_ids)
      return $ret_string;
   if (count($booking_ids)==1) {
      $is_single=1;
      $is_multi=0;
   } else {
      $is_single=0;
      $is_multi=1;
   }

   $total_price=eme_get_payment_price($payment_id);

   // we take the currency of the first event in the series
   $booking=eme_get_booking($booking_ids[0]);
   $event=eme_get_event($booking['event_id']);
   $cur = $event['currency'];

   // if it is paid: show that and return
   if ($booking['waitinglist']) {
	   return "<div class='eme-waiting'>".__('This booking is on a waitinglist, payment is not possible.','events-made-easy')."</div>";
   } else {
	   if ($unpaid_count==0) {
		   if ($is_single)
			   return "<div class='eme-already-paid'>".__('This booking has already been paid for','events-made-easy')."</div>";
		   else
			   return "<div class='eme-already-paid'>".__('The relevant bookings have already been paid for','events-made-easy')."</div>";
	   }
   }

   $ret_string = '';
   if ($is_single)
      $eme_payment_form_header_format=get_option('eme_payment_form_header_format');
   else
      $eme_payment_form_header_format=get_option('eme_multipayment_form_header_format');
   if (!empty($eme_payment_form_header_format)) {
      $result = eme_replace_placeholders($eme_payment_form_header_format, $event,"html",0);
      $result = eme_replace_booking_placeholders($result, $event, $booking, $is_multi);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   } else {
      $ret_string .= "<div id='eme-payment-handling' class='eme-payment-handling'>".__('Payment handling','events-made-easy')."</div>";
      $ret_string .= "<div id='eme-payment-price-info' class='eme-payment-price-info'>".sprintf(__("The price in %s is: %01.2f",'events-made-easy'),$cur,$total_price)."</div>";
   }
   $payment=eme_get_payment($payment_id);
   if ($event['use_paypal'])
      $ret_string .= eme_paypal_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['use_2co'])
      $ret_string .= eme_2co_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['use_webmoney'])
      $ret_string .= eme_webmoney_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['use_fdgg'])
      $ret_string .= eme_fdgg_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['use_mollie'])
      $ret_string .= eme_mollie_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['use_sagepay'])
      $ret_string .= eme_sagepay_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['event_properties']['use_worldpay'])
      $ret_string .= eme_worldpay_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['event_properties']['use_stripe'])
      $ret_string .= eme_stripe_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['event_properties']['use_braintree'])
      $ret_string .= eme_braintree_form($event,$payment, $total_price,$cur,$booking['lang'],$is_multi);
   if ($event['event_properties']['use_offline']) {
      $eme_offline_format = get_option('eme_offline_payment');
      $result = eme_replace_placeholders($eme_offline_format, $event,"html",0);
      $result = eme_replace_booking_placeholders($result, $event, $booking, $is_multi);
      $ret_string .= "<div id='eme-payment-offline' class='eme-payment-offline'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   }

   if ($is_single)
      $eme_payment_form_footer_format=get_option('eme_payment_form_footer_format');
   else
      $eme_payment_form_footer_format=get_option('eme_multipayment_form_footer_format');
   if (!empty($eme_payment_form_footer_format)) {
      $result = eme_replace_placeholders($eme_payment_form_footer_format, $event,"html",0);
      $result = eme_replace_booking_placeholders($result, $event, $booking, $is_multi);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   }

   return $ret_string;
}

function eme_payment_member_form($payment_id,$form_result_message="") {

   $ret_string = "<div id='eme-rsvp-addmessage'>";
   if(!empty($form_result_message))
      $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";
   $ret_string .= "</div>";

   $member = eme_get_member_by_paymentid($payment_id);
   $person = eme_get_person($member['person_id']);
   $total_price=eme_get_member_payment_price($payment_id);
   $membership=eme_get_membership($member['membership_id']);
   $cur = $membership['properties']['currency'];

   // if it is paid: show that and return
   if ($member['paid']) {
	   return $ret_string."<div class='eme-already-paid'>".__('This has already been paid for','events-made-easy')."</div>";
   }

   $eme_payment_form_header_format=eme_get_template_format($membership['properties']['payment_form_header_tpl']);
   if (!empty($eme_payment_form_header_format)) {
      $result = eme_replace_member_placeholders($eme_payment_form_header_format, $membership, $member);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   } else {
      $ret_string .= "<div id='eme-payment-handling' class='eme-payment-handling'>".__('Payment handling','events-made-easy')."</div>";
      $ret_string .= "<div id='eme-payment-price-info' class='eme-payment-price-info'>".sprintf(__("The price in %s is: %01.2f",'events-made-easy'),$cur,$total_price)."</div>";
   }
   $ret_string .= "<div id='eme-payment-form' class='eme-payment-form'>";
   $payment=eme_get_payment($payment_id);
   if ($membership['properties']['use_paypal'])
      $ret_string .= eme_paypal_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_2co'])
      $ret_string .= eme_2co_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_webmoney'])
      $ret_string .= eme_webmoney_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_fdgg'])
      $ret_string .= eme_fdgg_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_mollie'])
      $ret_string .= eme_mollie_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_sagepay'])
      $ret_string .= eme_sagepay_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_worldpay'])
      $ret_string .= eme_worldpay_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_stripe'])
      $ret_string .= eme_stripe_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_braintree'])
      $ret_string .= eme_braintree_form($membership,$payment, $total_price,$cur,$person['lang']);
   if ($membership['properties']['use_offline']) {
      $eme_offline_format = eme_nl2br_save_html(eme_get_template_format($membership['properties']['offline_payment_tpl']));
      $result = eme_replace_member_placeholders($eme_offline_format, $membership, $member);
      $ret_string .= "<div id='eme-payment-offline' class='eme-payment-offline'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   }
   $ret_string .= "</div>";

   $eme_payment_form_footer_format=eme_get_template_format($membership['properties']['payment_form_footer_tpl']);
   if (!empty($eme_payment_form_footer_format)) {
      $result = eme_replace_member_placeholders($eme_payment_form_footer_format, $membership, $member);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   }
   return $ret_string;
}

function eme_payment_gateway_total($price,$cur,$gateway) {
   $price += eme_payment_gateway_extra_charge($price,$gateway);
   $eme_zero_decimal_currencies_arr=eme_zero_decimal_currencies();
   if (in_array($cur,$eme_zero_decimal_currencies_arr)) {
      $price = intval($price);
   } else {
      if ($gateway == "stripe") $price *=100;
   }
   return $price;
}

function eme_payment_gateway_extra_charge($price,$gateway) {
   $extra=get_option('eme_'.$gateway.'_cost');
   $result=0;
   if ($extra) {
	   if (strstr($extra,"%")) {
		   $extra=str_replace("%","",$extra);
		   $result += sprintf("%01.2f",$price*$extra/100);
	   } else {
		   $result += sprintf("%01.2f",$extra);
	   }
   }
   $extra=get_option('eme_'.$gateway.'_cost2');
   if ($extra) {
	   if (strstr($extra,"%")) {
		   $extra=str_replace("%","",$extra);
		   $result += sprintf("%01.2f",$price*$extra/100);
	   } else {
		   $result += sprintf("%01.2f",$extra);
	   }
   }
   return $result;
}

function eme_webmoney_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $eme_webmoney_purse=get_option('eme_webmoney_purse');
   if (!$eme_webmoney_purse) return;

   $gateway="webmoney";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$line['currency'],$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$line['currency'],$lang);
   if (empty($button_label)) $button_label=$gateway;
   // webmoney api does this itself
   // $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$line['currency'],$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   require_once('payment_gateways/webmoney/webmoney.inc.php');
   $wm_request = new WM_Request();
   $wm_request->payment_amount =$price;
   $wm_request->payment_desc = $name;
   $wm_request->payment_no = $payment_id;
   $wm_request->payee_purse = $eme_webmoney_purse;
   $wm_request->success_method = WM_POST;
   $result_link = add_query_arg(array('eme_eventAction'=>'webmoney'),$events_page_link);

   $wm_request->result_url = $result_link;
   $wm_request->success_url = $success_link;
   $wm_request->fail_url = $fail_link;
   if (get_option('eme_webmoney_demo')) {
      $wm_request->sim_mode = WM_ALL_SUCCESS;
   }
   $wm_request->btn_label = $button_label;
   if (!empty($button_img_url))
      $wm_request->btn_img_url = $button_img_url;

   $form_html = $button_above;
   $form_html .= $wm_request->SetForm(false);
   $form_html .= $button_below;
   return $form_html;
}

function eme_2co_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $eme_2co_business=get_option('eme_2co_business');
   if (!$eme_2co_business) return;

   $gateway="2co";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }
   if (get_option('eme_2co_demo')==2)
      $url=CO_SANDBOX_URL;
   else
      $url=CO_LIVE_URL;
   $quantity=1;

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');
   $form_html = $button_above;
   $form_html.="<form action='$url' method='post' name='eme_2co_form' id='eme_2co_form'>";
   $form_html.="<input type='hidden' name='sid' value='$eme_2co_business' />";
   $form_html.="<input type='hidden' name='mode' value='2CO' />";
   $form_html.="<input type='hidden' name='return_url' value='$success_link' />";
   $form_html.="<input type='hidden' name='li_0_type' value='product' />";
   $form_html.="<input type='hidden' name='li_0_product_id' value='$payment_id' />";
   $form_html.="<input type='hidden' name='li_0_name' value='$name' />";
   $form_html.="<input type='hidden' name='li_0_price' value='$price' />";
   $form_html.="<input type='hidden' name='li_0_quantity' value='$quantity' />";
   $form_html.="<input type='hidden' name='currency_code' value='$cur' />";
   if (!empty($button_img_url))
      $form_html.="<input type='image' alt='$button_label' title='$button_label' src='$button_img_url' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   if (get_option('eme_2co_demo')==1) {
      $form_html.="<input type='hidden' name='demo' value='Y' />";
   }
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_worldpay_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $worldpay_instid=get_option('eme_worldpay_instid');
   $worldpay_md5_secret=get_option('eme_worldpay_md5_secret');
   if (!$worldpay_instid) return;

   $gateway="worldpay";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }
   $notification_link = add_query_arg(array('eme_eventAction'=>'worldpay_notification'),$events_page_link);
   if (get_option('eme_worldpay_demo')==1)
      $url=WORLDPAY_SANDBOX_URL;
   else
      $url=WORLDPAY_LIVE_URL;
   $quantity=1;

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');
   $form_html = $button_above;
   $form_html.="<form action='$url' method='post' name='eme_worldpay_form' id='eme_worldpay_form'>";
   $form_html.="<input type='hidden' name='instId' value='$worldpay_instid' />";
   $form_html.="<input type='hidden' name='cartId' value='$payment_id' />";
   $form_html.="<input type='hidden' name='desc' value='$name' />";
   $form_html.="<input type='hidden' name='amount' value='$price' />";
   $form_html.="<input type='hidden' name='currency' value='$cur' />";
   // for worldpay notifications to work: enable dynamic payment response in your worldpay setup, using the param MC_callback
   // also: set the Payment Response password and if wanted, the MD5 secret and field combo
   $form_html.="<input type='hidden' name='MC_callback' value='$notification_link' />";

   if ($worldpay_md5_secret) {
      require_once 'payment_gateways/worldpay/eme-worldpay.php';
      $params_arr=explode(':',get_option('eme_worldpay_md5_parameters'));
      $signature=eme_generate_worldpay_signature($worldpay_md5_secret,$params_arr,$worldpay_instid,$payment_id,$cur,$price);
      $form_html.="<input type='hidden' name='signature' value='$signature' />";
   }

   if (!empty($button_img_url))
      $form_html.="<input type='image' alt='$button_label' title='$button_label' src='$button_img_url' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   if (get_option('eme_worldpay_demo')==1) {
      $form_html.="<input type='hidden' name='testMode' value='100' />";
   }
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_braintree_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $eme_braintree_private_key=get_option('eme_braintree_private_key');
   $eme_braintree_public_key=get_option('eme_braintree_public_key');
   $eme_braintree_merchant_id=get_option('eme_braintree_merchant_id');
   $eme_braintree_env=get_option('eme_braintree_env');
   if (!$eme_braintree_public_key) return;

   require_once('payment_gateways/braintree/braintree-php-3.8.0/lib/Braintree.php');
   Braintree\Configuration::environment($eme_braintree_env);
   Braintree\Configuration::merchantId(   $eme_braintree_merchant_id);
   Braintree\Configuration::publicKey($eme_braintree_public_key);
   Braintree\Configuration::privateKey($eme_braintree_private_key);
   $clientToken = Braintree_ClientToken::generate();

   $gateway="braintree";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];

   $quantity=1;

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');
   $form_html = $button_above;
   $form_html.="<form action='' method='post' name='eme_braintree_form' id='eme_braintree_form'>
   <div id='braintree-payment-form'></div>
   <input type='hidden' name='payment_id' value='$payment_id' />
   <input type='hidden' name='eme_eventAction' value='braintree_charge' />
   <input type='hidden' name='eme_multibooking' value='$multi_booking' />
   ";

   if (!empty($button_img_url))
      $form_html.="<input type='image' alt='$button_label' title='$button_label' src='$button_img_url' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   $form_html.="</form>
<script src='https://js.braintreegateway.com/v2/braintree.js'></script>
<script>
var clientToken = '$clientToken';
braintree.setup(clientToken, 'dropin', {
  container: 'braintree-payment-form'
});
</script>
   ";
   $form_html.= $button_below;
   return $form_html;
}

function eme_stripe_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $eme_stripe_public_key=get_option('eme_stripe_public_key');
   if (!$eme_stripe_public_key) return;
   $blog_name=esc_attr(get_option('blog_name'));
   if ($payment['member_id']>0) {
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
      // stripe doesn't like the single quotes
      $name = preg_replace('/\'/', '', $name);
   } elseif ($multi_booking) {
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
      // stripe doesn't like the single quotes
      $name = preg_replace('/\'/', '', $name);
   }

   $gateway="stripe";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   $quantity=1;

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label = htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   $form_html = $button_above;
   $form_html.="<form action='' method='post' name='eme_stripe_form' id='eme_stripe_form'>
   <script
    src='https://checkout.stripe.com/checkout.js' class='stripe-button'
    data-key='$eme_stripe_public_key'
    data-name='$blog_name'
    data-label='$button_label'
    data-description='$name'
    data-amount='$price' data-currency='$cur'>
   </script>
   <input type='hidden' name='payment_id' value='$payment_id' />
   <input type='hidden' name='eme_eventAction' value='stripe_charge' />
   <input type='hidden' name='eme_multibooking' value='$multi_booking' />
   ";
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_fdgg_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   if (!$store_name) return;

   $gateway="fdgg";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }
   // the live or sandbox url
   $url = get_option('eme_fdgg_url');
   $quantity=1;
   // First Data only allows USD
   $cur="USD";
   $timezone_short="GMT";
   $eme_date_obj=new ExpressiveDate($payment['creation_date_gmt'],$timezone_short);
   $datetime=$eme_date_obj->format("Y:m:d-H:i:s");

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   require_once('payment_gateways/fdgg/fdgg-util_sha2.php');
   $form_html = $button_above;
   $form_html.="<form action='$url' method='post' name='eme_fdgg_form' id='eme_fdgg_form'>";
   $form_html.="<input type='hidden' name='timezone' value='$timezone_short' />";
   $form_html.="<input type='hidden' name='authenticateTransaction' value='false' />";
   $form_html.="<input type='hidden' name='txntype' value='sale' />";
   $form_html.="<input type='hidden' name='mode' value='payonly' />";
   $form_html.="<input type='hidden' name='trxOrigin' value='ECI' />";
   $form_html.="<input type='hidden' name='txndatetime' value='$datetime' />";
   $form_html.="<input type='hidden' name='hash' value='".fdgg_createHash($store_name . $datetime . $price . $shared_secret)."' />";
   $form_html.="<input type='hidden' name='storename' value='$store_name' />";
   $form_html.="<input type='hidden' name='chargetotal' value='$price' />";
   $form_html.="<input type='hidden' name='subtotal' value='$price' />";
   $form_html.="<input type='hidden' name='invoicenumber' value='$payment_id' />";
   $form_html.="<input type='hidden' name='oid' value='$payment_id' />";
   $form_html.="<input type='hidden' name='responseSuccessURL' value='$success_link' />";
   $form_html.="<input type='hidden' name='responseFailURL' value='$fail_link' />";
   $form_html.="<input type='hidden' name='eme_eventAction' value='fdgg_notification' />";
   if (!empty($button_img_url))
      $form_html.="<input type='image' src='$button_img_url' alt='$button_label' title='$button_label' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_sagepay_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $vendor_name = get_option('eme_sagepay_vendor_name');
   if (!$vendor_name) return;

   $gateway="sagepay";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }
   // sagepay doesn't use a notification url, but sends the status along as part of the return url
   // so we add the notification info to it too, so we can process paid info as usual
   $success_link = add_query_arg(array('eme_eventAction'=>'sagepay_notification'),$success_link);
   $fail_link = add_query_arg(array('eme_eventAction'=>'sagepay_notification'),$fail_link);

   // the live or sandbox url
   $sagepay_demo = get_option('eme_sagepay_demo');
   if ($sagepay_demo == 1) {
      $sagepay_pwd = get_option('eme_sagepay_test_pwd');
      $url = SAGEPAY_SANDBOX_URL;
   } else {
      $sagepay_pwd = get_option('eme_sagepay_live_pwd');
      $url = SAGEPAY_LIVE_URL;
   }

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   $query = array(
         'VendorTxCode' => $payment_id,
         'Amount' => number_format($price, 2, '.', ''),
         'Currency' => $cur,
         'Description' => $name,
         'SuccessURL' => $success_link,
         'FailureURL' => $fail_link
            );

   require_once 'payment_gateways/sagepay/eme-sagepay-util.php';
   $crypt = SagepayUtil::encryptAes(SagepayUtil::arrayToQueryString($query),$sagepay_pwd);

   $form_html = $button_above;
   $form_html.="<form action='$url' method='post' name='eme_sagepay_form' id='eme_sagepay_form'>";
   $form_html.="<input type='hidden' name='VPSProtocol' value='3.00' />";
   $form_html.="<input type='hidden' name='TxType' value='PAYMENT' />";
   $form_html.="<input type='hidden' name='Vendor' value='$vendor_name' />";
   $form_html.="<input type='hidden' name='Crypt' value='$crypt' />";
   if (!empty($button_img_url))
      $form_html.="<input type='image' src='$button_img_url' alt='$button_label' title='$button_label' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_mollie_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $mollie_api_key = get_option('eme_mollie_api_key');
   if (!$mollie_api_key) return;

   $gateway="mollie";
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']);
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']);
   }
   $notification_link = add_query_arg(array('eme_eventAction'=>'mollie_notification'),$events_page_link);

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   require_once 'payment_gateways/Mollie/API/Autoloader.php';
   $mollie = new Mollie_API_Client;
   $mollie->setApiKey($mollie_api_key);

   try {
      $payment = $mollie->payments->create(
            array(
               'amount'      => $price,
               'description' => $name,
               'redirectUrl' => $success_link,
               'webhookUrl'  => $notification_link,
               'metadata'    => array(
                  'payment_id' => $payment_id
                  )
               )
            );
      $url = $payment->getPaymentUrl();
   }
   catch (Mollie_API_Exception $e) {
      $url="";
      $form_html = "Mollie API call failed: " . htmlspecialchars($e->getMessage()) . " on field " . htmlspecialchars($e->getField());
   }

   if (!empty($url)) {
      $form_html = $button_above;
      $form_html.="<form action='$url' method='post'>";
      if (!empty($button_img_url))
         $form_html.="<input type='image' src='$button_img_url' alt='$button_label' title='$button_label' />";
      else
         $form_html.="<input type='submit' value='$button_label' /><br />";
      $form_html.= $button_below;
      $methods = $mollie->methods->all();
      foreach ($methods as $method) {
         $form_html.= '<img src="' . htmlspecialchars($method->image->normal) . '" alt="'.htmlspecialchars($method->description).'" title="'.htmlspecialchars($method->description).'"> ';
      }
      $form_html.="</form>";
   }
   return $form_html;
}

function eme_paypal_form($line,$payment,$baseprice,$cur,$lang,$multi_booking=0) {
   $eme_paypal_business = get_option('eme_paypal_business');
   if (!$eme_paypal_business) return;

   $quantity=1;
   $gateway='paypal';
   $price=eme_payment_gateway_total($baseprice,$cur,$gateway);
   $events_page_link = eme_get_events_page();
   $payment_id=$payment['id'];
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = eme_esc_html(sprintf(__("Member signup for '%s'",'events-made-easy'),$line['name']));
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $name = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $name = eme_esc_html(sprintf(__("Booking for '%s'",'events-made-easy'),$line['event_name']));
   }
   $notification_link = add_query_arg(array('eme_eventAction'=>'paypal_notification'),$events_page_link);

   $charge = eme_payment_gateway_extra_charge($baseprice,$gateway); 
   $button_above = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_above'),$charge,$cur,$lang);
   $button_label = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_label'),$charge,$cur,$lang);
   if (empty($button_label)) $button_label=$gateway;
   $button_label=htmlentities($button_label);
   $button_below = eme_replace_payment_gateway_placeholders(get_option('eme_'.$gateway.'_button_below'),$charge,$cur,$lang);
   $button_img_url = get_option('eme_'.$gateway.'_button_img_url');

   require_once "payment_gateways/paypal/Paypal.php";
   $p = new Paypal;

   // the paypal or paypal sandbox url
   $p->paypal_url = get_option('eme_paypal_url');

   // the timeout in seconds before the button form is submitted to paypal
   // this needs the included addevent javascript function
   // 0 = no delay
   // false = disable auto submission
   $p->timeout = false;

   // the button label
   // false to disable button (if you want to rely only on the javascript auto-submission) not recommended
   $p->button = $button_label;
   if (!empty($button_img_url))
      $p->button_img_url = $button_img_url;

   if (get_option('eme_paypal_s_encrypt')) {
      // use encryption (strongly recommended!)
      $p->encrypt = true;
      $p->private_key = get_option('eme_paypal_s_privkey');
      $p->public_cert = get_option('eme_paypal_s_pubcert');
      $p->paypal_cert = get_option('eme_paypal_s_paypalcert');
      $p->cert_id = get_option('eme_paypal_s_certid');
   } else {
      $p->encrypt = false;
   }

   // the actual button parameters
   // https://www.paypal.com/IntegrationCenter/ic_std-variable-reference.html
   $p->add_field('charset','utf-8');
   $p->add_field('business', $eme_paypal_business);
   $p->add_field('return', $success_link);
   $p->add_field('cancel_return', $fail_link);
   $p->add_field('notify_url', $notification_link);
   $p->add_field('item_name', $name);
   $p->add_field('item_number', $payment_id);
   $p->add_field('currency_code',$cur);
   $p->add_field('amount', $price);
   $p->add_field('quantity', $quantity);
   $p->add_field('no_shipping', 1);
   if (get_option('eme_paypal_no_tax')) {
      $p->add_field('tax', 0);
   }

   $form_html = $button_above;
   $form_html .= $p->get_button();
   $form_html .= $button_below;
   return $form_html;
}

function eme_paypal_notification() {
   require_once 'payment_gateways/paypal/IPN.php';
   $ipn = new IPN;

   // the paypal url, or the sandbox url, or the ipn test url
   //$ipn->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
   $ipn->paypal_url = get_option('eme_paypal_url');

   // your paypal email (the one that receives the payments)
   $ipn->paypal_email = get_option('eme_paypal_business');

   // log to file options
   $ipn->log_to_file = false;					// write logs to file
   $ipn->log_filename = '/path/to/ipn.log';  	// the log filename (should NOT be web accessible and should be writable)

   // log to e-mail options
   $ipn->log_to_email = false;					// send logs by e-mail
   $ipn->log_email = '';		// where you want to receive the logs
   $ipn->log_subject = 'IPN Log: ';			// prefix for the e-mail subject

   // database information
   $ipn->log_to_db = false;						// false not recommended
   $ipn->db_host = 'localhost';				// database host
   $ipn->db_user = '';				// database user
   $ipn->db_pass = '';			// database password
   $ipn->db_name = '';						// database name

   // array of currencies accepted or false to disable
   //$ipn->currencies = array('USD','EUR');
   $ipn->currencies = false;

   // date format on log headers (default: dd/mm/YYYY HH:mm:ss)
   // see http://php.net/date
   $ipn->date_format = 'd/m/Y H:i:s';

   // Prefix for file and mail logs
   $ipn->pretty_ipn = "IPN Values received:\n\n";

   // configuration ended, do the actual check

   if($ipn->ipn_is_valid()) {
      /*
         A valid ipn was received and passed preliminary validations
         You can now do any custom validations you wish to ensure the payment was correct
         You can access the IPN data with $ipn->ipn['value']
         The complete() method below logs the valid IPN to the places you choose
       */
      $payment_id=intval($ipn->ipn['item_number']);
      eme_update_payment_paid($payment_id);
      $ipn->complete();
   }
}

function eme_2co_notification() {
   $business=get_option('eme_2co_business');
   $secret=get_option('eme_2co_secret');

   if ($_POST['message_type'] == 'ORDER_CREATED'
       || $_POST['message_type'] == 'INVOICE_STATUS_CHANGED') {
      $insMessage = array();
      foreach ($_POST as $k => $v) {
         $insMessage[$k] = $v;
      }
 
      $hashSid = $insMessage['vendor_id'];
      if ($hashSid != $business) {
         die ('Not the 2Checkout Account number it should be ...');
      }
      $hashOrder = $insMessage['sale_id'];
      $hashInvoice = $insMessage['invoice_id'];
      $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $secret));
 
      if ($StringToHash != $insMessage['md5_hash']) {
	 die(__('Hash Incorrect','events-made-easy'));
      }

      if ($insMessage['invoice_status'] == 'approved' || $insMessage['invoice_status'] == 'deposited') {
         $payment_id=intval($insMessage['item_id_1']);
         eme_update_payment_paid($payment_id);
      }
   }
}

function eme_webmoney_notification() {
   $webmoney_purse = get_option('eme_webmoney_purse');
   $webmoney_secret = get_option('eme_webmoney_secret');

   require_once('payment_gateways/webmoney/webmoney.inc.php');
   $wm_notif = new WM_Notification(); 
   if ($wm_notif->GetForm() != WM_RES_NOPARAM) {
      $amount=$wm_notif->payment_amount;
      if ($webmoney_purse != $wm_notif->payee_purse) {
         die ('Not the webmoney purse it should be ...');
      }
      #if ($price != $amount) {
      #   die ('Not the webmoney amount I expected ...');
      #}
      $payment_id=intval($wm_notif->payment_no);
      if ($wm_notif->CheckMD5($webmoney_purse, $amount, $payment_id, $webmoney_secret) == WM_RES_OK) {
         eme_update_payment_paid($payment_id);
      }
   }
}

function eme_fdgg_notification() {
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   require_once('payment_gateways/fdgg/fdgg-util_sha2.php');

   $payment_id      = intval($_POST['invoicenumber']);
   $charge_total    = $_POST['charge_total'];
   $approval_code   = $_POST['approval_code'];
   $response_hash   = $_POST['response_hash'];
   $response_status = $_POST['status'];

   // First Data only allows USD
   $cur="USD";
   $payment=eme_get_payment($payment_id);
   $timezone_short="GMT";
   $eme_date_obj=new ExpressiveDate($payment['creation_date_gmt'],$timezone_short);
   $datetime=$eme_date_obj->format("Y:m:d-H:i:s");
   $calc_hash=fdgg_createHash($shared_secret.$approval_code.$charge_total.$cur.$datetime.$store_name);

   if ($response_hash != $calc_hash) {
      die(__('Hash Incorrect','events-made-easy'));
   }

   // TODO: do some extra checks, like the price paid and such
   #$price=eme_get_total_booking_price($booking);

   if (strtolower($response_status) == 'approved') {
      eme_update_payment_paid($payment_id);
   }
}

function eme_stripe_charge() {
   $eme_stripe_private_key=get_option('eme_stripe_private_key');
   $payment_id = intval($_POST['payment_id']);
   $payment = eme_get_payment($payment_id);
   $member_payment=0;
   $multi_booking = 0;
   if ($payment['member_id']>0) {
      $member = eme_get_member($payment['member_id']);
      if ($member) {
	      $person = eme_get_person($member['person_id']);
	      $membership=eme_get_membership($member['membership_id']);
	      $cur=$membership['currency'];
      } else {
	      _e('Incorrect payment id.','events-made-easy');return;
      }
   } else {
      $booking_ids=eme_get_payment_booking_ids($payment_id);
      if ($booking_ids) {
	      $person = eme_get_person($bookings[0]['person_id']);
	      $event=eme_get_event_by_booking_id($booking_ids[0]);
	      $cur=$event['currency'];
	      $multi_booking = isset($_POST['eme_multibooking']) ? intval($_POST['eme_multibooking']) : 0;
      } else {
	      _e('Incorrect payment id.','events-made-easy');return;
      }
   }

   $events_page_link = eme_get_events_page();
   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $booking_descr = sprintf(__("Member signup for '%s'",'events-made-easy'),$membership['name']);
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $booking_descr = __("Multiple booking request",'events-made-easy');
   } else {
      $success_link = eme_payment_return_url($payment,1,$line);
      $fail_link = eme_payment_return_url($payment,2,$line);
      $booking_descr = sprintf(__("Booking for '%s'",'events-made-easy'),$event['event_name']);
   }

   $price = eme_get_payment_price($payment_id);
   $price=eme_payment_gateway_total($price,$cur,'stripe');
   require_once('payment_gateways/stripe/stripe-php-5.5.0/init.php');
   \Stripe\Stripe::setApiKey($eme_stripe_private_key);
   try {
	   if (!isset($_POST['stripeToken']))
		   throw new Exception("The Stripe Token was not generated correctly");
	   $charge = \Stripe\Charge::create(array(
				   "amount" => $price,
				   "currency" => $cur,
				   "source" => $_POST['stripeToken'],
                                   "receipt_email" => $_POST['stripeEmail'],
				   "description" => $booking_descr)
			   );
	   if ($charge->status == 'succeeded') {
		   eme_update_payment_paid($payment_id);
		   header("Location: $success_link");exit;
	   } else {
		   header("Location: $fail_link");exit;
		   //die(__('Your payment failed.','events-made-easy'));
	   }
   } catch(\Stripe\Error\Card $e) {
	   // The card has been declined
	   $error = $e->getMessage();
	   die($error);
   }
}

function eme_braintree_charge() {
   $eme_braintree_private_key=get_option('eme_braintree_private_key');
   $eme_braintree_public_key=get_option('eme_braintree_public_key');
   $eme_braintree_merchant_id=get_option('eme_braintree_merchant_id');
   $eme_braintree_env=get_option('eme_braintree_env');
   $payment_id = intval($_POST['payment_id']);
   $payment = eme_get_payment($payment_id);
   $multi_booking = 0;
   if ($payment['member_id']>0) {
      $member = eme_get_member($payment['member_id']);
      if ($member) {
	      $person = eme_get_person($member['person_id']);
	      $membership=eme_get_membership($member['membership_id']);
	      $cur = $membership['currency'];
      } else {
	      _e('Incorrect payment id.','events-made-easy');return;
      }
   } else {
      $booking_ids=eme_get_payment_booking_ids($payment_id);
      if ($booking_ids) {
	      $person = eme_get_person($bookings[0]['person_id']);
	      $event=eme_get_event_by_booking_id($booking_ids[0]);
	      $cur=$event['currency'];
	      $multi_booking = isset($_POST['eme_multibooking']) ? intval($_POST['eme_multibooking']) : 0;
      } else {
	      _e('Incorrect payment id.','events-made-easy');return;
      }
   }

   if ($payment['member_id']>0) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $booking_descr = sprintf(__("Member signup for '%s'",'events-made-easy'),$membership['name']);
   } elseif ($multi_booking) {
      $success_link = eme_payment_return_url($payment,1);
      $fail_link = eme_payment_return_url($payment,2);
      $booking_descr = esc_attr(__("Multiple booking request",'events-made-easy'));
   } else {
      $payment=eme_get_payment($payment_id);
      $success_link = eme_payment_return_url($payment,1,$event);
      $fail_link = eme_payment_return_url($payment,2,$event);
      $booking_descr = esc_attr(sprintf(__("Booking for '%s'",'events-made-easy'),$event['event_name']));
   }

   $price = eme_get_payment_price($payment_id);
   $price=eme_payment_gateway_total($price,$cur,'braintree');
   require_once('payment_gateways/braintree/braintree-php-3.8.0/lib/Braintree.php');
   if (!isset($_POST['payment_method_nonce']))
	   die("The nonce was not generated correctly");
   Braintree\Configuration::environment($eme_braintree_env);
   Braintree\Configuration::merchantId($eme_braintree_merchant_id);
   Braintree\Configuration::publicKey($eme_braintree_public_key);
   Braintree\Configuration::privateKey($eme_braintree_private_key);
   $result = Braintree\Transaction::sale(array(
			   "amount" => $price,
			   "paymentMethodNonce" => $_POST['payment_method_nonce'],
			   "orderId" => $payment_id
			   )
		   );
   if ($result->success) {
	   eme_update_payment_paid($payment_id);
      header("Location: $success_link");exit;
   } else {
      header("Location: $fail_link");exit;
	   die(__('Your payment failed.','events-made-easy'));
   }
}

function eme_mollie_notification() {
   $api_key = get_option('eme_mollie_api_key');
   require_once 'payment_gateways/Mollie/API/Autoloader.php';

   $mollie = new Mollie_API_Client;
   $mollie->setApiKey($api_key);
   $payment = $mollie->payments->get($_POST["id"]);
   $payment_id = $payment->metadata->payment_id;
   if ($payment->isPaid()) {
      eme_update_payment_paid($payment_id);
   }
}

function eme_worldpay_notification() {
   // for worldpay notifications to work: enable dynamic payment response in your worldpay setup, using the param MC_callback
   $worldpay_demo = get_option('eme_worldpay_demo');
   if ($worldpay_demo == 1) {
      $worldpay_pwd = get_option('eme_worldpay_test_pwd');
   } else {
      $worldpay_pwd = get_option('eme_worldpay_live_pwd');
   }

   $post_pwd=$_POST["callbackPW"];
   $trans_status=$_POST["transStatus"];
   $test_mode=isset($_POST ['testMode']) ? $_POST ['testMode'] : 0;
   $post_instid=$_POST["instId"];
   $worldpay_instid=get_option('eme_worldpay_instid');
   $payment_id=intval($_POST["cartId"]);
   if ($post_pwd==$worldpay_pwd && $trans_status=='Y' && $test_mode==0 && $post_instid==$worldpay_instid) {
      eme_update_payment_paid($payment_id);
   }
}

function eme_sagepay_notification() {
   $sagepay_demo = get_option('eme_sagepay_demo');
   if ($sagepay_demo == 1) {
      $sagepay_pwd = get_option('eme_sagepay_test_pwd');
   } else {
      $sagepay_pwd = get_option('eme_sagepay_live_pwd');
   }

   require_once 'payment_gateways/sagepay/eme-sagepay-util.php';
   $decrypt = SagepayUtil::decryptAes($crypt, $sagepay_pwd);
   $decryptArr = SagepayUtil::queryStringToArray($decrypt);
   if ($decrypt && !empty($decryptArr)) {
      if ($decryptArr['Status']=='OK') {
         $payment_id=$decryptArr['VendorTxCode'];
         eme_update_payment_paid($payment_id);
      }
   }
}

function eme_event_can_pay_online ($event) {
   if ($event['use_paypal'] || $event['use_2co'] || $event['use_webmoney'] || $event['use_fdgg'] || $event['use_mollie'] || $event['use_sagepay'] || $event['event_properties']['use_worldpay'] || $event['event_properties']['use_stripe'] || $event['event_properties']['use_braintree'] || $event['event_properties']['use_offline'])
      return 1;
   else
      return 0;
}
function eme_membership_can_pay_online ($membership) {
   if ($membership['properties']['use_paypal'] || $membership['properties']['use_2co'] || $membership['properties']['use_webmoney'] || $membership['properties']['use_fdgg'] || $membership['properties']['use_mollie'] || $membership['properties']['use_sagepay'] || $membership['properties']['use_worldpay'] || $membership['properties']['use_stripe'] || $membership['properties']['use_braintree'] || $membership['properties']['use_offline'])
      return 1;
   else
      return 0;
}

function eme_create_member_payment($member_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $payment_id = false;
   $payment=array();
   $payment['random_id']=eme_payment_random_id();
   $payment['member_id']=$member_id;
   $payment['creation_date_gmt']=current_time('mysql', true);
   if ($wpdb->insert($payments_table,$payment)) {
      $payment_id = $wpdb->insert_id;
      $where['member_id'] = $member_id;
      $fields['transfer_nbr_be97'] = eme_transfer_nbr_be97($payment_id);
      $fields['payment_id'] = $payment_id;
      $wpdb->update($members_table, $fields, $where);
   }
   return $payment_id;
}

function eme_create_payment($booking_ids) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;

   // some safety
   if (!$booking_ids)
      return false;

   $payment_id = false;
   $payment=array();
   $payment['random_id']=eme_payment_random_id();
   $payment['creation_date_gmt']=current_time('mysql', true);
   if ($wpdb->insert($payments_table,$payment)) {
      $payment_id = $wpdb->insert_id;
      $booking_ids_arr=explode(",",$booking_ids);
      foreach ($booking_ids_arr as $booking_id) {
         $where = array();
         $fields = array();
         $where['booking_id'] = $booking_id;
         $fields['transfer_nbr_be97'] = eme_transfer_nbr_be97($payment_id);
         $fields['payment_id'] = $payment_id;
         $wpdb->update($bookings_table, $fields, $where);
      }
   }
   return $payment_id;
}

function eme_get_payment($payment_id,$payment_randomid=0) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   if ($payment_id)
      $sql = $wpdb->prepare("SELECT * FROM $payments_table WHERE id=%d",$payment_id);
   else
      $sql = $wpdb->prepare("SELECT * FROM $payments_table WHERE random_id=%s",$payment_randomid);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_payment_booking_ids($payment_id) {
   global $wpdb;
   $table_name = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_id FROM $table_name WHERE payment_id=%d",$payment_id);
   return $wpdb->get_col($sql);
}

function eme_get_randompayment_booking_ids($payment_randomid) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT bookings.booking_id FROM $bookings_table as bookings LEFT JOIN $payments_table as payments ON bookings.payment_id=payments.id where payments.random_id=%s",$payment_randomid);
   return $wpdb->get_col($sql);
}

function eme_delete_payment($payment_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $payments_table WHERE id=%d",$payment_id);
   return $wpdb->get_var($sql);
}

function eme_get_payment_seats($payment_id) {
   $seats=0;
   $bookings = eme_get_bookings_by_paymentid($payment_id);
   foreach ($bookings as $booking) {
      $seats += eme_get_total($booking['booking_seats']);
   }
   return $seats;
}

function eme_get_payment_price($payment_id) {
   $price=0;
   $bookings = eme_get_bookings_by_paymentid($payment_id);
   foreach ($bookings as $booking) {
      $price += eme_get_total_booking_price($booking);
   }
   return $price;
}

function eme_get_member_payment_price($payment_id) {
   $price=0;
   $member = eme_get_member_by_paymentid($payment_id);
   $price = eme_get_total_member_price($member);
   return $price;
}

function eme_update_payment_paid($payment_id, $is_ipn=1) {
   $payment = eme_get_payment($payment_id);
   if (isset($payment['member_id']) && $payment['member_id']>0) {
      $res = eme_member_set_paid($payment['member_id']);
      if ($res) {
	  $member = eme_get_member($payment['member_id']);
	  eme_email_member_booking($member,"paidRegistration");
      }
      if ($is_ipn) {
          eme_email_member_booking($member,'ipnReceived');
      }
   } else {
      $booking_ids=eme_get_payment_booking_ids($payment_id);
      foreach ($booking_ids as $booking_id) {
        $booking=eme_get_booking($booking_id);
        $event = eme_get_event($booking['event_id']);
        if ($event['event_properties']['auto_approve'] && !$booking['booking_approved'] && !$booking['waitinglist']) {
           $res = eme_set_booking_paid_approved($booking_id);
	   if ($res) {
		   //booking changed, let's get it again
		   $booking = eme_get_booking ($booking_id);
		   if (!get_option('eme_rsvp_mail_notify_paid'))
			   eme_email_rsvp_booking($booking,"approveRegistration");
	   }
        } else {
           $res = eme_set_booking_paid($booking_id);
	   if ($res) {
		   //booking changed, let's get it again
		   $booking = eme_get_booking ($booking_id);
	   }
        }
        if (get_option('eme_rsvp_mail_notify_paid'))
	       eme_email_rsvp_booking($booking,"paidRegistration");

        // if it is the result of an ipn (the default), we send mails and do possible actions
        if ($is_ipn) {
           eme_email_rsvp_booking($booking,'ipnReceived');
           if (has_action('eme_ipn_action')) do_action('eme_ipn_action',$booking);
        }
      }
   }
}

function eme_replace_payment_gateway_placeholders($format, $charge, $currency, $lang) {
   preg_match_all("/#_?[A-Za-z0-9_]+/", $format, $placeholders);

   usort($placeholders[0],'eme_sort_stringlenth');
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_EXTRACHARGE$/', $result)) {
         $replacement = $charge;
      } elseif (preg_match('/#_CURRENCY$/', $result)) {
         $replacement = $currency;
      } elseif (preg_match('/#_CURRENCYSYMBOL$/', $result)) {
         $replacement = eme_localized_currencysymbol($currency);
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

function eme_payment_random_id() {
   return uniqid() . '_' . md5(mt_rand());
}

function eme_payment_count_unpaid_bookings($payment_id) {
   global $wpdb;
   $table_name = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE payment_id=%d AND booking_paid=0",$payment_id);
   return $wpdb->get_var($sql);
}

?>
