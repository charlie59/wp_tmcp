<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function eme_new_person() {
   $person = array(
   'lastname' => '',
   'firstname' => '',
   'email' => '',
   'phone' => '',
   'address1' => '',
   'address2' => '',
   'city' => '',
   'state' => '',
   'zip' => '',
   'country' => '',
   'lang' => '',
   'wp_id' => NULL,
   'massmail' => get_option('eme_people_massmail'),
   'properties' => array()
   );
   $person['properties'] = eme_init_person_props($person['properties']);
   return $person;
}

function eme_new_group() {
   $group = array(
   'name' => '',
   'description' => ''
   );
   return $group;
}

function eme_init_person_props($props) {
   if (!isset($props['image_id']))
      $props['image_id']=0;
   return $props;
}

function eme_people_page() {
   $message="";
   if (!current_user_can( get_option('eme_cap_people')) && (isset($_POST['eme_admin_action']) || isset($_GET['eme_admin_action'])) ) {
      $message = __('You have no right to update people!','events-made-easy');
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'import' && isset($_FILES['eme_csv']) && current_user_can(get_option('eme_cap_cleanup')) ) {
      // eme_cap_cleanup is used for cleanup, cron and imports (should more be something like 'eme_cap_actions')
      check_admin_referer('eme_people','eme_admin_nonce');
      $message = eme_import_csv_people();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_addperson") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $message=eme_add_update_person();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_editperson") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $person_id = intval($_POST['person_id']);
      $message=eme_add_update_person($person_id);
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_person") {
      check_admin_referer('eme_people','eme_admin_nonce');
      if (current_user_can( get_option('eme_cap_people'))) {
         $person=eme_new_person();
         eme_person_edit_layout($person);
	 return;
      } else {
         $message = __('You have no right to add people!','events-made-easy');
      }
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_person") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $person_id = intval($_GET['person_id']);
      if (current_user_can( get_option('eme_cap_people'))) {
         $person = eme_get_person($person_id);
         eme_person_edit_layout($person);
	 return;
      } else {
         $message = __('You have no right to update people!','events-made-easy');
      }
   }
   eme_manage_people_layout($message);
}

function eme_groups_page() {
   $message="";
   if (!current_user_can( get_option('eme_cap_people')) && (isset($_POST['eme_admin_action']) || isset($_GET['eme_admin_action'])) ) {
      $message = __('You have no right to update people!','events-made-easy');
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_addgroup") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $message=eme_add_update_group();
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "do_editgroup") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $group_id = intval($_POST['group_id']);
      $message=eme_add_update_group($group_id);
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add_group") {
      check_admin_referer('eme_people','eme_admin_nonce');
      if (current_user_can( get_option('eme_cap_people'))) {
         $group=eme_new_group();
         eme_group_edit_layout($group);
	 return;
      } else {
         $message = __('You have no right to add groups!','events-made-easy');
      }
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_group") {
      check_admin_referer('eme_people','eme_admin_nonce');
      $group_id = intval($_GET['group_id']);
      if (current_user_can( get_option('eme_cap_people'))) {
         $group = eme_get_group($group_id);
         eme_group_edit_layout($group);
	 return;
      } else {
         $message = __('You have no right to update people!','events-made-easy');
      }
   }
   eme_manage_groups_layout($message);
}

function eme_gdpr_shortcode() {
	global $wp;
	$current_url = home_url(add_query_arg(array(),$wp->request));
	if (isset($_POST['eme_action']) && $_POST['eme_action']=='eme_gdpr_mail' && isset($_POST['eme_email'])) {
		// send email to client if it exists, otherwise do nothing, but always return the same message
		$email=eme_strip_tags($_POST['eme_email']);
		// check if email is found, if so: send the gdpr url
		// eme_gdpr_url($email)
		if (eme_count_persons_by_email($email)>0) {
			$contact = eme_get_contact(0);
			$contact_email = $contact->user_email;
			$contact_name = $contact->display_name;
			$gdpr_link=eme_gdpr_url($email);
			$gdpr_subject=get_option('eme_gdpr_subject');
			$gdpr_body=eme_translate(get_option('eme_gdpr_body'));
			$gdpr_body=str_replace('#_GDPR_URL', $gdpr_link ,$gdpr_body );
			eme_send_mail($gdpr_subject,$gdpr_body, $email, '', $contact_email, $contact_name);
		}
		print '<div id="message" class="info" style="background-color: rgb(255, 251, 204);">';
		_e('Thank you for your request, an email will be sent with further info','events-made-easy');
		print '</div>';
		return;
	}
?>
   <form id='eme_gdpr_form' method='post' action='#'>
   <input type="hidden" name="eme_action" value="eme_gdpr_mail" />
   <input type="email" name="eme_email" value="" />
   <input type="submit" value="<?php _e ( 'Request person data','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   </form>
<?php
}

function eme_gdpr_show($email) {
	if (eme_count_persons_by_email($email)>0) {
		$person_ids=eme_get_personids_by_email($email);
		foreach ($person_ids as $person_id) {
			$person=eme_get_person($person_id);
			$dyn_answers = eme_get_dyndata_person_answers($person['person_id']);
			$members = eme_get_members('','people.person_id='.$person['person_id']);
			$massmail = $person['massmail'] ? __('Yes') : __('No');
			print '<table>';
			print '<tr><td>'.__('Last Name', 'events-made-easy').'</td><td>'.eme_esc_html($person['lastname']).'</td></tr>';
			print '<tr><td>'.__('First Name', 'events-made-easy').'</td><td>'.eme_esc_html($person['firstname']).'</td></tr>';
			print '<tr><td>'.__('E-mail', 'events-made-easy').'</td><td>'.eme_esc_html($person['email']).'</td></tr>';
			print '<tr><td>'.__('Address1', 'events-made-easy').'</td><td>'.eme_esc_html($person['address1']).'</td></tr>';
			print '<tr><td>'.__('Address2', 'events-made-easy').'</td><td>'.eme_esc_html($person['address2']).'</td></tr>';
			print '<tr><td>'.__('City', 'events-made-easy').'</td><td>'.eme_esc_html($person['city']).'</td></tr>';
			print '<tr><td>'.__('State', 'events-made-easy').'</td><td>'.eme_esc_html($person['state']).'</td></tr>';
			print '<tr><td>'.__('Zip', 'events-made-easy').'</td><td>'.eme_esc_html($person['zip']).'</td></tr>';
			print '<tr><td>'.__('Country', 'events-made-easy').'</td><td>'.eme_esc_html($person['country']).'</td></tr>';
			print '<tr><td>'.__('Phone number', 'events-made-easy').'</td><td>'.eme_esc_html($person['phone']).'</td></tr>';
			print '<tr><td>'.__('MassMail', 'events-made-easy').'</td><td>'.$massmail.'</td></tr>';
			foreach ($dyn_answers as $answer) {
				$formfield = eme_get_formfield($anwser['field_id']);
				$name = eme_trans_esc_html($formfield['field_name']);
				$tmp_answer=eme_trans_esc_html(eme_convert_answer2tag($answer['answer'],$formfield));
				print "<tr><td>$name</td><td>$tmp_answer</td></tr>";
			}
			print '</table>';
			print "<br />".__('Memberships','events-made-easy')."<br />";
			foreach ($members as $member) {
				$start_date = ($member['start_date'] == '0000-00-00' || $member['start_date'] == '' ) ? '' : eme_localized_date($member['start_date']);
				$end_date = ($member['end_date'] == '0000-00-00' || $member['end_date'] == '') ? '' : eme_localized_date($member['end_date']);
				print '<table>';
				print '<tr><td>'.__('ID', 'events-made-easy').'</td><td>'.intval($member['member_id']).'</td></tr>';
				print '<tr><td>'.__('Membership', 'events-made-easy').'</td><td>'.eme_esc_html($member['membership_name']).'</td></tr>';
				print '<tr><td>'.__('Start', 'events-made-easy').'</td><td>'.$start_date.'</td></tr>';
				print '<tr><td>'.__('End', 'events-made-easy').'</td><td>'.$end_date.'</td></tr>';
				$all_answers=eme_get_member_answers_all($member['member_id']);
				foreach ($all_answers as $answer) {
					$formfield = eme_get_formfield($answer['field_id']);
					$name = eme_trans_esc_html($formfield['field_name']);
					$tmp_answer=eme_trans_esc_html(eme_convert_answer2tag($answer['answer'],$formfield));
					print "<tr><td>$name</td><td>$tmp_answer</td></tr>";
				}
				print '</table>';
			}
			print "<hr />";
		}
	}
}

function eme_get_people_shortcode($atts) {
   extract(shortcode_atts(array(
      'group_id'  => 0,
      'template_id' => 0
   ), $atts));

   if ($group_id) {
      $grouppersons=eme_get_grouppersons(intval($group_id));
      $persons=eme_get_persons($grouppersons);
   } else {
      return;
   }

   $format="";
   if ($template_id) {
      $format = eme_get_template_format($template_id);
   }
   $output = "";
   foreach ($persons as $person) {
      $output .= eme_replace_people_placeholders($format,$person);
   }
   //$output = $eme_format_header . $output . $eme_format_footer;
   return $output;
}

