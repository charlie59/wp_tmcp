<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_location() {
   $location = array(
   'location_name' => '',
   'location_address1' => '',
   'location_address2' => '',
   'location_city' => '',
   'location_state' => '',
   'location_zip' => '',
   'location_country' => '',
   'location_latitude' => '',
   'location_longitude' => '',
   'location_description' => '',
   'location_category_ids' => '',
   'location_url' => '',
   'location_slug' => '',
   'location_image_url' => '',
   'location_external_ref' => '',
   'location_image_id' => 0,
   'location_author' => 0,
   'location_attributes' => array(),
   'location_properties' => array()
   );

   $location['location_properties'] = eme_init_location_props($location['location_properties']);
   return $location;
}
 
function eme_init_location_props($props) {
   // can become more complex in the future
   return $props;
}

function eme_locations_page() {
   $current_userid=get_current_user_id();
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_location") { 
      check_admin_referer('eme_locations','eme_admin_nonce');
      $location_id = intval($_GET['location_id']);
      $location = eme_get_location($location_id);
      if (current_user_can( get_option('eme_cap_edit_locations')) ||
            (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
         // edit location
         eme_locations_edit_layout($location);
      } else {
         $message = __('You have no right to edit this location!','events-made-easy');
         eme_locations_table_layout($message);
      }
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "copy_location") { 
      check_admin_referer('eme_locations','eme_admin_nonce');
      $location_id = intval($_GET['location_id']);
      $location = eme_get_location($location_id);
      // make it look like a new location
      unset($location['location_id']);
      $location['location_name'].= __(" (Copy)",'events-made-easy');

      if (current_user_can( get_option('eme_cap_add_locations'))) {
         eme_locations_edit_layout($location);
      } else {
         $message = __('You have no right to copy this location!','events-made-easy');
         eme_locations_table_layout($message);
      }
    } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_location") { 
      check_admin_referer('eme_locations','eme_admin_nonce');
      if (current_user_can( get_option('eme_cap_add_locations'))) {
         $location = eme_new_location();
         eme_locations_edit_layout($location);
      } else {
         $message = __('You have no right to add a location!','events-made-easy');
         eme_locations_table_layout($message);
      }
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "delete_location" && isset($_POST['locations'])) { 
      check_admin_referer('eme_locations','eme_admin_nonce');
      $locations = $_POST['locations'];
      foreach($locations as $location_id) {
         $location = eme_get_location(intval($location_id));
         if (current_user_can( get_option('eme_cap_edit_locations')) ||
            (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
               eme_delete_location(intval($location_id));
         }
      }
      $message = __('Successfully deleted the selected locations.','events-made-easy');
      eme_locations_table_layout($message);
   } elseif (isset($_POST['eme_admin_action']) && ($_POST['eme_admin_action'] == "do_editlocation" || $_POST['eme_admin_action'] == "do_addlocation")) { 
      check_admin_referer('eme_locations','eme_admin_nonce');
      $action = $_POST['eme_admin_action'];
      if ($action == "do_editlocation")
         $orig_location=eme_get_location(intval($_POST['location_id']));

      if ($action == "do_addlocation" && !current_user_can( get_option('eme_cap_add_locations'))) {
         $message = __('You have no right to add a location!','events-made-easy');
         eme_locations_table_layout($message);
      } elseif ($action == "do_editlocation" && !(current_user_can( get_option('eme_cap_edit_locations')) ||
                  (current_user_can( get_option('eme_cap_author_locations')) && ($orig_location['location_author']==$current_userid)))) {
            $message = __('You have no right to edit this location!','events-made-easy');
            eme_locations_table_layout($message);
      } else {
         $location = eme_new_location();
         $post_vars=array('location_name','location_address1','location_address2','location_city','location_state','location_zip','location_country','location_url','location_image_url','location_image_id','location_slug','location_latitude','location_longitude');
         foreach ($post_vars as $post_var) {
            // sanitizing comes later
            if (isset($_POST[$post_var])) $location[$post_var]=stripslashes($_POST[$post_var]);
         }
         //switched to WP TinyMCE field
         $location['location_description'] = stripslashes($_POST['content']);

         if (isset ($_POST['location_category_ids']) && eme_array_integers($_POST['location_category_ids'])) {
            $location ['location_category_ids']= join(',',$_POST['location_category_ids']);
         } else {
            $location ['location_category_ids']="";
         }

	 $location_attributes = array();
	 for($i=1 ; isset($_POST["eme_attr_{$i}_ref"]) && trim($_POST["eme_attr_{$i}_ref"])!='' ; $i++ ) {
		 if(trim($_POST["eme_attr_{$i}_name"]) != '') {
			 $location_attributes[$_POST["eme_attr_{$i}_ref"]] = stripslashes($_POST["eme_attr_{$i}_name"]);
		 }
	 }
         $location['location_attributes'] = serialize($location_attributes);

         $location_properties = array();
         $location_properties = eme_init_location_props($location_properties);
         foreach($_POST as $key=>$value) {
            if (preg_match('/eme_loc_prop_(.+)/', $key, $matches)) {
               $location_properties[$matches[1]] = stripslashes($value);
            }
         }
         $location['location_properties'] = serialize($location_properties);

         $location = eme_sanitize_location($location);
         $validation_result = eme_validate_location($location);
         if ($validation_result == "OK") {
            if ($action == "do_addlocation") {
               $new_location = eme_insert_location($location);
               if ($new_location) {
                  $message = __('The location has been added.', 'events-made-easy'); 
               } else {
                  $message = __('There has been a problem adding the location.', 'events-made-easy'); 
               }      
            } elseif ($action == "do_editlocation") {      
               $location_id = $orig_location['location_id'];
               if (eme_update_location($location,$location_id)) {
                  $message = __('The location has been updated.', 'events-made-easy');
               } else {
                  $message = __('The location update failed.', 'events-made-easy');
               }
            }
            eme_locations_table_layout($message);
         } else {
            $message = $validation_result;
            eme_locations_edit_layout($location, $message);
         }
      }
   } else {
      // no action, just a locations list
      eme_locations_table_layout();
   }
}

