<?php
require_once(dirname(__FILE__) . '/../google-groups-php-api.php');

session_start();

createGroup();

function createGroup() {
  $action = $_REQUEST['action'];
  if ($action == 'show_image') {
    showImage();
  } elseif ($action == 'finish_create') {
    finishCreate();
  } else {
    init();
  }
}


function init() {
  // *************** Set your username and password here
  $email = '';
  $password = '';

  // *************** Set the parameters for the group that you want to create
  $group_name =         'My Group Name';
  $group_shortname =    'my-group-name';
  $group_description =  'My group is awesome! And automated through GGPHPAPI.';
  $group_privacy =      'private';


  // Initialize the API and login
  $gg = new GoogleGroupsAPI();
  $gg->login($email, $password);

  // Create the group!
  try {
    $gg->createGroup($group_name, $group_shortname, $group_description, $group_privacy);
  }
  catch (Exception $e) {
    die($e->message);
  }

  // Save the state of the program for use after the user answers the CAPTCHA
  $state_save_str = $gg->saveState();
  $_SESSION['state_save_str'] = $state_save_str;

  showCreateForm();
}

function showCreateForm() {
  ?>
  <img src="?action=show_image" />
  <form action="?">
    <label for="answer">Answer to CAPTCHA:</label>
    <input type="hidden" name="action" value="finish_create" />
    <input type="test" name="answer" />
    <input type="submit" value="Submit" />
  </form>
  <?php
}

function showImage() {
  $gg = unserialize($_SESSION['state_save_str']);
  $gg->createGroupShowImage();
}

function finishCreate() {
  $gg = unserialize($_SESSION['state_save_str']);
  $answer = $_REQUEST['answer'];

  try {
    $gg->createGroupAnswerCaptcha($answer);
  }
  catch (Exception $e) {
    die($e->message);
  }


  echo '<br /><a href="?">Start over</a>';

}