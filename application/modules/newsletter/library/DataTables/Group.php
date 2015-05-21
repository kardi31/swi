<?php

/**
 * Newsletter_DataTables_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_DataTables_Group extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Newsletter_DataTables_Adapter_Group';
    }
}

