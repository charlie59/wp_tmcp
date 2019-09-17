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

        var memberfields = {
                member_id: {
                    key: true,
		    title: eme.translate_id,
                    visibility: 'hidden'
                },
                lastname: {
		    title: eme.translate_lastname
                },
                firstname: {
		    title: eme.translate_firstname
                },
                email: {
		    title: eme.translate_email
                },
                membership_name: {
		    title: eme.translate_membership,
                    visibility: 'hidden'
                },
                start_date: {
		    title: eme.translate_startdate,
                    visibility: 'hidden'
                },
                end_date: {
		    title: eme.translate_enddate,
                    visibility: 'hidden'
                },
                creation_date: {
		    title: eme.translate_registrationdate,
                    visibility: 'hidden'
                },
                payment_date: {
		    title: eme.translate_paymentdate,
                    visibility: 'hidden'
                },
                paid: {
		    title: eme.translate_paid,
                    visibility: 'hidden'
                },
                reminder_date: {
		    title: eme.translate_lastreminder,
                    visibility: 'hidden'
                },
                reminder: {
		    title: eme.translate_nbrreminder,
                    visibility: 'hidden'
                },
                status: {
		    title: eme.translate_status,
                    visibility: 'hidden'
                }
	}
        if ($('#MembersTableContainer').length) {
                var extrafields=$('#MembersTableContainer').data('extrafields').toString().split(',');
                var extrafieldnames=$('#MembersTableContainer').data('extrafieldnames').toString().split(',');
                $.each(extrafields, function( index, value ) {
                        var fieldindex='FIELD_'+value;
                        var extrafield = {}
                        extrafield[fieldindex] = {
                                title: extrafieldnames[index],
                                searchable: false,
                                sorting: false,
                                visibility: 'hidden'
                        };
                        $.extend(memberfields,extrafield);
                });

		//Prepare jtable plugin
		$('#MembersTableContainer').jtable({
			title: eme.translate_members,
			paging: true,
			sorting: true,
			multiSorting: true,
			toolbarsearch: true,
			jqueryuiTheme: true,
			defaultSorting: '',
			selecting: true, //Enable selecting
			multiselect: true, //Allow multiple selecting
			selectingCheckboxes: true, //Show checkboxes on first column
			selectOnRowClick: true,
			toolbar: {
				items: [{
					text: eme.translate_csv,
					click: function () {
						jtable_csv('#MembersTableContainer');
					}
				},
					{
						text: eme.translate_print,
						click: function () {
							$('#MembersTableContainer').printElement();
						}
					}
				]
			},
			actions: {
				listAction: ajaxurl+'?action=eme_members_list'
			},
			fields: memberfields
		});
		$('#MembersTableContainer').jtable('load', {
			search_person: $('#search_person').val(),
			search_status: $('#search_status').val(),
			search_membershipid: $('#search_membershipid').val(),
			search_memberid: $('#search_memberid').val(),
			search_customfields: $('#search_customfields').val(),
			search_customfieldids: $('#search_customfieldids').val()
		});
	}

        if ($('#MembershipsTableContainer').length) {
		$('#MembershipsTableContainer').jtable({
			title: eme.translate_memberships,
			paging: true,
			sorting: true,
			multiSorting: true,
			jqueryuiTheme: true,
			defaultSorting: 'name ASC',
			toolbarsearch: true,
			toolbarreset: false,
			selecting: true, //Enable selecting
			multiselect: true, //Allow multiple selecting
			selectingCheckboxes: true, //Show checkboxes on first column
			selectOnRowClick: true,
			actions: {
				listAction: ajaxurl+'?action=eme_memberships_list'
			},
			fields: {
				membership_id: {
					key: true,
					title: eme.translate_id,
					visibility: 'hidden'
				},
				name: {
					title: eme.translate_name
				},
				description: {
					title: eme.translate_description
				},
				membercount: {
					title: eme.translate_membercount,
					sorting: false
				},
				contact: {
					title: eme.translate_contact,
					sorting: false
				}
			}
		});
		$('#MembershipsTableContainer').jtable('load');
        }
 
        // Actions button
        $('#MembershipsActionsButton').button().click(function () {
           var selectedRows = $('#MembershipsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteMemberships') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#MembershipsActionsButton').text(eme.translate_pleasewait);
		 $('#MembershipsActionsButton').prop('disabled', true);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['membership_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 $.post(ajaxurl, {"membership_id": idsjoined, "action": "eme_manage_memberships", "do_action": do_action, "eme_admin_nonce": eme.translate_nonce }, function(data) {
			$('#MembershipsTableContainer').jtable('reload');
			$('#MembershipsActionsButton').text(eme.translate_apply);
		 	$('#MembershipsActionsButton').prop('disabled', false);
			$('div#memberships-message').html(data.htmlmessage);
			$('div#memberships-message').show();
			$('div#memberships-message').delay(3000).fadeOut('slow');
                 }, "json");
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });

        // Actions button
        $('#MembersActionsButton').button().click(function () {
           var selectedRows = $('#MembersTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
	   var send_mail = $('#send_mail').val();
	   var pdf_template = $('#pdf_template').val();

           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteMembers') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#MembersActionsButton').text(eme.translate_pleasewait);
		 $('#MembersActionsButton').prop('disabled', true);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['member_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
		 var params = {
			"member_id": idsjoined,
			"action": "eme_manage_members",
			"do_action": do_action,
			"send_mail": send_mail,
			"pdf_template": pdf_template,
			"eme_admin_nonce": eme.translate_nonce };

		 if (do_action=='sendMails') {
                         var form = $('<form method="POST" action="'+eme.translate_admin_sendmails_url+'">');
                         var params = {
                                 "member_ids": idsjoined,
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
			 form.submit();
			 $('#MembersActionsButton').text(eme.translate_apply);
			 $('#MembersActionsButton').prop('disabled', false);
			 return false;
		 }
                 $.post(ajaxurl, params, function(data) {
	                        $('#MembersTableContainer').jtable('reload');
                                $('#MembersActionsButton').text(eme.translate_apply);
		                $('#MembersActionsButton').prop('disabled', false);
				$('div#members-message').html(data.htmlmessage);
				$('div#members-message').show();
			        $('div#members-message').delay(3000).fadeOut('slow');
                        }, "json");
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
 
        // Re-load records when user click 'load records' button.
        $('#MembersLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#MembersTableContainer').jtable('load', {
               search_person: $('#search_person').val(),
               search_status: $('#search_status').val(),
               search_membershipid: $('#search_membershipid').val(),
               search_memberid: $('#search_memberid').val(),
               search_customfields: $('#search_customfields').val(),
               search_customfieldids: $('#search_customfieldids').val()
           });
           // return false to make sure the real form doesn't submit
           return false;
        });

        $('.eme_select2_filter_class').select2({
		width: '10%'
	});
	// to make sure the placeholder shows after a hidden select2 is shown (bug workaround)
	$('.select2-search--inline, .select2-search__field').css('width', '100%');

        function updateShowHideFixedStartdate () {
           if ($('select#type').val() == "fixed") {
              $("tr#startdate").show();
           } else {
              $("tr#startdate").hide();
           }
        }
	if ($('select#type').length) {
           $('select#type').change(updateShowHideFixedStartdate);
           updateShowHideFixedStartdate();
	}
        function updateShowHideReminder () {
           if ($('select#duration_period').val() == "forever") {
              $("tr#reminder").hide();
              $("#duration_count").hide();
           } else {
              $("tr#reminder").show();
              $("#duration_count").show();
           }
        }
	if ($('select#duration_period').length) {
           $('select#duration_period').change(updateShowHideReminder);
           updateShowHideReminder();
	}

        function updateShowHideMemberState () {
           if ($('select#status_automatic').val() == "1") {
              $("select#status").attr("disabled", true);
           } else {
              $("select#status").attr("disabled", false);
           }
        }
	if ($('select#status_automatic').length) {
           $('select#status_automatic').change(updateShowHideMemberState);
           updateShowHideMemberState();
	}

	// initially the div is not shown using display:none, so jquery has time to render it and then we call show()
	if ($("#membership-tabs").length) {
		$("#membership-tabs").tabs();
		$("#membership-tabs").show();
	}

	// for autocomplete to work, the element needs to exist, otherwise JS errors occur
	// we check for that using length
	if ($("input[name=chooseperson]").length) {
		$("input[name=chooseperson]").autocomplete({
			source: function(request, response) {
				$.post(ajaxurl,
					{ q: request.term,
					  action: 'eme_autocomplete_people',
					  eme_searchlimit: 'people'
					},
					function(data){
						response($.map(data, function(item) {
							return {
								lastname: htmlDecode(item.lastname),
								firstname: htmlDecode(item.firstname),
								email: item.email,
								address1: htmlDecode(item.address1),
								address2: htmlDecode(item.address2),
								city: htmlDecode(item.city),
								status: htmlDecode(item.status),
								zip: htmlDecode(item.zip),
								country: htmlDecode(item.country),
								phone: item.phone,
								person_id: item.person_id
							};
						}));
					}, "json");
			},
			select:function(evt, ui) {
				// when a person is selected, populate related fields in this form
				$('input[name=lastname]').val(ui.item.lastname).attr("readonly", true);
				$('input[name=firstname]').val(ui.item.firstname).attr("readonly", true);
				$('input[name=email]').val(ui.item.email).attr("readonly", true);
				$('input[name=person_id]').val(ui.item.person_id).attr("readonly", true);
				$('input[name=address1]').val(ui.item.address1).attr("readonly", true);
				$('input[name=address2]').val(ui.item.address2).attr("readonly", true);
				$('input[name=city]').val(ui.item.city).attr("readonly", true);
				$('input[name=status]').val(ui.item.status).attr("readonly", true);
				$('input[name=zip]').val(ui.item.zip).attr("readonly", true);
				$('input[name=country]').val(ui.item.country).attr("readonly", true);
				$('input[name=phone]').val(ui.item.phone).attr("readonly", true);
				$('input[name=chooseperson]').val(ui.item.lastname+' '+ui.item.firstname).attr("readonly", true);
				return false;
			},
			minLength: 2
		}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.append("<a><strong>"+htmlDecode(item.lastname)+' '+htmlDecode(item.firstname)+'</strong><br /><small>'+htmlDecode(item.email)+' - '+htmlDecode(item.phone)+ '</small></a>')
				.appendTo( ul );
		};
	}
	// if manual input: set the hidden field empty again
	$('input[name=chooseperson]').change(function() {
		if ($('input[name=chooseperson]').val()=='') {
			$('input[name=person_id]').val('');
			$('input[name=lastname]').val('').attr("readonly", false);
			$('input[name=firstname]').val('').attr("readonly", false);
			$('input[name=email]').val('').attr("readonly", false);
			$('input[name=address1]').val('').attr("readonly", false);
			$('input[name=address2]').val('').attr("readonly", false);
			$('input[name=city]').val('').attr("readonly", false);
			$('input[name=status]').val('').attr("readonly", false);
			$('input[name=zip]').val('').attr("readonly", false);
			$('input[name=country]').val('').attr("readonly", false);
			$('input[name=phone]').val('').attr("readonly", false);
		}
	});

	function updateShowHideStuff () {
           var $action=$('select#eme_admin_action').val();
           if ($.inArray($action,['markPaid','extendMembership','renewExpiredMembership','stopMembership']) >= 0) {
	      $('#send_mail').val(1);
              $("span#span_sendmails").show();
	   } else if ($action == 'markUnpaid') {
	      $('#send_mail').val(0);
              $("span#span_sendmails").show();
           } else {
              $("span#span_sendmails").hide();
           }
           if ($action == 'pdf') {
              $("span#span_pdftemplate").show();
           } else {
              $("span#span_pdftemplate").hide();
           }
        }
        jQuery('select#eme_admin_action').change(updateShowHideStuff);
        updateShowHideStuff();

});
