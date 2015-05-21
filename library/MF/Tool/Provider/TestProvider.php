<?php

require_once 'Zend/Tool/Project/Provider/Abstract.php';
require_once 'Zend/Tool/Project/Provider/Exception.php';

class MF_Tool_Provider_TestProvider extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{

    public function hello($name = 'World')
    {
        $this->_registry->getResponse()->appendContent('hello ' . $name);
    }


}

