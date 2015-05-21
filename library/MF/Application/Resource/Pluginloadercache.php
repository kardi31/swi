<?php

class MF_Application_Resource_Pluginloadercache extends Zend_Application_Resource_ResourceAbstract
{
	protected $_options = array();
	
	public function init()
	{
		$this->_options = $this->getOptions();
		$this->_setFileIncCache();	
	}
	
	protected function _setFileIncCache()
	{
		if(file_exists($this->_options['classFileIncCache'])) 
		{
    		include_once $this->_options['classFileIncCache'];
		    Zend_Loader_PluginLoader::setIncludeFileCache($this->_options['classFileIncCache']);
		}
		
	}
}