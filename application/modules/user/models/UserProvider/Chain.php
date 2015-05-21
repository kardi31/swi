<?php

/**
 * Chain
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Model_UserProvider_Chain implements User_Model_UserProvider_Interface {
    
    protected $userProviders = array();
    
    public function addUserProvider($name, User_Model_UserProvider_Interface $userProvider) {
        $this->userProviders[$name] = $userProvider;
        return $this;
    }
    
    public function removeUserProvider($name) {
        if(array_key_exists($name, $this->_userProviders)) {
            unset($this->userProviders[$name]);
        }
        return $this;
    }
    
    public function getUserProviders() {
        return $this->userProviders;
    }
    
    public function findUserByIdentity($identity) {
        foreach($this->getUserProviders() as $name => $provider) {
            $user = $provider->findUserByIdentity($identity);
            if($user instanceof User_Model_User_Interface) {
                return $user;
            }
        }
		return null; 
    }
}

