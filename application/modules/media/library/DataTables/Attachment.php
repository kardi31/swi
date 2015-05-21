<?php

/**
 * Reference_DataTables_ReferenceSerwis7
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Media_DataTables_Attachment extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Media_DataTables_Adapter_Attachment';
    }
}

