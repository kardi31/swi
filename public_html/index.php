<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
if(strstr($_SERVER['HTTP_HOST'],'localhost')){
    if(strstr($_SERVER['HTTP_HOST'],'juniorzystarsi')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'juniorzystarsidevelopment');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'juniorzystarsi');

        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 2);

    }
    elseif(strstr($_SERVER['HTTP_HOST'],'juniorzymlodsi')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'juniorzymlodsidevelopment');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'juniorzymlodsi');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 3);

    }
    elseif(strstr($_SERVER['HTTP_HOST'],'trampkarze')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'trampkarzedevelopment');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'trampkarze');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 4);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'mlodzicy')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'mlodzicydevelopment');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'mlodzicy');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 5);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'orliki')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'orlikidevelopment');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'orliki');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 6);
    }
    else{
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'development');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'seniorzy');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 1);
    }
}
else{
    if(strstr($_SERVER['HTTP_HOST'],'juniorzystarsi')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'juniorzystarsiproduction');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'juniorzystarsi');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 2);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'juniorzymlodsi')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'juniorzymlodsiproduction');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'juniorzymlodsi');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 3);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'trampkarze')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'trampkarzeproduction');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'trampkarze');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 4);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'mlodzicy')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'mlodzicyproduction');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'mlodzicy');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 5);
    }
    elseif(strstr($_SERVER['HTTP_HOST'],'orliki')){
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'orlikiproduction');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'orliki');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 6);
    }
    else{
        defined('APPLICATION_ENV')|| define('APPLICATION_ENV', 'production');
        defined('APPLICATION_TYPE')|| define('APPLICATION_TYPE', 'seniorzy');
        defined('APPLICATION_GROUP')|| define('APPLICATION_GROUP', 1);
    }

}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */

require_once 'Zend/Application.php';

require_once 'Zend/Cache.php';
$cache = Zend_Cache::factory('Core', 'File',
    array('caching' => true, 'automatic_serialization' => true),
    array('cache_dir' => APPLICATION_PATH . '/../data/cache'));
require_once 'Zend/Config.php';
require_once 'Zend/Config/Ini.php';
$options = $cache->load('app_options_' . APPLICATION_ENV);
if(is_array($options) && count($options)) {
    $config = new Zend_Config($options, APPLICATION_ENV);
} else {
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
}


$application = new Zend_Application(
    APPLICATION_ENV, $config
);
require_once 'MF/Service/ServiceBroker.php';
$application->getBootstrap()->setContainer(MF_Service_ServiceBroker::getInstance());
$application->bootstrap()->run();
