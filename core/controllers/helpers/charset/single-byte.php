<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

require_once('charset-core-abs.php');
abstract class CharsetCore extends CharsetCoreAbs {
	
	static function Init() {
		
	}
	
	static function StrLen($str) {
		return strlen($str);
	}
	
	static function StrToLower($str) {
		return strtolower($str);
	}
	
	static function StrToUpper($str) {
		return strtoupper($str);
	}
	
	static function Compliant($str) {
		if (Config::Get('charset') == 'ISO-8859-1') {
			return !preg_match('/[\x00-\x1F\x7F-\x9F]/', $str);
		}
		
		return TRUE;
	}
	
	static function Clean($str) {
		if (Config::Get('charset') == 'ISO-8859-1') {
			
			// Fix Windows-1252 characters
			$str = str_replace(array(
				chr(130), // Single Low-9 Quotation Mark
				chr(132), // Double Low-9 Quotation Mark
				chr(133), // Horizontal Elipsis (...)
				chr(145), // Left Single Quotation Mark
				chr(146), // Right Single Quotation Mark
				chr(147), // Left Double Quotation Mark
				chr(148), // Right Double Quotation Mark
				chr(149), // Bullet
				chr(150), // En Dash
				chr(151), // Em Dash
				chr(153)  // Trademark
			), array(
				"'",      // Single Low-9 Quotation Mark
				'"',      // Double Low-9 Quotation Mark
				'...',    // Horizontal Elipsis (...)
				"'",      // Left Single Quotation Mark
				"'",      // Right Single Quotation Mark
				'"',      // Left Double Quotation Mark
				'"',      // Right Double Quotation Mark
				chr(183), // Bullet
				'-',      // En Dash
				'-',      // Em Dash
				'(TM)'    // Trademark
			), $str);
		}
		
		// Remove any out-of-range characters.
		$str = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $str);
		
		return $str;
	}
	
	static function Preg_Match($pattern, $str, &$regs) {
		return preg_match($pattern, $str, &$regs);
	}
	
	static function Preg_Match_All($pattern, $str, &$regs) {
		return preg_match_all($pattern, $str, &$regs);
	}
	
	static function Replace($pattern, $replace, $str, $opt = '') {
		return preg_replace($pattern, $replace, $str);
	}
	
	static function Str_Replace($search, $replace, $str, $count = NULL) {
		return str_replace($search, $replace, $str, $count);
	}
	
	static function StrPos($haystack, $needle, $offset = 0) {
		return strpos($haystack, $needle, $offset);
	}

	static function StrIPos($haystack, $needle, $offset = 0) {
		return stripos($haystack, $needle, $offset);
	}
	
	static function StrRPos($haystack, $needle, $offset = 0) {
		return strrpos($haystack, $needle, $offset);
	}
	
	static function StrRIPos($haystack, $needle, $offset = 0) {
		return strripos($haystack, $needle, $offset);
	}
	
	static function SubStr($str, $start, $length = -1) {
		return substr($str, $start, $length);
	}
}

?>