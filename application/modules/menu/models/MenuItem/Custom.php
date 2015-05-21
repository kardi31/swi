<?php

class Menu_Model_MenuItem_Custom
{
    protected $_targetHref;
    public $Translation;
    
    public function __construct() {
        $this->Translation = new ArrayObject();
    }
    
    public function getId() {
        return null;
    }
    
    public function setTargetHref($href) {
        $this->_targetHref = $href;
    }
    
    public function getTargetHref($language = null) {
        return $this->_targetHref;
    }
    
}
