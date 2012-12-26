<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class IO {
	
	static function MakeDir($path, $perm = NULL) {
		$mkdirs = array();
		while (!is_dir($path)) {
			$mkdirs[count($mkdirs)] = basename($path);
			$path = dirname($path);
		}
		
		// Get the permissions from the first existing dir, if not passed as an argument.
		if (is_null($perm)) $perm = fileperms($path);
	
		for ($i = count($mkdirs) - 1; $i >= 0; $i--) {
			$path .= DS.$mkdirs[$i];
			
			// Create the directory.
			if (!(@mkdir($path))) return FALSE;
			
			// Match the directory matches the base.
			chmod($path, $perm);
		}
		
		return TRUE;
	}
	
	static function GetFileList($path, $comparer = NULL) {
		$arr = array();
		$dh = @opendir($path);
		if ($dh === false) return false;
		
		$useComparer = is_object($comparer) && array_key_exists('IComparable', class_implements($comparer));
		
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != ".." && preg_match('/\.(jpg|gif|png|bmp)$/i', $file)) {
				if ($useComparer) {
					BinaryArrayInsert($arr, $file, true, $comparer);
				}
				else {
					$arr[count($arr)] = $file;
				}
			}
		}
		
		@closedir($dh);
		return $arr;
	}
	
	static function ConcatPath($sep) {
		$str = "";
		$args = func_get_args();
		array_shift($args); // Remove first arg, which is $sep.
		
		for ($i = 0; $i < count($args); $i++) {
			$args[$i] = $args[$i].'';
			if ($i > 0 && strpos($args[$i], $sep) === 0) { $args[$i] = substr($args[$i], 1); }
			if ($i + 1 < count($args) && strrpos($args[$i], $sep) === strlen($args[$i]) - 1) { $args[$i] = substr($args[$i], 0, strlen($args[$i]) - 1); }
			if ($i > 0) $str .= $sep;
			$str .= $args[$i];
		}
		
		return $str;
	}
	
}
?>