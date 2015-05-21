<?php

/**
 * AssignDiscount
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_AssignDiscount extends Admin_Form {
    
    public function init() {
        
        $productId = $this->createElement('multiselect', 'product_id');
        $productId->setDecorators(self::$selectDecorators);
        $productId->setAttrib('multiple', 'multiple');
        
        $productAssigned = $this->createElement('multiselect', 'product_selected');
        $productAssigned->setDecorators(self::$selectDecorators);
        $productAssigned->setAttrib('multiple', 'multiple');
          
        $producerId = $this->createElement('multiselect', 'producer_id');
        $producerId->setDecorators(self::$selectDecorators);
        $producerId->setAttrib('multiple', 'multiple');
        
        $producerAssigned = $this->createElement('multiselect', 'producer_selected');
        $producerAssigned->setDecorators(self::$selectDecorators);
        $producerAssigned->setAttrib('multiple', 'multiple');
        
        $userId = $this->createElement('multiselect', 'user_id');
        $userId->setDecorators(self::$selectDecorators);
        $userId->setAttrib('multiple', 'multiple');
        
        $userAssigned = $this->createElement('multiselect', 'user_selected');
        $userAssigned->setDecorators(self::$selectDecorators);
        $userAssigned->setAttrib('multiple', 'multiple');
        
        $groupId = $this->createElement('multiselect', 'group_id');
        $groupId->setDecorators(self::$selectDecorators);
        $groupId->setAttrib('multiple', 'multiple');
        
        $groupAssigned = $this->createElement('multiselect', 'group_selected');
        $groupAssigned->setDecorators(self::$selectDecorators);
        $groupAssigned->setAttrib('multiple', 'multiple');
 
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $productId,
            $productAssigned,
            $producerId,
            $producerAssigned,
            $userId,
            $userAssigned,
            $groupId,
            $groupAssigned,
            $submit,
        ));
    }
}
?>