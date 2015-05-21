<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Match extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'League_DataTables_Adapter_Match';
    }
}

