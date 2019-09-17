<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_category() {
   $category = array(
      'category_name' => '',
      'description' => ''
   );
   return $category;
}

function eme_categories_page() {      
   global $wpdb;
   
   if (!current_user_can( get_option('eme_cap_categories')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update categories!','events-made-easy');
      eme_categories_table_layout($message);
      return;
   }
   
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_category") { 
      check_admin_referer('eme_categories','eme_admin_nonce');
      // edit category  
      eme_categories_edit_layout();
      return;
   }

   if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_category") {
      // edit template  
      check_admin_referer('eme_categories','eme_admin_nonce');
      eme_categories_edit_layout();
      return;
   }

   // Insert/Update/Delete Record
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME;
   $message = '';
   if (isset($_POST['eme_admin_action'])) {
      check_admin_referer('eme_categories','eme_admin_nonce');
      if ($_POST['eme_admin_action'] == "do_editcategory" ) {
         // category update required  
         $category = array();
         $category['category_name'] =  eme_strip_tags($_POST['category_name']);
         $category['description'] =  eme_strip_tags($_POST['description']);
	 if (isset($_POST['category_slug']) && !empty($_POST['category_slug']))
		 $category['category_slug'] = untrailingslashit(eme_permalink_convert(eme_strip_tags($_POST['category_slug'])));
	 else
		 $category['category_slug'] = untrailingslashit(eme_permalink_convert($category['category_name']));
	 if (isset($_POST['category_id']) && intval($_POST['category_id'])>0) {
		 $validation_result = $wpdb->update( $categories_table, $category, array('category_id' => intval($_POST['category_id'])) );
		 if ($validation_result !== false) {
			 $message = __("Successfully edited the category", 'events-made-easy');
		 } else {
			 $message = __("There was a problem editing your category, please try again.",'events-made-easy');
		 }
	 } else {
		 $validation_result = $wpdb->insert($categories_table, $category);
		 if ($validation_result !== false) {
			 $message = __("Successfully added the category", 'events-made-easy');
		 } else {
			 $message = __("There was a problem adding your category, please try again.",'events-made-easy');
		 }
	 }
      } elseif ($_POST['eme_admin_action'] == "do_deletecategory" && isset($_POST['categories'])) {
         // Delete category or multiple
         $categories = $_POST['categories'];
         if (!empty($categories) && eme_array_integers($categories)) {
               $validation_result = $wpdb->query( "DELETE FROM $categories_table WHERE category_id IN ( ". implode(",", $categories) .")" );
               if ($validation_result !== false)
                  $message = __("Successfully deleted the selected categories.",'events-made-easy');
               else
                  $message = __("There was a problem deleting the selected categories, please try again.",'events-made-easy');
         } else {
            $message = __("Couldn't delete the categories. Incorrect category IDs supplied. Please try again.",'events-made-easy');
         }
      }
   }
   eme_categories_table_layout($message);
} 

function eme_categories_table_layout($message = "") {
   global $plugin_page;
   $categories = eme_get_categories();
   $destination = admin_url("admin.php?page=$plugin_page"); 
   $nonce_field = wp_nonce_field('eme_categories','eme_admin_nonce',false,false);
   $table = "
      <div class='wrap nosubsub'>
      <div id='poststuff'>
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h1>".__('Categories', 'events-made-easy')."</h1>\n ";   
         
         if($message != "") {
            $table .= "
            <div id='message' class='updated fade below-h1' style='background-color: rgb(255, 251, 204);'>
               <p>$message</p>
            </div>";
         }

	 $table.="
   <div class='wrap'>
         <form id='categories-new' method='post' action='$destination'>
            $nonce_field
            <input type='hidden' name='eme_admin_action' value='add_category' />
            <input type='submit' class='button-primary' name='submit' value='".__('Add category', 'events-made-easy')."' />
         </form>
   </div>
<br /><br />
         
                <form id='categories-form' method='post' action='$destination'>
                  <input type='hidden' name='eme_admin_action' value='do_deletecategory' />";
                  $table .= $nonce_field;
                  if (count($categories)>0) {
                     $table .= "<table class='widefat'>
                        <thead>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1' /></th>
                              <th>".__('ID', 'events-made-easy')."</th>
                              <th>".__('Name', 'events-made-easy')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1' /></th>
                              <th>".__('ID', 'events-made-easy')."</th>
                              <th>".__('Name', 'events-made-easy')."</th>
                           </tr>
                        </tfoot>
                        <tbody>";
                     foreach ($categories as $this_category) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_category['category_id']."' name='categories[]' /></td>
                           <td><a href='".wp_nonce_url(admin_url("admin.php?page=eme-categories&amp;eme_admin_action=edit_category&amp;category_id=".$this_category['category_id']),'eme_categories','eme_admin_nonce')."'>".$this_category['category_id']."</a></td>
                           <td><a href='".wp_nonce_url(admin_url("admin.php?page=eme-categories&amp;eme_admin_action=edit_category&amp;category_id=".$this_category['category_id']),'eme_categories','eme_admin_nonce')."'>".eme_trans_esc_html($this_category['category_name'])."</a></td>
                           </tr>
                        ";
                     }
                     $delete_text=__("Are you sure you want to delete these categories?",'events-made-easy');
                     $delete_button_text=__("Delete",'events-made-easy');
                     $table .= <<<EOT
                        </tbody>
                     </table>
   
                     <div class='tablenav'>
                        <div class='alignleft actions'>
                        <input class='button-primary action' type='submit' name='doaction' value='$delete_button_text' onclick="return areyousure('$delete_text');" />
                        <br class='clear'/>
                        </div>
                        <br class='clear'/>
                     </div>
EOT;
                  } else {
                        $table .= "<p>".__('No categories defined.', 'events-made-easy');
                  }
                   $table .= "
                  </form>
         </div>
   </div>";
   echo $table;  
}

