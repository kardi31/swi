<?php


class User_Plugin_Guard extends Zend_Controller_Plugin_Abstract
{
	private $_container;
	private $_acl;
	private $_auth;
	private $_role;
	private $_roles = array();
	private $_user;
	private $_userProviders = array();
	private $_options = array();
	private $_remember_me = false;
    
    
	private $_lock;
	
	public function __construct() {
    	$this->_auth = Zend_Auth::getInstance();
	}
	
	public function setContainer($container) {
		$this->_container = $container;
	}
	
	public function setAcl(Zend_Acl $acl) {
		$this->_acl = $acl;
	}

	protected function _getAcl() {
		return $this->_acl;
	}
	
	public function setOptions(array $options) {
		$this->_options = $options;
	}
	
	public function addUserProvider($name, $userProvider) {
		$this->_userProviders[$name] = $userProvider;
	}
		
	public function getUserProviders() {
		return $this->_userProviders;
	}
	
	public function getAuthenticatedUser() {
        if($this->_auth->hasIdentity()) {
            foreach($this->getUserProviders() as $name => $provider) {
                if(null !== $identity = $this->_auth->getIdentity()) {
                    $user = $provider->findUserByIdentity($identity);
                    if($user instanceof User_Model_User_Interface) {
                        return $user;
                    }
                }
            }
        }
		return null; 
	}
	
    /**
     * dispatchLoopStartup
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {

    	$this->_request = $request;

        if($request->getCookie('remember_me') == 'true') {
            $this->_remember_me = true;
        }

        if($this->_isSecured($request->getActionName(), $request->getControllerName(), $request->getModuleName())) {
            if($this->_isNotLoggedIn()) {
                // writing requested uri
                $this->_saveRequestUri();

                // redirecting to login action
                $this->_redirectToLoginAction();
                $request->setDispatched(false);
            } else {
                // redirecting to noauth action
                $this->_redirectToNoAuthAction();
            }
        }
        
        $this->_restoreAuthSession();
    }
    
    protected function _saveRequestUri() {
    	$requested = $this->_request->getRequestUri();
        $session = new Zend_Session_Namespace($this->_options['requested_url_ns']);
        $session->url = $requested;
    }

    public function getRequestedUrl() {
    	$session = new Zend_Session_Namespace($this->_options['requested_url_ns']);
    	if(!isset($session->url)) {
    		return false;
    	} else {
    		$url = $session->url;
    		unset($session->url); 
        	return $url;	
    	}
    }
    
    public function destroyAuthentication() {
    	$this->_auth->clearIdentity();
        $this->setRememberMeCookie(false);
    }
    
    protected function _getRole() {
    	if(null === $this->_role) {

	    	if($this->_auth->hasIdentity() && null !== $this->getAuthenticatedUser()) {

	            // user inactive
	            if($this->getAuthenticatedUser()->isActive() == false)  {
	                //$this->_auth->clearIdentity();
	                $this->_redirectToNoAuthAction();
	            }
	            
				// restoring session
//				$this->_restoreAuthSession();
	            
				$this->_role = $this->getAuthenticatedUser()->getRole(); 
	            
	        } else {
	         	// user not logged in
	         	$this->_role = $this->_options['anonymous_role']; 
	        }
    	}
        return $this->_role;
    }
    
    protected function _getRoles() {
    	if(empty($this->_roles)) {

	    	if($this->_auth->hasIdentity() && null !== $this->getAuthenticatedUser()) {

	            // user inactive
	            if($this->getAuthenticatedUser()->isActive() == false)  {
	                //$this->_auth->clearIdentity();
	                $this->_redirectToNoAuthAction();
	            }
	            
				// restoring session
//				$this->_restoreAuthSession();
	            
                if($this->getAuthenticatedUser() instanceof User_Model_Doctrine_User) {
                    $roles = $this->getAuthenticatedUser()->getRoles(); 
                } elseif($this->getAuthenticatedUser() instanceof User_Model_User_Memory) {
                    $roles = array($this->getAuthenticatedUser()->getRole());
                }
                
				$this->_roles = $roles;
	        }
            if(empty($this->_roles)) {
                $this->_roles = array($this->_options['anonymous_role']);
            }
    	}
        return $this->_roles;
    }
    
    protected function _restoreAuthSession() {
        $authSessionNamespace = new Zend_Session_Namespace($this->_options['auth_session_namespace']);
        $expirationSeconds = ($this->_remember_me === true) ? 60 * 60 * 24 * 365 : $this->_options['expiration_seconds'];
  		$authSessionNamespace->setExpirationSeconds($expirationSeconds);
//        setcookie('SET', (time() + $expirationSeconds) * 1000, null, '/');
    }
    
    public function setRememberMeCookie($set = true) {
        if($set) {
            setcookie('remember_me', 'true', time()+60*60*24*365, '/');
        } else {
            setcookie('remember_me', '', time()-42000, '/');
        }
    }
    
    protected function _isSecured($action, $controller, $module) {
    	if(null === $this->_getAcl()) {
    		return true;
    	}
    	
    	if(APPLICATION_ENV === 'testing') {
    		//return false;
    	}
    	
    	$resource = $module . ':' . $controller;
    	$privilege = $action;
        
        if(!is_array($this->_getRoles())) {
            return true;
        }

        foreach($this->_getRoles() as $role) {
            if($this->_getAcl()->isAllowed($role, $resource, $privilege)) {
                return false;
            }
        }
        
        return true;
    	//return !$this->_getAcl()->isAllowed($this->_getRole(), $resource, $privilege);
    }
    
    protected function _isNotLoggedIn() {
        return !Zend_Auth::getInstance()->hasIdentity();
    	//return ($this->_getRole() == $this->_options['anonymous_role']);
    }
    
    protected function _redirectToLoginAction() {
    	$this->_request->setModuleName($this->_options['login_module'])
            ->setControllerName($this->_options['login_controller'])
            ->setActionName($this->_options['login_action']);
    }
    
	protected function _redirectToNoAuthAction() {
    	$this->_request->setModuleName($this->_options['noauth_module'])
            ->setControllerName($this->_options['noauth_controller'])
            ->setActionName($this->_options['noauth_action']);
    }
    

}
