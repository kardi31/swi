<?php

/**
 * Newsletter_Form_Group
 *
 * @author Mateusz Aniołek
 */
class Newsletter_Form_Newsletter extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Search');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'button'));
        
        $this->setElements(array(
            $id,
            $submit
        ));
    }
}

