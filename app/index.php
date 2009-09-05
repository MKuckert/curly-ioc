<?php
set_include_path(
	realpath(dirname(__FILE__).'/../src').PATH_SEPARATOR.
	'D:/dev'.PATH_SEPARATOR.
	get_include_path()
);
function ci_autoload($class) {
	foreach(explode(PATH_SEPARATOR, get_include_path()) as $ipath) {
		$path=$ipath.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
		if(file_exists($path)) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register('ci_autoload');

// Load context
$container=new CI_Container();
$ctx=$container
	->parseFile('config.xml')
	->getContext('app');

// Load model
$news=$ctx->getObject('db')
		->select()
		->from('news')
		->order('id')
		->query()
		->fetchAll();

// View
echo $ctx->getObject('view')
	->assign('news', $news)
	->render('index.tpl');