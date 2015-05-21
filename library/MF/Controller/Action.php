<?php

abstract class MF_Controller_Action extends Zend_Controller_Action
{
	protected $_errorHandler;
    protected $_service;
	
    public function init() {
        parent::init();
        $this->_service = MF_Service_ServiceBroker::getInstance();
        $this->language = $this->_service->get('Zend_Locale')->getLanguage();
    }
    
	protected function _forward404Unless($assertion, $message = '') {
		if(!$assertion) {
			$error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
			$error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION;
			$error->exception = new Exception($message);
			$error->request = clone $this->getRequest();
			
			$this->_errorHandler = Zend_Controller_Front::getInstance()->getPlugin('Zend_Controller_Plugin_ErrorHandler');
			
			$this->_forward(
                $this->_errorHandler->getErrorHandlerAction(),
                $this->_errorHandler->getErrorHandlerController(),
                $this->_errorHandler->getErrorHandlerModule(),
                array('error_handler' => $error)
            );
		}
	}

	protected function _forward500Unless($assertion, $message = '') {
		if(!$assertion) {
			$error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
			$error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
			$error->exception = new Exception($message);
			$error->request = clone $this->getRequest();
			
			$this->_errorHandler = Zend_Controller_Front::getInstance()->getPlugin('Zend_Controller_Plugin_ErrorHandler');
			
			$this->_forward(
                $this->_errorHandler->getErrorHandlerAction(),
                $this->_errorHandler->getErrorHandlerController(),
                $this->_errorHandler->getErrorHandlerModule(),
                array('error_handler' => $error)
            );
		}
	}
	
}