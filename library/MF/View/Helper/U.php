<?php

/**
 * MF_View_Helper_U
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_View_Helper_U extends Zend_View_Helper_Abstract 
{
    protected $_view;
    
    public function u($action, $controller = null, $module = null, $params = array(), $name = null, $reset = false, $encode = true) {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $urlOptions = array(
            'action' => $action,
            'controller' => $controller,
            'module' => $module
        );
        $urlOptions = array_merge($urlOptions, $params);
        return $this->_view->serverUrl() . $router->assemble($urlOptions, $name, $reset, $encode);
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }
}

