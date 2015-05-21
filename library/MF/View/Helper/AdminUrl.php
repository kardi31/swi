<?php

class MF_View_Helper_AdminUrl extends Zend_View_Helper_Abstract
{
    public $view;
    
	public function adminUrl($action = 'index', $controller = 'index', $params = array()) {
        $params['controller'] = $controller;
        $params['action'] = $action;
        $params['lang'] = null;
        return $this->view->url($params, 'admin', true);
    }

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}

