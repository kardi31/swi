<?php

/**
 * Newsletter_Service_Subscriber
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Service_Subscriber extends MF_Service_ServiceAbstract {
    
    protected $subscriberTable;
    
    public function init() {
        $this->subscriberTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Subscriber');
        parent::init();
    }
    
    public function getSubscriber($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->subscriberTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getAllSubscribers($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->subscriberTable->getSubscriberQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetSubscriberSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllSubscribers();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
            $result[$item->getId()] = $item->username." - ".$item->email;
        }

        return $result;
    }
    
    public function removeSubscriber($subscriber){
        $subscriber->unlink('Groups');
        $subscriber->save();
        $subscriber->delete();        
    }
    
    public function getRegisterForm(Newsletter_Model_Doctrine_Subscriber $subscriber = null) {
        $form = new Newsletter_Form_Register();
        if(null != $subscriber) { 
            $form->populate($subscriber->toArray());
        }
        return $form;
    }
    
    public function subscriberExists($array) {
        return (!empty($array)) ? !!$this->subscriberTable->findOneBy('email', $array['email']) : false;  
    }
    
    public function saveSubscriberFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        if(!$subscriber = $this->subscriberTable->getProxy($values['id'])) {
            $subscriber = $this->subscriberTable->getRecord();
        }
        
        if (!$this->subscriberExists(array('email' => $values['email']))):
            $subscriber->fromArray($values);
        endif; 
        
        $subscriber->link('Groups', 1);
        $subscriber->save();
                
        return $subscriber;
    }
    
    public function sendRegistrationMail(Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/register-complete.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial)
        );
        $mail->send(); 
    }

}

