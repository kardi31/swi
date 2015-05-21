<?php

class MF_Controller_Action_Helper_User extends Zend_Controller_Action_Helper_Abstract
{
    public static $namespace = 'USER';
    
	protected $authService;
	
	public function init() {
		$this->authService = MF_Service_ServiceBroker::getInstance()->getService('User_Service_Auth');
        if(!$this->authService instanceof MF_Service_ServiceInterface) {
            throw new Exception('Auth service not known');
        }
	}
	
	public function direct($property = null) {
		if(null === $property) {
			return $this->authService->getAuthenticatedUser();	
		}

		if(method_exists($this->authService->getAuthenticatedUser(), 'get' . $this->_normalizeMethodName($property))) {
			return call_user_func(array($this->authService->getAuthenticatedUser(), 'get' . $this->_normalizeMethodName($property)));
		} 
	}
	
	public function toArray()
	{
		if(empty($this->_identity_vars))
		{
	    	$class = new ReflectionClass(get_class(Zend_Auth::getInstance()->getIdentity()));
	    	$result = array();
	    	foreach($class->getMethods() as $method)
	    	{
	    		if(preg_match('/^get(.+)$/', $method->name, $matches))
	    		{
	    			$result[$this->_denormalizeMethodName($matches[1])] = call_user_func(array($this->_identity, $matches[0]));
	    		}
	    	}
	    	$this->_identity_vars = $result;
		}
    	
    	return $this->_identity_vars;
	}
	
	public function isLoggedIn()
	{
		return Zend_Auth::getInstance()->hasIdentity();
	}
	
	public function render($partial, $module = null)
	{
		$data = array();
		if(null !== $this->_identity)
		{
			$data = $this->toArray();
		}
		$data['user'] = $this;
		return $this->getActionController()->view->partial($partial, $module, $data);
	}
	
	public function set($key, $value, $namespace = null, $expiration = 1800, $hops = null) {
        $namespace = null == $namespace ? self::$namespace : $namespace;
        $n = new Zend_Session_Namespace($namespace);	
		
		$n->setExpirationSeconds($expiration, $key);
		
		if(is_integer($hops))
			$n->setExpirationHops($hops, $key);
		
		$n->$key = $value;
	}
	
	public function get($key, $namespace = null) {
        $namespace = null == $namespace ? self::$namespace : $namespace;
        $n = new Zend_Session_Namespace($namespace);	
		
		return $n->$key;
	}
    
    public function cookie($name, $value = null, $time = 86400, $domain = '/', $secure = false, $httpOnly = false) {
        if(null === $value) {
            return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
        } else {
            setCookie($name, $value, $time, $domain, $secure, $httpOnly);
            return true;
        }
    }
    
	public function delete($key, $namespace = null)
	{
		if(null !== $namespace)
		{
			$n = new Zend_Session_Namespace();	
		}
		else 
		{
			$n = new Zend_Session_Namespace($namespace);	
		}
		
		unset($n->$key);
	}
	
	protected function _normalizeMethodName($method) {
		if(is_string($method)) {
			$pieces = explode('_', $method);
			$result = '';
			foreach($pieces as $piece) {
				$result .= ucfirst(strtolower($piece));
			}
			return $result;
		}
	}
	
	protected function _denormalizeMethodName($method)
	{
		if(is_string($method))
		{
			$result = '';
			preg_match_all('/[A-Z][^A-Z]*/', $method, $matches);
			$pieces = array();
			foreach($matches[0] as $match)
			{
				$pieces[] = strtolower($match);
			}
			return implode('_', $pieces);
		}
	}
	/*
	protected $_identity = null;
	
	public function init()
	{
		$this->_identity = Zend_Auth::getInstance()->getIdentity();
	}
	
	public function direct($property = null)
	{
		if(null === $property)
		{
			return $this->_identity;	
		}
		
		if(isset($this->_identity->$property))
		{
			return $this->_identity->$property;
		} 
	}
	
	public function toArray()
	{
		return get_object_vars($this->_identity);
	}
	
	public function render($partial, $module = null)
	{
		$data = array();
		if(null !== $this->_identity)
		{
			$data = $this->toArray();
		}
		
		return $this->getActionController()->view->partial($partial, $module, $data);
	}
	
	public function set($key, $value, $namespace = null, $expiration = 1800, $hops = null)
	{
		if(null !== $namespace)
		{
			$n = new Zend_Session_Namespace();	
		}
		else 
		{
			$n = new Zend_Session_Namespace($namespace);	
		}
		
		$n->setExpirationSeconds($expiration, $key);
		
		if(is_integer($hops))
			$n->setExpirationHops($hops, $key);
		
		$n->$key = $value;
	}
	
	public function get($key, $namespace = null)
	{
		if(null !== $namespace)
		{
			$n = new Zend_Session_Namespace();	
		}
		else 
		{
			$n = new Zend_Session_Namespace($namespace);	
		}
		
		return $n->$key;
	}
	
	public function delete($key, $namespace = null)
	{
		if(null !== $namespace)
		{
			$n = new Zend_Session_Namespace();	
		}
		else 
		{
			$n = new Zend_Session_Namespace($namespace);	
		}
		
		unset($n->$key);
	}
	*/
}