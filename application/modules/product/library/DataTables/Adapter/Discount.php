<?php

/**
 * Product_DataTables_Adapter_Discount
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_DataTables_Adapter_Discount extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        return $q;
    }
}

