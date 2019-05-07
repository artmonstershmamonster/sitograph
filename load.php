<?php
// *** ./load.php
// *** DO NOT EDIT THIS FILE
// *** WILL BE OVERWRITTEN DURING UPDATE

if (!headers_sent()) {
	session_start();
}

// include config file
// if config.php exists it will be included
// otherwise config-sample will be used and installation module will be started
if (file_exists(__DIR__."/config.php")) {
    require_once(__DIR__."/config.php");
	
	if (empty($_SESSION["msv_install_step"])) {
		define("MSV_INSTALED", true);
	} else {
		define("MSV_INSTALED", false);
	}
} elseif (file_exists(__DIR__."/config-sample.php")) {
    require_once(__DIR__."/config-sample.php");
	define("MSV_INSTALED", false);
} else {
	die("Can't load in configuration file");
}

if (!defined("ABS")) die(".");

if (defined("PHP_HIDE_ERRORS") && PHP_HIDE_ERRORS) {
	ini_set("display_errors", 0);
	error_reporting(0);
} else {
	ini_set("display_errors", 1);
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

if (defined("PHP_LOCALE")) {
	setlocale(LC_ALL, PHP_LOCALE); 
}

if (defined("PHP_TIMEZONE")) {
	date_default_timezone_set(PHP_TIMEZONE);
}

set_time_limit(1000);

$pathInclude = ABS."/include";
$pathIncludeLocal = "/include";

define("ABS_INCLUDE", $pathInclude);
define("ABS_MODULE", $pathInclude."/module");
define("ABS_CUSTOM", $pathInclude."/custom");
define("ABS_TEMPLATE", ABS."/templates");

define("LOCAL_INCLUDE", $pathIncludeLocal);
define("LOCAL_MODULE", $pathIncludeLocal."/module");
define("LOCAL_TEMPLATE", "templates");

require_once(ABS_INCLUDE."/util.php");
require_once(ABS_INCLUDE."/class.module.php");
require_once(ABS_INCLUDE."/class.msv.php");
