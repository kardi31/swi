<?php

class League_Form_League extends Admin_Form
{
    public function init() {
    
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $team = $this->createElement('text', 'name');
        $team->setLabel('Nazwa ligi');
        $team->setRequired(true);
        $team->setDecorators(self::$textDecorators);
        $team->setAttrib('class', 'span8');
	
        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Aktywna');
        $active->setRequired(true);
        $active->setDecorators(self::$checkboxDecorators);
        $active->setAttrib('class', 'span8');
        $active->setValue(1);
        
        $group = $this->createElement('select', 'group_id');
        $group->setLabel('Kategoria');
        $group->setRequired(true);
        $group->setDecorators(self::$selectDecorators);
        $group->setAttrib('class', 'span8');
  
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Zapisz');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $team,
	    $active,
            $group,
            $submit
        ));
    }
}