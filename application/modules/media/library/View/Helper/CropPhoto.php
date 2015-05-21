<?php

/**
 * CropPhoto
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_View_Helper_CropPhoto extends Zend_View_Helper_Abstract {
    
    protected $_view;
    protected $_photo;
    protected $_imgWidth;
    protected $_imgHeight;
    protected $_config;
    
    public function cropPhoto($photo, $dimensions, $config, $watermark = false) {
        $this->_photo = $photo;
        if(!$dimensions) {
            return false;
        }
        $this->_imgWidth = isset($dimensions['width']) ? (int) $dimensions['width'] : null;
        $this->_imgHeight = isset($dimensions['height']) ? (int) $dimensions['height'] : null;
        $this->_config = $config;
        
        return $this->_view->partial('crop_photo.phtml', 'media', array('photo' => $this->_photo, 'width' => $this->_imgWidth, 'height' => $this->_imgHeight, 'config' => $this->_config, 'watermark' => $watermark));
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }
    
}

