<?php

class MF_Service_ServiceBroker implements MF_Service_ServiceBrokerInterface
{
    private static $_instance;

    protected $_options = array();
    protected $_services = array();

    public static function getInstance()
    {
        if (!(self::$_instance instanceof MF_Service_ServiceBroker)) {
            self::$_instance = new MF_Service_ServiceBroker();
        }
        return self::$_instance;
    }

    /**
     * Fetches a service from internal storage. Creates the service with $options if it does not exist yet
     *
     * The options parameter is ignored if a service that is allready known
     * is fetched.
     *
     * @return MF_Service_Service
     */
    public function getService($type, array $options = array())
    {
        //check type parameter
        if (!is_string($type) || !in_array('MF_Service_ServiceInterface', class_implements($type))) {
            throw new MF_Service_InvalidArgumentException('$type must be a classname of a class inheriting from MF_Service_ServiceInterface ("' . $type . '" given)');
        }

        //check if we know an instance, create one if not
        if (!array_key_exists(strtolower($type), self::getInstance()->_services)) {
            $this->_services[strtolower($type)] = $type::factory($options, self::getInstance());
        }
        //return known instance
        return $this->_services[strtolower($type)];
    }

    /**
     * Creates a local instance of a service, always using the options passed
     * The created service is not stored withing the broker
     *
     * @return MF_Service_Service
     */
    public function getLocalService($type, array $options = array())
    {
        //check type parameter
        if (!is_string($type) || !in_array('MF_Service_ServiceInterface', class_implements($type))) {
            throw new MF_Service_InvalidArgumentException('$type must be a classname of a class inheriting from MF_Service_ServiceInterface ("' . $type . '" given)');
        }

        return $type::factory($options, self::getInstance());
    }

    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    public function getOptions()
    {
        return $this->_options;
    }
    
    public function __set($index, $newval) {
        $instance = self::getInstance();
        $instance->_services[strtolower($index)] = $newval;
    }
    
    public function __get($index) {
        $instance = self::getInstance();
        return $instance->_services[strtolower($index)];
    }
    
    public function __isset($index) {
        $instance = self::getInstance();
        return array_key_exists(strtolower($index), $instance->_services);
    }
    
    public function set($index, $newval) {
        self::getInstance()->_services[strtolower($index)] = $newval;
    }
    
    public function get($index) {
        return self::getInstance()->_services[strtolower($index)];
    }
    
}