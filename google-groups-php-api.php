<?php
/**
 * @author Jeremy Blanchard (auzigog) <auzigog@gmail.com>
 * @version dev1
 *
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 */


require_once(dirname(__FILE__) . '/simpletest/browser.php');

class GGAPICreateGroupException extends Exception { }
class GGAPICAPTCHAWrongException extends Exception { }

class GoogleGroupsAPI {

  // Default user agent to send while visiting pages.
  const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11';

  const DEFAULT_HTTP_ACCEPT_HTML = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
  const DEFAULT_HTTP_ACCEPT_IMAGE = 'image/png,image/*;q=0.8,*/*;q=0.5';

  // Default welcome message to send to a user when adding them to a group. %s
  //  is replaced with the GROUP_SHORTNAME
  const DEFAULT_WELCOME_MESSAGE = 'Welcome to the group! Send an email to %s@googlegroups.com to begin discussions with the group.';

  // SimpleTest browser
  var $browser;

  // Name of the group currently being accessed.
  var $group_shortname;

  // CAPTCHA image data for the most recent group creation process.
  var $captcha_image_data;

  /**
   * Initialize the class.
   */
  function __construct() {
    $this->browser = new SimpleBrowser();

    // Set a default user agent and other headers
    $this->setDefaultHeaders();
  }

  /*********************************************************************
   *                   API Functions
   *********************************************************************/


  /**
   * Login to a given Google Account.
   *
   * @param $email Email address of a Google Account. For most functions of
   *  this API, this account needs to have the role of _manager_ or
   *  _administrator_ within the group.
   * @param $password Password of the Google Account.
   */
  function login($email, $password) {
    $login_page = $this->browser->get('https://www.google.com/accounts/ServiceLogin?passive=true&hl=en&service=groups2&continue=http://groups.google.com/%3Fpli%3D1&cd=US&ssip=g3');
    $this->browser->setField('Email', $email);
    $this->browser->setField('Passwd', $password);
    $after_submit_page = $this->browser->clickSubmit('Sign in');

    // Do some tricky stuff to get around the meta redirect that Google Groups does here
    $matches = array();
    preg_match('@"(http://groups.google.com/.*)"@i', $after_submit_page, $matches);
    $redirect_url = $matches[1];
    $redirect_url = str_replace('\x3d', '=', $redirect_url);
    $redirect_url = str_replace('\x26', '&', $redirect_url);

    // Do the final page request
    $after_meta_redirect_page = $this->browser->get($redirect_url);

    // @todo Store the current state "logged in" state of the system and check it when doing other API calls
    // @todo Make this function return the success/failure of the login
  }

  /**
   * Force user(s) to become a member of a group. Equivalent to visiting the
   * "Add members directly" page. The user will recieve an email notifying
   * them of the addition.
   *
   * @param $emails Email addresses of the members to add. Formatted in a
   *  comma-separated list.
   * @param $welcome_message The message to include in the "welcome message"
   *  email that is sent to the member. This must be of a certain length. I
   *  think it's around 5 words or 25 characters. Place %s in the string
   *  to have it automatically be replaced with the GROUP_SHORTNAME.
   */
  function memberSubscribeDirectly($emails, $welcome_message = NULL) {
    // @todo Add param to specify the subscription type (no email, direct, daily summary, etc)
    // @todo Set up a system for throttling the additions to 10 emails per request.
    // Get the "Add members directly" page
    $add_page = $this->getGroupPage('manage_members_add');
    //echo $add_page;die();

    // Prep the welcome message
    if ($welcome_message == NULL) {
      $welcome_message = self::DEFAULT_WELCOME_MESSAGE;
    }
    // Replace the first instance of %s with the GROUP_SHORTNAME
    $welcome_message = sprintf($welcome_message, $this->group_shortname);

    // Submit the form
    $this->browser->setField('members_new', $emails);
    $this->browser->setField('body', $welcome_message);
    $add_member_result_page = $this->browser->clickSubmit('Add members');
    //echo $add_member_result_page; die();

    // @todo Make this function return success/failure of each member. Probably
    //  return as two arrays. One for successes and one for failures.
  }

