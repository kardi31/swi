<?php

/**
 * Menu
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_Service_Menu extends MF_Service_ServiceAbstract {
    
    protected $menuTable;
    protected $menuItemTable;

    
    public function init() {
        $this->menuTable = Doctrine_Core::getTable('Menu_Model_Doctrine_Menu');
        $this->menuItemTable = Doctrine_Core::getTable('Menu_Model_Doctrine_MenuItem');
    }
    
    public function getMenu($id, $field = 'id') {
        return $this->menuTable->findOneBy($field, $id);
    }
    
    public function getAllMenus() {
        return $this->menuTable->findAll();
    }
    public function getAvailableRoutes() {
        foreach($this->availablePages as $key=>$value):
            $pages[$value] = $key;
        endforeach;
        return $pages;
    }
    
    public function getMenuItem($id, $field = 'id') {
        return $this->menuItemTable->findOneBy($field, $id);
    }
    
    public function fetchMenu($menuId = 1, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->menuItemTable->getMenuMainItemsQuery($menuId);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function fetchSubMenu($od,$do,$limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->menuItemTable->getSubMenuItemsQuery($od,$do);
    
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        return $banners;
    }
    
    public function findMenuItemByUniqueId($uniqueId) {
        return $this->getMenuItem($uniqueId, 'unique_id');
    }
    
    public function findMenuItemByTargetType($targetType) {
        return $this->getMenuItem($targetType, 'target_type');
    }
    
    public function findMenuItemByTargetTypeAndTargetId($targetType, $targetId) {
        return $this->menuItemTable->findByDql('target_type = ? AND target_id = ?', array($targetType, $targetId))->getFirst();
    }
    
    public function getMenuItemTree($menu, $language, $deep = false, $hydrationMode = Doctrine_Core::HYDRATE_RECORD_HIERARCHY) {
        $root = $menu->get('MenuItemRoot');
        if(false == $deep) {
            return $this->menuItemTable->getTree()->fetchBranch($root->getId(), array(), $hydrationMode);
        } else {    
            $q = $this->menuItemTable->createQuery('i')
                ->select('i.*')
                ->addSelect('it.*')
                ->addSelect('ip.id')
                ->addSelect('ipt.*')
                ->leftJoin('i.Translation it')
                ->leftJoin("i.Page ip ON i.target_id = ip.id AND i.target_type = 'page'")
                ->leftJoin('ip.Translation ipt')
                ->where('i.root_id = ?', array($root->id))
                ->andWhere('it.lang = ?', array($language))
                ->andWhere('(ipt.lang = ? OR ipt.lang IS NULL)', array($language))
                ;
            $tree = $this->menuItemTable->getTree();           
            $tree->setBaseQuery($q);
            $menuTree = $tree->fetchTree(array('root_id' => $root->id), $hydrationMode);
            $tree->resetBaseQuery();
            return $menuTree;
        }
    }
    
    public function getMenuForm(Menu_Model_Doctrine_Menu $menu = null) {
        $form = new Menu_Form_Menu();
        if(null !== $menu) {
            $form->populate($menu->toArray());
        }
        return $form;
    }
    
    public function getMenuItemForm(Menu_Model_Doctrine_MenuItem $menuItem = null) {
        $form = new Menu_Form_MenuItem();
        $menuItemArray = $menuItem->toArray();
        $menuItemArray['target'] = $menuItemArray['route'];
        if(null !== $menuItem) {
            $form->populate($menuItemArray);
        }
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('title')->setValue($menuItem->Translation[$language]->title);
                $i18nSubform->getElement('title_attr')->setValue($menuItem->Translation[$language]->title_attr);
            }
        }
        return $form;
    }
    
    public function saveMenuFromArray($data) {
        foreach($data as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $data[$key] = NULL;
            }
        }
        if(!$menu = $this->menuTable->getProxy($data['id'])) {
            $menu = $this->menuTable->getRecord();
        }
        if(strlen($data['location'])) {
            $this->menuTable->resetLocation($data['location']);
        }
        $menu->refresh();
        $menu->fromArray($data);
        $menu->save();
        return $menu;
    }
    
    public function saveMenuItemFromArray($data) {
        foreach($data as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $data[$key] = NULL;
            }
        }
        if(!$menuItem = $this->menuItemTable->getProxy($data['id'])) {
            $menuItem = $this->menuItemTable->getRecord();
        }
        $data['route'] = $data['target'];
        $menuItem->fromArray($data);
        
        foreach($data['translations'] as $language => $translation) {
            $menuItem->Translation[$language]->title = $translation['title'];
            $menuItem->Translation[$language]->title_attr = $translation['title_attr'];
            $menuItem->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Menu_Model_Doctrine_MenuItemTranslation', $translation['title'], $menuItem->getId());
            
        }
      //  exit;
        if(preg_match('/^(\w+)_(\d+)$/', $data['target'], $match)) {
            if(isset($match[1]) && isset($match[2])) {
                $menuItem->setTargetType($match[1]);
                $menuItem->setTargetId($match[2]);
            }
        } elseif(preg_match('/^(homepage|contact|login|logout)$/', $data['target'], $match)) {
            if(isset($match[1])) {
                $menuItem->setTargetType($match[1]);
                $menuItem->setTargetId(NULL);
            }
        } else {
            $menuItem->setTargetType(null);
            $menuItem->setTargetId(null);
        }
        $menuItem->save();
        if(strlen($data['parent_id'])) {
            if($parent = $this->getMenuItem($data['parent_id']))
                $menuItem->getNode()->insertAsLastChildOf($parent);
        } else {
            if($menu = $this->getMenu($data['menu_id'])) {
                if($root = $this->getMenuItemRoot($menu))
                    $menuItem->getNode()->insertAsLastChildOf($root);
            }
        }
        
        return $menuItem;
    }

    public function createMenuItemRoot(Menu_Model_Doctrine_Menu $menu) {
        $menuItem = $this->menuItemTable->getRecord();
        $tree = $this->menuItemTable->getTree();
        $menuItem->save();
        $root = $tree->createRoot($menuItem);
        $menu->MenuItemRoot = $root;
        $menu->MenuItems[] = $root;
        $menu->save();
    }
    
    public function getMenuItemRoot($menu) {
        $root = $menu->get('MenuItemRoot');
        return (!$root->isInProxyState()) ? $root : null;
    }
    
    public function moveMenuItem($menuItem, $dest, $mode) {
        switch($mode) {
            case 'before':
                if($dest->getNode()->isRoot()) {
                    throw new Exception('Cannot move menu item on root level');
                }
                $menuItem->getNode()->moveAsPrevSiblingOf($dest);
                break;
            case 'after':
                if($dest->getNode()->isRoot()) {
                    throw new Exception('Cannot move men item on root level');
                }
                $menuItem->getNode()->moveAsNextSiblingOf($dest);
                break;
            case 'over':
                $menuItem->getNode()->moveAsLastChildOf($dest);
                break;
        }
    }
    
    public function removeMenuItem(Menu_Model_Doctrine_MenuItem $menuItem) {
        $menuItem['Translation']->delete();
        $menuItem->getNode()->delete();
    }
}

