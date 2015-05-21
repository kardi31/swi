<?php

/**
 * Bootstrap
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_Bootstrap extends Zend_Application_Module_Bootstrap {
    
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/page/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/page',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Page'
                )
            )
        ));
    }
    
}

