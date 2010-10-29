<?php

/**
 * Specify a Google Account username and password to use for testing.
 */

$login_email = 'ggphpapi@gmail.com';      // Example: foobar@gmai.com
$login_password = 'GoogleGroupsPHPAPI2010';   // Example: SeCrEt1337

/**
 * Specify a google group that the above account has manager or admin access to.
 * This group should not be safe to break incase a test does fail. This should
 * be the shortname of the group.
 */
$test_group = 'ggphpapi-test';       // Example: my-test-group