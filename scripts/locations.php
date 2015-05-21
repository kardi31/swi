<?php

define('APPLICATION_ENV', 'development');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Config.php';
require_once 'Zend/Config/Ini.php';
$appConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, $appConfig
);


require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Doctrine_');
$loader->pushAutoloader(array('Doctrine_Core', 'autoload'));

// importing module schema files
$front = $application->getBootstrap()->bootstrap('FrontController')->getResource('FrontController');

$moduleDirs = array();
foreach($front->getControllerDirectory() as $module => $dir) {
	$moduleDirs[$module] = $front->getModuleDirectory($module);
}



$application->getBootstrap()->bootstrap('configLoader');
//$application->getBootstrap()->bootstrap('acl'); // for modules below
$application->getBootstrap()->bootstrap('modules'); // for module autoloaders
$application->getBootstrap()->bootstrap('doctrine');


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
    }
}


$provinceTable = Doctrine_Core::getTable('Offer_Model_Doctrine_Province');
$cityTable = Doctrine_Core::getTable('Offer_Model_Doctrine_City');


exit;


$fileHandle = @fopen(dirname(__FILE__) . '/places.csv', 'r');
        
        
$results = array();
while (($data = fgetcsv($fileHandle, 1000, ";")) !== FALSE) {
    if(empty($data)) continue;

    $item['id']          = trim($data[0]);
    $item['province_id'] = trim($data[1]);
    $item['name']        = trim($data[2]);
    $results[]           = $item;
}

//try{
//foreach($results as $result) {
//    $province = $provinceTable->getRecord();
//    $province->setId($result['id']);
//    $province->setName($result['name']);
//    $province->save();
//    
//    echo $result['name'] . "\n";
//}
//
//} catch(Exception $e) {
//    echo $e->getMessage() . "\n";
//}

try{
foreach($results as $result) {
    $city = $cityTable->getRecord();
    $city->setId($result['id']);
    $city->setName($result['name']);
    $city->setProvinceId((int) $result['province_id']);
    $city->save();
    
    echo $result['name'] . "\n";
}

} catch(Exception $e) {
    echo $e->getMessage() . "\n";
}