<?php

/**
 * Newsletter_IndexController
 *
 * @author Mateusz Aniołek
 */
class Newsletter_IndexController extends MF_Controller_Action {
    
   public function registerAction(){
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        
        $translator = $this->_service->get('Zend_Translate');
        
        $form = $subscriberService->getRegisterForm();
        
        $form->removeElement('first_name');
        $form->removeElement('last_name');
        
        $options = $this->getFrontController()->getParam('bootstrap')->getOptions();
        $captchaDir = $this->getFrontController()->getParam('bootstrap')->getOption('captchaDir');
        
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
            )
        )); 
        
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
      
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) { 
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    if($subscriberService->subscriberExists(array('email' => $form->getValue('email')))) {
                        throw new User_Model_UserWithEmailAlreadyExistsException('Subscriber with this email already exists');
                    } else {

                        $values = $form->getValues();
                        
                        $values['token'] = MF_Text::createUniqueToken($values['salt'].$values['email']);
                        
                        $subscriber = $subscriberService->saveSubscriberFromArray($values);
                        
                        $mail = new Zend_Mail('UTF-8');
                        $mail->setSubject($translator->translate('Newsletter - calvarianum.pl'));
                        $mail->addTo($subscriber->getEmail(), $subscriber->getFirstName() . ' ' . $subscriber->getLastName());
                        $mail->setReplyTo($options['reply_email'], 'System newslettera - calvarianum.pl');
                        
                        $subscriberService->sendRegistrationMail($mail, $this->view);

                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoRoute(array(), 'domain-i18n:newsletter-register-complete');
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage()));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                    var_dump($e->getMessage());exit;
                } 
            }
        }
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
   } 
   
   public function registerCompleteAction() {
         $this->_helper->actionStack('layout', 'index', 'default');
   }
    
   public function signOutAction() {
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');

        $translator = $this->_service->get('Zend_Translate');
        
        $this->view->messages()->clean();

        if($this->getRequest()->getParam('token') && $subscriber = $subscriberService->getSubscriber($this->getRequest()->getParam('token'), 'token')) {
            $subscriberService->removeSubscriber($subscriber);
            
            $this->view->messages()->add($this->view->translate($this->view->translate('You have been unsubscribed from our newsletter.')), 'success');
        }
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    
}

