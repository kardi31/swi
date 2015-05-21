<?php

/**
 * Newsletter_Service_Newsletter
 *
 * @author Mateusz Aniołek
 */
class Newsletter_Service_Newsletter extends MF_Service_ServiceAbstract {
    
    protected $messageTable;
    protected $settingsTable;
    
    public function init() {
        $this->messageTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Message');
        $this->settingsTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Settings');
        parent::init();
    }
    
    public function getMessageForm(Newsletter_Model_Doctrine_Message $message = null) {
        $form = new Newsletter_Form_Message();
        if(null != $message) {
            $form->populate($message->toArray());
            //$form->getElement('send_date')->setValue(MF_Text::timeFormat($message->getSendDate(), 'd/m/Y H:i'));
        }
        return $form;
    }
    
    public function getNewsletterForm(Newsletter_Model_Doctrine_Newsletter $newsletter = null) {
        $form = new Newsletter_Form_Newsletter();
        if(null != $newsletter) {
            $form->populate($newsletter->toArray());
        }
        return $form;
    }
    
    public function saveMessageFromArray($values) {
        //var_dump($values); exit;
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->messageTable->getProxy($values['id'])) {
            $message = $this->messageTable->getRecord();
        }

        $values['send_date'] = strlen($values['send_date']) ? $values['send_date'] : date('d/m/Y H:i');
        $values['send_date'] = MF_Text::timeFormat($values['send_date'], 'Y-m-d H:i:s', 'd/m/Y H:i');
        
        $message->fromArray($values);
        $message->save();
        $k = $message->identifier();
        return $k['id'];
    }
    
    
    public function saveSettingsFromArray($values) {
        
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->settingsTable->getProxy($values['id'])) {
            $message = $this->settingsTable->getRecord();
        }

        //$values['send_date'] = strlen($values['send_date']) ? $values['send_date'] : date('d/m/Y H:i');
        //$values['send_date'] = MF_Text::timeFormat($values['send_date'], 'Y-m-d H:i:s', 'd/m/Y H:i');
        
        $message->fromArray($values);

        $message->save();
        
        return $message;
    }
    
    public function getMessageById($id){
        return $this->messageTable->findOneBy('id', $id);
    }
    
    public function getSettingsByMessage($id){
        return $this->settingsTable->findBy('message_id', $id);
    }
    
}

