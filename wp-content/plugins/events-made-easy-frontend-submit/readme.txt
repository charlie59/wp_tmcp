=== Events Made Easy Frontend Submit ===
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, frontend
Requires at least: 3.9
Tested up to: 4.9
Stable tag: 1.0.28
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin that displays a form to allow people to enter events for the Events Made Easy plugin on a regular wordpress page.

== Description ==

A simple plugin that displays a form to allow people to enter events for the Events Made Easy plugin on a regular wordpress page (called "Frontend Submit").

Get The Events Made Easy plugin:
http://wordpress.org/extend/plugins/events-made-easy/

== Installation ==

1. Download the plugin archive and expand it
2. Upload the events-made-easy-frontend-submit folder to your /wp-content/plugins/ directory
3. Go to the plugins page and click 'Activate' for EME Frontend Submit
4. Navigate to the Settings section within Wordpress and change the settings appropriately.
5. Ensure the Events Made Easy plugin is installed and configured - http://wordpress.org/extend/plugins/events-made-easy/
6. Put the shortcode [emefs_submit_event_form] on a regular page to display the form

== Frequently asked questions ==

= How/where do I change the form layout? =

The plugin will look for form template and style files in the following paths, in that priority:

   1. WP UPLOAD DIR/events-made-easy-frontend-submit/
   2. ./wp-content/themes/your-current-theme/eme-frontend-submit/
   3. ./wp-content/themes/your-current-theme/events-made-easy-frontend-submit/
   4. ./wp-content/themes/your-current-theme/emefs/
   5. ./wp-content/themes/your-current-theme/events-made-easy/

The overloadable files at this moment are:

   1. form.php which controls the html form. The default version can be found in the templates subdir.
   2. style.css which controls the style loaded automatically by the plugin. The default version can be found in the templates subdir.

To allow for multiple forms to be used, you can add an option to the shortcode to indicate the name of the form template (default: form.php).
Example: [emefs_submit_event_form template=test.php]
If your template is not found in one of the 4 locations mentioned above, the default templates/form.php will be taken

= What fields can I use in the form? =

As shown in the templates examples, you call EMEFS::field('xxx') with 'xxx' being one of:
event_name
event_category_ids
event_start_date
event_start_time
event_end_date
event_end_time
event_notes
event_contactperson_email_body
event_url
event_image_url
location_name
location_address1
location_address2
location_city
location_state
location_zip
location_country
location_latitude
location_longitude

If you activated the option to use the captcha, you can also use the field 'captcha'

= How about extra attributes? =

Well, in fact it is easier than thought. Just by using
EMEFS::attribute('phone2')
in the form template, the attribute phone2 is available and can be used in an event template via #_ATT{phone2}
By default, the formtype for attributes is textarea, if you want a plain text field, use e.g.:
EMEFS::attribute('phone2','text')

= How about extra event properties =
Many newer event options are stored in properties, see eme_events.php for a whole list.
A small enumeration:
auto_approve (*)
ignore_pending (*)
all_day (*)
take_attendance (*)
min_allowed
max_allowed
rsvp_end_target
rsvp_discount
rsvp_discountgroup
use_worldpay (*)
use_stripe (*)
use_braintree (*)

You can use these like this: EMEFS::property('xxx');
For the ones marked with (*), use the extra binary option: EMEFS::property('xxx','binary');

= Any html5 things? =

Well yes: EMEFS::attribute and EMEFS::field can have a second argument, indicating the html5-type of your field of choice:
Example: <?php EMEFS::attribute('extra1','url'); ?>
Example: <?php EMEFS::field('price','number'); ?>
The different types supported: textarea (default), text, tel, url, email and number
Also the event_url regular field entry can have the url-html5 option: <?php EMEFS::field('event_url','url');

= Binary field (YES/NO) =
If you want to show a certain field as a drop/down select, use as second option (for type) the value 'binary'
E.g. EMEFS::property('all_day','binary')

= Required fields =
If you want a certain field to be required (if not by default), use EMEFS::required_field('xxx')
For required attributes, use EMEFS::required_attribute('xxx')
For required properties, use EMEFS::required_property('xxx')

== Changelog ==

= 1.0.28 (2017/12/06) =
* Autocomplete location now works more predictable

= 1.0.27 (2017/11/22) =
* Fix for attributes with type textarea

= 1.0.26 (2017/08/26) =
* Make the default cat actually work if nothing is selected
* Make it an option to always return to the success page upon submit

= 1.0.25 (2017/08/15) =
* Add number as a hmlt5-type
* Allow event_category_ids to be a required field too
* Add the possibilty for a default category for new events

