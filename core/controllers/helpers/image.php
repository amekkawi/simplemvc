<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

/**
 * Representation of a rectangle.
 */
class Rectangle
{
	public $x;
	public $y;
	public $width;
	public $height;
	
	function __construct($x, $y, $width = NULL, $height = NULL) {
		if (is_null($width) || is_null($height)) {
			$this->x = 0;
			$this->y = 0;
			$this->width = $x;
			$this->height = $y;
		}
		else {
			$this->x = $x;
			$this->y = $y;
			$this->width = $width;
			$this->height = $height;
		}
	}
	
	/**
	 * Validates that the next four arguments, starting at $start, are valid x, y, width and height.
	 * @param int $start The start index to search from.
	 * @param array $args The arguments to search.
	 * @return boolean TRUE if valid. Otherwise, FALSE.
	 */
	private static function IsValidXYWH($start, $args) {
		if (!is_int($args[$start]) || !is_int($args[$start + 1]) || !is_int($args[$start + 2]) || !is_int($args[$start + 3])) return false;
		if ($args[$start] < 0 || $args[$start + 1] < 0 || $args[$start + 2] <= 0 || $args[$start + 3] <= 0) return false;
		return TRUE;
	}
	
	/**
	 * Validates that the next two arguments, starting at $start, are valid width and height.
	 * @param int $start The start index to search from.
	 * @param array $args The arguments to search.
	 * @return boolean TRUE if valid. Otherwise, FALSE.
	 */
	private static function IsValidWH($start, $args) {
		if (!is_int($args[$start]) || !is_int($args[$start + 1])) return false;
		if ($args[$start] <= 0 || $args[$start + 1] <= 0) return false;
		return TRUE;
	}
	
	/**
	 * Center a smaller inner rectangle in a larger container rectangle.
	 * 
	 * Possible arguments:
	 * ---------------------
	 * Rect, Rect
	 * Rect, width, height
	 * Rect, x, y, width, height
	 * width, height, Rect
	 * x, y, width, height, Rect
	 * x, y, width, height, x, y, width, height
	 * width, height, width, height
	 * @return Rectangle
	 */
	static function GetCenteredRectangle() {
		$args = func_get_args();
		
		// Rect, Rect
		if (count($args) == 2 && is_a($args[0], "Rectangle") && is_a($args[1], "Rectangle")) {
			$container = $args[0];
			$inner = $args[1];
		}
		
		// Rect, x, y, width, height
		elseif (count($args) == 5 && is_a($args[0], "Rectangle") && self::isValidXYWH(1, $args)) {
			$container = $args[0];
			$inner = new Rectangle($args[1], $args[2], $args[3], $args[4]);
		}
		
		// Rect, width, height
		elseif (count($args) == 3 && is_a($args[0], "Rectangle") && self::IsValidWH(1, $args)) {
			$container = $args[0];
			$inner = new Rectangle($args[1], $args[2]);
		}
		
		// x, y, width, height, Rect
		elseif (count($args) == 5 && is_a($args[4], "Rectangle") && self::isValidXYWH(0, $args)) {
			$container = $args[4];
			$inner = new Rectangle($args[0], $args[1], $args[2], $args[3]);
		}
		
		// width, height, Rect
		elseif (count($args) == 3 && is_a($args[2], "Rectangle") && self::isValidWH(0, $args)) {
			$container = $args[2];
			$inner = new Rectangle($args[0], $args[1]);
		}
		
		// x, y, width, height, x, y, width, height
		elseif (count($args) == 8 && self::isValidXYWH(0, $args) && self::isValidXYWH(4, $args)) {
			$container = new Rectangle($args[0], $args[1], $args[2], $args[3]);
			$inner = new Rectangle($args[4], $args[5], $args[6], $args[7]);
		}
		
		// width, height, width, height
		elseif (count($args) == 4 && self::isValidWH(0, $args) && self::isValidWH(2, $args)) {
			$container = new Rectangle($args[0], $args[1]);
			$inner = new Rectangle($args[2], $args[3]);
		}
		
		else {
			trigger_error("Invalid number of arguments for GetCenteredRectangle()", E_USER_ERROR);
		}
		
		$x = $container->x;
		$y = $container->y;
		$width = $inner->width;
		$height = $inner->height;
		
		if ($container->width < $inner->width) {
			$width = $container->width;
			//$x = 0;
		}
		else if ($container->width - $inner->width > 0) {
			$x = floor(($container->width - $inner->width) / 2) + $container->x;
		}
		
		if ($container->height < $inner->height) {
			$height = $container->height;
			//$y = 0;
		}
		else if ($container->height - $inner->height > 0) {
			$y = floor(($container->height - $inner->height) / 2) + $container->y;
		}
		
		return new Rectangle($x, $y, $width, $height);
	}
	
