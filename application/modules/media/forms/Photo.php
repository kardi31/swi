<?php

/**
 * Photo
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Form_Photo extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $title->setDecorators(self::$textDecorators);
        $title->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $title,
            $submit
        ));
    }
}

