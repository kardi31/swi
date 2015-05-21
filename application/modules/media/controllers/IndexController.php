<?php

class Media_AdminController extends MF_Controller_Action
{
    public function cropPhotoAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        $photosFolder = $options['media']['photosFolder'];
        
        $name = $this->getRequest()->getParam('photo_file');
        $cat = $this->getRequest()->getParam('cat');
        $filename = $photosFolder . DIRECTORY_SEPARATOR . $name;
        $x = $this->getRequest()->getParam('x');
        $y = $this->getRequest()->getParam('y');
        $x2 = $this->getRequest()->getParam('x2');
        $y2 = $this->getRequest()->getParam('y2');
        $w = $this->getRequest()->getParam('w');
        $h = $this->getRequest()->getParam('h');

        $destDimensions = explode('x', $cat);
        
        if(file_exists($filename)) {
            Media_Model_ImageResizer::cropImage($filename, $photosFolder . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $name, $x, $y, $x2, $y2, $destDimensions[0], $destDimensions[1]);
        }
    }
    
    /**
     * elfinder connect action for review edition
     */
    public function connectAction() {
        $appOptions = $this->getInvokeArg('bootstrap')->getOptions();
        
        if(!$user = $this->getFrontController()->getPlugin('User_Plugin_Guard')->getAuthenticatedUser()) {
            throw new Zend_Controller_Action_Exception('User not logged in');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getContainer()->offsetGet('elfinder');
        $options['root'] = $appOptions['media']['usersFolder'] . DIRECTORY_SEPARATOR . $user->getId();
        $options['URL'] = $appOptions['media']['userElfinderBaseUrl'] . $user->getId() . '/';
        
        $elFinder = new elFinder($options);
        $elFinder->run();
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /**
     * elfinder client configuration
     */
    public function clientAction() {
        
    }    
    
}
