<?php 

class Media_Resource_Elfinder extends Zend_Application_Resource_ResourceAbstract
{
	public function init() {
		if(!require_once 'elFinder.class.php') {
			throw new Zend_Controller_Action_Exception('Cannot find elFinder clsss file');
		}

		$options = $this->getOptions();
		$this->getBootstrap()->getApplication()->getContainer()->set('elfinder', $options);
		return $options;
	}
}