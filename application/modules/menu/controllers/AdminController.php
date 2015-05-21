<?php

/**
 * Menu_AdminController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_AdminController extends MF_Controller_Action
{
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('remove-menu', 'json')
                ->addActionContext('move-menu-item', 'json')
                ->initContext();
        parent::init();
    }
    
    public function listMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
        $menus = $menuService->getAllMenus();

        $this->view->assign('menus', $menus);
    }
    
    public function addMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
        $form = $menuService->getMenuForm();
        $locations = array_merge(array('' => null), $this->view->cms()->getlayoutLocations());
        $form->getElement('location')->setMultiOptions($locations);

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $menu = $menuService->saveMenuFromArray($values);
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu', 'menu', array('id' => $menu->getId())));
                        
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu', 'menu'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $menus = $menuService->getAllMenus();
        
        $this->view->assign('menus', $menus);
        $this->view->assign('form', $form);
    }
    
    public function editMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
        if(!$menu  = $menuService->getMenu($this->getRequest()->getParam('id'))) {
            throw new Exception('Menu not found');
        }
        
        $form = $menuService->getMenuForm($menu);

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $menu = $menuService->saveMenuFromArray($values);
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu', 'menu', array('id' => $menu->getId())));
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu', 'menu'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $menus = $menuService->getAllMenus();
        
        $this->view->assign('menu', $menu);
        $this->view->assign('menus', $menus);
        $this->view->assign('form', $form);
    }
    
    public function deleteMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
        if(!$menu = $menuService->getMenu($this->getRequest()->getParam('id'))) {
            throw new Exception('Menu not found');
        }
        
        $menuService->remove($menu);
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu', 'menu'));
    }
    
    public function listMenuItemAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $i18nService = $this->_service->getService('Default_Service_I18n');

        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$menu = $menuService->getMenu((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        if($menu->get('MenuItemRoot')->isInProxyState()) {
            $menuService->createMenuItemRoot($menu);
        }
        
        if(!$menuItem = $menuService->getMenuItem((int) $this->getRequest()->getParam('item'))) {
            $menuItem = $menu->get('MenuItemRoot');
        }
        
        $form = $menuService->getMenuItemForm($menuItem);
        $form->setAction($this->view->adminUrl('add-menu-item', 'menu', array('menu' => $menu->getId(), 'item' => $menuItem->getId())));
        $form->getElement('parent_id')->setValue($menuItem->getId());
        $form->getElement('menu_id')->setValue($menu->getId());
        
        $tree = $menuService->getMenuItemTree($menu, $adminLanguage->getId());
        $root = $tree ? $tree->getFirst() : null;
        
        $this->view->admincontainer->findOneBy('id', 'listmenuitem')->setLabel($menu->getName());
        $this->view->assign('adminTitle', $menu->getName());
        
        $this->view->assign('root', $root);
        $this->view->assign('menu', $menu);
        $this->view->assign('menuItem', $menuItem);
        $this->view->assign('tree', $tree);
        $this->view->assign('form', $form);
    }
    
    public function listMenuItemDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Menu_DataTables_MenuItem', 
            'columns' => array('x.id','xt.title', 'm.name'),
            'searchFields' => array('x.id','xt.title', 'm.name')
        ));
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
            $row[] = $result->get('Menu')->name;
            $options = '<a href="' . $this->view->adminUrl('edit-menu-item', 'menu', array('item' => $result->id, 'id' => $result->get('Menu')->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('delete-menu-item', 'menu', array('id' => $result->id, 'menu' => $result->get('Menu')->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addMenuItemAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $pageService = $this->_service->getService('Page_Service_Page');
//        $newsService = $this->_service->getService('News_Service_News');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        if(!$menu = $menuService->getMenu($this->getRequest()->getParam('menu'))) {
            throw new Zend_Controller_Action_Exception('Menu not specified');
        }
        
        if(!$parent = $menuService->getMenuItem((int) $this->getRequest()->getParam('item'))) {
            throw new Zend_Controller_Action_Exception('Parent menu item not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        // setting target element select options
        $targetSelect = array(null => '');
        $targetSelect['homepage'] = $this->view->translate('Homepage');
        $targetSelect['contact'] = $this->view->translate('Contact');
        $targetSelect['login'] = $this->view->translate('Login');
        $targetSelect['logout'] = $this->view->translate('Logout');
        $targetSelect = array_merge(
                $targetSelect, 
                array($this->view->translate('Routes') => $menuService->getAvailableRoutes())
           //     array($this->view->translate('Pages') => Page_Model_Doctrine_Page::getAvailableTypes())
//                array_merge($targetSelect, array($this->view->translate('Categories') => $newsService->getTargetCategorySelectOptions($adminLanguage->getId())))
                );

        $form = new Menu_Form_MenuItem();
        $form->getElement('target')->setMultiOptions($targetSelect);
        $form->getElement('parent_id')->setValue($this->getRequest()->getParam('parent'));
        $form->getElement('menu_id')->setValue($menu->getId());

        $languages = $i18nService->getLanguageList();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                     
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    if($menu->get('MenuItemRoot')->isInProxyState()) {
                        $menuService->createMenuRootItem($menu);
                    }
                     $data = $form->getValues();
                    $menuItem = $menuService->saveMenuItemFromArray($data);

                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu-item', 'menu', array('id' => $menuItem->getId(), 'menu' => $menu->getId())));
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu-item', 'menu', array('id' => $menu->getId(), 'menu-item' => $parent->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('menu', $menu);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        
    }
    
    public function editMenuItemAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $availableRouteService = $this->_service->getService('Default_Service_AvailableRoute');
        $pageService = $this->_service->getService('Page_Service_Page');
        $i18nService = $this->_service->getService('Default_Service_I18n');

        $router = $this->_service->get('router');
    
        if(!$menu = $menuService->getMenu($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Menu not specified');
        }
 
        if(!$menuItem = $menuService->getMenuItem($this->getRequest()->getParam('item'))) {
            throw new Zend_Controller_Action_Exception('Menu item not found');
        }

        $adminLanguage = $i18nService->getAdminLanguage();
        
        $targetSelect = $availableRouteService->getAvailableRoutes();
        
        $form = $menuService->getMenuItemForm($menuItem);
        
        $form->getElement('target')->setMultiOptions($targetSelect);
        $form->getElement('menu_id')->setValue($menu->getId());
        
        $languages = $i18nService->getLanguageList();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    if($menu->get('MenuItemRoot')->isInProxyState()) {
                        $menuService->createMenuRootItem($menu);
                    }
                    $data = $form->getValues();
                    
                    $menuItem = $menuService->saveMenuItemFromArray($data);
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu-item', 'menu', array('id' => $menuItem->getId(), 'menu' => $menu->getId())));
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu-item', 'menu', array('id' => $menu->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $menu = $menuItem->get('Menu');
        
        $addForm = $menuService->getMenuItemForm($menuItem);
        $addForm->setAction($this->view->adminUrl('add-menu-item', 'menu', array('menu' => $menu->getId(), 'item' => $menuItem->getId())));
        $addForm->getElement('parent_id')->setValue($menuItem->getId());
        $addForm->getElement('menu_id')->setValue($menu->getId());
        
        
        $this->view->admincontainer->findOneBy('id', 'listmenuitem')->setLabel($menu->getName())
                ->setParams(array('id' => $menu->getId()));
        $this->view->admincontainer->findOneBy('id', 'editmenuitem')->setLabel($menuItem->Translation[$adminLanguage->getId()]->title);
        $this->view->assign('adminTitle', $menu->getName());
        
        $tree = $menuService->getMenuItemTree($menu, $adminLanguage->getId());
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree);
        $this->view->assign('item', $menuItem);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('addForm', $addForm);
    }
    
    public function addMenuItemPhotoAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$item = $menuService->getMenuItem((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Menu item not found');
        }
  
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $href = $this->getRequest()->getParam('hrefs');

        if(is_string($href) && strlen($href)) {
            $path = str_replace("http://" . $options['domain'], "", urldecode($href));
            $filePath = urldecode($options['publicDir'] . $path);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $slug = MF_Text::createSlug($pathinfo['basename']);
                $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $root = $item->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Menu_Model_Doctrine_MenuItem::getMenuPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Menu_Model_Doctrine_MenuItem::getMenuPhotoDimensions()), false);                    
                    }

                    $item->set('PhotoRoot', $photo);
                    $item->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $itemPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $item->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $itemPhotos->add($root);
            $list = $this->view->partial('admin/menu-main-photo.phtml', 'menu', array('photos' => $itemPhotos, 'item' => $item));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $item->getId()
        )); 
    }
    
    public function editMenuItemPhotoAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$item = $menuService->getMenuItem((int) $this->getRequest()->getParam('menu-id'))) {
            throw new Zend_Controller_Action_Exception('Menu item not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-menu-item-photo', 'menu', array('menu-id' => $item->getId(), 'id' => $photo->getId())));
        
        $photosDir = $photoService->photosDir;
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $photo->getOffset());
        if(strlen($photo->getFilename()) > 0 && file_exists($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename())) {
            list($width, $height) = getimagesize($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename());
            $this->view->assign('imgDimensions', array('width' => $width, 'height' => $height));
        }
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $photo = $photoService->saveFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu-photo', 'menu', array('id' => $item->getId(), 'menu-id' => $item->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-menu-item', 'menu', array('item' => $item->getId(), 'id' => $item->get('Menu')->id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
//        $this->view->admincontainer->findOneBy('id', 'editmenuitemphoto')->setActive();
//        $this->view->admincontainer->findOneBy('id', 'editmenuitemphoto')->setLabel($item->Translation[$adminLanguage->getId()]->title);
//        $this->view->admincontainer->findOneBy('id', 'editmenuitemphoto')->setParam('id', $item->getId());
//        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropmenuitemphoto')->getLabel());  
        $this->view->assign('item', $item);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Menu_Model_Doctrine_MenuItem::getMenuPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeMenuItemPhotoAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$item = $menuService->getMenuItem((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Menu item not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $item->get('PhotoRoot')) {
                if($root && !$root->isInProxyState()) {
                    $photo = $photoService->updatePhoto($root);
                    $photo->setOffset(null);
                    $photo->setFilename(null);
                    $photo->setTitle(null);
                    $photo->save();
                }
            }
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $list = '';
        
        $itemPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $item->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $itemPhotos->add($root);
            $list = $this->view->partial('admin/menu-main-photo.phtml', 'menu', array('photos' => $itemPhotos, 'item' => $item));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $item->getId()
        ));
        
    }
    
    public function deleteMenuItemAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');

        if(!$menu = $menuService->getMenu($this->getRequest()->getParam('menu'))) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        if(!$menuItem = $menuService->getMenuItem($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Menu item not found');
        }
        
        $menuService->removeMenuItem($menuItem);
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu-item', 'menu', array('id' => $menu->getId())));
    }
    
    // ajax actions
    
    public function moveMenuItemAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
     
        $this->view->clearVars();
        
        $status = 'success';
        
        $menuItem = $menuService->getMenuItem((int) $this->getRequest()->getParam('id'));
        
        $dest = $menuService->getMenuItem((int) $this->getRequest()->getParam('dest_id'));
  
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $menuService->moveMenuItem($menuItem, $dest, $this->getRequest()->getParam('mode', 'after'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->view->assign('status', $status);
    }
    
    public function removeMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
     
        $this->view->clearVars();
        
        $status = 'success';
        
        if($item = $menuService->getMenuItem((int) $this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $parent = $item->getNode()->getParent();
                
                $menuService->removeMenuItem($item);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                if(!$this->getRequest()->isXmlHttpRequest()) {
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-menu', 'menu', array('id' => $parent->getId())));
                }
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->_service->get('log')->log($e->getMessage());
                $status = 'error';
            }
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    // ajax actions
    
    
}

