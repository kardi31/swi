<?php

class Invoice_Bootstrap extends Zend_Application_Module_Bootstrap
{	
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/invoice/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/invoice',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Invoice'
                )
            )
        ));
    }
    
}

