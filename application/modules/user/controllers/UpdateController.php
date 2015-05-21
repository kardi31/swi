<?php

/**
 * User_UpdateController
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_UpdateController extends MF_Controller_Action {
    
    public function init() {
        if (APPLICATION_ENV == 'ajurwedaDevelopment' || APPLICATION_ENV == 'ajurwedaProduction'):
              $this->_helper->actionStack('layout-ajurweda', 'index', 'default');
        elseif(APPLICATION_ENV == 'shopDevelopment' || APPLICATION_ENV == 'shopProduction'):
            $this->_helper->actionStack('layout-shop', 'index', 'default');
        endif;
        parent::init();
    }
    
    public function indexAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setSubject($this->view->translate('REGISTER_PASSWORD_RECOVERY_MESSAGE_SUBJECT'));
        
        // what going to change
        $subject = $this->getRequest()->getParam('subject');
        
        $form = new User_Form_Update();
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->removeElement('password');
        $form->removeElement('confirm_password');
        $form->removeElement('token');
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        $this->view->messages()->clean();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    // prepare password change and send confirmation email
                    $data = $form->getValues();
                    if(!$user = $userService->getUser($data['email'], 'email')) {
                        throw new Zend_Controller_Action_Exception('User not found');
                    }
                    $update = $userService->prepareUpdate($user, $subject);
                    $userService->sendUpdateMail(User_Model_Doctrine_Update::TYPE_PASSWORD, $user, $update->getToken(), $mail, $this->view, 'email/update.phtml');

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->view->messages()->add('Check email');
                    
                    $this->_helper->redirector->gotoRoute(array(), 'domain-user-update-complete');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->view->messages()->add($e->getMessage(), 'error');
                }
            }
        }
        
        $this->view->assign('form', $form);
        
    }
    
    public function updateClientPasswordAdminAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        if(!$user = $userService->getFullUser($this->getRequest()->getParam('id'), 'id')) {
            throw new Zend_Controller_Action_Exception('User not found');
        }
        
        $translator = $this->_service->get('translate');
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setSubject($this->view->translate('Change password'));
        
        // what going to change
        $subject = $this->getRequest()->getParam('subject');
        
        $form = new User_Form_Update();
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->removeElement('password');
        $form->removeElement('confirm_password');
        $form->removeElement('token');
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        $this->view->messages()->clean();
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            // prepare password change and send confirmation email

            $update = $userService->prepareUpdate($user, $subject);
            $userService->sendUpdateMail(User_Model_Doctrine_Update::TYPE_PASSWORD, $user, $update->getToken(), $mail, $this->view, 'email/update.phtml');

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
            $this->view->messages()->add($translator->translate('Email with link to change password has been sent'));
                
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-client', 'user', array('id' => $user->getId())));
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->view->messages()->add($e->getMessage(), 'error');
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-client', 'user', array('id' => $user->getId())));
        
        $this->_helper->viewRenderer->setNoRender();  
    }
    
    public function updateAdminPasswordAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        if(!$user = $userService->getFullUser($this->getRequest()->getParam('id'), 'id')) {
            throw new Zend_Controller_Action_Exception('User not found');
        }
        
        $translator = $this->_service->get('translate');
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setSubject($this->view->translate('Change password'));
        $mail->addTo($user->getEmail());
        
        // what going to change
        $subject = $this->getRequest()->getParam('subject');
        
        $form = new User_Form_Update();
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->removeElement('password');
        $form->removeElement('confirm_password');
        $form->removeElement('token');
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        $this->view->messages()->clean();
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            // prepare password change and send confirmation email

            $update = $userService->prepareUpdate($user, $subject);
                     
            $userService->sendAdminChangePasswordMail($user, $mail, $this->view);

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
            $this->view->messages()->add($translator->translate('Email with link to change password has been sent'));
                
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-admin', 'user', array('id' => $user->getId())));
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->view->messages()->add($e->getMessage(), 'error');
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-admin', 'user', array('id' => $user->getId())));
        
        $this->_helper->viewRenderer->setNoRender();  
    }
    
    public function completeAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        $token = $this->getRequest()->getParam('token');
        
        if($token && $update = $userService->getUpdateOfToken($token)) {
            $form = $userService->getUpdateForm($update, $update->getType());
            $form->getElement('password')->setDescription('Enter new password');
            $form->getElement('confirm_password')->setDescription('Re-type new password');
            $session = new Zend_Session_Namespace('RECOVER_CSRF');
            $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        }
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $values = $form->getValues();
                
                $update = $userService->getUpdateOfToken($token);
                $user = $userService->getUser($update->getUserId());
        
                $passwordEncoder = new User_PasswordEncoder();
                $password = $passwordEncoder->encode($values['password'], $user->getSalt());

                $update->setValue($password);
                $userService->completeUpdate($update, $user);
                
                $this->_helper->redirector->gotoRoute(array('lang' => null), 'domain-login');
            }
        }
           
        $this->view->assign('form', $form);
        
    }
}

