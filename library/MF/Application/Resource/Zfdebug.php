<?php

class MF_Application_Resource_Zfdebug extends Zend_Application_Resource_ResourceAbstract
{
	protected $_options = array();
	
	public function init()
	{
		$this->_options = $this->getOptions();

		// Setup autoloader with namespace
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $bootstrap = $this->getBootstrap();
        
        // Ensure the front controller is initialized
        $bootstrap->bootstrap('frontController');

        // Retrieve the front controller from the bootstrap registry
        $front = $bootstrap->getResource('frontController');

        // Create ZFDebug instance
        $zfdebug = new ZFDebug_Controller_Plugin_Debug($this->_options);

        // Alternative configuration without application.ini
		if($bootstrap->hasResource('db')) {
			$bootstrap->bootstrap('db');
	        $db = $bootstrap->getResource('db');
	        $zfdebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Database(array('adapter' => $db)));	
		}
        
		if($bootstrap->hasResource('cachemanager')) {
			$bootstrap->bootstrap('cachemanager');
	        $cacheResource = $bootstrap->getResource('cachemanager');
            foreach($cacheResource->getCaches() as $cache) {
                $zfdebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Cache(array('backend' => $cache->getBackend())));
            }
		}
        
        $zfdebug->registerPlugin(new Danceric_Controller_Plugin_Debug_Plugin_Doctrine());
                  
        // Register ZFDebug with the front controller
        $front->registerPlugin($zfdebug);
        
        return $zfdebug;

	}
}