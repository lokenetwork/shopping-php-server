<?php

define('APPLICATION_PATH', dirname(__FILE__));
include_once(APPLICATION_PATH."/application/library/develop_test.php");
include_once(APPLICATION_PATH."/application/library/global_function.php");

$application = new Yaf_Application( APPLICATION_PATH . "/conf/application_".ini_get('yaf.environ').".ini");

date_default_timezone_set('PRC');

$application->getDispatcher()->throwException(false);
$application->bootstrap()->run();


?>