function eme_replace_people_placeholders($format, $person, $target="html", $lang='') {
   preg_match_all("/#_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);
   usort($placeholders[0],'eme_sort_stringlenth');
   $dyn_answers = eme_get_dyndata_person_answers($person['person_id']);

   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_ID/', $result)) {
         $replacement = intval($person['person_id']);
      } elseif (preg_match('/#_(LASTNAME|FIRSTNAME|ZIP|CITY|STATE|COUNTRY|ADDRESS1|ADDRESS2|PHONE|EMAIL)/', $result)) {
         $field = preg_replace("/#_/","",$result);
         $field = strtolower($field);
         if ($field=="name") $field="lastname";
         $replacement = $person[$field];
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_MASSMAIL/', $result)) {
         $replacement = $person['massmail'] ? __('Yes') : __('No');
         if ($target == "html") {
            $replacement = eme_esc_html($replacement);
            $replacement = apply_filters('eme_general', $replacement); 
	 } else {
            $replacement = apply_filters('eme_text', $replacement); 
	 }
      } elseif (preg_match('/#_IMAGETITLE$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $info = eme_get_wp_image($person['properties']['image_id']);
	      $replacement = $info['title'];
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGEALT$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $info = eme_get_wp_image($person['properties']['image_id']);
	      $replacement = $info['alt'];
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGECAPTION$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $info = eme_get_wp_image($person['properties']['image_id']);
	      $replacement = $info['caption'];
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGEDESCRIPTION$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $info = eme_get_wp_image($person['properties']['image_id']);
	      $replacement = $info['description'];
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGE$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $replacement = wp_get_attachment_image($person['properties']['image_id'], 'full', 0, array('class'=>'eme_person_image') );
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGEURL$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $replacement = wp_get_attachment_image_url($person['properties']['image_id'],'full');
	 }
      } elseif (preg_match('/#_IMAGETHUMB$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $replacement = wp_get_attachment_image($person['properties']['image_id'], get_option('eme_thumbnail_size'), 0, array('class'=>'eme_person_image') );
	      if ($target == "html") {
		      $replacement = apply_filters('eme_general', $replacement);
	      } elseif ($target == "rss")  {
		      $replacement = apply_filters('eme_general_rss', $replacement);
	      } else {
		      $replacement = apply_filters('eme_text', $replacement);
	      }
	 }
      } elseif (preg_match('/#_IMAGETHUMBURL$/', $result)) {
	 if (!empty($person['properties']['image_id'])) {
	      $replacement = wp_get_attachment_image_url($person['properties']['image_id'], get_option('eme_thumbnail_size') );
	 }
      } elseif (preg_match('/#_IMAGETHUMB\{(.+)\}/', $result, $matches)) {
         if (!empty($person['properties']['image_id'])) {
            $replacement = wp_get_attachment_image( $person['properties']['image_id'], $matches[1], 0, array('class'=>'eme_person_image'));
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement);
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }
      } elseif (preg_match('/#_IMAGETHUMBURL\{(.+)\}/', $result, $matches)) {
         if (!empty($person['properties']['image_id'])) {
            $replacement = wp_get_attachment_image_url( $person['properties']['image_id'], $matches[1]);
         }
      } elseif (preg_match('/#_FIELDNAME\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $replacement = eme_trans_esc_html($formfield['field_name']);
      } elseif (preg_match('/#_FIELD\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
         $field_replace = "";
         foreach ($dyn_answers as $answer) {
            if ($answer['field_id'] == $field_id) {
               $tmp_answer=eme_convert_answer2tag($answer['answer'],$formfield);
               $field_replace=$tmp_answer;
            }
         }
         if ($target == "html") {
            $replacement = eme_trans_esc_html($field_replace,$lang);
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = eme_translate($field_replace,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }
      } elseif (preg_match('/#_FIELDVALUE\{(.+)\}/', $result, $matches)) {
         $field_key = $matches[1];
         $formfield = eme_get_formfield($field_key);
         $field_id = $formfield['field_id'];
         $field_replace = "";
         foreach ($dyn_answers as $answer) {
            if ($answer['field_id'] == $field_id) {
               if (is_array($answer['answer'])) {
                  $tmp_answer=eme_convert_array2multi($answer['answer']);
               } else {
                  $tmp_answer=$answer['answer'];
               }
	       $field_replace=$tmp_answer;
            }
         }
         if ($target == "html") {
            $replacement = eme_trans_esc_html($field_replace,$lang);
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = eme_translate($field_replace,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }
      } elseif (preg_match('/#_NICKNAME$/', $result)) {
         if ($person['wp_id']>0) {
            $user = get_userdata( $person['wp_id']);
            if ($user)
               $replacement=$user->user_nicename;
            if ($target == "html") {
               $replacement = eme_esc_html($replacement);
               $replacement = apply_filters('eme_general', $replacement); 
	    } else {
               $replacement = apply_filters('eme_text', $replacement); 
	    }
         }
      } elseif (preg_match('/#_DISPNAME$/', $result)) {
         if ($person['wp_id']>0) {
            $user = get_userdata( $person['wp_id']);
            if ($user)
               $replacement=$user->display_name;
            if ($target == "html") {
               $replacement = eme_esc_html($replacement);
               $replacement = apply_filters('eme_general', $replacement); 
	    } else {
               $replacement = apply_filters('eme_text', $replacement); 
	    }
         }
      } elseif (preg_match('/#_UNSUB_URL$/', $result)) {
         $replacement = eme_unsub_url();
      } else {
         $found = 0;
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }
   return $format;   
}

function eme_import_csv_people() {
	global $wpdb;
	$answers_table = $wpdb->prefix.ANSWERS_TBNAME;

	//validate whether uploaded file is a csv file
	$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	if (empty($_FILES['eme_csv']['name']) || !in_array($_FILES['eme_csv']['type'],$csvMimes)) {
		return sprintf(__('No CSV file detected: %s','events_made_eady'),$_FILES['eme_csv']['type']);
	}
	if (!is_uploaded_file($_FILES['eme_csv']['tmp_name'])) {
		return __('Problem detected while uploading the file','events_made_eady');
		return $result;
	}
	$updated=0;
	$inserted=0;
	$errors=0;
	$error_msg='';
	$handle = fopen($_FILES['eme_csv']['tmp_name'], "r");
	// get the first row as keys and lowercase them
	$headers = array_map('strtolower', fgetcsv($handle));

	// check required columns
	if (!in_array('lastname',$headers)||!in_array('firstname',$headers)||!in_array('email',$headers)) {
		$result = __("Not all required fields present.",'events-made-easy');
	} else {
		// now loop over the rest
		while (($row = fgetcsv($handle)) !== FALSE) {
			$line = array_combine($headers, $row);
			// remove columns with empty values
			$line = array_filter($line,'strlen');
			// we need at least 3 fields present, otherwise nothing will be done
			if (isset($_POST['allow_empty_email']) && $_POST['allow_empty_email']==1 && !isset($line['email'])) {
				$line['email']='';
				$line['massmail']=0;
			}
			if (isset($line['lastname']) && isset($line['firstname']) && isset($line['email'])) {
				// if the person already exists: update him
				$person = eme_get_person_by_name_and_email($line['lastname'],$line['firstname'],$line['email']);
				$person_id=0;
				if ($person) {
					$person_id=eme_db_update_person($person['person_id'],$line);
					if ($person_id) {
						$updated++;
					} else {
						$errors++;
						$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					}
				} else {
					$person_id=eme_db_insert_person($line);
					if ($person_id) {
						$inserted++;
					} else {
						$errors++;
						$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
					}
				}
				if ($person_id) {
					// now handle all the extra info, in the CSV they need to be named like 'answer_XX' (with 'XX' being either the fieldid or the fieldname, e.g. answer_myfieldname)
					foreach ($line as $key=>$value) {
						if (preg_match('/^answer_(.*)$/', $key, $matches)) {
							$grouping=0;
							$field_name = $matches[1];
							$formfield = eme_get_formfield($field_name);
							if ($formfield) {
								$field_id=$formfield['field_id'];
								$sql = $wpdb->prepare("INSERT INTO $answers_table (person_id,field_id,answer,grouping) VALUES (%d,%d,%s,%d)",$person_id,$field_id,$value,$grouping);
								$wpdb->query($sql);
							}
						}
					}

				}
			} else {
				$errors++;
				$error_msg.='<br />'.eme_esc_html(sprintf(__('Not imported: %s','events_made_easy'),implode(',',$row)));
			}

		}
	}
	fclose($handle);
	$result = sprintf(__('Import finished: %d inserts, %d updates, %d errors','events_made_eady'),$inserted,$updated,$errors);
	if ($errors) $result.='<br />'.$error_msg;
	return $result;
}

// a eme_fputcsv function to replace the original fputcsv
// reason: we want to enclose all fields with $enclosure
function eme_fputcsv ($fh, $fields, $delimiter = ';', $enclosure = '"', $mysql_null = false) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($field === null && $mysql_null) {
            $output[] = 'NULL';
            continue;
        }

        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s|\r|\t|\n)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $enclosure . $field . $enclosure;
    }

    fwrite($fh, join($delimiter, $output) . "\n");
}

