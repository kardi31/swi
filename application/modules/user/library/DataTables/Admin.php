<?php

/**
 * User_DataTables_Admin
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_DataTables_Admin extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Admin';
    }
}

