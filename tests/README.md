
Configuration
-------------
Edit *test_config.php* in this directory to contain the following:

  * The username and password for a google account
  * The "shortname" of a group that the above account has manager access to

Running a Test
--------------
To run a test, visit *autorun_tests.php* in your browser.

Git
---
If you want to keep your config file "untracked" in Git, you have to run:

    git update-index --assume-unchanged test_config.php

You can reverse this with:

    git update-index --no-assume-unchanged test_config.php
