<?php

/**
 * Admin_View_Helper_AdminUrl
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_View_Helper_AdminUrl extends Zend_View_Helper_Abstract
{
    public function adminUrl($action = 'index', $controller = 'index', $params = array()) {
        $params['controller'] = $controller;
        $params['action'] = $action;
        $params['lang'] = null;
        return $this->view->url($params, 'admin', true);
    }
}

