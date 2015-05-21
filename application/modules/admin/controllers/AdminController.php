<?php

class Admin_AdminController extends MF_Controller_Action
{
    public function dashboardAction() {
       
    }
    
    public function listMessageAction() {
        
    }
    
    public function fillMessageListAction() {
        $messageManager = new Admin_Model_MessageManager();
        $user = $this->_helper->user();
        
        $readMessages = $messageManager->findUserReadMessages($user->getId());
        
        $q = Doctrine_Core::getTable('Admin_Model_Doctrine_Message')->createQuery('m')
                ->select('m.*')
                ->addSelect('u.first_name, u.last_name')
                ->leftJoin('m.User u')
                ->limit($_GET['iDisplayLength'])
                ->offset($_GET['iDisplayStart'])
                ;
        
        // sorting
        if(isset( $_GET['iSortCol_0'])) {
            $columns = array('m.subject', 'u.last_name', 'm.created_at');
            
            for($i=0; $i < intval($_GET['iSortingCols']); $i++) {
                if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])] == "true") {
                    $order = $columns[intval( $_GET['iSortCol_'.$i])];
                    $dir = $_GET['sSortDir_'.$i];
                }
            }
            
            $q->orderBy("$order $dir");
        }
        
        // filtering
        if(isset($_GET['sSearch'])) {
            $phrase = $_GET['sSearch'];
            $q->andWhere("m.subject LIKE ? OR CONCAT_WS(' ', u.first_name, u.last_name)", array("%$phrase%"));
        }
                
        $messages = $q->execute();
        
        $total = Doctrine_Core::getTable('Admin_Model_Doctrine_Message')->count();
        $displayTotal = $messages->count();
   
        $rows = array();
        foreach($messages as $message) {
            $row = array();
            $row['DT_RowId'] = $message->id;
            if(!in_array($message->id, $readMessages->getPrimaryKeys())) {
                $row['DT_RowClass'] = 'gradeA';
            }
            $row[] = $message->subject;
            $row[] = $message->User->first_name . ' ' . $message->User->last_name;
            $row[] = MF_Text::convertTime($message->created_at, 'H:i d/m/Y');
            $options = '';
            if($message->user_id == $user->getId()) {
                $options .= '<a href="' . $this->view->adminUrl('delete-message', 'admin', array('id' => $message->id)) . '" class="delete-item" title="' . $this->view->translate('Delete') . '">' . $this->view->translate('Delete') . '</a>';
                $options .= '<a href="' . $this->view->adminUrl('edit-message', 'admin', array('id' => $message->id)) . '" class="edit-item" title="' . $this->view->translate('Edit') . '">' . $this->view->translate('Edit') . '</a>';
            }
            $options .= '<a href="' . $this->view->adminUrl('show-message', 'admin', array('id' => $message->id)) . '" class="show-item" title="' . $this->view->translate('Show') . '">' . $this->view->translate('Show') . '</a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $displayTotal,
            "iTotalDisplayRecords" => $total,
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addMessageAction() {
        $messageManager = new Admin_Model_MessageManager();
        
        $user = $this->_helper->user();
        
        $form = new Admin_Form_Message();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                $form->getElement('user_id')->setValue($user->getId());
                try {
                    $message = $messageManager->saveFromForm($form);
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-message', 'admin', array('lang' => null, 'id' => $message->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'admin', array('lang' => null)));
                } catch(Exception $e) {
                    Zend_Registry::get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editMessageAction() {
        $messageManager = new Admin_Model_MessageManager();
        
        $user = $this->_helper->user();
        
        if(!$message = $messageManager->findOneById($this->getRequest()->getParam('id'))) {
            throw new Zend_Contrller_Action_Exception('Message not found');
        }
        
        $form = $messageManager->createEditForm($message);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                $form->getElement('user_id')->setValue($user->getId());
                try {
                    $message = $messageManager->saveFromForm($form);
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-message', 'admin', array('lang' => null, 'id' => $message->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'admin', array('lang' => null)));
                } catch(Exception $e) {
                    Zend_Registry::get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('message', $message);
        $this->view->assign('form', $form);
    }
    
    public function deleteMessageAction() {
        $messageManager = new Admin_Model_MessageManager();
        
        if(!$message = $messageManager->findOneById($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Aciton_Exception('Message not found');
        }
        
        try {
            $messageManager->remove($message);
        } catch(Exception $e) {
            Zend_Registry::get('Logger')->log($e->getMessage(), 4);
        }
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'admin', array('lang' => null)));
    }
    
    public function showMessageAction() {
        $messageManager = new Admin_Model_MessageManager();
        
        $user = $this->_helper->user();
        
        if(!$message = $messageManager->findOneById($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }
        
        $messageManager->setMessageRead($message, $user->getId());
        
        $unreadMessages = $messageManager->findUserUnreadMessages($user->getId());
        $this->view->assign('unreadMessages', $unreadMessages);
        
        $this->view->assign('message', $message);
    }
    
}    