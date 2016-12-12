=== Custom Contact Forms ===
Contributors: tlovett1
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HR34W94MM53RQ
Tags: contact form, web form, custom contact form, custom forms, captcha form, contact fields, form mailers, forms
Requires at least: 3.9
Tested up to: 4.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build beautiful custom forms and manage submissions the WordPress way. View live previews of your forms while you build them. Create powerful and exstensible forms for simple contact inquiries, sign ups, subscriptions, events, payments, etc.

== Description ==

Custom Contact Forms enables you to build forms and manage submissions the WordPress way. User experience is at the top of the list for this plugin. Build forms in the media manager instead of going to separate areas of your site. Live previews of your forms are generated on the fly making your life much easier. Custom Contact Forms is a legacy name. The plugin is built for much more than just contact forms. Flexibility and extensible functionality allow you and your team to create forms to power subscriptions, payments, events, and more.

**Feature List (not exhaustive):**

* Create text fields, paragraph fields, first/last name fields, email fields (with optional confirmation), US address fields, international address fields, date fields (optional international format), time fields, checkbox fields, dropdown (optional multi-select), radio fields, hidden fields, file upload fields, phone fields (optional international number), website fields, and more.
* Add HTML and sections to your forms.
* Conditional fields and form sections
* Add help text, modify labels, mark required, add CSS classes, manage options, etc. for each of your form fields.
* Forms use AJAX so no page reloads are necessary. Stylish error messages are shown without page reload.
* All form submissions shown in an easy to view format within the WordPress admin.
* Export form submissions to CSV.
* Pre-setup for Twitter Bootstrap
* Multiple themes to choose from
* No custom database tables
* Easy form duplication
* Multiple types of CAPTCHAs for spam blocking
* Only show forms to logged in users
* Forms can show customizable completion text or redirect to a URL.
* Temporarily pause forms with a customizable pause message.
* Create and manage multiple notifications for each form. Notifications can send emails to one or more administrators or form submittors. Customize notification email subject, from email address, from email name, and email body. Map form fields to email subject, from name, and from email address. Easily activate and deactivate notifications.
* Create posts or custom post types when forms are submitted. For each form, you can configure the post type and status of the created post. You can also map form fields to post fields (as well as meta and taxonomies).
* View live previews for your forms. Live previews of your forms are shown in the post content. Make edits to forms and form fields without having to refresh the page in the media modal.
* Insert your forms in posts, custom post types, widgets, and themes.
* Customize form titles, submit button text, and form descriptions.
* Optionally only include CCF JavaScript and CSS on URLs that actually include forms improving page load times.
* Extensible code with many hooks and filters to allow for developer modifications.
* Translated in French, Chinese, German, and Danish. More translations on the way.
* Easily prevent spam with honey pots and [reCAPTCHA](https://www.google.com/recaptcha/intro/index.html).
* Import and export forms and form submissions with ease.
* Performant and scabable plugin built for enterprise.
* More!

For detailed install and usage instructions, please visit [Github](http://github.com/tlovett1/custom-contact-forms).

== Installation ==

Please refer to [Github](http://github.com/tlovett1/custom-contact-forms) for detailed installation instructions.

== Configuring and Using the Plugin ==

Please refer to [Github](http://github.com/tlovett1/custom-contact-forms) for detailed configuration instructions.

== Support ==

For full documentation, questions, feature requests, and support concerning the Custom Contact Forms plugin, please refer to [Github](http://github.com/tlovett1/custom-contact-forms).

== Changelog ==

= 7.8.3 =
* Fix WooCommerce conflict

= 7.8.2 = 
* Add $submission to ccf_email_subject filter, correct "Invalid Date" issue with datepicker. Props (quayzar)[https://github.com/quayzar]
* Fix WooCommerce conflict
* Add support for Customize Posts plugin. Props (westonruter)[https://github.com/westonruter]

= 7.8.1 =
* Cache busy form submission URL
* Improve field choice UI

= 7.8 =
* Hide form title setting
* Reply to notification fields
* Activate form notifications by default

= 7.7 =
* New CAPTCHA option
* Fix "0" choice input bug
* Fix empty conditional bug
* Reset field renderer bug fixed
* Guide user for whitelisting file extenions in file field
* Submit class form option
* Logged in users only form option

= 7.6 =
* Form duplication
* Fix multiple section header bug
* Button class field

= 7.5 =
* Conditional fields and sections
* [current_date_time] notification variable

= 7.4.5 =
* Fix CCF compat with API plugin

= 7.4.4 =
* HTTPS backend API bug fixed
* Fix European dates


= 7.4.3 =
* Fix notification email sending bug
* Allow empty email content notifications
* Set [all_fields] as default email notification content

= 7.4.2 =
* Fix notification and post field creation overlay issue
* Fix field, post field, and notification initialization
* Fix field remove post field update

= 7.4.1 =
* Fix form iframe onload bug

= 7.4 =
* Themes and Bootstrap support

= 7.3.2 =
* Fix field delete variable error message

= 7.3.1 =
* Add missing notification variables

= 7.3 =
* Add post creation functionality for when forms are submitted

= 7.2.3 =
* Fix non-English notification activation

= 7.2.2 =
* Fix asset restriciton adding bug in Firefox

= 7.2.2 =
* Fix asset restriciton adding bug in Firefox

= 7.2.1 =
* Fix API json url bug
* Update notification dialog

= 7.2 =
* Conditional asset loading

= 7.1 =
* Enable non-American date formats
* Make submissions and forms private so they don't show in sitemaps
* Redo form email notifications and settings UI

= 7.0.3 =
* Unhack API
* Use site_url() for API endpoints

= 7.0.2 =
* Bust script/style cache

= 7.0.1 =
* Fix CORS issue
* Add Chinese [davidabm](https://github.com/davidabm)

= 7.0 =
* Redo API integration for WordPress 4.4 improving stability
* Add `accept` attribute to file upload input. Props [tuamo](http://github.com/tuamo)
* Fix Chinese exporting. Props [davidabm](http://github.com/davidabm)

= 6.9.4 =
* WordPress list signup

= 6.9.3 =
* Ad removal

= 6.9.0 =
* Add Danish translation. Props [KasperLK](https://github.com/KasperLK)
* Wrap form completion message
* Fix jshint bugs
* Custom subject lines and user submitted subject lines

= 6.8.2 =
* Fix non-translateable strings
* Include French translation. Props [pyrog](https://github.com/pyrog)

= 6.8.1 =
* Tighten post type permissions so submissions and forms don't have archives and single views.

= 6.8 =
* Configurable "from" name field
* Minor variable stomping bug fix
* Successful/unsuccessful submission hooks

= 6.7.3 =
* Fix WP SEO conflict rooted in a rewrites bug

= 6.7.2 =
* Allow forms to be emptied
* Allow MCE previews to be re-rendered
* Fix spinner icon

= 6.7.1 =
* Fix MCE Previews that were broken in WP 4.2

= 6.7.0 =
* Ability to pause forms
* Emulate Backbone HTTP via filter instead of API hack

= 6.6.4 =
* Fix API setup so it doesn't conflict with WP API plugin
* Fix bug where export menu item was shown to non authorized users

= 6.6.3 =
* Fix form submission download bug

= 6.6.2 =
* Upgrade WP-API to 1.2.1

= 6.6.1 =
* Finally fix the bug where we can set our form title as empty

= 6.6 =
* Update WP-API to 1.2 (still have header method hack)
* Form CSV download

= 6.5.1 =
* Fix translatable field strings.

= 6.5 =
* Add import/export functionality

= 6.4.12 =
* Improved draggable interactions

= 6.4.11 =
* Fix title special character formatting

= 6.4.10 =
* Only use GET and POST HTTP methods. Remove 505 error message modal text.
* Add IP address to submission
* Fix phone number validator bug

= 6.4.9 =
* Don't cache backend endpoints. A useful fix for W3 Total Cache users.

= 6.4.8 =
* Use WP timezone for forms and submissions

= 6.4.7 =
* json_encode not wp_json_encode

= 6.4.6 =
* Add error modal text for HTTP 501 error code

= 6.4.5 =
* Add error modal to explain when the plugin is not working.

= 6.4.4 =
* Allow phone format to be saved
* Properly load reCAPTCHA in IE

= 6.4.3 =
* IE fix to prevent form downloading

= 6.4.2 =
* Fix IE bug where browser was prompting for download

= 6.4.1 =
* Fix ie8 bug where dragging a selected field broke the manager.
* Don't email field label for hidden fields

= 6.4 =
* File upload field
* Description field added to each field type
* Improved unit testing
* CCF widget

= 6.3.5 =
* Encode notification emails in UTF-8

= 6.3.4 =
* Re-add PHP function to server form via PHP
* Don't escape form title since it is already escaped
* Fix notice sent because of missing hidden field validator
* Remove overflow: auto from fields. Instead use clearing div

= 6.3.3 =
* Fix address bug where line two was required
* Add starting QUnit tests
* Fix performance bug where duplicate event listeners were being created in the form notifications panel
* Fix required field bug with checkbox fields
* Restructure unit test folder
* Add .jshintrc
* Clean up bower.json and composer.json

= 6.3.2 =
* Change underscores style templating to account for when ASP tags are turned on.

= 6.3.1 =
* Fix email confirm in from email submission sending

= 6.3 =
* Add form notifications tab
* Make from address for email notifications configurable
* Properly check if SCRIPT_DEBUG is defined
* Fix dropdown preview bug
* Properly notify user of duplicate slugs

= 6.2.3 =
* Fix form.min.js URL

= 6.2.2 =
* Properly conditionally enqueue JS/CSS with SCRIPT_DEBUG
* Make email message and subject filterable
* Make tinymce preview inclusion filterable

= 6.2.1 =
* Effectively calculate unique field slug
* Show site key and secret key as required for recaptcha
* Unit tests

= 6.2 =
* Add reCAPTCHA field
* Fix some localization of date field
* Fix spinner for non-root WP installs
* Strip slashes off of email field values

= 6.1.4 =
* Decode html entities on model sync. Escape entities on output.

= 6.1.3 =
* Fix form page bug

= 6.1.2 =
* Force JSON url to obey current protocol
* Enqueue scripts earlier

= 6.1.1 =
* Make sure to check for existence of legacy table before trying to read from it.

= 6.1.0 =
* Pre-6.0 upgrader added

= 6.0.3 =
* Make Google library URL protocol agnostic

= 6.0.2 =
* Create forms, fields, choices, and submissions under the proper user.

= 6.0.1 =
* Properly flush permalinks on activation
* Warn user if pretty permalinks is not enabled.

= 6.0 =
* Plugin completely rebuilt.

= 5.1.0.4 =
* Security fix

= 5.1.0.3 =
*   custom-contact-forms-front.php - $field_value properly escaped

= 5.1.0.1 =
*   custom-contact-forms-admin.php - Small UI updates
*   css/custom-contact-forms-admin.css - New admin styles

= 5.0.0.1 =
*	ishuman fixed field bug fixed
*	attach field bug fixed

= 5.0.0.0 =
*	Admin user interface improved 1000% with drag-and-drop fields as well as save/delete buttons.
*	Import bug fixed

= 4.8.0.0 =
*	js/jquery.tools.min.js - Updated to fix firefox tooltip bug

= 4.7.0.5 =
*	custom-contact-forms-front.php - Notice bugs fixed
*	custom-contact-forms.php - Notice bugs fixed
*	modules/db/custom-contact-forms-activate-db.php - Notice bugs fixed
*	modules/db/custom-contact-forms-db.php - Notice bugs fixed
*	modules/extra_fields/countries_field.php - Notice bugs fixed
*	modules/extra_fields/states_field.php - Notice bugs fixed
*	custom-contact-forms-admin.php - Notice bugs fixed, new language phrases added

= 4.7.0.4 =
*	custom-contact-forms-front.php - Language stuff changed

= 4.7.0.3 =
*	js/jquery.tools.js - Updated to not include jQuery
*	custom-contact-forms-front.php - jQuery bug fixed


= 4.7.0.1 =
*	custom-contact-forms-front.php - Look and feel changed
*	css/custom-contact-forms.css - Look and feel changed
*	js/custom-contact-forms-admin-ajax.js - IE detach field/field option bug fixed


= 4.7.0.0 =
*	All files have been changed!

= 4.6.0.1 =
*	custom-contact-forms-admin.php - -1 bug fixed in IE
*	js/jquery.form.js - Updated jquery forms plugin fixes huge IE bug

= 4.6.0.0 =
*	custom-contact-forms.php - Dependencies included differently, new general setting options
*	custom-contact-forms-admin.php - New field type (Date), guidelines inserted in to all pages, new general settings
*	modules/usage_popover/custom-contact-forms-usage-popover.php - New field type added
*	custom-contact-forms.php - Dependencies included differently, new field type added, JQuery files included differently
*	js/custom-contact-forms-datepicker.js - New file
*	js/jquery.ui.datepicker.js - New file



= 4.5.3.2 =
*	modules/widgets/custom-contact-forms-dashboard.php - Bugs fixed
*	custom-contact-forms-admin.php - Quick start guide added to general settings and form submissions.
*	custom-contact-forms.php - Dashboard widget security bug fixed.
*	modules/usage_popover/custom-contact-forms-quick-start-popover.php - Language changes made
*	modules/db/custom-contact-forms-db.php - Roles bug fixed

= 4.5.3.1 =
*	modules/widgets/custom-contact-forms-dashboard.php - Array shift bug fix

= 4.5.3.0 =
*	custom-contact-forms-admin.php - Dashboard widget security bug fixed. Now you can limit which users can see the dashboard widget. Also a quick start guide has been added.
*	custom-contact-forms.php - Dashboard widget security bug fixed.
*	modules/widgets/custom-contact-forms-dashboard.php - Dashboard widget security bug fixed. Now you can limit which users can see the dashboard widget.
*	modules/usage_popover/custom-contact-forms-usage-popover.php - Minor display changes made
*	modules/usage_popover/custom-contact-forms-quick-start-popover.php - Minor display changes made
*	js/custom-contact-forms-admin.js - Quick start guide added
*	css/custom-contact-forms-admin.css - Quick start guide added


= 4.5.2.2 =
*	custom-contact-forms.php - JQuery plugin conflict fixed

= 4.5.2.1 =
*	js/custom-contact-forms-admin-ajax.js - Save image bug fixed
*	custom-contact-forms-admin.php - Minor display change

= 4.5.2 =
*	custom-contact-forms.php - Template form display function fixed
*	custom-contact-forms-admin.php - jQuery dialog used for plugin usage popover
*	modules/db/custom-contact-forms-activate.php - Field options column changed to text
*	modules/widgets/custom-contact-forms-dashboard.php - jQuery dialog used for popovers
*	modules/widgets/custom-contact-forms-dashboard.css - jQuery dialog used for popovers

= 4.5.1.2 =
*	modules/widgets/custom-contact-forms-widget.php - Widget form display bug fixed

= 4.5.1.1 =
*	custom-contact-forms-admin.php - Display changes, form submissions non-ajax delete fixed


= 4.5.1 =
*	custom-contact-forms.php - enable_form_access_manager option added and defaulted to disabled
*	custom-contact-forms-admin.php - enable_form_access_manager option added and defaulted to disabled
*	custom-contact-forms-front.php - enable_form_access_manager option added and defaulted to disabled

= 4.5.0 =
*	custom-contact-forms.php - Saved form submissions manager, form background color added to style manager, import/export feature
*	custom-contact-forms-utils.php - Methods added/removed for efficiency
*	custom-contact-forms-admin.php - Admin code seperated in to a different file
*	custom-contact-forms-front.php - Admin code seperated in to a different file
*	modules/db/custom-contact-forms-db.php - DB methods reorganized for efficiency
*	modules/db/custom-contact-forms-activate-db.php - DB methods reorganized for efficiency
*	modules/db/custom-contact-forms-default-db.php - DB methods reorganized for efficiency
*	modules/usage-popover/custom-contact-forms-popover.php - Popover code seperated in to a different file
*	modules/export/custom-contact-forms-export.php - Functions for importing and exporting
*	modules/extra_fields/countries_field.php
*	modules/extra_fields/date_field.php
*	modules/extra_fields/states_field.php
*	modules/widget/custom-contact-forms-dashboard.php
*	css/custom-contact-forms-admin.css - AJAX abilities added
*	css/custom-contact-forms-standard.css - Classes renamed
*	css/custom-contact-forms.css - Classes renamed
*	css/custom-contact-forms-dashboard.css - Classes renamed
*	js/custom-contact-forms-dashboard.js - AJAX abilities added to admin panel
*	lang/custom-contact-forms.po - Allows for translation to different languages
*	lang/custom-contact-forms.mo - Allows for translation to different languages

= 4.0.9.2 =
*	css/custom-contact-forms-admin.css - Minor display changes
*	js/custom-contact-forms.js - JQuery conflict issue fixed

= 4.0.9.1 =
*	custom-contact-forms-admin.php - Minor display changes
*	css/custom-contact-forms-admin.css - Minor display changes to field options

= 4.0.9 =
*	js/custom-contact-forms.js - JQuery conflict issue fixed
*	js/custom-contact-forms-admin.js - JQuery conflict issue fixed
*	js/custom-contact-forms-admin-inc.js - JQuery conflict issue fixed
*	js/custom-contact-forms-admin-ajax.js - JQuery conflict issue fixed
*	custom-contact-forms-admin.php - JQuery conflict issue fixed
*	custom-contact-forms-front.php - Unnecessary JQuery dependencies removed

= 4.0.8.1 =
*	custom-contact-forms-admin.php - Email charset set to UTF-8
*	css/custom-contact-forms-admin.css - Usage Popover z-index set to 10000 and Usage button styled.
*	custom-contact-forms-front.php - Email charset set to UTF-8

= 4.0.8 =
*	custom-contact-forms-admin.php - Admin panel updated, WP_PLUGIN_URL to plugins_url()
*	custom-contact-forms-front.php - WP_PLUGIN_URL to plugins_url()

= 4.0.7 =
*	custom-contact-forms-admin.php - Admin panel updated

= 4.0.6 =
*	modules/widgets/custom-contact-forms-widget.php - Form title added via widget

= 4.0.5 =
*	modules/db/custom-contact-forms-db.php - Form email cutoff bug fixed

= 4.0.4 =
*	custom-contact-forms-admin.php - Bug reporting mail error fixed

= 4.0.3 =
*	custom-contact-forms-front.php - PHPMailer bug fixed, form redirect fixed
*	custom-contact-forms-static.php - Form redirect function added
*	custom-contact-forms-admin.php - redirects fixed, phpmailer bug fixed
*	widget/phpmailer - deleted
*	widget/db/custom-contact-forms-db.php - table charsets changed to UTF8

= 4.0.2 =
*	custom-contact-forms-front.php - Field instructions bug fixed
*	custom-contact-forms-admin.php - Display change

= 4.0.1 =
*	custom-contact-forms.php
*	custom-contact-forms-admin.php - support for multiple form destination emails added
*	custom-contact-forms-front.php - Mail bug fixed, email validation bug fixed
*	lang/custom-contact-forms.php - Phrases deleted/added


= 4.0.0 =
*	custom-contact-forms.php - Saved form submissions manager, form background color added to style manager, import/export feature
*	custom-contact-forms-user-data.php - Saved form submission
*	custom-contact-forms-db.php - DB methods reorganized for efficiency
*	custom-contact-forms-static.php - Methods added/removed for efficiency
*	custom-contact-forms-admin.php - Admin code seperated in to a different file
*	custom-contact-forms-popover.php - Popover code seperated in to a different file
*	custom-contact-forms-export.php - Functions for importing and exporting
*	css/custom-contact-forms-admin.css - AJAX abilities added
*	css/custom-contact-forms-standard.css - Classes renamed
*	js/custom-contact-forms-admin.js - AJAX abilities added to admin panel
*	download.php - Allows export file to be downloaded
*	lang/custom-contact-forms.po - Allows for translation to different languages
*	lang/custom-contact-forms.mo - Allows for translation to different languages

= 3.5.5 =
*	custom-contact-forms.php - Plugin usage popover reworded
*	css/custom-contact-forms-admin.css - Admin panel display problem fixed

= 3.5.4 =
*	custom-contact-forms.php - custom thank you redirect fix
*	custom-contact-forms-db.php - Style insert bug fixed, Unexpected header output bug fixed

= 3.5.3 =
*	custom-contact-forms.php - Style popover height option added to style manager. Form title heading not shown if left blank.
*	custom-contact-forms-db.php - New success popover height column added to styles table

= 3.5.2 =
*	custom-contact-forms.php - Plugin Usage popover added, insert default content button
*	custom-contact-forms-db.php - Insert default content function

= 3.5.1 =
*	custom-contact-forms.php - Style options added, color picker added, success popover styling bugs fixed
*	custom-contact-forms-db.php - Style format changed, new style fields added to tables
*	Lots of javascript files
*	Lots of images for the colorpicker

= 3.5.0 =
*	custom-contact-forms.php - Radio and dropdowns added via the field option manager
*	custom-contact-forms-mailer.php - Email body changed
*	custom-contact-forms-db.php - Field option methods added
*	custom-contact-forms.css - Form styles reorganized, file removed
*	css/custom-contact-forms.css - Form styles reorganized
*	css/custom-contact-forms-standards.css - Form styles reorganized
*	css/custom-contact-forms-admin.css - Form styles reorganized

= 3.1.0 =
*	custom-contact-forms.php - Success message title, disable jquery, choose between xhmtl and html, and more
*	custom-contact-forms-db.php - Success message title added
*	custom-contact-forms.css - Form styles rewritten

= 3.0.2 =
*	custom-contact-forms.php - Bugs fixed

= 3.0.1 =
*	custom-contact-forms.php - Php tags added to theme form display code

= 3.0.0 =
*	custom-contact-forms.php - Required fields, admin panel changed, style manager bugs fixed, custom html feature added, much more
*	custom-contact-forms-db.php - New functions added and old ones fixed
*	custom-contact-forms.css - New styles added and old ones modified

= 2.2.5 =
*	custom-contact-forms.php - Fixed field insert bug fixed

= 2.2.4 =
*	custom-contact-forms.php - Textarea field instruction bug fixed

= 2.2.3 =
*	custom-contact-forms.php - Remember fields bug fixed, init rearranged, field instructions
*	custom-contact-forms.css
*	custom-contact-forms-db.php

= 2.2.0 =
*	custom-contact-forms.php - Plugin nav, hide plugin author link, bug reporting, suggest a feature
*	custom-contact-forms.css - New styles added and style bugs fixed

= 2.1.0 =
*	custom-contact-forms.php - New fixed field added, plugin news, bug fixes
*	custom-contact-forms.css - New styles added and style bugs fixed
*	custom-contact-forms-db.php - New fixed field added

= 2.0.3 =
*	custom-contact-forms.php - custom style checkbox display:block error fixed
*	custom-contact-forms.css - li's converted to p's

= 2.0.2 =
*	custom-contact-forms.php - Form li's changed to p's
*	images/ - folder readded to correct captcha error

= 2.0.1 =
*	custom-contact-forms.php - Duplicate form slug bug fixed, default style values added, stripslahses on form messages
*	custom-contact-forms-db.php - default style values added

= 2.0.0 =
*	custom-contact-forms.php - Style manager added
*	custom-contact-forms.css - style manager styles added
*	custom-contact-forms-db.php - Style manager db functions added

= 1.2.1 =
*	custom-contact-forms.php - Upgrade options changed
*	custom-contact-forms-css.php - CSS bug corrected

= 1.2.0 =
*	custom-contact-forms.php - Option to update to Custom Contact Forms Pro

= 1.1.3 =
*	custom-contact-forms.php - Captcha label bug fixed
*	custom-contact-forms-db.php - Default captcha label changed

= 1.1.2 =
*	custom-contact-forms-db.php - create_tables function edited to work for Wordpress MU due to error in wp-admin/includes/upgrade.php

= 1.1.1 =
*	custom-contact-forms.css - Label styles changed
*	custom-contact-forms.php - Admin option added to remember field values

= 1.1.0 =
*	custom-contact-forms-db.php - Table upgrade functions added
*	custom-contact-forms.php - New functions for error handling and captcha
*	custom-contact-forms.css - Forms restyled
*	custom-contact-forms-images.php - Image handling class added
*	image.php, images/ - Image for captcha displaying

= 1.0.1 =
*	custom-contact-forms.css - Form style changes

= 1.0.0 =
*	Plugin Release
