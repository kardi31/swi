<?php

/**
 * InvoiceItem
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_Model_InvoiceItem {
    
    public static function factory($class, $id, $name, $price, $count) {
        switch ($class) {
            case 'Offer_Model_Doctrine_Category':
                $class = 'Invoice_Model_InvoiceItem_Category';
                break;
        }
        
        $factory = new $class();
        $item = $factory->createInvoiceItem($name, $id, $price, $count);
        return $item;
    }
}

