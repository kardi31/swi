<?php

/**
 * News_DataTables_adapter_NewsSerwis1
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_DataTables_Adapter_Tag extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        return $q;
    }
}

