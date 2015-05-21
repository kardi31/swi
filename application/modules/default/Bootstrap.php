<?php

class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{	
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/default/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/default',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Default'
                )
            )
        ));
    }
    
    protected function _initCmsHelpers() {
        $cmsHelper = new Default_View_Helper_Cms();
        $menuHelper = new Default_View_Helper_CmsMenu();
//        $breadcrumbsHelper = new Default_View_Helper_Breadcrumbs();
        $headTitleHelper = new Default_View_Helper_HeadTitle();

        $view = $this->getApplication()->bootstrap('view')->getResource('view');
        $view->registerHelper($cmsHelper, 'cms');
//        $view->registerHelper($menuHelper, 'cms_menu');
//        $view->registerHelper($breadcrumbsHelper, 'cms_breadcrumbs');
        $view->registerHelper($headTitleHelper, 'headTitle');
    }
    
    protected function _initLayoutLocations() {
        $view = $this->getApplication()->bootstrap('view')->getResource('view');
        $view->assign('layout_locations', array(
            'top_menu' => 'Top Menu',
            'header_menu' => 'Header Menu'
        ));
    }
	
    protected function _initMainNavigation() {
        $options = $this->getApplication()->getPluginResource('navigation')->getOptions();
        
        $acl = $this->getApplication()->bootstrap('acl')->getResource('acl');
		$translator = $this->getApplication()->bootstrap('translate')->getResource('translate');
		$view = $this->getApplication()->bootstrap('view')->getResource('view');

		Zend_Navigation_Page::setDefaultPageType('mvc');
        
		$container = new Zend_Navigation($options['pages']);
        $mainContainer = new Zend_Navigation($options['pages']['maincontainer']['pages']);
        $breadcrumbsContainer = new Zend_Navigation($options['pages']);
        
        $view->navigation($container);
		$view->navigation()->setAcl($acl);
        $view->navigation()->setTranslator($translator);
		$view->navigation()->setUseAcl(true);
        $view->navigation()->setDefaultRole('guest');
		$view->navigation()->setUseTranslator(true);
        
        $view->assign('maincontainer', $mainContainer);
        $view->assign('options', $options);
        $view->assign('breadcrumbscontainer', $breadcrumbsContainer);
        
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		
		return $container;
    }
    
    protected function _initFallbackLanguage() {
        $this->getApplication()->bootstrap('doctrine');
        
        $serviceBroker = MF_Service_ServiceBroker::getInstance();
        $i18nService = $serviceBroker->getService('Default_Service_I18n');

        $fallbackLanguage = new Default_Model_Language();
        $fallbackLanguage->setId('en');
        $fallbackLanguage->setName('English');

        $i18nService->setFallbackLanguage($fallbackLanguage);
        
        return $fallbackLanguage;
    }
}

