<?php
/*
 * Plugin Name: Events Made Easy Frontend Submit
 * Plugin URI: http://www.e-dynamics.be/wordpress
 * Description: Displays a form to allow people to enter events for the Events Made Easy plugin on a regular wordpress page.
 * Author: Franky Van Liedekerke
 * Version: 1.0.28
 * Author URI: http://www.e-dynamics.be/wordpress
 * License: GNU General Public License
 * Text Domain: events-made-easy-frontend-submit
 * Domain Path: /lang
*/

define( 'EMEFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMEFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
  Default Data used by the plugin
 */
$emefs_event_data = array();

$emefs_event_errors = array(
	"event_name" => false,
	"event_status" => false,
	"event_start_date" => false,
	"event_end_date" => false,
	"event_start_time" => false,
	"event_end_time" => false,
	"event_time" => false,
	"event_rsvp" => false,
	"rsvp_number_days" => false,
	"registration_requires_approval" => false,
	"registration_wp_users_only" => false,
	"event_seats" => false,
	"event_author" => false,
	"event_notes" => false,
	'event_page_title_format' => false,
	'event_single_event_format' => false,
	'event_contactperson_email_body' => false,
	'event_respondent_email_body' => false,
	'event_url' => false,
	'event_image_url' => false,
	'event_category_ids' => false,
	'event_attributes' => false,
	'event_properties' => false,
	'location_id' => false,
	'location_name' => false,
	'location_address' => false,
	'location_town' => false,
	'location_address1' => false,
	'location_address2' => false,
	'location_city' => false,
	'location_state' => false,
	'location_zip' => false,
	'location_country' => false,
	'location_latitude' => false,
	'location_longitude' => false,
	'captcha' => false,
);

$emefs_has_errors = false;

// don't load directly
if (!function_exists('is_admin')) {
   header('Status: 403 Forbidden');
   header('HTTP/1.1 403 Forbidden');
   exit();
}

if (!class_exists("EMEFS")) :
class EMEFS {

	/*
	 Load the options, set up hooks, and all, on the condition that EME is activated as well.
	 */
   var $settings;
	
   function __construct() {
      if ((function_exists('is_multisite') && is_multisite() && array_key_exists('events-made-easy/events-manager.php',get_site_option('active_sitewide_plugins'))) || in_array('events-made-easy/events-manager.php', apply_filters('active_plugins', get_option( 'active_plugins' )))) {
         add_action('init', array($this,'init') );
         register_activation_hook( __FILE__, array($this,'activate') );
         register_deactivation_hook( __FILE__, array($this,'deactivate') );
      } else {
         add_action('admin_notices', array(__CLASS__, 'do_dependencies_notice'));
      }
   }

   function network_propagate($pfunction, $networkwide) {
      global $wpdb;

      if (function_exists('is_multisite') && is_multisite()) {
         // check if it is a network activation - if so, run the activation function 
         // for each blog id
         if ($networkwide) {
            //$old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blogids as $blog_id) {
               switch_to_blog($blog_id);
               call_user_func($pfunction, $networkwide);
               restore_current_blog();
            }
            //switch_to_blog($old_blog);
            return;
         }
      }
      call_user_func($pfunction, $networkwide);
   }

   function activate($networkwide) {
      $this->network_propagate(array($this, '_activate'), $networkwide);
   }

   function deactivate($networkwide) {
      $this->network_propagate(array($this, '_deactivate'), $networkwide);
   }

   /*
      Enter our plugin activation code here.
    */
   function _activate() {}

   /*
      Enter our plugin deactivation code here.
    */
   function _deactivate() {}

   function init() {
      load_plugin_textdomain( 'events-made-easy-frontend-submit', EMEFS_PLUGIN_DIR . 'lang',
            basename( dirname( __FILE__ ) ) . '/lang' );
      // Load settings page
      if (!class_exists("emefs_settings"))
         require(EMEFS_PLUGIN_DIR . 'emefs-settings.php');
      $this->settings=new EMEFS_Settings();
      if (!is_admin()) {
         add_action('template_redirect', array($this, 'pageHasForm'));
         // captcha needs a session
         if ($this->settings->options['use_captcha'] && !session_id()) session_start();
         $this->processForm();
         self::registerAssets();
      }
   }

   function pageHasForm() { 
      global $wp_query;
      if ( is_page() || is_single() ) {
         $post = $wp_query->get_queried_object();
         if ( false !== strpos($post->post_content, '[emefs_submit_event_form') ) {
            if(!$this->settings->options['guest_submit'] && !current_user_can('edit_posts')){
               wp_redirect(get_permalink($this->settings->options['guest_not_allowed_page']));
            }

            // Display Form Shortcode & Wrapper
            add_shortcode( 'emefs_submit_event_form', array($this, 'deployForm'));

            // Scripts and Styles 
            add_action( 'wp_print_scripts', array(__CLASS__, 'printScripts') );
            add_action( 'wp_print_styles', array(__CLASS__, 'printStyles') );
         }
      }
   }

   /*
      Tell the user to activate EME before using EMEFS
    */
   public static function do_dependencies_notice() {
      $message = __( "The Events Made Easy Frontend Submit plugin is an extension to the Events Made Easy plugin, which has to be installed and activated first. The plugin has been deactivated.", 'events-made-easy-frontend-submit' );
      echo sprintf('<div class="error"><p>%s</p></div>', $message);
   }

   /*
      Processes the form submitted data
    */
   function processForm() {

      global $emefs_event_errors, $emefs_event_data, $emefs_has_errors;
      $eme_timezone=get_option('timezone_string');
      if (!$eme_timezone) {
	      $offset = get_option('gmt_offset');
	      $eme_timezone = timezone_name_from_abbr(null, $offset * 3600, false);
	      if($eme_timezone === false) $eme_timezone = timezone_name_from_abbr(null, $offset * 3600, true);
      }
      $eme_timezone_obj = new DateTimeZone($eme_timezone);

      $eme_date_obj=new DateTime(null,$eme_timezone_obj);

      if (!$this->settings->options['success_page']) {
         return false;
      }

      if (isset($_POST['event']) && isset($_POST['new-event']) && wp_verify_nonce($_POST['new-event'], 'action_new_event')) {

         $hasErrors = false;

         $event_data = stripslashes_deep($_POST['event']);
         // add in the event_attributes and properties
         if (isset($_POST['event_attributes']) && !empty($_POST['event_attributes'])) {
            $event_data['event_attributes'] = stripslashes_deep($_POST['event_attributes']);
         }
         if (isset($_POST['event_properties']) && !empty($_POST['event_properties'])) {
            $event_data['event_properties'] = stripslashes_deep($_POST['event_properties']);
         }

         if ($this->settings->options['use_captcha']) {
            $captcha_err = eme_check_captcha('captcha_check','emefs_add_event');
            if (!empty($captcha_err))
               $emefs_event_errors['captcha'] = $captcha_err;
         }
         if ( !isset($event_data['event_name']) || empty($event_data['event_name']) ) { 
            $emefs_event_errors['event_name'] = __('Please enter a name for the event', 'events-made-easy-frontend-submit');
         }

         if ( !isset($event_data['event_start_date']) || empty($event_data['event_start_date']) ) { 
            $emefs_event_errors['event_start_date'] = __('Enter the event\'s start date', 'events-made-easy-frontend-submit');
         }

         if ( isset($event_data['event_start_time']) && !empty($event_data['event_start_time']) ) { 
            $event_data['event_start_time'] = $eme_date_obj->setTimestamp(strtotime($event_data['event_start_time'].' '.$eme_timezone))->format('H:i:00');
         } else {
            $event_data['event_start_time'] = '00:00';
         }

         if ( isset($event_data['event_end_time']) && !empty($event_data['event_end_time']) ) { 
            $event_data['event_end_time'] = $eme_date_obj->setTimestamp(strtotime($event_data['event_end_time'].' '.$eme_timezone))->format('H:i:00');
         } else {
            $event_data['event_end_time'] = $event_data['event_start_time'];
         }

         if ( !isset($event_data['event_end_date']) || empty($event_data['event_end_date']) ) { 
            $event_data['event_end_date'] = $event_data['event_start_date'];
         }

         $time_start = $eme_date_obj->setTimestamp(strtotime($event_data['event_start_date'].' '.$event_data['event_start_time'].' '.$eme_timezone))->getTimestamp();
         $time_end = $eme_date_obj->setTimestamp(strtotime($event_data['event_end_date'].' '.$event_data['event_end_time'].' '.$eme_timezone))->getTimestamp();

         if(!$time_start){
            $emefs_event_errors['event_start_time'] = __('Check the start date and time', 'events-made-easy-frontend-submit');
         }

         if(!$time_end){
            $emefs_event_errors['event_end_time'] =  __('Check the end date and time', 'events-made-easy-frontend-submit');
         }

         if($time_start && $time_end && $time_start > $time_end){
            $emefs_event_errors['event_time'] =  __('The end date/time must occur <strong>after</strong> the start date/time', 'events-made-easy-frontend-submit');
         }

         foreach ($emefs_event_errors as $error) {
            if($error){
               $emefs_has_errors = true;
               break;
            }	
         }

         // after submit, not all event fields are present, so we will merge the submitted data with a new event
         $new_event = eme_new_event();

         if ( !$emefs_has_errors ) {

            $force=0;
            if ($this->settings->options['force_location_creation'])
               $force=1;
	    if (!$event_data['location_id'])
		    $event_data = self::processLocation($event_data, $force);

            $emefs_event_data_compiled = array_merge($emefs_event_data, $event_data);
            $emefs_event_data_compiled = array_merge($new_event,$emefs_event_data_compiled);
            //unset($emefs_event_data_compiled['action']);

            foreach ($emefs_event_data_compiled as $key => $value) {
               // location info is not part of the event
               if (strpos($key,'location') !== false && $key != 'location_id') {
                  unset($emefs_event_data_compiled[$key]);
               }
               // localized info is not part of the event
               if (strpos($key,'localized-') !== false) {
                  unset($emefs_event_data_compiled[$key]);
               }
            }
            if (!$emefs_event_data_compiled['event_category_ids'] && $this->settings->options['default_cat'])
		$emefs_event_data_compiled['event_category_ids']=$this->settings->options['default_cat'];

            if ($this->settings->options['auto_publish']) {
               $emefs_event_data_compiled['event_status'] = $this->settings->options['auto_publish'];
            }

            if (is_user_logged_in()) {
               $current_userid=get_current_user_id();
               $emefs_event_data_compiled['event_author'] = $current_userid;
            }

            $emefs_event_data_compiled = eme_sanitize_event($emefs_event_data_compiled);
            $validation_result = eme_validate_event ( $emefs_event_data_compiled );
            if ($validation_result == "OK") {
               if ($event_id = eme_db_insert_event($emefs_event_data_compiled)) {
	          if ($this->settings->options['always_success_page']) {
                     wp_redirect(get_permalink($this->settings->options['success_page']));
	          } elseif (is_user_logged_in() && $this->settings->options['auto_publish']!=STATUS_DRAFT) {
                     wp_redirect(html_entity_decode(eme_event_url(eme_get_event($event_id))));
                  } elseif (!is_user_logged_in() && $this->settings->options['auto_publish']==STATUS_PUBLIC) {
                     wp_redirect(html_entity_decode(eme_event_url(eme_get_event($event_id))));
                  } else {
                     wp_redirect(get_permalink($this->settings->options['success_page']));
                  }
                  exit;
               } else {
                  $emefs_has_errors = true;
               }
            } else {
               $emefs_has_errors = true;
            }

         } else {
            $emefs_event_data = array_merge($emefs_event_data, $event_data);	
            $emefs_event_data = array_merge($new_event,$emefs_event_data);
         }
      }
   }

   /* 
      Process the data for a new location
    */
   public static function processLocation($event_data, $force=0) {
      $location = eme_new_location();
      $location['location_name'] = isset($event_data['location_name']) ? $event_data['location_name'] : '';
      $location['location_address1'] = isset($event_data['location_address1']) ? $event_data['location_address1'] : '';
      $location['location_address2'] = isset($event_data['location_address2']) ? $event_data['location_address2'] : '';
      $location['location_city'] = isset($event_data['location_city']) ? $event_data['location_city'] : '';
      $location['location_state'] = isset($event_data['location_state']) ? $event_data['location_state'] : '';
      $location['location_zip'] = isset($event_data['location_zip']) ? $event_data['location_zip'] : '';
      $location['location_country'] = isset($event_data['location_country']) ? $event_data['location_country'] : '';
      $location['location_latitude'] = $event_data['location_latitude'];
      $location['location_longitude'] = $event_data['location_longitude'];
      // for backwards compatibility
      if (isset($event_data['location_address'])) {
         $location['location_address1'] = $event_data['location_address1'];
         unset($event_data['location_address']);
      }
      if (isset($event_data['location_town'])) {
         $location['location_city'] = $event_data['location_town'];
         unset($event_data['location_town']);
      }
      $location = eme_sanitize_location($location);
      $location_id=eme_get_identical_location_id($location);
      if (!$location_id ) {
         $validation_result = eme_validate_location ( $location );
         if ($validation_result == "OK") {
            $location = eme_insert_location($location, $force);
            $event_data['location_id'] = $location['location_id'];
         }
      } else {
         $event_data['location_id'] = $location_id;
      }
      return $event_data;
   }

   /*
      Print out the Submitting Form
    */
   function deployForm($atts, $content) {
      global $emefs_event_errors, $emefs_event_data;
      
      extract ( shortcode_atts ( array ('template' => "form.php"), $atts));
      $template=basename($template);

      if (empty($emefs_event_data)) {
            if (function_exists('eme_new_event')) {
               $emefs_event_data=eme_new_event()+eme_new_location();
               $emefs_event_data["event_status"] = 5;
               // the following 2 fields are not a part of an event, but are needed to prevent php notice errors when showing the form for the first time
               $emefs_event_data["localized-start-date"] = '';
               $emefs_event_data["localized-end-date"] = '';
            } else {
            ?>
               <div class="emefs_error">
               <h2><?php _e('Events Made Easy plugin missing', 'events-made-easy-frontend-submit'); ?></h2>
               <p><?php _e("This plugin requires the plugin 'Events Made Easy' to be installed.", 'events-made-easy-frontend-submit'); ?></p>
               </div>
            <?php
            }
      }
      if (!$this->settings->options['success_page']) {
      ?>
            <div class="emefs_error">
            <h2><?php _e('Basic Configuration is Missing', 'events-made-easy-frontend-submit'); ?></h2>
            <p><?php _e('You have to configure the page where successful submissions will be redirected to.', 'events-made-easy-frontend-submit'); ?></p>
            </div>
      <?php
            return false;
      }

      if (!$this->settings->options['guest_submit'] && !$this->settings->options['guest_not_allowed_page']) {
      ?>
            <div class="emefs_error">
            <h2><?php _e('Basic Configuration is Missing', 'events-made-easy-frontend-submit'); ?></h2>
            <p><?php _e('Since you have chosen not to accept guest submissions, you have to configure the page where to redirect unauthorized users.', 'events-made-easy-frontend-submit'); ?></p>
            </div>
      <?php
            return false;
      }

      $upload_dir_arr=wp_upload_dir();
      $upload_dir=$upload_dir_arr['basedir'];
      if (file_exists("$upload_dir/events-made-easy-frontend-submit/".$template)) {
         $filename="$upload_dir/events-made-easy-frontend-submit/".$template;
      } else {
         $filename = locate_template(array(
               'eme-frontend-submit/'.$template,
               'events-made-easy-frontend-submit/'.$template,
               'events-made-easy/'.$template,
               'emefs/'.$template
               ));
         if (empty($filename)) {
            $filename = EMEFS_PLUGIN_DIR.'templates/form.php';
         }
      }
      // check if the user wants AM/PM or 24 hour notation
      // make sure that escaped characters are filtered out first
      $time_format = preg_replace('/\\\\./','',get_option('time_format'));
      $show24Hours = 'true';
      if (preg_match ( "/g|h/", $time_format ))
         $show24Hours = 'false';
      $gmap_enabled=1;
      if (!$this->settings->options['gmap_enabled'])
         $gmap_enabled=0;

      $form_templ=file_get_contents($filename);
      if ($this->settings->options['use_captcha']) {
	 if (!strstr($form_templ,"EMEFS::field('captcha')") && !strstr($form_templ,'EMEFS::field("captcha")')) {
	    return "<span class='error'>".__('Error: captcha required in settings, but not used in the form template','events-made-easy-frontend-submit')."</span>";
	 }
      } else {
	 if (strstr($form_templ,"EMEFS::field('captcha')") || strstr($form_templ,'EMEFS::field("captcha")')) {
	    return "<span class='error'>".__('Error: captcha not required in settings, but used in the form template','events-made-easy-frontend-submit')."</span>";
	 }
      }
      unset($form_templ);
            
      ob_start();
      require($filename);
      ?>
      <script type="text/javascript">
         jQuery(document).ready( function(){
               emefs_gmap_enabled = <?php echo $gmap_enabled; ?>;
               show24Hours = <?php echo $show24Hours; ?>;
               emefs_gmap_hasSelectedLocation = <?php echo ($emefs_event_data['location_id'])?'1':'0'; ?>;
               emefs_deploy(show24Hours, emefs_gmap_enabled, emefs_gmap_hasSelectedLocation);
               });
      </script>
      <?php
      $form = ob_get_clean();
      return $form;
   }

   /*
      Print fields which act as security and blocking methods
      preventing unwanted submitions.
    */
   public static function end_form($submit = 'Submit Event') {
      echo sprintf('<input type="submit" value="%s" id="submit" />', __($submit));
      wp_nonce_field( 'action_new_event', 'new-event' );
   }

   /*
      Print event attribute fields (type: text, email, tel, url)
    */
   public static function attribute($field_id = false,$type = 'textarea',$more='') {
      if (!$field_id)
         return false;
      EMEFS::field('event_attributes','attr-'.$type,$field_id,$more);
   }
   public static function required_attribute($field_id = false,$type = 'text',$more='') {
      EMEFS::attribute($field_id,$type,$more." required='required'");
   }

   /*
      Print event property fields (type: text, email, tel, url)
    */
   public static function property($field_id = false,$type = 'text',$more='') {
      if (!$field_id)
         return false;
      EMEFS::field('event_properties','prop-'.$type,$field_id,$more);
   }
   public static function required_property($field_id = false,$type = 'text',$more='') {
      EMEFS::property($field_id,$type,$more." required='required'");
   }

   /*
      Print event data fields (not location data)
    */
   public static function required_field($field = false, $type = 'text', $field_id = false, $more = '') {
      EMEFS::field($field, $type, $field_id, $more." required='required'");
   }

   public static function field($field = false, $type = 'text', $field_id = false, $more = '') {
      global $emefs_event_data;

      //if (!$field || !isset($emefs_event_data[$field]))
      if (!$field)
         return false;

      if (is_array($field)) {
         $field = $field[0];
         $context = $field[1]; 
      } else {
         $context = 'event';
      }

      $localized_field_id='';
      $settings = new EMEFS_Settings();
      switch($field) {
         case 'event_notes':
            if ($settings->options['use_wysiwyg'])
               $type = 'wysiwyg_textarea';
            else
               $type = 'textarea';
            break;
         case 'event_category_ids':
            $type = ($type != 'radio')?'category_select':'category_radio';
            break;
         case 'event_rsvp':
            $type = 'binary';
            break;
         case 'location_latitude':
         case 'location_longitude':
            $type = 'hidden';
            break;
         case 'event_start_date':
            $localized_field_id='localized-start-date';
            $more = "required='required'";
            $type = 'localized_text';
            break;
         case 'event_end_date':
            $localized_field_id='localized-end-date';
            $type = 'localized_text';
            break;
         case 'event_name':
            $more = "required='required'";
            $type = 'text';
            break;
         case 'event_attributes':
         case 'event_properties':
            break;
         case 'captcha':
            if ($settings->options['use_captcha'])
               $type = 'captcha';
            else
               $type = '';
            break;
         case 'event_image_url':
         case 'event_url':
            $type = ($type != 'url')?'text':'url';
         default:
            $type = 'text';
      }

      $html_by_type = array(
            'number' => '<input type="number" id="%s" name="event[%s]" min="0" step="any" value="%s" %s/>',
            'text' => '<input type="text" id="%s" name="event[%s]" value="%s" %s/>',
            'url' => '<input type="url" id="%s" name="event[%s]" value="%s" %s/>',
            'localized_text' => '<input type="text" id="%s" name="%s" value="%s" %s/>',
            'textarea' => '<textarea id="%s" name="event[%s]" %s>%s</textarea>',
            'hidden' => '<input type="hidden" id="%s" name="event[%s]" value="%s" %s />',
            'attr-textarea' => '<textarea id="%s" name="event_attributes[%s]" %s>%s</textarea>',
            'attr-text' => '<input type="text" id="%s" name="event_attributes[%s]" value="" %s />',
            'attr-tel' => '<input type="tel" id="%s" name="event_attributes[%s]" value="" %s />',
            'attr-email' => '<input type="email" id="%s" name="event_attributes[%s]" value="" %s />',
            'prop-text' => '<input type="text" id="%s" name="event_properties[%s]" value="" %s />'
            );

      $field_id = ($field_id)?$field_id:$field;

      switch($type) {
         case 'wysiwyg_textarea':
            if ($settings->options['allow_upload'])
		    $editor_settings=array('media_buttons'=>true,'textarea_name'=>"event[$field]");
	    else
		    $editor_settings=array('media_buttons'=>false,'textarea_name'=>"event[$field]");
            wp_editor($emefs_event_data[$field],$field_id,$editor_settings);
            break;
         case 'textarea':
            echo sprintf($html_by_type[$type], $field_id, $field, $more, $emefs_event_data[$field]);
            break;
         case 'number':
         case 'text':
         case 'hidden':
         case 'url':
            // for backwards compatibility
            if ($field == "location_address") $field="location_address1";
            if ($field == "location_town") $field="location_city";
            echo sprintf($html_by_type[$type], $field_id, $field, $emefs_event_data[$field], $more);
            //echo sprintf($html_by_type[$type], $field_id, $field, '', $more);
            break;
         case 'localized_text':
            //echo sprintf($html_by_type['hidden'], $field_id, $field, '', $more);
            echo sprintf($html_by_type['hidden'], $field_id, $field, $emefs_event_data[$field], $more);
            echo sprintf($html_by_type[$type], $localized_field_id, "event[$localized_field_id]", $emefs_event_data[$localized_field_id], $more);
            break;
         case 'category_select':
            echo self::getCategoriesSelect($more);
            break;
         case 'category_radio':
            echo self::getCategoriesRadio($more);
            break;
         case 'captcha':
            echo self::getCaptcha();
            break;
         case 'binary':
            echo self::getBinarySelect($field,$field_id,0);
            break;
         case 'prop-binary':
            echo self::getBinarySelect("event_properties[".$field_id."]",$field_id,0);
            break;
	 case 'attr-textarea':
            if (!isset($emefs_event_data['event_attributes'][$field_id])) $emefs_event_data['event_attributes'][$field_id]='';
            echo sprintf($html_by_type[$type], $field_id, $field_id, $more, $emefs_event_data['event_attributes'][$field_id]);
            break;
         case 'prop-text':
         case 'attr-text':
         case 'attr-tel':
         case 'attr-email':
         case 'attr-number':
            echo sprintf($html_by_type[$type], $field_id, $field_id, $more);
            break;
      }
   }

   /*
      Print event data fields error messages (not location data)
    */
   public static function error($field = false, $html = '<span class="error">%s</span>') {
      global $emefs_event_errors;
      if (!$field || !isset($emefs_event_errors[$field]))
         return false;
      echo sprintf($html, $emefs_event_errors[$field]);
   }

   /*
      Wrapper function to get categories form eme
    */
   public static function getCategories() {
      $categories = eme_get_categories();
      if (has_filter('emefs_categories_filter')) $categories=apply_filters('emefs_categories_filter',$categories);
      return($categories);
   }

   /*
      Function that creates and returns a radio input set from the existing categories
    */
   public static function getCategoriesRadio($more) {
      global $emefs_event_data;

      $categories = self::getCategories();
      $category_radios = array();
      if ( $categories ) {
         // the first value should be empty, so if it is required, the browser can require it ...
         $category_radios[] = '<input type="hidden" name="event[event_category_ids]" value="0" '.$more.' />';
         foreach ($categories as $category){
            $checked = ($emefs_event_data['event_category_ids'] == $category['category_id'])?'checked="checked"':'';
            $category_radios[] = sprintf('<input type="radio" id="event_category_ids_%s" value="%s" name="event[event_category_ids]" %s />', $category['category_id'], $category['category_id'], $checked);
            $category_radios[] = sprintf('<label for="event_category_ids_%s">%s</label><br/>', $category['category_id'], $category['category_name']);
         }
      }

      return implode("\n", $category_radios);	
   }

   /*
      Print what self::getCategoriesRadio returns
    */
   public static function categoriesRadio() {
      echo self::getCategoriesRadio();
   }

   /*
      Function that creates and returns a select input set from the existing categories
    */
   public static function getCategoriesSelect($more) {
      global $emefs_event_data;

      $category_select = array();
      $category_select[] = '<select id="event_category_ids" name="event[event_category_ids]" '.$more.' >';
      $categories = self::getCategories();
      if ( $categories ) {
         // the first value should be empty, so if it is required, the browser can require it ...
         $category_select[] = '<option value="">&nbsp;</option>';
         foreach ($categories as $category){
            $selected = ($emefs_event_data['event_category_ids'] == $category['category_id'])?'selected="selected"':'';
            $category_select[] = sprintf('<option value="%s" %s>%s</option>', $category['category_id'], $selected, $category['category_name']);
         }
      }
      $category_select[] = '</select>';
      return implode("\n", $category_select);
   }

   /*
     Function that shows a captcha field
   */
   public static function getCaptcha() {
      $captcha_url=eme_captcha_url("emefs_add_event");
      return "<img src='$captcha_url'><br /><input required='required' type='text' name='captcha_check' autocomplete='off' />";
   }

   /*
     Function that shows a yes/no field
   */
   public static function getBinarySelect($name,$field_id,$default) {
      $val = "<select name='$name' id='$field_id'>";
      $selected_YES="";
      $selected_NO="";
      if ($default==1)
         $selected_YES = "selected='selected'";
      else
         $selected_NO = "selected='selected'";
      $val.= "<option value='0' $selected_NO>".__('No', 'events-made-easy')."</option>";
      $val.= "<option value='1' $selected_YES>".__('Yes', 'events-made-easy')."</option>";
      $val.=" </select>";
      return $val;
   }

   /*
      Sets up style and scripts assets the plugin uses
    */
   public static function registerAssets() {

      wp_register_script( 'jquery-plugin', EME_PLUGIN_URL.'js/jquery-datepick/jquery.plugin.min.js');
      wp_register_script( 'jquery-datepick',EME_PLUGIN_URL.'js/jquery-datepick/jquery.datepick.js',array( 'jquery','jquery-plugin' ));
      wp_register_script( 'jquery-mousewheel', EME_PLUGIN_URL.'js/jquery-mousewheel/jquery.mousewheel.min.js', array('jquery'));
      wp_register_script( 'jquery-timeentry', EME_PLUGIN_URL.'js/timeentry/jquery.timeentry.js', array('jquery','jquery-plugin','jquery-mousewheel'));

      // we don't have '$this' here yet, so get the option values the other way
      $settings = new EMEFS_Settings();
      if ($settings->options['gmap_enabled']) {
         $gmap_api_key = $settings->options['gmap_api_key'];
         if (!empty($gmap_api_key)) $gmap_api_key="key=$gmap_api_key";
             wp_register_script( 'google-maps', '//maps.google.com/maps/api/js?'.$gmap_api_key);
         wp_register_script( 'emefs', EMEFS_PLUGIN_URL.'js/emefs.js', array('jquery-datepick', 'jquery-timeentry', 'jquery-ui-autocomplete', 'google-maps'));
      } else {
         wp_register_script( 'emefs', EMEFS_PLUGIN_URL.'js/emefs.js', array('jquery-datepick', 'jquery-timeentry', 'jquery-ui-autocomplete'));
      }
      $upload_dir_arr=wp_upload_dir();
      $upload_dir=$upload_dir_arr['basedir'];
      if (file_exists("$upload_dir/events-made-easy-frontend-submit/style.css")) {
         $style_filename=$upload_dir_arr['baseurl']."/events-made-easy-frontend-submit/style.css";
      } else {
         $style_filename = locate_template(array(
               'eme-frontend-submit/style.css',
               'events-made-easy-frontend-submit/style.css',
               'emefs/style.css',
               'events-made-easy/style.css'
               ));

         if(empty($style_filename)){
            $style_filename = EMEFS_PLUGIN_URL.'templates/style.css';
         }else{
            $style_filename = get_bloginfo('url').'/'.str_replace(ABSPATH, '', $style_filename);
         }
      }

      wp_register_style( 'emefs', $style_filename );
      wp_register_style( 'emefs-internal', EMEFS_PLUGIN_URL.'templates/style.internal.css');
      wp_register_style('jquery-datepick', EME_PLUGIN_URL.'js/jquery-datepick/jquery.datepick.css');
   }

   /*
      Deliver scripts for output on the theme 
    */
   public static function printScripts() {
      if (!is_admin()) {
         // jquery ui locales are with dashes, not underscores
         $locale_code = get_locale();
         $locale_code = preg_replace( "/_/","-", $locale_code );
         $firstDayOfWeek = get_option('start_of_week');

         // Now we can localize the script with our data.
         // in our case: replace in the registered script "emefs" the string emefs.locale by $locale_code, and emefs.firstDayOfWeek by $firstDayOfWeek
         $translation_array = array( 'locale' => $locale_code, 'firstDayOfWeek' => $firstDayOfWeek, 'ajax_url' => admin_url( 'admin-ajax.php' ) );
         wp_localize_script( 'emefs', 'emefs', $translation_array );
         wp_enqueue_script( 'emefs' );

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
               wp_enqueue_script('jquery-datepick-locale',$locale_file_url);
         }
      }
   }

   /*
      Deliver styles for output on the theme 
    */
   public static function printStyles() {
      if (!is_admin()) {
         wp_enqueue_style('emefs');
         wp_enqueue_style('emefs-internal');
         wp_enqueue_style('jquery-datepick');
      }
   }

}
endif;

