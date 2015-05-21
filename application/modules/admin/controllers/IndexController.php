<?php

class Admin_IndexController extends MF_Controller_Action
{
	protected $_moduleAware;
	
    public function init() {
    	$this->_helper->layout->setLayout('admin');
        
    	$this->_moduleAware = Zend_Controller_Front::getInstance()->getPlugin('Admin_Plugin_ModuleAware');
    	
        $user = $this->_helper->user();
        if($user instanceof User_Model_User_Interface) {
            $this->view->userFullName = $user->getFirstName() . ' ' . $user->getLastName();
            $this->view->user = $user;
        }
        
        parent::init();
    }

    public function indexAction() {
    	$this->_prepareLayout();
        $this->_forward('dashboard', 'admin');
    }

    public function awareAction() {
    	$this->_helper->viewRenderer->setNoRender();

        
    	if(null === $this->_moduleAware->getCalledRequest()) {
    		throw new Zend_Controller_Dispatcher_Exception();
    	}
    	
    	if(!$this->getRequest()->isXmlHttpRequest() && $this->_moduleAware->getCalledRequest()->getControllerName() != 'media') {
            $this->_prepareLayout();
    	} else {
            $this->_helper->layout->disableLayout();
        }
		
        $this->_forward($this->_moduleAware->getCalledRequest()->getActionName(), 'admin', $this->_moduleAware->getCalledRequest()->getControllerName());
    	
    }
    
    public function sideMenuAction() {
    	$this->_helper->viewRenderer->setResponseSegment('sidemenu');
    }
    
    public function userProfilAction() {
    	$this->_helper->viewRenderer->setResponseSegment('user_profil');
    	
    	$user = $this->_helper->user();
    	$this->view->assign('user', $user);
    }
    
    public function javascriptAction() {
    	$this->_helper->viewRenderer->setResponseSegment('javascript');
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        
        if(isset($options['domain']))
            $this->view->assign('domain', $options['domain']);
    }
    
    protected function _prepareLayout() {
        $this->view->navigation()->setRole('admin');
        
        $newsService = $this->_service->getService('News_Service_News');
        $userService = $this->_service->getService('User_Service_User');
        

        
        
        
        $articleCount = $newsService->getAllArticles(true);
        $this->view->assign('articleCount', $articleCount);
        
        if(!$this->view->adminTitle && $current = $this->view->navigation()->findOneBy('active', true)) {
            $this->view->adminTitle = $this->view->translate($current->getLabel());
        }
        
        
        if(!$dashboardTime = $this->_helper->user->get('dashboard_time')) {
            $dashboardTime = array();
        }
        
        if(!isset($dashboardTime['new_articles'])) {
            $dashboardTime['new_articles'] = time();
        }
        
        
        
        $clients = $userService->getUsersByRole('client');
        $agents = $userService->getUsersByRole('agent');
        if($clients && $agents) {
            $usersCount = $clients->count() + $agents->count();
            $clientsRate = ($clients->count() / $usersCount) * 100;
            $agentsRate = ($agents->count() / $usersCount) * 100;
            $this->view->assign('clientsRate', $clientsRate);
            $this->view->assign('agentsRate', $agentsRate);
        }
        
        $this->_helper->user->set('dashboard_time', $dashboardTime);
            
//    	$this->_helper->actionStack('side-menu');
    	$this->_helper->actionStack('user-profil');
    	$this->_helper->actionStack('javascript');
        
        
    }
}
