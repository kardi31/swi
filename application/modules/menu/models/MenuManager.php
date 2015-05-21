<?php

/**
 * Menu_Model_MenuManager
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_Model_MenuManager 
{
    protected $_mim;
    protected $_sm;
    
    public function setMenuItemManager($menuItemManager) {
        $this->_mim = $menuItemManager;
    }
    
    public function setSiteManager($siteManager) {
        $this->_sm = $siteManager;
    }
    
    public function findOneById($id) {
        return Doctrine_Core::getTable('Menu_Model_Doctrine_Menu')->find($id);
    }
    
    public function findOneByLocation($location) {
        $q = Doctrine_Core::getTable('Menu_Model_Doctrine_Menu')->createQuery('m')
                ->where('location = ?', $location)
                ;
        return $q->fetchOne();
    }
    
    public function findAll() {
        return Doctrine_Core::getTable('Menu_Model_Doctrine_Menu')->findAll();
    }
    
    public function createEditForm($menu) {
        $form = new Menu_Form_Menu();
        $form->setDefault('id', $menu->getId());
        $form->setDefault('name', $menu->getName());
        $form->setDefault('location', $menu->getLocation());
        return $form;
    }
    
    public function saveFromForm(Zend_Form $form) {
        if($form->getValue('id')) {
            $menu = $this->findOneById($form->getValue('id'));
            $this->_doUpdate($menu, $form->getValues());
        } else {
            $this->_doCreate($form->getValues());
        }
    }
    
    public function remove($menu) {
        $menu->delete();
    }
    
    protected function _doUpdate($menu, $data) {
        $menu->setName($data['name']);
        $locations = $this->_sm->getLocations();
        $menu->setLocation($locations[$data['location']]);
        $menu->save();
    }
    
    protected function _doCreate($data) {
        $root = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->getRecord();
        $root->save();

        $tree = $this->_mim->getTree();
        $root = $tree->createRoot($root);
        
        $menu = new Menu_Model_Doctrine_Menu();
        $menu->setName($data['name']);
        $locations = $this->_sm->getLocations();
        $menu->setLocation($locations[$data['location']]);
        $menu->MenuRoot = $root;
        $menu->save();
        
    }
    
}