function eme_csv_booking_report($event_id) {
   global $eme_timezone;
   $event = eme_get_event($event_id);
   $is_multiprice = eme_is_multi($event['price']);
   $current_userid=get_current_user_id();
   if (!(current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
        (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)))) {
        echo "No access";
        die;
   }

   $delimiter = get_option('eme_csv_separator');
    if (empty($delimiter))
      $delimiter = ',';

   //header("Content-type: application/octet-stream");
   header('Content-type: text/csv; charset=UTF-8');
   header('Content-Encoding: UTF-8');
   header("Content-Disposition: attachment; filename=\"export.csv\"");
   echo "\xEF\xBB\xBF"; // UTF-8 BOM, Excell otherwise doesn't show the characters correctly ...
   $bookings =  eme_get_bookings_for($event_id);
   $answer_columns = eme_get_answer_fieldids(eme_get_bookingids_for($event_id));
   $out = fopen('php://output', 'w');
   if (has_filter('eme_csv_header_filter')) {
      $line=apply_filters('eme_csv_header_filter',$event);
      eme_fputcsv($out,$line,$delimiter);
   }
   $line=array();
   $line[]=__('ID', 'events-made-easy');
   $line[]=__('Last Name', 'events-made-easy');
   $line[]=__('First Name', 'events-made-easy');
   $line[]=__('Address1', 'events-made-easy');
   $line[]=__('Address2', 'events-made-easy');
   $line[]=__('City', 'events-made-easy');
   $line[]=__('State', 'events-made-easy');
   $line[]=__('Zip', 'events-made-easy');
   $line[]=__('Country', 'events-made-easy');
   $line[]=__('E-mail', 'events-made-easy');
   $line[]=__('Phone number', 'events-made-easy');
   $line[]=__('MassMail', 'events-made-easy');
   if ($is_multiprice)
      $line[]=__('Seats (Multiprice)', 'events-made-easy');
   else
      $line[]=__('Seats', 'events-made-easy');
   $line[]=__('Paid', 'events-made-easy');
   $line[]=__('Booking date','events-made-easy');
   $line[]=__('Discount','events-made-easy');
   $line[]=__('Total price','events-made-easy');
   $line[]=__('Unique nbr','events-made-easy');
   $line[]=__('Comment', 'events-made-easy');
   foreach($answer_columns as $col) {
      $tmp_formfield=eme_get_formfield($col['field_id']);
      $line[]=$tmp_formfield['field_name'];
   }
   $line_nbr=1;
   if (has_filter('eme_csv_column_filter'))
      $line=apply_filters('eme_csv_column_filter',$line,$event,$line_nbr);

   eme_fputcsv($out,$line,$delimiter);
   foreach($bookings as $booking) {
      $localized_booking_date = eme_localized_date($booking['creation_date']." ".$eme_timezone);
      $localized_booking_time = eme_localized_time($booking['creation_date']." ".$eme_timezone);
      $person = eme_get_person ($booking['person_id']);
      $line=array();
      $pending_string="";
      if ($booking['waitinglist']) {
         $pending_string=__('(waiting list)','events-made-easy');
      } elseif (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
         $pending_string=__('(pending)','events-made-easy');
      }
      $line[]=$booking['booking_id'];
      $line[]=$person['lastname'];
      $line[]=$person['firstname'];
      $line[]=$person['address1'];
      $line[]=$person['address2'];
      $line[]=$person['city'];
      $line[]=$person['state'];
      $line[]=$person['zip'];
      $line[]=$person['country'];
      $line[]=$person['email'];
      $line[]=$person['phone'];
      $line[]=$person['massmail'] ? __('Yes'): __('No');
      if ($is_multiprice) {
         // in cases where the event switched to multiprice, but somebody already registered while it was still single price: booking_seats_mp is then empty
         if ($booking['booking_seats_mp'] == "")
            $booking['booking_seats_mp']=$booking['booking_seats'];
         $line[]=$booking['booking_seats']." (".$booking['booking_seats_mp'].") ".$pending_string;
      } else {
         $line[]=$booking['booking_seats']." ".$pending_string;
      }
      $line[]=$booking['booking_paid'] ? __('Yes'): __('No');
      $line[]=$localized_booking_date." ".$localized_booking_time;
      $discount_name="";
      if ($booking['dgroupid']) {
         $dgroup=eme_get_discountgroup($booking['dgroupid']);
         if ($dgroup && isset($dgroup['name']))
		 $discount_name='('.$dgroup['name'].')';
         else
		 $discount_name='('.__('Applied discount no longer exists','events-made-easy').')';
      } elseif ($booking['discountid']) {
         $discount=eme_get_discount($booking['discountid']);
         if ($discount && isset($discount['name']))
		 $discount_name='('.$discount['name'].')';
         else
		 $discount_name='('.__('Applied discount no longer exists','events-made-easy').')';
      }
      $line[]=eme_localized_price($booking['discount'],$event['currency'],"text").$discount_name;
      $line[]=eme_localized_price(eme_get_total_booking_price($booking),$event['currency'],"text");
      $line[]=$booking['transfer_nbr_be97'];
      $line[]=$booking['booking_comment'];
      $answers = eme_get_booking_answers($booking['booking_id']);
      foreach($answer_columns as $col) {
         $found=0;
         foreach ($answers as $answer) {
            if ($answer['field_id'] == $col['field_id']) {
	       $tmp_formfield=eme_get_formfield($answer['field_id']);
               $line[]=eme_convert_answer2tag($answer['answer'],$tmp_formfield);
               $found=1;
               break;
            }
         }
         # to make sure the number of columns are correct, we add an empty answer if none was found
         if (!$found)
            $line[]="";
      }

      # add dynamic fields to the right
      if (isset($event['event_properties']['rsvp_dyndata'])) {
	      $answers = eme_get_dyndata_booking_answers($booking['booking_id']);
	      foreach ($answers as $answer) {
		      $grouping=$answer['grouping'];
		      $occurence=$answer['occurence'];
		      $tmp_formfield=eme_get_formfield($answer['field_id']);
		      $line[]="$grouping.$occurence ".$tmp_formfield['field_name'].": ".eme_convert_answer2tag($answer['answer'],$tmp_formfield)."\n";
	      }
      }

      $line_nbr++;
      if (has_filter('eme_csv_column_filter'))
	      $line=apply_filters('eme_csv_column_filter',$line,$event,$line_nbr);
      eme_fputcsv($out,$line,$delimiter);
   }

   if (has_filter('eme_csv_footer_filter')) {
      $line=apply_filters('eme_csv_footer_filter',$event);
      eme_fputcsv($out,$line,$delimiter);
   }
   fclose($out);
   die();
}

