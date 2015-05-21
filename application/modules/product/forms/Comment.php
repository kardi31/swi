<?php

/**
 * Product_Form_Comment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Comment extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
         
        $description = $this->createElement('textarea', 'description');
        $description->setLabel('Description');
        $description->setRequired(false);
        $description->setDecorators(self::$tinymceDecorators);
        $description->setAttrib('class', 'span8 tinymce');
    
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $description,
            $submit
        ));
    }
    
} 