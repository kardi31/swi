<?php

interface MF_Service_ServiceInterface
{

    public static function getType();

    public static function factory(array $options = array());

    public function getOptions();
    
}