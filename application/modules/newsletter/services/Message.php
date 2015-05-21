<?php

/**
 * Newsletter_Service_Message
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Service_Message extends MF_Service_ServiceAbstract {
    
    protected $messageTable;
    
    public function init() {
        $this->messageTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Message');
        parent::init();
    }
    
    public function getMessage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->messageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getMessageForm(Newsletter_Model_Doctrine_Message $message = null) {
        $form = new Newsletter_Form_Message();
        if(null != $message) {
            $form->populate($message->toArray());
        }
        return $form;
    }
    
     public function saveMessageFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->messageTable->getProxy($values['id'])) {
            $message = $this->messageTable->getRecord();
        }
        $message->fromArray($values);
        
//        $message->unlink('ProductMessages');
//        $message->link('ProductMessages', $values['product_id']);
        

        $message->save();

        return $message;
    }
    
    public function removeMessage($message){
        $message->delete();         
    }
    
}

