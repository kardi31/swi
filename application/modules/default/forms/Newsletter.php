<?php

/**
 * Contact
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_Newsletter extends Admin_Form {
    
    const SALT = 'cdf1fb4cd650bcf617ff89db3f0068f7';
    
    public function init() {
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('E-mail');
        $email->setValidators(array('EmailAddress'));
        $email->setDecorators(self::$textDecorators);
        $email->setRequired();
        $email->setAttrib('class', 'span8');
        
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Sign up!');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $email,
            $submit
        ));
    }
}

