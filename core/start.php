<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

/**
 * Initialize the MVC and then call the controller dispatcher.
 */

// A simple error page used until the dispatcher.
function __MVCStartError($msg) {
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><?php
	?><html xmlns="http://www.w3.org/1999/xhtml"><?php
	?><head><title>Simple MVC Startup Error</title></head><?php
	?><body><h1>Startup Error</h1><p><?php echo $msg; ?></p></body><?php
	?></html><?php
	exit;
}

/* ===============================================
 *           Directory Path Definitions
 * =============================================== */

define('DS', DIRECTORY_SEPARATOR);

// Root and config directories.
define('ROOT', dirname(dirname(__FILE__)));
define('CONFIG', ROOT.DS.'config');

// Core directory and sub directories.
define('CORE', ROOT.DS."core");
define('CORE_CONFIG', CORE.DS.'config');
define('CORE_MODELSBASE', CORE.DS.'models');
define('CORE_CONTROLLERS', CORE.DS.'controllers');
define('CORE_VIEWS', CORE.DS.'views');
define('CORE_VIEWHELPERS', CORE_VIEWS.DS."helpers");
define('CORE_ERRORS', CORE_VIEWS.DS.'errors');
define('CORE_PAGES', CORE_VIEWS.DS.'pages');

// App directory and sub directories.
define('APP', ROOT.DS.'app');
define('WEBROOT', APP.DS.'webroot');
define('CONTROLLERS', APP.DS.'controllers');
define('SCRIPTS', APP.DS.'scripts');
define('VIEWS', APP.DS.'views');
define('LAYOUTS', VIEWS.DS.'layouts');
define('ERRORS', VIEWS.DS.'errors');
define('PAGES', VIEWS.DS.'pages');


/* ===============================================
 *            Pre-Cofig Core Includes
 * =============================================== */

require_once(CORE_CONTROLLERS.DS."controller.php");
require_once(CORE_MODELSBASE.DS."model.php");

// Include early so it can be used in error messages.
require_once(CORE_VIEWHELPERS.DS."html.php");

// Pear::MDB2 library.
@(include_once('MDB2.php')) or __MVCStartError('<a href="http://pear.php.net/package/MDB2/">PEAR::MDB2</a> is missing.');

// Include general DB helper (optionally inherited in post-config).
require_once(CORE_MODELSBASE.DS."db_super.php");

// Include the config manager (optionally inherited).
require_once(CORE_CONFIG.DS."manager_core.php");
file_exists(CONFIG.DS."manager.php") ? require_once(CONFIG.DS."manager.php") : require_once(CORE_CONFIG.DS."manager.php");


/* ===============================================
 *                 Configuration
 * =============================================== */

// Include the general config
@(include_once(CONFIG.DS."general.php")) or __MVCStartError('Configuration file (config'.DS.'general.php) is missing.');

// Validate config and set defaults.
Config::Validate();


/* ===============================================
 *     Post-Cofig Directory Path Definitions
 * =============================================== */

// Determine the models directory based on the dbtype config.
define('CORE_MODELS', CORE_MODELSBASE.DS.Config::Get('dbtype'));
define('MODELS', APP.DS.'models'.DS.Config::Get('dbtype'));


/* ===============================================
 *            Post-Cofig Core Includes
 * =============================================== */

// DB-specific overrides to the general DB helper.
require_once(CORE_MODELS.DS."db_core.php");
file_exists(MODELS.DS."db.php") ? require_once(MODELS.DS."db.php") : require_once(CORE_MODELSBASE.DS."db.php");

// Character Set Helper
if (strpos('|ASCII|Windows-1251|ISO-8859-1|ISO-8859-2|ISO-8859-3|ISO-8859-4|ISO-8859-5|ISO-8859-6|ISO-8859-7|ISO-8859-8|ISO-8859-9|ISO-8859-10|ISO-8859-11|ISO-8859-12|ISO-8859-13|ISO-8859-14|ISO-8859-15|', '|'.Config::Get('charset').'|') !== FALSE) {
	require_once(CORE_CONTROLLERS.DS."helpers".DS."charset".DS."single-byte.php");
}
else {
	require_once(CORE_CONTROLLERS.DS."helpers".DS."charset".DS."multi-byte.php");
}
if (file_exists(CONTROLLERS.DS."helpers".DS. "charset.php")) {
	require_once(CONTROLLERS.DS."helpers".DS. "charset.php");
}
else {
	require_once(CORE_CONTROLLERS.DS."helpers".DS."charset".DS."charset.php");
}
Charset::Init();

// Core helpers (optionally inherited).
Controller::IncludeHelper('controller', 'validation', 'V', true);
Controller::IncludeHelper('controller', 'accesscontrol', 'AC', true);
Controller::IncludeHelper('controller', 'session', 'Session', true);


/* ===============================================
 *             Final Config Processing
 * =============================================== */

Config::Process();


/* ===============================================
 *               Open DB & Dispatch
 * =============================================== */

// Open a connection to the database.
if (is_string($result = DB::Open())) {
	Controller::ShowError('db_connection', array_merge(
		array('errormessage' => $result),
		Config::Get('debugqueries') === TRUE ? array('debuginfo' => $GLOBALS['DebugInfo']) : array()
	));
}

// TODO: Remove debug code.
if (isset($_GET['url']) && $_GET['url'] == "info") {
	phpinfo(); exit;
}

if (isset($argc) && $argc >= 3 && $argv[1] == "script") {
	if (file_exists(SCRIPTS.DS.$argv[2].'.php')) {
		$arguments = array_slice($argv, 2);
		unset($argv); unset($argc);
		require(SCRIPTS.DS.array_shift($arguments).'.php');
	}
	else {
		echo "ERROR: Script " . $argv[2] . ".php does not exist in " . SCRIPTS . "\n";
	}
}
else {
	// Dispatch the controller.
	Controller::Dispatch();
}

// Close any open DB connection.
DB::Close();
?>