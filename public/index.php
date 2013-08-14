<?php

session_start();

/* error reporting on */
error_reporting(E_ALL);


/* define the site path */
$application_path = realpath(dirname(dirname(__FILE__)));
define('APPLICATION_PATH', $application_path);

ini_set('include_path', APPLICATION_PATH . '/includes');
ini_set('display_errors', 'On');

/* include the properties file */
include('config.php');

/* include the init.php file */
include 'init.php';

require_once 'commonFunctions.inc.php';
require_once 'exceptions.inc.php';

/* setting default timezone */
date_default_timezone_set("Asia/Calcutta");

$registry = new \com\Registry();

$router = new \com\Router();
$router->setPath(APPLICATION_PATH . '/controller');

//$registry->ini_errorMsgs = parse_ini_file('errorMsgs.ini', TRUE);

// LOAD CONTROLLER
$router->loader($registry);

?>
