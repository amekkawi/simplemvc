<?php

// Based on the C# NSort library.
// Original is likely at http://www.codeproject.com/KB/recipes/cssorters.aspx by Marc Clifton

interface IComparable {
	// Returns -1 if $this < $obj
	// Returns 1 if $this > $obj
	// Otherwise, returns 0
	function CompareTo($obj);
}

interface IComparer {
	// Returns -1 if $x < $y
	// Returns 1 if $x > $y
	// Otherwise, returns 0
	function Compare($x, $y);
}

interface ISorter {
	public function Sort(array &$list);
}

interface ISwap {
	function Swap(array &$array, $left, $right);
	function Copy(array &$array, $left, $right);
	function Set(array &$array, $left, $obj);
	function Remove(array &$array, $index);
}

class DefaultSwap implements ISwap {
	function Swap(array &$array, $left, $right) {
		$swap = $array[$left];
		$array[$left] = $array[$right];
		$array[$right] = $swap;
	}
	
	function Copy(array &$array, $left, $right) {
		$array[$left] = $array[$right];
	}

	function Set(array &$array, $left, $obj) {
		$array[$left] = $obj;
	}

	function Remove(array &$array, $index) {
		return $array[$index];
	}
}

class ComparableComparer implements IComparer {
	private $caseInsensitive;
	
	function __constructor($caseInsensitive = FALSE) {
		$this->caseInsensitive = $caseInsensitive;
	}
	
	function Compare($x, $y) {
		$debug = false;
		if (is_null($x) && is_null($y)) {
			if ($debug) echo "<p>Equal nulls</p>";
			return 0;
		}
		else if (is_null($x)) {
			if ($debug) echo "<p>x nulls</p>";
			return -1;
		}
		else if (is_null($y)) {
			if ($debug) echo "<p>y nulls</p>";
			return 1;
		}
		else if (is_object($x) && array_key_exists('IComparable', class_implements($x))) {
			if ($debug) echo "<p>x is comparable</p>";
			return $x->CompareTo($y);
		}
		else if (is_object($y) && array_key_exists('IComparable', class_implements($y))) {
			if ($debug) echo "<p>y is comparable</p>";
			return $y->CompareTo($x) * -1;
		}
		else if (is_object($x) || is_object($y)) {
			trigger_error("ComparableComparer cannot compare objects unless one of them implements IComparable.", E_USER_ERROR);
		}
		else {
			if ($this->caseInsensitive) {
				if (is_string($x)) $x = strtolower($x);
				if (is_string($y)) $y = strtolower($y);
			}
			if ($debug) echo "<p>Compare $x to $y</p>";
			if ($x < $y) return -1;
			elseif ($x > $y) return 1;
			else return 0;
		}
	}
}

abstract class SwapSorter implements ISorter {
	protected $comparer;
	protected $swapper;

	function __construct(IComparer $comparer = NULL, ISwap $swapper = NULL) {
		if (is_null($comparer) || is_null($swrapper)) {
			$this->comparer = new ComparableComparer();
			$this->swapper = new DefaultSwap();
		}
		else {
			$this->comparer = $comparer;
			$this->swapper = $swapper;
		}
	}
	
	function Comparer(IComparer $comparer = NULL) {
		if (is_null($comparer)) {
			return $this->comparer;
		}
		else {
			$this->comparer = $comparer;
		}
	}
	
	function Swapper(ISwap $swapper = NULL) {
		if (is_null($swapper)) {
			return $this->swapper;
		}
		else {
			$this->swapper = $swapper;
		}
	}
}

class InPlaceMergeSort extends SwapSorter {
	function Sort(array &$list, $fromPos = NULL, $toPos = NULL) {
		if (is_null($fromPos) || is_null($toPos)) {
			$fromPos = 0;
			$toPos = count($list) - 1;
		}

		if ($fromPos < $toPos) {
			$mid = floor(($fromPos + $toPos) / 2);

			$this->Sort($list, $fromPos, $mid);
			$this->Sort($list, $mid + 1, $toPos);

			$end_low = $mid;
			$start_high = $mid + 1;

			while ($fromPos <= $end_low & $start_high <= $toPos) {
				if ($this->comparer->Compare($list[$fromPos], $list[$start_high]) <= 0) {
					$fromPos++;
				}
				else {
					$tmp = $list[$start_high];
					for ($i = $start_high - 1; $i >= $fromPos; $i--) {
						$this->swapper->Set($list, $i + 1, $list[$i]);
					}
					$this->swapper->Set($list, $fromPos, $tmp);
					$fromPos++;
					$end_low++;
					$start_high++;
				}
			}
		}
	}
}

/**
 * Find an object in a sorted array.
 * @param array     $haystack
 * @param object    $needle
 * @param int       $low [optional]
 * @param int       $high [optional]
 * @param IComparer $comparer [optional]
 * @link http://www.codecodex.com/wiki/Binary_search#PHP Original Version
 * @return 
 */
function BinaryArraySearch(array &$haystack, $needle, IComparer $comparer = NULL) {
	if (is_null($comparer)) {
		$comparer = new ComparableComparer();
	}
	
	$low = 0;
	$high = count($haystack) - 1;
	
	while ($low <= $high) {
		
		// Get the middle index
		$middle = intval(($low + $high) / 2);
		
		// Key has been found.
		if ($comparer->Compare($haystack[$middle], $needle) == 0) {
			return $middle;
		}
		
		// Key may be in the left part.
		elseif ($comparer->Compare($needle, $haystack[$middle]) < 0) {
			$high = $middle - 1;
		}
		
		// Key may be in the right part.
		else {
			$low = $middle + 1;
		}
	}
	
	// Could not be found.
	return ($low + 1) * -1;
}

/**
 * Insert an object into a sorted array.
 * @param array     $array
 * @param object    $new
 * @param boolean   $duplicates [optional] Whether or not to allow duplicates.
 * @param IComparer $comparer [optional]
 * @return 
 */
function BinaryArrayInsert(array &$array, $new, $duplicates = FALSE, IComparer $comparer = NULL) {
	if (is_object($duplicates)) {
		$duplicates = FALSE;
		$comparer = $duplicates;
	}
	
	$index = BinaryArraySearch($array, $new, $comparer);
	
	// Return if $new is in $array and duplicates are not allowed.
	if ($index >= 0 && !$duplicates)
		return;
	
	if ($index < 0)
		$index = ($index * -1) - 1;
	
	if ($index == 0) {
		$array = array_merge(array($new), $array);
	}
	elseif ($index == count($array)) {
		$array = array_merge($array, array($new));
	}
	else {
		$left = array_slice($array, 0, $index);
		$right = array_slice($array, $index);
		$array = array_merge($left, array($new), $right);
	}
}

?>