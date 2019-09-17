jQuery(document).ready( function($) {

        $('#FormfieldsTableContainer').jtable({
            title: emeformfields.translate_formfields,
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
                listAction: ajaxurl+'?action=eme_formfields_list',
                deleteAction: ajaxurl+'?action=eme_manage_formfields&do_action=deleteFormfields&eme_admin_nonce='+emeformfields.translate_nonce
            },
            fields: {
                field_id: {
                    key: true,
		    title: emeformfields.translate_id,
                    visibility: 'hidden'
                },
                field_name: {
		    title: emeformfields.translate_name
                },
                field_type: {
		    title: emeformfields.translate_type
                },
                field_required: {
		    title: emeformfields.translate_required,
                    width: '2%'
                },
                field_purpose: {
		    title: emeformfields.translate_purpose
                },
                used: {
		    title: emeformfields.translate_used,
                    sorting: false,
                    width: '2%'
                },
                edit_link: {
                    title: emeformfields.translate_edit,
                    searchable: false,
                    sorting: false,
                    visibility: 'fixed',
                    listClass: 'eme-jtable-center',
                    width: '2%'
                }
            }
        });
 
        if ($('#FormfieldsTableContainer').length) {
           $('#FormfieldsTableContainer').jtable('load', {
               search_type: $('#search_type').val()
	   });
        }
 
        // Actions button
        $('#FormfieldsActionsButton').button().click(function () {
           var selectedRows = $('#FormfieldsTableContainer').jtable('selectedRows');
           var do_action = $('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteFormfields') && !confirm(emeformfields.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 $('#FormfieldsActionsButton').text(emeformfields.translate_pleasewait);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push($(this).data('record')['field_id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 $.post(ajaxurl, {"field_id": idsjoined, "action": "eme_manage_formfields", "do_action": do_action, "eme_admin_nonce": emeformfields.translate_nonce }, function(data) {
			 $('#FormfieldsTableContainer').jtable('reload');
			 $('#FormfieldsActionsButton').text(emeformfields.translate_apply);
			 $('div#formfields-message').html(data.htmlmessage);
			 $('div#formfields-message').show();
			 $('div#formfields-message').delay(3000).fadeOut('slow');
                 },"json");
              }
           } else {
              alert(emeformfields.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
	//
        // Re-load records when user click 'load records' button.
        $('#FormfieldsLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#FormfieldsTableContainer').jtable('load', {
               search_type: $('#search_type').val()
           });
           // return false to make sure the real form doesn't submit
           return false;
        });

	function updateShowHideFormfields () {
		if (jQuery('select#field_purpose').val() == "people") {
			$("tr#tr_extra_charge").hide();
			$("tr#tr_field_tags").hide();
			$("tr#tr_field_condition").show();
		} else {
			$("tr#tr_extra_charge").show();
			$("tr#tr_field_tags").show();
			$("tr#tr_field_condition").hide();
		}
	}
	$('select#field_purpose').change(updateShowHideFormfields);
	updateShowHideFormfields();
});
