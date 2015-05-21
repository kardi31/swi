<?php

require_once 'facebook-php-sdk/src/base_facebook.php';
require_once 'facebook-php-sdk/src/facebook.php';
        
/**
 * User_View_Helper_Facebook
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_View_Helper_Facebook extends Zend_View_Helper_Abstract
{
    public $view;
    protected $config;
    protected $facebookSdk;
    protected $data;

    public function __construct($config) {
        $this->config = $config;
    }
    
    public function facebook() {
        if(null === $this->facebookSdk) {
            $this->facebookSdk = new Facebook($this->config);
        }
        if(!isset($this->view->facebook)) {
            $this->view->facebook = $this;
        }
        return $this->view->facebook;
    }
        
    /**
     * Indicates whether user is logged in and has authorized the app
     * 
     * @return boolean 
     */
    public function isConnected() {
        if(!$this->facebookSdk->getUser()) {
            return false;
        }
        try {
            $response = $this->facebookSdk->api('/me?fields=installed');
            if(array_key_exists('installed', $response) && $response['installed'] == true) {
                return true;
            }
        } catch(Exception $e) {
            Zend_Registry::get('Logger')->log($e->getMessage(), 6);
        }
        
        return false;
    }
    
    public function pictureUrl() {
        return 'https://graph.facebook.com/' . $this->facebookSdk->getUser() . '/picture';
    }
    
    public function loginUrl() {
        return $this->facebookSdk->getLoginUrl();
    }
    
    public function logoutUrl() {
        return $this->facebookSdk->getLogoutUrl();
    }
    
    public function user($key = null) {
        if(null === $key) {
            return $this->facebookSdk->getUser();
        } else {
            // handle api exception
            try {
                if(!count($this->data)) {
                    $this->data = $this->facebookSdk->api('/me');
                }
                if(array_key_exists($key, $this->data)) {
                    return $this->data[$key];
                }
            } catch(Exception $e) {
                Zend_Registry::get('Logger')->log($e->getMessage(), 6);
            }
        }
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}

