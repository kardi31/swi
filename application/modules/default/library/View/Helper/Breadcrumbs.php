<?php

class Default_View_Helper_Breadcrumbs extends Zend_View_Helper_Abstract
{
    protected $_language;
    protected $_breadcrumbs;
    protected $_activeId;
    protected $_separator = ' - ';
    
    public function breadcrumbs() {
        if(!isset($this->view->breadcrumbs)) {
            $this->_language = $this->view->language;
            $this->view->breadcrumbs = $this;
        }
        return $this->view->breadcrumbs;
    }
    
    public function render($withHome = true) {
        if(null !== $this->_breadcrumbs) {
            return $this->view->partial('_breadcrumbs.phtml', array('breadcrumbs' => $this->_breadcrumbs, 'language' => $this->_language, 'withHome' => $withHome, 'separator' => $this->_separator));
        }
    }
    
    public function set($breadcrumbs) {
        $this->_breadcrumbs = $breadcrumbs;
    }
    
    public function getBreadcrumbs() {
        return $this->_breadcrumbs;
    }
    
    public function setActiveId($activeId) {
        $this->_activeId = $activeId;
    }
    
    public function setSeparator($separator) {
        $this->_separator = $separator;
    }
    
    public function getSeparator() {
        return $this->_separator;
    }
    
}