function eme_categories_edit_layout($message = "") {
   global $plugin_page;

   if(isset($_GET['category_id'])) {
           $category_id = intval($_GET['category_id']);
           $template = eme_get_template($category_id);
           $h1_string=__('Edit category', 'events-made-easy');
           $action_string=__('Update category', 'events-made-easy');
	   $action="edit";
   } else {
           $category_id=0;
           $template = eme_new_category();
           $h1_string=__('Create category', 'events-made-easy');
           $action_string=__('Add category', 'events-made-easy');
	   $action="add";
   }

   $category = eme_get_category($category_id);
   $nonce_field = wp_nonce_field('eme_categories','eme_admin_nonce',false,false);
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h1>".$h1_string."</h1>";   
      
      if($message != "") {
         $layout .= "
      <div id='message' class='updated fade below-h1' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
      }
      $layout .= "
      <div id='ajax-response'></div>

      <form name='edit_category' id='edit_category' method='post' action='".admin_url("admin.php?page=$plugin_page")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='do_editcategory' />
      <input type='hidden' name='category_id' value='".$category_id."' />
      $nonce_field
      <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='category_name'>".__('Category name', 'events-made-easy')."</label></th>
               <td><input name='category_name' id='category_name' type='text' value='".eme_esc_html($category['category_name'])."' size='40' /><br />
                 ".__('The name of the category', 'events-made-easy')."</td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='description'>".__('Category description', 'events-made-easy')."</label></th>
               <td><textarea name='description' id='description' rows='5' >".eme_esc_html($category['description'])."</textarea><br />
                 ".__('The description of the category', 'events-made-easy')."</td>
            </tr>";
      if ($action=="edit") {
            $slug = $category['category_slug'] ? $category['category_slug'] : $category['category_name'];
            $slug = untrailingslashit(eme_permalink_convert($slug));
            $layout.="<tr>
	       <th scope='row' valign='top'><label for='slug'>". __('Permalink', 'events-made-easy')."</label></th>
               <td>". trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'))."cat/".
                   "<input type='text' id='slug' name='category_slug' value='$slug' />".user_trailingslashit("")."
               </td>
               </tr>";
      }

      $layout .= "
         </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".$action_string."' /></p>
      </form>
   </div>
   ";  
   echo $layout;
}

function eme_get_categories($eventful=false,$scope="future",$extra_conditions=""){
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $categories = array();
   $orderby = " ORDER BY category_name ASC";
   if ($eventful) {
      $events = eme_get_events(0, $scope, "ASC");
      if ($events) {
         foreach ($events as $event) {
            if (!empty($event['event_category_ids'])) {
               $event_cats=explode(",",$event['event_category_ids']);
               if (!empty($event_cats)) {
                  foreach ($event_cats as $category_id) {
                     $categories[$category_id]=$category_id;
                  }
               }
            }
         }
      }
      if (!empty($categories) && eme_array_integers($categories)) {
         $event_cats=join(",",$categories);
         if ($extra_conditions !="")
            $extra_conditions = " AND ($extra_conditions)";
         $result = $wpdb->get_results("SELECT * FROM $categories_table WHERE category_id IN ($event_cats) $extra_conditions $orderby", ARRAY_A);
      }
   } else {
      if ($extra_conditions !="")
         $extra_conditions = " WHERE ($extra_conditions)";
      $result = $wpdb->get_results("SELECT * FROM $categories_table $extra_conditions $orderby", ARRAY_A);
   }
   if (has_filter('eme_categories_filter')) $result=apply_filters('eme_categories_filter',$result); 
   return $result;
}

