<?php

/**
 * Newsletter_DataTables_Message
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_DataTables_Message extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Newsletter_DataTables_Adapter_Message';
    }
}

