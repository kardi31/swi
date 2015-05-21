<?php

/**
 * User_AjaxController
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_AjaxController extends MF_Controller_Action 
{
    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->ajaxContext
                ->addActionContext('reload-captcha', 'html')
                ->initContext();
        parent::init();
    }
    
    public function reloadCaptchaAction() {
        $captchaDir = $this->getFrontController()->getParam('bootstrap')->getOption('captchaDir');
        
        $form = new User_Form_Register();
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars', 
            'captcha' => array(
            'captcha' => 'Image',  
            'wordLen' => 6,  
            'timeout' => 300,  
            'font' => APPLICATION_PATH . '/../data/arial.ttf',  
            'imgDir' => $captchaDir,  
            'imgUrl' => $this->view->serverUrl() . '/captcha/',  
        ))); 
        
        $captcha = $form->getElement('captcha')->getCaptcha();
        $captcha->generate();
        $this->_helper->json(array('id' => $captcha->getId(), 'src' => $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix()));
    }
}

