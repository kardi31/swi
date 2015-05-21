<?php

/**
 * Invoice_DataTables_Adapter_Invoice
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_DataTables_Adapter_Invoice extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('u.*');
        $q->addSelect('i.*');
        $q->addSelect('p.*');
        $q->addSelect('TRIM(i.name) as name');
        $q->leftJoin('x.User u');
        $q->leftJoin('x.Items i');
        $q->leftJoin('x.Payment p');
        $q->addOrderBy('i.id');
        return $q;
    }
}

