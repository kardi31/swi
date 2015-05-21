<?php 

class MF_Auth_Adapter_Array implements Zend_Auth_Adapter_Interface
{
	const IDENTITY_NOT_FOUND = 'Identity not found';
	const CREDENTIAL_INVALID = 'Credential invalid';
	
	protected $_users;
	protected $_identity;
	protected $_credential;
    protected $_passwordEncoder;
	
	public function __construct($users) {
		$this->_users = $users;
	}
	
	public function setIdentity($identity) {
		$this->_identity = $identity;
	}
	
	public function setCredential($credential) {
		$this->_credential = $credential;
	}
	
    public function setPasswordEncoder($passwordEncoder) {
        $this->_passwordEncoder = $passwordEncoder;
    }
    
	public function authenticate() {
		foreach($this->_users as $user) {
			if(array_key_exists('identity', $user) && $this->_identity === $user['identity']) {
                $credential = (null != $this->_passwordEncoder) ? $this->_passwordEncoder->encode($this->_credential) : $this->_credential;
                if(array_key_exists('credential', $user) && $credential === $user['credential']) {
					return $this->_createResult(Zend_Auth_Result::SUCCESS);
                    break;
				} else {
					return $this->_createResult(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, array(self::CREDENTIAL_INVALID));
                    break;
				}
			}
		}		
        return $this->_createResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, array(self::IDENTITY_NOT_FOUND));
	}

	protected function _createResult($code = 0, $messages = array()) {
        return new Zend_Auth_Result($code, $this->_identity, $messages);
    }
}