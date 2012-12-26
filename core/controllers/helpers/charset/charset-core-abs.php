<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

require_once('charset-core-abs.php');
abstract class CharsetCoreAbs {
		
	abstract static function Init();
	abstract static function StrLen($str);
	abstract static function StrToLower($str);
	abstract static function StrToUpper($str);
	abstract static function Clean($str);
	abstract static function Compliant($str);
	abstract static function Preg_Match($pattern, $str, &$regs);
	abstract static function Preg_Match_All($pattern, $str, &$regs);
	abstract static function Replace($pattern, $replace, $str, $opt = '');
	abstract static function Str_Replace($search, $replace, $str, $count = NULL);
	abstract static function StrPos($haystack, $needle, $offset = 0);
	abstract static function StrIPos($haystack, $needle, $offset = 0);
	abstract static function StrRPos($haystack, $needle, $offset = 0);
	abstract static function StrRIPos($haystack, $needle, $offset = 0);
	abstract static function SubStr($str, $start, $length = -1);
	
	static function UCFirst($str) {
		return Charset::StrToUpper(Charset::SubStr($str, 0, 1)) . Charset::SubStr($str, 1);
	}
	
}

?>