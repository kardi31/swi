<?php

/**
 * Order_DataTables_Delivery
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Default_DataTables_Newsletter extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Default_DataTables_Adapter_Newsletter';
    }
}

