<?php 
class MF_Controller_Plugin_ViewSupplier extends Zend_Controller_Plugin_Abstract
{
	protected $_view;
	
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$this->_view = $bootstrap->getResource('view');
		
		$params = $request->getParams();
		
		if(isset($params['module']))
			$this->_view->assign('module', $params['module']);
			
		if(isset($params['controller']))
			$this->_view->assign('controller', $params['controller']);
			
		if(isset($params['action']))
			$this->_view->assign('action', $params['action']);

		if(isset($params['lang'])) {
			$this->_view->assign('lang', $params['lang']);
		}
		elseif(Zend_Registry::get('Zend_Locale')) {
			$this->_view->assign('lang', Zend_Registry::get('Zend_Locale')->getLanguage());
		}


		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$appOptions = $bootstrap->getOptions();
		$moduleName = $request->getModuleName();
		if(array_key_exists($moduleName, $bootstrap->getOptions()) && array_key_exists('view', $appOptions[$moduleName]['resources']))
		{
			if(isset($appOptions[$moduleName]['resources']['view']['css']))
	        {
	        	if(is_array($appOptions[$moduleName]['resources']['view']['css']))
	        	{
	        		$flag = 0;
	        		foreach($appOptions[$moduleName]['resources']['view']['css'] as $files => $stylesheet)
		        	{
		        		if($flag == 0)
		        		{
		        			$this->_view->headLink()->setStylesheet($stylesheet);
		        			$flag = 1;
		        		}
		        		else 
		        		{
		        			$this->_view->headLink()->appendStylesheet($stylesheet);	
		        		}
		        	}
	        	}
	        	else
	        	{
	        		$this->_view->headLink()->setStylesheet($appOptions[$moduleName]['resources']['view']['css']);	
	        	}
	        }
			
			if(isset($appOptions[$moduleName]['resources']['view']['js']))
	        {
	        	if(is_array($appOptions[$moduleName]['resources']['view']['js']))
	        	{
	        		$flag = 0;
	        		foreach($appOptions[$moduleName]['resources']['view']['js'] as $files => $stylesheet)
		        	{
		        		if($flag == 0)
		        		{
		        			$this->_view->headScript()->setFile($stylesheet);
		        			$flag = 1;
		        		}
		        		else 
		        		{
		        			$this->_view->headScript()->appendFile($stylesheet);	
		        		}
		        	}
	        	}
	        	else
	        	{
	        		$this->_view->headScript()->appendFile($appOptions[$moduleName]['resources']['view']['js']);	
	        	}
	        }
	        	
		}
		
	
	}
}