function eme_locations_edit_layout($location, $message = "") {
   global $plugin_page;

   if (!isset($location['location_id']))
      $action="add";
   else
      $action="edit";
   $nonce_field = wp_nonce_field('eme_locations','eme_admin_nonce',false,false);
   ?>
   <div class="wrap">
      <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>
            
         <h1><?php if ($action=="add")
                     _e('Add location', 'events-made-easy');
                   else
                     _e('Edit location', 'events-made-easy');
             ?></h1>
         
         <?php if ($message != "") { ?>
            <div id="message" class="updated fade below-h1" style="background-color: rgb(255, 251, 204);">
               <p><?php  echo $message ?></p>
            </div>
         <?php } ?>
         <div id="ajax-response"></div>
   
         <form enctype="multipart/form-data" name="editloc" id="editloc" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>" class="validate">
         <?php echo $nonce_field; ?>
         <?php if ($action == "add") { ?>
         <input type="hidden" name="eme_admin_action" value="do_addlocation" />
         <?php } else { ?>
         <input type="hidden" name="eme_admin_action" value="do_editlocation" />
         <input type="hidden" name="location_id" value="<?php echo $location['location_id'] ?>" />
         <?php } ?>
         
         <!-- we need titlediv and title for qtranslate as ID -->
         <div id="titlediv" class="postbox">
            <h3>
               <?php _e('Location name', 'events-made-easy') ?>
            </h3>
            <div class="inside">
           <input name="location_name" id="title" type="text" required="required" value="<?php echo eme_esc_html($location['location_name']); ?>" size="40" />
           <?php if ($action=="edit") {
                    _e ('Permalink: ', 'events-made-easy');
                    echo trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_locations_prefix')).$location['location_id']."/";
                    $slug = $location['location_slug'] ? $location['location_slug'] : $location['location_name'];
                    $slug = untrailingslashit(eme_permalink_convert($slug));
           ?>
                    <input type="text" id="slug" name="location_slug" value="<?php echo $slug; ?>" /><?php echo user_trailingslashit(""); ?>
           <?php
                  }
           ?>
           </div>
         </div>
         <div id="loc_address" class="postbox">
            <h3>
               <?php _e('Location address', 'events-made-easy') ?>
            </h3>
            <div class="inside">
            <table><tr>
            <td><label for="location_address1"><?php _e('Address1', 'events-made-easy') ?></label></td>
            <td><input id="location_address1" name="location_address1" type="text" value="<?php echo eme_esc_html($location['location_address1']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_address2"><?php _e('Address2', 'events-made-easy') ?></label></td>
            <td><input id="location_address2" name="location_address2" type="text" value="<?php echo eme_esc_html($location['location_address2']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_city"><?php _e('City', 'events-made-easy') ?></label></td>
            <td><input name="location_city" id="location_city" type="text" value="<?php echo eme_esc_html($location['location_city']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_state"><?php _e('State', 'events-made-easy') ?></label></td>
            <td><input name="location_state" id="location_state" type="text" value="<?php echo eme_esc_html($location['location_state']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_zip"><?php _e('Zip', 'events-made-easy') ?></label></td>
            <td><input name="location_zip" id="location_zip" type="text" value="<?php echo eme_esc_html($location['location_zip']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_country"><?php _e('Country', 'events-made-easy') ?></label></td>
            <td><input name="location_country" id="location_country" type="text" value="<?php echo eme_esc_html($location['location_country']); ?>" size="40" /></td>
            </tr>
            </table>
            </div>
         </div>
                        
         <div id="loc_lat_long" class="postbox">
            <h3>
               <?php _e('Location latitude/longitude', 'events-made-easy') ?>
            </h3>
            <div class="inside">
            <table><tr>
            <td><label for="location_latitude"><?php _e('Latitude', 'events-made-easy') ?></label></td>
            <td><input id="location_latitude" name="location_latitude" type="text" value="<?php echo eme_esc_html($location['location_latitude']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="location_longitude"><?php _e('Longitude', 'events-made-easy') ?></label></td>
            <td><input id="location_longitude" name="location_longitude" type="text" value="<?php echo eme_esc_html($location['location_longitude']); ?>" size="40" /></td>
            </tr></table>
            </div>
         </div>

         <div id="loc_image" class="postbox">
            <h3>
               <?php _e('Location image', 'events-made-easy') ?>
            </h3>
            <div id="location_no_image" class="postarea">
              <?php _e('No image set','events-made-easy'); ?>
            </div>
            <div id="location_current_image" class="inside">
             <?php if (isset($location['location_image_url']) && !empty($location['location_image_url'])) {
                       echo "<img id='eme_location_image_example' src='".$location['location_image_url']."' width='200' />";
                       echo "<input type='hidden' name='location_image_url' id='location_image_url' value='".$location['location_image_url']."' />";
                    } else {
                       echo "<img id='eme_location_image_example' src='' alt='' width='200' />";
                       echo "<input type='hidden' name='location_image_url' id='location_image_url' />";
                    }
                    if (isset($location['location_image_id']) && !empty($location['location_image_id'])) {
                       echo "<input type='hidden' name='location_image_id' id='location_image_id' value='".$location['location_image_id']."' />";
                    } else {
                       echo "<input type='hidden' name='location_image_id' id='location_image_id' />";
                    }
                    // based on code found at http://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
              ?>

            </div>
            <div class="uploader">
                <input type="button" name="location_image_button" id="location_image_button" value="<?php _e ( 'Set a featured image', 'events-made-easy')?>" />
                <input type="button" id="eme_remove_old_image" name="eme_remove_old_image" value=" <?php _e ( 'Unset featured image', 'events-made-easy')?>" />
            </div>
        </div>
 
        <?php if(get_option('eme_categories_enabled')) :?>
        <div id="loc_category" class="postbox">
            <h3>
               <?php _e('Category', 'events-made-easy') ?>
            </h3>
            <div class="inside">
           <?php
           $categories = eme_get_categories();
           foreach ( $categories as $category) {
              if ($location['location_category_ids'] && in_array($category['category_id'],explode(",",$location['location_category_ids']))) {
                 $selected = "checked='checked'";
              } else {
                 $selected = "";
              }
           ?>
              <input type="checkbox" name="location_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo eme_trans_esc_html($category['category_name']); ?><br />
           <?php
           }
           ?>
            </div>
        </div>
        <?php endif; ?>

         <?php 
            $gmap_is_active = get_option('eme_gmap_is_active');
            if ($gmap_is_active) :
          ?>   
         <div id="loc_qtrans_warning" class="postbox"><?php 
               if (function_exists('qtrans_getLanguage') || function_exists('ppqtrans_getLanguage') || defined('ICL_LANGUAGE_CODE')) {
                  _e("Because qtranslate or a derivate is active, the title of the location might not update automatically in the balloon, so don't panic there.", 'events-made-easy');
               }
              ?>
         </div>
         <div class="postbox" id="eme-admin-map-not-found"><p><?php _e('Map not found','events-made-easy') ?></p></div>
         <div class="postbox" id="eme-admin-location-map"></div>
         <br style="clear:both;" />
         <?php endif; ?>
         <div class="postbox" id="loc_description">
            <h3>
               <?php _e('Location description', 'events-made-easy') ?>
            </h3>
            <div class="inside">
               <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
                  <!-- we need content for qtranslate as ID -->
                  <?php    $eme_editor_settings = eme_get_editor_settings(true,true,10);
			   wp_editor($location['location_description'],"content",$eme_editor_settings);
		  ?>
               </div>
               <?php _e('A description of the Location. You may include any kind of info here.', 'events-made-easy') ?>
            </div>
         </div>
     	 <?php if (get_option('eme_attributes_enabled')) { ?>
         <div id="loc_attributes" class="postbox">
            <h3>
               <?php _e ( 'Attributes', 'events-made-easy'); ?>
            </h3>
               <?php eme_attributes_form($location); ?>
         </div>
       <?php } ?>
         <div id="loc_external" class="postbox">
            <h3>
               <?php _e ( 'External link', 'events-made-easy'); ?>
            </h3>
            <div class="inside">
            <input name="location_url" id="location_url" type="text" value="<?php echo eme_esc_html($location['location_url']); ?>" size="40" />
            <p><?php _e ( 'If this is filled in, the single location URL will point to this url instead of the standard location page.', 'events-made-easy')?></p>
            </div>
         </div>
         <p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php if ($action=="add") _e('Add location', 'events-made-easy'); else _e('Update location', 'events-made-easy'); ?>" /></p>
      </div>
      </form>
   </div>
   <?php
}