function eme_printable_booking_report($event_id) {
   global $eme_timezone;
   $event = eme_get_event($event_id);
   $current_userid=get_current_user_id();
   if (!(current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
        (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)))) {
        echo "No access";
        die;
   }

   $is_multiprice = eme_is_multi($event['price']);
   $is_multiseat = eme_is_multi($event['event_seats']);
   $bookings = eme_get_bookings_for($event_id);
   $answer_columns = eme_get_answer_fieldids(eme_get_bookingids_for($event_id));
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $pending_seats = eme_get_pending_seats($event_id);
   if ($is_multiseat) {
      $available_seats_ms=eme_convert_array2multi(eme_get_available_multiseats($event_id));
      $booked_seats_ms=eme_convert_array2multi(eme_get_booked_multiseats($event_id));
      $pending_seats_ms=eme_convert_array2multi(eme_get_pending_multiseats($event_id));
   }

   $stylesheet = EME_PLUGIN_URL."css/eme.css";
   ?>
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html>
      <head>
         <meta http-equiv="Content-type" content="text/html; charset=utf-8">
         <title><?php echo __('Bookings for', 'events-made-easy')." ".eme_trans_esc_html($event['event_name']);?></title>
          <link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" media="screen" />
          <?php
            $file_name= get_stylesheet_directory()."/eme.css";
            if (file_exists($file_name))
               echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css' media='screen' />\n";
            $file_name= get_stylesheet_directory()."/eme_print.css";
            if (file_exists($file_name))
               echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme_print.css' type='text/css' media='print' />\n";
          ?>
      </head>
      <body id="eme_printable_body">
         <div id="eme_printable_container">
         <h1><?php echo __('Bookings for', 'events-made-easy')." ".eme_trans_esc_html($event['event_name']);?></h1> 
         <p><?php echo eme_localized_date($event['event_start_date']." ".$event['event_start_time']." ".$eme_timezone); ?></p>
         <p><?php if ($event['location_id']) echo eme_replace_event_location_placeholders("#_LOCATIONNAME, #_ADDRESS, #_TOWN", $event); ?></p>
         <?php if ($event['price']) ?>
            <p><?php _e ( 'Price: ','events-made-easy'); echo eme_replace_placeholders("#_PRICE", $event); ?></p>
         <h1><?php _e('Bookings data', 'events-made-easy');?></h1>
         <table id="eme_printable_table">
            <tr>
               <th scope='col' class='eme_print_id'><?php _e('ID', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_name'><?php _e('Last Name', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_name'><?php _e('First Name', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_email'><?php _e('E-mail', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_phone'><?php _e('Phone number', 'events-made-easy'); ?></th> 
               <th scope='col' class='eme_print_seats'><?php if ($is_multiprice) _e('Seats (Multiprice)', 'events-made-easy'); else _e('Seats', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_paid'><?php _e('Paid', 'events-made-easy')?></th>
               <th scope='col' class='eme_print_booking_date'><?php _e('Booking date', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_discount'><?php _e('Discount', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_total_price'><?php _e('Total price', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_unique_nbr'><?php _e('Unique nbr', 'events-made-easy'); ?></th>
               <th scope='col' class='eme_print_comment'><?php _e('Comment', 'events-made-easy'); ?></th> 
            <?php
            $nbr_columns=12;
            foreach($answer_columns as $col) {
               $class="eme_print_formfield".$col['field_id'];
               $tmp_formfield=eme_get_formfield($col['field_id']);
               print "<th scope='col' class='$class'>".$tmp_formfield['field_name']."</th>";
               $nbr_columns++;
            }
            ?>
            </tr>
            <?php
            foreach($bookings as $booking) {
               $localized_booking_date = eme_localized_date($booking['creation_date']." ".$eme_timezone);
               $localized_booking_time = eme_localized_time($booking['creation_date']." ".$eme_timezone);
               $person = eme_get_person ($booking['person_id']);
               $pending_string="";
               if ($booking['waitinglist']) {
                  $pending_string=__('(waiting list)','events-made-easy');
	       } elseif (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
                  $pending_string=__('(pending)','events-made-easy');
               }
                ?>
            <tr>
               <td class='eme_print_id'><?php echo $booking['booking_id']?></td> 
               <td class='eme_print_name'><?php echo $person['lastname']?></td> 
               <td class='eme_print_name'><?php echo $person['firstname']?></td> 
               <td class='eme_print_email'><?php echo $person['email']?></td>
               <td class='eme_print_phone'><?php echo $person['phone']?></td>
               <td class='eme_print_seats' class='seats-number'><?php 
               if ($is_multiprice) {
                  // in cases where the event switched to multiprice, but somebody already registered while it was still single price: booking_seats_mp is then empty
                  if ($booking['booking_seats_mp'] == "")
                     $booking['booking_seats_mp']=$booking['booking_seats'];
                  echo $booking['booking_seats']." (".$booking['booking_seats_mp'].") ".$pending_string;
               } else {
                  echo $booking['booking_seats']." ".$pending_string;
               }
               ?>
               </td>
               <td class='eme_print_paid'><?php if ($booking['booking_paid']) _e('Yes'); else _e('No'); ?></td>
               <td class='eme_print_booking_date'><?php echo $localized_booking_date." ".$localized_booking_time; ?></td>
               <td class='eme_print_discount'><?php
	       $discount_name="";
	       if ($booking['dgroupid']) {
		       $dgroup=eme_get_discountgroup($booking['dgroupid']);
		       if ($dgroup && isset($dgroup['name']))
			       $discount_name='<br />'.$dgroup['name'];
		       else
			       $discount_name='<br />'.__('Applied discount no longer exists','events-made-easy');
	       } elseif ($booking['discountid']) {
		       $discount=eme_get_discount($booking['discountid']);
		       if ($discount && isset($discount['name']))
			       $discount_name='<br />'.$discount['name'];
		       else
			       $discount_name='<br />'.__('Applied discount no longer exists','events-made-easy');
	       }
               echo eme_localized_price($booking['discount'],$event['currency']).$discount_name; ?>
               </td>
               <td class='eme_print_total_price'><?php echo eme_localized_price(eme_get_total_booking_price($booking),$event['currency']); ?></td>
               <td class='eme_print_unique_nbr'><?php echo $booking['transfer_nbr_be97']; ?></td>
               <td class='eme_print_comment'><?=$booking['booking_comment'] ?></td> 
               <?php
                  $answers = eme_get_booking_answers($booking['booking_id']);
                  foreach($answer_columns as $col) {
                     $found=0;
                     foreach ($answers as $answer) {
                        if ($answer['field_id'] == $col['field_id']) {
                           $class="eme_print_formfield".$answer['field_id'];
			   $tmp_formfield=eme_get_formfield($answer['field_id']);
                           print "<td class='$class'>".eme_esc_html(eme_convert_answer2tag($answer['answer'],$tmp_formfield))."</td>";
                           $found=1;
                           break;
                        }
                     }
                     # to make sure the number of columns are correct, we add an empty answer if none was found
                     if (!$found)
                        print "<td class='$class'>&nbsp;</td>";
                  }
               ?>
            </tr>
            <tr>
	       <td>&nbsp;</td>
	       <td colspan='<?php echo $nbr_columns-1;?>' style='text-align: left;' >
               <?php
	       #$answers = eme_get_booking_answers($booking['booking_id']);
	       #foreach ($answers as $answer) {
	#	       $class="eme_print_formfield".$answer['field_id'];
	#	       $tmp_formfield=eme_get_formfield($answer['field_id']);
	#	       print "<span class='$class'>".eme_esc_html($tmp_formfield['field_name']).": ".eme_esc_html(eme_convert_answer2tag($answer))."</span><br />";
	#       }
	       if (isset($event['event_properties']['rsvp_dyndata'])) {
		       $answers = eme_get_dyndata_booking_answers($booking['booking_id']);
		       foreach ($answers as $answer) {
			       $grouping=$answer['grouping'];
			       $occurence=$answer['occurence'];
			       $class="eme_print_formfield".$answer['field_id'];
			       $tmp_formfield=eme_get_formfield($answer['field_id']);
			       print "<span class='$class'>$grouping.$occurence ".eme_esc_html($tmp_formfield['field_name']).": ".eme_esc_html(eme_convert_answer2tag($answer['answer'],$tmp_formfield))."</span><br />";
		       }
	       }
               ?>
	       <td>
            </tr>
               <?php } ?>
            <tr id='eme_printable_booked-seats'>
               <td colspan='<?php echo $nbr_columns-4;?>'>&nbsp;</td>
               <td class='total-label'><?php _e('Booked', 'events-made-easy')?>:</td>
               <td colspan='3' class='seats-number'><?php
               print $booked_seats;
               if ($is_multiseat) print " ($booked_seats_ms)";
			      if ($pending_seats>0) {
                  if ($is_multiseat)
                     print " ".sprintf( __('(%d pending)','events-made-easy'), $pending_seats) . " ($pending_seats_ms)";
                  else
                     print " ".sprintf( __('(%d pending)','events-made-easy'), $pending_seats);
               }
               ?>
            </td>
            </tr>
            <tr id='eme_printable_available-seats'>
               <td colspan='<?php echo $nbr_columns-4;?>'>&nbsp;</td>
               <td class='total-label'><?php _e('Available', 'events-made-easy')?>:</td>
               <td colspan='3' class='seats-number'><?php print $available_seats; if ($is_multiseat) print " ($available_seats_ms)"; ?></td>
            </tr>
         </table>
         </div>
      </body>
      </html>
      <?php
      die();
} 

function eme_manage_people_layout($message="") {
   $nonce_field = wp_nonce_field('eme_people','eme_admin_nonce',false,false);
   $groups=eme_get_groups();
   $pdftemplates = eme_get_templates('pdf');

   if (empty($message))
	   $hidden_style="display:none;";
   else
	   $hidden_style="";

   ?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <div id="people-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204); <?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Add a new person', 'events-made-easy') ?></h1>
         <div class="wrap">
         <form id="people-filter" method="post" action="<?php echo admin_url("admin.php?page=eme-people"); ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_person" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add person', 'events-made-easy');?>" />
         </form>
         </div>

         <h1><?php _e('People', 'events-made-easy') ?></h1>

   <?php if (current_user_can(get_option('eme_cap_cleanup'))) { ?>
   <span class="eme_import_form_img">
   <?php _e('Click on the icon to show the import form','events-made-easy'); ?>
   <img src="<?php echo EME_PLUGIN_URL;?>images/showhide.png" class="showhidebutton" alt="show/hide" data-showhide="div_import" style="cursor: pointer; vertical-align: middle; ">
   </span>
   <div id='div_import' name='div_import' style='display:none;'>
   <form id='people-import' method='post' enctype='multipart/form-data' action='#'>
   <?php echo $nonce_field; ?>
   <input type="file" name="eme_csv" />
   <input type="hidden" name="eme_admin_action" value="import" />
   <?php _e('Allow empty email?','events-made-easy'); echo eme_ui_select_binary('','allow_empty_email'); ?>
   <input type="submit" value="<?php _e ( 'Import','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   <?php _e('If you want, use this to import people info into the database', 'events-made-easy'); ?>
   </form>
   </div>
   <br />
   <?php } ?>
   <br />
   <form action="#" method="post">
   <input type="text" name="search_name" id="search_name" class="clearable" placeholder="<?php _e('Person name','events-made-easy'); ?>" size=10>
   <?php echo eme_ui_select_key_value('','search_group',$groups,'group_id','name',__('Any group','events-made-easy'),0); ?>
   <?php
   $formfields=eme_get_formfields('','people');
   $extrafields_arr=array();
   $extrafieldnames_arr=array();
   foreach ($formfields as $formfield) {
	   $extrafields_arr[]=$formfield['field_id'];
	   $extrafieldnames_arr[]=eme_esc_html($formfield['field_name']);
   }
   // these 2 values are used as data-fields to the container-div, and are used by the js to create extra columns
   $extrafields=join(',',$extrafields_arr);
   $extrafieldnames=join(',',$extrafieldnames_arr);
   echo eme_ui_multiselect_key_value('','search_customfieldids',$formfields,'field_id','field_name','',5,0,'eme_select2_filter_class');
   ?>
   <button id="PeopleLoadRecordsButton" class="button-secondary action"><?php _e('Filter people','events-made-easy'); ?></button>
   </form>

   <form id='people-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deletePeople"><?php _e ( 'Delete selected persons','events-made-easy'); ?></option>
   <option value="sendMails"><?php _e ( 'Send email','events-made-easy'); ?></option>
   <option value="pdf"><?php _e ( 'PDF output','events-made-easy'); ?></option>
   </select>
   <span id="span_transferto">
   <?php _e('Transfer associated bookings to (leave empty for deleting those too):','events-made-easy'); ?>
   <input type='hidden' id='transferto_id' name='transferto_id'>
   <input type='text' id='chooseperson' name='chooseperson' class="clearable" placeholder="<?php _e('Start typing a name','events-made-easy'); ?>">
   </span>
   <span id="span_pdftemplate">
   <?php echo eme_ui_select_key_value('',"pdf_template",$pdftemplates,'id','name',__('Please select a template','events-made-easy'),1);?>
   </span>
   <button id="PeopleActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
	   <div id="PeopleTableContainer" data-extrafields='<?php echo $extrafields;?>' data-extrafieldnames='<?php echo $extrafieldnames;?>'></div>
   </form>
   </div>
   </div>
<?php
}

function eme_person_edit_layout($person, $message = "") {
   global $plugin_page;

   if (!isset($person['person_id'])) {
      $action="add";
      $persongroups=array();
   } else {
      $action="edit";
      $persongroups=eme_get_persongroups($person['person_id']);
   }
   $nonce_field = wp_nonce_field('eme_people','eme_admin_nonce',false,false);
   $groups=eme_get_groups();
   ?>
   <div class="wrap">
      <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <h1><?php if ($action=="add")
                     _e('Add person', 'events-made-easy');
                   else
                     _e('Edit person', 'events-made-easy');
             ?></h1>

         <?php if ($message != "") { ?>
            <div id="message" class="updated fade below-h1" style="background-color: rgb(255, 251, 204);">
               <p><?php  echo $message ?></p>
            </div>
         <?php } ?>
         <div id="ajax-response"></div>
         <form name="editperson" id="editperson" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>" class="validate">
         <?php echo $nonce_field; ?>
         <?php if ($action == "add") { ?>
         <input type="hidden" name="eme_admin_action" value="do_addperson" />
         <?php } else { ?>
         <input type="hidden" name="eme_admin_action" value="do_editperson" />
         <input type="hidden" name="person_id" value="<?php echo $person['person_id'] ?>" />
         <?php } ?>

         <div id="div_person" class="postbox">
            <div class="inside">
	    <table>
            <tr>
            <td><label for="lastname"><?php _e('Last Name', 'events-made-easy') ?></label></td>
            <td><input id="lastname" name="lastname" type="text" value="<?php echo eme_esc_html($person['lastname']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="firstname"><?php _e('First Name', 'events-made-easy') ?></label></td>
            <td><input id="firstname" name="firstname" type="text" value="<?php echo eme_esc_html($person['firstname']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="email"><?php _e('E-Mail', 'events-made-easy') ?></label></td>
            <td><input id="email" name="email" type="email" value="<?php echo eme_esc_html($person['email']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="phone"><?php _e('Phone Number', 'events-made-easy') ?></label></td>
            <td><input id="phone" name="phone" type="text" value="<?php echo eme_esc_html($person['phone']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="address1"><?php _e('Address1', 'events-made-easy') ?></label></td>
            <td><input id="address1" name="address1" type="text" value="<?php echo eme_esc_html($person['address1']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="address2"><?php _e('Address2', 'events-made-easy') ?></label></td>
            <td><input id="address2" name="address2" type="text" value="<?php echo eme_esc_html($person['address2']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="city"><?php _e('City', 'events-made-easy') ?></label></td>
            <td><input name="city" id="city" type="text" value="<?php echo eme_esc_html($person['city']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="state"><?php _e('State', 'events-made-easy') ?></label></td>
            <td><input name="state" id="state" type="text" value="<?php echo eme_esc_html($person['state']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="zip"><?php _e('Zip', 'events-made-easy') ?></label></td>
            <td><input name="zip" id="zip" type="text" value="<?php echo eme_esc_html($person['zip']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="country"><?php _e('Country', 'events-made-easy') ?></label></td>
            <td><input name="country" id="country" type="text" value="<?php echo eme_esc_html($person['country']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="massmail"><?php _e('MassMail', 'events-made-easy') ?></label></td>
	    <td><?php echo eme_ui_select_binary($person['massmail'],'massmail'); ?></td>
            </tr>
            <tr>
            <td><label for="groups"><?php _e('Groups', 'events-made-easy') ?></label></td>
	    <td><?php echo eme_ui_multiselect_key_value($persongroups,'groups',$groups,'group_id','name','',5,0,'dyngroups eme_select2_class'); ?><br />
                <?php _e("Don't forget that you can define custom fields with purpose 'People' that will allow extra info based on the group the person is in.",'events-made-easy');?>
            </td>
            </tr>
            </table>
            </div>
            <?php person_image_div($person); ?>
            <div class='inside' id='eme_dynpersondata'></div>
         </div>
         <p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php if ($action=="add") _e('Add person', 'events-made-easy'); else _e('Update person', 'events-made-easy'); ?>" /></p>
      </form>
   </div>
<?php
}

function eme_group_edit_layout($group, $message = "") {
   global $plugin_page;

   $grouppersons=array();
   $mygroups=array();
   if (!isset($group['group_id'])) {
      $action="add";
   } else {
      $action="edit";
      $grouppersons=eme_get_grouppersons($group['group_id']);
      // when editing, select2 needs a populated list of selected items
      $persons=eme_get_persons($grouppersons);
      foreach ($persons as $person) {
	      $mygroups[$person['person_id']]=$person['lastname'].' '.$person['firstname'];
      }
   }
   $nonce_field = wp_nonce_field('eme_people','eme_admin_nonce',false,false);
   ?>
   <div class="wrap">
      <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <h1><?php if ($action=="add")
                     _e('Add group', 'events-made-easy');
                   else
                     _e('Edit group', 'events-made-easy');
             ?></h1>

         <?php if ($message != "") { ?>
            <div id="message" class="updated fade below-h1" style="background-color: rgb(255, 251, 204);">
               <p><?php  echo $message ?></p>
            </div>
         <?php } ?>
         <div id="ajax-response"></div>
         <form name="editgroup" id="editgroup" method="post" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>" class="validate">
         <?php echo $nonce_field; ?>
         <?php if ($action == "add") { ?>
         <input type="hidden" name="eme_admin_action" value="do_addgroup" />
         <?php } else { ?>
         <input type="hidden" name="eme_admin_action" value="do_editgroup" />
         <input type="hidden" name="group_id" value="<?php echo $group['group_id'] ?>" />
         <?php } ?>

         <!-- we need titlediv and title for qtranslate as ID -->
         <div id="titlediv" class="postbox">
            <div class="inside">
	    <table>
            <tr>
            <td><label for="name"><?php _e('Name', 'events-made-easy') ?></label></td>
            <td><input required='required' id="name" name="name" type="text" value="<?php echo eme_esc_html($group['name']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="description"><?php _e('Description', 'events-made-easy') ?></label></td>
            <td><input id="description" name="description" type="text" value="<?php echo eme_esc_html($group['description']); ?>" size="40" /></td>
            </tr>
            <tr>
            <td><label for="People"><?php _e('People', 'events-made-easy') ?></label></td>
	    <td><?php echo eme_ui_multiselect($grouppersons,'persons',$mygroups,5,0,'eme_select2_people_class'); ?></td>
            </tr>
            </table>
            </div>
         </div>
         <p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php if ($action=="add") _e('Add group', 'events-made-easy'); else _e('Update group', 'events-made-easy'); ?>" /></p>
      </div>
      </form>
   </div>
<?php
}

function eme_manage_groups_layout($message="") {
   $nonce_field = wp_nonce_field('eme_people','eme_admin_nonce',false,false);
   if (empty($message))
           $hidden_style="display:none;";
   else
           $hidden_style="";
   ?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>

         <div id="groups-message" class="notice is-dismissible" style="background-color: rgb(255, 251, 204);<?php echo $hidden_style; ?>">
               <p><?php echo $message; ?></p>
         </div>

         <h1><?php _e('Add a new group', 'events-made-easy') ?></h1>
         <div class="wrap">
         <form id="groups-filter" method="post" action="<?php echo admin_url("admin.php?page=eme-groups"); ?>">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="eme_admin_action" value="add_group" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Add group', 'events-made-easy');?>" />
         </form>
         </div>

         <h1><?php _e('Groups', 'events-made-easy') ?></h1>

   <form id='groups-form' action="#" method="post">
   <?php echo $nonce_field; ?>
   <select id="eme_admin_action" name="eme_admin_action">
   <option value="" selected="selected"><?php _e ( 'Bulk Actions' , 'events-made-easy'); ?></option>
   <option value="deleteGroups"><?php _e ( 'Delete selected groups','events-made-easy'); ?></option>
   </select>
   <button id="GroupsActionsButton" class="button-secondary action"><?php _e ( 'Apply' , 'events-made-easy'); ?></button>
   <span class="rightclickhint">
      <?php _e('Hint: rightclick on the column headers to show/hide columns','events-made-easy'); ?>
   </span>
   <div id="GroupsTableContainer"></div>
   </form>
   </div>
   </div>
   <?php
}

function person_image_div($person) {
    if ($person['properties']['image_id']>0)
       $image_url = wp_get_attachment_url($person['properties']['image_id']);
    else
       $image_url = '';
?>
<div id="div_person_image">
      <br />
      <?php
        echo "<b>". __('Person image', 'events-made-easy') . "</b>";
      ?>
   <div id="no_image" class="postarea">
      <?php _e('No image set','events-made-easy'); ?>
   </div>
   <div id="current_image" class="postarea">
   <img id='person_image_example' alt='<?php  _e('Person image','events-made-easy'); ?>' src='<?php echo $image_url;?>' />
   <input type='hidden' name='properties[image_id]' id='image_id' value='<?php echo $person['properties']['image_id'];?>' />
   </div>
   <br />

   <div class="uploader">
   <input type="button" name="image_button" id="image_button" value="<?php _e ( 'Set image', 'events-made-easy')?>" />
   <input type="button" id="remove_old_image" name="remove_old_image" value=" <?php _e ( 'Unset image', 'events-made-easy')?>" />
   </div>
</div>
<?php 
}

// API function for people wanting to check if somebody is already registered
function eme_get_person_by_post() {
   $booker=array();
   if (isset($_POST['lastname']) && isset($_POST['email'])) {
      $lastname = eme_strip_tags($_POST['lastname']);
      if (isset($_POST['firstname']))
         $firstname = eme_strip_tags($_POST['firstname']);
      else
         $firstname = "";
      $email = eme_strip_tags($_POST['email']);
      $person = eme_get_person_by_name_and_email($lastname, $firstname, $email);
   }
   return $person;
}

function eme_count_persons_by_email($email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT COUNT(*) FROM $people_table WHERE email = %s",$email);
   return $wpdb->get_var($sql);
}

function eme_get_personids_by_email($email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT person_id FROM $people_table WHERE email = %s",$email);
   return $wpdb->get_col($sql);
}

function eme_get_person_by_name_and_email($lastname, $firstname, $email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   if (!empty($firstname))
      $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE lastname = %s AND firstname = %s AND email = %s",$lastname,$firstname,$email);
   else
      $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE lastname = %s AND email = %s",$lastname,$email);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_person_by_wp_id($wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE wp_id = %d LIMIT 1",$wp_id);
   $result = $wpdb->get_row($sql, ARRAY_A);
   if (!is_null($result['wp_id']) && $result['wp_id']) {
      $user_info = get_userdata($result['wp_id']);
      $result['lastname']=$user_info->user_lastname;
      if (empty($result['lastname']))
         $result['lastname']=$user_info->display_name;
      $result['firstname']=$user_info->user_firstname;
      $result['email']=$user_info->user_email;
   }
   return $result;
}

function eme_delete_persons_without_bookings() {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   // first, clean up unreferenced bookings
   $sql = "DELETE FROM $bookings_table WHERE event_id NOT IN (SELECT DISTINCT event_id FROM $events_table)";
   $wpdb->query($sql);
   // now, delete unreferenced persons
   $sql = "DELETE FROM $people_table WHERE person_id NOT IN (SELECT DISTINCT person_id FROM $bookings_table)";
   $wpdb->query($sql);
   return 1;
}

function eme_get_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE person_id = %d",$person_id);
   $person = $wpdb->get_row($sql, ARRAY_A);
   $person['properties']=eme_init_person_props(unserialize($person['properties']));
   return $person;
}

function eme_delete_person($person_id) {
   global $wpdb;
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = "DELETE FROM $people_table WHERE person_id = '".intval($person_id)."'";
   $wpdb->query($sql);
   $sql = "DELETE FROM $usergroups_table WHERE person_id = '".intval($person_id)."'";
   $wpdb->query($sql);
   return 1;
}

function eme_get_group($group_id) {
   global $wpdb; 
   $groups_table = $wpdb->prefix.GROUPS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $groups_table WHERE group_id = %d",$group_id);
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_groups() {
   global $wpdb; 
   $table = $wpdb->prefix.GROUPS_TBNAME;
   $sql = "SELECT * FROM $table ORDER BY name";
   return $wpdb->get_results($sql, ARRAY_A );
}

function eme_get_persongroups($person_id) {
   global $wpdb; 
   $table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = $wpdb->prepare("SELECT group_id FROM $table WHERE person_id = %d", $person_id);
   return $wpdb->get_col($sql);
}

function eme_get_grouppersons($group_id) {
   global $wpdb; 
   $table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = $wpdb->prepare("SELECT person_id FROM $table WHERE group_id = %d", $group_id);
   return $wpdb->get_col($sql);
}

function eme_update_persongroups($person_id,$groups) {
   global $wpdb; 
   $table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = $wpdb->prepare("DELETE from $table WHERE person_id = %d", $person_id);
   $wpdb->query($sql);
   foreach ($groups as $group_id) {
	   $sql = $wpdb->prepare("INSERT INTO $table (person_id,group_id) VALUES (%d,%d)", $person_id, $group_id);
	   $wpdb->query($sql);
   }
}

function eme_update_grouppersons($group_id,$persons) {
   global $wpdb; 
   $table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = $wpdb->prepare("DELETE from $table WHERE group_id = %d", $group_id);
   $wpdb->query($sql);
   foreach ($persons as $person_id) {
	   $sql = $wpdb->prepare("INSERT INTO $table (person_id,group_id) VALUES (%d,%d)", $person_id, $group_id);
	   $wpdb->query($sql);
   }
}

function eme_delete_group($group_id) {
   global $wpdb;
   $groups_table = $wpdb->prefix.GROUPS_TBNAME;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $groups_table WHERE group_id = %d",$group_id);
   $wpdb->query($sql);
   $sql = $wpdb->prepare("DELETE FROM $usergroups_table WHERE group_id = %d",$group_id);
   $wpdb->query($sql);
}

function eme_get_persons($person_ids="",$extra_search="") {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   if (!empty($person_ids) && eme_array_integers($person_ids)) {
      $tmp_ids=join(",",$person_ids);
      $sql = "SELECT * FROM $people_table WHERE person_id IN ($tmp_ids)";
      if (!empty($extra_search)) $sql.=" AND $extra_search";
   } else {
      $sql = "SELECT * FROM $people_table";
      if (!empty($extra_search)) $sql.=" WHERE $extra_search";
   }
   $persons = $wpdb->get_results($sql, ARRAY_A);
   //return $lines;
   $result = array();
   foreach ($persons as $person) {
      # to be able to sort on person names, we need a hash starting with the name
      # but some people might have the same name (or register more than once),
      # so we add the ID to it
      $unique_id=$person['lastname']."_".$person['firstname']."_".$person['person_id'];
      $person['properties']=eme_init_person_props(unserialize($person['properties']));
      $result[$unique_id]=$person;
   }
   # now do the sorting
   ksort($result);
   return $result;
}

function eme_get_massmail_persons($except_event_id=0) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $except_event_id = intval ($except_event_id);
   if ($except_event_id)
	   $sql = "SELECT person_id FROM $people_table WHERE massmail=1 AND email<>'' AND person_id NOT IN (SELECT DISTINCT person_id FROM $bookings_table WHERE event_id = $except_event_id) GROUP BY email";
   else
	   $sql = "SELECT person_id FROM $people_table WHERE massmail=1 AND email<>'' GROUP BY email";
   return $wpdb->get_col($sql);
}

function eme_get_massmail_groups($group_ids) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = "SELECT people.person_id FROM $people_table AS people LEFT JOIN $usergroups_table as ugroups ON people.person_id=ugroups.person_id WHERE people.massmail=1 AND ugroups.group_id IN ($group_ids) GROUP BY people.email";
   return $wpdb->get_col($sql);
}
function eme_get_massmail_members($membership_ids) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $sql = "SELECT members.member_id FROM $people_table AS people LEFT JOIN $members_table as members ON people.person_id=members.person_id WHERE people.massmail=1 AND members.state=1 AND members.membership_id IN ($membership_ids) GROUP BY people.email";
   return $wpdb->get_col($sql);
}

