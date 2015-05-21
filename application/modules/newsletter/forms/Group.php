<?php

/**
 * Newsletter_Form_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Form_Group extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');

        $subscriberId = $this->createElement('multiselect', 'subscriber_id');
        $subscriberId->setLabel('Subscribers');
        $subscriberId->setRequired();
        $subscriberId->setDecorators(self::$selectDecorators);
        $subscriberId->setAttrib('multiple', 'multiple');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $subscriberId,
            $name,
            $id,
            $submit
        ));
    }
}

