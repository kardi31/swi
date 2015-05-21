<?php

class Menu_Model_Menu
{
    public $view;
    protected $tree;
    protected $active;
    protected $activeIds;
    
    public function __construct($tree) {
//        if(!isset($this->_view->menu)) {
//            $this->activeIds = array();
//            $this->tree = $tree;
//            $this->_view->menu = $this;
//        }
        $this->tree = $tree;
        return $this;
    }
    
    public function renderMenu() {
        return $this->view->partial('_tree.phtml', 'menu', array('menuItems' => $this->tree->toArray(), 'language' => $this->view->language));
    }

    public function editElement($id, $data) {
        foreach($this->tree as $node) {
            if($node['id'] == $id) {
//                $node->fromArray($data);
                foreach($data as $key => $value) {
                    $node->mapValue($key, $value);
                }
            }
        }
    }
    
    public function removeElement($id) {
        foreach($this->tree as $key => $node) {
            if($node['id'] == $id) {
                $this->tree->remove($key);
            }
        }
    }
    
//    public function setActive($active) {
//        $this->_active = $active;
//    }
//    
//    public function setActiveIds($ids) {
//        $this->_activeIds = $ids;
//    }
    
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}