function eme_db_insert_person($line) {
   global $wpdb; 
   $table = $wpdb->prefix.PEOPLE_TBNAME;

   $person=eme_new_person();
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$person);
   $new_line=array_merge($person,$keys);
   if (!is_serialized($new_line['properties']))
	   $new_line['properties'] = serialize($new_line['properties']);

   if ($wpdb->insert($table,$new_line) === false) {
      return false;
   } else {
      return $wpdb->insert_id;
   }
}

function eme_db_insert_group($line) {
   global $wpdb; 
   $table = $wpdb->prefix.GROUPS_TBNAME;

   $group=eme_new_group();
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$group);
   $new_line=array_merge($group,$keys);

   if ($wpdb->insert($table,$new_line) === false) {
      return false;
   } else {
      return $wpdb->insert_id;
   }
}

function eme_db_update_person($person_id,$line) {
   global $wpdb; 
   $table = $wpdb->prefix.PEOPLE_TBNAME;
   $where = array();
   $where['person_id'] = intval($person_id);

   $person=eme_get_person($person_id);
   unset($person['person_id']);
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$person);
   $new_line=array_merge($person,$keys);
   if (!is_serialized($new_line['properties']))
	   $new_line['properties'] = serialize($new_line['properties']);

   if (!empty($new_line) && $wpdb->update($table, $new_line, $where) === false) {
      return false;
   } else {
      return $person_id;
   }
}

