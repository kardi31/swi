<?php

class League_Form_Booking extends Admin_Form
{
    public function init() {
    
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $weight = $this->createElement('radio', 'weight');
        $weight->setLabel('Typ kartki');
        $weight->setRequired(true);
        $weight->setDecorators(self::$textDecorators);
        $weight->setAttrib('class', 'span8');
        $weight->addMultiOption(1,'Żółta');
        $weight->addMultiOption(2,'Czerwona');
        $weight->setValue(1);
        
        $player = $this->createElement('select', 'player_id');
        $player->setLabel('Zawodnik');
        $player->setRequired(true);
        $player->setDecorators(self::$selectDecorators);
        $player->setAttrib('class', 'span8');
  
        $comment = $this->createElement('textarea', 'comment');
        $comment->setLabel('Komentarz(dla czerwonej kartki tylko)');
        $comment->setRequired(false);
        $comment->setDecorators(self::$textareaDecorators);
        $comment->setAttrib('class', 'span8');
        
        $quantity = $this->createElement('radio', 'quantity');
        $quantity->setLabel('Ilość kartek');
        $quantity->setRequired(true);
        $quantity->setDecorators(self::$textDecorators);
        $quantity->setAttrib('class', 'span8');
        $quantity->addMultiOption(1,1);
        $quantity->addMultiOption(2,2);
        $quantity->setValue(1);
        
        
        $active = $this->createElement('hidden', 'active');
        $active->setLabel('Ilość kartek');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Zapisz');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $weight,
            $player,
            $active,
            $comment,
            $quantity,
            $submit
        ));
    }
}