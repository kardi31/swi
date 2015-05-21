<?php

class MF_Auth_Adapter_Db implements Zend_Auth_Adapter_Interface
{
    protected $_identity;
    protected $_credential;
    
    protected $_userTable;
    protected $_user = null;
    
    public function __construct($identity, $credential)
    {
        $this->_identity = $identity;
        $this->_credential = $credential;
        $this->_userTable = new Admin_Model_Table_User();
    }

    public function authenticate()
    {
        try
        {
            $this->_user = $this->_userTable->authenticate($this->_identity, $this->_credential);
            return $this->_createResult(Zend_Auth_Result::SUCCESS);
        }
        catch(Exception $e)
        {
            if($e->getCode() == Admin_Model_Table_User::WRONG_PASSWORD_CODE)
            {
                return $this->_createResult(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                                            array($e->getMessage()));
            }
            if($e->getCode() == Admin_Model_Table_User::NOT_FOUND_CODE)
            {
                return $this->_createResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                                            array($e->getMessage()));
            }
        }
        
    }

    protected function _createResult($code = 0, $messages = array())
    {
        return new Zend_Auth_Result($code, $this->_user, $messages);
    }
}