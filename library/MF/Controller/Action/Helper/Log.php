<?php 
class MF_Controller_Action_Helper_Log extends Zend_Controller_Action_Helper_Abstract
{
	protected $_logger;
	
	public function init() {
		$front = Zend_Controller_Front::getInstance();
		$bootstrap = $front->getParam('bootstrap');
		if(!$bootstrap->hasResource('log')) {
			throw new Exception('Resource log not found');
			return;
		}
		
		$this->_logger = $bootstrap->getResource('log');
	}
	
	public function direct($message, $priority = Zend_Log::INFO) {
		$this->_logger->log($message, $priority);
	}
}