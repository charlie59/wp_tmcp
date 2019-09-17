=== Events Made Easy ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, memberships, locations, booking, calendar, maps, paypal, rsvp  
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 2.0.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage and display events, memberships, recurring events, locations and maps, widgets, RSVP, ICAL and RSS feeds, payment gateways support. SEO compatible.
             
== Description ==

Events Made Easy is a full-featured event and membership management solution for Wordpress. Events Made Easy supports public, private, draft and recurring events, membership and locations management, RSVP (+ optional approval), Paypal, 2Checkout, FirstData and Google maps. With Events Made Easy you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Made Easy. 

Main features:
* public, private, draft and recurring events with custom and dynamic fields in the RSVP form
* membership management with custom and dynamic fields
* People and groups with custom fields per person
* PDF creation for membership, bookings and people info
* RSS and ICAL feeds
* Calendar management, with holidays integration
* Several widgets for event listings and calendar
* location management, with optional Google Maps integration
* RSVP bookings with custom fields and dynamic fields, payment tracking, optional approval, discounts
* Templating for mails, event lists, single events, feeds, RSVP forms, ... with specific placeholders for each
* Lots of shortcodes and options
* payment gateways: Paypal, FirstData, 2CheckOut, Mollie, Worldpay, Sagepay, Stripe, Braintree
* Send mails to registered people, automatically send reminders for payments
* Mail queueing and newsletter functionality
* Mailings can be planned in the future, cancelled ...
* Multi-site compatible
* Fully localisable and already partially localised in Italian, Spanish, German, Swedish, French and Dutch. Also fully compatible with qtranslate (and mqtranslate): most of the settings allow for language tags so you can show your events in different languages to different people. The booking mails also take the choosen language into account.