function eme_db_update_group($group_id,$line) {
   global $wpdb; 
   $table = $wpdb->prefix.GROUPS_TBNAME;
   $where = array();
   $where['group_id'] = intval($group_id);

   $group=eme_get_group($group_id);
   unset($group['group_id']);
   // we only want the columns that interest us
   // we need to do this since this function is also called for csv import
   $keys=array_intersect_key($line,$group);
   $new_line=array_merge($group,$keys);

   if (!empty($new_line) && $wpdb->update($table, $new_line, $where) === false) {
      return false;
   } else {
      return $group_id;
   }
}

function eme_add_update_person($person_id=0) {
   $person=array();
   if (isset($_POST['lastname'])) $person['lastname'] = eme_strip_tags($_POST['lastname']);
   if (isset($_POST['firstname'])) $person['firstname'] = eme_strip_tags($_POST['firstname']);
   if (isset($_POST['email'])) $person['email'] = eme_strip_tags($_POST['email']);
   if (isset($_POST['address1'])) $person['address1'] = eme_strip_tags($_POST['address1']);
   if (isset($_POST['address2'])) $person['address2'] = eme_strip_tags($_POST['address2']);
   if (isset($_POST['city'])) $person['city'] = eme_strip_tags($_POST['city']);
   if (isset($_POST['state'])) $person['state'] = eme_strip_tags($_POST['state']);
   if (isset($_POST['zip'])) $person['zip'] = eme_strip_tags($_POST['zip']);
   if (isset($_POST['country'])) $person['country'] = eme_strip_tags($_POST['country']);
   if (isset($_POST['phone'])) $person['phone'] = eme_strip_tags($_POST['phone']);
   if (isset($_POST['massmail']))
	   $person['massmail'] = intval($_POST['massmail']);
   else
	   $person['massmail'] = 0;
   if (isset($_POST['groups']))
	   $groups = $_POST['groups'];
   else
	   $groups=array();
   if (isset($_POST['properties'])) $properties = eme_strip_tags($_POST['properties']);

   if ($person_id) {
	   $res=eme_db_update_person($person_id,$person);
	   if ($res) {
		   eme_update_persongroups($person_id,$groups);
		   eme_people_answers($person_id);
		   return __('Person updated','events-made-easy');
	   } else {
		   return __('Problem dectected while updating person','events-made-easy');
	   }
   } else {
	   // check existing
	   $t_person=eme_get_person_by_name_and_email($person['lastname'],$person['firstname'],$person['email']);
	   if ($t_person) {
		   $person_id = $t_person['person_id'];
		   $res=eme_db_update_person($person_id,$person);
		   if ($res) {
			   eme_update_persongroups($person_id,$groups);
			   eme_people_answers($person_id);
			   return __('Existing person updated','events-made-easy');
		   } else {
			   return __('Problem dectected while updating person','events-made-easy');
		   }
	   } else {
		   $person_id=eme_db_insert_person($person);
		   if ($person_id) {
			   eme_update_persongroups($person_id,$groups);
			   eme_people_answers($person_id);
			   return __('Person added','events-made-easy');
		   } else {
			   return __('Problem dectected while adding person','events-made-easy');
		   }
	   }
   }
}

function eme_add_update_group($group_id=0) {
   $group=array();
   if (isset($_POST['name'])) $group['name'] = eme_strip_tags($_POST['name']);
   if (isset($_POST['description'])) $group['description'] = eme_strip_tags($_POST['description']);
   if ($group_id) {
	   $res=eme_db_update_group($group_id,$group);
	   if ($res) {
		   if (isset($_POST['persons']))
			   $persons = $_POST['persons'];
		   else
			   $persons = array();
		   eme_update_grouppersons($group_id,$persons);
		   return __('Group updated','events-made-easy');
	   } else {
		   return __('Problem dectected while updating group','events-made-easy');
	   }
   } else {
	   $group_id=eme_db_insert_group($group);
	   if ($group_id) {
		   if (isset($_POST['persons']))
			   $persons = $_POST['persons'];
		   else
			   $persons = array();
		   eme_update_grouppersons($group_id,$persons);
		   return __('Group added','events-made-easy');
	   } else {
		   return __('Problem dectected while adding group','events-made-easy');
	   }
   }
}

function eme_person_from_rsvp($person_id,$lastname='', $firstname='', $email='', $wp_id=0) {
   $person = array();
   if (isset($_POST['address1'])) $person['address1'] = eme_strip_tags($_POST['address1']);
   if (isset($_POST['address2'])) $person['address2'] = eme_strip_tags($_POST['address2']);
   if (isset($_POST['city'])) $person['city'] = eme_strip_tags($_POST['city']);
   if (isset($_POST['state'])) $person['state'] = eme_strip_tags($_POST['state']);
   if (isset($_POST['zip'])) $person['zip'] = eme_strip_tags($_POST['zip']);
   if (isset($_POST['country'])) $person['country'] = eme_strip_tags($_POST['country']);
   if (isset($_POST['phone'])) $person['phone'] = eme_strip_tags($_POST['phone']);

   if (!$person_id) {
	   $person['lastname'] = eme_strip_tags($lastname);
	   $person['firstname'] = eme_strip_tags($firstname);
	   $person['email'] = eme_strip_tags($email);
	   $person['wp_id'] = intval($wp_id);
	   $person['lang'] = eme_detect_lang();
	   if (isset($_POST['massmail']))
		   $person['massmail'] = intval($_POST['massmail']);
	   else
		   $person['massmail'] = get_option('eme_people_massmail');
	   $person_id = eme_db_insert_person($person);
	   if ($person_id)
		   return eme_get_person($person_id);
	   else
		   return false;
   } else {
	   // take into account that $person can be empty too
	   if (isset($_POST['massmail']))
		   $person['massmail'] = intval($_POST['massmail']);
	   $res = eme_db_update_person($person_id,$person);
	   if ($res)
		   return eme_get_person($person_id);
	   else
		   return false;
   }
}

function eme_user_profile($user) {
   // only show future bookings
   $future=1;
   // define a simple template
   $template="#_STARTDATE #_STARTTIME: #_EVENTNAME (#_RESPSPACES ".__('places','events-made-easy')."). #_CANCEL_LINK<br />";
   ?>
   <h3><?php _e('Events Made Easy settings', 'events-made-easy')?></h3>
   <table class='form-table'>
      <tr>
         <th><label for="eme_phone"><?php _e('Phone number','events-made-easy');?></label></th>
         <td><input type="text" name="eme_phone" id="eme_phone" value="<?php echo esc_attr( get_user_meta($user->ID, 'eme_phone', true) ); ?>" class="regular-text" /> <br />
         <?php _e('The phone number used by Events Made Easy when the user is indicated as the contact person for an event.','events-made-easy');?></td>
      </tr>
      <tr>
         <th><label for="eme_bookings"><?php _e('Future bookings made','events-made-easy');?></label></th>
	 <td><?php echo eme_get_bookings_list_for_wp_id($user->ID,$future,$template); ?>
      </tr>
   </table>
   <?php
}

