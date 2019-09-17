<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_filter_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('multiple' => 0, 'multisize' => 5, 'scope_count' => 12, 'submit' => 'Submit', 'category' => '', 'notcategory' => '', 'template_id' => 0 ), $atts ) );
   $multiple = ($multiple==="true" || $multiple==="1") ? true : $multiple;
   $multiple = ($multiple==="false" || $multiple==="0") ? false : $multiple;

   if ($template_id) {
      // when using a template, don't bother with fields, the template should contain the things needed
      $filter_form_format= eme_get_template_format($template_id);
   } else {
      $filter_form_format = get_option('eme_filter_form_format');
   }

   $content=eme_replace_filter_form_placeholders($filter_form_format,$multiple,$multisize,$scope_count,$category,$notcategory);
   # using the current page as action, so we can leave action empty in the html form definition
   # this helps to keep the language and any other parameters, and works with permalinks as well
   $form = "<form action='' method='POST'>";
   $form .= "<input type='hidden' name='eme_eventAction' value='filter' />";
#   foreach ($_REQUEST as $key => $item) {
#      $form .= "<input type='hidden' name='$key' value='$item' />";
#   }
   $form .= $content;
   $form .= "<input type='submit' value='$submit' /></form>";
   return $form;
}

function eme_create_week_scope($count) {
   global $eme_timezone;
   $start_of_week = get_option('start_of_week');
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   $eme_date_obj->setWeekStartDay($start_of_week);
   $scope=array();
   $scope[0] = __('Select Week','events-made-easy');
   for ( $i = 0; $i < $count; $i++) {
      $limit_start=$eme_date_obj->copy()->startOfWeek()->format('Y-m-d');
      $limit_end=$eme_date_obj->copy()->endOfWeek()->format('Y-m-d');
      $this_scope=$limit_start."--".$limit_end;
      $scope_text = eme_localized_date($limit_start." ".$eme_timezone)." -- ".eme_localized_date($limit_end." ".$eme_timezone);
      $scope[$this_scope] = $scope_text;
      $eme_date_obj->addOneWeek();
   }
   if (has_filter('eme_week_scope_filter')) $scope=apply_filters('eme_week_scope_filter',$scope);
   return $scope;
}

function eme_create_month_scope($count) {
   global $eme_timezone;
   $scope=array();
   $scope[0] = __('Select Month','events-made-easy');
   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   for ( $i = 0; $i < $count; $i++) {
      $limit_start= $eme_date_obj->startOfMonth()->format('Y-m-d');
      $days_in_month=$eme_date_obj->getDaysInMonth();
      $limit_end= $eme_date_obj->format("Y-m-$days_in_month");
      $this_scope = "$limit_start--$limit_end";
      $scope_text = eme_localized_date ($limit_start." ".$eme_timezone,get_option('eme_show_period_monthly_dateformat'));
      $scope[$this_scope] = $scope_text;
      $eme_date_obj->addOneMonth();
   }
   if (has_filter('eme_month_scope_filter')) $scope=apply_filters('eme_month_scope_filter',$scope);
   return $scope;
}

function eme_create_year_scope($count) {
   global $eme_timezone;
   $scope=array();
   $scope[0] = __('Select Year','events-made-easy');

   $eme_date_obj=new ExpressiveDate(null,$eme_timezone);
   for ( $i = 0; $i < $count; $i++) {
      $year=$eme_date_obj->getYear();
      $limit_start = "$year-01-01";
      $limit_end   = "$year-12-31";
      $this_scope = "$limit_start--$limit_end";
      $scope_text = eme_localized_date ($limit_start." ".$eme_timezone,get_option('eme_show_period_yearly_dateformat'));
      $scope[$this_scope] = $scope_text;
      $eme_date_obj->addOneYear();
   }
   if (has_filter('eme_year_scope_filter')) $scope=apply_filters('eme_year_scope_filter',$scope);
   return $scope;
}

