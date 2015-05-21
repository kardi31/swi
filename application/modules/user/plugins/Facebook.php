<?php

require_once 'facebook-php-sdk/src/base_facebook.php';
require_once 'facebook-php-sdk/src/facebook.php';

class User_Plugin_Facebook extends Zend_Controller_Plugin_Abstract
{
    protected $serviceBroker;
    protected $config;
    protected $facebookSdk;
    
    public function __construct($config) {
        $this->serviceBroker = MF_Service_ServiceBroker::getInstance();
        
        $this->config = $config;
    }
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $facebookService = $this->serviceBroker->getService('User_Service_Facebook');
        $authService = $this->serviceBroker->getService('User_Service_Auth');
        $userService = $this->serviceBroker->getService('User_Service_User');
        
        $facebook = $this->getFacebookSdk();

        $user = $authService->getAuthenticatedUser();
        if($request->getQuery('code') && $request->getQuery('state') && $fbId = $facebook->getUser()) {
            $userDataFacebook = $facebook->api('/me');
            $user = $userService->connectWithFacebook($user, $fbId, $userDataFacebook);
            $facebookService->forceIdentity($user->getEmail());
        } elseif(!$user && $fbId = $facebook->getUser()){
           if($user = $userService->getUser($fbId, 'fb_id')) {
                $facebookService->forceIdentity($user->getEmail());
            }
        }
    }
    
    public function getFacebookSdk() {
        if(null == $this->facebookSdk) {
            $this->facebookSdk = new Facebook($this->config);
        }
        return $this->facebookSdk;
    }
    
    public function destroyAuthentication() {
        $this->getFacebookSdk()->destroySession();
    }

}
