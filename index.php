<?php
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
setcookie("leo", '12345', time() + 3600, '/');
require 'vendor/autoload.php';
if(!isset($_SESSION)){
	session_start();
}
define('APPLICATION_PATH', dirname(__FILE__));
define('VIEW_PATH', APPLICATION_PATH.'/application/views/');
//include_once("./sphinxapi.php");
include_once(APPLICATION_PATH."/application/library/develop_test.php");
include_once(APPLICATION_PATH."/application/library/global_function.php");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application_".ini_get('yaf.environ').".ini");
define('CSS_TYPE', Yaf_Application::app()->getConfig()->css->type);
define('CSS_REL', Yaf_Application::app()->getConfig()->css->rel);

$logger = new Katzgrau\KLogger\Logger(APPLICATION_PATH.'/logs');

$r_db = new Medoo();
$w_db = new Medoo(['control_type' => 2]);

date_default_timezone_set('PRC');

$application->getDispatcher()->throwException(false);
$application->bootstrap()->run();


?>
