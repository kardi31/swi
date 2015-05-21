<?php

/**
 * Invoice_Service_Invoice
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_Service_Invoice extends MF_Service_ServiceAbstract {
    
    public static $invoiceCodePrefix = 'F';
    
    protected $invoiceTable;
    protected $paymentTable;
    protected $cart;
    
    public function init() {
        $this->invoiceTable = Doctrine_Core::getTable('Invoice_Model_Doctrine_Invoice');
        $this->paymentTable = Doctrine_Core::getTable('Invoice_Model_Doctrine_Payment');
        parent::init();
    }
 
    public function createInvoiceCode(Invoice_Model_Doctrine_Invoice $invoice) {
        return self::$invoiceCodePrefix . '-' . MF_Text::timeFormat($invoice['created_at'], 'Ymd') . '-' . $invoice['id'];
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Invoice_Model_Cart();
        }
        return $this->cart;
    }
    
    public function getInvoice($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->invoiceTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullInvoice($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->invoiceTable->getFullInvoiceQuery();
        $q->andWhere('i.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getCurrentInvoice($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->invoiceTable->getCurrentInvoiceQuery();
        $q->andWhere('(i.user_id = ?)', $userId);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getActiveInvoice($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->invoiceTable->getCurrentInvoiceQuery();
        $q->andWhere('(i.user_id = ?)', $userId);
        $q->andWhere('p.status > ?', MF_Code::STATUS_NEW);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getNewInvoices($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->invoiceTable->getFullInvoiceQuery();
        $q->andWhere('i.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function purchaseCart(Invoice_Model_Cart $cart = null) {
        $invoice = $this->invoiceTable->getRecord();
        
        if(null == $cart) {
            $cart = $this->getCart();
        }
        $items = $cart->getItems();
        
        foreach($items as $class => $ids) {
            foreach($ids as $id => $data) {
                if($item = Invoice_Model_InvoiceItem::factory($class, $id, $data['name'], $data['price'], $data['count']))
                    $invoice->get('Items')->add($item);
            }
        }
        
        return $invoice;
    }
    
    public function fetchPayment(Invoice_Model_Doctrine_Invoice $invoice, $paymentType = Invoice_Model_Doctrine_Payment::TYPE_PAYU) {
        if($payment != $invoice->get('Payment') || $invoice->get('Payment')->isInProxyState()) {
            $payment = $this->paymentTable->getRecord();
            $payment->setType($paymentType);
            $payment->setStatus(MF_Code::STATUS_NEW); // zmienić na kod PayU ?
            $payment->set('Invoice', $invoice);
            $payment->save();
        }
        return $payment;
    }
    
    public function applyInvoice(Invoice_Model_Doctrine_Invoice $invoice) {
        $items = $invoice->get('Items');
        
        if($invoice->getExecutionEndDate()) {
            return $invoice;
        }
        
        $monthCount = 1;
        
        foreach($items as $item) {
            switch($item->getItemType()) {
                case Invoice_Model_InvoiceItem_Category::INVOICE_ITEM_TYPE:
                    if($item->getCount() && $item->getCount() > $monthCount)
                        $monthCount = $item->getCount();
                    break;
            }
        }
        
        $date = new Zend_Date(date('Y-m-d 00:00:00'), Zend_Date::ISO_8601);
        $invoice->setExecutionStartDate($date->toString(Zend_Date::ISO_8601));
        $date->addMonth($monthCount);
        $date->subDay(1);
        $invoice->setExecutionEndDate($date->toString(Zend_Date::ISO_8601));

        return $invoice;
    }
}

