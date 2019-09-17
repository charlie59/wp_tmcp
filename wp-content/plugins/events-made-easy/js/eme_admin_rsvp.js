jQuery(document).ready(function ($) { 

	function getQueryParams(qs) {
		qs = qs.split("+").join(" ");
		var params = {},
			tokens,
			re = /[?&]?([^=]+)=([^&]*)/g;

		while (tokens = re.exec(qs)) {
			params[decodeURIComponent(tokens[1])]
				= decodeURIComponent(tokens[2]);
		}
		return params;
	}
	var $_GET = getQueryParams(document.location.search);

	$.datepick.setDefaults( $.datepick.regionalOptions[eme.translate_locale_code] );
	$.datepick.setDefaults({
		changeMonth: true,
		changeYear: true,
		altFormat: "yyyy-mm-dd",
		firstDay: parseInt(eme.translate_firstDayOfWeek)
	});
	$("#eme_localized_start_date").datepick({ altField: "#search_start_date" });

        //Prepare jtable plugin
        $('#BookingsTableContainer').jtable({
            title: eme.translate_bookings,
            paging: true,
            sorting: true,
            multiSorting: true,
            toolbarsearch: false,
            toolbarreset: false,
            jqueryuiTheme: true,
            defaultSorting: '',
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: true, //Enable this to only select using checkboxes
            toolbar: {
                items: [{
                        text: eme.translate_paidandapprove,
                        cssClass: "eme_jtable_button_for_pending_only",
                        click: function () {
			          var selectedRows = $('#BookingsTableContainer').jtable('selectedRows');
				  var do_action = "paidandapprove";
				  if (selectedRows.length > 0) {
					  var ids = [];
					  selectedRows.each(function () {
						ids.push($(this).data('record')['booking_id']);
					  });
					  var idsjoined = ids.join(); //will be such a string '2,5,7'
					  $('.eme_jtable_button_for_pending_only .jtable-toolbar-item-text').text(eme.translate_pleasewait);
					  $.post(ajaxurl, {"booking_id": idsjoined, "action": "eme_manage_bookings", "do_action": do_action, "eme_admin_nonce": eme.translate_nonce }, function() {
						$('#BookingsTableContainer').jtable('reload');
					   	$('.eme_jtable_button_for_pending_only .jtable-toolbar-item-text').text(eme.translate_paidandapprove);
					  });
				  } else {
					  alert(eme.translate_pleaseselectrecords);
				  }
			       }
                        },
                        {
                        text: eme.translate_csv,
                        click: function () {
                                  jtable_csv('#BookingsTableContainer');
                               }
                        },
                        {
                        text: eme.translate_print,
                        click: function () {
                                  $('#BookingsTableContainer').printElement();
                               }
                        }
                        ]
            },
            actions: {
                listAction: ajaxurl+'?action=eme_bookings_list',
            },
            fields: {
                booking_id: {
                    title: eme.translate_id,
                    key: true,
                    list: true,
                    width: '2%',
                    listClass: 'eme-jtable-center'
                },
                event_name: {
                    title: eme.translate_eventname,
                },
                rsvp: {
                    title: eme.translate_rsvp,
                    searchable: false,
                    sorting: false,
                    width: '2%',
                    listClass: 'eme-jtable-center'
                },
                datetime: {
                    title: eme.translate_datetime,
                    searchable: false,
                    sorting: true
                },
                booker: {
                    title: eme.translate_booker
                },
                creation_date: {
                    title: eme.translate_bookingdate
                },
                seats: {
                    title: eme.translate_seats,
                    searchable: false,
                    sorting: false,
                    listClass: 'eme-jtable-center'
                },
                eventprice: {
                    title: eme.translate_eventprice,
                    searchable: false,
                    sorting: false
                },
                discount: {
                    title: eme.translate_discount,
                    searchable: false,
                    sorting: false,
	            visibility: 'hidden'
                },
                totalprice: {
                    title: eme.translate_totalprice,
                    searchable: false,
                    sorting: false
                },
                transfer_nbr_be97: {
                    title: eme.translate_uniquenbr
                },
                booking_paid: {
                    title: eme.translate_paid,
                    type: 'checkbox',
                    searchable: false,
                    values: { '0' : eme.translate_no, '1' : eme.translate_yes }
                },
                payment_date: {
                    title: eme.translate_paymentdate,
	            visibility: 'hidden'
                },
                payment_id: {
                    title: eme.translate_paymentid
                },
                lastreminder: {
                    title: eme.translate_lastreminder,
                    searchable: false,
                    sorting: false,
	            visibility: 'hidden'
                },
                edit_link: {
                    title: eme.translate_edit,
                    searchable: false,
                    sorting: false,
                    visibility: 'fixed',
                    listClass: 'eme-jtable-center'
                },
            }
        });

        // Load list from server, but only if the container is there
        // and only in the initial load we take a possible person id in the url into account
        // This person id can come from the eme_people page when clicking on "view all bookings"
        if ($('#BookingsTableContainer').length) {
           $('#BookingsTableContainer').jtable('load', {
               scope: $('#scope').val(),
               event_id: $('#event_id').val(),
               booking_status: $('#booking_status').val(),
               search_event: $('#search_event').val(),
               search_person: $('#search_person').val(),
               search_customfields: $('#search_customfields').val(),
               search_unique: $('#search_unique').val(),
               search_start_date: $('#search_start_date').val(),
               event_id: $_GET["event_id"],
               person_id: $_GET["person_id"]
           });
        }

        function updateShowHideStuff () {
	   var $action=$('select#eme_admin_action').val();
           if ($.inArray($action,['denyRegistration','approveRegistration','pendingRegistration','unsetwaitinglistRegistration','setwaitinglistRegistration','markPaid','markUnpaid']) >= 0) {
              $("span#span_sendmails").show();
           } else {
              $("span#span_sendmails").hide();
           }
           if ($action == 'pdf') {
              jQuery("span#span_pdftemplate").show();
           } else {
              jQuery("span#span_pdftemplate").hide();
           }
        }
        $('select#eme_admin_action').change(updateShowHideStuff);
        updateShowHideStuff();

        // hide one toolbar button if not on pending approval
        function hideButtonPaidApprove() {
           if ($('#booking_status').val() == 1) {
              $('.eme_jtable_button_for_pending_only').show();
           } else {
              $('.eme_jtable_button_for_pending_only').hide();
           }
        }
        hideButtonPaidApprove();

        // Actions button
        $('#BookingsActionsButton').button().click(function (e) {
           e.preventDefault();
           var selectedRows = $('#BookingsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var send_mail = $('#send_mail').val();
           var pdf_template = $('#pdf_template').val();

           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='denyRegistration') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
		 $('#BookingsActionsButton').text(eme.translate_pleasewait);
		 $('#BookingsActionsButton').prop('disabled', true);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['booking_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 var params = {
                        "booking_ids": idsjoined,
                        "action": "eme_manage_bookings",
                        "do_action": do_action,
                        "send_mail": send_mail,
                        "pdf_template": pdf_template,
                        "eme_admin_nonce": eme.translate_nonce };

                 if (do_action=='sendMails') {
                         var form = $('<form method="POST" action="'+eme.translate_admin_sendmails_url+'">');
                         var params = {
                                 "booking_ids": idsjoined,
                                 "eme_admin_action": 'new_mailing'
                                 };
                         $.each(params, function(k, v) {
                                 form.append($('<input type="hidden" name="' + k + '" value="' + v + '">'));
                         });
                         $('body').append(form);
                         form.submit();
                         return false;
                 }

                 if (do_action=='pdf') {
                         var form = $('<form method="POST" action="' + ajaxurl + '">');
                         $.each(params, function(k, v) {
                                 form.append($('<input type="hidden" name="' + k + '" value="' + v + '">'));
                         });
                         $('body').append(form);
                         console.log(form);
                         form.submit();
                         $('#BookingsActionsButton').text(eme.translate_apply);
                         $('#BookingsActionsButton').prop('disabled', false);
                         return false;
                 }
                 $.post(ajaxurl, params, function(data) {
	            $('#BookingsTableContainer').jtable('reload');
		    $('#BookingsActionsButton').text(eme.translate_apply);
		    $('#BookingsActionsButton').prop('disabled', false);
		    $('div#bookings-message').html(data.htmlmessage);
		    $('div#bookings-message').show();
		    $('div#bookings-message').delay(3000).fadeOut('slow');
                 }, "json");
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });

        // Re-load records when user click 'load records' button.
        $('#BookingsLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#BookingsTableContainer').jtable('load', {
               scope: $('#scope').val(),
               event_id: $('#event_id').val(),
               booking_status: $('#booking_status').val(),
               search_event: $('#search_event').val(),
               search_person: $('#search_person').val(),
               search_customfields: $('#search_customfields').val(),
               search_unique: $('#search_unique').val(),
               search_start_date: $('#search_start_date').val(),
           });
           // return false to make sure the real form doesn't submit
           return false;
        });

    // for autocomplete to work, the element needs to exist, otherwise JS errors occur
    // we check for that using length
    if ($("input[name=chooseevent]").length) {
          $("input[name=chooseevent]").autocomplete({
            source: function(request, response) {
                         $.post(ajaxurl,
                                  { q: request.term,
                                    not_event_id: $('#event_id').val(),
                                    action: 'eme_autocomplete_event'
                                  },
                                  function(data){
                                       response($.map(data, function(item) {
                                          return {
                                             eventinfo: htmlDecode(item.eventinfo),
                                             transferto_id: htmlDecode(item.event_id),
                                          };
                                       }));
                                  }, "json");
                    },
            select:function(evt, ui) {
                         // when a person is selected, populate related fields in this form
                         $('input[name=transferto_id]').val(ui.item.transferto_id);
                         $('input[name=chooseevent]').val(ui.item.eventinfo).attr("readonly", true);
                         return false;
                   },
            minLength: 2
          }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            return $( "<li></li>" )
            .append("<a><strong>"+htmlDecode(item.eventinfo)+'</strong></a>')
            .appendTo( ul );
          };

          // if manual input: set the hidden field empty again
          $('input[name=chooseevent]').keyup(function() {
             $('input[name=transferto_id]').val('');
          }).change(function() {
             if ($('input[name=chooseevent]').val()=='') {
                $('input[name=transferto_id]').val('');
             }
          });
    }

});
