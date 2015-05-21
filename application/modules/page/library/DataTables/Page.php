<?php

/**
 * Page
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_DataTables_Page extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Page_DataTables_Adapter_Page';
    }
}

