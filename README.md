ACF5 Beta
=========

Welcome to the Advanced Custom Fields v5 beta repository. This repository will provide an effective workflow for issue tracking during the final stages of this build process.

V5 is a brand new ACF plugin which has been completely re-written from the ground up to solve many of the limitations that V4 suffered (due to it's growth over a 2 year period). There are lots of new features including new fields, settings, designs and functionality! All of which focus on creating a cleaner and more powerful interface for WordPress websites.

Rules
-----
* Please fully read this `README.md`
* Please pull the latest code before any testing
* Please don't share, leak or distribute any code from this repository

How to report an issue
----------------------

All issues are to be reported here: [Create a new issue](https://github.com/elliotcondon/acf5-beta/issues/). Please provide a clear title and comment that describes what the issue is, why it is an issue and how the issue can be recreated. If possible, please provide any errors, code snippets or attachments to aid your description.

Installation
------------

ACF5 introduces a new way to unlock premium add-ons. Previously, each add-on could be purchased and installed separately which made rolling out updates slow and cumbersome. To fix this, ACF5 can be upgraded from the `Free` version to a `PRO` version! The pro version will be purchased with either a single or multi-site license. 

For the duration of this beta testing, a developer license will be made available to all beta testers:
`cHVyY2hhc2VkPTI1MjQ3fHR5cGU9c2luZ2xlfGRhdGU9MjAxNC0wMS0xNyAwMzoyNToxOQ==`

To correctly install ACF5 for beta testing, please follow these steps:

1. Setup a new local WP installation or use an existing one
2. Disabled the `advanced-custom-fields` plugin if already exists
3. Clone this repository into a new folder: `wp-content/plugins/advanced-custom-fields-pro`
4. Activate the `advanced-custom-fields-pro` plugin via wp-admin
5. Navigate to `Custom Fields -> Updates` and enter the above License Key to unlock updates

### Notes regarding updates

Please do not update the plugin via the `Custom Fields -> Updates` page as this will destroy your `git` data. Instead, please test that the page functionality is working correctly.

The files in this git repository represent the files available for download using a purchased developer license. All `PRO` functionality is included in a `pro` folder. Removing this folder will represent the `FREE` version.

New features in ACF5
--------------------

* Overhaul of HTML & CSS
* Fields & Sub fields now save as separate post objects, not postmeta
* Added Select2 JS for AJAX and search functionality on select lists
* Added AXAJ search functionality for Post Object, taxonomy, user and select fields
* Added JSON read/write functionality to save field groups to files (saves DB query time)
* Added JSON import/export functionality to replace old XML style
* New location rules allow field groups on comments
* New location rules allow field groups on user (including registration and bbPress)
* New location rules allow field groups on widgets
* New API folder contains a library of functions to use in plugins and themes
* New oembed field for easy iframe embeds
* New Gallery field design
* New field group options for `Label placement` and `Instruction placement` allow for an all new look!
* New PHP + AJAX validation replaces old JS style
* New Relationship field setting for 'Filters' (Search, Post Type, Taxonomy)
* New field group functionality allows you to move a field between groups
* New field group functionality allows you to move a field between parents (repeater)
* New Add-ons page uses an external JSON file to read in data (easy to add 3rd party fields)
* Huge improvements to core functionality resulting in faster load times!
* New archives group in page_link field selection

Missing features in ACF5
------------------------

Please keep in mind that this is quite an early beta release and many core features are still missing. These will be added in over the next month and the above list will be updated.

* WPML has not yet been tested
* 3rd party fields are currently not supported
* Options page add-on has not yet been added

Thank you
---------

Last but not least, I would like to thank you very much for your help in testing this new version of ACF. I'm looking forward to hearing your feedback, and working with you to solve any issues.

Cheers
Elliot