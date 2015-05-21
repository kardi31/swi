<?php 

class User_Plugin_Action_Helper_User extends Zend_Controller_Action_Helper_Abstract
{
	protected $_user;
	
	public function init() {
		if(Zend_Controller_Front::getInstance()->hasPlugin('User_Plugin_Guard')) {
			$this->_user = Zend_Controller_Front::getInstance()->getPlugin('User_Plugin_Guard')->getAuthenticatedUser();
		}
	}
	
	public function direct() {
		return $this->_user;	
	}

}