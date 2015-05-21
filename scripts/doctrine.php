<?php

define('APPLICATION_ENV', 'development');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Cache.php';
$cache = Zend_Cache::factory('Core', 'File', array('caching' => true, 'automatic_serialization' => true), array('cache_dir' => APPLICATION_PATH . '/../data/cache'));
if(($appConfig = $cache->load('app_options_' . APPLICATION_ENV)) === false)
{
	require_once 'Zend/Config/Ini.php';
	$appConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
}

require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, $appConfig
);



// importing module schema files
$front = $application->getBootstrap()->bootstrap('FrontController')->getResource('FrontController');

$moduleDirs = array();
foreach($front->getControllerDirectory() as $module => $dir) {
	$moduleDirs[$module] = $front->getModuleDirectory($module);
}
foreach($moduleDirs as $module => $dir) {
	$schemaFile = $dir . '/data/schema/schema.yml';
	if(file_exists($schemaFile)) {
		copy($schemaFile, APPLICATION_PATH . '/doctrine/schema/' . $module . '.yml');
	}
}
/*
 * Migracje:
 * - zmiana w schema
 * - generate-migrations-diff (porównanie schema i modelu)
 * - migrate (zmiana w bazie)
 * - generate-models-yaml (dodanie zmian ze schemy do modelu)
 * - ...
 */


$loader = Zend_Loader_Autoloader::getInstance();
$loader->pushAutoloader(array('Doctrine_Core', 'autoload'));


$application->getBootstrap()->bootstrap('configLoader');
//$application->getBootstrap()->bootstrap('resourceAutoloader');
$application->getBootstrap()->bootstrap('doctrine');



$config = $application->getBootstrap()->getPluginResource('doctrine')->getOptions();

$cli = new Doctrine_Cli($config);
$cli->run($_SERVER['argv']);
