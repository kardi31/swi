<?php

require_once 'facebook-php-sdk/src/base_facebook.php';
require_once 'facebook-php-sdk/src/facebook.php';
        
/**
 * User_Service_Facebook
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Service_Facebook extends Facebook implements MF_Service_ServiceInterface
{
    protected $options;
    protected $serviceBroker;
    
    public static function factory(array $options = array(), $serviceBroker = null) {
        $class = get_called_class();
        $options = $serviceBroker->getOptions();
        $service = new $class($options['facebook']);
        $service->setServiceBroker($serviceBroker);
        return $service;
    }
    
    public function getOptions() {
        return $this->options;
    }

    public function setServiceBroker($serviceBroker) {
        $this->serviceBroker = $serviceBroker;
    }
    
    public function getServiceBroker() {
        return $this->serviceBroker;
    }
    
    public static function getType() {
        return get_called_class();
    }
    
    public function forceIdentity($identity) {
        Zend_Auth::getInstance()->getStorage()->write($identity);
    }
}

