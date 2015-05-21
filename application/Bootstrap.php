<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRouterTranslator() {
        $translator = new Zend_Translate(array('adapter' => 'tmx', 'content' => APPLICATION_PATH . '/configs/translations/router.tmx', 'locale' => 'en'));
        Zend_Controller_Router_Route::setDefaultTranslator($translator);
    }
    
    protected function _initFormTranslator() {
        $translator = $this->bootstrap('translate')->getResource('translate');
        Zend_Form::setDefaultTranslator($translator);
    }
    
    protected function _initViewVars() {
//        $view = $this->bootstrap('view')->getResource('view');
//        $view->assign('mediaServer', $this->getOption('mediaServer'));
    }
    
    protected function _initLaggerLog() {
//        echo "d";exit;
//        if(APPLICATION_ENV == 'development') {
//            $writer = new MF_Log_Writer_Lagger();
//            $filter = new Zend_Log_Filter_Priority(7);
//            $writer->addFilter($filter);
//            $log = $this->bootstrap('log')->getResource('log');
//            $log->addWriter($writer);
//        }
    }
	
}

