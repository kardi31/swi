<?php

/**
 * Newsletter_Form_SendForm
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Form_SendForm extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Send');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $submit
        ));
        
    }
}

