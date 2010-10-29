<?php

class TestOfGoogleGroupsAPI extends WebTestCase {
  var $gg;
  var $last_email_added;
  var $random_email;

  var $login_email;
  var $login_password;

  const DUMMY_MEMBER = 'ggphpapi+dummy@gmail.com';

  const GROUP_SHORTNAME = 'ggphpapi-test';
  const GROUP_URL = 'http://groups.google.com/group/ggphpapi-test';

  function __construct() {
    require_once(dirname(__FILE__).'/../../google-groups-php-api.php');
    require_once(dirname(__FILE__).'/../util.php');

    // Should set the $login_email and $login_password
    require_once(dirname(__FILE__).'/test_config.php');

    $this->gg = new GoogleGroupsAPI();
    $this->gg->setGroup(self::GROUP_SHORTNAME);
    $this->random_email = $this->_getRandomEmail();
  }

  /**
   * Must run this before doing assertions so the test bowser is logged in.
   *
   * @todo Probably a way to fix this so the setting only has to be done one time
   * isntead of after every change in the browser within the GG API.
   */
  function _fixBrowser() {
    $b = $this->gg->getBrowser();
    $this->setBrowser($b);
  }

  function _getRandomEmail() {
    return 'ggphpapi+'. mt_rand(1000, 9999) . '@gmail.com';
  }

  function testLogin() {
    $this->gg->login($this->login_email, $this->login_password);

    $this->_fixBrowser();

    $page = $this->get(self::GROUP_URL.'/manage_members_add');
    //echo $page; die('here');
    $this->assertText('Please use this feature carefully', 'Succesfully logged in with owner or manager access.');
  }

  function testSubscribeDirectly() {
    $this->gg->memberSubscribeDirectly($this->random_email);

    $this->_fixBrowser();

    $page = $this->get(self::GROUP_URL .'/manage_members');
    //echo $page; die();
    $this->assertText($this->random_email, 'User successfully added! Email: '. $this->random_email);
  }

  function testUnsubscribe() {
    $this->gg->memberUnsubscribe($this->random_email);

    $this->_fixBrowser();

    $page = $this->get(self::GROUP_URL .'/manage_members');
    //echo $page; die();
    $this->assertNoText($this->random_email, 'User successfully removed! Email: '. $this->random_email);
  }
}