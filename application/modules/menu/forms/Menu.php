<?php

class Menu_Form_Menu extends Admin_Form
{
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('VIewHelper'));
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setDecorators(self::$textDecorators);
        $name->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $name->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $name->setRequired();
        $name->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $submit
        ));
    }
    
}

