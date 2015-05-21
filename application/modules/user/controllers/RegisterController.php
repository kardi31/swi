<?php

class User_RegisterController extends MF_Controller_Action
{
    public static $profileTypeOptions = array(
        'c' => 'Client',
        'a' => 'Agent'
    );

    public static $changeableUserData = array(
        'password'
    );
    
    public function init() {
    //    $this->_helper->actionStack('layout', 'index', 'default');
        parent::init();
    }
    
    public function indexAction() {
        $userService = $this->_service->getService('User_Service_User');
        $locationService = $this->_service->getService('Default_Service_Location');

        $translator = $this->_service->get('Zend_Translate');
        
        $options = $this->getFrontController()->getParam('bootstrap')->getOptions();
        
        $captchaDir = $this->getFrontController()->getParam('bootstrap')->getOption('captchaDir');
        
        $form = new User_Form_Register();
//        $form->setAction($this->_helper->url->url(array(), 'domain-register'));
        $form->setMethod(Zend_Form::METHOD_POST);
//        $form->getElement('first_name')->setAttrib('class', 'span10');
//        $form->getElement('email')->setAttrib('class', 'span10');
//        $form->getElement('password')->setAttrib('class', 'span10');
//        $form->getElement('confirm_password')->setAttrib('class', 'span10');
//        $form->removeElement('type');
//        $form->removeElement('last_name');
//        $form->addElement('hidden', 'type', array(
//            'value' => $this->getRequest()->getParam('account'),
//            'decorators' => array('ViewHelper')
//        ));
//        $form->getElement('submit')->setLabel('Save');
        $form->removeElement('type');
  
        $clientForm = new User_Form_Client();
        $clientForm->getElement('province_id')->setMultiOptions($locationService->getProvinceSelectOptions());
//        $clientForm->getElement('address')->setAttribs(array('title' => $translator->translate('e.g.')." Krakowska 1"));
        $clientForm->getElement('phone')->setAttribs(array('title' => $translator->translate('e.g.')." +48001002003, 001002003"));
//        $profileForm->getElement('city_id')->setMultiOptions($offerService->getCitySelectOptions());
//        $profileForm->getElement('province_id')->setAttrib('class', 'span10');
//        $profileForm->getElement('city_id')->setAttrib('class', 'span10 combobox');
//        $profileForm->getElement('company_name')->setAttrib('class', 'span10');
//        $profileForm->getElement('address')->setAttrib('class', 'span10');
//        $profileForm->getElement('nip')->setAttrib('class', 'span10');
//        $profileForm->getElement('website')->setAttrib('class', 'span10');
//        $profileForm->getElement('proxy_name')->setAttrib('class', 'span10');
//        $profileForm->getElement('tags')->setAttrib('class', 'span10');
//        $profileForm->getElement('about')->setAttribs(array('class' => 'span10', 'rows' => 6));
//        switch($this->getRequest()->getParam('account')) {
//            case 'agent':
//                break;
//            case 'client':
//                $profileForm->removeElement('company_name');
//                $profileForm->removeElement('address');
//                $profileForm->removeElement('nip');
//                $profileForm->removeElement('website');
//                $profileForm->removeElement('proxy_name');
//                $profileForm->removeElement('tags');
//                $profileForm->removeElement('about');
//                break;
//        }
        $clientForm->removeElement('first_name');
        $clientForm->removeElement('last_name');
        $clientForm->removeElement('province_id');
        $clientForm->removeElement('captcha');
        $form->addSubForm($clientForm, 'client');
//        
//        $form->removeElement('username');
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
              $form->removeElement('captcha');
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null))) {
                        throw new User_Model_UserWithEmailAlreadyExistsException('User already exists');
                    } else {
                        // saving user
                        $values = $form->getValues();

                        $passwordEncoder = new User_PasswordEncoder();
                        $values['salt'] = MF_Text::createUniqueToken();
                        $values['token'] = MF_Text::createUniqueToken();
                        $values['role'] = 'client';

                        $values['password'] = $passwordEncoder->encode($values['password'], $values['salt']);
                        $user = $userService->saveClientFromArray($values);

                       // $profile = $userService->createProfile($user, $values['profile']);
                        
//                        $mail = new Zend_Mail('UTF-8');
//                        $mail->setSubject($translator->translate('Założenie konta użytkownika w portalu a-ajurweda.pl'));
//                        $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
//                        $mail->setReplyTo($options['reply_email'], 'System Logowania Ofinanse.pl');
                        // !!!
                        
//                        od System Logowania Ofinanse.pl
//                        do danego usera : imię / adres email
//                        temat: założenie konta użytkownika w portalu Ofinanse.pl
                        
                        
//                        $userService->sendRegistrationMail($user, $mail, $this->view);

                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                   // $this->_helper->redirector->gotoUrl('/register/complete');
                    
                    $this->_helper->redirector->gotoRoute(array(),'domain-register-complete');
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    
                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage()));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                    var_dump($e->getMessage());
                } 
            }
//            else{
//                Zend_Debug::dump($form->getErrors());
//                 Zend_Debug::dump($clientForm->getErrors());
//            }
        }
         $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->layout->setLayout('contact');
        $this->view->assign('form', $form);
        
//        if (APPLICATION_ENV == 'ajurwedaDevelopment' || APPLICATION_ENV == 'ajurwedaProduction'):
//              $this->_helper->actionStack('layout-ajurweda', 'index', 'default');
//        elseif(APPLICATION_ENV == 'shopDevelopment' || APPLICATION_ENV == 'shopProduction'):
//            $this->_helper->actionStack('layout-shop', 'index', 'default');
//        endif;
    }
    public function testAction(){}
    public function completeAction() {
        $userService = $this->_service->getService('User_Service_User');

        $translator = $this->_service->get('Zend_Translate');
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setSubject($translator->translate('REGISTER_COMPLETE_MESSAGE_SUBJECT'));
        
        $this->view->messages()->clean();

        if($this->getRequest()->getParam('token') && $user = $userService->getUser($this->getRequest()->getParam('token'), 'token')) {
            if(!$user->isActive()) {
                $user->setActive(true);
                $user->save();

                $userService->sendRegistrationCompleteMail($user, $mail, $this->view);

                $this->view->messages()->add($this->view->translate($this->view->translate('REGISTER_THANK_YOU')), 'info');
            } else {
                $this->view->messages()->add($this->view->translate($this->view->translate('REGISTER_ACCOUNT_ACTIVE')), 'info');
            }
        }
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->layout->setLayout('contact');
    }

    public function adminAction() {
        $userService = $this->_service->getService('User_Service_User');

        $passwordEncoder = new User_PasswordEncoder();
                        
        $this->view->messages()->clean();

        $updateForm = new User_Form_Update();
        $updateForm->setElementDecorators(array('ViewHelper'));
        $updateForm->removeElement('email');
        $updateForm->getElement('token')->setValue($this->getRequest()->getParam('token'));
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $updateForm->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        if($this->getRequest()->isPost()) {
            if($updateForm->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $updateForm->getValues();
                        
                    if($user = $userService->getUser($values['token'], 'token')) {
                        $user->setPassword($passwordEncoder->encode($values['password'], $user['salt']));
                        $user->save();
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array(), 'admin', true);
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->_helper->layout->setLayout('admin.login');
        
        $this->view->assign('form', $updateForm);
    }

}