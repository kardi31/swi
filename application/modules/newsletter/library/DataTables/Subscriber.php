<?php

/**
 * Newsletter_DataTables_Subscriber
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_DataTables_Subscriber extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Newsletter_DataTables_Adapter_Subscriber';
    }
}

