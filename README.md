
Introduction
------------
This library gives programatic access to Google Groups from PHP. It does so by emulating a user's interaction with a browser (AKA "a bot" or "screen scraping"). I can be thought of as a faux-API.

If you would like to encourage Google to create a real API, post in this thread (or "star it"): [Groups API](http://code.google.com/p/gdata-issues/issues/detail?id=27).

This library requires that you specify the username and password for a Google Account with manager/admin access to each group that you want to manipulate.

**NOTE:** This library comes with no gaurentee of service. If Google changes the format of pages or any form submission process, this library may cease to function.


Features
--------
Current features include:

  * Add members to a group (using the "direct add" feature)
  * Remove members from a group
  * Create a new group (and send the CAPTCHA through to your users)


TODO
----
API functions to add:

  * Retrieve a list of all members
  * Modify member settings
  * Modify group settings
    * Have "presets" for quickly switching between public, private, and semi-private
  * Retrieve URL for RSS feed of group

Other TODO items:

  * Remove any page requests before forms that don't set cookies and just submit the form directly
  * Add example script to demonstrate of basic functionality
  * Better error checking
  * Work with international Google Groups URLs


Examples
--------
View examples of this script in the exmaples directory.

  * `create_group_example.php` - Demonstrates how to complete the 3 step process for creating a group and showing the CAPTCHA to a user.

*Notes:* You must edit the example file to include your username/password and other settings.


Bugs and Feature Requests
-------------------------
Please submit bug reports and feature requests at the [Google Groups PHP API GitHub project page](http://github.com/auzigog/google-groups-php-api/issues).


Testing
-------
A test library with moderate test coverage comes with this library. They are available in the `tests` directory.

To run the tests, edit the `tests/test_config.php` file to contain a username/password to use for testing. Then visit the `tests/autorun_tests.php` from your browser to execute the entire test suite.


Legal
-----
To my best (non-expert) reading of the [Google Groups Terms of Service](http://groups.google.com/googlegroups/terms_of_service.html), I think that is permissible to use this API. That said, using the library to force-add users to a group for the purposes of SPAM could result in the API being removed.


License
-------
This code is available under [GPL v2](http://opensource.org/licenses/gpl-2.0.php).


Development
-----------
Development details are available at [Google Groups PHP API GitHub project page](http://github.com/auzigog/google-groups-php-api/issues).

This library may not see a stable release for some months. Currently, development is being done as a proof-of-concept. Use at your own risk.

Written by Jeremy Blanchard from [Activism Labs](http://activismlabs.org).
