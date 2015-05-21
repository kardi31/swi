<?php

class Menu_Model_Breadcrumbs extends ArrayIterator
{
    protected $_mm;
    protected $_breadcrumbs;
    private $_position;
    
    public function __construct() {
        $this->_breadcrumbs = array();
        $this->rewind();
    }
    
    public function offsetSet($index, $value) {
        $this->_breadcrumbs[$index] = $value;
    }
    
    public function offsetGet($index) {
        return $this->_breadcrumbs[$index];
    }
    
    public function offsetExists($index) {
        return array_key_exists($index, $this->_breadcrumbs);
    }
    
    public function offsetUnset($index) {
        unset($this->_breadcrumbs[$index]);
    }
    
    public function current() {
        return $this->_breadcrumbs[$this->_position];
    }
    
    public function key() {
        return $this->_position;
    }
    
    public function next() {
        $this->_position++;
    }
    
    public function rewind() {
        $this->_position = 0;
    }
    
    public function count() {
        return count($this->_breadcrumbs);
    }
    
    public function valid() {
        return array_key_exists($this->_position, $this->_breadcrumbs);
    }   

    public function setMenuManager($menuManager) {
        $this->_mm = $menuManager;
    }
    
    public function prepend($menuItem) {
        array_unshift($this->_breadcrumbs, $menuItem);
    }
    
    public function append($menuItem) {
        array_push($this->_breadcrumbs, $menuItem);
    }
    
    
}
