<?php


class MF_Controller_Plugin_AccessKeeper extends Zend_Controller_Plugin_Abstract
{
	private $_acl;
	private $_auth;
	private $_role;
	private $_options = array(
		'auth_session_namespace' => 'Zend_Auth',
		'prev_uri_namespace' => 'prevUri'
	);
	
	public function setAcl(Zend_Acl $acl) {
		$this->_acl = $acl;
	}
	
	public function setOptions(array $options) {
		$this->_options = array_merge($this->_options, $options);
	}
	
    /**
     * dispatchLoopStartup
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
    	$this->_auth = Zend_Auth::getInstance();

        if($this->_isSecure($request->getActionName(), $request->getControllerName(), $request->getModuleName())) {
        	if($this->_isAnonymous()) {
        		// writing requested uri
        		$requested = $request->getRequestUri();
	            $prevSession = new Zend_Session_Namespace($this->_options['prev_uri_namespace']);
	            $prevSession->uri = $requested;
	            
	            // redirecting to login action
        		$this->_redirectToLoginAction();
	            $request->setDispatched(false);
        	} else {
	            // redirecting to noauth action
                $this->_redirectToNoAuthAction();
        	}
        }
    }
    
    protected function _getRole() {
    	if(null === $this->_role) {
	    	if($this->_auth->hasIdentity()) {
	            $user = $this->_auth->getIdentity();
	
	            // user inactive
	            if($user->isActive() == false)  {
	                $this->_auth->clearIdentity();
	                $this->_redirectToNoAuthAction();
	            }
	            
				// restoring session
				$this->_restoreAuthSession();
	            
				$this->_role = $user->getRole(); 
	            
	        } else {
	         	// user not logged in
	         	$this->_role = $this->_options['anonymous_role']; 
	        }
    	}
        return $this->_role;
    }
    
    protected function _restoreAuthSession() {
        $authSessionNamespace = new Zend_Session_Namespace($this->_options['auth_session_namespace']);
  		$authSessionNamespace->setExpirationSeconds($this->_options['expiration_seconds']);
    }
    
    protected function _isSecure($action, $controller, $module) {
    	$resource = $module . ':' . $controller;
    	$privilege = $action;
    	return !$this->_acl->isAllowed($this->_getRole(), $resource, $privilege);
    }
    
    protected function _isAnonymous() {
    	return ($this->_getRole() == $this->_options['anonymous_role']);
    }
    
    protected function _redirectToLoginAction() {
    	$request->setModuleName($this->_options['login_module'])
		    	->setControllerName($this->_options['login_controller'])
		    	->setActionName($this->_options['login_action']);
    }
    
	protected function _redirectToNoAuthAction() {
    	$request->setModuleName($this->_options['noauth_module'])
		    	->setControllerName($this->_options['noauth_controller'])
		    	->setActionName($this->_options['noauth_action']);
    }
    

}
