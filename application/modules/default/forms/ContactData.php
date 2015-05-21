<?php

/**
 * Contact
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_ContactData extends Admin_Form {
    
    const SALT = 'cdf1fb4cd650bcf617ff89db3f0068f7';
    
    public function init() {
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
      $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        $name->setAttrib('disabled','disabled');
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('E-mail');
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'span8');
        
        $phone = $this->createElement('text', 'phone');
        $phone->setLabel('Phone');
        $phone->setDecorators(self::$textDecorators);
        $phone->setAttrib('class', 'span8');

        $address = $this->createElement('text', 'address');
        $address->setLabel('Address');
        $address->setDecorators(self::$textDecorators);
        $address->setAttrib('class', 'span8');
        
        $opening = $this->createElement('text', 'opening');
        $opening->setLabel('Opening hours');
        $opening->setDecorators(self::$textDecorators);
        $opening->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Send');
        $submit->setDecorators(self::$submitDecorators);
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $name,
            $email,
            $phone,
            $address,
            $opening,
            $submit
        ));
    }
}

