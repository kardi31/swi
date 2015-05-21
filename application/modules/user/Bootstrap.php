<?php

class User_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/user/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/user',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'User'
                )
            )
        ));
    }
    
    protected function _initResourcePaths() {       
        $this->getPluginLoader()->addPrefixPath('User_Resource', APPLICATION_PATH . '/modules/user/library/Resource');
    }

    protected function _initAuthService() {
        $front = $this->bootstrap('FrontController')->getResource('FrontController');
        $acl = $this->getApplication()->bootstrap('acl')->getResource('acl');
        $serviceBroker = MF_Service_ServiceBroker::getInstance();
        $authOptions = array(
            'noauth_module' => 'default',
            'noauth_controller' => 'error',
            'noauth_action' => 'noauth'
        );
        $authService = $serviceBroker->getService('User_Service_Auth', $authOptions);
        $authService->setAcl($acl);

//        $users = array(
//            'admin1' => array(
//                'first_name' => 'Tomek',
//                'last_name' => '',
//                'identity' => 'tomek',
//                'credential' => '098f6bcd4621d373cade4e832627b4f6',
//                'active' => true,
//                'role' => 'superadmin'
//            )
//        );
        
//        $users = array(
//            'admin' => array(
//                'first_name' => 'Michał',
//                'last_name' => 'Kowalik',
//                'identity' => 'admin',
//                'credential' => '0192023a7bbd73250516f069df18b500',
//                'active' => true,
//                'role' => 'superadmin'
//            )
//        );
        
   
        $userProviderChain = new User_Model_UserProvider_Chain();
        $userProviderChain->addUserProvider('doctrine', new User_Model_UserProvider_Doctrine('User_Model_Doctrine_User', 'email'));
        $userProviderChain->addUserProvider('array', new User_Model_UserProvider_Array(array(
            'users' => $users
        )));
        $authService->setUserProvider($userProviderChain);
        
        $adapter = new MF_Auth_Adapter_Doctrine('User_Model_Doctrine_User');
        $adapter->setIdentityField('email');

        $arrayAdapter = new MF_Auth_Adapter_Array($users);

        $chainAdapter = new MF_Auth_Adapter_Chain();
        $chainAdapter->addAdapter($adapter);
        $chainAdapter->addAdapter($arrayAdapter);
        $chainAdapter->setPasswordEncoder(new User_PasswordEncoder());
        
        $authService->setAuthAdapter($chainAdapter);
        
        $front->registerPlugin($authService);
    }
    
    protected function _initActionHelpers() {
		Zend_Controller_Action_HelperBroker::addHelper(new User_Controller_Action_Helper_Account());
    }
    
    protected function _initProfileNavigation() {
        $options = $this->getApplication()->getPluginResource('navigation')->getOptions();

        $acl = $this->getApplication()->bootstrap('acl')->getResource('acl');
//		$translator = $this->getApplication()->bootstrap('translate')->getResource('translate');
		$view = $this->getApplication()->bootstrap('view')->getResource('view');

		Zend_Navigation_Page::setDefaultPageType('mvc');
        
//		$container = new Zend_Navigation($options['pages']);
        $profileContainer = new Zend_Navigation($options['pages']['profilecontainer']['pages']);

//        $view->navigation($container);
//		$view->navigation()->setAcl($acl);
//        $view->navigation()->setTranslator($translator);
//		$view->navigation()->setUseAcl();
//		$view->navigation()->setUseTranslator(); // translator injected in menu/Bootstrap class
        
        $view->assign('profilecontainer', $profileContainer);
        
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		
//		return $container;
    }
    
//    protected function _initFacebook() {
//        $config = array('appId' => '412917462160297', 'secret' => '9497ddcecd110f62607613366ba8fa64');
//
//        $facebook = new User_Plugin_Facebook($config);
//        
//	$this->bootstrap('FrontController');
//	$front = $this->getResource('FrontController');
//        $front->registerPlugin($facebook);
//
//        
//        $facebookHelper = new User_View_Helper_Facebook($config);
//        $view = $this->getApplication()->bootstrap('view')->getResource('view');
//        $view->registerHelper($facebookHelper, 'facebook');
//    }

}

