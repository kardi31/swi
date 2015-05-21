<?php

class League_Form_Coach extends Admin_Form
{
    public function init() {
    
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $first_name = $this->createElement('text', 'first_name');
        $first_name->setLabel('Imie');
        $first_name->setRequired(true);
        $first_name->setDecorators(self::$textDecorators);
        $first_name->setAttrib('class', 'span8');
  
        $last_name = $this->createElement('text', 'last_name');
        $last_name->setLabel('Nazwisko');
        $last_name->setRequired(true);
        $last_name->setDecorators(self::$textDecorators);
        $last_name->setAttrib('class', 'span8');
        
        $position = $this->createElement('text', 'position');
        $position->setLabel('Pozycja');
        $position->setRequired(true);
        $position->setDecorators(self::$textDecorators);
        $position->setAttrib('class', 'span8');
        
        $team_id = $this->createElement('select', 'team_id');
        $team_id->setLabel('Drużyna');
        $team_id->setRequired(true);
        $team_id->setDecorators(self::$selectDecorators);
        $team_id->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Zapisz');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $first_name,
            $last_name,
            $team_id,
            $position,
            $submit
        ));
    }
}