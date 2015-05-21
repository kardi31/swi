<?php

/**
 * Invoice_Form_PayU
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_Form_PayU extends Zend_Form {
    
    public function init() {
        $posId = $this->createElement('hidden', 'pos_id');
        $posId->setDecorators(array('ViewHelper'));
        $posAuthKey = $this->createElement('hidden', 'pos_auth_key');
        $posAuthKey->setDecorators(array('ViewHelper'));
        $sessionId = $this->createElement('hidden', 'session_id');
        $sessionId->setDecorators(array('ViewHelper'));
        $amount = $this->createElement('hidden', 'amount');
        $amount->setDecorators(array('ViewHelper'));
        $desc = $this->createElement('hidden', 'desc');
        $desc->setDecorators(array('ViewHelper'));
        
        $firstName = $this->createElement('hidden', 'first_name');
        $firstName->setDecorators(array('ViewHelper'));
        $lastName = $this->createElement('hidden', 'last_name');
        $lastName->setDecorators(array('ViewHelper'));
        $email = $this->createElement('hidden', 'email');
        $email->setDecorators(array('ViewHelper'));
        
        $clientIp = $this->createElement('hidden', 'client_ip');
        $clientIp->setDecorators(array('ViewHelper'));
        $js = $this->createElement('hidden', 'js');
        $js->setDecorators(array('ViewHelper'));
        $js->setValue(0);
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Pay');
        $submit->setDecorators(array('ViewHelper'));
        
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        
        $this->addElements(array(
            $posId, $posAuthKey, $sessionId, $amount, $desc, $firstName, $lastName, $email, $clientIp, $js, $submit
        ));
    }
    
}

