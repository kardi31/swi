<?php

/**
 * Page_DataTables_PageShop
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Page_DataTables_PageShop extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Page_DataTables_Adapter_PageShop';
    }
}