function eme_get_category($category_id) { 
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = $wpdb->prepare("SELECT * FROM $categories_table WHERE category_id = %d",$category_id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_event_category_names($event_id,$extra_conditions="") { 
   global $wpdb;
   $event_table = $wpdb->prefix.EVENTS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   if ($extra_conditions !="")
      $extra_conditions = " AND ($extra_conditions)";
   $sql = $wpdb->prepare("SELECT category_name FROM $categories_table, $event_table where event_id = %d AND FIND_IN_SET(category_id,event_category_ids) $extra_conditions",$event_id);
   return $wpdb->get_col($sql);
}

function eme_get_event_category_descriptions($event_id,$extra_conditions="") { 
   global $wpdb;
   $event_table = $wpdb->prefix.EVENTS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   if ($extra_conditions !="")
      $extra_conditions = " AND ($extra_conditions)";
   $sql = $wpdb->prepare("SELECT description FROM $categories_table, $event_table where event_id = %d AND FIND_IN_SET(category_id,event_category_ids) $extra_conditions",$event_id);
   return $wpdb->get_col($sql);
}

function eme_get_event_categories($event_id,$extra_conditions="") { 
   global $wpdb;
   $event_table = $wpdb->prefix.EVENTS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   if ($extra_conditions !="")
      $extra_conditions = " AND ($extra_conditions)";
   $sql = $wpdb->prepare("SELECT $categories_table.* FROM $categories_table, $event_table where event_id = %d AND FIND_IN_SET(category_id,event_category_ids) $extra_conditions",$event_id);
   return $wpdb->get_results($sql,ARRAY_A);
}

function eme_get_category_eventids($category_id,$future=0) {
   // similar to eme_get_recurrence_eventids
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   if ($future_only) {
      $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
      $today = $eme_date_obj->format('Y-m-d');
      $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE FIND_IN_SET(%d,event_category_ids) AND event_start_date > %s ORDER BY event_start_date ASC, event_start_time ASC",$category_id,$today);
   } else {
      $sql = $wpdb->prepare("SELECT event_id FROM $events_table WHERE FIND_IN_SET(%d,event_category_ids) ORDER BY event_start_date ASC, event_start_time ASC",$category_id);
   }
   return $wpdb->get_col($sql);
}

function eme_get_location_categories($location_id) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = $wpdb->prepare("SELECT $categories_table.* FROM $categories_table, $locations_table where location_id = %d AND FIND_IN_SET(category_id,location_category_ids)",$location_id);
   return $wpdb->get_results($sql,ARRAY_A);
}

function eme_get_location_category_names($location_id) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = $wpdb->prepare("SELECT $categories_table.category_name FROM $categories_table, $locations_table where location_id = %d AND FIND_IN_SET(category_id,location_category_ids)",$location_id);
   return $wpdb->get_col($sql);
}

function eme_get_location_category_descriptions($location_id) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = $wpdb->prepare("SELECT $categories_table.description FROM $categories_table, $locations_table where location_id = %d AND FIND_IN_SET(category_id,location_category_ids)",$location_id);
   return $wpdb->get_col($sql);
}

function eme_get_category_ids($cat_slug) {
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $cat_ids = array();
   if (!empty($cat_slug)) {
      $sql = $wpdb->prepare("SELECT DISTINCT category_id FROM $categories_table WHERE category_slug = %s",$cat_slug);
      $cat_ids = $wpdb->get_col($sql);
   }
   return $cat_ids;
}

function eme_get_categories_shortcode($atts) {
   extract(shortcode_atts(array(
      'event_id'  => 0,
      'eventful'  => false,
      'scope'     => 'all',
      'template_id' => 0,
      'template_id_header' => 0,
      'template_id_footer' => 0
   ), $atts));
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

   if ($event_id)
      $categories = eme_get_event_categories($event_id);
   else
      $categories = eme_get_categories($eventful,$scope);

   // format is not a locations shortcode, so we need to set the value to "" here, to avoid php warnings
   $format="";
   $eme_format_header="";
   $eme_format_footer="";

   if ($template_id) {
      $format = eme_get_template_format($template_id);
   }
   if ($template_id_header) {
      $format_header = eme_get_template_format($template_id_header);
      $eme_format_header=eme_replace_categories_placeholders($format_header);
   }
   if ($template_id_footer) {
      $format_footer = eme_get_template_format($template_id_footer);
      $eme_format_footer=eme_replace_categories_placeholders($format_footer);
   }
   if (empty($format))
      $format = "<li class=\"cat-#_CATEGORYFIELD{category_id}\">#_CATEGORYFIELD{category_name}</li>";
   if (empty($eme_format_header))
      $eme_format_header = '<ul>';
   if (empty($eme_format_footer))
      $eme_format_header = '</ul>';

   $output = "";
   foreach ($categories as $cat) {
      $output .= eme_replace_categories_placeholders($format,$cat);
   }
   $output = $eme_format_header . $output . $eme_format_footer;
   return $output;
}

function eme_replace_categories_placeholders($format, $cat="", $target="html", $do_shortcode=1, $lang='') {

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

      if (preg_match('/#_CATEGORYFIELD\{(.+)\}/', $result, $matches)) {
         $tmp_attkey=$matches[1];
         if (isset($cat[$tmp_attkey]) && !is_array($cat[$tmp_attkey]))
            $replacement = $cat[$tmp_attkey];
      } elseif (preg_match('/#_CATEGORYURL/', $result)) {
         $replacement = eme_category_url($cat);
      } else {
         $found = 0;
      }

      if ($found) {
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
         if ($need_escape)
            $replacement = eme_sanitize_request(eme_esc_html(preg_replace('/\n|\r/','',$replacement)));
         if ($need_urlencode)
            $replacement = rawurlencode($replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now, replace any language tags found
   $format = eme_translate($format,$lang);

   // and now replace any shortcodes, if wanted
   if ($do_shortcode)
      return do_shortcode($format);   
   else
      return $format;   
}
?>
