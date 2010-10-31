

Introduction
------------
This library gives programatic access to Google Groups from PHP. It does so by emulating a user's interaction with a browser (AKA "a bot" or "screen scraping"). I can be thought of as a faux-API.

If you would like to encourage Google to create a real API, post in this thread (or "star it"): [Groups API](http://code.google.com/p/gdata-issues/issues/detail?id=27).

This library requires that you specify the username and password for a Google Account with manager/admin access to each group that you want to manipulate.

**NOTE:** This library comes with no gaurentee of service. If Google changes the format of pages or any form submission process, this library may cease to function.

Features
--------
Initial features include:

  * Add members to a group (using the "direct add" feature)
  * Remove members from a group
  * Retrieve a listing of all members


TODO
----
Potential future features include

  * Remove any page requests before forms that don't set cookies and just submit the form directly
  * Add example.php to demonstrate how the API can be used
  * Maintain a session between requests using cookies
  * Modify member settings
  * Modify group settings
    * Have "presets" for quickly switching between public, private, and semi-private
  * Retrieve URL for RSS feed of group
  * Better error checking
  * Create a group and pass the CAPTCHA through to the user of the API
  * Work with international Google Groups URLs


Examples
--------
View examples of this script in the exmaples directory.

  * create_group_example.php - Demonstrates how to complete the 3 step process for creating a group and showing the CAPTCHA to a user.


Bugs and Feature Requests
-------------------------
Please submit bug reports and feature requests at the [GGPHPAPI GitHub project page](http://github.com/auzigog/google-groups-php-api/issues).


Legal
-----
To my best (non-professional) of the [Google Groups Terms of Service](http://groups.google.com/googlegroups/terms_of_service.html), I think that is permissible to use this API. That said, using the library to force-add users to a group for the purposes of SPAM.


License
-------
This code is available under GPL v2.

Development
-----------
This library may not see a stable release for some months. Currently, development is being done as a proof-of-concept. Use at your own risk.

Written by Jeremy Blanchard from [Activism Labs](http://activismlabs.org).
