<?php 

class MF_Application_Resource_Security extends Zend_Application_Resource_ResourceAbstract
{
	protected $_options = array(
		'auth_session_namespace' => 'Zend_Auth',
		'requested_url_ns' => 'RequestedURL'
	);
	
	public function init() {
		$this->_options = array_merge($this->_options, $this->getOptions());

		if(!isset($this->_options['security_enabled']) || !$this->_options['security_enabled'] == true) {
			// security disabled
			return;
		}
		
		if(class_exists($this->_options['security_plugin'])) {
			
			$bootstrap = $this->getBootstrap(); 
			$bootstrap->bootstrap('FrontController');
			$bootstrap->bootstrap('acl');
			$front = $bootstrap->getResource('FrontController');
			
			if(!$front->hasPlugin($this->_options['security_plugin'])) {
				$front->registerPlugin(new  $this->_options['security_plugin']);
			}
			$plugin = $front->getPlugin($this->_options['security_plugin']);
			$plugin->setOptions($this->_options);	

			if(array_key_exists('user_provider', $this->_options)) {
				$bootstrap->bootstrap('db');
				$plugin->setUserProvider(new $this->_options['user_provider']);	
			}
			
			if($bootstrap->hasResource('acl')) {
				$acl = $bootstrap->getResource('acl');
				$plugin->setAcl($acl);
			}
		}
	}

}