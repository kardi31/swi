<?php

/**
 * UserService
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Service_Auth extends Zend_Controller_Plugin_Abstract implements MF_Service_ServiceInterface {

    protected $options = array(
        'default_role_name' => 'guest',
        'requested_url_ns' => 'RequestedURL',
        'login_module' => 'user',
        'login_controller' => 'auth',
        'login_action' => 'login',
        'noauth_module' => 'user',
        'noauth_controller' => 'error',
        'noauth_action' => 'noauth'
    );
    protected $acl;
    protected $authAdapter;
    protected $userProvider;
    protected $authenticatedUser;
    protected $currentRoleName;
    
    public function __construct($options) {
        $this->options = array_merge($this->options, $options);
    }
    
    public function setAcl($acl) {
        $this->acl = $acl;
    }
    
    public function setAuthAdapter($authAdapter) {
        $this->authAdapter = $authAdapter;
        return $this;
    }
    
    public function setUserProvider(User_Model_UserProvider_Interface $userProvider) {
        $this->userProvider = $userProvider;
        return $this;
    }
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if(null === $this->acl) {
    		throw new Exception('No Acl service specified');
    	}
        $role = $this->getCurrentRoleName();
        $resource = $request->getModuleName() . ':' . $request->getControllerName();
    	$privilege = $request->getActionName();

        if(!$this->acl->isAllowed($role, $resource, $privilege)) {
            if(!Zend_Auth::getInstance()->hasIdentity()) {
                $this->_saveRequestUri($request);

                $this->redirectToLoginAction($request);
            } else {
                $this->redirectToNoAuthAction($request);
            }
        }
    }
    
    public function authenticate($identity, $credential) {
        $this->authAdapter->setIdentity($identity);
        $this->authAdapter->setCredential($credential);
        return Zend_Auth::getInstance()->authenticate($this->authAdapter);
    }
    
	public function getAuthenticatedUser() {
        if(null == $this->authenticatedUser) {
            if(Zend_Auth::getInstance()->hasIdentity()) {
                if(null !== $identity = Zend_Auth::getInstance()->getIdentity()) {
                    $user = $this->userProvider->findUserByIdentity($identity);
                    if($user instanceof User_Model_User_Interface) {
                        $this->authenticatedUser = $user;
                    }
                }
            }
        }
		return $this->authenticatedUser;
	}
    
    public function setCurrentRoleName($currentRoleName) {
        $this->currentRoleName = $currentRoleName;
    }
    
    public function getCurrentRoleName() {
        if(null == $this->currentRoleName) {
            if(Zend_Auth::getInstance()->hasIdentity() && null !== $this->getAuthenticatedUser()) {
                $this->currentRoleName = $this->getAuthenticatedUser()->getRole();
            } else {
                $this->currentRoleName = $this->options['default_role_name'];
            }
        }

        return $this->currentRoleName;
    }
    
    public function setRememberMeCookie($set = true) {
        if($set) {
            setcookie('remember_me', 'true', time()+60*60*24*365, '/');
        } else {
            setcookie('remember_me', '', time()-42000, '/');
        }
    }
    
    public function destroyAuthentication() {
    	Zend_Auth::getInstance()->clearIdentity();
        $this->setRememberMeCookie(false);
    }
    
    public static function factory(array $options = array()) {
        $class = __CLASS__;
        return new $class($options);
    }
    
    public function getOptions()
    {
        return $this->options;
    }

    public static function getType()
    {
        return get_called_class();
    }
    
    public function redirectToLoginAction($request) {
    	$request->setModuleName($this->options['login_module'])
            ->setControllerName($this->options['login_controller'])
            ->setActionName($this->options['login_action']);
    }
    
	public function redirectToNoAuthAction($request) {
    	$request->setModuleName($this->options['noauth_module'])
            ->setControllerName($this->options['noauth_controller'])
            ->setActionName($this->options['noauth_action']);
    }
    
    public function getRequestedUrl($destroy = false) {
    	$session = new Zend_Session_Namespace($this->options['requested_url_ns']);
    	if(!isset($session->url)) {
    		return false;
    	} else {
    		$url = $session->url;
            if($destroy) unset($session->url); 
        	return $url;	
    	}
    }
    
    protected function _saveRequestUri($request) {
    	$requested = $request->getRequestUri();
        $session = new Zend_Session_Namespace($this->options['requested_url_ns']);
        $session->url = $requested;
    }
    
}

