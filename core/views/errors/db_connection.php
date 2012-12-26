<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::Get('charset'); ?>" />
<title><?php echo HTML::Encode(Config::GET('appname')); ?>: Database Connection Error</title>
<link href="<?php echo HTML::URL('core/css/error.css'); ?>" type="text/css" rel="stylesheet"/>
</head>

<body>
	<div id="Container">
		<h1>Error: Database Connection</h1>
		<p>An error was encountered while attempting to connect to the database:</p>
		<p><?php echo HTML::Encode($errormessage); ?><br/>&nbsp;</p>
		<pre><?php if (isset($debuginfo)) echo HTML::Encode($debuginfo); ?></pre>
	</div>
</body>
</html>