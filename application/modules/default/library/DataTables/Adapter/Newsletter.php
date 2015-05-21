<?php

/**
 * Order_DataTables_Adapter_Delivery
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Default_DataTables_Adapter_Newsletter extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('n');
        $q->select('n.*');
        return $q;
    }
}

