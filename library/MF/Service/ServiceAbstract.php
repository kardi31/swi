<?php

abstract class MF_Service_ServiceAbstract implements MF_Service_ServiceInterface
{
    protected $options = array();
    protected $serviceBroker;

    public function __construct(array $options = array(), $serviceBroker = null) {
        $this->serviceBroker = $serviceBroker;
        $this->setOptions($options);
        $this->init();
    }
    
    public function init() { }
    
    /**
     * Constructs a Service instance
     *
     * @param array $options
     * @return Ctrl\Service\Service
     */
    public static function factory(array $options = array(), $serviceBroker = null) {
        $class = get_called_class();
        return new $class($options, $serviceBroker);
    }
    
    protected function setOptions(array $options) {
        $this->options = $options;
    }

    public function getOptions() {
        return $this->options;
    }

    public function getServiceBroker() {
        return $this->serviceBroker;
    }
    
    public static function getType() {
        return get_called_class();
    }
}