function eme_replace_filter_form_placeholders($format, $multiple, $multisize, $scope_count, $category, $notcategory) {

   preg_match_all("/#_[A-Za-z0-9_]+/", $format, $placeholders);
   usort($placeholders[0],'eme_sort_stringlenth');

   // if one of these changes, also the eme_events.php needs changing for the "Next page" part
   $cat_post_name="eme_cat_filter";
   $loc_post_name="eme_loc_filter";
   $city_post_name="eme_city_filter";
   $country_post_name="eme_country_filter";
   $scope_post_name="eme_scope_filter";
   $localized_scope_post_name="eme_localized_scope_filter";

   $selected_scope = isset($_REQUEST[$scope_post_name]) ? eme_sanitize_request($_REQUEST[$scope_post_name]) : '';
   $selected_location = isset($_REQUEST[$loc_post_name]) ? eme_sanitize_request($_REQUEST[$loc_post_name]) : '';
   $selected_city = isset($_REQUEST[$city_post_name]) ? eme_sanitize_request($_REQUEST[$city_post_name]) : '';
   $selected_country = isset($_REQUEST[$country_post_name]) ? eme_sanitize_request($_REQUEST[$country_post_name]) : '';
   $selected_category = isset($_REQUEST[$cat_post_name]) ? eme_sanitize_request($_REQUEST[$cat_post_name]) : '';

   $extra_conditions_arr=array();
   if ($category != '')
      $extra_conditions_arr[]="(category_id IN ($category))";
   if ($notcategory != '')
      $extra_conditions_arr[]="(category_id NOT IN ($notcategory))";
   $extra_conditions = implode(' AND ',$extra_conditions_arr);

   $scope_fieldcount=0;
   foreach($placeholders[0] as $result) {
      $replacement = "";
      $eventful=0;
      $found = 1;
      $orig_result = $result;

      if (preg_match('/#_(EVENTFUL_)?FILTER_CATS/', $result) && get_option('eme_categories_enabled')) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }

         $categories = eme_get_categories($eventful,"future",$extra_conditions);
         if ($categories) {
            $cat_list = array();
            foreach ($categories as $this_category) {
               $id=$this_category['category_id'];
               $cat_list[$id]=eme_translate($this_category['category_name']);
            }
            asort($cat_list);
            if ($multiple) {
               $cat_list = array(0=>__('Select one or more categories','events-made-easy'))+$cat_list;
               $replacement = eme_ui_multiselect($selected_category,$cat_post_name,$cat_list,$multisize);
            } else {
               $cat_list = array(0=>__('Select a category','events-made-easy'))+$cat_list;
               $replacement = eme_ui_select($selected_category,$cat_post_name,$cat_list);
            }
         }

      } elseif (preg_match('/#_(EVENTFUL_)?FILTER_LOCS/', $result)) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }
         $locations = eme_get_locations($eventful,"future");

         if ($locations) {
            $loc_list = array();
            foreach ($locations as $this_location) {
               $id=$this_location['location_id'];
               $loc_list[$id]=eme_translate($this_location['location_name']);
            }
            asort($loc_list);
            if ($multiple) {
               $loc_list = array(0=>__('Select one or more locations','events-made-easy'))+$loc_list;
               $replacement = eme_ui_multiselect($selected_location,$loc_post_name,$loc_list,$multisize);
            } else {
               $loc_list = array(0=>__('Select a location','events-made-easy'))+$loc_list;
               $replacement = eme_ui_select($selected_location,$loc_post_name,$loc_list);
            }
         }

      } elseif (preg_match('/#_(EVENTFUL_)?FILTER_TOWNS/', $result)) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }
         $cities = eme_get_locations($eventful,"future");
         if ($cities) {
            $city_list = array();
            foreach ($cities as $this_city) {
               $id=eme_translate($this_city['location_city']);
               $city_list[$id]=$id;
            }
            asort($city_list);
            if ($multiple) {
               $city_list = array(0=>__('Select one or more cities','events-made-easy'))+$city_list;
               $replacement = eme_ui_multiselect($selected_city,$city_post_name,$city_list,$multisize);
            } else {
               $city_list = array(0=>__('Select a city','events-made-easy'))+$city_list;
               $replacement = eme_ui_select($selected_city,$city_post_name,$city_list);
            }
         }

      } elseif (preg_match('/#_(EVENTFUL_)?FILTER_COUNTRIES/', $result)) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }
         $countries = eme_get_locations($eventful,"future");
         if ($countries) {
            $country_list = array();
            foreach ($countries as $this_country) {
               $id=eme_translate($this_country['location_country']);
               $country_list[$id]=$id;
            }
            asort($country_list);
            if ($multiple) {
               $country_list = array(0=>__('Select one or more countries','events-made-easy'))+$country_list;
               $replacement = eme_ui_multiselect($selected_country,$country_post_name,$country_list,$multisize);
            } else {
               $country_list = array(0=>__('Select a country','events-made-easy'))+$country_list;
               $replacement = eme_ui_select($selected_country,$country_post_name,$country_list);
            }
         }

      } elseif (preg_match('/#_FILTER_WEEKS/', $result)) {
         if ($scope_fieldcount==0) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_week_scope($scope_count));
            $scope_fieldcount++;
         }
      } elseif (preg_match('/#_FILTER_MONTHS/', $result)) {
         if ($scope_fieldcount==0) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_month_scope($scope_count));
            $scope_fieldcount++;
         }
      } elseif (preg_match('/#_FILTER_MONTHRANGE/', $result)) {
         if ($scope_fieldcount==0) {
            $replacement = "<input type='text' id='$localized_scope_post_name' name='$localized_scope_post_name' readonly='readonly' >";
            $replacement .= "<input type='hidden' id='$scope_post_name' name='$scope_post_name' value='".eme_esc_html($selected_scope)."'>";
            eme_enqueue_datepick();
            $locale_code = get_locale();
            $locale_code = preg_replace( "/_/","-", $locale_code );

            ob_start();
            ?>
            <script type="text/javascript">
            var datepick_locale_code = '<?php echo $locale_code;?>';
            var firstDayOfWeek = <?php echo get_option('start_of_week');?>;
            </script>
            <?php
            $replacement .= ob_get_clean();
            $replacement .= "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_filters.js'></script>";
            $scope_fieldcount++;
         }
      } elseif (preg_match('/#_FILTER_YEARS/', $result)) {
         if ($scope_fieldcount==0) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_year_scope($scope_count));
            $scope_fieldcount++;
         }
      } else {
         $found = 0;
      }

      if ($found) {
         $replacement = apply_filters('eme_general', $replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   return do_shortcode($format);
}

?>
