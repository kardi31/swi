<?php

/**
 * User_Model_FacebookAdapter
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Model_FacebookAdapter 
{
    protected $_appId;
    protected $_appSecret;
    protected $_appUrl;
    protected $_user;

    public function __construct() {
        require_once 'facebook-php-sdk/src/base_facebook.php';
        require_once 'facebook-php-sdk/src/facebook.php';
        $this->_appId = '369187686447071';
        $this->_appSecret = 'd0c863778a2979a600d6b9adc3a77b0e';
        $this->_appUrl = 'http://ajurweda.localhost/';
    }
    
    public function get($key) {
        return (isset($this->_user->{$key})) ? $this->_user->{$key} : false;
    }
    
    public function authenticate($code) {
        //$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;

        if(!$code) {
            return false;
        }

        if($_REQUEST['state'] == $_SESSION['state']) {
            $token_url = "https://graph.facebook.com/oauth/access_token?"
            . "client_id=" . $this->_appId . "&redirect_uri=" . urlencode($this->_appUrl)
            . "&client_secret=" . $this->_appSecret . "&code=" . $code;
        Zend_Registry::get('Logger')->log($token_url, 4);
            $response = file_get_contents($token_url);
            $params = null;
            parse_str($response, $params);
        Zend_Registry::get('Logger')->log('response: ' . serialize($response), 4);
            $graph_url = "https://graph.facebook.com/me?access_token=" 
            . $params['access_token'];

            $this->_user = json_decode(file_get_contents($graph_url));
            return true;
        }
        else {
            throw new Exception('The state does not match. You may be a victim of CSRF');
        }
    }
    
    public function getLoginDialogUrl() {
        // S_SESSION['state'] is set in facebook view helper before call this method
        $dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" 
        . $this->_appId . "&redirect_uri=" . urlencode($this->_appUrl . 'user/auth/facebook-login') . "&state="
        . $_SESSION['state'];

        //return "<script> top.location.href='" . $dialog_url . "'</script>";
        return $dialog_url;
    }
}

