jQuery(document).ready( function($) {
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
                         $('input[name=send_previewmailto_id]').val(ui.item.person_id);
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
             $('input[name=send_previewmailto_id]').val('');
          }).change(function() {
             if ($('input[name=chooseperson]').val()=='') {
                $('input[name=send_previewmailto_id]').val('');
             }
          });
   }

   // initially the div is not shown using display:none, so jquery has time to render it and then we call show()
   // we use localStorage to remember the last active tab
   $("#mail-tabs").tabs({
	   active: localStorage.getItem("mailtabs_currentIdx"),
	   activate: function(event, ui) {
		   localStorage.setItem("mailtabs_currentIdx", $(this).tabs('option', 'active'));
	   }
   });
   $("#mail-tabs").show();
   if ($("#mail-tabs").data('showtab')) {
	   $("#mail-tabs").tabs( "option", "active", $("#mail-tabs").data('showtab') );
   }

   $('#eventmailButton').click(function (e) {
           e.preventDefault();
	   // if we want html mail, we need to save the html message first, otherwise the mail content is not ok via ajax submit
	   if (eme.translate_htmlmail=="yes") {
	   	var editor = tinymce.get('event_mail_message');
		editor.save();
	   }
	   var alldata = $(this.form).serializeArray();
	   var form_id = $(this.form).attr('id');
	   alldata.push({name: 'eme_ajax_action', value: 'eventmail'});
	   $('#eventmailButton').text(eme.translate_pleasewait);
	   $('#eventmailButton').prop('disabled', true);
	   $.post(self.location.href, alldata, function(data){
		   $('div#eventmail-message').html(data.htmlmessage);
		   $('div#eventmail-message').show();
		   if (data.result=='OK') {
		   	$('#'+form_id).trigger("reset");
		        $('div#eventmail-message').delay(10000).fadeOut('slow');
		   }
		   $('#eventmailButton').text(eme.translate_sendmail);
		   $('#eventmailButton').prop('disabled', false);
	   }, "json");
           return false;
   });

   $('#genericmailButton').click(function (e) {
           e.preventDefault();
	   // if we want html mail, we need to save the html message first, otherwise the mail content is not ok via ajax submit
	   if (eme.translate_htmlmail=="yes") {
	   	var editor = tinymce.get('generic_mail_message');
		editor.save();
	   }
	   var alldata = $(this.form).serializeArray();
	   var form_id = $(this.form).attr('id');
	   alldata.push({name: 'eme_ajax_action', value: 'genericmail'});
	   $('#genericmailButton').text(eme.translate_pleasewait);
	   $('#genericmailButton').prop('disabled', true);
	   $.post(self.location.href, alldata, function(data){
		   $('div#genericmail-message').html(data.htmlmessage);
		   $('div#genericmail-message').show();
		   if (data.result=='OK') {
		   	$('#'+form_id).trigger("reset");
			// the form reset doesn't reset other show/hide stuff apparently ...
			// so we call it ourselves
			$('input#eme_send_all_people').trigger('change');
			$('input#send_now').trigger('change');
			$('div#genericmail-message').delay(5000).fadeOut('slow');
		   }
		   $('#genericmailButton').text(eme.translate_sendmail);
		   $('#genericmailButton').prop('disabled', false);
	   }, "json");
           return false;
   });

   $('#previewmailButton').click(function (e) {
           e.preventDefault();
	   // if we want html mail, we need to save the html message first, otherwise the mail content is not ok via ajax submit
	   if (eme.translate_htmlmail=="yes") {
	   	var editor = tinymce.get('generic_mail_message');
		editor.save();
	   }
	   var alldata = $(this.form).serializeArray();
	   alldata.push({name: 'eme_ajax_action', value: 'previewmail'});
	   $.post(self.location.href, alldata, function(data){
		   $('div#previewmail-message').html(data.htmlmessage);
		   $('div#previewmail-message').show();
		   $('div#previewmail-message').delay(5000).fadeOut('slow');
		   if (data.result=='OK') {
                   	$('input[name=chooseperson]').val('');
                   	$('input[name=send_previewmailto_id]').val('');
			$('input[name=chooseperson]').attr("readonly", false);
		   }
	   }, "json");
           return false;
   });

   $('#testmailButton').click(function (e) {
           e.preventDefault();
	   var alldata = $(this.form).serializeArray();
	   var form_id = $(this.form).attr('id');
	   alldata.push({name: 'eme_ajax_action', value: 'testmail'});
	   $.post(self.location.href, alldata, function(data){
		   $('div#testmail-message').html(data.htmlmessage);
		   $('div#testmail-message').show();
		   $('div#testmail-message').delay(5000).fadeOut('slow');
		   if (data.result=='OK') {
		   	$('#'+form_id).trigger("reset");
		   }
	   }, "json");
           return false;
   });

   // show selected template in form
   $('select#event_subject_template').change(function (e) {
          e.preventDefault();
	  $.post(ajaxurl,
		  { action: 'eme_get_template',
		    template_id: $('select#event_subject_template').val(),
		  },
		  function(data){
		      $('input#event_mail_subject').val(data.htmlmessage);
		  }, "json");

   });

   // show selected template in form
   $('select#event_message_template').change(function (e) {
          e.preventDefault();
	  $.post(ajaxurl,
		  { action: 'eme_get_template',
		    template_id: $('select#event_message_template').val(),
		  },
		  function(data){
			  if (eme.translate_htmlmail=="yes") {
				  var editor = tinymce.get('event_mail_message');
				  editor.setContent(data.htmlmessage);
				  editor.save();
			  } else {
				  $('textarea#event_mail_message').val(data.htmlmessage);
			  }
		  }, "json");

   });

   // show selected template in form
   //$('select#generic_subject_template').change(function (e) {
   //       e.preventDefault();
