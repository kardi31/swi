<?php

/**
 * Contact
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_Contact extends Admin_Form {
    
   
    public function init() {
        $name = $this->createElement('text', 'name');
        $name->setLabel('First name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'col-12');
        $name->setAttrib('placeholder', 'Imie i nazwisko');
        
//        $surname = $this->createElement('text', 'surname');
//        $surname->setLabel('Last name');
//        $surname->setRequired(true);
//        $surname->setDecorators(self::$textDecorators);
//        $surname->setAttrib('class', 'span8');
        
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setRequired(true);
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'col-12');
        $email->setAttrib('placeholder', 'Email');
        
        
        $phone = $this->createElement('text','phone');
        $phone->setLabel('Phone');
        $phone->setDecorators(self::$textDecorators);
        $phone->setAttrib('class', 'col-12');
        $phone->setAttrib('placeholder', 'Telefon');
        
        
//        $subject = $this->createElement('text', 'subject');
//        $subject->setLabel('Title');
//        $subject->setRequired(true);
//        $subject->setDecorators(self::$textDecorators);
//        $subject->setAttrib('class', 'span8');
        
        
        $message = $this->createElement('textarea', 'message');
        $message->setLabel('Message');
        $message->setRequired(true);
        $message->setDecorators(self::$textDecorators);
        $message->setAttrib('class', 'col-12');
        $message->setAttrib('rows', '7');
        $message->setAttrib('placeholder', 'Wiadomość');
        
        
          
       $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Wyślij');
        $submit->setDecorators(self::$submitDecorators);
        $submit->setAttribs(array('class' => 'btn-dflt btn-red', 'type' => 'submit'));
        $submit->setAttrib('style', 'margin-top:20px;margin-bottom:10px;');
       
        $this->setElements(array(
            $name,
//            $surname,
            $email,
            $phone,
//            $subject,
            $message,
            $submit
        ));
    }
}

