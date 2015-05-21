<?php

/**
 * Slider
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Slider_View_Helper_Slider extends Zend_View_Helper_Abstract {
    
    public $view;
    protected $sliders;
    
    public function slider($id = null) {
        if(null != $id) {
            if(isset($this->sliders[$id])) {
                return $this->render($this->sliders[$id]);
            }
        } else {
            if(!isset($this->view->slider)) {
                $this->view->slider = $this;
            }
            return $this->view->slider;
        }
    }
    
    public function setSliders($sliders) {
        $this->sliders = $sliders;
    }

    public function render($id, $partial = 'slider.phtml') {
        if(isset($this->sliders[$id])) {
            return $this->view->partial($partial, 'slider', array('slider' => $this->sliders[$id]));
        }
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}

