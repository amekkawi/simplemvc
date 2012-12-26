<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

abstract class VCore
{
	static function Check($type, &$value) {
		if (is_string($value) && !Charset::Compliant($value)) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	
	final static function Set(&$variable, &$value, $type = NULL, $default = NULL) {
		if (isset($value)) {
			if ($type === NULL || V::Check($type, $value)) {
				$variable = $value;
				return TRUE;
			}
		}
		
		// Set the var to the default if the value was invalid and a default was set.
		if ($default !== NULL) {
			$variable = $default;
		}
		
		return FALSE;
	}
}

?>