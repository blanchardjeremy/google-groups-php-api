

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

  * Add example.php to demonstrate how the API can be used.
  * Maintain a session between requests using cookies
  * Create a new group
  * Modify member settings
  * Modify group settings
  * Retrieve URL for RSS feed of group
  * Complete esting suite
  * Better error checking


Legal
-----
To my best, non-professional of the [Google Groups Terms of Service](http://groups.google.com/googlegroups/terms_of_service.html), I think that is permissible to use this API. That said, using the library to force-add users to a group for the purposes of SPAM.


License
-------
This code is available under GPLv2.

Development
-----------
This library may not see a stable release for some months. Currently, development is being done as a proof-of-concept. Use at your own risk.

Written by Jeremy Blanchard from [Activism Labs](http://activismlabs.org).
