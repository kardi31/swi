<?php

/**
 * Lagger
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Log_Writer_Lagger extends Zend_Log_Writer_Abstract {
    
    protected $debug;
    
    static public function factory($config) {
        return new self();
    }
    
    public function __construct() {
        $laggerES = new Lagger_Eventspace();
        $debug = new Lagger_Handler_Debug($laggerES);
//        $errors = new Lagger_Handler_Errors($laggerES);
//        $exceptions = new Lagger_Handler_Exceptions($laggerES);

        $chromeConsole = new Lagger_Action_ChromeConsole();
        $debug->addAction($chromeConsole);
//        $errors->addAction($chromeConsole);
//        $exceptions->addAction($chromeConsole);
        
        $this->debug = $debug;
    }
    
    public function write($event) {
        $this->_write($event['message']);
    }
    
    protected function _write($event) {
        $this->debug->handle($event);
    }
}

