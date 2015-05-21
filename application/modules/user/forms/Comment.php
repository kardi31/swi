<?php

/**
 * User_Form_Comment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Form_Comment extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));

        $nick = $this->createElement('text', 'nick');
        $nick->setLabel('Nick');
        $nick->setRequired();
        $nick->setDecorators(self::$textDecorators);
        $nick->setAttrib('class', 'span8');
        
        $description = $this->createElement('textarea', 'description');
        //$description->setLabel('Add comment');
        $description->setRequired(true);
        $description->setDecorators(self::$textDecorators);
        $description->setAttrib('class', 'span8');
    
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $nick,
            $description,
            $submit
        ));
    }
    
}