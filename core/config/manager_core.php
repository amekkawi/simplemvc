<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class ConfigCore
{
	static function Get($name) {
		return Config::Exists($name) ? $GLOBALS['CONFIGURATION'][$name] : null; 
	}
	
	static function Set($name, $value) {
		$GLOBALS['CONFIGURATION'][$name] = $value;
	}
	
	static function Remove($name) {
		unset($GLOBALS['CONFIGURATION'][$name]);
	}
	
	static function Exists($name) {
		return isset($GLOBALS['CONFIGURATION'][$name]);
	}
	
	/**
	 * Validate config and set defaults.
	 */
	static function Validate() {
		if (!Config::Exists('appname')) {
			__MVCStartError("'appname' config was not set.");
		}
		
		if (!Config::Exists('baseurl')) {
			__MVCStartError("'baseurl' config was not set. It is likely &quot;". HTML::Encode(substr($_SERVER['SCRIPT_NAME'], 0, stripos($_SERVER['SCRIPT_NAME'], 'core/start.php'))). "&quot;.");
		}
		
		if (!Config::Exists('dbtype')) {
			__MVCStartError("'dbtype' config was not set. Example: &quot;mysql&quot;.");
		}
		
		if (!Config::Exists('dsn')) {
			__MVCStartError("'dsn' config was not set. Example: &quot;mysql://username:password@host/mydbname&quot;.");
		}
		
		if (!Config::Exists('timezone')) {
			__MVCStartError("'timezone' config was not set.");
		}
	
		if (!Config::Exists('charset')) {
			Config::Set('charset', 'ISO-8859-1');
		}
		
		// Whether or not to set $_GLOBAL['DebugInfo'] and $_GLOBAL['DebugSQL'] when DB methods fail.
		if (!Config::Exists('debugqueries')) {
			Config::Set('debugqueries', FALSE);
		}
		
		// Set the HTTP response character set. This isn't "validation" but it should be set early.
		header('Content-type: text/html; charset=' . Config::Get('charset'));
		
		// Set the timezone. This isn't just "validation" but it should be set early.
		if (!(function_exists("date_default_timezone_set") ? @(date_default_timezone_set(Config::Get('timezone'))) : @(putenv("TZ=".Config::Get('timezone'))))) {
			__MVCStartError("'timezone' config was set to an invalid identifier.");
		}
	}
	
	/**
	 * Take action that is specific to config.
	 */
	static function Process() {
		if (Config::Exists('session')) {
			Session::Init(Config::Get('session'), Config::Get('sessionid'), Config::Get('sessionname'));
		}
	}
}

?>