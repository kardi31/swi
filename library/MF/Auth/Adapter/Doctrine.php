<?php

class MF_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
	const IDENTITY_NOT_FOUND = 'User not found';
	const CREDENTIAL_INVALID = 'Credential invalid';
	
	protected $_model;
    protected $_identity;
    protected $_identityField;
    protected $_credential;
    protected $_passwordEncoder;

    protected $_user = null;
    
    public function __construct($model) {
        $this->_model = $model;
    }

    public function setIdentityField($field) {
        $this->_identityField = $field;
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

    	// fetching user entry from database
        $q = Doctrine_Query::create()
                ->select('u.*')
                ->from($this->_model . ' ' . 'u')
                ->where('u.' . $this->_identityField . ' = ?', $this->_identity)
                ->andWhere('u.deleted_at IS NULL')
                ;
        
		$user = $q->fetchOne();

        // checking user compliance
		if(!$user instanceof $this->_model) {
			return $this->_createResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, array(self::IDENTITY_NOT_FOUND));
		} 
        
        $credential = (null != $this->_passwordEncoder) ? $this->_passwordEncoder->encode($this->_credential, $user->get('salt')) : $this->_credential;

        if($user->getPassword() !== $credential) {
            return $this->_createResult(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, array(self::CREDENTIAL_INVALID));
		} else {
			return $this->_createResult(Zend_Auth_Result::SUCCESS);
		}
		
    }

    protected function _createResult($code = 0, $messages = array()) {
        return new Zend_Auth_Result($code, $this->_identity, $messages);
    }
}