<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Board extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'League_DataTables_Adapter_Board';
    }
}

