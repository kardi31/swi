<?php

class Media_Bootstrap extends Zend_Application_Module_Bootstrap
{	
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/media/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/media',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Media'
                )
            )
        ));
    }
	
    protected function _initResourcePaths() {       
//        $this->getPluginLoader()->addPrefixPath('Media_Resource', APPLICATION_PATH . '/modules/media/library/Resource');
//        Zend_Loader_Autoloader::autoload('Media_Resource_Elfinder');
//        $this->getPluginLoader()->load('elfinder');
    }
    
    protected function _initCropPhotoHelper() {
        $cropPhotoHelper = new Media_View_Helper_CropPhoto();
        $view = $this->getApplication()->bootstrap('view')->getResource('view');
        $view->registerHelper($cropPhotoHelper, 'cropPhoto');
    }

}

