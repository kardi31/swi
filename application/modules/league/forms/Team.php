<?php

class League_Form_Team extends Admin_Form
{
    public function init() {
    
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
    
        
        $team = $this->createElement('text', 'name');
        $team->setLabel('Nazwa drużyny');
        $team->setRequired(true);
        $team->setDecorators(self::$textDecorators);
        $team->setAttrib('class', 'span8');
  
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Zapisz');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $team,
            $submit
        ));
    }
}