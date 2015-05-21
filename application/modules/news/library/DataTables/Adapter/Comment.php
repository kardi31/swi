<?php

/**
 * News_DataTables_adapter_NewsSerwis1
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_DataTables_Adapter_Comment extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->leftJoin('x.News n');
        $q->leftJoin('n.Translation nt');
        
        return $q;
    }
}

