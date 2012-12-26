<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::Get('charset'); ?>" />
<title><?php echo HTML::Encode(Config::GET('appname')); ?>: Missing Arguments</title>
<link href="<?php echo HTML::URL('core/css/error.css'); ?>" type="text/css" rel="stylesheet"/>
</head>

<body>
	<div id="Container">
		<h1>Error: Missing Arguments</h1>
		<p>One or more arguments are missing<?php if (isset($argumentNames)) echo ': '. implode(', ', $argumentNames); ?>.</p>
	</div>
</body>
</html>