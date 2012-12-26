<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

abstract class SessionCore {
	
	static $_id = NULL;
	static $_mode = NULL;
	static $_data = NULL;
	
	static function Init($mode, $idLocation = NULL, $name = NULL) {
		Session::$_mode = $mode;
		$sessionVarName = (is_string($name) ? $name . '_' : '') . 'sessid';
		
		if ($mode == "db") {
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
			header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
			
			if ($idLocation == "querystring") {
				if (isset($_GET[$sessionVarName])) Session::$_id = $_GET[$sessionVarName];
				elseif (isset($_POST[$sessionVarName])) Session::$_id = $_POST[$sessionVarName];
			}
			else {
				if (isset($_COOKIE[$sessionVarName])) Session::$_id = $_COOKIE[$sessionVarName];
			}
			
			if (is_null(Session::$_id)) {
				Session::$_id = md5(uniqid());
				setcookie($sessionVarName, Session::$_id, 0, HTML::URL(''));
			}
		}
		else {
			if ($idLocation == "querystring") {
				ini_set('session.use_cookies', FALSE);
				if (isset($_GET[$sessionVarName])) session_id($_GET[$sessionVarName]);
				elseif (isset($_POST[$sessionVarName])) session_id($_POST[$sessionVarName]);
			}
			else {
				if (is_string($name)) session_name($name . '_sessid');
			}
			
			session_start();
			
			Session::$_id = session_id();
		}
	}
	
	/**
	 * PROTECTED
	 */
	static function _RetrieveData() {
		if (Session::$_mode == "db") {
			trigger_error('To use DB session storage you must override SessionCore::_RetrieveData()', E_USER_ERROR);
		}
		else {
			// Data should just be a pointer to $_SESSION.
			Session::$_data =& $_SESSION;
		}
	}
	
	/**
	 * PROTECTED
	 */
	static function _StoreData() {
		if (Session::$_mode == "db") {
			trigger_error('To use DB session storage you must override SessionCore::_StoreData()', E_USER_ERROR);
		}
	}
	
	/**
	 * Refresh the expiration of the session (if necessary).
	 */
	static function RefreshExpiration() {
	if (Session::$_mode == "db") {
			trigger_error('To use DB session storage you must override SessionCore::RefreshExpiration()', E_USER_ERROR);
		}
	}
	
	/**
	 * Get the ID for this session.
	 */
	static function ID() {
		return Session::$_id;
	}
	
	/**
	 * Get the mode, which defines where the session data is stored.
	 * Example: 'db'
	 */
	static function Mode() {
		return Session::$_mode;
	}
	
	/**
	 * Determine if a variable exists within the session data.
	 * @param string $name
	 * @return boolean
	 */
	static function Exists($name) {
		// Retrieve the data if it has not yet been retrieved.
		if (is_null(Session::$_data)) Session::_RetrieveData();
		
		return array_key_exists($name, Session::$_data);
	}
	
	/**
	 * Get all, or a specific, variable from teh session data.
	 * @param string $name
	 */
	static function Get($name = NULL) {
		// Retrieve the data if it has not yet been retrieved.
		if (is_null(Session::$_data)) Session::_RetrieveData();
		
		if (is_null($name)) {
			return Session::$_data;
		}
		else {
			return Session::$_data[$name];
		}
	}
	
	/**
	 * Set a variable within the session data.
	 * @param string $name
	 * @param mixed $value
	 */
	static function Set($name, $value) {
		// Retrieve the data if it has not yet been retrieved.
		if (is_null(Session::$_data)) Session::_RetrieveData();
		
		Session::$_data[$name] = $value;
		Session::_StoreData();
	}
	
	/**
	 * Remove a variable from the session data.
	 * @param string $name
	 */
	static function Remove($name) {
		// Retrieve the data if it has not yet been retrieved.
		if (is_null(Session::$_data)) Session::_RetrieveData();
		
		unset(Session::$_data[$name]);
		Session::_StoreData();
	}
	
	/**
	 * Remove all variables from the session data.
	 */
	static function Clear() {
		// Retrieve the data if it has not yet been retrieved.
		if (is_null(Session::$_data)) Session::_RetrieveData();
		
		foreach (Session::$_data as $key => $value) {
			unset(Session::$_data[$key]);
		}
		Session::_StoreData();
	}
	
}

?>