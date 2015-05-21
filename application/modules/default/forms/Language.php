<?php

class Default_Form_Language extends Admin_Form
{
    public function init() {
        $id = $this->createElement('text', 'id');
        $id->setLabel('Id');
        $id->setDecorators(self::$textDecorators);
        $id->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $id->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $id->setAttrib('class', 'span8');
        $id->setRequired();
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setDecorators(self::$textDecorators);
        $name->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $name->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $name->setAttrib('class', 'span8');
        $name->setRequired();
        
        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Active');
        $active->setDecorators(self::$checkgroupDecorators);
        $active->setAttrib('class', 'span8');
        $active->setRequired();
        
        $default = $this->createElement('checkbox', 'default');
        $default->setLabel('Default');
        $default->setDecorators(self::$checkgroupDecorators);
        $default->setAttrib('class', 'span8');
        $default->setRequired();
        
        $admin = $this->createElement('checkbox', 'admin');
        $admin->setLabel('Admin');
        $admin->setDecorators(self::$checkgroupDecorators);
        $admin->setAttrib('class', 'span8');
        $admin->setRequired();
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $active,
            $default,
            $admin,
            $submit
        ));
        
    }
}

