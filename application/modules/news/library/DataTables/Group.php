<?php

/**
 * News_DataTables_NewsSerwis1
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_DataTables_Group extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'News_DataTables_Adapter_Group';
    }
}

