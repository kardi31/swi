<?php

/**
 * Default_Plugin_PostSiteRouter
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Plugin_PostSiteRouter extends Zend_Controller_Plugin_Abstract
{
    protected $_container;
    protected $_router;
    protected $_mim;
    
    public function __construct($container, $router) {
        $this->_container = $container;
        $this->_router = $router;
    }
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if($this->_router->getCurrentRouteName() != 'i18n:site') {
            return;
        }

        $menuItemManager = new Menu_Model_MenuItemManager();
        
        $language = $request->getParam('lang');
        $site = $request->getParam('site');
        
        $menuItem = $menuItemManager->findOneBySlugAndLang($site, $language);
        if(!$menuItem) {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }
        
        switch($menuItem->id) {
            case 'aktualnosci':
                $request->setModuleName('news')->setControllerName('index')->setActionName('index');
                break;
            case 'kartoteka':
                $request->setModuleName('file')->setControllerName('index')->setActionName('index');
                break;
            case 'czytelnia':
                $request->setModuleName('default')->setControllerName('index')->setActionName('reading-room');
                break;
            case 'blog':
                $request->setModuleName('blog')->setControllerName('index')->setActionName('index');
                break;
            default:
                $request->setModuleName('site')->setControllerName('index')->setActionName('index');

        }
    }

    
}

