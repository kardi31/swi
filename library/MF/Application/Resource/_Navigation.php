<?php

class MF_Application_Resource_Navigation extends Zend_Application_Resource_ResourceAbstract
{
	protected $_options = array();
	
	public function init() {
		$this->_options = array_merge($this->_options, $this->getOptions());

		Zend_Navigation_Page::setDefaultPageType('mvc');
        
		$navigation = new Zend_Navigation($this->_options['pages']);
		$acl = $this->getBootstrap()->bootstrap('acl')->getResource('acl');
		$translator = $this->getBootstrap()->bootstrap('translate')->getResource('translate');
		$view = $this->getBootstrap()->bootstrap('view')->getResource('view');

        $sitenavContainer = new Zend_Navigation($this->_options['pages']['sitecontainer']['pages']);
        $view->sitenavContainer = $sitenavContainer;
        
		$breadcrumbsContainer = new Zend_Navigation($this->_options['pages']);
		$view->breadcrumbsContainer = $breadcrumbsContainer;

        $view->navigation($navigation);
		$view->navigation()->setAcl($acl);
		$view->navigation()->setUseAcl();
		$view->navigation()->setTranslator($translator);
		$view->navigation()->setUseTranslator();
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		
		return $navigation;
	}
}