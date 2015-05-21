<?php

/**
 * User_Form_Update
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Form_Update extends Zend_Form
{
    const salt = '6e26899d3195dabb8553dffe84899e0c';
    
    public function init() {
        $csrf = $this->createElement('hash', 'csrf');
		$csrf->setSalt(self::salt);
		$csrf->setDecorators(array('ViewHelper'));
        
        $userId = $this->createElement('hidden', 'user_id');
        $userId->setDecorators(array('ViewHelper'));
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        
        $password = $this->createElement('password', 'password');
		$password->setLabel('Password');
        $password->setDecorators(array('ViewHelper'));
		
		$confirmPassword = $this->createElement('password', 'confirm_password');
		$confirmPassword->setLabel('Confirm password');
		$confirmPassword->setValidators(array(array('Identical', false, array('token' => 'password'))));
        
        $token = $this->createElement('hidden', 'token');
        $token->setDecorators(array('ViewHelper'));
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Send');
		
		$this->setElements(array(
			$csrf,
            $userId,
            $email,
            $password,
            $confirmPassword,
            $token,
			$submit
		));
    }
}

