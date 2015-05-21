<?php

class MF_Application_Resource_Configloader extends Zend_Application_Resource_ResourceAbstract
{
	protected $_skipFiles = array('wurfl-config.php');
    protected $_config;
    protected $_options = array();
    
    protected $_bootstrap;
    protected $_front;
    
    public function init() {
    	
        $this->_options = $this->getOptions();
        
        // cache instance
        $cache = Zend_Cache::factory('Core', 'File', 
        	array('caching' => true, 'automatic_serialization' => true), 
        	array('cache_dir' => $this->_options['cache_dir']));

        // can be disabled for example in development environment
        if($this->_options['cache_enabled'] == '0') {
        	$this->_loadConfig();
        // save settings in cache if this is empty
        } elseif(($this->_config = $cache->load('app_options_' . APPLICATION_ENV)) == false) {
        	
	        // loading and caching config
           	$this->_loadConfig();
           	$cache->save($this->_config, 'app_options_' . APPLICATION_ENV);
        }
        
        // passing option to current application
        $this->getBootstrap()->setOptions($this->_config);

        // return config
        return $this->_config;
    }

    protected function _loadConfig() {
    	
        // getting bootstrap and front controller instances
	    $this->_bootstrap = $this->getBootstrap();
	    $this->_bootstrap->bootstrap('frontController');
	    $this->_front = $this->_bootstrap->getResource('frontController');
	        
    	// load application configs
    	$this->_loadApplicationConfig();

    	// loading modules level configuration can be omitted by giviing option with_modules value 1
    	if(array_key_exists('with_modules', $this->_options) && $this->_options['with_modules'] == '1') {
    		$this->_loadModulesConfig();
    	}
    }
    
    /**
     * Load application level configuration files
     *
     * @return void
     */
    protected function _loadApplicationConfig() {
    	
        if (!($this->_bootstrap instanceof Zend_Application_Bootstrap_Bootstrap)) {
            throw new Zend_Application_Exception('Invalid bootstrap class');
        }

        $configPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'resources';

        // loads application config files options
        if (file_exists($configPath)) {
        	
                $cfgdir = new DirectoryIterator($configPath);
                $appOptions = $this->getBootstrap()->getOptions();

                foreach ($cfgdir as $file) {
                	if ($file->isFile()) {
                        $filename = $file->getFilename();
				
                        if(in_array($filename, $this->_skipFiles)) continue;
						
                        $options = $this->_loadOptions($configPath
                                 . DIRECTORY_SEPARATOR . $filename);

                    	if (($len = strpos($filename, '.')) !== false) {
                            $cfgtype = substr($filename, 0, $len);
                        } else {
                            $cfgtype = $filename;
                        }

                        if (strtolower($cfgtype) != 'application') {
                        	$appOptions['resources'][$cfgtype] = $options;
                        }
                    }
                }
                $this->getBootstrap()->setOptions($appOptions);
        }
        
        else {
        	continue;
        }

        $this->_config = $this->getBootstrap()->getOptions();
    }
    
    /**
     * Load module level configuration files
     *
     * @return void
     */
    protected function _loadModulesConfig() {
    	
		$modulesDirs = array();
		
		foreach($this->_front->getControllerDirectory() as $module => $dir) {
			$moduleDirs[$module] = $this->_front->getModuleDirectory($module);
		}
		
		foreach($moduleDirs as $module => $dir) {
			
			$configPath = $dir . DIRECTORY_SEPARATOR . 'configs';
			
			if(file_exists($configPath)) {
				$cfgdir = new DirectoryIterator($configPath);
                $appOptions = $this->getBootstrap()->getOptions();
                
                foreach ($cfgdir as $file) {
                	if ($file->isFile()) {
                        $filename = $file->getFilename();
						
                        if(in_array($filename, $this->_skipFiles)) continue;
						
                        $options = $this->_loadOptions($configPath
                                 . DIRECTORY_SEPARATOR . $filename);

                    	if (($len = strpos($filename, '.')) !== false) {
                            $cfgtype = substr($filename, 0, $len);
                        } else {
                            $cfgtype = $filename;
                        }

                        if (strtolower($cfgtype) != 'module') {
                        	$appOptions[$module]['resources'][$cfgtype] = $options;
                        } else {
                        	if(!array_key_exists($module, $appOptions)) {
                        		$appOptions[$module] = array();
                        	}
                        	$appOptions[$module] = array_merge($appOptions[$module], $options);
                        }
                    }
                }
                $this->getBootstrap()->setOptions($appOptions);
                
			} else {
	        	continue;
	        }
			
		}
		
        $this->_config = $this->getBootstrap()->getOptions();
    }

    /**
     * Load the config file
     *
     * @param string $fullpath
     * @return array
     */
    protected function _loadOptions($fullpath) {
    	
        if (file_exists($fullpath)) {
            switch(substr(trim(strtolower($fullpath)), -3))
            {
                case 'ini':
                    $cfg = new Zend_Config_Ini($fullpath, $this->getBootstrap()
                                                    ->getEnvironment());
                    break;
                case 'xml':
                    $cfg = new Zend_Config_Xml($fullpath, $this->getBootstrap()
                                                    ->getEnvironment());
                    break;
                case 'yml':
                	if(file_exists(APPLICATION_PATH . '/../library/Symfony/lib/yaml/sfYaml.php')) {
	                	require_once APPLICATION_PATH . '/../library/Symfony/lib/yaml/sfYaml.php';
	                	$cfg = new Zend_Config_Yaml($fullpath, $this->getBootstrap()->getEnvironment(), 
	                		array('yaml_decoder' => array('sfYaml', 'load')));
                	}
                	break;
                default:
                    throw new Zend_Config_Exception('Invalid format for config file: ' . $fullpath);
                    break;
            }
        } else {
            throw new Zend_Application_Resource_Exception('File does not exist');
        }
        
        return $cfg->toArray();
        
    }
}