<?php

/**
 * Product_Service_Discount
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Service_Discount extends MF_Service_ServiceAbstract {
    
    protected $discountTable;
    
    public function init() {
        $this->discountTable = Doctrine_Core::getTable('Product_Model_Doctrine_Discount');
        parent::init();
    }
    
    public function getDiscount($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->discountTable->findOneBy($field, $id, $hydrationMode);
    }
   
    public function getDiscountForm(Product_Model_Doctrine_Discount $discount = null) {
        $form = new Product_Form_Discount();
        if(null != $discount) { 
            $form->populate($discount->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($discount->Translation[$language]->name);
                }
            }
        }
        return $form;
    }
    
    public function getAssignDiscountForm(Product_Model_Doctrine_Discount $discount = null) {
        $form = new Product_Form_AssignDiscount();
        if(null != $discount) { 
            $form->populate($discount->toArray());
        }
        return $form;
    }
    
    public function saveDiscountFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$discount = $this->getDiscount((int) $values['id'])) {
            $discount = $this->discountTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['start_date'])) {
            $date = new Zend_Date($values['start_date'], 'dd/MM/yyyy HH:mm');
            $values['start_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($discount['start_date'])) {
            $values['start_date'] = date('Y-m-d H:i:s');
        }
        
        if(strlen($values['finish_date'])) {
            $date = new Zend_Date($values['finish_date'], 'dd/MM/yyyy HH:mm');
            $values['finish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($discount['finish_date'])) {
            $values['finish_date'] = date('Y-m-d H:i:s');
        }
         
        $discount->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $discount->Translation[$language]->name = $values['translations'][$language]['name'];
            }
        }
        
        $discount->save();
        
        return $discount;
    }
    
    public function removeDiscount(Product_Model_Doctrine_Discount $discount) {
        $discount->unlink('Producers');
        $discount->unlink('Categories');
        $discount->unlink('Products');
        $discount->unlink('Users');
        $discount->unlink('Groups');
        $discount->get('Translation')->delete();
        $discount->save(); 
        $discount->delete();
    }
    
    public function refreshStatusDiscount($discount){
        if ($discount->isStatus()):
            $discount->setStatus(0);
        else:
            $discount->setStatus(1);
        endif;
        $discount->save();
    }
    
    public function getAllDiscounts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->discountTable->getDiscountQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetDiscountSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllDiscounts();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name.': '.$item->amount_discount."%";
        }
        return $result;
    }
}
?>