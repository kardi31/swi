<?php

/**
 * Product_DataTables_Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_DataTables_Category extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Product_DataTables_Adapter_Category';
    }
}

