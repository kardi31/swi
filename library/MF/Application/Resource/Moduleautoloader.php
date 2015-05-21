<?php 

class MF_Application_Resource_Moduleautoloader extends Zend_Application_Resource_ResourceAbstract
{
	protected $_bootstrap;
	protected $_front;
	
	public function init() {
		
		$this->_bootstrap = $this->getBootstrap();
		$this->_front = $this->_bootstrap->bootstrap('FrontController')->getResource('FrontController');
			
		$moduleDirs = array();
		foreach($this->_front->getControllerDirectory() as $module => $dir) {
			$moduleDirs[$module] = $this->_front->getModuleDirectory($module);
		}
		
		$paths = array();
		foreach($moduleDirs as $module => $dir) {
			
			$autoloader = new Zend_Application_Module_Autoloader(array(
				'basePath' => $dir,
				'namespace' => '',
				'resourceTypes' => array(
					'library' => array(
						'path' => 'library/',
						'namespace' => ucfirst($module)
					)
				)
			));

			$paths[] = $dir . DIRECTORY_SEPARATOR . 'library';
		}

		
		
		$paths[] = get_include_path();
		set_include_path(implode(PATH_SEPARATOR, $paths));
	}
}