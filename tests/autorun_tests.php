<?php

require_once('../../simpletest/autorun.php');
require_once('../../simpletest/web_tester.php');
//SimpleTest::prefer(new TextReporter());

class WebTests extends TestSuite {
    function WebTests() {
        $this->TestSuite('Google Groups API tests');
        $this->addFile(dirname(__FILE__) . '/basic_test.php');
    }
}
