<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_DataTables_Gallery extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Gallery_DataTables_Adapter_Gallery';
    }
}

