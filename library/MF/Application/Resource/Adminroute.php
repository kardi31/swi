<?php 

class MF_Application_Resource_Adminroute extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $front = $this->getBootstrap()->bootstrap('frontController')->getResource('frontController');
        $this->getBootstrap()->bootstrap('router');
        $router = $front->getRouter();
        
        $adminSegment = 'admin';
        $router->addRoute('admin', 
            new Zend_Controller_Router_Route('/' . $adminSegment . '/:controller/:action/*', array(
                'module' => 'admin',
                'controller' => 'index',
                'action' => 'index'
            ))
        );
    }
    
}