function eme_locations_table_layout($message = "") {
   $locations = eme_get_locations();
   $nonce_field = wp_nonce_field('eme_locations','eme_admin_nonce',false,false);

   ?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>
         
         <?php if ($message != "") { ?>
            <div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);">
               <p><?php echo $message ?></p>
            </div>
         <?php } ?>

         <h1><?php _e('Add a new location', 'events-made-easy') ?></h1>
         <div class="wrap">
         <form id="locations-filter" method="post" action="<?php echo admin_url("admin.php?page=eme-locations"); ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_location" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add location', 'events-made-easy');?>" />
         </form>
         </div>

         <h1><?php _e('Locations', 'events-made-easy') ?></h1>
   <form action="#" method="post">
   <input type="text" name="search_name" id="search_name" size=10>
   <button id="LocationsLoadRecordsButton" class="button-secondary action"><?php _e('Filter location','events-made-easy'); ?></button>
   </form>

   <form action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteLocations"><?php _e ( 'Delete selected locations','events-made-easy'); ?></option>
   </select>
   <button id="LocationsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="LocationsTableContainer"></div>
   </form>
   </div>
   </div>

   <?php
}

function eme_search_locations($name) {
   global $wpdb;
   $table = $wpdb->prefix.LOCATIONS_TBNAME;
   // the LIKE needs "%", but for prepare to work, we need to escape % using %%
   // and then the prepare is a sprintf, so we need %s for the search string too
   // This results in 3 %-signs, but it is what it is :-)
   $query = "SELECT * FROM $table WHERE (location_name LIKE '%%%s%%') OR
           (location_description LIKE '%%%s%%') ORDER BY location_name";
   $sql=$wpdb->prepare($query,$name,$name);
   return $wpdb->get_results ( $sql, ARRAY_A );
}

function eme_get_locations($eventful = false, $scope="all", $category = '', $offset = 0) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $locations = array();

   // the filter list
   $location_id="";
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

   // for the query: we don't do "SELECT *" because the data returned from this function is also used in the function eme_global_map_json()
   // and some fields from the events table contain carriage returns, which can't be passed along
   // The function eme_global_map_json tries to remove these, but the data is not needed and better be safe than sorry
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? true : $eventful;
   if ($eventful) {
      $events = eme_get_events(0, $scope, "ASC", $offset, $location_id, $category);
      if ($events) {
         foreach ($events as $event) {
            $location_id=$event['location_id'];
            if ($location_id) {
               $this_location = eme_get_location($location_id);
               // the key is based on the location name first and the location id (if different locations have the same name)
               // using this method we can then sort on the name
               if ($this_location['location_name']!='')
                  $locations[$this_location['location_name'].$location_id]=$this_location;
            }
         }
         // sort on the key (name/id pair)
         ksort($locations);
      }
   } else {
      $conditions = array ();
      if (get_option('eme_categories_enabled')) {
         if (is_numeric($category)) {
            if ($category>0)
               $conditions [] = " FIND_IN_SET($category,location_category_ids)";
         } elseif ($category == "none") {
            $conditions [] = "event_category_ids=''";
         } elseif ( preg_match('/,/', $category) ) {
            $category = explode(',', $category);
            $category_conditions = array();
            foreach ($category as $cat) {
               if (is_numeric($cat) && $cat>0) {
                  $category_conditions[] = " FIND_IN_SET($cat,location_category_ids)";
               } elseif ($cat == "none") {
                  $category_conditions[] = " location_category_ids=''";
               }
            }
            $conditions [] = "(".implode(' OR', $category_conditions).")";
         } elseif ( preg_match('/\+/', $category) ) {
            $category = explode('+', $category);
            $category_conditions = array();
            foreach ($category as $cat) {
               if (is_numeric($cat) && $cat>0)
                  $category_conditions[] = " FIND_IN_SET($cat,location_category_ids)";
            }
            $conditions [] = "(".implode(' AND ', $category_conditions).")";
         }
         if (!empty($location_id)) {
            $location_ids=explode(',', $location_id);
            if (eme_array_integers($location_ids))
               $conditions [] = "(location_id IN ($location_id))";
         }
      }

      // extra conditions for authors: if we're in the admin itf, return only the locations for which you have the right to change anything
      $current_userid=get_current_user_id();
      if (is_admin() && !current_user_can( get_option('eme_cap_edit_locations')) && current_user_can( get_option('eme_cap_author_locations'))) {
         $conditions [] = "(location_author = $current_userid)";
      }

      $where = implode ( " AND ", $conditions );
      if ($where != "")
         $where = " AND " . $where;

      $sql = "SELECT * FROM $locations_table WHERE location_name != '' $where ORDER BY location_name";
      $locations = $wpdb->get_results($sql, ARRAY_A); 
      // don't forget the images (for the older locations that didn't use the wp gallery)
      foreach ($locations as $key=>$location) {
         foreach ($location as $key2=>$val) {
            if (is_null($val)) $location[$key2]='';
         }
         $locations[$key] = eme_get_extralocation_data($location);
      }
   }
   if (has_filter('eme_location_list_filter')) $locations=apply_filters('eme_location_list_filter',$locations);
   return $locations;
}

function eme_get_location($location_id=0) { 
   global $wpdb;

   if (!$location_id) {
      return eme_new_location();
   } else {
      $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
      $sql = $wpdb->prepare("SELECT * from $locations_table WHERE location_id = %d",$location_id);
      $location = $wpdb->get_row($sql, ARRAY_A);
      if (!$location)
         return eme_new_location();

      foreach ($location as $key=>$val) {
         if (is_null($val)) $location[$key]='';
      }
      $location = eme_get_extralocation_data($location);
      return $location;
   }
}

function eme_get_extralocation_data($location) {

      // don't forget the images (for the older locations that didn't use the wp gallery)
      if (empty($location['location_image_id']) && empty($location['location_image_url']))
         $location['location_image_url'] = eme_image_url_for_location_id($location['location_id']);

      if (is_serialized($location['location_attributes']))
         $location['location_attributes'] = @unserialize($location['location_attributes']);
      $location['location_attributes'] = (!is_array($location['location_attributes'])) ?  array() : $location['location_attributes'] ;

      if (is_serialized($location['location_properties']))
         $location['location_properties'] = @unserialize($location['location_properties']);
      $location['location_properties'] = (!is_array($location['location_properties'])) ?  array() : $location['location_properties'] ;
      $location['location_properties'] = eme_init_location_props($location['location_properties']);

      if (has_filter('eme_location_filter')) $location=apply_filters('eme_location_filter',$location);

      return $location;
}

function eme_get_city_location_ids($cities) {
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $location_ids = array();
   $conditions="";
   if ( is_array($cities) ) {
      $city_conditions = array();
      foreach ($cities as $city) {
         $city_conditions[] = " location_city = '".esc_sql($city)."'";
      }
      $conditions = "(".implode(' OR', $city_conditions).")";
   } elseif (!empty($cities)) {
      $conditions = " location_city = '".esc_sql($cities)."'";
   }
   if (!empty($conditions)) {
      $sql = "SELECT DISTINCT location_id FROM $locations_table WHERE ".$conditions;
      $location_ids = $wpdb->get_col($sql); 
   }
   return $location_ids;
}

