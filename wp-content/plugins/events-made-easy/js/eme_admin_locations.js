jQuery(document).ready(function ($) { 
        //Prepare jtable plugin
        $('#LocationsTableContainer').jtable({
            title: eme.translate_locations,
            paging: true,
            sorting: true,
            toolbarsearch: true,
            jqueryuiTheme: true,
            defaultSorting: 'location_id ASC',
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: true, //Enable this to only select using checkboxes
            toolbar: {
                items: [{
                        text: eme.translate_csv,
                        click: function () {
                                  jtable_csv('#LocationsTableContainer');
                               }
                        },
                        {
                        text: eme.translate_print,
                        click: function () {
                                  $('#LocationsTableContainer').printElement();
                               }
                        }
                        ]
            },
            actions: {
                listAction: ajaxurl+'?action=eme_locations_list'
            },
            fields: {
                location_id: {
                    key: true,
		    title: eme.translate_id,
                    visibility: 'hidden'
                },
                location_name: {
		    title: eme.translate_name
                },
                copy: {
                    title: eme.translate_copy,
                    sorting: false,
                    width: '2%',
                    listClass: 'eme-jtable-center'
                },
                location_address1: {
		    title: eme.translate_address1,
                    visibility: 'hidden'
                },
                location_address2: {
		    title: eme.translate_address2,
                    visibility: 'hidden'
                },
                location_zip: {
		    title: eme.translate_zip,
                    visibility: 'hidden'
                },
                location_city: {
		    title: eme.translate_city,
                    visibility: 'hidden'
                },
                location_state: {
		    title: eme.translate_state,
                    visibility: 'hidden'
                },
                location_country: {
		    title: eme.translate_country,
                    visibility: 'hidden'
                },
                location_longitude: {
		    title: eme.translate_longitude,
                    visibility: 'hidden'
                },
                location_latitude: {
		    title: eme.translate_latitude,
                    visibility: 'hidden'
                }
            }
        });

        // Load list from server, but only if the container is there
        // and only in the initial load we take a possible person id in the url into account
        // This person id can come from the eme_people page when clicking on "view all bookings"
        if ($('#LocationsTableContainer').length) {
           $('#LocationsTableContainer').jtable('load', {
               search_name: $('#search_name').val(),
           });
        }

        // Actions button
        $('#LocationsActionsButton').button().click(function () {
           var selectedRows = $('#LocationsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var nonce = $('#eme_admin_nonce').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteLocations') && !confirm(eme.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#LocationsActionsButton').text(eme.translate_pleasewait);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['location_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 $.post(ajaxurl, {
					"location_id": idsjoined,
					"action": "eme_manage_locations",
					"do_action": do_action,
					"eme_admin_nonce": nonce },
                             function() {
	                        $('#LocationsTableContainer').jtable('reload');
                                $('#LocationsActionsButton').text(eme.translate_apply);
                             });
              }
           } else {
              alert(eme.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
 
        // Re-load records when user click 'load records' button.
        $('#LocationsLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#LocationsTableContainer').jtable('load', {
               search_name: $('#search_name').val()
           });
           // return false to make sure the real form doesn't submit
           return false;
        });

	$('#eme_remove_old_image').click(function(e) {
		$('#location_image_url').val('');
		$('#location_image_id').val('');
		$('#eme_location_image_example' ).attr("src",'');
		$('#location_current_image' ).hide();
		$('#location_no_image' ).show();
		$('#eme_remove_old_image' ).hide();
		$('#location_image_button' ).show();
	});
	$('#location_image_button').click(function(e) {
		e.preventDefault();

		var custom_uploader = wp.media({
			title: eme.translate_selectfeaturedimage,
			button: {
				text: eme.translate_setfeaturedimage
			},
			// Tell the modal to show only images.
			library: {
				type: 'image'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		}).on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				$('#location_image_url').val(attachment.url);
				$('#location_image_id').val(attachment.id);
				$('#eme_location_image_example' ).attr("src",attachment.url);
				$('#location_current_image' ).show();
				$('#location_no_image' ).hide();
				$('#eme_remove_old_image' ).show();
				$('#location_image_button' ).hide();
		}).open();
	});
	if ($('#location_image_url').val() != '') {
		$('#location_no_image' ).hide();
		$('#eme_remove_old_image' ).show();
		$('#location_image_button' ).hide();
	} else {
		$('#location_no_image' ).show();
		$('#eme_remove_old_image' ).hide();
		$('#location_image_button' ).show();
	}


});
