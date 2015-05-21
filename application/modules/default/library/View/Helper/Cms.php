<?php

/**
 * Defaultview_Helper_Cms
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_View_Helper_Cms extends Zend_View_Helper_Abstract
{
    public $view;
    protected $serviceBroker;
    protected $container = array();
    
    public function cms() {
        if(!isset($this->view->cms)) {
            $this->init();
            $this->view->cms = $this;
        }
        
        return $this->view->cms;
    }
    
    public function init() {
        $this->serviceBroker = MF_Service_ServiceBroker::getInstance();
    }
    
    public function getLayoutLocations() {
        return is_array($this->view->layout_locations) ? $this->view->layout_locations : array();
    }

    public function get($location, $type) {
        if(array_key_exists($location . $type, $this->container)) {
            return $this->container[$location . $type];
        }
        
        if(!array_key_exists($location, $this->getLayoutLocations())) {
            return;
        }
        
        switch($type) {
            case 'menu':
                $menuService = $this->serviceBroker->getService('Menu_Service_Menu');
                if($menu = $menuService->getMenu($location, 'location')) {
                    $tree = $menuService->getMenuItemTree($menu, $this->view->language, true, Doctrine_Core::HYDRATE_RECORD_HIERARCHY);
                    $menu = new Menu_Model_Menu($tree);
                    $menu->setView($this->view);
                    $this->container[$location . $type] = $menu;
//                    $this->container[$location . $type] = $this->view->cms_menu($tree);
                    return $this->container[$location . $type];
                } else {
                    return $this;
                }
                break;
//            case 'blog':
//                $blogService = $this->serviceBroker->getService('Blog_Service_Blog');
//                $this->container($location . $type) = $this->view->blogList(); 
//                break;
                
        }
    }
    
    public function setting($id, $type = 'text') {
        $settingService = $this->serviceBroker->getService('Default_Service_Setting');
        if($setting = $settingService->getSetting($id)) {
            return $setting->value;
        }
    }
    
    public function headTitle() {
        return $this->view->headTitle();
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
    
    public function __call($method, $arguments) {
        
    }
    
}

