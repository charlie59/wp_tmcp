<?php
if (!function_exists('is_admin')) {
   header('Status: 403 Forbidden');
   header('HTTP/1.1 403 Forbidden');
   exit();
}

if (!class_exists("EMEFS_Settings")) :

   class EMEFS_Settings {

      public static $default_settings = 
         array(   
               'auto_publish' => STATUS_PUBLIC,
               'guest_submit' => false,
               'success_page' => 0,
               'always_success_page' => 0,
               'default_cat' => 0,
               'guest_not_allowed_page' => 0,
               'force_location_creation' => 0,
               'use_captcha' => 0,
               'use_wysiwyg' => 0,
               'allow_upload' => 0,
               'gmap_enabled' => 1,
               'gmap_api_key' => ''
              );
      var $pagehook, $page_id, $settings_field, $options;


      function __construct() {   
         $this->page_id = 'emefs';
         // This is the get_options slug used in the database to store our plugin option values.
         $this->settings_field = 'emefs_options';
         $this->options = get_option( $this->settings_field );
         if (!is_array($this->options)) {
             $this->options=emefs_settings::$default_settings;
         } else {
             $this->options=array_merge(emefs_settings::$default_settings,$this->options);
         }

         add_action('admin_init', array($this,'admin_init'), 20 );
         add_action( 'admin_menu', array($this, 'admin_menu'), 20);
      }

      function admin_init() {
         register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
         add_option( $this->settings_field, emefs_settings::$default_settings );
      }

      function admin_menu() {
         if ( ! current_user_can('update_plugins') )
            return;

         // Add a new submenu to the standard Settings panel
         $this->pagehook = $page =  add_options_page( 
               __('EME Frontend Submit', 'events-made-easy-frontend-submit'), __('EME Frontend Submit', 'events-made-easy-frontend-submit'), 
               'administrator', $this->page_id, array($this,'render') );

         // Executed on-load. Add all metaboxes.
         add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

         // Include js, css, or header *only* for our settings page
         add_action("admin_print_scripts-$page", array($this, 'js_includes'));
         //    add_action("admin_print_styles-$page", array($this, 'css_includes'));
         add_action("admin_head-$page", array($this, 'admin_head') );
      }

      function admin_head() { ?>
         <style>
            .settings_page_emefs_submit label { display:inline-block; width: 150px; }
         </style>

            <?php }


      function js_includes() {
         // Needed to allow metabox layout and close functionality.
         wp_enqueue_script( 'postbox' );
      }


      /*
         Sanitize our plugin settings array as needed.
       */ 
      function sanitize_theme_options($options) {
         //$options['example_text'] = stripcslashes($options['example_text']);
         return $options;
      }


      /*
         Settings access functions.

       */
      protected function get_field_name( $name ) {
         return sprintf( '%s[%s]', $this->settings_field, $name );
      }

      protected function get_field_id( $id ) {
         return sprintf( '%s[%s]', $this->settings_field, $id );
      }

      protected function get_field_value( $key ) {
         return $this->options[$key];
      }


      /*
         Render settings page.

       */

      function render() {
         global $wp_meta_boxes;

         $title = __('Events Made Easy Frontend Submit Settings', 'events-made-easy-frontend-submit');
         ?>
            <div class="wrap">   
            <h2><?php echo esc_html( $title ); ?></h2>

            <form method="post" action="options.php">

            <div class="metabox-holder">
            <div class="postbox-container" style="width: 99%;">
            <?php 
            // Render metaboxes
            settings_fields($this->settings_field); 
            do_meta_boxes( $this->pagehook, 'main', null );
            if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
               do_meta_boxes( $this->pagehook, 'column2', null );
         ?>
            </div>
            </div>

            <p>
            <input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
            </p>
            </form>
            </div>

            <!-- Needed to allow metabox layout and close functionality. -->
            <script type="text/javascript">
            //<![CDATA[
            jQuery(document).ready( function ($) {
                  // close postboxes that should be closed
                  $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                  // postboxes setup
                  postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
                  });
         //]]>
         </script>
            <?php }


      function metaboxes() {
         // Example metabox containing two example checkbox controls.
         // Also includes and example input text box, rendered in HTML in the condition_box function
         add_meta_box( 'emefs-conditions', __( 'Events Made Easy Frontend Submit Settings', 'events-made-easy-frontend-submit' ), array( $this, 'condition_box' ), $this->pagehook, 'main' );
      }

      function condition_box() {
	      $categories=eme_get_categories();
	      $category_arr=array();
	      $category_arr[0]='';
	      if ( $categories ) {
		      // the first value should be empty, so if it is required, the browser can require it ...
		      foreach ($categories as $category){
			      $category_arr[$category['category_id']] = $category['category_name'];
		      }
	      }

         ?>
          <table class="form-table">
          <?php
          eme_options_select (__('State for new event','events-made-easy-frontend-submit'), $this->get_field_name('auto_publish'), eme_status_array(), __ ('The state for a newly submitted event.','events-made-easy-frontend-submit'), $this->get_field_value('auto_publish') );
          eme_options_select (__('Default category for new event','events-made-easy-frontend-submit'), $this->get_field_name('default_cat'), $category_arr, __ ('The default category assigned to an event if nothing is selected in the form.','events-made-easy-frontend-submit'), $this->get_field_value('default_cat') );
          eme_options_radio_binary (__('Force location creation?','events-made-easy-frontend-submit'), $this->get_field_name('force_location_creation'), __ ( 'Check this option if you want the location to be always created, even if the user does not have the needed capability set in EME to create locations.', 'events-made-easy-frontend-submit' ), $this->get_field_value('force_location_creation'));
          eme_options_radio_binary (__('Enable Google Maps integration?','events-made-easy-frontend-submit'), $this->get_field_name('gmap_enabled'), __ ( 'Check this option to enable Google Map integration', 'events-made-easy-frontend-submit' ), $this->get_field_value('gmap_enabled'));
          eme_options_input_text ( __('Google Maps API Key', 'events-made-easy-frontend-submit'), $this->get_field_name('gmap_api_key'), __( 'Your Google Maps API key, if needed. See <a href=https://developers.google.com/maps/documentation/javascript/get-api-key>Get Google Maps API Key</a> for more info..','events-made-easy-frontend-submit'), 'text', $this->get_field_value('gmap_api_key'));
          eme_options_radio_binary (__('Allow guest submit?','events-made-easy-frontend-submit'), $this->get_field_name('guest_submit'), __ ( 'Check this option if you want guests also to be able to add new events.', 'events-made-easy-frontend-submit' ), $this->get_field_value('guest_submit'));
          eme_options_select ( __ ( 'Success Page','events-made-easy-frontend-submit'), $this->get_field_name('success_page'), eme_get_all_pages (), __ ( 'The page a person will be redirected to after successfully submitting a new event if the person submitting the event has no right to see the newly submitted event.','events-made-easy-frontend-submit'), $this->get_field_value('success_page'));
          eme_options_radio_binary (__('Always show success page','events-made-easy-frontend-submit'), $this->get_field_name('always_success_page'), __ ( 'Check this option if you want to redirect to the success page even if the person submitting the event has the right to see the newly submitted event.', 'events-made-easy-frontend-submit' ), $this->get_field_value('always_success_page'));
          eme_options_select ( __ ( 'Guests not allowed page','events-made-easy-frontend-submit'), $this->get_field_name('guest_not_allowed_page'), eme_get_all_pages (), __ ( 'The page a guest will be redirected to when trying to submit a new event when they are not allowed to do so.','events-made-easy-frontend-submit'), $this->get_field_value('guest_not_allowed_page'));
          eme_options_radio_binary (__('Use captcha?','events-made-easy-frontend-submit'), $this->get_field_name('use_captcha'), __ ( 'Check this option to require the use of a captcha. The form used will need "EMEFS::field(\'captcha\');" in it (see the provided template examples), otherwise it will never get submitted.', 'events-made-easy-frontend-submit' ), $this->get_field_value('use_captcha'));
          eme_options_radio_binary (__('Use wysiwyg?','events-made-easy-frontend-submit'), $this->get_field_name('use_wysiwyg'), __ ( 'Check this option if you want to use a frontend wysiwyg editor for the event notes.', 'events-made-easy-frontend-submit' ), $this->get_field_value('use_wysiwyg'));
          eme_options_radio_binary (__('Allow image upload?','events-made-easy-frontend-submit'), $this->get_field_name('allow_upload'), __ ( 'Check this option if you want to allow image upload in the frontend wysiwyg editor for the event notes.', 'events-made-easy-frontend-submit' ), $this->get_field_value('allow_upload'));
          ?>
          </table>
          <?php
      }

   } // end class
endif;
?>
