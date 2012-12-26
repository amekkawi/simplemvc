<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class Model {
	static function Load($name) {
		$name = ucwords(strtolower($name));
		$modelFile = MODELS.DS.strtolower($name).".php";
		
		if (!file_exists($modelFile)) Controller::ShowError('missing_model', array('modelfile' => $modelFile));
		
		include_once($modelFile); 
		
		if (!class_exists('m'.$name)) {
			Controller::ShowError('missing_modelclass', array('modelfile' => $modelFile, 'modelclass' => $name));
		}
	}
}

?>