For more information, documentation and support forum visit the [Official site](http://www.e-dynamics.be/wordpress/) .

== Installation ==

Always take a backup of your db before doing the upgrade, just in case ...  
1. Upload the `events-made-easy` folder to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Add events list or calendars following the instructions in the Usage section.  

= Usage =

After the installation, Events Made Easy add a top level "Events" menu to your Wordpress Administration.

*  The *Events* page lets you edit or delete the events. The *Add new* page lets you insert a new event.  
	In the event edit page you can specify the number of spaces available for your event. You just need to turn on RSVP for the event and specify the spaces available in the right sidebar box.  
	When a visitor responds to your events, the box sill show you his reservation. You can remove reservation by clicking on the *x* button or view the respondents data in a printable page.
	You can also specify the category the event is in, if you activated the Categories support in the Settings page.  
	Also fine grained control of the RSVP mails and the event layout are possible here, if the defaults you configured in the Settings page are not ok for this specific event.  
*  The *Locations* page lets you add, delete and edit locations directly. Locations are automatically added with events if not present, but this interface lets you customise your locations data and add a picture. 
*  The *Categories* page lets you add, delete and edit categories (if Categories are activated in the Settings page). 
*  The *People* page serves as a gathering point for the information about the people who reserved a space in your events. 
*  The *Pending approvals* page is used to manage registrations/bookings for events that require approval 
*  The *Change registration* page is used to change bookings for events 
*  The *CRON* page is used to plan automated EME tasks (like sending reminders, cancel unpaid registrations, newsletter)
*  The *Settings* page allows a fine-grained control over the plugin. Here you can set the [format](#formatting-events) of events in the Events page.
*  Access control is in place for managing events and such: 
        - a user with role "Editor" can do anything 
        - with role "Author" you can only add events or edit existing events for which you are the author or the contact person 
        - with role "Contributor" you can only add events *in draft* or edit existing events for which you are the author or the contact person 

Events list and calendars can be added to your blogs through widgets, shortcodes and template tags. See the full documentation at the [Events Made Easy Support Page](http://www.e-dynamics.be/wordpress/).
 
== Frequently Asked Questions ==

See the FAQ section at [the documentation site](http://www.e-dynamics.be/wordpress).

== Changelog ==

= 2.0.10 (2017/12/06) =
* Allow more HTML5 formfield types
* Handle some datetime exceptions more gracefully for membership status calculation
* Fix so event authors can edit own events again
* Fix for template parsing (added a possible newline too much at the end)
* Make location autocomplete for events more predictable

= 2.0.9 (2017/12/02) =
* Show category slug (SEO permalink) and allow to change it
* Make mailing reports usefull again
* Add bulk action to resend mail to pending members
* Updating dynamic fields for members was not working
* Remove the deprecated 'fields' option for the filterform shortcode, making it work more logically
* Cron tip
  In crontab: */5 * * * * php -q /path/to/wordpress/wp-cron.php >/dev/null 2>&1
  And in wp-config.php: define('DISABLE_WP_CRON', true);

= 2.0.8 (2017/11/24) =
* Add "Send email" mass action to rsvp-list
* Fix for dynamic pricing field
* Delete answers not linked to anything when updating members/people/rsvp
* Fix for replacement of booking/attendee placeholders in the EME header/footer settings for bookings/attendees
* Fix for mail to all people
* Initial support for GDPR (General Data Protection Regulation, [eme_gdpr] shortcode)

= 2.0.7 (2017/11/17) =
* Adding/editing a membership was not saving its properties
* The displayed start/end date for new members was not correct when signing them up in the backend interface

= 2.0.6 (2017/11/13) =
* Allow shortcodes in the rsvp and member forms (allows you to use conditional tags and e.g. only show certain fields in the admin section)
  For this #_IS_ADMIN and #_IS_LOGGED_IN now work in member forms
* Better handling of answers (update when needed), to alleviate the pressure on the answers table
* Integrate better with wordpress images, so:
  - for people, we can use #_IMAGETITLE, #_IMAGEDESCRIPTION, #_IMAGEALT, #_IMAGECAPTION (the 4 properties you can set for a wordpress image)
  - for events, we can use #_EVENTIMAGETITLE, #_EVENTIMAGEDESCRIPTION, #_EVENTIMAGEALT, #_EVENTIMAGECAPTION (the 4 properties you can set for a wordpress image)
  - for locations, we can use #_LOCATIONIMAGETITLE, #_LOCATIONIMAGEDESCRIPTION, #_LOCATIONIMAGEALT, #_LOCATIONIMAGECAPTION (the 4 properties you can set for a wordpress image)
* Fix for inserting persons in the database (was causing a WP-prepare error)

= 2.0.5 (2017/11/12) =
* Show more error messages on screen to catch php issues for people ...
* Make global map work again
* Simple search added in mailing reports
* Person image column deleted and moved to properties, this allows more person properties in the future without needing to change the db
* Added title/description to person image, together with the placeholders #_IMAGETITLE, #_IMAGEDESCRIPTION
* Removing a person from the last group he was in was not working
* Removed the option "Add payment id to return page info" (added by default now) and as a result simplified the code for custom return/error pages for bookings (works for multi-bookings too now)
* Small interface fix for the membership and forever-period (to show an empty end-date and not the date of today)

= 2.0.4 (2017/11/10) =
* Add #_FIELDCOUNTER and #_FIELDGROUPINDEX for dynamic field formfields, so you can indicate the number used
* Small bugfix to make "Show all bookings" link work as expected again
* The mailing report showed all mails sent, not the one from the selected mailing
* Allow individual mails from the report to be reused
* Allow people to have a picture
* Add shortcode [eme_people] with option group_id (to show a specific group) and template_id (for the formatting)
  All people placeholders can be used in the format
* Added extra people placeholders #_IMAGE, #_IMAGEURL, #_IMAGETHUMB, #_IMAGETHUMBURL
                                  #_IMAGETHUMB{xx} and #_IMAGETHUMBURL{xx} ('xx' being a thumbnail format)

= 2.0.3 (2017/11/09) =
* Default ordering for people was not correct (should be lastname/firstname)
* The filtering fields for the people search form is now limited to those of fields with purpose "people"
* Corrected the startdate calculation for members
* The mailings tab is now ajaxified, showing correct results all the time without needing to refresh the page
* PDF output for people was no longer working due to typo

= 2.0.2 (2017/11/08) =
* Some mobile screen improvements in the admin
* Bugfix for answers table (was not storing answers for fresh EME installations)

= 2.0.1 (2017/11/05) =
* Allow some basic membership placeholders to be used in the memberform: #_MEMBERSHIPNAME, #_MEMBERSHIPDESCRIPTION, #_MEMBERSHIPPRICE
* New actions in people and members: send mail to selected rows
* Mailing can now be reused too
* Layout improvements for selecting people, groups and defining a mailing
* Bugfix: on new installations the answers and queue tables were not created due to a typo

= 2.0.0 (2017/11/03) =
Because of new membership functionality, this is a major update.
* INCOMPATIBILITY: Dynamic field conditions are now much more flexible, but not backwards compatible!
* "Add event" submenu is now part of "Events"
* Allow mails to be sent concerning multiple events
* Revamp the send-mails, holidays, categories, templates and formfields screens
* Form fields with multiple choice can now have different values in the backend as in the frontend
* Groups added, and the possibility to send mails to groups
* Mailings can now be planned, making EME into a very nice mailing system
* Custom fields are now possible for people (even with the possibility to conditionally show them for a specific group), and FIELDVALUE can be used in people placeholders now
* Add custom formfield type Date (JS) ==> to show a datefield via jquery
* Add custom formfield type Dropdown (Multi) ==> to show a dropdown multi-select field
* Membership functionality added
  Member placeholders: see doc
  Membership form placeholders: see doc
  Membership form shortcode eme_add_member_form: see doc
* PDF can be generated for both members and bookings (based on a choosen template with new type 'PDF') 
* API search function eme_wordpress_search_events can now take an argument "scope" (values=past/future/all, default=future)
* More visual feedback in the rsvp admin section when executing actions
* Added option to change the "bookings no longer allowed on this date" text
* Also check for free available seats when updating a booking in the backend
* Show dyndata in CSV export
* Allow "month=next_month" in the calendar shortcode
* Add new people placeholder #_MASSMAIL 
* Updated Stripe API to 5.5.0
* Bugfix: when someone cancelled a booking from the frontend, no mail was sent to the contact person
* Bugfix: #_FIELD was not working anymore in the multibooking rsvp form
* Bugfix: template type 'All' was not working as expected
* Cron tip
  In crontab: */5 * * * * php -q /path/to/wordpress/wp-cron.php >/dev/null 2>&1
  And in wp-config.php: define('DISABLE_WP_CRON', true);

Older changes can be found in changelog.txt
