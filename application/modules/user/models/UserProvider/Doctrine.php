<?php

/**
 * Doctrine
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Model_UserProvider_Doctrine implements User_Model_UserProvider_Interface {
    
    protected $model;
    protected $identityName;
    
    public function __construct($model, $identityName) {
        $this->model = $model;
        $this->identityName = $identityName;
    }
    
    public function findUserByIdentity($identity) {
        return Doctrine_Core::getTable($this->model)->findOneBy($this->identityName, $identity);
    }
}

