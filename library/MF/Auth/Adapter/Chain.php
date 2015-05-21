<?php 

/**
 * Class iterates through previously configured and added auth adapters and returns first successfully result.
 * If none of registered adapters pass, returns result with code, messages and identity of result with lowest
 * code i.e. the result that was closest to pass authentication procedure.
 * 
 * @author Michał Folga <michalfolga@gmail.com>
 *
 */
class MF_Auth_Adapter_Chain implements Zend_Auth_Adapter_Interface
{
	protected $_adapters;
	protected $_identity;
    protected $_credential;
    protected $_passwordEncoder;
	private $_code = 0;
	private $_messages = array();
	
	/**
	 * Adds adapters to stack
	 * 
	 * @param Zend_Auth_Adapter_Interface $adapter
	 */
	public function addAdapter(Zend_Auth_Adapter_Interface $adapter) {
		$this->_adapters[] = $adapter;	
	}
	
	/**
	 * Removes all adapters
	 * 
	 */
	public function cleanAdapters() {
		$this->_adapters = array();
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
    
	/**
	 * Apply authentication procedure with use of registered adapters
	 * 
	 */
	public function authenticate() {
		foreach($this->_adapters as $adapter) {
            $adapter->setIdentity($this->_identity);
            $adapter->setCredential($this->_credential);
            $adapter->setPasswordEncoder($this->_passwordEncoder);
			$result = $adapter->authenticate();
			if($result instanceof Zend_Auth_Result) {
                if($result->getCode() == Zend_Auth_Result::SUCCESS) {
                    // return seccessfully result
                    return $result;
                } else {
                    if($result->getCode() < $this->_code) {
                        // saving parameters of result with the lowest status code (the closest to pass authenticatino)
                        $this->_identity = $result->getIdentity();
                        $this->_code = $result->getCode();
                        $this->_messages = $result->getMessages();
                    }
                }
            }
		}	
		
		// returns unsuccessfull result
		return new Zend_Auth_Result($this->_code, $this->_identity, $this->_messages);
	}
}