Custom Contact Forms [![Build Status](https://travis-ci.org/tlovett1/custom-contact-forms.svg?branch=master)](https://travis-ci.org/tlovett1/custom-contact-forms)
===============

Build beautiful custom forms and manage submissions the WordPress way. View live previews of your forms while you build them. Create powerful and exstensible forms for simple contact inquiries, sign ups, subscriptions, events, payments, etc.

## Purpose

The problem of form creation in WordPress has been tackled many different ways. Custom Contact forms handles forms the
WordPress way. The plugin provides a seamless user experience for managing your forms through the comfort of the
WordPress media manager modal. CCF does not have as many features as some of it's competitors but instead provides
you with just what you need. Custom Contact Forms is a legacy plugin name. The plugin can handle all types of forms not
just contact forms.

## Requirements

* WordPress 3.9+
* PHP 5.2.4+

## Installation

Install the plugin in WordPress, you can download a
[zip via Github](https://github.com/tlovett1/custom-contact-forms/archive/master.zip) and upload it using the WP
plugin uploader.

## Usage

You can create and manage forms within a post by clicking the `Add Form` button next to the `Add Media`
button:

![Add a form in a post](https://tlovett1.files.wordpress.com/2015/01/add-form-post.png)

Select or create a new form and click `Insert into post`:

![Insert a form into a post](https://tlovett1.files.wordpress.com/2015/01/insert-form.png)

You can also add a form to your theme or plugin using PHP:

```php
if ( function_exists( 'ccf_output_form' ) ) {
    ccf_output_form( FORM_ID );
}
```

In case you want to use the shortcode, it is: `[ccf_form id="FORMID"]`. You can find the form ID in the existing forms table of the form manager or in the URL when directly editing a form.

### Form Settings

Each form has a number of settings that you should understand.

* `Title` - The main title for the form. This will be shown to the end user above the form.
* `Description` - A form description that will be shown to the end user below the form title.
* `Button Text` - This text will be shown to the end user on the submit button.
* `On form completion` - When a form is completed you can show a message or perform a browser redirect

  * `Completion Message` - If you choose to show a message, you can customize the message to be shown.
  * `Redirect URL` - If you choose to perform a redirect, you can customize the redirect URL.

* `Pause` - Pausing a form will temporarily disable new form submissions on the front end.

  * `Pause Message` - This message will be shown if the form is paused.

#### Form Notifications

For each of your forms you can add email notifications (as many as you want). Email notifications will be sent when a
form is successfully filled out. Each notification contains the following configurable settings:

* `Notification Title` - This is just an internal name for keeping track of your notification.
* `Email Content (HTML)` - This is the body of the email that will be sent. The email must be written using HTML. The following variables are support:

  * `[all_fields]` - Shows all your form fields
  * `[ip_address]` - Shows the IP address of the submitter
  * `[FIELD_SLUG]` - Each of your form fields can be inserted using their field slug.

* `"To" Email Addresses` - You can send each notification to as many emails as you want. Addresses have two types: `custom` and 
`field`. `custom` allows you to specify a specific email (such as your own). `field` will pull the email address dynamically from a form field.
* `"From" Email Address Type` - This allows you to set what email address the notification is sent from. `WordPress
Default` will use the default WordPress email address. `Custom Email` will allow you to manually type in a from
address. `Form Field` will allow you to choose an email field within the form to dynamically pull a from email
address.
* `"From" Name Type` - This allows you to set what name the notification email is sent from. `Custom Name` 
will allow you to manually type in a from name. `Form Field` will allow you to choose a name field within 
the form to dynamically pull a from email name.
* `Email Subject Type` - This allows you to set what subject line is used on the notification email. `Default` 
will use the CCF default subject. `Custom Subject` will allow you to manually type in an email subject. 
`Form Field` will allow you to choose a field within the form to dynamically pull a subject line.

__Note:__ In order for form notification changes to take affect, you will need to save the form.

#### Post Creation

For each of your forms, you can have a post (or custom post type) created every time someone submits the form. This is an extremely powerful feature.

* `Enable Post Creation` - Selecting `Yes` will enable post creation. Note that posts won't start creating until you map some fields.
* `Post Type` - You can choose the type of post type that will be created with each submission. This defaults to `post`.
* `Post Status` - You can choose the status (publish, draft, etc.) of the post that will be created with each submission. This defaults to `draft`.
* `Field Mappings` - You will need to map your form fields to the appropriate post fields. The available post fields are as possible:

  * `Post Title` - Selecting this post field will map your form field to the title of the post.
  * `Post Content` - Selecting this post field will map your form field to the content of the post.
  * `Post Excerpt` - Selecting this post field will map your form field to the excerpt of the post.
  * `Post Date` - Selecting this post field will map your form field to the publish date of the post.
  * `Post Tags` - Selecting this post field will map your form field to the tags of the post.
  * `Custom Field` - Selecting this post field will map your form field to a custom field of the post. If you use `Custom Field`, you will need to choose a custom field key.


### Fields

The building block of forms are fields. Each field has it's own set of settings that you can change on a
per-field basis.

#### Standard Field Settings

* `Internal Slug` - Every field in a form must have a unique slug. Slugs are auto-generated to make your life
easier. This makes it easier to develop with CCF.
* `Label` - A label shows up above a field and is visible to the form user.
* `Description` - A description shows up below a field and is visible to the form user.
* `Initial Value` - A fields value upon loading the form.
* `Required` - Required fields must be filled out for a form to be submitted.
* `Class Name` - You can manually add classes to a fields wrapper element.
* `Placeholder Text` - Very similar to `Initial Value` but makes use of HTML5 placeholder.

#### Field Types

You can create forms using a number of field types. Certain field types of special field settings that are
described below:

##### Normal Fields

* `Single Line Text` - A single line text box. This is the most standard field.
* `Paragraph Text` - A multi-line text box.
* `Dropdown` - A simple dropdown of choices.
* `Checkboxes` - A list of checkable choices.
* `Radio Buttons` - A list of choices where only one can be chosen.
* `Hidden` - A hidden field.
* `File Upload` - Allow users to upload a file.

  * __Allowed Files Types__ - Restrict the file extensions that can be uploaded. If left blank, this will default to whatever is allowed by WordPress.
  * __Max File Size__ - Restrict the max file size allowed to be uploaded. If left blank, will default to whatever is allowed by WordPress and your server.

__Note__: Choiceable fields all handle choices the same way. Choices can be set with a `value` and a `label`. Values
are internal, and labels are visible to the end form user. If a choice does not have a `value`, the choice will not
"count". Meaning the field will not be considered filled out if it's required.

##### Special Fields

* `Email` - A simple field that will ensure user input is a valid email address.

  * __Require Confirmation__ - Enabling this will insert another input box where the user must type the same email again.

* `Name` - A field with two input boxes, one for first and one for last name.
* `Date/Time` - A field to ask for dates and time. You can configure the field to only ask for date or time if you choose.

  * __Enable Date Select__ - Will prompt the user for a date selection.
  * __Enable Time Select__ - Will prompt the user for a time selection.

* `Website` - A simple field that will ensure user input is a valid URL.
* `Address` - A field for US and international addresses.

  * __Type__ - Allows you to prompt the user for a United States or international address.

* `Phone` - A simple field that will ensure user input is a valid phone number.

  * __Format__ - Allows you to prompt the user for a United States or international phone number.

* `reCAPTCHA` - A Captcha field using Google's free reCAPTCHA technology. You will need to
[sign up](https://www.google.com/recaptcha) for reCAPTCHA before this field will work.

  * __Site Key__ - Your Google reCAPTCHA site key.
  * __Secret Key__ - Your Google reCATPCHA secret key.

##### Structure Fields

* `HTML` - An easy way to insert arbitrary HTML into the middle of a form.

  * __HTML Content__: Supports all HTML tags except `<script>`.

* `Section Header` - Inserts a pre-styled heading to break up your form visually.

  * __Heading__ - Main section heading.
  * __Sub Heading__ - Smaller text below main heading.

### Submissions

CCF provides a very pretty table view for navigating your form submissions.

#### View Form Submissions

Click the `Forms` item in the administration menu. Click on the specific form for which you want to view submissions.
Scroll to the `Submissions` meta box. Click one the eye icon to view more information for a specific submission.

#### Customizing the Form Submissions Table

In the `Submissions` meta box, you can add and remove columns. Click the cog icon at the top of the meta box to open
the screen options panel. In this panel you can check which columns you would like to see in the table.

#### Download Form Submissions as CSV

In addition to being able to import/export forms with all fields and submissions, you can export submissions for
individual forms as .CSV files. Click the icon within the form edit view like so:

![Export Submissions as .CSV Files](https://tlovett1.files.wordpress.com/2015/03/submissions-csv.png)

### Import/Export

Custom Contact Forms allows you to import and export forms and form submissions using the default WordPress
importer/exporter functionality. You can export all your forms (with submissions) within the standard WordPress export view:

![Export CCF Forms](https://tlovett1.files.wordpress.com/2015/03/export-all1.png)

You can also export a single form (with submissions) within the `Edit Form` screen:

![Export Single CCF Form](https://tlovett1.files.wordpress.com/2015/03/export-single.png)

Both export methods will produce a standard WordPress WXR file. You can import your WXR files using the standard
WordPress importer:

![Import CCF Forms](https://tlovett1.files.wordpress.com/2015/03/import.png)

### General Settings

Custom Contact Forms has a general settings page. Right now the only thing it contains is asset loading restriction which allows you to choose specific pages that form CSS and JS should be loaded on. This allows you to prevent a ton of unnecessary website bloat.

## Frequently Asked Questions

* __My form(s) will not save. What's wrong?__

  You most likely have a theme or plugin conflict. Try deactivating other plugins and activating a default theme. If
  forms still won't save, please create an issue.

* __Form mail is not getting emailed to me. What's wrong?__

  CCF relies on default WordPress email functionality. If CCF email is not sending, most likely no WordPress email is
  sending. You can test this by trying to send yourself a lost password email. If you don't receive the lost password
  email, then there is an issue with your host or a plugin/theme conflict.

* __My form won't submit on the front end of my website. What's wrong?__

  Most likely there is a JavaScript error on your page that is conflicting with the form. This is the result of a theme
  or plugin conflict. Try deactivating other plugins and activating a default theme. Another possibility is that your
  theme does not call `wp_head()` or `wp_footer()`.

## Development

#### Setup
Follow the configuration instructions above to setup the plugin. I recommend developing the plugin locally in an
environment such as [Varying Vagrant Vagrants](https://github.com/Varying-Vagrant-Vagrants/VVV).

If you want to touch JavaScript or CSS, you will need to fire up [Grunt](http://gruntjs.com). Assuming you have
[npm](https://www.npmjs.org/) installed, you can setup and run Grunt like so:

First install Grunt:
```
npm install -g grunt-cli
```

Next install the node packages required by the plugin:
```
npm install
```

Finally, start Grunt watch. Whenever you edit JS or SCSS, the appropriate files will be compiled:
```
grunt watch
```

#### Testing
Within the terminal change directories to the plugin folder. Initialize your unit testing environment by running the
following command:

For VVV users:
```
bash bin/install-wp-tests.sh wordpress_test root root localhost latest
```

For VIP Quickstart users:
```
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

where:

* `wordpress_test` is the name of the test database (all data will be deleted!)
* `root` is the MySQL user name
* `root` is the MySQL user password (if you're running VVV). Blank if you're running VIP Quickstart.
* `localhost` is the MySQL server host
* `latest` is the WordPress version; could also be 3.7, 3.6.2 etc.

Run the plugin tests:
```
phpunit
```

##### Dockunit

This plugin contains a valid [Dockunit](https://www.npmjs.com/package/dockunit) file for running unit tests across a variety of environments locally (PHP 5.2 and 5.6). You can use Dockunit (after installing it via npm) by running:

```bash
dockunit
```

#### Extending the Plugin

Coming soon.

#### Issues
If you identify any errors or have an idea for improving the plugin, please
[open an issue](https://github.com/tlovett1/custom-contact-forms/issues?state=open).

## License

Custom Contact Forms is free software; you can redistribute it and/or modify it under the terms of the [GNU General
Public License](http://www.gnu.org/licenses/gpl-2.0.html) as published by the Free Software Foundation; either version 2 of the License, or (at your option) any
later version.
