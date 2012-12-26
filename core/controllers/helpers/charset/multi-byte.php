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
		
	// TODO: Speed test vs custom implementations.
	
	static function Init() {
		mb_internal_encoding(Config::Get('charset'));
		mb_regex_encoding(Config::Get('charset'));
		mb_detect_order('ASCII, JIS, UTF-8, EUC-JP, SJIS, ' . Config::Get('charset'));
	}
	
	static function StrLen($str) {
		return mb_strlen($str, Config::Get('charset'));
	}
	
	static function StrToLower($str) {
		return mb_strtolower($str, Config::Get('charset'));
	}
	
	static function StrToUpper($str) {
		return mb_strtoupper($str, Config::Get('charset'));
	}
	
	static function Compliant($str) {
	    return $str === Charset::Clean($str);
	}
	
	static function Clean($str) {
	    $fs = Config::Get('charset') == 'UTF-8' ? 'UTF-32' : Config::Get('charset');
	    $ts = Config::Get('charset') == 'UTF-32' ? 'UTF-8' : Config::Get('charset');
	    return mb_convert_encoding ( mb_convert_encoding ( $str, $fs, $ts ), $ts, $fs );
	}
	
	static function Preg_Match($pattern, $str, &$regs) {
		return Charset::_Matcher($pattern, $str, &$regs, 1);
	}
	
	static function Preg_Match_All($pattern, $str, &$regs) {
		return Charset::_Matcher($pattern, $str, &$regs, 0);
	}
	
	/**
	 * PROTECTED
	 */
	static function _Matcher($pattern, $str, &$regs, $limit) {
		
		// Fail if invalid byte streams.
		if (!Charset::Compliant($str) || !Charset::Compliant($pattern)) return FALSE;
		
		$result = 0;
		$regs = array();
		$grouped = false;
		
		$delim = Charset::SubStr($pattern, 0, 1);
		$lastdelim = strrpos($pattern, $delim);
		
		$opts = substr($pattern, $lastdelim + strlen($delim));
		$pattern = substr($pattern, strlen($delim), $lastdelim - strlen($delim));
		
		if (!mb_ereg_search_init($str, $pattern, $opts)) return FALSE;
		
		// Return 0 if string does not match pattern.
		if (!mb_ereg_search()) return 0;
		
		$reg = mb_ereg_search_getregs();
		
		if (is_array($reg)) {
			$grouped = true;
			for ($i = 0; $i < count($reg); $i++) {
				$regs[$i] = array($reg[$i]);
			}
		}
		else {
			array_push($regs, $reg);
		}
		
		while (($reg = mb_ereg_search_regs()) !== FALSE) {
			if (is_array($reg)) {
				for ($i = 0; $i < count($reg); $i++) {
					array_push($regs[$i], $reg[$i]);
				}
			}
			else {
				array_push($regs, $reg);
			}
		}
		
		return count($grouped ? $regs[0] : $regs);
	}
	
	static function Replace($pattern, $replace, $str, $opt = '') {
		return mb_ereg_match($pattern, $str);
	}
	
	static function Str_Replace($search, $replace, $str, $count = NULL) {
		if (!Charset::Compliant($search) || !Charset::Compliant($replace)) return FALSE;
		return str_replace($search, $replace, $str, $count);
	}
	
	static function StrPos($haystack, $needle, $offset = 0) {
		return mb_strpos($haystack, $needle, $offset, Config::Get('charset'));
	}

	static function StrIPos($haystack, $needle, $offset = 0) {
		return mb_stripos($haystack, $needle, $offset, Config::Get('charset'));
	}
	
	static function StrRPos($haystack, $needle, $offset = 0) {
		return mb_strrpos($haystack, $needle, $offset, Config::Get('charset'));
	}
	
	static function StrRIPos($haystack, $needle, $offset = 0) {
		return mb_strripos($haystack, $needle, $offset, Config::Get('charset'));
	}
	
	static function SubStr($str, $start, $length = -1) {
		return mb_substr($str, $start, $length, Config::Get('charset'));
	}
	
}

?>