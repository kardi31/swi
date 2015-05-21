<?php

/**
 * User_DataTables_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_DataTables_Group extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Group';
    }
}

