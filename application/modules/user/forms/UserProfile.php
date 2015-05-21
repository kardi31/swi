<?php

class User_Form_UserProfile extends Zend_Form
{
    const SALT = '6e26899d3195dabb8553dffe84899e0c';
    
    public function init() {
		$csrf = $this->createElement('hash', 'csrf');
		$csrf->setSalt(self::SALT);
		$csrf->setDecorators(array('ViewHelper'));
        $csrf->setOrder(20);
        
        $firstName = $this->createElement('text', 'first_name');
        $firstName->setLabel('First name');
        $firstName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $firstName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $firstName->setRequired();

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));

        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired();

        $password = $this->createElement('password', 'password');
		$password->setLabel('Password');
		
		$confirmPassword = $this->createElement('password', 'confirm_password');
		$confirmPassword->setLabel('Confirm password');
		$confirmPassword->setValidators(array(array('Identical', false, array('token' => 'password'))));
			
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setAttrib('type', 'submit');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        

        $this->setElements(array(
            $csrf,
            $firstName,
            $lastName,
            $email,
            $password,
            $confirmPassword,
            $submit,
            $id
        ));
    }
}