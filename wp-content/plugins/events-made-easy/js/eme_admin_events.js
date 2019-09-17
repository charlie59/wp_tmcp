function updateIntervalDescriptor () { 
   jQuery(".interval-desc").hide();
   var number = "-plural";
   if (jQuery('input#recurrence-interval').val() == 1 || jQuery('input#recurrence-interval').val() == "") {
      number = "-singular";
   }
   var descriptor = "span#interval-"+jQuery("select#recurrence-frequency").val()+number;
   jQuery(descriptor).show();
}

function updateIntervalSelectors () {
   jQuery('span.alternate-selector').hide();
   jQuery('span#'+ jQuery('select#recurrence-frequency').val() + "-selector").show();
   //jQuery('p.recurrence-tip').hide();
   //jQuery('p#'+ jQuery(this).val() + "-tip").show();
}

function updateShowHideRecurrence () {
   if(jQuery('input#event-recurrence').attr("checked")) {
      jQuery("#event_recurrence_pattern").fadeIn();
      jQuery("span#event-date-recursive-explanation").show();
      jQuery("div#div_recurrence_date").show();
   } else {
      jQuery("#event_recurrence_pattern").hide();
      jQuery("span#event-date-recursive-explanation").hide();
      jQuery("div#div_recurrence_date").hide();
   }
}

function updateShowHideRecurrenceSpecificDays () {
   if (jQuery('select#recurrence-frequency').val() == "specific") {
      jQuery("div#recurrence-intervals").hide();
      jQuery("input#localized-rec-end-date").hide();
      jQuery("p#recurrence-dates-explanation").hide();
      jQuery("span#recurrence-dates-explanation-specificdates").show();
      jQuery("#localized-rec-start-date").datepick('option','multiSelect',999);
   } else {
      jQuery("div#recurrence-intervals").show();
      jQuery("input#localized-rec-end-date").show();
      jQuery("p#recurrence-dates-explanation").show();
      jQuery("span#recurrence-dates-explanation-specificdates").hide();
      jQuery("#localized-rec-start-date").datepick('option','multiSelect',0);
   }
}

function updateShowHideRsvp () {
   if (jQuery('input#rsvp-checkbox').attr("checked")) {
      jQuery("div#div_event_rsvp").fadeIn();
      jQuery("div#div_dyndata").fadeIn();
      jQuery("#event-tabs").tabs("enable","#tab-mailformats");
      jQuery("div#div_event_contactperson_email_body").fadeIn();
      jQuery("div#div_event_registration_recorded_ok_html").fadeIn();
      jQuery("div#div_event_respondent_email_body").fadeIn();
      jQuery("div#div_event_registration_pending_email_body").fadeIn();
      jQuery("div#div_event_registration_updated_email_body").fadeIn();
      jQuery("div#div_event_registration_form_format").fadeIn();
      jQuery("div#div_event_cancel_form_format").fadeIn();
      jQuery("div#div_event_registration_cancelled_email_body").fadeIn();
      jQuery("div#div_event_registration_denied_email_body").fadeIn();
   } else {
      jQuery("div#div_event_rsvp").fadeOut();
      jQuery("div#div_dyndata").fadeOut();
      jQuery("#event-tabs").tabs("disable","#tab-mailformats");
      jQuery("div#div_event_contactperson_email_body").fadeOut();
      jQuery("div#div_event_registration_recorded_ok_html").fadeOut();
      jQuery("div#div_event_respondent_email_body").fadeOut();
      jQuery("div#div_event_registration_pending_email_body").fadeOut();
      jQuery("div#div_event_registration_updated_email_body").fadeOut();
      jQuery("div#div_event_registration_form_format").fadeOut();
      jQuery("div#div_event_cancel_form_format").fadeOut();
      jQuery("div#div_event_registration_cancelled_email_body").fadeOut();
      jQuery("div#div_event_registration_denied_email_body").fadeOut();
   }
}

function updateShowHideTime () {
   if (jQuery('input#eme_prop_all_day').attr("checked")) {
      jQuery("div#time-selector").hide();
   } else {
      jQuery("div#time-selector").show();
   }
}