function eme_get_country_location_ids($countries) {
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $location_ids = array();
   $conditions="";
   if ( is_array($countries) ) {
      $country_conditions = array();
      foreach ($countries as $country) {
         $country_conditions[] = " location_country = '".esc_sql($country)."'";
      }
      $conditions = "(".implode(' OR', $country_conditions).")";
   } elseif (!empty($countries)) {
      $conditions = " location_country = '".esc_sql($countries)."'";
   }
   if (!empty($conditions)) {
      $sql = "SELECT DISTINCT location_id FROM $locations_table WHERE ".$conditions;
      $location_ids = $wpdb->get_col($sql); 
   }
   return $location_ids;
}

function eme_image_url_for_location_id($location_id) {
   $image_basename= IMAGE_UPLOAD_DIR."/location-".$location_id;
   $image_baseurl= IMAGE_UPLOAD_URL."/location-".$location_id;
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

function eme_get_identical_location_id($location) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $prepared_sql=$wpdb->prepare("SELECT location_id FROM $locations_table WHERE location_name = %s AND location_address1 = %s AND location_address2 = %s AND location_city = %s AND location_state = %s AND location_zip = %s AND location_country = %s LIMIT 1", stripcslashes($location['location_name']), stripcslashes($location['location_address1']), stripcslashes($location['location_address2']),stripcslashes($location['location_city']),stripcslashes($location['location_state']),stripcslashes($location['location_zip']),stripcslashes($location['location_country']) );
   return $wpdb->get_var($prepared_sql);
}

function eme_sanitize_location($location) {
   // remove possible unwanted fields
   if (isset($location['location_id'])) {
      unset($location['location_id']);
   }

   // check all variables that need to be urls
   $url_vars = array('location_url','location_image_url');
   foreach ($url_vars as $url_var) {
      if (!empty($location[$url_var])) {
           //make sure url's have a correct prefix
	   $parsed = parse_url($location[$url_var]);
	   if (empty($parsed['scheme'])) {
              $scheme  = is_ssl() ? 'https://' : 'http://';
              $location[$url_var] = $scheme . ltrim($location[$url_var], '/');
           }
           //make sure url's are correctly escaped
	   $location[$url_var] = esc_url_raw ( $location[$url_var] ) ;
      }
   }

   if (empty($location['location_longitude']))
      $location['location_longitude'] = 0;
   if (empty($location['location_latitude']))
      $location['location_latitude'] = 0;

   if (!is_serialized($location['location_attributes']))
      $location['location_attributes'] = serialize($location['location_attributes']);
      
   if (!is_serialized($location['location_properties']))
      $location['location_properties'] = serialize($location['location_properties']);

   if (!empty($location['location_slug']))
	$location['location_slug'] = eme_permalink_convert(eme_strip_tags($location['location_slug']));
   else
	$location['location_slug'] = eme_permalink_convert(eme_strip_tags($location['location_name']));

   // some things just need to be integers, let's brute-force them
   $int_vars=array('location_image_id');
   foreach ($int_vars as $int_var) {
	   $location[$int_var]=intval($location[$int_var]);
   }

   return eme_strip_js(eme_strip_weird($location));
}

function eme_validate_location($location) {
   $location_required_fields = array("location_name" => __('The location name', 'events-made-easy'), "location_address1" => __('The location address', 'events-made-easy'), "location_city" => __('The location city', 'events-made-easy'));
   $troubles = "";
   if (empty($location['location_name'])) {
      $troubles .= "<li>".$location_required_fields['location_name'].__(" is missing!", 'events-made-easy')."</li>";
   }
   if (empty($location['location_longitude']) && empty($location['location_longitude'])) {
      if (empty($location['location_address1'])) {
         $troubles .= "<li>".$location_required_fields['location_address1'].__(" is missing!", 'events-made-easy')."</li>";
      }
      if (empty($location['location_city'])) {
         $troubles .= "<li>".$location_required_fields['location_city'].__(" is missing!", 'events-made-easy')."</li>";
      }
   }

   if (empty($troubles)) {
      return "OK";
   } else {
      $message = __('Ach, some problems here:', 'events-made-easy')."<ul>\n$troubles</ul>";
      return $message; 
   }
}

function eme_update_location($location,$location_id) {
   global $wpdb;
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;
   // never try to update a autoincrement value ...
   if (isset($location['location_id']))
      unset ($location['location_id']);

   $where=array('location_id' => $location_id);

   $wpdb->show_errors(true);
   if ($wpdb->update ( $table_name, $location, $where ) === false) {
      $wpdb->print_error();
      $wpdb->show_errors(false);
      return false;
   } else {
      $wpdb->show_errors(false);
      return true;
   }
}

function eme_insert_location($location,$force=0) {
   // the force parameter can be used to ignore capabilities for a user when inserting a location
   // the frontend submit plugin can use this
   global $wpdb;  
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME; 

   $location['location_creation_date']=current_time('mysql', false);
   $location['location_creation_date_gmt']=current_time('mysql', true);
   $current_userid=get_current_user_id();
   $location['location_author']=$current_userid;
   // never try to update a autoincrement value ...
   if (isset($location['location_id']))
      unset ($location['location_id']);

   if (current_user_can( get_option('eme_cap_add_locations')) || $force) {
      $wpdb->show_errors(true);
      if (!$wpdb->insert($table_name,$location)) {
         $wpdb->print_error();
         $wpdb->show_errors(false);
         return false;
      } else {
         $location_id = $wpdb->insert_id;
         $new_location = eme_get_location($location_id);
         $wpdb->show_errors(false);
         return $new_location;
      }
   } else {
      return false;
   }
}

function eme_delete_location($location_id) {
   global $wpdb;  

   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $table_name where location_id=%d",$location_id);
   $wpdb->query( $sql );

   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = $wpdb->prepare("UPDATE $events_table SET location_id=0 WHERE location_id = %d",$location_id);
   $wpdb->query($sql);

   $image_basename= IMAGE_UPLOAD_DIR."/location-".$location_id;
   eme_delete_image_files($image_basename);
}

function eme_location_has_events($location_id) {
   global $wpdb;  
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $condition = "AND event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $condition = "AND event_status=".STATUS_PUBLIC;
      }
   }

   $sql = "SELECT COUNT(event_id) FROM $events_table WHERE location_id = $location_id $condition";
   $affected_events = $wpdb->get_results($sql);
   return ($affected_events > 0);
}

