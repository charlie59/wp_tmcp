function base64_encode(data) {
  if (!data) {
    return data;
  }

  return window.btoa(unescape(encodeURIComponent(data)));
}

function areyousure(message) {
   if (!confirm(message)) {
      return false;
   } else {
      return true;
   }
}

jQuery(document).ready( function($) {

	jQuery('input.select-all').change(function() {
		if (jQuery(this).is(':checked')) {
			jQuery('input.row-selector').attr('checked', true);
		} else {
			jQuery('input.row-selector').attr('checked', false);
		}
	});
        jQuery('div[data-dismissible] button.notice-dismiss').click(function (event) {
            event.preventDefault();
            var $el = jQuery('div[data-dismissible]');

            var attr_value, option_name, dismissible_length, data;

            attr_value = $el.attr('data-dismissible').split('-');

            // remove the dismissible length from the attribute value and rejoin the array.
            dismissible_length = attr_value.pop();

            option_name = attr_value.join('-');

            var ajaxdata = {
                'action': 'eme_dismiss_admin_notice',
                'option_name': option_name,
                'dismissible_length': dismissible_length,
                'nonce': emeadmin.translate_nonce
            };

            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post(ajaxurl, ajaxdata);

        });
 
	// the next code adds an "X" to input fields, and removes possible readonly-attr
	function tog(v){return v?'addClass':'removeClass';}
	jQuery(document).on('input', '.clearableRW', function(){
		jQuery(this)[tog(this.value)]('x');
	}).on('mousemove', '.x', function( e ){
		jQuery(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');
	}).on('touchstart click', '.onX', function( ev ){
		ev.preventDefault();
		jQuery(this).removeClass('x onX').val('').change();
		jQuery(this).attr("readonly", false)
	});
	jQuery(document).on('input', '.clearable', function(){
		jQuery(this)[tog(this.value)]('x');
	}).on('mousemove', '.x', function( e ){
		jQuery(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');
	}).on('touchstart click', '.onX', function( ev ){
		ev.preventDefault();
		jQuery(this).removeClass('x onX').val('').change();
	});

	jQuery('#eme_attr_add_tag').click( function(event) {
		event.preventDefault();
		//Get All meta rows
		var metas = jQuery('#eme_attr_body').children();
		//Copy first row and change values
		var metaCopy = jQuery(metas[0]).clone(true);
		newId = metas.length + 1;
		metaCopy.attr('id', 'eme_attr_'+newId);
		metaCopy.find('a').attr('rel', newId);
		metaCopy.find('[name=eme_attr_1_ref]').attr({
				name:'eme_attr_'+newId+'_ref' ,
				value:'' 
		});
		metaCopy.find('[name=eme_attr_1_content]').attr({ 
				name:'eme_attr_'+newId+'_content' , 
				value:'' 
		});
		metaCopy.find('[name=eme_attr_1_name]').attr({ 
				name:'eme_attr_'+newId+'_name' ,
				value:'' 
		});
		//Insert into end of file
		jQuery('#eme_attr_body').append(metaCopy);
		//Duplicate the last entry, remove values and rename id
	});
	
	jQuery('#eme_attr_body a').click( function(event) {
		event.preventDefault();
		//Only remove if there's more than 1 meta tag
		if(jQuery('#eme_attr_body').children().length > 1){
			//Remove the item
			jQuery(jQuery(this).parent().parent().get(0)).remove();
			//Renumber all the items
			jQuery('#eme_attr_body').children().each( function(i){
				metaCopy = jQuery(this);
				oldId = metaCopy.attr('id').replace('eme_attr_','');
				newId = i+1;
				metaCopy.attr('id', 'eme_attr_'+newId);
				metaCopy.find('a').attr('rel', newId);
				metaCopy.find('[name=eme_attr_'+ oldId +'_ref]').attr('name', 'eme_attr_'+newId+'_ref');
				metaCopy.find('[name=eme_attr_'+ oldId +'_content]').attr('name', 'eme_attr_'+newId+'_content');
				metaCopy.find('[name=eme_attr_'+ oldId +'_name]').attr( 'name', 'eme_attr_'+newId+'_name');
			});
		} else {
			metaCopy = jQuery(jQuery(this).parent().parent().get(0));
			metaCopy.find('[name=eme_attr_1_ref]').attr('value', '');
			metaCopy.find('[name=eme_attr_1_content]').attr('value', '');
			metaCopy.find('[name=eme_attr_1_name]').attr( 'value', '');
		}
	});

	jQuery('#eme_dyndata_add_tag').click( function(event) {
		event.preventDefault();
		//Get All meta rows
		var metas = jQuery('#eme_dyndata_body').children();
		//Copy first row and change values
		var metaCopy = jQuery(metas[0]).clone(true);
		newId = metas.length + 1;
		metaCopy.attr('id', 'eme_dyndata_'+newId);
		metaCopy.find('a').attr('rel', newId);
		var metafields=['_field','_condition','_condval','_template_id_header','_template_id','_template_id_footer','_repeat','_grouping'];
		var arrayLength = metafields.length;
		for (var i = 0; i < arrayLength; i++) {
		   metaCopy.find('[name=eme_dyndata_1'+metafields[i]+']').attr({
				name:'eme_dyndata_'+newId+metafields[i] ,
				id:'eme_dyndata_'+newId+metafields[i] ,
				value:'' 
		   });
		}
		//Insert into end of file
		jQuery('#eme_dyndata_body').append(metaCopy);
		//Duplicate the last entry, remove values and rename id
	});
	
	jQuery('#eme_dyndata_body a').click( function(event) {
		event.preventDefault();
		//Only remove if there's more than 1 meta tag
		if(jQuery('#eme_dyndata_body').children().length > 1){
			//Remove the item
			jQuery(jQuery(this).parent().parent().get(0)).remove();
			//Renumber all the items
			jQuery('#eme_dyndata_body').children().each( function(i){
				metaCopy = jQuery(this);
				oldId = metaCopy.attr('id').replace('eme_dyndata_','');
				newId = i+1;
				metaCopy.attr('id', 'eme_dyndata_'+newId);
				metaCopy.find('a').attr('rel', newId);
				var metafields=['_field','_condition','_condval','_template_id_header','_template_id','_template_id_footer','_repeat','_grouping'];
				var arrayLength = metafields.length;
				for (var i = 0; i < arrayLength; i++) {
					metaCopy.find('[name=eme_dyndata_'+ oldId +metafields[i]+']').attr({name: 'eme_dyndata_'+newId+metafields[i],id:'eme_dyndata_'+newId+metafields[i]});
				}
			});
		} else {
			metaCopy = jQuery(jQuery(this).parent().parent().get(0));
			var metafields=['_field','_condition','_condval','_template_id_header','_template_id','_template_id_footer','_repeat','_grouping'];
			var arrayLength = metafields.length;
			for (var i = 0; i < arrayLength; i++) {
				metaCopy.find('[name=eme_dyndata_1'+metafields[i]+']').attr('value', '');
				metaCopy.find('[name=eme_dyndata_1'+metafields[i]+']').prop('required',false);
			}
		}
	});

	jQuery(".showhidebutton").click(function () {
		var elname= jQuery(this).data( "showhide" );
		jQuery("#"+elname).toggle();
        });

    $('.eme_select2_members_class').select2({
	    width: '100%',
	    ajax: {
		    url: ajaxurl+'?action=eme_members_select2',
		    dataType: 'json',
		    delay: 1000,
		    data: function (params) {
			    return {
				    q: params.term, // search term
				    page: params.page,
				    pagesize: 30,
				    eme_admin_nonce: emeadmin.translate_membernonce
			    };
		    },
		    processResults: function (data, params) {
			    // parse the results into the format expected by Select2
			    // since we are using custom formatting functions we do not need to
			    // alter the remote JSON data, except to indicate that infinite
			    // scrolling can be used
			    params.page = params.page || 1;
			    return {
				    results: data.Records,
				    pagination: {
					    more: (params.page * 30) < data.TotalRecordCount
				    }
			    };
		    },
		    cache: true
	    },
	    placeholder: emeadmin.translate_selectmembers
    });
    $('.eme_select2_people_class').select2({
	    width: '100%',
	    ajax: {
		    url: ajaxurl+'?action=eme_people_select2',
		    dataType: 'json',
		    delay: 1000,
		    data: function (params) {
			    return {
				    q: params.term, // search term
				    page: params.page,
				    pagesize: 30,
				    eme_admin_nonce: emeadmin.translate_peoplenonce
			    };
		    },
		    processResults: function (data, params) {
			    // parse the results into the format expected by Select2
			    // since we are using custom formatting functions we do not need to
			    // alter the remote JSON data, except to indicate that infinite
			    // scrolling can be used
			    params.page = params.page || 1;
			    return {
				    results: data.Records,
				    pagination: {
					    more: (params.page * 30) < data.TotalRecordCount
				    }
			    };
		    },
		    cache: true
	    },
	    placeholder: emeadmin.translate_selectpersons
    });

});

   // the next is a Jtable CSV export function
   function jtable_csv(container) {
      // create a copy to avoid messing with visual layout
      var newTable = jQuery(container).clone();

      // fix HTML table

      // th - remove attributes and header divs from jTable
      newTable.find('th').each(function (pos, el) {
         val = jQuery(this).find('.jtable-column-header-text').text();
         jQuery(this).html(val);
         jQuery(this).removeAttr('width');
         jQuery(this).removeAttr('class');
         jQuery(this).removeAttr('style');
      });

      // tr - remove attributes
      newTable.find('tr').each(function (pos, el) {
         jQuery(this).removeAttr('class');
         jQuery(this).removeAttr('style');
         jQuery(this).removeAttr('width');
         jQuery(this).removeAttr('title');
         jQuery(this).removeAttr('data-record-key');
      });

      // td - remove attributes, images and buttons
      newTable.find('td').each(function (pos, el) {
         jQuery(this).removeAttr('class');
         jQuery(this).removeAttr('style');
         jQuery(this).removeAttr('width');
         if (jQuery(this).find('img').length > 0)
           jQuery(this).html("");
         if (jQuery(this).find('button').length > 0)
           jQuery(this).html("");
         // to make sure: we convert html to text
         val = jQuery(this).text();
         jQuery(this).html(val);
      });

      // fix incompatible HTML (th to td, white spaces)
      var table = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Worksheet</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>' + newTable.find('thead').html().replace(/th/g, 'td') + newTable.find('tbody').html() + '</table></body></html>';
      table = base64_encode(table.replace(/ /g, ' ')); // deal with spaces and encode base64
      //console.log(table); // debugging
      window.open('data:application/vnd.ms-excel;base64,' + table);
   }

