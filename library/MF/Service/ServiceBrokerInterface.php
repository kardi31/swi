<?php

interface MF_Service_ServiceBrokerInterface
{
    public static function getInstance();

    public function getService($type, array $options = array());

    public function setOptions(array $options);

    public function getOptions();
}