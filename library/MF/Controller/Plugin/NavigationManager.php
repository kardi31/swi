<?php

class MF_Controller_Plugin_NavigationManager extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$frontController = Zend_Controller_Front::getInstance();

		$bootstrap = $frontController->getParam('bootstrap');

		$moduleName = $request->getModuleName();


		$bootstrap->bootstrap('view');
		$view = $bootstrap->getResource('view');

		$bootstrap->bootstrap('navigation');

		if($bootstrap->hasResource('navigation'))
		{
			$navigation = $bootstrap->getResource('navigation');
            $options = $bootstrap->getPluginResource('navigation')->getOptions();
		}

		if(isset($navigation))
		{
        	$view->navigation($navigation);

        	$current_route = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();

            $current_page = $view->navigation()->findOneBy('route', $current_route);
          
            if($current_page)
            {
            	$current_page->setActive();
            }
		}
		
		$view->navigation()->menu()
						   ->setMinDepth(1)
						   ->setMaxDepth(1);
		
		$view->navigation()->breadcrumbs()
						   ->setMinDepth(1)
        				   ->setLinkLast(false)
        				   ->setSeparator($options['breadcrumbs']['separator']);
		
        $auth = Zend_Auth::getInstance();
		if($bootstrap->hasResource('acl'))
		{
			if($auth->hasIdentity())
			{
				$user = $auth->getIdentity();
				$role = $user->role;
			}
			else
			{
				$role = 'guest';
			}
			$acl = $bootstrap->getResource('acl');	
			$view->navigation()->menu()->setAcl($acl)->setRole($role);
			$view->navigation()->breadcrumbs()->setAcl($acl)->setRole($role);
		}
 
        $view_renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

		$view_renderer->setView($view);


	}

}