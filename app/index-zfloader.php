<?php
set_include_path(
	realpath(dirname(__FILE__).'/../src').PATH_SEPARATOR.
	'D:/dev'.PATH_SEPARATOR.
	get_include_path()
);
require_once 'Zend/Loader.php';
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()
	->registerNamespace('CI_');

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