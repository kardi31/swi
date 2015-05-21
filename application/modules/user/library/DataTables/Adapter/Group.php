<?php

/**
 * User_DataTables_Adapter_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_DataTables_Adapter_Group extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('gr');
        $q->select('gr.*');
        return $q;
    }
}

