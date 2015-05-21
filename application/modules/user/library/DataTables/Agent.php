<?php

/**
 * User_DataTables_Agent
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_DataTables_Agent extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Agent';
    }
}

