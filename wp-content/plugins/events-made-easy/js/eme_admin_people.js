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

	function eme_dynamic_people_data_json(form_id) {
		if ($('div#eme_dynpersondata').length) {
			var alldata = $('#'+form_id).serializeArray();
			alldata.push({name: 'eme_ajax_action', value: 'dynpersondata'});
			$('div#eme_dynpersondata').html('<img src="'+eme.translate_plugin_url+'images/spinner.gif">');
			$.post(self.location.href, alldata, function(data){
				$('div#eme_dynpersondata').html(data.result);
				if ($('.eme_formfield_date').length) {
					$('.eme_formfield_date').datepicker({
						changeYear: true,
						yearRange: "-60:+10",
						showOn: 'focus',
						showButtonPanel: true,
						closeText: eme.translate_clear, // Text to show for "close" button
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
						$(this).datepicker("option", "dateFormat", "yy-mm-dd" );
						$(this).datepicker('setDate', $(this).data('date'));
						// now change the visual format to the desired format
						$(this).datepicker("option", "dateFormat", $(this).data('dateformat') );
						$(this).datepicker("option", "altField", $('input[name="'+$(this).data('altfield')+'"]') );
						$(this).datepicker("option", "altFormat", "yy-mm-dd" );
					});
				}
			}, "json");
		}
	}

        var personfields = {
                'people.person_id': {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },
                'people.lastname': {
		    title: eme.translate_lastname,
                    inputClass: 'validate[required]'
                },
                'people.firstname': {
		    title: eme.translate_firstname
                },
                'people.address1': {
		    title: eme.translate_address1,
                    visibility: 'hidden'
                },
                'people.address2': {
		    title: eme.translate_address2,
                    visibility: 'hidden'
                },
                'people.zip': {
		    title: eme.translate_zip,
                    visibility: 'hidden'
                },
                'people.city': {
		    title: eme.translate_city,
                    visibility: 'hidden'
                },
                'people.state': {
		    title: eme.translate_state,
                    visibility: 'hidden'
                },
                'people.country': {
		    title: eme.translate_country,
                    visibility: 'hidden'
                },
                'people.email': {
		    title: eme.translate_email,
                    inputClass: 'validate[required]'
                },
                'people.phone': {
		    title: eme.translate_phone,
                    visibility: 'hidden'
                },
                'people.lang': {
		    title: eme.translate_lang,
                    visibility: 'hidden',
                    edit: false
                },
                'people.massmail': {
		    title: eme.translate_massmail,
                    visibility: 'hidden',
	            type: 'checkbox',
                    searchable: false,
                    defaultValue: eme.translate_option_massmail,
                    values: { '0' : eme.translate_no, '1' : eme.translate_yes }

                },
                'bookingsmade': {
		    title: eme.translate_bookingsmade,
                    create: false,
                    edit: false,
                    searchable: false,
                    sorting: false,
                    display: function (data) {
                       return '<a href="admin.php?page=eme-registration-seats&person_id='+ data.record['people.person_id']+'">' + eme.translate_showallbookings + '</a>';
                    }
                }
            }

        if ($('#PeopleTableContainer').length) {
		var extrafields=$('#PeopleTableContainer').data('extrafields').toString().split(',');
		var extrafieldnames=$('#PeopleTableContainer').data('extrafieldnames').toString().split(',');
		$.each(extrafields, function( index, value ) {
			var fieldindex='FIELD_'+value;
			var extrafield = {}
			extrafield[fieldindex] = {
				title: extrafieldnames[index],
				searchable: false,
				sorting: false,
				visibility: 'hidden'
			};
			$.extend(personfields,extrafield);
		});

		//Prepare jtable plugin
		$('#PeopleTableContainer').jtable({
			title: eme.translate_people,
			paging: true,
			sorting: true,
			multiSorting: true,
			toolbarsearch: true,
			jqueryuiTheme: true,
			defaultSorting: 'people.lastname ASC, people.firstname ASC',
			selecting: true, //Enable selecting
			multiselect: true, //Allow multiple selecting
			selectingCheckboxes: true, //Show checkboxes on first column
			selectOnRowClick: true,
			toolbar: {
				items: [{
					text: eme.translate_csv,
					click: function () {
						jtable_csv('#PeopleTableContainer');
					}
				},
					{
						text: eme.translate_print,
						click: function () {
							$('#PeopleTableContainer').printElement();
						}
					}
				]
			},
			actions: {
				listAction: ajaxurl+'?action=eme_people_list'
			},
			fields: personfields
		});
	}

        $('#GroupsTableContainer').jtable({
            title: eme.translate_groups,
            paging: true,
            sorting: true,
            jqueryuiTheme: true,
            defaultSorting: 'name ASC',
            toolbarsearch: true,
            toolbarreset: false,
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: true,
            actions: {
                listAction: ajaxurl+'?action=eme_groups_list',
		deleteAction: ajaxurl+'?action=eme_manage_groups&do_action=deleteGroups&eme_admin_nonce='+eme.translate_nonce,
            },
            fields: {
                group_id: {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },
                name: {
		    title: eme.translate_name,
                    inputClass: 'validate[required]'
                },
                description: {
		    title: eme.translate_description
                },
		groupcount: {
                    title: eme.translate_groupcount,
                    sorting: false
                }
            }
        });
 
        // Load list from server, but only if the container is there
        // and only in the initial load we take a possible person id in the url into account
        // This person id can come from the eme_people page when clicking on "view all bookings"
        if ($('#PeopleTableContainer').length) {
           $('#PeopleTableContainer').jtable('load', {
               search_name: $('#search_name').val(),
               search_group: $('#search_group').val(),
	       search_customfieldids: $('#search_customfieldids').val(),
               person_id: $_GET["person_id"]
           });
        }
        if ($('#GroupsTableContainer').length) {
           $('#GroupsTableContainer').jtable('load');
        }

        // Actions button
        $('#GroupsActionsButton').button().click(function () {
           var selectedRows = $('#GroupsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteGroups') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#GroupsActionsButton').text(eme.translate_pleasewait);
		 $('#GroupsActionsButton').prop('disabled', true);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['group_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 $.post(ajaxurl, {"group_id": idsjoined, "action": "eme_manage_groups", "do_action": do_action, "eme_admin_nonce": eme.translate_nonce }, function() {
			 $('#GroupsTableContainer').jtable('reload');
			 $('#GroupsActionsButton').text(eme.translate_apply);
		 	 $('#GroupsActionsButton').prop('disabled', false);
			 if (do_action=='deleteGroups') {
				 $('div#groups-message').html(eme.translate_deleted);
				 $('div#groups-message').show();
				 $('div#groups-message').delay(3000).fadeOut('slow');
			 }
                 });
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });

        // Actions button
        $('#PeopleActionsButton').button().click(function () {
           var selectedRows = $('#PeopleTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var pdf_template = $('#pdf_template').val();

           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deletePeople') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#PeopleActionsButton').text(eme.translate_pleasewait);
		 $('#PeopleActionsButton').prop('disabled', true);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['people.person_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 var params = {
                        "person_id": idsjoined,
                        "action": "eme_manage_people",
                        "do_action": do_action,
			"chooseperson": $('#chooseperson').val(),
                        "transferto_id": $('#transferto_id').val(),
                        "pdf_template": pdf_template,
                        "eme_admin_nonce": eme.translate_nonce };

                 if (do_action=='sendMails') {
                         var form = $('<form method="POST" action="'+eme.translate_admin_sendmails_url+'">');
			 var params = {
				 "person_ids": idsjoined,
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
                         $('#PeopleActionsButton').text(eme.translate_apply);
                         $('#PeopleActionsButton').prop('disabled', false);
                         return false;
                 }
                 $.post(ajaxurl, params, function() {
	                        $('#PeopleTableContainer').jtable('reload');
                                $('#PeopleActionsButton').text(eme.translate_apply);
		                $('#PeopleActionsButton').prop('disabled', false);
				if (do_action=='deletePeople') {
					$('div#people-message').html(eme.translate_deleted);
					$('div#people-message').show();
					$('div#people-message').delay(3000).fadeOut('slow');
				}
                             });
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
 
        // Re-load records when user click 'load records' button.
        $('#PeopleLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#PeopleTableContainer').jtable('load', {
               search_name: $('#search_name').val(),
               search_group: $('#search_group').val(),
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

        function updateShowHideStuff () {
	   var $action=$('select#eme_admin_action').val();

           if ($action == "deletePeople") {
              $("span#span_transferto").show();
           } else {
              $("span#span_transferto").hide();
           }
	   if ($action == 'pdf') {
              jQuery("span#span_pdftemplate").show();
           } else {
              jQuery("span#span_pdftemplate").hide();
           }
        }
        updateShowHideStuff();
        $('select#eme_admin_action').change(updateShowHideStuff);

	$('#editperson').on('change','select.dyngroups', function() {
	      eme_dynamic_people_data_json('editperson');
	});
	eme_dynamic_people_data_json('editperson');

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
                                             email: htmlDecode(item.email),
                                             person_id: htmlDecode(item.person_id)
                                          };
                                       }));
                                  }, "json");
                    },
            select:function(evt, ui) {
                         // when a person is selected, populate related fields in this form
                         $('input[name=transferto_id]').val(ui.item.person_id);
                         $('input[name=chooseperson]').val(ui.item.lastname+' '+ui.item.firstname).attr("readonly", true);
                         return false;
                   },
            minLength: 2
          }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            return $( "<li></li>" )
            .append("<a><strong>"+htmlDecode(item.lastname)+' '+htmlDecode(item.firstname)+'</strong><br /><small>'+htmlDecode(item.email)+' - '+htmlDecode(item.phone)+ '</small></a>')
            .appendTo( ul );
          };

          // if manual input: set the hidden field empty again
          $('input[name=chooseperson]').keyup(function() {
             $('input[name=transferto_id]').val('');
          }).change(function() {
             if ($('input[name=chooseperson]').val()=='') {
                $('input[name=transferto_id]').val('');
             }
          });
    }

        // for the image 
        $('#remove_old_image').click(function(e) {
                $('#image_id').val('');
                $('#person_image_example' ).attr("src",'');
                $('#current_image' ).hide();
                $('#no_image' ).show();
                $('#remove_old_image' ).hide();
                $('#image_button' ).show();
        });
        $('#image_button').click(function(e) {
                e.preventDefault();
                var custom_uploader = wp.media({
                        title: eme.translate_selectimg,
                        button: {
                                text: eme.translate_setimg
                        },
                        // Tell the modal to show only images.
                        library: {
                                type: 'image'
                        },
                        multiple: false  // Set this to true to allow multiple files to be selected
                }).on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        $('#image_id').val(attachment.id);
                        $('#person_image_example' ).attr("src",attachment.url);
                        $('#current_image' ).show();
                        $('#no_image' ).hide();
                        $('#remove_old_image' ).show();
                        $('#image_button' ).hide();
                }).open();
        });
        if (parseInt($('#image_id').val()) >0) {
                $('#no_image' ).hide();
                $('#current_image' ).show();
                $('#remove_old_image' ).show();
                $('#image_button' ).hide();
        } else {
                $('#no_image' ).show();
                $('#current_image' ).hide();
                $('#remove_old_image' ).hide();
                $('#image_button' ).show();
        }


});