//	  $.post(ajaxurl,
//		  { action: 'eme_get_template',
//		    template_id: $('select#generic_subject_template').val(),
//		  },
//		  function(data){
//		      $('input#generic_mail_subject').val(data.htmlmessage);
//		  }, "json");
//  });

   // show selected template in form
   $('select#generic_message_template').change(function (e) {
          e.preventDefault();
	  $.post(ajaxurl,
		  { action: 'eme_get_template',
		    template_id: $('select#generic_message_template').val(),
		  },
		  function(data){
			  if (eme.translate_htmlmail=="yes") {
				  var editor = tinymce.get('generic_mail_message');
				  editor.setContent(data.htmlmessage);
				  editor.save();
			  } else {
				  $('textarea#generic_mail_message').val(data.htmlmessage);
			  }
		  }, "json");

   });

   function updateShowSendGroups () {
                if ($('input#eme_send_all_people').attr("checked")) {
                        $("div#div_eme_send_groups").hide();
                } else {
                        $("div#div_eme_send_groups").show();
                }
   }
   $('input#eme_send_all_people').change(updateShowSendGroups);
   updateShowSendGroups();

   function updateShowMailingName () {
                if ($('input#send_now').attr("checked")) {
                        $("div#div_mailing_definition").hide();
                } else {
                        $("div#div_mailing_definition").show();
                }
   }
   $('input#send_now').change(updateShowMailingName);
   updateShowMailingName();

    $('.eme_select2_events_class').select2({
	placeholder: eme.translate_selectevents,
        width: '100%'
    });
    $('.eme_select2_groups_class').select2({
	placeholder: eme.translate_selectgroups,
        width: '100%'
    });
    $('.eme_select2_memberships_class').select2({
	placeholder: eme.translate_selectmemberships,
        width: '100%'
    });
    // to make sure the placeholder shows after a hidden select2 is shown (bug workaround)
    $('.select2-search--inline, .select2-search__field').css('width', '100%');

    $('#startdate').datetimepicker({
	altField: "#actualstartdate",
	altFieldTimeOnly: false,
	altFormat: "yy-mm-dd",
	altTimeFormat: "HH:mm:ss",
	dateFormat: eme.translate_dateformat,
	timeFormat: eme.translate_timeformat,
	showSecond: false
    });

        //Prepare jtable plugin
        jQuery('#MailingReportTableContainer').jtable({
            title: eme.translate_mailingreport,
            paging: true,
            sorting: true,
            toolbarsearch: false,
            toolbarreset: false,
            jqueryuiTheme: true,
            defaultSorting: '',
            selecting: false, //Enable selecting
            multiselect: false, //Allow multiple selecting
            selectingCheckboxes: false, //Show checkboxes on first column
            selectOnRowClick: false, //Enable this to only select using checkboxes
            actions: {
                listAction: ajaxurl+'?action=eme_mailingreport_list&mailing_id='+$_GET["id"],
            },
            fields: {
                receiveremail: {
                    title: eme.translate_email,
                },
                receivername: {
                    title: eme.translate_name,
                },
                status: {
                    title: eme.translate_status,
                },
                sent_datetime: {
                    title: eme.translate_sentdatetime,
                    searchable: false,
                    sorting: true
                },
                action: {
                    title: eme.translate_action,
                    searchable: false,
                    sorting: false
                }
            }
        });
        if ($('#MailingReportTableContainer').length) {
           $('#MailingReportTableContainer').jtable('load');
        }

        // Re-load records when user click 'load records' button.
        $('#ReportLoadRecordsButton').click(function (e) {
           e.preventDefault();
           $('#MailingReportTableContainer').jtable('load', {
               search_name: $('#search_name').val()
           });
           // return false to make sure the real form doesn't submit
           return false;
        });


});
