<?php

/**
 * Product_Forms_Relate
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Relate extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
               
        $productId = $this->createElement('multiselect', 'product_id');
        $productId->setLabel('Edit products');
        $productId->setDecorators(self::$selectDecorators);
        $productId->setAttrib('multiple', 'multiple');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $productId, 
            $submit,
        ));
    }
}
?>