= 1.0.24 (2017/07/08) =
* Fix for datepick in the form

= 1.0.23 (2017/06/24) =
* By default frontend-attributes are textarea fields now. Use the second option to indicate just "text" if you need it
* Location autocomplete now works using standard wordpress ajax calls

= 1.0.22 (2017/06/09) =
* Add the possibility to allow image upload in the frontend wysiwyg editor
* Also allow the field event_image_url

= 1.0.21 (2017/01/29) =
* checking the default template for captcha info resulted in a not-found error

= 1.0.20 (2017/01/29) =
* event_notes is no longer a required field
* show error if captcha required and not used or visa versa

= 1.0.19 (2016/12/16) =
* Another javascript bugfix, so for new locations the google map lookup works ok too

= 1.0.18 (2016/12/15) =
* Javascript bugfix, preventing a correct calendar date to be transferred

= 1.0.17 (2016/12/10) =
* Allow all event properties to be used
* Introduce easy method for required fields
* EME events now support city, zip and country, so this plugin needed an update too
  For this version, you need at least EME version 1.7.8

= 1.0.16 (2016/10/14) =
* Changed text domain to "events-made-easy-frontend-submit" to be compliant with wp translation rules
* Allow the style.css and template files to be in a fixed uploads dir, so you don't have to worry about theme/plugin updates
  The upload dir should be called "events-made-easy-frontend-submit"

= 1.0.15 (2016/08/17) =
* Support frontend wysiwyg editor (new setting) for event notes
* Support google map api key (new setting) if/when needed
* Make sure network activation works ok
* Added binary yes/no selection for event_rsvp

= 1.0.14 (2016/02/24) =
* Extra attributes defined were not being taken into account

= 1.0.13 (2016/02/01) =
* Bugfix in the event validation prevented event submission

= 1.0.12 (2016/01/31) =
* Feature: now you can also define extra attributes in form.php. Example to define attribute with name extra1:
  <?php EMEFS::attribute('extra1'); ?>
  You can also define input types for these (default text, other html5 options: tel,url,email). Example:
  <?php EMEFS::attribute('extra1','url'); ?>
  And if you want to define extra html attributes for a field (e.g. pattern definitions for the html5 phone):
  <?php EMEFS::attribute('extra1','tel',"pattern='[\+]\d{2}[\(]\d{2}[\)]\d{4}[\-]\d{4}' title='Phone Number (Format: +99(99)9999-9999)'"); ?>
  See http://www.htmlgoodies.com/html5/tutorials/whats-new-in-html5-forms-email-url-and-telephone-input-types.html
  The url-html5 option is also possible for the event_url regular field entry:
  <?php EMEFS::field('event_url','url');
* Feature: added captcha field
* Feature: added option "template" to the shortcode, so you can use different templates for different type of events:
  [emefs_submit_event_form template=form2.php]
* Bugfix: fix yet another timezone problem

= 1.0.11 (2015/09/15) =
* Bugfix: fix timezone problem

= 1.0.10 (2015/09/15) =
* Bugfix: correct renamed function call eme_get_identical_location_id

= 1.0.9 (2015/07/31) =
* Feature: allow to enable/disable gmap integration
* Feature: allow category disable too

= 1.0.8 (2015/07/01) =
* Bugfix: only require the use of categories if those are enabled in EME
* Bugfix: do the autocompletion for the location only if the location field is actually there
* Updated translation template file (emefs.pot)

= 1.0.7 =
* Bugfix: fix location autocomplete

= 1.0.6 =
* Bugfix: the event author was not being set correctly for logged in users

= 1.0.5 =
* Bugfix: fix javascript comment preventing it to work in emefs.js

= 1.0.4 =
* Bugfix: first day of the week needs to be an integer in the javascript code, otherwise the calendar day headers are mangled

= 1.0.3 =
* Feature: first day of week is now also respected in the datepicker
* Bugfix: fix the error "Unknown column 'localized-start-date' in 'field list'" by redoing a big part of the code

= 1.0.2 =
* Feature: added an option for the location to be always created, even if the user does not have the needed capability set in EME to create locations
* Feature: localize event date too
* Improvement: show map for new locations too
* Improvement: show entered data if the form has an error
* Bugfix: time entry fields shouldn't be read-only
* Bugfix: form.php wasn't localized correctly
* Bugfix: better 24h timeformat notation detection

= 1.0.1 =
* Bugfix: better EME dependency checks, also work for multisite now

= 1.0.0 =
Released as seperate wordpress plugin, using it's own WP settings (no config file anymore)
