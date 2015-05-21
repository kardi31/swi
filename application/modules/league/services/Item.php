<?php

/**
 * Order_Service_Order
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Item extends MF_Service_ServiceAbstract {
    
    protected $itemTable;
    protected $cart;
    
    public function init() {
        $this->itemTable = Doctrine_Core::getTable('Order_Model_Doctrine_Item');
        parent::init();
    }
    
    public function getItem($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->itemTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getItemForm(Order_Model_Doctrine_Item $item = null) {
        $form = new Order_Form_Item();
        if(null != $item) { 
            $form->populate($item->toArray());
        }
        return $form;
    }
       public function saveItemFromArray($values) {
     
  //  Zend_Debug::dump($values);
//    exit;
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$item = $this->getItem((int) $values['id'])) {
            $item = $this->itemTable->getRecord();
        }
        $item->fromArray($values);
        $item->save();
        
        return $item;
    }

    public function saveItemsFromArray($values,$order_id)
    {
        $productService = new Product_Service_Product();
        $sumPrice = 0;
         $key = 0;
    //    Zend_Debug::dump($values);
        $keys = array_keys($values['Product_Model_Doctrine_Product']);
        //Zend_Debug::dump($keys);
     foreach($values['Product_Model_Doctrine_Product'] as $item):
        
        foreach($item as $itemDetail):
            $itemDetail['product_id'] = $keys[$key];  
            $itemDetail['order_id'] = $order_id;
            $itemDetail['number'] = $itemDetail['count'];
            $sumPrice += $itemDetail['price'];
          //  echo $itemDetail['dimension']."<-dim   ->id ".$itemDetail['product_id']."  <br />"; exit;
            $dpr = $productService->getProduct($keys[$key]);
            // zmniejszanie dostepnej ilosci i zwiekszanie licznika zakupow
            $avail = $dpr->getAvailability();
            $mfp = $dpr->getPurchasedNumber();
            $dpr->setAvailability($avail-1);
            $dpr->setPurchasedNumber($mfp+1);
            $dpr->save();
            $this->saveItemFromArray($itemDetail);
            $key++;
        endforeach;
    endforeach;
    return $sumPrice;
    }
       
    public function getFullItem($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->itemTable->getFullItemQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getNewItems($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->itemTable->getFullItemQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllItems($countOnly = false) {
        if(true == $countOnly) {
            return $this->itemTable->count();
        } else {
            return $this->itemTable->findAll();
        }
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
}
?>