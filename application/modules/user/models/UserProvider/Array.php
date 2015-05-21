<?php 

class User_Model_UserProvider_Array implements User_Model_UserProvider_Interface
{
	protected $_users;
	
	public function __construct($params) {
		$this->_users = $params['users'];	
	}
	
	public function findUserByIdentity($identity) {
		foreach($this->_users as $data) {
			if(array_key_exists('identity', $data) && $data['identity'] === $identity) {
				
				$user = new User_Model_User_Memory();
				$user->setFirstName($data['first_name']);
				$user->setLastName($data['last_name']);
				$user->setEmail($data['identity']);
				$user->setActive($data['active']);
				$user->setRole($data['role']);
				
				return $user;
			}
		}
	}
    
    public function findAllUsers() {
        foreach($this->_users as $data) {
            $user = new User_Model_User_Memory();
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $user->setEmail($data['email']);
            $user->setActive($data['active']);
            $user->setRole($data['role']);

            return $user;
		}
    }
}