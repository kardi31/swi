<?php

/**
 * Product_DataTables_Preview
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_DataTables_Preview extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Product_DataTables_Adapter_Preview';
    }
}

