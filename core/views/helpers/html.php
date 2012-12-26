<?php
/*
 * Copyright (c) 2011 André Mekkawi <simplemvc@andremekkawi.com>
 *
 * LICENSE
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at https://raw.github.com/amekkawi/simplemvc/master/LICENSE.txt
 */

class HTML
{
	static function Encode($string) {
		return htmlspecialchars($string, ENT_COMPAT, Config::Get('charset'));
	}
	
	static function URL($controller = null, $action = null, $arguments = null, $querystring = null) {
		
		// Allow for the querystring to be overridden when creating a URL to the current page.
		if (is_array($controller)) {
			$querystring = $controller;
			$controller = null;
		}
		
		// Create a URL to the current page.
		if (is_null($controller)) {
			// Default to $_GET if the querystring was not specified.
			$queryString = http_build_query(is_null($querystring) ? $_GET : $querystring);
			$uriParts = explode('?', $_SERVER['REQUEST_URI']);
			
			return $uriParts[0] . ($queryString == "" ? "" : '?' . $queryString);
		}
		
		// Keep as-is if pointing to a path relative to the root, or a full URL.
		if (substr($controller, 0, 1) == "/" || preg_match("/^[A-Za-z0-9]+:\/\//", $controller)) {
			return $controller;
		}
		
		// Create a URL that simply has the base URL prepended.
		if (is_null($action)) {
			return Config::Get('baseurl').$controller;
		}
		
		// The action is optional.
		if (is_array($action)) {
			$arguments = $action;
			$action = "index";
		}
		
		// Create the list of arguments for the URL.
		$argString = "";
		if (is_array($arguments)) {
			foreach ($arguments as $argvalue) {
				$argString .= "/" . urlencode($argvalue);
			}
		}
		
		// Create the query string for the URL.
		$queryString = is_array($querystring) ? http_build_query($querystring) : "";
		
		return Config::Get('baseurl').$controller."/".$action.$argString . ($queryString != "" ? "?" . $queryString : "");
	}
	
	static function Redirect($controller, $action = null, $arguments = array(), $querystring = array()) {
		$url = HTML::URL($controller, $action, $arguments, $querystring);
		
		?>
		<html>
		<head>
		
		<title>This page has moved!</title>
		
		<meta http-equiv="refresh" content="3;url=<?php echo HTML::Encode($url); ?>">
		<meta name="robots" content="noindex,follow">
		<script type="text/javascript"><!-- 
		location.replace('<?php echo HTML::JavascriptStringEncode($url); ?>');
		// --></script>
		
		</head>
		<body>
		
		<p><b>This page has moved to <a href="<?php echo HTML::Encode($url); ?>"><?php echo HTML::Encode($url); ?></a>.</b><br>
		You will be redirected in 3 seconds.</p>
		
		</body>
		</html>
		<?php
		
		exit;
	}
	
	static function JavascriptStringEncode($val, $quote = "'") {
	    switch ( $quote ) {
			case "double":
			case '"':
				$searches = array( '"', "\n" );
				$replacements = array( '\\"', "\\n\"\n\t+\"" );
				break;
			case "single":
			case "'":
				$searches = array( "'", "\n" );
				$replacements = array( "\\'", "\\n'\n\t+'" );
				break;
		}
		return str_replace( $searches, $replacements, $val );
	}
	
	static function ToJSON(&$mixed) {
		return json_encode($mixed);
	}
	
	static function FromJSON($json) {
		return json_decode($json);
	}
	
	static function CleanPath($value) {
		return (strpos($value, ROOT) == 0) ? ".".substr($value, strlen(ROOT)) :  $value;
	}
	
	static function Pre($text) {
		return str_replace("  ", "&nbsp; ", str_replace("  ", "&nbsp; ", str_replace("\n","<br/>", HTML::Encode($text))));
	}
}

?>