<?php

class User_AdminController extends MF_Controller_Action
{
    public function init() {
        $this->getFrontController()->getPlugin('Zend_Controller_Plugin_ErrorHandler')->setErrorHandlerModule('admin');
        parent::init();
    }
    
    public function editAccountAction() {   
        $userService = $this->_service->getService('User_Service_User');
        $authService = $this->_service->getService('User_Service_Auth');
        
        $translator = $this->_service->get('translate');
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        
        $user = $authService->getAuthenticatedUser();

        if(!$user instanceof User_Model_Doctrine_User) {
            $this->view->messages()->add($translator->translate("You don't have sufficient permissions"), 'error');
        }
        
        $form = $userService->getUserForm($user); //new User_Form_User();
        $form->removeElement('role');

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $oldEmail = $user->getEmail();
                    
                    $values = $form->getValues();
                    $values['id'] = $user->getId();
                    
                    if ( $user->getEmail() != $values['email'] ){
                        if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null)))
                            throw new User_Model_UserWithEmailAlreadyExistsException($translator->translate('User already exists'));
                    }
                    $passwordEncoder = new User_PasswordEncoder();
                    
                    $user = $userService->saveFromArray($values);

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setSubject($translator->translate('Edycja konta administratora w portalu ajurweda.pl'));
                    $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
                    $mail->setReplyTo($options['reply_email'], 'System logowania ajurweda.pl');

                    $userService->sendAdminEditMail($user, $mail, $this->view);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if ( $oldEmail != $values['email'] ){
                        Zend_Auth::getInstance()->clearIdentity();
                    }
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl());
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage()));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        $this->view->assign('admin', $user);
  
    }
    
    public function listClientAction() {
     
    }
    
    public function listClientDataAction() {
        $table = Doctrine_Core::getTable('User_Model_Doctrine_User');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'User_DataTables_Client', 
            'columns' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'x.email', 'p.city', 'p.province', 'x.created_at'),
            'searchFields' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'x.email', 'p.city', 'p.province')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result['first_name'] . ' ' . $result['last_name'];
            $row[] = $result['email'];
            $row[] = $result['Profile']['city'];
            $row[] = $result['Profile']['province'];
            $row[] = MF_Text::timeFormat($result['created_at'], 'H:i m/d/Y');
            if ($result['active'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-client', 'user', array('user-id' => $result['id'])) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-client', 'user', array('user-id' => $result['id'])) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $options = '<a href="' . $this->view->adminUrl('edit-client', 'user', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-client', 'user', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
     public function editClientAction() {
        $userService = $this->_service->getService('User_Service_User');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setSubject($this->view->translate('Update user data'));
        
        $translator = $this->_service->get('translate');

        if(!$user = $userService->getFullUser($this->getRequest()->getParam('id'), 'id', Doctrine_Core::HYDRATE_ARRAY_SHALLOW)) {
            throw new Zend_Controller_Action_Exception('User not found');
        }
        
        $form = $userService->getClientForm($user);

        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    
                    $mail->addTo($user['email']);
                    
                    if ( $user['email'] != $values['email'] ){
                        if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null))):                     
                            throw new User_Model_UserWithEmailAlreadyExistsException($translator->translate('Email already exists in database'));
                        else: 
                            $mail->clearRecipients();
                            $mail->addTo($values['email']);
                        endif;       
                    }
                    
                    $userService->saveClientFromArray($values);

                    $userService->sendUpdateDataMail($user, $mail, $this->view, 'email/update-client-admin.phtml');
                    
                    $this->view->messages()->add($translator->translate('User has been updated'), 'success');   
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-client', 'user'));
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage())); 
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }

        $this->view->assign('client', $user); 
        $this->view->assign('form', $form); 
    }
    
    public function removeClientAction() {
        $userService = $this->_service->getService('User_Service_User');
        $authService = $this->_service->getService('User_Service_Auth');
        
        $currentUser = $authService->getAuthenticatedUser();
        
        if($user = $userService->getUser($this->getRequest()->getParam('id'))) {
            // prevent remove oneself
            if($currentUser instanceof User_Model_Doctrine_User && ($currentUser->getId() == $user->getId())) {
                throw new Exception('Cannot remove your own account');
            }
            
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $profile = $userService->getProfile($user->getId(), 'user_id');
                
                $userService->removeClient($user, $profile);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-client', 'user'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-client', 'user'));
    }
    
    public function refreshStatusClientAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        if(!$user = $userService->getUser((int) $this->getRequest()->getParam('user-id'))) {
            throw new Zend_Controller_Action_Exception('User not found');
        }
        
        $userService->refreshStatusClient($user);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-client', 'user'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function listAdminAction() {
        
    }
    
    public function listAdminDataAction() {
        $table = Doctrine_Core::getTable('User_Model_Doctrine_User');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'User_DataTables_Admin', 
            'columns' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'x.email', 'x.created_at'),
            'searchFields' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'x.email')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result['first_name'] . ' ' . $result['last_name'];
            $row[] = $result['email'];
            $row[] = MF_Text::timeFormat($result['created_at'], 'H:i m/d/Y');
            $options = '<a href="' . $this->view->adminUrl('edit-admin', 'user', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-admin', 'user', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addAdminAction() {
        $userService = $this->_service->getService('User_Service_User');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $translator = $this->_service->get('translate');
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        
        $form = new User_Form_User();
        $form->removeElement('role');

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null))) {
                        throw new User_Model_UserWithEmailAlreadyExistsException($translator->translate('Email already exists in database'));
                    } else {
                        // saving user
                        $values = $form->getValues();
                        
                        $passwordEncoder = new User_PasswordEncoder();
                        $values['salt'] = MF_Text::createUniqueToken();
                        $values['token'] = MF_Text::createUniqueToken();
                        $values['role'] = 'admin';
                        
                        $user = $userService->saveAdminFromArray($values);

                        if(isset($values['profile'])) {
                            $profile = $userService->createUserProfile($user, $values['profile']);
                        } else {
                            $profile = $userService->createUserProfile($user);
                        }

                        $root = $profile->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $profile->set('PhotoRoot', $root);
                        }
                        
                        $mail = new Zend_Mail('UTF-8');
                        $mail->setSubject($translator->translate('Założenie konta administratora w portalu ajurweda.pl'));
                        $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
                        $mail->setReplyTo($options['reply_email'], 'System logowania ajurweda.pl');
                        
                        $userService->sendAdminAddMail($user, $mail, $this->view);
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-admin', 'user'));
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage()));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editAdminAction() {
        $userService = $this->_service->getService('User_Service_User');
        
        $translator = $this->_service->get('translate');
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        
        if(!$user = $userService->getUser((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('User not found');
        }
        
        $form = $userService->getUserForm($user); //new User_Form_User();
        $form->removeElement('role');

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if ( $user->getEmail() != $values['email'] ){
                        if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null)))
                            throw new User_Model_UserWithEmailAlreadyExistsException($translator->translate('User already exists'));
                    }
                    $passwordEncoder = new User_PasswordEncoder();

                    $user = $userService->saveFromArray($values);

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setSubject($translator->translate('Edycja konta administratora w portalu ajurweda.pl'));
                    $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
                    $mail->setReplyTo($options['reply_email'], 'System logowania ajurweda.pl');

                    $userService->sendAdminEditMail($user, $mail, $this->view);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-admin', 'user'));
                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
                    $form->getElement('email')->markAsError();
                    $form->getElement('email')->setErrors(array($e->getMessage()));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        $this->view->assign('admin', $user);
    }
    
    public function removeAdminAction() {
        $userService = $this->_service->getService('User_Service_User');
        $authService = $this->_service->getService('User_Service_Auth');
        
        $translator = $this->_service->get('translate');
        
        $currentUser = $authService->getAuthenticatedUser();
        
        if($user = $userService->getUser($this->getRequest()->getParam('id'))) {
            try {
            
                // prevent remove oneself
                if($currentUser instanceof User_Model_Doctrine_User && ($currentUser->getId() == $user->getId())) {
                    throw new User_Model_UserCannotRemoveException($translator->translate('Cannot remove your own account'));
                }
              
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $result = $userService->removeAdmin($user);
           
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-admin', 'user'));
            } catch(User_Model_UserCannotRemoveException $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-admin', 'user'));
    }
    
    public function listGroupClientAction() {

    }
    
    public function listGroupClientDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('User_Model_Doctrine_Group');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'User_DataTables_Group', 
            'columns' => array('gr.name', 'gr.created_at'),
            'searchFields' => array('gr.name')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = $result['created_at'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-group-client', 'user', array('group-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-group-client', 'user', array('group-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $options = '<a href="' . $this->view->adminUrl('edit-group-client', 'user', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-group-client', 'user', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }

    public function addGroupClientAction() { 
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $userService = $this->_service->getService('User_Service_User');
        $groupService = $this->_service->getService('User_Service_Group');
        $discountService = $this->_service->getService('Product_Service_Discount');
          
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        $form = $groupService->getGroupForm();
        $form->getElement('user_id')->setMultiOptions($userService->getTargetClientSelectOptions());
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
 
                    $group = $groupService->saveGroupFromArray($values); 
           
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group-client', 'user'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);       
    }
    
    public function editGroupClientAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $groupService = $this->_service->getService('User_Service_Group');
        $userService = $this->_service->getService('User_Service_User');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$group = $groupService->getGroup((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Group not found');
        }
        
        $form = $groupService->getGroupForm($group);
        
        $form->getElement('user_id')->setMultiOptions($userService->getTargetClientSelectOptions(false));
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('user_id')->setValue($group->get('Users')->getPrimaryKeys());
        
        $form->setAction($this->view->adminUrl('edit-group-client', 'user'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $group = $groupService->saveGroupFromArray($values); 
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group-client', 'user'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('group', $group);
    } 
    
    public function removeGroupClientAction() {
        $groupService = $this->_service->getService('User_Service_Group');
        
        if($group = $groupService->getGroup($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                        
                $groupService->removeGroup($group);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group-client', 'user'));
            } catch(Exception $e) {
                var_dump($e->getMessage()); exit; 
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group-client', 'user'));
    }
    
    public function refreshStatusGroupClientAction() {
        $groupService = $this->_service->getService('User_Service_Group');
        
        if(!$group = $groupService->getGroup((int) $this->getRequest()->getParam('group-id'))) {
            throw new Zend_Controller_Action_Exception('Group not found');
        }
        
        $groupService->refreshStatusGroupClient($group);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group-client', 'user'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    //    public function listAgentAction() {
//        
//    }
//    
//    public function listAgentDataAction() {
//        $table = Doctrine_Core::getTable('User_Model_Doctrine_User');
//        $dataTables = Default_DataTables_Factory::factory(array(
//            'request' => $this->getRequest(), 
//            'table' => $table, 
//            'class' => 'User_DataTables_Agent', 
//            'columns' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'offer_count', 'x.email', 'c.name', 'pv.name', 'x.created_at'),
//            'searchFields' => array('CONCAT_WS(" ", x.first_name, x.last_name)', 'x.email', 'c.name', 'pv.name')
//        ));
//        
//        $results = $dataTables->getResult();
//        
//        $rows = array();
//        foreach($results as $result) {
//            $row = array();
//            $row['DT_RowId'] = $result->id;
//            $row[] = $result['first_name'] . ' ' . $result['last_name'];
//            $row[] = '<a href="' . $this->view->adminUrl('list-offer', 'offer', array('user-id' => $result['id'])) . '">' . $result['offer_count'] . '</a>';
//            $row[] = $result['email'];
//            $row[] = $result['Profile']['city'];
//            $row[] = $result['Profile']['province'];
//            $row[] = MF_Text::timeFormat($result['created_at'], 'H:i m/d/Y');
//            $options = '<a href="' . $this->view->adminUrl('edit-user', 'user', array('role' =>'client', 'id' => $result->id)) . '" class="edit-item"><span class="icon16 icon-edit"></span></a>';
//            $options .= '<a href="' . $this->view->adminUrl('delete-user', 'user', array('role' =>'client', 'id' => $result->id)) . '" class="delete-item"><span class="icon16 icon-remove"></span></a>';
//            $row[] = $options;
//            $rows[] = $row;
//        }
//
//        $response = array(
//            "sEcho" => intval($_GET['sEcho']),
//            "iTotalRecords" => $dataTables->getDisplayTotal(),
//            "iTotalDisplayRecords" => $dataTables->getTotal(),
//            "aaData" => $rows
//        );
//
//        $this->_helper->json($response);
//    }
//    
    
    //    public function editAccountAction() {
//        $authService = $this->_service->getService('User_Service_Auth');
//        $userService = $this->_service->getService('User_Service_User');
//        $user = $authService->getAuthenticatedUser();
//
//        $translator = $this->_service->get('translate');
//        
//        $form = $userService->getUserForm($user); //new User_Form_User();
//        $form->removeElement('role');
//        
//        if($this->getRequest()->isPost()) {
//            if($form->isValid($this->getRequest()->getParams())) {
//                try {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//                    
//                    $values = $form->getValues();
//
//                    if ( $user->getEmail() != $values['email'] ){
//                            if($userService->userExists(array('email' => $form->getValue('email'), 'deleted_at' => null)))
//                                throw new User_Model_UserWithEmailAlreadyExistsException($translator->translate('User already exists'));
//                    }
//                    else{
//                        $passwordEncoder = new User_PasswordEncoder();
//
//                        $user = $userService->saveFromArray($values);
//
//                        $mail = new Zend_Mail('UTF-8');
//                        $mail->setSubject($translator->translate('Edycja konta administratora w portalu ajurweda.pl'));
//                        $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
//                        $mail->setReplyTo($options['reply_email'], 'System logowania ajurweda.pl');
//
//                        $userService->sendAdminEditMail($user, $mail, $this->view);
//                    }
//                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                    
//                    $this->_helper->redirector->gotoUrl($this->view->adminUrl());
//                } catch(User_Model_UserWithEmailAlreadyExistsException $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                    $this->view->messages()->add($this->view->translate($translator->translate($e->getMessage())), 'error');
//                    $form->getElement('email')->markAsError();
//                    $form->getElement('email')->setErrors(array($e->getMessage()));
//                } catch(Exception $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                    $this->_service->get('log')->log($e->getMessage(), 4);
//                }
//            }
//        }
//        
//        $this->view->assign('form', $form);
//        
//    }
    
//    public function listUserAction() {
//
//    }
//    
//    public function fillListAction() {
//        $table = Doctrine_Core::getTable('User_Model_Doctrine_User');
//        $dataTables = Default_DataTables_Factory::factory(array(
//            'request' => $this->getRequest(), 
//            'table' => $table, 
//            'class' => 'User_DataTables_User', 
//            'columns' => array('x.username', 'x.email'),
//            'searchFields' => array('x.username', 'x.email')
//        ));
//        
//        $results = $dataTables->getResult();
//        
//        $rows = array();
//        foreach($results as $result) {
//            $row = array();
//            $row['DT_RowId'] = $result->id;
//            $row[] = $result->username;
//            $row[] = $result->email;
//            $type = '';
//            switch($result->role) {
//                case 'admin':
//                    $type = 'Admin';
//                    break;
//                case 'user':
//                    if($result->type == 'a') {
//                        $type = 'Agency';
//                    } elseif($result->type == 'i') {
//                        $type = 'Independent';
//                    }
//            }
//            $row[] = $type;
//            $options = '<a href="' . $this->view->adminUrl('delete-user', 'user', array('id' => $result->id)) . '" class="delete-item">' . $this->view->translate('Delete') . '</a>';
//            $options .='<a href="' . $this->view->adminUrl('edit-user', 'user', array('id' => $result->id)) . '" class="edit-item">' . $this->view->translate('Edit') . '</a>';
//            $row[] = $options;
//            $rows[] = $row;
//        }
//
//        $response = array(
//            "sEcho" => intval($_GET['sEcho']),
//            "iTotalRecords" => $dataTables->getDisplayTotal(),
//            "iTotalDisplayRecords" => $dataTables->getTotal(),
//            "aaData" => $rows
//        );
//
//        $this->_helper->json($response);
//        
//    }
//    
//    public function addUserAction() {
//        $userService = $this->_service->getService('User_Service_User');
//        
//        $form = $userService->getUserForm();
//        $form->getElement('role')->setMultiOptions(array('admin' => 'Admin'));
//        $form->getElement('role')->setValue('admin');
//        
//        $mail = new Zend_Mail('UTF-8');
//        $mail->setSubject($this->view->translate('REGISTER_MESSAGE_SUBJECT'));
//        
//        if($this->getRequest()->isPost()) {
//            if($form->isValid($this->getRequest()->getParams())) {
//                try{
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//
//                    $values = $form->getValues();
//                    $values['salt'] = MF_Text::createUniqueToken();
//                    $values['token'] = MF_Text::createUniqueToken();
//                    
//                    $user = $userService->saveFromArray($values);
//                    
//                    $userService->sendRegistrationMail('admin', $user, $mail, $this->view);
//                    
//                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                } catch(Exception $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//                    $this->_service->get('Logger')->log($e->getMessage(), 4);
//                }
//            }
//        }
//        
//        $this->view->assign('form', $form);
//        
//    }
//    
//    public function editUserAction() {
//        $userService = $this->_service->getService('User_Service_User');
//        $authService = $this->_service->getService('User_Service_Auth');
//        
//        $currentUser = $authService->getAuthenticatedUser();
//        
//        if(!$user = $userService->getUser($this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('User not found');
//        }
//        
//        $form = $userService->getUserForm($user);
//        $form->getElement('role')->setMultiOptions(array('admin' => 'Admin'));
//        $form->getElement('role')->setValue('admin');
//        
//        if($this->getRequest()->isPost()) {
//            if($form->isValid($this->getRequest()->getParams())) {
//                try {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//
//                    $values = $form->getValues();
//                    
//                    if($currentUser instanceof User_Model_Doctrine_User && ($currentUser->getId() == $user->getId())) {
//                        $values['active'] = 1;
//                    }
//                    
//                    $user = $userService->saveFromArray($values);
//                    
//                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                    
//                    if($this->getRequest()->getParam('saveOnly') == '1')
//                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-user', 'user', array('id' => $user->getId())));
//                    
//                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-user', 'user'));
//                } catch(Exception $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//                    $this->_service->get('Logger')->log($e->getMessage(), 4);
//                }
//            }
//        }
// 
//        $this->view->assign('form', $form);
//        
//    }
//    
//    public function deleteUserAction() {
//        $userService = $this->_service->getService('User_Service_User');
//        $authService = $this->_service->getService('User_Service_Auth');
//        
//        $currentUser = $authService->getAuthenticatedUser();
//        
//        if($user = $userService->getUser($this->getRequest()->getParam('id'))) {
//            // prevent remove oneself
//            if($currentUser instanceof User_Model_Doctrine_User && ($currentUser->getId() == $user->getId())) {
//                throw new Exception('Cannot remove your own account');
//            }
//            
//            try {
//                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//                $userService->removeUser($user);
//                
//                $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-user', 'user'));
//            } catch(Exception $e) {
//                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//                $this->_service->get('Logger')->log($e->getMessage(), 4);
//            }
//        }
//        
//        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-user', 'user'));
//    }
//    
//    public function registerConfirmAction() {
//        $this->_helper->layout->setLayout('admin.login');
//        $userService = $this->_service->getService('User_Service_User');
//               
//        if(!$user = $userService->getUser($this->getRequest()->getParam('token'), 'token')) {
//            $this->_helper->redirector->gotoRoute(array(), 'domain-login');
//        }
//            
//        $recoverPasswordForm = new User_Form_RecoverPassword();
//        $recoverPasswordForm->getElement('token')->setValue($this->getRequest()->getParam('token'));
//        
//        if($this->getRequest()->isPost()) {
//            if($recoverPasswordForm->isValid($this->getRequest()->getParams())) {
//                try {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//
//                    $passwordEncoder = new User_PasswordEncoder();
//                    $password = $passwordEncoder->encode($recoverPasswordForm->getValue('password'));
//                    $user->setPassword($password);
//                    $user->setActive(1);
//                    $user->save();
//                    
//                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                    
//                    $this->_helper->redirector->gotoUrl($this->view->adminUrl());
//                } catch(Exception $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                    $this->_service->get('Logger')->log($e->getMessage(), 4);
//                }
//            }
//        }
//        
//        $this->view->assign('form', $recoverPasswordForm);
//       
//    }
//    
//    protected function _resolveTeamMemberGroupName($roles) {
//        $translator = $this->getInvokeArg('bootstrap')->getResource('translate');
//        if(in_array('admin', $roles)) {
//            return $translator->translate('role_admin');
//        }
//        if(in_array('blogger', $roles)) {
//            return $translator->translate('role_blogger');
//        }
//        if(in_array('user', $roles)) {
//            return $translator->translate('role_user');
//        }
//        
//        return '';
//    }
}