  /**
   * Remove a member from a group.
   *
   * @param $email Email address of the member to be remove.
   */
  function memberUnsubscribe($email) {
    // @todo Make it possible to unsubscribe multiple members with few page loads
    // @todo This would be easier if we could store the user ID of the person!
    $search_page_url = $this->getGroupURL().'manage_members?view=all&sort=email&member_q='.urlencode($email).'&Action.Search=Find+member';
    $manage_page = $this->browser->get($search_page_url);
    //echo $manage_page;

    // Get the user id from the page
    $matches = array();
    preg_match_all('@value="([0-9]+)"@i', $manage_page, $matches);
    //print_rr($matches);
    //  rr($user_id, 'user id');
    $user_id = $matches[1][1];

    // Remove the given user id
    $this->browser->setField('membership_type', 'unsub');
    $this->browser->setField('subcheckbox', $user_id);
    $after_delete_page = $this->browser->clickSubmitByName('Action.SetMembershipType');
    //print_rr($this->browser->getRequest());
  }

  /**
   * Initiate the process of creating a new group. Since this process has a
   * CAPTCHA, you need to get the user to do a second function call after having
   * the user solve the CAPTCHA.
   *
   * After this function returns, the process goes something like this:
   *   - Call $gg->saveState() to seralize the entire class (HUGE string)
   *   - Get the user to submit the answer to the captcha
   *   - Call $gg = unseralize($state_save_str) to restore the object
   *   - Call $gg->createGroupAnswerCaptcha() with the answer
   *   - Group should be created
   *
   * @param $privacy_level Can be 'public', 'announce' or 'private
   * @return The raw image 2 bytes for the CAPTCHA image.
   */
  function createGroup($group_name, $group_shortname, $group_description, $privacy_level = 'public') {
    $create_group_page = $this->browser->get('http://groups.google.com/groups/create');
    $this->browser->setField('name', $group_name);
    $this->browser->setField('addr', $group_shortname);
    $this->browser->setField('desc', $group_description);
    $this->browser->setFieldByName('privacy', $privacy_level);
    $after_submit_page = $this->browser->clickSubmitByName('Action.Create');
    //echo $after_submit_page; die();

    // Error checking - Make sure the group name doesn't already exist
    if (strpos($after_submit_page, 'This email address is already used') !== FALSE) {
      throw new GGAPICreateGroupException('Group name already exists.', 2);
    }

    // Find the captch URL
    $matches = array();
    preg_match('@img src="(/groups/captcha_media.*)"@i', $after_submit_page, $matches);
    //krumo($matches);
    // Error checking - CAPTCHA not found, therefore we got redirect to some other page.
    if (empty($matches[1])) {
      throw new GGAPICreateGroupException('Create group (step 1) did not success. CAPTCHA not available. This could mean your input was not valid.', 3);
    }
    $captcha_image_query = $matches[1];
    $captcha_image_url = 'http://groups.google.com'.$captcha_image_query;

    $referer = 'http://groups.google.com/groups/create';
    $imgbrowser = new SimpleImageBrowser($this->browser);
    $this->captcha_image_data = $imgbrowser->getImageData($captcha_image_url, $referer);
    unset($imgbrowser);

    return $this->captcha_image_data;
  }

  /**
   * Return the content of the image with the proper headers set. Should be
   * used to display the image in it's own right. For exmaple:
   * http://example.com/finish_group?action=show_image&session=abc123
   */
  function createGroupShowImage() {
    $image = $this->captcha_image_data;
    header("Content-Type: image/jpeg");
    header("Content-Length: " . strlen($image));
    echo $image;
  }

  /**
   * Finalizes the creation of a group. Call after the user solves the CAPTCHA
   * presented form createGroup(). This function assumes that the last 2 pages
   * loaded were the CAPTCHA submission page followed by then the CAPTCHA image
   *
   * @param $captcha_answer Answer to the CAPTCHA.
   * @return TRUE on success
   */
  function createGroupAnswerCaptcha($captcha_answer) {
    // @todo Add error checking if the CAPTCHA fails
    $this->browser->setField('answer', $captcha_answer);

    $after_submit_page = $this->browser->clickSubmitByName('Action.Create');
    //echo $after_submit_page;
    //print_rr($this->browser->getRequest());
    //print_rr($this->browser->getHeaders());

    //print_rr($this->browser->_user_agent->_additional_headers);

    // Error checking - Make sure this redirected us to the final "invite members" page
    if (strpos($after_submit_page, 'Invite members by email') === FALSE) {
      throw new GGAPICaptchaAnswerException('CAPTCHA answer did not succeeed. Answer was wrong or the session expired.', 1);
    }

    return TRUE;
  }

  /**
   * Save the cookies for use with restoing the state of a session.
   *
   * @return A long string containing a SimpleCookieJar object that can be
   *  passed into restoreCookies() to restore a session.
   */
  function saveCookies() {
    $jar_save_str = serialize($this->browser->_user_agent->_cookie_jar);
    return $jar_save_str;
  }

