<?php

class MF_Controller_Plugin_LayoutSwitcher extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$moduleName = $request->getModuleName();
		
		if(file_exists(Zend_Layout::getMvcInstance()->getLayoutPath() . '/' . $moduleName . '.phtml'))
			Zend_Layout::getMvcInstance()->setLayout($moduleName);
			return;
		Zend_Layout::getMvcInstance()->setLayout('layout');
	}
}