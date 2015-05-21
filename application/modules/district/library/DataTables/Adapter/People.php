<?php

/**
 * News_DataTables_adapter_NewsSerwis1
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_DataTables_Adapter_People extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->leftJoin('x.Translation xt');
        return $q;
    }
}

