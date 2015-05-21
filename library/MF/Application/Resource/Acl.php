<?php 

class MF_Application_Resource_Acl extends Zend_Application_Resource_ResourceAbstract
{
	protected $_options = array();
	protected $_acl;
	
    public function init()
    {
    	$this->_options = $this->getOptions();

    	$container = ($this->getBootstrap() instanceof Zend_Application_Module_Bootstrap) ? $this->getBootstrap()->getApplication()->getContainer() : $this->getBootstrap()->getContainer();
    	
    	if(isset($container->acl)) {
    		$this->_acl = $container->get('acl');
    	} else {
	   		$this->_acl = new Zend_Acl();
    	}
	
    	$this->_setRoles();
        $this->_setResources();
        $this->_setDeny();
        $this->_setAllow();

        $container->set('acl', $this->_acl);
    	return $this->_acl;
    }
    
    protected function _setRoles()
    {
    	if(array_key_exists('roles', $this->_options) && is_array($this->_options['roles'])) {
	    	$roles = $this->_options['roles'];
	    	foreach($roles as $role => $options) {
	    		if($this->_acl->hasRole($role))
	    			continue;
	    		
	    		$parents = null;
	    		if(is_array($options)) {
	    			if(isset($options['parents'])) {
		            	$parents = $options['parents'];
		    		}
	                elseif(isset($options['parent'])) {
	                    $parents = $options['parent'];
	                }
	                else {
	                    throw new Exception('Zend_Acl must have some roles');
	                    return;
	                }
	    		}
	    		           
	            $this->_acl->addRole(new Zend_Acl_Role($role), $parents);  
	    	}
    	}
    }
    
    protected function _setResources()
    {
    	if(array_key_exists('resources', $this->_options) && is_array($this->_options['resources'])) {
	    	$resources = $this->_options['resources'];
	    	foreach($resources as $resource => $options) {
	    		if(in_array($resource, $this->_acl->getResources()))
	    			continue;
	    			
	    		$parent = null;
	    		if(isset($options['parent'])) {
	            	$parent = $options['parent'];
	            }
	            
	            $this->_acl->addResource(new Zend_Acl_Resource($resource), $parent);  
	    	}
    	}
    }
    
    protected function _setDeny()
    {
    	if(array_key_exists('deny', $this->_options) && is_array($this->_options['deny'])) {
            $denied = $this->_options['deny'];
            foreach($denied as $resource => $action) {
                if($resource == 'all') $resource = null;
                foreach($action as $privilege => $role) {
                    if($privilege == 'all') $privilege = null;
                    if($role == 'all') $role = null;
                    $this->_acl->deny($role, $resource, $privilege);
                }
            }
    	}
    }

    protected function _setAllow()
    {
    	if(array_key_exists('allow', $this->_options) && is_array($this->_options['allow'])) {
            $allowed = $this->_options['allow'];
            foreach($allowed as $resource => $action) {
                if($resource == 'all') $resource = null;
                foreach($action as $privilege => $role) {
                    if($privilege == 'all') $privilege = null;
                    if($role == 'all') $role = null;
                    $this->_acl->allow($role, $resource, $privilege);
                }
            }
    	}
    	
		  	
    }
    
}
