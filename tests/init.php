<?php
define('SRCPATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR);
function testAutoload($class) {
	$path=SRCPATH.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
	if(file_exists($path)) {
		require_once $path;
	}
}
spl_autoload_register('testAutoload');