function eme_event_location_autocomplete () {
    // for autocomplete to work, the element needs to exist, otherwise JS errors occur
    // we check for that using length
    if (jQuery("input#location_name").length) {
          jQuery("input#location_name").autocomplete({
            source: function(request, response) {
                         jQuery.get(self.location.href,
                                   { q: request.term,
                                     eme_admin_action: 'autocomplete_locations'
                                   },
                                   function(data){
                                                response(jQuery.map(data, function(item) {
                                                      return {
                                                         id: item.id,
                                                         label: item.name,
                                                         name: htmlDecode(item.name),
                                                         address1: item.address1,
                                                         address2: item.address2,
                                                         city: item.city,
                                                         state: item.state,
                                                         zip: item.zip,
                                                         country: item.country,
                                                         latitude: item.latitude,
                                                         longitude: item.longitude,
                                                      };
                                                }));
                                    }, "json");
                    },
            select:function(evt, ui) {
                         // when a location is selected, populate related fields in this form
                         jQuery('input#location_id').val(ui.item.id).attr("readonly", true);
                         jQuery('input#location_name').val(ui.item.name).attr("readonly", true);
                         jQuery('input#location_address1').val(ui.item.address1).attr("readonly", true);
                         jQuery('input#location_address2').val(ui.item.address2).attr("readonly", true);
                         jQuery('input#location_city').val(ui.item.city).attr("readonly", true);
                         jQuery('input#location_state').val(ui.item.state).attr("readonly", true);
                         jQuery('input#location_zip').val(ui.item.zip).attr("readonly", true);
                         jQuery('input#location_country').val(ui.item.country).attr("readonly", true);
                         jQuery('input#location_latitude').val(ui.item.latitude).attr("readonly", true);
                         jQuery('input#location_longitude').val(ui.item.longitude).attr("readonly", true);
                         if(gmap_enabled) {
                            loadMapLatLong(ui.item.name, ui.item.address1, ui.item.address2, ui.item.city,ui.item.state,ui.item.zip,ui.item.country, ui.item.latitude, ui.item.longitude);
                         }
                         return false;
                   },
            minLength: 1
          }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            return jQuery( "<li></li>" )
            .append("<a><strong>"+htmlDecode(item.name)+'</strong><br /><small>'+htmlDecode(item.address1)+' - '+htmlDecode(item.city)+ '</small></a>')
            .appendTo( ul );
          };
	  jQuery("input#location_name").change(function(){
                if (jQuery("input#location_name").val()=='') {
                         jQuery('input#location_id').val('');
                         jQuery('input#location_name').val('').attr("readonly", false);
                         jQuery('input#location_address1').val('').attr("readonly", false);
                         jQuery('input#location_address2').val('').attr("readonly", false);
                         jQuery('input#location_city').val('').attr("readonly", false);
                         jQuery('input#location_state').val('').attr("readonly", false);
                         jQuery('input#location_zip').val('').attr("readonly", false);
                         jQuery('input#location_country').val('').attr("readonly", false);
                         jQuery('input#location_latitude').val('').attr("readonly", false);
                         jQuery('input#location_longitude').val('').attr("readonly", false);
                }
          });
    } else if (jQuery("input[name='location-select-name']").length) {
          jQuery('#location-select-id').change(function() {
            jQuery.getJSON(self.location.href,{eme_admin_action: 'autocomplete_locations',id: jQuery(this).val()}, function(item){
               jQuery("input[name='location-select-name']").val(item.name);
               jQuery("input[name='location-select-address1']").val(item.address1);
               jQuery("input[name='location-select-address2']").val(item.address2);
               jQuery("input[name='location-select-city']").val(item.city);
               jQuery("input[name='location-select-state']").val(item.state);
               jQuery("input[name='location-select-zip']").val(item.zip);
               jQuery("input[name='location-select-country']").val(item.country);
               jQuery("input[name='location-select-latitude']").val(item.latitude);
               jQuery("input[name='location-select-longitude']").val(item.longitude);
               if(gmap_enabled) {
                  loadMapLatLong(item.name,item.address1,item.address2,item.city,item.state,item.zip,item.country,item.latitude,item.longitude);
               }
            })
          });
    }
}

