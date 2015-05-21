<?php

/**
 * Admin_Model_MessageManager
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_Model_MessageManager {
    
    public function findOneById($id) {
        return Doctrine_Core::getTable('Admin_Model_Doctrine_Message')->find($id);
    }
    
    public function findUserReadMessages($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = Doctrine_Query::create()
                ->select('m.id, m.subject, m.content')
                ->from('Admin_Model_Doctrine_Message m')
                ->where('m.id IN (SELECT r.message_id FROM Admin_Model_Doctrine_MessageRegistry r ON r.id = m.id AND r.user_id = ' . $userId . ')')
                ;
        return $q->execute(array(), $hydrationMode);
    }
    
    public function findUserUnreadMessages($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = Doctrine_Query::create()
                ->select('m.id, m.subject, m.content')
                ->from('Admin_Model_Doctrine_Message m')
                ->where('m.id NOT IN (SELECT r.message_id FROM Admin_Model_Doctrine_MessageRegistry r ON r.id = m.id AND r.user_id = ' . $userId . ')')
                ;
        return $q->execute(array(), $hydrationMode);
    }
    
    public function setMessageRead($message, $userId) {
        if(!$this->checkUserMessageRead($message, $userId)) {
            $messageReqistry = new Admin_Model_Doctrine_MessageRegistry();
            $messageReqistry->setMessageId($message->id);
            $messageReqistry->setUserId($userId);
            $messageReqistry->save();
        }
    }
    
    public function checkUserMessageRead($message, $userId) {
        $q = Doctrine_Query::create()
                ->select('r.id')
                ->from('Admin_Model_Doctrine_MessageRegistry r')
                ->where('r.message_id = ? AND r.user_id = ?', array($message->id, $userId))
                ;
        return (bool) $q->count();
    }
    
    public function createEditForm($message) {
        $form = new Admin_Form_Message();
        $form->getElement('id')->setValue($message->getId());
        $form->getElement('user_id')->setValue($message->getUserId());
        $form->getElement('subject')->setValue($message->getSubject());
        $form->getElement('content')->setValue($message->getContent());
        return $form;
    }
    
    public function saveFromForm($form) {
        if($form->getValue('id')) {
            if(!$message = $this->findOneById($form->getValue('id'))) {
                throw new Zend_Controller_Action_Exception('Message not found');
            }
        } else {
            $message = new Admin_Model_Doctrine_Message();
        }
        
        $message->setUserId($form->getValue('user_id'));
        $message->setSubject($form->getValue('subject'));
        $message->setContent($form->getValue('content'));
        $message->save();
        return $message;
    }
    
    public function remove($message) {
        $message->delete();
    }
}

