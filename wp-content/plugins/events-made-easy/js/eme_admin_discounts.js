    jQuery(document).ready(function () { 
        //Prepare jtable plugin
        jQuery('#DiscountsTableContainer').jtable({
            title: emediscounts.translate_discounts,
            paging: true,
            sorting: true,
            toolbarsearch: true,
            toolbarreset: false,
            jqueryuiTheme: true,
            defaultSorting: 'name ASC',
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: true, //Enable this to only select using checkboxes
            actions: {
                listAction: ajaxurl+'?action=eme_discount_list',
                deleteAction: ajaxurl+'?action=eme_manage_discounts&do_action=deleteDiscounts&eme_admin_nonce='+emediscounts.translate_nonce,
		updateAction: function (postData) {
                    return jQuery.Deferred(function ($dfd) {
                        jQuery.ajax({
                            url: ajaxurl+'?action=eme_discount_edit',
                            type: 'POST',
                            dataType: 'json',
                            data: postData,
                            success: function (data) {
                                $dfd.resolve(data);
                                jQuery('div#discounts-message').html(emediscounts.translate_updated);
                                jQuery('div#discounts-message').show();
                                jQuery('div#discounts-message').delay(3000).fadeOut('slow');
                            },
                            error: function () {
                                $dfd.reject();
                            }
                        });
                    });
                },
		createAction: function (postData) {
                    return jQuery.Deferred(function ($dfd) {
                        jQuery.ajax({
                            url: ajaxurl+'?action=eme_discount_edit',
                            type: 'POST',
                            dataType: 'json',
                            data: postData,
                            success: function (data) {
                                $dfd.resolve(data);
                                jQuery('div#discounts-message').html(emediscounts.translate_added);
                                jQuery('div#discounts-message').show();
                                jQuery('div#discounts-message').delay(3000).fadeOut('slow');
                            },
                            error: function () {
                                $dfd.reject();
                            }
                        });
                    });
                }
            },
            fields: {
                id: {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },
                name: {
		    title: emediscounts.translate_name,
                    inputClass: 'validate[required]'
                },
                description: {
		    title: emediscounts.translate_description
                },
                dgroup: {
		    title: emediscounts.translate_discountgroup
                },
                coupon: {
		    title: emediscounts.translate_coupon
                },
                strcase: {
		    title: emediscounts.translate_casesensitive,
                    type: 'checkbox',
                    searchable: false,
                    values: { '0' : emediscounts.translate_no, '1' : emediscounts.translate_yes }
                },
                value: {
		    title: emediscounts.translate_value
                },
                type: {
                    title: emediscounts.translate_type,
                    searchable: false,
                    options: { '1': emediscounts.translate_fixed, '2': emediscounts.translate_percentage, '3' : emediscounts.translate_code, '4': emediscounts.translate_fixed_per_seat }
                },
                maxcount: {
		    title: emediscounts.translate_maxusage
                },
                count: {
		    title: emediscounts.translate_usage
                },
                expire: {
		    title: emediscounts.translate_expiration,
                    type: 'date',
                    displayFormat: 'yy-mm-dd'
                }
            }
        });

        jQuery('#DiscountGroupsTableContainer').jtable({
            title: emediscounts.translate_discountgroups,
            paging: true,
            sorting: true,
            jqueryuiTheme: true,
            defaultSorting: 'name ASC',
            toolbarsearch: true,
            toolbarreset: false,
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            selectOnRowClick: false, //Enable this to only select using checkboxes
            actions: {
                listAction: ajaxurl+'?action=eme_discountgroups_list',
                deleteAction: ajaxurl+'?action=eme_manage_discountgroups&do_action=deleteDiscountGroups&eme_admin_nonce='+emediscounts.translate_nonce,
		updateAction: function (postData) {
                    return jQuery.Deferred(function ($dfd) {
                        jQuery.ajax({
                            url: ajaxurl+'?action=eme_discountgroups_edit',
                            type: 'POST',
                            dataType: 'json',
                            data: postData,
                            success: function (data) {
                                $dfd.resolve(data);
                                jQuery('div#discountgroups-message').html(emediscounts.translate_updated);
                                jQuery('div#discountgroups-message').show();
                                jQuery('div#discountgroups-message').delay(3000).fadeOut('slow');
                            },
                            error: function () {
                                $dfd.reject();
                            }
                        });
                    });
                },
		createAction: function (postData) {
                    return jQuery.Deferred(function ($dfd) {
                        jQuery.ajax({
                            url: ajaxurl+'?action=eme_discountgroups_edit',
                            type: 'POST',
                            dataType: 'json',
                            data: postData,
                            success: function (data) {
                                $dfd.resolve(data);
                                jQuery('div#discountgroups-message').html(emediscounts.translate_added);
                                jQuery('div#discountgroups-message').show();
                                jQuery('div#discountgroups-message').delay(3000).fadeOut('slow');
                            },
                            error: function () {
                                $dfd.reject();
                            }
                        });
                    });
                }
            },
            fields: {
                id: {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },
                name: {
		    title: emediscounts.translate_name,
                    inputClass: 'validate[required]'
                },
                description: {
		    title: emediscounts.translate_description
                },
                maxdiscounts: {
		    title: emediscounts.translate_maxdiscounts
                }
            }
        });
 
        // Load list from server, but only if the container is there
        if (jQuery('#DiscountsTableContainer').length) {
           jQuery('#DiscountsTableContainer').jtable('load');
        }
        if (jQuery('#DiscountGroupsTableContainer').length) {
           jQuery('#DiscountGroupsTableContainer').jtable('load');
        }
 
        // Actions button
        jQuery('#DiscountsActionsButton').button().click(function () {
           var selectedRows = jQuery('#DiscountsTableContainer').jtable('selectedRows');
           var do_action = jQuery('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteDiscounts') && !confirm(emediscounts.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 jQuery('#DiscountsActionsButton').text(emediscounts.translate_pleasewait);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push(jQuery(this).data('record')['id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 jQuery.post(ajaxurl, {"id": idsjoined, "action": "eme_manage_discounts", "do_action": do_action, "eme_admin_nonce": emediscounts.translate_nonce }, function() {
			 jQuery('#DiscountsTableContainer').jtable('reload');
			 jQuery('#DiscountsActionsButton').text(emediscounts.translate_apply);
			 if (do_action=='deleteDiscounts') {
				 jQuery('div#discounts-message').html(emediscounts.translate_deleted);
				 jQuery('div#discounts-message').show();
				 jQuery('div#discounts-message').delay(3000).fadeOut('slow');
			 }
                 });
              }
           } else {
              alert(emediscounts.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
 
        // Actions button
        jQuery('#DiscountGroupsActionsButton').button().click(function () {
           var selectedRows = jQuery('#DiscountGroupsTableContainer').jtable('selectedRows');
           var do_action = jQuery('#eme_admin_action').val();
           var action_ok=1;
           if (selectedRows.length > 0) {
              if ((do_action=='deleteDiscountGroups') && !confirm(emediscounts.translate_areyousuretodeleteselected)) {
                 action_ok=0;
              }
              if (action_ok==1) {
                 jQuery('#DiscountGroupsActionsButton').text(emediscounts.translate_pleasewait);
                 var ids = [];
                 selectedRows.each(function () {
                   ids.push(jQuery(this).data('record')['id']);
                 });

                 var idsjoined = ids.join(); //will be such a string '2,5,7'
                 jQuery.post(ajaxurl, {"id": idsjoined, "action": "eme_manage_discountgroups", "do_action": do_action, "eme_admin_nonce": emediscounts.translate_nonce }, function() {
			 jQuery('#DiscountGroupsTableContainer').jtable('reload');
			 jQuery('#DiscountGroupsActionsButton').text(emediscounts.translate_apply);
			 if (do_action=='deleteDiscountGroups') {
				 jQuery('div#discountgroups-message').html(emediscounts.translate_deleted);
				 jQuery('div#discountgroups-message').show();
				 jQuery('div#discountgroups-message').delay(3000).fadeOut('slow');
			 }
                 });
              }
           } else {
              alert(emediscounts.translate_pleaseselectrecords);
           }
           // return false to make sure the real form doesn't submit
           return false;
        });
     });
