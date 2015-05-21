<?php

/**
 * Menu_Model_MenuItemManager
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_Model_MenuItemManager 
{
    protected $_mm;
    protected $_lm;
    
    public function setMenuManager($menuManager) {
        $this->_mm = $menuManager;
    }
    
    public function setLanguageManager($languageManager) {
        $this->_lm = $languageManager;
    }
    
    public function getTree() {
        return Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->getTree();
    }
    
    public function findOneById($id) {
        return Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->find($id);
    }
    
    public function findOneByTargetId($targetId) {
        $q = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->createQuery('i')
                ->where('target_id = ?', $targetId)
                ;
        return $q->fetchOne();
    }
    
    public function findAllByTargetId($targetId) {
        $q = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->createQuery('i')
                ->where('target_id = ?', $targetId)
                ;
        $targetItems = $q->execute();
        
        $result = array();
        foreach($targetItems as $item) {
            $result[] = $item->getId();
        }
        return $result;
    }
    
    public function getTargetObject($targetId) {
        preg_match('/^(\w+)_(\d+)$/', $targetId, $m);
        if(isset($m[1])) {
            $class = $this->_resolveTargetObjectClass($m[1]);
            if(class_exists($class)) {
                if(isset($m[2])) {
                    return Doctrine_Core::getTable($class)->find($m[2]);
                }
            }
        }
        return null;
    }
  
    public function createEditForm($menuItem) {
        $form = new Menu_Form_MenuItem();
        $form->setDefault('id', $menuItem->getId());
        $parent = $menuItem->getNode()->getParent();
        $form->setDefault('parent_id', $parent->getId());
        $form->getElement('custom_url', $menuItem->getCustomUrl());
        
        foreach($this->_lm->findAll() as $language) {
            $languageForm = $form->translations->getSubForm($language->getId());
            if($languageForm) {
                $languageForm->setDefault('title', $menuItem->Translation[$language->getId()]->title);
                $languageForm->setDefault('titleAttr', $menuItem->Translation[$language->getId()]->title_attr);
            }
        }
        return $form;
    }
    
    public function saveFromForm(Zend_Form $form) {
        if($form->getValue('id')) {
            $menuItem = $this->findOneById($form->getValue('id'));
            $this->_doUpdate($menuItem, $form);
        } else {
            $this->_doCreate($form);
        }
    }
      
    public function moveItem(Menu_Model_Doctrine_MenuItem $item, $direction = 'down') {
        if($direction == 'up') {
            $prevSibling = $item->getNode()->getPrevSibling();
            if($prevSibling) {
                $item->getNode()->moveAsPrevSiblingOf($prevSibling);
            }
        } elseif($direction == 'down') {
            $nextSibling = $item->getNode()->getNextSibling();
            if($nextSibling) {
                $item->getNode()->moveAsNextSiblingOf($nextSibling);
            }
        }
    }
    
    public function remove(Menu_Model_Doctrine_MenuItem $menuItem) {
        $menuItem->Translation->delete();
        $menuItem->getNode()->delete();
    }
    
    public function getBreadcrumbs($activeItem) {
        $breadcrumbs = new Menu_Model_Breadcrumbs();
        if($activeItem instanceof Menu_Model_Doctrine_MenuItem) {
            $breadcrumbs = $this->_buildBreadcrumbs($activeItem, $breadcrumbs);
        }
        return $breadcrumbs;
    }
    
    protected function _buildBreadcrumbs($item, $breadcrumbs) {
        $breadcrumbs->prepend($item);
        if($item->getNode()->getParent() instanceof Menu_Model_Doctrine_MenuItem && !$item->getNode()->getParent()->getNode()->isRoot()) {
            $this->_buildBreadcrumbs($item->getNode()->getParent(), $breadcrumbs);
        }
        return $breadcrumbs;
    }
    
    protected function _resolveTargetObjectClass($token) {
        switch($token) {
            case 'site':
                return 'Site_Model_Doctrine_Site';
                break;
            case 'category':
                return 'Post_Model_Doctrine_Category';
                break;
        }
    }
    
    protected function _doUpdate($menuItem, $form) {
        $menuItem->setTargetId($form->getValue('target_id'));
        $menuItem->setCustomUrl($form->getValue('custom_url'));
        $languages = $this->_lm->findAll();
        foreach($languages as $language) {
            $languageForm = $form->translations->getSubForm($language->getId());
            if($languageForm) {
                $menuItem->Translation[$language->getId()]->title = $languageForm->getValue('title');
                $menuItem->Translation[$language->getId()]->title_attr = ($languageForm->getValue('titleAttr')) ? $languageForm->getValue('titleAttr') : $languageForm->getValue('title');
            }
        }
        $menuItem->save();
    }
    
    protected function _doCreate($form) {
        // nested set
        $menuItem = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem')->getRecord();
        
        $menuItem->setTargetId($form->getValue('target_id'));
        $menuItem->setCustomUrl($form->getValue('custom_url'));
        
        $menu = $this->_mm->findOneById($form->getValue('menu_id'));
        $menuItem->Menu = $menu;
        
        $languages = $this->_lm->findAll();
        foreach($languages as $language) {
            $languageForm = $form->translations->getSubForm($language->getId());
            if($languageForm) {
                $menuItem->Translation[$language->getId()]->title = $languageForm->getValue('title');
                $menuItem->Translation[$language->getId()]->title_attr = ($languageForm->getValue('titleAttr')) ? $languageForm->getValue('titleAttr') : $languageForm->getValue('title');
                
            }
        }
        $menuItem->save();
        
        $parent = $this->findOneById((int) $form->getValue('parent_id'));
        if($parent instanceof Menu_Model_Doctrine_MenuItem) {
            $menuItem->getNode()->insertAsLastChildOf($parent);
        } else {
            $menu = $this->_mm->findOneById((int) $form->getValue('menu_id'));
            if($menu instanceof Menu_Model_Doctrine_Menu) {
                $root = $menu->MenuRoot;
                $menuItem->getNode()->insertAsLastChildOf($root);
            }
        }

    }
    
}

