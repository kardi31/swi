<?php

/**
 * MF_Controller_Action_Helper_Container
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Controller_Action_Helper_Container extends Zend_Controller_Action_Helper_Abstract
{
    protected $_container;
    
    public function init() {
        $this->_container = $this->getFrontController()->getParam('bootstrap')->getContainer();
    }
    
    public function direct($resource = null) {
        if(null !== $resource) {
            return $this->get($resource);
        } else {
            return $this->_container;
        }
    }
    
    public function get($resource) {
        if(isset($this->_container->{$resource})) {
            return $this->_container->{$resource};
        }
    }
}

