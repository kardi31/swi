<?php 

class User_Model_User_Memory implements User_Model_User_Interface
{
    protected $_id;
	protected $_firstName;
	protected $_lastName;
	protected $_email;
	protected $_role;
	protected $_active;
	
    public function setId($id) {
        $this->_id= $id;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setFirstName($firstName) {
	$this->_firstName = $firstName;
    }
	
    public function getFirstName() {
	return $this->_firstName;
    }
	
    public function setLastName($lastName) {
	$this->_lastName = $lastName;
    }
	
    public function getLastName() {
	return $this->_lastName;
    }
    
    public function setEmail($email) {
	$this->_email = $email;
    }
	
	public function getEmail() {
		return $this->_email;
	}
    
    public function setSalt($salt){
        $this->_set('salt', $salt);
    }
    
    public function getSalt() {
        return $this->_get('salt');
    }
    
    public function setPassword($password){
        $this->_set('password', $password);
    }
    
    public function getPassword() {
        return $this->_get('password');
    }
	
	public function setRole($role) {
		$this->_role = $role;
	}
	
	public function getRole() {
		return $this->_role;
	}
	
    public function setToken($token) {
        $this->_set('token', $token);
    }
    
    public function getToken() {
        return $this->_get('token');
    }
    
	public function setActive($active) {
		$this->_active = $active;
	}
	
	public function isActive() {
		return $this->_active;
	}
    
    public function isStatus($status) {
        $this->_set('status', $status);
	}
    
    public function getStatus() {
        return $this->_get('status');
    }
}