<?php

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initPagination() {
		$options = $this->getOptions();
		if(array_key_exists('pagination', $options)) {
			if(array_key_exists('default_scrolling_style', $options['pagination'])) {
				Zend_Paginator::setDefaultScrollingStyle($options['pagination']['default_scrolling_style']);
			}
			if(array_key_exists('default_item_count_per_page', $options['pagination'])) {
				Zend_Paginator::setDefaultItemCountPerPage($options['pagination']['item_count_per_page']);
			}
			if(array_key_exists('default_page_range', $options['pagination'])) {
				Zend_Paginator::setDefaultPageRange($options['pagination']['default_page_range']);
			}
			if(array_key_exists('default_view_partial', $options['pagination'])) {
				Zend_View_Helper_PaginationControl::setDefaultViewPartial($options['pagination']['default_view_partial']);
			}
		}
	}
    
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => realpath(dirname(__FILE__)) . '/',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Admin'
                )
            )
        ));
    }

	protected function _initModuleAwarePlugin() {
		$front = $this->getApplication()->getResource('FrontController');
		$front->registerPlugin(new Admin_Plugin_ModuleAware());
	}

    protected function _initAdminNavigation() {
        $options = $this->getApplication()->getPluginResource('navigation')->getOptions();

        $acl = $this->getApplication()->bootstrap('acl')->getResource('acl');
		$translator = $this->getApplication()->bootstrap('translate')->getResource('translate');
		$view = $this->getApplication()->bootstrap('view')->getResource('view');

		Zend_Navigation_Page::setDefaultPageType('mvc');
        
        $adminContainer = new Zend_Navigation($options['pages']['admincontainer']['pages']);
        
		$view->navigation()->setAcl($acl);
        $view->navigation()->setTranslator($translator);
		$view->navigation()->setUseAcl(true);
		$view->navigation()->setUseTranslator(true); 
        
        $view->assign('admincontainer', $adminContainer);
        
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		
		return $adminContainer;
        
    }
    
	
}
