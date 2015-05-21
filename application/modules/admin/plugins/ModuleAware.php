<?php

/*
 * If requested module is admin, plugin checks that module which names is requested controller name exists. If so,
 * saves controller's names so it can be used later to redirect from admin index controller.
 */
class Admin_Plugin_ModuleAware extends Zend_Controller_Plugin_Abstract
{
	/**
	 * Originally called request
	 * @type null|Zend_Controller_Request_Abstract
	 */
	protected $_calledRequest;
	
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		if($request->getModuleName() !== 'admin') {
			return;
		}
		
		$front = Zend_Controller_Front::getInstance();
		$controller = $request->getControllerName();
		
		if($front->getDispatcher()->isValidModule($controller)) {
			
			$this->_calledRequest = clone $request;
			
			$request->setModuleName('admin');
			$request->setControllerName('index');
			$request->setActionName('aware');

		}
	}
	
	/**
	 * Returns originally called request
	 * @return null|Zend_Controller_Request_Abstract
	 */
	public function getCalledRequest() {
		return $this->_calledRequest;
	}
}