jQuery(document).ready( function($) {
   $("#div_recurrence_date").hide();

   $.datepick.setDefaults( $.datepick.regionalOptions[eme.translate_locale_code] );
   $.datepick.setDefaults({
      changeMonth: true,
      changeYear: true,
      altFormat: "yyyy-mm-dd",
      firstDay: parseInt(eme.translate_firstDayOfWeek)
   });
   $("#eme_localized_start_date").datepick({ altField: "#search_start_date" });

    $("#localized-start-date").datepick({
        altField: "#start-date-to-submit",
        onClose: function(dates) {
           var selected = '';
           for (var i = 0; i < dates.length; i++) {
              startDate = $.datepick.formatDate(dates[i]);
              startDate_formatted = $.datepick.formatDate('yyyymmdd',dates[i]);
              endDate_basic = $("#localized-end-date").datepick('getDate');
              endDate_formatted = $.datepick.formatDate('yyyymmdd',endDate_basic[0]);
              //$("#localized-end-date").datepick( "option", "minDate", startDate);
              if (endDate_formatted<startDate_formatted) {
                $("#localized-end-date").datepick( 'setDate', startDate);
              }
           }
        }
    });

    $("#localized-end-date").datepick({
        altField: "#end-date-to-submit",
        onClose: function(dates) {
           var selected = '';
           for (var i = 0; i < dates.length; i++) {
              endDate = $.datepick.formatDate(dates[i]);
              endDate_formatted = $.datepick.formatDate('yyyymmdd',dates[i]);
              startDate_basic = $("#localized-start-date").datepick('getDate');
              startDate_formatted = $.datepick.formatDate('yyyymmdd',startDate_basic[0]);
              //$("#localized-start-date").datepick( "option", "maxDate", endDate);
              if (startDate_formatted>endDate_formatted) {
                $("#localized-start-date").datepick( 'setDate', endDate);
              }
           }
        }
     });

    $("#localized-rec-start-date").datepick({
        altField: "#rec-start-date-to-submit",
        onClose: function(dates) {
           var selected = '';
           for (var i = 0; i < dates.length; i++) {
              startDate = $.datepick.formatDate(dates[i]);
              startDate_formatted = $.datepick.formatDate('yyyymmdd',dates[i]);
              endDate_basic = $("#localized-rec-end-date").datepick('getDate');
              endDate_formatted = $.datepick.formatDate('yyyymmdd',endDate_basic[0]);
              //$("#localized-end-date").datepick( "option", "minDate", startDate);
              if (endDate_formatted<startDate_formatted) {
                $("#localized-rec-end-date").datepick( 'setDate', startDate);
              }
           }
        }
    });

    $("#localized-rec-end-date").datepick({
        altField: "#rec-end-date-to-submit",
        onClose: function(dates) {
           var selected = '';
           for (var i = 0; i < dates.length; i++) {
              endDate = $.datepick.formatDate(dates[i]);
              endDate_formatted = $.datepick.formatDate('yyyymmdd',dates[i]);
              startDate_basic = $("#localized-rec-start-date").datepick('getDate');
              startDate_formatted = $.datepick.formatDate('yyyymmdd',startDate_basic[0]);
              //$("#localized-start-date").datepick( "option", "maxDate", endDate);
              if (startDate_formatted>endDate_formatted) {
                $("#localized-rec-start-date").datepick( 'setDate', endDate);
              }
           }
        }
     });

   $("#start-time").timeEntry({spinnerImage: '', show24Hours: show24Hours });
   $("#end-time").timeEntry({spinnerImage: '', show24Hours: show24Hours });

   // if any of event_single_event_format,event_page_title_format,event_contactperson_email_body,event_respondent_email_body,event_registration_pending_email_body, event_registration_form_format, event_registration_updated_email_body
   // is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur

   $('textarea#event_page_title_format').focus(function(){
      var tmp_value=eme_event_page_title_format();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   }); 
   $('textarea#event_page_title_format').blur(function(){
      var tmp_value=eme_event_page_title_format();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 
   $('textarea#event_single_event_format').focus(function(){
      var tmp_value=eme_single_event_format();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   }); 
   $('textarea#event_single_event_format').blur(function(){
      var tmp_value=eme_single_event_format();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 
   $('textarea#event_contactperson_email_body').focus(function(){
      var tmp_value=eme_contactperson_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_contactperson_email_body').blur(function(){
      var tmp_value=eme_contactperson_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 
   $('textarea#event_respondent_email_body').focus(function(){
      var tmp_value=eme_respondent_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   }); 
   $('textarea#event_respondent_email_body').blur(function(){
      var tmp_value=eme_respondent_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 
   $('textarea#event_registration_recorded_ok_html').focus(function(){
      var tmp_value=eme_registration_recorded_ok_html();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_recorded_ok_html').blur(function(){
      var tmp_value=eme_registration_recorded_ok_html();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_pending_email_body').focus(function(){
      var tmp_value=eme_registration_pending_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_pending_email_body').blur(function(){
      var tmp_value=eme_registration_pending_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_updated_email_body').focus(function(){
      var tmp_value=eme_registration_updated_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_updated_email_body').blur(function(){
      var tmp_value=eme_registration_updated_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_cancelled_email_body').focus(function(){
      var tmp_value=eme_registration_cancelled_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_cancelled_email_body').blur(function(){
      var tmp_value=eme_registration_cancelled_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_denied_email_body').focus(function(){
      var tmp_value=eme_registration_denied_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_denied_email_body').blur(function(){
      var tmp_value=eme_registration_denied_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_paid_email_body').focus(function(){
      var tmp_value=eme_registration_paid_email_body();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   });
   $('textarea#event_registration_paid_email_body').blur(function(){
      var tmp_value=eme_registration_paid_email_body();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   });
   $('textarea#event_registration_form_format').focus(function(){
      var tmp_value=eme_registration_form_format();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   }); 
   $('textarea#event_registration_form_format').blur(function(){
      var tmp_value=eme_registration_form_format();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 
   $('textarea#event_cancel_form_format').focus(function(){
      var tmp_value=eme_cancel_form_format();
      if ($(this).val() == '') {
         $(this).val(tmp_value);
      }
   }); 
   $('textarea#event_cancel_form_format').blur(function(){
      var tmp_value=eme_cancel_form_format();
      if ($(this).val() == tmp_value) {
         $(this).val('');
      }
   }); 

   // initially the div is not shown using display:none, so jquery has time to render it and then we call show()
   $("#event-tabs").tabs();
   $("#event-tabs").show();
   $( "#event-tabs" ).on( "tabsactivate", function( event, ui ) {
      // we call both functions to show the map, only 1 will work (either the select-based or the other) depending on the form shown
      if (ui.newPanel.attr('id') == "tab-locationdetails") {
         // We need to call it here, because otherwise the map initially doesn't render correctly due to hidden tab div etc ...
         eme_SelectdisplayAddress();
         eme_displayAddress(0);
         // also initialize the code for auto-complete of location info
         eme_event_location_autocomplete();
      }
   });

   updateIntervalDescriptor(); 
   updateIntervalSelectors();
   updateShowHideRecurrence();
   updateShowHideRsvp();
   updateShowHideRecurrenceSpecificDays();
   updateShowHideTime();
   $('input#event-recurrence').change(updateShowHideRecurrence);
   $('input#rsvp-checkbox').change(updateShowHideRsvp);
   $('input#eme_prop_all_day').change(updateShowHideTime);
   // recurrency elements
   $('input#recurrence-interval').keyup(updateIntervalDescriptor);
   $('select#recurrence-frequency').change(updateIntervalDescriptor);
   $('select#recurrence-frequency').change(updateIntervalSelectors);
   $('select#recurrence-frequency').change(updateShowHideRecurrenceSpecificDays);

   // users cannot submit the event form unless some fields are filled
   function validateEventForm() {
      var errors = "";
      var recurring = $("input[name=repeated_event]:checked").val();
      //requiredFields= new Array('event_name', 'localized_event_start_date', 'location_name','location_address','location_town');
      var requiredFields = ['event_name', 'localized_event_start_date'];
      var localizedRequiredFields = {'event_name':eme.translate_name,
                      'localized_event_start_date':eme.translate_date
                     };
      
      var missingFields = [];
      var i;
      for (i in requiredFields) {
         if ($("input[name=" + requiredFields[i]+ "]").val() == 0) {
            missingFields.push(localizedRequiredFields[requiredFields[i]]);
            $("input[name=" + requiredFields[i]+ "]").css('border','2px solid red');
         } else {
            $("input[name=" + requiredFields[i]+ "]").css('border','1px solid #DFDFDF');
         }
      }
   
      if (missingFields.length > 0) {
         errors = eme.translate_fields_missing + missingFields.join(", ") + ".\n";
      }
      if (recurring && $("input#localized-rec-end-date").val() == "" && $("select#recurrence-frequency").val() != "specific") {
         errors = errors + eme.translate_enddate_required; 
         $("input#localized-rec-end-date").css('border','2px solid red');
      } else if (recurring && $("input#localized-rec-start-date").val() == $("input#localized-rec-end-date").val()) {
         errors = errors + eme.translate_startenddate_identical; 
         $("input#localized-rec-end-date").css('border','2px solid red');
      } else {
         $("input#localized-rec-end-date").css('border','1px solid #DFDFDF');
      }
      if (errors != "") {
         alert(errors);
         return false;
      }
      return true;
   }
   $('#eventForm').bind("submit", validateEventForm);

   //Prepare jtable plugin
   $('#EventsTableContainer').jtable({
            title: eme.translate_events,
            paging: true,
            pageSizes: [10, 25, 50, 100],
            sorting: true,
            multiSorting: true,
            toolbarsearch: true,
            jqueryuiTheme: true,
            defaultSorting: 'name ASC',
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: true, //Enable this to only select using checkboxes
            toolbar: {
                items: [{
                        text: eme.translate_csv,
                        click: function () {
                                  jtable_csv('#EventsTableContainer');
                               }
                        },
                        {
                        text: eme.translate_print,
                        click: function () {
                                  $('#EventsTableContainer').printElement();
                               }
                        }
                        ]
            },
            actions: {
                listAction: ajaxurl+'?action=eme_events_list',
                deleteAction: ajaxurl+'?action=eme_manage_events&do_action=deleteEvents&eme_admin_nonce='+eme.translate_nonce,
            },
            fields: {
                event_id: {
                    key: true,
		    title: eme.translate_id,
                    visibility: 'hidden'
                },
                event_name: {
		    title: eme.translate_name,
                    visibility: 'fixed',
                },
                event_status: {
		    title: eme.translate_status,
                    width: '5%'
                },
                copy: {
		    title: eme.translate_copy,
                    sorting: false,
                    width: '2%',
                    listClass: 'eme-jtable-center'
                },
                rsvp: {
		    title: eme.translate_rsvp,
                    sorting: false,
                    width: '2%',
                    listClass: 'eme-jtable-center'
                },
                location_name: {
		    title: eme.translate_location
                },
                datetime: {
		    title: eme.translate_datetime,
                    width: '5%'
                },
                recinfo: {
		    title: eme.translate_recinfo,
                    sorting: false
                }
            }
        });

        // Load list from server, but only if the container is there
        if ($('#EventsTableContainer').length) {
           $('#EventsTableContainer').jtable('load', {
               scope: $('#scope').val(),
               status: $('#status').val(),
               category: $('#category').val(),
               search_name: $('#search_name').val(),
               search_start_date: $('#search_start_date').val()
           });
        }
 
        // Actions button
        $('#EventsActionsButton').button().click(function () {
           var selectedRows = $('#EventsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteEvents' || do_action=='deleteRecurrence') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
		 $('#EventsActionsButton').text(eme.translate_pleasewait);
	         var ids = [];
	         selectedRows.each(function () {
	           ids.push($(this).data('record')['event_id']);
	         });
    
	         var idsjoined = ids.join(); //will be such a string '2,5,7'
                 $.post(ajaxurl, {"event_id": idsjoined, "action": "eme_manage_events", "do_action": do_action, "eme_admin_nonce": eme.translate_nonce }, function() {
	            $('#EventsTableContainer').jtable('reload');
		    $('#EventsActionsButton').text(eme.translate_apply);
                 });
	      }
	   } else {
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });

        // Re-load records when user click 'load records' button.
        $('#EventsLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#EventsTableContainer').jtable('load', {
               scope: $('#scope').val(),
               status: $('#status').val(),
               category: $('#category').val(),
               search_name: $('#search_name').val(),
               search_start_date: $('#search_start_date').val()
           });
           // return false to make sure the real form doesn't submit
           return false;
        });

	// for the image 
	$('#eme_remove_old_image').click(function(e) {
		$('#event_image_url').val('');
		$('#event_image_id').val('');
		$('#eme_event_image_example' ).attr("src",'');
		$('#event_current_image' ).hide();
		$('#event_no_image' ).show();
		$('#eme_remove_old_image' ).hide();
		$('#event_image_button' ).show();
	});
	$('#event_image_button').click(function(e) {
		e.preventDefault();
		var custom_uploader = wp.media({
			title: eme.translate_selectfeaturedimg,
			button: {
				text: eme.translate_setfeaturedimg
			},
			// Tell the modal to show only images.
			library: {
				type: 'image'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
			.on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				$('#event_image_url').val(attachment.url);
				$('#event_image_id').val(attachment.id);
				$('#eme_event_image_example' ).attr("src",attachment.url);
				$('#event_current_image' ).show();
				$('#event_no_image' ).hide();
				$('#eme_remove_old_image' ).show();
				$('#event_image_button' ).hide();
			})
			.open();
	});
	if ($('#event_image_url').val() != '') {
		$('#event_no_image' ).hide();
		$('#eme_remove_old_image' ).show();
		$('#event_image_button' ).hide();
	} else {
		$('#event_no_image' ).show();
		$('#eme_remove_old_image' ).hide();
		$('#event_image_button' ).show();
	}
});
