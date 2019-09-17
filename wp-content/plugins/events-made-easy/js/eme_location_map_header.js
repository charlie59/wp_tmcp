jQuery(document.body).unload(function() {
	GUnload();
});

jQuery(document).ready(function() {
	// we use the listeners to resize stuff also for responsive themes
        // but resize is triggered on scrolldown/up too, so we don't use it
	// google.maps.event.addDomListener(window, 'resize', resizeGMap);
	google.maps.event.addDomListener(window, 'load', loadGMap);
});
