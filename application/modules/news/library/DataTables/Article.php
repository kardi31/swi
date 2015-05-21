<?php

/**
 * Article
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class News_DataTables_Article extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'News_DataTables_Adapter_Article';
    }
}

