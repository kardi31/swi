<?php

/**
 * Newsletter_Service_SentMessage
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Service_SentMessage extends MF_Service_ServiceAbstract {
    
    protected $sentMessageTable;
    protected $subscriberTable;
    protected $groupTable;
    protected $messageTable;
    
    public function init() {
        $this->sentMessageTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_SentMessage');
        $this->subscriberTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Subscriber');
        $this->groupTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Group');
        $this->messageTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Message');
        parent::init();
    }
    
    public function getAllSubscribers($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->subscriberTable->getSubscriberQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getGroupWithSubscribers($groupId, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->groupTable->getGroupSubscribersQuery($groupId);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getSpecifySendMessage($messageId, $subscriberId) {
        $q = $this->sentMessageTable->getSentMessageQuery();
        $q->andWhere('message_id = ?', $messageId);
        $q->andWhere('subscriber_id = ?', $subscriberId);
    
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }
    
    public function getMessage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->messageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getSentMessage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->sentMessageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function saveSentMessagesFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        $message = $this->getMessage($values['message_id']);
        $messageSentMessages = $message->get('SentMessages')->toArray();

        if ($messageSentMessages):
            $message->get('SentMessages')->delete();
            $message->save();
        endif;

        if ($values['all_subscribers']):
            $subscribers = $this->getAllSubscribers();
            foreach($subscribers as $subscriber):
                $sentMessage = $this->sentMessageTable->getRecord();
                $sentMessage->setMessageId($values['message_id']);
                $sentMessage->setSubscriberId($subscriber->getId());
                $sentMessage->save();
            endforeach;
        else:
            foreach($values['subscriber_id'] as $subscriberId):
                $flag = true;
                foreach($values['group_id'] as $groupId):
                    $groupWithSubscribers = $this->getGroupWithSubscribers($groupId);
                    foreach($groupWithSubscribers[0]['Subscribers'] as $sub):
                        if ($sub['id'] == $subscriberId):
                            $flag = false;
                        endif;
                    endforeach;
                endforeach;
                if ($flag == true):
                    $sentMessage = $this->sentMessageTable->getRecord();
                    $sentMessage->setMessageId($values['message_id']);
                    $sentMessage->setSubscriberId($subscriberId);
                    $sentMessage->save();
                endif;
            endforeach;
            foreach($values['group_id'] as $groupId):
                $groupWithSubscribers = $this->getGroupWithSubscribers($groupId);
                foreach($groupWithSubscribers[0]['Subscribers'] as $sub):
                    $sentMessage = $this->getSpecifySendMessage($values['message_id'], $sub['id']);
                    if(!$sentMessage):
                        $sentMessage = $this->sentMessageTable->getRecord();
                        $sentMessage->setMessageId($values['message_id']);
                        $sentMessage->setSubscriberId($sub['id']);
                        $sentMessage->setGroupId($groupId);
                        $sentMessage->save();
                    endif;
                endforeach;
            endforeach;
        endif;
        foreach($messageSentMessages as $messageSen):
            $sentMessage = $this->getSpecifySendMessage($messageSen['message_id'], $messageSen['subscriber_id']);
            if($sentMessage):
                $mess = $this->getSentMessage($sentMessage[0]['id']);
                $mess->setSentAt($messageSen['sent_at']);
                $mess->setSent($messageSen['sent']);
                $mess->save();
            endif;
        endforeach;
    }
    
    public function getMessagesToSent($messageId){
        $q = $this->sentMessageTable->getSentMessageQuery();
        $q->andWhere('sm.message_id = ?', $messageId);
        $q->andWhere('sm.sent = ?', 0);
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getCountAllMessagesToSent($messageId){
        $q = $this->sentMessageTable->getSentMessageQuery();
        $q->andWhere('sm.message_id = ?', $messageId);
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD)->count();
    }

}

