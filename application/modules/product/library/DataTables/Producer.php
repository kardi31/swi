<?php

/**
 * Product_DataTables_Producer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_DataTables_Producer extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Product_DataTables_Adapter_Producer';
    }
}

