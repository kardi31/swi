<?php

class MF_Auth_Adapter_Doctrine2 implements Zend_Auth_Adapter_Interface
{
    protected $_identity;
    protected $_credential;

    protected $_user = null;
    
    public function __construct($identity, $credential)
    {
    	$this->_em = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('doctrine2');

        $this->_identity = $identity;
        $this->_credential = $credential;
    }

    public function authenticate()
    {
    	try
        {
            $this->_user = $this->_em->getRepository('Entities\User')->authenticate($this->_identity, $this->_credential);
            return $this->_createResult(Zend_Auth_Result::SUCCESS);
        }
        catch(Exception $e)
        {
            if($e->getCode() == \Repositories\UserRepository::WRONG_PASSWORD_CODE)
            {
                return $this->_createResult(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                                            array($e->getMessage()));
            }
            if($e->getCode() == \Repositories\UserRepository::NOT_FOUND_CODE)
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