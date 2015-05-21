<?php

class Default_View_Helper_CmsMenu extends Zend_View_Helper_Abstract
{
    public $view;
    protected $tree;
    protected $active;
    protected $activeIds;
    
    public function cms_menu($tree) {
        $this->tree = $tree;
        return $this;
    }
    
    public function renderMenu() {
        return $this->view->partial('_tree.phtml', 'menu', array('menuItems' => $this->tree->toArray(), 'language' => $this->view->language));
    }

    public function editElement($id, $data) {
        foreach($this->tree as $node) {
            if($node['id'] == $id) {
                $node->fromArray($data);
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
    
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}