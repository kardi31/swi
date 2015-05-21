<?php

/**
 * Invoice_Model_InvoiceItem_Category
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_Model_InvoiceItem_Category {
    
    const INVOICE_ITEM_TYPE = 'category';
    
    public static $nameFormat = "%s - Abonament na %s";
    
    public function createInvoiceItem($name, $id, $price, $count) {
        $item = new Invoice_Model_Doctrine_Item();
        
        $serviceBroker = MF_Service_ServiceBroker::getInstance();
        $translator = $serviceBroker->get('translate');
        
        $periods = Offer_Model_Doctrine_CategoryPrice::getAvailablePeriods();
        
        $name = sprintf(self::$nameFormat, $name, $translator->translate($periods[$count]));
        
        $item->setName($name);
        $item->setItemId($id);
        $item->setItemType(self::INVOICE_ITEM_TYPE);
        $item->setPrice($price);
        $item->setCount($count);
        $item->save();
        
        return $item;
    }
}