	/**
	 * Determine the size a inner rectangle would need to be to fit within a container rectangle.
	 * 
	 * Possible arguments:
	 * ---------------------
	 * Rect, Rect
	 * Rect, width, height
	 * Rect, x, y, width, height
	 * width, height, Rect
	 * x, y, width, height, Rect
	 * x, y, width, height, x, y, width, height
	 * width, height, width, height
	 * 
	 * First argument can be true to expand the inner rectangle if it is smaller.
	 * 
	 * @return Rectangle
	 */
	static function GetProportionalSize() {
		$args = func_get_args();
		$expand = false;
		
		// The first argument can be boolean which allows the inner to expand if smaller than the cotainer.
		if (count($args) > 0 && is_bool($args[0])) $expand = array_shift($args); 
		
		// Rect, Rect
		if (count($args) == 2 && is_a($args[0], "Rectangle") && is_a($args[1], "Rectangle")) {
			$container = $args[0];
			$inner = $args[1];
		}
		
		// Rect, width, height
		elseif (count($args) == 3 && is_a($args[0], "Rectangle") && self::IsValidWH(1, $args)) {
			$container = $args[0];
			$inner = new Rectangle($args[1], $args[2]);
		}
		
		// width, height, Rect
		elseif (count($args) == 3 && is_a($args[2], "Rectangle") && self::isValidWH(0, $args)) {
			$container = $args[2];
			$inner = new Rectangle($args[0], $args[1]);
		}
		
		// width, height, width, height
		elseif (count($args) == 4 && self::isValidWH(0, $args) && self::isValidWH(2, $args)) {
			$container = new Rectangle($args[0], $args[1]);
			$inner = new Rectangle($args[2], $args[3]);
		}
		
		else {
			trigger_error("Invalid number of arguments for GetCenteredRectangle()", E_USER_ERROR);
		}
		
		$scaledHeight = max(min($container->height, 1), floor(($inner->height * $container->width) / $inner->width));
		$scaledWidth = max(min($container->width, 1), floor(($inner->width * $container->height) / $inner->height));
		
		if (!$expand && $inner->width <= $container->width && $inner->height <= $container->height) {
			return $inner;
		}
		else if ($scaledHeight <= $container->height) {
			return new Rectangle($container->width, $scaledHeight);
		}
		else {
			return new Rectangle($scaledWidth, $container->height);
		}
	}
}

/**
 * Utilities for reading, manipulating, and writing images.
 */
class Image
{
	static function CreateThumbnail($src, $output, $width, $height = NULL, $quality = NULL) {
		if (is_a($width, "Rectangle")) {
			$quality = $height;
			$height = $width->height;
			$width = $width->width;
		}
		
		if (is_null($quality)) $quality = 65;
		
		$data = getimagesize($src);
		
		// Return false if the image could not be processed.
		if ($data === FALSE) return FALSE;
		
		switch ($data['mime']) {
			case "image/jpeg":
				$imgsrc = imagecreatefromjpeg($src);
				break;
			case "image/png":
				$imgsrc = imagecreatefrompng($src);
				break;
			case "image/gif":
				$imgsrc = imagecreatefromgif($src);
				break;
			default:
				$imgsrc = imagecreatefromstring(file_get_contents($src));
		}
		
		// Return false if the image could not be processed.
		if ($imgsrc === FALSE) return FALSE;
		
		$containerRect = new Rectangle($width, $height);
		$origRect = new Rectangle(imageSX($imgsrc), imageSY($imgsrc));
		$newRect = Rectangle::GetProportionalSize(true, $containerRect, $origRect);
		
		$imgdest = imagecreatetruecolor($newRect->width, $newRect->height);
		imagecopyresampled($imgdest, $imgsrc, 0, 0, 0, 0, $newRect->width, $newRect->height, $origRect->width, $origRect->height); 
		imagejpeg($imgdest, $output); 
		
		// Clean up.
		imagedestroy($imgsrc);
		imagedestroy($imgdest);
		
		return TRUE;
	}
}
?>