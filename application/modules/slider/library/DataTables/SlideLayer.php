<?php

/**
 * Slider_DataTables_SlideLayer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Slider_DataTables_SlideLayer extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Slider_DataTables_Adapter_SlideLayer';
    }
}

