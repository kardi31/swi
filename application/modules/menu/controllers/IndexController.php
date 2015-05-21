<?php

/**
 * Menu_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_IndexController extends MF_Controller_Action
{
    public function mainMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        if(!$menu = $menuService->getMenu(1)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        $tree = $menuService->getMenuItemTree($menu, $this->view->language);
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree[0]->getNode()->getChildren());
        
        $this->_helper->viewRenderer->setResponseSegment('mainMenu');
    }
    
     public function dropdownAction() {
          $newsService = $this->_service->getService('News_Service_News');
        $newsCategoryService = $this->_service->getService('News_Service_Category');
        
        
        $newsCategories = $newsCategoryService->getAllCategories();
        
        $newsList = array();
        foreach($newsCategories as $category):
            $newsList[$category['title']] = $newsService->getLastCategoryNews($category['id'],2,Doctrine_Core::HYDRATE_ARRAY);
        endforeach;
        
        $this->view->assign('newsList', $newsList);
        $this->view->assign('categories', $newsCategories);
        
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $categoryService = $this->_service->getService('Gallery_Service_Category');
        
        $latestGalleries = $galleryService->getLatestGalleries(2,Doctrine_Core::HYDRATE_ARRAY);
        
        $categories = $categoryService->getAllCategories();
        
        $galleries = array();
        foreach($categories as $category):
            $galleries[$category['title']] = $galleryService->getCategoryGalleries($category['id'],2,Doctrine_Core::HYDRATE_ARRAY);
        endforeach;
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('galleries', $galleries);
    }
   
}

