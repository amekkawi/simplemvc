<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class Controller
{
	/**
	 * The base class name of the controller.
	 * Example: Users
	 * @var string
	 */
	protected $name = null;
	
	/**
	 * The action requested.
	 * Example: index
	 * @var string
	 */
	protected $action = null;
	
	/**
	 * The action's arguments.
	 * @var array
	 */
	protected $arguments = null;
	
	/**
	 * The layout (in app/views/layouts) to use.
	 * @var string
	 */
	protected $layout = 'default';
	
	/**
	 * Set to false to allow the controller to be run without a view.
	 * Set to a string to define the view (e.g. "album/add")
	 */
	protected $view = null;
	
	/**
	 * The text to display within the <title> tags in the layout.
	 */
	protected $pageTitle = "";
	
	/**
	 * Whether or not to load the model that has the same name as the controller.
	 * If the controller's basename is "Comments" the model loaded would also be named "Comments".
	 */
	protected $loadControllerModel = true;
	
	/**
	 * Models that will be loaded in addition to the controller's model and associated models.
	 * @var array
	 */
	protected $modelNames = array();
	
	// Data to be passed to the layout and view. Set by controllers using $this->setData().
	private $_viewData = array();
	
	// File system paths to the view and layout files.
	private $_viewFile = null;
	private $_layoutFile = null;
	
	// CSS and JavaScript files that will be included in the <head> tags.
	// Set using various methods. Example: $this->includeCSS().
	private $_include_css = array();
	private $_include_javascript = array();
	
	function __construct($name, $action, $arguments) {
		$this->name = ucwords(strtolower($name));
		$this->pageTitle = $this->name;
		
		$this->action = $action;
		$this->arguments = $arguments;
		
		// Load the model with the same name as the controller, unless told not to or it's the homepage controller.
		if ($this->loadControllerModel && $this->name != 'Home') {
			array_push($this->modelNames, Inflector::Singularize($this->name));
			$this->modelNames = array_keys(array_flip($this->modelNames));
		}
		
		// Load the models
		foreach ($this->modelNames as $modelName) {
			Model::Load($modelName);
		}
		
		// Include CSS and JavaScript files from the config.
		if (is_array(Config::Get('defaultcss'))) {
			$this->includeCSS(Config::Get('defaultcss'));
		}
		if (is_array(Config::Get('defaultjavascript'))) {
			$this->includeJavascript(Config::Get('defaultjavascript'));
		}
	}
	
	/**
	 * Set data that will be available to the layout and view.
	 * @param string $name
	 * @param mixed $value
	 */
	final protected function setData($name, &$value) {
		$this->_viewData[$name] =& $value;
	}
	
	final protected function clearIncludes() {
		$this->clearCSSIncludes();
		$this->clearJavascriptIncludes();
	}
	
	final protected function clearCSSIncludes() {
		$this->_include_css = array();
	}
	
	final protected function clearJavascriptIncludes() {
		$this->_include_javascript = array();
	}
	
	final protected function includeCSS($path, $rel="stylesheet") {
		if (is_array($path)) {
			foreach ($path as $p) {
				$this->_include_css[count($this->_include_css)] = array("path" => $p, "rel" => $rel);
			}
		}
		else {
			$this->_include_css[count($this->_include_css)] = array("path" => $path, "rel" => $rel);
		}
	}
	
	final protected function includeJavascript($path) {
		if (is_array($path)) {
			foreach ($path as $p) {
				$this->_include_javascript[count($this->_include_javascript)] = $p;
			}
		}
		else {
			$this->_include_javascript[count($this->_include_javascript)] = $path;
		}
	}
	
	final protected function outputIncludes() {
		$this->outputCSSIncludes();
		$this->outputJavascriptIncludes();
	}
	
	final protected function outputJavascriptIncludes() {
		foreach ($this->_include_javascript as $include) {
			echo '<script type="text/javascript" src="' . HTML::URL($include) . '"></script>' . "\n";
		}
	}
	
	final protected function outputCSSIncludes() {
		foreach ($this->_include_css as $include) {
			echo '<link href="' . HTML::URL($include['path']) . '" type="text/css" rel="' . $include['rel'] . '"/>' . "\n";
		}
	}
	
	final private function _runAction($action, $arguments = array()) {
		
		// Call the action's method in the controller.
		call_user_func_array(array($this, $action), $arguments);
		
		if ($this->view !== false) {
			// Determine the view and layout files (done after the action so it can change them).
			$this->_viewFile = VIEWS.DS. strtolower(is_string($this->view) ? $this->view : $this->name.DS.$action) .".php";
			$this->_layoutFile = LAYOUTS.DS.$this->layout.".php";
			
			// Process the view for the action.
			if (is_string($viewhtml = $this->_renderView())) {
				if (is_string($finalhtml = $this->_renderLayout($viewhtml))) {
					echo $finalhtml;
				}
				else {
					Controller::ShowError('missing_layout', array('layoutfile' => $this->_layoutFile));
				}
			}
			else {
				Controller::ShowError('missing_view', array('viewfile' => $this->_viewFile));
			}
		}
	}
	
	final private function _renderView() {
		if (file_exists($this->_viewFile)) {
			// Extract variables used by views.
			extract($this->_viewData, EXTR_SKIP);
			
			ob_start();
			include($this->_viewFile);
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
		}
		else {
			return false;
		}
	}
	
	final private function _renderLayout($viewhtml) {
		if (file_exists($this->_layoutFile)) {
			// Extract variables used by layouts.
			$pagetitle = $this->pageTitle;
			extract($this->_viewData, EXTR_SKIP);
			
			// Include the layout for processing.
			ob_start();
			include($this->_layoutFile);
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
		}
		else {
			return false;
		}
	}
	
	final function ShowPage($name) {
		// Extract variables used by views.
		extract($this->_viewData, EXTR_SKIP);
		
		if (file_exists(PAGES.DS.$name.'.php')) {
			include(PAGES.DS.$name.'.php');
		}
		else {
			trigger_error('missing page: ' . $name, E_USER_ERROR); // TODO: Use ShowError();
		}
	}
	
	final function ShowCorePage($name) {
		// Extract variables used by views.
		extract($this->_viewData, EXTR_SKIP);
		
		if (file_exists(CORE_PAGES.DS.$name.'.php')) {
			include(CORE_PAGES.DS.$name.'.php');
		}
		else {
			trigger_error('missing core page: ' . $name, E_USER_ERROR); // TODO: Use ShowError();
		}
	}
	
	static function ShowError($error, $arguments = array()) {
		extract($arguments, EXTR_SKIP);
		
		if (file_exists(ERRORS.DS.$error.".php")) {
			require(ERRORS.DS.$error.".php");
		}
		else {
			require(CORE_ERRORS.DS.$error.".php");
		}
		
		exit;
	}
	
	/**
	 * Responsible for initializing and validating the request and executing the responsible controller.
	 * @param string $dispatch The URL path which specifies the controller and action to be run, as well as any arguments.
	 */
	static function Dispatch($dispatch = null) {
		
		// Get the path from a query string variable, if it was not set.
		// One example of where this applies when the _htaccess file is used.
		if (is_null($dispatch)) {
			if (isset($_GET['dispatch'])) {
				$dispatch = $_GET['dispatch'].'';
				unset($_GET['dispatch']);
			}
			else {
				$dispatch = '';
			}
		}
			
		// Defaults
		$controller = "Home";
		$action = "index";
		$arguments = array();
		
		// Include required helpers.
		Controller::IncludeCoreHelper('inflector');
		
		// Take apart the dispatch path.
		if (!empty($dispatch)) {
			$parts = explode('/', $dispatch);
			if (count($parts) > 0 && trim($parts[count($parts) - 1]) == '') {
				array_pop($parts);
			}
			
			// Get the controller name.
			if (count($parts) > 0) {
				// Make sure the controller name has the correct case (first char upper, the rest lower)
				$controller = ucfirst(strtolower(str_replace(' ', '', array_shift($parts))));
				
				// Validate the controller name.
				if (!Controller::ValidateControllerName(strtolower($controller))) {
					echo "Bad controller name"; exit; //TODO Use Controller::ShowError
				}
			}
			
			// Get the action name
			if (count($parts) > 0) {
				// If the action is empty then allow the default to take precedence.
				if (strlen($parts[0]) == 0) {
					array_shift($parts);
				}
				
				// Validate the action name.
				elseif (!Controller::ValidateAction(strtolower(str_replace(' ', '', $parts[0])))) {
					echo "Bad action name"; exit; //TODO Use Controller::ShowError
				}
				
				else {
					$action = strtolower(str_replace(' ', '', array_shift($parts)));
				}
			}
			
			// Get arguments for the action.
			if (count($parts) > 0) {
				$arguments = $parts;
			}
		}
		
		// Clean $_GET and $_POST variables.
		Controller::CleanArray($_GET, TRUE);
		Controller::CleanArray($_POST, TRUE);
		
		// Check that action name is not a method in the Controller.
		if (array_key_exists($action, array_flip(get_class_methods('Controller')))) {
			echo "Action name is reserved by 'Controller'."; exit; //TODO Use Controller::ShowError
		}
		
		// Include the AppController if it exists, otherwise include an empty AppController.
		$appcontroller = CONTROLLERS.DS."appcontroller.php";
		if (file_exists($appcontroller)) {
			require_once($appcontroller);
			if (!class_exists("AppController")) {
				Controller::ShowError('missing_controllerclass', array('controllerclass' => "AppController", 'controllerfile' => $appcontroller));
			}
		}
		else {
			require_once(CORE_CONTROLLERS.DS."appcontroller.php");
		}
		
		// Check that action name is not a method in the AppController.
		if (array_key_exists($action, array_flip(get_class_methods('AppController')))) {
			echo "Action name is reserved by 'AppController'."; exit; //TODO Use Controller::ShowError
		}
		
		// Determine the controller's file path and class name.
		$controllerPath = CONTROLLERS.DS.strtolower($controller)."_controller.php";
		$controllerClassname = $controller."Controller";
		
		if (file_exists($controllerPath)) {
			
			// Include class file.
			include_once($controllerPath);
			
			// Make sure class exists, and then create it.
			if (class_exists($controllerClassname)) {
				$controllerObj = new $controllerClassname($controller, $action, $arguments);
				
				// Make sure controller supports the action, and then instruct it to run the action.
				if (method_exists($controllerObj, $action)) {
					if ($controllerObj->PreAction() !== FALSE) {
						$controllerObj->_runAction($action, $arguments);
						$controllerObj->PostAction();
					}
				}
				
				else {
					Controller::ShowError('missing_controlleraction', array('action' => $action, 'controllerfile' => $controllerPath));
				}
			}
			else {
				Controller::ShowError('missing_controllerclass', array('controllerclass' => $controllerClassname, 'controllerfile' => $controllerPath));
			}
		}	
		else {
			Controller::ShowError('missing_controller', array('controllerfile' => $controllerPath));
		}
	}
	
	static function ValidateControllerName($name) {
		return is_string($name) && preg_match('/^[a-z][a-z0-9_\-]*$/', $name);
	}
	
	static function ValidateAction($action) {
		return is_string($action) && preg_match('/^[a-z][a-z0-9_\-]*$/', $action);
	}
	
	/**
	 * Include a core helper by name.
	 * @param string $name
	 */
	static function IncludeCoreHelper($name) {
		switch ($name) {
			case 'inflector':
				Controller::IncludeHelper('controller', 'inflector', NULL, true, false);
				break;
			case 'io':
				Controller::IncludeHelper('controller', 'io', NULL, true, false);
				break;
			case 'nsort':
				Controller::IncludeHelper('controller', 'nsort', NULL, true, false);
				break;
			case 'image':
				Controller::IncludeHelper('controller', 'image', NULL, true, false);
				break;
			case 'download':
				Controller::IncludeHelper('controller', 'download', NULL, true, false);
				break;
			default:
				trigger_error("Core helper $name does not exist", E_USER_ERROR);
		}
	}
	
	/**
	 * Include a helper.
	 * @param string  $type The type of controller: controller, view, model
	 * @param string  $filename The name of the helper file without the extension.
	 * @param string  $class [optional] Check that a class exists after including the file.
	 * @param boolean $core [optional] Whether or not this helper is in the core dir.
	 * @param boolean $core_is_abstract [optional] Whether or not this helper can be overridden in apps.
	 */
	static function IncludeHelper($type, $filename, $class = NULL, $core = NULL, $core_is_abstract = NULL) {
		
		// If $class is a boolean, then that argument was ommitted and
		// the arguments should be treated as ($type, $filename, $core [, $core_is_abstract])
		if (is_bool($class)) {
			$core_is_abstract = $core;
			$core = $class;
			$class = NULL;
		}
		
		// Set defaults
		if (is_null($core)) $core = false;
		if (is_null($core_is_abstract)) $core_is_abstract = true;
		
		switch ($type) {
			case 'controller':
				$helperdir = CONTROLLERS.DS."helpers";
				$coredir = CORE_CONTROLLERS.DS."helpers";
				break;
			case 'view':
				$helperdir = VIEWS.DS."helpers";
				$coredir = CORE_VIEWS.DS."helpers";
				break;
			case 'model':
				$helperdir = dirname(MODELS).DS."helpers";
				$coredir = CORE_MODELS.DS."helpers";
				break;
			default:
				trigger_error("Invalid helper type '$type'", E_USER_ERROR);
		}
		
		if ($core) {
			require_once($coredir.DS.$filename. ($core_is_abstract ? "_core" : "") .".php");
		}
		
		$path = $helperdir.DS.$filename.".php";
		if (file_exists($path)) {
			require_once($path);
			if (!is_null($class) && !class_exists($class)) {
				Controller::ShowError('missing_helperclass', array('type' => $type, 'filepath' => $path, 'classname' => $class));
			}
		}
		elseif ($core) {
			require_once($coredir.DS.$filename.".php");
		}
		else {
			Controller::ShowError('missing_helper', array('type' => $type, 'filepath' => $path));
		}
	}
	
	protected function PreAction() {
		
	}
	
	protected function PostAction() {
		
	}
	
	static function CleanArray(&$arr, $stripSlashes = FALSE) {
		if (!is_array($arr)) return;
		
		foreach ($arr as $key => $value) {
			
			// Process a sub-array.
			if (is_array($value)) {
				Controller::CleanArray($arr[$key], $stripSlashes);
			}
			
			// Only clean strings, but skip binary fields (starts with 'bin_').
			elseif (strpos($key, 'bin_') !== 0 && is_string($value)) {
				// Remove any escaping that may have happened if magic_quotes_gpc is set to ON in php.ini
				if (get_magic_quotes_gpc()) {
					$arr[$key] = stripslashes($value);
				}
				
				$arr[$key] = Charset::Clean($value);
			}
		}
	}
}

?>