function eme_update_user_profile($wp_user_ID) {
   if(isset($_POST['eme_phone'])) {
      update_user_meta($wp_user_ID,'eme_phone', $_POST['eme_phone']);
   }
}

function eme_get_indexed_users() {
   global $wpdb;
   $sql = "SELECT display_name, ID FROM $wpdb->users";
   $users = $wpdb->get_results($sql, ARRAY_A);
   $indexed_users = array();
   foreach($users as $user) 
      $indexed_users[$user['ID']] = $user['display_name'];
   return $indexed_users;
}

function eme_get_wp_users($search) {
	$meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => 'first_name',
				'value'   => $search,
				'compare' => 'LIKE'
			     ),
			array(
				'key'     => 'last_name',
				'value'   => $search,
				'compare' => 'LIKE'
			     )
			);
	$args = array(
			'meta_query'   =>$meta_query,
			'orderby'      => 'ID',
			'order'        => 'ASC',
			'count_total'  => false,
			'fields'       => array('ID'),
		     );
	$users = get_users($args);
	return $users;
}

function eme_unsub_form() {
   $target=eme_get_events_page();
?>
   <form id='eme_unsub_form' method='post' action='<?php echo $target; ?>'>
   <p><?php _e('If you want to unsubscribe from future mailings, please do so by entering your email here.', 'events-made-easy'); ?> </p>
   <input type="email" name="eme_unsub_email" value="" />
   <input type="submit" value="<?php _e ( 'Unsubscribe','events-made-easy'); ?>" name="doaction" id="doaction" class="button-primary action" />
   </form>
<?php
}

function eme_unsub_send_mail($email) {
	if (eme_count_persons_by_email($email)>0) {
		$contact = eme_get_contact(0);
		$contact_email = $contact->user_email;
		$contact_name = $contact->display_name;
		$unsub_link=eme_unsub_confirm_url($email);
		$unsub_subject=get_option('eme_unsub_subject');
		$unsub_body=eme_translate(get_option('eme_unsub_body'));
		$unsub_body=str_replace('#_UNSUB_CONFIRM_URL', $unsub_link ,$unsub_body );
		return eme_send_mail($unsub_subject,$unsub_body, $email, '', $contact_email, $contact_name);
	}
}

function eme_unsub_do($email) {
	global $wpdb;
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	if (eme_count_persons_by_email($email)>0) {
		$where = array();
		$where['email'] = $email;
		$fields = array();
		$fields['massmail'] = 0;
		$wpdb->update($people_table, $fields, $where);
	}
}

function eme_get_person_answers_all($person_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $answers_table WHERE person_id=%d",$person_id);
   return $wpdb->get_results($sql, ARRAY_A);
}
function eme_get_dyndata_person_answers($person_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $formfield_table_name = $wpdb->prefix.FORMFIELDS_TBNAME;
   $sql = $wpdb->prepare("SELECT a.*, b.field_name FROM $answers_table a, $formfield_table_name b WHERE a.person_id=%d AND b.field_id=a.field_id",$person_id);
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_delete_person_groups($person_ids) {
   global $wpdb;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;
   $sql = "DELETE FROM $usergroups_table WHERE person_id IN ($person_ids)";
   $wpdb->query($sql);
}

function eme_delete_person_answers($person_ids) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = "DELETE FROM $answers_table WHERE person_id IN ($person_ids) AND member_id=0";
   $wpdb->query($sql);
}

function eme_delete_person_memberships($person_ids) {
   global $wpdb;
   $members_table = $wpdb->prefix.MEMBERS_TBNAME;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;
   $sql = "DELETE FROM $answers_table WHERE member_id IN (SELECT member_id FROM $members_table WHERE person_id IN ($person_ids))";
   $wpdb->query($sql);
   $sql = "DELETE FROM $members_table WHERE person_id IN ($person_ids)";
   $wpdb->query($sql);
}

function eme_people_answers($person_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;

   $all_answers=array();
   if ($person_id>0) {
	   $all_answers=eme_get_person_answers_all($person_id);
   }

   $answer_ids_seen=array();
   if (isset($_POST['dynamic_personfields'][$person_id])) {
           foreach($_POST['dynamic_personfields'][$person_id] as $key =>$value) {
                   if (preg_match('/^FIELD(\d+)$/', $key, $matches)) {
                           $field_id = intval($matches[1]);
                           $formfield = eme_get_formfield($field_id);
                           if ($formfield) {
                                   // for multivalue fields like checkbox, the value is in fact an array
                                   // to store it, we make it a simple "multi" string using eme_convert_array2multi, so later on when we need to parse the values
                                   // (when editing a booking), we can re-convert it to an array with eme_convert_multi2array (see eme_formfields.php)
                                   if (is_array($value)) $value=eme_convert_array2multi($value);
                                   if ($formfield['field_type']=='textarea')
                                           $value=eme_sanitize_textarea($value);
                                   else
                                           $value=eme_sanitize_text_field($value);
				   $answer_id=eme_return_answerid($all_answers,0,$person_id,0,$field_id);
				   if ($answer_id) {
					   $sql = $wpdb->prepare("UPDATE $answers_table SET answer=%s WHERE answer_id=%d",$value,$answer_id);
					   $answer_ids_seen[]=$answer_id;
				   } else {
					   $sql = $wpdb->prepare("INSERT INTO $answers_table (person_id,field_id,answer) VALUES (%d,%d,%s)",$person_id,$field_id,$value);
				   }
				   $wpdb->query($sql);
                           }
                   }
           }
   }

   // delete old answer_ids
   foreach ($all_answers as $answer) {
	   if (!in_array($answer['answer_id'],$answer_ids_seen) && $person_id>0) {
		   // the where person_id=%d is actually not needed, answer_id is unique, but we add it as a precaution
		   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE person_id = %d and answer_id=%d",$person_id,$answer['answer_id']);
		   $wpdb->query($sql);
	   }
   }
}

function eme_people_autocomplete_ajax($no_wp_die=0) {
   $return = array();
   $q = '';
   if (isset($_REQUEST['lastname']))
      $q = strtolower($_REQUEST['lastname']);
   elseif (isset($_REQUEST['q']))
      $q = strtolower($_REQUEST['q']);

   header("Content-type: application/json; charset=utf-8");
   if (empty($q)) {
      echo json_encode($return);
      if (!$no_wp_die)
         wp_die();
      return;
   }

   $search_tables=get_option('eme_autocomplete_sources');
   if (isset($_REQUEST['eme_searchlimit']) && $_REQUEST['eme_searchlimit']=="people") {
      $search_tables="people";
   }
   if ($search_tables=='people' || $search_tables=='both') {
	   $search="(lastname LIKE '%".esc_sql($q)."%' OR firstname LIKE '%".esc_sql($q)."%')";
	   $persons = eme_get_persons('',$search);
	   foreach($persons as $item) {
		   $record = array();
		   $record['lastname']  = esc_html($item['lastname']); 
		   $record['firstname'] = esc_html($item['firstname']); 
		   $record['address1']  = esc_html($item['address1']); 
		   $record['address2']  = esc_html($item['address2']); 
		   $record['city']      = esc_html($item['city']); 
		   $record['state']     = esc_html($item['state']); 
		   $record['zip']       = esc_html($item['zip']); 
		   $record['country']   = esc_html($item['country']); 
		   $record['email']     = esc_html($item['email']);
		   $record['phone']     = esc_html($item['phone']); 
		   $record['person_id'] = intval($item['person_id']); 
		   $record['wp_id']     = intval($item['wp_id']); 
		   $record['massmail']  = intval($item['massmail']); 
		   $return[]  = $record;
	   }
   }
   if ($search_tables=='wp_users' || $search_tables=='both') {
	   $persons = eme_get_wp_users($q);
	   foreach($persons as $item) {
		   $record = array();
		   $user_info = get_userdata($item->ID);
		   $record['lastname']  = esc_html($user_info->user_lastname);
		   if (empty($record['lastname']))
			   $record['lastname']=esc_html($user_info->display_name);
		   $record['firstname'] = esc_html($user_info->user_firstname);
		   $record['email']     = esc_html($user_info->user_email);
		   $record['address1']  = ''; 
		   $record['address2']  = ''; 
		   $record['city']      = ''; 
		   $record['state']     = ''; 
		   $record['zip']       = ''; 
		   $record['country']   = ''; 
		   $record['phone']     = ''; 
		   $record['wp_id']     = intval($item->ID); 
		   $record['massmail']  = 1;
		   $return[]  = $record;
	   }
   }

   echo json_encode($return);
   if (!$no_wp_die)
      wp_die();
}

add_action( 'wp_ajax_eme_autocomplete_people', 'eme_people_autocomplete_ajax' );
add_action( 'wp_ajax_eme_people_select2', 'eme_ajax_people_select2' );
add_action( 'wp_ajax_eme_people_list', 'eme_ajax_people_list' );
add_action( 'wp_ajax_eme_groups_list', 'eme_ajax_groups_list' );
add_action( 'wp_ajax_eme_manage_people', 'eme_ajax_manage_people' );
add_action( 'wp_ajax_eme_manage_groups', 'eme_ajax_manage_groups' );

