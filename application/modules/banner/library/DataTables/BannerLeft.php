<?php

/**
 * Banner_DataTables_BannerLeft
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_DataTables_BannerLeft extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Banner_DataTables_Adapter_BannerLeft';
    }
}

