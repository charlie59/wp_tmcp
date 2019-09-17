          function loadMap(loc_name, address1, address2, city, state, zip, country) {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var scrollwheel = (emeadmin.translate_eme_gmap_zooming === "true");
            var myOptions = {
               zoom: 13,
               center: latlng,
               scrollwheel: scrollwheel,
               disableDoubleClickZoom: true,
               mapTypeControlOptions: {
                  mapTypeIds:[google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
               },
               mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            jQuery("#eme-admin-location-map").show();
            var map = new google.maps.Map(document.getElementById("eme-admin-location-map"), myOptions);
            var geocoder = new google.maps.Geocoder();
	    var searchKey_arr = [];
	    if (address1) {
		    searchKey_arr.push(address1);
	    }
	    if (address2) {
		    searchKey_arr.push(address2);
	    }
	    if (city) {
		    searchKey_arr.push(city);
	    }
	    if (state) {
		    searchKey_arr.push(state);
	    }
	    if (zip) {
		    searchKey_arr.push(zip);
	    }
	    if (country) {
		    searchKey_arr.push(country);
	    }
            searchKey = searchKey_arr.join(", ");
	    if (!searchKey) {
		    searchKey=loc_name;
	    }
            var lang = emeadmin.translate_lang;
            var lang_trans_function = emeadmin.translate_js_trans_function;
            if (lang!='' && typeof(lang_trans_function)=='function' ) {
               loc_name=window[lang_js_trans_function](lang,loc_name);
            }
               
            if (searchKey) {
               geocoder.geocode( { 'address': searchKey}, function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                     map.setCenter(results[0].geometry.location);
                     var marker = new google.maps.Marker({
                        map: map, 
                        position: results[0].geometry.location
                     });
                     var infowindow = new google.maps.InfoWindow({
                        content: '<div class=\"eme-location-balloon\"><strong>' + loc_name +'</strong><p>' + address1 + ' ' + address2 + '<br />' + city + ' ' + state + ' ' + zip + ' ' + country + '</p></div>'
                     });
                     infowindow.open(map,marker);
                     jQuery('input#location_latitude').val(results[0].geometry.location.lat());
                     jQuery('input#location_longitude').val(results[0].geometry.location.lng());
                     jQuery("#eme-admin-location-map").show();
                     jQuery('#eme-admin-map-not-found').hide();
                  } else {
                     jQuery("#eme-admin-location-map").hide();
                     jQuery('#eme-admin-map-not-found').show();
                  }
               });
            } else {
               jQuery("#eme-admin-location-map").hide();
               jQuery('#eme-admin-map-not-found').show();
            }
         }
      
         function loadMapLatLong(loc_name, address1, address2, city, state, zip, country, lat, lng) {
            if (lat === undefined) {
               lat = 0;
            }
            if (lng === undefined) {
               lng = 0;
            }
            var lang = emeadmin.translate_lang;
            var lang_trans_function = emeadmin.translate_js_trans_function;
            if (lang!='' && typeof(lang_trans_function)=='function' ) {
               loc_name=window[lang_js_trans_function](lang,loc_name);
            }
               
            if (lat != 0 && lng != 0) {
               var latlng = new google.maps.LatLng(lat, lng);
               var scrollwheel = (emeadmin.translate_eme_gmap_zooming === "true");

               var myOptions = {
                  zoom: 13,
                  center: latlng,
                  scrollwheel: scrollwheel,
                  disableDoubleClickZoom: true,
                  mapTypeControlOptions: {
                     mapTypeIds:[google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
                  },
                  mapTypeId: google.maps.MapTypeId.ROADMAP
               }
               var map = new google.maps.Map(document.getElementById("eme-admin-location-map"), myOptions);
               var marker = new google.maps.Marker({
                  map: map, 
                  position: latlng
               });
               var infowindow = new google.maps.InfoWindow({
                  content: '<div class=\"eme-location-balloon\"><strong>' + loc_name +'</strong><p>' + address1 + ' ' + address2 + '<br />' + city + ' ' + state + ' ' + zip + ' ' + country + '</p></div>'
               });
               infowindow.open(map,marker);
               jQuery("#eme-admin-location-map").show();
               jQuery('#eme-admin-map-not-found').hide();
            } else {
               loadMap(loc_name, address1, address2, city, state, zip, country);
            }
         }
 
         function eme_displayAddress(ignore_coord){
            var gmap_enabled = (emeadmin.translate_eme_gmap_is_active === "true");
            if (gmap_enabled && jQuery("input[name=location_name]").length) {
               eventLocation = jQuery("input[name=location_name]").val() || "";
               eventAddress1 = jQuery("input#location_address1").val() || "";
               eventAddress2 = jQuery("input#location_address2").val() || "";
               eventCity = jQuery("input#location_city").val() || "";
               eventState = jQuery("input#location_state").val() || "";
               eventZip = jQuery("input#location_zip").val() || "";
               eventCountry = jQuery("input#location_country").val() || "";
               if (ignore_coord) {
                  eventLat = 0;
                  eventLong = 0;
               } else {
                  eventLat = jQuery("input#location_latitude").val() || 0;
                  eventLong = jQuery("input#location_longitude").val() || 0;
               }
               loadMapLatLong(eventLocation, eventAddress1, eventAddress2,eventCity,eventState,eventZip,eventCountry, eventLat,eventLong);
            }
         }

         function eme_SelectdisplayAddress(){
            var gmap_enabled = (emeadmin.translate_eme_gmap_is_active === "true");
            if (gmap_enabled && jQuery("input[name='location-select-name']").length) {
               eventLocation = jQuery("input[name='location-select-name']").val() || "";
               eventAddress1 = jQuery("input[name='location-select-address1']").val() || "";
               eventAddress2 = jQuery("input[name='location-select-address2']").val() || "";
               eventCity = jQuery("input[name='location-select-city']").val() || "";
               eventState = jQuery("input[name='location-select-state']").val() || "";
               eventZip = jQuery("input[name='location-select-zip']").val() || "";
               eventCountry = jQuery("input[name='location-select-country']").val() || "";
               eventLat = jQuery("input[name='location-select-latitude']").val() || 0;
               eventLong = jQuery("input[name='location-select-longitude']").val() || 0;
               loadMapLatLong(eventLocation, eventAddress1, eventAddress2,eventCity,eventState,eventZip,eventCountry, eventLat,eventLong);
            }
         }

         jQuery(document).ready(function() {
            jQuery("#eme-admin-location-map").hide();
            jQuery('#eme-admin-map-not-found').show();
            eme_displayAddress(0);
            jQuery("input[name='location_name']").change(function(){
               eme_displayAddress(0);
            });
            jQuery("input#location_city").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_state").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_zip").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_country").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_address1").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_address2").change(function(){
               eme_displayAddress(1);
            });
            jQuery("input#location_latitude").change(function(){
               eme_displayAddress(0);
            });
            jQuery("input#location_longitude").change(function(){
               eme_displayAddress(0);
            });
         }); 
