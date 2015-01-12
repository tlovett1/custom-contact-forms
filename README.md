Custom Contact Forms [![Build Status](https://travis-ci.org/tlovett1/custom-contact-forms.svg?branch=master)](https://travis-ci.org/tlovett1/custom-contact-forms)
==============

Build beautiful custom forms the WordPress way.

## Purpose

The problem of form creation in WordPress has been tackled many different ways. Custom Contact forms handles forms the
WordPress way. The plugin provides a seamless user experience for managing your forms through the comfort of the
WordPress media manager modal. CCF does not have as many features as some of it's competitors but instead provides
you with just what you need. Custom Contact Forms is a legacy plugin name. The plugin can handle all types of forms not
just contact forms.

## Installation

Install the plugin in WordPress, you can download a
[zip via Github](https://github.com/tlovett1/custom-contact-forms/archive/master.zip) and upload it using the WP
plugin uploader.

## Form Management

You can create and manage forms within a post by clicking the `Add Form` button next to the `Add Media`
button. You can also create and manage forms by clicking the `Forms` menu item in the administration panel.

### Fields

The building block of forms are fields. Each field has it's own set of settings that you can change on a
per-field basis.

#### Standard Field Settings

* `Internal Slug` - Every field in a form must have a unique slug. Slugs are auto-generated to make your life
easier. This makes it easier to develop with CCF.
* `Label` - A label shows up above a field and is visible to the form user.
* `Initial Value` - A fields value upon loading the form.
* `Required` - Required fields must be filled out for a form to be submitted.
* `Class Name` - You can manually add classes to a fields wrapper element.
* `Placeholder Text` - Very similar to `Initial Value` but makes use of HTML5 placeholder.

#### Field Types

You can create forms using the following field types:

##### Normal Fields

* `Single Line Text` - A single line text box. This is the most standard field.
* `Paragraph Text` - A multi-line text box.
* `Dropdown` - A simple dropdown of choices.
* `Checkboxes` - A list of checkable choices.
* `Radio Buttons` - A list of choices where only one can be chosen.
* `Hidden` - A hidden field.

##### Special Fields

* `Email` - A simple field that will ensure user input is a valid email address.
* `Name` - A field with two input boxes, one for first and one for last name.
* `Date/Time` - A field to ask for dates and time. You can configure the field to only ask for date or time if you choose.
* `Website` - A simple field that will ensure user input is a valid URL.
* `Address` - A field for US and international addresses.
* `Phone` - A simple field that will ensure user input is a valid phone number.

##### Structure Fields

* `HTML`
* `Section Header`

### Submissions

CCF provides a very pretty table view for navigating your form submissions.

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

* wordpress_test is the name of the test database (all data will be deleted!)
* root is the MySQL user name
* root is the MySQL user password (if you're running VVV). Blank if you're running VIP Quickstart.
* localhost is the MySQL server host
* latest is the WordPress version; could also be 3.7, 3.6.2 etc.

Run the plugin tests:
```
phpunit
```

#### Issues
If you identify any errors or have an idea for improving the plugin, please
[open an issue](https://github.com/tlovett1/custom-contact-forms/issues?state=open).