  /**
   * Restore cookies to effectively restore a session.
   *
   * @param $jar_save_str The string retrieved from saveCookies().
   */
  function restoreCookies($jar_save_str) {
    $this->browser->_user_agent->_cookie_jar = $jar_save_str;
  }

  /**
   * Allows you to save the current state of the GGAPI. Returns the object
   * as a seralized string. Can only be restored by calling
   * $gg = unseralize($state_save_str).
   *
   * @return $state_save_str A serialized representation of the GGAPI object.
   */
  function saveState() {
    $state_save_str = serialize($this);
    return $state_save_str;
  }


  /*********************************************************************
   *                   API Internal Functions
   *********************************************************************/

  /**
   * Set the group the you would like to interact with. This is the "active
   * group."
   *
   * This saves you from specifying the group with every API call.
   *
   * @param $group_shortname Short name of the group in quesiton. Should be the
   *  part of the email address before *@googlegroups.com*.
   */
  function setGroup($group_shortname) {
    $this->group_shortname = $group_shortname;
  }

  /**
   * Returns the internal browser.
   */
  public function &getBrowser() {
    return $this->browser;
  }

  /**
   * Return the group URL for use with API calls. This uses the currently set
   * group shortname from the setGroup() function.
   * Format: http://groups.google.com/group/GROUP_SHORTNAME/
   */
  protected function getGroupURL() {
    $group_url = 'http://groups.google.com/group/'.$this->group_shortname.'/';
    return $group_url;
  }

  /**
   * Helper function to quickly run $browser->get() on a subpage within the
   * currently specified group.
   *
   * @param $suburl The part of the URL after http://groups.google.com/group/GROUP_SHORTNAME/
   *  Do not include the preceding slash on the suburl.
   *  Example: 'manage_members_add'
   * @return The HTML of the page that was retrieved.
   */
  protected function getGroupPage($suburl) {
    return $this->browser->get($this->getGroupURL().$suburl);
  }

  /**
   * Sets a user agent and other headers to emulate a real browser.
   *
   * @param $user_agent User agent to set for future page visits.
   */
  function setDefaultHeaders($user_agent = NULL) {
    if ($user_agent == NULL) {
      $user_agent = self::DEFAULT_USER_AGENT;
    }

    $headers = array(
      "User-Agent:\t".$user_agent,
      //'Accept:'."\t".self::DEFAULT_HTTP_ACCEPT_HTML,
      //'Accept-Language:'."\t".'en-us,en;q=0.5',
      //'Keep-Alive:'."\t".'115',
      //'Accept-Charset:'."\t".'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
      //'Connection:'."\t".'keep-alive',
    );
    foreach ($headers as $header) {
      $this->browser->addHeader($header);
    }
  }

}


/**
 * Use this to browse to an image and get it's data without interferring
 * with the state of broswer.
 */
class SimpleImageBrowser {
  const DEFAULT_HTTP_ACCEPT_HTML = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
  const DEFAULT_HTTP_ACCEPT_IMAGE = 'image/png,image/*;q=0.8,*/*;q=0.5';

  var $browser;
  var $image_data;

  function __construct(SimpleBrowser $browser) {
    $this->browser = clone $browser;
  }

  /**
   * Primary function
   */
  function getImageData($url, $referer = NULL) {
    $this->setImageHTTPAccept();

    if ($referer != NULL) {
      $this->setReferer($referer);
    }

    // Get the captcha image
    $this->image_data = $this->browser->get($url);

    return $this->image_data;
  }

  function setReferer($referer) {
    $header = 'Referer:'."\t".$referer;
    $this->browser->addHeader($header);
  }

  function setImageHTTPAccept() {
    $headers = $this->browser->_user_agent->_additional_headers;
    $replace_value = self::DEFAULT_HTTP_ACCEPT_IMAGE;
    $this->browser->_user_agent->_additional_headers = $this->replaceAccept($headers, $replace_value);
  }

  function setHTMLHTTPAccept() {
    $headers = $this->browser->_user_agent->_additional_headers;
    $replace_value = self::DEFAULT_HTTP_ACCEPT_HTML;
    $this->browser->_user_agent->_additional_headers = $this->replaceAccept($headers, $replace_value);
  }

  /**
   * Find the 'Accept' header and replace it with a given value
   */
  function replaceAccept($headers, $replace_value) {
    // Find the "Accept" header
    foreach ($headers as $i=>$header) {
      if (strpos($header, "Accept:\t") !== false) {
        $headers[$i] = $replace_value;
        break;
      }
    }

    return $headers;
  }

}