function eme_global_map($atts) {
   global $eme_need_gmap_js, $eme_timezone;

   if (get_option('eme_gmap_is_active') == '1') {
      // the locations shortcode has been deteced, so we indicate
      // that we want the javascript in the footer as well
      $eme_need_gmap_js=1;
      extract(shortcode_atts(array(
                  'show_locations' => true,
                  'show_events' => false,
                  'eventful' => false,
                  'scope' => 'all',
                  'paging' => 0,
                  'category' => '',
                  'width' => 450,
                  'height' => 300,
                  'list_location' => 'after'
                  ), $atts));
      $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
      $show_events = ($show_events==="true" || $show_events==="1") ? true : $show_events;
      $show_locations = ($show_locations==="true" || $show_locations==="1") ? true : $show_locations;
      $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;
      $show_events = ($show_events==="false" || $show_events==="0") ? false : $show_events;
      $show_locations = ($show_locations==="false" || $show_locations==="0") ? false : $show_locations;

      $events_page_link = eme_get_events_page();
      $prev_text = "";
      $next_text = "";
      $scope_offset=0;
      // for browsing: if paging=1 and only for this_week,this_month or today
      if ($eventful && $paging==1) {
         $eme_date_obj=new ExpressiveDate(null,$eme_timezone);

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
            $eme_date_obj->modifyMonths($scope_offset);
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

         // to prevent going on indefinitely and thus allowing search bots to go on for ever,
         // we stop providing links if there are no more events left
         $older_events=eme_get_events ( 1, "--".$limit_start, "ASC", 0, $location['location_id'], $category);
         $newer_events=eme_get_events ( 1, "++".$limit_end, "ASC", 0, $location['location_id'], $category);
         if (count($older_events) == 0)
            $prev_text = "";
         if (count($newer_events) == 0)
            $next_text = "";
      }

      $id_base = preg_replace("/\D/","_",microtime(1));
      $id_base = rand()."_".$id_base;
      if (!preg_match('/\%$|px$/',$width)) $width=$width."px";
      if (!preg_match('/\%$|px$/',$height)) $height=$height."px";
      $result = "<div id='eme_global_map_$id_base' class='eme_global_map' style='width: $width; height: $height'>map</div>";
      // get the paging output ready
      if ($paging==1) {
         $pagination_top = "<div id='locations-pagination-top'> ";
         $this_page_url=$_SERVER['REQUEST_URI'];
         // remove the offset info
         $this_page_url= remove_query_arg('eme_offset',$this_page_url);
         if ($prev_text != "")
            $pagination_top.= "<a class='eme_nav_left' href='".add_query_arg(array('eme_offset'=>$prev_offset),$this_page_url)."'>&lt;&lt; $prev_text</a>";
         if ($next_text != "")
            $pagination_top.= "<a class='eme_nav_right' href='".add_query_arg(array('eme_offset'=>$next_offset),$this_page_url)."'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>$scope_text</span>";
         $pagination_top.= "</div>";
         $pagination_bottom = str_replace("locations-pagination-top","locations-pagination-bottom",$pagination_top);
         $result = $pagination_top.$result.$pagination_bottom;
      }

      $eventful_string="eventful_".$id_base;
      $scope_string="scope_".$id_base;
      $category_string="category_".$id_base;
      $result .= "<script type='text/javascript'>
         <!--// 
      $eventful_string = '$eventful';
      $scope_string = '$scope';
      $category_string = '$category';
      events_page_link = '$events_page_link';
         //-->
         </script>";

      // we add the list if wanted (only for "before" or "after")
      $locations = eme_get_locations((bool)$eventful,$scope,$category,$scope_offset);
      $loc_list = "<div id='eme_div_locations_list_$id_base' class='eme_div_locations_list'><ol id='eme_locations_list_$id_base' class='eme_locations_list'>"; 
      $firstletter="A";
      foreach($locations as $location) {
         if ($show_locations) {
            $loc_list.="<li id='location-". $location['location_id']."_$id_base".
                           "' style='list-style-type: upper-alpha'><a>".
                           eme_trans_esc_html($location['location_name'])."</a></li>";
         }
         if ($show_events) {
            $events = eme_get_events(0,$scope,"ASC",$scope_offset,$location['location_id'], $category);
            $loc_list .= "<ol id='eme_events_list'>"; 
            foreach ($events as $event) {
               if ($show_locations)
                  $loc_list.="<li id='location-". $location['location_id']."_$id_base".
                           "' style='list-style-type: none'>- <a>".
                           eme_trans_esc_html($event['event_name'])."</a></li>";
               else
                  $loc_list.="<li id='location-". $location['location_id']."_$id_base".
                           "' style='list-style-type: none'>$firstletter. <a>".
                           eme_trans_esc_html($event['event_name'])."</a></li>";
            }
            $loc_list.= "</ol>"; 
         }
         // cool: we can increment strings in php, so we can mimic the CSS "style='list-style-type: upper-alpha'" thingie
         // usefull when we show events (more than one event per location)
         $firstletter++;
      }
      $loc_list .= "</ol></div>"; 
      if ($list_location=="before") {
         $result = $loc_list.$result;
      } elseif ($list_location=="after") {
         $result .= $loc_list;
      }
   } else {
      $result = "";
   }
   return $result;
}

function eme_single_location_map_shortcode($atts){
   extract ( shortcode_atts ( array ('id'=>'','width' => 0, 'height' => 0), $atts ) );
   $location=eme_get_location($id);
   $map_div = eme_single_location_map($location, $width, $height);
   return $map_div;
}

function eme_display_single_location($location_id,$template_id=0,$ignore_url=0) {
   $location = eme_get_location( intval($location_id) );
   // also take into account the generic option for using the external url
   if (!$ignore_url) $ignore_url=get_option('eme_use_external_url');
   if ($location['location_url'] != '' && !$ignore_url) {
      // url not empty, so we redirect to it
      $page_body = '<script type="text/javascript">window.location.href="'.$location['location_url'].'";</script>';
      return $page_body;
   } elseif ($template_id) {
      $single_location_format= eme_get_template_format($template_id);
   } else {
      $single_location_format = get_option('eme_single_location_format');
   }
   $page_body = eme_replace_locations_placeholders ($single_location_format, $location);
   return $page_body;
}

function eme_get_location_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>'','template_id'=>0,'ignore_url'=>0), $atts ) );
   return eme_display_single_location($id,$template_id,$ignore_url);
}

function eme_get_locations_shortcode($atts) {
   extract(shortcode_atts(array(
      'eventful'  => false,
      'category'  => '',
      'scope'     => 'all',
      'offset'    => 0,
      'template_id' => 0,
      'template_id_header' => 0,
      'template_id_footer' => 0
   ), $atts));
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

   $locations = eme_get_locations((bool)$eventful, $scope, $category, $offset);

   // format is not a locations shortcode, so we need to set the value to "" here, to avoid php warnings
   $format="";
   $eme_format_header="";
   $eme_format_footer="";

   if ($template_id) {
      $format = eme_get_template_format($template_id);
   }
   if ($template_id_header) {
      $format_header = eme_get_template_format($template_id_header);
      $eme_format_header=eme_replace_locations_placeholders($format_header);
   }
   if ($template_id_footer) {
      $format_footer = eme_get_template_format($template_id_footer);
      $eme_format_footer=eme_replace_locations_placeholders($format_footer);
   }
   if (empty($format)) {
      $format = get_option('eme_location_list_format_item' );
      $format = ( $format != '' ) ? $format : "<li class=\"location-#_LOCATIONID\">#_LOCATIONNAME</li>";
      if (empty($eme_format_header)) {
	      $eme_format_header = eme_replace_locations_placeholders(get_option('eme_location_list_format_header' ));
	      $eme_format_header = ( $eme_format_header != '' ) ? $eme_format_header : DEFAULT_LOCATION_LIST_HEADER_FORMAT;
      }
      if (empty($eme_format_footer)) {
	      $eme_format_footer = eme_replace_locations_placeholders(get_option('eme_location_list_format_footer' ));
	      $eme_format_footer = ( $eme_format_footer != '' ) ? $eme_format_footer : DEFAULT_LOCATION_LIST_FOOTER_FORMAT;
      }
   }

   $output = "";
   foreach ($locations as $location) {
      $output .= eme_replace_locations_placeholders($format,$location);
   }
   $output = $eme_format_header . $output . $eme_format_footer;
   $output .= <<<EOD
      <script type="text/javascript">
      //<![CDATA[
      jQuery(document).ready(function() {
         jQuery('#eme_locations.calendar li').each(function(){
               jQuery(this).click(function(){
                  location_id = jQuery(this).attr('class').replace('location-','');
                  jQuery('.location_chosen').text(location_id);
                  prev_month_link = jQuery('.prev-month:first');
                  tableDiv = jQuery(prev_month_link).closest('table').parent();
                  (jQuery(prev_month_link).hasClass('full-link')) ?
                     fullcalendar = 1 : fullcalendar = 0;
                  (jQuery(prev_month_link).hasClass('long_events')) ?
                     long_events = 1 : long_events = 0;
                  loadCalendar(tableDiv, fullcalendar, long_events);
               } );
         } );
      });   
      //]]> 

      </script>
EOD;
   return $output;
}

