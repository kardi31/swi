<?php

/**
 * Product
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Preview extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name of product');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $producerId = $this->createElement('select', 'producer_id');
        $producerId->setLabel('Producer');
        $producerId->setDecorators(self::$selectDecorators);
        
        $categoryId = $this->createElement('multiselect', 'category_id');
        $categoryId->setLabel('Categories');
        $categoryId->setRequired();
        $categoryId->setDecorators(self::$selectDecorators);
        $categoryId->setAttrib('multiple', 'multiple');
        
        $description = $this->createElement('textarea', 'description');
        $description->setLabel('Description');
        $description->setDecorators(self::$tinymceDecorators);
        $description->setAttrib('class', 'span8 tinymce');
        
        $youtube = $this->createElement('text', 'youtube');
        $youtube->setLabel('Youtube');
        $youtube->setRequired(false);
        $youtube->setDecorators(self::$textDecorators);
        $youtube->setAttrib('class', 'span8');
         
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $producerId, 
            $categoryId,
            $description,
            $youtube,
            $submit,
        ));
    }
}
?>