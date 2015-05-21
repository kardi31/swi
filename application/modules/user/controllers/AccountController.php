<?php

class User_AccountController extends MF_Controller_Action {
    

    public function indexAction()
    {
         $this->_helper->layout->setLayout('account');
    }
    public function accountDataAction()
    {
         $this->_helper->layout->setLayout('account');
        $orderService = $this->_service->getService('Order_Service_Order');
        $authService = $this->_service->getService('User_Service_Auth');
        $userService = $this->_service->getService('User_Service_User');
        $modelCart = $orderService->getCart();
        $form = new Order_Form_PersonalData();
        $form->removeElement('difstreet');
        $form->removeElement('client_type');
        $form->removeElement('difpostal_code');
        $form->removeElement('difcity');
        $form->removeElement('difAddress');
        $form->removeElement('difflatNr');
        $form->removeElement('email');
        $form->removeElement('difhouseNr');
        $form->removeElement('submit');
        $ud = $userService->getFullUser(Zend_Auth::getInstance()->getIdentity(),'email');
        $user_id = $ud->getId();
        $userData = $userService->getProfile($user_id);
        $companyName = new Zend_Form_Element_Text('company_name');
        $companyName->setLabel('Company name');
        $companyName->setRequired(false);
        
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Save changes');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $form->addElement($companyName);
        $form->addElement($submit);
 //       Zend_Debug::dump($modelCart->getItems());
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $user = $userService->saveClientFromArray($values,$user_id);
                    $this->_helper->redirector->gotoUrl($this->view->url(array('action'=> 'account-data'),'domain-account'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
             //   
         }}
        $this->view->assign('userData',$userData);
        $this->view->assign('form',$form);
    }
    public function orderHistoryAction()
    {
        $orderService = $this->_service->getService('Order_Service_Order');
        $orders = $orderService->getUserOrders(Zend_Auth::getInstance()->getIdentity());
        
        $this->view->assign('orders',$orders);
        $this->_helper->layout->setLayout('account');
    }
}