function eme_replace_event_location_placeholders($format, $event, $target="html", $do_shortcode=1, $lang='') {
   // in the case of the function eme_get_events, the $event contains also the location info
   // so let's just check if the location name is there, if not: get the location
   if ($event['location_id']>0) {
      if (isset($event['location_name'])) {
         // make sure the location data is unserialized etc ...
         $event = eme_get_extralocation_data($event);
         return eme_replace_locations_placeholders($format, $event, $target, $do_shortcode, $lang);
      } else {
         $location=eme_get_location($event['location_id']);
         return eme_replace_locations_placeholders($format, $location, $target, $do_shortcode, $lang);
      }
   } else {
      // to make sure all location placeholders are replaced (even by empty stuff)
      // we create a new location, otherwise we could just return $format but that would leave 
      // some location placeholders unreplaced (which is not the behavior before 1.6.6)
      $location=eme_new_location();
      return eme_replace_locations_placeholders($format, $location, $target, $do_shortcode, $lang);
   }
}

function eme_replace_locations_placeholders($format, $location="", $target="html", $do_shortcode=1, $lang='') {

   // first we do the custom attributes, since these can contain other placeholders
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
      if (isset($location['location_attributes'][$attRef])) {
         $replacement = $location['location_attributes'][$attRef];
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

   // and now all the other placeholders
   preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);

   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'eme_sort_stringlenth');

   foreach($placeholders[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      $found=1;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";

      // echo "RESULT: $result <br>";
      // matches alla fields placeholder
      if (preg_match('/#_MAP/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_single_location_map($location);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_PASTEVENTS$/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location, "past");
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_PASTEVENTS\{(.+)\}/', $result, $matches)) {
	 $order=$matches[1];
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location, "past", $order);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_NEXTEVENTS$/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location,"future");
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

       } elseif (preg_match('/#_NEXTEVENTS\{(.+)\}/', $result)) {
	 $order=$matches[1];
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location,"future",$order);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_ALLEVENTS$/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location, "all");
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_ALLEVENTS\{(.+)\}/', $result)) {
	 $order=$matches[1];
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_events_in_location_list($location, "all",$order);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_(ADDRESS|TOWN|CITY|STATE|ZIP|COUNTRY|LATITUDE|LONGITUDE)/', $result)) {
         $field = "location_".ltrim(strtolower($result), "#_");
         if ($field == "location_address") $field= "location_address1";
         if ($field == "location_town") $field= "location_city";
         if (isset($location[$field]))
            $replacement = $location[$field];
         $replacement = eme_trans_esc_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_(NAME|LOCATIONNAME|LOCATION)$/', $result)) {
         $field = "location_name";
         if (isset($location[$field]))
            $replacement = $location[$field];
         $replacement = eme_trans_esc_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_LOCATIONID/', $result)) {
         $field = "location_id";
         if (isset($location[$field]))
            $replacement = $location[$field];
         $replacement = eme_trans_esc_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }
      } elseif (preg_match('/#_LOCATIONIMAGETITLE$/', $result)) {
         if (!empty($location['location_image_id'])) {
              $info = eme_get_wp_image($location['location_image_id']);
              $replacement = $info['title'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_LOCATIONIMAGEALT$/', $result)) {
         if (!empty($location['location_image_id'])) {
              $info = eme_get_wp_image($location['location_image_id']);
              $replacement = $info['alt'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_LOCATIONIMAGECAPTION$/', $result)) {
         if (!empty($location['location_image_id'])) {
              $info = eme_get_wp_image($location['location_image_id']);
              $replacement = $info['caption'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_LOCATIONIMAGEDESCRIPTION$/', $result)) {
         if (!empty($location['location_image_id'])) {
              $info = eme_get_wp_image($location['location_image_id']);
              $replacement = $info['description'];
              if ($target == "html") {
                      $replacement = apply_filters('eme_general', $replacement);
              } elseif ($target == "rss")  {
                      $replacement = apply_filters('eme_general_rss', $replacement);
              } else {
                      $replacement = apply_filters('eme_text', $replacement);
              }
         }
      } elseif (preg_match('/#_(IMAGE|LOCATIONIMAGE)$/', $result)) {
         if (!empty($location['location_image_id'])) {
            $replacement = wp_get_attachment_image($location['location_image_id'], 'full', 0, array('class'=>'eme_location_image') );
	 } elseif (!empty($location['location_image_url'])) {
            $url = esc_url($location['location_image_url']);
            $replacement = "<img src='$url' alt='".eme_trans_esc_html($location['location_name'],$lang)."'/>";
	 }
         if (!empty($replacement)) {
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif (preg_match('/#_(IMAGEURL|LOCATIONIMAGEURL)/', $result)) {
         if (!empty($location['location_image_id']))
            $replacement = wp_get_attachment_image_url($location['location_image_id'], 'full' );
         elseif (!empty($location['location_image_url']))
            $replacement = esc_url($location['location_image_url']);

      } elseif (preg_match('/#_LOCATIONIMAGETHUMB$/', $result)) {
         if (!empty($location['location_image_id'])) {
            $replacement = wp_get_attachment_image($location['location_image_id'], get_option('eme_thumbnail_size'), 0, array('class'=>'eme_location_image') );
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif (preg_match('/#_LOCATIONIMAGETHUMB\{(.+)\}/', $result, $matches)) {
         if (!empty($location['location_image_id'])) {
            $replacement = wp_get_attachment_image($location['location_image_id'], $matches[1], 0, array('class'=>'eme_location_image') );
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif (preg_match('/#_LOCATIONIMAGETHUMBURL$/', $result)) {
         if (!empty($location['location_image_id'])) {
            $replacement = wp_get_attachment_image_url($location['location_image_id'], get_option('eme_thumbnail_size') );
         }

      } elseif (preg_match('/#_LOCATIONIMAGETHUMBURL\{(.+)\}/', $result, $matches)) {
         if (!empty($location['location_image_id'])) {
            $replacement = wp_get_attachment_image_url($location['location_image_id'], $matches[1] );
         }

      } elseif (preg_match('/#_LOCATIONPAGEURL/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0)
            $replacement = eme_location_url($location,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_DIRECTIONS/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0 && $target == "html") {
            $replacement = eme_add_directions_form($location);
            $replacement = apply_filters('eme_general', $replacement);
         }

      } elseif (preg_match('/#_LOCATIONFIELD\{(.+)\}/', $result, $matches)) {
         $tmp_attkey=$matches[1];
         if (isset($location[$tmp_attkey]) && !is_array($location[$tmp_attkey])) {
            $replacement = $location[$tmp_attkey];
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

      } elseif (preg_match('/#_MYLOCATIONATT\{(.+)\}/', $result, $matches)) {
         $tmp_attkey=$matches[1];
         if (isset($location['location_attributes'][$tmp_attkey])) {
            $replacement = $location['location_attributes'][$tmp_attkey];
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

      } elseif (preg_match('/#_CATEGORIES|#_LOCATIONCATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         if (isset($location['location_id']) && $location['location_id']>0) {
            $categories = eme_get_location_category_names($location['location_id']);
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
         }

      } elseif (preg_match('/#_LOCATIONCATEGORIES_CSS/', $result) && get_option('eme_categories_enabled')) {
         if (isset($location['location_id']) && $location['location_id']>0) {
            $categories = eme_get_location_category_names($location['location_id']);
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
         }

      } elseif (preg_match('/#_LOCATIONCATEGORYDESCRIPTIONS/', $result) && get_option('eme_categories_enabled')) {
         if (isset($location['location_id']) && $location['location_id']>0) {
            $categories = eme_get_location_category_descriptions($location['location_id']);
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
         }

      } elseif (preg_match('/#_EDITLOCATIONLINK/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0) {
            if (current_user_can( get_option('eme_cap_edit_locations')) ||
                  (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
               $replacement = "<a href=' ".wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=edit_location&amp;location_id=".$location['location_id']),'eme_locations','eme_admin_nonce')."'>".__('Edit', 'events-made-easy')."</a>";
            }
         }

      } elseif (preg_match('/#_EDITLOCATIONURL/', $result)) {
         if (isset($location['location_id']) && $location['location_id']>0) {
            if (current_user_can( get_option('eme_cap_edit_locations')) ||
                  (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
               $replacement = wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=edit_location&amp;location_id=".$location['location_id']),'eme_locations','eme_admin_nonce');
            }
         }

      } elseif ($location && preg_match('/#_LOCATION_EXTERNAL_REF/', $result)) {
         if (!empty($location['location_external_ref'])) {
            // remove the 'fb_' prefix
            $replacement=preg_replace('/fb_/','',$location['location_external_ref']);
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif (preg_match('/#_IS_SINGLE_LOC/', $result)) {
         if (eme_is_single_location_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_LOGGED_IN/', $result)) {
         if (is_user_logged_in())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_ADMIN_PAGE/', $result)) {
         if (is_admin())
            $replacement = 1;
         else
            $replacement = 0;

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

   # we handle DESCRIPTION the last, so no placeholder replacement happens accidentaly in the text of #_DESCRIPTION
   if (preg_match('/#_DESCRIPTION|#_LOCATIONDETAILS/', $format, $placeholders)) {
      $result=$placeholders[0];
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

      $field = "location_description";
      if (isset($location[$field]))
         $replacement = $location[$field];

      // no real sanitizing needed, but possible translation
      // this is the same as for an event in fact
      $replacement = eme_translate($replacement);
      if ($target == "html")
         $replacement = apply_filters('eme_notes', $replacement);
      elseif ($target == "rss") {
         $replacement = apply_filters('eme_notes_rss', $replacement);
         $replacement = apply_filters('the_content_rss', $replacement);
      } else {
         $replacement = apply_filters('eme_text', $replacement);
      }

      if ($need_escape)
         $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   // now, replace any language tags found
   $format = eme_translate($format,$lang);

   // and now replace any shortcodes, if wanted
   if ($do_shortcode)
      return do_shortcode($format);   
   else
      return $format;   
}

function eme_add_directions_form($location) {
   $locale_code = substr ( get_locale (), 0, 2 );
   $res = "";
   if (isset($location['location_address1']) && isset($location['location_city'])) {
      $res .= '<form action="//maps.google.com/maps" method="get" target="_blank" style="text-align:left;">';
      $res .= '<div id="eme_direction_form"><label for="saddr">'.__('Your Street Address','events-made-easy').'</label><br />';
      $res .= '<input type="text" name="saddr" id="saddr" value="" />';
      $res .= '<input type="hidden" name="daddr" value="'.$location['location_address1'].', '.$location['location_city'].'" />';
      $res .= '<input type="hidden" name="hl" value="'.$locale_code.'" /></div>';
      $res .= '<input type="submit" value="'.__('Get Directions','events-made-easy').'" />';
      $res .= '</form>';
   }

   # some people might want to change the form to their liking
   if (has_filter('eme_directions_form_filter')) $res=apply_filters('eme_directions_form_filter',$res);

   return $res;
}

function eme_global_map_json($eventful = false, $scope = "all", $category = '', $map_id, $offset = 0) {
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

   $locations = eme_get_locations((bool)$eventful,$scope,$category,$offset);
   $json_locations = array();
   foreach($locations as $location) {
      $json_location = array();

      # first we set the balloon info
      $tmp_loc=eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location);
      # no newlines allowed, otherwise no map is shown
      $tmp_loc=eme_nl2br($tmp_loc);
      $json_location["location_balloon"]=eme_trans_esc_html($tmp_loc);

      # second, we fill in the rest of the info
      foreach($location as $key => $value) {
         # we skip some keys, since json is limited in size we only return what's needed in the javascript
         if (preg_match('/location_balloon|location_id|location_latitude|location_longitude/', $key)) {
            # no newlines allowed, otherwise no map is shown
            $value=eme_nl2br($value);
            $json_location[$key] = eme_trans_esc_html($value);
         }
      }
      $json_locations[] = $json_location;
   }

   $zoom_factor=get_option('eme_global_zoom_factor');
   $maptype=get_option('eme_global_maptype');
   if ($zoom_factor >14) $zoom_factor=14;
   $json = array(
                'locations' => $json_locations,
                'enable_zooming' => get_option('eme_gmap_zooming') ? 'true' : 'false',
                'zoom_factor' => $zoom_factor,
                'maptype' => $maptype,
                'map_id' => $map_id
        );
   echo json_encode($json);
}

function eme_single_location_map($location,$width=0,$height=0) {
   global $eme_need_gmap_js;

   $gmap_is_active = get_option('eme_gmap_is_active');
   $map_text = addslashes(eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location));
   # no newlines allowed, otherwise no map is shown
   $map_text = eme_nl2br($map_text);
   // if gmap is not active: we don't show the map
   // if the location name is empty: we don't show the map. But that can never happen since it's checked when creating the location
   if ($gmap_is_active && isset($location['location_id']) && $location['location_id']>0) {
      $eme_need_gmap_js=1;
      //$id_base = $location['location_id'];
      // we can't create a unique <div>-id based on location id alone, because you can have multiple maps on the sampe page for
      // different events but they can go to the same location...
      // So we also use the event_id (if present) and the microtime for this, and replace all non digits by underscore (otherwise the generated javascript will error)
      $id_base = preg_replace("/\D/","_",microtime(1));
      // the next is only possible when called from within an event
      if (isset($location['event_id'])) {
         $id_base = $location['event_id']."_".$id_base;
      } else {
         $id_base = rand()."_".$id_base;
      }
      $id="eme-location-map_".$id_base;
      $latitude_string="latitude_".$id_base;
      $longitude_string="longitude_".$id_base;
      $map_text_string="map_text_".$id_base;
      $zoom_factor_string="zoom_factor_".$id_base;
      $maptype_string="maptype_".$id_base;
      $enable_zooming_string="enable_zooming_".$id_base;
      $enable_zooming=get_option('eme_gmap_zooming') ? 'true' : 'false';
      $zoom_factor=get_option('eme_indiv_zoom_factor');
      $maptype=get_option('eme_indiv_maptype');
      if ($zoom_factor >14) $zoom_factor=14;
      #$latitude_string="latitude";
      #$longitude_string="longitude";
         //$map_div = "<div id='$id' style=' background: green; width: 400px; height: 300px'></div>" ;
      if (!empty($width) && !empty($height)) {
	 if (!preg_match('/\%$|px$/',$width)) $width=$width."px";
	 if (!preg_match('/\%$|px$/',$height)) $height=$height."px";
         $map_div = "<div id='$id' style='width: $width; height: $height' ></div>" ;
      } else {
         $map_div = "<div id='$id' class='eme-location-map'></div>" ;
      }
      $map_div .= "<script type='text/javascript'>
      $latitude_string = parseFloat('".$location['location_latitude']."');
      $longitude_string = parseFloat('".$location['location_longitude']."');
      $map_text_string = '$map_text';
      $enable_zooming_string = '$enable_zooming';
      $zoom_factor_string = $zoom_factor;
      $maptype_string = '$maptype';
      </script>";
      // $map_div .= "<script src='".EME_PLUGIN_URL."eme_single_location_map.js' type='text/javascript'></script>";
   } else {
      $map_div = "";
   }
   return $map_div;
}

function eme_events_in_location_list($location, $scope = "future", $order="ASC") {
   $eme_event_list_number_events=get_option('eme_event_list_number_items' );
   $events = eme_get_events($eme_event_list_number_events,$scope,$order,"",$location['location_id']);
   $list = "";
   if (count($events) > 0) {
      foreach($events as $event)
         $list .= eme_replace_placeholders(get_option('eme_location_event_list_item_format'), $event);
   } else {
      $list = get_option('eme_location_no_events_message');
   }
   return $list;
}

function eme_locations_search_ajax() {
   header("Content-type: application/json; charset=utf-8");
   if(isset($_GET['id']) && $_GET['id'] != "") {
      $item = eme_get_location(intval($_GET['id']));
      $record = array();
      $record['id']       = $item['location_id'];
      $record['name']     = eme_trans_esc_html($item['location_name']); 
      $record['address1'] = eme_trans_esc_html($item['location_address1']);
      $record['address2'] = eme_trans_esc_html($item['location_address2']);
      $record['city']     = eme_trans_esc_html($item['location_city']);
      $record['state']    = eme_trans_esc_html($item['location_state']);
      $record['zip']      = eme_trans_esc_html($item['location_zip']);
      $record['country']  = eme_trans_esc_html($item['location_country']);
      $record['latitude'] = eme_trans_esc_html($item['location_latitude']);
      $record['longitude']= eme_trans_esc_html($item['location_longitude']);
      echo json_encode($record);

   } else {

      $res = array();

      if (!isset($_GET["q"])) {
         echo json_encode($res);
         return;
      }

      $locations = eme_search_locations($_GET["q"]);
      // change null to empty
      $locations = array_map(function($v){
        return (is_null($v)) ? "" : $v;
      },$locations);

      foreach($locations as $item) {
         $record = array();
         $record['id']       = $item['location_id'];
         $record['name']     = eme_trans_esc_html($item['location_name']); 
         $record['address1'] = eme_trans_esc_html($item['location_address1']);
         $record['address2'] = eme_trans_esc_html($item['location_address2']);
         $record['city']     = eme_trans_esc_html($item['location_city']);
         $record['state']    = eme_trans_esc_html($item['location_state']);
         $record['zip']      = eme_trans_esc_html($item['location_zip']);
         $record['country']  = eme_trans_esc_html($item['location_country']);
         $record['latitude'] = eme_trans_esc_html($item['location_latitude']);
         $record['longitude']= eme_trans_esc_html($item['location_longitude']);
         $res[]  = $record;
      }

      echo json_encode($res);
   }
}

add_action( 'wp_ajax_eme_locations_list', 'eme_ajax_locations_list' );
add_action( 'wp_ajax_eme_manage_locations', 'eme_ajax_manage_locations' );
function eme_ajax_locations_list() {
   global $wpdb;
   $table = $wpdb->prefix.LOCATIONS_TBNAME;
   $search_name = isset($_REQUEST['search_name']) ? eme_sanitize_request($_REQUEST['search_name']) : '';
   $where ='';
   $where_arr = array();
   if(!empty($search_name)) {
      $where_arr[] = "(location_name like '%".$search_name."%')";
   }
   if ($where_arr)
      $where = "WHERE ".implode(" AND ",$where_arr);

   $jTableResult = array();
   //if (current_user_can( get_option($cap))) {
      $sql = "SELECT COUNT(*) FROM $table $where";
      $recordCount = $wpdb->get_var($sql);
      $start=intval($_REQUEST["jtStartIndex"]);
      $pagesize=intval($_REQUEST["jtPageSize"]);
      $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
      $sql="SELECT * FROM $table $where $sorting LIMIT $start,$pagesize";
      $rows=$wpdb->get_results($sql,ARRAY_A);
      $records=array();
      foreach ($rows as $item) {
         $record = array();
         $record['location_id']       = $item['location_id'];
         $record['location_name']     = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=edit_location&amp;location_id=".$item['location_id']),'eme_locations','eme_admin_nonce')."' title='".__('Edit location','events-made-easy')."'>".eme_trans_esc_html($item['location_name'])."</a>";
         if (!$item['location_latitude'] && !$item['location_longitude']) {
            $record['location_name'] .= "&nbsp;<img style='vertical-align: middle;' src='".EME_PLUGIN_URL."images/warning.png' alt='warning' title='".__('Location map coordinates are empty! Please edit the location to correct this, otherwise it will not show correctly on your website.','events-made-easy')."'>";
         }
         $record['location_address1'] = eme_trans_esc_html($item['location_address1']);
         $record['location_address2'] = eme_trans_esc_html($item['location_address2']);
         $record['location_city']     = eme_trans_esc_html($item['location_city']);
         $record['location_state']    = eme_trans_esc_html($item['location_state']);
         $record['location_zip']      = eme_trans_esc_html($item['location_zip']);
         $record['location_country']  = eme_trans_esc_html($item['location_country']);
         $record['location_latitude'] = eme_trans_esc_html($item['location_latitude']);
         $record['location_longitude']= eme_trans_esc_html($item['location_longitude']);
	 $record['copy'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-locations&amp;eme_admin_action=copy_location&amp;location_id=".$item['location_id']),'eme_locations','eme_admin_nonce')."' title='".__('Duplicate this location','events-made-easy')."'><img src='".EME_PLUGIN_URL."images/copy_16.png'/></a>";
         $records[]  = $record;
      }
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = $recordCount;
      $jTableResult['Records'] = $records;
   //} else {
   //   $jTableResult['Result'] = "Error";
   //   $jTableResult['Message'] = __('Access denied!','events-made-easy');
   //}
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_manage_locations() {
   global $wpdb;
   $table = $wpdb->prefix.LOCATIONS_TBNAME;
   check_ajax_referer('eme_locations','eme_admin_nonce');
   $jTableResult = array();
   if (isset($_POST['do_action'])) {
      $do_action=eme_sanitize_request($_POST['do_action']);
      switch ($do_action) {
         case 'deleteLocations':
              $ids_arr=explode(',',$_POST['location_id']);
              if (eme_array_integers($ids_arr)) {
                 $wpdb->query("DELETE FROM $table WHERE location_id in ( ".$_POST['location_id'].")");
              }
              $jTableResult['Result'] = "OK";
              break;
      }
   }
   print json_encode($jTableResult);
   wp_die();
}


?>
