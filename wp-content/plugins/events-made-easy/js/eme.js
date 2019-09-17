function htmlDecode(value){ 
   return jQuery('<div/>').html(value).text(); 
}

function loadCalendar(tableDiv, fullcalendar, htmltable, htmldiv, showlong_events, month, year, cat_chosen, author_chosen, contact_person_chosen, location_chosen, not_cat_chosen,template_chosen,holiday_chosen,weekdays,language) {
         if (fullcalendar === undefined) {
             fullcalendar = 0;
         }

         if (showlong_events === undefined) {
             showlong_events = 0;
         }
         fullcalendar = (typeof fullcalendar == 'undefined')? 0 : fullcalendar;
         showlong_events = (typeof showlong_events == 'undefined')? 0 : showlong_events;
         month = (typeof month == 'undefined')? 0 : month;
         year = (typeof year == 'undefined')? 0 : year;
         cat_chosen = (typeof cat_chosen == 'undefined')? '' : cat_chosen;
         not_cat_chosen = (typeof not_cat_chosen == 'undefined')? '' : not_cat_chosen;
         author_chosen = (typeof author_chosen == 'undefined')? '' : author_chosen;
         contact_person_chosen = (typeof contact_person_chosen == 'undefined')? '' : contact_person_chosen;
         location_chosen = (typeof location_chosen == 'undefined')? '' : location_chosen;
         template_chosen = (typeof template_chosen == 'undefined')? 0 : template_chosen;
         holiday_chosen = (typeof template_chosen == 'undefined')? 0 : holiday_chosen;
         weekdays = (typeof weekdays == 'undefined')? '' : weekdays;
         jQuery.post(self.location.href, {
            eme_ajaxCalendar: 'true',
            calmonth: parseInt(month,10),
            calyear: parseInt(year,10),
            full : fullcalendar,
            long_events: showlong_events,
            htmltable: htmltable,
            htmldiv: htmldiv,
            category: cat_chosen,
            notcategory: not_cat_chosen,
            author: author_chosen,
            contact_person: contact_person_chosen,
            location_id: location_chosen,
            template_id: template_chosen,
            holiday_id: holiday_chosen,
            weekdays: weekdays,
            lang: language,
         }, function(data){
            tableDiv.replaceWith(data);
         });
      }

