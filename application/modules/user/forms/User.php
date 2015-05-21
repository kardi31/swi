<?php

class User_Form_User extends Admin_Form
{
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $firstName = $this->createElement('text', 'first_name');
        $firstName->setLabel('First name');
        $firstName->setRequired();
        $firstName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $firstName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $firstName->setDecorators(self::$textDecorators);
        $firstName->setAttrib('class', 'span8');

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->setRequired();
        $lastName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $lastName->setDecorators(self::$textDecorators);
        $lastName->setAttrib('class', 'span8');

        $username = $this->createElement('text', 'username');
        $username->setLabel('Username');
        $username->setDecorators(self::$textDecorators);
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setDecorators(self::$textDecorators);
        $email->setRequired();
        $email->setAttrib('class', 'span8');

        $role = $this->createElement('radio', 'role');
        $role->setLabel('Role');
        $role->setDecorators(self::$textDecorators);
        $role->setRequired();
        $role->setAttrib('class', 'span8');

        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Active');
        $active->setDecorators(self::$checkgroupDecorators);
        $active->setRequired();
        $active->setAttrib('class', 'span8');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $firstName,
            $lastName,
            $username,
            $email,
            $role,
            $active,
            $submit
        ));
    }
}