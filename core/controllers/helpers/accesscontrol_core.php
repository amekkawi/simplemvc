<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class ACCore
{
	static function Login($username, $groups) {
		Session::Set('AC_LOGGEDINUSER', $username);
		Session::Set('AC_USER', $username);
		Session::Set('AC_GROUPS', $groups);
	}
	
	static function Sudo($username, $groups) {
		Session::Set('AC_USER', $username);
		Session::Set('AC_GROUPS', $groups);
	}
	
	static function Logout() {
		Session::Set('AC_LOGGEDINUSER', null);
		Session::Remove('AC_LOGGEDINUSER');
		
		Session::Set('AC_USER', null);
		Session::Remove('AC_USER');
		
		Session::Set('AC_GROUPS', null);
		Session::Remove('AC_GROUPS');
	}
	
	static function GetLoggedInUser() {
		return Session::Exists('AC_LOGGEDINUSER') ? Session::Get('AC_LOGGEDINUSER') : null;
	}
	
	static function GetUser() {
		return Session::Exists('AC_USER') ? Session::Get('AC_USER') : null;
	}
	
	static function GetGroups() {
		return Session::Exists('AC_GROUPS') ? Session::Get('AC_GROUPS') : array();
	}
	
	static function InGroup($names) {
		$groups = AC::GetGroups();
		if (is_array($names)) {
			foreach ($names as $n) {
				if (array_key_exists($n, $groups)) return true;
			}
			return false;
		}
		else {
			return array_key_exists($names, $groups);
		}
	}
}

?>