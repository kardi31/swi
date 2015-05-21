<?php

/**
 * News_DataTables_NewsSerwis1
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_DataTables_Event extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'District_DataTables_Adapter_Event';
    }
}

