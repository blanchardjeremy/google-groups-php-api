<?php
/**
 * @author Jeremy Blanchard (auzigog)
 * @version dev1
 */


class GoogleGroupsAPI {

  // Default user agent to send while visiting pages.
  const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9';

  // Default welcome message to send to a user when adding them to a group. %s
  //  is replaced with the GROUP_SHORTNAME
  const DEFAULT_WELCOME_MESSAGE = 'Welcome to the group! Send an email to %s@googlegroups.com to discuss items with the group.';

  // SimpleTest browser
  protected $broswer;

  // Name of the group currently being accessed.
  protected $group_shortname;

  /**
   * Initialize the class.
   */
  function __construct() {
    require_once('simpletest/browser.php');
    $this->browser = new SimpleBrowser();

    // Set a default user agent
    $this->setUserAgent();
  }

  /*********************************************************************
   *                   API Functions
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
    //echo $after_meta_redirect_page; die();

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
    //print_rr($user_id, 'user id');
    $user_id = $matches[1][1];

    // Remove the given user id
    $this->browser->setField('membership_type', 'unsub');
    $this->browser->setField('subcheckbox', $user_id);
    $after_delete_page = $this->browser->clickSubmitByName('Action.SetMembershipType');
    //print_rr($this->browser->getRequest());
  }


  /*********************************************************************
   *                   API Internal Functions
   *********************************************************************/

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
   * Sets a user agent for the browser. This helps ensure that Google Groups
   * thinks that the user. A defult user agent is set when the class is
   * initialized.
   *
   * @param $user_agent User agent to set for future page visits.
   */
  function setUserAgent($user_agent = NULL) {
    if ($user_agent == NULL) {
      $user_agent = self::DEFAULT_USER_AGENT;
    }
    $user_agent = "User-Agent:\t".$user_agent;
    $this->browser->addHeader($user_agent);
  }

}
