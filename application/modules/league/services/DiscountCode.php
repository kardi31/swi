<?php

/**
 * Order_Service_DiscountCode
 *

 */
class Order_Service_DiscountCode extends MF_Service_ServiceAbstract {
    
    protected $discountCodeTable;
    
    public function init() {
        $this->discountCodeTable = Doctrine_Core::getTable('Order_Model_Doctrine_DiscountCode');
        parent::init();
    }
    
    public function getDiscountCode($code, $field = 'code', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->discountCodeTable->findOneBy($field, $code, $hydrationMode);
    }
    public function getDiscountCodeById($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->discountCodeTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getDiscountCodes() {    
        return $this->discountCodeTable->findAll();
    }
    public function getDiscountCodeForm(Order_Model_Doctrine_DiscountCode $discountCode = null) {
        $form = new Order_Form_DiscountCode();
        if(null != $discountCode) { 
            $form->populate($discountCode->toArray());
        }
        return $form;
    }
    public function changeActiveStatus($id,$status) {
        $discountCode = $this->getDiscountCodeById((int) $id);
        $discountCode->setActive($status);
        $discountCode->save();
    }
  
    
    public function saveDiscountCodeFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$discountCode = $this->getDiscountCode((int) $values['id'])) {
            $discountCode = $this->discountCodeTable->getRecord();
        }
        $values['active'] = 1;
        $values['discount'] = $values['discount'] / 100;
        $discountCode->fromArray($values);
        $discountCode->save();
        
        return $discountCode;
    }
    
    public function removeDiscountCode(Order_Model_Doctrine_DiscountCode $discountCode) {
        $discountCode->delete();
    }
}
?>