function eme_ajax_people_list() {
   global $wpdb;
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME;

   $person_id = isset($_REQUEST['person_id']) ? intval($_REQUEST['person_id']) : 0;
   $search_name = isset($_REQUEST['search_name']) ? esc_sql($_REQUEST['search_name']) : '';
   $search_group = isset($_REQUEST['search_group']) ? intval($_REQUEST['search_group']) : 0;
   $where ='';
   $where_arr = array();
   if(!empty($search_name)) {
      $where_arr[] = "(lastname like '%".$search_name."%' OR firstname like '%".$search_name."%' OR email like '%".$search_name."%')";
   }
   if ($person_id) {
      $where_arr[] = "person_id = $person_id";
   }
   if ($search_group) {
      $where_arr[] = "group_id = $search_group";
   }
   if ($where_arr)
      $where = "WHERE ".implode(" AND ",$where_arr);

   if (isset($_REQUEST['search_customfieldids']) && !empty($_REQUEST['search_customfieldids'])) {
           $field_ids=join(',',$_REQUEST['search_customfieldids']);
           $formfields = eme_get_formfields($field_ids);
   } else {
           $field_ids = '';
           $formfields = array();
   }

   $jTableResult = array();
   $start=intval($_REQUEST["jtStartIndex"]);
   $pagesize=intval($_REQUEST["jtPageSize"]);
   $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
   if ($search_group) {
	   $usergroup_join="LEFT JOIN $usergroups_table as ugroups ON people.person_id=ugroups.person_id";
   } else {
	   $usergroup_join="";
   }
   if (empty($formfields)) {
	   $sql1 = "SELECT COUNT(*) FROM $people_table as people $usergroup_join
		   $where";
	   $sql = "SELECT people.* FROM $people_table as people $usergroup_join
                   $where $sorting LIMIT $start,$pagesize";
   } else {
	   $group_concat_sql="";
	   foreach ($formfields as $formfield) {
		   $field_id=$formfield['field_id'];
		   $group_concat_sql.="GROUP_CONCAT(CASE WHEN a.field_id = $field_id THEN a.answer END) AS 'answer_$field_id',";
	   }

	   $sql1 = "SELECT COUNT(*) FROM $people_table as people $usergroup_join
		   $where";
	   $sql = "SELECT ans.*,people.* FROM $people_table as people $usergroup_join
		   LEFT JOIN (SELECT $group_concat_sql
                         a.person_id
                         FROM $answers_table a
			 WHERE a.person_id>0 AND a.field_id IN ($field_ids)
			 GROUP BY a.person_id
                         ) ans
                   ON people.person_id=ans.person_id 
                   $where $sorting LIMIT $start,$pagesize";
   }

   $recordCount = $wpdb->get_var($sql1);
   $rows=$wpdb->get_results($sql,ARRAY_A);
   $records=array();
   foreach ($rows as $item) {
         $record = array();
         $record['people.person_id']= $item['person_id'];
         $record['people.lastname'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-people&amp;eme_admin_action=edit_person&amp;person_id=".$item['person_id']),'eme_people','eme_admin_nonce')."' title='".__('Edit person','events-made-easy')."'>".eme_esc_html($item['lastname'])."</a>";
         $record['people.firstname']= "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-people&amp;eme_admin_action=edit_person&amp;person_id=".$item['person_id']),'eme_people','eme_admin_nonce')."' title='".__('Edit person','events-made-easy')."'>".eme_esc_html($item['firstname'])."</a>";
	 $record['people.phone']    = eme_esc_html($item['phone']);
	 $record['people.email']    = eme_esc_html($item['email']);
	 $record['people.address1'] = eme_esc_html($item['address1']);
         $record['people.address2'] = eme_esc_html($item['address2']);
         $record['people.city']     = eme_esc_html($item['city']);
         $record['people.state']    = eme_esc_html($item['state']);
         $record['people.zip']      = eme_esc_html($item['zip']);
         $record['people.country']  = eme_esc_html($item['country']);
	 $record['people.massmail'] = eme_esc_html($item['massmail']);
         if (!empty($formfields)) {
                 foreach ($formfields as $formfield) {
                     foreach ($item as $key=>$val) {
			 // the beginning of the key ('answer_') comes from the group-concat statement above
                         if ($key=='answer_'.$formfield['field_id'] && $val!='') {
				 $tmp_answer=eme_convert_answer2tag($val,$formfield);
				 // the 'FIELD_' value is used by the container-js
                                 $record['FIELD_'.$formfield['field_id']]=$tmp_answer;
			 }
                     }
                 }
         }
         $records[]  = $record;
   }
   $jTableResult['Result'] = "OK";
   $jTableResult['TotalRecordCount'] = $recordCount;
   $jTableResult['Records'] = $records;
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_groups_list() {
   global $wpdb;
   $table = $wpdb->prefix.GROUPS_TBNAME;
   $usergroups_table = $wpdb->prefix.USERGROUPS_TBNAME;

   $jTableResult = array();
   $sql = "SELECT COUNT(*) FROM $table";
   $recordCount = $wpdb->get_var($sql);

   $sql = "SELECT group_id,COUNT(*) as groupcount FROM $usergroups_table GROUP BY group_id";
   $res=$wpdb->get_results($sql,ARRAY_A);
   $groupcount=array();
   foreach ($res as $val) {
	  $groupcount[$val['group_id']]=$val['groupcount'];
   } 

   $start=intval($_REQUEST["jtStartIndex"]);
   $pagesize=intval($_REQUEST["jtPageSize"]);
   $sorting = (isset($_REQUEST['jtSorting'])) ? 'ORDER BY '.esc_sql($_REQUEST['jtSorting']) : '';
   $sql="SELECT * FROM $table $sorting LIMIT $start,$pagesize";
   $rows=$wpdb->get_results($sql,ARRAY_A);
   $records=array();
   foreach ($rows as $item) {
         $record = array();
         $record['group_id']= $item['group_id'];
         $record['name'] = "<a href='".wp_nonce_url(admin_url("admin.php?page=eme-groups&amp;eme_admin_action=edit_group&amp;group_id=".$item['group_id']),'eme_people','eme_admin_nonce')."' title='".__('Edit group','events-made-easy')."'>".eme_esc_html($item['name'])."</a>";
	 $record['description'] = eme_esc_html($item['description']);
	 $record['groupcount'] = isset($groupcount[$item['group_id']]) ? $groupcount[$item['group_id']] : 0;
         $records[]  = $record;
   }
   $jTableResult['Result'] = "OK";
   $jTableResult['TotalRecordCount'] = $recordCount;
   $jTableResult['Records'] = $records;
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_people_select2() {
   global $wpdb;

   check_ajax_referer('eme_people','eme_admin_nonce');

   $table = $wpdb->prefix.PEOPLE_TBNAME;

   $jTableResult = array();
   $q = isset($_REQUEST['q']) ? strtolower($_REQUEST['q']) : '';
   if (!empty($q)) {
	   $where = "(lastname LIKE '%".esc_sql($q)."%' OR firstname LIKE '%".esc_sql($q)."%')";
   } else {
	   $where = '(1=1)';
   }
   $pagesize=intval($_REQUEST["pagesize"]);
   $start= isset($_REQUEST["page"]) ? intval($_REQUEST["page"])*$pagesize : 0;
   $count_sql = "SELECT COUNT(*) FROM $table WHERE $where";
   $recordCount = $wpdb->get_var($count_sql);
   $search="$where ORDER BY lastname, firstname LIMIT $start,$pagesize";

   $records=array();
   $persons = eme_get_persons('',$search);
   foreach ($persons as $person) {
         $record = array();
         $record['id']= $person['person_id'];
	 // no eme_esc_html here, select2 does it own escaping upon arrival
	 $record['text'] = $person['lastname'].' '.$person['firstname'].' ('.$person['email'].')';
         $records[]  = $record;
   }
   $jTableResult['TotalRecordCount'] = $recordCount;
   $jTableResult['Records'] = $records;
   print json_encode($jTableResult);
   wp_die();
}

function eme_ajax_manage_people() {
   check_ajax_referer('eme_people','eme_admin_nonce');
   if (isset($_POST['do_action'])) {
      $do_action=eme_sanitize_request($_POST['do_action']);
      $pdf_template=(isset($_POST['pdf_template'])) ? intval($_POST['pdf_template']) : 0;
      $ids=$_POST['person_id'];
      $ids_arr=explode(',',$ids);
      if (!eme_array_integers($ids_arr) || !current_user_can( get_option('eme_cap_people'))) {
          $jTableResult['Result'] = "Error";
          $jTableResult['Message'] = __('Access denied!','events-made-easy');
	  wp_die();
      }

      switch ($do_action) {
         case 'deletePeople':
              if (!empty($_POST['chooseperson']) && !empty($_POST['transferto_id'])) {
                  $to_person_id=intval($_POST['transferto_id']);
                  eme_transfer_person_bookings($ids,$to_person_id);
              } else {
                  eme_delete_person_bookings($ids);
              }
	      eme_delete_person_answers($ids);
	      eme_delete_person_memberships($ids);
	      eme_delete_person_groups($ids);
              eme_ajax_record_delete(PEOPLE_TBNAME, 'eme_cap_people', 'person_id');
              break;
         case 'pdf':
              $template=eme_get_template($pdf_template);
              eme_ajax_generate_people_pdf($ids_arr,$template);
              break;
      }
   }
   wp_die();
}
function eme_ajax_manage_groups() {
   $jTableResult = array();
   check_ajax_referer('eme_people','eme_admin_nonce');
   if (isset($_REQUEST['do_action'])) {
     $do_action=eme_sanitize_request($_REQUEST['do_action']);
     switch ($do_action) {
         case 'deleteGroups':
              $ids_arr=explode(',',$_POST['group_id']);
              if (eme_array_integers($ids_arr) && current_user_can( get_option('eme_cap_people'))) {
                 foreach ($ids_arr as $group_id) {
		    eme_delete_group($group_id);
                 }
	         $jTableResult['Result'] = "OK";
              } else {
		 $jTableResult['Result'] = "Error";
		 $jTableResult['Message'] = __('Access denied!','events-made-easy');
              }
	      print json_encode($jTableResult);
	      wp_die();
              break;
      }
   }
   wp_die();
}

function eme_ajax_generate_people_pdf($ids_arr,$template) {
        require_once("dompdf/autoload.inc.php");
        // instantiate and use the dompdf class
        $dompdf = new Dompdf\Dompdf();
        $margin_info = "margin: ".$template['properties']['pdf_margins'].";";
        $orientation = $template['properties']['pdf_orientation'];
        $pagesize = $template['properties']['pdf_size'];
        if ($pagesize == "custom" )
                $pagesize = array(0,0,$template['properties']['pdf_width'],$template['properties']['pdf_height']);

        $dompdf->setPaper($pagesize, $orientation);
        $html="
<html>
<head>
<style>
    @page {
        $margin_info;
    }
    div.page-break {
        page-break-before: always;
    }
</style>
</head>

<body>
";
        $total = count($ids_arr);
        $i=1;
        foreach ($ids_arr as $person_id) {
                $person=eme_get_person($person_id);
                $html.=eme_replace_people_placeholders($template['format'],$person);
                if ($i < $total) {
                        // dompdf uses a style to detect forced page breaks
                        $html.='<div class="page-break"></div>';
                        $i++;
                }
        }
        $html .= '
</body>
</html>';

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream();
}


?>