// Initialize our plugin object.
global $emefs;
if (class_exists("EMEFS") && !$emefs) {
   $emefs = new EMEFS();
}


add_action( 'wp_ajax_emefs_locations_list', 'emefs_ajax_locations_list' );
function emefs_ajax_locations_list() {
	if (!isset($_POST["q"])) {
		echo json_encode($res);
		return;
	}

	$locations = eme_search_locations($_POST["q"]);
	$res = array();

	foreach($locations as $item) {
		$record = array();
		$record['id']       = $item['location_id'];
		$record['name']     = eme_trans_sanitize_html($item['location_name']); 
		$record['address1'] = eme_trans_sanitize_html($item['location_address1']);
		$record['address2'] = eme_trans_sanitize_html($item['location_address2']);
		$record['city']     = eme_trans_sanitize_html($item['location_city']); 
		$record['state']    = eme_trans_sanitize_html($item['location_state']); 
		$record['zip']      = eme_trans_sanitize_html($item['location_zip']); 
		$record['country']  = eme_trans_sanitize_html($item['location_country']); 
		$record['latitude'] = eme_trans_sanitize_html($item['location_latitude']); 
		$record['longitude']= eme_trans_sanitize_html($item['location_longitude']); 
		$res[]  = $record;
	}

	print json_encode($res);
	wp_die();
}
?>