jQuery(document).ready( function($) {

	function eme_delete_booking_json() {
		$('#booking-delete-form').find(':submit').hide();
		$("#loading_gif").show();
		var alldata = $('#booking-delete-form').serializeArray();
		alldata.push({name: 'eme_ajax_action', value: 'delete_bookings'});
		$.post(self.location.href, alldata, function(data){
			$('#booking-delete-form').find(':submit').show();
			$("#loading_gif").hide();
			if (data.result=='OK') {
				$('div#eme-rsvp-delmessage-ok').html(data.htmlmessage);
				$('div#eme-rsvp-delmessage-ok').show();
				$('div#eme-rsvp-delmessage-error').hide();
				$('div#div_booking-delete-form').hide();
			} else {
				$('div#eme-rsvp-delmessage-error').html(data.htmlmessage);
				$('div#eme-rsvp-delmessage-ok').hide();
				$('div#eme-rsvp-delmessage-error').show();
			}
		}, "json")
		.fail(function(xhr, textStatus, error){
			$('div#eme-rsvp-delmessage-error').html(emebasic.translate_error);
			$('div#eme-rsvp-delmessage-error').append(xhr.responseText+' : '+error);
			$('div#eme-rsvp-delmessage-ok').hide();
			$('div#eme-rsvp-delmessage-error').show();
		})
		.always(function(){
			// scroll to the message shown, with an added offset of half the screen height, so the message doesn't start at the high top of the screen
			$(document).scrollTop( $("#eme-rsvp-form").offset().top - $(window).height()/2 );  
			$('#eme-rsvp-form').find(':submit').show();
			$("#loading_gif").hide();
		})
	}

	function eme_add_member_json() {
		$("#loading_gif").show();
		$('#eme-member-form').find(':submit').hide();
		alldata = $('#eme-member-form').serializeArray();
		alldata.push({name: 'eme_ajax_action', value: 'add_member'});
		$.post(self.location.href, alldata, function(data){
			$('#eme-member-form').find(':submit').show();
			$("#loading_gif").hide();
			if (data.result=='OK') {
				$('div#eme-member-addmessage-ok').html(data.htmlmessage);
				$('div#eme-member-addmessage-ok').show();
				$('div#eme-member-addmessage-error').hide();
				$('div#div_eme-member-form').hide();
				if (typeof data.paymentform !== 'undefined') {
					$('div#div_eme-payment-form').html(data['paymentform']);
					$('div#div_eme-payment-form').show();
				}
			} else {
				$('div#eme-member-addmessage-error').html(data.htmlmessage);
				$('div#eme-member-addmessage-ok').hide();
				$('div#eme-member-addmessage-error').show();
			}
		}, "json")
		.fail(function(xhr, textStatus, error){
			$('div#eme-member-addmessage-error').html('An error has occurred');
			$('div#eme-member-addmessage-error').append(xhr.responseText+' : '+error);
			$('div#eme-member-addmessage-ok').hide();
			$('div#eme-member-addmessage-error').show();
		})
		.always(function(){
			// scroll to the message shown, with an added offset of half the screen height, so the message doesn't start at the high top of the screen
			$(document).scrollTop( $("#eme-member-form").offset().top - $(window).height()/2 );  
			$('#eme-member-form').find(':submit').show();
			$("#loading_gif").hide();
		})
	}

	function eme_add_booking_json() {
		$("#loading_gif").show();
		$('#eme-rsvp-form').find(':submit').hide();
		alldata = $('#eme-rsvp-form').serializeArray();
		alldata.push({name: 'eme_ajax_action', value: 'add_bookings'});
		$.post(self.location.href, alldata, function(data){
			if (data.result=='OK') {
				$('div#eme-rsvp-addmessage-ok').html(data.htmlmessage);
				$('div#eme-rsvp-addmessage-ok').show();
				$('div#eme-rsvp-addmessage-error').hide();
				if (data.keep_form==1) {
					// we are requested to show the form again, so let's just reset it to the initial state
					$('#eme-rsvp-form').trigger("reset");
					// and also reload all relevant dynamic fields
					var newbasicdata = $("#eme-rsvp-form :input").not(".dynamicfield").serializeArray();
					eme_dynamic_bookingdata_json(newbasicdata);
					if ($('#eme_captcha_img').length) {
						src=$("#eme_captcha_img").attr("src");
						// the booking is ok and the form needs to be presented again, so refresh the captcha
						// we need a new captcha, we take the src and add a timestamp to it, so the browser won't cache it
						// also: remove possible older timestamps, to be clean
						src=src.replace(/&ts=.*/,'');
						var timestamp = new Date().getTime();
						$("#eme_captcha_img").attr("src",src+'&ts='+timestamp);
					}
				} else {
					$('div#div_eme-rsvp-form').hide();
				}
				if (typeof data.paymentform !== 'undefined') {
					$('div#div_eme-payment-form').html(data['paymentform']);
					$('div#div_eme-payment-form').show();
				}
			} else {
				// delete possible older messages
				$('div#eme-rsvp-addmessage-error').html(data.htmlmessage);
				$('div#eme-rsvp-addmessage-ok').hide();
				$('div#eme-rsvp-addmessage-error').show();
			}
		}, "json")
		.fail(function(xhr, textStatus, error){
			$('div#eme-rsvp-addmessage-error').html(emebasic.translate_error);
			$('div#eme-rsvp-addmessage-error').append(xhr.responseText+' : '+error);
			$('div#eme-rsvp-addmessage-ok').hide();
			$('div#eme-rsvp-addmessage-error').show();
		})
		.always(function(){
			// scroll to the message shown, with an added offset of half the screen height, so the message doesn't start at the high top of the screen
			$(document).scrollTop( $("#eme-rsvp-form").offset().top - $(window).height()/2 );  
			$('#eme-rsvp-form').find(':submit').show();
			$("#loading_gif").hide();
		})
		;
	}

	function eme_dynamic_bookingprice_json(alldata) {
		// now calculate the price, but only do it if we have a "full" form
		if ($('span#eme_calc_bookingprice').length) {
			alldata.push({name: 'eme_override_eventAction', value: 'calc_bookingprice'});
			$('span#eme_calc_bookingprice').html('<img src="'+emebasic.translate_plugin_url+'images/spinner.gif">');
			$.post(self.location.href, alldata, function(data){
				$('span#eme_calc_bookingprice').html(data.total);
			}, "json")
			.fail(function(xhr, textStatus, error){
				$('span#eme_calc_bookingprice').html('Invalid reply');
			})
			;
		}
	}
	function eme_dynamic_bookingdata_json(alldata) {
		if ($('div#eme_dyndata').length) {
			$('div#eme_dyndata').html('<img src="'+emebasic.translate_plugin_url+'images/spinner.gif">');
			alldata.push({name: 'eme_override_eventAction', value: 'dynbookingdata'});
			$.post(self.location.href, alldata, function(data){
				$('div#eme_dyndata').html(data.result);
				// make sure to init select2 for dynamic added fields
				if ($('.eme_select2_class').length) {
					$('.eme_select2_class').select2({width: 'resolve'});
					// to make sure the placeholder shows after a hidden select2 is shown (bug workaround)
					$('.select2-search--inline, .select2-search__field').css('width', '100%');
				}
				// make sure to init the datapicker for dynamic added fields
				if ($('.eme_formfield_date').length) {
					$('.eme_formfield_date').datepicker({
						changeYear: true,
						yearRange: "-60:+10",
						showOn: 'focus',
						showButtonPanel: true,
						closeText: emebasic.translate_clear, // Text to show for "close" button
						onClose: function () {
							var event = arguments.callee.caller.caller.arguments[0];
							// If "Clear" gets clicked, then really clear it
							if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
								$(this).datepicker('setDate',null);
							}
						}
					});
					$.each($('.eme_formfield_date'), function() {
						if ($(this).is(".dynamicfield")) {
							// first make sure we're in the correct format for setDate to work as expected
							$(this).datepicker("option", "dateFormat", "yy-mm-dd" );
							if ($(this).data('date') == '0000-00-00') {
								$(this).datepicker('setDate',null);
							} else {
								$(this).datepicker('setDate', $(this).data('date'));
							}
							// now change the visual format to the desired format
							$(this).datepicker("option", "dateFormat", $(this).data('dateformat') );
							$(this).datepicker("option", "altField", $('input[name="'+$(this).data('altfield')+'"]') );
							$(this).datepicker("option", "altFormat", "yy-mm-dd" );
						}
					});
				}
				if ($('span#eme_calc_bookingprice').length) {
					$('span#eme_calc_bookingprice').html(data.total);
				}
			}, "json");
		} else {
			eme_dynamic_bookingprice_json(alldata);
		}
	}
	function eme_dynamic_memberprice_json(alldata) {
		// now calculate the price, but only do it if we have a "full" form
		if ($('span#eme_calc_memberprice').length) {
			$('span#eme_calc_memberprice').html('<img src="'+emebasic.translate_plugin_url+'images/spinner.gif">');
			alldata.push({name: 'eme_override_eventAction', value: 'calc_memberprice'});
			$.post(self.location.href, alldata, function(data){
				$('span#eme_calc_memberprice').html(data.total);
			}, "json");
		}
	}
	function eme_dynamic_memberdata_json(alldata) {
		if ($('div#eme_dyndata').length) {
			$('div#eme_dyndata').html('<img src="'+emebasic.translate_plugin_url+'images/spinner.gif">');
			alldata.push({name: 'eme_override_eventAction', value: 'dynmemberdata'});
			$.post(self.location.href, alldata, function(data){
				$('div#eme_dyndata').html(data.result);
				// make sure to init select2 for dynamic added fields
				if ($('.eme_select2_class').length) {
					$('.eme_select2_class').select2({width: 'resolve'});
					// to make sure the placeholder shows after a hidden select2 is shown (bug workaround)
					$('.select2-search--inline, .select2-search__field').css('width', '100%');
				}
				// make sure to init the datapicker for dynamic added fields
				if ($('.eme_formfield_date').length) {
					$('.eme_formfield_date').datepicker({
						changeYear: true,
						yearRange: "-60:+10",
						showOn: 'focus',
						showButtonPanel: true,
						closeText: emebasic.translate_clear, // Text to show for "close" button
						onClose: function () {
							var event = arguments.callee.caller.caller.arguments[0];
							// If "Clear" gets clicked, then really clear it
							if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
								$(this).datepicker('setDate',null);
							}
						}
					});
					$.each($('.eme_formfield_date'), function() {
						if ($(this).is(".dynamicfield")) {
							// first make sure we're in the correct format for setDate to work as expected
							$(this).datepicker("option", "dateFormat", "yy-mm-dd" );
							if ($(this).data('date') == '0000-00-00') {
								$(this).datepicker('setDate',null);
							} else {
								$(this).datepicker('setDate', $(this).data('date'));
							}
							// now change the visual format to the desired format
							$(this).datepicker("option", "dateFormat", $(this).data('dateformat') );
							$(this).datepicker("option", "altField", $('input[name="'+$(this).data('altfield')+'"]') );
							$(this).datepicker("option", "altFormat", "yy-mm-dd" );
						}
					});
				}
				if ($('span#eme_calc_bookingprice').length) {
					$('span#eme_calc_bookingprice').html(data.total);
				}
			}, "json");
		} else {
			eme_dynamic_memberprice_json(alldata);
		}
   }

	// using the below on-change syntax propagates the onchange from the form to all elements below, also those
	// dynamically added
	$('#booking-delete-form').on('submit', function(event) {
		event.preventDefault();
		eme_delete_booking_json();
	});

	// some basic rsvp and member form validation
	// normally required fields are handled by the browser, but not always (certainly not datepicker fields)
	$('.eme_submit_button').on('click', function(event) {
		var valid=true;
		$.each($('.eme_formfield_date'), function() {
			if ($(this).prop('required') && $(this).val() == '') {
				$(this).css("border", "2px solid red");
				$(document).scrollTop($(this).offset().top - $(window).height()/2 );
				return valid=false;
			}
		});
		if (!valid) {
			return false;
		}
	});
	$('#eme-rsvp-form').on('submit', function(event) {
		event.preventDefault();
		eme_add_booking_json();
	});
	$('#eme-member-form').on('submit', function(event) {
		event.preventDefault();
		eme_add_member_json();
	});

	// when doing form changes, we set a small delay to avoid calling the json function too many times
	var timer;
	var delay = 1000; // 1 seconds delay after last input
	if ($('#eme-rsvp-form').length) {
		// the on-syntax helps to propagate the event handler to dynamic created fields too
		// IE browsers don't react on on-input for select fields, so we define the on-change too
		// and check the type of formfield, so as not to trigger multiple events for a field
		$('#eme-rsvp-form').on('input', function(event) {
			var alldata = $(this).serializeArray();
			if ($(event.target).is("select")) {
				return;
			}
			if ($(event.target).is(".dynamicprice") && $(event.target).is(".nodynamicupdates")) {
				window.clearTimeout(timer);
				timer = window.setTimeout(function(){
					eme_dynamic_bookingprice_json(alldata);
				}, delay);
				return;
			}
			// for dynamic fields, we only consider a possible price change
			// but that is handled above already, so skipping the rest
			if ($(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			window.clearTimeout(timer);
			timer = window.setTimeout(function(){
				eme_dynamic_bookingdata_json(alldata);
			}, delay);
		});
		$('#eme-rsvp-form').on('change', function(event) {
			var alldata = $(this).serializeArray();
			if (!$(event.target).is("select")) {
				return;
			}
			if ($(event.target).is(".dynamicprice") && $(event.target).is(".nodynamicupdates")) {
				eme_dynamic_bookingprice_json(alldata);
				return;
			}
			// for dynamic fields, we only consider a possible price change
			// but that is handled above already, so skipping the rest
			if ($(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			eme_dynamic_bookingdata_json(alldata);
		});
		var alldata = $('#eme-rsvp-form').serializeArray();
		eme_dynamic_bookingdata_json(alldata);
	}
	if ($('#eme-rsvp-adminform').length) {
		// the on-syntax helps to propagate the event handler to dynamic created fields too
		// IE browsers don't react on on-input for select fields, so we define the on-change too
		// and check the type of formfield, so as not to trigger multiple events for a field
		$('#eme-rsvp-adminform').on('input', function(event) {
			var alldata = $(this).serializeArray();
			if ($(event.target).is("select")) {
				return;
			}
			if ($(event.target).is(".dynamicprice") && $(event.target).is(".nodynamicupdates")) {
				window.clearTimeout(timer);
				timer = window.setTimeout(function(){
					eme_dynamic_bookingprice_json(alldata);
				}, delay);
				return;
			}
			// for dynamic fields, we only consider a possible price change
			// but that is handled above already, so skipping the rest
			if ($(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			window.clearTimeout(timer);
			timer = window.setTimeout(function(){
				eme_dynamic_bookingdata_json(alldata);
			}, delay);
		});
		$('#eme-rsvp-adminform').on('change', function(event) {
			var alldata = $(this).serializeArray();
			if (!$(event.target).is("select")) {
				return;
			}
			if ($(event.target).is(".dynamicprice") && $(event.target).is(".nodynamicupdates")) {
				eme_dynamic_bookingprice_json(alldata);
				return;
			}
			// for dynamic fields, we only consider a possible price change
			// but that is handled above already, so skipping the rest
			if ($(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			eme_dynamic_bookingdata_json(alldata);
		});
		var alldata = $('#eme-rsvp-adminform').serializeArray();
		eme_dynamic_bookingdata_json(alldata);
	}

	if ($('#eme-member-form').length) {
		// the on-syntax helps to propagate the event handler to dynamic created fields too
		// IE browsers don't react on on-input for select fields, so we define the on-change too
		// and check the type of formfield, so as not to trigger multiple events for a field
		$('#eme-member-form').on('input', function(event) {
			var alldata = $(this).serializeArray();
			if ($(event.target).is("select") || $(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			window.clearTimeout(timer);
			timer = window.setTimeout(function(){
				eme_dynamic_memberdata_json(alldata);
			}, delay);
		});
		$('#eme-member-form').on('change', function(event) {
			var alldata = $(this).serializeArray();
			if (!$(event.target).is("select") || $(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			eme_dynamic_memberdata_json(alldata);
		});
		var alldata = $('#eme-member-form').serializeArray();
		eme_dynamic_memberdata_json(alldata);
	}
	if ($('#eme-member-adminform').length) {
		// the on-syntax helps to propagate the event handler to dynamic created fields too
		// IE browsers don't react on on-input for select fields, so we define the on-change too
		// and check the type of formfield, so as not to trigger multiple events for a field
		$('#eme-member-adminform').on('input', function(event) {
			if ($(event.target).is("select") || $(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates") || $(event.target).is(".clearable") ) {
				return;
			}
			var alldata = $(this).serializeArray();
			window.clearTimeout(timer);
			timer = window.setTimeout(function(){
				eme_dynamic_memberdata_json(alldata);
			}, delay);
		});
		$('#eme-member-adminform').on('change', function(event) {
			if (!$(event.target).is("select") || $(event.target).is(".dynamicfield") || $(event.target).is(".nodynamicupdates")) {
				return;
			}
			var alldata = $(this).serializeArray();
			eme_dynamic_memberdata_json(alldata);
		});
		var alldata = $('#eme-member-adminform').serializeArray();
		eme_dynamic_memberdata_json(alldata);
	}
        if ($('.eme_formfield_date').length) {
                $('.eme_formfield_date').datepicker({
                        changeYear: true,
                        yearRange: "-60:+10",
                        showOn: 'focus',
                        showButtonPanel: true,
                        closeText: emebasic.translate_clear, // Text to show for "close" button
                        onClose: function () {
                                var event = arguments.callee.caller.caller.arguments[0];
                                // If "Clear" gets clicked, then really clear it
                                if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
                                        $(this).datepicker('setDate',null);
                                }
                        }
		});
                $.each($('.eme_formfield_date'), function() {
                        // first make sure we're in the correct format for setDate to work as expected
                        $(this).datepicker("option", "altField", $('input[name="'+$(this).data('altfield')+'"]') );
                        $(this).datepicker("option", "altFormat", "yy-mm-dd" );
                        $(this).datepicker("option", "dateFormat", "yy-mm-dd" );
			if ($(this).data('date') == '0000-00-00') {
				$(this).datepicker('setDate',null);
			} else {
				$(this).datepicker('setDate', $(this).data('date'));
			}
                        // now change the visual format to the desired format
                        $(this).datepicker("option", "dateFormat", $(this).data('dateformat') );
                });
        }
        if ($('.eme_select2_class').length) {
		$('.eme_select2_class').select2({width: 'resolve'});
		// to make sure the placeholder shows after a hidden select2 is shown (bug workaround)
		$('.select2-search--inline, .select2-search__field').css('width', '100%');
        }
});
