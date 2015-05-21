<?php

/**
 * Invoice_DataTables_Invoice
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_DataTables_Invoice extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Invoice_DataTables_Adapter_Invoice';
    }
}

