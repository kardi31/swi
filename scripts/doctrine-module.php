<?php

define('APPLICATION_ENV', 'development');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

//require_once 'Zend/Cache.php';
//$cache = Zend_Cache::factory('Core', 'File', array('caching' => true, 'automatic_serialization' => true), array('cache_dir' => APPLICATION_PATH . '/../data/cache'));
//if(($appConfig = $cache->load('app_options_' . APPLICATION_ENV)) === false)
//{
	require_once 'Zend/Config/Ini.php';
	$appConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
//}

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

$loader = Zend_Loader_Autoloader::getInstance();
$loader->pushAutoloader(array('Doctrine_Core', 'autoload'));

$application->getBootstrap()->bootstrap('configLoader');
//$application->getBootstrap()->bootstrap('acl'); // for modules below
$application->getBootstrap()->bootstrap('modules'); // for module autoloaders
$application->getBootstrap()->bootstrap('doctrine');


//foreach($moduleDirs as $module => $dir) {
//    if(is_dir($dir . '/models/Doctrine/')) {
//        $modelDir = scandir($dir . '/models/Doctrine');
//        foreach($modelDir as $file) {
//            if(!is_dir($dir . '/models/Doctrine/' . $file)) {
//                if(preg_match('/^Base(.+)(?!Table)\.php/', $file, $match)) {
//                    require_once $dir . '/models/Doctrine/' . $file;
//                    Doctrine_Core::loadModel(ucfirst($module) . '_Model_Doctrine_Base' . $match[1], $dir . '/models/Doctrine/');
//                }
//            }
//        }
//        foreach($modelDir as $file) {
//            if(!is_dir($dir . '/models/Doctrine/' . $file)) {
//                if(preg_match('/^(?!Base)(.+)\.php/', $file, $match)) {
//                    require_once $dir . '/models/Doctrine/' . $file;
//                    Doctrine_Core::loadModel(ucfirst($module) . '_Model_Doctrine_' . $match[1], $dir . '/models/Doctrine/');
//                }
//            }
//        }
////        Doctrine_Core::loadModels($dir . '/models/Doctrine/', Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
//    }
//}
$defaultPath = APPLICATION_PATH;  
  
$args = $_SERVER["argv"];  
  
$config = $application->getBootstrap()->getPluginResource('doctrine')->getOptions();

// Usage: ./doctrine -m MODULE_NAME generate-models-yaml  
// The above usage will only generate models for the yaml defined in the module directory  
$moduleNameIndex = array_search("-m", $args);
if ($moduleNameIndex !== false && array_key_exists($args[$moduleNameIndex+1], $moduleDirs)) { //Check if the module bootstrapping exists or not...  

    $module = $args[$moduleNameIndex+1];
    $defaultPath = APPLICATION_PATH . "/modules/" . $module;  
    $moduleNamespace = ucfirst($module);
    // unset these module variables to prevent causing problems in the doctrine command line  
    array_splice($args, $moduleNameIndex, 2);  
    
    
    foreach($moduleDirs as $moduleName => $dir) {
        if(is_dir($dir . '/models/Doctrine/')) {
            $modelDir = scandir($dir . '/models/Doctrine');
            foreach($modelDir as $file) {
                if(!is_dir($dir . '/models/Doctrine/' . $file)) {
                    if(preg_match('/^Base(.+)(?!Table)\.php/', $file, $match)) {
                        require_once $dir . '/models/Doctrine/' . $file;
                        Doctrine_Core::loadModel(ucfirst($moduleName) . '_Model_Doctrine_Base' . $match[1], $dir . '/models/Doctrine/');
                    }
                }
            }
            foreach($modelDir as $file) {
                if(!is_dir($dir . '/models/Doctrine/' . $file)) {
                    if(preg_match('/^(?!Base)(.+)\.php/', $file, $match)) {
                        require_once $dir . '/models/Doctrine/' . $file;
                        Doctrine_Core::loadModel(ucfirst($moduleName) . '_Model_Doctrine_' . $match[1], $dir . '/models/Doctrine/');
                    }
                }
            }
    //        Doctrine_Core::loadModels($dir . '/models/Doctrine/', Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
        }
    }
    
    // change default value: 'Model_...'
    Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_MODEL_CLASS_PREFIX, $moduleNamespace . '_Model_Doctrine_');

    // Configure Doctrine Cli   
    $moduleConfig = array(  
        'data_fixtures_path'  =>  $defaultPath . '/data/fixtures',  
        'models_path'         =>  $defaultPath . '/models/Doctrine',
        'migrations_path'     =>  $defaultPath . '/data/migrations',  
        'sql_path'            =>  $defaultPath . '/data/sql',  
        'yaml_schema_path'    =>  $defaultPath . '/data/schema',  
        "generate_models_options" => array(  
            "pearStyle"             => false,
            "classPrefix"           => $moduleNamespace . '_Model_Doctrine_',
            "classPrefixFiles"      => false,
            "generateBaseClasses"   => true,
            "baseClassesDirectory"  => ".",
            "baseClassPrefix"       => "Base",
            //"baseClassName"       => "",  
            "generateTableClasses"  => true,
            "phpDocPackage"         => "Admi",  
            "phpDocSubpackage"      => "$moduleNamespace",  
            "phpDocName"            => "Michał Folga",  
            "phpDocEmail"           => "michalfolga@gmail.com"
            //"phpDocVersion"         => "0.1"
        ),  
    );  
    
    $config = array_merge($config, $moduleConfig);

}  

$cli = new Doctrine_Cli($config);  
$cli->run($args); 
