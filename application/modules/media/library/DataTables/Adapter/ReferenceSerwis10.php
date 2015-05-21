<?php

/**
 * Reference_DataTables_Adapter_ReferenceSerwis5
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Media_DataTables_Adapter_Attachment extends Default_DataTables_Adapter_AdapterAbstract {
   
     public function getBaseQuery() {
        $q = $this->table->createQuery('a');
        $q->select('a.*');
        return $q;
    }
    
}

