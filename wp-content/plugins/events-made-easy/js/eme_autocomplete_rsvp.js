if (typeof(htmlDecode) !== typeof(Function)) {
   function htmlDecode(value){ 
      return jQuery('<div/>').html(value).text(); 
   }
}

function eme_registrations_autocomplete () {
    // for autocomplete to work, the element needs to exist, otherwise JS errors occur
    // we check for that using length
    if (jQuery("input[name=lastname]").length) {
        jQuery("input[name=lastname]").autocomplete({
            source: function(request, response) {
     			alldata = jQuery("input[name=lastname]").parents('form:first').serializeArray();
      			alldata.push({name: 'eme_ajax_action', value: 'rsvp_autocomplete_people'});
      			jQuery.post(self.location.href, alldata,
                                  function(data){
                                                response(jQuery.map(data, function(item) {
                                                      return {
                                                         lastname: htmlDecode(item.lastname),
                                                         firstname: htmlDecode(item.firstname),
                                                         address1: htmlDecode(item.address1),
                                                         address2: htmlDecode(item.address2),
                                                         city: htmlDecode(item.city),
                                                         state: htmlDecode(item.state),
                                                         zip: htmlDecode(item.zip),
                                                         country: htmlDecode(item.country),
                                                         wp_id: item.wp_id,
                                                         email: item.email,
                                                         phone: item.phone
                                                      };
                                                }));
                                  }, "json");
                    },
            select:function(evt, ui) {
                         // when a person is selected, populate related fields in this form
                         jQuery('input[name=lastname]').val(ui.item.lastname);
                         jQuery('input[name=firstname]').val(ui.item.firstname);
                         jQuery('input[name=address1]').val(ui.item.address1);
                         jQuery('input[name=address2]').val(ui.item.address2);
                         jQuery('input[name=city]').val(ui.item.city);
                         jQuery('input[name=state]').val(ui.item.state);
                         jQuery('input[name=zip]').val(ui.item.zip);
                         jQuery('input[name=country]').val(ui.item.country);
                         jQuery('input[name=email]').val(ui.item.email);
                         jQuery('input[name=phone]').val(ui.item.phone);
                         jQuery('input[name=wp_id]').val(ui.item.wp_id);
                         return false;
                   },
            minLength: 2
          }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
            return jQuery( "<li></li>" )
            .append("<a><strong>"+htmlDecode(item.lastname)+' '+htmlDecode(item.firstname)+'</strong><br /><small>'+htmlDecode(item.email)+' - '+htmlDecode(item.phone)+ '</small></a>')
            .appendTo( ul );
          };
    }
}

jQuery(document).ready( function() {
   eme_registrations_autocomplete();
});
