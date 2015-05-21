<?php

/**
 * Newsletter_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_AdminController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('send-message-portion', 'json')
                ->initContext();
        parent::init();
    }
    
    private $TYPE_NAME = array(
        0 => 'wiadomość',
        );
    
    public function listSubscriberAction() {
  
    }
    
    public function listSubscriberDataAction() {
        
        $table = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Subscriber');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Newsletter_DataTables_Subscriber', 
            'columns' => array('s.username', 's.email', 's.created_at'),
            'searchFields' => array('s.username', 's.email', 's.created_at')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->username;
            $row[] = $result->email;
            $row[] = MF_Text::timeFormat($result->created_at, 'H:i d/m/Y');
            $row[] = '<a href="' . $this->view->adminUrl('remove-subscriber', 'newsletter', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function removeSubscriberAction(){
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        
        if($subscriber = $subscriberService->getSubscriber($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $subscriberService->removeSubscriber($subscriber);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-subscriber', 'newsletter'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-subscriber', 'newsletter'));           
    }
    
    public function listGroupAction() {

    }
    
    public function listGroupDataAction() {
        
        $table = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Group');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Newsletter_DataTables_Group', 
            'columns' => array('g.name'),
            'searchFields' => array('g.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->name;
            $options = '<a href="' . $this->view->adminUrl('edit-group', 'newsletter', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-group', 'newsletter', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;$rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addGroupAction() {
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        $groupService = $this->_service->getService('Newsletter_Service_Group');
        
        $form = $groupService->getGroupForm();
        $form->getElement('subscriber_id')->setMultiOptions($subscriberService->getTargetSubscriberSelectOptions(false));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $group = $groupService->saveGroupFromArray($values); 
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'newsletter'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
                
        $this->view->assign('form', $form);
    }
    
     public function editGroupAction() {
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        $groupService = $this->_service->getService('Newsletter_Service_Group');
        
        if(!$group = $groupService->getGroup((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Group not found');
        }
        
        $form = $groupService->getGroupForm($group);
        $form->getElement('subscriber_id')->setMultiOptions($subscriberService->getTargetSubscriberSelectOptions(false));
        $form->getElement('subscriber_id')->setValue($group->get('Subscribers')->getPrimaryKeys());
        
        $form->setAction($this->view->adminUrl('edit-group', 'newsletter', array('id' => $group->getId())));
       
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                    
                    $group = $groupService->saveGroupFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                  
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'newsletter'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
           
        }

        $this->view->assign('form', $form);
    }
    
    public function removeGroupAction(){
        $groupService = $this->_service->getService('Newsletter_Service_Group');
        
         if($group = $groupService->getGroup($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $groupService->removeGroup($group);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'newsletter'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'newsletter'));                     
    }
    
    public function listMessageAction() {
        
    }
    
    public function listMessageDataAction() {
        
        $table = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Message');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Newsletter_DataTables_Message', 
            'columns' => array('m.id', 'm.title', 'm.created_at'),
            'searchFields' => array('m.title')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->id;
            $row[] = $result->title;
            $row[] = MF_Text::timeFormat($result->created_at, 'H:i d/m/Y');
            $options = '<a href="' . $this->view->adminUrl('send-message', 'newsletter', array('id' => $result->id)) . '" title="' . $this->view->translate('Send') . '"><span class="icon24 icon-arrow-up"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('edit-message', 'newsletter', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-message', 'newsletter', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addMessageAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        $groupService = $this->_service->getService('Newsletter_Service_Group');
        $sentMessageService = $this->_service->getService('Newsletter_Service_SentMessage');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $messageService->getMessageForm();
        $form->removeElement('product_id');
        $form->getElement('subscriber_id')->setMultiOptions($subscriberService->getTargetSubscriberSelectOptions(false));
        $form->getElement('group_id')->setMultiOptions($groupService->getTargetGroupSelectOptions(false));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $message = $messageService->saveMessageFromArray($values);
                    $values['message_id'] = $message->getId();
                    $sentMessageService->saveSentMessagesFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-message', 'newsletter', array('id' => $message->getid())));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
                
        $this->view->assign('form', $form);
    }
    
    public function editMessageAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        $groupService = $this->_service->getService('Newsletter_Service_Group');
        $sentMessageService = $this->_service->getService('Newsletter_Service_SentMessage');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$message = $messageService->getMessage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }

        $form = $messageService->getMessageForm($message);
        $form->removeElement('product_id');
        $form->getElement('subscriber_id')->setMultiOptions($subscriberService->getTargetSubscriberSelectOptions(false));
        $form->getElement('group_id')->setMultiOptions($groupService->getTargetGroupSelectOptions(false)); 
        
        $sentMessages = $message->get('SentMessages');
        $subscriberIds = array();
        $groupIds = array();
        foreach($sentMessages as $sentMessage):
            if ($sentMessage['group_id']):
                $groupIds[] = $sentMessage['group_id'];
            else:
                $subscriberIds[] = $sentMessage['subscriber_id'];
            endif;
        endforeach;
        $allSubscribers = $form->getElement('all_subscribers')->getValue();
        $form->getElement('subscriber_id')->setValue($subscriberIds);
        $form->getElement('group_id')->setValue($groupIds);
     
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    
                    $message = $messageService->saveMessageFromArray($values);
                    
                    $values['message_id'] = $message->getId();
                    $sentMessageService->saveSentMessagesFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'newsletter'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
//            else{
//                var_dump($form->getMessages());exit;
//            }
        }     
        $this->view->assign('allSubscribers', $allSubscribers);
        $this->view->assign('form', $form);    
    }
    
    public function removeMessageAction(){
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        
        if($message = $messageService->getMessage($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $messageService->removeMessage($message);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'newsletter'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'newsletter'));          
    }
    
    public function sendMessageAction(){
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        $sentMessageService = $this->_service->getService('Newsletter_Service_SentMessage');
        
        if(!$message = $messageService->getMessage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }
        
        $form = new Newsletter_Form_SendForm();
        if($this->getRequest()->isPost()) {
                if($form->isValid($this->getRequest()->getPost())) {
                    try {
                       
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('send-message-progress', 'newsletter', array("id" => $message->getId())));

                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('log')->log($e->getMessage(), 4);
                    }
                }
        }           
        $this->view->assign('form', $form); 
        $this->view->assign('message', $message); 
    }
    
    public function sendMessageProgressAction(){
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        $sentMessageService = $this->_service->getService('Newsletter_Service_SentMessage');
        
        if(!$message = $messageService->getMessage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }
        $messagesToSent = $sentMessageService->getMessagesToSent($message->getId());
       
        $allMessagesToSentCounter = $sentMessageService->getCountAllMessagesToSent($message->getId());
        $counter = $messagesToSent->count();
        $alreadySent = $allMessagesToSentCounter - $counter;
        $percent = $alreadySent*100/$allMessagesToSentCounter;

        
        $this->view->assign("allMessagesToSentCounter", $allMessagesToSentCounter);
        $this->view->assign("percent", $percent);
        $this->view->assign("counter", $counter);
        $this->view->assign('message', $message); 
    }
    
    public function sendMessagePortionAction(){
        $this->_helper->layout->disableLayout();
        
        $messageService = $this->_service->getService('Newsletter_Service_Message');
        $sentMessageService = $this->_service->getService('Newsletter_Service_SentMessage');
        
        if(!$message = $messageService->getMessage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }
        
        $messagesToSent = $sentMessageService->getMessagesToSent($message->getId());
       
        if ($messagesToSent->count != 0){
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $i = 0;
                for($i=0; $i<5; $i++){ 
                    if ($messagesToSent[$i]->get('Subscriber')->getEmail()):
                        $mail = new Zend_Mail('UTF-8');
                        $mail->addTo($messagesToSent[$i]->get('Subscriber')->getEmail());
                        $mail->setBodyHTML($this->view->partial('message.phtml', array('message' => $message, 'token' => $messagesToSent[$i]->get('Subscriber')->getToken())));
                        
                        $mail->setSubject($message->getTitle());
                        $mail->send();
                        $messagesToSent[$i]->setSent(1);
                        $messagesToSent[$i]->save();
                    elseif(strlen($messagesToSent[$i]->get('Subscriber')->get('username'))):
                        $messagesToSent[$i]->setSent(1);
                        $messagesToSent[$i]->setError('Brak emaila');
                    else:
                        break;
                    endif;
                    sleep(5);
                }
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
               
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }           
        }

        $counter = $sentMessageService->getMessagesToSent($message->getId())->count();
        
        $allMessagesToSentCounter = $sentMessageService->getCountAllMessagesToSent($message->getId());
        $alreadySent = $allMessagesToSentCounter - $counter;
        $percent = $alreadySent*100/$allMessagesToSentCounter;

        
        $this->view->assign("allMessagesToSentCounter", $allMessagesToSentCounter);
        $this->view->assign("percent", $percent);
        $this->view->assign("counter", $counter);
        
        
        $progressView = $this->view->partial('admin/send-message-portion.phtml', 'newsletter', array('counter' => $counter, 'message' => $message, 'allMessagesToSentCounter' => $allMessagesToSentCounter, 'percent' => $percent));
        $this->view->assign('message', $message); 
        
        $this->_helper->json(array(
             'status' => 'success',
             'body' => $progressView
        ));      
    }
    
    public function headerTemplateAction() {
        
        $this->_helper->layout->disableLayout();
    }
    
    public function newsEventsTemplateAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $eventPromotedService = $this->_service->getService('Event_Service_EventPromoted');
        $companyService = $this->_service->getService('Company_Service_Company');
        
        $newsIds = $this->getRequest()->getParam('news-ids');
        $newsIds = explode(',', $newsIds);
        
        $news = $newsService->getPreSortedPredifiniedNews($newsIds);
        
        $eventsIds = $this->getRequest()->getParam('events-ids');
        $eventsIds = explode(',', $eventsIds);
        
        $events = $eventPromotedService->getPreSortedPredifiniedEventsPromoted($eventsIds);
        
        $companiesIds = $this->getRequest()->getParam('companies-ids');
        $companiesIds = explode(',', $companiesIds);
        
        $companies = $companyService->getPreSortedPredifiniedCompanies($companiesIds);
        
        $this->view->assign('companies', $companies);
        $this->view->assign('news', $news);
        $this->view->assign('events', $events);
    }
    
    public function newestProductsTemplateAction() {
        $this->_helper->layout->disableLayout();
      $productService = $this->_service->getService('Product_Service_Product');
      
      $productsIds = $this->getRequest()->getParam('products-ids');
      $productsIds = explode(',', $productsIds);
      $newestProducts = $productService->getPreSortedPredifiniedNewestProducts(3, $productsIds);
        
      $this->view->assign('newestProducts', $newestProducts);
    }
    
    public function footerTemplateAction() {
  
        $this->_helper->layout->disableLayout();
    }
}

